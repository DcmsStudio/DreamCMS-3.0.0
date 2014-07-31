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
 * @file         Minifier.php
 */

class Minifier
{
    /**
     * The input javascript to be minified.
     *
     * @var string
     */
    protected $input;

    /**
     * The location of the character (in the input string) that is next to be
     * processed.
     *
     * @var int
     */
    protected $index = 0;

    /**
     * The first of the characters currently being looked at.
     *
     * @var string
     */
    protected $a = '';

    /**
     * The next character being looked at (after a);
     *
     * @var string
     */
    protected $b = '';

    /**
     * This character is only active when certain look ahead actions take place.
     *
     *  @var string
     */
    protected $c;

    /**
     * Contains the options for the current minification process.
     *
     * @var array
     */
    protected $options;


    /**
     * Contains the default options for minification. This array is merged with
     * the one passed in by the user to create the request specific set of
     * options (stored in the $options attribute).
     *
     * @var array
     */
    protected static $defaultOptions = array('flaggedComments' => true);

    /**
     * Contains lock ids which are used to replace certain code patterns and
     * prevent them from being minified
     *
     * @var array
     */
    protected $locks = array();


    // The maximum size of an embedded file. (use 0 for unlimited size)
    private static $embedMaxSize = 25120; //5KB

    private static $embed = true;

    private static $embedExceptions = array('htc');

    private static $fileDir = '';

    private static $maxUniqueFiles = 2; // max unique images to convert base64


    private static $mimeTypes = array(
        "js"   => "text/javascript",
        "css"  => "text/css",
        "htm"  => "text/html",
        "html" => "text/html",
        "xml"  => "text/xml",
        "txt"  => "text/plain",
        "jpg"  => "image/jpeg",
        "jpeg" => "image/jpeg",
        "png"  => "image/png",
        "gif"  => "image/gif",
        "swf"  => "application/x-shockwave-flash",
        "ico"  => "image/x-icon",
    );

    private static $_imgMimes = array(
        "jpg"  => "image/jpeg",
        "jpeg" => "image/jpeg",
        "png"  => "image/png",
        "gif"  => "image/gif",
    );

    /**
     * @param $url
     * @param $count
     * @return mixed|string
     */
    private static function convertUrl($url, $count)
    {
        static $baseUrl = '';

        $url = trim( $url );

        if ( preg_match( '@^[^/]+:@', $url ) ) return $url;

        $fileType = substr( strrchr( $url, '.' ), 1 );





        $orginalurl = $url;
        $filepath = self::$fileDir . $url;
        $backwards = substr_count($url, '..');

        if ( $backwards )
        {
            $curr = explode('/', self::$fileDir );

            # $p = array();
            for ( $i = 0; $i < $backwards + 1; ++$i )
            {
                $pop = array_pop($curr);
            }

            $__currentPath = implode('/', $curr);

            if ( substr($__currentPath, -1) != '/' )
            {
                $__currentPath .= '/';
            }

            $url = '/' . $__currentPath . str_replace(array (
                    '../',
                    './'
                ), '', $url);

            if ( substr($url, 0, 2) == '//' )
            {
                $url = substr($url, 1);
            }

            $filepath = $url;
        }
        else {
            $url = preg_replace('#^(\./)#','', $url);

            $filepath = self::$fileDir . $url;
        }


        if (strpos($filepath, 'fancybox') !== false) {
            // die($filepath . ' '. $count);
        }

        if ( isset( self::$mimeTypes[ $fileType ] ) ) $mimeType = self::$mimeTypes[ $fileType ];
        elseif ( file_exists( $filepath ) && function_exists( 'mime_content_type' ) ) $mimeType = mime_content_type( $url );
        else $mimeType = null;


        if ( !self::$embed ||
            !file_exists( $filepath ) ||
            ( self::$embedMaxSize > 0 && filesize( $filepath ) > self::$embedMaxSize ) ||
            !$fileType ||
            in_array( $fileType, self::$embedExceptions ) ||
            !$mimeType ||
            $count > self::$maxUniqueFiles
        )
        {
            if ( strpos( $_SERVER[ 'REQUEST_URI' ], $_SERVER[ 'SCRIPT_NAME' ] . '?' ) === 0 ||
                strpos( $_SERVER[ 'REQUEST_URI' ], rtrim( dirname( $_SERVER[ 'SCRIPT_NAME' ] ), '\/' ) . '/?' ) === 0
            )
            {
                if ( !$baseUrl ) return $filepath;
            }

            return $orginalurl;
        }




        $contents = file_get_contents( $filepath );

        if ( $fileType == 'css' )
        {
            $oldFileDir = self::$fileDir;

            self::$fileDir = rtrim( dirname( $orginalurl ), '\/' ) . '/';

            $oldBaseUrl    = $baseUrl;
            $baseUrl       = 'http' . ( isset($_SERVER[ 'HTTPS' ]) ? 's' : '' ) . '://' . $_SERVER[ 'HTTP_HOST' ] . rtrim( dirname( $_SERVER[ 'SCRIPT_NAME' ] ), '\/' ) . '/' . str_replace(ROOT_PATH, '', self::$fileDir );

            $cssCode = self::minifyCss( $contents, self::$fileDir );

            self::$fileDir = $oldFileDir;
            $baseUrl       = $oldBaseUrl;

            return $cssCode;
        }

        $base64 = base64_encode( $contents );
        return 'data:' . $mimeType . ';base64,' . $base64;
    }

