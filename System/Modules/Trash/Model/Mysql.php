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
 * @package      Trash
 * @version      3.0.0 Beta
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Mysql.php
 */
class Trash_Model_Mysql extends Model
{

	/**
	 * @var null
	 */
	private $trashTable = null;

	/**
	 * @var null
	 */
	private $trashTableLabel = null;

	/**
	 * @var null
	 */
	private $trashTableData = null;

	/**
	 * @var null
	 */
	private $trashTableTransData = null;

	/**
	 *
	 * @return array
	 */
	public function getGridData ()
	{

		switch ( $GLOBALS[ 'sort' ] )
		{
			case 'asc':
				$sort = " ASC";
				break;

			case 'desc':
				$sort = " DESC";
				break;

			default:
				$sort = " DESC";
				break;
		}

		switch ( $GLOBALS[ 'orderby' ] )
		{
			case 'label':
				$order = " ORDER BY t.typelabel";
				break;
			case 'datalabel':
				$order = " ORDER BY t.datalabel";
				break;
			case 'username':
				$order = " ORDER BY u.username";
				break;
			case 'date':
			default:
				$order = " ORDER BY t.deletedate";
				break;
		}


		$search = '';
		$search = HTTP::input('q');
		$search = trim((string)strtolower($search));

		$_s = '';
		if ( $search != '' )
		{
			$search = $this->db->quote('%' . str_replace("*", "%", $search) . '%');
			$_s     = "(t.label LIKE {$search} OR t.data LIKE {$search})";
		}


		// get the total number of records
		$sql = "SELECT COUNT(t.trashid) AS total FROM %tp%trash AS t
                LEFT JOIN %tp%users AS u ON(u.userid=t.userid)
                WHERE t.pageid = ?" . ($_s ? " AND  " . $_s : '');
		$r   = $this->db->query($sql, PAGEID)->fetch();

		$total = $r[ 'total' ];
		$limit = $this->getPerpage();
		$page  = $this->getCurrentPage();


		$query = "SELECT t.typelabel, t.datalabel, t.userid, t.trashid, t.deletedate, u.username FROM %tp%trash AS t
                LEFT JOIN %tp%users AS u ON(u.userid=t.userid)
                WHERE t.pageid = ?" . ($_s ? " AND  " . $_s :
				'') . ' ' . $order . ' ' . $sort . ' LIMIT ' . ($limit * ($page - 1)) . "," . $limit;

		return array (
			'result' => $this->db->query($query, PAGEID)->fetchAll(),
			'total'  => $total
		);
	}

}
