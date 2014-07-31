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
 * @file        Tree.php
 *
 */
class Tree
{

    /**
     * @var string
     */
    private $_idColumnName = 'id';

    /**
     * @var string
     */
    private $_parentColumnName = 'parentid';

    /**
     * @var array
     */
    private $_data = array();

    /**
     * @var array
     */
    private $_dataArray = array();

    /**
     *
     * this node's id
     */
    protected $id;

    /**
     *
     * parent id
     */
    protected $parent;

    /**
     *
     * @param integer $id
     * @param integer $parent
     */
    public function __construct( $id = 0, $parent = 0 )
    {
        $this->_size = 0;
        $this->id = $id;
        $this->parent = $parent;
    }

    /**
     *
     * @param array    $fullData
     * @param string   $idColName
     * @param string   $parentColName
     * @param bool|int $isapp
     * @return $this
     */
    public function setupData( $fullData = array(), $idColName = 'id', $parentColName = 'parentid', $isapp = false )
    {

        if ( !empty( $idColName ) )
        {
            $this->_idColumnName = $idColName;
        }

        if ( !empty( $parentColName ) )
        {
            $this->_parentColumnName = $parentColName;
        }

        $children = array();
        $list = array();
        foreach ( $fullData as $idx => $r )
        {
            if ( isset( $r[ $parentColName ] ) )
            {

                if ( $isapp !== false )
                {
                    $r[ 'appid' ] = $isapp;
                }

                $r[ 'parent' ] = $r[ $parentColName ];
                $r[ 'id' ] = $r[ $idColName ];
                $pt = $r[ $parentColName ];
                $list = (isset( $children[ $pt ] ) ? $children[ $pt ] : array());
                array_push( $list, $r );

                if ( !is_array( $children ) )
                {
                    $children = array();
                }

                $children[ $pt ] = $list;
            }
        }


        $this->_data = $children;
        #   print_r($this->_data);

        return $this;
    }

    /**
     *
     * @param integer $id
     * @param string $indent
     * @param integer $maxlevel
     * @param integer $level
     * @param boolean $is_menu
     * @return array
     */
    public function buildRecurseArray( $id = 0, $indent = null, $maxlevel = 9999, $level = 0, $is_menu = false )
    {
        if ( isset( $this->_data[ $id ] ) && is_array( $this->_data[ $id ] ) && $level <= $maxlevel )
        {
            foreach ( $this->_data[ $id ] as $v )
            {
                $id = $v[ 'id' ];
                $txt = isset( $v[ 'name' ] ) ? $v[ 'name' ] : 'empty';
                $pt = $v[ 'parent' ];

                $indentsize = (int)$indent  ? (int)$indent  : 0;
                $w = $indentsize;

                if ( $level > 0 )
                {
                    $w = (20 * $level);
                }

                $this->_dataArray[ $id ] = $v;
                $this->_dataArray[ $id ][ 'parentlevel' ] = (isset( $this->_dataArray[ $pt ][ 'level' ] ) ? $this->_dataArray[ $pt ][ 'level' ] : 0);
                $this->_dataArray[ $id ][ 'nextlevel' ] = (isset( $this->_data[ $id ] ) && count( $this->_data[ $id ] ) > 0 ? $level + 1 : ($level > 0 ? $level - 1 : 0));
                $this->_dataArray[ $id ][ 'level' ] = $level;

                $txt = str_replace( '|', '&#124;', $txt );


                if ( $is_menu )
                {
                    $this->_dataArray[ $id ][ 'treename' ] = $txt;
                    $this->_dataArray[ $id ][ 'indent' ] = $w;
                }
                else
                {
                    $this->_dataArray[ $id ][ 'treename' ] = $indent . $txt;
                    $this->_dataArray[ $id ][ 'indent' ] = 0;
                }
                $this->_dataArray[ $id ][ 'children' ] = (isset( $this->_data[ $id ] ) ? count( $this->_data[ $id ] ) : 0);
                $this->_dataArray[ $id ][ 'is_folder' ] = (isset( $this->_data[ $id ] ) && count( $this->_data[ $id ] ) > 0 ? 1 : 0);

                if ( $is_menu )
                {
                    $this->buildRecurseArray( $id, $this->_dataArray[ $id ][ 'indent' ], $maxlevel, $level + 1, $is_menu );
                }
                else
                {

                    $this->buildRecurseArray( $id, $indent . '&#124;-- ', $maxlevel, (isset( $this->_data[ $id ] ) && count( $this->_data[ $id ] ) > 0 ? $level + 1 : ($level > 0 ? $level - 1 : 0) ), $is_menu );
                }
            }
        }

        # print_r($this->_dataArray);

        return $this->_dataArray;
    }

    /**
     *
     * @param array $items
     * @param string $primarykey
     * @param string $parentKey
     * @return array
     */
    public static function &mapTree( &$items, $primarykey, $parentKey )
    {
        $nodes = array();
        $tree = array();
        foreach ( $items as &$node )
        {
            if (!isset($node[ '_children' ]))
            {
                $node[ '_children' ] = array();
            }

            $id = isset($node[ $primarykey ]) ? $node[ $primarykey ] : false;
            $parent_id = isset($node[ $parentKey ]) ? $node[ $parentKey ] : 0;
            $nodes[ $id ] = & $node;

            if ( array_key_exists( $parent_id, $nodes ) )
            {
                $nodes[ $parent_id ][ '_children' ][] = & $node;
            }
            else
            {
                $tree[] = & $node;
            }
        }


        return $tree;
    }

}
