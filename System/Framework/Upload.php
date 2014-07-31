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
 * @file         Upload.php
 */
class Upload
{

	/**
	 * @var bool
	 */
	public $success = false;

	/**
	 * @var bool|string
	 */
	public $path = false;

	/**
	 * @var bool|string
	 */
	public $error = false;

	/**
	 * enable or disable xxs validation
	 * default is true (enabled)
	 * @var bool
	 */
	public $checkXXS = true;


	/**
	 * enable or disable dot file upload
	 * default is false (disabled)
	 * @var bool
	 */
	public $allowDotFiles = false;

	protected $uploadPath = '';

	protected $uploadFormName = '';

	protected $errors = array ();

	protected $maxuploadsize = false;

	protected $allowedattachmentextensions = false;


	/**
	 * @param string      $upload_path
	 * @param string      $formName
	 * @param bool|int    $maxuploadBytes
	 * @param bool|string $allowedattachmentextensions Example: *.gif,*.jpg ... or *.*
	 */
	public function __construct ( $upload_path = '', $formName, $maxuploadBytes = false, $allowedattachmentextensions = false )
	{

		$this->uploadPath     = $upload_path;
		$this->uploadFormName = $formName;
		$this->maxuploadsize  = $maxuploadBytes;


		if ( is_string($allowedattachmentextensions) && strpos($allowedattachmentextensions, '*.*') === false )
		{
			$exts                              = explode(',', str_replace(array ( '*.*', '*.' ), array ( '', '' ), str_replace(' ', '', strtolower($allowedattachmentextensions))));
			$this->allowedattachmentextensions = $exts;
		}
		else if ( is_array($allowedattachmentextensions) )
		{
			foreach ( $allowedattachmentextensions as $r )
			{
				if ( !trim($r) )
				{
					continue;
				}

				if ( strpos($r, '*.*') !== false )
				{
					$this->allowedattachmentextensions = false;
					break;
				}
				else
				{
					$this->allowedattachmentextensions[ ] = str_replace('*.', '', str_replace(' ', '', strtolower($r)));
				}
			}
		}

		// will allow all file extensions
		if ( is_string($allowedattachmentextensions) && strpos($allowedattachmentextensions, '*.*') !== false )
		{
			$this->allowedattachmentextensions = false;
		}

		$this->errors = array ();
	}

	/**
	 * @param string $id
	 * @return string
	 */
	protected function getServerVar ( $id )
	{
		return isset( $_SERVER[ $id ] ) ? $_SERVER[ $id ] : '';
	}

