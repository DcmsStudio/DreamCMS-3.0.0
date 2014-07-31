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
 * @file        ErrorHandler.php
 *
 */
class ErrorHandler
{

    /**
     * @var null
     */
    private static $instance = null;

    /**
     * @var int
     */
    private $maxTrace = 10;

    /**
     * @var array
     */
    private static $errorType = array(
        E_ERROR             => 'ERROR',
        E_WARNING           => 'WARNING',
        E_PARSE             => 'PARSING ERROR',
        E_NOTICE            => 'NOTICE',
        E_CORE_ERROR        => 'CORE ERROR',
        E_CORE_WARNING      => 'CORE WARNING',
        E_COMPILE_ERROR     => 'COMPILE ERROR',
        E_COMPILE_WARNING   => 'COMPILE WARNING',
        E_USER_ERROR        => 'USER ERROR',
        E_USER_WARNING      => 'USER WARNING',
        E_USER_NOTICE       => 'USER NOTICE',
        E_STRICT            => 'STRICT NOTICE',
        E_RECOVERABLE_ERROR => 'RECOVERABLE ERROR',
        E_DEPRECATED        => 'DEPRECATED ERROR'
    );

    /**
     * @var
     */
    private $_highlightArr;

    /**
     * @var bool
     */
    public $db = false;


    private $_line = null;
    private $_file = null;


    /**
     * @param $type
     * @return mixed
     */
    public static function convertError($type)
    {
        return ( isset( self::$errorType[ $type ] ) ? self::$errorType[ $type ] : $type );
    }


    /**
     * @return ErrorHandler|null
     */
    public static function getInstance()
    {
        if ( self::$instance === null )
        {
            self::$instance = new ErrorHandler();
            // self::$instance->db = Database::getInstance();
        }

        return self::$instance;
    }

