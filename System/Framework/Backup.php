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
 * @file        Backup.php
 *
 */
class BackupException extends Exception
{
    
}

/**
 * Class Backup
 */
class Backup
{

    /**
     * @param array $files
     * @param array $databases
     * @param array $tables
     * @return string
     * @throws BackupException
     */
    public static function create( $files = array(), $databases = array(), $tables = array() )
    {

        //$GLOBALS['memory_usage'] = array();
        // where to save the backup?
        $zip_path = DATA_PATH . 'backup/backup-' . ($files ? ($databases ? 'files-and-db' : 'filesonly') : ($databases ? 'dbonly' : '')) . '-' . date( 'YmdHis' ) . '.zip';

        $files = !is_array( $files ) ? array() : $files;
        $databases = !is_array( $databases ) ? array() : $databases;

        set_time_limit( 0 );

        // export dbs
        $db_files = array();
        $db_files[] = self::exportDB();


        // get a list of files
        $zip_files = array();
        foreach ( $files as $file )
        {
            $file_path = ROOT_PATH . $file;


            if ( Session::get( 'cancelBackup' ) || file_exists( DATA_PATH . 'backup/stop' ) )
            {
                break;
            }

            if ( is_dir( $file_path ) )
            {

                $iterator = new RecursiveDirectoryIterator( $file_path );
                foreach ( new RecursiveIteratorIterator( $iterator, RecursiveIteratorIterator::SELF_FIRST ) as $filename => $sfile )
                {
                    if ( Session::get( 'cancelBackup' ) || is_file( DATA_PATH . 'backup/stop' ) )
                    {
                        break;
                    }

                    //exclude dot files
                    if ( $file->getFilename() == '.' || $file->getFilename() == '..' || preg_match( '#(dev|\.bak|backup|\.ds_store)#is', $filename ) )
                    {
                        continue;
                    }

                    $filename = str_replace( '\\', '/', $filename );

                    //exclude cache and backup dirs
                    if ( strpos( $filename, DATA_PATH . 'backup/' ) !== false || strpos( $filename, CACHE_PATH ) !== false || strpos( $filename, PAGE_CACHE_PATH ) !== false )
                    {
                        continue;
                    }

                    // add the file to the zip index
                    $zip_files[] = $filename;
                }
            }
            else
            {
                if ( !preg_match( '#/backup/#i', $file_path ) )
                {
                    $zip_files[] = $file_path;
                }
            }
        }

        unset( $files );

        // create a zip file
        $zip = new Zip();

        // change umask and test if we can open the zip
        $old_umask = umask( 0 );
        if ( $zip->open( $zip_path, ZIPARCHIVE::CREATE ) !== true )
        {
            throw new BackupException( sprintf( trans( 'Cannot create zip file `%s` for backup.' ), $zip_path ) );
        }

        // can we save the zip?
        if ( $zip->close() !== true )
        {
            throw new BackupException( $zip->getStatusString() . ' (Directory writable?)' );
        }

        // open it again for writing
        $zip = new Zip();
        $zip->open( $zip_path, ZIPARCHIVE::CREATE );

        $counter = 0;
        foreach ( $zip_files as $file )
        {
            // if cancel backup break here
            if ( Session::get( 'cancelBackup' ) || is_file( DATA_PATH . 'backup/stop' ) )
            {
                break;
            }


            if ( is_dir( $file ) )
            {
                $zip->addEmptyDir( str_replace( ROOT_PATH, '', $file ) );
            }
            else
            {
                $zip->addFile( $file, str_replace( ROOT_PATH, '', $file ) );
            }

            $counter++;
            if ( $counter > 50 )
            {
                // 'cycle' the zip file so we don't run out of file descriptors or something.
                $zip->close();

                $zip = new ZipArchive();
                $zip->open( $zip_path, ZIPARCHIVE::CREATE );
                $counter = 0;
            }
        }

        unset( $zip_files );

        foreach ( $db_files as $file )
        {
            // if cancel backup break here
            if ( Session::get( 'cancelBackup' ) || is_file( DATA_PATH . 'backup/stop' ) )
            {
                break;
            }

            $zip->addFile( $file, basename( $file ) );
        }


        // if cancel backup break here and return
        if ( Session::get( 'cancelBackup' ) || is_file( DATA_PATH . 'backup/stop' ) )
        {
            $zip->close();


            foreach ( $db_files as $file )
            {
                if ( file_exists( $file ) && !is_dir( $file ) )
                {
                    unlink( $file );
                }
            }
            @umask( $old_umask );

            if ( is_file( $zip_path ) )
            {
                @unlink( $zip_path );
            }

            if ( is_file( DATA_PATH . 'backup/stop' ) )
            {
                unlink( DATA_PATH . 'backup/stop' );
            }


            return $zip_path;
        }



        // add some dirs that we skipped
        $zip->addEmptyDir( str_replace( ROOT_PATH, '', CACHE_PATH ) );
        $zip->addEmptyDir( str_replace( ROOT_PATH, '', DATA_PATH . 'backup/' ) );
        // these are sometimes not displayed in the zip, but they are there when extracted. weird...

        $zip->setArchiveComment( 'DreamCMS ' . VERSION . ' backupfile!' );
        $zip->close();

        foreach ( $db_files as $file )
        {
            if ( file_exists( $file ) && !is_dir( $file ) )
            {
                unlink( $file );
            }
        }

        unset( $db_files );

        umask( $old_umask );
        //dbgd($GLOBALS['memory_usage']);

        return $zip_path;
    }

