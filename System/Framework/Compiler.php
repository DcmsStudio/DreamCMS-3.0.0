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
 * @file         Compiler.php
 */

$_path = dirname( __FILE__ ) . '/';
include $_path . 'Compiler/Library.php';
include $_path . 'Compiler/Abstract.php';
include $_path . 'Compiler/Template.php';
include $_path . 'Compiler/Helper.php';

/*
include $_path .'Compiler/Parser.php';
include $_path .'Compiler/Attribute.php';
include $_path .'Compiler/Node.php';
include $_path .'Compiler/Tag.php';
include $_path .'Compiler/Expression.php';
include $_path .'Compiler/CData.php';
include $_path .'Compiler/Comment.php';
include $_path .'Compiler/Text.php';
*/

/**
 * Class Compiler_Exception
 */
class Compiler_Exception extends BaseException
{
    /**
     * @param $msg
     * @param null $xmltag
     */
    public function __construct($msg, $xmltag = null)
    {
        $this->message = $msg;

        $trace = debug_backtrace(false);

        $this->isCompilerError = true;
        $this->_errorFile = $trace[1]['file'];
        $this->_errorLine = $trace[1]['line'];
        $this->_errorType = 'Compiler';
        $this->_exeptionTilte = $this->_errorType;
        $this->code = 9000;
        $this->_compilerErrorTemplate = isset($GLOBALS['COMPILER_TEMPLATE']) ? $GLOBALS['COMPILER_TEMPLATE'] : '';
        $this->_compileTemplateName = Compiler::$nowcompileFile;
        $this->_compileXmlTag = $xmltag;
        $trace = null;

        $this->displayError();
    }
}

/**
 * Class Compiler
 */
class Compiler extends Compiler_Abstract
{

    const VERSION = '1.0';

    const TAGNAMESPACE = 'cp';

    /**
     *
     */
    const TAG = 1;

    /**
     *
     */
    const TEXT = 2;

    /**
     *
     */
    const CDATA = 3;

    /**
     *
     */
    const COMMENT = 4;

    /**
     *
     */
    const EXPRESSION = 5;

    /**
     *
     */
    const CDATA_OPEN = 10;

    /**
     *
     */
    const CDATA_CLOSE = 11;


    /**
     * @var array
     */
    protected $_attributNamespaces = array(
        'parse' => true,
        'cycle' => true,
        'if'    => true,
        'on'    => true);

    /**
     * @var bool
     */
    public $htmlEntities = true;

    /**
     * @var bool
     */
    public $escape = true;

    /**
     * @var bool
     */
    public $printComments = true;

    /**
     * @var Compiler_Data
     */
    public $datahandler = null;

    /**
     * @var int
     */
    private $cacheTime = 0;

    /**
     * @var null
     */
    public static $templateRenderTimerInit = null;

    /**
     * @var int
     */
    public static $templateRenderTimer = 0;

    /**
     * @var int
     */
    public static $templateRenderMemoryInit = null;

    /**
     * @var int
     */
    public static $templateRenderMemory = 0;

    /**
     * @var Compiler_Template
     */
    private $template = null;

    /**
     * @var null|string
     */
    private $compiledOutputDir = null;

    /**
     * @var null|string
     */
    private $compiledCacheOutputDir = null;

    /**
     * @var null|string
     */
    private $sourceTemplateDir = null;

    /**
     * @var bool
     */
    public $isProxyTemplate = false;

    /**
     * @var int
     */
    public static $cnt = 0;

    /**
     * @var bool
     */
    public $compileOnly = false;

    /**
     * @var bool
     */
    protected static $hasHashfunction = false;

    /**
     * Compiled Template output
     * @var array
     */
    protected static $_compiled = array();

    public $getCompiledFilename = null;

    static $nowcompileFile = null;


    /**
     *
     * @var null|array
     */
    private static $_filters = null;

    /**
     * Block Code cache
     * @var array
     */
    private $_bc = array();

