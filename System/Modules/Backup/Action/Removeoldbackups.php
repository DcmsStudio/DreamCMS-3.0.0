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
 * @file         Removeoldbackups.php
 */
class Backup_Action_Removeoldbackups extends Backup_Helper_BaseHelper
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		demoadm();

		$backups = $this->getBackups();
		$t       = time();
		$deleted = 0;
		foreach ( $backups as $r )
		{
			if ( ($t - Settings::get('backup.oldbackups', 86000)) > $r[ 'date' ] && file_exists(DATA_PATH . 'backup/' . $r[ 'name' ]) )
			{
				@unlink(DATA_PATH . 'backup/' . $r[ 'name' ]);
				$deleted++;
			}
		}


		Library::log(sprintf('Has delete %s old Backups', $deleted), 'warn');


		echo Library::json(array (
		                         'success' => true,
		                         'msg'     => $deleted > 1 ?
				                         sprintf(trans('Es wurden %s alte Backups gelöscht'), $deleted) :
				                         ($deleted === 1 ? trans('Es wurde ein altes Backup gelöscht') :
					                         trans('Es wurde kein altes Backup gelöscht'))
		                   ));
		exit;
	}

}
