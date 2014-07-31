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
 * @file         Css.php
 */
class Asset_Action_Css extends Controller_Abstract
{

	public function execute ()
	{

		$_cssfile = $this->input('file');

		// redirect to css controller if is a css file
		if ( substr($_cssfile, -3) === '.js' )
		{
			Library::redirect(Settings::get('portalurl') . '/asset/js/' . $_cssfile);
		}


		// redirect to css controller if is a image file
		if ( in_array(substr($_cssfile, -4), array (
		                                           '.gif',
		                                           '.png',
		                                           '.jpg',
		                                           '.jpe',
		                                           '.tif'
		                                     ))
		)
		{
			Library::redirect(Settings::get('portalurl') . '/' . $_cssfile);
		}




        if (substr($_cssfile, 0, 7) == '/asset/') {
            $_cssfile = substr($_cssfile, 7);

            if (substr($_cssfile, 0, 4) == 'css/') {
                $_cssfile = substr($_cssfile, 4);
            }
        }






		$mode = explode('/', $_cssfile);

		if ( $mode[ 0 ] === '' )
		{
			array_shift($mode);
		}


		if ( $mode[ 0 ] === 'assets' )
		{
			array_shift($mode);
			$this->processMediaCss(implode('/', $mode));
		}
		else
		{
			$this->processCss($_cssfile);
		}


		die("HELLO I´m a CSS File " . $_cssfile);
	}

	/**
	 * @param $mediaFile
	 */
	private function processCss ( $mediaFile )
	{
		if ( substr($mediaFile, 0, 1) === '/' )
		{
			$mediaFile = substr($mediaFile, 1);
		}

		$etag = md5($mediaFile); // ETag generieren (md5-Hash)

		if ( isset($_SERVER[ 'HTTP_IF_NONE_MATCH' ]) && str_replace( '"', '', stripslashes( $_SERVER[ 'HTTP_IF_NONE_MATCH' ] ) ) == $etag )
		{
            $output = new Output();
            $output->setMode( Output::CSS );

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


            header('HTTP/1.0 304 Not Modified'); // entsprechenden Header senden => Datei wird nicht geladen

			exit();
		}

        $doCache = true;
		$code  = '';
		$files = explode(',', $mediaFile);
		foreach ( $files as $file )
		{
			if ( trim($file) === '' )
			{
				continue;
			}

			if ( substr($file, -4) !== '.css' && substr($file, -4) !== '.php' )
			{
				$file .= '.css';
			}

			$inFile = $file;

			if ( substr($file, 0, 1) === '/' )
			{
				$file = substr($file, 1);
			}

			if ( substr($file, 0, 7) === 'Vendor/' )
			{
				$file = ROOT_PATH . $file;
			}
            else if ( substr($file, 0, 9) === 'Packages/' )
            {
                $file = ROOT_PATH .'System/'. $file;
            }
            else if ( substr($file, 0, 7) === 'public/')
            {
                $file = ROOT_PATH . $file;
            }
            else if ( substr($file, 0, 5) === 'html/' || substr($file, 0, 5) === 'simg/'  )
            {
                $file = PUBLIC_PATH . $file;
            }
			else
			{
				$file = PUBLIC_PATH . 'html/' . $file;
			}



			if ( file_exists($file) )
			{
				$c = file_get_contents($file);

				$path = explode('/', $file);
				array_pop($path);
				$curentPath = implode('/', $path) . '/';

                if (strpos($curentPath, PAGE_URL_PATH) === false) {
                    $code .= Minifier::minifyCss($c, $curentPath);
                }
                else {
                    $code .= $c;
                }


                //$code .= self::getCssImports($c, $curentPath);
			}
			else
			{
				$code .= '/* CSS File not exists! (' . $inFile . ') 1 */';
                $doCache = false;
			}
		}



        $mtime = TIMESTAMP;

		if ( $code && $doCache )
		{

			Library::makeDirectory(PAGE_CACHE_PATH . 'data/assets/');
			file_put_contents(PAGE_CACHE_PATH . 'data/assets/' . $etag . '.css', $code);
            $mtime = filemtime(PAGE_CACHE_PATH . 'data/assets/' . $etag . '.css');
		}

        $mtimestr = gmdate("D, d M Y H:i:s", $mtime) . " GMT";

		$cacheStamp = time();
		if ( date('Z') >= 0 ) {
			$cacheStamp += date('Z');
		}
		else {
			$cacheStamp -= date('Z');
		}


		$output = new Output();
		$output->setMode( Output::CSS );



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


		$output->addHeader( 'Content-Type', 'text/css' );
        $output->addHeader( 'Cache-Control', 'public, maxage=5184000');
		$output->addHeader( 'Last-Modified', $mtimestr );
		$output->addHeader( 'ETag', '"'.$etag .'"');
        $output->addHeader( 'Pragma', 'public' );
        $output->addHeader( "Vary", "Accept-Encoding" ); // Handle proxies
		$output->addHeader( 'Expires', gmdate("D, d M Y H:i:s", $cacheStamp + 5184000) . " GMT" );

		// Add body
		$output->appendOutput($code);

		// Send
		$output->sendOutput();
        exit();

	}

