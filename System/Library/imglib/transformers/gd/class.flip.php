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
 * @file        class.flip.php
 */
class ImageTransformationFlip
{

    public static function transform( $gd, $params )
    {
        $x_orig = imagesx( $gd );
        $y_orig = imagesy( $gd );

        $flip = !empty( $params[ 'axis' ] ) ? $params[ 'axis' ] : 'x';

        $newimg = ImageTools::getNewImage( $x_orig, $y_orig );
        //imagealphablending($new, false);
        imagesavealpha( $newimg, true );

        $x = 0;
        while ( $x < $x_orig )
        {
            $y = 0;
            while ( $y < $y_orig )
            {

                #for ($x = 0; $x < $this->image_dst_x; $x++) {
                #	for ($y = 0; $y < $this->image_dst_y; $y++){
                if ( $flip != 'x' )
                {
                    imagecopy( $newimg, $gd, $x_orig - $x - 1, $y, $x, $y, 1, 1 );
                }
                else
                {
                    imagecopy( $newimg, $gd, $x, $y_orig - $y - 1, $x, $y, 1, 1 );
                }

                $y++;
            }

            $x++;
        }

        imagedestroy( $gd );
        return $newimg;
    }
}

?>