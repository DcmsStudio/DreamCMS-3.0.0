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
 * @file        Registry.php
 *
 */
class Registry
{

    /**
     * @var array
     */
    protected static $_objects;

    /**
     * @var array
     */
    protected static $_vars;

    public static function freeObjects()
    {
        self::$_objects = null;
    }

    public static function free()
    {
        self::$_vars = null;
    }

    /**
     *
     * @param string $key
     * @param object $value
     */
    public static function setObject( $key, $value )
    {
        self::$_objects[ $key ] = $value;
    }

    /**
     *
     * @param string $key
     * @return object|null
     */
    public static function &getObject( $key )
    {
	    if (isset(self::$_objects[ $key ])) {
			return self::$_objects[ $key ];
	    }


    	$ref = null;
        return $ref;
    }

    /**
     *
     * @param string $key
     * @return bool
     */
    public static function objectExists( $key )
    {

        return (isset( self::$_objects[ $key ] ) && is_object( self::$_objects[ $key ] ));
    }

    /**
     *
     * @param string $key
     */
    public static function removeObject( $key )
    {
        self::$_objects[ $key ] = null;
    }

    /**
     *
     * @param string $key
     * @param mixed $value
     */
    public static function set( $key, $value )
    {
        self::$_vars[ $key ] = $value;
    }

    /**
     * 
     * 
     * @param string $key
     * @return bool
     */
    public static function exists( $key )
    {
        return isset( self::$_vars[ $key ] );
    }

    /**
     *
     * @param string $key
     * @return mixed|null
     */
    public static function get( $key )
    {
        return (self::exists( $key ) ? self::$_vars[ $key ] : null);
    }

    /**
     *
     * @param string $key
     */
    public static function clear( $key )
    {
        unset( self::$_vars[ $key ] );
    }

}

?>