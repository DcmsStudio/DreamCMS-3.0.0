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
 * @file        Pageidentifierfield.php
 */
class Field_PageidentifierField extends Field_BaseField
{

    /**
     * @var array
     */
    private static $suffixes = array( 'html', 'htm', 'xhtml', 'dcms', 'php', 'txt', 'xml' );

    /**
     * @return array
     */
    static function getAttributes()
    {
        return array( 'label', 'style', 'class', 'value', 'maxlength' );
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

        $field[ 'name' ] = $field[ 'id' ]   = 'alias';

        $data = array(
                'type'       => $field[ 'type' ],
                'label'      => (!empty( $field[ 'label' ] ) ? $field[ 'label' ] : null),
                'grouplabel' => (!empty( $field[ 'grouplabel' ] ) ? $field[ 'grouplabel' ] : null),
                'name'       => $field[ 'id' ],
                'id'         => $field[ 'id' ],
                'maxlength'  => (!empty( $field[ 'maxlength' ] ) ? $field[ 'maxlength' ] : null),
                'fieldid'    => $field[ 'fieldid' ],
                'style'      => (!empty( $field[ 'style' ] ) ? $field[ 'style' ] : null),
                'class'      => (!empty( $field[ 'class' ] ) ? $field[ 'class' ] : null),
                'controls'   => 1,
                'value'      => $value ? $value : (!empty( $field[ 'page_alias' ] ) ? $field[ 'page_alias' ] : '' ),
                'suffix'     => (!empty( $field[ 'page_suffix' ] ) ? $field[ 'page_suffix' ] : '' )
        );

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
        $data[ 'attributes' ][ 'type' ]  = 'text';
        $data[ 'attributes' ][ 'value' ] = $field[ 'value' ];

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
        $data[ 'attributes' ][ 'class' ] .= $data[ 'attributes' ][ 'class' ] ? ' pageident' : 'pageident';

        if ( !empty( $field[ 'controls' ] ) )
        {
            $data[ 'attributes' ][ 'class' ] .= ( $data[ 'attributes' ][ 'class' ] ? ' ' : '') . 'required';
        }
        if ( !empty( $field[ 'iscore' ] ) )
        {
            $data[ 'attributes' ][ 'class' ] .= ( $data[ 'attributes' ][ 'class' ] ? ' ' : '') . ' disabled';
        }

        $page_suffix     = $field[ 'suffix' ];
        $page_identifier = $field[ 'suffix' ];
        $page_alias      = $field[ 'value' ];

        $tag = <<<EOF
        <input name="identifier" value="{$page_identifier}" type="hidden" />
        <input name="oldalias" value="{$page_alias}" type="hidden" />
        <input name="oldsuffix" value="{$page_suffix}" type="hidden" />
EOF;

        $tag .= Html::createTag( $data );

        $data                           = array();
        $data[ 'tagname' ]              = 'select';
        $data[ 'attributes' ][ 'name' ] = 'suffix';
        $data[ 'attributes' ][ 'id' ]   = 'suffix';

        $tag .= ' . ' . Html::createTag( $data );

        foreach ( self::$suffixes as $str )
        {
            $selected = ( $str == $field[ 'suffix' ] ? ' selected="selected"' : '');
            $tag .= '<option value="' . $str . '"' . $selected . '>' . $str . '</option>';
        }

        $tag .= '</select>';




        return $tag;
    }
}

?>