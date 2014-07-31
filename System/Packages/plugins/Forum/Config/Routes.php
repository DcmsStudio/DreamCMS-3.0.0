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
 * @package     DreamCMS
 * @version     3.0.0 Beta
 * @category    Plugin s
 * @copyright    2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Routes.php
 */

/*
return array(
    0 => array(
        'controller' => 'forum',
        'action'     => 'addthread',
        'rule'       => 'forum/newthread/<forumid:int>',
    ),
    1 => array(
        'controller' => 'forum',
        'action'     => 'getattachment',
        'rule'       => 'forum/loadattachment/<attachmentid:uuid>',
    ),
    2 => array(
        'controller' => 'forum',
        'action'     => 'index',
        'rule'       => 'forum/<forumid:int>'
    ),
    3 => array(
        'controller' => 'forum',
        'action'     => 'index',
        'rule'       => 'forum/<forumname:any>',
    ),
    4 => array(
        'controller' => 'forum',
        'action'     => 'thread',
        'rule'       => 'thread/<threadid:int>[/<page:int>]',
    ),
    5 => array(
        'controller' => 'forum',
        'action'     => 'replythread',
        'rule'       => 'replythread/<threadid:int>[/<do:reply|quote>,<postid:int>]',
    ),
    6 => array(
        'controller' => 'forum',
        'action'     => 'upload',
        'rule'       => 'forum/upload[/<do:remove>,<attachmentid:int>]',
    )
);
*/

$route[ ] = array(
    'rule'   => 'forum',
    'action' => 'run'
);

$route[ ] = array(
    'rule'      => 'forum/:forumid',
    'action'    => 'run',
    'params'    => array(
        '[\d]+?'
    ),
    'paramkeys' => array(
        'forumid'
    )
);

$route[ ] = array(
    'rule'      => 'forum/:forumid/:page',
    'action'    => 'run',
    'params'    => array(
        '[\d]+?',
        '[\d]+?'
    ),
    'paramkeys' => array(
        'forumid',
        'page'
    )
);

$route[ ] = array(
    'rule'      => 'forum/thread/:threadid',
    'action'    => 'thread',
    'params'    => array(
        '[\d]+?'
    ),
    'paramkeys' => array(
        'threadid'
    )
);

$route[ ] = array(
    'rule'      => 'forum/thread/:threadid/:page',
    'action'    => 'thread',
    'params'    => array(
        '[\d]+?',
        '[\d]+?'
    ),
    'paramkeys' => array(
        'threadid',
        'page'
    )
);

$route[ ] = array(
    'rule'      => 'forum/thread/:threadid/:postid/:do',
    'action'    => 'thread',
    'params'    => array(
        '[\d]+?',
        '[\d]+?',
        'like|dislike'
    ),
    'paramkeys' => array(
        'threadid',
        'postid',
        'do'
    )
);

$route[ ] = array(
    'rule'      => 'forum/editpost/:postid',
    'action'    => 'editpost',
    'params'    => array(
        '[\d]+?'
    ),
    'paramkeys' => array(
        'postid'
    )
);
$route[ ] = array(
    'rule'      => 'forum/deletepost/:postid',
    'action'    => 'deletepost',
    'params'    => array(
        '[\d]+?'
    ),
    'paramkeys' => array(
        'postid'
    )
);

$route[ ] = array(
    'rule'      => 'forum/newthread/:forumid',
    'action'    => 'newthread',
    'params'    => array(
        '[\d]+?'
    ),
    'paramkeys' => array(
        'forumid'
    )
);

$route[ ] = array(
    'rule'      => 'forum/replythread/:threadid',
    'action'    => 'replythread',
    'params'    => array(
        '[\d]+?'
    ),
    'paramkeys' => array(
        'threadid'
    )
);

$route[ ] = array(
    'rule'      => 'forum/replythread/:threadid/:do/:postid',
    'action'    => 'replythread',
    'params'    => array(
        '[\d]+?',
        '[\d]+?',
        'quote'
    ),
    'paramkeys' => array(
        'threadid',
        'postid',
        'do'
    )
);
$route[ ] = array(
    'rule'      => 'forum/replythread/:threadid/:do/:postid',
    'action'    => 'replythread',
    'params'    => array(
        '[\d]+?',
        '[\d]+?',
        'reply'
    ),
    'paramkeys' => array(
        'threadid',
        'postid',
        'do'
    )
);


$route[ ] = array(
    'rule'      => 'forum/loadattachment/:attachmentid',
    'action'    => 'loadattachment',
    'params'    => array(
        '[0-9a-f]{8}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{4}\-[0-9a-f]{12}'
    ),
    'paramkeys' => array(
        'attachmentid'
    )
);


// moderator
$route[ ] = array(
    'rule'      => 'forum/publishthread/:threadid/:mode',
    'action'    => 'publishthread',
    'params'    => array(
        '[\d]+?',
        '0|1'
    ),
    'paramkeys' => array(
        'threadid',
        'mode'
    )
);

$route[ ] = array(
    'rule'      => 'forum/publishpost/:postid/:mode',
    'action'    => 'publishpost',
    'params'    => array(
        '[\d]+?',
        '0|1'
    ),
    'paramkeys' => array(
        'postid',
        'mode'
    )
);


$route[ ] = array(
    'rule'      => 'forum/closethread/:threadid/:mode',
    'action'    => 'closethread',
    'params'    => array(
        '[\d]+?',
        '0|1'
    ),
    'paramkeys' => array(
        'threadid',
        'mode'
    )
);

$route[ ] = array(
    'rule'      => 'forum/deletethread/:threadid',
    'action'    => 'deletethread',
    'params'    => array(
        '[\d]+?'
    ),
    'paramkeys' => array(
        'threadid'
    )
);


$route[ ] = array(
    'rule'      => 'forum/pinthread/:threadid/:mode',
    'action'    => 'pin',
    'params'    => array(
        '[\d]+?',
        '0|1'
    ),
    'paramkeys' => array(
        'threadid',
        'mode'
    )
);
