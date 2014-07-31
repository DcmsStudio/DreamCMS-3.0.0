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
 * @package      Asset
 * @version      3.0.0 Beta
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Mysql.php
 */
class Asset_Model_Mysql extends Model
{

	/**
	 * @param int $id
	 * @return type
	 */
	public function getAssetById ( $id = 0 )
	{
		return $this->db->query('SELECT * FROM %tp%assets WHERE id = ?', $id)->fetch();
	}

	/**
	 * Models
	 */
	public function validate ( $data )
	{

		$rules = array ();

		$rules[ 'name' ][ 'required' ]   = array (
			'message' => trans('Asset name is a required field'),
			'stop'    => true
		);
		$rules[ 'name' ][ 'min_length' ] = array (
			'message' => trans('Asset name must be at least 4 characters long'),
			'test'    => 3
		);
		$rules[ 'name' ][ 'max_length' ] = array (
			'message' => trans('Asset name may not be longer than 50 characters'),
			'test'    => 50
		);
		$rules[ 'name' ][ 'unique' ]     = array (
			'message'  => trans('Asset name must be unique, there is already an asset with that name'),
			'table'    => 'assets',
			'id_field' => 'id'
		);
/*
		$rules[ 'url' ][ 'required' ]   = array (
			'message' => trans('Asset file name is a required field'),
			'stop'    => true
		);
		$rules[ 'url' ][ 'min_length' ] = array (
			'message' => trans('Asset file name must be at least 4 characters long'),
			'test'    => 3
		);
		$rules[ 'url' ][ 'max_length' ] = array (
			'message' => trans('Asset file name may not be longer than 50 characters'),
			'test'    => 50
		);
		$rules[ 'url' ][ 'unique' ]     = array (
			'message'  => trans('Asset file name must be unique, there is already an asset using that name'),
			'table'    => 'assets',
			'id_field' => 'id'
		);
*/
		$validator = new Validation($data, $rules);
		$errors    = $validator->validate();

		return $errors;
	}

	/**
	 * @param $data
	 * @return int
	 */
	public function insert ( $data )
	{

		$this->db->query('INSERT INTO %tp%assets SET
            pageid = ?,
            name = ?,
            description = ?,
            `type` = ?,
            url = ?,
            content = ?', PAGEID, $data[ 'name' ], $data[ 'description' ], $data[ 'type' ], $data[ 'url' ], $data[ 'content' ]);

		return $this->db->insert_id();
	}

	/**
	 * @param $data
	 * @return mixed
	 */
	public function update ( $data )
	{

		$this->db->query('UPDATE %tp%assets SET
            name = ?,
            description = ?,
            `type` = ?,
            url = ?,
            content = ?
            WHERE id = ?', $data[ 'name' ], $data[ 'description' ], $data[ 'type' ], $data[ 'url' ], $data[ 'content' ], $data[ 'id' ]);

		return $data[ 'id' ];
	}

}

?>