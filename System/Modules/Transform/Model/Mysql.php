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
 * @package      Transform
 * @version      3.0.0 Beta
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Mysql.php
 */
class Transform_Model_Mysql extends Model
{

	/**
	 * @return array
	 */
	public function getGridData ()
	{

		$limit = $this->getPerpage();
		$page  = $this->getCurrentPage();


		$a = $limit * ((int)$page > 0 ? (int)$page - 1 : 0);

		switch ( strtolower($GLOBALS[ 'sort' ]) )
		{
			case 'asc':
				$sort = " ASC";
				break;

			case 'desc':
				$sort = " DESC";
				break;

			default:
				$sort = "DESC";
				break;
		}

		switch ( strtolower($GLOBALS[ 'orderby' ]) )
		{
			case 'description':
				$order = " ORDER BY description";
				break;

			case 'title':
			default:
				$order = " ORDER BY title";
				break;
		}


		// get the total number of records
		$r = $this->db->query('SELECT COUNT(id) AS total FROM %tp%transform')->fetch();


		$sql = "SELECT * FROM %tp%transform LIMIT " . $a . "," . $limit;

		return array (
			'result' => $this->db->query($sql)->fetchAll(),
			'total'  => $r[ 'total' ]
		);
	}

	/**
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getTransformation ( $id = 0 )
	{

		$transformation = $this->db->query('SELECT * FROM %tp%transform WHERE id = ' . $id)->fetch();
		if ( $transformation )
		{
			$steps = $this->db->query('SELECT * FROM %tp%transform_steps WHERE t_id = ' . $id . ' ORDER BY `order`')->fetchAll();
			if ( $steps )
			{
				foreach ( $steps as $step )
				{
					$transformation[ 'steps' ][ ] = array (
						'id'   => $step[ 'id' ],
						'type' => $step[ 'type' ]
					);
				}
			}
		}

		return $transformation;
	}

	/**
	 *
	 * @param integer $id
	 * @return void
	 */
	public function getStep ( $id = 0 )
	{

		return $this->db->query('SELECT * FROM %tp%transform_steps WHERE id = ' . $id)->fetch();
	}

	/**
	 *
	 * @param array $ids
	 */
	public function deleteTransformations ( $ids )
	{

		foreach ( $ids as $id )
		{
			$data = $this->getTransformation($id);
			if ( $data )
			{
				$this->db->query("DELETE FROM %tp%transformation WHERE id = " . $id);
				$this->db->query("DELETE FROM %tp%transformation_step WHERE t_id = " . $id);
				Library::log(sprintf("Deleted transformation %s.", $data[ 'title' ]), 'warning');
			}
		}

		Cache::reload();
	}

	/**
	 *
	 * @param integer $id
	 * @return array
	 */
	public function removeTransform ( $id = 0 )
	{

		$data = $this->getTransformation($id);
		$this->db->query('DELETE FROM %tp%transform WHERE id = ?', $id);

		return $data;
	}

	/**
	 * @param $t_id
	 * @param $type
	 * @param $params
	 * @return int
	 */
	public function insertStep ( $t_id, $type, $params )
	{

		$res   = $this->db->query('SELECT MAX(`order`) AS max_order FROM %tp%transform_steps WHERE t_id = ' . $t_id)->fetch();
		$order = $res[ 'max_order' ] + 1;

		$this->db->query('INSERT INTO %tp%transform_steps
                SET t_id = ' . $t_id . ',
                    `type` = ' . $this->db->quote($type) . ',
                    `order` = ' . $order . ',
                    parameters = ' . $this->db->quote(serialize($params)));

		return $this->db->insert_id();
	}

	/**
	 * @param $id
	 * @param $type
	 * @param $params
	 */
	public function updateStep ( $id, $type, $params )
	{

		$this->db->query('UPDATE %tp%transform_steps SET `type` = ?, parameters = ? WHERE id = ?', $type, serialize($params), $id);
	}

	/**
	 * @return array
	 */
	public function loadMasks ()
	{

		$masks           = array ();
		$folder          = PUBLIC_PATH . 'img/masks/';
		$files           = glob($folder . '*.png');
		$transformations = array ();
		foreach ( $files as $file )
		{

			$file = str_replace($folder, '', str_replace('\\', '/', $file));
			$f    = explode('.', $file);

			$masks[ ] = array (
				'mask' => $f[ 0 ]
			);
		}

		return $masks;
	}

	public function insert($data)
	{
		$this->db->query('INSERT INTO %tp%transform SET
            title = ?,
            description = ?', $data['name'], $data['description']
		);
		return $this->db->insert_id();
	}

	public function update($data)
	{
		$this->db->query('UPDATE %tp%transform SET
            title = ?,
            description = ?
            WHERE id = ?', $data['name'], $data['description'], $data['id']
		);

		return $data['id'];
	}

	public function updateStepOrder($data)
	{
		if (!empty($data['step']))
		{
			$steps = explode(',', $data['step']);
			foreach ($steps as $order => $step)
			{
				$step = (int)str_replace('step_', '', $step);
				$this->db->query('UPDATE %tp%transform_steps SET `order` = ' . ($order + 1) . ' WHERE id = ' . $step);
			}
		}
	}


}

?>