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
 * @file         Functions.php
 *
 * validate math function
 */

class Compiler_Functions
{

    /**
     * @var int
     */
    static $_cycleIterator = 0;

    /**
     * @var array
     */
    static private $_ignoreFunctions = array(
        'getSystemFunctions' => true,
        'buildAttributes'    => true,
        'processContainer'   => true,
        'isContainer'        => true,
        'containsKey'        => true
    );

    /**
     * @var array
     */
    private static $_phpFunctions = array(

        'empty'      => 'empty',
        'isset'      => 'isset',
        'set'        => 'isset',
        'count'      => 'count',
        'sizeof'     => 'sizeof',
        'position'   => 'strpos',
        'trim'       => 'trim',
        'repeat'     => 'str_repeat',
        'scalar'     => 'is_scalar',
        'gettext'    => 'trans',
        'countChars' => 'strlen',
        'countWords' => 'str_word_count',
        'is_array'   => 'is_array',
        'in_array'   => 'in_array',
        'isArray'    => 'is_array',
        'inArray'    => 'in_array',
        'test'       => 'stripos',
        'intval'     => 'intval',
        'round'      => 'round',
        'ceil'       => 'ceil',
        'ucfirst'    => 'ucfirst',
        'phpversion' => 'phpversion',
        'serialize'  => 'serialize'
        // 'lower'      => 'strtolower'
    );

    /**
     * @var Date
     */
    private static $date = null;

    protected static $_charset = null;

    protected static $_sf = null;

    /**
     * @param $charset
     * @return array
     */
    public static function getSystemFunctions( $charset )
    {
        self::$_charset = $charset;
        $path = dirname( __FILE__ ) . '/';

        if (file_exists($path . 'sf.php'))
        {
            global $_TPLFUNCS;

            include_once $path . 'sf.php';

            self::$_sf = $_TPLFUNCS;
            return self::$_sf;
        }

        $result = array();

        // create a reflection of this class
        $class = new ReflectionClass( 'Compiler_Functions' );
        foreach ( $class->getMethods( ReflectionMethod::IS_STATIC | ReflectionMethod::IS_PUBLIC ) as $method )
        {
            if ( !isset( self::$_ignoreFunctions[ $method->getName() ] ) )
            {
                $result[ $method->getName() ] = __CLASS__ . '::' . $method->getName();
            }
        }

        self::$_sf = $result = array_merge( self::$_phpFunctions, $result );

        file_put_contents($path . 'sf.php', '<'.'?php $_TPLFUNCS = '.Compiler_Helper::var_export_min($result, true) .'; ?'.'>' );

        return $result;
    }


    /**
     * The method allows to create aggregate functions that operate
     * on container. It calls the specified function for all the
     * container elements provided in the first function call
     * argument.
     *
     * @static
     * @param Callback $callback A valid function callback
     * @param Array $args The list of function arguments.
     * @return Container Processed container
     */
    static private function processContainer($callback, $args)
    {

        $result = array();
        if ( is_array( $args[ 0 ] ) )
        {
            foreach ( $args[ 0 ] as $idx => $value )
            {
                $args[ 0 ]      = $value;
                $result[ $idx ] = call_user_func_array( $callback, $args );
            }
        }

        return $result;
    }

    /**
     * Returns true, if the specified value is a valid BT container.
     *
     * @static
     * @param Mixed $value The value to test.
     * @return Boolean True, if the value is really a container
     */
    static private function isContainer($value)
    {

        return is_array( $value ) || !empty( $value ) || ( is_object( $value ) && ( $value instanceof Iterator || $value instanceof IteratorAggregate ) );
    }

    /**
     * Returns true, if the container contains the specified key.
     *
     * @static
     * @param string $item The container
     * @param mixed $key The key
     * @return True, if the key exists in the container.
     */
    static private function containsKey($item, $key)
    {

        if ( is_array( $item ) )
        {
            return isset( $item[ $key ] );
        }

        return false;
    }


    /**
     * @param $str
     * @return string
     */
    static public function json($str)
    {
        return Json::encode( $str );
    }

