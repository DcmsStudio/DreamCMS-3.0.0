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
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Progressbar.php
 *
 */
class Progressbar extends Loader
{

    /**
     * @var int
     */
    private $progressbarWidth = 500;

    /**
     * @var null
     */
    private $progressbarName = null;

    /**
     * @var array|null
     */
    private $id = null;

    /**
     * @var bool
     */
    private $startAtOnload = true;

    /**
     * @var string
     */
    private static $lastContent;

    /**
     * @param     $barname
     * @param int $barwidth
     * @param int $ts
     */
    public function __construct( $barname, $barwidth = 500, $ts = 0 )
    {
        $this->progressbarName = $barname;
        $this->progressbarWidth = $barwidth;


        if ( HTTP::input( 'stop' ) )
        {
            $this->id = HTTP::input( 'id' );

            Session::save( 'stopIndexing', true );

            // $this->clean();


            echo Library::json( array(
                'success' => true ) );
            exit;
        }

        if ( !($this->id = Session::get( 'IndexerID', false )) )
        {
            $this->generateUniqueId();
            $this->makeIndexingFile();
        }

        $getId = HTTP::input( 'GETDATA' );
        if ( $getId != '' )
        {
            if ( $getId === 'null' && HTTP::input( 'PROGRESS_DELID' ) )
            {
                $this->clean();
                Library::sendJson( true );
            }
            else
            {
                if ( !file_exists( PAGE_CACHE_PATH . $getId . '.idx' ) )
                {
                    echo Library::json( array(
                        'success'       => true,
                        'filenotexists' => true ) );
                    exit;
                }

                session_write_close();
                usleep( 10000 );

                $fp = fopen( PAGE_CACHE_PATH . $getId . '.idx', 'r' );
                $code = fread( $fp, 2048 );
                fclose( $fp );


                if ( !$code )
                {
                    $code = self::$lastContent;
                }
                else
                {
                    self::$lastContent = $code;
                }
                header( 'Content-Type: application/json' );

                header( "Expires: Mon, 20 Jul 1995 05:00:00 GMT" );
                header( "Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . " GMT" );
                header( "Cache-Control: no-store, no-cache, must-revalidate" );
                header( "Cache-Control: post-check=0, pre-check=0", false );
                header( "Pragma: no-cache" );

                echo $code;
            }

            exit;
        }
    }

    /**
     *
     */
    public function finish()
    {
        $this->clean();
        Library::log( 'Has reganerate the search index for.' );
    }

    /**
     * @param int $ts
     * @return Progressbar
     */
    public function generateUniqueId( $ts = 0 )
    {
        $this->id = 'IDX' . time();
        Session::save( 'IndexerID', $this->id );
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getUniqueId()
    {
        return $this->id;
    }

    /**
     *
     * @param string $data
     * @return Progressbar
     */
    public function set( $data )
    {
        if ( $this->id )
        {
            $fp = fopen( PAGE_CACHE_PATH . $this->id . '.idx', 'w' );
            fwrite( $fp, $data );
            fclose( $fp );

            @chmod( PAGE_CACHE_PATH . $this->id . '.idx', 0777 );
        }

        return $this;
    }

    /**
     *
     * @return Progressbar
     */
    public function clean()
    {
        Session::delete( 'currentIndex' );
        Session::delete( 'IndexerID' );

        if ( file_exists( PAGE_CACHE_PATH . $this->id . '.idx' ) )
        {
            @unlink( PAGE_CACHE_PATH . $this->id . '.idx' );
        }

        $this->id = false;

        return $this;
    }

    /**
     * @return Progressbar
     */
    private function makeIndexingFile()
    {
        $fp = fopen( PAGE_CACHE_PATH . $this->id . '.idx', 'w' );
        fwrite( $fp, '' );
        fclose( $fp );

        @chmod( PAGE_CACHE_PATH . $this->id . '.idx', 0777 );

        return $this;
    }

    /**
     *
     * @param boolean $param
     * @return Progressbar
     */
    public function setOnloadStart( $param = true )
    {
        $this->startAtOnload = $param;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function create()
    {
        $data[ 'progressbarName' ] = $this->progressbarName;
        $data[ 'progressbarWidth' ] = $this->progressbarWidth;

        $data[ 'progressbarIndex' ] = $this->id;
        $data[ 'progressbarRunAtOnload' ] = $this->startAtOnload;
        $data[ 'progressbarRunUrl' ] = $this->runUrl;

        return $data;
    }

}
