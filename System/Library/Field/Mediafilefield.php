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
 * @file        Mediafilefield.php
 */
class Field_MediafileField extends Field_BaseField
{

    /**
     * @return array
     */
    static function getAttributes()
    {
        return array( 'label', 'size', 'style', 'class', 'controls', 'value' );
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
                'type'       => $field[ 'type' ],
                'label'      => (!empty( $field[ 'label' ] ) ? $field[ 'label' ] : ''),
                'grouplabel' => (!empty( $field[ 'grouplabel' ] ) ? $field[ 'grouplabel' ] : null),
                'name'       => $field[ 'id' ],
                'id'         => $field[ 'id' ],
                'fieldid'    => $field[ 'fieldid' ],
                'size'       => (!empty( $field[ 'size' ] ) ? $field[ 'size' ] : 70),
                'style'      => (!empty( $field[ 'style' ] ) ? $field[ 'style' ] : null),
                'class'      => (!empty( $field[ 'class' ] ) ? $field[ 'class' ] : null),
                'controls'   => (!empty( $field[ 'controls' ] ) ? $field[ 'controls' ] : null),
                'value'      => (!empty( $field[ 'value' ] ) ? $field[ 'value' ] : '' )
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
     * @return string
     */
    public static function renderField( $field )
    {
        $value     = !empty( $field[ 'value' ] ) ? $field[ 'value' ] : NULL;
        $loadchain = !empty( $field[ 'chain' ] ) ? $field[ 'chain' ] : 'AuthorImageLarge';

        if ( !$value )
        {
            return '';
        }

        $_filename = $value;
        if ( Library::canGraphic( PAGE_PATH . $_filename ) )
        {

            if ( Library::isValidGraphic( PAGE_PATH . $_filename ) )
            {
                $imgchain = Library::getImageChain( $loadchain );

                $data = array(
                        'source' => PAGE_PATH . $_filename,
                        'output' => 'jpeg',
                        'chain'  => $imgchain
                );

                $img               = ImageTools::create( IMAGE_CACHE . 'apps' );
                $data              = $img->process( $data );
                $im[ 'cachefile' ] = $data[ 'path' ];
                $valid             = true;
                return '<img src="' . $data[ 'path' ] . '" width="' . $width . '" height="' . $height . '" alt="" />';
            }
        }

        return '';
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
            $data[ 'attributes' ][ 'size' ] = 70;
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
        if ( !empty( $field[ 'iscore' ] ) )
        {
            $data[ 'attributes' ][ 'class' ] .= ( $data[ 'attributes' ][ 'class' ] ? ' ' : '') . ' disabled';
        }

        $tag = Html::createTag( $data );

        $data                              = array();
        $data[ 'tagname' ]                 = 'input';
        $data[ 'attributes' ][ 'type' ]    = 'button';
        $data[ 'attributes' ][ 'class' ]   = 'action-button';
        $data[ 'attributes' ][ 'value' ]   = trans( 'Browse Files' );
        $data[ 'attributes' ][ 'onclick' ] = "openImageBrowser('/', $('#{$field[ 'id' ]}') , true);";


        return $tag . ' ' . Html::createTag( $data );
    }
}

?>