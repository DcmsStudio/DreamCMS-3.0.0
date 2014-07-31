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
 * @package      DreamCMS
 * @version      3.0.0 Beta
 * @category     Framework
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Output.php
 */

/** @noinspection PhpUndefinedClassInspection */
class Output extends Loader
{

    /**
     *
     */
    const HTTP_MODE = 1;

    /**
     *
     */
    const RETURN_MODE = 2;

    /**
     *
     */
    const XHTML = 0;

    /**
     *
     */
    const HTML = 1;

    /**
     *
     */
    const FORCED_XHTML = 2;

    /**
     *
     */
    const WML = 3;

    /**
     *
     */
    const XML = 4;

    /**
     *
     */
    const AJAX = 5;



    /**
     *
     */
    const JAVASCRIPT = 6;

    /**
     *
     */
    const CSS = 7;


    /**
     * @var array
     */
    private $_headers = array();

    /**
     * @var null
     */
    protected static $_instance = null;

    /**
     * @var string
     */
    protected $_body = '';

    /**
     * @var int
     */
    protected $_headerState = 200;

    /**
     * @var string
     */
    protected $_version = '1.1';

    /**
     * @var null
     */
    protected $_Mode = null;

    /**
     * @var bool
     */
    private $_doEncode = true;

    private $_isResource = false;

    /**
     * List of all known HTTP response codes to
     * translate numeric codes to messages.
     *
     * @var array
     */
    protected $messages = array(
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found', // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',
        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Temporarily Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    );

    private $settings = array(
        "X-Frame-Options" => "SAMEORIGIN",
        "X-XSS-Protection" => "1; mode=block",
        "X-Permitted-Cross-Domain-Policies" => "master-only",
        "X-Content-Type-Options" => "nosniff",
        "Content-Security-Policy" => false, //
        "Strict-Transport-Security" => false, // max-age=15768000
      #  "Content-Type" => "text/html; charset=utf-8"
    );

    /**
     * Prevent cloning of the object (Singleton)
     */
    final private function __clone()
    {

    }

    /**
     * Return the current object instance (Singleton)
     *
     * @return Output
     */
    public static function getInstance()
    {

        if ( self::$_instance === null )
        {
            self::$_instance = new Output();
        }

        return self::$_instance;
    }

    public function __destruct()
    {

        parent::__destruct();
        self::$_instance = null;
    }

    /**
     *
     * @param integer $_Mode
     * @return Output
     */
    public function setMode($_Mode = self::HTTP_MODE)
    {

        $this->_Mode = $_Mode;

        return $this;
    }

    /**
     *
     * @param type $key
     * @param type $value
     * @return Output
     */
    public function addHeader($key, $value)
    {

        // Http Header not allowed
        if ( stripos( $key, 'HTTP/1' ) !== false )
        {
            return;
        }

        if ( strpos( $key, ':' ) !== false )
        {
            $key = trim( str_replace( ':', '', $key ) );
        }


        $this->_headers[ $key ] = $value;

        return $this;
    }

    /**
     * This function will remove a existing Header before send to Output
     *
     * @param string $key
     * @return Output
     */
    public function removeHeader($key)
    {
        if ( isset( $this->_headers[ $key ] ) )
        {
            unset( $this->_headers[ $key ] );
        }

        return $this;
    }


    /**
     * @param $key
     */
    private function removeHeaderFiltered($key) {
        if (is_array($this->_headers)) {
            foreach ( $this->_headers as $name => $val ) {
                if ( stripos( $name, $key ) !== false ) {
                    unset($this->_headers[$name]);
                    return;
                }
            }
        }
    }


