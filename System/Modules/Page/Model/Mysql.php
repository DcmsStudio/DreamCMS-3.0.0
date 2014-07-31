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
 * @package      Page
 * @version      3.0.0 Beta
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Mysql.php
 */
class Page_Model_Mysql extends Model
{

    /**
     *
     * @param integer $id
     * @return array
     */
    public function getSerachItem($id = 0)
    {

        $locked = '';
        if ( $this->getApplication()->getMode() === Application::FRONTEND_MODE && !User::isAdmin() && !IS_SEEMODE )
        {
            $locked = 'p.published > 0 AND p.locked = 0 AND p.draft = 0 AND pt.draft = 0 AND
			((p.publishoff > 0 AND p.publishoff>=' . TIMESTAMP . ') OR p.publishoff=0) AND
			((p.publishon>0 AND p.publishon <= ' . TIMESTAMP . ') OR p.publishon = 0 AND p.created <= ' . TIMESTAMP . ') ';


        }

        $transq1 = $this->buildTransWhere( 'pages', 'p.id', 'pt' );
        $sql     = 'SELECT p.*, pt.content, pt.title
                FROM %tp%pages AS p 
                LEFT JOIN %tp%pages_trans AS pt ON(pt.id=p.id )
                WHERE p.pageid = ? AND p.inernalpage != 1
                AND p.id = ?
                AND ' . $transq1 . $locked . ' GROUP BY p.id';

        return $this->db->query( $sql, PAGEID, $id )->fetch();
    }

    /**
     *
     * @param string $text the content string
     * @param integer $created the content timestamp
     * @param integer $textLength default length is 450
     * @param string $imageChain default image chain is "maximum"
     * @return string
     */
    public function prepareContent($text, $created = 0, $textLength = 450, $imageChain = 'maximum')
    {

        $text = preg_replace( '#src=(["\']).*(pages/.+)\1#i', 'src=$1$2$1', $text );
        $text = Html::closeUnclosedTags( $text );
        $text = Strings::fixLatin( $text );

        $text = Content::tinyMCECoreTags( $text );
        $text = Content::parseImgTags( $text, $created, 'page', 1, $imageChain );

        if ( is_string( $imageChain ) )
        {
            $text = Content::parseImgTags( $text, $created, 'page', 1, $imageChain );
        }

        $text = str_replace( "\r", '', $text );
        $text = preg_replace( '/<br\s*\/?>/i', ' ', $text );
        //$text = Strings::unhtmlspecialchars($text);
        $text = strip_tags( $text, '<strong>,<em>,<img>' );

        return Strings::trimHtml( $text, $textLength, '<i>,<b>,<strong>,<em>,<img>', ' ...' );
    }

