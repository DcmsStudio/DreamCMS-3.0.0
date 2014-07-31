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
 * @file        Abstract.php
 *
 */
abstract class Provider_Abstract extends Loader
{

    /**
     * @var
     */
    public static $dcms;

    /**
     * @var null
     */
    public static $_availableProviders = null;

    /**
     * @var array
     */
    public static $providerdata = array();

    /**
     * @var array
     */
    public static $cachedata = array();

    /**
     * @var null
     */
    public static $extra = null;

    /**
     * @var null
     */
    public static $pagedata = null;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * helpers for the Provider function
     * @param string $key
     * @param string $data
     */
    public static function setProviderCacheData( $key, $data )
    {
        self::$providerdata[ $key ] = $data;
    }

    /**
     *
     * @param string $name
     * @return mixed/null
     */
    public static function getProviderCacheData( $name )
    {
        if ( isset( self::$providerdata[ $name ] ) )
        {
            return self::$providerdata[ $name ];
        }

        return null;
    }

    /**
     *
     * @param string $key
     * @param mixed $data
     */
    protected static function setCacheData( $key, $data )
    {
        self::$cachedata[ $key ] = $data;
    }

    /**
     *
     * @param string $name
     * @return mixed
     */
    protected static function getCacheData( $name )
    {
        if ( isset( self::$cachedata[ $name ] ) )
        {
            return self::$cachedata[ $name ];
        }

        return null;
    }

}

?>