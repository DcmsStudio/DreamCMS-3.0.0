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
 * @package     Helpers
 * @version     3.0.0 Beta
 * @category    Helper Tools
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        php_functions.php
 */
defined( 'IN' ) or die( 'Direct Access to this location is not allowed.' );

// Konstante in >= PHP 5.0.2
if ( !defined( "PHP_EOL" ) )
{
    /**
     *
     */
    define( "PHP_EOL", ( (DIRECTORY_SEPARATOR == "\\") ? "\015\012" : (strncmp( PHP_OS, "D", 1 ) ? "\012" : "\015") ) ); #"
}
#-- more new constants for 5.0
if ( !defined( "E_STRICT" ) )
{
    /**
     *
     */
    define( "E_STRICT", 2048 );  // _STRICT is a special case of _NOTICE (_DEBUG)
    # PHP_CONFIG_FILE_SCAN_DIR
    # COUNT_NORMAL   seems unused
    # COUNT_RECURSIVE
}




#-- ci string search functions
if ( !function_exists( "stripos" ) )
{
    #-- find position of first occourence of a case-insensitive string

    /**
     * @param      $haystack
     * @param      $needle
     * @param null $offset
     * @return int
     */
    function stripos( $haystack, $needle, $offset = NULL )
    {
        return strpos( strtolower( $haystack ), strtolower( $needle ), $offset );
    }
}


#-- more complicated, because $offset param not supported in basic form
if ( !function_exists( "strripos" ) )
{
    #-- ... from end of string

    /**
     * @param      $haystack
     * @param      $needle
     * @param null $offset
     * @return int
     */
    function strripos( $haystack, $needle, $offset = NULL )
    {
        if ( isset( $offset ) )
        {
            $haystack = strtolower( $haystack );
            $needle   = strtolower( $needle );
            if ( $offset < 0 )
            {
                $offset = strlen( $haystack ) + $offset;
            }
            do
            {
                $l   = $new;
                $new = strpos( $haystack, $needle, $l );
            }
            while ( ($new !== false) && ($new < $offset) );
            return($l);
        }
        else
        {
            return strrpos( strtolower( $haystack ), strtolower( $needle ) );
        }
    }
}

#-- case-insensitive version of str_replace
if ( !function_exists( "str_ireplace" ) )
{

    /**
     * @param      $search
     * @param      $replace
     * @param      $subject
     * @param null $count
     * @return mixed
     */
    function str_ireplace( $search, $replace, $subject, $count = NULL )
    {
        if ( is_array( $search ) )
        {
            $replace = array_values( $replace );
            foreach ( array_values( $search ) as $i => $srch )
            {
                $subject = str_ireplace( $srch, $replace[ $i ], $subject );
            }
        }
        else
        {
            $search  = "\007" . preg_quote( $search ) . "\007i";
            $replace = strtr( $replace, array( '$' => '\\$', "\\" => "\\\\" ) );
            $subject = preg_replace( $search, $replace, $subject );
        }
        return($subject);
    }
}



#-- write formatted string to stream/file
if ( !function_exists( "fprintf" ) )
{

    /**
     * @return int
     */
    function fprintf()
    {
        $args   = func_get_args();
        $stream = array_shift( $args );
        return fwrite( $stream, call_user_func_array( "sprintf", $args ) );
    }
}
if ( !function_exists( "vfprintf" ) )
{

    /**
     * @param      $stream
     * @param      $format
     * @param null $args
     * @return int
     */
    function vfprintf( $stream, $format, $args = NULL )
    {
        return fwrite( $stream, vsprintf( $format, $args ) );
    }
}


#-- splits a string in even sized chunks, returns an array
if ( !function_exists( "str_split" ) )
{

    /**
     * @param     $str
     * @param int $chunk
     * @return array
     */
    function str_split( $str, $chunk = 1 )
    {
        $r = array();
        if ( $chunk < 1 )
        {
            $r[] = $str;
        }
        else
        {
            $len = strlen( $str );
            for ( $n = 0; $n < $len; $n+=$chunk )
            {
                $r[] = substr( $str, $n, $chunk );
            }
        }
        return($r);
    }
}

#-- search first occourence of any of the given chars, returns rest of haystack
#   (char_list must be a string for compatibility with the real PHP func)
if ( !function_exists( "strpbrk" ) )
{

    /**
     * @param $haystack
     * @param $char_list
     * @return string
     */
    function strpbrk( $haystack, $char_list )
    {
        $cn  = strlen( $char_list );
        $min = strlen( $haystack );
        for ( $n = 0; $n < $cn; $n++ )
        {
            $l = strpos( $haystack, $char_list{$n} );
            if ( ($l !== false) && ($l < $min) )
            {
                $min = $l;
            }
        }
        return($min ? substr( $haystack, $min ) : $haystack);
    }
}

#-- no need to implement this (there aren't interfaces in PHP4 anyhow)
if ( !function_exists( "get_declared_interfaces" ) )
{

    /**
     * @return array
     */
    function get_declared_interfaces()
    {
        trigger_error( "get_declared_interfaces(): Current script won't run reliably with PHP4.", E_USER_WARNING );
        return( ( array ) NULL );
    }
}


#-- creates an array from lists of $keys and $values (should have same number of entries)
if ( !function_exists( "array_combine" ) )
{

    /**
     * @param $keys
     * @param $values
     * @return array
     */
    function array_combine( $keys, $values )
    {
        $keys   = array_values( $keys );
        $values = array_values( $values );
        $r      = array();
        foreach ( $values as $i => $val )
        {
            if ( $key = $keys[ $i ] )
            {
                $r[ $key ] = $val;
            }
            else
            {
                $r[] = $val;
            }
        }
        return($r);
    }
}


