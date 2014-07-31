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
 * @file         Upload.php
 */
class News_Action_Upload extends Controller_Abstract
{
    protected $_uploadPath;

    public function _onUploaded($data, Upload $uploader)
    {
        if ( $data[ 'success' ] )
        {
            $str = $this->db->compile_db_insert_string( array(
                'title'        => '',
                'contentid'    => -1,
                'mime'         => $data[ 'filemime' ],
                'filepath'     => str_replace( PAGE_PATH, '', $data[ 'filepath' ] ),
                'published'    => 1,
                'userid'       => User::getUserId(),
                'created'      => TIMESTAMP,
                'originalname' => $data[ 'filename' ],
                'isserverfile' => 0,
                'ordering'     => 0
            ) );

            $this->db->query( "INSERT INTO %tp%contentimages ({$str['FIELD_NAMES']}) VALUES({$str['FIELD_VALUES']})" );


            $status = array(
                'success'  => true,
                "status"   => trans( 'Speichern erfolgreich' ),
                'thumbid'  => $this->db->insert_id(),
                'fileurl'  => str_replace( ROOT_PATH, '', $data[ 'filepath' ] ),
                'size'     => $data[ 'filesize' ],
                'type'     => $data[ 'fileextension' ],
                'isimage'  => $data[ 'isimage' ],
                'filename' => $data[ 'filename' ],
            );

            $post     = $this->_post();

            if ( $post[ 'usechain' ] && $data[ 'filepath' ] )
            {
                $cache_path = PAGE_PATH . '.cache/thumbnails/img/';
                if ( !is_dir( $cache_path ) )
                {
                    Library::makeDirectory( $cache_path );
                }

                $srci = Library::formatPath( $data[ 'filepath' ] );
                $ext  = strtolower( Library::getExtension( $srci ) );

                if ( $ext === 'jpg' || $ext === 'jpeg' || $ext === 'png' || $ext === 'gif' )
                {
                    $img      = ImageTools::create( $cache_path );
                    $saveType = ( $ext === 'jpg' || $ext === 'gif' ? 'jpeg' : $ext );

                    $_chain = array(
                        0 => array(
                            0 => 'resize',
                            1 => array(
                                'width'       => ( isset( $post[ 'chainwidth' ] ) && (int)$post[ 'chainwidth' ] ?
                                        (int)$post[ 'chainwidth' ] : 120 ),
                                'height'      => ( isset( $post[ 'chainheight' ] ) && (int)$post[ 'chainheight' ] ?
                                        (int)$post[ 'chainheight' ] : 90 ),
                                'keep_aspect' => ( isset( $post[ 'chainaspect' ] ) ? $post[ 'chainaspect' ] : true ),
                                'shrink_only' => ( isset( $post[ 'chainshrink' ] ) ? $post[ 'chainshrink' ] : false )
                            )
                        )
                    );


                    $_data = $img->process( array(
                        'source' => Library::formatPath( $srci ),
                        'output' => $saveType,
                        'chain'  => $_chain
                    ) );

                    if ( $_data[ 'path' ] )
                    {
                        $status[ 'width' ]  = (int)$_data[ 'width' ];
                        $status[ 'height' ] = (int)$_data[ 'height' ];
                        $status[ 'path' ]   = $_data[ 'path' ];
                    }
                }
            }




            echo Library::json( $status );

            exit;
        }
        else
        {
            Library::sendJson( false, (isset($data[ 'error' ]) ? $data[ 'error' ] : $uploader->getError()) );
        }
    }

