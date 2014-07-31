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
 * @file        SideCache.php
 *
 */
class SideCache extends Loader
{

    /**
     * @var bool
     */
    protected static $isFaceBook = false;

    /**
     *
     * @var SideCache
     */
    protected static $objInstance;

    /**
     * @var bool
     */
    public $enabled = false;

    /**
     * @var null
     */
    private $cacheName = null;

    /**
     * @var null
     */
    public $_envName = null;

    /**
     *
     * @return SideCache
     */
    public static function getInstance()
    {
        if ( !is_object( self::$objInstance ) )
        {
            self::$objInstance = new SideCache();
	        self::$objInstance->load( 'Document' );

	        if ( Settings::get( 'pagedefaultenablecaching', false ) ) {
                self::$objInstance->Document->enableSiteCaching( Settings::get( 'pagedefaultcachetime', 0 ) );
	        }

            self::$objInstance->load( 'Env' );

            self::$isFaceBook = (stripos( self::$objInstance->Env->httpUserAgent(), 'facebook' ) !== false ? true : false);
        }

        return self::$objInstance;
    }

    /**
     *
     * @return SideCache
     */
    public function enable()
    {
        $this->enabled = true;
        return $this;
    }

    /**
     *
     * @return SideCache
     */
    public function disable()
    {
        $this->enabled = false;
        return $this;
    }

    public function getCache()
    {
        if ( !$this->Document->canCache() && $this->enabled )
        {
            $this->enabled = false;
        }

        if ( $this->enabled )
        {

	        $this->load('Input');
	        $this->load('Page');
	        $this->load('Document');

            $this->_envName = md5( $this->Env->requestUri() );
            $this->cacheName = Strings::getFirstCharsForFilename( DOCUMENT_NAME );


            if ( strtolower( ACTION ) === strtolower( $this->getApplication()->getOption( 'defaultAction' ) ) )
            {
                $cache = Cache::get( $this->cacheName, 'sitecache/' . CONTENT_TRANS . '/' . CONTROLLER . '/' . User::getGroupId() );
            }
            else
            {
                $cache = Cache::get( $this->cacheName, 'sitecache/' . CONTENT_TRANS . '/' . CONTROLLER . '/' . ACTION . '/' . User::getGroupId() );
            }


            if ( !isset( $cache[ DOCUMENT_NAME . '_' . $this->_envName ] ) )
            {
                return;
            }

            if ( !function_exists( 'gzcompress' ) || !function_exists( 'gzuncompress' ) )
            {
                return;
            }

            $tpl = Template::getInstance();
            // $this->load('Template');


            User::isLoggedIn();
            $data = $cache[ DOCUMENT_NAME . '_' . $this->_envName ];


            $data[ 'html' ] = Library::unmaskContent( $this->decryptPageHtmlCode( $data[ 'html' ] ) );

            $this->Document->setCommenting( $data[ 'commentingKey' ], $data[ 'commentingValue' ] );
            $this->Document->setClickAnalyse( $data[ 'clickanalyse' ] );
            $this->Document->setLastModified( $data[ 'lastmodified' ] );
            $this->Document->enableSiteCaching( $data[ 'cachetime' ] );
            $this->Document->setSiteCachingGroups( $data[ 'cachegroups' ] );
            $tpl->assign( 'permissionkey', $data[ 'commentingKey' ] );

            $data[ 'html' ] = str_replace( '</body>', '<!-- Cache: ' . $data[ 'id' ] . ' : ' . $this->_envName . ' --></body>', $data[ 'html' ] );

            if ( DEBUG )
            {
                Debug::store( 'getCache', 'Get the Page Cache' );
            }


            $tpl->sendCacheOutput( $data[ 'html' ] );

            exit;
        }
    }

