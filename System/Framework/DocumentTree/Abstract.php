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
abstract class DocumentTree_Abstract
{

    // Structure table and fields
    /**
     * @var mixed|string
     */
    protected $table = "";

    /**
     * @var array
     */
    public $fields = array(
        "id"       => "id",
        "parentid" => "parentid",
        "ordering" => "ordering",
        "lft"      => 'lft',
        "rgt"      => "rgt",
        "level"    => "level"
    );

    /**
     * @var null|string
     */
    protected $page = null;

    /**
     * @var bool
     */
    protected $enableTranslation = false;

    /**
     *
     * @param string $table
     * @param array $fields
     */
    public function __construct( $table = "tree", $fields = array() )
    {
        $this->db = Database::getInstance();


        $table = preg_replace( '/([\s\r\n\t]{1,})%tp%/', '$1' . $this->db->tp(), $table );
        $this->table = $table;
        $this->page = PAGEID;
        if ( !count( $fields ) )
        {
            foreach ( $this->fields as $k => &$v )
            {
                $v = $k;
            }
        }
        else
        {
            foreach ( $fields as $key => $field )
            {
                switch ( $key )
                {
                    case "id":
                    case "parentid":
                    case "ordering":
                    case "lft":
                    case "rgt":
                    case "level":
                        $this->fields[ $key ] = $field;
                        break;
                }
            }
        }
        // Database
        //$this->db = new _database;
    }

    /**
     *
     * @param bool|\type $useTranslation
     * @return string
     */
    public function getTable( $useTranslation = false )
    {
        if ( !$useTranslation )
        {
            return $this->table;
        }

        return $this->table . '_trans';
    }

    /**
     *
     * @return integer
     */
    public function page()
    {
        return PAGEID;
    }

    /**
     *
     * @param integer $id
     * @return array/boolean
     */
    public function _get_node( $id )
    {
        $result = $this->db->query( "SELECT * FROM " . $this->table . " WHERE " . $this->fields[ "id" ] . " = " . (int) $id )->fetch();
        return (!$result[ $this->fields[ "id" ] ] ? false : $result);
    }

    /**
     *
     * @param integer $id
     * @param boolean $recursive
     * @return array
     */
    public function _get_children( $id, $recursive = false )
    {
        $children = array();
        if ( $recursive )
        {
            $node = $this->_get_node( $id );
            $nodes = $this->db->query(
                            "SELECT * FROM " .
                            $this->table .
                            " WHERE `" . $this->fields[ "lft" ] . "` >= " . (int) $node[ $this->fields[ "lft" ] ] .
                            " AND `" . $this->fields[ "rgt" ] . "` <= " . (int) $node[ $this->fields[ "rgt" ] ] .
                            " AND pageid=" . $this->page .
                            " ORDER BY `" . $this->fields[ "lft" ] . "` ASC" )->fetchAll();
        }
        else
        {
            $nodes = $this->db->query(
                            "SELECT * FROM " .
                            $this->table .
                            " WHERE `" . $this->fields[ "parentid" ] . "` = " . (int) $id .
                            " AND pageid=" . $this->page .
                            " ORDER BY `" . $this->fields[ "ordering" ] . "` ASC" )->fetchAll();
        }


        foreach ( $nodes as $r )
        {
            $children[ $r[ $this->fields[ "id" ] ] ] = $r;
        }
        return $children;
    }

    /**
     *
     * @param integer $id
     * @param boolean $recursive
     * @return array
     */
    public function _get_children_nopage( $id, $recursive = false )
    {
        $children = array();
        if ( $recursive )
        {
            $node = $this->_get_node( $id );
            $nodes = $this->db->query(
                            "SELECT * FROM " . $this->table .
                            " WHERE `" . $this->fields[ "lft" ] . "` >= " . (int) $node[ $this->fields[ "lft" ] ] .
                            " AND `" . $this->fields[ "rgt" ] . "` <= " . (int) $node[ $this->fields[ "rgt" ] ] .
                            " ORDER BY `" . $this->fields[ "lft" ] . "` ASC" )->fetchAll();
        }
        else
        {
            $nodes = $this->db->query(
                            "SELECT * FROM " . $this->table .
                            " WHERE `" . $this->fields[ "parentid" ] . "` = " . (int) $id .
                            " ORDER BY `" . $this->fields[ "ordering" ] . "` ASC" )->fetchAll();
        }


        foreach ( $nodes as $r )
        {
            $children[ $r[ $this->fields[ "id" ] ] ] = $r;
        }
        return $children;
    }

