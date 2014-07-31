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
 * @package      Plugin
 * @version      3.0.0 Beta
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Mysql.php
 */
class Plugin_Model_Mysql extends Model
{

	/**
	 * @param int $id
	 * @return type
	 */
	public function getSearchedItem ( $id = 0 )
	{

		return $this->db->query("SELECT p.* FROM %tp%board_posts AS p WHERE p.postid = ?", $id)->fetch();
	}

	/**
	 * @return array
	 */
	public function getInstalledPlugins ()
	{

		$installed = array ();
		$res       = $this->db->query('SELECT `key` FROM %tp%plugin')->fetchAll();
		foreach ( $res as $row )
		{
			$installed[ ] = $row[ 'key' ];
		}

		return $installed;
	}

	/**
	 *
	 * @param array $data
	 * @return integer
	 */
	public function installPlugin ( $data )
	{

		$str = $this->db->compile_db_insert_string($data);
		$this->db->query("INSERT INTO %tp%plugin ({$str['FIELD_NAMES']}) VALUES ({$str['FIELD_VALUES']})");
		Library::log('Install the Plugin ' . $data[ 'name' ]);

		$this->Event->trigger('install.plugin');

		return $this->db->insert_id();
	}

	/**
	 *
	 * @param string $name
	 */
	public function uninstallPlugin ( $name )
	{

		$this->db->begin();
		$this->db->query("DELETE FROM %tp%plugin WHERE `key` = ?", $name);
		$this->db->query("DELETE FROM %tp%plugin_setting WHERE `plugin` = ?", $name);
		$this->db->commit();

		$this->Event->trigger('remove.plugin');
	}

	/**
	 * @return array
	 */
	public function getPlugins ()
	{

		return $this->db->query('SELECT * FROM %tp%plugin WHERE run = 1')->fetchAll();
	}

	/**
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getPluginById ( $id )
	{

		return $this->db->query('SELECT * FROM %tp%plugin WHERE id = ?', $id)->fetch();
	}

	/**
	 *
	 * @param string $key
	 * @return array
	 */
	public function getPluginByName ( $key )
	{

		return $this->db->query('SELECT * FROM %tp%plugin WHERE `key` = ?', $key)->fetch();
	}

	/**
	 *
	 * @param string $key
	 * @return array
	 */
	public function getPluginSettings ( $key )
	{

		return $this->db->query('SELECT * FROM %tp%plugin_setting WHERE pluginkey = ?', $key)->fetchAll();
	}

	/**
	 * @return array
	 */
	public function getGridData ()
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
			case "description":
				$_orderby = "description";
				break;

			case "version":
				$_orderby = "`version`";
				break;

			case "author":
				$_orderby = "author";
				break;


			case "name":
			default:
				$_orderby = "`name`";
				break;
		}


		$rs = $this->db->query('SELECT COUNT(*) AS total FROM %tp%plugin')->fetch();

		$limit = $this->getPerpage();
		$page  = $this->getCurrentPage();

		return array (
			'result' => $this->db->query("SELECT * FROM %tp%plugin ORDER BY {$_orderby} {$sort} LIMIT " . ($limit * ($page - 1)) . "," . $limit)->fetchAll(),
			'total'  => $rs[ 'total' ]
		);
	}

	/**
	 * @param      $idKey
	 * @param      $multiIdKey
	 * @param bool $mode
	 */
	public function publishPlugin ( $idKey, $multiIdKey, $mode = false )
	{

		$data = $this->getMultipleIds($idKey, $multiIdKey);

		if ( !$data[ 'id' ] && !$data[ 'isMulti' ] )
		{
			Error::raise("Invalid ID");
		}

		if ( !$mode )
		{
			if ( $data[ 'isMulti' ] )
			{
				$result = $this->db->query("SELECT published, name FROM %tp%plugin
                                            WHERE id IN(0," . $data[ 'id' ] . ") GROUP BY id")->fetchAll();

				foreach ( $result as $r )
				{
					if ( ACTION == 'Unpublish' || ACTION == 'Unarchive' )
					{
						$state = UNPUBLISH_MODE;
					}

					if ( ACTION == 'Publish' )
					{
						$state = PUBLISH_MODE;
					}

					if ( ACTION == 'Archive' )
					{
						$state = ARCHIV_MODE;
					}

					$this->db->query('UPDATE %tp%plugin SET published=? WHERE id=?', '' . $state, $r[ 'id' ]);
					Library::log("Change Plugin publishing \"{$r['name']}\" to status {$state} (ID:{$r['id']}).");
				}

				$this->Event->trigger('publish.plugin');
				Cache::delete( 'installed_plugins' );
				Cache::delete( 'interactive_plugins' );
				Library::sendJson(true, '' . $state);
				exit;
			}
			else
			{
				if ( HTTP::input('s') )
				{
					$state = UNPUBLISH_MODE;

					if ( HTTP::input('s') == 'publish' )
					{
						$state = PUBLISH_MODE;
					}

					if ( HTTP::input('s') == 'unpublish' )
					{
						$state = UNPUBLISH_MODE;
					}

					if ( HTTP::input('s') == 'archive' )
					{
						$state = ARCHIV_MODE;
					}

					if ( HTTP::input('s') == 'unarchive' )
					{
						$state = UNPUBLISH_MODE;
					}

					$r = $this->db->query("SELECT published, name FROM %tp%plugin
                        WHERE id=?", $data[ 'id' ])->fetch();
				}
				else
				{
					$r     = $this->db->query("SELECT published, name FROM %tp%plugin
                        WHERE id=?", $data[ 'id' ])->fetch();
					$state = ($r[ 'published' ] ? UNPUBLISH_MODE : PUBLISH_MODE);
				}

				$this->db->query('UPDATE %tp%plugin SET published=? WHERE id=?', $state, $data[ 'id' ]);

				$this->Event->trigger('publish.plugin');
				Cache::delete( 'installed_plugins' );
				Cache::delete( 'interactive_plugins' );



				Library::log("Change Plugin \"{$r['name']}\" publishing to status {$state} (ID:{$id}).");
				Library::sendJson(true, '' . $state);
				exit;
			}
		}
	}

}

?>