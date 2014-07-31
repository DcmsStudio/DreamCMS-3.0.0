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
 * @category    Framework
 * @copyright    2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Media.php
 */
class Media
{
    /**
     * @param $metadata
     * @param $data
     */
    private static function addId3TagData( &$metadata, $data ) {
        foreach ( array( 'id3v2', 'id3v1' ) as $version ) {
            if ( ! empty( $data[$version]['comments'] ) ) {
                foreach ( $data[$version]['comments'] as $key => $list ) {
                    if ( ! empty( $list ) ) {
                        $metadata[$key] = reset( $list );
                        // fix bug in byte stream analysis
                        if ( 'terms_of_use' === $key && 0 === strpos( $metadata[$key], 'yright notice.' ) )
                            $metadata[$key] = 'Cop' . $metadata[$key];
                    }
                }
                break;
            }
        }

        if ( ! empty( $data['id3v2']['APIC'] ) ) {
            $image = reset( $data['id3v2']['APIC']);
            if ( ! empty( $image['data'] ) ) {
                $metadata['image'] = array(
                    'data' => $image['data'],
                    'mime' => $image['image_mime'],
                    'width' => $image['image_width'],
                    'height' => $image['image_height']
                );
            }
        } elseif ( ! empty( $data['comments']['picture'] ) ) {
            $image = reset( $data['comments']['picture'] );
            if ( ! empty( $image['data'] ) ) {
                $metadata['image'] = array(
                    'data' => $image['data'],
                    'mime' => $image['image_mime']
                );
            }
        }
    }

    /**
     * @param string $file
     * @return array|bool
     */
    public static function getVideoMeta($file)
    {
        if ( !file_exists( $file ) )
        {
            return false;
        }

        $metadata = array();

        if ( !class_exists( 'getID3' ) )
        {
            require_once( VENDOR_PATH . 'ID3/getid3.php' );
        }


        $id3  = new getID3();
        $data = $id3->analyze( $file );

        if ( isset( $data[ 'video' ][ 'lossless' ] ) )
        {
            $metadata[ 'lossless' ] = $data[ 'video' ][ 'lossless' ];
        }
        if ( !empty( $data[ 'video' ][ 'bitrate' ] ) )
        {
            $metadata[ 'bitrate' ] = (int)$data[ 'video' ][ 'bitrate' ];
        }
        if ( !empty( $data[ 'video' ][ 'bitrate_mode' ] ) )
        {
            $metadata[ 'bitrate_mode' ] = $data[ 'video' ][ 'bitrate_mode' ];
        }
        if ( !empty( $data[ 'filesize' ] ) )
        {
            $metadata[ 'filesize' ] = (int)$data[ 'filesize' ];
        }
        if ( !empty( $data[ 'mime_type' ] ) )
        {
            $metadata[ 'mime_type' ] = $data[ 'mime_type' ];
        }
        if ( !empty( $data[ 'playtime_seconds' ] ) )
        {
            $metadata[ 'length' ] = (int)ceil( $data[ 'playtime_seconds' ] );
        }
        if ( !empty( $data[ 'playtime_string' ] ) )
        {
            $metadata[ 'length_formatted' ] = $data[ 'playtime_string' ];
        }
        if ( !empty( $data[ 'video' ][ 'resolution_x' ] ) )
        {
            $metadata[ 'width' ] = (int)$data[ 'video' ][ 'resolution_x' ];
        }
        if ( !empty( $data[ 'video' ][ 'resolution_y' ] ) )
        {
            $metadata[ 'height' ] = (int)$data[ 'video' ][ 'resolution_y' ];
        }
        if ( !empty( $data[ 'fileformat' ] ) )
        {
            $metadata[ 'fileformat' ] = $data[ 'fileformat' ];
        }
        if ( !empty( $data[ 'video' ][ 'dataformat' ] ) )
        {
            $metadata[ 'dataformat' ] = $data[ 'video' ][ 'dataformat' ];
        }
        if ( !empty( $data[ 'video' ][ 'encoder' ] ) )
        {
            $metadata[ 'encoder' ] = $data[ 'video' ][ 'encoder' ];
        }
        if ( !empty( $data[ 'video' ][ 'codec' ] ) )
        {
            $metadata[ 'codec' ] = $data[ 'video' ][ 'codec' ];
        }

        if ( !empty( $data[ 'audio' ] ) )
        {
            unset( $data[ 'audio' ][ 'streams' ] );
            $metadata[ 'audio' ] = $data[ 'audio' ];
        }

        self::addId3TagData( $metadata, $data );

        return $metadata;
    }

