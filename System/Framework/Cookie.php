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
 * @file        Cookie.php
 *
 */
class Cookie
{

    /**
     * Registry containing the data stored in the cookie.
     * @var Array
     */
    static $registry = array();

    /**
     * Boolean to indicate whether the information from the cookie has been loaded into the registry.
     * @var Boolean
     */
    static $initialized = false;

	static $_sendCookieHashes = array();

    /**
     * Initialisation function, loads data from the cookie into the registry.
     * @return void
     */
    public static function init()
    {
        if ( isset( $_COOKIE[ COOKIE_PREFIX . '_registry' ] ) )
        {
            self::$registry = (array) json_decode( $_COOKIE[ COOKIE_PREFIX . '_registry' ] );
        }

        self::$initialized = true;
    }

    /**
     * 
     */
    public static function destroy()
    {
        if ( isset( $_COOKIE[ COOKIE_PREFIX . '_registry' ] ) )
        {
            $_COOKIE[ COOKIE_PREFIX . '_registry' ] = array();
            self::$registry = array();
            self::$initialized = true;
        }
    }

    /**
     * Retrieve a value from the registry.
     *
     * @param      $name Name of the entry to retrieve.
     * @param bool $_default
     * @return The value stored in the registry, or false if the value isn't in the registry.
     */
    public static function get( $name = null, $_default = false )
    {
        if ( !self::$initialized )
        {
            self::init();
        }

        if ( $name === null )
        {
            return self::$registry;
        }


        if ( isset( self::$registry[ $name ] ) )
        {


            if ( substr( self::$registry[ $name ], 0, 3 ) === '@a@' )
            {
                return unserialize( substr( self::$registry[ $name ], 3 ) );
            }


            return self::$registry[ $name ];
        }
        else
        {
            return $_default;
        }
    }

    /**
     * Stores a value in the registry, and writes out the cookie.
     *
     *
     * @param string $name  Name of the entry to store in the registry.
     * @param mixed $value Value to store in the registry.
     * @param bool $timeout
     * @param null $cookiePrefix
     * @throws BaseException
     */
    public static function set( $name, $value, $timeout = false, $cookiePrefix = null )
    {
        if ( !self::$initialized )
        {
            self::init();
        }

        if ( is_null( $value ) )
        {
            unset( self::$registry[ $name ] );
        }
        else
        {
            self::$registry[ $name ] = is_array( $value ) ? '@a@' . serialize( $value ) : $value;
        }


	    $hash = md5(($cookiePrefix !== null && $cookiePrefix ? $cookiePrefix : COOKIE_PREFIX) . '_registry' . (string) Json::encode( self::$registry ));
	    if ( isset(self::$_sendCookieHashes[$hash]))
	    {
			return;
	    }


        if ( $timeout === false || !(int)$timeout  )
        {
            $timeout = Settings::get( 'cookie_timer', 3600 );
        }


        try
        {
	        self::$_sendCookieHashes[$hash] = true;
            setcookie( ($cookiePrefix !== null && $cookiePrefix ? $cookiePrefix : COOKIE_PREFIX) . '_registry', (string) Json::encode( self::$registry ), time() + (int)$timeout , '/' );
        }
        catch ( Exception $e )
        {
            throw new BaseException( $e->getMessage() );
        }
    }

    /**
     * Remove an entry from the registry.
     * @param $name Name of the entry to remove from the registry.
     */
    public static function delete( $name = null )
    {
        if ( !self::$initialized )
        {
            self::init();
        }

        if ( $name === null )
        {
            foreach ( self::$registry as $k => $v )
            {
                self::set( $k, null );
            }

            return;
        }

        self::set( $name, null );
    }

}
