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
 * @package     Field
 * @version     3.0.0 Beta
 * @category    Form Fields
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Field.php
 */
class Field
{
    
    
    
    
    /**
     * @param $type
     * @return bool|mixed
     */
    public static function getFieldAttributes( $type )
    {
        if ( empty( $type ) )
        {
            return false;
        }

        $class_name = 'Field_' . ucfirst( strtolower( $type ) ) . 'Field';


        return call_user_func( array( $class_name, 'getAttributes' ) );
    }

    /**
     * @param $field_data
     * @return mixed
     */
    public static function getFieldDefinition( $field_data )
    {
        $class_name = 'Field_' . ucfirst( strtolower( $field_data[ 'type' ] ) ) . 'Field';
        return call_user_func_array( array( $class_name, 'getFieldDefinition' ), array( $field_data ) );
    }

    /**
     * @param $field_data
     * @return mixed
     */
    public static function getFieldRender( $field_data )
    {



        $class_name = 'Field_' . ucfirst( strtolower( $field_data[ 'type' ] ) ) . 'Field';
        return call_user_func_array( array( $class_name, '_renderField' ), array( $field_data ) );
    }
}