    /**
     *
     * @param integer $id
     * @return array
     */
    public function _get_path( $id )
    {
        $node = $this->_get_node( $id );
        $path = array();
        if ( !$node === false )
        {
            return false;
        }

        $nodes = $this->db->query(
                        "SELECT `" . implode( "` , `", $this->fields ) .
                        "` FROM " . $this->table .
                        " WHERE `" . $this->fields[ "lft" ] . "` <= " . (int) $node[ $this->fields[ "lft" ] ] .
                        " AND `" . $this->fields[ "rgt" ] . "` >= " . (int) $node[ $this->fields[ "rgt" ] ] .
                        " AND " . $this->fields[ "parentid" ] . "=" . $this->page )->fetchAll();
        foreach ( $nodes as $r )
        {
            $path[ $r[ $this->fields[ "id" ] ] ] = $r;
        }
        return $path;
    }

    /**
     *
     * @param integer $parent
     * @param integer $position
     * @return boolean
     */
    public function _create( $parent, $position )
    {
        return $this->_move( 0, $parent, $position );
    }

    /**
     *
     * @param integer $id
     * @return boolean
     */
    public function _remove( $id )
    {
        if ( (int) $id === 1 )
        {
            return false;
        }

        $data = $this->_get_node( $id );
        $lft = (int) $data[ $this->fields[ "lft" ] ];
        $rgt = (int) $data[ $this->fields[ "rgt" ] ];
        $dif = $rgt - $lft + 1;


        // read all ids for translation before delete
        // deleting node and its children
        $this->db->query( "" .
                "DELETE FROM " . $this->table . " " .
                "WHERE `" . $this->fields[ "lft" ] . "` >= " . $lft . " AND `" . $this->fields[ "rgt" ] . "` <= " . $rgt
        );

        // shift left indexes of nodes right of the node
        $this->db->query( "" .
                "UPDATE " . $this->table . " " .
                "SET `" . $this->fields[ "lft" ] . "` = `" . $this->fields[ "lft" ] . "` - " . $dif . " " .
                "WHERE `" . $this->fields[ "lft" ] . "` > " . $rgt
        );

        // shift right indexes of nodes right of the node and the node's parents
        $this->db->query( "" .
                "UPDATE " . $this->table . " " .
                "SET `" . $this->fields[ "rgt" ] . "` = `" . $this->fields[ "rgt" ] . "` - " . $dif . " " .
                "WHERE `" . $this->fields[ "rgt" ] . "` > " . $lft
        );

        $pid = (int) $data[ $this->fields[ "parentid" ] ];
        $pos = (int) $data[ $this->fields[ "ordering" ] ];

        // Update position of siblings below the deleted node
        $this->db->query( "" .
                "UPDATE " . $this->table . " " .
                "SET `" . $this->fields[ "ordering" ] . "` = `" . $this->fields[ "ordering" ] . "` - 1 " .
                "WHERE `" . $this->fields[ "parentid" ] . "` = " . $pid . " AND `" . $this->fields[ "ordering" ] . "` > " . $pos
        );
        return true;
    }

