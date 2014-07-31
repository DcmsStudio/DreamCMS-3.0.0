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
    0  => array(
        'controller' => 'main',
        'action'     => 'bbcodepreview',
        'rule'       => 'main/bbcodepreview/<allowedbbcode:alphanum>',
    ),
    1  => array(
        'controller' => 'main',
        'action'     => 'captcha',
        'rule'       => 'main/captcha/<audio:audio>/<hash:uuid>',
    ),
    3  => array(
        'controller' => 'main',
        'action'     => 'captcha',
        'rule'       => 'main/captcha/<hash:uuid>[/<refresh:refresh>]',
    ),
    4  => array(
        'controller' => 'main',
        'action'     => 'comment',
        'rule'       => 'main/comment'
    ),
    5  => array(
        'controller' => 'main',
        'action'     => 'corejs',
        'rule'       => 'main/corejs/<jscript:any>',
    ),
    6  => array(
        'controller' => 'main',
        'action'     => 'css',
        'rule'       => 'main/css/<css:any>'
    ),
    7  => array(
        'controller' => 'main',
        'action'     => 'imgpreview',
        'rule'       => 'main/imgpreview/<chain:alphanum>/<format:alpha>/<img:any>',
    ),
    8  => array(
        'controller' => 'main',
        'action'     => 'index',
        'rule'       => 'e/<error:400|401|403|404|500>',
    ),
    9  => array(
        'controller' => 'main',
        'action'     => 'js',
        'rule'       => 'main/js/<jscript:any>'
    ),
    10 => array(
        'controller' => 'main',
        'action'     => 'lang',
        'rule'       => 'main/lang',
    ),
    11 => array(
        'controller' => 'main',
        'action'     => 'logclick',
        'rule'       => 'main/logclick/<x:int>/<y:int>/<t:int>/<screen:alphanum>/<url:any>',
    ),
    12 => array(
        'controller' => 'main',
        'action'     => 'runcomponent',
        'rule'       => 'main/runcomponent/<com:a-zA-Z{2,}>[/<params:any>]',
    )
);