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
 * @file        class.vignette.php
 */
class ImageTransformationVignette
{

    public static function getTitle()
    {
        return trans( 'Vignettierung' );
    }

    public static function getDescription()
    {
        return trans( 'Die Vignettierung ist eine Abschattung zum Rand eines Bildes, was oft in Fotografien des vergangenen Jahrhunderts zu sehen ist.' );
    }

    public static function getParameters()
    {
        return array(
                'blackpoint' => array(
                        'required'    => false,
                        'type'        => 'int',
                        'description' => 'The blackpoint. Whatever that is.'
                ),
                'whitepoint' => array(
                        'required'    => false,
                        'type'        => 'int',
                        'description' => 'The whitepoint. Whatever that is.'
                ),
                'x'          => array(
                        'required'    => false,
                        'type'        => 'int',
                        'description' => 'Some offset.'
                ),
                'y'          => array(
                        'required'    => false,
                        'type'        => 'int',
                        'description' => 'Some offset.'
                ),
        );
    }

    public static function transform( $img, $params )
    {
        $blackpoint = isset( $params[ 'blackpoint' ] ) ? $params[ 'blackpoint' ] : 70;
        $whitepoint = isset( $params[ 'whitepoint' ] ) ? $params[ 'whitepoint' ] : 70;
        $x          = isset( $params[ 'x' ] ) ? $params[ 'x' ] : 70;
        $y          = isset( $params[ 'y' ] ) ? $params[ 'y' ] : 70;
        $img->vignetteImage( $blackpoint, $whitepoint, $x, $y );
        return $img;
    }
}

?>