    /**
     *
     * @param integer $id
     * @param integer $ref_id
     * @param integer $position
     * @param boolean $is_copy
     * @return integer/boolean returns integer if is a insert or copy of node
     */
    public function _move( $id, $ref_id, $position = 1, $is_copy = false )
    {
        if ( (int) $ref_id === 0 || (int) $id === 1 )
        {
            return false;
        }
        $sql = array(); // Queries executed at the end
        $node = $this->_get_node( $id ); // Node data
        $nchildren = $this->_get_children_nopage( $id ); // Node children
        $ref_node = $this->_get_node( $ref_id ); // Ref node data
        $rchildren = $this->_get_children_nopage( $ref_id ); // Ref node children

        $ndif = 2;
        $node_ids = array(
            -1 );
        if ( $node !== false )
        {
            $node_ids = array_keys( $this->_get_children_nopage( $id, true ) );

            // TODO: should be !$is_copy && , but if copied to self - screws some right indexes
            if ( in_array( $ref_id, $node_ids ) )
            {
                return false;
            }

            $ndif = $node[ $this->fields[ "rgt" ] ] - $node[ $this->fields[ "lft" ] ] + 1;
        }
        if ( $position >= count( $rchildren ) )
        {
            $position = count( $rchildren );
        }

        // Not creating or copying - old parent is cleaned
        if ( $node !== false && $is_copy == false )
        {
            $sql[] = "" .
                    "UPDATE " . $this->table . " " .
                    "SET `" . $this->fields[ "ordering" ] . "` = `" . $this->fields[ "ordering" ] . "` - 1 " .
                    "WHERE " .
                    "`" . $this->fields[ "parentid" ] . "` = " . $node[ $this->fields[ "parentid" ] ] . " AND " .
                    "`" . $this->fields[ "ordering" ] . "` > " . $node[ $this->fields[ "ordering" ] ];
            $sql[] = "" .
                    "UPDATE " . $this->table . " " .
                    "SET `" . $this->fields[ "lft" ] . "` = `" . $this->fields[ "lft" ] . "` - " . $ndif . " " .
                    "WHERE `" . $this->fields[ "lft" ] . "` > " . $node[ $this->fields[ "rgt" ] ];
            $sql[] = "" .
                    "UPDATE " . $this->table . " " .
                    "SET `" . $this->fields[ "rgt" ] . "` = `" . $this->fields[ "rgt" ] . "` - " . $ndif . " " .
                    "WHERE " .
                    "`" . $this->fields[ "rgt" ] . "` > " . $node[ $this->fields[ "lft" ] ] . " AND " .
                    "`" . $this->fields[ "id" ] . "` NOT IN (" . implode( ",", $node_ids ) . ") ";
        }
        // Preparing new parent
        $sql[] = "" .
                "UPDATE " . $this->table . " " .
                "SET `" . $this->fields[ "ordering" ] . "` = `" . $this->fields[ "ordering" ] . "` + 1 " .
                "WHERE " .
                "`" . $this->fields[ "parentid" ] . "` = " . $ref_id . " AND " .
                "`" . $this->fields[ "ordering" ] . "` >= " . $position . " " .
                ($is_copy ? "" : " AND `" . $this->fields[ "id" ] . "` NOT IN (" . implode( ",", $node_ids ) . ") ");

        $ref_ind = $ref_id === 0 ? (int) $rchildren[ count( $rchildren ) - 1 ][ $this->fields[ "rgt" ] ] + 1 : (int) $ref_node[ $this->fields[ "rgt" ] ];
        $ref_ind = max( $ref_ind, 1 );

        $self = ($node !== false && !$is_copy && (int) $node[ $this->fields[ "parentid" ] ] == $ref_id && $position > $node[ $this->fields[ "ordering" ] ]) ? 1 : 0;
        foreach ( $rchildren as $k => $v )
        {
            if ( $v[ $this->fields[ "ordering" ] ] - $self == $position )
            {
                $ref_ind = (int) $v[ $this->fields[ "lft" ] ];
                break;
            }
        }


        if ( $node !== false && !$is_copy && $node[ $this->fields[ "lft" ] ] < $ref_ind )
        {
            $ref_ind -= $ndif;
        }

        $sql[] = "" .
                "UPDATE " . $this->table . " " .
                "SET `" . $this->fields[ "lft" ] . "` = `" . $this->fields[ "lft" ] . "` + " . $ndif . " " .
                "WHERE " .
                "`" . $this->fields[ "lft" ] . "` >= " . $ref_ind . " " .
                ($is_copy ? "" : " AND `" . $this->fields[ "id" ] . "` NOT IN (" . implode( ",", $node_ids ) . ") ");
        $sql[] = "" .
                "UPDATE " . $this->table . " " .
                "SET `" . $this->fields[ "rgt" ] . "` = `" . $this->fields[ "rgt" ] . "` + " . $ndif . " " .
                "WHERE " .
                "`" . $this->fields[ "rgt" ] . "` >= " . $ref_ind . " " .
                ($is_copy ? "" : " AND `" . $this->fields[ "id" ] . "` NOT IN (" . implode( ",", $node_ids ) . ") ");

        $ldif = $ref_id == 0 ? 0 : $ref_node[ $this->fields[ "level" ] ] + 1;
        $idif = $ref_ind;
        if ( $node !== false )
        {
            $ldif = $node[ $this->fields[ "level" ] ] - ($ref_node[ $this->fields[ "level" ] ] + 1);
            $idif = $node[ $this->fields[ "lft" ] ] - $ref_ind;
            if ( $is_copy )
            {
                $sql[] = "" .
                        "INSERT INTO " . $this->table . " (" .
                        "`" . $this->fields[ "parentid" ] . "`, " .
                        "`" . $this->fields[ "ordering" ] . "`, " .
                        "`" . $this->fields[ "lft" ] . "`, " .
                        "`" . $this->fields[ "rgt" ] . "`, " .
                        "`" . $this->fields[ "level" ] . "`" .
                        ") " .
                        "SELECT " .
                        "" . $ref_id . ", " .
                        "`" . $this->fields[ "ordering" ] . "`, " .
                        "`" . $this->fields[ "lft" ] . "` - (" . ($idif + ($node[ $this->fields[ "lft" ] ] >= $ref_ind ? $ndif : 0)) . "), " .
                        "`" . $this->fields[ "rgt" ] . "` - (" . ($idif + ($node[ $this->fields[ "lft" ] ] >= $ref_ind ? $ndif : 0)) . "), " .
                        "`" . $this->fields[ "level" ] . "` - (" . $ldif . ") " .
                        "FROM " . $this->table . " " .
                        "WHERE " .
                        "`" . $this->fields[ "id" ] . "` IN (" . implode( ",", $node_ids ) . ") " .
                        "ORDER BY `" . $this->fields[ "level" ] . "` ASC";
            }
            else
            {
                $sql[] = "" .
                        "UPDATE " . $this->table . " SET " .
                        "`" . $this->fields[ "parentid" ] . "` = " . $ref_id . ", " .
                        "`" . $this->fields[ "ordering" ] . "` = " . $position . " " .
                        "WHERE " .
                        "`" . $this->fields[ "id" ] . "` = " . $id;
                $sql[] = "" .
                        "UPDATE " . $this->table . " SET " .
                        "`" . $this->fields[ "lft" ] . "` = `" . $this->fields[ "lft" ] . "` - (" . $idif . "), " .
                        "`" . $this->fields[ "rgt" ] . "` = `" . $this->fields[ "rgt" ] . "` - (" . $idif . "), " .
                        "`" . $this->fields[ "level" ] . "` = `" . $this->fields[ "level" ] . "` - (" . $ldif . ") " .
                        "WHERE " .
                        "`" . $this->fields[ "id" ] . "` IN (" . implode( ",", $node_ids ) . ") ";
            }
        }
        else
        {
            $sql[] = "" .
                    "INSERT INTO " . $this->table . " (" .
                    "`" . $this->fields[ "parentid" ] . "`, " .
                    "`" . $this->fields[ "ordering" ] . "`, " .
                    "`" . $this->fields[ "lft" ] . "`, " .
                    "`" . $this->fields[ "rgt" ] . "`, " .
                    "`" . $this->fields[ "level" ] . "`, pageid " .
                    ") " .
                    "VALUES (" .
                    $ref_id . ", " .
                    $position . ", " .
                    $idif . ", " .
                    ($idif + 1) . ", " .
                    $ldif . "," . PAGEID .
                    ")";
        }

        foreach ( $sql as $q )
        {
            $this->db->query( $q );
        }


        if ( $node == false || $is_copy )
        {
            $ind = $this->db->insert_id();
        }

        if ( $is_copy )
        {
            $this->_fix_copy( $ind, $position );
        }

        return ($node === false || $is_copy ? $ind : true);
    }

