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
 * @file        Select.php
 *
 */
class Database_Query_Select extends Database_Query_Where
{

    // SELECT ...
    /**
     * @var array
     */
    protected $_select = array();

    // DISTINCT
    /**
     * @var bool
     */
    protected $_distinct = FALSE;

    // FROM ...
    /**
     * @var array
     */
    protected $_from = array();

    // JOIN ...
    /**
     * @var array
     */
    protected $_join = array();

    // HAVING ...
    /**
     * @var array
     */
    protected $_having = array();

    // OFFSET ...
    /**
     * @var null
     */
    protected $_offset = NULL;

    // The last JOIN statement created
    /**
     * @var
     */
    protected $_last_join;

    /**
     * @var array
     */
    protected $__selctors = array(
        'AS',
        'CONCAT',
        'COUNT',
        'MAX',
        'SUM',
        'IF',
        'FLOOR',
        'RAND',
        'CASE',
        'WHEN',
        'THEN',
        'ELSE',
        'END' );

    /**
     * Sets the initial columns to select from.
     *
     * @param array $columns column list
     * @return \Database_Query_Select
     */
    public function __construct( array $columns = NULL )
    {
        if ( !empty( $columns ) )
        {
            // Set the initial columns
            $this->_select = $columns;
        }

        // Start the query with no actual SQL statement
        parent::__construct( Database::SELECT, '' );

        return $this;
    }

    /**
     * Enables or disables selecting only unique columns using "SELECT DISTINCT"
     *
     * @param   boolean  enable or disable distinct columns
     * @return  Database_Query_Select
     */
    public function distinct( $value )
    {
        $this->_distinct = (bool) $value;

        return $this;
    }

    /**
     * Choose the columns to select from.
     *
     * @param   mixed  column name or array($column, $alias) or object
     * @param   ...
     * @return  Database_Query_Select
     */
    public function select( $columns = NULL )
    {
        $columns = func_get_args();

        $this->_select = array_merge( $this->_select, $columns );

        return $this;
    }

    /**
     * Choose the tables to select "FROM ..."
     *
     * @param   mixed  table name or array($table, $alias) or object
     * @param   ...
     * @return  Database_Query_Select
     */
    public function from( $tables )
    {
        $tables = func_get_args();

        $this->_from = array_merge( $this->_from, $tables );

        return $this;
    }

    /**
     * Adds addition tables to "JOIN ...".
     *
     * @param   mixed   column name or array($column, $alias) or object
     * @param   string  join type (LEFT, RIGHT, INNER, etc)
     * @return  Database_Query_Select
     */
    public function join( $table, $type = NULL )
    {
        $this->_join[] = $this->_last_join = new Database_Query_Join( $table, $type );

        return $this;
    }

    /**
     * Adds "ON ..." conditions for the last created JOIN statement.
     *
     * @param   mixed   column name or array($column, $alias) or object
     * @param   string  logic operator
     * @param   mixed   column name or array($column, $alias) or object
     * @return  Database_Query_Select
     */
    public function on( $c1, $op, $c2 )
    {
        $this->_last_join->on( $c1, $op, $c2 );

        return $this;
    }

    /**
     * Alias of and_having()
     *
     * @param   mixed   column name or array($column, $alias) or object
     * @param   string  logic operator
     * @param   mixed   column value
     * @return  $this
     */
    public function having( $column, $op, $value = NULL )
    {
        return $this->and_having( $column, $op, $value );
    }

    /**
     * Creates a new "AND HAVING" condition for the query.
     *
     * @param   mixed   column name or array($column, $alias) or object
     * @param   string  logic operator
     * @param   mixed   column value
     * @return  Database_Query_Select
     */
    public function and_having( $column, $op, $value = NULL )
    {
        $this->_having[] = array(
            'AND' => array(
                $column,
                $op,
                $value ) );

        return $this;
    }

    /**
     * Creates a new "OR HAVING" condition for the query.
     *
     * @param   mixed   column name or array($column, $alias) or object
     * @param   string  logic operator
     * @param   mixed   column value
     * @return  Database_Query_Select
     */
    public function or_having( $column, $op, $value = NULL )
    {
        $this->_having[] = array(
            'OR' => array(
                $column,
                $op,
                $value ) );

        return $this;
    }

    /**
     * Alias of and_having_open()
     *
     * @return  Database_Query_Select
     */
    public function having_open()
    {
        return $this->and_having_open();
    }

    /**
     * Opens a new "AND HAVING (...)" grouping.
     *
     * @return  Database_Query_Select
     */
    public function and_having_open()
    {
        $this->_having[] = array(
            'AND' => '(' );

        return $this;
    }

    /**
     * Opens a new "OR HAVING (...)" grouping.
     *
     * @return  Database_Query_Select
     */
    public function or_having_open()
    {
        $this->_having[] = array(
            'OR' => '(' );

        return $this;
    }

    /**
     * Closes an open "AND HAVING (...)" grouping.
     *
     * @return  Database_Query_Select
     */
    public function having_close()
    {
        return $this->and_having_close();
    }

