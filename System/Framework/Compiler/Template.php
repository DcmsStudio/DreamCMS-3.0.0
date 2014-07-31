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
 * @file         Template.php
 */
class Compiler_Template /* extends Compiler_Template_Abstract */
{

    /**
     * template name
     *
     * @var string
     */
    protected $name;

    public $forceCompilation = false;

    public $isStringTemplate = false;

    public $sourceFilename = false;

    public $compiledFilename = false;

    protected $_code = '';

    protected $_compiledCode = false;

    private $cacheTime = 0;

    private $compileId = false;


    public $scriptHeader = null;

    public $setCheckFiles = null;

    public $blockFiles = null;

    public $isProxyTemplate = false;

    //////

    /**
     * @var Compiler|null
     */
    protected $compiler = null;

    /**
     * @var Compiler_Compile|null
     */
    protected $comp = null;

    public $disableModCheck = false;


    public static $_cache = array();


    public $isRecompileFromExtends = false;

    private $_filters = null;


    /**
     * @param Compiler $compiler
     * @param string $template
     * @param bool $isStringTemplate
     * @param bool $parentTemplate
     * @param bool $isProxyTemplate
     * @throws Compiler_Exception
     */
    public function __construct(Compiler $compiler, $template, $isStringTemplate = false, $parentTemplate = false, $isProxyTemplate = false)
    {
        /*
        #if ( is_file($template) )
        #{
        #	$isStringTemplate = false;
        #}

        if (is_file($template))
        {
            // die($template);
            $isStringTemplate = false;
        }
*/


        $this->compiler         =& $compiler;
        $this->isStringTemplate = $isStringTemplate;
        //$this->scope            = new Compiler_Scope( $compiler );


        if ( $parentTemplate )
        {
            if ( substr( $parentTemplate, -5 ) === '.html' )
            {
                $this->sourceFilename = $parentTemplate;
                $this->_code          = $template;

                if ( !$isProxyTemplate )
                {
                    $this->setCompileID( $this->sourceFilename );
                }
            }
            else
            {

                $this->_code          = $template;
                $this->sourceFilename = md5( $template );

                if ( !$isProxyTemplate )
                {
                    $this->setCompileID( md5( $parentTemplate ) );
                }
            }
        }
        else
        {
            if ( substr( $template, -5 ) === '.html' )
            {
                $this->sourceFilename = $template;

                if ( !$isProxyTemplate )
                {
                    $this->setCompileID( $this->sourceFilename );
                }


                if ( file_exists( $this->sourceFilename ) )
                {
                    $this->_code = file_get_contents( $this->sourceFilename );
                }
                else
                {
                    throw new Compiler_Exception( 'The Template ' . $this->sourceFilename . ' not exists!' );
                }
            }
            else
            {
                $this->_code          = $template;
                $this->sourceFilename = md5( $template );

                if ( !$isProxyTemplate )
                {
                    $this->setCompileID( $this->sourceFilename );
                }
            }
        }
    }

    /**
     * resets some runtime variables to allow a cloned object to be used to render sub-templates
     */
    public function __clone()
    {
        $this->sourceFilename   = null;
        $this->_code            = '';
        $this->isStringTemplate = false;
    }

    public function __destruct()
    {
        $this->_code = '';
        //$this->compiler = null;
    }

    /**
     * @param null|array $filter
     */
    public function setFilterEvents($filter = null)
    {
        $this->_filters = $filter;
    }

    /**
     * @return null|array
     */
    public function getFilterEvents()
    {
        return $this->_filters;
    }


