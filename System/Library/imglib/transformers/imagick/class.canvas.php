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

    public static function getTitle()
    {
        return trans( 'Bild Gräße ändern' );
    }

    public static function getDescription()
    {
        return trans( 'Ändert die Größe des Bildes.' );
    }

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

    public static function transform( $img, $params, $trans )
    {

        // work out the background colour
        if ( (isset( $params[ 'transparent' ] ) && $params[ 'transparent' ] == 1) || !isset( $params[ 'bgcolor' ] ) )
        {
            $bgcolor = 'none';
        }
        elseif ( isset( $params[ 'bgcolor' ] ) && $params[ 'bgcolor' ] == 'auto' )
        {
            $bgcolor = $img->getImagePixelColor( $img->getImageWidth(), $img->getImageHeight() );
        }
        elseif ( isset( $params[ 'bgcolor' ] ) )
        {
            $rgb     = ImageTools::htmlcolor2rgb( $params[ 'bgcolor' ] );
            $bgcolor = "rgb({$rgb[ 0 ]}, {$rgb[ 1 ]}, {$rgb[ 2 ]})";
        }

        if ( $bgcolor == 'none' && $trans->getOutputType() == 'jpeg' )
        {
            $rgb     = ImageTools::htmlcolor2rgb( '#ffffff' );
            $bgcolor = "rgb({$rgb[ 0 ]}, {$rgb[ 1 ]}, {$rgb[ 2 ]})";
        }

        // work out the position of the original image on the canvas
        $params[ 'align' ]  = isset( $params[ 'align' ] ) ? $params[ 'align' ] : 'center';
        $params[ 'valign' ] = isset( $params[ 'valign' ] ) ? $params[ 'valign' ] : 'middle';

        $x = $y = 0;

        if ( $params[ 'align' ] == 'center' )
        {
            $x = ($params[ 'width' ] / 2) - ($img->getImageWidth() / 2);
        }
        elseif ( $params[ 'align' ] == 'right' )
        {
            $x = ($params[ 'width' ]) - ($img->getImageWidth());
        }

        if ( $params[ 'valign' ] == 'middle' )
        {
            $y = ($params[ 'height' ] / 2) - ($img->getImageHeight() / 2);
        }
        elseif ( $params[ 'valign' ] == 'bottom' )
        {
            $y = ($params[ 'height' ]) - ($img->getImageHeight());
        }

        $canvas = new Imagick();
        $canvas->newImage( $params[ 'width' ], $params[ 'height' ], $bgcolor, 'png' );
        $canvas->compositeImage( $img, Imagick::COMPOSITE_SRCOVER, $x, $y );

        return $canvas;
    }
}

?>