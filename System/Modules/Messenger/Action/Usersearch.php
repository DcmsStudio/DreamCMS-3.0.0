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
 * @package      Messenger
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Usersearch.php
 */
class Messenger_Action_Usersearch extends Controller_Abstract
{

	public function execute ()
	{

		$q = HTTP::input('q');

		if ( !trim($q) )
		{
			Library::sendJson(false, trans('Sie haben keinen Benutzer übergeben.'));
		}

		$data = array ();

		$sql             = "SELECT userid, username, `name` AS realname FROM %tp%users
				WHERE username LIKE " . $this->db->quote('%' . $q . '%') . " OR
				`name` LIKE " . $this->db->quote('%' . $q . '%') . "
				GROUP BY userid ORDER BY username ASC LIMIT 50";
		$data[ 'users' ] = $this->db->query($sql)->fetchAll();
		$this->Template->process('messenger/search_user', $data, true);
		exit;
	}

}

?>