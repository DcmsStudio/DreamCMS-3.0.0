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
 * @file        RecordSet.php
 *
 */
class Database_Adapter_Pdo_RecordSet implements Iterator, ArrayAccess
{

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
    public $_fields = array();

    /**
     * @var array
     */
    public $_fieldMeta = array();

    /**
     * @var null
     */
    public $_primaryKey = NULL;

    /**
     * @var null|type
     */
    private $_db = null;

    /**
     * @var null|PDOStatement
     */
    private $_statement = null;

    /**
     * @var array
     */
    private $_params = array();

    /**
     * @var null
     */
    private $_currentRowObj = null;

    /**
     * @var int
     */
    private $_currentRowIndex = 0;

    /**
     * @var
     */
    private $_fetchMode = PDO::FETCH_ASSOC;

    /**
     * @param Database_Adapter_Pdo $pdo
     * @param array                $params
     */
    public function __construct( Database_Adapter_Pdo $pdo, array $params )
    {
        $this->_statement = $pdo->_statement;
        $this->_params = $params;
        $this->_db = $pdo->_connection;
    }

    /**
     * @param PDO $_db
     */
    public function setDbObj( PDO $_db )
    {
        $this->_db = & $_db;
    }

    public function refresh()
    {
        $this->_statement->execute( $this->_params );

        if ( $this->_statement->errorCode() !== '00000' )
        {
            throw new Database_Exception( $this->_statement->errorInfo() );
        }
    }

    /**
     * @param string $mode
     * @return mixed
     */
    public function fetchMode( $mode = 'assoc' )
    {

        if ( $mode == 'assoc' )
        {
            return $this->_fetchMode = PDO::FETCH_ASSOC;
        }
        elseif ( $mode == 'num' )
        {
            return $this->_fetchMode = PDO::FETCH_NUM;
        }
        else
        {
            return $this->_fetchMode = PDO::FETCH_ASSOC;
        }
    }

    /**
     * @param null $mode
     * @return type
     */
    public function fetch( $mode = null )
    {
        if ( is_string( $mode ) )
        {
            $this->fetchMode( $mode );
        }

        return $this->next();
    }

    /**
     * @param null $mode
     * @return array
     */
    public function fetchAll( $mode = null )
    {
        $this->_currentRowIndex = $this->count() - 1;

        if ( is_string( $mode ) )
        {
            $this->fetchMode( $mode );
        }

        return $this->_statement->fetchAll( $this->_fetchMode );
    }

    /**
     * @param null $mode
     * @return type
     */
    public function first( $mode = null )
    {
        if ( is_string( $mode ) )
        {
            $this->fetchMode( $mode );
        }

        return $this->next();
    }

    /**
     * @return null
     */
    public function current()
    {
        return $this->_currentRowObj;
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->_currentRowIndex;
    }

    /**
     *
     *
     * @throws Database_Exception
     * @return type
     */
    public function next()
    {
        $this->_currentRowObj = $this->_statement->fetch( $this->_fetchMode );
        if ( $this->_statement->errorCode() !== '00000' )
        {
            throw new Database_Exception( $this->_statement->errorInfo() );
        }

        $this->_currentRowIndex++;
        return $this->_currentRowObj;
    }

    public function rewind()
    {
        $this->refresh();
        $this->_currentRowIndex = 0;
        $this->_currentRowObj = null;

        if ( $this->_statement->errorCode() !== '00000' )
        {
            throw new BaseException( $this->_statement->errorInfo() );
        }
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->_currentRowObj !== false;
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->_statement->rowCount();
    }

    /**
     * @return int
     */
    public function rowCount()
    {
        return $this->_statement->rowCount();
    }

    /**
     * @return int
     */
    public function recordCount()
    {
        try
        {
            $rows = $this->fetchAll();
            return count( $rows );
        }
        catch ( Exception $e )
        {
            return 0;
        }
    }

    public function __destruct()
    {
        //$this->_statement->closeCursor();
    }

    /**
     *
     * @param integer $offset
     * @param mixed   $value
     * @throws BaseException
     */
    public function offsetSet( $offset, $value )
    {
        throw new BaseException( 'Cannot modify ResultSet.' );
    }

    /**
     *
     * @param integer $offset
     * @return type
     */
    public function offsetExists( $offset )
    {
        return $offset < $this->_statement->rowCount();
    }

    /**
     *
     * @param integer $offset
     * @throws BaseException
     * @return Exception
     */
    public function offsetUnset( $offset )
    {
        throw new BaseException( 'Cannot modify ResultSet.' );
    }

    /**
     *
     * @param integer $offset
     * @return type
     */
    public function offsetGet( $offset )
    {
        for ( $i = 0; $i < $offset; $i++ )
        {
            $this->next();
        }
        return $this->current();
    }

    /**
     *    Automatically get column metadata
     */
    public function getColumnMeta( array $columns )
    {
        // Clear any previous column/field info
        $this->_fields = array();
        $this->_fieldMeta = array();
        $this->_primaryKey = NULL;

        // Automatically retrieve column information if column info not specified
        if ( count( $this->_fields ) == 0 || count( $this->_fieldMeta ) == 0 )
        {
            // Fetch all columns and store in $this->fields

            foreach ( $columns as $key => $col )
            {
                // Insert into fields array
                $colname = $col[ 'Field' ];
                $this->_fields[ $colname ] = $col;
                if ( $col[ 'Key' ] == "PRI" )
                {
                    $this->_primaryKey[] = $colname;
                }


                $extras = explode( ' ', $col[ 'Extra' ] );
                foreach ( $extras as $x )
                {
                    if ( in_array( $x, $this->_flags ) )
                    {
                        $this->_fields[ $colname ][ str_replace( array(
                                    '_',
                                    ' ' ), '', $x ) ] = true;
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
                foreach ( $extras as $x )
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
     *    Parse PDO-produced column type
     *    [internal function]
     */
    public function parseColumnType( $colType )
    {
        $colInfo = array();
        $colParts = explode( " ", $colType );
        if ( ($fparen = strpos( $colParts[ 0 ], "(" ) ) )
        {
            $colInfo[ 'type' ] = substr( $colParts[ 0 ], 0, $fparen );
            $colInfo[ 'pdoType' ] = '';
            $colInfo[ 'length' ] = str_replace( ")", "", substr( $colParts[ 0 ], $fparen + 1 ) );
            $colInfo[ 'attributes' ] = isset( $colParts[ 1 ] ) ? $colParts[ 1 ] : NULL;
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

?>