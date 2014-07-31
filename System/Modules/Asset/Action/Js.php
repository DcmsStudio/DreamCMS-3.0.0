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
 * @package      Asset
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Js.php
 */
class Asset_Action_Js extends Controller_Abstract
{

    public function execute()
    {

        $_jsfile = urldecode( $this->input( 'file' ) );

        $mode = explode( '/', $_jsfile );
        if ( $mode[ 0 ] === '' )
        {
            array_shift( $mode );
        }

        if ( $mode[ 0 ] === 'assets' )
        {
            array_shift( $mode );

            $this->processMedia( implode( '/', $mode ) );
        }
        else
        {
            $this->processJs( $_jsfile );
        }
    }

    /**
     *
     * @param string $mediaFile
     */
    private function processJs($mediaFile)
    {

        if ( substr( $mediaFile, 0, 1 ) === '/' )
        {
            $mediaFile = substr( $mediaFile, 1 );
        }


        // redirect to css controller if is a css file
        if ( substr( $mediaFile, -4 ) === '.css' )
        {
            Library::redirect( Settings::get( 'portalurl' ) . '/asset/css/' . $mediaFile );
        }

        $ob = ob_get_clean();

        $mediaFile = preg_replace( '#^asset/js/#', '', $mediaFile );


        $comp = Settings::get( 'compress_js' ) ? '-min' : '';
        $etag = md5( $mediaFile . $comp ); // ETag generieren (md5-Hash)

        if ( isset( $_SERVER[ 'HTTP_IF_NONE_MATCH' ] ) && str_replace( '"', '', stripslashes( $_SERVER[ 'HTTP_IF_NONE_MATCH' ] ) ) == $etag )
        {

            $output = new Output();
            $output->setMode( Output::JAVASCRIPT );

            if (headers_sent())
            {
                $headers = apache_response_headers();
                if ( !empty( $headers ) ) {
                    foreach ( $headers as $name => $value ) {
                        $output->addHeader( $name, $value );
                    }
                }
            }

            ob_end_clean();
            ob_start();


            $output->addHeader( 'Content-Type', 'application/javascript' );
            header( 'HTTP/1.0 304 Not Modified' ); // entsprechenden Header senden => Datei wird nicht geladen

            exit();
        }

        $files = explode( ',', $mediaFile );
        $skip  = false;
        if ( count( $files ) == 1 && strpos( $files[ 0 ], PAGE_CACHE_URL ) !== false )
        {
            if ( is_file( ROOT_PATH . $files[ 0 ] ) )
            {
                $assetcache = file_get_contents( ROOT_PATH . $files[ 0 ] );
                $mtimestr   = gmdate( "D, d M Y H:i:s", filemtime( ROOT_PATH . $files[ 0 ] ) ) . " GMT";
            }
            $skip = true;
            // die($mtimestr);
        }

        if ( !$skip )
        {
            if ( file_exists( PAGE_CACHE_PATH . 'data/assets/' . $etag . $comp . '.js' ) )
            {
                $assetcache = file_get_contents( PAGE_CACHE_PATH . 'data/assets/' . $etag . $comp . '.js' );
                $mtimestr   = gmdate( "D, d M Y H:i:s", filemtime( PAGE_CACHE_PATH . 'data/assets/' . $etag . $comp . '.js' ) ) . " GMT";
            }
            else
            {
                $path       = ROOT_PATH . 'public/html/js/';
                $assetcache = null;


                $tmp = array();
                if ( count( $files ) )
                {
                    $cache = false;

                    foreach ( $files as $file )
                    {
                        if ( trim( $file ) == '' )
                        {
                            continue;
                        }

                        if ( substr( $file, -3 ) === '.js' )
                        {
                            $file = substr( $file, 0, -3 );
                        }


                        if ( substr( $file, 0, 8 ) === 'html/js/' )
                        {
                            $file = substr( $file, 8 );
                        }


                        if ( substr( $file, 0, 1 ) === '/' )
                        {
                            $file = substr( $file, 1 );
                        }


                        if ( substr( $file, 0, 8 ) === 'Modules/' )
                        {
                            $file = MODULES_PATH . substr( $file, 8 );
                        }
                        elseif ( substr( $file, 0, 9 ) === 'Packages/' )
                        {
                            $file = PACKAGES_PATH . substr( $file, 9 );
                        }
                        else if ( substr( $file, 0, 12 ) === 'public/simg/' )
                        {
                            $file = ROOT_PATH . $file;
                        }
                        else if ( substr( $file, 0, 5 ) === 'simg/' )
                        {
                            $file = PUBLIC_PATH . $file;
                        }
                        else if ( substr( $file, 0, 7 ) === 'public/' )
                        {
                            $file = ROOT_PATH . $file;
                        }
                        else if ( substr( $file, 0, 5 ) === 'html/' )
                        {
                            $file = PUBLIC_PATH . $file;
                        }
                        elseif ( substr( $file, 0, 7 ) === 'Vendor/' || substr( $file, 0, strlen( 'public/html/js/' ) ) === 'public/html/js/' )
                        {
                            $file = ROOT_PATH . $file;
                        }
                        else
                        {
                            $file = $path . $file;
                        }


                        if ( file_exists( $file . '.js' ) )
                        {
                            if ( strpos( $file, '.min' ) === false && $comp && strpos( $file, PAGE_URL_PATH ) === false )
                            {
                                $tmp[ ] = Minifier::minifyJs( file_get_contents( $file . '.js' ) );
                            }
                            else
                            {
                                $tmp[ ] = file_get_contents( $file . '.js' );
                            }

                            #$cache = (!$cache ? preg_match('/(jquery\.|dcms[\.-])(.*?)/', $file) : $cache);
                        }
                        else
                        {
                            $tmp[ ] = '/* FILE: ' . $file . '.js not exists! */';
                        }
                    }
                }

                if ( count( $tmp ) )
                {
                    $assetcache = implode( "\n", $tmp );
                    Library::makeDirectory( PAGE_CACHE_PATH . 'data/assets/' );
                    file_put_contents( PAGE_CACHE_PATH . 'data/assets/' . $etag . $comp . '.js', Strings::fixUtf8( $assetcache ) );
                    unset( $tmp );
                }


                $mtimestr = gmdate( "D, d M Y H:i:s", filemtime( PAGE_CACHE_PATH . 'data/assets/' . $etag . $comp . '.js' ) ) . " GMT";
            }

        }

        $cacheStamp = time();
        if ( date( 'Z' ) >= 0 )
        {
            $cacheStamp += date( 'Z' );
        }
        else
        {
            $cacheStamp -= date( 'Z' );
        }





        $output = new Output();
        $output->setMode( Output::JAVASCRIPT );

        if (headers_sent())
        {
            $headers = apache_response_headers();
            if ( !empty( $headers ) ) {
                foreach ( $headers as $name => $value ) {
                    $output->addHeader( $name, $value );
                }
            }
        }

        ob_end_clean();
        ob_start();

        $output->addHeader( 'Content-Type', 'application/javascript' );

        if ( $assetcache === null )
        {
            $assetcache = '/* Javascript File not found! */' . "\n/* File: " . $mediaFile . ' */';
        }
        else
        {
            $output->addHeader( 'Cache-Control', "public, maxage=5184000" );
            $output->addHeader( 'ETag', '"' . $etag . '"' );
            $output->addHeader( 'Pragma', 'public' );
            $output->addHeader( "Vary", "Accept-Encoding" ); // Handle proxies
            $output->addHeader( 'Last-Modified', $mtimestr );
            $output->addHeader( 'Expires', gmdate( "D, d M Y H:i:s", $cacheStamp + 5184000 ) . " GMT" );
        }

        // Add body
        $output->appendOutput( $assetcache );

        // Send
        $output->sendOutput();
        exit;

    }

