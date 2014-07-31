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

    public static function getTitle()
    {
        return trans( 'Reflexion' );
    }

    public static function getDescription()
    {
        return trans( 'Erstellen Sie eine Reflexion des Bildes.' );
    }

    public static function transform( $img, $params )
    {

        $height           = !empty( $params[ 'height' ] ) ? intval( $params[ 'height' ] ) : null;
        $background_color = !empty( $params[ 'bgcolor' ] ) ? $params[ 'bgcolor' ] : '';

        if ( empty( $image_default_color ) )
            $image_default_color = '#FFFFFF';




        $p_height = !empty( $params[ 'p_height' ] ) ? intval( $params[ 'p_height' ] ) : null;
        $_opacity = !empty( $params[ 'opacity' ] ) ? $params[ 'opacity' ] : 0.6;

        $image_reflection_space  = !empty( $params[ 'space' ] ) ? intval( $params[ 'space' ] ) : 0;
        $image_reflection_height = null;
        if ( !is_null( $p_height ) && $p_height > 0 )
            $image_reflection_height = $img_y * ($p_height / 100);
        if ( $image_reflection_height === null && !is_null( $height ) && $height > 0 )
            $image_reflection_height = $height;







        if ( isset( $params[ 'transparent' ] ) && $params[ 'transparent' ] == 1 )
        {
            $reflection = $img->clone();
            $reflection->flipImage();

            $gradient = new Imagick();
            $gradient->newPseudoImage( $reflection->getImageWidth() + 10, $reflection->getImageHeight() * 0.5, "gradient:transparent-black" );
            $reflection->compositeImage( $gradient, Imagick::COMPOSITE_DSTOUT, 0, 0 );

            $gradient->newPseudoImage( $reflection->getImageWidth() + 10, $reflection->getImageHeight() * 0.5, "gradient:black" );
            $reflection->compositeImage( $gradient, Imagick::COMPOSITE_DSTOUT, 0, $reflection->getImageHeight() * 0.5 );

            /* Add some opacity */
            $reflection->setImageOpacity( $_opacity );



            $canvas = new Imagick();
            $width  = $img->getImageWidth();
            $height = ( $img->getImageHeight() * 1.5 );


            $canvas->newImage( $width, $height, 'none', "png" );
            $canvas->compositeImage( $img, imagick::COMPOSITE_SRCOVER, 0, 0 );
            $canvas->compositeImage( $reflection, imagick::COMPOSITE_SRCOVER, 0, $img->getImageHeight() );
            return $canvas;
        }
        else
        {
            $img->borderImage( "white", 0, 0 );
            $reflection = $img->clone();
            $reflection->flipImage();


            $gradient = new Imagick();
            $gradient->newPseudoImage( $reflection->getImageWidth() + 10, $reflection->getImageHeight() + 10, "gradient:transparent-black" );
            $reflection->compositeImage( $gradient, imagick::COMPOSITE_OVER, 0, 0 );
            $reflection->setImageOpacity( 0.3 );


            $canvas = new Imagick();
            $width  = $img->getImageWidth() + 40;
            $height = ( $img->getImageHeight() * 2 ) + 30;

            $canvas->newImage( $width, $height, "white", "png" );
            $canvas->compositeImage( $img, imagick::COMPOSITE_OVER, 20, 10 );
            $canvas->compositeImage( $reflection, imagick::COMPOSITE_OVER, 20, $img->getImageHeight() + 10 );

            return $canvas;
        }
    }
}

?>