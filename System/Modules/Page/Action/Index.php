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
 * @file         Index.php
 */
class Page_Action_Index extends Controller_Abstract
{

    protected $_BreadcrumbCache = null;

    protected $allCats = array();

    public function execute()
    {

        if ( $this->getApplication()->getMode() === Application::BACKEND_MODE )
        {
            $this->_processBackend();
        }
        else
        {
            $this->_processFrontend();
        }
    }

    private function _processBackend()
    {

        $model = Model::getModelInstance( 'page' );
        $cats  = $model->getCats();


        $catid = (int)HTTP::input( 'catid' );

        $arr = array(
            ''               => '---------------------------',
            'online'         => trans( 'Online Seiten' ),
            'offline'        => trans( 'Offline Seiten' ),
            'archived'       => trans( 'Archivierte Seiten' ),
            'draft'          => trans( 'in Bearbeitung' ),
            'online_offline' => trans( 'Online &amp; Offline Seiten' ),
        );


        $states = array();
        foreach ( $arr as $k => $v )
        {
            $states[ $k ] = $v;
        }

        $helper = new Page_Helper_Base();

        $types            = $this->model->getPagetypes();
        $pagetypes        = array();
        $pagetypes[ '0' ] = '--';
        foreach ( $types as $r )
        {
            $pagetypes[ $r[ 'id' ] ] = $r[ 'title' ];
        }


        $this->load( 'Grid' );
        $this->Grid->initGrid( 'pages', 'id', 'date', 'desc' );
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
                'name'   => 'pagetype',
                'type'   => 'select',
                'select' => $pagetypes,
                'label'  => trans( 'Seitentyp' ),
                'show'   => false
            ),
            array(
                'name'   => 'catid',
                'type'   => 'select',
                'select' => $cats,
                'label'  => trans( 'Kategorie' ),
                'show'   => false
            ),
            array(
                'name'   => 'state',
                'type'   => 'select',
                'select' => $states,
                'label'  => trans( 'Status' ),
                'show'   => false
            ),
            array(
                'name'  => 'untrans',
                'type'  => 'checkbox',
                'value' => 1,
                'label' => trans( 'nicht Übersetzte' ),
                'show'  => true
            )
        ) );

        $move = '<select name="moveto" id="move">';
        foreach ( $cats as $cid => $title )
        {
            $move .= '<option value="' . $cid . '">' . $title . '</option>';
        }
        $move .= '</select>';

        $this->Grid->addActions( array(
            'publish'   => trans( 'Seiten Veröffentlichen' ),
            "unpublish" => trans( 'Seiten nicht Veröffentlichen' ),
            "archive"   => trans( 'Seiten Archivieren' ),
            "unarchive" => trans( 'Seiten aus Archiv holen' ),
            "delete"    => array(
                'label' => trans( 'Seiten Löschen' ),
                'msg'   => trans( 'Ausgewählte Seiten werden gelöscht. Wollen Sie fortsetzen?' )
            ),
            "move"      => array(
                'label'     => trans( 'Verschieben nach' ),
                'subaction' => $move
            )
        ) );


        $this->Grid->addHeader( array(
            array(
                "field"   => "id",
                'width'   => '3%',
                "content" => trans( 'ID' ),
                "sort"    => "id",
                "default" => true,
                'type'    => 'num'
            ), // sql feld						 header	 	sortieren		standart
            array(
                'islabel' => true,
                "field"   => "title",
                "content" => trans( 'Titel' ),
                "sort"    => "title",
                "default" => true,
                'type'    => 'alpha label'
            ),
            array(
                "field"   => "cattitle",
                "content" => trans( 'Kategorie' ),
                'width'   => '15%',
                "sort"    => "cat",
                "default" => true,
                'nowrap'  => true,
                'type'    => 'alpha'
            ),
            array(
                "field"   => "pagetypetitle",
                "content" => trans( 'Seitentyp' ),
                'width'   => '15%',
                "sort"    => "pagetypetitle",
                "default" => true,
                'nowrap'  => true,
                'type'    => 'alpha'
            ),
            array(
                "field"   => "created",
                "content" => trans( 'Datum' ),
                "sort"    => "date",
                "default" => true,
                'width'   => '10%',
                'nowrap'  => true,
                'type'    => 'date'
            ),
            array(
                "field"   => "created_user",
                "content" => trans( 'Autor' ),
                'width'   => '10%',
                "sort"    => "createdby",
                "default" => false,
                'nowrap'  => true,
                'type'    => 'alpha'
            ),
            array(
                "field"   => "modifed",
                "content" => trans( 'Bearbeitet am' ),
                "sort"    => "moddate",
                "default" => false,
                'width'   => '10%',
                'nowrap'  => true,
                'type'    => 'date'
            ),
            array(
                "field"   => "modifed_user",
                "content" => trans( 'Bearbeiter' ),
                'width'   => '10%',
                "sort"    => "modifedby",
                "default" => false,
                'nowrap'  => true,
                'type'    => 'alpha'
            ),
            array(
                "field"   => "hits",
                "content" => trans( 'Hits' ),
                "sort"    => "hits",
                'width'   => '5%',
                "default" => true,
                'align'   => 'tc',
                'type'    => 'num'
            ),
            array(
                "field"   => "published",
                "content" => trans( 'Aktiv' ),
                "sort"    => "published",
                'width'   => '5%',
                "default" => true,
                'align'   => 'tc'
            ),
            array(
                "field"   => "comments",
                "content" => trans( 'Kommentare' ),
                "sort"    => "comments",
                'width'   => '7%',
                "default" => false,
                'align'   => 'tc',
                'type'    => 'num'
            ),
            array(
                "field"   => "options",
                "content" => trans( 'Optionen' ),
                'width'   => '7%',
                "default" => true,
                'align'   => 'tc'
            ),
        ) );

        $_result = $model->getGridData( $catid );


        $limit = $this->getPerpage();
        $pages = ceil( $_result[ 'total' ] / $limit );


        $e = trans( '`%s` barbeiten' );
        foreach ( $_result[ 'result' ] as $rs )
        {


            if ( $rs[ 'modifed' ] > $rs[ 'created' ] )
            {
                $rs[ 'modifed' ] = date( 'd.m.Y, H:i', $rs[ 'modifed' ] );
            }
            else
            {
                $rs[ 'modifed' ] = '';
            }

            $rs[ 'created' ] = date( 'd.m.Y, H:i', $rs[ 'created' ] );

            $_e              = htmlspecialchars( sprintf( $e, $rs[ "title" ] ) );
            $edit            = $this->linkIcon( "adm=page&action=edit&id={$rs['id']}&edit=1", 'edit', $_e );
            $delete          = $this->linkIcon( "adm=page&action=delete&id={$rs['id']}", 'delete' );
            $rs[ 'options' ] = $edit . ' ' . $delete;

            $published = $this->getGridState( ( $rs[ 'draft' ] ? DRAFT_MODE :
                $rs[ 'published' ] ), $rs[ 'id' ], $rs[ 'publishon' ], $rs[ 'publishoff' ], 'admin.php?adm=page&action=publish&id=' );

            $fcss = ( $rs[ 'lang' ] != CONTENT_TRANS ? 'wtrans ' . $rs[ 'lang' ] : 'trans ' . $rs[ 'lang' ] );

            $row = $this->Grid->addRow( $rs );
            $row->addFieldData( "id", $rs[ "id" ] );


            $space = '';
            /*
            if ($rs[ "parentids" ])
            {
                $arr = Library::unempty( explode(',', $rs[ "parentids" ]) );
                $space = '#ts_' . count($arr) .'#';
            }
*/
            $row->addFieldData( "title", $space . ( $rs[ 'locked' ] == 1 ? '<span class="fa fa-pause"></span>' : '' ) . $rs[ "title" ], $fcss );

            $rs[ "cattitle" ] = ( !$rs[ "cattitle" ] ? '-' : $rs[ "cattitle" ] );
            $row->addFieldData( "cattitle", $rs[ "cattitle" ] );
            $rs[ 'pagetypetitle' ] = ( $rs[ 'pagetypetitle' ] === 'default' ? trans( 'Standart' ) : $rs[ 'pagetypetitle' ] );

            $row->addFieldData( "pagetypetitle", $rs[ 'pagetypetitle' ] );
            $row->addFieldData( "created", $rs[ 'created' ] );
            $row->addFieldData( "modifed", $rs[ 'modifed' ] );
            $row->addFieldData( "created_user", $rs[ "created_user" ] );
            $row->addFieldData( "modifed_user", $rs[ "modifed_user" ] );
            $row->addFieldData( "hits", $rs[ "hits" ] );
            $row->addFieldData( "comments", $rs[ "comments" ] );
            $row->addFieldData( "published", $published );
            $row->addFieldData( "options", $rs[ 'options' ] );


        }

        $griddata = array();
        $griddata = $this->Grid->renderData( $_result[ 'total' ] );
        $data     = array();
        if ( $this->input( 'getGriddata' ) )
        {

            $data[ 'success' ]  = true;
            $data[ 'total' ]    = $_result[ 'total' ];
            $data[ 'datarows' ] = $griddata[ 'rows' ];

            Ajax::Send( true, $data );
            exit;
        }


        Library::addNavi( trans( 'Übersicht Statischer Seiten' ) );
        $this->Template->process( 'pages/index', array(
            'grid' => $this->Grid->getJsonData( $_result[ 'total' ] )
        ), true );

        exit;
    }

    private function _processFrontend()
    {

        $this->Document
            ->validateModel('pages', true)
            ->loadConfig()
            ->getMetaInstance()
            ->setMetadataType(true);

/*

        $this->model->setTable( 'pages' );


        $modelTables = $this->model->getConfig( 'tables' );
        if ( !isset( $modelTables[ 'pages' ] ) )
        {
            throw new BaseException( 'The Pages modul has no Model configuration! ' . print_r( $modelTables, true ) );
        }

        $this->Document->setTableConfiguration( 'pages', $modelTables[ 'pages' ] );
        $this->Document->getMetaInstance()->setMetadataType( true );

*/



        $request = trim( str_ireplace( '/' . CONTROLLER . '/', '', REQUEST ), '/' );
        $req     = explode( '/', $request );

        $doRate = false;
        if ( $req[ 0 ] == 'rate' )
        {
            $doRate = true;
        }


        if ( $doRate && ( !(int)$req[ 1 ] || !(int)$req[ 2 ] ) )
        {
            $this->Page->send404( trans( 'Die von Ihnen aufgerufene Seite existiert nicht.' ) );
        }

        if ( $doRate )
        {
            $id = (int)$req[ 1 ];

            $this->Document->setDocumentID( $id );

            $this->load( 'ContentLock' );
            if ( $this->ContentLock->isLock( $id, 'page' ) )
            {
                $this->Document->offline();
            }

            HTTP::setinput( 'rate', (int)$req[ 2 ] );
        }


        if ( $this->Input->input( 'tag' ) )
        {
            $_result         = $this->model->findItemsByTag( $this->Input->input( 'tag' ) );
            $data            = array();
            $data[ 'pages' ] = array();

            foreach ( $_result[ 'result' ] as $r )
            {

                $r[ 'content' ] = preg_replace( '#src=(["\']).*(pages/.+)\1#i', 'src=$1$2$1', $r[ 'content' ] );
                $r[ 'url' ]     = 'page/' . Url::makeRw( $r[ 'alias' ], $r[ 'suffix' ], $r[ 'title' ] );

                if ( ( $img = Content::extractFirstImage( $r[ 'content' ] ) ) )
                {
                    $r[ 'image' ]   = $img[ 'attributes' ];
                    $r[ 'content' ] = str_replace( $img[ 'full_tag' ], '', $r[ 'content' ] );

                    // process for metatags "og"
                    $this->setSocialNetworkImageData( $data, $r[ 'image' ] );
                }

                $r[ 'content' ] = $this->model->prepareContent( $r[ 'content' ], $r[ 'created' ], 450, 'maximum' );


                $data[ 'pages' ][ ] = $r;
            }


            if ( $_result[ 'total' ] > 0 )
            {
                $page  = (int)$this->input( 'page' ) ? (int)$this->input( 'page' ) : 1;
                $pages = ceil( $_result[ 'total' ] / 20 );

                $this->load( 'Paging' );
                $url = $this->Paging->generate( array() );
                $this->Paging->setPaging( $url, $page, $pages );


                if ( $this->Input->input( 'tag' ) )
                {
                    $modeltags = Model::getModelInstance( 'Tags' );
                    $modeltags->updateTagHits( $this->Input->input( 'tag' ) );
                }
            }

            $this->db->free();

            $this->Breadcrumb->add( sprintf( trans( 'Seiten mit dem Tag `%s`' ), $this->Input->input( 'tag' ) ), '' );

            $this->Template->process( 'pages/index', $data, true );
        }
        else
        {
            $id           = $this->Document->getDocumentID();
            $documentName = $this->getDocumentName( null );

            if ( (int)$this->input( 'id' ) )
            {
                $id = (int)$this->input( 'id' );
            }

            /*

              if ( !$id && $this->Router->getDocumentName( false ) )
              {
              $this->Page->error( 404, trans( 'Die von Ihnen aufgerufene Seite existiert.' ) );
              }
             */
            $rs = null;


            // find page
            if ( $id && !$documentName )
            {
                $rs = $this->model->findItemByID( $id );
            }
            else if ( !$id && $documentName )
            {
                $rs = $this->model->findItemByAlias( $documentName );
            }
            else
            {
                // error?
            }

            // if not page foud then find the categorie
            if ( !$rs[ 'id' ] && $documentName )
            {
                $rs = $this->model->findIndexPageByCatAlias( $documentName );
            }


            $this->allCats = $this->model->getCategories();

            $this->db->free();
            $this->Input->set( 'id', $rs[ 'id' ] );


            $pagetype = $this->model->getPagetypeById( $rs[ 'pagetype' ] );


            $bodyCss = false;
            if ( trim( $rs[ 'cssclass' ] ) != '' )
            {
                $bodyCss = true;
                $this->Template->addBodyClass( $rs[ 'cssclass' ] );
            }

            if ( !$bodyCss && trim( $rs[ 'cat_cssclass' ] ) != '' )
            {
                $bodyCss = true;
                $this->Template->addBodyClass( $rs[ 'cat_cssclass' ] );
            }


            /**
             * Check Usergroup Permissions
             *
             */
            $access = explode( ',', $rs[ 'usergroups' ] );
            if ( $rs[ 'usergroups' ] != '' && !in_array( User::getGroupId(), $access ) && !in_array( 0, $access ) )
            {
                $this->Page->sendAccessError( trans( 'Sie haben keine rechte diese Seite zu sehn. Bitte loggen Sie sich ein oder Registrieren Sie sich.' ) );
            }


            /**
             * Document is in a categorie and is this categorie locked?
             */
            $useBreadcrumbData = null;

            // Build Breadcrumb
            if ( $rs[ 'parentid' ] )
            {

                $breadcrumbData = $this->model->getParentPageBreadcrumbs( $rs[ 'parentids' ] );

                $tmpBreadcrumbData = array();
                $hasIndexpage      = false;
                foreach ( $breadcrumbData as $idxs => $breadcrumb )
                {
                    if ( $hasIndexpage )
                    {
                        $tmpBreadcrumbData[ ] = $breadcrumb;
                    }

                    if ( $breadcrumb[ 'isindexpage' ] && !$hasIndexpage )
                    {
                        $tmpBreadcrumbData[ ] = $breadcrumb;
                        $hasIndexpage         = true;
                    }
                }

                $useBreadcrumbData = ( $hasIndexpage ? $tmpBreadcrumbData : $breadcrumbData );

                if ( !$bodyCss )
                {
                    foreach ( $breadcrumbData as $breadcrumb )
                    {
                        if ( trim( $breadcrumb[ 'cssclass' ] ) )
                        {
                            $this->Template->addBodyClass( $breadcrumb[ 'cssclass' ] );
                            break;
                        }
                    }
                }


                $useBreadcrumbData = array_reverse( $useBreadcrumbData );

                if ( $rs[ 'catid' ] )
                {
                    if ( !isset( $GLOBALS[ 'IS_FRONTPAGE' ] ) )
                    {
                        $lastTitle   = '';
                        $breadcrumbs = $this->getCatBreadcrumb( $rs[ 'catid' ] );
                        $foundIndex  = false;
                        $isIndexPage = false;
                        foreach ( $breadcrumbs as $idx => $r )
                        {
                            $url       = '';
                            $lastTitle = '';
                            foreach ( $useBreadcrumbData as $breadcrumb )
                            {
                                if ( $breadcrumb[ 'isindexpage' ] && $lastTitle != ( trim( $breadcrumb[ 'pagetitle' ] ) ? $breadcrumb[ 'pagetitle' ] : $breadcrumb[ 'title' ] ) )
                                {
                                    $foundIndex  = true;
                                    $isIndexPage = true;

                                    $lastTitle = !empty( $breadcrumb[ 'pagetitle' ] ) ? $breadcrumb[ 'pagetitle' ] : $breadcrumb[ 'title' ];

                                    $this->Breadcrumb->add( ( trim( $breadcrumb[ 'pagetitle' ] ) ?
                                            $breadcrumb[ 'pagetitle' ] :
                                            $breadcrumb[ 'title' ] ) /* . ' IDX:' . $breadcrumb[ 'isindexpage' ] */, 'page/' . Url::makeRw( $breadcrumb[ 'alias' ], $breadcrumb[ 'suffix' ], $breadcrumb[ 'title' ] ) );
                                }
                            }


                            if ( $foundIndex )
                            {
                                continue;
                            }

                            if ( $foundIndex )
                            {
                                $lastTitle = trim( $breadcrumb[ 'pagetitle' ] ) ? $breadcrumb[ 'pagetitle' ] : $breadcrumb[ 'title' ];
                                $url       = 'page/' . Url::makeRw( $rs[ 'alias' ], $rs[ 'suffix' ], $rs[ 'title' ] );
                                #	$this->Breadcrumb->add((!empty($r[ 'pagetitle' ]) ? $r[ 'pagetitle' ] :$r[ 'title' ]), $url);
                            }

                        }

                        if ( !$foundIndex && $lastTitle != $r[ 'title' ] )
                        {
                            $this->Breadcrumb->add( ( trim( $rs[ 'pagetitle' ] ) ? $rs[ 'pagetitle' ] : $rs[ 'title' ] ) );
                        }
                        else if ( $foundIndex && $lastTitle != $r[ 'title' ] )
                        {
                            $this->Breadcrumb->add( ( trim( $rs[ 'pagetitle' ] ) ? $rs[ 'pagetitle' ] : $rs[ 'title' ] ) );
                        }
                    }
                }
                else
                {
                    foreach ( $useBreadcrumbData as $breadcrumb )
                    {
                        $this->Breadcrumb->add( ( trim( $breadcrumb[ 'pagetitle' ] ) ? $breadcrumb[ 'pagetitle' ] :
                                $breadcrumb[ 'title' ] ), 'page/' . Url::makeRw( $breadcrumb[ 'alias' ], $breadcrumb[ 'suffix' ], $breadcrumb[ 'title' ] ) );
                    }
                }
            }
            elseif ( !$rs[ 'parentid' ] && $rs[ 'catid' ] )
            {
                foreach ( $useBreadcrumbData as $breadcrumb )
                {

                }

                $this->Breadcrumb->add( ( trim( $rs[ 'pagetitle' ] ) ? $rs[ 'pagetitle' ] : $rs[ 'title' ] ) );
            }
            else
            {

                $this->Breadcrumb->add( ( trim( $rs[ 'pagetitle' ] ) ? $rs[ 'pagetitle' ] : $rs[ 'title' ] ) );
            }

            $this->load( 'ContentLock' );


            // check if is categorie offline
            if ( $rs[ 'catid' ] )
            {
                $cat = $this->findParentCatIfLocked( $rs[ 'catid' ], $allCats );

                if ( $cat !== false && $cat[ 'locked' ] )
                {
                    if ( !empty( $pagetype[ 'pagetype' ] ) )
                    {
                        $this->Document->set( 'contentlayout', $pagetype[ 'pagelayout' ] );
                    }

                    $this->Document->offline();
                }
            }


            // if page and indexpage not foud then error
            if ( !$rs[ 'id' ] )
            {
                $this->Page->send404( trans( 'Die von Ihnen aufgerufene Seite existiert nicht.' ) );
            }
            else
            {

                // check if is page offline
                if ( $rs[ 'id' ] && $rs[ 'locked' ] )
                {
                    if ( !empty( $pagetype[ 'pagetype' ] ) )
                    {
                        $this->Document->set( 'contentlayout', $pagetype[ 'pagelayout' ] );
                    }
                    $this->Document->offline();
                }

                // check time controlled page
                if ( $rs[ 'published' ] == 2 )
                {
                    if ( $rs[ 'publishon' ] > 0 && $rs[ 'publishon' ] > TIMESTAMP )
                    {
                        $this->Page->error( 404, trans( 'Die von Ihnen aufgerufene Seite existiert nicht.' ) );
                    }

                    if ( $rs[ 'publishoff' ] > 0 && $rs[ 'publishoff' ] < TIMESTAMP )
                    {
                        $this->Page->error( 404, trans( 'Die von Ihnen aufgerufene Seite existiert nicht.' ) );
                    }
                }
            }


            if ( $useBreadcrumbData !== null )
            {
                $tmp = array_reverse( $useBreadcrumbData );

                foreach ( $tmp as $r )
                {
                    if ( !$r[ 'parentid' ] && $r[ 'catid' ] )
                    {
                        if ( $this->ContentLock->isLock( $r[ 'catid' ], 'page', 'cat' ) )
                        {
                            if ( !empty( $pagetype[ 'pagetype' ] ) )
                            {
                                $this->Document->set( 'contentlayout', $pagetype[ 'pagelayout' ] );
                            }
                            $this->Document->offline();
                        }
                    }
                }
            }


            /**
             * Document is locked?
             */
            if ( $this->ContentLock->isLock( $rs[ 'id' ], 'page', 'index' ) )
            {
                if ( !empty( $pagetype[ 'pagetype' ] ) )
                {
                    $this->Document->set( 'contentlayout', $pagetype[ 'pagelayout' ] );
                }

                $this->Document->offline();
            }


            $cat = array();
            if ( $rs[ 'catid' ] )
            {
                // @todo add Brute-Force protection
                // test current cat and parent cats for password protection
                $cat = $this->getProtection( $rs[ 'catid' ], $allCats );

                if ( !empty( $cat[ 'password' ] ) )
                {
                    $post = $this->_post( 'unprotect' );

                    if ( is_string( $post ) )
                    {

                        $this->load( 'Crypt' );
                        $pass = $this->_post( 'unprotect' ) != '' ? $this->_post( 'unprotect' ) : TIMESTAMP;
                        if ( $pass == $this->Crypt->decrypt( $cat[ 'password' ] ) )
                        {
                            Session::save( 'page-success-' . $rs[ 'catid' ], true );

                            if ( IS_AJAX )
                            {
                                Library::sendJson( true );
                            }
                        }
                        else
                        {
                            Session::delete( 'page-success-' . $rs[ 'catid' ] );

                            if ( IS_AJAX )
                            {
                                Library::sendJson( false, trans( 'Leider ist dieses Passwort falsch! Bitte erneut versuchen.' ) );
                            }
                        }
                    }

                    // send page protection code
                    if ( !Session::get( 'page-success-' . $rs[ 'catid' ], false ) )
                    {
                        $this->Document->disableSiteCaching();
                        $this->Document->protect();
                    }
                }
            }


            // @todo add page password protection add Brute-Force protection




            /**
             * Rate this Page
             */
            if ( HTTP::input( 'rate' ) || $doRate && IS_AJAX )
            {

                $this->load( 'ContentLock' );
                if ( $this->ContentLock->isLock( $rs[ 'id' ], 'page', 'index' ) )
                {
                    $this->Document->offline();
                }

                if ( Session::get( 'pages-rate-' . $rs[ 'id' ] ) )
                {
                    Library::sendJson( false, trans( 'Sorry aber Sie können diese Seite nicht mehr bewerten, da Sie Ihre Wertung schon abgegeben haben.' ) );
                }

                $rate = (int)HTTP::input( 'rate' );
                if ( !$rate )
                {
                    Library::sendJson( false, 'Your Rating is Empty.' );
                }

                $newrating = ( $rs[ 'rating' ] * $rs[ 'votes' ] + $rate ) / ( $rs[ 'votes' ] + 1 );

                $this->db->query( 'UPDATE %tp%pages SET rating = ?, votes = votes + 1 WHERE id = ?', $newrating, $rs[ 'id' ] );

                Session::save( 'pages-rate-' . $rs[ 'id' ], 1 );
                $newrating = sprintf( "%01.2f", $newrating );

                echo Library::json( array(
                    'success' => true,
                    'msg'     => trans( 'Danke für Ihre Bewertung' ),
                    'rating'  => $newrating,
                    'votes'   => $rs[ 'votes' ] + 1
                ) );
                exit();
            }

            if ( $id > 0 )
            {
                $rs[ 'commentscounter' ] = $rs[ 'comments' ];
            }


            if ( $rs[ 'created_by' ] )
            {
                $rs[ 'author' ] = User::getUserById( $rs[ 'created_by' ] );
                $this->db->free();
            }

            if ( $rs[ 'author' ] )
            {
                BBCode::allowBBCodes( 'biobbcodes' );
                $rs[ 'author' ][ 'userphoto' ] = User::getUserPhoto( $rs[ 'author' ] );
                $rs[ 'author' ][ 'bio' ]       = BBCode::toXHTML( $rs[ 'author' ][ 'usertext' ] );
            }

            // Page Rating
            $rating                 = sprintf( "%01.2f", $rs[ 'rating' ] );
            $image_name             = Library::makeRatingImg( $rating );
            $rs[ 'ratingimg_name' ] = $image_name;
            $rs[ 'ratingsum' ]      = $rating;
            $rs[ 'rating' ]         = round( $rating );


            // Comment settings
            $rs[ 'can_comment' ] = $rs[ 'show_comments' ] = ( isset( $rs[ 'cancomment' ] ) && $rs[ 'cancomment' ] ? true : false );
            if ( $rs[ 'can_comment' ] && !User::hasPerm( 'page/cancommentpages' ) )
            {
                User::setPerm( 'page/cancommentpages', false );
                $rs[ 'can_comment' ] = $rs[ 'cancomment' ] = true;
            }


            /**
             * Init Tags
             */
            if ( $rs[ 'tags' ] )
            {
                $this->load( 'Tags' );
                $this->Tags->setContentTable( 'pages_trans' );
                $rs[ 'tags' ] = $this->Tags->getContentTags( $rs[ 'tags' ] );
            }
            else
            {
                $rs[ 'tags' ] = array();
            }
            //	$rs[ 'content' ] = preg_replace('#src=(["\']).*(pages/.+)\1#i', 'src=$1$2$1', $rs[ 'content' ]);

            $rs[ 'content' ]   = Content::tinyMCECoreTags( $rs[ 'content' ] );

            $rs[ 'content' ]   = Content::parseContent( $rs[ 'content' ], "page", false, true );
            $rs[ 'siteindex' ] = Content::getSiteIndexes();
            $rs[ 'paging' ]    = Content::getContentPageing();

            // Set Content of this page
            $this->Document->setData( $rs );
            $this->Document->setMetaAuthor( $rs[ 'created_user' ] );
            $this->Document->setClickAnalyse( $rs[ 'clickanalyse' ] );


            // Set page Caching
            if ( $rs[ 'cacheable' ] || $this->SideCache->enabled )
            {
                $this->Document->enableSiteCaching( $rs[ 'cachetime' ] );
                $this->Document->setSiteCachingGroups( $rs[ 'cachegroups' ] );
            }
            else
            {
                $this->Document->disableSiteCaching();
            }

            $this->db->free();


            $pagetype = $this->model->getPagetypeById( $rs[ 'pagetype' ] );
            if ( $rs[ 'pagetype' ] )
            {
                CustomField::set( $this->model->getCustomFieldData( $rs[ 'id' ], $rs[ 'pagetype' ] ) );
            }

            $rs[ 'pagetitle' ] = trim( $rs[ 'pagetitle' ] );
            //          $this->Breadcrumb->add( (!empty( $rs[ 'pagetitle' ] ) ? $rs[ 'pagetitle' ] : $rs[ 'title' ] ), '' );
            // Set last modify date for http header
            $lastModified = ( $rs[ 'modifed' ] > $rs[ 'created' ] ? $rs[ 'modifed' ] : $rs[ 'created' ] );
            $this->Document->setLastModified( $lastModified );
            $this->Document->setDocumentID( $rs[ 'id' ] );

            if ( empty( $pagetype[ 'pagetype' ] ) )
            {
                $_layout = 'default_container';
            }
            else
            {
                $_layout = $pagetype[ 'pagetype' ];

                if ( $pagetype[ 'pagelayout' ] )
                {
                    $this->Document->set( 'contentlayout', $pagetype[ 'pagelayout' ] );
                }
            }


            $this->load( 'ContentLock' );
            if ( $this->ContentLock->isLock( $rs[ 'id' ], 'page', 'index' ) )
            {
                $this->Document->offline();
            }

            if ( trim($rs[ 'cssclass' ]) )
            {
                $this->Template->addBodyClass($rs[ 'cssclass' ]);
            }

            $this->model->updateHits( $rs[ 'id' ] );
            $this->Template->process( 'pages/' . $_layout, array(
                    'page' => $rs
                ), true );
        }
    }

    private function findCategorieByAlias($alias)
    {

        foreach ( $this->allCats as &$r )
        {
            if ( !$r[ 'alias' ] )
            {
                $r[ 'alias' ] = Library::suggest( $r[ 'title' ] );
            }

            if ( $alias === $r[ 'alias' ] )
            {
                return $r;
            }
        }

        return false;
    }

    private function getCatBreadcrumb($catid)
    {

        if ( $this->_BreadcrumbCache === null )
        {
            $this->_BreadcrumbCache = array();
            $result                 = $this->model->getCategories();
            foreach ( $result as $r )
            {
                $this->_BreadcrumbCache[ $r[ 'catid' ] ] = $r;
            }
        }

        $parentlist = array();
        if ( $catid && isset( $this->_BreadcrumbCache[ $catid ] ) )
        {
            $parentlist[ ] = $catid;
        }

        $parentlist1 = $this->getAllParentIds( $catid, $parentlist );
        $parentlist1 = array_reverse( $parentlist1 );

        unset( $parentlist );

        $navarray = array();
        foreach ( $parentlist1 AS $_id )
        {
            $navarray[ ] = $this->_BreadcrumbCache[ $_id ];
        }

        return $navarray;
    }

    /**
     * returns an array of ALL parent ids for a given id($id)
     *
     * @param integer $id
     * @param array $idarray
     * @return array
     */
    private function getAllParentIds($id, $idarray)
    {

        if ( !is_array( $idarray ) )
        {
            $idarray = array();
        }
        if ( !(int)$id || !isset( $this->_BreadcrumbCache[ (int)$id ] ) )
        {
            return $idarray;
        }

        $rs = $this->_BreadcrumbCache[ (int)$id ];
        if ( !isset( $rs[ 'id' ] ) || empty( $rs[ 'parentid' ] ) )
        {
            return $idarray;
        }

        $idarray[ ] = $rs[ 'parentid' ];
        $idarray    = $this->getAllParentIds( $rs[ 'parentid' ], $idarray );

        return $idarray;
    }

    /**
     *
     * @param int $catid
     * @param array $allCats
     */
    private function getProtection($catid, &$allCats)
    {

        foreach ( $allCats as $r )
        {
            if ( $r[ 'catid' ] == $catid )
            {

                if ( $r[ 'parentid' ] && !$r[ 'password' ] )
                {
                    return $this->getProtection( $r[ 'parentid' ], $allCats );
                }

                if ( $r[ 'password' ] )
                {
                    return $r;
                }

                return false;
            }
        }

        return false;
    }

    private function findParentCatIfLocked($catid, &$allCats)
    {

        foreach ( $allCats as $r )
        {
            if ( $r[ 'catid' ] == $catid )
            {
                if ( $r[ 'parentid' ] && !$r[ 'locked' ] )
                {
                    return $this->getProtection( $r[ 'parentid' ], $allCats );
                }

                if ( $r[ 'locked' ] )
                {
                    return $r;
                }

                return false;
            }
        }

        return false;
    }

}

?>