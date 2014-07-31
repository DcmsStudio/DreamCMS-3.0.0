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
 * @package      User
 * @version      3.0.0 Beta
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Mysql.php
 */
class User_Model_Mysql extends Model
{

	/**
	 * @var null
	 */
	protected $where = null;

	/**
	 *
	 */
	public function __construct ()
	{

		parent::__construct();
	}

	/**
	 * @param int $userid
	 * @return type
	 */
	public function getUser ( $userid = 0 )
	{

		return $this->db->query('SELECT u.* FROM %tp%users AS u WHERE u.userid=?', $userid)->fetch();
	}

	/**
	 * @param int   $userid
	 * @param array $data
	 * @param null  $transdata
	 * @return int
	 */
	public function save ( $userid = 0, $data = array (), $transdata = null )
	{

		if ( $userid )
		{
			$this->db->update('%tp%users')->set($data)->where('userid', '=', $userid)->execute();
		}
		else
		{
			$this->db->insert('%tp%users')->values($data)->execute();
		}

		return $userid;
	}

	/**
	 *
	 * @param integer $userid
	 * @return array
	 */
	public function getProfileFieldsData ( $userid = 0 )
	{

		if ( !$userid )
		{
			return array ();
		}

		$user_profilefield_data = array ();

		$datas = $this->db->query('SELECT field_id, value FROM %tp%form_fielddata WHERE userid = ? AND rel = \'profilefield\'', $userid)->fetchAll();
		foreach ( $datas as $udata )
		{
			$user_profilefield_data[ $udata[ 'field_id' ] ] = $udata[ 'value' ];
		}

		return $user_profilefield_data;
	}

	/**
	 *
	 * @param integer $groupid
	 * @param integer $userposts
	 * @param integer $gender
	 * @return \type
	 */
	public function getRank ( $groupid = 0, $userposts = 0, $gender = 0 )
	{

		$sql = "SELECT * FROM %tp%users_ranks WHERE groupid IN (0," . (int)$groupid . ")
						AND needposts <= " . (int)$userposts . "
						AND gender IN (0," . (int)$gender . ")
						ORDER BY needposts DESC, gender DESC LIMIT 1";

		return $this->db->query($sql)->fetch();
	}

	/**
	 *
	 * @staticvar type $lasttype
	 * @staticvar type $and
	 * @staticvar type $or
	 * @staticvar type $not
	 * @param string $add
	 * @param string $type sql mod (OR, AND, NOT ...)
	 */
	public function add2where ( $add, $type = 'AND' )
	{

		static $lasttype;
		static $and;
		static $or;
		static $not;

		$currenttype = strtolower($type);

		if ( $currenttype == 'and' )
		{
			if ( !is_array($and) )
			{
				$and = array ();
			}
			$and[ ] = $add;
		}

		if ( $currenttype == 'or' )
		{
			if ( !is_array($or) )
			{
				$or = array ();
			}
			$or[ ] = $add;
		}

		if ( $currenttype == 'not' )
		{
			if ( !is_array($not) )
			{
				$not = array ();
			}
			$not[ ] = $add;
		}

		$and_sql = '';
		$and_c   = count($and);
		for ( $i = 0; $i <= $and_c; $i++ )
		{
			if ( !$and[ $i ] )
			{
				continue;
			}

			if ( $i == 0 )
			{
				$and_sql .= $and[ $i ];
			}
			else
			{
				$and_sql .= ' AND ' . $and[ $i ];
			}
		}

		if ( $and_sql )
		{
			$and_sql = '(' . $and_sql . ')';
		}


		$or_sql = '';
		$or_c   = count($or);
		for ( $i = 0; $i <= $or_c; $i++ )
		{
			if ( !$or[ $i ] )
			{
				continue;
			}

			if ( $i == 0 )
			{
				$or_sql .= $or[ $i ];
			}
			else
			{
				$or_sql .= ' OR ' . $or[ $i ];
			}
		}
		if ( $or_sql )
		{
			$or_sql = '(' . $or_sql . ')';
		}

		if ( $this->where )
		{
			$this->where = $and_sql . ( $or_sql && $and_sql ? ' AND ' : '' ) . $or_sql;
		}
		else
		{
			$this->where = $and_sql . ( $or_sql && $and_sql ? ' AND ' : '' ) . $or_sql;
		}

		$lasttype = $type;
	}

	/**
	 *
	 * @return string
	 */
	public function getWhere ()
	{

		return $this->where;
	}

