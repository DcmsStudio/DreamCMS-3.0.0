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
 * @file        Arr.php
 *
 */
class Arr implements Iterator
{

    private $tmpArray = array();

    /**
     * @param $array
     */
    public function __construct( $array )
    {
        if ( is_array( $array ) )
        {
            $this->tmpArray = $array;
        }
    }

    /**
     * @return int
     */
    public function rowCount()
    {
        return count( $this->tmpArray );
    }

    public function rewind()
    {
        reset( $this->tmpArray );
    }

    /**
     * @return mixed
     */
    public function current()
    {
        $var = current( $this->tmpArray );

        return $var;
    }

    /**
     * @return mixed
     */
    public function key()
    {
        $var = key( $this->tmpArray );

        return $var;
    }

    /**
     * @return mixed
     */
    public function fetch()
    {

        $r = $this->current();
        $this->next();
        return $r;
    }

    public function fetchAll()
    {
        $tmp = array();

        $max = $this->rowCount();
        for ( $x = 0; $x < $max; ++$x )
        {
            $tmp[] = $this->current();
            $this->next();
        }


        return $r;
    }

    /**
     * @return mixed
     */
    public function next()
    {
        $var = next( $this->tmpArray );

        return $var;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        $key = key( $this->tmpArray );
        $var = ($key !== NULL && $key !== FALSE);

        return $var;
    }

    /**
     * Tests if an array is associative or not.
     *
     *     // Returns TRUE
     *     Arr::is_assoc(array('username' => 'john.doe'));
     *
     *     // Returns FALSE
     *     Arr::is_assoc('foo', 'bar');
     *
     * @param array $array
     * @return boolean
     */
    public static function is_assoc( array $array )
    {
        // Keys of the array
        $keys = array_keys( $array );

        // If the array keys of the keys match the keys, then the array must
        // not be associative (e.g. the keys array looked like {0:0, 1:1...}).
        return array_keys( $keys ) !== $keys;
    }

    /**
     * Retrieve a single key from an array. If the key does not exist in the
     * array, the default value will be returned instead.
     *
     *     // Get the value "username" from $_POST, if it exists
     *     $username = Arr::get($_POST, 'username');
     *
     *     // Get the value "sorting" from $_GET, if it exists
     *     $sorting = Arr::get($_GET, 'sorting', 'desc');
     *
     *
     * @param array $array array to extract from
     * @param string $key key name
     * @param mixed $default default value
     * @return mixed
     */
    public static function get( $array, $key, $default = NULL )
    {
        return isset( $array[ $key ] ) ? $array[ $key ] : $default;
    }

    /**
     * Retrieves multiple keys from an array. If the key does not exist in the
     * array, the default value will be added instead.
     *
     *     // Get the values "username", "password" from $_POST<br>
     *     $auth = Arr::extract($_POST, array('username', 'password'));<br>
     *      or<br>
     *     $auth = Arr::extract($_POST, 'username,password');
     *
     * @param array $array array to extract keys from
     * @param array/string $keys list of key names
     * @param mixed $default default value
     * @return array
     */
    public static function extract( $array, $keys, $default = NULL )
    {
        if ( is_string( $keys ) )
        {
            $keys = explode( ',', $keys );
        }

        $found = array();
        foreach ( $keys as $key )
        {
            $found[ $key ] = isset( $array[ $key ] ) ? $array[ $key ] : $default;
        }

        return $found;
    }

    /**
     *
     * @param array $array
     * @param array $toKeys
     * @return array
     */
    public static function convertKeys( $array, array $toKeys )
    {
        $retArr = array();

        foreach ( $array as $k => &$v )
        {
            if ( is_array( $v ) )
            {
                $array[ $k ] = self::convertKeys( $v, $toKeys );
            }
            else
            {
                foreach ( $toKeys as $oldName => $newName )
                {
                    if ( $k == $oldName )
                    {
                        $array[ $newName ] = $v;
                        unset( $array[ $oldName ] );
                    }
                }
            }
        }

        return $array;
    }

    /**
     *
     * @param array $array
     * @param string $key
     */
    public static function aSort( &$array, $key )
    {
        $sorter = array();
        $ret = array();
        reset( $array );
        foreach ( $array as $ii => $va )
        {
            $sorter[ $ii ] = $va[ $key ];
        }
        asort( $sorter );
        foreach ( $sorter as $ii => $va )
        {
            $ret[ $ii ] = $array[ $ii ];
        }
        $array = $ret;
    }

}

?>