#-- apply userfunction to each array element (descending recursively)
#   use it like:  array_walk_recursive($_POST, "stripslashes");
if ( !function_exists( "array_walk_recursive" ) )
{

    /**
     * @param      $input
     * @param      $callback
     * @param null $userdata
     * @return null
     */
    function array_walk_recursive( $input, $callback, $userdata = NULL )
    {

        #-- walk
        foreach ( $input as $i => $value )
        {
            #-- recurse
            if ( is_array( $value ) )
            {
                array_walk_recursive( $input[ $i ], $callback, @$userdata );
            }
            #-- scalar
            else
            {
                call_user_func_array( $callback, array( $input[ $i ], $i, @$userdata ) );
            }
        }
        return NULL; //?
    }
}


#-- complicated wrapper around substr() and and strncmp()
if ( !function_exists( "substr_compare" ) )
{

    /**
     * @param     $haystack
     * @param     $needle
     * @param int $offset
     * @param int $len
     * @param int $ci
     * @return bool|int
     */
    function substr_compare( $haystack, $needle, $offset = 0, $len = 0, $ci = 0 )
    {

        #-- check params
        if ( $len <= 0 )
        {   // not well documented
            $len = strlen( $needle );
            if ( !$len )
            {
                return(0);
            }
        }
        #-- length exception
        if ( $len + $offset >= strlen( $haystack ) )
        {
            trigger_error( "substr_compare: given length exceeds main_str", E_USER_WARNING );
            return(false);
        }
        #-- cut
        if ( $offset )
        {
            $haystack = substr( $haystack, $offset, $len );
        }
        #-- case-insensitivity
        if ( $ci )
        {
            $haystack = strtolower( $haystack );
            $needle   = strtolower( $needle );
        }

        #-- do
        return(strncmp( $haystack, $needle, $len ));
    }
}






#-- gets you list of class names the given objects class was derived from, slow
if ( !function_exists( "class_parents" ) )
{

    /**
     * @param $obj
     * @return array
     */
    function class_parents( $obj )
    {
        $all = get_declared_classes();
        $r   = array();
        foreach ( $all as $potential_parent )
        {
            if ( is_subclass_of( $obj, $potential_parent ) )
            {
                $r[ $potential_parent ] = $potential_parent;
            }
        }
        return($r);
    }
}

#-- simplified file read-at-once function
if ( !function_exists( "file_get_contents" ) )
{

    /**
     * @param     $filename
     * @param int $use_include_path
     * @return string
     */
    function file_get_contents( $filename, $use_include_path = 1 )
    {
        if ( $f = fopen( $filename, "rb", $use_include_path ) )
        {
            $content = fread( $f, 1 << 21 );  # max 2MB
            fclose( $f );
            return($content);
        }
    }
}

#-- redundant alias for isset()
if ( !function_exists( "array_key_exists" ) )
{

    /**
     * @param $key
     * @param $search
     * @return bool
     */
    function array_key_exists( $key, $search )
    {
        return isset( $search[ $key ] );
    }
}


#-- who could need that?
if ( !function_exists( "array_intersect_assoc" ) )
{

    /**
     * @return array
     */
    function array_intersect_assoc( /* array, array, array... */ )
    {
        $whatsleftover = array();
        $in            = func_get_args();
        $cmax          = count( $in );
        foreach ( $in[ 0 ] as $i => $v )
        {
            for ( $c = 1; $c < $cmax; $c++ )
            {
                if ( isset($in[ $c ][ $i ]) && $in[ $c ][ $i ] !== $v )
                {
                    continue 2;
                }
            }
            $whatsleftover[ $i ] = $v;
        }
        return $whatsleftover;
    }
}


#-- the opposite of the above
if ( !function_exists( "array_diff_assoc" ) )
{

    /**
     * @return array
     */
    function array_diff_assoc( /* array, array, array... */ )
    {
        $diff = array();
        $in   = func_get_args();
        foreach ( $in[ 0 ] as $i => $v )
        {
            for ( $c = 1; $c < count( $in ); $c++ )
            {
                if ( isset( $in[ $c ][ $i ] ) && ($in[ $c ][ $i ] == $v) )
                {
                    continue 2;
                }
            }
            $diff[ $i ] = $v;
        }
        return $diff;
    }
}


#-- opposite of htmlentities
if ( !function_exists( "html_entity_decode" ) )
{

    /**
     * @param        $string
     * @param int    $quote_style
     * @param string $charset
     * @return string
     */
    function html_entity_decode( $string, $quote_style = ENT_COMPAT, $charset = "ISO-8859-1" )
    {
        // we fall short on anything other than Latin-1
        $y = array_flip( get_html_translation_table( HTML_ENTITIES, $quote_style ) );
        return strtr( $string, $y );
    }
}


#-- extracts single words from a string
if ( !function_exists( "str_word_count" ) )
{

    /**
     * @param     $string
     * @param int $format
     * @return array|int
     */
    function str_word_count( $string, $format = 0 )
    {
        preg_match_all( '/([\w](?:[-\'\w]?[\w]+)*)/', $string, $uu );
        if ( !$format )
        {
            return(count( $uu[ 1 ] ));
        }
        elseif ( $format == 1 )
        {
            return($uu[ 1 ]);
        }
        else
        {
            $r = array();
            $l = 0;
            foreach ( $uu[ 1 ] as $word )
            {
                $l       = strpos( $string, $word, $l );
                $r[ $l ] = $word;
                $l += strlen( $word );
            }
            return($r);
        }
    }
}


