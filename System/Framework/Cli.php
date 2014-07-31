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
 * @file        Cli.php
 *
 */
class Cli
{

    protected static $_registredCLIModules = null;

    /**
     *
     * @var Application
     */
    protected static $_applicationInstance = null;

    public $arguments = array();

    public $commands = array();

    public static $syscommands = array(
        'help' => 'Show the help',
        'v'    => 'Show the version of this DreamCMS'
    );

    /**
     * @param $args
     * @param $cmd
     */
    public function __construct( $args, $cmd )
    {
        $this->arguments = $args;

        self::$_applicationInstance = Registry::getObject( 'Application' );
        $this->registerCliModules();

        if (
                isset( $args[ 'help' ] ) ||
                isset( $args[ 'h' ] )
        )
        {
            $this->printHelp( $cmd );
        }
    }

    private function registerCliModules()
    {
        if ( is_array( self::$_registredCLIModules ) )
        {
            return;
        }

        # Library::enableErrorHandling();
        # error_reporting( E_ALL );

        $reg = self::$_applicationInstance->getModulRegistry();
        foreach ( $reg as $modul => $opts )
        {
            if ( file_exists( MODULES_PATH . ucfirst( $modul ) . '/Config/Console.php' ) )
            {
                $className = ucfirst( $modul ) . '_Config_Console';

                if ( !class_exists( $className, false ) && checkClassMethod( $className . '/getHelp', 'static' ) )
                {
                    $helpitems = call_user_func( $className . '::getHelp' );
                    # die($helpitems);
                    # exit;
                    if ( is_array( $helpitems ) && count( $helpitems ) )
                    {
                        //  self::$_registredCLIModules[ $modul ] = $helpitems;

                        CommandLine::addCmd( $modul );

                        $this->commands[ $modul ] = $helpitems;
                    }
                }
            }
        }
    }

    /**
     * @param null $cmd
     */
    public function printHelp( $cmd = null )
    {
        //  ob_start();
        // print only the command help
        if ( $cmd )
        {

            $linewidth = 30;
            if ( isset( $this->commands[ $cmd ] ) )
            {
                self::p( 'Command "' . $cmd . '" options' );
                foreach ( $this->commands[ $cmd ] as $exec => $help )
                {
                    $spacer2 = str_repeat( " ", $linewidth - (strlen( $cmd . ' --' . $exec ) + 2) );
                    self::p( $cmd . ' --' . $exec . $spacer2 . $help );
                }
            }
            else
            {
                self::p( 'Command "' . $cmd . '" not found' );
            }

            self::nl();

            if ( defined( 'BACKEND_CONSOLE' ) && BACKEND_CONSOLE )
            {
                ob_flush();

                ob_end_clean();
                $output = ob_get_contents();
                ob_get_clean();

                /* Welche Header wurden gesendet? */
                #var_dump(headers_list());
                #exit;
                #Ajax::Send(true, array('output' => $output));


                echo Library::json( array(
                    'success' => true,
                    'output'  => $output ) );
            }
            else
            {
                ob_flush();
            }
            exit;
        }





        self::p( 'List of all available arguments' );


        $linewidth = 30;
        foreach ( self::$syscommands as $command => $help )
        {
            $spacer = str_repeat( " ", $linewidth - (strlen( $command ) + 2) );
            self::p( '--' . $command . $spacer . $help );
        }

        self::p( 'List of all available modules' );
        $spacer = str_repeat( " ", $linewidth - (strlen( 'dirname' ) + 2) );
        self::p( 'dirname' . $spacer . 'print the current path' );

        $spacer = str_repeat( " ", $linewidth - (strlen( 'ls' ) + 2) );
        self::p( 'ls' . $spacer . 'list dirs' );

        $spacer = str_repeat( " ", $linewidth - (strlen( 'cd [dir]' ) + 2) );
        self::p( 'cd [dir]' . $spacer . 'get dir' );

        foreach ( $this->commands as $modul => $help )
        {
            foreach ( $help as $exec => $helps )
            {
                $spacer2 = str_repeat( " ", $linewidth - (strlen( $modul . ' --' . $exec ) + 2) );
                self::p( $modul . ' --' . $exec . $spacer2 . $helps );
            }
        }

        self::nl();


        if ( defined( 'BACKEND_CONSOLE' ) && BACKEND_CONSOLE )
        {
            ob_flush();

            ob_end_clean();
            $output = ob_get_contents();
            ob_get_clean();

            /* Welche Header wurden gesendet? */
            #var_dump(headers_list());
            #exit;
            #Ajax::Send(true, array('output' => $output));


            echo Library::json( array(
                'success' => true,
                'output'  => $output ) );
        }
        else
        {
            ob_flush();
        }

        exit;
    }