    /**
     * @param string $format
     * @param bool $timestamp
     * @param bool $statical
     * @param null $assign
     * @return string
     */
    static public function date($format = '', $timestamp = false, $statical = false, $assign = null)
    {
        static $localeDateFormat = null, $localeTimeFormat = null;

        $args = func_get_args();

        if (!class_exists('Locales', false))
        {
            if ( isset( $args[ 2 ] ) && ( is_numeric( $args[ 2 ] ) || is_int( $args[ 2 ] ) ) )
            {
                if ( $localeDateFormat === null )
                {
                    $localeDateFormat = 'd.m.Y';
                }

                if ( $localeTimeFormat === null )
                {
                    $localeTimeFormat = 'H:i:s';
                }

                $dateFormat = ( is_string( $args[ 0 ] ) && $args[ 0 ] != '' ? $args[ 0 ] : $localeDateFormat);
                $timeFormat = ( is_string( $args[ 1 ] ) && $args[ 1 ] != '' ? $args[ 1 ] : $localeTimeFormat);

                return date($dateFormat .' '. $timeFormat, $args[ 2 ]);
            }

            $numericTimestamp = false;
            if ( preg_match( '/^\d+?$/', trim( $timestamp ) ) )
            {
                $numericTimestamp = true;
            }

            if ( $format === 'monthname' )
            {
                return date('F', ( $numericTimestamp ? $timestamp : strtotime($timestamp) ) );
            }

            if ( $format === 'dayname' )
            {
                return date('D',  ( $numericTimestamp ? $timestamp : strtotime($timestamp) ));
            }

            if ( preg_match( '/^\d{4,4}-\d{2,2}-\d{2,2}/', trim( $timestamp ) ) == 1 )
            {
                $timestamp = Compiler_Library::convertSqlDatetime( $timestamp );
            }

            if ( $timestamp === false )
            {
                $timestamp = time();
            }

            if ( (int)$timestamp > 9999 )
            {
                return date( $format, ( $numericTimestamp ? $timestamp : strtotime($timestamp) ) );
            }

            return '-';

        }









        if ( isset( $args[ 2 ] ) && ( is_numeric( $args[ 2 ] ) || is_int( $args[ 2 ] ) ) )
        {
            if ( self::$date === null )
            {
                self::$date = new Date();
            }

            if ( $localeDateFormat === null )
            {
                $localeDateFormat = Locales::getConfig( 'datetime_format' );
            }

            if ( $localeTimeFormat === null )
            {
                $localeTimeFormat = Locales::getConfig( 'timeformat' );
            }


            $dateFormat = ( is_string( $args[ 0 ] ) && $args[ 0 ] != '' ? $args[ 0 ] : ( $localeDateFormat ? $localeDateFormat : Settings::get( 'dateformat', 'd.m.Y, H:i' ) ) );
            $timeFormat = ( is_string( $args[ 1 ] ) && $args[ 1 ] != '' ? $args[ 1 ] : ( $localeTimeFormat ? $localeTimeFormat : Settings::get( 'timeformat', 'H:i:s' ) ) );
            $useNames   = ( is_bool( $args[ 3 ] ) ? $args[ 3 ] : false );

            return self::$date->formatPostDate( $args[ 2 ], $dateFormat, $timeFormat, $useNames );
        }


        $numericTimestamp = false;
        if ( preg_match( '/^\d+?$/', trim( $timestamp ) ) )
        {
            $numericTimestamp = true;
        }

        if ( $format === 'monthname' )
        {
            return Locales::getMonthName( ( $numericTimestamp ? $timestamp : (string)$timestamp ), $statical );
        }

        if ( $format === 'dayname' )
        {
            return Locales::getDayName( ( $numericTimestamp ? $timestamp : (string)$timestamp ), $statical );
        }

        if ( preg_match( '/^\d{4,4}-\d{2,2}-\d{2,2}/', trim( $timestamp ) ) == 1 )
        {
            $timestamp = Compiler_Library::convertSqlDatetime( $timestamp );
        }

        if ( $timestamp === false )
        {
            $timestamp = time();
        }

        if ( (int)$timestamp > 9999 )
        {
            return Locales::formatedDate( $format, ( $numericTimestamp ? $timestamp : (string)$timestamp ) );
        }

        return '-';
    }


