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
 * @package      News
 * @version      3.0.0 Beta
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Mysql.php
 */
class News_Model_Mysql extends Model
{


    /**
     *
     * @param integer $id
     * @return array
     */
    public function getSerachItem($id = 0)
    {

        $transq1 = $this->buildTransWhere( 'news', 'n.id', 'nt' );
        $sql     = 'SELECT n.*, nt.text AS content, nt.title
                FROM %tp%news AS n 
                LEFT JOIN %tp%news_trans AS nt ON(nt.id=n.id )
                WHERE n.pageid = ? AND n.id = ? AND ' . $transq1 . ' GROUP BY n.id';

        return $this->db->query( $sql, PAGEID, $id )->fetch();
    }

    /**
     *
     * @param string $alias
     * @return mixed (array/bool)
     */
    public function findItemByAlias($alias = '')
    {


        $ac = defined( 'ACTION' ) ? ACTION : $GLOBALS[ 'tmp_ACTION' ];

        switch ( strtolower( $ac ) )
        {
            case 'index':

                if ( $this->getApplication()->isFrontend() && ( !User::isAdmin() || !User::hasPerm('generic/canviewofflinedocuments')) && !IS_SEEMODE )
                {
                    $publish = ' n.published>0 AND c.published=1 AND n.draft = 0 AND
					((n.publishoff>0 AND n.publishoff>=' . TIMESTAMP . ') OR n.publishoff=0) AND
					((n.publishon>0 AND n.publishon <= ' . TIMESTAMP . ') OR n.publishon = 0 AND n.created <= ' . TIMESTAMP . ') AND ';


                    $groupQuery  = '';
                    $groupQueryC = '';
                    $groupQuery  = '';
                }

                $catid   = (int)HTTP::input( 'catid' ) ? (int)HTTP::input( 'catid' ) : 0;
                $transq1 = $this->buildTransWhere( 'news_categories', 'c.id', 'ct' );

                $sql = 'SELECT c.*, ct.*
                        FROM %tp%news_categories AS c
                        LEFT JOIN %tp%news_categories_trans AS ct ON(ct.id=c.id)
                        WHERE
                            ' . $groupQueryC . ' c.published=1
                            AND c.locked = 0 AND c.pageid = ? AND ' . ( $catid ? 'ct.id = ?' : 'ct.alias = ?' ) . ' AND ' . $transq1 . '
                        GROUP BY c.id';

                return $this->db->query( $sql, PAGEID, ( $catid ? $catid : $alias ) )->fetch();
                break;

            case 'show':
            case 'item':

                if ( $this->getApplication()->isFrontend() && ( !User::isAdmin() || !User::hasPerm('generic/canviewofflinedocuments')) && !IS_SEEMODE )
                {
                    $publish = ' n.published>0 AND c.published=1 AND
                        ((n.publishoff>0 AND n.publishoff>=' . TIMESTAMP . ') OR n.publishoff=0) AND
                        ((n.publishon>0 AND n.publishon <= ' . TIMESTAMP . ') OR n.publishon = 0 AND n.created <= ' . TIMESTAMP . ') AND ';


                    $groupQuery  = '';
                    $groupQueryC = '';
                    $groupQuery  = '';
                }


                $transq1 = $this->buildTransWhere( 'news', 'n.id', 'nt' );
                $transq2 = $this->buildTransWhere( 'news_categories', 'c.id', 'ct' );

                $sql = 'SELECT n.*, nt.*, c.clickanalyse AS catclickanalyse,
                            ct.title AS category, ct.title AS cattitle, ct.alias AS catalias, ct.suffix AS catsuffix,
                            c.clickanalyse AS catclickanalyse,
                            c.cacheable AS catcacheable,
                            c.cachetime AS catcachetime,
                            c.cachegroups AS catcachegroups,
                            c.access AS cat_access,
                            c.published AS catpublished,
                            u.username AS newsauthor,
                            u1.username AS modifyauthor,
                            (SELECT COUNT(*) FROM %tp%comments WHERE post_id = n.id AND modul=\'news\') AS comments
                        FROM %tp%news AS n
                        LEFT JOIN %tp%news_trans AS nt ON(nt.id=n.id )
                        LEFT JOIN %tp%news_categories AS c ON(c.id=n.cat_id)
                        LEFT JOIN %tp%news_categories_trans AS ct ON(ct.id=c.id)
                        LEFT JOIN %tp%users AS u ON (u.userid = n.created_by)
                        LEFT JOIN %tp%users AS u1 ON (u1.userid = n.modifed_by )
                        WHERE
                            ' . $groupQuery . '' . $publish . '
                            n.pageid = ? AND nt.alias = ? AND ' . $transq1 . ' AND ' . $transq2 . '
                        GROUP BY n.id';

                return $this->db->query( $sql, PAGEID, $alias )->fetch();
                break;
        }

        return false;
    }

    /**
     * @param array $ids
     * @return array
     */
    public function findItemsByID($ids = array())
    {

        //$com = Comments::getCountQuery(array('prefix' => 'com', 'joinon' => 'n.id', 'source' => 'news'));
        $transq1 = $this->buildTransWhere( 'news', 'n.id', 'nt' );
        $transq2 = $this->buildTransWhere( 'news_categories', 'c.id', 'ct' );

        $publish = '';
        if ( $this->getApplication()->isFrontend() && ( !User::isAdmin() || !User::hasPerm('generic/canviewofflinedocuments')) && !IS_SEEMODE )
        {

            $groupQuery = 'n.usergroups IN(0,' . User::getGroupId() . ') AND ';
            $publish    = ' n.locked = 0 AND n.published>0 AND c.published=1 AND n.draft = 0 AND
					((n.publishoff>0 AND n.publishoff>=' . TIMESTAMP . ') OR n.publishoff=0) AND
					((n.publishon>0 AND n.publishon <= ' . TIMESTAMP . ') OR n.publishon = 0 AND n.created <= ' . TIMESTAMP . ') AND ';

            $groupQuery = '';

        }

        $sql = 'SELECT n.*, nt.*,
                            ct.title AS category, ct.title AS cattitle, ct.alias AS catalias, ct.suffix AS catsuffix,
                            c.access AS cat_access,
                            c.clickanalyse AS catclickanalyse,
                            c.cacheable AS catcacheable,
                            c.cachetime AS catcachetime,
                            c.cachegroups AS catcachegroups,
                            c.published AS catpublished,
                            u.username AS newsauthor,
                            u1.username AS modifyauthor,
                            (SELECT COUNT(*) FROM %tp%comments WHERE post_id = n.id AND modul=\'news\') AS comments
                        FROM %tp%news AS n
                        LEFT JOIN %tp%news_trans AS nt ON(nt.id=n.id )
                        LEFT JOIN %tp%news_categories AS c ON(c.id=n.cat_id)
                        LEFT JOIN %tp%news_categories_trans AS ct ON(ct.id=c.id)
                        LEFT JOIN %tp%users AS u ON (u.userid = n.created_by)
                        LEFT JOIN %tp%users AS u1 ON (u1.userid = n.modifed_by )
                        WHERE
                            ' . $groupQuery . '' . $publish . '
                            n.pageid = ? AND nt.id IN(0,' . implode( ',', $ids ) . ') AND ' . $transq1 . ' AND ' . $transq2 . '
                        GROUP BY n.id';

        return $this->db->query( $sql, PAGEID )->fetchAll();
    }

    /**
     * @param int $id
     * @return type
     */
    public function findItemByID($id = 0)
    {

        //$com = Comments::getCountQuery(array('prefix' => 'com', 'joinon' => 'n.id', 'source' => 'news'));
        $transq1 = $this->buildTransWhere( 'news', 'n.id', 'nt' );
        $transq2 = $this->buildTransWhere( 'news_categories', 'c.id', 'ct' );

        $publish = $groupQuery = '';
        if ( $this->getApplication()->isFrontend() && ( !User::isAdmin() || !User::hasPerm('generic/canviewofflinedocuments')) && !IS_SEEMODE )
        {
            $publish = ' n.locked = 0 AND n.published>0 AND c.published=1 AND
					((n.publishoff>0 AND n.publishoff>=' . TIMESTAMP . ') OR n.publishoff=0) AND
					((n.publishon>0 AND n.publishon <= ' . TIMESTAMP . ') OR n.publishon = 0 AND n.created <= ' . TIMESTAMP . ') AND ';


            //   $groupQuery = 'n.usergroups IN(0,' . User::getGroupId() . ') AND ';
        }

        $sql = 'SELECT n.*, nt.*,
                            ct.title AS category, ct.title AS cattitle, ct.alias AS catalias, ct.suffix AS catsuffix,
                            c.access AS cat_access,
                            c.clickanalyse AS catclickanalyse,
                            c.cacheable AS catcacheable,
                            c.cachetime AS catcachetime,
                            c.cachegroups AS catcachegroups,
                            c.published AS catpublished,
                            u.username AS newsauthor,
                            u1.username AS modifyauthor,
                            (SELECT COUNT(*) FROM %tp%comments WHERE post_id = n.id AND modul=\'news\') AS comments
                        FROM %tp%news AS n
                        LEFT JOIN %tp%news_trans AS nt ON(nt.id=n.id )
                        LEFT JOIN %tp%news_categories AS c ON(c.id=n.cat_id)
                        LEFT JOIN %tp%news_categories_trans AS ct ON(ct.id=c.id)
                        LEFT JOIN %tp%users AS u ON (u.userid = n.created_by)
                        LEFT JOIN %tp%users AS u1 ON (u1.userid = n.modifed_by )
                        WHERE
                            ' . $groupQuery . $publish . '
                             n.pageid = ? AND nt.id = ? AND ' . $transq1 . ' AND ' . $transq2 . '
                        GROUP BY n.id';

        return $this->db->query( $sql, PAGEID, $id )->fetch();
    }

    /**
     *
     * @param array $data
     * @return array|mixed
     * @internal param int $id
     */
    public function getData($data = array(), $forFeed = false)
    {

        $_sql     = array();
        $_sqlArgs = array();


        if ( !empty( $data[ 'catid' ] ) && $data[ 'catid' ] > 0 )
        {
            $_sql[ ]     = ' AND n.cat_id = ?';
            $_sqlArgs[ ] = $data[ 'catid' ];
        }


        if ( !empty( $data[ 'bettween' ] ) )
        {
            if ( is_int( $data[ 'bettween' ][ 'start' ] ) )
            {
                $_sql[ ]     = ' AND n.created >= ?';
                $_sqlArgs[ ] = $data[ 'bettween' ][ 'start' ];
            }

            if ( is_int( $data[ 'bettween' ][ 'end' ] ) )
            {
                $_sql[ ]     = ' AND n.created <= ?';
                $_sqlArgs[ ] = $data[ 'bettween' ][ 'end' ];
            }
        }


        if ( !(int)$data[ 'catid' ] && defined( 'DOCUMENT_NAME' ) && DOCUMENT_NAME != '' )
        {
            #$_sql[] = ' AND ct.alias = ?';
            #$_sqlArgs[] = DOCUMENT_NAME;
        }

        if ( !empty( $data[ 'q' ] ) )
        {
            $_sql[ ] = ' AND (nt.title LIKE ? OR nt.text LIKE ?)';

            $_sqlArgs[ ] = '%' . $data[ 'q' ] . '%';
            $_sqlArgs[ ] = '%' . $data[ 'q' ] . '%';
        }


        if ( !empty( $data[ 'tag' ] ) )
        {

            $this->load( 'Tags' );
            $this->Tags->getHash( 'news_trans' );
            $_id     = $this->Tags->getTagIdByTag( $data[ 'tag' ] );
            $_sql[ ] = ' AND FIND_IN_SET(' . ( $_id > 0 ? $_id : '-1' ) . ', CONCAT(nt.tags, \',\') )';
            #$_sql[] = ' AND ' . ($_id > 0 ? $_id : '-1') . ' IN(nt.tags)';
            $this->unload( 'Tags' );
        }
        $_sql = implode( ' ', $_sql );


        $transq1 = $this->buildTransWhere( 'news', 'n.id', 'nt' );
        $transq2 = $this->buildTransWhere( 'news_categories', 'c.id', 'ct' );


        $publishQuery = '';
        if ( !User::isAdmin() && !IS_SEEMODE )
        {
            $publishQuery = '
                AND n.published>0
                AND n.locked = 0
                AND n.draft = 0
                AND ((n.publishon>0 AND n.publishon <= ?) OR n.publishon = 0 AND n.created <= ?)
                AND ((n.publishoff>0 AND n.publishoff>=?) OR n.publishoff=0)
                AND c.published=1
                AND c.locked = 0
                ';

            #	if ( $forFeed )
            #	{
            $publishQuery .= ' AND n.usergroups IN(0,' . User::getGroupId() . ') ';
            #	}


            array_unshift( $_sqlArgs, TIMESTAMP );
            array_unshift( $_sqlArgs, TIMESTAMP );
            array_unshift( $_sqlArgs, TIMESTAMP );
            array_unshift( $_sqlArgs, PAGEID );
        }
        else
        {
            array_unshift( $_sqlArgs, PAGEID );
        }


        $sql = "SELECT COUNT(*) AS total
                FROM %tp%news AS n
                LEFT JOIN %tp%news_trans AS nt ON (nt.id=n.id)
                LEFT JOIN %tp%news_categories AS c ON(c.id=n.cat_id)
                LEFT JOIN %tp%news_categories_trans AS ct ON(ct.id = c.id)
                LEFT JOIN %tp%users AS u ON (u.userid = n.created_by)
                LEFT JOIN %tp%users AS u1 ON (u1.userid = n.modifed_by)
                WHERE n.pageid = ?                
                " . $publishQuery . "
                AND " . $transq1 . ' 
                AND ' . $transq2 . $_sql;

        $r = $this->db->query( $sql, $_sqlArgs )->fetch();

        $a = $data[ 'perpage' ] * ( (int)$this->input( 'page' ) > 0 ? (int)$this->input( 'page' ) - 1 : 0 );

        switch ( strtolower( $data[ 'order' ] ) )
        {
            case 'title':
                $order = " ORDER BY nt.title ";
                if ( empty( $sort ) )
                {
                    $sort = " ASC";
                }
                break;

            case 'rating':
                $order = " ORDER BY n.rating ";
                break;


            case 'hits':
                $order = " ORDER BY n.hits ";
                break;

            case 'category':
                $order = " ORDER BY ct.title ";

                if ( empty( $sort ) )
                {
                    $sort = " ASC";
                }

                break;

            case 'date':
            default:
                $order = " ORDER BY n.created ";
                break;
        }

        switch ( strtolower( $data[ 'sort' ] ) )
        {
            case 'desc':
            default:
                $sort = 'DESC';
                break;
            case 'asc':
                $sort = 'ASC';
                break;
        }

        $sql = "SELECT n.*, nt.*, c.cancomment,
                ct.alias AS catalias,
                ct.suffix AS catsuffix,
                ct.title AS cat_title,
                u.username AS author,
                u1.username AS modifyauthor,
                (SELECT COUNT(cc.id) FROM %tp%comments AS cc WHERE cc.post_id = n.id AND cc.modul='news') AS comments
                FROM %tp%news AS n
                LEFT JOIN %tp%news_trans AS nt ON (nt.id=n.id)
                LEFT JOIN %tp%news_categories AS c ON(c.id=n.cat_id)
                LEFT JOIN %tp%news_categories_trans AS ct ON(ct.id = c.id)                
                LEFT JOIN %tp%users AS u ON (u.userid = n.created_by)
                LEFT JOIN %tp%users AS u1 ON (u1.userid = n.modifed_by)

                WHERE n.pageid = ?                
                " . $publishQuery . "
                AND " . $transq1 . ' 
                AND ' . $transq2 . $_sql . $order . $sort . ' LIMIT ' . $a . ', ' . $data[ 'perpage' ];

        return array(
            'result' => $this->db->query( $sql, $_sqlArgs )->fetchAll(),
            'total'  => $r[ 'total' ]
        );
    }

    /**
     *
     * @param integer $cat_id
     * @return array array('result', 'total')
     */
    public function getGridQuery($cat_id = 0)
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
            case 'date':
                $order = " ORDER BY n.created";
                break;

            case 'modifed':
                $order = " ORDER BY n.modifed";
                break;

            case 'hits':
                $order = " ORDER BY n.hits";
                break;

            case 'published':
                $order = " ORDER BY nt.draft " . $sort . ", n.draft " . $sort . ", n.published";
                break;

            case 'cat':

                $order = " ORDER BY ct.title";

                break;

            case 'comments':
                $order = " ORDER BY comments";
                break;

            case 'id':
                $order = " ORDER BY n.id ";
                break;

            case 'title':
            default:
                $order = " ORDER BY nt.title";

                break;
        }

        $where = array();

        $where[ ] = 'n.pageid = ' . PAGEID;

        // ====================================================
        // Status der News
        // ====================================================
        switch ( HTTP::input( 'state' ) )
        {

            case 'online':
                $where[ ] = ' n.published = ' . PUBLISH_MODE;
                break;

            case 'offline':
                $where[ ] = ' n.published = ' . UNPUBLISH_MODE;
                break;

            case 'archived':
                $where[ ] = ' n.published = ' . ARCHIV_MODE;
                break;

            case 'draft':
                $where[ ] = ' nt.draft = 1 OR n.draft=1 OR n.published = ' . DRAFT_MODE;
                break;

            case 'online_offline':
                $where[ ] = ' n.published >= ' . UNPUBLISH_MODE;
                break;

            case 'online_offline':
                $where[ ] = ' n.published >= ' . UNPUBLISH_MODE;
                break;

            default:

                break;
        }


        // mark untranslated news
        if ( HTTP::input( 'untrans' ) )
        {
            $where[ ] = ' nt.iscorelang = 1 AND nt.lang != ' . $this->db->quote( CONTENT_TRANS );
        }

        $search = '';
        $search = HTTP::input( 'q' );
        $search = trim( (string)strtolower( $search ) );
        $all    = null;

        if ( $cat_id > 0 )
        {
            $where[ ] = "n.cat_id={$cat_id}";
        }

        $_s = '';
        if ( $search != '' )
        {
            $search = str_replace( "%", "\%", $search );
            $search = str_replace( "*", "%", $search );

            $_s = " ( nt.title LIKE " . $this->db->quote( "%{$search}%" ) . " OR nt.text LIKE " . $this->db->quote( "%{$search}%" ) . ") AND ";
        }

        $transq1 = $this->buildTransWhere( 'news', 'n.id', 'nt' );
        $transq2 = $this->buildTransWhere( 'news_categories', 'c.id', 'ct' );

        // get the total number of records
        $sql = "SELECT COUNT(n.id) AS total
                FROM %tp%news AS n
                LEFT JOIN %tp%news_trans AS nt ON (nt.id=n.id)
                LEFT JOIN %tp%news_categories AS c ON(c.id=n.cat_id)
                LEFT JOIN %tp%news_categories_trans AS ct ON(ct.id = c.id)
                WHERE " . ( $_s ? $_s : '' ) . $transq1 . ' AND ' . $transq2 . " " . ( count( $where ) ? ' AND ' . implode( ' AND ', $where ) : "" );
        $r   = $this->db->query( $sql )->fetch();

        $total = $r[ 'total' ];
        $limit = $this->getPerpage();
        $page  = $this->getCurrentPage();


        $query = "SELECT n.*, nt.lang, nt.title, ct.title AS cat_title,
                    u1.username AS created_user,
                    u2.username AS modifed_user,
                    (SELECT COUNT(*) FROM %tp%comments WHERE post_id = n.id AND modul='news') AS comments
                    FROM %tp%news AS n
                    LEFT JOIN %tp%news_trans AS nt ON (nt.id=n.id)
                    /* LEFT OUTER JOIN %tp%comments AS com ON( com.post_id = n.id AND com.modul='news' ) */
                    LEFT JOIN %tp%news_categories AS c ON(c.id=n.cat_id)
                    LEFT JOIN %tp%news_categories_trans AS ct ON(ct.id = c.id)
                    LEFT OUTER JOIN %tp%users AS u1 ON(u1.userid=n.created_by)
                    LEFT OUTER JOIN %tp%users AS u2 ON(u2.userid=n.modifed_by)
                WHERE " . ( $_s ? $_s : '' ) . $transq1 . ' AND ' . $transq2 . ( count( $where ) ? ' AND ' . implode( ' AND ', $where ) : "" ) . " GROUP BY n.id " . $order . ' ' . $sort . " LIMIT " . ( $limit * ( $page - 1 ) ) . "," . $limit;


        return array(
            'result' => $this->db->query( $query )->fetchAll(),
            'total'  => $total
        );
    }

    /**
     * @param bool $returnSqlResult
     * @return array
     */
    public function getCats($returnSqlResult = false)
    {

        $transq = $this->buildTransWhere( 'news_categories', 'c.id', 'ct' );
        $locked = '';
        if ( $this->getApplication()->getMode() === Application::FRONTEND_MODE && !User::isAdmin() && !IS_SEEMODE )
        {
            $locked = ' AND c.locked = 0 AND c.published = 1';
        }
        $sql = "SELECT c.id, c.parentid, ct.title AS name
                FROM %tp%news_categories AS c
                LEFT JOIN %tp%news_categories_trans AS ct ON(ct.id = c.id)
                WHERE " . $transq . $locked . " AND c.pageid = ?
                ORDER BY c.ordering ASC";


        if ( $returnSqlResult )
        {
            return $this->db->query( $sql, PAGEID )->fetchAll();
        }

        $result = $this->db->query( $sql, PAGEID )->fetchAll();

        $_options      = array();
        $_options[ 0 ] = '----';
        foreach ( $result as $r )
        {
            $_options[ $r[ 'id' ] ] = $r[ 'name' ];
        }

        return $_options;
    }

    /**
     * @param int $id
     * @return array
     */
    public function getCatByID($id = 0)
    {

        $transq = $this->buildTransWhere( 'news_categories', 'c.id', 'ct' );
        $locked = '';
        if ( $this->getApplication()->isFrontend() && !User::isAdmin() && !IS_SEEMODE )
        {
            $locked = ' AND c.locked = 0 AND c.published = 1';
        }

        return $this->db->query( "SELECT c.*, ct.*
                FROM %tp%news_categories AS c
                LEFT JOIN %tp%news_categories_trans AS ct ON(ct.id = c.id)
                WHERE " . $transq . $locked . " AND c.id = ? AND c.pageid = ?", $id, PAGEID )->fetch();
    }

    /**
     * @param string $id
     * @return array
     */
    public function getCatByAlias($alias)
    {

        $transq = $this->buildTransWhere( 'news_categories', 'c.id', 'ct' );
        $locked = '';
        if ( $this->getApplication()->isFrontend() && !User::isAdmin() && !IS_SEEMODE )
        {
            $locked = ' AND c.locked = 0 AND c.published = 1';
        }

        return $this->db->query( "SELECT c.*, ct.*
                FROM %tp%news_categories AS c
                LEFT JOIN %tp%news_categories_trans AS ct ON(ct.id = c.id)
                WHERE " . $transq . $locked . " AND ct.alias = ? AND c.pageid = ?
                ORDER BY c.ordering ASC", $alias, PAGEID )->fetch();
    }

    /**
     *
     * @param integer $id
     * @param bool $useCat
     * @return bool
     */
    public function hasTranslation($id = 0, $useCat = false)
    {

        $table = ( $useCat ? 'news_categories' : 'news' );
        $trans = $this->db->query( 'SELECT id FROM %tp%' . $table . '_trans WHERE id = ? AND lang = ?', $id, CONTENT_TRANS )->fetch();

        if ( $trans[ 'id' ] )
        {
            return true;
        }

        return false;
    }

    /**
     *
     * @param      $idKey
     * @param      $multiIdKey
     * @param bool $mode default is false
     * @internal param int $publish
     * @internal param $int /array $ids
     */
    public function publish($idKey, $multiIdKey, $mode = false)
    {

        $data = $this->getMultipleIds( $idKey, $multiIdKey );

        if ( !$data[ 'id' ] && !$data[ 'isMulti' ] )
        {
            Error::raise( "Invalid ID" );
        }

        if ( !$mode )
        {

            $this->load( 'SideCache' );

            $ps = new PingService();


            if ( $data[ 'isMulti' ] )
            {

                $result = $this->db->query( "SELECT nt.title, nt.alias, n.id FROM %tp%news AS n LEFT JOIN %tp%news_trans AS nt ON(nt.id = n.id)
                                            WHERE n.id IN(0," . $data[ 'id' ] . ") GROUP BY n.id" )->fetchAll();

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

                    $this->SideCache->cleanSideCache( 'news', 'show', $r[ 'alias' ], $r[ 'id' ] );

                    $this->db->query( 'UPDATE %tp%news SET published=? WHERE id=?', '' . $state, $r[ 'id' ] );


                    // send Ping
                    if ( $state === PUBLISH_MODE )
                    {
                        // send pings
                        $ps->setData('news/item/'. $r['alias'] . ($r['suffix'] ? '.'.$r['suffix'] : '.'.Settings::get( 'mod_rewrite_suffix', 'html' )) , $r['title'])->genericPing();
                    }

                    Library::log( "Change News publishing \"{$r['title']}\" to status {$state} (ID:{$r['id']})." );
                }

                Library::sendJson( true, '' . $state );
                exit;
            }
            else
            {
                if ( HTTP::input( 's' ) )
                {
                    $state = UNPUBLISH_MODE;

                    if ( HTTP::input( 's' ) == 'publish' )
                    {
                        $state = PUBLISH_MODE;
                    }

                    if ( HTTP::input( 's' ) == 'unpublish' )
                    {
                        $state = UNPUBLISH_MODE;
                    }

                    if ( HTTP::input( 's' ) == 'archive' )
                    {
                        $state = ARCHIV_MODE;
                    }

                    if ( HTTP::input( 's' ) == 'unarchive' )
                    {
                        $state = UNPUBLISH_MODE;
                    }

                    $r = $this->db->query( "
                        SELECT n.published, nt.title, nt.alias FROM %tp%news AS n
                        LEFT JOIN %tp%news_trans AS nt ON(nt.id = n.id) 
                        WHERE n.id=?", $data[ 'id' ] )->fetch();
                }
                else
                {
                    $r     = $this->db->query( "SELECT n.published, nt.title, nt.alias FROM %tp%news AS n
                        LEFT JOIN %tp%news_trans AS nt ON(nt.id = n.id) 
                        WHERE n.id=?", $data[ 'id' ] )->fetch();

                    $state = ( $r[ 'published' ] > UNPUBLISH_MODE ? UNPUBLISH_MODE : PUBLISH_MODE );
                }

                $this->db->query( 'UPDATE %tp%news SET published=? WHERE id=?', $state, $data[ 'id' ] );

                $this->SideCache->cleanSideCache( 'news', 'show', $r[ 'alias' ], $data[ 'id' ] );

                if ( $state === PUBLISH_MODE)
                {
                    // send pings
                    $ps->setData('news/item/'. $r['alias'] . ($r['suffix'] ? '.'.$r['suffix'] : '.'.Settings::get( 'mod_rewrite_suffix', 'html' )) , $r['title'])->genericPing();
                }


                Library::log( "Change News publishing to status {$state} (ID:{$id})." );
                Library::sendJson( true, '' . $state );
                exit;
            }
        }
        else
        {

        }
    }

    /**
     * Delete a News Item by ID
     * deleting news, news trans, alias registry and searchindex of this newsitem
     *
     * @param integer $id
     * @param bool $trash
     * @return bool
     */
    public function deleteNews($id, $trash = true)
    {

        //    $indexer = new Search();


        $this->load( 'AliasRegistry' );

        if ( $trash )
        {

            $this->load( 'Trash' );
            $this->Trash->setTrashTable( '%tp%news' );
            $this->Trash->setTrashTableLabel( 'News Item' );
            $rs = $this->findItemByID( $id );
        }

        $r = $this->db->query( 'SELECT * FROM %tp%news WHERE id = ?', $id )->fetch();

        $this->db->query( 'DELETE FROM %tp%news WHERE id = ?', $id );

        // Move to Trash
        $trashData                 = array();
        $trashData[ 'data' ]       = $r;
        $trashData[ 'label' ]      = $rs[ 'title' ];
        $trashData[ 'trans_data' ] = $this->db->query( 'SELECT * FROM %tp%news_trans WHERE id = ?', $id )->fetchAll();


        // remove cached News
        $this->load( 'SideCache' );
        foreach ( $trashData[ 'trans_data' ] as $rd )
        {
            $this->SideCache->cleanSideCache( 'news', 'show', $rd[ 'alias' ], $id );
        }

        if ( $trash )
        {
            // move the News to trash
            $this->Trash->addTrashData( $trashData );
            $this->Trash->moveToTrash();
        }

        // unregister alias
        $this->AliasRegistry->removeAlias( $trashData[ 'trans_data' ][ 'alias' ], 'news', 'show' );

        // remove Search Index
        //  $indexer->deleteIndex('news', CONTENT_TRANS, 'news/item/', $id);
        // Remove Cache
        Cache::delete( 'newsText-' . $id, 'data/news' );

        return true;
    }

    /**
     * @param $id
     * @return bool
     */
    public function deleteNewsCat($id)
    {

        $r         = $this->db->query( 'SELECT * FROM %tp%news_categories WHERE id = ?', $id )->fetch();
        $trashData = $this->db->query( 'SELECT alias FROM %tp%news_categories_trans WHERE id = ? LIMIT 1', $id )->fetch();


        $items = $this->db->query( 'SELECT id FROM %tp%news WHERE cat_id = ?', $id )->fetchAll();
        foreach ( $items as $r )
        {
            $this->deleteNews( $r[ 'id' ], false );
        }


        // unregister alias
        $this->load( 'AliasRegistry' );
        $this->AliasRegistry->removeAlias( $trashData[ 'alias' ], 'news', 'index' );

        $this->db->query( 'DELETE FROM %tp%news_categories WHERE id = ?', $id );
        $this->db->query( 'DELETE FROM %tp%news_categories_trans WHERE id = ?', $id );


        return true;
    }


    /**
     * will rollback the temporary translated content
     *
     * @param int $id
     * @param bool $useCat
     * @return Database_Adapter_Pdo_RecordSet|void
     */
    public function rollbackTranslation($id, $useCat = false)
    {

        $table = ( $useCat ? 'news_categories' : 'news' );
        return $this->db->query( 'DELETE FROM %tp%' . $table . '_trans WHERE `rollback` = 1 AND id = ? AND lang = ?', $id, CONTENT_TRANS );
    }

    /**
     * Copy the original translation to other translation
     *
     * @param int $id
     * @param bool $useCat
     * @return bool
     */
    public function copyOriginalTranslation($id, $useCat = false)
    {

        $table = ( $useCat ? 'news_categories' : 'news' );


        $r = $this->db->query( 'SELECT lang FROM %tp%' . $table . '_trans WHERE id = ? AND iscorelang = 1', $id )->fetch();
        if ( CONTENT_TRANS == $r[ 'lang' ] )
        {
            return false;
        }

        $trans                 = $this->db->query( 'SELECT t.* FROM %tp%' . $table . '_trans AS t WHERE t.id = ? AND t.lang = ?', $id, $r[ 'lang' ] )->fetch();
        $trans[ 'lang' ]       = CONTENT_TRANS;
        $trans[ 'rollback' ]   = 1;
        $trans[ 'iscorelang' ] = 0;

        $f      = array();
        $fields = array();
        $values = array();
        foreach ( $trans as $key => $value )
        {
            $fields[ ] = $key;
            $f[ ]      = '?';
            $values[ ] = $value;
        }

        $this->db->query( 'INSERT INTO %tp%' . $table . '_trans (' . implode( ',', $fields ) . ') VALUES(' . implode( ',', $f ) . ')', $values );
        return true;
    }

    /**
     *
     * @param integer $id
     * @param array $data
     * @return int
     */
    public function saveNewsTranslation($id = 0, $data = array())
    {

        $this->setTable( 'news' );

        $access = ( is_array( $data[ 'access' ] ) ? $data[ 'access' ] : array(
            0
        ) );


        $data[ 'usergroups' ] = implode( ',', $access );
        $data[ 'text' ]       = $data[ 'content' ];

        $coredata = array(
            'cat_id'        => (int)$data[ 'cat_id' ],
            'pageid'        => PAGEID,
            'usergroups'    => (string)$data[ 'usergroups' ],
            'teaserimage'   => (string)$data[ 'teaserimage' ],
            'links_extern'  => (string)$data[ 'links_extern' ],
            'isfeed'        => (int)$data[ 'isfeed' ],
            'feed_link'     => (string)$data[ 'feed_link' ],
            'can_comment'   => (int)$data[ 'can_comment' ],
            'offline'       => (int)$data[ 'offline' ],
            'hits'          => 0,
            'created_by'    => (int)User::getUserId(),
            'created'       => TIMESTAMP,
            'modifed_by'    => (int)User::getUserId(),
            'modifed'       => TIMESTAMP,
            'rollback'      => 0,
            'inlinegallery' => ''
        );

        if ( !is_array( HTTP::input( 'documentmeta' ) ) )
        {
            $coredata[ 'published' ] = $data[ 'published' ];
        }

        $imgs = Session::get( 'news-content-images', false );
        if ( is_array( $imgs ) )
        {
            //     $coredata[ 'inlinegallery' ] = implode( ',', $imgs );
        }

        if ( isset( $data[ 'reorder-contentimages' ] ) )
        {
            $coredata[ 'inlinegallery' ] = $data[ 'reorder-contentimages' ];
        }


        $transData = array();

        if ( !$id )
        {
            $coredata[ 'modifed' ]    = 0;
            $coredata[ 'modifed_by' ] = 0;

            $transData[ 'controller' ] = 'news';
            $transData[ 'action' ]     = 'show';
            $transData[ 'data' ]       = $data;

            $transData[ 'alias' ]  = $data[ 'alias' ];
            $transData[ 'suffix' ] = $data[ 'suffix' ];

            $transData[ 'id' ] = $this->save( $id, $coredata, $transData );
        }
        else
        {
            unset( $coredata[ 'created' ], $coredata[ 'created_by' ], $coredata[ 'hits' ] );

            $transData[ 'controller' ] = 'news';
            $transData[ 'action' ]     = 'show';
            $transData[ 'data' ]       = $data;

            $transData[ 'alias' ]  = $data[ 'alias' ];
            $transData[ 'suffix' ] = $data[ 'suffix' ];


            //remove cache by old alias
            $r = $this->db->query( "SELECT nt.alias FROM %tp%news AS n
                        LEFT JOIN %tp%news_trans AS nt ON(nt.id = n.id) 
                        WHERE n.id=?", $id )->fetch();

            $this->load( 'SideCache' );
            $this->SideCache->cleanSideCache( 'news', 'show', $r[ 'alias' ], $id );


            $transData[ 'id' ] = $this->save( $id, $coredata, $transData );
        }

        //$this->saveContentDraft( $transData[ 'id' ], $data[ 'title' ], trans( 'Nachrichten' ) );
        $this->updateGalleryImagesIds( $transData[ 'id' ], $coredata[ 'inlinegallery' ] );

        return $transData[ 'id' ];
    }

    private function updateGalleryImagesIds($contentid, $imgs = '')
    {

        $imgs = explode( ',', $imgs );
        if ( is_array( $imgs ) && count( $imgs ) > 0 )
        {
            foreach ( $imgs as $i => $id )
            {
                $this->db->query( 'UPDATE %tp%contentimages SET contentid = ?, ordering = ? WHERE imageid = ?', $contentid, $i, $id );
            }
        }
        Session::delete( 'news-content-images' );
    }

    /**
     *
     * @param int $id
     * @param array $data
     * @return int
     */
    public function saveCatTranslation($id = 0, $data = array())
    {

        $access           = ( is_array( $data[ 'access' ] ) ? $data[ 'access' ] : array(
            0
        ) );
        $data[ 'access' ] = implode( ',', $access );

        /**
         * create Password
         */
        if ( $data[ 'password' ] != "" )
        {
            $this->load( 'Crypt' );
            $data[ 'password' ] = (string)$this->Crypt->encrypt( $data[ 'password' ] );
        }

        $coredata = array(
            //'tags' => (string) $data['tags'],
            'password'    => (string)$data[ 'password' ],
            'parentid'    => (int)$data[ 'parentid' ],
            'pageid'      => PAGEID,
            'newscounter' => 0,
            'access'      => (string)$data[ 'access' ],
            'moderators'  => (string)$data[ 'moderators' ],
            'cancomment'  => (int)$data[ 'cancomment' ],
            'teaserimage' => (string)$data[ 'teaserimage' ],
            'language'    => (string)$data[ 'language' ],
            'rollback'    => 0
        );

        if ( !is_array( HTTP::input( 'documentmeta' ) ) )
        {
            $coredata[ 'published' ] = $data[ 'published' ];
        }

        $data[ 'description' ] = (string)$data[ 'description' ];


        $transData[ 'data' ] = $data;
        $transData[ 'tags' ] = (string)$data[ 'tags' ];

        if ( !$id )
        {
            $transData[ 'iscorelang' ] = 1;
            $transData[ 'isnew' ]      = true;

            $transData[ 'controller' ] = 'news';
            $transData[ 'action' ]     = 'index';
            $transData[ 'alias' ]      = (string)$data[ 'alias' ];
            $transData[ 'suffix' ]     = (string)$data[ 'suffix' ];


            $transData[ 'id' ] = $this->save( $id, $coredata, $transData );
        }
        else
        {
            $transData[ 'controller' ] = 'news';
            $transData[ 'action' ]     = 'index';
            $transData[ 'alias' ]      = (string)$data[ 'alias' ];
            $transData[ 'suffix' ]     = (string)$data[ 'suffix' ];

            $transData[ 'id' ] = $this->save( $id, $coredata, $transData );
        }


        return $transData[ 'id' ];
    }

    /**
     *
     * @param integer $id
     * @param string $action
     * @param string $none

    public function unlock( $id, $action, $none = '' )
     * {
     *
     * if ( $action === 'show' )
     * {
     * $this->db->query( 'UPDATE %tp%news SET locked = 0 WHERE id = ?', $id );
     * }
     * elseif ( $action === 'index' )
     * {
     * $this->db->query( 'UPDATE %tp%news_categories SET locked = 0 WHERE id = ?', $id );
     * }
     * }
     */

    /**
     *
     * @param integer $id
     */
    public function updateHits($id)
    {

        $this->db->query( 'UPDATE %tp%news SET hits = hits+1 WHERE id = ?', $id );
    }

    /**
     *
     * @return string
     */
    public function getRelnews()
    {

        $basedonId = $this->getParam( 'id' );
        $template  = $this->getParam( 'template', 'rel_items' );
        $limit     = $this->getParam( 'limit', 5 );
        $images    = $this->getParam( 'images', false );
        $catId     = 0;

        $baselang = $basetitle = null;


        $where = '';
        if ( !User::isAdmin() && !IS_SEEMODE )
        {
            /*
            $now   = time();
            $where = ' AND n.locked = 0 AND n.published>0 AND c.published=1 AND n.draft = 0 AND
                    (n.publishoff>=' . $now . ' OR n.publishoff=0) AND
                    ((n.publishon>0 AND n.publishon <= ' . $now . ') OR n.created <= ' . $now . ') AND n.usergroups IN(0,' . User::getGroupId() . ') ';
    */

            $where = '
			AND n.locked = 0
			AND n.published>0
			AND n.draft = 0
			AND ((n.publishon>0 AND n.publishon <= ' . TIMESTAMP . ') OR n.publishon = 0 AND n.created <= ' . TIMESTAMP . ')
			AND ((n.publishoff>0 AND n.publishoff>=' . TIMESTAMP . ') OR n.publishoff=0)
			AND n.usergroups IN(0,' . User::getGroupId() . ')
			AND c.published=1 ';


        }


        if ( !$basedonId )
        {
            return;
        }
        else
        {
            $this->load( 'Document' );
            $basetitle = $this->Document->get( 'text' );
            $baselang  = $this->Document->get( 'lang' );
            $where .= ' AND n.id != ' . (int)$basedonId;
        }

        if ( !$catId )
        {
            $where .= ' AND n2.id = ' . (int)$basedonId;
        }

        $transq  = $this->buildTransWhere( 'news_categories', 'c.id', 'ct' );
        $transq1 = $this->buildTransWhere( 'news', 'n.id', 'nt' );
        $sql     = 'SELECT DISTINCT n.id, n.cat_id, n.created, n.created_by, nt.alias, nt.suffix, nt.title, nt.text,
                    u.username AS author,
                    u1.username AS modifyauthor
                    FROM %tp%news AS n
                    LEFT JOIN %tp%news AS n2 ON(n2.cat_id=n.cat_id)
                    LEFT JOIN %tp%news_trans AS nt ON(nt.id=n.id)
                    LEFT JOIN %tp%news_categories AS c ON(c.id=n.cat_id)
                    LEFT JOIN %tp%news_categories_trans AS ct ON(ct.id = c.id)                    
                    LEFT JOIN %tp%users AS u ON (u.userid = n.created_by)
                    LEFT JOIN %tp%users AS u1 ON (u1.userid = n.modifed_by)
                    WHERE n.pageid = ? AND ' . $transq1 . ' AND ' . $transq . $where . '
                    GROUP BY n.id
                    ORDER BY RAND()
                    LIMIT ' . $limit;
        $rs      = $this->db->query( $sql, PAGEID )->fetchAll();

        $data = array();
        foreach ( $rs as &$r )
        {
            $r[ 'rewrite' ] = ( $r[ 'alias' ] ? $r[ 'alias' ] . '.' . ( $r[ 'suffix' ] ? $r[ 'suffix' ] : Settings::get( 'mod_rewrite_suffix', 'html' ) ) : $r[ 'id' ] );
            $r[ 'url' ]     = 'news/item/' . $r[ 'rewrite' ];


            $r[ 'text' ]  = Strings::cleanString( Strings::fixLatin( $r[ 'text' ] ) );
            $r[ 'title' ] = Strings::cleanString( Strings::fixLatin( $r[ 'title' ] ) );


            $r[ 'text' ] = preg_replace( '#src=(["\']).*(pages/.+)\1#i', 'src=$1$2$1', $r[ 'text' ] );


            if ( ( $img = Content::extractFirstImage( $r[ 'text' ] ) ) )
            {
                $r[ 'image' ] = $img[ 'attributes' ];
                $r[ 'text' ]  = str_replace( $img[ 'full_tag' ], '', $r[ 'text' ] );
            }


            if ( $r[ 'image' ][ 'src' ] )
            {
                $r[ 'image' ][ 'src' ] = str_replace( Settings::get( 'portalurl' ) . '/', '', $r[ 'image' ][ 'src' ] );
                $r[ 'image' ][ 'src' ] = preg_replace( '#/?public/#', '', $r[ 'image' ][ 'src' ] );
                if ( $r[ 'image' ][ 'src' ] && !is_file( PUBLIC_PATH . $r[ 'image' ][ 'src' ] ) )
                {
                    unset( $r[ 'image' ] );
                }
            }
            else
            {
                unset( $r[ 'image' ] );
            }

            $r[ 'text' ] = $this->prepareNewsContent( $r[ 'text' ], $r[ 'created' ], 300 );
        }

        $data[ 'related_items' ] = $rs;

        unset( $rs );

        $this->load('Template');
        $tpl = new Template();
        $tpl->isProvider = true;
        $data = array_merge($this->Template->getTemplateData(), $data);

        return $tpl->process( 'cms/' . $template, $data, null );
    }

    /**
     *
     * @return string
     */
    public function getRecentnews()
    {

        $this->setParam( 'order', 'created' );

        if ( !$this->getParam( 'template', null ) )
        {
            $this->setParam( 'template', 'recentnews' );
        }

        return $this->getTopnews();
    }

    /**
     *
     * @param string $order
     * @param int $limit
     * @return string
     */
    public function getTopratednews($order = 'rating', $limit = 5)
    {

        $this->setParam( 'order', 'rating' );
        if ( !$this->getParam( 'template', null ) )
        {
            $this->setParam( 'template', 'topratednews' );
        }

        return $this->getTopnews( 'rating', $limit );
    }

    /**
     *
     * @param string $order
     * @param int $limit
     * @return string
     */
    public function getCommentsnews($order = 'rating', $limit = 5)
    {

        $this->setParam( 'order', 'comments' );
        if ( !$this->getParam( 'template', null ) )
        {
            $this->setParam( 'template', 'lastcommentsnews' );
        }

        return $this->getTopnews( 'rating', $limit );
    }

    /**
     *
     * @return string
     */
    public function getTopnews()
    {

        $images    = $this->getParam( 'images', false );
        $catid     = $this->getParam( 'catid', null );
        $order     = $this->getParam( 'order' );
        $limit     = $this->getParam( 'limit', 5 );
        $template  = $this->getParam( 'template', null );
        $skip      = $this->getParam( 'skip', 0 );
        $showlabel = $this->getParam( 'showlabel', null );


        $data = array();


        if ( $showlabel === 'false' )
        {
            $showlabel = false;
        }

        if ( $showlabel === 'true' )
        {
            $showlabel = true;
        }

        $data[ 'showlabel' ] = $showlabel;


        if ( $template === null )
        {
            $template = 'topnews';
        }

        /*
    $where = " c.published=1 AND n.published > 0 AND ((n.publishoff>=? OR n.publishoff=0) AND (n.publishon=0 OR n.publishon <= ?))
                AND n.usergroups IN(0, ?) AND n.pageid = ?";
    */

        $where = '
				n.locked = 0 AND
				n.published>0 AND
				n.draft = 0 AND nt.draft = 0 AND
				((n.publishon>0 AND n.publishon <= ' . TIMESTAMP . ') OR n.publishon = 0 AND n.created <= ' . TIMESTAMP . ') AND
				((n.publishoff>0 AND n.publishoff>=' . TIMESTAMP . ') OR n.publishoff=0) AND
				 n.usergroups IN(0,?) AND n.pageid = ? AND c.published=1 ';

        if ( $catid !== null && (int)$catid >= 1 )
        {
            $where .= ' AND n.cat_id = ' . $this->db->escape( $catid );
        }

        if ( $images )
        {
            $where .= ' AND nt.text LIKE \'%<img %\'';
        }


        switch ( $order )
        {

            case 'rating':
                $order = 'n.rating DESC';
                break;

            case 'created':
                $order = 'n.created DESC';
                break;


            case 'hits':
                $order = 'n.hits DESC';
                break;

            case 'comments':
                $order = 'comments DESC';
                break;

            case 'randum':
                $order = 'RAND()';
                break;

            default:
                $order = 'n.hits DESC';
                break;
        }


        //$returns = Cache::get('getTopNews-' . CONTENT_TRANS . '-' . md5($order . $limit . PAGEID . User::getGroupId()));
        //if (!$returns)
        //{
        $transq1 = $this->buildTransWhere( 'news', 'n.id', 'nt' );
        $transq2 = $this->buildTransWhere( 'news_categories', 'c.id', 'ct' );

        $sql = "SELECT n.*, nt.title, nt.text, nt.alias, nt.suffix, ct.title AS category,
                (SELECT COUNT(cc.id) FROM %tp%comments AS cc WHERE cc.post_id = n.id AND cc.modul='news') AS comments,
                u.username AS author,
                u1.username AS modifyauthor
                FROM %tp%news AS n
                LEFT JOIN %tp%news_trans AS nt ON(nt.id=n.id)
                LEFT JOIN %tp%news_categories AS c ON(c.id=n.cat_id)
                LEFT JOIN %tp%news_categories_trans AS ct ON(ct.id=c.id)
                LEFT JOIN %tp%users AS u ON (u.userid = n.created_by)
                LEFT JOIN %tp%users AS u1 ON (u1.userid = n.modifed_by)
		    WHERE
            " . $where . ' AND ' . $transq1 . ' AND ' . $transq2 . "
            GROUP BY n.id
	 		ORDER BY {$order} LIMIT {$limit}";

        $returns = $this->db->query( $sql, User::getGroupId(), PAGEID )->fetchAll();
        //Cache::write('getTopNews-' . CONTENT_TRANS . '-' . md5($order . $limit . PAGEID . User::getGroupId()), $returns);
        //}


        foreach ( $returns as $idx => &$r )
        {
            if ( $skip > 0 && $idx < $skip )
            {
                unset( $returns[ $idx ] );
                continue;
            }


            //$r[ 'rewrite' ] = ($r[ 'alias' ] ? $r[ 'alias' ] . '.' . ( $r[ 'suffix' ] ? $r[ 'suffix' ]  : $r[ 'id' ]);
            $r[ 'text' ]  = Strings::fixLatin( $r[ 'text' ] );
            $r[ 'title' ] = Strings::fixLatin( $r[ 'title' ] );

            $r[ 'rewrite' ] = Url::makeRw( $r[ 'alias' ], $r[ 'suffix' ], $r[ 'title' ] );

            $r[ 'url' ]  = 'news/item/' . $r[ 'rewrite' ];
            $r[ 'text' ] = preg_replace( '#src=(["\']).*(pages/.+)\1#i', 'src=$1$2$1', $r[ 'text' ] );


            /**
             * get the first Image of the content and make it as teaser image
             */
            if ( ( $img = Content::extractFirstImage( $r[ 'text' ] ) ) )
            {

                $r[ 'image' ] = $img[ 'attributes' ];

                if ( $r[ 'image' ][ 'src' ] )
                {
                    $r[ 'image' ][ 'src' ] = str_replace( Settings::get( 'portalurl' ) . '/', '', $r[ 'image' ][ 'src' ] );
                    $r[ 'image' ][ 'src' ] = preg_replace( '#/?public/#', '', $r[ 'image' ][ 'src' ] );

                    if ( $r[ 'image' ][ 'src' ] && !is_file( PUBLIC_PATH . $r[ 'image' ][ 'src' ] ) )
                    {
                        unset( $r[ 'image' ] );
                    }
                }
                else
                {
                    unset( $r[ 'image' ] );
                }

                $r[ 'text' ] = str_replace( $img[ 'full_tag' ], '', $r[ 'text' ] );
            }


            $r[ 'text' ] = $this->prepareNewsContent( $r[ 'text' ], $r[ 'created' ], 500, 'maximum' );

        }

        $data[ 'topnews' ] = $returns;


        unset( $returns );


        $data[ 'blockdata' ] = $this->getParam( 'attributes' );
        //Cache::freeMem('getTopNews-' . CONTENT_TRANS . '-' . md5($order . $limit . PAGEID . User::getGroupId()));

        $this->load('Template');
        $tpl = new Template();
        $tpl->isProvider = true;
        $data = array_merge($this->Template->getTemplateData(), $data);

        return $tpl->process( 'cms/' . $template, $data, null, null, true );
    }

    /**
     *
     * @param string $text the content string
     * @param integer $created the content timestamp
     * @param integer $textLength default length is 500
     * @param string $imageChain default image chain is "maximum"
     * @return string
     */
    public function prepareNewsContent($text, $created = 0, $textLength = 500, $imageChain = 'maximum')
    {

        $text = Html::closeUnclosedTags( $text );
        $text = Strings::fixLatin( $text );
        $text = Content::tinyMCECoreTags( $text );

        if ( is_string( $imageChain ) )
        {
            $text = Content::parseImgTags( $text, $created, 'news', 1, $imageChain );
        }

        $text = str_replace( "\r", '', $text );

        // $text = preg_replace( '/<br\s*\/? >/i', ' ', $text );
        // $text = Strings::unhtmlspecialchars($text);

        return Strings::trimHtml( $text, $textLength, 's', ' [...]' );
    }

    /**
     * get all news categories
     *
     * @param bool $forGrid
     * @return array
     */
    public function getCategories($forGrid = false)
    {

        $transq = $this->buildTransWhere( 'news_categories', 't.id', 't' );

        $publish = '';
        if ( !defined( 'ADM_SCRIPT' ) || ADM_SCRIPT === false )
        {
            $publish = ' AND c.published=1';
        }

        if ( !$forGrid )
        {
            $cache = Cache::get( 'news-cats-' . PAGEID . '-' . CONTENT_TRANS . '-' . substr( md5( $publish ), 0, 3 ), 'data/news/' );

            if ( !is_array( $cache ) )
            {
                $cache = $this->db->query( 'SELECT c.*, t.*, COUNT(n.id) AS totalnews
                                 FROM %tp%news_categories AS c
                                LEFT JOIN %tp%news_categories_trans AS t ON(t.id=c.id)
                                LEFT JOIN %tp%news AS n ON(n.cat_id=c.id)
                                WHERE c.pageid = ? AND c.locked = 0' . $publish . ' AND ' . $transq . '
                                GROUP BY c.id
                                ORDER BY c.parentid, title', PAGEID )->fetchAll();

                Cache::write( 'news-cats-' . PAGEID . '-' . CONTENT_TRANS . '-' . substr( md5( $publish ), 0, 3 ), $cache, 'data/news/' );
            }

            return $cache;
        }
        else
        {

            return $this->db->query( 'SELECT c.*, t.*, COUNT(n.id) AS totalnews
                                 FROM %tp%news_categories AS c
                                LEFT JOIN %tp%news_categories_trans AS t ON(t.id=c.id)
                                LEFT JOIN %tp%news AS n ON(n.cat_id=c.id)
                                WHERE c.pageid = ? AND ' . $transq . '
                                GROUP BY c.id
                                ORDER BY c.ordering ASC, c.parentid ASC, title', PAGEID )->fetchAll();
        }
    }

    /**
     * Update the menuitems parent ids and ordering
     *
     * @param array $data
     * @internal param int $id
     * @return boolean
     */
    public function updateCatOrdering($data)
    {

        if ( count( $data[ 'items' ] ) )
        {
            $orderings = explode( ',', $data[ 'ordering' ] );
            foreach ( $data[ 'items' ] as $idx => $r )
            {
                $this->db->query( 'UPDATE %tp%news_categories SET ordering = ?, parentid = ? WHERE id = ?', $orderings[ $idx ], (int)$r[ 'parentid' ], $r[ 'itemid' ] );
            }

            Library::log( 'News Tree update success', 'info' );

            return true;
        }

        Library::log( 'News Tree update error!', 'warn' );

        return false;
    }

    /**
     *
     * @param boolean $isGoogleMap default is false
     * @return string/array
     */
    public function getSitemap($isGoogleMap = false)
    {

        $cats = $this->getCategories();

        foreach ( $cats as &$r )
        {
            $url = 'news/';
            if ( $r[ 'alias' ] )
            {
                $url .= $r[ 'alias' ];
                $url .= '.' . ( $r[ 'suffix' ] ? $r[ 'suffix' ] : Settings::get( 'mod_rewrite_suffix', 'html' ) );
            }
            else
            {
                $url .= 'category/' . $r[ 'id' ] . '/1';
            }

            $r[ 'url' ] = $url;
        }


        if ( $isGoogleMap )
        {
            $transq1 = $this->buildTransWhere( 'news', 'n.id', 'nt' );

            foreach ( $this->db->query( "SELECT n.id, n.created, n.modifed, nt.alias, nt.suffix
                FROM %tp%news AS n
                LEFT JOIN %tp%news_trans AS nt ON (nt.id=n.id)
                WHERE n.published>1
                AND n.locked = 0
                AND n.draft = 0
                AND n.pageid = ?
                AND n.usergroups IN(0, " . User::getGroupId() . ")
                AND ((n.publishoff > 0 AND n.publishoff>=?) OR n.publishoff=0)
                AND ((n.publishon > 0 AND n.publishon <= ?) OR n.publishon = 0 AND n.created <= ?)
                AND " . $transq1, PAGEID, TIMESTAMP, TIMESTAMP, TIMESTAMP )->fetchAll() as $r )
            {
                $r[ 'url' ] = 'news/item/' . ( $r[ 'alias' ] ? ( $r[ 'alias' ] . '.' . ( $r[ 'suffix' ] ? $r[ 'suffix' ] : 'html' ) ) : $r[ 'id' ] . '/' );
                $cats[ ]    = $r;
            }

            return $cats;
        }

        $data[ 'sitemap' ][ 'newscats' ] = $cats;
        unset( $cats );


        $this->load('Template');
        $tpl = new Template();
        $tpl->isProvider = true;
        $data = array_merge($this->Template->getTemplateData(), $data);

        return $tpl->process( 'news/sitemaptree', $data, null );
    }

    // Indexer functions

    /**
     *
     * @return integer
     */
    public function getSearchIndexDataCount()
    {

        $transq1 = $this->buildTransWhere( 'news', 'n.id', 'nt' );


        $this->db->query( 'REPLACE INTO %tp%indexer (contentid, title, content, content_time, groups, alias, suffix, modul, lang)
                        SELECT n.id AS contentid, nt.title, nt.text AS content, n.created AS content_time, n.usergroups AS groups, nt.alias, nt.suffix, \'news\', nt.lang
                FROM %tp%news AS n
                LEFT JOIN %tp%news_trans AS nt ON(nt.id=n.id)
                WHERE n.locked = 0 AND n.published>0 AND n.draft = 0 AND n.pageid = ?
                AND ((n.publishoff > 0 AND n.publishoff>=?) OR n.publishoff=0)
                AND ((n.publishon > 0 AND n.publishon <= ?) OR n.publishon = 0 AND n.created <= ?)
                AND ' . $transq1, PAGEID, TIMESTAMP, TIMESTAMP, TIMESTAMP );


        $r = $this->db->query( "SELECT COUNT(n.id) AS total FROM %tp%news AS n
                LEFT JOIN %tp%news_trans AS nt ON (nt.id=n.id)
                WHERE n.locked = 0 AND n.published>0 AND n.draft = 0 AND n.pageid = ?
                AND ((n.publishoff > 0 AND n.publishoff>=?) OR n.publishoff=0)
                AND ((n.publishon > 0 AND n.publishon <= ?) OR n.publishon = 0 AND n.created <= ?)
                AND " . $transq1, PAGEID, TIMESTAMP, TIMESTAMP, TIMESTAMP )->fetch();

        return $r[ 'total' ];
    }

    /**
     *
     * @param integer $from
     * @param integer $limit
     * @return array
     */
    public function getSearchIndexData($from = 0, $limit = 200)
    {

        $transq1 = $this->buildTransWhere( 'news', 'n.id', 'nt' );
        $sql     = "SELECT n.id AS contentid, nt.title, nt.text AS content, n.created AS time, n.usergroups AS groups, nt.alias, nt.suffix
                FROM %tp%news AS n
                LEFT JOIN %tp%news_trans AS nt ON(nt.id=n.id)
                WHERE n.locked = 0 AND n.published>0 AND n.draft = 0 AND n.pageid = ?
                AND ((n.publishoff > 0 AND n.publishoff>=?) OR n.publishoff=0)
                AND ((n.publishon > 0 AND n.publishon <= ?) OR n.publishon = 0 AND n.created <= ?)
                AND " . $transq1 . " LIMIT " . $from . "," . $limit;

        return $this->db->query( $sql, PAGEID, TIMESTAMP, TIMESTAMP, TIMESTAMP )->fetchAll();
    }

}

?>