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
 * @copyright    2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Loader.php
 *
 */
abstract class Loader
{

    /**
     *
     * @var type
     */
    protected static $_loadedInstances;

    /**
     *
     * @var Application
     */
    protected static $_applicationInstance;

    /**
     *
     * @var Controller
     */
    protected static $_controllerInstance;

    /**
     *
     * @var boolean
     */
    protected static $addDraftButton = false;

    /**
     *
     * @var Database_Adapter
     */
    public $db = null;

    /**
     *
     * @var Env
     */
    public $Env;

    /**
     *
     * @var Input
     */
    public $Input;

    /**
     *
     * @var Template
     */
    public $Template;

    /**
     *
     * @var Output
     */
    public $Output;

    /**
     *
     * @var Document
     */
    public $Document;

    /**
     *
     * @var GUI
     */
    public $GUI;

    /**
     *
     * @var Breadcrumb
     */
    public $Breadcrumb;

    /**
     *
     * @var Widget
     */
    public $Widget;

    /**
     *
     * @var SideCache
     */
    public $SideCache;

    /**
     *
     * @var Hook
     */
    public $Hook;

    /**
     *
     * @var Page
     */
    public $Page;

    /**
     *
     * @var Plugin
     */
    public $Plugin;

    /**
     *
     * @var Router
     */
    public $Router;

    /**
     *
     * @var ContentLock
     */
    public $ContentLock;

    /**
     *
     * @var Permisson
     */
    public $Permission;

    /**
     *
     * @var Grid
     */
    public $Grid;


    /**
     * @var Event
     */
    public $Event;

    /**
     * @var Provider
     */
    public $Provider;

    /**
     * @var Site
     */
    public $Site;

    /**
     * @var Remote
     */
    public $Remote;


    /**
     * @var Layouter
     */
    public $Layouter;

    /**
     * @var Personal
     */
    public $Personal;

    /**
     * @var AliasRegistry
     */
    public $AliasRegistry;


    /**
     * @var Usergroup
     */
    public $Usergroup;

    /**
     * @var Cronjob
     */
    public $Cronjob;


    /**
     * @var Controller
     */
    public $Controller;

    /**
     * @var Firewall
     */
    public $Firewall;

    /**
     * @var Mail
     */
    public $Mail;


    /**
     * @var Paging
     */
    public $Paging;



    /**
     *
     * @var Autoloader
     */
    public $__autoloader = null;


    /**
     * @var
     */
    protected static $_directorys;

    /**
     * Import some default libraries
     */
    public function __construct()
    {

        if ( $this->__autoloader === null )
        {
            if ( Registry::objectExists( 'Autoloader' ) )
            {
                $this->__autoloader = Registry::getObject( 'Autoloader' );
            }

            if ( $this->__autoloader === null )
            {
                $_startTime = Debug::getMicroTime();

                $this->__autoloader = Autoloader::getInstance();

                if ( DEBUG )
                {
                    Debug::store( '`Autoloader`', 'End Load... ' . str_replace( ROOT_PATH, '', Library::formatPath( __FILE__ ) ) . ' @Line: ' . ( __LINE__ - 4 ), $_startTime );
                }

                Registry::setObject( 'Autoloader', $this->__autoloader );
            }
        }

        if ( !( $this->Event instanceof Event ) )
        {
            $this->load( 'Event' );
        }

        if ( !( $this->Input instanceof Input ) )
        {
            $this->load( 'Input' );
        }

        if ( !( $this->Env instanceof Env ) )
        {
            $this->load( 'Env' );
        }


        $this->Env->init();


        if ( $this->db === null )
        {
            if ( Registry::objectExists( md5( serialize( $GLOBALS[ 'dcms_db_configs' ] ) ) ) )
            {
                $this->db = Registry::getObject( md5( serialize( $GLOBALS[ 'dcms_db_configs' ] ) ) );
            }

            if ( $this->db === null )
            {
                Database::setConfig( $GLOBALS[ 'dcms_db_configs' ] );
                $this->db = Database::factory( $GLOBALS[ 'dcms_db_configs' ] );
                Registry::setObject( md5( serialize( $GLOBALS[ 'dcms_db_configs' ] ) ), $this->db );
            }
        }
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->db       = null;
        $this->Page     = null;
        $this->Output   = null;
        $this->Document = null;

        $this->Env        = null;
        $this->Template   = null;
        $this->GUI        = null;
        $this->Breadcrumb = null;
        $this->Input      = null;
        $this->Hook       = null;

        $this->__autoloader = null;
    }

