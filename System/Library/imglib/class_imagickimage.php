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
 * @file        class_imagickimage.php
 */
class ImagickImage extends ImageTransformer
{

    protected function loadImage()
    {
        if ( !$this->sourceImageType || !$this->sourceImagePath )
        {
            return false;
        }


        $image = new Imagick( $this->sourceImagePath );
        $image->setImageFormat( $this->outputImageType );
        $image->setImageResolution( 72, 72 );
        return $image;
    }

    protected function getErrorImage()
    {
        $image_width  = 200;
        $image_height = 0;
        $text         = "Cannot find source image.";
        $im           = new Imagick();
        $im->newPseudoImage( $image_width, $image_height, "caption:" . $text );
        /* Put 1px border around the image */
        $im->borderImage( 'black', 1, 1 );
        $im->setImageFormat( "png" );
        return $im;
    }

    protected function writeToCache()
    {
        $this->image->stripImage();
        if ( $this->outputImageType == 'jpeg' )
        {
            $this->image->setImageCompression( Imagick::COMPRESSION_JPEG );
            $this->image->setImageCompressionQuality( $this->outputImageQuality );
        }
        elseif ( $this->outputImageType == 'png' )
        {
            if ( $this->image->getImageType() == 2 )
            {
                //$this->image->setImageType(Imagick::IMGTYPE_PALETTE);
            }
            $this->image->setImageCompression( Imagick::COMPRESSION_ZIP );
            $this->image->setImageCompressionQuality( $this->outputImageQuality );
        }



        $this->image->writeImage( $this->outputImagePath );
    }

    public function output( $params )
    {
        $params[ 'write' ] = false;
        $params[ 'cache' ] = false;
        $data              = $this->process( $params );
        $this->image->stripImage();
        if ( $this->outputImageType == 'jpeg' )
        {

            // set background to 'replace' transparency
            $background  = new imagick();
            $background->newImage( $this->image->getImageWidth(), $this->image->getImageheight(), $this->bgcolor, 'jpeg' );
            $background->compositeImage( $this->image, Imagick::COMPOSITE_DEFAULT, 0, 0 );
            $this->image = $background;

            $this->image->setImageCompression( Imagick::COMPRESSION_JPEG );
            $this->image->setImageCompressionQuality( $this->outputImageQuality );
        }
        elseif ( $this->outputImageType == 'png' )
        {
            $this->image->setImageCompression( Imagick::COMPRESSION_ZIP );
            $this->image->setImageCompressionQuality( $this->outputImageQuality );
        }
        $this->image->setImageFormat( $this->outputImageType );
        $this->image->setFormat( $this->outputImageType );
        header( "Content-Type: image/" . $this->outputImageType );
        echo $this->image->getImageBlob();
        exit();
    }
    /*
      public function text($params) {
      if($params!==false) {
      $type   = !empty($params['output']) ? $params['output'] : false ;
      $chain  = !empty($params['chain']) ? $params['chain'] : false ;
      if(isset($params['cache'])) {
      $this->setCaching($params['cache']);
      }
      if(isset($params['quality'])) {
      $this->setQuality($params['quality']);
      }
      if(isset($params['callback'])) {
      $this->setCallback($params['callback']);
      }
      $this->outputImageType='png';
      $this->sourceImagePath = $params['text'];
      $this->outputImagePath = $this->getOutputImagePath();
      } else {
      throw new ImageException('Missing configuration data for text manipulation');
      }
      if(!$this->imageIsTransformed()) {
      $im = new Imagick();
      $im->setFont($params['chain']['font']);
      $im->newPseudoImage($params['chain']['width'], $params['chain']['height'], "caption:" . $params['text'] );
      $im->setImageFormat("png");
      $this->outputImageType='png';
      $this->sourceImagePath = $params['text'];
      $this->outputImagePath = $this->getOutputImagePath();
      $this->image = $im;
      $this->writeToCache();
      }
      $data = getimagesize($this->outputImagePath);
      $data['width'] = $data[0];
      $data['height'] = $data[1];
      unset($data[0], $data[1], $data[2], $data[3], $data['channels'], $data['bits']);
      $data['path'] = $this->outputImagePath;
      $data['size'] = filesize($data['path']);
      $data['library'] = IMAGE_LIBRARY;
      if($this->callback!==false) {
      $data = call_user_func_array($this->callback, array($data));
      }
      return $data;
      }
     */
}

?>