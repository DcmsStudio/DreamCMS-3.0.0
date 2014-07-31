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
 * @file        class.blur.php
 */
class ImageTransformationBlur
{

    public static function getTitle()
    {
        return trans( 'Bild verwischen' );
    }

    public static function getDescription()
    {
        return trans( 'Bewegung in das Bild bringen um es zu verwischen.' );
    }

    public static function getParameters()
    {
        return array(
                'radius' => array(
                        'required'    => false,
                        'type'        => 'int',
                        'description' => 'The radius of the Gaussian, in pixels, not counting the center pixel'
                ),
                'sigma'  => array(
                        'required'    => false,
                        'type'        => 'int',
                        'description' => 'The standard deviation of the Gaussian, in pixels'
                ),
                'angle'  => array(
                        'required'    => false,
                        'type'        => 'int',
                        'description' => 'The angle along which to apply the blur (can be used to simulate motion blur)'
                ),
        );
    }

    public static function transform( $img, $params )
    {
        $radius = isset( $params[ 'radius' ] ) ? $params[ 'radius' ] : 5;
        $sigma  = isset( $params[ 'sigma' ] ) ? $params[ 'sigma' ] : 2;
        $angle  = !empty( $params[ 'angle' ] ) ? $params[ 'angle' ] : false;
        if ( $angle !== false )
        {
            $img->motionBlurImage( $radius, $sigma, $angle );
        }
        else
        {
            $img->gaussianBlurImage( $radius, $sigma );
        }
        return $img;
    }
}

?>
