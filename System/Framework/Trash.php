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
 * @file        Trash.php
 *
 */
class Trash
{

    /**
     * Current object instance (do not remove)
     * @var Trash
     */
    protected static $objInstance = null;

    public $db = null;

    protected $trashTable = null;

    protected $trashTableLabel = null;

    protected $trashTableData = null;

    protected $trashTableTransData = null;

    /**
     * Prevent cloning of the object (Singleton)
     */
    private function __clone()
    {
        
    }

    /**
     * Return the current object instance (Singleton)
     *
     * @return Trash
     */
    public static function getInstance()
    {
        if ( !is_object( self::$objInstance ) )
        {
            self::$objInstance = new Trash();
            self::$objInstance->db = Database::getInstance();
        }

        return self::$objInstance;
    }

    /**
     * Set Table
     * @param string $tbl
     * @return Trash
     */
    public function setTrashTable( $tbl )
    {
        $this->trashTable = $tbl;
        return $this;
    }

    /**
     * Set Table Label
     * @param string $tbllable
     * @return Trash
     */
    public function setTrashTableLabel( $tbllable )
    {
        $this->trashTableLabel = $tbllable;
        return $this;
    }

    /**
     *
     *
     * @param array $tblData
     * @return Trash
     */
    public function setTrashData( $tblData = null )
    {
        $this->trashTableData = $tblData;
        return $this;
    }

    /**
     *
     *
     * @param array $tblData
     * @return Trash
     */
    public function setTrashTransData( $tblData = null )
    {
        $this->trashTableTransData = $tblData;
        return $this;
    }

    /**
     *
     * @param array $tblData
     * @return Trash
     */
    public function addTrashData( $tblData )
    {
        if ( !is_array( $this->trashTableData ) )
        {
            $this->trashTableData = array();
        }
        $this->trashTableData[] = $tblData;

        return $this;
    }

    /**
     * Move all trashTableData to Trash
     *
     * @return bool
     */
    public function moveToTrash()
    {

        if ( is_null( $this->trashTable ) )
        {
            Error::raise( trans( 'Es wurde nicht angegeben, aus welcher Tabelle die Daten in den Papierkorb geschoben werden soll!' ) );
        }
        if ( is_null( $this->trashTableData ) )
        {
            Error::raise( sprintf( trans( 'Es wurden keine Daten aus der Tabelle `%s` ausgewählt, welche in den Papierkorb geschoben werden sollen!' ), $this->trashTable ) );
        }

        if ( is_array( $this->trashTableData ) )
        {
            $timestamp = time();
            $userid = User::getUserId();

            foreach ( $this->trashTableData as $idx => $row )
            {
                $label = (isset( $row[ 'label' ] ) ? trim( strip_tags( $row[ 'label' ] ) ) : '');
                if ( empty( $label ) && is_string( $row[ 'data' ] ) )
                {
                    $label = trim( strip_tags( $row[ 'data' ] ) );
                }

                if ( !$label )
                {
                    $label = sprintf( trans( 'Unbekannter Inhalt (%s)' ), str_replace( '%tp%', '', $this->trashTable ) );
                }

                if ( !isset( $row[ 'fieldsdata' ] ) )
                {
                    $row[ 'fieldsdata' ] = '';
                }

                $this->db->query( 'INSERT INTO %tp%trash (pageid, fromtable, typelabel, `datalabel`, `data`, trans_data, `fieldsdata`, `deletedate`, `userid`, appid) VALUES(?,?,?,?,?,?,?,?,?,?)', PAGEID, $this->trashTable, (!is_string( $this->trashTableLabel ) ? $this->trashTable : $this->trashTableLabel ), $label, (is_array( $row[ 'data' ] ) ? serialize( $row[ 'data' ] ) : $row[ 'data' ] ), (is_array( $row[ 'trans_data' ] ) ? serialize( $row[ 'trans_data' ] ) : $row[ 'trans_data' ] ), (is_array( $row[ 'fieldsdata' ] ) ? serialize( $row[ 'fieldsdata' ] ) : $row[ 'fieldsdata' ] ), $timestamp, $userid, intval( $row[ 'appid' ] )
                );
            }

            // free mem
            $this->trashTableData = null;

            return true;
        }

        return false;
    }

