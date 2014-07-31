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
 * @file        class.reflect.php
 */
class ImageTransformationReflect
{

    public static function transform( $gd, $params )
    {

        $img_x = imagesx( $gd );
        $img_y = imagesy( $gd );


        if ( empty( $image_default_color ) )
            $image_default_color = '#FFFFFF';






        // we decode image_reflection_height, which can be a integer, a string in pixels or percentage
        $height                 = !empty( $params[ 'height' ] ) ? intval( $params[ 'height' ] ) : null;
        $background_color       = !empty( $params[ 'bgcolor' ] ) ? $params[ 'bgcolor' ] : '';
        $p_height               = !empty( $params[ 'p_height' ] ) ? intval( $params[ 'p_height' ] ) : null;
        $_opacity               = !empty( $params[ 'opacity' ] ) ? intval( $params[ 'opacity' ] ) : 60;
        $image_reflection_space = !empty( $params[ 'space' ] ) ? intval( $params[ 'space' ] ) : 0;

        $image_reflection_height = null;
        if ( !is_null( $p_height ) && $p_height > 0 )
            $image_reflection_height = $img_y * ($p_height / 100);
        if ( $image_reflection_height === null && !is_null( $height ) && $height > 0 )
            $image_reflection_height = $height;


        if ( is_null( $image_reflection_height ) )
        {
            return $gd;
        }


        if ( empty( $_opacity ) )
            $_opacity = 60;


        $image_reflection_height = ( int ) $image_reflection_height;
        if ( $image_reflection_height > $img_y )
            $image_reflection_height = $img_y;




        $newgd = ImageTools::getNewImage( $img_x, $img_y );
        imagealphablending( $newgd, false );
        imagesavealpha( $newgd, true );



        if ( $background_color == '' )
        {
            // make background color equal bottom-right pixel
            $rgb   = imagecolorat( $gd, imagesx( $gd ) - 1, imagesy( $gd ) - 1 );
            if ( imageistruecolor( $gd ) )
                $bgcol = imagecolorallocate( $newgd, ($rgb >> 16) & 0xFF, ($rgb >> 8) & 0xFF, $rgb & 0xFF );
            else
            {
                $col   = imagecolorsforindex( $gd, $rgb );
                $bgcol = imagecolorallocate( $newgd, $col[ "red" ], $col[ "green" ], $col[ "blue" ] );
            }
            imagefill( $newgd, 0, 0, $bgcol );
        }
        elseif ( $background_color != '' )
        {
            $col   = ImageTools::htmlcolor2rgb( $background_color );
            $bgcol = imagecolorallocate( $newgd, $col[ 0 ], $col[ 1 ], $col[ 2 ] );
            imagefill( $newgd, 0, 0, $bgcol );
        }
        else
        {
            imagefilledrectangle( $newgd, 0, 0, $img_x, $img_y, imagecolorallocatealpha( $newgd, 0, 0, 0, 127 ) );
        }

        imagecopyresampled( $newgd, $gd, 0, 0, 0, 0, $img_x, $img_y, $img_x, $img_y );
        imagedestroy( $gd );

        $gd = & $newgd;

        // create the new destination image
        $img_y_reflect_height = $img_y + $image_reflection_height + $image_reflection_space;



        $tmp = ImageTools::getNewImage( $img_x, $img_y_reflect_height );
        imagealphablending( $tmp, false );
        imagesavealpha( $tmp, false );

        imagefilledrectangle( $tmp, 0, 0, $img_x, $img_y_reflect_height, imagecolorallocatealpha( $tmp, 0, 0, 0, 127 ) );


        $transparency = $_opacity;

        // copy the original image
        //imagecopy($tmp, $gd, 0, 0, 0, 0, $img_x, $img_y + ($image_reflection_space < 0 ? $image_reflection_space : 0));
        imagecopyresampled( $tmp, $gd, 0, 0, 0, 0, imagesx( $gd ), imagesy( $gd ), imagesx( $gd ), imagesy( $gd ) );



        // we have to make sure the extra bit is the right color, or transparent
        if ( ($image_reflection_height + $image_reflection_space) > 0 )
        {

            // use the background color if present
            if ( !empty( $background_color ) )
            {
                $col  = ImageTools::htmlcolor2rgb( $background_color );
                #$fill = imagecolorallocate($tmp, $col[0], $col[1], $col[2]);
                $fill = imagecolorallocate( $tmp, $col[ 0 ], $col[ 1 ], $col[ 2 ] );
            }
            else
            {
                $fill = imagecolorallocatealpha( $tmp, 0, 0, 0, 127 );
            }

            // fill in from the edge of the extra bit
            imagefill( $tmp, round( $img_x / 2 ), $img_y + $image_reflection_height + $image_reflection_space - 1, $fill );
        }


        $debug = '';
        // copy the reflection
        for ( $y = 0; $y < $image_reflection_height; ++$y )
        {
            #$y = 0;
            #while($y < $image_reflection_height) {
            #$x = 0;
            #while($x < $img_x) {
            for ( $x = 0; $x < $img_x; ++$x )
            {


                $pixel_b = imagecolorsforindex( $tmp, imagecolorat( $tmp, $x, $y + $img_y + $image_reflection_space ) );
                $pixel_o = imagecolorsforindex( $gd, imagecolorat( $gd, $x, $img_y - $y - 1 + ($image_reflection_space < 0 ? $image_reflection_space : 0) ) );
                $alpha_b = 1 - ($pixel_b[ 'alpha' ] / 127);
                $alpha_o = 1 - ($pixel_o[ 'alpha' ] / 127);


                $opacity = ($alpha_o * $transparency) / 100;

                if ( $opacity > 0 )
                {
                    $red   = round( (($pixel_o[ 'red' ] * $opacity) + ($pixel_b[ 'red' ] ) * $alpha_b) / ($alpha_b + $opacity) );
                    $green = round( (($pixel_o[ 'green' ] * $opacity) + ($pixel_b[ 'green' ]) * $alpha_b) / ($alpha_b + $opacity) );
                    $blue  = round( (($pixel_o[ 'blue' ] * $opacity) + ($pixel_b[ 'blue' ] ) * $alpha_b) / ($alpha_b + $opacity) );

                    $alpha = ($opacity + $alpha_b);

                    if ( $alpha > 1 )
                    {
                        $alpha = 1;
                    }

                    $alpha = round( (1 - $alpha) * 127 );
                    $color = imagecolorallocatealpha( $tmp, $red, $green, $blue, $alpha );
                    imagesetpixel( $tmp, $x, ($y + $img_y + $image_reflection_space ), $color );
                }

                #$x++;
            }
            #$y++;
            #$debug .= "opacity:$opacity | $transparency \n";
            if ( $transparency > 0 )
            {
                $transparency = ($transparency - ($_opacity / $image_reflection_height) );
            }
        }
        #die($debug);

        imagedestroy( $gd );
        return $tmp;
    }
}

?>