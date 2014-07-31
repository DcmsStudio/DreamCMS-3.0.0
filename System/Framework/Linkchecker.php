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
 * @file        Linkchecker.php
 *
 */
class LinkChecker
{

    /**
     * Current object instance (Singleton)
     * @var object
     */
    protected static $objInstance = null;

    /**
     * @var array
     */
    protected $settings = array(
        'timeoutTime' => 5 );

    /**
     * @var null
     */
    protected $extractedLinks = null;

    /**
     * @var null
     */
    protected $db = null;

    /**
     * Prevent cloning of the object (Singleton)
     */
    final private function __clone()
    {
        
    }

    /**
     * Return the current object instance (Singleton)
     * @return object
     */
    public static function getInstance()
    {
        if ( self::$objInstance === null )
        {
            self::$objInstance = new LinkChecker();
            self::$objInstance->db = Database::getInstance();
        }

        return self::$objInstance;
    }

    /**
     * Check all scaned links
     * returns array (
     *                  'link' => '<a href=....>your link</a>',
     *                  'url' => 'http://www.website.de/my-page.html',
     *                  'errormessage' => 'Request Entity Too Large', << Example
     *                  'isok' => true (if is ok)
     *                            false (if is not ok)
     *          )
     *
     * @return array
     */
    public function checkAllLinks()
    {
        if ( !is_array( $this->extractedLinks ) )
        {
            return true;
        }

        if ( $this->settings[ 'timeoutTime' ] > (ini_get( 'max_execution_time' ) - 2) )
        {
            $this->settings[ 'timeoutTime' ] = ini_get( 'max_execution_time' ) - 2;
        }

        $states = array();

        $currentServer = preg_replace( '/^(www\.)/i', '', $_SERVER[ "SERVER_NAME" ] );

        foreach ( $links as $idx => $_link )
        {
            foreach ( $_link[ 'attributes' ] as $i => $attr )
            {
                $link = $attr[ 'href' ];

                if ( !$link )
                {
                    continue;
                }

                if ( !$checkextern && preg_match( '/^(ftp|callto|tel|mailto|file|javascript|about):/i', $link ) )
                {
                    continue;
                }

                $fixedUrl = preg_replace( '/^https?:\/\/(www\.)/i', '', $link );
                $_fixedUrl = explode( '/', $fixedUrl );


                $hasWWW = false;
                if ( preg_match( '/^https?:\/\/(www\.)/i', $link ) )
                {
                    $hasWWW = true;
                }


                if ( isset( $_fixedUrl[ 0 ] ) && preg_match( '/^https?:\/\/(www\.)/i', $link ) && $_fixedUrl[ 0 ] != $currentServer && !$checkextern )
                {
                    continue;
                }

                // add the serverurl
                if ( stripos( $link, 'http' ) === false && stripos( $link, 'https' ) === false )
                {
                    $link = Settings::get( 'portalurl' ) . $link;
                }

                $state = $this->checkUrl( $link );
                $message = '';
                if ( $state !== true )
                {
                    $message = $state;
                    $state = false;
                }


                $states[] = array(
                    'link'         => $attr[ 'full_tag' ],
                    'url'          => $link,
                    'isok'         => $state,
                    'errormessage' => $message );
            }
        }

        return $states;
    }

    /**
     * Check a single link
     *
     * returns array (
     *                  'errormessage' => 'Request Entity Too Large', << Example
     *                  'isok' => true (if is ok)
     *                            false (if is not ok)
     *          )
     *
     * @param string $link
     * @param bool   $checkextern
     * @return array
     */
    public function checkSingeLink( $link, $checkextern = false )
    {

        if ( $this->settings[ 'timeoutTime' ] > (ini_get( 'max_execution_time' ) - 2) )
        {
            $this->settings[ 'timeoutTime' ] = ini_get( 'max_execution_time' ) - 2;
        }

        if ( !$link || !$this->isValidUrl( $link, $checkextern ) )
        {
            return false;
        }

        $hasWWW = false;
        if ( preg_match( '/^https?:\/\/(www\.)/i', $link ) )
        {
            $hasWWW = true;
        }

        // add the serverurl
        if ( stripos( $link, 'http' ) === false && stripos( $link, 'https' ) === false )
        {
            $link = Settings::get( 'portalurl' ) . $link;
        }

        $state = $this->checkUrl( $link );

        $message = '';
        if ( is_string( $state ) )
        {
            $message = $state;
            $state = false;
        }

        return array(
            'isok'         => $state,
            'errormessage' => $message );
    }

    /**
     * @param $url
     * @return string
     */
    function fixUrl( $url )
    {

        // add the serverurl
        if ( !preg_match( '/^https?:/is', $url ) )
        {
            if ( substr( $url, 0, 1 ) == '/' )
            {
                $url = substr( $url, 1 );
            }
            if ( substr( $link, 0, 2 ) == './' )
            {
                $url = substr( $url, 2 );
            }
            $url = Settings::get( 'portalurl', '' ) . '/' . $url;
        }

        return $url;
    }

