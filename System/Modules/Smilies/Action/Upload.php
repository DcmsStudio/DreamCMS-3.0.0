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
 * @package
 * @version      3.0.0 Beta
 * @category
 * @copyright    2008-2014 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Upload.php
 */
class Smilies_Action_Upload extends Controller_Abstract
{

    public function execute()
    {

        if ( $this->isFrontend() )
        {
            return;
        }

        $this->runBackend();

    }

    private function runBackend()
    {

        $upload_path = HTML_URL . 'img/smilies/';


        $posthash = $this->input( 'posthash' );

        if ( $this->input( 'remove' ) )
        {
            $id     = intval( $this->input( 'remove' ) );
            $smilie = $this->model->getSmilieById( $id );

            if ( $smilie[ 'smilieid' ] && $smilie[ 'smiliepath' ] )
            {

                $file = new File( null, true );
                $file->delete( $upload_path . $posthash . '/' . $smilie[ 'smiliepath' ] );


            }
        }


        if ( isset( $_FILES[ 'Filedata' ] ) && !empty( $_FILES[ 'Filedata' ][ 'name' ] ) )
        {
            demoadm();


            if ( !is_dir( $upload_path ) )
            {
                Library::makeDirectory( $upload_path );
            }


            $uploader           = new Upload( $upload_path . $posthash, 'Filedata', User::getPerm( 'smilies/maxuploadsize' ) * 1024, User::getPerm( 'smilies/allowedextensions' ) );
            $uploader->checkXXS = true;
            $imgData            = $uploader->execute( array($this, '_onUploaded'), true );


            $file = new Upload( $_FILES[ 'Filedata' ], true, $upload_path );
            if ( $file->success() )
            {
                $contents = '';

                if ( HTTP::input( 'pathonly' ) )
                {
                    $contents = file_get_contents( $file->getPath() );
                    $file->delete();
                }


                if ( str_replace( ROOT_PATH, '', $file->getPath() ) === '' )
                {
                    Library::sendJson( array(
                        'success' => false,
                        'msg'     => 'upload.error.not.moved'
                    ) );
                }

                $path     = $file->getPath();
                $p        = explode( '/', $path );
                $filename = array_pop( $p );

                rename( $file->getPath(), UPLOAD_PATH . "tmp/avatar-" . $filename );

                echo Library::json( array(
                    'success'  => true,
                    'fileurl'  => UPLOAD_URL . "tmp/avatar-" . $filename,
                    'filename' => $filename,
                    'filesize' => Library::formatSize( filesize( UPLOAD_PATH . "tmp/avatar-" . $filename ) ),
                    'path'     => str_replace( ROOT_PATH, '', $path ),
                    'name'     => trans( 'Load file...' ),
                    'content'  => $contents
                ) );
                exit;
            }
            else
            {
                Error::raise( $file->getError() );
            }
        }
        else
        {
            Error::raise( 'no file in upload?' );
        }
    }

    public function _onUploaded($data, Upload $uploader)
    {
        if ( $data[ 'success' ] )
        {
            $posthash = $this->input( 'posthash' );

            $upload_path = HTML_URL . 'img/smilies/';

            $file = new File( null, true );

            if ( $file->isGzFile( $data[ 'filepath' ] ) )
            {

            }
            elseif ( $file->isZipFile( $data[ 'filepath' ] ) )
            {
                $this->unZip( $data[ 'filepath' ], $upload_path . $posthash . '/' );
            }
            else
            {
                $name = Library::getFilename( $data[ 'filepath' ], true );
                if ( $name )
                {
                    $bbcode = ':' . $name . ':';
                }
                else
                {
                    $bbcode = ':' . substr( (string)TIMESTAMP, -5 ) . ':';
                }

                $id = $this->model->insert( array(
                    'posthash'    => $posthash,
                    'groupid'     => 0,
                    'type'        => 0,
                    'smiliepath'  => str_replace($upload_path, '', $data[ 'filepath' ]),
                    'smilietitle' => $name,
                    'smiliecode'  => $bbcode,
                    'smilieorder' => 0
                ) );

                echo Library::json( array(
                    'success'  => true,
                    'id'       => $id,
                    'smiliepath'  => str_replace($upload_path, '', $data[ 'filepath' ]),
                    'smiliecode' => $bbcode,
                    'smilietitle'  => $name
                ) );

                exit;

            }


            $this->db->query( 'INSERT INTO %tp%board_attachments (postid,userid,path,hits,filesize,mime,posthash)
							  VALUES(?,?,?,?,?,?,?)', 0, User::getUserId(), str_replace( $upload_path, '', $data[ 'filepath' ] ), 0, $data[ 'filesize' ], $data[ 'filemime' ], $this->input( 'posthash' ) );



        }
        else
        {

            if ( IS_AJAX )
            {
                Library::sendJson( false, $uploader->getError() );
            }
            else
            {
                return false;
            }
        }
    }

    private function unZip($file, $topath)
    {

    }
}