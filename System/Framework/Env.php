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
 * @file        Env.php
 *
 */
class Env
{

    /**
     * Current object instance (Singleton)
     * @var object
     */
    protected static $objInstanceEnv = null;

    /**
     * @var null
     */
    protected $_scriptFilename = null;

    /**
     * @var null
     */
    protected $_scriptName = null;

    /**
     * @var null
     */
    protected $_documentRoot = null;

    /**
     * @var null
     */
    protected $_httpAcceptLanguage = null;

    /**
     * @var null
     */
    protected $_httpUserAgent = null;

	protected $_requestUri = null;

    /**
     * Prevent cloning of the object (Singleton)
     */
    final private function __clone()
    {
        
    }

    /**
     * Return the current object instance (Singleton)
     * @return Env
     */
    public static function getInstance()
    {
        if ( self::$objInstanceEnv === null )
        {
            self::$objInstanceEnv = new Env();
        }

        return self::$objInstanceEnv;
    }

    /**
     *
     * @return Env
     */
    public function init()
    {
        $this->scriptFilename();
        $this->scriptName();
        $this->documentRoot();
        $this->requestUri();
        $this->httpAcceptLanguage();
        $this->httpUserAgent();

        return $this;
    }

    /**
     * Return the absolute path to the script (e.g. /home/www/html/website/index.php)
     * @return string
     */
    public function scriptFilename()
    {
        if ( $this->_scriptFilename === null )
        {
            $this->_scriptFilename = str_replace( '//', '/', str_replace( '\\', '/', (php_sapi_name() == 'cgi' || php_sapi_name() == 'isapi' || php_sapi_name() == 'cgi-fcgi') && (isset( $_SERVER[ 'ORIG_PATH_TRANSLATED' ] ) ? $_SERVER[ 'ORIG_PATH_TRANSLATED' ] : $_SERVER[ 'PATH_TRANSLATED' ]) ? (isset( $_SERVER[ 'ORIG_PATH_TRANSLATED' ] ) ? $_SERVER[ 'ORIG_PATH_TRANSLATED' ] : $_SERVER[ 'PATH_TRANSLATED' ]) : (isset( $_SERVER[ 'ORIG_SCRIPT_FILENAME' ] ) ? $_SERVER[ 'ORIG_SCRIPT_FILENAME' ] : $_SERVER[ 'SCRIPT_FILENAME' ])  ) );
        }

        return $this->_scriptFilename;
    }

    /**
     * Return the relative path to the script (e.g. /website/index.php)
     * @return string
     */
    public function scriptName()
    {

        if ( $this->_scriptName === null )
        {
            $this->_scriptName = (php_sapi_name() == 'cgi' || php_sapi_name() == 'cgi-fcgi') && (isset( $_SERVER[ 'ORIG_PATH_INFO' ] ) ? $_SERVER[ 'ORIG_PATH_INFO' ] : $_SERVER[ 'PATH_INFO' ]) ? (isset( $_SERVER[ 'ORIG_PATH_INFO' ] ) ? $_SERVER[ 'ORIG_PATH_INFO' ] : $_SERVER[ 'PATH_INFO' ]) : (isset( $_SERVER[ 'ORIG_SCRIPT_NAME' ] ) ? $_SERVER[ 'ORIG_SCRIPT_NAME' ] : $_SERVER[ 'SCRIPT_NAME' ]);
        }

        return $this->_scriptName;
    }

    /**
     *
     * @return boolean
     */
    public function isSubdomain()
    {
        $domain = str_replace( 'www.', '', $_SERVER[ "HTTP_HOST" ] );
        if ( substr_count( $domain, '.' ) > 1 )
        {
            return true;
        }

        return false;
    }

    /**
     * Determines if the application is accessed via an encrypted
     * (HTTPS) connection.
     *
     * @return        bool
     */
    public function isHttps()
    {
        if ( !empty( $_SERVER[ 'HTTPS' ] ) && strtolower( $_SERVER[ 'HTTPS' ] ) !== 'off' )
        {
            return true;
        }
        elseif ( isset( $_SERVER[ 'HTTP_X_FORWARDED_PROTO' ] ) && $_SERVER[ 'HTTP_X_FORWARDED_PROTO' ] === 'https' )
        {
            return true;
        }
        elseif ( !empty( $_SERVER[ 'HTTP_FRONT_END_HTTPS' ] ) && strtolower( $_SERVER[ 'HTTP_FRONT_END_HTTPS' ] ) !== 'off' )
        {
            return true;
        }

        return false;
    }

