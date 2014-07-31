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
 * @file         Provider.php
 */
class Provider extends Provider_Abstract
{
    protected static $_instance = null;

    /**
     * @var null|string
     */
    private $leftInTag = null;

    /**
     * @var null|string
     */
    private $rightInTag = null;

    /**
     * @var null|string
     */
    private $leftTag = null;

    /**
     * @var null|string
     */
    private $rightTag = null;

    /**
     * @var null
     */
    private $params = null;

    /**
     * @var string
     */
    private $parseMode = 'pre';

    /**
     * @var null
     */
    private $_providerOutput = null;

    /**
     * @var null
     */
    private $contentProviders = null;

    /**
     * @var bool
     */
    private $providerLoaded = false;

    /**
     * @var array
     */
    private $skipProviderOutput = array();

    /**
     * @var array
     */
    private $providerInited = array();

    /**
     * @var array
     */
    private $coretagInited = array();

    /**
     * @var array
     */
    private $_cachedProviderOutput = array();

    /**
     * @var null
     */
    protected $_tags = null;

    /**
     *
     */
    public function __construct($fromTemplateTag = false)
    {
        if (!$fromTemplateTag)
        {
            parent::__construct();

            // core tags
            $this->leftInTag  = preg_quote( Settings::get( 'left_intag', '{' ), "#" );
            $this->rightInTag = preg_quote( Settings::get( 'right_intag', '}' ), "#" );

            // providers
            $this->leftTag  = preg_quote( Settings::get( 'left_tag', '[' ), "#" );
            $this->rightTag = preg_quote( Settings::get( 'right_tag', ']' ), "#" );

            // register providers and coretags in the autoloader
            $this->getApplication()->getAutoloader()->addLibrary( 'Provider', PROVIDER_PATH );
            $this->getApplication()->getAutoloader()->addLibrary( 'Tag', CORETAGS_PATH );
        }

        $this->scanAvailableProviders();
        $this->loadContentProviders();
    }


    /**
     * @param $name
     * @param array $params
     * @return string
     */
    public static function process($name, &$params = array())
    {
        if ( self::$_instance === null )
        {
            self::$_instance = new Provider(true);
        }

        return self::$_instance->renderProvider( $name, $params );
    }


    /**
     * @param $name
     */
    public function addProviderSkip($name)
    {

        $this->skipProviderOutput[ $name ] = $name;
    }

    /**
     * @param $name
     */
    public function removeProviderSkip($name)
    {

        unset( $this->skipProviderOutput[ $name ] );
    }

    /**
     *
     * @param string $code
     * @return bool
     */
    public function hasProviders(&$code)
    {

        //        $providerKeys = array_keys( $this->contentProviders );

        if ( ( stripos( $code, stripslashes( $this->leftTag ) ) !== false || stripos( $code, stripslashes( $this->leftInTag ) ) !== false ) /*&& preg_match( '#' . implode( '|', $providerKeys ) . '#isU', $code )*/ )
        {
            return true;
        }

        return false;


        $tags = array();
        preg_match( '#((' . $this->leftTag . '|' . $this->leftInTag . ')(' . implode( '[\:\s]|', $providerKeys ) . '[\:\s])([^' . $this->rightTag . $this->rightInTag . ']*)(' . $this->rightTag . '|' . $this->rightInTag . '))#is', $code, $tags );

        if ( is_array( $tags[ 0 ] ) && count( $tags[ 0 ] ) )
        {
            return true;
        }

        return false;
    }

    /**
     *
     * @param string $output
     * @param string $mode (pre, post)
     *
     * @return string
     */
    public function renderProviderTags($output, $mode = 'post')
    {

        if ( !$this->hasProviders( $output ) )
        {
            return $output;
        }

        self::getParams();

        $this->params         = self::$extra;
        $this->providerOutput = $output;
        $this->parseMode      = $mode;
        $this->parse();

        return $this->providerOutput;
    }

    /**
     *
     */
    private function scanAvailableProviders()
    {

        if ( is_array( self::$_availableProviders ) )
        {
            return;
        }

        self::$_availableProviders = array();

        $coretags = File::getSubDir( CORETAGS_PATH );
        foreach ( $coretags as $x => $tag )
        {
            self::$_availableProviders[ 'Tag' ][ $tag ] = true;
        }
        $coretags = null;

        $providers = File::getSubDir( PROVIDER_PATH );
        foreach ( $providers as $x => $provider )
        {
            self::$_availableProviders[ 'Provider' ][ $provider ] = true;
        }
        $providers = null;
    }