    /**
     *
     * @param string $code
     * @param string $currentPath
     * @return string
     */
    private static function getCssImports ( $code, $currentPath )
    {

        preg_match_all('#(@charset\s*([\'"])([^\2]*)\2\s*(;))#isU', $code, $charsetmatches, PREG_SET_ORDER);
        preg_match_all('#(@import\s*url\(([^\)]*)\)\s*(;))#isU', $code, $matches, PREG_SET_ORDER);

        if ( substr($currentPath, -1) != '/' )
        {
            $currentPath .= '/';
        }

       # $code = self::fixCssUrlRule($code, $currentPath);

        if ( is_array($matches) )
        {
            $cachePath = PAGE_URL_PATH . '.cache/data/assets/';

            foreach ( $matches as $idx => $m )
            {
                $f = str_replace('"', '', $m[2]);
                $f = str_replace('\'', '', $f);

                if ( file_exists($currentPath . $f) )
                {

                    $_code = file_get_contents($currentPath . $f);

                    // fix url("../path")
                   # $_code = self::fixCssUrlRule($_code, $currentPath);
                    $code  = str_replace($m[1], $_code, $code);
                }
                else
                {
                    $code = str_replace($m[1], '/** CSS File "' . $f . '" not exists! **/', $code);
                }
            }
        }



        if ( is_array($charsetmatches) )
        {
            $charsetTag = '';

            foreach ( $charsetmatches as $idx => $cs )
            {
                $charsetTag = $cs[1];
                $code = str_replace($cs[1], '', $code);
            }

            if ( $charsetTag )
            {
                $code = $charsetTag . $code;
            }

        }


        $css = $code;

        // Normalize all whitespace strings to single spaces. Easier to work with that way.
        $css = preg_replace('@\s+@', ' ', $css);

        $css = preg_replace("@\\s+([!{};:>+\\(\\)\\],])@", "$1", $css);

        // Remove the spaces after the things that should not have spaces after them.
        $css = preg_replace("@([!{}:;>+\\(\\[,])\\s+@", "$1", $css);

        // Add the semicolon where it's missing.
        $css = preg_replace("@([^;\\}])}@", "$1;}", $css);

        // Replace 0(px,em,%) with 0.
        $css = preg_replace("@([\\s:])(0)(px|em|%|in|cm|mm|pc|pt|ex)@", "$1$2", $css);

        // Replace 0 0 0 0; with 0.
        $css = str_replace(":0 0 0 0;", ":0;", $css);
        $css = str_replace(":0 0 0;", ":0;", $css);
        $css = str_replace(":0 0;", ":0;", $css);

        // Replace background-position:0; with background-position:0 0;
        $css = str_replace("background-position:0;", "background-position:0 0;", $css);

        // Replace 0.6 to .6, but only when preceded by : or a white-space
        $css = preg_replace("@(:|\\s)0+\\.(\\d+)@", "$1.$2", $css);

        // Shorten colors from rgb(51,102,153) to #336699
        // This makes it more likely that it'll get further compressed in the next step.
        $css = preg_replace_callback("@rgb\\s*\\(\\s*([0-9,\\s]+)\\s*\\)@", array('Minifier', '_shortenRgbCB'), $css);

        // Shorten colors from #AABBCC to #ABC. Note that we want to make sure
        // the color is not preceded by either ", " or =. Indeed, the property
        //     filter: chroma(color="#FFFFFF");
        // would become
        //     filter: chroma(color="#FFF");
        // which makes the filter break in IE.
        $css = preg_replace_callback("@([^\"'=\\s])(\\s*)#([0-9a-fA-F])([0-9a-fA-F])([0-9a-fA-F])([0-9a-fA-F])([0-9a-fA-F])([0-9a-fA-F])@", array('Minifier', '_shortenHexCB'), $css);


        //
        return $css;
    }