    /**
     * Test to see if a request was made from the command line.
     * 
     * @return bool
     */
    public function isCLI()
    {
        return (PHP_SAPI === 'cli' || defined( 'STDIN' ));
    }

    /**
     * Alias for scriptName()
     * 
     * @return string
     */
    public function phpSelf()
    {
        return $this->scriptName();
    }

    /**
     * Return the document root (e.g. /home/www/user/)
     *
     * Calculated as SCRIPT_FILENAME minus SCRIPT_NAME as some CGI versions
     * and mod-rewrite rules might return an incorrect DOCUMENT_ROOT.
     * @throws BaseException
     * @return string
     */
    public function documentRoot()
    {
        if ( $this->_documentRoot === null )
        {
            return str_replace( '//', '/', str_replace( '\\', '/', realpath( $_SERVER[ 'DOCUMENT_ROOT' ] ) ) );
            /*
            $scriptName = $this->scriptName();
            $scriptFilename = $this->scriptFilename();


            $strDocumentRoot = '';
            $arrUriSegments = array();

            // Fallback to DOCUMENT_ROOT if SCRIPT_FILENAME and SCRIPT_NAME point to different files
            if ( basename( $scriptName ) !== basename( $scriptFilename ) )
            {
                return str_replace( '//', '/', str_replace( '\\', '/', realpath( $_SERVER[ 'DOCUMENT_ROOT' ] ) ) );
            }

            if ( substr( $scriptFilename, 0, 1 ) === '/' )
            {
                $strDocumentRoot = '/';
            }


            $_sn = $scriptName ? strrev( $scriptName ) : '';
            $_fn = $scriptFilename ? strrev( $scriptFilename ) : '';

            $arrSnSegments = !empty( $_sn ) ? explode( '/', $_sn ) : array(
                0 => '' );
            $arrSfnSegments = !empty( $_fn ) ? explode( '/', $_fn ) : array(
                0 => '' );

            foreach ( $arrSfnSegments as $k => $v )
            {
                if ( $arrSnSegments[ $k ] != $v )
                {
                    $arrUriSegments[] = $v;
                }
            }

            try
            {
                $_segStr = implode( '/', $arrUriSegments );

                $strDocumentRoot .= (!empty( $_segStr ) ? strrev( $_segStr ) : '');

                if ( strlen( $strDocumentRoot ) < 2 && strlen( $strDocumentRoot ) > 0 )
                {
                    $strDocumentRoot = substr( $arrSfnSegments, 0, -(strlen( $strDocumentRoot ) + 1) );
                }
            }
            catch ( Exception $e )
            {
                throw new BaseException( $e->getMessage() );
            }

            $this->_documentRoot = str_replace( '//', '/', str_replace( '\\', '/', realpath( $strDocumentRoot ) ) );
            */
        }


        return $this->_documentRoot;


    }


    /**
     * @param $arr
     * @param $out
     */
    private function convertArray($arr, &$out)
    {
        if (!is_array($arr))
        {
            return;
        }

        foreach ( $arr as $k => $v )
        {
            if (is_array($v)) {
                $this->convertArray($v, $out);
            }
            else {
                $out[] = $k .'=' . urlencode( $v );
            }
        }
    }



    /**
     * @return bool|string
     */
    public function convertPostToGet()
    {
        $input = new Input();


        if ( $input->getMethod() === 'post' )
        {
            $ret = array();
            foreach ( $input->input() as $k => $v )
            {
                if (is_array($v))
                {
                    $this->convertArray($v, $ret);
                }
                else
                {
                    $ret[] = $k . '=' . (!is_object($v) ? urlencode( $v ) : $v);
                }
            }

            return implode( '&amp;', $ret );
        }

        return false;
    }

    /**
     * Return the request URI [path]?[query] (e.g. /index.php?id=1)
     * @return string
     */
    public function requestUri()
    {

        if ( $this->_requestUri === null )
        {
            if ( !empty( $_SERVER[ 'REQUEST_URI' ] ) )
            {
                $serverRoot = str_replace( '//', '/', str_replace( '\\', '/', realpath( $_SERVER[ 'DOCUMENT_ROOT' ] ) ) );
                $this->_requestUri = str_replace( PUBLIC_PATH, '', $serverRoot . $_SERVER[ 'REQUEST_URI' ] );


                if ( ($params = $this->convertPostToGet()) !== false )
                {
                    $this->_requestUri .= '?' . $params;
                }
            }
            else
            {
                $this->_requestUri = '/' . preg_replace( '/^\//', '', $this->scriptName() ) . (!empty( $_SERVER[ 'QUERY_STRING' ] ) ? '?' . $_SERVER[ 'QUERY_STRING' ] : '');
            }
        }

        return str_replace( ROOT_PATH, '/', $this->_requestUri );
    }

