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
 * @package     Helpers
 * @version     3.0.0 Beta
 * @category    Helper Tools
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        file_mappings.php
 */
// Type mappings
// this file contains type mappings for the file manager
// mediatype is used when adding to media library - items without mediatype may not be added

$file_types = array( 'folders' => array(), 'files' => array() );

// folder types
$file_types[ 'folders' ][ '__default' ]   = array( 'icon' => 'folder', 'view' => false, 'edit' => false, 'mediatype' => false );
$file_types[ 'folders' ][ 'css' ]         = array( 'icon' => 'folder_css', 'view' => false, 'edit' => false, 'mediatype' => false );
$file_types[ 'folders' ][ 'style' ]       = array( 'icon' => 'folder_css', 'view' => false, 'edit' => false, 'mediatype' => false );
$file_types[ 'folders' ][ 'styles' ]      = array( 'icon' => 'folder_css', 'view' => false, 'edit' => false, 'mediatype' => false );
$file_types[ 'folders' ][ 'stylesheets' ] = array( 'icon' => 'folder_css', 'view' => false, 'edit' => false, 'mediatype' => false );
$file_types[ 'folders' ][ 'js' ]          = array( 'icon' => 'folder_js', 'view' => false, 'edit' => false, 'mediatype' => false );
$file_types[ 'folders' ][ 'script' ]      = array( 'icon' => 'folder_js', 'view' => false, 'edit' => false, 'mediatype' => false );
$file_types[ 'folders' ][ 'scripts' ]     = array( 'icon' => 'folder_js', 'view' => false, 'edit' => false, 'mediatype' => false );
$file_types[ 'folders' ][ 'javascript' ]  = array( 'icon' => 'folder_js', 'view' => false, 'edit' => false, 'mediatype' => false );
$file_types[ 'folders' ][ 'images' ]      = array( 'icon' => 'folder_img', 'view' => false, 'edit' => false, 'mediatype' => false );
$file_types[ 'folders' ][ 'img' ]         = array( 'icon' => 'folder_img', 'view' => false, 'edit' => false, 'mediatype' => false );
$file_types[ 'folders' ][ 'gfx' ]         = array( 'icon' => 'folder_img', 'view' => false, 'edit' => false, 'mediatype' => false );
$file_types[ 'folders' ][ 'pictures' ]    = array( 'icon' => 'folder_img', 'view' => false, 'edit' => false, 'mediatype' => false );
$file_types[ 'folders' ][ 'photos' ]      = array( 'icon' => 'folder_img', 'view' => false, 'edit' => false, 'mediatype' => false );
$file_types[ 'folders' ][ 'media' ]       = array( 'icon' => 'folder_media', 'view' => false, 'edit' => false, 'mediatype' => false );
$file_types[ 'folders' ][ 'assets' ]      = array( 'icon' => 'folder_media', 'view' => false, 'edit' => false, 'mediatype' => false );

// file types (based on extension)
$file_types[ 'files' ][ '__default' ] = array( 'icon' => 'file', 'view' => false, 'edit' => false, 'mediatype' => false );

// image files
$file_types[ 'files' ][ 'png' ]  = array( 'icon' => 'file_img', 'view' => 'image', 'edit' => false, 'mediatype' => 'image', 'mimetype' => 'image/png' );
$file_types[ 'files' ][ 'gif' ]  = array( 'icon' => 'file_img', 'view' => 'image', 'edit' => false, 'mediatype' => 'image', 'mimetype' => 'image/gif' );
$file_types[ 'files' ][ 'jpg' ]  = array( 'icon' => 'file_img', 'view' => 'image', 'edit' => false, 'mediatype' => 'image', 'mimetype' => 'image/jpeg' );
$file_types[ 'files' ][ 'jpeg' ] = array( 'icon' => 'file_img', 'view' => 'image', 'edit' => false, 'mediatype' => 'image', 'mimetype' => 'image/jpeg' );
$file_types[ 'files' ][ 'jpe' ]  = array( 'icon' => 'file_img', 'view' => 'image', 'edit' => false, 'mediatype' => 'image', 'mimetype' => 'image/jpeg' );
$file_types[ 'files' ][ 'bmp' ]  = array( 'icon' => 'file_img', 'view' => 'image', 'edit' => false, 'mediatype' => 'image', 'mimetype' => 'image/bmp' );
$file_types[ 'files' ][ 'tiff' ] = array( 'icon' => 'file_img', 'view' => 'image', 'edit' => false, 'mediatype' => 'image', 'mimetype' => 'image/tiff' );
$file_types[ 'files' ][ 'psd' ]  = array( 'icon' => 'file_img', 'view' => 'image', 'edit' => false, 'mediatype' => 'image', 'mimetype' => 'image/vnd.adobe.photoshop' );

