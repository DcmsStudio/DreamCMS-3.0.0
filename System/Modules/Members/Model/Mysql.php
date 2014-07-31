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
 * @package      Members
 * @version      3.0.0 Beta
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Mysql.php
 */
class Members_Model_Mysql extends Model
{

	/**
	 * @var int
	 */
	private $limit = 20;

	/**
	 *
	 */
	public function __construct ()
	{

		parent::__construct();
	}

	/**
	 * @return array
	 */
	public function getMembers ()
	{

		$page  = (!(int)HTTP::input('page') ? 1 : (int)HTTP::input('page'));
		$q     = (HTTP::input('q') ? HTTP::input('q') : null);
		$order = (!HTTP::input('order') ? 'username' : HTTP::input('order'));
		$sort  = (!HTTP::input('sort') ? 'ASC' : HTTP::input('sort'));

		switch ( strtolower($order) )
		{
			case 'username':
			default:
				$_sort = ' u.username';
				break;
			case 'posts':
				$_sort = ' u.userposts';
				break;
			case 'lastvisit':
				$_sort = ' u.lastpost';
				break;
			case 'firstname':
				$_sort = ' u.`name`';
				break;
			case 'lastpost':
				$_sort = ' u.lastpost_timestamp';
				break;
		}

		switch ( strtolower($sort) )
		{
			case 'asc':
			default:
				$_order = ' ASC';
				break;
			case 'desc':
				$_order = ' DESC';
				break;
		}

		$avatars     = 'avatar.avatarname,avatar.avatarextension,avatar.width AS avatarwidth,
                    avatar.height AS avatarheight,avatar.userid AS avatarowner,
                    NOT ISNULL(customavatar.avatarname) AS hascustomavatar';
		$avatarsjoin = "LEFT JOIN %tp%avatars AS avatar ON(avatar.avatarid = u.avatarid)
                        LEFT JOIN %tp%avatars AS customavatar ON(customavatar.userid = u.userid) ";


		$rs          = $this->db->query('SELECT COUNT(userid) AS total FROM %tp%users' . ($db_search ?
				'WHERE ' . $db_search : ''))->fetch();
		$this->limit = Settings::get('membersperpage', $this->limit);

		$pages = ceil($rs[ 'total' ] / $this->limit);
		$a     = $this->limit * ($page - 1);

		if ( $rs[ 'total' ] > 0 )
		{
			$page  = (int)$this->input('page') ? (int)$this->input('page') : 1;
			$pages = ceil($rs[ 'total' ] / $this->limit);

			$this->load('Paging');
			$url = $this->Paging->generate(array (
			                                     'controller' => 'members',
			                                     'action'     => 'index',
			                                     'order'      => $order,
			                                     'sort'       => $sort,
			                                     'q'          => ''
			                               ));
			$this->Paging->setPaging($url, $page, $pages);
		}


		return array (
			'total'  => $rs[ 'total' ],
			'result' => $this->db->query('SELECT u.*,' . $avatars . '
                                                FROM %tp%users AS u
                                                LEFT JOIN %tp%users_ranks AS r ON(r.rankid = u.rankid)
                                                ' . $avatarsjoin . ($db_search ? ' WHERE ' . $db_search : '') . '
                                                GROUP BY u.userid
                                                ORDER BY ' . $_sort . $_order . ' LIMIT ' . $a . ',' . $this->limit, $_sort, $_order)->fetchAll()
		);
	}

	/**
	 * @return string
	 */
	public function getLastmembers ()
	{

		$limit    = $this->getParam('limit', 5);
		$template = $this->getRequiredParam('template', true);


		$data          = array ();
		$avatarEnabled = Settings::get('avatarenabled');


		$avatars     = ($avatarEnabled ? ' avatar.avatarname,
                                        avatar.avatarextension,
                                        avatar.width AS avatarwidth,
                                        avatar.height AS avatarheight,
                                        avatar.userid AS avatarowner,
                                        NOT ISNULL(customavatar.avatarname) AS hascustomavatar,' : '');
		$avatarsjoin = ($avatarEnabled ? "
                            LEFT JOIN %tp%avatars AS avatar ON(avatar.avatarid = u.avatarid)
                            LEFT JOIN %tp%avatars AS customavatar ON(customavatar.userid = u.userid) " : '');

		$rs   = $this->db->query("SELECT u.userid, {$avatars} u.username, u.usertext, u.regdate
                                FROM %tp%users AS u
                                {$avatarsjoin}
                                ORDER BY u.regdate DESC LIMIT " . $limit)->fetchAll();
		$data = array ();
		foreach ( $rs as $r )
		{
			BBCode::setBBcodeHandler('biobbcodes');
			$r[ 'bio' ]       = BBCode::removeBBCode($r[ 'usertext' ]);
			$r[ 'userphoto' ] = User::getUserPhoto($r);
			$r[ 'url' ]       = 'profile/' . $r[ 'userid' ] . '/' . Library::suggest($r[ 'username' ]) . '.' . Settings::get('mod_rewrite_suffix', 'html');

			$data[ 'lastmembers' ][ ] = $r;
		}
		$rs = null;

		return $this->getController()->Template->process('cms/' . $template, $data);
	}

}

?>