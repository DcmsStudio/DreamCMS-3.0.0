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
 * @file        TagsField.php
 */
class Field_TagsField extends Field_BaseField
{

    /**
     * @return array
     */
    static function getAttributes()
    {
        return array( 'label', 'style', 'class', 'value' );
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

        $data = array(
                'type'       => $field[ 'type' ],
                'label'      => (!empty( $field[ 'label' ] ) ? $field[ 'label' ] : ''),
                'grouplabel' => (!empty( $field[ 'grouplabel' ] ) ? $field[ 'grouplabel' ] : null),
                'name'       => $field[ 'id' ],
                'id'         => $field[ 'id' ],
                'fieldid'    => $field[ 'fieldid' ],
                'style'      => (!empty( $field[ 'style' ] ) ? $field[ 'style' ] : null),
                'class'      => (!empty( $field[ 'class' ] ) ? $field[ 'class' ] : null),
                'value'      => $value, // ids of tags
                'tags'       => (is_array( $field[ 'tags' ] ) ? $field[ 'tags' ] : null),
        );
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
     * @return mixed
     */
    public static function _renderField( $field )
    {
        $data[ 'tagname' ]               = 'input';
        $data[ 'attributes' ][ 'name' ]  = 'tag_' . $field[ 'name' ];
        $data[ 'attributes' ][ 'id' ]    = 'tag_' . $field[ 'id' ];
        $data[ 'attributes' ][ 'type' ]  = 'text';
        $data[ 'attributes' ][ 'value' ] = '';

        if ( !empty( $field[ 'size' ] ) )
        {
            $data[ 'attributes' ][ 'size' ] = $field[ 'size' ];
        }
        else
        {
            $data[ 'attributes' ][ 'size' ] = 60;
        }
        if ( !empty( $field[ 'maxlength' ] ) )
        {
            $data[ 'attributes' ][ 'maxlength' ] = $field[ 'maxlength' ];
        }
        if ( !empty( $field[ 'style' ] ) )
        {
            $data[ 'attributes' ][ 'style' ] = $field[ 'style' ];
        }
        if ( !empty( $field[ 'class' ] ) )
        {
            $data[ 'attributes' ][ 'class' ] = $field[ 'class' ];
        }

        if ( !empty( $field[ 'controls' ] ) )
        {
            $data[ 'attributes' ][ 'class' ] .= ( $data[ 'attributes' ][ 'class' ] ? ' ' : '') . 'required';
        }

        if ( !empty( $field[ 'iscore' ] ) )
        {
            $data[ 'attributes' ][ 'class' ] .= ( $data[ 'attributes' ][ 'class' ] ? ' ' : '') . 'disabled';
        }

        $data[ 'attributes' ][ 'class' ] .= ( $data[ 'attributes' ][ 'class' ] ? ' ' : '') . 'content-tags';

        $hiddenField = Html::createTag(
                        array( 'tagname'    => 'input',
                                'attributes' => array(
                                        'type'  => 'hidden',
                                        'name'  => $field[ 'name' ],
                                        'id'    => $field[ 'name' ],
                                        'value' => $field[ 'value' ]
                                )
                ) );

        $inputField = Html::createTag( $data );

        $data                  = array();
        $data[ 'tag_fields' ]  = $hiddenField . $inputField;
        $data[ 'contenttags' ] = $field[ 'tags' ];
        $ret                   = View::out( 'generic/contenttags', $data );



        return $ret;
    }
}

?>