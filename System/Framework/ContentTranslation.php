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
 * @file        ContentTranslation.php
 *
 */
class ContentTranslation extends Loader
{

    /**
     * Current object instance (do not remove)
     * @var object
     */
    protected static $objInstance = null;

    /**
     * @var null
     */
    protected $contenttable = null;

    /**
     * @var null
     */
    protected $tableTransMetaDefinition = null;

    /**
     * @var null
     */
    protected $tableMainMetaDefinition = null;

    /**
     * @var null
     */
    protected $translateableFields = null;

    /**
     * @var null
     */
    protected $translateTable = null;

    /**
     * Prevent cloning of the object (Singleton)
     */
    final private function __clone()
    {
        
    }

    /**
     * Return the current object instance (Singleton)
     * @return object
     */
    public static function getInstance()
    {
        if ( self::$objInstance === null )
        {
            self::$objInstance = new ContentTranslation();
        }
        return self::$objInstance;
    }

    /**
     *
     * @return void
     */
    private function loadMetaDefiniton()
    {
        if ( is_array( $this->tableTransMetaDefinition ) )
        {
            return;
        }

        $tableTranslationMetaDefinition = array();
        $tableCoreMetaFieldDefinition = array();
        include(DATA_PATH . 'system/meta_definition.php');

        $this->tableTransMetaDefinition = $tableTranslationMetaDefinition;
        $this->tableMainMetaDefinition = $tableCoreMetaFieldDefinition;
    }

    /**
     * @return null
     */
    public function getCoreTransMetaFields()
    {
        $this->loadMetaDefiniton();
        return $this->tableTransMetaDefinition;
    }

    /**
     * @return null
     */
    public function getCoreMetaFields()
    {
        $this->loadMetaDefiniton();
        return $this->tableMainMetaDefinition;
    }

    /**
     *
     * @param string $model
     * @param string $table
     * @return string or null
     */
    public function getTransMetaFields( $model, $table )
    {
        $model = Model::getModelInstance( $model );
        if ( !method_exists( $model, 'getTranslateFields' ) )
        {
            return null;
        }

        $this->loadMetaDefiniton();
        $tables = $model->getTranslateFields();

        if ( isset( $tables[ $table ] ) )
        {
            $fields = array();
            foreach ( $tables[ $table ] as $fieldname => $data )
            {
                $fields[ $fieldname ] = $fieldname;
            }

            foreach ( $this->tableTransMetaDefinition as $fieldname => $data )
            {
                $fields[ $fieldname ] = $fieldname;
            }

            return $fields;
        }

        return null;
    }

    /**
     *
     * @param string $model
     * @param string $table
     * @return string or null
     */
    public function getPrimaryKey( $model, $table )
    {
        $model = Model::getModelInstance( $model );
        if ( !method_exists( $model, 'getTranslateFields' ) )
        {
            return null;
        }

        $tables = $model->getTranslateFields();
        if ( isset( $tables[ $table ] ) )
        {
            $fields = array();
            foreach ( $tables[ $table ] as $fieldname => $data )
            {
                if ( isset( $data[ 'isprimary' ] ) && $data[ 'isprimary' ] )
                {
                    return $fieldname;
                }
            }
        }

        return null;
    }

    /**
     * find duplicates in translation table and return true if entry exists
     *
     * @param        $model
     * @param string $table
     * @param string $where
     * @return bool
     */
    public function isDuplicate( $model, $table, $where = '' )
    {
        if ( substr( $table, 0, 4 ) === '%tp%' )
        {
            $table = substr( $table, 4 );
        }
        if ( substr( $table, -6 ) === '_trans' )
        {
            $table = substr( $table, 0, -6 );
        }

        $pk = $this->getPrimaryKey( $model, $table );
        $transFields = $this->getTransMetaFields( $model, $table );

        if ( substr( $table, 0, 4 ) !== '%tp%' )
        {
            $table = '%tp%' . $table;
        }

        if ( substr( $table, -6 ) !== '_trans' )
        {
            $table = $table . '_trans';
        }

        $result = $this->db->query( 'SELECT ' . ($pk !== null ? $pk : '*') . ' FROM ' . $table . ' WHERE ' . $where . ' AND lang = ?', CONTENT_TRANS );
        if ( $result->rowCount() > 0 )
        {
            return true;
        }

        return false;
    }

