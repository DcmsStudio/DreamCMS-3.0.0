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
 * @file        Pagelink.php
 *
 */

class Tag_Pagelink extends Provider_Abstract
{
    /**
     * @var array
     */
    private static $_cache = array( );

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
            throw new BaseException('Empty page id and alias ');
        }
        
        $item = null;
        
        if ( $_id && !$_alias )
        {

            if ( isset( self::$_cache[ $_id ] ) )
            {
                $item = self::$_cache[ $_id ];
            }
            else
            {
                $item = Api::callModul( 'page', 'getItemByID', array( $_id ) );
                self::$_cache[ $_id ] = $item;
                self::$_cache[ $item[ 'alias' ] ] = $item;
            }
            $item[ 'itemurl' ] = 'page/' . $item[ 'alias' ] . '.' . ($item[ 'suffix' ] ? $item[ 'suffix' ] : 'html');
        }
        else
        {
            if ( isset( self::$_cache[ $_alias ] ) )
            {
                $item = self::$_cache[ $_alias ];
            }
            else
            {
                $item = Api::callModul( 'page', 'getItemByAlias', array( $_alias ) );
                self::$_cache[ $item[ 'id' ] ] = $item;
                self::$_cache[ $_alias ] = $item;
            }
            
            $item[ 'itemurl' ] = 'page/' . $item[ 'alias' ] . '.' . ($item[ 'suffix' ] ? $item[ 'suffix' ] : 'html');
        }


        if ( $item !== null )
            return $item[ 'itemurl' ];
        else
            return '#';



        ob_start();
        $output = ob_get_contents();
        ob_get_clean();
        $buf    = ob_end_clean();
        $buf    = @ob_end_clean();

        return $output;
    }
}

?>