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
 * @file         Getnew.php
 */
class Messenger_Action_Getnew extends Controller_Abstract
{

	public function execute ()
	{


		$result = $this->db->query('SELECT m.id, m.title AS subject, m.sendtime, SUBSTR(m.message, 1, 350) AS message,
                               u.username
                               FROM %tp%messages AS m 
                               LEFT JOIN %tp%users AS u ON (u.userid=m.fromuser)
                               WHERE m.readtime <= 0 AND m.touser = ?', User::getUserId())->fetchAll();
		foreach ( $result as &$r )
		{
			$r[ 'message' ] = BBCode::toXHTML($r[ 'message' ]);
			$r[ 'time' ]    = Locales::formatDateTime($r[ 'sendtime' ]);
		}


		echo Library::json(array (
		                         'success' => true,
		                         'result'  => $result
		                   ));
		exit;
	}

}
