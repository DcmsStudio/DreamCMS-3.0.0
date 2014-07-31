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

    public static function getTitle()
    {
        return trans( 'Abstände' );
    }

    public static function getDescription()
    {
        return trans( 'Fügt dem Bild Abstände hinzu.' );
    }

    public static function getParameters()
    {
        return array(
                'global'      => array(
                        'required'    => true,
                        'type'        => 'int',
                        'description' => 'Global margin width to add'
                ),
                'top'         => array(
                        'required'    => false,
                        'type'        => 'int',
                        'description' => 'The margin to add to the top of the image'
                ),
                'right'       => array(
                        'required'    => false,
                        'type'        => 'int',
                        'description' => 'The margin to add to the right of the image'
                ),
                'bottom'      => array(
                        'required'    => false,
                        'type'        => 'int',
                        'description' => 'The margin to add to the bottom of the image'
                ),
                'left'        => array(
                        'required'    => false,
                        'type'        => 'int',
                        'description' => 'The margin to add to the left of the image'
                ),
                'transparent' => array(
                        'required'    => false,
                        'type'        => 'boolean',
                        'description' => 'Makes the margins transparent'
                ),
                'bgcolor'     => array(
                        'required'    => false,
                        'type'        => 'color',
                        'description' => 'Colour to fill the margins with (auto or html notation)'
                ),
        );
    }

    public static function transform( $img, $params, $trans )
    {

        // work out the margins
        $left   = !empty( $params[ 'left' ] ) ? $params[ 'left' ] : 0;
        $right  = !empty( $params[ 'right' ] ) ? $params[ 'right' ] : 0;
        $top    = !empty( $params[ 'top' ] ) ? $params[ 'top' ] : 0;
        $bottom = !empty( $params[ 'bottom' ] ) ? $params[ 'bottom' ] : 0;

        if ( isset( $params[ 'global' ] ) && !empty( $params[ 'global' ] ) )
        {
            $top    = $right  = $bottom = $left   = ( int ) $params[ 'global' ];
        }

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

        // create a canvas for the image and margins
        $width  = $img->getImageWidth() + $left + $right;
        $height = $img->getImageHeight() + $top + $bottom;
        $canvas = new Imagick();
        $canvas->newImage( $width, $height, $bgcolor, 'png' );
        $canvas->compositeImage( $img, Imagick::COMPOSITE_SRCOVER, $left, $top );
        return $canvas;
    }
}

?>