    /**
     *
     * @param Application $object
     */
    protected function setApplicationInstance(Application $object)
    {

        #return;
        # if ( !(self::$_applicationInstance instanceof Application ) )
        #{

        self::$_applicationInstance = $object;
        Registry::setObject( 'Application', $object );
        # }
    }

    /**
     *
     * @return Application
     */
    public function getApplication()
    {
        if ( !( self::$_applicationInstance instanceof Application ) )
        {
            self::$_applicationInstance = Registry::getObject( 'Application' );
            if ( !( self::$_applicationInstance instanceof Application ) )
            {
                trigger_error( 'Undefined Application instance!', E_USER_ERROR );
            }
        }

        return self::$_applicationInstance;
    }

    /**
     *
     * @param Controller $object
     * @return Controller
     */
    protected function setControllerInstance(Controller $object)
    {
        if ( !( self::$_controllerInstance instanceof Controller ) )
        {
            self::$_controllerInstance = $object;
            Registry::setObject( 'Controller', $object );
        }
    }

    /**
     *
     * @return Controller $_controllerInstance
     */
    public function getController()
    {
        if ( !( self::$_controllerInstance instanceof Controller ) )
        {
            trigger_error( 'Undefined Controller instance!', E_USER_ERROR );
        }


        return self::$_controllerInstance;
    }

    /**
     * Import a library and make it accessible by its name or an optional key
     *
     * @param string $strClass
     * @param bool|string $strKey
     * @param boolean $blnForce
     *
     * @throws BaseException
     * @return Loader
     */
    public function load($strClass, $strKey = false, $blnForce = false)
    {
        $strKey = ( $strKey !== false ? $strKey : $strClass );

        if ( isset( $this->{$strKey} ) && is_object( $this->{$strKey} ) && $blnForce === false )
        {
            return $this;
        }

        if ( Registry::objectExists( $strClass ) )
        {
            if ( $blnForce === false )
            {
                $this->{$strKey} = Registry::getObject( $strClass );

                return $this;
            }
            else
            {
                Registry::removeObject( $strClass );
            }
        }


        #  $_startTime = Debug::getMicroTime();

        /*
        if ( DEBUG )
        {
            $trace = debug_backtrace();
            $trace = $trace[ 0 ];
            $caller = array(
                'file' => (!empty( $trace[ 'file' ] ) ? str_replace( ROOT_PATH, '', Library::formatPath( $trace[ 'file' ] ) ) : 'unknown'),
                'line' => (!empty( $trace[ 'line' ] ) ? $trace[ 'line' ] : 'unknown'),
            );
            unset( $trace );
        }
*/

        if ( $strClass && checkClassMethod( $strClass . '/getInstance' ) )
        {
            $this->{$strKey} = call_user_func( array(
                $strClass,
                'getInstance') );
        }
        else
        {

            if ( class_exists( $strClass ) )
            {
                $this->{$strKey} = new $strClass;
            }
            else
            {
                throw new BaseException( sprintf( 'The Class %s not exists', $strClass ) );
            }


            #Registry::setObject($strClass, new $strClass());
        }

        Registry::setObject( $strClass, $this->{$strKey} );


        if ( DEBUG )
        {
            Debug::store( '`' . $strClass . '`', 'End Load... ' );
            // Debug::store( '`' . $strClass . '`', 'End Load... ' . $caller[ 'file' ] . ' @Line: ' . $caller[ 'line' ], $_startTime );
            // $caller = null;
        }

        return $this;
    }

    /**
     *
     * @param string $strClass
     * @return Loader
     */
    public function unload($strClass)
    {
        if ( Registry::objectExists( $strClass ) )
        {
            $obj = Registry::getObject( $strClass );
            if ( ( $obj instanceof $strClass ) && method_exists( $obj, 'freeMem' ) )
            {
                $obj->freeMem();
            }

            $obj = null;
        }

        Registry::removeObject( $strClass );

        if ( isset( $this->{$strClass} ) )
        {
            if ( ( $this->{$strClass} instanceof $strClass ) && method_exists( $this->{$strClass}, 'freeMem' ) )
            {
                $this->{$strClass}->freeMem();
            }
            if ( ( $this->{$strClass} instanceof $strClass ) && method_exists( $this->{$strClass}, '__destruct' ) )
            {
                $this->{$strClass}->__destruct();
            }
            $this->{$strClass} = null;
        }

        return $this;
    }

