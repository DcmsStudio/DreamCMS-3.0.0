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
 * @file        Remote.php
 *
 */
class Remote
{

    /**
     * @var null
     */
    protected static $objInstance = null;

    /**
     * @var null
     */
    protected $_url = null;

    /**
     * @var null
     */
    protected $_extraHeaders = null; // store get params
    /**
     * @var null
     */

    protected $_postParams = null; // store post params
    /**
     * @var int
     */

    protected $_maxRedirects = 3;

    /**
     * @var int
     */
    protected $_redirects = 0;

    /**
     * @var bool
     */
    protected $_isSSL = false;

    /**
     * @var int
     */
    protected $_port = 80;

    /**
     * @var bool
     */
    protected $useCURL = false;

    /**
     * @var int
     */
    protected $timeout = 5;

    /**
     *
     * @var type
     */
    protected $remoteData = array(
        'headers' => null,
        'content' => null
    );

    /**
     * @var bool
     */
    protected $success = true;

    /**
     * @var array
     */
    protected $headers = array();

    /**
     * @var
     */
    protected $body;

    /**
     * @var
     */
    protected $status;

    /**
     * @var
     */
    protected $error;

    /**
     * Return the current object instance (Singleton)
     * @return Remote
     */
    public static function getInstance()
    {
        if ( self::$objInstance === null )
        {
            self::$objInstance = new Remote();

            if ( function_exists( 'curl_init' ) )
            {
                self::$objInstance->useCURL = true;
            }
        }

        return self::$objInstance;
    }

    /**
     *
     * @param string $url
     * @return \Remote
     */
    public function setUrl( $url )
    {
        $this->_url = $url;
        return $this;
    }

    /**
     *
     * @param string $key
     * @param mixed $value
     * @return \Remote
     */
    public function appendExtraHeader( $key, $value = null )
    {
        if ( !is_array( $this->_extraHeaders ) )
        {
            $this->_extraHeaders = array();
        }

        $this->_extraHeaders[ $key ] = $value;

        return $this;
    }

    /**
     *
     * @param array $params
     * @return \Remote
     */
    public function extraHeaders( array $params )
    {
        $this->_extraHeaders = $params;

        return $this;
    }

    /**
     *
     * @param string $key
     * @param mixed $value
     * @return \Remote
     */
    public function appendPostParams( $key, $value = null )
    {
        if ( !is_array( $this->_postParams ) )
        {
            $this->_postParams = array();
        }

        $this->_postParams[ $key ] = $value;

        return $this;
    }

    /**
     *
     * @param array $params
     * @return \Remote
     */
    public function setPostParams( array $params )
    {
        $this->_postParams = $params;

        return $this;
    }

    /**
     *
     * @return string/null
     */
    public function getContent()
    {
        return $this->body;
    }

    /**
     *
     * @return integer/null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     *
     * @return type
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     *
     * @return boolean
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * @return int|string
     */
    public static function get_curl_version()
    {
        if ( is_array( $curl = curl_version() ) )
        {
            $curl = $curl[ 'version' ];
        }
        elseif ( substr( $curl, 0, 5 ) === 'curl/' )
        {
            $curl = substr( $curl, 5, strcspn( $curl, "\x09\x0A\x0B\x0C\x0D", 5 ) );
        }
        elseif ( substr( $curl, 0, 8 ) === 'libcurl/' )
        {
            $curl = substr( $curl, 8, strcspn( $curl, "\x09\x0A\x0B\x0C\x0D", 8 ) );
        }
        else
        {
            $curl = 0;
        }
        return $curl;
    }

