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
 * @package      DreamCMS
 * @version      3.0.0 Beta
 * @category     Plugin s
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Mysql.php
 */
class Addon_Forum_Model_Mysql extends Model
{

	public function __construct ()
	{

		parent::__construct();
	}

	/**
	 *
	 * @param boolean $isGoogleMap default is false
	 * @return string/array
	 */
	public function getSitemap ( $isGoogleMap = false )
	{

		$transq1 = $this->buildTransWhere('board', 'f.forumid', 'ft');
		$sql     = "SELECT f.*, ft.*, t.lastpostid, p.title AS lastposttitle, IF(p.userid, u.username, p.username) AS lastpostusername
                    FROM %tp%board AS f
                    LEFT JOIN %tp%board_trans AS ft ON(ft.forumid = f.forumid)
                    LEFT JOIN %tp%board_threads AS t ON(t.forumid = f.forumid AND t.published = 1)
                    LEFT JOIN %tp%board_posts AS p ON(p.threadid = t.threadid AND p.postid = t.lastpostid AND p.published = 1)
                    LEFT JOIN %tp%users AS u ON(u.userid = p.userid)
                    WHERE f.published = 1 AND f.pageid = ? AND " . $transq1 . "
                    GROUP BY f.forumid
                    ORDER BY f.parent ASC, f.ordering ASC";
		$cats    = $this->db->query($sql, PAGEID)->fetchAll();

		foreach ( $cats as $idx => &$r )
		{

			if ( !in_array(User::getGroupId(), explode(',', $r[ 'access' ])) && !in_array(0, explode(',', $r[ 'access' ])) )
			{
				unset( $cats[ $idx ] );
				continue;
			}

			$r[ 'alias' ]  = $r[ 'forumid' ] . '/' . Library::suggest($r[ 'title' ]);
			$r[ 'suffix' ] = $r[ 'suffix' ] ? $r[ 'suffix' ] : Settings::get('mod_rewrite_suffix', 'html');
			$r[ 'url' ]    = 'forum/' . $r[ 'alias' ] . '.' . $r[ 'suffix' ];
		}


		if ( $isGoogleMap )
		{
			return $cats;
		}

		$data[ 'sitemap' ][ 'forumcats' ] = $cats;
		unset( $cats );

        $this->load('Template');
        $tpl = new Template();
        $tpl->isProvider = true;
        $data = array_merge($this->Template->getTemplateData(), $data);

		return $tpl->process('board/sitemaptree', $data, null);
	}

	private function userHasLiked ( $postid )
	{

		$l = $this->db->query("SELECT * FROM %tp%board_likes WHERE postid = ? AND userid = ? AND ip = ?", $postid, User::getUserid(), $this->Env->ip())->fetch();

		return $l[ 'id' ] ? true : false;
	}

	public function likePost ( $postid )
	{

		if ( !$this->userHasLiked($postid) )
		{
			$this->db->query("UPDATE %tp%board_posts SET likes = likes + 1 WHERE postid = ?", $postid);
			$this->db->query("REPLACE INTO %tp%board_likes (postid,userid,liked,ip) VALUES(?, ?, 1, ?)", $postid, User::getUserid(), $this->Env->ip());

			return true;
		}

		return false;
	}

	public function dislikePost ( $postid )
	{

		$sliked = Session::get('disliked', array ());
		$cliked = Cookie::get('disliked', array ());

		if ( !$this->userHasLiked($postid) )
		{
			$this->db->query("UPDATE %tp%board_posts SET dislike = dislike + 1 WHERE postid = ?", $postid);
			$this->db->query("REPLACE INTO %tp%board_likes (postid,userid,liked,ip) VALUES(?, ?, 0, ?)", $postid, User::getUserid(), $this->Env->ip());

			return true;
		}


		return false;
	}

	public function getSerachItem ( $id = 0 )
	{

		return $this->db->query("SELECT p.* FROM %tp%board_posts AS p WHERE p.postid = ?", $id)->fetch();
	}

