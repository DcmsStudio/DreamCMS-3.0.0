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
 * @file        Content.php
 *
 */
class Content
{

    /**
     * @var int
     */
    private static $footnotesIndex = 1;

    /**
     * @var array
     */
    public static $fck_regex_search = array(
        '/<!--\s*pagebreak\s*-->/is',
        '/<center style="page-break-after:\s*always;"><\/center>/isU',
        '/<span\s*style="display:\s*none;">([\s\r\n\t]*)(&nbsp;)?([\s\r\n\t]*)<\/span>/isU',
        '/<div\s*style="page-break-after:\s*always;">([\s\r\n\t]*)<\/div>/isU',
        '/<p>([\s\r\n\t]*)(&nbsp;)?<div\s*style="page-break-after:\s*always;">([\s\r\n\t]*)<span\s*style="display:\s*none;">([^>]*)<\/span>([\s\r\n\t]*)<\/div>(&nbsp;)?([\s\r\n\t]*)(&nbsp;)?<\/p>/isU',
        '/([\s\r\n\t]*)(&nbsp;)?<div\s*style="page-break-after:\s*always;">([\s\r\n\t]*)<span\s*style="display:\s*none;">([^>]*)<\/span>([\s\r\n\t]*)<\/div>(&nbsp;)?([\s\r\n\t]*)(&nbsp;)?/isU'
    );

    /**
     * @var array
     */
    public static $fck_regex_replade = array(
        '<pagebreak>',
        '<pagebreak>',
        '',
        '<pagebreak>',
        '<pagebreak>',
        '<pagebreak>'
    );

    /**
     * @var string
     */
    private static $page_index_tag = 'pageindex'; //
    /**
     * @var string
     */

    private static $pagebreak_tag = 'pagebreak'; //
    /**
     * @var string
     */

    private static $parseContent = '';

    /**
     * @var array
     */
    private static $content_siteindexes = array();

    /**
     * @var string
     */
    private static $content_pagelinks = '';

    /**
     * @var array
     */
    private static $content_images = array();

    /**
     * @var bool
     */
    private static $parsePagebreaks = true;

    /**
     * @var bool
     */
    private static $parseFootnotes = false;

    /**
     * @var bool
     */
    private static $parseTubeSites = true;

    /**
     * @var null
     */
    private static $_scan_links = null;

    /**
     * @var null
     */
    private static $createDate = null;

    /**
     * @var bool
     */
    private static $mustUpdateString = false;

    /**
     * @var array
     */
    private static $_hashID = array();

    /**
     * @param $key
     * @return bool
     */
    public static function getOpt( $key )
    {
        switch ( strtolower( $key ) )
        {
            case 'footnotes':
                return self::$parseFootnotes;
            case 'videos':
                return self::$parseTubeSites;
        }

        return false;
    }

    /**
     * @param $string
     * @return mixed
     */
    public static function tinyMCECoreTags( $string )
    {
        if ( empty( $string ) )
        {
            return $string;
        }

        preg_match_all( '/<a([^>]*)>([^><]*)<\/a>/isU', $string, $matches, PREG_SET_ORDER );

        if ( isset($matches[ 0 ]) && count( $matches[ 0 ] ) )
        {
            foreach ( $matches as $idx => $match )
            {
                if ( strpos($match[ 1 ], 'data-contentid') !== false ) {
                    $_string = self::fixtinyMCECoreTags( $match[0], $match[ 1 ], $match[ 2 ] );
                    $string = preg_replace( '#' . preg_quote( $match[0], '#' ) . '#isU', $_string, $string, 1 );
                }
            }

            $matches = null;
        }


        //$string = preg_replace_callback( '/(<span[^>]*mceNonEditable[^>]*>[^><]*<a([^>]*)>([^><]*)<\/a>[^><]*<\/span>)/isU', array( 'self', 'fixtinyMCECoreTags' ), $string );
        return $string;
    }

