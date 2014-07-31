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
 * @copyright    2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Cache.php
 *
 */
class Cache extends Cache_Abstract
{

    /**
     * Saves cache data to the APC or Xcache cache (if available) or the file cache
     *
     * @param string $name Name of the entry to cache.
     * @param mixed $data Data to store in the cache.
     * @param string $type Type of the cached data (usually data, but can be any of data, components, page or resources).
     */
    public static function write($name, $data, $type = 'data')
    {
        if ( USE_APC )
        {
            Cache_APC::set( $name, $data, $type );
        }
        elseif ( USE_XCACHE )
        {
            Cache_XCache::set( $name, $data, $type );
        }
        elseif ( USE_DBCACHE )
        {
            Cache_DBCache::set( $name, $data, $type );
        }
        elseif ( USE_SQLITECACHE )
        {
            Cache_Sqlite::set( $name, $data, $type );
        }
        elseif ( USE_EACCELERATOR )
        {
            Cache_Eaccelerator::set( $name, $data, $type );
        }
        else
        {
            Cache_Filecache::set( $name, $data, $type );
        }
    }

    /**
     * Writes a cache to a file. skipping any APC or XCache checks (this file can later be included, or read from cache using get).
     *
     * @param  string $name Name of the cache entry.
     * @param  mixed $data Data to store in the file.
     * @param string $type Type of the cached data.
     */
    public static function file($name, $data, $type = 'data')
    {
        Cache_Filecache::set( $name, $data, $type, true );
    }

    /**
     * Retrieve an entry from the cache.
     *
     * @param     string $name Name of the entry to retrieve from cache.
     * @param string $type Type of the entry to retrieve from cache (defaults to 'data').
     * @param null $cacheTime
     * @return null|mixed $cache data retrieved from cache, or NULL if item is not in cache.
     */
    public static function get($name, $type = 'data', $cacheTime = null)
    {
        $cache = null;
        $cache = Cache_Filecache::get( $name, $type, $cacheTime );

        if ( USE_APC )
        {
            $cache = Cache_APC::get( $name, $type, $cacheTime );
        }
        elseif ( USE_XCACHE )
        {
            $cache = Cache_XCache::get( $name, $type, $cacheTime );
        }
        elseif ( USE_DBCACHE )
        {
            $cache = Cache_DBCache::get( $name, $type, $cacheTime );
        }
        elseif ( USE_SQLITECACHE )
        {
            $cache = Cache_Sqlite::get( $name, $type, $cacheTime );
        }
        elseif ( USE_EACCELERATOR )
        {
            $cache = Cache_Eaccelerator::get( $name, $type, $cacheTime );
        }


        return $cache;
    }

    /**
     * Delete an entry from the cache.
     *
     * @param  string $name Name of the entry to delete.
     * @param string $type Type of the entry to delete.
     * @return void
     */
    public static function delete($name, $type = 'data')
    {
        if ( USE_SQLITECACHE )
        {
            Cache_Sqlite::delete( $name, $type );
        }
        elseif ( USE_APC )
        {
            Cache_APC::delete( $name, $type );
        }
        elseif ( USE_XCACHE )
        {
            Cache_XCache::delete( $name, $type );
        }
        elseif ( USE_DBCACHE )
        {
            Cache_DBCache::clear( $name, $type );
        }
        elseif ( USE_EACCELERATOR )
        {
            Cache_Eaccelerator::delete( $name, $type );
        }

        Cache_Filecache::delete( $name, $type );

    }

    /**
     * Clears an entire cache.
     *
     * @param string $type Type of the cache to delete.
     * @param bool $clearsubdirs
     * @return void
     */
    public static function clear($type = 'data', $clearsubdirs = false)
    {

        if ( USE_SQLITECACHE )
        {
            Cache_Sqlite::delete( $type, true );
        }
        elseif ( USE_APC )
        {
            Cache_APC::clear( $type );
        }
        elseif ( USE_XCACHE )
        {
            Cache_XCache::clear( $type );
        }
        elseif ( USE_DBCACHE )
        {
            Cache_DBCache::clear( $type );
        }
        elseif ( USE_EACCELERATOR )
        {
            Cache_Eaccelerator::flush();
        }

        Cache_Filecache::clear( $type, $clearsubdirs );

    }

    /**
     * Flush the cache, except for cached images.
     * @return void
     */
    public static function refresh()
    {

        if ( USE_APC )
        {
            Cache_APC::flush( array(
                'img') );
            Cache_APC::flush( array(
                'session') );
        }
        elseif ( USE_SQLITECACHE )
        {
            Cache_Sqlite::flush( array(
                'img') );
            Cache_Sqlite::flush( array(
                'session') );
        }
        elseif ( USE_XCACHE )
        {
            Cache_XCache::flush( array(
                'img') );
            Cache_XCache::flush( array(
                'session') );
        }
        elseif ( USE_DBCACHE )
        {
            Cache_DBCache::flush( array(
                'img') );
            Cache_DBCache::flush( array(
                'session') );
        }
        elseif ( USE_EACCELERATOR )
        {
            Cache_Eaccelerator::flush();
        }

        else
        {
            Cache_Filecache::flush( array(
                'img') );
        }
    }

    /**
     * Flush the cache, including cached images.
     * @return void
     */
    public static function reload()
    {
        if ( USE_SQLITECACHE )
        {
            Cache_Sqlite::flush( array(
                'session') );
        }
        elseif ( USE_APC )
        {
            Cache_APC::flush( array(
                'session') );
        }
        elseif ( USE_XCACHE )
        {
            Cache_APCCache::flush( array(
                'session') );
        }
        elseif ( USE_DBCACHE )
        {
            Cache_DBCache::flush( array(
                'session') );
        }
        elseif ( USE_EACCELERATOR )
        {
            Cache_Eaccelerator::flush();
        }
        else
        {
            Cache_Filecache::flush();
        }
    }

    /**
     *
     */
    public static function doRunShutdown()
    {
        if ( USE_DBCACHE )
        {
            Cache_DBCache::runShutdown();
        }
        elseif ( USE_SQLITECACHE )
        {
            Cache_Sqlite::runShutdown();
        }
    }

    /**
     * @param string $query
     * @param array $data
     */
    public static function setDB($query, &$data)
    {
        self::write( md5( $query ), $data, 'data/db_cache' );
    }

    /**
     * @param string $query
     * @return mixed
     */
    public static function getDB($query)
    {
        return self::get( md5( $query ), 'data/db_cache' );
    }

    /**
     * @param string $type
     * @return array
     */
    public static function getData($type = 'data')
    {

        $data = array();

        if ( USE_APC )
        {
            $data[ 'apc' ] = Cache_APC::getData();
        }

        $data[ 'filecache' ] = Cache_Filecache::countCaches( $type );

        return $data;
    }
}

?>