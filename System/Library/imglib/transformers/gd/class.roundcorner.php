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
 * @file        class.roundcorner.php
 */
class ImageTransformationRoundcorner
{

    private static function limit( $val, $c1, $c2 )
    {
        return min( max( $val, min( $c1, $c2 ) ), max( $c1, $c2 ) );
    }

    private static function color( $color, $opacity )
    {
        $opacity = self::limit( $opacity, 0, 1 );
        #list($red, $green, $blue) = ImageTools::htmlcolor2rgb($color);

        preg_match( '/^([a-fA-F0-9]{2})([a-fA-F0-9]{2})([a-fA-F0-9]{2})$/', $color, $m );
        $red   = hexdec( $m[ 1 ] );
        $green = hexdec( $m[ 2 ] );
        $blue  = hexdec( $m[ 3 ] );


        $data = array(
                'red'     => $red,
                'green'   => $green,
                'blue'    => $blue,
                'opacity' => $opacity
        );
        return $data;
    }

    private static function getColorResource( &$image, $color )
    {
        return imagecolorallocatealpha( $image, (isset( $color[ 'red' ] ) ? $color[ 'red' ] : 0 ), (isset( $color[ 'green' ] ) ? $color[ 'green' ] : 0 ), (isset( $color[ 'blue' ] ) ? $color[ 'blue' ] : 0 ), 127 * (1 - (isset( $color[ 'opacity' ] ) ? $color[ 'opacity' ] : 0) ) );
    }

    private static function corner( $params )
    {

        if ( is_array( $params ) )
        {
            extract( $params );
        }


        $radius      = max( intval( $radius ), 1 );
        $borderwidth = self::limit( $borderwidth, 0, $radius );
        $orientation = strtolower( $orientation );

        $image = imagecreatetruecolor( $radius, $radius );
        imagealphablending( $image, false );

        self::drawCorner( $image, $radius, $borderwidth, $orientation, $colors, $antialias );

        switch ( $orientation )
        {
            case 'br' :
            case 'rb' :
                break;
            case 'bl' :
            case 'lb' :
                self::imageFlipHorizontal( $image );
                break;
            case 'tr' :
            case 'rt' :
                self::imageFlipVertical( $image );
                break;
            case 'tl' :
            case 'lt' :
            default :
                self::imageFlipHorizontal( $image );
                self::imageFlipVertical( $image );
                break;
        }

        return $image;
    }

    private static function drawCorner( &$image, $radius, $borderwidth, $orientation, $colors, $antialias )
    {
        $c = self::getColorResource( $image, $colors[ 'background' ] );
        imagefilledrectangle( $image, 0, 0, $radius - 1, $radius - 1, $c );

        if ( $borderwidth > 0 )
        {

            $c = self::getColorResource( $image, $colors[ 'border' ] );


            imagefilledellipse( $image, 0, 0, ($radius - 1) * 2, ($radius - 1) * 2, $c );
            self::drawAA( $image, $radius, $colors[ 'border' ], $colors[ 'background' ], $antialias );
        }

        if ( $radius - $borderwidth > 0 )
        {
            $c = self::getColorResource( $image, $colors[ 'foreground' ] );


            imagefilledellipse( $image, 0, 0, ($radius - $borderwidth - 1) * 2, ($radius - $borderwidth - 1) * 2, $c );
            if ( $borderwidth > 0 )
                self::drawAA( $image, $radius - $borderwidth, $colors[ 'foreground' ], $colors[ 'border' ], $antialias );
            else
                self::drawAA( $image, $radius, $colors[ 'foreground' ], $colors[ 'background' ], $antialias );
        }
    }

    private static function drawAA( &$image, $r, $c1, $c2, $antialias )
    {
        if ( !$antialias )
            return;

        $px = array_fill( 0, $r, array_fill( 0, $r, false ) );

        for ( $x = 0; $x < $r; $x++ )
        {
            for ( $y = ceil( self::loc( $x, $r ) ) - 1; $y > -1; $y-- )
            {
                if ( $px[ $x ][ $y ] )
                    return;

                if ( self::isInside( $x + 1, $y + 1, $r ) )
                    break;


                $color = self::blendColors( $c1, $c2, self::computeRatio( $x, $y, $r, $antialias ) );
                $c     = self::getColorResource( $image );

                imagesetpixel( $image, $x, $y, $c );
                $px[ $x ][ $y ] = true;

                if ( $x <> $y )
                {
                    imagesetpixel( $image, $y, $x, $c );
                    $px[ $y ][ $x ] = true;
                }
            }
        }
    }