    /**
     * @return mixed
     */
    public function run()
    {
        $this->_isSSL = false;
        $isPost = false;

        if ( is_array( $this->_postParams ) )
        {
            $isPost = true;

            if ( strpos( $this->_url, '?' ) !== false )
            {
                $_u = explode( '?', $this->_url );
                $this->_url = array_shift( $_u );
            }
        }


        $url_parts = parse_url( $this->_url );
        $socket_host = $url_parts[ 'host' ];

        if ( isset( $url_parts[ 'scheme' ] ) && strtolower( $url_parts[ 'scheme' ] ) === 'https' )
        {
            $this->_isSSL = true;
            $socket_host = 'ssl://' . $url_parts[ 'host' ];
            $this->_port = 443;
        }

        if ( !isset( $url_parts[ 'port' ] ) )
        {
            $this->_port = 80;
        }


        if ( $this->useCURL )
        {
            $fp = curl_init();
            $headers = array();

            if ( is_array( $this->_extraHeaders ) )
            {
                foreach ( $this->_extraHeaders as $key => $value )
                {
                    $headers[] = $key . ": " . $value;
                }
            }

            if ( $isPost )
            {
                // Add post Params if is in Post method
                $_postData = '';
                if ( is_array( $this->_postParams ) )
                {
                    foreach ( $this->_postParams as $key => $value )
                    {
                        $_postData .= ($_postData ? '&' : '') . $key . "=" . $value;
                    }
                }

                curl_setopt( $fp, CURLOPT_POST, true );
                curl_setopt( $fp, CURLOPT_POSTFIELDS, $_postData );
            }

            if ( version_compare( self::get_curl_version(), '7.10.5', '>=' ) )
            {
                curl_setopt( $fp, CURLOPT_ENCODING, '' );
            }

            curl_setopt( $fp, CURLOPT_URL, $this->_url );
            curl_setopt( $fp, CURLOPT_HEADER, 1 );
            curl_setopt( $fp, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt( $fp, CURLOPT_TIMEOUT, $this->timeout );
            curl_setopt( $fp, CURLOPT_CONNECTTIMEOUT, $this->timeout );
            curl_setopt( $fp, CURLOPT_REFERER, $this->_url );
            curl_setopt( $fp, CURLOPT_USERAGENT, 'DreamCMS ' );
            curl_setopt( $fp, CURLOPT_HTTPHEADER, $headers );


            if ( !ini_get( 'open_basedir' ) && !ini_get( 'safe_mode' ) && version_compare( self::get_curl_version(), '7.15.2', '>=' ) )
            {
                curl_setopt( $fp, CURLOPT_FOLLOWLOCATION, 1 );
                curl_setopt( $fp, CURLOPT_MAXREDIRS, $this->_maxRedirects );
            }


            $this->headers = curl_exec( $fp );


            if ( curl_errno( $fp ) === 23 || curl_errno( $fp ) === 61 )
            {
                curl_setopt( $fp, CURLOPT_ENCODING, 'none' );
                $this->headers = curl_exec( $fp );
            }


            if ( curl_errno( $fp ) )
            {
                $this->error = 'cURL error ' . curl_errno( $fp ) . ': ' . curl_error( $fp );
                $this->success = false;
            }
            else
            {
                $info = curl_getinfo( $fp );
                curl_close( $fp );

                $this->headers = explode( "\r\n\r\n", $this->headers, $info[ 'redirect_count' ] + 1 );


                $this->headers = array_pop( $this->headers );


                $parser = new Remote_HTTP_Parser( $this->headers );
                if ( $parser->parse() )
                {
                    $this->headers = $parser->headers;
                    $this->body = $parser->body;
                    $this->status_code = $parser->status_code;

                    if ( (in_array( $this->status_code, array(
                                300,
                                301,
                                302,
                                303,
                                307 ) ) ||
                            $this->status_code > 307 && $this->status_code < 400) && isset( $this->headers[ 'location' ] ) && $this->_redirects < $this->_maxRedirects
                    )
                    {
                        $this->_redirects++;

                        $this->_url = Remote_Base::absolutize_url( $this->headers[ 'location' ], $this->_url );

                        return $this->run();
                    }
                }
            }
        }
        else
        {

            $fp = @fsockopen( $socket_host, $this->_port, $errno, $errstr, $this->timeout );
            if ( !$fp )
            {
                $this->error = 'fsockopen error: ' . $errstr;
                $this->success = false;
            }
            else
            {
                stream_set_timeout( $fp, $timeout );
                if ( isset( $url_parts[ 'path' ] ) )
                {
                    if ( isset( $url_parts[ 'query' ] ) )
                    {
                        $get = $url_parts[ 'path' ] . '?' . $url_parts[ 'query' ];
                    }
                    else
                    {
                        $get = $url_parts[ 'path' ];
                    }
                }
                else
                {
                    $get = '/';
                }


                if ( !$isPost )
                {
                    $out = "GET $get HTTP/1.1\r\n";
                }
                else
                {
                    $out = "POST $get HTTP/1.1\r\n";
                }

                $out .= "Host: " . $url_parts[ 'host' ] . "\r\n";
                $out .= "User-Agent: DreamCMS\r\n";

                if ( extension_loaded( 'zlib' ) )
                {
                    $out .= "Accept-Encoding: x-gzip,gzip,deflate\r\n";
                }

                if ( isset( $url_parts[ 'user' ] ) && isset( $url_parts[ 'pass' ] ) )
                {
                    $out .= "Authorization: Basic " . base64_encode( $url_parts[ 'user' ] . ':' . $url_parts[ 'pass' ] ) . "\r\n";
                }


                if ( is_array( $this->_extraHeaders ) )
                {
                    foreach ( $this->_extraHeaders as $key => $value )
                    {
                        $out .= $key . ": " . $value;
                    }
                }

                $out .= "Connection: Close\r\n\r\n";

                // Add post Params if is in Post method
                if ( $isPost )
                {
                    if ( is_array( $this->_postParams ) )
                    {
                        $_post = '';
                        foreach ( $this->_postParams as $key => $value )
                        {
                            $_post .= ($_post ? '&' : '') . $key . "=" . $value;
                        }

                        $out .= $_post;
                    }
                }

                fwrite( $fp, $out );

                $info = stream_get_meta_data( $fp );

                $this->headers = '';
                while ( !$info[ 'eof' ] && !$info[ 'timed_out' ] )
                {
                    $this->headers .= fread( $fp, 1160 );
                    $info = stream_get_meta_data( $fp );
                }


                if ( !$info[ 'timed_out' ] )
                {
                    $parser = new Remote_HTTP_Parser( $this->headers );

                    if ( $parser->parse() )
                    {
                        $this->headers = $parser->headers;
                        $this->body = $parser->body;
                        $this->status_code = $parser->status_code;

                        if ( (in_array( $this->status_code, array(
                                    300,
                                    301,
                                    302,
                                    303,
                                    307 ) ) ||
                                $this->status_code > 307 && $this->status_code < 400) && isset( $this->headers[ 'location' ] ) && $this->_redirects < $this->_maxRedirects
                        )
                        {
                            $this->_redirects++;
                            $this->_url = Remote_Base::absolutize_url( $this->headers[ 'location' ], $this->_url );
                            return $this->run();
                        }


                        if ( isset( $this->headers[ 'content-encoding' ] ) )
                        {
                            // Hey, we act dumb elsewhere, so let's do that here too
                            switch ( strtolower( trim( $this->headers[ 'content-encoding' ], "\x09\x0A\x0D\x20" ) ) )
                            {
                                case 'gzip':
                                case 'x-gzip':
                                    $decoder = new Remote_gzDecode( $this->body );

                                    if ( !$decoder->parse() )
                                    {
                                        $this->error = 'Unable to decode HTTP "gzip" stream';
                                        $this->success = false;
                                    }
                                    else
                                    {
                                        $this->body = $decoder->data;
                                    }
                                    break;

                                case 'deflate':
                                    if ( ($decompressed = gzinflate( $this->body )) !== false )
                                    {
                                        $this->body = $decompressed;
                                    }
                                    else if ( ($decompressed = gzuncompress( $this->body )) !== false )
                                    {
                                        $this->body = $decompressed;
                                    }
                                    else if ( function_exists( 'gzdecode' ) && ($decompressed = gzdecode( $this->body )) !== false )
                                    {
                                        $this->body = $decompressed;
                                    }
                                    else
                                    {
                                        $this->error = 'Unable to decode HTTP "deflate" stream';
                                        $this->success = false;
                                    }
                                    break;

                                default:
                                    $this->error = 'Unknown content coding';
                                    $this->success = false;
                            }
                        }
                    }
                }
                else
                {
                    $this->error = 'fsocket timed out';
                    $this->success = false;
                }

                fclose( $fp );
            }
        }
    }

}
