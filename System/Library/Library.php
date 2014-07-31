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
 * @package      Library
 * @version      3.0.0 Beta
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Library.php
 */
class Library
{

    /**
     * @var bool
     */
    private static $tplParserLoad = false;

    /**
     * @var string
     */
    private static $cryptkey = 'ZFhg<gs>1!ahgSASASögfds#*->';

    /**
     * @var array
     */
    private static $S = array();

    /**
     * @var bool
     */
    public static $controller = false;

    /**
     * @var bool
     */
    private static $useSyntaxKeywords = false;

    /**
     * @var null
     */
    private static $dbUpdateQuerys = null;

    /**
     * @var null
     */
    private static $router = null;

    /**
     * @var array
     */
    private static $navigation = array();

    /**
     * @var null
     */
    private static $activePage = null;

    /**
     * @var null
     */
    // Call from Controller
    private static $imageChains = null;

    /**
     * @var array
     */
    public static $protectedEmails = array();

    /**
     * @var null
     */
    public static $routerMaps = null;

    /**
     * @var array
     */
    protected static $instances = array();

    /**
     * @var null
     */
    public static $versionRecords = null;

    // use from backend
    protected static $oldErrorReporting = array();

    private static $_finfo = null;

    /**
     *
     * @var array
     */
    public static $mimeTypes = array(
        // applications
        'ai'      => 'application/postscript',
        'eps'     => 'application/postscript',
        'exe'     => 'application/x-executable',
        'doc'     => 'application/vnd.ms-word',
        'xls'     => 'application/vnd.ms-excel',
        'ppt'     => 'application/vnd.ms-powerpoint',
        'pps'     => 'application/vnd.ms-powerpoint',
        'pdf'     => 'application/pdf',
        //       'xml'     => 'application/xml',
        'swf'     => 'application/x-shockwave-flash',
        'torrent' => 'application/x-bittorrent',
        'jar'     => 'application/x-jar',
        // open office (finfo detect as application/zip)
        'odt'     => 'application/vnd.oasis.opendocument.text',
        'ott'     => 'application/vnd.oasis.opendocument.text-template',
        'oth'     => 'application/vnd.oasis.opendocument.text-web',
        'odm'     => 'application/vnd.oasis.opendocument.text-master',
        'odg'     => 'application/vnd.oasis.opendocument.graphics',
        'otg'     => 'application/vnd.oasis.opendocument.graphics-template',
        'odp'     => 'application/vnd.oasis.opendocument.presentation',
        'otp'     => 'application/vnd.oasis.opendocument.presentation-template',
        'ods'     => 'application/vnd.oasis.opendocument.spreadsheet',
        'ots'     => 'application/vnd.oasis.opendocument.spreadsheet-template',
        'odc'     => 'application/vnd.oasis.opendocument.chart',
        'odf'     => 'application/vnd.oasis.opendocument.formula',
        'odb'     => 'application/vnd.oasis.opendocument.database',
        'odi'     => 'application/vnd.oasis.opendocument.image',
        'oxt'     => 'application/vnd.openofficeorg.extension',
        // MS office 2007 (finfo detect as application/zip)
        'docx'    => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'docm'    => 'application/vnd.ms-word.document.macroEnabled.12',
        'dotx'    => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
        'dotm'    => 'application/vnd.ms-word.template.macroEnabled.12',
        'xlsx'    => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xlsm'    => 'application/vnd.ms-excel.sheet.macroEnabled.12',
        'xltx'    => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
        'xltm'    => 'application/vnd.ms-excel.template.macroEnabled.12',
        'xlsb'    => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
        'xlam'    => 'application/vnd.ms-excel.addin.macroEnabled.12',
        'pptx'    => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'pptm'    => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
        'ppsx'    => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
        'ppsm'    => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
        'potx'    => 'application/vnd.openxmlformats-officedocument.presentationml.template',
        'potm'    => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
        'ppam'    => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
        'sldx'    => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
        'sldm'    => 'application/vnd.ms-powerpoint.slide.macroEnabled.12',
        // archives
        'gz'      => 'application/x-gzip',
        'tgz'     => 'application/x-gzip',
        'bz'      => 'application/x-bzip2',
        'bz2'     => 'application/x-bzip2',
        'tbz'     => 'application/x-bzip2',
        'zip'     => 'application/zip',
        'rar'     => 'application/x-rar',
        'tar'     => 'application/x-tar',
        '7z'      => 'application/x-7z-compressed',
        // texts
        'txt'     => 'text/plain',
        'php'     => 'text/x-php',
        'html'    => 'text/html',
        'htm'     => 'text/html',
        'js'      => 'text/javascript',
        'css'     => 'text/css',
        'rtf'     => 'text/rtf',
        'rtfd'    => 'text/rtfd',
        'py'      => 'text/x-python',
        'java'    => 'text/x-java-source',
        'rb'      => 'text/x-ruby',
        'sh'      => 'text/x-shellscript',
        'pl'      => 'text/x-perl',
        'xml'     => 'text/xml',
        'sql'     => 'text/x-sql',
        'c'       => 'text/x-csrc',
        'h'       => 'text/x-chdr',
        'cpp'     => 'text/x-c++src',
        'hh'      => 'text/x-c++hdr',
        'log'     => 'text/plain',
        'csv'     => 'text/x-comma-separated-values',
        // images
        'bmp'     => 'image/x-ms-bmp',
        'jpg'     => 'image/jpeg',
        'jpeg'    => 'image/jpeg',
        'gif'     => 'image/gif',
        'png'     => 'image/png',
        'tif'     => 'image/tiff',
        'tiff'    => 'image/tiff',
        'tga'     => 'image/x-targa',
        'psd'     => 'image/vnd.adobe.photoshop',
        'ai'      => 'image/vnd.adobe.photoshop',
        'xbm'     => 'image/xbm',
        'pxm'     => 'image/pxm',
        //audio
        'mp3'     => 'audio/mpeg',
        'mid'     => 'audio/midi',
        //  'ogg'     => 'audio/ogg',
        'oga'     => 'audio/ogg',
        'm4a'     => 'audio/x-m4a',
        'wav'     => 'audio/wav',
        'wma'     => 'audio/x-ms-wma',
        // video
        'avi'     => 'video/x-msvideo',
        'dv'      => 'video/x-dv',
        'mp4'     => 'video/mp4',
        'mpeg'    => 'video/mpeg',
        'mpg'     => 'video/mpeg',
        'mov'     => 'video/quicktime',
        'wm'      => 'video/x-ms-wmv',
        'flv'     => 'video/x-flv',
        'mkv'     => 'video/x-matroska',
        'webm'    => 'video/webm',
        'ogg'     => 'video/ogg',
        'ogv'     => 'video/ogg',
        'ogm'     => 'video/ogg'
    );

    /**
     * @param $name
     * @return mixed
     */
    static function getInstance($name)
    {

        if ( isset( self::$instances[ $name ] ) )
        {
            return self::$instances[ $name ];
        }

        self::$instances[ $name ] = new $name();

        return self::$instances[ $name ];
    }


    /**
     * @param string $name
     */
    public static function isBlacklistedUsername($name)
    {
        static $names;


        if ( $name && is_file( DATA_PATH . 'system/spamprotection/badusername.txt' ) )
        {

            $ret = false;

            $fd = fopen( DATA_PATH . 'system/spamprotection/badusername.txt', 'rb' );
            while ( ( $line = fgets( $fd ) ) !== false )
            {

                $l = trim( $line );
                if ( $l && $l == $name )
                {
                    $ret = true;
                    break;
                }
            }
            fclose( $fd );


            return $ret;
        }

        return false;
    }

    /**
     * @param string $email
     */
    public static function isBlacklistedEmail($email)
    {

    }


    /**
     * Computes the division of i1 by i2. If either i1 or i2 are not number, or if i2 has a value of zero
     * we return 0 to avoid the division by zero.
     *
     * @param number $i1
     * @param number $i2
     * @return number The result of the division or zero
     */
    static public function secureDiv($i1, $i2)
    {

        if ( is_numeric( $i1 ) && is_numeric( $i2 ) && floatval( $i2 ) != 0 )
        {
            return $i1 / $i2;
        }

        return 0;
    }

    /**
     * @param string $typ
     * @return null
     */
    public static function getImageChain($typ = 'thumbnail')
    {

        self::loadChains();

        if ( isset( self::$imageChains[ $typ ] ) )
        {
            return self::$imageChains[ $typ ];
        }

        return null;

        // Error::raise('Chain Type `' . $typ . '` not exists!');
    }

    /**
     * @function    loadChains
     * Loads the image chains.
     */
    static function loadChains()
    {

        if ( is_null( self::$imageChains ) )
        {
            self::$imageChains = Cache::get( 'imagechains' );

            if ( is_null( self::$imageChains ) )
            {
                include_once Library::formatPath( DATA_PATH . 'system/image-chains.php' );


                $db     = Database::getInstance();
                $chains = $db->query( "SELECT t.*, s.* 
                                       FROM %tp%transform AS t 
                                       LEFT JOIN %tp%transform_steps AS s ON(s.t_id=t.id) 
                                       ORDER BY s.t_id, s.`order` ASC;" )->fetchAll();
                foreach ( $chains as $chain )
                {
                    if ( !empty( $chain[ 'type' ] ) )
                    {
                        $arr = @unserialize( $chain[ 'parameters' ] );
                        if ( is_array( $arr ) )
                        {
                            self::$imageChains[ $chain[ 'title' ] ][ ] = array(
                                $chain[ 'type' ],
                                $arr,
                                $chain[ 'id' ]
                            );
                        }
                    }
                    else
                    {
                        self::$imageChains[ $chain[ 'title' ] ] = array();
                    }
                }


                if ( isset( $imageChains ) && is_array( $imageChains ) )
                {
                    self::$imageChains = array_merge( $imageChains, self::$imageChains );
                }


                Cache::write( 'imagechains', self::$imageChains );
            }
        }
    }

    /**
     * @param null $arr
     */
    public static function addQuery($arr = null)
    {

        if ( is_array( $arr ) )
        {
            self::$dbUpdateQuerys[ $arr[ 'tbl' ] ][ ] = $arr[ 'sql' ];
        }
    }

    /**
     * @return null
     */
    public static function getQuerys()
    {

        if ( is_array( self::$dbUpdateQuerys ) )
        {
            return self::$dbUpdateQuerys;
        }

        return null;
    }

    /**
     * @return mixed
     */
    public static function getRules()
    {

        $maps = Cache::get( 'router-map', 'data' );
        if ( $maps == null )
        {
            $db   = Database::getInstance();
            $maps = $db->query( 'SELECT * FROM %tp%routermap WHERE published = 1 ORDER BY LENGTH(rule) DESC, controller, `action` ASC' )->fetchAll();
            Cache::write( 'router-map', $maps, 'data' );
        }

        return $maps;
    }

    /**
     * @return mixed
     */
    public static function getAbsoluteRequestURI()
    {

        return str_replace( str_replace( str_replace( '\\', '/', $_SERVER[ 'DOCUMENT_ROOT' ] ) . '/', '', ROOT_PATH ), '', HTTP::getClean( $_SERVER[ 'REQUEST_URI' ] ) );
    }

    /**
     * pretty print the contents of passed variables
     *
     * @internal param mixed $data
     * @return void
     * @access   public
     * @static
     * @return void
     */
    public static function dbg()
    {

        if ( Library::ajax() || IS_FLASH )
        {
            $output                   = array();
            $output[ 'success' ]      = false;
            $output[ 'debug' ]        = true;
            $args                     = func_get_args();
            $output[ 'data_display' ] = '';
            foreach ( $args as $arg )
            {
                $output[ 'data' ][ ] = $arg;
                $output[ 'data_display' ] .= '<pre>' . Library::encode( print_r( $arg, 1 ) ) . '</pre>';
            }
            echo Library::json( $output );
            die();
        }

        $trace  = $orig_trace = debug_backtrace();
        $caller = array_shift( $trace );

        while ( empty( $caller[ 'file' ] ) || realpath( Library::formatPath( $caller[ 'file' ] ) ) == realpath( Library::formatPath( __FILE__ ) ) )
        {
            $caller = array_shift( $trace );
        }

        $data[ 'pagepath' ]           = 'pages/' . SERVER_PAGE;
        $data[ 'backendImagePath' ]   = BACKEND_IMAGE_PATH;
        $data[ 'cfg' ]                = Settings::getAll();
        $data[ 'user' ]               = User::initUserData();
        $data[ 'user' ][ 'is_admin' ] = User::isAdmin();
        $data[ 'js_url' ]             = JS_URL;
        $data[ 'version' ]            = VERSION;
        $data[ 'message' ]            = $message;

        $out = '';

        $function = $caller[ 'function' ];
        $class    = isset( $caller[ 'class' ] ) ? $caller[ 'class' ] . '::' : '';
        $header   = $class . $function . '()';
        $header .= !empty( $caller[ 'file' ] ) ? ' @ ' . self::formatPath( $caller[ 'file' ] ) : '';
        $header .= !empty( $caller[ 'line' ] ) ? ', line ' . $caller[ 'line' ] : '';

        $out .= chr( 10 ) . '<div style="border: 1px solid #eee; margin: 5px;">' . chr( 10 );
        $out .= chr( 9 ) . '<div style="background-color: #eee; color: #333; font-family: Arial, Helv, Sans; font-size: 12px; padding: 3px;">';
        $out .= '&nbsp;' . $header . '</div>' . chr( 10 );

        $totalArgs = func_num_args();

        if ( $totalArgs )
        {
            $args = func_get_args();

            if ( $totalArgs > 1 && gettype( $args[ 0 ] ) == 'boolean' )
            {
                $returnOnly = true;
                $args       = array_shift( $args );
            }


            foreach ( $args as $arg )
            {
                $out .= chr( 9 ) . '<div style="color: #333; font-family: Arial, Helv, Sans; font-size: 12px; padding: 3px;">' . chr( 10 );
                $dump = Library::prettyPrint( $arg );
                $out .= $dump;
            }
            $out .= chr( 9 ) . '</div>' . chr( 10 );
        }
        else
        {
            $out .= chr( 9 ) . '<div style="color: #333; font-family: Arial, Helv, Sans; font-size: 12px; padding: 3px;">' . chr( 10 );
            $out .= 'No parameters passed, nothing to ' . $function . '().' . chr( 10 );
            $out .= chr( 9 ) . '</div>' . chr( 10 );
        }


        $out .= '</div>' . chr( 10 ) . chr( 10 );


        if ( !$returnOnly )
        {
            echo $out;
        }

        return $out;
    }

    /**
     * pretty print the contents of passed variables
     *
     * @internal param mixed $data
     * @return void
     * @access   public
     * @static
     * @return void
     */
    public static function dbgOutputCache()
    {

        if ( Library::ajax() || IS_FLASH )
        {
            $output                   = array();
            $output[ 'success' ]      = false;
            $output[ 'debug' ]        = true;
            $args                     = func_get_args();
            $output[ 'data_display' ] = '';
            foreach ( $args as $arg )
            {
                $output[ 'data' ][ ] = $arg;
                $output[ 'data_display' ] .= '<pre>' . Library::encode( print_r( $arg, 1 ) ) . '</pre>';
            }
            echo Library::json( $output );
            die();
        }

        $trace  = $orig_trace = debug_backtrace();
        $caller = array_shift( $trace );

        while ( empty( $caller[ 'file' ] ) || realpath( Library::formatPath( $caller[ 'file' ] ) ) == realpath( Library::formatPath( __FILE__ ) ) )
        {
            $caller = array_shift( $trace );
        }


        $function = $caller[ 'function' ];
        $class    = isset( $caller[ 'class' ] ) ? $caller[ 'class' ] . '::' : '';
        $header   = $class . $function . '()';
        $header .= !empty( $caller[ 'file' ] ) ? ' @ ' . self::formatPath( $caller[ 'file' ] ) : '';
        $header .= !empty( $caller[ 'line' ] ) ? ', line ' . $caller[ 'line' ] : '';

        $out = chr( 10 ) . '<div style="border: 1px solid #eee; margin: 5px;">' . chr( 10 );
        $out .= chr( 9 ) . '<div style="background-color: #eee; color: #333; font-family: Arial, Helv, Sans; font-size: 12px; padding: 3px;">';
        $out .= '&nbsp;' . $header . '</div>' . chr( 10 );


        if ( func_num_args() )
        {
            foreach ( func_get_args() as $arg )
            {
                $out .= chr( 9 ) . '<div style="color: #333; font-family: Arial, Helv, Sans; font-size: 12px; padding: 3px;">' . chr( 10 );
                $dump = Library::prettyPrint( $arg );
                $out .= $dump;
            }
            $out .= chr( 9 ) . '</div>' . chr( 10 );
        }
        else
        {
            $out .= chr( 9 ) . '<div style="color: #333; font-family: Arial, Helv, Sans; font-size: 12px; padding: 3px;">' . chr( 10 );
            $out .= 'No parameters passed, nothing to ' . $function . '().' . chr( 10 );
            $out .= chr( 9 ) . '</div>' . chr( 10 );
        }


        $out .= '</div>' . chr( 10 ) . chr( 10 );

        return $out;
    }

    /**
     * Output the contents of the passed variables, and stops script executions
     *
     * @return void
     * @access public
     * @static
     */
    public static function dbgd()
    {

        $args = func_get_args();
        call_user_func_array( array(
            'Library',
            'dbg'
        ), $args );
        die();
    }

    /**
     * Cleans a (file) path and replaces backslashes with forward slashes
     *
     * @param string $path
     * @return string
     * @access public
     * @static
     */
    public static function formatPath($path)
    {

        $path = str_replace( '\\', '/', $path );
        $path = str_replace( '///', '/', $path );

        $path = str_replace( '../', '/', $path ); // stop directory traversal?

        $path = str_replace( '//', '/', $path );
        $path = str_replace( '\\', '/', $path );

        return $path;
    }

    /**
     *
     * @param string $content
     * @access public
     * @return string
     */
    public static function maskContent($content)
    {

        $content = preg_replace( '#(src|href)\s*=\s*(["\'])([^\2]*)pages/\d{1,}/#isU', '$1=$2$3pages/%PAGEID%/', $content );

        return $content;
    }

    /**
     *
     * @param string $content
     * @access public
     * @return string
     */
    public static function unmaskContent($content)
    {

        $content = preg_replace( '#pages/%PAGEID%/#', 'pages/' . PAGEID . '/', $content );

        return $content;
    }

    /**
     * Checks if an incoming request is an AJAX request.
     *
     * @return boolean
     * @access public
     * @static
     */
    public static function ajax()
    {

        if ( IS_AJAX === true || ( isset( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) && $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] == 'XMLHttpRequest' ) || IS_FLASH )
        {
            return true;
        }

        return false;
    }

