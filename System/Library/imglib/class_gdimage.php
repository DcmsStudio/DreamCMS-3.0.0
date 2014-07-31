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
 * @file        class_gdimage.php
 */
class GDImage extends ImageTransformer
{

    protected function loadImage()
    {

        if ( !$this->sourceImageType )
        {
            return false;
        }

        $loaderFunction = 'imagecreatefrom' . $this->sourceImageType;

        if ( function_exists( $loaderFunction ) )
        {
            return call_user_func_array( $loaderFunction, array( $this->sourceImagePath ) );
        }
        else
        {
            throw new BaseException( 'GD function "imagecreatefrom' . $this->sourceImageType . '" not exists!' );
            return false;
        }
    }

    protected function writeToCache()
    {
        $saveFunction = 'image' . strtolower( $this->outputImageType );
        if ( !function_exists( $saveFunction ) )
        {
            throw new ImageException( 'Image saving function `' . $saveFunction . '` does not exist.' );
        }
        if ( $this->outputImageType == 'jpeg' )
        {
            call_user_func_array( $saveFunction, array( $this->image, $this->outputImagePath, $this->outputImageQuality ) );
        }
        elseif ( $this->outputImageType == 'png' )
        {
            imagesavealpha( $this->image, true );
            $quality = 10 - ceil( ($this->outputImageQuality / 10 ) );
            $quality = $quality < 1 ? 1 : $quality;
            $quality = $quality > 9 ? 9 : $quality;
            call_user_func_array( $saveFunction, array( $this->image, $this->outputImagePath, $quality ) );
        }
        else
        {
            call_user_func_array( $saveFunction, array( $this->image, $this->outputImagePath ) );
        }
    }

    public function output( $params )
    {
        $params[ 'write' ] = false;
        $params[ 'cache' ] = false;
        $data              = $this->process( $params );
        $saveFunction      = 'image' . strtolower( $this->outputImageType );

        if ( !function_exists( $saveFunction ) )
        {
            throw new ImageException( 'Image saving function `' . $saveFunction . '` does not exist.' );
        }

        #  header("Content-Type: image/" . $this->outputImageType);

        if ( $this->outputImageType == 'jpeg' )
        {
            call_user_func_array( $saveFunction, array( $this->image, NULL, $this->outputImageQuality ) );
        }
        elseif ( $this->outputImageType == 'png' )
        {
            imagesavealpha( $this->image, true );
            $quality = 10 - ceil( ($this->outputImageQuality / 10 ) );
            $quality = $quality < 1 ? 1 : $quality;
            $quality = $quality > 9 ? 9 : $quality;
            call_user_func_array( $saveFunction, array( $this->image, NULL, $quality ) );
        }
        else
        {
            call_user_func_array( $saveFunction, array( $this->image, NULL ) );
        }
        exit();
    }
}

?>