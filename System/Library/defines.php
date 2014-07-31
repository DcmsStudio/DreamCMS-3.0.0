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
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         defines.php
 */

// set the default backend skin
/*
  $skin = '';
  if ( is_dir( PUBLIC_PATH . 'html/style/c9' ) )
  {
  $skin = 'c9';
  } */
$skin = 'window';
$skin = 'bootstrap';
define('BACKEND_SKIN', $skin );
define( 'BACKEND_SKIN_ISWINDOWED', true );

if (!defined('E_DEPRECATED')) {
    define('E_DEPRECATED', 8192);
}

if (!defined('E_USER_DEPRECATED')) {
    define('E_USER_DEPRECATED', E_USER_NOTICE);
}

if ( !defined('BACKEND_TPL_PATH') )
{
	define( 'BACKEND_TPL_PATH', PUBLIC_PATH . 'html/style/' . ( !empty( $skin ) ? $skin : '' ) . '/' );
	define( 'BACKEND_IMAGE_PATH', 'html/style/' . ( !empty( $skin ) ? $skin : '' ) . '/img/' );
	define( 'BACKEND_CSS_PATH', 'html/style/' . ( !empty( $skin ) ? $skin : '' ) . '/css/' );
}


define( 'DEMO_USERID', 0 ); // for CMS DEMO !!! it will stop bad actions


define ('DUMMY_IMAGE', 'public/html/img/placeholder.png');







// ------------ END YOUR SETTINGS -----------------


/**
 * -------  PRIVATE SETTINGS - DO NOT EDIT  -------
 */

define( 'DEV_MODE', 0 );

define( 'USE_ERROR_HIGHLIGHT', true );
define( 'MEM_DUMP', false ); // Memory debugging
define( 'TIMESTAMP', time() );


define ( '_OS', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'w' : '');


define( 'SYSTEM_PATH', ROOT_PATH . 'System/' );


/**
 * Data
 */
define( 'DATA_PATH', SYSTEM_PATH . 'data/' );
define( 'CACHE_PATH', DATA_PATH . 'cache/' );
define( 'XMLDATA_PATH', DATA_PATH . 'xml/' );

/**
 * Basic Paths
 */
define( 'FRAMEWORK_PATH', SYSTEM_PATH . 'Framework/' );
define( 'LIBRARY_PATH', SYSTEM_PATH . 'Library/' );
define( 'PACKAGES_PATH', SYSTEM_PATH . 'Packages/' );

define( 'PLUGIN_PATH', PACKAGES_PATH . 'plugins/' );
define( 'WIDGET_PATH', PACKAGES_PATH . 'widgets/' );
define( 'PLUGIN_URL_PATH', 'Packages/plugins/' );
define( 'WIDGET_URL_PATH', 'Packages/widgets/' );

define( 'CRONJOBS_PATH', SYSTEM_PATH . 'Cronjobs/' );
define( 'MODULES_PATH', SYSTEM_PATH . 'Modules/' );
define( 'VENDOR_PATH', ROOT_PATH . 'Vendor/' );
define( 'VENDOR_URL_PATH', 'Vendor/' );
define( 'HELPER_PATH', LIBRARY_PATH . 'Helpers/' );
define( 'I18N_PATH', DATA_PATH . 'i18n/' );

define( 'PROVIDER_PATH', SYSTEM_PATH . 'Provider/' );
define( 'CORETAGS_PATH', SYSTEM_PATH . 'CoreTags/' );
define( 'TEMPLATES_PATH', ROOT_PATH . 'Templates/' );


if ( isset( $_SERVER[ 'HTTPS' ] ) )
{
	define( 'SSL_MODE', true );
}
else
{
	define( 'SSL_MODE', false );
}

/**
 * Is a windows Server?
 */
$isWindows = false;
if ( '\\' === PATH_SEPARATOR )
{
	$isWindows = true;
}
define( 'IS_WIN', $isWindows );


if ( php_sapi_name() != 'cli' )
{
	define( 'IS_CLI', false );
}
else
{
	define( 'IS_CLI', true );
}

/**
 * Cache setup
 */
define( 'USE_APC', function_exists('apc_store') ? true : false ); // not tested!
define( 'USE_XCACHE', function_exists('xcache_set') && USE_APC === false ? true : false ); // not tested!
define( 'USE_DBCACHE', false ); // not implemented!
define( 'USE_SQLITECACHE', false /*( (class_exists('SQLite3', false) && !USE_XCACHE && !USE_APC ) ? true : false) */ ); // filecache is faster :(
define( 'USE_EACCELERATOR', function_exists('eaccelerator_set') ? true : false );


// load version defines
include_once LIBRARY_PATH . 'Version.php';

/**
 * Public resource url paths
 */
define( 'HTML_URL', 'public/html/' );
define( 'JS_URL', 'html/js/' );
define( 'CSS_URL', 'html/css/' );
define( 'IMG_URL', 'html/img/' );
define( 'BACKEND_JS_URL', JS_URL . 'backend/' );


/**
 * Frontend Skin Images
 */
define( 'SKIN_PATH', ROOT_PATH . 'skins/' );
//define('SKIN_IMG_PATH', PUBLIC_PATH . 'simg/');
define( 'SKIN_IMG_URL_PATH', 'public/simg/' );


/**
 * Dokument modes
 */
define( 'TIME_MODE', 2 );
define( 'PUBLISH_MODE', 1 ); // Dokument is Active
define( 'UNPUBLISH_MODE', 0 ); // Dokument is not Active
define( 'TRASH_MODE', 9 ); // Dokument is in Trash
define( 'ARCHIV_MODE', 10 ); //
define( 'DRAFT_MODE', 20 );
define( 'LOCK_MODE', 30 );

define( 'MODERATE_MODE', 90 ); // is Spam
define( 'SPAM_MODE', 99 ); // is Spam

/**
 * Crypt Modes
 */
if ( !defined('CRYPT_BLOWFISH') )
{
	define( 'CRYPT_BLOWFISH', false );
}

if ( !defined('CRYPT_EXT_DES') )
{
	define( 'CRYPT_EXT_DES', false );
}


/**
 * Content Versioning
 */
$GLOBALS[ 'versioning' ] = array (
	'period'          => 7776000, // (global) all versions that are older than 3 months automatically delete
	'enabled'         => true, // enable global versioning
	// tables
	'menu'            => array ( 'enabled' => false ), // not implemented
	'news'            => array ( 'enabled' => true, 'period' => 7776000 ), // all versions that are older than 3 months automatically delete
	'news_categories' => array ( 'enabled' => true, 'period' => 7776000 ),
	'profile_fields'  => array ( 'enabled' => true ), // not implemented
	//    'skins_templates' => array('enabled' => true, 'period' => 7776000), // not implemented
);

define('E_FATAL', E_ERROR | E_USER_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR);
define( 'CHUNK_SIZE', 5120 ); // Size (in bytes) of tiles chunk used in function readfile_chunked

/**
 * We change complete to the TinyMCE 4
 * @deprecated
 */
define( 'TINYMCE_VERSION', 4 );

if (!defined('APP_PATH')) {
    define('APP_PATH', '');
}