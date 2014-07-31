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
 * @package     Library
 * @version     3.0.0 Beta
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        functions.php
 */
function flush_buffers()
{
    ob_end_flush();
    ob_flush();
    flush();
    ob_start();
}
/**
 * Return the real REMOTE_ADDR even if a proxy server is used
 * @return string
 */
function ip()
{
	global $HTTP_SERVER_VARS, $_SERVER;

	$ra = $_SERVER[ 'REMOTE_ADDR' ];

	if ( $ra == '' && isset( $HTTP_SERVER_VARS[ 'REMOTE_ADDR' ] ) )
	{
		$ra = $HTTP_SERVER_VARS[ 'REMOTE_ADDR' ];
	}

	$ip = null;

	if ( isset( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) && !empty( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) )
	{
		$ip = $_SERVER[ 'HTTP_X_FORWARDED_FOR' ];
	}
	elseif ( isset( $HTTP_SERVER_VARS[ 'HTTP_X_FORWARDED_FOR' ] ) && !empty( $HTTP_SERVER_VARS[ 'HTTP_X_FORWARDED_FOR' ] ) )
	{
		$ip = $HTTP_SERVER_VARS[ 'HTTP_X_FORWARDED_FOR' ];
	}

	if ( $ip === null && isset( $_SERVER[ 'HTTP_CLIENT_IP' ] ) && !empty( $_SERVER[ 'HTTP_CLIENT_IP' ] ) )
	{
		$ip = $_SERVER[ 'HTTP_CLIENT_IP' ];
	}
	elseif ( $ip === null && isset( $HTTP_SERVER_VARS[ 'HTTP_CLIENT_IP' ] ) && !empty( $HTTP_SERVER_VARS[ 'HTTP_CLIENT_IP' ] ) )
	{
		$ip = $HTTP_SERVER_VARS[ 'HTTP_CLIENT_IP' ];
	}
	else
	{
		$ip = $ra;
	}

	return $ip;


	return (!empty( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) && substr( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ], 0, 1 ) !== ':' ? $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] : $_SERVER[ 'REMOTE_ADDR' ]);
}

function rangeDownload( $file )
{

    $fp = @fopen( $file, 'rb' );

    $size   = filesize( $file ); // File size
    $length = $size;           // Content length
    $start  = 0;               // Start byte
    $end    = $size - 1;       // End byte
    // Now that we've gotten so far without errors we send the accept range header
    /* At the moment we only support single ranges.
     * Multiple ranges requires some more work to ensure it works correctly
     * and comply with the spesifications: http://www.w3.org/Protocols/rfc2616/rfc2616-sec19.html#sec19.2
     *
     * Multirange support annouces itself with:
     * header('Accept-Ranges: bytes');
     *
     * Multirange content must be sent with multipart/byteranges mediatype,
     * (mediatype = mimetype)
     * as well as a boundry header to indicate the various chunks of data.
     */
    header( "Accept-Ranges: 0-$length" );
    // header('Accept-Ranges: bytes');
    // multipart/byteranges
    // http://www.w3.org/Protocols/rfc2616/rfc2616-sec19.html#sec19.2
    if ( isset( $_SERVER[ 'HTTP_RANGE' ] ) )
    {

        $c_start = $start;
        $c_end   = $end;
        // Extract the range string
        list(, $range) = explode( '=', $_SERVER[ 'HTTP_RANGE' ], 2 );
        // Make sure the client hasn't sent us a multibyte range
        if ( strpos( $range, ',' ) !== false )
        {

            // (?) Shoud this be issued here, or should the first
            // range be used? Or should the header be ignored and
            // we output the whole content?
            header( 'HTTP/1.1 416 Requested Range Not Satisfiable' );
            header( "Content-Range: bytes $start-$end/$size" );
            // (?) Echo some info to the client?
            exit;
        }
        // If the range starts with an '-' we start from the beginning
        // If not, we forward the file pointer
        // And make sure to get the end byte if spesified
        if ( $range0 == '-' )
        {

            // The n-number of the last bytes is requested
            $c_start = $size - substr( $range, 1 );
        }
        else
        {

            $range   = explode( '-', $range );
            $c_start = $range[ 0 ];
            $c_end   = (isset( $range[ 1 ] ) && is_numeric( $range[ 1 ] )) ? $range[ 1 ] : $size;
        }
        /* Check the range and make sure it's treated according to the specs.
         * http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
         */
        // End bytes can not be larger than $end.
        $c_end = ($c_end > $end) ? $end : $c_end;
        // Validate the requested range and return an error if it's not correct.
        if ( $c_start > $c_end || $c_start > $size - 1 || $c_end >= $size )
        {

            header( 'HTTP/1.1 416 Requested Range Not Satisfiable' );
            header( "Content-Range: bytes $start-$end/$size" );
            // (?) Echo some info to the client?
            exit;
        }
        $start  = $c_start;
        $end    = $c_end;
        $length = $end - $start + 1; // Calculate new content length
        fseek( $fp, $start );
        header( 'HTTP/1.1 206 Partial Content' );
    }
    // Notify the client the byte range we'll be outputting
    header( "Content-Range: bytes $start-$end/$size" );
    header( "Content-Length: $length" );

    // Start buffered download
    $buffer = 1024 * 8;
    while ( !feof( $fp ) && ($p      = ftell( $fp )) <= $end )
    {

        if ( $p + $buffer > $end )
        {

            // In case we're only outputtin a chunk, make sure we don't
            // read past the length
            $buffer = $end - $p + 1;
        }
        set_time_limit( 0 ); // Reset time limit for big files
        echo fread( $fp, $buffer );
        flush(); // Free up memory. Otherwise large files will trigger PHP's memory limit.
    }

    fclose( $fp );
}

