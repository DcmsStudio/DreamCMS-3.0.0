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
 * @package      Importer
 * @version      3.0.0 Beta
 * @category     Helper
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Csv.php
 */
class Importer_Action_Upload extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}
		$status = array ();


		$upload = isset($_FILES[ 'Filedata' ]) ? $_FILES[ 'Filedata' ] : null;
		// Parse the Content-Disposition header, if available:
		$file_name = $this->get_server_var('HTTP_CONTENT_DISPOSITION') ?
			rawurldecode(preg_replace('/(^[^"]+")|("$)/', '', $this->get_server_var('HTTP_CONTENT_DISPOSITION'))) :
			null;

		// Parse the Content-Range header, which has the following form:
		// Content-Range: bytes 0-524287/2000000
		$content_range = $this->get_server_var('HTTP_CONTENT_RANGE') ?
			preg_split('/[^0-9]+/', $this->get_server_var('HTTP_CONTENT_RANGE')) : null;
		$size          = $content_range ? $content_range[ 3 ] : null;


		$name     = $file_name ? $file_name : (isset($upload[ 'name' ]) ? $upload[ 'name' ] : null);
		$temp     = isset($upload[ 'tmp_name' ]) ? $upload[ 'tmp_name' ] : null;
		$size     = $size ? $size :
			(isset($upload[ 'size' ]) ? $upload[ 'size' ] : $this->get_server_var('CONTENT_LENGTH'));
		$filetype = isset($upload[ 'type' ]) ? $upload[ 'type' ] : $this->get_server_var('CONTENT_TYPE');

		if ( $name === null || $temp === null )
		{
			$status[ 'error' ] = 'Invalid name';
		}

		// Session::delete( 'Importer' );

		if ( !isset($status[ 'error' ]) )
		{
			$fileupload = new Upload($upload, true, UPLOAD_PATH);

			if ( $fileupload->success() )
			{
				$basepath = $fileupload->getPath();

				$mime = Library::getMimeType($basepath);


				if ( ($name = $this->checkName(($name ? $name : $temp))) === false )
				{
					$status[ 'error' ] = 'Invalid name ' . (($name ? $name : $temp));
					unlink($basepath);
				}
				elseif ( $this->isUploadAllow($basepath) !== true )
				{
					$status[ 'error' ] = 'Not allowed file type ' . ($mime);
					unlink($basepath);
				}
				else
				{
					$status[ "filepath" ] = $basepath;
					$status[ "filename" ] = $name;
					$status[ "filesize" ] = Library::formatSize((int)filesize($basepath));
					$status[ "status" ]   = trans('Speichern erfolgreich');
					$status[ "size" ]     = Library::formatSize((int)$size);
					$status[ "success" ]  = true;
					$status[ "isimage" ]  = false;
					$status[ 'fileurl' ]  = str_replace(ROOT_PATH, '', $basepath);


					Session::save('Importer', $status);
				}
			}
			else
			{
				$status[ 'error' ] = $fileupload->getError();
			}
		}

		if ( isset($status[ 'error' ]) )
		{
			$status[ 'success' ] = false;
		}
		else
		{
			$status[ 'success' ] = true;
		}

		echo Library::json($status);
		exit;
	}

	/**
	 * @param string $id
	 * @return string
	 */
	protected function get_server_var ( $id )
	{

		return isset($_SERVER[ $id ]) ? $_SERVER[ $id ] : '';
	}

	/**
	 * Check new file name for invalid simbols. Return name if valid
	 *
	 * @param $n
	 * @return string $n file name
	 * @return string
	 */
	public function checkName ( $n )
	{

		$n = strip_tags(trim($n));

		if ( '.' === substr($n, 0, 1) )
		{
			return false;
		}

		return preg_match('|^[^\\/\<\>:]+$|', $n) ? $n : false;
	}

	/**
	 *
	 * @param string $tmpName
	 * @return bool
	 */
	private function isUploadAllow ( $tmpName )
	{

		$mime = Library::getMimeType($tmpName);


		foreach ( Importer_Helper_Base::$allowedMimes as $ext => $data )
		{
			if ( is_array($data) )
			{
				if ( in_array($mime, $data) )
				{
					return true;
				}
			}
			else if ( $mime == $data )
			{
				return true;
			}
		}

		return false;
	}

}
