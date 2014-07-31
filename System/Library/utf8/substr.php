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
 * @file        substr.php
 */
defined( 'ROOT_PATH' ) or die( 'No direct script access.' );

/**
 * UTF8::substr
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2011 Kohana Team
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _substr( $str, $offset, $length = NULL )
{
    if ( UTF8::is_ascii( $str ) )
        return ($length === NULL) ? substr( $str, $offset ) : substr( $str, $offset, $length );

    // Normalize params
    $str    = ( string ) $str;
    $strlen = UTF8::strlen( $str );
    $offset = ( int ) ($offset < 0) ? max( 0, $strlen + $offset ) : $offset; // Normalize to positive offset
    $length = ($length === NULL) ? NULL : ( int ) $length;

    // Impossible
    if ( $length === 0 OR $offset >= $strlen OR ($length < 0 AND $length <= $offset - $strlen) )
        return '';

    // Whole string
    if ( $offset == 0 AND ($length === NULL OR $length >= $strlen) )
        return $str;

    // Build regex
    $regex = '^';

    // Create an offset expression
    if ( $offset > 0 )
    {
        // PCRE repeating quantifiers must be less than 65536, so repeat when necessary
        $x = ( int ) ($offset / 65535);
        $y = ( int ) ($offset % 65535);
        $regex .= ($x == 0) ? '' : ('(?:.{65535}){' . $x . '}');
        $regex .= ($y == 0) ? '' : ('.{' . $y . '}');
    }

    // Create a length expression
    if ( $length === NULL )
    {
        $regex .= '(.*)'; // No length set, grab it all
    }
    // Find length from the left (positive length)
    elseif ( $length > 0 )
    {
        // Reduce length so that it can't go beyond the end of the string
        $length = min( $strlen - $offset, $length );

        $x = ( int ) ($length / 65535);
        $y = ( int ) ($length % 65535);
        $regex .= '(';
        $regex .= ($x == 0) ? '' : ('(?:.{65535}){' . $x . '}');
        $regex .= '.{' . $y . '})';
    }
    // Find length from the right (negative length)
    else
    {
        $x = ( int ) (-$length / 65535);
        $y = ( int ) (-$length % 65535);
        $regex .= '(.*)';
        $regex .= ($x == 0) ? '' : ('(?:.{65535}){' . $x . '}');
        $regex .= '.{' . $y . '}';
    }

    preg_match( '/' . $regex . '/us', $str, $matches );
    return $matches[ 1 ];
}
