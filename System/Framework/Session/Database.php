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
 * @file        Database.php
 *
 */
class Session_Database
{

    /**
     * @var bool|int
     */
    private static $mode = false;

    /**
     * @var bool
     */
    private static $inited = false;

    /**
     * @var
     */
    public static $sessionID;

    /**
     * @var int
     */
    private static $lifeTime;

    /**
     * @var null|string
     */
    private static $_ip = null;

    /**
     * @var null|string
     */
    private static $_proxyIP = null;

    /**
     * @var null|string
     */
    private static $_ua = null;

    /**
     * @var null
     */
    protected static $_db = null;

    /**
     * @param $mode
     * @param null $sessionID
     */
    public function __construct( $mode, $sessionID = null )
    {
        #parent::__construct();

        if ( !self::$inited )
        {
            self::$mode = (int)$mode ;

            self::$lifeTime = $GLOBALS[ 'SESSIONTIMEOUT' ];

            if ( empty( self::$lifeTime ) )
            {
                self::$lifeTime = 3600;
            }

            ini_set( 'session.gc_maxlifetime', self::$lifeTime );

            $_env = new Env;
            $_env->init();

            self::$_ip = $_env->ip();
            self::$_proxyIP = $_env->proxy();
            self::$_proxyIP = (self::$_proxyIP === null ? '' : self::$_proxyIP);
            self::$_ua = $_env->httpUserAgent() ? substr( $_env->httpUserAgent(), 0, 240 ) : '';
            self::$sessionID = $sessionID;

            $_env = null;


            session_set_save_handler(
                    array(
                $this,
                "open" ), array(
                $this,
                "close" ), array(
                $this,
                "read" ), array(
                $this,
                "write" ), array(
                $this,
                "destroy" ), array(
                $this,
                "gc" )
            );

            register_shutdown_function( 'session_write_close' );

            self::$inited = true;
        }
    }

    /**
     *
     * @return boolean
     */
    public function open()
    {
        self::$_db = Database::getInstance();
        return true;
    }

    /**
     *
     * @return boolean
     */
    public function close()
    {
        // unset(self::$_db);
        return true;
    }

    /**
     *
     * @param string $id
     * @return mixed
     */
    public function read( $id )
    {
        if ( !IS_SWFUPLOAD )
        {
            $s = self::$_db->query( 'SELECT `data` FROM %tp%session
            WHERE sid = ? AND ip = ? AND proxy = ? AND agent = ? AND expires > ?', $id, self::$_ip, self::$_proxyIP, self::$_ua, TIMESTAMP );
        }
        else
        {
            $s = self::$_db->query( 'SELECT `data` FROM %tp%session
            WHERE sid = ? AND expires > ?', SWFUPLOAD_SID, TIMESTAMP );
        }


        $data = '';
        if ( $s->count() === 1 )
        {
            $db_session = $s->fetch();
            $data = $db_session[ 'data' ];
        }

        return $data;
    }

    /**
     *
     * @param string $id
     * @param mixed $data
     * @return boolean
     */
    public function write( $id = null, $data = null )
    {

        if ( $id === null )
        {
            $id = Session::getId();
        }

	    $serialize = false;
	    if ( $data === null )
	    {
		    $data = Session::get() ;
		    $serialize = true;
	    }

        $expiry = (TIMESTAMP + self::$lifeTime);

	    if (self::$mode != Application::BACKEND_MODE) {
		    $location = isset($_SESSION[ INSTALL_ID ]['location']) ? $_SESSION[ INSTALL_ID ]['location'] : '';
		    $location_title = isset($_SESSION[ INSTALL_ID ]['location_title']) ? $_SESSION[ INSTALL_ID ]['location_title'] : '';
		    $userid = isset($_SESSION[ INSTALL_ID ]['userid']) ? $_SESSION[ INSTALL_ID ]['userid'] : 0;
	    }
	    else {
		    $location = '';
		    $location_title = '';
		    $userid = isset($_SESSION[ INSTALL_ID ]['userid']) ? $_SESSION[ INSTALL_ID ]['userid'] : 0;
	    }

		if ( $serialize ) {
			$data = serialize( $data );
		}

        if ( !IS_SWFUPLOAD )
        {
            $dat = self::$_db->query( 'SELECT * FROM %tp%session WHERE expires > ? AND sid = ?', TIMESTAMP, $id )->fetch();

            if ( isset( $dat[ 'sid' ] ) )
            {
                self::$_db->query( 'UPDATE %tp%session
                                    SET `data` = ?, expires = ?, location = ? , locationtitle = ?, userid = ?
                                    WHERE sid = ? AND ip = ? AND proxy = ?', $data, $expiry, $location, $location_title, $userid, $id, self::$_ip, self::$_proxyIP );
            }
            else
            {
                self::$_db->query( 'REPLACE INTO %tp%session (sid, `data`, ip, proxy, agent, expires, befe, userid, location, locationtitle)
                                    VALUES( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', $id, $data, self::$_ip, self::$_proxyIP, self::$_ua, $expiry, self::$mode, $userid, $location, $location_title );
            }
        }
        else
        {
            $dat = self::$_db->query( 'SELECT * FROM %tp%session WHERE expires > ? AND sid = ?', TIMESTAMP, $id );

            if ( isset( $dat[ 'sid' ] ) )
            {
                self::$_db->query( 'UPDATE %tp%session
                                    SET `data` = ?, expires = ?, location = ? , locationtitle = ?, userid = ?  WHERE sid = ?',
	                                $data, $expiry, $location, $location_title, $userid, $id );
            }
            else
            {
                self::$_db->query( 'REPLACE INTO %tp%session (sid, `data`, ip, proxy, agent, expires, befe, userid, location, locationtitle)
                                    VALUES( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', $id, $data, self::$_ip, self::$_proxyIP, self::$_ua, $expiry, self::$mode, $userid, $location, $location_title );
            }
        }

        return true;
    }

    /**
     *
     * @param string $id
     * @return boolean
     */
    public function destroy( $id = null )
    {
        self::$_db->query( 'DELETE FROM %tp%session WHERE sid = ?', (!IS_SWFUPLOAD ? $id : SWFUPLOAD_SID ) );

        return true;
    }

    /**
     *
     * @return boolean
     */
    public function gc()
    {
        self::$_db->query( 'DELETE FROM %tp%session WHERE expires < ?', TIMESTAMP );
        return true;
    }

}

?>