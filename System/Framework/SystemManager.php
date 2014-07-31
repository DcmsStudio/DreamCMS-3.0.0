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
 * @file        SystemManager.php
 *
 */
class SystemManager
{

    /**
     * @var null
     */
    private static $db = null;

    /**
     * @var array
     */
    private static $componentPaths = array();

    /**
     *
     * @return type
     */
    public static function syncEventHooks()
    {
        $db = Database::getInstance();

        if ( !is_object( $db ) || !method_exists( $db, 'query' ) )
        {
            die( 'Invalid Database Connect' );
        }

        // load stored events
        $stored_events = array();
        $res = $db->query( 'SELECT * FROM %tp%event' )->fetchAll();


        foreach ( $res as $r )
        {
            $stored_events[ $r[ 'event' ] ] = true;
        }


        // find hooks in plugins and components
        $plugin_hooks = array();
        $rs = $db->query( 'SELECT * FROM %tp%event_hook' )->fetchAll();
        foreach ( $rs as $row )
        {
            if ( $row[ 'type' ] == 'plugin' )
            {
                $plugin_hooks[ $row[ 'handler' ] ][ $row[ 'event' ] ] = 0;
            }
        }

        unset( $rs );

        /*

          // update plugin hooks in database
          $res     = $db->query( 'SELECT `key` FROM %tp%plugin' )->fetchAll();
          $plugins = array( );
          foreach ( $res as $row )
          {
          $plugins[ ] = PLUGIN_PATH . $row[ 'key' ] . '/' . 'plugin.' . $row[ 'key' ] . '.php';
          }

          unset( $res );

          $not_inserted = array( );
          foreach ( $plugins as $file )
          {
          preg_match( '/plugin.(.*?).php/', basename( $file ), $plugin );
          $plugin = $plugin[ 1 ];
          require_once $file;

          $class = new ReflectionClass( $plugin . 'Plugin' );

          $plugin = str_replace( 'plugin', '', strtolower( $class->getName() ) );

          foreach ( $class->getMethods() as $method )
          {
          if ( $method->isPublic() && strpos( $method->getName(), '_' ) === false )
          {

          if ( isset( $stored_events[ $method->getName() ] ) )
          {


          if ( !isset( $plugin_hooks[ $plugin ][ $method->getName() ] ) )
          {

          $row = $db->query_first( 'SELECT MAX(run_order) AS max_run_order FROM %tp%event_hook WHERE event = ?', $method->getName() );
          $mru = $row[ 'max_run_order' ] + 1;
          $db->query( 'INSERT INTO %tp%event_hook SET `type` = \'plugin\', event = ?, handler = ?, run_order = ?', $method->getName(), $plugin, $mru );

          $plugin_hooks[ $plugin ][ $method->getName() ] = 1;
          }
          else
          {
          $plugin_hooks[ $plugin ][ $method->getName() ] = 1;
          }
          }
          else
          {
          $not_inserted[ $plugin ][ ] = $method->getName();
          }
          }
          }
          }

          foreach ( $plugin_hooks as $plugin => $events )
          {
          foreach ( $events as $event => $exists )
          {
          if ( $exists == 0 )
          {
          $db->query( 'DELETE FROM %tp%event_hook WHERE `type` = \'plugin\' AND event = ? AND handler = ?', $event, $plugin );
          }
          }
          }
         * 
         * 
         */

        unset( $plugin_hooks, $not_inserted, $plugins );

        Cache::delete( 'event_hooks' );

        return array();
    }

