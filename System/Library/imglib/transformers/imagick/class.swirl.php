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
 * @file        class.swirl.php
 */
class ImageTransformationSwirl
{

    public static function getTitle()
    {
        return trans( 'Schwenken' );
    }

    public static function getDescription()
    {
        return trans( 'Schwenken Sie die Pixel um die Mitte des Bildes.' );
    }

    public static function getParameters()
    {
        return array(
                'rotation' => array(
                        'required'    => false,
                        'type'        => 'int',
                        'description' => 'Number of degrees to swirl by.'
                ),
        );
    }

    public static function transform( $img, $params )
    {
        $deg = isset( $params[ 'rotation' ] ) ? $params[ 'rotation' ] : 120;
        $img->swirlImage( $deg );
        return $img;
    }
}

?>
