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
 * @file        Static.php
 *
 */
class Router_Static extends Router_Abstract
{

    /**
     * @var null
     */
    protected $_regex = null;

    /**
     * @var array
     */
    protected $_defaults = array();

    /**
     * @var null
     */
    protected $_reverse = null;

    /**
     * @var array
     */
    protected $_map = array();

    /**
     * @var array
     */
    protected $_values = array();

    /**
     * Prepares the route for mapping.
     *
     * @param string $route Map used to match with later submitted URL path
     * @param array $defaults Defaults for map variables with keys as variable names
     */
    public function __construct( $route, $defaults = array() )
    {
        $this->_route = trim( $route, '/' );
        $this->_defaults = (array) $defaults;
    }

    /**
     * Matches a user submitted path with a previously defined route.
     * Assigns and returns an array of defaults on a successful match.
     *
     * @param string $path Path used to match against this routing map
     * @param bool   $partial
     * @return array|false An array of assigned values or a false on a mismatch
     */
    public function match( $path, $partial = false )
    {
        if ( $partial )
        {
            if ( substr( $path, 0, strlen( $this->_route ) ) === $this->_route )
            {
                $this->setMatchedPath( $this->_route );
                return $this->_defaults;
            }
        }
        else
        {
            if ( trim( $path, '/' ) == $this->_route )
            {
                return $this->_defaults;
            }
        }

        return false;
    }

    /**
     * Assembles a URL path defined by this route
     *
     * @param array $data An array of variable and value pairs used as parameters
     * @param bool  $reset
     * @param bool  $encode
     * @param bool  $partial
     * @return string Route path with user submitted parameters
     */
    public function assemble( $data = array(), $reset = false, $encode = false, $partial = false )
    {
        return $this->_route;
    }

    /**
     * Return a single parameter of route's defaults
     *
     * @param string $name Array key of the parameter
     * @return string Previously set default
     */
    public function getDefault( $name )
    {
        if ( isset( $this->_defaults[ $name ] ) )
        {
            return $this->_defaults[ $name ];
        }
        return null;
    }

    /**
     * Return an array of defaults
     *
     * @return array Route defaults
     */
    public function getDefaults()
    {
        return $this->_defaults;
    }

}

?>