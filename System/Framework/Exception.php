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
 * @file        Exception.php
 *
 */
include_once FRAMEWORK_PATH . 'ErrorHandler.php';

/**
 * Class BaseException
 */
class BaseException extends Exception
{

    /**
     * @var string
     */
    protected $message = '%s';

    public $isCompilerError = false;
    public $_compilerErrorTemplate = false;
    public $_compileTemplateName = false;
    public $_compileXmlTag = false;

    public $sqlCode = false;
    public $sqlArgs = null;

    /**
     * @var string
     */
    public $_exeptionTilte = 'PHP';

    /**
     * @var string
     */
    public $_errorType = 'PHP';

    /**
     * @var string
     */
    public $_errorCode;

    /**
     * @var null|string
     */
    public $_errorFile = null;

    /**
     * @var null|string
     */
    public $_errorLine = null;

    /**
     * @var null
     */
    public $_exeptionXmlMessage = null;

    /**
     * The template, where the exception occured.
     *
     * @var string
     */
    private $_template;

    /**
     *
     */
    public function __construct()
    {

        $args = func_get_args();
        $clean = ob_get_clean();
        ob_clean();
        ob_start();

        $this->message = $args[ 0 ];

        array_shift( $args );

        $this->_errorType = isset( $args[ 0 ] ) ? $args[ 0 ] : $this->_errorType; // PHP/JS or SQL
        $this->_errorCode = isset( $args[ 1 ] ) ? $args[ 1 ] : null; // only SQL/JS code and PHP error code
        $this->_errorFile = isset( $args[ 2 ] ) ? $args[ 2 ] : null;
        $this->_errorLine = isset( $args[ 3 ] ) ? $args[ 3 ] : null;

        $this->_exeptionTilte = ( !empty( $this->_errorType ) ? $this->_errorType : $this->__exeptionTilte );

        $this->displayError();
    }

    public function displayError()
    {
        $handler = ErrorHandler::getInstance();
        $handler->display( $this );
    }

    /**
     * Sets the template name, where the exception occured.
     *
     * @param string $template The template name.
     */
    public function setTemplate($template)
    {
        $this->_template = $template;
    }

    /**
     * Returns the template name where the exception occured.
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->_template;
    }

}

/**
 * Class DcmsException
 */
class DcmsException extends BaseException
{

    /**
     * @var string
     */
    protected $_message = '%s';

    /**
     * @var string
     */
    public $_exeptionTilte = 'PHP';

    /**
     * The template, where the exception occured.
     *
     * @var string
     */
    private $_template;

    /**
     * @param $message
     */
    function __construct($message)
    {
        $this->message = vsprintf( $this->_message, $message );

        $args = func_get_args();
        array_shift( $args );


        $errtype = $args[ 0 ];
        $code    = $args[ 1 ];
        $file    = $args[ 2 ];
        $line    = $args[ 3 ];

        $this->_exeptionTilte = ( !empty( $errtype ) ? $errtype : $this->__exeptionTilte );
        $this->message .= '<br/>File: ' . $file;
        $this->message .= '<br/>Line: ' . $line;


        $handler = new ErrorHandler;
        $handler->display( $this );
    }

}

/**
 * the old errors
 */
class Error extends BaseException
{

    /**
     * @var string
     */
    protected $_message = '%s';

    /**
     * @var string
     */
    public $_exeptionTilte = 'PHP';

    /**
     * The template, where the exception occured.
     *
     * @var string
     */
    private $_template;

    /**
     * @param $message
     */
    public function __construct($message)
    {

        $this->message = vsprintf( $this->_message, $message );

        $args = func_get_args();
        array_shift( $args );


        $this->_errorType = isset( $args[ 0 ] ) ? $args[ 0 ] : $this->_errorType; // PHP/JS or SQL
        $this->_errorCode = isset( $args[ 1 ] ) ? $args[ 1 ] : null; // only SQL/JS code and PHP error code
        $this->_errorFile = isset( $args[ 2 ] ) ? $args[ 2 ] : null;
        $this->_errorLine = isset( $args[ 3 ] ) ? $args[ 3 ] : null;

        $this->_exeptionTilte = ( !empty( $errtype ) ? $errtype : $this->__exeptionTilte );

        $handler = new ErrorHandler();
        $handler->display( $this );
    }

    /**
     * @return Error
     */
    public static function raise()
    {
        $args = func_get_args();

        //$this->message = $args[0];
        return new BaseException( $args[ 0 ], $args[ 1 ], $args[ 2 ], $args[ 3 ], $args[ 4 ] );
    }

    /**
     * Sets the template name, where the exception occured.
     *
     * @param string $template The template name.
     */
    public function setTemplate($template)
    {
        $this->_template = $template;
    }

    /**
     * Returns the template name where the exception occured.
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->_template;
    }

}

/**
 * the old errors
 */
class DatabaseError extends BaseException
{

    /**
     * @var string
     */
    protected $_message = '%s';

    /**
     * @var string
     */
    public $_exeptionTilte = 'SQL';

    /**
     * The template, where the exception occured.
     *
     * @var string
     */
    private $_template;

    /**
     * @param $message
     * @param $sqlCode
     * @param $sqlArgs
     * @param $codeFile
     * @param $codeLine
     */
    public function __construct($message, $sqlCode, $sqlArgs, $codeFile, $codeLine)
    {
        $args = func_get_args();
        $clean = ob_get_clean();
        ob_clean();
        ob_start();

        $this->message = vsprintf( $this->_message, $message );
        $this->file    = $codeFile;
        $this->line    = $codeLine;
        $this->_exeptionTilte = 'SQL';
        $this->_errorType = 'SQL';
        $this->sqlCode = $sqlCode;
        $this->sqlArgs = $sqlArgs;


        $this->displayError();
    }

    /**
     * Sets the template name, where the exception occured.
     *
     * @param string $template The template name.
     */
    public function setTemplate($template)
    {
        $this->_template = $template;
    }

    /**
     * Returns the template name where the exception occured.
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->_template;
    }

}

?>