    /**
     * @param string $tagname
     * @param null|string $attribut
     * @param string $content
     * @return mixed
     * @throws BaseException
     */
    public function filterContent($tagname, $attribut = null, $content = '')
    {
        if ( isset( $this->_filters[ strtolower( $tagname ) ] ) )
        {
            $args = func_get_args();
            unset($args[0], $args[1]);

            if (isset($this->_filters[ strtolower( $tagname ) ][ strtolower($attribut)])) {


                if (is_callable($this->_filters[ strtolower( $tagname ) ][ strtolower($attribut)]))
                {
                    throw new BaseException('Could not call your compiler filter for tag: '. $tagname .' with attribute: '.$attribut );
                }


                return call_user_func_array( $this->_filters[ strtolower( $tagname ) ][ strtolower($attribut)], $args );
            }

            if (is_callable($this->_filters[ strtolower( $tagname ) ]))
            {
                throw new BaseException('Could not call your compiler filter for tag: '. $tagname);
            }


            return call_user_func_array( $this->_filters[ strtolower( $tagname ) ], $args );
        }
        else {
            return $content;
        }
    }

    /**
     * @param Compiler $compiler
     * @param             $content
     * @param null|string $compileId
     * @param null|string $parentTemplate
     * @return Compiler_Template
     */
    public function factoryTemplate(Compiler &$compiler, $content, $compileId = null, $parentTemplate = null)
    {

        /*

        $clone = clone $this;
        $filemode = false;

        if (is_file($content)) {
            $filemode = true;
        }

        if ($filemode)
        {
            if ( $parentTemplate )
            {
                $clone->sourceFilename = $parentTemplate;
                $clone->setTemplateCode($content);
            }
            else {
                $clone->sourceFilename = $content;
                $clone->setTemplateCode(file_get_contents($content));
            }
        }
        else
        {
            $clone->sourceFilename = md5( $content );
            $clone->setTemplateCode($content);
        }

        $clone->isProxyTemplate = true;

        if ( $compileId !== null )
        {
            $clone->setCompileID( $compileId );
        }

        return $clone;
*/
        $filemode = false;

        if ( is_file( $content ) )
        {
            $filemode = true;
        }
        $factory = new Compiler_Template( $compiler, $content, $filemode, $parentTemplate, true );

        if ( $compileId !== null )
        {
            $factory->setCompileID( $compileId );
        }

        $factory->isProxyTemplate = true;

        return $factory;
    }


    /**
     * @return Compiler_Compile
     */
    public function getCompilerProcess()
    {
        return $this->comp;
    }


    /**
     * @return bool|Compiler
     */
    public function &getCompiler()
    {
        return $this->compiler;
    }

    /**
     *
     * @return string
     */
    public function getCurrentTemplateFilename()
    {
        return $this->sourceFilename;
    }

    /**
     * @return bool
     */
    public function isStringTemplate()
    {
        return $this->isStringTemplate;
    }

    /**
     * @return string
     */
    public function getTemplateCode()
    {
        return $this->_code;
    }


    /**
     * @param string $code
     */
    public function setTemplateCode($code = '')
    {
        $this->_code = $code;
    }

    public function disableFileCheck()
    {
        $this->disableModCheck = true;
    }

    public function enableFileCheck()
    {
        $this->disableModCheck = false;
    }

    /**
     * @param array $arr
     */
    public function setCheckFiles(array $arr)
    {
        if ( !is_array( $this->scriptHeader ) )
        {
            $this->setCheckFiles = array();
        }

        $this->setCheckFiles = array_merge( $this->setCheckFiles, $arr );
        $this->setCheckFiles = array_unique( $this->setCheckFiles );
    }

    /**
     * @return null
     */
    public function getCheckFiles()
    {
        return $this->setCheckFiles;
    }


    /**
     * @param $blockname
     * @param $filePath
     */
    public function addBlockFile($blockname, $filePath)
    {
        if ( !is_array( $this->blockFiles ) )
        {
            $this->blockFiles = array();
        }

        $this->blockFiles[ ] = array($blockname, $filePath);
    }

    /**
     * @return null
     */
    public function getBlockFiles()
    {
        return $this->blockFiles;
    }


    /**
     *
     */
    public function resetHeaderCode()
    {
        $this->scriptHeader = array();
    }

