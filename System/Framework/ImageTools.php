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
 * @file         ImageTools.php
 */
if ( IMAGE_LIBRARY === 'gd' && !extension_loaded('gd') )
{
	Error::raise('The installed PHP Version has no GD support! Please contact your server administrator.');
}

/**
 * Class ImageException
 */
class ImageException extends Exception
{

	/**
	 * @param $message
	 */
	public function _construct ( $message )
	{

		Error::raise($message);
	}

}

/**
 * Class UnsupportedImageException
 */
class UnsupportedImageException extends ImageException
{

	/**
	 * @param $message
	 */
	public function _construct ( $message )
	{

		Error::raise($message);
	}

}

/**
 * Class NotAnImageException
 */
class NotAnImageException extends ImageException
{

	/**
	 * @param $message
	 */
	public function _construct ( $message )
	{

		Error::raise($message);
	}

}

/**
 * Class ImageNotFoundException
 */
class ImageNotFoundException extends ImageException
{

	/**
	 * @param $message
	 */
	public function _construct ( $message )
	{

		Error::raise($message);
	}

}

/**
 * Class ImageTools
 */
class ImageTools
{

	/**
	 * @var null
	 */
	private static $cachedir = null;

	/**
	 * @var
	 */
	private static $isExt;

	/**
	 * @var bool
	 */
	public static $callback = false;

	/**
	 * @param string $cachedir
	 * @return mixed
	 */
	public static function create ( $cachedir = '' )
	{

		Library::enableErrorHandling();

		// ini_set( 'gd.jpeg_ignore_warning', 1 );
		// ignore warning: imagecreatefromjpeg() : gd-jpeg, libjpeg: recoverable error: Premature end of JPEG
		// Patch by: http://worcesterwideweb.com/2008/03/17/php-5-and-imagecreatefromjpeg-recoverable-error-premature-end-of-jpeg-file/

		if ( IMAGE_LIBRARY == 'imagick' )
		{
			$className = 'ImagickImage';
			$file      = 'class_imagickimage.php';
		}
		else
		{
			$className = 'GDImage';
			$file      = 'class_gdimage.php';
		}

		if ( $cachedir != '' )
		{
			if ( substr($cachedir, -1) != '/' )
			{
				$cachedir .= '/';
			}

			self::$cachedir = $cachedir;
		}

		self::$isExt = ( _OS == "W" ? ".exe" : "" );

		if ( !class_exists($className, false) )
		{
			include_once LIBRARY_PATH . 'imglib/' . $file;
		}

		return new $className();
	}

	/**
	 * @return null
	 */
	public static function getCachepath ()
	{

		return self::$cachedir;
	}

	/**
	 * @param        $w
	 * @param        $h
	 * @param string $bg
	 * @return Imagick
	 */
	public static function getNewImage ( $w, $h, $bg = "none" )
	{

		if ( IMAGE_LIBRARY == 'imagick' )
		{
			$image = new Imagick();
			$image->newImage($w, $h, $bg);

			return $image;
		}
		else
		{
			$imageFunction = ( function_exists("imagecreatetruecolor") ) ? "imagecreatetruecolor" : "imagecreate";
			$w             = floor($w);
			$h             = floor($h);
			$w             = $w > 0 ? $w : 1;
			$h             = $h > 0 ? $h : 1;

			$dst_im = $imageFunction($w, $h);

			return $dst_im;
		}
	}

	/**
	 * @param $transformation
	 * @return bool|string
	 * @throws ImageException
	 */
	public static function loadTransformer ( $transformation )
	{

		$className = 'ImageTransformation' . ucfirst($transformation);
		$path      = LIBRARY_PATH . 'imglib/transformers/' . IMAGE_LIBRARY . '/class.' . strtolower($transformation) . '.php';

		if ( is_file($path) )
		{
			include_once $path;
			if ( !class_exists($className, false) )
			{
				throw new ImageException( 'Transformation `' . ucfirst($transformation) . '` not found' );

				return false;
			}
		}
		else
		{
			throw new ImageException( 'Transformation `' . ucfirst($transformation) . '` not found' );

			return false;
		}

		return $className;
	}

	/**
	 * @param       $transformation
	 * @param       $img
	 * @param array $params
	 * @return mixed
	 */
	public static function runTransformation ( $transformation, $img, $params = array () )
	{

		$class = ImageTools::loadTransformer($transformation);

		return call_user_func_array(array (
		                                  $class,
		                                  'transform'
		                            ), array (
		                                     $img,
		                                     $params
		                               ));
	}

