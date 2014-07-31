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
 * @file        Site.php
 *
 */
class Site extends Loader
{

    /**
     * Current object instance (do not remove)
     * @var object
     */
    protected static $objInstance;

    /**
     * @var null
     */
    private $_sitedata = null;

    /**
     * @var null
     */
    private $_siteoutputdata = null;

    /**
     * @var null
     */
    private $_moduldata = null;

    /**
     * @var null
     */
    private $cachgroups = null;

    /**
     * @var array
     */
    protected $metatags = array(
        'date'             => true,
        'pragma'           => true,
        'keywords'         => true,
        'description'      => true,
        'copyright'        => true,
        'author'           => true,
        'expires'          => true,
        'revisit'          => true,
        'robots'           => true,
        'publisher'        => true,
        'content-language' => true,
        'page-topic'       => true,
        'page-type'        => true,
        'language'         => true );

    /**
     * @var array
     */
    protected $meta_geotags = array(
        'geo.region',
        'geo.placename',
        'geo.position',
        'ICBM' );

    /**
     * @var array
     */
    protected static $_allowedSiteData = array(
        // the absolute content
        'pagetitle'     => true,
        'metadata'      => true,
        'contentdata'   => true,
        //loadPage data
        'aliasregistry' => true,
        // only the root page
        'website'       => true,
    );

    /**
     * Prevent cloning of the object (Singleton)
     */
    private function __clone()
    {
        
    }

    /**
     * Return the current object instance (Singleton)
     *
     * @return object
     */
    public static function getInstance()
    {
        if ( !is_object( self::$objInstance ) )
        {
            self::$objInstance = new Site();
            self::$objInstance->initDefaults();
        }

        return self::$objInstance;
    }

    /**
     *
     */
    public function initDefaults()
    {
        if ( Settings::get( 'meta_author', '' ) )
        {
            $this->setMetaAuthor( Settings::get( 'meta_author' ) );
        }
    }

    public function freeMem()
    {
        $this->_sitedata = null;
        $this->_siteoutputdata = null;
        $this->_moduldata = null;
        $this->cachgroups = null;
    }

    /**
     * Enable the Site Caching
     *
     * @param integer $cachetime default null
     */
    public function enableSiteCaching( $cachetime = null )
    {
        $this->cachable = true;
        if ( !is_null( $cachetime ) && intval( $cachetime ) > 0 )
        {
            $this->cachtime = intval( $cachetime );
        }
    }

    /**
     * will disable Site Caching
     */
    public function disableSiteCaching()
    {
        $this->cachable = false;
    }

    /**
     *
     * @return bool
     */
    public function canCache()
    {
        return $this->cachable;
    }

    /**
     *
     * @return bool
     */
    public function cachetime()
    {
        return $this->cachtime;
    }

    /**
     * will set the Usergroups if can Site Caching
     *
     * @param (string or integer) $cachegroups default is null
     */
    public function setSiteCachingGroups( $cachegroups = null )
    {
        $this->cachgroups = $cachegroups;
    }

    /**
     *
     * @return type
     */
    public function getCacheGroups()
    {
        return $this->cachgroups;
    }

    /**
     * will set the click analyser
     *
     * @param bool $clickanalyse
     * @internal param $clickanalyse (bool or integer)
     */
    public function setClickAnalyse( $clickanalyse = false )
    {
        $this->clickanalyse = ($clickanalyse ? true : false);
    }

    /**
     *
     * @return type
     */
    public function getClickAnalyse()
    {
        return $this->clickanalyse;
    }

    /**
     * insert
     *
     * @param      $key
     * @param null $value
     * @internal param string $label
     * @internal param string $link
     */
    public function set( $key, $value = null )
    {
        if ( isset( $this->_sitedata[ $key ] ) && is_array( $this->_sitedata[ $key ] ) && is_array( $value ) )
        {
            $this->_sitedata[ $key ] = array_merge( $this->_sitedata[ $key ], $value );
        }
        else
        {
            $this->_sitedata[ $key ] = $value;
        }
    }

    /**
     *
     * @param string $key default is null and return complete array
     * @return type
     */
    public function get( $key = null )
    {
        if ( is_string( $key ) )
        {
            return (isset( $this->_sitedata[ $key ] ) ? $this->_sitedata[ $key ] : null);
        }
        return $this->_sitedata;
    }

    /**
     * check existing array entry by the key
     *
     * @param string $key
     * @return bool
     */
    public function exists( $key )
    {
        return (isset( $this->_sitedata[ $key ] ) ? true : false);
    }

    /**
     * Remove
     */
    public function remove( $key )
    {
        unset( $this->_sitedata[ $key ] );
    }

    /**
     * Clear
     */
    public function clear()
    {
        $this->_sitedata = array();
    }

