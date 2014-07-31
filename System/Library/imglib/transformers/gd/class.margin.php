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
 * @file        class.margin.php
 */
class ImageTransformationMargin
{

    public static function transform( $gd, $params )
    {
        $left   = !empty( $params[ 'left' ] ) ? $params[ 'left' ] : 0;
        $right  = !empty( $params[ 'right' ] ) ? $params[ 'right' ] : 0;
        $top    = !empty( $params[ 'top' ] ) ? $params[ 'top' ] : 0;
        $bottom = !empty( $params[ 'bottom' ] ) ? $params[ 'bottom' ] : 0;

        if ( isset( $params[ 'global' ] ) && !empty( $params[ 'global' ] ) )
        {
            $top    = $right  = $bottom = $left   = ( int ) $params[ 'global' ];
        }

        $new = ImageTools::getNewImage( imagesx( $gd ) + $left + $right, imagesy( $gd ) + $top + $bottom );
        imagealphablending( $new, false );
        imagesavealpha( $new, true );

        if ( isset( $params[ 'transparent' ] ) && $params[ 'transparent' ] == 1 )
        {
            $bgcol = imagecolorallocatealpha( $new, 0, 0, 0, 127 );
        }
        elseif ( isset( $params[ 'bgcolor' ] ) && !empty( $params[ 'bgcolor' ] ) )
        {
            $col   = ImageTools::htmlcolor2rgb( $params[ 'bgcolor' ] );
            $bgcol = imagecolorallocate( $new, $col[ 0 ], $col[ 1 ], $col[ 2 ] );
        }
        else
        {
            // make background color equal bottom-right pixel
            $rgb   = imagecolorat( $gd, imagesx( $gd ) - 1, imagesy( $gd ) - 1 );
            if ( imageistruecolor( $gd ) )
                $bgcol = imagecolorallocate( $new, ($rgb >> 16) & 0xFF, ($rgb >> 8) & 0xFF, $rgb & 0xFF );
            else
            {
                $col   = imagecolorsforindex( $gd, $rgb );
                $bgcol = imagecolorallocate( $new, $col[ "red" ], $col[ "green" ], $col[ "blue" ] );
            }
        }
        imagefill( $new, 0, 0, $bgcol );
        imagecopyresampled( $new, $gd, $left, $top, 0, 0, imagesx( $gd ), imagesy( $gd ), imagesx( $gd ), imagesy( $gd ) );
        imagedestroy( $gd );
        return $new;
    }
}

?>
