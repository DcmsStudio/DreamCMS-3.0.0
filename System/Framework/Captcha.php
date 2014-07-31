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
 * @package      DreamCMS
 * @version      3.0.0 Beta
 * @category     Framework
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Captcha.php
 */
class Captcha
{

	private static $image_width = 200;

	private static $image_height = 60;

	private static $num_lines = 4;

	private static $gdlinecolor;

	private static $im;

	private static $shadow_color;

	private static $captcha_string;

	private static $fonts;

	public static $audio_format = 'mp3';

	private static $audio_lang = 'de'; //'default'; //de-DE';

	public static $audio = null;

    protected static $_instance = null;

	/**
	 * Return the current object instance (Singleton)
	 *
	 * @return Captcha
	 */
	public static function getInstance ()
	{
		if ( self::$_instance === null )
		{
			self::$_instance = new Captcha;
		}

		return self::$_instance;
	}

	/**
	 * @param bool $difficult default is true
	 * @param string $hashName default is null
	 * @throws BaseException
	 */
	public static function generate ( $difficult = true, $hashName = null )
	{

        if (headers_sent()) {
            die('headers_sent');
        }


		if ( !function_exists('imagecreatetruecolor') )
		{
			throw new BaseException('Could not create Captcha! The GD v2 is not installed! Please install the GD-Lib before you can create a captcha.');
		}

		if ( $hashName === null )
		{
			throw new BaseException('Invalid Captcha hash!');
		}



        $_chars = Session::get('captcha_' . $hashName, false);

        if ( !$_chars )
        {
            throw new BaseException('Invalid Site Captcha');
        }



        $zlibOn = ini_get( 'zlib.output_compression' ) || ( ini_set( 'zlib.output_compression', 0 ) !== false );
        if ($zlibOn) {
            ini_get( 'zlib.output_compression', 0 );
        }
		#Library::disableErrorHandling();

		$noises = glob(DATA_PATH . 'captcha/background/*.png');
		shuffle($noises);
		$noise = $noises[ 0 ];

		self::$fonts = glob(DATA_PATH . 'captcha/fonts/*.ttf');

		$fontsizes = array (
			20,
			22,
			24
		);

		for ( $i = 0; $i < strlen($_chars); $i++ )
		{
			$char     = strtoupper($_chars[ $i ]);
			$rotation = self::_getgrad();
			$size     = $fontsizes[ rand(10, count($fontsizes)) ];
			$color    = self::_getcolor();

			$captcha_chars[ ] = array (
				'char'     => $char,
				'rotation' => $rotation,
				'size'     => $size,
				'color'    => $color,
				'y'        => rand(10, 50)
			);
		}


		self::$captcha_string = $_chars;
		self::$audio          = self::getAudibleCode('mp3', self::$captcha_string);

		// generate an image to work in
		self::$im = imagecreatetruecolor(self::$image_width, self::$image_height) or die( "Cannot Initialize new GD image stream" );
		imagealphablending(self::$im, true);
		imagecolorallocatealpha(self::$im, 255, 255, 255, 75);

		self::$gdlinecolor = imagecolorallocate(self::$im, 200, 220, 210);


		// create the background layer
		$noise_img = imagecreatefrompng($noise);

		$size = getimagesize($noise);

		imagecopyresampled(self::$im, $noise_img, 0, 0, 0, 0, self::$image_width, self::$image_height, $size[ 0 ], $size[ 1 ]);
		self::$shadow_color = imagecolorallocate(self::$im, 20, 20, 20);
		self::$gdlinecolor  = imagecolorallocate(self::$im, 200, 150, 200);
		self::drawLines();
		self::drawWord($captcha_chars);


		header("Content-type: image/png");
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

		imagepng(self::$im);
		imagedestroy(self::$im);

		exit;
	}

