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
 * @file        Abstract.php
 *
 */
class Database_Adapter_Abstract
{
    protected static $pdo = array();
    /**
     *    Will attempt to bind columns with datatypes based on parts of the column type name
     *    Any part of the name below will be picked up and converted unless otherwise sepcified
     *     Example: 'VARCHAR' columns have 'CHAR' in them, so 'char' => PDO::PARAM_STR will convert
     *    all columns of that type to be bound as PDO::PARAM_STR
     *    If there is no specification for a column type, column will be bound as PDO::PARAM_STR
     */
    protected $_pdoBindTypes = array(
        'char'   => PDO::PARAM_STR,
        'int'    => PDO::PARAM_INT,
        'bool'   => PDO::PARAM_BOOL,
        'date'   => PDO::PARAM_STR,
        'time'   => PDO::PARAM_INT,
        'text'   => PDO::PARAM_STR,
        'blob'   => PDO::PARAM_LOB,
        'binary' => PDO::PARAM_LOB
    );

    /**
     * Array for converting MYSQLI_*_FLAG constants to text values
     */
    protected $_flags = array(
        'not_null',
        'primary_key',
        'unique_key',
        'multiple_key',
        'blob',
        'unsigned',
        'zerofill',
        'auto_increment',
        'timestamp',
        'set',
        // MYSQLI_NUM_FLAG             => 'numeric',  // unnecessary
        // MYSQLI_PART_KEY_FLAG        => 'multiple_key',  // duplicatvie
        'group_by'
    );
    /**
     * @var array
     */
    protected $_config;

    // Private variables
    /**
     * @var bool
     */
    protected $_protect_identifiers = TRUE;

    /**
     * @var array
     */
    protected $_reserved_identifiers = array(
        '*' );

    /**
     * @var
     */
    protected $_escape_char = '`';

    // Identifiers that should NOT be escaped
    //
	/**
     * @var array
     */
    protected $_ValidAdapters = array(
        'PDO'    => true,
        'MYSQL'  => true,
        'MYSQLI' => true,
        'SQLITE' => true );

    /**
     * @var null
     */
    public $_adapter = null;

    // store the adaptername
    /**
     * @var null
     */
    public $_adapterInstance = null;

    /**
     * @var array|null
     */
    public $_adapterConfig = null;

    /**
     * @var null
     */
    public $_tablePrefix = null;

    /**
     * @var null
     */
    public $_charset = null;

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
     * @var
     */
    public $_databaseName;

    // only used by Pdo
    /**
     * @var PDOStatement
     */
    public $_statement = null;

    /**
     * @var int
     */
    public $query_count = 0;

    /**
     * @var array
     */
    public static $querylog = array();

    /**
     * @var int
     */
    public static $totalQueryTimes = 0;

    /**
     *
     * @var type
     */
    public static $_TotalQuerys = 0;

    /**
     * @var int
     */
    public static $_TotalQueryTime = 0;

    /**
     * @var int
     */
    public static $_QueryTime = 0;

    /**
     * @var array
     */
    private $data_cache = array();

    /**
     * @var array
     */
    protected $ar_from = array();

    /**
     * @var array
     */
    protected $ar_join = array();

    /**
     * @var array
     */
    protected $ar_where = array();

    /**
     * @var array
     */
    protected $ar_like = array();

    /**
     * @var array
     */
    protected $ar_groupby = array();

    /**
     *
     * @var int 
     */
    public $_startTimer = null;

    public $_lock = false;

    public static $error;

    /**
     *
     * @param array $config
     * @throws BaseException
     */
    public function __construct( array $config )
    {
        if ( !isset( $config[ 'connection' ] ) )
        {
            throw new BaseException( 'Database Connect Informations not exists' );
        }

        $this->_config = $config;

        $this->_adapterConfig = $config;

        return $this;
    }

