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
 * @file        RichtextField.php
 */
class Field_RichtextField extends Field_BaseField
{

    /**
     * @return array
     */
    static function getAttributes()
    {
        return array(
                'label',
                'cols',
                'rows',
                'style',
                'class',
                'controls',
                'value',
                'toolbar',
                'multiple' );
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
                'height'     => isset($field[ 'height' ]) ? $field[ 'height' ] : null,
                'minheight'  => isset($field[ 'minheight' ]) ? $field[ 'minheight' ] : null,
                'type'       => $field[ 'type' ],
                'label'      => (!empty( $field[ 'label' ] ) ? $field[ 'label' ] : null),
                'grouplabel' => (!empty( $field[ 'grouplabel' ] ) ? $field[ 'grouplabel' ] : null),
                'name'       => $field[ 'id' ],
                'id'         => $field[ 'id' ],
                'fieldid'    => $field[ 'fieldid' ],
                'value'      => (!empty( $field[ 'value' ] ) ? $field[ 'value' ] : '' ),
                'maxlength'  => (!empty( $field[ 'maxlength' ] ) ? $field[ 'maxlength' ] : null),
                'cols'       => (!empty( $field[ 'cols' ] ) ? $field[ 'cols' ] : null),
                'rows'       => (!empty( $field[ 'rows' ] ) ? $field[ 'rows' ] : null),
                'style'      => (!empty( $field[ 'style' ] ) ? $field[ 'style' ] : null),
                'class'      => (!empty( $field[ 'class' ] ) ? $field[ 'class' ] : null),
                'toolbar'    => (!empty( $field[ 'toolbar' ] ) ? $field[ 'toolbar' ] : null),
                'toolbarpos' => (!empty( $field[ 'toolbarpos' ] ) ? $field[ 'toolbarpos' ] : 'external'),
                'multiple'   => (!empty( $field[ 'multiple' ] ) ? $field[ 'multiple' ] : null),
                'controls'   => (!empty( $field[ 'controls' ] ) && $field[ 'controls' ] == 1 ? true : false),
        );
        if ( !empty( $field[ 'description' ] ) )
        {
            $data[ 'tip' ] = 'custom::' . $field[ 'id' ];
        }
        if ( !empty( $field[ 'tip' ] ) )
        {
            $data[ 'tip' ] = $field[ 'tip' ];
        }
        if ( !empty( $field[ 'style' ] ) )
        {
            $data[ 'style' ] = $field[ 'style' ];
        }
        if ( !is_null( $value ) )
        {
            # $data['value'] = Library::encode($value);
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
     * @param $field
     * @return mixed
     */
    public static function _renderField( $field )
    {
        $value     = !empty( $field[ 'value' ] ) ? $field[ 'value' ] : '';
        $name      = $field[ 'name' ];
        $width     = !empty( $field[ 'width' ] ) && intval( $field[ 'width' ] ) ? $field[ 'width' ] : null;
        $height    = !empty( $field[ 'height' ] ) && intval( $field[ 'height' ] ) ? $field[ 'height' ] : null;
        $minheight = !empty( $field[ 'minheight' ] ) && intval( $field[ 'minheight' ] ) ? $field[ 'minheight' ] : null;

        $rows = !empty( $field[ 'rows' ] ) && intval( $field[ 'rows' ] ) ? $field[ 'rows' ] : 10;
        $cols = !empty( $field[ 'cols' ] ) && intval( $field[ 'cols' ] ) ? $field[ 'cols' ] : 60;


        if ( !function_exists( 'InitEditor' ) )
        {
            User::getPersonalSettings();
            $wysiwyg = $GLOBALS[ 'personal_settings' ][ 'wysiwyg' ];
            if ( file_exists( VENDOR_PATH . $wysiwyg . '/' . $wysiwyg . '_php5.php' ) )
            {
                require_once(VENDOR_PATH . $wysiwyg . '/' . $wysiwyg . '_php5.php');
            }
        }
        
        
        if ( $height !== null && $height<$minheight)
        {
            $height = $minheight;
        }

        return Tinymce::getTextarea( $value, $name, ($width ? $width : '100%' ), ($height ? $height : "300" ), $cols, $rows, $field[ 'toolbar' ], $field[ 'toolbarpos' ] );

        InitEditor( Settings::get( 'portalurl' ) );

        return EditorArea( $value, $name, ($width ? $width : '100%' ), ($height ? $height : "300" ), $cols, $rows, $field[ 'toolbar' ], $field[ 'toolbarpos' ] );
    }
}

?>