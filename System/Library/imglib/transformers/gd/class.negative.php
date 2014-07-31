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
 * @file        class.negative.php
 */
class ImageTransformationNegative
{

    public static function transform( $gd, $params )
    {
        imagealphablending( $gd, true );

        $img_x = imagesx( $gd );
        $img_y = imagesy( $gd );

        #for($y=0; $y < $img_y; $y++) {
        #	for($x=0; $x < $img_x; $x++) {

        $y = 0;
        while ( $y < $img_y )
        {

            $x = 0;
            while ( $x < $img_x )
            {


                if ( isset( $params[ 'greyscale' ] ) )
                {
                    $pixel = imagecolorsforindex( $gd, imagecolorat( $gd, $x, $y ) );
                    $r     = $g     = $b     = round( (0.2125 * $pixel[ 'red' ]) + (0.7154 * $pixel[ 'green' ]) + (0.0721 * $pixel[ 'blue' ]) );
                    $color = imagecolorallocatealpha( $gd, $r, $g, $b, $pixel[ 'alpha' ] );
                    imagesetpixel( $gd, $x, $y, $color );
                }

                $pixel = imagecolorsforindex( $gd, imagecolorat( $gd, $x, $y ) );

                $r = round( 255 - $pixel[ 'red' ] );
                $g = round( 255 - $pixel[ 'green' ] );
                $b = round( 255 - $pixel[ 'blue' ] );

                $color = imagecolorallocatealpha( $gd, $r, $g, $b, $pixel[ 'alpha' ] );

                imagesetpixel( $gd, $x, $y, $color );

                $x++;
            }

            $y++;
        }

        return $gd;
    }
}

?>