	/**
	 * @param $htmlcol
	 * @return array
	 */
	public static function htmlcolor2rgb ( $htmlcol )
	{

		$offset = 0;

		if ( substr($htmlcol, 0, 1) == '#' )
		{
			$offset = 1;
		}

		$r = hexdec(substr($htmlcol, $offset, 2));
		$g = hexdec(substr($htmlcol, $offset + 2, 2));
		$b = hexdec(substr($htmlcol, $offset + 4, 2));

		return array (
			$r,
			$g,
			$b
		);
	}

	/**
	 * @param $color
	 * @return string
	 */
	public static function checkColor ( $color )
	{

		if ( $color{0} !== '#' )
		{
			$color = '#' . $color;
		}

		return $color;
	}

	/**
	 * @param $glue
	 * @param $pieces
	 * @return string
	 */
	public static function implode ( $glue, $pieces )
	{

		if ( empty( $pieces ) )
		{
			return 'empty';
		}
		foreach ( $pieces as $r_pieces )
		{
			if ( is_array($r_pieces) )
			{
				$retVal[ ] = self::implode($glue, $r_pieces);
			}
			else
			{
				$retVal[ ] = $r_pieces;
			}
		}

		return implode($glue, $retVal);
	}

	/**
	 * @param bool $with_params
	 * @return array
	 */
	public static function getTransformations ( $with_params = false )
	{

		$folder          = LIBRARY_PATH . 'imglib/transformers/' . IMAGE_LIBRARY . '/';
		$files           = glob($folder . 'class.*.php');
		$transformations = array ();
		foreach ( $files as $file )
		{
			$name  = basename($file);
			$name  = str_replace('class.', '', $name);
			$name  = str_replace('.php', '', $name);
			$class = 'ImageTransformation' . ucfirst($name);
			if ( !class_exists($class, false) )
			{
				include_once $file;
			}
			if ( $with_params )
			{
				if ( method_exists($class, 'getParameters') )
				{
					$params = call_user_func(array (
					                               $class,
					                               'getParameters'
					                         ));
				}
				else
				{
					$params = array ();
				}

				$transformations[ $name ] = $params;
			}
			else
			{
				$label = $name;
				if ( method_exists($class, 'getTitle') )
				{
					$label = call_user_func(array (
					                              $class,
					                              'getTitle'
					                        ));
				}

				$transformations[ $name ] = $label;
			}
		}

		return $transformations;
	}

	/**
	 * @param $name
	 * @return array|mixed
	 */
	public static function getParameters ( $name )
	{

		$file  = LIBRARY_PATH . 'imglib/transformers/' . IMAGE_LIBRARY . '/class.' . $name . '.php';
		$class = 'ImageTransformation' . ucfirst($name);
		if ( !class_exists($class, false) )
		{
			include_once $file;
		}
		if ( method_exists($class, 'getParameters') )
		{
			$params = call_user_func(array (
			                               $class,
			                               'getParameters'
			                         ));
		}
		else
		{
			$params = array ();
		}

		return $params;
	}

	// this function can be overridden if needed. Requires ROOT_PATH and BASE to be defined.
	/**
	 * @param $data
	 * @return mixed
	 */
	public static function formatPath ( $data )
	{

		$data[ 'path' ] = str_replace(ROOT_PATH, '', $data[ 'path' ]);

		return $data;
	}

}

/**
 * Class ImageTransformer
 */
class ImageTransformer
{

	/**
	 *
	 * @var type
	 */
	protected $sourceImagePath;

	/**
	 *
	 * @var array
	 */
	protected $sourceImageData = array ();

	/**
	 *
	 * @var type
	 */
	protected $sourceImageType;

	/**
	 *
	 * @var string
	 */
	protected $outputImagePath;


	protected $disablePathPatch = false;


	/**
	 *
	 * @var string
	 */
	protected $outputImageName = null;

	/**
	 *
	 * @var type
	 */
	protected $outputImageType;

	/**
	 *
	 * @var type
	 */
	protected $outputImageQuality = 90;

	/**
	 *
	 * @var string
	 */
	protected $bgcolor = '#ffffff';

	/**
	 * @var null
	 */
	protected $verlay_color = null;

	/**
	 * @var null
	 */
	protected $overlay_percent = null;

	/**
	 * @var bool
	 */
	protected $cache = true;

	/**
	 * @var array
	 */
	protected $transformation = array ();

