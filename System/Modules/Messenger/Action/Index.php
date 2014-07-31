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
 * @package      Messenger
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Messenger_Action_Index extends Controller_Abstract
{

    public function execute()
    {

        if ( $this->input( 'updatecounter' ) )
        {
            $tmp     = array();
            $folders = $this->model->getUserFolderCount();

            foreach ( $folders as $idx => $counted )
            {
                if ( $idx <= 3 && $idx != 'all' )
                {
                    unset( $folders[ $idx ] );
                    $tmp[ $idx ][ 'counter' ] = $counted;
                }
            }

            $allfolders = $this->model->getFolders();
            foreach ( $allfolders as $idx => $rs )
            {
                $tmp[ $rs[ 'id' ] ][ 'counter' ] = $allfolders[ $rs[ 'id' ] ];
            }
            $r[ 'totalmessages' ] = $folders[ 'totalmessages' ];
            $r[ 'folders' ]       = $tmp;
            $r[ 'success' ]       = true;

            echo Library::json( $r );
            exit;
        }


        $this->load( 'Grid' );
        $this->Grid
            ->initGrid( 'messages', 'id', 'date', 'desc' )
            ->setGridDataUrl( 'admin.php?adm=messenger' )
            ->enableForceSelectable()
            ->addGridEvent( 'selectionChecker', 'function (sel) {checkMessageSelection(sel);}' )
            ->addGridEvent( 'onAfterSearchToggle', 'function () {messiAfterSearchToggle();}' )
            ->addGridEvent( 'onAfterLoad', 'function (data, obj) {messiAfterLoad(data, obj);}' );


        $this->Grid->addActions( array() );
        $this->Grid->addFilter( array(
            array(
                'name'  => 'q',
                'type'  => 'input',
                'value' => '',
                'label' => trans( 'Suchen nach' ),
                'show'  => true,
                'parms' => array(
                    'size' => '40'
                )
            ),
            array(
                'separator' => true
            ),
            array(
                'submitbtn' => true
            ),
        ) );

        $this->Grid->addHeader( array(

            array(
                "field"        => "status",
                "content"      => '&nbsp;',
                "default"      => true,
                'width'        => '4%',
                'nowrap'       => true,
                'align'        => 'tc',
                'forcevisible' => true,
                'fixedwidth'   => true
            ),
            array(
                "field"        => "title",
                "content"      => trans( 'Betreff' ),
                "sort"         => "title",
                'width'        => 'auto',
                "default"      => true,
                'forcevisible' => true,
                'islabel'      => true
            ),
            array(
                "field"   => "fromuser",
                "content" => trans( 'Von' ),
                "sort"    => "fromuser",
                'width'   => '15%',
                "default" => true
            ),
            array(
                "field"   => "date",
                "content" => trans( 'Erhalten am' ),
                "sort"    => "date",
                "default" => true,
                'width'   => '15%',
                'nowrap'  => true,
                'align'   => 'tl'
            ),
        ) );


        $perpage = $this->getPerpage();
        if ( !$perpage )
        {
            $perpage = 20;
        }

        $page = $this->getCurrentPage();
        $page = !$page ? 1 : $page;


        $r = array();
        if ( $this->input( 'q' ) != '' )
        {
            $rx[ 'q' ]     = $this->input( 'q' );
            $rx[ 'qtype' ] = $this->input( 'qtype' );
        }

        switch ( $this->input( 'qfield' ) )
        {
            case 'title':
            default:
                $rx[ 'qfield' ] = 'title';
                break;
            case 'message':
                $rx[ 'qfield' ] = 'message';
                break;
        }

        $rx[ 'page' ]   = $page;
        $rx[ 'sort' ]   = $this->input( 'sort' );
        $rx[ 'order' ]  = $this->input( 'order' );
        $rx[ 'limit' ]  = $perpage;
        $rx[ 'userid' ] = User::getUserId();
        $rx[ 'folder' ] = 1;
        if ( intval($this->input( 'folder' )) )
        {
            $rx[ 'folder' ] = intval( $this->input( 'folder' ) );
        }

        // Get messages
        $messages = $this->model->getMessages( $rx );

        $dataresults = $messages[ 'total' ];


        // Build Message Grid Table
        foreach ( $messages[ 'result' ] as $idx => $rs )
        {
            $icon = 'new.png';

            // wurde schon gelesen
            if ( $rs[ 'readtime' ] )
            {
                $icon = 'spacer.gif';
            }

            $readed = ' <img src="' . BACKEND_IMAGE_PATH . $icon . '" width="16" height="16" title="' . ( $rs[ 'readtime' ] ?
                    '' : trans( 'Neue Nachricht' ) ) . '"/> ';


            // Priorität
            $icon = 'spacer.gif';
            if ( $rs[ 'important' ] )
            {
                $icon = 'critical.png';
            }
            $important      = ' <img src="' . BACKEND_IMAGE_PATH . $icon . '" width="16" height="16" title="' . ( !$rs[ 'important' ] ?
                    '' : trans( 'Wichtig' ) ) . '"/> ';
            $rs[ 'status' ] = $readed . $important;


            $row = $this->Grid->addRow( $rs );

            $row->addFieldData( "title", $rs[ "title" ] );
            $row->addFieldData( "fromuser", $rs[ 'username' ] );
            $row->addFieldData( "date", Locales::formatDateTime( $rs[ 'sendtime' ] ) );
            $row->addFieldData( "status", $rs[ "status" ] );
        }


        $rx[ 'folders' ] = $this->model->getUserFolderCount();

        foreach ( $rx[ 'folders' ] as $idx => $counted )
        {
            if ( $idx <= 3 && $idx != 'all' )
            {
                unset( $r[ 'folders' ][ $idx ] );
                $r[ 'foldercount_' . $idx ] = $counted;
            }
        }


        $r[ 'folders' ] = $this->model->getFolders();
        foreach ( $r[ 'folders' ] as $idx => $rs )
        {
            $r[ 'folders' ][ $idx ][ 'counter' ] = $rx[ 'folders' ][ $rs[ 'id' ] ];
        }
        $r[ 'totalmessages' ] = $rx[ 'folders' ][ 'totalmessages' ];


        $griddata = $this->Grid->renderData( $messages[ 'total' ] );

        if ( HTTP::input( 'getGriddata' ) )
        {
            $data[ 'success' ]       = true;
            $data[ 'total' ]         = $messages[ 'total' ];
            $data[ 'totalmessages' ] = $r[ 'totalmessages' ];
            $data[ 'datarows' ]      = $griddata[ 'rows' ];
            $data[ 'folder' ]        = $rx[ 'folder' ];

            Ajax::Send( true, $data );
            exit;
        }

        Library::addNavi( trans( 'Messenger' ) );

        // $griddata[ 'sort' ]         = $GLOBALS[ 'sort' ];
        // $griddata[ 'orderby' ]      = $GLOBALS[ 'orderby' ];
        // $griddata[ 'total' ]        = $messages[ 'total' ];
        // $griddata[ 'searchitems' ]  = json_encode($griddata[ 'searchitems' ]);
        // $griddata[ 'colModel' ]     = json_encode($griddata[ 'colModel' ]);
        // $griddata[ 'gridActions' ]  = json_encode($griddata[ 'gridActions' ]);
        // $griddata[ 'activeFilter' ] = json_encode($griddata[ 'activeFilter' ]);

        // $r[ 'grid' ]   = $griddata;
        $r[ 'folder' ] = $rx[ 'folder' ];

        $this->Template->addScript( BACKEND_JS_URL . 'dcms.messenger' );
        $this->Template->process( 'messenger/index', $r, true );
        exit;
    }

}

?>