    /**
     * Return the first eight user languages as array
     * @return array
     */
    public function httpAcceptLanguage()
    {
        if ( $this->_httpAcceptLanguage === null )
        {
            $arrAccepted = array();

            $str = isset( $_SERVER[ 'HTTP_ACCEPT_LANGUAGE' ] ) && $_SERVER[ 'HTTP_ACCEPT_LANGUAGE' ] != '' ? $_SERVER[ 'HTTP_ACCEPT_LANGUAGE' ] : 'de';
            $str = (string) strtolower( $str );

            $arrLanguages = explode( ',', $str );
            foreach ( $arrLanguages as $strLanguage )
            {
                $strTag = substr( $strLanguage, 0, 2 );

                if ( $strTag != '' && preg_match( '/^[a-z]{2}$/', $strTag ) )
                {
                    $arrAccepted[] = $strTag;
                }
            }

            $this->_httpAcceptLanguage = array_slice( array_unique( $arrAccepted ), 0, 8 );
        }

        return $this->_httpAcceptLanguage;
    }

    /**
     * Return accepted encoding types as array
     * @return array
     */
    public function httpAcceptEncoding()
    {
        return array_values( array_unique( explode( ',', strtolower( $_SERVER[ 'HTTP_ACCEPT_ENCODING' ] ) ) ) );
    }

    /**
     * Return the user agent as string
     * @return string
     */
    public function httpUserAgent()
    {
        if ( $this->_httpUserAgent === null )
        {
	        if ( isset( $_SERVER[ 'HTTP_USER_AGENT' ] ) )
	        {
		        $ua = $_SERVER[ 'HTTP_USER_AGENT' ];
	        }
	        elseif ( isset( $HTTP_SERVER_VARS[ 'HTTP_USER_AGENT' ] ) )
	        {
		        $ua = $HTTP_SERVER_VARS[ 'HTTP_USER_AGENT' ];
	        }
	        else
	        {
		        $ua = 'undefined';
	        }

            $this->_httpUserAgent = preg_replace( '/javascript|vbscri?pt|script|applet|alert|document|write|cookie/i', '', strip_tags( $ua ) );
        }


        return $this->_httpUserAgent;
    }

    /**
     * Return the HTTP Host
     * @return string
     */
    public function httpHost()
    {
        return httpHost();
    }

    /**
     * Return the HTTP X-Forwarded-Host
     * @return string
     */
    public function httpXForwardedHost()
    {
        return httpXForwardedHost();
    }

    /**
     * Return true if the current page was requested via an SSL connection
     * @return boolean
     */
    public function SSL()
    {
        return isSSL();
    }

    /**
     * Return the current URL without path or query string
     * @return string
     */
    public function url()
    {
        $xhost = $this->httpXForwardedHost();
        $protocol = $this->SSL() ? 'https://' : 'http://';
        return $protocol . (!empty( $xhost ) ? $xhost . '/' : '') . $this->httpHost();
    }

    /**
     *
     * @return string/null
     */
    public function proxy()
    {
        $ra = $_SERVER[ 'REMOTE_ADDR' ];

        if ( $ra === '' && isset( $HTTP_SERVER_VARS[ 'REMOTE_ADDR' ] ) )
        {
            $ra = $HTTP_SERVER_VARS[ 'REMOTE_ADDR' ];
        }

        $proxy = null;

        if ( isset( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) && !empty( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) )
        {
            $proxy = $ra;
        }
        elseif ( isset( $HTTP_SERVER_VARS[ 'HTTP_X_FORWARDED_FOR' ] ) && !empty( $HTTP_SERVER_VARS[ 'HTTP_X_FORWARDED_FOR' ] ) )
        {
            $proxy = $ra;
        }


        return $proxy;
    }

