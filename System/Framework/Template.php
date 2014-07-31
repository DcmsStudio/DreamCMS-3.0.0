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
 * @file         Template.php
 */
class Template extends Template_Abstract
{

    /**
     * @var
     */
    protected static $objInstance;

    /**
     * @var bool
     */
    protected $isFrontend = true;

    /**
     * @var bool
     */
    protected $renderPlugin = false;

    /**
     * @var int
     */
    private $_skinID = 0;

    /**
     * @var string
     */
    private $_templateDir = 'default';

    public $isProvider = false;

    /**
     * Return the current object instance (Singleton)
     *
     * @return Template
     */
    public static function getInstance()
    {

        if ( !is_object( self::$objInstance ) )
        {
            self::$objInstance = new Template();

            if ( self::$objInstance->getApplication()->getMode() !== Application::FRONTEND_MODE )
            {
                self::$objInstance->load( 'Site' );
                self::$objInstance->load( 'Document' );
                self::$objInstance->isFrontend = false;
            }
            else
            {

                self::$objInstance->load( 'Document' );
                self::$objInstance->load( 'Provider' );
                self::$objInstance->load( 'Site' );
                self::$objInstance->load( 'Breadcrumb' );
            }
        }

        return self::$objInstance;
    }

    /**
     *
     */
    public function __construct()
    {

        if ( self::$objInstance instanceof Template )
        {
            return self::$objInstance;
        }


        parent::__construct();

        if ( $this->getApplication()->getMode() !== Application::FRONTEND_MODE )
        {
            $this->isFrontend = false;
        }


    }

    public function __clone()
    {

    }

    /**
     *
     * @return string the compiled and prepared Template
     */
    public function getCompiled()
    {

        return $this->_compiledTemplate;
    }

    public function __destruct()
    {
        parent::__destruct();
        $this->freeMem();
        $this->_parser = null;
    }

    /**
     *
     */
    public function freeMem()
    {

        $this->_data = array();

        //$this->_parser->freeMem();
        //$this->_parser = null;
        self::$skinData = null;
        self::$layout   = null;

        // unset($this->_compiledTemplate);
    }


    /**
     * Only used for Seemode Editing.
     *
     * @param string $lockAction
     */
    public static function setLockAction($lockAction)
    {

        self::$_contentUnlockActionName = $lockAction;

    }

    /**
     * @param $className
     */
    public function addBodyClass($className)
    {

        if ( !trim( $className ) )
        {
            return;
        }

        if ( !is_array( self::$_extraBodyClasses ) )
        {
            self::$_extraBodyClasses = array();
        }

        self::$_extraBodyClasses[ ] = trim( $className );
        self::$_extraBodyClasses    = array_unique( self::$_extraBodyClasses );
    }

    /**
     * example javascript: addScript( JS_URL . 'jquery/jquery.url' )
     * example css: addScript( 'asset/js/html/js/jquery_dynDateTime/css/calendar-system.css', true )
     *
     *
     * @param string $src
     * @param boolean $isCss default is false
     * @param null $rel
     * @return Template
     */
    public function addScript($src, $isCss = false, $rel = null)
    {

        if ( !$isCss )
        {
            self::$_loadExtraScripts[ 'js' ][ ] = $src;
            self::$_loadExtraScripts[ 'js' ]    = array_unique( self::$_loadExtraScripts[ 'js' ] );
        }
        else
        {
            self::$_loadExtraScripts[ 'css' ][ ] = $src;
            self::$_loadExtraScripts[ 'css' ]    = array_unique( self::$_loadExtraScripts[ 'css' ] );
        }

        return $this;
    }


    /**
     * Add class attribute to the HTML Body Tag
     *
     * @param string $code
     * @return string
     */
    private function addBodyClasses($code)
    {

        if ( is_array( self::$_extraBodyClasses ) )
        {
            preg_match( '#<body([^>]*)>#isU', $code, $_matches );
            if ( $_matches[ 1 ] )
            {
                if ( preg_match( '#class=#is', $_matches[ 1 ] ) )
                {
                    $org = $_matches[ 1 ];
                    $_m  = preg_replace( '#class\s*=\s*([\'"])([^\1]*)\1#isU', 'class=$1$2' . ' ' . implode( ' ', self::$_extraBodyClasses ) . '$1', $org );

                    $code = preg_replace( '#<body' . preg_quote( $org, '#' ) . '>#isU', '<body' . $_m . '>', $code, 1 );
                }
            }
        }

        return $code;
    }

    /**
     * @param $code
     */
    private static function cleanContent(&$code)
    {

        // make valid html code if form element is in a p tag
        $code = preg_replace( '!<p[^>]*>\s*\r*\n*\t*<form([^>]*)>!isU', '<form$1>', $code );
        $code = preg_replace( '!(</form>(.+?)</p>)!is', '</form>$2', $code );

        // remove tinymce attribute rel
        #   $code = preg_replace( '!<div([^>]*)(rel=("|\')([^\3]*)\3)([^>]*)>!isU', '<div$1$5>', $code );

        $code = Strings::cleanString( $code );
    }