    /**
     * @param $fulltag
     * @param $attribute
     * @param $title
     * @return string
     */
    private static function fixtinyMCECoreTags( $fulltag, $attribute, $title )
    {

        if ( empty( $attribute ) )
        {
            return $fulltag;
        }

        $contentid = Html::getAttribute( 'data-contentid', $attribute );
        $modul = Html::getAttribute( 'data-modul', $attribute );
        $isapp = Html::getAttribute( 'data-isapp', $attribute );
        $href = Html::getAttribute( 'data-href', $attribute );


        if ( empty( $contentid ) || empty( $modul ) || (substr( $href, 0, 1 ) === '{' && substr( $href, -1 ) === '}') )
        {
            return $fulltag;
        }

        // replace the title with the original document title
        $notitle = Html::getAttribute( 'data-notitle', $attribute );

        if ( !empty( $isapp ) && $isapp !== '' && $isapp != null )
        {
            $cat = '';
            if ( substr( $modul, -3 ) === 'cat' )
            {
                $modul = substr( $modul, 0, -3 );
                $cat = ':cat';
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
            }

            if ( !$_modul )
            {
                return $title;
            }

            if ( !is_null( $notitle ) && $notitle )
            {
                return '<a href="{' . $_modul . 'link:' . $contentid . $cat . '}">' . $title . '</a>';
            }
            else
            {
                return '<a href="{' . $_modul . 'link:' . $contentid . $cat . '}">' . $title . '</a>';
            }
        }
        else
        {

            if ( !is_null( $notitle ) && $notitle )
            {
                return '<a href="{' . $modul . 'link:' . $contentid . '}">' . $title . '</a>';
            }

            return '<a href="{' . $modul . 'link:' . $contentid . '}">' . $title . '</a>';
        }
    }

    /**
     *
     * @param string $data
     * @param integer $contentid
     * @return integer
     */
    public static function getContentHashID( $data, $contentid = 0 )
    {
        $hash = md5( $data );

        if ( isset( self::$_hashID[ $hash ] ) )
        {
            return self::$_hashID[ $hash ];
        }


        $db = Database::getInstance();

        if ( $contentid > 0 )
        {
            $rs = $db->query( 'SELECT hashid FROM %tp%content_hash WHERE hash = ? AND contentid = ?', $hash, $contentid )->fetch();
            if ( !$rs[ 'hashid' ] )
            {
                $db->query( 'INSERT INTO %tp%content_hash (hash, contentid) VALUES(?, ?)', $hash, $contentid );
                $rs[ 'hashid' ] = $db->insert_id();
            }
        }
        else
        {
            $rs = $db->query( 'SELECT hashid FROM %tp%content_hash WHERE hash = ?', $hash )->fetch();
            if ( !$rs[ 'hashid' ] )
            {
                $db->query( 'INSERT INTO %tp%content_hash (hash, contentid) VALUES(?, ?)', $hash, 0 );
                $rs[ 'hashid' ] = $db->insert_id();
            }
        }

        self::$_hashID[ $hash ] = $rs[ 'hashid' ];

        return $rs[ 'hashid' ];
    }

    /**
     *
     * @param string $data
     * @param integer $contentid
     * @return void
     */
    public static function removeContentHash( $data, $contentid = 0 )
    {
        $db = Database::getInstance();
        $hash = md5( $data );

        if ( $contentid > 0 )
        {
            $db->query( 'DELETE FROM %tp%content_hash WHERE hash = ? AND contentid = ?', $hash, $contentid );
        }
        else
        {
            $db->query( 'DELETE FROM %tp%content_hash WHERE hash = ? AND contentid = ?', $hash, 0 );
        }

        unset( self::$_hashID[ $hash ] );
    }

