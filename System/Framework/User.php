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
 * @file        User.php
 *
 */
class User
{

    /**
     * @var null
     */
    private static $is_logged_in = null;

    /**
     * @var int
     */
    private static $user_id = 0;

    /**
     * @var bool
     */
    private static $username = false;

    /**
     * @var bool
     */
    private static $password = false;

    /**
     * @var bool
     */
    private static $usergroupid = false;

    /**
     * @var bool
     */
    private static $isAdmin = false;

    /**
     * @var bool
     */
    private static $groupType = false;

    /**
     * @var null
     */
    private static $styleid = null;

    /**
     * @var string
     */
    private static $template = false;

    /**
     * @var null
     */
    private static $userdata = null;

    /**
     * @var null
     */
    private static $uniqidkey = null;

    /**
     * @var null
     */
    private static $skinData = null;

    /**
     * @var string
     */
    private static $language = 'de';

    /**
     *
     * @var array
     */
    public static $permissions = null;

    /**
     *
     * @var array
     */
    public static $special_permissions = null;

    /**
     *
     * @var array
     */
    public static $forum_permissions = null;

    /**
     *
     * @var integer
     */
    public static $group_forum_permissions = null;

    /**
     *
     */
    public function __construct()
    {

    }

    /**
     *
     * @param string $key
     * @param mixed $value default is null
     */
    public static function setUserData($key, $value = null)
    {
        if ( is_null( self::$is_logged_in ) )
        {
            self::loadFromSession();
        }

        self::$userdata[ $key ] = $value;
    }

    /**
     *
     * @return array
     */
    static public function getUserData()
    {
        if ( is_null( self::$is_logged_in ) )
        {
            self::loadFromSession();
        }

        return self::$userdata;
    }