    /**
     *
     * @param BaseException $exception The exception to be displayed.
     */
    public function display(BaseException $exception)
    {
        User::disableUserLocationUpdate();

        $len = ob_get_length();
        if ( $len )
        {
            $c = ob_get_contents();
            ob_clean();
        }

        if ( defined( 'SKIP_DEBUG' ) )
        {
            return;
        }

        $db = $this->db;

        if ( $db )
        {
            $dbError = $db->getError();
        }


        $message     = $exception->getMessage();
        $basemessage = $message;

        $file = ( $exception->_errorFile !== null ? $exception->_errorFile : ( $exception->getFile() ? $exception->getFile() : 'Unkown' ) );
        $line = ( $exception->_errorLine !== null ? $exception->_errorLine : ( $exception->getLine() ? $exception->getLine() : 'Unkown' ) );


        $this->_file = $file;
        $this->_line = $line;


        if ( $exception->_errorType == 'SQL' && strstr( $exception->getMessage(), 'SQLSTATE[' ) )
        {
            if ( isset( $dbError[ 2 ] ) )
            {
                $code    = $dbError[ 0 ] . "\n";
                $message = $dbError[ 2 ] . " \n<br/>" . $basemessage;
            }
        }
        elseif ( $exception->_errorType == 'SQL' )
        {
            preg_match( '/\[(\w0-9+)\]\s*(.*)/s', $exception->getMessage(), $matches );
            $code     = $matches[ 1 ] . "\n";
            $message2 = '';

            if ( isset( $dbError[ 2 ] ) )
            {
                $code     = $dbError[ 0 ] . "\n";
                $message2 = $dbError[ 2 ] . " \n<br/>" . $basemessage;
            }

            if ( $code )
            {
                $message = '<strong><span style="font-size:42px">&#9785;</span> Hmmmm... Database Error [' . $code . ']</strong><p>' . "\n" . $matches[ 2 ] . "\n" . $message2 . '</p>';
            }
        }
        else
        {
            $message = Debug::protectPath( $exception->getMessage() );
        }

        $data = array();


        if ( $exception->_errorType == 'PHP' )
        {
            $data[ 'code' ]  = Debug::source( $file, $line );
        }
        elseif ( $exception->_errorType == 'SQL' )
        {
            $error = Database::getInstance()->getError();
            $data['sql_line'] = 1;
            $data['sql_message'] = $error[2];
            if (preg_match('#at\sline\s*(\d+)\s*$#is', $error[2], $match)) {
                $data['sql_line'] = intval($match[1]);
            }
            $exception->_errorCode = $error[0];



        }
        elseif ( $exception->_errorType == 'JS' )
        {
            $output         = Library::syntaxHighlightCode( $exception->_errorCode, 'javascript' );
            $data[ 'code' ] = $output[ 'code' ];
            $data[ 'css' ]  = $output[ 'css' ];
            #    $data[ 'trace' ] = $this->getTraceCode( $exception );
            $output = null;
        }



        if ( defined( 'IS_AJAX' ) && IS_AJAX )
        {
            //$dbError = $db->getError();

            $trace = $this->_prepareTrace( $exception );
            $trace = array_reverse($trace);
            #array_shift( $trace );

            if ( ( $exception->_errorType == 'SQL' && !empty( $exception->_errorCode ) ) || isset( $dbError[ 1 ] ) )
            {
                $message = ( $dbError ? ' SQL Error: ' . "\n" . implode( ' ', $dbError ) . "\n" . $basemessage : $message );

                if ( $exception->sqlCode ) {
                    $message .= "\nSQL: ". $exception->sqlCode;
                }

                $timeout = TIMESTAMP;
            }
            else
            {
                $timeout = Session::get( "expiry" );
            }

            $file = $trace[ 0 ][ 'file' ]; // ( $exception->_errorFile ? $exception->_errorFile : ( $exception->getFile() ? $exception->getFile() : 'Unkown' ) );
            $line = $trace[ 0 ][ 'line' ]; // ( $exception->_errorLine ? $exception->_errorLine : ( $exception->getLine() ? $exception->getLine() : 'Unkown' ) );
            /*
              $data[ 'request' ] = Debug::debugContent( print_r( HTTP::input(), true ), (isset( $data[ 'line' ] ) ? $data[ 'line' ] : 0 ), 10 );

              if ( $exception->_errorType == 'PHP' )
              {
              #$data            = $this->buildPhpError( $exception );
              $data[ 'trace' ] = $this->getTraceCode( $exception );
              $data[ 'code' ]  = Debug::source( $file, $line );
              }
              elseif ( $exception->_errorType == 'SQL' )
              {
              $output          = Library::syntaxHighlightCode( $exception->_errorCode, 'sql' );
              $data[ 'code' ]  = $output[ 'code' ];
              $data[ 'css' ]   = $output[ 'css' ];
              $data[ 'trace' ] = $this->getTraceCode( $exception );
              }
              elseif ( $exception->_errorType == 'JS' )
              {

              $output          = Library::syntaxHighlightCode( $exception->_errorCode, 'javascript' );
              $data[ 'code' ]  = $output[ 'code' ];
              $data[ 'css' ]   = $output[ 'css' ];
              $data[ 'trace' ] = $this->getTraceCode( $exception );
              }




              if ( isset( $GLOBALS[ 'COMPILER_TEMPLATE' ] ) && $GLOBALS[ 'COMPILER_TEMPLATE' ] )
              {
              $errors = libxml_get_errors();

              if ( isset( $data[ 'message' ] ) )
              {

              $exception->_errorLine = $data[ 'line' ];
              if ( preg_match( '/\:([\d]+)\: parser error/', $data[ 'message' ], $l ) )
              {
              $errors[ 'line' ] = $l[ 1 ];
              }

              $_code = '';
              if ( intval( $errors[ 'line' ] ) )
              {
              $data[ 'line' ]        = intval( $errors[ 'line' ] );
              $exception->_errorLine = $data[ 'line' ];


              $lines = explode( "\n", $GLOBALS[ 'COMPILER_TEMPLATE' ] );

              for ( $i = ($data[ 'line' ] - 10); $i < ($data[ 'line' ] + 10); $i++ )
              {
              $_code .= $i . ': ' . $lines[ $i - 1 ] . "\n";
              }
              }

              $data[ 'message' ] = 'Template Compiler Error';
              $data[ 'code' ]    = Debug::debugContent( $GLOBALS[ 'COMPILER_TEMPLATE' ], $data[ 'line' ], 10 );
              $data[ 'css' ] .= $output[ 'css' ];

              $exception->_errorType == 'PHP';

              if ( $GLOBALS[ 'COMPILER_TEMPLATE_PATH' ] )
              {
              $exception->_errorFile = $GLOBALS[ 'COMPILER_TEMPLATE_PATH' ];
              }

              if ( $data[ 'trace' ] )
              {
              $data[ 'trace' ] = '<pre>' . $data[ 'trace' ] . '</pre>';
              }
              }
              }




              Library::log( (empty( $message ) ? $basemessage : strip_tags( $message, '<strong><p><br><em><span><pre>' ) ), 'error', $data );
             */

            $message = ( $message ? $message . '<br/>File: ' . $file . ' @Line: ' . $line : $basemessage . ' File: ' . $file . ' @Line: ' . $line );

            Library::log( strip_tags( $message ), 'error', null, $trace );


            echo Library::json( array(
                'success'              => false,
                'fatalError'           => true,
                'error'                => ( empty( $message ) ? $basemessage : strip_tags( $message, '<strong><p><br><em><span><pre>' ) ),
                'msg'                  => ( empty( $message ) ? $basemessage : strip_tags( $message, '<strong><p><br><em><span><br/>' ) ),
                'backtrace'            => trim( $this->getTraceCode( $exception, true, true ) ),
                // '_log'                 => preg_replace( '/<\/?([^>]*)>/', " ", $message ) . "\n\n" . strip_tags( $this->getTraceCode( $exception, true, true ) ),
                'session_expiry'       => sprintf( trans( 'Session Ende: %s' ), date( 'd.m.Y, H:i', $timeout ) ),
                'controllerperm'       => defined( 'CONTROLLER' ) ? Permission::hasControllerActionPerm( CONTROLLER ) : false,
                'controlleractionperm' => defined( 'ACTION' ) ? Permission::hasControllerActionPerm( CONTROLLER . '/' . ACTION ) : false
            ) );

            exit;
        }











        $data[ 'trace' ] = $this->getTraceCode( $exception );
        $data[ 'message' ] = strip_tags( $message, '<strong><p><br><em><span>' );
        $data[ 'request' ] = Debug::debugContent( print_r( HTTP::input(), true ), ( isset( $data[ 'line' ] ) ? $data[ 'line' ] : 0 ), 10 );

        if ( $exception->isCompilerError && $exception->_compilerErrorTemplate )
        {

            $highlightLine = null;

            if ( is_string( $exception->_compileXmlTag ) && $exception->_compileXmlTag )
            {
                $exception->_compileXmlTag = preg_replace('#\s*>$#', ' >', $exception->_compileXmlTag);
                if ( is_string( $exception->_compilerErrorTemplate ) )
                {
                    $lines = explode( "\n", $exception->_compilerErrorTemplate );


                    foreach ( $lines as $linenum => $l )
                    {
                        if ( preg_match('#'. preg_quote($exception->_compileXmlTag, '#').'#is', $l) )
                        {
                            $highlightLine = $linenum + 1;
                            break;
                        }
                    }
                }
            }

            if ( $highlightLine >= 0 )
            {

                $html   = Debug::sourceXmlString( $exception->_compilerErrorTemplate, $highlightLine, 10 );
                #$output = Library::syntaxHighlightCode( $exception->_compilerErrorTemplate, 'xml', $highlightLine);


                $data[ 'css' ] .= $html[ 'css' ];
                $data[ 'code_html' ]    = $html['code'];
                $data[ 'message' ] = 'Template Compiler Error: ' . $data[ 'message' ] . ( $exception->_compileTemplateName ? '<br/>in Template: ' . $exception->_compileTemplateName : '' ) . ' @Line: ' . ( $highlightLine  );
            }


            if ( $highlightLine === null )
            {

                $errors = libxml_get_errors();
                if ( sizeof( $errors ) )
                {

                    $_templateCode = '';
                    foreach ( $errors as $err )
                    {
                        if ( $err->line )
                        {
                            //$htmlstrip = Debug::debugContent( $exception->_compilerErrorTemplate, $err->line, 10 );
                            $output = Library::syntaxHighlightCode( $exception->_compilerErrorTemplate, 'xml' );
                            $data[ 'css' ] .= $output[ 'css' ];
                            $data[ 'code_html' ] = $output[ 'code' ];


                            $data[ 'message_html' ] = 'Template Compiler Error: ' . $data[ 'message' ] .
                                ( $exception->_compileTemplateName ? '<br/>in Template: ' . $exception->_compileTemplateName : '' );
                            $data[ 'message_html' ] .= '<br/>Line: ' . $err->line . '<br/>Column: ' . $err->column;

                            if ( $err->file )
                            {
                                $data[ 'message_html' ] .= '<br/>File: ' . $err->file;
                            }
                            $data[ 'message' ] = false;

                            break;
                        }
                    }

                }
                else
                {
                    $data[ 'message_html' ] = 'Template Compiler Error: ' . $data[ 'message' ] . ( $exception->_compileTemplateName ? '<br/>in Template: ' . $exception->_compileTemplateName : '' );
                    $data[ 'message' ]      = false;
                    $output                 = Debug::sourceXmlString( $exception->_compilerErrorTemplate, 'xml' );
                    $data[ 'css' ] .= $output[ 'css' ];
                    $data[ 'code_html' ] = $output[ 'code' ];

                }
            }
        }


        /*

                if ( isset( $GLOBALS[ 'COMPILER_TEMPLATE' ] ) && $GLOBALS[ 'COMPILER_TEMPLATE' ] )
                {


                    $errors = libxml_get_errors();

                    //die($data[ 'message' ] . ' '.$GLOBALS[ 'COMPILER_TEMPLATE' ]);


                    if ( isset( $data[ 'message' ] ) )
                    {

                        $errors[ 'line_html' ] = $data[ 'line' ];

                        if ( preg_match( '/\:([\d]+)\:\s{1,}parser\s{1,}error/s', $data[ 'message' ], $l ) )
                        {
                            $errors[ 'line_html' ] = $l[ 1 ];
                        }

                        $_code = '';
                        if ( intval( $errors[ 'line_html' ] ) )
                        {
                            $data[ 'line_html' ] = intval( $errors[ 'line_html' ] );
                            $exception->_errorLine = $data[ 'line_html' ];


                            $lines = explode( "\n", $GLOBALS[ 'COMPILER_TEMPLATE' ] );

                            for ( $i = ($data[ 'line' ] - 10); $i < ($data[ 'line' ] + 10); $i++ )
                            {
                                $_code .= $i . ': ' . $lines[ $i - 1 ] . "\n";
                            }

                            $data[ 'message_html' ] = 'Template Compiler Error';
                            $data[ 'code_html' ] = Debug::debugContent( $GLOBALS[ 'COMPILER_TEMPLATE' ], $data[ 'line_html' ], 10 );
                            $data[ 'css_html' ] .= $output[ 'css' ];

                            $exception->_errorType == 'PHP';

                            if ( $GLOBALS[ 'COMPILER_TEMPLATE_PATH' ] )
                            {
                                $exception->_errorFile = $GLOBALS[ 'COMPILER_TEMPLATE_PATH' ];
                            }

                            if ( $data[ 'trace' ] )
                            {
                                $data[ 'trace_html' ] = '<pre>' . $data[ 'trace' ] . '</pre>';
                            }

                        }


                    }
                }
        */





        Library::log( strip_tags( $data[ 'message' ] ), 'error', null, $this->_prepareTrace($exception) );



        $trace = $this->_prepareTrace( $exception );
        include DATA_PATH . 'system/exception_error.php';

        if ( $exception->_errorType != 'SQL' )
        {
            // $html .= Debug::write( false );
        }

        exit;
    }