    private function loadContentProviders()
    {

        if ( $this->providerLoaded )
        {
            return;
        }

        $this->contentProviders = Cache::get( 'content_providers' );

        if ( is_null( $this->contentProviders ) )
        {
            $this->contentProviders = array();

            $providers = $this->db->query( 'SELECT * FROM %tp%provider ORDER BY execution_order ASC' )->fetchAll();
            foreach ( $providers as $provider )
            {
                $this->contentProviders[ $provider[ 'name' ] ] = $provider;
            }
            Cache::write( 'content_providers', $this->contentProviders );
        }

        $this->providerLoaded = true;
    }

    /**
     *
     * @param type $name
     * @param bool $isProvider
     * @throws BaseException
     * @internal param bool|\type $isCoreTag is a inserttag
     *
     * @return string
     */
    private function getContentProvider($name, $isProvider = true)
    {

        $name = strtolower( $name );

        $_startTime = Debug::getMicroTime();

        if ( !$isProvider )
        {

            $class = 'Tag_' . ucfirst( $name );

            if ( !isset( $this->coretagInited[ $name ] ) && !class_exists( $class, false ) )
            {
                $path = CORETAGS_PATH . ucfirst( $name ) . '.php';
                try
                {
                    include_once $path;

                    //Debug::store('Load Provider', 'Tag Provider: ' . $class, $_startTime);
                }
                catch ( Exception $e )
                {
                    throw new BaseException( 'Class file for CoreTag `' . $name . '` does not exist.' );
                }

                // call_user_func_array(array($class, 'init'), array($this));
            }


            if ( !class_exists( $class ) )
            {
                throw new BaseException( 'Class ' . $class . ' not found!' );
            }


            $this->coretagInited[ 'tag_' . $name ] = new $class;

            //Debug::store('Load Coretag', ' Coretag: ' . ucfirst($name), $_startTime);

            return $this->coretagInited[ 'tag_' . $name ];
        }
        else
        {
            $class = 'Provider_' . ucfirst( $name );
            if ( !class_exists( $class ) )
            {
                throw new BaseException( 'Class ' . $class . ' not found!' );
            }

            $this->providerInited[ 'provider_' . $name ] = new $class;

            //Debug::store('Load Provider', ' Provider: ' . ucfirst($name), $_startTime);

            return $this->providerInited[ 'provider_' . $name ];
        }
    }

    /**
     *
     * @param string $str
     * @param array $providers all proviters to mask
     * @param string $mask
     * @return string
     */
    public function maskProviders($str, $providers = array(), $mask = '')
    {

        return preg_replace( '#((' . $this->leftTag . '|' . $this->leftInTag . ')(' . implode( '|', $providers ) . ')([^' . $this->rightTag . $this->rightInTag . ']*)(' . $this->rightTag . '|' . $this->rightInTag . '))#isSU', $mask, $str );
    }

    /**
     * free the memory
     */
    public function freeMem()
    {

        $this->_providerOutput = null;
        self::$providerdata    = null;
    }

    /**
     *
     * @return void
     */
    private static function getParams()
    {

        #$router = Library::getRouter();
        // parse the request step by step

        if ( !defined( 'REQUEST' ) || is_array( self::$extra ) )
        {
            return;
        }
        $request     = explode( '/', REQUEST );
        $extra       = array();
        self::$extra = HTTP::input();

        $count = count( $request );

        while ( $count > 0 )
        {
            $extra[ ] = array_pop( $request );
            $url      = implode( '/', $request );

            // check for direct URL mapping
            if ( $url === REQUEST )
            {
                self::$extra = Library::unempty( array_reverse( $extra ) );

                return;
            }

            // check for direct URL with trailing slash mapping
            if ( $url . '/' === REQUEST )
            {
                self::$extra = Library::unempty( array_reverse( $extra ) );

                return;
            }

            $count = count( $request );
        }

        self::$extra = Library::unempty( array_reverse( $extra ) );
    }

