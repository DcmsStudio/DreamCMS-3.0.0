<?php

session_cache_limiter( 'public' );



ob_start();

ini_set('display_startup_errors', '1');


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
 * @package     Library
 * @version     3.0.0 Beta
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Bootstrap.php
 */
/**
 * Check PHP version
 */
if ( version_compare( phpversion(), '5.3.0', '<' ) === true )
{
    echo 'ERROR: Your PHP version is ' . phpversion() . '. DreamCMS requires PHP 5.3.0 or newer.';

    exit;
}


define( 'START', microtime( true ) );

/**
 * Redirect to Setup the CMS
 */
if ( !file_exists( ROOT_PATH . 'System/data/setupdone.dat' ) )
{
    if ( file_exists( ROOT_PATH . 'public/install.php' ) )
    {
        header( 'Location: public/install.php' );

        exit;
    }
    else
    {
        die( 'No Setup file was found in the public directory!!!' );
    }
}





include_once ROOT_PATH . 'System/Library/defines.php';
include_once LIBRARY_PATH . 'functions.php';

$host = !empty( $_SERVER[ 'SERVER_ADDR' ] ) ? $_SERVER[ 'SERVER_ADDR' ] : false;
$host = !empty( $_SERVER[ 'SERVER_NAME' ] ) ? $_SERVER[ 'SERVER_NAME' ] : $host;
$host = !empty( $_SERVER[ 'HTTP_HOST' ] ) ? $_SERVER[ 'HTTP_HOST' ] : $host;





$installID = md5( $host ) . '-' . (defined( 'ADM_SCRIPT' ) && ADM_SCRIPT ? '1' : '0');
// 
define( 'INSTALL_ID', $installID );
define( 'COOKIE_PREFIX', 'dcms_' . $installID );
define('MEMORY_CACHE_KEY',  'dcms_'.INSTALL_ID);



$sessionName = 'DCMSSESSION-'.(defined( 'ADM_SCRIPT' ) && ADM_SCRIPT ? '1' : '0');



/**
 * Try to disable PHPSESSID
 */
ini_set( 'session.use_trans_sid', 0 );
ini_set( 'session.save_handler', 'user' );
ini_set( 'session.name', $sessionName);
$old = session_name( $sessionName );
ini_set( 'session.use_cookies', 1 );
ini_set( 'session.use_only_cookies', 0 );



ini_set( 'zlib.output_compression', 'Off' );
$mem = ini_get('memory_limit');
if ($mem) {
    $mem = (int)str_replace(array('M', 'GB'), '', $mem);
    if ($mem < 64) {
        @ini_set( 'memory_limit', '64M' );
    }
}


if (get_magic_quotes_gpc())
{
    @ini_set( 'magic_quotes_gpc', 'Off' );
}



ini_set( 'zend.ze1_compatibility_mode', 0 );
ini_set( 'register_long_arrays', 'On' );
ini_set( 'suhosin.simulation', 'Off' );
ini_set( 'suhosin.log.phpscript.is_safe', 'On' );
ini_set( 'allow_call_time_pass_reference', 'On' );
ini_set( 'log_errors', 1 );
ini_set( 'error_log', DATA_PATH . 'logs/php-errors.txt' );
ini_set( 'display_errors', 'On' );

// needed for case insensitive search to work, due to broken UTF-8 support in PHP
ini_set( 'mbstring.internal_encoding', 'UTF-8' );
ini_set( 'mbstring.func_overload', 2 );

date_default_timezone_set( 'Europe/Berlin' );







$time = null;

$GLOBALS[ 'COMPILER_TEMPLATE' ] = null;
/*
  if (!function_exists('trans'))
  {

  function trans($string)
  {
  return $string;
  }

  }
 */

include_once LIBRARY_PATH . 'config.inc.php';

if ( !function_exists( 'bindtextdomain' ) )
{
    include_once VENDOR_PATH . 'php-gettext/combined.php';
}
else
{

    /**
     * Returns whether we are using our emulated gettext API or PHP built-in one.
     */
    function locale_emulation()
    {
        return 0;
    }

    function T_setlocale( $category, $locale )
    {
        return setlocale( $category, $locale );
    }
}

define( 'DEBUG', DEBUGGING );



/*
 * Is Ajax Request from a Flash application?
 */
if ( !empty( $_POST[ 'is_flash' ] ) || !empty( $_GET[ 'is_flash' ] ) )
{
    define( 'IS_FLASH', true );
}
else
{
    define( 'IS_FLASH', false );
}