    /**
     * constructor, sets the cache and compile dir to the default values if not provided
     *
     * @param string $templatesDir path to the templates directory
     * @param string $compileDir path to the compiled directory, defaults to /compiled
     * @param string $cacheDir path to the cache directory, defaults to /cache
     */
    public function __construct($templatesDir = null, $compileDir = null, $cacheDir = null)
    {
        if ( !isset( $GLOBALS[ 'COMPILER' ] ) )
        {
            $GLOBALS[ 'COMPILER' ] = true;
        }

        if ( isset( $GLOBALS[ 'BACKEND' ] ) && is_object( $GLOBALS[ 'BACKEND' ] ) )
        {
            $this->cacheTime = 0;
        }

        $this->sourceTemplateDir      = $templatesDir;
        $this->compiledOutputDir      = $compileDir;
        $this->compiledCacheOutputDir = $cacheDir;

        if ( $this->dat === null )
        {
            $this->dat = array();
        }

        if ( !is_array( self::$_staticData ) )
        {
            self::$_staticData = array();
        }

        //
        $this->helper = new Compiler_Helper( $this->charset );


        // $this->datahandler = new Compiler_Data();

        $this->Env = Registry::getObject( 'Env' );

        Library::enableErrorHandling();

    }

    public function __destruct()
    {
        self::$_compiled   = array();
        self::$_staticData = array();
    }


    public function freeMem()
    {
        $this->free();
        self::$_staticData = array();
        $this->dat = array();
    }

    public function free()
    {
        # self::$_compiledBlocks   = array();
        # self::$_staticData = array();

        # $this->_checkheaders    = array();
        # $this->_functions       = array();
        # $this->_useBlockHeaders = array();
        # $this->registredBlocks  = array();
        //$this->compiler = null;

    }


    /**
     * @param string $tagname
     * @param array $callback
     * @return Compiler
     */
    public function addFilter($tagname, $callback)
    {
        if (!$tagname) {
            return $this;
        }

        if (($pos = strpos($tagname, '[')) !== false && substr($tagname, -1) === ']')
        {
            self::$_filters[ substr($tagname, 0, $pos) ][ substr($tagname, $pos+1, -1) ] = $callback;
        }
        else {
            self::$_filters[ $tagname ] = $callback;
        }

        return $this;
    }