#-- creates a permutation of the given strings characters
if ( !function_exists( "str_shuffle" ) )
{

    /**
     * @param $str
     * @return string
     */
    function str_shuffle( $str )
    {
        $r = "";
        while ( strlen( $str ) )
        {
            $n = strlen( $str ) - 1;
            if ( $n )
            {
                $n = rand( 0, $n );
            }
            $r .= $str[ $n ];
            $str = substr( $str, 0, $n ) . substr( $str, $n + 1 );
        }
        return($r);
    }
}


#-- simple shorthands
if ( !function_exists( "get_include_path" ) )
{

    /**
     * @return string
     */
    function get_include_path()
    {
        return(get_cfg_var( "include_path" ));
    }

    /**
     * @param $new
     * @return string
     */
    function set_include_path( $new )
    {
        return ini_set( "include_path", $new );
    }

    function restore_include_path()
    {
        ini_restore( "include_path" );
    }
}


#-- constants for 4.3
if ( !defined( "PATH_SEPARATOR" ) )
{
    /**
     *
     */
    define( "PATH_SEPARATOR", ((DIRECTORY_SEPARATOR == '\\') ? ';' : ':' ) );
    /**
     *
     */
    define( "PHP_SHLIB_SUFFIX", ((DIRECTORY_SEPARATOR == '\\') ? 'dll' : 'so' ) );
}
if ( !defined( "PHP_SAPI" ) )
{
    /**
     *
     */
    define( "PHP_SAPI", php_sapi_name() );
}
if ( !defined( "PHP_PREFIX" ) && isset( $_ENV[ "_" ] ) )
{
    /**
     *
     */
    define( "PHP_PREFIX", substr( $_ENV[ "_" ], strpos( $_ENV[ "_" ], "/bin/" ) ) );
}



#-- well, if you need it
if ( !function_exists( "array_change_key_case" ) )
{
    /**
     *
     */
    define( "CASE_LOWER", 0 );
    /**
     *
     */
    define( "CASE_UPPER", 1 );

    /**
     * @param     $array
     * @param int $case
     * @return mixed
     */
    function array_change_key_case( $array, $case = CASE_LOWER )
    {
        foreach ( $array as $i => $v )
        {
            unset( $array[ $i ] );
            if ( is_string( $i ) )
            {
                $i = ($case == CASE_LOWER) ? strtolower( $i ) : strtoupper( $i );
            }
            $array[ $i ] = $v;
        }
        return($array);
    }
}


#-- hey, why not
if ( !function_exists( "array_fill" ) )
{

    /**
     * @param $start_index
     * @param $num
     * @param $value
     * @return array
     */
    function array_fill( $start_index, $num, $value )
    {
        $r = array();
        for ( $i = $start_index, $end = $num + $i; $i < $end; $i++ )
        {
            $r[ $i ] = $value;
        }
        return($r);
    }
}


#-- split an array into evenly sized parts
if ( !function_exists( "array_chunk" ) )
{

    /**
     * @param      $input
     * @param      $size
     * @param bool $preserve_keys
     * @return array
     */
    function array_chunk( $input, $size, $preserve_keys = false )
    {
        $r = array();
        $n = -1;
        foreach ( $input as $i => $v )
        {
            if ( ($n < 0) || (count( $r[ $n ] ) == $size) )
            {
                $n++;
                $r[ $n ] = array();
            }
            if ( $preserve_keys )
            {
                $r[ $n ][ $i ] = $v;
            }
            else
            {
                $r[ $n ][] = $v;
            }
        }
        return($r);
    }
}


#-- convenience wrapper
if ( !function_exists( "md5_file" ) )
{

    /**
     * @param      $filename
     * @param bool $raw_output
     * @return string
     */
    function md5_file( $filename, $raw_output = false )
    {
        if ( $f = fopen( $filename, "rb" ) )
        {
            $data = fread( $f, 1 << 22 );  // can be too large for mem
            fclose( $f );
            $r    = md5( $data );
            $data = NULL;
            if ( $raw_output )
            {
                $r = pack( "H*", $r );
            }
            return $r;
        }
    }
}

#-- floating point modulo
if ( !function_exists( "fmod" ) )
{

    /**
     * @param $x
     * @param $y
     * @return mixed
     */
    function fmod( $x, $y )
    {
        $r = $x / $y;
        $r -= ( int ) $r;
        $r *= $y;
        return($r);
    }
}


#-- makes float variable from string
if ( !function_exists( "floatval" ) )
{

    /**
     * @param $str
     * @return float
     */
    function floatval( $str )
    {
        $str = ltrim( ( string ) $str );
        return ( float ) $str;
    }
}


#-- floats
if ( !function_exists( "is_infinite" ) )
{
    /**
     *
     */
    define( "NAN", "NAN" );
    /**
     *
     */
    define( "INF", "INF" );

    /**
     * @param $f
     * @return bool
     */
    function is_infinite( $f )
    {
        $s = ( string ) $f;
        return( ($s == "INF") || ($s == "-INF") );
    }

    /**
     * @param $f
     * @return bool
     */
    function is_nan( $f )
    {
        $s = ( string ) $f;
        return( $s == "NAN" );
    }

    /**
     * @param $f
     * @return bool
     */
    function is_finite( $f )
    {
        $s = ( string ) $f;
        return(!strpos( $s, "N" ) );
    }
}