    /**
     * @param  $callback
     * @param bool $tempUpload
     * @throws BaseException
     * @return bool
     */
	public function execute ( $callback, $tempUpload = false )
	{
        if ( !is_callable($callback) )
        {
            throw new BaseException('Invalid upload Callback function!');
        }



		// where to save the upload?
		$savePath = ( $this->uploadPath ? $this->uploadPath : UPLOAD_PATH );
		Library::makeDirectory($savePath);


		if ( !is_writable($savePath) )
		{
			$this->error   = sprintf(trans('Der Pfad %s ist nicht schreibbar! Code:' . __LINE__), $savePath);
			$this->success = false;

			return false;
		}


		$upload = isset( $_FILES[ $this->uploadFormName ] ) ? $_FILES[ $this->uploadFormName ] : null;
		// Parse the Content-Disposition header, if available:
		$file_name = $this->getServerVar('HTTP_CONTENT_DISPOSITION') ? rawurldecode(preg_replace('/(^[^"]+")|("$)/', '', $this->getServerVar('HTTP_CONTENT_DISPOSITION'))) : null;

		// Parse the Content-Range header, which has the following form:
		// Content-Range: bytes 0-524287/2000000
		$content_range = $this->getServerVar('HTTP_CONTENT_RANGE') ? preg_split('/[^0-9]+/', $this->getServerVar('HTTP_CONTENT_RANGE')) : null;
		$size          = $content_range ? $content_range[ 3 ] : null;


		$this->path = null;

		if ( $upload && is_array($upload[ 'tmp_name' ]) )
		{

			// param_name is an array identifier like "files[]",
			// $_FILES is a multi-dimensional array:
			foreach ( $upload[ 'tmp_name' ] as $index => $value )
			{
				$name     = $file_name ? $file_name : $upload[ 'name' ][ $index ];
				$temp     = $upload[ 'tmp_name' ][ $index ];
				$size     = ( $size ? $size : $upload[ 'size' ][ $index ] );
				$filetype = isset( $upload[ 'type' ][ $index ] ) ? $upload[ 'type' ][ $index ] : $this->getServerVar('CONTENT_TYPE');

				// Fix for overflowing signed 32 bit integers,
				// works for sizes up to 2^32-1 bytes (4 GiB - 1):
				$size = $this->fixIntegerOverflow(intval($size));

				if ( $size === null )
				{
					$this->errors[ ] = trans('Fehlerhafte Upload-Datei!');
					$this->success   = false;

                   // if ( is_callable($callback) )
                  //  {
                        call_user_func_array($callback, array (
                                array (
                                    'success'        => false,
                                    'error'          => trans('Fehlerhafte Upload-Datei!')
                                ),
                                $this
                            )
                        );
                   // }


				}
				else
				{
					$name      = Library::sanitizeFilename($name);
					$extension = Library::getExtension($name);
					$isImage   = Library::canGraphic($name);

					$fupload_name = strtolower($name);
					$fupload_name = str_replace(" ", "", $fupload_name);

					if ( $this->_executeUpload(isset( $upload[ 'tmp_name' ] ) ? $upload[ 'tmp_name' ] : null, $file_name ? $file_name : ( isset( $upload[ 'name' ] ) ? $upload[ 'name' ] : null ), $size ? $size : $size, $filetype, isset( $upload[ 'error' ] ) ? $upload[ 'error' ] :
						null, null, $content_range, $tempUpload)
					)
					{
                        $mime = Library::getMimeType($this->path);

					//	if ( is_callable($callback) )
					//	{
                            call_user_func_array($callback, array (
							                array (
								                'filesize'       => $size,
								                'filetype'       => $filetype,
								                'filemime'       => $mime,
								                'filename'       => $name,
								                'fileextension'  => $extension,
								                'isimage'        => $isImage,
								                'uploadFilename' => $fupload_name,
								                'filepath'       => $this->path,
								                'success'        => $this->success
							                ),
							                $this
							          ));
					//	}
					}
					else
					{
                        $this->errors[ ] = $this->error;
                        $this->success   = false;
					//	if ( is_callable($callback) )
					//	{
                            call_user_func_array($callback, array (
							                array (
								                'filesize'       => $size,
								                'filetype'       => $filetype,
								                'filename'       => $name,
								                'fileextension'  => $extension,
								                'isimage'        => $isImage,
								                'uploadFilename' => $fupload_name,
								                'filepath'       => $this->path,
								                'success'        => false,
								                'error'          => $this->getError()
							                ),
							                $this
							          )
                            );
					//	}
					//	else
					//	{
							$this->errors[ ] = $this->error;
							$this->success   = false;
					//	}
					}

				}
			}
		}
		else
		{



			$name     = $file_name ? $file_name : ( isset( $upload[ 'name' ] ) ? $upload[ 'name' ] : null );
			$temp     = isset( $upload[ 'tmp_name' ] ) ? $upload[ 'tmp_name' ] : null;
			$size     = $size ? $size : ( isset( $upload[ 'size' ] ) ? $upload[ 'size' ] : $this->getServerVar('CONTENT_LENGTH') );
			$filetype = isset( $upload[ 'type' ] ) ? $upload[ 'type' ] : $this->getServerVar('CONTENT_TYPE');


			// Fix for overflowing signed 32 bit integers,
			// works for sizes up to 2^32-1 bytes (4 GiB - 1):
			$size = $this->fixIntegerOverflow(intval(( isset( $upload[ 'size' ] ) ? $upload[ 'size' ] : $this->get_server_var('CONTENT_LENGTH') )));


			if ( $name === null || $size === null )
			{
				$this->error   = trans('Fehlerhafte Upload-Datei!');
				$this->success = false;

              //  if ( is_callable($callback) )
             //   {
                    call_user_func_array($callback, array (
                            array (
                                'success'        => false,
                                'error'          => trans('Fehlerhafte Upload-Datei!')
                            ),
                            $this
                        )
                    );
             //   }

				return false;
			}

			$name      = Library::sanitizeFilename($name);
			$extension = Library::getExtension($name);
			$isImage   = Library::canGraphic($name);

			$fupload_name = strtolower($name);
			$fupload_name = str_replace(" ", "", $fupload_name);

			// $_FILES is a one-dimensional array:
			if ( $this->_executeUpload(isset( $upload[ 'tmp_name' ] ) ? $upload[ 'tmp_name' ] : null, $file_name ? $file_name : ( isset( $upload[ 'name' ] ) ? $upload[ 'name' ] : null ), $size ? $size : $size, $filetype, isset( $upload[ 'error' ] ) ? $upload[ 'error' ] :
				null, null, $content_range, $tempUpload)
			)
			{

				$mime = Library::getMimeType($this->path);

			//	if ( is_callable($callback) )
			//	{
                    call_user_func_array($callback, array (
					                                      array (
						                                      'filesize'       => $size,
						                                      'filetype'       => $filetype,
						                                      'filemime'       => $mime,
						                                      'filename'       => $name,
						                                      'fileextension'  => $extension,
						                                      'isimage'        => $isImage,
						                                      'uploadFilename' => $fupload_name,
						                                      'filepath'       => $this->path,
						                                      'success'        => true
					                                      ),
					                                      $this
					                                ));


			//	}

			}
			else
			{
                $this->errors[ ] = $this->error;
                $this->success   = false;
			//	if ( is_callable($callback) )
			//	{
                    call_user_func_array($callback, array (
					                                      array (
						                                      'filesize'       => $size,
						                                      'filetype'       => $filetype,
						                                      'filename'       => $name,
						                                      'fileextension'  => $extension,
						                                      'isimage'        => $isImage,
						                                      'uploadFilename' => $fupload_name,
						                                      'filepath'       => $this->path,
						                                      'success'        => false,
						                                      'error'          => $this->getError()
					                                      ),
					                                      $this
					                                ));

			//	}
			//	else
			//	{
					$this->errors[ ] = $this->error;
					$this->success   = false;

					return false;
			//	}
			}
		}
	}


