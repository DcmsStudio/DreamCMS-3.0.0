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
 * @package      DreamCMS
 * @version      3.0.0 Beta
 * @category     Framework
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Model.php
 */
class Model extends Model_Abstract
{

	/**
	 * TranslationTableName
	 *
	 * @var string $TranslationTableName
	 */
	protected $TranslationTableName = '';

	/**
	 * This is the id number of the current db row held in the class.
	 * This number is the value of the primary key field of the current db table.
	 * This is a protected member variable and can only be changed from within
	 * the class to ensure its kept a valid integer
	 *
	 * @var integer $id
	 * @see SetId
	 * @see GetId
	 */
	protected $id = 0;

	/**
	 *
	 * @var array $_modulParams
	 */
	protected $_modulParams = array ();

	/**
	 * @var null
	 */
	protected static $__instance = null;


    public function __clone() {

    }


	/**
	 *
	 * @return Model
	 */
	public static function getInstance ( )
	{
		if ( self::$__instance === null )
		{
			self::$_dbadapter = Database::getInstance()->getAdapter();
			self::$__instance = new Model();
		}

		return self::$__instance;
	}

	/**
	 *
	 * @param bool $modul
	 * @throws BaseException
	 * @return object
	 */
	public static function &getModelInstance ( $modul = false )
	{
		if ( $modul === false )
		{
			$modul = defined('CONTROLLER') ? strtolower(CONTROLLER) : strtolower($GLOBALS[ 'tmp_CONTROLLER' ]);
		}

        if (!$modul)
        {
            throw new BaseException('Controller not inited!');
        }


		$modul      = ( stripos($modul, 'Addon_') !== false ? $modul : ucfirst(strtolower($modul)) );
        self::setModul($modul);



		$modelClass = '';
		if ( !isset( self::$_singletons[ $modul ] ) )
		{

			// Model Class
			$modelClass = $modul . '_Model_' . ucfirst( strtolower( Database::getInstance()->getAdapter() ));
			try
			{
				if ( class_exists($modelClass) )
				{
					/**
					 * @var $modelClass
					 */
					self::$_singletons[ $modul ] = new $modelClass;
				}
			}
			catch ( Exception $e )
			{
				throw new BaseException( $e->getMessage() );
			}
		}

		if ( !isset( self::$_singletons[ $modul ] ) )
		{
			throw new BaseException( 'Invalid model for "' . $modelClass . '" @' . $modul );
		}

		if ( isset( self::$_singletons[ $modul ] ) && is_object(self::$_singletons[ $modul ]) )
		{
			return self::$_singletons[ $modul ];
		}


		$ref = null;

		return $ref;

	}

	public function __destruct ()
	{
		self::$_singletons = self::$_dbadapter = null;
	}

	/**
	 *
	 * @return Model
	 */
	public function enableSearchIndexer ()
	{

		$this->_enableCreateSearchIndex = true;

		return $this;
	}

	/**
	 *
	 * @return Model
	 */
	public function disableSearchIndex ()
	{

		$this->_enableCreateSearchIndex = false;

		return $this;
	}

	/**
	 *
	 * @return array
	 */
	public function getTranslateFields ()
	{

		return array ();
	}

	/**
	 *
	 * @return string
	 */
	public function getModulLabel ()
	{

		return $this->getModulDefinitionKey('modulelabel');
	}

	/**
	 *
	 * @param array $params
	 */
	public function setModulParams ( $params = null )
	{

		$this->_params = $params;
	}

	/**
	 *
	 * @param string $table
	 * @return string
	 */
	public function prepareTableName ( $table )
	{

		if ( substr($table, 0, 4) != '%tp%' )
		{
			return '%tp%' . $table;
		}

		return $table;
	}

	/**
	 * Returns the value from $data of the requested field ($name).
	 * Whether or not $name is a valid field is also checked first.
	 *
	 * @param $name string The name of the field to get the data value for
	 * @return mixed The value of the field requested.
	 */
	public function getColumnData ( $name )
	{

		if ( $this->validColumn($name) && isset( $this->data[ $name ] ) )
		{
			return $this->_data[ $name ];
		}

		return false;
	}

