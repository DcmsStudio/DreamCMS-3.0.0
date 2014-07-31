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
 * @file        HiddenField.php
 */
class Field_HiddenField extends Field_BaseField
{

    /**
     * @return array
     */
    static function getAttributes()
    {
        return array( 'description', 'value', 'id' );
    }

    /**
     * @param $field
     * @return array
     */
    static public function getFieldDefinition( $field )
    {
        $value = !empty( $field[ 'value' ] ) ? $field[ 'value' ] : NULL;

        if ( !empty( $field[ 'options' ] ) )
        {
            $field = array_merge( $field, unserialize( $field[ 'options' ] ) );
        }

        $data = array(
                'type'        => $field[ 'type' ],
                'label'       => (!empty( $field[ 'label' ] ) ? $field[ 'label' ] : ''),
                'description' => (!empty( $field[ 'description' ] ) ? $field[ 'description' ] : ''),
                'grouplabel'  => (!empty( $field[ 'grouplabel' ] ) ? $field[ 'grouplabel' ] : null),
                'name'        => $field[ 'id' ],
                'id'          => $field[ 'id' ],
                'fieldid'     => $field[ 'fieldid' ],
                'value'       => (!empty( $field[ 'value' ] ) ? $field[ 'value' ] : '' )
        );
        if ( !empty( $field[ 'description' ] ) )
        {
            $data[ 'tip' ] = 'custom::' . $field[ 'id' ];
        }
        if ( !empty( $field[ 'tip' ] ) )
        {
            $data[ 'tip' ] = $field[ 'tip' ];
        }
        if ( !is_null( $value ) )
        {
            $data[ 'value' ] = $value;
        }
        return $data;
    }

    /**
     * @param $field
     * @return null
     */
    public static function renderField( $field )
    {
        return !empty( $field[ 'value' ] ) ? $field[ 'value' ] : NULL;
    }

    /**
     * @param $field
     * @return string
     */
    public static function _renderField( $field )
    {

        $data[ 'tagname' ]               = 'input';
        $data[ 'attributes' ][ 'name' ]  = $field[ 'name' ];
        $data[ 'attributes' ][ 'id' ]    = $field[ 'id' ];
        $data[ 'attributes' ][ 'type' ]  = 'hidden';
        $data[ 'attributes' ][ 'value' ] = $field[ 'value' ];
        return Html::createTag( $data );
    }
}

?>