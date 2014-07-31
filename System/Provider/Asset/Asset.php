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
 * @category    Content Provider
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Asset.php
 */
class Provider_Asset extends Provider_Abstract
{

    public static function render( $tag )
    {
        if ( !isset( $tag[ 'name' ] ) || empty( $tag[ 'name' ] ) )
            return '-';

        $____isCacheable = true;
        if ( (isset( $tag[ 'cacheable' ] ) && !$tag[ 'cacheable' ]) || (isset( $tag[ 'cacheable' ] ) && strtolower( $tag[ 'cacheable' ] ) == 'false') )
        {
            unset( $tag[ 'cacheable' ] );
            $____isCacheable = false;
        }



        $media = Cache_Filecache::get($tag[ 'name' ], 'data/assets');

        if ( !$media && $____isCacheable )
        {
            preg_match('#\.([a-z0-9]+)$#i', $tag[ 'name' ], $match);
            $media = Media::getAsset( $tag[ 'name' ], ($match[1] ? true : false) );

            if ( !isset($media[ 'url' ]) || empty($media[ 'url' ]) )
            {
                return 'not found 1';
            }

            Cache_Filecache::set($tag[ 'name' ], $media[ 'content' ], 'data/assets');
        }
        else
        {
            preg_match('#\.([a-z0-9]+)$#i', $tag[ 'name' ], $match);
            $media = Media::getAsset( $tag[ 'name' ], ($match[1] ? true : false) );
            if ( !isset($media[ 'url' ]) || empty($media[ 'url' ]) )
            {
                return 'not found 2';
            }
        }

        if ( $media[ 'type' ] == 'stylesheet' || $media[ 'type' ] == 'css' )
        {
            $path = 'asset/css/assets/' . $media[ 'url' ];
        }
        elseif ( $media[ 'type' ] == 'javascript' || $media[ 'type' ] == 'js' )
        {
            $path = 'asset/js/assets/' . $media[ 'url' ];
        }
        else {
            $path = 'not found 3';
        }

        return $path;
    }

}

?>