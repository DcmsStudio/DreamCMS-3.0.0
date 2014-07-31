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
 * @file        Abstract.php
 *
 */
class Tracking_Abstract
{

    /**
     * @var null
     */
    public static $db = null;

    /**
     * @var null
     */
    public static $ref = null;

    // Refferer
    /**
     * @var null
     */
    public static $fullref = null;

    // Refferer
    /**
     * @var null
     */
    public static $countrycode = null;

    /**
     * @var null
     */
    public static $ua = null;

    // User Agent
    /**
     * @var null
     */
    public static $ip = null;

    /**
     * @var null
     */
    public static $proxyIp = null;

    /**
     * @var null
     */
    public static $prxdomain = null;

    /**
     * @var null
     */
    public static $host_domain = null;

    /**
     * @var null
     */
    public static $os = null;

    /**
     * @var null
     */
    public static $browser = null;

    /**
     * @var null
     */
    public static $browser_version = null;

    /**
     * @var null
     */
    public static $spider = null;

    /**
     * @var null
     */
    public static $sphrase = null;

    /**
     * @var null
     */
    public static $getSiteHits = null;

    public static function init()
    {
	    if (self::$ua !== null) {
		    return;
	    }

        $sessionRefferer = Session::get( 'HTTP_REFERER', false );
        $postRefferer = '';

        if ( HTTP::post( 'HTTP_REFERER' ) )
        {
            $postRefferer = HTTP::post( 'HTTP_REFERER' );
        }

        if ( isset( $postRefferer ) && $postRefferer )
        {
            self::$ref = (isset( $postRefferer ) && $postRefferer ? $postRefferer : '');
        }
        elseif ( $sessionRefferer )
        {
            self::$ref = ($sessionRefferer ? $sessionRefferer : '');
        }
        else
        {
            self::$ref = (isset( $_SERVER[ 'HTTP_REFERER' ] ) ? $_SERVER[ 'HTTP_REFERER' ] : '');
        }

        self::$fullref = self::$ref;

        $ua = HTTP::input( 'ua' );

        if ( empty( $ua ) )
        {

            if ( isset( $_SERVER[ 'HTTP_USER_AGENT' ] ) )
            {
                self::$ua = $_SERVER[ 'HTTP_USER_AGENT' ];
            }
            elseif ( isset( $HTTP_SERVER_VARS[ 'HTTP_USER_AGENT' ] ) )
            {
                self::$ua = $HTTP_SERVER_VARS[ 'HTTP_USER_AGENT' ];
            }
            else
            {
                self::$ua = 'undefined';
            }
        }
        else
        {
            self::$ua = $ua;
        }


        if ( empty( self::$ua ) )
        {
            self::$ua = 'undefined';
        }


        if ( self::$db === null )
        {
            self::$db = Database::getInstance();
        }
    }

    /**
     * get current Client IP
     *
     * @return array (IP, Proxy)
     */
    protected static function getIP()
    {
        global $HTTP_SERVER_VARS, $_SERVER;

        $ra = '';
        $ra = $_SERVER[ 'REMOTE_ADDR' ];
        if ( $ra == '' && isset( $HTTP_SERVER_VARS[ 'REMOTE_ADDR' ] ) )
        {
            $ra = $HTTP_SERVER_VARS[ 'REMOTE_ADDR' ];
        }

        if ( isset( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) && !empty( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) )
        {
            $ip = $_SERVER[ 'HTTP_X_FORWARDED_FOR' ];
            $proxy = $ra;
        }
        elseif ( isset( $HTTP_SERVER_VARS[ 'HTTP_X_FORWARDED_FOR' ] ) && !empty( $HTTP_SERVER_VARS[ 'HTTP_X_FORWARDED_FOR' ] ) )
        {
            $ip = $HTTP_SERVER_VARS[ 'HTTP_X_FORWARDED_FOR' ];
            $proxy = $ra;
        }

        if ( isset( $_SERVER[ 'HTTP_CLIENT_IP' ] ) && !empty( $_SERVER[ 'HTTP_CLIENT_IP' ] ) )
        {
            $ip = $_SERVER[ 'HTTP_CLIENT_IP' ];
        }
        elseif ( isset( $HTTP_SERVER_VARS[ 'HTTP_CLIENT_IP' ] ) && !empty( $HTTP_SERVER_VARS[ 'HTTP_CLIENT_IP' ] ) )
        {
            $ip = $HTTP_SERVER_VARS[ 'HTTP_CLIENT_IP' ];
        }
        else
        {
            $ip = $ra;
        }

        if ( !empty( $proxy ) )
        {
            //$proxy = $proxy;
        }
        else
        {
            $proxy = 0;
        }

        return array(
            $ip,
            $proxy );
    }

    /**
     * This function returns the real hostname of an ip address.
     * Warning: $ip must be validated before calling this function.
     *
     * @param: $ip - the ip address in format x.x.x.x where x are
     *         numbers (0-255) or the hostname you want to lookup
     * @return string : returns the hostname as string. Something like 'user-id.isp-dialin.tld'
     */
    protected static function nslookup( $ip )
    {
        $op = array();

        // execute nslookup command
        exec( 'nslookup ' . $ip, $op );

        // php is running on windows machine
        if ( substr( php_uname(), 0, 7 ) == "Windows" )
        {
            return substr( $op[ 3 ], 6 );
        }
        else
        {
            // on linux nslookup returns 2 diffrent line depending on
            // ip or hostname given for nslookup
            if ( strpos( $op[ 4 ], 'name = ' ) > 0 )
            {
                return substr( $op[ 4 ], strpos( $op[ 4 ], 'name =' ) + 7, -1 );
            }
            else
            {
                return substr( $op[ 4 ], strpos( $op[ 4 ], 'Name:' ) + 6 );
            }
        }
    }

    /**
     *
     * @param string $ip
     * @return string hostname
     */
    protected static function getHostname( $ip )
    {
        if ( $ip == '127.0.0.1' )
        {
            return 'localhost';
        }

        if ( function_exists( 'exec' ) )
        {
            return self::nslookup( $ip );
        }

        $host = gethostbyaddr( $ip );
        return $host;
    }

    /**
     *
     * @param string $ip
     * @return string
     */
    protected static function getCountry( $ip )
    {

        /*
          $ip2country = new Tracking_IpToCountry(FRAMEWORK_PATH . 'Tracking/IP2Country/ip2cntry.dat');
          if ( $ip2country === IP2C_DATABASE_OPEN_ERROR )
          {
          throw new BaseException('Ip2Country Database error');
          }





          $index = $ip2country->lookup($ip);
          $country = $ip2country->idx2country($index);
          $ip2country->freeMem();
          $ip2country = null;
         */

        if ( !class_exists( 'GeoIP', false ) )
        {
            include_once FRAMEWORK_PATH . 'Tracking/IP2Country/ip2country.php';
        }

        $geoip = GeoIP::getInstance( FRAMEWORK_PATH . 'Tracking/IP2Country/geoIP.dat' );
        $country = $geoip->lookupCountryCode( $ip );
        $geoip = null;

        return strtolower( $country );
    }

}
