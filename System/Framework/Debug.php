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
 * @package      DreamCMS
 * @version      3.0.0 Beta
 * @category     Framework
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Debug.php
 */

/** @noinspection PhpUndefinedClassInspection */
class Debug
{

    /**
     * @var
     */
    static private $startTime;

    /**
     * @var bool
     */
    static private $currentStartTime = false;

    /**
     * @var bool
     */
    static private $currentTime = false;

    static private $totalTime = null;

    /**
     * @var null
     */
    static private $log = null;

    /**
     * @var int
     */
    static private $countMemoryLogs = 0;

    /**
     * @var array
     */
    static private $backtrace = array();

    /**
     * @var array
     */
    static private $marker = array();

    static private $enableSyslog;

    /**
     *
     */
    public function __construct()
    {

    }

    /**
     * @param $filename
     * @param $str
     */
    public static function putFile($filename, $str)
    {

        file_put_contents( DATA_PATH . 'logs/' . $filename, $str . "\n", FILE_APPEND | LOCK_EX );
    }

    /**
     *
     */
    public static function initDebugger()
    {

        $mt = self::getMicroTime();

        self::$startTime        = $mt;
        self::$currentTime      = $mt;
        self::$currentStartTime = $mt;
        self::$enableSyslog     = defined( 'SYS_LOGS' );
    }

    /**
     *
     * @param string $name
     */
    public static function setMarker($name)
    {

        self::$marker[ $name ] = self::getMicroTime();
    }

    /**
     *
     * @return type
     */
    public static function getMicroTime()
    {
        return microtime( true );
    }

    /**
     *
     * @param integer $time
     * @return string
     */
    public static function getReadableTime($time)
    {
        $ret       = $time;
        $formatter = 0;
        $formats   = array(
            'ms',
            's',
            'm'
        );

        if ( $time >= 1000 && $time < 60000 )
        {
            $formatter = 1;
            $ret       = ( $time / 1000 );
        }

        if ( $time >= 60000 )
        {
            $formatter = 2;
            $ret       = ( $time / 1000 ) / 60;
        }

        $ret = number_format( $ret, 3, '.', '' ) . ' ' . $formats[ $formatter ];

        return $ret;
    }

    /**
     *
     * @param bool|\type $object
     * @return type
     */
    public static function logMemory($object = false)
    {

        $memory = memory_get_usage();
        if ( $object )
        {
            $memory = strlen( serialize( $object ) );
        }

        return $memory;
    }

    /**
     *
     * @param string $process
     * @param string $msg
     * @param bool|int $time
     * @param string $sql
     * @return void
     */
    public static function store($process = '', $msg = '', $time = false, $sql = '')
    {

        if ( DEBUG !== true /* || !defined( 'SKIP_DEBUG' ) || (defined( 'SKIP_DEBUG' ) && SKIP_DEBUG === true) */ || ( self::$enableSyslog === 1 && FRONTEND_DEBUG_OUTPUT !== true ) || $sql === 'SELECT found_rows() AS rows' )
        {
            return;
        }

        $mt = self::getMicroTime();


        $cumulativeTime = ( $mt - ( $time !== false && $time > 0 ? $time : self::$currentStartTime ) );

        $msg = str_replace( ROOT_PATH, '', $msg );

        self::$log[ ]           = array(
            'start'    => self::$currentStartTime,
            'duration' => $cumulativeTime,
            'memory'   => memory_get_usage(),
            'name'     => $process,
            'message'  => $msg,
            'sql'      => $sql
        );
        self::$currentStartTime = $mt;
        self::$currentTime      = $mt;
    }

    /**
     *
     * @param string $msg
     * @return void
     */
    public static function putLastStore($msg = '')
    {

        if ( !$msg )
        {
            return;
        }

        self::$log = array_reverse( self::$log );

        self::$log[ 0 ][ 'message' ] .= ' ' . $msg;

        self::$log = array_reverse( self::$log );
    }

