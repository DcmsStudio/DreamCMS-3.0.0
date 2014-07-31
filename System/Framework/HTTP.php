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
 * @file        HTTP.php
 *
 */
class HTTP
{

    /**
     * @deprecated
     */
    public static function unsetRequest( $key )
    {
        return Input::getInstance()->remove( $key );
    }

    /**
     * @deprecated
     */
    public static function get( $key = null, $type = null )
    {
        return Input::getInstance()->get( $key, $type );
    }

    /**
     * @deprecated
     */
    public static function post( $key = null, $type = null )
    {
        return Input::getInstance()->post( $key, $type );
    }

    /**
     * @deprecated
     */
    public static function input( $key = null, $type = null )
    {
        return Input::getInstance()->input( $key, $type );
    }

    /**
     * @deprecated
     */
    public static function setinput( $key = null, $value = null )
    {
        return Input::getInstance()->set( $key, $value );
    }

    /**
     * @deprecated
     */
    public static function requestType()
    {
        return Input::getInstance()->getMethod();
    }

    /**
     * @deprecated
     */
    public static function getClean( $data, $strictMode = false )
    {
        return Input::getInstance()->clean( $data, $strictMode );
    }

}

?>