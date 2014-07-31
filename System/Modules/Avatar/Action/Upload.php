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
 * @package      Avatar
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Upload.php
 */
class Avatar_Action_Upload extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}


		if ( isset($_FILES[ 'Filedata' ]) && !empty($_FILES[ 'Filedata' ][ 'name' ]) )
		{
			demoadm();

			$upload_path = '/';
			$upload_path = UPLOAD_PATH . "tmp/";

			if ( !is_dir($upload_path) )
			{
				Library::makeDirectory($upload_path);
			}

			if ( substr($upload_path, -1) !== '/' )
			{
				$upload_path = $upload_path . '/';
			}

			$file = new Upload($_FILES[ 'Filedata' ], true, $upload_path);

			if ( $file->success() )
			{
				$contents = '';

				if ( HTTP::input('pathonly') )
				{
					$contents = file_get_contents($file->getPath());
					$file->delete();
				}


				if ( str_replace(ROOT_PATH, '', $file->getPath()) === '' )
				{
					Library::sendJson(array (
					                        'success' => false,
					                        'msg'     => 'upload.error.not.moved'
					                  ));
				}

				$path     = $file->getPath();
				$p        = explode('/', $path);
				$filename = array_pop($p);

				rename($file->getPath(), UPLOAD_PATH . "tmp/avatar-" . $filename);


				echo Library::json(array (
				                         'success'  => true,
				                         'fileurl'  => UPLOAD_URL . "tmp/avatar-" . $filename,
				                         'filename' => $filename,
				                         'filesize' => Library::formatSize(filesize(UPLOAD_PATH . "tmp/avatar-" . $filename)),
				                         'path'     => str_replace(ROOT_PATH, '', $path),
				                         'name'     => trans('Load file...'),
				                         'content'  => $contents
				                   ));
				exit;
			}
			else
			{
				Error::raise($file->getError());
			}
		}
		else
		{
			Error::raise('no file in upload?');
		}
	}

}
