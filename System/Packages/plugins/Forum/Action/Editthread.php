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
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Editthread.php
 */
class Addon_Forum_Action_Editthread extends Addon_Forum_Helper_Base
{

    public function execute()
    {
        if ( $this->isFrontend() )
        {

            $threadid = intval( $this->input( 'threadid' ) );
            if ( !$threadid )
            {
                $this->Page->send404( trans( 'Dieses Thema wurde leider nicht gefunden.' ) );
            }

            $this->initCache();

            $thread = $this->model->getThreadById( $threadid );
            $this->currentForumID = $thread[ 'forumid' ];
            $parents = $this->getParents( $thread[ 'forumid' ] );
            $this->buildBreadCrumb( $parents, $thread );


            if ( $this->_post( 'send' ) )
            {

	            $this->model->sync('thread', $threadid);
	            $this->model->sync('forum', $thread[ 'forumid' ]);


               // $this->model->updateThreadCounters( $thread[ 'forumid' ] );
               // $this->model->updateForumCounters( $thread[ 'forumid' ] );
            }

            $data[ 'thread' ] = $thread;
            unset( $thread );

            $this->Template->process( 'board/mod_thread_edit', $data, true );
            exit;
        }
    }

}