    /**
     * @param string $name default is null an will return the tokenhash from key "token"
     * @return string
     */
    public static function gettoken($name = null)
    {

        if ( $name == null )
        {
            return Csrf::generateCSRF( 'token' );
        }
        else
        {
            return Csrf::generateCSRF( $name );
        }


    }

    /**
     *
     * @param string $search
     * @param string $str
     * @return string
     */
    static public function match($search, $str = '')
    {

        if ( strpos( $search, '#' ) !== false )
        {
            $search = str_replace( '#', '\#', $search );
        }

        return preg_match( '#' . $search . '#is', $str );
    }

    /**
     *
     * @param string $search
     * @param string $replace
     * @param string $str
     * @return string
     */
    static public function replace($search, $replace = '', $str = '')
    {

        if ( strpos( $search, '#' ) !== false )
        {
            $search = str_replace( '#', '\#', $search );
        }

        return preg_replace( '#' . $search . '#is', $replace, $str );
    }

    /**
     * Returns the first non-empty argument.
     *
     * @static
     * @param mixed ... The arguments.
     * @return mixed The first non-empty argument
     */
    static public function firstof()
    {

        $args = func_get_args();
        $cnt  = sizeof( $args );
        for ( $i = 0; $i < $cnt; $i++ )
        {
            if ( !empty( $args[ $i ] ) )
            {
                return $args[ $i ];
            }
        }
    }

    /**
     * Returns true, if the container contains the specified value.
     *
     * @static
     * @param string $item The container
     * @param mixed $value The value
     * @return True, if the value exists in the container.
     */
    static public function contains($item, $value)
    {

        if ( is_array( $item ) )
        {
            return in_array( $value, $item );
        }
        elseif ( $item === $value )
        {
            return true;
        }

        return false;
    }


    /**
     * Pads the string or a container.
     *
     * @static
     * @param string|container $string The string to pad.
     * @param int $length The pad length
     * @param string $padString The string used for padding.
     * @param string $type The padding type.
     * @return string|container The modified string.
     */
    static public function pad($string, $length, $padString = ' ', $type = 'right')
    {

        if ( !is_scalar( $padString ) )
        {
            $padString = ' ';
        }

        switch ( (string)$type )
        {
            case 'left':
                $type = STR_PAD_LEFT;
                break;
            case 'both':
                $type = STR_PAD_BOTH;
                break;
            default:
                $type = STR_PAD_RIGHT;
        }

        if ( self::isContainer( $string ) )
        {
            $list = array();
            foreach ( $string as $idx => $str )
            {
                if ( is_scalar( $str ) )
                {
                    $list[ $idx ] = str_pad( (string)$str, (int)$length, (string)$padString, $type );
                }
            }

            return $list;
        }

        return str_pad( (string)$string, (int)$length, (string)$padString, $type );
    }

    /**
     * Changes the newline characters to the BR tag.
     *
     * @static
     * @param String $item The string or the container.
     * @return String The modified string
     */
    static public function nl2br($item)
    {

        if ( is_string( $item ) )
        {
            return nl2br( $item );
        }

        return $item;
    }

    /**
     * @param       $value
     * @param array $list
     * @return bool
     */
    static public function inlist($value, $list = array())
    {

        if ( !is_array( $list ) )
        {
            return false;
        }

        return in_array( $value, $list );
    }

    /**
     * Returns the next element of the specified list of items.
     *
     * @return Mixed
     */
    public static function cycle()
    {

        $items = func_get_args();
        if ( is_array( $items[ 0 ] ) )
        {
            $items = $items[ 0 ];
        }
        elseif ( is_string( $items[ 1 ] ) )
        {
            $items = explode( ',', $items[ 1 ] );
        }
        elseif ( is_string( $items[ 0 ] ) )
        {
            $items = explode( ',', $items[ 0 ] );
        }

        return ( $items[ ( self::$_cycleIterator++ ) % sizeof( $items ) ] );
    }

    /**
     *
     */
    static public function group($var, $default = null)
    {

        $var = str_replace( ':', '/', $var );

        if ( $default !== null )
        {

            switch ( strtolower( $default ) )
            {
                case 'false':
                case 'true':
                case 'null':
                    return User::getPerm( $var, $default );

                    break;
                default:

                    return User::getPerm( $var, $default );

                    break;
            }
        }

        return User::getPerm( $var );
    }

