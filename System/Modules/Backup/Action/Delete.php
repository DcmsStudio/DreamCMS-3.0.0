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
 * @file         Delete.php
 */
class Backup_Action_Delete extends Backup_Helper_BaseHelper
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		demoadm();


		$backups = $this->getBackups();

		$id = (int)$this->input('id');
		$x  = 1;
		foreach ( $backups as $r )
		{
			if ( $x === $id )
			{
				$filename = $r[ 'name' ];
				break;
			}

			$x++;
		}

		Library::disableErrorHandling();
		$path = Library::formatPath(DATA_PATH . 'backup/' . $filename);
		if ( file_exists($path) && !is_dir($path) )
		{
			unlink($path);
		}
		else
		{
			Error::raise('That is not a backup which can be deleted.');
		}

		Library::enableErrorHandling();

		Library::sendJson(true, trans('Backup-Datei wurde gelöscht'));
	}

}

?>