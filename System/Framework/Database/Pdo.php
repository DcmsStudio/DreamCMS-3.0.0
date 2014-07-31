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
 * @file         Pdo.php
 */
class Database_Pdo extends Database_Adapter_Abstract
{

    /**
     * @param array $configIn
     */
    public function __construct(array $configIn)
    {
        parent::__construct( $configIn );


        $config = $this->_config;

        // sql type
        $adapter = null;
        if ( isset( $config[ 'connection' ][ 'dsn' ] ) && !empty( $config[ 'connection' ][ 'dsn' ] ) )
        {
            $adapter = $config[ 'connection' ][ 'dsn' ];
        }

        $this->_adapter = strtoupper( $adapter );
        $this->setAdapter( $this->_adapter );


        $connection          = $config[ 'connection' ];
        $this->_databaseName = !empty( $connection[ 'dbname' ] ) ? $connection[ 'dbname' ] : null;
        $this->_username     = !empty( $connection[ 'username' ] ) ? $connection[ 'username' ] : null;
        $this->_password     = !empty( $connection[ 'password' ] ) ? $connection[ 'password' ] : null;
        $this->_hostname     = !empty( $connection[ 'host' ] ) ? $connection[ 'host' ] : null;
        $this->_port         = !empty( $connection[ 'port' ] ) ? $connection[ 'port' ] : null;
        $this->_tablePrefix  = !empty( $config[ 'prefix' ] ) ? $config[ 'prefix' ] : null;
        $this->_charset      = !empty( $config[ 'charset' ] ) ? str_replace( array(
            '"',
            "'"), '', $config[ 'charset' ] ) : null;

        $this->getConnect();
    }

    public function __destruct()
    {
        $this->_pdoRecord = null;
        $this->_statement = null;
    }


    public function __clone()
    {

    }

    /**
     * @return string
     */
    private function getHash()
    {
        return md5( $this->_hostname . ( isset( $this->_port ) && !empty( $this->_port ) ? ';port=' . $this->_port : ';port=' ) . $this->_databaseName . $this->_username . $this->_password );
    }

    private function getConnect()
    {

        $port = ( isset( $this->_port ) && !empty( $this->_port ) ? ';port=' . $this->_port : ';port=' );

        $initOptions = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING);
        $hash        = $this->getHash();
        $type        = strtolower( $this->_adapter );

