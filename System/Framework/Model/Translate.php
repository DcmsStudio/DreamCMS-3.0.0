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
 * @copyright    2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Translate.php
 *
 */
class Model_Translate extends Model_Abstract
{

    protected $contenttable = null;

    /*
    protected $tableTransMetaDefinition = null;

    protected $tableMainMetaDefinition = null;

    protected $translateableFields = null;

    protected $translateTable = null;
*/


    /***
     *
     *
     */

    public function __construct() {
        parent::__construct();
    }

    private function loadMetaDefiniton()
    {
        return;
/*
        if ( is_array( $this->tableTransMetaDefinition ) )
        {
            return;
        }

        $tableTranslationMetaDefinition = array();
        $tableCoreMetaFieldDefinition   = array();
        include( DATA_PATH . 'system/meta_definition.php' );

        $this->tableTransMetaDefinition = $tableTranslationMetaDefinition;
        $this->tableMainMetaDefinition  = $tableCoreMetaFieldDefinition;

*/
    }

    /**
     * @return array|null
     */
    public function getCoreTransMetaFields()
    {
        $this->loadMetaDefiniton();

        return $this->tableTransMetaDefinition;
    }

    /**
     * @return array|null
     */
    public function getCoreMetaFields()
    {
        $this->loadMetaDefiniton();

        return $this->tableMainMetaDefinition;
    }

    /**
     *
     * @param string $table
     * @internal param string $model
     * @return string or null
     */
    public function getTransMetaFields($table)
    {
        $fields = $this->getTableTranslationFields( $table );

        if ( $fields === false )
        {
            return null;
        }

        return $fields;
    }

    /**
     * find duplicates in translation table and return true if entry exists
     * @param string $table
     * @param string $where
     * @return bool
     */
    public function isDuplicate($table, $where = '')
    {
        if ( substr( $table, 0, 4 ) === '%tp%' )
        {
            $table = substr( $table, 4 );
        }

        if ( substr( $table, -6 ) == '_trans' )
        {
            $table = substr( $table, 0, -6 );
        }

        $pk = $this->getPrimaryKey( $table );
        if ( substr( $table, 0, 4 ) != '%tp%' )
        {
            $table = '%tp%' . $table;
        }

        if ( substr( $table, -6 ) != '_trans' )
        {
            $table = $table . '_trans';
        }

        $result = $this->db->query( 'SELECT ' . ( $pk !== null ? $pk : '*' ) . ' FROM ' . $table . ' WHERE ' . $where . ' AND lang = ?', CONTENT_TRANS );
        if ( $result->rowCount() > 0 )
        {
            return true;
        }

        return false;
    }