    /**
     * Return the real REMOTE_ADDR even if a proxy server is used
     * @return string
     */
    public function ip()
    {
        global $HTTP_SERVER_VARS;

        $ra = $_SERVER[ 'REMOTE_ADDR' ];

        if ( $ra === '' && isset( $HTTP_SERVER_VARS[ 'REMOTE_ADDR' ] ) )
        {
            $ra = $HTTP_SERVER_VARS[ 'REMOTE_ADDR' ];
        }

        $ip = null;

        if ( isset( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) && !empty( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) )
        {
            $ip = $_SERVER[ 'HTTP_X_FORWARDED_FOR' ];
        }
        elseif ( isset( $HTTP_SERVER_VARS[ 'HTTP_X_FORWARDED_FOR' ] ) && !empty( $HTTP_SERVER_VARS[ 'HTTP_X_FORWARDED_FOR' ] ) )
        {
            $ip = $HTTP_SERVER_VARS[ 'HTTP_X_FORWARDED_FOR' ];
        }

        if ( $ip === null && isset( $_SERVER[ 'HTTP_CLIENT_IP' ] ) && !empty( $_SERVER[ 'HTTP_CLIENT_IP' ] ) )
        {
            $ip = $_SERVER[ 'HTTP_CLIENT_IP' ];
        }
        elseif ( $ip === null && isset( $HTTP_SERVER_VARS[ 'HTTP_CLIENT_IP' ] ) && !empty( $HTTP_SERVER_VARS[ 'HTTP_CLIENT_IP' ] ) )
        {
            $ip = $HTTP_SERVER_VARS[ 'HTTP_CLIENT_IP' ];
        }
        else
        {
            $ip = $ra;
        }

        return $ip;


        // return (!empty( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) && substr( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ], 0, 1 ) !== ':' ? $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] : $_SERVER[ 'REMOTE_ADDR' ]);
    }

    /**
     * Return the
     * @return string
     */
    public function getUserHost()
    {
        $strServer = $this->ip();

        // Special workaround for Strato users
        if ( $strServer )
        {
            //$strServer = @gethostbyaddr($strServer);
            $strServer = $this->nslookup( $strServer );
        }
        else
        {
            $strServer = '';
        }

        return $strServer;
    }

    /*
     * This function returns the real hostname of an ip address.
     *
     * @param: $ip - the ip address in format x.x.x.x where x are
     *         numbers (0-255) or the hostname you want to lookup
     * @return: returns the hostname as string. Something like 'user-id.isp-dialin.tld'
     *
     * Warning: $ip must be validated before calling this function.
     */

    /**
     * @param $ip
     * @return string
     */
    public function nslookup( $ip )
    {

        return gethostbyaddr($ip);

        /*



        // execute nslookup command
        try {
            exec( 'nslookup ' . $ip, $op );
        } catch (Exception $e) {
            throw new BaseException($e->getMessage());
        }

        // php is running on windows machine
        if ( IS_WIN )
        {
            return substr( $op[ 3 ], 6 );
        }
        else
        {
            // on linux nslookup returns 2 diffrent line depending on
            // ip or hostname given for nslookup
            if ( stripos( $op[ 4 ], 'name = ' ) > 0 )
                return substr( $op[ 4 ], stripos( $op[ 4 ], 'name =' ) + 7, -1 );
            else
                return substr( $op[ 4 ], stripos( $op[ 4 ], 'Name:' ) + 6 );
        }
        */
    }

    /**
     * Return the SERVER_ADDR
     * @return string
     */
    public function server()
    {
        $strServer = !empty( $_SERVER[ 'SERVER_ADDR' ] ) ? $_SERVER[ 'SERVER_ADDR' ] : $_SERVER[ 'LOCAL_ADDR' ];

        // Special workaround for Strato users
        if ( empty( $strServer ) )
        {
            $strServer = gethostbyname( $_SERVER[ 'SERVER_NAME' ] );
        }

        return $strServer;
    }

    /**
     * Return the relative path to the base directory (e.g. /path)
     * @return string
     */
    public function path()
    {
        return preg_replace( '/\/$/', '', BASE ) . '/';
    }

    /**
     * Return the relativ path to the script (e.g. index.php)
     * @return string
     */
    public function script()
    {
        return preg_replace( '/^' . preg_quote( BASE, '/' ) . '\/?/i', '', $this->scriptName() );
    }