function Stream( $file )
{
    header_remove();
    $arr = get_headers( $file );
    foreach ( $arr as &$value )
    {
        if ( (strpos( $value, 'Content-Type' ) !== false ) )
        {
            header( $value );
        }
    }

    if ( isset( $_SERVER[ 'HTTP_RANGE' ] ) )
    {
        rangeDownload( $file );
    }
    else
    {
        header( 'HTTP/1.1 206 Partial Content' );
        header( "Content-Length:1" );
        //foreach ($arr as &$value) {if((strpos($value,'Content-Length')!== false)){header($value);}}
//header("Content-Range:bytes 21056-21056/243957100");
        readfile( $file );
    }
}


// Read a file and display its content chunk by chunk
function readfile_chunked( $filename, $retbytes = TRUE )
{

    $buffer = '';
    $cnt    = 0;

    $handle = fopen( $filename, 'rb' );
    if ( $handle === false )
    {
        return false;
    }
    set_time_limit( 0 );
    while ( !feof( $handle ) )
    {
        $buffer = fread( $handle, CHUNK_SIZE );
        echo $buffer;
        flush_buffers();

        if ( $retbytes )
        {
            $cnt += strlen( $buffer );
        }
    }

    $status = fclose( $handle );

    if ( $retbytes && $status )
    {
        return $cnt; // return num. bytes delivered like readfile() does.
    }
    return $status;
}

function remove_invisible_characters( $str, $url_encoded = true )
{
    $non_displayables = array();

    // every control character except newline (dec 10),
    // carriage return (dec 13) and horizontal tab (dec 09)
    if ( $url_encoded )
    {
        $non_displayables[] = '/%0[0-8bcef]/'; // url encoded 00-08, 11, 12, 14, 15
        $non_displayables[] = '/%1[0-9a-f]/'; // url encoded 16-31
    }

    $non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S'; // 00-08, 11, 12, 14-31, 127

    do
    {
        $str = preg_replace( $non_displayables, '', $str, -1, $count );
    }
    while ( $count );

    return $str;
}

