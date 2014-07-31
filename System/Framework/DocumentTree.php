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
 * @file        DocumentTree.php
 *
 */
class DocumentTree extends DocumentTree_Abstract
{

    /**
     * @var null|string
     */
    protected $_inputTableName = null;

    /**
     *
     * @param string $table
     * @param array $fields
     * @param boolean $enableTranslation
     */
    public function __construct( $table = "tree", $fields = array(
        'type' => 'type' ), $enableTranslation = false )
    {
        parent::__construct( $table, $fields );

        $this->enableTranslation = $enableTranslation;

        $this->_inputTableName = $table;
        $this->table = preg_replace( '/([\s\r\n\t]{1,})%tp%/', '$1' . $this->db->tp(), $table );

        if ( !$this->enableTranslation )
        {
            $this->add_fields = array(
                'type' => 'type' );
            $this->fields = array_merge( $this->fields, $this->add_fields );
        }
        else
        {
            $this->add_fields = array(
                'title' => 'title' );
        }
    }

    /**
     *
     * @param array $data
     * @return string
     */
    public function create_node( $data )
    {


        $id = $this->_create( (int) $data[ $this->fields[ "id" ] ], (int) $data[ $this->fields[ "ordering" ] ] );

        if ( is_numeric( $id ) )
        {

            $this->db->query( 'UPDATE %tp%page SET `type` = ? WHERE ' . $this->fields[ "id" ] . ' = ?', $data[ 'type' ], $id );


            $data[ $this->fields[ "id" ] ] = $id;

            $data[ 'alias' ] = '';
            $data[ 'suffix' ] = '';
            $data[ 'lang' ] = CONTENT_TRANS;
            $data[ 'languagefallback' ] = 1;
            $data[ 'searchable' ] = 1;
            $data[ 'published' ] = 1;
            $data[ 'indexfollow' ] = 1;


            $this->enableTranslation = true;
            $this->add_fields = array(
                'title'  => 'title',
                'alias'  => 'alias',
                'suffix' => 'suffix',
                'lang'   => 'lang'
            );

            // Set translation data
            $this->set_data( $data, true );

            return "{\"success\": true, \"status\" : 1, \"id\" : " . (int) $id . " }";
        }

        return "{\"success\": false, \"status\" : 0, \"msg\": 'Invalid " . $id . "' }";
    }

    /**
     *
     * @param array $data
     * @return string
     */
    public function rename_node( $data )
    {

        $this->enableTranslation = true;
        $this->add_fields = array(
            'title' => 'title' );

        $this->set_data( $data );

        return "{\"success\": true, \"status\" : 1}";
    }

    /**
     *
     * @param array $data
     * @param bool  $useInsert
     * @return string
     */
    public function set_data( $data, $useInsert = false )
    {
        if ( count( $this->add_fields ) == 0 )
        {
            return "{ \"success\": true, \"status\" : 1 }";
        }

        $trans = '';
        if ( $this->enableTranslation )
        {
            $trans = '_trans';
        }


        $s = (!$useInsert ? 'UPDATE' : 'REPLACE INTO') . " " . $this->table . $trans . " SET ";
        #if (!$useInsert)
        #{
        $s .= "`" . $this->fields[ "id" ] . "` = " . (int) $data[ $this->fields[ "id" ] ] . " ";
        #}


        foreach ( $this->add_fields as $k => $v )
        {
            if ( isset( $data[ $k ] ) )
            {
                $s .= ", `" . $this->add_fields[ $v ] . "` = " . $this->db->escape( $data[ $k ] ) . " ";
            }
            else
            {
                $s .= ", `" . $this->add_fields[ $v ] . "` = `" . $this->add_fields[ $v ] . "` ";
            }
        }

        if ( !$useInsert )
        {
            $s .= " WHERE `" . $this->fields[ "id" ] . "` = " . (int) $data[ $this->fields[ "id" ] ] . ' AND `lang` = ?';

            $this->db->query( $s, CONTENT_TRANS );
            return;
        }
        $this->db->query( $s );
        return;

    }