    // -------------------- Input/post/get Alias

    /**
     *
     * @param string $key
     * @param string $type
     * @return mixed
     */
    public function _post($key = null, $type = null)
    {
        $this->load( 'Input' );

        return $this->Input->post( $key, $type );
    }

    /**
     *
     * @param string $key
     * @param string $type
     * @return mixed
     */
    public function _get($key = null, $type = null)
    {
        $this->load( 'Input' );

        return $this->Input->get( $key, $type );
    }

    /**
     *
     * @param string $key
     * @param string $type
     * @return mixed
     */
    public function input($key = null, $type = null)
    {
        $this->load( 'Input' );

        return $this->Input->input( $key, $type );
    }

    /**
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function setInput($key = null, $value = null)
    {
        return $this->Input->set( $key, $value );
    }

    /**
     * Helper Functions
     *
     */
    public function removeTablePrefix($table)
    {
        return str_replace( '%tp%', '', $table );
    }

    /**
     * create the where clause for translation tables
     *
     * @staticvar integer $xx
     * @param string $table the table withs translation content
     * @param        $primarykey
     * @param string $transTblAlias the translationtable alias
     * @internal  param string $pk primarykey (tbl.id, t.id, c.catid) will build the expression (... NOT EXISTS (SELECT ... WHERE catid=c.catid AND lang=...)
     * @return string sql query for the where
     */
    public static function sbuildTransWhere($table, $primarykey, $transTblAlias = null)
    {
        static $xx;

        if ( !trim( $table ) )
        {
            Error::raise( 'Table is not set' );
        }

        $refalias = '';
        $tmppk    = $primarykey;
        $_pk      = explode( '.', $primarykey );
        if ( count( $_pk ) === 2 )
        {
            $refalias   = $_pk[ 0 ];
            $primarykey = $_pk[ 1 ];
        }
        else
        {
            Error::raise( 'Invalid primarykey value' );
        }

        if ( substr( $table, 0, 4 ) !== '%tp%' )
        {
            $table = '%tp%' . $table;
        }

        if ( substr( $table, -6 ) === '_trans' )
        {
            $table = substr( $table, 0, -6 );
        }

        $xx++;

        $db = Database::getInstance();

        $query = '';
        if ( $transTblAlias !== null && is_string( $transTblAlias ) )
        {
            $query .= "\n" . '(`' . $transTblAlias . '`.`lang` = ' . $db->quote( CONTENT_TRANS ) . ' OR ' . $transTblAlias . '.iscorelang = 1';
        }

        $query .= '
            AND NOT EXISTS (
                SELECT `x' . $xx . 'x`.`lang` FROM ' . $table . '_trans AS `x' . $xx . 'x` WHERE `x' . $xx . 'x`.`' . $primarykey . '` = ' . $tmppk . ' AND `x' . $xx . 'x`.`lang` = ' . $db->quote( CONTENT_TRANS ) . '
                 )';

        if ( $transTblAlias !== null && is_string( $transTblAlias ) )
        {
            $query .= ')';
        }

        return $query;
    }

