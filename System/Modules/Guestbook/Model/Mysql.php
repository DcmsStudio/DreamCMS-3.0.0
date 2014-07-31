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
 * @package      Guestbook
 * @version      3.0.0 Beta
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Mysql.php
 */
class Guestbook_Model_Mysql extends Model
{

	/**
	 * @return array
	 */
	public function getGbData ()
	{

		$limit = Settings::get('guestbook.perpage', 10);
		$r     = $this->db->query('SELECT COUNT(id) AS total FROM %tp%guestbook')->fetch();

		$a = $limit * ((int)$this->input('page') > 0 ? (int)$this->input('page') - 1 : 0);

		return array (
			'result' => $this->db->query('SELECT b.*, IF(b.userid>0, u.username, b.`username`) AS `username`
                                               FROM %tp%guestbook AS b 
                                               LEFT JOIN %tp%users AS u ON(u.userid = b.userid)
                                               ORDER BY `timestamp` DESC 
                                               LIMIT ' . $a . ', ' . $limit)->fetchAll(),
			'total'  => $r[ 'total' ]
		);
	}

	/**
	 * @param $userid
	 * @return array
	 */
	public function getUserGbData ( $userid )
	{

		$limit = Settings::get('guestbook.perpage', 10);
		$r     = $this->db->query('SELECT COUNT(id) AS total FROM %tp%users_guestbook WHERE user_gbid = ?', $userid)->fetch();

		$a = $limit * ((int)$this->input('page') > 0 ? (int)$this->input('page') - 1 : 0);

		return array (
			'result' => $this->db->query('SELECT b.*, IF(b.uid>0, u.username, b.`username`) AS `username`, b.uid AS `userid`
                                                FROM %tp%users_guestbook AS b
                                                LEFT JOIN %tp%users AS u ON(u.userid = b.uid)
                                                WHERE b.user_gbid = ? ORDER BY b.`timestamp` DESC LIMIT ' . $a . ', ' . $limit, $userid)->fetchAll(),
			'total'  => $r[ 'total' ]
		);
	}

}

?>