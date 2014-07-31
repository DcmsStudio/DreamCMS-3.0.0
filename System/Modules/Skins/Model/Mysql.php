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
 * @package      Skins
 * @version      3.0.0 Beta
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Mysql.php
 */
class Skins_Model_Mysql extends Model
{

	/**
	 *
	 */
	public function __construct ()
	{

		parent::__construct();
	}

	public function getSkins ()
	{

		switch ( strtolower(HTTP::input('sort')) )
		{
			case "desc":
			default:
				$_sortby = " DESC";
				break;
			case "asc":
				$_sortby = " ASC";
				break;
		}


		switch ( $GLOBALS[ 'orderby' ] )
		{
			case 'title':
				$order = " ORDER BY title";
				break;

			case 'author':
				$order = " ORDER BY author";
				break;

			case 'default_set':
				$order = " ORDER BY default_set";
				break;

			default:
				$order = " ORDER BY title";
				break;
		}

		$rs = $this->db->query("SELECT * FROM %tp%skins WHERE pageid = ? " . $order . $_sortby, PAGEID)->fetchAll();
	}

	/**
	 *
	 * @param integer $skinid
	 * @param string  $groupname
	 * @return array
	 */
	public function listTemplates ( $skinid, $groupname = '' )
	{

		if ( $groupname != '' )
		{
			if ( $groupname == 'ROOT' )
			{
				$groupname = '';
			}

			$sql = "SELECT s.id, s.group_name, s.templatename, s.updated, s.modifie_by, u.username, x.title AS skintitle
                    FROM %tp%skins_templates AS s
                    LEFT JOIN %tp%skins AS x ON(x.id=s.set_id)
                    LEFT JOIN %tp%users AS u ON(u.userid=s.modifie_by)
                    WHERE s.set_id = ?  AND x.pageid = ? AND s.group_name = " . ($groupname != '' ?
					$this->db->quote($groupname) : "''");
			$sql .= " ORDER BY s.templatename ASC";
		}
		else
		{
			$sql = "SELECT s.id, s.group_name, s.templatename, s.updated, s.modifie_by, u.username, COUNT(s.set_id) AS totaltemplates, x.title AS skintitle
                    FROM %tp%skins_templates AS s
                    LEFT JOIN %tp%skins AS x ON(x.id=s.set_id)
                    LEFT JOIN %tp%users AS u ON(u.userid=s.modifie_by)
                    WHERE s.set_id = ? AND x.pageid = ? 
                    GROUP BY s.group_name 
                    ORDER BY s.group_name ASC, s.templatename ASC, s.updated DESC";
		}


