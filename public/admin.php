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
 * @version     3.0.0 Beta
 * @category    
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        ajax.php
 */
define('INIT_MEMORY', memory_get_usage());

define('PUBLIC_PATH', str_replace('\\', '/', dirname(__FILE__)) . '/');
define('ROOT_PATH', substr(PUBLIC_PATH, 0, -7));
define('IN', true);
define('ADM_SCRIPT', true);
//
error_reporting( E_ALL  );

include ROOT_PATH . 'System/Library/Bootstrap.php';

$application = new Application('develpoment');
$application
    ->setupWebsiteDomain(true) // multiple Domain support is true
    ->setOptions(
        array(
            'defaultController' => 'dashboard',
            'defaultAction' => 'index'
        )
)->run();



?>