    public function execute()
    {

        if ( $this->isFrontend() )
        {
            return;
        }


        demoadm();


        if ( $this->_post( 'remove' ) )
        {
            $id  = (int)$this->_post( 'remove' );
            $img = $this->model->getContentGalleryImage( $id );
            $this->model->removeContentGalleryImage( $id );


            if ( !$img[ 'isserverfile' ] && file_exists( ROOT_PATH . $img[ 'filepath' ] ) )
            {
                unlink( ROOT_PATH . $img[ 'filepath' ] );
            }

            Library::sendJson( true, sprintf( trans( 'Das Bild ´%s´ wurde gelöscht' ), $img[ 'originalname' ] ) );
        }


        if ( $this->_post( 'update' ) )
        {
            $id  = (int)$this->_post( 'update' );
            $img = $this->model->getContentGalleryImage( $id );


            $this->db->query( 'UPDATE %tp%contentimages SET title = ?, description = ? WHERE imageid = ?', $this->_post( 'title' ), $this->_post( 'description' ), $id );

            Library::sendJson( true, sprintf( trans( 'Das Bild ´%s´ wurde aktualisiert' ), $img[ 'originalname' ] ) );
        }


        if ( $this->_post( 'filepath' ) )
        {
            $post     = $this->_post();
            $filepath = $this->_post( 'filepath' );
            $filepath = PUBLIC_PATH . $filepath;

            $mime = Library::getMimeType( $filepath );
            $ext  = strtolower( Library::getExtension( $filepath ) );
            $name = Library::getFilename( $filepath );

            $str = $this->db->compile_db_insert_string( array(
                'title'        => '',
                'contentid'    => -1,
                'mime'         => $mime,
                'filepath'     => str_replace( ROOT_PATH, '', $filepath ),
                'published'    => 1,
                'userid'       => User::getUserId(),
                'created'      => TIMESTAMP,
                'originalname' => $name,
                'ordering'     => 0,
                'isserverfile' => 1
            ) );

            $this->db->query( "INSERT INTO %tp%contentimages ({$str['FIELD_NAMES']}) VALUES({$str['FIELD_VALUES']})" );

            $id = $this->db->insert_id();

            $imgs    = Session::get( 'news-content-images', array() );
            $imgs[ ] = $id;
            Session::save( 'news-content-images', $imgs );

            $status               = array();
            $status[ "success" ]  = true;
            $status[ "thumbid" ]  = $id;
            $status[ "filepath" ] = str_replace( ROOT_PATH, '', $filepath );
            $status[ "filename" ] = $name;
            $status[ "filesize" ] = Library::formatSize( (int)filesize( $filepath ) );
            $status[ "status" ]   = trans( 'Speichern erfolgreich' );
            $status[ "size" ]     = Library::formatSize( filesize( $filepath ) );
            $status[ "type" ]     = $ext;
            $status[ 'fileurl' ]  = str_replace( ROOT_PATH, '', $filepath );


            if ( $ext === 'jpg' || $ext === 'jpeg' || $ext === 'png' || $ext === 'gif' )
            {
                $img      = ImageTools::create( $cache_path );
                $saveType = ( $ext === 'jpg' || $ext === 'gif' ? 'jpeg' : $ext );

                $_chain = array(
                    0 => array(
                        0 => 'resize',
                        1 => array(
                            'width'       => ( isset( $post[ 'chainwidth' ] ) && (int)$post[ 'chainwidth' ] ?
                                    (int)$post[ 'chainwidth' ] : 120 ),
                            'height'      => ( isset( $post[ 'chainheight' ] ) && (int)$post[ 'chainheight' ] ?
                                    (int)$post[ 'chainheight' ] : 90 ),
                            'keep_aspect' => ( isset( $post[ 'chainaspect' ] ) ? $post[ 'chainaspect' ] : true ),
                            'shrink_only' => ( isset( $post[ 'chainshrink' ] ) ? $post[ 'chainshrink' ] : false )
                        )
                    )
                );


                $_data = $img->process( array(
                    'source' => Library::formatPath( $filepath ),
                    'output' => $saveType,
                    'chain'  => $_chain
                ) );

                if ( $_data[ 'path' ] )
                {
                    $status[ 'width' ]  = (int)$_data[ 'width' ];
                    $status[ 'height' ] = (int)$_data[ 'height' ];
                    $status[ 'path' ]   = $_data[ 'path' ];
                }
            }

            echo Library::json( $status );
            exit;
        }


        $galpfad            = $this->giveFolder( 'img/news/content-gallery', true, true, true );
        $uploader           = new Upload( $galpfad, 'Filedata', false, array('*.jpg', '*.jpeg', '*.gif', '*.png') );
        $uploader->checkXXS = true;
        $uploader->execute( array($this, '_onUploaded'), true );
        exit;


        $upload = isset( $_FILES[ 'Filedata' ] ) ? $_FILES[ 'Filedata' ] : null;
        // Parse the Content-Disposition header, if available:
        $file_name = $_SERVER[ 'HTTP_CONTENT_DISPOSITION' ] ?
            rawurldecode( preg_replace( '/(^[^"]+")|("$)/', '', $_SERVER[ 'HTTP_CONTENT_DISPOSITION' ] ) ) : null;

        // Parse the Content-Range header, which has the following form:
        // Content-Range: bytes 0-524287/2000000
        $content_range = $_SERVER[ 'HTTP_CONTENT_RANGE' ] ? preg_split( '/[^0-9]+/', $_SERVER[ 'HTTP_CONTENT_RANGE' ] ) :
            null;
        $size          = $content_range ? $content_range[ 3 ] : null;


        $post = $this->input();


        if ( $upload && is_array( $upload[ 'tmp_name' ] ) )
        {
            foreach ( $upload[ 'tmp_name' ] as $index => $value )
            {
                $uploadname = $file_name ? $file_name : $upload[ 'name' ][ $index ];
                $temp       = $upload[ 'tmp_name' ][ $index ];
                $size       = ( $size ? $size : $upload[ 'size' ][ $index ] );


                $name      = Library::sanitizeFilename( $uploadname );
                $extension = Library::getExtension( $uploadname );
                $isImage   = Library::canGraphic( $uploadname );


                if ( !$isImage )
                {
                    continue;
                }


                $fupload_name = strtolower( $uploadname );
                $fupload_name = str_replace( " ", "", $fupload_name );
                $ndat         = explode( ".", $fupload_name );

                $upload_path = $galpfad;

                $file = new Upload( $_FILES[ 'Filedata' ], true, $galpfad );
                if ( $file->success() )
                {
                    $basepath = $file->getPath();
                    $path     = str_replace( $galpfad, '', $basepath );
                    $mime     = Library::getMimeType( $basepath );

                    $str = $this->db->compile_db_insert_string( array(
                        'title'        => '',
                        'contentid'    => -1,
                        'mime'         => $mime,
                        'filepath'     => str_replace( basename( $path ), '', str_replace( PAGE_PATH, '', $basepath ) ),
                        'published'    => 1,
                        'userid'       => User::getUserId(),
                        'created'      => TIMESTAMP,
                        'originalname' => $uploadname,
                        'isserverfile' => 0,
                        'ordering'     => 0
                    ) );

                    $this->db->query( "INSERT INTO %tp%contentimages ({$str['FIELD_NAMES']}) VALUES({$str['FIELD_VALUES']})" );
                }
                else
                {
                    throw new BaseException( $file->getError() );
                }

                $id      = $this->db->insert_id();
                $imgs    = Session::get( 'news-content-images', array() );
                $imgs[ ] = $id;
                Session::save( 'news-content-images', $imgs );

                $status               = array();
                $status[ "success" ]  = true;
                $status[ "thumbid" ]  = $id;
                $status[ "filepath" ] = $basepath;
                $status[ "filename" ] = $name;
                $status[ "filesize" ] = Library::formatSize( (int)filesize( $basepath ) );
                $status[ "status" ]   = trans( 'Speichern erfolgreich' );
                $status[ "size" ]     = Library::formatSize( (int)$size );
                $status[ "type" ]     = $extension;
                $status[ 'fileurl' ]  = str_replace( ROOT_PATH, '', $basepath );


                if ( $post[ 'usechain' ] && is_file( $basepath ) )
                {
                    $cache_path = PAGE_PATH . '.cache/thumbnails/img/';
                    if ( !is_dir( $cache_path ) )
                    {
                        Library::makeDirectory( $cache_path );
                    }

                    $srci = Library::formatPath( $basepath );
                    $ext  = strtolower( Library::getExtension( $srci ) );

                    if ( $ext === 'jpg' || $ext === 'jpeg' || $ext === 'png' || $ext === 'gif' )
                    {
                        $img      = ImageTools::create( $cache_path );
                        $saveType = ( $ext === 'jpg' || $ext === 'gif' ? 'jpeg' : $ext );

                        $_chain = array(
                            0 => array(
                                0 => 'resize',
                                1 => array(
                                    'width'       => ( isset( $post[ 'chainwidth' ] ) && (int)$post[ 'chainwidth' ] ?
                                            (int)$post[ 'chainwidth' ] : 120 ),
                                    'height'      => ( isset( $post[ 'chainheight' ] ) && (int)$post[ 'chainheight' ] ?
                                            (int)$post[ 'chainheight' ] : 90 ),
                                    'keep_aspect' => ( isset( $post[ 'chainaspect' ] ) ? $post[ 'chainaspect' ] : true ),
                                    'shrink_only' => ( isset( $post[ 'chainshrink' ] ) ? $post[ 'chainshrink' ] : false )
                                )
                            )
                        );


                        $_data = $img->process( array(
                            'source' => Library::formatPath( $srci ),
                            'output' => $saveType,
                            'chain'  => $_chain
                        ) );

                        if ( $_data[ 'path' ] )
                        {
                            $status[ 'width' ]  = (int)$_data[ 'width' ];
                            $status[ 'height' ] = (int)$_data[ 'height' ];
                            $status[ 'path' ]   = $_data[ 'path' ];
                        }
                    }
                }


                echo Library::json( $status );
                exit;
            }
        }


        if ( isset( $_FILES[ 'Filedata' ] ) && ( !empty( $_FILES[ 'Filedata' ][ 'name' ] ) || !empty( $_FILES[ 'Filedata' ][ 'filename' ] ) ) )
        {
            $size       = $_FILES[ 'Filedata' ][ 'size' ];
            $uploadname = isset( $_FILES[ 'Filedata' ][ 'filename' ] ) ? $_FILES[ 'Filedata' ][ 'filename' ] :
                $_FILES[ 'Filedata' ][ 'name' ];
            $temp       = isset( $_FILES[ 'Filedata' ][ 'filename' ] ) ? $_FILES[ 'Filedata' ][ 'filename' ] :
                $_FILES[ 'Filedata' ][ 'tmp_name' ];

            $name      = Library::sanitizeFilename( $uploadname );
            $extension = Library::getExtension( $uploadname );
            $isImage   = Library::canGraphic( $uploadname );

            if ( !$isImage )
            {
                Library::sendJson( false, trans( 'Es sind leider nur Bilder für den Upload erlaubt!' ) );
            }

            $fupload_name = strtolower( $uploadname );
            $fupload_name = str_replace( " ", "", $fupload_name );
            $ndat         = explode( ".", $fupload_name );

            $upload_path = /* PAGE_PATH . */
                $galpfad;

            $file = new Upload( $_FILES[ 'Filedata' ], true, $galpfad );
            if ( $file->success() )
            {
                $basepath = $file->getPath();
                $path     = str_replace( $galpfad, '', $basepath );
                $mime     = Library::getMimeType( $basepath );

                $str = $this->db->compile_db_insert_string( array(
                    'title'        => '',
                    'contentid'    => -1,
                    'mime'         => $mime,
                    'filepath'     => str_replace( ROOT_PATH, '', $basepath ),
                    'published'    => 1,
                    'userid'       => User::getUserId(),
                    'created'      => TIMESTAMP,
                    'originalname' => $uploadname,
                    'isserverfile' => 0,
                    'ordering'     => 0
                ) );
                $this->db->query( "INSERT INTO %tp%contentimages ({$str['FIELD_NAMES']}) VALUES({$str['FIELD_VALUES']})" );


                $id      = $this->db->insert_id();
                $imgs    = Session::get( 'news-content-images', array() );
                $imgs[ ] = $id;
                Session::save( 'news-content-images', $imgs );


                $status               = array();
                $status[ "success" ]  = true;
                $status[ "filepath" ] = str_replace( ROOT_PATH, '', $basepath );
                $status[ "filename" ] = $name;
                $status[ "filesize" ] = Library::formatSize( (int)filesize( $basepath ) );
                $status[ "status" ]   = trans( 'Speichern erfolgreich' );
                $status[ "size" ]     = Library::formatSize( (int)$size );
                $status[ "type" ]     = $extension;
                $status[ "thumbid" ]  = $id;
                $status[ 'fileurl' ]  = str_replace( ROOT_PATH, '', $basepath );


                if ( $post[ 'usechain' ] && is_file( $basepath ) )
                {
                    $srci       = Library::formatPath( $basepath );
                    $cache_path = PAGE_PATH . '.cache/thumbnails/img/';
                    if ( !is_dir( $cache_path ) )
                    {
                        Library::makeDirectory( $cache_path );
                    }

                    $ext = strtolower( Library::getExtension( $srci ) );

                    if ( $ext === 'jpg' || $ext === 'jpeg' || $ext === 'png' || $ext === 'gif' )
                    {
                        $img      = ImageTools::create( $cache_path );
                        $saveType = ( $ext === 'jpg' || $ext === 'gif' ? 'jpeg' : $ext );


                        $_chain = array(
                            0 => array(
                                0 => 'resize',
                                1 => array(
                                    'width'       => ( isset( $post[ 'chainwidth' ] ) && (int)$post[ 'chainwidth' ] ?
                                            (int)$post[ 'chainwidth' ] : 120 ),
                                    'height'      => ( isset( $post[ 'chainheight' ] ) && (int)$post[ 'chainheight' ] ?
                                            (int)$post[ 'chainheight' ] : 90 ),
                                    'keep_aspect' => ( isset( $post[ 'chainaspect' ] ) ? $post[ 'chainaspect' ] : true ),
                                    'shrink_only' => ( isset( $post[ 'chainshrink' ] ) ? $post[ 'chainshrink' ] : false )
                                )
                            )
                        );

                        $_data = $img->process( array(
                            'source' => Library::formatPath( $srci ),
                            'output' => $saveType,
                            'chain'  => $_chain
                        ) );

                        if ( $_data[ 'path' ] )
                        {
                            $status[ 'width' ]  = (int)$_data[ 'width' ];
                            $status[ 'height' ] = (int)$_data[ 'height' ];
                            $status[ 'path' ]   = $_data[ 'path' ];
                        }
                    }
                }


                echo Library::json( $status );
                exit;
            }
            else
            {
                throw new BaseException( $file->getError() );
            }
        }
        else
        {
            throw new BaseException( 'The file could not be saved, perhaps it\'s too large?' );
        }
    }

}