    /**
     * @param $m
     * @return string
     */
    public static function _shortenRgbCB($m)
    {
        $rgbcolors = explode(',', $m[1]);
        $hexcolor = '#';
        for ($i = 0; $i < count($rgbcolors); $i++) {
            $val = round($rgbcolors[$i]);
            if ($val < 16) {
                $hexcolor .= '0';
            }
            $hexcolor .= dechex($val);
        }
        return $hexcolor;
    }

    /**
     * @param $m
     * @return string
     */
    public static function _shortenHexCB($m)
    {
        // Test for AABBCC pattern
        if ((strtolower($m[3])===strtolower($m[4])) &&
            (strtolower($m[5])===strtolower($m[6])) &&
            (strtolower($m[7])===strtolower($m[8]))) {
            return $m[1] . $m[2] . "#" . $m[3] . $m[5] . $m[7];
        } else {
            return $m[0];
        }
    }


    /**
     * @param string $str
     * @param string $curentPath
     * @return string
     */
    public static function minifyCss($str, $curentPath = '')
    {
        self::$fileDir = $curentPath;

        $str = self::getCssImports($str, $curentPath);


        $res          = '';
        $i            = 0;
        $inside_block = false;
        $current_char = '';
        while ( $i + 1 < strlen( $str ) )
        {
            if ( $str[ $i ] == '"' || $str[ $i ] == "'" )
            {
                //quoted string detected
                $res .= $quote = $str[ $i++ ];
                $url = '';
                while ( $i < strlen( $str ) && $str[ $i ] != $quote )
                {
                    if ( $str[ $i ] == '\\' )
                    {
                        $url .= $str[ $i++ ];
                    }
                    $url .= $str[ $i++ ];
                }



                if ( strtolower( substr( $res, -5, 4 ) ) == 'url(' || strtolower( substr( $res, -9, 8 ) ) == '@import ' )
                {
                    $url = self::convertUrl( $url, substr_count( $str, $url ) );

                }

                $res .= $url;
                $res .= $str[ $i++ ];


                continue;
            }
            elseif ( strtolower( substr( $res, -4 ) ) == 'url(' )
            { //url detected
                $url = '';
                do
                {
                    if ( $str[ $i ] == '\\' )
                    {
                        $url .= $str[ $i++ ];
                    }
                    $url .= $str[ $i++ ];
                }
                while ( $i < strlen( $str ) && $str[ $i ] != ')' );

                $url = self::convertUrl( $url, substr_count( $str, $url ) );

                if ( strpos($url, 'data:') !== false ) {
                    $url = '"' . $url .'"';
                }

                $res .= $url;
                $res .= $str[ $i++ ];


                continue;
            }
            elseif ( $str[ $i ] . $str[ $i + 1 ] == '/*' )
            { //css comment detected
                $i += 3;
                while ( $i < strlen( $str ) && $str[ $i - 1 ] . $str[ $i ] != '*/' ) $i++;
                if ( $current_char == "\n" ) $str[ $i ] = "\n";
                else $str[ $i ] = ' ';
            }

            if ( strlen( $str ) <= $i + 1 ) break;

            $current_char = $str[ $i ];

            if ( $inside_block && $current_char == '}' )
            {
                $inside_block = false;
            }

            if ( $current_char == '{' )
            {
                $inside_block = true;
            }

            if ( preg_match( '/[\n\r\t ]/', $current_char ) ) $current_char = " ";

            if ( $current_char == " " )
            {
                $pattern = $inside_block ? '/^[^{};,:\n\r\t ]{2}$/' : '/^[^{};,>+\n\r\t ]{2}$/';
                if ( strlen( $res ) && preg_match( $pattern, $res[ strlen( $res ) - 1 ] . $str[ $i + 1 ] ) )
                    $res .= $current_char;
            }
            else $res .= $current_char;

            $i++;
        }
        if ( $i < strlen( $str ) && preg_match( '/[^\n\r\t ]/', $str[ $i ] ) ) $res .= $str[ $i ];

        return $res;
    }









