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
 * @file        Where.php
 *
 */
abstract class Database_Query_Where extends Database_Query_Builder
{

    // WHERE ...
    /**
     * @var array
     */
    protected $_where = array();

    // ORDER BY ...
    /**
     * @var array
     */
    protected $_order_by = array();

    // LIMIT ...
    /**
     * @var null
     */
    protected $_limitMin = NULL;

    /**
     * @var null
     */
    protected $_limitMax = NULL;

    // GROUP BY ...
    /**
     * @var array
     */
    protected $_group_by = array();

    /**
     * Alias of and_where()
     *
     * @param   mixed   column name or array($column, $alias) or object
     * @param   string  logic operator
     * @param   mixed   column value
     * @return  Database_Query_Where|Database_Query_Update|Database_Query_Delete
     */
    public function where( $column, $op, $value )
    {
        return $this->and_where( $column, $op, $value );
    }

    /**
     * Creates a new "AND WHERE" condition for the query.
     *
     * @param   mixed   column name or array($column, $alias) or object
     * @param   string  logic operator
     * @param   mixed   column value
     * @return  Database_Query_Where|Database_Query_Update|Database_Query_Delete
     */
    public function and_where( $column, $op, $value )
    {
        $this->_where[] = array(
            'AND' => array(
                $column,
                $op,
                $value ) );

        return $this;
    }

    /**
     * Creates a new "OR WHERE" condition for the query.
     *
     * @param   mixed   column name or array($column, $alias) or object
     * @param   string  logic operator
     * @param   mixed   column value
     * @return  Database_Query_Where|Database_Query_Update|Database_Query_Delete
     */
    public function or_where( $column, $op, $value )
    {
        $this->_where[] = array(
            'OR' => array(
                $column,
                $op,
                $value ) );

        return $this;
    }

    /**
     * Alias of and_where_open()
     *
     * @return  Database_Query_Where|Database_Query_Update|Database_Query_Delete
     */
    public function where_open()
    {
        return $this->and_where_open();
    }

    /**
     * Opens a new "AND WHERE (...)" grouping.
     *
     * @return Database_Query_Where
     */
    public function and_where_open()
    {
        $this->_where[] = array(
            'AND' => '(' );

        return $this;
    }

    /**
     * Opens a new "OR WHERE (...)" grouping.
     *
     * @return Database_Query_Where|Database_Query_Update|Database_Query_Delete
     */
    public function or_where_open()
    {
        $this->_where[] = array(
            'OR' => '(' );

        return $this;
    }

    /**
     * Closes an open "AND WHERE (...)" grouping.
     *
     * @return Database_Query_Where|Database_Query_Update|Database_Query_Delete
     */
    public function where_close()
    {
        return $this->and_where_close();
    }

    /**
     * Closes an open "AND WHERE (...)" grouping.
     *
     * @return  Database_Query_Where|Database_Query_Update|Database_Query_Delete
     */
    public function and_where_close()
    {
        $this->_where[] = array(
            'AND' => ')' );

        return $this;
    }

    /**
     * Closes an open "OR WHERE (...)" grouping.
     *
     * @return Database_Query_Where|Database_Query_Update|Database_Query_Delete
     */
    public function or_where_close()
    {
        $this->_where[] = array(
            'OR' => ')' );

        return $this;
    }

    /**
     * Applies sorting with "ORDER BY ..."
     *
     * @param   mixed   column name or array($column, $alias) or object
     * @param   string  direction of sorting
     * @return Database_Query_Where|Database_Query_Update|Database_Query_Delete
     */
    public function order_by( $column, $direction = NULL )
    {
        $this->_order_by[] = array(
            $column,
            $direction );

        return $this;
    }

    /**
     * Return up to "LIMIT ..." results
     *
     * @param   integer  minimum results to return
     * @param   integer  maximum results to return
     * @return  Database_Query_Where|Database_Query_Update|Database_Query_Delete
     */
    public function limit( $number, $number2 = null )
    {
        $this->_limitMin = (int) $number;
        if ( is_integer( $number2 ) )
        {
            $this->_limitMax = (int) $number2;
        }
        return $this;
    }

    /**
     * Creates a "GROUP BY ..." filter.
     *
     * @param   mixed   column name or array($column, $alias) or object
     * @param   ...
     * @return  Database_Query_Where|Database_Query_Update|Database_Query_Delete
     */
    public function group_by( $columns )
    {
        $columns = func_get_args();

        $this->_group_by = array_merge( $this->_group_by, $columns );

        return $this;
    }

}

?>