    /**
     * @param string $headerCode
     */
    public function appendHeaderCode($headerCode)
    {
        if ( !is_array( $this->scriptHeader ) )
        {
            $this->scriptHeader = array();
        }

        $this->scriptHeader[ ] = $headerCode;
        $this->scriptHeader    = array_unique( $this->scriptHeader );
    }

    /**
     *
     * @return array
     */
    public function getHeaderCode()
    {
        return $this->scriptHeader;
    }

    /**
     * sets the cache duration for this template
     *
     * can be used to set it after the object is created if you did not provide
     * it in the constructor
     *
     * @param int $seconds duration of the cache validity for this template, if
     *                     null it defaults to the instance's cache time. 0 = disable and
     *                     -1 = infinite cache
     */
    public function setCacheTime($seconds = 0)
    {
        $this->cacheTime = $seconds;
    }

    /**
     * returns the cache duration for this template
     * defaults to null if it was not provided
     *
     * @return int|null
     */
    public function getCacheTime()
    {

        return $this->cacheTime;
    }


    /**
     * returns an unique value identifying the current version of this template,
     * in this case it's the md4 hash of the content
     *
     * @param null $_uid
     * @return string
     */
    public function getUid($_uid = null)
    {

        if ( $_uid !== null )
        {
            $uid = md5( $this->_code );

            return ( $_uid === $uid ? true : false );
        }

        return $this->sourceFilename;
    }


    /**
     * @param string $compileId
     */
    public function setCompileID($compileId)
    {

        if ( !$this->isStringTemplate )
        {
            $this->compileId = str_replace( $this->compiler->getTemplateDir(), '', str_replace( '\\', '/', $compileId ) );
        }
        else
        {
            $this->compileId = md5( $this->_code );
        }

        $this->compileId = str_replace( ROOT_PATH, '', $this->compileId );
        $this->compileId = strtr( $this->compileId, '.\\%?=!:;', '_/------' );

        #if ( HTTP::input('print') )
        #{
        #	$this->compileId .= '-print';
        #}

        $this->compiledFilename = str_replace( '\\', '/', $this->compiler->getCompileDir() . $this->compileId . '.php' );
    }

    /**
     *
     * @return string
     */
    public function getCompiledFilename()
    {
        // no compile id was provided, set default
        if ( $this->compiledFilename === false )
        {
            if ( !$this->compileId )
            {
                if ( $this->sourceFilename )
                {
                    # $this->setCompileID($this->sourceFilename);
                }


                if ( !$this->compileId )
                {
                    #throw new Compiler_Exception( 'Invalid Template file ID!' );
                }
            }

            #if ( HTTP::input('print') )
            #{
            #	$this->compileId .= '-print';
            #}

            $this->compiledFilename = str_replace( '\\', '/', $this->compiler->getCompileDir() . $this->compileId . '.php' );
        }

        return $this->compiledFilename;
    }


    /**
     * @param bool $recompile
     * @param bool $isproxy currently not used!
     * @return string
     */
    public function getCompiledCode($recompile = false, $isproxy = false)
    {
        $compiledFile = $this->getCompiledFilename();


        if ( !$this->forceCompilation && isset( self::$_cache[ $this->compileId ] ) )
        {
        }
        elseif ( !$this->forceCompilation && is_file( $compiledFile ) )
        {
            self::$_cache[ $this->compileId ] = true;
        }
        else
        {
            $this->forceCompilation = false;
            $this->compiler->helper->initCache();
            $this->compiler->setFunctions();

            $this->setFilterEvents( $this->compiler->getFilters() );

            $this->comp                  = new Compiler_Compile( $this );
            $this->comp->disableModCheck = $this->disableModCheck;

            if ( $this->isRecompileFromExtends && !$recompile )
            {
                $this->isRecompileFromExtends = false;
                $recompile                    = true;
            }

            $tmp = $this->comp->compileIt( $recompile );
            $this->comp->cleanCompiledCode( $compiledFile, $tmp );

            self::$_cache[ $this->compileId ] = true;
            unset( $GLOBALS[ 'COMPILER_TEMPLATE' ] );
        }

        return $compiledFile;
    }
}