	/**
	 *
	 * @param array $params
	 * @return string
	 */
	public static function regenerate ( &$params )
	{

		$name = ( isset( $params[ 'name' ] ) && !empty( $params[ 'name' ] ) ? $params[ 'name' ] : null );
		$hash = $name !== null ? $name : Library::UUIDv4();

		$params[ 'name' ] = $hash;

		// generate a random string
		$chars = 'ABCDEFGHJKMNOPQRTYVWXY123456789';

		$captcha_string = '';
		for ( $i = 0; $i < 5; $i++ )
		{
			$captcha_string .= $chars[ rand(0, strlen($chars) - 1) ];
		}

		Session::save('captcha_' . $hash, $captcha_string);
		Session::save('site_captcha', $captcha_string);
        // Cookie::set('captcha-' . $hash, $captcha_string);

		return $captcha_string;
	}

	/**
	 *
	 * @param array  $params
	 * @param string $captchaCode
	 * @return string
	 */
	public static function getCaptcha ( $params, $captchaCode = null )
	{

		Library::enableErrorHandling();
		$width        = ( isset( $params[ 'width' ] ) && (int)$params[ 'width' ] > 0 ? $params[ 'width' ] : 200 );
		$height       = ( isset( $params[ 'height' ] ) && (int)$params[ 'height' ] > 0 ? $params[ 'height' ] : 80 );
		$name         = ( isset( $params[ 'name' ] ) && !empty( $params[ 'name' ] ) ? $params[ 'name' ] : null );
		$enableAudio  = ( isset( $params[ 'audio' ] ) ? (bool)$params[ 'audio' ] : true );
		$enableReload = ( isset( $params[ 'reloader' ] ) ? (bool)$params[ 'reloader' ] : true );
		$audio        = '';
		$reload       = '';
		$hash         = $name !== null ? $name : Library::UUIDv4();

		if ( $captchaCode === null )
		{
			$captchaCode = Session::get('site_captcha');
		}

		Session::save('captcha_' . $hash, $captchaCode);
        Session::write();



		if ( $enableAudio )
		{
			$bgColor  = ( isset( $params[ 'bgcolor' ] ) && !empty( $params[ 'bgcolor' ] ) ? $params[ 'bgcolor' ] : '#FFA1A1' );
			$htmlPath = Settings::get('portalurl') . '/' . HTML_URL;


			//Session::save('AudioCaptureParams', )
/*
			$audio = '
            <object type="application/x-shockwave-flash" width="18" height="18" id="SecurImage_as3">
                <param name="codebase" value="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" />
                <param name="classid" value="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" />
                <param name="allowScriptAccess" value="sameDomain" />
                <param name="allowFullScreen" value="false" />
                <param name="movie" value="' . $htmlPath . 'flash/captcha.swf?audio=main/captcha/audio/' . $hash . '&bgColor1=#FFA1A1&bgColor2=#FFF&iconColor=#000&roundedCorner=0" />
                <param name="quality" value="high" />
                <param name="audio" value="1" />
                <param name="bgcolor" value="#ffffff" />
                <embed src="' . urlencode($htmlPath . 'flash/captcha.swf?audio=' . Settings::get('portalurl') . '/main/captcha/audio/' . $hash . '&iconColor=#000&roundedCorner=0&bgColor1=#FFA1A1&bgColor2=#FFF') . '" quality="high" width="18" height="18" allowScriptAccess="sameDomain" allowFullScreen="false" pluginspage="http://www.macromedia.com/go/getflashplayer" />
            </object>
';

			*/

			$audio = '<a href="javascript:captchAudio(this, \'' . $hash . '\')" class="captcha-audio" data-toggle="' . $hash . '"><i></i></a>';
		}

		if ( $enableReload )
		{
			$skinData = User::loadSkin();
			$path     = SKIN_IMG_URL_PATH . $skinData[ 'img_dir' ] . '/img/';
			$reload   = ( $enableAudio ? '<br/>' : '' ) . '<a href="javascript:void(0)" onclick="reloadcaptch(\'' . $hash . '\')" class="captcha-reload"><i></i></a>';
		}


		return '
            <div class="captcha">
                <input type="hidden" name="_ch" value="' . $hash . '"/>
                <div class="captcha-image"><img id="' . $hash . '" class="captchaImage" alt="-" src="main/captcha/' . $hash . '" width="' . $width . '" height="' . $height . '" /></div>
                <div class="captcha-options">
                ' . $audio . $reload . '
                </div>
            </div>
';
	}