	/**
	 * Fix for overflowing signed 32 bit integers,
	 * works for sizes up to 2^32-1 bytes (4 GiB - 1)
	 *
	 * @param int $size
	 * @return float
	 */
	protected function fixIntegerOverflow ( $size )
	{

		if ( $size < 0 )
		{
			$size += 2.0 * ( PHP_INT_MAX + 1 );
		}

		return $size;
	}

	/**
	 * @param      $uploaded_file
	 * @param      $name
	 * @param      $size
	 * @param      $type
	 * @param      $error
	 * @param null $index
	 * @param null $content_range
	 * @param bool $tempUpload
	 * @return bool
	 */
	protected function _executeUpload ( $uploaded_file, $name, $size, $type, $error, $index = null, $content_range = null, $tempUpload = false )
	{

		if ( empty( $name ) || is_null($name) )
		{
			$this->error   = trans('Es wurde keine Datei zum hochgeladen übergeben.');
			$this->success = false;

			return false;
		}

		if ( strpos($name, '.') === false || (substr($name, 0, 1) == '.' && !$this->allowDotFiles ) )
		{
			$this->error   = trans('Unbekannter Dateityp.');
			$this->success = false;

			return false;
		}


		if ( strpos($name, '.') !== false && substr($name, 0, 1) !== '.' && is_array($this->allowedattachmentextensions) && count($this->allowedattachmentextensions) )
		{
			$extension = Library::getExtension($name);

			if ( !in_array($extension, $this->allowedattachmentextensions) )
			{
				$this->error   = trans('Nicht erlaubter Dateityp.');
				$this->success = false;

				return false;
			}
		}


		if ( $this->maxuploadsize )
		{
			if ( $size && $size > $this->maxuploadsize )
			{
				$this->error   = trans('Die Datei ist zu groß.');
				$this->success = false;

				return false;
			}
		}
		else
		{
			if ( $size > Library::getMaxUploadSize(true) )
			{
				$this->error   = trans('Die Datei ist zu groß.');
				$this->success = false;

				return false;
			}
		}


		$savePath = $this->uploadPath;

		if ( substr($savePath, -1) != '/' && $this->uploadPath )
		{
			$savePath .= '/';
		}

		$filename = Library::sanitizeFilename($name);
		$ext      = Library::getExtension($name);

		if ( $filename == '.' . $ext )
		{
			$filename = md5($name) . '.' . $ext;
		}

		if ( !ini_get('safe_mode') )
		{
			if ( $name[ 0 ] == '.' && $this->uploadPath == '' )
			{
				$savePath .= 'dotfiles/';
			}
			elseif ( $tempUpload && $this->uploadPath == '' )
			{
				$savePath .= 'temp/';
			}
			else
			{
				if ( $this->uploadPath == '' )
				{
					$savePath .= strtolower($name[ 0 ]) . '/';
				}
			}

			if ( !file_exists($savePath) )
			{
				$old_umask = umask(0);

				Library::makeDirectory($savePath);
				@chmod($savePath, 0777);
			}
		}
		else
		{
			if ( $tempUpload && $this->uploadPath == '' )
			{
				$savePath .= 'temp/';
				Library::makeDirectory($savePath);
				@chmod($savePath, 0777);

				if ( !file_exists($savePath) || !is_writable($savePath) )
				{
					$this->error   = sprintf(trans('Der Pfad %s ist nicht schreibbar! Code:' . __LINE__), $savePath);
					$this->success = false;

					return false;
				}
			}
		}

		$savePath .= $filename;

		//$savePath .= Library::sanitizeFilename($upload['name']);
		// is there already a file with the same name?
		while ( file_exists($savePath) )
		{
			$savePath = Library::incrementFileName($savePath);
		}

		if ( !empty( $error ) )
		{
			switch ( $error )
			{
				case 1 :

					$this->error = trans('Die hochgeladene Datei überschreitet die in der Anweisung upload_max_filesize in php.ini festgelegte Größe.');
					break;
				case 2 :
					$this->error = trans('Die hochgeladene Datei überschreitet die in dem Formular mittels der Anweisung MAX_FILE_SIZE angegebene maximale Dateigröße.');
					break;
				case 3 :
					$this->error = trans('Die Datei wurde nur teilweise hochgeladen.');
					break;
				case 4 :
					$this->error = trans('Es wurde keine Datei hochgeladen.');
					break;
				case 6 :
					$this->error = 'upload.error.no.temp.folder';
					break;
				case 7 :
					$this->error = 'upload.error.disk.write.failed';
					break;
				case 8 :
					$this->error = 'upload.error.extension.aborted';
					break;
				default :
					$this->error = 'upload.error.default';
					break;
			}


			if ( !$this->error )
			{
				$this->error = sprintf(trans('Upload Error: %s'), $error);
			}
			$this->success = false;

			return false;
		}


		// is the file actually an uploaded file?
		if ( is_uploaded_file($uploaded_file) !== true )
		{
			$this->error   = 'upload.error.not.uploaded.file ' . $uploaded_file;
			$this->success = false;

			return false;
		}


		// move the file
		if ( !move_uploaded_file($uploaded_file, $savePath) )
		{
			$this->error   = 'upload.error.not.moved';
			$this->success = false;

			return false;
		}


		if ( !file_exists($savePath) )
		{
			$this->error   = 'upload.error.not.moved';
			$this->success = false;

			return false;
		}


		//   Library::disableErrorHandling();
		//   chmod( $savePath, 0777 );
		//   Library::enableErrorHandling();

		if ( !file_exists($savePath) )
		{
			$this->error   = 'upload.error.not.moved';
			$this->success = false;

			return false;
		}

		$this->path = $savePath;


		if ( $this->checkXXS )
		{
			if ( !$this->_checkXSS() )
			{
				$this->success = false;

				return false;
			}
			else
			{
				$this->success = true;
			}
		}
		else
		{
			$this->success = true;
			$this->error   = false;
		}

		return true;
	}