    private function parse()
    {

        /**
         * remove all provider if not runable
         */
        foreach ( $this->contentProviders as $provider => $arr )
        {
            if ( !$arr[ 'runnable' ] )
            {
                unset( $this->contentProviders[ $provider ] );
            }
        }

        $passes = Settings::get( $this->parseMode . '_cache_passes', 1 );

        $providerKeys = array_keys( $this->contentProviders );

        $coreTags     = array();
        $providerTags = array();

        foreach ( $this->contentProviders as $r )
        {
            if ( $r[ 'iscoretag' ] )
            {
                $coreTags[ ] = $r[ 'name' ];
            }
            else
            {
                $providerTags[ ] = $r[ 'name' ];
            }
        }

        #$timer = Debug::getMicroTime();

        for ( $i = 0; $i < $passes; ++$i )
        {
            $this->parsePass = $i;

            if ( DEBUG )
            {
                //Debug::store('Provider Process', 'Mode ' . $this->parseMode . ' Step ' . ( $i + 1 ) . ' of ' . $passes . ' Steps');
            }
            #preg_match_all( '#(?P<tag>(?P<sdelim>' . $this->leftTag . ')(?P<name>' . implode('|', $providerTags) . ')(?P<inner>\s{1,}[^' . $this->rightTag. $this->rightInTag . ']*)(?P<edelim>' . $this->rightTag  . '))#si', $this->providerOutput, $tags, PREG_SET_ORDER );


            preg_match_all( '#(?P<tag>(?P<sdelim>' . $this->leftTag . ')(?P<name>[a-z0-9_]+)\s(?P<inner>\s*[^' . $this->rightTag . $this->rightInTag . ']*)(?P<edelim>' . $this->rightTag . '))#si', $this->providerOutput, $tags, PREG_SET_ORDER );

            /*

                        $split = explode( Settings::get( 'left_tag', '[' ), $this->providerOutput );
                        $cache = false;

                        if ( isset( $split[ 1 ] ) )
                        {
                            $pt = array_map( 'strtolower', $providerTags );
                            foreach ( $split as $_split )
                            {

                                $insplit = explode( Settings::get( 'right_tag', ']' ), $_split );
                                $code    = $insplit[ 0 ];

                                $cs = explode( ' ', $code );
                                if ( $cs[ 0 ] && isset( $insplit[ 1 ] ) && in_array( strtolower( $cs[ 0 ] ), $pt ) )
                                {
                                    $cache[ ] = array(
                                        'iscoretag' => true,
                                        'name'      => $cs[ 0 ],
                                        'inner'     => $insplit[ 0 ],
                                        'tag'       => Settings::get( 'left_tag', '[' ) . $insplit[ 0 ] . Settings::get( 'right_tag', ']' )
                                    );
                                }
                            }
                        }


                        // insert tags
                        $split = explode( Settings::get( 'left_intag', '{' ), $this->providerOutput );
                        if ( isset( $split[ 1 ] ) )
                        {
                            $pt = array_map( 'strtolower', $coreTags );
                            foreach ( $split as $_split )
                            {
                                $insplit = explode( Settings::get( 'right_intag', '}' ), $_split );
                                $code    = $insplit[ 0 ];

                                $cs = explode( ':', $code );
                                if ( $cs[ 0 ] && strpos( $cs[ 0 ], ' ' ) === false && isset( $insplit[ 1 ] ) && in_array( strtolower( $cs[ 0 ] ), $pt ) )
                                {
                                    $cache[ ] = array(
                                        'iscoretag' => false,
                                        'name'      => $cs[ 0 ],
                                        'inner'     => str_replace( $cs[ 0 ] . ':', '', $insplit[ 0 ] ),
                                        'tag'       => Settings::get( 'left_intag', '{' ) . $insplit[ 0 ] . Settings::get( 'right_intag', '}' )
                                    );
                                }
                            }
                        }
            */

            if ( $tags )
            {
                #echo Debug::getMicroTime()-$timer;
                # print_r($tags);exit;
                $this->_tags = $tags;
                $this->parseTags( null, true, $providerKeys );

                //exit;
            }

            #$this->_tags = null;
        }

        $this->_tags          = null;
        $this->coretagInited  = array();
        $this->providerInited = array();
    }