    /**
     * @param      $url
     * @param bool $checkextern
     * @return bool
     */
    function isValidUrl( $url, $checkextern = false )
    {
        if ( !$url )
        {
            return false;
        }

        if ( !$checkextern && (preg_match( '/^(ftp|callto|tel|mailto|file|javascript|about):/is', $url ) || preg_match( '/^#/is', $url ) ) )
        {
            return false;
        }

        $pageurl = Settings::get( 'portalurl', '' );
        $currentServer = preg_replace( '/^(www\.)/is', '', $pageurl );

        $fixedUrl = preg_replace( '/^https?:\/\/(www\.)/is', '', $url );
        $_fixedUrl = explode( '/', $fixedUrl );

        if ( isset( $_fixedUrl[ 0 ] ) && preg_match( '/^https?:\/\/(www\.)/is', $url ) && $_fixedUrl[ 0 ] != $currentServer && !$checkextern )
        {
            return false;
        }

        return true;
    }

    /**
     * Check a url
     *
     * @param string $url
     * @return bool Returns true if found or error message if link is broken
     */
    public function checkUrl( $url )
    {
        $request = new Request();
        $request->__set( 'useragent', 'DreamCMS/' . VERSION . ' (Linkchecker)' );
        $request->__set( 'timeout', $this->settings[ 'timeoutTime' ] );

        $request->send( $url, NULL, 'GET' );
      //  $code = $request->__get( 'code' );
        $error = $request->__get( 'error' );

        return ($error != '' ? $error : true); // 200 header is ok
    }

    /**
     * Extract all links from string
     *
     * @param string $string  HTML Code
     * @param bool $checkextern
     * @return array Arry with all found links
     */
    public function getLinks( $string, $checkextern = false )
    {
        $pageurl = Settings::get( 'portalurl', '' );
        $links = Html::extractTags( $string, 'a', null );
        $currentServer = preg_replace( '/^(www\.)/i', '', $pageurl );


        if ( !is_array( $links ) )
        {
            return false;
        }

        foreach ( $links as $idx => $_link )
        {

            $attrib = $links[ $idx ][ 'attributes' ];


	        if ( isset($attrib['href']) && !empty($attrib['href']) ) {
		        $link = $attrib['href'];
		        if (preg_match( '/^(ftp|callto|tel|mailto|file|javascript|about):/is', $link ) || preg_match( '/^#/is', $link ) ) {
			        unset( $links[ $idx ] );
			        continue;
		        }


		        $fixedUrl = preg_replace( '/^https?:\/\/(www\.)?\//is', '', $link );
		        $_fixedUrl = explode( '/', $fixedUrl );

		        // Skip external link?
		        if ( preg_match( '/^https?:\/\/(www\.)?/is', $link ) && $_fixedUrl[ 0 ] != $currentServer && !$checkextern )
		        {
			        unset( $links[ $idx ] );
			        continue;
		        }

		        $nlink = $link;

		        // add the serverurl
		        if ( !preg_match( '/^https?:/i', $link ) )
		        {
			        if ( substr( $link, 0, 1 ) == '/' )
			        {
				        $link = substr( $link, 1 );
			        }
			        if ( substr( $link, 0, 2 ) == './' )
			        {
				        $link = substr( $link, 2 );
			        }
			        $nlink = $pageurl . '/' . $link;
		        }

		        $attrib[ 'href' ] = $nlink;
	        }

	        $links[ $idx ] = $attrib;
			/*

            foreach ( $attrib as $key => $value )
            {
                if ( $key != 'href' )
                {
                    continue;
                }
                $link = $value;

                if ( !$link )
                {
                    unset( $links[ $idx ] );
                    continue 2;
                }

                // Skip protocols
                if ( preg_match( '/^(ftp|callto|tel|mailto|file|javascript|about):/is', $link ) )
                {
                    unset( $links[ $idx ] );
                    continue 2;
                }

                $fixedUrl = preg_replace( '/^https?:\/\/(www\.)?\//is', '', $link );
                $_fixedUrl = explode( '/', $fixedUrl );


                // Skip external link?
                if ( preg_match( '/^https?:\/\/(www\.)?/is', $link ) && $_fixedUrl[ 0 ] != $currentServer && !$checkextern )
                {
                    unset( $links[ $idx ] );
                    continue 2;
                }

                $nlink = $link;

                // add the serverurl
                if ( !preg_match( '/^https?:/i', $link ) )
                {
                    if ( substr( $link, 0, 1 ) == '/' )
                    {
                        $link = substr( $link, 1 );
                    }
                    if ( substr( $link, 0, 2 ) == './' )
                    {
                        $link = substr( $link, 2 );
                    }
                    $nlink = $pageurl . '/' . $link;
                }

                $_link[ 'attributes' ][ 'href' ] = $nlink;
            }


            $links[ $idx ] = $_link;
	        */
        }

        $this->extractedLinks = $links;

        return $links;
    }

}
