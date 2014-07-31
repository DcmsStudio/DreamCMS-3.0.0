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
 * @file        TreeIterator.php
 *
 */
class Tree_TreeIterator extends ArrayIterator implements RecursiveIterator
{

    /**
     * @var _list this is the hash table
     */
    private $_list = array();

    /**
     * @var _next this is for the children
     */
    private $_next = array();

    /**
     * @var _position the iterator position
     */
    private $_position;

    /**
     *
     * @param mixed $list - the hash table
     * @param mixed $tree -
     * @return Tree_TreeIterator
     */
    public function __construct( array $list, array $tree = null )
    {
        $this->_list = $list;

        if ( is_null( $tree ) )
        {
            reset( $this->_list );
            $next = current( $this->_list );
            $this->_next = $next->getChildren();
        }
        else
        {
            $this->_next = $tree;
        }

        parent::__construct( $this->_next );
    }

    /**
     *
     * @return mixed
     */
    public function current()
    {
        //get the object uid from the hash table
        //then get the object
        $current = parent::current();
        $nObj = $this->_list[ $current ];
        return $nObj;
    }

    /**
     *
     * @return mixed
     */
    public function key()
    {
        $key = parent::key();
        $key = $this->_next[ $key ];
        return $key;
    }

    /**
     *
     * @return mixed
     */
    public function hasChildren()
    {
        $next = $this->_list[ $this->key() ];
        $next = $next->hasChildren();
        return $next;
    }

    /**
     *
     * @return Tree_TreeIterator
     */
    public function getChildren()
    {
        $childObj = $this->_list[ $this->key() ];
        $children = $childObj->getChildren();
        return new Tree_TreeIterator( $this->_list, $children );
    }

}
