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
 * @file        Pdo.php
 *
 */
class pdoDbException extends PDOException
{

    /**
     * @param PDOException $e
     */
    public function __construct( PDOException $e )
    {
        if ( strstr( $e->getMessage(), 'SQLSTATE[' ) )
        {
            preg_match( '/SQLSTATE\[(\w+)\] \[(\w+)\] (.*)/', $e->getMessage(), $matches );
            $this->code = ($matches[ 1 ] == 'HT000' ? $matches[ 2 ] : $matches[ 1 ]);
            $this->message = $matches[ 3 ];

            Error::raise( $this->message );
        }
    }

}

/**
 * Class Database_Adapter_Pdo
 */
class Database_Adapter_Pdo extends Database_Adapter_Abstract
{

    /**
     * PDO Fetch modes
     *
     * @var type
     */
    public $fetchModes = array(
        'num'    => PDO::FETCH_NUM,
        'assoc'  => PDO::FETCH_ASSOC,
        'object' => PDO::FETCH_OBJ,
        'both'   => PDO::FETCH_BOTH
    );

    /**
     * @var string
     */
    protected $_escape_char = '`';

    /**
     * @var string
     */
    protected $bind_marker = '?';

    /**
     * @var string
     */
    protected $fetchMode = 'assoc';

    /**
     * @var
     */
    protected $query;

    /**
     * @var
     */
    protected $result;

    /**
     * @var
     */
    protected $logFile;

    /**
     * @var
     */
    protected $fields;

    /**
     * @var
     */
    protected $fieldNames;

    /**
     * @var
     */
    protected $schemaNameField;

    /**
     * @var
     */
    protected $schemaTypeField;

    /**
     * @var bool
     */
    protected $addQuotes = true;

    /**
     * @var bool
     */
    public $enableProfiler = true;

    /**
     *
     * @var type
     */
    public $_connection = null;

    // handle to Database connection
    /**
     * @var null
     */
    public $_result = null;

    // handle to Database connection
    /**
     *
     * @var PDOStatement
     */
    public $_statement = null;

    /**
     *
     * @var type
     */
    protected $_db = null;

    /**
     * @var null
     */
    protected $_pdoRecord = null;

    /**
     * @var null
     */
    protected static $error = null;

    /**
     *
     * @param array $config
     * @return Database_Adapter_Pdo
     */
    public function __construct( array $config )
    {

        parent::__construct( $config );


        return $this->prepareAdapter();
    }

    /**
     *
     * @throws BaseException
     * @return Database_Adapter_Pdo
     */
    protected function prepareAdapter()
    {
        $config = $this->_config[ 'connection' ];

        $this->enableProfiler = isset( $this->_config[ 'profiling' ] ) ? $this->_config[ 'profiling' ] : false;

        if ( empty( $config[ 'dsn' ] ) )
        {
            throw new BaseException( 'Database Connect Informations not valid. Please check the DSN information for the PDO database driver' );
        }

        $dsn = ucfirst( strtolower( $config[ 'dsn' ] ) );

        $this->_databaseName = !empty( $config[ 'dbname' ] ) ? $config[ 'dbname' ] : null;
        $this->_username = !empty( $config[ 'username' ] ) ? $config[ 'username' ] : null;
        $this->_password = !empty( $config[ 'password' ] ) ? $config[ 'password' ] : null;
        $this->_hostname = !empty( $config[ 'host' ] ) ? $config[ 'host' ] : null;
        $this->_port = !empty( $config[ 'port' ] ) ? $config[ 'port' ] : null;
        $this->_tablePrefix = !empty( $this->_config[ 'prefix' ] ) ? $this->_config[ 'prefix' ] : null;
        $this->_charset = !empty( $this->_config[ 'charset' ] ) ? str_replace( array(
                    '"',
                    "'" ), '', $this->_config[ 'charset' ] ) : null;


        $className = 'Database_Adapter_Pdo_' . $dsn;
        $this->_adapterInstance = new $className( $this );
        $this->setAdapter( $dsn );
        $this->connect();

        #      $this->_config        = null;
        #       $this->_adapterConfig = null;


        if (!empty($this->_charset)) {
            //$this->_connection->exec( 'SET NAMES ' . $this->_charset );
        }
        else {
            //$this->_connection->exec( 'SET NAMES utf8');
        }



        return $this;
    }