    /**
     * @param string $file
     * @return array|bool
     */
    public static function getAudioMeta($file)
    {
        if ( !file_exists( $file ) )
        {
            return false;
        }


        $metadata = array();

        if ( !class_exists( 'getID3' ) )
        {
            require_once( VENDOR_PATH . 'ID3/getid3.php' );
        }

        $id3  = new getID3();
        $data = $id3->analyze( $file );

        if ( !empty( $data[ 'audio' ] ) )
        {
            unset( $data[ 'audio' ][ 'streams' ] );
            $metadata = $data[ 'audio' ];
        }

        if ( !empty( $data[ 'fileformat' ] ) )
        {
            $metadata[ 'fileformat' ] = $data[ 'fileformat' ];
        }
        if ( !empty( $data[ 'filesize' ] ) )
        {
            $metadata[ 'filesize' ] = (int)$data[ 'filesize' ];
        }
        if ( !empty( $data[ 'mime_type' ] ) )
        {
            $metadata[ 'mime_type' ] = $data[ 'mime_type' ];
        }
        if ( !empty( $data[ 'playtime_seconds' ] ) )
        {
            $metadata[ 'length' ] = (int)ceil( $data[ 'playtime_seconds' ] );
        }
        if ( !empty( $data[ 'playtime_string' ] ) )
        {
            $metadata[ 'length_formatted' ] = $data[ 'playtime_string' ];
        }


        self::addId3TagData( $metadata, $data );

        return $metadata;
    }


    /**
     * @param        $name
     * @param string $library
     * @return mixed
     */
    public static function get($name, $library = '')
    {

        $db    = Database::getInstance();
        $media = $db->query( 'SELECT * FROM %tp%mediafiles WHERE `name` = ? AND catalogname = ?', (string)$name, (string)$library )->fetch();

        return $media;
    }

    /**
     * @param string $name
     * @param null $byUrl
     * @return mixed
     */
    public static function getAsset($name = '', $byUrl = null)
    {
        $db = Database::getInstance();

        if ( !$byUrl )
        {
            return $db->query( 'SELECT * FROM %tp%assets WHERE `name` = ? LIMIT 1', $name )->fetch();
        }
        else
        {
            return $db->query( 'SELECT * FROM %tp%assets WHERE url = ? LIMIT 1', $name )->fetch();
        }
    }

    /**
     * @param        $name
     * @param string $library
     */
    public static function download($name, $library = '')
    {
        $media = self::get( $name, $library );

        if ( $media === false )
        {
            $Page = Page::getInstance();
            $Page->send404( sprintf( trans( 'The requested media - `%s` - does not exist.' ), $name ) );
        }

        $filename = Library::formatPath( ROOT_PATH . $media[ 'path' ] );

        if ( !is_file( $filename ) )
        {
            $Page = Page::getInstance();
            $Page->send404( sprintf( trans( 'The requested file - `%s` - cannot be found.' ), basename( $filename ) ) );
        }

        EventManager::run( 'onBeforeDownloadMedia', $name ); // {CONTEXT: framework, DESC: This event fires just before a file is downloaded to the client.}

        header( "Pragma: public" );
        header( "Expires: 0" );
        header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
        header( "Cache-Control: private", false );
        header( "Content-Type: " . Library::getMimeType( $filename ) );
        header( "Content-Disposition: attachment; filename=\"" . basename( $filename ) . "\";" );
        header( "Content-Transfer-Encoding: binary" );
        header( "Content-Length: " . filesize( $filename ) );

        ob_clean();
        flush();
        readfile( $filename );
        exit();
    }

}