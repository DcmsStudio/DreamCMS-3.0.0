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
 * @package
 * @version      3.0.0 Beta
 * @category
 * @copyright    2008-2014 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         scripts.php
 */

ob_start();
error_reporting( 0 );




if (isset($_GET['useSeemode'])) {
    $scripts = array(

        'public/html/js/backend/modernizr.js',
        'public/html/style/bootstrap/js/bootstrap.js',
        'public/html/js/TweenMax.js',
        'public/html/js/jquery/jquery.gsap.min.js',
        'public/html/js/jquery/jquery.scrollTo.js',
        'public/html/js/jquery/jquery.tools.min.js',
        'public/html/js/jquery/jquery-css-transform.js',
        'public/html/js/jquery/jquery.quicksand.js',
        'public/html/js/jquery/jquery.colorpicker.js',
        'public/html/js/jquery/jquery.ui.widget.js',
        'public/html/js/jquery/jquery.fileupload.js',
        'public/html/js/jquery/fancybox/jquery.fancybox-1.3.1.js',

        'Vendor/tinymce4/plugins/atd/atd.core.js',

        'Vendor/tinymce4/plugins/atd/jquery.atd.js',
        'Vendor/tinymce4/plugins/atd/atd-autoproofread.js',
        'Vendor/tinymce4/tinymce.jquery.js',
        'public/html/js/jquery/jquery.contextMenu.js',
        // get backend scripts
        'public/html/js/backend/phpjs.js',

        'public/html/js/backend/dcms.serialize.js',
        'public/html/js/backend/jquery.alert.js',
        'public/html/js/backend/dcms.namespace.js',
        'public/html/js/backend/dcms.functions.js',


        'public/html/js/backend/dcms.autocomplete.js',
        'public/html/js/backend/dcms.scrollbar.js',
        'public/html/js/backend/dcms.utf8.js',
        'public/html/js/backend/dcms.config.js',
        'public/html/js/backend/dcms.debug.js',
        'public/html/js/backend/dcms.string.js',
        'public/html/js/backend/dcms.hashgen.js',
        'public/html/js/backend/dcms.template.js',
        'public/html/js/backend/dcms.notifier.js',
        'public/html/js/backend/dcms.selectbox.js',
    //    'public/html/js/backend/dcms.checkbox.js',
        'public/html/js/backend/dcms.form-new.js',

        'public/html/js/backend/dcms.console.js',

        'public/html/js/backend/dcms.grid-new.js',
        'public/html/js/backend/dcms.content.js',
        'public/html/js/backend/dcms.sourceeditor.js', // the new Source Code Editor use ACE
        'public/html/js/backend/fileman/dcms.fileman.js',
        'public/html/js/backend/fileman/dcms.fileman.layout.js',
        'public/html/js/backend/fileman/dcms.fileman.commands.js',
        'public/html/js/backend/fileman/dcms.fileman.quickview.js',

        'public/html/js/backend/tpleditor/placeholder.js',
        'public/html/js/backend/tpleditor/html5.js',

        'public/html/js/xregexp.js',
        'public/html/js/dcms.googlemap.js',
        'public/html/js/backend/dcms.googlemap.js',
        // skin js files
        'public/html/style/bootstrap/js/dcms.bootstrap-widget.js',
        'public/html/style/bootstrap/js/dcms.bootstrap-fileinput.js',
        'public/html/style/bootstrap/js/dcms.bootstrap-input-tooltip.js',
        'public/html/style/bootstrap/js/dcms.bootstrap-input-spin.js',
        'public/html/style/bootstrap/js/dcms.bootstrap-inputtrigger.js',
        'public/html/style/bootstrap/js/dcms.bootstrap-init.js',
        'public/html/style/bootstrap/js/dcms.tools.js',
        'public/html/style/bootstrap/js/dcms.document.js',
        'public/html/style/bootstrap/js/dcms.window.js',
        'public/html/style/bootstrap/js/dcms.desktop.js',
        'public/html/style/bootstrap/js/dcms.auth.js',
        'public/html/style/bootstrap/js/dcms.scrollbar.js',

     //   'public/html/js/dcms.seemode.js',
     //   'public/html/js/seemode.analyseclicks.js',

        'public/html/js/backend/templates/dcms.form.notification.js',
        'public/html/js/backend/templates/dcms.user.menu.js',
    );

}
else {
    $scripts = array(

        'public/html/js/backend/modernizr.js',
        'public/html/style/bootstrap/js/bootstrap.js',
        //     'public/html/js/jquery/jquery.animate-enhanced.js',

        // better animation as jquery ^^, jquery is very very slow
        'public/html/js/TweenMax.js',
        'public/html/js/jquery/jquery.gsap.min.js',
        //
        // for tinyMCE 3         'Vendor/tinymce/plugins/AtD/atd.core.js',
        'public/html/js/jquery/jquery.scrollTo.js',
        'public/html/js/jquery/jquery.tools.min.js',
        'public/html/js/jquery/jquery-css-transform.js',
        'public/html/js/jquery/jquery.quicksand.js',
        'public/html/js/jquery/jquery.colorpicker.js',
        'public/html/js/jquery/jquery.ui.widget.js',
        'public/html/js/jquery/jquery.fileupload.js',
        'public/html/js/jquery/fancybox/jquery.fancybox-1.3.1.js',


        //         'Vendor/tinymce/tiny_mce_src.js',
        //         'Vendor/tinymce/jquery.tinymce.js',

        'Vendor/tinymce4/plugins/atd/atd.core.js',
        //          'Vendor/tinymce4/plugins/atd/atd-nonvis-editor-plugin.js',
        'Vendor/tinymce4/plugins/atd/jquery.atd.js',
        'Vendor/tinymce4/plugins/atd/atd-autoproofread.js',
        'Vendor/tinymce4/tinymce.js',
        //'Vendor/tinymce4/tinymce.jquery.js',


        'public/html/js/jquery/jquery.contextMenu.js',
        // get backend scripts
        'public/html/js/backend/phpjs.js',

        // 'public/html/js/backend/dcms.websocket.js',
        'public/html/js/backend/dcms.serialize.js',
        'public/html/js/backend/jquery.mjs.nestedSortable.js',
        'public/html/js/backend/jquery.alert.js',
        'public/html/js/backend/dcms.namespace.js',
        'public/html/js/backend/dcms.functions.js',
        'public/html/js/backend/dcms.indexer.js',
        'public/html/js/backend/dcms.autosave.js',
        'public/html/js/backend/dcms.autocomplete.js',
        'public/html/js/backend/dcms.scrollbar.js',
        'public/html/js/backend/dcms.utf8.js',
        'public/html/js/backend/dcms.config.js',
        'public/html/js/backend/dcms.debug.js',
        'public/html/js/backend/dcms.string.js',
        'public/html/js/backend/dcms.hashgen.js',
        'public/html/js/backend/dcms.template.js',
        'public/html/js/backend/dcms.notifier.js',
        'public/html/js/backend/dcms.selectbox.js',
        'public/html/js/backend/dcms.checkbox.js',
        'public/html/js/backend/dcms.layouter.js',
        // 'public/html/js/backend/dcms.form.js',
        'public/html/js/backend/dcms.form-new.js',

        'public/html/js/backend/dcms.console.js',
        //    'public/html/js/backend/dcms.application.js',
        'public/html/js/backend/dcms.grid-new.js',
        'public/html/js/backend/dcms.content.js',
        'public/html/js/backend/dcms.sourceeditor.js', // the new Source Code Editor use ACE
        'public/html/js/backend/fileman/dcms.fileman.js',
        'public/html/js/backend/fileman/dcms.fileman.layout.js',
        'public/html/js/backend/fileman/dcms.fileman.commands.js',
        'public/html/js/backend/fileman/dcms.fileman.quickview.js',
        // the old Source Code Editor !!!
        // @deprecated


        'public/html/js/backend/tpleditor/placeholder.js',
        'public/html/js/backend/tpleditor/html5.js',
        //   'public/html/js/backend/tpleditor/dcms.editor.js', // use codemirror (Old diff mode only)


        'public/html/js/xregexp.js',
        'public/html/js/dcms.googlemap.js',
        'public/html/js/backend/dcms.googlemap.js',

        // skin js files
        'public/html/style/bootstrap/js/dcms.bootstrap-widget.js',
        'public/html/style/bootstrap/js/dcms.bootstrap-fileinput.js',
        'public/html/style/bootstrap/js/dcms.bootstrap-input-tooltip.js',
        'public/html/style/bootstrap/js/dcms.bootstrap-input-spin.js',
        'public/html/style/bootstrap/js/dcms.bootstrap-inputtrigger.js',
        'public/html/style/bootstrap/js/dcms.bootstrap-init.js',


        'public/html/style/bootstrap/js/dcms.tools.js',
        'public/html/style/bootstrap/js/dcms.document.js',
        'public/html/style/bootstrap/js/dcms.window.js',
        'public/html/style/bootstrap/js/dcms.desktop.js',
        'public/html/style/bootstrap/js/dcms.core.js',
        'public/html/style/bootstrap/js/dcms.core.tabs.js',
        'public/html/style/bootstrap/js/dcms.tooltip.js',
        'public/html/style/bootstrap/js/dcms.menu.js',
        'public/html/style/bootstrap/js/dcms.auth.js',
        'public/html/style/bootstrap/js/dcms.contenttree.js',
        'public/html/style/bootstrap/js/dcms.scrollbar.js',
        'public/html/style/bootstrap/js/dcms.advanced-menus.js',
        'public/html/style/bootstrap/js/dcms.panel.js',
        'public/html/style/bootstrap/js/dcms.dashboard.js',


        //
        //'public/html/js/backend/templates/dcms.desktop.contextmenu.js',
        'public/html/js/backend/templates/dcms.form.notification.js',
        'public/html/js/backend/templates/dcms.user.menu.js',

        //'public/html/js/backend/templates/dcms.desktop.mask.js',
        //'public/html/js/backend/templates/dcms.window.mask.js'
    );
}

