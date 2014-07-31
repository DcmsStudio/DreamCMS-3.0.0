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
 * @file        Component.php
 */
class Provider_Component extends Provider_Abstract
{

    protected static $paths = array();

    public function render( $tag )
    {
        if ( !isset( $tag[ 'name' ] ) )
        {
            return '';
        }

        $component = $tag[ 'name' ];
        unset( $tag[ 'name' ] );



        $____isCacheable = true;
        if ( isset( $tag[ 'cacheable' ] ) && (!$tag[ 'cacheable' ] || strtolower( $tag[ 'cacheable' ] ) == 'false') )
        {
            unset( $tag[ 'cacheable' ] );
            $____isCacheable = false;
        }

        if ( $____isCacheable )
        {
            $____md5CheckSum = md5( serialize( $tag ) );

            $output = Cache::get( 'componente-' . $component . '-' . $____md5CheckSum, 'data/component-cache' );
            if ( $output !== null )
            {
                return $output;
            }
        }




        if ( !isset( self::$paths[ $component ] ) )
        {
            $component_path = SystemManager::getComponentPath( $component );
            self::$paths[ $component ] = $component_path;
        }
        else
        {
            $component_path = self::$paths[ $component ];
        }

        if ( empty( $component_path ) || !is_file( $component_path ) )
        {
            return '';
        }
        if (isset($tag['toObj']) ) {
            // print_r($tag);exit;
        }

        Registry::set($component, $tag);

        extract( $tag );
        ob_start();



        try
        {
            include $component_path;
        }
        catch ( Exception $e )
        {
            Error::raise( 'Unhandled Exception: ' . $e->getMessage(), 'PHP', $e->getCode(), $e->getFile(), $e->getLine() );
        }

        $output = ob_get_contents();
        $buf = ob_end_clean();

        Registry::clear($component);

        if ( $____isCacheable )
        {
            Cache::write( 'componente-' . $component . '-' . $____md5CheckSum, $output, 'data/component-cache' );
        }

        return $output;
    }

}

?>