    /**
     * Does a redirect.
     *
     * @param $target
     * @return void
     * @access public
     * @static
     */
    public static function redirect($target)
    {
        header( 'Status: 301 Moved Permanently' );
        header( 'Retry-After: 3600' );
        header( 'Location: ' . $target );

        exit;
    }

    /**
     * Escapes single quotes, but not double quotes.
     *
     * @param string $string
     * @return string
     */
    public static function escapequotes($string)
    {

        return str_replace( '\"', '"', addslashes( $string ) );
    }


    /**
     * @param string $str
     * @return string
     */
    public static function escapeJS($str = '')
    {

        $str = preg_replace( '/\\?%/', '\\%', $str );

        return strtr( ( string )$str, array(
            '\\' => '\\\\',
            "'"  => "\\'",
            '"'  => '\\"',
            "\r" => '\\r',
            "\n" => '\\n',
            '</' => '<\/'
        ) );
    }

    /**
     * @param string $in_str
     * @return string
     */
    public static function json_encode_string($in_str)
    {

        /*
          mb_internal_encoding("UTF-8");
          $convmap = array(0x80, 0xFFFF, 0, 0xFFFF);
          $str = "";
          for ($i = mb_strlen($in_str) - 1; $i >= 0; $i--)
          {
          $mb_char = mb_substr($in_str, $i, 1);
          if (mb_ereg("&#(\\d+);", mb_encode_numericentity($mb_char, $convmap, "UTF-8"), $match))
          {
          $str = sprintf("\\u%04x", $match[1]) . $str;
          }
          else
          {
          $str = $mb_char . $str;
          }
          } */

        static $jsonReplaces = array(
            array(
                "\\",
                "/",
                "\n",
                "\t",
                "\r",
                "\b",
                "\f",
                '"'
            ),
            array(
                '\\\\',
                '\\/',
                '\\n',
                '\\t',
                '\\r',
                '\\b',
                '\\f',
                '\"'
            )
        );
        $str = str_replace( $jsonReplaces[ 0 ], $jsonReplaces[ 1 ], Strings::fixUtf8( $in_str ) );

        return $str;
    }

    /**
     * @param mixed $arr
     * @return string
     */
    public static function php_json_encode($arr)
    {

        $json_str = "";
        if ( is_array( $arr ) )
        {
            $pure_array   = true;
            $array_length = count( $arr );
            for ( $i = 0; $i < $array_length; $i++ )
            {
                if ( !isset( $arr[ $i ] ) )
                {
                    $pure_array = false;
                    break;
                }
            }
            if ( $pure_array )
            {
                $json_str = "[";
                $temp     = array();
                for ( $i = 0; $i < $array_length; $i++ )
                {
                    $temp[ ] = sprintf( "%s", self::php_json_encode( $arr[ $i ] ) );
                }
                $json_str .= implode( ",", $temp );
                $json_str .= "]";
            }
            else
            {
                $json_str = "{";
                $temp     = array();
                foreach ( $arr as $key => $value )
                {
                    $temp[ ] = sprintf( "\"%s\":%s", $key, self::php_json_encode( $value ) );
                }
                $json_str .= implode( ",", $temp );
                $json_str .= "}";
            }
        }
        else
        {
            if ( is_bool( $arr ) )
            {
                $json_str = json_encode( $arr );
            }
            elseif ( is_string( $arr ) )
            {
                $json_str = "\"" . self::json_encode_string( $arr ) . "\"";
            }
            else if ( is_numeric( $arr ) )
            {
                $json_str = $arr;
            }
            else
            {
                $json_str = "\"" . self::json_encode_string( $arr ) . "\"";
            }
        }

        return $json_str;
    }

    /**
     *
     * @param boolean $ok
     * @param bool|string $msg
     * @param integer $newid
     */
    public static function sendJson($ok = true, $msg = false, $newid = null)
    {

        $output              = array();
        $output[ 'success' ] = $ok;

        if ( !is_null( $newid ) && $newid > 0 )
        {
            $output[ 'newid' ] = $newid;
        }

        $isAdmin = ( defined( 'ADM_SCRIPT' ) && ADM_SCRIPT === true );

        if ( $isAdmin )
        {
            if ( ( !defined( 'SKIP_DEBUG' ) || ( defined( 'SKIP_DEBUG' ) && SKIP_DEBUG !== true ) ) && !isset( $data[ 'debugoutput' ] ) )
            {
                if ( CONTROLLER !== 'Indexer' )
                {
                    $output[ 'debugoutput' ] = Debug::write( true );
                }
            }
        }

        $disableAjaxDebug = Registry::get( 'disableAjaxDebug' );

        if ( is_array( $disableAjaxDebug ) )
        {

            $cl = strtolower( CONTROLLER );
            $ac = strtolower( ACTION );


            foreach ( $disableAjaxDebug as $r )
            {
                if ( $cl === $r[ 'controller' ] && $ac === $r[ 'action' ] )
                {
                    unset( $output[ 'debugoutput' ] );
                    break;
                }
            }
        }

        if ( $isAdmin && isset( $GLOBALS[ 'contentlock' ] ) )
        {
            $output[ 'lock_content' ] = true;
        }

        if ( $isAdmin && defined( 'SEND_UNLOCK' ) )
        {
            unset( $output[ 'lock_content' ] );
            $output[ 'unlock_content' ] = true;
        }

        if ( $isAdmin && isset( $GLOBALS[ 'content_lockerror' ] ) )
        {
            $output[ 'lockerror' ] = $GLOBALS[ 'content_lockerror' ];
        }


        if ( $msg !== false )
        {
            $msg = ( $msg == '' ? '' : $msg );

            if ( !$ok )
            {

                $output[ 'msg' ]   = $msg;
                $output[ 'error' ] = $msg;
            }
            else
            {
                if ( $msg )
                {
                    $output[ 'msg' ] = $msg;
                }
            }
        }

        if ( !isset( $output[ 'csrfToken' ] ) )
        {
            $output[ 'csrfToken' ] = Csrf::generateCSRF( 'token' );
        }
        Session::write();
        Ajax::Send( $ok, $output );

    }

