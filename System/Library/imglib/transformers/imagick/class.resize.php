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
 * @file        class.resize.php
 */
class ImageTransformationResize
{

    public static function getTitle()
    {
        return trans( 'Ändern der Bildgröße' );
    }

    public static function getDescription()
    {
        return trans( 'Ändern der Bildgröße.' );
    }

    public static function transform( $img, $params )
    {

        $x_orig     = $img->getImageWidth();
        $y_orig     = $img->getImageHeight();
        $ratio_orig = $x_orig / $y_orig;

        $x_new = !empty( $params[ 'width' ] ) ? $params[ 'width' ] : $x_orig;
        $y_new = !empty( $params[ 'height' ] ) ? $params[ 'height' ] : $y_orig;
        $x_new = !empty( $params[ 'p_width' ] ) ? ceil( $x_orig * ($params[ 'p_width' ] / 100) ) : $x_new;
        $y_new = !empty( $params[ 'p_height' ] ) ? ceil( $y_orig * ($params[ 'p_height' ] / 100) ) : $y_new;

        if ( isset( $params[ 'keep_aspect' ] ) && $params[ 'keep_aspect' ] == true )
        {
            if ( $x_new / $y_new > $ratio_orig )
            {
                $x_new = $y_new * $ratio_orig;
            }
            else
            {
                $y_new = $x_new / $ratio_orig;
            }
        }

        $do_transform = true;

        if ( isset( $params[ 'shrink_only' ] ) && $params[ 'shrink_only' ] == true )
        {
            $do_transform = false;
            if ( ($x_orig > $x_new) || ($y_orig > $y_new) )
            {
                $do_transform = true;
            }
        }

        if ( $do_transform )
        {
            $img->scaleImage( $x_new, $y_new ); //, Imagick::FILTER_LANCZOS, 0);
        }

        return $img;
    }
}

?>