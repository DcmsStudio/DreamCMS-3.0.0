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
 * @file         Read.php
 */
class Fileman_Action_Read extends Fileman_Helper_Base
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}
		demoadm();

		$this->configure(array ())->checkCommand();

		if ( empty($_GET[ 'current' ]) || empty($_GET[ 'target' ]) || false == ($dir = $this->_findDir(trim($_GET[ 'current' ]))) || false == ($target = $this->_find(trim($_GET[ 'target' ]), $dir))
		)
		{
			$this->_result[ 'error' ] = 'File not found';
		}
		elseif ( !$this->_isAllowed($dir, 'write') )
		{
			$this->_result[ 'error' ] = 'Access denied';
		}
		elseif ( !file_exists($target) )
		{
			$this->_result[ 'error' ] = 'File or folder not exists "' . $target . '"';
		}
		else
		{
			$this->_result[ 'content' ] = file_get_contents($target);
		}


		$this->_result[ 'success' ] = (isset($this->_result[ 'error' ]) ? false : true);
		$this->prepareData();

		Ajax::Send( (isset($this->_result['error']) ? false : true), $this->_result);
		exit;

		header("Content-Type: application/json");
		header("Connection: close");
		echo json_encode($this->_result);
		exit();
	}

}
