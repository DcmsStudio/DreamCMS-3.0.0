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
 * @file        MultiplecheckboxField.php
 */
class Field_MultiplecheckboxField extends Field_BaseField
{

    /**
     * @return array
     */
    static function getAttributes()
    {
        return array( 'grouplabel', 'values', 'value' );
    }

    /**
     * @param $field
     * @return array
     */
    static public function getFieldDefinition( $field )
    {
        if ( !empty( $field[ 'options' ] ) )
        {
            $field = array_merge( $field, unserialize( $field[ 'options' ] ) );
        }

        $value         = !empty( $field[ 'value' ] ) ? $field[ 'value' ] : NULL;
        $default_value = ($value === NULL && isset( $field[ 'form_field_default' ] ) ? $field[ 'form_field_default' ] : NULL);



        $values = parent::parseOptions( $field );
        $data   = array(
                'type'           => $field[ 'type' ],
                'label'          => (!empty( $field[ 'label' ] ) ? $field[ 'label' ] : 'undefined'),
                'grouplabel'     => (!empty( $field[ 'grouplabel' ] ) ? $field[ 'grouplabel' ] : null),
                'name'           => $field[ 'id' ],
                'id'             => $field[ 'id' ],
                'fieldid'        => $field[ 'fieldid' ],
                'values'         => $field[ 'values' ],
                'parsed_options' => $values
        );
        if ( !empty( $field[ 'description' ] ) )
        {
            $data[ 'tip' ] = 'custom::' . $field[ 'id' ];
        }
        if ( !empty( $field[ 'tip' ] ) )
        {
            $data[ 'tip' ] = $field[ 'tip' ];
        }

        $data[ 'checked' ] = false;

        if ( $value == $field[ 'value' ] || ($default_value == $field[ 'value' ] && $value === NULL) )
        {
            $data[ 'checked' ] = true;
        }

        return $data;
    }

    /**
     * @param $field
     */
    public static function renderField( $field )
    {
        
    }

    /**
     * @param $fields
     * @return string
     */
    public static function _renderField( $fields )
    {
        $tags = '';
        if ( isset( $fields[ 'parsed_options' ] ) && count( $fields[ 'parsed_options' ] ) )
        {
            foreach ( $fields[ 'parsed_options' ] as $idx => $field )
            {
                $data                            = array();
                $data[ 'tagname' ]               = 'input';
                $data[ 'attributes' ][ 'name' ]  = $fields[ 'name' ] . '[]';
                $data[ 'attributes' ][ 'id' ]    = $fields[ 'id' ] . '-' . $idx;
                $data[ 'attributes' ][ 'type' ]  = 'checkbox';
                $data[ 'attributes' ][ 'value' ] = $field[ 'value' ];

                if ( !empty( $field[ 'size' ] ) )
                {
                    $data[ 'attributes' ][ 'size' ] = $field[ 'size' ];
                }
                if ( !empty( $field[ 'style' ] ) )
                {
                    $data[ 'attributes' ][ 'style' ] = $field[ 'style' ];
                }
                if ( !empty( $field[ 'class' ] ) )
                {
                    $data[ 'attributes' ][ 'class' ] = $field[ 'class' ];
                }
                if ( !empty( $field[ 'iscore' ] ) )
                {
                    $data[ 'attributes' ][ 'class' ] .= ( $data[ 'attributes' ][ 'class' ] ? ' ' : '') . ' disabled';
                }

                if ( !empty( $field[ 'checked' ] ) && $field[ 'checked' ] )
                {
                    $data[ 'attributes' ][ 'checked' ] = 'checked';
                }

                $tags .= '<label for="' . $fields[ 'id' ] . '-' . $idx . '" class="multi-cbk">';
                $tags .= Html::createTag( $data );
                $tags .= $field[ 'label' ];
                $tags .= '</label>';
                $tags .= "\r\n";
            }
        }
        return $tags;
    }
}

?>