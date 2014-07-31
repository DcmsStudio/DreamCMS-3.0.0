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
 * @file        Delete.php
 *
 */
class Database_Query_Delete extends Database_Query_Where
{

    // DELETE FROM ...
    /**
     * @var
     */
    protected $_table;

    /**
     * Set the table for a delete.
     *
     * @param $table
     * @param null $db
     * @internal param \table $mixed name or array($table, $alias) or object
     * @return \Database_Query_Delete
     */
    public function __construct( $table, $db = null )
    {
        // Set the inital table name
        $this->_table = $table;

        // Start the query with no SQL
        parent::__construct( Database::DELETE, '' );
        $this->_dbInstance = $db;

        return $this;
    }

    /**
     * Sets the table to delete from.
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


        // Start a deletion query
        $query = 'DELETE FROM ' . $db->_escape_identifiers( $this->_table, true );

        if ( !empty( $this->_where ) )
        {
            // Add deletion conditions
            $query .= ' WHERE ' . $this->compile_conditions( $db, $this->_where );
        }

        return $query;
    }

    /**
     * @return $this
     */
    public function reset()
    {
        $this->_table = NULL;
        $this->_where = array();

        return $this;
    }

}

?>