	/**
	 * @return array
	 */
	private static function _getcolor ()
	{

		$col_r = rand(70, 180);
		$col_g = rand(70, 180);
		$col_b = rand(70, 180);

		return array (
			$col_r,
			$col_g,
			$col_b
		);
	}

	/**
	 * @return int
	 */
	private static function _getgrad ()
	{
		$grad = rand(-20, 20);
		return $grad;
	}

	/**
	 * Draw random curvy lines over the image
	 *
	 * @access private
	 */
	private static function drawLines ()
	{

		imagealphablending(self::$im, true);
		imagesavealpha(self::$im, true);
		for ( $line = 0; $line < self::$num_lines; ++$line )
		{
			$x = self::$image_width * ( 1 + $line ) / ( self::$num_lines + 1 );
			$x += ( 0.5 - self::frand() ) * self::$image_width / self::$num_lines;
			$y = rand(self::$image_height * 0.1, self::$image_height * 0.9);

			$theta = ( self::frand() - 0.5 ) * M_PI * 0.7;
			$w     = self::$image_width;
			$len   = rand($w * 0.4, $w * 0.7);
			$lwid  = rand(0.5, 1.5);

			$k    = self::frand() * 0.6 + 0.2;
			$k    = $k * $k * 0.5;
			$phi  = self::frand() * 5.28;
			$step = 0.5;
			$dx   = $step * cos($theta);
			$dy   = $step * sin($theta);
			$n    = $len / $step;
			$amp  = 1.5 * self::frand() / ( $k + 5.0 / $len );
			$x0   = $x - 0.5 * $len * cos($theta);
			$y0   = $y - 0.5 * $len * sin($theta);

			$ldx = round(-$dy * $lwid);
			$ldy = round($dx * $lwid);

			for ( $i = 0; $i < $n; ++$i )
			{
				$x = $x0 + $i * $dx + $amp * $dy * sin($k * $i * $step + $phi);
				$y = $y0 + $i * $dy - $amp * $dx * sin($k * $i * $step + $phi);

				imagefilledrectangle(self::$im, $x, $y, $x + $lwid, $y + $lwid, self::$gdlinecolor);
			}
		}
	}

	/**
	 *
	 * @param array $captcha_chars
	 */
	private static function drawWord ( $captcha_chars = array() )
	{

		$font = self::$fonts[ rand(0, count(self::$fonts) - 1) ];

		$perturbation = 0.85;
		$iscale       = 3;

		$strlen = strlen(self::$captcha_string);

		$width2  = self::$image_width * $iscale;
		$height2 = self::$image_height * $iscale;


		// allocate bg color first for imagecreate
		$alpha = 50;


		$tmpimg = imagecreatetruecolor(self::$image_width * $iscale, self::$image_height * $iscale);


		imagesavealpha(self::$im, true);
		imagealphablending($tmpimg, false);


		imagepalettecopy(self::$im, $tmpimg);


		$font_size = $height2 * .35;
		$bb        = imagettfbbox($font_size, 0, $font, self::$captcha_string);
		$tx        = $bb[ 4 ] - $bb[ 0 ];
		$ty        = $bb[ 5 ] - $bb[ 1 ];
		$x         = round($width2 / 2 - $tx / 2 - $bb[ 0 ]);
		$y         = round($height2 / 2 - $ty / 2 - $bb[ 1 ]);


		foreach ( $captcha_chars as $char => $data )
		{
			$angle      = $data[ 'rotation' ];
			$_font_size = rand($font_size - 20, $font_size + 20);


			$font_color = imagecolorallocatealpha($tmpimg, $data[ 'color' ][ 0 ], $data[ 'color' ][ 1 ], $data[ 'color' ][ 2 ], $alpha);
			#$font_color = imagecolorallocate($tmpimg, $data['color'][0], $data['color'][1], $data['color'][2] );
			$y  = rand($y - 5, $y + 5);
			$ch = $data[ 'char' ];

			imagettftext($tmpimg, $_font_size, $angle, $x, $y, self::$shadow_color, $font, $ch);
			imagettftext($tmpimg, $_font_size, $angle, $x - 1, $y - 1, $font_color, $font, $ch);

			// estimate character widths to increment $x without creating spaces that are too large or too small
			// these are best estimates to align text but may vary between fonts
			// for optimal character widths, do not use multiple text colors or character angles and the complete string will be written by imagettftext
			if ( stripos('abcdeghknopqsuvxyz', $ch) !== false )
			{
				$min_x = $_font_size + ( $iscale * 2 );
				$max_x = $_font_size + ( $iscale * 5 );
			}
			else if ( stripos('ilI1', $ch) !== false )
			{
				$min_x = $_font_size / 5;
				$max_x = $_font_size / 3;
			}
			else if ( stripos('fjrt', $ch) !== false )
			{
				$min_x = $_font_size - ( $iscale * 3 );
				$max_x = $_font_size - ( $iscale * 12 );
			}
			else if ( stripos('wm', $ch) !== false )
			{
				$min_x = $_font_size;
				$max_x = $_font_size + ( $iscale * 2 );
			}
			else
			{ // numbers, capitals or unicode
				$min_x = $_font_size + ( $iscale * 2 );
				$max_x = $_font_size + ( $iscale * 6 );
			}

			$x += rand($min_x, $max_x);
		} //for loop


		self::distortedCopy($tmpimg);
	}