    /**
     * Save the generated HTML Code
     * @param string $htmlString
     */
    public function setCache( $htmlString )
    {

        $enabled = $this->enabled;

        if ( $this->Document->canCache() === false && $this->enabled )
        {
            $enabled = false;
        }



        if ( $this->Document->canCache() && $enabled )
        {
            $this->_envName = md5( $this->Env->requestUri() );
            $name = Strings::getFirstCharsForFilename( DOCUMENT_NAME );

            $htmlString = $this->cryptPageHtmlCode( $htmlString );

            if ( strtolower( ACTION ) === strtolower( $this->getApplication()->getOption( 'defaultAction' ) ) )
            {
                $data = Cache::get( $name, 'sitecache/' . CONTENT_TRANS . '/' . CONTROLLER . '/' . User::getGroupId() );
                $data[ DOCUMENT_NAME . '_' . $this->_envName ] = array(
                    'html'            => $htmlString,
                    'cachetime'       => $this->Document->cachetime() ? $this->Document->cachetime() : Settings::get( 'pagedefaultcachetime', 3600 ),
                    'cachegroups'     => $this->Document->getCacheGroups(),
                    'clickanalyse'    => $this->Document->getClickAnalyse(),
                    'id'              => $this->Document->getDocumentID(),
                    'lastmodified'    => $this->Document->getLastModified(),
                    'commentingKey'   => $this->Document->getCommentingKey(),
                    'commentingValue' => $this->Document->getCommentingValue()
                );


                Cache::write( $this->cacheName, $data, 'sitecache/' . CONTENT_TRANS . '/' . CONTROLLER . '/' . User::getGroupId() );
            }
            else
            {
                $data = Cache::get( $name, 'sitecache/' . CONTENT_TRANS . '/' . CONTROLLER . '/' . ACTION . '/' . User::getGroupId() );
                $data[ DOCUMENT_NAME . '_' . $this->_envName ] = array(
                    'html'            => $htmlString,
                    'cachetime'       => $this->Document->cachetime() ? $this->Document->cachetime() : Settings::get( 'pagedefaultcachetime', 3600 ),
                    'cachegroups'     => $this->Document->getCacheGroups(),
                    'clickanalyse'    => $this->Document->getClickAnalyse(),
                    'id'              => $this->Document->getDocumentID(),
                    'lastmodified'    => $this->Document->getLastModified(),
                    'commentingKey'   => $this->Document->getCommentingKey(),
                    'commentingValue' => $this->Document->getCommentingValue()
                );


                Cache::write( $this->cacheName, $data, 'sitecache/' . CONTENT_TRANS . '/' . CONTROLLER . '/' . ACTION . '/' . User::getGroupId() );
            }


            if ( DEBUG )
            {
                Debug::store( 'setCache', 'Set the Cache for current page' );
            }
        }
    }

    /**
     *
     * @param string $contoller
     * @param string $action
     * @param string $alias
     * @param integer $id optional
     */
    public function cleanSideCache( $contoller, $action, $alias = '', $id = 0 )
    {
        $this->load( 'Usergroup' );
        $name = Strings::getFirstCharsForFilename( $alias );

        $contoller = ucfirst( strtolower( $contoller ) );
        $action = ucfirst( strtolower( $action ) );
        $loweraction = strtolower( $action );

        $registredLocales = Locales::getAllRegistredLocales();

        $defaultAction = strtolower( $this->getApplication()->getOption( 'defaultAction' ) );


        foreach ( $this->Usergroup->getAllUsergroups() as $r )
        {
            foreach ( $registredLocales as $loc )
            {
                if ( $loc[ 'contentlanguage' ] )
                {
                    $code = Locales::getShortLocaleFromCode( $loc[ 'code' ] );

                    if ( $loweraction === $defaultAction )
                    {
                        $cachePath = 'sitecache/' . $code . '/' . $contoller . '/' . $r[ 'groupid' ];
                    }
                    else
                    {
                        $cachePath = 'sitecache/' . $code . '/' . $contoller . '/' . $action . '/' . $r[ 'groupid' ];
                    }

                    $cache = Cache::get( $name, $cachePath );

                    if ( is_array( $cache ) )
                    {
                        foreach ( $cache as $aliasKey => $arr )
                        {
                            if ( $alias && $alias . '_' === substr( $aliasKey, 0, strlen( $alias ) + 1 ) )
                            {
                                unset( $cache[ $aliasKey ] );
                            }
                        }

                        Cache::write( $name, $cache, $cachePath );
                    }


                    /**
                     * @todo remove all parent cache elements
                     * also news/index
                     *
                     */
                }
            }
        }
    }

    /**
     *
     * @param string $code
     * @return string
     */
    private function cryptPageHtmlCode( &$code )
    {
        #  $crypt = new Crypt(Settings::get('crypt_key'));
        #   $str = $crypt->encrypt($code);
        #  $crypt = null;


        if ( function_exists( 'gzcompress' ) )
        {
            return gzcompress( $code, 3 );
        }

        return $code;
    }

    /**
     *
     * @param string $cyptedcode
     * @return string
     */
    private function decryptPageHtmlCode( &$cyptedcode )
    {
        if ( function_exists( 'gzuncompress' ) )
        {
            $cyptedcode = gzuncompress( $cyptedcode );
        }

        return $cyptedcode;

        $crypt = new Crypt( Settings::get( 'crypt_key' ) );
        $str = $crypt->decrypt( $cyptedcode );
        $crypt = null;
        return $str;
    }

}