    /**
     * Closes an open "AND HAVING (...)" grouping.
     *
     * @return  Database_Query_Select
     */
    public function and_having_close()
    {
        $this->_having[] = array(
            'AND' => ')' );

        return $this;
    }

    /**
     * Closes an open "OR HAVING (...)" grouping.
     *
     * @return  Database_Query_Select
     */
    public function or_having_close()
    {
        $this->_having[] = array(
            'OR' => ')' );

        return $this;
    }

    /**
     * Start returning results after "OFFSET ..."
     *
     * @param   integer   starting result number
     * @return  Database_Query_Select
     */
    public function offset( $number )
    {
        $this->_offset = (int) $number;

        return $this;
    }

    /**
     *
     * @throws BaseException
     * @return string
     */
    public function compileSelectColums()
    {
        $compiled = '';

        $quote_ident = array(
            $this->_dbInstance,
            '_escape_identifiers' );

        $regex = '^\s*(';
        $regex .= implode( '|', $this->__selctors );
        $regex .= ')(\s\s*|\s*\()';

        $fieldregex = $regex . '([a-z0-9\s\*_]*)';

        foreach ( $this->_select as $col )
        {
            $stop = false;
            while ( !$stop )
            {
                preg_match( '#' . $regex . '#i', $col, $matches );
                if ( !empty( $matches[ 0 ] ) && !empty( $matches[ 2 ] ) )
                {

                    preg_match( '#' . $fieldregex . '#i', $col, $fmatches );

                    if ( trim( $fmatches[ 2 ] ) == '(' )
                    {
                        if ( trim( $fmatches[ 3 ] ) === '*' )
                        {
                            $compiled .= $fmatches[ 0 ] . ')';
                            $col = str_ireplace( $fmatches[ 0 ], '', $col );
                        }
                        else
                        {
                            $compiled .= $fmatches[ 0 ];
                            $pos = strpos( ')', $col );

                            if ( $pos === false )
                            {
                                throw new BaseException( 'SQL Query Builder error. Please check your SQL Query!' );
                            }

                            $fieldname = substr( $col, 0, $pos );
                            $compiled .= implode( '', array_map( $quote_ident, array(
                                $fieldname ) ) );
                            $col = substr( $col, $pos );
                            $compiled .= ')';
                        }
                    }

                    if ( !strlen( trim( $col ) ) )
                    {
                        $stop = true;
                    }
                }
            }

        }

        return $compiled;
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

        // Callback to quote identifiers
        $quote_ident = array(
            $this->_dbInstance,
            '_escape_identifiers' );

        // Start a selection query
        $query = 'SELECT ';

        if ( $this->_distinct === TRUE )
        {
            // Select only unique results
            $query .= 'DISTINCT ';
        }

        $query .= "\n\t";

        //$this->compileSelectColums();

        if ( empty( $this->_select ) )
        {
            // Select all columns
            $query .= '*';
        }
        else
        {
            // Select all columns
            $query .= implode( ', ', array_map( $quote_ident, $this->_select ) );
        }


        if ( !empty( $this->_from ) )
        {
            $query .= "\n";


            $tmp = array();
            foreach ($this->_from as $tbl) {
                $tmp[] = $db->_escape_identifiers($tbl, true);
            }

            $mapped = implode( ', ', $tmp );

            // Set tables to select from
            $query .= ' FROM ' . $mapped;
        }


        if ( !empty( $this->_join ) )
        {
            $query .= "\n";
            // Add tables to join
            $query .= ' ' . $this->compile_join( $db, $this->_join );
        }


        if ( !empty( $this->_where ) )
        {
            $query .= "\n";
            // Add selection conditions
            $query .= ' WHERE ' . "\n\t" . $this->compile_conditions( $db, $this->_where );
        }

        if ( !empty( $this->_group_by ) )
        {
            $query .= "\n";

            // Add group by
            $query .= ' ' . $this->_compile_group_by( $db, $this->_group_by );
        }

        if ( !empty( $this->_having ) )
        {
            $query .= "\n";

            // Add filtering conditions
            $query .= ' HAVING ' . $this->compile_conditions( $db, $this->_having );
        }

        if ( !empty( $this->_order_by ) )
        {
            $query .= "\n";

            // Add sorting
            $query .= ' ' . $this->_compile_order_by( $db, $this->_order_by );
        }

        if ( $this->_limitMin !== NULL )
        {
            $query .= "\n";
            // Add limiting
            $query .= ' LIMIT ' . $this->_limitMin;


            if ( $this->_limitMax !== NULL )
            {
                $query .= ', ' . $this->_limitMax;
            }
        }

        if ( $this->_offset !== NULL )
        {
            $query .= "\n";
            // Add offsets
            $query .= ' OFFSET ' . $this->_offset;
        }

        return $query;
    }

    /**
     * @return Database_Query_Select
     */
    public function reset()
    {
        $this->_select = $this->_from = $this->_join = $this->_where = $this->_group_by = $this->_having = $this->_order_by = array();

        $this->_distinct = FALSE;

        $this->_limit = $this->_offset = $this->_last_join = NULL;

        return $this;
    }

}

?>