	/**
	 * @param $mediaFile
	 */
	private function processMediaCss ( $mediaFile )
	{
		$etag = md5(REQUEST); // ETag generieren (md5-Hash)

		if (  isset($_SERVER[ 'HTTP_IF_NONE_MATCH' ]) && str_replace( '"', '', stripslashes( $_SERVER[ 'HTTP_IF_NONE_MATCH' ] ) ) == $etag )
		{
            $output = new Output();
            $output->setMode( Output::CSS );

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
            header('HTTP/1.0 304 Not Modified'); // entsprechenden Header senden => Datei wird nicht geladen

			exit();
		}

        $mediaFile = preg_replace('#(../|./)#', '', $mediaFile);
        $mediaContent = Cache_Filecache::get($mediaFile, 'data/assets/');

		if ( !$mediaContent )
		{
            preg_match('#\.([a-z0-9]+)$#i', $mediaFile, $match);

			$media      = Media::getAsset($mediaFile, ($match[1] ? true : false) );
            if (isset($media[ 'content' ]))
            {
                $mediaContent = $media[ 'content' ];
                Cache_Filecache::set($mediaFile, $mediaContent, 'data/assets');
            }

            unset($media);
		}

		if ( !$mediaContent )
		{
            $mediaContent = '/* Asset File not exists! */';
		}

		$output = new Output();
		$output->setMode( Output::CSS );

		$output->addHeader( 'Content-Type', 'text/css' );
        $output->addHeader( 'Cache-Control', 'public');
        $output->addHeader( 'ETag', '"'.$etag .'"');
        $output->addHeader( 'Pragma', 'public' );
        $output->addHeader( "Vary", "Accept-Encoding" ); // Handle proxies
		$output->addHeader( 'Last-Modified', gmdate("D, d M Y H:i:s", time()) . " GMT" );
        $output->addHeader( 'Expires', gmdate("D, d M Y H:i:s", TIMESTAMP + 5184000) . " GMT" );


		// Add body
		$output->appendOutput($mediaContent);

		// Send
		$output->sendOutput();
        exit();


	}

	/**
	 *
	 * @param string $code
	 * @param string $currentPath
	 * @return string
	 */
	private static function fixCssUrlRule ( $code, $currentPath )
	{

		$cachePath     = PAGE_URL_PATH . '.cache/data/assets/';
		$backwardsBase = substr_count($cachePath, '/');
		$baseUrl       = Settings::get('portalurl');

		$backPath = '';
		if ( $backwardsBase )
		{
			$backPath = str_repeat("../", $backwardsBase);
		}


		$_currentPath = str_replace(PUBLIC_PATH, '', $currentPath);

		if ( substr($_currentPath, -1) != '/' )
		{
			$_currentPath .= '/';
		}


		preg_match_all('#(url\(([^\)]*)\))#isU', $code, $matches);

		if ( $matches[ 0 ] && is_array($matches[ 2 ]) )
		{
			//  print_r( $matches );

			foreach ( $matches[ 2 ] as $finput )
			{
				$f = str_replace('"', '', $finput);
				$f = str_replace('\'', '', $f);

				if ( preg_match('#data:image#', $f) )
				{
					continue;
				}

				$backwards = substr_count($f, '..');

				if ( $backwards )
				{

					$curr = explode('/', $_currentPath);

					# $p = array();
					for ( $i = 0; $i < $backwards + 1; ++$i )
					{
						$pop = array_pop($curr);
					}

					$__currentPath = implode('/', $curr);

					if ( substr($__currentPath, -1) != '/' )
					{
						$__currentPath .= '/';
					}

					$f = '/' . $__currentPath . str_replace(array (
					                                              '../',
					                                              './'
					                                        ), '', $f);
				}
				else
				{

				}

				$f    = '"' . $backPath . $f . '"';
				$f    = str_replace('//', '/', $f);
				$f    = preg_replace('#/\./#', '/', $f);
				$code = preg_replace('#url\(' . preg_quote($finput, '#') . '\)#', 'url(' . $f . ')', $code, 1);

				/*
				  $backwards = substr_count($f, '../');

				  if ( $backwards )
				  {

				  }
				  else
				  {

				  } */
			}

			# die();
		}

		return $code;
	}