    /**
     *
     * @param string $code
     *
     * @return mixed|string
     */
    public function repair(&$code)
    {
        if ( !trim( $code ) )
        {
            return $code;
        }


        Debug::store( 'Process repair' );

        $code = preg_replace( '/&;([a-z0-9_\-\s\t]+)/', '&amp;$1', $code );

        preg_match_all( '#<script[^>]*>(.*)</script>#isSU', $code, $matches );
        if ( is_array( $matches[ 0 ] ) )
        {
            $code = preg_replace( '#<script([^>]*)>(.*)</script>#isSU', '@@@SCRIPT@@', $code );
        }


        // fix textareas & pre tags
        // @todo move to template engine
        $code = preg_replace( '/<(textarea|pre)([^>]*)>\s*\n*\r*\t*/is', '<$1$2>', $code );
        $code = preg_replace( '/\s*\n*\r*\t*<\/(textarea|pre)>/is', '</$1>', $code );

        // Ampsan Fix
        $code = preg_replace( '/&(?!:amp;|#[Xx][0-9A-fa-f]+;|#[0-9]+;|[a-zA-Z0-9]+;)/', '&amp;', $code );

        /**
         * Javascript patch
         */
        if ( is_array( $matches ) && count( $matches[ 0 ] ) )
        {
            $url = Settings::get( 'portalurl' );
            $len = strlen( $url );

            foreach ( $matches[ 0 ] as $idx => $c )
            {
                preg_match( '/<script([^>]*)src=(["\'])([^\2]*)\2([^>]*)>/isU', $c, $m );

                if ( isset( $m[ 3 ] ) )
                {
                    $src = $m[ 3 ];

                    if ( substr( $src, 0, 5 ) === 'asset/' || ( substr( $src, 0, $len ) !== $url && substr( $src, 0, 4 ) !== 'http' ) )
                    {
                        $c = str_ireplace( ' src=' . $m[ 2 ] . $src . $m[ 2 ], ' src=' . $m[ 2 ] . $url . '/' . $src . $m[ 2 ], $c );
                    }

                    $code = preg_replace( '#@@@SCRIPT@@#', $c, $code, 1 );
                }
                else
                {
                    /*
                     * @todo inline script compressor
                    if (isset($matches[ 1 ][$idx])) {

                    }
                    */


                    $code = preg_replace( '#@@@SCRIPT@@#', $c, $code, 1 );
                }
            }

            $matches = null;
        }


        // repatch CDATA
   #     $code = preg_replace( '#\/\*\r*\n*\s*\t*\/\*#sU', '/*', $code );
   #     $code = preg_replace( '#\*/\r*\n*\s*\t*\*/#sU', '*/', $code );

        if ( isset( $this->_data[ 'layout' ] ) && isset( $this->_data[ 'layout' ][ 'doctype' ] ) )
        {
            if ( stripos( $this->_data[ 'layout' ][ 'doctype' ], 'html_5' ) !== false || stripos( $this->_data[ 'layout' ][ 'doctype' ], 'html5' ) !== false )
            {
                $code = preg_replace( '#\s(wrap|align)\s*=\s*(["\'])([^\2]*)\2#isSU', '', $code ); // @todo move to template engine

                $code = preg_replace( '#<script([^>]*)language\s*=\s*([\'"])javascript\2([^>]*)>#isSU', '<script$1$3>', $code );
                $code = preg_replace( '#<script([^>]*)charset\s*=\s*([\'"])([^\2]*)\2([^>]*)>#isSU', '<script$1$4>', $code );


                // @todo move to template engine
                $objects = Html::extractTags( $code, 'object', null, true );

                if ( is_array( $objects ) )
                {
                    foreach ( $objects as $r )
                    {
                        $org = $r[ 'full_tag' ];
                        if ( !isset( $r[ 'attributes' ][ 'type' ] ) )
                        {
                            $r[ 'full_tag' ] = str_replace( '<object ', '<object ' . 'type="application/x-shockwave-flash" ', $r[ 'full_tag' ] );
                        }

                        $orgC            = $r[ 'contents' ];
                        $r[ 'contents' ] = preg_replace( '#\s*/>#s', '/>', $r[ 'contents' ] );
                        $r[ 'full_tag' ] = str_replace( $orgC, $r[ 'contents' ], $r[ 'full_tag' ] );
                        $code            = str_replace( $org, $r[ 'full_tag' ], $code );
                    }

                    Debug::store( 'Process repair', 'Repair HTML 5 object tags' );

                    $objects = null;
                }
                Debug::store( 'Process repair', 'Repair HTML 5 Done' );
            }
        }

        // @todo move to template engine
        /*
         *
                $images = Html::extractTags( $code, 'img', true, true );
                if ( is_array( $images ) )
                {
                    foreach ( $images as $r )
                    {

                        if ( isset( $r[ 'attributes' ][ 'src' ] ) && $r[ 'attributes' ][ 'src' ] == '@@LAZYLOAD-PLACEHOLDER@@' )
                        {
                            $org             = $r[ 'full_tag' ];
                            $r[ 'full_tag' ] = preg_replace( '#src=("|\')@@LAZYLOAD-PLACEHOLDER@@\1#U', 'src=$1data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsQAAA7EAZUrDhsAAAANSURBVBhXYzh8+PB/AAffA0nNPuCLAAAAAElFTkSuQmCC$1', $r[ 'full_tag' ] );
                            $code            = str_replace( $org, $r[ 'full_tag' ], $code );
                        }

                        if ( !isset( $r[ 'attributes' ][ 'alt' ] ) )
                        {
                            $org = $r[ 'full_tag' ];
                            if ( !empty( $r[ 'attributes' ][ 'title' ] ) )
                            {
                                $r[ 'full_tag' ] = str_replace( '<img ', '<img ' . 'alt="' . $r[ 'attributes' ][ 'title' ] . '" ', $r[ 'full_tag' ] );
                            }
                            else
                            {
                                $r[ 'full_tag' ] = str_replace( '<img ', '<img ' . 'alt="-" ', $r[ 'full_tag' ] );
                            }

                            $code = str_replace( $org, $r[ 'full_tag' ], $code );
                        }
                    }
                    Debug::store( 'Process repair', 'Repair img tags' );

                    $images = null;
                }
        */
        if ( !$this->isFrontend )
        {
            $this->load( 'Env' );
        }


        // @todo move to template engine
        $forms = Html::extractTags( $code, 'form', false, true );
        if ( is_array( $forms ) )
        {
            $x = 0;
            foreach ( $forms as $r )
            {
                $x++;
                $str    = $r[ 'contents' ];
                $inputs = Html::extractTags( $str, 'input', true, true );

                if ( is_array( $inputs ) )
                {
                    $org           = $r[ 'full_tag' ];
                    $foundcsrf     = false;
                    $foundFormSend = false;
                    $foundDraft    = false;
                    foreach ( $inputs as $rs )
                    {
                        if ( isset( $rs[ 'attributes' ][ 'type' ] ) && $rs[ 'attributes' ][ 'type' ] === 'password' )
                        {
                            if ( !isset( $rs[ 'attributes' ][ 'autocomplete' ] ) )
                            {
                                $fix = str_ireplace( '<input ', '<input autocomplete="off" ', $rs[ 'full_tag' ] );
                                $org = str_replace( $rs[ 'full_tag' ], $fix, $org );
                            }
                        }

                        if ( isset( $rs[ 'attributes' ][ 'name' ] ) )
                        {
                            if ( $rs[ 'attributes' ][ 'name' ] === 'token' )
                            {
                                $foundcsrf = true;
                            }
                            if ( $rs[ 'attributes' ][ 'name' ] === '_fsend' )
                            {
                                $foundFormSend = true;
                            }

                            if ( !$this->isFrontend && $rs[ 'attributes' ][ 'name' ] === 'draftlocation' )
                            {

                                $foundDraft = true;
                            }

                        }
                    }

                    // add draft if not exists
                    if ( !$this->isFrontend && !$foundDraft )
                    {
                        $location = $this->Env->location();
                        $location = str_replace( '&amp;', '&', $location );

                        $location = preg_replace( '#^/public/#is', '', $location );
                        $location = preg_replace( '#&_=([\d]*)#is', '', $location );
                        $location = preg_replace( '#&ajax=(1|true|on)#is', '', $location );

                        $org = str_ireplace( '</form>', '<input type="hidden" name="draftlocation" value="' .
                            base64_encode(
                                serialize( array($location, CONTROLLER, ACTION) )
                            ) .
                            '"/></form>', $org );
                    }


                    if ( !$foundcsrf )
                    {
                        $token = Form::getCSRFToken( 'token' );
                        $org   = str_ireplace( '</form>', '<input type="hidden" name="token" value="' . $token . '"/></form>', $org );
                    }


                    if ( !$foundFormSend )
                    {
                        $org = str_ireplace( '</form>', '<input type="hidden" name="_fsend" value="1"/></form>', $org );
                    }


                    // fix form if not has attribute method
                    // set default method to POST
                    if ( !isset( $r[ 'attributes' ][ 'method' ] ) )
                    {
                        $org = str_ireplace( '<form ', '<form method="post" ', $org );
                    }

                    $code = str_replace( $r[ 'full_tag' ], $org, $code );
                }
            }
        }

        // @todo move to template engine
        $objects = Html::extractTags( $code, 'p', null, true );
        if ( is_array( $objects ) )
        {
            foreach ( $objects as $r )
            {
                $org = $r[ 'full_tag' ];

                $after = '';

                if ( stripos( $org, '<form' ) !== false )
                {
                    $str   = $r[ 'contents' ];
                    $forms = Html::extractTags( $str, 'form', null, true );

                    if ( is_array( $forms ) )
                    {

                        foreach ( $forms as $rs )
                        {
                            $after .= $rs[ 'full_tag' ];
                            $org = str_replace( $rs[ 'full_tag' ], '', $org );
                        }

                        unset( $forms );
                    }
                }

                if ( $after )
                {
                    $code = str_replace( $r[ 'full_tag' ], $org . $after, $code );
                }
            }
        }

        // add classes to body tag
        if ( is_array( self::$_extraBodyClasses ) && sizeof( self::$_extraBodyClasses ) )
        {
            $body = Html::extractTags( $code, 'body', null, true );
            if ( isset( $body[ 0 ] ) )
            {
                $cls                     = ( isset( $body[ 0 ][ 'attributes' ][ 'class' ] ) ? $body[ 0 ][ 'attributes' ][ 'class' ] . ' ' : '' ) . implode( ' ', self::$_extraBodyClasses );
                $org                     = $body[ 0 ][ 'full_tag' ];
                $body[ 0 ][ 'full_tag' ] = preg_replace( '#<body([^>]*)class\s*=\s*(["\'])([^\2]*)\2([^>]*)>#isU', '<body$1class="' . $cls . '$4">', $body[ 0 ][ 'full_tag' ] );
                $code                    = str_replace( $org, $body[ 0 ][ 'full_tag' ], $code );
                Debug::store( 'Process repair', 'Add body extra classes' );
            }

            $body = null;
        }


        if ( !Strings::isUTF8( $code ) )
        {
            // now convert to utf-8
            $code = Strings::mbConvertTo( $code, 'UTF-8' );
            //$code = iconv("UTF-8","UTF-8//IGNORE",$code);
            Debug::store( 'Process repair', 'Repair UTF-8' );
        }

        /*
                if ( !IS_AJAX )
                {
                    Debug::store( 'Process repair', 'Repair stripNonUtf8' );

                    //$code = preg_replace_callback( "/(&#[0-9]+;)/U", array('Template', 'fixNonAjaxEntitys') , $code );
                    $code = preg_replace_callback( "/(&#[0-9]+;)/U", create_function( '$m', 'return mb_convert_encoding( $m[ 1 ], "UTF-8", "HTML-ENTITIES" );' ), $code );
                    $code = Strings::stripNonUtf8( $code );
                }
        */
        // protect php
        $code = str_replace( '?>', '?&gt;', str_replace( '<?', '&lt;?', $code ) );
        /*
        if ( !Strings::isUTF8( $code ) )
        {
            // now convert to utf-8
            $code = Strings::mbConvertTo( $code, 'UTF-8' );
            Debug::store( 'Process repair', 'Repair UTF-8' );
        }


        $code = preg_replace_callback( "/(&#[0-9]+;)/U", function ($m)
        {
            return mb_convert_encoding( $m[ 1 ], "UTF-8", "HTML-ENTITIES" );
        }, $code );
*/
        // protect php
        $code = str_replace( '?>', '?&gt;', str_replace( '<?', '&lt;?', $code ) );

        // patch for space char
        $code = str_replace( array("\u0080", "\u00a0", "\u0080", "\x{c2}", "\x{0080}", "\x{00}", "\x{80}", "\x{00A0}", '&#160;'), " ", $code );

        $code = str_replace( 'â€¦', '…', $code ); # elipsis
        $code = str_replace( 'â€“', '–', $code ); # long hyphen
        $code = str_replace( 'â€™', '’', $code ); # curly apostrophe
        $code = str_replace( 'â€œ', '“', $code ); # curly open quote
        $code = preg_replace( '/â€[[:cntrl:]]/u', '”', $code ); # curly close quote
        $code = str_replace( 'Ã, ¨', '', $code );


        $code = str_replace( '-l-t-', '<', $code );
        $code = str_replace( '-g-t-', '>', $code );


        $code = preg_replace( '#/\*\s*\n*\s*/\*#', '/*', $code );
        $code = preg_replace( '#s*\*/\s*\n*\s*\*/#', '*/', $code );
        $code = str_replace( '&nbsp;&nbsp;', ' &nbsp;', $code ); # curly close quote
        $code = preg_replace( '#([\W]*)&nbsp;#', '$1 ', $code ); # curly close quote
        Debug::store( 'Process repair', 'Done' );

        return $code;
    }


    /**
     * @param $m
     * @return string
     */
    public static function fixDataOpts($m)
    {
        if ( strpos( $m[ 0 ], 'googlemaps' ) === false )
        {
            return '<div' . $m[ 1 ] . $m[ 3 ] . '>';
        }
        else
        {
            return $m[ 0 ];
        }
    }

    /**
     * @param $m
     * @return string
     */
    public static function fixNonAjaxEntitys($m)
    {
        return mb_convert_encoding( $m[ 1 ], "UTF-8", "HTML-ENTITIES" );
    }

