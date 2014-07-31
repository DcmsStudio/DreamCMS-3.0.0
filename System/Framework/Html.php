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
 * @file        Html.php
 *
 */
class Html
{

    /**
     * @var
     */
    private static $masked;

    /**
     * @var
     */
    private static $lastAttr;

    /**
     * @var
     */
    private static $lastStr;

    /**
     *
     * @var array
     */
    private static $singleTags = array(
        'area',
        'base',
        'basefont',
        'br',
        'hr',
        'input',
        'img',
        'link',
        'meta',
        'col',
        'param' );

    /**
     * @param string $source
     * @param bool $xhtmlOff
     * @param null $docType
     * @return mixed|string|tidy
     */
    public static function pretty( $source, $xhtmlOff = false, $docType = null )
    {
        if ( class_exists( 'tidy', false ) && stripos( $docType, 'html_5' ) === false )
        {
            // Specify configuration
            $config = array(
                'new-blocklevel-tags'         => 'article,header,footer,section,nav',
                'new-inline-tags'             => 'video,audio,canvas,ruby,rt,rp',
                'doctype'                     => 'omit',
                'sort-attributes'             => 'alpha',
                'indent'                      => true,
                'markup'                      => true,
                'preserve-entities'           => true,
                'drop-proprietary-attributes' => false,
                'output-xhtml'                => true,
                'enclose-text'                => true,
                'tab-size'                    => 4,
                'indent-spaces'               => 4,
                'tidy-mark'                   => false,
                'wrap'                        => 800,
                'write-back'                  => true );


            if ( $xhtmlOff )
            {
                $config[ 'output-xhtml' ] = false;
            }

            // Tidy
            $tidy = new tidy;
            $tidy->parseString( $source, $config );
            $tidy->cleanRepair();

            return $tidy;
        }
        else
        {

            $level = 4;
            $indent = 0; // current indentation level  
            $pretty = array();
            $tab = chr( 32 );


            $script = false;
            preg_match_all( '#(<(script|textarea)[^>]*>\n*\s*\t*(.*)\n*\s*\t*</\2>)#misU', $source, $scriptcode );
            if ( is_array( $scriptcode[ 3 ] ) )
            {
                $source = preg_replace( '#<(script|textarea)([^>]*)>\n*\s*\t*(.*)\n*\s*\t*</\1>#misU', '<$1$2>@@CODE@@</$1>', $source );
                $script = true;
            }


            $pre = false;
            preg_match_all( '#<pre[^>]*>\n*\s*(.*)\n*\s*</pre>#iU', $source, $precode );
            if ( is_array( $precode[ 1 ] ) )
            {
                $source = preg_replace( '#(<pre[^>]*>)\n*\s*(.*)\n*\s*</pre>#iU', '$1@@PRECODE@@</pre>', $source );
                $pre = true;
            }


            // get an array containing each XML element  
            // $source = preg_replace( '/>\s*\t*\n*/', ">\n", $source );
            $source = preg_replace( '/\s*\n*\t*\s*(<\/?[^>]*>)\s*\n*\t*\s*/s', "\n$1\n", $source );
            $source = preg_replace( '/\n*\s*<\/a>/is', "</a>", $source );
            $source = preg_replace( '/<a ([^>]*)>\n*\s*/is', "<a $1>", $source );
            $xml = explode( "\n", $source );

            // shift off opening XML tag if present  
            if ( count( $xml ) && preg_match( '/^<\?\s*xml/', $xml[ 0 ] ) )
            {
                $pretty[] = array_shift( $xml );
            }

            $isLink = false;

            foreach ( $xml as $el )
            {
                if ( preg_match( '/^<\w+[^>]*>$/U', $el ) )
                {
                    // opening tag, increase indent  
                    $pretty[] = str_repeat( $tab, $indent ) . $el;

                    if ( substr( $el, -2 ) === '/>' )
                    {
                        // $indent += (substr($el, 0, 2) === '<!' ? 0 : $level);
                    }
                    else
                    {
                        $indent += (substr( $el, 0, 2 ) === '<!' ? 0 : $level);
                    }
                }
                else
                {
                    if ( trim( $el ) != '' )
                    {
                        if ( preg_match( '/^<\/.+>$/', $el ) )
                        {
                            $indent -= $level; // closing tag, decrease indent
                        }

                        if ( $indent < 0 )
                        {
                            $indent = 0;
                        }

                        $pretty[] = str_repeat( $tab, $indent ) . $el;
                    }
                }
            }

            $xml = implode( "\n", $pretty );
            #     $xml = preg_replace('#<([\w]+)([^>\/]*)>\n*\s*\t*</#i', '<$1$2></', $xml);
            #    $xml = preg_replace('#<(a\s|pre)([^<>]*)>\n\s*\t*#i', '<$1$2>', $xml);
            #    $xml = preg_replace('#\s*\n\s*</a>#iSU', '$1</a>', $xml);
            // trim empty tag contents
            $xml = preg_replace( '#<([\w]+)([^>\/]*)>\n*\s*\t*</#i', '<$1$2></', $xml );


            if ( $pre )
            {
                foreach ( $precode[ 1 ] as $c )
                {
                    $xml = preg_replace( '#\n*\s*\n*\s*@@PRECODE@@\n*\s*\n*\s*#', trim( $c ), $xml, 1 );
                }
            }
            if ( $script )
            {

                foreach ( $scriptcode[ 3 ] as $idx => $c )
                {
                    if ( $scriptcode[ 2 ][ $idx ] === 'textarea' )
                    {
                        $xml = preg_replace( '/\n*\s*\t*@@CODE@@\n*\s*\t*/', trim( $c ), $xml, 1 );
                    }
                    else
                    {
                        $xml = preg_replace( '/\s*@@CODE@@\s*/', trim( $c ), $xml, 1 );
                    }
                }
            }


            return $xml; //($html_output) ? htmlentities( $source ) : $xml;
        }

        //return $source;
    }

