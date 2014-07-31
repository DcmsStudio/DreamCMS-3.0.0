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
 * @package      DreamCMS
 * @version      3.0.0 Beta
 * @category     Framework
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Widget.php
 */
class Widget extends Widget_Abstract
{

    const SHOW = 1;

    const CONFIG = 2;

    /**
     *
     * @var Widget
     */
    protected static $_instance = null;


    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->load( 'Input' );
    }

    /**
     * Prevent cloning of the object (Singleton)
     */
    final private function __clone()
    {

    }

    /**
     * Return the current object instance (Singleton)
     *
     * @return Controller
     */
    public static function getInstance()
    {

        if ( self::$_instance === null )
        {
            self::$_instance = new Widget;
        }

        return self::$_instance;
    }


    /**
     *
     * @param type $mode
     * @throws BaseException
     * @return Widget
     */
    public function viewMode($mode = null)
    {

        if ( $mode !== self::SHOW && $mode !== self::CONFIG )
        {
            throw new BaseException( 'Please set the Widget mode!' );
        }

        $this->__viewMode = $mode;
        $this->_viewMode  = ( $mode === self::SHOW ? 'Show' : 'Config' );

        return $this;
    }

    /**
     * Get name of widget
     *
     * @return string
     *
     */
    public function getName()
    {

        return $this->_widgetName;
    }

    /**
     * Get name of module that widget belong to
     *
     * @return string
     */
    public function getModule()
    {

        return $this->_module;
    }

    /**
     *
     * @param array $config
     * @return Widget
     */
    public function setConfig($config = array())
    {

        $this->_config = $config;

        return $this;
    }

    /**
     *
     * @param integer $id
     * @return Widget
     */
    public function setID($id)
    {

        $this->_widgetID = $id;

        return $this;
    }

    /**
     *
     * @param string $name
     * @return Widget
     */
    public function setName($name)
    {

        $this->_widgetName = $name;

        return $this;
    }

    /**
     *
     * @param array $data
     * @return Widget
     */
    public function setWidgetData(array $data)
    {
        if (!is_array($this->_widgetData) || !isset($this->_widgetData) )
        {
            $this->_widgetData = $data;
        }
        else {
            $this->_widgetData = array_merge($this->_widgetData, $data);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getWidgetData()
    {

        return $this->_widgetData;
    }

    /**
     *
     */
    public function run()
    {


        if ( $this->_isModulWidget )
        {
            // @todo build modul widgets

            $className = ucfirst( strtolower( $this->_module ) ) . '_Widget_' . ucfirst( strtolower( $this->_widgetName ) ) . '_' . $this->_viewMode;
        }
        else
        {
            $className = 'Widget_' . ucfirst( strtolower( $this->_widgetName ) ) . '_' . $this->_viewMode;
        }

        if ( !class_exists( $className ) )
        {
            throw new BaseException( sprintf( 'The Widget `%s` not exists!', ucfirst( strtolower( $this->_widgetName ) ) ) );
        }

        /**
         * @var
         */
        $widget = new $className( $this->_config );
        $call   = '_render' . ucfirst( $this->_viewMode );

        $widget
            ->viewMode( $this->__viewMode )
            ->setName( $this->_widgetName )
            ->setID( $this->_widgetID )
            ->setConfig( $this->_config )
            ->setWidgetData( (array)$this->_widgetData )
            ->getData(); // prepare data


        // return error
        if ( $this->error )
        {
            return $this->error;
        }




        // merge data
        $dat = array_merge( ( is_array( $widget->getWidgetData() ) ? $widget->getWidgetData() : array() ), array(
            'name'     => strtolower( $this->_widgetName ),
            'id'       => $this->_widgetID,
            'widgetid' => $this->_widgetID
        ) );

        // set config to separat array key "config"
        if ( !isset( $dat[ 'config' ] ) )
        {
            $dat[ 'config' ] = $widget->getConfig();
        }

        // register config vars global
        $tmp = $widget->getConfig();
        unset( $tmp[ 'name' ], $tmp[ 'id' ], $tmp[ 'widgetid' ] );
        $dat = array_merge( $tmp, $dat );

        #print_r($dat);exit;


        // set data
        $widget->setWidgetData( $dat );

        // now call template
        return $widget->$call($dat);
    }

    /**
     *
     */
    public function _renderShow($dat)
    {

        if ( $this->_isModulWidget )
        {
            // @todo build modul widgets
        }
        else
        {
            $this->load( 'Template' );

            return $this->Template->renderTemplate( WIDGET_PATH . ucfirst( strtolower( $this->_widgetName ) ) . '/template/' . strtolower( $this->_viewMode ) . '.html', $dat );
        }
    }

    /**
     *
     */
    public function _renderConfig($dat)
    {
        if ( $this->_isModulWidget )
        {
            // @todo build modul widgets
        }
        else
        {
            $this->load( 'Template' );

            return $this->Template->renderTemplate( WIDGET_PATH . ucfirst( strtolower( $this->_widgetName ) ) . '/template/' . strtolower( $this->_viewMode ) . '.html', $dat );
        }
    }


}
