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
 * @file         Paste.php
 */
class Fileman_Action_Paste extends Fileman_Helper_Base
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}
		demoadm();
		$mode = $this->input('mode', 'string'); // if mode "media" use as mediamanager


		$this->configure(array ());
		$this->_paste();
		$this->prepareData();

		header("Content-Type: application/json");
		header("Connection: close");
		echo json_encode($this->_result);
		exit();
	}

	/**
	 * @return bool|string
	 */
	protected function _paste ()
	{

		if ( empty($_GET[ 'current' ]) || false == ($current = $this->_findDir(trim($_GET[ 'current' ]))) || empty($_GET[ 'src' ]) || false == ($src = $this->_findDir(trim($_GET[ 'src' ]))) || empty($_GET[ 'dst' ]) || false == ($dst = $this->_findDir(trim($_GET[ 'dst' ]))) || empty($_GET[ 'targets' ]) || !is_array($_GET[ 'targets' ])
		)
		{
			return $this->_result[ 'error' ] = 'Invalid parameters' && $this->_content($current, true);
		}


		$cut                         = !empty($_GET[ 'cut' ]);
		$this->_logContext[ 'src' ]  = array ();
		$this->_logContext[ 'dest' ] = $dst;
		$this->_logContext[ 'cut' ]  = $cut;


		if ( !$this->_isAllowed($dst, 'write') || !$this->_isAllowed($src, 'read') )
		{
			return $this->_result[ 'error' ] = 'Access denied';
		}

		foreach ( $_GET[ 'targets' ] as $hash )
		{

			$hash = preg_replace('#^link_#', '', $hash);


			if ( false == ($f = $this->_find($hash, $src)) )
			{
				return $this->_result[ 'error' ] = 'File not found' && $this->_content($current, true);
			}


			$this->_logContext[ 'src' ][ ] = $f;
			$_dst                          = $dst . DIRECTORY_SEPARATOR . basename($f);

			if ( 0 === strpos($dst, $f) )
			{
				return $this->_result[ 'error' ] = 'Unable to copy into itself' && $this->_content($current, true);
			}
			elseif ( file_exists($_dst) )
			{
				return $this->_result[ 'error' ] = 'File or folder with the same name already exists' && $this->_content($current, true);
			}
			elseif ( $cut && !$this->_isAllowed($f, 'rm') )
			{
				return $this->_result[ 'error' ] = 'Access denied' && $this->_content($current, true);
			}

			if ( $cut )
			{
				if ( !@rename($f, $_dst) )
				{
					return $this->_result[ 'error' ] = 'Unable to move files' && $this->_content($current, true);
				}
				elseif ( !is_dir($f) )
				{
					$this->_rmTmb($f);
				}
			}
			elseif ( !$this->_copy($f, $_dst) )
			{
				return $this->_result[ 'error' ] = 'Unable to copy files' && $this->_content($current, true);
			}
		}

		$this->_content($dst, true);
	}

}
