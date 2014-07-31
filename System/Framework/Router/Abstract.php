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
abstract class Router_Abstract extends Loader
{

    /**
     * @var null
     */
    protected $_variables = null;

    /**
     * Path matched by this route
     *
     * @var string
     */
    protected $_matchedPath = null;

    /**
     * @var null
     */
    protected $_Controller = null;

    /**
     * @var null
     */
    protected $_ControllerAction = null;

    /**
     * @var null
     */
    protected $appKey = null;

    /**
     * @var null
     */
    protected $_preparedRule = null;

    /**
     * @var null
     */
    protected $_mapKeys = null;

    /**
     * Set partially matched path
     *
     * @param  string $path
     * @return void
     */
    public function setMatchedPath( $path )
    {
        $this->_matchedPath = $path;
    }


    /**
     * Get partially matched path
     *
     * @return string
     */
    public function getMatchedPath()
    {
        return $this->_matchedPath;
    }

    /**
     * Get all variables which are used by the route
     *
     * @return array
     */
    public function getVariables()
    {
        return $this->_variables;
    }

    /**
     *
     * @param string $key
     * @return mixed if not exists the return NULL
     */
    public function getVariable( $key )
    {
        return (isset( $this->_variables[ $key ] ) ? $this->_variables[ $key ] : null);
    }

    /**
     * returns the current action
     * @return string
     */
    public function getAction()
    {
        return $this->_ControllerAction;
    }

    /**
     * @return null
     */
    public function getApplicationType()
    {
        return $this->appKey;
    }

    /**
     * @return null
     */
    public function getApplicationController()
    {
        return $this->_Controller;
    }

    /**
     *
     * @param array  $variables
     * @param string $_controller
     * @param string $_action
     * @param null   $_mapKeys
     * @param null   $preparedRule
     */
    public function setVariables( $variables, $_controller = null, $_action = null, $_mapKeys = null, $preparedRule = null )
    {
        $this->_variables = $variables;

        if ( $_mapKeys !== null )
        {
            $this->_mapKeys = $_mapKeys;
        }

        if ( $preparedRule !== null )
        {
            $this->_preparedRule = $preparedRule;
        }

        if ( $_action !== null )
        {
            $this->_ControllerAction = $_action;
        }

        if ( $_controller !== null )
        {
            $this->_Controller = $_controller;
        }
    }

}

?>