    /**
     * @param $input
     * @return mixed
     */
    public function jsShrink($input)
    {

        return preg_replace_callback( '(
		(?:
			(^|[-+\([{}=,:;!%^&*|?~]|/(?![/*])|return|throw) # context before regexp
			(?:\s|//[^\n]*+\n|/\*(?:[^*]|\*(?!/))*+\*/)* # optional space
			(/(?![/*])(?:\\\\[^\n]|[^[\n/\\\\]|\[(?:\\\\[^\n]|[^]])++)+/) # regexp
			|(^
				|\'(?:\\\\.|[^\n\'\\\\])*\'
				|"(?:\\\\.|[^\n"\\\\])*"
				|([0-9A-Za-z_$]+)
				|([-+]+)
				|.
			)
		)(?:\s|//[^\n]*+\n|/\*(?:[^*]|\*(?!/))*+\*/)* # optional space
	)sx', 'Asset_Action_Js::jsShrinkCallback', "$input\n" );
    }

    /**
     * @param $match
     * @return string
     */
    public static function jsShrinkCallback($match)
    {

        static $last = '';
        $match += array_fill( 1, 5, null ); // avoid E_NOTICE
        list( , $context, $regexp, $result, $word, $operator ) = $match;
        if ( $word != '' )
        {
            $result = ( $last == 'word' ? "\n" : ( $last == 'return' ? " " : "" ) ) . $result;
            $last   = ( $word == 'return' || $word == 'throw' || $word == 'break' ? 'return' : 'word' );
        }
        elseif ( $operator )
        {
            $result = ( $last == $operator[ 0 ] ? "\n" : "" ) . $result;
            $last   = $operator[ 0 ];
        }
        else
        {
            if ( $regexp )
            {
                $result = $context . ( $context == '/' ? "\n" : "" ) . $regexp;
            }
            $last = '';
        }

        return $result;
    }

    /**
     *
     * @param string $mediaFile
     */
    private function processMedia($mediaFile)
    {

        $etag = md5( $this->Env->requestUri() ); // ETag generieren (md5-Hash)
        $comp = Settings::get( 'compress_js' ) ? '-min' : '';

        if ( isset( $_SERVER[ 'HTTP_IF_NONE_MATCH' ] ) && str_replace( '"', '', stripslashes( $_SERVER[ 'HTTP_IF_NONE_MATCH' ] ) ) == $etag )
        {

            $output = new Output();
            $output->setMode( Output::JAVASCRIPT );

            if (headers_sent())
            {
                $headers = apache_response_headers();
                if ( !empty( $headers ) ) {
                    foreach ( $headers as $name => $value ) {
                        $output->addHeader( $name, $value );
                    }
                }
            }

            ob_end_clean();
            ob_start();


            $output->addHeader( 'Content-Type', 'application/javascript' );

            // Datei/ETag im Browser Cache vorhanden?
            header( 'HTTP/1.0 304 Not Modified' ); // entsprechenden Header senden => Datei wird nicht geladen

            exit();
        }

        $mediaFile = preg_replace( '#^asset/js/#', '', $mediaFile );

        $assetcache = Cache::get( str_replace( '/', '_', $mediaFile ) . $comp, 'data/assets' );

        if ( !$assetcache )
        {
            $media = Media::getAsset( $mediaFile, true );


            if ( Settings::get( 'compress_js' ) )
            {
                $media[ 'content' ] = Asset_Helper_Base::removeComments( $media[ 'content' ] );
            }


            Cache::write( str_replace( '/', '_', $mediaFile ) . '-' . $comp, $media[ 'content' ], 'data/assets' );
            $assetcache = $media[ 'content' ];
        }

        $mtimestr = gmdate( "D, d M Y H:i:s", TIMESTAMP ) . " GMT";

        $output = new Output();
        $output->setMode( Output::JAVASCRIPT );
        $output->addHeader( 'Content-Type', 'application/javascript' );


        if ( empty( $assetcache ) || $assetcache === null )
        {
            $assetcache = '/* Asset Javascript File not exists! */';
        }
        else
        {
            $output->addHeader( 'Cache-Control', 'must-revalidate, proxy-revalidate, private' );
            $output->addHeader( 'ETag', '"' . $etag . '"' );
            $output->addHeader( 'Pragma', 'public' );
            $output->addHeader( 'Last-Modified', $mtimestr );
            $output->addHeader( "Vary", "Accept-Encoding" ); // Handle proxies
            $output->addHeader( 'Expires', gmdate( "D, d M Y H:i:s", TIMESTAMP + 5184000 ) . " GMT" );
        }

        // Add body
        $output->appendOutput( Strings::fixUtf8( $assetcache ) );

        // Send
        $output->sendOutput();
        exit;

    }

}

?>