	/**
	 *
	 * @param imagecreatetruecolor $tmpimg
	 */
	private static function distortedCopy ( $tmpimg )
	{

		$perturbation = 0.45;
		$iscale       = 3;
		$numpoles     = 3; // distortion factor
		// make array of poles AKA attractor points
		for ( $i = 0; $i < $numpoles; ++$i )
		{
			$px[ $i ]  = rand(self::$image_width * 0.3, self::$image_width * 0.7);
			$py[ $i ]  = rand(self::$image_height * 0.3, self::$image_height * 0.7);
			$rad[ $i ] = rand(self::$image_width * 0.4, self::$image_width * 0.7);
			$tmp       = -self::frand() * 0.15 - 0.15;
			$amp[ $i ] = $perturbation * $tmp;
		}

		$bgCol   = imagecolorat($tmpimg, 0, 0);
		$width2  = $iscale * self::$image_width;
		$height2 = $iscale * self::$image_height;

		imagepalettecopy($tmpimg, self::$im); // copy palette to final image so text colors come across
		// loop over $img pixels, take pixels from $tmpimg with distortion field
		for ( $ix = 0; $ix < self::$image_width; ++$ix )
		{
			for ( $iy = 0; $iy < self::$image_height; ++$iy )
			{
				$x = $ix;
				$y = $iy;

				for ( $i = 0; $i < $numpoles; ++$i )
				{
					$dx = $ix - $px[ $i ];
					$dy = $iy - $py[ $i ];
					if ( $dx === 0 && $dy === 0 )
					{
						continue;
					}

					$r = sqrt($dx * $dx + $dy * $dy);
					if ( $r > $rad[ $i ] )
					{
						continue;
					}

					$rscale = $amp[ $i ] * sin(3.14 * $r / $rad[ $i ]);
					$x += $dx * $rscale;
					$y += $dy * $rscale;
				}

				$c = $bgCol;
				$x *= $iscale;
				$y *= $iscale;

				if ( $x >= 0 && $x < $width2 && $y >= 0 && $y < $height2 )
				{
					$c = imagecolorat($tmpimg, $x, $y);
				}

				if ( $c != $bgCol )
				{ // only copy pixels of letters to preserve any background image
					imagesetpixel(self::$im, $ix, $iy, $c);
				}
			}
		}
	}

	/**
	 *
	 * @return int
	 */
	private static function frand ()
	{
		return 0.0001 * rand(0, 22000);
	}