    /**
     * @param BaseException $exception
     * @param $data
     * @return string
     */
    protected function getHeader(BaseException $exception, &$data)
    {
        $title = $exception->_exeptionTilte != '' ? $exception->_exeptionTilte . ' ' : '';

        if ( isset( $GLOBALS[ 'COMPILER_TEMPLATE' ] ) && $GLOBALS[ 'COMPILER_TEMPLATE' ] !== null )
        {
            $this->_highlightArr = Library::syntaxHighlightCode( $GLOBALS[ 'COMPILER_TEMPLATE' ], 'xml' );
        }
        $_css = ( !empty( $data[ 'css' ] ) ? $data[ 'css' ] : '' );
        $_css .= ( !empty( $data[ 'css_html' ] ) ? $data[ 'css_html' ] : '' );

        if ( isset( $this->_highlightArr[ 'css' ] ) )
        {
            $_css .= $this->_highlightArr[ 'css' ];
        }

        $portalurl = '';
        if ( $exception->_errorType != 'SQL' )
        {
            $portalurl = Settings::get( 'portalurl', '' );
        }
        $bt = Settings::get( 'portalurl', '' ) . '/public/' . BACKEND_CSS_PATH;


        return <<<EOF
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>{$title}Error</title>
<base href="{$portalurl}/" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="Content-Style-Type" content="text/css" />

<link rel="stylesheet" href="{$bt}dcms.error.css?_=1" type="text/css" />

<style type="text/css">
{$_css}

h2 {
    font-size: 14px;
    margin: 0;
    padding: 0;
    color: #333;
}
a {
    color: #333;
}

</style>

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.js"></script>

<script type="text/javascript">
    // <![CDATA[


function toggleTrace(elem)
{
    elem = document.getElementById(elem);

    if (elem.style && elem.style['display'])
            // Only works with the "style" attr
            var disp = elem.style['display'];
    else if (elem.currentStyle)
            // For MSIE, naturally
            var disp = elem.currentStyle['display'];
    else if (window.getComputedStyle)
            // For most other browsers
            var disp = document.defaultView.getComputedStyle(elem, null).getPropertyValue('display');

    // Toggle the state of the "display" style
    elem.style.display = disp == 'block' ? 'none' : 'block';
    // Win.refreshWindowScrollbars();
    return false;
}

//]]>
</script>

</head>
<body id="error-screen">

EOF;
    }