    /**
     *
     * @param integer $id
     */
    public function restore( $id )
    {

        if ( is_array( $id ) )
        {
            $result = $this->db->query( 'SELECT fromtable, datalabel, `data`, fieldsdata, appid FROM %tp%trash WHERE trashid IN(0,' . implode( ',', $id ) . ') AND pageid = ?', PAGEID )->fetchAll();

            $items = '';
            foreach ( $result as $rs )
            {
                $this->setTrashTable( $rs[ 'fromtable' ] );

                $str = null;
                if ( $rs[ 'data' ] )
                {
                    if ( substr( $rs[ 'data' ], 0, 2 ) == 'a:' )
                    {
                        $rs[ 'data' ] = unserialize( $rs[ 'data' ] );
                    }

                    $str = $this->db->compile_db_insert_string( $rs[ 'data' ] );
                    $this->db->query( "INSERT INTO " . $rs[ 'fromtable' ] . " ({$str[ 'FIELD_NAMES' ]}) VALUES({$str[ 'FIELD_VALUES' ]})" );
                }

                $str = null;
                if ( $rs[ 'fieldsdata' ] )
                {
                    if ( substr( $rs[ 'fieldsdata' ], 0, 2 ) == 'a:' )
                    {
                        $rs[ 'fieldsdata' ] = unserialize( $rs[ 'fieldsdata' ] );
                    }

                    if ( $rs[ 'appid' ] && is_array( $rs[ 'fieldsdata' ] ) )
                    {
                        foreach ( $rs[ 'fieldsdata' ] as $row )
                        {
                            $str = $this->db->compile_db_insert_string( $row );
                            $this->db->query( "INSERT INTO %tp%applications_fieldsdata ({$str[ 'FIELD_NAMES' ]}) VALUES({$str[ 'FIELD_VALUES' ]})" );
                        }
                    }
                    else
                    {
                        
                    }
                }

                $str = null;
                if ( $rs[ 'trans_data' ] )
                {
                    if ( substr( $rs[ 'trans_data' ], 0, 2 ) == 'a:' )
                    {
                        $rs[ 'trans_data' ] = unserialize( $rs[ 'trans_data' ] );
                    }

                    if ( is_array( $rs[ 'trans_data' ] ) )
                    {
                        foreach ( $rs[ 'trans_data' ] as $row )
                        {
                            $str = $this->db->compile_db_insert_string( $row );
                            $this->db->query( "REPLACE INTO " . $rs[ 'fromtable' ] . "_trans ({$str[ 'FIELD_NAMES' ]}) VALUES({$str[ 'FIELD_VALUES' ]})" );
                        }
                    }
                    else
                    {
                        
                    }
                }


                Library::log( sprintf( 'Restore the trash items `%s` type %s.', $rs[ 'datalabel' ], $rs[ 'typelabel' ] ) );
            }

            $this->db->query( 'DELETE FROM %tp%trash WHERE trashid IN(0,' . implode( ',', $id ) . ') AND pageid = ?', PAGEID );

            Library::sendJson( true, trans( 'Die Einträge wurden wiederhergestellt.' ) );
        }
        else
        {
            $rs = $this->db->query( 'SELECT datalabel, typelabel, fromtable, `data`, fieldsdata FROM %tp%trash WHERE trashid  = ?', $id )->fetch();
            $this->setTrashTable( $rs[ 'fromtable' ] );

            if ( $rs[ 'data' ] )
            {
                if ( substr( $rs[ 'data' ], 0, 2 ) == 'a:' )
                {
                    $rs[ 'data' ] = unserialize( $rs[ 'data' ] );
                }

                if ( is_array( $rs[ 'data' ] ) )
                {
                    $str = $this->db->compile_db_insert_string( $rs[ 'data' ] );
                    $this->db->query( "INSERT INTO " . $rs[ 'fromtable' ] . " ({$str[ 'FIELD_NAMES' ]}) VALUES({$str[ 'FIELD_VALUES' ]})" );
                }
            }

            if ( $rs[ 'fieldsdata' ] )
            {
                if ( substr( $rs[ 'fieldsdata' ], 0, 2 ) == 'a:' )
                {
                    $rs[ 'fieldsdata' ] = unserialize( $rs[ 'fieldsdata' ] );
                }

                if ( $rs[ 'appid' ] && is_array( $rs[ 'fieldsdata' ] ) )
                {
                    foreach ( $rs[ 'fieldsdata' ] as $row )
                    {
                        $str = $this->db->compile_db_insert_string( $row );
                        $this->db->query( "INSERT INTO %tp%applications_fieldsdata ({$str[ 'FIELD_NAMES' ]}) VALUES({$str[ 'FIELD_VALUES' ]})" );
                    }
                }
                else
                {
                    
                }
            }

            $this->db->query( 'DELETE FROM %tp%trash WHERE trashid  = ?', $id );

            Library::log( sprintf( 'Restore the trash items `%s` type %s.', $rs[ 'datalabel' ], $rs[ 'typelabel' ] ) );

            Library::sendJson( true, sprintf( trans( 'Der Eintrag `%s` wurde wiederhergestellt.' ), $rs[ 'datalabel' ] ) );
        }
    }

    /**
     *
     * @param string $idKey
     * @param string $multiIdKey
     * @return array (isMulti = bool, id)
     */
    private function getMultipleIds( $idKey, $multiIdKey )
    {
        $id = intval( HTTP::input( $idKey ) );
        $ids = HTTP::input( $multiIdKey ) ? explode( ',', HTTP::input( $multiIdKey ) ) : null;

        $multi = false;
        if ( is_array( $ids ) && count( $ids ) > 0 )
        {
            $multi = true;
            $id = implode( ',', $ids );
        }


        return array(
            'isMulti' => $multi,
            'id'      => $id );
    }

    /**
     *
     * @param string $idKey
     * @param string $multiIdKey
     * @param bool $mode default is false
     */
    public function delete( $idKey, $multiIdKey, $mode = false )
    {
        $data = $this->getMultipleIds( $idKey, $multiIdKey );

        if ( !$data[ 'id' ] && !$data[ 'isMulti' ] && !$mode )
        {
            Error::raise( "Invalid ID" );
        }

        if ( !$mode )
        {
            if ( $data[ 'isMulti' ] )
            {
                $this->db->query( "DELETE FROM %tp%trash WHERE trashid IN(0," . $data[ 'id' ] . ") AND pageid = ?", PAGEID );
                Library::log( sprintf( 'Has empty the Trash Items `%s`!', $data[ 'id' ] ) );
            }
            else
            {
                $r = $this->db->query( 'SELECT datalabel %tp%trash WHERE trashid = ?', $data[ 'id' ] );
                $this->db->query( 'DELETE FROM %tp%trash WHERE trashid = ?', $data[ 'id' ] );

                Library::log( sprintf( "Has remove the item `%s` from trash.", $r[ 'datalabel' ] ) );
                return $r[ 'datalabel' ];
            }
        }
        else
        {
            $this->db->query( "DELETE FROM %tp%trash WHERE pageid = ?", PAGEID );
            //$this->db->query('TRUNCATE TABLE %tp%trash');
            Library::log( 'Has empty the Trash!' );
        }
    }

}

?>