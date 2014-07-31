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
 * @file        SelectField.php
 */
class Field_SelectField extends Field_BaseField
{

    /**
     * @return array
     */
    static function getAttributes()
    {
        return array( 'label', 'style', 'class', 'values', 'value' );
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
                'label'          => (!empty( $field[ 'label' ] ) ? $field[ 'label' ] : ''),
                'grouplabel'     => (!empty( $field[ 'grouplabel' ] ) ? $field[ 'grouplabel' ] : null),
                'name'           => $field[ 'id' ],
                'id'             => $field[ 'id' ],
                'fieldid'        => $field[ 'fieldid' ],
                'style'          => (!empty( $field[ 'style' ] ) ? $field[ 'style' ] : null),
                'class'          => (!empty( $field[ 'class' ] ) ? $field[ 'class' ] : null),
                'values'         => $field[ 'values' ],
                'parsed_options' => $values
        );


        $data[ 'checked' ] = false;

        if ( $value == $field[ 'value' ] || ($default_value == $field[ 'value' ] && $value === NULL) )
        {
            $data[ 'checked' ] = true;
        }

        if ( !empty( $field[ 'description' ] ) )
        {
            $data[ 'tip' ] = 'custom::' . $field[ 'id' ];
        }
        if ( !empty( $field[ 'tip' ] ) )
        {
            $data[ 'tip' ] = $field[ 'tip' ];
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


        $data[ 'tagname' ]              = 'select';
        $data[ 'attributes' ][ 'name' ] = $fields[ 'name' ];
        $data[ 'attributes' ][ 'id' ]   = $fields[ 'id' ];

        if ( !empty( $fields[ 'size' ] ) )
        {
            $data[ 'attributes' ][ 'size' ] = $fields[ 'size' ];
        }
        if ( !empty( $fields[ 'style' ] ) )
        {
            $data[ 'attributes' ][ 'style' ] = $fields[ 'style' ];
        }
        if ( !empty( $fields[ 'class' ] ) )
        {
            $data[ 'attributes' ][ 'class' ] = $fields[ 'class' ];
        }
        if ( !empty( $fields[ 'iscore' ] ) )
        {
            $data[ 'attributes' ][ 'class' ] .= ( $data[ 'attributes' ][ 'class' ] ? ' ' : '') . ' disabled';
        }
        $tags .= Html::createTag( $data );



        if ( isset( $fields[ 'parsed_options' ] ) && count( $fields[ 'parsed_options' ] ) )
        {
            $selectedIndex = 0;
            
            foreach ( $fields[ 'parsed_options' ] as $idx => $field )
            {
                if ( $field[ 'checked' ] )
                {
                    $selectedIndex = $idx;
                }
            }

            
            


            foreach ( $fields[ 'parsed_options' ] as $idx => $field )
            {
                $data                            = array();
                $data[ 'tagname' ]               = 'option';
                $data[ 'attributes' ][ 'value' ] = $field[ 'value' ];

                if ( $field[ 'value' ] == $fields[ 'value' ] )
                {
                    $data[ 'attributes' ][ 'selected' ] = 'selected';
                }

                $tags .= Html::createTag( $data );
                $tags .= $field[ 'label' ];
                $tags .= '</option>';
            }
        }

        $tags .= '</select>';
        
       // print_r($fields); die($tags); exit;
        
        
        return $tags;
    }
}

?>