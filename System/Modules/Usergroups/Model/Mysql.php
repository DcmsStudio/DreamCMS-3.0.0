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
 * @package      Usergroups
 * @version      3.0.0 Beta
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Mysql.php
 */
class Usergroups_Model_Mysql extends Model
{

	/**
	 *
	 */
	public function __construct ()
	{

		parent::__construct();
	}

	/**
	 *
	 * @internal param int $cat_id
	 * @return array array('result', 'total')
	 */
	public function getGridQuery ()
	{

		$sort = ' ASC';
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

			case 'users':
				$order = " ORDER BY users";
				break;

			case 'dash':
				$order = " ORDER BY ug.dashboard";
				break;

			case 'title':
			default:
				$order = " ORDER BY ug.title";
				break;
		}

		$sql    = "SELECT ug.*, COUNT(u.userid) AS users
				FROM %tp%users_groups AS ug
				LEFT JOIN %tp%users AS u ON(u.groupid=ug.groupid)
				GROUP BY ug.groupid" . $order . " " . $sort;
		$result = $this->db->query($sql)->fetchAll();


		return array (
			'result' => $result,
			'total'  => count($result)
		);
	}

	/**
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getGroupByID ( $id )
	{

		return $this->db->query('SELECT * FROM %tp%users_groups WHERE groupid= ?', $id)->fetch();
	}

	/**
	 * @return type
	 */
	public function getDefaultGroup ()
	{

		return $this->db->query('SELECT * FROM %tp%users_groups WHERE default_group = 1')->fetch();
	}

	/**
	 *
	 * @param int   $groupid
	 * @param array $currentgroup
	 * @return integer|false
	 */
	public function saveGroup ( $groupid = 0, $currentgroup = array () )
	{

		$error = '';

		// Register all Application Perms
		#$apps = new Application();
		#$apps->registerPermissions();
		#
		// Suche nicht vorhandere Felder in der Datenbank
		$k        = $this->_post('k');
		$postperm = $this->_post('perm');

		$this->load('Action');
		$this->Action->loadPermissionOptions(true);


		$permKeys = Permission::initFrontendPermissions();

		$serialize_field = array ();
		$actions         = array ();

		foreach ( $permKeys[ 'usergroup' ] as $key => $rows )
		{
			// the first row is the tab Label. also remove it
			array_shift($rows);

			if ( !isset($serialize_field[ $key ]) )
			{
				$serialize_field[ $key ] = array ();
			}

			foreach ( $rows as $fieldname => $field )
			{

				$fieldvalue = $postperm[ $key ][ $fieldname ];

				// only for the controller permissions
				if ( isset($field[ 'isActionKey' ]) && $field[ 'isActionKey' ] )
				{
					$actions[ 'perm' ][ $key ][ $fieldname ] = !empty($fieldvalue) ? (int)$fieldvalue : false;
					$serialize_field[ $key ][ $fieldname ]   = $actions[ 'perm' ][ $key ][ $fieldname ];
				}
				else
				{

					// all others for the usergroup permissions
					if ( !isset($postperm[ $key ][ $fieldname ]) )
					{
						$serialize_field[ $key ][ $fieldname ] = 0;
					}
					else
					{
						// $fieldvalue                            = $fieldvalues[ $fieldname ];
						$serialize_field[ $key ][ $fieldname ] = $fieldvalue;

						if ( !empty($field[ 'require' ]) )
						{
							if ( $fieldvalue === null )
							{
								$serialize_field[ $key ][ $fieldname ] = 0;
							}
							else
							{
								$serialize_field[ $key ][ $fieldname ] = $fieldvalue;
							}
						}
						else
						{
							if ( $fieldname == 'allowedavatarextensions' || $fieldname == 'allowedattachmentextensions' )
							{
								$fieldvalue = preg_replace("/\s*\n\s*/", "\n", trim((string)$fieldvalue));
							}

							if ( $fieldname != 'groupid' && $fieldname != 'title' && $fieldname != 'description' && $fieldname != 'dashboard' )
							{
								$serialize_field[ $key ][ $fieldname ] = (preg_match('/^([0-9]+)$/', trim($fieldvalue)) ?
									$fieldvalue : (string)$fieldvalue);
							}
							else
							{
								$serialize_field[ $key ][ $fieldname ] = $fieldvalue;
							}
						}
					}
				}
			}
		}

		/*

		  // Unserialize all current permisssions to array
		  $currentgroup_perm = unserialize( $currentgroup[ 'permissions' ] );

		  $fp              = unserialize( $currentgroup_perm[ 'forumpermissions' ] );
		  $last_permission = (int)$currentgroup_perm[ 'groupforumpermissions' ] ;

		  $fperm = HTTP::input( 'fperm' );
		  if ( $fperm )
		  {
		  $foption = Permission::convert_array_to_bits( $fperm, Usergroup::getPermissionBitFields( 'forumpermissions' ) );
		  }



		  $fcache = array( );
		  $pcache = 0;


		  $result = $this->db->query( 'SELECT forumid FROM %tp%board ORDER BY forumid' )->fetchAll();
		  foreach ( $result as $r )
		  {
		  if ( $fp[ $r[ 'forumid' ] ] == $last_permission )
		  {
		  $fcache[ $r[ 'forumid' ] ] = $last_permission;
		  }
		  else
		  {
		  $fcache[ $r[ 'forumid' ] ] = ( int ) $foption;
		  }
		  }


		  $pcache = (int)$foption ;

		  $serialize_field[ 'groupforumpermissions' ] = $pcache;
		  $serialize_field[ 'forumpermissions' ]      = serialize( $fcache );

		 */


		if ( !isset($k[ 'title' ]) || !trim((string)$k[ 'title' ]) )
		{
			$error .= trans('Sie haben keinen Titel für die Benutzergruppe eingegeben!') . '<br/>';
		}

		if ( $error )
		{
			Error::raise($error);
			exit;
		}


		$fields = array (
			'permissions' => serialize($serialize_field),
			'title'       => $k[ 'title' ],
			'description' => $k[ 'description' ],
			'dashboard'   => (int)$k[ 'dashboard' ]
		);

		// $groupid = (int)HTTP::input('id');

		if ( $groupid )
		{

			if ( User::groupType() === 'administrator' )
			{
				$fields[ 'grouptype' ] = $k[ 'grouptype' ];
			}
			else
			{
				Library::sendJson(false, trans('Sie besitzen nicht die Rechte um den Benutzergruppen Typ zu ändern!'));
			}

			$this->load('Action');


			// Lösche Dashboard Access falls die Gruppe keinen Zugriff auf das Dashboard besitzt
			if ( !(int)$k[ 'dashboard' ] )
			{

				$this->Action->cleanUsergroupControllerPerms($groupid, true);

				// remove user private perms
				$users = $this->db->query('SELECT userid FROM %tp%users AS u
                                           LEFT JOIN %tp%users_groups AS g ON(g.groupid = u.groupid)
                                           WHERE g.dashboard = 1 AND g.groupid = ? AND g.pageid=?', $groupid, PAGEID)->fetchAll();

				foreach ( $users as $rs )
				{
					$this->Action->cleanUserControllerPerms($rs[ 'userid' ], true);
				}
			}


			// Save frontend action perm settings
			$this->Action->saveUsergroupControllerPerms($actions, $groupid, false);


			$string = $this->db->compile_db_update_string($fields);

			$this->db->query("UPDATE %tp%users_groups SET " . $string . " WHERE groupid = ?", $groupid);
			Settings::write();


			// Reload Session
			User::loadFromSession();
			Library::log('Edit the Usergroup: ' . $k[ 'title' ], 'warn');

			Cache::delete('groupactionperms-' . $groupid);
			Cache::delete('menu_user_' . User::getUserId());
			Cache::delete('default_usergroup');

			return $groupid;
		}
		else
		{

			$fields[ 'grouptype' ] = $k[ 'grouptype' ];
			$fields[ 'pageid' ]    = PAGEID;

			$string = $this->db->compile_db_insert_string($fields);
			$this->db->query("INSERT INTO %tp%users_groups (" . $string[ 'FIELD_NAMES' ] . ") VALUES(" . $string[ 'FIELD_VALUES' ] . ") ");
			$next_id = $this->db->insert_id();

			if ( !$next_id )
			{
				Error::raise('Group not addet!');
			}

			// Save frontend action perm settings
			$this->Action->saveUsergroupControllerPerms($actions, $next_id, false);

			#Settings::write();
			Cache::delete('groupactionperms-' . $next_id);
			Cache::delete('menu_user_' . User::getUserId());
			Cache::delete('default_usergroup');

			Library::log('Add new Usergroup: ' . $k[ 'title' ]);

			return $next_id;
		}


		return false;
	}

}
