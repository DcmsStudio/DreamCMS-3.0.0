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
 * @file        class.overlay.php
 */
class ImageTransformationOverlay
{

    public static function getDescription()
    {
        return 'Overlay another image on top of the image.';
    }

    public static function getParameters()
    {
        return array(
                'align'  => array(
                        'required'    => false,
                        'type'        => 'position',
                        'description' => 'The horizontal position of the overlay (left, center, right)'
                ),
                'valign' => array(
                        'required'    => false,
                        'type'        => 'position',
                        'description' => 'The vertical position of the overlay (top, middle, bottom)'
                ),
                'x'      => array(
                        'required'    => false,
                        'type'        => 'int',
                        'description' => 'The horizontal position of the overlay (in pixels, compliments align).'
                ),
                'y'      => array(
                        'required'    => false,
                        'type'        => 'int',
                        'description' => 'The vertical position of the overlay (in pixels, compliments valign).'
                ),
        );
    }

    public static function transform( $gd, $params )
    {
        $overlay = !empty( $params[ 'overlay' ] ) ? $params[ 'overlay' ] : false;
        if ( $overlay === false || !file_exists( $overlay ) )
        {
            return $gd;
        }

        $ext = substr( strrchr( $overlay, "." ), 1 );
        $ext = $ext == 'jpg' ? 'jpeg' : $ext;
        if ( !function_exists( 'imagecreatefrom' . $ext ) )
        {
            return $gd;
        }

        $overlay = call_user_func_array( 'imagecreatefrom' . $ext, array( $overlay ) );

        $params[ 'align' ]  = !empty( $params[ 'align' ] ) ? $params[ 'align' ] : 'left';
        $params[ 'valign' ] = !empty( $params[ 'valign' ] ) ? $params[ 'valign' ] : 'top';
        $params[ 'x' ]      = !empty( $params[ 'x' ] ) ? ( int ) $params[ 'x' ] : 0;
        $params[ 'y' ]      = !empty( $params[ 'y' ] ) ? ( int ) $params[ 'y' ] : 0;


        $w  = imagesx( $gd );
        $h  = imagesy( $gd );
        $sw = imagesx( $overlay );
        $sh = imagesy( $overlay );

        switch ( $params[ "align" ] )
        {
            case "center":
                $x = $w / 2 - $sw / 2 + $params[ "x" ];
                break;
            case "left":
                $x = $params[ "x" ];
                break;
            case "right":
                $x = $w - $sw + $params[ "x" ];
                break;
        }

        switch ( $params[ "valign" ] )
        {
            case "middle":
                $y = $h / 2 - $sh / 2 + $params[ "y" ];
                break;
            case "top":
                $y = $params[ "y" ];
                break;
            case "bottom":
                $y = $h - $sh + $params[ "y" ];
                break;
        }

        imagealphablending( $gd, true );
        imagecopy( $gd, $overlay, $x, $y, 0, 0, $sw, $sh );

        imagedestroy( $overlay );

        return $gd;
    }
}

?>