	/**
	 * @var bool
	 */
	protected $transformed = false;

	/**
	 * @var bool
	 */
	protected $callback = false;

	/**
	 * @var bool
	 */
	protected $debug = false;

	/**
	 * @var bool
	 */
	protected $image = false;

	/**
	 * @var bool
	 */
	protected $writeFile = true;

	/**
	 * @param      $src
	 * @param bool $outputType
	 * @param bool $transformation
	 * @return bool
	 */
	public function prepare ( $src, $outputType = false, $transformation = false )
	{

		if ( is_string($transformation) )
		{
			// get transformation?
		}

		if ( is_array($transformation) )
		{
			$this->transformation = $transformation;
		}

		if ( !file_exists($src) )
		{
			return false;
		}

		if ( !$this->loadSourceImage($src) )
		{
			return false;
		}

		$outputType = $outputType === 'jpg' ? 'jpeg' : $outputType;
		$outputType = $outputType === false ? $this->sourceImageType : $outputType;
		$outputType = strtolower($outputType);


		if ( !in_array($outputType, array (
		                                  'png',
		                                  'jpeg',
		                                  'gif'
		                            ))
		)
		{
			// throw new ImageException('Output format `' . $outputType . '` is not supported');
			$outputType = 'jpeg';
		}
		$this->outputImageType = $outputType;
		$this->outputImagePath = $this->getOutputImagePath();

		return true;
	}

	/**
	 * @param int $quality
	 */
	public function setQuality ( $quality = 80 )
	{

		$this->outputImageQuality = $quality;
	}

	/**
	 * @param $color
	 */
	public function setBackground ( $color )
	{

		$this->bgcolor = $color;
	}

	/**
	 * @param bool $write
	 */
	public function setWrite ( $write = true )
	{

		$this->writeFile = $write;
	}

	/**
	 * @param bool $cache
	 */
	public function setCaching ( $cache = true )
	{

		$this->cache = $cache;
	}

	/**
	 * @param null|string $name
	 */
	public function setOutputFilename ( $name = null )
	{

		$this->outputImageName = $name;
	}


	public function enablePathPatch ()
	{

		$this->disablePathPatch = false;
	}

	public function disablePathPatch ()
	{

		$this->disablePathPatch = true;
	}

	/**
	 * @param $callback
	 */
	public function setCallback ( $callback )
	{

		$this->callback = $callback;
	}

	/**
	 * @param bool $debug
	 */
	public function setDebug ( $debug = false )
	{

		$this->debug = $debug;
	}

	/**
	 * @return type
	 */
	public function getOutputType ()
	{

		return $this->outputImageType;
	}

	/**
	 * @param bool $params
	 * @return mixed
	 */
	public function getImageData ( $params = false )
	{

		if ( $params !== false )
		{
			$source = !empty( $params[ 'source' ] ) ? $params[ 'source' ] : false;
			$type   = !empty( $params[ 'output' ] ) ? $params[ 'output' ] : false;
			$chain  = !empty( $params[ 'chain' ] ) ? $params[ 'chain' ] : false;

			if ( isset( $params[ 'cache' ] ) )
			{
				$this->setCaching($params[ 'cache' ]);
			}

			if ( isset( $params[ 'write' ] ) )
			{
				$this->setWrite($params[ 'write' ]);
			}

			if ( isset( $params[ 'quality' ] ) )
			{
				$this->setQuality($params[ 'quality' ]);
			}

			if ( isset( $params[ 'callback' ] ) )
			{
				$this->setCallback($params[ 'callback' ]);
			}

			if ( isset( $params[ 'bgcolor' ] ) )
			{
				$this->setBackground($params[ 'bgcolor' ]);
			}

			$valid = $this->prepare($source, $type, $chain);

			if (!$valid)
			{
				return false;
			}
		}


		if ( !$this->imageIsTransformed() )
		{
			$this->transformImage();
		}


		if ( $this->writeFile && is_file($this->outputImagePath) )
		{
			$data             = @getimagesize($this->outputImagePath);
			$data[ 'width' ]  = $data[ 0 ];
			$data[ 'height' ] = $data[ 1 ];
			unset( $data[ 0 ], $data[ 1 ], $data[ 2 ], $data[ 3 ], $data[ 'channels' ], $data[ 'bits' ] );
			$data[ 'path' ] = $this->outputImagePath;
			$data[ 'size' ] = filesize($data[ 'path' ]);
		}
		else
		{
			$data             = array ();
			$data             = @getimagesize($this->outputImagePath);
			$data[ 'width' ]  = $data[ 0 ];
			$data[ 'height' ] = $data[ 1 ];
			unset( $data[ 0 ], $data[ 1 ], $data[ 2 ], $data[ 3 ], $data[ 'channels' ], $data[ 'bits' ] );
			$data[ 'path' ] = $this->outputImagePath;
			$data[ 'size' ] = filesize($data[ 'path' ]);

		}

		$data[ 'library' ]   = IMAGE_LIBRARY;
		$data[ 'fromcache' ] = $this->image !== false ? 'no' : 'yes';

		if ( $this->callback !== false )
		{
			$data = call_user_func_array($this->callback, array (
			                                                    $data
			                                              ));
		}
		else
		{
			$data = call_user_func_array(array (
			                                   'ImageTools',
			                                   'formatPath'
			                             ), array (
			                                      $data
			                                ));
		}

		if ( $this->debug === true && IMAGE_LIBRARY == 'imagick' )
		{
			if ( $this->image !== false )
			{
				$data[ 'extra' ] = $this->image->identifyImage();
			}
			else
			{
				$image           = new Imagick( $this->outputImagePath );
				$data[ 'extra' ] = $image->identifyImage();
			}
		}

		return $data;
	}