    /**
     *
     */
    public function connect()
    {
        $this->_connection = $this->_adapterInstance->connect();

        if ( $this->_connection )
        {
            $this->_adapterInstance->selectDb( $this->_databaseName );
        }
        else
        {
            trigger_error( $this->_connection->getError(), E_USER_ERROR );
        }
        return $this->_connection;
    }

    /**
     * @return null
     */
    public function getError()
    {
        return self::$error;
    }

    /**
     *
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->_databaseName;
    }

    /**
     *
     * @return string
     */
    public function getHostname()
    {
        return $this->_hostname;
    }

    /**
     *
     * @return string
     */
    public function getPort()
    {
        return $this->_port;
    }

    /**
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->_username;
    }

    /**
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->_password;
    }

    /**
     * returns the table prfix
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->_tablePrefix;
    }

    /**
     * returns the database charset
     *
     * @return string
     */
    public function getCharset()
    {
        return $this->_charset;
    }

    /**
     *
     * @return string
     */
    public function getPreparedDatabaseName()
    {
        return $this->prepareDatabaseName( $this->_databaseName );
    }

    /**
     * returns the first selected row 
     * 
     * @param string $sql
     * @return array
     */
    public function queryFirst( $sql )
    {
        return $this->query_first( $sql );
    }

    /**
     * returns the first selected row. It will prepare your sql query with LIMI 1 if not exists
     * 
     * @internal param string $sql
     * @throws BaseException
     * @return array
     */
    public function query_first()
    {
        $this->_startTimer = Debug::getMicroTime();



        if ( !$this->_connection )
        {
            throw new BaseException( 'Database Connect Informations not valid. Please check the DSN information for the PDO database driver' );
        }

        $_startTimer = ($this->_startTimer ? $this->_startTimer : Debug::getMicroTime());
        $this->_startTimer = null;

	    $args = func_get_args();
	    $query = array_shift( $args );


        $query = preg_replace( '/LIMIT\n*\t*\s*\s+?\d*\n*\t*\s*$/isU', '', trim( (string) $query ) );
        $query .= ' LIMIT 1';

        $sql = $this->prepareTablePrefix( $query );
        /**
         *
         */
        $this->_statement = $this->_connection->prepare( $sql );
        // $this->_statement->queryString;
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
            // self::$error = $e->getMessage();
            $err = $this->_statement->errorInfo();
            self::$error = $err;
            throw new BaseException(
            $e->getMessage() . '<p>SQL: <br/><pre>' . $this->_statement->queryString . '</pre><p>Args: <br/><pre>' . print_r( $args, true ) . '</pre>', 'SQL', $sql );
        }


        if ( $this->_statement->errorCode() !== '00000' )
        {
            $err = $this->_statement->errorInfo();
            self::$error = $err;

            throw new BaseException( 'PDO error: ' . $err[ 2 ] . '<p>SQL: <br/><pre>' . $this->_statement->queryString . '</pre><p>Args: <br/><pre>' . print_r( $args, true ) . '</pre>', 'SQL', $sql );
        }


        // Debug informations
        self::$_QueryTime = Debug::getMicroTime() - $_startTimer;
        self::$_TotalQueryTime += self::$_QueryTime;

        #if (substr(strtolower(trim($query)), 0, 6) === 'select')
        #{
        self::$_TotalQuerys++;
        # }

        $this->query_count++;


        $this->_params = & $args;
        $this->_pdoRecord = new Database_Adapter_Pdo_RecordSet( $this, $args );

