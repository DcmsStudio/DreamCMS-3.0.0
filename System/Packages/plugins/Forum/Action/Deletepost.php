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
 * @file        Deletepost.php
 */
class Addon_Forum_Action_Deletepost extends Addon_Forum_Helper_Base
{

    public function execute()
    {
        if ( $this->isFrontend() )
        {

            $postid = intval( $this->input( 'postid' ) );
            if ( !$postid )
            {
                $this->Page->send404( trans( 'Dieser Beitrag wurde leider nicht gefunden.' ) );
            }

            $this->initCache();

            $post = $this->model->getPostById( $postid );

            $this->currentForumID = $post[ 'forumid' ];
            $parents = $this->getParents( $post[ 'forumid' ] );
            $this->buildBreadCrumb( $parents, $post );


            if ( $this->_post( 'send' ) )
            {

	            $this->model->sync('thread', $post[ 'threadid' ]);
	            $this->model->sync('forum', $post[ 'forumid' ]);

	            $this->updateSearchIndexer( $post[ 'threadid' ], null, 0 );


                //$this->model->updateThreadCounters( $post[ 'forumid' ] );
                //$this->model->updateForumCounters( $post[ 'forumid' ] );
            }

            $data[ 'post' ] = $post;

            $this->Template->process( 'board/mod_post_delete', $data, true );
            exit;
        }
    }

}