	/**
	 * @param string $hash
	 * @throws BaseException
	 */
	public static function outputAudioFile ( $hash )
	{
		$_chars = Session::get('captcha_' . $hash, false);
		if ( !$_chars )
		{
			$_chars = Session::get('site_captcha', false);
		}

		if ( !$_chars )
		{
			throw new BaseException( 'Invalid Captcha Hash for the audio mode!' );
		}

		$audio = self::getAudibleCode('mp3', $_chars);

		if ( !$audio )
		{
			self::$audio_format = 'wav';
			$audio              = self::getAudibleCode('wav', $_chars);
		}

        if (headers_sent()) {
            die('headers_sent');
        }

        $zlibOn = ini_get( 'zlib.output_compression' ) || ( ini_set( 'zlib.output_compression', 0 ) !== false );
        if ($zlibOn) {
            ini_get( 'zlib.output_compression', 0 );
        }


		if ( strtolower(self::$audio_format) === 'wav' )
		{
			header('Content-type: audio/x-wav');
			$ext = 'wav';
		}
		else
		{
			header('Content-type: audio/mpeg'); // default to mp3
			$ext = 'mp3';
		}



		header("Content-Disposition: attachment; filename=\"captcha-audio.{$ext}\"");
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Expires: Sun, 1 Jan 1997 12:00:00 GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT');
		header('Content-Length: ' . strlen($audio));

		echo $audio;

		exit;
	}

	/**
	 * Get WAV or MP3 file data of the spoken code.
	 * This is appropriate for output to the browser as audio/x-wav or audio/mpeg
	 *
	 * @since 1.0.1
	 * @param string $format
	 * @param null   $use
	 * @return string  WAV or MP3 data
	 */
	private static function getAudibleCode ( $format = 'mp3', $use = null )
	{

		$letters = array ();
		$code    = ( $use !== null ? $use : Session::get('site_captcha') );


		if ( CONTENT_TRANS && is_dir(DATA_PATH . 'captcha/audio/' . CONTENT_TRANS) )
		{
			self::$audio_lang = CONTENT_TRANS;
		}


		if ( $code === '' )
		{

			$filename = DATA_PATH . 'captcha/audio/' . self::$audio_lang . '/forbidden.' . strtolower($format);

			$fp   = fopen($filename, 'rb');
			$data = fread($fp, filesize($filename)); // read file in
			self::scrambleAudioData($data, 'mp3');
			$out_data = $data;
			fclose($fp);

			return $out_data;
		}

		for ( $i = 0; $i < strlen($code); ++$i )
		{
			$letters[ ] = $code{$i};
		}

		if ( $format === 'mp3' )
		{
			return self::generateMP3($letters);
		}
		else
		{
			return self::generateWAV($letters);
		}
	}


	/**
	 * Generate a wav file by concatenating individual files
	 *
	 * @param array $letters Array of letters to build a file from
	 * @return string WAV file data
	 * @throws BaseException
	 */
	private static function generateWAV ( $letters )
	{

		$data_len = 0;
		$files    = array ();
		$out_data = '';

		foreach ( $letters as $letter )
		{
			$filename = DATA_PATH . 'captcha/audio/' . self::$audio_lang . '/' . strtolower($letter) . '.wav';
			if ( !is_file($filename) )
			{
				$filename = DATA_PATH . 'captcha/audio/' . self::$audio_lang . '/' . strtoupper($letter) . '.wav';
			}

			if ( !is_readable($filename) )
			{
				throw new BaseException( sprintf('The captcha audio file "%s" is not readable!', $filename) );
			}

			$fp = fopen($filename, 'rb');

			$file = array ();

			$data = fread($fp, filesize($filename)); // read file in

			$header = substr($data, 0, 36);
			$body   = substr($data, 20);


			$data = unpack('NChunkID/VChunkSize/NFormat/NSubChunk1ID/VSubChunk1Size/vAudioFormat/vNumChannels/VSampleRate/VByteRate/vBlockAlign/vBitsPerSample', $header);

			$file[ 'sub_chunk1_id' ]   = $data[ 'SubChunk1ID' ];
			$file[ 'bits_per_sample' ] = $data[ 'BitsPerSample' ];
			$file[ 'channels' ]        = $data[ 'NumChannels' ];
			$file[ 'format' ]          = $data[ 'AudioFormat' ];
			$file[ 'sample_rate' ]     = $data[ 'SampleRate' ];
			$file[ 'size' ]            = $data[ 'ChunkSize' ] + 2;
			$file[ 'data' ]            = $body;

			if ( ( $p = strpos($file[ 'data' ], 'LIST') ) !== false )
			{
				// If the LIST data is not at the end of the file, this will probably break your sound file
				$info           = substr($file[ 'data' ], $p + 5, 10);
				$data           = unpack('Vlength/Vjunk', $info);
				$file[ 'data' ] = substr($file[ 'data' ], 0, $p);
				$file[ 'size' ] = $file[ 'size' ] - ( strlen($file[ 'data' ]) - $p );
			}

			$files[ ] = $file;
			$data     = null;
			$header   = null;
			$body     = null;

			$data_len += strlen($file[ 'data' ]);

			fclose($fp);
		}

		$out_data = '';
		for ( $i = 0; $i < sizeof($files); ++$i )
		{
			if ( $i == 0 )
			{ // output header
				$out_data .= pack('C4VC8', ord('R'), ord('I'), ord('F'), ord('F'), $data_len + 36, ord('W'), ord('A'), ord('V'), ord('E'), ord('f'), ord('m'), ord('t'), ord(' '));

				$out_data .= pack('VvvVVvv', 16, $files[ $i ][ 'format' ], $files[ $i ][ 'channels' ], $files[ $i ][ 'sample_rate' ], $files[ $i ][ 'sample_rate' ] * ( ( $files[ $i ][ 'bits_per_sample' ] * $files[ $i ][ 'channels' ] ) / 8 ), ( $files[ $i ][ 'bits_per_sample' ] * $files[ $i ][ 'channels' ] ) / 8, $files[ $i ][ 'bits_per_sample' ]);

				$out_data .= pack('C4', ord('d'), ord('a'), ord('t'), ord('a'));

				$out_data .= pack('V', $data_len);
			}

			$out_data .= $files[ $i ][ 'data' ];
		}

		self::scrambleAudioData($out_data, 'wav');

		return $out_data;
	}