        if ( $this->enableProfiler && (!defined( 'SKIP_DEBUG' ) || (defined( 'SKIP_DEBUG' ) && SKIP_DEBUG !== true)) )
        {
            if ( $query !== 'SELECT found_rows() AS rows' )
            {

                $log = array();
                $log[ 'query' ] = $sql . "\n    [arguments: " . implode( ', ', (is_array( $args ) && !empty( $args ) ? $args : array(
                                    'none' ) ) ) . ']';
                $log[ 'duration' ] = Debug::getReadableTime( self::$_QueryTime );
                $log[ 'raw' ] = self::$_QueryTime;


                $trace = array();
                $trace = debug_backtrace( false );

                if ( $trace[ 0 ][ 'class' ] == __CLASS__ )
                {
                    array_shift( $trace );
                }

                $index = 0;
                $_className = $trace[ 0 ][ 'class' ];
                $_functionName = $trace[ 0 ][ 'function' ];

                if ( $trace[ 0 ][ 'class' ] == 'Database_Query' )
                {
                    $_className = $trace[ 1 ][ 'class' ];
                    $_functionName = $trace[ 1 ][ 'function' ];
                }

                if ( !$_className && isset( $trace[ 1 ][ 'class' ] ) )
                {
                    $_className = $trace[ 1 ][ 'class' ];
                    $_functionName = $trace[ 1 ][ 'function' ];
                    $index++;
                }

                $_trace = $trace[ 0 ];

                if ( !isset( $trace[ 0 ][ 'line' ] ) )
                {
                    $_trace = $trace[ 1 ];
                }

                $log[ 'caller' ] = array(
                    'file'  => (!empty( $_trace[ 'file' ] ) ? str_replace( ROOT_PATH, '', Library::formatPath( $_trace[ 'file' ] ) ) : 'unknown'),
                    'line'  => (!empty( $_trace[ 'line' ] ) ? $_trace[ 'line' ] : 'unknown'),
                    'fnc'   => (!empty( $_functionName ) ? $_functionName : 'unknown'),
                    'class' => (!empty( $_className ) ? $_className : 'unknown'),
                );

                unset($trace);

                Debug::store( 'SQL: ' . self::$_TotalQuerys, $log[ 'caller' ][ 'file' ] . ' (' . ($log[ 'caller' ][ 'class' ] !== 'unknown' ? $log[ 'caller' ][ 'class' ] . '::' : '') . $log[ 'caller' ][ 'fnc' ] . ') @Line: ' . $log[ 'caller' ][ 'line' ], false, $query );

                self::$querylog[] = $log;


                $trace = null;
            }
        }


