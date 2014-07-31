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
 * @file        ord.php
 */
defined( 'ROOT_PATH' ) or die( 'No direct script access.' );

/**
 * UTF8::ord
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2011 Kohana Team
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _ord( $chr )
{
    $ord0 = ord( $chr );

    if ( $ord0 >= 0 AND $ord0 <= 127 )
        return $ord0;

    if ( !isset( $chr[ 1 ] ) )
    {
        throw new UTF8_Exception( 'Short sequence - at least 2 bytes expected, only 1 seen' );
    }

    $ord1 = ord( $chr[ 1 ] );

    if ( $ord0 >= 192 AND $ord0 <= 223 )
        return ($ord0 - 192) * 64 + ($ord1 - 128);

    if ( !isset( $chr[ 2 ] ) )
    {
        throw new UTF8_Exception( 'Short sequence - at least 3 bytes expected, only 2 seen' );
    }

    $ord2 = ord( $chr[ 2 ] );

    if ( $ord0 >= 224 AND $ord0 <= 239 )
        return ($ord0 - 224) * 4096 + ($ord1 - 128) * 64 + ($ord2 - 128);

    if ( !isset( $chr[ 3 ] ) )
    {
        throw new UTF8_Exception( 'Short sequence - at least 4 bytes expected, only 3 seen' );
    }

    $ord3 = ord( $chr[ 3 ] );

    if ( $ord0 >= 240 AND $ord0 <= 247 )
        return ($ord0 - 240) * 262144 + ($ord1 - 128) * 4096 + ($ord2 - 128) * 64 + ($ord3 - 128);

    if ( !isset( $chr[ 4 ] ) )
    {
        throw new UTF8_Exception( 'Short sequence - at least 5 bytes expected, only 4 seen' );
    }

    $ord4 = ord( $chr[ 4 ] );

    if ( $ord0 >= 248 AND $ord0 <= 251 )
        return ($ord0 - 248) * 16777216 + ($ord1 - 128) * 262144 + ($ord2 - 128) * 4096 + ($ord3 - 128) * 64 + ($ord4 - 128);

    if ( !isset( $chr[ 5 ] ) )
    {
        throw new UTF8_Exception( 'Short sequence - at least 6 bytes expected, only 5 seen' );
    }

    if ( $ord0 >= 252 AND $ord0 <= 253 )
        return ($ord0 - 252) * 1073741824 + ($ord1 - 128) * 16777216 + ($ord2 - 128) * 262144 + ($ord3 - 128) * 4096 + ($ord4 - 128) * 64 + (ord( $chr[ 5 ] ) - 128);

    if ( $ord0 >= 254 AND $ord0 <= 255 )
    {
        throw new UTF8_Exception( "Invalid UTF-8 with surrogate ordinal ':ordinal'", array(
        ':ordinal' => $ord0,
        ) );
    }
}
