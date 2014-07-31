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

    public static function getTitle()
    {
        return trans( 'Flip' );
    }

    public static function getDescription()
    {
        return trans( 'Flip the image along either it\'s x or y axis.' );
    }

    public static function getParameters()
    {
        return array(
                'axis' => array(
                        'required'    => false,
                        'type'        => 'y|x',
                        'description' => 'The axis along which the image should be flipped'
                ),
        );
    }

    public static function transform( $img, $params )
    {
        $axis = isset( $params[ 'axis' ] ) ? $params[ 'axis' ] : 'x';
        if ( $axis == 'x' )
        {
            $img->flipImage();
        }
        else
        {
            $img->flopImage();
        }
        return $img;
    }
}

?>