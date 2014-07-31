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
 * @file        class.modulate.php
 */
class ImageTransformationModulate
{

    public static function getTitle()
    {
        return trans( 'Helligkeit, Sättigung und Farbton' );
    }

    public static function getDescription()
    {
        return trans( 'Ändern Sie die Helligkeit, Sättigung und Farbton des Bildes.' );
    }

    public static function getParameters()
    {
        return array(
                'brightness' => array(
                        'required'    => false,
                        'type'        => 'int',
                        'description' => 'Amount with which to modify the brightness'
                ),
                'saturation' => array(
                        'required'    => false,
                        'type'        => 'int',
                        'description' => 'Amount with which to modify the saturation'
                ),
                'hue'        => array(
                        'required'    => false,
                        'type'        => 'int',
                        'description' => 'Amount with which to modify the hue'
                ),
        );
    }

    public static function transform( $img, $params )
    {
        $brightness = !empty( $params[ 'brightness' ] ) ? $params[ 'brightness' ] : 20;
        $saturation = !empty( $params[ 'saturation' ] ) ? $params[ 'saturation' ] : 0;
        $hue        = !empty( $params[ 'hue' ] ) ? $params[ 'hue' ] : 0;

        $brightness = 100 + $brightness;
        $saturation = 100 + $saturation;
        $hue        = 100 + $hue;


        $img->modulateImage( $brightness, $saturation, $hue );
        return $img;
    }
}

?>