function betterUnserialize( $raw )
{

    # Split raw data into fields
    $rawList = explode( ':', $raw, 3 );

    # Get datatype field
    $type = $rawList[ 0 ];

    # Basically a switch operation on datatype possibilities
    if ( $type == 'N;' )
    {
        # ... NULL datatype
        return 0;
    }
    elseif ( $type == 'i' or $type == 'd' or $type == 'b' )
    {
        # ... Integer, Float and Boolean datatypes
        $body = $rawList[ 1 ];
        //	chomp($body);
        return substr( $body, 0, -1 );
    }
    elseif ( $type == 's' )
    {
        # ... String datatype
        $len  = $rawList[ 1 ];
        $body = $rawList[ 2 ];
        //	chomp($body);
        return substr( $body, 1, $len );
    }
    elseif ( $type == 'a' )
    {

        # ... Use a recursive solution for Arrays
        $keyMatch = 'i:\d+;|s:\d+:\".*\";';
        $valMatch = 'N;|b:[01];|i:\d+;|d:\d+\.\d+;|s:\d+:\".*\";|a:\d+:\{.*\}';
        $assoc    = array();
        $len      = $rawList[ 1 ];
        $body     = $rawList[ 2 ];
        $body     = trim( $body );
        $body     = substr( $body, 1, -1 );

        #preg_match_all( "/($keyMatch)($valMatch).*/isU", $body, $match );
        # print_r($match);
        #echo $body;


        while ( preg_match( "/^(($keyMatch)($valMatch)($keyMatch|$))(.*)$/sU", $body, $match ) )
        {
            print_r( $match );
            $name = $match[ 2 ];
            if ( strpos( $name, '"' ) !== false )
            {
                $pos  = strpos( $name, '"' );
                $name = substr( $name, $pos + 1 );

                $pos  = strpos( $name, '"' );
                $name = substr( $name, 0, $pos );
            }

            $assoc[ /* unserialize( $match[ 2 ] ) */ $name ] = @unserialize( $match[ 3 ] );
            $body                                            = $match[ 4 ] . $match[ 5 ];

            //  unset( $match );
        }
        print_r( $assoc );


        return array();
        # Return reference to hash; allows multi-layer arrays
        return $assoc;
    }
    elseif ( $type == 'O' )
    {
        # ... Use a recursive solution for Objects
        //	my ($obj, @objList, $className, $objLen, $objBody, $objAssoc);
        //	my %object;

        $obj     = $rawList[ 2 ];
        $objList = explode( ':', $obj, 3 );

        $className = substr( $objList[ 0 ], 1, -1 );
        $objLen    = $objList[ 1 ];
        $objBody   = $objList[ 2 ];
        # A little hacky; plunders the Array unserialize logic.
        $objAssoc  = unserialize( "a:$objLen:$objBody" );

        # We must distinguish Objects from Arrays in the internal PERL 
        # representation.  We do this by using an undef hash key
        # 'OBJECT'.  No PHP array should return an undef hash key.
        # Therefore the test exists($hash{'OBJECT'}) combined with
        # not(defined($hash{'OBJECT'})) should work to determine if
        # the hash returned is an array or an object.
        $object        = new stdClass();
        $object->name  = $className;
        $object->len   = $objLen;
        $object->assoc = $objAssoc;

        return $object;
    }
}

/**
 * Check whether serialized data is of string type.
 *
 * @since 2.0.5
 *
 * @param mixed $data Serialized data
 * @return bool False if not a serialized string, true if it is.
 */
function is_serialized_string( $data )
{
    // if it isn't a string, it isn't a serialized string
    if ( !is_string( $data ) )
        return false;
    $data   = trim( $data );
    $length = strlen( $data );
    if ( $length < 4 )
        return false;
    elseif ( ':' !== $data[ 1 ] )
        return false;
    elseif ( ';' !== $data[ $length - 1 ] )
        return false;
    elseif ( $data[ 0 ] !== 's' )
        return false;
    elseif ( '"' !== $data[ $length - 2 ] )
        return false;
    else
        return true;
}

/**
 * Check value to find if it was serialized.
 *
 * If $data is not an string, then returned value will always be false.
 * Serialized data is always a string.
 *
 * @since 2.0.5
 *
 * @param mixed $data Value to check to see if was serialized.
 * @return bool False if not serialized and true if it was.
 */
function is_serialized( $data )
{
    // if it isn't a string, it isn't serialized
    if ( !is_string( $data ) )
        return false;
    $data   = trim( $data );
    if ( 'N;' == $data )
        return true;
    $length = strlen( $data );
    if ( $length < 4 )
        return false;
    if ( ':' !== $data[ 1 ] )
        return false;
    $lastc  = $data[ $length - 1 ];
    if ( ';' !== $lastc && '}' !== $lastc )
        return false;
    $token  = $data[ 0 ];
    switch ( $token )
    {
        case 's' :
            if ( '"' !== $data[ $length - 2 ] )
                return false;
        case 'a' :
        case 'O' :
            return ( bool ) preg_match( "/^{$token}:[0-9]+:/s", $data );
        case 'b' :
        case 'i' :
        case 'd' :
            return ( bool ) preg_match( "/^{$token}:[0-9.E-]+;\$/", $data );
    }
    return false;
}

