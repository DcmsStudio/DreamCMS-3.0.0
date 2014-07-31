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
 * @package      Component
 * @version      3.0.0 Beta
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Mysql.php
 */
class Component_Model_Mysql extends Model
{

	/**
	 *
	 * @return array
	 */
	public function getComponents ()
	{

		return $this->db->query("SELECT com.*, comc.name AS cat_name, comc.id AS cat_id, comc.description AS cat_description, comc.system AS system
                                  FROM %tp%component AS com
                                  LEFT JOIN %tp%component_category AS comc ON(comc.id=com.category) ORDER BY comc.display_order ASC,comc.name ASC")->fetchAll();
	}

	/**
	 *
	 * @return array
	 */
	public function getCatComponents ()
	{

		return $this->db->query("SELECT comc.* FROM %tp%component_category AS comc
                                  ORDER BY comc.display_order ASC,comc.name ASC")->fetchAll();
	}

	/**
	 *
	 * @return array
	 */
	public function getCategories ()
	{

		return $this->db->query("SELECT * FROM %tp%component_category ORDER BY display_order ASC")->fetchAll();
	}

	/**
	 *
	 * @return array
	 */
	public function getCategoryCounts ()
	{

		$rows   = $this->db->query('SELECT category, COUNT(*) AS counted FROM %tp%component GROUP BY category')->fetchAll();
		$counts = array ();
		foreach ( $rows as $row )
		{
			$counts[ $row[ 'category' ] ] = $row[ 'counted' ];
		}

		return $counts;
	}

	/**
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getCategory ( $id = 0 )
	{

		return $this->db->query("SELECT * FROM %tp%component_category WHERE id = ?", $id)->fetch();
	}

	/**
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getComponent ( $id = 0 )
	{

		return $this->db->query("SELECT * FROM %tp%component WHERE id = ?", $id)->fetch();
	}

	/**
	 *
	 * @param string  $name
	 * @param integer $id
	 * @return array
	 */
	public function componentExists ( $name, $id )
	{

		return $this->db->query("SELECT * FROM %tp%component WHERE name = ? AND id != ?", $name, $id)->fetch();
	}

	/**
	 *
	 * @param array $data
	 * @return integer
	 */
	public function saveComponente ( $data )
	{

		$this->db->begin();
		if ( $data[ 'id' ] == 0 )
		{
			$id = $this->insertCom($data);
			Library::log(sprintf("Created component %s.", $data[ 'name' ]));
		}
		else
		{
			$id = $this->updateCom($data);
			Library::log(sprintf("Edited component %s.", $data[ 'name' ]));
		}

		$data[ 'id' ] = $id;

		$this->db->commit();

		Cache::refresh();

		return $data[ 'id' ];
	}

	/**
	 *
	 * @param array $data
	 * @return integer
	 */
	private function insertCom ( $data )
	{

		$this->db->query('INSERT INTO %tp%component SET
            name = ?,
            description = ?,
            category = ?,
            component = ?', $data[ 'name' ], $data[ 'description' ], $data[ 'category' ], $data[ 'component' ]);

		return $this->db->insert_id();
	}

	/**
	 *
	 * @param array $data
	 * @return integer
	 */
	private function updateCom ( $data )
	{

		$old_data = $this->getComponent($data[ 'id' ]);
		$this->db->query('UPDATE %tp%component SET
            name = ?,
            description = ?,
            category = ?,
            component = ?
            WHERE id = ?', $data[ 'name' ], $data[ 'description' ], $data[ 'category' ], $data[ 'component' ], $data[ 'id' ]);

		$path = SystemManager::getComponentPath($old_data[ 'name' ]);
		unlink($path);


		// update component hooks
		$res = $this->db->query('UPDATE %tp%event_hook SET `handler` = ? WHERE `type`=\'component\' AND `handler` = ?', $data[ 'name' ], $old_data[ 'name' ]);

		return $data[ 'id' ];
	}

	/**
	 *
	 * @param array $data
	 * @return integer
	 * @throws BaseException
	 */
	public function saveCategory ( $data )
	{

		$id          = !empty($data[ 'id' ]) ? $data[ 'id' ] : 0;
		$name        = $data[ 'name' ];
		$description = strip_tags($data[ 'description' ]);

		if ( !preg_match('/^[-a-z0-9_ \'\,\.]{0,50}$/i', $name) )
		{
			throw new BaseException(trans('The supplied name is not valid.'));
		}

		if ( $id == 0 )
		{
			$this->db->query('INSERT INTO %tp%component_category SET name = ?, description = ?, system = 0, display_order = 0', $name, $description);
		}
		else
		{
			$this->db->query('UPDATE %tp%component_category SET name = ?, description = ? WHERE id = ?', $name, $description, $id);
		}

		return $id;
	}

	/**
	 *
	 * @param array $data
	 */
	public function updateCategoryOrder ( $data )
	{

		$_data = explode(',', $data);

		foreach ( $_data as $order => $id )
		{
			if ( !$id )
			{
				continue;
			}
			$this->db->query("UPDATE %tp%component_category SET display_order = $order WHERE id = $id");
		}
	}

	/**
	 *
	 * @param array $ids
	 * @return array
	 */
	public function deleteComponents ( $ids )
	{

		$this->db->begin();
		foreach ( $ids as $id )
		{
			$data = $this->getComponent($id);

			if ( $data )
			{

				$path = SystemManager::getComponentPath($data[ 'name' ]);
				unlink($path);


				$this->db->query("DELETE FROM %tp%component WHERE id = ?", $id);
				$data[ 'component' ] = '[Suppressed]';
				Library::log(sprintf("Deleted component %s.", $data[ 'name' ]), $id, 'warning', $data);
			}
		}
		$this->db->commit();

		return $data;
	}

	/**
	 *
	 * @param integer $id
	 * @return array
	 * @throws BaseException
	 */
	public function deleteCategory ( $id )
	{

		$cat = $this->getCategory($id);

		if ( $cat[ 'system' ] == 1 )
		{
			throw new BaseException(trans('System-owned categories cannot be deleted.'));
		}

		$count = $this->db->query('SELECT COUNT(*) AS counted FROM %tp%component WHERE category = ?', $id)->fetch();

		//$count = $this->db->select('COUNT(*) AS counted')->from(array('%tp%component'))->where('category', array($id));
		if ( $count[ 'counted' ] > 0 )
		{
			throw new BaseException(trans('Categories containing components cannot be deleted.'));
		}

		$this->db->query('DELETE FROM %tp%component_category WHERE id = ?', $id);
		Library::log(sprintf("Deleted component category %s.", $cat[ 'name' ]), $id, 'warning', $cat);

		return $cat;
	}

}

?>