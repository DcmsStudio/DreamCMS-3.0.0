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
 * @file        Session.php
 *
 */
class Session
{

    /**
     * @var bool
     */
    protected static $mode = false;

    /**
     * @var bool
     */
    protected static $sessionStarted = false;

    /**
     * @var null|string
     */
    private static $sessionID = null;

    /**
     * @var null
     */
    private static $instance = null;

    /**
     * @var null
     */
    private static $saveHandlerInstance = null;

    /**
     *
     */
    public function __construct()
    {
        $session_is_set = false;
        $sid = HTTP::input( 'sid' );
        $cookiesid = Cookie::get( session_name(), null );

        if ( (IS_AJAX === true || IS_SWFUPLOAD === true) && defined( 'SWFUPLOAD_SID' ) && is_string( SWFUPLOAD_SID ) && SWFUPLOAD_SID !== '' )
        {
            // $_COOKIE[INSTALL_ID] = SWFUPLOAD_SID;
            $session_is_set = true;
            $_sid = SWFUPLOAD_SID;
            self::$sessionID = $_sid;
            session_id( $_sid );
        }

        if ( !$session_is_set && $cookiesid !== null )
        {
            self::$sessionID = $cookiesid;
            session_id( $cookiesid );
            $session_is_set = true;
        }


        if ( !$session_is_set && $sid !== null )
        {
            self::$sessionID = $sid;
            session_id( $sid );
            $session_is_set = true;
        }


        if ( !$session_is_set )
        {
            // Cookie::delete( 'uhash' );
            Cookie::delete( 'uhash' );


            if ( !session_id() )
            {
                self::regenerate_id();
            }

            self::$sessionID = session_id();
        }

        $sessionStarted = true;


        /* make sure that the session values are stored */
        // register_shutdown_function( array( $this, 'write' ) );
    }

    /**
     *
     * @return Session
     */
    public static function getInstance()
    {
        if ( self::$instance === null )
        {
            self::$mode = defined( 'ADM_SCRIPT' );
            self::$saveHandlerInstance = new Session_Database( self::$mode );
            self::$saveHandlerInstance->open();
            self::$saveHandlerInstance->gc();

            if ( !self::$sessionStarted )
            {
                session_start();
                self::$sessionStarted = true;
            }

            self::$instance = new Session();
        }

        Cookie::delete( session_name() );
        Cookie::set( session_name(), self::$sessionID, $GLOBALS[ 'SESSIONTIMEOUT' ] );

        return self::$instance;
    }

    /**
     *
     * @return string
     */
    static public function getLock()
    {
        return md5( INSTALL_ID );
    }

    /**
     *
     * @param bool|string $name
     * @param bool|mixed $default
     * @return array
     */
    static public function get( $name = false, $default = false )
    {
        if ( !isset( $_SESSION[ INSTALL_ID ] ) )
        {
            $_SESSION[ INSTALL_ID ] = array();
        }

        if ( $name === false )
        {
            return $_SESSION[ INSTALL_ID ];
        }

        return (isset( $_SESSION[ INSTALL_ID ][ $name ] ) ? $_SESSION[ INSTALL_ID ][ $name ] : $default);
    }

    /**
     *
     * @param string $name
     * @param null|mixed $value
     */
    static public function save( $name, $value = null )
    {
        $_SESSION[ INSTALL_ID ][ $name ] = $value;
    }

    /**
     *
     * @param string $sid
     * @return string
     */
    static public function setId( $sid )
    {
        self::$sessionID = $sid;
        return session_id( $sid );
    }

    /**
     *
     * @return string
     */
    static public function getId()
    {
        return self::$sessionID;
    }

    /**
     *
     * @param string $name
     */
    static public function delete( $name )
    {
        if ( isset( $_SESSION[ INSTALL_ID ][ $name ] ) )
        {
            unset( $_SESSION[ INSTALL_ID ][ $name ] );
        }
    }

    static public function destroy()
    {
        unset( $_SESSION[ INSTALL_ID ] );
    }

    static public function close()
    {
        #   if (!(self::$saveHandlerInstance instanceof Session_Database))
        #   {
        #       self::getInstance();
        #   }
        #return self::$saveHandlerInstance->close();
        session_write_close();
    }

    /**
     * It will permanently close the session
     * @param string $id
     * @param null|mixed $data
     */
    static public function write( $id = null, $data = null )
    {
        if (self::$saveHandlerInstance === null) {
            self::getInstance();
        }

        // self::$saveHandlerInstance->write(session_id(), $_SESSION);
        session_write_close();
        # $ob = ob_end_clean();
        try {
            @session_start();
        }
        catch (Exception $e) {
            Library::log($e->getMessage(), 'error', null, $e->getTrace());
        }

        #if ($ob) echo $ob;
        # self::$saveHandlerInstance->write($id, $_SESSION);
    }

    /**
     *  Regenerates the session id.
     *
     *  <b>Call this method whenever you do a privilege change!</b>
     *
     * @return void
     */
    public static function regenerate_id()
    {
        // saves the old session's id
        $oldSessionID = session_id();

        // regenerates the id
        // this function will create a new session, with a new id and containing the data from the old session
        // but will not delete the old session
        session_regenerate_id();

        self::$sessionID = session_id();

        // because the session_regenerate_id() function does not delete the old session,
        // we have to delete it manually
        self::destroy( $oldSessionID );

        session_id( self::$sessionID );
    }

}

?>