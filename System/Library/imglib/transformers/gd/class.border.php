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
 * @package     imglib
 * @version     3.0.0 Beta
 * @category    Transform
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        class.border.php
 */
class ImageTransformationBorder
{

    public static function transform( $gd, $params )
    {

        if ( !isset( $params[ 'advanced' ] ) || $params[ 'advanced' ] == false )
        {
            $borders             = array();
            $borders[ 'top' ]    = $borders[ 'right' ]  = $borders[ 'bottom' ] = $borders[ 'left' ]   = array( 'width' => $params[ 'width' ], 'color' => $params[ 'color' ] );
        }
        else
        {
            $borders             = array();
            $borders[ 'top' ]    = array( 'width' => $params[ 'top' ][ 'width' ], 'color' => $params[ 'top' ][ 'color' ] );
            $borders[ 'right' ]  = array( 'width' => $params[ 'right' ][ 'width' ], 'color' => $params[ 'right' ][ 'color' ] );
            $borders[ 'bottom' ] = array( 'width' => $params[ 'bottom' ][ 'width' ], 'color' => $params[ 'bottom' ][ 'color' ] );
            $borders[ 'left' ]   = array( 'width' => $params[ 'left' ][ 'width' ], 'color' => $params[ 'left' ][ 'color' ] );
        }



        $w = imagesx( $gd );
        $h = imagesy( $gd );

        if ( isset( $params[ "enlarge" ] ) )
        {
            $new_width  = $w + $borders[ 'right' ][ 'width' ] + $borders[ 'left' ][ 'width' ];
            $new_height = $h + $borders[ 'top' ][ 'width' ] + $borders[ 'bottom' ][ 'width' ];
            $new        = ImageTools::getNewImage( $new_width, $new_height );
            imagealphablending( $new, false );
            imagesavealpha( $new, true );
            imagecopyresampled( $new, $gd, $borders[ 'left' ][ 'width' ], $borders[ 'top' ][ 'width' ], 0, 0, imagesx( $gd ), imagesy( $gd ), imagesx( $gd ), imagesy( $gd ) );
            imagedestroy( $gd );
            $gd         = $new;
            $w          = $new_width;
            $h          = $new_height;
        }

        // add top border
        $border = $borders[ 'top' ];
        list($r, $g, $b) = ImageTools::htmlcolor2rgb( $border[ "color" ] );
        $col    = imagecolorallocate( $gd, $r, $g, $b );
        $x1     = 0;
        $y1     = 0;
        $x2     = $w;
        $y2     = 0;
        for ( $i = 0; $i < $border[ 'width' ]; $i++ )
        {
            imageline( $gd, $x1, $y1, $x2, $y2, $col );
            $x1 += 1;
            $y1 += 1;
            $x2 -= 1;
            $y2 += 1;
        }

        // add right border
        $border = $borders[ 'right' ];
        list($r, $g, $b) = ImageTools::htmlcolor2rgb( $border[ "color" ] );
        $col    = imagecolorallocate( $gd, $r, $g, $b );
        $x1     = $w - 1;
        $y1     = 0;
        $x2     = $w - 1;
        $y2     = $h;
        for ( $i = 0; $i < $border[ 'width' ]; $i++ )
        {
            imageline( $gd, $x1, $y1, $x2, $y2, $col );
            $x1 -= 1;
            $y1 += 1;
            $x2 -= 1;
            $y2 -= 1;
        }

        // add bottom border
        $border = $borders[ 'bottom' ];
        list($r, $g, $b) = ImageTools::htmlcolor2rgb( $border[ "color" ] );
        $col    = imagecolorallocate( $gd, $r, $g, $b );
        $x1     = 0;
        $y1     = $h - 1;
        $x2     = $w;
        $y2     = $h - 1;
        for ( $i = 0; $i < $border[ 'width' ]; $i++ )
        {
            imageline( $gd, $x1, $y1, $x2, $y2, $col );
            $x1 += 1;
            $y1 -= 1;
            $x2 -= 1;
            $y2 -= 1;
        }

        // add left border
        $border = $borders[ 'left' ];
        list($r, $g, $b) = ImageTools::htmlcolor2rgb( $border[ "color" ] );
        $col    = imagecolorallocate( $gd, $r, $g, $b );
        $x1     = 0;
        $y1     = 0;
        $x2     = 0;
        $y2     = $h - 1;
        for ( $i = 0; $i < $border[ 'width' ]; $i++ )
        {
            imageline( $gd, $x1, $y1, $x2, $y2, $col );
            $x1 += 1;
            $y1 += 1;
            $x2 += 1;
            $y2 -= 1;
        }

        return $gd;
    }
}

?>