    /**
     *
     * @param int $size
     * @param string $retstring
     * @return string
     */
    public static function getReadableFileSize($size, $retstring = null)
    {

        // adapted from code at http://aidanlister.com/repos/v/function.size_readable.php
        $sizes = array(
            'bytes',
            'kB',
            'MB',
            'GB',
            'TB',
            'PB',
            'EB',
            'ZB',
            'YB'
        );

        if ( $retstring === null )
        {
            $retstring = '%01.2f %s';
        }

        $lastsizestring = end( $sizes );

        foreach ( $sizes as $sizestring )
        {
            if ( $size < 1024 )
            {
                break;
            }
            if ( $sizestring != $lastsizestring )
            {
                $size /= 1024;
            }
        }
        if ( $sizestring === $sizes[ 0 ] )
        {
            $retstring = '%01d %s';
        } // Bytes aren't normally fractional
        return sprintf( $retstring, $size, $sizestring );
    }

    /**
     *
     * @param bool $outputType if is true then create an HTML comment string (default is false)
     * @return string
     */
    public static function write($outputType = false)
    {

        if ( !count( self::$log ) /* || SKIP_DEBUG === true */ )
        {
            return '';
        }


        /*
          $pqp_output['files'] = $fileList;
          $pqp_output['fileTotals'] = $fileTotals;
         */

        $s = '';
        if ( $outputType === false )
        {
            $s .= "\n\n<!--\n\n";
        }

        $s .= "DreamCMS DEBUG\n\n";
        $s .= "Current Locale   : " . T_setlocale( LC_MESSAGES, 0 ) . "\n";
        $s .= "Emulating Locale : " . ( locale_emulation() ? 'true' : 'false' ) . "\n";

        $s .= "GUI Language     : " . ( defined( 'GUI_LANGUAGE' ) ? GUI_LANGUAGE : '' ) . "\n";
        $s .= "Content Language : " . ( defined( 'CONTENT_TRANS' ) ? CONTENT_TRANS : '' ) . "\n\n";
        $s .= "Request : " . ( isset( $_SERVER[ 'REQUEST_URI' ] ) ? $_SERVER[ 'REQUEST_URI' ] : ( defined( 'REQUEST' ) ? REQUEST : '' ) ) . "\n\n";
        $s .= "The debug information reveals much about your site and adds a couple of Kb to the size of your page.\n\n";
        $s .= "Please note that times may be slightly off due to rounding.\n\n";
        $s .= "TOTAL:    the cumulative time taken to complete the request\n";
        $s .= "PROCESS:  the time taken to complete a specific function call\n";
        $s .= "FUNCTION: the function call\n";
        $s .= "COMMENT:  extra information reported by the function\n\n";
        $s .= "TOTAL        PROCESS      MEMORY       FUNCTION                         COMMENT\n";
        $s .= "==========   ==========   ==========   ==============================   ==========================================================================\n";


        $total = 0;

        foreach ( self::$log as $debug )
        {
            $total += $debug[ 'duration' ];

            $s .= '' . self::getReadableTime( $total ) . '     ' . self::getReadableTime( $debug[ 'duration' ] ) . '     ';

            $s .= sprintf( "%-13s", self::getReadableFileSize( $debug[ 'memory' ] ) );

            $s .= sprintf( "%-33s", $debug[ 'name' ] );

            $debug[ 'message' ] = substr( $debug[ 'message' ], 0, 320 );
            $s .= sprintf( "%-74s", $debug[ 'message' ] );
            $s .= chr( 10 );
        }

        self::$log = null;

        /*
          $files = get_included_files();

          $fileList   = array();
          $fileTotals = array(
          "count"   => count( $files ),
          "size"    => 0,
          "largest" => 0
          );

          foreach ( $files as $key => $file )
          {
          if ( !is_file( $file ) )
          {
          continue;
          }
          $size = filesize( $file );


          $fileTotals[ 'size' ] += $size;
          if ( $size > $fileTotals[ 'largest' ] )
          {
          $fileTotals[ 'largest' ] = $size;
          }
          }


          $fileTotals[ 'size' ]    = self::getReadableFileSize( $fileTotals[ 'size' ] );
          $fileTotals[ 'largest' ] = self::getReadableFileSize( $fileTotals[ 'largest' ] );

         */


        $s .= "\r\n";

        $s .= "========================================================\r\n";

        $s .= 'Memory Used:           ' . self::getReadableFileSize( memory_get_usage() ) . "\r\n";
        $s .= 'Memory Limit:          ' . ini_get( "memory_limit" ) . "\r\n";


        $s .= "========================================================\r\n";
        $s .= "Compiler Version:      " . Compiler::VERSION . "\r\n";
        $s .= 'Compiler Memory:       ' . self::getReadableFileSize( Compiler::getCompileMemory() ) . "\r\n";
        $s .= 'Compiler Time:         ' . self::getReadableTime( Compiler::getCompileTimer() ) . "\r\n";

        //    $s .= "========================================================\r\n";
        //    $s .= 'File Includes:         ' . $fileTotals[ 'count' ] . "\r\n";
        //    $s .= 'Size of File Includes: ' . $fileTotals[ 'size' ] . "\r\n";
        //    $s .= 'Largest File:          ' . $fileTotals[ 'largest' ] . "\r\n";


        $db              = Database::getInstance();
        $_time           = ( self::getMicroTime() - START );
        self::$totalTime = $_time;

        $s .= "========================================================\r\n";
        $s .= 'Script Time:           ' . self::getReadableTime( $_time ) . "\r\n";
        #     $s .= 'Template Render Time:  ' . sprintf("%2.4f s", Template::getRenderTime() ) . ' (' . sprintf("%2.2f", (Template::getRenderTime() * 100) / $total) . '%)' . "\r\n";
        $s .= 'Database Time:         ' . self::getReadableTime( $db->getQueryTimer() ) . "\r\n";

        #	$s .= "========================================================\r\n";
        #	$s .= "Request Data:\n\n";
        #	$s .= strip_tags(self::_dump( HTTP::input(), true ));
        #	$s .= "\r\n";

        $s .= "========================================================\r\n\r\n";
        $s .= "Database Debug Information:\r\n";


        $s .= $outputType !== false ? htmlspecialchars( $db->getDebug() ) : $db->getDebug();

        if ( $outputType === false )
        {
            $s .= "\r\n-->";
        }

        return $s;
    }