    /**
     *
     * @param $mode
     * @return array
     */
    public static function scanEvents( $mode )
    {
        $db = Database::getInstance();

        // load stored events
        $stored_events = array();
        $ev = $db->query( 'SELECT event FROM %tp%event' )->fetchAll();
        foreach ( $ev as $r )
        {
            $stored_events[] = $r[ 'event' ];
        }

        unset( $rs );

        $scanned_events = array();

        @set_time_limit( 120 );


        $files = array();


        if ( $mode === 'plugin' )
        {

            $dir_iterator = new RecursiveDirectoryIterator( PLUGIN_PATH );
            $iterator = new RecursiveIteratorIterator( $dir_iterator, RecursiveIteratorIterator::SELF_FIRST );
            foreach ( $iterator as $fullFileName => $fileSPLObject )
            {
                if ( Library::getExtension( $fileSPLObject->getFilename() ) == 'php' )
                {
                    $files[] = Library::formatPath( $fullFileName );
                }
            }
        }
        else
        {
            $files[] = PUBLIC_PATH . 'index.php';
            $files[] = PUBLIC_PATH . 'admin.php';

            $dir_iterator = new RecursiveDirectoryIterator( FRAMEWORK_PATH );
            $iterator = new RecursiveIteratorIterator( $dir_iterator, RecursiveIteratorIterator::SELF_FIRST );

            foreach ( $iterator as $fullFileName => $fileSPLObject )
            {
                if ( Library::getExtension( $fileSPLObject->getFilename() ) == 'php' )
                {
                    $files[] = Library::formatPath( $fullFileName );
                }
            }

            //  print_r($files);exit;

            $dir_iterator = $iterator = null;

            $dir_iterator = new RecursiveDirectoryIterator( MODULES_PATH );
            $iterator = new RecursiveIteratorIterator( $dir_iterator, RecursiveIteratorIterator::SELF_FIRST );
            foreach ( $iterator as $fullFileName => $fileSPLObject )
            {
                if ( Library::getExtension( $fileSPLObject->getFilename() ) == 'php' )
                {
                    $files[] = Library::formatPath( $fullFileName );
                }
            }


            $dir_iterator = $iterator = null;

            $dir_iterator = new RecursiveDirectoryIterator( WIDGET_PATH );
            $iterator = new RecursiveIteratorIterator( $dir_iterator, RecursiveIteratorIterator::SELF_FIRST );
            foreach ( $iterator as $fullFileName => $fileSPLObject )
            {
                if ( Library::getExtension( $fileSPLObject->getFilename() ) == 'php' )
                {
                    $files[] = Library::formatPath( $fullFileName );
                }
            }

            /*

              $dir_iterator = $iterator     = null;
              if ( is_dir( PAGE_CACHE_PATH . 'component/' ) )
              {
              $dir_iterator = new RecursiveDirectoryIterator( PAGE_CACHE_PATH . 'component/' );
              $iterator     = new RecursiveIteratorIterator( $dir_iterator, RecursiveIteratorIterator::SELF_FIRST );
              foreach ( $iterator as $fullFileName => $fileSPLObject )
              {
              if ( Library::getExtension( $fileSPLObject->getFilename() ) == 'php' )
              {
              $files[] = Library::formatPath( $fullFileName );
              }
              }
              }
              $dir_iterator = $iterator     = null;

              $dir_iterator = new RecursiveDirectoryIterator( PAGE_CACHE_PATH . 'data/' );
              $iterator     = new RecursiveIteratorIterator( $dir_iterator, RecursiveIteratorIterator::SELF_FIRST );
              foreach ( $iterator as $fullFileName => $fileSPLObject )
              {
              if ( Library::getExtension( $fileSPLObject->getFilename() ) == 'php' )
              {
              $files[] = Library::formatPath( $fullFileName );
              }
              }

              $dir_iterator = $iterator     = null;



             */
        }

        // cache all components
        $comps = $db->query( 'SELECT name FROM %tp%component' )->fetchAll();
        foreach ( $comps as $comp )
        {
            $files[] = self::getComponentPath( $comp[ 'name' ] );
        }

        foreach ( $files as $file )
        {
            preg_match_all( "/Hook::run\s*\n*\r*\(\n*\r*\s*(['\"])([a-zA-Z0-9_]+)\\1\s*\n*\r*([^\)]*)\);\s*\/\/\s*\{([^\}]*)\}\n/sU", file_get_contents( $file ), $matches );


            if ( !empty( $matches[ 2 ] ) )
            {

                // print_r( $matches[0] );

                foreach ( $matches[ 2 ] as $key => $match )
                {
                    $str = explode( '{CONTEXT:', $matches[ 0 ][ $key ] );

                    $_context = explode( 'DESC:', $str[ 1 ] );
                    $context = array_shift( $_context );
                    $description = array_shift( $_context );

                    $scanned_events[ $match ] = array(
                        'event'       => $match,
                        'description' => str_replace( '}', '', trim( $description ) ),
                        'context'     => str_replace( ',', '', trim( $context ) ) );
                }

                $matches = null;
            }
        }

        unset( $files );

        // work out which events are stored, but don't actually exist (and should be deleted)
        $delete = array_diff( $stored_events, array_keys( $scanned_events ) );

        // work out which events are not yet stored
        $add = array_diff( array_keys( $scanned_events ), $stored_events );

        foreach ( $delete as $event )
        {
            $db->query( 'DELETE FROM %tp%event WHERE event = ?', $event );
        }

        foreach ( $add as $event )
        {
            $db->query( 'INSERT %tp%event SET event = ?, description = ?, context = ?', $event, $scanned_events[ $event ][ 'description' ], $scanned_events[ $event ][ 'context' ] );
        }


        Cache::delete( 'event_hooks' );

        return array(
            'events' => count( $scanned_events ),
            'add'    => count( $add ),
            'delete' => count( $delete )
        );
    }

