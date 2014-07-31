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
 * @file        TreeRecursiveIterator.php
 *
 */
class Tree_TreeRecursiveIterator extends RecursiveIteratorIterator
{

    /**
     * @var Tree
     */
    private $_Tree;

    /**
     * @var _str string with ul/li string
     */
    private $_str;

    private $_array;

    /**
     *
     * @param mixed $tree - the tree object
     * @param mixed $iterator - the tree iterator
     * @param mixed $mode
     * @param integer $flags
     * @return \Tree_TreeRecursiveIterator
     */
    public function __construct( Tree $tree, $iterator, $mode = LEAVES_ONLY, $flags = 0 )
    {

        parent::__construct( $iterator, $mode, $flags );
        $this->_Tree = $tree;
        $this->_str = "<ul>\n";
        $this->_array = array();
    }

    /**
     * Called when end recursing one level.(See manual)
     * @return void
     */
    public function endChildren()
    {
        parent::endChildren();
        $this->_str .= "</ul></li>\n";
    }

    /**
     * Called for each element to test whether it has children. (See Manual)
     *
     * @return mixed
     */
    public function callHasChildren()
    {
        $ret = parent::callHasChildren();
        $value = $this->current()->getValue();

        if ( $ret === true )
        {
            $this->_array[ $this->key() ] = $value;
            $this->_str .= "<li>{$value}<ul>\n";
        }
        else
        {
            $this->_array[ $this->key() ] = $value;
            $this->_str .= "<li>{$value}</li>\n";
        }
        return $ret;
    }

    /**
     * On destruction end the list and display.
     * @return void
     */
    public function __destruct()
    {
        $this->_str .= "</ul>\n";
        # echo $this->_str;
    }

    /**
     * @return _str|string
     */
    public function getUlList()
    {
        return $this->_str;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->_array;
    }

}