    /**
     * @return bool
     */
    public static function hasperm()
    {
        $items = func_get_args();
        return User::hasPerm( str_replace( array(':', '.'), '/', $items[ 0 ] ), ( isset( $items[ 1 ] ) ? $items[ 1 ] : false ) );
    }

    /**
     * @param        $file
     * @param null $chain
     * @param string $cache
     * @return bool|null
     */
    public static function photo($file, $chain = null, $cache = 'thumbnails')
    {

        if ( substr( $file, 0, 1 ) == "'" || substr( $file, 0, 1 ) == '"' )
        {
            $file = substr( $file, 1 );
        }

        if ( substr( $file, -1 ) == "'" || substr( $file, -1 ) == '"' )
        {
            $file = substr( $file, 0, -1 );
        }

        if ( substr( $chain, 0, 1 ) == "'" || substr( $chain, 0, 1 ) == '"' )
        {
            $chain = substr( $chain, 1 );
        }

        if ( substr( $chain, -1 ) == "'" || substr( $chain, -1 ) == '"' )
        {
            $chain = substr( $chain, 0, -1 );
        }

        if ( $chain == '' || $chain === null )
        {
            $imgchain = 'thumbnail';
        }
        else
        {
            $imgchain = $chain;
        }

        if ( substr( $cache, 0, 1 ) == "'" || substr( $cache, 0, 1 ) == '"' )
        {
            $cache = substr( $cache, 1 );
        }

        if ( substr( $cache, -1 ) == "'" || substr( $cache, -1 ) == '"' )
        {
            $cache = substr( $cache, 0, -1 );
        }


        return Api::loadImg( $file, $imgchain, $cache );
    }

    /**
     *
     * @param string $regex
     * @param string $string
     * @return bool
     */
    public static function find($regex, $string)
    {
        if ( preg_match( '#' . preg_quote( $regex, '#' ) . '#is', $string ) )
        {
            return true;
        }

        return false;
    }


    /**
     * @param      $name        the fieldname
     * @param bool $emptyCheck if true when empty will return true if not empty the return false,
     *                          default value of the field (default is null).
     * @return bool|mixed|null
     */
    public static function field($name, $emptyCheck = null)
    {

        if ( $emptyCheck === true )
        {
            if ( CustomField::isEmpty( $name ) )
            {
                return false;
            }

            return true;
        }

        return CustomField::get( $name, $emptyCheck );
    }

    /**
     *
     * @param string $mode
     * @param string $delimiter default is &
     * @return string
     */
    public static function requeststring($mode, $delimiter = '&')
    {

        $mode = strtoupper( $mode );

        switch ( $mode )
        {
            case 'POST':
                $req = HTTP::post();
                break;

            case 'GET':
            default:
                $req = HTTP::get();
                break;
        }

        $str = '';
        foreach ( $req as $k => $v )
        {
            if ( $k !== '' && !is_array( $v ) )
            {
                $str .= ( $str !== '' ? $delimiter : '' ) . $k . "=" . $v;
            }
        }

        return $str;
    }

    /**
     * @param string $value
     * @param string $key
     * @param bool $noImages
     * @param null $length
     * @param null $allowedTags
     * @param null $ending
     * @return string
     */
    public static function &seemode($value, $key, $noImages = false, $length = null, $allowedTags = null, $ending = null)
    {

        $args    = func_get_args();
        $numArgs = count( $args );
        $lastArg = $args[ $numArgs - 1 ];

        if ( is_numeric( $lastArg ) )
        {
            $key = $lastArg . '-' . $key;

            if ( $numArgs == 3 )
            {
                $noImages = false; // reset to default
            }

            if ( $numArgs == 4 )
            {
                $length = null; // reset to default
            }

            if ( $numArgs == 5 )
            {
                $allowedTags = null; // reset to default
            }

            if ( $numArgs == 6 )
            {
                $ending = null; // reset to default
            }
        }


        if ( Session::get( 'seemode', false ) )
        {
            return '<span class="seemode-var" id="seemode-var-' . $key . '" noimages="' . $noImages . '" length="' . $length . '" allowedtags="' . $allowedTags . '">' . $value . '</span>';
        }
        else
        {
            return $value;
        }
    }

