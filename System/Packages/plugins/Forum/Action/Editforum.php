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
 * @file        Editforum.php
 */
class Addon_Forum_Action_Editforum extends Addon_Forum_Helper_Base
{

    public function execute()
    {
        if ( $this->isFrontend() )
        {
            return;
        }


        $id = intval( HTTP::input( 'id' ) );

        $data = array();


        /*

        $modelTables = $this->model->getConfig( 'tables' );
        if ( !isset( $modelTables[ 'board' ] ) )
        {
            throw new BaseException( 'The Forum modul has no Model configuration! ' . print_r( $modelTables, true ) );
        }

        $this->Document->setTableConfiguration( 'board', $modelTables[ 'board' ] );
        $this->Document->getMetaInstance()->setMetadataType( true );

        */


        $this->Document
            ->validateModel('board', true)
            ->loadConfig()
            ->getMetaInstance()
            ->setMetadataType(true);






        // Send ajax Rollback?
        if ( HTTP::input( 'transrollback' ) )
        {
            $this->model->rollbackForumTranslation( $id );
            Library::sendJson( true );
        }





        if ( $this->_post( 'send' ) )
        {
            if ( $id )
            {
                $this->model->saveForumTranslation( $id, $this->_post() );
                Library::log( sprintf( 'Has update the Forum %s (ID: %s)', $this->_post( 'title' ), $id ) );
                Library::sendJson( true, sprintf( trans( 'Das Forum `%s` wurde aktualisiert' ), $this->_post( 'title' ) ) );
            }
            else
            {
                $newid = $this->model->saveForumTranslation( 0, $this->_post() );
                Library::log( sprintf( 'Has create the Forum %s (ID: %s)', $this->_post( 'title' ), $newid ) );
                Library::sendJson( true, sprintf( trans( 'Das Forum `%s` wurde erstellt' ), $this->_post( 'title' ) ), $newid );
            }
        }




        $groups = $this->getUserGroups();
        $data[ 'access_options' ] = Arr::convertKeys( $groups, array(
                    'groupid' => 'value',
                    'title'   => 'label' ) );
        array_unshift( $data[ 'access_options' ], array(
            'value' => 0,
            'label' => trans( 'Alle Benutzergruppen' ) ) );
        $data[ 'access_options' ] = Library::unempty( $data[ 'access_options' ] );


        if ( $id )
        {

            $hasTranslation = $this->model->hasForumTranslation( $id );

            // Create translation if not exists (temporary)
            // If the User click close tab or cancel then send a rollback event via ajax
            if ( !$hasTranslation )
            {
                // Copy the original article to translated
                $this->model->copyOriginalForumTranslation( $id );
            }

            $data[ 'forum' ] = $this->model->getForumById( $id );

            if ( $data[ 'forum' ][ 'forumpassword' ] )
            {
                $data[ 'forum' ][ 'forumpassword' ] = Library::decrypt( $data[ 'forum' ][ 'forumpassword' ] );
            }

            $data[ 'access_options' ][ 'selected' ] = (!empty( $data[ 'forum' ][ 'access' ] ) ? explode( ',', $data[ 'forum' ][ 'access' ] ) : array(
                        0 ) );
        }
        else
        {
            $data[ 'forum' ] = array();
            $data[ 'access_options' ][ 'selected' ] = array(
                0 );
        }

        /**
         * Init Metadata
         */
        $this->Document->getMetaInstance()->initMetadata( $data[ 'forum' ] );

        $this->getForumTree();
        $data[ 'forumtree' ] = $this->catcache;

        Library::addNavi( trans( 'Foren' ) );
        Library::addNavi( ($id ? sprintf( trans( 'Forum `%s` bearbeiten' ), $data[ 'forum' ][ 'title' ] ) : trans( 'Forum erstellen' ) ) );

        $this->Template->process( 'forum/edit', $data, true );
        exit;
    }

}