$root = '../../../../../';

define( 'ROOT_PATH', realpath( $root ) . '/' );
define( 'PUBLIC_PATH', ROOT_PATH . '/public/' );
define( 'IN', true );
$expires_offset = 96000;

include ROOT_PATH . 'System/Library/Bootstrap.php';

$application = new Application( 'DEVELOPMENT' );
$application->setupWebsiteDomain( true );


$config = $application->getSystemConfig();

$config->setWriteable();
$config->__set( 'gziplevel', 9 );
$config->__set( 'gzip', true );
ini_set( 'zlib.output_compression', '0' );
ini_set( 'output_handler', '' );


$etag = md5( serialize( $scripts ) );


$cachePath = PAGE_PATH . '.cache/data/assets/';

Library::makeDirectory( $cachePath );


$path = ROOT_PATH . 'public/html/js/';

$assetcache = '';
$gmt_mtime  = gmdate( 'D, d M Y H:i:s', TIMESTAMP ) . ' GMT';

// Check if it supports gzip
$zlibOn = ini_get( 'zlib.output_compression' ) || ( ini_set( 'zlib.output_compression', 0 ) === false );

if ( $zlibOn )
{
    ini_set( 'zlib.output_compression', 0 );

    $zlibOn = ini_get( 'zlib.output_compression' ) || ( ini_set( 'zlib.output_compression', 0 ) === false );
}