    /**
     *
     * @param integer $id
     * @param integer $position
     */
    public function _fix_copy( $id, $position )
    {
        $node = $this->_get_node( $id );
        $children = $this->_get_children_nopage( $id, true );

        $map = array();
        for ( $i = $node[ $this->fields[ "lft" ] ] + 1; $i < $node[ $this->fields[ "rgt" ] ]; $i++ )
        {
            $map[ $i ] = $id;
        }
        foreach ( $children as $cid => $child )
        {
            if ( (int) $cid == (int) $id )
            {
                $this->db->query(
                        "UPDATE " . $this->table .
                        " SET `" . $this->fields[ "ordering" ] . "` = " . $position .
                        " WHERE `" . $this->fields[ "id" ] . "` = " . $cid );
                continue;
            }
            $this->db->query(
                    "UPDATE " . $this->table .
                    " SET `" . $this->fields[ "parentid" ] . "` = " . $map[ (int) $child[ $this->fields[ "lft" ] ] ] .
                    " WHERE `" . $this->fields[ "id" ] . "` = " . $cid );
            for ( $i = $child[ $this->fields[ "lft" ] ] + 1; $i < $child[ $this->fields[ "rgt" ] ]; $i++ )
            {
                $map[ $i ] = $cid;
            }
        }
    }

