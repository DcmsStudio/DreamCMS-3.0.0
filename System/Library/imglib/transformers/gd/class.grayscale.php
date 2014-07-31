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
 * @file        class.grayscale.php
 */
class ImageTransformationGrayscale
{

    public static function transform( $gd, $params )
    {

        $params[ "value" ] = !isset( $params[ "value" ] ) ? 100 : $params[ "value" ];
        $fact              = $params[ "value" ] / 100;

        $w = imagesx( $gd );
        $h = imagesy( $gd );
        for ( $i = 0; $i < $h; $i++ )
        {
            for ( $j = 0; $j < $w; $j++ )
            {
                $pos = imagecolorat( $gd, $j, $i );
                if ( !imageistruecolor( $gd ) )
                {
                    $f   = imagecolorsforindex( $gd, $pos );
                    $gst = $f[ "red" ] * 0.15 + $f[ "green" ] * 0.5 + $f[ "blue" ] * 0.35;
                    list($r, $g, $b) = array( $fact * $gst + (1 - $fact) * $f[ "red" ],
                            $fact * $gst + (1 - $fact) * $f[ "green" ],
                            $fact * $gst + (1 - $fact) * $f[ "blue" ] );
                    $col = imagecolorexact( $gd, $r, $g, $b );
                    if ( $col = -1 )
                        $col = imagecolorallocate( $gd, $r, $g, $b );
                } else
                {
                    list($r, $g, $b) = array( (($pos >> 16) & 0xFF), (($pos >> 8) & 0xFF), ($pos & 0xFF) );
                    $gst = $r * 0.15 + $g * 0.5 + $b * 0.35;
                    $col = imagecolorallocate( $gd, $fact * $gst + (1 - $fact) * $r, $fact * $gst + (1 - $fact) * $g, $fact * $gst + (1 - $fact) * $b );
                }
                imagesetpixel( $gd, $j, $i, $col );
            }
        }
        return $gd;
    }
}

?>
