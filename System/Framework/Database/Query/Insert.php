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
 * @file        Insert.php
 *
 */
class Database_Query_Insert extends Database_Query_Builder
{

    // INSERT INTO ...
    /**
     * @var
     */
    protected $_table;

    // (...)
    /**
     * @var array|null
     */
    protected $_columns = null;

    // VALUES (...)
    /**
     * @var array
     */
    protected $_values = array();

    /**
     * Set the table and columns for an insert.
     *
     * @param $table
     * @param array $columns column names
     * @param null $db
     * @internal param \table $mixed name or array($table, $alias) or object
     * @return \Database_Query_Insert
     */
    public function __construct( $table, array $columns = NULL, $db = null )
    {
        // Set the inital table name
        $this->_table = $table;

        if ( !empty( $columns ) )
        {
            // Set the column names
            $this->_columns = $columns;
        }

        // Start the query with no SQL
        parent::__construct( Database::INSERT, '' );
        $this->_dbInstance = $db;
        return $this;
    }

    /**
     * Sets the table to insert into.
     *
     * @param   mixed  table name or array($table, $alias) or object
     * @return  $this
     */
    public function table( $table )
    {
        $this->_table = $table;

        return $this;
    }

    /**
     * Set the columns that will be inserted.
     *
     * @param   array  column names
     * @return  $this
     */
    public function columns( array $columns )
    {
        $k = array_keys( $columns );
        if ( $k[ 0 ] === 0 )
        {
            $columns = array_values( $columns );
            foreach ( $columns as $column )
            {
                $this->_columns[ $column ] = $column;
            }
        }
        else
        {
            $this->_columns = $columns;
        }

        return $this;
    }

    /**
     * Adds or overwrites values. Multiple value sets can be added.
     *
     * @param array $values values list
     * @throws BaseException
     * @internal param $ ...
     * @return  $this
     */
    public function values( array $values )
    {
        if ( !is_array( $this->_values ) )
        {
            throw new BaseException( 'INSERT INTO ... SELECT statements cannot be combined with INSERT INTO ... VALUES' );
        }


        // Get all of the passed values
        $values = func_get_args();

	    $_values = array();
	    $_columns = array();


        if ( is_array( $values ) )
        {
            $_values = array();
            $_columns = array();
            $tmpVal = array();
            $_single = false;

            foreach ( array_shift( $values ) as $idx => $rs )
            {
                if ( is_array( $rs ) && !is_string( $idx ) )
                {
                    $tmpVal = array();
                    foreach ( $rs as $column => $value )
                    {
                        $_columns[ $column ] = $column;
                        $tmpVal[] = $value;
                    }
                    $_values[] = $tmpVal;
                }
                else
                {
                    $_columns[] = $idx;
                    $tmpVal[] = $rs;
                    $_single = true;
                }
            }


            if ( $_single )
            {
                $_values[] = $tmpVal;
            }


            if ( !is_array( $this->_columns ) )
            {
                $this->columns( $_columns );
            }
            $this->_values = array_merge( $this->_values, $_values );

            return $this;

            print_r( $_columns );
            exit;


            if ( count( $values ) > 1 )
            {
                foreach ( $values as $rs )
                {
                    $tmpVal = array();

                    if ( is_array( $rs ) )
                    {

                        foreach ( $rs as $column => $value )
                        {
                            $_columns[ $column ] = $column;
                            $tmpVal[] = $value;
                        }
                    }
                    else
                    {
                        list($column, $value) = $rs;
                        $_columns[ $column ] = $column;
                        $tmpVal[] = $value;
                    }

                    $_values[] = $tmpVal;
                }
            }
            else
            {

                foreach ( $values as $column => $value )
                {
                    $_columns[ $column ] = $column;
                    $tmpVal[] = $value;
                }
                $_values[] = $tmpVal;
            }

            # print_r($_values);
            # exit;
            $this->columns( $_columns );

            $this->_values = $_values;

            return $this;


            $_values = array();
            $_columns = array();


            print_r( array_values( $args ) );

            foreach ( array_values( $args[ 0 ] ) as $col => $rs )
            {
                if ( is_array( $rs ) && is_int( $col ) )
                {

                    $tmpVals = array();
                    $tmpCols = array();

                    print_r( array_values( $args[ 0 ] ) );

                    foreach ( $rs[ 0 ] as $column => $value )
                    {
                        $tmpCols[] = $column;
                        $_values[] = $value;
                    }

                    $_columns = $tmpCols;
                }
                else
                {
                    $_columns[] = $col;
                    $_values[] = $rs;
                }
            }

            print_r( $_values );
            exit;
            $this->columns( $_columns );
        }
        else
        {
            die();
        }


        $this->_values = $_values;

        return $this;
    }

    /**
     * Use a sub-query to for the inserted values.
     *
     * @param Database_Query $query
     * @throws BaseException
     * @internal param \Database_Query $object of SELECT type
     * @return  $this
     */
    public function select( Database_Query $query )
    {
        if ( $select->type() !== Database::SELECT )
        {
            throw new BaseException( 'Only SELECT queries can be combined with INSERT queries' );
        }

        $this->_values = $select;

        return $select;
    }

    /**
     * Compile the SQL query and return it.
     *
     * @param   object  Database instance
     * @return  string
     */
    public function compile( $db = null )
    {


        if ($db === null) {
            $db = $this->getDbInstance();
        }

        // Start an insertion query
        $query = 'INSERT INTO ' . $db->_escape_identifiers( $this->_table, true );

        $query .= "\n\t";
        // Add the column names
        $query .= ' (' . implode( ', ', array_map( array(
                    $db,
                    '_escape_identifiers' ), $this->_columns ) ) . ') ';
        $query .= "\n";

        if ( is_array( $this->_values ) )
        {
            // Callback for quoting values
            $quote = array(
                $db,
                'quote' );

            $groups = array();
            foreach ( $this->_values as $group )
            {

                foreach ( $group as $offset => $value )
                {
                    if ( (is_string( $value ) && array_key_exists( $value, $this->_parameters )) === FALSE )
                    {
                        // Quote the value, it is not a parameter
                        $group[ $offset ] = $value;
                    }
                }


                $groups[] = '(' . implode( ', ', array_map( $quote, $group ) ) . ')';
            }


            // Add the values
            $query .= 'VALUES ' . implode( ', ' . "\n\t", $groups );
        }
        else
        {
            // Add the sub-query
            $query .= (string) $this->_values;
        }

        return $query;
    }

    /**
     * @return $this
     */
    public function reset()
    {
        $this->_table = NULL;

        $this->_columns = $this->_values = array();

        return $this;
    }

}

?>