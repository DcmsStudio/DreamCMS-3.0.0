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
 * @file         Base.php
 */
class Addon_Forum_Helper_Base extends Controller_Abstract
{

    protected $states = array(
        'published'    => 1, // Post/Thread is published
        'unpublished'  => 0, // Post/Thread is unpublished
        'deleted'      => 9, // Post is deleted
        'announcement' => 10, // Announcements
        'important'    => 20, // Important Topics
    );

    protected $cats = null;

    protected $catcache = array();

    private $cache = null;

    public $forum_cache = null;

    public $forum_by_id = null;

    public $currentForumID = 0;

    /**
     *
     * @var type
     */
    protected $forum = null;

    protected $postcache = array();

    protected $announcements = array();

    protected $importants = array();

    protected $modPermissions = array();

    protected $isWbbUpload = false;
    protected $uploadError = false;

    public function __construct()
    {

        parent::__construct();


        # $this->model = Model::getModelInstance( PLUGIN_MODEL );

        $this->modPermissions = array(
            'caneditpost'        => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann Beiträge bearbeiten' ),
                'default' => 0
            ),
            'candeletepost'      => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann Beiträge löschen' ),
                'default' => 0
            ),
            'canpublishpost'     => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann Beiträge freischalten' ),
                'default' => 0
            ),
            'canpinthread'       => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann Themen verankern (Pinnen)' ),
                'default' => 0
            ),
            'caneditthread'      => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann Themen bearbeiten' ),
                'default' => 0
            ),
            'canpublishthread'   => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann Themen freischalten' ),
                'default' => 0
            ),
            'candeletethread'    => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann Themen löschen' ),
                'default' => 0
            ),
            'canopenclosethread' => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann Themen öffnen/schließen' ),
                'default' => 0
            ),
            'canmove'            => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann Themen verschieben' ),
                'default' => 0
            ),
        );


        if ( $this->isFrontend() )
        {
            $this->load( 'Template' );
            $this->load( 'Plugin' );

            $this->Template->assign('pluginpath', PLUGIN_URL_PATH . PLUGIN . '/');
            $this->Template->addScript( 'html/js/jquery/wysibb/theme/default/wbbtheme.css', true );
            $this->Template->addScript( 'Packages/plugins/Forum/asset/css/frontend.css', true );

            if ( User::hasPerm( 'forum/canpostattachment' ) )
            {
                $this->Template->addScript( 'html/js/swfupload/swfupload.js', false );
                $this->Template->addScript( 'html/js/swfupload/plugins/swfupload.swfobject.js', false );
                $this->Template->addScript( 'html/js/swfupload/plugins/swfupload.cookies.js', false );
                $this->Template->addScript( 'html/js/swfupload/plugins/swfupload.queue.js', false );
            }

            $this->Template->addScript( 'Packages/plugins/Forum/asset/js/forum.js', false );

            $this->load( 'Document' );

            #$this->Document->disableSiteCaching();
        }

        return $this;
    }

    public function updateSearchIndexer($threadid, $postid = null, $mode = 1)
    {

        $opt            = Addon_Forum_Config_Base::getIndexerOptions();
        $opt[ 'modul' ] = strtolower( $this->Plugin->key );
        $buildIndex     = Indexer::getInstance();

        if ( $mode && $postid )
        {
            $all = $this->db->query( 'SELECT p.postid AS contentid, t.threadid, t.alias, t.suffix, IF(p.title != \'\', p.title, t.title) AS title, t.title AS threadtitle, p.content,
                                  b.access AS groups, p.createdate AS time 
                                  FROM %tp%board_posts AS p
                                  LEFT JOIN %tp%board_threads AS t ON(t.threadid = p.threadid)
                                  LEFT JOIN %tp%board AS b ON(b.forumid = t.forumid)
                                  WHERE p.published = 1 AND t.published = 1 AND b.published = 1 AND p.postid = ?
                                  GROUP BY p.postid ', $postid )->fetchAll();
            foreach ( $all as &$r )
            {
                if ( $r[ 'threadtitle' ] && !$r[ 'alias' ] )
                {
                    $r[ 'alias' ]  = $r[ 'threadid' ] . '/' . Library::suggest( $r[ 'threadtitle' ] );
                    $r[ 'suffix' ] = $r[ 'suffix' ] ? $r[ 'suffix' ] : Settings::get( 'mod_rewrite_suffix', 'html' ) . '?getpost=' . $r[ 'contentid' ] . '#post-' . $r[ 'contentid' ];
                }
            }

            $opt[ 'data' ] = $all;
        }
        else if ( $mode && $threadid )
        {
            $all = $this->db->query( 'SELECT p.postid AS contentid, t.threadid, t.alias, t.suffix, IF(p.title != \'\', p.title, t.title) AS title, t.title AS threadtitle, p.content,
                                  b.access AS groups, p.createdate AS time 
                                  FROM %tp%board_posts AS p
                                  LEFT JOIN %tp%board_threads AS t ON(t.threadid = p.threadid)
                                  LEFT JOIN %tp%board AS b ON(b.forumid = t.forumid)
                                  WHERE p.published = 1 AND t.published = 1 AND b.published = 1 AND t.threadid = ?
                                  GROUP BY p.postid ', $threadid )->fetchAll();
            foreach ( $all as &$r )
            {
                if ( $r[ 'threadtitle' ] && !$r[ 'alias' ] )
                {
                    $r[ 'alias' ]  = $r[ 'threadid' ] . '/' . Library::suggest( $r[ 'threadtitle' ] );
                    $r[ 'suffix' ] = $r[ 'suffix' ] ? $r[ 'suffix' ] : Settings::get( 'mod_rewrite_suffix', 'html' ) . '?getpost=' . $r[ 'contentid' ] . '#post-' . $r[ 'contentid' ];
                }
            }

            $opt[ 'data' ] = $all;
        }
        else
        {
            if ( !$mode && $threadid )
            {
                $all             = $this->db->query( 'SELECT p.postid AS contentid
                                  FROM %tp%board_posts AS p
                                  LEFT JOIN %tp%board_threads AS t ON(t.threadid = p.threadid)
                                  LEFT JOIN %tp%board AS b ON(b.forumid = t.forumid)
                                  WHERE p.published = 1 AND t.published = 1 AND b.published = 1 AND t.threadid = ?
                                  GROUP BY p.postid ', $threadid )->fetchAll();
                $opt[ 'remove' ] = $all;
            }
            else if ( !$mode && $postid )
            {
                $all             = $this->db->query( 'SELECT p.postid AS contentid
                                  FROM %tp%board_posts AS p
                                  LEFT JOIN %tp%board_threads AS t ON(t.threadid = p.threadid)
                                  LEFT JOIN %tp%board AS b ON(b.forumid = t.forumid)
                                  WHERE p.published = 1 AND b.published = 1 AND t.postid = ?
                                  GROUP BY p.postid ', $postid )->fetchAll();
                $opt[ 'remove' ] = $all;
            }
        }


        $buildIndex->buildIndex( $opt );
    }

    public function updateModeratorsCache()
    {

        $this->initForumCats();

        $mods    = array();
        $userids = array();
        foreach ( $this->forum_by_id as $forumid => $r )
        {
            if ( $r[ 'containposts' ] )
            {
                $result              = $this->model->getForumModerators( $forumid );
                $userids[ $forumid ] = array();
                $mods[ $forumid ]    = array();

                foreach ( $result as $mod )
                {
                    $userids[ $forumid ][ ] = $mod[ 'userid' ];
                    $mods[ $forumid ][ ]    = array(
                        'modid'    => $mod[ 'modid' ],
                        'userid'   => $mod[ 'userid' ],
                        'username' => $mod[ 'username' ]
                    );
                }

                $this->model->updateForumModerators( array(
                    'array'   => $mods[ $forumid ],
                    'userids' => $userids[ $forumid ]
                ), $forumid );

                unset( $result );
            }
        }
    }

    public function initForumCats()
    {


        if ( is_null( $this->cats ) )
        {
            $this->cats = array();
            $userid     = User::getUserId();
            $frontend   = $this->isFrontend();

            $moderation = $this->model->getModeratorByUserID( $userid );
            $result     = $this->model->getCategories( $frontend, $moderation );

            foreach ( $result as $r )
            {

                if ( $r[ 'containposts' ] )
                {
                    if ( $r[ 'moderatorids' ] != '' )
                    {
                        $r[ 'moderatorids' ] = explode( ',', $r[ 'moderatorids' ] );
                    }
                    else
                    {
                        $r[ 'moderatorids' ] = array();
                    }

                    if ( $r[ 'moderators' ] != '' )
                    {
                        $r[ 'moderators' ] = unserialize( $r[ 'moderators' ] );
                    }
                    else
                    {
                        $r[ 'moderators' ] = array();
                    }
                }

                if ( $r[ 'lang' ] != CONTENT_TRANS && !$frontend )
                {
                    $r[ 'title' ] .= ' *';
                }

                #       $r[ 'postcounter' ] = $r[ 'postcounter2' ];
                # $r[ 'threadcounter' ] = $r[ 'threadcounter2' ];

                if ( $frontend )
                {
                    if ( !Settings::get( 'showforumdescription', true ) )
                    {
                        unset( $r[ 'description' ] );
                    }
                }


                $this->forum_by_id[ $r[ 'forumid' ] ]                                = $r;
                $this->cats[ $r[ 'parent' ] ][ $r[ 'ordering' ] ][ $r[ 'forumid' ] ] = $r;
            }
        }
    }

    public function getForumTree($catid = 0, $depth = 0)
    {

        $this->initForumCats();

        // database has already been queried
        if ( is_array( $this->cats[ $catid ] ) )
        {
            foreach ( $this->cats[ $catid ] as $holder )
            {
                foreach ( $holder as $forum )
                {
                    // Check Usergroup
                    if ( $forum[ 'access' ] != '' && !in_array( User::getGroupId(), explode( ',', $forum[ 'access' ] ) ) && !in_array( 0, explode( ',', $forum[ 'access' ] ) ) )
                    {
                        #  continue;
                    }

                    $this->catcache[ $forum[ 'forumid' ] ]            = $forum;
                    $this->catcache[ $forum[ 'forumid' ] ][ 'depth' ] = $depth;

                    if ( isset( $this->cats[ $forum[ 'forumid' ] ] ) )
                    {
                        $this->getForumTree( $forum[ 'forumid' ], $depth + 1 );
                    }
                }
            }
        }
    }

    public function initCache()
    {

        $this->initForumCats();

        if ( is_null( $this->forum_cache ) )
        {
            $this->getForumTree();

            foreach ( $this->catcache as $forumid => $f )
            {
                $this->forum_by_id[ $forumid ] = $f;

                if ( $f[ 'parent' ] < 1 )
                {
                    $f[ 'parent' ] = 'root';
                }

                $this->forum_cache[ $f[ 'parent' ] ][ $forumid ] = $f;
            }
        }
    }

    /**
     * Updates the thread and post counter for the given boards.
     *
     * @param integer $forumid default is 0 and will update all forums and threads
     */
    public function updateForumCounter($forumid = 0)
    {

        $sql = "UPDATE %tp%board b
			SET threadcounter = (
                                SELECT COUNT(*)
                                FROM %tp%board_threads AS t
                                WHERE t.forumid = b.forumid AND t.published = 1
                        ),
                        postcounter = (
                                SELECT COUNT(*)
                                FROM %tp%board_posts AS p
                                LEFT JOIN %tp%board_threads AS t2 ON(t.threadid = p.threadid AND t2.published = 1)
                                WHERE t2.forumid = b.forumid AND p.published = 1
                        )";

        $sql2 = "UPDATE %tp%board_threads t
			SET postcounter = (
                                SELECT COUNT(*)
                                FROM %tp%board_posts AS p
                                WHERE p.threadid = t.threadid AND p.published = 1
                        ), attachmentcounter = (
                                SELECT COUNT(*) 
                                FROM %tp%board_attachments AS a LEFT JOIN %tp%board_posts AS p2 ON(a.postid = p2.postid ) 
                                WHERE p2.threadid = t.threadid AND p2.published = 1
                        )";

        if ( $forumid )
        {
            $sql .= ' WHERE b.forumid = ?';
            $this->db->query( $sql, $forumid );

            $sql2 .= ' WHERE t.forumid = ?';
            $this->db->query( $sql2, $forumid );

            return;
        }

        $this->db->query( $sql );
        $this->db->query( $sql2 );

        return;
        $_parents = array_reverse( $parents );


        $this->db->query( 'UPDATE %tp%board_trans SET threadcounter = 0, postcounter = 0 WHERE forumid = ?', $forumid );
        $tcount = $this->db->query( 'SELECT COUNT(threadid) AS total FROM %tp%board_threads WHERE forumid = ? AND published = 1', $forumid )->fetch();
        $pcount = $this->db->query( '
                SELECT COUNT(p.postid) AS total
                FROM %tp%board_posts AS p
                LEFT JOIN %tp%board_threads AS t ON(t.threadid = p.threadid AND t.published = 1)
                WHERE t.forumid = ? AND p.published = 1 AND t.pageid = ?', $forumid, PAGEID )->fetch();

        $this->db->query( 'UPDATE %tp%board_trans
            SET threadcounter = threadcounter + ' . (int)$tcount[ 'total' ] . ', postcounter = postcounter + ' . (int)$pcount[ 'total' ] . '
            WHERE forumid = ?', $forumid );
    }

    /**
     *
     * @param array $parents
     * @param array $thread
     */
    public function buildBreadCrumb($parents = array(), $thread = array())
    {

        $this->load( 'Breadcrumb' );

        $action = strtolower( PLUGIN_ACTION );
        $parents = is_array( $parents ) ? array_reverse( $parents ) : array();
        $this->Breadcrumb->add( trans( 'Forum' ), '/forum' );
        $countParents = count( $parents );
        $currentForum = array();
        foreach ( $this->catcache as $idx => $forum )
        {
            if ( $this->currentForumID == $forum[ 'forumid' ] )
            {
                $currentForum = $forum;
                break;
            }
        }

        if ( $action === 'run' )
        {


            if ( isset($forum[ 'forumid' ])) {
                foreach ( $this->catcache as $forum )
                {
                    foreach ( $parents as $parentid )
                    {
                        if ( $forum[ 'forumid' ] === $parentid )
                        {
                            $this->Breadcrumb->add( $forum[ 'title' ], '/forum/' . $forum[ 'forumid' ] . '/' . Url::makeRw( $forum[ 'alias' ], $forum[ 'suffix' ], $forum[ 'title' ] ) );
                        }
                    }
                }
            }



            if ( isset( $currentForum[ 'title' ] ) )
            {
                $this->Breadcrumb->add( $currentForum[ 'title' ], '' );
            }
        }
        else
        {

            foreach ( $this->forum_cache as $idx => $row )
            {
                foreach ( $row as $idx => $forum )
                {

                    foreach ( $parents as $parentid )
                    {
                        if ( isset($forum[ 'forumid' ]) && isset( $forum[ 'title' ] ) && $forum[ 'forumid' ] === $parentid )
                        {
                            $this->Breadcrumb->add( $forum[ 'title' ], '/forum/' . $forum[ 'forumid' ] . '/' . Url::makeRw( $forum[ 'alias' ], $forum[ 'suffix' ], $forum[ 'title' ] ) );
                        }
                    }

                    if ( ( isset($thread[ 'forumid' ]) && isset($forum[ 'forumid' ]) && $thread[ 'forumid' ] && $thread[ 'forumid' ] === $forum[ 'forumid' ] ) || $forum[ 'forumid' ] === $this->currentForumID )
                    {
                        $this->Breadcrumb->add( $forum[ 'title' ], '/forum/' . $forum[ 'forumid' ] . '/' . Url::makeRw( $forum[ 'alias' ], $forum[ 'suffix' ], $forum[ 'title' ] ) );
                    }
                }
            }
        }

        if ( isset( $thread[ 'title' ] ) && $action === 'thread' )
        {
            $this->Breadcrumb->add( $thread[ 'title' ], '' );
        }

        if ( isset( $thread[ 'title' ] ) && $action === 'replythread' )
        {
            $this->Breadcrumb->add( $thread[ 'title' ], '/forum/thread/' . $thread[ 'threadid' ] . '/' . Url::makeRw( $thread[ 'alias' ], $thread[ 'suffix' ], $thread[ 'title' ] ) );
            $this->Breadcrumb->add( sprintf( trans( 'Auf das Thema `%s` antworten ' ), $thread[ 'title' ] ), '' );
        }

        if ( $action === 'newthread' )
        {
            $this->Breadcrumb->add( trans( 'Neues Thema anlegen' ), '' );
        }

        if ( isset( $thread[ 'title' ] ) && $action === 'loadattachment' )
        {
            $this->Breadcrumb->add( $thread[ 'title' ], '/forum/thread/' . $thread[ 'threadid' ] . '/' . Url::makeRw( $thread[ 'alias' ], $thread[ 'suffix' ], $thread[ 'title' ] ) );
            $this->Breadcrumb->add( trans( 'Attachment Download' ), '' );
        }
    }

    /* ------------------------------------------------------------------------- */

    // Get parents
    // ------------------
    // Find all the parents of a child without getting the nice lady to
    // use the superstore tannoy to shout "Small ugly boy in tears at reception"
    /* ------------------------------------------------------------------------- */
    public function getParents($root_id = 0, $ids = array())
    {

        if ( isset( $this->catcache[ $root_id ] ) && is_array( $this->catcache[ $root_id ] ) )
        {
            $parentID = $this->catcache[ $root_id ][ 'parent' ];
            $ids[ ]   = $parentID;

            if ( isset( $this->catcache[ $parentID ] ) )
            {
                $ids = $this->getParents( $parentID, $ids );
            }
        }

        return $ids;
    }

    /* ------------------------------------------------------------------------- */

    // Gets children (Debug purposes)
    /* ------------------------------------------------------------------------- */
    public function getChildren($root_id = 0, $ids = array())
    {

        if ( isset( $this->forum_cache[ $root_id ] ) && is_array( $this->forum_cache[ $root_id ] ) )
        {
            foreach ( $this->forum_cache[ $root_id ] as $id => $forum_data )
            {
                if (!isset($forum_data[ 'access' ]) || (!in_array( User::getGroupId(), explode( ',', $forum_data[ 'access' ] ) ) && !in_array( 0, explode( ',', $forum_data[ 'access' ] ) )) )
                {
                    continue;
                }

                $ids[ ] = $forum_data[ 'forumid' ];
                $ids    = $this->getChildren( $forum_data[ 'forumid' ], $ids );
            }
        }

        return $ids;
    }

    /**
     *
     * @param type $root_id
     * @param type $forum_data
     * @return type
     */
    public function childForums($root_id, $forum_data = array())
    {

        if ( isset( $this->forum_cache[ $root_id ] ) && is_array( $this->forum_cache[ $root_id ] ) )
        {
            foreach ( $this->forum_cache[ $root_id ] as $id => $data )
            {
                if ( !isset($data[ 'access' ]) || (!in_array( User::getGroupId(), explode( ',', $data[ 'access' ] ) ) && !in_array( 0, explode( ',', $data[ 'access' ] ) )) )
                {
                    continue;
                }

                $forum_data[ 'subforums' ][ ] = $data;
                if ( isset( $this->forum_cache[ $data[ 'forumid' ] ] ) && is_array( $this->forum_cache[ $data[ 'forumid' ] ] ) )
                {
                    $forum_data[ 'subforums' ] = $this->_forumsForumJumpInternal( $data[ 'forumid' ], $forum_data[ 'subforums' ] );
                }

                //$forum_data[ 'subforums' ][ ] = $data;
            }
        }

        return $forum_data;
    }

    /**
     *
     * @param type $root_id
     * @param type $forum_data
     * @return type
     */
    public function childForumList()
    {

        $tree = array();
        if ( is_array( $this->forum_cache[ 'root' ] ) && count( $this->forum_cache[ 'root' ] ) )
        {
            foreach ( $this->forum_cache[ 'root' ] as $forum_data )
            {
                if ( isset( $this->forum_cache[ $forum_data[ 'forumid' ] ] ) && is_array( $this->forum_cache[ $forum_data[ 'forumid' ] ] ) )
                {
                    $forum_data[ 'children' ] = $this->_forumsForumJumpInternal( $forum_data[ 'forumid' ], array() );
                    $tree[ ]                  = $forum_data;
                }
                else
                {
                    $tree[ ] = $forum_data;
                }
            }
        }

        return $tree;

    }


    /**
     * Internal helper function for forumsForumJump
     *
     * @param    integer $root_id
     * @param    string $jump_string
     * @param    string $depth_guide
     * @param    bool $html
     * @param    bool $override
     * @param    bool $remove_redirects
     * @param    array $defaulted
     * @return    string
     */
    protected function _forumsForumJumpInternal($root_id, $tree = array(), $depth_guide = "", $html = 0, $override = 0, $remove_redirects = 0, $defaulted = array())
    {

        if ( isset( $this->forum_cache[ $root_id ] ) && is_array( $this->forum_cache[ $root_id ] ) )
        {
            foreach ( $this->forum_cache[ $root_id ] as $forum_data )
            {
                if ( isset( $this->forum_cache[ $forum_data[ 'forumid' ] ] ) )
                {
                    $forum_data[ 'children' ] = $this->_forumsForumJumpInternal( $forum_data[ 'forumid' ], array() );
                    $tree[ ]                  = $forum_data;
                }
                else
                {
                    $tree[ ] = $forum_data;
                }
            }
        }

        return $tree;
    }


    /**
     *
     * @return type
     */
    public function loadPostings($isMod = false)
    {

        // $date = new Date();
        $opts = Session::get( 'forumoptions' );

        $pp    = ( HTTP::input( 'perpage' ) && intval( HTTP::input( 'perpage' ) ) ? intval( HTTP::input( 'perpage' ) ) : Settings::get( 'forum.threadsperpage' ) );
        $limit = ( !empty( $pp ) && intval( $pp ) ? intval( $pp ) : ( isset( $opts[ 'perpage' ] ) && $opts[ 'perpage' ] > 0 ? $opts[ 'perpage' ] : $this->forum[ 'threadsperpage' ] ) );
        $page  = ( HTTP::input( 'page' ) ? intval( HTTP::input( 'page' ) ) : ( isset( $opts[ 'page' ] ) && $opts[ 'page' ] > 0 ? $opts[ 'page' ] : 1 ) );

        $timefilter = ( HTTP::input( 'timefilter' ) ? HTTP::input( 'timefilter' ) : ( isset( $opts[ 'timefilter' ] ) && $opts[ 'timefilter' ] ? $opts[ 'timefilter' ] : 'all' ) );
        $sort       = ( HTTP::input( 'sort' ) ? HTTP::input( 'sort' ) : ( isset( $opts[ 'sort' ] ) && $opts[ 'sort' ] ? $opts[ 'sort' ] : Settings::get( 'forum.threadsort' ) ) );
        $order      = ( HTTP::input( 'order' ) ? HTTP::input( 'order' ) : ( isset( $opts[ 'order' ] ) && $opts[ 'order' ] ? $opts[ 'order' ] : Settings::get( 'forum.threadorder' ) ) );
        $q          = ( HTTP::input( 'q' ) ? HTTP::input( 'q' ) : ( isset( $opts[ 'q' ] ) && $opts[ 'q' ] ? $opts[ 'q' ] : null ) );

        Session::save( 'forumoptions', array(
            'timefilter' => $timefilter,
            'perpage'    => $limit,
            'sort'       => $sort,
            'order'      => $order,
            'q'          => $q
        ) );


        $opts = Session::get( 'forumoptions' );

        $this->Input->set( 'page', $page );
        $this->Input->set( 'sort', $opts[ 'sort' ] );
        $this->Input->set( 'order', $opts[ 'order' ] );
        $this->Input->set( 'q', $opts[ 'q' ] );
        $this->Input->set( 'timefilter', $opts[ 'timefilter' ] );


        $_announcementsids = array();

        /**
         * Announcements and Important Topics on the first page
         */
        if ( $page == 1 )
        {
            $this->announcements = $this->model->getAnnouncementsAndImportants( $isMod, $this->forum[ 'forumid' ], $opts[ 'sort' ], $opts[ 'order' ], $opts[ 'q' ], $opts[ 'timefilter' ] );

            foreach ( $this->announcements as $idx => $r )
            {
                if ( $this->forum[ 'allowicons' ] && $r[ 'iconpath' ] )
                {
                    $r[ 'posticon' ] = HTML_URL . 'img/icons/' . $r[ 'iconpath' ];
                    if ( !file_exists( $r[ 'posticon' ] ) )
                    {
                        $r[ 'posticon' ] = HTML_URL . 'img/icons/default.gif';
                    }
                }
                else
                {
                    $r[ 'posticon' ] = HTML_URL . 'img/icons/default.gif';
                }
                $r[ 'hits' ] = (string)$r[ 'hits' ];

                // Announcements
                if ( $r[ 'threadtype' ] == 10 )
                {
                    $r[ 'content' ] = BBCode::removeBBCode( $r[ 'content' ] );
                    $r[ 'content' ] = substr( trim( $r[ 'content' ] ), 0, Settings::get( 'threadpreview', 150 ) );


                    $r[ 'postcounter' ] = (string)( $r[ 'postcounter' ] ? $r[ 'postcounter' ] - 1 : 0 );


                    $this->announcements[ $idx ] = $r;
                }

                // Important Topics
                if ( $r[ 'threadtype' ] == 20 )
                {
                    $r[ 'content' ] = BBCode::removeBBCode( $r[ 'content' ] );
                    $r[ 'content' ] = substr( trim( $r[ 'content' ] ), 0, Settings::get( 'threadpreview', 150 ) );

                    $r[ 'postcounter' ] = (string)( $r[ 'postcounter' ] ? $r[ 'postcounter' ] - 1 : 0 );


                    $this->importants[ $idx ] = $r;

                    unset( $this->announcements[ $idx ] );
                }


                $r[ 'lastposttitle' ] = preg_replace( '#(RE:){2,}#', 'RE:', $r[ 'lastposttitle' ] );


                $_announcementsids[ ] = $r[ 'threadid' ];
            }
        }


        $postCache = $this->model->getPosts( $isMod, $this->forum, $limit, $page, $opts[ 'sort' ], $opts[ 'order' ], $opts[ 'q' ], $opts[ 'timefilter' ] );

        $this->postcache = $postCache[ 'result' ];
        $_news_found     = $postCache[ 'total' ];
        $_pages          = $postCache[ 'total' ] ? ceil( $postCache[ 'total' ] / $limit ) : 1;

        $this->Input->set( 'pages', $_pages );

        unset( $postCache );


        $pages = null;
        if ( $_pages )
        {
            $this->pages = Library::paging( $page, $_pages, "forum/" . $this->forum[ 'forumid' ] );
        }

        foreach ( $this->postcache as $idx => &$r )
        {
            if ( $r[ 'postcounter' ] > 1 )
            {
                $r[ 'lastposttitle' ] = preg_replace( '#(RE: RE: RE:|RE: RE:| RE:)#sS', 'RE:', $r[ 'lastposttitle' ] );

                if ( isset( $r[ 'firstpostid' ] ) && isset( $r[ 'postid' ] ) && $r[ 'firstpostid' ] != $r[ 'postid' ] )
                {
                    if ( strpos( $r[ 'lastposttitle' ], 'RE: ' ) === false )
                    {
                        $r[ 'lastposttitle' ] = 'RE: ' . $r[ 'lastposttitle' ];
                    }
                }
            }
            $ids[ ] = $r[ 'threadid' ];
        }


        $dotthreads = array();
        if ( count( $ids ) && User::getUserId() > 0 && Settings::get( 'forum.showdots', true ) )
        {
            $dotthreads = $this->model->getDotThreads( $isMod, $ids );
        }


        $lastread = User::get( 'lastvisit' );

        foreach ( $this->postcache as $idx => &$r )
        {

            if ( in_array( $r[ 'threadid' ], $_announcementsids ) )
            {
                unset( $this->postcache[ $idx ] );
                continue;
            }

            // folder icon generation
            $r[ 'foldericon' ] = '';

            // Eigene beiträge
            // show dot folder?
            if ( User::getUserId() > 0 && Settings::get( 'forum.showdots', true ) && isset( $dotthreads[ $r[ 'threadid' ] ] ) )
            {
                $r[ 'foldericon' ] .= '_dot';
                $r[ 'dot_count' ]    = $dotthreads[ $r[ 'threadid' ] ][ 'count' ];
                $r[ 'dot_lastpost' ] = $dotthreads[ $r[ 'threadid' ] ][ 'lastpost' ];
            }

            // show hot folder?
            if ( Settings::get( 'forum.usehotthreads', true ) AND ( ( $r[ 'postcounter' ] >= Settings::get( 'forum.hotnumberposts', 50 ) ) OR ( $r[ 'hits' ] >= Settings::get( 'forum.hotnumberposts', 50 ) ) )
            )
            {
                $r[ 'foldericon' ] .= '_hot';
            }

            // show locked folder?
            if ( $r[ 'closed' ] == 1 )
            {
                $r[ 'foldericon' ] .= '_lock';
            }

            $threadview = Session::get( 'thread_lastview-' . $r[ 'threadid' ] );

            // show new folder?
            if ( $r[ 'createdate' ] > $lastread )
            {
                if ( $r[ 'createdate' ] > $threadview )
                {
                    $r[ 'foldericon' ] .= '_new';
                    $r[ 'gotonewpost' ] = true;
                }
                else
                {
                    $r[ 'gotonewpost' ] = false;
                }
            }
            else
            {
                $r[ 'gotonewpost' ] = false;
            }


            if ( $r[ 'iconpath' ] )
            {
                $r[ 'posticon' ] = HTML_URL . 'img/icons/' . $r[ 'iconpath' ];

                // if not exists then use the default post icon
                if ( !file_exists( PUBLIC_PATH . 'img/icons/' . $r[ 'iconpath' ] ) )
                {
                    $r[ 'posticon' ] = HTML_URL . 'img/icons/default.gif';
                }
            }
            else
            {
                $r[ 'posticon' ] = HTML_URL . 'img/icons/default.gif';
            }

            $r[ 'postcounter' ] = (string)( $r[ 'postcounter' ] ? $r[ 'postcounter' ] - 1 : 0 );
            $r[ 'hits' ]        = (string)$r[ 'hits' ];

            // $r[ 'lastposttitle' ] = preg_replace('#(RE: RE: RE:|RE: RE:| RE:)#sS', 'RE:', $r[ 'lastposttitle' ]);

            $r[ 'content' ] = BBCode::removeBBCode( $r[ 'content' ] );
            $r[ 'content' ] = substr( trim( $r[ 'content' ] ), 0, Settings::get( 'threadpreview', 150 ) );

            // $this->postcache[ $idx ] = $r;
        }


        $this->postcache = array_merge( $this->importants, $this->announcements, $this->postcache );


        return $pages;
    }

    /**
     *
     * @return array
     */
    protected function loadPostIcons()
    {

        $result = $this->db->query( 'SELECT * FROM %tp%icons' )->fetchAll();
        foreach ( $result as $idx => $r )
        {
            $r[ 'icon' ] = HTML_URL . 'img/icons/' . $r[ 'iconpath' ];
            if ( !file_exists( PUBLIC_PATH . 'img/icons/' . $r[ 'iconpath' ] ) )
            {
                unset( $result[ $idx ] );
            }

            $result[ $idx ] = $r;
        }

        $result[ ] = array(
            'iconpath' => 'default.gif',
            'iconid'   => 0,
            'icon'     => HTML_URL . 'img/icons/default.gif'
        );

        return $result;
    }

    protected function get_server_var($id)
    {

        return isset( $_SERVER[ $id ] ) ? $_SERVER[ $id ] : '';
    }

    /**
     * @param int $postid used from classic upload
     */
    public function doUpload($classicUpload = false)
    {

        if ( $this->_post( 'do' ) === 'remove' )
        {
            $id       = intval( $this->_post( 'id' ) );
            $posthash = $this->_post( 'posthash' );
            if ( !$id || !$posthash )
            {
                Library::sendJson( false, trans( 'Unbekanntes Attachment' ) );
            }

            $attach = $this->model->getAttachmentById( $id, false );
            if ( $attach[ 'posthash' ] != $posthash )
            {
                Library::sendJson( false, trans( 'Unbekanntes Attachment' ) );
            }

            @unlink( PAGE_PATH . $attach[ 'path' ] );

            $this->db->query( 'DELETE FROM %tp%board_attachments WHERE attachmentid = ? AND posthash = ?', $id, $posthash );
            Library::sendJson( true, trans( 'Attachment wurde entfernt' ) );
            exit;
        }


        if ( $this->input( 'mode' ) === 'wbb' )
        {
            $this->isWbbUpload = true;
            $isIframe          = $this->input( 'iframe' ) ? true : false;
            $idarea            = $this->input( 'idarea' );

           // $maxwidth          = is_numeric( $this->input( 'maxwidth' ) ) ? $this->input( 'maxwidth' ) : 600;
           // $maxheight         = is_numeric( $this->input( 'maxheight' ) ) ? $this->input( 'maxheight' ) : 600;

            $image_link = $thumb_link = '';
            $imgData = null;


            if ( isset( $_FILES[ 'img' ] ) )
            {
                $path               = $this->giveFolder( 'file/forum', true, true, false );
                $uploader           = new Upload( $path, 'img', User::getPerm( 'forum/maxuploadsize' ) * 1024, User::getPerm( 'forum/allowedattachmentextensions' ) );
                $uploader->checkXXS = true;
                $imgData            = $uploader->execute( array($this, '_onUploaded'), true );

                $image_link         = $imgData['fileurl'];

                if ( is_file(ROOT_PATH . $imgData[ 'fileurl' ]) )
                {
                    $img = ImageTools::create(PAGE_CACHE_PATH . 'thumbnails/forum');
                    $chain = array (
                        0 => array (
                            0 => 'resize',
                            1 => array (
                                'width'       => intval(Settings::get('forum.attachthumbwidth', 100)),
                                'height'      => intval(Settings::get('forum.attachthumbheight', 100)),
                                'keep_aspect' => true,
                                'shrink_only' => false
                            )
                        )
                    );

                    $_data = $img->process(
                        array(
                            'source' => Library::formatPath(ROOT_PATH . $imgData[ 'fileurl' ]),
                            'output' => 'png',
                            'chain'  => $chain
                        )
                    );

                    if ( $_data['path'] )
                    {
                        $thumb_link = str_replace(ROOT_PATH, '', $_data['path']);
                    }
                }
            }



            if ( !isset( $uploader ) )
            {
                if ( !$isIframe )
                {
                    Library::json( array('status' => 0, 'msg' => 'Uploader instance not set!') );
                }
                else
                {
                    if ( $isIframe )
                    {
                        echo '<html><body>-</body></html>';
                    }
                    else
                    {
                        return false;
                    }
                }
            }

            if ( !$uploader->success() )
            {
                if ( !$isIframe )
                {
                    Library::json( array('status' => 0, 'msg' => $uploader->getError()) );
                }
                else
                {

                    $this->uploadError = $uploader->getError();
                    if ( $isIframe )
                    {
                        echo '<html><body>'.$this->uploadError.'</body></html>';
                    }
                    else
                    {
                        return false;
                    }
                }
            }
            else
            {
                if ( !$isIframe )
                {
                    Library::json( array('status' => true, 'msg' => 'OK', 'image_link' => $image_link, 'thumb_link' => $thumb_link) );
                }
                else
                {
                    if ( $isIframe )
                    {
                        #use for iframe upload
                        echo '<html><body>OK<script>window.parent.$("#' . $idarea . '").insertImage("' . $image_link . '","' . $thumb_link . '").closeModal().updateUI();</script></body></html>';
                    }
                    else
                    {
                        return true;
                    }
                }
            }

            exit;
        }


        if ( $classicUpload )
        {
            if ( isset( $_FILES[ 'uploadFile' ] ) )
            {
                $path               = $this->giveFolder( 'file/forum', true, true, false );
                $uploader           = new Upload( $path, 'uploadFile', User::getPerm( 'forum/maxuploadsize' ) * 1024, User::getPerm( 'forum/allowedattachmentextensions' ) );
                $uploader->checkXXS = true;
                $uploader->execute( array($this, '_onUploaded'), true );


                if ( !$uploader->success() )
                {
                    if ( IS_AJAX )
                    {
                        Library::sendJson( false, $uploader->getError() );
                    }
                    else
                    {

                        $this->uploadError = $uploader->getError();

                        return false;
                    }
                }
                else
                {
                    if ( IS_AJAX )
                    {
                        Library::json( array('success' => true) );
                    }
                    else
                    {
                        return true;
                    }
                }

                exit;
            }
            else
            {
                return true;
            }
        }
        else
        {
            if ( IS_SWFUPLOAD )
            {

                $path = $this->giveFolder( 'file/forum', true, true, false );

                $uploader           = new Upload( $path, 'uploadFile', User::getPerm( 'forum/maxuploadsize' ) * 1024, User::getPerm( 'forum/allowedattachmentextensions' ) );
                $uploader->checkXXS = true;
                $uploader->execute( array($this, '_onUploaded'), true );
            }
            else
            {
                Library::sendJson( false, trans( 'Upload Fehler' ) );
            }

            exit;
        }
    }

    /**
     * @param        $data
     * @param Upload $uploader
     */
    public function _onUploaded($data, Upload $uploader)
    {

        if ( $data[ 'success' ] )
        {
            $this->db->query( 'INSERT INTO %tp%board_attachments (postid,userid,path,hits,filesize,mime,posthash)
							  VALUES(?,?,?,?,?,?,?)', 0, User::getUserId(), str_replace( PAGE_PATH, '', $data[ 'filepath' ] ), 0, $data[ 'filesize' ], $data[ 'filemime' ], $this->input( 'posthash' ) );

            if ( $this->isWbbUpload )
            {
                return array(
                    'id'       => $this->db->insert_id(),
                    'fileurl'  => str_replace( ROOT_PATH, '', $data[ 'filepath' ] ),
                    'filesize' => $data[ 'filesize' ],
                    'isimage'  => $data[ 'isimage' ],
                    'filename' => $data[ 'filename' ],
                );
            }

            if ( IS_AJAX )
            {
                echo Library::json( array(
                    'success'  => true,
                    'id'       => $this->db->insert_id(),
                    'fileurl'  => str_replace( PAGE_PATH, '', $data[ 'filepath' ] ),
                    'filesize' => $data[ 'filesize' ],
                    'isimage'  => $data[ 'isimage' ],
                    'filename' => $data[ 'filename' ],
                ) );

                exit;
            }
        }
        else
        {
            if ( $this->isWbbUpload )
            {
                return false;
            }
            if ( IS_AJAX )
            {
                Library::sendJson( false, $uploader->getError() );
            }
            else
            {
                return false;
            }

        }
    }

    public function updateUploads($postid, $posthash)
    {

        $this->db->query( 'UPDATE %tp%board_attachments SET postid = ? WHERE posthash = ?', $postid, $posthash );
    }

}

?>