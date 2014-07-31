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
 * @package      Logs
 * @version      3.0.0 Beta
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Mysql.php
 */
class Logs_Model_Mysql extends Model
{

	/**
	 *
	 */
	public function __construct ()
	{

		parent::__construct();
	}

	/**
	 * @param int $limit
	 * @return array
	 */
	public function getLogs ( $limit = 75, $isPanel = false )
	{

		$page = 1;

		$sql   = "SELECT COUNT(l.id) AS total FROM %tp%logs AS l
                LEFT JOIN %tp%users AS u ON(u.userid=l.userid) WHERE l.pageid=? OR l.pageid = 0 AND l.userid = 0";
		$r     = $this->db->query($sql, PAGEID)->fetch();
		$total = $r[ 'total' ];

		// get the total number of records
		$sql = "SELECT ". (!$isPanel ? 'l.*' : 'l.id,l.userid,l.time,l.action AS message,l.ip,l.browser') .", IF( l.logtype = 'warn', 'critical', logtype) AS logtype, u.username FROM %tp%logs AS l
                LEFT JOIN %tp%users AS u ON(u.userid=l.userid)
                WHERE l.pageid=? OR l.pageid = 0 AND l.userid = 0 ORDER BY l.time DESC LIMIT " . ($limit * ($page - 1)) . "," . $limit;


		return array (
			'result' => $this->db->query($sql, PAGEID)->fetchAll(),
			'total'  => $total
		);
	}

	/**
	 *
	 *
	 * @return array array('result', 'total')
	 */
	public function getGridQuery ()
	{

		$sort = ' ASC';

		switch ( $GLOBALS[ 'sort' ] )
		{
			case 'asc':
				$sort = " ASC";
				break;

			case 'desc':
			default:
				$sort = " DESC";
				break;
		}

		switch ( $GLOBALS[ 'orderby' ] )
		{
			case 'username':
				$order = " ORDER BY u.username";
				break;
			case 'logtype':
				$order = " ORDER BY IF( l.logtype = 'warn', 'critical', logtype)";
				break;
			case 'time':
				$order = " ORDER BY l.time";
				break;

			case 'message':
				$order = " ORDER BY l.message";
				break;
			case 'ip':
				$order = " ORDER BY l.ip";
				break;
			default:
				$order = " ORDER BY l.time";
				break;
		}


		$search = HTTP::input('q');
		$search = trim((string)strtolower($search));
		$all    = null;

		$_s = '';
		if ( $search != '' )
		{
			$search = $this->db->quote('%' . str_replace("*", "%", $search) . '%');

			switch ( HTTP::input('searchin') )
			{
				case 'username':
					$_s = "u.username LIKE " . $search;
					break;

				case 'message':
					$_s = "l.message LIKE " . $search;
					break;
				case 'ip':
					$_s = "l.ip LIKE " . $search;
					break;
				default:
					$_s = "( LOWER(l.message) LIKE " . $search;
					$_s .= "OR LOWER(u.username) LIKE " . $search;
					$_s .= "OR LOWER(l.ip) LIKE " . $search . ")";
					break;
			}
		}

		switch ( HTTP::input('logtype') )
		{
			case 'fe':
				$_logtype = " AND l.fb = 1" . ($_s ? ' AND ' : '');
				break;

			case 'be':
				$_logtype = " AND l.fb = 2" . ($_s ? ' AND ' : '');
				break;

			default:
				$_logtype = ($_s ? ' AND ' : '');
				break;
		}


		$limit = $this->getPerpage();
		$page  = $this->getCurrentPage();


		$sql   = "SELECT COUNT(l.id) AS total FROM %tp%logs AS l
                LEFT JOIN %tp%users AS u ON(u.userid=l.userid) WHERE l.pageid = ? OR l.pageid = 0 AND l.userid = 0 " . $_logtype . $_s;
		$r     = $this->db->query($sql, PAGEID)->fetch();
		$total = $r[ 'total' ];


		// get the total number of records
		$sql = "SELECT l.*, IF( l.logtype = 'warn', 'critical', logtype) AS logtype, u.username FROM %tp%logs AS l
                LEFT JOIN %tp%users AS u ON(u.userid=l.userid)
                WHERE l.pageid = ? OR l.pageid = 0 AND l.userid = 0 " . $_logtype . $_s . " " . $order . ' ' . $sort . " LIMIT " . ($limit * ($page - 1)) . "," . $limit;


		return array (
			'result' => $this->db->query($sql, PAGEID)->fetchAll(),
			'total'  => $total
		);
	}

}

?>