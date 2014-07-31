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
 * @package      Tags
 * @version      3.0.0 Beta
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Mysql.php
 */
class Tags_Model_Mysql extends Model
{

	/**
	 *
	 * @param string $tagname
	 * @param string $hash default is null
	 */
	public function updateTagHits ( $tagname, $hash = null )
	{

		if ( is_string($hash) && $hash != '' )
		{
			$this->db->query('UPDATE %tp%tags SET hits = hits+1 WHERE tablehash = ?', $hash);
		}
		else
		{
			$this->db->query('UPDATE %tp%tags SET hits = hits+1 WHERE tag = ?', $tagname);
		}
	}

	/**
	 * @return array
	 */
	public function getGridData ()
	{

		$limit = $this->getPerpage();
		$page  = $this->getCurrentPage();

		$a = $limit * ((int)$page > 0 ? (int)$page - 1 : 0);

		switch ( strtolower($GLOBALS[ 'orderby' ]) )
		{
			case 'hits':
				$order = " ORDER BY hits ";
				break;
			case 'tag':
			default:
				$order = " ORDER BY tag ";
				break;
		}

		switch ( strtolower($GLOBALS[ 'sort' ]) )
		{
			case 'asc':
				$sort = 'ASC';
				break;
			case 'desc':
			default:
				$sort = 'DESC';
				break;
		}

		$r = $this->db->query('SELECT COUNT(*) AS total FROM %tp%tags WHERE pageid = ?', PAGEID)->fetch();

		return array (
			'result' => $this->db->query('SELECT * FROM %tp%tags WHERE pageid = ? ' . $order . $sort . ' LIMIT ' . $a . ', ' . $limit, PAGEID)->fetchAll(),
			'total'  => $r[ 'total' ]
		);
	}

}

?>