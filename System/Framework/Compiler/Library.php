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
 * @file         Library.php
 */





class Compiler_Library {

    private static $imageChains = null;
    private static $oldErrorReporting = array();
    /**
     * Returns the extension on a file (or other string)
     *
     * @param $instring
     * @internal param string $string
     * @return string $ext
     * @access   public
     * @static
     */
    static function getExtension($instring)
    {
        if (class_exists('Library', false))
        {
            return Library::getExtension($instring);
        }

        return substr( strrchr( $instring, "." ), 1 );
    }
    /**
     * @function    disableErrorHandling
     * Turn off error handling
     */
    static function disableErrorHandling()
    {
        if (class_exists('Library'))
        {
            return Library::disableErrorHandling();
        }

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
        if (class_exists('Library'))
        {
            return Library::enableErrorHandling();
        }

        error_reporting( array_pop( self::$oldErrorReporting ) );
        if (function_exists('catch_errors')) {
            set_error_handler( 'catch_errors' );
        }
    }
    /**
     * @param string $typ
     * @return null
     */
    public static function getImageChain($typ = 'thumbnail')
    {
        if (class_exists('Library', false))
        {
            return Library::getImageChain($typ);
        }

        self::loadChains();

        if ( isset( self::$imageChains[ $typ ] ) )
        {
            return self::$imageChains[ $typ ];
        }
        return null;
    }

    /**
     * @function    loadChains
     * Loads the image chains.
     */
    static function loadChains()
    {
        if ( is_null( self::$imageChains ) )
        {

            if (!class_exists('Database', false))
            {
                self::$imageChains = array();
                return;
            }


            self::$imageChains = Cache::get( 'imagechains' );

            if ( is_null( self::$imageChains ) )
            {
                global $imageChains;

                include self::formatPath( DATA_PATH . 'system/image-chains.php' );

                $db     = Database::getInstance();
                $chains = $db->query( "SELECT t.*, s.*
                                       FROM %tp%transform AS t
                                       LEFT JOIN %tp%transform_steps AS s ON(s.t_id=t.id)
                                       ORDER BY s.t_id, s.`order` ASC;" )->fetchAll();
                foreach ( $chains as $chain )
                {
                    if ( !empty( $chain[ 'type' ] ) )
                    {
                        self::$imageChains[ $chain[ 'title' ] ][ ] = array(
                            $chain[ 'type' ],
                            unserialize( $chain[ 'parameters' ] ),
                            $chain[ 'id' ]
                        );
                    }
                    else
                    {
                        self::$imageChains[ $chain[ 'title' ] ] = array();
                    }
                }

                if ( is_array( $imageChains ) )
                {
                    self::$imageChains = array_merge( $imageChains, self::$imageChains );
                }

                Cache::write( 'imagechains', self::$imageChains );
            }
        }
    }

    private static $sizes = array(
        'B',
        'KB',
        'MB',
        'GB',
        'TB',
        'PB'
    );


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
        if (class_exists('Library', false))
        {
            return Library::makeDirectory($path, $baseDir);
        }


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
     * @param $message
     * @param string $type
     * @param null $data
     * @param $dataIsTrace
     */
    static function log( $message, $type = 'info', $data = null, $dataIsTrace ) {
        if (class_exists('Library', false))
        {
            return Library::log($message, $type, $data, $dataIsTrace);
        }
    }

    /**
     *
     */
    static function enableFloodcheck()
    {
        if (class_exists('Library', false))
        {
            return Library::enableFloodcheck();
        }
    }

    /**
     *
     */
    static function disableFloodcheck()
    {
        if (class_exists('Library', false))
        {
            return Library::disableFloodcheck();
        }
    }

    /**
     * Formats a bytecount to be readable for humans
     *
     * @param $size
     * @param $unit
     * @param $retstring
     * @param $si
     * @return string
     */
    static function sizeReadable($size, $unit = null, $retstring = null, $si = true)
    {
        $mod   = 1024;
        $ii    = count( self::$sizes ) - 1;
        $unit  = array_search( ( string )$unit, self::$sizes );
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
            self::$sizes[ $i ]
        );
    }


    /**
     * @param int $size
     * @param null $retstring
     * @return string
     */
    static function humanSize($size, $retstring = null)
    {
        $size = self::sizeReadable( $size, null, $retstring, null );
        return $size[ 0 ] . ' ' . $size[ 1 ];
    }

    /**
     * @param int $size
     * @param null $retstring
     * @return string
     */
    static function formatSize($size, $retstring = null)
    {
        return self::humanSize( $size, $retstring );
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
        if (class_exists('Library', false))
        {
            return Library::suggest($name, $addExtension);
        }

        if ( !is_string( $name ) )
        {
            return $name;
        }

        if (class_exists('Strings', false)) {
            $name = Strings::fixLatin( strip_tags( $name ) );
            $name = Strings::unhtmlspecialchars( $name, true );
        }


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
            $name = $name . '.' . (class_exists('Settings', false) ? Settings::get( 'mod_rewrite_suffix', 'html' ) : 'html');
        }
        elseif ( is_string( $addExtension ) && $addExtension !== '' )
        {
            $name = $name . '.' . $addExtension;
        }

        return $name;
    }

    /*
     * Function to turn a mysql datetime (YYYY-MM-DD HH:MM:SS) into a unix timestamp
     * @param string str
     * @return int
     */
    /**
     * @param $str
     * @return int
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


}