    /**
     *
     * @return Output
     */
    public function sendHeaders()
    {

        if ( headers_sent() )
        {
            // throw new BaseException('Headers Allready Send');
        }


        $this->_headers = array_unique( $this->_headers );
        $this->removeHeaderFiltered('Server');
        $this->removeHeaderFiltered('X-Powered-By');



        // send status
        header($_SERVER['SERVER_PROTOCOL'] .' ' . $this->getStatus() . ' ' . $this->messages[ $this->getStatus() ] );
  //      header( 'HTTP/1.0 ' . $this->getStatus() . ' ' . $this->messages[ $this->getStatus() ] );

        if ( $this->getStatus() === 503 )
        {
            header( 'Status: 503 Service Temporarily Unavailable' );
            header( 'Retry-After: 86400' );

            $this->removeHeaderFiltered('Status');
            $this->removeHeaderFiltered('Retry-After');
        }

        foreach ($this->settings as $key => $val) {
            if ($val !== false) {
                if ($key === "Content-Security-Policy") {
                    // so many policies...
                    header("X-WebKit-CSP: " . $val);
                    header("X-Content-Security-Policy: " . $val);
                    header("Content-Security-Policy: " . $val);

                    $this->removeHeaderFiltered('X-WebKit-CSP');
                    $this->removeHeaderFiltered('X-Content-Security-Policy');
                    $this->removeHeaderFiltered($key);
                } else {
                    header($key . ": " . $val);
                    $this->removeHeaderFiltered($key);
                }
            }
        }


        $this->load( 'Document' );
        if ( $this->Document->getLastModified() > 0 )
        {
            header( "Last-Modified: " . gmdate( "D, d M Y H:i:s", $this->Document->getLastModified() ) . " GMT", true );
            $this->removeHeaderFiltered('Last-Modified');
        }

        if (!$this->getApplication()->isBackend()) {
            if (Settings::get('sendnocacheheaders') && $this->_Mode != self::JAVASCRIPT && $this->_Mode != self::CSS && $this->_Mode != self::AJAX )
            {
                header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
                header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
                header("Cache-Control: post-check=0, pre-check=0", false);
                header("Pragma: no-cache");

                $this->removeHeaderFiltered('Pragma');
                $this->removeHeaderFiltered('Cache-Control');
                $this->removeHeaderFiltered('Expires');
            }
        }

        # header('X-Powered-By: DreamCMS v2.0.1');

        $this->removeHeaderFiltered('X-Powered-By');

        if (!$this->getApplication()->isBackend()) {
            if ( $this->_Mode !== self::JAVASCRIPT && $this->_Mode !== self::CSS && $this->_Mode !== self::AJAX ) {
                $this->removeHeaderFiltered('Vary');
                $this->removeHeaderFiltered('Content-Encoding');
            }
        }

        $contentTypeSet = false;

        foreach ( $this->_headers as $key => $value )
        {
            if ( trim( $key ) && strtolower( $key ) !== 'x-powered-by' && $value )
            {
                if ( strtolower( $key ) === 'content-type' )
                {
                    if ( stripos( $value, 'html' ) !== false || stripos( $value, 'xml' ) !== false )
                    {
                        $this->_doEncode = true;
                        $value .= '; charset=utf-8';
                        $contentTypeSet = true;
                        $this->_isResource = false;
                    }
                    else if ( stripos( $value, 'json' ) !== false )
                    {
                        $contentTypeSet = true;
                        $this->_isResource = true;
                    }

                    if ( stripos( $value, 'javascript' ) !== false )
                    {
                        $this->_doEncode = true;
                        $contentTypeSet  = true;
                        $this->_isResource = true;
                    }


                    if ( stripos( $value, 'css' ) !== false )
                    {
                        $this->_isResource = true;
                    }

                    if ( stripos( $value, 'image/' ) !== false || stripos( $value, 'audio/' ) !== false || stripos( $value, 'video/' ) !== false )
                    {
                        $this->_isResource = true;
                    }

                    header( $key . ': ' . $value, true );

                    break;
                }
            }
        }


        foreach ( $this->_headers as $key => $value )
        {
            if ( trim( $key ) && strtolower( $key ) !== 'x-powered-by' && $value )
            {

                if ( !$contentTypeSet && strtolower( $key ) === 'content-type' )
                {
                    if ( stripos( $value, 'html' ) !== false || stripos( $value, 'xml' ) !== false )
                    {
                        $this->_doEncode = false;
                        $contentTypeSet  = true;
                    }
                    else if ( stripos( $value, 'json' ) !== false )
                    {
                        #$this->_doEncode = true;
                        $contentTypeSet = true;
                    }

                    if ( stripos( $value, 'javascript' ) !== false )
                    {
                        $this->_isResource = true;
                    }

                    if ( stripos( $value, 'css' ) !== false )
                    {
                        $this->_isResource = true;
                    }

                    if ( stripos( $value, 'image/' ) !== false || stripos( $value, 'audio/' ) !== false || stripos( $value, 'video/' ) !== false )
                    {
                        $this->_isResource = true;
                    }

                    if ( ( stripos( $value, 'html' ) !== false || stripos( $value, 'javascript' ) !== false ) )
                    {
                        $this->_doEncode = true;
                        $value .= '; charset=utf-8';
                        $contentTypeSet = true;
                    }

                }

                header( $key . ': ' . $value, true );
            }
        }

     //   header( 'X-Powered-By: DreamCMS ' . VERSION );


        if ( !$contentTypeSet && $this->_Mode === self::HTML || $this->_Mode === self::XHTML )
        {
            header( 'Content-Type: text/html; charset=utf-8', true );
        }
        else if ( !$contentTypeSet && $this->_Mode === self::XML )
        {
            header( 'Content-Type: text/xml; charset=utf-8', true );
        }
        else if ( !$contentTypeSet && $this->_Mode === self::AJAX )
        {
            header( 'Content-Type: application/json; charset=utf-8', true );
        }
        else if ( !$contentTypeSet && $this->_Mode === self::JAVASCRIPT )
        {
            header( 'Content-Type: application/javascript; charset=utf-8', true );
        }
        else if ( !$contentTypeSet && $this->_Mode === self::CSS )
        {
            header( 'Content-Type: text/css; charset=utf-8', true );
        }

        return $this;
    }