    /**
     *
     * @param string $str
     * @param integer $created the unix timestamp (if null will use the current timestamp)
     * @param string $directoryName default is null (if null will use the CONTROLLER name as directory)
     * @param integer $numImages default is null and will return all images
     * @param string $imageChain default will use the Chain (thumbnail)
     *
     * @return string
     */
    public static function parseImgTags( &$str, $created = null, $directoryName = null, $numImages = null, $imageChain = 'thumbnail' )
    {
        $str = Library::maskContent( $str );
        $str = Library::unmaskContent( $str );


        $images = Html::extractTags( $str, 'img', true, true );

        if ( !is_array( $images ) )
        {
            return $str;
        }

        if ( $created == null )
        {
            $created = time();
        }

        if ( $directoryName === null )
        {
            $directoryName = strtolower( CONTROLLER );
        }


        // clean path
        if ( substr( $directoryName, 0, 1 ) === '/' )
        {
            $directoryName = substr( $directoryName, 1 );
        }
        // clean path
        if ( substr( $directoryName, -1 ) === '/' )
        {
            $directoryName = substr( $directoryName, 0, -1 );
        }

        $i = 0;
        $path = 'img/' . $directoryName . '/' . date( 'Y', $created ) . '/' . date( 'm', $created ) . '/';
        $imgchain = Library::getImageChain( $imageChain );

        foreach ( $images as $idx => $r )
        {

            $attr = $r[ 'attributes' ];
            $fullimage = $r[ 'full_tag' ];
            $originalimage = $fullimage;

            // maximum images in content allowed
            if ( $numImages !== null && $i === $numImages )
            {
                $str = str_replace( $originalimage, '', $str );
                continue;
            }


            if ( !isset( $attr[ 'src' ] ) )
            {
                $str = str_replace( $originalimage, '<!-- Image not exists ' . "\r\n" . $originalimage . "\r\n" . ' -->', $str );
                continue;
            }

            $isextern = false;
            $isextern = Tools::isExternalUrl( $attr[ 'src' ], Settings::get( 'portalurl' ) );


            if ( $isextern )
            {
                $filename = Library::getFilename( $attr[ 'src' ] );
                $ext = Library::getExtension( $filename );

                if ( is_file($path . $filename) && !is_file( PUBLIC_PATH . $path . $filename ) )
                {

                    $imgcontent = Fetch::URL( $attr[ 'src' ], 10 );

                    if ( !Library::validGrapicHeader( $ext, $imgcontent ) )
                    {
                        $str = str_replace( $originalimage, '<!-- Image not exists ' . "\r\n" . $originalimage . "\r\n" . ' -->', $str );
                        continue;
                    }

                    file_put_contents( $path . $filename, $imgcontent );


                    $tovalue = str_replace( PUBLIC_PATH, '', $path . $filename );
                    $attr[ 'src' ] = $tovalue;
                }
            }


            $attr[ 'src' ] = str_replace( Settings::get( 'portalurl' ), '', $attr[ 'src' ] );


            if ( Library::canGraphic( PUBLIC_PATH . $attr[ 'src' ] ) && is_file( PUBLIC_PATH . $attr[ 'src' ] ) )
            {
                $valid = false;

                if ( Library::isValidGraphic( PUBLIC_PATH . $attr[ 'src' ] ) )
                {
                    /*
                      $indata = array(
                      'source' => PUBLIC_PATH . $attr[ 'src' ],
                      'output' => 'png',
                      'chain'  => $imgchain
                      );

                      $img = ImageTools::create( PAGE_CACHE_PATH . 'thumbnails/' . $path );

                      $data           = $img->process( $indata );
                      $data[ 'path' ] = str_replace( array(
                      PUBLIC_PATH,
                      str_replace( ROOT_PATH, '', PUBLIC_PATH ) ), '', $data[ 'path' ] );

                      $valid         = true;
                      $attr[ 'src' ] = Settings::get( 'portalurl' ) . '/' . $data[ 'path' ];

                      $fullimage = Html::setAttribute( 'img', 'width', $data[ 'width' ], $fullimage );
                      $fullimage = Html::setAttribute( 'img', 'height', $data[ 'height' ], $fullimage );
                      $fullimage = Html::setAttribute( 'img', 'src', $attr[ 'src' ], $fullimage );

                      $str = str_replace( $originalimage, $fullimage, $str );

                     */


                    $im = getimagesize( PUBLIC_PATH . $attr[ 'src' ] );
                    $valid = true;


                    $fullimage = Html::setAttribute( 'img', 'width', $im[ 0 ], $fullimage );
                    $fullimage = Html::setAttribute( 'img', 'height', $im[ 1 ], $fullimage );
                    $fullimage = Html::setAttribute( 'img', 'src', $attr[ 'src' ], $fullimage );
                    $str = str_replace( $originalimage, $fullimage, $str );
                }

                if ( !$valid )
                {
                    $str = str_replace( $originalimage, '<!-- Image not exists ' . "\r\n" . $originalimage . "\r\n" . ' -->', $str );
                }
                else
                {
                    $i++;
                }
            }
            else
            {

                //die(PUBLIC_PATH . $attr['src']);
            }
        }

        unset( $images );


        return $str;
    }