    /**
     *
     * @param string $name
     * @return string
     */
    public static function getComponentPath( $name )
    {

        if ( self::$db === null )
        {
            self::$db = Database::getInstance();
            Library::makeDirectory( PAGE_CACHE_PATH . 'component/' );
        }

        if ( isset( self::$componentPaths[ $name ] ) )
        {
            return self::$componentPaths[ $name ];
        }

        $path = PAGE_CACHE_PATH . 'component/component.' . $name . '.php';

        if ( !file_exists( $path ) )
        {
            $component = self::$db->query( 'SELECT name, component FROM %tp%component WHERE name = ?', $name )->fetch();
            if ( !$component[ 'name' ] )
            {
                Error::raise( sprintf( 'Could not found the Componente `%s`', $name ) );
                return false;
            }

            $f = fopen(PAGE_CACHE_PATH . 'component/component.' . $name . '.php', 'w');
            fwrite($f, $component[ 'component' ]);
            fclose($f);

        }

        self::$componentPaths[ $name ] = $path;

        return $path;
    }

    /**
     *
     * @return array
     */
    private static function _getModules()
    {
        return File::getSubDir( MODULES_PATH );
    }

    /**
     * 
     */
    public static function cleanControllerActionsAfterInstall()
    {

        self::syncEventHooks();
        self::scanEvents();
        self::cleanControllerActions();

        Settings::write(); // refresh the config after install
        // remove instller first run lock
        $cfgContent = file_get_contents( LIBRARY_PATH . 'config.inc.php' );
        $cfgContent = preg_replace( '#define\(\s*\'FIRSTRUN\'\s*,\s*true\s*\)\s*;#is', '', $cfgContent );
        file_put_contents( LIBRARY_PATH . 'config.inc.php', $cfgContent );
    }

    /**
     *
     * @return boolean
     */
    public static function cleanControllerActions()
    {

        $found = array();
        $allModules = self::_getModules();
        /**
         * 
         * @var Application
         */
        $application = Registry::getObject( 'Application' );

        // delete modul registry an refresh the registry
        $application->refreshModulRegistry();

        foreach ( $allModules as $modulKey )
        {
            $be = $application->getModulPermissionKeys( $modulKey, Application::BACKEND_MODE );

            if ( is_array( $be ) )
            {
                foreach ( $be as $action => $option )
                {
                    $found[] = array(
                        strtolower( $modulKey ),
                        $action,
                        intval( $option[ 'requirelogin' ] ),
                        intval( $option[ 'requirepermission' ] ),
                        1 );
                }
                $be = null;
            }


            $fe = $application->getModulPermissionKeys( $modulKey, Application::FRONTEND_MODE );
            if ( is_array( $fe ) )
            {
                foreach ( $fe as $action => $option )
                {
                    $found[] = array(
                        strtolower( $modulKey ),
                        $action,
                        intval( $option[ 'requirelogin' ] ),
                        intval( $option[ 'requirepermission' ] ),
                        0 );
                }
                $fe = null;
            }
        }

        #$found = array_unique($found);

        $db = Database::getInstance();
        $db->query( 'TRUNCATE TABLE %tp%actions' );
        $db->insert( '%tp%actions' )->columns( array(
            'controller',
            'action',
            'login',
            'permission',
            'isbackend' ) )->values( $found )->execute();

        $db = $application = null;

        return true;
    }

}