        return $this->_pdoRecord->fetch();
    }

    /**
     * @return bool
     * @throws BaseException
     */
    private function exec() {
		$args = func_get_args();
		$query = array_shift( $args );

		$sql = $this->prepareTablePrefix( $query );
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

                if ( is_string($argument) ) {
                   //if (mb_detect_encoding($argument) != "UTF-8") {
                    //    $argument = Strings::mbConvertTo( $argument, 'UTF-8' );
                    //}

                    //$argument = preg_replace ("/\x{00A0}/", " ", $argument);
                }

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
			$err = $this->_statement->errorInfo();
			self::$error = $err;

			throw new BaseException(
				$e->getMessage() . '<p>SQL: <br/><pre>' . $this->_statement->queryString . '</pre><p>Args: <br/><pre>' . print_r( $args, true ) . '</pre>', 'SQL', $sql );
		}

		if ( $this->_statement->errorCode() !== '00000' )
		{
			$err = $this->_statement->errorInfo();
			self::$error = $err;

			throw new BaseException( 'PDO error: ' . $err[ 2 ] . '<p>SQL: <br/><pre>' . $this->_statement->queryString . '</pre><p>Args: <br/><pre>' . print_r( $args, true ) . '</pre>', 'SQL', $sql );
		}

		return true;
	}

    /**
     * Execute a giving SQL Query. 
     * 
     * @param string $sql
     * @throws BaseException
     * @return Database_Adapter_Pdo_RecordSet
     */
    public function query( $sql )
    {

        if ( !$this->_connection )
        {
            die( 'Database Connect Informations not valid. Please check the DSN information for the PDO database driver' );
        }

	    $args = func_get_args();


	    if(strncmp($sql, 'INSERT', strlen('INSERT')) === 0 || strncmp($sql, 'UPDATE', strlen('UPDATE')) === 0 || strncmp($sql, 'REPLACE', strlen('REPLACE')) === 0 || strncmp($sql, 'DELETE', strlen('DELETE')) === 0)
	    {
		    return call_user_func_array(array($this, 'exec'), $args );
	    }




        $_startTimer = ($this->_startTimer ? $this->_startTimer : Debug::getMicroTime());
        $this->_startTimer = null;


        $query = array_shift( $args );


        $sql = $this->prepareTablePrefix( $query );
        $this->_statement = $this->_connection->prepare( $sql );
        // $this->_statement->queryString;
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
            // self::$error = $e->getMessage();
            $err = $this->_statement->errorInfo();
            self::$error = $err;

            throw new BaseException(
            $e->getMessage() . '<p>SQL: <br/><pre>' . $this->_statement->queryString . '</pre><p>Args: <br/><pre>' . print_r( $args, true ) . '</pre>', 'SQL', $sql );
        }


        if ( $this->_statement->errorCode() !== '00000' )
        {
            $err = $this->_statement->errorInfo();
            self::$error = $err;

            throw new BaseException( 'PDO error: ' . $err[ 2 ] . '<p>SQL: <br/><pre>' . $this->_statement->queryString . '</pre><p>Args: <br/><pre>' . print_r( $args, true ) . '</pre>', 'SQL', $sql );
        }


        // Debug informations
        self::$_QueryTime = Debug::getMicroTime() - $_startTimer;
        self::$_TotalQueryTime += self::$_QueryTime;

        #if (substr(strtolower(trim($query)), 0, 6) === 'select')
        #{
        self::$_TotalQuerys++;
        # }

        $this->query_count++;


        $this->_params = & $args;
        $this->_pdoRecord = new Database_Adapter_Pdo_RecordSet( $this, $args );

        if ( $this->enableProfiler && (!defined( 'SKIP_DEBUG' ) || (defined( 'SKIP_DEBUG' ) && SKIP_DEBUG !== true)) )
        {
            if ( $query !== 'SELECT found_rows() AS rows' )
            {

                $log = array();
                $log[ 'query' ] = $sql . "\n    [arguments: " . implode( ', ', (is_array( $args ) && !empty( $args ) ? $args : array(
                                    'none' ) ) ) . ']';
                $log[ 'duration' ] = Debug::getReadableTime( self::$_QueryTime );
                $log[ 'raw' ] = self::$_QueryTime;


                $trace = debug_backtrace( false );

                if ( $trace[ 0 ][ 'class' ] == __CLASS__ )
                {
                    array_shift( $trace );
                }

                $index = 0;
                $_className = $trace[ 0 ][ 'class' ];
                $_functionName = $trace[ 0 ][ 'function' ];

                if ( $trace[ 0 ][ 'class' ] == 'Database_Query' )
                {
                    $_className = $trace[ 1 ][ 'class' ];
                    $_functionName = $trace[ 1 ][ 'function' ];
                }

                if ( !$_className && isset( $trace[ 1 ][ 'class' ] ) )
                {
                    $_className = $trace[ 1 ][ 'class' ];
                    $_functionName = $trace[ 1 ][ 'function' ];
                    $index++;
                }

                if ( !$_className && isset( $trace[ 2 ][ 'class' ] ) )
                {
                    #$_className    = $trace[ 2 ][ 'class' ];
                    # $_functionName = $trace[ 2 ][ 'function' ];
                    #$index++;
                }

                $_trace = $trace[ 0 ];

                if ( !isset( $trace[ 0 ][ 'line' ] ) )
                {
                    $_trace = $trace[ 1 ];
                }

                $log[ 'caller' ] = array(
                    'file'  => (!empty( $_trace[ 'file' ] ) ? str_replace( ROOT_PATH, '', Library::formatPath( $_trace[ 'file' ] ) ) : 'unknown'),
                    'line'  => (!empty( $_trace[ 'line' ] ) ? $_trace[ 'line' ] : 'unknown'),
                    'fnc'   => (!empty( $_functionName ) ? $_functionName : 'unknown'),
                    'class' => (!empty( $_className ) ? $_className : 'unknown'),
                );

                Debug::store( 'SQL: ' . self::$_TotalQuerys, $log[ 'caller' ][ 'file' ] . ' (' . ($log[ 'caller' ][ 'class' ] !== 'unknown' ? $log[ 'caller' ][ 'class' ] . '::' : '') . $log[ 'caller' ][ 'fnc' ] . ') @Line: ' . $log[ 'caller' ][ 'line' ], false, $query );

                self::$querylog[] = $log;


	            unset($trace, $_trace);
            }
        }


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

    /**
     * @param null $mode
     * @return mixed
     */
    public function fetch( $mode = null )
    {
        return $this->_pdoRecord->fetch( $mode );
    }

    /**
     * @param null $mode
     * @return mixed
     */
    public function fetchAll( $mode = null )
    {
        $tmp = $this->_pdoRecord->fetchAll( $mode );

        $this->free();

        return $tmp;
    }

    public function current()
    {
        return $this->_pdoRecord->current();
    }

    public function key()
    {
        return $this->_pdoRecord->key();
    }

    public function next()
    {
        return $this->_pdoRecord->next();
    }

    public function rewind()
    {
        return $this->_pdoRecord->rewind();
    }

    public function valid()
    {
        return $this->_pdoRecord->valid();
    }

    public function count()
    {
        return $this->rowCount();
    }

    public function num_rows()
    {
        return $this->rowCount();
    }

    public function rowCount()
    {
        return $this->_pdoRecord->rowCount();
    }

    public function close()
    {
        return $this->_pdoRecord->__destruct();
    }

    /**
     * @param $offset
     * @param $value
     * @return mixed
     */
    public function offsetSet( $offset, $value )
    {
        return $this->_pdoRecord->offsetSet( $offset, $value );
    }

    /**
     * @param $offset
     * @return mixed
     */
    public function offsetExists( $offset )
    {
        return $this->_pdoRecord->offsetExists( $offset );
    }

    /**
     * @param $offset
     * @return mixed
     */
    public function offsetUnset( $offset )
    {
        return $this->_pdoRecord->offsetUnset( $offset );
    }

    /**
     * @param $offset
     * @return mixed
     */
    public function offsetGet( $offset )
    {
        return $this->_pdoRecord->offsetGet( $offset );
    }

    /**
     * @param array $columns
     * @return mixed
     */
    public function getColumnMeta( array $columns )
    {
        return $this->_pdoRecord->getColumnMeta( $columns );
    }

    /**
     *
     */
    public function begin()
    {
        if ( !$this->_connection )
            $this->connect();

        try
        {
            $this->_connection->beginTransaction();
        }
        catch ( PDOException $e )
        {
            Error::raise( 'PDO Exception: ' . $e->getMessage() );
        }
    }

    /**
     *
     */
    public function commit()
    {
        if ( !$this->_connection )
            $this->connect();
        try
        {
            $this->_connection->commit();
        }
        catch ( PDOException $e )
        {
            Error::raise( 'PDO Exception: ' . $e->getMessage() );
        }
    }

    /**
     *
     */
    public function rollback()
    {
        if ( !$this->_connection )
            $this->connect();
        try
        {
            $this->_connection->rollBack();
        }
        catch ( PDOException $e )
        {
            Error::raise( 'PDO Exception: ' . $e->getMessage() );
        }
    }

    /**
     *
     * @param string $str
     * @return string
     */
    public function escape( $str = '' )
    {
        return $this->quote( $str );
    }

    /**
     *
     * @param string $str
     * @return string
     */
    public function esc( $str = '' )
    {
        return $this->quote( $str );
    }

    /**
     *
     * @param string $value
     * @internal param string $str
     * @return string
     */
    public function quote( $value = '' )
    {
        if ( $value === NULL )
        {
            return 'NULL';
        }
        elseif ( $value === TRUE OR $value === FALSE )
        {
            return $value ? 'TRUE' : 'FALSE';
        }
        elseif ( is_int( $value ) )
        {
            return (int) $value;
        }
        elseif ( is_array( $value ) )
        {
            return '(' . implode( ', ', array_map( array(
                        $this,
                        __FUNCTION__ ), $value ) ) . ')';
        }

        return $this->_connection->quote( $value );
    }

    /**
     *
     * @return integer
     */
    public function insert_id()
    {
        return $this->_connection->lastInsertId();
    }

    public function last_insert_id()
    {
        return $this->_connection->lastInsertId();
    }

    /**
     * @param $table
     * @return mixed
     */
    public function getNextInsertId( $table )
    {
        $r = $this->getTableState( $table );
        return $r[ 'Auto_increment' ];
    }

    /**
     * @param $table
     * @return mixed
     */
    protected function _schema( $table )
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
    protected function _list_columns( $table = '' )
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
    public function _list_tables( $prefix_limit = FALSE )
    {
        $sql = "SHOW TABLES FROM " . $this->prepareDatabaseName( $this->_databaseName );

        if ( $prefix_limit !== FALSE AND $this->getPrefix() != '' )
        {
            $sql .= " LIKE '" . $this->getPrefix() . "%'";
        }

        return $sql;
    }

    public function _list_databases()
    {
        
    }

    /**
     * Escape the SQL Identifiers
     *
     * This function escapes column and table names
     *
     * @access    private
     * @param    $item
     * @param    string
     * @return    string
     */
    public function _escape_identifiers( $item, $escapeTablename = null )
    {
        if ( $this->_escape_char == '' || $item === '*' )
        {
            return $item;
        }


        if ( is_array( $item ) )
        {
            return implode( ' AS ', array_map( array(
                $this,
                __FUNCTION__ ), $item ) );
        }
        elseif ( is_int( $item ) )
        {
            return (int) $item;
        }


        if ( $escapeTablename || (substr( $item, 0, 4 ) === '%tp%' && strlen( $item ) > 4) )
        {
            $item = $this->tp() . substr( $item, 4 );
        }

        foreach ( $this->_reserved_identifiers as $id )
        {
            if ( strpos( $item, '.' . $id ) !== FALSE )
            {
                $str = $this->_escape_char . str_replace( '.', $this->_escape_char . '.', $item );

                // remove duplicates if the user already included the escape
                return preg_replace( '/[' . $this->_escape_char . ']+/', $this->_escape_char, $str );
            }
        }

        if ( strpos( $item, '.' ) !== FALSE )
        {
            $str = $this->_escape_char . str_replace( '.', $this->_escape_char . '.' . $this->_escape_char, $item ) . $this->_escape_char;
        }
        else
        {
            $str = $this->_escape_char . $item . $this->_escape_char;
        }

        // remove duplicates if the user already included the escape
        return preg_replace( '/[' . $this->_escape_char . ']+/', $this->_escape_char, $str );
    }

    /**
     * Version number query string
     * @return    string
     */
    public function version()
    {
        return $this->_adapterInstance->version();
    }

    /**
     *
     * @param array $data
     * @param string $table
     * @return \Database_Adapter_Pdo_RecordSet
     */
    public function insert0( array $data, $table = null )
    {
        $field_names = '';
        $field_values = '';

        try
        {
            foreach ( $data as $k => $v )
            {
                if ( !is_string( $k ) )
                {
                    continue;
                }

                $field_names .= '`' . $k . '`,';

                if ( is_array( $v ) || is_object( $v ) )
                {
                    die( 'Invalid Database insert! Key: ' . $k . ' Value: ' . print_r( $v, true ) );
                }

                $field_values .= $this->escape( $v ) . ',';
            }

            if ( $field_names !== '' )
            {
                $field_names = substr( $field_names, 0, -1 );
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

        return $this->query( 'INSERT INTO ' . $table . ' ' . $field_names . ' VALUES(' . $field_values . ')' );
    }

    /**
     *
     * @param array  $data
     * @param string $table
     * @param null   $where
     * @return \Database_Adapter_Pdo_RecordSet
     */
    public function update0( array $data, $table, $where = null )
    {
        $sql = array();
        foreach ( $data as $k => $v )
        {
            if ( !is_string( $k ) )
            {
                continue;
            }

            if ( is_array( $v ) || is_object( $v ) )
            {
                die( 'Invalid Database insert! Key: ' . $k . ' Value: ' . print_r( $v, true ) );
            }

            $sql[] = '`' . $k . '` = ' . $this->escape( $v );
        }


        if ( $where )
        {
            
        }


        return $this->query( 'UPDATE ' . $table . ' SET ' . implode( ', ', $sql ) );
    }

    /* ======================================================================== */

    // Create an array from a multidimensional array returning formatted
    // strings ready to use in an INSERT query, saves having to manually format
    // the (INSERT INTO table) ('field', 'field', 'field') VALUES ('val', 'val')
    /* ======================================================================== */

    /**
     * @param      $data
     * @param bool $tablename
     * @param bool $ignore
     * @return array|string
     */
    public function compile_db_insert_string( $data, $tablename = false, $ignore = false )
    {
        $field_names = '';
        $field_values = '';
        try
        {
            #    print_r($data);
            foreach ( $data as $k => $v )
            {
                if ( !is_string( $k ) )
                {
                    continue;
                }


                $field_names .= '`' . $k . '`,';

                if ( is_array( $v ) || is_object( $v ) )
                {
                    die( 'Invalid Database insert! Key: ' . $k . ' Value: ' . print_r( $v, true ) );
                }
#echo $k .' => ' .gettype($v).'<br/>';
                $field_values .= $this->escape( $v ) . ',';
            }

            if ( $field_names !== '' )
            {
                $field_names = substr( $field_names, 0, -1 );
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
            'FIELD_VALUES' => $field_values );
    }

    /* ======================================================================== */

    // Create an array from a multidimensional array returning a formatted
    // string ready to use in an UPDATE query, saves having to manually format
    // the FIELD='val', FIELD='val', FIELD='val'
    /* ======================================================================== */

    /**
     * @param      $data
     * @param bool $tablename
     * @return mixed|string
     */
    public function compile_db_update_string( $data, $tablename = false )
    {
        $return_string = "";
        foreach ( $data as $k => $v )
        {

            $return_string .= '`' . $k . '` = ' . $this->escape( $v ) . ',';
        }
        $return_string = preg_replace( '/,$/', '', $return_string );
        if ( $tablename !== false )
        {
            return 'UPDATE ' . $tablename . ' SET ' . $return_string; // Where Clausel muss anschließend gebildet werden
        }

        return $return_string;
    }

    /**
     * Generates the WHERE portion of the query. Separates
     * multiple calls with AND
     *
     * @param      $key
     * @param      string
     * @param bool $escape
     * @internal param $mixed
     * @return    object
     */
    function Where( $key, $value = NULL, $escape = TRUE )
    {
        $prefix = (count( $this->ar_where ) === 0) ? '' : 'AND ';
        $k = $this->_protect_identifiers( $key, FALSE, $escape );
        $this->ar_where[] = $prefix . $k . '=' . $this->escape( $value );

        return $this;
    }

    /**
     * Generates the WHERE portion of the query. Separates
     * multiple calls with OR
     *
     * @param string $key
     * @param string $value
     * @param bool $escape
     * @internal param $mixed
     * @return    object
     */
    function orWhere( $key, $value = NULL, $escape = TRUE )
    {
        $prefix = (count( $this->ar_where ) === 0) ? '' : 'OR ';
        $k = $this->_protect_identifiers( $key, FALSE, $escape );
        $this->ar_where[] = $prefix . $k . '=' . $this->escape( $value );

        return $this;
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
    public function tableInfo( $table )
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

                $idx[ $type ][ $keyname ][ 'fields' ][] = $r[ 'Column_name' ];
            }
            else
            {
                $idx[ $type ][] = $r[ 'Column_name' ];
            }
        }


        $crlf = "|@|";

        $create = $this->query( "SHOW CREATE TABLE " . $table )->fetch();
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

                $first = TRUE;
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

                        $constraints[] = $constraint;

                        $first = FALSE;
                    }
                    else
                    {
                        break;
                    }
                }
            }
        }


        $columns = $this->query( "SHOW COLUMNS FROM " . $table )->fetchAll( 'assoc' );
        $_fieldMeta = $this->getColumnMeta( $columns );

        $ret = array();
        $ret[ 'fields' ] = $_fieldMeta;
        $ret[ 'indexes' ] = $idx;
        if ( count( $constraints ) )
        {
            $ret[ 'constraints' ] = $constraints;
        }

        $tableinfo = $this->getTableState( $table );


        $ret[ 'table' ][ 'name' ] = $table;
        $ret[ 'table' ][ 'comment' ] = $tableinfo[ 'Comment' ];
        $ret[ 'table' ][ 'collation' ] = $tableinfo[ 'Collation' ];
        $ret[ 'table' ][ 'engine' ] = $tableinfo[ 'Engine' ];
        return $ret;
    }

    /**
     *
     * @param        $table
     * @param string $fieldname
     * @return bool
     */
    public function fieldExists( $table, $fieldname )
    {
        $table = preg_replace( '/^%tp%/', $this->tp(), trim( $table ) );

        $columns = $this->query( "SHOW COLUMNS FROM " . $table )->fetchAll( 'assoc' );
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

}

?>