    /**
     *
     * @param string $str
     * @return array/boolean
     */
    public static function extractFirstImage( $str )
    {
        $str = Library::maskContent( $str );
        $str = Library::unmaskContent( $str );

        $images = Html::extractTags( $str, 'img', true, true );

        if ( !count( $images ) )
        {
            return false;
        }

        $image = array_shift( $images );
        unset( $images );


        if ( !isset($image[ 'attributes' ][ 'src' ]) )
        {
            return false;
        }

        return $image;
    }

    /**
     *
     * @param string $str
     * @return array/boolean
     */
    public static function extractImages( &$str )
    {
        $str = Library::maskContent( $str );
        $str = Library::unmaskContent( $str );


        $images = Html::extractTags( $str, 'img', true, true );
        if ( !count( $images ) )
        {
            return false;
        }

        $ret = array();
        $url = Settings::get( 'portalurl' );
        foreach ( $images as $img )
        {
            if ( !Tools::isExternalUrl( $img[ 'attributes' ][ 'src' ], $url ) )
            {

                $img[ 'attributes' ][ 'src' ] = str_replace( $url . '/', '', $img[ 'attributes' ][ 'src' ] );


                if ( !isset( $img[ 'attributes' ][ 'width' ] ) || !isset( $img[ 'attributes' ][ 'height' ] ) )
                {
                    if ( Library::canGraphic( PUBLIC_PATH . $img[ 'attributes' ][ 'src' ] ) && is_file( PUBLIC_PATH . $img[ 'attributes' ][ 'src' ] ) )
                    {
                        $info = getimagesize( PUBLIC_PATH . $img[ 'attributes' ][ 'src' ] );

                        $img[ 'attributes' ][ 'width' ] = $info[ 0 ];
                        $img[ 'attributes' ][ 'height' ] = $info[ 1 ];
                    }
                }

                $img[ 'attributes' ][ 'src' ] = $url . '/' . $img[ 'attributes' ][ 'src' ];

                $ret[] = $img[ 'attributes' ];
            }
            else
            {
                $ret[] = $img[ 'attributes' ];
            }
        }

        unset( $images );

        return $ret;
    }

    /**
     * Extract all links in the Content
     *
     * @param string $str
     * @param bool   $autoSetFootnote
     * @return array
     */
    public static function extractFootnotes( &$str, $autoSetFootnote = false )
    {
        if ( !self::$parseFootnotes )
        {
            return null;
        }

        //    die('ENABLED');


        $url = Settings::get( 'portalurl' );
        $links = Html::extractTags( $str, 'a', null, true );
        if ( !count( $links ) )
        {
            return false;
        }

        $data = null;
        $data[ 'index' ] = self::$footnotesIndex;
        $note = 1;

        foreach ( $links as $r )
        {
            $attr = $r[ 'attributes' ];
            if ( $attr[ 'href' ] && substr( $attr[ 'href' ], 0, 1 ) !== '#' && trim( strip_tags( $r[ 'contents' ] ) ) != '' )
            {
                $dat[ 'title' ] = strip_tags( $r[ 'contents' ] );
                $dat[ 'url' ] = $attr[ 'href' ];


                if ( Tools::isExternalUrl( $attr[ 'href' ], $url ) )
                {
                    $dat[ 'extern' ] = true;
                }


                if ( $autoSetFootnote && $r[ 'full_tag' ] )
                {
                    $dat[ 'footnote' ] = 'footnote-' . $note;
                    $dat[ 'footnoteindex' ] = $note;
                    $str = str_replace( $r[ 'full_tag' ], $dat[ 'title' ] . ' <sup><a href="' . Api::currentLocation() . '#footnote-' . $note . '">' . $note . '</a></sup>', $str );
                }
                else
                {
                    $dat[ 'footnote' ] = 'footnote-' . $note;
                    $dat[ 'footnoteindex' ] = $note;

                    $str = str_replace( $r[ 'full_tag' ], $r[ 'full_tag' ] . ' <sup><a href="' . Api::currentLocation() . '#footnote-' . $note . '">' . $note . '</a></sup>', $str );
                }
                $note++;
                $data[ 'links' ][] = $dat;
            }
        }


        unset( $links );

        self::$footnotesIndex++;


        return $data;
    }

