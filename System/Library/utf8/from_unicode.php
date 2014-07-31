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
 * @package     utf8
 * @version     3.0.0 Beta
 * @category    UTF-8 Tools
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        from_unicode.php
 */
function _from_unicode( $arr )
{
    ob_start();

    $keys = array_keys( $arr );

    foreach ( $keys as $k )
    {
        // ASCII range (including control chars)
        if ( ($arr[ $k ] >= 0) AND ($arr[ $k ] <= 0x007f) )
        {
            echo chr( $arr[ $k ] );
        }
        // 2 byte sequence
        elseif ( $arr[ $k ] <= 0x07ff )
        {
            echo chr( 0xc0 | ($arr[ $k ] >> 6) );
            echo chr( 0x80 | ($arr[ $k ] & 0x003f) );
        }
        // Byte order mark (skip)
        elseif ( $arr[ $k ] == 0xFEFF )
        {
            // nop -- zap the BOM
        }
        // Test for illegal surrogates
        elseif ( $arr[ $k ] >= 0xD800 AND $arr[ $k ] <= 0xDFFF )
        {
            // Found a surrogate
            throw new UTF8_Exception( "UTF8::from_unicode: Illegal surrogate at index: ':index', value: ':value'", array(
            ':index' => $k,
            ':value' => $arr[ $k ],
            ) );
        }
        // 3 byte sequence
        elseif ( $arr[ $k ] <= 0xffff )
        {
            echo chr( 0xe0 | ($arr[ $k ] >> 12) );
            echo chr( 0x80 | (($arr[ $k ] >> 6) & 0x003f) );
            echo chr( 0x80 | ($arr[ $k ] & 0x003f) );
        }
        // 4 byte sequence
        elseif ( $arr[ $k ] <= 0x10ffff )
        {
            echo chr( 0xf0 | ($arr[ $k ] >> 18) );
            echo chr( 0x80 | (($arr[ $k ] >> 12) & 0x3f) );
            echo chr( 0x80 | (($arr[ $k ] >> 6) & 0x3f) );
            echo chr( 0x80 | ($arr[ $k ] & 0x3f) );
        }
        // Out of range
        else
        {
            throw new UTF8_Exception( "UTF8::from_unicode: Codepoint out of Unicode range at index: ':index', value: ':value'", array(
            ':index' => $k,
            ':value' => $arr[ $k ],
            ) );
        }
    }

    $result = ob_get_contents();
    ob_end_clean();
    return $result;
}