if ( (isset( $_GET[ 'swfupload_sid' ] ) && !empty( $_GET[ 'swfupload_sid' ] )) ||
        (isset( $_POST[ 'swfupload_sid' ] ) && !empty( $_POST[ 'swfupload_sid' ] )) )
{
    if ( !defined( 'IS_AJAX' ) )
    {
        define( 'IS_AJAX', true );
    }

    define( 'IS_SWFUPLOAD', true );
    define( 'SWFUPLOAD_SID', (!empty( $_GET[ 'swfupload_sid' ] ) ? $_GET[ 'swfupload_sid' ] : (!empty( $_POST[ 'swfupload_sid' ] ) ? $_POST[ 'swfupload_sid' ] : null) ) );
}
else
{
    define( 'IS_SWFUPLOAD', false );
}


/*
 * Is Ajax Request?
 */
if ( !defined( 'IS_AJAX' ) )
{
    if ( (isset( $_GET[ 'ajaxsubmit' ] ) && !empty( $_GET[ 'ajaxsubmit' ] )) ||
            (isset( $_POST[ 'ajaxsubmit' ] ) && !empty( $_POST[ 'ajaxsubmit' ] )) ||
            (isset( $_GET[ 'ajax' ] ) && !empty( $_GET[ 'ajax' ] )) ||
            (isset( $_POST[ 'ajax' ] ) && !empty( $_POST[ 'ajax' ] ) ||
            ( isset( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) && $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] == 'XMLHttpRequest') )
    )
    {
        define( 'IS_AJAX', true );
    }
    else
    {
        define( 'IS_AJAX', false );
    }
}


// Include core classes

include_once FRAMEWORK_PATH .'Database/Adapter/Abstract.php';
include_once FRAMEWORK_PATH .'Database/Pdo.php';
include_once FRAMEWORK_PATH .'Database/PdoRecordSet.php';
include_once FRAMEWORK_PATH .'Database.php';

include_once FRAMEWORK_PATH .'Loader.php';
include_once FRAMEWORK_PATH .'Cache/Abstract.php';
include_once FRAMEWORK_PATH .'Cache.php';
include_once FRAMEWORK_PATH .'Input.php';
include_once FRAMEWORK_PATH .'HTTP.php';
include_once FRAMEWORK_PATH .'Env.php';

include_once FRAMEWORK_PATH .'Firewall/Abstract.php';
include_once FRAMEWORK_PATH .'Firewall.php';

if (!defined('ADM_SCRIPT') || ADM_SCRIPT === false) {
    include_once FRAMEWORK_PATH .'Router/Abstract.php';
    include_once FRAMEWORK_PATH .'Router/Regex.php';
    include_once FRAMEWORK_PATH .'Router/Static.php';
    include_once FRAMEWORK_PATH .'Router.php';
    include_once FRAMEWORK_PATH .'Breadcrumb.php';
}

include_once FRAMEWORK_PATH .'Template/Abstract.php';
include_once FRAMEWORK_PATH .'Template.php';



//
include_once LIBRARY_PATH . 'Library.php';
include_once FRAMEWORK_PATH . 'Debug.php';
include_once FRAMEWORK_PATH . 'Exception.php';

include_once LIBRARY_PATH . 'permission_defines.php';





// Include SimplePie
// Located in the parent directory
#include_once VENDOR_PATH . 'simplepie/SimplePieAutoloader.php';
#include_once VENDOR_PATH . 'simplepie/idn/idna_convert.class.php';

include_once FRAMEWORK_PATH . 'Autoloader.php';
#include_once VENDOR_PATH . 'simple_html_dom/loader.php';





$autoloader = new Autoloader( '_', FRAMEWORK_PATH );
if ( defined( 'DEBUG' ) && DEBUG )
{
    $autoloader->enableFileCheck()->enableAutoloadDebug();
}
$autoloader->addLibrary( 'Library', LIBRARY_PATH );
$autoloader->addLibrary( 'Field', LIBRARY_PATH . 'Field/' );
$autoloader->addLibrary( 'Addon', PACKAGES_PATH . 'plugins/' );
$autoloader->addLibrary( 'Widget', PACKAGES_PATH . 'widgets/' );
$autoloader->addLibrary( 'CoreTag', CORETAGS_PATH );

$autoloader->register();


Registry::setObject( 'Autoloader', $autoloader );

if ( defined( 'DEBUG' ) && DEBUG )
{

    Debug::initDebugger();
}


Cache::setCachePath( DATA_PATH );

if ( !defined( 'IMAGE_LIBRARY' ) )
{
    define( 'IMAGE_LIBRARY', (class_exists( 'Imagick', false ) ? 'imagick' : 'gd' ) );
}


Registry::set('disableAjaxDebug', array(
                                       array('controller' => 'messenger', 'action' => 'getnew'),
                                       array('controller' => 'logs', 'action' => 'getpanellogs'),
                                       array('controller' => 'fileman', 'action' => 'open')

                                  ));

// Check if it supports gzip
$zlibOn = ini_get( 'zlib.output_compression' ) || ( ini_set( 'zlib.output_compression', 0 ) === false );
if ( $zlibOn )
{
    ini_set( 'zlib.output_compression', 0 );
}