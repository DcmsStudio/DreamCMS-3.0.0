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
 * @file        Update.php
 *
 */
class Database_Query_Update extends Database_Query_Where
{

    // UPDATE ...
    /**
     * @var
     */
    protected $_table;

    // SET ...
    /**
     * @var array
     */
    protected $_set = array();

    /**
     * Set the table for a update.
     *
     * @param $table
     * @param null $columns
     * @param null $db
     * @internal param \table $mixed name or array($table, $alias) or object
     * @return \Database_Query_Update
     */
    public function __construct( $table, $columns = null, $db = null )
    {
        // Set the inital table name
        $this->_table = $table;


        if ( !empty( $columns ) )
        {
            // Set the values
            foreach ($columns as $column => $value) {
                if (is_string($column)) {
                    $this->_set[] = array(
                        $column,
                        $value );
                }
            }
        }

        // Start the query with no SQL
        parent::__construct( Database::UPDATE, '' );
        $this->_dbInstance = $db;
        return $this;

    }

    /**
     * Sets the table to update.
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
     * Set the values to update with an associative array.
     *
     * @throws BaseException
     * @internal param \associative $array list
     *           or use
     *          ->set('id', 1)
     *
     * @return  $this
     */
    public function set()
    {
        $args = func_get_args();

        if ( func_num_args() === 2 && is_string( $args[ 0 ] ) )
        {
            $this->_set[] = array(
                $args[ 0 ],
                $args[ 1 ] );
        }
        elseif ( func_num_args() === 1 && is_array( $args ) )
        {
            foreach ( $args[ 0 ] as $column => $value )
            {
                $this->_set[] = array(
                    $column,
                    $value );
            }
        }
        else
        {
            throw new BaseException( 'Invalid Database Querybuilder set!' );
        }


        return $this;
    }

    /**
     * Set the value of a single column.
     *
     * @param   string  column name
     * @param   mixed  column value
     * @return  $this
     */
    public function value( $column, $value )
    {
        $this->_set[] = array(
            $column,
            $value );

        return $this;
    }

    /**
     * Compile the SQL query and return it.
     *
     * @param   object  Database instance
     * @return  string
     */
    public function compile( $db )
    {

        if ($db === null) {
            $db = $this->getDbInstance();
        }

        // Start an update query
        $query = 'UPDATE ' . $db->_escape_identifiers( $this->_table, true );

        // Add the columns to update
        $query .= "\n\t" . ' SET ' . $this->_compile_set( $db, $this->_set );


        if ( !empty( $this->_where ) )
        {
            // Add selection conditions
            $query .= "\n" . ' WHERE ' . "\n\t" . Database_Query_Builder::compile_conditions( $db, $this->_where );
        }

        return $query;
    }

    /**
     * @return $this
     */
    public function reset()
    {
        $this->_table = NULL;

        $this->_set = $this->_where = array();

        return $this;
    }

}
