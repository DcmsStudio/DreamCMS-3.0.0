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
 * @file        class.crop.php
 */
class ImageTransformationCrop
{

    public static function getTitle()
    {
        return trans( 'Bild Ausschnitt' );
    }

    public static function getDescription()
    {
        return trans( 'Schneidet einen bestimmten Bereich des Bildes aus.' );
    }

    public static function getParameters()
    {
        return array(
                'width'  => array(
                        'required'    => true,
                        'type'        => 'int',
                        'description' => 'The width of the area to crop'
                ),
                'height' => array(
                        'required'    => true,
                        'type'        => 'int',
                        'description' => 'The height of the area to crop'
                ),
                'align'  => array(
                        'required'    => false,
                        'type'        => 'position',
                        'description' => 'The horizontal position of the crop area'
                ),
                'valign' => array(
                        'required'    => false,
                        'type'        => 'position',
                        'description' => 'The vertical position of the crop area'
                ),
        );
    }

    public static function transform( $img, $params )
    {
        $w     = $img->getImageWidth();
        $h     = $img->getImageHeight();
        $max_w = min( $params[ "width" ], $w );
        $max_h = min( $params[ "height" ], $h );


	    if ( !isset( $params[ "valign" ] ) ) {
		    $params[ "valign" ] = "middle";
	    }

	    if ( $params[ "valign" ] == "top" )
	    {
		    $y = 0;
	    }
	    else if ( $params[ "valign" ] == "bottom" )
	    {
		    $y = $h - $max_h;
	    }
	    else if ( $params[ "valign" ] == "middle" )
	    {
		    $y = floor( ($h - $max_h) / 2 );
	    }
	    else if ( is_numeric($params[ "valign" ]) )
	    {
		    $y = intval($params[ "valign" ]);
	    }



	    if ( !isset( $params[ "align" ] ) ) {
		    $params[ "align" ] = "center";
	    }

	    if ( $params[ "align" ] == "left" )
	    {
		    $x = 0;
	    }
	    else if ( $params[ "align" ] == "right" )
	    {
		    $x = $w - $max_w;
	    }
	    else if ( $params[ "align" ] == "center" )
	    {
		    $x = floor( ($w - $max_w) / 2 );
	    }
	    else if ( is_numeric( $params[ "align" ] ) )
	    {
		    $x = intval($params[ "align" ]);
	    }

        $img->cropImage( $max_w, $max_h, $x, $y );
        return $img;
    }
}

?>