    /**
     * Enable pagebreaks for the content
     */
    public static function enablePagebreaks()
    {
        self::$parsePagebreaks = true;
    }

    /**
     * Disable pagebreaks for the content
     */
    public static function disablePagebreaks()
    {
        self::$parsePagebreaks = false;
    }

    /**
     * Enable creating content footnotes from all links in the Content
     */
    public static function enableFootnotes()
    {
        self::$parseFootnotes = true;
    }

    /**
     * Disable creating content footnotes from all links in the Content
     */
    public static function disableFootnotes()
    {
        self::$parseFootnotes = false;
    }

    /**
     * Will enable Tube Site Videos (youtube and others) in Content.
     */
    public static function enableTubeVideos()
    {
        self::$parseTubeSites = true;
    }

    /**
     * Will disable Tube Site Videos (youtube and others) in Content.
     */
    public static function disableTubeVideos()
    {
        self::$parseTubeSites = false;
    }

    /**
     * @param $content
     */
    public static function cleanContent( &$content )
    {
        preg_match_all( '!<script[^>]*>.*</script>!isU', $content, $match );
        $content = preg_replace( '!<script[^>]*>.*</script>!isU', '', $content );



        $content = $content . implode( '', $match[ 0 ] );
    }

    /**
     *
     * @param string $content
     * @param bool|string $doParsePagebreaks default is false
     * @param boolean $doParseSiteIndex default is false
     * @param boolean $doParseVideos default is false
     * @return string
     */
    public static function parseContent( $content, $doParsePagebreaks = false, $doParseSiteIndex = false, $doParseVideos = false )
    {
        self::$_scan_links = null; // reset


        if ( !$doParsePagebreaks && !$doParseSiteIndex && !$doParseVideos )
        {
            # self::cleanContent($content);
            return $content;
        }


        $content = preg_replace( self::$fck_regex_search, self::$fck_regex_replade, $content );
        $content = preg_replace( '/<' . self::$page_index_tag . '([^>]+?)+>/iSU', '<' . self::$page_index_tag . '>', $content );


        $page = HTTP::input( 'page' );
        $page = ((int)$page  > 0 ? (int)$page  : 1);

        $title_header = '';
        $anchor_num = 0;
        $anchor = 1;
        $seiten = 1;

        if ( $doParsePagebreaks && self::$parsePagebreaks === true )
        {
            $ptext = preg_split( '/(<p>)?\s*\r*\n*\t*<' . self::$pagebreak_tag . '>\s*\r*\n*\t*(<\/p>)?/i', $content );
            $seiten = count( $ptext );
        }
        // count the number of pages
        $n = $seiten;
        $pages = $n;


        $indexed_sites = array();

        // Seiten Seitenumbrüche
        if ( $doParsePagebreaks && self::$parsePagebreaks === true && $pages > 1 && !HTTP::input( 'getpdf' ) )
        {
            // Für die Seitenweise Ausgabe
            $paging = new Paging();
            self::$content_pagelinks = $paging->setPaging( $doParsePagebreaks, $page, $pages, 'content_paging' );
        }

        // Seiten Inhalsverzeichnis
        if ( $doParseSiteIndex )
        {

            // ======================================
            // Inhaltsverzeichnis
            foreach ( $ptext as $i => $pt )
            {
                $old_index = $i;
                $current_site = $i + 1;

                preg_match_all( '!<' . self::$page_index_tag . '>.+</' . self::$page_index_tag . '>!isU', $pt, $match );
                $_pt_blocks = $match[ 0 ];
                unset( $match );

                $pt = preg_replace( '!<' . self::$page_index_tag . '>.+</' . self::$page_index_tag . '>!isU', '@@@TRIM:PINDEX@@@', $pt );

                if ( is_array( $_pt_blocks ) )
                {
                    foreach ( $_pt_blocks as $curr_block )
                    {
                        $add = false;

                        if ( $page != $anchor )
                        {
                            $curr_block = preg_replace( '/<(\/)?' . self::$page_index_tag . '[^>]+>/isU', '<\\1h1>', $curr_block );
                        }
                        else
                        {
                            $title_header = preg_replace( '/<' . self::$page_index_tag . '>([^<]*)<\/' . self::$page_index_tag . '>/isU', '\\1', $curr_block );
                            $anchor_num = $anchor;
                            $curr_block = preg_replace( '/<' . self::$page_index_tag . '>([^<]*)<\/' . self::$page_index_tag . '>/isU', '', $curr_block, 1 );
                        }

                        if ( $curr_block )
                        {
                            $anchorurl = '<a name="anchor' . $anchor . '"></a>';

                            $indexed_sites[] = array(
                                'title'  => trim( (string) strip_tags( $curr_block ) ),
                                'anchor' => $anchor,
                                'page'   => $current_site );

                            $add = true;
                        }
                        else
                        {
                            $anchorurl = '<a name="anchor' . $anchor . '"></a>';

                            $indexed_sites[] = array(
                                'title'  => trim( (string) strip_tags( $title_header ) ),
                                'anchor' => $anchor,
                                'page'   => $current_site );

                            $add = true;
                        }

                        $pt = preg_replace( "!@@@TRIM:PINDEX@@@!S", '' . $curr_block, $pt, 1 );

                        if ( $add )
                        {
                            $anchor++;
                        }
                    }
                    unset( $_pt_blocks );
                }


                $pt = preg_replace( '/(<p>)\s*\r*\n*\t*<' . self::$pagebreak_tag . '>\s*\r*\n*\t*(<\/p>)/i', '', $pt );
                $ptext[ $old_index ] = $pt;

                unset( $pt );
            }

            self::$content_siteindexes = $indexed_sites;
        }


        /**
         * Prepare all Links in the Content
         *
         */
        if ( $doParsePagebreaks && self::$parsePagebreaks === true )
        {
            $_p = ($page > 0 ? ($page - 1) : 0);
            $content = $ptext[ $_p ];
        }

        // Text der Seite
        if ( $doParsePagebreaks && self::$parsePagebreaks === true )
        {
            if ( HTTP::input( 'getpdf' ) /* isset($cp->input['getpdf']) && !empty($cp->input['getpdf']) && $access->uca('canprintpdfpage') */ )
            {

                $content = "";
                foreach ( $ptext as $i => $pt )
                {
                    $content .= $ptext[ $i ];
                }
            }
            else
            {
                if ( $pages > 1 )
                {
                    $content = "";
                    if ( $anchor_num )
                    {
                        $content .= '<a id="anchor' . $anchor_num . '" name="anchor' . $anchor_num . '"></a>';
                    }
                    if ( $title_header )
                    {
                        $content .= '<h3>' . $title_header . '</h3>';
                    }

                    $content .= $ptext[ $_p ];
                }
            }
        } // if

        unset( $ptext );

        // cleanup
        $content = preg_replace( '/\r*\n*\s*\t*<(\/)?' . self::$page_index_tag . '>\r*\n*\s*\t*/iU', '', $content );
        $content = preg_replace( '/\r*\n*\s*\t*<(\/)?' . self::$pagebreak_tag . '>\r*\n*\s*\t*/iU', '', $content );

        if ( self::$parseTubeSites || $doParseVideos )
        {
            $content = self::parseVideos( $content );
        }

        # self::cleanContent($content);
        // remove index tags
        self::$parseContent = preg_replace( '/\r*\n*\s*\t*<(\/)?' . self::$page_index_tag . '>\r*\n*\s*\t*/i', '', $content );

        return $content;
    }

