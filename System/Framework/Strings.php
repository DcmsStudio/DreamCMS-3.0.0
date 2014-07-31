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
 * @file         Strings.php
 */
class Strings
{

    protected static $win1252ToUtf8 = array(
        128 => "\xe2\x82\xac",
        130 => "\xe2\x80\x9a",
        131 => "\xc6\x92",
        132 => "\xe2\x80\x9e",
        133 => "\xe2\x80\xa6",
        134 => "\xe2\x80\xa0",
        135 => "\xe2\x80\xa1",
        136 => "\xcb\x86",
        137 => "\xe2\x80\xb0",
        138 => "\xc5\xa0",
        139 => "\xe2\x80\xb9",
        140 => "\xc5\x92",
        142 => "\xc5\xbd",
        145 => "\xe2\x80\x98",
        146 => "\xe2\x80\x99",
        147 => "\xe2\x80\x9c",
        148 => "\xe2\x80\x9d",
        149 => "\xe2\x80\xa2",
        150 => "\xe2\x80\x93",
        151 => "\xe2\x80\x94",
        152 => "\xcb\x9c",
        153 => "\xe2\x84\xa2",
        154 => "\xc5\xa1",
        155 => "\xe2\x80\xba",
        156 => "\xc5\x93",
        158 => "\xc5\xbe",
        159 => "\xc5\xb8"
    );

    protected static $brokenUtf8ToUtf8 = array(
        "\xc2\x80" => "\xe2\x82\xac",
        "\xc2\x82" => "\xe2\x80\x9a",
        "\xc2\x83" => "\xc6\x92",
        "\xc2\x84" => "\xe2\x80\x9e",
        "\xc2\x85" => "\xe2\x80\xa6",
        "\xc2\x86" => "\xe2\x80\xa0",
        "\xc2\x87" => "\xe2\x80\xa1",
        "\xc2\x88" => "\xcb\x86",
        "\xc2\x89" => "\xe2\x80\xb0",
        "\xc2\x8a" => "\xc5\xa0",
        "\xc2\x8b" => "\xe2\x80\xb9",
        "\xc2\x8c" => "\xc5\x92",
        "\xc2\x8e" => "\xc5\xbd",
        "\xc2\x91" => "\xe2\x80\x98",
        "\xc2\x92" => "\xe2\x80\x99",
        "\xc2\x93" => "\xe2\x80\x9c",
        "\xc2\x94" => "\xe2\x80\x9d",
        "\xc2\x95" => "\xe2\x80\xa2",
        "\xc2\x96" => "\xe2\x80\x93",
        "\xc2\x97" => "\xe2\x80\x94",
        "\xc2\x98" => "\xcb\x9c",
        "\xc2\x99" => "\xe2\x84\xa2",
        "\xc2\x9a" => "\xc5\xa1",
        "\xc2\x9b" => "\xe2\x80\xba",
        "\xc2\x9c" => "\xc5\x93",
        "\xc2\x9e" => "\xc5\xbe",
        "\xc2\x9f" => "\xc5\xb8"
    );

    protected static $utf8ToWin1252 = array(
        "\xe2\x82\xac" => "\x80",
        "\xe2\x80\x9a" => "\x82",
        "\xc6\x92"     => "\x83",
        "\xe2\x80\x9e" => "\x84",
        "\xe2\x80\xa6" => "\x85",
        "\xe2\x80\xa0" => "\x86",
        "\xe2\x80\xa1" => "\x87",
        "\xcb\x86"     => "\x88",
        "\xe2\x80\xb0" => "\x89",
        "\xc5\xa0"     => "\x8a",
        "\xe2\x80\xb9" => "\x8b",
        "\xc5\x92"     => "\x8c",
        "\xc5\xbd"     => "\x8e",
        "\xe2\x80\x98" => "\x91",
        "\xe2\x80\x99" => "\x92",
        "\xe2\x80\x9c" => "\x93",
        "\xe2\x80\x9d" => "\x94",
        "\xe2\x80\xa2" => "\x95",
        "\xe2\x80\x93" => "\x96",
        "\xe2\x80\x94" => "\x97",
        "\xcb\x9c"     => "\x98",
        "\xe2\x84\xa2" => "\x99",
        "\xc5\xa1"     => "\x9a",
        "\xe2\x80\xba" => "\x9b",
        "\xc5\x93"     => "\x9c",
        "\xc5\xbe"     => "\x9e",
        "\xc5\xb8"     => "\x9f"
    );

    /**
     * @var string
     */
    private static $allowedTags = '<b>,<i>,<em>,<strong>,<u>,<br>,<span>';

    private static $entitys = array(
        '¡' => 'iexcl',
        '¢' => 'cent',
        '£' => 'pound',
        '¤' => 'curren',
        '¥' => 'yen',
        '¦' => 'brvbar',
        '§' => 'sect',
        '¨' => 'uml',
        '©' => 'copy',
        'ª' => 'ordf',
        '«' => 'laquo',
        '¬' => 'not',
        '­' => 'shy',
        '®' => 'reg',
        '¯' => 'macr',
        '°' => 'deg',
        '±' => 'plusmn',
        '²' => 'sup2',
        '³' => 'sup3',
        '´' => 'acute',
        'µ' => 'micro',
        '¶' => 'para',
        '·' => 'middot',
        '¸' => 'cedil',
        '¹' => 'sup1',
        'º' => 'ordm',
        '»' => 'raquo',
        '¼' => 'frac14',
        '½' => 'frac12',
        '¾' => 'frac34',
        '¿' => 'iquest',
        '×' => 'times',
        '÷' => 'divide',
        'ƒ' => 'fnof',
        '•' => 'bull',
        '…' => 'hellip',
        "'" => 'prime',

        '/' => 'frasl',
        'P' => 'weierp',
        'I' => 'image',
        'R' => 'real',
        '™' => 'trade',
        'Ø' => 'empty',
        '-' => 'minus',
        '*' => 'lowast',

        'ˆ' => 'circ',
        '˜' => 'tilde',
        ' ' => 'ensp',
        ' ' => 'emsp',
        '–' => 'ndash',
        '—' => 'mdash',
        '‘' => 'lsquo',
        '’' => 'rsquo',
        '‚' => 'sbquo',
        '“' => 'ldquo',
        '”' => 'rdquo',
        '„' => 'bdquo',
        '†' => 'dagger',
        '‡' => 'Dagger',
        '‰' => 'permil',
        '‹' => 'lsaquo',
        '›' => 'rsaquo',
        '€' => 'euro',
        'À' => 'Agrave',
        'Á' => 'Aacute',
        'Â' => 'Acirc',
        'Ã' => 'Atilde',
        'Ä' => 'Auml',
        'Å' => 'Aring',
        'Æ' => 'AElig',
        'Ç' => 'Ccedil',
        'È' => 'Egrave',
        'É' => 'Eacute',
        'Ê' => 'Ecirc',
        'Ë' => 'Euml',
        'Ì' => 'Igrave',
        'Í' => 'Iacute',
        'Î' => 'Icirc',
        'Ï' => 'Iuml',
        'Ð' => 'ETH',
        'Ñ' => 'Ntilde',
        'Ò' => 'Ograve',
        'Ó' => 'Oacute',
        'Ô' => 'Ocirc',
        'Õ' => 'Otilde',
        'Ö' => 'Ouml',
        'Ø' => 'Oslash',
        'Ù' => 'Ugrave',
        'Ú' => 'Uacute',
        'Û' => 'Ucirc',
        'Ü' => 'Uuml',
        'Ý' => 'Yacute',
        'Þ' => 'THORN',
        'ß' => 'szlig',
        'à' => 'agrave',
        'á' => 'aacute',
        'â' => 'acirc',
        'ã' => 'atilde',
        'ä' => 'auml',
        'å' => 'aring',
        'æ' => 'aelig',
        'ç' => 'ccedil',
        'è' => 'egrave',
        'é' => 'eacute',
        'ê' => 'ecirc',
        'ë' => 'euml',
        'ì' => 'igrave',
        'í' => 'iacute',
        'î' => 'icirc',
        'ï' => 'iuml',
        'ð' => 'eth',
        'ñ' => 'ntilde',
        'ò' => 'ograve',
        'ó' => 'oacute',
        'ô' => 'ocirc',
        'õ' => 'otilde',
        'ö' => 'ouml',
        'ø' => 'oslash',
        'ù' => 'ugrave',
        'ú' => 'uacute',
        'û' => 'ucirc',
        'ü' => 'uuml',
        'ý' => 'yacute',
        'þ' => 'thorn',
        'ÿ' => 'yuml',
        'Œ' => 'OElig',
        'œ' => 'oelig',
        'Š' => 'Scaron',
        'š' => 'scaron',
        'Ÿ' => 'Yuml'
    );


    private static $_brokenChars = array(
        'Ã¼'  => 'ü',
        'Ã¤'  => 'ä',
        'Ã¶'  => 'ö',
        'Ã–'  => 'Ö',
        'ÃŸ'  => 'ß',
        'Ã '  => 'à',
        'Ã¡'  => 'á',
        'Ã¢'  => 'â',
        'Ã£'  => 'ã',
        'Ã¹'  => 'ù',
        'Ãº'  => 'ú',
        'Ã»'  => 'û',
        'Ã™'  => 'Ù',
        'Ãš'  => 'Ú',
        'Ã›'  => 'Û',
        'Ãœ'  => 'Ü',
        'Ã²'  => 'ò',
        'Ã³'  => 'ó',
        'Ã´'  => 'ô',
        'Ã¨'  => 'è',
        'Ã©'  => 'é',
        'Ãª'  => 'ê',
        'Ã«'  => 'ë',
        'Ã€'  => 'À',
        'Ã'  => 'Á',
        'Ã‚'  => 'Â',
        'Ãƒ'  => 'Ã',
        'Ã„'  => 'Ä',
        'Ã…'  => 'Å',
        'Ã‡'  => 'Ç',
        'Ãˆ'  => 'È',
        'Ã‰'  => 'É',
        'ÃŠ'  => 'Ê',
        'Ã‹'  => 'Ë',
        'ÃŒ'  => 'Ì',
        'Ã'  => 'Í',
        'ÃŽ'  => 'Î',
        'Ã'  => 'Ï',
        'Ã‘'  => 'Ñ',
        'Ã’'  => 'Ò',
        'Ã“'  => 'Ó',
        'Ã”'  => 'Ô',
        'Ã•'  => 'Õ',
        'Ã˜'  => 'Ø',
        'Ã¥'  => 'å',
        'Ã¦'  => 'æ',
        'Ã§'  => 'ç',
        'Ã¬'  => 'ì',
        'Ã­'  => 'í',
        'Ã®'  => 'î',
        'Ã¯'  => 'ï',
        'Ã°'  => 'ð',
        'Ã±'  => 'ñ',
        'Ãµ'  => 'õ',
        'Ã¸'  => 'ø',
        'Ã½'  => 'ý',
        'Ã¿'  => 'ÿ',
        'â‚¬' => '€'
    );