#-- throws value-instantiation PHP-code for given variable
#   (a bit different from the standard, was intentional for its orig use)
if ( !function_exists( "var_export" ) )
{

    /**
     * @param        $var
     * @param bool   $return
     * @param string $indent
     * @param string $output
     * @return string
     */
    function var_export( $var, $return = false, $indent = "", $output = "" )
    {
        if ( is_object( $var ) )
        {
            $output = "class " . get_class( $var ) . " {\n";
            foreach ( (( array ) $var ) as $id => $var )
            {
                $output .= "  var \$$id = " . var_export( $var, true ) . ";\n";
            }
            $output .= "}";
        }
        elseif ( is_array( $var ) )
        {
            foreach ( $var as $id => $next )
            {
                if ( $output )
                    $output .= ",\n";
                else
                    $output = "array(\n";
                $output .= $indent . '  '
                        . (is_numeric( $id ) ? $id : '"' . addslashes( $id ) . '"')
                        . ' => ' . var_export( $next, true, "$indent  " );
            }
            if ( empty( $output ) )
                $output = "array(";
            $output .= "\n{$indent})";
            #if ($indent == "") $output .= ";";
        }
        elseif ( is_numeric( $var ) )
        {
            $output = "$var";
        }
        elseif ( is_bool( $var ) )
        {
            $output = $var ? "true" : "false";
        }
        else
        {
            $output = "'" . preg_replace( "/([\\\\\'])/", '\\\\$1', $var ) . "'";
        }
        #-- done
        if ( $return )
        {
            return($output);
        }
        else
        {
            print($output );
        }
    }
}


#-- strcmp() variant that respects locale setting,
#   existed since PHP 4.0.5, but under Win32 first since 4.3.2
if ( !function_exists( "strcoll" ) )
{

    /**
     * @param $str1
     * @param $str2
     * @return int
     */
    function strcoll( $str1, $str2 )
    {
        return strcmp( $str1, $str2 );
    }
}



#-- a silly alias (an earlier fallen attempt to unify PHP function names)
if ( !function_exists( "diskfreespace" ) )
{

    function diskfreespace()
    {
        return disk_free_sapce();
    }

    function disktotalspace()
    {
        return disk_total_sapce();
    }
}

#-- me has no idea what this function means
if ( !function_exists( "hypot" ) )
{

    /**
     * @param $num1
     * @param $num2
     * @return float
     */
    function hypot( $num1, $num2 )
    {
        return sqrt( $num1 * $num1 + $num2 * $num2 );  // as per PHP manual ;)
    }
}

#-- more accurate logarithm func, but we cannot simulate it
#   (too much work, too slow in PHP)
if ( !function_exists( "log1p" ) )
{

    /**
     * @param $x
     * @return float
     */
    function log1p( $x )
    {
        return( log( 1 + $x ) );
    }
    #-- same story for:

    /**
     * @param $x
     * @return float
     */
    function expm1( $x )
    {
        return( exp( $x ) - 1 );
    }
}

#-- as per PHP manual
if ( !function_exists( "sinh" ) )
{

    /**
     * @param $f
     * @return float
     */
    function sinh( $f )
    {
        return( (exp( $f ) - exp( -$f )) / 2 );
    }

    /**
     * @param $f
     * @return float
     */
    function cosh( $f )
    {
        return( (exp( $f ) + exp( -$f )) / 2 );
    }

    /**
     * @param $f
     * @return float
     */
    function tanh( $f )
    {
        return( sinh( $f ) / cosh( $f ) );   // ok, that one makes sense again :)
    }
}

#-- these look a bit more complicated
if ( !function_exists( "asinh" ) )
{

    /**
     * @param $x
     * @return float
     */
    function asinh( $x )
    {
        return( log( $x + sqrt( $x * $x + 1 ) ) );
    }

    /**
     * @param $x
     * @return float
     */
    function acosh( $x )
    {
        return( log( $x + sqrt( $x * $x - 1 ) ) );
    }

    /**
     * @param $x
     * @return float
     */
    function atanh( $x )
    {
        return( log1p( 2 * $x / (1 - $x) ) / 2 );
    }
}








if ( !function_exists( 'file_get_contents' ) )
{
    # Exists in PHP 4.3.0+

    /**
     * @param $filename
     * @return string
     */
    function file_get_contents( $filename )
    {
        return implode( '', file( $filename ) );
    }
}



if ( !function_exists( 'is_a' ) )
{
    # Exists in PHP 4.2.0+

    /**
     * @param $object
     * @param $class_name
     * @return bool
     */
    function is_a( $object, $class_name )
    {
        return
                (strcasecmp( get_class( $object ), $class_name ) == 0) ||
                is_subclass_of( $object, $class_name );
    }
}




if ( !function_exists( 'mb_substr' ) )
{

    /**
     * @param $str
     * @param $start
     * @return string
     */
    function mb_substr( $str, $start )
    {
        preg_match_all( "/./us", $str, $ar );

        if ( func_num_args() >= 3 )
        {
            $end = func_get_arg( 2 );
            return join( "", array_slice( $ar[ 0 ], $start, $end ) );
        }
        else
        {
            return join( "", array_slice( $ar[ 0 ], $start ) );
        }
    }
}


# html_entity_decode exists in PHP 4.3.0+ but is FATALLY BROKEN even then,
# with no UTF-8 support.
if ( !function_exists( 'do_html_entity_decode' ) )
{

    /**
     * @param        $string
     * @param int    $quote_style
     * @param string $charset
     * @return string
     */
    function do_html_entity_decode( $string, $quote_style = ENT_COMPAT, $charset = 'ISO-8859-1' )
    {
        static $trans;
        if ( !isset( $trans ) )
        {
            $trans = array_flip( get_html_translation_table( HTML_ENTITIES, $quote_style ) );
            # Assumes $charset will always be the same through a run, and only understands
            # utf-8 or default. Note - mixing latin1 named entities and unicode numbered
            # ones will result in a bad link.
            if ( strcasecmp( 'utf-8', $charset ) == 0 )
            {
                $trans = array_map( 'utf8_encode', $trans );
            }
        }
        return strtr( $string, $trans );
    }
}


