    /**
     * Return the parsed Content
     *
     * @return string
     */
    public static function getParseContent()
    {
        return self::$parseContent;
    }

    /**
     * return a array with the content site index
     * @return array
     */
    public static function getSiteIndexes()
    {
        return self::$content_siteindexes;
    }

    /**
     * return string with content paging
     *
     * @return string
     */
    public static function getContentPageing()
    {
        return self::$content_pagelinks;
    }

    /**
     *
     * @param string $source
     * @return string
     */
    public static function escapeCoreTags( &$source )
    {
        $source = preg_replace( '/\{([a-z]+)+:([^\}]*)\}/i', '&#123;$1:$2&#125;', $source );
        $source = preg_replace( '/\{([a-z]+)+\}/i', '&#123;$1&#125;', $source );

        // find core tags for unescape
        preg_match_all( '#<!--\s*unescape-coretag\s*-->(.+?)<!--\s*/\s*unescape-coretag\s*-->#', $source, $match );
        if ( isset( $match[ 1 ] ) )
        {
            foreach ( $match[ 1 ] as $idx => $str )
            {
                $unesc = self::unescapeCoreTags( $str );
                $source = preg_replace( '/' . preg_quote( $match[ 0 ][ $idx ], '/' ) . '/U', ' ' . $unesc . ' ', $source, 1 );
            }
            $match = null;
        }

        return $source;
    }

