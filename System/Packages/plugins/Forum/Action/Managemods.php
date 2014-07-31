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
 * @file        Managemods.php
 */
class Addon_Forum_Action_Managemods extends Addon_Forum_Helper_Base
{

    public function execute()
    {
        if ( $this->isFrontend() )
        {
            return;
        }

        $this->initCache();

        $forumid = intval( HTTP::input( 'id' ) );

        $data[ 'forum' ] = (isset( $this->forum_by_id[ $forumid ] ) ? $this->forum_by_id[ $forumid ] : false );

        if ( !$data[ 'forum' ] )
        {
            Library::sendJson( false, trans( 'Das Forum existiert nicht.' ) );
        }






        $this->load( 'Grid' );
        $this->Grid->initGrid( 'board_moderators', 'modid', 'username', 'asc' );
        $this->Grid->addFilter( array(
            array(
                'name'  => 'q',
                'type'  => 'input',
                'value' => '',
                'label' => trans( 'Suchen nach' ),
                'show'  => true,
                'parms' => array(
                    'size' => '40' ) ),
            array(
                'name'   => 'cat_id',
                'type'   => 'select',
                'select' => $cats,
                'label'  => trans( 'Kategorie' ),
                'show'   => false ),
            array(
                'name'   => 'state',
                'type'   => 'select',
                'select' => $states,
                'label'  => trans( 'Status' ),
                'show'   => false ),
            array(
                'name'  => 'untrans',
                'type'  => 'checkbox',
                'value' => '1',
                'label' => trans( 'nicht Übersetzte' ),
                'show'  => true )
                )
        );

        $this->Grid->addHeader(
                array(
                    array(
                        "field"   => "modid",
                        "content" => trans( 'ID' ),
                        'width'   => '5%',
                        "sort"    => "modid",
                        "default" => true,
                        'type'    => 'num' ),
                    // sql feld						 header	 	sortieren		standart
                    array(
                        "field"   => "username",
                        "content" => trans( 'Benutzer' ),
                        "sort"    => "username",
                        "default" => true,
                        'type'    => 'alpha label' ),
                    array(
                        "field"   => "grouptitle",
                        "content" => trans( 'Benutzergrippe' ),
                        "sort"    => "grouptitle",
                        'width'   => '20%',
                        "default" => true,
                        'type'    => 'alpha label' ),
                    array(
                        "field"   => "published",
                        "content" => trans( 'Aktiv' ),
                        "sort"    => "published",
                        'width'   => '10%',
                        "default" => true,
                        'align'   => 'tc' ),
                    array(
                        "field"   => "options",
                        "content" => trans( 'Optionen' ),
                        'width'   => '10%',
                        "default" => true,
                        'align'   => 'tc' ),
                )
        );

        $this->Grid->addActions( array(
            'publish'     => trans( 'Aktivieren' ),
            "unpublish"   => trans( 'Deaktivieren' ),
            "delete_news" => array(
                'label' => trans( 'Löschen' ),
                'msg'   => trans( 'Ausgewählte Moderatoren werden gelöscht. Wollen Sie fortsetzen?' ) )
        ) );


        $_result = $this->model->getModeratorsGridQuery( $forumid );

        foreach ( $_result[ 'result' ] as $rs )
        {
            $published = $this->getGridState(
                    ($rs[ 'draft' ] ? DRAFT_MODE : $rs[ 'published' ] ), $rs[ 'modid' ], $rs[ 'publishon' ], $rs[ 'publishoff' ], 'admin.php?adm=plugin&plugin=forum&action=publishmod&forumid=' . $forumid . '&id='
            );

            $_e = htmlspecialchars( sprintf( $e, $rs[ "title" ] ) );
            $edit = $this->linkIcon( "adm=plugin&plugin=forum&action=editmod&id={$rs[ 'modid' ]}&forumid=" . $forumid, 'edit', $_e );
            $delete = $this->linkIcon( "adm=plugin&plugin=forum&action=deletemod&id={$rs[ 'modid' ]}&forumid=" . $forumid, 'delete' );
            $rs[ 'options' ] = $edit . ' ' . $delete;

            $row = $this->Grid->addRow( $rs );
            $row->addFieldData( "id", $rs[ "modid" ] );
            $row->addFieldData( "username", $rs[ "username" ] );
            $row->addFieldData( "grouptitle", $rs[ "grouptitle" ] );
            $row->addFieldData( "published", $published );
            $row->addFieldData( "options", $rs[ 'options' ] );
        }

        $griddata = $this->Grid->renderData();

        if ( $this->input( 'getGriddata' ) )
        {

            $data[ 'success' ] = true;
            $data[ 'total' ] = $_result[ 'total' ];
            # $data['sort'] = $GLOBALS['sort'];
            # $data['orderby'] = $GLOBALS['orderby'];
            $data[ 'datarows' ] = $griddata[ 'rows' ];
            unset( $_result, $this->Grid );

            Ajax::Send( true, $data );
            exit;
        }


        $data[ 'sort' ] = $GLOBALS[ 'sort' ];
        $data[ 'orderby' ] = $GLOBALS[ 'orderby' ];
        $data[ 'total' ] = $_result[ 'total' ];
        $data[ 'datarows' ] = Json::encode( $griddata[ 'rows' ] );
        $data[ 'searchitems' ] = Json::encode( $griddata[ 'searchitems' ] );
        $data[ 'colModel' ] = Json::encode( $griddata[ 'colModel' ] );
        $data[ 'gridActions' ] = Json::encode( $griddata[ 'gridActions' ] );
        $data[ 'activeFilter' ] = Json::encode( $griddata[ 'activeFilter' ] );
        $data[ 'datarows' ] = Json::encode( $griddata[ 'rows' ] );

        Library::addNavi( trans( 'Foren' ) );
        Library::addNavi( sprintf( trans( 'Moderatoren im Forum `%s` bearbeiten' ), $data[ 'forum' ][ 'title' ] ) );


        $this->Template->process( 'forum/forum_moderators', array(
            'grid'  => $data,
            'forum' => $data[ 'forum' ] ), true );
        exit;
    }

}
