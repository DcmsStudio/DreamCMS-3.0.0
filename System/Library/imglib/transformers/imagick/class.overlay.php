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
 * @file        class.overlay.php
 */
class ImageTransformationOverlay
{

    public static function getTitle()
    {
        return trans( 'Bild überlappen (Wasserzeichen)' );
    }

    public static function getDescription()
    {
        return trans( 'Überlappt das Bild mit einem anderen Bild.' );
    }

    public static function getParameters()
    {
        return array(
                'align'  => array(
                        'required'    => false,
                        'type'        => 'position',
                        'description' => 'The horizontal position of the overlay (left, center, right)'
                ),
                'valign' => array(
                        'required'    => false,
                        'type'        => 'position',
                        'description' => 'The vertical position of the overlay (top, middle, bottom)'
                ),
                'x'      => array(
                        'required'    => false,
                        'type'        => 'int',
                        'description' => 'The horizontal position of the overlay (in pixels, compliments align).'
                ),
                'y'      => array(
                        'required'    => false,
                        'type'        => 'int',
                        'description' => 'The vertical position of the overlay (in pixels, compliments valign).'
                ),
        );
    }

    public static function transform( $img, $params )
    {
        $overlay = !empty( $params[ 'overlay' ] ) ? ROOT_PATH . $params[ 'overlay' ] : false;
        if ( $overlay === false || !file_exists( $overlay ) )
        {
            return $img;
        }

        //$path = $overlay;
        $overlay = new Imagick( $overlay );
        //$overlay->setBackgroundColor(new ImagickPixel('transparent'));
        //$overlay->readImage($path);




        if ( !isset( $params[ "align" ] ) )
            $params[ "align" ] = "left";
        if ( $params[ "align" ] == "left" )
        {
            $x = 0;
        }
        else if ( $params[ "align" ] == "right" )
        {
            $x = $img->getImageWidth() - $overlay->getImageWidth();
        }
        else
        {
            $x = floor( ($img->getImageWidth() - $overlay->getImageWidth()) / 2 );
        }


        if ( !isset( $params[ "valign" ] ) )
            $params[ "valign" ] = "top";
        if ( $params[ "valign" ] == "top" )
        {
            $y = 0;
        }
        else if ( $params[ "valign" ] == "bottom" )
        {
            $y = $img->getImageHeight() - $overlay->getImageHeight();
        }
        else
        {
            $y = floor( ($img->getImageHeight() - $overlay->getImageHeight()) / 2 );
        }


        if ( isset( $params[ 'x' ] ) )
        {
            $x += ( int ) $params[ 'x' ];
        }

        if ( isset( $params[ 'y' ] ) )
        {
            $y += ( int ) $params[ 'y' ];
        }


        //if (!isset($params["opacity"])) $params["opacity"]=100;
        //$overlay->setImageOpacity(.5);
        //$overlay->stripImage();



        $img->compositeImage( $overlay, Imagick::COMPOSITE_SRCOVER, $x, $y );


        return $img;
    }
}

?>