    /**
     *
     * @param string $source
     * @return string
     */
    public static function unescapeCoreTags( &$source )
    {
        $source = preg_replace( '/&#123;([a-z]+)+:([^\}]*)&#125;/i', '{$1:$2}', $source );
        return preg_replace( '/&#123;([a-z]+)+&#125;/i', '{$1}', $source );
    }

    /**
     * Replace CMS images to CKEditor images
     * @param string $content
     * @return string
     */
    public static function replaceImagesToCms( &$content )
    {
        return preg_replace( "/<img[^>]+content_image_([\d]+)[^>]+\/?>/is", '{content_image_\\1}', $content );
    }

    /**
     * enable or disable Tube parsing
     * @param boolean $param
     */
    public static function setTubeVideosParser( $param = true )
    {
        self::$parseTubeSites = $param;
    }

    /**
     * this function scan the giving content by <a> tags and add automaticly attributes
     * target=_blank
     * class=extlink
     *
     * @param string $txt
     * @return string
     */
    public static function parseVideos( &$txt )
    {
        $tubeParser = null;

        if ( self::$parseTubeSites )
        {
            $tubeParser = new TubeParser();
            $tubeParser->setObjectParam( 'wmode', 'transparent' );
            $tubeParser->setParam( 'autoplay', 'false' );
            $tubeParser->setHeight( 375 );
            $tubeParser->setWidth( 460 );
        }
        else
        {
            return $txt;
        }


        preg_match_all( '!<iframe[^>]*>!isU', $txt, $match );
        $_blocks = $match[ 0 ];
        if ( !is_array( $_blocks ) )
        {
            return $txt;
        }




        if ( is_array( $_blocks ) )
        {
            $txt = preg_replace( '!<iframe[^>]*>!isU', '@@@TRIM:IFRAME_BLOCKS@@@', $txt );
            $cachedVideos = array();
            foreach ( $_blocks as $curr_block )
            {
                if ( ($href = Html::hasAttribute( 'src', $curr_block )) !== false )
                {
                    $found_href = true;
                }

                if ( $found_href )
                {
                    $isextern = Tools::isExternalUrl( $href, Settings::get( 'portalurl' ) );
                }

                if ( $isextern )
                {
                    if ( $tubeParser )
                    {
                        $hasVideo = $tubeParser->parseUrl( $href );

                        if ( $hasVideo )
                        {
                            $video = $tubeParser->getEmbedCode();

                            if ( $video )
                            {
                                $cachedVideos[ md5( $video ) ] = $video;
                                /**
                                 * @todo cache content videos
                                 */
                                //$cachedVideos[] = $video;
                                $txt = preg_replace( '/@@@TRIM:IFRAME_BLOCKS@@@([^>]*)<\/iframe>/misU', '<span class="tub-video">' . $video . '</span>', $txt, 1 );
                                continue;
                            }
                            else {
                                $txt = preg_replace( '!@@@TRIM:IFRAME_BLOCKS@@@!U', $curr_block, $txt, 1 );
                            }
                        }
                        else {
                            $txt = preg_replace( '!@@@TRIM:IFRAME_BLOCKS@@@!U', $curr_block, $txt, 1 );
                        }
                    }
                    else {
                        $txt = preg_replace( '!@@@TRIM:IFRAME_BLOCKS@@@!U', $curr_block, $txt, 1 );
                    }
                }
                else
                {
                    $txt = preg_replace( '!@@@TRIM:IFRAME_BLOCKS@@@!U', $curr_block, $txt, 1 );
                }
            }
        }






        $match = array();
        preg_match_all( '!<a [^>]+>!isU', $txt, $match );

        $_blocks = $match[ 0 ];
        if ( !is_array( $_blocks ) )
        {
            return $txt;
        }

        $txt = preg_replace( '!<a [^>]+>!isU', '@@@TRIM:A_BLOCKS@@@', $txt );


        if ( is_array( $_blocks ) )
        {
            $cachedVideos = array();
            foreach ( $_blocks as $curr_block )
            {
                $found_href = false;

                if ( ($href = Html::hasAttribute( 'href', $curr_block )) !== false )
                {
                    $found_href = true;
                }

                $isextern = false;


                if ( $found_href )
                {
                    $isextern = Tools::isExternalUrl( $href, Settings::get( 'portalurl' ) );
                }

                if ( $isextern )
                {

                    if ( $tubeParser )
                    {
                        $hasVideo = $tubeParser->parseUrl( $href );

                        if ( $hasVideo )
                        {
                            $video = $tubeParser->getEmbedCode();


                            if ( $video && !isset( $cachedVideos[ md5( $video ) ] ) )
                            {
                                $cachedVideos[ md5( $video ) ] = true;
                                /**
                                 * @todo cache content videos
                                 */

                                //$cachedVideos[] = $video;
                                $txt = preg_replace( '/@@@TRIM:A_BLOCKS@@@([^>]*)<\/a>/msU', '<span class="tub-video">' . $video . '</span>', $txt, 1 );
                                continue;
                            }
                            else
                            {
                                $newtxt = Html::setAttribute( 'a', 'target', '_blank', $curr_block );
                                $newtxt = Html::setAttribute( 'a', 'class', 'extlink', $newtxt );
                                $txt = preg_replace( '!@@@TRIM:A_BLOCKS@@@!U', $newtxt, $txt, 1 );
                            }
                        }
                        else {
                            $newtxt = Html::setAttribute( 'a', 'target', '_blank', $curr_block );
                            $newtxt = Html::setAttribute( 'a', 'class', 'extlink', $newtxt );
                            $txt = preg_replace( '!@@@TRIM:A_BLOCKS@@@!U', $newtxt, $txt, 1 );
                        }
                    }
                    else {
                        $newtxt = Html::setAttribute( 'a', 'target', '_blank', $curr_block );
                        $newtxt = Html::setAttribute( 'a', 'class', 'extlink', $newtxt );
                        $txt = preg_replace( '!@@@TRIM:A_BLOCKS@@@!U', $newtxt, $txt, 1 );
                        unset( $newtxt );
                    }
                }
                else
                {
                    $txt = preg_replace( '!@@@TRIM:A_BLOCKS@@@!U', $curr_block, $txt, 1 );
                }
            }

            unset( $_blocks );
        }

        unset( $tubeParser );

        return $txt;
    }

}