    /**
     *
     * @param array $arr
     * @param bool  $isInit
     */
    public function setMetadata( $arr = array(), $isInit = false )
    {
        return;
        print_r( debug_backtrace() );

        die();
        $this->load( 'Pagemeta' );
        $defines = $this->Pagemeta->tableMetaFieldDefinition;

        if ( $isInit === true )
        {
            $this->_sitedata[ 'metadata' ] = $meta = array();

            if ( empty( $meta[ 'meta_keywords' ] ) )
            {
                $meta[ 'meta_keywords' ] = Settings::get( 'meta_keywords', '' );
            }

            if ( empty( $meta[ 'meta_description' ] ) )
            {
                $meta[ 'meta_description' ] = Settings::get( 'meta_description', '' );
            }


            if ( empty( $meta[ 'meta_copyright' ] ) )
            {
                $meta[ 'meta_copyright' ] = Settings::get( 'meta_copyright', '' );
            }


            if ( empty( $meta[ 'meta_expires' ] ) )
            {
                $meta[ 'meta_expires' ] = Settings::get( 'meta_expires', '' );
            }


            if ( empty( $meta[ 'meta_author' ] ) )
            {
                $meta[ 'meta_author' ] = Settings::get( 'meta_author', '' );
            }


            if ( empty( $meta[ 'meta_revisit' ] ) )
            {
                $meta[ 'meta_revisit' ] = Settings::get( 'meta_revisitafter', '30 Days' );
            }


            if ( empty( $meta[ 'meta_indexfollow' ] ) )
            {
                $meta[ 'meta_robots' ] = Settings::get( 'meta_robots', 'index,follow' );
            }

            if ( empty( $meta[ 'meta_language' ] ) )
            {
                $meta[ 'meta_language' ] = CONTENT_TRANS;
            }

            $this->_sitedata[ 'metadata' ] = $meta;
        }


        $tmp = array();

        foreach ( $this->metatags as $k => $v )
        {
            if ( $k === 'description' )
            {
                $arr[ $k ] = isset( $arr[ 'metadescription' ] ) ? $arr[ 'metadescription' ] : (isset( $arr[ $k ] ) && is_string( $arr[ $k ] ) ? $arr[ $k ] : '');
            }

            if ( $k === 'keywords' )
            {
                if ( isset( $arr[ 'metakeywords' ] ) && !isset( $arr[ 'keywords' ] ) )
                {
                    $arr[ 'keywords' ] = $arr[ 'metakeywords' ];
                }
                elseif ( isset( $arr[ 'metakeywords' ] ) && isset( $arr[ 'keywords' ] ) )
                {
                    $arr[ 'keywords' ] = $arr[ 'metakeywords' ];
                }

                //$arr['keywords'] = isset($arr['metakeywords']) ? $arr['metakeywords'] : ( isset($arr['keywords']) && is_string($arr['keywords']) ? $arr['keywords'] : '' );
            }

            if ( $k === 'robots' )
            {
                $arr[ 'robots' ] = isset( $arr[ 'indexfollow' ] ) ? $arr[ 'indexfollow' ] : (isset( $arr[ $k ] ) && is_string( $arr[ $k ] ) ? $arr[ $k ] : '');
            }

            if ( isset( $arr[ $k ] ) && $arr[ $k ] != '' )
            {
                if ( !empty( $arr[ $k ] ) && trim( $arr[ $k ] ) != '' )
                {
                    $this->_sitedata[ 'metadata' ][ 'meta_' . $k ] = isset( $arr[ $k ] ) && is_string( $arr[ $k ] ) ? $arr[ $k ] : '';
                }
            }
        }
    }

    /**
     *
     * @param type $data
     * @return type
     */
    public function prepareWebsiteMetadata( $data )
    {
        if ( is_array( $data ) )
        {
            $meta = array();
            $meta[ 'meta_keywords' ] = Settings::get( 'meta_keywords', $data[ 'metakeywords' ] );
            $meta[ 'meta_description' ] = Settings::get( 'meta_description', $data[ 'metadescription' ] );
            $meta[ 'meta_copyright' ] = Settings::get( 'meta_copyright', 'hghj' );
            $meta[ 'meta_expires' ] = Settings::get( 'meta_expires', '' );
            $meta[ 'meta_author' ] = Settings::get( 'meta_author', '' );
            $meta[ 'meta_revisit' ] = Settings::get( 'meta_revisitafter', '30 Days' );
            $meta[ 'meta_robots' ] = Settings::get( 'meta_robots', 'index,follow' );
            $meta[ 'meta_language' ] = CONTENT_TRANS;

            if ( isset( $data[ 'metakeywords' ] ) && !empty( $data[ 'metakeywords' ] ) )
            {
                $meta[ 'meta_keywords' ] = $data[ 'metakeywords' ];
            }

            if ( isset( $data[ 'metadescription' ] ) && !empty( $data[ 'metadescription' ] ) )
            {
                $meta[ 'meta_description' ] = $data[ 'metadescription' ];
            }

            if ( isset( $data[ 'indexfollow' ] ) && !empty( $data[ 'indexfollow' ] ) )
            {
                $meta[ 'meta_robots' ] = $data[ 'indexfollow' ];
            }

            if ( isset( $data[ 'indexfollow' ] ) && !empty( $data[ 'indexfollow' ] ) )
            {
                $meta[ 'meta_robots' ] = $data[ 'indexfollow' ];
            }

            if ( isset( $data[ 'language' ] ) && !empty( $meta[ 'language' ] ) )
            {
                $meta[ 'meta_language' ] = $data[ 'language' ];
            }
            $data[ 'metadata' ] = $meta;
        }
        return $data;
    }

