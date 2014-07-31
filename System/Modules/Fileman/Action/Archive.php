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
 * @file         Archive.php
 */
class Fileman_Action_Archive extends Fileman_Helper_Base
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$this->configure(array ())->checkCommand();

		$_name   = $this->input('name');
		$current = $this->input('current');
		$targets = $this->input('targets');
		$type    = $this->input('type'); // archive type


		$this->_checkArchivers();
		if ( empty($this->options[ 'archivers' ][ 'create' ]) || empty($type) || empty($this->options[ 'archivers' ][ 'create' ][ $type ]) || !in_array($type, $this->options[ 'archiveMimes' ]) )
		{
			$this->_result[ 'error' ] = 'Invalid parameters';
		}

		if ( !isset($this->_result[ 'error' ]) && (empty($targets) || empty($targets) || !is_array($targets) || false == ($dir = $this->_findDir(trim($current))) || !$this->_isAllowed($dir, 'write'))
		)
		{
			$this->_result[ 'error' ] = 'Invalid parameters';
		}


		if ( !isset($this->_result[ 'error' ]) )
		{
			$files = array ();
			$argc  = '';

			foreach ( $targets as $hash )
			{
				if ( false == ($f = $this->_find($hash, $dir)) )
				{
					$this->_result[ 'error' ] = 'File not found';
					break;
				}
				else
				{
					$files[ ] = $f;
					$argc .= escapeshellarg(basename($f)) . ' ';
				}
			}

			$arc  = $this->options[ 'archivers' ][ 'create' ][ $type ];
			$name = count($files) == 1 ? basename($files[ 0 ]) : $_name;
			$name = basename($this->_uniqueName($name . '.' . $arc[ 'ext' ], ''));

			$cwd = getcwd();
			chdir($dir);
			$cmd = $arc[ 'cmd' ] . ' ' . $arc[ 'argc' ] . ' ' . escapeshellarg($name) . ' ' . $argc;
			exec($cmd, $o, $c);

			chdir($cwd);

			if ( file_exists($dir . DIRECTORY_SEPARATOR . $name) )
			{
				$this->_content($dir);
				$this->_result[ 'select' ] = array (
					$this->_hash($dir . DIRECTORY_SEPARATOR . $name)
				);
			}
			else
			{
				$this->_result[ 'error' ] = 'Unable to create archive';
			}
		}


		if ( false == ($dir = $this->_findDir(trim($current))) )
		{
			$this->_result[ 'error' ] = 'File not found';
		}
		else
		{
			$this->_content($dir, false);
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
