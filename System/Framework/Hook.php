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
 * @file        Hook.php
 *
 */
class Hook extends Loader
{

    /**
     * @var bool
     */
    private static $inited = false;

    /**
     * @var null
     */
    protected static $_instance = null;

    /**
     * @var null
     */
    private static $_hooks = null;

    /**
     * @var null
     */
    private static $_loadedComponentes = null;

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
            self::$_instance = new Hook;
            self::$_instance->initEventHooks();
        }

        return self::$_instance;
    }

    public function __destruct()
    {
        parent::__destruct();
        self::$_instance = null;
        self::$_hooks = null;
        self::$_loadedComponentes = null;
    }

    public function initEventHooks()
    {
        if ( self::$inited )
        {
            return;
        }

        $events = Cache::get( 'event_hooks', 'data' );


        if ( $events !== null )
        {
            $rows = $this->db->query( 'SELECT * FROM %tp%event_hook WHERE hook_enabled = 1 ORDER BY run_order ASC' )->fetchAll();

            foreach ( $rows as $row )
            {
                $events[ $row[ 'event' ] ][ $row[ 'type' ] ][] = $row[ 'handler' ];
            }

            $rows = null;

            Cache::write( 'event_hooks', $events, 'data' );
        }

        if ( is_string( $events ) )
        {
            $events = Library::unserialize( $events );
        }

        self::$_hooks = $events;
        unset( $events );

        self::$inited = true;
    }

    /**
     *
     * @param string      $name
     * @param array|\type $data   reference
     * @param bool|\type  $caller reference
     * @return void
     */
    public static function run( $name, &$data = array(), &$caller = false )
    {
	    /*
        if ( !isset( self::$_hooks[ $name ] ) )
        {
            if ( DEBUG )
            {
                $trace = debug_backtrace();
                $trace = $trace[ 0 ];
                $caller = array(
                    'file' => (!empty( $trace[ 'file' ] ) ? str_replace( ROOT_PATH, '', Library::formatPath( $trace[ 'file' ] ) ) : 'unknown'),
                    'line' => (!empty( $trace[ 'line' ] ) ? $trace[ 'line' ] : 'unknown'),
                );

                Debug::store( 'Skip Hook', $name . ' > ' . $caller[ 'file' ] . ' - ' . $caller[ 'line' ] );
                unset( $trace );
            }
            return;
        }


        if ( ((isset( self::$_hooks[ $name ][ 'component' ] ) && is_array( self::$_hooks[ $name ][ 'component' ] )) || (isset( self::$_hooks[ $name ] ) && is_array( self::$_hooks[ $name ][ 'plugin' ] ))) && DEBUG
        )
        {
            $trace = debug_backtrace();
            $trace = $trace[ 0 ];
            $caller = array(
                'file' => (!empty( $trace[ 'file' ] ) ? str_replace( ROOT_PATH, '', Library::formatPath( $trace[ 'file' ] ) ) : 'unknown'),
                'line' => (!empty( $trace[ 'line' ] ) ? $trace[ 'line' ] : 'unknown'),
            );

            Debug::store( 'Run Hook', $name . ' > ' . $caller[ 'file' ] . ' - ' . $caller[ 'line' ] );
            unset( $trace );
        }
*/

        // run plugin hooks for this event
        if ( isset(self::$_hooks[ $name ][ 'plugin' ]) && is_array( self::$_hooks[ $name ][ 'plugin' ] ) )
        {

            foreach ( self::$_hooks[ $name ][ 'plugin' ] as $plugin_name )
            {
                $plugin = Plugin::getPlugin( $plugin_name, true, true );

                if ( !is_null( $plugin ) )
                {
                    if ( !method_exists( $plugin, $name ) )
                    {
                        Error::raise( sprintf( 'The Plugin `%s` not exists!', $plugin_name ) );
                    }

                    $plugin->$name( $data, $caller );

                    if ( DEBUG )
                    {
                        Debug::store( 'Hook (plugin)', $plugin_name );
                    }
                }
            }
        }


        // run component hooks for this event
        if ( isset( self::$_hooks[ $name ][ 'component' ] ) && is_array( self::$_hooks[ $name ][ 'component' ] ) )
        {
            Library::disableErrorHandling();

            foreach ( self::$_hooks[ $name ][ 'component' ] as $component_name )
            {
                if ( !isset( self::$_loadedComponentes[ $component_name ] ) )
                {
                    $path = SystemManager::getComponentPath( $component_name );
                }
                else
                {
                    $path = self::$_loadedComponentes[ $component_name ];
                }

                try
                {
                    self::$_loadedComponentes[ $component_name ] = $path;

                    include $path;

                    if ( DEBUG )
                    {
                        Debug::store( 'Hook (component)', $component_name );
                    }
                }
                catch ( Exception $e )
                {

                    Error::raise( "File \"$path\" does not exist" );
                }
            }

            Library::enableErrorHandling();
        }
    }

}

?>