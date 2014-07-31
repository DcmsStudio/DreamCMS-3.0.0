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
 * @file        Url.php
 *
 */
class Url
{

    /**
     *
     * @param string $alias
     * @param string $suffix
     * @param string $title
     * @param string $contenturl
     * @return string
     */
    public static function makeRw( $alias = null, $suffix = null, $title = '', $contenturl = '' )
    {
        if ( $alias == '' || $alias === null )
        {
            if ( trim( $title ) == '' )
            {
                Error::raise( 'Could not make a alias for this page. Code:' . __LINE__ );
            }

            $alias = Library::suggest( $title, (($suffix === null || $suffix == true || $suffix == '') ? true : false) );
        }
        else
        {
            $alias = Library::suggest( $alias, (($suffix === null || $suffix == true || $suffix == '') ? true : false) );
        }

        return $alias;
    }

    /**
     * If current URL is "http://example.org/dir1/dir2/index.php?param1=value1&param2=value2"
     * will return "/dir1/dir2/index.php"
     *
     * @return string
     */
    static public function getCurrentScriptName()
    {
        $url = '';

        if ( !empty( $_SERVER[ 'REQUEST_URI' ] ) )
        {
            $url = $_SERVER[ 'REQUEST_URI' ];

            // strip http://host (Apache+Rails anomaly)
            if ( preg_match( '~^https?://[^/]+($|/.*)~D', $url, $matches ) )
            {
                $url = $matches[ 1 ];
            }

            // strip parameters
            if ( ($pos = strpos( $url, "?" )) !== false )
            {
                $url = substr( $url, 0, $pos );
            }

            // strip path_info
            if ( isset( $_SERVER[ 'PATH_INFO' ] ) )
            {
                $url = substr( $url, 0, -strlen( $_SERVER[ 'PATH_INFO' ] ) );
            }
        }

        /**
         * SCRIPT_NAME is our fallback, though it may not be set correctly
         *
         * @see http://php.net/manual/en/reserved.variables.php
         */
        if ( empty( $url ) )
        {
            if ( isset( $_SERVER[ 'SCRIPT_NAME' ] ) )
            {
                $url = $_SERVER[ 'SCRIPT_NAME' ];
            }
            elseif ( isset( $_SERVER[ 'SCRIPT_FILENAME' ] ) )
            {
                $url = $_SERVER[ 'SCRIPT_FILENAME' ];
            }
            elseif ( isset( $_SERVER[ 'argv' ] ) )
            {
                $url = $_SERVER[ 'argv' ][ 0 ];
            }
        }

        if ( !isset( $url[ 0 ] ) || $url[ 0 ] !== '/' )
        {
            $url = '/' . $url;
        }
        return $url;
    }

    /**
     * If current URL is "http://example.org/dir1/dir2/index.php?param1=value1&param2=value2"
     * will return "http://example.org/dir1/dir2/index.php"
     *
     * @return string
     */
    static public function getCurrentUrlWithoutQueryString()
    {
        return self::getCurrentScheme() . '://'
                . self::getCurrentHost()
                . self::getCurrentScriptName();
    }

    /**
     * Redirects the user to the referrer if found.
     * If the user doesn't have a referrer set, it redirects to the current URL without query string.
     */
    static public function redirectToReferer()
    {
        $referrer = self::getReferer();
        if ( $referrer !== false )
        {
            self::redirectToUrl( $referrer );
        }
        self::redirectToUrl( self::getCurrentUrlWithoutQueryString() );
    }

    /**
     * Redirects the user to the specified URL
     *
     * @param string $url
     */
    static public function redirectToUrl( $url )
    {
        if ( self::isLookLikeUrl( $url ) || strpos( $url, 'index.php' ) === 0
        )
        {
            @header( "Location: $url" );
        }
        else
        {
            echo "Invalid URL to redirect to.";
        }
        exit;
    }

    /**
     * Returns true if the string passed may be a URL.
     * We don't need a precise test here because the value comes from the website
     * tracked source code and the URLs may look very strange.
     *
     * @param string $url
     * @return bool
     */
    static function isLookLikeUrl( $url )
    {
        return preg_match( '~^(ftp|news|http|https)?://(.*)$~D', $url, $matches ) !== 0 && strlen( $matches[ 2 ] ) > 0;
    }