    /**
     *
     * @param BaseException $exception
     * @return string
     */
    protected function getFooter(BaseException $exception)
    {
        return <<<EOF

<script type="text/javascript">
    //<![CDATA[

    $(document).ready(function() {

        $('#back-button').click(function() {
            //if(window.opener) {
            //    window.close();
            //} else {
            history.go(-1);
            //}
        });

        $('#reload-button').click(function() {
            document.location.href = document.location.href;
        });


        $('.args', $('.trace')).find('span.array').each(function(){

            if ($(this).prev().prev().prev().is('small') ) {
                $(this).hide();

                var tag = $(this);
                $(this).prev().prev().click(function(e){
                    tag.toggle(0);
                    Win.refreshWindowScrollbars();
                    return false;
                });
                $(this).prev().prev().prev().click(function(e){
                    tag.toggle(0);
                    Win.refreshWindowScrollbars();
                    return false;
                });
            }
        });

        $('.args', $('.trace')).find('span').each(function(){
            var tag = $(this).next();
            if ($(this).next().is('code'))
            {
                $(this).next().hide();
                $(this).wrap('<span>');
                $(this).click(function(e){
                    tag.toggle(0);
                    Win.refreshWindowScrollbars();
                    
                    return false;
                });
             }
        });
    });

    //]]>
    </script>




</body>
</html>

EOF;
    }