    /**
     *
     * @param array $providername
     * @param bool $isProvider
     * @param array $providerKeys
     * @throws BaseException
     * @internal param bool $isCoreTag
     * @return type
     */
    private function parseTags($providername = null, $isProvider = false, $providerKeys = array())
    {

        if ( $providername !== null && !isset( $this->contentProviders[ $providername ] ) )
        {
            return $this->providerOutput;
        }

        $_left          = Settings::get( 'left_tag', '[' );
        $_leftInsertTag = Settings::get( 'left_intag', '{' );

        //    $countedSkip = is_array( $this->skipProviderOutput ) ? count( $this->skipProviderOutput ) : 0;

        if ( is_array( $this->_tags ) && ( $tlen = count( $this->_tags ) ) )
        {
            foreach ( $this->_tags as $r )
            {
                if ( strtolower( $r[ 'name' ] ) == 'if' )
                {
                    continue;
                }

                $strTag = $r[ 'tag' ]; //$tags[ $_rit ][ 0 ];

                $strTagParms = $r[ 'inner' ]; //$tags[ $_rit ][ 2 ];


                $strTagHash   = md5( $strTag );
                $providername = $r[ 'name' ]; //preg_replace( '#:$#', '', trim( $tags[ $_rit ][ 1 ] ) );


                $isProvider = $r[ 'sdelim' ] === $_left; //$r[ 'iscoretag' ];


                // Skip empty tags
                if ( !trim( $strTagParms ) )
                {
                    // $this->providerOutput = str_replace( $strTag, '<!-- Provider: '. $providername .' has no attributes -->', $this->providerOutput );
                    continue;
                }

                if ( in_array( strtolower( $providername ), $this->skipProviderOutput ) )
                {
                    //     echo $providername." Skip \n";

                    Debug::store( 'Provider Process', 'Skip the content provider "' . $providername . '"' );
                    $this->providerOutput = str_ireplace( $strTag, '', $this->providerOutput );
                    continue;
                }

                $useCache = true;

                if ( $isProvider )
                {
                    // get attributes from Core Provider
                    $tag = $this->parseTag( $strTagParms, strtolower( $providername ) );
                    /*
                    if ( $providername == 'asset') {
                        echo $providername . ' '. $strTagParms."\n";
                        print_r($tag);
                        exit;
                    }
                    */

                    if ( isset( $tag[ 'parseonpass' ] ) && (int)$tag[ 'parseonpass' ] > $this->parsePass )
                    {
                        // echo $providername . " Skip \n";
                        continue;
                    }

                    if ( $this->parseMode === 'post' && ( ( isset( $tag[ 'cache' ] ) && ( $tag[ 'cache' ] == 'false' || $tag[ 'cache' ] == '0' || !$tag[ 'cache' ] ) ) || ( isset( $tag[ 'cacheable' ] ) && ( !$tag[ 'cacheable' ] || $tag[ 'cacheable' ] == 'false' || $tag[ 'cacheable' ] == '0' || !$tag[ 'cacheable' ] ) ) )
                    )
                    {
                        $useCache = false;
                    }
                }
                else
                {
                    // get attributes from Core-Tag Provider
                    $t   = $providername . ':' . $strTagParms;
                    $tag = $this->parseInTag( $t );
                }


                // Load value from cache array
                if ( isset( $this->_cachedProviderOutput[ $strTagHash ] ) )
                {
                    $this->providerOutput = str_ireplace( $strTag, $this->_cachedProviderOutput[ $strTagHash ], $this->providerOutput );
                    continue;
                }


                $providerCLS = null;

                if ( $isProvider )
                {
                    if ( !isset( $this->coretagInited[ 'tag_' . strtolower( $providername ) ] ) )
                    {
                        $providerCLS = $this->getContentProvider( strtolower( $providername ), true );
                    }
                    else
                    {
                        $providerCLS = $this->coretagInited[ 'tag_' . strtolower( $providername ) ];
                    }
                }
                else
                {
                    if ( !isset( $this->providerInited[ 'provider_' . strtolower( $providername ) ] ) )
                    {
                        $providerCLS = $this->getContentProvider( strtolower( $providername ), false );
                    }
                    else
                    {
                        $providerCLS = $this->providerInited[ 'provider_' . strtolower( $providername ) ];
                    }
                }

                try
                {
                    //   Debug::store( 'Provider Process', 'Start Provider "' . $providername . '" executing', $_startTime );
                    // ob_start();
                    $this->_cachedProviderOutput[ $strTagHash ] = $providerCLS->render( $tag );
                }
                catch ( Exception $e )
                {
                    throw new BaseException( $e->getMessage() );
                }

                $this->providerOutput = str_ireplace( $strTag, (string)$this->_cachedProviderOutput[ $strTagHash ], $this->providerOutput );
            }
        }


        // unset( $tags, $providerKeys, $providerCLS );

        $this->coretagInited = null;
    }


