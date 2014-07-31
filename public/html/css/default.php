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
 * @file         default.php
 */

error_reporting(0);
$etag = md5(__FILE__);

ob_start();

if ( isset($_SERVER[ 'HTTP_IF_NONE_MATCH' ]) && str_replace( '"', '', stripslashes( $_SERVER[ 'HTTP_IF_NONE_MATCH' ] ) ) == $etag )
{

	// Datei/ETag im Browser Cache vorhanden?
	header('HTTP/1.0 304 Not Modified'); // entsprechenden Header senden => Datei wird nicht geladen

	exit();
}


header('Content-Type: text/css', true);
header('Cache-Control: maxage=86000');
header('Expires: '. gmdate("D, d M Y H:i:s", time() + 860000) . " GMT");
header('Etag: '. $etag);



echo file_get_contents("../js/jquery/css/smoothness/jquery-ui.css");
echo file_get_contents("../js/jquery/fancybox/style.css");
echo file_get_contents("../js/syntax/styles/shCore.css");
echo file_get_contents("../js/syntax/styles/shThemeDefault.css");
echo file_get_contents("subcols.css");
echo file_get_contents("subcols_extended.css");

exit;