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
 * @file        Sqlite.php
 *
 */
class Database_Adapter_Sqlite extends Database_Adapter_Abstract
{

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
    public $_hostname;

    /**
     * @var
     */
	public $_username;

    /**
     * @var
     */
	public $_password;

    /**
     * @var null
     */
	public $_port = null;

    /**
     *
     * @var array
     */
    protected $_config;

    /**
     *
     * @var Database_Adapter_Sqlite
     */
    protected $_db = null;

    /**
     *
     * @var type
     */
    protected $_pdoRecord = null;

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
     * @var null
     */
    public $_statement = null;

	public static $error = null;

    /**
     *
     * @param array $config
     * @throws BaseException
     * @return \Database_Adapter_Sqlite
     */
    public function __construct( array $config )
    {
        if ( !class_exists( 'PDO', false ) )
        {
            throw new BaseException( 'Please install the PHP extension "PDO" to connect sqlite databases!' );
        }

        parent::__construct( $config );

        $this->_config = $config;

        $this->prepareAdapter();

        return $this;
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
     *
     * @return Database_Adapter_Sqlite
     */
    protected function prepareAdapter()
    {
        $connect = $this->_config[ 'connection' ];


        $this->_databaseName = !empty( $connect[ 'dbname' ] ) ? $connect[ 'dbname' ] : null;
        $this->_username = !empty( $connect[ 'username' ] ) ? $connect[ 'username' ] : null;
        $this->_password = !empty( $connect[ 'password' ] ) ? $connect[ 'password' ] : null;
        $this->_hostname = !empty( $connect[ 'host' ] ) ? $connect[ 'host' ] : null;
        $this->_port = !empty( $connect[ 'port' ] ) ? $connect[ 'port' ] : null;

        $this->_tablePrefix = !empty( $this->_config[ 'prefix' ] ) ? $this->_config[ 'prefix' ] : null;
        $this->_charset = !empty( $this->_config[ 'charset' ] ) ? $this->_config[ 'charset' ] : null;

        $className = 'Database_Adapter_Sqlite_Connect';
        $this->_adapterInstance = new $className( $this );
        $this->connect();

        $this->_config = null;
        $this->_adapterConfig = null;

        return $this;
    }

    /**
     *
     */
    public function connect()
    {
        $this->_connection = $this->_adapterInstance->connect( $this->_databaseName );

        if ( !$this->_connection )
        {
            trigger_error( $this->_connection->getError(), E_USER_ERROR );
        }


        return $this->_connection;
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
     *
     * @param type $sql
     * @throws BaseException
     * @return Database_Adapter_Sqlite
     */
    public function query( $sql )
    {
        if ( !$this->_connection )
        {
            die( 'Database Connect Informations not valid. Please check the DSN information for the PDO database driver' );
        }

	    $args = func_get_args();
	    $query = array_shift( $args );

      //  $sql = $this->prepareTablePrefix( $sql );
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

		    if ( substr( strtolower( trim( $sql ) ), 0, 6 ) !== 'select' )
		    {
			    // close before update/insert and other
			    if ( is_object( $this->_statement ) )
			    {
				    $this->_statement->closeCursor();
			    }
		    }

		    $this->_statement->execute();
	    }
	    catch ( PDOException $e )
	    {
		    // self::$error = $e->getMessage();
		    $err = $this->_statement->errorInfo();
		    self::$error = $err;

		    die( $e->getMessage() . '<p>SQL: <br/><pre>' . $this->_statement->queryString . '</pre><p>Args: <br/><pre>' . print_r( $args, true ) . '</pre>' );
	    }

	    if ( $this->_statement->errorCode() !== '00000' )
	    {
		    $err = $this->_statement->errorInfo();
		    self::$error = $err;

		    die( 'PDO error: ' . $err[ 2 ] . '<p>SQL: <br/><pre>' . $this->_statement->queryString . '</pre><p>Args: <br/><pre>' . print_r( $args, true ) . '</pre>' );
	    }

/*



        try
        {
            if ( substr( strtolower( trim( $sql ) ), 0, 6 ) === 'select' )
            {
                $this->_statement = $this->_connection->query( $sql );
            }
            else
            {
                // close before update/insert and other
                if ( is_object( $this->_statement ) )
                {
                    $this->_statement->closeCursor();
                }


                $this->_statement = $this->_connection->query( $sql );
            }
        }
        catch ( Exception $e )
        {
            throw new BaseException( 'SQLite error! Query:' . $sql );
        }
*/
	    $this->_params = & $args;
	    $this->_pdoRecord = new Database_Adapter_Pdo_RecordSet( $this, $args );

	    return $this->_pdoRecord;
    }

    public function free()
    {
        $this->_pdoRecord = null;
        $this->_statement = null;
    }

    /**
     * @param int $mode
     * @return mixed
     */
    public function fetch( $mode = SQLITE_BOTH )
    {
        return $this->_statement->fetch( $mode );
    }

    /**
     * @param int $mode
     * @return mixed
     */
    public function fetchAll( $mode = SQLITE_BOTH )
    {
        return $this->_statement->fetchAll( $mode );
    }

    public function current()
    {
        return $this->_statement->current();
    }

    public function key()
    {
        return $this->_statement->key();
    }

    public function next()
    {
        return $this->_statement->next();
    }

    public function rewind()
    {
        return $this->_statement->rewind();
    }

    public function valid()
    {
        return $this->_statement->valid();
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
        if ( !is_object( $this->_pdoRecord ) )
        {
            throw new Exception();
        }
        try
        {
            $r = $this->_pdoRecord->rowCount();
        }
        catch ( Exception $e )
        {
            trigger_error( $e->getMessage(), E_USER_ERROR );
        }


        return $r;
    }

    public function close()
    {
	    unset($this->_statement);
        // return $this->_statement->__destruct();
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
     * List table query
     * Generates a platform-specific query string so that the table names can be fetched
     *
     * @param bool $prefix_limit
     * @return    string
     */
    public function _list_tables( $prefix_limit = FALSE )
    {
        return "SELECT name AS TABLE_NAME FROM sqlite_master WHERE type='table'";
    }

}

?>