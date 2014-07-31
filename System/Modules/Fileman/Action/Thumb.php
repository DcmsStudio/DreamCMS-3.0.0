<?php

/**
 * DreamCMS 3.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * PHP Version 5
 *
 * @package      Fileman
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Thumb.php
 */
class Fileman_Action_Thumb extends Fileman_Helper_Base
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$this->configure(array ());

		$ts = $this->utime();
		$this->_thumbnails();

		$this->prepareData();


		Ajax::Send( (isset($this->_result['error']) ? false : true), $this->_result);
		exit;
		header("Content-Type: application/json");
		header("Connection: close");
		echo json_encode($this->_result);
		exit();
	}

	/**
	 * Create images thumbnails
	 *
	 * @return void
	 */
	protected function _thumbnails ()
	{

		if ( !empty($this->options[ 'tmbPath' ]) && !empty($_GET[ 'current' ]) && false != ($current = $this->_findDir(trim($_GET[ 'current' ]))) )
		{
			$this->_result[ 'current' ] = $this->_hash($current);
			$this->_result[ 'images' ]  = array ();
			$ls                         = scandir($current);
			$cnt                        = 0;
			$max                        = (int)$this->options[ 'tmbAtOnce' ] > 0 ?
				(int)$this->options[ 'tmbAtOnce' ] : 5;
			$_maxLs                     = count($ls);
			for ( $i = 0; $i < $_maxLs; $i++ )
			{
				if ( $this->_isAccepted($ls[ $i ]) )
				{
					$path  = $current . DIRECTORY_SEPARATOR . $ls[ $i ];
					$pinfo = pathinfo($path);
					$ext   = isset($pinfo[ 'extension' ]) ? strtolower($pinfo[ 'extension' ]) : '';
					$mime  = $this->getFileMime($ext);

					if ( is_readable($path) && $this->_canCreateTmb($mime) )
					{


						$tmb = $this->_tmbPath($path, false);

						# echo $tmb . '<br/>';
						if ( !file_exists($tmb) )
						{
							if ( $cnt >= $max )
							{
								return $this->_result[ 'tmb' ] = true;
							}
							else
							{
								if ( $this->_tmb($path, $tmb) )
								{
									// if ( $_GET[ 'coverflow' ] )
									// {
									$coverflow = $this->_tmbPath($path, true);
									if ( $this->_tmb($path, $coverflow, true) )
									{
										$this->_result[ 'images' ][ $this->_hash($path) ][ 'coverflow' ] = $this->_path2url($coverflow);
									}
									// }

									$this->_result[ 'images' ][ $this->_hash($path) ][ 'tmb' ] = $this->_path2url($tmb);
									$cnt++;
								}
								else
								{
									$this->_result[ 'images' ][ $this->_hash($path) ][ 'coverflow' ] = '-';
								}
							}
						}
					}
					else
					{
						$this->_result[ 'images' ][ $this->_hash($path) ][ 'coverflow' ] = '-';
					}
				}
			}
		}
	}

	/**
	 * Create image thumbnail
	 *
	 * @param string $img image file
	 * @param string $tmb thumbnail name
	 * @param bool   $isCoverflow
	 * @return bool
	 */
	protected function _tmb ( $img, $tmb, $isCoverflow = false )
	{

		if ( false === ($s = getimagesize($img)) )
		{
			return false;
		}

		$tmbSize = (!$isCoverflow ? $this->options[ 'tmbSize' ] : $this->options[ 'coverflowSize' ]);
		ini_set("gd.jpeg_ignore_warning", true);
		if ( $s[ 'mime' ] == 'image/jpeg' )
		{
			$_img = @imagecreatefromjpeg($img);
		}
		elseif ( $s[ 'mime' ] == 'image/png' )
		{
			$_img = @imagecreatefrompng($img);
		}
		elseif ( $s[ 'mime' ] == 'image/gif' )
		{
			$_img = @imagecreatefromgif($img);
		}

		if ( !$_img )
		{
			return false;
		}

		$x_orig = imagesx($_img);
		$y_orig = imagesy($_img);

		$ratio_orig = $x_orig / $y_orig;


		$newwidth  = $x_orig;
		$newheight = $tmbSize;


		if ( $newwidth / $newheight > $ratio_orig )
		{
			$newwidth = $newheight * $ratio_orig;
		}
		else
		{
			$newheight = $newwidth / $ratio_orig;
		}


		$align_x = 0;
		$align_y = 0;


		switch ( $this->options[ 'imgLib' ] )
		{
			case 'imagick':
				try
				{
					$_img = new imagick($img);
				}
				catch ( Exception $e )
				{
					return false;
				}

				$_img->contrastImage(1);

				if ( $this->options[ 'tmbCrop' ] == false )
				{
					$img1 = new Imagick();
					$img1->newImage($tmbSize, $tmbSize, new ImagickPixel($this->options[ 'tmbBgColor' ]));
					$img1->setImageFormat('png');
					$_img->resizeImage($newwidth, $newheight, null, true);
					$img1->compositeImage($_img, imagick::COMPOSITE_OVER, $align_x, $align_y);

					return $img1->writeImage($tmb);
				}
				else
				{
					return $_img->cropThumbnailImage($newwidth, $newheight) && $_img->writeImage($tmb);
				}
				break;

			case 'mogrify':
				if ( @copy($img, $tmb) )
				{
					list($x, $y, $size) = $this->_cropPos($s[ 0 ], $s[ 1 ]);
					// exec('mogrify -crop '.$size.'x'.$size.'+'.$x.'+'.$y.' -scale '.$tmbSize.'x'.$tmbSize.'! '.escapeshellarg($tmb), $o, $c);

					$mogrifyArgs = 'mogrify -resize ' . $tmbSize . 'x' . $tmbSize;

					if ( $this->options[ 'tmbCrop' ] == false )
					{
						$mogrifyArgs .= ' -gravity center -background "' . $this->options[ 'tmbBgColor' ] . '" -extent ' . $newwidth . 'x' . $newheight;
					}

					if ( $this->options[ 'tmbCrop' ] == false )
					{
						$mogrifyArgs .= ' ' . escapeshellarg($tmb);
					}

					exec($mogrifyArgs, $o, $c);

					if ( file_exists($tmb) )
					{
						return true;
					}
					elseif ( $c == 0 )
					{
						// find tmb for psd and animated gif
						$mime = $this->_mimetype($img);
						if ( $mime == 'image/vnd.adobe.photoshop' || $mime = 'image/gif' )
						{
							$pinfo = pathinfo($tmb);
							$test  = $pinfo[ 'dirname' ] . DIRECTORY_SEPARATOR . $pinfo[ 'filename' ] . '-0.' . $pinfo[ 'extension' ];
							if ( file_exists($test) )
							{
								return rename($test, $tmb);
							}
						}
					}
				}
				break;

			case 'gd':


				if ( !$_img || false == ($_tmb = imagecreatetruecolor($newwidth, $newheight)) )
				{
					return false;
				}

				if ( $this->options[ 'tmbCrop' ] == false )
				{

					list($r, $g, $b) = sscanf($this->options[ 'tmbBgColor' ], "#%02x%02x%02x");

					if ( $this->options[ 'tmbBgColor' ] != 'transparent' )
					{
						imagefill($_tmb, 0, 0, imagecolorallocate($_tmb, $r, $g, $b));
					}
					else
					{
						imagealphablending($_img, false);
						imagesavealpha($_img, true);
					}

					if ( !imagecopyresampled($_tmb, $_img, 0, 0, 0, 0, $newwidth, $newheight, $x_orig, $y_orig) )
					{
						return false;
					}
				}
				else
				{
					list($x, $y, $size) = $this->_cropPos($s[ 0 ], $s[ 1 ]);
					if ( !imagecopyresampled($_tmb, $_img, 0, 0, $x, $y, $newwidth, $newheight, $x_orig, $y_orig) )
					{
						return false;
					}
				}

				$r = imagepng($_tmb, $tmb, 7);
				imagedestroy($_img);
				imagedestroy($_tmb);

				return $r;
				break;
		}
	}

}
