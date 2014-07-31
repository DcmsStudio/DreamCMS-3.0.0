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
 * @file        CheckboxField.php
 */
class Field_CheckboxField extends Field_BaseField
{

    /**
     * @return array
     */
    static function getAttributes()
    {
        return array( 'label', 'grouplabel', 'value', 'checked' );
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

        $values = Field_BaseField::parseOptions( $field );

        $default_value = (isset( $field[ 'form_field_default' ] ) ? $field[ 'form_field_default' ] : null);
        $data          = array(
                'require'        => $field[ 'require' ],
                'type'           => $field[ 'type' ],
                'label'          => (!empty( $field[ 'label' ] ) ? $field[ 'label' ] : ''),
                'grouplabel'     => (!empty( $field[ 'grouplabel' ] ) ? $field[ 'grouplabel' ] : null),
                'name'           => $field[ 'id' ],
                'id'             => $field[ 'id' ],
                'fieldid'        => $field[ 'fieldid' ],
                'value'          => $field[ 'value' ],
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

        if ( $value == $field[ 'value' ] && $value !== null )
        {
            $data[ 'checked' ] = true;
        }
        elseif ( $default_value == $field[ 'value' ] && $default_value != null && $value == null )
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

                $data              = array();
                $data[ 'tagname' ] = 'input';

                $data[ 'attributes' ][ 'name' ]  = $fields[ 'name' ]; // . '[]';
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


                $tags .= '<label for="' . $data[ 'attributes' ][ 'id' ] . '">';
                $tags .= Html::createTag( $data );
                $tags .= $field[ 'label' ];
                $tags .= '</label> &nbsp; ';
                $tags .= "\r\n";
            }
        }



        return $tags;
    }
}

?>