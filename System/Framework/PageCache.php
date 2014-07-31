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
 * @file        PageCache.php
 *
 */
class PageCache extends Loader
{

    /**
     * @var null
     */
    protected static $objInstance = null;

    /**
     * @var null
     */
    private $_cacheHash = null;

    /**
     * @var null
     */
    private $_dataChecksum = null;

    /**
     *
     * @var integer
     */
    private $_lastModifyTimestamp = null;

    /**
     * Prevent cloning of the object (Singleton)
     */
    private function __clone()
    {
        
    }

    /**
     * Return the current object instance (Singleton)
     *
     * @return object
     */
    public static function getInstance()
    {
        if ( !is_object( self::$objInstance ) )
        {
            self::$objInstance = new PageCache();
        }

        return self::$objInstance;
    }

    public function __destruct()
    {
        parent::__destruct();
        self::$objInstance = null;
    }

    private function _createCacheHash()
    {
        $this->_cacheHash = md5( CONTROLLER . ACTION . REQUEST . CONTENT_TRANS . User::getGroupId() );
    }

    /**
     *
     * @return string
     */
    private function getCachePath()
    {
        return CONTENT_TRANS . '/' . CONTROLLER;
    }

    /**
     *
     * @return string
     */
    public function getCacheHash()
    {
        if ( is_null( $this->_cacheHash ) )
        {
            $this->_createCacheHash();
        }

        return $this->_cacheHash;
    }

    /**
     *
     * @param string $html
     * @param array $data
     */
    public function cachePage( $html, $data = array() )
    {
        $fileHash = $this->getCacheHash();
        $path = $this->getCachePath();

        $filedata_path = PAGE_PATH . 'cache/pagecache/' . $path . '/' . $fileHash . '-data.php';
        $fileoutput_path = PAGE_PATH . 'cache/pagecache/' . $path . '/' . $fileHash . '-output.php';
    }

    /**
     * @return bool
     */
    public function getPageCache()
    {
        $fileHash = $this->getCacheHash();
        $path = $this->getCachePath();

        $filedata_path = PAGE_PATH . 'cache/pagecache/' . $path . '/' . $fileHash . '-data.php';
        $fileoutput_path = PAGE_PATH . 'cache/pagecache/' . $path . '/' . $fileHash . '-output.php';

        if ( is_file( $filedata_path ) )
        {
            $this->_dataChecksum = md5( serialize( $_data ) );


            $outputCacheTime = null;
            if ( is_file( $fileoutput_path ) )
            {
                $outputCacheTime = filemtime( $fileoutput_path );
            }

            if ( !$outputCacheTime )
            {
                return false;
            }

            // Cache is to old
            if ( (TIMESTAMP - $_data[ 'cacheTime' ]) > $outputCacheTime )
            {
                $_data = null;
                unlink( $fileoutput_path );
                unlink( $filedata_path );
                return false;
            }

            $this->_lastModifyTimestamp = $outputCacheTime;
            $this->sendCache( file_get_contents( $fileoutput_path ), $_data );
        }

        return false;
    }

    /**
     *
     * @param string $output
     * @param        $data
     */
    private function sendCache( $output, &$data )
    {
        // Update Site hits
        if ( $data[ 'clickanalyse' ] && isset( $data[ 'clickanalyseTable' ] ) && !empty( $data[ 'clickanalyseTable' ] ) && isset( $data[ 'clickanalyseTablePK' ] ) && !empty( $data[ 'clickanalyseTablePK' ] ) )
        {
            $this->db->query( 'UPDATE ' . $data[ 'clickanalyseTable' ] . ' SET hits = hits + 1 WHERE ' . $data[ 'clickanalyseTablePK' ] . ' = ?', $data[ $data[ 'clickanalyseTablePK' ] ] );
        }


        $this->load( 'Output' );
        $this->Output->appendOutput( $output )->sendOutput();
    }

}
