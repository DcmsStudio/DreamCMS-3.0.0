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
 * @package      Backup
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Create.php
 */
class Backup_Action_Create extends Backup_Helper_BaseHelper
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}


		demoadm();


		$path = DATA_PATH . 'backup/';

		if ( $this->input('cancel') )
		{

			Session::save('cancelBackup', true);


			file_put_contents($path . 'stop', TIMESTAMP);


			Library::sendJson(true, trans('Backup abgebrochen'));


			exit;
		}

		Library::enableErrorHandling();
		error_reporting(E_ALL);

		if ( $this->input('mode') == 'full' )
		{
			ob_start();


			$data[ 'files' ] = $this->getCoreDirectorysAndFiles();
			$data[ 'dbs' ]   = $this->getDatabases();

			//
			$tmpDb = array ();
			foreach ( $data[ 'dbs' ] as $x => $database )
			{
				if ( $this->db->_databaseName === $database )
				{


					$tables = $this->db->query("SHOW TABLES FROM `" . $database . "` LIKE '%'")->fetchAll();
					foreach ( $tables as $i => $table )
					{
						foreach ( $table as $k => $tablename )
						{
							$tmp[ ] = $tablename;
						}
					}

					$data[ 'database' ]             = $database;
					$tmpDb[ $database ][ 'tables' ] = $tmp;

					$i++;
				}
			}

			unset($data[ 'dbs' ]);


			file_put_contents($path . 'lock', TIMESTAMP);


			try
			{
				Backup::create($data[ 'files' ], array (
				                                       $this->db->_databaseName
				                                 ), $tmpDb);
			}
			catch ( BackupException $e )
			{
				@unlink($path . 'lock');

				Session::delete('cancelBackup');
				Session::write();

				echo 'error';
				ob_flush();

				exit;
				// Error::raise( $e->getMessage() );
			}


			if ( Session::get('cancelBackup') )
			{
				Session::delete('cancelBackup');
				Session::write();

				@unlink($path . 'lock');

				echo 'cancel';

				ob_flush();
				exit;
			}

			Session::delete('cancelBackup');
			Session::write();




			echo 'Backup wurde erstellt';
			ob_flush();

			@unlink($path . 'lock');
			exit;
		}


		if ( $this->Input->getMethod() == 'post' )
		{
			$post = HTTP::post();


			if ( file_exists($path . 'lock') )
			{
				Error::raise(trans('Ein Backup wird gerade angelegt! Bitte warten...'));
			}


			if ( empty($post) )
			{
				Error::raise(trans('Please select the files and databases that should be backed up.'));
			}

            Session::delete('cancelBackup');

			file_put_contents($path . 'lock', TIMESTAMP);

			try
			{
				Backup::create($post[ 'files' ], $post[ 'db' ], $post[ 'table' ]);
			}
			catch ( BackupException $e )
			{
				@unlink($path . 'lock');
				Error::raise($e->getMessage());
			}

			@unlink($path . 'lock');
			Library::sendJson(true, trans('Backup wurde erstellt'));
			exit;
		}

		$data            = array ();
		$data[ 'files' ] = $this->getRootDirectoryListing();
		$data[ 'dbs' ]   = $this->getDatabases();

		$i = 0;
		foreach ( $data[ 'dbs' ] as $x => $database )
		{

			$tmp    = array ();
			$tables = array ();
			$tables = $this->db->query("SHOW TABLES FROM `" . $database . "` LIKE '%'")->fetchAll();
			foreach ( $tables as $i => $table )
			{
				foreach ( $table as $k => $tablename )
				{
					$tmp[ ][ 'table' ] = $tablename;
				}
			}
			$data[ 'dbs' ][ $x ]               = array ();
			$data[ 'dbs' ][ $x ][ 'database' ] = $database;
			$data[ 'dbs' ][ $x ][ 'tables' ]   = $tmp;

			$i++;
		}

        Session::delete('cancelBackup');


		Library::addNavi(trans('Backups'));
		Library::addNavi(trans('Backup anlegen'));


		$this->Template->process('backup/create', $data, true);
		exit;
	}

}

?>