#-- fake zlib
if ( !function_exists( "gzopen" ) )
{

    /**
     * @param $fp
     * @param $mode
     * @return resource
     */
    function gzopen( $fp, $mode )
    {
        $mode = preg_replace( '/[^carwb+]/', '', $mode );
        return(fopen( $fp, $mode ));
    }

    /**
     * @param $fp
     * @param $len
     * @return string
     */
    function gzread( $fp, $len )
    {
        return(fread( $fp, $len ));
    }

    /**
     * @param $fp
     * @param $string
     * @return int
     */
    function gzwrite( $fp, $string )
    {
        return(fwrite( $fp, $string ));
    }

    /**
     * @param $fp
     * @param $string
     * @return FALSE|int
     */
    function gzputs( $fp, $string )
    {
        return(fputs( $fp, $string ));
    }

    /**
     * @param $fp
     * @return bool
     */
    function gzclose( $fp )
    {
        return(fclose( $fp ));
    }

    /**
     * @param $fp
     * @return bool
     */
    function gzeof( $fp )
    {
        return(feof( $fp ));
    }

    /**
     * @param $fp
     * @param $offs
     * @return int
     */
    function gzseek( $fp, $offs )
    {
        return(fseek( $fp, $offs, SEEK_SET ));
    }

    /**
     * @param $fp
     * @return mixed
     */
    function gzrewind( $fp )
    {
        return(frewind( $fp ));
    }

    /**
     * @param $fp
     * @return int
     */
    function gztell( $fp )
    {
        return(ftell( $fp ));
    }

    /**
     * @param $fp
     */
    function gzpassthru( $fp )
    {
        while ( !gzeof( $fp ) )
        {
            print(gzred( $fp, 1 << 20 ) );
        }
        gzclose( $fp );
    }

    /**
     * @param $fn
     */
    function readgzfile( $fn )
    {
        if ( $fp = gzopen( $fn, "rb" ) )
        {
            gzpassthru( $fp );
        }
    }

    /**
     * @param $fn
     * @return array
     */
    function gzfile( $fn )
    {
        return(file( $fn ));
    }

    /**
     * @param $fp
     * @return string
     */
    function gzgetc( $fp )
    {
        return(fgetc( $fp ));
    }

    /**
     * @param $fp
     * @param $len
     * @return string
     */
    function gzgets( $fp, $len )
    {
        return(fgets( $fp, $len ));
    }

    /**
     * @param        $fp
     * @param        $len
     * @param string $allowedtags
     * @return string
     */
    function gzgetss( $fp, $len, $allowedtags = "" )
    {
        return(fgetss( $fp, $len, $allowedtags ));
    }
}


#-- fake compression methods
if ( !function_exists( "gzdeflate" ) )
{

    // only returns uncompressed deflate streams
    /**
     * @param     $data
     * @param int $level
     * @return string
     */
    function gzdeflate( $data, $level = 0 )
    {
        $gz  = "";
        $end = strlen( $data );
        $p   = 0;
        do
        {
            $c = $end - $pos;
            if ( $c >= 65536 )
            {
                $c   = 0xFFFF;
                $end = 0x00;
            }
            else
            {
                $end = 0x01;
            }
            $gz .= pack( "Cvv", ($end << 7) + (00 << 5), // LAST=0/1, BTYPE=00
                         $c, // LEN
                         $c ^ 0xFFFF               // NLEN
            );
            $gz .= substr( $data, $p, $c );
            $p += $c;
        }
        while ( $p < $end );
        return($gz);
    }

    // only can strip deflate headers, cannot decompress
    /**
     * @param      $data
     * @param null $length
     * @return string
     */
    function gzinflate( $data, $length = NULL )
    {
        $end = strlen( $data );

        if ( isset( $length ) && (($max * 0.99) > $length) )
        {
            trigger_error( "gzinflate(): gave up, decompressed string is likely longer than requested", E_USER_ERROR );
            return;
        }
        $out = "";
        $p   = 0;
        do
        {
            $head = ord( $data[ $p ] );
            $last = ($head >> 7);
            if ( ($head & 0x60) != 00 )
            {
                trigger_error( "gzinflate(): cannot decode compressed stream", E_USER_ERROR );
                return;
            }
            $head = unpack( "v1LEN/v1NLEN", substr( $data, $p + 1, 4 ) );
            $c    = $head[ "LEN" ];
            if ( ($c ^ 0xFFFF) != $head[ "NLEN" ] )
            {
                trigger_error( "gzinflate(): data error in stream", E_USER_ERROR );
                return;
            }
            $p += 5;
            $out .= substr( $data, $p, $c );
            $p += $c;
        }
        while ( ($p < $end) && !$last );
        return($out);
    }

//    function gzcompress() {
//    }
//    function gzuncompress() {
//    }
    // without real compression support again
    /**
     * @param     $data
     * @param int $level
     * @return string
     */
    function gzencode( $data, $level = 0 )
    {
        $isize = strlen( $data );
        $crc32 = crc32( $data );
        $gz    = "";
        {
            $gz .= pack( "nCCVCC", $_ID    = 0x1f8b, $_CM    = 0x08, // deflate fmt
                         $_FLG   = 0x00, // nothing extra
                         $_MTIME = TIME_STAMP, $_XFL   = 0x00, // no bonus flags
                         $_OS    = 255    // "unknown"
            );
            $gz .= gzdeflate( $data );
            $gz .= pack( "VV", $crc32, $isize );
        }
        return($gz);
    }
}






