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
 * @file        class.shadow.php
 */
class ImageTransformationShadow
{

    public static function getTitle()
    {
        return trans( 'Schatten' );
    }

    public static function getDescription()
    {
        return trans( 'Hinzufügen eines Schattens, um das Bild.' );
    }

    public static function getParameters()
    {
        return array(
                'color'    => array(
                        'required'    => false,
                        'type'        => 'int',
                        'description' => 'Colour of the shadow'
                ),
                'opacity'  => array(
                        'required'    => false,
                        'type'        => 'int',
                        'description' => 'The opacity of the shadow'
                ),
                'distance' => array(
                        'required'    => false,
                        'type'        => 'int',
                        'description' => 'Distance of the shadow from the image'
                ),
                'align'    => array(
                        'required'    => false,
                        'type'        => 'position',
                        'description' => 'The horizontal position of the original image (left, center, right)'
                ),
                'valign'   => array(
                        'required'    => false,
                        'type'        => 'position',
                        'description' => 'The vertical position of the original image (top, middle, bottom)'
                ),
        );
    }

    public static function transform( $img, $params )
    {
        $color    = isset( $params[ 'color' ] ) ? $params[ 'color' ] : '#999999';
        $bgcolor  = isset( $params[ 'bgcolor' ] ) ? $params[ 'bgcolor' ] : false;
        $opacity  = isset( $params[ 'opacity' ] ) ? $params[ 'opacity' ] : 70;
        $distance = isset( $params[ 'distance' ] ) ? $params[ 'distance' ] : 15;

        $rgb         = ImageTools::htmlcolor2rgb( $color );
        $shadowColor = "rgb({$rgb[ 0 ]}, {$rgb[ 1 ]}, {$rgb[ 2 ]})";

        $shadow = $img->clone();
        $shadow->setImageBackgroundColor( new ImagickPixel( $shadowColor ) );
        $shadow->shadowImage( $opacity, $distance, 5, 5 );

        if ( !isset( $params[ "valign" ] ) )
            $params[ "valign" ] = "middle";
        if ( $params[ "valign" ] == "top" )
        {
            $y = 0;
        }
        else if ( $params[ "valign" ] == "bottom" )
        {
            $y = $shadow->getImageHeight() - $img->getImageHeight();
        }
        else
        {
            $y = floor( ($shadow->getImageHeight() - $img->getImageHeight()) / 2 );
        }

        if ( !isset( $params[ "align" ] ) )
            $params[ "align" ] = "center";
        if ( $params[ "align" ] == "left" )
        {
            $x = 0;
        }
        else if ( $params[ "align" ] == "right" )
        {
            $x = $shadow->getImageWidth() - $img->getImageWidth();
        }
        else
        {
            $x = floor( ($shadow->getImageWidth() - $img->getImageWidth()) / 2 );
        }

        $shadow->compositeImage( $img, Imagick::COMPOSITE_OVER, $x, $y );

        if ( $bgcolor !== false || $img->getImageFormat() == 'jpeg' )
        {
            $bgcolor     = $bgcolor !== false ? $bgcolor : '#ffffff';
            $rgb         = ImageTools::htmlcolor2rgb( $bgcolor );
            $canvasColor = "rgb({$rgb[ 0 ]}, {$rgb[ 1 ]}, {$rgb[ 2 ]})";
            $canvas      = ImageTools::getNewImage( $shadow->getImageWidth(), $shadow->getImageHeight(), $canvasColor );
            $canvas->compositeImage( $shadow, Imagick::COMPOSITE_OVER, 0, 0 );
        }
        else
        {
            $canvas = $shadow;
        }

        return $canvas;
    }
}

?>