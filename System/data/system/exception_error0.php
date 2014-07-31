<?php

$title = $exception->_exeptionTilte != '' ? $exception->_exeptionTilte . ' ' : '';

if ( isset( $GLOBALS[ 'COMPILER_TEMPLATE' ] ) && $GLOBALS[ 'COMPILER_TEMPLATE' ] !== null )
{
    $this->_highlightArr = Library::syntaxHighlightCode( $GLOBALS[ 'COMPILER_TEMPLATE' ], 'xml' );
}


$portalurl = '';
if ( $exception->_errorType != 'SQL' )
{
    $portalurl = Settings::get( 'portalurl', '' );
}

$bt = Settings::get( 'portalurl', '' ) . '/public/' . BACKEND_CSS_PATH;

$BACKEND_IMAGE_PATH = defined( 'BACKEND_IMAGE_PATH' ) ? BACKEND_IMAGE_PATH : '';
$errortype = $exception->_errorType;
$errorcode = $exception->_errorCode;
$message = $data[ 'message' ];

$file = $this->_file;
$line = $this->_line;

$_controller = ( defined( 'CONTROLLER' ) ? ( CONTROLLER . '/' ) : '-- none -- / ' );
$_action = ( defined( 'ACTION' ) ? ACTION : '-- none --' );
$_requestURI = isset( $_SERVER[ 'REQUEST_URI' ] ) ? $_SERVER[ 'REQUEST_URI' ] : '-- none --';
$_requestDefine = ( defined( 'REQUEST' ) ? REQUEST : '-- none --' );

$_css = ( isset( $data[ 'css' ] ) ? $data[ 'css' ] : '' );
$_css .= ( isset( $data[ 'css_html' ] ) ? $data[ 'css_html' ] : '' );

