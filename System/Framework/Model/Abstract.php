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
 * @file        Abstract.php
 *
 */
abstract class Model_Abstract extends Loader
{
    protected $_metadataDefinitions = array(
        'baseTable'        => array(
            'pageid'           => array(
                'type'    => 'int',
                'length'  => 10,
                'default' => 0,
                'index'   => true ),
            'clickanalyse'     => array(
                'type'    => 'tinyint',
                'length'  => 1,
                'default' => 0 ),
            'searchable'       => array(
                'type'    => 'tinyint',
                'length'  => 1,
                'default' => 1 ),
            'language'         => array(
                'type'    => 'char',
                'length'  => 6,
                'default' => '' ), // the base Language
            'languagefallback' => array(
                'type'    => 'tinyint',
                'length'  => 1,
                'default' => 1 ),
            'activemenuitemid' => array(
                'type'    => 'int',
                'length'  => 10,
                'default' => 0 ),
            'published'        => array(
                'type'    => 'tinyint',
                'length'  => 2,
                'default' => 1,
                'index'   => true ),
            'publishon'        => array(
                'type'    => 'int',
                'length'  => 11,
                'default' => 0,
                'index'   => true ),
            'publishoff'       => array(
                'type'    => 'int',
                'length'  => 11,
                'default' => 0,
                'index'   => true ),
            'indexfollow'      => array(
                'type'    => 'varchar',
                'length'  => 15,
                'default' => 1 ),
            'target'           => array(
                'type'    => 'varchar',
                'length'  => 10,
                'default' => '' ),
            'cacheable'        => array(
                'type'    => 'tinyint',
                'length'  => 1,
                'default' => 0 ),
            'cachetime'        => array(
                'type'    => 'int',
                'length'  => 8,
                'default' => 0 ),
            'cachegroups'      => array(
                'type'     => 'varchar',
                'length'   => 250,
                'default'  => '',
                'datatype' => 'split' ),
            'goto'             => array(
                'type'    => 'int',
                'length'  => 10,
                'default' => 0 ),
            /* since version 2.0 */
            'draft'            => array(
                'type'    => 'tinyint',
                'length'  => 1,
                'default' => 0 ),
            // add rollback if is a new document (don´t use for a existing document)
            'rollback'         => array(
                'type'    => 'tinyint',
                'length'  => 1,
                'default' => 0 ),
        ),
        'translationTable' => array(
            'lang'            => array(
                'type'    => 'char',
                'length'  => 6,
                'default' => '',
                'index'   => true ),
            'iscorelang'      => array(
                'type'    => 'tinyint',
                'length'  => 1,
                'default' => 0 ),
            'alias'           => array(
                'type'    => 'varchar',
                'length'  => 150,
                'default' => '',
                'index'   => true ), // @since version 2.0.1 moved to the alias registry
            'suffix'          => array(
                'type'    => 'varchar',
                'length'  => 6,
                'default' => '' ), // @since version 2.0.1 moved to the alias registry
            // @since version 2.0.1 added the alias registry id
            // 'rewriteid' => array('type' => 'int', 'length' => 10, 'default' => 0),
            'tags'            => array(
                'type'    => 'varchar',
                'length'  => 250,
                'default' => '' ),
            'pagetitle'       => array(
                'type'    => 'varchar',
                'length'  => 250,
                'default' => '' ),
            'metadescription' => array(
                'type' => 'text' ),
            'metakeywords'    => array(
                'type' => 'text' ),
            'draft'           => array(
                'type'    => 'tinyint',
                'length'  => 1,
                'default' => 0 ),
            'rollback'        => array(
                'type'    => 'tinyint',
                'length'  => 1,
                'default' => 0 ),
        )
    );

    protected static $_db = null;

    protected static $_dbadapter = null;

    /**
     * cache the modul instances
     *
     * @var array $_singletons
     */
    protected static $_singletons;

    public static $_modul;

    /**
     * This is an array of the current table's columns. This also contains the
     * current working data.
     * Most sub-classes will not hold the primary key in here, but those using
     * the 'multi' option (i.e. using LoadMulti() instead of Load()) will as they
     * need the primary key for each row.
     * This is a duplicate of the final variable $_columns (or an arrayed version if its a 'multi' class)
     *
     * @var array $data
     * @see Save
     * @see Load
     * @see ValidColumn
     */
    protected $_data = array();

    /**
     * This is an array of the current table's columns. This array stays blank and is used
     * as a table structure reference.
     * In the sub-classes it is declared as a final private variable with the table column names
     *
     * @var array $_columns
     * @see ResetColumns
     * @see ValidColumn
     */
    protected $_columns = array();

    /**
     * This is the primary key field name for the current working db table.
     *
     * @var array $primaryKey
     */
    protected $primaryKey = array();

    /**
     * TableName
     * This is the name of the current working database table without its prefix.
     * This is set in the child class declaration.
     *
     * @var string $TableName
     */
    protected $TableName = null;

	/**
	 * Translations TableName
	 * This is the name of the current working database table without its prefix.
	 * This is set in the child class declaration.
	 *
	 * @var string $TransTableName
	 */
	protected $TransTableName = null;

