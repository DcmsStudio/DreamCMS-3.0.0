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
 * @file        Loadattachment.php
 */
class Addon_Forum_Action_Loadattachment extends Addon_Forum_Helper_Base
{

    public function execute()
    {
        if ( $this->isFrontend() )
        {
            $this->getFrontend();
        }
        else
        {
            
        }
    }

    private function getFrontend()
    {
        $attachmentid = ( HTTP::input( 'attachmentid' ) ? HTTP::input( 'attachmentid' ) : null );
	    $_attachments = Session::get('forumattachments');


	    $id = (isset($_attachments[$attachmentid]) ? $_attachments[$attachmentid] : 0);


	    $this->initCache();

      #  $id = intval( Session::get( $attachmentid ) );
        $attach = $this->model->getAttachmentById( $id );
   #       print_r($attach); print_r(Session::get());die("$attachmentid");exit;

        $this->currentForumID = $attach[ 'forumid' ];


        $forum = (isset( $this->forum_by_id[ $attach[ 'forumid' ] ] ) ? $this->forum_by_id[ $attach[ 'forumid' ] ] : false );
        if ( !$forum )
        {
            $this->Page->send404( trans( 'Das Attachment wurde nicht gefunden.' ) );
        }


        if ( $attach[ 'forumid' ] )
        {

            $parents = $this->getParents( $attach[ 'forumid' ] );
            $this->buildBreadCrumb( $parents, $attach );
        }
        else
        {
            # $this->buildBreadCrumb( array(), $thread );
        }


        if ( !User::hasPerm( 'forum/cangetattachment' ) )
        {
            $this->Page->sendAccessError( trans( 'Sie besitzen nicht die nötigen Rechte, um dieses Attachment herunter zu laden!' ) );
            exit;
        }


        if ( !Library::isValidUUID( $attachmentid ) )
        {
            $this->Page->send404( trans( 'Das Attachment wurde nicht gefunden.' ) );
            exit;
        }


        if ( $forum[ 'access' ] != '' && !in_array( User::getGroupId(), explode( ',', $forum[ 'access' ] ) ) && !in_array( 0, explode( ',', $forum[ 'access' ] ) ) )
        {
            $this->Page->sendAccessError( trans( 'Sie besitzen nicht die nötigen Rechte, um dieses Attachment herunter zu laden!' ) );
        }




        if ( !$attach[ 'attachmentid' ] )
        {
            $this->Page->send404( trans( 'Das Attachment wurde nicht gefunden.' ) );
        }


        if ( !is_file( PAGE_PATH . $attach[ 'path' ] ) )
        {
            $this->Page->send404( trans( 'Das Attachment existiert nicht mehr.') );
            exit;
        }




        $this->model->updateAttachmentHits( $id );


        Library::skipDebug();

        @ini_set( 'zlib.output_compression', 'Off' );

        header( "HTTP/1.0 200 OK" );
        header( "HTTP/1.1 200 OK" );
        header( 'X-Powered-By: DreamCMS ' . VERSION );
        header( "Cache-Control: no-store, no-cache, must-revalidate" );
        header( "Cache-Control: post-check=0, pre-check=0", false );
        header( "Pragma: no-cache" );
        //  header('Content-Type: application/octet-stream'. $attach['mime']);



        /**
         *  @TODO Download Counter Not work with MOD_REWRITE when download a Image, all other files works :(
         */
        $ext = Library::getExtension( $attach[ 'path' ] );

        if ( in_array( $ext, array(
                    'gif',
                    'png',
                    'jpg',
                    'jpeg' ) ) )
        {
            header( 'Content-Type: ' . $attach[ 'mime' ] );
        }
        else
        {
            header( 'Content-Type: application/octet-stream' );
        }

        header( 'Content-Length: ' . @filesize( (PAGE_PATH . $attach[ 'path' ] ) ) );


        $fname = explode( '/', str_replace( '\\', '/', $attach[ 'path' ] ) );
        $fname = $fname[ count( $fname ) - 1 ];

        header( "Content-disposition: inline; filename=\"$fname\"" );

        readfile( PAGE_PATH . $attach[ 'path' ] );

        exit;
    }

}