    /**
     * @return null|type
     */
    public static function getScriptRuntime()
    {

        if ( self::$totalTime !== null )
        {
            return self::$totalTime;
        }

        return ( self::getMicroTime() - START );
    }

    /**
     * Returns an HTML string of debugging information about any number of
     * variables, each wrapped in a "pre" tag:
     *
     *     // Displays the type and value of each variable
     *     echo Debug::vars($foo, $bar, $baz);
     *
     * @param   mixed   variable to debug
     * @param   ...
     * @return  string
     */
    public static function vars()
    {

        if ( func_num_args() === 0 )
        {
            return '';
        }

        // Get all passed variables
        $variables = func_get_args();

        $output = array();
        foreach ( $variables as $var )
        {
            $output[ ] = Debug::_dump( $var, 1024 );
        }

        return '<pre class="debug">' . implode( "\n", $output ) . '</pre>';
    }

    /**
     * Returns an HTML string of information about a single variable.
     * Borrows heavily on concepts from the Debug class of [Nette](http://nettephp.com/).
     *
     * @param   mixed $value variable to dump
     * @param   integer $length maximum length of strings
     * @param   integer $level_recursion recursion limit
     * @return  string
     */
    public static function dump($value, $length = 250, $level_recursion = 1)
    {

        return Debug::_dump( $value, $length, $level_recursion, 0 );
    }

    static $currentLevel = null;

    public static function resetDump()
    {
        self::$currentLevel = null;
    }

    /**
     * @param $value
     * @return mixed|string
     */
    public static function protectPath($value)
    {

        if ( is_string( $value ) )
        {
            if ( strpos( $value, ROOT_PATH ) !== false )
            {
                $value = str_replace( ROOT_PATH, 'ROOT_PATH/', $value );
                #   $value = 'ROOT_PATH' . DIRECTORY_SEPARATOR . substr($value, strlen(ROOT_PATH));
            }
        }

        return $value;
    }