    /**
     * Hase the modul a Translation table
     *
     * @var bool $hasTranslation
     * @see canTranslation
     */
    protected $hasTranslation = false;

    /**
     *
     * @var array/null
     */
    protected $_params = null;

    /**
     * Modul model config
     * @var array
     */
    protected $_modelConfig;

    /**
     *
     * @var boolean
     */
    protected $_enableCreateSearchIndex = false;

    /**
     *
     * @var boolean
     */
    public $providerModulCall = false;


    // Core definitions
    public $tableTransMetaDefinition = null;
    public $tableMainMetaDefinition = null;


    /**
     * @var array
     */
    protected static $table_options = array();

    /**
     * @var array
     */
    protected static $table_fields = array();

    /**
     * @var array
     */
    protected static $table_cantrans = array();

    /**
     * @var array
     */
    protected static $table_primary_key = array();

    /**
     * @var array
     */
    protected static $translation_table_primary_key = array();

    protected static $translation_table_relationkey = array();

    /**
     * @var array
     */
    protected static $table_translation_fields = array();

    /**
     * @var array
     */
    protected static $table_search_fields = array();

    /**
     * @var string
     */
    private $default_primarykey = 'id';

    /**
     * @var string
     */
    private static $__tablename = '';


    private $modelInited = false;


    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $tableTranslationMetaDefinition = array();
        $tableCoreMetaFieldDefinition = array();
        include(DATA_PATH . 'system/meta_definition.php');

