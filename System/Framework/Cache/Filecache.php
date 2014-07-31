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
 * @file        Filecache.php
 *
 */
class Cache_Filecache extends Cache_Abstract
{

    /**
     *
     * @param string $name
     * @return string
     */
    private static function getExtension( $name )
    {
        if ( strpos( $name, '.' ) !== false )
        {
            $exts = explode( '.', $name );
            return array_pop( $exts );
        }
        else
        {
            return 'php';
        }
    }

    /**
     *
     * @param string $name
     * @param mixed $data
     * @param string $type
     * @param boolean $as_file
     */
    public static function set( $name, $data, $type = 'data', $as_file = false )
    {

        Library::makeDirectory( self::$cachePath . $type );

        $ext = strtolower( self::getExtension( $name ) );
        $_name = str_replace( '.' . $ext, '', $name );
        $path = self::$cachePath . $type . '/' . $_name . '.' . $ext;


        if ( is_array( $data ) || is_object( $data ) || is_resource( $data ) )
        {
            if ( !$as_file )
            {
                $data = "<?php\nif(!defined('IN')) { die('Access Denied'); }\nreturn " . self::var_export_min( $data, true ) . ";\n?>";
            }
            else
            {

                trigger_error( 'Cannot save non-scalar data in `' . $name . '` to cache as file (filecache::set()).', E_USER_ERROR );
            }
        }
        else
        {
            if ( $ext === 'php' )
            {
                $data = '<?php if(!defined("IN")) { die("Access Denied"); } ob_start(); ?>' . $data . '<?php return ob_get_clean(); ?>';
            }
            else
            {
                $data = '<?php if(!defined("IN")) { die("Access Denied"); } ob_start(); ?>' . $data . '<?php return ob_get_clean(); ?>';
            }
        }

        if ( !is_dir( self::$cachePath . $type ) )
        {
            trigger_error( 'Cache directory `' . $type . '` not found in `' . str_replace( ROOT_PATH, '', self::$cachePath ) . '` - please create this folder with write permissions for the webserver process before continuing.', E_USER_ERROR );
        }

        if ( !is_writable( self::$cachePath . $type ) )
        {

            trigger_error( 'Cache directory `' . str_replace( ROOT_PATH, '', self::$cachePath . $type ) . '`is not writable - please provide write permissions on this directory for the webserver process before continuing.', E_USER_ERROR );
        }


        $path = Library::formatPath( $path );


        $file = new File($path);

        if ( !Library::isWin() )
        {
            $file->open('wb')->writelock()->write($data);
            $file->unlock()->close();
        }
        else
        {
            $file->open('wb')->write($data);
            $file->close();
        }

        //@chmod($path, 0777); // change the permissions

        unset( self::$_dataCache[ $type ][ $name ] );
    }

    /**
     *
     * @param string $name
     * @param string $type
     * @param null|int $cacheTime
     * @return null|mixed
     */
    public static function get( $name, $type = 'data', $cacheTime = null )
    {

        if ( isset( self::$_dataCache[ $type ][ $name ] ) )
        {
            return self::$_dataCache[ $type ][ $name ];
        }
        $ext = self::getExtension( $name );
        $_name = str_replace( '.' . $ext, '', $name );
        $path = self::$cachePath . $type . '/' . $_name . '.' . $ext;

        if ( (int)$cacheTime  > 0 && file_exists( $path ) )
        {
            if ( (time() - @filemtime( $path )) > $cacheTime )
            {
                Library::enableErrorHandling();
                return null;
            }
        }

	    if ( !file_exists( $path ) ) {
		    return null;
	    }

        Library::disableErrorHandling();

        ob_start();
        $data = include($path);
        ob_get_clean();


        Library::enableErrorHandling();

        if ( is_array( $data ) || is_string( $data ) )
        {
            self::$_dataCache[ $type ][ $name ] = $data;
            return $data;
        }

        return null;
    }

    /**
     *
     * @param string $name
     * @param string $type
     */
    public static function delete( $name, $type = 'data' )
    {
        if ( substr( $type, -1 ) == '/' )
        {
            $type = substr( $type, 0, -1 );
        }


        if ( strpos( $name, '*' ) !== false )
        {
            $items = glob( self::$cachePath . $type . '/' . $name, GLOB_NOSORT );
            if ( is_array( $items ) )
            {
                foreach ( $items as $item )
                {
                    if ( is_file( $item ) && !is_dir( $item ) )
                    {
                        unset( self::$_dataCache[ $type ][ $name ] );
                        @unlink( $item );
                    }
                }
            }
        }
        else
        {
            $cache_name = $name . '.php';
            if ( is_file( self::$cachePath . $type . '/' . $cache_name ) && !is_dir( self::$cachePath . $type . '/' . $cache_name ) )
            {
                unset( self::$_dataCache[ $type ][ $name ] );
                @unlink( self::$cachePath . $type . '/' . $cache_name );
            }
        }
    }

    /**
     *
     * @param string $type
     * @param boolean $clearsubdirs
     */
    public static function clear( $type = 'data', $clearsubdirs = false )
    {
        if ( is_dir( self::$cachePath . $type ) && self::$cachePath != self::$cachePath . $type )
        {
            $d = dir( self::$cachePath . $type );
            while ( false !== ($entry = $d->read()) )
            {
                if ( $entry != '.' && $entry != '..' )
                {
                    $cache = Library::formatPath( $d->path . '/' . $entry );

                    if ( is_dir( $cache ) )
                    {
                        if ( $clearsubdirs ) {
                            // @chmod($cache, 0777);
                            try
                            {
                                Library::rmdirr( $cache, $clearsubdirs );
                            }
                            catch ( Exception $e )
                            {
                                trigger_error( 'Unhandled Exception: ' . $e->getMessage(), E_USER_ERROR );
                            }
                        }
                    }
                    else
                    {
                        try
                        {
                            unlink( $cache );
                        }
                        catch ( Exception $e )
                        {
                            trigger_error( 'Unhandled Exception: ' . $e->getMessage(), E_USER_ERROR );
                        }
                    }
                }
            }
            $d->close();

            unset( self::$_dataCache[ $type ] );
        }
    }

    /**
     *
     * @param array $excludes
     */
    public static function flush( $excludes = array() )
    {
        $d = dir( self::$cachePath );
        while ( false !== ($entry = $d->read()) )
        {
            if ( $entry != '.' && $entry != '..' && $entry != '' )
            {
                $cache = Library::formatPath( $d->path . '/' ) . $entry;
                if ( is_dir( $cache ) && !in_array( $entry, $excludes ) )
                {
                    self::clear( $entry );
                }
            }
        }
        $d->close();

        self::$_dataCache = array();
    }

    /**
     *
     * @param string $path
     * @return array
     */
    public static function countCaches( $path = 'data' )
    {
        $data = array('filecache' => array('folders' => 0, 'files' => 0, 'size' => 0));

        $files = Library::getFiles(PAGE_CACHE_PATH . rtrim($path, '/\\') . '/', true);
        foreach ($files as $r) {

            if (file_exists($r['path'] . $r['filename'])) {
                $data['filecache']['files'] += 1;
                $data['filecache']['size'] += filesize($r['path'] . $r['filename']);
            }

        }

        return $data;
    }

}

?>