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
abstract class Grid_Abstract extends Loader
{

    /**
     * @var array
     */
    protected $headers = array();

    /**
     * @var array
     */
    protected $filters = array();

    /**
     * @var array
     */
    protected $sortbyForFilters = array();

    /**
     * @var array
     */
    protected $defaultFields = array();

    /**
     * @var array
     */
    protected $visibleFields = array();

    /**
     * @var array
     */
    protected $availebleFields = array();

    /**
     * @var int
     */
    protected $defaultPerpage = 20;

    /**
     * @var null
     */
    protected $perpage = null;

    /**
     * @var string
     */
    protected $uniq = '';

    /**
     * @var array
     */
    protected $gridSettings = array();

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * will generate manually a uiq string for absolutly save the settings to this uiq string
     * you can use this function when you use a table as multiple grids
     *
     * @param string $id
     */
    public function setUiqid( $id )
    {
        $isSeemode = Cookie::get( 'isSeemodePopup' ) ? '-seemode' : '';

        $this->uniq = md5( $id . CONTROLLER . ACTION . $isSeemode . (!empty( $GLOBALS[ 'APPID' ] ) ? $GLOBALS[ 'APPID' ] : '') );
    }

    /**
     *
     * @param array $actions
     */
    public function addActions( $actions = array() )
    {
        $this->gridActions = $actions;
    }

    /**
     *
     * @param array $arr
     */
    public function addFilter( $arr = array() )
    {
        $this->filters = $arr;
    }

}