    /**
     * Takes a string containing javascript and removes unneeded characters in
     * order to shrink the code without altering it's functionality.
     *
     * @param  string      $js      The raw javascript to be minified
     * @param  array       $options Various runtime options in an associative array
     * @throws BaseException
     * @return bool|string
     */
    public static function minifyJs($js, $options = array())
    {
        return self::minifyJs0($js);
        try {
            ob_start();

            $jshrink = new Minifier();
            $js = $jshrink->lock($js);
            $jshrink->minifyDirectToOutput($js, $options);

            // Sometimes there's a leading new line, so we trim that out here.
            $js = ltrim(ob_get_clean());
            $js = $jshrink->unlock($js);
            unset($jshrink);

            return $js;

        } catch (Exception $e) {

            if (isset($jshrink)) {
                // Since the breakdownScript function probably wasn't finished
                // we clean it out before discarding it.
                $jshrink->clean();
                unset($jshrink);
            }

            // without this call things get weird, with partially outputted js.
            ob_end_clean();
            throw new BaseException($e->getMessage());
        }
    }

    /**
     * Processes a javascript string and outputs only the required characters,
     * stripping out all unneeded characters.
     *
     * @param string $js      The raw javascript to be minified
     * @param array  $options Various runtime options in an associative array
     */
    protected function minifyDirectToOutput($js, $options)
    {
        $this->initialize($js, $options);
        $this->loop();
        $this->clean();
    }

    /**
     *  Initializes internal variables, normalizes new lines,
     *
     * @param string $js      The raw javascript to be minified
     * @param array  $options Various runtime options in an associative array
     */
    protected function initialize($js, $options)
    {
        $this->options = array_merge(static::$defaultOptions, $options);
        $js = str_replace("\r\n", "\n", $js);
        $this->input = str_replace("\r", "\n", $js);

        // We add a newline to the end of the script to make it easier to deal
        // with comments at the bottom of the script- this prevents the unclosed
        // comment error that can otherwise occur.
        $this->input .= PHP_EOL;

        // Populate "a" with a new line, "b" with the first character, before
        // entering the loop
        $this->a = "\n";
        $this->b = $this->getReal();
    }

    /**
     * The primary action occurs here. This function loops through the input string,
     * outputting anything that's relevant and discarding anything that is not.
     */
    protected function loop()
    {
        while ($this->a !== false && !is_null($this->a) && $this->a !== '') {

            switch ($this->a) {
                // new lines
                case "\n":
                    // if the next line is something that can't stand alone preserve the newline
                    if (strpos('(-+{[@', $this->b) !== false) {
                        echo $this->a;
                        $this->saveString();
                        break;
                    }

                    // if B is a space we skip the rest of the switch block and go down to the
                    // string/regex check below, resetting $this->b with getReal
                    if($this->b === ' ')
                        break;

                // otherwise we treat the newline like a space

                case ' ':
                    if(static::isAlphaNumeric($this->b))
                        echo $this->a;

                    $this->saveString();
                    break;

                default:
                    switch ($this->b) {
                        case "\n":
                            if (strpos('}])+-"\'', $this->a) !== false) {
                                echo $this->a;
                                $this->saveString();
                                break;
                            } else {
                                if (static::isAlphaNumeric($this->a)) {
                                    echo $this->a;
                                    $this->saveString();
                                }
                            }
                            break;

                        case ' ':
                            if(!static::isAlphaNumeric($this->a))
                                break;

                        default:
                            // check for some regex that breaks stuff
                            if ($this->a == '/' && ($this->b == '\'' || $this->b == '"')) {
                                $this->saveRegex();
                                continue;
                            }

                            echo $this->a;
                            $this->saveString();
                            break;
                    }
            }

            // do reg check of doom
            $this->b = $this->getReal();

            if(($this->b == '/' && strpos('(,=:[!&|?', $this->a) !== false))
                $this->saveRegex();
        }
    }