    /**
     * @param string $key
     * @param null $default
     * @return null|mixed
     */
    public static function user($key, $default = null)
    {

        $val = User::get( $key );

        return $val !== null ? $val : $default;
    }


    /**
     * create a string from giving args
     * @return string
     */
    public static function str()
    {
        return implode('', func_get_args());
    }

    /**
     *
     * @return string
     */
    public static function trans()
    {

        $args   = func_get_args();
        $string = array_shift( $args );

        // patch from TemplateCompiler_Parser
        $string = preg_replace( '#\slt\s(/?)([^gt])\sgt\s#', '<$1$2>', $string );

        if ( ( $counted = count( $args ) ) > 0 )
        {
            $lastArg = array_pop( $args );

            // escape
            if ( substr( $lastArg, 0, 2 ) === 'e:' )
            {
                $counted = $counted - 1;
                $string  = self::escape( $string, substr( $lastArg, 2 ) );
            }
            else
            {
                $args[ ] = (string)$lastArg;
            }
        }

        if ( count( $args ) > 0 )
        {
            return vsprintf( trans( $string ), $args );
        }

        return trans( $string );
    }

    /**
     *
     * @return mixed
     */
    public static function iif()
    {

        $args       = func_get_args();
        $expression = array_shift( $args );
        if ( $expression )
        {
            return ( isset( $args[ 0 ] ) ? '' . $args[ 0 ] : null );
        }

        return ( isset( $args[ 1 ] ) ? '' . $args[ 1 ] : null );
    }

    /**
     *
     * @return string
     */
    public static function cfg()
    {

        $args   = func_get_args();
        $string = array_shift( $args );


        if ( isset( $args[ 0 ] ) )
        {
            return Settings::get( $string, $args[ 0 ] );
        }

        return Settings::get( $string );
    }

    /**
     *
     * if param $key only then set get session by key
     * if param $key and $value then set the session
     *
     * @see      Session
     * @uses     Session::get()
     * @uses     Session::save()
     * @internal param string $key
     * @internal param mixed $value
     * @return mixed
     */
    public static function session()
    {

        $args   = func_get_args();
        $string = array_shift( $args );

        if ( isset( $args[ 0 ] ) )
        {
            return Session::save( $string, $args[ 0 ] );
        }

        return Session::get( $string );
    }

    /**
     *
     * @return string
     */
    public static function escape()
    {
        $args  = func_get_args();
        $value = $args[ 0 ];


        if (!trim($value)) {
            return $value;
        }

        $format = 'html';
        if ( trim($args[ 1 ]) )
        {
            $format = strtolower( $args[ 1 ] );
        }

        $charset = false;
        if ( isset( $args[ 2 ] ) && $args[ 2 ] != '' )
        {
            $charset = strtolower( $args[ 2 ] );
        }

        $charset = ( $charset != '' ? $charset : 'utf-8' );

        switch ( $format )
        {
            case 'number':
                return (string)"$value";
                break;
            case 'html':


                if ( !Strings::isUTF8( $value ) )
                {
                    $value = Strings::mbConvertTo( $value, 'UTF-8' );
                }

                return Strings::fixAmpsan( htmlspecialchars( Strings::utf8ToEntities( $value ) ) );
                break;

            case 'htmlall':
                $value = Strings::unhtmlSpecialchars( (string)$value );

                if ( !Strings::isUTF8( $value ) )
                {
                    $value = Strings::mbConvertTo( $value, 'UTF-8' );
                }

                return htmlentities( $value, ENT_QUOTES, $charset );
                break;

            case 'url':
                return rawurlencode( (string)$value );
                break;

            case 'urlpathinfo':
                return str_replace( '%2F', '/', rawurlencode( (string)$value ) );
                break;
            case 'quotes':

                return strtr( (string)$value, array(
                    "'" => "&#039;",
                    '"' => '&quot;',
                ) );
                break;


            case 'hex':
                $out = '';
                $cnt = strlen( (string)$value );
                for ( $i = 0; $i < $cnt; ++$i )
                {
                    $out .= '%' . bin2hex( (string)$value[ $i ] );
                }

                return $out;
                break;

            case 'hexentity':
                $out = '';
                $cnt = strlen( (string)$value );
                for ( $i = 0; $i < $cnt; ++$i)
                {
                    $out .= '&#x' . bin2hex( (string)$value[ $i ] ) . ';';
                }

                return $out;
                break;

            case 'javascript':
                return strtr( (string)$value, array(
                    '\\' => '\\\\',
                    "'"  => "\\'",
                    '"'  => '\\"',
                    "\r" => '\\r',
                    "\n" => '\\n',
                    '</' => '<\/'
                ) );
                break;

            case 'mail':
                return str_replace( array(
                    '@',
                    '.'
                ), array(
                    '&nbsp;(AT)&nbsp;',
                    '&nbsp;(DOT)&nbsp;'
                ), (string)$value );
                break;
        }

        return $value;
    }

