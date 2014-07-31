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
 * @file        Api.php
 *
 */
class Api extends Loader
{

    /**
     * @var null
     */
    static $_env = null;

    /**
     * @param string $name
     * @return mixed
     */
    public static function triggerEvent($name) {

        /**
         * $event Event
         */
        $event = Registry::getObject( 'Event' );
        if (!($event instanceof Event)) {
            $event = new Event();
            Registry::setObject('Event', $event);
        }

        $event->trigger($name);
    }

    /**
     *
     * @param string $name
     * @param mixed $default
     * @throws BaseException
     * @internal param int $contentid
     * @return mixed
     */
    public static function getCustomField( $name = null, $default = null )
    {
        if ( $name === null )
        {
            throw new BaseException( 'Invalid custom field name!' );
        }

        return CustomField::get( $name, $default );
    }

    /**
     * 
     */
    public static function buildSearchIndex()
    {
        $Indexer = Indexer::getInstance();
        $Indexer->isCronjob = true;
        $Indexer->initIndexer();
        $Indexer->buildIndexes();
    }

    /**
     * @return bool
     */
    static function getShortUrl()
    {
        if ( self::$_env === null )
        {
            $env = self::$_env = new Env();
        }
        else
        {
            $env = self::$_env;
        }
        $url = Settings::get( 'portalurl' ) . $env->requestUri();

        if ( Registry::objectExists( 'Controller' ) && Registry::objectExists( 'Document' ) )
        {
            $documentID = Registry::getObject( 'Document' )->getDocumentID();
            $modulID = Registry::getObject( 'Controller' )->getModulID();

            return Registry::getObject( 'Controller' )->getShorturls( $url, $documentID, $modulID );
        }

        return false;
    }

    /**
     *
     * @param bool $caching
     * @return object
     */
    static function getFacebookShareCounter( $caching = false )
    {
        if ( self::$_env === null )
        {
            $env = self::$_env = new Env();
        }
        else
        {
            $env = self::$_env;
        }

        $requestUri = $env->requestUri();
        if ( substr( $requestUri, 0, 1 ) !== '/' )
        {
            $requestUri = '/' . $requestUri;
        }


        $url = Settings::get( 'portalurl' ) . $requestUri;
        $hash = md5( $url );
        if ( $caching )
        {
            $cache = Cache::get( 'url-' . $hash . '-fbshare', 'data/sharecounts', ($caching ? Settings::get( 'sharcounterupdate', null ) : null ) );
            if ( $cache !== null )
            {
                Debug::store( 'getFacebookShareCounter', 'using Cache' );

                return $cache;
            }
        }

        $xmlCode = Library::getRemoteFile( 'http://api.facebook.com/restserver.php?method=links.getStats&urls=' . urlencode( $url ) . '&t=' . TIMESTAMP );
        // Parse the XML results
        $xml = simplexml_load_string( $xmlCode );
        // Format output
        $shareCount = (string) $xml->link_stat[ 0 ]->share_count;
        if ( $caching )
        {
            Cache::write( 'url-' . $hash . '-fbshare', (int)$shareCount  ? "$shareCount" : "0", 'data/sharecounts' );
        }

        return (int)$shareCount  ? $shareCount : 0;
    }

    /**
     *
     * @return object
     */
    static function getFacebookLikeCounter()
    {
        if ( self::$_env === null )
        {
            $env = self::$_env = new Env();
        }
        else
        {
            $env = self::$_env;
        }


        $xmlCode = Library::getRemoteFile( 'http://api.facebook.com/restserver.php?method=links.getStats&urls=' . rawurlencode( Settings::get( 'portalurl' ) . $env->requestUri() ) . '&t=' . TIMESTAMP );

        // Parse the XML results
        $xml = simplexml_load_string( $xmlCode );

        // Format output
        $shareCount = (string) $xml->link_stat[ 0 ]->like_count;


        return (int)$shareCount  ? $shareCount : 0;
    }