        $this->tableTransMetaDefinition = $tableTranslationMetaDefinition;
        $this->tableMainMetaDefinition = $tableCoreMetaFieldDefinition;
    }

    public function __destruct()
    {
        parent::__destruct();
    }


    /**
     *
     * @param string $method
     * @param $arguments
     * @throws BaseException
     * @internal param $mixed /array $arguments
     */
    public function __call( $method, $arguments )
    {
        throw new BaseException( 'Model `' . get_class( $this ) . '` has no method `' . $method . '`.' );
    }


    /**
     * @return $this
     */
    public function _initModel()
    {
        if ($this->modelInited)
        {
            return;
        }



        $this->loadModelConfig();

        $this->modelInited = true;

    }

    /**
     *
     */
    protected function loadModelConfig()
    {
        if ( !Model::$_modul )
        {
            throw new BaseException( 'Invalid Model instance to get the Model Config.' );
        }

        $className = (strpos( Model::$_modul, 'Addon_' ) === false ? ucfirst( strtolower( Model::$_modul ) ) . '_Config_Model' : Model::$_modul . '_Config_Model');

        // Read all permission options
        if ( checkClassMethod( $className.'/getConfig', 'static' ) )
        {
            $config = call_user_func( $className . '::getConfig', false );
            if ( is_array( $config ) )
            {
                $this->_modelConfig = $config;
                $this->initDefinition($config);
                unset( $config );
            }
        }
    }


    /**
     * return tablename without prefix placeholder %tp%
     * @param string $name
     * @return string
     */
    public function fixTableName($name)
    {
        return str_replace('%tp%', '', $name);
    }


    /**
     * @param null|string $tablename
     * @param bool $removetrans
     * @return null|string
     * @throws BaseException
     */
    protected function _getTblname($tablename = null, $removetrans = false)
    {
        if ($tablename === null)
        {
            $tablename = self::$__tablename;
        }

        if ( $removetrans && substr( $tablename, -6 ) === '_trans' )
        {
            $tablename = substr( $tablename, 0, -6 );
        }

        if (empty($tablename))
        {
            throw new BaseException('Tablename not defined! '. print_r(self::$table_options, true));
        }

        return $tablename;
    }

    /**
     *
     * structure:
     * array(
     *      'tablename' => array(
     *          'trans'       => true,
     *          'pk'          => 'tableid'
     *          'transpk'     => 'transid',
     *          'relationkey' => 'tableid',     // in translation table
     *          'useMetadata' => true,
     *          'sourcemode' => 'news/index'
     *          // fields in translation table
     *          'fields'    => array(
     *
     *              'title' => array(
     *                  'trans'     => true,
     *                  'type'      => 'varchar',
     *                  'length'    => 50,
     *                  'index'     => true,
     *                  'fulltext'  => ...,
     *                  'unique'    => ...
     *              ),
     *              'testfield' => array(
     *                  'trans'     => true,
     *                  'type'      => 'varchar',
     *                  'length'    => 150,
     *                  'index'     => false,
     *                  'fulltext'  => ...,
     *                  'unique'    => ...
     *              ),
     *              'testfield2' => array(
     *                  'trans'     => false,
     *                  'type'      => 'varchar',
     *                  'length'    => 250,
     *                  'index'     => false,
     *                  'fulltext'  => ...,
     *                  'unique'    => ...
     *              ),
     *              ...
     *          ),
     *
     *          'searchFields' => array(
     *               'titlefield'   => 'title',
     *               'contentfield' => 'testfield2'
     *          )
     *
     *      )
     * )
     *
     * @param array $config
     */
    protected function initDefinition(array $config)
    {



        foreach ($config as $tablename => $opts)
        {
            if (!is_string($tablename)) {
                continue;
            }
            $foundpk = false;
            $cantrans = false;

            if (isset($opts['pk']) && !empty($opts['pk']))
            {
                $foundpk = true;
                self::$table_primary_key[ $tablename ] = $opts['pk'];
            }

            if (isset($opts['transpk']) && !empty($opts['transpk']))
            {
                self::$translation_table_primary_key[ $tablename ] = $opts['transpk'];
            }

            if (isset($opts['relationkey']) && !empty($opts['relationkey']))
            {
                self::$translation_table_relationkey[ $tablename ] = $opts['relationkey'];
            }

            if (isset($opts['trans']) && !empty($opts['trans']))
            {
                $cantrans = true;
            }

            if (isset($opts['searchFields']) && is_array($opts['searchFields']))
            {
                self::$table_search_fields[$tablename] = $opts['searchFields'];
            }

            self::$table_cantrans[$tablename] = $cantrans;
            self::$table_fields[$tablename] = (isset($opts['fields']) && is_array($opts['fields']) ? $opts['fields'] : array());

            if (is_array(self::$table_fields[$tablename]))
            {
                foreach (self::$table_fields[$tablename] as $fieldname => $fieldoptions)
                {
                    if (!is_string($fieldname) || (isset(self::$table_translation_fields[$tablename]) && is_array(self::$table_translation_fields[$tablename]) && in_array($fieldname, self::$table_translation_fields[$tablename])) )
                    {
                        continue;
                    }

                    if (!isset($fieldoptions['isprimary']))
                    {
                        self::$table_translation_fields[$tablename][] = $fieldname;
                    }

                    if (isset($fieldoptions['trans']) && !empty($fieldoptions['trans']) || empty($fieldoptions['isprimary']) )
                    {
                        //self::$table_translation_fields[$tablename][] = $fieldname;
                    }

                    if (!$foundpk && isset($fieldoptions['isprimary']))
                    {
                        if (!empty($fieldoptions['isprimary']))
                        {
                            $foundpk = true;
                            self::$table_primary_key[ $tablename ] = $fieldname;
                        }
                    }
                }

                #self::$table_translation_fields[$tablename] = array_unique(self::$table_translation_fields[$tablename]);
            }

            // set default primary key if not found a primary key
            if ( !$foundpk )
            {
                self::$table_primary_key[ $tablename ] = $this->default_primarykey;
            }

            unset($opts['fields'], $opts['searchFields'], $opts['pk'], $opts['relationkey'], $opts['transpk'], $opts['trans'] );

            // the rest of table configuration
            self::$table_options[ $tablename ] = $opts;

        }

    }


    /**
     * @param string $name
     * @param null $tablename
     * @return null
     */
    public function getTableOption($name, $tablename = null) {
        $tablename = $this->_getTblname($tablename, true);
        $tablename = $this->fixTableName($tablename);

        if (isset(self::$table_options[ $tablename ]) && isset(self::$table_options[ $tablename ][$name])) {
            return self::$table_options[ $tablename ][$name];
        }

        return null;
    }

    /**
     * @param null $tablename
     * @return null
     */
    public function getTableOptions($tablename = null) {
        $tablename = $this->_getTblname($tablename, true);
        $tablename = $this->fixTableName($tablename);

        if (isset(self::$table_options[ $tablename ])) {
            return self::$table_options[ $tablename ];
        }

        return null;
    }


    /**
     * @param null $tablename
     */
    public function getTableConfig($tablename = null)
    {
        $tablename = $this->_getTblname($tablename, true);
        $tablename = $this->fixTableName($tablename);

    }


    /**
     * @param null|string $tablename
     * @return bool
     */
    public function allowTrans($tablename = null)
    {
        $tablename = $this->_getTblname($tablename, true);
        $tablename = $this->fixTableName($tablename);

        if (self::$table_cantrans[$tablename] && !empty(self::$table_cantrans[$tablename]) )
        {
            return true;
        }

        return false;
    }



    /**
     * @param null|string $tablename
     * @return bool|array
     */
    public function getTableDefinition($tablename)
    {
        $tablename = $this->_getTblname($tablename, true);
        $tablename = $this->fixTableName($tablename);

        if (isset(self::$table_fields[$tablename]))
        {
            return self::$table_fields[$tablename];
        }

        return false;
    }

    /**
     *
     * @param null|string $tablename
     * @return null|string
     */
    public function getTablePrimaryKey($tablename = null)
    {
        $tablename = $this->_getTblname($tablename, true);
        $tablename = $this->fixTableName($tablename);

        if (isset(self::$table_primary_key[$tablename]))
        {
            return self::$table_primary_key[$tablename];
        }

        return null;
    }

    /**
     *
     * @param null|string $tablename
     * @return null
     */
    public function getTranslationTableRelationKey($tablename = null)
    {
        $tablename = $this->_getTblname($tablename, true);
        $tablename = $this->fixTableName($tablename);

        if (isset(self::$translation_table_relationkey[$tablename]))
        {
            return self::$translation_table_relationkey[$tablename];
        }

        return null;
    }

    /**
     *
     * @param null|string $tablename
     * @return null|string
     */
    public function getTranslationTablePrimaryKey($tablename = null)
    {
        $tablename = $this->_getTblname($tablename, true);
        $tablename = $this->fixTableName($tablename);

        if (isset(self::$translation_table_primary_key[$tablename]))
        {
            return self::$translation_table_primary_key[$tablename];
        }

        return null;
    }





    /**
     * @param null|string $tablename
     * @return Model
     */
    public function useTable($tablename = null)
    {
        $tablename = $this->_getTblname($tablename, true);
        self::$__tablename = $this->fixTableName($tablename);

        return $this;
    }


    /**
     * @return string
     */
    public function getUsedTable() {
        return self::$__tablename;
    }


    /**
     * @param null|string $tablename
     * @param bool $setastable
     * @return Model
     * @throws BaseException
     */
    public function validateModel($tablename = null, $setastable = false )
    {
        $tablename = $this->fixTableName( $this->_getTblname($tablename, true) );

        if (!isset(self::$table_options[$tablename]))
        {
            throw new BaseException('The "'.$tablename.'" modul has no Model configuration! '. print_r( self::$table_fields, true ));
        }

        if ( $setastable )
        {
            return $this->useTable($tablename);
        }

        return $this;
    }



    /**
     * @param null|string $tablename
     * @return string
     * @throws BaseException
     */
    public function getTransTablename($tablename = null)
    {
        $tablename = $this->fixTableName( $this->_getTblname($tablename, true));
        return $tablename .'_trans';
    }


    /**
     * @param null|string $tablename
     * @return bool
     */
    public function getTableTranslationFields($tablename = null)
    {
        $tablename = $this->fixTableName( $this->_getTblname($tablename, true));

        if (isset(self::$table_translation_fields[$tablename]))
        {
            return self::$table_translation_fields[$tablename];
        }

        return false;
    }










    /**
     *
     * @param string $key
     * @return mixed
     */
    public function getModulDefinitionKey( $key )
    {
        $reg = $this->getApplication()->getModulRegistry( self::$_modul );
        return (isset( $reg[ $key ] ) ? $reg[ $key ] : null);
    }

    /**
     * will return all used fields in the core table or translation table
     *
     * @param boolean $useCoreTable
     * @return array
     */
    public function getMetatableDefinition( $useCoreTable = true )
    {
        if ( $useCoreTable )
        {
            return $this->_metadataDefinitions[ 'baseTable' ];
        }
        else
        {
            return $this->_metadataDefinitions[ 'translationTable' ];
        }
    }

    /**
     *
     * @param string $table
     * @return Model
     */
    public function setTable( $table )
    {
        if ( substr( $table, -6 ) === '_trans' )
        {
            $table = substr( $table, 0, -6 );
        }

        if ( substr( $table, 0, 4 ) === '%tp%' )
        {
            $table = substr( $table, 4 );
        }

        $this->TableName = $table;

        return $this;
    }

    /**
     *
     * @param string $key
     * @return mixed
     */
    public function getConfig( $key = null )
    {
        if ( $key !== null )
        {
            return isset( $this->_modelConfig[ $key ] ) ? $this->_modelConfig[ $key ] : null;
        }

        return $this->_modelConfig;
    }

    /**
     * return all translatable fields
     *
     * @param null|string $table
     * @return array/null
     */
    public function getTranslationFields( $table = null )
    {
        if ($this->allowTrans($table)) {
            return $this->getTableTranslationFields($table);
        }

        /*

                if ( $this->canTranslation( $table ) )
                {
                    $cfg = $this->getConfig( 'TranslationTables' );
                    return $cfg[ $table ];
                }
        */

        return null;
    }

    /**
     *
     * @return array
     */
    public function getTranslationTables()
    {
        if ($this->allowTrans())
        {
            return $this->getTableTranslationFields();
        }
        return null;
    }

    /**
     *
     * @param null|string $table
     * @return boolean
     */
    public function canTranslation( $table = null )
    {
        return $this->allowTrans($table);
    }


    /**
     * get the translation table relation key
     *
     * @param null|string $table
     * @return null|string
     */
    public function getRelationKey($table = null) {
        if ( $this->canTranslation( $table ) )
        {
            return $this->getTranslationTableRelationKey($table);
        }

        return null;
    }

    /**
     *
     * get the base table primarykey
     *
     * @param null|string $table
     * @return string/null
     */
    public function getPrimaryKey( $table = null )
    {
        if ( $this->canTranslation( $table ) )
        {
            return $this->getTranslationTablePrimaryKey($table);
        }
        else
        {
            return $this->getTablePrimaryKey($table);
        }

        return null;
    }


    /**
     * @param string $name
     * @param null|mixed $default
     * @return null|mixed returns null if not exists
     */
    public function getParam( $name, $default = null )
    {
        return (isset( $this->_params[ $name ] ) && !empty( $this->_params[ $name ] ) ? $this->_params[ $name ] : $default);
    }

    /**
     * @param string $name
     * @param null|mixed $value
     * @return $this
     */
    public function setParam( $name, $value = null )
    {
        $this->_params[ $name ] = $value;

        return $this;
    }

    /**
     * @param string $name
     * @param bool $debug
     * @return null
     * @throws BaseException
     */
    public function getRequiredParam( $name, $debug = false )
    {
        if ( $debug && !isset( $this->_params[ $name ] ) )
        {
            throw new BaseException( sprintf( trans( 'Das parameter `%s` für das Modul `%s` ist erforderlich aber wurde nicht übergeben!' ), $name, $this->getModul() ) );
        }
        elseif ( !isset( $this->_params[ $name ] ) )
        {
            return null;
        }


        return $this->_params[ $name ];
    }

    /**
     *
     */
    public function freeMem()
    {
        $this->_modelConfig = null;
        $this->_data = null;
        $this->TableName = null;
        $this->primaryKey = null;


        self::$_db = null;
        if ( self::$_modul )
        {
            self::$_singletons[ self::$_modul ] = null;
        }
    }

    /**
     *
     * @param string $modul
     * @return Model
     */
    public static function setModul( $modul )
    {
        self::$_modul = $modul;
    }

    /**
     * Returns the modul name
     * @return string
     */
    public function getModul()
    {
        return self::$_modul;
    }

    /**
     * @param int $id Item ID
     * @param bool|string $table
     * @return bool
     * @throws BaseException
     */
    public function hasTranslation( $id = 0, $table = false )
    {
        $table = str_replace('%tp%', '', ($table === false ? self::$__tablename : $table) );
        if ( !$table )
        {
            throw new BaseException( sprintf( 'Could not check if exists Translated Item from a empty table!' ) );
        }

        $relkey = $this->getRelationKey($table);

        if (empty($relkey)) {
            throw new BaseException( 'Table definition "'.$table.'" has no defined relation key!' );
        }


        $trans = $this->db->query( 'SELECT '.$relkey.' FROM %tp%' . $table . '_trans WHERE '.$relkey.' = ? AND lang = ?', $id, CONTENT_TRANS )->fetch();

        if ( $trans[ $relkey ] )
        {
            return true;
        }

        return false;
    }

    /**
     * will rollback the temporary translated Item
     *
     * @param int $id
     * @param bool $table
     * @return Database_Adapter_Pdo_RecordSet
     * @throws BaseException
     */
    public function rollbackTranslation( $id, $table = false )
    {
        $table = ($table === false ? self::$__tablename : $table);

        if ( !$table )
        {
            throw new BaseException( sprintf( 'Could not rollback the entry `ID: %s` in a empty table! Please set the tablename.', $id ) );
        }

        $relkey = $this->getRelationKey($table);
        if (empty($relkey)) {
            throw new BaseException( 'Table definition "'.$table.'" has no defined relation key!' );
        }

        return $this->db->query( 'DELETE FROM %tp%' . $table . '_trans WHERE `rollback` = 1 AND '.$relkey.' = ? AND lang = ?', $id, CONTENT_TRANS );
    }


    /**
     * Copy the original translation to other translation
     *
     * @param int $id
     * @param bool $table
     * @return bool
     * @throws BaseException
     */
    public function copyOriginalTranslation( $id, $table = false )
    {
        $table = ($table === false ? $this->TableName : $table);

        if ( !$table )
        {
            throw new BaseException( sprintf( 'Could not rollback the entry `ID: %s` in a empty table! Please set the tablename.', $id ) );
        }

        // get the relation key in the translation table
        $relkey = $this->getRelationKey($table);
        if ( empty($relkey) )
        {
            throw new BaseException( 'Table definition "'.$table.'" has no defined relation key!' );
        }

        $pk = $this->getTranslationTablePrimaryKey($table);


        $r = $this->db->query( 'SELECT lang FROM %tp%' . $table . '_trans WHERE '. $relkey .' = ? AND iscorelang = 1', $id )->fetch();
        if ( CONTENT_TRANS == $r[ 'lang' ] )
        {
            return false;
        }

        $trans = $this->db->query( 'SELECT * FROM %tp%' . $table . '_trans WHERE '. $relkey .' = ? AND lang = ?', $id, $r[ 'lang' ] )->fetch();
        $trans[ 'lang' ] = CONTENT_TRANS;
        $trans[ 'rollback' ] = 1;
        $trans[ 'iscorelang' ] = 0;

        if (isset($trans[$pk]) && $pk !== $relkey)
        {
            unset($trans[$pk]);
        }

        $trans[ 'iscorelang' ] = 0;

        /*
        $f = array();
        $fields = array();
        $values = array();
        foreach ( $trans as $key => $value )
        {
            $fields[] = $key;
            $f[] = '?';
            $values[] = $value;
        }
        */



        $this->db->insert($table .'_trans', $trans)->execute();

        //$this->db->query( 'INSERT INTO %tp%' . $table . '_trans (' . implode( ',', $fields ) . ') VALUES(' . implode( ',', $f ) . ')', $values );

        return true;
    }

    /**
     * @param int $id
     * @param array $coredata
     * @param null $transdata
     * @return int
     * @throws BaseException
     */
    public function save( $id = 0, $coredata = array(), $transdata = null )
    {

        if ( !self::$__tablename )
        {
            throw new BaseException( 'Could not save content to empty table! Please set the tablename before you can the content to a table.' );
        }

        if ( $transdata !== null && !isset( $transdata[ 'data' ] ) )
        {
            throw new BaseException( 'Could not save content! (`data`)' );
        }

        if ( $transdata !== null && !isset( $transdata[ 'controller' ] ) )
        {
            throw new BaseException( 'Could not save content! (`controller`)' );
        }

        $isTranslatable = $this->canTranslation( self::$__tablename );


        /**
         * @todo prepare table structure
         */



        $primarykey = $this->getTablePrimaryKey( self::$__tablename );
        if ( empty($primarykey) )
        {
            throw new BaseException( 'Table definition "'.self::$__tablename.'" has no defined primary key!' );
        }

        $relkey = $this->getRelationKey(self::$__tablename);
        if ( empty($relkey) )
        {
            throw new BaseException( 'Table definition "'.self::$__tablename.'" has no defined relation key!' );
        }

        if (!is_array($this->tableMainMetaDefinition))
        {
            throw new BaseException( 'Table definition "'.self::$__tablename.'" has no defined relation key!' );
        }

        if ( !is_array($this->tableTransMetaDefinition)  )
        {
            throw new BaseException( 'Table definition "'.self::$__tablename.'" has no defined relation key!' );
        }


        // save post as draft
        $savedraft  = (int)$this->input('savedraft');
        if ($this->db->fieldExists( self::$__tablename, 'draft' ) )
        {
            $coredata[ 'draft' ] = $savedraft ? 1 : 0;
        }


        // save meta informations
        $method = $this->Input->getMethod();
        if ( ($method == 'post' && $this->_post( 'documentmeta' )) || ($method == 'get' && $this->_get( 'documentmeta' )) )
        {
            $tmp      = array();
            $tmpTrans = array();

            /**
             * prepare date for the translation table
             */
            foreach ( $this->tableTransMetaDefinition as $fieldname => $fdata )
            {
                if ( isset( $data[ $fieldname ] ) )
                {
                    $tmpTrans[ $fieldname ] = $data[ $fieldname ];
                }
            }

            unset( $tmpTrans[ 'alias' ], $tmpTrans[ 'suffix' ] );
            unset( $tmpTrans[ 'lang' ], $tmpTrans[ 'iscorelang' ] );

            /**
             * prepare meta data for the main table
             */
            foreach ( $this->tableMainMetaDefinition as $fieldname => $fdata )
            {
                if (trim($fieldname)) {
                    if ($this->db->fieldExists( self::$__tablename, $fieldname )) {
                        $coredata[ $fieldname ] = ( isset( $coredata[ $fieldname ] ) ? $coredata[ $fieldname ] : ( ( $fdata[ 'type' ] == 'int' || $fdata[ 'type' ] == 'tinyint' ) ? 0 : '' ) );
                    }
                }
            }

            if ( is_array( $data[ 'cachegroups' ] ) && $this->db->fieldExists( self::$__tablename, 'cachegroups' ) )
            {
                $coredata[ 'cachegroups' ] = implode( ',', $data[ 'cachegroups' ] );
            }

            if ( isset( $data[ 'languagefallback' ] ) && isset( $data[ 'language' ] ) && empty( $coredata[ 'languagefallback' ] ) && trim( $coredata[ 'language' ] ) == '' )
            {
                $coredata[ 'languagefallback' ] = 1;
            }

            /**
             * prepare date for the main table
             */
            $coredata = $this->preparePublishingFields( $coredata, $data );
        }

        $controller = isset($transdata[ 'controller' ]) ? $transdata[ 'controller' ] : null;
        $alias = isset($transdata[ 'alias' ]) ? $transdata[ 'alias' ] : null;
        $suffix = isset($transdata[ 'suffix' ]) ? $transdata[ 'suffix' ] : null;
        $action = isset($transdata[ 'action' ]) ? $transdata[ 'action' ] : null;



        if (!$this->db->fieldExists( self::$__tablename, 'controller' )) {
            unset($coredata[ 'controller' ]);
        }
        if (!$this->db->fieldExists( self::$__tablename, 'alias' )) {
            unset($coredata[ 'alias' ]);
        }
        if (!$this->db->fieldExists( self::$__tablename, 'suffix' )) {
            unset($coredata[ 'suffix' ]);
        }
        if (!$this->db->fieldExists( self::$__tablename, 'action' )) {
            unset($coredata[ 'action' ]);
        }

        $_transData = null;

        if ( !$id )
        {
            if ( isset( $coredata[ $primarykey ] ) )
            {
                unset( $coredata[ $primarykey ] );
            }

            $this->db->insert(self::$__tablename)->values( $coredata )->execute();
            $id = $this->db->insert_id();
            $newid = $id;

            if ( $isTranslatable )
            {
                $_transData = array(
                    'table'       => self::$__tablename,
                    //'transfields' => $this->getTranslationFields( self::$__tablename ),
                    'id'          => $newid,
                    'isnew'       => true
                );

                if ( isset( $coredata[ 'contentid' ] ) )
                {
                    $_transData[ 'contentid' ] = $coredata[ 'contentid' ];
                }

                if ( is_array( $transdata ) )
                {
                    $_transData = array_merge( $transdata, $_transData );
                }
            }
        }
        else
        {
            $newid = $id;
            $this->db->update(self::$__tablename, $coredata )->where(($primarykey ? $primarykey : 'id'), '=', $id)->execute();

            if ( $isTranslatable )
            {
                $_transData = array(
                    'table'       => self::$__tablename,
                    //'transfields' => $this->getTranslationFields( self::$__tablename ),
                    'id'          => $id
                );

                if ( isset( $coredata[ 'contentid' ] ) )
                {
                    $_transData[ 'contentid' ] = $coredata[ 'contentid' ];
                }


                if ( is_array( $transdata ) )
                {
                    $_transData = array_merge( $transdata, $_transData );
                }
            }
        }


	    $modelTranslate = new Model_Translate();
        $modelTranslate->_initModel();

        if ( $isTranslatable )
        {
            if (!is_array($_transData))
            {
                throw new BaseException('Translation data not set for table "'.self::$__tablename.'_trans"!');
            }

            if ( count($tmpTrans) )
            {
                $_transData = array_merge( $_transData, $tmpTrans );
            }

            $modelTranslate->saveContentTranslation( $_transData );
        }


        if ( $alias && $controller && $action )
        {

            if (!is_string($suffix) || (is_string($suffix) && $suffix == '') )
            {
                $suffix = Settings::get('mod_rewrite_suffix', 'html');
            }


            $this->load( 'AliasRegistry' );
            $aliasid = $this->AliasRegistry->registerAlias(
                array(
                    'controller' => $controller,
                    'appid'      => 0,
                    'contentid'  => $newid,
                    'alias'      => $alias,
                    'suffix'     => $suffix,
                    'lang'       => CONTENT_TRANS,
                    'action'     => $action
                )
            );
        }




        if ( ($method == 'post' && $this->_post( 'documentmeta' )) || ($method == 'get' && $this->_get( 'documentmeta' )) )
        {
            $metaData = array(
                'table'      => self::$__tablename,
                'primarykey' => $primarykey,
                'id'         => $newid,
                'data'       => ($method == 'post' ? $this->_post( 'documentmeta' ) : $this->_get( 'documentmeta' ))
            );

            if ( isset( $coredata[ 'contentid' ] ) )
            {
                $metaData[ 'contentid' ] = $coredata[ 'contentid' ];
            }

            $modelTranslate->saveMainMetadata( $metaData );
        }

		// form will exit then unlock the document
	    if ( $this->input('exit') == 1 && $this->input('unlockaction') && $id )
	    {
		    $ac = preg_replace('#([^a-z0-9_]*)#is', '', $this->input('unlockaction') );
			if ($ac === $this->input('unlockaction') )
			{
				$this->load('ContentLock');
				$this->ContentLock->unlock($id, strtolower(CONTROLLER), strtolower($ac));

                /**
                 *
                 */
                define('SEND_UNLOCK', true);
			}
	    }


        unset( $_transData[ 'data' ], $_transData[ 'transfields' ] );


        return $newid;
    }



    /**
     * helper for saveMainMetadata
     * @param array $tmp
     * @param array $data
     * @return array
     */
    private function preparePublishingFields($tmp, &$data)
    {
        $on  = 0;
        $off = 0;

        if ( function_exists( 'date_parse_from_format' ) )
        {
            if ( !empty( $data[ 'publishon' ] ) && strpos( $data[ 'publishon' ], '.' ) !== false )
            {
                $on = date_parse_from_format( "d.m.Y", $data[ 'publishon' ] );
                $on = @mktime( 12, 0, 59, $on[ 'month' ], $on[ 'day' ], $on[ 'year' ] );
            }
            else if ( !empty( $data[ 'publishon' ] ) && strpos( $data[ 'publishon' ], '/' ) !== false )
            {
                $on = date_parse_from_format( "m/d/Y", $data[ 'publishon' ] );
                $on = @mktime( 12, 0, 59, $on[ 'month' ], $on[ 'day' ], $on[ 'year' ] );
            }


            if ( !empty( $data[ 'publishoff' ] ) && strpos( $data[ 'publishoff' ], '.' ) !== false )
            {
                $off = date_parse_from_format( "d.m.Y", $data[ 'publishoff' ] );
                $off = @mktime( 12, 0, 59, $off[ 'month' ], $off[ 'day' ], $off[ 'year' ] );
            }
            else if ( !empty( $data[ 'publishoff' ] ) && strpos( $data[ 'publishoff' ], '/' ) !== false )
            {
                $off = date_parse_from_format( "m/d/Y", $data[ 'publishoff' ] );
                $off = @mktime( 12, 0, 59, $off[ 'month' ], $off[ 'day' ], $off[ 'year' ] );
            }
        }
        else
        {
            if ( !empty( $data[ 'publishon' ] ) )
            {
                $on = @strtotime( $data[ 'publishon' ] );
            }
            if ( !empty( $data[ 'publishoff' ] ) )
            {
                $off = @strtotime( $data[ 'publishoff' ] );
            }
        }

        if ( isset( $tmp[ 'publishon' ] ) )
        {
            $tmp[ 'publishon' ] = $on;
        }

        if ( isset( $tmp[ 'publishoff' ] ) )
        {
            $tmp[ 'publishoff' ] = $off;
        }

        return $tmp;
    }


    /**
     * give the item with the translation
     * if $language is null returns the current translation by defined CONTENT_TRANS
     * @param int $id
     * @param null|string $language default is null
     * @param bool|string $_table default is false
     * @throws BaseException
     * @return array (record => ... , trans => ...)
     */
    public function getVersioningRecord( $id, $language = null, $_table = null )
    {
        $table = str_replace('%tp%', '', (!$_table ? $this->_getTblname() : $_table));


        $primarykey = $this->getTablePrimaryKey( $table );
        if ( empty($primarykey) )
        {
            throw new BaseException( 'Table definition "'. $table.'" has no defined primary key!' );
        }

        $relkey = $this->getRelationKey($table);
        if ( empty($relkey) )
        {
            throw new BaseException( 'Table definition "'.$table.'" has no defined relation key!' );
        }

        // @todo get customfields

        if ( $language === null )
        {
            $record = $this->db->query( 'SELECT n.* FROM %tp%' . $table . ' AS n WHERE n.'.$primarykey.' = ?', $id )->fetch();
            $trans = $this->db->query( 'SELECT t.* FROM %tp%' . $table . '_trans AS t WHERE t.'.$relkey.' = ? AND t.lang = ?', $id, CONTENT_TRANS )->fetch();
        }
        else
        {
            $record = $this->db->query( 'SELECT n.* FROM %tp%' . $table . ' AS n WHERE n.'.$primarykey.' = ?', $id )->fetch();
            $trans = $this->db->query( 'SELECT t.* FROM %tp%' . $table . '_trans AS t WHERE t.'.$relkey.' = ? AND t.lang = ?', $id, $language )->fetch();
        }


        return array(
            'record' => $record,
            'trans'  => $trans
        );
    }


    /**
     *
     * @param array $original
     * @param bool|string $table default is false
     * @return bool
     */
    public function createVersion( $original = array(), $table = null )
    {
        if ( !isset( $original[ 'record' ] ) )
        {
            return false;
        }

        $table = str_replace('%tp%', '', (!$table ? $this->_getTblname() : $table));

        $current = $this->getVersioningRecord( $original[ 'record' ][ 'id' ], $original[ 'trans' ][ 'lang' ], $table );

        if ( !is_array( $current[ 'trans' ] ) )
        {
            $current[ 'trans' ] = array();
        }


        if ( !$table )
        {
            // Versioning the currenct record if changed if not changed back
            $original[ 'record' ][ 'modifed_by' ] = $current[ 'record' ][ 'modifed_by' ];
            $original[ 'record' ][ 'modifed' ] = $current[ 'record' ][ 'modifed' ];
        }
        else
        {
            $original[ 'record' ][ 'password' ] = $current[ 'record' ][ 'password' ];
        }

        $result = array_diff( $original[ 'record' ], $current[ 'record' ] );

        // test translation diff
        $resultTrans = is_array($original[ 'trans' ]) ? array_diff( $original[ 'trans' ], $current[ 'trans' ] ) : array();

        // @todo add customfield diff
        // $resultCustomfields = is_array($original[ 'customfields' ]) ? array_diff( $original[ 'customfields' ], $current[ 'customfields' ] ) : array();

        if ( count( $resultTrans ) > 0 || count( $result ) > 0 /* || count( $resultCustomfields ) > 0*/ )
        {
            $versions = new Versioning();
            $versions->createVersion( $original[ 'record' ][ 'id' ], $table, $current[ 'record' ], $current[ 'trans' ] );
        }
    }


    /**
     * @param int|array $id
     * param bool|string $table default is false
     * @param null $table
     * @throws BaseException
     */
    public function delete( $id, $table = null )
    {
        if ( !self::$__tablename )
        {
            throw new BaseException( sprintf( 'Could not delete the entry `ID: %s` in a empty table! Please set the tablename.', $id ) );
        }

        $multi = false;
        if ( is_array($id) )
        {
            $id    = is_array( $id ) ? implode( ',', $id ) : $id;
            $multi = true;
        }


        $trash = Model::getModelInstance( 'Trash' );
        if ( $this->canTranslation( self::$__tablename ) )
        {
            
        }
    }


    /**
     * 
     * @param type $id
     * @return array 
     */
    public function getContentGalleryImage( $id )
    {
        return $this->db->query( 'SELECT * FROM %tp%contentimages WHERE imageid = ?', $id )->fetch();
    }

    /**
     * 
     * @param type $id
     * @return array 
     */
    public function removeContentGalleryImage( $id )
    {
        $this->db->query( 'DELETE FROM %tp%contentimages WHERE imageid = ?', $id );
    }

    /**
     * 
     * @param integer $id
     * @param array|string $imageIds
     * @return array 
     */
    public function getContentImages( $id, $imageIds )
    {

        if ( empty( $imageIds ) )
        {
            return array();
        }

        if ( is_string( $imageIds ) )
        {
            $imageIds = explode( ',', $imageIds );
        }

        $all = $this->db->query( 'SELECT * FROM %tp%contentimages WHERE contentid = ? AND imageid IN(0,' . implode( ',', $imageIds ) . ') ORDER BY ordering ASC', $id )->fetchAll();

        foreach ( $all as &$r )
        {

            // fix path
            if (substr($r[ 'filepath' ], 0, 4) == 'img/') {
                $r[ 'filepath' ] = PAGE_URL_PATH . $r[ 'filepath' ];
            }

        }
        return $all;
    }

}
