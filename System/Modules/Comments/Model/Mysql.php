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
 * @package      Comments
 * @version      3.0.0 Beta
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Mysql.php
 */
class Comments_Model_Mysql extends Model
{

	/**
	 * @param bool $modul
	 * @return array
	 */
	public function getGridData ( $modul = false )
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
			case 'created':
				$order = " ORDER BY c.timestamp";
				break;

			case 'modul':
				$order = " ORDER BY c.modul";
				break;


			case 'username':
				$order = " ORDER BY username";
				break;

			case 'published':
				$order = " ORDER BY c.published";
				break;

			case 'ip':
				$order = " ORDER BY c.ip";
				break;


			case 'title':
			default:
				$order = " ORDER BY c.title";

				break;
		}


		$params = array ();

		$_sql = 'SELECT COUNT(id) AS total FROM %tp%comments AS c
                 LEFT JOIN %tp%users AS u ON(u.userid=c.userid)';

		$_where    = ' WHERE c.pageid = ?';
		$params[ ] = PAGEID;

		switch ( $this->input('state') )
		{
			case 'online':
				$_where .= " AND c.published = 1 ";
				break;

			case 'offline':
				$_where .= " AND c.published = 0 ";
				break;

			case 'draft':
				$_where .= " AND c.published = 9 ";
				break;


			case 'online_offline':
				$_where .= " AND c.published = 0 OR c.published = 1";
				break;

            case 'spam':
                $_where .= " AND c.published = " . intval(SPAM_MODE);
                break;

			case '-1':
			default:
				$_where .= " AND c.published >= 0 ";
				break;
		}


		if ( $search != '' )
		{
			$search = str_replace("%", "\%", $search);
			$search = str_replace("*", "%", $search);

			$_where .= " AND (c.title LIKE ? OR c.comment LIKE ?) ";

			$params[ ] = '%' . $search . '%';
			$params[ ] = '%' . $search . '%';
		}


		if ( $this->input('modul') && $this->input('modul') != '-' )
		{
			$_where .= ' AND c.modul = ?';
			$params[ ] = $this->input('modul');
		}

		$r     = $this->db->query($_sql . $_where, $params)->fetch();
		$total = $r[ 'total' ];
		$limit = $this->getPerpage();
		$page  = $this->getCurrentPage();

		return array (
			'result' => $this->db->query('SELECT c.*, IF(c.userid > 0, u.username, c.username) AS username
                         FROM %tp%comments AS c
                         LEFT JOIN %tp%users AS u ON(u.userid=c.userid)
                         ' . $_where . $order . $sort . ' LIMIT ' . ($limit * ($page - 1)) . "," . $limit, $params)->fetchAll(),
			'total'  => $total
		);
	}

	/**
	 * Load all Comments for a Modul (Controller) by Post ID
	 *
	 * @uses Tree To create a Tree Struckture
	 * @param string  $source the modul for comments
	 * @param integer $postid the Content ID
	 * @param null    $limit
	 * @return array
	 */
	public function loadComments ( $source, $postid, $limit = null )
	{

		$avatars     = 'avatar.avatarid, avatar.avatarname, avatar.avatarextension, avatar.width AS avatarwidth,
                        avatar.height AS avatarheight, avatar.userid AS avatarowner,
                        NOT ISNULL(customavatar.avatarname) AS hascustomavatar';
		$avatarsjoin = "LEFT JOIN %tp%avatars AS avatar ON(avatar.avatarid = u.avatarid)
                        LEFT JOIN %tp%avatars AS customavatar ON(customavatar.userid = u.userid) ";

		$sql  = 'SELECT c.id, c.parentid, c.modul AS section, c.post_id, c.title, c.`comment`, c.`timestamp`, c.userid, c.ip, c.published,
				IF(c.userid > 0, u.username, c.username) AS username, ' . $avatars . '
				FROM %tp%comments AS c
				LEFT JOIN %tp%users AS u ON(u.userid=c.userid)
                                ' . $avatarsjoin . '
				WHERE c.published = 1 AND c.post_id = ? AND c.modul = ?/* AND c.pageid = ?*/
                ORDER BY c.`timestamp` ASC' . ($limit !== null && $limit > 0 ? ' LIMIT ' . $limit : '');
		$rows = $this->db->query($sql, $postid, $source/*, PAGEID*/)->fetchAll();

		foreach ( $rows as &$r )
		{
			if ( $r[ 'userid' ] )
			{
				$r[ 'photo' ] = User::getUserPhoto($r);
			}
		}

        #print_r($sql);exit;


		$tree = new Tree();
		$tree->setupData($rows, 'id', 'parentid');


		return $tree->buildRecurseArray();
	}

	/**
	 * Used for Comment Preview in the Backend
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getCommentById ( $id )
	{

		return $this->db->query('SELECT c.*, IF(c.userid > 0, u.username, c.username) AS username
                                  FROM %tp%comments AS c
                                  LEFT JOIN %tp%users AS u ON(u.userid=c.userid)
                                  WHERE id = ?', $id)->fetch();
	}


    public function countWaitingComments () {
        return $this->db->query('SELECT COUNT(id) as total FROM %tp%comments WHERE published = ?', MODERATE_MODE )->fetch();
    }

	/**
	 *
	 * @todo For better Comment link handling add a new Field in Table comments named 'link'
	 * @return array
	 */
	public function getLastComments ()
	{

		$limit    = $this->getParam('limit', 5);
		$template = $this->getParam('template', 'lastcomments');

		$sql  = 'SELECT c.id, c.parentid, c.modul AS section, c.post_id, c.title, c.`comment`, c.`timestamp`, c.userid, c.ip, c.published,
				IF(c.userid > 0, u.username, c.username) AS username
				FROM %tp%comments AS c
				LEFT JOIN %tp%users AS u ON(u.userid=c.userid)
				WHERE c.published = 1 AND c.pageid = ?
                ORDER BY c.`timestamp` DESC LIMIT ' . $limit;
		$rows = $this->db->query($sql, PAGEID)->fetchAll();

		$this->db->free();

		$sections = array ();
		foreach ( $rows as $r )
		{
			switch ( $r[ 'section' ] )
			{
				case 'news':
					$sections[ $r[ 'section' ] ][ ] = $r[ 'post_id' ];
					break;
				case 'page':
					$sections[ $r[ 'section' ] ][ ] = $r[ 'post_id' ];
					break;
			}
		}


		if ( count($sections) )
		{
			foreach ( $sections as $key => $_rows )
			{
				switch ( $key )
				{
					case 'news':
						$posts            = Model::getModelInstance($key)->findItemsByID($_rows);
						$sections[ $key ] = $posts;
						break;
					case 'page':
						$posts            = Model::getModelInstance($key)->findItemsByID($_rows);
						$sections[ $key ] = $posts;
						break;

				}
			}
		}


		// reset($rows);
		foreach ( $rows as &$r )
		{

			switch ( $r[ 'section' ] )
			{
				case 'news':

					if ( !isset($sections[ $r[ 'section' ] ]) )
					{
						continue;
					}

					foreach ( $sections[ $r[ 'section' ] ] as $rs )
					{
						if ( $rs[ 'id' ] == $r[ 'post_id' ] )
						{
							if (empty($rs[ 'alias' ]) && !empty($rs[ 'title' ]) ) {
								$rs[ 'alias' ] = Library::suggest($rs[ 'title' ]);
							}


							$r[ 'comment' ]     = trim(BBCode::removeSmilies(BBCode::removeBBCode($r[ 'comment' ])));
							$r[ 'commentlink' ] = 'news/item/' . ($rs[ 'alias' ] . '.' . ($rs[ 'suffix' ] ?
										$rs[ 'suffix' ] :
										Settings::get('mod_rewrite_suffix', 'html'))) . '#comment_' . $r[ 'id' ];
						}
						else {
							continue;
						}
					}

					#$post               = Model::getModelInstance( $r[ 'section' ] )->findItemByID( $r[ 'post_id' ] );
					#$r[ 'commentlink' ] = 'news/item/' . ($post[ 'alias' ] . '.' . ($post[ 'suffix' ] ? $post[ 'suffix' ] : Settings::get( 'mod_rewrite_suffix', 'html' ) ) ) . '#comment_' . $r[ 'id' ];
					break;
				case 'page':
					#$post               = Model::getModelInstance( $r[ 'section' ] )->findItemByID( $r[ 'post_id' ] );
					#$r[ 'commentlink' ] = 'page/' . ($post[ 'alias' ] . '.' . ($post[ 'suffix' ] ? $post[ 'suffix' ] : Settings::get( 'mod_rewrite_suffix', 'html' ) ) ) . '#comment_' . $r[ 'id' ];

					if ( !isset($sections[ $r[ 'section' ] ]) )
					{
						continue;
					}

					foreach ( $sections[ $r[ 'section' ] ] as $rs )
					{
						if ( $rs[ 'id' ] == $r[ 'post_id' ] )
						{
							if (empty($rs[ 'alias' ]) && !empty($rs[ 'title' ]) ) {
								$rs[ 'alias' ] = Library::suggest($rs[ 'title' ]);
							}

							if (empty($rs[ 'alias' ]) && !empty($r[ 'title' ]) ) {
								$rs[ 'alias' ] = Library::suggest($r[ 'title' ]);
							}


							$r[ 'comment' ]     = trim(BBCode::removeSmilies(BBCode::removeBBCode($r[ 'comment' ])));
							$r[ 'commentlink' ] = 'page/' . ($rs[ 'alias' ] . '.' . ($rs[ 'suffix' ] ? $rs[ 'suffix' ] :
										Settings::get('mod_rewrite_suffix', 'html'))) . '#comment_' . $r[ 'id' ];
						}
						else {
							continue;
						}
					}

					break;
			}
		}




		$this->load('Template');

		return $this->Template->process('cms/' . $template, array (
		                                                          'lastcomments' => $rows
		                                                    ), null);
	}

	/**
	 *
	 * @param integer $postid
	 * @param string  $postType
	 * @return \type
	 */
	public function getLastComment ( $postid = 0, $postType = '' )
	{

		return $this->db->query('SELECT userid, username FROM %tp%comments WHERE post_id = ? AND modul = ? AND pageid = ?' . 'ORDER BY `timestamp` DESC LIMIT 1', $postid, $postType, PAGEID)->fetch();
	}

	/**
	 *
	 * @param string  $title
	 * @param string  $comment
	 * @param string  $postType
	 * @param integer $postid
	 * @param string  $username
	 * @param integer $parentID
	 * @return integer
	 */
	public function saveComment ( $title, $comment, $postType, $postid, $username, $parentID = 0 )
	{
        $ip = $this->Env->ip();

        $published = 1;

        // filter badwords
        $list = explode("\n", Settings::get('badcommentwords', ''));
        foreach ($list as $word) {
            $word = trim($word);
            if ($word) {
                if ( !User::getUserId() ) {
                    if ( preg_match('#'. preg_quote($word, '#').'#is', $username) ){
                        $published = SPAM_MODE;
                    }
                }

                if ( $published !== SPAM_MODE && preg_match('#'. preg_quote($word, '#').'#is', $ip)  ) {
                    $published = SPAM_MODE;
                }

                if ( $published !== SPAM_MODE && preg_match('#'. preg_quote($word, '#').'#is', $title)  ) {
                    $published = SPAM_MODE;
                }

                if ( $published !== SPAM_MODE && preg_match('#'. preg_quote($word, '#').'#is', $comment)  ) {
                    $published = SPAM_MODE;
                }

                if ($published === SPAM_MODE) {
                    break;
                }
            }
        }

        if ( Settings::get('moderatecommentsifmorelinks', 0) && $published !== SPAM_MODE )
        {
            $links = substr_count('[url', $comment);
            if ( $links > Settings::get('moderatecommentsifmorelinks', 0) ) {
                $published = MODERATE_MODE;
            }
        }

		$this->db->query('INSERT INTO %tp%comments (pageid,ip,`timestamp`,title,comment,parentid,modul,post_id,userid,username, published)
                VALUES(?,?,?,?,?,?,?,?,?,?,?)', PAGEID, $ip, TIMESTAMP, $title, $comment, $parentID, $postType, $postid, User::getUserId(), $username, $published);

		return $this->db->insert_id();
	}

}
