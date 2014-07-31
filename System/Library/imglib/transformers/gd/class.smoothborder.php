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
 * @file        class.smoothborder.php
 */
class ImageTransformationSmoothborder
{

    public static function transform( $gd, $params )
    {
        $x_orig = imagesx( $gd );
        $y_orig = imagesy( $gd );

        $border = !empty( $params[ 'border' ] ) ? intval( $params[ 'border' ] ) : 3;
        $color1 = !empty( $params[ 'color1' ] ) ? $params[ 'color1' ] : '#FFFFFF';
        $color2 = !empty( $params[ 'color2' ] ) ? $params[ 'color2' ] : '#000000';

        list($red1, $green1, $blue1) = ImageTools::htmlcolor2rgb( $color1 );
        list($red2, $green2, $blue2) = ImageTools::htmlcolor2rgb( $color2 );

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