#-- fake dba_* using dbm_* functions
if ( !function_exists( "dba_open" ) && function_exists( "dbm_open" ) )
{

    /**
     * @param     $path
     * @param     $mode
     * @param     $handler
     * @param int $a1
     * @return bool
     */
    function dba_open( $path, $mode, $handler, $a1 = 0 )
    {
        if ( $handler == "dbm" )
        {
            return(dbmopen( $path, $mode ));
        }
        else
            return(false);
    }

    /**
     * @param     $a
     * @param     $b
     * @param     $c
     * @param int $d
     * @return bool|resource
     */
    function dba_popen( $a, $b, $c, $d = 0 )
    {
        return(dba_open( $a, $b, $c ));
    }

    /**
     * @param $key
     * @param $handle
     * @return mixed
     */
    function dba_exists( $key, $handle )
    {
        return(dbmexists( $handle, $key ));
    }

    /**
     * @param $key
     * @param $handle
     * @return mixed
     */
    function dba_fetch( $key, $handle )
    {
        return(dbmfetch( $handle, $key ));
    }

    /**
     * @param $key
     * @param $string
     * @param $handle
     * @return mixed
     */
    function dba_insert( $key, $string, $handle )
    {
        return(dbminsert( $handle, $key, $string ));
    }

    /**
     * @param $key
     * @param $string
     * @param $handle
     * @return mixed
     */
    function dba_replace( $key, $string, $handle )
    {
        return(dbmreplace( $handle, $key, $string ));
    }

    /**
     * @param $key
     * @param $handle
     * @return mixed
     */
    function dba_delete( $key, $handle )
    {
        return(dbmdelete( $handle, $key ));
    }

    /**
     * @param $handle
     * @return mixed
     */
    function dba_firstkey( $handle )
    {
        return($GLOBALS[ "dbm_lastkey" ] = dbmfirstkey( $handle ));
    }

    /**
     * @param $handle
     * @return mixed
     */
    function dba_nextkey( $handle )
    {
        return(dbmnextkey( $handle, $GLOBALS[ "dbm_lastkey" ] ));
    }

    /**
     * @param $handle
     * @return mixed
     */
    function dba_close( $handle )
    {
        return(dbmclose( $handle ));
    }

    /**
     * @return array
     */
    function dba_handlers()
    {
        return(array( "dbm" ));
    }
}


#-- regex variants
if ( !function_exists( "ctype_alnum" ) )
{

    /**
     * @param $text
     * @return int
     */
    function ctype_alnum( $text )
    {
        return preg_match( "/^[A-Za-z\d\300-\377]+$/", $text );
    }

    /**
     * @param $text
     * @return int
     */
    function ctype_alpha( $text )
    {
        return preg_match( "/^[a-zA-Z\300-\377]+$/", $text );
    }

    /**
     * @param $text
     * @return int
     */
    function ctype_digit( $text )
    {
        return preg_match( "/^\d+$/", $text );
    }

    /**
     * @param $text
     * @return int
     */
    function ctype_xdigit( $text )
    {
        return preg_match( "/^[a-fA-F0-9]+$/", $text );
    }

    /**
     * @param $text
     * @return int
     */
    function ctype_cntrl( $text )
    {
        return preg_match( "/^[\000-\037]+$/", $text );
    }

    /**
     * @param $text
     * @return int
     */
    function ctype_space( $text )
    {
        return preg_match( "/^\s+$/", $text );
    }

    /**
     * @param $text
     * @return int
     */
    function ctype_upper( $text )
    {
        return preg_match( "/^[A-Z\300-\337]+$/", $text );
    }

    /**
     * @param $text
     * @return int
     */
    function ctype_lower( $text )
    {
        return preg_match( "/^[a-z\340-\377]+$/", $text );
    }

    /**
     * @param $text
     * @return int
     */
    function ctype_graph( $text )
    {
        return preg_match( "/^[\041-\176\241-\377]+$/", $text );
    }

    /**
     * @param $text
     * @return int
     */
    function ctype_punct( $text )
    {
        return preg_match( "/^[^0-9A-Za-z\000-\040\177-\240\300-\377]+$/", $text );
    }

    /**
     * @param $text
     * @return bool
     */
    function ctype_print( $text )
    {
        return ctype_punct( $text ) && ctype_graph( $text );
    }
}



