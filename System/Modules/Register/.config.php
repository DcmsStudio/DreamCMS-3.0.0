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
 * @package      Register
 * @version      3.0.0 Beta
 * @category     /Volumes/Daten/Web/DCMS201/System/Modules/Register//Volumes/Daten/Web/DCMS201/System/Modules/Register/
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         .config.php
 */

if ( !defined('IN') )
{
	throw new BaseException('No direct use allowed!');
}


$cfg[ 'appitems_perpage' ] = 15;

$cfg[ 'backup' ][ 'oldbackups' ] = 86000;

$cfg[ 'pagedefaultcachetime' ] = 3600;

$cfg[ 'pagedefaultenablecaching' ] = 1;

$cfg[ 'sharcounterupdate' ] = 1200;

$cfg[ 'system_cachetimeout' ] = 99600;

$cfg[ 'versioning_period' ] = 7776000;

$cfg[ 'pagedefaultsearchable' ] = 1;

$cfg[ 'pagedefaultclickanalyse' ] = 1;

$cfg[ 'autolock' ] = 1;

$cfg[ 'cookie_timer' ] = 3600;

$cfg[ 'cookiedomain' ] = '';

$cfg[ 'cookiepath' ] = '/';

$cfg[ 'default_startweek' ] = 1;

$cfg[ 'default_timezoneoffset' ] = 404;

$cfg[ 'timeformat' ] = 'H:i:s';

$cfg[ 'dateformat' ] = 'd.m.Y';

$cfg[ 'smtp_port' ] = 25;

$cfg[ 'smtp_encryption' ] = 'ssl';

$cfg[ 'smtp_server' ] = 'mail.your-server.de';

$cfg[ 'mailtype' ] = 0;

$cfg[ 'use_email_debugger' ] = 0;

$cfg[ 'webmastermail' ] = 'info@dcms-studio.de';

$cfg[ 'frommail' ] = 'info@dcms-studio.de';

$cfg[ 'smtp_user' ] = 'info@dcms-studio.de';

$cfg[ 'smtp_password' ] = 'c0801p78';

$cfg[ 'forum' ][ 'postsperpage' ] = 15;

$cfg[ 'forum' ][ 'threadsperpage' ] = 20;

$cfg[ 'forum' ][ 'uselikesystem' ] = '';

$cfg[ 'googleapikey' ] = 'AIzaSyDVUYIDUf754RQ46PNp3ctkdUcdDz853T4';

$cfg[ 'mod_rewrite_suffix' ] = 'html';

$cfg[ 'twittername' ] = 'Dream_CMS';

$cfg[ 'mod_rewrite' ] = 1;

$cfg[ 'locale' ] = 'de_DE';

$cfg[ 'websiteoffline' ] = 0;

$cfg[ 'portalurl' ] = 'http://www.dcms-studio.de';

$cfg[ 'pagename' ] = 'Dream Design Studio - DreamCMS 3.0 Beta 1';

$cfg[ 'meta_keywords' ] = 'Content Management, Content Management System, dreamcms, Dream CMS, GPL, OpenSource, Open Source, Web-CMS, WCMS';

$cfg[ 'meta_robots' ] = 'index,follow';

$cfg[ 'meta_description' ] = 'Das DreamCMS ist ein web-basiertes Content Management System, das barrierefreie Webseiten generiert. Es verwendet Web 2.0-Technologien, unterstützt mehrere Sprachen und kann einfach erlernt und erweitert werden.';

$cfg[ 'meta_revisitafter' ] = '5 Days';

$cfg[ 'meta_author' ] = 'Marcel Domke';

$cfg[ 'meta_copyright' ] = 'Marcel Domke';

$cfg[ 'news' ][ 'usecomments' ] = 1;

$cfg[ 'news' ][ 'perpage' ] = 25;

$cfg[ 'news' ][ 'userating' ] = 1;

$cfg[ 'sendnocacheheaders' ] = 1;

$cfg[ 'sendheaders' ] = 1;

$cfg[ 'pretty_html' ] = 1;

$cfg[ 'compress_html' ] = 0;

$cfg[ 'gziplevel' ] = 5;

$cfg[ 'gzip' ] = 1;

$cfg[ 'compress_js' ] = '';

$cfg[ 'use_cache_system' ] = 1;

$cfg[ 'register' ][ 'emailverifymode' ] = 1;

$cfg[ 'register' ][ 'allowregister' ] = 1;

$cfg[ 'sortby' ] = 'relevance_desc';

$cfg[ 'max_failed_logins' ] = 3;

$cfg[ 'block_failed_logins' ] = 1;

$cfg[ 'cli_key' ] = '9012065c488b4ba37c7497e4263fe97d';

$cfg[ 'crypt_key' ] = 'bf260a5e0d78becf859cadab90deba71';

$cfg[ 'minuserpasswordlength' ] = 6;

$cfg[ 'adminsession_timeout' ] = 4800;

$cfg[ 'usersession_timeout' ] = 4800;

$cfg[ 'failed_login_timeout' ] = 60;

$cfg[ 'showuserlevelinprofile' ] = 0;

$cfg[ 'maxsigimage' ] = 5;

$cfg[ 'allowsigsmilies' ] = 1;

$cfg[ 'allowsigbbcode' ] = 1;

$cfg[ 'allowsightml' ] = 0;

$cfg[ 'allowflashavatar' ] = 0;

$cfg[ 'showavatar' ] = 1;

$cfg[ 'membersperpage' ] = 20;
?>