    /**
     * @param null|string $tagname
     * @param null|string $attribut
     * @return bool
     */
    public function hasFilter($tagname = null, $attribut = null)
    {
        if (!$tagname) {
            return false;
        }

        if ( isset( self::$_filters[ strtolower( $tagname ) ] ) )
        {
            if ($attribut !== null && isset(self::$_filters[ strtolower( $tagname ) ][ strtolower($attribut)])) {
                return true;
            }

            if ($attribut === null) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $tagname
     * @param null|string $attribut
     * @param string $content
     * @return mixed
     * @throws BaseException
     */
    public static function filterContent($tagname, $attribut = null, $content = '')
    {
        if (!$content)
        {
            return $content;
        }

        if ( isset( self::$_filters[ strtolower( $tagname ) ] ) )
        {
            $args = func_get_args();
            unset($args[0], $args[1]);

            if ($attribut !== null && isset(self::$_filters[ strtolower( $tagname ) ][ strtolower($attribut)]))
            {
                $callback = self::$_filters[ strtolower( $tagname ) ][ strtolower($attribut)];
                return call_user_func_array( $callback, array($content, $attribut) );
            }

            $callback = self::$_filters[ strtolower( $tagname ) ];
            return call_user_func_array( $callback, array($content, $attribut) );
        }
        else {
            return $content;
        }
    }
    /**
     * @param string $tagname
     * @return Compiler
     */
    public function removeFilter($tagname)
    {
        if (isset(self::$_filters[ $tagname ])) {
            unset(self::$_filters[ $tagname ]);
        }

        return $this;
    }

    /**
     * @return null|array
     */
    public function getFilters() {
        return self::$_filters;
    }

    /**
     * @return string
     */
    public function getTagNamespace()
    {
        return ( self::TAGNAMESPACE ? self::TAGNAMESPACE . ':' : '' );
    }

    /**
     * @return null|string
     */
    public function getTemplateDir()
    {

        return $this->sourceTemplateDir;
    }

    /**
     * @param string $path
     */
    public function setCompileDir($path)
    {

        $this->compiledOutputDir = $path;
    }

    /**
     * @return null|string
     */
    public function getCompileDir()
    {

        return $this->compiledOutputDir;
    }

    /**
     * @return null|string
     */
    public function getCompileCacheDir()
    {

        return $this->compiledCacheOutputDir;
    }


    /**
     * @return int
     */
    public static function getCompileTimer()
    {

        return self::$templateRenderTimer;
    }

    /**
     * @return int
     */
    public static function getCompileMemory()
    {

        return self::$templateRenderMemory;
    }



    /**
     * @param $name
     * @param $code
     */
    public function setBlockCode($name, $code)
    {
        $this->bc[ $name ] = $code;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function getBlockCode($name)
    {
        if ( isset( $this->bc[ $name ] ) )
        {
            return $this->bc[ $name ];
        }

        return false;
    }


    /**
     * Remove all block files and the current compiled template.
     * Set true to force compilation!
     *
     * @param string $file
     */
    public function clearCompilerCache($file)
    {
        // clear registred blocks
        if ( count( $this->_registredBlocks ) )
        {
            foreach ( $this->_registredBlocks as $name => $r )
            {
                if ( $r[ 'compiledScope' ] === $file )
                {
                    if ( file_exists( $r[ 'path' ] ) )
                    {
                        unlink( $r[ 'path' ] );
                    }
                }
            }
        }

        unlink( $file );

        $this->forceCompilation = true;
    }

    /**
     * @return Compiler_Template
     */
    public function getTemplate()
    {

        return $this->template;
    }


    /**
     * @param string|null $tpl
     * @param array|null $data
     * @param Compiler_Template|null $proxy
     * @return bool|string
     * @throws BaseException
     */
    public function get($tpl = null, $data = null, $proxy = null)
    {
        if ( self::$templateRenderTimerInit === null )
        {
            self::$templateRenderTimerInit  = Debug::getMicroTime();
            self::$templateRenderMemoryInit = memory_get_usage();
        }

        if ( is_array( $data ) )
        {
            if ( !$this->isProxyTemplate )
            {
                $this->dat         = $data;
                self::$_staticData = array_merge( self::$_staticData, $this->dat );
            }
            else
            {
                //$this->datahandler = new Compiler_Data();
                $this->dat         = $data;
                self::$_staticData = array_merge( self::$_staticData, $this->dat );
            }
        }
        else
        {
            if ( !is_array( $data ) && ( $data instanceof Compiler_Data ) )
            {
                $this->dat         = $data->getData();
                self::$_staticData = array_merge( self::$_staticData, $this->dat );
            }
        }

        #$dataHash           = md5(serialize($this->dat));
        #$this->template = null;

        if ( $proxy !== null )
        {
            if ( ( $proxy instanceof Compiler_Template ) )
            {
                $this->template        = $proxy;
                $this->isProxyTemplate = true;
            }
            else
            {
                throw new BaseException( 'Invalid Compiler Proxy instance!' );
            }
        }
        else
        {

            if ( substr( $tpl, -5 ) === '.html' )
            {

                self::$nowcompileFile = $tpl;

                if ( file_exists( $tpl ) )
                {

                    $this->forceCompilationFile = $tpl;
                    #  $templateKey = md5($tpl);


                    #  if ( isset( self::$_compiled[ $templateKey . $dataHash ] ) )
                    #  {
                    #  	return self::$_compiled[ $templateKey . $dataHash ];
                    #  }

                    $this->template = new Compiler_Template( $this, $this->forceCompilationFile, false );

                    //$tpl = null;
                }
                else
                {
                    throw new BaseException( 'The Template ' . htmlspecialchars( $tpl ) . ' not exists!' );
                }
            }
            else
            {


                #die('str'. $tpl . ' '.  md5($tpl));
                // template is a string
                # $templateKey = md5($tpl);
                #  if ( isset( self::$_compiled[ $templateKey . $dataHash ] ) )
                # {
                #     return self::$_compiled[ $templateKey . $dataHash ];
                # }

                $this->template = new Compiler_Template( $this, $tpl, true );
            }
        }

        $filename                  = $this->template->getCompiledCode();
        $this->getCompiledFilename = $filename;


        if ( !$this->compileOnly )
        {
            Compiler_Library::disableErrorHandling();
            $this->buffer = include $filename;
            Compiler_Library::enableErrorHandling();

            // template returned false so it needs to be recompiled
            if ( !is_string( $this->buffer ) || $this->forceCompilation )
            {

                if ( !$this->isProxyTemplate )
                {
                    #$this->clearScopes();
                    $this->_usedBlocks      = array();
                    $this->_sections        = array();
                    $this->_registredBlocks = array();
                    $this->_options         = array();
                    $this->_checkheaders    = array();
                }

                $this->forceCompilation           = false;
                $this->template->forceCompilation = true;
                $filename                 = $this->template->getCompiledCode();
                # $this->getCompiledFilename        = $filename;

                Compiler_Library::disableErrorHandling();
                $this->buffer = include $filename;
                Compiler_Library::enableErrorHandling();





                if ( !is_string( $this->buffer ) )
                {
                    if ( $this->isProxyTemplate )
                    {
                        throw new BaseException( 'The Template: <pre>' . htmlspecialchars( $this->template->getTemplateCode() ) . '</pre>' );
                    }

                    throw new BaseException( 'The Template: <pre>' . ( $this->getCompiledFilename ? $this->getCompiledFilename : $filename ) . '</pre>' );
                }
            }

            self::$templateRenderMemory = memory_get_usage() - self::$templateRenderMemoryInit;
            self::$templateRenderTimer = Debug::getMicroTime() - self::$templateRenderTimerInit;
/*
            if ( self::$templateRenderTimer)
            {
                self::$templateRenderTimer = Debug::getMicroTime() - self::$templateRenderTimer;
            }
            else
            {
                self::$templateRenderTimer = Debug::getMicroTime() - self::$templateRenderTimerInit;
            }
*/

            // $this->template = $this->dat = null;



            return $this->buffer;

            # self::$_compiled[ $templateKey . $dataHash ] = trim( $this->buffer );
            # return self::$_compiled[ $templateKey . $dataHash ];
        }
    }


    public function printTemplate()
    {
        echo $this->buffer;
    }

    /**
     * Used for TAG include
     * @param string $template
     * @param string $compiledFileName
     * @return string
     */
    public function loadTemplate($template, $compiledFileName)
    {
        if ( is_string( $compiledFileName ) && $compiledFileName && file_exists( $this->compiledOutputDir . $compiledFileName ) )
        {


            Compiler_Library::disableErrorHandling();
            $out = include( $this->compiledOutputDir . $compiledFileName );
            Compiler_Library::enableErrorHandling();


            if ( is_string($out) && !$this->forceCompilation )
            {
                return $out;
            }
        }


        $factory = $this->template->factoryTemplate( $this, $this->getTemplateDir() . $template );
        $factory->enableFileCheck();
        $factory->getCompiledCode();

        Compiler_Library::disableErrorHandling();
        $out = include( $factory->getCompiledFilename() );
        Compiler_Library::enableErrorHandling();

        $this->forceCompilation = false;
        unset( $factory );

        return trim( $out );
    }

    /**
     *
     * @param string $namespaceStr
     * @return bool
     */
    public function isAttributeNamespace($namespaceStr = '')
    {
        if ( in_array( $namespaceStr, $this->_attributNamespaces ) )
        {
            return true;
        }

        return false;
    }

    /**
     *
     * @param string $namespaceStr
     * @return bool
     */
    public function isTagNamespace($namespaceStr = '')
    {
        return ( self::TAGNAMESPACE === $namespaceStr ? true : false );
    }

    /**
     *
     * @param string $namespaceStr
     * @return bool
     */
    public function isCompilerNamespace($namespaceStr = '')
    {
        if ( $this->isAttributeNamespace( $namespaceStr ) || self::TAGNAMESPACE === $namespaceStr )
        {
            return true;
        }

        return false;
    }

}