		return $this->db->query($sql, $skinid, PAGEID)->fetchAll();
	}

	/**
	 *
	 * @return array
	 */
	public function getDefaultSkin ()
	{

		return $this->db->query('SELECT * FROM %tp%skins WHERE default_set = 1 AND pageid = ?', PAGEID)->fetch();
	}

	/**
	 * Change the default skin to $id
	 *
	 * @param integer $id
	 */
	public function updateDefaultSkin ( $id )
	{

		$this->db->query('UPDATE %tp%skins SET default_set = 0 WHERE default_set = 1 AND pageid = ?', PAGEID);
		$this->db->query('UPDATE %tp%skins SET default_set = 1 WHERE id = ? AND pageid = ?', $id, PAGEID);
	}

	/**
	 *
	 * @param integer $id
	 * @param string  $tpldir
	 */
	public function updateSkinAfterCreated ( $id, $tpldir )
	{

		$this->db->query('UPDATE %tp%skins SET templates = ? WHERE id = ? AND pageid = ?', $tpldir, $id, PAGEID);
	}

	/**
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getSkinByID ( $id )
	{

		return $this->db->query('SELECT * FROM %tp%skins WHERE id = ? AND pageid = ?', $id, PAGEID)->fetch();
	}

	/**
	 *
	 * @param array $data
	 * @return integer
	 */
	public function saveSkin ( $data )
	{

		$data[ 'default_set' ] = 0;
		$data[ 'pageid' ]      = PAGEID;

		$str = $this->db->compile_db_insert_string($data);
		$sql = "INSERT INTO %tp%skins ({$str['FIELD_NAMES']}) VALUES ({$str['FIELD_VALUES']})";
		$this->db->query($sql);

		return $this->db->insert_id();
	}

	/**
	 *
	 * @param integer $id
	 * @param array   $data
	 */
	public function updateSkin ( $id, $data )
	{

		$str = $this->db->compile_db_update_string($data);
		$this->db->query("UPDATE %tp%skins SET {$str} WHERE id = ? AND pageid = ?", $id, PAGEID);
	}

	/**
	 *
	 * @param integer $skinid
	 * @return array
	 */
	public function getTemplatesBySkinId ( $skinid )
	{

		return $this->db->query('SELECT group_name,templatename,content,plugin,iswidgettemplate ' . 'FROM %tp%skins_templates ' . 'WHERE set_id = ? ' . 'ORDER BY group_name ASC,templatename ASC', $skinid)->fetchAll();
	}

	/**
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getTemplateByID ( $id )
	{

		return $this->db->query('SELECT t.*, s.title AS skintitle, s.templates AS tpldir FROM %tp%skins_templates AS t
                LEFT JOIN %tp%skins AS s ON(s.id=t.set_id)
                WHERE t.id = ?', $id)->fetch();
	}

	/**
	 *
	 * @param string $groupname
	 * @param int    $skinid
	 * @return array
	 */
	public function getTemplatesByGroup ( $groupname, $skinid )
	{

		return $this->db->query('SELECT * FROM %tp%skins_templates WHERE group_name = ? AND set_id = ?', $groupname, $skinid)->fetchAll();
	}

	/**
	 *
	 * @param integer $id
	 * @param string  $newname
	 */
	public function renameTemplate ( $id, $newname )
	{

		$rs = $this->db->query('SELECT s.id, s.title AS skintitle, t.templatename
                                FROM %tp%skins_templates AS t 
                                LEFT JOIN %tp%skins AS s ON(s.id=t.set_id)
                                WHERE t.id = ?', $id)->fetch();

		$this->db->query('UPDATE %tp%skins_templates SET templatename = ? WHERE id = ?', $newname, $id);

		Library::log(sprintf('Template in Skin `%s` wurde von `%s` in `%s` umbenannt.', $rs[ 'skintitle' ], $rs[ 'templatename' ], $newname));
	}

	/**
	 *
	 * @param int $id
	 */
	public function deleteTemplate ( $id )
	{

		$this->db->query('DELETE FROM %tp%skins_templates WHERE id = ?', $id);
	}

	/**
	 *
	 * @param string $groupname
	 * @param int    $skinid
	 */
	public function deleteTemplatesByGroup ( $groupname, $skinid )
	{
		$this->db->query('DELETE FROM %tp%skins_templates WHERE group_name = ? AND set_id = ?', $groupname, $skinid);
	}


	public function processSkinImport ($data)
	{
		return $this->saveSkin($data);
	}


	public function processTemplateImport($skinid, $data)
	{
		$this->db->query('INSERT INTO %tp%skins_templates (set_id, group_name, templatename, content, updated, modifie_by, can_remove, moduleid, plugin, iswidgettemplate)
						  VALUES(?,?,?,?,?,?,?,?,?,?)', $skinid, $data['group_name'], $data['templatename'], $data['content'], 0, 0, 1, (int)$data['moduleid'], $data['plugin'],  (int)$data['iswidgettemplate'] );
	}


	public function deleteSkin( $id )
	{
		$this->db->query('DELETE FROM %tp%skins_templates WHERE set_id = ?', $id);
		$this->db->query('DELETE FROM %tp%skins WHERE id = ?', $id);
	}

}

?>