function fixSerialzedString( &$string )
{

    preg_match_all( '#(\d:"([^"]*)")#sSU', $string, $matches );
    if ( count( $matches[ 2 ] ) )
    {
        foreach ( $matches[ 2 ] as $str )
        {
            if ( strpos( $str, '{' ) !== false && strpos( $str, '\{' ) === false )
            {
                $string = preg_replace( '#\{#U', '\{', $string, 1 );
            }

            if ( strpos( $str, '}' ) !== false && strpos( $str, '\}' ) === false )
            {
                $string = preg_replace( '#\}#U', '\}', $string, 1 );
            }
        }

        # print_r($matches);
        #die($string);
    }
}

function checkSerialization( $string, &$errmsg )
{

    $str       = 's';
    $array     = 'a';
    $integer   = 'i';
    $any       = '[^}]*?';
    $count     = '\d+';
    $content   = '"(?:\\\";|.)*?";';
    $open_tag  = '\{';
    $close_tag = '\}';
    $parameter = "($str|$array|$integer|$any):($count)" . "(?:[:]($open_tag|$content)|[;])";
    $preg      = "/$parameter|($close_tag)/";
    if ( !preg_match_all( $preg, $string, $matches ) )
    {
        $errmsg = 'not a serialized string';
        return false;
    }

    $open_arrays = 0;
    foreach ( $matches[ 1 ] AS $key => $value )
    {
        if ( !empty( $value ) && ( $value != $array xor $value != $str xor $value != $integer ) )
        {
            $errmsg = 'undefined datatype';
            return false;
        }
        if ( $value == $array )
        {
            $open_arrays++;
            if ( $matches[ 3 ][ $key ] != '{' )
            {
                $errmsg = 'open tag expected';
                return false;
            }
        }
        if ( $value == '' )
        {
            if ( $matches[ 4 ][ $key ] != '}' )
            {
                $errmsg = 'close tag expected';
                return false;
            }
            $open_arrays--;
        }
        if ( $value == $str )
        {
            $aVar = ltrim( $matches[ 3 ][ $key ], '"' );
            $aVar = rtrim( $aVar, '";' );
            if ( strlen( $aVar ) != $matches[ 2 ][ $key ] )
            {
                $errmsg = 'stringlen for string not match $key:' . $key . ' $value:' . $value . ' ' . $matches[ 3 ][ $key ];
                return false;
            }
        }
        if ( $value == $integer )
        {
            if ( !empty( $matches[ 3 ][ $key ] ) )
            {
                $errmsg = 'unexpected data';
                return false;
            }
            if ( !is_integer( ( int ) $matches[ 2 ][ $key ] ) )
            {
                $errmsg = 'integer expected';
                return false;
            }
        }
    }
    if ( $open_arrays != 0 )
    {
        $errmsg = 'wrong setted arrays';
        return false;
    }
    return true;
}

/**
 * 
 * @param int|boolean $contentValue
 * @param int|boolean $baseAllowed
 * @return boolean
 */
function allowComment( $contentValue, $baseAllowed = false )
{


    return false;
}

/**
 *
 * @param string $class
 * @return bool
 */
function checkClass( $class )
{
    if ( class_exists( $class, true ) )
    {
        return true;
    }

    return false;
}

/**
 *
 * @param string $classMethod e.g: className/Method
 * @param string $type (static, public, private ) default is public
 * @return boolean
 */
function checkClassMethod( $classMethod, $type = '' )
{
    // Debug::store('Process checkClassMethod', $classMethod);
    list($class, $method) = explode( '/', $classMethod );


    if ( class_exists( $class, true ) )
    {
        try
        {
            $refl = new ReflectionMethod( $class, $method );
        }
        catch ( ReflectionException $e )
        {
            return false;
        }
    }

	if (!isset($refl)) {
		return false;
	}

    if ( gettype( $refl ) != 'object' )
    {
        return false;
    }

    switch ( $type )
    {
        case "static":
            if ( $refl->isPublic() )
            {
                return $refl->isStatic();
            }
            return false;
        case "public":
        default:
            return $refl->isPublic();
        case "private":
            return $refl->isPrivate();
    }

    $refl = null;

    return false;
}
/*
 * translation functions
 */