    /**
     * @param $node
     */
    protected function getChildren( $node )
    {
        for ( $x = 0; $x < $node->childNodes->length; ++$x )
        {
            echo $node->childNodes->item( $x )->textContent . '<br>';
        }
    }

    /**
     * extract_tags()
     * Extract specific HTML tags and their attributes from a string.
     *
     * You can either specify one tag, an array of tag names, or a regular expression that matches the tag name(s).
     * If multiple tags are specified you must also set the $selfclosing parameter and it must be the same for
     * all specified tags (so you can't extract both normal and self-closing tags in one go).
     *
     * The function returns a numerically indexed array of extracted tags. Each entry is an associative array
     * with these keys :
     *    tag_name    - the name of the extracted tag, e.g. "a" or "img".
     *    offset        - the numberic offset of the first character of the tag within the HTML source.
     *    contents    - the inner HTML of the tag. This is always empty for self-closing tags.
     *    attributes    - a name -> value array of the tag's attributes, or an empty array if the tag has none.
     *    full_tag    - the entire matched tag, e.g. '<a href="http://example.com">example.com</a>'. This key
     *                  will only be present if you set $return_the_entire_tag to true.
     *
     * @param string $html The HTML code to search for tags.
     * @param string|array $tag The tag(s) to extract.
     * @param bool $selfclosing    Whether the tag is self-closing or not. Setting it to null will force the script to try and make an educated guess.
     * @param bool $return_the_full_tag Return the entire matched tag in 'full_tag' key of the results array.
     * @param string $charset The character set of the HTML code. Defaults to ISO-8859-1.
     *
     * @return array An array of extracted tags, or an empty array if no matching tags were found.
     */
    public static function extractTags( $html, $tag, $selfclosing = null, $return_the_full_tag = false, $charset = 'ISO-8859-1' )
    {


        /*
          // Create a new DOM document
          $dom = new DOMDocument;
          libxml_use_internal_errors(true);

          // Parse the HTML. The @ is used to suppress any parsing errors
          // that will be thrown if the $html string isn't valid XHTML.
          $dom->loadHTML( $html );

          // Get all links. You could also use any other tag name here,
          // like 'img' or 'table', to extract other tags.
          $links = $dom->getElementsByTagName('a');

          // Iterate over the extracted links and display their URLs
          foreach ( $links as $i => $link )
          {
          $el = $links->item($i);

          // Extract and show the "href" attribute.

          // $attributes = $el->attributes->



          echo $el->getAttribute( 'href' ), '<br>';
          for( $x=0;$x<$el->childNodes->length;++$x)
          {
          //echo $el->. '<br>';
          }
          }
          return;
          exit;

         */


        if ( is_array( $tag ) )
        {
            $tag = implode( '|', $tag );
        }

        // If the user didn't specify if $tag is a self-closing tag we try to auto-detect it
        // by checking against a list of known self-closing tags.
        $selfclosing_tags = array(
            'area',
            'base',
            'basefont',
            'br',
            'hr',
            'input',
            'img',
            'link',
            'meta',
            'col',
            'param',
            'embed' );
        if ( is_null( $selfclosing ) )
        {
            $selfclosing = in_array( $tag, $selfclosing_tags );
        }

        $tag_pattern = '';

        // The regexp is different for normal and self-closing tags because I can't figure out
        // how to make a sufficiently robust unified one.
        if ( $selfclosing )
        {
            $tag_pattern = '@<(?P<tag>' . $tag . ')\s*(?P<attributes>\s*[^>]*)?\s*/?>@xsi';
        }
        else
        {
            $tag_pattern = '@<(?P<tag>' . $tag . ')\s*(?P<attributes>\s*[^>]*)?\s*>(?P<contents>.*?)</(?P=tag)>@xsi';
        }

        $attribute_pattern = '@(?P<name>[a-z0-9\-]+)\s*=\s*((?P<quote>[\"\'])(?P<value_quoted>.*?)(?P=quote)|(?P<value_unquoted>[^\s"\']+?)(?:\s+|$))@xsi';

        // Find all tags
        $matches = null;
        if ( !preg_match_all( $tag_pattern, $html, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE ) )
        {
            // Return an empty array if we didn't find anything
            return null;
        }


        $tags = array();
        foreach ( $matches as $match )
        {

            // Parse tag attributes, if any
            $_attributes = array();
            if ( !empty( $match[ 'attributes' ][ 0 ] ) )
            {
                $attribute_data = array();
                if ( preg_match_all( $attribute_pattern, $match[ 'attributes' ][ 0 ], $attribute_data, PREG_SET_ORDER ) )
                {


                    // Turn the attribute data into a name->value array
                    foreach ( $attribute_data as $attr )
                    {
                        $value = '';

                        if ( !empty( $attr[ 'value_quoted' ] ) )
                        {
                            $value = $attr[ 'value_quoted' ];
                        }
                        elseif ( !empty( $attr[ 'value_unquoted' ] ) )
                        {
                            $value = $attr[ 'value_unquoted' ];
                        }
                        else
                        {
                            $value = '';
                        }

                        // Passing the value through html_entity_decode is handy when you want
                        // to extract link URLs or something like that. You might want to remove
                        // or modify this call if it doesn't fit your situation.
                        $value = html_entity_decode( $value, ENT_QUOTES, $charset );

                        $_attributes[ $attr[ 'name' ] ] = $value;
                    }
                }
            }

            $tag = array(
                'tag_name'   => $match[ 'tag' ][ 0 ],
                'offset'     => $match[ 0 ][ 1 ],
                'contents'   => isset($match[ 'contents' ][ 0 ] ) && $match[ 'contents' ][ 0 ] ? $match[ 'contents' ][ 0 ] : '', // empty for self-closing tags
                'attributes' => $_attributes
            );

            if ( $return_the_full_tag )
            {
                $tag[ 'full_tag' ] = $match[ 0 ][ 0 ];
            }

            $tags[] = $tag;
        }


        return $tags;
    }