    /**
     * json_encode() wrapper, also skips debug output and sends headers
     *
     * @param $data
     * @throws BaseException
     * @return string
     * @access public
     * @static
     */
    public static function json($data)
    {

        # Session::close();
        // self::disableErrorHandling();

        $len1 = ob_get_length();
        if ( $len1 )
        {
            $buffer = ob_get_clean();
            throw new BaseException( 'Header is send. Length:' . $len1 . '. Buffer:' . $buffer );
        }
        /*

          ob_start('ob_gzhandler');

          if ( !defined( 'SKIP_DEBUG' ) )
          define( 'SKIP_DEBUG', true );

         */


        $isAdmin = ( defined( 'ADM_SCRIPT' ) && ADM_SCRIPT == true );


        if ( ( !defined( 'SKIP_DEBUG' ) || ( defined( 'SKIP_DEBUG' ) && SKIP_DEBUG != true ) || $isAdmin ) && !isset( $data[ 'logs' ] ) )
        {
            if ( !isset( $data[ 'debugoutput' ] ) && CONTROLLER !== 'Indexer' )
            {
                $data[ 'debugoutput' ] = Debug::write( true );
            }
        }
        else
        {
            unset( $data[ 'debugoutput' ] );
        }


        if ( isset( $GLOBALS[ 'contentlock' ] ) )
        {
            $data[ 'lock_content' ] = true;
        }

        $disableAjaxDebug = Registry::get( 'disableAjaxDebug' );

        if ( is_array( $disableAjaxDebug ) )
        {

            $cl = strtolower( CONTROLLER );
            $ac = strtolower( ACTION );


            foreach ( $disableAjaxDebug as $r )
            {
                if ( $cl == $r[ 'controller' ] && $ac == $r[ 'action' ] )
                {
                    unset( $data[ 'debugoutput' ] );
                    break;
                }
            }
        }


        if ( $isAdmin && defined( 'SEND_UNLOCK' ) )
        {
            unset( $data[ 'lock_content' ] );
            $data[ 'unlock_content' ] = true;
        }

        if ( isset( $GLOBALS[ 'content_lockerror' ] ) )
        {
            $data[ 'lockerror' ] = $GLOBALS[ 'content_lockerror' ];
        }

        if ( !isset( $data[ 'pageCurrentTitle' ] ) && $isAdmin )
        {
            /*
             * navi structure
             */
            $items        = Library::getNavi();
            $title        = trans( 'Administration' );
            $currentTitle = '';

            foreach ( $items as $r )
            {
                $title .= ( $title != '' ? ' - ' : '' ) . $r[ 'title' ];
                $currentTitle = $r[ 'title' ];
            }
            $data[ 'pageTitle' ]        = $title;
            $data[ 'pageCurrentTitle' ] = $currentTitle;
            $data[ 'pageCurrentIcon' ]  = Cookie::get( 'pageCurrentIcon' );
        }

        if ( !isset( $data[ 'csrfToken' ] ) )
        {
            $data[ 'csrfToken' ] = Csrf::generateCSRF( 'token' );
        }

        if ( $isAdmin && !isset( $data[ 'mem_usage' ] ) )
        {
            $data[ 'mem_limit' ] = (int)ini_get( 'memory_limit' );
            $data[ 'mem_usage' ] = function_exists( 'memory_get_usage' ) ? round( memory_get_usage() / 1024 / 1024, 2 ) : 0;

            if ( !empty( $options[ 'mem_usage' ] ) && !empty( $data[ 'mem_limit' ] ) )
            {
                $data[ 'mem_percent' ] = round( $data[ 'mem_usage' ] / $data[ 'mem_limit' ] * 100, 0 );
            }
        }

        // checks if gzip is supported by client
        $pack = false;
        if ( isset( $_SERVER[ "HTTP_ACCEPT_ENCODING" ] ) && strpos( "gzip", $_SERVER[ "HTTP_ACCEPT_ENCODING" ] ) !== false )
        {
            $pack = true;
        }

        Session::write();

        try
        {
            header( 'Content-Type: application/json' );
            header( "Expires: Mon, 20 Jul 1995 05:00:00 GMT", true );
            header( "Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . " GMT" );
            header( "Cache-Control: no-store, no-cache, must-revalidate", true );
            header( "Cache-Control: post-check=0, pre-check=0", true );
            header( "Pragma: no-cache", true );

            $replyBody = Json::encode( $data ); // self::php_json_encode($data);
            // if supported, gzips data
            if ( $pack && strlen( $replyBody ) > 2048 )
            {
                header( "Content-Encoding: gzip" );
                $replyBody = gzencode( $replyBody, 9, true );
            }
        }
        catch ( Exception $e )
        {
            throw new BaseException( $e->getMessage() );
        }

        // compressed or not, sets the Content-Length
        #  header("Content-Length: " . mb_strlen($replyBody, 'latin1'));


        return $replyBody;

    }

    /**
     * Writes an entry to the audit log
     *
     * @param        $message
     * @param string $type
     * @param null $data
     * @return void
     * @access public
     * @static
     */
    static public function audit($message, $type = 'info', $data = null)
    {

        self::log( $message, $type, $data );
    }

    /**
     * @param array $trace
     * @return array
     */
    private static function _prepareTrace($trace)
    {

        $getTraceMode = true;
        $trace        = array_reverse( $trace );
        foreach ( $trace as $key => $line )
        {
            if ( in_array( $line[ 'function' ], array(
                    'getTraceCode',
                    'backtrace',
                    'raise',
                    'catch_errors'
                ) ) || ( isset( $line[ 'class' ] ) && in_array( $line[ 'class' ], array(
                        'Debug'
                    ) )

                )
            )
            {
                unset( $trace[ $key ] );
            }
        }

        $trace = array_reverse( $trace );

        $trace = Debug::trace( $trace );


        if ( !$getTraceMode )
        {
            $output = '<ol class="trace">';
        }
        else
        {
            $output = '<div style="display:block;text-align:left!important">';
        }


        foreach ( $trace as $i => $step )
        {

            if ( !$getTraceMode )
            {

                $output .= <<<E
   
			<li>
				<p>
					<span class="file ext_php">
E;
            }
            else
            {

            }

            $error_id = 0;


            if ( $step[ 'file' ] )
            {


                if ( !$getTraceMode )
                {
                    $source_id = $error_id . 'source' . $i;

                    $output .= '<a href="#' . $source_id . '" onclick="return toggleTrace(\'' . $source_id . '\')">';
                }

                $output .= Debug::path( $step[ 'file' ] );

                if ( !$getTraceMode )
                {
                    $output .= '[ ' . $step[ 'line' ] . ' ]</a>';
                }
                else
                {
                    $output .= ' [ ' . $step[ 'line' ] . ' ]';
                }
            }
            else
            {
                $output .= 'PHP internal call ';
            }


            if ( !$getTraceMode )
            {
                $output .= <<<E
</span> &raquo; {$step['function']} (
E;
            }
            else
            {
                $output .= " {$step['function']} (";
            }


            if ( $step[ 'args' ] )
            {
                $args_id = $error_id . 'args' . $i;
                if ( !$getTraceMode )
                {
                    $output .= '<a href="#' . $args_id . '" onclick="return toggleTrace(\'' . $args_id . '\')">arguments</a>';
                    $output .= ')';
                }
            }
            else
            {
                $output .= ')';
            }

            if ( !$getTraceMode )
            {
                $output .= '</p>';
            }
            else
            {

            }


            if ( isset( $args_id ) )
            {
                if ( !$getTraceMode )
                {
                    $output .= <<<E

				<div id="{$args_id}" class="collapsed args">
					<table cellspacing="0" class="args">
E;
                }
                else
                {

                }


                foreach ( $step[ 'args' ] as $name => $arg )
                {
                    $_dump = Debug::dump( $arg );


                    if ( !$getTraceMode )
                    {
                        $output .= <<<E
						<tr>
							<td><code>{$name}</code></td>
							<td><pre>{$_dump}</pre></td>
						</tr>
E;
                    }
                    else
                    {


                        $_dump = substr( $_dump, 0, 150 );

                        $output .= <<<E
                        <div style="display:block;font-weight:normal!important">
<div style="width:30%;float:left;display:inline-block">{$name}</div><div style="width:68%;float:right;display:inline-block">{$_dump}</div>
    </div>
E;
                    }
                }


                if ( !$getTraceMode )
                {
                    $output .= '
					</table>
				</div>';
                }
                else
                {

                }
            }

            if ( isset( $source_id ) )
            {
                if ( !$getTraceMode )
                {

                    $output .= <<<E
                    
					<div id="{$source_id}" class="source collapsed"><code>{$step['source']}</code></div>
E;
                }
            }


            if ( !$getTraceMode )
            {
                $output .= '</li>';
            }
            else
            {
                // stop other traces
                if ( !$fullReturnMode )
                {
                    break;
                }
                else
                {
                    if ( isset( $args_id ) )
                    {
                        $output .= '
';
                    }
                }
            }
        }

        if ( !$getTraceMode )
        {
            $output .= '</ol>';
        }
        else
        {

            $output .= '</div>';
        }

        return $output;
    }

    /**
     *
     * @param string $message
     * @param string $type default is info (info,note,warn,error)
     * @param string $data default is null
     * @param bool $dataIsTrace unused
     */
    static public function log($message, $type = 'info', $data = null, $dataIsTrace = false)
    {
        $application = false;
        if ( Registry::objectExists( 'Application' ) )
        {
            $application = Registry::getObject( 'Application' );
        }


        $_backtrace = '';
        if ( $type == 'error' || $type == 'warn' )
        {

            if ( !is_array( $dataIsTrace ) )
            {
                // get backtrace
                $trace = debug_backtrace( false );
                array_shift( $trace );

                if ( $trace[ 0 ][ 'class' ] === 'DatabaseError' )
                {
                    array_shift( $trace );
                }

                $newtrace = $trace;
                foreach ( $trace as $line )
                {
                    if ( !empty( $line[ 'class' ] ) && (
                            ( $line[ 'class' ] == __CLASS__ && $line[ 'function' ] == 'log' ) ||
                            $line[ 'class' ] == 'Exception' ||
                            $line[ 'class' ] == 'BaseException' ||
                            $line[ 'class' ] == 'Error' ||
                            $line[ 'class' ] == 'Debug' ||
                            $line[ 'class' ] == 'PDOStatement' ||
                            $line[ 'class' ] == 'Database_PDO' ||
                            $line[ 'class' ] == 'DatabaseError' || $line[ 'class' ] == 'PDOException' ) ||
                        $line[ 'function' ] == 'shutdownError' ||
                        $line[ 'function' ] == 'call_user_func_array'

                    )
                    {
                        array_shift( $newtrace );
                    }
                }
                $trace = $newtrace;
            }
            else
            {
                $trace = $dataIsTrace;
                if ( $trace[ 0 ][ 'class' ] === 'DatabaseError' )
                {
                    array_shift( $trace );
                }
            }

            /*


                        if ( $trace[ 0 ][ 'class' ] == 'Library' && $trace[ 0 ][ 'function' ] == 'log' )
                        {
                            array_shift( $trace );
                            $file = $trace[ 0 ][ 'file' ];
                            $line = $trace[ 0 ][ 'line' ];
                        }

                        if ( $trace[ 0 ][ 'class' ] == 'ErrorHandler' || $trace[ 0 ][ 'class' ] == 'BaseException' )
                        {
                            array_shift( $trace );
                            $file = $trace[ 0 ][ 'file' ];
                            $line = $trace[ 0 ][ 'line' ];
                        }



                        if ( $trace[ 0 ][ 'function' ] == 'catch_errors' )
                        {
                            array_shift( $trace );
                            $file = $trace[ 0 ][ 'file' ];
                            $line = $trace[ 0 ][ 'line' ];
                        }
            */

            $file = $trace[ 0 ][ 'file' ];
            $line = $trace[ 0 ][ 'line' ];

            $sourceSnip = Debug::source( $file, $line, 8 );

            if ( isset( $trace[ 0 ][ 'class' ] ) && $trace[ 0 ][ 'class' ] == 'BaseException' || $trace[ 0 ][ 'class' ] == 'PDOStatement' )
            {
                array_shift( $trace );
            }

            $_backtrace = serialize( array(
                'args'     => $trace[ 0 ][ 'args' ] && !( $trace[ 0 ][ 'args' ] instanceof PDOStatement ) ? $trace[ 0 ][ 'args' ] : array(),
                'class'    => $trace[ 0 ][ 'class' ] ? $trace[ 0 ][ 'class' ] : '',
                'function' => $trace[ 0 ][ 'function' ] ? $trace[ 0 ][ 'function' ] : '',
                'file'     => str_replace( ROOT_PATH, '... /', $file ),
                'line'     => $line,
                'snipped'  => $sourceSnip
            ) );

            unset( $trace );
        }

        $_message = strip_tags( $message );


        if ( $data !== null )
        {
            if ( is_array( $data ) )
            {
                if ( !isset( $data[ 'log' ] ) )
                {
                    $data[ 'log' ] = $data;
                }
            }
            else if ( is_string( $data ) )
            {
                $data[ 'log' ] = $data;
            }


            $data[ 'requestMethod' ] = HTTP::requestType();
            $data[ 'request' ]       = HTTP::input();
            $data[ 'cookie' ]        = Cookie::get();
        }
        else
        {
            $data[ 'log' ] = false;

            $data[ 'requestMethod' ] = HTTP::requestType();
            $data[ 'request' ]       = HTTP::input();
            $data[ 'cookie' ]        = Cookie::get();
        }

        $data[ 'REQUEST_URI' ] = ( isset( $_SERVER[ 'REQUEST_URI' ] ) ? $_SERVER[ 'REQUEST_URI' ] : '' );


        $db = Database::getInstance();
        $db->query( 'INSERT INTO %tp%logs (fb,pageid,action,message,username,userid,ip,logtype,backtrace,browser,time, data) ' . 'VALUES(?,?,?,?,?,?,?,?,?,?,?,?)', ( $application ? $application->getMode() : 0 ), defined( 'PAGEID' ) ? PAGEID : 0, substr( $_message, 0, 70 ) . ( strlen( $_message ) > 70 ?
                '...' : '' ), $message, $application ? User::getUsername() : '', $application ? User::getUserId() : '', ( defined( 'USER_IP' ) ? USER_IP : ( isset( $_SERVER[ 'REMOTE_ADDR' ] ) ? $_SERVER[ 'REMOTE_ADDR' ] : '' ) ), $type, $_backtrace, ( defined( 'USER_BROWSER' ) ? USER_BROWSER :
                ( isset( $_SERVER[ 'HTTP_USER_AGENT' ] ) ? $_SERVER[ 'HTTP_USER_AGENT' ] : '' ) ), time(), ( $data !== null && $data != '' ? serialize( $data ) : '' ) );
    }

    /**
     * Recursive stripslashes function.
     *
     * @param $value
     * @return mixed
     * @access public
     * @static
     */
    static function stripslashesDeep($value)
    {

        $value = is_array( $value ) ? array_map( array(
            'Library',
            'stripslashesDeep'
        ), $value ) : ( !is_numeric( $value ) ? stripslashes( $value ) : $value );

        return $value;
    }

    /*
     * Function to turn a mysql datetime (YYYY-MM-DD HH:MM:SS) into a unix timestamp
     * @param str
     * The string to be formatted
     */

    static function convertSqlDatetime($str)
    {

        if ( strpos( $str, '-' ) === false )
        {
            return 0;
        }

        list( $date, $time ) = explode( ' ', $str );
        list( $year, $month, $day ) = explode( '-', $date );
        list( $hour, $minute, $second ) = explode( ':', $time );
        $timestamp = mktime( intval( $hour ), intval( $minute ), intval( $second ), $month, $day, $year );

        return $timestamp;
    }

    /**
     * Store a message in the session to display on next page load.
     *
     * @param string $message
     */
    static function notify($message)
    {

        Session::save( '_notification_message', $message );
    }

    /**
     * Retrieve a message from the session store.
     *
     * @return string
     */
    static function getNotification()
    {

        $message = Session::get( '_notification_message' );
        Session::delete( '_notification_message' );

        return $message;
    }

    /**
     * Return mimetype detect method name
     *
     * @return string
     */
    public static function getMimeDetect()
    {

        if ( class_exists( 'finfo' ) )
        {
            return 'finfo';
        }
        elseif ( function_exists( 'mime_content_type' ) && ( mime_content_type( __FILE__ ) == 'text/x-php' || mime_content_type( __FILE__ ) == 'text/php' || mime_content_type( __FILE__ ) == 'application/php' ) )
        {
            return 'php';
        }

        /*
          elseif ( function_exists( 'exec' ) )
          {
          $type = exec( 'file -ib ' . escapeshellarg( __FILE__ ) );
          if ( 0 === strpos( $type, 'text/x-php' ) || 0 === strpos( $type, 'text/x-c++' ) )
          {
          return 'linux';
          }
          $type = exec( 'file -Ib ' . escapeshellarg( __FILE__ ) );
          if ( 0 === strpos( $type, 'text/x-php' ) || 0 === strpos( $type, 'text/x-c++' ) )
          {
          return 'bsd';
          }
          }
         */

        return 'internal';
    }


    /**
     * Works out a file's mimetype. Tries to do so the nice way (by using finfo). If that fails,
     * tries to get the mimetype using a slightly less nice way (by using mime_content_type) and
     * if that fails, uses a very dirty lookup table using the file's extension.
     *
     * @param $path
     * @return bool|mixed|string
     */
    static function getMimeType($path)
    {

        if ( !is_string( $path ) )
        {
            return false;
        }
        $path     = str_replace( '\\', '/', $path );
        $detector = self::getMimeDetect();

        switch ( $detector )
        {

            case 'finfo':
                $mimefile = ini_get( 'mime_magic.magicfile' );
                if ( is_readable( $mimefile ) )
                {
                    if ( is_null( self::$_finfo ) )
                    {
                        self::$_finfo = finfo_open( FILEINFO_MIME, $mimefile );
                    }
                    $mime = @finfo_file( self::$_finfo, $path );
                }
                else if ( function_exists( 'mime_content_type' ) && ( mime_content_type( __FILE__ ) == 'text/x-php' || mime_content_type( __FILE__ ) == 'text/x-c++' ) )
                {
                    $mime = mime_content_type( $path );
                }
                else
                {
                    $pinfo = pathinfo( $path );
                    $ext   = isset( $pinfo[ 'extension' ] ) ? strtolower( $pinfo[ 'extension' ] ) : '';
                    $mime  = ( isset( self::$mimeTypes[ $ext ] ) && $ext ? self::$mimeTypes[ $ext ] : 'unknown' );
                }

                break;
            case 'php':
                $mime = mime_content_type( $path );
                break;
            /*
              case 'linux':
              $mime  = exec( 'file -ib ' . escapeshellarg( $path ) );
              break;
              case 'bsd':
              $mime  = exec( 'file -Ib ' . escapeshellarg( $path ) );
              break;
             * 
             */
            default:
                $pinfo = pathinfo( $path );
                $ext   = isset( $pinfo[ 'extension' ] ) ? strtolower( $pinfo[ 'extension' ] ) : '';
                $mime  = isset( self::$mimeTypes[ $ext ] ) && $ext ? self::$mimeTypes[ $ext ] : 'unknown';
        }


        return $mime;
    }

    /**
     * @param      $ext
     * @param bool $is_dir
     * @return mixed
     */
    static function getInfo($ext, $is_dir = false)
    {

        static $fType;

        if ( !is_array( $fType ) )
        {
            $file_types = array();
            $fType      = array();
            include( HELPER_PATH . 'file_mappings.php' );
            $fType = $file_types;
        }

        $collection = ( ( $is_dir === false ) ? 'files' : 'folders' );

        if ( isset( $fType[ $collection ][ $ext ] ) )
        {
            return $fType[ $collection ][ $ext ];
        }
        else
        {
            return $fType[ $collection ][ '__default' ];
        }
    }

    /**
     * Fast find in_array
     *
     * @param mixed $elem
     * @param array $array
     * @return bool
     */
    static function inArray($elem, $array)
    {

        $top = sizeof( $array ) - 1;
        $bot = 0;

        while ( $top >= $bot )
        {
            $p = floor( ( $top + $bot ) / 2 );
            if ( $array[ $p ] < $elem )
            {
                $bot = $p + 1;
            }
            elseif ( $array[ $p ] > $elem )
            {
                $top = $p - 1;
            }
            else
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Protect the folder with an .htaccess file
     *
     * @param string $strFolder
     * @param bool $blnForceProtection
     * @return bool
     */
    public static function protectFolder($strFolder, $blnForceProtection = false)
    {

        $strFolder = str_replace( '\\', '/', $strFolder );

        if ( !file_exists( ROOT_PATH . $strFolder . '/.htaccess' ) || $blnForceProtection == true )
        {
            $objFile = fopen( $strFolder . '/.htaccess', 'a+' );
            fwrite( $objFile, "order deny,allow\ndeny from all" );
            fclose( $objFile );

            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Remove the .htaccess protection from the folder
     *
     * @param string $strFolder
     * @return bool
     */
    public static function removeProtection($strFolder)
    {

        $strFolder = str_replace( '\\', '/', $strFolder );

        if ( file_exists( ROOT_PATH . $strFolder . '/.htaccess' ) )
        {
            $file = new File( '', true );
            $file->delete( $strFolder . '/.htaccess' );

            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Delete file recursively
     *
     * @param string $dir
     * @return bool
     */
    public static function recursiveDelete($dir)
    {

        $dir = str_replace( '\\', '/', $dir );

        foreach ( array_diff( scandir( $dir ), array(
            '.',
            '..'
        ) ) as $file )
        {
            ( is_dir( "$dir/$file" ) ) ? self::recursiveDelete( "$dir/$file" ) : unlink( "$dir/$file" );
        }

        return rmdir( $dir );
    }

    /**
     * Copy file recursively
     *
     * @param        string src, Source
     * @param string $dest , where to save
     * @return bool
     */
    public static function recursiveCopy($src, $dest)
    {

        $src  = str_replace( '\\', '/', $src );
        $dest = str_replace( '\\', '/', $dest );

        // recursive function to delete
        // all subdirectories and contents:
        if ( is_dir( $src ) )
        {

            $dir_handle = opendir( $src );
        }

        while ( $file = readdir( $dir_handle ) )
        {
            if ( $file != "." && $file != ".." )
            {
                if ( !is_dir( $src . "/" . $file ) )
                {
                    if ( !file_exists( $dest . "/" . $file ) )
                    {
                        @copy( $src . "/" . $file, $dest . "/" . $file );
                    }
                }
                else
                {
                    @mkdir( $dest . "/" . $file, 0775 );
                    self::recursiveCopy( $src . "/" . $file, $dest . "/" . $file );
                }
            }
        }

        closedir( $dir_handle );

        return true;
    }

    /**
     * Get the directory size
     *
     * @param string $directory
     * @return integer
     */
    public static function dirSize($directory)
    {

        $size      = 0;
        $directory = str_replace( '\\', '/', $directory );

        foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $directory ) ) as $file )
        {
            $size += $file->getSize();
        }

        return $size;
    }

    /**
     * create a array of all dirs from path
     *
     * @param string $path any path
     * @return array $dirs
     */
    static function getDirs($path)
    {

        if ( is_dir( $path ) !== true )
        {
            return array();
        }

        $path = str_replace( '\\', '/', $path );

        $files = scandir( $path );

        $dirs = array();
        natcasesort( $files );
        if ( count( $files ) > 2 )
        {
            // All dirs
            foreach ( $files as $file )
            {
                if ( $file != '.' && $file != '..' && is_dir( $path . $file ) )
                {
                    $dirs[ ] = array(
                        'dirname' => $file
                    );
                }
            }
        }

        return $dirs;
    }

    /**
     * create a array of all files from path
     *
     * @param string $path any path
     * @param boolean $subScan with all subdirs? default is false
     * @param array $_file cached files
     * @param bool $withDirs
     * @return array $file
     */
    public static function getFiles($path, $subScan = false, &$_file = array(), $withDirs = false)
    {

        if ( is_dir( $path ) !== true )
        {
            return array();
        }

        $path = str_replace( '\\', '/', $path );

        $files = scandir( $path );

        //$file = array();
        natcasesort( $files );

        // All dirs
        foreach ( $files as $file )
        {
            if ( $subScan && $file != '.' && $file != '..' && is_dir( $path . $file ) )
            {
                if ( $withDirs )
                {
                    $_file[ ] = array(
                        'dirname' => $file,
                        'path'    => $path
                    );
                }

                self::getFiles( $path . $file . '/', $subScan, $_file, $withDirs );
            }
            else if ( $file != '.' && $file != '..' && is_file( $path . $file ) && !is_dir( $path . $file ) )
            {
                $_file[ ] = array(
                    'filename' => $file,
                    'path'     => $path
                );
            }
        }

        return $_file;
    }

    /**
     * Helper Function for moveDirs
     *
     * @param string $dir
     * @param int $mode default is 0777
     * @param bool $recursive default is true
     * @return bool
     */
    static function makeAll($dir, $mode = 0777, $recursive = true)
    {
        if ( is_null( $dir ) || $dir === "" )
        {
            return false;
        }

        $dir = str_replace( '\\', '/', $dir );

        if ( is_dir( $dir ) || $dir === "/" )
        {
            return true;
        }

        if ( self::makeAll( dirname( $dir ), $mode, $recursive ) )
        {
            return mkdir( $dir, $mode );
        }

        return false;
    }

    /**
     * Simple function to Copy Dirs and Files
     *
     * @param string $source
     * @param string $dest
     * @param array $options (folderPermission => 0755 and filePermission => 0755)
     * @return boolean
     */
    static function moveDirs($source, $dest, $options = array(
        'folderPermission' => 0755,
        'filePermission'   => 0755
    )) {

        $result = false;

        $source = str_replace( '\\', '/', $source );
        $dest   = str_replace( '\\', '/', $dest );


        // For Cross Platform Compatibility
        if ( !isset( $options[ 'noTheFirstRun' ] ) )
        {
            $options[ 'noTheFirstRun' ] = true;
        }


        if ( is_file( $source ) )
        {
            if ( $dest[ strlen( $dest ) - 1 ] == '/' )
            {
                if ( !file_exists( $dest ) )
                {
                    self::makeAll( $dest, $options[ 'folderPermission' ], true );
                }
                $__dest = $dest . "/" . basename( $source );
            }
            else
            {
                $__dest = $dest;
            }
            $result = copy( $source, $__dest );


            @chmod( $__dest, $options[ 'filePermission' ] );

            // unlink $source
        }
        elseif ( is_dir( $source ) )
        {
            if ( $dest[ strlen( $dest ) - 1 ] == '/' )
            {
                if ( $source[ strlen( $source ) - 1 ] == '/' )
                {
                    //Copy only contents
                }
                else
                {
                    //Change parent itself and its contents
                    $dest = $dest . basename( $source );
                    @mkdir( $dest );
                    @chmod( $dest, $options[ 'filePermission' ] );
                }
            }
            else
            {
                if ( $source[ strlen( $source ) - 1 ] == '/' )
                {
                    //Copy parent directory with new name and all its content
                    @mkdir( $dest, $options[ 'folderPermission' ] );
                    @chmod( $dest, $options[ 'filePermission' ] );
                }
                else
                {
                    //Copy parent directory with new name and all its content
                    @mkdir( $dest, $options[ 'folderPermission' ] );
                    @chmod( $dest, $options[ 'filePermission' ] );
                }
            }

            $dirHandle = opendir( $source );
            while ( $file = readdir( $dirHandle ) )
            {
                if ( $file != "." && $file != ".." )
                {
                    $__dest   = $dest . "/" . $file;
                    $__source = $source . "/" . $file;

                    if ( $__source != $dest )
                    {
                        $result = self::moveDirs( $__source, $__dest, $options );
                    }
                }
            }
            closedir( $dirHandle );

            // unlink $source
        }
        else
        {
            $result = false;
        }

        return $result;
    }

    /**
     * ensures the given path exists
     *
     * @param string $path any path
     * @param string $baseDir the base directory where the directory is created
     *                        ($path must still contain the full path, $baseDir
     *                        is only used for unix permissions)
     * @return bool
     */
    static function makeDirectory($path, $baseDir = null)
    {

        $path = str_replace( '\\', '/', $path );

        if ( substr( $path, -1 ) === '/' )
        {
            $path = substr( $path, 0, -1 );
        }

        if ( is_dir( $path ) === true )
        {
            return true;
        }

        if ( $baseDir === null )
        {
            $baseDir = ROOT_PATH;
        }

        $chmod = 0777;

        $path    = strtr( str_replace( $baseDir, '', $path ), '\\', '/' );
        $folders = explode( '/', trim( $path, '/' ) );
        foreach ( $folders as $folder )
        {
            if ( $folder === '' )
            {
                continue;
            }
            if ( !file_exists( $baseDir . $folder ) )
            {
                $baseDir .= $folder . '/';

                $oldumask = umask( 0 );

                mkdir( $baseDir, $chmod );
                chmod( $baseDir, $chmod );

                umask( $oldumask );
            }
            else
            {
                $baseDir .= $folder . '/';
            }
        }


        if ( is_dir( $path ) === true )
        {
            return true;
        }

        return false;
    }

    /**
     * create a empty file
     *
     * @param string $file
     * @param string $mode
     * @param string $content
     * @internal param string $path any path
     */
    static function makeFile($file, $mode = 'w', $content = '')
    {

        $file = str_replace( '\\', '/', $file );

        $path = dirname( $file );

        if ( file_exists( $file ) === true )
        {
            return;
        }

        self::makeDirectory( $path );


        $fp = fopen( $file, $mode );

        if ( $content )
        {
            fwrite( $fp, $content );
        }

        fclose( $fp );

        chmod( $file, 0777 );
    }

    /**
     * Returns the filename from string
     *
     * @param string $instring
     * @param bool $excludeExt
     * @return string $filename
     */
    static function getFilename($instring, $excludeExt = false)
    {
        $str = explode( '/', str_replace( '\\', '/', $instring ) );
        $str = ( count( $str ) ? $str[ count( $str ) - 1 ] : $instring );

        if ( $excludeExt )
        {
            $str = str_replace( '.' . self::getExtension( $instring ), '', $str );
        }

        return $str;
    }

    /**
     * Returns the extension on a file (or other string)
     *
     * @param string $instring
     * @internal param string $string
     * @return string $ext
     * @access   public
     * @static
     */
    static function getExtension($instring)
    {
        include_once( HELPER_PATH . 'mbstring.php' );

        return utf8_substr( utf8_strrchr( $instring, "." ), 1 );
    }

    /**
     * Returns a string's length.
     *
     * @param $data The string to get the length of.
     * @return int The length of the string.
     */
    static function length($data)
    {

        include_once( HELPER_PATH . 'mbstring.php' );

        if ( function_exists( 'mb_strlen' ) )
        {
            return mb_strlen( $data, 'utf-8' );
        }
        else
        {
            return strlen( $data );
        }
    }

    /**
     * Create a word array from HTML
     * clear all bad words
     *
     * @param string $string
     * @return array return a unique words array
     */
    static function makeWordArray($string)
    {

        include_once( HELPER_PATH . 'mbstring.php' );

        $str      = strip_tags( utf8_strtolower( $string ) );
        $str      = str_replace( array(
            '-',
            "\r\n",
            "\n",
            "\t"
        ), ' ', $str );
        $str      = preg_replace( '/([^a-zäöüß0-9 ]+)/is', ' ', $str );
        $arrWords = preg_split( '/[\s,;]/sU', $str );


        // Remove stopwords
        $locale = Locales::getLocale();
        if ( file_exists( I18N_PATH . $locale . '/stopwords.txt' ) )
        {
            $arrWords = array_diff( $arrWords, array_map( 'trim', file( I18N_PATH . $locale . '/stopwords.txt' ) ) );
        }

        return array_unique( $arrWords );
    }

    /**
     * Cleans up a filename. Don't supply a file path, as that will be cleaned up beyond all recognition (cubar?).
     *
     * @param $string The string to sanitize.
     * @return string The sanitized file name.
     */
    static function sanitizeFilename($string)
    {

        $string = str_replace( '\\', '/', $string );


        $extension = Library::getExtension( $string );
        $simple    = str_replace( '.' . $extension, '', $string );
        $simple    = trim( $simple );
        $simple    = str_replace( " ", "-", $simple );
        $simple    = str_replace( "--", "-", $simple );
        $string    = htmlentities( $string );
        $string    = preg_replace( "/&([a-z])[a-z]+;/i", "$1", $string );
        $simple    = preg_replace( "/[^a-zA-Z0-9_-]/", "", $simple );
        if ( $extension != '' )
        {
            $simple .= '.' . $extension;
        }

        return strtolower( $simple );
    }

    /**
     * Works out the max upload size for files based on ini settings.
     *
     * @param boolean $as_int Indicates whether the max upload size should be returned as integer.
     * @return string The max possible upload size.
     */
    static function getMaxUploadSize($as_int = false)
    {

        $post_max_size = ini_get( 'post_max_size' );
        $unit          = strtoupper( substr( $post_max_size, -1 ) );
        $multiplier    = ( $unit == 'M' ? 1048576 : ( $unit == 'K' ? 1024 : ( $unit == 'G' ? 1073741824 : 1 ) ) );
        $post_size     = $multiplier * ( int )$post_max_size;

        $upload_max_size = ini_get( 'upload_max_filesize' );
        $unit            = strtoupper( substr( $upload_max_size, -1 ) );
        $multiplier      = ( $unit == 'M' ? 1048576 : ( $unit == 'K' ? 1024 : ( $unit == 'G' ? 1073741824 : 1 ) ) );
        $upload_size     = $multiplier * ( int )$upload_max_size;

        $max_size = min( $post_size, $upload_size );
        if ( $as_int )
        {
            return $max_size;
        }
        else
        {
            return strtoupper( Library::humanSize( $max_size, '%1.0f' ) );
        }
    }

    /**
     * Formats a bytecount to be readable for humans
     *
     * @param int $size
     * @param null|string $unit
     * @param null|string $retstring
     * @param bool $si
     * @author Aidan Lister <aidan@php.net>
     * @link   http://aidanlister.com/repos/v/function.rmdirr.php
     * @author
     * @return unknown_type
     */
    static function sizeReadable($size, $unit = null, $retstring = null, $si = true)
    {

        $sizes = array(
            'B',
            'KB',
            'MB',
            'GB',
            'TB',
            'PB'
        );
        $mod   = 1024;
        $ii    = count( $sizes ) - 1;
        $unit  = array_search( ( string )$unit, $sizes );
        if ( $unit === null || $unit === false )
        {
            $unit = $ii;
        }
        if ( $retstring === null )
        {
            $retstring = '%2.1f';
        }
        $i = 0;
        while ( $unit != $i && $size >= 1024 && $i < $ii )
        {
            $size /= $mod;
            $i++;
        }

        return array(
            sprintf( $retstring, $size ),
            $sizes[ $i ]
        );
    }

    /**
     * @function    humanSize
     * Wrapper for sizeReadable, returns a single string.
     */
    static function humanSize($size, $retstring = null)
    {

        $size = Library::sizeReadable( $size, null, $retstring, null );

        return $size[ 0 ] . ' ' . $size[ 1 ];
    }

    static function formatSize($size, $retstring = null)
    {

        return Library::humanSize( $size, $retstring );
    }

    /**
     * @function    incrementFileName
     * Checks to see if a file name contains a counter, and if so, increases it
     */
    static function incrementFilename($file)
    {

        $ext_only  = false;
        $file      = explode( '/', Library::formatPath( $file ) );
        $file_name = array_pop( $file );
        $ext       = Library::getExtension( $file_name );
        $ext       = !empty( $ext ) ? '.' . $ext : '';
        $file_name = str_replace( $ext, '', $file_name );
        if ( empty( $file_name ) )
        {
            $ext_only  = true;
            $file_name = $ext;
        }
        $base_path = implode( '/', $file ) . '/';

        // split the file name up using the counter separator (-)
        $file_name_parts = explode( '-', $file_name );
        if ( count( $file_name_parts ) == 1 )
        {
            $file_name_parts[ ] = '0';
        }

        // does the file already have an increment counter?
        if ( preg_match( '/(?<digit>\d+)/', $file_name_parts[ count( $file_name_parts ) - 1 ], $matches ) )
        {
            $counter                                          = $matches[ 0 ];
            $new_counter                                      = $counter + 1;
            $file_name_parts[ count( $file_name_parts ) - 1 ] = $new_counter;
            $file_name                                        = implode( '-', $file_name_parts );
        }
        else
        {
            $file_name .= '-1';
        }

        $path = $base_path . $file_name;
        if ( !$ext_only )
        {
            $path .= $ext;
        }

        return $path;
    }

    /**
     * @function    moveTempFile
     * moves a file from it's temp to it's permament location
     */
    public static function moveTempFile($path)
    {

        $path = str_replace( '\\', '/', $path );

        if ( strpos( Library::formatPath( UPLOAD_PATH . $path ), Library::formatPath( UPLOAD_PATH . '/temp/' ) ) !== false )
        {
            $filename    = basename( $path );
            $source      = Library::formatPath( UPLOAD_PATH . $path );
            $destination = Library::formatPath( UPLOAD_PATH . strtolower( $filename[ 0 ] ) . '/' );
            if ( !ini_get( 'safe_mode' ) )
            {
                if ( !file_exists( $destination ) )
                {
                    $old_umask = umask( 0 );
                    mkdir( $destination, 0777 );
                }
            }
            else
            {
                $destination = Library::formatPath( UPLOAD_PATH . '/' );
            }
            $destination .= $filename;
            while ( file_exists( $destination ) )
            {
                $destination = Library::incrementFileName( $destination );
            }

            Library::disableErrorHandling();
            $res = rename( $source, $destination );
            Library::enableErrorHandling();
            if ( $res === false )
            {
                Error::raise( trans( 'could.not.move.uploaded.file' ) );
            }
            else
            {
                return str_replace( UPLOAD_PATH, '', $destination );
            }
        }
        else
        {
            return $path;
        }
    }

    /**
     * @function    disableErrorHandling
     * Turn off error handling
     */
    static function disableErrorHandling()
    {

        self::$oldErrorReporting[ ] = error_reporting();

        error_reporting( 0 );
        $old_error_handling = restore_error_handler();
    }

    /**
     * @function    enableErrorHandling
     * Turn error handling back on again
     */
    static function enableErrorHandling()
    {

        // restore_error_handler();
        error_reporting( array_pop( self::$oldErrorReporting ) );
        set_error_handler( 'catch_errors' );
    }

    /**
     * @function    canGraphic
     * Checks a file's extension to see if it's an image that can be modified using Graphic
     */
    static function canGraphic($path)
    {

        $ext    = strtolower( self::getExtension( $path ) );
        $images = array(
            'png',
            'jpg',
            'jpeg',
            'gif'
        );

        return in_array( $ext, $images );
    }

    /**
     *
     * @param string $ext
     * @param string $valid
     * @return boolean
     */
    static function validGrapicHeader($ext, &$valid)
    {

        preg_match( '/^GIF8[79]a/', $valid, $gif );
        preg_match( '/\xff\xd8/', $valid, $jpg2 );
        preg_match( '/(Exif|JFIF)/', $valid, $jpg );
        preg_match( '/^\.PNG/', $valid, $png );
        preg_match( '/^x89PNGx0dx0ax1ax0a/', $valid, $png2 );

        $_valid = false;

        switch ( $ext )
        {
            case 'png':
                if ( isset( $png2[ 0 ] ) || isset( $png[ 0 ] ) )
                {
                    $_valid = true;
                }
                break;

            case 'jpg':
            case 'jpeg':
                if ( isset( $jpg2[ 0 ] ) || isset( $jpg[ 0 ] ) )
                {
                    $_valid = true;
                }
                break;

            case 'gif':
                if ( isset( $gif[ 0 ] ) )
                {
                    $_valid = true;
                }
                break;
        }


        return $_valid;
    }

    /**
     * @function    isValidGraphic
     * Checks a file's extension to see if it's an image that can be modified using Graphic
     */
    static function isValidGraphic($path)
    {

        $path = str_replace( '\\', '/', $path );

        if ( !file_exists( $path ) || !is_file( $path ) )
        {
            return false;
        }

        $ext = strtolower( self::getExtension( $path ) );

        $imageinfo = array();
        $size      = @getimagesize( $path, $imageinfo );


        if ( $size[ 2 ] === 1 || $size[ 2 ] === 2 || $size[ 2 ] === 3 || $size[ 2 ] === 5 || $size[ 2 ] === 6 )
        {
            return true;
        }


        $_valid = false;
        $val    = 0;
        if ( function_exists( 'exif_imagetype' ) )
        {
            $val = @exif_imagetype( $path );
        }
        else
        {
            $valid = '';
            $fp    = fopen( $path, "rb" );
            $valid = fread( $fp, 500 );
            fclose( $fp );

            preg_match( '/^GIF8[79]a/', $valid, $gif );
            preg_match( '/\xff\xd8/', $valid, $jpg2 );
            preg_match( '/(Exif|JFIF)/', $valid, $jpg );
            preg_match( '/^\.PNG/', $valid, $png );
            preg_match( '/^x89PNGx0dx0ax1ax0a/', $valid, $png2 );
        }

        switch ( $ext )
        {
            case 'png':
                if ( isset( $png2[ 0 ] ) || isset( $png[ 0 ] ) || $val == 3 )
                {
                    $_valid = true;
                }
                break;

            case 'jpg':
            case 'jpeg':
                if ( isset( $jpg2[ 0 ] ) || isset( $jpg[ 0 ] ) || $val == 2 )
                {
                    $_valid = true;
                }
                break;

            case 'gif':
                if ( isset( $gif[ 0 ] ) || $val == 1 )
                {
                    $_valid = true;
                }
                break;
        }

        return $_valid;
    }

    /**
     * Encodes $data for display in a form, uses htmlspeciarchars().
     *
     * @param  string $data The data to encode.
     * @param int $quote_type Quote type to use when encoding, defaults to ENT_QUOTES.
     * @return unknown_type
     */
    static function encode($data, $quote_type = ENT_QUOTES)
    {

        return htmlspecialchars( $data, $quote_type, 'UTF-8' );
    }

    /**
     * @param string $str
     * @param int $quote_type
     * @return mixed
     */
    static function decode($str, $quote_type = ENT_QUOTES)
    {

        $str = str_replace( "&amp;", "&", $str );
        $str = str_replace( "&apos", "'", $str );
        $str = str_replace( "&#039;", "'", $str );
        $str = str_replace( '&quot;', "\"", $str );
        $str = str_replace( '&lt;', "<", $str );
        $str = str_replace( '&gt;', ">", $str );

        return $str;
    }

    /**
     * Recursively delete a directory and it's contents
     *
     * @author Aidan Lister <aidan@php.net>
     * @link   http://aidanlister.com/repos/v/function.rmdirr.php
     * @param  string $dirname
     * @param  bool $currentRoot
     * @return Boolean indicating success at deleting the directory.
     */
    static function rmdirr($dirname, $currentRoot = true)
    {

        static $dirRoot;

        if ( $currentRoot === true && !is_string( $dirRoot ) )
        {
            $dirRoot = $dirname;
            $dirRoot = str_replace( '\\', '/', $dirRoot );
        }


        $dirname = str_replace( '\\', '/', $dirname );


        if ( !file_exists( $dirname ) || !is_dir( $dirname ) )
        {
            return false;
        }

        if ( ( is_file( $dirname ) || is_link( $dirname ) ) && !is_dir( $dirname ) )
        {
            return unlink( $dirname );
        }

        foreach ( scandir( $dirname ) as $item )
        {
            if ( $item == '.' || $item == '..' || $item == '' )
            {
                continue;
            }

            $path = $dirname . "/" . $item;

            if ( is_dir( $path ) )
            {
                Library::rmdirr( $path, false );
            }
            else
            {
                @unlink( $path );
            }
        }

        /**
         * das root verzeichnis nicht löschen!!!
         */
        if ( $dirRoot != '' && $dirRoot != $dirname && is_dir( $dirname ) )
        {
            rmdir( $dirname );
        }

        return true;
    }

    /**
     * Tells Fruml not to output debug information.
     *
     * @return void
     */
    public static function skipDebug()
    {

        if ( !defined( 'SKIP_DEBUG' ) )
        {
            define( 'SKIP_DEBUG', true );
        }
    }

    /**
     * Pads a string.
     *
     * @param string $value The string to pad.
     * @param int $len The The length to pad the string to.
     * @param string $char The The character to pad with.
     * @return The padded string.
     */
    static function stringPad($value, $len = 2, $char = '0')
    {

        return str_pad( $value, $len, $char, STR_PAD_LEFT );
    }

    /**
     * Simple wrapper for retrieving remote files. Wrapped here for error_reporting issues. Tries to use cURL if available.
     *
     * @param string $url The URL to retrieve.
     * @param null $params
     * @return The contents of the remote file or false in case of failure (dns, timeout, bad url etc).
     */
    static function getRemoteFile($url, $params = null)
    {

        self::disableErrorHandling();

        if ( function_exists( 'curl_init' ) )
        {
            $ch      = curl_init();
            $request = '';

            if ( is_array( $params ) )
            {
                $isPost = false;
                if ( isset( $params[ 'request_method' ] ) )
                {
                    if ( strtolower( $params[ 'request_method' ] ) == 'post' )
                    {
                        $isPost = true;
                        curl_setopt( $ch, CURLOPT_POST, true );
                        curl_setopt( $ch, CURLINFO_CONTENT_TYPE, 'multipart/form-data' );
                    }
                    unset( $params[ 'request_method' ] );
                }


                if ( $isPost )
                {

                    curl_setopt( $ch, CURLOPT_POSTFIELDS, $params );
                }
            }


            curl_setopt( $ch, CURLOPT_URL, $url );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_HEADER, 0 );
            curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
            curl_setopt( $ch, CURLOPT_MAXREDIRS, 3 );
            curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
            curl_setopt( $ch, CURLOPT_USERAGENT, 'DreamCMS ' . VERSION );
            $res = curl_exec( $ch );
            curl_close( $ch );
        }
        else
        {
            $res = file_get_contents( $url );
        }

        self::enableErrorHandling();

        return $res;
    }

    /**
     *
     */
    static function disableSyntaxKeywords()
    {

        self::$useSyntaxKeywords = false;
    }

    /**
     *
     */
    static function enableSyntaxKeywords()
    {

        self::$useSyntaxKeywords = true;
    }

    /**
     * Returns pretty-printable version of $data.
     *
     * @param $data
     * @return string $data
     * @access public
     * @static
     */
    static public function prettyPrint($data)
    {

        // set html_errors to 1 just in case xdebug's installed. It'll give pretty colours :)
        $html_errors = ini_get( 'html_errors' );
        ini_set( 'html_errors', 1 );
        ob_start();
        echo '<pre>';
        var_dump( $data );
        echo '</pre>';
        ini_set( 'html_errors', $html_errors );

        return ob_get_clean();
    }

    /**
     * Pretty-formats the contents of a file. If GeSHi has been installed, it will be used, otherwise the file's contents are wrapped in a <pre> tag.
     *
     * @param        $path The file containing the code to highlight
     * @param bool $lines
     * @param string $strict
     * @return String containing the highlighted code.
     */
    static function syntaxHighlight($path, $lines = true, $strict = 'maybe')
    {

        if ( file_exists( VENDOR_PATH . 'geshi/geshi.php' ) && filesize( $path ) < 10024 && ( defined( 'USE_ERROR_HIGHLIGHT' ) && USE_ERROR_HIGHLIGHT !== false )
        )
        {
            require_once VENDOR_PATH . 'geshi/geshi.php';
            $geshi = new GeSHi();
            $geshi->set_header_type( GESHI_HEADER_DIV );
            $geshi->enable_keyword_links( false );
            $geshi->set_tab_width( 4 );
            $geshi->load_from_file( $path );

            if ( $strict == 'maybe' )
            {
                $strict = GESHI_MAYBE;
            }
            $geshi->enable_strict_mode( $strict );
            $geshi->set_overall_class( 'code-container' );
            $geshi->enable_classes();

            if ( $lines !== false )
            {
                $geshi->enable_line_numbers( GESHI_NORMAL_LINE_NUMBERS );
                $geshi->start_line_numbers_at( 0 );
                $geshi->highlight_lines_extra( $lines );
                $geshi->set_highlight_lines_extra_style( 'background-color: #FFDDDD' );
            }

            Library::disableErrorHandling();
            $ret = array(
                'code' => $geshi->parse_code(),
                'css'  => $geshi->get_stylesheet()
            );
            Library::enableErrorHandling();

            return $ret;
        }
        else
        {
            return array(
                'code' => '<pre class="code-container">' . htmlentities( file_get_contents( $path ) ) . '</pre>',
                'css'  => ''
            );
        }
    }

    /**
     * Pretty-formats the passed string. If GeSHi has been installed, it will be used, otherwise the passed string is wrapped in a <pre> tag.
     *
     * @param string $code The code to highlight
     * @param string $type
     * @param bool $lines
     * @param string $strict
     * @param int $start
     * @param null $use_line_numbers
     * @return String containing the highlighted code.
     */
    static function syntaxHighlightCode($code, $type = 'php', $lines = true, $strict = 'maybe', $start = 0, $use_line_numbers = null)
    {

        $len = Library::length( $code );
        if ( !$len )
        {
            return array();
        }

        if ( file_exists( VENDOR_PATH . 'geshi/geshi.php' ) && ( $len < 30000 || $type != 'php' ) && ( defined( 'USE_ERROR_HIGHLIGHT' ) && USE_ERROR_HIGHLIGHT !== false ) )
        {
            require_once VENDOR_PATH . 'geshi/geshi.php';

            if ( empty( $type ) )
            {
                $type = 'php';
            }


            $geshi = new GeSHi( $code, $type );
            $geshi->set_header_type( GESHI_HEADER_DIV );
            $geshi->enable_keyword_links( false );
            $geshi->set_tab_width( 4 );
            if ( $strict == 'maybe' )
            {
                $strict = GESHI_MAYBE;
            }

            $geshi->enable_strict_mode( $strict );
            $geshi->set_overall_class( 'code-container' );
            $geshi->enable_classes();

            if ( $lines !== false )
            {
                $geshi->enable_line_numbers( GESHI_NORMAL_LINE_NUMBERS );
                $geshi->highlight_lines_extra( $lines );
                $geshi->start_line_numbers_at( $start );
                $geshi->set_highlight_lines_extra_style( 'background-color: #FFDDDD' );
            }

            Library::disableErrorHandling();
            $ret = array(
                'code' => $geshi->parse_code(),
                'css'  => $geshi->get_stylesheet()
            );
            Library::enableErrorHandling();

            return $ret;
        }
        else
        {
            return array(
                'code' => '<pre class="code-container">' . htmlentities( $code ) . '</pre>',
                'css'  => ''
            );
        }
    }

    /**
     * Removes empty values in an array.
     *
     * @param array $array The array to unempty.
     * @return array containingno empty entries.
     */
    static function unempty(array $array)
    {

        $res = array();
        foreach ( $array as $key => $value )
        {
            if ( !empty( $value ) )
            {
                $res[ $key ] = $value;
            }
        }

        return $res;
    }

    /**
     * Removes entries with null values in an array.
     *
     * @param array $array The array to remove null values.
     * @return array containingno empty entries.
     */
    static function removeNullValues(array $array)
    {

        $res = array();
        foreach ( $array as $key => $value )
        {
            if ( !is_null( $value ) )
            {
                $res[ $key ] = $value;
            }
        }

        return $res;
    }

    /**
     * Merges any number of arrays of any dimensions, the later overwriting
     * previous keys, unless the key is numeric, in whitch case, duplicated
     * values will not be added.
     *
     * The arrays to be merged are passed as arguments to the function.
     *
     * @access public
     * @return array Resulting array, once all have been merged
     */
    static function arrayMergeReplaceRecursive()
    {

        self::disableErrorHandling();

        // Holds all the arrays passed
        $params = func_get_args();

        // First array is used as the base, everything else overwrites on it
        $return = array_shift( $params );

        if ( !is_array( $return ) )
        {
            $return = empty( $return ) ? array() : array(
                $return
            );
        }

        // Merge all arrays on the first array
        foreach ( $params as $array )
        {
            if ( !is_array( $array ) )
            {
                $array = array(
                    $array
                );
            }

            foreach ( $array as $key => $value )
            {
                if ( is_array( $value ) )
                {
                    $value = self::removeNullValues( $value );
                }


                // Numeric keyed values are added (unless already there)
                if ( is_numeric( $key ) && ( !in_array( $value, $return ) ) && isset( $return [ $$key ] ) )
                {
                    if ( is_array( $value ) )
                    {
                        $return[ ] = self::arrayMergeReplaceRecursive( $return [ $$key ], $value );
                    }
                    else
                    {
                        if ( !isset( $value [ $key ] ) && isset( $return [ $$key ] ) )
                        {
                            $return[ ] = $return [ $$key ];
                        }
                        else
                        {
                            $return[ ] = $value;
                        }
                    }

                    // String keyed values are replaced
                }
                else
                {

                    if ( isset( $return [ $key ] ) && is_array( $value ) && is_array( $return [ $key ] ) )
                    {
                        $return [ $key ] = self::arrayMergeReplaceRecursive( $return [ $$key ], $value );
                    }
                    else
                    {
                        if ( !isset( $value [ $key ] ) && isset( $return [ $$key ] ) )
                        {
                            $return[ ] = $return [ $$key ];
                        }
                        else
                        {
                            $return[ ] = $value;
                        }
                    }
                }
            }
        }

        self::enableErrorHandling();

        return $return;
    }

    /**
     * Shortens a string, adds dots
     *
     * @param               $string The string to shorten
     * @param int|\The $chars The number of characters to keep
     * @param bool|\Whether $dots Whether to add dots to the truncated string
     * @return String containing the shortened string.
     */
    static function truncate($string, $chars = 30, $dots = true)
    {

        if ( strlen( $string ) > $chars )
        {
            $string = substr( $string, 0, $chars );
            if ( $dots )
            {
                $string .= '...';
            }
        }

        return $string;
    }

    /**
     * Pauses execution for a while.
     *
     * @param int|\The $pause The number of seconds to pause
     * @param bool|\Only $debug_only Only pause is DEBUG is set to true
     * @return void
     */
    static function pause($pause = 2, $debug_only = true)
    {

        if ( ( $debug_only == true && DEBUG ) || !$debug_only )
        {
            sleep( $pause );
        }
    }

    /**
     * Create Order Fields
     *
     * @param array $fields
     * @param bool $order_by_field
     * @internal param $
     * @internal param $
     * @return void
     */
    static function orderFields($fields = array(), $order_by_field = false)
    {

    }

    /**
     * Create Sort the Order Fields
     *
     * @param string $fieldname
     * @param bool $do_sort
     * @internal param $
     * @internal param $
     * @return void
     */
    static function sortFields($fieldname = 'sort', $do_sort = false)
    {

    }

    /**
     * Pseudo-random UUID
     * Generates a universally unique identifier (UUID v4) according to RFC 4122
     * Version 4 UUIDs are pseudo-random UUID.
     *
     * @return string <type> String
     */
    public static function UUIDv4()
    {

        // 32 bits for "time_low"
        // 16 bits for "time_mid"
        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        // 48 bits for "node"
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0x0fff ) | 0x4000, mt_rand( 0, 0x3fff ) | 0x8000, mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ) );
    }

    /**
     * Pseudo-random UUID
     * Generates a universally unique identifier (UUID v3) according to RFC 4122
     * Version 3 UUIDs are Named-based UUID.
     *
     * @param $namespace
     * @param $name
     * @return bool|string <mixed> String or Bool
     */
    public static function UUIDv3($namespace, $name)
    {

        if ( !self::isValidUUID( $namespace ) )
        {
            return false;
        }

        // Get hexadecimal components of namespace
        $nhex = str_replace( array(
            '-',
            '{',
            '}'
        ), '', $namespace );

        // Binary Value
        $nstr = '';

        // Convert Namespace UUID to bits
        for ( $i = 0; $i < strlen( $nhex ); $i += 2 )
        {
            $nstr .= chr( hexdec( $nhex[ $i ] . $nhex[ $i + 1 ] ) );
        }

        // Calculate hash value
        $hash = md5( $nstr . $name );

        // 32 bits for "time_low"
        // 16 bits for "time_mid"
        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 3
        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        // 48 bits for "node"
        return sprintf( '%08s-%04s-%04x-%04x-%12s', substr( $hash, 0, 8 ), substr( $hash, 8, 4 ), ( hexdec( substr( $hash, 12, 4 ) ) & 0x0fff ) | 0x3000, ( hexdec( substr( $hash, 16, 4 ) ) & 0x3fff ) | 0x8000, substr( $hash, 20, 12 ) );
    }

    /**
     *
     * @param string $strings
     * @return string
     */
    public static function makeUUIDv3($strings)
    {

        $hash = md5( $strings );

        return sprintf( '%08s-%04s-%04s-%04s-%12s', substr( $hash, 0, 8 ), substr( $hash, 8, 4 ), substr( $hash, 12, 4 ), substr( $hash, 16, 4 ), substr( $hash, 20, 12 ) );
    }

    /**
     *
     * @param string $uuid
     * @return bool
     */
    public static function isValidUUID($uuid)
    {

        return ( preg_match( '/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $uuid ) === 1 ? true : false );
    }


    /**
     * Instantiates and returns a dcmsParser instance
     *
     * @param null $include_only
     * @param null $backend
     * @return object dcmsParser
     * @access public
     * @static
     */
    public static function getDcmsParser($include_only = null, $backend = null)
    {

        if ( !self::$tplParserLoad || class_exists( 'TplParser', false ) )
        {
            require_once( CLASS_PATH . 'tplengine/class_idcmsCompiler.php' );
            require_once( CLASS_PATH . 'tplengine/class_dcmsTemplate.php' );
            require_once( CLASS_PATH . 'tplengine/class_dcmsData.php' );
            require_once( CLASS_PATH . 'tplengine/class_dcmsCompiler.php' );
            require_once CLASS_PATH . 'tplengine/class_dcmsProcessor.php';
            require_once CLASS_PATH . 'tplengine/class_dcmsCData.php';
            require_once( CLASS_PATH . 'tplengine/class_dcmsParser.php' );

            self::$tplParserLoad = true;
        }
        else
        {
            self::$tplParserLoad = true;
        }


        if ( $include_only !== null )
        {
            return;
        }

        if ( defined( 'SYS_LOGS' ) && SYS_LOGS == 1 && $backend === null )
        {
            static $skinid;

            if ( empty( $skinid ) )
            {
                $skinid = User::getSkinId();

                if ( !is_dir( PAGE_CACHE_PATH . 'skins/skin_' . $skinid . '/compiled' ) )
                {
                    self::makeDirectory( PAGE_CACHE_PATH . 'skins/skin_' . $skinid . '/compiled/' );
                }
                if ( !is_dir( PAGE_CACHE_PATH . 'skins/skin_' . $skinid . '/template' ) )
                {
                    self::makeDirectory( PAGE_CACHE_PATH . 'skins/skin_' . $skinid . '/template/' );
                }
            }

            return new dcmsParser( SKIN_PATH . $skinid . '/html/', PAGE_CACHE_PATH . 'skins/skin_' . $skinid . '/compiled/', PAGE_CACHE_PATH . 'skins/skin_' . $skinid . '/template/' );
        }
        else
        {
            if ( !is_dir( CACHE_PATH . 'compiled/' ) )
            {
                self::makeDirectory( CACHE_PATH . 'compiled/' );
            }

            if ( !is_dir( CACHE_PATH . 'template/' ) )
            {
                self::makeDirectory( CACHE_PATH . 'template/' );
            }

            return new dcmsParser( BACKEND_TPL_PATH . 'html/', CACHE_PATH . 'compiled/', CACHE_PATH . 'template/' );
        }
    }

    /**
     * @param string $cadena
     * @return mixed
     */
    static function caracteres_latinos($cadena)
    {

        //acentos
        $cadena = preg_replace( "#(À|Á|Â|Ã|Ä|Å|à|á|â|ã|ä|å)#", "Ae", $cadena );
        $cadena = preg_replace( "#(à|á|â|ã|ä|å)#", "ae", $cadena );
        $cadena = preg_replace( "#(È|É|Ê|Ë|è|é|ê|ë)#", "e", $cadena );
        $cadena = preg_replace( "#(Ì|Í|Î|Ï|ì|í|î|ï)#", "i", $cadena );
        $cadena = preg_replace( "#(Ò|Ó|Ô|Õ|Ö|Ø)#", "Oe", $cadena );
        $cadena = preg_replace( "#(Ø|ò|ó|ô|õ|ö|ø)#", "oe", $cadena );
        $cadena = preg_replace( "#(ù|ú|û|ü)#", "ue", $cadena );
        $cadena = preg_replace( "#(Ù|Ú|Û|Ü)#", "Ue", $cadena );
        //la ñ
        $cadena = preg_replace( "#(Ñ|ñ)#", "n", $cadena );

        //caracteres extraños
        $cadena = preg_replace( "#(Ç|ç)#", "c", $cadena );
        $cadena = str_replace( "ÿ", "y", $cadena );

        return $cadena;
    }

    /**
     * @param string $string
     * @return mixed
     */
    static function noDiacritics($string)
    {

        static $utf8From;


        //cyrylic transcription
        $cyrylicFrom = array(
            'А',
            'Б',
            'В',
            'Г',
            'Д',
            'Е',
            'Ё',
            'Ж',
            'З',
            'И',
            'Й',
            'К',
            'Л',
            'М',
            'Н',
            'О',
            'П',
            'Р',
            'С',
            'Т',
            'У',
            'Ф',
            'Х',
            'Ц',
            'Ч',
            'Ш',
            'Щ',
            'Ъ',
            'Ы',
            'Ь',
            'Э',
            'Ю',
            'Я',
            'а',
            'б',
            'в',
            'г',
            'д',
            'е',
            'ё',
            'ж',
            'з',
            'и',
            'й',
            'к',
            'л',
            'м',
            'н',
            'о',
            'п',
            'р',
            'с',
            'т',
            'у',
            'ф',
            'х',
            'ц',
            'ч',
            'ш',
            'щ',
            'ъ',
            'ы',
            'ь',
            'э',
            'ю',
            'я'
        );
        $cyrylicTo   = array(
            'A',
            'B',
            'W',
            'G',
            'D',
            'Ie',
            'Io',
            'Z',
            'Z',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'R',
            'S',
            'T',
            'U',
            'F',
            'Ch',
            'C',
            'Tch',
            'Sh',
            'Shtch',
            '',
            'Y',
            '',
            'E',
            'Iu',
            'Ia',
            'a',
            'b',
            'w',
            'g',
            'd',
            'ie',
            'io',
            'z',
            'z',
            'i',
            'j',
            'k',
            'l',
            'm',
            'n',
            'o',
            'p',
            'r',
            's',
            't',
            'u',
            'f',
            'ch',
            'c',
            'tch',
            'sh',
            'shtch',
            '',
            'y',
            '',
            'e',
            'iu',
            'ia'
        );


        $from = array(
            "Á",
            "À",
            "Â",
            "Ä",
            "Ă",
            "Ā",
            "Ã",
            "Å",
            "Ą",
            "Æ",
            "Ć",
            "Ċ",
            "Ĉ",
            "Č",
            "Ç",
            "Ď",
            "Đ",
            "Ð",
            "É",
            "È",
            "Ė",
            "Ê",
            "Ë",
            "Ě",
            "Ē",
            "Ę",
            "Ə",
            "Ġ",
            "Ĝ",
            "Ğ",
            "Ģ",
            "á",
            "à",
            "â",
            "ä",
            "ă",
            "ā",
            "ã",
            "å",
            "ą",
            "æ",
            "ć",
            "ċ",
            "ĉ",
            "č",
            "ç",
            "ď",
            "đ",
            "ð",
            "é",
            "è",
            "ė",
            "ê",
            "ë",
            "ě",
            "ē",
            "ę",
            "ə",
            "ġ",
            "ĝ",
            "ğ",
            "ģ",
            "Ĥ",
            "Ħ",
            "I",
            "Í",
            "Ì",
            "İ",
            "Î",
            "Ï",
            "Ī",
            "Į",
            "Ĳ",
            "Ĵ",
            "Ķ",
            "Ļ",
            "Ł",
            "Ń",
            "Ň",
            "Ñ",
            "Ņ",
            "Ó",
            "Ò",
            "Ô",
            "Ö",
            "Õ",
            "Ő",
            "Ø",
            "Ơ",
            "Œ",
            "ĥ",
            "ħ",
            "ı",
            "í",
            "ì",
            "i",
            "î",
            "ï",
            "ī",
            "į",
            "ĳ",
            "ĵ",
            "ķ",
            "ļ",
            "ł",
            "ń",
            "ň",
            "ñ",
            "ņ",
            "ó",
            "ò",
            "ô",
            "ö",
            "õ",
            "ő",
            "ø",
            "ơ",
            "œ",
            "Ŕ",
            "Ř",
            "Ś",
            "Ŝ",
            "Š",
            "Ş",
            "Ť",
            "Ţ",
            "Þ",
            "Ú",
            "Ù",
            "Û",
            "Ü",
            "Ŭ",
            "Ū",
            "Ů",
            "Ų",
            "Ű",
            "Ư",
            "Ŵ",
            "Ý",
            "Ŷ",
            "Ÿ",
            "Ź",
            "Ż",
            "Ž",
            "ŕ",
            "ř",
            "ś",
            "ŝ",
            "š",
            "ş",
            "ß",
            "ť",
            "ţ",
            "þ",
            "ú",
            "ù",
            "û",
            "ü",
            "ŭ",
            "ū",
            "ů",
            "ų",
            "ű",
            "ư",
            "ŵ",
            "ý",
            "ŷ",
            "ÿ",
            "ź",
            "ż",
            "ž"
        );
        $to   = array(
            "A",
            "A",
            "A",
            "A",
            "A",
            "A",
            "A",
            "A",
            "A",
            "AE",
            "C",
            "C",
            "C",
            "C",
            "C",
            "D",
            "D",
            "D",
            "E",
            "E",
            "E",
            "E",
            "E",
            "E",
            "E",
            "E",
            "G",
            "G",
            "G",
            "G",
            "G",
            "a",
            "a",
            "a",
            "a",
            "a",
            "a",
            "a",
            "a",
            "a",
            "ae",
            "c",
            "c",
            "c",
            "c",
            "c",
            "d",
            "d",
            "d",
            "e",
            "e",
            "e",
            "e",
            "e",
            "e",
            "e",
            "e",
            "g",
            "g",
            "g",
            "g",
            "g",
            "H",
            "H",
            "I",
            "I",
            "I",
            "I",
            "I",
            "I",
            "I",
            "I",
            "IJ",
            "J",
            "K",
            "L",
            "L",
            "N",
            "N",
            "N",
            "N",
            "O",
            "O",
            "O",
            "O",
            "O",
            "O",
            "O",
            "O",
            "CE",
            "h",
            "h",
            "i",
            "i",
            "i",
            "i",
            "i",
            "i",
            "i",
            "i",
            "ij",
            "j",
            "k",
            "l",
            "l",
            "n",
            "n",
            "n",
            "n",
            "o",
            "o",
            "o",
            "o",
            "o",
            "o",
            "o",
            "o",
            "o",
            "R",
            "R",
            "S",
            "S",
            "S",
            "S",
            "T",
            "T",
            "T",
            "U",
            "U",
            "U",
            "U",
            "U",
            "U",
            "U",
            "U",
            "U",
            "U",
            "W",
            "Y",
            "Y",
            "Y",
            "Z",
            "Z",
            "Z",
            "r",
            "r",
            "s",
            "s",
            "s",
            "s",
            "B",
            "t",
            "t",
            "b",
            "u",
            "u",
            "u",
            "u",
            "u",
            "u",
            "u",
            "u",
            "u",
            "u",
            "w",
            "y",
            "y",
            "y",
            "z",
            "z",
            "z"
        );


        $from = array_merge( $from, $cyrylicFrom );
        $to   = array_merge( $to, $cyrylicTo );

        if ( !is_array( $utf8From ) )
        {
            $utf8From = array();
            foreach ( $from as $idx => $v )
            {
                $utf8From[ ] = chr( ord( $v ) );
            }
        }

        $string = str_replace( $utf8From, $to, $string );
        $string = str_replace( $from, $to, $string );

        return $newstring;
    }

    /**
     *
     * @param string $output
     * @return string
     */
    static function symbols_to_words($output)
    {

        $output = str_replace( '@', ' at ', $output );
        $output = str_replace( '%', ' percent ', $output );
        $output = str_replace( '&', ' and ', $output );
        $output = str_replace( '&amp;', ' and ', $output );

        return $output;
    }

    /**
     * @var array
     */
    static $char_map = array(
        // Latin
        'À' => 'A',
        'Á' => 'A',
        'Â' => 'A',
        'Ã' => 'A',
        'Ä' => 'Ae',
        'Å' => 'A',
        'Æ' => 'AE',
        'Ç' => 'C',
        'È' => 'E',
        'É' => 'E',
        'Ê' => 'E',
        'Ë' => 'E',
        'Ì' => 'I',
        'Í' => 'I',
        'Î' => 'I',
        'Ï' => 'I',
        'Ð' => 'D',
        'Ñ' => 'N',
        'Ò' => 'O',
        'Ó' => 'O',
        'Ô' => 'O',
        'Õ' => 'O',
        'Ö' => 'Oe',
        'Ő' => 'O',
        'Ø' => 'O',
        'Ù' => 'U',
        'Ú' => 'U',
        'Û' => 'U',
        'Ü' => 'Ue',
        'Ű' => 'U',
        'Ý' => 'Y',
        'Þ' => 'TH',
        'ß' => 'ss',
        'à' => 'a',
        'á' => 'a',
        'â' => 'a',
        'ã' => 'a',
        'ä' => 'ae',
        'å' => 'a',
        'æ' => 'ae',
        'ç' => 'c',
        'è' => 'e',
        'é' => 'e',
        'ê' => 'e',
        'ë' => 'e',
        'ì' => 'i',
        'í' => 'i',
        'î' => 'i',
        'ï' => 'i',
        'ð' => 'd',
        'ñ' => 'n',
        'ò' => 'o',
        'ó' => 'o',
        'ô' => 'o',
        'õ' => 'o',
        'ö' => 'oe',
        'ő' => 'o',
        'ø' => 'o',
        'ù' => 'u',
        'ú' => 'u',
        'û' => 'u',
        'ü' => 'ue',
        'ű' => 'u',
        'ý' => 'y',
        'þ' => 'th',
        'ÿ' => 'y',
        // Latin symbols
        '©' => '(c)',
        // Greek
        'Α' => 'A',
        'Β' => 'B',
        'Γ' => 'G',
        'Δ' => 'D',
        'Ε' => 'E',
        'Ζ' => 'Z',
        'Η' => 'H',
        'Θ' => '8',
        'Ι' => 'I',
        'Κ' => 'K',
        'Λ' => 'L',
        'Μ' => 'M',
        'Ν' => 'N',
        'Ξ' => '3',
        'Ο' => 'O',
        'Π' => 'P',
        'Ρ' => 'R',
        'Σ' => 'S',
        'Τ' => 'T',
        'Υ' => 'Y',
        'Φ' => 'F',
        'Χ' => 'X',
        'Ψ' => 'PS',
        'Ω' => 'W',
        'Ά' => 'A',
        'Έ' => 'E',
        'Ί' => 'I',
        'Ό' => 'O',
        'Ύ' => 'Y',
        'Ή' => 'H',
        'Ώ' => 'W',
        'Ϊ' => 'I',
        'Ϋ' => 'Y',
        'α' => 'a',
        'β' => 'b',
        'γ' => 'g',
        'δ' => 'd',
        'ε' => 'e',
        'ζ' => 'z',
        'η' => 'h',
        'θ' => '8',
        'ι' => 'i',
        'κ' => 'k',
        'λ' => 'l',
        'μ' => 'm',
        'ν' => 'n',
        'ξ' => '3',
        'ο' => 'o',
        'π' => 'p',
        'ρ' => 'r',
        'σ' => 's',
        'τ' => 't',
        'υ' => 'y',
        'φ' => 'f',
        'χ' => 'x',
        'ψ' => 'ps',
        'ω' => 'w',
        'ά' => 'a',
        'έ' => 'e',
        'ί' => 'i',
        'ό' => 'o',
        'ύ' => 'y',
        'ή' => 'h',
        'ώ' => 'w',
        'ς' => 's',
        'ϊ' => 'i',
        'ΰ' => 'y',
        'ϋ' => 'y',
        'ΐ' => 'i',
        // Russian
        'А' => 'A',
        'Б' => 'B',
        'В' => 'V',
        'Г' => 'G',
        'Д' => 'D',
        'Е' => 'E',
        'Ё' => 'Yo',
        'Ж' => 'Zh',
        'З' => 'Z',
        'И' => 'I',
        'Й' => 'J',
        'К' => 'K',
        'Л' => 'L',
        'М' => 'M',
        'Н' => 'N',
        'О' => 'O',
        'П' => 'P',
        'Р' => 'R',
        'С' => 'S',
        'Т' => 'T',
        'У' => 'U',
        'Ф' => 'F',
        'Х' => 'H',
        'Ц' => 'C',
        'Ч' => 'Ch',
        'Ш' => 'Sh',
        'Щ' => 'Sh',
        'Ъ' => '',
        'Ы' => 'Y',
        'Ь' => '',
        'Э' => 'E',
        'Ю' => 'Yu',
        'Я' => 'Ya',
        'а' => 'a',
        'б' => 'b',
        'в' => 'v',
        'г' => 'g',
        'д' => 'd',
        'е' => 'e',
        'ё' => 'yo',
        'ж' => 'zh',
        'з' => 'z',
        'и' => 'i',
        'й' => 'j',
        'к' => 'k',
        'л' => 'l',
        'м' => 'm',
        'н' => 'n',
        'о' => 'o',
        'п' => 'p',
        'р' => 'r',
        'с' => 's',
        'т' => 't',
        'у' => 'u',
        'ф' => 'f',
        'х' => 'h',
        'ц' => 'c',
        'ч' => 'ch',
        'ш' => 'sh',
        'щ' => 'sh',
        'ъ' => '',
        'ы' => 'y',
        'ь' => '',
        'э' => 'e',
        'ю' => 'yu',
        'я' => 'ya',
        // Ukrainian
        'Є' => 'Ye',
        'І' => 'I',
        'Ї' => 'Yi',
        'Ґ' => 'G',
        'є' => 'ye',
        'і' => 'i',
        'ї' => 'yi',
        'ґ' => 'g',
        // Czech
        'Č' => 'C',
        'Ď' => 'D',
        'Ě' => 'E',
        'Ň' => 'N',
        'Ř' => 'R',
        'Š' => 'S',
        'Ť' => 'T',
        'Ů' => 'U',
        'Ž' => 'Z',
        'č' => 'c',
        'ď' => 'd',
        'ě' => 'e',
        'ň' => 'n',
        'ř' => 'r',
        'š' => 's',
        'ť' => 't',
        'ů' => 'u',
        'ž' => 'z',
        // Polish
        'Ą' => 'A',
        'Ć' => 'C',
        'Ę' => 'e',
        'Ł' => 'L',
        'Ń' => 'N',
        'Ó' => 'o',
        'Ś' => 'S',
        'Ź' => 'Z',
        'Ż' => 'Z',
        'ą' => 'a',
        'ć' => 'c',
        'ę' => 'e',
        'ł' => 'l',
        'ń' => 'n',
        'ó' => 'o',
        'ś' => 's',
        'ź' => 'z',
        'ż' => 'z',
        // Latvian
        'Ā' => 'A',
        'Č' => 'C',
        'Ē' => 'E',
        'Ģ' => 'G',
        'Ī' => 'i',
        'Ķ' => 'k',
        'Ļ' => 'L',
        'Ņ' => 'N',
        'Š' => 'S',
        'Ū' => 'u',
        'Ž' => 'Z',
        'ā' => 'a',
        'č' => 'c',
        'ē' => 'e',
        'ģ' => 'g',
        'ī' => 'i',
        'ķ' => 'k',
        'ļ' => 'l',
        'ņ' => 'n',
        'š' => 's',
        'ū' => 'u',
        'ž' => 'z'
    );

    /**
     * Create a slug of giving string
     *
     * @param string $name
     * @param bool|string $addExtension (default is false and will not add the extension)
     * @return string
     */
    static function suggest($name = '', $addExtension = false)
    {

        if ( !is_string( $name ) )
        {
            return $name;
        }

        $name = Strings::fixLatin( strip_tags( $name ) );


        $name = Strings::unhtmlspecialchars( $name, true );
        $name = html_entity_decode( $name );
        $name = self::symbols_to_words( $name );

        $name = preg_replace( '/([:,;\.\?\/\\#\*\+~\^\$\=]*)/is', '', $name );

        $name = preg_replace( '/\s+/s', '-', $name );
        $name = str_replace( array_keys( self::$char_map ), array_values( self::$char_map ), $name );
        $name = str_replace( ' ', '-', $name );
        $name = preg_replace( '/([-]{1,})/', '-', $name );
        $name = preg_replace( '/-+/', '-', $name ); // Clean up extra dashes

        $name = preg_replace( '/^-/', '', $name );
        $name = trim( preg_replace( '/-$/', '', $name ) );


        if ( $addExtension === true )
        {
            $name = $name . '.' . Settings::get( 'mod_rewrite_suffix' );
        }
        elseif ( is_string( $addExtension ) && $addExtension !== '' )
        {
            $name = $name . '.' . $addExtension;
        }

        return $name;
    }

    /**
     * Get Page identifier (URL)
     *
     * @return string
     * @param $page the name is giving
     * @param $include_host
     * @access public
     * @static
     */
    static function getUrl($page, $include_host = false)
    {

        if ( empty( $page[ 'alias' ] ) )
        {
            return '';
        }

        $url = '';
        if ( $include_host )
        {
            $url = Settings::get( 'portalurl' );
        }

        if ( Strings::is_utf8( $page[ 'alias' ] ) )
        {
            $page[ 'alias' ] = utf8_decode( $page[ 'alias' ] );
        }

        $url .= $page[ 'alias' ];
        $url .= ( !empty( $page[ 'suffix' ] ) && $page[ 'suffix' ] != '-' ? '.' . $page[ 'suffix' ] : '' );

        return $url;
    }

    /**
     * num_to_image()
     *
     * @param integer $n
     * @return string
     */
    public static function makeRatingImg($n = 0)
    {

        if ( $n < 0.25 || $n > 5 )
        {
            return "_0";
        }
        if ( $n >= 0.25 && $n < 0.75 )
        {
            return "0half";
        }
        if ( $n >= 0.75 && $n < 1.25 )
        {
            return "_1";
        }
        if ( $n >= 1.25 && $n < 1.75 )
        {
            return "1half";
        }
        if ( $n >= 1.75 && $n < 2.25 )
        {
            return "_2";
        }
        if ( $n >= 2.25 && $n < 2.75 )
        {
            return "2half";
        }
        if ( $n >= 2.75 && $n < 3.25 )
        {
            return "_3";
        }
        if ( $n >= 3.25 && $n < 3.75 )
        {
            return "3half";
        }
        if ( $n >= 3.75 && $n < 4.25 )
        {
            return "_4";
        }
        if ( $n >= 4.25 && $n < 4.75 )
        {
            return "4half";
        }
        if ( $n >= 4.75 && $n <= 5 )
        {
            return "_5";
        }
    }

    /**
     * @param int $page
     * @param int $pages
     * @param string $link
     * @return string
     */
    static function paging($page = 1, $pages = 1, $link = '')
    {

        if ( $pages == 1 )
        {
            return '';
        }
        $paging = new Paging();

        return $paging->setPaging( $link, $page, $pages );
    }

    /**
     *
     * @param string $title
     * @param string $link is optional
     */
    static function addNavi($title, $link = '')
    {

        self::$navigation[ ] = array(
            'title' => $title,
            'url'   => $link
        );
    }

    /**
     *
     * @return array
     */
    static function getNavi()
    {

        $application = Registry::getObject( 'Application' );
        if ( ( $application instanceof Application ) && $application->getMode() !== Application::BACKEND_MODE )
        {
            $application->load( 'Breadcrumb' );

            return $application->Breadcrumb->get();
        }

        return self::$navigation;
    }

    /**
     * Set a Active Menu Item from Controller
     * $actPage contains the formated Url Rule (incl. all data)
     *
     * @param string $actPage
     */
    static function setActivePage($actPage = null)
    {

        if ( !is_null( $actPage ) )
        {
            self::$activePage = $actPage;
        }
    }

    /**
     * Call from Page
     * will return the active page as string (formated Url Rule) or return null
     *
     * @return mixed
     */
    static function getActivePage()
    {

        return self::$activePage;
    }

    /**
     * Get Timezones will return a array of all Timezones
     *
     * @return array
     */
    static function getTimezones()
    {

        $timezoneTable = array(
            "-12"  => "(GMT -12:00) Eniwetok, Kwajalein",
            "-11"  => "(GMT -11:00) Midway Island, Samoa",
            "-10"  => "(GMT -10:00) Hawaii",
            "-9"   => "(GMT -9:00) Alaska",
            "-8"   => "(GMT -8:00) Pacific Time (US &amp; Canada)",
            "-7"   => "(GMT -7:00) Mountain Time (US &amp; Canada)",
            "-6"   => "(GMT -6:00) Central Time (US &amp; Canada), Mexico City",
            "-5"   => "(GMT -5:00) Eastern Time (US &amp; Canada), Bogota, Lima",
            "-4"   => "(GMT -4:00) Atlantic Time (Canada), Caracas, La Paz",
            "-3.5" => "(GMT -3:30) Newfoundland",
            "-3"   => "(GMT -3:00) Brazil, Buenos Aires, Georgetown",
            "-2"   => "(GMT -2:00) Mid-Atlantic",
            "-1"   => "(GMT -1:00 hour) Azores, Cape Verde Islands",
            "0"    => "(GMT) Western Europe Time, London, Lisbon, Casablanca",
            "1"    => "(GMT +1:00 hour) Brussels, Copenhagen, Madrid, Paris",
            "2"    => "(GMT +2:00) Kaliningrad, South Africa",
            "3"    => "(GMT +3:00) Baghdad, Riyadh, Moscow, St. Petersburg",
            "3.5"  => "(GMT +3:30) Tehran",
            "4"    => "(GMT +4:00) Abu Dhabi, Muscat, Baku, Tbilisi",
            "4.5"  => "(GMT +4:30) Kabul",
            "5"    => "(GMT +5:00) Ekaterinburg, Islamabad, Karachi, Tashkent",
            "5.5"  => "(GMT +5:30) Bombay, Calcutta, Madras, New Delhi",
            "6"    => "(GMT +6:00) Almaty, Dhaka, Colombo",
            "7"    => "(GMT +7:00) Bangkok, Hanoi, Jakarta",
            "8"    => "(GMT +8:00) Beijing, Perth, Singapore, Hong Kong",
            "9"    => "(GMT +9:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk",
            "9.5"  => "(GMT +9:30) Adelaide, Darwin",
            "10"   => "(GMT +10:00) Eastern Australia, Guam, Vladivostok",
            "11"   => "(GMT +11:00) Magadan, Solomon Islands, New Caledonia",
            "12"   => "(GMT +12:00) Auckland, Wellington, Fiji, Kamchatka"
        );

        return $timezoneTable;
    }

    /**
     * Get all System Tools
     *
     * @return array
     * @deprecated since version 3.0 alpha
     */
    static function getSystemModules()
    {

        $Table = array(
            'main'              => trans( "Startseite" ),
            'faq'               => trans( "FAQ" ),
            'guestbook'         => trans( "Gästebuch" ),
            'user'              => trans( "User Controllpanel" ),
            'profile'           => trans( "Profil eines Mitgliedes" ),
            'messenger'         => trans( "Private Nachrichten" ),
            'container_gallery' => trans( "Bilder Gallery" ),
            'container_picture' => trans( "Galeriebild" ),
            'members'           => trans( "Liste der registrierten Mitglieder" ),
            'register'          => trans( "Registrierung" ),
            'lostpassword'      => trans( "Passwort vergessen" ),
            'login'             => trans( "Login Seite" ),
            'logout'            => trans( "Logout Seite" ),
            'static'            => trans( "Statische Seiten" ),
            'apps'              => trans( "Anwendungen" ),
            'appcat'            => trans( "Anwendungen in der Kategorie Ansicht" ),
            'appitem'           => trans( "Anwendungen in der Artikel Ansicht" ),
            'news'              => trans( "News Artikel" ),
            'newsarchiv'        => trans( "News Archive" ),
            'contact'           => trans( "Kontakt" ),
            'team'              => trans( "Website Team" ),
            'calendar'          => trans( "Kalender" ),
            'mail'              => trans( "Formmailder / Weiterempfehlen" ),
            'misc'              => trans( "Sonstige Seiten" ),
            'search'            => trans( "Suchen" ),
            'forum'             => trans( "Forum" ),
            'author'            => trans( "Autoren" ),
            'poll'              => trans( "Umfragen" ),
            'sitemap'           => trans( "Sitemap" ),
            'error'             => trans( "Fehler Seiten (404/403)" ),
        );

        return $Table;
    }

    /**
     * Generate a random String by Chars
     *
     * @param int $length length of the String
     * @param bool $charsonly if true then will build a randum cahr string not with numbers, when false then build the random string with numbers and chars
     * @return string returns the random String
     */
    static function getRandomChars($length = 6, $charsonly = false)
    {

        $chars   = 'A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z,a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z';
        $numbers = '1,2,3,4,5,6,7,8,9';
        if ( !$charsonly )
        {
            $chars .= ',' . $numbers;
        }

        if ( $length < 1 )
        {
            $length = 2;
        }

        srand( ( double )microtime() * 1000000 );

        $i   = 1;
        $str = '';

        $chr = explode( ',', $chars );

        shuffle( $chr );


        $nums = count( $chr ) - 1;

        while ( $i <= $length )
        {
            $num = rand( 0, $nums );
            $str .= $chr[ $num ];

            shuffle( $chr );
            $i++;
        }

        return $str;
    }

    /**
     *
     * @param array $array
     * @return array|string
     */
    static function serialize($array)
    {

        if ( is_array( $array ) )
        {
            return '[dcms-serialized]' . serialize( $array );
        }

        return $array;
    }

    /**
     *
     * @param string $string
     * @return mixed|string
     */
    static function unserialize($string = '')
    {

        if ( substr( ltrim( $string ), 0, 17 ) == '[dcms-serialized]' )
        {
            $string = substr( ltrim( $string ), 17 );

            return unserialize( $string );
        }

        return $string;
    }

    static function cleanSearchString($search)
    {

        return preg_replace( '/[^\pN\pL \*\+\'"\.:,_-]/iu', " ", $search );
    }

    /**
     *
     *  Order array by keys
     *
     *   $arr1 = array(
     *       array('id'=>1,'name'=>'aA','cat'=>'cc'),
     *       array('id'=>2,'name'=>'aa','cat'=>'dd'),
     *       array('id'=>3,'name'=>'bb','cat'=>'cc'),
     *       array('id'=>4,'name'=>'bb','cat'=>'dd')
     *   );
     *
     *   $arr2 = array_msort($arr1, array('name'=>SORT_DESC, 'cat'=>SORT_ASC));
     *
     * @param array $array
     * @param array $cols
     * @return array
     */
    static function array_msort($array, $cols)
    {

        $colarr = array();
        foreach ( $cols as $col => $order )
        {
            $colarr[ $col ] = array();
            foreach ( $array as $k => $row )
            {
                $colarr[ $col ][ '_' . $k ] = strtolower( $row[ $col ] );
            }
        }
        $eval = 'array_multisort(';
        foreach ( $cols as $col => $order )
        {
            $eval .= '$colarr[\'' . $col . '\'],' . $order . ',';
        }
        $eval = substr( $eval, 0, -1 ) . ');';
        eval( $eval );
        $ret = array();
        foreach ( $colarr as $col => $arr )
        {
            foreach ( $arr as $k => $v )
            {
                $k = substr( $k, 1 );
                if ( !isset( $ret[ $k ] ) )
                {
                    $ret[ $k ] = $array[ $k ];
                }
                $ret[ $k ][ $col ] = $array[ $k ][ $col ];
            }
        }

        return $ret;
    }

    /**
     * @param $v1
     * @param $v2
     */
    private static function swap(&$v1, &$v2)
    {

        $v1 = $v1 ^ $v2;
        $v2 = $v1 ^ $v2;
        $v1 = $v1 ^ $v2;
    }

    /**
     * Make, store and returns the permutation vector about the key.
     *
     * @param string $key Key
     * @return array
     */
    private static function KSA($key)
    {

        $idx = crc32( $key );
        if ( !isset( self::$S[ $idx ] ) )
        {
            $S = range( 0, 255 );
            $j = 0;
            $n = strlen( $key );
            for ( $i = 0; $i < 255; $i++ )
            {
                $char = ord( $key{$i % $n} );
                $j    = ( $j + $S[ $i ] + $char ) % 256;
                self::swap( $S[ $i ], $S[ $j ] );
            }
            self::$S[ $idx ] = $S;
        }

        return self::$S[ $idx ];
    }

    /**
     * @param string $encrypt
     * @return string
     */
    static function encrypt($encrypt)
    {

        $S = self::KSA( self::$cryptkey );
        $n = strlen( $encrypt );
        $i = $j = 0;

        $data = str_split( $encrypt, 1 );

        for ( $m = 0; $m < $n; $m++ )
        {
            $i = ( $i + 1 ) % 256;
            $j = ( $j + $S[ $i ] ) % 256;

            self::swap( $S[ $i ], $S[ $j ] );

            $char       = ord( $data{$m} );
            $char       = $S[ ( $S[ $i ] + $S[ $j ] ) % 256 ] ^ $char;
            $data[ $m ] = chr( $char );
        }
        $data = implode( '', $data );

        return $data;
    }

    /**
     * @param string $decrypt
     * @return string
     */
    static function decrypt($decrypt)
    {

        return self::encrypt( $decrypt );
    }

    /**
     * Convert Objects to Array
     *
     * @param object $obj
     * @return array
     */
    static function object2array($obj)
    {

        $_arr = is_object( $obj ) ? get_object_vars( $obj ) : $obj;
        foreach ( $_arr as $key => $val )
        {
            $val         = ( is_array( $val ) || is_object( $val ) ) ? self::object2array( $val ) : $val;
            $arr[ $key ] = $val;
        }

        return $arr;
    }

    /**
     * @param $number
     * @param $threshold
     * @return string
     */
    static function zeroise($number, $threshold)
    {

        return sprintf( '%0' . $threshold . 's', $number );
    }

    /**
     * get Protected Email Adress
     *
     * @param string $emailaddy
     * @param int $mailto
     * @return string of protected Email
     */
    static function protectEmail($emailaddy, $mailto = 0)
    {

        if ( isset( self::$protectedEmails[ $emailaddy ] ) )
        {
            return self::$protectedEmails[ $emailaddy ];
        }

        $strMailto = "&#109;&#097;&#105;&#108;&#116;&#111;&#058;";


        $emailNOSPAMaddy = '';
        srand( ( float )microtime() * 1000 );


        $len = strlen( $emailaddy );
        for ( $i = 0; $i < $len; $i = $i + 1 )
        {
            $j = floor( rand( 0, 1 + $mailto ) );

            if ( $j == 0 )
            {
                $emailNOSPAMaddy .= '&#' . ord( substr( $emailaddy, $i, 1 ) ) . ';';
            }
            elseif ( $j == 1 )
            {
                $emailNOSPAMaddy .= substr( $emailaddy, $i, 1 );
            }
            elseif ( $j == 2 )
            {
                $emailNOSPAMaddy .= '%' . self::zeroise( dechex( ord( substr( $emailaddy, $i, 1 ) ) ), 2 );
            }
        }


        self::$protectedEmails[ $emailaddy ] = str_replace( '@', '&#64;', $emailNOSPAMaddy );

        return self::$protectedEmails[ $emailaddy ];
    }

    /**
     *
     * @param string $name
     * @return mixed
     */
    static function cleanDirName($name)
    {

        $clean = str_replace( "\0", '', $name );
        $clean = preg_replace( '/([^a-z0-9_\-]*)/is', '', $clean );


        if ( IS_AJAX && $clean !== $name )
        {
            self::sendJson( false, 'Invalid Dirname!!!' );
        }
        elseif ( $clean !== $name )
        {

        }

        return $clean;
    }

    /**
     *
     * @param string $path
     */
    static function cleanPath($path)
    {

    }

    /**
     * @param $string
     * @return mixed
     * @deprecated since version 3.0 alpha
     */
    static function cleanXXS($string)
    {

        $string = str_replace( array(
            "&amp;",
            "&lt;",
            "&gt;"
        ), array(
            "&amp;amp;",
            "&amp;lt;",
            "&amp;gt;"
        ), $string );

        // fix &entitiy\n;
        $string = preg_replace( '#(&\#*\w+)[\x00-\x20]+;#u', "$1;", $string );
        $string = preg_replace( '#(&\#x*)([0-9A-F]+);*#iu', "$1$2;", $string );

        $string = html_entity_decode( $string, ENT_COMPAT, "UTF-8" );

        // remove any attribute starting with "on" or xmlns
        $string = preg_replace( '#(<[^>]+[\x00-\x20\"\'\/])(on|xmlns)[^>]*>#iUu', "$1>", $string );

        // remove javascript: and vbscript: protocol
        $string = preg_replace( '#([a-z]*)[\x00-\x20\/]*=[\x00-\x20\/]*([\`\'\"]*)[\x00-\x20\/]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iUu', '$1=$2nojavascript...', $string );
        $string = preg_replace( '#([a-z]*)[\x00-\x20\/]*=[\x00-\x20\/]*([\`\'\"]*)[\x00-\x20\/]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iUu', '$1=$2novbscript...', $string );
        $string = preg_replace( '#([a-z]*)[\x00-\x20\/]*=[\x00-\x20\/]*([\`\'\"]*)[\x00-\x20\/]*-moz-binding[\x00-\x20]*:#Uu', '$1=$2nomozbinding...', $string );
        $string = preg_replace( '#([a-z]*)[\x00-\x20\/]*=[\x00-\x20\/]*([\`\'\"]*)[\x00-\x20\/]*data[\x00-\x20]*:#Uu', '$1=$2nodata...', $string );

        //remove any style attributes, IE allows too much stupid things in them, eg.
        //<span style="width: expression(alert('Ping!'));"></span>
        // and in general you really don't want style declarations in your UGC

        $string = preg_replace( '#(<[^>]+[\x00-\x20\"\'\/])style[^>]*>#iUu', "$1>", $string );

        //remove namespaced elements (we do not need them...)
        $string = preg_replace( '#</*\w+:\w[^>]*>#i', "", $string );

        //remove really unwanted tags
        do
        {
            $oldstring = $string;
            $string    = preg_replace( '#</*(applet|meta|xml|blink|link|style|script|embed|object|iframe|frame|frameset|ilayer|layer|bgsound|title|base)[^>]*>#i', "", $string );
        }
        while ( $oldstring != $string );

        return $string;
    }

    /**
     * @param $seconds
     * @return array
     */
    static function getTimespanArray($seconds)
    {

        $td          = array();
        $td[ 'sec' ] = $seconds % 60;
        $td[ 'min' ] = ( ( $seconds - $td[ 'sec' ] ) / 60 ) % 60;
        $td[ 'std' ] = ( ( ( ( $seconds - $td[ 'sec' ] ) / 60 ) - $td[ 'min' ] ) / 60 ) % 24;
        $td[ 'day' ] = floor( ( ( ( ( ( $seconds - $td[ 'sec' ] ) / 60 ) - $td[ 'min' ] ) / 60 ) / 24 ) );

        return $td;
    }

    /**
     *
     * @return bool
     */
    static function createVersion()
    {

    }

    /**
     *
     */
    static function undoVersion()
    {

    }

    /**
     *
     */
    static function enableFloodcheck()
    {

        Session::save( 'floodchecker', true );
    }

    /**
     *
     */
    static function disableFloodcheck()
    {

        Session::save( 'floodchecker', false );
    }

    /**
     * @return bool
     */
    static function isWin()
    {
        if ( substr( strtoupper( PHP_OS ), 0, 3 ) === 'WIN' )
        {
            return true;
        }

        return false;
    }


    /**
     * @return bool
     */
    static function isLinux()
    {
        return strtoupper( PHP_OS ) === 'LINUX';
    }

    /**
     * @return bool
     */
    static function isUnix()
    {
        $os = strtoupper( PHP_OS );

        if ( substr( $os, 0, 3 ) !== 'WIN' && $os !== 'LINUX' )
        {
            return true;
        }

        return false;
    }


}

/**
 * Alias of {@link Libary::dbg()}
 *
 * @internal param mixed $data
 *
 * @see      Library::dbg()
 */
function dbg()
{

    $args = func_get_args();
    call_user_func_array( array(
        'Library',
        'dbg'
    ), $args );
}

/**
 * Alias of {@link Libary::dbgd()}
 *
 * @internal param mixed $data
 * @see      Library::dbgd()
 */
function dbgd()
{

    $args = func_get_args();
    call_user_func_array( array(
        'Library',
        'dbgd'
    ), $args );
}

/**
 * Alias of {@link Libary::dbgOutputCache()}
 *
 * @internal param mixed $data
 * @see      Library::dbgd()
 */
function dbgOutputCache()
{

    $args = func_get_args();
    call_user_func_array( array(
        'Library',
        'dbgOutputCache'
    ), $args );
}


function _writeCatch($type, $msg, $file, $line)
{

    $type = ErrorHandler::convertError( $type );
    $data = $type . ' | ' . $msg . ' | ' . $file . ' @' . $line . "\n";
    file_put_contents( DATA_PATH . 'logs/catch_errors.txt', $data, FILE_APPEND | LOCK_EX );
}


/**
 *
 * @param integer $errno
 * @param string $errstr
 * @param string $errfile
 * @param integer $errline
 */
function catch_errors($errno, $errstr, $errfile, $errline)
{

    _writeCatch( $errno, $errstr, $errfile, $errline );

    if ( !defined( 'SKIP_DEBUG' ) || ( defined( 'SKIP_DEBUG' ) && SKIP_DEBUG === true ) || DEBUG !== true || ( defined( 'FRONTEND_DEBUG_OUTPUT' ) && FRONTEND_DEBUG_OUTPUT !== true ) || $errno === E_NOTICE || $errno === E_WARNING )
    {
        return;
    }

    //die('Catch Error: <strong>' . $errstr . '</strong><br /><span class="mono">' . $errfile . ':' . $errline . '</span>');
    $cln = @ob_get_clean();
    //ob_clean();


    if ( ( $pos = strpos( $errstr, ', called' ) ) !== false )
    {
        $errstr = substr( $errstr, 0, $pos + 1 );
    }

    if ( ( $posd = strpos( $errstr, ': No such file or directory' ) ) !== false )
    {
        $errstr = substr( $errstr, 0, $posd + 1 );
    }

    throw new BaseException( 'Catch Error: <strong>' . $errstr . '</strong>', 'PHP', $errno, $errfile, $errline );
    /*
      Library::log( 'Catch Error: ' . $errstr . '! File: ' . $errfile . ' @Line: ' . $errline, 'error' );
      Error::raise(
      'Catch Error: <strong>' . $errstr . '</strong>', $errtype = 'PHP', $errno, $errfile, $errline
      ); */
}

set_error_handler( 'catch_errors' );

/**
 * base function for unhandled (eep!) exceptions
 *
 * @param Exception $exception
 */
function unhandled_exception_handler($exception)
{

    _writeCatch( $exception->getCode(), $exception->getMessage(), $exception->getFile(), $exception->getLine() );

    $errno = $exception->getCode();
    if ( !defined( 'SKIP_DEBUG' ) || ( defined( 'SKIP_DEBUG' ) && SKIP_DEBUG === true ) || DEBUG !== true || ( defined( 'FRONTEND_DEBUG_OUTPUT' ) && FRONTEND_DEBUG_OUTPUT !== true ) || $errno === E_NOTICE || $errno === E_WARNING )
    {
        return;
    }

    throw new BaseException( 'Unhandled Exception: ' . $exception->getMessage(), 'PHP', $errno, $exception->getFile(), $exception->getLine() );

    //Error::raise( 'Unhandled Exception: ' . $exception->getMessage(), $errtype = 'PHP', $exception->getCode(), $exception->getFile(), $exception->getLine() );
}

set_exception_handler( 'unhandled_exception_handler' );

/**
 * @throws BaseException
 */
function shutdownError()
{
    $exception = error_get_last();

    if ( $exception && ( $exception[ 'type' ] & E_FATAL ) )
    {
        switch ( $exception[ 'type' ] )
        {
            case E_ERROR: // 1 //
            case E_PARSE: // 4 //
            case E_CORE_ERROR: // 16 //
            case E_CORE_WARNING: // 32 //
            case E_COMPILE_ERROR: // 64 //
            case E_CORE_WARNING: // 128 //
            case E_USER_ERROR: // 256 //
            case E_USER_WARNING: // 512 //
            case E_USER_NOTICE: // 1024 //
            case E_STRICT: // 2048 //

                _writeCatch( $exception[ 'type' ], $exception[ 'message' ], $exception[ 'file' ], $exception[ 'line' ] );

                throw new BaseException( 'Fatal Error: <strong>' . $exception[ 'message' ] . '</strong>', 'PHP', $exception[ 'type' ], $exception[ 'file' ], $exception[ 'line' ] );

                break;
        }
    }

}

register_shutdown_function( 'shutdownError' );
?>