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
 * @file        class.rotate.php
 */
class ImageTransformationRotate
{

    private static function doRotate( $res, $angel, $color = -1 )
    {
        $new = imagerotate( $res, $rotate, $color );

        return $new;
    }

    public static function transform( $gd, $params )
    {
        $x_orig = imagesx( $gd );
        $y_orig = imagesy( $gd );

        $rotate = !empty( $params[ 'rotate' ] ) ? intval( $params[ 'rotate' ] ) : 0;

        if ( !$rotate )
        {
            return $gd;
        }
        /*
          if ( $rotate == 90 || $rotate == 270 ) {
          $new = ImageTools::getNewImage($y_orig, $x_orig);
          } else {
          $new = ImageTools::getNewImage($x_orig, $y_orig);
          }
         */
        if ( !function_exists( "imagerotate" ) )
        {
            imagealphablending( $gd, true );
            imagesavealpha( $gd, true );
            $new = self::imagerotateEquivalent( $gd, $rotate, 0, 0 );

            imagealphablending( $new, true );
            imagesavealpha( $new, true );
        }
        else
        {

            imagealphablending( $gd, true );
            imagesavealpha( $gd, true );
            $new = imagerotate( $gd, $rotate, -1, 0 );
        }


        imagedestroy( $gd );
        return $new;

        for ( $x = 0; $x < $x_orig; $x++ )
        {
            for ( $y = 0; $y < $y_orig; $y++ )
            {
                if ( $rotate == 90 )
                {
                    imagecopy( $new, $gd, $y, $x, $x, $y_orig - $y - 1, 1, 1 );
                }
                else if ( $rotate == 180 )
                {
                    imagecopy( $new, $gd, $x, $y, $x_orig - $x - 1, $y_orig - $y - 1, 1, 1 );
                }
                else if ( $rotate == 270 )
                {
                    imagecopy( $new, $gd, $y, $x, $x_orig - $x - 1, $y, 1, 1 );
                }
                else
                {
                    imagecopy( $new, $gd, $y, $x, $x, $y, 1, 1 );
                }
            }
        }

        imagedestroy( $gd );
        return $new;
    }

    private static function rotateX( $x, $y, $theta )
    {
        return $x * cos( $theta ) - $y * sin( $theta );
    }

    private static function rotateY( $x, $y, $theta )
    {
        return $x * sin( $theta ) + $y * cos( $theta );
    }

    private static function imagerotateEquivalent( &$srcImg, $angle, $bgcolor = 0, $ignore_transparent = 0 )
    {


        $srcw = imagesx( $srcImg );
        $srch = imagesy( $srcImg );

        //Normalize angle
        $angle %= 360;
        //Set rotate to clockwise
        $angle = -$angle;

        if ( $angle == 0 )
        {
            if ( $ignore_transparent == 0 )
            {
                imagesavealpha( $srcImg, true );
            }
            return $srcImg;
        }

        // Convert the angle to radians
        $theta = deg2rad( $angle );

        //Standart case of rotate
        if ( (abs( $angle ) == 90) || (abs( $angle ) == 270) )
        {
            $width  = $srch;
            $height = $srcw;
            if ( ($angle == 90) || ($angle == -270) )
            {
                $minX = 0;
                $maxX = $width;
                $minY = -$height + 1;
                $maxY = 1;
            }
            else if ( ($angle == -90) || ($angle == 270) )
            {
                $minX = -$width + 1;
                $maxX = 1;
                $minY = 0;
                $maxY = $height;
            }
        }
        else if ( abs( $angle ) === 180 )
        {
            $width  = $srcw;
            $height = $srch;
            $minX   = -$width + 1;
            $maxX   = 1;
            $minY   = -$height + 1;
            $maxY   = 1;
        }
        else
        {
            // Calculate the width of the destination image.
            $temp  = array( self::rotateX( 0, 0, 0 - $theta ),
                    self::rotateX( $srcw, 0, 0 - $theta ),
                    self::rotateX( 0, $srch, 0 - $theta ),
                    self::rotateX( $srcw, $srch, 0 - $theta )
            );
            $minX  = floor( min( $temp ) );
            $maxX  = ceil( max( $temp ) );
            $width = $maxX - $minX;

            // Calculate the height of the destination image.
            $temp   = array( self::rotateY( 0, 0, 0 - $theta ),
                    self::rotateY( $srcw, 0, 0 - $theta ),
                    self::rotateY( 0, $srch, 0 - $theta ),
                    self::rotateY( $srcw, $srch, 0 - $theta )
            );
            $minY   = floor( min( $temp ) );
            $maxY   = ceil( max( $temp ) );
            $height = $maxY - $minY;
        }

        $destimg = imagecreatetruecolor( $width, $height );



        if ( $ignore_transparent == 0 )
        {

            imagealphablending( $destimg, false );
            imagesavealpha( $destimg, true );

            $bgcol = imagecolorallocatealpha( $destimg, 0, 0, 0, 127 );
            imagefill( $destimg, 0, 0, $bgcol );
        }

        // sets all pixels in the new image
        for ( $x = $minX; $x < $maxX; $x++ )
        {
            for ( $y = $minY; $y < $maxY; $y++ )
            {
                // fetch corresponding pixel from the source image
                $srcX = round( self::rotateX( $x, $y, $theta ) );
                $srcY = round( self::rotateY( $x, $y, $theta ) );
                if ( $srcX >= 0 && $srcX < $srcw && $srcY >= 0 && $srcY < $srch )
                {
                    $color = imagecolorat( $srcImg, $srcX, $srcY );
                    imagesetpixel( $destimg, $x - $minX, $y - $minY, $color );
                }
                else
                {
                    $bgcol = imagecolorallocatealpha( $destimg, 0, 0, 0, 127 );
                    imagesetpixel( $destimg, $x - $minX, $y - $minY, $bgcol );
                }
            }
        }
        return $destimg;
    }
}

?>