    /**
     *
     * @param type         $name
     * @param string|\type $str
     * @param bool|\type   $dbg
     * @return type
     */
    public static function getAttribute( $name, $str = '', $dbg = false )
    {
        $name = strtolower( $name );
        $hash = md5( $str );

        if ( isset( self::$lastAttr[ $hash ] ) )
        {

            if ( isset( self::$lastAttr[ $hash ][ $name ] ) )
            {
                return self::$lastAttr[ $hash ][ $name ];
            }

            return null;
        }


        if ( !trim( $str ) )
        {
            return null;
        }


        self::$lastStr = $str;


        $arr = array();
        $foo = array();
        preg_match_all( '/\s*([^= ]*)=\s*"([^"]*)"/is', $str, $foo );
        $all = count( $foo[ 1 ] );

        if ( $all > 0 )
        {
            for ( $i = 0; $i < $all; ++$i )
            {
                $fixed_value = trim( (string) $foo[ 2 ][ $i ] );
                $attname = strtolower( trim( (string) $foo[ 1 ][ $i ] ) );

                if ( !isset( $arr[ $attname ] ) )
                {
                    $arr[ $attname ] = $fixed_value;
                }
            }
        }

        if ( isset( $arr[ $name ] ) )
        {
            return $arr[ $name ];
        }


        $foo = array();
        preg_match_all( '/\s*([^= ]*)=\s*\'([^\']*)\'/is', $str, $foo );
        $all = count( $foo[ 1 ] );
        if ( $all > 0 )
        {
            for ( $i = 0; $i < $all; ++$i ) //foreach ( $foo[0] as $i => $v )
            {
                $fixed_value = trim( (string) $foo[ 2 ][ $i ] );
                $attname = strtolower( trim( (string) $foo[ 1 ][ $i ] ) );
                if ( !isset( $arr[ $attname ] ) )
                {
                    $arr[ $attname ] = $fixed_value;
                }
            }
        }

        self::$lastAttr[ $hash ] = $arr;


        if ( isset( $arr[ $name ] ) )
        {
            return $arr[ $name ];
        }

        return null;
    }