#-- simple char-by-char comparisions
if ( !function_exists( "ctype_alnum" ) )
{


    #-- true if string is made of letters and digits only

    /**
     * @param $text
     * @return bool
     */
    function ctype_alnum( $text )
    {
        $r = true;
        for ( $i = 0; $i < strlen( $text ); $i++ )
        {
            $c = ord( $text{$i} );
            /** @noinspection PhpSillyAssignmentInspection */
            $r = $r and (
                    ($c >= 65) && ($c <= 90)    // A-Z
                    or ($c >= 97) && ($c <= 122)   // a-z
                    or ($c >= 48) && ($c <= 59)    // 0-9
                    or ($c >= 192)          // Latin-1 letters
                    );
        }
        return($r);
    }
    #-- only letters in given string

    /**
     * @param $text
     * @return bool
     */
    function ctype_alpha( $text )
    {
        $r = true;
        for ( $i = 0; $i < strlen( $text ); $i++ )
        {
            $c = ord( $text{$i} );
            /** @noinspection PhpSillyAssignmentInspection */
            $r = $r and (
                    ($c >= 65) && ($c <= 90)    // A-Z
                    or ($c >= 97) && ($c <= 122)   // a-z
                    or ($c >= 192)          // Latin-1 letters
                    );
        }
        return($r);
    }
    #-- only numbers in string

    /**
     * @param $text
     * @return bool
     */
    function ctype_digit( $text )
    {
        $r = true;
        for ( $i = 0; $i < strlen( $text ); $i++ )
        {
            $c = ord( $text{$i} );
            /** @noinspection PhpSillyAssignmentInspection */
            $r = $r and ($c >= 48) && ($c <= 59);   // 0-9
        }
        return($r);
    }
    #-- hexadecimal numbers only

    /**
     * @param $text
     * @return bool
     */
    function ctype_xdigit( $text )
    {
        $r = true;
        for ( $i = 0; $i < strlen( $text ); $i++ )
        {
            $c = ord( $text{$i} );
            /** @noinspection PhpSillyAssignmentInspection */
            $r = $r and (
                    ($c >= 48) && ($c <= 59)    // 0-9
                    or ($c >= 65) && ($c <= 70)    // A-F
                    or ($c >= 97) && ($c <= 102)   // a-f
                    );
        }
        return($r);
    }
    #-- hexadecimal numbers only

    /**
     * @param $text
     * @return bool
     */
    function ctype_cntrl( $text )
    {
        $r = true;
        for ( $i = 0; $i < strlen( $text ); $i++ )
        {
            $c = ord( $text{$i} );
            /** @noinspection PhpSillyAssignmentInspection */
            $r = $r and ($c < 32);
        }
        return($r);
    }
    #-- hexadecimal numbers only

    /**
     * @param $text
     * @return bool
     */
    function ctype_space( $text )
    {
        $r = true;
        for ( $i = 0; $i < strlen( $text ); $i++ )
        {
            $c = $text{$i};
            /** @noinspection PhpSillyAssignmentInspection */
            $r = $r and (
                    ($c == " ") or ($c == "\240") or ($c == "\n") or ($c == "\r") or ($c == "\t") or ($c == "\f")
                    );
        }
        return($r);
    }
    #-- all-uppercase

    /**
     * @param $text
     * @return bool
     */
    function ctype_upper( $text )
    {
        $r = true;
        for ( $i = 0; $i < strlen( $text ); $i++ )
        {
            $c = ord( $text{$i} );
            /** @noinspection PhpSillyAssignmentInspection */
            $r = $r and (
                    ($c >= 65) && ($c <= 90)    // A-Z
                    or ($c >= 192) && ($c <= 223)  // Latin-1 letters
                    );
        }
        return($r);
    }
    #-- all-lowercase

    /**
     * @param $text
     * @return bool
     */
    function ctype_lower( $text )
    {
        $r = true;
        for ( $i = 0; $i < strlen( $text ); $i++ )
        {
            $c = ord( $text{$i} );
            /** @noinspection PhpSillyAssignmentInspection */
            $r = $r and (
                    ($c >= 97) && ($c <= 122)   // a-z
                    or ($c >= 224) && ($c <= 255)  // Latin-1 letters
                    );
        }
        return($r);
    }
    #-- everything except spaces that produces a valid printable output
    #   (this probably excludes contral chars as well)

    /**
     * @param $text
     * @return bool
     */
    function ctype_graph( $text )
    {
        $r = true;
        for ( $i = 0; $i < strlen( $text ); $i++ )
        {
            $c = ord( $text{$i} );
            /** @noinspection PhpSillyAssignmentInspection */
            $r = $r and ($c >= 33) and ($c != 160) and (($c <= 126) or ($c >= 161));
        }
        return($r);
    }
    #-- everything printable, but no spaces+letters+digits

    /**
     * @param $text
     * @return bool
     */
    function ctype_punct( $text )
    {
        $r = true;
        for ( $i = 0; $i < strlen( $text ); $i++ )
        {
            $c = ord( $text{$i} );
            /** @noinspection PhpSillyAssignmentInspection */
            $r = $r and (
                    ($c >= 33) && ($c <= 47)   // !../
                    or ($c >= 58) && ($c <= 64)   // :..@
                    or ($c >= 91) && ($c <= 96)   // [ ..`
                    or ($c >= 123) && ($c <= 126) // .. ~
                    or ($c >= 161) && ($c <= 191) // Latin-1 everything else
                    );
        }
        return($r);
    }
//   - no idea what this means exactly
//
//   function ctype_print($text) {
//   }
}


#-- diff associative arrays with two user callbacks
#   (if this looks complicated to you, don't even try to look at the manual)
if ( !function_exists( "array_udiff_uassoc" ) )
{

    /**
     * @return array
     */
    function array_udiff_uassoc()
    {
        $in     = func_get_args();
        $key_cb = array_pop( $in );
        $val_cb = array_pop( $in );
        $arr1   = array_shift( $in );
        $r      = array();

        foreach ( $arr1 as $i => $v )
        {
            #-- in each array, compare against each key/value pair
            foreach ( array_keys( $in ) as $c )
            {
                foreach ( $in[ $c ] as $i2 => $v2 )
                {

                    $key_cmp = call_user_func_array( $key_cb, array( $i, $i2 ) );
                    if ( $key_cmp == 0 )
                    {

                        #-- ok, in this case we must compare the data as well
                        $val_cmp = call_user_func_array( $val_cb, array( $v, $v2 ) );
                        if ( $val_cmp == 0 )
                        {
                            continue 3;
                        }
                    }
                }
            }

            #-- this combination isn't really found anywhere else
            $r[ $i ] = $v;
        }
        return($r);
    }
}


#-- same, but that keys now are compared normally (without callback)
if ( !function_exists( "array_udiff_assoc" ) )
{

    /**
     * @return array
     */
    function array_udiff_assoc()
    {
        $in     = func_get_args();
        $val_cb = array_pop( $in );
        $arr1   = array_shift( $in );
        $r      = array();

        #-- compare against each key/value pair in other arrays
        foreach ( $arr1 as $i => $v )
        {
            foreach ( array_keys( $in ) as $c )
            {
                if ( isset( $in[ $c ][ $i ] ) )
                {
                    #-- now compare data by callback
                    $cmp = call_user_func_array( $val_cb, array( $v, $in[ $c ][ $i ] ) );
                    if ( $cmp == 0 )
                    {
                        continue 2;
                    }
                }
            }
            #-- everything exists only in array1
            $r[ $i ] = $v;
        }
        return($r);
    }
}


