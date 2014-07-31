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
 * @file         Fields.php
 */
class Page_Action_Fields extends Controller_Abstract
{

    public function execute()
    {

        if ( $this->isFrontend() )
        {
            return;
        }

        $pagetypeid = (int)$this->input( 'pagetypeid' );

        $pagetype = $this->model->getPagetypeById( $pagetypeid );


        $this->load( 'Grid' );
        $this->Grid->setUiqid( 'pages_fields' );
        $this->Grid->initGrid( 'pages_fields', 'fieldid', 'ordering', 'asc' )
            ->setGridDataUrl( 'admin.php?adm=page&action=fields&pagetypeid=' . $pagetypeid . '&' )
            ->enableColumnVisibleToggle();

        $this->Grid->addFilter( array(
            array(
                'name'  => 'q',
                'type'  => 'input',
                'value' => '',
                'label' => 'Suchen nach',
                'show'  => true,
                'parms' => array(
                    'size' => '40'
                )
            ),
            array(
                'submitbtn' => true
            ),
        ) );

        $this->Grid->addHeader( array(
            // sql feld						 header	 	sortieren		standart
            array(
                "field"        => "fieldname",
                "content"      => 'Feldname',
                'width'        => '20%',
                "sort"         => "name",
                "default"      => true,
                'forcevisible' => true,
                'islabel'      => true,
            ),
            array(
                "field"   => "fieldtype",
                "content" => 'Feld Typ',
                'width'   => '12%',
                "sort"    => "fieldtype",
                "default" => true
            ),
            array(
                "field"   => "description",
                "content" => 'Feld Beschreibung',
                "sort"    => "description",
                "default" => true
            ),
            array(
                "field"   => "options",
                "content" => 'Optionen',
                'width'   => '10%',
                "default" => true,
                'align'   => 'tc'
            ),
        ) );


        $_result = $this->model->getFieldsGridData( $pagetypeid );


        $im = BACKEND_IMAGE_PATH;

        $e = trans( 'Bearbeiten' );
        $d = trans( 'Löschen' );
        foreach ( $_result[ 'result' ] as $rs )
        {
            $_e = sprintf( $e, $rs[ 'fieldname' ] );
            $_d = sprintf( $d, $rs[ 'fieldname' ] );


            $rs[ 'options' ] = <<<EOF
		<a class="doTab" href="admin.php?adm=page&action=editfield&fieldid={$rs['fieldid']}&pagetypeid={$pagetypeid}"><img src="{$im}edit.png" border="0" alt="{$_e}" title="{$_e}" /></a> &nbsp;
		<a class="delconfirm ajax" href="admin.php?adm=page&action=deletefield&fieldid={$rs['fieldid']}&pagetypeid={$pagetypeid}"><img src="{$im}delete.png" border="0" alt="{$_d}" title="{$_d}" /></a>
EOF;


            $rs[ 'fieldtype' ] = $rs[ 'fieldtype' ];

            $row = $this->Grid->addRow( $rs );
            $row->addFieldData( "fieldname", $rs[ "fieldname" ] );
            $row->addFieldData( "fieldtype", $rs[ 'fieldtype' ] );
            $row->addFieldData( "description", $rs[ 'description' ] );
            $row->addFieldData( "options", $rs[ 'options' ] );
        }

        Library::addNavi( trans( 'Seitentypen Übersicht' ) );
        Library::addNavi( sprintf( trans( 'Formularfelder des Seitentypes `%s` ' ), $pagetype[ 'title' ] ) );


        $griddata = $this->Grid->renderData( $_result[ 'total' ] );
        $data     = array();


        if ( $this->input( 'getGriddata' ) )
        {
            $data[ 'success' ] = true;
            $data[ 'total' ]   = $_result[ 'total' ];
            # $data['sort'] = $GLOBALS['sort'];
            # $data['orderby'] = $GLOBALS['orderby'];
            $data[ 'datarows' ] = $griddata[ 'rows' ];
            unset( $_result, $this->Grid );

            Ajax::Send( true, $data );
            exit;
        }


        $this->Template->process( 'pages/fields', array(), true );

        exit;
    }

}