    /**
     * Helper for Debug::dump(), handles recursion in arrays and objects.
     *
     * @param   mixed $var variable to dump
     * @param   integer $length maximum length of strings
     * @param   integer $limit recursion limit
     * @param   integer $level current recursion level (internal usage only!)
     * @return  string
     */
    protected static function _dump(&$var, $length = 250, $limit = 2, $level = 0)
    {
        /*
		if ( !is_int(self::$currentLevel) )
		{
			self::$currentLevel = $level;
		}

		if ( self::$currentLevel > $limit )
		{
			return '';
		}

		self::$currentLevel++;
*/

        if ( $var === null )
        {
            return '<i class="nu">NULL</i>';
        }
        elseif ( is_bool( $var ) )
        {
            return '<i class="bo">(bool) ' . ( $var ? 'TRUE' : 'FALSE' ) . '</i>';
        }
        elseif ( is_float( $var ) )
        {
            return '<i class="flo">(float) ' . $var . '</i>';
        }
        elseif ( is_numeric( $var ) || is_integer( $var ) )
        {
            return '<i class="num">(int) ' . $var . '</i>';
        }
        elseif ( is_resource( $var ) )
        {
            if ( ( $type = get_resource_type( $var ) ) === 'stream' AND $meta = stream_get_meta_data( $var ) )
            {
                $meta = stream_get_meta_data( $var );

                if ( isset( $meta[ 'uri' ] ) )
                {
                    $file = $meta[ 'uri' ];

                    if ( function_exists( 'stream_is_local' ) )
                    {
                        // Only exists on PHP >= 5.2.4
                        if ( stream_is_local( $file ) )
                        {
                            $file = Debug::path( $file );
                        }
                    }

                    # $file = self::protectPath($file);

                    return '<small>resource</small><span>(' . $type . ')</span> ' . htmlspecialchars( $file, ENT_NOQUOTES, Application::$charset );
                }
            }
            else
            {
                return '<small>resource</small><span>(' . $type . ')</span>';
            }
        }
        elseif ( is_string( $var ) )
        {
            // Clean invalid multibyte characters. iconv is only invoked
            // if there are non ASCII characters in the string, so this
            // isn't too much of a hit.
            #$var = UTF8::clean($var, Application::$charset);

            if ( UTF8::strlen( $var ) > $length && $length > 0 )
            {
                // Encode the truncated string
                $s   = UTF8::substr( $var, 0, $length );
                $str = htmlspecialchars( self::protectPath( $s ) ) . '&nbsp;&hellip;';
            }
            else
            {
                // Encode the string
                $str = htmlspecialchars( self::protectPath( $var ) );
            }

            return '<small>string</small><span>(' . strlen( $var ) . ')</span> ' . $str . '';
        }
        elseif ( is_array( $var ) )
        {
            $output = array();

            // Indentation for this variable
            $space = str_repeat( $s = '    ', $level );

            static $marker;

            if ( $marker === null )
            {
                // Make a unique marker
                $marker = uniqid( "\x00" );
            }

            if ( empty( $var ) )
            {
                // Do nothing
            }
            elseif ( isset( $var[ $marker ] ) )
            {
                $output[ ] = "<i class=\"op\">(</i>\n$space$s*RECURSION*\n$space<i class=\"op\">)</i>";
            }
            elseif ( $level < $limit )
            {
                $output[ ] = "<span>";

                $var[ $marker ] = true;
                foreach ( $var as $key => & $val )
                {
                    if ( $key === $marker )
                    {
                        continue;
                    }


                    if ( !is_int( $key ) )
                    {

                        if ( $key === 'cfg' || $key === 'crypt_key' || $key === 'cli_key' || $key == 'uniqidkey' || ( $key === 'host' || $key === 'dbname' || $key === 'username' || $key === 'password' || $key === 'port' || $key === '_hostname' || $key === '_username' || $key === '_password' || $key === '_port' || ( $key && strstr( $key, 'smtp' ) ) || ( $key && strstr( $key, 'mail' ) )


                            )
                        )
                        {
                            $val = '*** Protected ***';
                        }
                        else
                        {
                            //$val = $val; //self::protectPath($val);
                        }

                        $key = '"' . htmlspecialchars( $key ) . '"';
                    }


                    $output[ ] = "$space$s$key <i class=\"op\">=></i> " . Debug::_dump( $val, $length, $limit, $level + 1 );
                }
                unset( $var[ $marker ] );

                $output[ ] = "$space</span>";
            }
            else
            {
                // Depth too great
                $output[ ] = "\n$space$s...\n$space";
            }

            return '<small class="atoggle">array</small><span>(' . count( $var ) . ')</span><i class="op">(</i> <span class="array">' . implode( "\n", $output ) . '</span><i class="op">)</i>';
        }
        elseif ( is_object( $var ) )
        {
            // Copy the object as an array
            $array = (array)$var;

            $output = array();

            // Indentation for this variable
            $space = str_repeat( $s = '    ', $level );

            $hash = spl_object_hash( $var );

            // Objects that are being dumped
            static $objects = array();

            if ( empty( $var ) )
            {
                // Do nothing
            }
            elseif ( isset( $objects[ $hash ] ) )
            {
                $output[ ] = "\n$space$s*RECURSION*\n$space";
            }
            elseif ( $level < $limit )
            {
                $output[ ] = "<code>";

                $objects[ $hash ] = true;
                foreach ( $array as $key => $val )
                {
                    if ( $key[ 0 ] === "\x00" )
                    {
                        // Determine if the access is protected or protected
                        $access = '<small>' . ( ( $key[ 1 ] === '*' ) ? 'protected' : 'private' ) . '</small>';

                        // Remove the access level from the variable name
                        $key = substr( $key, strrpos( $key, "\x00" ) + 1 );
                    }
                    else
                    {
                        $access = '<small>public</small>';
                    }


                    if ( $key === 'cfg' || $val === 'cfg' || $key === 'crypt_key' || $key === 'cli_key' || strpos( get_class( $var ), 'Database' ) !== false && ( $key == 'uniqidkey' || $key == 'email' || $key == 'uniqidkey' || $key == 'email' || $key === '_databaseName' || $key === '_hostname' || $key === '_username' || $key === '_password' || $key === '_port' ) || preg_match( '/.*smtp_.*/i', $key )
                    )
                    {
                        $val = '*** Protected ***';
                    }
                    else
                    {
                        //$val = $val; //self::protectPath($val);
                    }


                    $output[ ] = "$space$s$access $key <i class=\"op\">=></i> " . Debug::_dump( $val, $length, $limit, $level + 1 );
                }
                //unset( $objects[ $hash ] );

                $output[ ] = "$space</code>";
            }
            else
            {
                // Depth too great
                $output[ ] = "\n$space$s...\n$space";
            }

            return '<small class="atoggle">object</small> <span>' . get_class( $var ) . '(' . count( $array ) . ')</span> <i class="op">{</i><span class="array">' . implode( "\n", $output ) . '</span><i class="op">}</i>';
        }
        else
        {
            return '<small>' . gettype( $var ) . '</small> ' . htmlspecialchars( print_r( $var, true ) );
        }
    }