    /**
     *
     * @param string $raw
     * @param $providername
     * @return array
     */
    private function parseTag($raw, $providername)
    {

        $tag = array();
        #$attribute_pattern = '@(?P<name>\w+)\s*=\s*((?P<quote>[\"\'])(?P<value_quoted>.*?)(?P=quote)|(?P<value_unquoted>[^\s"\']+?)(?:\s+|$))@x';
        preg_match_all( '@(([a-z0-9:]+)\s*=\s*(["\'])([^\3]*)\3)@isU', $raw, $matches, PREG_SET_ORDER );

        $len = count( $matches );
        for ( $i = 0; $i < $len; ++$i )
        {
            $name  = $matches[ $i ][ 2 ];
            $value = $matches[ $i ][ 4 ];

            $tag[ $name ] = $value;
        }


        return $tag;


        if ( $name === 'asset' )
        {
            //print_r($matches);
            //exit;
        }
        Library::disableErrorHandling();

        libxml_use_internal_errors( true );

        $dochtml = new DOMDocument( '1.0', 'UTF-8' );
        $dochtml->loadHTML( '<!DOCTYPE html><' . $name . ' ' . $raw . '/>' );

        libxml_use_internal_errors( false );


        $atgs = $dochtml->getElementsByTagName( $name );

        foreach ( $atgs as $data )
        {
            if ( is_object( $data->attributes ) )
            {
                foreach ( $data->attributes as $attr )
                {
                    if ( $name === 'asset' )
                    {
                        //   print_r($attr);
                        //    exit;
                    }

                    if ( $name === 'component' )
                    {
                        //	print_r($attr);
                    }

                    if ( $attr->nodeName === $name && !$attr->nodeValue )
                    {
                        continue;
                    }

                    if ( $attr->nodeName !== 'transform' )
                    {
                        $tag[ $attr->nodeName ] = $attr->nodeValue; //$matches[3][$key];
                    }
                    else
                    {
                        $tag[ $attr->nodeName ][ ] = $attr->nodeValue; //$matches[3][$key];
                    }
                }
            }
        }

        // unset( $matches );

        Library::enableErrorHandling();

        return $tag;
    }

    /**
     * Parse Core-Tag attributes
     *
     * @param string $raw
     * @return array
     */
    private function parseInTag(&$raw)
    {

        $tag = array();

        $attribute = explode( ':', trim( $raw ) );

        if ( ( $len = count( $attribute ) ) > 1 )
        {
            array_shift( $attribute );
            $len--;
        }
        else
        {
            return $tag;
        }

        for ( $x = 0; $x < $len; ++$x )
        {
            $tag[ ] = $attribute[ $x ];
        }

        unset( $attribute );

        return $tag;
    }


    /**
     * Call from Compiler Tag "Provider"
     *
     * @param string $name
     * @param array $params
     * @return string
     * @throws BaseException
     */
    public function renderProvider($name, $params = array())
    {
        $name = strtolower( $name );

        if ( !$name || !isset( $this->contentProviders[ $name ] ) || isset( $this->skipProviderOutput[ $name ] ) )
        {
            return '';
        }


        $strTagHash = md5( $name . serialize( $params ) );

        // Load value from cache array
        if ( isset( $this->_cachedProviderOutput[ $strTagHash ] ) )
        {
            return $this->_cachedProviderOutput[ $strTagHash ];
        }

        if ( !isset( $this->coretagInited[ 'provider_' . $name ] ) )
        {
            $providerCLS = $this->getContentProvider( $name, true );
        }
        else
        {
            $providerCLS = $this->coretagInited[ 'provider_' . $name ];
        }

        try
        {
            $this->load('Template');

            $this->Template->isProvider = true;
            $str = $this->_cachedProviderOutput[ $strTagHash ] = $providerCLS->render( $params );
            $this->Template->isProvider = false;

            return $str;
        }
        catch ( Exception $e )
        {
            throw new BaseException( $e->getMessage() );
        }
    }

}
