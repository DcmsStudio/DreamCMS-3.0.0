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

    public static function getTitle()
    {
        return trans( 'Bild Einrahmen' );
    }

    public static function getDescription()
    {
        return trans( 'Fügt dem Bil einen Rahmen hinzu.' );
    }

    public static function getParameters()
    {
        return array(
                'width'    => array(
                        'required'    => true,
                        'type'        => 'int',
                        'description' => 'The width of the border (not required if specifying advanced borders)'
                ),
                'color'    => array(
                        'required'    => true,
                        'type'        => 'color',
                        'description' => 'The colour of the border (not required if specifying advanced borders)'
                ),
                'advanced' => array(
                        'required'    => false,
                        'type'        => 'boolean',
                        'description' => 'Allows defining of all four borders seperately'
                ),
                'top'      => array(
                        'width' => array(
                                'required'    => true,
                                'type'        => 'int',
                                'description' => 'The width of the border'
                        ),
                        'color' => array(
                                'required'    => true,
                                'type'        => 'color',
                                'description' => 'The colour of the border'
                        ),
                ),
                'right'    => array(
                        'width' => array(
                                'required'    => true,
                                'type'        => 'int',
                                'description' => 'The width of the border'
                        ),
                        'color' => array(
                                'required'    => true,
                                'type'        => 'color',
                                'description' => 'The colour of the border'
                        ),
                ),
                'bottom'   => array(
                        'width' => array(
                                'required'    => true,
                                'type'        => 'int',
                                'description' => 'The width of the border'
                        ),
                        'color' => array(
                                'required'    => true,
                                'type'        => 'color',
                                'description' => 'The colour of the border'
                        ),
                ),
                'left'     => array(
                        'width' => array(
                                'required'    => true,
                                'type'        => 'int',
                                'description' => 'The width of the border'
                        ),
                        'color' => array(
                                'required'    => true,
                                'type'        => 'color',
                                'description' => 'The colour of the border'
                        ),
                ),
        );
    }

    public static function transform( $img, $params )
    {

        // work out border dimensions
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

        // enlarge the image?
        if ( isset( $params[ 'enlarge' ] ) && $params[ 'enlarge' ] == 1 )
        {
            $width  = $img->getImageWidth() + $borders[ 'left' ][ 'width' ] + $borders[ 'right' ][ 'width' ];
            $height = $img->getImageHeight() + $borders[ 'top' ][ 'width' ] + $borders[ 'bottom' ][ 'width' ];
            $canvas = new Imagick();
            $canvas->newImage( $width, $height, 'none', 'png' );
            $canvas->compositeImage( $img, Imagick::COMPOSITE_SRCOVER, $borders[ 'left' ][ 'width' ], $borders[ 'top' ][ 'width' ] );
            $img->destroy();
            $img    = $canvas;
        }

        // top border
        $draw = new ImagickDraw();
        $rgb  = ImageTools::htmlcolor2rgb( $borders[ 'top' ][ 'color' ] );
        $draw->setStrokeColor( "rgb({$rgb[ 0 ]}, {$rgb[ 1 ]}, {$rgb[ 2 ]})" );
        for ( $i = 0; $i < $borders[ 'top' ][ 'width' ]; $i++ )
        {
            $draw->line( $i, $i, $img->getImageWidth() - 1 - $i, $i );
        }
        $img->drawImage( $draw );

        // right border
        $draw = new ImagickDraw();
        $rgb  = ImageTools::htmlcolor2rgb( $borders[ 'right' ][ 'color' ] );
        $draw->setStrokeColor( "rgb({$rgb[ 0 ]}, {$rgb[ 1 ]}, {$rgb[ 2 ]})" );
        for ( $i = 0; $i < $borders[ 'right' ][ 'width' ]; $i++ )
        {
            $draw->line( $img->getImageWidth() - 1 - $i, $i, $img->getImageWidth() - 1 - $i, $img->getImageHeight() - 1 - $i );
        }
        $img->drawImage( $draw );

        // bottom border
        $draw = new ImagickDraw();
        $rgb  = ImageTools::htmlcolor2rgb( $borders[ 'bottom' ][ 'color' ] );
        $draw->setStrokeColor( "rgb({$rgb[ 0 ]}, {$rgb[ 1 ]}, {$rgb[ 2 ]})" );
        for ( $i = 1; $i <= $borders[ 'bottom' ][ 'width' ]; $i++ )
        {
            $draw->line( $i - 1, $img->getImageHeight() - $i, ($img->getImageWidth()) - $i, $img->getImageHeight() - $i );
        }
        $img->drawImage( $draw );

        // left border
        $draw = new ImagickDraw();
        $rgb  = ImageTools::htmlcolor2rgb( $borders[ 'left' ][ 'color' ] );
        $draw->setStrokeColor( "rgb({$rgb[ 0 ]}, {$rgb[ 1 ]}, {$rgb[ 2 ]})" );
        for ( $i = 0; $i < $borders[ 'left' ][ 'width' ]; $i++ )
        {
            $draw->line( $i, $i, $i, $img->getImageHeight() - 1 - $i );
        }
        $img->drawImage( $draw );

        return $img;
    }
}

?>