	/**
	 *
	 * @param array /boolean $ids
	 * @return array
	 */
	public function getUsersForMailing ( $ids )
	{

		if ( $ids === true )
		{
			return $this->db->query('SELECT email, username FROM %tp%users ORDER BY username ASC')->fetchAll();
		}
		else
		{
			return $this->db->query('SELECT email, username FROM %tp%users WHERE userid IN(0,' . implode(',', $ids) . ') ORDER BY username ASC')->fetchAll();
		}
	}

	/**
	 *
	 * @param array $ids
	 * @return array
	 */
	public function findUsersById ( $ids )
	{

		return $this->db->query('SELECT * FROM %tp%users WHERE userid IN(0,' . implode(',', $ids) . ')')->fetchAll();
	}

	/**
	 * Block a User by ids
	 *
	 * @param boolean $block default is false
	 * @param array   $ids
	 */
	public function setBlocking ( $block = false, $ids )
	{

		$this->db->query('UPDATE %tp%users SET blocked = ? WHERE userid IN(0,' . implode(',', $ids) . ')', ( $block ? 1 : 0 ));
	}

	/**
	 *
	 * @param string /integer $userids
	 * @return \type
	 */
	public function getUserAccess ( $userids )
	{

		return $this->db->query('SELECT permissions, userid FROM %tp%users_access WHERE userid IN(0,' . $userids . ') ORDER BY LENGTH(permissions) DESC LIMIT 1')->fetch();
	}

	/**
	 *
	 * @param string /integer $userids
	 */
	public function removeUserAccess ( $userids )
	{

		$this->db->query("DELETE FROM %tp%users_access WHERE userid IN(0,$userids)");
	}

	/**
	 *
	 * @param      $userids
	 * @param bool $limit
	 * @return array|\type
	 * @internal param $string /integer $userids
	 */
	public function getUserPermission ( $userids, $limit = true )
	{

		$res = $this->db->query('SELECT u.userid, u.groupid, u.username, g.permissions FROM %tp%users AS u
                    LEFT JOIN %tp%users_groups AS g ON(g.groupid=u.groupid) WHERE u.userid IN(0,' . $userids . ') 
                    ORDER BY LENGTH(g.permissions) DESC' . ( $limit ? ' LIMIT 1' : '' ));

		if ( $limit )
		{
			return $res->fetch();
		}
		else
		{
			return $res->fetchAll();
		}
	}

	/**
	 *
	 * @param       $users
	 * @param array $groupperms
	 * @return bool
	 * @internal param array $userids
	 */
	public function saveUserAccess ( $users, $groupperms )
	{

		// Register all Application Perms
		# $apps = new Application();
		# $apps->registerPermissions();
		// Register all Plugins Perms
		Plugin::loadPluginPermissions();
		$permKeys = Permission::initFrontendPermissions();

		// Suche nicht vorhandere Felder in der Datenbank
		$k = HTTP::input('k');

		$postperm = $this->_post('perm');


		$x     = 0;
		$model = Model::getModelInstance('usergroups');

		foreach ( $users as $r )
		{

			// load group defaults
			$usergroup         = $model->getGroupByID($r[ 'groupid' ]);
			$groupDefaultPerms = ( isset( $usergroup[ 'permissions' ] ) && trim($usergroup[ 'permissions' ]) ? unserialize($usergroup[ 'permissions' ]) : array () );

			$serialize_field = array ();

			foreach ( $permKeys[ 'usergroup' ] as $key => $rows )
			{


				// the first row is the tab Label. also remove it
				array_shift($rows);

				$serialize_field[ $key ] = array ();

				foreach ( $rows as $fieldname => $field )
				{

					$fieldvalue = $postperm[ $key ][ $fieldname ];

					// only for the controller permissions
					if ( isset( $field[ 'isActionKey' ] ) && $field[ 'isActionKey' ] && isset( $postperm[ $key ][ $fieldname ] ) )
					{
						$actions[ 'perm' ][ $key ][ $fieldname ] = !empty( $fieldvalue ) ? (int)$fieldvalue : false;
					}

					// all others for the usergroup permissions
					if ( $fieldvalue == -1 )
					{
						$serialize_field[ $key ][ $fieldname ] = null;
						/*
						  if ( isset( $groupDefaultPerms[ $key ][ $fieldname ] ) )
						  {
						  $serialize_field[ $key ][ $fieldname ] = $groupDefaultPerms[ $key ][ $fieldname ];
						  }
						  else
						  {
						  $serialize_field[ $key ][ $fieldname ] = 0;
						  }
						 *
						 */
					}
					else
					{
						$serialize_field[ $key ][ $fieldname ] = $fieldvalue;

						if ( !empty( $field[ 'require' ] ) )
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
								$serialize_field[ $key ][ $fieldname ] = ( preg_match('/^([0-9]+)$/', trim($fieldvalue)) ? $fieldvalue : (string)$fieldvalue );
							}
							else
							{
								$serialize_field[ $key ][ $fieldname ] = $fieldvalue;
							}
						}
					}


					/*
					  if ( !isset( $fieldvalues[ $fieldname ] ) )
					  {
					  $serialize_field[ $key ][ $fieldname ] = 0;
					  continue;
					  }

					  if ( !isset( $fieldvalues[ $fieldname ] ) )
					  {
					  if ( isset( $groupperms[ $r[ 'groupid' ] ][ $key ][ $fieldname ] ) )
					  {
					  $serialize_field[ $key ][ $fieldname ] = $groupperms[ $r[ 'groupid' ] ][ $key ][ $fieldname ];
					  }
					  else
					  {
					  $serialize_field[ $key ][ $fieldname ] = 0;
					  }

					  continue;
					  }

					  $fieldvalue = $fieldvalues[ $fieldname ];

					  if ( !empty( $field[ 'require' ] ) )
					  {
					  $fieldvalue = ($fieldvalues[ $field[ 'require' ] ] ? $fieldvalue : null);
					  if ( $fieldvalue === null )
					  {
					  $serialize_field[ $key ][ $fieldname ] = 0;
					  continue;
					  }
					  }

					  if ( $fieldname != 'groupid' && $fieldname != 'title' && $fieldname != 'description' && $fieldname != 'dashboard' )
					  {
					  $serialize_field[ $key ][ $fieldname ] = (preg_match( '/^([0-9]+)$/', trim( $fieldvalue ) ) ?
					  (int)$fieldvalue  : $fieldvalue);
					  }
					 *
					 */
				}
			}

			$this->db->query('DELETE FROM %tp%users_access WHERE userid = ?', $r[ 'userid' ]);
			Cache::delete('menu_user_' . $r[ 'userid' ]);

			$fields = array (
				'permissions' => serialize($serialize_field),
				'userid'      => $r[ 'userid' ]
			);

			$string = $this->db->compile_db_insert_string($fields);
			$sql    = "INSERT INTO %tp%users_access (" . $string[ 'FIELD_NAMES' ] . ") VALUES(" . $string[ 'FIELD_VALUES' ] . ") ";
			$this->db->query($sql);
		}

