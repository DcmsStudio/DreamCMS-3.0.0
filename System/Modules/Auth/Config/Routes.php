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
        'controller'           => 'auth',
        'action'               => 'login',
        'rule'                 => '(auth)/login',
        'params'               => null,
        'paramkeys'            => null,
        'compiled_static_rule' => '(auth)/login/?',
        'map_index'            => array(
            1 => 'action',
            2 => 'file'
        )
    ),
    1 => array(
        'controller'           => 'auth',
        'action'               => 'logout',
        'rule'                 => '(auth)/logout',
        'params'               => null,
        'paramkeys'            => null,
        'compiled_static_rule' => '(auth)/logout/?',
        'map_index'            => array(
            1 => 'action',
            2 => 'file'
        )
    ),
    2 => array(
        'controller'           => 'auth',
        'action'               => 'lostpassword',
        'rule'                 => 'auth/lostpw',
        'params'               => null,
        'paramkeys'            => null,
        'compiled_static_rule' => 'auth/lostpw/?',
        'map_index'            => array(
            1 => 'action',
            2 => 'file'
        )
    )
);