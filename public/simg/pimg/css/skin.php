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
 * @package     Importer
 * @version     3.0.0 Beta
 * @category    Config
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Base.php
 */
define( 'CACHE_EXPIRES_TIME', 60 * 60 * 24 * 7 ); // Browser Cache Life Tim
define( "_EXP", (time() + CACHE_EXPIRES_TIME ) );
error_reporting( E_ALL ^ E_NOTICE );


ob_start();
ob_implicit_flush( 0 );

$files = array(
        'reset.css',
        
        '../../../html/css/jquery-ui-1.8.6.custom.css',
        '../../../html/css/subcols.css',
        
        
        'bootstrap.css',
        'bootstrap-theme.css',
        
        '../../../html/css/contentgrid.css',
        '../../../html/css/dcmscore.css',        
        'fancybox/style.css',
        'font-awesome.min.css',
        'tinymce-content.css',
        'wrapper.css',
        'layout.css',
        'grid.css',
        'typo.css',
        'form-ui.css',
        'bubble.css',
        'boxes.css',
        'comments.css',
        'bbcode.css',
        'navi.css',

        'memberslist.css',
        'teas.css',
        'dokumentation.css',

      //  'seemode.css',
       'style-base.css',
        'dcms.page.css',
        'user.css',
        'frontpage.css',

    //    'mobile.css',
        
        
        );


$etag = 0;
foreach ( $files as $file )
{
    if ( is_file( $file ) )
    {
        $etag += filemtime( $file );
    }
}
$etag = md5( $etag . implode( '', $files ) );

if ( (isset( $_SERVER[ 'HTTP_IF_NONE_MATCH' ] ) && $_SERVER[ 'HTTP_IF_NONE_MATCH' ] === $etag ) )
{
    // Datei/ETag im Browser Cache vorhanden?
    header( 'Content-Type: text/css' );
    header( 'HTTP/1.0 304 Not Modified' ); // entsprechenden Header senden => Datei wird nicht geladen

    exit();
}


$code = '';
foreach ( $files as $file )
{
    $code .= file_get_contents( $file );
}

/** This Functions Cleans Standard Block Comments( /* )
 * @author M. Schwarz
 * @param String $code
 * @return String 
 */
function clean_comments( $code )
{
    do
    {
        $start = strpos( $code, '/*' );
        $ende  = strpos( $code, '*/', $start );
        $comm  = substr( $code, $start + 2, $ende - $start - 2 );
        $code  = str_replace( '/*' . $comm . '*/', '', $code );
    }
    while ( $ende );

    return $code;
}

/** This Functions Cleans CSS Stylesheet Code
 * @author M. Schwarz
 * @param String $jscode
 * @return String 
 */
function clean_css_code( $code )
{
    $code = clean_comments( $code );
    $code = preg_replace( '/\s\s+/', ' ', $code );
    $code = preg_replace( '/\t*\s*\{\t*\s*/', "{", $code );
    $code = preg_replace( '/\t*\s*\;([\s]?)/', ";", $code );
    $code = preg_replace( "/\t*\s*\:\t*\s*/", ":", $code );
    $code = preg_replace( "/\t*\s*\}\t*\s*/", "}\r\n", $code );
    return $code;
}
header( "Content-type: text/css; charset: UTF-8" );
header( "Cache-Control: public" );
header( 'Vary: Accept-Encoding' );
header( 'Pragma: public' );
header( 'Cache-Control: maxage=' . (60 * 60 * 24 * 14) );
header( 'Expires: ' . gmdate( "D, d M Y H:i:s", time() + (60 * 60 * 24 * 14) ) . " GMT" );
header( 'Last-Modified: ' . gmdate( "D, d M Y H:i:s", time() ) . " GMT" );
 header( 'Etag: ' . $etag );

echo clean_css_code( $code );

/* ---------------------------------------------------------------------------- */
if ( isset( $_SERVER[ 'HTTP_ACCEPT_ENCODING' ] ) )
    $accept_enc = $_SERVER[ 'HTTP_ACCEPT_ENCODING' ];
else if ( isset( $HTTP_SERVER_VARS[ 'HTTP_ACCEPT_ENCODING' ] ) )
    $accept_enc = $HTTP_SERVER_VARS[ 'HTTP_ACCEPT_ENCODING' ];
else if ( isset( $HTTP_ACCEPT_ENCODING ) )
    $accept_enc = $HTTP_ACCEPT_ENCODING;
else
    $accept_enc = '';

/* ---------------------------------------------------------------------------- */

if ( extension_loaded( 'zlib' ) )
{
    @ini_set( 'zlib.output_compression', 'Off' );
    if ( strpos( $accept_enc, 'gzip' ) !== false )
    {
        header( 'Content-Encoding: gzip' );
        $gzip_size     = ob_get_length();
        $gzip_contents = ob_get_clean();
        echo "\x1f\x8b\x08\x00\x00\x00\x00\x00",
        substr( gzcompress( $gzip_contents, 9 ), 0, - 4 ),
        pack( 'V', crc32( $gzip_contents ) ),
        pack( 'V', $gzip_size );
    }
}

exit();

?>