    /**
     *
     * @param type         $name
     * @param string|\type $str
     * @param bool|\type   $dbg
     * @return type
     */
    public static function hasAttribute( $name, $str = '', $dbg = false )
    {
        if ( !trim( $str ) )
        {
            return false;
        }

        if ( ($value = self::getAttribute( $name, $str, $dbg )) !== null )
        {
            return $value;
        }

        return false;
    }

    /**
     *
     * @param type         $tagName
     * @param type         $name
     * @param string|\type $tovalue
     * @param string|\type $str
     * @param bool|\type   $dbg
     * @return type
     */
    public static function setAttribute( $tagName, $name, $tovalue = '', $str = '', $dbg = false )
    {
        if ( !trim( $str ) )
        {
            return false;
        }

        $lower = strtolower( $name );
        $value = self::getAttribute( $name, $str, $dbg );


        /*
         * CSS Fix
         */
        if ( $lower === 'height' || $lower === 'width' )
        {
            $ovalue = self::getAttribute( 'style', $str, $dbg );
            if ( $ovalue !== null )
            {
                $nvalue = preg_replace( '/' . $lower . '\s*:\s*\d{1,}\s*(px|pt|em|%)(;)?/i', "{$lower}:{$tovalue}px$2", $ovalue );
                $str = preg_replace( '/(\sstyle\s*=\s*([\'"])' . preg_quote( $ovalue, '/' ) . '\\2)/is', ' style="' . $nvalue . '"', $str );
            }
        }


        if ( $value !== null )
        {
            if ( $dbg )
            {
                #  preg_match('/(\s' . $name . '\s*=\s*([\'"])' . preg_quote($value, '/') . '\\2)/is', $str, $m);
                #  echo $value;
                #  print_r($m);
                #  exit;
            }

            // replace existing value with new attribute
            $str = preg_replace( '/(\s' . $name . '\s*=\s*([\'"])' . preg_quote( $value, '/' ) . '\\2)/is', ' ' . $name . '="' . $tovalue . '"', $str );
        }
        else
        {
            if ( $dbg )
            {
                #   die($str);
                #   exit;
            }

            // Attribut not found also add this
            $str = str_ireplace( '<' . $tagName . ' ', '<' . $tagName . ' ' . $name . '="' . $tovalue . '" ', $str );
        }

        return $str;
    }

