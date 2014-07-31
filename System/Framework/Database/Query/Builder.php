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
 * @file        Builder.php
 *
 */
class Database_Query_Builder extends Database_Query
{

    /**
     * Compiles an array of set values into an SQL partial. Used for UPDATE.
     *
     * @param   object  Database instance
     * @param   array   updated values
     * @return  string
     */
    protected function _compile_set( $db, array $values )
    {
        $set = array();
        foreach ( $values as $group )
        {
            // Split the set
            list ($column, $value) = $group;

            // Quote the column name
            $column = $db->_escape_identifiers( $column );

            if ( is_string( $value ) /* && array_key_exists($value, parent::$_parameters)) === FALSE */ )
            {
                // Quote the value, it is not a parameter
                $value = $db->quote( $value );
            }

            $set[ $column ] = $column . ' = ' . $value;
        }

        return implode( ', ', $set );
    }

    /**
     * Compiles an array of JOIN statements into an SQL partial.
     *
     * @param   object  Database instance
     * @param   array   join statements
     * @return  string
     */
    protected function compile_join( $db, array $joins )
    {
        $statements = array();

        foreach ( $joins as $join )
        {
            // Compile each of the join statements
            $statements[] = $join->compile( $db );
        }

        return implode( ' ', $statements );
    }

    /**
     * Compiles an array of conditions into an SQL partial. Used for WHERE
     * and HAVING.
     *
     * @param   object  Database instance
     * @param   array   condition statements
     * @return  string
     */
    protected function compile_conditions( $db, array $conditions )
    {
        $last_condition = NULL;

        $sql = '';
        foreach ( $conditions as $group )
        {
            // Process groups of conditions
            foreach ( $group as $logic => $condition )
            {
                if ( $condition === '(' )
                {
                    if ( !empty( $sql ) AND $last_condition !== '(' )
                    {
                        // Include logic operator
                        $sql .= ' ' . $logic . ' ';
                    }

                    $sql .= '(';
                }
                elseif ( $condition === ')' )
                {
                    $sql .= ')';
                }
                else
                {
                    if ( !empty( $sql ) AND $last_condition !== '(' )
                    {
                        // Add the logic operator
                        $sql .= ' ' . $logic . ' ';
                    }

                    // Split the condition
                    list($column, $op, $value) = $condition;

                    if ( $value === NULL )
                    {
                        if ( $op === '=' )
                        {
                            // Convert "val = NULL" to "val IS NULL"
                            $op = 'IS';
                        }
                        elseif ( $op === '!=' )
                        {
                            // Convert "val != NULL" to "val IS NOT NULL"
                            $op = 'IS NOT';
                        }
                    }


                    // Database operators are always uppercase
                    $op = strtoupper( $op );

                    if ( $op === 'BETWEEN' AND is_array( $value ) )
                    {
                        // BETWEEN always has exactly two arguments
                        list($min, $max) = $value;

                        if ( is_string( $min ) )
                        {
                            // Quote the value, it is not a parameter
                            $min = $db->quote( $min );
                        }

                        if ( is_string( $max ) )
                        {
                            // Quote the value, it is not a parameter
                            $max = $db->quote( $max );
                        }

                        // Quote the min and max value
                        $value = $min . ' AND ' . $max;
                    }
                    elseif ( is_string( $value ) )
                    {
                        // Quote the value, it is not a parameter
                        $value = $db->quote( $value );
                    }

                    if ( $column )
                    {
                        if ( is_array( $column ) )
                        {
                            // Use the column name
                            $column = $db->_escape_identifiers( reset( $column ) );
                        }
                        else
                        {
                            // Apply proper quoting to the column
                            $column = $db->_escape_identifiers( $column );
                        }
                    }

                    // Append the statement to the query
                    $sql .= trim( $column . ' ' . $op . ' ' . $value );
                }

                $last_condition = $condition;
            }
        }

        return $sql;
    }

    /**
     * Compiles an array of ORDER BY statements into an SQL partial.
     *
     * @param   object  Database instance
     * @param   array   sorting columns
     * @return  string
     */
    protected function _compile_order_by( $db, array $columns )
    {

        $sort = array();
        foreach ( $columns as $group )
        {
            list ($column, $direction) = $group;

            if ( is_array( $column ) )
            {
                // Use the column alias
                $column = $db->_escape_identifiers( end( $column ) );
            }
            else
            {
                // Apply proper quoting to the column
                $column = $db->_escape_identifiers( $column );
            }

            if ( $direction )
            {
                // Make the direction uppercase
                $direction = ' ' . strtoupper( $direction );
            }

            $sort[] = $column . $direction;
        }

        return 'ORDER BY ' . implode( ', ', $sort );
    }

    /**
     * Compiles an array of GROUP BY columns into an SQL partial.
     *
     * @param   object  Database instance
     * @param   array   columns
     * @return  string
     */
    protected function _compile_group_by( $db, array $columns )
    {
        $group = array();

        foreach ( $columns as $column )
        {
            if ( is_array( $column ) )
            {
                // Use the column alias
                $column = $db->_escape_identifiers( end( $column ) );
            }
            else
            {
                // Apply proper quoting to the column
                $column = $db->_escape_identifiers( $column );
            }

            $group[] = $column;
        }

        return 'GROUP BY ' . implode( ', ', $group );
    }

}

?>