    /**
     * @param $error
     * @param $xml
     * @return string
     */
    protected function display_xml_error($error, $xml)
    {
        $return = $xml[ $error->line - 1 ] . "\n";
        $return .= str_repeat( '-', $error->column ) . "^\n";

        switch ( $error->level )
        {
            case LIBXML_ERR_WARNING:
                $return .= "Warning $error->code: ";
                break;
            case LIBXML_ERR_ERROR:
                $return .= "Error $error->code: ";
                break;
            case LIBXML_ERR_FATAL:
                $return .= "Fatal Error $error->code: ";
                break;
        }

        $return .= trim( $error->message ) .
            "\n  Line: $error->line" .
            "\n  Column: $error->column";

        if ( $error->file )
        {
            $return .= "\n  File: $error->file";
        }

        return "$return\n\n--------------------------------------------\n\n";
    }

    /**
     * @param BaseException $exception
     * @param array $data
     * @return string
     */
    protected function getMain(BaseException $exception, array $data)
    {

        # $HTML_URL           = HTML_URL;
        $BACKEND_IMAGE_PATH = defined( 'BACKEND_IMAGE_PATH' ) ? BACKEND_IMAGE_PATH : '';

        $code = <<<EOF

        <div id="widget-contents">
            <div id="header">
               <div class="menu-header">
                <div id="title-bar">
                    <div id="copyright">&copy;
EOF;


        $code .= date( 'Y' );
        $code .= <<<EOF

        by DreamCMS Development</div>
                    <div id="title-bar-logo"></div>
                    <h1 id="title-container" style="display:block">
			Error!
                    </h1>
                    <h1 id="tip-container" style="display:none"></h1>
                </div>

                <div id="menu-bar">
                    <div id="topmenubar">
                        <ul id="dcms-menu">
                            <li style="float:left;width:100%;height:16px">
                                <button id="back-button" class="action-button" style="float:left;">
                                    <img src="{$BACKEND_IMAGE_PATH}back.png" width="16" height="16" alt="" />&nbsp;Back
                                </button>
                                <button id="reload-button" class="action-button" style="margin-left:5px;float:left;">
                                    <img src="{$BACKEND_IMAGE_PATH}buttons/refresh-large.png" width="16" height="16" alt="" />&nbsp;Try Again
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
                </div>
            </div>
            
<div class="middle-container" style="width: 100%">
            <div id="content" class="gui-content">
                <div id="maincontent" class="isscrollable">


EOF;

        if ( ( !defined( 'SKIP_DEBUG' ) || SKIP_DEBUG !== true ) )
        {
            $code .= <<<EOF

                    <div class="box">
                        <h2><img src="{$BACKEND_IMAGE_PATH}info.png" width="16" height="16" alt="" />&nbsp;{$exception->_errorType} Debug Information </h2>

                        <div class="box-inner">
                            Please note that <strong style="color: red;">DEBUG is turned on</strong>. This means that this error page may reveal important information about your server setup, the code that's running or queries being executed on the database.<br />If you're running the System on a live site, be sure to turn DEBUG off (in /index.php, around line 33).
                        </div>

                    </div>

                    <div class="box">
                        <h2><img src="{$BACKEND_IMAGE_PATH}form-not-ok.png" width="16" height="16" alt="" />&nbsp;Could not complete your last request</h2>
                        <div class="box-inner">

EOF;


            $code .= $data[ 'message' ] . $exception->_exeptionXmlMessage;

            if ( isset( $data[ 'message_html' ] ) )
            {
                $code .= $data[ 'message_html' ] . $exception->_exeptionXmlMessage;
            }


            $code .= '
                        </div>
                    </div>
';


            $file = Debug::path( $this->_file );
            $line = $this->_line;


            if ( $exception->_errorType == 'PHP' )
            {


                $code .= <<<EOF

                <div class="box">
                    <h2 class="collapsible"><img src="{$BACKEND_IMAGE_PATH}info.png" width="16" height="16" alt="" />&nbsp;Error Information</h2>
                    <div class="box-inner">
                        <table cellpadding="2" cellspacing="0" summary="" style="width: 100%;">
                            <tr>
                                <td width="8%">Type:</td>
                                <td>{$exception->_errorType}</td>
                            </tr>
                            <tr>
                                <td width="8%">Error Code:</td>
                                <td>{$exception->_errorCode}</td>
                            </tr>
EOF;
                if ( $file )
                {
                    $code .= <<<EOF
                                
                                
                            <tr>
                                <td>File:</td>
                                <td>{$file}</td>
                            </tr>
EOF;
                }


                if ( $line )
                {
                    $code .= <<<EOF
                            <tr>
                                <td>Line:</td>
                                <td>{$line}</td>
                            </tr>

EOF;
                }
                //     if (defined('ADM_SCRIPT') && ADM_SCRIPT )
                //      {

                $_controller = ( defined( 'CONTROLLER' ) ? ( CONTROLLER . '/' ) : '-- none -- / ' );
                $_action     = ( defined( 'ACTION' ) ? ACTION : '-- none --' );

                $_requestURI    = isset( $_SERVER[ 'REQUEST_URI' ] ) ? $_SERVER[ 'REQUEST_URI' ] : '-- none --';
                $_requestDefine = ( defined( 'REQUEST' ) ? REQUEST : '-- none --' );


                $code .= <<<EOF
                                <tr>
                                    <td>Script:</td>
                                    <td>{$_SERVER['SCRIPT_NAME']}</td>
                                </tr>
                                <tr>
                                    <td>Controller / Action:</td>
                                    <td>{$_controller}{$_action}</td>
                                </tr>
                                <tr>
                                    <td>Server Request:</td>
                                    <td>
                                    {$_requestURI}</td>
                                </tr>
                                <tr>
                                    <td>Defined Request:</td>
                                    <td>{$_requestDefine}</td>
                                </tr>
                                <tr>
                                    <td>Input:</td>
                                    <td>

EOF;

                $code .= var_export( HTTP::input(), true );
                $code .= '
                                    </td>
                                </tr>';
                //  }


                $code .= '
                        </table>
                    </div>
                </div>
';
                if ( !empty( $data[ 'code' ] ) )
                {


                    $code .= <<<EOF

                    <div class="box">
                        <h2><img src="{$BACKEND_IMAGE_PATH}info.png" width="16" height="16" alt="" />&nbsp;Code Excerpt</h2>
                        <div class="box-inner">

EOF;


                    $code .= $data[ 'code' ];
                    $code .= '
                        </div>
                    </div>';
                }

                if ( !empty( $data[ 'code_html' ] ) )
                {

                    $code .= <<<EOF

                    <div class="box">
                        <h2><img src="{$BACKEND_IMAGE_PATH}info.png" width="16" height="16" alt="" />&nbsp;Template Code</h2>
                        <div class="box-inner">
EOF;


                    $code .= $data[ 'code_html' ];
                    $code .= '
                        </div>
                    </div>';
                }
            }


            if ( $exception->_errorType == 'SQL' && $data[ 'code' ] )
            {

                $code .= <<<EOF

                <div class="box">
                    <h2 class="collapsible"><img src="{$BACKEND_IMAGE_PATH}info.png" width="16" height="16" alt="" />&nbsp;SQL Query</h2>
                    <div class="box-inner">

EOF;

                $code .= $data[ 'code' ];
                $code .= '
                    </div>
                </div>
';

                if ( isset( $args[ 3 ] ) && count( $args[ 3 ] ) > 0 )
                {

                    $code .= <<<EOF

                    <div class="box" id="queryargs">
                        <h2 class="collapsible"><img src="{$BACKEND_IMAGE_PATH}info.png" width="16" height="16" alt="" />&nbsp;Query Arguments</h2>

                        <div class="box-inner">
                            <table cellpadding="3" cellspacing="0" style="width: 100%;">

EOF;
                    foreach ( $args[ 3 ] as $key => $argument )
                    {


                        $_k = $key + 1;

                        $code .= <<<EOF

                                    <tr>
                                        <td style="width: 20%;">Argument {$_k}:</td>
                                        <td>
                                        {$argument}
                                        </td>
                                    </tr>

EOF;
                    }

                    $code .= <<<EOF

                            </table>
                        </div>
                    </div>

EOF;
                }
            }


            if ( !empty( $data[ 'trace' ] ) )
            {


                $code .= <<<EOF

                <div class="box">
                    <h2 class="collapsible"><img src="{$BACKEND_IMAGE_PATH}info.png" width="16" height="16" alt="" />&nbsp;Backtrace</h2>
                    <div class="box-inner">
                    {$data['trace']}
                    </div>
                </div>

EOF;
            }
            if ( !empty( $data[ 'trace_html' ] ) )
            {


                $code .= <<<EOF

                <div class="box">
                    <h2 class="collapsible"><img src="{$BACKEND_IMAGE_PATH}info.png" width="16" height="16" alt="" />&nbsp;HTML Backtrace</h2>
                    <div class="box-inner">
                    {$data['trace']}
                    </div>
                </div>

EOF;
            }
            if ( $exception->_errorType != 'SQL' )
            {
                $code .= <<<EOF
                    
                    
                <div class="box">
                    <h2 class="collapsible"><img src="{$BACKEND_IMAGE_PATH}info.png" width="16" height="16" alt="" />&nbsp;SQL Querys</h2>
                    <div class="box-inner">
                    <pre style="overflow:hidden">
EOF;

                $code .= htmlspecialchars( Database::getInstance()->getDebug() );
                $code .= <<<EOF

                    </pre>
                    </div>
               </div>
EOF;
            }
        }


        $code .= <<<EOF

</div>
</div>
</div>

</div>
EOF;


        return $code;
    }