    /**
     *
     */
    public function __destruct()
    {

        $this->data_cache = array();
        $this->_statement = null;
        $this->_adapter = null;
        $this->_adapterInstance = null;
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
     * Will set the Database Adapter
     *
     * @param string $adapter
     */
    public function setAdapter( $adapter )
    {
        $this->_adapter = $adapter;
    }

    /**
     * Returns the current Database Adapter
     *
     * @return string
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }

    /**
     * @param $str
     * @return string
     */
    public function prepareDatabaseName( $str )
    {
        return $this->_escape_char . $str . $this->_escape_char;
    }

    /**
     * @param bool $prefix_only
     * @return null|string
     */
    public function tp( $prefix_only = false )
    {
        if ( $prefix_only !== false )
        {
            return $this->_tablePrefix;
        }

        return $this->prepareDatabaseName( $this->_databaseName ) . '.' . $this->_tablePrefix;
    }

    /**
     * @param $sql
     * @return string
     */
    public function prepareTablePrefix( $sql )
    {
        return ltrim( preg_replace( '/([\s\r\n\t]{1,})%tp%/', '$1' . $this->tp(), $sql ) );
    }

    /**
     * @param      $str
     * @param bool $prepare
     * @return mixed
     */
    public function autoPrefixTable( $str, $prepare = true )
    {
        if ( !is_string( $str ) )
        {
            Error::raise( 'Invalid SQL Query ' . $str );
        }

        if ( $prepare )
        {
            return preg_replace( '/([\s\r\n\t]{1,})?%tp%/', '$1' . $this->tp(), $str );
        }
        else
        {
            return preg_replace( '/([\s\r\n\t]{1,})?%tp%/', '$1' . $this->tp( true ), $str );
        }
    }

    /**
     * PDO Records
     * @param type $args
     */
    public function ResultSet( $args )
    {
        
    }

    /**
     * @return int
     */
    public function getQueryTimer()
    {
        return self::$_TotalQueryTime;
    }

    /**
     *
     * @return string
     */
    public static function getDebug()
    {
        $output = '';
        $total = 0;
        foreach ( self::$querylog as $key => $log )
        {
            $output .= "\r\n" . Library::stringPad( $key + 1, 2, ' ' ) . ") " . $log[ 'duration' ] . "\n";
            $output .= "    " . trim( $log[ 'query' ] ) . "\r\n\r\n";
            $total += $log[ 'raw' ]; // timer
        }
        $output .= "\nTotal query time: " . Debug::getReadableTime( $total );
        return $output;
    }

    /**
     * Quote a database column name and add the table prefix if needed.
     *
     *     $column = $db->quote_column($column);
     *
     * You can also use SQL methods within identifiers.
     *
     *     // The value of "column" will be quoted
     *     $column = $db->quote_column('COUNT("column")');
     *
     * Objects passed to this function will be converted to strings.
     * [Database_Expression] objects will be compiled.
     * [Database_Query] objects will be compiled and converted to a sub-query.
     * All other objects will be converted using the `__toString` method.
     *
     * @param   mixed   column name or array(column, alias)
     * @return  string
     * @uses    Database::quote_identifier
     * @uses    Database::table_prefix
     */
    public function quote_column( $column )
    {
        if ( is_array( $column ) )
        {
            list($column, $alias) = $column;
        }

        if ( $column instanceof Database_Query )
        {
            // Create a sub-query
            $column = '(' . $column->compile( $this ) . ')';
        }
        else
        {
            // Convert to a string
            $column = (string) $column;

            if ( $column === '*' )
            {
                return $column;
            }
            elseif ( strpos( $column, '"' ) !== FALSE )
            {
                // Quote the column in FUNC("column") identifiers
                $column = preg_replace( '/"(.+?)"/e', '$this->quote_column("$1")', $column );
            }
            elseif ( strpos( $column, '.' ) !== FALSE )
            {
                $parts = explode( '.', $column );

                if ( ($prefix = $this->tp()) != '' )
                {
                    // Get the offset of the table name, 2nd-to-last part
                    $offset = count( $parts ) - 2;

                    // Add the table prefix to the table name
                    $parts[ $offset ] = $prefix . $parts[ $offset ];
                }

                foreach ( $parts as & $part )
                {
                    if ( $part !== '*' )
                    {
                        // Quote each of the parts
                        $part = $this->_identifier . $part . $this->_identifier;
                    }
                }

                $column = implode( '.', $parts );
            }
            else
            {
                $column = $this->_identifier . $column . $this->_identifier;
            }
        }

        if ( isset( $alias ) )
        {
            $column .= ' AS ' . $this->_identifier . $alias . $this->_identifier;
        }

        return $column;
    }

    /**
     * This function is used extensively by the Active Record class, and by
     * a couple functions in this class.
     * It takes a column or table name (optionally with an alias) and inserts
     * the table prefix onto it.  Some logic is necessary in order to deal with
     * column names that include the path.  Consider a query like this:
     *
     * SELECT * FROM hostname.database.table.column AS c FROM hostname.database.table
     *
     * Or a query with aliasing:
     *
     * SELECT m.member_id, m.member_name FROM members AS m
     *
     * Since the column name can include up to four segments (host, DB, table, column)
     * or also have an alias prefix, we need to do a bit of work to figure this out and
     * insert the table prefix (if it exists) in the proper position, and escape only
     * the correct identifiers.
     *
     * @access    private
     * @param    string
     * @param    bool
     * @param    mixed
     * @param    bool
     * @return    string
     */
    protected function _protect_identifiers( $item, $prefix_single = FALSE, $protect_identifiers = NULL, $field_exists = TRUE )
    {
        if ( !is_bool( $protect_identifiers ) )
        {
            $protect_identifiers = $this->_protect_identifiers;
        }

        if ( is_array( $item ) )
        {
            $escaped_array = array();

            foreach ( $item as $k => $v )
            {
                $escaped_array[ $this->_protect_identifiers( $k ) ] = $this->_protect_identifiers( $v );
            }

            return $escaped_array;
        }

        // Convert tabs or multiple spaces into single spaces
        $item = preg_replace( '/[\t ]+/', ' ', $item );

        // If the item has an alias declaration we remove it and set it aside.
        // Basically we remove everything to the right of the first space
        $alias = '';
        if ( strpos( $item, ' ' ) !== FALSE )
        {
            $alias = strstr( $item, " " );
            $item = substr( $item, 0, -strlen( $alias ) );
        }

        // This is basically a bug fix for queries that use MAX, MIN, etc.
        // If a parenthesis is found we know that we do not need to
        // escape the data or add a prefix.  There's probably a more graceful
        // way to deal with this, but I'm not thinking of it -- Rick
        if ( strpos( $item, '(' ) !== FALSE )
        {
            return $item . $alias;
        }

        // Break the string apart if it contains periods, then insert the table prefix
        // in the correct location, assuming the period doesn't indicate that we're dealing
        // with an alias. While we're at it, we will escape the components
        if ( strpos( $item, '.' ) !== FALSE )
        {
            $parts = explode( '.', $item );

            // Does the first segment of the exploded item match
            // one of the aliases previously identified?  If so,
            // we have nothing more to do other than escape the item
            if ( in_array( $parts[ 0 ], $this->ar_aliased_tables ) )
            {
                if ( $protect_identifiers === TRUE )
                {
                    foreach ( $parts as $key => $val )
                    {
                        if ( !in_array( $val, $this->_reserved_identifiers ) )
                        {
                            $parts[ $key ] = $this->_escape_identifiers( $val );
                        }
                    }

                    $item = implode( '.', $parts );
                }
                return $item . $alias;
            }

            // Is there a table prefix defined in the config file?  If not, no need to do anything
            if ( $this->dbprefix !== '' )
            {
                // We now add the table prefix based on some logic.
                // Do we have 4 segments (hostname.database.table.column)?
                // If so, we add the table prefix to the column name in the 3rd segment.
                if ( isset( $parts[ 3 ] ) )
                {
                    $i = 2;
                } // Do we have 3 segments (database.table.column)?
                // If so, we add the table prefix to the column name in 2nd position
                elseif ( isset( $parts[ 2 ] ) )
                {
                    $i = 1;
                } // Do we have 2 segments (table.column)?
                // If so, we add the table prefix to the column name in 1st segment
                else
                {
                    $i = 0;
                }

                // This flag is set when the supplied $item does not contain a field name.
                // This can happen when this function is being called from a JOIN.
                if ( $field_exists === FALSE )
                {
                    $i++;
                }

                // Verify table prefix and replace if necessary
                if ( $this->swap_pre !== '' && strncmp( $parts[ $i ], $this->swap_pre, strlen( $this->swap_pre ) ) === 0 )
                {
                    $parts[ $i ] = preg_replace( "/^" . $this->swap_pre . "(\S+?)/", $this->dbprefix . "\\1", $parts[ $i ] );
                }

                // We only add the table prefix if it does not already exist
                if ( substr( $parts[ $i ], 0, strlen( $this->dbprefix ) ) != $this->dbprefix )
                {
                    $parts[ $i ] = $this->dbprefix . $parts[ $i ];
                }

                // Put the parts back together
                $item = implode( '.', $parts );
            }

            if ( $protect_identifiers === TRUE )
            {
                $item = $this->_escape_identifiers( $item );
            }

            return $item . $alias;
        }

        // Is there a table prefix?  If not, no need to insert it
        if ( $this->dbprefix !== '' )
        {
            // Verify table prefix and replace if necessary
            if ( $this->swap_pre !== '' && strncmp( $item, $this->swap_pre, strlen( $this->swap_pre ) ) === 0 )
            {
                $item = preg_replace( "/^" . $this->swap_pre . "(\S+?)/", $this->dbprefix . "\\1", $item );
            }

            // Do we prefix an item with no segments?
            if ( $prefix_single === TRUE AND substr( $item, 0, strlen( $this->dbprefix ) ) != $this->dbprefix )
            {
                $item = $this->dbprefix . $item;
            }
        }

        if ( $protect_identifiers === TRUE AND !in_array( $item, $this->_reserved_identifiers ) )
        {
            $item = $this->_escape_identifiers( $item );
        }

        return $item . $alias;
    }

    /**
     * Primary
     * Retrieves the primary key.  It assumes that the row in the first
     * position is the primary key
     *
     * @access    public
     * @param    string    the table name
     * @return    string
     */
    public function primary( $table = '' )
    {
        $fields = $this->list_fields( $table );

        if ( !is_array( $fields ) )
        {
            return FALSE;
        }

        return current( $fields );
    }

    /**
     * List databases
     *
     * @access    public
     * @return    bool
     */
    public function list_databases()
    {
        // Is there a cached result?
        if ( isset( $this->data_cache[ 'db_names' ] ) )
        {
            return $this->data_cache[ 'db_names' ];
        }


        $result = $this->query( 'SHOW DATABASES' )->fetchAll();

        $dbs = array();
        if ( count( $result ) > 0 )
        {
            foreach ( $result as $row )
            {
                $dbs[] = current( $row );
            }
        }

        $this->data_cache[ 'db_names' ] = $dbs;
        return $this->data_cache[ 'db_names' ];
    }

    /**
     * @return array
     */
    function listTables()
    {
        return $this->list_tables();
    }

    /**
     * Returns an array of table names
     *
     * @access    public
     * @param bool $constrain_by_prefix
     * @return    array
     */
    public function list_tables( $constrain_by_prefix = FALSE )
    {
        // Is there a cached result?
        if ( isset( $this->data_cache[ 'table_names' ] ) )
        {
            return $this->data_cache[ 'table_names' ];
        }

        if ( FALSE === ($sql = $this->_list_tables( $constrain_by_prefix )) )
        {
            if ( $this->db_debug )
            {
                return $this->display_error( 'db_unsupported_function' );
            }
            return FALSE;
        }

        $retval = array();
        $query = $this->query( $sql );

        if ( $query->count() > 0 )
        {
            foreach ( $query->fetchAll() as $row )
            {
                if ( isset( $row[ 'TABLE_NAME' ] ) )
                {
                    $retval[] = $row[ 'TABLE_NAME' ];
                }
                else
                {
                    $retval[] = array_shift( $row );
                }
            }
        }
        else
        {
            die( $sql );
        }

        $this->data_cache[ 'table_names' ] = $retval;
        return $this->data_cache[ 'table_names' ];
    }

    /**
     *
     * @param bool|string $table
     * @return array
     */
    public function getTableState( $table = false )
    {
        if ( $table === false )
        {
            return false;
        }

        $table = $this->autoPrefixTable( $table, false );

        $sql = "SHOW TABLE STATUS FROM " . $this->prepareDatabaseName( $this->_databaseName ) . " LIKE " . $this->quote( $table );
        return $this->query( $sql )->fetch();
    }

    /**
     *
     * @param string $table_name
     * @return boolean
     */
    public function tbl_exists( $table_name )
    {
        return $this->table_exists( $table_name );
    }

    /**
     * Determine if a particular table exists
     *
     * @access    public
     * @param $table_name
     * @return    boolean
     */
    public function table_exists( $table_name )
    {
        $table_name = $this->autoPrefixTable( $table_name, false );
        return (!in_array( $this->_protect_identifiers( $table_name, TRUE, FALSE, FALSE ), $this->list_tables() )) ? FALSE : TRUE;
    }

    /**
     * Create a new SELECT builder.
     *
     * @param   mixed   column name or array($column, $alias) or object
     * @param   ...
     * @return  Database_Query_Select
     */
    public function select( $columns = NULL )
    {
        $s = new Database_Query_Select( func_get_args() );
        $s->_dbInstance = $this;
        return $s;
    }

    /**
     * Create a new INSERT builder.
     *
     * @param array list of column names or array($column, $alias) or object
     * @param array $columns
     * @return  Database_Query_Insert
     */
    public function insert( $table, array $columns = NULL )
    {
        return new Database_Query_Insert( $table, $columns, $this );
    }

    /**
     * Create a new UPDATE builder.
     *
     * @param   string  table to update
     * @param array $columns
     * @return  Database_Query_Update
     */
    public function update( $table, array $columns = NULL )
    {
        return new Database_Query_Update( $table, $columns, $this );
    }

    /**
     * Create a new DELETE builder.
     *
     * @param   string  table to delete from
     * @return  Database_Query_Delete
     */
    public function delete( $table )
    {
        return new Database_Query_Delete( $table, $this );
    }


}

?>