    /**
     * Append output string
     *
     * @param string $body
     * @return Output
     */
    public function appendOutput($body = '')
    {

        $this->_body .= $body;

        return $this;
    }

    /**
     * Get the HTTP response status code
     *
     * @return int
     */
    public function getStatus()
    {

        return $this->_headerState;
    }

    /**
     *
     * @param null $code
     * @internal param int $mode
     * @return Output
     */
    public function setStatus($code = null)
    {

        if ( $this->messages[ (int)$code ] )
        {
            $this->_headerState = (int)$code;
        }

        return $this;
    }

    /**
     * default Http Header Version is 1.1
     *
     * @param string $version
     * @return Output
     */
    public function setHttpVersion($version = '1.1')
    {

        $this->_version = $version;

        return $this;
    }

    /**
     * Check whether the response is a error
     *
     * @return boolean
     */
    public function isError()
    {

        $restype = floor( $this->_headerState / 100 );
        if ( $restype == 4 || $restype == 5 )
        {
            return true;
        }

        return false;
    }

    /**
     * Check whether the response in successful
     *
     * @return boolean
     */
    public function isSuccessful()
    {

        $restype = floor( $this->_headerState / 100 );
        if ( $restype == 2 || $restype == 1 )
        { // Shouldn't 3xx count as success as well ???
            return true;
        }

        return false;
    }

    /**
     * Check whether the response is a redirection
     *
     * @return boolean
     */
    public function isRedirect()
    {

        $restype = floor( $this->_headerState / 100 );
        if ( $restype == 3 )
        {
            return true;
        }

        return false;
    }

    /**
     *
     * @throws BaseException
     * @uses Strings::utf8_to_unicode, Strings::fixLatin
     *
     * @return string if mode is RETURN_MODE
     *
     */
    public function sendOutput()
    {

        if ( $this->_Mode === self::RETURN_MODE )
        {
            if ( !$this->_doEncode )
            {
                return $this->_body;
            }


            return Strings::utf8_to_unicode( Strings::fixLatin( $this->_body ) );
        }

        if ( !$this->sendHeaders() )
        {
            throw new BaseException( 'Headers Allready Send' );
        }

        if ( $this->_body )
        {
            $this->send($this->_body);
        }

        exit;
    }

