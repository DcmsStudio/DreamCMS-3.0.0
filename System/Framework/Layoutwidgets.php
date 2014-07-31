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
 * @file        Layoutwidgets.php
 *
 */
class Layoutwidgets extends Controller_Abstract
{

    /**
     * @var null
     */
    private static $_allContentWidgets = null;

    /**
     * @var null
     */
    private static $_currentContentWidget = null;

    /**
     *
     * @var array
     */
    public $options = array();

    /**
     * @var int
     */
    public $widgetID = 0;

    /**
     *
     * @return array
     */
    public function getAllContentWidgets()
    {
        if ( !is_null( self::$_allContentWidgets ) )
        {
            return self::$_allContentWidgets;
        }

        $allWidgets = array();
        $modules = $this->getApplication()->getModuleNames();
        foreach ( $modules as $modul )
        {
            $modul = strtolower( $modul );

            $modUcFirst = ucfirst( $modul );
            $_cls = $modUcFirst . '_Config_Base/getWidgets';
            if ( checkClassMethod( $_cls ) )
            {
                $widgets = call_user_func( $modUcFirst . '_Config_Base::getWidgets' );
                if ( is_array( $widgets ) )
                {
                    foreach ( $widgets as $callname => $label )
                    {
                        $className = $modUcFirst . '_Widget_' . ucfirst( $callname );
                        if ( class_exists( $className ) )
                        {
                            $allWidgets[ $modul ][ $callname ] = array(
                                'modul'  => $modul,
                                'widget' => $callname,
                                'label'  => $label,
                                'class'  => $className );
                        }
                    }
                }
            }
        }

        self::$_allContentWidgets = $allWidgets;

        return $allWidgets;
    }

    /**
     *
     * @param array $setOptions
     */
    public function setOptions( $setOptions )
    {
        $this->options = $setOptions;
    }

    /**
     *
     * @param string $name
     * @return array/boolean
     */
    public function getContentWidgetsByModul( $name )
    {
        $this->getAllContentWidgets();

        if ( isset( self::$_allContentWidgets[ $name ] ) )
        {
            return self::$_allContentWidgets[ $name ];
        }

        return false;
    }

    /**
     *
     * @param string $modul
     * @param $callname
     * @internal param string $name
     * @return array/boolean
     */
    public function getContentWidget( $modul, $callname )
    {
        $this->getAllContentWidgets();

        if ( isset( self::$_allContentWidgets[ $modul ][ $callname ] ) )
        {
            return self::$_allContentWidgets[ $modul ][ $callname ];
        }

        return false;
    }

    /**
     *
     * @param string $modul
     * @param string $name
     * @return \Layoutwidgets
     * @throws BaseException
     */
    public function setActiveContentWidget( $modul, $name )
    {
        $this->getAllContentWidgets();

        if ( isset( self::$_allContentWidgets[ $modul ][ $name ] ) )
        {
            self::$_currentContentWidget = self::$_allContentWidgets[ $modul ][ $name ];
            return $this;
        }
        else
        {
            throw new BaseException( sprintf( 'This Modul (%s) has no method `%s`', ucfirst( $modul ), ucfirst( $name ) ) );
        }
    }

    /**
     *
     * @return mixed
     * @throws BaseException
     */
    public function getWidgetData()
    {
        if ( self::$_currentContentWidget === null )
        {
            throw new BaseException( 'The method Layoutwidgets::getWidgetData() must set the Widget before run it.' );
        }

        $widget = self::$_currentContentWidget;
        $className = ucfirst( $widget[ 'modul' ] ) . '_Widget_' . ucfirst( $widget[ 'widget' ] );


        $cls = new $className();
        $data = $cls->execute();

        $cls->load( 'Template' );

        return $data;
    }

    /**
     *
     * @param string $templateName the name of template without the path!
     * @param array $data
     * @param bool $return
     * @internal param $bollean /null $return
     * @return mixed
     */
    public function process( $templateName, $data, $return = true )
    {
        $widget = self::$_currentContentWidget;
        $path = strtolower( $widget[ 'modul' ] ) . '/widget/' . strtolower( $templateName );
        return $this->Template->process( $path, $data, $return );
    }

}