    /**
     * Removes librarypath, framworkpath, public, or rootpath from a filename,
     * replacing them with the plain text equivalents. Useful for debugging
     * when you want to display a shorter path.
     *
     * @param  $file string  path to debug
     * @return  string
     */
    public static function path($file)
    {

        $file = str_replace( '\\', '/', $file );

        if ( strpos( $file, PUBLIC_PATH ) !== false )
        {
            $file = 'PUBLIC_PATH' . DIRECTORY_SEPARATOR . substr( $file, strlen( PUBLIC_PATH ) );
        }
        elseif ( strpos( $file, FRAMEWORK_PATH ) !== false )
        {
            $file = 'FRAMEWORK_PATH' . DIRECTORY_SEPARATOR . substr( $file, strlen( FRAMEWORK_PATH ) );
        }
        elseif ( strpos( $file, LIBRARY_PATH ) !== false )
        {
            $file = 'LIBRARY_PATH' . DIRECTORY_SEPARATOR . substr( $file, strlen( LIBRARY_PATH ) );
        }
        elseif ( strpos( $file, MODULES_PATH ) !== false )
        {
            $file = 'MODULES_PATH' . DIRECTORY_SEPARATOR . substr( $file, strlen( MODULES_PATH ) );
        }
        elseif ( strpos( $file, ROOT_PATH ) !== false )
        {
            $file = 'ROOT_PATH' . DIRECTORY_SEPARATOR . substr( $file, strlen( ROOT_PATH ) );
        }

        return $file;
    }

