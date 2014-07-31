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
 * @file        CommandLine.php
 *
 */
class CommandLine
{

    public static $args;

    protected static $_commands = array(
        'h',
        'cd',
        'ls',
        'dir',
        'dirname',
        'date',
        'time' );

    /**
     * @param $cmd
     */
    public static function addCmd( $cmd )
    {
        array_push( self::$_commands, $cmd );
    }

    /**
     * PARSE ARGUMENTS
     *
     * @author              Patrick Fisher <patrick@pwfisher.com>
     * @since               August 21, 2009
     * @see                 http://www.php.net/manual/en/features.commandline.php
     *                      #81042 function arguments($argv) by technorati at gmail dot com, 12-Feb-2008
     *                      #78651 function getArgs($args) by B Crawford, 22-Oct-2007
     * @usage               $args = CommandLine::parseArgs($_SERVER['argv']);
     */
    public static function parseArgs( array $argv )
    {

        if ( (!defined( 'BACKEND_CONSOLE' ) && !BACKEND_CONSOLE) && count( $argv ) && substr( $argv[ 0 ], 0, 1 ) != '-' )
        {
            array_shift( $argv );
        }

        $out = array();

        foreach ( $argv as $arg )
        {

            // --foo --bar=baz
            if ( substr( $arg, 0, 2 ) == '--' )
            {
                $eqPos = strpos( $arg, '=' );

                // --foo
                if ( $eqPos === false )
                {
                    $key = substr( $arg, 2 );
                    $value = isset( $out[ $key ] ) ? $out[ $key ] : true;
                    $out[ $key ] = $value;
                } // --bar=baz
                else
                {
                    $key = substr( $arg, 2, $eqPos - 2 );
                    $value = substr( $arg, $eqPos + 1 );
                    $out[ $key ] = $value;
                }
            } // -k=value -abc
            else if ( substr( $arg, 0, 1 ) == '-' )
            {

                // -k=value
                if ( substr( $arg, 2, 1 ) == '=' )
                {
                    $key = substr( $arg, 1, 1 );
                    $value = substr( $arg, 3 );
                    $out[ $key ] = $value;
                } // -abc
                else
                {
                    $chars = str_split( substr( $arg, 1 ) );
                    foreach ( $chars as $char )
                    {
                        $key = $char;
                        $value = isset( $out[ $key ] ) ? $out[ $key ] : true;
                        $out[ $key ] = $value;
                    }
                }
            } // plain-arg
            else
            {

                if ( in_array( strtolower( $arg ), self::$_commands ) )
                {
                    $out[ strtolower( $arg ) ] = $arg;
                }
                else
                {


                    $value = $arg;
                    $out[] = $value;
                }
            }
        }

        self::$args = $out;

        return $out;
    }

    /**
     * GET BOOLEAN
     */
    public static function getBoolean( $key, $default = false )
    {
        if ( !isset( self::$args[ $key ] ) )
        {
            return $default;
        }
        $value = self::$args[ $key ];
        if ( is_bool( $value ) )
        {
            return $value;
        }
        if ( is_int( $value ) )
        {
            return (bool) $value;
        }
        if ( is_string( $value ) )
        {
            $value = strtolower( $value );
            $map = array(
                'y'     => true,
                'n'     => false,
                'yes'   => true,
                'no'    => false,
                'true'  => true,
                'false' => false,
                '1'     => true,
                '0'     => false,
                'on'    => true,
                'off'   => false,
            );
            if ( isset( $map[ $value ] ) )
            {
                return $map[ $value ];
            }
        }
        return $default;
    }

    /**
     * Parses an argument to find out what code to run, defines constants needed to run the request
     */
    public static function parseCommand( $arg )
    {
        $cmd = explode( '/', $arg );
        $controller = $cmd[ 0 ];

        if ( !empty( $cmd[ 1 ] ) )
        {
            $action = $cmd[ 1 ];
        }
        else
        {
            $action = 'index';
        }

        /**
         *
         */
        define( 'CONTROLLER', $controller );
        /**
         *
         */
        define( 'ACTION', $action );
    }