    /**
     * create the where clause for translation tables
     *
     * @staticvar integer $xx
     * @param string $table the table withs translation content
     * @param        $primarykey
     * @param string $transTblAlias the translationtable alias
     * @internal  param string $pk primarykey (tbl.id, t.id, c.catid) will build the expression (... NOT EXISTS (SELECT ... WHERE catid=c.catid AND lang=...)
     * @return string sql query for the where
     */
    public function buildTransWhere($table, $primarykey, $transTblAlias = null)
    {
        static $xx;

        if ( !trim( $table ) )
        {
            Error::raise( 'Table is not set' );
        }

        $refalias = '';
        $tmppk    = $primarykey;
        $_pk      = explode( '.', $primarykey );
        if ( count( $_pk ) === 2 )
        {
            $refalias   = $_pk[ 0 ];
            $primarykey = $_pk[ 1 ];
        }
        else
        {
            Error::raise( 'Invalid primarykey value' );
        }

        if ( substr( $table, 0, 4 ) !== '%tp%' )
        {
            $table = '%tp%' . $table;
        }

        if ( substr( $table, -6 ) === '_trans' )
        {
            $table = substr( $table, 0, -6 );
        }

        $xx++;

        $query = '';
        if ( $transTblAlias !== null && is_string( $transTblAlias ) )
        {
            $query .= "\n" . '(`' . $transTblAlias . '`.`lang` = ' . $this->db->quote( CONTENT_TRANS ) . ' OR ' . $transTblAlias . '.iscorelang = 1';
        }

        $query .= '
            AND NOT EXISTS (
                SELECT `x' . $xx . 'x`.`lang` FROM ' . $table . '_trans AS `x' . $xx . 'x` WHERE `x' . $xx . 'x`.`' . $primarykey . '` = ' . $tmppk . ' AND `x' . $xx . 'x`.`lang` = ' . $this->db->quote( CONTENT_TRANS ) . '
                 )';

        if ( $transTblAlias !== null && is_string( $transTblAlias ) )
        {
            $query .= ')';
        }

        return $query;
    }

    /**
     * @param      $table
     * @param      $primarykey
     * @param null $transTblAlias
     * @return mixed
     */
    public static function buildContentLockJoin($table, $primarykey, $transTblAlias = null)
    {


        return $query;
    }

    /**
     * add the draft buttons for content editing
     *
     * @param boolean $to
     */
    public function setDraftButton($to = false)
    {
        self::$addDraftButton = $to;
    }

    /**
     *
     * @return boolean
     */
    public function hasDraftButton()
    {
        return self::$addDraftButton;
    }

    /**
     * get the current content pagenumber
     *
     * @return int
     */
    public function getCurrentPage()
    {
        $page = 1;

        if ( $this->Input->input( 'page' ) > 0 )
        {
            $page = intval( $this->Input->input( 'page' ) );

            if ( $page === 0 )
            {
                $page = 1;
            }
        }
        else
        {
            $page = 1;
        }

        return $page;
    }

    /**
     *
     * @return int
     */
    public function getPerpage()
    {
        $aSizes            = array(
            5,
            10,
            15,
            20,
            25,
            30,
            35,
            50,
            75,
            100,
            200,
            500);
        $perpage_hardlimit = 2000;
        $perpage_default   = 20;
        $perpage           = 20;


        $inputPerPage = (int)$this->input( 'perpage' );
        $inputPerPage = ( !$inputPerPage ? 20 : $inputPerPage );
        $ipp          = (int)$this->input( 'perpage' );


        // anzahl hinzufügen falls nicht vorhanden
        if ( $inputPerPage && (int)$inputPerPage > 0 && !in_array( (int)$inputPerPage, $aSizes ) )
        {
            array_unshift( $aSizes, (int)HTTP::input( 'perpage' ) );
        }

        $uniq = md5( CONTROLLER . ACTION . (int)$inputPerPage . HTTP::input( 'q', '' ) . HTTP::input( 'sort', '' ) . HTTP::input( 'order', '' ) );

        if ( isset( $GLOBALS[ 'perpage' ] ) && (int)$GLOBALS[ 'perpage' ] > 0 )
        {
            $perpage = (int)$GLOBALS[ 'perpage' ];
        }
        else
        {
            if ( $ipp !== null && (int)$inputPerPage === 0 )
            {
                $perpage = $perpage_hardlimit;
            }
            else if ( $ipp === null )
            {
                $perpage = $perpage_default;
            }
            else
            {
                if ( !isset( $_SESSION[ 'perpage' ][ $uniq ] ) )
                {
                    $perpage = (int)$inputPerPage ? (int)$inputPerPage : $perpage_default;
                }
                else
                {
                    $perpage = $_SESSION[ 'perpage' ][ $uniq ];
                }
            }
        }

        //	$_SESSION[ 'perpage' ][ $uniq ] = $perpage;

        if ( !HTTP::input( 'perpage' ) )
        {
            HTTP::setinput( 'perpage', $perpage );
        }

        $_SESSION[ 'perpage' ][ $uniq ] = $perpage;

        $GLOBALS[ 'perpage' ] = $perpage;


        return $perpage;
    }

