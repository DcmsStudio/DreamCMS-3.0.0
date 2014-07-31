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
 * @file        class.mask.php
 */
class ImageTransformationMask
{

    public static function getParameters()
    {
        return array(
                'mask'     => array(
                        'required'    => true,
                        'type'        => 'string',
                        'description' => 'The Image Mask'
                ),
                'position' => array(
                        'required'    => false,
                        'type'        => 'position',
                        'description' => 'The horizontal position'
                ),
        );
    }

    private static function getMasks()
    {
        $masks = array();

        $folder = PUBLIC_PATH . 'img/masks/';
        $files  = glob( $folder . '*.png' );

        foreach ( $files as $file )
        {
            $file = str_replace( $folder, '', str_replace( '\\', '/', $file ) );
            $f    = explode( '.', $file );

            $masks[] = $f[ 0 ];
        }

        return $masks;
    }

    public static function transform( $gd, $params )
    {
        $x_orig = imagesx( $gd );
        $y_orig = imagesy( $gd );

        $masks       = self::getMasks();
        $mask        = !empty( $params[ 'mask' ] ) ? $params[ 'mask' ] : '';
        $mask_resize = isset( $params[ 'resize' ] ) ? true : false;


        if ( empty( $mask ) || !file_exists( PUBLIC_PATH . 'img/masks/' . $mask . '.png' ) || !in_array( $mask, $masks ) )
        {
            //die(PUBLIC_PATH . 'img/masks/' . $mask . '.png');
            return $gd;
        }

        $position     = !empty( $params[ 'position' ] ) ? $params[ 'position' ] : 'top left';
        $mask_checked = false;


        $mask_margin = 5;

        if ( !function_exists( 'imagecreatefrompng' ) )
        {
            return $gd;
        }
        else
        {
            $filter = imagecreatefrompng( PUBLIC_PATH . 'img/masks/' . $mask . '.png' );
            imagealphablending( $filter, true );


            if ( $mask_resize )
            {
                $mask_width  = imagesx( $filter );
                $mask_height = imagesy( $filter );

                $image_new = ImageTools::getNewImage( $x_orig, $y_orig );
                $bgcol     = imagecolorallocatealpha( $image_new, 0, 0, 0, 127 );
                imagefill( $image_new, 0, 0, $bgcol );


                imagealphablending( $image_new, true );
                imagesavealpha( $image_new, true );

                imagecopyresampled( $image_new, $filter, 0, 0, 0, 0, $x_orig, $y_orig, $mask_width, $mask_height );

                imagedestroy( $filter );

                $mask_margin = 0;
                $filter      = $image_new;
            }






            if ( !$filter )
            {
                return $gd;
            }
            else
            {
                $mask_checked = true;
            }
        }



        if ( $mask_checked )
        {
            $mask_width  = imagesx( $filter );
            $mask_height = imagesy( $filter );


            $mask_x = 0;
            $mask_y = 0;


            if ( strpos( $position, 'right' ) !== false )
            {
                $mask_x = $x_orig - $mask_width + $mask_margin;
            }
            else if ( strpos( $position, 'left' ) !== false )
            {
                $mask_x = 0 + $mask_margin;
            }

            if ( strpos( $position, 'bottom' ) !== false )
            {
                $mask_y = $y_orig - $mask_height + $mask_margin;
            }
            else if ( strpos( $position, 'top' ) !== false )
            {
                $mask_y = 0 + $mask_margin;
            }

            $image_new = ImageTools::getNewImage( $x_orig, $y_orig );
            imagecopyresampled( $image_new, $gd, 0, 0, 0, 0, $x_orig, $y_orig, $x_orig, $y_orig );

            $transcol = imagecolortransparent( $image_new );

            $t_x = 0;
            $t_y = 0;
            imagecolortransparent( $filter, imagecolorat( $filter, $t_x, $t_y ) );

            imagecopyresampled( $image_new, $filter, $mask_x, $mask_y, 0, 0, $mask_width, $mask_height, $mask_width, $mask_height );
            imagedestroy( $gd );

            return $image_new;
        }

        return $gd;







        $new   = ImageTools::getNewImage( $x_orig, $y_orig );
        $bgcol = imagecolorallocatealpha( $new, 0, 0, 0, 127 );
        imagefill( $new, 0, 0, $bgcol );


        imagealphablending( $new, true );
        imagesavealpha( $gd, false );

        imagecopy( $new, $gd, 0, 0, 0, 0, $x_orig, $y_orig );

        #imagesavealpha($new, true);

        for ( $i = 0; $i < $border; $i++ )
        {
            $alpha = round( ($i / $border) * 127 );
            $c1    = imagecolorallocatealpha( $new, $red1, $green1, $blue1, $alpha );
            $c2    = imagecolorallocatealpha( $new, $red2, $green2, $blue2, $alpha );

            imageline( $new, $i, $i, $x_orig - $i - 1, $i, $c1 );
            imageline( $new, $x_orig - $i - 1, $y_orig - $i, $x_orig - $i - 1, $i, $c2 );
            imageline( $new, $x_orig - $i - 1, $y_orig - $i - 1, $i, $y_orig - $i - 1, $c2 );
            imageline( $new, $i, $i, $i, $y_orig - $i - 1, $c1 );
        }

        imagedestroy( $gd );
        return $new;
    }
}

?>