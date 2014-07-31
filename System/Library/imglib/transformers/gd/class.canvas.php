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
 * @file        class.canvas.php
 */
class ImageTransformationCanvas
{

    public static function getParameters()
    {
        return array(
                'width'       => array(
                        'required'    => true,
                        'type'        => 'int',
                        'description' => 'The width of the image'
                ),
                'height'      => array(
                        'required'    => true,
                        'type'        => 'int',
                        'description' => 'The height of the image'
                ),
                'transparent' => array(
                        'required'    => false,
                        'type'        => 'boolean',
                        'description' => 'Makes the empty part of the canvas transparent'
                ),
                'bgcolor'     => array(
                        'required'    => false,
                        'type'        => 'color',
                        'description' => 'Colour to fill the empty part of the canvas with (auto or html notation)'
                ),
                'align'       => array(
                        'required'    => false,
                        'type'        => 'position',
                        'description' => 'The horizontal alignment of the original image on the canvas'
                ),
                'valign'      => array(
                        'required'    => false,
                        'type'        => 'position',
                        'description' => 'The vertical alignment of the original image on the canvas'
                ),
        );
    }

    public static function transform( $gd, $params )
    {
        $new = ImageTools::getNewImage( $params[ 'width' ], $params[ 'height' ] );
        imagealphablending( $new, false );
        imagesavealpha( $new, true );

        if ( isset( $params[ 'transparent' ] ) && $params[ 'transparent' ] == 1 )
        {
            $bgcol = imagecolorallocatealpha( $new, 0, 0, 0, 127 );
        }
        elseif ( isset( $params[ 'bgcolor' ] ) && $params[ 'bgcolor' ] == 'auto' )
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
        elseif ( isset( $params[ 'bgcolor' ] ) && !empty( $params[ 'bgcolor' ] ) )
        {
            $col   = ImageTools::htmlcolor2rgb( $params[ 'bgcolor' ] );
            $bgcol = imagecolorallocate( $new, $col[ 0 ], $col[ 1 ], $col[ 2 ] );
        }

        imagefill( $new, 0, 0, $bgcol );

        if ( !isset( $params[ 'valign' ] ) )
            $params[ 'valign' ] = "middle";
        if ( !isset( $params[ 'align' ] ) )
            $params[ 'align' ]  = "center";

        if ( $params[ 'align' ] == "center" )
            $x = ( int ) ($params[ 'width' ] / 2 - imagesx( $gd ) / 2);
        else if ( $params[ 'align' ] == "right" )
            $x = $params[ 'width' ] - imagesx( $gd );
        else
            $x = 0;

        if ( $params[ 'valign' ] == "middle" )
            $y = ( int ) ($params[ 'height' ] / 2 - imagesy( $gd ) / 2);
        else if ( $params[ 'valign' ] == "bottom" )
            $y = $height - imagesy( $gd );
        else
            $y = 0;

        imagecopyresampled( $new, $gd, $x, $y, 0, 0, imagesx( $gd ), imagesy( $gd ), imagesx( $gd ), imagesy( $gd ) );
        imagedestroy( $gd );
        return $new;
    }
}

?>
