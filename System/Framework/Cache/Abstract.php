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
abstract class Cache_Abstract
{

    protected static $cachePath = false;

    protected static $_dataCache = array();

    /**
     *
     * @param bool|string $name default false
     */
    public static function setCachePath( $name = false )
    {
        self::$cachePath = $name;
    }

    /**
     * @return bool|string
     */
    public static function getCachePath()
    {
        return (!self::$cachePath ? PAGE_CACHE_PATH : self::$cachePath);
    }

    /**
     *
     * @param string $name
     * @param string $type
     */
    public static function freeMem( $name, $type = 'data' )
    {
        if ( isset( self::$_dataCache[ $type ][ $name ] ) )
        {
            unset( self::$_dataCache[ $type ][ $name ] );
        }
    }

    /**
     *
     * @param mixed $var
     * @param boolean $return
     * @return string
     */
    static function var_export_min( $var, $return = false )
    {
        if ( is_array( $var ) )
        {
            $toImplode = array();
            foreach ( $var as $key => $value )
            {

                if ( (is_numeric( $value ) && substr( $value, 0, 1 ) !== 0) || is_bool( $value ) )
                {
                    $toImplode[] = var_export( $key, true ) . '=>' . (is_bool( $value ) ? ($value ? 'true' : 'false') : $value);
                }
                else
                {
                    $toImplode[] = var_export( $key, true ) . '=>' . self::var_export_min( $value, true );
                }
            }

            $code = 'array(' . implode( ',', $toImplode ) . ')';
            unset( $toImplode, $var );

            if ( $return )
            {
                return $code;
            }
            else
            {
                echo $code;
            }
        }
        else
        {
            return var_export( $var, $return );
        }
    }

}

?>