    /**
     * @param $modelname
     * @return bool
     */
    public function prepareMainTables($modelname)
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
     * @internal param string $model
     *
     * @return bool
     * @deprecated
     */
    public function prepareTranslationTables($modelname)
    {
        $model = Model::getModelInstance( $modelname );
        if ( !method_exists( $model, 'getTranslateFields' ) )
        {
            return false;
        }

        $this->loadMetaDefiniton();
        $tables = $model->getTranslationTables();

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
     *
     * @deprecated
     */
    public function getContentTranslation($contenttable, $where = '')
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
                $this->db->query( "INSERT INTO " . $contenttable . " ({$str['FIELD_NAMES']}) VALUES ({$str['FIELD_VALUES']})" );

                return $res;
            }
        }

        return $res;
    }

    /**
     * Save Translated Content
     *
     * @param array $params
     * @throws BaseException
     */
    public function saveContentTranslation($params)
    {

        if ( !is_array( $params ) )
        {
            Error::raise( 'Translation Error!!!' );
        }


        $transtable  = $params[ 'table' ];
        // $transfields = $params[ 'transfields' ]; // @todo remove it no longer used

        $id          = intval( $params[ 'id' ] );
        $isnew       = ( empty( $params[ 'isnew' ] ) || !$params[ 'isnew' ] ? false : true );
        $data        = $params[ 'data' ];
        $controller = $params[ 'controller' ];
        $action     = $params[ 'action' ];

        if ( !trim( $transtable ) || empty($transtable))
        {
            Error::raise( 'Translation Table is not set' );
        }


        if ( !trim( $controller ) || empty($controller) )
        {
            Error::raise( 'Controller is not set');
        }

        if ( !$id )
        {
            Error::raise( 'Item ID (relation id) for Translation Table is not set' );
        }

        if ( !is_array( $data ) )
        {
            Error::raise( 'Post Data for Translation Table is not set' );
        }


        $tmp = array();

        // get the relation key in the translation table
        $relkey = $this->getRelationKey($transtable);
        if ( empty($relkey) )
        {
            throw new BaseException( 'Table definition "'.$transtable.'" has no defined relation key!' );
        }


        $tmp[ $relkey ] = $id;

        // get primary key
        $pk = $this->getTranslationTablePrimaryKey( $transtable );

        $transtable = $this->fixTableName($transtable);

        // get the traslatable fields
        $transfields = $this->getTableTranslationFields( $transtable );



        $transfields = array_unique( $transfields );

        if ( !is_array( $transfields ) )
        {
            Error::raise( 'Translation Table Fields is not set' );
        }

        // get table field definitions
        $fielddefs = $this->getTableDefinition( $transtable );


        /**
         * Form fields must exactly have the name!
         */
        foreach ( $transfields as $idx => $fieldname )
        {
            if ( !is_string( $fieldname ) || $fieldname === $pk )
            {
                continue;
            }

            $fdata = $fielddefs[ $fieldname ];

            if ( strtolower( $fdata[ 'type' ] ) === 'int' || strtolower( $fdata[ 'type' ] ) === 'tinyint' )
            {
                $tmp[ $fieldname ] = isset( $data[ $fieldname ] ) ? (int)$data[ $fieldname ] : 0;
            }
            else
            {
                $tmp[ $fieldname ] = isset( $data[ $fieldname ] ) ? Library::maskContent( $data[ $fieldname ] ) : '';
            }
        }





        // remove primarykey
        if (isset($tmp[ $pk ]) && $relkey !== $pk )
        {
            unset($tmp[ $pk ]);
        }

        /**
         * add metadata to translation table
         */
        $this->loadMetaDefiniton();
        foreach ( $this->tableTransMetaDefinition as $fieldname => $fdata )
        {
            $tmp[ $fieldname ] = ( isset( $data[ $fieldname ] ) ? Library::maskContent( $data[ $fieldname ] ) : ( $fdata[ 'type' ] == 'int' || $fdata[ 'type' ] == 'tinyint' ? 0 : '' ) );
        }

        $tmp[ 'alias' ]  = ( isset( $params[ 'alias' ] ) ? $params[ 'alias' ] : $data[ 'alias' ] );
        $tmp[ 'suffix' ] = ( isset( $params[ 'suffix' ] ) ? $params[ 'suffix' ] : $data[ 'suffix' ] );

        if ( substr( $transtable, -6 ) != '_trans' )
        {
            $transtable = $transtable . '_trans';
        }

        // save post as draft
        $savedraft = (int)$this->input( 'savedraft' );
        if ( $this->db->fieldExists( $transtable, 'draft' ) && $savedraft )
        {
            $tmp[ 'draft' ] = 1;
        }

        if ( $isnew )
        {
            $tmp[ 'lang' ]       = CONTENT_TRANS;
            $tmp[ 'iscorelang' ] = 1;
            //$str                 = $this->db->compile_db_insert_string( $tmp );
            //$sql                 = 'INSERT INTO ' . $transtable . ' (' . $str[ 'FIELD_NAMES' ] . ') VALUES(' . $str[ 'FIELD_VALUES' ] . ')';
            //die($sql);
            //$this->db->query( $sql );

            $this->db->insert($transtable)->values($tmp)->execute();
        }
        else
        {

            unset( $tmp[ 'lang' ], $tmp[ 'iscorelang' ] );

            #$str = $this->db->compile_db_update_string( $tmp );
            #$sql = 'UPDATE ' . $transtable . ' SET ' . $str . ' WHERE `' . $pk . '` = ? AND `lang` = ?';


            $this->db->update($transtable, $tmp)->where($relkey, '=', $id)->where('lang', '=', CONTENT_TRANS)->execute();
            #$this->db->query( $sql, $id, CONTENT_TRANS );
        }


        /*
        // update the document alias
        $this->load( 'AliasRegistry' );
        $this->AliasRegistry->registerAlias(
            array(
                'controller' => $params[ 'controller' ],
                'appid'      => (int)$tmp[ 'appid' ],
                'contentid'  => ( isset( $params[ 'contentid' ] ) ? $params[ 'contentid' ] : $id ),
                'alias'      => $tmp[ 'alias' ],
                'suffix'     => $tmp[ 'suffix' ],
                'lang'       => CONTENT_TRANS,
                'action'     => $params[ 'action' ]
            )
        );
        */


        unset( $tmp );
    }


    /**
     * Copy Core Translation to current Content Locale
     * @param string $contenttable
     * @param string $where
     * @return void
     */
    public function copyTranslation($contenttable, $where = '')
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
                $this->db->query( "INSERT INTO " . $contenttable . " ({$str['FIELD_NAMES']}) VALUES ({$str['FIELD_VALUES']})" );
            }
        }
    }

    /**
     *
     * @param integer $id
     * @param string $table
     * @param string $pk
     * @return array/null
     */
    public function getTranslation($id, $table, $pk)
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

    /**
     * Save the Metadata to the Main Content Table
     * @param array $params
     * @throws BaseException
     */
    function saveMainMetadata(&$params)
    {
        $coretable = $params[ 'table' ];
        $coretable = $this->fixTableName($coretable);

        $data      = $params[ 'data' ];
        $id        = (int)$params[ 'id' ];
        $pk        = $params[ 'primarykey' ]; // key from main table

        if ( !trim( $coretable ) )
        {
            Error::raise( 'Main Meta Table is not set' );
        }

        if ( !trim( $pk ) )
        {
            Error::raise( 'Primary Key for the Main Meta Table is not set' );
        }

        if ( !is_array( $data ) )
        {
            Error::raise( 'Post Data for the Main Meta Table is not set' );
        }

        if ( !$id )
        {
            Error::raise( 'Item ID for the Main Meta Table is not set' );
        }



        $relkey = $this->getRelationKey($coretable);
        if ( empty($relkey) )
        {
            throw new BaseException( 'Table definition "'.$coretable.'" has no defined relation key!' );
        }



        /*
        $pk1 =  $this->getTablePrimaryKey($coretable);
        if ( empty($pk1) )
        {
            throw new BaseException( 'Table definition "'.$coretable.'" has no defined primary key!' );
        }

        if ($pk && $pk1 !== $pk)
        {
            throw new BaseException( 'Table definition "'.$coretable.'" has no defined primary key!' );
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
        */



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


        //$str = $this->db->compile_db_update_string( $tmpTrans );
        if ( count($tmpTrans) )
        {
            //$sql = 'UPDATE ' . $coretable . '_trans SET ' . $str . ' WHERE `' . $pk . '` = ? AND lang = ?';
            //$this->db->query( $sql, $id, CONTENT_TRANS );

            $this->db->update($coretable .'_trans', $tmpTrans)->where($relkey, '=', $id)->where('lang', '=', CONTENT_TRANS)->execute();
        }


        /**
         * prepare date for the main table
         */
        foreach ( $this->tableMainMetaDefinition as $fieldname => $fdata )
        {
            $tmp[ $fieldname ] = ( isset( $data[ $fieldname ] ) ? $data[ $fieldname ] : ( ( $fdata[ 'type' ] == 'int' || $fdata[ 'type' ] == 'tinyint' ) ? 0 : '' ) );
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
         *
         * if ( isset( $tmp[ 'draft' ] ) && Session::get( 'DraftLocation' ) && (int)HTTP::input( 'savedraft'  ) )
         * {
         * $tmp[ 'draft' ] = 1;
         * }
         *
         * if ( isset( $tmp[ 'draft' ] ) && Session::get( 'DraftLocation' ) && !HTTP::input( 'savedraft' ) )
         * {
         * $tmp[ 'draft' ] = 0;
         * }
         */


        // save post as draft
        $savedraft = (int)$this->input( 'savedraft' );
        if ( $this->db->fieldExists( $coretable, 'draft' )  )
        {
            $tmp[ 'draft' ] = $savedraft ? 1 : 0;
        }

        unset( $tmp[ 'pageid' ], $tmp[ $pk ] );

        //$str = $this->db->compile_db_update_string( $tmp );
        //$sql = 'UPDATE ' . $coretable . ' SET ' . $str . ' WHERE `' . $pk . '` = ?';
        //$this->db->query( $sql, $id );

        $this->db->update($coretable, $tmp)->where($pk, '=', $id)->execute();
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

}