    /**
     * This funtion will take a pattern and a folder as the argument and go thru it(recursivly if needed)and return the list of
     *               all files in that folder.
     * Link             : http://www.bin-co.com/php/scripts/filesystem/ls/
     *
     *
     *
      $pattern
      The first argument is the pattern(or the file mask) to look out for.
     * Example: ls("*.html"). This supports any pattern supported by the glob function.
     *                        This is an optional argument - if nothing is given, it defaults to "*". Some possible values...
     *    Matches everything.
     * .php    All files with the extension php
     * .{php,html}    Files with extension 'php' or 'html'
      file[1-3].php	It could match file1.php, file2.php and file3.php
      $folder
      The path of the directory of which directory list you want. This is an optional argument. If empty, the function will assume the value to be the current folder.
      $recursively
      The function will traverse the folder tree recursively if this is true. Defaults to false.
      $options
      An array of values 'return_files' or 'return_folders' or both. This decides what must be returned.
     *
     *
     *
     *
     *
     *
     * Arguments     :  $pattern - The pattern to look out for [OPTIONAL]
     *                    $folder - The path of the directory of which's directory list you want [OPTIONAL]
     *                    $recursivly - The funtion will traverse the folder tree recursivly if this is true. Defaults to false. [OPTIONAL]
     *                    $options - An array of values 'return_files' or 'return_folders' or both
     * Returns       : A flat list with the path of all the files(no folders) that matches the condition given.
     */
    static function ls( $pattern = "*", $folder = "", $recursivly = false, $options = array(
        'return_files',
        'return_folders' ) )
    {
        if ( $folder )
        {
            $current_folder = realpath( '.' );
            if ( in_array( 'quiet', $options ) )
            { // If quiet is on, we will suppress the 'no such folder' error
                if ( !file_exists( $folder ) )
                {
                    return array();
                }
            }

            if ( !chdir( $folder ) )
            {
                return array();
            }
        }


        $get_files = in_array( 'return_files', $options );
        $get_folders = in_array( 'return_folders', $options );
        $both = array();
        $folders = array();

        // Get the all files and folders in the given directory.
        if ( $get_files )
        {
            $both = glob( $pattern, GLOB_BRACE + GLOB_MARK );
        }
        if ( $recursivly || $get_folders )
        {
            $folders = glob( "*", GLOB_ONLYDIR + GLOB_MARK );
        }
        //If a pattern is specified, make sure even the folders match that pattern.
        $matching_folders = array();
        if ( $pattern !== '*' )
        {
            $matching_folders = glob( $pattern, GLOB_ONLYDIR + GLOB_MARK );
        }
        //Get just the files by removing the folders from the list of all files.
        $all = array_values( array_diff( $both, $folders ) );

        if ( $recursivly or $get_folders )
        {
            foreach ( $folders as $this_folder )
            {
                if ( $get_folders )
                {
                    //If a pattern is specified, make sure even the folders match that pattern.
                    if ( $pattern !== '*' )
                    {
                        if ( in_array( $this_folder, $matching_folders ) )
                        {
                            array_push( $all, $this_folder );
                        }
                    }
                    else
                    {
                        array_push( $all, $this_folder );
                    }
                }

                if ( $recursivly )
                {
                    // Continue calling this function for all the folders
                    $deep_items = self::ls( $pattern, $this_folder, $recursivly, $options ); # :RECURSION:

                    foreach ( $deep_items as $item )
                    {
                        array_push( $all, $this_folder . $item );
                    }
                }
            }
        }

        if ( $folder )
        {
            chdir( $current_folder );
        }

        $all = array_reverse( $all );

        return $all;
    }

    /**
     * @param $path
     * @return string
     */
    private static function preparePath( $path )
    {
        if ( substr( $path, 0, 1 ) == '/' )
        {
            #  $path = substr($path, 1);
        }


        if ( substr( $path, -1 ) == '/' )
        {
            $path = substr( $path, 0, -1 );
        }


        return $path;
    }

    /**
     * @param $path
     * @return mixed|string
     */
    private static function absolute( $path )
    {
        if ( substr( $path, 0, 1 ) != '/' )
        {
            $path = '/' . Session::get( 'consolePath' ) . '/' . $path;
        }
        $path = str_replace( '//', '/', $path );
        return $path;
    }

    /**
     * @param $p
     * @return array|mixed|string
     */
    private static function path_expand( $p )
    {
        $p = self::absolute( $p );
        $p = explode( '/', $p );

        $newpath = array();
        foreach ( $p as $c )
        {
            if ( $c == '..' && count( $newpath > 0 ) )
            {
                array_pop( $newpath );
            }
            elseif ( $c == '.' )
            {
                continue;
            }
            elseif ( $c != '' )
            {
                array_push( $newpath, $c );
            }
        }

        $p = '/' . implode( '/', $newpath );

        return $p;
    }

    /**
     * @param $path
     */
    public static function cd( $path )
    {
        $current_folder = realpath( '.' );


        $outPath = 'Invalid Dirname';

        if ( $path )
        {
            //change to home, or...
            $_path = Session::get( 'consolePath' ) ? Session::get( 'consolePath' ) : '';


            $p = self::preparePath( self::path_expand( $path ) );


            if ( file_exists( ROOT_PATH . $p ) && is_dir( ROOT_PATH . $p ) )
            {
                $outPath = $p;

                Session::save( 'consolePath', (string) $outPath );
            }
            else
            {
                $outPath = 'Invalid Dirname';
                Session::save( 'consolePath', '' );
            }


            Session::write();
        }
        else
        {
            $p = self::preparePath( self::path_expand( $path ) );

            if ( file_exists( ROOT_PATH . $p ) && is_dir( ROOT_PATH . $p ) )
            {
                $outPath = $p;

                Session::save( 'consolePath', (string) $outPath );
            }
            else
            {
                $outPath = 'Invalid Dirname';
                Session::save( 'consolePath', '' );
            }

            Session::write();
        }


        ob_flush();

        echo $outPath;

        ob_end_clean();
        $output = ob_get_contents();
        ob_get_clean();


        echo Library::json( array(
            'success' => true,
            'output'  => $output,
            'cwd'     => $outPath
        ) );

        exit;
    }

}

?>