    /**
     * Returns an HTML string, highlighting a specific line of a file, with some
     * number of lines padded above and below.
     *
     *     // Highlights the current line of the current file
     *     echo Debug::source(__FILE__, __LINE__);
     *
     * @param   string $filename file to open
     * @param   integer $line_number line number to highlight
     * @param   integer $padding number of padding lines
     * @return  string   source of file
     * @return  FALSE    file is unreadable
     */
    public static function source($filename, $line_number = 1, $padding = 10)
    {

        if ( !$filename || !is_readable( $filename ) )
        {
            // Continuing will cause errors
            return false;
        }

        // Set the reading range
        $range = array(
            'start' => ( $line_number < $padding ? $line_number : ( $line_number - $padding ) ),
            'end'   => $line_number + $padding
        );

        // Set the zero-padding amount for line numbers
        $format = '% ' . strlen( $range[ 'end' ] ) . 'd';

        $source = '';

        $line = 0;

        // Open the file and set the line position
        $file = fopen( $filename, 'r' );
        while ( ( $row = fgets( $file ) ) !== false )
        {
            // Increment the line number
            ++$line;
            if ( $line > $range[ 'end' ] )
            {
                break;
            }

            if ( $line >= $range[ 'start' ] )
            {
                // Make the row safe for output
                $row = htmlspecialchars( $row, ENT_NOQUOTES, Application::$charset );

                // Trim whitespace and sanitize the row
                $row = '<span class="number">' . sprintf( $format, $line ) . '</span> ' . $row;

                if ( $line == $line_number )
                {
                    // Apply highlighting to this row
                    $row = '<span class="line highlight">' . $row . '</span>';
                }
                else
                {
                    $row = '<span class="line">' . $row . '</span>';
                }

                // Add to the captured source
                $source .= $row;
            }
        }

        // Close the file
        fclose( $file );


        return '<pre class="source"><code>' . $source . '</code></pre>';
    }


    /**
     * Returns an HTML string, highlighting a specific line of a file, with some
     * number of lines padded above and below.
     *
     * @param   string $code
     * @param   integer $line_number line number to highlight
     * @param   integer $padding number of padding lines
     * @return  bool|string
     */
    public static function sourceXmlString($code, $line_number = 1, $padding = 10)
    {

        if ( !$code )
        {
            // Continuing will cause errors
            return false;
        }

        // Set the reading range
        $range = array(
            'start' => ( $line_number < $padding ? $line_number : ( $line_number - $padding ) ),
            'end'   => $line_number + $padding
        );

        // Set the zero-padding amount for line numbers
        $format = '% ' . strlen( $range[ 'end' ] ) . 'd';
        $source = '';

        // Open the file and set the line position
        $lines = explode( "\n", $code );
        $css = null;
        foreach ( $lines as $linenum => $row )
        {
            if ( $linenum+1 > $range[ 'end' ] )
            {
                break;
            }

            if ( $linenum+1 >= $range[ 'start' ] )
            {
                // Make the row safe for output
                $out = Library::syntaxHighlightCode( rtrim($row), 'xml');
                // $row = htmlspecialchars( Library::syntaxHighlightCode($row, 'xml'), ENT_NOQUOTES, Application::$charset );

                // Trim whitespace and sanitize the row
                $css = $out['css'];
                $row = '<span class="number">' . sprintf( $format, $linenum+1 ) . '</span> ' . $out['code'];

                if ( $linenum+1 == $line_number )
                {
                    // Apply highlighting to this row
                    $row = '<span class="line highlight">' . $row . '</span>';
                }
                else
                {
                    $row = '<span class="line">' . $row . '</span>';
                }

                // Add to the captured source
                $source .= $row;
            }
        }


        return array('code' => '<pre class="source"><code>' . $source . '</code></pre>', 'css' => $css);
    }

    /**
     * @param     $content
     * @param int $line_number
     * @param int $padding
     * @return string
     */
    public static function getContentSnipped($content, $line_number = 1, $padding = 5)
    {

        // Set the reading range
        $range = array(
            'start' => ( $line_number < $padding ? $line_number : $line_number - $padding ),
            'end'   => $line_number + $padding
        );

        $source = '';
        $line   = 0;

        $rows = explode( "\n", $content );
        foreach ( $rows as $row )
        {

            // Increment the line number
            if ( ++$line > $range[ 'end' ] )
            {
                break;
            }

            if ( $line >= $range[ 'start' ] )
            {
                $source .= $row . "\n";
            }
        }

        return $source;
    }