    /**
     * @param BaseException $exception
     * @return array
     */
    private function _prepareTrace(BaseException $exception)
    {
        $trace    = debug_backtrace( true );
        $newtrace = array();

        $file = $this->_file;
        $line = $this->_line;

        foreach ( $trace as $key => $step )
        {

            if ( isset( $step[ 'class' ] ) &&
                (
                    $step[ 'class' ] == __CLASS__ ||
                    $step[ 'class' ] == 'Exception' ||
                    $step[ 'class' ] == 'BaseException' ||
                    $step[ 'class' ] == 'DatabaseError' ||
                    $step[ 'class' ] == 'Error' ||
                    $step[ 'class' ] == 'Debug' ||
                    $step[ 'class' ] == 'PDOStatement' ||
                    $step[ 'class' ] == 'PDO' ||
                    $step[ 'class' ] == 'Database_PDO' ||
                    $step[ 'class' ] == 'PDOException' ) ||
                $step[ 'function' ] == 'shutdownError' ||
                $step[ 'function' ] == 'call_user_func_array' ||
                $step[ 'function' ] == 'catch_errors'
            )
            {
                continue;
            }


            if ( isset( $step[ 'file' ] ) )
            {
                $file = $step[ 'file' ];

                if ( isset( $step[ 'line' ] ) )
                {
                    $line = $step[ 'line' ];
                }
            }


            if ( empty( $step[ 'class' ] ) && !function_exists( $step[ 'function' ] ) )
            {
                # introspection on closures or language constructs in a stack trace is impossible before PHP 5.3
                $params = null;
            }
            else
            {
                try
                {
                    if ( isset( $step[ 'class' ] ) )
                    {
                        if ( method_exists( $step[ 'class' ], $step[ 'function' ] ) )
                        {
                            $reflection = new ReflectionMethod( $step[ 'class' ], $step[ 'function' ] );
                        }
                        else if ( isset( $step[ 'type' ] ) && $step[ 'type' ] == '::' )
                        {
                            $reflection = new ReflectionMethod( $step[ 'class' ], '__callStatic' );
                        }
                        else
                        {
                            $reflection = new ReflectionMethod( $step[ 'class' ], '__call' );
                        }
                    }
                    else
                    {
                        $reflection = new ReflectionFunction( $step[ 'function' ] );
                    }

                    # get the function parameters
                    $params = $reflection->getParameters();
                }
                catch ( Exception $e )
                {
                    $params = null; # avoid various PHP version incompatibilities
                }
            }

            $args = array();
            foreach ( $step[ 'args' ] as $i => $arg )
            {
                if ( isset( $params[ $i ] ) )
                {
                    # assign the argument by the parameter name
                    $args[ $params[ $i ]->name ] = $arg;
                }
                else
                {
                    # assign the argument by number
                    $args[ $i ] = $arg;
                }
            }


            if ( isset( $step[ 'class' ] ) )
            {
                # Class->method() or Class::method()
                $function = $step[ 'class' ] . $step[ 'type' ] . $step[ 'function' ];
            }

            if ( isset( $step[ 'object' ] ) )
            {
                $function = $step[ 'class' ] . $step[ 'type' ] . $step[ 'function' ];
            }

            if ( isset( $file ) && isset( $line ) )
            {
                // Include the source of this step
                $source = Debug::source( $file, $line );
            }

            $newtrace[ $key ] = array(
                'function' => $function,
                'args'     => isset( $args ) ? $args : null,
                'file'     => isset( $file ) ? $file : null,
                'line'     => isset( $line ) ? $line : null,
                'source'   => isset( $source ) ? $source : null,
                'object'   => isset( $step[ 'object' ] ) ? $step[ 'object' ] : null,
            );

            unset( $function, $args, $file, $line, $source );

        }

        $newtrace = array_reverse( $newtrace );


        if ( $this->_line && $this->_file )
        {
            $index = count( $newtrace ) - 1;
            $callFunction = ( isset($newtrace[$index]['function']) && !empty($newtrace[$index]['function']) ? $newtrace[$index]['function'] : '* UNDEFINED FUNCTION *' );

            $callFunction = preg_replace('#\$?([a-zA-Z0-9_]*)->#', '', $callFunction);

            $source = Debug::source( $this->_file, $this->_line );

            array_push( $newtrace, array(
                'function' => $callFunction,
                'args'     => isset( $args ) ? $args : null,
                'file'     => isset( $this->_file ) ? $this->_file : null,
                'line'     => isset( $this->_line ) ? $this->_line : null,
                'source'   => isset( $source ) ? $source : null,
                'object'   => null,
            ) );
        }


        return $newtrace;
    }


