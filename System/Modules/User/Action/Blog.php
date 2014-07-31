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
 * @package      User
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Blog.php
 */
class User_Action_Blog extends Controller_Abstract
{

    private $user = false;

    private $item = false;

    public function execute()
    {

        if ( $this->isFrontend() )
        {
            $userid   = 0;
            $username = $this->input( 'user' );

            if ( is_numeric( $username ) && (int)$username )
            {
                $userid   = (int)$username;
                $username = null;
            }


            if ( !$userid && !$username && User::getUserId() )
            {
                $userid = User::getUserId();
            }


            if ( !$username && !$userid )
            {
                $this->Page->send404( trans( 'Dieser Blog existiert nicht!' ) );
            }

            $user = null;
            if ( $userid )
            {
                $user = User::getUserById( $userid );
            }
            else
            {
                $user = User::getUserByUsername( $username );
            }


            if ( !$user[ 'userid' ] )
            {
                $this->Page->send404( trans( 'Dieser Blog existiert nicht!' ) );
            }

            $this->user = $user;

            $do = $this->input( 'do' );

            switch ( $do )
            {
                case 'add':

                    $this->manage();
                    break;

                case 'edit':

                    $this->manage();

                    break;

                case 'delete':

                    $this->manage();
                    die( 'delete' );
                    break;

                case 'read':

                    $id = (int)$this->input( 'id' );
                    if ( !$id )
                    {
                        $this->Page->send404( trans( 'Dieser Blogeintrag existiert nicht!' ) );
                    }

                    $d = $this->model->getBlogItemById( $id );
                    if ( !$d[ 'id' ] )
                    {
                        $this->Page->send404( trans( 'Dieser Blogeintrag existiert nicht!' ) );
                    }

                    $d[ 'content' ] = BBCode::toXHTML( $d[ 'content' ] );
                    $d[ 'teaser' ]  = BBCode::toXHTML( $d[ 'teaser' ] );

                    $this->Document->setData( $d );
                    $this->Document->setDocumentID( $d[ 'id' ] );


                    $this->Breadcrumb->add( sprintf( trans( 'Profil von %s' ), $user[ 'username' ] ), 'profile/' . $user[ 'username' ] . '/' . Library::suggest( $user[ 'username' ], true ) );
                    $this->Breadcrumb->add( sprintf( trans( '%s´s Blog' ), $user[ 'username' ] ), 'user/blog/' . $user[ 'username' ] . '/' . Library::suggest( $user[ 'username' ], true ) );
                    $this->Breadcrumb->add( $d[ 'title' ] );

                    $this->Template->process( 'profile/blog_read', array(
                        'entry'    => $d,
                        'bloguser' => $user
                    ), true );


                    break;

                default:
                    $this->Template->addScript( 'Modules/User/asset/js/user.js' );
                    $d = $this->model->getUserBlock( $user[ 'userid' ] );

                    BBCode::setBBcodeHandler( 'userblog' );

                    $d = Library::unempty( $d );

                    foreach ( $d[ 'result' ] as &$r )
                    {
                        $r[ 'content' ] = BBCode::toXHTML( $r[ 'content' ] );
                        $r[ 'teaser' ]  = BBCode::toXHTML( $r[ 'teaser' ] );

                        $r[ 'url' ] = '/user/blog/' . $user[ 'username' ] . '/read/' . $r[ 'id' ] . '/' . Library::suggest( $r[ 'title' ], true );
                    }


                    $this->Breadcrumb->add( sprintf( trans( 'Profil von %s' ), $user[ 'username' ] ), 'profile/' . $user[ 'username' ] . '/' . Library::suggest( $user[ 'username' ] ) );
                    $this->Breadcrumb->add( sprintf( trans( '%s´s Blog' ), $user[ 'username' ] ) );

                    $this->Template->process( 'profile/blog', array(
                        'blogdata'        => $d[ 'result' ],
                        'blogitemcounter' => $d[ 'total' ],
                        'bloguser'        => $user
                    ), true );
                    break;

            }


            $this->Page->send404(trans('Blog nicht vorhanden!'));
        }
    }


    private function manage()
    {

        $do = $this->input( 'do' );

        if ( $do == 'add' )
        {
            $this->addEntry();
        }
        elseif ( $do == 'edit' )
        {
            $this->editEntry();
        }
        elseif ( $do == 'delete' )
        {
            $this->deleteEntry();
        }
    }