    /**
     * @param     $content
     * @param int $line_number
     * @param int $padding
     * @return string
     */
    public static function debugContent($content, $line_number = 1, $padding = 5)
    {

        // Set the reading range
        $range = array(
            'start' => ( $line_number < $padding ? $line_number : $line_number - $padding ),
            'end'   => $line_number + $padding
        );


        if ( 1 > ($line_number - $padding)) {
            $range['start'] = 1;
        }


        // Set the zero-padding amount for line numbers
        $format = '% ' . strlen( $range[ 'end' ] ) . 'd';

        $source = '';
        $line   = 0;

        $rows = explode( "\n", $content );
        foreach ( $rows as $row )
        {

            // Increment the line number
            if ( ++$line > $range[ 'end' ] )
            {
                break;
            }

            if ( $line >= $range[ 'start' ] )
            {
                // Make the row safe for output
                $row = htmlspecialchars( $row, ENT_NOQUOTES, Application::$charset );

                // Trim whitespace and sanitize the row
                $row = '<span class="number">' . sprintf( $format, $line ) . '</span> ' . $row;

                if ( $line === $line_number )
                {
                    // Apply highlighting to this row
                    $row = '<span class="line highlight">' . $row . '</span>';
                }
                else
                {
                    $row = '<span class="line">' . $row . '</span>';
                }

                // Add to the captured source
                $source .= $row;
            }
        }

        return '<pre class="source"><code>' . $source . '</code></pre>';
    }



    /**
     * @param     $content
     * @param int highlight
     * @return string
     */
    public static function debugSqlQuery($content, $highlight = 1)
    {
        $source = '';
        $line   = 0;
        $rows = explode( "\n", $content );
        $format = '% ' . strlen( count($rows) ) . 'd';
        foreach ( $rows as $row )
        {
            ++$line;

            // Make the row safe for output
            $row = htmlspecialchars( $row, ENT_NOQUOTES, Application::$charset );

            // Trim whitespace and sanitize the row
            $row = '<span class="number">' . sprintf( $format, $line ) . '</span> ' . $row;

            if ( $line === $highlight )
            {
                // Apply highlighting to this row
                $row = '<span class="line highlight">' . $row . '</span>';
            }
            else
            {
                $row = '<span class="line">' . $row . '</span>';
            }

            // Add to the captured source
            $source .= $row;

        }

        return '<pre class="source"><code>' . $source . '</code></pre>';
    }








    /**
     *
     * @param mixed $arg
     * @param bool $protect
     * @return string
     */
    private static function getArgument($arg, $protect = false)
    {


        switch ( strtolower( gettype( $arg ) ) )
        {
            case 'string' :
                $arg = ( strlen( $arg ) > 200 ? substr( $arg, 0, 200 ) . ' ... ' . substr( $arg, strlen( $arg ) - 40, strlen( $arg ) ) : $arg );


                if ( $protect === 'cfg' || $protect === 'crypt_key' || $protect === 'cli_key' || $protect == 'uniqidkey' || $protect == 'email' || ( $protect === '_databaseName' || $protect === '_hostname' || $protect === '_username' || $protect === '_password' || $protect === '_port' ) || preg_match( '/.*smtp_.*/i', $protect ) || ( $arg && strstr( $protect, 'smtp' ) )
                )
                {
                    $arg = '*** Protected ***';
                }
                else
                {
                    $arg = self::protectPath( $arg );
                }

                return ( "'" . str_replace( array(
                        "\n",
                        '\\'
                    ), array(
                        '',
                        '/'
                    ), $arg ) . "'" );

            case 'boolean' :
                return (bool)$arg;

            case 'object' :
                return 'object(' . get_class( $arg ) . ')';

            case 'array' :
                $ret      = 'array(';
                $separtor = '';

                foreach ( $arg as $key => $val )
                {
                    if ( $key === 'cfg' || $key === 'crypt_key' || $key === 'cli_key' || $key == 'uniqidkey' || $key == 'email' || $key === '_databaseName' || $key === '_hostname' || $key === '_username' || $key === '_password' || $key === '_port' || ( is_string( $val ) && preg_match( '/.*smtp_.*/i', $val ) ) || ( is_string( $val ) && strstr( $val, 'smtp' ) )
                    )
                    {
                        $val = '*** Protected ***';
                    }
                    else
                    {
                        $val = self::protectPath( $val );
                    }

                    $ret .= $separtor . self::getArgument( $key ) . ' => ' . self::getArgument( $val, $key );
                    $separtor = ", ";
                }
                $ret .= ')';

                return $ret;

            case 'resource' :
                return 'resource(' . get_resource_type( $arg ) . ')';

            default :
                return strtolower( gettype( $arg ) ) . ' ' . var_export( $arg, true );
        }
    }