	public function getCategories ( $isFrontend = false, $moderation = false )
	{

		$transq1 = $this->buildTransWhere('board', 'f.forumid', 'ft');

		if ( $isFrontend )
		{
			$minInForums = false;
			if ( count($moderation) )
			{
				foreach ( $moderation as $r )
				{
					if ( $r[ 'published' ] )
					{
						$minInForums[ ] = $r[ 'forumid' ];
					}
				}
			}


			$transq1 = $this->buildTransWhere('board', 'f.forumid', 'ft');
			$sql     = "SELECT f.*, ft.*,
					/* t.published AS threadpublished, */
					last.username AS lastpostusername,
					last.userid AS lastpostuserid,
					last.createdate AS lastposttime,
					last.threadid AS lastpostthreadid,
					IF(last.title != '', last.title,
						(
							SELECT title FROM %tp%board_threads
							WHERE forumid = f.forumid AND published = 1
							ORDER BY MAX(lastpostid) LIMIT 1
						)
					) AS lastposttitle,

					 (SELECT COUNT(threadid) FROM %tp%board_threads WHERE forumid = f.forumid LIMIT 1) AS threadcounter
,					(SELECT COUNT(l.postid) FROM %tp%board_posts AS l, %tp%board_threads AS r
						WHERE r.forumid = f.forumid AND l.threadid = r.threadid
						" . ( !$minInForums ? ' AND l.published = 1 AND r.published = 1' : '' ) . "
						LIMIT 1) AS postcounter

                    FROM %tp%board AS f
                    LEFT JOIN %tp%board_trans AS ft ON(ft.forumid = f.forumid)
                    LEFT JOIN %tp%board_posts AS last ON(last.postid = f.lastpostid AND last.published = 1)
                    WHERE f.published = 1 AND f.pageid = ? AND " . $transq1 . "
					GROUP BY f.forumid
                    ORDER BY f.parent DESC, f.ordering ASC";

			return $this->db->query($sql, PAGEID)->fetchAll();
		}

		return $this->db->query('SELECT f.*, ft.*,
					t.lastposttime,
					t.title AS lastposttitle,
					t.lastpostuserid,
					t.lastpostusername,
					last.username AS lastpostusername,
					last.userid AS lastpostuserid,
					last.createdate AS lastposttime
                    FROM %tp%board AS f
                    LEFT JOIN %tp%board_trans AS ft ON(ft.forumid = f.forumid)
                    LEFT JOIN %tp%board_threads AS t ON(t.forumid = f.forumid AND t.lastpostid = f.lastpostid)
                    LEFT JOIN %tp%board_posts AS last ON(last.threadid = t.threadid AND last.postid = t.lastpostid)
                    WHERE ' . $transq1 . ' 
                    GROUP BY f.forumid
                    ORDER BY f.parent DESC, f.ordering ASC')->fetchAll(); // establish the hierarchy
	}


	/**
	 * get attachment counter
	 *
	 * @return array
	 */
	public function countAttachments ( $ismod = false )
	{

		$transq1 = $this->buildTransWhere('board', 'f.forumid', 'ft');
		$sql     = "SELECT COUNT(a.attachmentid) AS attachmentcounter
                    FROM %tp%board AS f
                    LEFT JOIN %tp%board_trans AS ft ON(ft.forumid = f.forumid)
				 	LEFT JOIN %tp%board_threads AS t ON(t.forumid = f.forumid " . ( !$ismod ? 'AND t.published = 1' : '' ) . ")
                    LEFT JOIN %tp%board_posts AS p ON(p.threadid = t.threadid " . ( !$ismod ? 'AND p.published = 1' : '' ) . ")
                    LEFT JOIN %tp%board_attachments AS a ON(a.postid = p.postid)
                    WHERE f.published = 1 AND f.pageid = ? AND " . $transq1 . " LIMIT 1";

		return $this->db->query($sql, PAGEID)->fetch();
	}

	/**
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getForumById ( $id )
	{

		$transq1 = $this->buildTransWhere('board', 'f.forumid', 'ft');

		return $this->db->query('SELECT f.*, ft.* FROM %tp%board AS f
                                LEFT JOIN %tp%board_trans AS ft ON(ft.forumid = f.forumid)
                                WHERE f.forumid = ? AND ' . $transq1, $id)->fetch();
	}

	public function getForumsForCache ()
	{

		$transq1 = $this->buildTransWhere('board', 'f.forumid', 'ft');
		$sql     = "SELECT f.*, ft.*,
                    IF(ft.lastpostuserid, u.username, ft.lastpostusername) AS lastpostusername
                    FROM %tp%board AS f
                    LEFT JOIN %tp%board_trans AS ft ON(ft.forumid = f.forumid)
                    LEFT JOIN %tp%board_threads AS t ON(t.forumid = f.forumid)
                    WHERE f.published = 1 AND f.pageid = ? AND " . $transq1 . "
                    GROUP BY f.forumid 
                    ORDER BY f.parent DESC, f.ordering ASC";

		return $this->db->query($sql, PAGEID)->fetchAll(); // establish the hierarchy
	}

	/**
	 *
	 * @param boolean $isMod
	 * @param integer $forumid
	 * @param string  $sort
	 * @param string  $order
	 * @return array
	 */
	public function getAnnouncementsAndImportants ( $isMod = false, $forumid = 0, $sort = null, $order = null, $q = null, $timefilter = 'all' )
	{

		/*
		$timefilter_query = '';
		if ( $timefilter != 'all' && intval($timefilter) > 0 )
		{
			$zerohour = mktime(0, 0, 0, date('m', TIMESTAMP), date('d', TIMESTAMP), date('Y', TIMESTAMP));

			switch ( intval($timefilter) )
			{
				case 1:
					$timefilter_query = ' AND last.createdate >= '. $zerohour;
					break;
				case 7:
					$timefilter_query = ' AND last.createdate >= '. (time() - 604800);
					break;
				case 15:
					$timefilter_query = ' AND last.createdate >= '. (time() - 6060247);
					break;
				case 30:
					$timefilter_query = ' AND last.createdate >= '. (time() - 2592000);
					break;
				case 45:
					$timefilter_query = ' AND last.createdate >= '. (time() - 3888000);
					break;
				case 75:
					$timefilter_query = ' AND last.createdate >= '. (time() - 6480000);
					break;
				case 365:
					$timefilter_query = ' AND last.createdate >= '. (time() - 31536000);
					break;
				default:
					$timefilter_query = ' AND last.createdate >= '. (time() - 31536000);
					break;
			}
		}
*/

		// Sortierung

		switch ( $sort )
		{
			case 'asc':
				$sort = " ASC";
				break;

			case 'desc':
			default:
				$sort = " DESC";
				break;
		}

		switch ( $order )
		{
			case 'title':
				$order = " ORDER BY t.title";
				if ( $sort == null )
				{
					$sort = " ASC";
				}
				break;

			case 'rating':
				$order = " ORDER BY t.rating";
				break;

			case 'hits':
				$order = " ORDER BY t.hits";
				break;
			case 'attachments':
				$order = " ORDER BY attachmentcounter";
				break;
			case 'date':
			case 'lastpost':
			default:
				$order = " ORDER BY last.createdate DESC, p.createdate";
				break;
		}


		$sql = "SELECT t.*,
					last.parent,
					last.content,
					IF(last.title != '', last.title, t.title) AS lastposttitle,
					IF(last.userid > 0, u.username, IF(last.username != '', last.username, p.username)) AS username,
					COUNT(a.attachmentid) AS attachmentcounter
                    FROM %tp%board_threads AS t
                    LEFT JOIN %tp%board_posts AS p ON(p.threadid = t.threadid" . ( !$isMod ? ' AND p.published = 1' : '' ) . ")
                    LEFT JOIN %tp%board_posts AS last ON(last.threadid = t.threadid AND last.postid = " . // show the real last post for moderators
			( $isMod ? '(SELECT postid FROM %tp%board_posts WHERE threadid = t.threadid ORDER BY createdate DESC LIMIT 1)' : 't.lastpostid' ) . ( !$isMod ? ' AND last.published = 1' : '' ) . ")
                    LEFT JOIN %tp%users AS u ON(u.userid = last.userid)
                   LEFT JOIN %tp%board_attachments AS a ON(a.postid = p.postid)
                    WHERE t.forumid = ? AND (t.threadtype = 10 OR t.threadtype = 20)" . ( !$isMod ? ' AND t.published = 1' : '' ) . " AND t.pageid = ?
                    GROUP BY t.threadid
                    {$order} {$sort}";

		return $this->db->query($sql, $forumid, PAGEID)->fetchAll();
	}

	/**
	 *
	 * @param boolean $isMod
	 * @param array   $forum
	 * @param integer $limit
	 * @param integer $page
	 * @param string  $sort
	 * @param string  $order
	 * @param string  $q
	 * @return array
	 */
	public function getPosts ( $isMod = false, &$forum, $limit = 20, $page = 1, $sort = null, $order = null, $q = null, $timefilter = 'all' )
	{

		$timefilter_query = '';
		if ( $timefilter != 'all' && intval($timefilter) > 0 )
		{
			$zerohour = mktime(0, 0, 0, date('m', TIMESTAMP), date('d', TIMESTAMP), date('Y', TIMESTAMP));

			switch ( intval($timefilter) )
			{
				case 1:
					$timefilter_query = ' AND last.createdate >= ' . $zerohour;
					break;
				case 7:
					$timefilter_query = ' AND last.createdate >= ' . ( time() - 604800 );
					break;
				case 15:
					$timefilter_query = ' AND last.createdate >= ' . ( time() - 6060247 );
					break;
				case 30:
					$timefilter_query = ' AND last.createdate >= ' . ( time() - 2592000 );
					break;
				case 45:
					$timefilter_query = ' AND last.createdate >= ' . ( time() - 3888000 );
					break;
				case 75:
					$timefilter_query = ' AND last.createdate >= ' . ( time() - 6480000 );
					break;
				case 365:
					$timefilter_query = ' AND last.createdate >= ' . ( time() - 31536000 );
					break;
			}
		}


		$sql = "SELECT (SELECT postid FROM %tp%board_posts WHERE threadid = t.threadid ORDER BY createdate DESC LIMIT 1) AS lastpostid, COUNT(t.threadid) AS total
				FROM %tp%board_threads AS t
				LEFT JOIN %tp%board_posts AS last ON(last.threadid = t.threadid AND last.postid = lastpostid)
                WHERE t.pageid = ? AND t.threadtype = 0" . ( !$isMod ? ' AND t.published = 1' : '' ) . " AND t.forumid = ?" . $timefilter_query;
		$r   = $this->db->query($sql, PAGEID, $forum[ 'forumid' ])->fetch();

		$a = $limit * ( $page > 0 ? $page - 1 : 1 );


		// Sortierung
		switch ( $sort )
		{
			case 'asc':
				$sort = " ASC";
				break;

			case 'desc':
			default:
				$sort = " DESC";
				break;
		}

		switch ( $order )
		{
			case 'title':
				$order = " t.title";
				if ( $sort == null )
				{
					$sort = " ASC";
				}
				break;

			case 'rating':
				$order = " t.rating";
				break;

			case 'hits':
				$order = " t.hits";
				break;
			case 'posts':
				$order = " t.postcounter";
				break;
			case 'attachments':
				$order = " attachmentcounter";
				break;
			case 'date':
			case 'lastpost':
			default:
				$order = " last.createdate";
				break;
		}


		/**
		 * Posts
		 */
		$icons     = 'icon.icontitle, icon.iconpath';
		$iconsjoin = ' LEFT JOIN %tp%icons AS icon ON(icon.iconid = last.iconid) ';

		$sql = "SELECT t.*, {$icons}, last.parent, last.content, last.userid,

                IF(last.userid > 0, u.username, IF(last.username != '', last.username, 'unknown')) AS username,
                COUNT(a.attachmentid) AS attachmentcounter,
                (SELECT postid FROM %tp%board_posts WHERE threadid = t.threadid" . ( !$isMod ? ' AND published = 1' : '' ) . " ORDER BY createdate DESC LIMIT 1) AS lastpostid,

				last.username AS lastpostusername,
				last.userid AS lastpostuserid,
				last.createdate AS lastposttime,
				IF(last.title != '', last.title, t.title) AS lastposttitle

                /*(SELECT IF(title != '', title, t.title) FROM %tp%board_posts WHERE threadid = t.threadid ORDER BY createdate DESC LIMIT 1) AS lastposttitle,
				(SELECT createdate FROM %tp%board_posts WHERE threadid = t.threadid ORDER BY createdate DESC LIMIT 1) AS lastposttime */
                FROM %tp%board_threads AS t
                LEFT JOIN %tp%board_posts AS last ON(last.threadid = t.threadid AND last.postid = " .

			// show the real last post for moderators
			( $isMod ? '(SELECT postid FROM %tp%board_posts WHERE threadid = t.threadid ORDER BY createdate DESC LIMIT 1)' : 'lastpostid' ) . ")

                LEFT JOIN %tp%users AS u ON(u.userid = last.userid)
                LEFT JOIN %tp%board_posts AS p ON(p.threadid = t.threadid)
                LEFT JOIN %tp%board_attachments AS a ON(a.postid = p.postid)

                {$iconsjoin}
                WHERE
                    t.forumid = ? AND
                    t.threadtype = 0
                    " . ( !$isMod ? ' AND t.published = 1' : '' ) . $timefilter_query . " AND t.pageid = ?
                GROUP BY t.threadid
                ORDER BY {$order} {$sort}
                LIMIT {$a}, {$limit}";

		return array (
			'total'  => $r[ 'total' ],
			'result' => $this->db->query($sql, $forum[ 'forumid' ], PAGEID)->fetchAll()
		);
	}

	/**
	 *
	 * @param boolean $isMod
	 * @param array   $ids
	 * @return array
	 */
	public function getDotThreads ( $isMod = false, array $ids )
	{

		$sql = "SELECT COUNT(postid) AS count, threadid, MAX(createdate) AS lastpost
                FROM %tp%board_posts
                WHERE userid = ? 
                " . ( !$isMod ? 'AND published = 1' : '' ) . " AND
                threadid IN (" . implode(',', $ids) . ")
                GROUP BY threadid";

		$mythreads = $this->db->query($sql, User::getUserId())->fetchAll();

		$dotthreads = array ();
		foreach ( $mythreads as $r )
		{
			$dotthreads[ $r[ 'threadid' ] ][ 'count' ]    = $r[ 'count' ];
			$dotthreads[ $r[ 'threadid' ] ][ 'lastpost' ] = $r[ 'lastpost' ];
		}

		unset( $mythreads );

		return $dotthreads;
	}

	/**
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getThreadById ( $id = 0 )
	{

		if ( is_array($id) )
		{
			return $this->db->query('SELECT * FROM %tp%board_threads WHERE threadid IN(' . implode(',', $id) . ')')->fetchAll();
		}

		return $this->db->query('SELECT * FROM %tp%board_threads WHERE threadid = ?', $id)->fetch();
	}

	public function getThreadPostsAtPostID ( $isMod = false, &$forum, $threadid = 0, $postid = 0 )
	{


		// $r = $this->db->query( 'SELECT postid FROM %tp%board_posts WHERE ' . (!$isMod ? 'published=1 AND ' : '') . 'threadid = ? ORDER BY createdate DESC', $threadid )->fetchAll();


		$limit   = Settings::get('forum.postsperpage', 20);
		$order   = Settings::get('forum.postorder', 'desc');
		$setPage = 1;
		$x       = 0;
		foreach ( $this->db->query('SELECT postid FROM %tp%board_posts WHERE ' . ( !$isMod ? 'published=1 AND ' : '' ) . 'threadid = ? ORDER BY createdate ' . $order, $threadid)->fetchAll() as $rs )
		{
			if ( $rs[ 'postid' ] == $postid )
			{
				$this->db->free();

				return $setPage;
				break;
			}

			$x++;

			if ( $x == $limit )
			{
				$setPage++;
				$x = 0;
			}
		}

		return $setPage;


		$icons     = ( $forum[ 'allowicons' ] ? 'icon.icontitle, icon.iconpath,' : '' );
		$iconsjoin = ( $forum[ 'allowicons' ] ? " LEFT JOIN %tp%icons AS icon ON(icon.iconid = p.iconid) " : '' );

		$avatars     = ( Settings::get('avatarenabled') ? '
                                                avatar.avatarname,avatar.avatarid,
                                                avatar.avatarextension,
                                                avatar.width AS avatarwidth,
                                                avatar.height AS avatarheight,
                                                avatar.userid AS avatarowner,
                                                NOT ISNULL(customavatar.avatarname) AS hascustomavatar,' : '' );
		$avatarsjoin = ( Settings::get('avatarenabled') ? "
                        LEFT JOIN %tp%avatars AS avatar ON(avatar.avatarid = u.avatarid)
                        LEFT JOIN %tp%avatars AS customavatar ON(customavatar.userid = p.userid) " : '' );

		$sql = "SELECT p.*,
                IF(p.userid != 0, u.username, p.username) AS username,
                IF(p.userid != 0, g.title, " . $this->db->quote(trans('Gast')) . ") AS usergroup,
                    {$avatars}
                    {$icons}
                u.userposts, u.signature,
                u.gender,
                u.`name` AS firstname,
                u.signature,
                u.lastname,
                r.ranktitle, r.repeats, r.rankimages
                FROM %tp%board_posts AS p
                LEFT JOIN %tp%board_threads AS t ON(t.threadid = p.threadid)
                LEFT JOIN %tp%users AS u ON(u.userid = p.userid)
                LEFT JOIN %tp%users_groups AS g ON(g.groupid = u.groupid)
                LEFT JOIN %tp%users_ranks r ON(r.rankid = u.rankid)
                    {$avatarsjoin}
                    {$iconsjoin}
                WHERE " . ( !$isMod ? 'p.published=1 AND ' : '' ) . "
                p.threadid = ? AND t.pageid = ?
                GROUP BY p.postid
                ORDER BY p.createdate DESC
                LIMIT {$a}, {$limit}";
	}

	/**
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getThreadPosts ( $isMod = false, &$forum, $threadid = 0, $page = 1 )
	{


		$order = Settings::get('forum.postorder', 'desc');


		$r = $this->db->query('SELECT COUNT(threadid) AS total FROM %tp%board_posts WHERE ' . ( !$isMod ? 'published=1 AND ' : '' ) . 'threadid = ?', $threadid)->fetch();

		$limit = Settings::get('forum.postsperpage', 20);
		$a     = ( $page - 1 ) * $limit;


		$icons     = ( $forum[ 'allowicons' ] ? 'icon.icontitle, icon.iconpath,' : '' );
		$iconsjoin = ( $forum[ 'allowicons' ] ? " LEFT JOIN %tp%icons AS icon ON(icon.iconid = p.iconid) " : '' );

		$avatars     = ( Settings::get('avatarenabled') ? '
                                                avatar.avatarname,avatar.avatarid,
                                                avatar.avatarextension,
                                                avatar.width AS avatarwidth,
                                                avatar.height AS avatarheight,
                                                avatar.userid AS avatarowner,
                                                NOT ISNULL(customavatar.avatarname) AS hascustomavatar,' : '' );
		$avatarsjoin = ( Settings::get('avatarenabled') ? "
                        LEFT JOIN %tp%avatars AS avatar ON(avatar.avatarid = u.avatarid)
                        LEFT JOIN %tp%avatars AS customavatar ON(customavatar.userid = p.userid) " : '' );

		$sql = "SELECT p.*,
                IF(p.userid != 0, u.username, p.username) AS username,
                IF(p.userid != 0, g.title, " . $this->db->quote(trans('Gast')) . ") AS usergroup,
                    {$avatars}
                    {$icons}
                u.userposts, u.signature,
                u.gender,
                u.`name` AS firstname,
                u.signature,
                u.lastname,
                r.ranktitle, r.repeats, r.rankimages
                FROM %tp%board_posts AS p
                LEFT JOIN %tp%board_threads AS t ON(t.threadid = p.threadid)
                LEFT JOIN %tp%users AS u ON(u.userid = p.userid)
                LEFT JOIN %tp%users_groups AS g ON(g.groupid = u.groupid)
                LEFT JOIN %tp%users_ranks r ON(r.rankid = u.rankid)
                    {$avatarsjoin}
                    {$iconsjoin}
                WHERE " . ( !$isMod ? 'p.published=1 AND ' : '' ) . "
                p.threadid = ? AND t.pageid = ?
                GROUP BY p.postid
                ORDER BY p.createdate {$order}
                LIMIT {$a}, {$limit}";

		return array (
			'total'  => $r[ 'total' ],
			'result' => $this->db->query($sql, $threadid, PAGEID)->fetchAll()
		);
	}

	/**
	 *
	 * @param integer $threadid
	 * @param integer $parentpostid default is null
	 * @return array
	 */
	public function getThreadPost ( $threadid = 0, $parentpostid = null )
	{

		if ( $parentpostid > 0 )
		{
			return $this->db->query('SELECT t.*, p.postid, p.userid AS posterid, p.content,
                    p.title AS posttitle, IF(p.userid > 0, u.username, p.username) AS username
                    FROM %tp%board_posts AS p
                    LEFT JOIN %tp%board_threads AS t ON(t.threadid = p.threadid)
                    LEFT JOIN %tp%users AS u ON(u.userid = p.userid)
                    WHERE t.pageid = ? AND p.postid = ? AND t.threadid = ?', PAGEID, $parentpostid, $threadid)->fetch();
		}
		else
		{
			return $this->db->query('SELECT t.*, p.userid AS posterid
                    FROM %tp%board_threads AS t
                    LEFT JOIN %tp%board_posts AS p ON(p.postid = t.lastpostid)
                    WHERE t.pageid = ? AND t.threadid = ?', PAGEID, $threadid)->fetch();
		}
	}

	/**
	 *
	 * @param array $postids
	 * @return array
	 */
	public function getPostAttachments ( array $postids )
	{

		return $this->db->query('SELECT a.* FROM %tp%board_attachments AS a WHERE a.postid IN(' . implode(',', $postids) . ')')->fetchAll();
	}

	/**
	 *
	 * @param integer $threadid
	 */
	public function updateThreadHits ( $threadid = 0 )
	{

		$this->db->query('UPDATE %tp%board_threads SET hits = hits+1 WHERE threadid = ?', $threadid);
	}

	/**
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getAttachmentById ( $id = 0, $usePublish = true )
	{

		return $this->db->query('
            SELECT a.*, t.*
            FROM %tp%board_attachments AS a 
            LEFT JOIN %tp%board_posts AS p ON(p.postid = a.postid)
            LEFT JOIN %tp%board_threads AS t ON(t.threadid = p.threadid)
            WHERE a.attachmentid = ?' . ($usePublish ? ' AND p.published = 1' : ''), $id)->fetch();
	}

	/**
	 *
	 * @param integer $id
	 * @return array
	 */
	public function updateAttachmentHits ( $id = 0 )
	{

		$this->db->query('UPDATE %tp%board_attachments SET hits = hits+1 WHERE attachmentid = ?', $id);
	}

	/**
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getPostById ( $postid = 0 )
	{

		return $this->db->query('SELECT t.*, t.title AS threadtitle, p.*, IF(p.userid > 0, u.username, p.username) AS username
                                  FROM %tp%board_posts AS p
                                  LEFT JOIN %tp%board_threads AS t ON(t.threadid = p.threadid)
                                  LEFT JOIN %tp%users AS u ON(u.userid = p.userid)
                                  WHERE p.postid = ? 
                                  GROUP BY p.postid', $postid)->fetch();
	}

	/**
	 *
	 * @param integer $id
	 * @param bool    $useCat
	 * @return bool
	 */
	public function hasForumTranslation ( $id = 0, $useCat = false )
	{

		$trans = $this->db->query('SELECT forumid FROM %tp%board_trans WHERE forumid = ? AND lang = ?', $id, CONTENT_TRANS)->fetch();

		if ( $trans[ 'forumid' ] )
		{
			return true;
		}

		return false;
	}

	/**
	 * will rollback the temporary translated content
	 *
	 * @param integer $id
	 * @return type
	 */
	function rollbackForumTranslation ( $id, $useCat = false )
	{

		$this->db->query('DELETE FROM %tp%board_trans WHERE `rollback` = 1 AND forumid = ? AND lang = ?', $id, CONTENT_TRANS);
	}

	/**
	 * Copy the original translation to other translation
	 *
	 * @param integer $id
	 */
	public function copyOriginalForumTranslation ( $id, $useCat = false )
	{

		$r = $this->db->query('SELECT lang FROM %tp%board_trans WHERE forumid = ? AND iscorelang = 1', $id)->fetch();
		if ( CONTENT_TRANS == $r[ 'lang' ] )
		{
			return;
		}

		$trans                 = $this->db->query('SELECT t.* FROM %tp%board_trans AS t WHERE t.forumid = ? AND t.lang = ?', $id, $r[ 'lang' ])->fetch();
		$trans[ 'lang' ]       = CONTENT_TRANS;
		$trans[ 'rollback' ]   = 1;
		$trans[ 'iscorelang' ] = 0;

		$f      = array ();
		$fields = array ();
		$values = array ();
		foreach ( $trans as $key => $value )
		{
			$fields[ ] = $key;
			$f[ ]      = '?';
			$values[ ] = $value;
		}

		$this->db->query('INSERT INTO %tp%board_trans (' . implode(',', $fields) . ') VALUES(' . implode(',', $f) . ')', $values);
	}

	/**
	 *
	 * @param integer $id ID of the forum default is 0
	 * @param array   $data
	 * @return integer the ID of the forum
	 */
	public function saveForumTranslation ( $id = 0, $data )
	{

		$this->setTable('board');

		$access           = ( is_array($data[ 'access' ]) ? $data[ 'access' ] : array (
			0
		) );
		$data[ 'access' ] = implode(',', $access);


		$coredata = array (
			'parent'         => intval($data[ 'parent' ]),
			'pageid'         => PAGEID,
			// 'tags' => (string) $data['tags'],
			'access'         => (string)$data[ 'access' ],
			'moderatorids'   => '',
			'moderators'     => '',
			// 'keywords' => (string) $data['keywords'],
			'threadsperpage' => intval($data[ 'threadsperpage' ]),
			'containposts'   => intval($data[ 'containposts' ]),
			'forumpassword'  => ( !empty( $data[ 'forumpassword' ] ) ? Library::encrypt($data[ 'forumpassword' ]) : '' ),
			'allowicons'     => intval($data[ 'allowicons' ]),
			'allowhtml'      => intval($data[ 'allowhtml' ]),
			'allowbbcode'    => intval($data[ 'allowbbcode' ]),
			'allowimg'       => intval($data[ 'allowimg' ]),
			'created_by'     => intval(User::getUserId()),
			'created'        => TIMESTAMP,
			'modifed_by'     => intval(User::getUserId()),
			'modifed'        => TIMESTAMP,
            'redirect_url'   => (string)$data[ 'redirect_url' ],
            'redirect_on'    => intval($data[ 'redirect_on' ]),
			'rollback'       => 0,
			'threadcounter'  => 0,
			'postcounter'    => 0,
		);


		if ( !is_array($data[ 'documentmeta' ]) )
		{
			$coredata[ 'published' ] = $data[ 'published' ];
		}

		$transData = array ();

		if ( !$id )
		{
			$coredata[ 'modifed' ]    = 0;
			$coredata[ 'modifed_by' ] = 0;

			$transData[ 'controller' ] = 'p:forum';
			$transData[ 'action' ]     = 'index';
			$transData[ 'data' ]       = $data;
			$transData[ 'alias' ]      = $data[ 'alias' ];
			$transData[ 'suffix' ]     = $data[ 'suffix' ];

			$transData[ 'id' ] = $this->save($id, $coredata, $transData);
		}
		else
		{
			unset( $coredata[ 'threadcounter' ], $coredata[ 'postcounter' ], $coredata[ 'created' ], $coredata[ 'created_by' ], $coredata[ 'moderatorids' ], $coredata[ 'moderators' ] );

			$transData[ 'controller' ] = 'p:forum';
			$transData[ 'action' ]     = 'index';
			$transData[ 'data' ]       = $data;
			$transData[ 'alias' ]      = $data[ 'alias' ];
			$transData[ 'suffix' ]     = $data[ 'suffix' ];

			$transData[ 'id' ] = $this->save($id, $coredata, $transData);
		}


		return $transData[ 'id' ];
	}

	public function getModeratorsGridQuery ( $forumid )
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

			case 'grouptitle':
				$order = " ORDER BY grouptitle";
				break;

			case 'modid':
				$order = " ORDER BY m.modid";
				break;

			case 'published':
				$order = " ORDER BY m.published";
				break;

			case 'username':
			default:
				$order = " ORDER BY u.username";

				break;
		}

		$r = $this->db->query('SELECT COUNT(*) AS total FROM %tp%board_moderators WHERE forumid = ?', $forumid)->fetch();

		$total = $r[ 'total' ];
		$limit = $this->getPerpage();
		$page  = $this->getCurrentPage();


		return array (
			'total'  => $r[ 'total' ],
			'result' => $this->db->query('SELECT m.*, u.username, g.title AS grouptitle
                                          FROM %tp%board_moderators AS m 
                                          LEFT JOIN %tp%users AS u ON(u.userid = m.userid) 
                                          LEFT JOIN %tp%users_groups AS g ON(g.groupid = u.groupid) 
                                          WHERE m.forumid = ? ' . $order . $sort . " LIMIT " . ( $limit * ( $page - 1 ) ) . "," . $limit, $forumid)->fetchAll()
		);
	}

	/**
	 *
	 * @param integer $modid
	 * @param integer $forumid
	 * @return array
	 */
	public function getForumModerators ( $forumid )
	{

		return $this->db->query('SELECT m.*, u.username, u.groupid, g.grouptype, g.dashboard, g.title AS grouptitle
                                FROM %tp%board_moderators AS m 
                                LEFT JOIN %tp%users AS u ON(u.userid=m.userid) 
                                LEFT JOIN %tp%users_groups AS g ON(g.groupid = u.groupid) 
                                WHERE m.forumid = ? AND m.published = 1
                                ORDER BY u.username ASC', $forumid)->fetchAll();
	}

	/**
	 *
	 * @param integer $modid
	 * @param integer $forumid
	 * @return array
	 */
	public function getModeratorByID ( $modid, $forumid )
	{

		return $this->db->query('SELECT m.*, u.username, u.groupid, g.grouptype, g.dashboard, g.title AS grouptitle
                                FROM %tp%board_moderators AS m 
                                LEFT JOIN %tp%users AS u ON(u.userid=m.userid) 
                                LEFT JOIN %tp%users_groups AS g ON(g.groupid = u.groupid) 
                                WHERE m.forumid = ? AND m.modid = ?', $forumid, $modid)->fetch();
	}

	/**
	 *
	 * @param integer $id
	 * @param integer $forumid
	 * @return array
	 */
	public function getModeratorByUserID ( $userid, $forumid = null )
	{

		if ( $forumid === null )
		{
			return $this->db->query('SELECT m.*, u.username, u.groupid, g.grouptype, g.dashboard, g.title AS grouptitle
                                FROM %tp%board_moderators AS m
                                LEFT JOIN %tp%users AS u ON(u.userid=m.userid)
                                LEFT JOIN %tp%users_groups AS g ON(g.groupid = u.groupid)
                                WHERE m.userid = ?', $userid)->fetchAll();
		}


		return $this->db->query('SELECT m.*, u.username, u.groupid, g.grouptype, g.dashboard, g.title AS grouptitle
                                FROM %tp%board_moderators AS m 
                                LEFT JOIN %tp%users AS u ON(u.userid=m.userid) 
                                LEFT JOIN %tp%users_groups AS g ON(g.groupid = u.groupid) 
                                WHERE m.forumid = ? AND m.userid = ?', $forumid, $userid)->fetch();
	}

	/**
	 *
	 * @param array   $data
	 * @param integer $forumid
	 */
	public function updateForumModerators ( $data = array (), $forumid = 0 )
	{

		$this->db->query('UPDATE %tp%board SET moderators = ?, moderatorids = ? WHERE forumid = ?', serialize($data[ 'array' ]), implode(',', $data[ 'userids' ]), $forumid);
	}

	public function setModeratorPublishing ( $id, $forumid )
	{

		$mod = $this->getModeratorByID($id, $forumid);
		$pub = ( $mod[ 'published' ] ? 0 : 1 );
		$this->db->query('UPDATE %tp%board_moderators SET published = ? WHERE modid = ?', $pub, $id);

		return $pub;
	}

	/**
	 *
	 * @param integer $modid
	 * @param array   $data
	 * @return integer
	 */
	public function saveModerator ( $modid = 0, $data )
	{

		if ( $modid )
		{
			$this->db->query('UPDATE %tp%board_moderators SET permissions = ?, userid = ?, forumid = ?, published = ? WHERE modid = ?', $data[ 'permissions' ], $data[ 'userid' ], $data[ 'forumid' ], $data[ 'published' ], $modid);

			Library::log(sprintf('Has update the Moderator `%s` (ID:%s) in the Forum `%s` (ID:%s)', $data[ 'user' ][ 'username' ], $data[ 'user' ][ 'userid' ], $data[ 'forum' ][ 'title' ], $data[ 'forum' ][ 'forumid' ]));

			return $modid;
		}
		else
		{
			$this->db->query('INSERT INTO %tp%board_moderators (permissions, userid, forumid, published) VALUES(?,?,?,?)', $data[ 'permissions' ], $data[ 'userid' ], $data[ 'forumid' ], $data[ 'published' ]);

			Library::log(sprintf('Has create the Moderator `%s` (ID:%s) in the Forum `%s` (ID:%s)', $data[ 'user' ][ 'username' ], $data[ 'user' ][ 'userid' ], $data[ 'forum' ][ 'title' ], $data[ 'forum' ][ 'forumid' ]));

			return $this->db->insert_id();
		}
	}

	// ------------------------------- Forum Post functions

	/**
	 *
	 * @param integer $id
	 * @param array   $data
	 */
	public function saveThread ( $id = 0, $data )
	{

		if ( $id )
		{
			$this->db->query('UPDATE ');
		}
		else
		{
			$this->db->query('INSERT INTO %tp%board_threads () VALUES()');
		}
	}

	// ------------------------------- Core


	private $cats = null;

	private $catcache = array ();

	private $forum_by_id = array ();

	private $forum_cache = array ();

	private function initCache ()
	{

		if ( $this->cats != null )
		{
			return;
		}

		$result = $this->getCategories(false);
		foreach ( $result as $r )
		{
			$this->cats[ $r[ 'parent' ] ][ $r[ 'ordering' ] ][ $r[ 'forumid' ] ] = $r;

			$this->forum_by_id[ $r[ 'forumid' ] ] = $r;

			if ( $r[ 'parent' ] < 1 )
			{
				$r[ 'parent' ] = 'root';
			}
			$this->forum_cache[ $r[ 'parent' ] ][ $r[ 'forumid' ] ] = $r;
		}
	}

	public function getForumTree ( $catid = 0, $depth = 0 )
	{

		$this->initCache();

		// database has already been queried
		if ( is_array($this->cats[ $catid ]) )
		{
			foreach ( $this->cats[ $catid ] as $holder )
			{
				foreach ( $holder as $forum )
				{
					$this->catcache[ $forum[ 'forumid' ] ]            = $forum;
					$this->catcache[ $forum[ 'forumid' ] ][ 'depth' ] = $depth;

					if ( isset( $this->cats[ $forum[ 'forumid' ] ] ) )
					{
						$this->getForumTree($forum[ 'forumid' ], $depth + 1);
					}
				}
			}
		}
	}

	public function getParents ( $root_id = 0, $ids = array () )
	{

		if ( is_array($this->forum_by_id[ $root_id ]) )
		{
			$parentID = $this->forum_by_id[ $root_id ][ 'parent' ];
			if ( $parentID )
			{
				$ids[ ] = $parentID;

				if ( isset( $this->forum_by_id[ $parentID ] ) )
				{
					$ids = $this->getParents($parentID, $ids);
				}
			}

		}

		return $ids;
	}

	public function getChildren ( $root_id = 0, $ids = array (), $ignoreAccess = false )
	{

		if ( is_array($this->forum_cache[ $root_id ]) )
		{
			foreach ( $this->forum_cache[ $root_id ] as $id => $forum_data )
			{
				if ( !$ignoreAccess )
				{
					if ( !in_array(User::getGroupId(), explode(',', $forum_data[ 'access' ])) && !in_array(0, explode(',', $forum_data[ 'access' ])) )
					{
						continue;
					}
				}

				$ids[ ] = $forum_data[ 'forumid' ];
				$ids    = $this->getChildren($forum_data[ 'forumid' ], $ids, $ignoreAccess);
			}
		}

		return $ids;
	}


	public function sync ( $type, $id = false)
	{

		switch ( $type )
		{
			case 'forums':
				$result = $this->db->query('SELECT forumid, containposts FROM %tp%board WHERE containposts = 1 ORDER BY parent ASC')->fetchAll();
				foreach ( $result as $r )
				{
					$this->sync('forum', $r[ 'forumid' ]);
				}
				break;

			case 'forum':

				$q  = 'SELECT COUNT(p.postid) AS total
					   FROM %tp%board_posts AS p
					   LEFT JOIN %tp%board_threads AS t ON(t.threadid = p.threadid)
					   WHERE t.forumid = ? AND p.published = 1 AND t.published = 1';
				$r1 = $this->db->query($q, $id)->fetch();


				$q = 'SELECT MAX(p.postid) AS postid, p.createdate AS lastposttime, p.threadid
					  FROM %tp%board_posts AS p
					  LEFT JOIN %tp%board_threads AS t ON(t.threadid = p.threadid)
					  WHERE t.forumid = ? AND p.published = 1 AND t.published = 1
					  LIMIT 1';
				$last = $this->db->query($q, $id)->fetch();

				//$r           = $this->db->query($sql, $id)->fetch();
				$last_post   = intval($last[ 'postid' ]);
				$total_posts = intval($r1[ 'total' ]);


				$sql          = "SELECT COUNT(threadid) AS total FROM %tp%board_threads WHERE forumid = ? AND published = 1";
				$r            = $this->db->query($sql, $id)->fetch();
				$total_topics = intval($r[ 'total' ]);

				$sql = "UPDATE %tp%board SET lastpostid = ?, lastpostthreadid = ?, postcounter = ?, threadcounter = ?, lastposttime = ? WHERE forumid = ?";
				$this->db->query($sql, $last_post, $last[ 'threadid' ], $total_posts, $total_topics, $last[ 'lastposttime' ], $id);


				break;


			case 'threads':
				$result = $this->db->query('SELECT threadid FROM %tp%board_threads')->fetchAll();
				foreach ( $result as $r )
				{
					$sql = "SELECT
								MAX(p.postid) AS postid,
								MIN(p.postid) AS firstpostid,
								COUNT(p.postid) AS total, p.createdate, p.userid,
								IF(
									p.userid>0,
									u.username,
									p.username
								) AS username,
								IF(p.title != '', p.title, t.title) AS lastposttitle

							FROM %tp%board_posts AS p
							LEFT JOIN %tp%board_threads AS t ON(t.threadid = p.threadid)
							LEFT JOIN %tp%users AS u ON(u.userid = p.userid)
							WHERE t.threadid = ? AND t.published = 1 AND p.published = 1
							ORDER BY p.createdate DESC
							LIMIT 1";

					$rs = $this->db->query($sql, $r[ 'threadid' ])->fetch();

					$this->db->query('UPDATE %tp%board_threads
									  SET firstpostid = ?, lastposttime = ?, lastpostid = ?, lastposttitle = ?, postcounter = ?, lastpostuserid = ?, lastpostusername = ?
									  WHERE threadid = ?', $rs[ 'firstpostid' ], $rs[ 'createdate' ], intval($rs[ 'postid' ]), $rs[ 'lastposttitle' ], intval($rs[ 'total' ]), $rs[ 'userid' ], $rs[ 'username' ], $r[ 'threadid' ]);

				}
				break;
			case 'thread':

				$sql = "SELECT MAX(p.postid) AS postid,
							MIN(p.postid) AS firstpostid,
							COUNT(p.postid) AS total, p.createdate, p.userid,
							IF(
								p.userid>0,
								u.username,
								p.username
							) AS username,
							IF(p.title != '', p.title, t.title) AS lastposttitle
						FROM %tp%board_posts AS p
						LEFT JOIN %tp%board_threads AS t ON(t.threadid = p.threadid)
						LEFT JOIN %tp%users AS u ON(u.userid = p.userid)
						WHERE t.threadid = ? AND t.published = 1 AND p.published = 1
						ORDER BY p.createdate DESC
						LIMIT 1";

				$rs = $this->db->query($sql, $id)->fetch();

				$this->db->query('UPDATE %tp%board_threads
								  SET firstpostid = ?, lastposttime = ?, lastposttitle = ?, lastpostid = ?, postcounter = ?, lastpostuserid = ?, lastpostusername = ?
								  WHERE threadid = ?', $rs[ 'firstpostid' ], $rs[ 'createdate' ], $rs[ 'lastposttitle' ], intval($rs[ 'postid' ]), intval($rs[ 'total' ]), $rs[ 'userid' ], $rs[ 'username' ], $id);


				break;
		}
	}


	/**
	 *
	 * @param integer $forumid default is 0 and will update all forums and threads
	 * @return void
	 */
	public function updateForumCounters ( $forumid = 0 )
	{

		$sql = "UPDATE %tp%board b
			SET threadcounter = (
                                SELECT COUNT(t.forumid)
                                FROM %tp%board_threads AS t
                                WHERE t.forumid = b.forumid AND t.published = 1
                                GROUP BY t.forumid
                                LIMIT 1
                        ),
                        postcounter = (
                                SELECT COUNT(p.threadid)
                                FROM %tp%board_posts AS p
                                LEFT JOIN %tp%board_threads AS t2 ON(t2.threadid = p.threadid AND t2.published = 1)
                                WHERE t2.forumid = b.forumid AND p.published = 1
                                GROUP BY p.threadid
                                LIMIT 1
                        )";

		if ( $forumid )
		{
			$sql .= ' WHERE b.forumid = ?';
			$this->db->query($sql, $forumid);
		}
		else
		{
			$this->db->query($sql);
		}


	}

	/**
	 *
	 * @param integer $forumid default is 0 and will update all forums and threads
	 * @return void
	 */
	public function updateThreadCounters ( $forumid = 0 )
	{

		$sql = "UPDATE %tp%board_threads t
			SET postcounter = (
                                SELECT COUNT(p.threadid)
                                FROM %tp%board_posts AS p
                                WHERE p.threadid = t.threadid AND p.published = 1
                                GROUP BY p.threadid
                                LIMIT 1
                        ), attachmentcounter = (
                                SELECT COUNT(a.postid)
                                FROM %tp%board_attachments AS a LEFT JOIN %tp%board_posts AS p2 ON(a.postid = p2.postid ) 
                                WHERE p2.threadid = t.threadid AND p2.published = 1
                                GROUP BY a.postid
                                LIMIT 1
                        ), lastpostid = (
                            SELECT p3.postid FROM %tp%board_posts AS p3
                            WHERE p3.threadid = t.threadid AND p3.published = 1
                            ORDER BY p3.createdate DESC LIMIT 1
                        )";
		if ( $forumid )
		{
			$sql .= ' WHERE t.forumid = ?';
			$this->db->query($sql, $forumid);

			return;
		}

		$this->db->query($sql);
	}

	// ------------------------------- Forum Mod functions


	public function deleteThread ( $threadid = 0 )
	{

		if ( is_array($threadid) )
		{

			$this->db->query('DELETE FROM %tp%board_threads WHERE threadid IN(' . implode(',', $threadid) . ')');
			$this->db->query('DELETE FROM %tp%board_posts WHERE threadid IN(' . implode(',', $threadid) . ')');

			// clean search indexer
			$this->load('Indexer');
			$this->Indexer->initIndexer();
			$indexmodules = $this->Indexer->getIndexableModules();

			if ( isset( $indexmodules[ 'forum' ] ) )
			{
				$opt       = $indexmodules[ 'forum' ];
				$sectionid = $this->Indexer->getIndexSection($opt[ 'modul' ], CONTENT_TRANS, $opt[ 'location' ]);
				if ( $sectionid )
				{
					$this->db->query('DELETE FROM %tp%search_fulltext WHERE contentid IN(' . implode(',', $threadid) . ') AND section_id = ?', $sectionid);
				}
			}

			return;
		}

		$this->db->query('DELETE FROM %tp%board_threads WHERE threadid = ?', $threadid);
		$this->db->query('DELETE FROM %tp%board_posts WHERE threadid = ?', $threadid);
	}

	/**
	 *
	 * @param         integer /array $threadid
	 * @param integer $state
	 */
	public function changeThreadPublishing ( $threadid = 0, $state = 0 )
	{

		if ( is_array($threadid) )
		{
			$this->db->query('UPDATE %tp%board_threads SET published = ? WHERE threadid IN(' . implode(',', $threadid) . ')', $state);

			return;
		}
		$this->db->query('UPDATE %tp%board_threads SET published = ? WHERE threadid = ?', $state, $threadid);
	}

	/**
	 *
	 * @param         integer /array $threadid
	 * @param integer $state
	 */
	public function changeThreadCloseing ( $threadid = 0, $state = 0 )
	{

		if ( is_array($threadid) )
		{
			$this->db->query('UPDATE %tp%board_threads SET closed = ? WHERE threadid IN(' . implode(',', $threadid) . ')', $state);

			return;
		}
		$this->db->query('UPDATE %tp%board_threads SET closed = ? WHERE threadid = ?', $state, $threadid);
	}

	/**
	 *
	 * @param         integer /array $threadid
	 * @param integer $state
	 */
	public function changeImportantThread ( $threadid = 0, $state = 0 )
	{

		if ( is_array($threadid) )
		{
			$this->db->query('UPDATE %tp%board_threads SET threadtype = ? WHERE threadid IN(' . implode(',', $threadid) . ')', ( $state != 0 ? 20 : 0 ));

			return;
		}
		$this->db->query('UPDATE %tp%board_threads SET threadtype = ? WHERE threadid = ?', ( $state != 0 ? 20 : 0 ), $threadid);
	}

	public function changePostPublishing ( $postid, $state = 0 )
	{

		if ( is_array($postid) )
		{
			$this->db->query('UPDATE %tp%board_posts SET published = ? WHERE postid IN(' . implode(',', $postid) . ')', $state);

			return;
		}
		$this->db->query('UPDATE %tp%board_posts SET published = ? WHERE postid = ?', ( $state ? 1 : 0 ), $postid);
	}


	public function moveThread ( $threadid = 0, $sourceforumid = 0, $targetforumid = 0 )
	{

		if ( is_array($threadid) )
		{
			$this->db->query('UPDATE %tp%board_threads SET forumid = ? WHERE threadid IN(' . implode(',', $threadid) . ') ', $targetforumid);

			return;
		}

		$this->db->query('UPDATE %tp%board_threads SET forumid = ? WHERE threadid = ?', $targetforumid, $threadid);
	}


	public function getSearchIndexDataCount ()
	{

		$this->db->query('REPLACE INTO %tp%indexer (contentid, title, content, content_time, groups, alias, suffix, modul)
                        SELECT p.postid AS contentid, IF(p.title != \'\', p.title, t.title) AS title, p.content, p.createdate AS time, b.access AS groups, 
                        CONCAT(t.threadid, \'/\') AS alias, 
                        CONCAT(IF(p.alias != \'\', p.suffix, ?), \'?getpost=\', p.postid, \'#post-\', p.postid) AS suffix,
                        \'forum\'
                        FROM %tp%board_posts AS p
                        LEFT JOIN %tp%board_threads AS t ON(t.threadid = p.threadid)
                        LEFT JOIN %tp%board AS b ON(b.forumid = t.forumid)
                        WHERE p.published = 1 AND t.published = 1 AND b.published = 1
                        GROUP BY p.postid', Settings::get('mod_rewrite_suffix', 'html'));


		$r = $this->db->query('SELECT COUNT(p.postid) AS total FROM %tp%board_posts AS p
                                     LEFT JOIN %tp%board_threads AS t ON(t.threadid = p.threadid)
                                     LEFT JOIN %tp%board AS b ON(b.forumid = t.forumid)
                                     WHERE p.published = 1 AND t.published = 1 AND b.published = 1 ')->fetch();

		return $r[ 'total' ];
	}

	public function getSearchIndexData ( $from = 0, $limit = 200 )
	{

		$all = $this->db->query('SELECT p.postid AS contentid, t.threadid, t.alias, t.suffix, IF(p.title != \'\', p.title, t.title) AS title, t.title AS threadtitle, p.content,
                                  b.access AS groups, p.createdate AS time 
                                  FROM %tp%board_posts AS p
                                  LEFT JOIN %tp%board_threads AS t ON(t.threadid = p.threadid)
                                  LEFT JOIN %tp%board AS b ON(b.forumid = t.forumid)
                                  WHERE p.published = 1 AND t.published = 1 AND b.published = 1 
                                  GROUP BY p.postid LIMIT ' . $from . ', ' . $limit)->fetchAll();


		foreach ( $all as &$r )
		{
			$r[ 'title' ]       = preg_replace('#(Re:\s*)#i', '', $r[ 'title' ]);
			$r[ 'threadtitle' ] = preg_replace('#(Re:\s*)#i', '', $r[ 'threadtitle' ]);


			if ( $r[ 'threadtitle' ] && !empty( $r[ 'alias' ] ) )
			{

				$r[ 'alias' ]  = $r[ 'threadid' ] . '/' . Library::suggest($r[ 'threadtitle' ], false);
				$r[ 'suffix' ] = !empty( $r[ 'suffix' ] ) ? $r[ 'suffix' ] : Settings::get('mod_rewrite_suffix', 'html') . '?getpost=' . $r[ 'contentid' ] . '#post-' . $r[ 'contentid' ];
			}
			else
			{
				$r[ 'suffix' ] = !empty( $r[ 'suffix' ] ) ? $r[ 'suffix' ] : Settings::get('mod_rewrite_suffix', 'html') . '?getpost=' . $r[ 'contentid' ] . '#post-' . $r[ 'contentid' ];
			}
		}

		return $all;
	}

}