		return true;
	}

	/**
	 *
	 * @param array  $data
	 * @param string $validateMode
	 * @return array
	 */
	public function validate ( $data, $validateMode )
	{

		$rules = array ();

		switch ( $validateMode )
		{
			case 'settings':

				// MSN is only a email format
				if ( $data[ 'msn' ] != '' )
				{
					$rules[ 'msn' ][ 'email' ] = array (
						'message' => trans('MSN Adresse ist nicht korrekt. Es wird eine Email Adresse erwartet'),
						'stop'    => true
					);
				}
				break;

			case 'password':

				$rules[ 'securecode' ][ 'required' ]  = array (
					'message' => trans('Sicherheitscode ist erforderlich'),
					'stop'    => true
				);
				$rules[ 'securecode' ][ 'identical' ] = array (
					'message' => trans('Sicherheitscode ist fehlerhaft'),
					'stop'    => true,
					'test'    => Session::get('site_captcha')
				);


				$rules[ 'oldpassword' ][ 'required' ] = array (
					'message' => trans('das alte Passwort ist erforderlich'),
					'stop'    => true
				);


				$rules[ 'password' ][ 'required' ] = array (
					'message' => trans('Passwort ist erforderlich'),
					'stop'    => true
				);


				$rules[ 'password' ][ 'nostars' ] = array (
					'message' => trans('Passwörter dürfen das Zeichen "*" nicht enthalten'),
					'stop'    => true,
					'test'    => $data[ 'passwordconfirm' ]
				);


				$rules[ 'password' ][ 'min_length' ] = array (
					'message' => sprintf(trans('Dein neues Passwort muss mind. %s Zeichen lang sein'), Settings::get('minuserpasswordlength', 5)),
					'test'    => Settings::get('minuserpasswordlength', 5)
				);

				$rules[ 'passwordconfirm' ][ 'required' ]   = array (
					'message' => trans('Bestätigungs Passwort ist erforderlich'),
					'stop'    => true
				);
				$rules[ 'passwordconfirm' ][ 'min_length' ] = array (
					'message' => sprintf(trans('Deine Passwort Wiederholung muss mind. %s Zeichen lang sein'), Settings::get('minuserpasswordlength', 5)),
					'test'    => Settings::get('minuserpasswordlength', 5)
				);


				$rules[ 'password' ][ 'identical' ] = array (
					'message' => trans('Passwörter sind nicht identisch'),
					'stop'    => true,
					'test'    => $data[ 'passwordconfirm' ]
				);


				// crypt old password
				if ( !empty( $data[ 'oldpassword' ] ) )
				{
					$phpass                = new PasswordHash();
					$data[ 'oldpassword' ] = $phpass->CheckPassword($data[ 'oldpassword' ], Session::get('password'));


					// $data[ 'oldpassword' ] = md5( $data[ 'oldpassword' ] );

					$rules[ 'oldpassword' ][ 'identical' ] = array (
						'message' => trans('die eingabe des alten Passworts ist fehlerhaft'),
						'stop'    => true,
						'test'    => true
					);
				}

				break;
			case 'signatur':

				break;
			case 'avatar':
				// allowedavatarextensions

				break;
			case 'other':

				break;
		}


		$validator = new Validation( $data, $rules );
		$errors    = $validator->validate();

		return $errors;
	}

	/**
	 *
	 * @param integer $userid
	 */
	public function updateProfileViews ( $userid )
	{

	}

	// ------- User Blog

	/**
	 * @param int $userid
	 */
	public function getUserBlock ( $userid )
	{

		$page  = $this->getCurrentPage();
		$limit = Settings::get('user/maxblog_items_perpage', 10);

		switch ( strtolower($this->input('orderby')) )
		{
			case 'hits':
				$order = 'b.hits';
				break;
			case 'date':
			default:
				$order = 'b.created';
				break;
		}

		switch ( strtolower($this->input('sortby')) )
		{
			case 'asc':
				$sort = 'ASC';
				break;
			case 'desc':
			default:
				$sort = 'DESC';
				break;
		}

		$rs    = $this->db->query('SELECT COUNT(id) AS total FROM %tp%users_blog_items WHERE userid = ? AND published = 1', $userid)->fetch();
		$pages = ceil($rs[ 'total' ] / $limit);
		$a     = $limit * ( $page - 1 );

		$sql = "SELECT b.*, u.username,
					IF(b.cancomment=1, COUNT(c.id), 0) AS comments
					FROM %tp%users_blog_items AS b
					LEFT JOIN %tp%users AS u ON(u.userid=b.userid)
					LEFT JOIN %tp%comments AS c ON(c.modul='blog' AND c.post_id=b.id)
					WHERE b.published=1 AND b.userid = ?
					GROUP BY b.id
					ORDER BY {$order} {$sort}
					LIMIT {$a}, {$limit}";

		return array (
			'result' => $this->db->query($sql, $userid)->fetchAll(),
			'total'  => $rs[ 'total' ]
		);
	}

	public function getBlogItemById ( $id, $mod = false )
	{

		if ( !$mod )
		{
			$sql = "SELECT b.*, u.username,
					IF(b.cancomment=1, COUNT(c.id), 0) AS comments
					FROM %tp%users_blog_items AS b
					LEFT JOIN %tp%users AS u ON(u.userid=b.userid)
					LEFT JOIN %tp%comments AS c ON(c.modul='blog' AND c.post_id=b.id)
					WHERE b.published=1 AND b.id = ?";

			return $this->db->query($sql, $id)->fetch();
		}
		else
		{
			$sql = "SELECT b.*, u.username,
					IF(b.cancomment=1, COUNT(c.id), 0) AS comments
					FROM %tp%users_blog_items AS b
					LEFT JOIN %tp%users AS u ON(u.userid=b.userid)
					LEFT JOIN %tp%comments AS c ON(c.modul='blog' AND c.post_id=b.id)
					WHERE b.userid = ? AND b.id = ?";

			return $this->db->query($sql, User::getUserId(), $id)->fetch();
		}
	}

	/**
	 * @param int   $id
	 * @param array $data
	 */
	public function saveBlogEntry ( $id = 0, $data = array () )
	{

		if ( $id )
		{
			$this->db->query('UPDATE %tp%users_blog_items
							  SET title = ?, content = ?, teaser = ?, cancomment = ?, permission = ?, published = ?, modifed = ?
							  WHERE id = ?', $data['title'], $data['content'], $data['teaser'], (int)$data['cancomment'], (int)$data['permission'], (int)$data['published'], TIMESTAMP , $id);

			return $id;
		}
		else
		{
			$this->db->query('INSERT INTO %tp%users_blog_items
							  (userid,title,content,teaser,created,modifed,cancomment,published,permission,hits,ip)
							  VALUES(?,?,?,?,?,?,?,?,?,?,?)', User::getUserId(), $data['title'], $data['content'], $data['teaser'], TIMESTAMP, 0,
							  (int)$data['cancomment'], (int)$data['published'], (int)$data['permission'], 0, $this->Env->ip() );

			return $this->db->insert_id();
		}


	}

}

?>