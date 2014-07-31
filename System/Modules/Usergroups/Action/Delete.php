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
 * @package      Usergroups
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Delete.php
 */
class Usergroups_Action_Delete extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}
		$groupid = (int)HTTP::input('id');

		if ( !$groupid && HTTP::input('id') )
		{
			Error::raise('Group not exists!');
		}

		// Standart Benutzergruppe (kein gast)
		$group    = $this->db->query('SELECT * FROM %tp%users_groups WHERE grouptype = ? AND  groupid!=?', 'default', $groupid)->fetch();
		$groupTmp = $this->db->query('SELECT * FROM %tp%users_groups WHERE groupid=?', $groupid)->fetch();

		// Guest Group check
		if ( $group[ 'groupid' ] == $groupid )
		{
			Library::log("Faild Group Delete {$group['title']} (ID: {$group['groupid']}).", 'warn');
			Library::sendJson(false, trans("Standardgruppen können nicht gelöscht werden."));
		}

		// Admin Group check
		if ( $groupTmp[ 'groupid' ] == User::getGroupId() || $group[ 'groupid' ] == 1 )
		{
			Library::log("Faild Group Delete {$groupTmp['title']} (ID: {$groupTmp['groupid']}).", 'warn');
			Library::sendJson(false, trans("Sie können diese Gruppe nicht löschen, da Sie dem Admin bzw. der Standardgruppe gehört."));
		}


		demoadm();

		$this->db->query('UPDATE %tp%users SET groupid=? WHERE groupid=?', $group[ 'groupid' ], $groupid);


		$this->db->query('UPDATE %tp%avatars SET groupid=? WHERE groupid=?', $group[ 'groupid' ], $groupid);

		$this->db->query('UPDATE %tp%events SET groupid=? WHERE groupid=?', $group[ 'groupid' ], $groupid);

		$this->db->query('DELETE FROM %tp%users_ranks WHERE groupid=?', $groupid);

		$this->db->query('DELETE FROM %tp%users_groups WHERE groupid=?', $groupid);


		Cache::delete('menu_user_' . User::getUserId());
		Cache::delete('groupactionperms-' . $groupid);

		Library::log("Delete Usergroup {$groupTmp['title']} (ID: {$groupTmp['groupid']}).", 'warn');
		Library::sendJson(true);
	}

}

?>