        if ( !isset( self::$pdo[ $hash ] ) )
        {

            $limit = 10;
            $counter = 0;
            while (true) {

                try
                {
                    if ( $this->getAdapter() != 'SQLITE' )
                    {
                        self::$pdo[ $hash ] = new PDO( $type . ':host=' . $this->_hostname . $port . ';dbname=' . $this->_databaseName . '', $this->_username, $this->_password, $initOptions );
                        self::$pdo[ $hash ]->setAttribute( PDO::ATTR_PERSISTENT, true );
                    }
                    else
                    {
                        self::$pdo[ $hash ] = new PDO( 'sqlite:' . $this->_hostname, $initOptions );
                    }


                    self::$pdo[ $hash ]->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
                    self::$pdo[ $hash ]->setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC );

                    break;
                }
                catch ( PDOException $e )
                {
                    $counter++;
                    if ($counter === $limit) {
                        die( '<h1>Database Error</h1><h3>' . trans( 'Fehler beim Aufbau einer Datenbankverbindung' ) . '</h3><code>' . $e->getMessage() . '</code>' );
                    }

                }
            }
        }


        if ( !self::$pdo[ $hash ] instanceof PDO )
        {
            die( 'Invalid Database connect!' );
        }

        if ( self::$pdo[ $hash ] )
        {
            $this->_connection =& self::$pdo[ $hash ];
        }
        else
        {
            trigger_error( self::$pdo[ $hash ]->getError(), E_USER_ERROR );
        }

    }

    public function close()
    {
        $this->_connection = null;
    }


    /**
     * @return array
     */
    private function getTrace()
    {
        $trace = debug_backtrace( );
        $newtrace = $trace;
        foreach ( $trace as $name => $r )
        {
            if (isset( $r[ 'class' ] ) && ($r[ 'class' ] === __CLASS__ || $r[ 'class' ] === 'Database_Query' ||
                $r[ 'function' ] === 'call_user_func_array' || $r[ 'function' ] === 'catch_errors')
            )
            {
                array_shift($newtrace);
            }
        }
        $trace = null;
        return $newtrace;
    }


    /**
     * @param null $args
     */
    private function buildDebug($args = null)
    {
        $sql   = $this->_statement->queryString;
        $trace = $this->getTrace( );

        $trace = $trace[0];

        $log[ 'caller' ] = array(
            'file'  => ( !empty( $trace[ 'file' ] ) ? str_replace( ROOT_PATH, '', Library::formatPath( $trace[ 'file' ] ) ) : 'unknown' ),
            'line'  => ( !empty( $trace[ 'line' ] ) ? $trace[ 'line' ] : 'unknown' ),
            'fnc'   => ( !empty( $trace[ 'function' ] ) ? $trace[ 'function' ] : 'unknown' ),
            'class' => ( !empty( $trace[ 'class' ] ) ? $trace[ 'class' ] : 'unknown' ),
        );

        $log[ 'query' ]    = $sql . "\n    [arguments: " . implode( ', ', ( is_array( $args ) && !empty( $args ) ? $args : array('none') ) ) . ']';
        $log[ 'duration' ] = Debug::getReadableTime( self::$_QueryTime );
        $log[ 'raw' ]      = self::$_QueryTime;

        self::$querylog[ ] = $log;

        Debug::store( 'SQL: ' . self::$_TotalQuerys, $log[ 'caller' ][ 'file' ] . ' (' . ( $log[ 'caller' ][ 'class' ] !== 'unknown' ? $log[ 'caller' ][ 'class' ] . '::' : '' ) . $log[ 'caller' ][ 'fnc' ] . ') @Line: ' . $log[ 'caller' ][ 'line' ], false, $sql );

    }


    /**
     * @return bool
     * @throws BaseException
     * @throws DatabaseError
     */
    private function exec()
    {

        // Query Timer
        $_startTimer = Debug::getMicroTime();

        $args             = func_get_args();
        $query            = array_shift( $args );
        $sql              = $this->prepareTablePrefix( $query );
        $this->_statement = $this->_connection->prepare( $sql );

        if ( count( $args ) === 1 && is_array( $args[ 0 ] ) )
        {
            $args = $args[ 0 ];
        }

        if ( count( $args ) >= 1 && is_array( $args ) )
        {
            $i = 1;
            foreach ( $args as $argument )
            {
                $this->_statement->bindValue( $i, $argument );
                $i++;
            }
        }


        try
        {
            $this->affected = $this->_statement->execute();
        }
        catch ( PDOException $e )
        {
            // self::$error = $e->getMessage();
            $err         = $this->_statement->errorInfo();
            self::$error = $err;
            $trace = $this->getTrace();

            throw new DatabaseError( $e->getMessage(), $this->_statement->queryString, $args, $trace[0]['file'], $trace[0]['line'] );
        }

        if ( $this->_statement->errorCode() !== '00000' )
        {
            $err         = $this->_statement->errorInfo();
            self::$error = $err;

            throw new BaseException( 'PDO error: ' . $err[ 2 ] . '<p>SQL: <br/><pre>' . $this->_statement->queryString . '</pre><p>Args: <br/><pre>' . print_r( $args, true ) . '</pre>', 'SQL', $sql );
        }


        // Debug informations
        self::$_QueryTime = Debug::getMicroTime() - $_startTimer;
        self::$_TotalQueryTime += self::$_QueryTime;
        self::$_TotalQuerys++;

        $this->buildDebug($args);


        return true;
    }

    /**
     * returns the first selected row
     *
     * @param string $sql
     * @return array
     */
    public function queryFirst($sql)
    {
        return $this->query_first( $sql );
    }


    /**
     * returns the first selected row
     *
     * @throws BaseException
     * @throws DatabaseError
     * @return array
     */
    public function query_first()
    {
        if ( !$this->_connection )
        {
            throw new BaseException( 'Database Connect Informations not valid. Please check the DSN information for the PDO database driver' );
        }

        // Query Timer
        $_startTimer = Debug::getMicroTime();

        $args  = func_get_args();
        $query = array_shift( $args );
        $query = trim( $query );

        $query = preg_replace( '/LIMIT\n*\t*\s*\s+?\d*\n*\t*\s*$/isU', '', trim( (string)$query ) );
        $query .= ' LIMIT 1';


        $sql = $this->prepareTablePrefix( $query );


        // prepare statement
        $this->_statement = $this->_connection->prepare( $sql );

        // default fetch mode
        $this->_statement->setFetchMode( PDO::FETCH_ASSOC );

        if ( count( $args ) === 1 && is_array( $args[ 0 ] ) )
        {
            $args = $args[ 0 ];
        }

        if ( count( $args ) >= 1 && is_array( $args ) )
        {
            $i = 1;
            foreach ( $args as $argument )
            {
                $this->_statement->bindValue( $i, $argument );
                $i++;
            }
        }


        try
        {
            $this->_statement->execute();
        }
        catch ( PDOException $e )
        {
            self::$error = $this->_statement->errorInfo();

            $trace = $this->getTrace();

            throw new DatabaseError( $e->getMessage(), $this->_statement->queryString, $args, $trace[0]['file'], $trace[0]['line'] );
        }

        // Debug informations
        self::$_QueryTime = Debug::getMicroTime() - $_startTimer;
        self::$_TotalQueryTime += self::$_QueryTime;
        self::$_TotalQuerys++;

        $this->buildDebug($args);

        $this->_params    = & $args;
        $this->_pdoRecord = new Database_PdoRecordSet( $this, $args );


        return $this->_pdoRecord->fetch();
    }


    /**
     * @param string $sql
     * @return Database_PdoRecordSet
     * @throws DatabaseError
     */
    public function query($sql)
    {
        if ( !$this->_connection )
        {
            die( 'Database Connect Informations not valid. Please check the DSN information for the PDO database driver' );
        }

        $args = func_get_args();
        $sql  = trim( $sql );

        if ( $this->getAdapter() != 'SQLITE' )
        {
            if (
                strncmp( $sql, 'INSERT', 6 ) === 0 ||
                strncmp( $sql, 'UPDATE', 6 ) === 0 ||
                strncmp( $sql, 'REPLACE', 7 ) === 0 ||
                strncmp( $sql, 'DELETE', 6 ) === 0
            )
            {
                return call_user_func_array( array($this, 'exec'), $args );
            }
        }

        // Query Timer
        $_startTimer = Debug::getMicroTime();


        $query = array_shift( $args );
        $sql   = $this->prepareTablePrefix( $query );

        // prepare statement
        $this->_statement = $this->_connection->prepare( $sql );

        // default fetch mode
        $this->_statement->setFetchMode( PDO::FETCH_ASSOC );

        // convert array argument
        if ( count( $args ) === 1 && is_array( $args[ 0 ] ) )
        {
            $args = $args[ 0 ];
        }

        // add bind
        if ( count( $args ) >= 1 && is_array( $args ) )
        {
            $i = 1;
            foreach ( $args as $argument )
            {
                $this->_statement->bindValue( $i, $argument );
                $i++;
            }
        }

        try
        {
            $this->_statement->execute();
        }
        catch ( PDOException $e )
        {
            self::$error = $this->_statement->errorInfo();
            $trace = $this->getTrace();

            throw new DatabaseError( $e->getMessage(), $this->_statement->queryString, $args, $trace[0]['file'], $trace[0]['line'] );
        }

        // Debug informations
        self::$_QueryTime = Debug::getMicroTime() - $_startTimer;
        self::$_TotalQueryTime += self::$_QueryTime;
        self::$_TotalQuerys++;


        $this->buildDebug($args);


        $this->_params    = & $args;
        $this->_pdoRecord = new Database_PdoRecordSet( $this, $args );



        return $this->_pdoRecord;
    }


    /**
     *
     * @throws BaseException
     */
    public function refresh()
    {
        $this->_statement->execute( $this->_params );

        if ( $this->_statement->errorCode() !== '00000' )
        {
            throw new BaseException( $this->_statement->errorInfo() );
        }
    }

    public function free()
    {
        // $this->data_cache = array();
        $this->_pdoRecord = null;
        $this->_statement = null;
    }


    public function lock()
    {
        $this->_lock = true;
    }

    public function unlock()
    {
        if ( $this->_lock )
        {
            $this->_lock = false;

        }
    }


    /**
     * @return Database_Pdo
     */
    public function begin()
    {
        if ( !$this->_connection )
            $this->connect( $this->getHash() );

        try
        {
            $this->_connection->beginTransaction();
        }
        catch ( PDOException $e )
        {
            Error::raise( 'PDO Exception: ' . $e->getMessage() );
        }

        return $this;
    }

    /**
     * @return Database_Pdo
     */
    public function commit()
    {
        if ( !$this->_connection )
            $this->connect( $this->getHash() );
        try
        {
            $this->_connection->commit();
        }
        catch ( PDOException $e )
        {
            Error::raise( 'PDO Exception: ' . $e->getMessage() );
        }

        return $this;
    }

    /**
     * @return Database_Pdo
     */
    public function rollback()
    {
        if ( !$this->_connection )
            $this->connect( $this->getHash() );
        try
        {
            $this->_connection->rollBack();
        }
        catch ( PDOException $e )
        {
            Error::raise( 'PDO Exception: ' . $e->getMessage() );
        }

        return $this;
    }


    /**
     *
     * @param string $str
     * @return string
     */
    public function escape($str = '')
    {
        return $this->quote( $str );
    }

    /**
     *
     * @param string $str
     * @return string
     */
    public function esc($str = '')
    {
        return $this->quote( $str );
    }

    /**
     *
     * @param string $value
     * @internal param string $str
     * @return string
     */
    public function quote($value = '')
    {
        if ( $value === null || is_null( $value ) )
        {
            return 'NULL';
        }
        elseif ( is_bool( $value ) )
        {
            return $value ? 'TRUE' : 'FALSE';
        }
        elseif ( is_int( $value ) )
        {
            return (int)$value;
        }
        elseif ( is_array( $value ) )
        {
            return '(' . implode( ', ', array_map( array(
                $this,
                __FUNCTION__), $value ) ) . ')';
        }

        return $this->_connection->quote( $value );
    }

    /**
     *
     * @return integer
     */
    public function insert_id()
    {
        return $this->last_insert_id();
    }

    /**
     *
     * @return integer
     */
    public function last_insert_id()
    {
        return $this->_connection->lastInsertId();
    }

    /**
     * @param $table
     * @return mixed
     */
    public function getNextInsertId($table)
    {
        $r = $this->getTableState( $table );

        return $r[ 'Auto_increment' ];
    }

    /**
     * Version number query string
     * @return    string
     */
    public function version()
    {
        return "SELECT version() AS `version`";
    }

    /**
     * @param $table
     * @return mixed
     */
    protected function _schema($table)
    {
        $table = $this->prepareTablePrefix( $table );
        $this->execute( 'pragma table_info(' . $table . ')' );

        return $this->fetchAll();
    }

    /**
     *
     * @param string $table
     * @internal param $ <type> $table
     * @return string <type>
     */
    protected function _list_columns($table = '')
    {
        $table = $this->prepareTablePrefix( $table );

        return "SELECT * FROM INFORMATION_SCHEMA.Columns WHERE TABLE_NAME = '" . $table . "' AND TABLE_SCHEMA = '" . $this->_databaseName . "'";
    }

    /**
     * List table query
     * Generates a platform-specific query string so that the table names can be fetched
     *
     * @access    private
     * @param    boolean
     * @return    string
     */
    public function _list_tables($prefix_limit = false)
    {

        if ( $this->getAdapter() != 'SQLITE' )
        {
            $sql = "SHOW TABLES FROM " . $this->prepareDatabaseName( $this->_databaseName );
        }
        else
        {
            return "SELECT name AS TABLE_NAME FROM sqlite_master WHERE type='table'";
        }

        if ( $prefix_limit !== false AND $this->getPrefix() != '' )
        {
            $sql .= " LIKE '" . $this->getPrefix() . "%'";
        }

        return $sql;
    }

    /**
     * Returns information about a table or a result set
     *
     * @param $table
     * @internal                       param object|string $result DB_result object from a query or a
     *                                 string containing the name of a table.
     *                                 While this also accepts a query result
     *                                 resource identifier, this behavior is
     *                                 deprecated.
     * @internal                       param int $mode a valid tableInfo mode
     *
     * @return array  an associative array with the information requested.
     *                                 A DB_Error object on failure.
     *
     * @see                            DB_common::setOption()
     */
    public function tableInfo($table)
    {

        /*
         * Probably received a table name.
         * Create a result resource identifier.
         */

        $table = preg_replace( '/^%tp%/', $this->tp(), trim( $table ) );
        if ( empty( $table ) )
            return array();
        /*

          $id = $this->query("SELECT * FROM " . $table . " LIMIT 0");
          $res = array();
         */
        $indexes = $this->query( 'SHOW INDEX FROM ' . $table )->fetchAll();

        $idx = array();
        foreach ( $indexes as $r )
        {
            $type = null;

            if ( $r[ 'Key_name' ] === 'PRIMARY' )
            {
                $type = 'primary';
            }

            if ( $type === null && $r[ 'Non_unique' ] === 0 )
            {
                $type = 'unique';
            }

            if ( $r[ 'Index_type' ] === 'FULLTEXT' )
            {
                $type = 'fulltext';
            }

            if ( $type === null )
            {
                $type = 'key';
            }


            if ( $type !== 'primary' )
            {
                $keyname = $r[ 'Key_name' ];

                if ( !isset( $idx[ $type ] ) )
                {
                    $idx[ $type ] = array();
                }

                if ( !isset( $idx[ $type ][ $keyname ] ) )
                {
                    $idx[ $type ][ $keyname ][ 'fields' ] = array();
                }

                $idx[ $type ][ $keyname ][ 'fields' ][ ] = $r[ 'Column_name' ];
            }
            else
            {
                $idx[ $type ][ ] = $r[ 'Column_name' ];
            }
        }


        $crlf = "|@|";

        $create       = $this->query( "SHOW CREATE TABLE " . $table )->fetch();
        $create_query = $create[ 'Create Table' ];


        // Convert end of line chars to one that we want (note that MySQL doesn't return query it will accept in all cases)
        if ( strpos( $create_query, "(\r\n " ) )
        {
            $create_query = str_replace( "\r\n", $crlf, $create_query );
        }
        elseif ( strpos( $create_query, "(\n " ) )
        {
            $create_query = str_replace( "\n", $crlf, $create_query );
        }
        elseif ( strpos( $create_query, "(\r " ) )
        {
            $create_query = str_replace( "\r", $crlf, $create_query );
        }


        $constraints = array();

        if ( preg_match( '@CONSTRAINT|FOREIGN[\s]+KEY@', $create_query ) )
        {
            // Split the query into lines, so we can easily handle it. We know lines are separated by $crlf (done few lines above).
            $sql_lines = explode( $crlf, $create_query );
            $sql_count = count( $sql_lines );

            // lets find first line with constraints
            for ( $i = 0; $i < $sql_count; $i++ )
            {
                if ( preg_match( '@^[\s]*(CONSTRAINT|FOREIGN[\s]+KEY)@', $sql_lines[ $i ] ) )
                {
                    break;
                }
            }


            // If we really found a constraint
            if ( $i !== $sql_count )
            {

                // remove , from the end of create statement
                $sql_lines[ $i - 1 ] = preg_replace( '@,$@', '', $sql_lines[ $i - 1 ] );

                $first = true;
                for ( $j = $i; $j < $sql_count; ++$j )
                {
                    if ( preg_match( '@CONSTRAINT|FOREIGN[\s]+KEY@', $sql_lines[ $j ] ) )
                    {

                        $constraint = array();

                        preg_match( '/CONSTRAINT([\s])([\S]*)([\s])/', $sql_lines[ $j ], $match );
                        $constraint[ 'name' ] = substr( $match[ 2 ], 1, -1 );

                        preg_match( '/FOREIGN[\s]+KEY([\s])\(([\S]*)\)([\s])/', $sql_lines[ $j ], $match );
                        $constraint[ 'foreignkey' ] = substr( $match[ 2 ], 1, -1 );

                        preg_match( '/REFERENCES([\s])([\S]*)([\s])/', $sql_lines[ $j ], $match );
                        $constraint[ 'reftable' ] = substr( $match[ 2 ], 1, -1 );

                        preg_match( '/REFERENCES([\s])([\S]*)([\s])\(([\S]*)\)/', $sql_lines[ $j ], $match );
                        $constraint[ 'refkey' ] = substr( $match[ 4 ], 1, -1 );

                        preg_match( '/ON\s{1,}UPDATE([\s])([\S]*)([\s]*)/', $sql_lines[ $j ], $match );
                        $constraint[ 'update' ] = strtolower( $match[ 2 ] );

                        preg_match( '/ON\s{1,}DELETE([\s])([\S]*)([\s]*)/', $sql_lines[ $j ], $match );
                        $constraint[ 'delete' ] = strtolower( $match[ 2 ] );

                        $constraints[ ] = $constraint;

                        $first = false;
                    }
                    else
                    {
                        break;
                    }
                }
            }
        }


        $columns    = $this->query( "SHOW COLUMNS FROM " . $table )->fetchAll( 'assoc' );
        $_fieldMeta = $this->getColumnMeta( $columns );

        $ret              = array();
        $ret[ 'fields' ]  = $_fieldMeta;
        $ret[ 'indexes' ] = $idx;
        if ( count( $constraints ) )
        {
            $ret[ 'constraints' ] = $constraints;
        }

        $tableinfo = $this->getTableState( $table );


        $ret[ 'table' ][ 'name' ]      = $table;
        $ret[ 'table' ][ 'comment' ]   = $tableinfo[ 'Comment' ];
        $ret[ 'table' ][ 'collation' ] = $tableinfo[ 'Collation' ];
        $ret[ 'table' ][ 'engine' ]    = $tableinfo[ 'Engine' ];

        return $ret;
    }

    /**
     *
     * @param        $table
     * @param string $fieldname
     * @return bool
     */
    public function fieldExists($table, $fieldname)
    {

        if ( strpos( trim( $table ), '%tp%' ) === false )
        {
            $table = '%tp%' . trim( $table );
        }

        $table = preg_replace( '/^%tp%/', $this->tp(), trim( $table ) );

        $columns    = $this->query( "SHOW COLUMNS FROM " . $table )->fetchAll( 'assoc' );
        $_fieldMeta = $this->getColumnMeta( $columns );

        foreach ( $_fieldMeta as $name => $r )
        {
            if ( $fieldname === $name )
            {
                return true;
            }
        }

        return false;
    }


    /**
     * Escape the SQL Identifiers
     *
     * This function escapes column and table names
     *
     * @access    private
     * @param    mixed $item
     * @param    string
     * @return    string
     */
    public function _escape_identifiers($item, $escapeTablename = null)
    {
        if ( $this->_escape_char == '' || $item === '*' )
        {
            return $item;
        }


        if ( is_array( $item ) )
        {
            return implode( ' AS ', array_map( array(
                $this,
                __FUNCTION__), $item ) );
        }
        elseif ( is_int( $item ) )
        {
            return (int)$item;
        }

        if ($escapeTablename)
        {
            if (substr( $item, 0, 4 ) != '%tp%')
            {
                $item = $this->tp() . $item;
            }
            else
            {
                $item = $this->tp() . substr( $item, 4 );
            }
        }

        foreach ( $this->_reserved_identifiers as $id )
        {
            if ( strpos( $item, '.' . $id ) !== false )
            {
                $str = $this->_escape_char . str_replace( '.', $this->_escape_char . '.', $item );

                // remove duplicates if the user already included the escape
                return preg_replace( '/[' . preg_quote( $this->_escape_char, '/' ) . ']+/', $this->_escape_char, $str );
            }
        }

        if ( strpos( $item, '.' ) !== false )
        {
            $str = $this->_escape_char . str_replace( '.', $this->_escape_char . '.' . $this->_escape_char, $item ) . $this->_escape_char;
        }
        else
        {
            $str = $this->_escape_char . $item . $this->_escape_char;
        }



        // remove duplicates if the user already included the escape
        return preg_replace( '/[' . preg_quote( $this->_escape_char, '/' ) . ']+/', $this->_escape_char, $str );
    }

    /**
     * @param      $data
     * @param bool $tablename
     * @param bool $ignore
     * @return array|string
     */
    public function compile_db_insert_string($data, $tablename = false, $ignore = false)
    {
        $field_names  = '';
        $field_values = '';
        try
        {
            foreach ( $data as $k => $v )
            {
                if ( !is_string( $k ) )
                {
                    continue;
                }
                $field_names .= ($field_names ? ', ' : '') . $this->_escape_identifiers( $k );

                if ( is_array( $v ) || is_object( $v ) )
                {
                    die( 'Invalid Database insert! Key: ' . $k . ' Value: ' . print_r( $v, true ) );
                }

                $field_values .= $this->escape( $v ) . ',';
            }


            if ( $field_values !== '' )
            {
                $field_values = substr( $field_values, 0, -1 );
            }
        }
        catch ( Exception $e )
        {
            Error::raise( $e->getMessage(), 'PHP' );
        }

        if ( $tablename !== false )
        {
            $_ignore = '';
            if ( $ignore !== false )
            {
                $_ignore = 'IGNORE ';
            }

            return 'INSERT ' . $_ignore . 'INTO ' . $tablename . ' ' . $field_names . ' VALUES(' . $field_values . ')';
        }

        return array(
            'FIELD_NAMES'  => $field_names,
            'FIELD_VALUES' => $field_values);
    }

    /**
     * @param      $data
     * @param bool $tablename
     * @return mixed|string
     */
    public function compile_db_update_string($data, $tablename = false)
    {
        $return_string = "";
        foreach ( $data as $k => $v )
        {
            $return_string .= $this->_escape_identifiers( $k ) . ' = ' . $this->escape( $v ) . ',';
        }

        $return_string = preg_replace( '/,$/', '', $return_string );
        if ( $tablename !== false )
        {
            return 'UPDATE ' . $tablename . ' SET ' . $return_string; // Where Clausel muss anschließend gebildet werden
        }

        return $return_string;
    }


    /**
     * Automatically get column metadata
     * @param array $columns
     * @return array
     */
    public function getColumnMeta(array $columns)
    {
        // Clear any previous column/field info
        $this->_fields     = array();
        $this->_fieldMeta  = array();
        $this->_primaryKey = null;

        // Automatically retrieve column information if column info not specified
        if ( count( $this->_fields ) == 0 || count( $this->_fieldMeta ) == 0 )
        {
            // Fetch all columns and store in $this->fields

            foreach ( $columns as $key => $col )
            {
                // Insert into fields array
                $colname                   = $col[ 'Field' ];
                $this->_fields[ $colname ] = $col;
                if ( $col[ 'Key' ] == "PRI" )
                {
                    $this->_primaryKey[ ] = $colname;
                }


                $extras = explode( ' ', $col[ 'Extra' ] );
                foreach ( $extras as $idx => $x )
                {
                    if ( in_array( $x, $this->_flags ) )
                    {
                        $this->_fields[ $colname ][ str_replace( array(
                            '_',
                            ' '), '', $x ) ] = true;
                    }
                }


                // Set field types
                $colType = $this->parseColumnType( $col[ 'Type' ] );

                if ( $col[ 'Null' ] == 'NO' )
                {
                    $colType[ 'required' ] = true;
                }

                if ( $col[ 'Null' ] == 'NO' )
                {
                    $colType[ 'default' ] = $col[ 'Default' ];
                }
                else
                {
                    $colType[ 'defaultnull' ] = true;
                }


                $extras = explode( ' ', $col[ 'Extra' ] );
                foreach ( $extras as $idx => $x )
                {
                    if ( strtolower( $x ) == 'auto_increment' )
                    {
                        $colType[ 'autoincrement' ] = true;
                    }
                }

                $colType[ '_type' ] = $col[ 'Type' ];

                $this->_fieldMeta[ $colname ] = $colType;
            }
        }

        return $this->_fieldMeta;
    }


    /**
     * Parse PDO-produced column type
     * @param string $colType
     * @return array
     */
    public function parseColumnType($colType)
    {
        $colInfo  = array();
        $colParts = explode( " ", $colType );
        if ( ( $fparen = strpos( $colParts[ 0 ], "(" ) ) )
        {
            $colInfo[ 'type' ]       = substr( $colParts[ 0 ], 0, $fparen );
            $colInfo[ 'pdoType' ]    = '';
            $colInfo[ 'length' ]     = str_replace( ")", "", substr( $colParts[ 0 ], $fparen + 1 ) );
            $colInfo[ 'attributes' ] = isset( $colParts[ 1 ] ) ? $colParts[ 1 ] : null;
        }
        else
        {
            $colInfo[ 'type' ] = $colParts[ 0 ];
        }

        // PDO Bind types
        $pdoType = '';
        foreach ( $this->_pdoBindTypes as $pKey => $pType )
        {
            if ( strpos( ' ' . strtolower( $colInfo[ 'type' ] ) . ' ', $pKey ) )
            {
                $colInfo[ 'pdoType' ] = $pKey;
                break;
            }
            else
            {
                $colInfo[ 'pdoType' ] = PDO::PARAM_STR;
            }
        }

        return $colInfo;
    }

}