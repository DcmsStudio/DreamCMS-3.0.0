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
 * @package      Page
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Edit.php
 */
class Page_Action_Edit extends Controller_Abstract
{

    /**
     * @param bool $isNew
     * @throws BaseException
     */
    public function execute($isNew = false)
    {

        if ( $this->isFrontend() )
        {
            return;
        }


        $this->Document
            ->validateModel('pages', true)
            ->loadConfig()
            ->getMetaInstance()
            ->setMetadataType(true);

        if ( $this->input( 'send' ) == 'save' )
        {
            demoadm();
            $this->save();
        }

        $id = (int)$this->input( 'id' );


        $this->Document->addRollback();

        // Send ajax Rollback?
        if ( $this->input( 'transrollback' ) )
        {
            $this->model->rollbackTranslation( $id );
            $this->model->unlock( $id, 'page', 'index' );

            Library::sendJson( true );
        }


        /**
         * Init Tags
         */
        $this->load( 'ContentLock' );
        $this->load( 'Tags' );
        $this->Tags->setContentTable( 'pages_trans' );


        $r = array();


        if ( $id )
        {
            if ( ( $lockedBy = $this->model->checkLocked( $id, 'page', 'index' ) ) !== false )
            {
                Library::sendJson( false, sprintf( trans( 'Dieses Dokument wird bereits von %s bearbeitet.' ), $lockedBy ) );
            }



            $this->load( 'Versioning' );

            // Change version
            if ( $this->input( 'setVersion' ) )
            {
                demoadm();

                $this->Versioning->undoVersion( (int)$this->input( 'setVersion' ), $id, 'pages' );
                Library::sendJson( true );
            }

            $hasTranslation = $this->model->hasTranslation( $id );

            // Create translation if not exists (temporary)
            // If the User click close tab or cancel then send a rollback event via ajax
            if ( !$hasTranslation )
            {
                // Copy the original article to translated
                $this->model->copyOriginalTranslation( $id );
            }

            $transq = $this->buildTransWhere( 'pages', 'p.id', 'pt' );
            $r      = $this->db->query( 'SELECT p.*, pt.*,
                                u.username AS creator,
                                m.username AS modifer
                                FROM %tp%pages AS p
                                LEFT JOIN %tp%pages_trans AS pt ON (pt.id=p.id)
                                LEFT JOIN %tp%users AS u ON (u.userid=p.created_by)
                                LEFT JOIN %tp%users AS m ON (m.userid=p.modifed_by)
                                WHERE p.id= ? AND ' . $transq, $id )->fetch();

            if ( !$r[ 'id' ] )
            {
                Error::raise( "Page not exist! Code:" . __LINE__ );
            }


            if ( $this->input( 'tomenu' ) )
            {
                $r[ 'form_keyname' ] = 'id';
                $this->getGriditemAppendToMenu( 'pageitem', $r );
            }


            /**
             * Versioning
             */
            $record            = $this->db->query( 'SELECT * FROM %tp%pages WHERE id = ?', $id )->fetch();
            $recordTrans       = $this->db->query( 'SELECT * FROM %tp%pages_trans WHERE id = ? AND lang = ?', $id, CONTENT_TRANS )->fetch();
            // @todo add custom fields to version
            #$customfieldvalues = $this->model->getCustomFieldData( $r[ 'id' ], (int)$r[ 'pagetype' ] );


            /**
             * Init Metadata
             */
            $this->Document->getMetaInstance()->initMetadata( $r );


            // create init version
            $this->Versioning->createInitialVersion( $id, 'pages', $record, $recordTrans );


            Library::$versionRecords = $this->Versioning->getVersions( $id, 'pages' );

            $r[ 'modified' ] = ( !empty( $r[ 'modified' ] ) ? $r[ 'modified' ] : 0 );
            $r[ 'created' ]  = ( !empty( $r[ 'created' ] ) ? $r[ 'created' ] : 0 );

            $groups                = $this->getUserGroups();
            $r[ 'access_options' ] = Arr::convertKeys( $groups, array(
                'groupid' => 'value',
                'title'   => 'label'
            ) );
            array_unshift( $r[ 'access_options' ], array(
                'value' => 0,
                'label' => trans( 'Alle Benutzergruppen' )
            ) );
            $r[ 'access_options' ][ 'selected' ] = ( !empty( $r[ 'usergroups' ] ) ? explode( ',', $r[ 'usergroups' ] ) :
                array(
                    0
                ) );


            //
            $r[ 'content' ] = preg_replace( '/(' . preg_quote( $r[ 'alias' ], '/' ) . ( $r[ 'suffix' ] ?
                    '.' . $r[ 'suffix' ] : '' ) . '(#[a-z0-9_\-]+?))"/isU', '\\2"', $r[ 'content' ] );

            $r[ 'contenttags' ] = $this->Tags->getContentTags( $r[ 'tags' ] );

            $parents = $this->model->getParentPages( $r[ 'id' ] );

            $r[ 'parent_options' ] = Arr::convertKeys( $parents, array(
                'id'    => 'value',
                'title' => 'label'
            ) );

            array_unshift( $r[ 'parent_options' ], array(
                'value' => 0,
                'label' => trans( 'keine Seite' )
            ) );

            $r[ 'parent_options' ][ 'selected' ] = ( !empty( $r[ 'parentid' ] ) ? explode( ',', $r[ 'parentid' ] ) : array(
                0
            ) );


            //$this->ContentLock->lock( $id, 'page', 'index', $r[ 'title' ] );
            $this->model->lock( array(
                'pk'         => 'id',
                'table'      => '%tp%pages',
                'contentid'  => $id,
                'title'      => $r[ 'title' ],
                'controller' => 'page',
                'action'     => 'index'
            ) );
        }
        else
        {
            $groups = $this->getUserGroups();

            $r[ 'access_options' ] = Arr::convertKeys( $groups, array(
                'groupid' => 'value',
                'title'   => 'label'
            ) );

            array_unshift( $r[ 'access_options' ], array(
                'value' => 0,
                'label' => trans( 'Alle Benutzergruppen' )
            ) );

            $r[ 'access_options' ][ 'selected' ] = array(
                0
            );


            $parents = $this->model->getParentPages();

            $r[ 'parent_options' ] = Arr::convertKeys( $parents, array(
                'id'    => 'value',
                'title' => 'label'
            ) );

            array_unshift( $r[ 'parent_options' ], array(
                'value' => 0,
                'label' => trans( 'keine Seite' )
            ) );

            $r[ 'parent_options' ][ 'selected' ] = array(
                0
            );


            $r[ 'pagetype' ] = (int)$this->input( 'pagetype' );
        }


        $r[ 'layout_options' ] = $this->model->getContainerOptions();

        array_unshift( $r[ 'layout_options' ], array(
            'value' => '',
            'label' => trans( '--- Standart Layout ---' )
        ) );
        $r[ 'layout_options' ][ 'selected' ] = $r[ 'layout' ];


        $r[ 'layouts' ] = $this->getLayouts();

        $categories = $this->model->getCats( true );


        $tree = new Tree();
        $tree->setupData( $categories, 'id', 'parentid' );

        $c = $tree->buildRecurseArray();

        /*
        $emptyData = array (
            'value'    => 0,
            'parentid' => 0,
            'label'    => '-----'
        );
        array_unshift($c, $emptyData);
        */
        $r[ 'cat_options' ] = Arr::convertKeys( $c, array(
            'id'       => 'value',
            'treename' => 'label'
        ) );


        if ( $id || $r[ 'catid' ] )
        {
            $r[ 'cat_options' ][ 'selected' ] = $r[ 'catid' ];
        }

        if ( $id )
        {
            $this->addLastEdit( $id, trim( $r[ 'title' ] ), trans( 'Seite' ) );
        }

        if ( !$id && $this->input( 'catid' ) > 0 )
        {
            $r[ 'cat_options' ][ 'selected' ] = (int)$this->input( 'catid' );
        }

        $pagetype = (int)$r[ 'pagetype' ];
        if ( IS_AJAX && $this->input( 'changepagetype' ) )
        {
            $pagetype = (int)$this->input( 'changepagetype' );
        }
        /**
         * Custom Fields
         */
        $fields       = $this->model->getCustomFields( $pagetype );
        $field_values = $this->model->getCustomFieldData( $r[ 'id' ], $pagetype );


        foreach ( $fields as $idx => &$field )
        {
            $class_name = 'Field_' . ucfirst( $field[ 'fieldtype' ] ) . 'Field';

            $field[ 'value' ] = ( isset( $field_values[ $field[ 'fieldname' ] ][ 'value' ] ) ?
                $field_values[ $field[ 'fieldname' ] ][ 'value' ] : '' );

            if ( $field[ 'fieldtype' ] == 'tags' && $field[ 'value' ] != '' )
            {
                $field[ 'tags' ] = $this->Tags->getContentTags( $field[ 'value' ] );
            }

            $options = ( trim( $field[ 'options' ] ) ? unserialize( $field[ 'options' ] ) : array() );


            $field = array_merge( $options, $field );

            $fieldDefinition = call_user_func_array( array(
                $class_name,
                'getFieldDefinition'
            ), array(
                $field
            ) );

            $fieldDefinition[ 'value' ] = $field[ 'value' ];
            $fieldDefinition[ 'id' ]    = $field[ 'fieldname' ];
            $fieldDefinition[ 'name' ]  = $field[ 'fieldname' ];
            $field[ 'field' ]           = call_user_func_array( array(
                $class_name,
                '_renderField'
            ), array(
                $fieldDefinition
            ) );
        }


        $r[ 'customfields' ] = $fields;


        if ( $this->input( 'changepagetype' ) )
        {
            $dat = $this->Template->process( 'pages/edit', $r, false, 'customfields' );

            echo Library::json( array('success' => true, 'customfields' => $dat) );
            exit;
        }


        $types                   = $this->model->getPagetypes();
        $r[ 'pagetype_options' ] = array();

        foreach ( $types as $rs )
        {
            $r[ 'pagetype_options' ][ ] = array(
                'label' => $rs[ 'title' ],
                'value' => $rs[ 'id' ]
            );
        }

        if ( $r[ 'pagetype' ] )
        {
            $r[ 'pagetype_options' ][ 'selected' ] = (int)$r[ 'pagetype' ];
        }

        $r[ 'addFileSelector' ] = true;

        // add draft button
        $this->setDraftButton( true );
        Library::addNavi( trans( 'Übersicht Statischer Seiten' ) );
        Library::addNavi( ( !$isNew ? sprintf( trans( 'Statische Seite `%s` bearbeiten' ), $r[ 'title' ] ) :
            trans( 'Statische Seite erstellen' ) ) );

        $this->Template->process( 'pages/edit', $r, true );
    }

    /**
     *
     */
    protected function save()
    {

        $post = $this->_post();
        $id = (int)$post[ 'pageid' ];


        $fulltext = $post[ 'content' ];
        $fulltext = str_replace( '<br>', '<br/>', $fulltext );


        if ( trim( (string)$post[ 'title' ] ) == '' )
        {
            Library::sendJson( false, trans( "Seiten Titel fehlt!" ) );
        }

        if ( !trim( $fulltext ) )
        {
            Library::sendJson( false, trans( "Seite enthält keinen Inhalt!1" ) );
        }


        /**
         * Validate custom fields
         */
        $fields = $this->model->getCustomFields( $post[ 'pagetype' ] );

        foreach ( $fields as $field )
        {
            $options = ( trim( $field[ 'options' ] ) ? unserialize( $field[ 'options' ] ) : array() );

            $value = $post[ $field[ 'fieldname' ] ];
            if ( $options[ 'controls' ] && empty( $value ) )
            {
                Library::sendJson( false, sprintf( trans( 'Angaben für das Feld `%s` sind erforderlich' ), ( !empty( $options[ 'grouplabel' ] ) ? $options[ 'grouplabel' ] : $options[ 'label' ] ) ) );
            }
        }


        $alias  = $this->input( 'alias' );
        $suffix = $this->input( 'suffix' );

        $aliasRegistry = new AliasRegistry();
        $aliasExists   = $aliasRegistry->aliasExists( array(
            'alias'         => $alias,
            'suffix'        => $suffix,
            'documenttitle' => $this->input( 'title' )
        ) );

        if ( $aliasExists && ( $id && $aliasRegistry->getErrorAliasID() != $id ) )
        {
            Library::log( sprintf( 'Alias Builder has found many errors! The Alias `%s` already exists!', $aliasRegistry->getAlias() ), 'warn' );
            Library::sendJson( false, sprintf( trans( 'Der Alias "%s" existiert bereits!' ), $aliasRegistry->getAlias() ) );
        }




        $this->load( 'Versioning' );

        $post[ 'alias' ] = $aliasRegistry->getAlias();
        // $post['suffix'] = $aliasRegistry->getSuffix();

        $aliasRegistry->freeMem();
        $aliasRegistry = null;


        if ( $id )
        {
            $original = $this->model->getVersioningRecord( $id );


            if ( !$original[ 'record' ]['id'] )
            {
                Error::raise( "Page not exist!" );
            }



            // @todo add custom fields to version
            #$customfieldvalues = $this->model->getCustomFieldData( $id, $original[ 'record' ][ 'pagetype' ] );

            $this->Event->trigger( 'onBeforeSave.page', $id, $post );

            $this->model->saveTranslation( $id, $post );

            $isDraft = $this->saveContentDraft( $id, trim( (string)$this->input( 'title' ) ), trans( 'Seite' ) );

            /**
             * Create new version
             */
            $this->model->createVersion( $original );// @todo add custom fields to version

            // get Versions for select field
            $versSelect = $this->Versioning->buildAjaxVersions( $id, 'pages' );


            $this->Event->trigger( 'onAfterSave.page', $id, $post );

            // $model->unlockDocument( $id, 'page', 'index' );
            // Remove Cache
            Cache::delete( 'page-' . $id, 'data/pages/' . CONTENT_TRANS );


            // send pings
            if (!$isDraft && $post['documentmeta']['published'] == 1) {
                $ps = new PingService();
                $ps->setData('page/'. $post['alias'] . ($post['suffix'] ? '.'.$post['suffix'] : '.'.Settings::get( 'mod_rewrite_suffix', 'html' )) , $this->input('title'))->genericPing();
            }




            User::updateLastpost('page/' . $post['alias'] . ($post['suffix'] ? '.'.$post['suffix'] : '.'.Settings::get( 'mod_rewrite_suffix', 'html' )), $this->input('title'));


            Library::log( "Updating Page " . $this->input( 'title' ) . " (ID:{$id})." );


            echo Library::json( array(
                'success'          => true,
                'msg'              => trans( 'Seite wurde erfolgreich geändert' ),
                'versionselection' => $versSelect
            ) );
            exit;
        }
        else
        {
            $this->Event->trigger( 'onBeforeSave.page', 0, $post );


            $id = $this->model->saveTranslation( 0, $post );
            $isDraft = $this->saveContentDraft( $id, trim( (string)$this->input( 'title' ) ), trans( 'Seite' ) );

            /**
             * Create new version
             */
            $original = $this->model->getVersioningRecord( $id );
            $this->model->createVersion( $original );

            // get Versions for select field
            $versSelect = $this->Versioning->buildAjaxVersions( $id, 'pages' );

            $this->Event->trigger( 'onAfterSave.page', $id, $post );

            // Remove Cache
            Cache::delete( 'page-' . $id, 'data/pages/' . CONTENT_TRANS );

            // send pings
            if (!$isDraft && $post['documentmeta']['published'] == 1) {
                $ps = new PingService();
                $ps->setData('page/'. $post['alias'] . ($post['suffix'] ? '.'.$post['suffix'] : '.'.Settings::get( 'mod_rewrite_suffix', 'html' )) , $this->input('title'))->genericPing();
            }

            Library::log( "Create the Page " . HTTP::input( 'title' ) . " (ID:{$id})." );

            // update user posts
            User::subPostCounter();
            User::updateLastpost('page/' . $post['alias'] . ($post['suffix'] ? '.'.$post['suffix'] : '.'.Settings::get( 'mod_rewrite_suffix', 'html' )), $this->input('title'));


            echo Library::json( array(
                'success'          => true,
                'msg'              => trans( 'Seite wurde erfolgreich geändert' ),
                'versionselection' => $versSelect
            ) );
            exit;
        }

        exit;
    }

}

?>