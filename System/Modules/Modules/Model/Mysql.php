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
 * @package      Model
 * @version      3.0.0 Beta
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Mysql.php
 */
class Modules_Model_Mysql extends Model
{

	/**
	 * @return array
	 */
	public function getData ()
	{

		$search = '';
		$search = HTTP::input('q');
		$search = trim((string)strtolower($search));
		$all    = null;

		$_s = '';
		$_s = 'pageid = ' . PAGEID;
		if ( $search != '' )
		{
			$search = $this->db->quote('%' . str_replace("*", "%", $search) . '%');

			switch ( HTTP::input('searchin') )
			{
				case 'name':
					$_s .= " AND module LIKE " . $search;
					break;
				case 'metatables':
					$_s .= " AND metatables LIKE " . $search;
					break;
				default:
					$_s .= " AND ( LOWER(module) LIKE " . $search;
					$_s .= "OR LOWER(metatables) LIKE " . $search . ")";
					break;
			}
		}


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
			case 'module':
				$order = " ORDER BY module";
				break;
			case 'version':
				$order = " ORDER BY `version`";
				break;
			case 'published':
				$order = " ORDER BY published";
				break;
			default:
				$order = " ORDER BY module";
				break;
		}

		// get the total number of records
		$r = $this->db->query('SELECT COUNT(id) AS total FROM %tp%module WHERE ' . $_s)->fetch();

		$total = $r[ 'total' ];
		$limit = $this->getPerpage();
		$page  = $this->getCurrentPage();


		$result = $this->db->query('SELECT * FROM %tp%module WHERE ' . $_s . ' GROUP BY id' . $order . $sort . ' LIMIT ' . ($limit * ($page - 1)) . "," . $limit)->fetchAll();


		return array (
			'result' => $result,
			'total'  => $r[ 'total' ]
		);
	}

	public function getInstalled ()
	{

		return $this->db->query('SELECT * FROM %tp%module WHERE pageid = ?', PAGEID)->fetchAll();
	}

	/**
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getModulById ( $id )
	{

		return $this->db->query('SELECT * FROM %tp%module WHERE id = ?', $id)->fetch();
	}

	/**
	 *
	 * @param array $data
	 */
	public function uninstall ( $data )
	{
		$this->db->query('DELETE FROM %tp%module WHERE id = ?', $data[ 'id' ]);
		$this->db->query('DELETE FROM %tp%actions WHERE controller = ?', $data[ 'module' ]);
	}

}

?>