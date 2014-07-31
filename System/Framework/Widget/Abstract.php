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
abstract class Widget_Abstract extends Loader
{

    /**
     * Name of module that widget belong to
     *
     * @var string
     */
    protected $_module;

    /**
     * Name of widget
     *
     * @var string
     */
    protected $_name;

    /**
     * Widget prepared data
     *
     * @var array
     */
    protected $_data;
    protected $_widgetData = array();
    /**
     *
     * Store the Widget config
     *
     * @var array
     */
    protected $_config = null;

    /**
     * @var null
     */
    protected $_widgetID = null;

    /**
     *
     * @var string
     */
    protected $_viewMode;

    /**
     * @var int
     */
    protected $__viewMode;

    /**
     *
     * @var boolean
     */
    protected $_isModulWidget = false;

    protected $error = false;


    /**
     * Import some default libraries
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *
     * @param string $method
     * @param mixed/array $arguments
     */
    public function __call( $method, $arguments )
    {
        Error::raise( 'Class `' . get_class( $this ) . '` has no method `' . $method . '`.' );
    }

    /**
     * reset all stored data
     *
     */
    public function reset()
    {
        $this->_module = null;
        $this->_name = null;
        $this->_data = null;
        $this->_config = null;
        $this->_widgetID = null;
        $this->_isModulWidget = false;
    }

    /**
     * @param $message
     */
    public function setError($message)
    {
        $this->error = $message;
    }


    /**
     * @return null
     */
    public function getID()
    {
        return $this->_widgetID;
    }
    /**
     *
     * @param string $keyName default is null and will return the config array
     * @return mixed|null
     */
    public function getConfig( $keyName = null )
    {
        if ( $keyName === null )
        {
            return $this->_config;
        }

        return (isset( $this->_config[ $keyName ] ) ? $this->_config[ $keyName ] : null);
    }

    /**
     *
     * @param string $modul
     * @param string $name
     *
     * @todo build modul widgets
     */
    public function getModulWidget( $modul, $name )
    {
        $this->reset();

        $this->_module = $modul;
        $this->_name = $name;
        $this->_isModulWidget = true;
    }

}
