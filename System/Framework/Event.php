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
 * @package
 * @version      3.0.0 Beta
 * @category
 * @copyright    2008-2014 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Event.php
 */


/**
 *
 *   Event::bind('init', function($r = array()){  echo 'Hello '. $r['username'];  });         << will use priority 1
 *   Event::bind('init', function($r = array()){  echo 'Hello '. $r['username'];  }, 2);
 *   namespace use:
 *   Event::bind('init.test', function($r = array()){  echo 'Hello '. $r['username'];  });    << will use priority 1
 *   Event::bind('init.test', function($r = array()){  echo 'Hello '. $r['username'];  }, 3);
 *
 *
 *
 *   Event::trigger('init', array('username' => 'Bob') );
 *   namespace use:
 *   Event::trigger('init.test', array('username' => 'Bill') );
 *
 *
 *   Event::unbind('init'); or Event::unbind('init', 1);
 *   namespace use:
 *   Event::unbind('init.test'); or Event::unbind('init.test', 3);
 *
 *
 *
 *   Event::getListener('init'); will return only this events whitout namspaced events!
 *   namespace use:
 *   Event::getListener('init.test'); will return only the namspace events
 */


/**
 * Class Event
 */
class Event
{

    private static $events = array();


    /**
     * first is the event name.
     *
     * @param string $event
     */
    public static function trigger($event)
    {

        // get rest of arguments
        $args = func_get_args();

        $event     = array_shift( $args ); // remove $event
        $namespace = false;
        if ( strpos( $event, '.' ) !== false )
        {
            list( $name, $namespace ) = explode( '.', $event );
        }
        else
        {
            $name = $event;
        }

        if ( $namespace )
        {
            if ( isset( self::$events[ $name ][ $namespace ] ) )
            {
                foreach ( self::$events[ $name ][ $namespace ] as $call )
                {
                    call_user_func_array( $call[ 'callback' ], $args );
                }
            }
        }
        else
        {
            if ( isset( self::$events[ $name ][ '__global' ] ) )
            {
                foreach ( self::$events[ $name ][ '__global' ] as $call )
                {
                    call_user_func_array( $call[ 'callback' ], $args );
                }
            }
        }
    }

    /**
     * Will add a new Event
     *
     * @param string $event the name of the event
     * @param callable|\the $callback the callback function
     * @param int $priority
     */
    public static function bindevent($event, \Closure $callback, $priority = 1)
    {

        if ( strpos( $event, '.' ) !== false )
        {
            list( $name, $namespace ) = explode( '.', $event );

            if ( !isset( self::$events[ $name ][ $namespace ] ) )
            {
                self::$events[ $name ][ $namespace ] = array();
            }

            self::$events[ $name ][ $namespace ][ ] = array('callback' => $callback, 'priority' => $priority);

            /**
             * Sort priority ?
             */
            if ( count( self::$events[ $name ][ $namespace ] ) > 1 )
            {
                usort( self::$events[ $name ][ $namespace ], function ($a, $b)
                {

                    if ( $a[ 'priority' ] == $b[ 'priority' ] )
                    {
                        return 0;
                    }

                    return ( $a[ 'priority' ] < $b[ 'priority' ] ) ? -1 : 1;
                } );
            }

        }
        else
        {
            if ( !isset( self::$events[ $event ][ '__global' ] ) )
            {
                self::$events[ $event ][ '__global' ] = array();
            }

            self::$events[ $event ][ '__global' ][ ] = array('callback' => $callback, 'priority' => $priority);


            /**
             * Sort priority ?
             */
            if ( count( self::$events[ $event ][ '__global' ] ) > 1 )
            {
                usort( self::$events[ $event ][ '__global' ], function ($a, $b)
                {

                    if ( $a[ 'priority' ] == $b[ 'priority' ] )
                    {
                        return 0;
                    }

                    return ( $a[ 'priority' ] < $b[ 'priority' ] ) ? -1 : 1;
                } );
            }
        }
    }


    /**
     * Will remove a Event
     *
     * @param string $event the name of the event
     * @param int|bool $priority default is false
     */
    public static function unbind($event, $priority = false)
    {

        if ( strpos( $event, '.' ) !== false )
        {
            list( $name, $namespace ) = explode( '.', $event );

            if ( $namespace !== '__global' )
            {
                if ( $priority )
                {
                    if ( isset( self::$events[ $event ][ $namespace ] ) )
                    {
                        foreach ( self::$events[ $event ][ $namespace ] as $idx => $call )
                        {
                            if ( $call[ 'priority' ] === $priority )
                            {
                                unset( self::$events[ $event ][ $namespace ][ $idx ] );
                            }
                        }
                    }
                }
                else
                {
                    unset( self::$events[ $name ][ $namespace ] );
                }
            }
            else
            {
                unset( self::$events[ $name ][ '__global' ] );
            }
        }
        else
        {

            if ( $priority )
            {
                if ( isset( self::$events[ $event ][ '__global' ] ) )
                {
                    foreach ( self::$events[ $event ][ '__global' ] as $idx => $call )
                    {
                        if ( $call[ 'priority' ] === $priority )
                        {
                            unset( self::$events[ $event ][ '__global' ][ $idx ] );
                        }
                    }
                }
            }
            else
            {
                unset( self::$events[ $event ][ '__global' ] );
            }
        }
    }


    /**
     * Returns all registred Events
     *
     * @return array
     */
    public static function getListeners()
    {

        return self::$events;
    }

    /**
     * Returns all registred Events by name (namespaces)
     *
     * @param string $event Example:
     *                      Event::getListener('init') or by namespace Event::getListener('init.test')
     * @return bool | array
     */
    public static function getListener($event)
    {

        $namespace = false;
        if ( strpos( $event, '.' ) !== false )
        {
            list( $name, $namespace ) = explode( '.', $event );
        }
        else
        {
            $name = $event;
        }

        if ( $namespace )
        {
            if ( isset( self::$events[ $name ][ $namespace ] ) )
            {
                return self::$events[ $name ][ $namespace ];
            }
        }
        else
        {
            if ( isset( self::$events[ $name ][ '__global' ] ) )
            {
                return self::$events[ $name ][ '__global' ];
            }
        }


        return false;
    }

} 