    /**
     * @param BaseException $exception
     * @return array
     */
    private function getTraceArray(BaseException $exception)
    {
        return $this->_prepareTrace( $exception );
    }


    /**
     * @param BaseException $exception
     * @param bool $getTraceMode
     * @param bool $fullReturnMode
     * @return string
     */
    private function getTraceCode(BaseException $exception, $getTraceMode = false, $fullReturnMode = false)
    {
        // $trace = debug_backtrace(false);
        $trace = $this->getTraceArray( $exception );


        if ( $exception->isCompilerError && $exception->_compilerErrorTemplate )
        {
            $errors = libxml_get_errors();

            if ( sizeof( $errors ) )
            {

                if ( preg_match( '/\:([\d]+)\: parser error/', $exception->getMessage(), $l ) )
                {
                    $errors[ 'line' ] = $l[ 1 ];
                }


                if ( preg_match( '/\:([\d]+)\: parser error\s*\:\s*(.+)/', $exception->getMessage(), $l ) )
                {
                    $errors[ 'message' ] = $l[ 2 ];
                }


                $_code = '';
                if ( isset( $errors[ 'line' ] ) && (int)$errors[ 'line' ] )
                {
                    $data[ 'line' ]                 = (int)$errors[ 'line' ];
                    $exception->_errorLine          = $data[ 'line' ];
                    $exception->_exeptionXmlMessage = $errors[ 'message' ];

                    $lines = explode( "\n", $exception->_compilerErrorTemplate );

                    for ( $i = ( $data[ 'line' ] - 10 ); $i < ( $data[ 'line' ] + 10 ); $i++ )
                    {
                        $_code .= $i . ': ' . trim( $lines[ $i - 1 ] ) . "\n";
                    }
                }


                $output = 'Template error in ' . $GLOBALS[ 'COMPILER_TEMPLATE_PATH' ] . ' @Line:' . $exception->getLine() . "\n\n" . htmlentities( $_code );

                return $output;
            }
        }


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

                $source_id = $error_id . 'source' . $i;
                if ( !$getTraceMode )
                {

                    $output .= '<a href="#' . $source_id . '" onclick="return toggleTrace(\'' . $source_id . '\')">';
                }

                $output .= Debug::path( $step[ 'file' ] );

                if ( !$getTraceMode )
                {
                    $output .= ' [ ' . $step[ 'line' ] . ' ]</a>';
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
                $output .= ')' . "\n";
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
                $this->formatArgs( $step[ 'args' ], $args_id, $output, ( !$getTraceMode ? true : false ) );
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
                        $output .= '
';
                }
            }