    private static function computeRatio( $x, $y, $r, $antialias )
    {
        if ( !$antialias )
            return 1;

        $x_a = min( $x + 1, self::loc( $y, $r ) );
        $x_b = max( $x, self::loc( $y + 1, $r ) );
        return self::area( $x_a, $r ) - self::area( $x_b, $r ) + $x_b - $x - $y * ($x_a - $x_b);
    }

    /**
     * BlendColors
     *
     * Blends 2 colors, giving attention to both
     * the ratio of color amounts, and the opacity
     * level of each color
     *
     * @access	private
     * @param	Color	$c1	1st color
     * @param	Color	$c2	2nd color
     * @param	float	$r	ratio of blend (0.7 means 70% of color 1)
     * @return array
     */
    private static function blendColors( $c1, $c2, $r )
    {
        $o1 = $c1[ 'opacity' ] * $r;
        $o2 = $c2[ 'opacity' ] * (1 - $r);
        $o  = $o1 + $o2;

        $o_r = $o == 0 ? 0 : $o2 / $o;

        $r = str_pad( dechex( $c1[ 'red' ] - $o_r * ($c1[ 'red' ] - $c2[ 'red' ]) ), 2, '0', STR_PAD_LEFT );
        $g = str_pad( dechex( $c1[ 'green' ] - $o_r * ($c1[ 'green' ] - $c2[ 'green' ]) ), 2, '0', STR_PAD_LEFT );
        $b = str_pad( dechex( $c1[ 'blue' ] - $o_r * ($c1[ 'blue' ] - $c2[ 'blue' ]) ), 2, '0', STR_PAD_LEFT );

        return self::color( $r . $g . $b, $o );
    }

    /**
     * Area
     *
     * Given a value for x = n, computes the area under a circular arc
     * from x = 0 -> n, with the cirle centerd at the orgin
     *
     * @access	public
     * @param	int		$x	x-coordinate for the pixel
     * @param	int		$r	radius of the arc
     * @return	float	area under the arc
     */
    private static function area( $x, $r )
    {
        return ($x * self::loc( $x, $r ) + $r * $r * asin( $x / $r )) / 2;
    }

    /**
     * IsInside
     *
     * Helper method to determine if a coordinate lies inside
     * of the arc.
     *
     * @access	public
     * @param	int		$x	x-coordinate
     * @param	int		$y	y-coordinate
     * @param	int		$r	radius of the arc
     * @return	bool	true if coordinate lies inside bounds of arc
     */
    private static function isInside( $x, $y, $r )
    {
        return $x * $x + $y * $y <= $r * $r;
    }

    /**
     * LawOfCosines (loc)
     *
     * Used to calculate length of opposite side
     * of a right triangle, given the length of the
     * hypotenuse and one side.
     *
     * @access	public
     * @param	int		$xy		Length of either side of the right triangle
     * @param	int		$h		Length of the hypotenuse
     * @return	int		Length of the unknown side
     */
    private static function loc( $xy, $r )
    {
        return sqrt( $r * $r - $xy * $xy );
    }

    /**
     * ImageFlipHorizontal
     *
     * Flip an image horizontally
     *
     * @access	public
     * @param	image	$old	image resource for original image
     * @return	void
     */
    private static function imageFlipHorizontal( &$old )
    {
        $w   = imagesx( $old );
        $h   = imagesy( $old );
        $new = imagecreatetruecolor( $w, $h );
        imagealphablending( $new, false );
        for ( $x = 0; $x < $w; $x++ )
            imagecopy( $new, $old, $x, 0, $w - $x - 1, 0, 1, $h );
        $old = $new;
    }