    /**
     * @param $s
     */
    static function p( $s )
    {
        $s = strip_tags( preg_replace( '#<br\s*/?\s*>#is', "\n", $s ) );
        echo $s . " \n";
    }

    static function nl()
    {
        echo "\n";
    }

    static function progress()
    {
        echo "#";
    }

    /**
     * show a status bar in the console
     *
     * <code>
     * for($x=1;$x<=100;$x++){
     *
     *     Cli::show_status($x, 100);
     *
     *     usleep(100000);
     *
     * }
     * </code>
     *
     * @param   int $done   how many items are completed
     * @param   int $total  how many items are to be done total
     * @param   int $size   optional size of the status bar
     * @return  void
     *
     */
    static function Status( $done = 1, $total = 1, $size = 30 )
    {
        static $start_time;

        // if we go over our bound, just ignore it
        if ( $done > $total )
        {
            return;
        }

        if ( empty( $start_time ) )
        {
            $start_time = time();
        }

        $now = TIMESTAMP;
        if ( $done )
        {
            $perc = (double) ($done / $total);
            $bar = floor( $perc * $size );
        }
        else
        {
            $perc = (double) 0;
            $bar = floor( $perc * $size );
        }

        $status_bar = "\r[";
        $status_bar .= str_repeat( "#", $bar );
        if ( $bar < $size )
        {
            $status_bar .= " ";
            $status_bar .= str_repeat( " ", $size - $bar );
        }
        else
        {
            $status_bar .= "#";
        }

        $disp = number_format( $perc * 100, 0 );

        $status_bar .= "] $disp%  $done/$total";

        $rate = ($now - $start_time) / $done;
        $left = $total - $done;
        $eta = round( $rate * $left, 2 );

        $elapsed = $now - $start_time;

        $status_bar .= "  remaining: " . number_format( $eta ) . " sec.  elapsed: " . number_format( $elapsed ) . " sec.";

        $clean = ob_get_clean();
        unset( $clean );

        echo $status_bar;
        flush();

        // when done, send a newline
        if ( $done == $total )
        {
            echo "\n";
        }
    }

    /**
     *
     * @param $cmd
     * @param type $params
     */
    static function execSystem( $cmd, $params )
    {
        if ( isset( $params[ 'v' ] ) )
        {
            self::p( 'DreamCMS v' . VERSION . ' (www.dcms-studio.de)' );

            if ( defined( 'BACKEND_CONSOLE' ) && BACKEND_CONSOLE )
            {
                ob_flush();

                ob_end_clean();
                $output = ob_get_contents();
                ob_get_clean();


                echo Library::json( array(
                    'success' => true,
                    'output'  => $output ) );
                exit;
            }
        }

        if ( $params[ 0 ] === $cmd )
        {
            array_shift( $params );
        }

        $_action = '';

        if ( is_array( $params ) )
        {
            $i = 0;
            foreach ( $params as $key => $value )
            {
                if ( is_string( $key ) && is_bool( $value ) )
                {
                    $_action = $key;
                    unset( $params[ $key ] );
                    break;
                }
                else
                {
                    $_action = $value;
                    unset( $params[ $key ] );
                    break;
                }
            }
        }


        // clean action
        $action = preg_replace( '#([^a-z0-9_]*)#i', '', $_action );

        if ( $action != $_action )
        {
            self::p( 'No no... Hack your Server!' );
        }
        else
        {

            if ( !empty( $action ) )
            {
                ob_end_clean();

                $className = ucfirst( $cmd ) . '_Config_Console';

                $action = strtolower( $action );

                if ( checkClassMethod( $className . '/' . $action, 'static' ) )
                {
                    call_user_func( $className . '::' . $action, $params );
                }
                else
                {
                    //print_r( $params );
                    self::p( 'Invalid command call. The command "' . $cmd . '" has no action "' . $action . '"' );
                }
            }
            else
            {
                //print_r( $params );
                self::p( 'Invalid command call. ' );
            }
        }
    }

}

?>