    /**
     *
     * @param string $username
     * @param string $password
     * @param bool|string $key default is false
     * @return boolean
     */
    static public function login($username = '', $password = '', $key = false)
    {
        $db = Database::getInstance();

        $sql = false;

        if ( defined( 'ADM_SCRIPT' ) && ADM_SCRIPT )
        {
            if ( ( $username === false || $password === false ) && $key !== false )
            {
                $sql = "SELECT u.*, up.permissions AS specialperms, g.title AS grouptitle, g.permissions, g.dashboard, g.grouptype
                    FROM %tp%users AS u
                    LEFT JOIN %tp%users_access AS up ON(up.userid=u.userid)
                    LEFT JOIN %tp%users_groups AS g ON(g.groupid=u.groupid)
                    WHERE g.dashboard=1 AND u.uniqidkey=" . $db->quote( $key );
            }
            elseif ( $username !== false && $password !== false )
            {
                $sql = "SELECT u.*, up.permissions AS specialperms, g.title AS grouptitle, g.permissions, g.dashboard, g.grouptype
                    FROM %tp%users AS u
                    LEFT JOIN %tp%users_access AS up ON(up.userid=u.userid)
                    LEFT JOIN %tp%users_groups AS g ON(g.groupid=u.groupid)
                    WHERE g.dashboard=1 AND u.username=" . $db->quote( $username ) /* . "
                          AND u.password=" . $db->quote( $password ); */ . ' GROUP BY u.userid';
            }
        }
        else
        {

            $avatars     = ( Settings::get( 'showavatar' ) ? '
                                                    avatar.avatarname,
                                                    avatar.avatarextension,
                                                    avatar.width AS avatarwidth,
                                                    avatar.height AS avatarheight,
                                                    avatar.userid AS avatarowner,
                                                    NOT ISNULL(customavatar.avatarname) AS hascustomavatar,' : '' );
            $avatarsjoin = ( Settings::get( 'showavatar' ) ? "
                            LEFT JOIN %tp%avatars AS avatar ON(avatar.avatarid = u.avatarid)
                            LEFT JOIN %tp%avatars AS customavatar ON(customavatar.userid = u.userid) " : '' );

            if ( ( $username === false || $password === false ) && $key !== false )
            {
                $sql = "SELECT u.*, up.permissions AS specialperms,
                    COUNT(m.id) AS pmcounter,
                    r.ranktitle, r.rankimages, {$avatars} g.title AS grouptitle, g.permissions, g.dashboard, g.grouptype
                    FROM %tp%users AS u
                    LEFT JOIN %tp%users_access AS up ON(up.userid=u.userid)
                    LEFT JOIN %tp%users_groups AS g ON(g.groupid=u.groupid)
                    LEFT JOIN %tp%users_ranks AS r ON(r.rankid = u.rankid)
                    LEFT JOIN %tp%messages AS m ON(m.touser = u.userid AND readtime = 0)
                    {$avatarsjoin}
                    WHERE u.uniqidkey=" . $db->quote( $key ) . ' GROUP BY u.userid';
            }
            elseif ( $username !== false && $password !== false )
            {
                $sql = "SELECT u.*, up.permissions AS specialperms, COUNT(m.id) AS pmcounter, r.ranktitle, r.rankimages, {$avatars}  g.title AS grouptitle, g.permissions, g.dashboard, g.grouptype
                    FROM %tp%users AS u
                    LEFT JOIN %tp%users_access AS up ON(up.userid=u.userid)
                    LEFT JOIN %tp%users_groups AS g ON(g.groupid=u.groupid)
                    LEFT JOIN %tp%users_ranks AS r ON(r.rankid = u.rankid)
                    LEFT JOIN %tp%messages AS m ON(m.touser = u.userid AND readtime = 0)
                    {$avatarsjoin}
                    WHERE u.username=" . $db->quote( $username ) /* . "
                          AND u.password=" . $db->quote( $password ) */ . ' GROUP BY u.userid';
            }
        }

        if ( $sql !== false )
        {
            $r = $db->query( $sql )->fetch();
        }


        $validLogin = false;

        // Use PasswordHash Class
        if ( isset( $r[ 'userid' ] ) && $r[ 'userid' ] > 0 && $password )
        {
            // Password Hash Strength is default 16
            $phpass     = new PasswordHash();
            $validLogin = $phpass->CheckPassword( $password, $r[ 'password' ] );
        }
        elseif ( isset( $r[ 'userid' ] ) && $r[ 'userid' ] > 0 && !$password && $key !== false && $key === $r[ 'uniqidkey' ] )
        {

            $validLogin = true;
        }


        if ( $validLogin )
        {

            if ( $r[ 'blocked' ] )
            {
                if ( IS_AJAX )
                {
                    Library::sendJson( false, trans( 'Dieser Account wurde von uns gesperrt!' ) );
                }
                else
                {

                    $page = new Page();

                    $page->error( 403, trans( 'Dieser Account wurde von uns gesperrt!' ) );
                }


                self::$is_logged_in = false;

                return false;
            }


            if ( empty( $r[ 'uniqidkey' ] ) )
            {
                $crypt            = new Crypt( Settings::get( 'crypt_key', null ) );
                $r[ 'uniqidkey' ] = $crypt->encrypt( $username . '|' . $password );
                $sql              = "UPDATE %tp%users SET uniqidkey = " . $db->quote( $r[ 'uniqidkey' ] ) . " WHERE userid = " . $r[ 'userid' ];
                $db->query( $sql );
            }

            $db->query( 'UPDATE %tp%users SET lastvisit = ' . time() . ', lastactivity = ' . ( time() + 10 ) . ', blocked_until = 0, failed_logins = 0 WHERE userid = ' . $r[ 'userid' ] );


            self::$userdata = $r;

            if ( !empty( self::$userdata[ 'editorsettings' ] ) )
            {
                self::$userdata[ 'editorsettings' ] = unserialize( self::$userdata[ 'editorsettings' ] );
            }


            self::$permissions         = unserialize( $r[ 'permissions' ] );
            self::$special_permissions = unserialize( self::$userdata[ 'specialperms' ] );

            if ( is_array( self::$special_permissions ) && count( self::$special_permissions ) > 0 )
            {
                foreach ( self::$special_permissions as $k => $v )
                {
                    if ( is_array( $v ) )
                    {
                        foreach ( $v as $kk => $vv )
                        {
                            if ( isset( self::$permissions[ $k ][ $kk ] ) && !is_null( $vv ) )
                            {
                                self::$permissions[ $k ][ $kk ] = $vv;
                            }
                        }
                    }
                    else
                    {
                        if ( isset( self::$special_permissions[ $k ] ) && !is_null( self::$special_permissions[ $k ] ) )
                        {
                            self::$permissions[ $k ] = self::$special_permissions[ $k ];
                        }
                    }
                }
                // self::$permissions = Library::arrayMergeReplaceRecursive( self::$permissions, self::$special_permissions );
                #self::$permissions = array_merge_recursive(self::$permissions, self::$special_permissions);
            }

            self::$forum_permissions       = isset( self::$permissions[ 'forumpermissions' ] ) && !empty( self::$permissions[ 'forumpermissions' ] ) ? unserialize( self::$permissions[ 'forumpermissions' ] ) : array();
            self::$group_forum_permissions = isset( self::$permissions[ 'groupforumpermissions' ] ) ? (int)self::$permissions[ 'groupforumpermissions' ] : 0;
            self::$is_logged_in            = true;

            self::$usergroupid = $r[ 'groupid' ];
            self::$username    = $r[ 'username' ];
            self::$user_id     = $r[ 'userid' ];
            self::$uniqidkey   = $r[ 'uniqidkey' ];
            self::$password    = $r[ 'password' ];
            self::$styleid     = ( (int)$r[ 'styleid' ] ? $r[ 'styleid' ] : null );


            if ( Session::get( 'skinid', 0 ) > 0 )
            {
                self::$styleid = Session::get( 'skinid', 0 );

                if ( !self::skinExist( self::$styleid ) )
                {
                    self::$styleid = null;
                }
            }

            if ( is_null( self::$styleid ) )
            {
                self::getSkinId();
            }


            self::$isAdmin   = ( $r[ 'dashboard' ] ? true : false );
            self::$groupType = $r[ 'grouptype' ];

            self::$language = self::$userdata[ 'language' ];


            self::getPhoto();


            Session::save( 'username', $r[ 'username' ] );
            Session::save( 'groupid', $r[ 'groupid' ] );
            Session::save( 'password', $r[ 'password' ] );
            Session::save( 'userid', (int)$r[ 'userid' ] );
            Session::save( 'styleid', (int)( (int)$r[ 'styleid' ] ? $r[ 'styleid' ] : 0 ) );
            Session::save( 'uniqidkey', $r[ 'uniqidkey' ] );

            if ( !Session::get( 'sessionstart' ) )
            {
                Session::save( 'sessionstart', time() );
            }

            if ( defined( 'ADM_SCRIPT' ) )
            {
                Session::save( "expiry", time() + Settings::get( 'adminsession_timeout', 3600 ) );
            }
            else
            {
                Session::save( "expiry", time() + Settings::get( 'usersession_timeout', 2600 ) );
            }
            #  Session::write();


            if ( $key === false )
            {
                Library::log( sprintf( "User %s logged in successfully.", $r[ 'username' ] ) );
            }
            else
            {
                // Library::log( sprintf( "User %s logged in successfully (resumed persistent session).", $r[ 'username' ] ) );
            }

            $r = null;

            return true;
        }
        else
        {


            // 
            if ( Settings::get( 'block_failed_logins', 3 ) && !isset( $GLOBALS[ 'BACKEND' ] ) )
            {
                if ( $r[ 'userid' ] )
                {

                    $maxFailds       = Settings::get( 'max_failed_logins', 3 );
                    $maxFaildTimeout = Settings::get( 'failed_login_timeout', 500 );
                    if ( $maxFaildTimeout > 0 )
                    {
                        if ( $r[ 'blocked_until' ] > 0 && TIMESTAMP > $r[ 'blocked_until' ] )
                        {
                            $r[ 'failed_logins' ] = 0;
                        }

                        $r[ 'failed_logins' ]++;
                        $r[ 'blocked_until' ] = ( $r[ 'failed_logins' ] >= Settings::get( 'max_failed_logins', 3 ) ? TIMESTAMP + ( $maxFaildTimeout * 60 ) : 0 );

                        $db->query( 'UPDATE %tp%users SET blocked_until = ?, failed_logins = ? WHERE userid = ' . $r[ 'userid' ], $r[ 'blocked_until' ], $r[ 'failed_logins' ] );
                        $blocked = $r[ 'blocked_until' ] > 0 && $r[ 'blocked_until' ] > TIMESTAMP ? true : false;

                        self::$is_logged_in = false;
                        self::$isAdmin      = false;
                        self::$user_id      = 0;
                        self::$userdata     = self::loadDefaultGroup();

                        self::$userdata[ 'dateformat' ] = Settings::get( 'dateformat' );
                        self::$userdata[ 'timeformat' ] = Settings::get( 'timeformat' );

                        self::$groupType = self::$userdata[ 'grouptype' ];

                        self::$permissions             = unserialize( self::$userdata[ 'permissions' ] );
                        self::$forum_permissions       = unserialize( self::$permissions[ 'forumpermissions' ] );
                        self::$group_forum_permissions = (int)self::$permissions[ 'groupforumpermissions' ];

                        self::$usergroupid = self::$userdata[ 'groupid' ];
                        Session::save( 'groupid', self::$usergroupid );

                        if ( $blocked )
                        {


                            $tspan = Locales::getTimeDifferenceInWords( ( TIMESTAMP - ( $maxFaildTimeout * 60 ) ) );

                            if ( IS_AJAX )
                            {
                                Library::log( sprintf( "`%s` attempted to log in, but failed", $r[ 'username' ] ), 'critical' );
                                Library::sendJson( false, sprintf( trans( 'Login ist fehlgeschlagen!<br/><strong>Dein Account ist für %s gesperrt.</strong>' ), $tspan ) );
                            }
                            else
                            {
                                $page = new Page();
                                $page->error( 403, sprintf( trans( 'Login ist fehlgeschlagen!<br/><strong>Dein Account ist für %s gesperrt.</strong>' ), $tspan ) );
                            }

                            return false;
                        }
                        else
                        {
                            if ( IS_AJAX )
                            {
                                $count = $maxFailds - $r[ 'failed_logins' ];

                                Library::log( sprintf( "`%s` attempted to log in, but failed", $r[ 'username' ] ), 'critical' );
                                Library::sendJson( false, sprintf( trans( 'Login ist fehlgeschlagen!<br/><strong>Dir verbleiben noch %s login versuche.</strong>' ), $count ) );
                            }
                        }
                    }
                }
            }


            Session::save( 'userid', 0 );

            if ( !Session::get( 'sessionstart' ) )
            {
                Session::save( 'sessionstart', time() );
            }

            self::$isAdmin = false;

            if ( Session::get( 'skinid', 0 ) > 0 )
            {
                self::$styleid = Session::get( 'skinid', 0 );

                if ( !self::skinExist( self::$styleid ) )
                {
                    self::$styleid = null;
                }
            }

            if ( is_null( self::$styleid ) )
            {
                self::getSkinId();
            }

            self::$user_id  = 0;
            self::$userdata = self::loadDefaultGroup();

            self::$userdata[ 'dateformat' ] = Settings::get( 'dateformat' );
            self::$userdata[ 'timeformat' ] = Settings::get( 'timeformat' );

            self::$groupType = self::$userdata[ 'grouptype' ];

            self::$permissions             = unserialize( self::$userdata[ 'permissions' ] );
            self::$forum_permissions       = unserialize( self::$permissions[ 'forumpermissions' ] );
            self::$group_forum_permissions = (int)self::$permissions[ 'groupforumpermissions' ];

            self::$usergroupid = self::$userdata[ 'groupid' ];
            Session::save( 'groupid', self::$usergroupid );
            self::$is_logged_in = false;

            return false;
        }
    }

    /**
     *
     * @return array
     */
    static public function getGroupPermission()
    {
        if ( is_null( self::$is_logged_in ) )
        {
            self::loadFromSession();
        }


        return self::$permissions;
    }

    /**
     *
     * @return array
     */
    static public function getPrivatePermission()
    {
        if ( is_null( self::$is_logged_in ) )
        {
            self::loadFromSession();
        }

        return self::$special_permissions;
    }

    /**
     *
     * @param boolean $useCookie
     * @return array
     */
    static public function initUserData($useCookie = false)
    {
        if ( !is_null( self::$userdata ) )
        {
            return self::$userdata;
        }

        $db = Database::getInstance();

        if ( defined( 'ADM_SCRIPT' ) && ADM_SCRIPT )
        {
            if ( !$useCookie )
            {

                $sql = "
                    SELECT u.*, up.permissions AS specialperms, g.title AS grouptitle, g.permissions, g.dashboard, g.grouptype, g.editorsettings
                    FROM %tp%users AS u
                    LEFT JOIN %tp%users_access AS up ON(up.userid=u.userid)
                    LEFT JOIN %tp%users_groups AS g ON(g.groupid=u.groupid)
                    LEFT JOIN %tp%avatars AS a ON(a.avatarid=u.avatarid)
                    WHERE g.dashboard=1 AND u.username=" . $db->quote( self::$username ); /* . " AND u.password=" . $db->quote( self::$password ); */
            }
            else
            {

                $sql = "SELECT u.*, up.permissions AS specialperms, g.title AS grouptitle, g.permissions, g.dashboard, g.grouptype, g.editorsettings
                    FROM %tp%users AS u
                    LEFT JOIN %tp%users_access AS up ON(up.userid=u.userid)
                    LEFT JOIN %tp%users_groups AS g ON(g.groupid=u.groupid)
                    LEFT JOIN %tp%avatars AS a ON(a.avatarid=u.avatarid)
                    WHERE g.dashboard=1 AND u.uniqidkey=" . $db->quote( Cookie::get( 'uhash' ) );
            }
        }
        else
        {

            $avatars     = ( Settings::get( 'showavatar' ) ? '
                                                    avatar.avatarname,
                                                    avatar.avatarextension,
                                                    avatar.width AS avatarwidth,
                                                    avatar.height AS avatarheight,
                                                    avatar.userid AS avatarowner,
                                                    NOT ISNULL(customavatar.avatarname) AS hascustomavatar,' : '' );
            $avatarsjoin = ( Settings::get( 'showavatar' ) ? "
                            LEFT JOIN %tp%avatars AS avatar ON(avatar.avatarid = u.avatarid)
                            LEFT JOIN %tp%avatars AS customavatar ON(customavatar.userid = u.userid) " : '' );


            if ( !$useCookie )
            {

                $sql = "SELECT u.*, 
                    up.permissions AS specialperms, 
                    COUNT(m.id) AS pmcounter, r.ranktitle, r.rankimages, 
                    {$avatars} g.title AS grouptitle, g.permissions, g.dashboard, g.grouptype, g.editorsettings
                    FROM %tp%users AS u
                    LEFT JOIN %tp%users_groups AS g ON(g.groupid=u.groupid)
                    LEFT JOIN %tp%users_access AS up ON(up.userid=u.userid)
                    LEFT JOIN %tp%users_ranks AS r ON(r.rankid = u.rankid)
                    LEFT JOIN %tp%messages AS m ON(m.touser = u.userid AND readtime = 0)
                    {$avatarsjoin}
                    WHERE u.username=" . $db->quote( self::$username ) /* . " AND u.password=" . $db->quote( self::$password ) */ . ' GROUP BY u.userid';
            }
            else
            {
                $sql = "SELECT u.*, up.permissions AS specialperms, COUNT(m.id) AS pmcounter, r.ranktitle, r.rankimages, 
                    {$avatars} g.title AS grouptitle, g.permissions, g.dashboard, g.grouptype, g.editorsettings
                    FROM %tp%users AS u
                    LEFT JOIN %tp%users_access AS up ON(up.userid=u.userid)
                    LEFT JOIN %tp%users_groups AS g ON(g.groupid=u.groupid)
                    LEFT JOIN %tp%messages AS m ON(m.touser = u.userid AND readtime = 0)
                        {$avatarsjoin}
                    LEFT JOIN %tp%users_ranks AS r ON(r.rankid = u.rankid)
                    WHERE u.uniqidkey=" . $db->quote( Cookie::get( 'uhash' ) ) . ' GROUP BY u.userid';
            }
        }


        return $db->query( $sql )->fetch();
    }

    /**
     *
     * @return array
     */
    static public function loadDefaultGroup()
    {
        $cached = Cache::get( 'default_usergroup', 'data' );
        if ( is_null( $cached ) )
        {
            $db     = Database::getInstance();
            $sql    = "SELECT groupid, permissions, dashboard, grouptype, title AS grouptitle, editorsettings FROM %tp%users_groups WHERE default_group=1";
            $cached = $db->query( $sql )->fetch();
            Cache::write( 'default_usergroup', $cached, 'data' );
        }

        return $cached;
    }

    /**
     *
     * @return string
     */
    static public function getLanguage()
    {
        if ( is_null( self::$is_logged_in ) )
        {
            self::loadFromSession();
        }

        return self::$language;
    }

    /**
     *
     * @param integer $id
     * @return boolean
     */
    static private function skinExist($id = 0)
    {
        if ( !$id )
        {
            return false;
        }
        $db = Database::getInstance();
        $r  = $db->select( 'id' )
            ->from( '%tp%skins' )
            ->where( 'pageid', '=', (int)PAGEID )
            ->where( 'id', '=', $id )
            ->get();

        if ( $r[ 'id' ] )
        {
            self::$styleid  = (int)$r[ 'id' ];
            self::$template = isset($r[ 'templates' ]) ? $r[ 'templates' ] : false; // the template folder
            Session::save( 'skinid', self::$styleid );
        }


        return ( $r[ 'id' ] > 0 ? true : false );
    }

    /**
     *
     * @throws BaseException
     * @return integer
     */
    static public function getSkinId()
    {

        if ( !(int)self::$styleid )
        {
            $db = Database::getInstance();
            $r  = $db->select( 'id' )
                ->from( '%tp%skins' )
                ->where( 'pageid', '=', (int)PAGEID )
                ->where( 'default_set', '=', 1 )
                ->get();

            //$r = $db->query( 'SELECT id FROM %tp%skins WHERE pageid = ? AND default_set = 1 LIMIT 1', PAGEID )->fetch();
            self::$styleid = (int)$r[ 'id' ];

            Session::save( 'skinid', self::$styleid );
        }


        if ( !self::$styleid )
        {
            trigger_error( 'Can not find the Skin.', E_USER_ERROR );
        }

        return ( self::$styleid ? self::$styleid : null );
    }

    /**
     * get the template folder name
     * @return string
     */
    static public function getTemplate()
    {
        if ( !(int)self::$styleid )
        {
            $db = Database::getInstance();

            $r              = $db->query( 'SELECT templates FROM %tp%skins WHERE pageid = ? AND default_set = 1 LIMIT 1', PAGEID )->fetch();
            self::$template = $r[ 'templates' ]; // the template folder
            $r              = null;
        }
        else
        {

            if ( !self::$template )
            {
                $db             = Database::getInstance();
                $r              = $db->query( 'SELECT templates FROM %tp%skins WHERE pageid = ? AND id = ? LIMIT 1', PAGEID, self::$styleid )->fetch();
                self::$template = $r[ 'templates' ]; // the template folder
            }
        }


        return ( self::$template ? self::$template : 'default' );
    }

    /**
     *
     * @return array
     */
    static public function loadSkin()
    {
        if ( is_array( self::$skinData ) )
        {
            return self::$skinData;
        }


        $id = self::getSkinId();

        $db = Database::getInstance();

        if ( !$id )
        {
            $skin = Cache::get( 'skin-default' );
            if ( $skin === null )
            {
                $skin = $db->query( 'SELECT * FROM %tp%skins WHERE pageid = ? AND default_set=1 LIMIT 1', PAGEID )->fetch();
                Cache::write( 'skin-default', $skin );
            }
        }
        else
        {
            $skin = Cache::get( 'skin-' . $id );

            if ( $skin === null )
            {
                $skin = $db->query( "SELECT * FROM %tp%skins WHERE pageid = ? AND id = ?", PAGEID, $id )->fetch();
                Cache::write( 'skin-' . $id, $skin );
            }
        }

        # die("$id ". Session::get('skinid'));

        self::$skinData = $skin;

        return self::$skinData;
    }

    /**
     *
     * @return boolean
     */
    static public function getEditorSettings()
    {
        if ( is_null( self::$is_logged_in ) )
        {
            self::loadFromSession();
        }

        if ( is_string( self::$userdata[ 'editorsettings' ] ) && !empty( self::$userdata[ 'editorsettings' ] ) )
        {
            self::$userdata[ 'editorsettings' ] = unserialize( self::$userdata[ 'editorsettings' ] );
        }

        return is_array( self::$userdata[ 'editorsettings' ] ) ? self::$userdata[ 'editorsettings' ] : false;
    }

    /**
     *
     * @return boolean
     */
    static public function isLoggedIn()
    {
        if ( is_null( self::$is_logged_in ) )
        {
            self::loadFromSession();
        }

        return self::$is_logged_in;
    }

    /**
     *
     * @return string
     */
    static public function getUserUiqKey()
    {
        if ( is_null( self::$is_logged_in ) )
        {
            self::loadFromSession();
        }

        return self::$uniqidkey;
    }

    /**
     *
     * @return integer
     */
    static public function getUserId()
    {
        if ( is_null( self::$is_logged_in ) )
        {
            self::loadFromSession();
        }

        return self::$user_id;
    }

    /**
     *
     * @return string
     */
    static public function getUsername()
    {
        if ( is_null( self::$is_logged_in ) )
        {
            self::loadFromSession();
        }

        return self::$username;
    }

    /**
     *
     * @return integer
     */
    static public function getGroupId()
    {
        if ( is_null( self::$is_logged_in ) )
        {
            self::loadFromSession();
        }

        return self::$usergroupid;
    }

    /**
     *
     * @return boolean
     */
    static public function isAdmin()
    {
        if ( is_null( self::$is_logged_in ) )
        {
            self::loadFromSession();
        }

        return self::$isAdmin;
    }

    /**
     *
     * @return type
     */
    static public function groupType()
    {
        if ( is_null( self::$is_logged_in ) )
        {
            self::loadFromSession();
        }

        return self::$groupType;
    }


    /**
     *
     * @return boolean
     */
    static public function loadFromSession()
    {


        // check for current session
        if ( Session::get( 'userid', 0 ) > 0 && Session::get( 'username', '' ) != '' && Session::get( 'password', '' ) != '' )
        {

            self::$usergroupid = (int)Session::get( 'groupid' );
            self::$user_id     = (int)Session::get( 'userid' );
            self::$username    = Session::get( 'username' );
            self::$password    = Session::get( 'password' );


            self::$userdata = self::initUserData( false );

            $valid = false;

            // Use PasswordHash Class
            if ( isset( self::$userdata[ 'userid' ] ) && self::$userdata[ 'userid' ] > 0 && self::$userdata[ 'password' ] === self::$password )
            {
                $valid = true;
            }

            if ( !$valid )
            {
                self::$is_logged_in             = false;
                self::$isAdmin                  = false;
                self::$user_id                  = 0;
                self::$userdata                 = self::loadDefaultGroup();
                self::$usergroupid              = self::$userdata[ 'groupid' ];
                self::$userdata[ 'dateformat' ] = Settings::get( 'dateformat', 'd.m.Y' );
                self::$userdata[ 'timeformat' ] = Settings::get( 'timeformat', 'H:i' );

                if ( Session::get( 'skinid', 0 ) > 0 )
                {
                    self::$styleid = Session::get( 'skinid', 0 );

                    if ( !self::skinExist( self::$styleid ) )
                    {
                        self::$styleid = null;
                    }
                }

                if ( is_null( self::$styleid ) )
                {
                    self::getSkinId();
                }

                self::$groupType = self::$userdata[ 'grouptype' ];

                self::$permissions         = !empty( self::$userdata[ 'permissions' ] ) ? unserialize( self::$userdata[ 'permissions' ] ) : array();
                self::$special_permissions = !empty( self::$userdata[ 'specialperms' ] ) ? unserialize( self::$userdata[ 'specialperms' ] ) : '';

                if ( is_array( self::$special_permissions ) && count( self::$special_permissions ) > 0 )
                {


                    foreach ( self::$special_permissions as $k => $v )
                    {
                        if ( is_array( $v ) )
                        {
                            foreach ( $v as $kk => $vv )
                            {
                                if ( isset( self::$permissions[ $k ][ $kk ] ) && !is_null( $vv ) )
                                {
                                    self::$permissions[ $k ][ $kk ] = $vv;
                                }
                            }
                        }
                        else
                        {
                            if ( isset( self::$special_permissions[ $k ] ) && !is_null( self::$special_permissions[ $k ] ) )
                            {
                                self::$permissions[ $k ] = self::$special_permissions[ $k ];
                            }
                        }
                    }
                    //self::$permissions = Library::arrayMergeReplaceRecursive( self::$permissions, self::$special_permissions );
                }

                self::$forum_permissions       = !empty( self::$permissions[ 'forumpermissions' ] ) ? unserialize( self::$permissions[ 'forumpermissions' ] ) : array();
                self::$group_forum_permissions = (int)self::$permissions[ 'groupforumpermissions' ];

                if ( !Session::get( 'sessionstart' ) )
                {
                    Session::save( 'sessionstart', time() );
                }

                Session::save( 'userid', 0 );
                Session::save( 'groupid', self::$usergroupid );

                return false;
            }


            if ( is_string( self::$userdata[ 'editorsettings' ] ) && self::$userdata[ 'editorsettings' ] != '' )
            {
                self::$userdata[ 'editorsettings' ] = unserialize( self::$userdata[ 'editorsettings' ] );
            }


            self::$uniqidkey = self::$userdata[ 'uniqidkey' ];
            self::$isAdmin   = self::$userdata[ 'dashboard' ] ? true : false;
            self::$groupType = self::$userdata[ 'grouptype' ];

            self::$is_logged_in = true;
            self::$language     = self::$userdata[ 'language' ];

            self::$permissions             = isset( self::$userdata[ 'permissions' ] ) && is_string( self::$userdata[ 'permissions' ] ) ? unserialize( self::$userdata[ 'permissions' ] ) : array();
            self::$special_permissions     = isset( self::$userdata[ 'specialperms' ] ) && is_string( self::$userdata[ 'specialperms' ] ) ? unserialize( self::$userdata[ 'specialperms' ] ) : array();
            self::$forum_permissions       = isset( self::$userdata[ 'forumpermissions' ] ) && is_string( self::$userdata[ 'forumpermissions' ] ) ? unserialize( self::$permissions[ 'forumpermissions' ] ) : array();
            self::$group_forum_permissions = isset( self::$permissions[ 'groupforumpermissions' ] ) ? (int)self::$permissions[ 'groupforumpermissions' ] : 0;


            self::getPhoto();


            if ( is_array( self::$special_permissions ) && count( self::$special_permissions ) > 0 )
            {
                foreach ( self::$special_permissions as $k => $v )
                {
                    if ( is_array( $v ) )
                    {
                        foreach ( $v as $kk => $vv )
                        {
                            if ( isset( self::$permissions[ $k ][ $kk ] ) && !is_null( $vv ) )
                            {
                                self::$permissions[ $k ][ $kk ] = $vv;
                            }
                        }
                    }
                    else
                    {
                        if ( isset( self::$special_permissions[ $k ] ) && !is_null( self::$special_permissions[ $k ] ) )
                        {
                            self::$permissions[ $k ] = self::$special_permissions[ $k ];
                        }
                    }
                }


                //self::$permissions = Library::arrayMergeReplaceRecursive( self::$permissions, self::$special_permissions );
            }

            if ( !Session::get( 'sessionstart' ) )
            {
                Session::save( 'sessionstart', time() );
            }

            if ( defined( 'ADM_SCRIPT' ) )
            {
                Session::save( "expiry", time() + Settings::get( 'adminsession_timeout', 3600 ) );
            }
            else
            {

                Session::save( "expiry", time() + Settings::get( 'usersession_timeout', 2600 ) );
            }

            return true;
        }

        // check for persistant session
        if ( Cookie::get( 'uhash', false ) )
        {
            return self::login( false, false, Cookie::get( 'uhash' ) );
        }

        self::$is_logged_in             = false;
        self::$isAdmin                  = false;
        self::$user_id                  = 0;
        self::$userdata                 = self::loadDefaultGroup();
        self::$usergroupid              = self::$userdata[ 'groupid' ];
        self::$userdata[ 'dateformat' ] = Settings::get( 'dateformat' );
        self::$userdata[ 'timeformat' ] = Settings::get( 'timeformat' );
        self::$groupType                = self::$userdata[ 'grouptype' ];

        if ( Session::get( 'skinid', 0 ) > 0 )
        {
            self::$styleid = Session::get( 'skinid', 0 );

            if ( !self::skinExist( self::$styleid ) )
            {
                self::$styleid = null;
            }
        }

        if ( is_null( self::$styleid ) )
        {
            self::getSkinId();
        }


        self::$permissions         = !empty( self::$userdata[ 'permissions' ] ) ? unserialize( self::$userdata[ 'permissions' ] ) : array();
        self::$special_permissions = !empty( self::$userdata[ 'specialperms' ] ) ? unserialize( self::$userdata[ 'specialperms' ] ) : '';


        if ( is_array( self::$special_permissions ) && count( self::$special_permissions ) > 0 )
        {


            foreach ( self::$permissions as $k => $v )
            {
                if ( isset( self::$special_permissions[ $k ] ) && !is_null( self::$special_permissions[ $k ] ) )
                {
                    self::$permissions[ $k ] = self::$special_permissions[ $k ];
                }
            }


            //self::$permissions = Library::arrayMergeReplaceRecursive( self::$permissions, self::$special_permissions );
        }

        self::$forum_permissions       = !empty( self::$permissions[ 'forumpermissions' ] ) ? unserialize( self::$permissions[ 'forumpermissions' ] ) : array();
        self::$group_forum_permissions = isset( self::$permissions[ 'groupforumpermissions' ] ) ? (int)self::$permissions[ 'groupforumpermissions' ] : 0;

        if ( !Session::get( 'sessionstart' ) )
        {
            Session::save( 'sessionstart', time() );
        }

        Session::save( 'userid', 0 );
        Session::save( 'groupid', self::$usergroupid );

        return false;
    }

    /**
     *
     * @param bool|int $userid
     * @return array
     */
    static public function getUserById($userid = false)
    {

        if ( $userid === false || !(int)$userid )
        {
            Error::raise( 'The Userid not exists!' );
        }

        $db = Database::getInstance();

        $avatars     = ( Settings::get( 'showavatar' ) ? '
                                                    avatar.avatarname,
                                                    avatar.avatarextension,
                                                    avatar.width AS avatarwidth,
                                                    avatar.height AS avatarheight,
                                                    avatar.userid AS avatarowner,
                                                    NOT ISNULL(customavatar.avatarname) AS hascustomavatar,' : '' );
        $avatarsjoin = ( Settings::get( 'showavatar' ) ? "
                            LEFT JOIN %tp%avatars AS avatar ON(avatar.avatarid = u.avatarid)
                            LEFT JOIN %tp%avatars AS customavatar ON(customavatar.userid = u.userid) " : '' );

        $sql = "SELECT u.*, up.permissions AS specialperms,
                    {$avatars}
                    g.title AS grouptitle, g.permissions, g.dashboard, g.grouptype
				FROM %tp%users AS u
                LEFT JOIN %tp%users_access AS up ON(up.userid=u.userid)
                LEFT JOIN %tp%avatars AS a ON(a.avatarid=u.avatarid)
				LEFT JOIN %tp%users_groups AS g ON(g.groupid=u.groupid)
                    {$avatarsjoin}
				WHERE u.userid = ?";

        $result = $db->query( $sql, $userid )->fetch();

        $result[ 'permissions' ]  = !empty( $result[ 'permissions' ] ) ? unserialize( $result[ 'permissions' ] ) : array();
        $result[ 'specialperms' ] = !empty( $result[ 'specialperms' ] ) ? unserialize( $result[ 'specialperms' ] ) : array();
        foreach ( $result[ 'specialperms' ] as $k => $v )
        {
            if ( is_array( $v ) )
            {
                foreach ( $v as $kk => $vv )
                {
                    if ( isset( $result[ 'permissions' ][ $k ][ $kk ] ) && !is_null( $vv ) )
                    {
                        $result[ 'permissions' ][ $k ][ $kk ] = $vv;
                    }
                }
            }
            else
            {
                if ( isset( $result[ 'specialperms' ][ $k ] ) && !is_null( $result[ 'specialperms' ][ $k ] ) )
                {
                    $result[ 'permissions' ][ $k ] = $result[ 'specialperms' ][ $k ];
                }
            }
        }
        if ( !empty( $result[ 'editorsettings' ] ) )
        {
            $result[ 'editorsettings' ] = unserialize( $result[ 'editorsettings' ] );
        }

        return $result;
    }

    /**
     *
     * @param bool|string $username
     * @return array
     */
    static public function getUserByUsername($username = false)
    {
        if ( $username === false || !trim( $username ) )
        {
            Error::raise( 'The Username not exists!' );
        }

        $db = Database::getInstance();


        $avatars     = ( Settings::get( 'showavatar' ) ? '
                                                    avatar.avatarname,
                                                    avatar.avatarextension,
                                                    avatar.width AS avatarwidth,
                                                    avatar.height AS avatarheight,
                                                    avatar.userid AS avatarowner,
                                                    NOT ISNULL(customavatar.avatarname) AS hascustomavatar,' : '' );
        $avatarsjoin = ( Settings::get( 'showavatar' ) ? "
                            LEFT JOIN %tp%avatars AS avatar ON(avatar.avatarid = u.avatarid)
                            LEFT JOIN %tp%avatars AS customavatar ON(customavatar.userid = u.userid) " : '' );

        $sql = "SELECT u.*, up.permissions AS specialperms, {$avatars}
                    g.title AS grouptitle,
                    g.permissions,
                    g.dashboard, g.grouptype
		FROM %tp%users AS u
                LEFT JOIN %tp%users_access AS up ON(up.userid=u.userid)
		LEFT JOIN %tp%users_groups AS g ON(g.groupid=u.groupid)
                
                {$avatarsjoin}
				WHERE LOWER(u.username) = " . $db->quote( strtolower( $username ) );

        $result = $db->query( $sql )->fetch();

        $result[ 'permissions' ]  = !empty( $result[ 'permissions' ] ) ? unserialize( $result[ 'permissions' ] ) : array();
        $result[ 'specialperms' ] = !empty( $result[ 'specialperms' ] ) ? unserialize( $result[ 'specialperms' ] ) : array();
        foreach ( $result[ 'specialperms' ] as $k => $v )
        {
            if ( is_array( $v ) )
            {
                foreach ( $v as $kk => $vv )
                {
                    if ( isset( $result[ 'permissions' ][ $k ][ $kk ] ) && !is_null( $vv ) )
                    {
                        $result[ 'permissions' ][ $k ][ $kk ] = $vv;
                    }
                }
            }
            else
            {
                if ( isset( $result[ 'specialperms' ][ $k ] ) && !is_null( $result[ 'specialperms' ][ $k ] ) )
                {
                    $result[ 'permissions' ][ $k ] = $result[ 'specialperms' ][ $k ];
                }
            }
        }
        if ( !empty( $result[ 'editorsettings' ] ) )
        {
            $result[ 'editorsettings' ] = unserialize( $result[ 'editorsettings' ] );
        }

        return $result;
    }

    /**
     *
     * @param bool $mail
     * @internal param string $username
     * @return array
     */
    static public function getUserByEmail($mail = false)
    {
        if ( $mail === false || !trim( $mail ) )
        {
            Error::raise( 'The User not exists!' );
        }

        $db          = Database::getInstance();
        $avatars     = ( Settings::get( 'showavatar' ) ? '
                                                    avatar.avatarname,
                                                    avatar.avatarextension,
                                                    avatar.width AS avatarwidth,
                                                    avatar.height AS avatarheight,
                                                    avatar.userid AS avatarowner,
                                                    NOT ISNULL(customavatar.avatarname) AS hascustomavatar,' : '' );
        $avatarsjoin = ( Settings::get( 'showavatar' ) ? "
                            LEFT JOIN %tp%avatars AS avatar ON(avatar.avatarid = u.avatarid)
                            LEFT JOIN %tp%avatars AS customavatar ON(customavatar.userid = u.userid) " : '' );

        $sql = "SELECT u.*, up.permissions AS specialperms, {$avatars}
                    g.title AS grouptitle,
                    g.permissions,
                    g.dashboard, g.grouptype
		FROM %tp%users AS u
                LEFT JOIN %tp%users_access AS up ON(up.userid=u.userid)
		LEFT JOIN %tp%users_groups AS g ON(g.groupid=u.groupid)                
                {$avatarsjoin}
				WHERE u.email = ?";

        $result                   = $db->query( $sql, $mail )->fetch();
        $result[ 'permissions' ]  = !empty( $result[ 'permissions' ] ) ? unserialize( $result[ 'permissions' ] ) : array();
        $result[ 'specialperms' ] = !empty( $result[ 'specialperms' ] ) ? unserialize( $result[ 'specialperms' ] ) : array();

        foreach ( $result[ 'specialperms' ] as $k => $v )
        {
            if ( is_array( $v ) )
            {
                foreach ( $v as $kk => $vv )
                {
                    if ( isset( $result[ 'permissions' ][ $k ][ $kk ] ) && !is_null( $vv ) )
                    {
                        $result[ 'permissions' ][ $k ][ $kk ] = $vv;
                    }
                }
            }
            else
            {
                if ( isset( $result[ 'specialperms' ][ $k ] ) && !is_null( $result[ 'specialperms' ][ $k ] ) )
                {
                    $result[ 'permissions' ][ $k ] = $result[ 'specialperms' ][ $k ];
                }
            }
        }
        if ( !empty( $result[ 'editorsettings' ] ) )
        {
            $result[ 'editorsettings' ] = unserialize( $result[ 'editorsettings' ] );
        }

        return $result;
    }

    /**
     * @param $q
     * @return mixed
     */
    static public function findUser($q)
    {

        $db          = Database::getInstance();
        $avatars     = ( Settings::get( 'showavatar' ) ? '
                                                    avatar.avatarname,
                                                    avatar.avatarextension,
                                                    avatar.width AS avatarwidth,
                                                    avatar.height AS avatarheight,
                                                    avatar.userid AS avatarowner,
                                                    NOT ISNULL(customavatar.avatarname) AS hascustomavatar,' : '' );
        $avatarsjoin = ( Settings::get( 'showavatar' ) ? "
                            LEFT JOIN %tp%avatars AS avatar ON(avatar.avatarid = u.avatarid)
                            LEFT JOIN %tp%avatars AS customavatar ON(customavatar.userid = u.userid) " : '' );

        $sql = "SELECT u.*, up.permissions AS specialperms, {$avatars}
                    g.title AS grouptitle,
                    g.permissions,
                    g.dashboard, 
                    g.grouptype
		FROM %tp%users AS u
                LEFT JOIN %tp%users_access AS up ON(up.userid=u.userid)
		LEFT JOIN %tp%users_groups AS g ON(g.groupid=u.groupid)                
                {$avatarsjoin}
		WHERE u.username LIKE " . $db->quote( '%' . $q . '%' ) . " ORDER BY u.username ASC LIMIT 50";

        return $db->query( $sql )->fetchAll();
    }

    /**
     *
     * @return array
     */
    static public function getDashboardUsers()
    {
        $db = Database::getInstance();

        $sql = "SELECT u.userid, u.username, u.groupid, u.name, u.lastname, up.permissions AS specialperms,
                    g.title AS grouptitle,
                    g.permissions,
                    g.dashboard, g.grouptype
				FROM %tp%users AS u
                LEFT JOIN %tp%users_access AS up ON(up.userid=u.userid)
				LEFT JOIN %tp%users_groups AS g ON(g.groupid=u.groupid)
				WHERE g.dashboard = 1 ORDER BY u.username, u.name";

        return $db->query( $sql )->fetchAll();
    }

    /**
     * get current user dashboard settings
     * @return array
     */
    static public function getPersonalSettings()
    {
        if ( isset( $GLOBALS[ 'personal_settings' ] ) && is_array( $GLOBALS[ 'personal_settings' ] ) )
        {
            return $GLOBALS[ 'personal_settings' ];
        }

        if ( is_null( self::$is_logged_in ) )
        {
            self::loadFromSession();
        }

        $a                              = new Personal;
        $GLOBALS[ 'personal_settings' ] = $a->get( 'personal', 'settings', array(
                'gridlimit'          => 20,
                'contenttranslation' => Locales::getLocaleId(),
                'guilanguage'        => 'de_DE',
                'wysiwyg'            => 'tinymce',
                'skin'               => 'default',
                'desktopbackground'  => ''
            )
        );


        if ( !is_array( $GLOBALS[ 'personal_settings' ] ) && $GLOBALS[ 'personal_settings' ] )
        {
            $GLOBALS[ 'personal_settings' ] = array(
                'gridlimit'          => 20,
                'contenttranslation' => Locales::getLocaleId(),
                'guilanguage'        => 'de_DE',
                'wysiwyg'            => 'tinymce',
                'skin'               => 'default',
                'desktopbackground'  => ''
            );
        }


        if ( !$GLOBALS[ 'personal_settings' ][ 'desktopbackground' ] || !isset( $GLOBALS[ 'personal_settings' ][ 'desktopbackground' ] ) )
        {
            $GLOBALS[ 'personal_settings' ][ 'desktopbackground' ] = 'galaxy.jpg';
        }

        $currentTrans = Session::get( 'contenttranslation' );


        if ( !$currentTrans )
        {
            $db                                               = Database::getInstance();
            $GLOBALS[ 'personal_settings' ][ 'contenttrans' ] = $db->query( 'SELECT title, flag, id, code FROM %tp%locale WHERE id = ?', $GLOBALS[ 'personal_settings' ][ 'contenttranslation' ] )->fetch();


            Session::save( 'contenttranslation', (int)$GLOBALS[ 'personal_settings' ][ 'contenttranslation' ] );
        }
        elseif ( $currentTrans > 0 )
        {
            $db                                               = Database::getInstance();
            $GLOBALS[ 'personal_settings' ][ 'contenttrans' ] = $db->query( 'SELECT title, flag, id, code FROM %tp%locale WHERE id = ?', $currentTrans )->fetch();
            Session::save( 'contenttranslation', (int)$currentTrans );
        }

        if ( !isset( $GLOBALS[ 'personal_settings' ][ 'contenttrans' ][ 'code' ] ) )
        {
            $db                                               = Database::getInstance();
            $GLOBALS[ 'personal_settings' ][ 'contenttrans' ] = $db->query( 'SELECT title, flag, id, code FROM %tp%locale WHERE contentlanguage = 1 LIMIT 1' )->fetch();

            Session::save( 'contenttranslation', (int)$GLOBALS[ 'personal_settings' ][ 'contenttrans' ][ 'id' ] );
        }

        $GLOBALS[ 'GUI_LOCALE' ] = $GLOBALS[ 'personal_settings' ][ 'guilanguage' ];


        # if (!isset($GLOBALS['personal_settings']['contenttrans']))
        # {
        return $GLOBALS[ 'personal_settings' ];
        # }
    }

    /**
     *
     * @return boolean
     */
    static public function getUserGender()
    {
        if ( is_null( self::$is_logged_in ) )
        {
            self::loadFromSession();
        }

        return self::$isAdmin;
    }

    /**
     *
     * @param array $user
     * @return string
     */
    static public function getUserPhoto($user)
    {
        $avatarpath     = PAGE_PATH . 'avatars';
        $useravatarpath = PAGE_PATH . 'upload/userfiles';

        $upload_path    = trim( trim( UPLOAD_PATH ) . 'userfiles/userphotos/' . $user[ 'userid' ] );
        $upload_urlpath = trim( trim( $upload_path ) . 'userfiles/userphotos/' . $user[ 'userid' ] );
        $upload_urlpath = str_replace( ROOT_PATH, '', $upload_path );

        if ( isset( $user[ 'hascustomavatar' ] ) && !$user[ 'hascustomavatar' ] && is_file( ROOT_PATH . HTML_URL . "img/avatars/avatar-" . $user[ 'avatarid' ] . '.' . $user[ 'avatarextension' ] ) )
        {
            return HTML_URL . "img/avatars/avatar-" . $user[ 'avatarid' ] . '.' . $user[ 'avatarextension' ];
        }
        elseif ( isset( $user[ 'hascustomavatar' ] ) && $user[ 'hascustomavatar' ] && is_file( $upload_path . '/' . $user[ 'avatarname' ] . '.' . $user[ 'avatarextension' ] ) )
        {
            return $upload_urlpath . '/' . $user[ 'avatarname' ] . '.' . $user[ 'avatarextension' ];
        }
        else
        {
            return HTML_URL . 'img/nophoto.gif';
        }
    }

    /**
     *
     * @return string
     */
    static public function getPhoto()
    {
        if ( self::get( 'userphoto' ) )
        {
            return self::get( 'userphoto' );
        }

        if ( is_null( self::$is_logged_in ) )
        {
            self::loadFromSession();
        }

        $avatarpath     = PAGE_PATH . 'avatars';
        $useravatarpath = PAGE_PATH . 'upload/userfiles';
        $upload_path    = trim( trim( UPLOAD_PATH ) . 'userfiles/userphotos/' . self::get( 'userid' ) );
        $upload_urlpath = trim( trim( $upload_path ) . 'userfiles/userphotos/' . self::get( 'userid' ) );
        $upload_urlpath = str_replace( ROOT_PATH, '', $upload_path );

        if ( !self::get( 'hascustomavatar' ) && is_file( ROOT_PATH . HTML_URL . "img/avatars/avatar-" . self::get( 'avatarid' ) . '.' . self::get( 'avatarextension' ) ) )
        {
            $userphoto = HTML_URL . "img/avatars/avatar-" . self::get( 'avatarid' ) . '.' . self::get( 'avatarextension' );
        }
        elseif ( self::get( 'hascustomavatar' ) && is_file( $upload_path . '/' . self::get( 'avatarname' ) . '.' . self::get( 'avatarextension' ) ) )
        {
            $userphoto = $upload_urlpath . '/' . self::get( 'avatarname' ) . '.' . self::get( 'avatarextension' );
        }
        else
        {
            $userphoto = HTML_URL . 'img/nophoto.gif';
        }

        self::setUserData( 'userphoto', $userphoto );

        return $userphoto;
    }

    /**
     * @param string $email
     * @param int $size
     * @param bool $default
     * @param string $rating
     * @return string
     */
    static public function gravatar($email = "", $size = 56, $default = false, $rating = "pg")
    {
        if (!$default)
        {
            $default = "http://www.gravatar.com/avatar/00000000000000000000000000000000";
        }

        return "http://www.gravatar.com/avatar/" . md5(strtolower($email)) . "?s=" . $size . "&d=" . urlencode($default) . "&rating=" . $rating;
    }

    /**
     *
     * @return array
     */
    static public function getAllUserPhotos()
    {
        if ( is_null( self::$is_logged_in ) )
        {
            self::loadFromSession();
        }

        $useravatarpath = PAGE_PATH . 'upload/userfiles/userphotos/' . self::get( 'userid' ) . '/*.*';

        $fs    = glob( $useravatarpath );
        $files = array();
        foreach ( $fs as $file )
        {
            $file     = str_replace( PAGE_PATH, '', $file );
            $files[ ] = array(
                'file' => $file);
        }

        return $files;
    }

    /**
     *
     * @param bool|int $gender
     * @throws BaseException
     * @return string
     */
    static public function getGender($gender = false)
    {


        if ( $gender === false )
        {
            trigger_error( 'The gender not set!', E_USER_ERROR );
        }

        if ( $gender === 0 )
        {
            return trans( 'Ohne Angabe' );
        }
        else if ( $gender === 1 )
        {
            return trans( 'Männlich' );
        }
        else if ( $gender === 2 )
        {
            return trans( 'Weiblich' );
        }
        else if ( $gender === 3 )
        {
            return trans( 'Zwitter' );
        }

        return false;
    }

    /**
     *
     * @param type $rank
     * @return string
     */
    static public function getRankImage(&$rank)
    {
        if ( empty( $rank[ 'rankimages' ] ) )
        {
            return '-';
        }

        $path = HTML_URL . 'img/ranks/';

        if ( stripos( $rank[ 'rankimages' ], '[imagefolder]' ) === false )
        {
            $rank[ 'rankimages' ] = $path . $rank[ 'rankimages' ];
        }
        else
        {
            $rank[ 'rankimages' ] = str_replace( '[imagefolder]', Skin::getDefaultSkinImgPath(), $rank[ 'rankimages' ] );
        }

        $rank[ 'repeats' ] = (int)$rank[ 'repeats' ] > 0 ? (int)$rank[ 'repeats' ] : 1;

        $img = '';
        for ( $i = 0; $i < $rank[ 'repeats' ]; $i++ )
        {
            $img .= '<img src="' . $rank[ 'rankimages' ] . '" alt=""/>';
        }

        return $img;
    }

    /**
     * get a user var from template
     * @param bool|\type $var
     * @return type
     */
    static public function get($var = false)
    {
        self::isLoggedIn();

        return ( isset( self::$userdata[ $var ] ) ? self::$userdata[ $var ] : null );
    }

    /**
     *
     * @param string $var
     * @param boolean $value default is null and will remove the permission
     * @throws BaseException
     */
    public static function setPerm($var, $value = null)
    {
        self::isLoggedIn();

        if ( strpos( $var, '/' ) !== false )
        {
            list( $section, $var ) = explode( '/', $var );
        }
        else
        {
            if ( DEV_MODE !== true )
            {
                throw new BaseException( 'Permission Key must write with "/" (section/here the key)' );
            }
        }

        if ( !isset( self::$permissions[ $section ][ $var ] ) )
        {
            throw new BaseException( sprintf( 'The permission section `%s` width option `%s` not exists in the permission registry!', $section, $var ) );
        }


        self::$permissions[ $section ][ $var ] = $value;
    }

    /**
     * Will check Permissions for a User.
     * Not for the current user!
     * @param string $perm
     * @param array /string/integer $data if is a string or integer then search the username in the database
     *
     * @param boolean $default default is false
     * @return boolean
     *
     * @throws BaseException
     */
    static public function Allowed($perm, $data, $default = false)
    {

        if ( is_null( self::$is_logged_in ) )
        {
            self::loadFromSession();
        }

        if ( strpos( $perm, '/' ) !== false )
        {
            list( $section, $var ) = explode( '/', $perm );
        }
        else
        {
            if ( DEV_MODE !== true )
            {
                trigger_error( 'Permission Key must write with "/" (section/here the key)', E_USER_ERROR );
            }
        }

        $permKeys = Usergroup::getGroupPermissionKeys( 'usergroup' );

        if ( !isset( $permKeys[ $section ] ) )
        {
            if ( DEV_MODE !== true )
            {
                trigger_error( 'Permission Section "' . $section . '" not found', E_USER_ERROR );
            }
        }

        if ( is_string( $data ) && !empty( $data ) )
        {
            $data = self::getUserByUsername( $data );
        }
        else if ( is_int( $data ) && (int)$data )
        {
            $data = self::getUserById( (int)$data );
        }


        if ( isset( $data[ 'specialperms' ][ $section ][ $var ] ) && $data[ 'specialperms' ][ $section ][ $var ] )
        {
            return true;
        }


        if ( isset( $data[ 'permissions' ][ $section ][ $var ] ) && $data[ 'permissions' ][ $section ][ $var ] )
        {
            return true;
        }

        return $default;
    }

    /**
     * Will check Permissions for the current User.
     * @param $inputvar
     * @param boolean $default default is false
     * @throws BaseException
     * @internal param string $var
     * @return boolean
     *
     */
    static public function hasPerm($inputvar, $default = false)
    {


        if (!trim($inputvar)) {
            throw new BaseException(  'Permission Key is not set' );
        }

        if ( is_null( self::$is_logged_in ) )
        {
            self::loadFromSession();
        }


        self::isLoggedIn();



        static $isPlugin;

        if ( !is_bool( $isPlugin ) )
        {
            $isPlugin = defined( 'PLUGIN' );
        }


        if ( strpos( $inputvar, '/' ) !== false )
        {
            list( $section, $var ) = explode( '/', $inputvar );


        }
        else
        {
            if ( DEV_MODE === true )
            {
               // throw new BaseException( ( $isPlugin ? 'Plugin ' : '' ) . 'Permission Key must write with "/" (section/here the key)' );
            }
        }



        $section       = strtolower( $section );
        $pluginSection = false;

        if ( $isPlugin && substr( $section, 0, 7 ) != 'plugin_' )
        {
            $pluginSection = 'plugin_' . $section;
        }


        $permKeys = Usergroup::getGroupPermissionKeys( 'usergroup' );

        // print_r($permKeys); exit;




        if ( !isset( $permKeys[ $section ] ) && !isset( $permKeys[ $pluginSection ] ) )
        {
            if ( DEV_MODE !== true && !$default )
            {
                #throw new BaseException( ($isPlugin ? 'Plugin ' : '') . 'Permission Section "' .  ($isPlugin ? $pluginSection : $section) . '" not found', E_USER_ERROR );
            }
            else
            {
                return $default;
            }
        }

        /*
          if ( defined( strtoupper( $var ) ) && !is_null( self::$forum_permissions ) )
          {
          return (self::$forum_permissions[ HTTP::input( 'forumid' ) ] & strtoupper( $var ));
          }
         */

        $foundDefault = false;

        if ( isset( $permKeys[ $pluginSection ][ $var ][ 'default' ] ) && $default === null )
        {
            $default      = (int)$permKeys[ $pluginSection ][ $var ][ 'default' ] > 0 ? true : false;
            $foundDefault = true;
        }

        if ( !$foundDefault && isset( $permKeys[ $section ][ $var ][ 'default' ] ) && $default === null )
        {
            $default = (int)$permKeys[ $section ][ $var ][ 'default' ] > 0 ? true : false;
        }





        if ( isset( self::$permissions[ $pluginSection ][ $var ] ) )
        {
            if ( self::$permissions[ $pluginSection ][ $var ] )
            {
                return true;
            }
            else
            {
                return $default;
            }
        }


        if ( !empty( self::$permissions[ $section ][ $var ] ) )
        {
            return true;
        }
        else
        {
            if ( DEV_MODE === true )
            {
                #     return true;
            }

            return $default;
        }
    }

    /**
     *
     * @param string $var
     * @param boolean $default default is null
     * @return boolean
     * @throws BaseException
     */
    static public function getPerm($var, $default = null)
    {
        self::isLoggedIn();
        static $isPlugin;
        if ( !is_bool( $isPlugin ) )
        {
            $isPlugin = defined( 'PLUGIN' );
        }


        if ( strpos( $var, '/' ) !== false )
        {
            list( $section, $var ) = explode( '/', $var );
        }
        else
        {
            if ( DEV_MODE !== true )
            {
                trigger_error( ( $isPlugin ? 'Plugin ' : '' ) . 'Permission Key must write with "/" (section/here the key)', E_USER_ERROR );
            }
        }

        if ( $isPlugin && substr( $section, 0, 7 ) != 'plugin_' )
        {
            $section = 'plugin_' . $section;
        }

        $permKeys = Usergroup::getGroupPermissionKeys( 'usergroup' );

        if ( !isset( $permKeys[ $section ] ) )
        {
            if ( DEV_MODE !== true )
            {
                trigger_error( ( $isPlugin ? 'Plugin ' : '' ) . 'Permission Section "' . $section . '" not found', E_USER_ERROR );
            }
        }


        if ( $default === null && isset( $permKeys[ $section ][ $var ][ 'default' ] ) )
        {
            $default = (int)$permKeys[ $section ][ $var ][ 'default' ] > 0 ? true : false;
        }

        if ( isset( self::$permissions[ $section ][ $var ] ) )
        {
            return self::$permissions[ $section ][ $var ];
        }

        return $default;
    }

    /**
     *
     * @param string $msn
     * @return array
     */
    public static function getMSNStatus($msn)
    {
        //http://messenger.services.live.com/users/IDYOUR_USER@apps.messenger.live.com/presence?dt=&mkt=th-TH
        $content = Library::getRemoteFile( 'http://messenger.services.live.com/users/' . $msn . '/presence?dt=&mkt=en-US' );

        if ( $content !== '' )
        {
            $rs = Library::object2array( json_decode( $content ) );
        }
        else
        {
            $rs[ 'error' ] = true;
        }

        return $rs;
    }

    /**
     *
     * @param string $yim
     * @return array
     */
    public static function getYIMStatus($yim)
    {
        $content = Library::getRemoteFile( 'http://opi.yahoo.com/online?u=' . $yim . '&m=b&t=1' );

        if ( $content !== '' )
        {
            $ret[ 'status' ] = ( trim( $content ) == '01' ? 1 : 0 );
        }
        else
        {
            $ret[ 'error' ] = true;
        }

        return $ret;
    }

    /**
     *
     * @param string $skype
     * @return array
     */
    public static function getSkypeStatus($skype)
    {
        $content = Library::getRemoteFile( 'http://mystatus.skype.com/' . $skype . '.xml' );
        if ( $content !== '' )
        {
            preg_match( '/"NUM">([\d]{1,})</i', $content, $match );
            $ret[ 'status' ] = ( $match[ 1 ] > 1 ? 1 : 0 );
        }
        else
        {
            $ret[ 'error' ] = true;
        }


        return $ret;
    }

    /**
     *
     * @param string $icq
     * @return array
     */
    public static function getICQStatus($icq)
    {
        // return array( );

        if ( !$icq )
        {
            return array();
        }


        $fp = fsockopen( "status.icq.com", 80, $errno, $errstr, 3 );
        fputs( $fp, "GET /online.gif?icq=$icq HTTP/1.0\n\n" );
        $icq_finished = false;
        while ( !feof( $fp ) && !$icq_finished )
        {
            $line = fgets( $fp, 128 );
            if ( substr( $line, 0, 9 ) === 'Location:' )
            {

                if ( strpos( $line, 'online1.gif' ) !== false )
                {
                    $online       = "1";
                    $icq_finished = true;
                }
                elseif ( strpos( $line, 'online0.gif' ) !== false )
                {
                    $online       = "0";
                    $icq_finished = true;
                }
                elseif ( strpos( $line, 'online2.gif' ) !== false )
                {
                    $online       = "2";
                    $icq_finished = true;
                }
            }
        }
        fclose( $fp );

        if ( $online === "1" )
        {

            $r[ 'status' ] = 1;
        }
        elseif ( $online === "0" )
        {
            $r[ 'status' ] = 0;
        }
        elseif ( $online === "2" )
        {
            $r[ 'status' ] = 0;
        }
        else
        {
            $r[ 'error' ] = true;
        }

        return $r;
    }

    /**
     *
     * @param integer $userid
     * @return void
     */
    static function subPostCounter($userid = 0)
    {
        if ( !$userid )
        {
            $userid = self::getUserId();
        }

        $db = Database::getInstance();
        $db->query( 'UPDATE %tp%users SET userposts = userposts + 1 WHERE userid = ?', $userid );
        $db = null;
    }

    /**
     *
     * @param integer $userid
     * @return void
     */
    static function minPostCounter($userid = 0)
    {
        if ( !$userid )
        {
            $userid = self::getUserId();
        }

        $db = Database::getInstance();
        $db->query( 'UPDATE %tp%users SET userposts = IF((userposts-1) > 0, userposts - 1, 0) WHERE userid = ?', $userid );
        $db = null;
    }

    /**
     * @var bool
     */
    private static $_disableUserLocationUpdate = false;

    static function disableUserLocationUpdate()
    {
        self::$_disableUserLocationUpdate = true;
    }

    static function enableUserLocationUpdate()
    {
        self::$_disableUserLocationUpdate = false;
    }

    static function updateUserLocation()
    {
        $nav = Library::getNavi();

        if ( !is_array( $nav ) || self::$_disableUserLocationUpdate )
        {
            return;
        }

        $nav   = Library::unempty( $nav );
        $label = array_pop( $nav );

        if ( !$label[ 0 ] || self::$_disableUserLocationUpdate )
        {
            return;
        }

        $env = new Env();
        Session::save( 'location', $env->requestUri() );
        Session::save( 'location_title', $label[ 0 ] );

        if ( !self::getUserId() || self::$_disableUserLocationUpdate )
        {
            return;
        }

        $db = Database::getInstance();
        $db->query( 'UPDATE %tp%users SET location = ?, location_title = ? WHERE userid = ?', $env->requestUri(), $label[ 0 ], self::getUserId() );
        $db = null;
    }

    /**
     *
     * @param string $location
     * @param string $title
     *
     * @todo rename field lastpost_url_caption to lastpost_title in table users
     */
    static function updateLastpost($location, $title)
    {
        $db = Database::getInstance();
        $db->query( 'UPDATE %tp%users SET lastpost_url = ?, lastpost_url_caption = ?, lastpost_timestamp = ? WHERE userid = ?', $location, $title, TIMESTAMP, self::getUserId() );
        $db = null;
    }

}

?>