    /**
     * Return the relativ path to the script and include the request (e.g. index.php?id=2)
     * @return string
     */
    public function request()
    {
        $strRequest = preg_replace( '/^' . preg_quote( BASE, '/' ) . '\/?/i', '', $this->requestUri() );

        // IE security fix (thanks to Michiel Leideman)
        $strRequest = str_replace( array(
            '<',
            '>',
            '"' ), array(
            '%3C',
            '%3E',
            '%22' ), $strRequest );

        // Do not urldecode() here (thanks to Russ McRee)!
        return $strRequest;
    }

    /**
     * Return the current URL and path that can be used in a <base> tag
     * @return string
     */
    public function base()
    {
        return $this->url() . preg_replace( '/\/$/', '', BASE ) . '/';
    }

    /**
     * Return the current host name
     * @return string
     */
    public function host()
    {
        return getHostname();
    }

    /**
     * Return true on Ajax requests
     * @return boolean
     */
    public function isAjaxRequest()
    {
        return (isset( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) && $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] == 'XMLHttpRequest');
    }





    /**
     * Return the current browser
     * @return array (browser, version)
     */
    public function browser()
    {
        Tracking::init();
        return Tracking::getBrowser();
    }

    /**
     * Return the current refferer
     * @return string
     */
    public function refferer()
    {
        return isset( $_SERVER[ 'HTTP_REFERER' ] ) ? $_SERVER[ 'HTTP_REFERER' ] : '';
    }

    /**
     * Return the current location incl. post/get vars
     * @return string
     */
    public function location()
    {
        $location = $this->phpSelf();
        $params = '';

        if ( HTTP::requestType() === 'post' )
        {

            foreach ( HTTP::post() as $key => $value )
            {
                $params .= ($params ? '&amp;' : '') . $key . '=' . $value;
            }
        }
        else
        {
            foreach ( HTTP::get() as $key => $value )
            {
                $params .= ($params ? '&amp;' : '') . $key . '=' . $value;
            }
        }

        return $location . ($params ? '?' . $params : '');
    }

    /**
     * @var null
     */
    private $osType = null;

    /**
     * @var null
     */
    private $os = null;

    /**
     * @var null
     */
    private $osBuild = null;

    /**
     * get the Server OperationSystem
     *
     * @return array array(osType, os (short name), osbuild)
     */
    public function getOs()
    {
        if ( substr( PHP_OS, 0, 3 ) == "WIN" )
        {
            $this->osType = $this->winOsName();
            $this->osBuild = php_uname( 'v' );
            $this->os = "windows";
        }
        elseif ( PHP_OS == "FreeBSD" )
        {
            $this->os = "freebsd";
            $this->osType = "FreeBSD";
            $this->osBuild = php_uname( 'r' );
        }
        elseif ( PHP_OS == "Darwin" )
        {
            $this->os = "darwin";
            $this->osType = "Apple OS X";
            $this->osBuild = php_uname( 'r' );
        }
        elseif ( PHP_OS == "Linux" )
        {
            $this->os = "linux";
            $this->osType = "Linux";
            $this->osBuild = php_uname( 'r' );
        }
        else
        {
            $this->os = "nocpu";
            $this->osType = "Unknown OS";
            $this->osBuild = php_uname( 'r' );
        }

        return array(
            $this->osType,
            $this->os,
            $this->osBuild );
    }

    /**
     * @return string
     */
    private function winOsName()
    {
        $wUnameB = php_uname( "v" );
        $wUnameBM = php_uname( "r" );
        $wUnameB = preg_replace( "#build\s*#is", "", $wUnameB );

        if ( $wUnameBM === "5.0" && ($wUnameB == "2195") )
        {
            $wVer = "Windows 2000";
        }

        if ( $wUnameBM === "5.1" && ($wUnameB == "2600") )
        {
            $wVer = "Windows XP";
        }

        if ( $wUnameBM === "5.2" && ($wUnameB == "3790") )
        {
            $wVer = "Windows Server 2003";
        }

        if ( $wUnameBM === "6.0" && (php_uname( "v" ) === "build 6000") )
        {
            $wVer = "Windows Vista";
        }

        if ( $wUnameBM === "6.0" && (php_uname( "v" ) === "build 6001") )
        {
            $wVer = "Windows Vista SP1";
        }

        if ( $wUnameBM === "6.0" && (php_uname( "v" ) === "build 6002") )
        {
            $wVer = "Windows Vista SP2";
        }


        if ( $wUnameBM === "6.1" )
        {
            $wVer = "Windows Seven";
        }

        return $wVer;
    }

}

?>