    /** No bugs detected in iconv. */
    const ICONV_OK = 0;

    /** Iconv truncates output if converting from UTF-8 to another
     *  character set with //IGNORE, and a non-encodable character is found */
    const ICONV_TRUNCATES = 1;

    /** Iconv does not support //IGNORE, making it unusable for
     *  transcoding purposes */
    const ICONV_UNUSABLE = 2;

    /**
     * Error-handler that mutes errors, alternative to shut-up operator.
     */
    public static function muteErrorHandler()
    {
    }


    /**
     * iconv wrapper which mutes errors and works around bugs.
     * @param string $in Input encoding
     * @param string $out Output encoding
     * @param string $text The text to convert
     * @param int $max_chunk_size
     * @return string
     */
    public static function iconv($in, $out, $text, $max_chunk_size = 8000)
    {
        $code = self::testIconvTruncateBug();
        if ( $code == self::ICONV_OK )
        {
            return self::unsafeIconv( $in, $out, $text );
        }
        elseif ( $code == self::ICONV_TRUNCATES )
        {
            // we can only work around this if the input character set
            // is utf-8
            if ( $in == 'utf-8' )
            {
                if ( $max_chunk_size < 4 )
                {
                    trigger_error( 'max_chunk_size is too small', E_USER_WARNING );

                    return false;
                }
                // split into 8000 byte chunks, but be careful to handle
                // multibyte boundaries properly
                if ( ( $c = strlen( $text ) ) <= $max_chunk_size )
                {
                    return self::unsafeIconv( $in, $out, $text );
                }
                $r = '';
                $i = 0;
                while ( true )
                {
                    if ( $i + $max_chunk_size >= $c )
                    {
                        $r .= self::unsafeIconv( $in, $out, substr( $text, $i ) );
                        break;
                    }
                    // wibble the boundary
                    if ( 0x80 != ( 0xC0 & ord( $text[ $i + $max_chunk_size ] ) ) )
                    {
                        $chunk_size = $max_chunk_size;
                    }
                    elseif ( 0x80 != ( 0xC0 & ord( $text[ $i + $max_chunk_size - 1 ] ) ) )
                    {
                        $chunk_size = $max_chunk_size - 1;
                    }
                    elseif ( 0x80 != ( 0xC0 & ord( $text[ $i + $max_chunk_size - 2 ] ) ) )
                    {
                        $chunk_size = $max_chunk_size - 2;
                    }
                    elseif ( 0x80 != ( 0xC0 & ord( $text[ $i + $max_chunk_size - 3 ] ) ) )
                    {
                        $chunk_size = $max_chunk_size - 3;
                    }
                    else
                    {
                        return false; // rather confusing UTF-8...
                    }
                    $chunk = substr( $text, $i, $chunk_size ); // substr doesn't mind overlong lengths
                    $r .= self::unsafeIconv( $in, $out, $chunk );
                    $i += $chunk_size;
                }

                return $r;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    /**
     * Cleans a UTF-8 string for well-formedness and SGML validity
     *
     * It will parse according to UTF-8 and return a valid UTF8 string, with
     * non-SGML codepoints excluded.
     *
     * @param string $str The string to clean
     * @return string
     *
     * @note Just for reference, the non-SGML code points are 0 to 31 and
     *       127 to 159, inclusive.  However, we allow code points 9, 10
     *       and 13, which are the tab, line feed and carriage return
     *       respectively. 128 and above the code points map to multibyte
     *       UTF-8 representations.
     *
     * @note Fallback code adapted from utf8ToUnicode by Henri Sivonen and
     *       hsivonen@iki.fi at <http://iki.fi/hsivonen/php-utf8/> under the
     *       LGPL license.  Notes on what changed are inside, but in general,
     *       the original code transformed UTF-8 text into an array of integer
     *       Unicode codepoints. Understandably, transforming that back to
     *       a string would be somewhat expensive, so the function was modded to
     *       directly operate on the string.  However, this discourages code
     *       reuse, and the logic enumerated here would be useful for any
     *       function that needs to be able to understand UTF-8 characters.
     *       As of right now, only smart lossless character encoding converters
     *       would need that, and I'm probably not going to implement them.
     *       Once again, PHP 6 should solve all our problems.
     */
    public static function cleanUTF8($str)
    {
        // UTF-8 validity is checked since PHP 4.3.5
        // This is an optimization: if the string is already valid UTF-8, no
        // need to do PHP stuff. 99% of the time, this will be the case.
        // The regexp matches the XML char production, as well as well as excluding
        // non-SGML codepoints U+007F to U+009F
        if (preg_match(
            '/^[\x{9}\x{A}\x{D}\x{20}-\x{7E}\x{A0}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]*$/Du',
            $str
        )) {
            return $str;
        }

        $mState = 0; // cached expected number of octets after the current octet
        // until the beginning of the next UTF8 character sequence
        $mUcs4  = 0; // cached Unicode character
        $mBytes = 1; // cached expected number of octets in the current sequence

        // original code involved an $out that was an array of Unicode
        // codepoints.  Instead of having to convert back into UTF-8, we've
        // decided to directly append valid UTF-8 characters onto a string
        // $out once they're done.  $char accumulates raw bytes, while $mUcs4
        // turns into the Unicode code point, so there's some redundancy.

        $out = '';
        $char = '';

        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $in = ord($str{$i});
            $char .= $str[$i]; // append byte to char
            if (0 == $mState) {
                // When mState is zero we expect either a US-ASCII character
                // or a multi-octet sequence.
                if (0 == (0x80 & ($in))) {
                    // US-ASCII, pass straight through.
                    if (($in <= 31 || $in == 127) &&
                        !($in == 9 || $in == 13 || $in == 10) // save \r\t\n
                    ) {
                        // control characters, remove
                    } else {
                        $out .= $char;
                    }
                    // reset
                    $char = '';
                    $mBytes = 1;
                } elseif (0xC0 == (0xE0 & ($in))) {
                    // First octet of 2 octet sequence
                    $mUcs4 = ($in);
                    $mUcs4 = ($mUcs4 & 0x1F) << 6;
                    $mState = 1;
                    $mBytes = 2;
                } elseif (0xE0 == (0xF0 & ($in))) {
                    // First octet of 3 octet sequence
                    $mUcs4 = ($in);
                    $mUcs4 = ($mUcs4 & 0x0F) << 12;
                    $mState = 2;
                    $mBytes = 3;
                } elseif (0xF0 == (0xF8 & ($in))) {
                    // First octet of 4 octet sequence
                    $mUcs4 = ($in);
                    $mUcs4 = ($mUcs4 & 0x07) << 18;
                    $mState = 3;
                    $mBytes = 4;
                } elseif (0xF8 == (0xFC & ($in))) {
                    // First octet of 5 octet sequence.
                    //
                    // This is illegal because the encoded codepoint must be
                    // either:
                    // (a) not the shortest form or
                    // (b) outside the Unicode range of 0-0x10FFFF.
                    // Rather than trying to resynchronize, we will carry on
                    // until the end of the sequence and let the later error
                    // handling code catch it.
                    $mUcs4 = ($in);
                    $mUcs4 = ($mUcs4 & 0x03) << 24;
                    $mState = 4;
                    $mBytes = 5;
                } elseif (0xFC == (0xFE & ($in))) {
                    // First octet of 6 octet sequence, see comments for 5
                    // octet sequence.
                    $mUcs4 = ($in);
                    $mUcs4 = ($mUcs4 & 1) << 30;
                    $mState = 5;
                    $mBytes = 6;
                } else {
                    // Current octet is neither in the US-ASCII range nor a
                    // legal first octet of a multi-octet sequence.
                    $mState = 0;
                    $mUcs4  = 0;
                    $mBytes = 1;
                    $char = '';
                }
            } else {
                // When mState is non-zero, we expect a continuation of the
                // multi-octet sequence
                if (0x80 == (0xC0 & ($in))) {
                    // Legal continuation.
                    $shift = ($mState - 1) * 6;
                    $tmp = $in;
                    $tmp = ($tmp & 0x0000003F) << $shift;
                    $mUcs4 |= $tmp;

                    if (0 == --$mState) {
                        // End of the multi-octet sequence. mUcs4 now contains
                        // the final Unicode codepoint to be output

                        // Check for illegal sequences and codepoints.

                        // From Unicode 3.1, non-shortest form is illegal
                        if (((2 == $mBytes) && ($mUcs4 < 0x0080)) ||
                            ((3 == $mBytes) && ($mUcs4 < 0x0800)) ||
                            ((4 == $mBytes) && ($mUcs4 < 0x10000)) ||
                            (4 < $mBytes) ||
                            // From Unicode 3.2, surrogate characters = illegal
                            (($mUcs4 & 0xFFFFF800) == 0xD800) ||
                            // Codepoints outside the Unicode range are illegal
                            ($mUcs4 > 0x10FFFF)
                        ) {

                        } elseif (0xFEFF != $mUcs4 && // omit BOM
                            // check for valid Char unicode codepoints
                            (
                                0x9 == $mUcs4 ||
                                0xA == $mUcs4 ||
                                0xD == $mUcs4 ||
                                (0x20 <= $mUcs4 && 0x7E >= $mUcs4) ||
                                // 7F-9F is not strictly prohibited by XML,
                                // but it is non-SGML, and thus we don't allow it
                                (0xA0 <= $mUcs4 && 0xD7FF >= $mUcs4) ||
                                (0x10000 <= $mUcs4 && 0x10FFFF >= $mUcs4)
                            )
                        ) {
                            $out .= $char;
                        }
                        // initialize UTF8 cache (reset)
                        $mState = 0;
                        $mUcs4  = 0;
                        $mBytes = 1;
                        $char = '';
                    }
                } else {
                    // ((0xC0 & (*in) != 0x80) && (mState != 0))
                    // Incomplete multi-octet sequence.
                    // used to result in complete fail, but we'll reset
                    $mState = 0;
                    $mUcs4  = 0;
                    $mBytes = 1;
                    $char ='';
                }
            }
        }
        return $out;
    }

    /**
     * iconv wrapper which mutes errors, but doesn't work around bugs.
     * @param string $in Input encoding
     * @param string $out Output encoding
     * @param string $text The text to convert
     * @return string
     */
    public static function unsafeIconv($in, $out, $text)
    {
        set_error_handler( array('Strings', 'muteErrorHandler') );
        $r = iconv( $in, $out, $text );
        restore_error_handler();

        return $r;
    }

    /**
     * @return bool
     */
    public static function iconvAvailable()
    {
        static $iconv = null;
        if ( $iconv === null )
        {
            $iconv = function_exists( 'iconv' ) && self::testIconvTruncateBug() != self::ICONV_UNUSABLE;
        }

        return $iconv;
    }

    /**
     * glibc iconv has a known bug where it doesn't handle the magic
     * //IGNORE stanza correctly.  In particular, rather than ignore
     * characters, it will return an EILSEQ after consuming some number
     * of characters, and expect you to restart iconv as if it were
     * an E2BIG.  Old versions of PHP did not respect the errno, and
     * returned the fragment, so as a result you would see iconv
     * mysteriously truncating output. We can work around this by
     * manually chopping our input into segments of about 8000
     * characters, as long as PHP ignores the error code.  If PHP starts
     * paying attention to the error code, iconv becomes unusable.
     *
     * @return int Error code indicating severity of bug.
     */
    public static function testIconvTruncateBug()
    {
        static $code = null;
        if ( $code === null )
        {
            // better not use iconv, otherwise infinite loop!
            $r = self::unsafeIconv( 'utf-8', 'ascii//IGNORE', "\xCE\xB1" . str_repeat( 'a', 9000 ) );
            if ( $r === false )
            {
                $code = self::ICONV_UNUSABLE;
            }
            elseif ( ( $c = strlen( $r ) ) < 9000 )
            {
                $code = self::ICONV_TRUNCATES;
            }
            elseif ( $c > 9000 )
            {
                trigger_error(
                    'Your copy of iconv is extremely buggy. Please notify HTML Purifier maintainers: ' .
                    'include your iconv version as per phpversion()',
                    E_USER_ERROR
                );
            }
            else
            {
                $code = self::ICONV_OK;
            }
        }

        return $code;
    }

    /**
     * This expensive function tests whether or not a given character
     * encoding supports ASCII. 7/8-bit encodings like Shift_JIS will
     * fail this test, and require special processing. Variable width
     * encodings shouldn't ever fail.
     *
     * @param string $encoding Encoding name to test, as per iconv format
     * @param bool $bypass Whether or not to bypass the precompiled arrays.
     * @return Array of UTF-8 characters to their corresponding ASCII,
     *      which can be used to "undo" any overzealous iconv action.
     */
    public static function testEncodingSupportsASCII($encoding, $bypass = false)
    {
        // All calls to iconv here are unsafe, proof by case analysis:
        // If ICONV_OK, no difference.
        // If ICONV_TRUNCATE, all calls involve one character inputs,
        // so bug is not triggered.
        // If ICONV_UNUSABLE, this call is irrelevant
        static $encodings = array();
        if ( !$bypass )
        {
            if ( isset( $encodings[ $encoding ] ) )
            {
                return $encodings[ $encoding ];
            }
            $lenc = strtolower( $encoding );
            switch ( $lenc )
            {
                case 'shift_jis':
                    return array("\xC2\xA5" => '\\', "\xE2\x80\xBE" => '~');
                case 'johab':
                    return array("\xE2\x82\xA9" => '\\');
            }
            if ( strpos( $lenc, 'iso-8859-' ) === 0 )
            {
                return array();
            }
        }
        $ret = array();
        if ( self::unsafeIconv( 'UTF-8', $encoding, 'a' ) === false )
        {
            return false;
        }
        for ( $i = 0x20; $i <= 0x7E; $i++ )
        { // all printable ASCII chars
            $c = chr( $i ); // UTF-8 char
            $r = self::unsafeIconv( 'UTF-8', "$encoding//IGNORE", $c ); // initial conversion
            if ( $r === '' ||
                // This line is needed for iconv implementations that do not
                // omit characters that do not exist in the target character set
                ( $r === $c && self::unsafeIconv( $encoding, 'UTF-8//IGNORE', $r ) !== $c )
            )
            {
                // Reverse engineer: what's the UTF-8 equiv of this byte
                // sequence? This assumes that there's no variable width
                // encoding that doesn't support ASCII.
                $ret[ self::unsafeIconv( $encoding, 'UTF-8//IGNORE', $c ) ] = $c;
            }
        }
        $encodings[ $encoding ] = $ret;

        return $ret;
    }


    /**
     * @param $file
     * @param $line
     * @param $expr
     */
    public static function assertFailed($file, $line, $expr)
    {

    }


    /**
     * @param $code
     * @return mixed
     */
    public static function entitysToChar($code)
    {

        return str_replace( array_values( self::$entitys ), array_keys( self::$entitys ), $code );

    }


    /**
     * @param $code
     * @return mixed
     */
    public static function repairBrokenChars($code)
    {
        foreach ( self::$_brokenChars as $key => $value )
        {
            $code = str_replace( $key, html_entity_decode( $value, ENT_COMPAT ), $code );
        }

        return $code;
    }

    /**
     * @param string $value
     * @return string
     */
    public static function revertInputEntitiesClean($value)
    {

        return str_replace( array(
            '[&]',
            '[&]',
            '[lt]',
            '[lt]',
            '[gt]',
            '[gt]',
            '[nbsp]',
            '[nbsp]',
            '[-]',
            '[-]'
        ), array(
            '[&amp;]',
            '&amp;',
            '[&lt;]',
            '&lt;',
            '[&gt;]',
            '&gt;',
            '[&nbsp;]',
            '&nbsp;',
            '[&shy;]',
            '&shy;'
        ), $value );
    }


    /**
     * @param string $in
     * @return mixed
     */
    public static function cleanString($in)
    {

        $find[ ] = 'â€œ'; // left side double smart quote
        $find[ ] = 'â€'; // right side double smart quote
        $find[ ] = 'â€˜'; // left side single smart quote
        $find[ ] = 'â€™'; // right side single smart quote
        $find[ ] = 'â€¦'; // elipsis
        $find[ ] = 'â€”'; // em dash
        $find[ ] = 'â€“'; // en dash
        #	$find[] = '%';  // en dash
        $find[ ] = '&nbsp;'; //
        $find[ ] = '&amp;nbsp;'; //

        $replace[ ] = '"';
        $replace[ ] = '"';
        $replace[ ] = "'";
        $replace[ ] = "'";
        $replace[ ] = "...";
        $replace[ ] = "-";
        $replace[ ] = "-";
        #	$replace[] = "&#37;";
        $replace[ ] = ' ';
        $replace[ ] = ' ';

        $arr = array(
            'Ã¼'  => 'ü',
            'Ã¤'  => 'ä',
            'Ã¶'  => 'ö',
            'Ã–'  => 'Ö',
            'ÃŸ'  => 'ß',
            'Ã '  => 'à',
            'Ã¡'  => 'á',
            'Ã¢'  => 'â',
            'Ã£'  => 'ã',
            'Ã¹'  => 'ù',
            'Ãº'  => 'ú',
            'Ã»'  => 'û',
            'Ã™'  => 'Ù',
            'Ãš'  => 'Ú',
            'Ã›'  => 'Û',
            'Ãœ'  => 'Ü',
            'Ã²'  => 'ò',
            'Ã³'  => 'ó',
            'Ã´'  => 'ô',
            'Ã¨'  => 'è',
            'Ã©'  => 'é',
            'Ãª'  => 'ê',
            'Ã«'  => 'ë',
            'Ã€'  => 'À',
            'Ã'  => 'Á',
            'Ã‚'  => 'Â',
            'Ãƒ'  => 'Ã',
            'Ã„'  => 'Ä',
            'Ã…'  => 'Å',
            'Ã‡'  => 'Ç',
            'Ãˆ'  => 'È',
            'Ã‰'  => 'É',
            'ÃŠ'  => 'Ê',
            'Ã‹'  => 'Ë',
            'ÃŒ'  => 'Ì',
            'Ã'  => 'Í',
            'ÃŽ'  => 'Î',
            'Ã'  => 'Ï',
            'Ã‘'  => 'Ñ',
            'Ã’'  => 'Ò',
            'Ã“'  => 'Ó',
            'Ã”'  => 'Ô',
            'Ã•'  => 'Õ',
            'Ã˜'  => 'Ø',
            'Ã¥'  => 'å',
            'Ã¦'  => 'æ',
            'Ã§'  => 'ç',
            'Ã¬'  => 'ì',
            'Ã­'  => 'í',
            'Ã®'  => 'î',
            'Ã¯'  => 'ï',
            'Ã°'  => 'ð',
            'Ã±'  => 'ñ',
            'Ãµ'  => 'õ',
            'Ã¸'  => 'ø',
            'Ã½'  => 'ý',
            'Ã¿'  => 'ÿ',
            'â‚¬' => '€'
        );
        $in  = str_replace( array_keys( $arr ), array_values( $arr ), $in );

        return str_replace( $find, $replace, $in );
    }

    /**
     * @param string $string
     * @return mixed
     */
    public static function removeDoubleSpace($string)
    {
        $string = preg_replace( '/\s{2,}/', ' ', $string );
        return preg_replace( '#\s(\.|,|;|:|!|\?)#', '$1', $string );
    }

    /**
     * @param string $str
     * @return int
     */
    public static function countSentences($str)
    {
        $str = strip_tags($str);
        preg_match_all('/((\.|!|\?)(\s*\t*\n*[A-Z0-9]|\s*\t*\n*$))/us', $str, $match);
        return count($match[1]);
    }

    /**
     * @param $text
     * @return array|string
     */
    static function toUTF8($text)
    {

        /**
         * Function Encoding::toUTF8
         *
         * This function leaves UTF8 characters alone, while converting almost all non-UTF8 to UTF8.
         *
         * It assumes that the encoding of the original string is either Windows-1252 or ISO 8859-1.
         *
         * It may fail to convert characters to UTF-8 if they fall into one of these scenarios:
         *
         * 1) when any of these characters:   ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÚÛÜÝÞß
         *    are followed by any of these:  ("group B")
         *                                    ¡¢£¤¥¦§¨©ª«¬­®¯°±²³´µ¶•¸¹º»¼½¾¿
         * For example:   %ABREPRESENT%C9%BB. «REPRESENTÉ»
         * The "«" (%AB) character will be converted, but the "É" followed by "»" (%C9%BB)
         * is also a valid unicode character, and will be left unchanged.
         *
         * 2) when any of these: àáâãäåæçèéêëìíîï  are followed by TWO chars from group B,
         * 3) when any of these: ðñòó  are followed by THREE chars from group B.
         *
         * @name         toUTF8
         * @param string $text Any string.
         * @return string  The same string, UTF8 encoded
         *
         */

        if ( is_array( $text ) )
        {
            foreach ( $text as $k => $v )
            {
                $text[ $k ] = self::toUTF8( $v );
            }

            return $text;
        }
        elseif ( is_string( $text ) )
        {

            $max = strlen( $text );
            $buf = "";
            for ( $i = 0; $i < $max; $i++ )
            {
                $c1 = $text{$i};
                if ( $c1 >= "\xc0" )
                { //Should be converted to UTF8, if it's not UTF8 already
                    $c2 = $i + 1 >= $max ? "\x00" : $text{$i + 1};
                    $c3 = $i + 2 >= $max ? "\x00" : $text{$i + 2};
                    $c4 = $i + 3 >= $max ? "\x00" : $text{$i + 3};
                    if ( $c1 >= "\xc0" & $c1 <= "\xdf" )
                    { //looks like 2 bytes UTF8
                        if ( $c2 >= "\x80" && $c2 <= "\xbf" )
                        { //yeah, almost sure it's UTF8 already
                            $buf .= $c1 . $c2;
                            $i++;
                        }
                        else
                        { //not valid UTF8.  Convert it.
                            $cc1 = ( chr( ord( $c1 ) / 64 ) | "\xc0" );
                            $cc2 = ( $c1 & "\x3f" ) | "\x80";
                            $buf .= $cc1 . $cc2;
                        }
                    }
                    elseif ( $c1 >= "\xe0" & $c1 <= "\xef" )
                    { //looks like 3 bytes UTF8
                        if ( $c2 >= "\x80" && $c2 <= "\xbf" && $c3 >= "\x80" && $c3 <= "\xbf" )
                        { //yeah, almost sure it's UTF8 already
                            $buf .= $c1 . $c2 . $c3;
                            $i = $i + 2;
                        }
                        else
                        { //not valid UTF8.  Convert it.
                            $cc1 = ( chr( ord( $c1 ) / 64 ) | "\xc0" );
                            $cc2 = ( $c1 & "\x3f" ) | "\x80";
                            $buf .= $cc1 . $cc2;
                        }
                    }
                    elseif ( $c1 >= "\xf0" & $c1 <= "\xf7" )
                    { //looks like 4 bytes UTF8
                        if ( $c2 >= "\x80" && $c2 <= "\xbf" && $c3 >= "\x80" && $c3 <= "\xbf" && $c4 >= "\x80" && $c4 <= "\xbf" )
                        { //yeah, almost sure it's UTF8 already
                            $buf .= $c1 . $c2 . $c3;
                            $i = $i + 2;
                        }
                        else
                        { //not valid UTF8.  Convert it.
                            $cc1 = ( chr( ord( $c1 ) / 64 ) | "\xc0" );
                            $cc2 = ( $c1 & "\x3f" ) | "\x80";
                            $buf .= $cc1 . $cc2;
                        }
                    }
                    else
                    { //doesn't look like UTF8, but should be converted
                        $cc1 = ( chr( ord( $c1 ) / 64 ) | "\xc0" );
                        $cc2 = ( ( $c1 & "\x3f" ) | "\x80" );
                        $buf .= $cc1 . $cc2;
                    }
                }
                elseif ( ( $c1 & "\xc0" ) === "\x80" )
                { // needs conversion
                    if ( isset( self::$win1252ToUtf8[ ord( $c1 ) ] ) )
                    { //found in Windows-1252 special cases
                        $buf .= self::$win1252ToUtf8[ ord( $c1 ) ];
                    }
                    else
                    {
                        $cc1 = ( chr( ord( $c1 ) / 64 ) | "\xc0" );
                        $cc2 = ( ( $c1 & "\x3f" ) | "\x80" );
                        $buf .= $cc1 . $cc2;
                    }
                }
                else
                { // it doesn't need convesion
                    $buf .= $c1;
                }
            }

            return $buf;
        }
        else
        {
            return $text;
        }
    }

    /**
     * @param $text
     * @return array|string
     */
    static function toWin1252($text)
    {

        if ( is_array( $text ) )
        {
            foreach ( $text as $k => $v )
            {
                $text[ $k ] = self::toWin1252( $v );
            }

            return $text;
        }
        elseif ( is_string( $text ) )
        {
            return utf8_decode( str_replace( array_keys( self::$utf8ToWin1252 ), array_values( self::$utf8ToWin1252 ), self::toUTF8( $text ) ) );
        }
        else
        {
            return $text;
        }
    }

    /**
     * @param $text
     * @return array|string
     */
    static function toISO8859($text)
    {

        return self::toWin1252( $text );
    }

    /**
     * @param $text
     * @return array|string
     */
    static function toLatin1($text)
    {

        return self::toWin1252( $text );
    }

    /**
     * @param $text
     * @return mixed
     */
    static function UTF8FixWin1252Chars($text)
    {
        // If you received an UTF-8 string that was converted from Windows-1252 as it was ISO8859-1
        // (ignoring Windows-1252 chars from 80 to 9F) use this function to fix it.
        // See: http://en.wikipedia.org/wiki/Windows-1252
        return str_replace( array_keys( self::$brokenUtf8ToUtf8 ), array_values( self::$brokenUtf8ToUtf8 ), $text );
    }

    /**
     * @param string $str
     * @return string
     */
    static function removeBOM($str = "")
    {

        if ( substr( $str, 0, 3 ) === pack( "CCC", 0xef, 0xbb, 0xbf ) )
        {
            $str = substr( $str, 3 );
        }

        return $str;
    }

    /**
     * @param $encodingLabel
     * @return string
     */
    public static function normalizeEncoding($encodingLabel)
    {

        $encoding     = strtoupper( $encodingLabel );
        $enc          = preg_replace( '/[^a-zA-Z0-9\s]/', '', $encoding );
        $equivalences = array(
            'ISO88591'    => 'ISO-8859-1',
            'ISO8859'     => 'ISO-8859-1',
            'ISO'         => 'ISO-8859-1',
            'LATIN1'      => 'ISO-8859-1',
            'LATIN'       => 'ISO-8859-1',
            'UTF8'        => 'UTF-8',
            'UTF'         => 'UTF-8',
            'WIN1252'     => 'ISO-8859-1',
            'WINDOWS1252' => 'ISO-8859-1'
        );

        if ( empty( $equivalences[ $encoding ] ) )
        {
            return 'UTF-8';
        }

        return $equivalences[ $encoding ];
    }


    /**
     * Strips out all non UTF-8 characters from a string
     * This is best used when you have already converted / got UTF-8 data
     *
     * @param string $some_string
     * @return string
     */
    public static function cleanUtf8us($some_string)
    {

        return $some_string;
        // reject overly long 2 byte sequences, as well as characters above U+10000 and replace with ?
        $some_string = preg_replace( '/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]' . '|[\x00-\x7F][\x80-\xBF]+' . '|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*' . '|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})' . '|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/S', '', $some_string );

        // reject overly long 3 byte sequences and UTF-16 surrogates and replace with ?
        return preg_replace( '/\xE0[\x80-\x9F][\x80-\xBF]' . '|\xED[\xA0-\xBF][\x80-\xBF]/S', '', $some_string );
    }


    /**
     * Strips out all non UTF-8 characters from a string
     * This is best used when you have already converted / got UTF-8 data
     *
     * @param  string $string In
     * @return string Cleaned
     */
    public static function stripNonUtf8($string)
    {

        $string = preg_replace( '/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]|(?<=^|[\x00-\x7F])[\x80-\xBF]+|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*' . '|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/', '', $string );


        $string = preg_replace( '/\xE0[\x80-\x9F][\x80-\xBF]|\xED[\xA0-\xBF][\x80-\xBF]/S', '', $string );

        return $string;
    }

    /**
     * Shorten a HTML string to a certain number of characters
     * Shortens a string to a given number of characters preserving words
     * (therefore it might be a bit shorter or longer than the number of
     * characters specified). Preserves allowed tags.
     *
     * @param string $strString
     * @param integer $intNumberOfChars
     * @return string
     */
    public static function substrHtml($strString, $intNumberOfChars)
    {

        $strReturn    = "";
        $intCharCount = 0;
        $arrOpenTags  = array();
        $arrTagBuffer = array();
        $arrEmptyTags = array(
            'area',
            'base',
            'br',
            'col',
            'hr',
            'img',
            'input',
            'frame',
            'link',
            'meta',
            'param'
        );

        $strString = preg_replace( '/[\t\n\r]+/', ' ', $strString );
        $strString = strip_tags( $strString, '<i>,<em>,<b>,<strong>,<u>,<span>' );
        $strString = preg_replace( '/ +/', ' ', $strString );

        // Seperate tags and text
        $arrChunks = preg_split( '/(<[^>]+>)/', $strString, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );

        for ( $i = 0; $i < count( $arrChunks ); $i++ )
        {
            // Buffer tags to include them later
            if ( preg_match( '/<([^>]+)>/', $arrChunks[ $i ] ) )
            {
                $arrTagBuffer[ ] = $arrChunks[ $i ];
                continue;
            }

            // Get the substring of the current text
            if ( ( $arrChunks[ $i ] = self::dcmsSubStr( $arrChunks[ $i ], ( $intNumberOfChars - $intCharCount ) ) ) === false )
            {
                break;
            }

            $intCharCount += utf8_strlen( self::decodeEntities( $arrChunks[ $i ] ) );

            if ( $intCharCount <= $intNumberOfChars )
            {
                foreach ( $arrTagBuffer as $strTag )
                {
                    $strTagName = strtolower( substr( trim( $strTag ), 1, -1 ) );

                    // Skip empty tags
                    if ( in_array( $strTagName, $arrEmptyTags ) )
                    {
                        continue;
                    }

                    // Store opening tags in the open_tags array
                    if ( substr( $strTag, 0, 2 ) != '</' )
                    {
                        if ( strlen( $arrChunks[ $i ] ) || $i < count( $arrChunks ) )
                        {
                            $arrOpenTags[ ] = $strTag;
                        }

                        continue;
                    }

                    // Closing tags will be removed from the "open tags" array
                    if ( strlen( $arrChunks[ $i ] ) || $i < count( $arrChunks ) )
                    {
                        $arrOpenTags = array_values( $arrOpenTags );

                        for ( $j = count( $arrOpenTags ) - 1; $j >= 0; $j-- )
                        {
                            $strOpenTag = str_replace( '<', '</', $arrOpenTags[ $j ] );
                            $strOpenTag = substr( $strOpenTag, 0, strpos( $strOpenTag, ' ' ) ) . '>';

                            if ( $strOpenTag === $strTag )
                            {
                                unset( $arrOpenTags[ $j ] );
                                break;
                            }
                        }
                    }
                }

                // If the current chunk contains text, add tags and text to the return string
                if ( strlen( $arrChunks[ $i ] ) || $i < count( $arrChunks ) )
                {
                    $strReturn .= implode( '', $arrTagBuffer ) . $arrChunks[ $i ];
                }

                $arrTagBuffer = array();
                continue;
            }

            break;
        }

        // Close all remaining open tags
        krsort( $arrOpenTags );

        foreach ( $arrOpenTags as $strTag )
        {
            $strReturn .= str_replace( '<', '</', $strTag );
        }

        $arrTagBuffer = $arrOpenTags = null;

        return trim( $strReturn );
    }

    /**
     * Shortens a string to a given number of characters preserving words
     * (therefore it might be a bit shorter or longer than the number of
     * characters specified). Stips all tags.
     *
     * @param string $strString
     * @param integer $intNumberOfChars
     * @param boolean $blnAddEllipsis
     * @return string
     */
    public static function dcmsSubStr($strString, $intNumberOfChars, $blnAddEllipsis = false)
    {

        $strString = preg_replace( '/[\t\n\r]+/', ' ', $strString );
        $strString = strip_tags( $strString );

        if ( utf8_strlen( $strString ) <= $intNumberOfChars )
        {
            return $strString;
        }

        $intCharCount = 0;
        $arrWords     = array();
        $arrChunks    = preg_split( '/\s+/', $strString );
        $strEllipsis  = '';

        foreach ( $arrChunks as $strChunk )
        {
            $intCharCount += utf8_strlen( self::decodeEntities( $strChunk ) );

            if ( $intCharCount++ <= $intNumberOfChars )
            {
                $arrWords[ ] = $strChunk;
                continue;
            }

            // If the first word is longer than $intNumberOfChars already, shorten it
            // with utf8_substr() so the method does not return an empty string.
            if ( empty( $arrWords ) )
            {
                $arrWords[ ] = utf8_substr( $strChunk, 0, $intNumberOfChars );
            }

            $strEllipsis = ' …';
            break;
        }

        return implode( ' ', $arrWords ) . ( $blnAddEllipsis ? $strEllipsis : '' );
    }

    /**
     * Decode all entities
     *
     * @param      string
     * @param int $strQuoteStyle
     * @param bool $strCharset
     * @return string
     */
    public static function decodeEntities($strString, $strQuoteStyle = ENT_COMPAT, $strCharset = false)
    {

        if ( !strlen( $strString ) )
        {
            return '';
        }

        if ( !$strCharset )
        {
            $strCharset = 'utf-8';
        }

        $strString = preg_replace( '/(&#*\w+)[\x00-\x20]+;/i', '$1;', $strString );
        $strString = preg_replace( '/(&#x*)([0-9a-f]+);/i', '$1$2;', $strString );

        return html_entity_decode( $strString, $strQuoteStyle, $strCharset );
    }

    /**
     * Trim HTML Content (Valid)
     *
     * @param string $htmlInput
     * @param int $maxLength
     * @param string $allowedTags <br>
     *                            e.g.: '< b >, < i >,<em >,<strong >,<u >,< br >,<span >' (x)HTML Tags
     * @param string $ending default is null
     * @return string
     */
    public static function TrimHtml000($htmlInput, $maxLength = 200, $allowedTags = null, $ending = null)
    {
        $printedLength = 0;
        $position      = 0;
        $tags          = array();

        if ( $allowedTags === null )
        {
            $allowedTags = self::$allowedTags;
        }

        $html = self::removeDoubleSpace( str_replace( "\n", ' ', str_replace( "\r\n", ' ', $htmlInput ) ) );

        if ( $allowedTags !== '' )
        {
            if ( !stristr( $allowedTags, '<script>' ) )
            {
                $html = preg_replace( '/<script[^>]*>([^>]*)(<\/script>)?/isU', '', $html );
            }

            if ( !stristr( $allowedTags, '<blockquote>' ) )
            {
                $html = preg_replace( '/<blockquote[^>]*>([^>]*)(<\/blockquote>)?/isU', '\\1', $html );
            }

            if ( !stristr( $allowedTags, '<br>' ) && !stristr( $allowedTags, '<br/>' ) )
            {
                $html = preg_replace( '#<br\s*/?>#isU', " \n", $html );
            }

            $html = strip_tags( $html, $allowedTags );
        }


        $re = self::isUTF8( $html )
            ? '{</?([a-z]+)[^>]*>|&#?[a-zA-Z0-9]+;|[\x80-\xFF][\x80-\xBF]*}u'
            : '{</?([a-z]+)[^>]*>|&#?[a-zA-Z0-9]+;}';


        $htmlcode = '';

        while ( $printedLength < $maxLength && preg_match( $re, $html, $match, PREG_OFFSET_CAPTURE, $position ) )
        {
            list( $tag, $tagPosition ) = $match[ 0 ];

            // Print text leading up to the tag.
            $str = mb_substr( $html, $position, $tagPosition - $position );
            if ( $printedLength + mb_strlen( $str ) > $maxLength )
            {
                $htmlcode .= mb_substr( $str, 0, $maxLength - $printedLength );
                $printedLength = $maxLength;
                break;
            }

            $htmlcode .= $str;

            $printedLength += strlen( $str );

            if ( $printedLength >= $maxLength ) break;

            if ( $tag[ 0 ] == '&' || ord( $tag ) >= 0x80 )
            {
                // Pass the entity or UTF-8 multibyte sequence through unchanged.
                $htmlcode .= $tag;
                $printedLength++;
            }
            else
            {
                // Handle the tag.
                $tagName = $match[ 1 ][ 0 ];
                if ( $tag[ 1 ] == '/' )
                {
                    // This is a closing tag.
                    $openingTag = array_pop( $tags );
                    assert( $openingTag == $tagName ); // check that tags are properly nested.

                    $htmlcode .= $tag;
                }
                else if ( $tag[ strlen( $tag ) - 2 ] == '/' )
                {
                    // Self-closing tag.
                    $htmlcode .= $tag;
                }
                else
                {
                    // Opening tag.
                    $htmlcode .= $tag;
                    $tags[ ] = $tagName;
                }
            }

            // Continue after the tag.
            $position = $tagPosition + strlen( $tag );
        }

        // Print any remaining text.
        if ( $printedLength < $maxLength && $position < strlen( $html ) )
        {
            $htmlcode .= mb_substr( $html, $position, $maxLength - $printedLength );
        }


        // Close any open tags.
        while ( !empty( $tags ) )
        {
            $htmlcode .= sprintf( '</%s>', array_pop( $tags ) );
        }

        return $htmlcode;
    }


    /**
     * Trim HTML Content (Valid)
     *
     * @param string $htmlInput
     * @param int $maxLength
     * @param string $allowedTags <br>
     *                            e.g.: '< b >, < i >,<em >,<strong >,<u >,< br >,<span >' (x)HTML Tags
     * @param string $ending default is null
     * @return string
     */
    public static function TrimHtml($htmlInput, $maxLength = 200, $allowedTags = null, $ending = null)
    {

        if ( $allowedTags === null )
        {
            $allowedTags = self::$allowedTags;
        }

        $html = self::removeDoubleSpace( $htmlInput );
        #$html = self::fixUtf8($html);

        // remove html comments
        #	$html = preg_replace('#<!--.*-->#mU', '', $html);
        #	$html = self::Utf8ToAscii($html);

        if ( $allowedTags !== '' && $allowedTags !== null )
        {
            if ( !stristr( $allowedTags, '<script>' ) )
            {
                $html = preg_replace( '/<script[^>]*>([^>]*)(<\/script>)?/isU', '', $html );
            }

            if ( !stristr( $allowedTags, '<blockquote>' ) )
            {
                $html = preg_replace( '/<blockquote[^>]*>([^>]*)(<\/blockquote>)?/isU', '\\1', $html );
            }

            if ( !stristr( $allowedTags, '<br>' ) && !stristr( $allowedTags, '<br/>' ) )
            {
                $html = preg_replace( '#<br\s*/?>#isU', "\n", $html );
            }

            $html = strip_tags( $html, $allowedTags );
        }
        else
        {

            $html  = strip_tags( $html );
            $start = mb_strlen( $html );
            $html  = mb_substr( $html, 0, $maxLength );

            if ( $start > mb_strlen( $html ) && is_string( $ending ) )
            {
                $html .= $ending;
            }

            return $html;

        }

        $baseLength = strlen( $html );

        $trimmed       = false;
        $printedLength = 0;
        $position      = 0;
        $tags          = array();
        $match         = array();
        $_returnStr    = '';

        while ( $printedLength < $maxLength && preg_match( '#</?([a-zA-Z]+)[^>]*>|&\#?[a-zA-Z0-9]+;|[\x80-\xFF][\x80-\xBF]*#u', $html, $match, PREG_OFFSET_CAPTURE, $position ) )
        {
            list( $tag, $tagPosition ) = $match[ 0 ];

            // Print text leading up to the tag.
            $str = substr( $html, $position, $tagPosition - $position );


            if ( $str !== "\n" && $str !== "\r\n" && $printedLength + strlen( $str ) > $maxLength )
            {
                $_returnStr .= substr( $str, 0, $maxLength - $printedLength );
                $printedLength = $maxLength;
                break;
            }

            $_returnStr .= $str;

            $printedLength += strlen( $str );

            if ( $str !== "\n" && $str !== "\r\n" && $printedLength >= $maxLength ) {

                break;
            }


            if ( $tag[ 0 ] === '&' || ord( $tag ) >= 0x80 )
            {
                // Handle the entity.
                $_returnStr .= $tag;
                $printedLength++;
            }
            else
            {
                // Handle the tag.
                $tagName = $match[ 1 ][ 0 ];
                if ( $tag[ 1 ] === '/' )
                {
                    // This is a closing tag.
                    ini_set( 'assert.active', 1 );
                    assert_options( ASSERT_ACTIVE, 1 );
                    assert_options( ASSERT_WARNING, 1 );
                    assert_options( ASSERT_QUIET_EVAL, 1 );
                    assert_options( ASSERT_CALLBACK, 'Strings::assertFailed' );
                    $openingTag = array_pop( $tags );

                    if ( $openingTag )
                    {
                        assert( $openingTag == $tagName ); // check that tags are properly nested.
                    }
                    $_returnStr .= $tag;
                }
                else if ( $tag[ strlen( $tag ) - 2 ] === '/' )
                {
                    // Self-closing tag.
                    $_returnStr .= $tag;
                }
                else
                {
                    // Opening tag.
                    $_returnStr .= $tag;
                    $tags[ ] = $tagName;
                }
            }

            // Continue after the tag.
            $position = $tagPosition + strlen( $tag );

            unset( $tag, $tagPosition );
        }

        // Print any remaining text.
        if ( $printedLength < $maxLength && $position < strlen( $html ) )
        {
            $_returnStr .= substr( $html, $position, $maxLength - $printedLength );
        }

        // add the defined ending to the text
        if ( ( $baseLength != $printedLength || !empty( $tags ) ) )
        {
            $trimmed = true;
        }

        // Close any open tags.
        while ( !empty( $tags ) )
        {
            $_returnStr .= sprintf( '</%s>', array_pop( $tags ) );
        }

        // add the defined ending to the text
        if ( is_string( $ending ) && $trimmed )
        {
            $_returnStr .= $ending;
        }

        // $_returnStr = self::mbConvertTo($_returnStr, 'utf-8');

        unset( $tags, $match );


        return $_returnStr;
    }

    /**
     *
     * @param string $str
     * @param integer $len
     * @param string $wrap
     * @return string
     */
    static public function Wrap($str, $len = 80, $wrap = "\n")
    {

        return wordwrap( $str, $len, $wrap, true );
    }

    /**
     * Is this String a ISO-88591 ?
     *
     * @param string $str
     * @return int
     */
    public static function isISO88591($str)
    {

        return preg_match( '/^([\x09\x0A\x0D\x20-\x7E\xA0-\xFF])*$/', $str );
    }

    /**
     * @param string $str
     * @return int
     */
    public static function isCP1252($str)
    {

        return preg_match( '/^([\x09\x0A\x0D\x20-\x7E\x80\x82-\x8C\x8E\x91-\x9C\x9E-\xFF])*$/', $str );
    }

    /**
     * Seems like UTF-8?
     * hmdker at gmail dot com {@link php.net/utf8_encode}
     *
     * @param    string        Raw text
     * @return    boolean
     */
    public static function isUTF8($str)
    {

        return self::is_utf8( $str );
    }

    /**
     * Is input String a UTF-8 String?
     *
     * @param string $string
     * @return bool
     */
    public static function is_utf8($string)
    {

        if ( function_exists( 'mb_check_encoding' ) && is_callable( 'mb_check_encoding' ) )
        {
            return mb_check_encoding( $string, 'UTF8' );
        }

        // From http://w3.org/International/questions/qa-forms-utf-8.html
        return preg_match( '%^(?:
	          [\x09\x0A\x0D\x20-\x7E]            # ASCII
	        | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
	        |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
	        | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
	        |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
	        |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
	        | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
	        |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
	    )*$%xs', $string );
    }

    /**
     * Convert String from Unicode to UTF-8
     *
     * @param string $str
     * @return string
     */
    public static function UnicodeToUtf8($str)
    {

        $str = preg_replace_callback( '/&#([0-9]*);/', function ($m)
        {
            chr( intval( $m[ 1 ] ) );
        }, $str );

        return $str;
    }

    /**
     * Convert String from UTF-8 to ISO-8859
     *
     * @param string $str
     * @return string
     */
    public static function Utf8ToIso8859($str)
    {
        return preg_replace_callback( "/([\xC2\xC3])([\x80-\xBF])/S", function ($m)
        {
            return chr( ord( $m[ 1 ] ) << 6 & 0xC0 | ord( $m[ 2 ] ) & 0x3F );
        }, $str );
    }

    /**
     * Convert UTF8 to ASCII
     *
     * @param string $text
     * @return mixed|string
     */
    public static function Utf8ToAscii($text)
    {

        if ( is_string( $text ) )
        {
            // Includes combinations of characters that present as a single glyph
            $text = preg_replace_callback( '/\X/u', 'Strings::Utf8ToAscii', $text );
        }
        elseif ( is_array( $text ) && count( $text ) === 1 && is_string( $text[ 0 ] ) )
        {
            // IGNORE characters that can't be TRANSLITerated to ASCII
            $text = iconv( "UTF-8", "ASCII//IGNORE//TRANSLIT", $text[ 0 ] );

            // The documentation says that iconv() returns false on failure but it returns ''
            if ( $text === '' || !is_string( $text ) )
            {
                $text = '?';
            }
            elseif ( preg_match( '/\w/', $text ) )
            { // If the text contains any letters...
                $text = preg_replace( '/\W+/', '', $text ); // ...then remove all non-letters
            }
        }
        else
        { // $text was not a string
            $text = '';
        }

        return $text;
    }

    /**
     * @param $text
     * @return array|string
     */
    static function fixUTF8($text)
    {
        if ( is_array( $text ) )
        {
            foreach ( $text as $k => $v )
            {
                $text[ $k ] = self::fixUTF8( $v );
            }

            return $text;
        }

        $last = "";
        while ( $last <> $text )
        {
            $last = $text;
            $text = self::toUTF8( utf8_decode( str_replace( array_keys( self::$utf8ToWin1252 ), array_values( self::$utf8ToWin1252 ), $text ) ) );
        }
        $text = self::toUTF8( utf8_decode( str_replace( array_keys( self::$utf8ToWin1252 ), array_values( self::$utf8ToWin1252 ), $text ) ) );

        return $text;
    }

    /**
     * @param $str
     * @return mixed|string
     */
    public static function _fixUtf8($str)
    {

        $nstr = trim( self::fixLatin( $str ) );
        if ( $nstr != '' )
        {
            return $nstr;
        }

        $str = self::Utf8ToIso8859( $str );

        return preg_replace_callback( "/([\x80-\xFF])/S", function ($m)
        {
            return chr( 0xC0 | ord( $m[ 1 ] ) >> 6 ) . chr( 0x80 | ord( $m[ 1 ] ) & 0x3F );
        }, $str );
    }

    /**
     * Convert String from UTF-8 to Unicode
     *
     * @param string $str
     * @return string
     */
    public static function utf8_to_unicode($str)
    {

        if ( !self::is_utf8( $str ) )
        {
            return $str;
        }

        $values     = array();
        $lookingFor = 1;
        $unicode    = '';
        for ( $i = 0; $i < strlen( $str ); ++$i )
        {
            $thisValue = ord( $str[ $i ] );
            if ( $thisValue < 128 )
            {
                $unicode .= $str[ $i ];
            }
            else
            {
                if ( count( $values ) === 0 )
                {
                    $lookingFor = ( $thisValue < 224 ) ? 2 : 3;
                }
                $values[ ] = $thisValue;
                if ( count( $values ) === $lookingFor )
                {
                    $number = ( $lookingFor === 3 ) ? ( ( $values[ 0 ] % 16 ) * 4096 ) + ( ( $values[ 1 ] % 64 ) * 64 ) + ( $values[ 2 ] % 64 ) : ( ( $values[ 0 ] % 32 ) * 64 ) + ( $values[ 1 ] % 64 );
                    $unicode .= "&#" . $number . ";";
                    $values     = array();
                    $lookingFor = 1;
                }
            }
        }


        return $unicode;
    }


    // Convert illegal HTML numbered entities in the range 128 - 159 to legal couterparts
    /**
     * @param $str
     * @return mixed
     */
    public static function correctIllegalEntities($str)
    {
        $chars = array(
            128 => '&#8364;',
            130 => '&#8218;',
            131 => '&#402;',
            132 => '&#8222;',
            133 => '&#8230;',
            134 => '&#8224;',
            135 => '&#8225;',
            136 => '&#710;',
            137 => '&#8240;',
            138 => '&#352;',
            139 => '&#8249;',
            140 => '&#338;',
            142 => '&#381;',
            145 => '&#8216;',
            146 => '&#8217;',
            147 => '&#8220;',
            148 => '&#8221;',
            149 => '&#8226;',
            150 => '&#8211;',
            151 => '&#8212;',
            152 => '&#732;',
            153 => '&#8482;',
            154 => '&#353;',
            155 => '&#8250;',
            156 => '&#339;',
            158 => '&#382;',
            159 => '&#376;');
        foreach ( array_keys( $chars ) as $num )
            $str = str_replace( "&#" . $num . ";", $chars[ $num ], $str );

        return $str;
    }


    /**
     * @param $m
     * @return string
     */
    public static function fixFourByteChars($m)
    {
        return '&#' . ( ( ord( $m[ 1 ] ) - 240 ) * 262144 + ( ord( $m[ 2 ] ) - 128 ) * 4096 + ( ord( $m[ 3 ] ) - 128 ) * 64 + ( ord( $m[ 4 ] ) - 128 ) ) . ';';
    }

    /**
     * @param $m
     * @return string
     */
    public static function fixTreeByteChars($m)
    {
        return '&#' . ( ( ord( $m[ 1 ] ) - 224 ) * 4096 + ( ord( $m[ 2 ] ) - 128 ) * 64 + ( ord( $m[ 3 ] ) - 128 ) ) . ';';
    }

    /**
     * @param $m
     * @return string
     */
    public static function fixTwoByteChars($m)
    {
        return '&#' . ( ( ord( $m[ 1 ] ) - 192 ) * 64 + ( ord( $m[ 2 ] ) - 128 ) ) . ';';
    }


    /**
     * Converts UTF-8 into HTML entities (&#1xxx;) for correct display in browsers
     *
     * @param  string $string UTF8 Encoded string
     * @return string converted into HTML entities (similar to what a browser does with POST)
     */
    public static function utf8ToEntities($string)
    {

        if ( !trim( $string ) )
        {
            return $string;
        }


        mb_internal_encoding( "UTF-8" );

        if ( !self::is_utf8( $string ) )
        {
            // return $string;
        }

        // return htmlentities($string, ENT_COMPAT, 'UTF-8');

        //$string = preg_replace_callback( "/([\360-\364])([\200-\277])([\200-\277])([\200-\277])/", array('Strings', 'fixFourByteChars'), $string );

        //$string = preg_replace_callback( "/([\340-\357])([\200-\277])([\200-\277])/", array('Strings', 'fixTreeByteChars'), $string );

        //$string = preg_replace_callback( "/([\300-\337])([\200-\277])/", array('Strings', 'fixTwoByteChars'), $string );

        # Four-byte chars
        $string = preg_replace_callback( "/([\360-\364])([\200-\277])([\200-\277])([\200-\277])/", create_function( '$m',
            'return \'&#\' . ( ( ord( $m[ 1 ] ) - 240 ) * 262144 + ( ord( $m[ 2 ] ) - 128 ) * 4096 + ( ord( $m[ 3 ] ) - 128 ) * 64 + ( ord( $m[ 4 ] ) - 128 ) ) . \';\';' ), $string );

        // Three byte chars
        $string = preg_replace_callback( "/([\340-\357])([\200-\277])([\200-\277])/", create_function( '$m',
            'return \'&#\' . ( ( ord( $m[ 1 ] ) - 224 ) * 4096 + ( ord( $m[ 2 ] ) - 128 ) * 64 + ( ord( $m[ 3 ] ) - 128 ) ) . \';\';' ), $string );

        // Two byte chars
        $string = preg_replace_callback( "/([\300-\337])([\200-\277])/", create_function( '$m',
            'return \'&#\' . ( ( ord( $m[ 1 ] ) - 192 ) * 64 + ( ord( $m[ 2 ] ) - 128 ) ) . \';\';' ), $string );


        /*

        # Four-byte chars
        $string = preg_replace_callback( "/([\360-\364])([\200-\277])([\200-\277])([\200-\277])/", function ($m)
        {
            return '&#' . ( ( ord( $m[ 1 ] ) - 240 ) * 262144 + ( ord( $m[ 2 ] ) - 128 ) * 4096 + ( ord( $m[ 3 ] ) - 128 ) * 64 + ( ord( $m[ 4 ] ) - 128 ) ) . ';';
        }, $string );

        // Three byte chars
        $string = preg_replace_callback( "/([\340-\357])([\200-\277])([\200-\277])/", function ($m)
        {
            return '&#' . ( ( ord( $m[ 1 ] ) - 224 ) * 4096 + ( ord( $m[ 2 ] ) - 128 ) * 64 + ( ord( $m[ 3 ] ) - 128 ) ) . ';';
        }, $string );

        // Two byte chars
        $string = preg_replace_callback( "/([\300-\337])([\200-\277])/", function ($m)
        {
            return '&#' . ( ( ord( $m[ 1 ] ) - 192 ) * 64 + ( ord( $m[ 2 ] ) - 128 ) ) . ';';
        }, $string );

*/

        return $string;


    }

    /**
     * helper for fixLatin
     *
     * @staticvar array $byte_map
     * @return array
     */
    private static function initByteMap()
    {

        static $byte_map;

        if ( !is_array( $byte_map ) )
        {
            $byte_map = array();

            for ( $x = 128; $x < 256; ++$x )
            {
                $byte_map[ chr( $x ) ] = utf8_encode( chr( $x ) );
            }

            $cp1252_map = array(
                "\x80" => "\xE2\x82\xAC", // EURO SIGN
                "\x82" => "\xE2\x80\x9A", // SINGLE LOW-9 QUOTATION MARK
                "\x83" => "\xC6\x92", // LATIN SMALL LETTER F WITH HOOK
                "\x84" => "\xE2\x80\x9E", // DOUBLE LOW-9 QUOTATION MARK
                "\x85" => "\xE2\x80\xA6", // HORIZONTAL ELLIPSIS
                "\x86" => "\xE2\x80\xA0", // DAGGER
                "\x87" => "\xE2\x80\xA1", // DOUBLE DAGGER
                "\x88" => "\xCB\x86", // MODIFIER LETTER CIRCUMFLEX ACCENT
                "\x89" => "\xE2\x80\xB0", // PER MILLE SIGN
                "\x8A" => "\xC5\xA0", // LATIN CAPITAL LETTER S WITH CARON
                "\x8B" => "\xE2\x80\xB9", // SINGLE LEFT-POINTING ANGLE QUOTATION MARK
                "\x8C" => "\xC5\x92", // LATIN CAPITAL LIGATURE OE
                "\x8E" => "\xC5\xBD", // LATIN CAPITAL LETTER Z WITH CARON
                "\x91" => "\xE2\x80\x98", // LEFT SINGLE QUOTATION MARK
                "\x92" => "\xE2\x80\x99", // RIGHT SINGLE QUOTATION MARK
                "\x93" => "\xE2\x80\x9C", // LEFT DOUBLE QUOTATION MARK
                "\x94" => "\xE2\x80\x9D", // RIGHT DOUBLE QUOTATION MARK
                "\x95" => "\xE2\x80\xA2", // BULLET
                "\x96" => "\xE2\x80\x93", // EN DASH
                "\x97" => "\xE2\x80\x94", // EM DASH
                "\x98" => "\xCB\x9C", // SMALL TILDE
                "\x99" => "\xE2\x84\xA2", // TRADE MARK SIGN
                "\x9A" => "\xC5\xA1", // LATIN SMALL LETTER S WITH CARON
                "\x9B" => "\xE2\x80\xBA", // SINGLE RIGHT-POINTING ANGLE QUOTATION MARK
                "\x9C" => "\xC5\x93", // LATIN SMALL LIGATURE OE
                "\x9E" => "\xC5\xBE", // LATIN SMALL LETTER Z WITH CARON
                "\x9F" => "\xC5\xB8" // LATIN CAPITAL LETTER Y WITH DIAERESIS
            );
            foreach ( $cp1252_map as $k => $v )
            {
                $byte_map[ $k ] = $v;
            }
        }

        return $byte_map;
    }

    /**
     * I think this is a reasonable port of Perl's Encoding::FixLatin by Grant McLean,
     * which converts a string with mixed encodings (ASCII, ISO-8859-1, CP1252, and UTF-8) to UTF-8.
     *
     * @param string $instr
     * @return string
     */
    public static function fixLatin($instr)
    {

        if ( function_exists( 'mb_check_encoding' ) )
        {
            if ( mb_check_encoding( $instr, 'UTF-8' ) )
            {
                return $instr; // no need for the rest if it's all valid UTF-8 already
            }
        }

        $byte_map = self::initByteMap();

        $ascii_char        = '[\x00-\x7F]';
        $cont_byte         = '[\x80-\xBF]';
        $utf8_2            = '[\xC0-\xDF]' . $cont_byte;
        $utf8_3            = '[\xE0-\xEF]' . $cont_byte . '{2}';
        $utf8_4            = '[\xF0-\xF7]' . $cont_byte . '{3}';
        $utf8_5            = '[\xF8-\xFB]' . $cont_byte . '{4}';
        $nibble_good_chars = "@^($ascii_char+|$utf8_2|$utf8_3|$utf8_4|$utf8_5)(.*)$@s";


        $outstr = '';
        $char   = '';
        $rest   = '';
        while ( strlen( $instr ) > 0 )
        {
            if ( 1 === preg_match( $nibble_good_chars, $instr, $match ) )
            {
                $char = $match[ 1 ];
                $rest = $match[ 2 ];
                $outstr .= $char;
            }
            elseif ( 1 === preg_match( '@^(.)(.*)$@s', $instr, $match ) )
            {
                $char = $match[ 1 ];
                $rest = $match[ 2 ];
                $outstr .= $byte_map[ $char ];
            }
            $instr = $rest;
        }


        return $outstr;
    }

    /**
     *
     * @param string $source
     * @param string $target_encoding
     * @param string $detect
     * @return string
     */
    public static function mbConvertTo($source, $target_encoding, $detect = 'auto')
    {


        if ( !function_exists( 'mb_convert_encoding' ) )
        {
            return $source;
        }

        mb_internal_encoding( "UTF-8" );


        #$source = html_entity_decode( $source, ENT_COMPAT, 'utf-8' );
        $source = self::fixLatin( $source );

        // detect the character encoding of the incoming file
        $encoding = mb_detect_encoding( $source, $detect, true );


        // escape all of the question marks so we can remove artifacts from
        // the unicode conversion process
        $target = str_replace( "?", "[question_mark]", $source );

       # if ( strtolower( $target_encoding ) === 'utf-8' && strtolower( $encoding ) !== 'utf-8' )
       # {
            if ( function_exists( 'iconv' ) )
            {
                // convert the string to the target encoding
                $target = iconv( $encoding, $target_encoding, $target );
            }
            elseif ( function_exists( 'mb_convert_encoding' ) )
            {
                // convert the string to the target encoding
                $target = mb_convert_encoding( $target, $target_encoding, $encoding );
            }
       # }

        // remove any question marks that have been introduced because of illegal characters
        $target = str_replace( "?", "", $target );

        $target = str_replace( 'Â ', ' ', $target ); // remove old broken convertions
        $target = str_replace( 'â¦', '', $target );
        $target = str_replace( 'Â', '', $target );
        #$target = self::utf8ToEntities( $target );
        #$target = preg_replace_callback('/[^(\x20-\x7F)]*/x','', $target);


        // replace the token string "[question_mark]" with the symbol "?"
        return str_replace( "[question_mark]", "?", $target );
    }


    /**
     * @param $captures
     * @return string
     */
    public static function utf8replacer($captures)
    {
        if ( !empty( $captures[ 1 ] ) )
        {
            // Valid byte sequence. Return unmodified.
            return $captures[ 1 ];
        }
        elseif ( !empty( $captures[ 2 ] ) )
        {
            // Invalid byte of the form 10xxxxxx.
            // Encode as 11000010 10xxxxxx.
            return "\xC2" . $captures[ 2 ];
        }
        else
        {
            // Invalid byte of the form 11xxxxxx.
            // Encode as 11000011 10xxxxxx.
            return "\xC3" . $captures[ 3 ];
        }
    }


    /**
     * @param $str
     * @return mixed
     */
    public static function fixForXml($str)
    {

        return preg_replace( '/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $str );
    }

    /**
     *
     * @param string $str
     * @return string
     */
    public static function fixAmpsan($str)
    {

        return preg_replace( '/&(?!#[a-z0-9]+;)/si', '&amp;', str_replace( '&amp;', '&', $str ) );
    }

    /**
     *
     * @param string $text
     * @return string
     */
    public static function htmlspecialcharsUnicode($text)
    {

        // this is a version of htmlspecialchars that still allows unicode to function correctly
        $text = preg_replace( '/&(?!#[0-9]+;)/si', '&amp;', $text ); // translates all non-unicode entities
        return str_replace( array(
            '<',
            '>',
            '"'
        ), array(
            '&lt;',
            '&gt;',
            '&quot;'
        ), $text );
    }

    /**
     *
     * @param string $text
     * @param boolean $doUniCode default is false
     * @return string
     */
    public static function unhtmlSpecialchars($text, $doUniCode = false)
    {

        if ( $doUniCode )
        {
            $text = preg_replace( '/&#([0-9]*);/esiU', "chr(intval('\\1'))", $text );
        }

        return str_replace( array(
            '&lt;',
            '&gt;',
            '&quot;',
            '&amp;'
        ), array(
            '<',
            '>',
            '"',
            '&'
        ), $text );
    }

    /**
     *
     * Converted HTML entitys to chars
     *
     * @staticvar array $trans_entitles
     * @staticvar array $trans_specialchars
     * @param string $text
     * @param string $fromencoding
     * @return string
     */
    public static function rehtmlconverter($text, $fromencoding = null)
    {

        static $trans_entitles;
        static $trans_specialchars;

        if ( !is_null( $fromencoding ) )
        {
            $charset = strtolower( $fromencoding );
        }
        else
        {
            $charset = "utf-8";
        }

        if ( function_exists( 'html_entity_decode' ) && ( $charset == 'iso-8859-1' || $charset == 'iso-8859-15' || $charset == 'utf-8' || $charset == 'cp1252' || $charset == 'windows-1252' || $charset == 'koi8-r' || $charset == 'big5' || $charset == 'gb2312' || $charset == 'big5-hkscs' || $charset == 'shift_jis' || $charset == 'euc-jp' )
        )
        {

            return html_entity_decode( $text, ENT_COMPAT, $charset );
        }
        elseif ( $charset === 'iso-8859-1' || $charset === 'windows-1252' )
        {
            if ( !is_array( $trans_entitles ) )
            {
                $trans_entitles = get_html_translation_table( HTML_ENTITIES );
            }
        }
        else
        {
            if ( !is_array( $trans_specialchars ) )
            {
                $trans_specialchars = get_html_translation_table( HTML_SPECIALCHARS );
            }
        }

        // replace numeric entities
        $text = preg_replace( '~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $text );
        $text = preg_replace( '~&#([0-9]+);~e', 'chr("\\1")', $text );

        if ( $charset === 'iso-8859-1' || $charset === 'windows-1252' )
        {
            $trans_tbl = $trans_entitles;
        }
        else
        {
            $trans_tbl = $trans_specialchars;
        }

        $trans_tbl = array_flip( $trans_tbl );

        return strtr( $text, $trans_tbl );
    }

    /**
     * Will generate Core Tags from tinyMCE tags (only urls)
     *
     * @param string $string
     * @return string
     */
    public static function tinyMCECoreTags($string)
    {

        return preg_replace_callback( '/<span[^>]*mceNonEditable[^>]*>[^><]*<a([^>]*)>([^><]*)<\/a>[^><]*<\/span>/is', array(
            'self',
            'fixtinyMCECoreTags'
        ), $string );
    }

    /**
     * Will generate Core Tags
     *
     * @param array $match
     * @return string
     */
    private static function fixtinyMCECoreTags($match)
    {

        $fulltag = $match[ 0 ];
        if ( strpos( $fulltag, 'dcmsCTag' ) === false )
        {
            return $fulltag;
        }


        $attribute = $match[ 1 ];
        $title     = $match[ 2 ];

        $contentid = Html::getAttribute( 'contentid', $attribute );
        $modul     = Html::getAttribute( 'modul', $attribute );
        $isapp     = Html::getAttribute( 'isapp', $attribute );

        // replace the title with the original document title
        $notitle = Html::getAttribute( 'notitle', $attribute );


        if ( !is_null( $isapp ) )
        {
            $cat = '';
            if ( substr( $modul, -3 ) === 'cat' )
            {
                $modul = substr( $modul, 0, -3 );
                $cat   = 'cat';
            }

            $_modul = '';

            switch ( $modul )
            {
                case 'documentation':
                    $_modul = 'docu';
                    break;
                case 'blog':
                    $_modul = 'blog';
                    break;
                case 'page':
                    $_modul = 'page';
                    break;

                default:
                    $_modul = $modul;
                    break;
            }

            if ( !$_modul )
            {
                return $title;
            }

            if ( !is_null( $notitle ) && $notitle )
            {
                return '{' . $_modul . $cat . ':' . $contentid . '}';
            }
            else
            {
                return '<a href="{' . $_modul . $cat . 'link:' . $contentid . '}">' . $title . '</a>';
            }
        }
        else
        {
            if ( $notitle )
            {
                return '[coretag modul="' . $modul . $cat . '" contentid="' . $contentid . '" type=\'link\']';
            }

            return '<a href="[coretag modul=\'' . $modul . $cat . '\' contentid=\'' . $contentid . '\' type=\'url\']">' . $title . '</a>';
        }
    }

    /**
     * Clean string for Searching Content
     *
     * @param string $str
     * @return string
     */
    public static function cleanSearchString($str)
    {

        $searchString = preg_replace( '/([\'#\?])/U', '', $str );
        $searchString = preg_replace( '/([\ ;,][\||\+|\-]?)\ \+/sU', '\\1', $searchString );

        // %tp% FIX
        $searchString = str_replace( '%tp%', '\%tp\%', $searchString );
        $searchString = str_replace( '%', '\%', $searchString );
        $searchString = str_replace( '/', ' ', $searchString );

        return str_replace( '*', '%', $searchString );
    }

    /**
     * Returns a String with all alpha chars only
     *
     * @param sting $str
     * @param integer $length the length of the return string
     *
     * @return string Returns a String with all alpha chars only. If the string empty the returns 'other'!
     */
    public static function getFirstCharsForFilename($str, $length = 1)
    {

        $str       = preg_replace( '/[^a-z0-9]/iS', '', $str );
        $strlength = strlen( $str );

        if ( !$strlength )
        {
            return 'other';
        }

        if ( $length > $strlength )
        {
            return $str;
        }

        return substr( $str, 0, $length );
    }

}