	/**
	 *
	 * @param string $filename
	 * @return boolean
	 */
	public function is_uploaded_file ( $filename )
	{

		if ( !$tmp_file = get_cfg_var('upload_tmp_dir') )
		{
			$tmp_file = dirname(tempnam('', ''));
		}
		$tmp_file .= '/' . basename($filename);

		/* Der Benutzer könnte einen führenden Slash in php.ini haben... */

		return ( preg_replace('#/+#', '/', $tmp_file) == $filename );
	}

	/**
	 * @return bool
	 */
	protected function _checkXSS ()
	{

		$fh         = fopen($this->path, 'rb');
		$file_check = fread($fh, 512);
		fclose($fh);

		if ( !$file_check )
		{
			@unlink($this->path);
			$this->error = 'XXS Error';

			return false;
		}
		else if ( preg_match('#(<script|<html|<head|<title|<body|<pre|<table|<a\s+href|<img|<plaintext|<cross\-domain\-policy)(\s|=|>)#si', $file_check) )
		{
			@unlink($this->path);
			$this->error = 'XXS Error';

			return false;
		}
		else if ( strpos($file_check, '<' . '?php') !== false || strpos($file_check, 'eval') !== false || strpos($file_check, 'base64_decode') !== false )
		{
			@unlink($this->path);
			$this->error = 'XXS Error';

			return false;
		}

		return true;
	}

	/**
	 *
	 * @return boolean
	 */
	public function success ()
	{

		return ( count($this->errors) ? false : $this->success );
	}

	/**
	 *
	 * @return string
	 */
	public function getPath ()
	{

		return $this->path;
	}

	/**
	 *
	 * @return string
	 */
	public function getRelativePath ()
	{

		return str_replace(UPLOAD_PATH, '', $this->path);
	}

	/**
	 * get the last error
	 *
	 * @return string
	 */
	public function getError ()
	{

		return count($this->errors) > 1 ? $this->errors : $this->error;
	}

	/**
	 * get all errors
	 *
	 * @return array
	 */
	public function getErrors ()
	{

		return $this->errors;
	}

	/**
	 *
	 */
	public function delete ()
	{

		if ( $this->path )
		{
			unlink($this->path);
		}
	}

}