$encodings = ( isset( $_SERVER[ 'HTTP_ACCEPT_ENCODING' ] ) ) ? strtolower( $_SERVER[ 'HTTP_ACCEPT_ENCODING' ] ) : "";
$encoding  = preg_match( '/\b(x-gzip|gzip)\b/', $encodings, $match ) ? $match[ 1 ] : "";

// Is northon antivirus header
if ( isset( $_SERVER[ '---------------' ] ) )
{
    $encoding = "x-gzip";
}

$supportsGzip = !empty( $encoding ) && !$zlibOn && function_exists( 'gzencode' );


// Set cache file name
$cacheFile = $cachePath . $etag . ( $supportsGzip ? ".gz" : ".js" );


if ( file_exists( $cacheFile ) )
{
    if ( isset( $_SERVER[ 'HTTP_IF_NONE_MATCH' ] ) )
    {
        if ( str_replace( '"', '', stripslashes( $_SERVER[ 'HTTP_IF_NONE_MATCH' ] ) ) == $etag )
        {

            # header('Content-Type', 'text/javascript');
            # header( 'ETag:"' . $etag . '"', true );
            # header( "Last-Modified: $gmt_mtime", true );
            header('HTTP/1.0 304 Not Modified'); // entsprechenden Header senden => Datei wird nicht geladen
            exit();
        }
    }

    $gmt_mtime = gmdate( 'D, d M Y H:i:s', filemtime( $cacheFile ) ) . ' GMT';
    // $assetcache = file_get_contents( $cacheFile);

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
    $output->addHeader( 'Content-Type', 'text/javascript; charset=UTF-8' );

    if ( $assetcache === null )
    {
        $assetcache = '/* Javascript File not found! */';
    }
    else
    {
        $output->addHeader( 'Cache-Control', "max-age=" . ( 96000 ) );
        $output->addHeader( "Vary", "Accept-Encoding" ); // Handle proxies
        $output->addHeader( 'Pragma', 'public' );
        $output->addHeader( 'ETag', '"' . $etag . '"' );
        $output->addHeader( 'Last-Modified', $gmt_mtime );
        $output->addHeader( 'Expires', gmdate( "D, d M Y H:i:s", ( $cacheStamp + 96000 ) ) . " GMT" );

        if ( $supportsGzip )
        {
            $output->addHeader( "Content-Encoding", $encoding );
        }
    }

    $output->sendHeaders();

    readfile( $cacheFile );
    exit;
}
else
{
    $tmp = array();
    foreach ( $scripts as $file )
    {
        if ( trim( $file ) == '' )
        {
            continue;
        }

        if ( substr( $file, -3 ) !== '.js' )
        {
            $file .= '.js';
        }

        if ( substr( $file, 0, 8 ) === 'Modules/' )
        {
            $file = MODULES_PATH . substr( $file, 8 );
        }
        elseif ( substr( $file, 0, 9 ) === 'Packages/' )
        {
            $file = PACKAGES_PATH . substr( $file, 9 );
        }
        elseif ( substr( $file, 0, 7 ) === 'Vendor/' || substr( $file, 0, strlen( 'public/html/js/' ) ) === 'public/html/js/' || substr( $file, 0, strlen( 'public/html/style/' ) ) === 'public/html/style/' )
        {
            $file = ROOT_PATH . $file;
        }
        else
        {
            $file = $path . $file;
        }


        if ( file_exists( $file ) )
        {
            $tmp[ ] = trim( file_get_contents( $file ) ) . ';';
        }
        else
        {
            $tmp[ ] = '/* FILE: ' . $file . ' not exists! */';
        }
    }

    if ( count( $tmp ) )
    {
        $assetcache = implode( "\n", $tmp );
       // $assetcache = Minifier::minifyJs( $assetcache );

        // Compress data
        if ( $supportsGzip )
        {
            $assetcache = gzencode( $assetcache, 9, FORCE_GZIP );
        }

        file_put_contents( $cacheFile, $assetcache );
        $gmt_mtime = gmdate( 'D, d M Y H:i:s', filemtime( $cacheFile ) ) . ' GMT';
        unset( $tmp );
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
$output->addHeader( 'Content-Type', 'text/javascript; charset=UTF-8' );

if ( $assetcache === null )
{
    $assetcache = '/* Javascript File not found! */';
}
else
{
    $output->addHeader( 'Cache-Control', "max-age=" . ( 96000 ) );
    $output->addHeader( "Vary", "Accept-Encoding" ); // Handle proxies
    $output->addHeader( 'Pragma', 'public' );
    $output->addHeader( 'ETag', '"' . $etag . '"' );
    $output->addHeader( 'Last-Modified', $gmt_mtime );
    $output->addHeader( 'Expires', gmdate( "D, d M Y H:i:s", ( $cacheStamp + 96000 ) ) . " GMT" );

    if ( $supportsGzip )
    {
        $output->addHeader( "Content-Encoding", $encoding );
    }

}
$output->sendHeaders();


echo( !$supportsGzip ? Strings::fixUtf8( $assetcache ) : $assetcache );

exit;