    /**
     * Resets attributes that do not need to be stored between requests so that
     * the next request is ready to go. Another reason for this is to make sure
     * the variables are cleared and are not taking up memory.
     */
    protected function clean()
    {
        unset($this->input);
        $this->index = 0;
        $this->a = $this->b = '';
        unset($this->c);
        unset($this->options);
    }

    /**
     * Returns the next string for processing based off of the current index.
     *
     * @return string
     */
    protected function getChar()
    {
        // Check to see if we had anything in the look ahead buffer and use that.
        if (isset($this->c)) {
            $char = $this->c;
            unset($this->c);

            // Otherwise we start pulling from the input.
        } else {
            $char = substr($this->input, $this->index, 1);

            // If the next character doesn't exist return false.
            if (isset($char) && $char === false) {
                return false;
            }

            // Otherwise increment the pointer and use this char.
            $this->index++;
        }

        // Normalize all whitespace except for the newline character into a
        // standard space.
        if($char !== "\n" && ord($char) < 32)

            return ' ';

        return $char;
    }

    /**
     * This function gets the next "real" character. It is essentially a wrapper
     * around the getChar function that skips comments. This has significant
     * performance benefits as the skipping is done using native functions (ie,
     * c code) rather than in script php.
     *
     *
     * @return string            Next 'real' character to be processed.
     * @throws \RuntimeException
     */
    protected function getReal()
    {
        $startIndex = $this->index;
        $char = $this->getChar();

        // Check to see if we're potentially in a comment
        if ($char !== '/') {
            return $char;
        }

        $this->c = $this->getChar();

        if ($this->c == '/') {
            return $this->processOneLineComments($startIndex);

        } elseif ($this->c == '*') {
            return $this->processMultiLineComments($startIndex);
        }

        return $char;
    }

    /**
     * Removed one line comments, with the exception of some very specific types of
     * conditional comments.
     *
     * @param  int    $startIndex The index point where "getReal" function started
     * @return string
     */
    protected function processOneLineComments($startIndex)
    {
        $thirdCommentString = substr($this->input, $this->index, 1);

        // kill rest of line
        $this->getNext("\n");

        if ($thirdCommentString == '@') {
            $endPoint = ($this->index) - $startIndex;
            unset($this->c);
            $char = "\n" . substr($this->input, $startIndex, $endPoint);
        } else {
            // first one is contents of $this->c
            $this->getChar();
            $char = $this->getChar();
        }

        return $char;
    }

    /**
     * Skips multiline comments where appropriate, and includes them where needed.
     * Conditional comments and "license" style blocks are preserved.
     *
     * @param  int               $startIndex The index point where "getReal" function started
     * @return bool|string       False if there's no character
     * @throws BaseException Unclosed comments will throw an error
     */
    protected function processMultiLineComments($startIndex)
    {
        $this->getChar(); // current C
        $thirdCommentString = $this->getChar();

        // kill everything up to the next */ if it's there
        if ($this->getNext('*/')) {

            $this->getChar(); // get *
            $this->getChar(); // get /
            $char = $this->getChar(); // get next real character

            // Now we reinsert conditional comments and YUI-style licensing comments
            if (($this->options['flaggedComments'] && $thirdCommentString == '!')
                || ($thirdCommentString == '@') ) {

                // If conditional comments or flagged comments are not the first thing in the script
                // we need to echo a and fill it with a space before moving on.
                if ($startIndex > 0) {
                    echo $this->a;
                    $this->a = " ";

                    // If the comment started on a new line we let it stay on the new line
                    if ($this->input[($startIndex - 1)] == "\n") {
                        echo "\n";
                    }
                }

                $endPoint = ($this->index - 1) - $startIndex;
                echo substr($this->input, $startIndex, $endPoint);

                return $char;
            }

        } else {
            $char = false;
        }

        if($char === false)
            throw new BaseException('Unclosed multiline comment at position: ' . ($this->index - 2));

        // if we're here c is part of the comment and therefore tossed
        if(isset($this->c))
            unset($this->c);

        return $char;
    }