    /**
     *
     * @param string|\type $attr
     * @param string|\type $value
     * @return type
     */
    public static function addAttribute( $attr = '', $value = '' )
    {
        if ( !$attr )
        {
            return '';
        }
        return strtolower( $attr ) . '="' . htmlspecialchars( $value ) . '"';
    }

    /**
     *
     * @param array $data
     * @return string
     */
    public static function createTag( $data = array() )
    {
        if ( !isset( $data[ 'tagname' ] ) )
        {
            return '';
        }

        $newtag = '<' . strtolower( $data[ 'tagname' ] );

        if ( is_array( $data[ 'attributes' ] ) )
        {
            foreach ( $data[ 'attributes' ] as $key => $value )
            {
                $newtag .= ' ' . self::addAttribute( $key, $value );
            }
        }

        if ( in_array( strtolower( $data[ 'tagname' ] ), self::$singleTags ) )
        {
            $newtag .= '/>';
        }
        else
        {
            $newtag .= '>';
        }

        if ( isset( $data[ 'cdata' ] ) )
        {
            if ( in_array( strtolower( $data[ 'tagname' ] ), self::$singleTags ) )
            {
                Error::raise( sprintf( trans( 'Sorry der Tag `%s` ist kein HTML Block tag!' ), $data[ 'tagname' ] ) );
            }

            $newtag .= $data[ 'cdata' ];
            $newtag .= '</' . $data[ 'tagname' ] . '>';
        }

        return $newtag;
    }

    /**
     *
     * @param type $tag
     * @param type $xml
     * @return type
     */
    private static function cerrarTag( $tag, $xml )
    {
        $indice = 0;
        while ( $indice < strlen( $xml ) )
        {
            $pos = strpos( $xml, "<$tag ", $indice );
            if ( $pos )
            {
                $posCierre = strpos( $xml, ">", $pos );
                if ( $xml[ $posCierre - 1 ] === "/" )
                {
                    $xml = substr_replace( $xml, "></$tag>", $posCierre - 1, 2 );
                }
                $indice = $posCierre;
            }
            else
                break;
        }
        return $xml;
    }

    /**
     * Repair unclosed HTML Tags
     *
     * @param string $html
     * @return string
     */
    static function closeUnclosedTags( $html )
    {
        if ( class_exists( 'tidy', false ) )
        {
            return $html;
        }

        $result = array();


        #put all opened tags into an array
        preg_match_all( "#<([a-z0-9]+)( .*)?(?!/)>#iU", $html, $result );
        $openedtags = $result[ 1 ];

        #put all closed tags into an array
        preg_match_all( "#</([a-z0-9]+)>#iU", $html, $result );
        $closedtags = $result[ 1 ];
        $len_opened = count( $openedtags );

        # all tags are closed
        if ( count( $closedtags ) === $len_opened )
        {
            return $html;
        }

        $openedtags = array_reverse( $openedtags );

        # close tags
        for ( $i = 0; $i < $len_opened; $i++ )
        {
            if ( !in_array( $openedtags[ $i ], $closedtags ) && !in_array( strtolower( $openedtags[ $i ] ), self::$singleTags ) )
            {
                $html .= "</" . strtolower( $openedtags[ $i ] ) . ">";
            }
            else
            {
                unset( $closedtags[ array_search( $openedtags[ $i ], $closedtags ) ] );
            }
        }
        return $html;
    }

    /**
     *
     * @param string $str
     * @param string $attribut
     * @return string
     */
    public static function fixAttributeAmpsans( $str, $attribut )
    {
        $ovalue = self::getAttribute( $attribut, $str );

        if ( $ovalue !== null )
        {
            $nvalue = preg_replace( "/&(?![a-zA-Z0-9#]+;{1})/", "&amp;", $ovalue );
            $str = str_replace( $ovalue, $nvalue, $str );
        }
        return $str;
    }