if ( isset( $this->_highlightArr[ 'css' ] ) )
{
    $_css .= $this->_highlightArr[ 'css' ];
}
?>
<!DOCTYPE html>
<html>
<head>
    <base href="<?php echo $portalurl; ?>/"/>
    <title><?php echo $title; ?> Error</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta http-equiv="Content-Style-Type" content="text/css"/>
    <link rel="stylesheet" href="<?php echo $bt; ?>dcms.error.css?_=1.0" type="text/css"/>
    <style type="text/css">

        <?php echo $css; ?>

        .toggle-container {
            display: none;
        }

    </style>


    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js"></script>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.js"></script>

    <script type="text/javascript">

        function toggleTrace(elem) {
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

        $(document).ready(function () {

            $('#back-button').click(function () {
                //if(window.opener) {
                //    window.close();
                //} else {
                history.go(-1);
                //}
            });

            $('#reload-button').click(function () {
                document.location.href = document.location.href;
            });


            $('#trace-list li').click(function (e) {
                if (!$(this).hasClass('selected')) {
                    var currentid = $(this).parent().find('li.selected').attr('rel');

                    $(this).parent().find('li.selected').removeClass('selected');
                    $(this).addClass('selected');

                    $('#' + currentid).hide();
                    $('#' + $(this).attr('rel')).show();
                }
            });


            $('#trace-content').find('.traceparams').parent().addClass('has-params');

            $('#trace-content').find('.tracesourcecode').each(function () {

                var lines = $(this).find('.line');
                lines.each(function () {
                    var number = $(this).find('.number').clone();
                    $(this).find('.number').remove();
                    var code = $(this).text();
                    $(this).empty().append($('<div><div class="number">' + number.text() + '</div><div>' + code + '</div></div>'));
                });

                if (lines.length) $(this).parent().prepend('<div class="col-sep"></div>');

            });


            $('h3').click(function () {
                var next = $(this).next();
                var next2 = $(this).next().next();

                if (next.hasClass('toggle-container')) {


                    if (next.is(':visible')) {
                        $(this).find('span.fa').removeClass('close').addClass('open');
                        next.hide();
                    }
                    else {
                        $(this).find('span.fa').removeClass('open').addClass('close');
                        next.show();
                    }
                }

                if (next2.hasClass('toggle-container')) {
                    if (next2.is(':visible')) {
                        $(this).find('span.fa').removeClass('close').addClass('open');
                        next2.hide();
                    }
                    else {
                        $(this).find('span.fa').removeClass('open').addClass('close');
                        next2.show();
                    }
                }
            });


            $('.args', $('.trace')).find('span.array').each(function () {

                if ($(this).prev().prev().prev().is('small')) {
                    $(this).hide();

                    var tag = $(this);
                    $(this).prev().prev().click(function (e) {
                        tag.toggle(0);
                        Win.refreshWindowScrollbars();
                        return false;
                    });
                    $(this).prev().prev().prev().click(function (e) {
                        tag.toggle(0);
                        Win.refreshWindowScrollbars();
                        return false;
                    });
                }
            });

            $('.args', $('.trace')).find('span').each(function () {
                var tag = $(this).next();
                if ($(this).next().is('code')) {
                    $(this).next().hide();
                    $(this).wrap('<span>');
                    $(this).click(function (e) {
                        tag.toggle(0);
                        Win.refreshWindowScrollbars();

                        return false;
                    });
                }
            });
        });


    </script>

</head>
<body>
<div id="header">
    <div class="buttons">

        <button id="back-button" class="action-button">
            <img src="<?php echo $BACKEND_IMAGE_PATH; ?>back.png" width="16" height="16" alt=""/>&nbsp;Back
        </button>
        <button id="reload-button" class="action-button">
            <img src="<?php echo $BACKEND_IMAGE_PATH; ?>buttons/refresh-large.png" width="16" height="16" alt=""/>&nbsp;Try Again
        </button>
    </div>
    <div class="copyright"><a href="http://www.dcms-studio.de" target="_blank">DreamCMS <?php echo VERSION; ?></a> - &copy; 2008 - <?php echo date( 'Y' ); ?> by Marcel Domke</div>
</div>

<div id="main">

    <div class="box exception-message-container">

        <h3>Exception</h3>

        <div>
            <div class="exception-message <?php echo strtolower( $errortype ); ?>">
                <?php
                echo $message;
                ?>
            </div>

            <div class="exception-info <?php echo strtolower( $errortype ); ?>">

                <?php
                if ( strtolower( $errortype ) == 'php' )
                {
                    ?>
                    <dl>
                        <dt>Type:</dt>
                        <dd><?php echo $errortype; ?></dd>
                    </dl>
                    <dl>
                        <dt>Error Code:</dt>
                        <dd><?php echo $errorcode; ?></dd>
                    </dl>
                <?php
                }
                elseif ( strtolower( $errortype ) == 'sql' )
                {
                    ?>
                    <dl>
                        <dt>Type:</dt>
                        <dd><?php echo $errortype; ?></dd>
                    </dl>
                    <dl>
                        <dt>Error Code:</dt>
                        <dd><?php echo $errorcode; ?></dd>
                    </dl>
                <?php
                }
                else
                {
                    ?>
                    <dl>
                        <dt>Type:</dt>
                        <dd><?php echo $errortype; ?></dd>
                    </dl>
                    <dl>
                        <dt>Error Code:</dt>
                        <dd><?php echo $errorcode; ?></dd>
                    </dl>
                <?php
                }

                if ( $file )
                {
                    ?>
                    <dl>
                        <dt>File:</dt>
                        <dd><?php echo $file; ?></dd>
                    </dl>
                <?php
                }

                if ( $line )
                {
                    ?>
                    <dl>
                        <dt>Line:</dt>
                        <dd><?php echo $line; ?></dd>
                    </dl>
                <?php
                }
                ?>

            </div>
        </div>
    </div>

    <?php



    $input = HTTP::input();
    ?>

    <div class="box input-vars">
        <h3>Vars</h3>

        <div>
            <dl>
                <dt>Script</dt>
                <dd><?php echo $_SERVER[ 'SCRIPT_NAME' ]; ?></dd>
            </dl>
            <dl>
                <dt>Controller / Action</dt>
                <dd><?php echo $_controller; ?> / <?php echo $_action; ?></dd>
            </dl>
            <dl>
                <dt>Server Request</dt>
                <dd><?php echo $_requestURI; ?></dd>
            </dl>
            <dl>
                <dt>Defined Request</dt>
                <dd><?php echo $_requestDefine; ?></dd>
            </dl>
            <?php

            if ( isset( $input ) && count( $input ) > 0 )
            {
                ?>
                <h3><span class="fa"></span>Input Vars</h3>
                <div class="toggle-container"><pre><code class="vars"><?php
                            echo var_export( $input, true );
                            ?></code></pre>
                </div>
            <?php
            }
            ?>
        </div>
    </div>
    <?php


    if ( isset( $sqlerror ) && !empty( $sqlerror ) )
    {
        ?>
        <div class="box executed-sql">
            <h3><span class="fa"></span>Sql Error</h3>

            <div class="toggle-container">

                <code class="sql-code">
                    <?php
                    echo $sqlerror;
                    ?>
                </code>

            <span class="sql-args">
                <?php
                echo $sqlerror_args;
                ?>
            </span>

            </div>
        </div>
    <?php
    }

    $compiler_message = $data[ 'message_html' ];
    $compiler_code = $data[ 'code_html' ];

    if ( !empty( $compiler_code ) )
    {
        ?>

        <div class="box compiler-error">
            <h3><span class="fa"></span>Compiler Error</h3>

            <?php if ( !empty( $compiler_code ) )
            {
                ?>
                <div class="toggle-container">
                    <div class="compiler-template">
                        <?php
                        echo $compiler_code;
                        ?>
                        <div class="col-sep"></div>
                    </div>
                </div>
            <?php } ?>

        </div>

    <?php } ?>




















    <div class="box trace-content-wrapper">
        <h3>Debug Trace</h3>

        <div class="trace-content-container">
            <div class="trace-list">
                <ul id="trace-list">
                    <?php

                    foreach ( $trace as $key => $r )
                    {
                        echo '<li rel="trace-' . $key . '"' . ( $key == 0 ? ' class="selected"' : '' ) . '>' . $r[ 'file' ] . '  [' . $r[ 'line' ] . ']</li>';
                    }

                    ?>
                </ul>
            </div>
            <div id="trace-content" class="trace-content">

                <?php

                $total = count($trace);
                foreach ( $trace as $key => $r )
                {
                    ?>
                    <div id="trace-<?php echo $key; ?>"<?php echo( $key > 0 ? ' style="display: none"' : '' ); ?>>
                        <div class="traceheader <?php echo ($key < $total-1 ? '' : 'error') ?>">
                               <?php
                            echo ($key < $total-1 ? 'Call: '.$r[ 'function' ] .'()': '<em>Error found in Line: '. $line .'</em>');
                            ?>
                        </div>
                        <div class="scrollable">
                            <?php
                            if ( count( $r[ 'args' ] ) )
                            {
                                ?>
                                <div class="traceparams">

                                    <?php
                                    foreach ( $r[ 'args' ] as $name => $v )
                                    {
                                        ?>
                                        <dl>
                                            <dt><?php echo $name; ?></dt>
                                            <dd>
                                                <pre><code><?php echo Debug::dump( $r[ 'args' ][$name], 300, 3 ); ?></code></pre>
                                            </dd>
                                        </dl>
                                    <?php
                                    }
                                    ?>

                                </div>
                            <?php
                            }
                            ?>


                            <div class="traceinner <?php echo ($key < $total-1 ? '' : 'error') ?>">
                                <div class="tracesourcecode">
                                    <?php echo $r[ 'source' ]; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php
                }
                ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>