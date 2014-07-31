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
 * @file        class.blend.php
 */
class ImageTransformationBlend
{

    public static function getTitle()
    {
        return trans( 'Farbe in das Bild mischen.' );
    }

    public static function getDescription()
    {
        return trans( 'Mischt eine Farbe in das Bild.' );
    }

    public static function getParameters()
    {
        return array(
                'color'   => array(
                        'required'    => false,
                        'type'        => 'int',
                        'description' => trans( 'Die Farbe wird in das Bild gemischt' )
                ),
                'opacity' => array(
                        'required'    => false,
                        'type'        => 'int',
                        'description' => trans( 'Die Deckkraft (Prozentsatz) der Farbe' )
                )
        );
    }

    public static function transform( $img, $params )
    {
        $color = isset( $params[ 'color' ] ) ? $params[ 'color' ] : '#ff0000';
        $alpha = isset( $params[ 'alpha' ] ) ? $params[ 'alpha' ] : 50;

        $img->setImageOpacity( ($alpha / 100 ) );

        $canvas = new Imagick();
        $canvas->newImage( $img->getImageWidth(), $img->getImageHeight(), ImageTools::checkColor( $color ), 'png' );
        $canvas->compositeImage( $img, Imagick::COMPOSITE_SRCOVER, 0, 0 );

        return $canvas;
    }
}

?>