    /**
     * hmmm is slower :(
     * @param string $code
     * @param bool $isCache
     */
    public function repair0(&$code, $isCache = false)
    {

        if ( !trim( $code ) )
        {
            return;
        }


        Library::disableErrorHandling();

        Debug::store( 'Process repair' );

        #$code = Strings::unhtmlSpecialchars($code);
        if ( !IS_AJAX )
        {
            // convert to real chars
            $code = Strings::rehtmlconverter( $code, 'UTF-8' );
        }

        $dom  = new SimpleHTMLDom();
        $html = $dom->getStrHtml( $code, true, true, false );


        $scriptCache = null;
        $url         = Settings::get( 'portalurl' );
        $len         = strlen( $url );

        if ( $html )
        {

            $scripts = $html->find( 'script' );
            foreach ( $scripts as $script )
            {

                if ( isset( $this->_data[ 'layout' ][ 'doctype' ] ) && stripos( $this->_data[ 'layout' ][ 'doctype' ], 'html_5' ) !== false )
                {
                    if ( $script->hasAttribute( 'language' ) )
                    {
                        $script->removeAttribute( 'language' );
                    }

                    if ( $script->hasAttribute( 'charset' ) )
                    {
                        $script->removeAttribute( 'charset' );
                    }
                }

                if ( $script->src )
                {

                    $script->src = str_replace( '&', '&amp;', str_replace( '&amp;', '&', $script->src ) );

                    $src = $script->src;
                    if ( substr( $src, 0, 5 ) === 'asset/' || ( substr( $src, 0, $len ) != $url && substr( $src, 0, 4 ) !== 'http' ) )
                    {
                        $src = $url . '/' . $src;
                        $script->setAttribute( 'src', $src );
                    }
                }

                $scriptCache[ ]    = $script->outertext;
                $script->outertext = '@@@SCRIPT@@'; // set new
            }


            // add classes to body tag
            if ( is_array( self::$_extraBodyClasses ) )
            {
                $body = $html->find( 'body', 0 );
                if ( $body )
                {
                    $body->class = ( $body->class ? $body->class . ' ' : '' ) . implode( ' ', self::$_extraBodyClasses );
                }
            }

            foreach ( $html->find( 'img' ) as $img )
            {
                if ( !$img->alt )
                {
                    if ( $img->title )
                    {
                        if ( !Strings::isUTF8( $img->title ) )
                        {
                            $img->title = Strings::utf8ToEntities( Strings::mbConvertTo( $img->title, 'UTF-8' ) );
                        }

                        $img->alt = Strings::fixLatin( $img->title );
                    }
                    else
                    {
                        $img->alt = 'Image';
                    }
                }
                else
                {
                    if ( !Strings::isUTF8( $img->alt ) )
                    {
                        $img->alt = Strings::utf8ToEntities( Strings::mbConvertTo( Strings::fixLatin( $img->alt ), 'UTF-8' ) );
                    }
                }

                if ( $img->hasAttribute( 'src' ) )
                {
                    if ( $img->src === '@@LAZYLOAD-PLACEHOLDER@@' )
                    {
                        $img->src = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsQAAA7EAZUrDhsAAAANSURBVBhXYzh8+PB/AAffA0nNPuCLAAAAAElFTkSuQmCC';
                    }
                    else
                    {
                        $img->src = str_replace( '&', '&amp;', str_replace( '&amp;', '&', $img->src ) );
                    }
                }


                if ( $img->hasAttribute( 'data-options' ) && !$img->hasAttribute( 'data-fancybox' ) && $this->isFrontend )
                {
                    $img->removeAttribute( 'data-options' );
                    $img->removeAttribute( 'data-basefile' );
                }
            }


            $url = preg_replace( '#http(s)://(www\.)#', '', Settings::get( 'portalurl' ) );


            foreach ( $html->find( 'a' ) as $a )
            {
                if ( $a->hasAttribute( 'href' ) )
                {
                    $a->href = str_replace( '&', '&amp;', str_replace( '&amp;', '&', $a->href ) );

                    $isextern = Tools::isExternalUrl( $a->href, Settings::get( 'portalurl' ) );

                    if ( $isextern && !$a->hasAttribute( 'rel' ) )
                    {
                        $a->rel = 'nofollow';
                    }
                    if ( $isextern )
                    {
                        if ( !$a->class )
                        {
                            $a->class = 'external';
                        }
                        else
                        {
                            $a->class .= ' external';
                        }
                    }
                }
            }

            foreach ( $html->find( 'link' ) as $a )
            {
                if ( $a->hasAttribute( 'src' ) )
                {
                    $a->src = str_replace( '&', '&amp;', str_replace( '&amp;', '&', $a->src ) );
                }
            }

            /*
                        foreach ( $html->find('a') as $a )
                        {
                            if ( !$a->title && trim($a->plaintext) )
                            {
                                $text = trim($a->plaintext);
                                if ( !Strings::isUTF8($text) )
                                {
                                    $text = Strings::mbConvertTo($text, 'UTF-8');
                                }
                                $a->title = $text;
                            }
                        }
            */

            // fix pre tags
            foreach ( $html->find( 'pre' ) as $pre )
            {
                foreach ( $pre->find( 'br' ) as $br )
                {
                    $br->outertext = "\n";
                }
            }


            foreach ( $html->find( 'form' ) as $form )
            {
                if ( !$form->method )
                {
                    $form->method = 'POST';
                }


                if ( !$this->isFrontend )
                {
                    $draftlocation = $form->find( 'input[name=draftlocation]' );

                    if ( !$draftlocation )
                    {
                        $location = $this->Env->location();
                        $location = str_replace( '&amp;', '&', $location );
                        $location = preg_replace( '#^/public/#is', '', $location );
                        $location = preg_replace( '#&_=([\d]*)#is', '', $location );
                        $location = preg_replace( '#&ajax=(1|true|on)#is', '', $location );

                        $org = '<input type="hidden" name="draftlocation" value="' .
                            base64_encode(
                                serialize( array($location, CONTROLLER, ACTION) )
                            ) .
                            '"/>';

                        $form->innertext = $form->innertext . $org;
                    }
                }

                $token = $form->find( 'input[name=token]' );

                if ( !$token )
                {
                    $token           = '<input type="hidden" name="token" value="' . Form::getCSRFToken( 'token' ) . '"/>';
                    $form->innertext = $form->innertext . $token;
                }
            }

            foreach ( $html->find( 'input[type=password]' ) as $input )
            {
                if ( !$input->autocomplete )
                {
                    $input->autocomplete = 'off';
                }
            }

            if ( isset( $this->_data[ 'layout' ][ 'doctype' ] ) && stripos( $this->_data[ 'layout' ][ 'doctype' ], 'html_5' ) !== false )
            {
                foreach ( $html->find( 'object' ) as $object )
                {
                    if ( !$object->type )
                    {
                        $object->type = 'application/x-shockwave-flash';
                    }
                }
            }


            $code = $html->save();

        }
        else
        {
            $code = $code . '<!-- simple_html_dom warning -->';
        }

        $html = null;
        $dom  = null;

        // $code = preg_replace('#\s*\n*\t*<html#isU', '<html', $code);

        if ( $this->isFrontend )
        {
            $code = preg_replace_callback( '#<div([^>]*)\sdata-options="([^\"]*)"([^>]*)>#isU', create_function( '$m', 'if (strpos($m[0], \'googlemaps\') === false ) {
            return \'<div\'. $m[1] . $m[3] .\'>\';
        }
        else {
            return $m[0];
        }' ), $code );
        }


        // fix textareas
        $code = preg_replace( '/<(textarea|pre)([^>]*)>\s*\n*\r*\t*\s*/is', '<$1$2>', $code );
        $code = preg_replace( '/\s*\n*\r*\s*\t*<\/(textarea|pre)>/is', '</$1>', $code );

        // Ampsan Fix
        $code = preg_replace( '/&(?!:amp;|#[Xx][0-9A-fa-f]+;|#[0-9]+;|[a-zA-Z0-9]+;)/s', '&amp;', $code );

        /**
         * Javascript patch
         */
        if ( is_array( $scriptCache ) )
        {
            foreach ( $scriptCache as $script )
            {
                $code = preg_replace( '#@@@SCRIPT@@#', $script, $code, 1 );
            }
        }

        // repatch CDATA
        $code = preg_replace( '#\/\*\r*\n*\s*\t*\/\*#', '/*', $code );
        $code = preg_replace( '#\*/\r*\n*\s*\t*\*/#', '*/', $code );

        $code = preg_replace( '#/\*\s*\n*\s*/\*#', '/*', $code );
        $code = preg_replace( '#s*\*/\s*\n*\s*\*/#', '*/', $code );
        /*
        if ( !Strings::isUTF8( $code ) )
        {
            // remove non utf-8 chars
            $code = Strings::stripNonUtf8( $code );

            // now convert to utf-8
            $code = Strings::mbConvertTo( Strings::fixLatin( $code ), 'UTF-8' );
        }
*/


        #$code = Strings::entitysToChar($code);


        if ( !Strings::isUTF8( $code ) )
        {
            // now convert to utf-8
            $code = Strings::mbConvertTo( $code, 'UTF-8' );
            Debug::store( 'Process repair', 'Repair UTF-8' );
        }

        if ( !IS_AJAX )
        {
            //$code = preg_replace_callback( "/(&#[0-9]+;)/U", array('Template', 'fixNonAjaxEntitys') , $code );
            $code = preg_replace_callback( "/(&#[0-9]+;)/U", create_function( '$m', 'return mb_convert_encoding( $m[ 1 ], "UTF-8", "HTML-ENTITIES" );' ), $code );
            $code = Strings::stripNonUtf8( $code );
        }

        // protect php
        $code = str_replace( '?>', '?&gt;', str_replace( '<?', '&lt;?', $code ) );

        // patch for space char
        //  $code = str_replace( array(/*"\u0080", "\009e",*/ "\u00a0", "\x{00A0}"), " ", $code );

        Debug::store( 'Process repair', 'Done' );
        Library::enableErrorHandling();


        //$code = Strings::fixLatin(Strings::utf8ToEntities($code));


        /*
        if ( is_array($matches) && count($matches[ 0 ]) )
        {
            $url = Settings::get('portalurl');
            $len = strlen($url);

            foreach ( $matches[ 0 ] as $c )
            {
                preg_match('/<script([^>]*)src=(["\'])([^\2]*)\2([^>]*)>/isU', $c, $m);

                if ( isset( $m[ 3 ] ) )
                {
                    $src = $m[ 3 ];

                    if ( substr($src, 0, 5) == 'asset/' || ( substr($src, 0, $len) != $url && substr($src, 0, 4) !== 'http' ) )
                    {
                        $c = str_ireplace(' src=' . $m[ 2 ] . $src . $m[ 2 ], ' src=' . $m[ 2 ] . $url . '/' . $src . $m[ 2 ], $c);
                    }

                    $code = preg_replace('#@@@SCRIPT@@#', $c, $code, 1);
                    //$code = preg_replace('#</body>#i', $c .'</body>', $code);

                }
                else
                {
                    $code = preg_replace('#@@@SCRIPT@@#', $c, $code, 1);
                    #$code = preg_replace('#</body>#i', $c .'</body>', $code);
                }
            }

            #unset( $matches );
        }
        */


        // repatch CDATA
        /*
                $forms = Html::extractTags($code, 'form', false, true);
                if ( is_array($forms) )
                {
                    $x = 0;
                    foreach ( $forms as $r )
                    {
                        $x++;
                        $str    = $r[ 'contents' ];
                        $inputs = Html::extractTags($str, 'input', true, true);


                        if ( is_array($inputs) )
                        {
                            $org           = $r[ 'full_tag' ];
                            $foundcsrf     = false;
                            $foundFormSend = false;
                            foreach ( $inputs as $rs )
                            {
                                if ( isset( $rs[ 'attributes' ][ 'type' ] ) && $rs[ 'attributes' ][ 'type' ] == 'password' )
                                {
                                    if ( !isset( $rs[ 'attributes' ][ 'autocomplete' ] ) )
                                    {
                                        $fix = str_ireplace('<input ', '<input autocomplete="off" ', $rs[ 'full_tag' ]);
                                        $org = str_replace($rs[ 'full_tag' ], $fix, $org);
                                    }
                                }

                                if ( isset( $rs[ 'attributes' ][ 'name' ] ) )
                                {
                                    if ( $rs[ 'attributes' ][ 'name' ] == 'token' )
                                    {
                                        $foundcsrf = true;
                                    }
                                    if ( $rs[ 'attributes' ][ 'name' ] == '_fsend' )
                                    {
                                        $foundFormSend = true;
                                    }
                                }
                            }

                            if ( !$foundcsrf )
                            {
                                $token = Form::getCSRFToken('token');
                                $org   = str_ireplace('</form>', '<input type="hidden" name="token" value="' . $token . '"/></form>', $org);
                            }


                            if ( !$foundFormSend )
                            {
                                $org = str_ireplace('</form>', '<input type="hidden" name="_fsend" value="1"/></form>', $org);
                            }


                            // fix form if not has attribute method
                            // set default method to POST
                            if ( !isset( $r[ 'attributes' ][ 'method' ] ) )
                            {
                                $org = str_ireplace('<form ', '<form method="post" ', $org);
                            }

                            $code = str_replace($r[ 'full_tag' ], $org, $code);
                        }
                    }
                }
        */
        #$code = preg_replace('#data-options\s*=\s*"([^"]*)"\s*#is', '', $code);
        #$code = preg_replace('#data-options\s*=\s*\'([^\']*)\'\s*#is', '', $code);
        #unset( $forms );
        /*

                $images = Html::extractTags($code, 'img', true, true);
                if ( is_array($images) )
                {
                    //print_r($images);exit;
                    foreach ( $images as $r )
                    {
                        $org = $r[ 'full_tag' ];

                        if ( isset($r[ 'attributes' ]['data-options']) )
                        {
                            $r[ 'full_tag' ] = str_replace( array('data-options="'.$r[ 'attributes' ]['data-options'].'"', "data-options='".$r[ 'attributes' ]['data-options'] ."'"), '', $r[ 'full_tag' ]);
                        }

                        if ( !isset( $r[ 'attributes' ][ 'alt' ] ) )
                        {

                            if ( !empty( $r[ 'attributes' ][ 'title' ] ) )
                            {
                                $r[ 'full_tag' ] = str_ireplace('<img ', '<img ' . 'alt="' . $r[ 'attributes' ][ 'title' ] . '" ', $r[ 'full_tag' ]);
                            }
                            else
                            {
                                $r[ 'full_tag' ] = str_ireplace('<img ', '<img ' . 'alt="-" ', $r[ 'full_tag' ]);
                            }
                        }
                        else {
                            if ( !isset( $r[ 'attributes' ][ 'title' ] ) )
                            {
                                $r[ 'full_tag' ] = str_ireplace('<img ', '<img ' . 'title="' . $r[ 'attributes' ][ 'alt' ] . '" ', $r[ 'full_tag' ]);
                            }
                        }

                        $code = str_replace($org, $r[ 'full_tag' ], $code);

                    }
                }
        */
        #unset( $images );

        /*
                if ( isset( $this->_data[ 'layout' ][ 'doctype' ] ) && stripos($this->_data[ 'layout' ][ 'doctype' ], 'html_5') !== false )
                {
                    $code = preg_replace('#\s(wrap|align)\s*=\s*(["\'])([^\2]*)\2#isU', '', $code);
                    $code = preg_replace('#<script([^>]*)language\s*=\s*([\'"])javascript\2([^>]*)>#isU', '<script$1$3>', $code);
                    $code = preg_replace('#<script([^>]*)charset\s*=\s*([\'"])([^\2]*)\2([^>]*)>#isU', '<script$1$4>', $code);

                    $objects = Html::extractTags($code, 'object', null, true);

                    if ( is_array($objects) )
                    {
                        foreach ( $objects as $r )
                        {
                            $org = $r[ 'full_tag' ];
                            if ( !isset( $r[ 'attributes' ][ 'type' ] ) )
                            {
                                $r[ 'full_tag' ] = str_replace('<object ', '<object ' . 'type="application/x-shockwave-flash" ', $r[ 'full_tag' ]);
                            }

                            $orgC            = $r[ 'contents' ];
                            $r[ 'contents' ] = str_replace(array (
                                                                 '  />',
                                                                 ' />'
                                                           ), '/>', $r[ 'contents' ]);
                            $r[ 'full_tag' ] = str_replace($orgC, $r[ 'contents' ], $r[ 'full_tag' ]);

                            $code = str_replace($org, $r[ 'full_tag' ], $code);
                        }
                    }

                    #unset( $objects );

                    #print_r($objects);exit;
                }


                $objects = Html::extractTags($code, 'p', null, true);
                if ( is_array($objects) )
                {
                    foreach ( $objects as $r )
                    {
                        $org = $r[ 'full_tag' ];

                        $after = '';

                        if ( stripos($org, '<form') !== false )
                        {
                            $str   = $r[ 'contents' ];
                            $forms = Html::extractTags($str, 'form', null, true);

                            if ( is_array($forms) )
                            {

                                foreach ( $forms as $rs )
                                {
                                    $after .= $rs[ 'full_tag' ];
                                    $org = str_replace($rs[ 'full_tag' ], '', $org);
                                }

                                unset( $forms );
                            }
                        }

                        if ( $after )
                        {
                            $code = str_replace($r[ 'full_tag' ], $org . $after, $code);
                        }
                    }
                }
                #unset( $objects );

        */
    }

    /**
     * @return bool
     */
    public function isFacebook()
    {

        if ( !( stristr( $_SERVER[ "HTTP_USER_AGENT" ], 'facebook' ) === false ) )
        {
            return true;
        }

        return false;
    }

    /**
     *
     */
    public function getTemplatePath($template)
    {

        static $skinid, $templateDir;

        if ( $this->renderPlugin )
        {
            $paths = explode( '/', $template );
            array_pop( $paths ); // remove the template filename
            $path = Library::formatPath( '/' . implode( '/', $paths ) );
        }
        else
        {
            if ( $this->isFrontend )
            {
                if ( empty( $skinid ) )
                {
                    $skinid      = User::getSkinId();
                    $templateDir = User::getTemplate();
                }

                $path = $templateDir;
            }
            else
            {
                $path = BACKEND_TPL_PATH;
            }
        }

        return $path;
    }

    /**
     * Compile all Plugin/Widget Template
     *
     * @param string $template
     * @param array $basedata
     * @param bool $return default is null
     * @param string $getBlock (default is null)
     * @internal param array $data
     * @return string
     */
    public function renderTemplate($template, $basedata = array(), $return = null, $getBlock = null)
    {

        $this->renderPlugin = true;


        if ( $return === null || $return === false )
        {
            return $this->process( $template, $basedata, $return, $getBlock );
        }
        else
        {
            $this->process( $template, $basedata, $return, $getBlock );
            exit;
        }
    }

    /**
     *
     * @param mixed $var
     * @param boolean $return
     * @return string
     */
    static function var_export_min($var, $return = false)
    {

        if ( is_array( $var ) )
        {
            $toImplode = array();
            foreach ( $var as $key => $value )
            {

                if ( ( is_numeric( $value ) && substr( $value, 0, 1 ) !== 0 ) || is_bool( $value ) )
                {
                    $toImplode[ ] = var_export( $key, true ) . '=>' . ( is_bool( $value ) ? ( $value ? 'true' : 'false' ) : $value );
                }
                else
                {
                    $toImplode[ ] = var_export( $key, true ) . '=>' . self::var_export_min( $value, true );
                }
            }

            $code = 'array(' . implode( ',', $toImplode ) . ')';
            unset( $toImplode, $var );

            if ( $return )
            {
                return $code;
            }
            else
            {
                echo $code;
            }
        }
        else
        {
            return var_export( $var, $return );
        }
    }

    /**
     * Compile the Template
     *
     * @param string $template
     * @param array $basedata
     * @param bool $return default is null
     * @param string $getBlock (default is null)
     * @param bool $callFromProvider
     * @throws BaseException
     * @internal param array $data
     * @return string
     */
    public function process($template, $basedata = array(), $return = null, $getBlock = null, $callFromProvider = false)
    {

        if ( !trim( $template ) )
        {
            return false;
        }

        $this->_compiledTemplate = null;
        $this->_data             = array_merge( $this->_data, $basedata );


        // send Ajax Rollback
        if ( !$this->isFrontend && HTTP::input( 'transrollback' ) && $return === null )
        {
            Library::sendJson( true );
        }

        $this->load( 'Document' );
        $this->load( 'Provider' );
        $this->load( 'Site' );
        $this->load( 'Breadcrumb' );

        try
        {
            if ( !( $this->_parser instanceof Compiler ) )
            {

                if ( $this->isFrontend )
                {
                    static $skinid, $templateDir;

                    if ( !is_int( $skinid ) )
                    {

                        $skinid      = User::getSkinId();
                        $templateDir = User::getTemplate();

                        if ( !is_dir( PAGE_CACHE_PATH . 'templates/' . $templateDir . '/compiled' ) )
                        {
                            Library::makeDirectory( PAGE_CACHE_PATH . 'templates/' . $templateDir . '/compiled/' );
                        }

                        if ( !is_dir( PAGE_CACHE_PATH . 'templates/' . $templateDir . '/template' ) )
                        {
                            Library::makeDirectory( PAGE_CACHE_PATH . 'templates/' . $templateDir . '/template/' );
                        }
                    }
                    $this->_templateDir = $templateDir;
                    $this->_skinID      = $skinid;

                    //$this->_parser = new TemplateCompiler( TEMPLATES_PATH . $templateDir . '/', PAGE_CACHE_PATH . 'templates/' . $templateDir . '/compiled/', PAGE_CACHE_PATH . 'templates/' . $templateDir . '/template/' );

                    $this->_parser = new Compiler( TEMPLATES_PATH . $templateDir . '/', PAGE_CACHE_PATH . 'templates/' . $templateDir . '/compiled/', PAGE_CACHE_PATH . 'templates/' . $templateDir . '/template/' );


                }
                else
                {

                    if ( !is_dir( CACHE_PATH . 'compiled/' . BACKEND_SKIN . '/' ) )
                    {
                        Library::makeDirectory( CACHE_PATH . 'compiled/' . BACKEND_SKIN . '/' );
                    }

                    if ( !is_dir( CACHE_PATH . 'template/' . BACKEND_SKIN . '/' ) )
                    {
                        Library::makeDirectory( CACHE_PATH . 'template/' . BACKEND_SKIN . '/' );
                    }


                    $this->_parser = new Compiler( BACKEND_TPL_PATH . 'html/', CACHE_PATH . 'compiled/' . BACKEND_SKIN . '/', CACHE_PATH . 'template/' . BACKEND_SKIN . '/' );
                }
            }
        }
        catch ( Exception $e )
        {
            throw new BaseException( $e->getMessage() );
        }


        if ( !$this->_parser instanceof Compiler )
        {
            throw new BaseException( 'Compiler Instance not valid.' );
        }


        if ( $this->isFrontend )
        {
            $this->renderFrontend( $template );
        }
        else
        {
            $this->renderBackend( $template );
        }

        #	exit;


        if ( $return === null || $return === false )
        {
            if ( $getBlock && is_string( $getBlock ) )
            {

                // get multiple blocks
                if ( strpos( $getBlock, ',' ) !== false )
                {
                    $blocks  = array();
                    $_blocks = explode( ',', $getBlock );
                    foreach ( $_blocks as $bname )
                    {
                        $bname = trim( $bname );
                        if ( $bname )
                        {
                            $block = $this->_parser->useBlock( $bname );
                            if ( $block === false )
                            {
                                Error::raise( sprintf( 'Template Block `%s` not exists!', $bname ), __FILE__, __LINE__ );
                            }

                            if ( $this->isFrontend )
                            {
                                $this->prepareFrontendTemplate( $block );
                                $this->repair( $block );
                            }
                            else
                            {
                                $block = $this->prepareBackendTemplate( $block );
                                $this->repair( $block );
                            }

                            $blocks[ ] = Library::unmaskContent( $block );
                        }
                    }


                    unset( $this->_parser );
                    $this->_data = array();


                    return $blocks;

                }
                else
                {
                    $block = $this->_parser->useBlock( $getBlock );

                    # $this->_parser->freeMem();
                    # $this->_parser = null;

                    if ( $block === false )
                    {
                        Error::raise( sprintf( 'Template Block `%s` not exists!', $getBlock ), __FILE__, __LINE__ );
                    }

                    if ( $this->isFrontend )
                    {
                        $this->prepareFrontendTemplate( $block );
                    }
                    else
                    {
                        $block = $this->prepareBackendTemplate( $block );
                    }

                    $block = Library::unmaskContent( $block );

                    unset( $this->_parser );
                    $this->_data = array();

                    return $block;
                }
            }

            #if ($this->_parser !== null) $this->_parser->freeMem();
            #$this->_parser = null;

            if ( $this->isFrontend )
            {
                $this->prepareFrontendTemplate( $this->_compiledTemplate );
                $this->_compiledTemplate = Library::unmaskContent( $this->_compiledTemplate );

            }
            else
            {
                $this->_compiledTemplate = $this->prepareBackendTemplate( $this->_compiledTemplate );
            }


            #$this->_data = array();


            # if (preg_match('#menu#', $template) ) die($template . $this->_compiledTemplate);

            return $this->_compiledTemplate;
        }
        else
        {

            // $this->_parser->getCompiledFilename();

            if ( $this->isFrontend )
            {
                $this->prepareFrontendTemplate( $this->_compiledTemplate );


                if ( isset( $this->_data[ 'layout' ][ 'doctype_html' ] ) )
                {
                    $this->_compiledTemplate = str_ireplace( $this->_data[ 'layout' ][ 'doctype_html' ], '', $this->_compiledTemplate );
                }

                if ( $this->Document->getClickAnalyse() || Settings::get( 'pagedefaultclickanalyse', false ) )
                {
                    $this->_compiledTemplate = str_ireplace( '</body>', '<script type="text/javascript" src="html/js/dcms.clicklogger.js"></script></body>', $this->_compiledTemplate );
                }

                if ( isset( $this->_data[ 'layout' ][ 'doctype' ] ) && stripos( $this->_data[ 'layout' ][ 'doctype' ], 'xhtml' ) === false && $this->isFacebook() )
                {

                    $this->_compiledTemplate = $this->getDocumentTypeHeader( 'html_trans' ) . "\n" . $this->_compiledTemplate;


                    if ( stripos( $this->_compiledTemplate, 'xmlns:fb="' ) === false )
                    {
                        $this->_compiledTemplate = preg_replace( '#<html([^>]*)>#iU', '<html$1 xmlns:fb="http://facebook.com/2008/fbml">', $this->_compiledTemplate );
                    }

                    if ( stripos( $this->_compiledTemplate, '="og:' ) !== false && stripos( $this->_compiledTemplate, 'xmlns:og="' ) === false )
                    {
                        $this->_compiledTemplate = preg_replace( '#<html([^>]*)>#iU', '<html$1 xmlns:og="http://opengraphprotocol.org/schema/">', $this->_compiledTemplate );
                    }
                }
                else
                {
                    $this->_compiledTemplate = ( isset( $this->_data[ 'layout' ][ 'doctype' ] ) ? $this->_data[ 'layout' ][ 'doctype_html' ] : '' ) . $this->_compiledTemplate;
                }


                Hook::run( 'onBeforeCachePage', $this->_compiledTemplate, $this ); // {CONTEXT: framework, DESC: Dieses Ereignis vor dem Cachen des Compilierten Templates ausgelöst.}


                // Cache before render dynamic elements
                $this->load( 'SideCache' );
                $cache = $this->addBodyClasses( $this->_compiledTemplate );
                $this->SideCache->setCache( $cache );


                if ( $this->Provider->hasProviders( $this->_compiledTemplate ) )
                {
                    // render providers
                    $this->_compiledTemplate = $this->Provider->renderProviderTags( $this->_compiledTemplate, 'post' );
                }

                // SSL Patch
                $this->_compiledTemplate = $this->prepareSSLUrls( $this->_compiledTemplate );


            }
            else
            {

            }


            /**
             *
             * Ajax Loads (backend only)
             */
            if ( !$this->isFrontend && ( IS_AJAX || HTTP::input( 'reload' ) == 'maincontent' ) )
            {


                # if ( !IS_AJAX)
                #{
                #       Library::sendJson(false, 'no ajax');
                # }


                $maincontent = '';


                Hook::run( 'onBeforeClean', $this->_compiledTemplate, $this ); // {CONTEXT: framework, DESC: Dieses Ereignis wird vor dem Optimieren/Reparieren ausgeführt.}



                $before = $this->_compiledTemplate;
                $this->repair( $this->_compiledTemplate );

                if (!trim($this->_compiledTemplate) && $before)
                {
                    throw new BaseException('Template repair error');
                }

                $this->_compiledTemplate = Strings::cleanString( $this->_compiledTemplate );
                $maincontent = trim( $this->_parser->useBlock( 'maincontent' ) );

                if (!$maincontent) {
                    if ( !preg_match( '/<\!--\s*maincontent\s*-->/isU', $this->_compiledTemplate ) )
                    {
                        $maincontent = trim( $this->_compiledTemplate );
                    }
                    else
                    {
                        $maincontents = preg_split( '/<\!--\s*maincontent\s*-->/isU', $this->_compiledTemplate );
                        $maincontentx = preg_split( '/<\!--\s*end_maincontent\s*-->.*/isU', $maincontents[ 1 ] );
                        $maincontent  = trim( $maincontentx[ 0 ] );
                    }
                }

                //$maincontent = Strings::fixLatin($maincontent);
                //$maincontent = Strings::fixUtf8($maincontent);

                #$this->repair($maincontent);

                $toolbar = trim( $this->_parser->useBlock( 'toolbar' ) );

                //$toolbar = trim($this->loadBlock('toolbar', $this->_compiledTemplate));

                if ( !$toolbar && isset( $this->extendTplData[ 'toolbar' ] ) )
                {
                    $toolbar = trim( $this->extendTplData[ 'toolbar' ] );
                }

                $toolbar = $this->prepareBackendTemplate( $toolbar );
                //$this->repair( $toolbar );

                /*
                $session_history = $this->db->query( 'SELECT l.* FROM %tp%logs AS l LEFT JOIN %tp%users AS u ON(u.userid=l.userid) ORDER BY time DESC LIMIT 20' )->fetchAll();
                foreach ( $session_history as &$r )
                {
                    unset( $r[ 'data' ] );
                    unset( $r[ 'backtrace' ] );
                }
                */


                Hook::run( 'onBeforeAjaxOutput', $this->_compiledTemplate, $this ); // {CONTEXT: framework, DESC: Dieses Ereignis wird vor der AJAX Ausgabe ausgelöst.}

                ob_get_clean();


                $d = array();

                if ( defined( 'IS_SWFUPLOAD' ) && IS_SWFUPLOAD )
                {

                    $d[ 'msg' ] = 'UPLOAD Error!' . "\n\n" . print_r( HTTP::input(), true );

                    Ajax::Send( false, $d );
                }
                else
                {


                    $modDefine = $this->Site->getController()->getModulConfig();

                    unset( $modDefine[ 'metatables' ], $modDefine[ 'modulactions' ], $modDefine[ 'treeactions' ] );

                    //
                    $applicationMenu = $this->Site->getController()->getControllerMenu();

                    //
                    $maincontent = Library::unmaskContent( $maincontent );

                    Hook::run( 'onBeforeClean', $maincontent, $this ); // {CONTEXT: framework, DESC: Dieses Ereignis wird vor dem Optimieren/Reparieren ausgeführt.}
                    $this->repair( $maincontent );


                    Hook::run( 'onBeforeClean', $toolbar, $this ); // {CONTEXT: framework, DESC: Dieses Ereignis wird vor dem Optimieren/Reparieren ausgeführt.}
                    $this->repair( $toolbar );

                    $d = array(
                        'success'            => true,
                        'csrfToken'          => Csrf::generateCSRF( 'token' ),
                        //       'sessionerror'            => true,
                        'addFileSelector'    => ( isset( $this->_data[ 'addFileSelector' ] ) ? $this->_data[ 'addFileSelector' ] : false ),
                        'bytesize'           => $r[ 'bytesize' ] = Library::dirSize( MODULES_PATH . CONTROLLER . '/' ),
                        'currentLocation'    => $this->Env->requestUri(),
                        'isSingleWindow'     => ( !empty( $modDefine[ 'isSingleWindow' ] ) || !empty( $this->_data[ 'isSingleWindow' ] ) ? true : false ),
                        'sid'                => session_id(),
                        'loadScripts'        => self::$_loadExtraScripts,
                        'dynjs'              => $this->_initDynamicScripts() . ( isset( $GLOBALS[ 'EDITOR_JS' ] ) ? $GLOBALS[ 'EDITOR_JS' ] : '' ),
                        'versioning'         => $this->_data[ 'versioning' ], // 'dock'               => $personaldata,
                        'scrollable'         => $this->_data[ 'scrollable' ],
                        'nopadding'          => $this->_data[ 'nopadding' ],
                        'onAfterMenuCreated' => ( !empty( $this->_data[ 'onAfterMenuCreated' ] ) ? $this->_data[ 'onAfterMenuCreated' ] : false ),
                        'applicationTitle'   => $this->Site->getController()->getModulLabel(),
                        'applicationMenu'    => $applicationMenu,
                        'applicationPath'    => '../Modules/' . CONTROLLER,
                        'applicationDefines' => $modDefine,
                        'applicationIcon128' => CONTROLLER . '_128.png',
                        'applicationIcon64'  => CONTROLLER . '_64.png',
                        'applicationIcon32'  => CONTROLLER . '_32.png',
                        'applicationIcon16'  => CONTROLLER . '_16.png',
                        'requestString'      => $_SERVER[ "QUERY_STRING" ],
                        'pageTitle'          => $this->_data[ 'pageTitle' ],
                        'pageCurrentTitle'   => $this->_data[ 'pageCurrentTitle' ],
                        'runtimer'           => $runtime,
                        'session_expiry'     => $this->_data[ 'session_expiry' ],
                        // 'session_history'    => $session_history,
                        'toolbar'            => $toolbar,
                        'maincontent'        => $maincontent,
                        'debugoutput'        => htmlspecialchars( Debug::write( true ) )
                    );
                    unset( $session_history );

                    //$this->_data = null;
                    //$this->_parser = null;

                    $this->load( 'Document' );
                    if ( $this->Document->getRollback() )
                    {
                        $d[ 'rollback' ]          = true;
                        $d[ 'contentlockaction' ] = self::$_contentUnlockActionName;
                    }

                    if ( isset( $GLOBALS[ 'contentlock' ] ) )
                    {
                        $d[ 'lock_content' ] = true;
                    }


                    $personal = new Personal;


                    $url = preg_replace( '#^.*/?(admin\.php.*)$#', '$1', $this->Env->requestUri() );
                    $url = preg_replace( '#([&\?])ajax=[^&]*#', '', $url );
                    $url = preg_replace( '#([&\?])_=\d+#', '', $url );

                    $win = $personal->get( 'window', md5( $url ) );
                    if ( is_array( $win ) )
                    {
                        $winData = $win;

                        if ( is_array( $winData ) )
                        {
                            $p = explode( '|', $winData[ 'windowpos' ] );
                            if ( $p[ 1 ] > 0 )
                            {
                                $d[ 'winLeft' ] = $p[ 0 ];
                                $d[ 'winTop' ]  = $p[ 1 ];
                            }

                            $s = explode( '|', $winData[ 'windowsize' ] );
                            if ( $s[ 0 ] > 0 && ( empty( $this->_data[ 'WinHeight' ] ) || !isset( $this->_data[ 'WinHeight' ] ) ) )
                            {
                                $d[ 'winWidth' ]  = $s[ 0 ];
                                $d[ 'winHeight' ] = $s[ 1 ];
                            }
                            $d[ 'screensize' ] = $winData[ 'screensize' ];
                        }
                    }


                    if ( isset( $this->_data[ 'WinResizeable' ] ) )
                    {
                        $d[ 'WindowResizeable' ] = $this->_data[ 'WinResizeable' ];
                    }

                    if ( isset( $this->_data[ 'WinMaximize' ] ) )
                    {
                        $d[ 'WindowMaximize' ] = $this->_data[ 'WinMaximize' ];
                    }

                    if ( isset( $this->_data[ 'WinMinimize' ] ) )
                    {
                        $d[ 'WindowMinimize' ] = $this->_data[ 'WinMinimize' ];
                    }

                    if ( isset( $this->_data[ 'WinHeight' ] ) && $this->_data[ 'WinHeight' ] > 0 )
                    {
                        $d[ 'WindowHeight' ] = $this->_data[ 'WinHeight' ];
                    }
                    if ( isset( $this->_data[ 'WinWidth' ] ) && $this->_data[ 'WinWidth' ] > 100 )
                    {
                        $d[ 'WindowWidth' ] = $this->_data[ 'WinWidth' ];
                    }
                    $this->_parser->freeMem();

                    $this->unload( 'Provider' );
                    $this->unload( 'Breadcrumb' );
                    $this->unload( 'Page' );
                    $this->unload( 'Site' );
                    $this->unload( 'Action' );
                    $this->unload( 'Layouter' );

                    Ajax::Send( true, $d );
                }

                exit;
            }


            if ( $this->isFrontend )
            {
                if ( isset( $this->_data[ 'user' ] ) )
                {
                    $tmpdata[ 'user' ] = $this->_data[ 'user' ];
                    $userid            = isset( $tmpdata[ 'user' ][ 'userid' ] ) ? $tmpdata[ 'user' ][ 'userid' ] : User::getUserId();
                }

                $tmpdata = null;
                Hook::run( 'onBeforeClean', $this->_compiledTemplate, $this ); // {CONTEXT: framework, DESC: Dieses Ereignis wird vor dem Optimieren/Reparieren ausgeführt.}
                //  self::cleanContent( $this->_compiledTemplate );
                //$this->repair($this->_compiledTemplate);
                $this->_compiledTemplate = Library::unmaskContent( $this->_compiledTemplate );
                $this->repair( $this->_compiledTemplate );


                if ( !Settings::get( 'mod_rewrite', false ) )
                {
                    //   $this->_compiledTemplate = $this->unModRewrite( $this->_compiledTemplate );
                }

                $layout = $this->getLayout();
            }
            else
            {
                Hook::run( 'onBeforeClean', $this->_compiledTemplate, $this ); // {CONTEXT: framework, DESC: Dieses Ereignis wird vor dem Optimieren/Reparieren ausgeführt.}
                $this->repair( $this->_compiledTemplate );
            }


            //$this->unload( 'Router' );
            // Use the tracker?
            # Tracking::track();


            #print_r(Settings::getAll());
            #exit;
            /**
             * (x)HTML Tidy
             */
            $isXHTML = false;
            if ( Settings::get( 'pretty_html', false ) && !Settings::get( 'compress_html', false ) )
            {
                if ( class_exists( 'tidy', false ) && isset( $layout[ 'doctype' ] ) && stripos( $layout[ 'doctype' ], 'xhtml' ) === true )
                {

                    $this->_compiledTemplate = preg_replace( '#<!DOCTYPE[^>]*>#U', $this->getDocumentTypeHeader( 'xhtml_trans' ), $this->_compiledTemplate );
                    $this->_compiledTemplate = preg_replace( '#<(p|span|div)([^>]*)>\t*\s*\r*\n*</\1>#is', "<$1$2>&nbsp;</$1>", $this->_compiledTemplate );

                    $isXHTML = true;

                    // Tidy
                    $this->_compiledTemplate = Html::pretty( $this->_compiledTemplate, $isXHTML, $layout[ 'doctype' ] );
                }
                else
                {
                    $this->_compiledTemplate = Html::pretty( $this->_compiledTemplate, $isXHTML, $layout[ 'doctype' ] );

                    if ( isset( $layout[ 'doctype' ] ) && stripos( $layout[ 'doctype' ], 'xhtml' ) === false )
                    {
                        $this->_compiledTemplate = preg_replace( '#<!DOCTYPE[^>]*>#sU', $this->getDocumentTypeHeader( $layout[ 'doctype' ] ), $this->_compiledTemplate );
                        $this->_compiledTemplate = preg_replace( '#<html([^>]*)(\s' . preg_quote( 'xmlns="http://www.w3.org/1999/xhtml"' ) . ')?([^>]*)>#isU', '<html$1$2>', $this->_compiledTemplate );

                        if ( strpos( $this->_compiledTemplate, ' xmlns:og="http://opengraphprotocol.org/schema/"' ) === false && stripos( $_SERVER[ 'HTTP_USER_AGENT' ], 'facebook' ) !== false )
                        {
                            $this->_compiledTemplate = preg_replace( '#<html([^>]*)>#isU', '<html$1 xmlns:og="http://opengraphprotocol.org/schema/">', $this->_compiledTemplate );
                        }
                    }
                }
            }

            if ( !Settings::get( 'pretty_html', false ) && Settings::get( 'compress_html', false ) )
            {
                $this->_compiledTemplate = Html::compressHtml( $this->_compiledTemplate );
            }


            if ( DEBUG && DEBUGGING )
            {
                Debug::store( 'System::end', 'end of processing' );
                $this->_compiledTemplate .= Debug::write();
            }
            else
            {
                $_time = ( Debug::getMicroTime() - START );


                $s = "\r\n";
                $s .= "====================================\r\n";
                $s .= 'Memory Used:           ' . Debug::getReadableFileSize( memory_get_peak_usage() ) . "\r\n";
                $s .= 'Memory Limit:          ' . ini_get( "memory_limit" ) . "\r\n";
                $s .= "====================================\r\n";
                $s .= 'Compiler Memory:       ' . Debug::getReadableFileSize( Compiler::getCompileMemory() ) . "\r\n";
                $s .= 'Compiler Time:         ' . Debug::getReadableTime( Compiler::getCompileTimer() ) . "\r\n";
                $s .= "====================================\r\n";
                $s .= 'Script total run Time: ' . Debug::getReadableTime( $_time ) . "\r\n";

                $db            = Database::getInstance();
                $scriptRunTime = $_time - $db->getQueryTimer();

                $s .= 'Script Time:           ' . Debug::getReadableTime( $scriptRunTime ) . "\r\n";
                $s .= 'Database Time:         ' . Debug::getReadableTime( $db->getQueryTimer() ) . "\r\n";
                $s .= "====================================\r\n\r\n";


                $this->_compiledTemplate = str_replace( '</body>', '<!-- ' . $s . ' --></body>', $this->_compiledTemplate );
            }

            // Update the User Location :)
            if ( $this->isFrontend )
            {
                User::enableUserLocationUpdate();
                User::updateUserLocation();
            }

            Session::write();

            #if ($this->_parser instanceof Compiler) $this->_parser->freeMem();
            #$this->freeMem();

            #$this->_parser = null;

            $this->unload( 'Provider' );
            $this->unload( 'Breadcrumb' );
            $this->unload( 'Action' );
            $this->unload( 'Layouter' );


            self::cleanContent( $this->_compiledTemplate );


            $_output = $this->getController()->Output;

            Hook::run( 'onBeforeOutput', $this->_compiledTemplate, $this ); // {CONTEXT: framework, DESC: Dieses Ereignis wird vor der Ausgabe ausgelöst.}

            if (headers_sent())
            {
                $headers = apache_response_headers();
                if ( !empty( $headers ) ) {
                    foreach ( $headers as $name => $value ) {
                        $_output->addHeader( $name, $value );
                    }
                }
            }

            ob_end_clean();
            ob_start();

            if ( Settings::get( 'sendnocacheheaders', false ) || !$this->Document->canCache() || !$this->isFrontend )
            {
                $_output->addHeader( 'Expires', 'Mon, 20 Jul 1995 05:00:00 GMT' );
                $_output->addHeader( 'Last-Modified', gmdate( "D, d M Y H:i:s", $this->Document->getLastModified() ) . " GMT" );
                $_output->addHeader( 'Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0' );
                $_output->addHeader( 'Pragma', 'no-cache' );
            }
            else
            {
                $etag = md5( REQUEST );

                $cacheStamp = time();
                if ( date( 'Z' ) >= 0 )
                {
                    $cacheStamp += date( 'Z' );
                }
                else
                {
                    $cacheStamp -= date( 'Z' );
                }

                $_output->addHeader( 'Pragma', 'public' );
                $_output->addHeader( 'Cache-Control', 'max-age=3600, must-revalidate' );
                $_output->addHeader( 'Expires', gmdate( "D, d M Y H:i:s", $cacheStamp + 3600 ) . " GMT" );
                $_output->addHeader( 'Last-Modified', gmdate( "D, d M Y H:i:s", $this->Document->getLastModified() ) . " GMT" );
                $_output->addHeader( 'Etag', $etag );
            }




            $_output->appendOutput( $this->_compiledTemplate )->setMode( Output::XHTML )->sendOutput();

            unset( $this->_compiledTemplate, $_output );
        }
    }

    /**
     * @param $htmlCode
     * @internal param $headers
     */
    public function sendCacheOutput($htmlCode)
    {

        if ( $this->isFrontend )
        {
            $this->load( 'Provider' );
            $this->load( 'Site' );
            $this->load( 'Breadcrumb' );
        }

        $this->_compiledTemplate = utf8_decode( Strings::fixLatin( $htmlCode ) );

        if ( $this->Provider->hasProviders( $this->_compiledTemplate ) )
        {
            // render providers
            $this->_compiledTemplate = $this->Provider->renderProviderTags( $this->_compiledTemplate );
        }

        $tmpdata[ 'user' ] = User::getUserData();
        $userid            = isset( $tmpdata[ 'user' ][ 'userid' ] ) ? $tmpdata[ 'user' ][ 'userid' ] : User::getUserId();


        $this->unload( 'Provider' );
        $this->unload( 'Breadcrumb' );
        $this->unload( 'Page' );
        $this->unload( 'Site' );
        $this->unload( 'Action' );
        $this->unload( 'Layouter' );

        $tmpdata = null;

        $this->_parser = null;


        #$this->_compiledTemplate = Library::unmaskContent($this->_compiledTemplate);

        // Use the tracker?
        # Tracking::track();

        if ( DEBUG && DEBUGGING )
        {
            Debug::store( 'System::end', 'end of processing' );
            $this->_compiledTemplate .= Debug::write();
        }
        else
        {
            $_time = ( Debug::getMicroTime() - START );


            $s = "\r\n";
            $s .= "====================================\r\n";
            $s .= 'Memory Used:           ' . Debug::getReadableFileSize( memory_get_peak_usage() ) . "\r\n";
            $s .= 'Memory Limit:          ' . ini_get( "memory_limit" ) . "\r\n";
            $s .= "====================================\r\n";
            $s .= 'Compiler Memory:       ' . Debug::getReadableFileSize( TemplateCompiler::getCompileMemory() ) . "\r\n";
            $s .= 'Compiler Time:         ' . Debug::getReadableTime( TemplateCompiler::getCompileTimer() ) . "\r\n";
            $s .= "====================================\r\n";
            $s .= 'Script total run Time: ' . Debug::getReadableTime( $_time ) . "\r\n";

            $db            = Database::getInstance();
            $scriptRunTime = $_time - $db->getQueryTimer();

            $s .= 'Script Time:           ' . Debug::getReadableTime( $scriptRunTime ) . "\r\n";
            $s .= 'Database Time:         ' . Debug::getReadableTime( $db->getQueryTimer() ) . "\r\n";
            $s .= "====================================\r\n\r\n";


            $this->_compiledTemplate = str_replace( '</body>', '<!-- ' . $s . ' --></body>', $this->_compiledTemplate );
        }

        Hook::run( 'onBeforeClean', $this->_compiledTemplate, $this ); // {CONTEXT: framework, DESC: Dieses Ereignis wird vor dem Optimieren/Reparieren ausgeführt.}

        $this->repair( $this->_compiledTemplate );
        $this->_compiledTemplate = Library::unmaskContent( $this->_compiledTemplate );


        $layout = $this->getLayout();

        /**
         * (x)HTML Tidy
         */
        if ( Settings::get( 'pretty_html', false ) && !Settings::get( 'compress_html', false ) )
        {
            if ( class_exists( 'tidy', false ) )
            {
                $this->_compiledTemplate = preg_replace( '#<!DOCTYPE[^>]*>#U', $this->getDocumentTypeHeader( 'xhtml_trans' ), $this->_compiledTemplate );
                $this->_compiledTemplate = preg_replace( '#<(p|span|div)([^>]*)>\t*\s*\r*\n*</\1>#is', "<$1$2>&nbsp;</$1>", $this->_compiledTemplate );

                $isXHTML = false;

                if ( stripos( $layout[ 'doctype' ], 'xhtml' ) !== false )
                {
                    $isXHTML = true;
                }

                // Tidy
                $tidy = Html::pretty( $this->_compiledTemplate, $isXHTML, $layout[ 'doctype' ] );


                if ( stripos( $layout[ 'doctype' ], 'xhtml' ) === false )
                {
                    $tidy = preg_replace( '#<!DOCTYPE[^>]*>#sU', $this->getDocumentTypeHeader( $layout[ 'doctype' ] ), $tidy );
                    $tidy = preg_replace( '#<html([^>]*)(\s' . preg_quote( 'xmlns="http://www.w3.org/1999/xhtml"' ) . ')?([^>]*)>#isU', '<html$1$2>', $tidy );

                    if ( strpos( $tidy, ' xmlns:og="http://opengraphprotocol.org/schema/"' ) === false && stripos( $_SERVER[ 'HTTP_USER_AGENT' ], 'facebook' ) !== false )
                    {
                        $tidy = preg_replace( '#<html([^>]*)>#isU', '<html$1 xmlns:og="http://opengraphprotocol.org/schema/">', $tidy );
                    }
                }

                $this->_compiledTemplate = $tidy;
            }
            else
            {
                $this->_compiledTemplate = Html::pretty( $this->_compiledTemplate, $isXHTML, $layout[ 'doctype' ] );

                if ( stripos( $layout[ 'doctype' ], 'xhtml' ) === false )
                {
                    $this->_compiledTemplate = preg_replace( '#<!DOCTYPE[^>]*>#sU', $this->getDocumentTypeHeader( $layout[ 'doctype' ] ), $this->_compiledTemplate );
                    $this->_compiledTemplate = preg_replace( '#<html([^>]*)(\s' . preg_quote( 'xmlns="http://www.w3.org/1999/xhtml"' ) . ')?([^>]*)>#isU', '<html$1$2>', $this->_compiledTemplate );

                    if ( strpos( $this->_compiledTemplate, ' xmlns:og="http://opengraphprotocol.org/schema/"' ) === false && stripos( $_SERVER[ 'HTTP_USER_AGENT' ], 'facebook' ) !== false )
                    {
                        $this->_compiledTemplate = preg_replace( '#<html([^>]*)>#isU', '<html$1 xmlns:og="http://opengraphprotocol.org/schema/">', $this->_compiledTemplate );
                    }
                }
            }
        }

        if ( !Settings::get( 'pretty_html', false ) && Settings::get( 'compress_html', false ) )
        {
            $this->_compiledTemplate = Html::compressHtml( $this->_compiledTemplate );
        }


        if ( !Settings::get( 'mod_rewrite', false ) )
        {
            $this->_compiledTemplate = $this->unModRewrite( $this->_compiledTemplate );
        }


        // SSL Patch
        $this->_compiledTemplate = $this->prepareSSLUrls( $this->_compiledTemplate );

        self::cleanContent( $this->_compiledTemplate );


        // Update the User Location :)
        if ( $this->isFrontend )
        {
            User::enableUserLocationUpdate();
            User::updateUserLocation();
        }

        $this->freeMem();

        Hook::run( 'onBeforeOutput', $this->_compiledTemplate, $this ); // {CONTEXT: framework, DESC: Dieses Ereignis wird vor der Ausgabe ausgelöst.}



        $_output = $this->getController()->Output;

        if (headers_sent())
        {
            $headers = apache_response_headers();
            if ( !empty( $headers ) ) {
                foreach ( $headers as $name => $value ) {
                    $_output->addHeader( $name, $value );
                }
            }
        }

        ob_end_clean();
        ob_start();

        if ( Settings::get( 'sendnocacheheaders', false ) || !$this->isFrontend )
        {
            $_output->addHeader( 'Expires', 'Mon, 20 Jul 1995 05:00:00 GMT' );
            $_output->addHeader( 'Last-Modified', gmdate( "D, d M Y H:i:s", $this->Document->getLastModified() ) . " GMT" );
            $_output->addHeader( 'Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0' );
            $_output->addHeader( 'Pragma', 'no-cache' );
        }
        else
        {
            $etag = md5( $this->Env->requestUri() );

            $cacheStamp = time();
            if ( date( 'Z' ) >= 0 )
            {
                $cacheStamp += date( 'Z' );
            }
            else
            {
                $cacheStamp -= date( 'Z' );
            }

            $_output->addHeader( 'Pragma', 'public' );
            $_output->addHeader( 'Cache-Control', 'max-age=3600, must-revalidate' );
            $_output->addHeader( 'Expires', gmdate( "D, d M Y H:i:s", $cacheStamp + 3600 ) . " GMT" );
            $_output->addHeader( 'Last-Modified', gmdate( "D, d M Y H:i:s", $this->Document->getLastModified() ) . " GMT" );
            $_output->addHeader( 'Etag', $etag );
        }



        $_output->appendOutput( $this->_compiledTemplate )->setMode( Output::XHTML )->sendOutput();
    }

    /**
     *
     * @param $intemplate
     * @throws BaseException
     * @internal param string $template
     */
    private function renderFrontend($intemplate)
    {

        $template                                      = ( !$this->renderPlugin ? TEMPLATES_PATH . $this->_templateDir . '/' . $intemplate . '.html' : $intemplate );
        $this->_data[ 'contenttranslation' ][ 'flag' ] = Locales::getConfig( 'flag' );

        $this->loadFrontendData();

        $metadata                      = $this->Document->getMetaInstance()->getMetadata();
        $this->_data[ 'documentmeta' ] = array();
        if ( $metadata !== null )
        {
            $this->_data[ 'documentmeta' ] = $metadata;
        }

        // load all document data into the array
        $this->_data[ 'document' ] = $this->Document->get();
        $this->load( 'SideCache' );


        if (is_array(self::$_extraBodyClasses))
        {
            $this->_data[ 'document' ]['body_css'] = implode(' ', self::$_extraBodyClasses);
        }


        if ( $this->SideCache->enabled )
        {
            #$this->_parser->enableSideCache();
        }
        else
        {
            #$this->_parser->disableSideCache();
        }

        #	die($template);


        if ( !( $this->_parser instanceof Compiler ) )
        {
            throw new BaseException( 'Invalid compiler instance!' );
        }

        if ( $this->isProvider )
        {
            $this->_parser->fromprovidertag = true;
        }



        /**
         * Add parser filter for mod_rewrite
         */
        $this->_parser->addFilter( 'a[href]', array('Template', 'filterModrewrite'));
        $this->_parser->addFilter( 'form[action]', array('Template', 'filterModrewrite'));




        $this->_compiledTemplate = $this->_parser->get( $template, $this->_data );

        if ( ( $this->_parser instanceof Compiler ) ) $this->_parser->fromprovidertag = false;

        #if (preg_match('#menu#', $template) ) { print_r($this->_data); die($template . $this->_compiledTemplate); }
        $this->renderPlugin = false;
    }

    /**
     *
     * @param string $template
     * @throws BaseException
     */
    private function renderBackend($template)
    {


        $this->loadBackendData();
        //	$this->_initBackendScripts();


        $this->_data[ 'documentmeta' ]       = array();
        $this->_data[ 'documentmeta_pages' ] = array();

        #      echo '-------------';
        #     print_r($this->_data);exit;
        $metadata = $this->Document->getMetaInstance()->getMetadata();

        if ( $metadata !== null )
        {
            $this->_data[ 'documentmeta' ] = $metadata;
        }

        $this->_data[ 'jcssboot' ] = & $this->cssBootCalls;
        $this->_data[ 'jsboot' ]   = & $this->jsBootCalls;

        $_css = array();

        if ( isset( self::$_loadExtraScripts[ 'css' ] ) && is_array( self::$_loadExtraScripts[ 'css' ] ) )
        {

            foreach ( self::$_loadExtraScripts[ 'css' ] as $s )
            {
                if ( trim( $s ) )
                {
                    if ( preg_match( '#\.css$#', $s ) )
                    {

                        $_css[ ] = '<link href="' . $s . '" type="text/css" rel="stylesheet"/>';
                    }
                    else
                    {
                        $_css[ ] = '<link href="' . $s . '.css" type="text/css" rel="stylesheet"/>';
                    }
                }
            }
        }

        $this->_data[ 'extra_css' ] = implode( '', $_css );

        $_scripts = array();

        if ( isset( self::$_loadExtraScripts[ 'js' ] ) && is_array( self::$_loadExtraScripts[ 'js' ] ) )
        {

            foreach ( self::$_loadExtraScripts[ 'js' ] as $s )
            {
                if ( trim( $s ) )
                {
                    if ( preg_match( '#\.(php|js)$#', $s ) )
                    {

                        $_scripts[ ] = '<script src="' . $s . '" type="text/javascript"></script>';
                    }
                    else
                    {
                        $_scripts[ ] = '<script src="' . $s . '.js" type="text/javascript"></script>';
                    }
                }
            }
        }


        $this->_data[ 'extra_scripts' ] = implode( '', $_scripts );

        $r = $this->getApplication()->getContentTranslation();


        $this->_data[ 'contenttranslation' ][ 'flag' ] = $r[ 'flag' ];

        $template = ( !$this->renderPlugin ? BACKEND_TPL_PATH . 'html/' . $template . '.html' : $template );

        if ( !( $this->_parser instanceof Compiler ) )
        {
            throw new BaseException( 'Invalid compiler instance!' );
        }

        if ( $this->isProvider )
        {
            $this->_parser->fromprovidertag = true;
        }

        $this->_compiledTemplate = $this->prepareBackendTemplate( $this->_parser->get( $template, $this->_data ) );

        if ( ( $this->_parser instanceof Compiler ) ) $this->_parser->fromprovidertag = false;

        $this->renderPlugin = false;
    }


    /**
     * @param $content
     * @param $arributName
     * @return string
     */
    public static function filterModrewrite($content, $arributName)
    {
        $lower = strtolower(trim($content));

        if ( substr( $lower, 0, 7) === 'mailto:' ) {
            $email = substr( trim($content), 7);
            $email = Library::protectEmail($email);
            return 'mailto:' . $email;
        }




        if ( Settings::get( 'mod_rewrite', false ) || substr($lower, 0, 1) === '#' || substr( $lower, 0, 11) === 'javascript:' || substr( $lower, 0, 7) === 'mailto:' ) {
            return $content;
        }

        $addPublic = Settings::get('mod_rewrite_addpublic', false);

        if ( $content && ($arributName === 'href' || $arributName === 'action') )
        {
            $r = parse_url( $content );
            if ( isset( $r[ 'host' ] ) )
            {
                $u = Settings::get('portalurl', '');
                $r2 = parse_url( $u );


                // skip external urls
                if (isset($r2[ 'host' ]) && strtolower($r2[ 'host' ]) !== strtolower($r[ 'host' ]) )
                {
                    return $content;
                }

                $ret = ''; //( isset( $r[ 'scheme' ] ) ? $r[ 'scheme' ] . '//' : '' ) . $r[ 'host' ];


                if ($addPublic) {
                    $ret .= '/public';
                }

                if ( isset( $r[ 'path' ] ) && !empty( $r[ 'path' ] ) )
                {
                    if (strpos($r[ 'path' ], 'index.php') === false) {
                        $ret .= '/index.php?_call=';
                        $ret .= rawurlencode( preg_replace('#^(\./)#', '', $r[ 'path' ]) );
                    }
                    else {
                        $ret .= '/index.php';
                    }

                }
                else
                {
                    $ret .= '/index.php';
                }
            }
            else
            {
                $ret = '';

                if ($addPublic) {
                    $ret .= '/public';
                }

                if ( isset( $r[ 'path' ] ) && !empty( $r[ 'path' ] ) )
                {
                    if (strpos($r[ 'path' ], 'index.php') === false) {
                        $ret .= '/index.php?_call=';
                        $ret .= rawurlencode( preg_replace('#^(\./)#', '', $r[ 'path' ]) );
                    }
                    else {
                        $ret .= '/index.php';
                    }
                }
                else
                {
                    $ret = '/index.php';
                }
            }

            return $ret;
        }

        return $content;
    }

}

?>