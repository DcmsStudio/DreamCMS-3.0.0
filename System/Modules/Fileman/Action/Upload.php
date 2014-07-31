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
 * @file         Upload.php
 */
class Fileman_Action_Upload extends Fileman_Helper_Base
{

	/**
	 * @param $id
	 * @return string
	 */
	protected function get_server_var ( $id )
	{

		return isset($_SERVER[ $id ]) ? $_SERVER[ $id ] : '';
	}

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}
		demoadm();
		$mode = $this->input('mode', 'string'); // if mode "media" use as mediamanager


		$this->configure(array ());

		if ( empty($_POST[ 'current' ]) || false == ($dir = $this->_findDir(trim($_POST[ 'current' ]))) )
		{
			$this->_result[ 'error' ] = 'Invalid parameters';
		}

		if ( !$this->_isAllowed($dir, 'write') )
		{
			$this->_result[ 'error' ] = 'Access denied';
		}


		$upload = isset($_FILES[ 'upload' ]) ? $_FILES[ 'upload' ] : null;
		// Parse the Content-Disposition header, if available:
		$file_name = $this->get_server_var('HTTP_CONTENT_DISPOSITION') ?
			rawurldecode(preg_replace('/(^[^"]+")|("$)/', '', $this->get_server_var('HTTP_CONTENT_DISPOSITION'))) :
			null;

		// Parse the Content-Range header, which has the following form:
		// Content-Range: bytes 0-524287/2000000
		$content_range = $this->get_server_var('HTTP_CONTENT_RANGE') ?
			preg_split('/[^0-9]+/', $this->get_server_var('HTTP_CONTENT_RANGE')) : null;
		$size          = $content_range ? $content_range[ 3 ] : null;


		if ( empty($upload) )
		{
			$this->_result[ 'error' ] = 'No file to upload';
		}

		if ( !isset($this->_result[ 'error' ]) )
		{
			$this->_logContext[ 'upload' ] = array ();
			$this->_result[ 'select' ]     = array ();

			$total = 0;


			if ( $upload && is_array($upload[ 'tmp_name' ]) )
			{
				// param_name is an array identifier like "files[]",
				// $_FILES is a multi-dimensional array:
				foreach ( $upload[ 'tmp_name' ] as $index => $value )
				{

					$name = $file_name ? $file_name : $upload[ 'name' ][ $index ];
					$temp = $upload[ 'tmp_name' ][ $index ];
					$size = ($size ? $size : $upload[ 'size' ][ $index ]);


					if ( !empty($temp) )
					{
						$total++;

						$this->_logContext[ 'upload' ][ ] = $name;

						if ( $upload[ 'error' ][ $index ] > 0 )
						{
							$this->_result[ 'error' ] = 'Unable to upload file';
							switch ( $upload[ 'error' ][ $index ] )
							{
								case UPLOAD_ERR_INI_SIZE:
								case UPLOAD_ERR_FORM_SIZE:
									$this->_result[ 'error' ] = 'File exceeds the maximum allowed filesize';
									break;
								case UPLOAD_ERR_EXTENSION:
									$this->_result[ 'error' ] = 'Not allowed file type';
									break;
							}
						}
						elseif ( false == ($name = $this->_checkName($name)) )
						{
							$this->_result[ 'error' ] = 'Invalid name';
						}
						elseif ( !$this->_isUploadAllow($name, $temp) )
						{
							$this->_result[ 'error' ] = 'Not allowed file type';
						}
						else
						{
							$name = $this->_checkName($name);
							$file = $dir . DIRECTORY_SEPARATOR . $name;
							if ( !@move_uploaded_file($temp, $file) )
							{
								$this->_result[ 'error' ] = 'Unable to save uploaded file';
							}
							else
							{
								@chmod($file, $this->options[ 'fileMode' ]);
								$this->_result[ 'select' ][ ] = $this->_hash($file);
							}
						}

						if ( !empty($this->_result[ 'error' ]) )
						{
							break 1;
						}
					}
				}
			}
			else
			{
				$name     = $file_name ? $file_name : (isset($upload[ 'name' ]) ? $upload[ 'name' ] : null);
				$temp     = isset($upload[ 'tmp_name' ]) ? $upload[ 'tmp_name' ] : null;
				$size     = $size ? $size :
					(isset($upload[ 'size' ]) ? $upload[ 'size' ] : $this->get_server_var('CONTENT_LENGTH'));
				$filetype = isset($upload[ 'type' ]) ? $upload[ 'type' ] : $this->get_server_var('CONTENT_TYPE');

				if ( $name === null || $temp === null )
				{
					$this->_result[ 'error' ] = 'Invalid name';
				}


				if ( false == ($name = $this->_checkName($name)) )
				{
					$this->_result[ 'error' ] = 'Invalid name';
				}
				elseif ( !$this->_isUploadAllow($name, $temp) )
				{
					$this->_result[ 'error' ] = 'Not allowed file type';
				}
				else
				{
					$name = $this->_checkName($name);
					$file = $dir . DIRECTORY_SEPARATOR . $name;

					if ( !@move_uploaded_file($temp, $file) )
					{
						$this->_result[ 'error' ] = 'Unable to save uploaded file';
					}
					else
					{
						@chmod($file, $this->options[ 'fileMode' ]);
						$this->_result[ 'select' ][ ] = $this->_hash($file);
					}
				}
			}
		}

		$errCnt = isset($this->_result[ 'error' ]) ? true : false;

		if ( $errCnt )
		{
			$this->_result[ 'error' ]   = $this->_result[ 'error' ];
			$this->_result[ 'success' ] = false;
		}
		else
		{
			$this->_content($dir);
			$this->_result[ 'success' ] = true;
		}

		$this->prepareData();

		Ajax::Send( (isset($this->_result['error']) ? false : true), $this->_result);
		exit;

		header("Content-Type: application/json");
		header("Connection: close");

		echo json_encode($this->_result);

		exit();
	}

}
