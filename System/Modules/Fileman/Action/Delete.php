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
 * @file         Delete.php
 */
class Fileman_Action_Delete extends Fileman_Helper_Base
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$mode = $this->input('mode', 'string'); // if mode "media" use as mediamanager

		demoadm();
		$this->configure(array ())->checkCommand();

		if ( empty($_GET[ 'current' ]) || false == ($dir = $this->_findDir(trim($_GET[ 'current' ]))) || (empty($_GET[ 'targets' ]) || !is_array($_GET[ 'targets' ])) )
		{
			$this->_result[ 'error' ] = 'Invalid parameters';
		}
		else
		{
			$this->_logContext[ 'targets' ] = array ();
			foreach ( $_GET[ 'targets' ] as $hash )
			{
				if ( false != ($f = $this->_find($hash, $dir)) )
				{
					$this->_remove($f);
					$this->_logContext[ 'targets' ][ ] = $f;
				}
			}
			if ( !empty($this->_result[ 'errorData' ]) )
			{
				$this->_result[ 'error' ] = 'Unable to remove file';
			}
			$this->_content($dir, true);
		}

		$this->prepareData();

		Ajax::Send( (isset($this->_result['error']) ? false : true), $this->_result);
		exit;

	}

	/**
	 * Remove file or folder (recursively)
	 *
	 * @param string $path fole/folder path
	 * @return void
	 * */
	protected function _remove ( $path )
	{

		if ( !$this->_isAllowed($path, 'rm') )
		{
			return $this->_errorData($path, 'Access denied');
		}
		if ( !is_dir($path) )
		{
			$info = pathinfo($path);
			$name = Library::getFilename($path);

			if (!is_dir(PAGE_PATH . '/.trash') ) {
				Library::makeDirectory(PAGE_PATH . '/.trash');
			}

			$filepath = str_replace( PAGE_PATH, '', str_replace($name, '', $path) );
			if (!is_dir(PAGE_PATH . '/.trash/' . $filepath ) ) {
				Library::makeDirectory(PAGE_PATH . '/.trash/' . $filepath);
			}

			// move file to the trash folder with structure
			if ( !@rename($path, PAGE_PATH . '/.trash/' . $filepath . '/' . $name) )
			{
				$this->_errorData($path, 'Unable to remove file');
			}
			else
			{
				// remove thumbnails
				$this->_rmTmb($path);


				$this->Event->trigger('delete.fileman', $path, $this->_hash($path));
			}
		}
		else
		{
			$info = pathinfo($path);

			$name = dirname($path);
			$filepath = str_replace( PAGE_PATH, '',  $path );

			if (!is_dir(PAGE_PATH . '/.trash/' . $filepath ) )
			{
				Library::makeDirectory(PAGE_PATH . '/.trash/' . $filepath);
			}


			$ls = scandir($path);
			for ( $i = 0; $i < count($ls); $i++ )
			{
				if ( '.' != $ls[ $i ] && '..' != $ls[ $i ] )
				{
					$this->_remove($path . DIRECTORY_SEPARATOR . $ls[ $i ]);
				}
			}

			if ( !@rename($path, PAGE_PATH . '/.trash/' . $filepath . '/' . $name) )
			{
				$this->_errorData($path, 'Unable to remove file');
			}
			else {
				$this->Event->trigger('delete.fileman', $path, $this->_hash($path));
			}

		}

		return true;
	}

}