	/**
	 * Public read-only accessor for the _columns protected member.
	 *
	 * @return array
	 */
	public function getColumns ()
	{

		return $this->_columns;
	}

	/**
	 * Takes a string value and checks it again the final class variable _columns that defines the table structure used
	 * and returns true if the column exists and false if it doesn't
	 *
	 * @param $col string The name of the column/field to check
	 * @return boolean True if it does exist, otherwise false
	 */
	public function validColumn ( $col )
	{

		return true; // hack for now, should not be this
		// return in_array( $col, $this->_columns );
	}

	/**
	 * This returns the integer current primary key value.
	 *
	 * @return integer The id number or primary key value of the data currently stored
	 */
	public function getId ()
	{

		return (int)$this->id;
	}

	/**
	 * This checks to see if the current Id is value. If its not a positive integer above zero, return false.
	 *
	 * @return boolean Whether or not the id number is a positive integer above zero
	 */
	public function validId ()
	{

		$id = (int)$this->getId();
		if ( $id > 0 )
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Sets the id or primary key value for the current class.
	 * This value must be a positive integer so it is checked before being set.
	 *
	 * @param $id integer The number value to set the ID to
	 * @return void
	 */
	public function setId ( $id )
	{

		$id = (int)$id;
		if ( $id > 0 )
		{
			$this->id = (int)$id;

			return true;
		}

		return false;
	}

	/**
	 * This sets the current data value of $name to be $value only if $name is a value column of this the current table.
	 * Also accepts an associative array of column/value pairs as the $name parameter.
	 *
	 * @param string|array $name  The name of the field to set/change the value of
	 * @param string       $value The value of the field to set/change
	 *                            (optional but required if $name is string, do not supply a value for this if $name is an array).
	 * @return void
	 * */
	public function setData ( $name, $value = '' )
	{

		if ( is_string($name) )
		{
			//	simple, single-value setter
			if ( $this->validColumn($name) )
			{
				$this->_data[ $name ] = $value;
			}
		}
		elseif ( is_array($name) || is_object($name) )
		{
			//	multi-value set
			foreach ( $name as $key => $value )
			{
				$this->setData($key, $value);
			}
		}
	}

	/**
	 *
	 * @return mixed
	 */
	public function getData ()
	{

		return $this->_data;
	}

	/**
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getDataById ( $id = null )
	{

		if ( !is_integer($id) || !intval($id) )
		{
			return array ();
		}

		$this->loadData($id);

		return $this->getData();
	}


	/**
	 * Load
	 * Loads the data from the database table based upon the current primary key name and value
	 *
	 * @param $id integer This is optional if the class has an ID set already, or if you want to override it use this.
	 * @return boolean
	 * */
	public function loadData ( $id = null )
	{

		if ( $id === null )
		{
			if ( $this->validId() )
			{
				$id = $this->getId();
			}
			else
			{
				return false;
			}
		}

		$this->TableName = $this->prepareTableName($this->TableName);

		$data = null;

		if ( $this->canTranslation($this->TableName) && $this->TranslationTableName != '' )
		{
			$transq1   = $this->buildTransWhere($this->TableName, 'n.' . $this->primaryKey, 't');
			$frontendQ = '';

			/**
			 * hide all draft items in the frontend
			 */
			if ( !$this->getApplication()->isBackend() )
			{
				$frontendQ = ' AND i.draft=0 AND t.draft=0';
			}

			$data = $this->db->query('SELECT i.*, t.* FROM %tp%' . $this->TableName . ' AS i
                                      LEFT JOIN %tp%' . $this->TranslationTableName . ' AS t ON(t.' . $this->primaryKey . ' = i.' . $this->primaryKey . ')
                                      WHERE ' . $this->primaryKey . ' = ?' . $frontendQ . ' AND ' . $transq1, (int)$id)->fetch();
		}
		else
		{
			$data = $this->db->query('SELECT * FROM %tp%' . $this->TableName . ' WHERE ' . $this->primaryKey . ' = ?', (int)$id)->fetch();
		}

		if ( is_array($data) )
		{
			$this->setId($id);
			$this->setData($data);
			$this->db->free();

			$data = null;

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * @param array $fields
	 * @param array $querys
	 * @return array

	public function getCount($fields, $querys, $exact = true)
	 * {
	 * if (empty($this->TableName))
	 * {
	 * Error::raise(sprintf('Model `%s` is missing it\'s $this->TableName declaration.', get_class($this)));
	 * }
	 * if (empty($q))
	 * {
	 * Error::raise(sprintf('Model `%s` is missing it\'s $q declaration.', get_class($this)));
	 * }
	 *
	 * $where = '';
	 * if (!empty($q))
	 * {
	 * if ($exact)
	 * {
	 * $where = " WHERE `" . $params['field'] . "` = '" . addslashes($params['query']) . "'";
	 * foreach ($fields as $field)
	 * {
	 * $where .= $field;
	 * }
	 * }
	 * else
	 * {
	 * $where = " WHERE `" . $params['field'] . "` LIKE '%" . addslashes($params['query']) . "%'";
	 * }
	 * }
	 *
	 * $result = self::$_db->query('SELECT COUNT(*) AS total FROM %tp%' . $this->TableName . $where)->fetch();
	 * return $result['total'];
	 * } */

	/**
	 *
	 * @param string $idKey
	 * @param string $multiIdKey
	 * @return array (isMulti = bool, id)
	 */
	public function getMultipleIds ( $idKey, $multiIdKey )
	{

		$id  = intval(HTTP::input($idKey));
		$ids = HTTP::input($multiIdKey) ? explode(',', HTTP::input($multiIdKey)) : null;

		$multi = false;
		if ( is_array($ids) && count($ids) > 0 )
		{
			$multi = true;
			$id    = implode(',', $ids);
		}


		return array (
			'isMulti' => $multi,
			'id'      => $id
		);
	}

	/**
	 * Will lock a document
	 *
	 * @param array $data array('location', 'title', 'table', 'pk', 'contentid', 'controller' ,'action')
	 * @use ContentLock
	 */
	public function lock ( $data )
	{

		Template::setLockAction($data[ 'action' ]);

		$table = $this->prepareTableName($data[ 'table' ]);
		$sql   = 'UPDATE ' . $table . ' SET locked = 1 WHERE ' . $data[ 'pk' ] . ' = ?';

		$this->db->query($sql, $data[ 'contentid' ]);

		$this->load('ContentLock');
		$this->ContentLock->lock($data);
	}


	/**
	 * @param integer $contentid
	 * @param string  $modul
	 * @param string  $modulaction
	 * @use ContentLock
	 */
	public function unlock ( $contentid, $modul, $modulaction )
	{

		$this->load('ContentLock');
		$this->ContentLock->unlock($contentid, $modul, $modulaction);
	}


    /**
     * @param integer $contentid
     * @param string $modul
     * @param string $modulaction
     * @return bool|string
     * @use ContentLock
     */
	public function checkLocked( $contentid, $modul, $modulaction )
	{
		$this->load('ContentLock');
		return $this->ContentLock->isLockedByUser($contentid, $modul, $modulaction);
	}


	/**
	 * Will auto unlock the dokument if click the save&exit button
	 *
	 * @param integer $contentid
	 * @param string  $modul
	 * @param string  $lockaction
	 */
	public function unlockDocument ( $contentid, $modul, $lockaction )
	{

		if ( intval($this->input('exit')) === 1 )
		{
			$this->load('ContentLock');
			if ( $this->ContentLock->isLocked($contentid, $modul, $lockaction) )
			{
				$this->ContentLock->unlock($contentid, $modul, $lockaction);

				$model = Model::getModelInstance($modul);
				$model->unlock($contentid, $lockaction);

                /**
                 *
                 */
                !defined('SEND_UNLOCK') or define( 'SEND_UNLOCK', true );
			}
		}
	}

}

?>