    /**
     *
     */
    public function _reconstruct()
    {
        $this->db->query( 'DROP TABLE IF EXISTS `temp_tree`' );
        $this->db->query( 'DROP TABLE IF EXISTS `temp_stack`' );
        $this->db->query( 'DROP TABLE IF EXISTS `temp_tree2`' );


        $this->db->query( "" .
                "CREATE TABLE `temp_tree` (" .
                "`" . $this->fields[ "id" ] . "` INTEGER NOT NULL, " .
                "`" . $this->fields[ "parentid" ] . "` INTEGER NOT NULL, " .
                "`" . $this->fields[ "ordering" ] . "` INTEGER NOT NULL" .
                ") type=HEAP"
        );
        $this->db->query( "" .
                "INSERT INTO `temp_tree` " .
                "SELECT " .
                "`" . $this->fields[ "id" ] . "`, " .
                "`" . $this->fields[ "parentid" ] . "`, " .
                "`" . $this->fields[ "ordering" ] . "` " .
                "FROM " . $this->table
        );


        $this->db->query( "" .
                "CREATE TABLE `temp_stack` (" .
                "`" . $this->fields[ "id" ] . "` INTEGER NOT NULL, " .
                "`" . $this->fields[ "lft" ] . "` INTEGER, " .
                "`" . $this->fields[ "rgt" ] . "` INTEGER, " .
                "`" . $this->fields[ "level" ] . "` INTEGER, " .
                "`stack_top` INTEGER NOT NULL, " .
                "`" . $this->fields[ "parentid" ] . "` INTEGER, " .
                "`" . $this->fields[ "ordering" ] . "` INTEGER " .
                ") type=HEAP"
        );
        $counter = 2;
        $max = $this->db->query( "SELECT COUNT(*) AS total FROM temp_tree" )->fetch();

        $maxcounter = (int) $max[ 'total' ] * 2;
        $currenttop = 1;
        $this->db->query( "" .
                "INSERT INTO `temp_stack` " .
                "SELECT " .
                "`" . $this->fields[ "id" ] . "`, " .
                "1, " .
                "NULL, " .
                "0, " .
                "1, " .
                "`" . $this->fields[ "parentid" ] . "`, " .
                "`" . $this->fields[ "ordering" ] . "` " .
                "FROM `temp_tree` " .
                "WHERE `" . $this->fields[ "parentid" ] . "` = 0"
        );
        $this->db->query( "DELETE FROM `temp_tree` WHERE `" . $this->fields[ "parentid" ] . "` = 0" );

        while ( $counter <= $maxcounter )
        {
            $rs = $this->db->query( "" .
                            "SELECT " .
                            "`temp_tree`.`" . $this->fields[ "id" ] . "` AS tempmin, " .
                            "`temp_tree`.`" . $this->fields[ "parentid" ] . "` AS pid, " .
                            "`temp_tree`.`" . $this->fields[ "ordering" ] . "` AS lid " .
                            "FROM `temp_stack`, `temp_tree` " .
                            "WHERE " .
                            "`temp_stack`.`" . $this->fields[ "id" ] . "` = `temp_tree`.`" . $this->fields[ "parentid" ] . "` AND " .
                            "`temp_stack`.`stack_top` = " . $currenttop . " " .
                            "ORDER BY `temp_tree`.`" . $this->fields[ "ordering" ] . "` ASC LIMIT 1"
                    )->fetch();

            if ( $rs[ 'tempmin' ] )
            {
                $tmp = $rs[ 'tempmin' ];

                $q = "INSERT INTO temp_stack (stack_top, `" . $this->fields[ "id" ] . "`, `" . $this->fields[ "lft" ] . "`, `" . $this->fields[ "rgt" ] . "`, `" . $this->fields[ "level" ] . "`, `" . $this->fields[ "parentid" ] . "`, `" . $this->fields[ "ordering" ] . "`) VALUES(" . ($currenttop + 1) . ", " . $tmp . ", " . $counter . ", NULL, " . $currenttop . ", " . $rs[ 'pid' ] . ", " . $rs[ 'lid' ] . ")";
                $this->db->query( $q );
                $this->db->query( "DELETE FROM `temp_tree` WHERE `" . $this->fields[ "id" ] . "` = " . $tmp );
                $counter++;
                $currenttop++;
            }
            else
            {
                $this->db->query( "" .
                        "UPDATE temp_stack SET " .
                        "`" . $this->fields[ "rgt" ] . "` = " . $counter . ", " .
                        "`stack_top` = -`stack_top` " .
                        "WHERE `stack_top` = " . $currenttop
                );
                $counter++;
                $currenttop--;
            }
        }

        $temp_fields = $this->fields;


        $rs = $this->db->query( "SHOW FIELDS FROM " . $this->table )->fetchAll( 'assoc' );
        foreach ( $rs as $r )
        {
            $temp_fields[ $r[ 'Field' ] ] = $r[ 'Field' ];
        }


        unset( $temp_fields[ "parentid" ] );
        unset( $temp_fields[ "ordering" ] );
        unset( $temp_fields[ "lft" ] );
        unset( $temp_fields[ "rgt" ] );
        unset( $temp_fields[ "level" ] );


        if ( count( $temp_fields ) > 1 )
        {
            $this->db->query( "" .
                    "CREATE TABLE `temp_tree2` " .
                    "SELECT `" . implode( "`, `", $temp_fields ) . "` FROM " . $this->table
            );
        }

        # exit;
        $this->db->query( "TRUNCATE TABLE " . $this->table . "" );
        $this->db->query( "" .
                "INSERT INTO " . $this->table . " (" .
                "`" . $this->fields[ "id" ] . "`, " .
                "`" . $this->fields[ "parentid" ] . "`, " .
                "`" . $this->fields[ "ordering" ] . "`, " .
                "`" . $this->fields[ "lft" ] . "`, " .
                "`" . $this->fields[ "rgt" ] . "`, " .
                "`" . $this->fields[ "level" ] . "` " .
                ") " .
                "SELECT " .
                "`" . $this->fields[ "id" ] . "`, " .
                "`" . $this->fields[ "parentid" ] . "`, " .
                "`" . $this->fields[ "ordering" ] . "`, " .
                "`" . $this->fields[ "lft" ] . "`, " .
                "`" . $this->fields[ "rgt" ] . "`, " .
                "`" . $this->fields[ "level" ] . "` " .
                "FROM temp_stack " .
                "ORDER BY `" . $this->fields[ "id" ] . "`"
        );
        if ( count( $temp_fields ) > 1 )
        {
            $sql = "UPDATE " . $this->table . " v, `temp_tree2` SET v.`" . $this->fields[ "id" ] . "` = v.`" . $this->fields[ "id" ] . "` ";
            foreach ( $temp_fields as $k => $v )
            {
                if ( $k == $this->fields[ "id" ] )
                {
                    continue;
                }

                $sql .= ", v.`" . $v . "` = `temp_tree2`.`" . $v . "` ";
            }

            $sql .= " WHERE v.`" . $this->fields[ "id" ] . "` = `temp_tree2`.`" . $this->fields[ "id" ];
            $this->db->query( $sql );
        }
    }

