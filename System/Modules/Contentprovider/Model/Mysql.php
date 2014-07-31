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
 * @package      Contentprovider
 * @version      3.0.0 Beta
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Mysql.php
 */
class Contentprovider_Model_Mysql extends Model
{

	/**
	 * @param bool $coretag
	 * @return array
	 */
	public function getGridQuery ( $coretag = false )
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
			case 'name':
				$order = " ORDER BY `name`";
				break;

			case 'description':
				$order = " ORDER BY description";
				break;

			case 'runnable':
				$order = " ORDER BY runnable";
				break;

			case 'order':
				$order = " ORDER BY execution_order";
				break;

			case 'title':
			default:
				$order = " ORDER BY title";
				break;
		}


		$cortag_where = ' WHERE iscoretag = 0';
		if ( $coretag )
		{
			$cortag_where = ' WHERE iscoretag = 1';
		}


		// get the total number of records
		$r     = $this->db->query('SELECT COUNT(id) AS total FROM %tp%provider' . $cortag_where . ' LIMIT 1')->fetch();
		$total = $r[ 'total' ];

		$limit = $this->getPerpage();
		$page  = $this->getCurrentPage();

		$query = '';


		return array (
			'result' => $this->db->query('SELECT * FROM %tp%provider' . $cortag_where . ' ' . $order . ' ' . $sort . " LIMIT " . ($limit * ($page - 1)) . "," . $limit)->fetchAll(),
			'total'  => $total
		);
	}

	/**
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getProvider ( $id = 0 )
	{

		return $this->db->query('SELECT * FROM %tp%provider WHERE id = ?', $id)->fetch();
	}

	/**
	 *
	 * @param $name
	 * @internal param string $id
	 * @return array
	 */
	public function getProviderByName ( $name )
	{

		return $this->db->query('SELECT * FROM %tp%provider WHERE name = ? LIMIT 1', $name)->fetch();
	}

	/**
	 *
	 * @param integer $id
	 * @param array   $data
	 * @param type    $transdata
	 * @internal param array $coredata
	 * @return integer
	 */
	public function save ( $id = 0, $data = array (), $transdata = null )
	{

		//$this->db->begin();
		if ( $id == 0 )
		{
			if ( !isset($data[ 'runnable' ]) )
			{
				$data[ 'execution_order' ] = '0';
			}
			else
			{
				$row                       = $this->db->query('SELECT MAX(execution_order) AS meo FROM %tp%provider')->fetch();
				$data[ 'execution_order' ] = $row[ 'meo' ] + 1;
			}
			unset($data[ 'id' ]);
			$id = $this->insert($data);
		}
		else
		{
			if ( !isset($data[ 'runnable' ]) )
			{
				$data[ 'execution_order' ] = '0';
			}

			$id = $this->update($data);
		}

		//$this->db->commit();

		return $id;
	}

	/**
	 * @param $data
	 * @return int
	 */
	private function insert ( $data )
	{

		$this->db->query('INSERT INTO %tp%provider SET
            name = ' . $this->db->quote($data[ 'name' ]) . ',
            title = ' . $this->db->quote($data[ 'title' ]) . ',
            type = ' . $this->db->quote($data[ 'type' ]) . ',
            description = ' . $this->db->quote($data[ 'description' ]) . ',
            runnable = ' . (isset($data[ 'runnable' ]) ? 1 : 0) . ',
            iscoretag = ' . (!empty($data[ 'iscoretag' ]) ? 1 : 0) . ',
            system = 0,
            execution_order = ' . (int)$data[ 'execution_order' ]);
		$id = $this->db->insert_id();
		Library::log(sprintf("Content Provider `%s` erstellt.", $data[ 'name' ]), $id);

		return $id;
	}

	/**
	 * @param $data
	 * @return mixed
	 */
	private function update ( $data )
	{

		$this->db->query('UPDATE %tp%provider SET
            name = ' . $this->db->quote($data[ 'name' ]) . ',
            type = ' . $this->db->quote($data[ 'type' ]) . ',
            title = ' . $this->db->quote($data[ 'title' ]) . ',
            description = ' . $this->db->quote($data[ 'description' ]) . ',
            runnable = ' . (isset($data[ 'runnable' ]) ? 1 : 0) . ',
            iscoretag = ' . (!empty($data[ 'iscoretag' ]) ? 1 : 0) . ',
            system = 0
            WHERE id = ' . $data[ 'id' ]);
		Library::log(sprintf("Content Provider `%s` aktualisiert. ", $data[ 'name' ]), $data[ 'id' ]);

		return $data[ 'id' ];
	}

}

?>