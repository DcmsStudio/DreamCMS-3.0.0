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
 * @file         Extract.php
 */
class Fileman_Action_Extract extends Fileman_Helper_Base
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}
		demoadm();
		$this->configure(array ())->checkCommand();

		$_current = $this->input('current');
		$target   = $this->input('target');


		if ( empty($_current) || false == ($current = $this->_findDir(trim($_current))) || empty($target) || false == ($file = $this->_find(trim($target), $current)) || !$this->_isAllowed($current, 'write')
		)
		{
			$this->_result[ 'error' ] = 'Invalid parameters';
		}


		if ( !isset($this->_result[ 'error' ]) )
		{
			$this->_checkArchivers();
			$mime = $this->_mimetype($file);

			if ( empty($this->options[ 'archivers' ][ 'extract' ][ $mime ]) )
			{
				$this->_result[ 'error' ] = 'Invalid parameters';
			}
		}

		if ( !isset($this->_result[ 'error' ]) )
		{
			$cwd = getcwd();
			$arc = $this->options[ 'archivers' ][ 'extract' ][ $mime ];
			$cmd = $arc[ 'cmd' ] . ' ' . $arc[ 'argc' ] . ' ' . escapeshellarg(basename($file));
			chdir(dirname($file));
			exec($cmd, $o, $c);
			chdir($cwd);

			if ( $c == 0 )
			{
				$this->_content($current, true);
			}
			else
			{
				$this->_result[ 'error' ] = 'Unable to extract files from archive';
			}
		}

		header("Content-Type: application/json");
		header("Connection: close");
		echo json_encode($this->_result);
		exit();
	}

}
