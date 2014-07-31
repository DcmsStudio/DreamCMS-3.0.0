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
 * @file         Routes.php
 */

return array(
    0 => array(
        'controller' => 'user',
        'action'     => 'avatar',
        'rule'       => 'user/avatar[/<page:int>]',
    ),

    1 => array(
        'controller' => 'user',
        'action'     => 'blog',
        'rule'       => 'user/blog/<user:any>[/<do:edit|add|delete|read>,<id:int>]',
    ),
    2 => array(
        'controller' => 'user',
        'action'     => 'controlpanel',
        'rule'       => 'user/controlpanel',
    ),
    4 => array(
        'controller' => 'user',
        'action'     => 'other',
        'rule'       => 'user/other',
    ),
    5 => array(
        'controller' => 'user',
        'action'     => 'password',
        'rule'       => 'user/password',
    ),
    6 => array(
        'controller' => 'user',
        'action'     => 'settings',
        'rule'       => 'user/settings',
    ),
    7 => array(
        'controller' => 'user',
        'action'     => 'signatur',
        'rule'       => 'user/signatur',
    )
);