    /**
     *
     * @param type $data
     */
    public function prepareContentMetadata( $data )
    {
        if ( is_array( $data ) )
        {
            $website = $this->_siteoutputdata[ 'website' ];

            if ( isset( $data[ 'metakeywords' ] ) && !empty( $data[ 'metakeywords' ] ) )
            {
                $website[ 'metadata' ][ 'meta_keywords' ] = $data[ 'metakeywords' ];
            }

            if ( isset( $data[ 'indexfollow' ] ) && !empty( $data[ 'indexfollow' ] ) )
            {
                $website[ 'metadata' ][ 'meta_robots' ] = $data[ 'indexfollow' ];
            }

            if ( isset( $data[ 'metadescription' ] ) && !empty( $data[ 'metadescription' ] ) )
            {
                $website[ 'metadata' ][ 'meta_description' ] = $data[ 'metadescription' ];
            }

            $this->_siteoutputdata[ 'website' ] = $website;
        }
    }

    /**
     *
     * @param type $author
     */
    public function setMetaAuthor( $author = null )
    {
        if ( is_string( $author ) && $author !== '' )
        {
            $website = & $this->_siteoutputdata[ 'website' ];
            $website[ 'metadata' ][ 'meta_author' ] = $author;

            $this->_siteoutputdata[ 'website' ] = $website;
        }
    }

    /**
     *
     * @param type $key
     * @return type
     */
    private function isAllowedSiteData( $key )
    {
        $key = strtolower( $key );
        if ( isset( self::$_allowedSiteData[ $key ] ) )
        {
            return true;
        }

        return false;
    }

    /**
     * this function cache the content for a site
     *
     * @param string $key
     * @param mixed $data
     */
    public function setSiteData( $key, $data )
    {

        if ( !$this->isAllowedSiteData( $key ) )
        {
            Error::raise( sprintf( 'Sitedata key `%s` not allowed', $key ) );
        }
        $key = strtolower( $key );

        switch ( $key )
        {
            case 'pagetitle':
                $this->_siteoutputdata[ 'pagetitle' ] = $data;
                break;

            case 'website':
                $data = $this->prepareWebsiteMetadata( $data );

                if ( $data[ 'cacheable' ] && intval( $data[ 'cachetime' ] ) > 0 )
                {
                    $this->cachable = true;
                    $this->cachtime = intval( $data[ 'cachetime' ] );
                }
                $this->_siteoutputdata[ 'website' ] = $data;
                break;


            case 'contentdata':
                $this->prepareContentMetadata( $data );
                $this->_siteoutputdata[ 'contentdata' ] = $data;
                break;
        }
        $this->_siteoutputdata[ $key ] = $data;
    }

    /**
     *
     * @param type $key
     * @param type $data
     */
    public function replaceSiteData( $key, $data )
    {
        if ( !$this->isAllowedSiteData( $key ) )
        {
            Error::raise( sprintf( 'Sitedata key `%s` not allowed', $key ) );
        }

        $key = strtolower( $key );
    }

    /**
     *
     * @param type $key
     * @return type
     */
    public function getSiteData( $key )
    {
        if ( !$this->isAllowedSiteData( $key ) )
        {
            Error::raise( sprintf( 'Sitedata key `%s` not allowed', $key ) );
        }

        $key = strtolower( $key );
        switch ( $key )
        {
            case 'pagetitle':
                return (isset( $this->_siteoutputdata[ 'pagetitle' ] ) ? $this->_siteoutputdata[ 'pagetitle' ] : null);
                break;
            case 'website':
                return (isset( $this->_siteoutputdata[ 'website' ] ) ? $this->_siteoutputdata[ 'website' ] : null);
                break;
            case 'aliasregistry':
                return (isset( $this->_siteoutputdata[ 'aliasregistry' ] ) ? $this->_siteoutputdata[ 'aliasregistry' ] : null);
                break;
            case 'contentdata':
                return (isset( $this->_siteoutputdata[ 'contentdata' ] ) ? $this->_siteoutputdata[ 'contentdata' ] : null);
                break;
        }
    }

    /**
     *
     * @return type
     */
    public function getModulData()
    {
        return $this->_moduldata;
    }

    /**
     *
     * @param type $id
     */
    public function setContentID( $id = null )
    {
        $this->_contentid = $id;
    }

    /**
     * get the foundet content by alias (@see class urlmapper)
     *
     * @return mixed returns integer of contentid if found or null if not found
     */
    public function getContentID()
    {
        if ( isset( $this->_siteoutputdata[ 'aliasregistry' ] ) && !empty( $this->_siteoutputdata[ 'aliasregistry' ][ 'contentid' ] ) )
        {
            return $this->_siteoutputdata[ 'aliasregistry' ][ 'contentid' ];
        }


        if ( !is_null( $this->_contentid ) && intval( $this->_contentid ) )
        {
            return $this->_contentid;
        }

        return null;
    }

}