    /**
     *
     * @return string
     */
    public function _analyze()
    {
        $report = array();

        $res = $this->db->query( "" .
                "SELECT " .
                "`" . $this->fields[ "lft" ] . "` FROM " . $this->table . " s " .
                "WHERE " .
                "`" . $this->fields[ "parentid" ] . "` = 0"
        );

        if ( $res->count() == 0 )
        {
            $report[] = "[FAIL]\tNo root node.";
        }
        else
        {
            $report[] = ($res->count() > 1) ? "[FAIL]\tMore than one root node." : "[OK]\tJust one root node.";
        }
        $report[] = ($res->count() != 1) ? "[FAIL]\tRoot node's left index is not 1." : "[OK]\tRoot node's left index is 1.";

        $res0 = $this->db->query( "" .
                        "SELECT " .
                        "COUNT(*) AS p FROM " . $this->table . " s " .
                        "WHERE " .
                        "`" . $this->fields[ "parentid" ] . "` != 0 AND " .
                        "(SELECT COUNT(*) FROM " . $this->table . " WHERE `" . $this->fields[ "id" ] . "` = s.`" . $this->fields[ "parentid" ] . "`) = 0 " )->fetch();

        $report[] = ($res0[ 'p' ] > 0) ? "[FAIL]\tMissing parents." : "[OK]\tNo missing parents.";

        $max = $this->db->query( "SELECT MAX(`" . $this->fields[ "rgt" ] . "`) AS m FROM " . $this->table )->fetch();
        $n = $max[ 'm' ];

        $res1 = $this->db->query( "SELECT COUNT(*) AS c FROM " . $this->table )->fetch();
        $c = $res1[ 'c' ];

        $report[] = ($n / 2 != $c) ? "[FAIL]\tRight index does not match node count." : "[OK]\tRight index matches count.";

        $res2 = $this->db->query( "" .
                        "SELECT COUNT(`" . $this->fields[ "id" ] . "`) AS a FROM " . $this->table . " s " .
                        "WHERE " .
                        "(SELECT COUNT(*) FROM " . $this->table . " WHERE " .
                        "`" . $this->fields[ "rgt" ] . "` < s.`" . $this->fields[ "rgt" ] . "` AND " .
                        "`" . $this->fields[ "lft" ] . "` > s.`" . $this->fields[ "lft" ] . "` AND " .
                        "`" . $this->fields[ "level" ] . "` = s.`" . $this->fields[ "level" ] . "` + 1" .
                        ") != " .
                        "(SELECT COUNT(*) FROM " . $this->table . " WHERE " .
                        "`" . $this->fields[ "parentid" ] . "` = s.`" . $this->fields[ "id" ] . "`" .
                        ") "
                )->fetch();

        $report[] = ($res2[ 'a' ] > 0) ? "[FAIL]\tAdjacency and nested set do not match." : "[OK]\tNS and AJ match";

        return implode( "<br />", $report );
    }

