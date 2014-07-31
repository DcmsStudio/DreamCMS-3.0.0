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
 * @file        Node.php
 *
 */
class Tree_Node
{

    /**
     * @var _value for the value field
     */
    private $_value;

    /**
     * @var _parent uid of the parent node
     */
    private $_parent;

    /**
     * @var _children collection of uids for the child nodes
     */
    private $_children = array();

    /**
     * @var _uid for this node
     */
    private $_uid;

    /**
     *
     * @param mixed $value
     * @param mixed $uid
     * @param null  $parentUid
     * @return \Tree_Node
     */
    public function __construct( $value = null, $uid = null, $parentUid = null )
    {
        $this->setValue( $value );
        $this->setUid( $uid );
        $this->setParent( $parentUid );
    }

    /**
     *
     * @param mixed $uid
     * @return void
     */
    public function setUid( $uid = null )
    {
        //if uid not supplied...generate
        if ( empty( $uid ) )
        {
            $this->_uid = uniqid();
        }
        else
        {
            $this->_uid = $uid;
        }
    }

    /**
     *
     * @return string uid
     */
    public function getUid()
    {
        return $this->_uid;
    }

    /**
     *
     * @param mixed $value
     * @return void
     */
    public function setValue( $value )
    {
        if ( $this->_value !== $value )
        {
            $this->_value = $value;
        }
    }

    /**
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * gets the uid of the parent node
     *
     * @return string uid
     */
    public function getParent()
    {
        return $this->_parent;
    }

    /**
     *
     * @param mixed $parent
     * @return void
     */
    public function setParent( $parent )
    {
        if ( $this->_parent !== $parent )
        {
            $this->_parent = $parent;
        }
    }

    /**
     *
     * @return mixed
     */
    public function getChildren()
    {
        return $this->_children;
    }

    /**
     * A child node's uid is added to the childrens array
     *
     * @param mixed $child
     * @return void
     */
    public function setChild( $child )
    {
        if ( !empty( $child ) )
        {
            $this->_children[] = $child;
        }
    }

    /**
     * Checks if there are any children
     * returns ture if it does, false otherwise
     *
     * @return bool
     */
    public function hasChildren()
    {
        $ret = false;

        if ( count( $this->_children ) > 0 )
        {
            $ret = true;
        }
        return $ret;
    }

    /**
     * returns the number of children
     *
     * @return bool/int
     */
    public function childrenCount()
    {
        $ret = false;
        if ( is_array( $this->_children ) )
        {
            $ret = count( $this->_children );
        }
        return $ret;
    }

}