    /**
     * will return the path to content resource
     * if the path not exists will create the path
     *
     * @example giveFolder('img::news', true, true) returns "pages/1/img/news/2011/10/"
     *          or
     *          giveFolder('img::'. CONTROLLER, true, true) returns "pages/1/img/news/2011/10/"
     *          or
     *          giveFolder('img', true, true) returns "pages/1/img/2011/10/"
     *
     * @param string $type the resource type (img, media or file) media is only used from media controller
     * @param bool $useyears will use extra folder for the years (default is true)
     * @param bool $usemonth will use extra folder for the month (default is true)
     * @param bool $usedays will use extra folder for days (default is false)
     *
     * @return string
     */
    public function giveFolder($type = 'img', $useyears = true, $usemonth = true, $usedays = false)
    {
        $path  = '';
        $type  = strtolower( $type );
        $modul = '';

        if ( strpos( $type, '/' ) !== false )
        {
            $type = str_replace( '/', '::', $type );
        }


        if ( strpos( $type, '::' ) !== false )
        {
            $types = explode( '::', $type );
            $type  = $types[ 0 ];

            if ( count( $types ) > 2 )
            {
                // Error::raise(sprintf('The resource `%s` is not correct', $type));
            }


            if ( $type == 'file' )
            {
                $modultype = preg_replace( '#([^a-z]*)#i', '', $types[ 1 ] );

                // clean the modulname with alpha only
                $modul = preg_replace( '#([^a-z]*)#i', '', $types[ 2 ] );
                if ( ( $types[ 2 ] && !$modul ) || $modul != $types[ 2 ] )
                {
                    Error::raise( sprintf( 'The giving Modul `%s` is not correct', $types[ 2 ] ) );
                }

                if ( $modul )
                {
                    $modul = $modultype . '/' . $modul;
                }
                else
                {
                    $modul = $modultype;
                }
            }
            else
            {
                // clean the modulname with alpha only
                $modul = preg_replace( '#([^a-z]*)#i', '', $types[ 1 ] );

                if ( ( $types[ 1 ] && !$modul ) || $modul != $types[ 1 ] )
                {
                    Error::raise( sprintf( 'The giving Modul `%s` is not correct', $types[ 1 ] ) );
                }

                unset( $types[ 0 ] );
                unset( $types[ 1 ] );
                foreach ( $types as $folder )
                {
                    if ( $folder )
                    {
                        $modul .= '/' . $folder;
                    }
                }
            }
        }

        switch ( $type )
        {
            case 'img':
            default:
                $path = PAGE_PATH . 'img';

                if ( $modul != '' )
                {
                    $path .= '/' . $modul;
                }

                if ( ( $usemonth && !$useyears ) || ( $useyears && $usemonth ) || ( $usedays && ( !$usemonth || !$useyears ) ) )
                {
                    $path .= '/' . date( 'Y' ) . '/' . date( 'm' );
                }
                elseif ( $useyears && !$usemonth )
                {
                    $path .= '/' . date( 'Y' );
                }

                if ( $usedays )
                {
                    $path .= '/' . date( 'd' );
                }

                $path .= '/';
                break;

            case 'file':
                $path = PAGE_PATH . 'file';

                if ( $modul != '' )
                {
                    $path .= '/' . $modul;
                }

                if ( ( $usemonth && !$useyears ) || ( $useyears && $usemonth ) || ( $usedays && ( !$usemonth || !$useyears ) ) )
                {
                    $path .= '/' . date( 'Y' ) . '/' . date( 'm' );
                }
                elseif ( $useyears && !$usemonth )
                {
                    $path .= '/' . date( 'Y' );
                }

                if ( $usedays )
                {
                    $path .= '/' . date( 'd' );
                }

                $path .= '/';
                break;

            case 'media':
                $path = MEDIA_PATH;

                if ( ( $usemonth && !$useyears ) || ( $useyears && $usemonth ) )
                {
                    $path .= '/' . date( 'Y' ) . '/' . date( 'm' );
                }
                elseif ( $useyears && !$usemonth )
                {
                    $path .= '/' . date( 'Y' );
                }

                $path .= '/';
                break;
        }


        if ( !$path )
        {
            Error::raise( 'Path is not set in giveFolder' );
        }

        /**
         * create the path if not exists and cache the create
         * will skip all other requests
         */
        if ( !isset( self::$_directorys[ $path ] ) || !is_dir( $path ) )
        {
            Library::makeDirectory( $path );
            self::$_directorys[ $path ] = true;
        }

        return $path;
    }