    /**
     *
     * @param bool $caching
     * @return object
     */
    static function getTwitterShareCounter( $caching = false )
    {
        if ( self::$_env === null )
        {
            $env = self::$_env = new Env();
        }
        else
        {
            $env = self::$_env;
        }


        $url = Settings::get( 'portalurl' ) . $env->requestUri();
        $hash = md5( $url );
        if ( $caching )
        {
            $cache = Cache::get( 'url-' . $hash . '-twittershare', 'data/sharecounts', ($caching ? Settings::get( 'sharcounterupdate', null ) : null ) );
            if ( $cache !== null )
            {
                Debug::store( 'getTwitterShareCounter', 'using Cache' );

                return $cache;
            }
        }

        $xmlCode = Library::getRemoteFile( 'http://urls.api.twitter.com/1/urls/count.json?url=' . rawurlencode( $url ) . '&t=' . TIMESTAMP );

        $count = 0;
        if ( !empty( $xmlCode ) )
        {
            $resp = json_decode( $xmlCode, true );
            if ( isset( $resp[ 'count' ] ) )
            {
                $count = $resp[ 'count' ];
            }
        }

        if ( $caching )
        {
            Cache::write( 'url-' . $hash . '-twittershare', (int)$count ? '' . $count : '0', 'data/sharecounts' );
        }

        return (int)$count  ? $count : 0;
    }

    /**
     * @param bool $caching
     * @return mixed|string
     */
    public static function getGoogleShareCounter( $caching = false )
    {
        if ( self::$_env === null )
        {
            $env = self::$_env = new Env();
        }
        else
        {
            $env = self::$_env;
        }


        $url = Settings::get( 'portalurl' ) . $env->requestUri();
        $hash = md5( $url );
        if ( $caching )
        {
            $cache = Cache::get( 'url-' . $hash . '-googleshare', 'data/sharecounts', ($caching ? Settings::get( 'sharcounterupdate', null ) : null ) );
            if ( $cache !== null )
            {
                Debug::store( 'getGoogleShareCounter', 'using Cache' );

                return $cache;
            }
        }

        $xmlCode = Library::getRemoteFile( 'https://plusone.google.com/u/0/_/+1/fastbutton?url=' . urlencode( Settings::get( 'portalurl' ) . $env->requestUri() ) . '&count=true' );

        if ( $xmlCode )
        {

            $dom = new DOMDocument;
            $dom->preserveWhiteSpace = false;
            $dom->loadHTML( $xmlCode );
            $domxpath = new DOMXPath( $dom );

            $filtered = $domxpath->query( "//div[@id='aggregateCount']" );


            if ( $caching )
            {
                Cache::write( 'url-' . $hash . '-googleshare', $filtered->item( 0 )->nodeValue ? '' . $filtered->item( 0 )->nodeValue : '0', 'data/sharecounts' );
            }

            unset( $dom, $domxpath );

            return $filtered->item( 0 )->nodeValue;
        }
        else
        {
            return 0;
        }


        $curl = curl_init();
        curl_setopt( $curl, CURLOPT_URL, "https://clients6.google.com/rpc?key=" . Settings::get( 'googleapikey', '' ) );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $curl, CURLOPT_HTTPHEADER, array(
            'Content-type: application/json',
            'User-Agent: Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_7; en-us) AppleWebKit/533.21.1 (KHTML, like Gecko) Version/5.0.5 Safari/533.21.1' ) );

        curl_setopt( $curl, CURLOPT_POST, 1 );
        curl_setopt( $curl, CURLOPT_POSTFIELDS, '[{"method":"pos.plusones.get","id":"p","params":{"nolog":true,"id":"' . $url . '","source":"widget","userId":"@viewer","groupId":"@self"},"jsonrpc":"2.0","key":"p","apiVersion":"v1"}]' );
        $curl_results = curl_exec( $curl );
        curl_close( $curl );

        $json = json_decode( $curl_results, true );

        if ( $caching )
        {
            Cache::write( 'url-' . $hash . '-googleshare', (int)$json[ 0 ][ 'result' ][ 'metadata' ][ 'globalCounts' ][ 'count' ]  ? '' . $json[ 0 ][ 'result' ][ 'metadata' ][ 'globalCounts' ][ 'count' ] : '0', 'data/sharecounts' );
        }

