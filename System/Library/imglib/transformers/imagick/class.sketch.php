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
 * @file        class.sketch.php
 */
class ImageTransformationSketch
{

    public static function getTitle()
    {
        return trans( 'Bleistiftskizze' );
    }

    public static function getDescription()
    {
        return trans( 'Simulieren Sie eine Bleistiftskizze.' );
    }

    public static function getParameters()
    {
        return array(
                'radius'    => array(
                        'required'    => false,
                        'type'        => 'int',
                        'description' => 'The radius of the pencil'
                ),
                'sigma'     => array(
                        'required'    => false,
                        'type'        => 'int',
                        'description' => 'The deviation applied to the radius of the pencil'
                ),
                'angle'     => array(
                        'required'    => false,
                        'type'        => 'int',
                        'description' => 'The angle along which to sketch'
                ),
                'grayscale' => array(
                        'required'    => false,
                        'type'        => 'boolean',
                        'description' => 'Simulate a grayscale pencil sketch'
                ),
        );
    }

    public static function transform( $img, $params )
    {
        $radius    = isset( $params[ 'radius' ] ) ? $params[ 'radius' ] : 10;
        $sigma     = isset( $params[ 'sigma' ] ) ? $params[ 'sigma' ] : 5;
        $angle     = isset( $params[ 'angle' ] ) ? $params[ 'angle' ] : 45;
        $grayscale = isset( $params[ 'grayscale' ] ) ? $params[ 'grayscale' ] : false;
        if ( $grayscale )
        {
            $img = ImageTools::runTransformation( 'grayscale', $img );
        }
        $img->sketchImage( $radius, $sigma, $angle );
        return $img;
    }
}

?>