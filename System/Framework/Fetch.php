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
 * @file        Fetch.php
 *
 */
class Fetch
{

    /**
     * @param     $url
     * @param int $timeout
     * @return mixed|string
     */
    public static function URL( $url, $timeout = 20 )
    {
        if ( function_exists( 'curl_init' ) )
        {
            return self::fileGetContents( $url, $timeout );
        }
        else
        {
            return self::fetchURL( $url, $timeout );
        }
    }

    /**
     * @param $url
     * @param $timeout
     * @return mixed
     */
    private static function fileGetContents( $url, $timeout )
    {
        $p = parse_url( $url );

        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_HEADER, 0 );
        curl_setopt( $ch, CURLOPT_REFERER, 'http://www.' . $p[ 'host' ] );
        curl_setopt( $ch, CURLOPT_USERAGENT, $_SERVER[ 'HTTP_USER_AGENT' ] );

        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 ); //Set curl to return the data instead of printing it to the browser.
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 0 );

        curl_setopt( $ch, CURLOPT_URL, $url );
        $data = curl_exec( $ch );
        curl_close( $ch );

        return $data;
    }

    /**
     * @param $url
     * @param $timeout
     * @return string
     */
    private static function fetchURL( $url, $timeout )
    {
        $url_parsed = parse_url( $url );
        $host = $url_parsed[ "host" ];
        $port = $url_parsed[ "port" ];
        if ( $port === 0 )
            $port = 80;
        $path = $url_parsed[ "path" ];
        if ( $url_parsed[ "query" ] != "" )
            $path .= "?" . $url_parsed[ "query" ];

        $out = "GET $path HTTP/1.0\r\nHost: $host\r\n\r\n";

        $fp = fsockopen( $host, $port, $errno, $errstr, $timeout );
        $in = '';


        if ( $fp )
        {
            fwrite( $fp, $out );
            $body = false;
            while ( !feof( $fp ) )
            {
                $s = fgets( $fp, 1024 );
                if ( $body )
                    $in .= $s;
                if ( $s === "\r\n" )
                    $body = true;
            }

            fclose( $fp );
        }

        return $in;
    }

}
