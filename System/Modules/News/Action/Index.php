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
 * @package      News
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class News_Action_Index extends Controller_Abstract
{

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

    protected function _processBackend()
    {

        $model = Model::getModelInstance( 'news' );

        $cat_id     = (int)HTTP::input( 'cat_id' );
        $max_levels = 20;
        $cats       = $model->getCats();


        $arr = array(
            ''               => '----',
            'online'         => trans( 'Online News' ),
            'offline'        => trans( 'Offline News' ),
            'archived'       => trans( 'Archivierte News' ),
            'draft'          => trans( 'in Bearbeitung' ),
            'online_offline' => trans( 'Online &amp; Offline News' ),
        );


        $states = array();
        foreach ( $arr as $k => $v )
        {
            $states[ $k ] = $v;
        }


        $this->load( 'Grid' );
        $this->Grid
            ->initGrid( 'news', 'id', 'date', 'desc' )
            ->setGridDataUrl( 'admin.php?adm=news' )
            ->addGridEvent( 'onDoubleClick', 'function (row) {
            openTab({url: "admin.php?adm=news&action=edit_news&id=" + row.attr("id").replace("data-",""),obj: null,label: row.find("[rel=title]").text() })
        }' )
            ->enableColumnVisibleToggle();


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
                'name'   => 'cat_id',
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
                'value' => '1',
                'label' => trans( 'nicht Übersetzte' ),
                'show'  => true
            )
        ) );

        $this->Grid->addHeader( array(
            array(
                "field"        => "id",
                "content"      => trans( 'ID' ),
                'width'        => '3%',
                "sort"         => "id",
                "default"      => true,
                'type'         => 'num',
                'forcevisible' => true
            ),
            array(
                'islabel'      => true,
                "field"        => "title",
                "content"      => trans( 'Titel' ),
                "sort"         => "title",
                "default"      => true,
                'type'         => 'alpha label',
                'forcevisible' => true
            ),
            array(
                "field"   => "cat_title",
                "content" => trans( 'Kategorie' ),
                'width'   => '15%',
                "sort"    => "cat",
                "default" => true,
                'nowrap'  => true,
                'type'    => 'alpha'
            ),
            array(
                "field"   => "created",
                "content" => trans( 'Datum' ),
                'width'   => '12%',
                "sort"    => "date",
                "default" => true,
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
                'width'   => '12%',
                "sort"    => "modifed",
                "default" => false,
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
                'width'   => '5%',
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
            )
        ) );


        $move = '<select name="moveto" id="move">';
        foreach ( $cats as $cid => $title )
        {
            $move .= '<option value="' . $cid . '">' . $title . '</option>';
        }
        $move .= '</select>';

        $this->Grid->addActions( array(
            'publish'     => trans( 'Veröffentlichen' ),
            "unpublish"   => trans( 'nicht Veröffentlichen' ),
            "archive"     => trans( 'Archivieren' ),
            "unarchive"   => trans( 'aus Archiv holen' ),
            "delete_news" => array(
                'label' => trans( 'Löschen' ),
                'msg'   => trans( 'Ausgewählte Nachrichten werden gelöscht. Wollen Sie fortsetzen?' )
            ),
            "move"        => array(
                'label'     => trans( 'Verschieben nach' ),
                'subaction' => $move
            )
        ) );

        $_result = $model->getGridQuery( $cat_id );


        $e = trans( '`%s` barbeiten' );
        foreach ( $_result[ 'result' ] as $rs )
        {


            if ( $rs[ 'created' ] < (int)$rs[ 'modifed' ] )
            {
                $rs[ 'modifed' ] = date( 'd.m.Y, H:i', (int)$rs[ 'modifed' ] );
                #$lastModified    = ($rs[ 'modifed' ] > $lastModified ? $rs[ 'modifed' ] : $lastModified);
            }
            else
            {
                $rs[ 'modifed' ] = '';
                #$lastModified    = ($rs[ 'created' ] > $lastModified ? $rs[ 'created' ] : $lastModified);
            }
            $rs[ 'created' ] = date( 'd.m.Y, H:i', $rs[ 'created' ] );
            $_e              = htmlspecialchars( sprintf( $e, $rs[ "title" ] ) );
            $edit            = $this->linkIcon( "adm=news&action=edit_news&id={$rs['id']}&edit=1", 'edit', $_e );
            $delete          = $this->linkIcon( "adm=news&action=delete_news&id={$rs['id']}", 'delete' );
            $rs[ 'options' ] = $edit . ' ' . $delete;

            $published = $this->getGridState( ( $rs[ 'draft' ] ? DRAFT_MODE :
                $rs[ 'published' ] ), $rs[ 'id' ], $rs[ 'publishon' ], $rs[ 'publishoff' ], 'admin.php?adm=news&action=publish&id=' );

            $fcss = ( $rs[ 'lang' ] != CONTENT_TRANS ? 'wtrans ' . $rs[ 'lang' ] : 'trans ' . $rs[ 'lang' ] );

            $row = $this->Grid->addRow( $rs );
            $row->addFieldData( "id", $rs[ "id" ] );
            $row->addFieldData( "title", ( $rs[ 'locked' ] == 1 ? '<span class="fa fa-pause"></span>' : '' ) . $rs[ "title" ], $fcss );
            $row->addFieldData( "created", $rs[ 'created' ] );
            $row->addFieldData( "modifed", $rs[ 'modifed' ] );
            $row->addFieldData( "created_user", $rs[ "created_user" ] );
            $row->addFieldData( "modifed_user", $rs[ "modifed_user" ] );
            $row->addFieldData( "hits", $rs[ "hits" ] );
            $row->addFieldData( "cat_title", $rs[ "cat_title" ] );
            $row->addFieldData( "comments", $rs[ "comments" ] );
            $row->addFieldData( "published", $published );
            $row->addFieldData( "options", $rs[ 'options' ] );
        }


        $griddata = $this->Grid->renderData( $_result[ 'total' ] );
        $data     = array();
        if ( $this->input( 'getGriddata' ) )
        {

            $data[ 'success' ]  = true;
            $data[ 'total' ]    = $_result[ 'total' ];
            $data[ 'datarows' ] = $griddata[ 'rows' ];
            unset( $_result, $this->Grid, $griddata );

            Ajax::Send( true, $data );
            exit;
        }

        Library::addNavi( trans( 'News Übersicht' ) );
        $this->Template->process( 'news/news_list', array(), true );

        exit;
    }

    protected function _processFrontend()
    {

        if ( $this->input( 'getFeed' ) )
        {
            $this->_getRssFeed();
            exit;
        }

        /**
         * Add Rss Header
         */
        $this->Document->addRssHeader( 'atom', $this->getModulLabel(), 'news/index/' . $this->getDocumentName( 'news' ) );
        $this->Document->addRssHeader( 'rss', $this->getModulLabel(), 'news/index/' . $this->getDocumentName( 'news' ) );

        #   $map = new UrlMapper();
        #   $map->regenerate(PAGEID, CONTROLLER);
        #   $map->getSiteMap(PAGEID, CONTROLLER);


        $id = $this->Document->getDocumentID() ? $this->Document->getDocumentID() : (int)$this->input( 'catid' );

        $currentCat  = null;
        $_currentcat = array();

        if ( $id )
        {
            HTTP::setinput( 'catid', $id );
            $this->Document->setDocumentID( $id );

            $_currentcat = $this->model->getCatById( $id );
        }

        if ( !$_currentcat[ 'id' ] )
        {
            if ( defined('DOCUMENT_NAME') && DOCUMENT_NAME )
            {
                $_currentcat = $this->model->getCatByAlias( DOCUMENT_NAME );
                if ( !$_currentcat[ 'id' ] )
                {
                    $this->Page->error( 404, trans( 'Die von Ihnen aufgerufene Seite existiert.' ) );
                }
            }
        }

        if ( $id && !$_currentcat[ 'id' ] )
        {
            $this->Page->error( 404, trans( 'Die von Ihnen aufgerufene Seite existiert.' ) );
        }


        $this->load( 'ContentLock' );

        if ( $_currentcat[ 'id' ] && $this->ContentLock->isLock( $_currentcat[ 'id' ], 'news', 'index' ) )
        {
            $this->Document->offline();
        }

        if ( $_currentcat[ 'id' ] )
        {
            /**
             *
             */
            $access = explode( ',', $_currentcat[ 'access' ] );
            if ( $_currentcat[ 'access' ] != '' && !in_array( User::getGroupId(), $access ) && !in_array( 0, $access ) )
            {
                $this->Page->sendAccessError( trans( 'Sie haben keine rechte diese Seite zu sehn. Bitte loggen Sie sich ein oder Registrieren Sie sich.' ) );
            }

            HTTP::setinput( 'catid', $_currentcat[ 'id' ] );
        }


        //$dat = Session::get('news-options');

        $pp    = (int)$this->input( 'perpage' ) > 0 ? (int)$this->input( 'perpage' ) : $this->getModulOption( 'perpage', 20 );
        $limit = $pp;
        HTTP::setinput( 'perpage', $limit );

        $default = array(
            'snewsday'   => array(
                1,
                Controller::INPUT_DAY
            ),
            'snewsmonth' => array(
                1,
                Controller::INPUT_MONTH
            ),
            'snewsyear'  => array(
                2000,
                Controller::INPUT_YEAR
            ),
            'enewsday'   => array(
                date( 'd' ),
                Controller::INPUT_DAY
            ),
            'enewsmonth' => array(
                date( 'm' ),
                Controller::INPUT_MONTH
            ),
            'enewsyear'  => array(
                date( 'Y' ),
                Controller::INPUT_YEAR
            ),
            'sort'       => array(
                'desc',
                Controller::INPUT_SORT
            ),
            'order'      => array(
                'date',
                Controller::INPUT_ORDER
            ),
            'q'          => array(
                '',
                Controller::INPUT_SEARCH
            ),
            'tag'        => array(
                $this->input( 'tag' ),
                Controller::INPUT_STRING
            ),
            'perpage'    => array(
                $limit,
                Controller::INPUT_INTEGER
            ),
            'catid'      => array(
                ( $currentCat[ 'id' ] > 0 ? $currentCat[ 'id' ] : 0 ),
                Controller::INPUT_INTEGER
            )
        );

        $_options = $this->getOptions( $default, $this->input(), 'news-options' );


        $model  = Model::getModelInstance( 'news' );
        $result = $model->getData( $_options );

        $data = array(
            'news' => array()
        );

        $catid        = ( $_currentcat[ 'id' ] > 0 ? $_currentcat[ 'id' ] : 0 );
        $lastModified = 0;


        foreach ( $result[ 'result' ] as $idx => $r )
        {
            if ( $r[ 'modifed' ] > 0 )
            {
                $lastModified = ( $r[ 'modifed' ] > $lastModified ? $r[ 'modifed' ] : $lastModified );
            }
            else
            {
                $lastModified = ( $r[ 'created' ] > $lastModified ? $r[ 'created' ] : $lastModified );
            }

            #$r[ 'text' ] = preg_replace( '#src=(["\']).*(pages/.+)\1#i', 'src=$1$2$1', $r[ 'text' ] );
            $r[ 'text' ] = str_replace( 'pages/0/', 'pages/' . PAGEID . '/', $r[ 'text' ] );
            $r[ 'text' ] = str_replace( 'pages/1/', 'pages/' . PAGEID . '/', $r[ 'text' ] );
            $r[ 'text' ] = str_replace( 'pages/beta/', 'pages/' . PAGEID . '/', $r[ 'text' ] );

            $r[ 'show_comments' ]      = ( $r[ 'cancomment' ] ? true : false );
            $r[ 'show_comments' ]      = ( $r[ 'can_comment' ] ? true : $r[ 'show_comments' ] );
            $r[ 'show_category_link' ] = 1;
            $r[ 'show_author' ]        = 1;

            $textCache    = Cache::get( 'newsText-' . $r[ 'id' ], 'data/news/' . CONTENT_TRANS . '/' );
            $r[ 'title' ] = Strings::cleanString( $r[ 'title' ] );

            if ( ( $img = Content::extractFirstImage( $r[ 'text' ] ) ) )
            {
                $r[ 'image' ] = $img[ 'attributes' ];
                $r[ 'text' ]  = str_replace( $img[ 'full_tag' ], '', $r[ 'text' ] );

                // process for metatags "og"
                $this->setSocialNetworkImageData( $data, $r[ 'image' ] );
            }

            if ( $textCache === null )
            {
                $r[ 'text' ] = $model->prepareNewsContent( $r[ 'text' ], $r[ 'created' ], 800, 'maximum' );

                Cache::write( 'newsText-' . $r[ 'id' ], $r[ 'text' ], 'data/news/' . CONTENT_TRANS . '/' );
            }
            else
            {
                $r[ 'text' ] = $textCache;
            }

            Cache::freeMem( 'newsText-' . $r[ 'id' ], 'data/news/' . CONTENT_TRANS . '/' );


            $rating                     = sprintf( "%01.2f", $r[ 'rating' ] );
            $image_name                 = Library::makeRatingImg( $rating );
            $r[ 'news_ratingimg_name' ] = $image_name;
            $r[ 'news_ratingsum' ]      = $rating;

            // prepare new url
            $r[ 'url' ] = $this->generateUrl( array(
                'action' => 'show',
                'id'     => $r[ 'id' ],
                'alias'  => $r[ 'alias' ],
                'suffix' => $r[ 'suffix' ]
            ), 'news/item/' );

            // prepare news cat url
            $r[ 'caturl' ] = $this->generateUrl( array(
                'action' => 'index',
                'catid'  => $r[ 'cat_id' ],
                'alias'  => $r[ 'catalias' ],
                'suffix' => $r[ 'catsuffix' ]
            ), 'news/categorie/' );

            $data[ 'news' ][ ] = $r;

            //
            if ( ( defined( 'DOCUMENT_NAME' ) && $r[ 'catalias' ] === DOCUMENT_NAME ) )
            {
                $cat_title = $r[ 'cat_title' ];
                $catid     = $r[ 'cat_id' ];
            }
        }




        if ( $result[ 'total' ] > 0 )
        {
            $page  = (int)$this->input( 'page' ) ? (int)$this->input( 'page' ) : 1;
            $pages = ceil( $result[ 'total' ] / $_options[ 'perpage' ] );

            $this->load( 'Paging' );
            $url = $this->Paging->generate( array(
                'catid' => $_options[ 'catid' ] ? $_options[ 'catid' ] : $catid
            ) );
            $this->Paging->setPaging( $url, $page, $pages );


            if ( !empty( $_options[ 'tag' ] ) )
            {
                $modeltags = Model::getModelInstance( 'Tags' );
                $modeltags->updateTagHits( $_options[ 'tag' ] );
            }
        }

        unset( $result );


        $newsCatCache = Cache::get( 'newsCategories-' . CONTENT_TRANS, 'data/news' );
        if ( !$newsCatCache )
        {
            $result = $model->getCategories();

            $data[ 'cats' ] = array();
            foreach ( $result as $r )
            {

                $r[ 'cat_title' ]   = $r[ 'title' ];
                $r[ 'description' ] = $r[ 'description' ];

                if ( $r[ 'teaserimage' ] != '' )
                {
                    $r[ 'teaserimage' ] = unserialize( $r[ 'teaserimage' ] );

                    if ( !$r[ 'teaserimage' ][ 'src' ] )
                    {
                        $r[ 'teaserimage' ] = null;
                    }
                    else
                    {
                        $r[ 'teaserimage' ][ 'src' ] = PAGE_URL_PATH . preg_replace( '/^\//', '', $r[ 'teaserimage' ][ 'src' ] );
                    }
                }

                $r[ 'caturl' ] = $this->generateUrl( array(
                    'id'     => isset( $r[ 'cat_id' ] ) ? $r[ 'cat_id' ] : 0,
                    'alias'  => $r[ 'alias' ],
                    'suffix' => $r[ 'suffix' ]
                ), '/news/category/' );

                if ( $catid > 0 && $catid == $r[ 'id' ] )
                {
                    /**
                     *
                     */
                    $access = explode( ',', $r[ 'access' ] );
                    if ( $r[ 'access' ] != '' && !in_array( User::getGroupId(), $access ) && !in_array( 0, $access ) )
                    {
                        $this->Page->sendAccessError( trans( 'Sie haben keine rechte diese Seite zu sehn. Bitte loggen Sie sich ein oder Registrieren Sie sich.' ) );
                    }


                    $_currentcat = $r;
                    $cat_title   = Strings::utf8_to_unicode( $r[ 'title' ] );
                }

                $data[ 'cats' ][ ] = $r;
            }
            unset( $result );
            Cache::write( 'newsCategories', $data[ 'cats' ], 'data/news' );
        }
        else
        {
            $data[ 'cats' ] = $newsCatCache;
            foreach ( $newsCatCache as $r )
            {
                if ( $catid > 0 && $catid == $r[ 'id' ] )
                {
                    /**
                     *
                     */
                    $access = explode( ',', $r[ 'access' ] );
                    if ( $r[ 'access' ] != '' && !in_array( User::getGroupId(), $access ) && !in_array( 0, $access ) )
                    {
                        $this->Page->sendAccessError( trans( 'Sie haben keine rechte diese Seite zu sehn. Bitte loggen Sie sich ein oder Registrieren Sie sich.' ) );
                    }


                    $_currentcat = $r;
                    $cat_title   = $r[ 'title' ];
                    break;
                }
            }
            unset( $newsCatCache );
        }
        Cache::freeMem( 'newsCategories-' . CONTENT_TRANS, 'data/news' );

        if ( is_array( $currentCat ) && !$cat_title )
        {
            $cat_title   = $currentCat[ 'title' ];
            $_currentcat = $currentCat;
        }

        if ( $catid > 0 && !$cat_title )
        {
            $this->Page->send404( gettext( 'Die von Ihnen aufgerufene Seite existiert leider nicht!' ) );
        }


        $data[ 'currentcat' ] = $_currentcat;

        if ( $_currentcat[ 'cat_title' ] )
        {
            $this->setSocialNetworkData( $data, $cat_title, $_currentcat[ 'description' ] );
        }

        if ( $catid || $id )
        {
            $breadcrumbs = $this->Breadcrumb->getNewsBreadcrumb( ( $id > 0 ? $id : $catid ) );
            $max         = count( $breadcrumbs );

            if ( !$this->Breadcrumb->getBreadcrumbs( ( $max ? true : false ) ) )
            {
                $this->Breadcrumb->add( $this->getModulLabel(), '/news' );
            }

            $x = 1;
            foreach ( $breadcrumbs as $rx )
            {
                $link = $this->generateUrl( array(
                    'id'     => isset( $rx[ 'cat_id' ] ) ? $rx[ 'cat_id' ] : 0,
                    'alias'  => $rx[ 'alias' ],
                    'suffix' => $rx[ 'suffix' ]
                ), '/news/category/' );

                $this->Breadcrumb->add( $rx[ 'title' ], ( $max > $x ? $link : '' ) );

                ++$x;
            }

            unset( $breadcrumbs );
        }
        else
        {
            if ( !$this->Breadcrumb->getBreadcrumbs( false ) && empty( $_options[ 'tag' ] ) )
            {
                $this->Breadcrumb->add( $this->getModulLabel(), '' );
            }
            elseif ( !$this->Breadcrumb->getBreadcrumbs( false ) && !empty( $_options[ 'tag' ] ) )
            {
                $this->Breadcrumb->add( $this->getModulLabel(), '/news' );
                $this->Breadcrumb->add( sprintf( trans( 'Nachrichten mit dem Tag `%s`' ), $_options[ 'tag' ] ), '' );
            }
        }

        // $this->Document->setData($data);
        // $this->Document->setLayout('newsarchive');
        $this->Document->disableSiteCaching();
        $this->Document->setLastModified( $lastModified );
        $this->Template->process( 'news/index', $data, true );
    }

    /**
     *
     */
    private function _getRssFeed()
    {

        $getFeed = $this->input( 'getFeed' );
        $rs      = $this->model->getData( array(
            'perpage' => 50
        ), true );

        $feed = new Feed( array(
            'atomName'    => CONTROLLER,
            'title'       => $this->getModulLabel(),
            'description' => '',
            'link'        => Settings::get( 'portalurl' ) . '/' . CONTROLLER,
            'published'   => TIMESTAMP
        ) );

        $cmsUrl = Settings::get( 'portalurl' );


        foreach ( $rs[ 'result' ] AS $idx => $r )
        {

            $r[ 'text' ]      = Strings::fixForXml( Strings::fixAmpsan( Strings::fixLatin( Strings::unhtmlSpecialchars( $r[ 'text' ], true ) ) ) );
            $r[ 'title' ]     = Strings::fixForXml( Strings::fixAmpsan( Strings::fixLatin( Strings::unhtmlSpecialchars( $r[ 'title' ], true ) ) ) );
            $r[ 'published' ] = $r[ 'created' ];


            $r[ 'text' ] = preg_replace( '/(\{content_image[\}]+\})/is', '', $r[ 'text' ] );
            $r[ 'text' ] = preg_replace( '#src\s*=\s*([\'"])' . preg_quote( $cmsUrl, '#' ) . '/#is', 'src=$1', $r[ 'text' ] );

            #  $r[ 'text' ] = Library::unmaskContent( $r[ 'text' ] );

            $imgs = Html::extractTags( $r[ 'text' ], 'img', true, true );
            if ( is_array( $imgs ) )
            {
                foreach ( $imgs as $img )
                {
                    if ( isset( $img[ 'attributes' ][ 'src' ] ) )
                    {

                        $img[ 'attributes' ][ 'src' ] = str_replace( $cmsUrl, '', $img[ 'attributes' ][ 'src' ] );

                        $src = str_replace( array(
                            'http://',
                            'https://'
                        ), '', $img[ 'attributes' ][ 'src' ] );

                        $u   = explode( '/', $src );
                        $loc = array_shift( $u );


                        if ( substr( $loc, 0, 4 ) !== 'http' )
                        {
                            array_unshift( $u, $loc );

                            $url                          = implode( '/', $u );
                            $img[ 'attributes' ][ 'src' ] = $cmsUrl . '/' . $url;
                        }
                        else
                        {
                            if ( $loc !== $cmsUrl )
                            {
                                array_unshift( $u, $loc );
                                $url                          = implode( '/', $u );
                                $img[ 'attributes' ][ 'src' ] = $url;
                            }
                        }


                        $i = Html::createTag( array(
                            'tagname'    => 'img',
                            'attributes' => $img[ 'attributes' ]
                        ) );

                        $r[ 'text' ] = str_replace( $img[ 'full_tag' ], $i, $r[ 'text' ] );

                        $i = null;
                    }
                }
            }

            #  $r[ 'text' ] = preg_replace( '#(https?://([^/]*)/)/(public|pages/)#siU', '$2', $r[ 'text' ] );
            #  $r[ 'text' ] = preg_replace( '#src\s*\n*=\s*\n*([\'"])pages/#s', 'src=$1' . $cmsUrl . '/pages/$1', $r[ 'text' ] );
            // $filename = ($r[ 'alias' ] ? $r[ 'alias' ] . ($r[ 'suffix' ] ? '.' . $r[ 'suffix' ] : '') : Library::suggest( $r[ 'title' ], 'alias' ));

            $r[ 'link' ]        = $this->generateUrl( $r, 'news/item/', true ); //Settings::get('portalurl') . '/news/' . $r['id'] . '/' . $filename;
            $r[ 'description' ] = Strings::TrimHtml( $r[ 'text' ], Settings::get( 'feed_description_length', 350 ), '<i>,<em>,<img>', '...' );
            $r[ 'content' ]     = str_replace( '<p></p>', '', Strings::TrimHtml( $r[ 'text' ], Settings::get( 'feed_content_length', 1000 ), '<i>,<em>,<img>,<strong>,<p>,<br>' ) );

            $feed->addItem( $r );
        }

        $output = 'Invalid RSS request!';

        if ( $getFeed === 'rss' )
        {
            $output = $feed->generateRss();
        }
        else if ( $getFeed === 'atom' )
        {
            $output = $feed->generateAtom();
        }

        $_output = $this->getController()->Output;


        $output = str_replace( $cmsUrl . '/' . $cmsUrl, $cmsUrl, $output );


        $output = Library::unmaskContent( $output );

        #    $output = preg_replace( '#' . Settings::get( 'portalurl' ) . '/(https?://([^/]*)/)/(public|pages)#U', '$1/$3', $output );
        #    $output = preg_replace( '#(https?://([^/]*)/)/(public|pages)#U', Settings::get( 'portalurl' ) . '/$3', $output );

        /*
          $_output->addHeader( 'Expires', 'Mon, 20 Jul 1995 05:00:00 GMT' );
          $_output->addHeader( 'Last-Modified', gmdate( "D, d M Y H:i:s" ) . " GMT" );
          $_output->addHeader( 'Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0' );
          $_output->addHeader( 'Pragma', 'no-cache' ); */
        $_output->appendOutput( $output )->setMode( Output::XML )->sendOutput();

        exit;
    }

}
