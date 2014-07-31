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
 * @file        Apc.php
 *
 */
class Cache_APC {
    private static $_dataCache = array();

    /**
     * Stores a value in APC cache.
     *
     * @param string $name Name of the cache entry.
     * @param mixed $data Data to store in the cache entry.
     * @param string $type Type of the cache entry.
     * @return unknown_type Boolean true if the item is saved, NULL if APC is disabled.
     */
    public static function set($name, $data, $type='data') {
        if(USE_APC) {

            $type = str_replace('/', '_', $type);

            if(apc_store(MEMORY_CACHE_KEY.'_'.$type.'_'.$name, $data)) {

                unset( self::$_dataCache[ $type ][ $name ] );

                return true;
            }
        }
    }

    /**
     * Retrieve an entry from the APC cache.
     *
     * @param string $name Name of the entry to retrieve.
     * @param string $type Type of the entry to retrieve.
     * @return mixed $cache containing the data retrieved, NULL if the entry is not stored in APC cache.
     */
    public static function get($name, $type='data') {
        if(USE_APC) {

            $type = str_replace('/', '_', $type);

            if ( isset( self::$_dataCache[ $type ][ $name ] ) )
            {
                return self::$_dataCache[ $type ][ $name ];
            }

            $cache = apc_fetch(MEMORY_CACHE_KEY.'_'.$type.'_'.$name, $success);

            if ($success !== false && is_array( $cache ) )
            {
                self::$_dataCache[ $type ][ $name ] = $cache;
                return $cache;
            }

            if($success !== false) {
                self::$_dataCache[ $type ][ $name ] = $cache;
                return $cache;
            }
            else
            {
                return NULL;
            }
        }
    }

    /**
     * Delete an entry from the APC cache.
     * @param string $name Name of the entry to delete.
     * @param string $type Type of the entry to delete.
     * @return void
     */

    public static function delete($name, $type='data') {
        if(USE_APC) {
            $type = str_replace('/', '_', $type);

            apc_delete(MEMORY_CACHE_KEY.'_'.$type.'_'.$name);
        }
    }

    /**
     * Clears an entire cache.
     *
     * @param string $type Type fo the cache to delete.
     * @return void
     */
    public static function clear($type='data') {
        if(USE_APC) {
            $type = str_replace('/', '_', $type);

            if($type=='session') return;

            $apc_user_caches = apc_cache_info('user');
            if(!empty($apc_user_caches['cache_list'])) {
                foreach($apc_user_caches['cache_list'] as $cache) {
                    if(strpos($cache['info'], MEMORY_CACHE_KEY.'_'.$type)!==false) {
                        apc_delete($cache['info']);
                    }
                }
            }
        }
    }

    /**
     * Flush the cache. All entries in all types are deleted.
     * @param $excludes Array of cache types to exclude from the flush action.
     * @return void
     */
    public static function flush($excludes = array())
    {
        if(USE_APC) {
            $excludes[] = 'session'; // don't flush sessions
            $apc_user_caches = apc_cache_info('user');
            if(!empty($apc_user_caches['cache_list'])) {
                foreach($apc_user_caches['cache_list'] as $cache) {
                    if(strpos($cache['info'], MEMORY_CACHE_KEY)!==false) {
                        foreach($excludes as $exclude) {
                            if(strpos($cache['info'], MEMORY_CACHE_KEY.'_'.$exclude)===false) {
                                apc_delete($cache['info']);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @return array
     */
    public static function getData()
    {
        $data = array('apc' => array('folders' => 0, 'files' => 0, 'size' => 0));
        if(USE_APC) {
            $apc_user_caches = apc_cache_info('user');
            if(!empty($apc_user_caches['cache_list'])) {
                foreach($apc_user_caches['cache_list'] as $cache) {
                    if(strpos($cache['info'], MEMORY_CACHE_KEY)!==false) {
                        $data['apc']['files'] += 1;
                        $data['apc']['size'] += $cache['mem_size'];
                    }
                }
            }
        }
        return $data;
    }

}
