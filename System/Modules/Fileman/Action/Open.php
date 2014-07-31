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
 * @file         Open.php
 */
class Fileman_Action_Open extends Fileman_Helper_Base
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$mode = $this->input('mode', 'string'); // if mode "media" use as mediamanager


		$this->configure(array ());

		if ( !Session::get('browsePath', false) )
		{
			Session::save('browsePath', PAGE_PATH);
		}

		$_currentPath = Session::get('browsePath');

		if ( $this->input('path') != null )
		{
			if ( is_dir($_currentPath . $this->input('path')) )
			{
				Session::save('browsePath', $_currentPath . $this->input('path') . '/');
			}
		}


		if ( $this->input('gethash') )
		{
			$_path = $this->input('path');
			$out   = false;

			if ( empty($_path) )
			{
				Library::sendJson(false, 'Invalid Path');
			}

			$path = realpath($_path);
			$path = str_replace($this->root, '', $path);

			if ( substr($path, 0, 1) == '/' )
			{
				$path = substr($path, 1);
			}

			if ( is_file($this->root .'/'. $path) && is_readable($this->root .'/'. $path))
			{
				$out[ 'hash' ] = $this->_hash($this->root .'/'. $path);
			}

			if ( $out === false )
			{
				Library::sendJson(false, 'Invalid Path');
			}
			else
			{
				$out[ 'success' ] = true;

				// change work directory
				$cwd = Session::get('cwd', '');

				$file = Library::getFilename($path);
				$path = str_replace('/'.$file, '', $path);

				if ( $cwd != $this->root .'/'. $path ) {

					$personal = new Personal;
					$personal->set('filemanager', 'path', array ('path' => $this->root .'/'. $path ));
					Session::save('cwd', $this->root .'/'. $path );


					$this->_open(1);
					$out = array_merge($out, $this->_result);
				}

				Ajax::Send(true, $out);

				exit;
			}

		}


		$ts = $this->utime();


		if ( (int)$this->_get('page') )
		{
			$this->_open((int)$this->_get('page'));
		}
		else
		{
			$this->_open(1);
		}

		Ajax::Send(true, $this->_result);
		exit;

		header("Content-Type: application/json");
		header("Connection: close");
		echo json_encode($this->_result);
		exit();
	}

}