    /**
     * Pushes the index ahead to the next instance of the supplied string. If it
     * is found the first character of the string is returned and the index is set
     * to it's position.
     *
     * @param  string       $string
     * @return string|false Returns the first character of the string or false.
     */
    protected function getNext($string)
    {
        // Find the next occurrence of "string" after the current position.
        $pos = strpos($this->input, $string, $this->index);

        // If it's not there return false.
        if($pos === false)

            return false;

        // Adjust position of index to jump ahead to the asked for string
        $this->index = $pos;

        // Return the first character of that string.
        return substr($this->input, $this->index, 1);
    }

    /**
     * When a javascript string is detected this function crawls for the end of
     * it and saves the whole string.
     *
     * @throws BaseException Unclosed strings will throw an error
     */
    protected function saveString()
    {
        $startpos = $this->index;

        // saveString is always called after a gets cleared, so we push b into
        // that spot.
        $this->a = $this->b;

        // If this isn't a string we don't need to do anything.
        if ($this->a != "'" && $this->a != '"') {
            return;
        }

        // String type is the quote used, " or '
        $stringType = $this->a;

        // Echo out that starting quote
        echo $this->a;

        // Loop until the string is done
        while (1) {

            // Grab the very next character and load it into a
            $this->a = $this->getChar();

            switch ($this->a) {

                // If the string opener (single or double quote) is used
                // output it and break out of the while loop-
                // The string is finished!
                case $stringType:
                    break 2;

                // New lines in strings without line delimiters are bad- actual
                // new lines will be represented by the string \n and not the actual
                // character, so those will be treated just fine using the switch
                // block below.
                case "\n":
                    throw new BaseException('Unclosed string at position: ' . $startpos );
                    break;

                // Escaped characters get picked up here. If it's an escaped new line it's not really needed
                case '\\':

                    // a is a slash. We want to keep it, and the next character,
                    // unless it's a new line. New lines as actual strings will be
                    // preserved, but escaped new lines should be reduced.
                    $this->b = $this->getChar();

                    // If b is a new line we discard a and b and restart the loop.
                    if ($this->b == "\n") {
                        break;
                    }

                    // echo out the escaped character and restart the loop.
                    echo $this->a . $this->b;
                    break;


                // Since we're not dealing with any special cases we simply
                // output the character and continue our loop.
                default:
                    echo $this->a;
            }
        }
    }

    /**
     * When a regular expression is detected this function crawls for the end of
     * it and saves the whole regex.
     *
     * @throws BaseException Unclosed regex will throw an error
     */
    protected function saveRegex()
    {
        echo $this->a . $this->b;

        while (($this->a = $this->getChar()) !== false) {
            if($this->a == '/')
                break;

            if ($this->a == '\\') {
                echo $this->a;
                $this->a = $this->getChar();
            }

            if($this->a == "\n")
                throw new BaseException('Unclosed regex pattern at position: ' . $this->index);

            echo $this->a;
        }
        $this->b = $this->getReal();
    }

    /**
     * Checks to see if a character is alphanumeric.
     *
     * @param  string $char Just one character
     * @return bool
     */
    protected static function isAlphaNumeric($char)
    {
        return preg_match('/^[\w\$]$/', $char) === 1 || $char == '/';
    }

    /**
     * Replace patterns in the given string and store the replacement
     *
     * @param  string $js The string to lock
     * @return bool
     */
    protected function lock($js)
    {
        /* lock things like <code>"asd" + ++x;</code> */
        $lock = '"LOCK---' . crc32(time()) . '"';

        $matches = array();
        preg_match('/([+-])(\s+)([+-])/', $js, $matches);
        if (empty($matches)) {
            return $js;
        }

        $this->locks[$lock] = $matches[2];

        $js = preg_replace('/([+-])\s+([+-])/', "$1{$lock}$2", $js);
        /* -- */

        return $js;
    }