#-- ....
if ( !function_exists( "array_diff_uassoc" ) )
{

    /**
     * @return array
     */
    function array_diff_uassoc()
    {
        $in     = func_get_args();
        $key_cb = array_pop( $in );
        $arr1   = array_shift( $in );
        $num    = count( $in );
        $r      = array();

        foreach ( $arr1 as $i => $v )
        {
            #-- in other arrays?
            for ( $c = 0; $c < $num; $c++ )
            {
                foreach ( $in[ $c ] as $i2 => $v2 )
                {
                    if ( $v == $v2 )
                    {
                        $cmp = call_user_func_array( $key_cb, array( $i, $i2 ) );
                        if ( $cmp == 0 )
                        {
                            continue 3;
                        }
                    }
                }
            }
            #-- exists only in array1
            $r[ $i ] = $v;
        }
        return($r);
    }
}


#-- diff array, keys ignored, callback for comparing values
if ( !function_exists( "array_udiff" ) )
{

    /**
     * @return array
     */
    function array_udiff()
    {
        $in     = func_get_args();
        $val_cb = array_pop( $in );
        $arr1   = array_shift( $in );
        $num    = count( $in );
        $r      = array();
        foreach ( $arr1 as $i => $v )
        {
            #-- check other arrays
            for ( $c = 0; $c < $num; $c++ )
            {
                foreach ( $in[ $c ] as $v2 )
                {
                    $cmp = call_user_func_array( $val_cb, array( $v, $v2 ) );
                    if ( $cmp == 0 )
                    {
                        continue 3;
                    }
                }
            }
            #-- exists only in array1
            $r[ $i ] = $v;
        }
        return($r);
    }
}












#-- same for intersections
if ( !function_exists( "array_uintersect_uassoc" ) )
{

    /**
     * @return array
     */
    function array_uintersect_uassoc()
    {
        $in     = func_get_args();
        $key_cb = array_pop( $in );
        $val_cb = array_pop( $in );
        $all    = array();
        $conc   = count( $in );
        foreach ( $in[ 0 ] as $i => $v )
        {
            #-- must exist in each array (at least once, callbacks may match fuzzy)
            for ( $c = 1; $c < $conc; $c++ )
            {
                $ok = false;
                foreach ( $in[ $c ] as $i2 => $v2 )
                {
                    $key_cmp = call_user_func_array( $key_cb, array( $i, $i2 ) );
                    $val_cmp = call_user_func_array( $val_cb, array( $v, $v2 ) );
                    if ( ($key_cmp == 0) && ($val_cmp == 0) )
                    {
                        $ok = true;
                        break;
                    }
                }
                if ( !$ok )
                {
                    continue 2;
                }
            }
            #-- exists in all arrays
            $all[ $i ] = $v;
        }
        return($all);
    }
}




#-- intersection again
if ( !function_exists( "array_uintersect_assoc" ) )
{

    /**
     * @return array
     */
    function array_uintersect_assoc()
    {
        $in     = func_get_args();
        $val_cb = array_pop( $in );
        $all    = array();
        $conc   = count( $in );
        foreach ( $in[ 0 ] as $i => $v )
        {
            #-- test for that entry in any other array
            for ( $c = 1; $c < $conc; $c++ )
            {
                if ( isset( $in[ $c ][ $i ] ) )
                {
                    $cmp = call_user_func_array( $val_cb, array( $v, $in[ $c ][ $i ] ) );
                    if ( $cmp == 0 )
                    {
                        continue;
                    }
                }
                #-- failed
                continue 2;
            }
            #-- exists in all arrays
            # (but for fuzzy matching: only the first entry will be returned here)
            $all[ $i ] = $v;
        }
        return($all);
    }
}





#-- array intersection, no keys compared, but callback for values
if ( !function_exists( "array_uintersect" ) )
{

    /**
     * @return array
     */
    function array_uintersect()
    {
        $in     = func_get_args();
        $val_cb = array_pop( $in );
        $arr1   = array_shift( $in );
        $num    = count( $in );
        $r      = array();

        foreach ( $arr1 as $i => $v )
        {
            #-- must have equivalent value in all other arrays
            for ( $c = 0; $c < $num; $c++ )
            {
                foreach ( $in[ $c ] as $i2 => $v2 )
                {
                    $cmp = call_user_func_array( $val_cb, array( $v, $v2 ) );
                    if ( $cmp == 0 )
                    {
                        continue 2; //found
                    }
                }
                continue 2; //failed
            }
            #-- everywhere
            $r[ $i ] = $v;
        }
        return($r);
    }
}




#-- diff array, keys ignored, callback for comparing values
if ( !function_exists( "array_intersect_uassoc" ) )
{

    function array_intersect_uassoc()
    {
        $args   = func_get_args();
        $key_cb = array_pop( $args );
        $array1 = array_shift( $args );
        $num    = count( $args );

        foreach ( $array1 as $i => $v )
        {
            #-- look through other arrays
            for ( $c = 0; $c < $num; $c++ )
            {
                $ok = 0;
                foreach ( $args[ $c ] as $i2 => $v2 )
                {
                    $cmp = call_user_func_array( $key_cb, array( $i, $i2 ) );
                    if ( ($cmp == 0) && ($v == $v2) )
                    {
                        $ok = 1;
                        continue 2;
                    }
                }
                if ( !$ok )
                {
                    continue 2;
                }
            }
            #-- found in all arrays
            if ( $ok )
            {
                $diff[ $i ] = $v;
            }
        }
        return($diff);
    }
}
?>