    /**
     * Find all Pages by Tag
     *
     * @param string $tag
     * @return array
     */
    public function findItemsByTag($tag = '')
    {

        $publish = $groupQuery = '';
        $locked  = $locked2 = '';
        if ( $this->getApplication()->getMode() === Application::FRONTEND_MODE && !User::isAdmin() && !IS_SEEMODE )
        {

            $publish = ' p.published > 0 AND p.draft = 0  AND pt.draft = 0 AND
			(p.publishoff>=' . TIMESTAMP . ' OR p.publishoff=0) AND
			((p.publishon>0 AND p.publishon <= ' . TIMESTAMP . ') OR p.created <= ' . TIMESTAMP . ') ';

            $publish = ' p.published > 0 AND p.locked = 0 AND p.draft = 0 AND pt.draft = 0 AND
			((p.publishoff > 0 AND p.publishoff>=' . TIMESTAMP . ') OR p.publishoff=0) AND
			((p.publishon>0 AND p.publishon <= ' . TIMESTAMP . ') OR p.publishon = 0 AND p.created <= ' . TIMESTAMP . ') ';


            $groupQuery = '';

            #	$locked = ' AND c.locked = 0 AND p.catid>0';
            #	$locked2 = ' AND p.locked = 0';
        }

        $transq1 = $this->buildTransWhere( 'pages', 'p.id', 'pt' );
        $transq  = $this->buildTransWhere( 'pages_categories', 'c.catid', 'ct' );

        $this->load( 'Tags' );
        $this->Tags->getHash( 'pages_trans' );
        $_id = $this->Tags->getTagIdByTag( $tag );
        $this->unload( 'Tags' );

        $sql = 'SELECT COUNT(p.id) AS total
                            FROM %tp%pages AS p
                            LEFT JOIN %tp%pages_trans AS pt ON (pt.id=p.id)
                            LEFT JOIN %tp%pages_categories AS c ON (c.catid=p.catid' . $locked . ')
                            LEFT OUTER JOIN %tp%comments AS com ON( com.post_id = p.id AND com.modul=\'page\' )
                            LEFT JOIN %tp%users AS u1 ON(u1.userid=p.created_by)
                            LEFT JOIN %tp%users AS u2 ON(u2.userid=p.modifed_by)
                            WHERE ' . $groupQuery . $publish . $locked2 . ' AND FIND_IN_SET(' . ( $_id > 0 ?
                $_id : '-1' ) . ', CONCAT(pt.tags, \',\') )
                            AND p.pageid = ? AND ' . $transq1 . '
                        GROUP BY p.id';
        $r   = $this->db->query( $sql, PAGEID )->fetch();


        $a = 20 * ( (int)$this->input( 'page' ) > 0 ? (int)$this->input( 'page' ) - 1 : 0 );

        $sql = 'SELECT p.*, pt.*,
                            u1.username AS created_user,
                            u2.username AS modifed_user,
                            COUNT(com.post_id) AS commentscounter
                            FROM %tp%pages AS p
                            LEFT JOIN %tp%pages_trans AS pt ON (pt.id=p.id)
                            LEFT JOIN %tp%pages_categories AS c ON (c.catid=p.catid' . $locked . ')
                            LEFT OUTER JOIN %tp%comments AS com ON( com.post_id = p.id AND com.modul=\'page\' )
                            LEFT JOIN %tp%users AS u1 ON(u1.userid=p.created_by)
                            LEFT JOIN %tp%users AS u2 ON(u2.userid=p.modifed_by)
                            WHERE ' . $groupQuery . $publish . $locked2 . ' AND FIND_IN_SET(' . ( $_id > 0 ?
                $_id : '-1' ) . ', CONCAT(pt.tags, \',\') )
                            AND p.pageid = ? AND ' . $transq1 . '
                        GROUP BY p.id LIMIT ' . $a . ', ' . 20;

        return array(
            'result' => $this->db->query( $sql, PAGEID )->fetchAll(),
            'total'  => $r[ 'total' ]
        );


        return $this->db->query( $sql, PAGEID )->fetchAll();
    }

    /**
     *
     * @param string $alias
     * @return mixed array
     */
    public function findAlias($alias = '')
    {

        $locked = $locked2 = '';
        if ( $this->getApplication()->getMode() === Application::FRONTEND_MODE && !User::isAdmin() && !IS_SEEMODE )
        {

            $publish = ' p.published > 0 AND p.draft = 0  AND pt.draft = 0 AND
			(p.publishoff>=' . TIMESTAMP . ' OR p.publishoff=0) AND
			((p.publishon>0 AND p.publishon <= ' . TIMESTAMP . ') OR p.created <= ' . TIMESTAMP . ') ';

            $publish = ' p.published > 0 AND p.draft = 0 AND pt.draft = 0 AND
			((p.publishoff > 0 AND p.publishoff>=' . TIMESTAMP . ') OR p.publishoff=0) AND
			((p.publishon>0 AND p.publishon <= ' . TIMESTAMP . ') OR p.publishon = 0 AND p.created <= ' . TIMESTAMP . ') ';


            $groupQuery = '';
        }
        $transq1 = $this->buildTransWhere( 'pages', 'p.id', 'pt' );
        $transq  = $this->buildTransWhere( 'pages_categories', 'c.catid', 'ct' );

        $sql = 'SELECT c.cssclass AS cat_cssclass, p.*, pt.*, IF(pt.teaser !=\'\', pt.teaser, ct.description) AS teaser,
                            u1.username AS created_user,
                            u2.username AS modifed_user,
                            COUNT(com.post_id) AS commentscounter
                            FROM %tp%pages AS p
                            LEFT JOIN %tp%pages_trans AS pt ON (pt.id=p.id)
                            LEFT JOIN %tp%pages_categories AS c ON (c.catid=p.catid' . $locked . ')
                            LEFT JOIN %tp%pages_categories_trans AS ct ON(ct.catid=c.catid)
                            LEFT OUTER JOIN %tp%comments AS com ON( com.post_id = p.id AND com.modul=\'page\' )
                            LEFT JOIN %tp%users AS u1 ON(u1.userid=p.created_by)
                            LEFT JOIN %tp%users AS u2 ON(u2.userid=p.modifed_by)
                            WHERE ' . $groupQuery . $publish . ' AND ' . $transq1 . $locked2 . ' AND  IF(p.catid > 0,' . $transq . ', 1)
                            AND p.pageid = ? AND pt.alias = ? GROUP BY p.id';

        return $this->db->query( $sql, PAGEID, $alias )->fetch();
    }

    /**
     * @param string $alias
     * @return mixed
     */
    public function getItemByAlias($alias = '')
    {

        return $this->findItemByAlias( $alias );
    }

    /**
     * Find a Page by the alias
     *
     * @param string $alias
     * @return mixed (array/bool)
     */
    public function findItemByAlias($alias = '')
    {

        $locked = $locked2 = '';
        if ( $this->getApplication()->getMode() === Application::FRONTEND_MODE && !User::isAdmin() && !IS_SEEMODE )
        {

            $publish = ' p.published > 0 AND p.draft = 0  AND pt.draft = 0 AND
			(p.publishoff>=' . TIMESTAMP . ' OR p.publishoff=0) AND
			((p.publishon>0 AND p.publishon <= ' . TIMESTAMP . ') OR p.created <= ' . TIMESTAMP . ') ';

            $publish = 'p.published > 0 AND p.draft = 0 AND pt.draft = 0 AND
			((p.publishoff > 0 AND p.publishoff>=' . TIMESTAMP . ') OR p.publishoff=0) AND
			((p.publishon>0 AND p.publishon <= ' . TIMESTAMP . ') OR p.publishon = 0 AND p.created <= ' . TIMESTAMP . ') AND ';

            #	$locked = ' AND c.locked = 0 AND p.catid>0';
            #	$locked2 = ' AND p.locked = 0';
            $groupQuery = '';
            // $groupQuery = 'p.usergroups IN(0,' . User::getGroupId() . ') AND ';
        }

        $transq1 = $this->buildTransWhere( 'pages', 'p.id', 'pt' );
        $transq  = $this->buildTransWhere( 'pages_categories', 'c.catid', 'ct' );

        $sql = 'SELECT c.cssclass AS cat_cssclass, p.*, pt.*, IF(pt.teaser !=\'\', pt.teaser, ct.description) AS teaser,
                            u1.username AS created_user,
                            u2.username AS modifed_user,
                            COUNT(com.post_id) AS commentscounter
                            FROM %tp%pages AS p
                            LEFT JOIN %tp%pages_trans AS pt ON (pt.id=p.id)
                            LEFT JOIN %tp%pages_categories AS c ON (c.catid=p.catid' . $locked . ')
                            LEFT JOIN %tp%pages_categories_trans AS ct ON(ct.catid=c.catid)
                            LEFT OUTER JOIN %tp%comments AS com ON( com.post_id = p.id AND com.modul=\'page\' )
                            LEFT JOIN %tp%users AS u1 ON(u1.userid=p.created_by)
                            LEFT JOIN %tp%users AS u2 ON(u2.userid=p.modifed_by)
                            WHERE ' . $groupQuery . $publish . '
                            p.pageid = ? AND pt.alias = ? AND ' . $transq1 . $locked2 . ' AND IF(p.catid > 0,' . $transq . ', 1)
                        GROUP BY p.id';

        return $this->db->query( $sql, PAGEID, $alias )->fetch();
    }

    /**
     * @param int $id
     * @return array
     */
    public function getItemByID($id = 0)
    {

        return $this->findItemByID( $id );
    }

    /**
     * Find a Page by the ID
     *
     * @param array $id
     * @return array
     */
    public function findItemsByID($id = array())
    {

        $locked = $locked2 = '';
        if ( $this->getApplication()->getMode() === Application::FRONTEND_MODE && !User::isAdmin() && !IS_SEEMODE )
        {
            $publish = ' p.published > 0 AND p.draft = 0 AND pt.draft = 0 AND
			(p.publishoff>=' . TIMESTAMP . ' OR p.publishoff=0) AND
			((p.publishon>0 AND p.publishon <= ' . TIMESTAMP . ') OR p.created <= ' . TIMESTAMP . ') ';

            $publish = ' p.published > 0 AND p.draft = 0 AND pt.draft = 0 AND
			((p.publishoff > 0 AND p.publishoff>=' . TIMESTAMP . ') OR p.publishoff=0) AND
			((p.publishon>0 AND p.publishon <= ' . TIMESTAMP . ') OR p.publishon = 0 AND p.created <= ' . TIMESTAMP . ') AND';

            $groupQuery = '';
            //  $groupQuery = 'p.usergroups IN(0,' . User::getGroupId() . ') AND ';

            #	$locked = ' AND c.locked = 0 AND p.catid > 0';
            #	$locked2 = ' AND p.locked = 0';
        }

        $transq1 = $this->buildTransWhere( 'pages', 'p.id', 'pt' );
        $transq  = $this->buildTransWhere( 'pages_categories', 'c.catid', 'ct' );

        $sql = 'SELECT c.cssclass AS cat_cssclass, p.*, pt.*, IF(pt.teaser !=\'\', pt.teaser, ct.description) AS teaser1,
                            u1.username AS created_user,
                            u2.username AS modifed_user,
                            COUNT(com.post_id) AS commentscounter
                            FROM %tp%pages AS p 
                            LEFT JOIN %tp%pages_trans AS pt ON (pt.id=p.id)
                            LEFT JOIN %tp%pages_categories AS c ON (c.catid=p.catid' . $locked . ')
                            LEFT JOIN %tp%pages_categories_trans AS ct ON(ct.catid=c.catid)
                            LEFT OUTER JOIN %tp%comments AS com ON( com.post_id = p.id AND com.modul=\'page\' )
                            LEFT JOIN %tp%users AS u1 ON(u1.userid=p.created_by)
                            LEFT JOIN %tp%users AS u2 ON(u2.userid=p.modifed_by)
                            WHERE ' . $groupQuery . $publish . '
                            p.pageid = ? AND ' . $transq1 . $locked2 . ' AND IF(p.catid > 0,' . $transq . ', 1) AND p.id IN(0,' . implode( ',', $id ) . ')
                        GROUP BY p.id';

        return $this->db->query( $sql, PAGEID )->fetchAll();
    }

    /**
     * Find a Page by the ID
     *
     * @param integer $id
     * @return array
     */
    public function findItemByID($id = 0)
    {

        $locked  = '';
        $locked2 = '';
        if ( $this->getApplication()->getMode() === Application::FRONTEND_MODE && !User::isAdmin() && !IS_SEEMODE )
        {
            $publish = ' p.published > 0 AND p.draft = 0 AND pt.draft = 0 AND
			(p.publishoff>=' . TIMESTAMP . ' OR p.publishoff=0) AND
			((p.publishon>0 AND p.publishon <= ' . TIMESTAMP . ') OR p.created <= ' . TIMESTAMP . ') ';


            $publish = ' p.published > 0 AND p.draft = 0 AND pt.draft = 0 AND
			((p.publishoff > 0 AND p.publishoff>=' . TIMESTAMP . ') OR p.publishoff=0) AND
			((p.publishon>0 AND p.publishon <= ' . TIMESTAMP . ') OR p.publishon = 0 AND p.created <= ' . TIMESTAMP . ') AND';


            $groupQuery = '';
            // $groupQuery = 'p.usergroups IN(0,' . User::getGroupId() . ') AND ';


            #	$locked = ' AND c.locked = 0 AND p.catid > 0';
            #	$locked2 = ' AND p.locked = 0';

        }

        $transq1 = $this->buildTransWhere( 'pages', 'p.id', 'pt' );
        $transq  = $this->buildTransWhere( 'pages_categories', 'c.catid', 'ct' );

        $sql = 'SELECT c.cssclass AS cat_cssclass, p.*, pt.*, IF(pt.teaser !=\'\', pt.teaser, ct.description) AS teaser,
                            u1.username AS created_user,
                            u2.username AS modifed_user,
                            COUNT(com.post_id) AS commentscounter
                            FROM %tp%pages AS p 
                            LEFT JOIN %tp%pages_trans AS pt ON (pt.id=p.id)
                            LEFT JOIN %tp%pages_categories AS c ON (c.catid=p.catid' . $locked . ')
                            LEFT JOIN %tp%pages_categories_trans AS ct ON(ct.catid=c.catid)
                            LEFT OUTER JOIN %tp%comments AS com ON( com.post_id = p.id AND com.modul=\'page\' )
                            LEFT JOIN %tp%users AS u1 ON(u1.userid=p.created_by)
                            LEFT JOIN %tp%users AS u2 ON(u2.userid=p.modifed_by)
                            WHERE ' . $groupQuery . $publish . '
                            p.pageid = ? AND ' . $transq1 . $locked2 . ' AND IF(p.catid > 0,' . $transq . ', 1) AND p.id = ?
                        GROUP BY p.id';

        return $this->db->query( $sql, PAGEID, $id )->fetch();
    }

    /**
     *
     * @param integer $pagetypeID
     * @return array
     */
    public function getPagesByPagetypeID($pagetypeID = 0)
    {

        $locked = $locked2 = '';

        if ( $this->getApplication()->getMode() === Application::FRONTEND_MODE && !User::isAdmin() && !IS_SEEMODE )
        {
            $publish = ' p.published > 0 AND p.draft = 0 AND pt.draft = 0 AND
			(p.publishoff>=' . TIMESTAMP . ' OR p.publishoff=0) AND
			((p.publishon>0 AND p.publishon <= ' . TIMESTAMP . ') OR p.created <= ' . TIMESTAMP . ') ';


            $publish = 'p.published > 0 AND p.draft = 0 AND pt.draft = 0 AND
			((p.publishoff > 0 AND p.publishoff>=' . TIMESTAMP . ') OR p.publishoff=0) AND
			((p.publishon>0 AND p.publishon <= ' . TIMESTAMP . ') OR p.publishon = 0 AND p.created <= ' . TIMESTAMP . ') AND ';


            $groupQuery = '';
            // $groupQuery = 'p.usergroups IN(0,' . User::getGroupId() . ') AND ';


            #	$locked = ' AND c.locked = 0 AND p.catid>0';
            #	$locked2 = ' AND p.locked = 0';

        }

        $transq1 = $this->buildTransWhere( 'pages', 'p.id', 'pt' );
        $transq  = $this->buildTransWhere( 'pages_categories', 'c.catid', 'ct' );

        $sql = 'SELECT c.cssclass AS cat_cssclass, p.*, pt.*, ptype.id AS pagetypeid, ptype.fields AS pagefields, ptype.contentlayout AS pagelayout,
                            u1.username AS created_user,
                            u2.username AS modifed_user,
                            COUNT(com.post_id) AS commentscounter
                            FROM %tp%pages AS p 
                            LEFT JOIN %tp%pages_types AS ptype ON (ptype.pagetype=p.pagetype)
                            LEFT JOIN %tp%pages_trans AS pt ON (pt.id=p.id)
                            LEFT JOIN %tp%pages_categories AS c ON (c.catid=p.catid' . $locked . ')
                            LEFT OUTER JOIN %tp%comments AS com ON( com.post_id = p.id AND com.modul=\'page\' )
                            LEFT JOIN %tp%users AS u1 ON(u1.userid=p.created_by)
                            LEFT JOIN %tp%users AS u2 ON(u2.userid=p.modifed_by)
                            WHERE ' . $groupQuery . $publish . '
                            p.pageid = ? AND ' . $transq1 . $locked2 . ' AND ptype.id = ?
                        GROUP BY p.id';

        return $this->db->query( $sql, PAGEID, $pagetypeID )->fetchAll();
    }

    /**
     *
     * @param $pagetype
     * @return array
     * @internal param string $pagetypeID
     */
    public function getPagesByPagetype($pagetype)
    {

        $locked = $locked2 = '';
        if ( $this->getApplication()->getMode() === Application::FRONTEND_MODE && !User::isAdmin() && !IS_SEEMODE )
        {
            $publish = ' p.published > 0 AND p.draft = 0 AND pt.draft = 0 AND
			(p.publishoff>=' . TIMESTAMP . ' OR p.publishoff=0) AND
			((p.publishon>0 AND p.publishon <= ' . TIMESTAMP . ') OR p.created <= ' . TIMESTAMP . ') ';

            $publish = 'p.published > 0 AND p.draft = 0 AND pt.draft = 0 AND
			((p.publishoff > 0 AND p.publishoff>=' . TIMESTAMP . ') OR p.publishoff=0) AND
			((p.publishon>0 AND p.publishon <= ' . TIMESTAMP . ') OR p.publishon = 0 AND p.created <= ' . TIMESTAMP . ') AND ';


            $groupQuery = '';
            //$groupQuery = 'p.usergroups IN(0,' . User::getGroupId() . ') AND ';

            #	$locked = ' AND c.locked = 0 AND p.catid>0';
            #	$locked2 = ' AND p.locked = 0';
        }


        $transq1 = $this->buildTransWhere( 'pages', 'p.id', 'pt' );

        $sql = 'SELECT c.cssclass AS cat_cssclass, p.*, pt.*, ptype.id AS pagetypeid, ptype.fields AS pagefields, ptype.contentlayout AS pagelayout,
                            u1.username AS created_user,
                            u2.username AS modifed_user,
                            COUNT(com.post_id) AS commentscounter
                            FROM %tp%pages AS p 
                            LEFT JOIN %tp%pages_types AS ptype ON (ptype.pagetype=p.pagetype)
                            LEFT JOIN %tp%pages_trans AS pt ON (pt.id=p.id)
                            LEFT JOIN %tp%pages_categories AS c ON (c.catid=p.catid' . $locked . ')
                            LEFT OUTER JOIN %tp%comments AS com ON( com.post_id = p.id AND com.modul=\'page\' )
                            LEFT JOIN %tp%users AS u1 ON(u1.userid=p.created_by)
                            LEFT JOIN %tp%users AS u2 ON(u2.userid=p.modifed_by)
                            WHERE ' . $groupQuery . $publish . '
                            p.pageid = ? AND ' . $transq1 . $locked2 . ' AND ptype.pagetype = ?
                        GROUP BY p.id';

        return $this->db->query( $sql, PAGEID, $pagetype )->fetchAll();
    }

    /**
     *
     * @param integer $id
     */
    public function getParents($id = 0)
    {

    }

    protected function _sort_helper(&$input, &$output, $parent_id, $idKey = 'id', $parentKey = 'parentid')
    {
        foreach ( $input as $key => $item )
            if ( $item[ $parentKey ] == $parent_id )
            {
                $output[ ] = $item;
                unset( $input[ $key ] );

                // Sort nested!!
                $this->_sort_helper( $input, $output, $item[ $idKey ], $idKey, $parentKey );
            }
    }

    protected function sort_items_into_tree($items, $idKey = 'id', $parentKey = 'parentid')
    {
        $tree = array();
        $this->_sort_helper( $items, $tree, 0, $idKey, $parentKey );

        return $tree;
    }


    /**
     * Get the Grid data for the Backend
     *
     * @param null $catid
     * @return array
     */
    public function getGridData($catid = null)
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
                $order = " ORDER BY p.created";
                break;

            case 'moddate':
                $order = " ORDER BY p.modifed";
                break;

            case 'id':
                $order = " ORDER BY p.id";
                break;

            case 'cat':
                $order = " ORDER BY p.catid";
                break;


            case 'hits':
                $order = " ORDER BY p.hits";
                break;

            case 'published':
                $order = " ORDER BY pt.draft " . $sort . ", p.draft " . $sort . ", p.published";
                break;

            case 'cattitle':
                $order = " ORDER BY cattitle";
                break;

            case 'comments':
                $order = " ORDER BY comments";
                break;
            case 'pagetypetitle':
                $order = " ORDER BY pagetypetitle";
                break;
            case 'title':
            default:
                $order = " ORDER BY pt.title";

                break;
        }

        $where = array();

        $where[ ] = 'p.pageid = ' . PAGEID;

        // ====================================================
        // Status der News
        // ====================================================
        switch ( HTTP::input( 'state' ) )
        {

            case 'online':
                $where[ ] = ' p.published = ' . PUBLISH_MODE;
                break;

            case 'offline':
                $where[ ] = ' p.published = ' . UNPUBLISH_MODE;
                break;

            case 'archived':
                $where[ ] = ' p.published = ' . ARCHIV_MODE;
                break;

            case 'draft':
                $where[ ] = ' pt.draft = 1 OR p.draft=1 OR p.published = ' . DRAFT_MODE;
                break;

            case 'online_offline':
                $where[ ] = ' p.published >= ' . UNPUBLISH_MODE;
                break;

            case 'online_offline':
                $where[ ] = ' p.published >= ' . UNPUBLISH_MODE;
                break;

            default:

                break;
        }


        if ( $this->input( 'pagetype' ) )
        {
            $where[ ] = ' p.pagetype = ' . (int)$this->input( 'pagetype' );
        }


        // mark untranslated news
        if ( HTTP::input( 'untrans' ) )
        {
            $where[ ] = ' pt.iscorelang = 1 AND pt.lang != ' . $this->db->quote( CONTENT_TRANS );
        }


        if ( $catid > 0 )
        {
            $where[ ] = " p.catid={$catid}";
        }


        $search = '';
        $search = HTTP::input( 'q' );
        $search = trim( (string)strtolower( $search ) );
        $all    = null;


        $_s = '';
        if ( $search != '' )
        {
            $search = str_replace( "%", "\%", $search );
            $search = str_replace( "*", "%", $search );

            $_s = " AND ( LOWER(pt.title) LIKE " . $this->db->quote( "%{$search}%" ) . " OR LOWER(pt.content) LIKE " . $this->db->quote( "%{$search}%" ) . ")";
        }


        $transq1 = $this->buildTransWhere( 'pages', 'p.id', 'pt' );

        // get the total number of records
        $sql = "SELECT COUNT(p.id) AS total
                FROM %tp%pages AS p
                LEFT JOIN %tp%pages_trans AS pt ON (pt.id=p.id)
                WHERE " . $transq1 . ( count( $where ) ? ' AND ' . implode( ' AND ', $where ) : "" ) . ( $_s ? $_s : '' ) . "";
        $r   = $this->db->query( $sql )->fetch();

        $total = $r[ 'total' ];
        $limit = $this->getPerpage();
        $page  = $this->getCurrentPage();


        $query = "SELECT p.*, pt.lang, pt.title,
                    u1.username AS created_user,
                    u2.username AS modifed_user,
                    COUNT(com.post_id) AS comments,
                    p_t.pagetype AS pagetypename,
                    IF(p.pagetype>0,p_t.title,'" . trans( 'Standart' ) . "') AS pagetypetitle,
                    ct.title AS cattitle
                    FROM %tp%pages AS p 
                    LEFT JOIN %tp%pages_types AS p_t ON(p_t.id = p.pagetype) 
                    LEFT JOIN %tp%pages_trans AS pt ON (pt.id=p.id) 
                    LEFT JOIN %tp%pages_categories_trans AS ct ON (ct.catid=p.catid)
                    LEFT OUTER JOIN %tp%comments AS com ON( com.post_id = p.id AND com.modul='page' )
                    LEFT JOIN %tp%users AS u1 ON(u1.userid=p.created_by)
                    LEFT JOIN %tp%users AS u2 ON(u2.userid=p.modifed_by)
                WHERE " . $transq1 . ( count( $where ) ? ' AND ' . implode( ' AND ', $where ) : "" ) . ( $_s ? $_s :
                '' ) . " GROUP BY p.id " . $order . ' ' . $sort . " LIMIT " . ( $limit * ( $page - 1 ) ) . "," . $limit;


        // $tree = $this->sort_items_into_tree($this->db->query($query)->fetchAll(), 'id', 'parentid' );
        return array(
            'result' => $this->db->query( $query )->fetchAll(),
            'total'  => $total
        );
    }

    /**
     * get all news categories
     *
     * @param bool $forGrid
     * @return array
     */
    public function getCategories($forGrid = false)
    {

        $transq  = $this->buildTransWhere( 'pages_categories', 't.catid', 't' );
        $publish = '';
        if ( !defined( 'ADM_SCRIPT' ) )
        {
            $publish = ' AND c.published=1';
        }

        $locked = '';
        if ( $this->getApplication()->getMode() === Application::FRONTEND_MODE && !User::isAdmin() && !IS_SEEMODE )
        {
            #	$locked = ' AND c.locked = 0 AND n.locked = 0';


        }

        if ( !$forGrid )
        {
            $cache = Cache::get( 'pages-cats-' . PAGEID . '-' . CONTENT_TRANS, 'data/pages/' );

            if ( !is_array( $cache ) )
            {
                $cache = $this->db->query( 'SELECT c.*, t.*, COUNT(n.id) AS totalpages
                                 FROM %tp%pages_categories AS c
                                LEFT JOIN %tp%pages_categories_trans AS t ON(t.catid=c.catid)
                                LEFT JOIN %tp%pages AS n ON(n.catid=c.catid AND n.published > 0)
                                WHERE c.pageid = ?' . $publish . $locked . ' AND ' . $transq . '
                                GROUP BY c.catid
                                ORDER BY c.ordering ASC, title ASC', PAGEID )->fetchAll();

                Cache::write( 'pages-cats-' . PAGEID . '-' . CONTENT_TRANS . '-' . substr( md5( $publish ), 0, 3 ), $cache, 'data/pages/' );
            }

            return $cache;
        }
        else
        {

            return $this->db->query( 'SELECT c.*, t.*, COUNT(n.id) AS totalpages
                                 FROM %tp%pages_categories AS c
                                LEFT JOIN %tp%pages_categories_trans AS t ON(t.catid=c.catid)
                                LEFT JOIN %tp%pages AS n ON(n.catid=c.catid AND n.published > 0)
                                WHERE c.pageid = ? AND ' . $transq . $locked . '
                                GROUP BY c.catid
                                ORDER BY c.ordering ASC, title ASC', PAGEID )->fetchAll();
        }
    }

    /**
     *
     * @param boolean $returnSqlResult
     * @return array
     */
    public function getCats($returnSqlResult = false)
    {

        $transq = $this->buildTransWhere( 'pages_categories', 'c.catid', 'ct' );
        $locked = '';
        if ( $this->getApplication()->getMode() === Application::FRONTEND_MODE && !User::isAdmin() && !IS_SEEMODE )
        {
            #	$locked = ' AND c.locked = 0';
        }

        $sql = "SELECT c.*, ct.*, c.catid AS id, c.parentid, ct.title AS name
                FROM %tp%pages_categories AS c
                LEFT JOIN %tp%pages_categories_trans AS ct ON(ct.catid = c.catid)
                WHERE " . $transq . $locked . "
                GROUP BY c.catid
                ORDER BY c.ordering ASC, ct.title ASC, c.parentid DESC";
        if ( $returnSqlResult )
        {
            return $this->db->query( $sql )->fetchAll();
        }

        $result = $this->db->query( $sql )->fetchAll();

        $_options      = array();
        $_options[ 0 ] = '-----';
        foreach ( $result as $r )
        {
            $_options[ $r[ 'id' ] ] = $r[ 'name' ];
        }

        return $_options;
    }

    /**
     *
     * @param int $pagetypeid
     * @return array
     * @internal param int $id
     */
    public function getCategoriesGridData($pagetypeid = 0)
    {

        switch ( $GLOBALS[ 'orderby' ] )
        {
            case 'title':
                $order = " ORDER BY c.ordering ASC, ct.title ";
                if ( empty( $GLOBALS[ 'orderby' ] ) )
                {
                    $sort = " ASC";
                }
                break;
            case 'date':
            default:
                $order = " ORDER BY c.ordering ASC, c.created ";
                break;
        }

        switch ( $GLOBALS[ 'orderby' ] )
        {
            case 'desc':
            default:
                $sort = 'DESC';
                break;
            case 'asc':
                $sort = 'ASC';
                break;
        }

        $transq1 = $this->buildTransWhere( 'pages_categories', 'c.catid', 'ct' );

        $r = $this->db->query( 'SELECT COUNT(c.catid) AS total
                          FROM %tp%pages_categories AS c
                          LEFT JOIN %tp%pages_categories_trans AS ct ON (ct.catid=c.catid)
                          LEFT JOIN %tp%users AS u1 ON(u1.userid=c.created_by)
                          LEFT JOIN %tp%users AS u2 ON(u2.userid=c.modifed_by)
                          WHERE c.pageid = ? AND ' . $transq1 . ( (int)$pagetypeid > 0 ?
                ' AND c.pagetypeid = ' . (int)$pagetypeid : '' ), PAGEID )->fetch();

        $limit = $this->getPerpage();
        $page  = $this->getCurrentPage();
        $a     = ( $page > 1 ? $limit * $page - 1 : 0 );

        $sql = 'SELECT COUNT(c.catid) AS total
                          FROM %tp%pages_categories AS c
                          LEFT JOIN %tp%pages_categories_trans AS ct ON (ct.catid=c.catid)
                          LEFT JOIN %tp%users AS u1 ON(u1.userid=c.created_by)
                          LEFT JOIN %tp%users AS u2 ON(u2.userid=c.modifed_by)
                          WHERE c.pageid = ? AND ' . $transq1 . ( (int)$pagetypeid > 0 ?
                ' AND c.pagetypeid = ' . (int)$pagetypeid : '' ) . $order . $sort . ' LIMIT ' . $a . ', ' . $limit;

        return array(
            'result' => $this->db->query( $sql, PAGEID )->fetchAll(),
            'total'  => $r[ 'total' ]
        );
    }

    /**
     *
     * @param integer $id
     * @param int $pagetypeid
     * @return \type
     */
    public function getCategorieById($id, $pagetypeid = 0)
    {

        $transq1 = $this->buildTransWhere( 'pages_categories', 'c.catid', 'ct' );

        return $this->db->query( 'SELECT c.*, ct.*,
                            u1.username AS created_user,
                            u2.username AS modifed_user
                          FROM %tp%pages_categories AS c
                          LEFT JOIN %tp%pages_categories_trans AS ct ON (ct.catid=c.catid)
                          LEFT JOIN %tp%users AS u1 ON(u1.userid=c.created_by)
                          LEFT JOIN %tp%users AS u2 ON(u2.userid=c.modifed_by)
                          WHERE c.catid = ? AND c.pageid = ? AND ' . $transq1 . ( (int)$pagetypeid > 0 ?
                ' AND c.pagetypeid = ' . (int)$pagetypeid : '' ), $id, PAGEID )->fetch();
    }

    /**
     * Get all  Pagetype
     */
    public function getPagetypesGridData()
    {

        switch ( $GLOBALS[ 'orderby' ] )
        {
            case 'pagetype':
                $order = " ORDER BY p.pagetype";
                break;
            case 'contentlayout':
                $order = " ORDER BY p.contentlayout";
                break;
            case 'title':
            default:
                $order = " ORDER BY p.title ";
                break;
        }

        switch ( $GLOBALS[ 'orderby' ] )
        {
            case 'desc':
            default:
                $sort = ' DESC';
                break;
            case 'asc':
                $sort = ' ASC';
                break;
        }


        $r = $this->db->query( 'SELECT COUNT(p.id) AS total FROM %tp%pages_types AS p' )->fetch();


        $limit = $this->getPerpage();
        $page  = $this->getCurrentPage();
        $a     = ( $page > 1 ? $limit * $page - 1 : 0 );

        $sql = 'SELECT p.*,
                                    u1.username AS created_user,
                                    u2.username AS modifed_user
                                    FROM %tp%pages_types AS p 
                                    LEFT JOIN %tp%users AS u1 ON(u1.userid=p.created_by)
                                    LEFT JOIN %tp%users AS u2 ON(u2.userid=p.modifed_by)
                                    ' . $order . $sort . ' LIMIT ' . $a . ', ' . $limit;

        return array(
            'result' => $this->db->query( $sql )->fetchAll(),
            'total'  => $r[ 'total' ]
        );
    }

    /**
     *
     * @return array
     */
    public function getPagetypes()
    {

        return $this->db->query( 'SELECT * FROM %tp%pages_types ORDER BY title' )->fetchAll();
    }

    /**
     *
     * @param integer $id
     * @return array
     */
    public function getPagetypeById($id = 0)
    {

        return $this->db->query( 'SELECT p.*,
                                    u1.username AS created_user,
                                    u2.username AS modifed_user
                                    FROM %tp%pages_types AS p 
                                    LEFT JOIN %tp%users AS u1 ON(u1.userid=p.created_by)
                                    LEFT JOIN %tp%users AS u2 ON(u2.userid=p.modifed_by)
                                  WHERE p.id = ?', $id )->fetch();
    }

    /**
     *
     * @param integer $id
     * @param array $data
     * @return integer
     */
    public function savePagetype($id, $data)
    {

        if ( $id )
        {
            $this->db->query( 'UPDATE %tp%pages_types SET pagetype = ?, title = ?, pagelayout = ?,  contentlayout = ?, fields = ?, description = ?, modifed = ?, modifed_by = ? WHERE id = ?',
                $data[ 'pagetype' ], $data[ 'title' ], (int)$data[ 'pagelayout' ], $data[ 'contentlayout' ], $data[ 'fields' ], $data[ 'description' ], TIMESTAMP, User::getUserId(), $id );

            return $id;
        }
        else
        {
            $this->db->query( 'INSERT INTO %tp%pages_types
                             (pagetype, title, description, pagelayout, contentlayout, fields, created_by, created, modifed, modifed_by) 
                             VALUES(?,?,?,?,?,?,?,?,?,?)', $data[ 'pagetype' ], $data[ 'title' ], $data[ 'description' ], (int)$data[ 'pagelayout' ], $data[ 'contentlayout' ], $data[ 'fields' ], User::getUserId(), TIMESTAMP, 0, 0 );

            return $this->db->insert_id();
        }
    }

    /**
     * Get all Fields in the Pagetype
     *
     * @param integer $pagetypeid
     * @return array
     */
    public function getFieldsGridData($pagetypeid)
    {

        switch ( $GLOBALS[ 'orderby' ] )
        {
            case 'fieldtype':
                $order = " ORDER BY f.fieldtype ";
                break;
            case 'fieldname':
                $order = " ORDER BY f.fieldname ";
                break;
            case 'description':
                $order = " ORDER BY f.fieldtype ASC, f.description ";
                break;
            case 'ordering':
            default:
                $order = " ORDER BY f.ordering ASC, f.fieldname ";
                break;
        }

        switch ( $GLOBALS[ 'sort' ] )
        {
            case 'desc':
            default:
                $sort = 'DESC';
                break;
            case 'asc':
                $sort = 'ASC';
                break;
        }


        $r = $this->db->query( 'SELECT COUNT(f.fieldid) AS total FROM %tp%pages_fields AS f WHERE f.pagetypeid = ?', $pagetypeid )->fetch();


        $limit = $this->getPerpage();
        $page  = $this->getCurrentPage();
        $a     = $page > 1 ? $limit * ( $page - 1 ) : 0;

        $sql = 'SELECT f.* FROM %tp%pages_fields AS f
                WHERE f.pagetypeid = ?' . $order . $sort . ' LIMIT ' . $a . ', ' . $limit;

        return array(
            'result' => $this->db->query( $sql, $pagetypeid )->fetchAll(),
            'total'  => $r[ 'total' ]
        );
    }

    /**
     *
     * @param integer $pagetypeid
     * @return array
     */
    public function getFieldsByPagetypeId($pagetypeid)
    {

        return $this->db->query( 'SELECT ft.* FROM %tp%pages_fields AS ft
                                  WHERE ft.pagetypeid = ? ORDER BY ft.ordering ASC', $pagetypeid )->fetchAll();
    }

    /**
     *
     * @param array $ids
     * @return array
     */
    public function getFields($ids)
    {

        return $this->db->query( 'SELECT ft.* FROM %tp%pages_fields AS ft
                                  WHERE ft.fieldid IN(0,' . implode( ',', $ids ) . ')' )->fetchAll();
    }

    /**
     *
     * @param integer $id
     * @return array
     */
    public function getFieldById($id = 0)
    {

        return $this->db->query( 'SELECT * FROM %tp%pages_fields WHERE fieldid = ?', $id )->fetch();
    }

    /**
     *
     * @param integer $contentid
     * @param array $ids
     * @return array
     */
    public function getContentFieldsData($contentid, $ids)
    {

        $transq1 = $this->buildTransWhere( 'pages_fields_data', 'ft.fieldid', 'ft' );

        return $this->db->query( 'SELECT ft.* FROM %tp%pages_fields_data AS ft
                                  WHERE ft.fieldid IN(0,' . implode( ',', $ids ) . ') AND ft.itemid = ?' . $transq1, $contentid )->fetchAll();
    }

    /**
     *
     * @param integer $id
     * @param array $data
     * @return integer
     */
    public function savePagetypeField($id, $data)
    {

        $savedata = $this->prepareData( $data );

        if ( !$id )
        {
            $this->db->query( '
            INSERT INTO %tp%pages_fields SET
            `pagetypeid` = ?,
            `fieldname` = ?,
            `fieldtype` = ?,
            `description` = ?,
            `options` = ?', (int)$savedata[ 'pagetypeid' ], $savedata[ 'fieldname' ], $savedata[ 'fieldtype' ], $savedata[ 'description' ], $savedata[ 'options' ] );


            return $this->db->insert_id();
        }
        else
        {
            $this->db->query( 'UPDATE %tp%pages_fields SET `fieldname` = ?, `fieldtype` = ?, `description` = ?, `options` = ? WHERE `fieldid` = ?', $savedata[ 'fieldname' ], $savedata[ 'fieldtype' ], $savedata[ 'description' ], $savedata[ 'options' ], $id );
        }
    }

    /**
     *
     * @param integer $id
     *
     */
    public function deleteField($id = 0)
    {

        $result = $this->db->query( 'SELECT dataid FROM %tp%pages_fields_data WHERE fieldid = ?', $id )->fetchAll();
        foreach ( $result as $r )
        {
            $this->db->query( 'DELETE FROM %tp%pages_fields_data_trans WHERE dataid = ?', $r[ 'dataid' ] );
        }

        unset( $result );

        $this->db->query( 'DELETE FROM %tp%pages_fields_data WHERE fieldid = ?', $id );
        $this->db->query( 'DELETE FROM %tp%pages_fields WHERE fieldid = ?', $id );
    }

    /**
     * Prepare Form Field settings for saving
     *
     * @param array $data
     * @return array
     */
    private function prepareData($data)
    {

        $class_name = 'Field_' . ucfirst( strtolower( $data[ 'fieldtype' ] ) ) . 'Field';
        $attributes = call_user_func( array(
            $class_name,
            'getAttributes'
        ) );

        $options = array();
        foreach ( $attributes as $attribute )
        {
            if ( !empty( $data[ $attribute ] ) )
            {
                $options[ $attribute ] = $data[ $attribute ];
            }
        }

        $ret                  = array();
        $ret[ 'options' ]     = serialize( $options );
        $ret[ 'pagetypeid' ]  = (int)$data[ 'pagetypeid' ];
        $ret[ 'fieldid' ]     = (int)$data[ 'fieldid' ];
        $ret[ 'fieldname' ]   = (string)$data[ 'fieldname' ];
        $ret[ 'fieldtype' ]   = (string)$data[ 'fieldtype' ];
        $ret[ 'description' ] = (string)$data[ 'description' ];

        // $ret[ 'rel' ]         = (!$ret[ 'formid' ] ? 'profilefield' : ( string ) $data[ 'rel' ] );
        return $ret;
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
                $this->db->query( 'UPDATE %tp%pages_categories SET ordering = ?, parentid = ? WHERE catid = ?', $orderings[ $idx ], (int)$r[ 'parentid' ], $r[ 'itemid' ] );
            }

            Library::log( 'Page Categorie Tree update success', 'info' );

            return true;
        }

        Library::log( 'Page Categorie Tree update error!', 'warn' );

        return false;
    }

    /**
     *
     * @param integer $id
     * @param string $action
     * @param string $none

    public function unlock( $id, $action, $none = '' )
     * {
     *
     * if ( $action === 'index' )
     * {
     * $this->db->query( 'UPDATE %tp%pages SET locked = 0 WHERE id = ?', $id );
     * }
     * }
     */

    /**
     *
     * @return array
     */
    public function getContainerOptions()
    {

        return $this->db->query( "SELECT
            t.templatename AS value,             
            t.templatename AS label, 
            CONCAT(t.templatename, ' (', s.title, ')') AS label         
            FROM %tp%skins_templates AS t
            LEFT JOIN %tp%skins AS s ON(s.id = t.set_id)
            WHERE t.group_name='pages'
            GROUP BY t.id
            ORDER BY label" )->fetchAll();
    }

    /**
     *
     * @param integer $id
     * @return array|void
     */
    public function getDataById($id = null)
    {

    }

    /**
     *
     * @param string $parentitems
     * @return array
     */
    public function getParentPageBreadcrumbs($parentitems = '')
    {

        if ( $parentitems === '' || $parentitems == null || $parentitems === false )
        {
            return array();
        }
        $locked1 = '';
        $locked2 = '';
        if ( $this->getApplication()->getMode() === Application::FRONTEND_MODE && !User::isAdmin() && !IS_SEEMODE )
        {
            $locked1 = ' AND p.locked = 0 AND p.draft = 0 AND pt.draft = 0 AND
			((p.publishoff > 0 AND p.publishoff>=' . TIMESTAMP . ') OR p.publishoff=0) AND
			((p.publishon>0 AND p.publishon <= ' . TIMESTAMP . ') OR p.publishon = 0 AND p.created <= ' . TIMESTAMP . ') ';


            $locked2 = ' AND c.locked = 0';
        }

        $transq1 = $this->buildTransWhere( 'pages', 'p.id', 'pt' );
        $transq1 = $this->buildTransWhere( 'pages_categories', 'c.catid', 'ct' );

        return $this->db->query( 'SELECT
                    p.id, p.catid, p.parentid, pt.title, pt.pagetitle, pt.alias, pt.suffix, p.isindexpage, c.cssclass,
                    ct.title AS cattitle, pt.alias AS catalias, pt.suffix AS catsuffix
                    FROM %tp%pages AS p
                    LEFT JOIN %tp%pages_trans AS pt ON (pt.id=p.id)
                    LEFT JOIN %tp%pages_categories AS c ON(c.catid=p.catid)
                    LEFT JOIN %tp%pages_categories_trans AS ct ON(ct.catid=c.catid)
                    WHERE ' . $transq1 . $locked1 . $locked2 . ' AND p.id IN(' . $parentitems . ')' )->fetchAll();
    }

    /**
     *
     * @param integer $catid
     * @param array $arr
     * @return array
     */
    public function getCatBreadcrumbs($catid, $arr = array())
    {

        $locked = '';
        if ( $this->getApplication()->getMode() === Application::FRONTEND_MODE && !User::isAdmin() && !IS_SEEMODE )
        {
            $locked = ' AND c.locked = 0';
        }

        $transq = $this->buildTransWhere( 'pages_categories', 'c.catid', 'ct' );
        $rs     = $this->db->query( "SELECT c.catid, c.parentid, c.cssclass, ct.title, ct.alias, ct.suffix
                FROM %tp%pages_categories AS c
                LEFT JOIN %tp%pages_categories_trans AS ct ON(ct.catid = c.catid)
                WHERE " . $transq . $locked . " AND c.catid = ?
                GROUP BY c.catid", $catid )->fetch();

        if ( $rs[ 'parentid' ] )
        {
            $arr[ ] = $rs;

            return $this->getCatBreadcrumbs( $rs[ 'parentid' ], $arr );
        }
        else
        {
            $arr[ ] = $rs;

            return $arr;
        }
    }

    /**
     * Get all parent Pages
     *
     * @param int $id
     * @return array
     */
    public function getParentPages($id = 0)
    {

        $locked = '';
        if ( $this->getApplication()->getMode() === Application::FRONTEND_MODE && !User::isAdmin() && !IS_SEEMODE )
        {
            $locked = ' AND p.locked = 0 AND p.draft = 0 AND pt.draft = 0 AND
			((p.publishoff > 0 AND p.publishoff>=' . TIMESTAMP . ') OR p.publishoff=0) AND
			((p.publishon>0 AND p.publishon <= ' . TIMESTAMP . ') OR p.publishon = 0 AND p.created <= ' . TIMESTAMP . ') ';
        }

        $transq1 = $this->buildTransWhere( 'pages', 'p.id', 'pt' );
        $query   = "SELECT p.id, p.parentid, pt.title, pt.alias, pt.suffix
                    FROM %tp%pages AS p
                    LEFT JOIN %tp%pages_trans AS pt ON (pt.id=p.id)
                    WHERE " . $transq1 . $locked . " AND p.id != ? GROUP BY p.id " . ' ORDER BY p.parentid, pt.title' . " LIMIT 200";


        $result = $this->db->query( $query, $id )->fetchAll();

        $pages = array();
        foreach ( $result as $row )
        {
            $pages[ $row[ 'parentid' ] ][ $row[ 'id' ] ] = $row;
        }
        $result  = null;
        $options = array();
        $this->createPages( $options, $pages, 0, 1 );

        return $options;
    }

    /**
     * Helper for function getPages
     * build a array of all pages (selection tree)
     *
     * @param     $options
     * @param     $pages
     * @param int $parentid
     * @param int $depth
     * @return void
     */
    private function createPages(&$options, &$pages, $parentid = 0, $depth = 1)
    {

        if ( !isset( $pages[ $parentid ] ) )
        {
            return;
        }

        while ( list( $key1, $rows ) = each( $pages[ $parentid ] ) )
        {
            if ( strlen( $rows[ 'title' ] ) > 60 )
            {
                $rows[ 'title' ] = substr( $rows[ 'title' ], 0, 60 ) . '...';
            }
            $titel_of_board  = ( ( $depth > 1 ) ? str_repeat( "&nbsp;&nbsp;&#0124;--", $depth - 1 ) :
                    '' ) . " " . $rows[ 'title' ] . ' (' . $rows[ 'alias' ] . ( $rows[ 'suffix' ] ?
                    '.' . $rows[ 'suffix' ] : '' ) . ')';
            $rows[ 'title' ] = $titel_of_board;
            $options[ ]      = $rows;
            $this->createPages( $options, $pages, $rows[ 'id' ], $depth + 1 );
        }
    }

    /**
     * @param int $catid
     * @param      $idKey
     * @param      $multiIdKey
     * @param bool $mode
     */
    public function movePages($catid = 0, $idKey, $multiIdKey, $isCat = false)
    {

        $data = $this->getMultipleIds( $idKey, $multiIdKey );


        if ( !$data[ 'id' ] && !$data[ 'isMulti' ] )
        {
            Error::raise( "Invalid ID" );
        }


        if ( !$isCat )
        {
            $this->load( 'SideCache' );

            if ( $data[ 'isMulti' ] )
            {
                $result = $this->db->query( "SELECT
                                              nt.title, n.id, nt.alias 
                                              FROM %tp%pages AS n 
                                              LEFT JOIN %tp%pages_trans AS nt ON(nt.id = n.id)
                                             WHERE n.id IN(0," . $data[ 'id' ] . ") GROUP BY n.id" )->fetchAll();

                foreach ( $result as $r )
                {
                    $this->SideCache->cleanSideCache( 'page', 'index', $r[ 'alias' ], $id );

                    $this->db->query( 'UPDATE %tp%pages SET catid=? WHERE id=?', $catid, $r[ 'id' ] );

                    Library::log( "Change Page categorie \"{$r['title']}\" (ID:{$r['id']})." );
                }


                Library::sendJson( true, trans( 'Seiten wurden verschoben' ) );
                exit;
            }
            else
            {
                $r = $this->db->query( "SELECT nt.title, n.id, nt.alias
                                        FROM %tp%pages AS n 
                                        LEFT JOIN %tp%pages_trans AS nt ON(nt.id = n.id)
                                        WHERE n.id=? LIMIT 1", $data[ 'id' ] )->fetch();

                $this->db->query( 'UPDATE %tp%pages SET catid=? WHERE id=?', $catid, $data[ 'id' ] );
                $this->SideCache->cleanSideCache( 'page', 'index', $r[ 'alias' ], $data[ 'id' ] );

                Library::log( "Change Page categorie \"{$r['title']}\" (ID:{$r['id']})." );
                Library::sendJson( true, trans( 'Seite wurde verschoben' ) );
                exit;
            }
        }
        else
        {
            $to = $this->getCategorieById( $catid );
            $r  = $this->getCategorieById( $data[ 'id' ] );
            $this->db->query( 'UPDATE %tp%pages_categories SET parentid = ? WHERE catid = ?', $catid, $r[ 'catid' ] );

            Library::log( "Move page categorie \"{$r['title']}\" (ID:{$r['id']}) to \"{$to['title']}\"." );
            Library::sendJson( true, trans( 'Kategorie wurde verschoben' ) );
            exit;
        }
    }

    /**
     *
     * @param int $id
     * @return bool
     */
    public function setIndexPage($id)
    {


        $transq1 = $this->buildTransWhere( 'pages', 'p.id', 'pt' );
        $rs      = $this->db->query( 'SELECT p.*, pt.*
                                 FROM %tp%pages AS p 
                                 LEFT JOIN %tp%pages_trans AS pt ON(pt.id = p.id)
                                 WHERE p.id=? AND ' . $transq1, $id )->fetch();

        if ( !$rs[ 'isindexpage' ] )
        {
            $indexpage = $this->findIndexPage( $rs[ 'catid' ] );
            if ( $indexpage[ 'id' ] )
            {
                $this->db->query( 'UPDATE %tp%pages SET isindexpage = 0 WHERE id = ?', $indexpage[ 'id' ] );
            }

            $this->db->query( 'UPDATE %tp%pages SET isindexpage = 1 WHERE id = ?', $id );

            Library::log( 'Has change the indexpage to `' . $rs[ 'title' ] . '`.' );

            return $rs[ 'title' ];
        }

        return false;
    }

    /**
     * @param $catid
     * @return type
     */
    public function findIndexPage($catid)
    {

        $transq1 = $this->buildTransWhere( 'pages', 'p.id', 'pt' );
        $locked  = '';
        if ( $this->getApplication()->getMode() === Application::FRONTEND_MODE && !User::isAdmin() && !IS_SEEMODE )
        {
            $locked = ' AND p.locked = 0 AND p.draft = 0 AND pt.draft = 0 AND
			((p.publishoff > 0 AND p.publishoff>=' . TIMESTAMP . ') OR p.publishoff=0) AND
			((p.publishon>0 AND p.publishon <= ' . TIMESTAMP . ') OR p.publishon = 0 AND p.created <= ' . TIMESTAMP . ') ';


        }

        return $this->db->query( "SELECT p.*, pt.*
                                 FROM %tp%pages AS p 
                                 LEFT JOIN %tp%pages_trans AS pt ON(pt.id = p.id)
                                 WHERE 
                                 p.catid = ? AND p.isindexpage = 1 AND " . $transq1 . $locked, $catid )->fetch();
    }

    /**
     * Find index page by Categorie
     *
     * @param $alias
     * @return array|type
     */
    public function findIndexPageByCatAlias($alias)
    {

        $locked = '';
        if ( $this->getApplication()->getMode() === Application::FRONTEND_MODE && !User::isAdmin() && !IS_SEEMODE )
        {
            $locked = ' AND c.locked = 0';
        }


        $transq1 = $this->buildTransWhere( 'pages_categories', 'c.catid', 'ct' );
        $cat     = $this->db->query( 'SELECT c.*, ct.*,
                            u1.username AS created_user,
                            u2.username AS modifed_user
                          FROM %tp%pages_categories AS c
                          LEFT JOIN %tp%pages_categories_trans AS ct ON (ct.catid=c.catid)
                          LEFT JOIN %tp%users AS u1 ON(u1.userid=c.created_by)
                          LEFT JOIN %tp%users AS u2 ON(u2.userid=c.modifed_by)
                          WHERE c.pageid = ? AND ' . $transq1 . $locked . ' AND ct.alias = ? ', PAGEID, $alias )->fetch();

        if ( $cat[ 'catid' ] )
        {

            if ( $this->getApplication()->getMode() === Application::FRONTEND_MODE && !User::isAdmin() && !IS_SEEMODE )
            {
                $publish = ' AND p.draft = 0 AND pt.draft = 0 AND
			((p.publishoff > 0 AND p.publishoff>=' . TIMESTAMP . ') OR p.publishoff=0) AND
			((p.publishon>0 AND p.publishon <= ' . TIMESTAMP . ') OR p.publishon = 0 AND p.created <= ' . TIMESTAMP . ') ';


                $groupQuery = 'p.usergroups IN(0,' . User::getGroupId() . ') AND ';
            }

            $transq1 = $this->buildTransWhere( 'pages', 'p.id', 'pt' );

            $sql = 'SELECT p.*, pt.*,
                            u1.username AS created_user,
                            u2.username AS modifed_user,
                            COUNT(com.post_id) AS commentscounter
                            FROM %tp%pages AS p 
                            LEFT JOIN %tp%pages_trans AS pt ON (pt.id=p.id)
                            LEFT JOIN %tp%pages_categories AS c ON(c.catid = p.catid)
                            LEFT OUTER JOIN %tp%comments AS com ON( com.post_id = p.id AND com.modul=\'page\' )
                            LEFT JOIN %tp%users AS u1 ON(u1.userid=p.created_by)
                            LEFT JOIN %tp%users AS u2 ON(u2.userid=p.modifed_by)
                            WHERE ' . $groupQuery . ' p.published=1' . $publish . '
                            AND p.pageid = ? AND p.catid = ? AND ' . $transq1 . ' AND p.isindexpage = 1
                        GROUP BY p.id';

            return $this->db->query( $sql, PAGEID, $cat[ 'catid' ] )->fetch();
        }

        return array();
    }

    public function publishCat($idKey, $multiIdKey, $mode = false)
    {

        $data = $this->getMultipleIds( $idKey, $multiIdKey );

        if ( !$data[ 'id' ] && !$data[ 'isMulti' ] )
        {
            Error::raise( "Invalid ID" );
        }

        if ( !$mode )
        {
            $this->load( 'SideCache' );

            if ( $data[ 'isMulti' ] )
            {

                $result = $this->db->query( "SELECT
                                              nt.title, n.catid, nt.alias, n.published 
                                              FROM %tp%pages_categories AS n 
                                              LEFT JOIN %tp%pages_categories_trans AS nt ON(nt.catid = n.catid)
                                             WHERE n.catid IN(0," . $data[ 'id' ] . ") GROUP BY n.catid" )->fetchAll();

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

                    $this->SideCache->cleanSideCache( 'page', 'index', $r[ 'alias' ], $id );

                    $this->db->query( 'UPDATE %tp%pages_categories SET published=? WHERE id=?', '' . $state, $r[ 'catid' ] );
                    Library::log( "Change Pagecat publishing \"{$r['title']}\" to status {$state} (ID:{$r['catid']})." );
                }

                Library::sendJson( true, '' . $state );
                exit;
            }
            else
            {
                $r = $this->db->query( "SELECT nt.title, n.catid, nt.alias, n.published
                                        FROM %tp%pages_categories AS n 
                                        LEFT JOIN %tp%pages_categories_trans AS nt ON(nt.catid = n.catid)
                                        WHERE n.catid=?", $data[ 'id' ] )->fetch();

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
                }
                else
                {
                    $state = ( $r[ 'published' ] > UNPUBLISH_MODE ? UNPUBLISH_MODE : PUBLISH_MODE );
                }

                $this->db->query( 'UPDATE %tp%pages_categories SET published=? WHERE catid=?', $state, $data[ 'id' ] );
                $this->SideCache->cleanSideCache( 'page', 'index', $r[ 'alias' ], $data[ 'id' ] );

                Library::log( "Change Pagecat publishing to status {$state} (ID:{$id})." );
                Library::sendJson( true, '' . $state );
                exit;
            }
        }
    }

    /**
     * Publish or unpublish pages
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

                $result = $this->db->query( "SELECT
                                              nt.title, n.id, nt.alias, n.published 
                                              FROM %tp%pages AS n 
                                              LEFT JOIN %tp%pages_trans AS nt ON(nt.id = n.id)
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

                    $this->SideCache->cleanSideCache( 'page', 'index', $r[ 'alias' ], $r[ 'id' ] );

                    $this->db->query( 'UPDATE %tp%pages SET published=? WHERE id=?', '' . $state, $r[ 'id' ] );


                    if ( $state === PUBLISH_MODE )
                    {
                        // send pings
                        $ps->setData( 'page/' . $r[ 'alias' ] . ( $r[ 'suffix' ] ? '.' . $r[ 'suffix' ] : '.' . Settings::get( 'mod_rewrite_suffix', 'html' ) ), $r[ 'title' ] )->genericPing();
                    }


                    Library::log( "Change Page publishing \"{$r['title']}\" to status {$state} (ID:{$r['id']})." );
                }

                Library::sendJson( true, '' . $state );
                exit;
            }
            else
            {
                $r = $this->db->query( "SELECT nt.title, n.id, nt.alias, n.published
                                        FROM %tp%pages AS n 
                                        LEFT JOIN %tp%pages_trans AS nt ON(nt.id = n.id)
                                        WHERE n.id=?", $data[ 'id' ] )->fetch();

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
                }
                else
                {
                    $state = ( $r[ 'published' ] > UNPUBLISH_MODE ? UNPUBLISH_MODE : PUBLISH_MODE );
                }

                $this->db->query( 'UPDATE %tp%pages SET published=? WHERE id=?', $state, $data[ 'id' ] );
                $this->SideCache->cleanSideCache( 'page', 'index', $r[ 'alias' ], $data[ 'id' ] );

                if ( $state === PUBLISH_MODE )
                {
                    // send pings
                    $ps->setData( 'page/' . $r[ 'alias' ] . ( $r[ 'suffix' ] ? '.' . $r[ 'suffix' ] : '.' . Settings::get( 'mod_rewrite_suffix', 'html' ) ), $r[ 'title' ] )->genericPing();
                }

                Library::log( "Change Page publishing to status {$state} (ID:{$id})." );
                Library::sendJson( true, '' . $state );
                exit;
            }
        }
        else
        {

        }
    }

    /**
     * Delete the Page and move to Trash
     *
     * @param integer $id
     * @return bool
     */
    public function delete($id, $table = null)
    {

        //    $indexer = new Search();

        $this->load( 'AliasRegistry' );
        $this->load( 'Trash' );

        $this->Trash->setTrashTable( '%tp%pages' );
        $this->Trash->setTrashTableLabel( 'Page Item' );

        $rs = $this->findItemByID( $id );


        $r = $this->db->query( 'SELECT * FROM %tp%pages WHERE id = ?', $id )->fetch();
        $this->db->query( 'DELETE FROM %tp%pages WHERE id = ?', $id );

        // Move to Trash
        $trashData                 = array();
        $trashData[ 'data' ]       = $r;
        $trashData[ 'label' ]      = $rs[ 'title' ];
        $trashData[ 'trans_data' ] = $this->db->query( 'SELECT * FROM %tp%pages_trans WHERE id = ?', $id )->fetchAll();

        $this->Trash->addTrashData( $trashData );
        $this->Trash->moveToTrash();

        // unregister alias
        $this->AliasRegistry->removeAlias( $trashData[ 'trans_data' ][ 'alias' ], 'page', 'index' );

        // remove Search Index
        //  $indexer->deleteIndex('news', CONTENT_TRANS, 'page/', $id);
        // Remove Cache
        Cache::delete( 'pageText-' . $id, 'data/page' );


        $this->load( 'SideCache' );
        $this->SideCache->cleanSideCache( 'page', 'index', $trashData[ 'trans_data' ][ 'alias' ], $id );

        return true;
    }

    /**
     * Is content translated?
     *
     * @param integer $id
     * @param bool $useCat
     * @return bool
     */
    public function hasTranslation($id = 0, $useCat = false)
    {

        if ( !$useCat )
        {
            $trans = $this->db->query( 'SELECT id FROM %tp%pages_trans WHERE id = ? AND lang = ?', $id, CONTENT_TRANS )->fetch();
        }
        else
        {
            $trans = $this->db->query( 'SELECT catid AS id FROM %tp%pages_categories_trans WHERE catid = ? AND lang = ?', $id, CONTENT_TRANS )->fetch();
        }

        if ( $trans[ 'id' ] )
        {
            return true;
        }

        return false;
    }

    /**
     *
     * @param integer $id
     * @param array $arr
     */
    private function getParentItemsList($id, &$arr)
    {

        $rs = $this->db->query( 'SELECT id, parentid FROM %tp%pages WHERE id = ?', $id )->fetch();

        if ( $rs[ 'parentid' ] )
        {
            $arr[ ] = $rs[ 'id' ];
            $this->getParentItemsList( $rs[ 'parentid' ], $arr );
        }
        else
        {
            $arr[ ] = $id;
        }
    }

    /**
     *
     * @param integer $id
     * @param int $parentid
     */
    private function updateParentIds($id, $parentid = 0)
    {

        if ( $parentid )
        {
            $items = array();
            $this->getParentItemsList( $parentid, $items );

            if ( count( $items ) )
            {
                $this->db->query( 'UPDATE %tp%pages SET parentids = ? WHERE id = ?', implode( ',', $items ), $id );
            }
        }
    }

    /**
     * give the news with the translation
     * if $language is null returns the current translation by defined CONTENT_TRANS
     *
     * @param type $id
     * @param string $language default is null
     * @param bool $useCat
     * @return array (record => ... , trans => ...)
     */


    /**
     *
     * @param integer $id
     * @param array $data
     * @return int
     */
    public function saveTranslation($id = 0, $data = array())
    {

        $access = ( is_array( $data[ 'access' ] ) ? $data[ 'access' ] : array(
            0
        ) );


        $data[ 'usergroups' ] = implode( ',', $access );
        $data[ 'content' ]    = $data[ 'content' ];


        $teaser = trim( strip_tags( $data[ 'teaser' ] ) );
        if ( !$teaser )
        {
            $data[ 'teaser' ] = '';
        }


        $coredata = array(
            'parentid'           => (int)$data[ 'parentid' ],
            'catid'              => (int)$data[ 'catid' ],
            'pageid'             => PAGEID,
            'pagetype'           => ( !empty( $data[ 'pagetype' ] ) ? $data[ 'pagetype' ] : 'page' ),
            'usergroups'         => (string)$data[ 'usergroups' ],
            'cancomment'         => (int)$data[ 'cancomment' ],
            'usesocialbookmarks' => (int)$data[ 'usesocialbookmarks' ],
            'usefootnotes'       => (int)$data[ 'usefootnotes' ],
            'useauthorinfo'      => (int)$data[ 'useauthorinfo' ],
            'userating'          => (int)$data[ 'userating' ],
            'usetitle'           => (int)$data[ 'usetitle' ],
            'useteaser'          => (int)$data[ 'useteaser' ],
            'layout'             => $data[ 'layout' ],
            'parentids'          => '',
            'hits'               => 0,
            'created_by'         => (int)User::getUserId(),
            'created'            => TIMESTAMP,
            'modifed_by'         => (int)User::getUserId(),
            'modifed'            => TIMESTAMP,
            'rollback'           => 0,
            'contentlayout'      => (string)$data[ 'content-layout' ],
            'offline'            => (int)$data[ 'offline' ],
            'inernalpage'        => (int)$data[ 'inernalpage' ],
            'cssclass'           => trim( $data[ 'cssclass' ] ),
            'teaserimage'        => trim( $data[ 'teaserimage' ] )
        );

        if ( !is_array( HTTP::input( 'documentmeta' ) ) )
        {
            $coredata[ 'published' ] = 1;
            $data[ 'alias' ]         = Library::suggest( $data[ 'title' ] );
            $data[ 'suffix' ]        = '';
        }


        $transData = array();

        if ( !$id )
        {
            $coredata[ 'modifed' ]    = 0;
            $coredata[ 'modifed_by' ] = 0;

            $transData[ 'controller' ] = 'page';
            $transData[ 'action' ]     = 'index';
            $transData[ 'data' ]       = $data;

            $transData[ 'alias' ]  = $data[ 'alias' ];
            $transData[ 'suffix' ] = $data[ 'suffix' ];

            $transData[ 'id' ] = $this->save( $id, $coredata, $transData );
        }
        else
        {
            unset( $coredata[ 'created' ], $coredata[ 'created_by' ], $coredata[ 'hits' ] );

            $transData[ 'controller' ] = 'page';
            $transData[ 'action' ]     = 'index';
            $transData[ 'data' ]       = $data;

            $transData[ 'alias' ]  = $data[ 'alias' ];
            $transData[ 'suffix' ] = $data[ 'suffix' ];

            $transData[ 'id' ] = $this->save( $id, $coredata, $transData );


            $this->load( 'SideCache' );
            $this->SideCache->cleanSideCache( 'page', 'index', $transData[ 'alias' ], $id );
        }

        $this->updateParentIds( $transData[ 'id' ], (int)$data[ 'parentid' ] );

        // save custom fields
        if ( $data[ 'pagetype' ] > 0 )
        {
            $this->saveCustomFieldData( $transData[ 'id' ], $data[ 'pagetype' ], $data );
        }

        // $this->saveContentDraft( $transData[ 'id' ], $data[ 'title' ], trans( 'Seiten' ) );

        return $transData[ 'id' ];
    }

    /**
     *
     * @param integer $id
     * @param array $data
     * @return integer
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
            $data[ 'password' ] = $this->Crypt->encrypt( $data[ 'password' ] );
        }

        $coredata = array(
            //'tags' => (string) $data['tags'],
            'password'   => $data[ 'password' ],
            'parentid'   => (int)$data[ 'parentid' ],
            'pageid'     => PAGEID,
            'cssclass'   => (string)$data[ 'cssclass' ],
            'access'     => (string)$data[ 'access' ], //   'moderators'  => ( string ) $data[ 'moderators' ],
            'cancomment' => (int)$data[ 'cancomment' ], //   'teaserimage' => ( string ) $data[ 'teaserimage' ],
            'language'   => (string)$data[ 'language' ],
            'rollback'   => 0
        );

        if ( !is_array( HTTP::input( 'documentmeta' ) ) )
        {
            $coredata[ 'published' ] = $data[ 'published' ];
        }

        $data[ 'description' ] = (string)$data[ 'cat_description' ];


        $transData[ 'data' ] = $data;
        $transData[ 'tags' ] = (string)$data[ 'tags' ];

        if ( !$id )
        {
            $transData[ 'iscorelang' ] = 1;
            $transData[ 'isnew' ]      = true;

            $transData[ 'controller' ] = 'page';
            $transData[ 'action' ]     = 'index';
            $transData[ 'alias' ]      = (string)$data[ 'alias' ];
            $transData[ 'suffix' ]     = (string)$data[ 'suffix' ];


            $transData[ 'catid' ] = $this->save( $id, $coredata, $transData );
        }
        else
        {
            $transData[ 'controller' ] = 'page';
            $transData[ 'action' ]     = 'index';
            $transData[ 'alias' ]      = (string)$data[ 'alias' ];
            $transData[ 'suffix' ]     = (string)$data[ 'suffix' ];

            $transData[ 'catid' ] = $this->save( $id, $coredata, $transData );
        }


        return $transData[ 'catid' ];
    }

    /**
     * will rollback the temporary translated content
     *
     * @param integer $id
     * @param bool $useCat
     * @return type
     */
    public function rollbackTranslation($id, $useCat = false)
    {

        if ( !$useCat )
        {
            $this->db->query( 'DELETE FROM %tp%pages_trans WHERE `rollback` = 1 AND id = ? AND lang = ?', $id, CONTENT_TRANS );
        }
        else
        {
            $this->db->query( 'DELETE FROM %tp%pages_categories_trans WHERE `rollback` = 1 AND catid = ? AND lang = ?', $id, CONTENT_TRANS );
        }
    }


    /**
     * Copy the original translation to other translation
     *
     * @param int $id
     * @param bool $useCat
     * @return bool|void
     */
    public function copyOriginalTranslation($id, $useCat = false)
    {
        if ( !$useCat )
        {
            $r = $this->db->query( 'SELECT lang FROM %tp%pages_trans WHERE id = ? AND iscorelang = 1', $id )->fetch();
        }
        else
        {
            $r = $this->db->query( 'SELECT lang FROM %tp%pages_categories_trans WHERE catid = ? AND iscorelang = 1', $id )->fetch();
        }

        if ( CONTENT_TRANS == $r[ 'lang' ] )
        {
            return false;
        }

        if ( !$useCat )
        {
            $trans = $this->db->query( 'SELECT t.* FROM %tp%pages_trans AS t WHERE t.id = ? AND t.lang = ?', $id, $r[ 'lang' ] )->fetch();
        }
        else
        {
            $trans = $this->db->query( 'SELECT t.* FROM %tp%pages_categories_trans AS t WHERE t.catid = ? AND t.lang = ?', $id, $r[ 'lang' ] )->fetch();
        }

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

        $this->db->query( 'INSERT INTO %tp%pages_trans (' . implode( ',', $fields ) . ') VALUES(' . implode( ',', $f ) . ')', $values );

        return true;
    }

    /**
     *
     * @param string $text the content string
     * @param integer $created the content timestamp
     * @param integer $textLength default length is 450
     * @param string $imageChain default image chain is "maximum"
     * @return string
     */
    public function preparePageContent($text, $created = 0, $textLength = 450, $imageChain = 'maximum')
    {

        $text = Html::closeUnclosedTags( $text );
        $text = Strings::fixLatin( $text );
        $text = Content::tinyMCECoreTags( $text );

        if ( is_string( $imageChain ) )
        {
            $text = Content::parseImgTags( $text, $created, 'page', 1, $imageChain );
        }

        $text = str_replace( "\r", '', $text );
        // $text = preg_replace( '/<br\s*\/? >/i', ' ', $text );
        // $text = Strings::unhtmlspecialchars($text);
        $text = strip_tags( $text, '<i>,<b>,<strong>,<em>,<img>' );

        return Strings::trimHtml( $text, $textLength, '<i>,<b>,<strong>,<em>,<img>', ' ...' );
    }

    /**
     *
     * @param int $pagetypeId
     * @return array
     */
    public function getCustomFields($pagetypeId)
    {

        $r = $this->db->query( 'SELECT * FROM %tp%pages_types WHERE id = ?', $pagetypeId )->fetch();

        if ( trim( $r[ 'fields' ] ) )
        {
            return $this->db->query( 'SELECT * FROM %tp%pages_fields WHERE fieldid IN(' . $r[ 'fields' ] . ')' )->fetchAll();
        }

        return array();
    }

    /**
     *
     * @param int $contentid
     * @return array
     */
    public function getCustomFieldData($contentid, $pagetypeid = 0)
    {

        $transq1 = $this->buildTransWhere( 'pages_fields_data', 'pd.dataid', 'pdt' );
        $result  = $this->db->query( 'SELECT pd.*,pdt.*,f.*
						FROM %tp%pages_fields_data AS pd
		                LEFT JOIN %tp%pages_fields_data_trans AS pdt ON(pdt.dataid=pd.dataid)
		                LEFT JOIN %tp%pages_fields AS f ON(f.fieldid = pd.fieldid)
		                WHERE pd.contentid = ? AND pd.pagetypeid = ? AND ' . $transq1, $contentid, $pagetypeid )->fetchAll();


        $cache = array();
        foreach ( $result as $r )
        {
            $cache[ $r[ 'fieldname' ] ] = $r;
        }

        $result = null;

        return $cache;
    }

    /**
     *
     * @param int $contentid
     * @param int $pagetypeId
     * @param array $data
     */
    public function saveCustomFieldData($contentid, $pagetypeId, $data)
    {

        $fields = $this->getCustomFields( $pagetypeId );
        // $fields2 = $fields;


        foreach ( $fields as $idx => &$field )
        {

            $result = $this->db->query( 'SELECT * FROM %tp%pages_fields_data WHERE contentid = ? AND pagetypeid = ? AND fieldid = ? LIMIT 1', $contentid, $pagetypeId, $field[ 'fieldid' ] )->fetch();


            if ( $result[ 'dataid' ] )
            {
                $trans = $this->db->query( 'SELECT * FROM %tp%pages_fields_data_trans WHERE dataid = ? AND lang = ? LIMIT 1', $result[ 'dataid' ], CONTENT_TRANS )->fetch();


                // delete old data
                if ( $trans[ 'iscorelang' ] )
                {
                    $this->db->query( 'DELETE FROM %tp%pages_fields_data_trans WHERE dataid = ? AND lang = ? AND iscorelang = 1', $result[ 'dataid' ], CONTENT_TRANS );
                }
                else
                {
                    $this->db->query( 'DELETE FROM %tp%pages_fields_data_trans WHERE dataid = ? AND lang = ? AND iscorelang = 0', $result[ 'dataid' ], CONTENT_TRANS );
                }
                $this->db->begin();
                $value = $data[ $field[ 'fieldname' ] ];
                $this->db->query( 'REPLACE INTO %tp%pages_fields_data_trans (dataid,value,lang,iscorelang) VALUES(?,?,?,?)', $result[ 'dataid' ], $value, CONTENT_TRANS, (int)$trans[ 'iscorelang' ] );
                $this->db->commit();
            }
            else
            {
                $this->db->begin();
                $this->db->query( 'INSERT INTO %tp%pages_fields_data (fieldid,contentid,pagetypeid) VALUES(?,?,?)', $field[ 'fieldid' ], $contentid, $pagetypeId );
                $dataid = $this->db->insert_id();

                $value = $data[ $field[ 'fieldname' ] ];
                $this->db->query( 'INSERT INTO %tp%pages_fields_data_trans (dataid,value,lang,iscorelang) VALUES(?,?,?,?)', $dataid, $value, CONTENT_TRANS, 1 );

                $this->db->commit();
            }
        }


    }

    /**
     *
     * @param integer $id
     */
    public function updateHits($id)
    {
        $this->db->begin();
        $this->db->query( 'UPDATE %tp%pages SET hits = hits+1 WHERE id = ?', $id );
        $this->db->commit();
    }

    /**
     *
     * @return string
     */
    public function getRelpages()
    {

        $basedonId = $this->getParam( 'id' );
        $template  = $this->getParam( 'template', 'rel_items' );
        $limit     = $this->getParam( 'limit', 5 );
        $images    = $this->getParam( 'images', false );
        $catId     = 0;

        $baselang = $basetitle = null;


        $where = '';
        if ( $this->getApplication()->getMode() === Application::FRONTEND_MODE && !User::isAdmin() && !IS_SEEMODE )
        {
            $now   = time();
            $where = ' AND p.locked = 0 AND p.published > 0 AND p.published=1 AND p.draft = 0 AND
					((p.publishoff > 0 AND p.publishoff>=' . $now . ') OR p.publishoff=0) AND
					((p.publishon>0 AND p.publishon <= ' . $now . ') OR p.publishon = 0 AND p.created <= ' . $now . ')
					AND p.usergroups IN(0,' . User::getGroupId() . ') ';
        }

        if ( !$basedonId )
        {
            return;
        }
        else
        {
            $this->load( 'Document' );
            $basetitle = $this->Document->get( 'content' );
            $baselang  = $this->Document->get( 'lang' );

            $where .= ' AND p.id != ' . (int)$basedonId;
        }

        $transq1 = $this->buildTransWhere( 'pages', 'p.id', 'pt' );

        $sql = 'SELECT DISTINCT p.id, p.created, p.created_by, pt.alias, pt.suffix, pt.title, pt.content,
                    u.username AS author,
                    u1.username AS modifyauthor
                    FROM %tp%pages AS p
                    LEFT JOIN %tp%pages_trans AS nt ON(pt.id=p.id)               
                    LEFT JOIN %tp%users AS u ON (u.userid = p.created_by)
                    LEFT JOIN %tp%users AS u1 ON (u1.userid = p.modifed_by)
                    WHERE p.pageid = ? AND ' . $transq1 . $where . '
                    GROUP BY p.id
                    ORDER BY RAND()
                    LIMIT ' . $limit;
        $rs  = $this->db->query( $sql, PAGEID )->fetchAll();

        $data = array();
        foreach ( $rs as $r )
        {
            $r[ 'rewrite' ] = ( $r[ 'alias' ] ? $r[ 'alias' ] . '.' . $r[ 'suffix' ] : $r[ 'id' ] );
            $r[ 'url' ]     = 'page/' . $r[ 'rewrite' ];
            $r[ 'content' ] = preg_replace( '#src=(["\']).*(pages/.+)\1#i', 'src=$1$2$1', $r[ 'content' ] );

            if ( $images )
            {
                if ( ( $img = Content::extractFirstImage( $r[ 'content' ] ) ) )
                {
                    $r[ 'image' ]   = $img[ 'attributes' ];
                    $r[ 'content' ] = str_replace( $img[ 'full_tag' ], '', $r[ 'content' ] );
                }
            }

            $r[ 'text' ] = $this->preparePageContent( $r[ 'content' ], $r[ 'created' ], 300 );

            if ( $assign != '' )
            {
                $data[ ] = $r;
            }
            else
            {
                $data[ 'related_items' ][ ] = $r;
            }
        }
        unset( $rs );
        $this->db->free();

        $this->load( 'Template' );
        $tpl             = new Template();
        $tpl->isProvider = true;
        $data            = array_merge( $this->Template->getTemplateData(), $data );

        return $tpl->process( 'cms/' . $template, $data, null );
    }

    private function buildTreeArray(&$cats, $pages)
    {

        foreach ( $cats as &$r )
        {
            foreach ( $pages as $rs )
            {
                if ( $rs[ 'catid' ] && $rs[ 'catid' ] === $r[ 'catid' ] )
                {
                    $r[ '_children' ][ ] = $rs;
                }
            }
        }
    }

    private function addPagesToChildCats(&$cats, &$pages)
    {
        foreach ( $cats as &$r )
        {
            if ( isset( $r[ '_children' ] ) )
            {
                $this->addPagesToChildCats( $r[ '_children' ], $pages );
            }
            else
            {
                foreach ( $pages as $rs )
                {
                    if ( $rs[ 'catid' ] && $rs[ 'catid' ] == $r[ 'catid' ] )
                    {
                        $r[ '_children' ][ ] = $rs;
                    }
                }
            }
        }

        return $cats;
    }

    private function getChildPagesOfPage($pageid, $pages)
    {
        $ret = null;

        foreach ( $pages as $r )
        {
            if ( $pageid === $r[ 'parentid' ] )
            {
                if ( $r[ 'parentids' ] !== '0' )
                {
                    explode( $r[ 'parentids' ] );
                }
            }
        }


    }


    private function getChildPagesOfCat($catid, $pages)
    {
        foreach ( $pages as $r )
        {
            if ( $catid == $r[ 'catid' ] )
            {

                $t = Tree::mapTree( $pages, 'id', 'parentid' );


            }
        }


    }

    private $_pagetree = array();
    private $_cattree = array();
    private $_cache = array();
    private $_pages = array();


    private function addPagesToCatTree()
    {
        foreach ( $this->_cattree as &$r )
        {
            if ( !$r[ 'parentid' ] )
            {
                if ( sizeof( $r[ '_children' ] ) )
                {
                    $this->addPagesToSubCatTree( $r[ '_children' ] );
                }
                $this->_cache[ ] = $r;
            }
        }
    }

    private function addPagesToSubCatTree(&$subtree)
    {
        if ( is_array( $subtree ) )
        {
            foreach ( $subtree as &$r )
            {

                $childs = $this->getPagesForCat( $r[ 'catid' ] );
                if ( sizeof( $childs ) )
                {

                    getSubPagesOfPage( $childs );

                    $r[ '_children' ] = $childs;
                }

                $this->addPagesToSubCatTree( $r[ 'catid' ] );

            }
        }
    }


    private function getPagesForCat($catid = 0)
    {
        $tmp = null;
        foreach ( $this->_pages as $r )
        {
            if ( $r[ 'catid' ] == $catid )
            {
                $tmp[ ] = $r;
            }
        }

        return $tmp;
    }

    private function getSubPagesOfPage($children, $pageid = 0)
    {
        $tmp = null;
        foreach ( $this->_pages as $r )
        {
            if ( $r[ 'parentid' ] == $pageid )
            {
                $tmp[ ] = $r;
            }
        }

        return $tmp;
    }


    private function getTreeList($pageid = 0)
    {
        if ( is_array( $this->_flatlist[ $pageid ] ) )
        {
            foreach ( $this->_flatlist[ $pageid ] as $r )
            {
                $this->_cache[ $r[ 'id' ] ] = $r;

                if ( isset( $this->_flatlist[ $r[ 'id' ] ] ) )
                {
                    $this->getTreeList( $r[ 'id' ] );
                }
            }
        }
    }

    /**
     * @param int $parentid
     * @param       $cats
     * @param array $arr
     * @return array
     */
    private function getAllParentCategories($parentid = 0, $cats, $arr = array())
    {

        foreach ( $cats as $r )
        {
            if ( $r[ 'id' ] == $parentid )
            {
                $arr[ ] = $r;
                $arr    = $this->getAllParentCategories( $r[ 'parentid' ], $cats, $arr );

                #$arr = array_reverse($arr);
            }
        }

        return $arr;
    }

    /**
     * @param int $id
     * @param       $cats
     * @param array $arr
     * @return array
     */
    private function getAllChildCategories($id = 0, $cats, $arr = array())
    {

        foreach ( $cats as $r )
        {
            if ( $r[ 'parentid' ] == $id )
            {
                $url = 'page/';
                if ( $r[ 'alias' ] )
                {
                    $url .= $r[ 'alias' ];
                    $url .= '.' . ( $r[ 'suffix' ] ? $r[ 'suffix' ] :
                            Settings::get( 'mod_rewrite_suffix', 'html' ) );
                }
                else
                {
                    $url .= $r[ 'id' ] . '/1';
                }

                $r[ 'url' ] = $url;

                $this->_cache[ ] = $r[ 'id' ];


                $childs = array();
                $childs = $this->getAllChildCategories( $r[ 'id' ], $cats, $childs );
                if ( sizeof( $childs ) ) $r[ '_children' ] = $childs;

                $arr[ ] = $r;
            }
        }

        return $arr;
    }

    /**
     *
     * @param boolean $isGoogleMap default is false
     * @return string/array
     */
    public function getSitemap($isGoogleMap = false)
    {

        $cats = $this->getCats( true );


        if ( $this->getApplication()->getMode() === Application::FRONTEND_MODE && !User::isAdmin() && !IS_SEEMODE )
        {
            $publish = 'p.published > 0 AND p.locked = 0 AND p.draft = 0 AND pt.draft = 0 AND
			((p.publishoff>0 AND p.publishoff>=' . TIMESTAMP . ') OR p.publishoff = 0) AND
			((p.publishon>0 AND p.publishon <= ' . TIMESTAMP . ') OR p.publishon = 0 AND p.created <= ' . TIMESTAMP . ' ) AND ';


            $groupQuery = 'p.usergroups IN(0,' . User::getGroupId() . ') AND ';
        }

        $transq1 = $this->buildTransWhere( 'pages', 'p.id', 'pt' );


        $tmp = array();


        //   $cats = Tree::mapTree($cats, 'id', 'parentid');


        $sql     = 'SELECT p.id, p.catid, p.parentid, p.parentids, pt.alias, pt.suffix, pt.title, p.created, p.modifed, p.isindexpage
                            FROM %tp%pages AS p 
                            LEFT JOIN %tp%pages_trans AS pt ON (pt.id=p.id)
                            WHERE ' . $groupQuery . $publish . '
                            p.pageid = ? AND p.inernalpage != 1 AND ' . $transq1 . '
                        GROUP BY p.id 
                        ORDER BY pt.title ASC, p.parentid DESC
                        LIMIT 500';
        $_catdat = $this->db->query( $sql, PAGEID )->fetchAll();


        $frontpage         = Settings::get( 'frontpage', '' );
        $frontpageNoDomain = preg_replace( '#^' . preg_quote( Settings::get( 'portalurl' ), '#' ) . '/?#is', '', $frontpage );

        foreach ( $_catdat as $idx => &$rs )
        {

                $url = 'page/';
                if ( $rs[ 'alias' ] )
                {
                    $url .= $rs[ 'alias' ];
                    $url .= '.' . ( $rs[ 'suffix' ] ? $rs[ 'suffix' ] :
                            Settings::get( 'mod_rewrite_suffix', 'html' ) );
                }
                else
                {
                    $url .= $rs[ 'id' ] . '/1';
                }

                $rs[ 'url' ] = $url;



            if ( $frontpage )
            {
                if ( preg_match( '#^' . preg_quote( $frontpageNoDomain, '#' ) . '.*#is', $rs[ 'url' ] ) )
                {
                    unset( $_catdat[ $idx ] );
                }
            }
        }

        $this->_pages    = $_catdat;
        $this->_pagetree = Tree::mapTree( $_catdat, 'id', 'parentid' );

        foreach ( $cats as $idx => &$rs )
        {
            // if has index page the add url to page
            $indexpageFound = $this->hasIndexPage( $_catdat, $rs[ 'catid' ] );
            if ( is_array( $indexpageFound ) )
            {
                $url = 'page/';
                if ( $indexpageFound[ 'alias' ] )
                {
                    $url .= $indexpageFound[ 'alias' ];
                    $url .= '.' . ( $indexpageFound[ 'suffix' ] ? $indexpageFound[ 'suffix' ] :
                            Settings::get( 'mod_rewrite_suffix', 'html' ) );
                }
                else
                {
                    $url .= $indexpageFound[ 'id' ] . '/1';
                }

                $rs[ 'url' ] = $url;

                if ( $frontpage )
                {
                    if ( preg_match( '#^' . preg_quote( $frontpageNoDomain, '#' ) . '.*#is', $rs[ 'url' ] ) )
                    {
                        continue;
                    }
                }

            }
            else
            {

                #unset( $rs[ 'url' ] );
            }

            if ( $rs[ 'parentid' ] )
            {

                foreach ( $this->_pagetree as $r )
                {
                    if ( $r[ 'catid' ] == $rs[ 'catid' ] )
                    {
                        $rs[ '_children' ][ ] = $r;
                    }
                }

                if ( count( $rs[ '_children' ] ) )
                {
                    $url = 'page/';
                    if ( $rs[ 'alias' ] )
                    {
                        $url .= $rs[ 'alias' ];
                        $url .= '.' . ( $rs[ 'suffix' ] ? $rs[ 'suffix' ] :
                                Settings::get( 'mod_rewrite_suffix', 'html' ) );
                    }
                    else
                    {
                        $url .= $rs[ 'id' ] . '/1';
                    }

                    $rs[ 'url' ] = $url;
                }
                else
                {
                    if ( !$indexpageFound ) unset( $cats[ $idx ] );
                }
            }
            else
            {
                foreach ( $this->_pages as $r )
                {
                    if ( ($r[ 'catid' ] == $rs[ 'catid' ] && !$r[ 'parentid' ])  )
                    {
                        $url = 'page/';
                        if ( $r[ 'alias' ] )
                        {
                            $url .= $r[ 'alias' ];
                            $url .= '.' . ( $r[ 'suffix' ] ? $r[ 'suffix' ] :
                                    Settings::get( 'mod_rewrite_suffix', 'html' ) );
                        }
                        else
                        {
                            $url .= $r[ 'id' ] . '/1';
                        }

                        $r[ 'url' ] = $url;

                        if ( $frontpage )
                        {
                            if ( preg_match( '#^' . preg_quote( $frontpageNoDomain, '#' ) . '.*#is', $r[ 'url' ] ) )
                            {
                                continue;
                            }
                        }

                        $rs[ '_children' ][ ] = $r;
                    }
                    elseif ($r[ 'catid' ] == $rs[ 'catid' ] && $r[ 'parentid' ]) {
                        $url = 'page/';
                        if ( $r[ 'alias' ] )
                        {
                            $url .= $r[ 'alias' ];
                            $url .= '.' . ( $r[ 'suffix' ] ? $r[ 'suffix' ] :
                                    Settings::get( 'mod_rewrite_suffix', 'html' ) );
                        }
                        else
                        {
                            $url .= $r[ 'id' ] . '/1';
                        }

                        $r[ 'url' ] = $url;

                        if ( $frontpage )
                        {
                            if ( preg_match( '#^' . preg_quote( $frontpageNoDomain, '#' ) . '.*#is', $r[ 'url' ] ) )
                            {
                                continue;
                            }
                        }

                        $rs[ '_children' ][ ] = $r;
                    }
                }
            }
        }

        $this->_cattree = Tree::mapTree( $cats, 'catid', 'parentid' );
        foreach ( $this->_pages as $r )
        {
            if ( !$r[ 'catid' ] && !$r[ 'parentid' ] )
            {
                $this->_cattree[ ] = $r;
            }
        }

        $data[ 'sitemap' ][ 'page_cats' ] = $this->_pagetree;
        $data[ 'sitemap' ][ 'pages' ]     = $this->_cattree;

        $this->load( 'Template' );
        $tpl             = new Template();
        $tpl->isProvider = true;
        $data            = array_merge( $this->Template->getTemplateData(), $data );

        return $this->Template->process( 'pages/sitemaptree', $data, null );

    }

    private function hasIndexPage($d, $catid)
    {
        $indexpageFound = false;

        foreach ( $d as &$_rs )
        {
            if ( isset( $_rs[ 'catid' ] ) && isset( $_rs[ 'isindexpage' ] ) )
            {
                if ( $_rs[ 'catid' ] == $catid && $_rs[ 'isindexpage' ] && !$indexpageFound )
                {
                    $indexpageFound = $_rs;
                    break;
                }
            }
        }

        return $indexpageFound;
    }


    private function findPagesForCatID($catid = 0, $pages)
    {
        $tmp = null;

        foreach ( $pages as $r )
        {
            if ( $r[ 'catid' ] === $catid )
            {
                $tmp[ ] = $r;
            }

            if ( !$r[ 'parentid' ] )
            {
                $tmp[ ] = $this->findSubPagesForPage( $r[ 'id' ], $pages, $catid );
            }
        }

        // array_unique( $tmp );

        return $tmp;
    }


    private function findSubPagesForPage($pageid = 0, $pages, $catid)
    {

        $tmp = array();
        foreach ( $pages as $r )
        {
            if ( isset( $r[ 'parentids' ] ) )
            {
                $parentlist = explode( ',', $r[ 'parentids' ] );
                foreach ( $parentlist as &$i )
                {
                    if ( $i )
                    {
                        $i = 'p' . $i;
                    }
                }

                if ( $r[ 'parentid' ] === $pageid || in_array( $pageid, $parentlist ) )
                {
                    $r[ 'catid' ] = $catid;
                    $tmp[ ]       = $r;
                    if ( $r[ 'parentid' ] )
                    {
                        $tmp[ ] = $this->findSubPagesForPage( $r[ 'id' ], $pages, $r[ 'catid' ] );
                    }
                }
            }
        }
        $tmp = array_unique( $tmp );

        return $tmp;
    }

    /**
     *
     * @return integer
     */
    public function getSearchIndexDataCount()
    {

        $transq1 = $this->buildTransWhere( 'pages', 'n.id', 'nt' );

        $this->db->begin();

        $this->db->query( 'REPLACE INTO %tp%indexer (contentid, title, content, content_time, groups, alias, suffix, modul, lang)
                        SELECT n.id AS contentid, nt.title, nt.content, n.created AS time, n.usergroups AS groups,
                        nt.alias,
                        IF(nt.suffix != \'\', nt.suffix, ? ) AS suffix, \'page\', nt.lang
                FROM %tp%pages AS n
                LEFT JOIN %tp%pages_trans AS nt ON(nt.id=n.id)
                WHERE n.locked = 0 AND n.inernalpage != 1 AND n.published>0 AND n.draft = 0 AND n.pageid = ?
                AND ((n.publishoff > 0 AND n.publishoff>=?) OR n.publishoff=0)
                AND ((n.publishon>0 AND n.publishon <= ?) OR n.publishon = 0 AND n.created <= ?)
                AND ' . $transq1, Settings::get( 'mod_rewrite_suffix', 'html' ), PAGEID, TIMESTAMP, TIMESTAMP, TIMESTAMP );

        $this->db->commit();

        $r = $this->db->query( "SELECT COUNT(n.id) AS total FROM %tp%pages AS n
                LEFT JOIN %tp%pages_trans AS nt ON (nt.id=n.id)
                WHERE n.locked = 0 AND n.inernalpage != 1 AND n.published>0 AND n.draft = 0 AND n.pageid = ?
                AND ((n.publishoff > 0 AND n.publishoff>=?) OR n.publishoff=0)
                AND ((n.publishon>0 AND n.publishon <= ?) OR n.publishon = 0 AND n.created <= ?)
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

        $transq1 = $this->buildTransWhere( 'pages', 'n.id', 'nt' );
        $sql     = "SELECT n.id AS contentid, nt.title, nt.content, n.created AS time, n.usergroups AS groups, nt.alias, IF(nt.suffix != \'\', nt.suffix, ? ) AS suffix
                FROM %tp%pages AS n
                LEFT JOIN %tp%pages_trans AS nt ON(nt.id=n.id)
                WHERE n.locked = 0 AND n.inernalpage != 1 AND n.published>0 AND n.draft = 0 AND n.pageid = ?
                AND ((n.publishoff > 0 AND n.publishoff>=?) OR n.publishoff=0)
                AND ((n.publishon>0 AND n.publishon <= ?) OR n.publishon = 0 AND n.created <= ?)
                AND " . $transq1 . " LIMIT " . $from . "," . $limit;

        return $this->db->query( $sql, Settings::get( 'mod_rewrite_suffix', 'html' ), PAGEID, TIMESTAMP, TIMESTAMP, TIMESTAMP )->fetchAll();
    }

}

?>