    /**
     * Replace "locks" with the original characters
     *
     * @param  string $js The string to unlock
     * @return bool
     */
    protected function unlock($js)
    {
        if (!count($this->locks)) {
            return $js;
        }

        foreach ($this->locks as $lock => $replacement) {
            $js = str_replace($lock, $replacement, $js);
        }

        return $js;
    }















    /**
     * @param string $str
     * @return string
     */
    public static function minifyJs0($str)
    {
        $str = preg_replace( '#(\w+)://#', "$1:#-#SLASH#-#", $str );

        $res          = '';
        $maybe_regex  = true;
        $i            = 0;
        $current_char = '';
        $strlen = strlen( $str );
        while ( $i + 1 < $strlen )
        {
            if ( $maybe_regex && $str[ $i ] == '/' && $str[ $i + 1 ] != '/' && $str[ $i + 1 ] != '*' && (!isset($str[ $i - 1 ]) || $str[ $i - 1 ] != '*') )
            { //regex detected
                if ( strlen( $res ) && $res[ strlen( $res ) - 1 ] === '/' ) $res .= ' ';

                do
                {
                    if ( $str[ $i ] == '\\' )
                    {
                        $res .= $str[ $i++ ];
                    }
                    elseif ( $str[ $i ] == '[' )
                    {
                        do
                        {
                            if ( $str[ $i ] == '\\' )
                            {
                                $res .= $str[ $i++ ];
                            }
                            $res .= $str[ $i++ ];
                        }
                        while ( $i < $strlen && $str[ $i ] != ']' );
                    }
                    $res .= $str[ $i++ ];
                }
                while ( $i < $strlen && $str[ $i ] != '/' );

                $res .= $str[ $i++ ];
                $maybe_regex = false;
                continue;
            }
            elseif ( $str[ $i ] == '"' || $str[ $i ] == "'" )
            { //quoted string detected
                $quote = $str[ $i ];


                do
                {
                    if ( $str[ $i ] == '\\' )
                    {
                        $res .= $str[ $i++ ];
                    }
                    $res .= $str[ $i++ ];
                }
                while ( $i < $strlen && $str[ $i ] != $quote );
                $res .= $str[ $i++ ];

                continue;
            }
            elseif ( $str[ $i ] . $str[ $i + 1 ] == '/*' && @$str[ $i + 2 ] != '@' )
            { //multi-line comment detected
                $i += 3;

                while ( $i < $strlen && $str[ $i - 1 ] . $str[ $i ] != '*/' ) $i++;
                if ( $current_char == "\n" ) $str[ $i ] = "\n";
                else $str[ $i ] = ' ';

                $strlen = strlen( $str );
            }
            elseif ( $str[ $i ] . $str[ $i + 1 ] == '//' )
            { //single-line comment detected
                $i += 2;
                $strlen = strlen( $str );
                while ( $i < $strlen && $str[ $i ] != "\n" && $str[ $i ] != "\r" ) $i++;
            }


            $LF_needed = false;
            if (

                strpos( $str[ $i ], "\r" ) !== false ||
                strpos( $str[ $i ], "\n" ) !== false ||
                strpos( $str[ $i ], "\t" ) !== false ||
                strpos( $str[ $i ], " " ) !== false
                /*preg_match( '/[\n\r\t ]/sU', $str[ $i ] )*/
            )
            {
                $reslen = strlen( $res );


                if ( $reslen &&

                    (
                        strpos( $res[ $reslen - 1 ], "\n" ) !== false ||
                        strpos( $res[ $reslen - 1 ], " " ) !== false
                    )
                    // preg_match( '/[\n ]/s', $res[ strlen( $res ) - 1 ] )
                )
                {
                    if ( $res[ $reslen - 1 ] == "\n" ) $LF_needed = true;
                    $res = substr( $res, 0, -1 );
                }

                while ( $i + 1 < $strlen &&
                    (
                        strpos( $str[ $i + 1 ], "\n" ) !== false ||
                        strpos( $str[ $i + 1 ], "\r" ) !== false ||
                        strpos( $str[ $i + 1 ], "\t" ) !== false ||
                        strpos( $str[ $i + 1 ], " " ) !== false
                    )

                    /* preg_match( '/[\n\r\t ]/s', $str[ $i + 1 ] )*/ )
                {
                    if ( !$LF_needed && ( strpos( $str[ $i ], "\n" ) !== false || strpos( $str[ $i ], "\r" ) !== false ) /* preg_match( '/[\n\r]/', $str[ $i ] ) */ ) $LF_needed = true;
                    $i++;
                }
            }

            if ( strlen( $str ) <= $i + 1 ) break;

            $current_char = $str[ $i ];

            if ( $LF_needed ) $current_char = "\n";
            elseif ( $current_char == "\t" ) $current_char = " ";
            elseif ( $current_char == "\r" ) $current_char = "\n";

            // detect unnecessary white spaces
            if ( $current_char == " " )
            {
                $reslen = strlen( $res );

                if ( $reslen &&
                    (
                        preg_match( '/^[^(){}[\]=+\-*\/%&|!><?:~^,;"\']{2}$/', $res[ $reslen - 1 ] . $str[ $i + 1 ] ) ||
                        ( $res[ $reslen - 1 ] . $str[ $i + 1 ] ) === '--' ||
                        ( $res[ $reslen - 1 ] . $str[ $i + 1 ] ) === '++'


                        //preg_match( '/^(\+\+)|(--)$/', $res[ strlen( $res ) - 1 ] . $str[ $i + 1 ] ) // for example i+ ++j;
                    )
                ) $res .= $current_char;
            }
            elseif ( $current_char == "\n" )
            {
                $reslen = strlen( $res );
                if ( $reslen &&
                    (
                        preg_match( '/^[^({[=+\-*%&|!><?:~^,;\/][^)}\]=+\-*%&|><?:,;\/]$/', $res[ $reslen - 1 ] . $str[ $i + 1 ] ) ||
                        ( $reslen > 1 &&

                            //preg_match( '/^(\+\+)|(--)$/', $res[ strlen( $res ) - 2 ] . $res[ strlen( $res ) - 1 ] )

                            (
                                ( $res[ $reslen - 2 ] . $str[ $i - 1 ] ) === '--' ||
                                ( $res[ $reslen - 2 ] . $str[ $i - 1 ] ) === '++'
                            )

                        ) ||
                        (
                            $strlen > $i + 2 &&

                            (
                                ( $str[ $i + 1 ] . $str[ $i + 2 ] ) === '--' ||
                                ( $str[ $i + 1 ] . $str[ $i + 2 ] ) === '++'
                            )

                            // preg_match( '/^(\+\+)|(--)$/', $str[ $i + 1 ] . $str[ $i + 2 ] )

                        ) ||

                        ( $res[ $reslen - 1 ] . $str[ $i + 1 ] ) === '--' ||
                        ( $res[ $reslen - 1 ] . $str[ $i + 1 ] ) === '++'


                        //preg_match( '/^(\+\+)|(--)$/', $res[ strlen( $res ) - 1 ] . $str[ $i + 1 ] ) // || // for example i+ ++j;
                    )
                ) $res .= $current_char;
            }
            else $res .= $current_char;

            // if the next charachter be a slash, detects if it is a divide operator or start of a regex
            if ( preg_match( '/[({[=+\-*\/%&|!><?:~^,;]/', $current_char ) ) $maybe_regex = true;
            elseif ( $current_char != ' ' && $current_char != "\n" /* preg_match( '/[\n ]/s', $current_char ) */ ) $maybe_regex = false;

            $i++;
        }


        if ( $i < strlen( $str ) && preg_match( '/[^\n\r\t ]/s', $str[ $i ] ) ) $res .= $str[ $i ];




        $res = str_replace( ':#-#SLASH#-#', "://", $res );

        $res = preg_replace( '#(--|\+\+)\s*(else if|if|else|while|do|for|var|function|break|continue)#s', "$1;$2", $res );



        return $res;
    }

}