    /**
     * @return string
     * @throws BackupException
     */
    public static function exportDB( /* $database, $arrtables */ )
    {
        $db = Database::getInstance();

        $database = $db->getDatabaseName();

        $path = DATA_PATH . 'backup/database-export-' . $database . '-' . date( 'YmdHis' ) . '.sql';
        $old_umask = umask( 0 );
        $handle = fopen( $path, "a" );

        // yes, this function looks like a mess :)
        $sql = '-- DreamCMS MySQL Database Dump

-- Database: ' . $database . '
-- ------------------------------------------------------
-- Export date: ' . date( "M j, Y at H:i" ) . '

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE=\'+00:00\' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE=\'NO_AUTO_VALUE_ON_ZERO\' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
';

        if ( fwrite( $handle, $sql ) === FALSE )
        {
            throw new BackupException( 'Cannot write to sql export file.' );
        }

        $alltables = true; //(isset( $arrtables[ $database ] ) ? ( array ) $arrtables[ $database ] : null );

        $tables = $db->query( 'SHOW TABLES FROM `' . $database . '`' )->fetchAll( 'num' );


        foreach ( $tables as $table )
        {
            $table = $table[ 0 ];
            // if cancel backup break here
            if ( Session::get( 'cancelBackup' ) || is_file( DATA_PATH . 'backup/stop' ) )
            {
                break;
            }
            /*

              // Only selected Tables and if $alltables null then all tables
              if ( $alltables !== null && !in_array( $table, $alltables ) )
              {
              continue;
              }
             */

            $row = $db->query( 'SHOW CREATE TABLE `' . $database . '`.`' . $table . '`' )->fetch( PDO::FETCH_NUM );

            $sql = '
--
-- Table structure for table `' . $table . '`
--

DROP TABLE IF EXISTS `' . $table . '`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
';
            $sql .= $row[ 1 ] . ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table data for table `' . $table . '`
--

LOCK TABLES `' . $table . '` WRITE;
/*!40000 ALTER TABLE `' . $table . '` DISABLE KEYS */;
';
            if ( fwrite( $handle, $sql ) === FALSE )
            {
                trigger_error( 'Cannot write to sql export file.', E_USER_ERROR );
            }

            if ( $alltables !== null )
            {
                self::exportTableData( $database, $table, $handle );
            }


            $sql = '
/*!40000 ALTER TABLE `' . $table . '` ENABLE KEYS */;
UNLOCK TABLES;
';

            if ( fwrite( $handle, $sql ) === FALSE )
            {
                trigger_error( 'Cannot write to sql export file.', E_USER_ERROR );
            }
        }
        umask( $old_umask );

        return $path;
    }

    /**
     * @param $database
     * @param $table
     * @param $handle
     * @return string
     */
    public static function exportTableData( $database, $table, $handle )
    {
        $db = Database::getInstance();

        $total = $db->query( 'SELECT COUNT(*) AS counted FROM `' . $database . '`.`' . $table . '`' )->fetch();
        $total = $total[ 'counted' ];

        if ( $total == 0 )
        {
            return '';
        }

        // start writing output to file
        $output = 'INSERT INTO `' . $table . "` VALUES \n";
        if ( fwrite( $handle, $output ) === FALSE )
        {
            trigger_error( 'Cannot write to sql export file.', E_USER_ERROR );
        }

        $processed = 0;
        $start = 0;
        $limit = 200;

        while ( $processed < $total )
        {

            // if cancel backup break here
            if ( Session::get( 'cancelBackup' ) || is_file( DATA_PATH . 'backup/stop' ) )
            {
                break;
            }

            $rows = $db->query( 'SELECT * FROM `' . $database . '`.`' . $table . '` LIMIT ' . (int) $start . ', ' . (int) $limit )->fetchAll( 'num' );
            $start += $limit;

            foreach ( $rows as $row )
            {
                $processed += 1;
                $insert_values = array();
                foreach ( $row as $value )
                {
                    if ( is_null( $value ) )
                    {
                        $insert_values[] = "NULL";
                    }
                    else
                    {
                        $value = addslashes( $value );
                        $value = str_replace( "\n", '\\r\\n', $value );
                        $value = str_replace( "\r", '', $value );
                        $insert_values[] = "'" . $value . "'";
                    }
                }
                $insert_rows[] = '(' . implode( ',', $insert_values ) . ')';
            }

            $output = implode( ",\n", $insert_rows );
            if ( $start > 0 && $processed < $total )
            {
                $output .= ",\n";
            }
            if ( fwrite( $handle, $output ) === FALSE )
            {
                trigger_error( 'Cannot write to sql export file.', E_USER_ERROR );
            }
            $insert_rows = array();
        }

        $output = ";";
        if ( fwrite( $handle, $output ) === FALSE )
        {
            trigger_error( 'Cannot write to sql export file.', E_USER_ERROR );
        }

        unset( $output );

        //$GLOBALS['memory_usage'][$table] = Library::humanSize(memory_get_peak_usage());
    }

}