    /**
     *
     * @param string $html
     * @return string
     */
    static function tidyHTML( $html )
    {
        $html = Strings::fixLatin( Strings::UnicodeToUtf8( $html ) );
        $html = preg_replace( "#(<(option|a)[^>]*>)\n*\s*\t*#i", '$1', $html );
        $html = preg_replace( "#\n*\s*\t*(</(option|a)[^>]*>)#i", '$1', $html );

        return $html;
        /*

        preg_match( "!<html[^>]+>.*</html>!isSU", $html, $match );

        die( $html );

        $html = self::maskProtectedTags( $match[ 0 ] );


        $html = preg_replace( '#(\s{2,})#', " ", $html );
        $html = preg_replace( '#(\n{2,})#', "\n", $html );

        // load our document into a DOM object
        $dom = new DOMDocument();

        // we want nice output
        $dom->preserveWhiteSpace = false;
        $dom->loadHTML( $html );
        $dom->formatOutput = true;

        $html = $dom->saveHTML();

        return self::unmaskProtectedTags( $html );

        */
    }

    /**
     * Function to seperate multiple tags one line
     * @param string $fixthistext
     * @return string
     */
    static function fix_newlines_for_clean_html( $fixthistext )
    {
        $fixedtext_array = array();
        $fixthistext_array = explode( "\n", $fixthistext );
        foreach ( $fixthistext_array as $unfixedtextkey => $unfixedtextvalue )
        {
            //Makes sure empty lines are ignores
            if ( !preg_match( "/^(\s)*$/", $unfixedtextvalue ) )
            {
                $fixedtextvalue = preg_replace( "#>(\s|\t)*</#U", ">\n</", $unfixedtextvalue );
                $fixedtext_array[ $unfixedtextkey ] = $fixedtextvalue;
            }
        }


        return implode( "\n", $fixedtext_array );
    }

    /**
     *
     * @param string $html
     * @return string
     */
    private static function maskProtectedTags( $html )
    {
        self::$masked = array();
        $match = array();
        preg_match_all( "!<(script|pre|textarea)[^>]+>.*</\\1>!isSU", $html, $match );
        self::$masked = $match[ 0 ];

        return preg_replace( "!<(script|pre|textarea)[^>]+>.*</\\1>!isSU", '@@@MASKED@@@', $html );
    }

    /**
     *
     * @param string $html
     * @return string
     */
    private static function unmaskProtectedTags( $html )
    {
        foreach ( self::$masked as $curr_block )
        {
            $html = preg_replace( "!@@@MASKED@@@!SU", rtrim( $curr_block ), $html, 1 );
        }
        return $html;
    }

    /**
     *
     */
    public static function compressHtml( $html )
    {
        $html = self::maskProtectedTags( $html );
        $html = preg_replace( '/\r*\n*\s*\t*(<\/?\w[^>]+>)\r*\n*\s*\t*/sxS', '$1', $html );
        $html = preg_replace( '/\s{2,}/sS', ' ', $html );

        $html = self::stripHtmlComments( $html );

        return self::unmaskProtectedTags( $html );
    }