	/**
	 * @param $params
	 * @return mixed
	 */
	public function process ( $params )
	{

		return $this->getImageData($params);
	}

    /**
     * @param $path
     * @throws NotAnImageException
     * @throws UnsupportedImageException
     * @throws ImageNotFoundException
     * @return bool
     */
	protected function loadSourceImage ( $path )
	{

		if ( file_exists($path) )
		{

			$this->sourceImagePath = $path;
			$this->sourceImageData = @getimagesize($this->sourceImagePath);

			if ( isset( $this->sourceImageData[ 'mime' ] ) )
			{
				switch ( $this->sourceImageData[ 'mime' ] )
				{
					case 'image/jpeg' :
					case 'image/jpg' :
						$this->sourceImageType = 'jpeg';

						return true;
						break;
					case 'image/png' :
						$this->sourceImageType = 'png';

						return true;
						break;
					case 'image/gif' :
						$this->sourceImageType = 'gif';

						return true;
						break;
					case 'image/bmp' :
						if ( IMAGE_LIBRARY != 'imagick' )
						{
							throw new UnsupportedImageException( 'Images of type `' . $this->sourceImageData[ 'mime' ] . '` cannot be modified using the Image class' );
						}
						$this->sourceImageType = 'bmp';

						return true;
						break;
					case 'image/tiff' :
						if ( IMAGE_LIBRARY != 'imagick' )
						{
							throw new UnsupportedImageException( 'Images of type `' . $this->sourceImageData[ 'mime' ] . '` cannot be modified using the Image class' );
						}
						$this->sourceImageType = 'tiff';

						return true;
						break;
					case 'image/psd' :
						if ( IMAGE_LIBRARY != 'imagick' )
						{
							throw new UnsupportedImageException( 'Images of type `' . $this->sourceImageData[ 'mime' ] . '` cannot be modified using the Image class' );
						}
						$this->sourceImageType = 'psd';

						return true;
						break;
					default :
						throw new UnsupportedImageException( 'Images of type `' . $this->sourceImageData[ 'mime' ] . '` cannot be modified using the Image class' );
				}
			}
			else
			{
				return false;
				 throw new NotAnImageException('File `' . $path . '` cannot be processed by the Image class! ' . print_r($this->sourceImageData, true));
			}
		}
		else
		{return true;
			throw new ImageNotFoundException( 'File `' . $path . '` does not exist' );
		}
	}

	/**
	 * @return bool
	 */
	protected function imageIsTransformed ()
	{

		if ( $this->cache && is_file($this->outputImagePath) )
		{
			$this->transformed = true;
		}
		else
		{
			$this->transformed = false;
		}

		return $this->transformed;
	}

	// this function can be overridden if needed. Requires ROOT_PATH and BASE to be defined.
	/**
	 * @param $path
	 */
	public function setOutputImagePath ( $path )
	{

		$name = $this->getOutputImageName();


		if ( substr($path, -1) != '/' )
		{
			$path .= '/';
		}


		$this->outputImagePath = $path . $name . '.' . $this->getOutputType();
	}