if ( !function_exists( 'trans' ) )
{

    function trans( $str )
    {


        /*
          $ret = T_($string);

          if ($ret !== '')
          {
          return $ret;
          } */

        return $str;
    }
}


if ( !function_exists( 'setlocale' ) )
{
    include VENDOR_PATH . 'php-gettext/combined.php';
}

// temporary stuff
if ( !defined( 'LC_MESSAGES' ) )
{
    define( 'LC_MESSAGES', 'de_DE' );
}

$GLOBALS[ 'trans_bound_domains' ] = array();

if ( !function_exists( 'gettext' ) )
{

    function gettext( $string )
    {
        /*
          $ret = T_($string);

          if ( $ret !== '')
          {
          return $ret;
          }
         */
        return $string;
    }
}


if ( !function_exists( 'p_trans' ) )
{

    function p_trans( $domain, $string )
    {
        if ( !in_array( $domain, $GLOBALS[ 'trans_bound_domains' ] ) )
        {
            T_bindtextdomain( $domain, I18N_PATH );
            T_bind_textdomain_codeset( $domain, 'utf8' );
            $GLOBALS[ 'trans_bound_domains' ][] = $domain;
        }

        return T_dtrans( $domain, $string );
    }
}

/**
 * 
 */
function demoadm()
{
    if ( defined( 'DEMO_USERID' ) && DEMO_USERID > 0 )
    {
        if ( User::getUserId() == DEMO_USERID )
        {
            if ( IS_AJAX )
            {
                Library::sendJson( false, trans( 'Diese Funktion steht in der Demonstrtion nicht zur Verfügung!' ) );

                exit;
            }
            else
            {
                die( trans( 'Diese Funktion steht in der Demonstrtion nicht zur Verfügung!' ) );
            }
        }
    }
}
if ( !defined( 'USE_MBSTRING' ) )
{
    define( 'USE_MBSTRING', function_exists( 'mb_strlen' ) );
}


/**
 * Fallback for UTF8 Functions
 *
 */
