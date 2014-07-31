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
 * @package      Importer
 * @version      3.0.0 Beta
 * @category     Config
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
return array(
    0  => array(
        'controller'           => 'news',
        'action'               => 'captcha',
        'rule'                 => 'news/captcha',
    ),
    1  => array(
        'controller' => 'news',
        'action'     => 'comment',
        'rule'       => 'news/comment/<id:int>',
    ),
    2  => array(
        'controller' => 'news',
        'action'     => 'index',
        'rule'       => '^news$',
    ),
    3  => array(
        'controller' => 'news',
        'action'     => 'index',
        'rule'       => 'news/tag/<tag:any>'
    ),
    4  => array(
        'controller' => 'news',
        'action'     => 'index',
        'rule'       => '^news/category$'
    ),
    5  => array(
        'controller' => 'news',
        'action'     => 'index',
        'rule'       => '^news/category/<page:int>[/<order:alpha>,<sort:asc|desc>]$',
    ),
    6  => array(
        'controller' => 'news',
        'action'     => 'index',
        'rule'       => '^news/category/<catid:int>[/<page:int>,<order:alphanum>,<sort:asc|desc>]$',
    ),
    7  => array(
        'controller' => 'news',
        'action'     => 'index',
        'rule'       => '^news/category/<catid:int>[/<perpage:int>,<start:int>,<end:int>,<order:alphanum>,<sort:asc|desc>,<q:any>,<page:int>]$',
    ),

    8  => array(
        'controller' => 'news',
        'action'     => 'show',
        'rule'       => 'news/item[/<id:int>,<catid:int>,<page:int>]',
    ),
    10 => array(
        'controller' => 'news',
        'action'     => 'show',
        'rule'       => 'newsrate/<id:int>/<rate:\d{1,5}>',
    ),
    11 => array(
        'controller' => 'news',
        'action'     => 'show',
        'rule'       => 'news/rate/<id:int>/<rate:\d{1,5}>',
    )
);