	/**
	 * @return string
	 */
	protected function getOutputImagePath ()
	{

		$name = (string)$this->getOutputImageName();

		$toThisPath = ImageTools::getCachepath();

		if ( is_null($toThisPath) )
		{
			if ( $this->disablePathPatch )
			{
				$old_umask = umask(0);
				Library::makeDirectory(IMAGE_CACHE);

				if ( ini_get('safe_mode') )
				{
					return IMAGE_CACHE . $name . '.' . $this->outputImageType;
				}
				else
				{
					Library::makeDirectory(IMAGE_CACHE . $name[ 0 ] . '/');

					return IMAGE_CACHE . $name[ 0 ] . '/' . $name . '.' . $this->outputImageType;
				}
			}
			else
			{
				if ( defined('ADM_SCRIPT') )
				{
					$old_umask = umask(0);
					Library::makeDirectory(IMAGE_CACHE . 'backend/');

					if ( ini_get('safe_mode') )
					{
						return IMAGE_CACHE . 'backend/' . $name . '.' . $this->outputImageType;
					}
					else
					{
						Library::makeDirectory(IMAGE_CACHE . 'backend/' . $name[ 0 ] . '/');

						return IMAGE_CACHE . 'backend/' . $name[ 0 ] . '/' . $name . '.' . $this->outputImageType;
					}
				}
				else
				{
					$old_umask = umask(0);
					Library::makeDirectory(IMAGE_CACHE . 'frontend/');

					if ( ini_get('safe_mode') )
					{
						return IMAGE_CACHE . 'frontend/' . $name . '.' . $this->outputImageType;
					}
					else
					{
						Library::makeDirectory(IMAGE_CACHE . 'frontend/' . $name[ 0 ] . '/');

						return IMAGE_CACHE . 'frontend/' . $name[ 0 ] . '/' . $name . '.' . $this->outputImageType;
					}
				}
			}
		}
		else
		{
			if ( substr($toThisPath, -1) != '/' )
			{
				$toThisPath = $toThisPath . '/';
			}


			if ( !$this->disablePathPatch )
			{
				if ( defined('ADM_SCRIPT') )
				{
					$toThisPath = $toThisPath . 'backend/';
				}
				else
				{
					$toThisPath = $toThisPath . 'frontend/';
				}
			}


			$old_umask = umask(0);

			Library::makeDirectory($toThisPath);

			if ( ini_get('safe_mode') )
			{
				return $toThisPath . $name . '.' . $this->outputImageType;
			}
			else
			{
				if ( !is_file($toThisPath . $name[ 0 ]) )
				{
					Library::makeDirectory($toThisPath . $name[ 0 ] . '/');
				}

				return $toThisPath . $name[ 0 ] . '/' . $name . '.' . $this->outputImageType;
			}
		}
	}

	/**
	 * @return string
	 */
	protected function getOutputImageName ()
	{

		if ( is_string($this->outputImageName) && $this->outputImageName )
		{
			return $this->outputImageName;
		}

		return md5($this->sourceImagePath . $this->outputImageType . $this->outputImageQuality . ImageTools::implode('', $this->transformation));
	}

	protected function transformImage ()
	{

		if ( is_null($this->sourceImagePath) )
		{
			throw new ImageException( 'Image class is not prepare()\'d' );
		}

		$this->image = $this->loadImage();

		if ( $this->image === false )
		{
			//return;
			#$this->image=$this->getErrorImage('Cannot load image');
			throw new ImageException( 'Cannot load image from:' . $this->sourceImagePath );
		}

		foreach ( $this->transformation as $transformation )
		{
			$this->image = $this->transform($this->image, $transformation);
		}

		if ( $this->writeFile || $this->cache )
		{
			$this->writeToCache();
		}
	}

	/**
	 * @param       $img
	 * @param array $transformation
	 * @return mixed
	 */
	protected function transform ( $img, $transformation = array () )
	{

		if ( isset( $transformation[ 0 ] ) )
		{
			$className = 'ImageTransformation' . ucfirst($transformation[ 0 ]);
			if ( !class_exists($className, false) )
			{
				if ( !ImageTools::loadTransformer($transformation[ 0 ]) )
				{
					return $img;
				}
			}

			$params = isset( $transformation[ 1 ] ) ? $transformation[ 1 ] : array ();

			return call_user_func_array(array (
			                                  $className,
			                                  'transform'
			                            ), array (
			                                     $img,
			                                     $params,
			                                     $this
			                               ));
		}

		return $img;
	}

}
