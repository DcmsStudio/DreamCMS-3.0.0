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
 * @package      Search
 * @version      3.0.0 Beta
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Mysql.php
 */
class Search_Model_Mysql extends Model
{

	/**
	 *
	 * @param string $hash
	 * @return integer
	 */
	public function scoreRelevance ( $hash )
	{

		return $this->db->query('SELECT COUNT(id) AS total, MAX(score) AS total_relevance
                    FROM %tp%search_spider
                    WHERE searchhash=?', $hash)->fetch();
	}

	/**
	 *
	 * @return integer
	 */
	public function countIndexedSites ()
	{

		$indexed = $this->db->query('SELECT COUNT(id) AS total FROM %tp%search_fulltext')->fetch();

		return (int)$indexed[ 'total' ];
	}

	/**
	 *
	 * @param string $hash
	 * @return integer
	 */
	public function countResults ( $hash )
	{

		$r = $this->db->query('SELECT COUNT(c.id) AS total
                       FROM %tp%search_spider AS s
                       LEFT JOIN %tp%search_fulltext AS c ON(c.id=s.indexid)
                       LEFT JOIN %tp%search_sections AS se ON(se.id=c.section_id)
                       WHERE s.searchhash = ?
                       GROUP BY c.id LIMIT 1', $hash)->fetch();

		return (int)$r[ 'total' ];
	}

	/**
	 *
	 * @param string  $hash
	 * @param integer $page
	 * @param integer $perPage
	 * @return array
	 */
	public function getResults ( $hash, $page = 0, $perPage = 20 )
	{

		$order = HTTP::input('order');
		$sort  = HTTP::input('sort');

		switch ( strtolower($sort) )
		{
			case 'asc':
				$sort = 'ASC';
				break;
			case 'desc':
			default:
				$sort = 'DESC';
				break;
		}

		switch ( strtolower($order) )
		{
			case 'title':
				$order    = 'title';
				$sqlorder = 'c.title';
				break;

			case 'date':
				$order    = 'date';
				$sqlorder = 'c.content_time';
				break;

			case 'relevance':
				$order    = 'relevance';
				$sqlorder = 's.score';
				break;
			default:
				$order    = 'relevance';
				$sqlorder = 's.score';
				break;
		}


		$add_limit = " LIMIT " . ($perPage * ($page - 1)) . "," . $perPage;


		$order_sql = " ORDER BY " . $sqlorder . ' ' . $sort;

		if ( $this->getApplication()->getMode() === Application::BACKEND_MODE )
		{
			$order_sql = " ORDER BY se.section_key ASC, " . $sqlorder . ' ' . $sort;
		}


		$sql = "SELECT s.id, c.title, c.content, c.content_time, c.content_bytes, c.controller, c.`action`, c.`contentid`, c.`appid`,
                       s.score, s.indexid, c.section_id, se.lang, se.section_key, s.matches, c.alias, c.suffix, se.location
                       FROM %tp%search_spider AS s
                       LEFT JOIN %tp%search_fulltext AS c ON(c.id=s.indexid)
                       LEFT JOIN %tp%search_sections AS se ON(se.id=c.section_id)
                       WHERE s.searchhash = ?
                       GROUP BY c.id
                       " . $order_sql . $add_limit;


		return $this->db->query($sql, $hash)->fetchAll();
	}

	/**
	 * @param int $id
	 * @return type
	 */
	public function getSearchedItem ( $id = 0 )
	{

		$sql = "SELECT s.id, c.title, c.content, c.content_time, c.content_bytes, c.controller, c.`action`, c.`contentid`, c.`appid`,
                       s.score, s.indexid, c.section_id, se.lang, se.section_key, s.matches, c.alias, c.suffix, se.location
                       FROM %tp%search_spider AS s
                       LEFT JOIN %tp%search_fulltext AS c ON(c.id=s.indexid)
                       LEFT JOIN %tp%search_sections AS se ON(se.id=c.section_id)
                       WHERE s.id = ?";

		return $this->db->query($sql, $id)->fetch();
	}

}

?>