// text files
$file_types[ 'files' ][ 'txt' ] = array( 'icon' => 'file_txt', 'view' => 'text', 'edit' => 'text', 'mediatype' => 'text', 'mimetype' => 'text/plain' );
$file_types[ 'files' ][ 'wri' ] = array( 'icon' => 'file_txt', 'view' => 'text', 'edit' => 'text', 'mediatype' => 'text', 'mimetype' => 'application/x-mswrite' );
$file_types[ 'files' ][ 'nfo' ] = array( 'icon' => 'file_txt', 'view' => 'text', 'edit' => 'text', 'mediatype' => 'text', 'mimetype' => 'text/plain' );

// archive files
$file_types[ 'files' ][ 'zip' ]  = array( 'icon' => 'file_arc', 'view' => false, 'edit' => false, 'mediatype' => 'archive', 'mimetype' => 'application/zip' );
$file_types[ 'files' ][ 'rar' ]  = array( 'icon' => 'file_arc', 'view' => false, 'edit' => false, 'mediatype' => 'archive', 'mimetype' => 'application/x-rar-compressed' );
$file_types[ 'files' ][ 'tar' ]  = array( 'icon' => 'file_arc', 'view' => false, 'edit' => false, 'mediatype' => 'archive', 'mimetype' => 'application/x-tar' );
$file_types[ 'files' ][ 'gz' ]   = array( 'icon' => 'file_arc', 'view' => false, 'edit' => false, 'mediatype' => 'archive', 'mimetype' => 'application/x-gzip' );
$file_types[ 'files' ][ 'sitx' ] = array( 'icon' => 'file_arc', 'view' => false, 'edit' => false, 'mediatype' => 'archive', 'mimetype' => 'application/x-stuffitx' );
$file_types[ 'files' ][ 'sit' ]  = array( 'icon' => 'file_arc', 'view' => false, 'edit' => false, 'mediatype' => 'archive', 'mimetype' => 'application/zip' );
$file_types[ 'files' ][ 'hqx' ]  = array( 'icon' => 'file_arc', 'view' => false, 'edit' => false, 'mediatype' => 'archive', 'mimetype' => 'application/zip' );

// audio files
$file_types[ 'files' ][ 'mp3' ] = array( 'icon' => 'file_snd', 'view' => 'audio', 'edit' => false, 'mediatype' => 'sound', 'mimetype' => 'audio/mpeg' );
$file_types[ 'files' ][ 'wav' ] = array( 'icon' => 'file_snd', 'view' => false, 'edit' => false, 'mediatype' => 'sound', 'mimetype' => 'audio/x-wav' );
$file_types[ 'files' ][ 'aif' ] = array( 'icon' => 'file_snd', 'view' => false, 'edit' => false, 'mediatype' => 'sound', 'mimetype' => 'audio/x-aiff' );
$file_types[ 'files' ][ 'ogg' ] = array( 'icon' => 'file_snd', 'view' => false, 'edit' => false, 'mediatype' => 'sound', 'mimetype' => 'audio/ogg' );
$file_types[ 'files' ][ 'wma' ] = array( 'icon' => 'file_snd', 'view' => false, 'edit' => false, 'mediatype' => 'sound', 'mimetype' => 'audio/x-ms-wma' );

// movie files
$file_types[ 'files' ][ '3gp' ] = array( 'icon' => 'file_vid', 'view' => false, 'edit' => false, 'mediatype' => 'movie', 'mimetype' => 'video/3gpp' );
$file_types[ 'files' ][ 'avi' ] = array( 'icon' => 'file_vid', 'view' => false, 'edit' => false, 'mediatype' => 'movie', 'mimetype' => 'video/x-msvideo' );
$file_types[ 'files' ][ 'mp4' ] = array( 'icon' => 'file_vid', 'view' => false, 'edit' => false, 'mediatype' => 'movie', 'mimetype' => 'video/mp4' );
$file_types[ 'files' ][ 'mov' ] = array( 'icon' => 'file_vid', 'view' => 'video', 'edit' => false, 'mediatype' => 'movie', 'mimetype' => 'video/quicktime' );
$file_types[ 'files' ][ 'flv' ] = array( 'icon' => 'file_vid', 'view' => 'video', 'edit' => false, 'mediatype' => 'movie', 'mimetype' => 'video/flv' );
$file_types[ 'files' ][ 'mpg' ] = array( 'icon' => 'file_vid', 'view' => false, 'edit' => false, 'mediatype' => 'movie', 'mimetype' => 'video/mpg' );

// shell script files
$file_types[ 'files' ][ 'sh' ]  = array( 'icon' => 'file_sh', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => 'application/x-sh' );
$file_types[ 'files' ][ 'bat' ] = array( 'icon' => 'file_sh', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => 'application/x-msdownload' );
$file_types[ 'files' ][ 'cmd' ] = array( 'icon' => 'file_sh', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => 'text/plain' );