    /**
     * If the current URL is 'http://example.org/dir1/dir2/index.php?param1=value1&param2=value2"
     * will return 'http'
     *
     * @return string 'https' or 'http'
     */
    static public function getCurrentScheme()
    {
        $assume_secure_protocol = true; ///@Piwik_Config::getInstance()->General['assume_secure_protocol'];
        if ( $assume_secure_protocol || (isset( $_SERVER[ 'HTTPS' ] ) && ($_SERVER[ 'HTTPS' ] == 'on' || $_SERVER[ 'HTTPS' ] === true))
        )
        {
            return 'https';
        }
        return 'http';
    }

    /**
     * Sanitize a single input value
     *
     * @param string $value
     * @return string sanitized input
     */
    static public function sanitizeInputValue( $value )
    {
        // $_GET and $_REQUEST already urldecode()'d
        // decode
        // note: before php 5.2.7, htmlspecialchars() double encodes &#x hex items
        $value = html_entity_decode( $value, ENT_QUOTES, 'UTF-8' );

        // filter
        $value = str_replace( array(
            "\n",
            "\r",
            "\0" ), '', $value );

        // escape
        $tmp = @htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );

        // note: php 5.2.5 and above, htmlspecialchars is destructive if input is not UTF-8
        if ( $value != '' && $tmp == '' )
        {
            // convert and escape
            $value = utf8_encode( $value );
            $tmp = htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
        }
        return $tmp;
    }

    /*
     * Validate "Host" (untrusted user input)
     *
     * @param string $host         Contents of Host: header from Request
     * @param array  $trustedHosts An array of trusted hosts
     *
     * @return boolean True if valid; false otherwise
     */

    /**
     * @param $host
     * @param $trustedHosts
     * @return bool
     */
    public function isValidHost( $host, $trustedHosts )
    {
        // Only punctuation we allow is '[', ']', ':', '.' and '-'
        $hostLength = strlen( $host );
        if ( $hostLength !== strcspn( $host, '`~!@#$%^&*()_+={}\\|;"\'<>,?/ ' ) )
        {
            return false;
        }

        $untrustedHost = strtolower( $host );
        $hostRegex = strtolower( str_replace( '.', '\.', '/(^|.)' . implode( '|', $trustedHosts ) . '(:[0-9]+)?$/' ) );

        return 0 !== preg_match( $hostRegex, rtrim( $untrustedHost, '.' ) );
    }

    /**
     * Get host
     *
     * @return string|false
     */
    static public function getHost()
    {
        // HTTP/1.1 request
        if ( isset( $_SERVER[ 'HTTP_HOST' ] ) && strlen( $host = $_SERVER[ 'HTTP_HOST' ] ) && (!($trustedHosts = @Piwik_Config::getInstance()->General[ 'trusted_hosts' ]) || self::isValidHost( $host, $trustedHosts ))
        )
        {
            return $host;
        }

        // HTTP/1.0 request doesn't include Host: header
        if ( isset( $_SERVER[ 'SERVER_ADDR' ] ) )
        {
            return $_SERVER[ 'SERVER_ADDR' ];
        }

        return false;
    }

    /**
     * If current URL is "http://example.org/dir1/dir2/index.php?param1=value1&param2=value2"
     * will return "example.org"
     *
     * @param string $default Default value to return if host unknown
     * @return string
     */
    static public function getCurrentHost( $default = 'unknown' )
    {
        $hostHeaders = null; //@Piwik_Config::getInstance()->General['proxy_host_headers'];
        if ( !is_array( $hostHeaders ) )
        {
            $hostHeaders = array();
        }

        $host = self::getHost();
        $default = self::sanitizeInputValue( $host ? $host : $default  );

        return self::getNonProxyIpFromHeader( $default, $hostHeaders );
    }

    /**
     * Is the URL on the same host?
     *
     * @param string $url
     * @return bool True if local; false otherwise.
     */
    static public function isLocalUrl( $url )
    {
        if ( empty( $url ) )
        {
            return true;
        }

        // handle host name mangling
        $requestUri = isset( $_SERVER[ 'SCRIPT_URI' ] ) ? $_SERVER[ 'SCRIPT_URI' ] : '';
        $parseRequest = @parse_url( $requestUri );
        $hosts = array(
            self::getHost(),
            self::getCurrentHost() );
        if ( isset( $parseRequest[ 'host' ] ) )
        {
            $hosts[] = $parseRequest[ 'host' ];
        }

        // drop port numbers from hostnames and IP addresses
        $hosts = array_map( array(
            'Url',
            'sanitizeIp' ), $hosts );

        // compare scheme and host
        $parsedUrl = @parse_url( $url );
        $scheme = $parsedUrl[ 'scheme' ];
        $host = Url::sanitizeIp( $parsedUrl[ 'host' ] );
        return (in_array( $scheme, array(
                    'http',
                    'https' ) ) && in_array( $host, $hosts ));
    }

    /**
     * Sanitize human-readable IP address.
     *
     * @param string $ipString  IP address
     * @return string|false
     */
    static public function sanitizeIp( $ipString )
    {
        $ipString = trim( $ipString );

        // CIDR notation, A.B.C.D/E
        $posSlash = strrpos( $ipString, '/' );
        if ( $posSlash !== false )
        {
            $ipString = substr( $ipString, 0, $posSlash );
        }

        $posColon = strrpos( $ipString, ':' );
        $posDot = strrpos( $ipString, '.' );
        if ( $posColon !== false )
        {
            // IPv6 address with port, [A:B:C:D:E:F:G:H]:EEEE
            $posRBrac = strrpos( $ipString, ']' );
            if ( $posRBrac !== false && $ipString[ 0 ] == '[' )
            {
                $ipString = substr( $ipString, 1, $posRBrac - 1 );
            }

            if ( $posDot !== false )
            {
                // IPv4 address with port, A.B.C.D:EEEE
                if ( $posColon > $posDot )
                {
                    $ipString = substr( $ipString, 0, $posColon );
                }
                // else: Dotted quad IPv6 address, A:B:C:D:E:F:G.H.I.J
            }
            else if ( strpos( $ipString, ':' ) === $posColon )
            {
                $ipString = substr( $ipString, 0, $posColon );
            }
            // else: IPv6 address, A:B:C:D:E:F:G:H
        }
        // else: IPv4 address, A.B.C.D

        return $ipString;
    }

    /**
     * Returns the best possible IP of the current user, in the format A.B.C.D
     * For example, this could be the proxy client's IP address.
     *
     * @return string  IP address in presentation format
     */
    static public function getIpFromHeader()
    {
        $clientHeaders = null; //@Piwik_Config::getInstance()->General['proxy_client_headers'];
        if ( !is_array( $clientHeaders ) )
        {
            $clientHeaders = array();
        }

        $default = '0.0.0.0';
        if ( isset( $_SERVER[ 'REMOTE_ADDR' ] ) )
        {
            $default = $_SERVER[ 'REMOTE_ADDR' ];
        }

        $ipString = self::getNonProxyIpFromHeader( $default, $clientHeaders );
        return self::sanitizeIp( $ipString );
    }

    /**
     * Returns a non-proxy IP address from header
     *
     * @param string $default       Default value to return if no matching proxy header
     * @param array $proxyHeaders  List of proxy headers
     * @return string
     */
    static public function getNonProxyIpFromHeader( $default, $proxyHeaders )
    {
        $proxyIps = null; //@Piwik_Config::getInstance()->General['proxy_ips'];
        if ( !is_array( $proxyIps ) )
        {
            $proxyIps = array();
        }
        $proxyIps[] = $default;

        // examine proxy headers
        foreach ( $proxyHeaders as $proxyHeader )
        {
            if ( !empty( $_SERVER[ $proxyHeader ] ) )
            {
                $proxyIp = self::getLastIpFromList( $_SERVER[ $proxyHeader ], $proxyIps );
                if ( strlen( $proxyIp ) && stripos( $proxyIp, 'unknown' ) === false )
                {
                    return $proxyIp;
                }
            }
        }

        return $default;
    }

    /**
     * Returns the last IP address in a comma separated list, subject to an optional exclusion list.
     *
     * @param string $csv          Comma separated list of elements
     * @param array $excludedIps  Optional list of excluded IP addresses (or IP address ranges)
     * @return string  Last (non-excluded) IP address in the list
     */
    static public function getLastIpFromList( $csv, $excludedIps = null )
    {
        $p = strrpos( $csv, ',' );
        if ( $p !== false )
        {
            $elements = explode( ',', $csv );
            for ( $i = count( $elements ); $i--; )
            {
                $element = trim( Url::sanitizeInputValue( $elements[ $i ] ) );
                if ( empty( $excludedIps ) || (!in_array( $element, $excludedIps ) && !self::isIpInRange( self::P2N( self::sanitizeIp( $element ) ), $excludedIps )) )
                {
                    return $element;
                }
            }
        }
        return trim( Url::sanitizeInputValue( $csv ) );
    }

    /**
     * Get low and high IP addresses for a specified range.
     *
     * @param array $ipRange  An IP address range in presentation format
     * @return array|false  Array ($lowIp, $highIp) in network address format, or false if failure
     */
    static public function getIpsForRange( $ipRange )
    {
        if ( strpos( $ipRange, '/' ) === false )
        {
            $ipRange = self::sanitizeIpRange( $ipRange );
        }
        $pos = strpos( $ipRange, '/' );

        $bits = substr( $ipRange, $pos + 1 );
        $range = substr( $ipRange, 0, $pos );
        $high = $low = @_inet_pton( $range );
        if ( $low === false )
        {
            return false;
        }

        $lowLen = strlen( $low );
        $i = $lowLen - 1;
        $bits = $lowLen * 8 - $bits;

        for ( $n = (int) ($bits / 8); $n > 0; $n--, $i-- )
        {
            $low[ $i ] = chr( 0 );
            $high[ $i ] = chr( 255 );
        }

        $n = $bits % 8;
        if ( $n )
        {
            $low[ $i ] = chr( ord( $low[ $i ] ) & ~((1 << $n) - 1) );
            $high[ $i ] = chr( ord( $high[ $i ] ) | ((1 << $n) - 1) );
        }

        return array(
            $low,
            $high );
    }

    /**
     * Determines if an IP address is in a specified IP address range.
     *
     * An IPv4-mapped address should be range checked with an IPv4-mapped address range.
     *
     * @param string $ip        IP address in network address format
     * @param array $ipRanges  List of IP address ranges
     * @return bool  True if in any of the specified IP address ranges; else false.
     */
    static public function isIpInRange( $ip, $ipRanges )
    {
        $ipLen = strlen( $ip );
        if ( empty( $ip ) || empty( $ipRanges ) || ($ipLen != 4 && $ipLen != 16) )
        {
            return false;
        }

        foreach ( $ipRanges as $range )
        {
            if ( is_array( $range ) )
            {
                // already split into low/high IP addresses
                $range[ 0 ] = self::P2N( $range[ 0 ] );
                $range[ 1 ] = self::P2N( $range[ 1 ] );
            }
            else
            {
                // expect CIDR format but handle some variations
                $range = self::getIpsForRange( $range );
            }
            if ( $range === false )
            {
                continue;
            }

            $low = $range[ 0 ];
            $high = $range[ 1 ];
            if ( Piwik_Common::strlen( $low ) != $ipLen )
            {
                continue;
            }

            // binary-safe string comparison
            if ( $ip >= $low && $ip <= $high )
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Convert presentation format IP address to network address format
     *
     * @param string $ipString  IP address, either IPv4 or IPv6, e.g., "127.0.0.1"
     * @return string  Binary-safe string, e.g., "\x7F\x00\x00\x01"
     */
    static public function P2N( $ipString )
    {
        // use @inet_pton() because it throws an exception and E_WARNING on invalid input
        $ip = @_inet_pton( $ipString );
        return $ip === false ? "\x00\x00\x00\x00" : $ip;
    }

    /**
     * Convert network address format to presentation format
     *
     * @see prettyPrint()
     *
     * @param string $ip  IP address in network address format
     * @return string  IP address in presentation format
     */
    static public function N2P( $ip )
    {
        // use @inet_ntop() because it throws an exception and E_WARNING on invalid input
        $ipStr = @_inet_ntop( $ip );
        return $ipStr === false ? '0.0.0.0' : $ipStr;
    }

}