    /**
     *
     * @param string $uncleanhtml
     * @return string
     */
    public static function clean_html_code( &$uncleanhtml )
    {
        //Set wanted indentation
        $indent = "    ";

        $uncleanhtml = str_replace( "\r\n", "\n", $uncleanhtml );

        $uncleanhtml = self::maskProtectedTags( $uncleanhtml );


        //Uses previous function to seperate tags
        $uncleanhtml = preg_replace( "#\s*\n*\t*>#", ">", $uncleanhtml );
        $uncleanhtml = preg_replace( "#\s*\n*\t*</#", "\n</", $uncleanhtml );
        $uncleanhtml = preg_replace( "#\s*\n*\t*>#", ">\n", $uncleanhtml );
        $fixed_uncleanhtml = self::fix_newlines_for_clean_html( $uncleanhtml );


        $cleanhtml_array = array();
        $uncleanhtml_array = explode( "\n", $fixed_uncleanhtml );

        //Sets no indentation
        $indentlevel = 0;


        foreach ( $uncleanhtml_array as $uncleanhtml_key => $currentuncleanhtml )
        {
            //Removes all indentation
            $currentuncleanhtml = preg_replace( "/\t+/", "", $currentuncleanhtml );
            $currentuncleanhtml = preg_replace( "/^\s+/", "", $currentuncleanhtml );

            $replaceindent = "";

            //Sets the indentation from current indentlevel
            for ( $o = 0; $o < $indentlevel; $o++ )
            {
                $replaceindent .= $indent;
            }

            //If self-closing tag, simply apply indent
            if ( preg_match( "/<(.+)\/>/", $currentuncleanhtml ) )
            {
                $cleanhtml_array[ $uncleanhtml_key ] = $replaceindent . $currentuncleanhtml;
            } //If doctype declaration, simply apply indent
            else if ( preg_match( "/<!([^>]*)>/", $currentuncleanhtml ) )
            {
                $cleanhtml_array[ $uncleanhtml_key ] = $replaceindent . $currentuncleanhtml;
            } //If opening AND closing tag on same line, simply apply indent
            else if ( preg_match( "/<[^\/]([^>]*)>/", $currentuncleanhtml ) &&
                    preg_match( "/<\/([^>]*)>/", $currentuncleanhtml )
            )
            {

                $cleanhtml_array[ $uncleanhtml_key ] = $replaceindent . $currentuncleanhtml;
            } //If closing HTML tag or closing JavaScript clams, decrease indentation and then apply the new level
            else if ( preg_match( "/<\/([^>]*)>/", $currentuncleanhtml ) || preg_match( "/^(\s|\t)*\}{1}(\s|\t)*$/", $currentuncleanhtml ) )
            {
                $indentlevel--;
                $replaceindent = "";
                for ( $o = 0; $o < $indentlevel; $o++ )
                {
                    $replaceindent .= $indent;
                }

                // fix for textarea whitespace and in my opinion nicer looking script tags
                if ( $currentuncleanhtml === '</textarea>' || $currentuncleanhtml === '</script>' || $currentuncleanhtml === '</pre>' )
                {
                    $cleanhtml_array[ $uncleanhtml_key ] = $cleanhtml_array[ ($uncleanhtml_key - 1) ] . $currentuncleanhtml;
                    unset( $cleanhtml_array[ ($uncleanhtml_key - 1) ] );
                }
                else
                {
                    $cleanhtml_array[ $uncleanhtml_key ] = $replaceindent . $currentuncleanhtml;
                }
            } //If opening HTML tag AND not a stand-alone tag, or opening JavaScript clams, increase indentation and then apply new level
            else if ( (preg_match( "/<[^\/](.*)>/", $currentuncleanhtml ) &&
                    !preg_match( "/<(" . implode( '|', self::$singleTags ) . ")([^>]*)>/", $currentuncleanhtml )) ||
                    preg_match( "/^(\s|\t)*\{{1}(\s|\t)*$/", $currentuncleanhtml )
            )
            {
                $cleanhtml_array[ $uncleanhtml_key ] = $replaceindent . $currentuncleanhtml;

                $indentlevel++;
                $replaceindent = "";
                for ( $o = 0; $o < $indentlevel; $o++ )
                {
                    $replaceindent .= $indent;
                }
            }
            else //Else, only apply indentation
            {
                $cleanhtml_array[ $uncleanhtml_key ] = $replaceindent . $currentuncleanhtml;
            }
        }
        //Return single string seperated by newline
        return self::unmaskProtectedTags( implode( "\n", $cleanhtml_array ) );
    }

    /**
     * To strip html comments.
     * But will leave conditionals comments such as <!-- [if IE 7]><![endif]-->
     *
     * @param string $content
     * @return string
     */
    public static function stripHtmlComments( $content )
    {
        $stripHtmlCommentsRegex = "/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/Uis";
        return preg_replace( $stripHtmlCommentsRegex, "", $content );
    }

}

?>