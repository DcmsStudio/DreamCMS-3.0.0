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
 * @package     CoreTags
 * @version     3.0.0 Beta
 * @category    Core Tag
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Newslink.php
 *
 */

class Tag_Newslink extends Provider_Abstract
{
    /**
     * @var array
     */
    private static $_cache = array();

    /**
     * @param $tag
     * @return string
     * @throws BaseException
     */
    public static function render( $tag )
    {
        $dcms = self::$dcms;

        if ( is_numeric( $tag[ 0 ] ) )
        {
            $_id = intval( $tag[ 0 ] );
        }
        elseif ( is_string( $tag[ 0 ] ) )
        {
            $_alias = trim( $tag[ 0 ] );
        }
        else
        {
            throw new BaseException();
        }

        

        if ( $_id > 0 )
        {

            if ( isset( self::$_cache[ $_id ] ) )
            {
                $data = self::$_cache[ $_id ];
            }
            else
            {
                
                $model = Model::getInstance('news');
                $data = $model->findItemByID($_id);
              
                self::$_cache[$_alias] = $data;
                self::$_cache[$data[ 'id' ]] = $data;
            }
        }
        elseif ( !empty( $_alias ) )
        {

            if ( isset( self::$_cache[ $_alias ] ) )
            {
                $data = self::$_cache[ $_alias ];
            }
            else
            {
                $model = Model::getInstance('news');
                $data = $model->findItemByAlias($_alias);
                
                self::$_cache[$_alias] = $data;
                self::$_cache[$data[ 'id' ]] = $data;
            }
        }
        else
        {
            return '';
        }

        return 'news/item/' . $data['alias'] . ($data['suffix'] ? '.' . $data['suffix'] : '.html');
    }
}