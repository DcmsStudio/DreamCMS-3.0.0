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
 * @file        BaseField.php
 */
class Field_BaseField
{

    /**
     * @var array
     */
    static $defaultItemTblFields = array(
            'itemid', 'appid', 'itemtype', 'catid', 'title', 'content', 'author',
            'modify_by', 'createdate', 'modifydate', 'hits', 'publish_on', 'publish_off',
            'published', 'access', 'images', 'params', 'escapecoretags', 'lang'
    );

    /**
     *
     * 
     */
    public static function validateFieldName( $field )
    {
        if ( in_array( $field[ 'fieldname' ], self::$defaultItemTblFields ) && empty( $field[ 'iscore' ] ) )
        {
            return true;
        }

        return false;
    }

    /**
     * @param $field
     */
    static public function getFieldDefinition( $field )
    {
        Error::raise( 'Field definition for `' . $field[ 'type' ] . '` does not have it\'s own getField method.' );
    }

    /**
     * @param $field
     * @return array
     */
    static public function parseOptions( $field )
    {
        // work out the options
        $raw_options = explode( "\n", $field[ 'values' ] );
        $options     = array();
        foreach ( $raw_options as $option )
        {
            if ( Library::length( $option ) > 0 )
            {
                $parts                  = explode( '|', $option );
                $options[ $parts[ 0 ] ] = array(
                        'value'   => $parts[ 0 ],
                        'label'   => (isset( $parts[ 1 ] ) ? $parts[ 1 ] : $parts[ 0 ] ),
                        'checked' => (isset( $parts[ 2 ] ) ? $parts[ 2 ] : 0 )
                );
            }
        }

        // work out what has been selected (for existing fields)
        if ( isset( $field[ 'value' ] ) )
        {
            $values = explode( ',', $field[ 'value' ] );
            foreach ( $options as $key => $option )
            {
                if ( in_array( $key, $values ) )
                {
                    $options[ $key ][ 'checked' ] = 1;
                }
                else
                {
                    $options[ $key ][ 'checked' ] = 0;
                }
            }
        }
        return $options;
    }
}

?>