	/**
	 * Randomly modify the audio data to scramble sound and prevent binary recognition.<br />
	 * Take care not to "break" the audio file by leaving the header data intact.
	 *
	 * @since  2.0
	 * @access private
	 * @param $data Sound data in mp3 of wav format
	 * @param $format
	 */

	private static function scrambleAudioData ( &$data, $format )
	{

		if ( $format === 'wav' )
		{
			$start = strpos($data, 'data') + 4; // look for "data" indicator
			if ( $start === false )
			{
				$start = 44; // if not found assume 44 byte header
			}
		}
		else
		{
			// mp3
			$start = 4; // 4 byte (32 bit) frame header
		}


		$start += rand(1, 64);
		//$start += $initstart; // randomize starting offset
		$datalen = strlen($data) - $start - 256; // leave last 256 bytes unchanged

		for ( $i = $start; $i < $datalen; $i += 64 )
		{
			$ch = ord($data{$i});
			if ( $ch < 64 || $ch > 119 )
			{
				continue;
			}

			$data{$i} = chr($ch + rand(-8, 8));
		}
	}


	/**
	 * Generate an mp3 file by concatenating individual files
	 *
	 * @param $letters $letters Array of letters to build a file from
	 * @return string MP3 file data
	 * @throws BaseException
	 */
	private static function generateMP3 ( $letters )
	{

		$data_len  = 0;
		$files     = array ();
		$out_data  = '';
		$out_data2 = '';
		$out_data3 = '';


		foreach ( $letters as $letter )
		{
			$filename = DATA_PATH . 'captcha/audio/' . self::$audio_lang . '/' . strtolower($letter) . '.mp3';
			if ( !is_file($filename) )
			{
				$filename = DATA_PATH . 'captcha/audio/' . self::$audio_lang . '/' . strtoupper($letter) . '.mp3';
			}

			if ( !is_readable($filename) )
			{
				throw new BaseException( sprintf('The captcha audio file "%s" is not readable!', $filename) );
			}

			$fp   = fopen($filename, 'rb');
			$data = fread($fp, filesize($filename)); // read file in
			fclose($fp);

			$out_data2 .= $data;
			unset( $data );
		}


		$filename = DATA_PATH . 'captcha/audio/finally.mp3';
		if ( is_file($filename) )
		{
			$fp   = fopen($filename, 'rb');
			$data = fread($fp, filesize($filename)); // read file in
			fclose($fp);


			$out_data3 .= $data;
			unset( $data );
		}


		self::scrambleAudioData($out_data2, 'mp3');

		return $out_data . $out_data2 . $out_data3;
	}

}