// web/ programming files
$file_types[ 'files' ][ 'htm' ]   = array( 'icon' => 'file_html', 'view' => 'text', 'edit' => 'text', 'mediatype' => 'html', 'mimetype' => 'text/html' );
$file_types[ 'files' ][ 'html' ]  = array( 'icon' => 'file_html', 'view' => 'text', 'edit' => 'text', 'mediatype' => 'html', 'mimetype' => 'text/html' );
$file_types[ 'files' ][ 'xhtml' ] = array( 'icon' => 'file_html', 'view' => 'text', 'edit' => 'text', 'mediatype' => 'html', 'mimetype' => 'application/xhtml+xml' );
$file_types[ 'files' ][ 'shtml' ] = array( 'icon' => 'file_html', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => 'text/html' );
$file_types[ 'files' ][ 'php' ]   = array( 'icon' => 'file_php', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => 'application/x-httpd-php' );
$file_types[ 'files' ][ 'phtml' ] = array( 'icon' => 'file_php', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => 'application/x-httpd-php' );
$file_types[ 'files' ][ 'asp' ]   = array( 'icon' => 'file_html', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => '' );
$file_types[ 'files' ][ 'jsp' ]   = array( 'icon' => 'file_java', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => '' );
$file_types[ 'files' ][ 'cfm' ]   = array( 'icon' => 'file_html', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => '' );
$file_types[ 'files' ][ 'js' ]    = array( 'icon' => 'file_js', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => 'application/javascript' );
$file_types[ 'files' ][ 'xml' ]   = array( 'icon' => 'file_html', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => 'text/xml' );
$file_types[ 'files' ][ 'css' ]   = array( 'icon' => 'file_css', 'view' => 'text', 'edit' => 'text', 'mediatype' => 'stylesheet', 'mimetype' => 'text/css' );
$file_types[ 'files' ][ 'pl' ]    = array( 'icon' => 'file_js', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => '' );
$file_types[ 'files' ][ 'py' ]    = array( 'icon' => 'file_js', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => '' );
$file_types[ 'files' ][ 'swf' ]   = array( 'icon' => 'file_swf', 'view' => false, 'edit' => false, 'mediatype' => 'flash', 'mimetype' => 'application/x-shockwave-flash' );
$file_types[ 'files' ][ 'rb' ]    = array( 'icon' => 'file_rb', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => '' );
$file_types[ 'files' ][ 'lol' ]   = array( 'icon' => 'file_lol', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => '' );

// other/ misc files
$file_types[ 'files' ][ 'doc' ]      = array( 'icon' => 'file_doc', 'view' => false, 'edit' => false, 'mediatype' => 'document', 'mimetype' => 'application/msword' );
$file_types[ 'files' ][ 'xls' ]      = array( 'icon' => 'file_xls', 'view' => false, 'edit' => false, 'mediatype' => 'spreadsheet', 'mimetype' => 'application/vnd.ms-excel' );
$file_types[ 'files' ][ 'ppt' ]      = array( 'icon' => 'file_ppt', 'view' => false, 'edit' => false, 'mediatype' => 'presentation', 'mimetype' => 'application/vnd.ms-powerpoint' );
$file_types[ 'files' ][ 'docx' ]     = array( 'icon' => 'file_doc', 'view' => false, 'edit' => false, 'mediatype' => 'document', 'mimetype' => 'application/msword' );
$file_types[ 'files' ][ 'xlsx' ]     = array( 'icon' => 'file_xls', 'view' => false, 'edit' => false, 'mediatype' => 'spreadsheet', 'mimetype' => 'application/vnd.ms-excel' );
$file_types[ 'files' ][ 'pptx' ]     = array( 'icon' => 'file_ppt', 'view' => false, 'edit' => false, 'mediatype' => 'presentation', 'mimetype' => 'application/vnd.ms-powerpoint' );
$file_types[ 'files' ][ 'pdf' ]      = array( 'icon' => 'file_pdf', 'view' => false, 'edit' => false, 'mediatype' => 'pdf', 'mimetype' => 'application/pdf' );
$file_types[ 'files' ][ 'exe' ]      = array( 'icon' => 'file_exe', 'view' => false, 'edit' => false, 'mediatype' => false, 'mimetype' => 'application/x-msdownload' );
$file_types[ 'files' ][ 'dll' ]      = array( 'icon' => 'file_exe', 'view' => false, 'edit' => false, 'mediatype' => false, 'mimetype' => 'application/x-msdownload' );
$file_types[ 'files' ][ 'elf' ]      = array( 'icon' => 'file_exe', 'view' => false, 'edit' => false, 'mediatype' => false, 'mimetype' => '' );
$file_types[ 'files' ][ 'htaccess' ] = array( 'icon' => 'file_js', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => '' );
$file_types[ 'files' ][ 'java' ]     = array( 'icon' => 'file_java', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => 'text/x-java-source' );
$file_types[ 'files' ][ 'sql' ]      = array( 'icon' => 'file_sql', 'view' => 'text', 'edit' => 'text', 'mediatype' => false, 'mimetype' => '' );
$file_types[ 'files' ][ 'data' ]     = array( 'icon' => 'file_sql', 'view' => false, 'edit' => false, 'mediatype' => false, 'mimetype' => '' );
$file_types[ 'files' ][ 'bak' ]      = array( 'icon' => 'file_bak', 'view' => false, 'edit' => false, 'mediatype' => false, 'mimetype' => '' );
?>