    /**
     * Returns an array of HTML strings that represent each step in the backtrace.
     *
     * @param   array $trace
     * @return  string
     */
    public static function trace(array $trace = null)
    {

        if ( !is_array( $trace ) )
        {
            // Start a new trace
            $trace = debug_backtrace( false );
        }

        // Non-standard function calls
        $statements = array(
            'include',
            'include_once',
            'require',
            'require_once'
        );

        $output = array();
        foreach ( $trace as $step )
        {
            if ( !isset( $step[ 'function' ] ) )
            {
                // Invalid trace step
                continue;
            }

            if ( isset( $step[ 'class' ] ) && ( $step[ 'class' ] === 'Debug' || $step[ 'class' ] === 'BaseException' || $step[ 'class' ] === 'Exception' || $step[ 'class' ] === 'Error' )
            )
            {
                continue;
            }


            if ( isset( $step[ 'file' ] ) && isset( $step[ 'line' ] ) )
            {
                // Include the source of this step
                $source = Debug::source( $step[ 'file' ], $step[ 'line' ] );
            }

            if ( isset( $step[ 'file' ] ) )
            {
                $file = $step[ 'file' ];

                if ( isset( $step[ 'line' ] ) )
                {
                    $line = $step[ 'line' ];
                }
            }

            // function()
            $function = $step[ 'function' ];

            if ( in_array( $step[ 'function' ], $statements ) )
            {
                if ( empty( $step[ 'args' ] ) )
                {
                    // No arguments
                    $args = array();
                }
                else
                {
                    if ( is_object( $step[ 'args' ][ 0 ] ) )
                    {
                        $args = array();
                    }
                    else
                    {


                        // Sanitize the file path
                        if ( stripos( $step[ 'args' ][ 0 ], 'Object' ) === false )
                        {
                            $step[ 'args' ][ 0 ][ 'trace:Exception:private' ] = null;
                            $args                                             = array(
                                $step[ 'args' ][ 0 ]
                            );
                        }
                    }
                }
            }
            elseif ( isset( $step[ 'args' ] ) )
            {
                if ( !function_exists( $step[ 'function' ] ) || strpos( $step[ 'function' ], '{closure}' ) !== false || strpos( $step[ 'function' ], 'getTraceCode' ) !== false )
                {
                    // Introspection on closures or language constructs in a stack trace is impossible
                    $params = null;
                }
                else
                {
                    if ( isset( $step[ 'class' ] ) )
                    {
                        if ( method_exists( $step[ 'class' ], $step[ 'function' ] ) )
                        {
                            $reflection = new ReflectionMethod( $step[ 'class' ], $step[ 'function' ] );
                        }
                        else
                        {
                            $reflection = new ReflectionMethod( $step[ 'class' ], '__call' );
                        }
                    }
                    else
                    {
                        $reflection = new ReflectionFunction( $step[ 'function' ] );
                    }

                    // Get the function parameters
                    $params = $reflection->getParameters();
                }

                $args = array();

                foreach ( $step[ 'args' ] as $i => $arg )
                {
                    #$args[$i] = self::getArgument($arg);


                    if ( isset( $params[ $i ] ) )
                    {
                        // Assign the argument by the parameter name
                        if ( $params[ $i ]->name !== 'trace:Exception:private' )
                        {
                            #$args[$params[$i]->name] = $arg;
                            $args[ $params[ $i ]->name ] = self::getArgument( $arg );
                        }
                    }
                    else
                    {
                        // Assign the argument by number
                        $args[ $i ] = $arg;

                        #$args[$i] = self::getArgument($arg);
                    }
                }
            }

            if ( isset( $step[ 'class' ] ) )
            {
                // Class->method() or Class::method()
                $function = $step[ 'class' ] . $step[ 'type' ] . $step[ 'function' ];
            }

            #$args = null;

            $output[ ] = array(
                'function' => $function,
                'args'     => isset( $args ) ? $args : null,
                'file'     => isset( $file ) ? $file : null,
                'line'     => isset( $line ) ? $line : null,
                'source'   => isset( $source ) ? $source : null,
            );

            unset( $function, $args, $file, $line, $source );
        }

        return $output;
    }

}

?>