    /**
     *
     * @param        string /array $tags
     * @param string $url

    public static function cloud ( $tags, $url )
     *                      {
     *
     * if ( !is_array($tags) )
     * {
     * $tags = explode(',', $tags);
     * }
     *
     * $str = array ();
     * foreach ( $tags as $k => $value )
     * {
     * // $str[] =
     * }
     * }*/

    /**
     * Tries to break the string every specified number of characters.
     *
     * @param String $string The input string
     * @param Integer $width The width of a line
     * @param String $break The break string
     * @param Boolean $cut Whether to cut too long words
     * @return String
     */
    static public function wordwrap($string, $width = 80, $break = '<br />', $cut = null)
    {

        if ( !is_null( $break ) )
        {
            $break = str_replace( array(
                '\\n',
                '\\r',
                '\\t',
                '\\\\'
            ), array(
                "\n",
                "\r",
                "\t",
                '\\'
            ), $break );
        }

        return wordwrap( $string, $width, $break, true );
    }

    /**
     * Holds the allowed function, characters, operators and constants
     */
    private static $allowed = array(
        '0',
        '1',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
        '+',
        '-',
        '/',
        '*',
        '.',
        ' ',
        '<<',
        '>>',
        '%',
        '&',
        '^',
        '|',
        '~',
        'abs(',
        'ceil(',
        'floor(',
        'exp(',
        'log10(',
        'cos(',
        'sin(',
        'sqrt(',
        'tan(',
        'M_PI',
        'INF',
        'M_E',
    );

    /**
     * Holds the functions that can accept multiple arguments
     */
    private static $funcs = array(
        'round(',
        'log(',
        'pow(',
        'max(',
        'min(',
        'rand(',
    );

    /**
     * @param      $equation
     * @param null $format
     * @return mixed
     * @todo validate math function
     */
    static public function math($equation, $format = null)
    {

        return $equation;
    }

    /**
     *
     * @param integer $number
     * @return string
     */
    static public function sizeformat($number)
    {

        return Tools::formatSize( $number );
    }

    /**
     * Formats the input number to look nice in the text. If the extra arguments
     * are not present, they are taken from configuration.
     *
     * @param Number $number The input number
     * @param Integer $d1 The number of decimals
     * @param String $d2 The decimal separator
     * @param String $d3 The thousand separator
     * @return String
     */
    static public function number($number, $d1 = null, $d2 = null, $d3 = null)
    {

        $t  = Beast_Registry::get( 'Template' );
        $d1 = ( $d1 === null ? $t->numberDecimals : $d1 );
        $d2 = ( $d2 === null ? $t->numberDecPoint : $d2 );
        $d3 = ( $d3 === null ? $t->numberThousandSep : $d3 );

        return number_format( $number, $d1, $d2, $d3 );
    }

    /**
     * Returns the absolute value of a number.
     *
     * @param Number|Container $items The input number of container of numbers
     * @return Number|Container
     */
    static public function absolute($items)
    {

        return abs( $items );
    }

    /**
     * Sums all the numbers in the specified container.
     *
     * @param Container $items The container of numbers.
     * @return Number
     */
    static public function sum($items)
    {

        if ( self::isContainer( $items ) )
        {
            $sum = 0;
            foreach ( $items as $item )
            {
                if ( !self::isContainer( $item ) )
                {
                    $sum += $item;
                }
            }

            return $sum;
        }

        return null;
    }

