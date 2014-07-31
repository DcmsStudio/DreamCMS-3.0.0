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
 * @package      Component
 * @version      3.0.0 Beta
 * @category     Helper
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         BaseHelper.php
 */
class Component_Helper_BaseHelper extends Controller_Abstract
{

	/**
	 *
	 * @param array $data
	 * @return array
	 */
	public function validate ( $data )
	{

		$name = trim($data[ 'name' ]);

		$errors = array ();

		if ( !$name )
		{
			$errors[ ] = 'Component name is a required field';
		}


		if ( strlen($name) < 3 )
		{
			$errors[ ] = 'Component name must be at least 4 characters long';
		}

		if ( strlen($name) > 50 )
		{
			$errors[ ] = 'Component name may not be longer than 50 characters';
		}

		if ( preg_match('/\s+/', $name) )
		{
			$errors[ ] = 'Spaces are not allowed in the Component name';
		}

		if ( (int)$data[ 'id' ] )
		{
			$isAdded = $this->model->componentExists($name, $data[ 'id' ]);

			if ( $isAdded[ 'name' ] )
			{
				$errors[ ] = 'Component name must be unique, there is already a component with that name';
			}
		}


		return $errors;
	}

	/**
	 * Helpers
	 */
	public function getBackups ()
	{

		$path    = DATA_PATH . '/backup/';
		$backups = array ();
		foreach ( new DirectoryIterator($path) as $file )
		{
			if ( $file->isDot() )
			{
				continue;
			}

			if ( Library::getExtension($file->getFileName()) == 'zip' )
			{

				$data                         = array ();
				$data[ 'date' ]               = $file->getMTime();
				$data[ 'name' ]               = $file->getFileName();
				$data[ 'size' ]               = Library::humanSize($file->getSize());
				$backups[ $file->getMTime() ] = $data;
			}
		}
		krsort($backups);

		return $backups;
	}

	/**
	 * @return array
	 */
	public function getRootDirectoryListing ()
	{

		$path  = ROOT_PATH;
		$files = array ();
		$temp  = array ();
		foreach ( new DirectoryIterator($path) as $file )
		{
			if ( $file->isDot() )
			{
				continue;
			}

			$data              = array ();
			$data[ 'date' ]    = $file->getMTime();
			$data[ 'name' ]    = $file->getFileName();
			$data[ 'size' ]    = $file->getSize();
			$data[ 'checked' ] = in_array($file->getFileName(), array (
			                                                          'dm',
			                                                          'setup',
			                                                          'xmlrpc',
			                                                          'pages',
			                                                          'simg',
			                                                          'LICENSE',
			                                                          'index.php',
			                                                          'admin.php',
			                                                          'setup.php',
			                                                          '.htaccess',
			                                                          'LICENSE',
			                                                          'robots.txt'
			                                                    ));
			$data[ 'icon' ]    = ($file->isDir() ? 'folder' : Library::getExtension($file->getFileName()));
			$files[ ]          = $data;
		}

		usort($files, array (
		                    $this,
		                    'sortDirectoryListing'
		              ));

		return $files;
	}

	/**
	 * @param $a
	 * @param $b
	 * @return int
	 */
	public function sortDirectoryListing ( $a, $b )
	{

		$a_t = strtotime($a[ 'date' ]);
		$b_t = strtotime($b[ 'date' ]);
		if ( $a[ 'date' ] == $b[ 'date' ] )
		{
			return 0;
		}

		return ($a[ 'date' ] > $b[ 'date' ]) ? -1 : 1;
	}

	/**
	 * @return bool
	 */
	public function getDatabases ()
	{

		return $this->db->list_databases();
	}

}

?>