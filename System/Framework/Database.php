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
 * @package      DreamCMS
 * @version      3.0.0 Beta
 * @category     Framework
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Database.php
 */
class Database
{

    // Query types

    const SELECT = 1;

    const INSERT = 2;

    const UPDATE = 3;

    const DELETE = 4;

    const REPLACE = 5;

    protected static $_dbRegistry = array();

    protected static $config = null;

    /**
     * @param array $config
     */
    public static function setConfig( $config = array() )
    {

        if ( $config instanceof Config )
        {
            $config = $config->toArray();
        }


        // set configuration
        if ( self::$config === null )
        {
            self::$config = $config;
        }
    }

    /**
     * @param string $db
     * @return Database_Pdo
     */
    public static function getInstance( $db = 'default' )
    {

        if ( self::$config === null && !isset( self::$_dbRegistry[ $db ] ) )
        {
            die( 'The "' . $db . '" Database is not configured!' );
        }

        if ( !isset( self::$_dbRegistry[ $db ] ) )
        {
            return self::factory( self::$config, $db );
        }

        return self::$_dbRegistry[ $db ];
    }

    /**
     *
     * @param type $slaveDb all slave Databases
     */
    public static function syncDatabaseContents( $slaveDb )
    {

        $_masterDB = self::getInstance();


        $_slaveDB = self::getInstance( $slaveDb );
    }

    /**
     *
     * @param string $db
     * @return string
     * @throws BaseException
     * @return string
     * @internal param string $dbName ucfirst
     */
    public static function getAdapterName( $db = 'default' )
    {

        if ( self::$config === null )
        {
            die( 'The Database is not configured!' );
        }


        // multiple Database instructions
        if ( $db !== null && $db !== '' && !isset( self::$config[ $db ] ) )
        {
            die( 'The "' . $db . '" Database is not configured!' );
        }

        return ucfirst( strtolower( self::$config[ $db ][ 'adapter' ] ) );
    }

    /**
     *
     * @param array  $config
     * @param string $db
     * @throws BaseException
     * @return Database_Pdo
     */
    public static function &factory( $config = array(), $db = 'default' )
    {

        $time = microtime();
        $time = explode( ' ', $time );
        $_startTime = $time[ 0 ] + $time[ 1 ];


        if ( $config instanceof Config )
        {
            $config = $config->toArray();
        }

        if ( self::$config === null )
        {
            if ( !isset( $config[ 'default' ] ) )
            {
                die( 'The default Database is not configured!' );
            }

            if ( !isset( $config[ 'default' ][ 'adapter' ] ) )
            {
                die( 'The default Database Adapter is not configured!' );
            }
        }


        // multiple Database instructions
        if ( $db !== null && $db !== '' && !isset( $config[ $db ] ) )
        {
            die( 'The "' . $db . '" Database is not configured!' );
        }


        // set configuration
        if ( self::$config === null )
        {
            self::$config = $config;
        }

        $adapter = $config[ 'default' ][ 'adapter' ];
        $adapterConfig = $config[ 'default' ];


        if ( is_string( $db ) )
        {
            $adapter = $config[ $db ][ 'adapter' ];
            $adapterConfig = $config[ $db ];
        }


        /*
         * Verify that an adapter name has been specified.
         */
        if ( !is_string( $adapter ) || empty( $adapter ) )
        {
            /**
             * @see Database_Exception
             */
            die( sprintf( 'Adapter name must be specified in a string. "%s"', $db ) );
        }

        /*
         * Verify that the object created is a descendent of the abstract adapter type.
         */
        //$adapterName = 'Database_Adapter_' . ucfirst( strtolower( $adapter ) );


        switch ( strtolower( $adapter ) )
        {
            case 'pdo':
                #$adapterName = 'Database_Adapter_Pdo';
                #$dbAdapter = new Database_Adapter_Pdo( $adapterConfig );

                $adapterName = 'Database_Pdo';
                $dbAdapter = new Database_Pdo( $adapterConfig );



            break;

            case 'mysql':
                throw new BaseException( "The MySql Database Adapter is currently not available", 'SQL' );
               # $dbAdapter = new Database_Adapter_Mysql( $adapterConfig );
               # $adapterName = 'Database_Adapter_Mysql';
                break;

            case 'mysqli':
                throw new BaseException( "The MySqli Database Adapter is currently not available", 'SQL' );
                #$dbAdapter = new Database_Adapter_Mysql( $adapterConfig );
                #$adapterName = 'Database_Adapter_Mysql';
                break;

	        case 'sqlite':
		        //throw new BaseException( "The Sqlite Database Adapter is currently not available", 'SQL' );
		        $dbAdapter = new Database_Adapter_Sqlite( $adapterConfig );
		        $adapterName = 'Database_Adapter_Sqlite';
		        break;
            default:
                /**
                 * @see Database_Exception
                 */
                die( "Adapter class 'Database_Adapter_" . ucfirst( strtolower( $adapter ) ) . "' does not extend Database_Adapter_Abstract" );
                break;
        }

        if ( DEBUG )
        {
            Debug::store( '`' . $adapterName . '`', 'End Load... ' . str_replace( ROOT_PATH, '', Library::formatPath( __FILE__ ) ) . ' @Line: ' . (__LINE__ - 4), $_startTime );
        }

        self::$_dbRegistry[ $db ] = & $dbAdapter;

        return $dbAdapter;
    }

    /**
     * @return int
     */
    public static function getQueryCounter()
    {

        return self::getInstance()->query_count;
    }

    /**
     *
     * @param string $db
     * @return null|string
     */
    public static function getVersion( $db = null )
    {

        if ( false === ($sql = self::getInstance( $db )->version()) )
        {
            return null;
        }


        $config = self::$config[ $db ];


        // Some DBs have functions that return the version, and don't run special
        // SQL queries per se. In these instances, just return the result.
        $driver_version_exceptions = array(
            'oci8',
            'sqlite'
        );

        if ( in_array( $config[ 'connection' ][ 'dns' ], $driver_version_exceptions ) )
        {
            return '';
        }
        else
        {
            $query = self::getInstance( $db )->query( $sql )->fetch();

            return $query[ 'version' ];
        }
    }

    /**
     * @param null $db
     * @return array
     */
    public static function getDatabaseInfo( $db = null )
    {

        return array(
            'description' => self::getAdapterName( $db ),
            'version'     => self::getVersion( $db )
        );
    }

}


?>