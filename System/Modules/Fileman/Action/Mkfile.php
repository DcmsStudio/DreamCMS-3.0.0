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
 * @file         Mkfile.php
 */
class Fileman_Action_Mkfile extends Fileman_Helper_Base
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}
		demoadm();
		$this->configure(array ())->checkCommand();

		if ( empty($_GET[ 'current' ]) || false == ($dir = $this->_findDir(trim($_GET[ 'current' ]))) )
		{
			$this->_result[ 'error' ] = 'Invalid parameters';
		}
		$this->_logContext[ 'file' ] = $dir . DIRECTORY_SEPARATOR . $_GET[ 'name' ];
		if ( !$this->_isAllowed($dir, 'write') )
		{
			$this->_result[ 'error' ] = 'Access denied';
		}
		elseif ( false == ($name = $this->_checkName($_GET[ 'name' ])) )
		{
			$this->_result[ 'error' ] = 'Invalid name';
		}
		elseif ( file_exists($dir . DIRECTORY_SEPARATOR . $name) )
		{
			$this->_result[ 'error' ] = 'File or folder with the same name already exists';
		}
		else
		{
			$f                           = $dir . DIRECTORY_SEPARATOR . $name;
			$this->_logContext[ 'file' ] = $f;
			if ( false != ($fp = @fopen($f, 'wb')) )
			{
				fwrite($fp, "");
				fclose($fp);

				@chmod($f, $this->options[ 'fileMode' ]);
				$this->_result[ 'select' ] = array (
					$this->_hash($dir . DIRECTORY_SEPARATOR . $name)
				);
				$this->_content($dir);
			}
			else
			{
				$this->_result[ 'error' ] = 'Unable to create file';
			}
		}
		Ajax::Send( (isset($this->_result['error']) ? false : true), $this->_result);
		exit;

		exit;
		header("Content-Type: application/json");
		header("Connection: close");
		echo json_encode($this->_result);
		exit();
	}

}
