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
 * @category    Plugin s
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        plugin.timing.php
 */
class TimingPlugin extends Plugin
{

    public $is_runnable = true;

    public $is_configurable = false;

    public static $timing = 0;

    public function __construct()
    {
        parent::__construct();
    }

    public function callMethod( $method )
    {
        $this->$method();
    }

    public function run()
    {
        $data = array();
        return $this->renderTemplate( $data, 'run' );
    }

    public function onBeforeOutput( &$data, &$object )
    {

        $time = '<!--Block:timing-->' . Debug::getReadableTime(microtime( true ) - START ) ;
        if ( function_exists( 'memory_get_peak_usage' ) )
        {
            $time .= ' | <small>(Peak: ' . Library::humanSize( memory_get_peak_usage( true ) ) . ' - Usage: ' . Library::humanSize( memory_get_usage() ) . ')</small>';
        }

        $querys = Database::getQueryCounter();
        $time .= ' | <small>Querys: ' . $querys . '</small>';


        if ( !defined( 'ADM_SCRIPT' ) )
        {
            if ( defined( 'USE_FIREWALL' ) && USE_FIREWALL )
            {
                $time .= ' | <small>Firewall: ' . trans( 'AN' ) . '</small>';
            }
            elseif ( defined( 'USE_FIREWALL' ) && USE_FIREWALL !== true )
            {
                $time .= ' | <small>Firewall: ' . trans( 'AUS' ) . '</small>';
            }
        }

        $data = str_replace( '<!--TIMING-->', $time, $data );
        return $data;
    }

    public function onAjaxOutput( &$data, &$object )
    {
        $time = Debug::getReadableTime(microtime( true ) - START );
        if ( function_exists( 'memory_get_peak_usage' ) )
        {
            $time .= ' | <small>Memory: ' . Library::humanSize( memory_get_usage() ) . ' (peak:' . Library::humanSize( memory_get_peak_usage( true ) ) . ')</small>';
        }
        $querys = Database::getQueryCounter();
        $time .= ' | <small>Querys: ' . $querys . '</small>';
        $data = $time;
        return $data;
    }

}

?>