    /**
     * @param string $outputString
     * @param null $type
     * @throws BaseException
     */
    public function send($outputString, $type = null)
    {
        if ( !$this->sendHeaders() )
        {
            throw new BaseException( 'Headers Allready Send' );
        }

        $zlibOn = ini_get( 'zlib.output_compression' ) || ( ini_set( 'zlib.output_compression', 0 ) === false );
        $encodings = ( isset( $_SERVER[ 'HTTP_ACCEPT_ENCODING' ] ) ) ? strtolower( $_SERVER[ 'HTTP_ACCEPT_ENCODING' ] ) : "";
        $encoding  = preg_match( '/\b(x-gzip|gzip)\b/', $encodings, $match ) ? $match[ 1 ] : "";

        // Is northon antivirus header
        if ( isset( $_SERVER[ '---------------' ] ) )
        {
            $encoding = "x-gzip";
        }

        $supportsGzip = !empty( $encoding ) && !$zlibOn && function_exists( 'gzencode' );



       # if ( strlen( $outputString ) > 1024 )
       # {
            if ( $this->getApplication()->getSystemConfig()->get( 'gzip', false ) && $this->getApplication()->getSystemConfig()->get( 'gziplevel', 0 ) > 0 )
            {
                if ( $supportsGzip )
                {
                    header( 'Vary: Accept-Encoding' ); // Handle proxies

                    if ( false !== stripos( $_SERVER[ 'HTTP_ACCEPT_ENCODING' ], 'deflate' ) && function_exists( 'gzdeflate' ) )
                    {
                        header( 'Content-Encoding: deflate', true );
                        $outputString = gzdeflate( $outputString, $this->getApplication()->getSystemConfig()->get( 'gziplevel', 9 ) );
                    }
                    elseif ( false !== stripos( $_SERVER[ 'HTTP_ACCEPT_ENCODING' ], 'gzip' ) && function_exists( 'gzencode' ) )
                    {
                        header( 'Content-Encoding: gzip', true );
                        $outputString = gzencode( $outputString, $this->getApplication()->getSystemConfig()->get( 'gziplevel', 9 ) );
                    }

                }
                else
                {
                    header( 'Vary: Accept-Encoding' ); // Handle proxies
                    ini_set( 'zlib.output_compression', 1);
                    ini_set( 'zlib.output_compression_level', $this->getApplication()->getSystemConfig()->get( 'gziplevel', 9 ) );
                }
            }
        #}

        if ( !$this->_isResource && strpos( $outputString, '</head>' ) !== false )
        {
            #echo $this->_doEncode ? Strings::fixLatin($outputString) : $outputString;

            if ( !$this->getApplication()->getSystemConfig()->get( 'gzip', false ) )
            {
                $out = explode( '</head>', $outputString );
                echo $out[ 0 ];

                //echo ( $this->_doEncode ? Strings::fixLatin($out[ 0 ]) : $out[ 0 ] ) . '</head>';
                ob_flush();
                //usleep(250000);
                //echo $this->_doEncode ? Strings::fixLatin($out[ 1 ]) : $out[ 1 ];
                echo $out[ 1 ];
            }
            else
            {
                echo $outputString;
                // echo $this->_doEncode ? Strings::fixLatin($outputString) : $outputString;
            }

        }
        else
        {

            if ( !$this->_isResource && $this->_doEncode )
            {
                if ( $type === null )
                {
                    echo $outputString;
                    // echo Strings::fixLatin($outputString);
                }
                else
                {
                    echo $outputString;
                }
            }
            else
            {
                echo $outputString;
            }

        }


        if ( !$this->getApplication()->getSystemConfig()->get( 'gzip', false ) )
        {
            ob_end_flush();
        }

        exit;
    }

}

?>