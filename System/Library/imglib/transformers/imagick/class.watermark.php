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
 * @file        class.watermark.php
 */
class ImageTransformationWatermark
{

    public static function getDescription()
    {
        return 'Add a vignette filter to the image.';
    }

    public static function getParameters()
    {
        return array(
                'blackpoint' => array(
                        'required'    => false,
                        'type'        => 'int',
                        'description' => 'The blackpoint. Whatever that is.'
                ),
                'whiepoint'  => array(
                        'required'    => false,
                        'type'        => 'int',
                        'description' => 'The whitepoint. Whatever that is.'
                ),
                'x'          => array(
                        'required'    => false,
                        'type'        => 'int',
                        'description' => 'Some offset.'
                ),
                'y'          => array(
                        'required'    => false,
                        'type'        => 'int',
                        'description' => 'Some offset.'
                ),
        );
    }

    public static function transform( $image, $padding = 0 )
    {
        // Check if the watermark is bigger than the image
        $image_width  = $image->getImageWidth();
        $image_height = $image->getImageHeight();


        $image_watermark          = !empty( $params[ 'watermark' ] ) ? PUBLIC_PATH . $params[ 'watermark' ] : '';
        $image_watermark_position = !empty( $params[ 'watermark_position' ] ) ? $params[ 'watermark_position' ] : 'top left'; // Left or Right
        $image_watermark_x        = !empty( $params[ 'watermark_x' ] ) ? $params[ 'watermark_x' ] : null;
        $image_watermark_y        = !empty( $params[ 'watermark_y' ] ) ? $params[ 'watermark_y' ] : null;

        if ( !is_file( $image_watermark ) )
        {
            return $image;
        }

        $watermark        = new Imagick( $image_watermark );
        $watermark_width  = $watermark->getImageWidth();
        $watermark_height = $watermark->getImageHeight();

        if ( $image_width < $watermark_width + $padding || $image_height < $watermark_height + $padding )
        {
            return $image;
        }

        $watermark_width  = $watermark->getImageWidth();
        $watermark_height = $watermark->getImageHeight();

        $x_orig = $watermark_width;
        $y_orig = $watermark_height;

        $watermark_x = 0;
        $watermark_y = 0;

        $watermark_margin = 5;

        if ( is_numeric( $image_watermark_x ) )
        {
            if ( $image_watermark_x < 0 )
            {
                $watermark_x = $x_orig - $watermark_width + $image_watermark_x;
            }
            else
            {
                $watermark_x = $image_watermark_x;
            }
        }
        else
        {
            if ( strpos( $image_watermark_position, 'right' ) !== false )
            {
                $watermark_x = $x_orig - $watermark_width - $watermark_margin;
            }
            else if ( strpos( $image_watermark_position, 'left' ) !== false )
            {
                $watermark_x = 0 + $watermark_margin;
            }
            else
            {
                $watermark_x = ($x_orig - $watermark_width) / 2;
            }
        }


        if ( is_numeric( $image_watermark_y ) )
        {
            if ( $image_watermark_y < 0 )
            {
                $watermark_y = $y_orig - $watermark_height + $image_watermark_y;
            }
            else
            {
                $watermark_y = $image_watermark_y;
            }
        }
        else
        {
            if ( strpos( $image_watermark_position, 'bottom' ) !== false )
            {
                $watermark_y = $y_orig - $watermark_height - $watermark_margin;
            }
            else if ( strpos( $image_watermark_position, 'top' ) !== false )
            {
                $watermark_y = 0 + $watermark_margin;
            }
            else
            {
                $watermark_y = ($y_orig - $watermark_height) / 2;
            }
        }











        // Calculate each position
        $positions   = array();
        $positions[] = array( 0 + $padding, 0 + $padding );
        $positions[] = array( $image_width - $watermark_width - $padding, 0 + $padding );
        $positions[] = array( $image_width - $watermark_width - $padding, $image_height - $watermark_height - $padding );
        $positions[] = array( 0 + $padding, $image_height - $watermark_height - $padding );

        // Initialization
        $min        = null;
        $min_colors = 0;

        // Calculate the number of colors inside each region
        // and retrieve the minimum
        foreach ( $positions as $position )
        {
            $colors = $image->getImageRegion( $watermark_width, $watermark_height, $position[ 0 ], $position[ 1 ] )->getImageColors();

            if ( $min === null || $colors <= $min_colors )
            {
                $min        = $position;
                $min_colors = $colors;
            }
        }

        // Draw the watermark
        $image->compositeImage( $watermark, Imagick::COMPOSITE_OVER, $min[ 0 ], $min[ 1 ] );

        return $image;
    }
}

?>