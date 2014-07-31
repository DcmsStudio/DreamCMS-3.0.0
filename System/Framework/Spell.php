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
 * @file        Spell.php
 *
 */
class Spell
{

    const PREFIX = "data:,";

    const LENGTH = 1994;

    private $driver = null; // Afterthedeadline, Google

    /**
     *
     * @param string $driver
     */

    public function __construct( $driver = null )
    {
        $this->driver = $driver;
    }

    /**
     *
     * @param string $driver
     * @return \Spell
     */
    public function setDriver( $driver = null )
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     *
     * @param string $text
     * @param string $lang
     * @return mixed|null|void
     */
    public function getSpell( $text, $lang = '' )
    {
        switch ( strtolower( $this->driver ) )
        {
            case 'google':
                return $this->getGoogleSpell( $text, $lang );
                breaK;
            case 'afterthedeadline':
                return $this->getAfterthedeadlineSpell( $text, $lang );
                breaK;
        }

        return null;
    }

    /**
     *
     * @param array $jsonArray
     */
    public function transformForTinyMCE( $jsonArray )
    {
        
    }

    /**
     *
     * @param string $text
     * @param string $lang
     * @return mixed
     */
    private function getGoogleSpell( $text, $lang = '' )
    {
        $curl = curl_init();
        curl_setopt( $curl, CURLOPT_URL, "https://www.googleapis.com/rpc?key=" . Settings::get( 'googleapikey', '' ) );
        curl_setopt( $curl, CURLOPT_POST, 1 );
        curl_setopt( $curl, CURLOPT_POSTFIELDS, '[{"method":"spelling.check","id":"p","params":{"language":"' . ($lang ? $lang : '') . '","text":"' . rawurlencode( $text ) . '"},"jsonrpc":"2.0","key":"p","apiVersion":"v2"}]' );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $curl, CURLOPT_HTTPHEADER, array(
            'Content-type: application/json' ) );
        $curl_results = curl_exec( $curl );
        curl_close( $curl );

        $json = json_decode( $curl_results, true );

        return $json;
    }

    /**
     *
     * @param string $text
     * @param string $lang
     */
    private function getAfterthedeadlineSpell( $text, $lang = '' )
    {
        // You get the option of hardcoding your API key here.  Do this if you don't want people seeing
        // your key when they do View -> Source.  
        $API_KEY = "";

        $postText = trim( $text );


        if ( strcmp( $API_KEY, "" ) != 0 )
        {
            $postText .= '&key=' . $API_KEY;
        }

        // I am a vampire
        // I have lost my fangs

        $url = $_GET[ 'url' ];

        // So I'm sad and I feel lonely
        // So I cry and I'm very angry
        // And I hate some garlic
        // So I'm so no more sad and
        // Ache yeah yeah

        $data = $this->_post( $postText, "service.afterthedeadline.com", $url );

        // I am a vampire and I am looking in the city
        // Pretty girls don't look at me
        // Don't look at me
        // Cause I don't have my fangs
        // But I have lost my fangs

        header( "Content-Type: text/xml" );

        echo $data[ 1 ];

        exit;
    }

    /**
     *
     */
    public function getSpellCss()
    {
        $API_KEY = 'cssproxy';

        $postText = 'data=' . $_GET[ 'data' ];
        $language = $_GET[ 'lang' ];

        if ( strcmp( $API_KEY, '' ) != 0 )
        {
            $postText .= '&key=' . $API_KEY;
        }

        $url = '/checkDocument';

        /* this function directly from akismet.php by Matt Mullenweg.  *props* */

        if ( strcmp( $language, 'en' ) == 0 || strcmp( $language, 'de' ) == 0 || strcmp( $language, 'es' ) == 0 || strcmp( $language, 'fr' ) == 0 || strcmp( $language, 'pt' ) == 0 )
        {
            $host = $language . '.service.afterthedeadline.com';
        }
        else
        {
            $host = 'service.afterthedeadline.com';
        }

        $data = $this->_post( str_replace( "\\'", "'", $postText ), $host, $url );
        header( "Content-Type: text/plain" );

        echo $this->encode_css( $data[ 1 ] );

        exit;
    }

    /**
     *
     * @param string $request
     * @param string $host
     * @param string $path
     * @param integer $port
     * @return string
     */
    private function _post( $request, $host, $path, $port = 80 )
    {
        $http_request = "POST $path HTTP/1.0\r\n";
        $http_request .= "Host: $host\r\n";
        $http_request .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $http_request .= "Content-Length: " . strlen( utf8_decode( $request ) ) . "\r\n";
        $http_request .= "User-Agent: AtD/0.1\r\n";
        $http_request .= "\r\n";
        $http_request .= utf8_decode( $request );

        $response = '';
        if ( false != ($fs = @fsockopen( $host, $port, $errno, $errstr, 10 )) )
        {
            fwrite( $fs, $http_request );

            while ( !feof( $fs ) )
            {
                $response .= fgets( $fs );
            }
            fclose( $fs );

            $response = explode( "\r\n\r\n", $response, 2 );
        }

        return $response;
    }

    /**
     *
     * @param string $string
     * @return string
     */
    private function encode_css( $string )
    {
        $quoted = rawurlencode( $string );
        $out = "";
        for ( $i = 0, $n = 0; $i < strlen( $quoted ); $i += LENGTH, $n++ )
        {
            $out .= "#c" . $n . "{background:url(" . PREFIX . substr( $quoted, $i, LENGTH ) . ");}\n";
        }
        return $out;
    }

}