    private function editEntry()
    {

        if ( $this->user[ 'userid' ] != User::getUserId() && !User::getUserId() )
        {
            $this->Page->sendAccessError( trans( 'Wenn du den Blogeintrag bearbeiten möchtest logge dich ein.' ) );
        }

        if ( $this->user[ 'userid' ] != User::getUserId() && User::getUserId() )
        {
            $this->Page->sendError( trans( 'Dies ist leider nicht dein Blog!' ) );
        }

        $id = (int)$this->input( 'id' );
        if ( !$id )
        {
            $this->Page->send404( trans( 'Dieser Blogeintrag existiert nicht!' ) );
        }

        $d = $this->model->getBlogItemById( $id, true );
        if ( !$d[ 'id' ] )
        {
            $this->Page->send404( trans( 'Dieser Blogeintrag existiert nicht!' ) );
        }

        $msg   = false;
        $error = false;

        if ( $this->_post( 'send' ) )
        {
            $data = $this->_post();

            if ( !trim( $data[ 'title' ] ) )
            {
                $error .= trans( 'Der Titel deines Blogeintrags fehlt' );
            }
            elseif ( !trim( $data[ 'content' ] ) )
            {
                $error .= trans( 'Der Inhalt deines Blogeintrags fehlt' );
            }


            if ( !$error )
            {
                $this->model->saveBlogEntry( $id, $data );
                $msg = trans( 'Dein Blogeintrag wurde gespeichert' );


                // send pings
                $blogurl = '/user/blog/' . User::getUsername() . '/read/' . $id . '/' . Library::suggest( $data[ 'title' ], true );
                $ps = new PingService();
                $ps->setData($blogurl, $data[ 'title' ])->genericPing();
            }
        }


        $this->Template->addScript( 'Modules/User/asset/js/user.js' );
        $this->Template->process( 'profile/blog_edit', array(
            'error'    => $error,
            'notifier' => $msg,
            'blogdata' => $d,
            'bloguser' => $this->user
        ), true );
    }


    private function addEntry()
    {

        if ( $this->user[ 'userid' ] != User::getUserId() && !User::getUserId() )
        {
            $this->Page->sendAccessError( trans( 'Wenn du einen neuen Blogeintrag schreiben möchtest logge dich ein.' ) );
        }
        if ( $this->user[ 'userid' ] != User::getUserId() && User::getUserId() )
        {
            $this->Page->sendAccessError( trans( 'Dies ist leider nicht dein Blog!' ) );
        }

        $error = false;
        $msg   = false;

        if ( $this->_post( 'send' ) )
        {
            $data = $this->_post();

            if ( !trim( $data[ 'title' ] ) )
            {
                $error .= trans( 'Der Titel deines Blogeintrags fehlt' );
            }
            elseif ( !trim( $data[ 'content' ] ) )
            {
                $error .= trans( 'Der Inhalt deines Blogeintrags fehlt' );
            }

            if ( !$error )
            {
                $id = $this->model->saveBlogEntry( 0, $data );
                $msg = trans( 'Dein Blogeintrag wurde gespeichert' );


                // send pings
                $blogurl = '/user/blog/' . User::getUsername() . '/read/' . $id . '/' . Library::suggest( $data[ 'title' ], true );
                $ps = new PingService();
                $ps->setData($blogurl, $data[ 'title' ])->genericPing();
            }

            unset( $data );
        }


        $this->Template->addScript( 'Modules/User/asset/js/user.js' );
        $this->Template->process( 'profile/blog_edit', array(
            'error'    => $error,
            'notifier' => $msg,
            'bloguser' => $this->user
        ), true );
    }

    private function deleteEntry()
    {

        if ( $this->user[ 'userid' ] != User::getUserId() && !User::getUserId() )
        {
            $this->Page->sendAccessError( trans( 'Wenn du den Blogeintrag löschen möchtest logge dich ein.' ) );
        }
        if ( $this->user[ 'userid' ] != User::getUserId() && User::getUserId() )
        {
            $this->Page->sendError( trans( 'Dies ist leider nicht dein Blog!' ) );
        }

        $id = (int)$this->input( 'id' );
        if ( !$id )
        {
            $this->Page->send404( trans( 'Dieser Blogeintrag existiert nicht!' ) );
        }

        $d = $this->model->getBlogItemById( $id, true );
        if ( !$d[ 'id' ] )
        {
            $this->Page->send404( trans( 'Dieser Blogeintrag existiert nicht!' ) );
        }

        if ( $this->_post( 'send' ) )
        {

            Library::redirect( 'user/blog/' . $this->user[ 'username' ] );
        }

        $this->Template->addScript( 'Modules/User/asset/js/user.js' );
        $this->Template->process( 'profile/blog_delete', array(
            'notifier' => trans( 'Soll dein Blogeintrag `%s` wirklich gelöscht werden?' ),
            'bloguser' => $this->user
        ), true );
    }
}


?>