if ( !defined( 'USED_MBSTRING' ) )
{
    if ( USE_MBSTRING )
    {
        mb_internal_encoding( 'UTF-8' );
    }

    define( 'USED_MBSTRING', true );

    /**
     * Return a specific character
     *
     * Unicode version of chr() that handles UTF-8 characters. It is basically
     * used as callback function for utf8_decode_entities().
     * @param integer
     * @return string
     */
    function utf8_chr( $dec )
    {
        if ( $dec < 128 )
            return chr( $dec );

        if ( $dec < 2048 )
            return chr( ($dec >> 6) + 192 ) . chr( ($dec & 63) + 128 );

        if ( $dec < 65536 )
            return chr( ($dec >> 12) + 224 ) . chr( (($dec >> 6) & 63) + 128 ) . chr( ($dec & 63) + 128 );

        if ( $dec < 2097152 )
            return chr( ($dec >> 18) + 240 ) . chr( (($dec >> 12) & 63) + 128 ) . chr( (($dec >> 6) & 63) + 128 ) . chr( ($dec & 63) + 128 );

        return '';
    }

    /**
     * Return the ASCII value of a character
     *
     * Unicode version of ord() that handles UTF-8 characters. The function has
     * been published by R. Rajesh Jeba Anbiah on php.net.
     * @param string
     * @return integer
     */
    function utf8_ord( $str )
    {
        if ( ord( $str{0} ) >= 0 && ord( $str{0} ) <= 127 )
            return ord( $str{0} );

        if ( ord( $str{0} ) >= 192 && ord( $str{0} ) <= 223 )
            return (ord( $str{0} ) - 192) * 64 + (ord( $str{1} ) - 128);

        if ( ord( $str{0} ) >= 224 && ord( $str{0} ) <= 239 )
            return (ord( $str{0} ) - 224) * 4096 + (ord( $str{1} ) - 128) * 64 + (ord( $str{2} ) - 128);

        if ( ord( $str{0} ) >= 240 && ord( $str{0} ) <= 247 )
            return (ord( $str{0} ) - 240) * 262144 + (ord( $str{1} ) - 128) * 4096 + (ord( $str{2} ) - 128) * 64 + (ord( $str{3} ) - 128);

        if ( ord( $str{0} ) >= 248 && ord( $str{0} ) <= 251 )
            return (ord( $str{0} ) - 248) * 16777216 + (ord( $str{1} ) - 128) * 262144 + (ord( $str{2} ) - 128) * 4096 + (ord( $str{3} ) - 128) * 64 + (ord( $str{4} ) - 128);

        if ( ord( $str{0} ) >= 252 && ord( $str{0} ) <= 253 )
            return (ord( $str{0} ) - 252) * 1073741824 + (ord( $str{1} ) - 128) * 16777216 + (ord( $str{2} ) - 128) * 262144 + (ord( $str{3} ) - 128) * 4096 + (ord( $str{4} ) - 128) * 64 + (ord( $str{5} ) - 128);

        if ( ord( $str{0} ) >= 254 && ord( $str{0} ) <= 255 ) //error
            return false;

        return 0;
    }

    /**
     * Convert character encoding
     *
     * Use utf8_decode() to convert UTF-8 to ISO-8859-1, otherwise use iconv()
     * or mb_convert_encoding(). Return the original string if none of these
     * libraries is available.
     * @param string
     * @param string
     * @param string
     * @return string
     */
    function utf8_convert_encoding( $str, $to, $from = null )
    {
        if ( !$str )
            return '';

        if ( !$from )
            $from = utf8_detect_encoding( $str );

        if ( $from == $to )
            return $str;

        if ( $from == 'UTF-8' && $to == 'ISO-8859-1' )
            return utf8_decode( $str );

        if ( $from == 'ISO-8859-1' && $to == 'UTF-8' )
            return utf8_encode( $str );

        if ( USE_MBSTRING )
        {
            @mb_substitute_character( 'none' );
            return @mb_convert_encoding( $str, $to, $from );
        }

        if ( function_exists( 'iconv' ) )
        {
            if ( strlen( $iconv = @iconv( $from, $to . '//IGNORE', $str ) ) )
                return $iconv;

            return @iconv( $from, $to, $str );
        }

        return $str;
    }

    /**
     * Convert all unicode entities to their applicable characters
     *
     * Calls utf8_chr() to convert unicode entities. HTML entities like '&nbsp;'
     * or '&quot;' will not be decoded.
     * @param string
     * @return string
     */
    function utf8_decode_entities( $str )
    {
        $str = preg_replace_callback( '~&#x([0-9a-f]+);~i', 'utf8_hexchr_callback', $str );
        $str = preg_replace_callback( '~&#([0-9]+);~', 'utf8_chr_callback', $str );

        return $str;
    }

    /**
     * Callback function for utf8_decode_entities 
     * @param array
     * @return string
     */
    function utf8_chr_callback( $matches )
    {
        return utf8_chr( $matches[ 1 ] );
    }

    /**
     * Callback function for utf8_decode_entities 
     * @param array
     * @return string
     */
    function utf8_hexchr_callback( $matches )
    {
        return utf8_chr( hexdec( $matches[ 1 ] ) );
    }

    /**
     * Detect the encoding of a string
     *
     * Use mb_detect_encoding() if available since it seems to be about 20 times
     * faster than using ereg() or preg_match().
     * @param string
     * @return string
     */
    function utf8_detect_encoding( $str )
    {
        if ( USE_MBSTRING )
            return mb_detect_encoding( $str, array(
                    'ASCII',
                    'ISO-2022-JP',
                    'UTF-8',
                    'EUC-JP',
                    'ISO-8859-1' ) );

        if ( !preg_match( "/[\x80-\xFF]/", $str ) )
        {
            if ( !preg_match( "/\x1B/", $str ) )
                return 'ASCII';

            return 'ISO-2022-JP';
        }

        if ( preg_match( "/^([\x01-\x7F]|[\xC0-\xDF][\x80-\xBF]|[\xE0-\xEF][\x80-\xBF][\x80-\xBF])+$/", $str ) == 1 )
            return 'UTF-8';

        if ( preg_match( "/^([\x01-\x7F]|\x8E[\xA0-\xDF]|\x8F[xA1-\xFE][\xA1-\xFE]|[\xA1-\xFE][\xA1-\xFE])+$/", $str ) == 1 )
            return 'EUC-JP';

        return 'ISO-8859-1';
    }

    /**
     * Romanize a string
     *
     * Use the UTF-8 lookup table to replace non ascii characters with their
     * respective roman character.
     * @param string
     * @return string
     */
    function utf8_romanize( $str )
    {
        global $UTF8_LOOKUP_TABLE;

        if ( !is_array( $UTF8_LOOKUP_TABLE ) )
            require_once(HELPER_PATH . 'utf8_lookup.php');

        return strtr( utf8_convert_encoding( $str, 'UTF-8' ), $UTF8_LOOKUP_TABLE[ 'romanize' ] );
    }

    /**
     * Determine the number of characters of a string
     *
     * Use mb_strlen() if available since it seems to be the fastes way to
     * determine the string length. Otherwise decode the string (will convert
     * non ISO-8859-1 characters to '?') and use strlen().
     * @param string
     * @return integer
     */
    function utf8_strlen( $str )
    {
        if ( USE_MBSTRING )
            return mb_strlen( $str );

        return strlen( utf8_decode( $str ) );
    }

    /**
     * Find the position of the first occurence of a string in another string
     *
     * Use mb_strpos() if available. Otherwise combine strpos() and utf8_strlen()
     * to detect the numeric position of the first occurrence.
     * @param string
     * @param string
     * @param integer
     * @return integer
     */
    function utf8_strpos( $haystack, $needle, $offset = 0 )
    {
        if ( USE_MBSTRING )
        {
            if ( $offset === 0 )
                return mb_strpos( $haystack, $needle );

            return mb_strpos( $haystack, $needle, $offset );
        }

        $comp   = 0;
        $length = null;

        while ( is_null( $length ) || $length < $offset )
        {
            $pos = strpos( $haystack, $needle, $offset + $comp );

            if ( $pos === false )
                return false;

            $length = utf8_strlen( substr( $haystack, 0, $pos ) );

            if ( $length < $offset )
                $comp = $pos - $length;
        }

        return $length;
    }

    /**
     * Find the last occurrence of a character in a string
     *
     * Use mb_strrchr() if available since it seems to be about eight times
     * faster than combining utf8_substr() and utf8_strrpos().
     * @param string
     * @param string
     * @return string
     */
    function utf8_strrchr( $haystack, $needle )
    {
        if ( USE_MBSTRING )
            return mb_strrchr( $haystack, $needle );

        $pos = utf8_strrpos( $haystack, $needle );

        if ( $pos === false )
            return false;

        return utf8_substr( $haystack, $pos );
    }

    /**
     * Find the position of the last occurrence of a string in another string
     *
     * Use mb_strrpos() if available since it is about twice as fast as our
     * workaround. Otherwise use utf8_strlen() to determine the position.
     * @param string
     * @param string
     * @return mixed
     */
    function utf8_strrpos( $haystack, $needle )
    {
        if ( USE_MBSTRING )
            return mb_strrpos( $haystack, $needle );

        $pos = strrpos( $haystack, $needle );

        if ( $pos === false )
            return false;

        return utf8_strlen( substr( $haystack, 0, $pos ) );
    }

    /**
     * Find the first occurrence of a string in another string
     *
     * Use mb_strstr() if available since it seems to be about eight times
     * faster than combining utf8_substr() and utf8_strpos().
     * @param string
     * @param string
     * @return string
     */
    function utf8_strstr( $haystack, $needle )
    {
        if ( USE_MBSTRING )
            return mb_strstr( $haystack, $needle );

        $pos = utf8_strpos( $haystack, $needle );

        if ( $pos === false )
            return false;

        return utf8_substr( $haystack, $pos );
    }

    /**
     * Make a string lowercase
     *
     * Use mb_strtolower() if available, although our workaround does not seem
     * to be significantly slower.
     * @param string
     * @return string
     */
    function utf8_strtolower( $str )
    {
        if ( USE_MBSTRING )
            return mb_strtolower( $str, utf8_detect_encoding( $str ) );

        global $UTF8_LOOKUP_TABLE;

        if ( !is_array( $UTF8_LOOKUP_TABLE ) )
            require_once(HELPER_PATH . 'utf8_lookup.php');

        return strtr( $str, $UTF8_LOOKUP_TABLE[ 'strtolower' ] );
    }

    /**
     * Make a string uppercase
     *
     * Use mb_strtoupper() if available, although our workaround does not seem
     * to be significantly slower.
     * @param string
     * @return string
     */
    function utf8_strtoupper( $str )
    {
        if ( USE_MBSTRING )
            return mb_strtoupper( $str, utf8_detect_encoding( $str ) );

        global $UTF8_LOOKUP_TABLE;

        if ( !is_array( $UTF8_LOOKUP_TABLE ) )
            require_once(HELPER_PATH . 'utf8_lookup.php');

        return strtr( $str, $UTF8_LOOKUP_TABLE[ 'strtoupper' ] );
    }

    /**
     * Return part of a string
     *
     * Use mb_substr() if available since it is about three times faster than
     * our workaround. Otherwise, use PCRE regular expressions with 'u' flag.
     * Thanks to Andreas Gohr <andi@splitbrain.org> for this wonderful algorithm
     * which is the fastes workaround I could find on the internet.
     * @param string
     * @param integer
     * @param integer
     * @return string
     */
    function utf8_substr( $str, $start, $length = null )
    {
        if ( USE_MBSTRING )
        {
            if ( is_null( $length ) )
                return mb_substr( $str, $start );

            return mb_substr( $str, $start, $length );
        }

        $str   = ( string ) $str;
        $start = ( int ) $start;

        if ( !is_null( $length ) )
            $length = ( int ) $length;

        // Handle trivial cases
        if ( $length === 0 )
            return '';

        if ( $start < 0 && $length < 0 && $length < $start )
            return '';

        $start_pattern  = '';
        $length_pattern = '';

        // Normalise -ve offsets
        if ( $start < 0 )
        {
            $strlen = strlen( utf8_decode( $str ) );
            $start  = $strlen + $start;

            if ( $start < 0 )
                $start = 0;
        }

        // Establish a pattern for offset
        if ( $start > 0 )
        {
            $Ox = ( int ) ($start / 65535);
            $Oy = $start % 65535;

            if ( $Ox )
                $start_pattern = '(?:.{65535}){' . $Ox . '}';

            $start_pattern = '^(?:' . $start_pattern . '.{' . $Oy . '})';
        }

        // Anchor the pattern if offset == 0
        else
        {
            $start_pattern = '^';
        }

        // Establish a pattern for length
        if ( is_null( $length ) )
        {
            $length_pattern = '(.*)$';
        }
        else
        {
            if ( !isset( $strlen ) )
                $strlen = strlen( utf8_decode( $str ) );

            if ( $start > $strlen )
                return '';

            if ( $length > 0 )
            {
                // Reduce any length that would go passed the end of the string
                $length = min( $strlen - $start, $length );

                $Lx = ( int ) ($length / 65535);
                $Ly = $length % 65535;

                if ( $Lx )
                    $length_pattern = '(?:.{65535}){' . $Lx . '}';

                $length_pattern = '(' . $length_pattern . '.{' . $Ly . '})';
            }
            else if ( $length < 0 )
            {
                if ( $length < ($start - $strlen) )
                    return '';

                $Lx = ( int ) ((-$length) / 65535);
                $Ly = (-$length) % 65535;

                if ( $Lx )
                    $length_pattern = '(?:.{65535}){' . $Lx . '}';

                $length_pattern = '(.*)(?:' . $length_pattern . '.{' . $Ly . '})$';
            }
        }

        $match = array();

        if ( !preg_match( '#' . $start_pattern . $length_pattern . '#us', $str, $match ) )
            return '';

        return $match[ 1 ];
    }

    /**
     * Make sure the first letter is uppercase
     *
     * @param string
     * @return string
     */
    function utf8_ucfirst( $str )
    {
        return utf8_strtoupper( utf8_substr( $str, 0, 1 ) ) . utf8_substr( $str, 1 );
    }

    /**
     * Convert a string to an array
     *
     * Unicode version of str_split() that handles UTF-8 characters. The function
     * has been published by saeedco on php.net.
     * @param string
     * @return array
     */
    function utf8_str_split( $str )
    {
        $array = array();

        for ( $i = 0; $i < strlen( $str ); )
        {
            $split = 1;
            $value = ord( $str[ $i ] );
            $key   = NULL;

            if ( $value >= 192 && $value <= 223 )
                $split = 2;
            elseif ( $value >= 224 && $value <= 239 )
                $split = 3;
            elseif ( $value >= 240 && $value <= 247 )
                $split = 4;

            for ( $j = 0; $j < $split; $j++, $i++ )
            {
                $key .= $str[ $i ];
            }

            array_push( $array, $key );
        }

        return $array;
    }
}
?>