    public function setDraftLoaction()
    {

    }

    /**
     * Save content drafts and call Controller_Abstract::addLastEdit()
     *
     * @param integer $id
     * @param string $title
     * @param string $recentType
     * @return int
     */
    public function saveContentDraft($id = 0, $title = '', $recentType = '')
    {

        $save  = (int)$this->input( 'savedraft' );
        $draft = unserialize( base64_decode( $this->input( 'draftlocation' ) ) ); //Session::get( 'DraftLocation' ); session is not save for multiple edit instances

        if ( !$id || !is_array( $draft ) || empty( $draft[ 0 ] ) )
        {
            if ( $id && is_array( $draft ) )
            {
                if ( $save )
                {
                    $this->db->query( 'REPLACE INTO %tp%drafts (contentid,contentlocation,userid,timestamp,title, `lang`, controller, `action`)
									  VALUES(?,?,?,?,?,?,?,?)', $id, $draft[ 0 ], User::getUserId(), time(), $title, CONTENT_TRANS, $draft[ 1 ], $draft[ 2 ] );
                }
                else
                {
                    $this->db->query( 'DELETE FROM %tp%drafts WHERE contentid = ? AND `lang` = ? AND controller = ? AND `action` = ?', $id, CONTENT_TRANS, $draft[ 1 ], $draft[ 2 ] );
                }

                $this->addLastEdit( $id, $title, $recentType );
            }

            return $save;
        }

        if ( !empty( $save ) )
        {
            $this->db->query( 'REPLACE INTO %tp%drafts (contentid,contentlocation,userid,timestamp,title, `lang`, controller, `action`)
							  VALUES(?,?,?,?,?,?,?,?)', $id, $draft[ 0 ], User::getUserId(), time(), $title, CONTENT_TRANS, $draft[ 1 ], $draft[ 2 ] );
        }
        else
        {
            $this->db->query( 'DELETE FROM %tp%drafts WHERE contentid = ? AND `lang` = ? AND contentlocation = ?', $id, CONTENT_TRANS, $draft[ 0 ] );
        }

        $this->addLastEdit( $id, $title, $recentType, $draft[ 0 ] );

        return $save;
    }

    /**
     * will save the last edited document (recents)
     *
     * @param int $id
     * @param string $title
     * @param string $recentType
     * @param string $location
     * @return void
     */
    public function addLastEdit($id = 0, $title = '', $recentType = '', $location = '')
    {

        /**
         * count items by group
         */
        $c = $this->db->query( 'SELECT COUNT(*) AS counter FROM %tp%last_edit WHERE controller = ?', CONTROLLER )->fetch();


        if ( $c[ 'counter' ] === 15 )
        {
            //
            $r = $this->db->query( 'SELECT id FROM %tp%last_edit WHERE controller = ? ORDER BY timestamp ASC LIMIT 1', CONTROLLER )->fetch();
            $this->db->query( 'DELETE FROM %tp%last_edit WHERE id = ?', $r[ 'id' ] );
        }
        else
        {
            // delete old item
            $this->db->query( 'DELETE FROM %tp%last_edit WHERE timestamp < ? AND contentid = ?', TIMESTAMP - 86000, $id );
        }

        if ( !$location )
        {
            $location = $this->Env->location();
            $location = str_replace( '&amp;', '&', $location );

            $location = preg_replace( '#^/public/#is', '', $location );
            $location = preg_replace( '#&_=([\d]*)#is', '', $location );
            $location = preg_replace( '#&ajax=(1|true|on)#is', '', $location );
        }

        $this->db->query( 'REPLACE INTO %tp%last_edit (contentid, contentlocation, userid, timestamp, title, `lang`, controller, recenttype)
                          VALUES(?,?,?,?,?,?,?,?)', $id, $location, User::getUserId(), TIMESTAMP, $title, CONTENT_TRANS, CONTROLLER, $recentType );
    }

}

?>