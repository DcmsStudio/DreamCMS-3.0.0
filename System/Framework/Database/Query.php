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
 * @file        Query.php
 *
 */
abstract class Database_Query
{

    // Query type
    /**
     * @var
     */
    protected $_type;

    // Cache lifetime
    /**
     * @var
     */
    protected $_lifetime;

    // SQL statement
    /**
     * @var
     */
    protected $_sql;

    // Quoted query parameters
    /**
     * @var array
     */
    protected $_parameters = array();

    // Return results as associative arrays or objects
    /**
     * @var bool
     */
    protected $_as_object = FALSE;

    /**
     * @var Database_Pdo
     */
    public $_dbInstance = null;

    /**
     * Creates a new SQL query of the specified type.
     *
     * @param $type
     * @param $sql
     * @internal param \query $integer type: Database::SELECT, Database::INSERT, etc
     * @internal param \query $string string
     * @return Database_Query
     */
    public function __construct( $type, $sql )
    {
        $this->_type = $type;
        $this->_sql = $sql;
    }



    /**
     *
     */
    public function __destruct()
    {
        
    }

    /**
     * Return the SQL query string.
     *
     * @throws BaseException
     * @return  string
     */
    final public function __toString()
    {
        try
        {
            // Return the SQL string
            return $this->compile( ($this->_dbInstance ? $this->_dbInstance : Database::getInstance()) );
        }
        catch ( Exception $e )
        {
            throw new BaseException( $e );
        }
    }

    /**
     * @param null $db
     * @return Database_Pdo|null
     */
    public function getDbInstance($db = null ) {
        if ( $this->_dbInstance === null && is_string($db) && $db !== '' )
        {
            // Get the database instance
            /**
             * @var Database_Pdo
             */
            $this->_dbInstance = Database::getInstance( $db );
        }

        return $this->_dbInstance;
    }



    /**
     * Get the type of the query.
     *
     * @return  integer
     */
    public function type()
    {
        return $this->_type;
    }

	/**
	 * Utilitie to check if array is associative array.
	 * http://stackoverflow.com/questions/173400/php-arrays-a-good-way-to-check-if-an-array-is-associative-or-sequential/4254008#4254008
	 *
	 * @param array $array input array to check.
	 * @return boolean true if array is associative array with at least one key, else false.
	 */
	public function isAssoc($array)
	{
		return (bool) count(array_filter(array_keys($array), 'is_string'));
	}

	/**
	 * Create a proper column value arrays from incoming $columns and $values.
	 *
	 * @param array  $columns
	 * @param array  $values
	 * @return array($columns, $values)
	 */
	public function mapColumnsWithValues($columns, $values)
	{
		// If $values is null, then use $columns to build it up
		if (is_null($values)) {

			if ($this->isAssoc($columns)) {

				// Incoming is associative array, split it up in two
				$values = array_values($columns);
				$columns = array_keys($columns);

			} else {

				// Create an array of '?' to match number of columns
				for ($i = 0; $i < count($columns); $i++) {
					$values[] = '?';
				}
			}
		}

		return array($columns, $values);
	}

    /**
     * Compile the SQL query and return it. Replaces any parameters with their
     * given values.
     *
     * @param   object  Database instance
     * @return  string
     */
    public function compile( $db )
    {
        // Import the SQL locally
        $sql = $this->_sql;


        if ( !empty( $this->_parameters ) )
        {
            // Quote all of the values
            $values = array_map( array(
                $db,
                'quote' ), $this->_parameters );

            // Replace the values in the SQL
            $sql = strtr( $sql, $values );
        }

        return $sql;
    }

    /**
     * Execute the current query on the given database.
     *
     * @param bool $db Database instance name (default is false)
     * @return array
     */
    public function get($db = false)
    {
        if ( $this->_dbInstance === null && is_string($db) && $db !== '' )
        {
            // Get the database instance
            /**
             * @var Database_Pdo
             */
            $db = Database::getInstance( $db );

            // Compile the SQL query
            $sql = $this->compile( $db );

            // Execute the query
            return $db->query( $sql )->fetch();

        }
        else
        {
            // Compile the SQL query
            $sql = $this->compile( $this->_dbInstance );

            // Execute the query
            return $this->_dbInstance->query( $sql )->fetch();
        }
    }

    /**
     * Execute the current query on the given database.
     *
     * @param bool $db Database instance name (default is false)
     * @return array
     */
    public function getAll($db = false)
    {
        if ( $this->_dbInstance === null && is_string($db) && $db !== '' )
        {
            // Get the database instance
            /**
             * @var Database_Pdo
             */
            $db = Database::getInstance( $db );

            // Compile the SQL query
            $sql = $this->compile( $db );

            // Execute the query
            return $db->query( $sql )->fetchAll();

        }
        else
        {
            // Compile the SQL query
            $sql = $this->compile( $this->_dbInstance );

            // Execute the query
            return $this->_dbInstance->query( $sql )->fetchAll();
        }
    }

    /**
     * Execute the current query on the given database.
     *
     * @param bool $db Database instance name (default is false)
     * @return Database_PdoRecordSet
     */
    public function execute( $db = false )
    {
        if ( $this->_dbInstance === null && is_string($db) && $db !== '' )
        {
            // Get the database instance
            /**
             * @var Database_Pdo
             */
            $db = Database::getInstance( $db );

            // Compile the SQL query
            $sql = $this->compile( $db );

            // Execute the query
            return $db->query( $sql );

        }
        else
        {
            // Compile the SQL query
            $sql = $this->compile( $this->_dbInstance );

            // Execute the query
            return $this->_dbInstance->query( $sql );

            $cache_key = md5($sql);
        }
    }

}

?>