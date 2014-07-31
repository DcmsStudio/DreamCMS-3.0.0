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
 * @file        Empty.php
 */
class Addon_Forum_Action_Empty extends Addon_Forum_Helper_Base
{

    public function execute()
    {
        if ( $this->isFrontend() )
        {
            return;
        }

        $id = intval( HTTP::input( 'id' ) );

        $forum = $this->model->getForumById( $id );

        $postids = array();
        $attachmentids = array();

        $threads = $this->db->query( "SELECT threadid FROM %tp%board_threads WHERE forumid = ?", $id )->fetchAll();
        foreach ( $threads as $r )
        {
            $posts = $this->db->query( "SELECT postid FROM %tp%board_posts WHERE threadid = ?", $r[ 'threadid' ] )->fetchAll();
            foreach ( $posts as $rs )
            {
                $postids[] = $rs[ 'postid' ];
            }
        }

        unset( $threads );

        $attachments = $this->db->query( "SELECT attachmentid, path FROM %tp%board_attachments WHERE postid IN(" . implode( ',', $postids ) . ")" )->fetchAll();
        foreach ( $attachments as $r )
        {
            if ( is_file( ROOT_PATH . $r[ 'path' ] ) )
            {
                unlink( ROOT_PATH . $r[ 'path' ] );
            }

            $attachmentids[] = $r[ 'attachmentid' ];
        }

        unset( $attachments );

        if ( count( $attachmentids ) )
        {
            $this->db->query( "DELETE FROM %tp%board_attachments WHERE attachmentid IN(" . implode( ',', $attachmentids ) . ")" );
            unset( $attachmentids );
        }

        if ( count( $postids ) )
        {
            $this->db->query( "DELETE FROM %tp%board_posts WHERE WHERE postid IN(" . implode( ',', $postids ) . ")" );
            unset( $postids );
        }

        $this->db->query( "DELETE FROM %tp%board_threads WHERE forumid = ?", $id );

        $this->db->query( "UPDATE %tp%board SET
                          threadcounter = 0, postcounter = 0, lastposttime=0,
                          lastpostthreadid = 0, lastpostuserid = 0, lastpostusername = '', lastposttitle = ''
                          WHERE forumid = ?", $id );

        Library::log( sprintf( 'Has empty the Forum `%s`', $forum[ 'title' ] ) );
        Library::sendJson( true, sprintf( trans( "Forum `%s` erfolgreich geleert." ), $forum[ 'title' ] ) );
    }

}