    /**
     *
     * @param string $modelname
     * @return boolean
     */
    public function prepareMainTables( $modelname )
    {
        $model = Model::getModelInstance( $modelname );
        if ( !method_exists( $model, 'getTranslateFields' ) )
        {
            return false;
        }


        $this->loadMetaDefiniton();

        $fields = $this->tableMainMetaDefinition;
        $tables = $model->getTranslateFields();
        foreach ( $tables as $table => $fields0 )
        {
            $prefixedTable = '%tp%' . $table;


            foreach ( $fields as $fieldname => $values )
            {
                if ( !$this->db->fieldExists( $prefixedTable, $fieldname ) )
                {

                    if ( $values[ 'type' ] === 'text' )
                    {
                        $defaults = '';
                        if ( empty( $values[ 'default' ] ) )
                        {
                            $defaults = 'NOT NULL DEFAULT \'' . $values[ 'default' ] . '\'';
                        }

                        $this->db->query( 'ALTER TABLE ' . $prefixedTable . ' ADD COLUMN `' . $fieldname . '` ' . strtoupper( $values[ 'type' ] ) );
                    }
                    else
                    {
                        $unsigned = '';
                        if ( $values[ 'type' ] === 'int' || $values[ 'type' ] === 'tinyint' )
                        {
                            $unsigned = ' UNSIGNED ';
                        }

                        $defaults = '';
                        if ( !empty( $values[ 'default' ] ) )
                        {
                            $defaults = 'NOT NULL DEFAULT \'' . $values[ 'default' ] . '\'';
                        }

                        if ( isset( $values[ 'index' ] ) && $values[ 'index' ] && !$unsigned )
                        {
                            $defaults .= ', KEY `' . $fieldname . '` (`' . $fieldname . '`)';
                        }

                        $this->db->query( 'ALTER TABLE ' . $prefixedTable . '
                                          ADD COLUMN `' . $fieldname . '` ' . strtoupper( $values[ 'type' ] ) . '(' . $values[ 'length' ] . ')
                                          ' . $unsigned . $defaults );
                    }
                }
            }
        }
    }

    /**
     * prepare all translation tables for the model
     *
     * @param $modelname
     * @return bool
     * @internal param string $model
     */
    public function prepareTranslationTables( $modelname )
    {
        $model = Model::getModelInstance( $modelname );
        if ( !method_exists( $model, 'getTranslateFields' ) )
        {
            return false;
        }

        $this->loadMetaDefiniton();
        $tables = $model->getTranslateFields();
        foreach ( $tables as $table => $fields )
        {
            $prefixedTable = '%tp%' . $table . '_trans';

            /**
             * Add translateable fields
             */
            foreach ( $fields as $fieldname => $values )
            {
                if ( !$this->db->fieldExists( $prefixedTable, $fieldname ) )
                {
                    if ( $values[ 'type' ] == 'text' )
                    {
                        $defaults = '';
                        if ( empty( $values[ 'default' ] ) )
                        {
                            $defaults = 'NOT NULL DEFAULT \'' . $values[ 'default' ] . '\'';
                        }

                        $this->db->query( 'ALTER TABLE ' . $prefixedTable . ' ADD COLUMN `' . $fieldname . '` ' . strtoupper( $values[ 'type' ] ) );
                    }
                    else
                    {
                        $unsigned = '';
                        if ( $values[ 'type' ] == 'int' || $values[ 'type' ] == 'tinyint' )
                        {
                            $unsigned = ' UNSIGNED ';
                        }

                        $defaults = '';
                        if ( !empty( $values[ 'default' ] ) )
                        {
                            $defaults = 'NOT NULL DEFAULT \'' . $values[ 'default' ] . '\'';
                        }


                        if ( isset( $values[ 'index' ] ) && $values[ 'index' ] )
                        {
                            $defaults .= ', KEY `' . $fieldname . '` (`' . $fieldname . '`)';
                        }

                        $this->db->query( 'ALTER TABLE ' . $prefixedTable . '
                                          ADD COLUMN `' . $fieldname . '` ' . strtoupper( $values[ 'type' ] ) . '(' . $values[ 'length' ] . ')
                                          ' . $unsigned . $defaults );
                    }
                }
            }


            /**
             * add the meta data fields
             */
            $fields = $this->tableTransMetaDefinition;
            foreach ( $fields as $fieldname => $data )
            {
                if ( !$this->db->fieldExists( $prefixedTable, $fieldname ) )
                {
                    if ( $values[ 'type' ] == 'text' )
                    {
                        $defaults = '';
                        if ( empty( $values[ 'default' ] ) )
                        {
                            $defaults = 'NOT NULL DEFAULT \'' . $values[ 'default' ] . '\'';
                        }

                        $this->db->query( 'ALTER TABLE ' . $prefixedTable . ' ADD COLUMN `' . $fieldname . '` ' . strtoupper( $values[ 'type' ] ) );
                    }
                    else
                    {
                        $unsigned = '';
                        if ( $values[ 'type' ] == 'int' || $values[ 'type' ] == 'tinyint' )
                        {
                            $unsigned = ' UNSIGNED ';
                        }

                        $defaults = '';
                        if ( !empty( $values[ 'default' ] ) )
                        {
                            $defaults = 'NOT NULL DEFAULT \'' . $values[ 'default' ] . '\'';
                        }

                        if ( isset( $values[ 'index' ] ) && $values[ 'index' ] )
                        {
                            $defaults .= ', KEY `' . $fieldname . '` (`' . $fieldname . '`)';
                        }

                        $this->db->query( 'ALTER TABLE ' . $prefixedTable . '
                                          ADD COLUMN `' . $fieldname . '` ' . strtoupper( $values[ 'type' ] ) . '(' . $values[ 'length' ] . ')
                                          ' . $unsigned . $defaults );
                    }
                }
            }
        }
    }

    /**
     * returns the translated content if not exists
     * @param string $contenttable
     * @param string $where
     * @return array
     */
    public function getContentTranslation( $contenttable, $where = '' )
    {
        if ( substr( $contenttable, 0, 4 ) != '%tp%' )
        {
            $contenttable = '%tp%' . $contenttable;
        }

        $coretable = $contenttable;

        if ( substr( $contenttable, -6 ) != '_trans' )
        {
            $contenttable = $contenttable . '_trans';
        }

        $res = array();
        if ( $this->db->fieldExists( $contenttable, 'lang' ) )
        {
            $res = $this->db->query( 'SELECT * FROM ' . $contenttable . ' WHERE lang = ? AND ' . $where, CONTENT_TRANS )->fetch();
        }

        if ( !$res[ 'lang' ] )
        {
            $r = $this->db->query( 'SELECT corelang FROM ' . $coretable . ' WHERE ' . $where )->fetch();
            if ( $r[ 'corelang' ] )
            {
                $res = $this->db->query( 'SELECT * FROM ' . $contenttable . ' WHERE lang = ? AND ' . $where, $r[ 'corelang' ] )->fetch();
                /**
                 * copy to new translation
                 */
                $res[ 'lang' ] = CONTENT_TRANS;

                $str = $this->db->compile_db_insert_string( $res );
                $this->db->query( "INSERT INTO " . $contenttable . " ({$str[ 'FIELD_NAMES' ]}) VALUES ({$str[ 'FIELD_VALUES' ]})" );
                return $res;
            }
        }

        return $res;
    }

    /**
     * Copy Core Translation to current Content Locale
     *
     * @param string $contenttable
     * @param string $where
     * @return void
     */
    public function copyTranslation( $contenttable, $where = '' )
    {
        if ( substr( $contenttable, 0, 4 ) != '%tp%' )
        {
            $contenttable = '%tp%' . $contenttable;
        }

        $coretable = $contenttable;

        if ( substr( $contenttable, -6 ) != '_trans' )
        {
            $contenttable = $contenttable . '_trans';
        }

        $res = array();
        if ( $this->db->fieldExists( $contenttable, 'lang' ) )
        {
            $res = $this->db->query( 'SELECT lang FROM ' . $contenttable . ' WHERE lang = ? AND ' . $where, CONTENT_TRANS )->fetch();
        }

        if ( !$res[ 'lang' ] )
        {
            $r = $this->db->query( 'SELECT corelang FROM ' . $coretable . ' WHERE ' . $where )->fetch();
            if ( $r[ 'corelang' ] )
            {
                $res = $this->db->query( 'SELECT * FROM ' . $contenttable . ' WHERE lang = ? AND ' . $where, $r[ 'corelang' ] )->fetch();
                /**
                 * copy to new translation
                 */
                $res[ 'lang' ] = CONTENT_TRANS;

                $str = $this->db->compile_db_insert_string( $res );
                $this->db->query( "INSERT INTO " . $contenttable . " ({$str[ 'FIELD_NAMES' ]}) VALUES ({$str[ 'FIELD_VALUES' ]})" );
            }
        }
    }

    /**
     * Save Translated Content
     *
     * @param $params
     * @internal param \type $transfields
     * @internal param \type $id
     * @internal param \type $data
     * @internal param \type $isNewtranslation
     */
    public function saveContentTranslation( $params )
    {
        $transtable = $params[ 'table' ];
        $transfields = $params[ 'transfields' ];
        $id = intval( $params[ 'id' ] );
        $isnew = (empty( $params[ 'isnew' ] ) || !$params[ 'isnew' ] ? false : true);
        $data = $params[ 'data' ];
        $controller = $params[ 'controller' ];


        if ( !trim( $transtable ) )
        {
            Error::raise( 'Translation Table is not set' );
        }

        if ( !is_array( $transfields ) )
        {
            Error::raise( 'Translation Table Fields is not set' );
        }

        if ( !trim( $controller ) )
        {
            Error::raise( 'Controller is not set' );
        }

        if ( !$id )
        {
            Error::raise( 'Item ID for Translation Table is not set' );
        }

        if ( !is_array( $data ) )
        {
            Error::raise( 'Post Data for Translation Table is not set' );
        }


        if ( substr( $transtable, 0, 4 ) != '%tp%' )
        {
            $transtable = '%tp%' . $transtable;
        }
        if ( substr( $transtable, -6 ) != '_trans' )
        {
            $transtable = $transtable . '_trans';
        }


        $tmp = array();
        $pk = null;
        foreach ( $transfields as $fieldname => $fdata )
        {
            if ( $pk === null && isset( $fdata[ 'isprimary' ] ) && $fdata[ 'isprimary' ] )
            {
                $pk = $fieldname;
            }

            if ( $fdata[ 'type' ] == 'int' || $fdata[ 'type' ] == 'tinyint' && isset( $data[ $fieldname ] ) )
            {
                $tmp[ $fieldname ] = intval( $data[ $fieldname ] );
            }
            else
            {
                $tmp[ $fieldname ] = isset( $data[ $fieldname ] ) ? $data[ $fieldname ] : '';
            }
        }

        if ( $pk !== null )
        {
            $tmp[ $pk ] = $id;
        }
        else
        {
            Error::raise( sprintf( 'Primary Field not exists for Table `%s`', $transtable ) );
        }


        /**
         * add metadata to translation table
         */
        $this->loadMetaDefiniton();
        foreach ( $this->tableTransMetaDefinition as $fieldname => $fdata )
        {
            $tmp[ $fieldname ] = (isset( $data[ $fieldname ] ) ? $data[ $fieldname ] : ($fdata[ 'type' ] == 'int' || $fdata[ 'type' ] == 'tinyint' ? 0 : ''));
        }

        $tmp[ 'alias' ] = $data[ 'alias' ];
        $tmp[ 'suffix' ] = $data[ 'suffix' ];

        if ( $isnew )
        {
            $tmp[ 'lang' ] = CONTENT_TRANS;
            $tmp[ 'iscorelang' ] = 1;
            $str = $this->db->compile_db_insert_string( $tmp );
            $sql = 'INSERT INTO ' . $transtable . ' (' . $str[ 'FIELD_NAMES' ] . ') VALUES(' . $str[ 'FIELD_VALUES' ] . ')';
            $this->db->query( $sql );
        }
        else
        {
            unset( $tmp[ 'lang' ], $tmp[ 'iscorelang' ] );

            $str = $this->db->compile_db_update_string( $tmp );
            $sql = 'UPDATE ' . $transtable . ' SET ' . $str . ' WHERE `' . $pk . '` = ? AND `lang` = ?';
            $this->db->query( $sql, $id, CONTENT_TRANS );
        }

        $this->load( 'AliasRegistry' );

        $this->AliasRegistry->registerAlias(
                array(
                    'controller' => $params[ 'controller' ],
                    'appid'      => intval( $tmp[ 'appid' ] ),
                    'contentid'  => $id,
                    'alias'      => $tmp[ 'alias' ],
                    'suffix'     => $tmp[ 'suffix' ],
                    'lang'       => CONTENT_TRANS,
                    'action'     => $params[ 'action' ]
                )
        );
    }

    /**
     * Save the Metadata to the Main Content Table
     * @param array $params
     */
    function saveMainMetadata( &$params )
    {
        $coretable = $params[ 'table' ];
        $data = $params[ 'data' ];
        $id = intval( $params[ 'id' ] );
        $pk = $params[ 'primarykey' ];

        if ( !trim( $coretable ) )
        {
            Error::raise( 'Main Meta Table is not set' );
        }

        if ( !trim( $pk ) )
        {
            Error::raise( 'Primary Key fot the Main Meta Table is not set' );
        }

        if ( !is_array( $data ) )
        {
            Error::raise( 'Post Data for the Main Meta Table is not set' );
        }

        if ( !$id )
        {
            Error::raise( 'Item ID for the Main Meta Table is not set' );
        }


        if ( substr( $coretable, -6 ) == '_trans' )
        {
            $coretable = substr( $coretable, 0, -6 );
        }

        if ( substr( $coretable, 0, 4 ) != '%tp%' )
        {
            $coretable = '%tp%' . $coretable;
        }

        $this->loadMetaDefiniton();
        $tmp = array();
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


        $str = $this->db->compile_db_update_string( $tmpTrans );
        if ( $str )
        {
            $sql = 'UPDATE ' . $coretable . '_trans SET ' . $str . ' WHERE `' . $pk . '` = ? AND lang = ?';
            $this->db->query( $sql, $id, CONTENT_TRANS );
        }


        /**
         * prepare date for the main table
         */
        foreach ( $this->tableMainMetaDefinition as $fieldname => $fdata )
        {
            $tmp[ $fieldname ] = (isset( $data[ $fieldname ] ) ? $data[ $fieldname ] : (($fdata[ 'type' ] == 'int' || $fdata[ 'type' ] == 'tinyint') ? 0 : ''));
        }


        $tmp = $this->preparePublishingFields( $tmp, $data );

        if ( is_array( $data[ 'cachegroups' ] ) )
        {
            $tmp[ 'cachegroups' ] = implode( ',', $data[ 'cachegroups' ] );
        }

        if ( isset( $data[ 'languagefallback' ] ) && isset( $data[ 'language' ] ) && empty( $tmp[ 'languagefallback' ] ) && trim( $tmp[ 'language' ] ) == '' )
        {
            $tmp[ 'languagefallback' ] = 1;
        }

        /**
         * change draft
         */
        if ( isset( $tmp[ 'draft' ] ) && Session::get( 'DraftLocation' ) && intval( HTTP::input( 'savedraft' ) ) )
        {
            $tmp[ 'draft' ] = 1;
        }

        if ( isset( $tmp[ 'draft' ] ) && Session::get( 'DraftLocation' ) && !HTTP::input( 'savedraft' ) )
        {
            $tmp[ 'draft' ] = 0;
        }


        unset( $tmp[ 'pageid' ] );

        $str = $this->db->compile_db_update_string( $tmp );
        $sql = 'UPDATE ' . $coretable . ' SET ' . $str . ' WHERE `' . $pk . '` = ?';
        $this->db->query( $sql, $id );
    }

    /**
     * helper for saveMainMetadata
     * @param array $tmp
     * @param array $data
     * @return array
     */
    private function preparePublishingFields( $tmp, &$data )
    {
        $on = 0;
        $off = 0;

        if ( function_exists( 'date_parse_from_format' ) )
        {
            if ( !empty( $data[ 'publishon' ] ) )
            {
                $on = date_parse_from_format( "d.m.Y, H:i", $data[ 'publishon' ] );
                $on = @mktime( $on[ 'hour' ], $on[ 'minute' ], 59, $on[ 'day' ], $on[ 'month' ], $on[ 'year' ] );
            }
            if ( !empty( $data[ 'publishoff' ] ) )
            {
                $off = date_parse_from_format( "d.m.Y, H:i", $data[ 'publishoff' ] );
                $off = @mktime( $off[ 'hour' ], $off[ 'minute' ], 59, $off[ 'day' ], $off[ 'month' ], $off[ 'year' ] );
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
     * @param $id
     * @param $table
     * @param $pk
     * @return mixed
     */
    public function getTranslation( $id, $table, $pk )
    {
        if ( !trim( $table ) )
        {
            Error::raise( 'Table is not set' );
        }

        if ( empty( $pk ) )
        {
            Error::raise( sprintf( 'Primary Key fot the Table `%s` is not set', $table ) );
        }

        if ( substr( $table, -6 ) == '_trans' )
        {
            $table = substr( $table, 0, -6 );
        }

        if ( substr( $table, 0, 4 ) != '%tp%' )
        {
            $table = '%tp%' . $table;
        }

        return $this->db->query( 'SELECT * FROM ' . $table . '_trans WHERE `' . $pk . '` = ? AND `lang` = ?', $id, CONTENT_TRANS )->fetch();
    }

}

?>