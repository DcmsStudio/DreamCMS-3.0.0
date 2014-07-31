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
 * @package     DreamCMS
 * @version     3.0.0 Beta
 * @category    Framework
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Page.php
 *
 */
class Page extends Loader
{

    /**
     * @var string
     */
    protected static $_instance = null;
    public static $defaultSuffix = 'html';


    /**
     * @return Page|string
     */
    public static function getInstance() {
        if ( self::$_instance === null ) {
            self::$_instance = new Page();
        }

        return self::$_instance;
    }




    /**
     * Import some default libraries
     */
    public function __construct()
    {
        parent::__construct();

        $this->load( 'Document' );
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * @param int    $code
     * @param string $message
     */
    public function error( $code = 404, $message = 'It is an error in processing occurred' )
    {

        User::disableUserLocationUpdate();

        if ( IS_AJAX )
        {
            echo Library::sendJson( false, $message );
            exit();
        }

        Library::addNavi( trans( 'Allgemeiner Fehler' ) );


        if ( $this->getApplication()->getMode() === Application::FRONTEND_MODE )
        {
            $this->Document->set( 'layout', 'error' );
        }
        else
        {
            $this->Document->set( 'layout', 'error' );
        }

        $this->Document->set( 'isErrorLayout', true );


        $data = array();
        if ( is_array( $message ) )
        {
            $data = $message;
        }
        else
        {
            $data[ 'message' ] = $message;
        }

        $data[ 'errorcode' ] = (string) $code;
        $template = 'error';

        if ( $code == 404 )
        {
            $template .= '404';
            Hook::run( 'onBeforeSend404', $data ); // {CONTEXT: frontend, DESC: Dieses Ereignis wird ausgelöst, bevor Error 404 gesendet wird (Frontend Controller Error).}
        }
        else if ( $code == 403 )
        {
            $template .= '403';
            Hook::run( 'onBeforeSend403', $data ); // {CONTEXT: frontend, DESC: Dieses Ereignis wird ausgelöst, bevor Error 403 gesendet wird (Frontend Controller Error).}
        }
        else if ( $code === 500 )
        {
            Hook::run( 'onBeforeSend500', $data ); // {CONTEXT: frontend, DESC: Dieses Ereignis wird ausgelöst, bevor Error 500 gesendet wird (Frontend Controller Error).}
        }
        else if ( $code === 503 )
        {
            Hook::run( 'onBeforeSend503', $data ); // {CONTEXT: frontend, DESC: Dieses Ereignis wird ausgelöst, bevor Error 503 gesendet wird (Frontend Controller Error).}
        }

        $this->load( 'Document' );
        $this->load( 'Template' );


        User::disableUserLocationUpdate();
        $this->Document->disableSiteCaching();
        $html = $this->Template->process( $template, $data, null );

        $html = preg_replace('#/\*\s*\n*\s*/\*#', '/*', $html);
        $html = preg_replace('#s*\*/\s*\n*\s*\*/#', '*/', $html);

        $this->load( 'Provider' );

        if ( $this->Provider->hasProviders( $html ) )
        {
            // render providers
            $html = $this->Provider->renderProviderTags( $html, 'post' );
        }


        $html .= Debug::write();

        #
        $this->load( 'Output' );
        $this->Output->appendOutput( $html )->setStatus( $code )->sendOutput();
    }

    /**
     * @param string $errorMsg
     */
    public function sendError( $errorMsg = 'It is an error in processing occurred' )
    {

        User::disableUserLocationUpdate();

        if ( IS_AJAX )
        {
            echo Library::sendJson( false, $errorMsg );
            exit();
        }


        Library::addNavi( trans( 'Allgemeiner Fehler' ) );


        if ( $this->getApplication()->getMode() === Application::FRONTEND_MODE )
        {
            $this->Document->set( 'layout', 'error' );
        }
        else
        {
            $this->Document->set( 'layout', 'error' );
        }

        $this->Document->set( 'isErrorLayout', true );

        $data = array();
        if ( is_array( $errorMsg ) )
        {
            $data = $errorMsg;
        }
        else
        {
            $data[ 'message' ] = $errorMsg;
        }

        $this->load( 'Document' );
        $this->Document->disableSiteCaching();

        Hook::run( 'onBeforeSendError', $data ); // {CONTEXT: frontend, DESC: Dieses Ereignis wird ausgelöst, bevor die Error Seite gesendet wird (Frontend Controller Error).}

        User::disableUserLocationUpdate();
        $this->load( 'Template' );
        $this->Template->process( 'error', $data, true );


        Hook::run( 'onAfterSendError' ); // {CONTEXT: frontend, DESC: Dieses Ereignis wird ausgelöst, nachdem die Error Seite gesendet wurde (Frontend Controller Error).}


        exit;
    }

    /**
     * @param string $errorMsg
     */
    public function sendAccessError( $errorMsg = 'You don\'t have permission to access the requested document' )
    {
        if ( IS_AJAX )
        {
            echo Library::sendJson( false, $errorMsg );
            exit();
        }

        $this->error( 403, $errorMsg );
    }

    /**
     * @param string $errorMsg
     */
    public function send404( $errorMsg = 'The requested resource does not exist here.' )
    {
        if ( IS_AJAX )
        {
            echo Library::sendJson( false, $errorMsg );
            exit();
        }

        $this->error( 404, $errorMsg );
        exit;

        $data = array();
        $data[ 'message' ] = $errorMsg;

        $this->load( 'Document' );
        $this->Document->set( 'isErrorLayout', true );

        if ( $this->getApplication()->getMode() === Application::FRONTEND_MODE )
        {
            $this->load( 'Site' );
            $this->Site->disableSiteCaching();
            $this->Document->set( 'layout', 'error' );
        }
        else
        {
            $this->Document->set( 'layout', 'error' );
        }

        Hook::run( 'onBeforeSend404', $data ); // {CONTEXT: frontend, DESC: Dieses Ereignis wird ausgelöst, bevor Error 404 gesendet wird (Frontend Controller Error).}

        header( "HTTP/1.0 404 Not Found" );

        echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>HTTP 404 Not Found</title>
</head>
<body>
    <h1>HTTP 404</h1>
    <p>' . $message . '</p>
</body>
</html>';

        Hook::run( 'onAfterSend404' ); // {CONTEXT: frontend, DESC: Dieses Ereignis wird ausgelöst, nach Error 404 gesendet wird (Frontend Controller Error).}
        die();
    }

    /**
     *
     * @return array
     */
    public static function getActivePage()
    {
        $activepage = self::$_applicationInstance->Site->getSiteData( 'contentdata' );

        if ( !is_null( $activepage ) && is_array( $activepage ) )
        {
            return $activepage;
        }

        return array();
    }

    /**
     *
     * @param bool|string $link
     * @return string
     */
    public static function FromatLink( $link = false )
    {
        if ( !$link )
        {
            return '';
        }


        if ( substr( $link, 0, 1 ) == '/' )
        {
            $link = Settings::get( 'portalurl' ) . $link;
        }

        return $link;
    }

    /**
     *
     * @param integer $node
     * @return array
     */
    public static function getLeftRight( $node )
    {
        $db = Database::getInstance();
        return $db->query( 'SELECT lft AS `left`, rgt AS `right` FROM %tp%page WHERE id = ?', $node )->fetch();
    }

    /**
     *
     * @param integer $node
     * @param string $order
     * @return array
     */
    public static function getChildrenNodes( $node, $order = 'ordering' )
    {
        $db = Database::getInstance();
        $level = count( self::getAncestors( $node ) ) + 1;

        if ( !in_array( $order, array(
                    'ordering',
                    'title' ) ) )
        {
            $order = 'ordering';
        }

        /*
          $pages = self::$db->query('SELECT `id`, `name`, is_folder, `parent`, `published`, link, `type` AS `orginal_type`,
          IF(`parent`=1, \'site\', `type`) AS `type`,
          ' . $level . ' AS level
          FROM %tp%menu WHERE parent = ' . $node . ' ORDER BY parent ASC,ordering ASC')->fetchAll();

          $pages = $db->query('SELECT `id`, `parentid`, `title` AS name, `published`, is_folder, link, `type` AS `orginal_type`,
          IF(`parentid` = 1, \'site\', `type`) AS `type`,
          ' . $level . ' AS level
          FROM %tp%page WHERE parentid = ' . $node . ' AND breadcrumb = 1 ORDER BY lft,' . $order . ' ASC')->fetchAll();

         */


        $transq1 = Registry::getObject( 'Application' )->buildTransWhere( 'page', 'p.id', 'pt' );

        $sql = 'SELECT p.id, p.published, p.parentid, p.is_folder, p.link,pt.title AS name, p.`type` AS `orginal_type`,
                IF(`parentid` = 1, \'site\', `type`) AS `type`, 
                ' . $level . ' AS level
                FROM %tp%page AS p
                LEFT JOIN %tp%page_trans AS pt ON (pt.id=p.id)
                WHERE parentid = ? AND p.pageid = ? AND breadcrumb = 1 AND ' . $transq1 . ' ORDER BY p.lft, p.' . $order . ' ASC';

        $pages = $db->query( $sql, $node, PAGEID )->fetchAll();


        foreach ( $pages as $key => $page )
        {
            switch ( $page[ 'type' ] )
            {
                case 'static':
                    $row_type = trans( 'Statische Seite' );
                    break;

                case 'news_category':
                    $row_type = trans( 'News Kategorie' );
                    break;

                case 'article_category':
                    $row_type = trans( 'Artikel Kategorie' );
                    break;

                case 'link':
                    $row_type = trans( 'Url' );
                    break;

                case 'main':
                    $row_type = trans( 'Main' );
                    break;

                case 'modules':
                    $row_type = trans( 'Modul' );
                    break;

                case 'link_intern':
                    $row_type = trans( 'System Link' );
                    break;


                case 'plugin':
                    $row_type = trans( 'Plugin' );
                    break;
            }

            $pages[ $key ][ 'typename' ] = $row_type;

            if ( $page[ 'published' ] == 0 )
            {
                $pages[ $key ][ 'extraClass' ] = 'tree-node-unpublished';
            }
        }
        return $pages;
    }

    /**
     *
     * @param integer $node
     * @return array
     */
    public static function getAncestors( $node )
    {
        $db = Database::getInstance();

        // retrieve the left and right value of the $node
        $rs = $db->query( 'SELECT lft, rgt FROM %tp%page WHERE id = ' . $node );
        if ( $rs->rowCount() == 0 )
        {
            //Error::raise(trans('There is no node with id ' . $node . '.'));
            return array();
        }
        $row = $rs->fetch();
        $rs = $db->query( 'SELECT id FROM %tp%page WHERE lft < ' . $row[ 'lft' ] . ' AND rgt > ' . $row[ 'rgt' ] . ' AND breadcrumb = 1 ORDER BY lft ASC' );
        return $rs->fetchAll();
    }

    /**
     *
     * @param integer $node
     * @return array
     */
    public static function getNodeInfo( $node = 0 )
    {
        if ( !$node )
        {
            return false;
        }
        return Database::getInstance()->query( 'SELECT * FROM %tp%page WHERE id = ? ', $node )->fetch();
    }

    /**
     *
     *
     */
    public static function updateLevels()
    {
        $db = Database::getInstance();

        $cache = $db->query( 'SELECT n.`id`, COUNT(n.`id`) AS level
             FROM %tp%page AS n, %tp%page AS p
             WHERE n.`type` != \'root\' AND n.pageid = ? AND n.lft BETWEEN p.lft AND p.rgt
             GROUP BY n.`id`
             ORDER BY n.`lft`', PAGEID )->fetchAll();

        foreach ( $cache as $r )
        {
            $db->query( 'UPDATE %tp%page SET level = ? WHERE id = ?', $r[ 'level' ], $r[ 'id' ] );
        }
    }

    /**
     * returns an array of ALL parent ids for a given id($id)
     * @staticvar type $deep
     * @param integer $id
     * @param array $idarray
     * @return array
     */
    public static function getAllParentIds( $id = 0, $idarray = array() )
    {

        static $deep;
        if ( !is_array( $idarray ) )
        {
            $idarray = array();
        }

        if ( !(int)$id  )
        {
            return $idarray;
        }


        $db = Database::getInstance();

        $sql = 'SELECT id, parent_page FROM %tp%doc_pages_settings WHERE id=' . (int)$id ;
        $rs = $db->query_first( $sql );

        if ( !isset( $rs[ 'id' ] ) || empty( $rs[ 'parent_page' ] ) )
        {
            return $idarray;
        }

        $idarray[] = $rs[ 'parent_page' ];
        $idarray = self::getAllParentIds( $rs[ 'parent_page' ], $idarray );

        return $idarray;
    }

    /**
     *
     * @param integer $node
     * @return array
     */
    public static function getTree( $node )
    {
        $db = Database::getInstance();

        // retrieve the left and right value of the $node
        $rs = $db->query( 'SELECT lft, rgt FROM %tp%page WHERE id = ?', $node );
        if ( $rs->rowCount() == 0 )
        {
            Error::raise( trans( 'There is no node with id ' . $node . '.' ) );
        }
        $row = $rs->fetch();

        $output = array();

        // start with an empty $right stack
        $right = array();

        $transq1 = Registry::getObject( 'Application' )->buildTransWhere( 'page', 'p.id', 'pt' );


        // now, retrieve all descendants of the $node
        $rs = $db->query( 'SELECT p.rgt, p.id, p.type, pt.title FROM %tp%page AS p 
                          LEFT JOIN %tp%page_trans AS pt ON (pt.id=p.id)
                          WHERE p.lft BETWEEN ? AND ? 
                          AND ' . $transq1 . '
                          ORDER BY p.lft ASC', $row[ 'lft' ], $row[ 'rgt' ] );

        // parse each row
        while ( $row = $rs->fetch() )
        {
            // only check stack if there is one
            if ( count( $right ) > 0 )
            {
                // check if we should remove a node from the stack
                while ( $right[ count( $right ) - 1 ] < $row[ 'rgt' ] )
                {
                    array_pop( $right );
                }
            }

            // store the node data
            $output[] = array(
                'id'    => $row[ 'id' ],
                'title' => $row[ 'title' ],
                'type'  => $row[ 'type' ],
                'level' => count( $right )
            );

            // add this node to the stack
            $right[] = $row[ 'rgt' ];
        }

        return $output;
    }

    /**
     *
     * @param integer $node
     * @return array
     */
    public static function openTo( $node )
    {

        $db = Database::getInstance();
        $rs = $db->query( 'SELECT COUNT(*) AS counted FROM %tp%page WHERE id = ?', $node );
        $row = $rs->fetch();
        if ( $row[ 'counted' ] == 0 )
        {
            return array();
        }

        $ancestors = self::getAncestors( $node );
        array_shift( $ancestors ); // don't need the root level

        $output = array();

        foreach ( $ancestors as $ancestor )
        {
            $children = self::getChildrenNodes( $ancestor[ 'id' ] );
            $output[ $ancestor[ 'id' ] ] = $children;
        }

        return $output;
    }

}