    /**
     * Returns the average value of the numbers in a container.
     *
     * @param Container $items The container of numbers
     * @return Number
     */
    static public function average($items)
    {

        if ( self::isContainer( $items ) )
        {
            $sum = 0;
            $cnt = 0;
            foreach ( $items as $item )
            {
                if ( !self::isContainer( $item ) && !is_null( $item ) )
                {
                    $sum += $item;
                    $cnt++;
                }
            }
            if ( $cnt > 0 )
            {
                return $sum / $cnt;
            }
        }

        return null;
    }

    /**
     * Replaces the lowercase characters in $item with the uppercase.
     *
     * @static
     * @param String|Container $item The string or the container.
     * @return String|Container The modified string
     */
    static public function upper($item)
    {
        return strtoupper( $item );
    }

    /**
     * Replaces the uppercase characters in $item with the lowercase.
     *
     * @param string $item The string.
     * @return string Container The modified string
     */
    static public function lower($item)
    {
        return strtolower( $item );
    }

    /**
     * Returns true, if the specified string is a valid URL.
     *
     * @static
     * @param String $address The string to test.
     * @return Boolean True, if the specified string is a valid URL.
     */
    static public function isUrl($address)
    {
        return filter_var( $address, FILTER_VALIDATE_URL ) !== false;
    }