            unset( $args_id, $source_id );
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
     * @param $step
     * @param $args_id
     * @param $code
     * @param bool $asTable
     */
    private function formatArgs(&$step, $args_id, &$code, $asTable = true)
    {

        if ( $asTable )
        {
            $code .= <<<E
				<div id="{$args_id}" class="collapsed args">
					<table cellspacing="0" class="args">
E;
        }

        foreach ( $step as $name => $arg )
        {
            Debug::resetDump();
            $_dump = Debug::dump( $arg, 300, 3 );

            #$step[$name]['args_formated'] = $_dump;

            if ( $asTable )
            {
                $code .= <<<E
						<tr>
							<td><code>{$name}</code></td>
							<td><pre>{$_dump}</pre></td>
						</tr>
E;
            }
            else
            {
                $_dump = substr( $_dump, 0, 150 );

                $code .= <<<E
                        <div style="display:block;font-weight:normal!important">
<div style="width:30%;float:left;display:inline-block">{$name}</div>
<div style="width:68%;float:right;display:inline-block">{$_dump}</div>
    </div>
E;
            }

        }

        if ( $asTable )
        {
            $code .= '
					</table>
				</div>';
        }

    }


    /**
     *
     * @param mixed $arg
     * @return string
     */
    private function getArgument($arg)
    {
        switch ( strtolower( gettype( $arg ) ) )
        {

            case 'string' :
                $arg = ( strlen( $arg ) > 200 ? substr( $arg, 0, 200 ) . ' ... ' . substr( $arg, strlen( $arg ) - 40, strlen( $arg ) ) : $arg );


                if ( $protect === 'cfg' || $protect === 'crypt_key' || $protect === 'cli_key' || $protect == 'uniqidkey' || $protect == 'email' || ( $protect === '_databaseName' || $protect === '_hostname' || $protect === '_username' || $protect === '_password' || $protect === '_port' ) || preg_match( '/.*smtp_.*/i', $protect ) || ( $arg && strstr( $protect, 'smtp' ) )
                )
                {
                    $arg = '*** Protected ***';
                }
                else
                {
                    $arg = Debug::protectPath( $arg );
                }

                return ( "'" . str_replace( array(
                        "\n",
                        '\\'
                    ), array(
                        '',
                        '/'
                    ), $arg ) . "'" );

            case 'boolean' :
                return (bool)$arg;

            case 'object' :
                return 'object(' . get_class( $arg ) . ')';
/*
            case 'string' :
                return '' . htmlspecialchars( $arg ) . '';
*/
            case 'array' :
                $ret      = 'array(';
                $separtor = '';

                foreach ( $arg as $key => $val )
                {
                    if ( $key === 'cfg' || $key === 'crypt_key' || $key === 'cli_key' || $key == 'uniqidkey' || $key == 'email' || $key === '_databaseName' || $key === '_hostname' || $key === '_username' || $key === '_password' || $key === '_port' || ( is_string( $val ) && preg_match( '/.*smtp_.*/i', $val ) ) || ( is_string( $val ) && strstr( $val, 'smtp' ) )
                    )
                    {
                        $val = '*** Protected ***';
                    }
                    else
                    {
                        $val = Debug::protectPath( $val );
                    }

                    $ret .= $separtor . $this->getArgument( $key ) . ' => ' . $this->getArgument( $val, $key );
                    $separtor = ", ";
                }
                $ret .= ')';

                return $ret;

            case 'resource' :
                return 'resource(' . get_resource_type( $arg ) . ')';

            default :
                return var_export( $arg, true );
        }
    }

}

?>