	/**
	 *
	 * @param string $code
	 * @param string $currentPath
	 * @return string
	 */
	public static function getCssImports ( $code, $currentPath )
	{

		preg_match_all('#(@charset\s*([\'"])([^\2]*)\2\s*(;))#isU', $code, $charsetmatches);
		preg_match_all('#(@import\s*url\(([^\)]*)\)\s*(;))#isU', $code, $matches);

		if ( substr($currentPath, -1) != '/' )
		{
			$currentPath .= '/';
		}

		if ( $charsetmatches[ 0 ] && is_array($charsetmatches[ 3 ]) )
		{
			$code = preg_replace('#(@charset\s*([\'"])([^\2]*)\2\s*(;))#isU', '@@@CHARSET@@', $code);
			// print_r($charsetmatches);exit;
		}

		if ( $matches[ 0 ] && is_array($matches[ 2 ]) )
		{
			$code = preg_replace('#(@import\s*url\(([^\)]*)\)\s*(;))#isU', '@@@IMPORT@@', $code);
		}

		$code = self::fixCssUrlRule($code, $currentPath);

		if ( $matches[ 0 ] && is_array($matches[ 2 ]) )
		{
			$cachePath = PAGE_URL_PATH . '.cache/data/assets/';
			foreach ( $matches[ 2 ] as $f )
			{
				$f = str_replace('"', '', $f);
				$f = str_replace('\'', '', $f);

				if ( file_exists($currentPath . $f) )
				{

					$_code = file_get_contents($currentPath . $f);

					// fix url("../path")
					$_code = self::fixCssUrlRule($_code, $currentPath);
					$code  = preg_replace('#@@@IMPORT@@#', $_code, $code, 1);
				}
				else
				{
					$code = preg_replace('#@@@IMPORT@@#', '/** CSS File "' . $f . '" not exists! **/', $code, 1);
				}
			}
		}


		if ( $charsetmatches[ 0 ] && is_array($charsetmatches[ 3 ]) )
		{
			foreach ( $charsetmatches[ 0 ] as $idx  => $charsetTag )
			{
				$code = preg_replace('#@@@CHARSET@@#', '', $code, 1);
			}
			if ( $charsetTag )
			{
				$code = $charsetTag . $code;
			}
		}


		//
		return $code;
	}

	/**
	 *
	 * @param string $code
	 * @return string
	 */
	public static function compressCssCode ( &$code )
	{

		$orglen = strlen($code);
		$code   = str_replace(array (
		                            "\r\n",
		                            "\r",
		                            "\n"
		                      ), "\n", $code);

		/* remove comments */
		$code = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $code);

		/* remove tabs, spaces, newlines, etc. */
		$code = str_replace(array (
		                          "\r\n",
		                          "\r",
		                          "\n",
		                          "\t",
		                          '  ',
		                          '    ',
		                          '    '
		                    ), ' ', $code);


		$code        = preg_replace('!\s*\}\s*\.(\w)!', '}.$1', $code);
		$code        = preg_replace('!\s*\{\s*!', '{', $code);
		$code        = preg_replace('!;\s*(\w)!', ';$1', $code);
		$code        = preg_replace('!,\s*(\w)!', ',$1', $code);
		$code        = preg_replace('!";\s*!', '";' . "\n", $code);
		$compressLen = strlen($code);

		$code = '/* Original Size: ' . Library::formatSize($orglen) . ', Compression Size: ' . Library::formatSize($compressLen) . ' */' . "\n" . $code;

		return $code;
	}

}

?>