    /**
     * ImageFlipVertical
     *
     * Flip an image vertically
     *
     * @access	public
     * @param	image	$old	image resource for original image
     * @return	void
     */
    private static function imageFlipVertical( &$old )
    {
        $w   = imagesx( $old );
        $h   = imagesy( $old );
        $new = imagecreatetruecolor( $w, $h );
        imagealphablending( $new, false );
        for ( $y = 0; $y < $h; $y++ )
            imagecopy( $new, $old, 0, $y, 0, $h - $y - 1, $w, 1 );
        $old = $new;
    }

    public static function transform( $gd, $params )
    {

        $corner_radius = !empty( $params[ 'x_rounding' ] ) ? $params[ 'x_rounding' ] : 20; // The default corner radius is set to 20px
        $angle         = 0; // The default angle is set to 0�


        $topleft     = (!isset( $params[ 'topleft' ] ) || $params[ 'topleft' ] == "") ? false : true; // Top-left rounded corner is shown by default
        $bottomleft  = (!isset( $params[ 'bottomleft' ] ) || $params[ 'bottomleft' ] == "") ? false : true; // Bottom-left rounded corner is shown by default
        $bottomright = (!isset( $params[ 'bottomright' ] ) || $params[ 'bottomright' ] == "") ? false : true; // Bottom-right rounded corner is shown by default
        $topright    = (!isset( $params[ 'topright' ] ) || $params[ 'topright' ] == "") ? false : true; // Top-right rounded corner is shown by default


        if ( !$corner_radius )
        {
            return $gd;
        }

        /*
          $foregroundcolor = 'FFFFFF';
          $backgroundcolor = 'FF0000';
          $bordercolor = '000000';
          $backgroundopacity = 10;
          $borderopacity = 100;
          $foregroundopacity = 10;

          $borderwidth = 1;
          $orientation = 'tl';

          $x_orig = imagesx($gd);
          $y_orig = imagesy($gd);

          $corner_radius = 20;
          $shape = 'c';



          $x_orig = max($x_orig, 2);
          $y_orig = max($y_orig, 2);
          $corner_radius = self::limit($corner_radius, 1, floor(min($x_orig, $y_orig) / 2));
          $borderwidth = self::limit($borderwidth, 0, ceil(min($x_orig, $y_orig) / 2));



          $params = array(
          'radius'			=> $corner_radius,
          'width'				=> $x_orig,
          'height'			=> $y_orig,
          'borderwidth'		=> $borderwidth,
          'orientation'		=> $orientation,
          'side'				=> 'top',
          'antialias'			=> true,
          'colors'			=> array(
          'foreground'	=> self::color($foregroundcolor, $foregroundopacity / 100),
          'border'		=> self::color($bordercolor, $borderopacity / 100),
          'background'	=> self::color($backgroundcolor, $backgroundopacity / 100)
          )
          );


          $image = imagecreatetruecolor($x_orig, $y_orig);
          imagealphablending($image, false);
          imagesavealpha($image, true);

          $color = self::getColorResource($image, $params['colors']['border']);




          imagefilledrectangle($image, 0, 0, $x_orig - 1, $y_orig - 1, $color);


          if ($borderwidth < min($x_orig, $y_orig) / 2)
          {
          $color = self::getColorResource($image, $params['colors']['foreground']);
          imagefilledrectangle($image, $borderwidth, $borderwidth, $x_orig - $borderwidth - 1, $y_orig - $borderwidth - 1, $color);

          imagecopyresampled($image, $gd, $borderwidth, $borderwidth, 0, 0, $x_orig - $borderwidth - 1, $y_orig - $borderwidth - 1, $x_orig, $y_orig);
          }


          // Create Corners
          $_params = array(
          'radius'		=> $corner_radius,
          'orientation'	=> 'tl',
          'colors'		=> $params['colors'],
          'borderwidth'	=> $borderwidth,
          'antialias'		=> false
          );

          $img = self::corner($_params);
          imagecopy($image, $img, 0, 0, 0, 0, $corner_radius, $corner_radius);

          self::imageFlipVertical($img);
          imagecopy($image, $img, 0, $y_orig - $corner_radius, 0, 0, $corner_radius, $corner_radius);

          self::imageFlipHorizontal($img);
          imagecopy($image, $img, $x_orig - $corner_radius, $y_orig - $corner_radius, 0, 0, $corner_radius, $corner_radius);

          self::imageFlipVertical($img);
          imagecopy($image, $img, $x_orig - $corner_radius, 0, 0, 0, $corner_radius, $corner_radius);









          imagedestroy($gd);
          return $image;












         */


















        $bwidth = $corner_radius;

        $x_orig      = imagesx( $gd );
        $y_orig      = imagesy( $gd );
        $width_orig  = $x_orig;
        $height_orig = $y_orig;

        $mask = imagecreatetruecolor( $width_orig, $height_orig );

        $white = imagecolorallocate( $mask, 255, 255, 255 );
        imagecolortransparent( $mask, $white );

        imagealphablending( $mask, true );


        #$white = imagecolorallocatealpha($mask, 255, 255, 255);

        imagefill( $mask, 0, 0, $white );
        $cornerImg = imagecreatetruecolor( $bwidth, $bwidth );
        imagealphablending( $cornerImg, false );

        // Find Border
        $border_colour = imagecolorsforindex( $gd, imagecolorat( $gd, 0, 0 ) );


        $transp = imagecolorallocatealpha( $cornerImg, 0, 0, 255, 127 );
        imagefill( $cornerImg, 0, 0, $transp );
        imagecolortransparent( $cornerImg, $transp );



        $bgc = imagecolorallocatealpha( $cornerImg, 0, 0, 0, 127 );
        imagefilledellipse( $cornerImg, $bwidth, $bwidth, $bwidth * 2, $bwidth * 2, $bgc );
        imagecolortransparent( $cornerImg, $bgc );
        imagealphablending( $cornerImg, false );


        if ( $topleft )
            imagecopymerge( $mask, $cornerImg, 0, 0, 0, 0, $bwidth, $bwidth, 100 );
        $cornerImg = imagerotate( $cornerImg, 270, 0 );

        if ( $topright )
            imagecopymerge( $mask, $cornerImg, $width_orig - $bwidth, 0, 0, 0, $bwidth, $bwidth, 100 );
        $cornerImg = imagerotate( $cornerImg, 270, 0 );

        if ( $bottomright )
            imagecopymerge( $mask, $cornerImg, $width_orig - $bwidth, $height_orig - $bwidth, 0, 0, $bwidth, $bwidth, 100 );
        $cornerImg = imagerotate( $cornerImg, 270, 0 );

        if ( $bottomleft )
            imagecopymerge( $mask, $cornerImg, 0, $height_orig - $bwidth, 0, 0, $bwidth, $bwidth, 100 );

        $newImage = imagecreatetruecolor( $width_orig, $height_orig );

        imagealphablending( $newImage, true );


        // $allocatedAlpha = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
        $allocatedAlpha = imagecolorallocate( $newImage, 254, 254, 254 );
        imagecolortransparent( $newImage, $allocatedAlpha );
        imagefill( $newImage, 0, 0, $allocatedAlpha );

        for ( $x = 0; $x < $width_orig; $x++ )
        {
            for ( $y = 0; $y < $height_orig; $y++ )
            {
                $alpha = imagecolorsforindex( $mask, imagecolorat( $mask, $x, $y ) );
                $alpha = 127 - floor( $alpha[ 'red' ] / 2 );

                $colour = imagecolorsforindex( $gd, imagecolorat( $gd, $x, $y ) );
                imagesetpixel( $newImage, $x, $y, imagecolorallocatealpha( $newImage, $colour[ 'red' ], $colour[ 'green' ], $colour[ 'blue' ], $alpha ) );
            }
        }

        imagedestroy( $gd );


        $transp = imagecolorallocate( $newImage, 0, 0, 255 );
        imagecolortransparent( $newImage, $transp );

        $transp = imagecolorallocate( $cornerImg, 0, 0, 255 );
        imagecolortransparent( $newImage, $transp );

        imagedestroy( $cornerImg );

        return $newImage;




















        imagealphablending( $gd, false );
        imagesavealpha( $gd, true );


        $corner_image = ImageTools::getNewImage( $x_orig, $y_orig );

        $bgcol = imagecolorallocatealpha( $corner_image, 0, 0, 0, 127 );
        imagecolortransparent( $corner_image, $bgcol );
        imagefill( $corner_image, 0, 0, $bgcol );


        imagefilledellipse( $corner_image, $corner_radius, $corner_radius, $corner_radius * 2, $corner_radius * 2, $bgcol );

        imagecopymerge( $gd, $corner_image, 0, 0, 0, 0, $corner_radius, $corner_radius, 100 );
        $corner_image = imagerotate( $corner_image, 90, 0 );

        imagecopymerge( $gd, $corner_image, 0, $x_orig - $corner_radius, 0, 0, $corner_radius, $corner_radius, 100 );
        $corner_image = imagerotate( $corner_image, 90, 0 );

        imagecopymerge( $gd, $corner_image, $x_orig - $corner_radius, $y_orig - $corner_radius, 0, 0, $corner_radius, $corner_radius, 100 );
        $corner_image = imagerotate( $corner_image, 90, 0 );


        imagecopymerge( $gd, $corner_image, 0, $x_orig - $corner_radius, 0, 0, $corner_radius, $corner_radius, 100 );





        // imagedestroy($gd);
        imagedestroy( $corner_image );

        return $gd;


























        $corner_source = imagecreatefrompng( HTML_PATH . 'img/rounded_corner.png' );
        imagealphablending( $corner_source, false );
        imagesavealpha( $corner_source, true );

        $corner_width   = imagesx( $corner_source );
        $corner_height  = imagesy( $corner_source );
        $corner_resized = ImageCreateTrueColor( $corner_radius, $corner_radius );

        imagealphablending( $corner_resized, false );
        imagesavealpha( $corner_resized, true );


        imagefill( $corner_resized, 0, 0, imagecolorallocatealpha( $corner_resized, 0, 0, 0, 127 ) );
        //imagecolortransparent($corner_resized, imagecolorallocatealpha($corner_resized,0,0,0,127) ); 

        ImageCopyResampled( $corner_resized, $corner_source, 0, 0, 0, 0, $corner_radius, $corner_radius, $corner_width, $corner_height );


        $corner_width  = imagesx( $corner_resized );
        $corner_height = imagesy( $corner_resized );
        #	$image = imagecreatetruecolor($corner_width, $corner_height);  
        //$image = imagecreatefromjpeg($images_dir . $image_file); // replace filename with $_GET['src'] 
        $image         = $gd;


        //$size = getimagesize($images_dir . $image_file); // replace filename with $_GET['src'] 
        $white = imagecolorallocatealpha( $image, 0, 0, 0, 127 );
        $black = imagecolorallocatealpha( $image, 0, 0, 0, 127 );


        // Top-left corner
        if ( $topleft == true )
        {
            $dest_x = 0;
            $dest_y = 0;
            imagecolortransparent( $corner_resized, $black );
            imagecopymerge( $image, $corner_resized, $dest_x, $dest_y, 0, 0, $corner_width, $corner_height, 100 );
        }

        // Bottom-left corner
        if ( $bottomleft == true )
        {
            $dest_x  = 0;
            $dest_y  = $y_orig - $corner_height;
            $rotated = imagerotate( $corner_resized, 90, 0 );
            imagecolortransparent( $rotated, $black );
            imagecopymerge( $image, $rotated, $dest_x, $dest_y, 0, 0, $corner_width, $corner_height, 100 );
        }

        // Bottom-right corner
        if ( $bottomright == true )
        {
            $dest_x  = $x_orig - $corner_width;
            $dest_y  = $y_orig - $corner_height;
            $rotated = imagerotate( $corner_resized, 180, 0 );
            imagecolortransparent( $rotated, $black );
            imagecopymerge( $image, $rotated, $dest_x, $dest_y, 0, 0, $corner_width, $corner_height, 100 );
        }

        // Top-right corner
        if ( $topright == true )
        {
            $dest_x  = $x_orig - $corner_width;
            $dest_y  = 0;
            $rotated = imagerotate( $corner_resized, 270, 0 );
            imagecolortransparent( $rotated, $black );
            imagecopymerge( $image, $rotated, $dest_x, $dest_y, 0, 0, $corner_width, $corner_height, 100 );
        }

        // Rotate image
        $image = imagerotate( $image, $angle, -1 );

        imagedestroy( $gd );
        imagedestroy( $corner_resized );

        return $image;
    }
}

?>