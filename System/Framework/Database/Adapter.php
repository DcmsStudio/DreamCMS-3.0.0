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
 * @file        Adapter.php
 *
 */
class Database_Adapter extends Database_Adapter_Abstract
{

    /**
     * @var null
     */
    protected $connection = null;

    // handle to Database connection
    /**
     * @var null
     */
    protected $_db = null;

    /**
     * @var string
     */
    protected $bind_marker = '?';

    /**
     * @var array
     */
    protected $fetchModes = array();

    /**
     * @var string
     */
    protected $fetchMode = 'assoc';

    /**
     * @var
     */
    protected $query;

    /**
     * @var
     */
    protected $result;

    /**
     * @var
     */
    protected $logFile;

    /**
     * @var
     */
    protected $fields;

    /**
     * @var
     */
    protected $fieldNames;

    /**
     * @var
     */
    protected $schemaNameField;

    /**
     * @var
     */
    protected $schemaTypeField;

    /**
     * @var bool
     */
    protected $addQuotes = true;
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
     * Adapter Construct
     *
     * @param array $config
     * @param string $adapter
     */
    public function __construct( $config, $adapter )
    {
        parent::__construct( $config );
        $this->_adapterConfig = $config;
        $this->_adapter = strtoupper( $adapter );
        $this->initAdapter();
        $this->_adapterConfig = null;
        unset( $config );
    }

    /**
     *
     */
    public function __clone()
    {
        
    }

    /**
     *
     */
    public function __destruct()
    {
        parent::__destruct();
        $this->_adapterConfig = null;
        $this->_adapter = null;
        $this->_db = null;
    }

    /**
     *
     * @return bool
     */
    public function initAdapter()
    {
        if ( isset( $this->_ValidAdapters[ $this->_adapter ] ) )
        {
            $name = 'Database_Adapter_' . ucfirst( strtolower( $this->_adapter ) );
            $this->_db = new $name();
            $this->_db->setAdapter( $this->_adapter );

            return $this->_db;
        }

        return false;
    }



}