        return '' . (int)$json[ 0 ][ 'result' ][ 'metadata' ][ 'globalCounts' ][ 'count' ]  ? '' . $json[ 0 ][ 'result' ][ 'metadata' ][ 'globalCounts' ][ 'count' ] : '0';


        $doc = new DOMDocument();
        $doc->loadHTML( $html );
        $counter = $doc->getElementById( 'aggregateCount' );

        return '' . (int)$counter->nodeValue ;


        $curl = curl_init();
        curl_setopt( $curl, CURLOPT_URL, "https://clients6.google.com/rpc?key=" . Settings::get( 'googleapikey', '' ) );
        curl_setopt( $curl, CURLOPT_POST, 1 );
        curl_setopt( $curl, CURLOPT_POSTFIELDS, '[{"method":"pos.plusones.get","id":"p","params":{"nolog":true,"id":"' . Settings::get( 'portalurl' ) . $env->requestUri() . '","source":"widget","userId":"@viewer","groupId":"@self"},"jsonrpc":"2.0","key":"p","apiVersion":"v1"}]' );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $curl, CURLOPT_HTTPHEADER, array(
            'Content-type: application/json' ) );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
        $curl_results = curl_exec( $curl );
        curl_close( $curl );
        die( $curl_results . ' ' . "https://clients6.google.com/rpc?key=" . Settings::get( 'googleapikey', '' ) );


        $json = json_decode( $curl_results, true );

        return '' . (int)$json[ 0 ][ 'result' ][ 'metadata' ][ 'globalCounts' ][ 'count' ] ;
    }

    /**
     * @param string $mode (facebook|twitter|plusone)
     * @param string $url Description
     * @return bool|string
     */
    public static function getShareUrl( $mode = 'facebook', $url = null )
    {
        $str = false;

        if ( (self::$_controllerInstance instanceof Controller ) )
        {
            $data = self::$_controllerInstance->getSocialNetworkData();
        }
        else
        {
            return false;
        }


        if ( $url === null )
        {
            $ur = Api::getShortUrl();

            if ( is_array( $ur ) && count( $ur ) )
            {
                shuffle( $ur );
                array_values( $ur );
                $url = $ur[ 0 ];
            }
            else
            {
                //   $url = Api::currentLocation();
            }

            if ( self::$_env === null )
            {
                $env = self::$_env = new Env();
            }
            else
            {
                $env = self::$_env;
            }

            $u = $env->requestUri();

            $url = Settings::get( 'portalurl' ) . (substr( $u, 0, 1 ) != '/' ? '/' : '') . $u;
        }


        if ( $mode === 'facebook' )
        {
            $str .= 'http://www.facebook.com/sharer.php?s=100';
            $str .= '&amp;u=' . $url;
        }
        elseif ( $mode == 'twitter' )
        {
            $str .= 'https://twitter.com/intent/tweet?source=webclient&amp;text=' . rawurlencode( Strings::unhtmlSpecialchars( Strings::fixLatin( $data[ 'title' ] ), true ) );

            $str .= '%20' . urlencode( $url );

            if ( Settings::get( 'twittername', null ) )
            {
                $str .= '&amp;related=' . Settings::get( 'twittername', null );
                $str .= '&amp;via=' . Settings::get( 'twittername', null );
            }
        }
        elseif ( $mode == 'plusone' )
        {
            $str .= 'https://plus.google.com/share?key=' . Settings::get( 'googleapikey', '' ) . '&amp;url=' . urlencode( $url );
            $str .= '&amp;title=' . rawurlencode( Strings::fixLatin( $data[ 'title' ] ) );
        }


        return $str;
    }

    /**
     *
     * @return string
     */
    static function getContentText()
    {
        if ( (self::$_controllerInstance instanceof Controller ) )
        {
            $data = self::$_controllerInstance->getSocialNetworkData();

            if ( !isset( $data[ 'title' ] ) )
            {
                return '';
            }

            return Strings::fixLatin( $data[ 'title' ] );
        }

        return '';
    }

    /**
     *
     * @return string
     */
    static function currentLocation()
    {
        if ( self::$_env === null )
        {
            $env = self::$_env = new Env();
        }
        else
        {
            $env = self::$_env;
        }

        return Settings::get( 'portalurl' ) . $env->requestUri();
    }

    /**
     *
     * @param string $url
     * @param string $mode
     * @return string
     */
    static function feedLink( $url, $mode = null )
    {
        $ret = '';

        if ( $mode === null )
        {
            $mode = 'rss';
        }

        $mode = str_replace( '.', '', strtolower( $mode ) );

        if ( strpos( $url, Settings::get( 'portalurl' ) ) === false )
        {
            $ret .= Settings::get( 'portalurl' ) . (substr( $url, 0, 1 ) !== '/' ? '/' : '');
            $filename = Library::getFilename( $url );

            if ( $filename !== '' )
            {
                $names = explode( '.', $filename );
                $filename = $names[ 0 ] . '.' . $mode;
            }
            else
            {
                $filename = (defined( 'DOCUMENT_NAME' ) && DOCUMENT_NAME !== '' ? DOCUMENT_NAME : md5( REQUEST )) . '.' . $mode;
            }

            $segments = explode( '/', $url );
            if ( preg_match( '/\.([a-z0-9]+?)$/i', $segments[ count( $segments ) - 1 ] ) )
            {
                array_pop( $segments );
            }

            if ( $segments[ 0 ] === '' )
            {
                array_shift( $segments );
            }

            $ret .= implode( '/', $segments );
            $ret .= '/' . $filename;
        }
        else
        {
            $filename = Library::getFilename( $url );
            if ( $filename !== '' )
            {
                $names = explode( '.', $filename );
                $filename = $names[ 0 ] . '.' . $mode;
            }
            else
            {
                $filename = (defined( 'DOCUMENT_NAME' ) && DOCUMENT_NAME !== '' ? DOCUMENT_NAME : md5( REQUEST )) . '.' . $mode;
            }

            $segments = explode( '/', $url );
            if ( preg_match( '/\.([a-z0-9]+?)/i', $segments[ count( $segments ) - 1 ] ) )
            {
                array_pop( $segments );
            }

            $ret .= implode( '/', $segments );
            $ret .= '/' . $filename;
        }

        return $ret;
    }

    /**
     *
     * @param type    $url
     * @param bool    $cacheable
     * @param integer $cachetime
     * @return array
     */
    static function fetchRssFeed( $url, $cacheable = null, $cachetime = null )
    {
        $cachetime = ($cachetime === null || $cachetime < 1) ? 3600 : $cachetime;
        $cache = null;

        if ( $cacheable )
        {
            $cache = Cache::get( md5( $url ), 'data/rssfech' );
        }

        if ( $cacheable && $cache !== null )
        {
            if ( $cache[ 'lastfetchtime' ] < (time() - $cachetime) )
            {
                $cache = null;
            }
        }

        if ( $cache === null )
        {
            $xml_str = Library::getRemoteFile( $url );
            Cache::write( md5( $url ), array(
                'lastfetchtime' => time(),
                'xml_str'       => $xml_str ), 'data/rssfech' );
        }
        else
        {
            $xml_str = $cache[ 'xml_str' ];
        }


        libxml_use_internal_errors( true );
        $xml = new SimplexmlElement( $xml_str );

        if ( !$xml )
        {
            Cache::delete( md5( $url ), 'data/rssfech' );

            return null;
        }

        $data = array();
        $data[ 'rss_title' ] = (string) $xml->channel[ 0 ]->title;
        $data[ 'rss_url' ] = (string) $xml->channel[ 0 ]->link;
        $data[ 'rss_copyright' ] = (string) $xml->channel[ 0 ]->copyright;
        $data[ 'rss_fetched' ] = $cache[ 'lastfetchtime' ];

        if ( isset( $xml->channel[ 0 ]->image->url ) )
        {
            $data[ 'rss_icon' ] = (string) $xml->channel[ 0 ]->image->url;
        }

        $limit = count( $xml->channel[ 0 ]->item );

        $data[ 'rss_items' ] = array();

        if ( !$limit )
        {
            return $data;
        }

        for ( $i = 0; $i < $limit; $i++ )
        {
            if ( isset( $xml->channel[ 0 ]->item[ $i ] ) )
            {
                $item = $xml->channel[ 0 ]->item[ $i ];
                $data[ 'rss_items' ][ $i ][ 'date' ] = (string) $item->date;
                $data[ 'rss_items' ][ $i ][ 'title' ] = (string) $item->title;
                $data[ 'rss_items' ][ $i ][ 'link' ] = (string) $item->link;
                $data[ 'rss_items' ][ $i ][ 'description' ] = (string) $item->description;

                if ( isset( $item->enclosure ) )
                {
                    $data[ 'rss_items' ][ $i ][ 'image_url' ] = (string) $item->enclosure[ 0 ][ 'url' ];
                }
            }
        }

        return $data;
    }

    /**
     *
     * @param string $modul
     * @param string $call
     * @param array  $attributes
     */

    /**
     * Will call a Method in the $modul (only in the Model class of the Modul!)
     *
     * @param string $modul
     * @param string $call
     * @param array  $attributes
     * @return mixed
     * @throws BaseException
     */
    static function callModul( $modul, $call, $attributes = array() )
    {
        $application = Registry::getObject( 'Application' );


        if ( !($application instanceof Application) )
        {
            throw new BaseException( 'Invalid Application instance.' );
        }

        if ( preg_match( '/(delete|remove|clone|kill|update|save|rollback|copy|publish|Grid|getData|__construct)/is', $call ) )
        {
            throw new BaseException( 'Invalid Application Call.' );
        }

        if ( !preg_match( '/^get/', $call ) )
        {
            throw new BaseException( 'Invalid Application Call.' );
        }


        $modreg = $application->getModulRegistry( $modul );

        if ( is_array( $modreg ) )
        {
            $modul = ucfirst( $modul );
            $model = Model::getModelInstance( $modul );


            $model->providerModulCall = $call;

            if ( !method_exists( $model, $call ) )
            {
                throw new BaseException( sprintf( 'The modul function `%s` not exists!', $call ) );
            }

            $model->setModulParams( $attributes );

            return call_user_func_array( array(
                $model,
                $call ), $attributes );
        }

        return;
    }

    /**
     * @param        $file
     * @param null   $chain
     * @param string $cache
     * @return bool|null
     */
    public static function loadImg( $file, $chain = null, $cache = 'thumbnails' )
    {
        if ( $chain == '' || $chain === null )
        {
            $imgchain = Library::getImageChain( 'thumbnail' );
        }
        else
        {
            $imgchain = Library::getImageChain( $chain );
        }

        if ( substr( $cache, 0, 1 ) == "'" || substr( $cache, 0, 1 ) == '"' )
        {
            $cache = substr( $cache, 1 );
        }
        if ( substr( $cache, -1 ) == "'" || substr( $cache, -1 ) == '"' )
        {
            $cache = substr( $cache, 0, -1 );
        }

        if ( !trim( $cache ) )
        {
            $cache = 'thumbnails';
        }
        $len = strlen( ROOT_PATH );
        if ( substr( str_replace( '\\', '/', $file ), 0, $len ) != ROOT_PATH )
        {
            $file = ROOT_PATH . $file;
        }


        if ( is_file( $file ) )
        {
            $pathtoimg = $file;

            $im = array();
            $im[ 'cachefile' ] = null;
            $valid = false;
            if ( Library::canGraphic( $file ) )
            {
                $im = @getimagesize( $file );

                if ( Library::isValidGraphic( $file ) )
                {
                    $data = array(
                        'source' => $file,
                        'output' => 'png',
                        'chain'  => $imgchain );

                    $img = ImageTools::create( PAGE_CACHE_PATH . $cache );

                    $data = $img->process( $data );
                    $im[ 'cachefile' ] = $data[ 'path' ];
                    $valid = true;
                }
            }

            if ( !$valid )
            {
                return false;
            }

            return $im[ 'cachefile' ];
        }
        else
        {
            return false;
        }
    }


    public static function updateSpamprotection() {
        $application = Registry::getObject( 'Application' );
        if ( !($application instanceof Application) )
        {
            throw new BaseException( 'Invalid Application instance.' );
        }






    }

}