    /**
     * Returns true, if the specified URL or file path points to the
     * image file. The recognition bases on the file extension and
     * currently recognizes JPG, PNG, GIF, SVG and BMP.
     *
     * @static
     * @param String $address The URL or filesystem path to test.
     * @return Boolean True, if the specified address points to an image.
     */
    static public function isImage($address)
    {

        $result = @parse_url( $address );
        if ( is_array( $result ) )
        {
            if ( isset( $result[ 'path' ] ) )
            {
                // Try to obtain the file extension
                if ( ( $id = strrpos( $result[ 'path' ], '.' ) ) !== false )
                {
                    if ( in_array( substr( $result[ 'path' ], $id + 1, 3 ), array(
                            'jpg',
                            'png',
                            'gif',
                            'svg',
                            'bmp'
                        ) ) || in_array( substr( $result[ 'path' ], $id + 1, 4 ), array(
                            'jpeg'
                        ) )
                    )
                    {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Creates an entity for the specified string. If used with the 'u:' modifier,
     * it allows to display the entities in the output document.
     *
     * @static
     * @param String $name A valid entity name.
     * @throws Compiler_Exception
     * @return String
     */
    static public function entity($name)
    {

        if ( !preg_match( '/^(([a-zA-Z\_\:]{1}[a-zA-Z0-9\_\:\-\.]*)|(\#((x[a-fA-F0-9]+)|([0-9]+))))$/', $name ) )
        {
            throw new Compiler_Exception( $name . ' is not a valid entity name.' );
        }

        return '&' . $name . ';';
    }

    /**
     * Turns URL-s into clickable links.
     *
     * @static
     * @param string $text The text to parse.
     * @param string $class The optional CSS class.
     * @param string $target The target.
     * @return string The parsed text.
     */
    static public function autoLink($text, $class = null, $target = '_blank')
    {

        $extra = '';
        if ( $class )
        {
            $extra .= ' class="' . (string)htmlspecialchars( $class ) . '"';
        }

        if ( $target )
        {
            $extra .= ' target="' . (string)htmlspecialchars( $target ) . '"';
        }

        return str_replace( array(
            '<a href="www.',
            '<a href="ftp.'
        ), array(
            '<a href="http://www.',
            '<a href="ftp://ftp.'
        ), preg_replace( '/(((http|https|ftp|ftps|gopher)\:\/\/)|(www\.)|(ftp\.))([a-zA-Z0-9\_\-]+\@)?(([0-9a-fA-F\:]{6,39})|(([a-zA-Z0-9\-\_]+\.)*[a-zA-Z0-9\-\_]+(\:[0-9]{1,5})?)|(\[[0-9a-fA-F\:]{6,39}\]\:[0-9]{1,5}))(\/[a-zA-Z0-9\_\-\&\;\?\/\.\=\+\#]*)?/is', '<a href="$0"' . $extra . '>$0</a>', htmlspecialchars( $text ) ) );
    }

    /**
     *
     * @param string $value
     * @param integer $length default is 80
     * @param string $allowedHTMLTags default is null
     * @param string $etc default is null
     * @return string
     */
    static public function truncate($value = '', $length = 80, $allowedHTMLTags = null, $etc = null)
    {

        return Strings::trimHtml( $value, $length, $allowedHTMLTags, $etc );
    }

    /**
     *
     * @param string $alias
     * @param bool $suffix
     * @param string $title
     * @param string $contenttype
     * @return string
     */
    static public function urlrewrite($alias = '', $suffix = false, $title = '', $contenttype = '')
    {

        if ( substr( $alias, 0, 1 ) == "'" || substr( $alias, 0, 1 ) == '"' )
        {
            $alias = substr( $alias, 1 );
        }
        if ( substr( $alias, -1 ) == "'" || substr( $alias, -1 ) == '"' )
        {
            $alias = substr( $alias, 0, -1 );
        }

        if ( substr( $title, 0, 1 ) == "'" || substr( $title, 0, 1 ) == '"' )
        {
            $title = substr( $title, 1 );
        }
        if ( substr( $title, -1 ) == "'" || substr( $title, -1 ) == '"' )
        {
            $title = substr( $title, 0, -1 );
        }

        if ( !$title )
        {
            return $title;
        }

        $rewrite = '';
        if ( $suffix === false )
        {
            $rewrite = trim( Url::makeRw( (string)$alias, false, $title, (string)$contenttype ) );
        }
        else
        {
            $rewrite = trim( Url::makeRw( (string)$alias, true, $title, (string)$contenttype ) );
        }

        return $rewrite;
    }

    /**
     * will parse bbcode for the giving string
     *
     * @param string $str
     * @param bool $removebbcode
     * @param string $useMode default is "commentbbcodes"
     * @return string
     */
    static public function parsetext($str, $removebbcode = false, $useMode = 'commentbbcodes')
    {

        if ( $removebbcode )
        {
            return BBCode::removeBBCode( $str );
        }
        else
        {
            BBCode::setBBcodeHandler( $useMode );

            return BBCode::toXHTML( $str );
        }
    }

    /**
     *
     * @param string $str
     * @param bool $addExtension
     * @return string
     */
    static public function suggest($str = '', $addExtension = false)
    {

        return Compiler_Library::suggest( $str, $addExtension );
    }


    /**
     * @param $code
     * @param $domain
     * @return string
     * @throws Compiler_Exception
     */
    static public function googleanalytics($code, $domain)
    {
        if ( empty( $code ) )
        {
            throw new Compiler_Exception( 'The first parameter must contained a valid Google Analytics UA code.' );
        }

        if ( !empty( $domain ) )
        {
            $domain = "_gaq.push(['_setDomainName', '" . $domain . "']);";
        }

        return "
<script>
	var _gaq = _gaq || [];
	_gaq.push(['_setAccount', '{$code}']);
	{$domain}
	_gaq.push(['_setAllowLinker', true]);
	_gaq.push(['_trackPageview']);

	(function() {
	var ga = document.createElement('script'); ga.type = 'text/javascript';
	  ga.async = true;
	ga.src = ('https:' == document.location.protocol ? 'https://ssl' :
	  'http://www') + '.google-analytics.com/ga.js';
	var s = document.getElementsByTagName('script')[0];
	  s.parentNode.insertBefore(ga, s);
	})();
</script>
";
    }

    /**
     * @param $value
     * @return string
     */
    static public function formatsize($value) {
        return Compiler_Library::formatSize($value);
    }


    /**
     * @param $value
     * @param bool $count_spaces
     * @return int
     */
    static public function countchars( $value, $count_spaces = false )
    {
        if ($count_spaces === false) {
            return preg_match_all('#[^\s\pZ]#u', $value, $tmp);
        }
        return mb_strlen($value, self::$charset);
    }

    /**
     * @param $value
     * @return int
     */
    static public function countwords( $value )
    {
        return preg_match_all(strcasecmp(self::$charset, 'utf-8')===0 ? '#[\w\pL]+#u' : '#\w+#', $value, $tmp);

    }

    /**
     * @param $value
     * @return int
     */
    static public function countsentences( $value )
    {
        return preg_match_all('/[^\s](\.|\!|\?)(?!\w)/', $value, $tmp);

    }

}