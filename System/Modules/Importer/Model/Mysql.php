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
 * @package      Indexer
 * @version      3.0.0 Beta
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Mysql.php
 */
class Importer_Model_Mysql extends Model
{

	/**
	 * @return array
	 */
	public function getData ()
	{

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
			case 'date':
				$order = " ORDER BY initdate";
				break;

			case 'xpath':
				$order = " ORDER BY xpath";
				break;

			case 'published':
				$order = " ORDER BY published";
				break;

			case 'initdate':
				$order = " ORDER BY initdate";
				break;

			case 'lastupdate':
				$order = " ORDER BY lastupdate";
				break;

			case 'filepath':
			default:
				$order = " ORDER BY filepath";

				break;
		}

		$where = array ();

		$where[ ] = ' pageid = ' . PAGEID;

		if ( HTTP::input('q') )
		{
			$search  = HTTP::input('q');
			$_search = trim((string)strtolower($search));
			if ( $_search != '' )
			{
				$_search  = str_replace("*", "%", str_replace("%", "\%", $_search));
				$where[ ] = " AND ( LOWER(filepath) LIKE " . $this->db->quote("%{$_search}%") . " OR LOWER(xpath) LIKE " . $this->db->quote("%{$_search}%") . ")";
			}
		}

		// get the total number of records
		$sql = "SELECT COUNT(id) AS total
                FROM %tp%importer
                WHERE " . (count($where) ? implode(' AND ', $where) : "");
		$r   = $this->db->query($sql)->fetch();

		$limit = $this->getPerpage();

		$query = "SELECT *
                    FROM %tp%importer
                    WHERE " . (count($where) ? implode(' AND ', $where) :
				"") . " GROUP BY id " . $order . ' ' . $sort . " LIMIT " . ($limit * ($this->getCurrentPage() - 1)) . "," . $limit;

		return array (
			'result' => $this->db->query($query)->fetchAll(),
			'total'  => $r[ 'total' ]
		);
	}

}

?>