    /**
     *
     * @param array $data
     * @return string
     */
    public function move_node( $data )
    {
        $id = $this->_move( (int) $data[ "id" ], (int) $data[ "ref" ], (int) $data[ "ordering" ], (int) $data[ "copy" ] );
        if ( !$id )
        {
            return "{ \"success\": false, \"status\" : 0 }";
        }

        if ( (int) $data[ "copy" ] && count( $this->add_fields ) )
        {
            $ids = array_keys( $this->_get_children( $id, true ) );
            $data = $this->_get_children( (int) $data[ "id" ], true );

            $i = 0;

            $trans = '';
            if ( $this->enableTranslation )
            {
                $trans = '_trans';
            }


            foreach ( $data as $dk => $dv )
            {
                $s = "UPDATE " . $this->table . " SET `" . $this->fields[ "id" ] . "` = `" . $this->fields[ "id" ] . "` ";
                foreach ( $this->add_fields as $k => $v )
                {
                    if ( isset( $dv[ $k ] ) )
                        $s .= ", `" . $this->fields[ $v ] . "` =  " . $this->db->escape( $dv[ $k ] ) . " ";
                    else
                        $s .= ", `" . $this->fields[ $v ] . "` = `" . $this->fields[ $v ] . "` ";
                }
                $s .= "WHERE `" . $this->fields[ "id" ] . "` = " . $ids[ $i ];
                $this->db->query( $s );
                $i++;
            }
        }
        return "{ \"success\": true, \"status\" : 1, \"id\" : " . $id . " }";
    }

    /**
     *
     * @param array $data
     * @return jsonstring
     */
    public function remove_node( $data )
    {
        $ok = $this->_remove( (int) $data[ "id" ] );


        if ( $ok )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     *
     * @param array $data
     * @param boolean $recursive
     * @return jsonstring
     */
    public function get_children( $data, $recursive = false )
    {
        $tmp = $this->_get_children( (int) $data[ "id" ] );
        if ( (int) $data[ "id" ] === 1 && count( $tmp ) === 0 )
        {
            $this->_create_default();
            $tmp = $this->_get_children( (int) $data[ "id" ] );
        }

        $result = array();
        if ( (int) $data[ "id" ] === 0 )
        {
            return json_encode( $result );
        }

        foreach ( $tmp as $k => $v )
        {
            $css = '';

            if ( !$v[ 'mpublished' ] )
            {
                $css = 'disabled';
            }

            $result[] = array(
                "attr"  => array(
                    "id"    => "node_" . $k,
                    "rel"   => $v[ $this->fields[ "type" ] ],
                    'class' => $css ),
                "data"  => $v[ $this->fields[ "title" ] ],
                "state" => ((int) $v[ $this->fields[ "rgt" ] ] - (int) $v[ $this->fields[ "lft" ] ] > 1) ? "closed" : ""
            );
        }
        return json_encode( $result );
    }

    /**
     *
     * @param array $data
     * @return jsonstring
     */
    public function search( $data )
    {

        $trans = '';
        if ( $this->enableTranslation )
        {
            $trans = '_trans';
        }

        $res = $this->db->query( "SELECT `" . $this->fields[ "lft" ] . "`, `" . $this->fields[ "rgt" ] . "` FROM " . $this->table . $trans . " WHERE `" . $this->fields[ "title" ] . "` LIKE " . $this->db->escape( '%' . $data[ "search_str" ] . '%' ) . "" );


        if ( $res->count() === 0 )
        {
            return "[]";
        }


        $q = "SELECT DISTINCT `" . $this->fields[ "id" ] . "` FROM " . $this->table . " WHERE 0 ";

        $result = $res->fetchAll();
        foreach ( $result as $r )
        {
            $q .= " OR (`" . $this->fields[ "lft" ] . "` < " . (int) $r[ $this->fields[ "lft" ] ] . " AND `" . $this->fields[ "rgt" ] . "` > " . (int) $r[ $this->fields[ "rgt" ] ] . ") ";
        }
        $result = array();
        $search = $this->db->query( $q )->fetchAll();
        foreach ( $search as $r )
        {
            $result[] = "#node_" . $r[ $this->fields[ "id" ] ];
        }
        return json_encode( $result );
    }

    /**
     *
     * @param integer $nodeId
     * @return bool
     */
    public function hasChildren( $nodeId )
    {
        $tmp = $this->_get_children( (int) $nodeId );

        if ( count( $tmp ) )
        {
            return true;
        }

        return false;
    }

    public function _create_default()
    {
        
    }

}
