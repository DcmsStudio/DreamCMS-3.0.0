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
 * @package      Avatar
 * @version      3.0.0 Beta
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Mysql.php
 */
class Avatar_Model_Mysql extends Model
{

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
				$sort = " DESC";
				break;

			default:
				$sort = " ASC";
				break;
		}

		switch ( $GLOBALS[ 'orderby' ] )
		{
			case 'avatarname':
				$order = " ORDER BY a.avatarname";
				break;

			case 'needposts':
				$order = " ORDER BY a.needposts";
				break;

			case 'groupname':
				$order = " ORDER BY g.title";
				break;

			case 'published':
				$order = " ORDER BY a.published";
				break;
			case 'username':
				$order = " ORDER BY u.username";
				break;
			default:
				$order = " ORDER BY a.avatarname";
				break;
		}


		if ( HTTP::input('page') )
		{
			$page = (int)HTTP::input('page');
			if ( $page == "0" )
			{
				$page = "1";
			}
		}
		else
		{
			$page = "1";
		}

		$where = '';
		if ( (int)HTTP::input('groupid') )
		{
			$where .= ' a.groupid = ' . (int)HTTP::input('groupid');
		}


		$av    = $this->db->query('SELECT COUNT(a.avatarid) AS total FROM %tp%avatars AS a WHERE userid = 0' . ($where ?
				' AND ' . $where : ''))->fetch();
		$total = $av[ 'total' ];

		$limit = $this->getPerpage();
		$pages = ceil($total / $limit);

		$sql = "SELECT a.*, g.title AS grouptitle, u.username
                FROM %tp%avatars AS a
                LEFT JOIN %tp%users_groups AS g ON(g.groupid = a.groupid)
                LEFT JOIN %tp%users AS u ON (a.userid = u.userid)
                " . ($where ? 'WHERE ' . $where :
				'') . $order . $sort . " LIMIT " . ($limit * ($page - 1)) . "," . $limit;

		return array (
			'result' => $this->db->query($sql)->fetchAll(),
			'total'  => $total
		);
	}

	/**
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getAvatarByID ( $id )
	{

		return $this->db->query('SELECT * FROM %tp%avatars WHERE avatarid = ?', $id)->fetch();
	}

	/**
	 *
	 * @param integer $id
	 * @param array   $data
	 * @param null    $rest
	 * @return integer
	 */
	public function save ( $id = 0, $data = array (), $rest = null )
	{

		if ( !$id )
		{
			$this->db->query('INSERT INTO %tp%avatars (avatarname,avatarextension,groupid,needposts,userid, width, height)
                              VALUES(?,?,?,?,?,?,?)', $data[ 'avatarname' ], $data[ 'avatarextension' ], $data[ 'groupid' ], $data[ 'needposts' ], 0, $data[ 'width' ], $data[ 'height' ]);

			return $this->db->insert_id();
		}
		else
		{
			$this->db->query('UPDATE %tp%avatars SET groupid = ?, needposts = ? WHERE avatarid = ?', $data[ 'groupid' ], $data[ 'needposts' ], $id);

			return $id;
		}
	}

	/**
	 *
	 * @param string  $idKey
	 * @param string  $multiIdKey
	 * @param boolean $mode
	 */
	public function deleteAvatar ( $idKey, $multiIdKey, $mode = false )
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
				$result = $this->db->query("SELECT avatarid, avatarextension, avatarname, userid FROM %tp%avatars WHERE avatarid IN(0," . $data[ 'id' ] . ")")->fetchAll();

				$_labels = array ();
				foreach ( $result as $r )
				{
					$_labels[ ] = $r[ 'avatarname' ];
					$this->db->query('DELETE FROM %tp%avatars  WHERE avatarid = ?', $r[ 'avatarid' ]);

					if ( $r[ 'userid' ] )
					{
						$this->db->query('UPDATE %tp%users SET avatarid = 0 WHERE avatarid = ?', $r[ 'avatarid' ]);
					}

					if ( is_file(ROOT_PATH . HTML_URL . 'img/avatars/avatar-' . $r[ 'avatarid' ] . '.' . $r[ 'avatarextension' ]) )
					{
						@unlink(ROOT_PATH . HTML_URL . 'img/avatars/avatar-' . $r[ 'avatarid' ] . '.' . $r[ 'avatarextension' ]);
					}
				}

				Cache::delete('avatars');
				Library::log(sprintf("Deleting Avatars \"%s\".", implode(', ', $_labels)));
				Library::sendJson(true, trans('Avatare wurde erfolgreich gelöscht'));
				exit;
			}
			else
			{
				$r = $this->db->query("SELECT * FROM %tp%avatars WHERE avatarid = ?", $data[ 'id' ])->fetch();

				$this->db->query('DELETE FROM %tp%avatars  WHERE avatarid = ?', $data[ 'id' ]);
				if ( $r[ 'userid' ] )
				{
					$this->db->query('UPDATE %tp%users SET avatarid = 0 WHERE avatarid = ?', $r[ 'avatarid' ]);
				}

				if ( is_file(ROOT_PATH . HTML_URL . 'img/avatars/avatar-' . $r[ 'avatarid' ] . '.' . $r[ 'avatarextension' ]) )
				{
					@unlink(ROOT_PATH . HTML_URL . 'img/avatars/avatar-' . $r[ 'avatarid' ] . '.' . $r[ 'avatarextension' ]);
				}


				Cache::delete('avatars');

				Library::log("Has delete the Avatar `" . $r[ 'avatarname' ] . "`.");
				Library::sendJson(true, sprintf(trans('Avatar `%s` wurde erfolgreich gelöscht'), $r[ 'avatarname' ]));

				exit;
			}
		}
	}

	/**
	 *
	 * @param integer $id
	 */
	public function changePublish ( $id = 0 )
	{

		$r = $this->getAvatarByID($id);

		if ( !$r[ 'avatarid' ] )
		{
			Library::sendJson(false, trans('Das Avatar existiert nicht'));
		}

		$this->db->query('UPDATE %tp%avatars SET published = ? WHERE avatarid = ?', ($r[ 'published' ] ? 0 : 1), $id);
		Library::log('Has change the Avatar publishing to `' . ($r[ 'published' ] ? 'ON' :
				'OFF') . '` for avatar `' . $r[ 'avatarname' ] . '`.');

		Library::sendJson(true, ($r[ 'published' ] ? '0' : '1'));
	}

}

?>