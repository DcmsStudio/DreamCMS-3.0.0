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
 * @file        Javascript.php
 *
 */
class Javascript
{

    const TAB = 1;

    const WYSIWYG = 2;

    const CALENDER = 3;

    private $_mode = null;

    /**
     *
     * @param integer $mode
     *
     * @return \Javascript
     * @return \Javascript
     * @return \Javascript
     */
    public function __construct( $mode )
    {
        $this->_mode = $mode;
    }

    /**
     *
     * @return Javascript_Tab
     * @return Javascript_Editor
     * @return Javascript_Calender
     */
    public function getInstance()
    {
        switch ( $this->_mode )
        {
            case self::TAB :
                return new Javascript_Tab( $this );
                break;
            case self::WYSIWYG :
                return new Javascript_Editor( $this );
                break;
            case self::CALENDER :
                return new Javascript_Calender( $this );
                break;
        }

        return null;
    }

}

?>