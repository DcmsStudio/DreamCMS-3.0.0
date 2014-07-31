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
 * @copyright    2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Abstract.php
 */
class Template_Abstract extends Loader
{

    /**
     * @var array
     */
    protected $_data = array();

    /**
     * @var array
     */
    protected $jsBootCalls = array();

    /**
     * @var array
     */
    protected $cssBootCalls = array();

    /**
     * @var null
     */
    protected static $layout = null;

    /**
     * @var null
     */
    protected static $skinData = null;

    /**
     *
     * @var Compiler
     */
    public $_parser = null;

    /**
     * @var null
     */
    protected $_compiledTemplate = null;

    /**
     * @var null
     */
    protected static $currentPageTitle = null;

    /**
     * @var bool
     */
    protected $_dataLoaded = false;

    /**
     * @var null
     */
    protected $javascripts = null;

    /**
     * @var null
     */
    protected $dynJs = null;

    /**
     * @var
     */
    protected static $rssHeaders;

    /**
     * @var array
     */
    protected static $_loadExtraScripts = array();

    /**
     * @var null
     */
    protected static $_contentUnlockActionName = null;

    /**
     * @var null
     */
    protected static $_extraBodyClasses = null;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->load( 'Document' );

        // reset data
        $this->_data = array();
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }


    public function __destruct()
    {
        parent::__destruct();
        $this->_data = array();

        self::$rssHeaders        = null;
        self::$skinData          = null;
        self::$layout            = null;
    }

    /**
     *
     * @param string $type the rss header type (rss/atom)
     * @param string $title feed Title
     * @param string $controller
     */
    public function addRssHeader($type, $title, $controller = '')
    {
        self::$rssHeaders[ ] = array(
            'rsstype'    => strtolower( $type ),
            'title'      => $title,
            'controller' => $controller);
    }

    /**
     *
     * @param string $typ
     * @param integer $id
     * @param boolean $usercancomment
     * @return void
     */
    public function setComment($typ, $id = 0, $usercancomment = null)
    {
        if ( !$id )
        {
            return;
        }

        if ( $usercancomment !== null )
        {
            User::setUserData( 'can_comment', $usercancomment );
        }
        else
        {
            User::setUserData( 'can_comment', true );
        }

        self::$commentType   = $typ;
        self::$commentPostId = $id;
    }

    /**
     *
     * @param string $key
     * @param mixed $value
     */
    public function assign($key, $value = null)
    {
        $this->_data[ $key ] = $value;
    }

    /**
     *
     * @return array
     */
    public function getTemplateData()
    {
        return $this->_data;
    }

    /**
     *
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->_data = $data;
    }


    protected function refreshFrontendData()
    {
        $this->_data[ 'layout' ][ 'pagetitle' ] = $this->buildPageTitle();
        #      $this->_data[ 'NAVIGATION' ] = $this->buildNavigation();
        $this->_data[ 'current_pagetitle' ] = self::$currentPageTitle;
        $this->_data[ 'breadcrumb' ]        = $this->buildNavigation( true );
    }


    /**
     *
     * @return void
     */
    protected function loadFrontendData()
    {
        /*
        $this->load( 'Document' );
        $this->load( 'Provider' );
        $this->load( 'Site' );
        $this->load( 'Breadcrumb' );

        if ( $this->_dataLoaded )
        {
            $this->_data                            = $this->SetupLayout( $this->_data );
            $_items                                 = $this->Breadcrumb->get();
            self::$currentPageTitle                 = ( count( $_items ) ? $_items[ count( $_items ) - 1 ][ 0 ] : '' );
            $this->_data[ 'layout' ][ 'pagetitle' ] = self::$currentPageTitle;
            $this->_data[ 'current_pagetitle' ]     = self::$currentPageTitle;

            $this->_data[ 'breadcrumb' ] = $this->buildNavigation( true );

            #$this->refreshFrontendData();

            return;
        }
        */

        $this->load( 'Breadcrumb' );
        $this->load( 'Document' );
        $this->initSkin();

        /**
         *
         */
        if (!defined( 'SKIN_CSS_PATH')) /**
         *
         */
            define( 'SKIN_CSS_PATH', SKIN_IMG_URL_PATH . self::$skinData[ 'img_dir' ] . '/css/' );
        /**
         *
         */
        if (!defined( 'SKIN_IMG_PATH')) /**
         *
         */
            define( 'SKIN_IMG_PATH', SKIN_IMG_URL_PATH . self::$skinData[ 'img_dir' ] . '/' );

        if (!defined( 'SKIN_URL')) /**
         *
         */
            define( 'SKIN_URL', SKIN_IMG_URL_PATH . self::$skinData[ 'img_dir' ] . '/' );

        $this->_data[ 'controller-action' ] = ( defined( 'PLUGIN' ) ? PLUGIN : CONTROLLER ) . ( defined( 'ACTION' ) ? ' ' . ACTION : '' );
        $this->_data[ 'css_url' ]           = SKIN_CSS_PATH;
        $this->_data[ 'img_url' ]           = SKIN_IMG_PATH;

        $this->_data[ 'pagepath' ]         = 'pages/' . SERVER_PAGE;
        $this->_data[ 'backendImagePath' ] = BACKEND_IMAGE_PATH;
        $this->_data[ 'cfg' ]              = Settings::getAll();
        $this->_data[ 'js_url' ]           = JS_URL;
        $this->_data[ 'version' ]          = VERSION;
        $this->_data[ 'cookieprefix' ]     = COOKIE_PREFIX;
        $this->_data[ 'html_path' ]        = HTML_URL;
        $this->_data[ 'website' ]          = PAGEID;
        $this->_data[ 'user' ]             = User::getUserData();

        $this->_data[ 'uploadsize' ]  = Library::getMaxUploadSize();
        $this->_data[ 'uploadlimit' ] = ( $this->_data[ 'uploadsize' ] * 1024 );
        $this->_data[ 'session_id' ]  = session_id();

        $this->_data = $this->SetupLayout( $this->_data );

        $this->refreshFrontendData();


        $metadata = $this->Document->getMetatags();

        if ( trim( $metadata[ 'author' ] ) )
        {
            $this->_data[ 'META_AUTHOR' ] = trim( $metadata[ 'author' ] );
        }

        if ( trim( $metadata[ 'copyright' ] ) )
        {
            $this->_data[ 'META_COPYRIGHT' ] = trim( $metadata[ 'copyright' ] );
        }

        if ( trim( $metadata[ 'language' ] ) )
        {
            $this->_data[ 'META_LANGUAGE' ] = trim( $metadata[ 'language' ] );
        }

        if ( trim( $metadata[ 'description' ] ) )
        {
            $this->_data[ 'META_DESCRIPTION' ] = trim( $metadata[ 'description' ] );
        }

        if ( trim( $metadata[ 'keywords' ] ) )
        {
            $this->_data[ 'META_KEYWORDS' ] = trim( $metadata[ 'keywords' ] );
        }

        if ( trim( $metadata[ 'expires' ] ) )
        {
            $this->_data[ 'META_EXPIRES' ] = trim( $metadata[ 'expires' ] );
        }

        if ( trim( $metadata[ 'robot_indexfollow' ] ) )
        {
            $this->_data[ 'META_ROBOTS' ] = trim( $metadata[ 'robot_indexfollow' ] );
        }

        if ( trim( $metadata[ 'robot_revisit' ] ) )
        {
            $this->_data[ 'META_REVISIT_AFTER' ] = trim( $metadata[ 'robot_revisit' ] );
        }


        $this->_data[ 'og' ] = $this->getController()->getSocialNetworkData();

        $this->_dataLoaded = true;
    }

    /**
     *
     * @return array
     */
    protected function getPageTitle()
    {
        $items = $this->Breadcrumb->get();

        $title    = array();
        $title[ ] = Settings::get( 'pagename' );
        foreach ( $items as $r )
        {
            if ( $r[ 0 ] )
            {
                $title[ ] = $r[ 0 ];
            }
        }

        return $title;
    }

    /**
     *
     * @return string
     */
    protected function buildPageTitle()
    {
        if ( is_null( self::$skinData ) )
        {
            self::$skinData = User::loadSkin();
        }

        // $items = Library::getNavi();
        $_items = $this->Breadcrumb->get();


        $title = Settings::get( 'pagename' );

        if ( self::$currentPageTitle === null )
        {
            self::$currentPageTitle = ( count( $_items ) ? $_items[ count( $_items ) - 1 ][ 0 ] : '' );
        }

        foreach ( $_items as $r )
        {
            $title .= ( $r[ 0 ] ? ( $title !== '' ? ' - ' : '' ) . $r[ 0 ] : '' );
        }


        return $title;
    }

    /**
     * Build the frontend Breadcrumb
     *
     * @param bool $returnOnly default is false
     * @return string
     */
    protected function buildNavigation($returnOnly = false)
    {
        if ( is_null( self::$skinData ) )
        {
            self::$skinData = User::loadSkin();
        }

        $_items = $this->Breadcrumb->get();

        # print_r($_items);exit;

        $frontpage = Settings::get( 'frontpage', '' );
        if ( $frontpage )
        {
            foreach ( $_items as $idx => $r )
            {
                if ( $r[ 1 ] )
                {
                    $frontpageNoDomain = preg_replace( '#^' . preg_quote( Settings::get( 'portalurl' ), '#' ) . '#is', '', $frontpage );
                    if ( preg_match( '#' . preg_quote( $frontpage, '#' ) . '#is', '/' . $r[ 1 ] ) || preg_match( '#' . preg_quote( $frontpageNoDomain, '#' ) . '#is', '/' . $r[ 1 ] ) )
                    {
                        for ( $x = 0; $x <= $idx; ++$x )
                        {
                            unset( $_items[ $x ] );
                        }
                        break;
                    }
                }
            }
        }

        if ( !is_array( $_items ) )
        {
            return '';
        }

        $navi    = '';
        $returns = array();
        foreach ( $_items as $r )
        {
            if ( !empty( $r[ 1 ] ) )
            {

                if ( substr( $r[ 1 ], 0, 1 ) === '/' )
                {
                    $r[ 1 ] = substr( $r[ 1 ], 1 );
                }

                $navi .= ( $r[ 0 ] ? ( $navi !== '' ? '<div class="sep"><span>' . self::$skinData[ 'navsplitter' ] . '</span></div>' : '' ) . '<a href="./' . $r[ 1 ] . '" class="navbar">' . $r[ 0 ] . '</a>' : '' );
            }
            else
            {
                $navi .= ( $r[ 0 ] ? ( $navi !== '' ? '<div class="sep"><span>' . self::$skinData[ 'navsplitter' ] . '</span></div>' : '' ) . '<span class="navbar">' . $r[ 0 ] . '</span>' : '' );
            }


            if ( $r[ 0 ] )
            {
                $returns[ ] = array('url' => $r[ 1 ], 'label' => $r[ 0 ]);
            }
        }

        if ( self::$currentPageTitle === null )
        {
            self::$currentPageTitle = ( count( $_items ) ? $_items[ count( $_items ) - 1 ][ 0 ] : '' );
        }

        if ( $returnOnly )
        {
            return $returns;
        }

        if ( $navi )
        {
            $navi = '<div class="sep"><span>' . self::$skinData[ 'navsplitter' ] . '</span></div>' . $navi;
        }


        return $navi;
    }

    /**
     * load parsed block
     *
     * @param string $name is the name of block
     * @param        $output
     * @return string
     */
    protected function loadBlock($name, $output)
    {
        $object_pieces = explode( '<!--StartBlock:' . $name, $output );
        $code          = array_shift( $object_pieces );

        foreach ( $object_pieces as $object_piece )
        {
            list ( $loop_name, $end ) = explode( '-->', $object_piece, 2 );
            $loop = explode( '<!--EndBlock:' . $name . '-->', $end, 2 );

            if ( 2 === count( $loop ) )
            {
                list ( $loop_code, $end ) = $loop;

                return $loop_code;
            }
        }

        return '';
    }

    /**
     *
     * @param type $output
     * @return type
     */
    private function highlighter_header(&$output)
    {
        return $output;

        static $isset;

        if ( $isset === true )
        {
            return $output;
        }
        if ( stripos( $output, 'js/syntax/scripts/shCore' ) !== false || stripos( $output, 'js/syntax/scripts/shCore' ) !== false )
        {
            $isset = true;

            return $output;
        }

        $isset        = true;
        $css_path     = Settings::get( 'portalurl' ) . '/html/js/syntax/';
        $current_path = Settings::get( 'portalurl' ) . '/html/js/syntax/';
        $url          = Settings::get( 'portalurl' );
        $header       = <<<EOF
<script language="javascript" type="text/javascript" src="asset/js/syntax/scripts/shCore,syntax/scripts/shBrushCss,syntax/scripts/shBrushSql,syntax/scripts/shBrushPhp,syntax/scripts/shBrushXml,syntax/scripts/shBrushPlain"></script>
<script language="javascript" type="text/javascript">
/*<![CDATA[*/

        setTimeout(function(){
		SyntaxHighlighter.config.clipboardSwf = '{$url}/html/js/syntax/scripts/clipboard.swf';
		SyntaxHighlighter.config.bloggerMode = true;
		SyntaxHighlighter.all();
        }, 500);

/*]]>*/
</script>

EOF;

        return str_replace( '</head>', $header . "\n\t" . '</head>', $output );
    }

    /**
     *
     * @param string $str
     * @return string
     */
    private function addCopyHeader(&$str)
    {
        $y    = date( 'Y' );
        $copy = <<<EOF

<!--
	This website is powered by DreamCMS
	Copyright (c)2007-{$y} by Marcel Domke
	Visit the project website at http://www.dcms-studio.de for more information
//-->
EOF;


        return preg_replace( '/<head(?!(er))([^>]*)+>/isU', '<head$1>' . $copy, $str );
    }

    /**
     *
     * @param string $output (reference)
     */
    protected function prepareFrontendTemplate(&$output)
    {
        $this->load('Input');

        // little Template Parser Output CleanUp
        $output = preg_replace( '#<!--([^>]*)(StartBlock|EndBlock|dcmsTag_block)([^>]+)+>#iU', '', $output );

        //
        $output = Library::maskContent( $output );

        /**
         * prepare head tag
         */
        if ( stripos( $output, '</head>' ) !== false )
        {
            /**
             * add rss headers
             */
            if ( is_array( $this->Document->_rssHeaders ) )
            {
                $feed = '';
                foreach ( $this->Document->_rssHeaders as $r )
                {
                    $_controll = ( $r[ 'controller' ] ? $r[ 'controller' ] : CONTROLLER );

                    if ( $r[ 'rsstype' ] === 'rss' )
                    {
                        $feed .= "\n\t\t" . '<link rel="alternate" href="' . $_controll . '.rss" type="application/rss+xml" title="' . Settings::get( 'pagename' ) . ' - ' . htmlspecialchars( $r[ 'title' ] ) . ' (RSS)' . '" />';
                    }
                    elseif ( $r[ 'rsstype' ] === 'atom' )
                    {
                        $feed .= "\n\t\t" . '<link rel="alternate" href="' . $_controll . '.atom" type="application/atom+xml" title="' . Settings::get( 'pagename' ) . ' - ' . htmlspecialchars( $r[ 'title' ] ) . ' (ATOM)' . '" />';
                    }
                }

                if ( $feed )
                {
                    $output = str_ireplace( '</head>', $feed . "\n" . '</head>', $output );
                }
            }


            /**
             * Add short link
             */
            if ( strpos( $output, '<link rel="shortlink"' ) === false && $this->Input->getMethod() == 'get' )
            {

                $shortlink    = '<link rel="shortlink" href="' . Settings::get( 'portalurl' ) . '/index.php';
                $shortlinkTmp = '';

                foreach ( $this->_get() as $k => $v )
                {
                    if ( $k !== '_call' )
                    {
                        $shortlinkTmp .= ( $shortlinkTmp ? '&amp;' : '' ) . $k . '=' . $v;
                    }
                }

                $shortlink .= ( $shortlinkTmp ? '?' . $shortlinkTmp : '' ) . '"/>';

                $output = str_ireplace( '</head>', $shortlink . "\n" . '</head>', $output );
            }


            if ( stripos( $output, 'type="application/rsd+xml' ) === false && stripos( $output, 'type=\'application/rsd+xml' ) === false )
            {
                $rsd    = '<link rel="EditURI" type="application/rsd+xml" title="RSD" href="' . Settings::get( 'portalurl' ) . '/xmlrpc/index.php?rsd" />';
                $output = str_ireplace( '</head>', "\n\t" . $rsd . '</head>', $output );
            }

            if ( stripos( $output, 'type="application/wlwmanifest+xml' ) === false && stripos( $output, 'type=\'application/wlwmanifest+xml' ) === false )
            {
                $wlw    = '<link rel="wlwmanifest" type="application/wlwmanifest+xml" href="' . Settings::get( 'portalurl' ) . '/xmlrpc/wlwmanifest.xml" />';
                $output = str_ireplace( '</head>', "\n\t" . $wlw . '</head>', $output );
            }

            $output = str_ireplace( '</head>', "\n" . '</head>', $output );

            $output = $this->addCopyHeader( $output );
        }


        /**
         * Email Obfuscation
         */
        $mails  = null;
        $regexp = '/(([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6})/iU';
        preg_match_all( $regexp, $output, $mails, PREG_PATTERN_ORDER );
        if ( isset( $mails[ 0 ] ) )
        {
            foreach ( $mails[ 0 ] as $mail )
            {
                $output = str_ireplace( $mail, Library::protectEmail( $mail ), $output );
            }
        }
        unset( $mails );
    }

    /**
     *
     * @return void
     */
    protected function initSkin()
    {
        if ( is_array( self::$skinData ) )
        {
            return;
        }
        self::$skinData = User::loadSkin();
    }

    /**
     *
     * @staticvar array $layout
     * @throws BaseException
     * @return array
     */
    public function getLayout()
    {
        static $layout;
        if ( is_array( self::$layout ) )
        {
            return self::$layout;
        }


        if ( !is_array( self::$skinData ) )
        {
            self::$skinData = User::loadSkin();
        }

        $skinid = self::$skinData[ 'id' ];

        $layout = array();

        $isErrorLayout = $this->Document->get( 'isErrorLayout', false );
        $isPrintLayout = $this->Document->get( 'isPrintLayout', false );


        $id       = $this->Document->get( 'contentlayout' );
        $felayout = $this->Document->getLayout();


        $_layoutTemplate = 'layout';

        if ( $isErrorLayout === true )
        {
            $_layoutTemplate = 'errorpage';
        }

        if ( !$isErrorLayout && $isPrintLayout )
        {
            // $_layoutTemplate = 'layout';
        }


        if ( $id > 0 )
        {
            $felayout = null;
        }

        $this->load('Database', 'db');


        if ( !$isErrorLayout && $felayout !== null )
        {
            if ( !$isErrorLayout )
            {
                $layout = $this->db->query( 'SELECT * FROM %tp%layouts '
                    . 'WHERE (skinid = ? AND modules LIKE ? OR modules LIKE ?) '
                    . 'AND (template = ? OR template = ?) LIMIT 1', $skinid, '%"' . $felayout . '"%', '%' . $felayout . '%', $_layoutTemplate, $_layoutTemplate . '_html5' )->fetch();
            }
            else
            {
                $layout = $this->db->query( 'SELECT * FROM %tp%layouts WHERE skinid = ? AND (template = ? OR template = ?) LIMIT 1', $skinid, $_layoutTemplate, $_layoutTemplate . '_html5' )->fetch();
            }


            if ( !$layout[ 'id' ] )
            {
                $layout = $this->db->query( 'SELECT * FROM %tp%layouts WHERE defaultlayout = 1 AND skinid = ? AND (template = ? OR template = ?) LIMIT 1', $skinid, $_layoutTemplate, $_layoutTemplate . '_html5' )->fetch();
            }
        }
        else
        {
            if ( !$id )
            {
                if ( !$isErrorLayout )
                {

                    $defMod = defined( 'PLUGIN' );


                    if ( $defMod && defined( 'PLUGIN_ACTION' ) )
                    {
                        $layout = $this->db->query( 'SELECT * FROM %tp%layouts
                            WHERE (skinid = ? AND modules LIKE ?)
                            AND (template = ? OR template = ?)
                            LIMIT 1', $skinid, '%"' . ( strtolower( CONTROLLER ) . '-' . strtolower( PLUGIN ) . '-' . strtolower( PLUGIN_ACTION ) ) . '"%', $_layoutTemplate, $_layoutTemplate . '_html5' )->fetch();


                        if ( !$layout[ 'skinid' ] )
                        {
                            $layout = $this->db->query( 'SELECT * FROM %tp%layouts
                            WHERE (skinid = ? AND modules LIKE ?) AND (template = ? OR template = ?)
                            LIMIT 1', $skinid, '%"' . ( strtolower( CONTROLLER ) . '-' . strtolower( PLUGIN ) ) . '%', $_layoutTemplate, $_layoutTemplate . '_html5' )->fetch();
                        }
                    }
                    elseif ( $defMod )
                    {
                        $layout = $this->db->query( 'SELECT * FROM %tp%layouts
                            WHERE (skinid = ? AND modules LIKE ?) AND (template = ? OR template = ?)
                            LIMIT 1', $skinid, '%"' . strtolower( CONTROLLER ) . '-' . strtolower( PLUGIN ) . '"%', $_layoutTemplate, $_layoutTemplate . '_html5' )->fetch();
                    }
                    else
                    {
                        $layout = $this->db->query( 'SELECT * FROM %tp%layouts WHERE (skinid = ? AND modules LIKE ?) AND (template = ? OR template = ?) LIMIT 1', $skinid, '%"' . ( strtolower( CONTROLLER ) . '-' . strtolower( ACTION ) ) . '"%', $_layoutTemplate, $_layoutTemplate . '_html5' )->fetch();
                    }
                }
                else
                {
                    $layout = $this->db->query( 'SELECT * FROM %tp%layouts WHERE skinid = ? AND (template = ? OR template = ?) LIMIT 1', $skinid, $_layoutTemplate, $_layoutTemplate . '_html5' )->fetch();
                }
            }
            else
            {
                $layout = $this->db->query( 'SELECT * FROM %tp%layouts WHERE skinid = ? AND id = ? AND (template = ? OR template = ?)  LIMIT 1', $skinid, $id, $_layoutTemplate, $_layoutTemplate . '_html5' )->fetch();
            }

            if ( !$layout[ 'id' ] )
            {
                $layout = $this->db->query( 'SELECT * FROM %tp%layouts WHERE defaultlayout = 1 AND skinid = ? AND (template = ? OR template = ?)  LIMIT 1', $skinid, $_layoutTemplate, $_layoutTemplate . '_html5' )->fetch();
            }
        }

        if ( !$layout[ 'id' ] )
        {
            throw new BaseException( 'Can´t find the Layout for the current page!' );
        }

        self::$layout = $layout;

        return self::$layout;
    }

    /**
     *
     * @param string $doctype
     * @return string
     */
    public function getDocumentTypeHeader($doctype)
    {
        switch ( $doctype )
        {
            case 'xhtml_trans' :
            default:
                return '<!DOCTYPE html
PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
                break;
            case 'xhtml_strict' :
                return '<!DOCTYPE html
PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
                break;
            case 'xhtml_frames' :
                return '<!DOCTYPE html
PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">';
                break;
            case 'xhtml_basic' :
                return '<!DOCTYPE html
PUBLIC "-//W3C//DTD XHTML Basic 1.0//EN"
"http://www.w3.org/TR/xhtml-basic/xhtml-basic10.dtd">';
                break;
            case 'xhtml_11' :
                return '<!DOCTYPE html
PUBLIC "-//W3C//DTD XHTML 1.1//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
                break;
            case 'xhtml_2' :
                return '<!DOCTYPE html
PUBLIC "-//W3C//DTD XHTML 2.0//EN"
"http://www.w3.org/TR/xhtml2/DTD/xhtml2.dtd">';
                break;
            case 'html_5' :
                return '<!DOCTYPE html>';
                break;
            case 'html_4_trans' :
                return '<!DOCTYPE html
PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">';
                break;
        }
    }

    /**
     * prepare the layout template data
     *
     * @staticvar array $layout
     * @param array $data
     * @return array
     */
    public function SetupLayout($data)
    {
        static $layout;

        unset( $data[ 'layout' ] );


        if ( is_array( $layout ) )
        {
            $data[ 'layout' ]        =& $layout;
            $this->_data[ 'layout' ] =& $layout;

            return $data;
        }

        if ( !is_array( $layout ) )
        {

            $layout = $this->getLayout();


            $layout[ 'headerheight' ]       = unserialize( $layout[ 'headerheight' ] );
            $layout[ 'customheaderheight' ] = unserialize( $layout[ 'customheaderheight' ] );

            $layout[ 'footerheight' ]       = unserialize( $layout[ 'footerheight' ] );
            $layout[ 'customfooterheight' ] = unserialize( $layout[ 'customfooterheight' ] );


            $layout[ 'widthleft' ]  = unserialize( $layout[ 'widthleft' ] );
            $layout[ 'widthright' ] = unserialize( $layout[ 'widthright' ] );
            $layout[ 'width' ]      = unserialize( $layout[ 'width' ] );

            $header       = '';
            $customheader = '';
            $footer       = '';
            $customfooter = '';
            $main         = '';
            $wrapper      = '';
            $width        = '';
            $left         = '';
            $right        = '';


            if ( $layout[ 'header' ] )
            {
                if ( $layout[ 'headerheight' ][ 'value' ] )
                {
                    $height = $layout[ 'headerheight' ][ 'value' ] . ( $layout[ 'headerheight' ][ 'unit' ] ? $layout[ 'headerheight' ][ 'unit' ] : '' );
                    if ( $height )
                    {
                        $header = "#header {height:{$height};}\n";
                    }
                }
            }

            if ( $layout[ 'customheader' ] )
            {
                if ( $layout[ 'customheaderheight' ][ 'value' ] )
                {
                    $customheader = $layout[ 'customheaderheight' ][ 'value' ] . ( $layout[ 'customheaderheight' ][ 'unit' ] ? $layout[ 'customheaderheight' ][ 'unit' ] : '' );
                    if ( $customheader )
                    {
                        $customheader = "#customheader {height:{$customheader};}\n";
                    }
                }
            }

            if ( $layout[ 'footer' ] )
            {
                if ( $layout[ 'footerheight' ][ 'value' ] )
                {
                    $height = $layout[ 'footerheight' ][ 'value' ] . ( $layout[ 'footerheight' ][ 'unit' ] ? $layout[ 'footerheight' ][ 'unit' ] : '' );
                    if ( $height )
                    {
                        $footer = "#footer {height:{$height};}\n";
                    }
                }
            }

            if ( $layout[ 'customfooter' ] )
            {
                if ( $layout[ 'customfooterheight' ][ 'value' ] )
                {
                    $customheight = $layout[ 'customfooterheight' ][ 'value' ] . ( $layout[ 'customfooterheight' ][ 'unit' ] ? $layout[ 'customfooterheight' ][ 'unit' ] : '' );
                    if ( $customheight )
                    {
                        $customfooter = "#customfooter {height:{$customheight};}\n";
                    }
                }
            }


            if ( $layout[ 'static' ] )
            {
                if ( $layout[ 'align' ] == 'center' )
                {
                    $layout[ 'align' ] = 'margin:0 auto 0 0;'; /* right */
                }
                if ( $layout[ 'align' ] == 'center' )
                {
                    $layout[ 'align' ] = 'margin:0 0 0 auto;'; /* left */
                }
                if ( $layout[ 'align' ] == 'center' )
                {
                    $layout[ 'align' ] = 'margin:0 auto;'; /* center */
                }

                if ( $layout[ 'width' ][ 'value' ] )
                {
                    $width = $layout[ 'width' ][ 'value' ] . ( $layout[ 'width' ][ 'unit' ] ? $layout[ 'width' ][ 'unit' ] : '' );
                    if ( $width )
                    {
                        // $layout['align'] must insert here
                        $wrapper = "#wrapper {width:{$width};}\n";
                    }
                }
            }

            switch ( $layout[ 'cols' ] )
            {
                case 'cols0-content':
                    $layout[ 'left' ]  = false;
                    $layout[ 'right' ] = false;
                    break;
                case 'cols0-content-margin':
                    $layout[ 'left' ]  = false;
                    $layout[ 'right' ] = false;
                    break;
                case 'cols2-content-left':
                    $layout[ 'left' ]  = true;
                    $layout[ 'right' ] = false;
                    break;
                case 'cols2-content-right':
                    $layout[ 'left' ]  = false;
                    $layout[ 'right' ] = true;


                    break;
                case 'cols3-left-content-right':
                    $layout[ 'left' ]  = true;
                    $layout[ 'right' ] = true;
                    break;
            }


            if ( $layout[ 'widthleft' ][ 'value' ] && $layout[ 'left' ] )
            {
                $width = $layout[ 'widthleft' ][ 'value' ] . ( $layout[ 'widthleft' ][ 'unit' ] ? $layout[ 'widthleft' ][ 'unit' ] : '' );
                if ( $width )
                {
                    $left = "#left {width:{$width};float:left;display:inline-block;}\n";
                    $main .= "margin-left:{$width};";
                }
            }

            if ( $layout[ 'widthright' ][ 'value' ] && $layout[ 'right' ] )
            {
                $width = $layout[ 'widthright' ][ 'value' ] . ( $layout[ 'widthright' ][ 'unit' ] ? $layout[ 'widthright' ][ 'unit' ] : '' );
                if ( $width )
                {
                    $right = "#right {width:{$width};float:right;display:inline-block;}\n";
                    $main .= "margin-right:{$width};";
                }
            }

            $main = ( $main ? '#main {' . $main . " width:auto; float:none; }\n" : '' );
            if ( $wrapper || $header || $customheader || $footer || $customfooter || $left || $right || $main )
            {
                $layout[ 'layoutcss' ] = <<<EOF
<style type="text/css" media="screen">
<!--/*--><![CDATA[/*><!--*/
{$wrapper}{$header}{$customheader}{$footer}{$customfooter}{$left}{$right}{$main}
/*]]>*/-->
</style>
EOF;
            }

            $layout[ 'doctype_html' ] = $this->getDocumentTypeHeader( $layout[ 'doctype' ] ) . "\n";

            $xhtmlVersion = 0;

            // Setting <html> tag attributes:
            $htmlTagAttributes = array();
            if ( stripos( $layout[ 'doctype' ], 'xhtml' ) !== false )
            {
                $match = array();
                preg_match( '/DTD XHTML ([^\/]+)/is', $layout[ 'doctype_html' ], $match );

                if ( $match[ 1 ] )
                {
                    $xhtmlVersion = (int)trim( str_replace( '.', '', $match[ 1 ] ) );
                }

                $layout[ 'html' ][ 'xmlns' ]   = 'http://www.w3.org/1999/xhtml';
                $layout[ 'html' ][ 'xmllang' ] = 'en';
                if ( $xhtmlVersion < 11 )
                {
                    $layout[ 'html' ][ 'lang' ] = 'en';
                }
            }


            if ( HTTP::input( 'print' ) )
            {
                $url = REQUEST;
                if ( substr( $url, 0, 5 ) === 'apps/' )
                {
                    $url = substr( $url, 5 );
                }
                $url = preg_replace( '#(?|&)print=([^&]*)#i', '', $url );

                $layout[ 'documenttitle' ] = self::$currentPageTitle;
                $layout[ 'documenturl' ]   = Settings::get( 'portalurl' ) . '/' . $url;
                $layout[ 'template' ]      = 'print';
            }


            $data[ 'layout' ] = $layout;

            Library::disableErrorHandling();
            //  $this->_parser->enableModCheck();
            $data[ 'layout' ]     = $layout;
            $layout[ 'headtags' ] = $this->_parser->get( $layout[ 'headtags' ], $data );
            Library::enableErrorHandling();


            if ( stripos( $layout[ 'doctype' ], 'html_5' ) !== false && stripos( $layout[ 'template' ], 'html5' ) === false )
            {
                $layout[ 'template' ] = $layout[ 'template' ] . '_html5';
            }

            if ( $layout[ 'template' ] !== '' && is_file( DATA_PATH . 'layouts/' . $layout[ 'template' ] . '.html' ) )
            {
                /**
                 * Compile the layout template first
                 */
                if ( !file_exists( CACHE_PATH . 'layout/' . $layout[ 'template' ] . '-styled.html' ) )
                {
                    $this->load( 'Layouter' );
                    $this->Layouter->init( $layout[ 'template' ] );

                    Library::disableErrorHandling();
                    $this->Layouter->loadStyleGuide();
                    $code = $this->Layouter->processStyle();
                    $this->Layouter->saveProcessedStyle( $code );
                    Library::enableErrorHandling();
                }

                $GLOBALS[ 'LAYOUT_TEMPLATE' ]      = $layout[ 'template' ] . '-styled.html';
                $GLOBALS[ 'LAYOUT_TEMPLATE_PATH' ] = CACHE_PATH . 'layout/';

                $layout[ 'template' ] = CACHE_PATH . 'layout/' . $layout[ 'template' ] . '-styled.html';

                #  die('1 '.$layout[ 'template' ]);
            }
            else
            {
                if ( stripos( $data[ 'layout' ][ 'doctype' ], 'html_5' ) !== false )
                { #die('2 '.$layout[ 'template' ]);
                    if ( strpos( $layout[ 'template' ], '_html5' ) === false )
                    {
                        $layout[ 'template' ] .= '_html5';
                    }

                    $layout[ 'template' ] = CACHE_PATH . 'layout/' . $layout[ 'template' ] . '-styled.html';
                    #   die('2 '.$layout[ 'template' ]);
                }
                else
                {

                    #  $layout[ 'template' ] = DATA_PATH . 'layouts/layout.html';


                    $layout[ 'template' ] = CACHE_PATH . 'layout/' . $layout[ 'template' ] . '-styled.html';
                    #  die('3 '.$layout[ 'template' ]);
                }

                if ( HTTP::get( 'print' ) )
                {
                    $url = REQUEST;
                    if ( substr( $url, 0, 5 ) === 'apps/' )
                    {
                        $url = substr( $url, 5 );
                    }
                    $url = preg_replace( '#(?|&)print=([^&]*)#i', '', $url );

                    $layout[ 'documenttitle' ] = self::$currentPageTitle;
                    $layout[ 'documenturl' ]   = Settings::get( 'portalurl' ) . '/' . $url;
                    $layout[ 'template' ]      = DATA_PATH . 'layouts/print.html';

                    $GLOBALS[ 'LAYOUT_TEMPLATE_PATH' ] = DATA_PATH . 'layouts/';
                    $GLOBALS[ 'LAYOUT_TEMPLATE' ]      = 'print.html';
                }
            }


            $data[ 'layout' ] = $layout;
        }


        if ( $this->isFrontend && is_array( self::$_loadExtraScripts ) )
        {


            if ( trim( $data[ 'layout' ][ 'headtags' ] ) )
            {
                preg_match_all( '#(<link[^>]*>)#isU', $data[ 'layout' ][ 'headtags' ], $matches );
                if ( isset( $matches[ 1 ] ) && $matches[ 1 ] )
                {
                    $tmp = '';
                    foreach ( $matches[ 1 ] as $str )
                    {
                        $layout[ 'headtags' ] = preg_replace( '#(<link[^>]*>)#isU', '', $data[ 'layout' ][ 'headtags' ], 1 );
                        $tmp .= "\n\t" . $str;
                    }
                    if ( !isset( $data[ 'layout' ][ 'stylesheets' ] ) )
                    {
                        $data[ 'layout' ][ 'stylesheets' ] = '';
                    }
                    $data[ 'layout' ][ 'stylesheets' ] = $tmp;
                }

                preg_match_all( '#(<script[^>]*>.*</script>)#isU', $data[ 'layout' ][ 'headtags' ], $matches );
                if ( isset( $matches[ 1 ] ) && $matches[ 1 ] )
                {
                    $tmp = '';
                    foreach ( $matches[ 1 ] as $str )
                    {
                        $data[ 'layout' ][ 'headtags' ] = preg_replace( '#(<script[^>]*>.*</script>)#isU', '', $data[ 'layout' ][ 'headtags' ], 1 );
                        $tmp .= "\n\t" . $str;
                    }
                    if ( !isset( $data[ 'layout' ][ 'javascripts' ] ) )
                    {
                        $data[ 'layout' ][ 'javascripts' ] = '';
                    }
                    $data[ 'layout' ][ 'javascripts' ] = $tmp;
                }
            }

            $stylesheets = '';
            $javascripts = '';

            if ( isset( self::$_loadExtraScripts[ 'css' ] ) )
            {
                foreach ( self::$_loadExtraScripts[ 'css' ] as $idx => $src )
                {
                    if ( $src )
                    {
                        $stylesheets .= "\n\t" . '<link href="' . $src . '" type="text/css" rel="stylesheet"/>';
                    }
                }
                if ( !isset( $data[ 'layout' ][ 'stylesheets' ] ) )
                {
                    $data[ 'layout' ][ 'stylesheets' ] = '';
                }
                $data[ 'layout' ][ 'stylesheets' ] .= $stylesheets;

                $stylesheets = '';
            }


            if ( isset( self::$_loadExtraScripts[ 'js' ] ) )
            {
                foreach ( self::$_loadExtraScripts[ 'js' ] as $idx => $src )
                {
                    if ( $src )
                    {
                        $javascripts .= '<script src="' . $src . '" type="text/javascript"></script>';
                    }
                }

                if ( !isset( $data[ 'layout' ][ 'javascripts' ] ) )
                {
                    $data[ 'layout' ][ 'javascripts' ] = '';
                }

                $data[ 'layout' ][ 'javascripts' ] .= $javascripts;
                $javascripts = '';
            }
        }


        $this->_data[ 'layout' ] =& $data[ 'layout' ];


        return $data;
    }

    public function mergeJavascripts()
    {
        $tmp = array();
        if ( is_array( self::$_loadExtraScripts[ 'js' ] ) && count( self::$_loadExtraScripts[ 'js' ] ) )
        {

            foreach ( self::$_loadExtraScripts[ 'js' ] as $idx => $src )
            {
                if ( $src && !preg_match( '#^https?://#is', $src ) )
                {
                    $tmp[ ] = $src;
                    unset( self::$_loadExtraScripts[ 'js' ][ $idx ] );
                }
            }
        }

        if ( count( $tmp ) )
        {
            self::$_loadExtraScripts[ 'js' ][ ] = 'asset/js/' . implode( ',', $tmp );
        }
    }

    /**
     * load all Javascripts
     *
     * @return type
     *
     * @todo Remove this function
     *
     */
    protected function _initBackendScripts()
    {
        if ( !is_null( $this->javascripts ) )
        {
            return;
        }

        //$js_code = urlencode(JS_URL . 'jquery/jquery');
        //$js_code .= ',' . urlencode(JS_URL . 'jquery/jquery_ui');
        $js_code = '' . urlencode( JS_URL . 'jquery/jquery.url' );
        $js_code .= ',' . urlencode( JS_URL . 'jquery/jquery.alert' );
        $js_code .= ',' . urlencode( JS_URL . 'jquery/jquery.field' );
        #$js_code .= ',' . urlencode(JS_URL . 'jquery/interface');
        //$js_code .= ',' . urlencode(JS_URL . 'jquery/fancybox/jquery.mousewheel-3.0.2.pack');
        $js_code .= ',' . urlencode( JS_URL . 'jquery/fancybox/jquery.fancybox-1.3.1' );
        $js_code .= ',' . urlencode( BACKEND_JS_URL . 'forms_save_actions' );

        $js_code .= ',' . urlencode( BACKEND_JS_URL . 'dcms.core' );

        #   if (!isset($GLOBALS['isLoginScreen']))
        #    {
        $js_code .= ',' . urlencode( substr( VENDOR_URL_PATH, 3 ) . 'tinymce/jquery.tinymce' );
        #    }

        if ( !isset( $GLOBALS[ 'isLoginScreen' ] ) )
        {
            $js_code .= ',' . urlencode( BACKEND_JS_URL . 'dcms.toolbar' );
            $js_code .= ',' . urlencode( BACKEND_JS_URL . 'dcms.toolbartabs' );
            $js_code .= ',' . urlencode( BACKEND_JS_URL . 'dcms.tree' );
        }


        $js_code .= ',' . urlencode( BACKEND_JS_URL . 'global' );
        $js_code .= ',' . urlencode( 'admin.php?tinymce=getconfig' );
        $js_code .= ',' . urlencode( BACKEND_JS_URL . 'dcms.gui' );

        if ( empty( $GLOBALS[ 'isLoginScreen' ] ) )
        {
            $js_code .= ',' . urlencode( JS_URL . 'jquery/jquery.contextMenu' );
            $js_code .= ',' . urlencode( JS_URL . 'jquery/jquery.tooltip' );

            $js_code .= ',' . urlencode( BACKEND_JS_URL . 'dcms.tabs' );
            $js_code .= ',' . urlencode( BACKEND_JS_URL . 'dcms.forms' );
            // $js_code .= ',' . urlencode(BACKEND_JS_URL . 'dcms.grid'); // old

            $js_code .= ',' . urlencode( JS_URL . 'jquery/dragtable/js/jquery.ui.mouse' );
            $js_code .= ',' . urlencode( JS_URL . 'jquery/dragtable/js/jquery.dragtable' );

            ##########           $js_code .= ',' . urlencode(BACKEND_JS_URL . 'dcms.griddata');
            $js_code .= ',' . urlencode( BACKEND_JS_URL . 'swfupload/swfupload' );
            $js_code .= ',' . urlencode( BACKEND_JS_URL . 'upload_multi' );
            // $js_code .= ',' . urlencode(BACKEND_JS_URL . 'dcmsfileman');
            // $js_code .= ',' . urlencode(BACKEND_JS_URL . 'dcms.filetree');
            //$js_code .= ',' . urlencode(BACKEND_JS_URL . 'dcms.adminbox');
            // $js_code .= ',' . urlencode(BACKEND_JS_URL . 'side_toolbar');
            $js_code .= ',' . urlencode( BACKEND_JS_URL . 'dcms.pages-sidebar-tree' );
            $js_code .= ',' . urlencode( JS_URL . 'jquery/jquery.validation' );
            $js_code .= ',' . urlencode( JS_URL . 'dcms.livesearch' );
            #$js_code .= ',' . urlencode(BACKEND_JS_URL . 'dcms.messenger');
        }
        else
        {
            #$this->addPreloaderSrc(JS_URL . 'jquery/jquery_ui', 'jQuery UI');
            $this->addPreloaderSrc( JS_URL . 'jquery/jquery.alert', 'jQuery Alert' );
            $this->addPreloaderSrc( JS_URL . 'jquery/jquery.field', 'jQuery Fields' );
            $this->addPreloaderSrc( JS_URL . 'jquery/interface', 'jQuery Interface' );
            $this->addPreloaderSrc( JS_URL . 'jquery/fancybox/jquery.mousewheel-3.0.2.pack', 'jQuery Mousewheel' );
            $this->addPreloaderSrc( JS_URL . 'jquery/fancybox/jquery.fancybox-1.3.1', 'jQuery Fancybox' );


            $this->addPreloaderSrc( BACKEND_JS_URL . 'dcms.core', 'DreamCMS Core' );
            $this->addPreloaderSrc( BACKEND_JS_URL . 'global', 'DreamCMS Main Scripts' );

            $this->addPreloaderSrc( JS_URL . 'jquery/jquery.contextMenu', 'DreamCMS Context Menu' );
            $this->addPreloaderSrc( JS_URL . 'jquery/jquery.tooltip', 'DreamCMS Tooltips' );

            $this->addPreloaderSrc( BACKEND_JS_URL . 'dcms.toolbar', 'DreamCMS Toolbar' );
            $this->addPreloaderSrc( BACKEND_JS_URL . 'dcms.toolbartabs', 'DreamCMS Toolbar Tabs' );
            $this->addPreloaderSrc( BACKEND_JS_URL . 'forms_save_actions', 'DreamCMS Form Actions' );
            $this->addPreloaderSrc( BACKEND_JS_URL . 'dcms.forms', 'DreamCMS Form Handler' );
            $this->addPreloaderSrc( BACKEND_JS_URL . 'swfupload/swfupload', 'DreamCMS SWF Upload' );
            $this->addPreloaderSrc( BACKEND_JS_URL . 'upload_multi', 'DreamCMS Multiuploader' );
            $this->addPreloaderSrc( BACKEND_JS_URL . 'dcmsfileman', 'DreamCMS Filemanager' );

            $this->addPreloaderSrc( BACKEND_JS_URL . 'dcms.filetree', 'DreamCMS FileTree' );
            $this->addPreloaderSrc( BACKEND_JS_URL . 'dcms.grid', 'DreamCMS Grid (OLD)' );
            $this->addPreloaderSrc( BACKEND_JS_URL . 'dcms.griddata', 'DreamCMS Grid' );

            $this->addPreloaderSrc( EXTERNAL_URL_PATH . 'tinymce/jquery.tinymce', 'TinyMCE' );

            //$this->addPreloaderSrc(BACKEND_JS_URL . 'dcms.adminbox', 'DreamCMS adminbox');
            //$this->addPreloaderSrc(BACKEND_JS_URL . 'side_toolbar', 'DreamCMS Site Toolbar');
            //$js_code .= ',' . urlencode(BACKEND_JS_URL . 'splashScreen');
        }


        $files = explode( ',', $js_code );


        #print_r($files);

        $errors = array();

        $js_code = '';
        $files   = array_unique( $files );

        $this->jsBootCalls = array();
        foreach ( $files as $idx => $v )
        {
            if ( empty( $v ) )
            {
                continue;
            }

            $js_code .= ( $js_code ? ',' : '' ) . urlencode( $v );
            $_s                   = explode( '/', urldecode( $v ) );
            $_data                = array(
                'src'  => urldecode( $v ),
                'name' => array_pop( $_s ));
            $this->jsBootCalls[ ] = $_data;
        }


        if ( isset( self::$_loadExtraScripts[ 'js' ] ) && count( self::$_loadExtraScripts[ 'js' ] ) )
        {
            foreach ( self::$_loadExtraScripts[ 'js' ] as $src )
            {
                if ( empty( $src ) )
                {
                    continue;
                }
                $_s    = explode( '/', urldecode( $src ) );
                $_data = array(
                    'src'  => $src,
                    'name' => array_pop( $_s ));

                $this->jsBootCalls[ ] = $_data;
            }
        }

        if ( isset( self::$_loadExtraScripts[ 'css' ] ) && count( self::$_loadExtraScripts[ 'css' ] ) )
        {
            foreach ( self::$_loadExtraScripts[ 'css' ] as $src )
            {
                if ( empty( $src ) )
                {
                    continue;
                }

                $_data = array(
                    'src'  => $src,
                    'type' => 'css');

                $this->cssBootCalls[ ] = $_data;
            }
        }


        if ( is_array( $this->js_compress ) )
        {
            $files = array_unique( $this->js_compress );
            foreach ( $files as $idx => $v )
            {
                $v = str_replace( 'html/js/', '', $v );

                if ( empty( $v ) || !file_exists( PUBLIC_PATH . $v . '.js' ) )
                {
                    $errors[ ] = PUBLIC_PATH . $v . '.js';
                    continue;
                }

                $this->jsBootCalls[ ][ 'src' ] = $v;
                $js_code .= ( $js_code ? ',' : '' ) . urlencode( $v );
            }
        }

        unset( $files );
        $this->js_compress = array();


        return '<script type="text/javascript" src="admin.php?adm=asset&action=js&file=' . $js_code . '"></script>';
    }

    /**
     *
     * @return string
     */
    protected function _initDynamicScripts()
    {
        if ( !count( $this->dynJs ) )
        {
            return;
        }

        $js_code     = '';
        $php_srcipts = array();
        if ( is_array( $this->dynJs ) )
        {
            $files = array_unique( $this->dynJs );
            foreach ( $files as $idx => $v )
            {
                if ( !file_exists( PUBLIC_PATH . $v . '.js' ) && !file_exists( PUBLIC_PATH . $v ) )
                {
                    continue;
                }

                if ( substr( $v, -4 ) == '.php' )
                {
                    $php_srcipts[ ] = '<script id="dynjs" type="text/javascript" src="' . $v . '"></script>';
                }
                else
                {
                    $js_code .= ( $js_code ? ',' : '' ) . urlencode( $v );
                }
                //$js_code .= '<script id="dynjs" type="text/javascript" src="admin.php?adm=loadjs&js=' . urlencode($v) . '"></script>';
                //$js_code .= ( $js_code ? ',' : '') . urlencode($v);
            }
        }

        if ( !$js_code && !count( $php_srcipts ) )
        {
            return '';
        }

        return implode( '', $php_srcipts ) . ( $js_code ? '<script id="dynjs" type="text/javascript" src="admin.php?adm=asset&jsfile=' . $js_code . '"></script>' : '' );
    }

    /**
     *
     * @param type $url
     * @param string|\type $title
     * @param string|\type $type $type
     */
    private function addPreloaderSrc($url, $title = '', $type = 'js')
    {
        $this->PreloaderSrc[ ] = array(
            'src'   => $url . '.' . $type,
            'title' => $title,
            'type'  => $type);
    }

    /**
     * @return string
     */
    private function buildVersioning()
    {
        $output  = '';
        $request = '';
        $hidden  = '';

        HTTP::unsetRequest( 'setVersion' );
        HTTP::unsetRequest( 'changeVersion' );

        foreach ( Http::input() as $k => $v )
        {
            if ( is_array( $v ) || $k == 'setVersion' || $k == 'changeVersion' )
            {
                continue;
            }

            $request .= ( $request ? '&amp;' : '' ) . $k . '=' . $v;
            $hidden .= '<input type="hidden" name="' . $k . '" value="' . $v . '"/>';
        }

        if ( is_array( Library::$versionRecords ) && count( Library::$versionRecords ) )
        {
            $request = ( $request ? '?' . $request : '' );
            $output  = '<form action="admin.php" id="VersioningForm" method="post">' . $hidden;
            $output .= '<select name="setVersion" id="setVersion" class="nodirty">';

            foreach ( Library::$versionRecords as $r )
            {
                $sel = ( $r[ 'current' ] ? ' selected="selected"' : '' );
                $output .= '<option value="' . $r[ 'version' ] . '"' . $sel . '>Version ' . $r[ 'version' ] . ' (' . date( 'd.m.Y, H:i:s', $r[ 'timestamp' ] ) . ') - ' . $r[ 'username' ] . '</option>';
            }

            $output .= '</select>
                
            <button name="changeVersion" id="changeVersion" type="button" class="changeVersion">
                <span class="icn"></span>
                <span class="label">' . trans( 'Wiederherstellen' ) . '</span>
            </button>
            <button name="diffVersion" id="diffVersion" type="button" class="diffVersion">
                <span class="icn"></span>
                <span class="label">' . trans( 'Versions Unterschiede' ) . '</span>
            </button>
    </form>';
        }


        return $output;
    }


    /**
     * @return array
     */
    private function getBasicConfig()
    {

        $config = Settings::getAll();
        unset( $config[ 'smtp_server' ], $config[ 'smtp_port' ], $config[ 'smtp_port' ], $config[ 'smtp_user' ], $config[ 'smtp_password' ], $config[ 'cli_key' ], $config[ 'frommail' ], $config[ 'disclaimer_text' ], $config[ 'crypt_key' ] );
        $_conf                              = Locales::getLocaleByLang( CONTENT_TRANS );
        $config[ 'backend_css_url' ]        = BACKEND_CSS_PATH;
        $config[ 'backendImagePath' ]       = BACKEND_IMAGE_PATH;
        $config[ 'js_url' ]                 = JS_URL;
        $config[ 'css_url' ]                = BACKEND_CSS_PATH;
        $config[ 'IS_AJAX' ]                = IS_AJAX;
        $config[ 'is_ajax' ]                = IS_AJAX;
        $config[ 'version' ]                = VERSION;
        $config[ 'cookieprefix' ]           = COOKIE_PREFIX;
        $config[ 'app_path' ]               = APP_PATH;
        $config[ 'data_path' ]              = DATA_PATH;
        $config[ 'html_path' ]              = HTML_URL;
        $config[ 'contenttranslationFlag' ] = $_conf[ 'flag' ];
        $config[ 'post_max_size' ]          = ini_get( 'post_max_size' );
        $config[ 'upload_max_filesize' ]    = ini_get( 'upload_max_filesize' );
        $config[ 'max_file_uploads' ]       = ini_get( 'max_file_uploads' );


        if ( !User::isLoggedIn() )
        {
            $data = array(
                'sysconfig'   => $config,
                'splashinfos' => $this->getSplashscreenInfos(),
            );

        }
        else
        {
            $data = array(
                'sysconfig' => $config
            );

            $_conf                              = Locales::getLocaleByLang( CONTENT_TRANS );
            $config[ 'contenttranslationFlag' ] = $_conf[ 'flag' ];

            $udata = User::getUserData();

            unset( $udata[ 'password' ], $udata[ 'uhash' ], $udata[ 'uniqidkey' ], $udata[ 'usertext' ], $udata[ 'signature' ], $udata[ 'permissions' ], $udata[ 'specialperms' ] );


            if ( BACKEND_SKIN_ISWINDOWED )
            {
                $default = array(
                    'dockposition' => 'center',
                    'dockautohide' => false,
                    'mintoappicon' => false,
                    'dockHeight'   => 40,
                    'activeItems'  => array(),
                    'dockItems'    => array()
                );

                $personal         = new Personal;
                $personalsettings = User::getPersonalSettings();

                if ( !$personalsettings[ 'desktopbackground' ] || !isset( $personalsettings[ 'desktopbackground' ] ) )
                {
                    $personalsettings[ 'desktopbackground' ] = 'galaxy.jpg';
                }


                $personaldata        = $personal->get( 'dock', 'settings', $default );
                $personaldataIcs     = $personal->get( 'desktop', 'icons', array() );
                $personaldataFolders = $personal->get( 'desktop', 'folders', array() );

                foreach ( $personaldataIcs as $k => &$r )
                {
                    if ( !in_array( $k, array(
                        'iconGutterSize',
                        'iconLabelPos',
                        'iconWidth',
                        'subIconWidth',
                        'iconSort',
                        'showObjectInfo'
                    ) )
                    )
                    {
                        if ( $r[ 'controller' ] )
                        {


                            if ( $r[ 'controller' ] != 'plugin' && !preg_match( '#plugin=([a-z0-9]+)#i', $r[ 'url' ] ) )
                            {
                                $reg = $this->getApplication()->getModulRegistry( $r[ 'controller' ] );

                                if ( isset( $reg[ 'definition' ] ) )
                                {
                                    if ( isset( $reg[ 'definition' ][ 'version' ] ) )
                                    {
                                        $r[ 'version' ] = $reg[ 'definition' ][ 'version' ];
                                    }
                                    if ( isset( $reg[ 'definition' ][ 'moduledescription' ] ) && $reg[ 'definition' ][ 'moduledescription' ] )
                                    {
                                        $r[ 'moduledescription' ] = $reg[ 'definition' ][ 'moduledescription' ];
                                    }


                                    $r[ 'bytesize' ] = Library::dirSize( MODULES_PATH . ucfirst( $r[ 'controller' ] ) . '/' );
                                    $r[ 'size' ]     = Library::formatSize( $r[ 'bytesize' ] );
                                }
                            }
                            else
                            {
                                if ( $r[ 'url' ] )
                                {
                                    $n = array();
                                    preg_match( '#plugin=([a-z0-9]+)#i', $r[ 'url' ], $n );

                                    if ( $n[ 1 ] )
                                    {
                                        $this->load( 'Plugin' );
                                        $this->Plugin->initPlugin( Plugin::getConfig( $n[ 1 ] ), $n[ 1 ] );
                                        $reg = $this->Plugin->getDefinition();

                                        if ( is_array( $reg ) )
                                        {
                                            if ( isset( $reg[ 'definition' ] ) )
                                            {
                                                if ( isset( $reg[ 'definition' ][ 'version' ] ) )
                                                {
                                                    $r[ 'version' ] = $reg[ 'definition' ][ 'version' ];
                                                }
                                                if ( isset( $reg[ 'definition' ][ 'moduledescription' ] ) && $reg[ 'definition' ][ 'moduledescription' ] )
                                                {
                                                    $r[ 'moduledescription' ] = $reg[ 'definition' ][ 'moduledescription' ];
                                                }

                                                $r[ 'bytesize' ] = Library::dirSize( MODULES_PATH . ucfirst( $r[ 'controller' ] ) . '/' );
                                                $r[ 'size' ]     = Library::formatSize( $r[ 'bytesize' ] );
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            list( $plugins, $toolbar_output, $_toolbars ) = Tinymce::getTinyMceToolbars();

            $data = array(
                'sysconfig'        => $config,
                'userdata'         => $udata,
                'personalsettings' => $personalsettings,
                'dock'             => $personaldata,
                'desktopicons'     => $personaldataIcs,
                'desktopfolders'   => $personaldataFolders,
                'splashinfos'      => $this->getSplashscreenInfos(),
                'tinymce'          => array_merge( array(
                    'plugins'     => $plugins,
                    'language'    => CONTENT_TRANS,
                    'content_css' => Tinymce::getContentCss(),
                    'templates'   => Tinymce::getContentTemplates(),
                ), $_toolbars )
            );
        }


        $data[ 'sid' ] = session_id();

        return $data;

    }

    /**
     * init all basic backend data
     *
     */
    public function loadBackendData($returns = false)
    {
        if ( $this->_dataLoaded )
        {
            return;
        }


        $this->_data[ 'session_id' ]         = session_id();
        $this->_data[ 'pagepath' ]           = 'pages/' . SERVER_PAGE;
        $this->_data[ 'backend_css_url' ]    = BACKEND_CSS_PATH;
        $this->_data[ 'backendImagePath' ]   = BACKEND_IMAGE_PATH;
        $this->_data[ 'cfg' ]                = Settings::getAll();
        $this->_data[ 'user' ]               = User::initUserData();
        $this->_data[ 'user' ][ 'is_admin' ] = User::isAdmin();
        $this->_data[ 'js_url' ]             = JS_URL;
        $this->_data[ 'css_url' ]            = BACKEND_CSS_PATH;
        $this->_data[ 'IS_AJAX' ]            = IS_AJAX;
        $this->_data[ 'is_ajax' ]            = IS_AJAX;
        $this->_data[ 'version' ]            = VERSION;
        $this->_data[ 'cookieprefix' ]       = COOKIE_PREFIX;
        $this->_data[ 'app_path' ]           = APP_PATH;
        $this->_data[ 'data_path' ]          = DATA_PATH;
        $this->_data[ 'html_path' ]          = HTML_URL;

        $this->load( 'Personal' );


        $sidebar = $this->Personal->get( "sidebar", 'pos' );


        $this->_data[ 'sidebar' ]             = array();
        $this->_data[ 'sidebar' ][ 'height' ] = isset( $sidebar[ 'height' ] ) ? $sidebar[ 'height' ] : 0;
        $this->_data[ 'sidebar' ][ 'width' ]  = isset( $sidebar[ 'width' ] ) && $sidebar[ 'width' ] > 0 ? $sidebar[ 'width' ] : 200;
        $this->_data[ 'sidebar' ][ 'panel' ]  = isset( $sidebar[ 'panel' ] ) ? $sidebar[ 'panel' ] : '';

        $this->_data[ 'js_code' ] = $this->_initBackendScripts() . ( isset( $GLOBALS[ 'EDITOR_JS' ] ) ? $GLOBALS[ 'EDITOR_JS' ] : '' );

        $this->_data[ 'uploadsize' ]  = preg_replace( '/\s*([A-Z]+)$/i', '', ini_get( 'upload_max_filesize' ) );
        $this->_data[ 'uploadlimit' ] = ( $this->_data[ 'uploadsize' ] * 1024 );

        $this->_data[ 'fileman_view_mode' ] = isset( $GLOBALS[ 'filemanager_viewmode' ][ 'mode' ] ) ? $GLOBALS[ 'filemanager_viewmode' ][ 'mode' ] : '';

        $this->_data[ 'cmsurl_jsregex' ] = '/' . strtolower( str_replace( '/', '\/', Settings::get( 'portalurl' ) ) ) . '\//gi';
        $this->_data[ 'debug_dbquerys' ] = '<div class="debug_dbquerys"><small>SQL Querys: ' . Database::getInstance()->query_count . '</small></div>';

        $timeout                         = Session::get( "expiry" );
        $this->_data[ 'session_expiry' ] = sprintf( trans( 'Session Ende: %s' ), date( 'd.m.Y, H:i', $timeout ) );


        $this->_data[ 'versioning' ] = $this->buildVersioning();
        $this->_data[ 'menu' ]       = Menu::getMenu();

        $this->_data = array_merge( $this->_data, $this->getBasicConfig() );


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

        $this->_data[ 'pageTitle' ]        = $title;
        $this->_data[ 'pageCurrentTitle' ] = $currentTitle;
        $this->_data[ 'pageCurrentIcon' ]  = Cookie::get( 'pageCurrentIcon' );

        if ( isset( $GLOBALS[ 'isLoginScreen' ] ) )
        {
            $this->_data[ 'islogin' ]   = true;
            $this->_data[ 'preloader' ] = $this->PreloaderSrc;
        }

        #$this->load('Personal');
        $this->_data[ 'toolbarTabs' ] = trim( $this->Personal->get( "toolbarTabs", 'tabs' ) );
        #$this->Personal = null;


        $this->_dataLoaded = true;
    }

    /**
     * @return array
     */
    public function getSplashscreenInfos()
    {
        static $splashData;

        if ( !is_array( $splashData ) )
        {
            $splashData = array();

            $reg = $this->getApplication()->getModulRegistry();
            foreach ( $reg as $modul => $r )
            {
                if ( isset( $r[ 'definition' ] ) )
                {
                    $o                    = $r[ 'definition' ];
                    $splashData[ $modul ] = array('modulelabel' => $o[ 'modulelabel' ],
                                                  'version' => isset($o[ 'version' ]) ? $o[ 'version' ] : '',
                                                  'copyright' => isset($o[ 'copyright' ]) ? $o[ 'copyright' ] : '');
                }
            }

            $plugins = Plugin::getInstalledPlugins();
            foreach ( $plugins as $r )
            {
                if ( $r[ 'run' ] && $r[ 'published' ] )
                {
                    $def = Plugin::getPluginDefinition( $r[ 'key' ] );
                    if ( is_array( $def ) && isset( $def[ 'modulelabel' ] ) )
                    {
                        $splashData[ $r[ 'key' ] ] = array(
                            'plugin'      => true,
                            'modulelabel' => $def[ 'modulelabel' ],
                            'version'     => isset( $def[ 'version' ] ) ? $def[ 'version' ] : '1.0',
                            'copyright'      => isset( $def[ 'author' ] ) ? $def[ 'author' ] : null
                        );
                    }
                }
            }
        }

        return $splashData;
    }

    /**
     * Backend helper functions
     *
     */
    protected function prepareBackendTemplate($output)
    {


        $match = array();
        preg_match_all( "!<textarea[^>]+>.*</textarea>!isSU", $output, $match );
        $_area_blocks = $match[ 0 ];
        $output       = preg_replace( "!<textarea[^>]+>.*</textarea>!isSU", '@@@TRIM:AREA@@@', $output );

        $output = preg_replace( '!id=([\'|"]?)button\\1!iS', 'class="button" ', $output );
        $output = str_replace( 'src="images/', 'src="' . BACKEND_IMAGE_PATH . '', $output );
        $output = str_replace( 'src="core/admin/images/', 'src="' . BACKEND_IMAGE_PATH . '', $output );
        $output = str_replace( '"admin/js/', '"' . BACKEND_JS_URL, $output );
        $output = str_replace( '"skins/', '"core/admin/skins/', $output );

        if ( is_array( $_area_blocks ) )
        {
            // replace script blocks
            foreach ( $_area_blocks as $curr_block )
            {
                preg_match( "!(class=)!is", $curr_block, $m );
                if ( isset( $m[ 1 ] ) && $m[ 1 ] === '' )
                {
                    $curr_block = str_replace( "name=", 'class="textarea" name=', $curr_block );
                }

                $output = preg_replace( "!@@@TRIM:AREA@@@!SU", $curr_block, $output, 1 );
            }
            unset( $_area_blocks );
        }

        $str = $output;

        $this->addRollback = false;


        /**
         * prepare content edit form buttons
         */
        // suche save buttons
        preg_match_all( "!(\[save_exit:([^\]]*)\])!isU", $str, $match );
        $_blocks = $match[ 2 ];

        $has_ajax_buttons = false;

        if ( is_array( $_blocks ) )
        {

            $str = preg_replace( "!(\[save_exit:[^\]]*\])!is", '@@@TRIM:SEB@@@', $str );
            foreach ( $_blocks as $formnumber => $current )
            {
                $this->addRollback = true;
                $str               = preg_replace( "!@@@TRIM:SEB@@@!", '<button rel="' . $current . '" class="save_exit action-button btn btn-default"><span class="icn"></span><span class="label">' . trans( 'Speichern &amp; Beenden' ) . '</span></button>', $str, 1 );
            }
        }
        unset( $_blocks );

        // suche save buttons
        preg_match_all( "!(\[run_exit:([^\]]*)\])!isU", $str, $match );
        $_blocks = $match[ 2 ];

        if ( is_array( $_blocks ) )
        {
            $str = preg_replace( "!(\[run_exit:[^\]]*\])!isU", '@@@TRIM:RE@@@', $str );
            foreach ( $_blocks as $formnumber => $current )
            {
                $str = preg_replace( "!@@@TRIM:RE@@@!", '<button rel="' . $current . '" class="run_exit action-button btn btn-default"><span class="icn"></span><span class="label">' . trans( 'Ausführen &amp; Beenden' ) . '</span></button>', $str, 1 );
            }
        }
        unset( $_blocks );

        // suche save&exit buttons
        preg_match_all( "!(\[save:([^\]]*)\])!isU", $str, $match );
        $_blocks = $match[ 2 ];
        if ( is_array( $_blocks ) )
        {
            $str = preg_replace( "!(\[save:[^\]]*\])!isU", '@@@TRIM:SB@@@', $str );
            foreach ( $_blocks as $formnumber => $current )
            {
                $this->addRollback = true;
                $str               = preg_replace( "!@@@TRIM:SB@@@!", '<button rel="' . $current . '" class="save action-button btn btn-default"><span class="icn"></span><span class="label">' . trans( 'Speichern' ) . '</span></button>', $str, 1 );
            }
        }
        unset( $_blocks );

        preg_match_all( "!(\[run:([^\]]*)\])!isU", $str, $match );
        $_blocks = $match[ 2 ];
        if ( is_array( $_blocks ) )
        {
            $str = preg_replace( "!(\[run:[^\]]*\])!isU", '@@@TRIM:RB@@@', $str );
            foreach ( $_blocks as $formnumber => $current )
            {
                $str = preg_replace( "!@@@TRIM:RB@@@!", '<button rel="' . $current . '" class="run action-button btn btn-default"><span class="icn"></span><span class="label">' . trans( 'Ausführen' ) . '</span></button>', $str, 1 );
            }
        }
        unset( $_blocks );


        preg_match_all( "!(\[reset:([^\]]*)\])!isU", $str, $match );
        $_blocks = $match[ 2 ];
        if ( is_array( $_blocks ) )
        {
            $str = preg_replace( "!(\[reset:[^\]]*\])!isU", '@@@TRIM:RB@@@', $str );
            foreach ( $_blocks as $formnumber => $current )
            {

                $str = preg_replace( "!@@@TRIM:RB@@@!", '<button rel="' . $current . '" class="reset action-button btn btn-default"><span class="icn"></span><span class="label">' . trans( 'Zurücksetzen' ) . '</span></button>', $str, 1 );
            }
        }


        preg_match_all( "!(\[cancel:([^\]]*)\])!isU", $str, $match );
        $_blocks = $match[ 2 ];
        if ( is_array( $_blocks ) )
        {

            $str = preg_replace( "!(\[cancel:[^\]]*\])!isU", '@@@TRIM:RB@@@', $str );
            foreach ( $_blocks as $formnumber => $current )
            {
                $str = preg_replace( "!@@@TRIM:RB@@@!", '<button rel="' . $current . '" class="cancel action-button btn btn-default"><span class="icn"></span><span class="label">' . trans( 'Abbrechen' ) . '</span></button>', $str, 1 );
            }
        }


        /**
         * Add content draft button
         */
        if ( $this->hasDraftButton() )
        {
            $this->addRollback = true;

            $draftBtn = ' <button rel="%s" class="draft action-button btn btn-default"><span class="icn"></span><span class="label">' . trans( 'Pause' ) . '</span></button> ';
            preg_match( '/<button\s*rel="([^\"]+)"\s*class="save_exit[^>]*>/iU', $str, $match );

            Session::save( 'DraftLocation', array(
                $this->Env->location(),
                CONTROLLER,
                ACTION) );

            if ( !$match[ 1 ] )
            {

                preg_match( '/<button\s*rel="([^\"]+)"\s*class="save[^>]*>/iU', $str, $matchs );
                if ( $matchs[ 1 ] )
                {


                    $draftBtn = sprintf( $draftBtn, $matchs[ 1 ] );
                    $str      = preg_replace( '/(<button\s*rel="[^\"]+"\s*class="save[^>]*>.*<\/button>)/iU', '$1' . $draftBtn, $str, 1 );
                }
            }
            else
            {
                if ( $match[ 1 ] )
                {

                    $draftBtn = sprintf( $draftBtn, $match[ 1 ] );
                    $str      = preg_replace( '/(<button\s*rel="' . preg_quote( $match[ 1 ], '/' ) . '"\s*class="save_exit[^>]*>.*<\/button>)/iU', '$1' . $draftBtn, $str, 1 );
                }
            }
        }
        else
        {
            Session::delete( 'DraftLocation' );
        }


        $str = $this->replaceInfoIcons( $str );
        $str = $this->addCopyHeader( $str );
        $str = $this->prepareSSLUrls( $str );


        return $str;
    }

    /**
     * auto generate nox existing help files (XML)
     * used from backend
     *
     * @param type $contentid
     * @return type
     */
    private function check_info($contentid)
    {
        $header = '<?xml version="1.0" encoding="utf-8"?>' . "\r\n";

        $r      = explode( '|', $contentid );
        $r[ 0 ] = 'tooltip_' . $r[ 0 ];
        $path   = XMLDATA_PATH;

        if ( $r[ 1 ] )
        {
            if ( !file_exists( $path . $r[ 0 ] . '.xml' ) )
            {
                $dom                     = new DOMDocument( '1.0', 'UTF-8' );
                $dom->preserveWhiteSpace = false;
                $dom->formatOutput       = true;
                $dom->encoding           = 'UTF-8';
                $root                    = $dom->createElement( 'tooltip' );
                $dom->appendChild( $root );

                $item  = $dom->createElement( $r[ 1 ] );
                $title = $dom->createAttribute( 'title' );
                $item->appendChild( $title );
                $itemValue = $dom->createCDATASection( '' );
                $item->appendChild( $itemValue );
                $root->appendChild( $item );


                file_put_contents( $path . $r[ 0 ] . '.xml', utf8_encode( $dom->saveXML() ) );
            }
            else
            {
                $dom                     = new DOMDocument( '1.0', 'utf-8' );
                $dom->preserveWhiteSpace = false;

                $dom->Load( $path . $r[ 0 ] . '.xml' );
                $dom->formatOutput = true;


                $xpath = new DOMXPath( $dom );
                $root  = $xpath->query( '//tooltip' )->item( 0 );
                $node  = $xpath->query( '//tooltip/' . $r[ 1 ] )->item( 0 );
                if ( !is_object( $node ) )
                {
                    $item  = $dom->createElement( $r[ 1 ] );
                    $title = $dom->createAttribute( 'title' );
                    $item->appendChild( $title );

                    $itemValue = $dom->createCDATASection( '' );
                    $item->appendChild( $itemValue );
                    $root->appendChild( $item );


                    file_put_contents( $path . $r[ 0 ] . '.xml', $dom->saveXML() );
                }

                # print_r($_arr);
                # exit;
            }
        }

        return true;
    }

    /**
     *
     * @param int|string $contentid
     * @return string (the help icon)
     */
    private function _info($contentid = 0)
    {
        if ( !$contentid )
        {
            return '';
        }

        $this->check_info( $contentid );

        return '<img src="' . BACKEND_IMAGE_PATH . 'info.png" width="16" height="16" alt="' . $contentid . '" class="infoicon" />';
    }

    /**
     *
     * @param string $html_out
     * @param bool $replace
     * @return string
     */
    private function replaceInfoIcons($html_out, $replace = true)
    {
        if ( !$replace )
        {
            return preg_replace( "/{info:([^}]+?)}/ie", "", $html_out );
        }

        return preg_replace( "/{info:([^}]+?)}/ie", "\$this->_info('\\1')", $html_out );
    }

    /**
     * Prepare all internal urls if used SSL mode
     *
     * @param string $html_out
     * @return string
     */
    public function prepareSSLUrls($html_out)
    {

        if ( SSL_MODE !== true )
        {
            return $html_out;
        }

        $internalUrl = Settings::get( 'portalurl' );
        $sslInternal = preg_replace( '#https?://#i', 'https://', $internalUrl );

        $baseTag = Html::extractTags( $html_out, 'base', true, true );
        if ( is_array( $baseTag ) )
        {
            foreach ( $baseTag as $r )
            {
                $attr = $r[ 'attributes' ];

                if ( !isset( $attr[ 'href' ] ) )
                {
                    continue;
                }

                if ( strstr( $attr[ 'href' ], 'http://' ) or strstr( $attr[ 'href' ], 'https://' ) )
                {

                    $newTag   = preg_replace( '#(\shref\s*=\s*([\'"])https?://)#is', ' href=$2' . 'https://', $r[ 'full_tag' ] );
                    $html_out = str_replace( $r[ 'full_tag' ], $newTag, $html_out );
                }
                else
                {
                    $newTag   = preg_replace( '#(\shref\s*=\s*([\'"]))#i', ' href=$2' . $sslInternal . '/', $attr[ 'full_tag' ] );
                    $html_out = str_replace( $r[ 'full_tag' ], $newTag, $html_out );
                }
            }

            $baseTag = null;
        }


        $allTags = Html::extractTags( $html_out, 'a', null, true );
        if ( !is_array( $allTags ) )
        {
            return $html_out;
        }


        foreach ( $allTags as $r )
        {
            $attr = $r[ 'attributes' ];

            if ( $attr[ 'href' ] )
            {

                if ( preg_match( '#^(mailto:|javascript:|call:|\#)?#i', $attr[ 'href' ] ) )
                {
                    continue;
                }

                // skip external urls
                if ( ( strstr( $attr[ 'href' ], 'http://' ) || strstr( $attr[ 'href' ], 'https://' ) ) && Tools::isExternalUrl( $attr[ 'href' ], $internalUrl ) )
                {
                    continue;
                }

                if ( strstr( $attr[ 'href' ], 'http://' ) || strstr( $attr[ 'href' ], 'https://' ) )
                {

                    $newTag   = preg_replace( '#(\shref\s*=\s*([\'"])https?://)#is', ' href=$2' . 'https://', $r[ 'full_tag' ] );
                    $html_out = str_replace( $r[ 'full_tag' ], $newTag, $html_out );
                }
                else
                {
                    $newTag   = preg_replace( '#(\shref\s*=\s*([\'"]))#is', ' href=$2' . $sslInternal . '/', $r[ 'full_tag' ] );
                    $html_out = str_replace( $r[ 'full_tag' ], $newTag, $html_out );
                }
            }
        }
        unset( $allTags );

        return $html_out;
    }

    /**
     *
     * @param string $html_out
     * @return string
     */
    public function unModRewrite(&$html_out)
    {
        $allTags = Html::extractTags( $html_out, 'a', null, true );

        if ( !is_array( $allTags ) )
        {
            return $html_out;
        }

        $internalUrl = Settings::get( 'portalurl' );

        $this->load( 'Router' );


        foreach ( $allTags as $r )
        {
            $attr = $r[ 'attributes' ];

            if ( isset( $attr[ 'href' ] ) && !empty( $attr[ 'href' ] ) && !preg_match( '#\.php\?#', $attr[ 'href' ] ) )
            {

                if ( preg_match( '#^(mailto:|javascript:|call:|\#)#i', $attr[ 'href' ] ) )
                {
                    //die($attr[ 'href' ]);
                    continue;
                }

                // skip external urls
                if ( ( strstr( $attr[ 'href' ], 'http://' ) || strstr( $attr[ 'href' ], 'https://' ) ) && Tools::isExternalUrl( $attr[ 'href' ], $internalUrl ) )
                {

                    continue;
                }

                if ( strstr( $attr[ 'href' ], 'http://' ) || strstr( $attr[ 'href' ], 'https://' ) )
                {
                    $location = preg_replace( '#^((http)(s)?://([^/]*)+/)#', '$1', $attr[ 'href' ] );
                    $href     = preg_replace( '#^((http)(s)?://([^/]*)+/)#', '', $attr[ 'href' ] );

                    $newhref = $this->Router->routeToArgs( $href );


                    $newTag   = preg_replace( '#(\shref\s*=\s*([\'"])([^\2]*)\2)#is', ' href=$2' . $location . $newhref . '$2', $r[ 'full_tag' ] );
                    $html_out = str_replace( $r[ 'full_tag' ], $newTag, $html_out );
                }
                else
                {
                    $newhref = $this->Router->routeToArgs( $attr[ 'href' ] );


                    //die($newhref);
                    $newTag = preg_replace( '#(\shref\s*=\s*([\'"])([^\2]+)\2)#is', ' href=$2' . $newhref . '$2', $r[ 'full_tag' ] );

                    # die($newhref . $newTag);
                    $html_out = str_replace( $r[ 'full_tag' ], $newTag, $html_out );
                }
            }
        }
        # print_r($allTags);
        unset( $allTags );

        # exit;

        return $html_out;
    }

}

?>