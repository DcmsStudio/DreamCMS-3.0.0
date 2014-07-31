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
 * @file        Join.php
 *
 */
class Database_Query_Join extends Database_Query_Builder
{

    // Type of JOIN
    /**
     * @var string
     */
    protected $_type;

    // JOIN ...
    /**
     * @var
     */
    protected $_table;

    // ON ...
    /**
     * @var array
     */
    protected $_on = array();

    /**
     * Creates a new JOIN statement for a table. Optionally, the type of JOIN
     * can be specified as the second parameter.
     *
     * @param   $table
     * @param   string  type of JOIN: INNER, RIGHT, LEFT, etc
     * @return \Database_Query_Join
     */
    public function __construct( $table, $type = NULL )
    {
        // Set the table to JOIN on
        $this->_table = $table;

        if ( $type !== NULL )
        {
            // Set the JOIN type
            $this->_type = (string) $type;
        }
    }

    /**
     * Adds a new condition for joining.
     *
     * @param   mixed   column name or array($column, $alias) or object
     * @param   string  logic operator
     * @param   mixed   column name or array($column, $alias) or object
     * @return  $this
     */
    public function on( $c1, $op, $c2 )
    {
        $this->_on[] = array(
            $c1,
            $op,
            $c2 );

        return $this;
    }

    /**
     * Compile the SQL partial for a JOIN statement and return it.
     *
     * @param   object  Database instance
     * @return  string
     */
    public function compile( $db = null )
    {

        if ($db === null) {
            $db = $this->getDbInstance();
        }




        if ( $this->_type )
        {
            $sql = strtoupper( $this->_type ) . ' JOIN';
        }
        else
        {
            $sql = 'JOIN';
        }

        // Quote the table name that is being joined
        $sql .= ' ' . $db->_escape_identifiers( $this->_table, true );

        $conditions = array();
        foreach ( $this->_on as $condition )
        {
            // Split the condition
            list($c1, $op, $c2) = $condition;

            if ( $op )
            {
                // Make the operator uppercase and spaced
                $op = ' ' . strtoupper( $op );
            }
            // Quote each of the columns used for the condition
            $conditions[] = $db->_escape_identifiers( $c1 ) . $op . ' ' . $db->_escape_identifiers( $c2 );
        }

        // Concat the conditions "... AND ..."
        $conditions = implode( ' AND ', $conditions );

        if ( count( $this->_on ) >= 1 )
        {
            // Wrap the conditions in a group. Some databases (Postgre) will fail
            // when singular conditions are grouped like this.
            $sql .= ' ON(' . $conditions . ')';
        }
        else
        {
            $sql .= $conditions;
        }

        return $sql;
    }

    public function reset()
    {
        $this->_type = $this->_table = NULL;

        $this->_on = array();
    }

}

?>