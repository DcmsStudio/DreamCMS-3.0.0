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
 * @package      Widgets
 * @version      3.0.0 Beta
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Mysql.php
 */
class Widgets_Model_Mysql extends Model
{
	public function getInstalledWidgets() {
		return $this->db->query('SELECT * FROM %tp%widget ORDER BY title ASC')->fetchAll();
	}

	public function getAllUserWidgets() {
		return $this->db->query('SELECT uw.*, w.title, w.configurable, w.multiple, w.author, w.website, w.description, w.version FROM %tp%users_widget AS uw
								 LEFT JOIN %tp%widget AS w ON(w.widgetkey = uw.widget)
								 WHERE uw.userid = ? ORDER BY w.title ASC', User::getUserId())->fetchAll();
	}

	public function installUserWidget($data) {
		$this->db->query('INSERT INTO %tp%users_widget (userid,widget,config,col,pos,collapsible,top,`left`)
						  VALUES(?,?,?,?,?,?,?,?)', User::getUserId(), $data['widget'], '', 1, 1000, 0, 0, 0);

		return $this->db->insert_id();
	}

	/**
	 * @param int $id
	 */
	public function getWidgetByName( $name ) {
		return $this->db->query('SELECT * FROM %tp%users_widget WHERE widget = ? AND userid = ?', $name, User::getUserId())->fetch();
	}


	/**
	 * @param int $id
	 */
	public function getWidgetById( $id = 0) {
		return $this->db->query('SELECT * FROM %tp%users_widget WHERE id = ? AND userid = ?', $id, User::getUserId())->fetch();
	}

	/**
	 * @param     $id
	 * @param int $left
	 * @param int $top
	 */
	public function saveWidgetPos ( $id, $left = 0, $top = 0 )
	{
		$this->db->query('UPDATE %tp%users_widget SET `left`=?, `top`=? WHERE id = ? AND userid = ?', $left, $top, $id, User::getUserId());
	}
	/**
	 * @param     $id
	 * @param int $column
	 * @param int $order
	 */
	public function saveWidgetOrder ( $id, $column = 0, $order = 0 )
	{
		$this->db->query('UPDATE %tp%users_widget SET `col`=?, `pos`=? WHERE id = ? AND userid = ?', $column, $order, $id, User::getUserId());
	}

	/**
	 * @param int $id
	 * @param int $collapse
	 */
	public function setCollapse($id, $collapse){
		$this->db->query('UPDATE %tp%users_widget SET collapsible=? WHERE id = ? AND userid = ?', $collapse, $id, User::getUserId());
	}

	public function deleteWidget($id) {
		$this->db->query('DELETE FROM %tp%users_widget WHERE id = ? AND userid = ?', $id, User::getUserId());
	}
}

?>