    /**
     *
     * @param boolean $output default is false
     * @return array
     */
    public function _dump( $output = false )
    {
        $nodes = array();
        $res = $this->db->query( "SELECT * FROM " . $this->table . " ORDER BY `" . $this->fields[ "lft" ] . "`" )->fetchAll();
        foreach ( $res as $r )
        {
            $nodes[] = $r;
        }

        if ( $output )
        {
            echo "<pre>";
            foreach ( $nodes as $node )
            {
                echo str_repeat( "&#160;", (int) $node[ $this->fields[ "level" ] ] * 2 );
                echo $node[ $this->fields[ "id" ] ] . " (" . $node[ $this->fields[ "lft" ] ] . "," . $node[ $this->fields[ "rgt" ] ] . "," . $node[ $this->fields[ "level" ] ] . "," . $node[ $this->fields[ "parentid" ] ] . "," . $node[ $this->fields[ "ordering" ] ] . ")<br />";
            }
            echo str_repeat( "-", 40 );
            echo "</pre>";
        }

        return $nodes;
    }

    /**
     * TRUNCATE table and
     * create new tree root
     */
    public function _drop()
    {
        $this->db->query( "TRUNCATE TABLE " . $this->table . "" );
        $this->db->query( "" .
                "INSERT INTO " . $this->table . " (" .
                "`" . $this->fields[ "id" ] . "`, " .
                "`" . $this->fields[ "parentid" ] . "`, " .
                "`" . $this->fields[ "ordering" ] . "`, " .
                "`" . $this->fields[ "lft" ] . "`, " .
                "`" . $this->fields[ "rgt" ] . "`, " .
                "`" . $this->fields[ "level" ] . "`, title, alias, suffix, `type` " .
                ") " .
                "VALUES (" .
                "1, " .
                "0, " .
                "0, " .
                "1, " .
                "2, " .
                "0, '', '', '', 'root' " .
                ")" );
    }

}
