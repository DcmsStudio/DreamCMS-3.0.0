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

    public static function transform( $gd, $params )
    {

        if ( !isset( $params[ 'watermark' ] ) || !file_exists( PUBLIC_PATH . $params[ 'watermark' ] ) )
        {
            return $gd;
        }

        $x_orig = imagesx( $gd );
        $y_orig = imagesy( $gd );

        $image_watermark          = !empty( $params[ 'watermark' ] ) ? PUBLIC_PATH . $params[ 'watermark' ] : '';
        $image_watermark_position = !empty( $params[ 'watermark_position' ] ) ? $params[ 'watermark_position' ] : 'top-left'; // Left or Right
        $image_watermark_x        = !empty( $params[ 'watermark_x' ] ) ? $params[ 'watermark_x' ] : null;
        $image_watermark_y        = !empty( $params[ 'watermark_y' ] ) ? $params[ 'watermark_y' ] : null;




        $watermark_info    = getimagesize( $image_watermark );
        $watermark_type    = (array_key_exists( 2, $watermark_info ) ? $watermark_info[ 2 ] : null); // 1 = GIF, 2 = JPG, 3 = PNG
        $watermark_checked = false;


        if ( $watermark_type == 1 )
        {
            if ( !function_exists( 'imagecreatefromgif' ) )
            {
                return $gd;
            }
            else
            {
                $filter = @imagecreatefromgif( $image_watermark );
                imagealphablending( $filter, TRUE );
                if ( !$filter )
                {
                    return $gd;
                }
                else
                {
                    $watermark_checked = true;
                }
            }
        }
        else if ( $watermark_type == 2 )
        {
            if ( !function_exists( 'imagecreatefromjpeg' ) )
            {
                return $gd;
            }
            else
            {
                $filter = @imagecreatefromjpeg( $image_watermark );
                imagealphablending( $filter, TRUE );
                if ( !$filter )
                {
                    return $gd;
                }
                else
                {
                    $watermark_checked = true;
                }
            }
        }
        else if ( $watermark_type == 3 )
        {
            if ( !function_exists( 'imagecreatefrompng' ) )
            {
                return $gd;
            }
            else
            {
                $filter = @imagecreatefrompng( $image_watermark );
                imagealphablending( $filter, TRUE );
                if ( !$filter )
                {
                    return $gd;
                }
                else
                {
                    $watermark_checked = true;
                }
            }
        }
        else
        {
            return $gd;
        }


        if ( $watermark_checked )
        {
            $watermark_width  = imagesx( $filter );
            $watermark_height = imagesy( $filter );

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
                if ( stripos( $image_watermark_position, 'right' ) !== false )
                {
                    $watermark_x = $x_orig - $watermark_width - $watermark_margin;
                }
                else if ( stripos( $image_watermark_position, 'left' ) !== false )
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
                if ( stripos( $image_watermark_position, 'bottom' ) !== false )
                {
                    $watermark_y = $y_orig - $watermark_height - $watermark_margin;
                }
                else if ( stripos( $image_watermark_position, 'top' ) !== false )
                {
                    $watermark_y = 0 + $watermark_margin;
                }
                else
                {
                    $watermark_y = ($y_orig - $watermark_height) / 2;
                }
            }


            $image_new = ImageTools::getNewImage( $x_orig, $y_orig );

            imagecopyresampled( $image_new, $gd, 0, 0, 0, 0, $x_orig, $y_orig, $x_orig, $y_orig );

            $transcol = imagecolortransparent( $image_new );

            $t_x = 0;
            $t_y = 0;
            imagecolortransparent( $filter, imagecolorat( $filter, $t_x, $t_y ) );
            imagecopyresampled( $image_new, $filter, $watermark_x, $watermark_y, 0, 0, $watermark_width, $watermark_height, $watermark_width, $watermark_height );
            imagedestroy( $gd );
            return $image_new;

            // imagecopyresampled($gd, $filter, $watermark_x, $watermark_y, 0, 0, $watermark_width, $watermark_height, $watermark_width, $watermark_height);
        }

        return $gd;
    }
}

?>