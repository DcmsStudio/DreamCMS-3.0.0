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
 * @file         Compile.php
 */


include dirname( __FILE__ ) . '/Parser.php';
include dirname( __FILE__ ) . '/Node.php';
include dirname( __FILE__ ) . '/Tag.php';


/**
 * Class Compiler_Compile
 */
class Compiler_Compile extends Compiler_Template_Abstract
{
    public $disableModCheck = true;

    private $scriptHeader = null;

    protected $chmod = 0777;

    protected $useEndPostCompiler = false;


    /**
     * @param Compiler_Template $templateInstance
     */
    public function __construct(Compiler_Template $templateInstance)
    {
        // $this->compiler = $compiler;
        $this->templateInstance = $templateInstance;
        $this->scope            = new Compiler_Scope( $this->templateInstance->getCompiler() );
        $this->templateInstance->getCompiler()->helper->initCache();
    }


    /**
     * @param $tplcode
     * @param bool $recompile
     * @return bool|SplQueue
     */
    public function getSplData(&$tplcode, $recompile = false)
    {
        $parser = new Compiler_Parser( $this->templateInstance->getCompiler()->charset, $this->templateInstance->getCompiler()->helper->getFunctions() );

        return $parser->parse( $tplcode, $recompile );
    }

    /**
     * @param bool $recompile
     * @param int $step
     * @return string
     */
    public function compileIt($recompile = false, $step = 0)
    {
        $tplcode                        = $this->templateInstance->getTemplateCode();
        $GLOBALS[ 'COMPILER_TEMPLATE' ] = $tplcode;


        /**
         * @var SplQueue
         */
        $nodes = $this->getSplData( $tplcode, $recompile );
        $nodes->rewind();
        /*
        if ($step === 9999) {

            die('Code: '.$GLOBALS[ 'COMPILER_TEMPLATE' ]);
        }*/

        $code = '';
        $ret  = $this->_processCompile( $nodes, $code );


        return $code;
    }

    /**
     * @param $nodes
     */
    private function test($nodes)
    {

        $nodes->rewind();
        while ( $nodes->valid() )
        {
            $object = $nodes->dequeue();

            print_r( $object );
            $nodes->next();
        }
    }


    /**
     * @param SplQueue $nodes
     * @param string $code
     * @param int $level
     * @param null $parentInstance
     * @param null $prevInstance
     * @return bool|string
     * @throws Compiler_Exception
     */
    public function _processCompile(SplQueue &$nodes, &$code, $level = 0, &$parentInstance = null, &$prevInstance = null)
    {
        static $_cdata;


        if ( is_bool( $_cdata ) )
        {
            $_cdata = false;
        }

        if ( /*!is_array( $nodes )*/
        !$nodes->count()
        )
        {

            if ( $level > 0 )
            {
                $this->useEndPostCompiler = true;
            }


            return false;
        }


        # $len = sizeof( $nodes );

        # for ( $i = 0; $i < $len; ++$i )
        # {
        #     $node =& $nodes[ $i ];

        $compiler =& $this->templateInstance->getCompiler();

        while ( $nodes->valid() )
        {
            $node = $nodes->dequeue();
//print_r($node);

            /*
            if ( $level == 9999 )
            {
                print_r( $node );
                die( 'Start' );
            }
            */

            switch ( $node[ 'type' ] )
            {
                case Compiler::TAG :

                    if ( $node[ 'tagname' ] && !$node[ 'isEndTag' ] )
                    {
                        //$xnode = $node;

                        /*
                        if ( isset( $node[ 'children' ] ) && ( $node[ 'children' ] instanceof SplQueue ) )
                        {
                            #$xnode[ 'children' ] = iterator_to_array($node[ 'children' ]);
                        }
                        */


                        $instance = new Compiler_Tag( $node, $this );

                        if ( $parentInstance instanceof Compiler_Tag )
                        {
                            $instance->setParent( $parentInstance );
                        }

                        if ( $prevInstance instanceof Compiler_Tag )
                        {
                            $instance->setPrev( $prevInstance );
                        }

                        if ( $prevInstance !== null )
                        {
                            $prevInstance = $instance;
                        }

                        if ( !( $prevInstance instanceof Compiler_Tag ) )
                        {
                            //$prevInstance = $instance;
                            $prevInstance = $instance;
                            $null = null;
                            $instance->setPrev( $null );
                        }


                        $instance->setCompiler( $compiler );

                        $processor = null;

                        if ( !$node[ 'isEndTag' ] && $node[ 'namespace' ] === Compiler::TAGNAMESPACE && Compiler_Helper::isSystemTag( $node[ 'tagname' ] ) )
                        {
                            $tagClass  = "Compiler_Tag_" . ucfirst( strtolower( $node[ 'tagname' ] ) );
                            $processor = new $tagClass();
                        }

                        if ( !$node[ 'isEndTag' ] && $processor === null && $node[ 'namespace' ] === Compiler::TAGNAMESPACE && Compiler_Helper::isCustomTag( $node[ 'tagname' ] ) )
                        {
                            $tagClass  = "Compiler_Tag_Custom_" . ucfirst( strtolower( $node[ 'tagname' ] ) );
                            $processor = new $tagClass();
                        }

                        if ( !$node[ 'isEndTag' ] && $processor === null && $node[ 'namespace' ] !== Compiler::TAGNAMESPACE )
                        {
                            $processor = new Compiler_Tag_Html();
                        }


                        if ( $processor === null )
                        {
                            throw new Compiler_Exception( 'Undefined processor for tag: ' . $node[ 'tagname' ] . "\n" . '<br/>is Endtag: ' . ( $node[ 'isEndTag' ] ? 'true' : 'false' ) . "\n" . '<br/>is Single Tag: ' . ( $node[ 'singletag' ] ? 'true' : 'false' ), E_USER_ERROR );

                        }


                        if ( $processor !== null )
                        {
                            if ( isset( $node[ 'children' ] ) && ( $node[ 'children' ] instanceof SplQueue ) )
                            {
                                if ( !( $node[ 'children' ] instanceof SplQueue ) )
                                {
                                    throw new Compiler_Exception( 'childs not a SplQueue' );
                                }

                                //
                                $ref = iterator_to_array( $node[ 'children' ] );
                                $instance->appendChildren( $ref );
                            }
                            elseif ( isset( $node[ 'children' ] ) && is_array( $node[ 'children' ] ) )
                            {
                                $instance->appendChildren( $node[ 'children' ] );
                            }

                            #	$processor->setCompilerTemplate($this);
                            $processor->setCompiler( $compiler );
                            $processor->setTag( $instance );
                            $instance->registerProcessor( $processor )->configure();

                            if ( !method_exists( $processor, 'process' ) )
                            {
                                throw new Compiler_Exception( 'The method "process" in tag processor "' . $node[ 'tagname' ] . '" not exists!' );
                            }

                            $this->setCurrentSourceTag( $instance->getTagSource() );

                            if ( $node[ 'tagname' ] === 'combine' && isset( $node[ 'children' ] ) )
                            {

                            }


                            /**
                             * -----------------------------
                             *      EXTENDS TAG Process
                             * -----------------------------
                             */
                            if ( $node[ 'tagname' ] === 'extends' && $node[ 'namespace' ] === Compiler::TAGNAMESPACE && !$instance->get( 'remove_children' ) )
                            {
                                if ( $this->templateInstance->isStringTemplate )
                                {
                                    throw new Compiler_Exception( 'Extens Tag can only use in Template Files and not in a String Template!' );
                                }
                                $nodes->top();
                                $nodes->setIteratorMode( SplQueue::IT_MODE_DELETE );
                                $nodes->dequeue();

                                #unset($nodes);


                                $code = $processor->process();

                                #$code = '';
                                #$this->_processCompile($newspl, $code);
                                return $code;

                            }
                            else
                            {

                                $processor->process();

                                if ( ( $node[ 'tagname' ] === 'block' && $node[ 'namespace' ] === Compiler::TAGNAMESPACE ) || $instance->get( 'remove_children' ) )
                                {
                                    unset( $node[ 'children' ] );
                                    #$nodes->pop();
                                }

                            }

                            $code .= $instance->getCompiledOpenTag();

                            if ( isset( $node[ 'children' ] ) && !$instance->get( 'remove_children' ) && ( $node[ 'children' ] instanceof SplQueue ) )
                            {
                                try
                                {
                                    #die('1');
                                    $code = $this->_processCompile( $node[ 'children' ], $code, $level + 1, $instance );

                                }
                                catch ( Exception $e )
                                {
                                    throw new Compiler_Exception( 'Compiler ERROR:' . $e->getMessage() );
                                }



                                //
                                $ref = iterator_to_array($node[ 'children' ]);
                                $instance->appendChildren( $ref );
                                //unset( $node[ 'children' ] );
                            }

                            /*
                            if ( isset( $node[ 'children' ] ) && ($node[ 'children' ] instanceof SplQueue) ) {
                                / *
                                if (!($node[ 'children' ] instanceof SplQueue) ) {
                                    die('childs not a SplQueue');
                                }

                                //
                                $ref = iterator_to_array($node[ 'children' ]);
                                $instance->appendChildren( $ref );


                                try
                                {
                                    $code = $this->_processCompile( $node[ 'children' ], $code, $level + 1, $instance );
                                }
                                catch ( Exception $e )
                                {
                                    die( 'Compiler ERROR:' . $e->getMessage() );
                                }

                                * /
                            }
                            elseif ( isset( $node[ 'children' ] ) && !($node[ 'children' ] instanceof SplQueue) && is_array( $node[ 'children' ] )  )
                            {
                                try
                                {
                                    die('1');
                                    $code = $this->_processCompile( $node[ 'children' ], $code, $level + 1, $instance );
                                }
                                catch ( Exception $e )
                                {
                                    die( 'Compiler ERROR:' . $e->getMessage() );
                                }

                                //
                                $instance->appendChildren( $node[ 'children' ] );
                            }

                            */

                            $processor->postProcess( $level );

                            if ( !$instance->isEmptyTag() )
                            {
                                $code .= $instance->getCompiledCloseTag();
                            }
                        }


                    }

                    break;

                case Compiler::EXPRESSION :
                    $instance = new Compiler_Expression( $node, $this );
                    $instance->setCompiler( $compiler );

                    $result = $this->compileExpression( (string)$instance );
                    $code .= Compiler_Abstract::PHP_OPEN . "\n" . ' echo ' . (string)$result[ 0 ] . '; ' . Compiler_Abstract::PHP_CLOSE;

                    break;

                case Compiler::TEXT :

                    if ( !trim( $node[ 'value' ] ) )
                    {
                        $code .= $node[ 'value' ];
                        continue;
                    }

                    $instance = new Compiler_Text( $node, $this );
                    $instance->setCompiler( $compiler );
                    /*

                                        if ( isset( $node[ 'children' ] ) && is_array( $node[ 'children' ] ) )
                                        {
                                            try
                                            {
                                                $code = $this->_processCompile( $node[ 'children' ], $code, $level + 1, $instance );
                                            }
                                            catch ( Exception $e )
                                            {
                                                throw new Compiler_Exception( $e->getMessage() );
                                            }
                                            //
                                            $instance->appendChildren( $node[ 'children' ] );
                                        }
                    */

                    $objstr = (string)$instance;

                    if ( strpos( $objstr, Compiler_Abstract::PHP_OPEN ) === false && strpos( $objstr, '$this->d' ) === false )
                    {
                        $code .= $this->postCompiler( $objstr );
                    }
                    elseif ( strpos( $objstr, '$this->d' ) === false )
                    {
                        $code .= $this->postCompiler( $objstr );
                    }
                    elseif ( strpos( $objstr, Compiler_Abstract::PHP_OPEN ) === false )
                    {
                        $code .= $this->postCompiler( $objstr );
                    }
                    else
                    {
                        $code .= $this->postCompiler( $objstr );
                    }

                    break;

                case Compiler::COMMENT :

                    $instance = new Compiler_Comment( $node, $this );
                    $instance->setCompiler( $compiler );


                    if ( preg_match( '#^\s*\[#', (string)$instance ) )
                    {
                        $code .= '<!--' . (string)$instance . '>';
                    }
                    else
                    {
                        if ( $compiler->printComments )
                        {
                            $code .= '<!--' . ( $instance->get( 'noEntitize' ) ? (string)$instance : $this->parseSpecialChars( (string)$instance ) ) . '-->';
                        }
                    }
                    break;

                case Compiler::CDATA:
                    $instance = new Compiler_CData( $node, $this );
                    $instance->setCompiler( $compiler );
                    $objstr = (string)$instance;

                    if ( $instance->get( 'cdata' ) && !$_cdata )
                    {
                        $code .= $this->parseEntities( $objstr );
                        #$code .= '<![CDATA[' . $this->postCompiler( $ref ) . ']]>';
                    }
                    else
                    {
                        $code .= ( !$instance->get( 'noEntitize' ) ? $this->parseSpecialChars( $objstr ) : $this->parseEntities( $objstr ) );

                        #$code .= $this->postCompiler( $ref );
                    }
                    break;

                case Compiler::CDATA_OPEN:
                    $code .= '<![CDATA[';
                    $_cdata = true;
                    break;

                case Compiler::CDATA_CLOSE:
                    $code .= '<![CDATA[';
                    $_cdata = false;
                    break;


            }

            // move to next
            $nodes->next();
        }

        return $code;
    }


    /**
     * @param $nodes
     * @param $code
     * @param int $level
     * @param bool $recompile
     * @return bool
     * @throws Compiler_Exception
     */
    public function compile(&$nodes, &$code, $level = 0, $recompile = false)
    {
        if ( !is_array( $nodes ) )
        {
            return false;
        }

        static $_cdata;

        if ( is_bool( $_cdata ) )
        {
            $_cdata = false;
        }

        foreach ( $nodes as &$node )
        {
            $obj = isset( $node[ 'instance' ] ) ? $node[ 'instance' ] : null;

            if ( is_object( $obj ) )
            {
                switch ( $obj->getType() )
                {
                    case Compiler::TAG :

                        if ( !$node[ 'isEndTag' ] )
                        {
                            $processor = $obj->getProcessor();

                            if ( !is_object( $processor ) )
                            {
                                throw new Compiler_Exception( 'Invalid processor instance for the tag processor "' . $node[ 'tagname' ] . '"!' . "\n" . '<br/>is Endtag: ' . ( $node[ 'isEndTag' ] ? 'true' : 'false' ) . "\n" . '<br/>is Single Tag: ' . ( $node[ 'singletag' ] ? 'true' :
                                        'false' ) );
                            }

                            if ( !method_exists( $processor, 'process' ) )
                            {
                                throw new Compiler_Exception( 'The method "process" in tag processor "' . $node[ 'tagname' ] . '" not exists!' );
                            }

                            # var_dump($node);
                            #  exit;

                            $processor->process();

                            $code .= $obj->getCompiledOpenTag();

                            if ( isset( $node[ 'children' ] ) && is_array( $node[ 'children' ] ) && $node[ 'tagname' ] != 'block' )
                            {
                                $tmp = '';
                                $cn  = $obj->getChildren();
                                $this->compile( $cn, $tmp, $level + 1 );
                                $code .= $tmp; //array_merge($code, $tmp);
                                $cn = null;
                            }

                            $processor->postProcess( $level );
                            if ( !$obj->isEmptyTag() )
                            {
                                $code .= $obj->getCompiledCloseTag();
                            }
                        }


                        break;


                    case Compiler::EXPRESSION :
                        $result = $this->compileExpression( (string)$obj );
                        $code .= Compiler_Abstract::PHP_OPEN . "\n" . ' echo ' . (string)$result[ 0 ] . '; ' . Compiler_Abstract::PHP_CLOSE;

                        break;

                    case Compiler::TEXT :
                        $objstr = (string)$obj;

                        if ( strpos( $objstr, Compiler_Abstract::PHP_OPEN ) === false && strpos( $objstr, '$this->d' ) === false )
                        {
                            $code .= $this->postCompiler( $objstr );
                        }
                        elseif ( strpos( $objstr, '$this->d' ) === false )
                        {
                            $code .= $this->postCompiler( $objstr );
                        }
                        elseif ( strpos( $objstr, Compiler_Abstract::PHP_OPEN ) === false )
                        {
                            $code .= $this->postCompiler( $objstr );
                        }
                        else
                        {
                            $code .= $objstr;
                        }

                        break;

                    case Compiler::COMMENT :

                        if ( preg_match( '#^\s*\[#', $obj ) )
                        {
                            $code .= '<!--' . $obj . '>';
                        }
                        else
                        {
                            if ( $this->templateInstance->getCompiler()->printComments )
                            {
                                $$code .= '<!--' . ( $obj->get( 'noEntitize' ) ? (string)$obj : $this->parseSpecialChars( (string)$obj ) ) . '-->';
                            }
                        }
                        break;


                    case Compiler::CDATA :
                        $objstr = (string)$obj;

                        if ( $obj->get( 'cdata' ) && !$_cdata )
                        {
                            $ref = $this->parseEntities( $objstr );
                            $code .= '<![CDATA[' . $this->postCompiler( $ref ) . ']]>';
                        }
                        else
                        {
                            $ref = ( !$obj->get( 'noEntitize' ) ? $this->parseSpecialChars( $objstr ) : $this->parseEntities( $objstr ) );
                            $code .= $this->postCompiler( $ref );
                        }
                        break;


                    case Compiler::CDATA_OPEN :
                        $code .= '<![CDATA[';
                        $_cdata = true;
                        break;
                    case Compiler::CDATA_CLOSE :
                        $code .= ']]>';
                        $_cdata = false;
                        break;
                }
            }
        }
    }


    /**
     * @param bool $recompile
     * @return array|bool
     * @throws Compiler_Exception
     */
    public function prepareNodeCompiler($recompile)
    {
        $parser                         = new Compiler_Parser( $this->templateInstance->getCompiler()->charset );
        $code                           = $this->templateInstance->getTemplateCode();
        $GLOBALS[ 'COMPILER_TEMPLATE' ] = & $code;

        $nodes = $parser->parse( $code, $recompile );
        // unset( $parser ); // free mem

        if ( is_array( $nodes ) )
        {
            /**
             * First scan extends
             */
            $_extend = null;

            if ( !$recompile )
            {
                foreach ( $nodes as $node )
                {
                    if ( isset( $node[ 'tagname' ] ) )
                    {
                        if ( $node[ 'tagname' ] === 'extends' && $node[ 'namespace' ] === Compiler::TAGNAMESPACE )
                        {
                            $_extend = $node;
                            break;
                        }
                    }
                }
            }

            /**
             * if template using extens tag the get the template tree and reset this template code
             * then recall Compiler_Template::getCompiledCode()
             */
            if ( is_array( $_extend ) && !$recompile )
            {
                if ( $this->templateInstance->isStringTemplate )
                {
                    throw new Compiler_Exception( 'Extens Tag can only use in Template Files and not in a String Template!' );
                }

                unset( $nodes ); // free mem

                $_extend[ 'instance' ] = new Compiler_Tag( $_extend, $this );
                $processor             = new Compiler_Tag_Extends();
                $processor->setCompiler( $this->templateInstance->getCompiler() );
                $processor->setTag( $_extend[ 'instance' ] );
                $_extend[ 'instance' ]->registerProcessor( $processor )->configure();
                $nodes = $processor->process(); // this function recall Compiler_Template::getCompiledCode()

                #print_r($nodes);exit;
                #return $this->_processNodes($nodes);
                #unset( $processor );
                # return false;
            }

            return $this->_processNodes( $nodes );
        }

        return array();
    }


    /**
     *
     * @param array $nodes
     * @param null $parentInstance
     * @param null $prevInstance
     * @throws Compiler_Exception
     * @return array
     */
    public function _processNodes(&$nodes, &$parentInstance = null, &$prevInstance = null)
    {

        foreach ( $nodes as &$node )
            #while ($node = each($nodes) )
        {
            switch ( $node[ 'type' ] )
            {
                case Compiler::TAG:

                    // add processors only to the open tag
                    if ( $node[ 'tagname' ] && !$node[ 'isEndTag' ] )
                    {

                        $node[ 'instance' ] = new Compiler_Tag( $node, $this );

                        if ( $parentInstance instanceof Compiler_Tag )
                        {
                            $node[ 'instance' ]->setParent( $parentInstance );
                        }

                        if ( $prevInstance instanceof Compiler_Tag )
                        {
                            $node[ 'instance' ]->setPrev( $prevInstance );
                        }

                        if ( $prevInstance !== null )
                        {
                            $prevInstance = & $node[ 'instance' ];
                        }

                        if ( !( $prevInstance instanceof Compiler_Tag ) )
                        {
                            $prevInstance = & $node[ 'instance' ];
                        }

                        $node[ 'instance' ]->setCompiler( $this->templateInstance->getCompiler() );


                        $processor = null;
                        if ( !$node[ 'isEndTag' ] && $node[ 'namespace' ] === Compiler::TAGNAMESPACE && Compiler_Helper::isSystemTag( $node[ 'tagname' ] ) )
                        {
                            $tagClass  = "Compiler_Tag_" . ucfirst( strtolower( $node[ 'tagname' ] ) );
                            $processor = new $tagClass();
                        }

                        if ( !$node[ 'isEndTag' ] && $processor === null && $node[ 'namespace' ] === Compiler::TAGNAMESPACE && Compiler_Helper::isCustomTag( $node[ 'tagname' ] ) )
                        {
                            $tagClass  = "Compiler_Tag_Custom_" . ucfirst( strtolower( $node[ 'tagname' ] ) );
                            $processor = new $tagClass();
                        }

                        if ( !$node[ 'isEndTag' ] && $processor === null && $node[ 'namespace' ] !== Compiler::TAGNAMESPACE )
                        {
                            $processor = new Compiler_Tag_Html();
                        }


                        if ( $processor === null )
                        {
                            trigger_error( 'Undefined processor for tag: ' . $node[ 'tagname' ] . "\n" . '<br/>is Endtag: ' . ( $node[ 'isEndTag' ] ? 'true' : 'false' ) . "\n" . '<br/>is Single Tag: ' . ( $node[ 'singletag' ] ? 'true' : 'false' ), E_USER_ERROR );

                        }


                        if ( $processor !== null )
                        {
                            #	$processor->setCompilerTemplate($this);
                            $processor->setCompiler( $this->templateInstance->getCompiler() );
                            $processor->setTag( $node[ 'instance' ] );

                            $node[ 'instance' ]->registerProcessor( $processor )->configure();
                            unset( $processor );

                            if ( isset( $node[ 'children' ] ) && is_array( $node[ 'children' ] ) )
                            {
                                try
                                {
                                    $this->_processNodes( $node[ 'children' ], $node[ 'instance' ] );
                                }
                                catch ( Exception $e )
                                {
                                    die( 'Compiler ERROR:' . $e->getMessage() );
                                }
                                //
                                $node[ 'instance' ]->appendChildren( $node[ 'children' ] );
                            }
                        }
                    }
                    break;

                case Compiler::COMMENT:
                    $node[ 'instance' ] = new Compiler_Comment( $node, $this );
                    $node[ 'instance' ]->setCompiler( $this->templateInstance->getCompiler() );
                    break;

                case Compiler::CDATA:
                case Compiler::CDATA_OPEN:
                case Compiler::CDATA_CLOSE:
                    $node[ 'instance' ] = new Compiler_CData( $node, $this );
                    $node[ 'instance' ]->setCompiler( $this->templateInstance->getCompiler() );
                    break;

                case Compiler::TEXT:
                    $node[ 'instance' ] = new Compiler_Text( $node, $this );
                    $node[ 'instance' ]->setCompiler( $this->templateInstance->getCompiler() );


                    if ( isset( $node[ 'children' ] ) && is_array( $node[ 'children' ] ) )
                    {
                        try
                        {
                            $this->processNodes( $node[ 'children' ], $node[ 'instance' ], $prevInstance );
                        }
                        catch ( Exception $e )
                        {
                            throw new Compiler_Exception( $e->getMessage() );
                        }
                        //
                        $node[ 'instance' ]->appendChildren( $node[ 'children' ] );
                    }
                    break;

                case Compiler::EXPRESSION:
                    $node[ 'instance' ] = new Compiler_Expression( $node, $this );
                    $node[ 'instance' ]->setCompiler( $this->templateInstance->getCompiler() );
                    break;
            }
        }

        return $nodes;
    }


    /**
     * returns some php code that will check if this template has been modified or not
     * if the function returns null, the template will be instanciated and then the Uid checked
     *
     * @return string
     */
    public function getIsModifiedCode()
    {

        if ( $this->templateInstance->isStringTemplate() )
        {
            $this->disableModCheck = true;
            $uid                   = md5( $this->templateInstance->getTemplateCode() );

            return ' !$this->template->getUiq("' . $uid . '") ';
        }


        $mtime = filemtime( $this->templateInstance->getCurrentTemplateFilename() );

        if ( !$mtime )
        {
            return false;
            // die( 'Invalid template to get filemtime(): ' . $this->templateInstance->getCurrentTemplateFilename() );
        }


        return ' ' . $mtime . ' !== filemtime(ROOT_PATH.\'' . str_replace( ROOT_PATH, '', str_replace( '\\', '/', $this->templateInstance->getCurrentTemplateFilename() ) ) . '\') ';
    }

    /**
     *
     * @param mixed $var
     * @return string
     */
    private function var_export_min($var)
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
                    $toImplode[ ] = var_export( $key, true ) . '=>' . self::var_export_min( $value );
                }
            }

            $code = 'array(' . implode( ',', $toImplode ) . ')';

            return $code;
        }
        else
        {
            return var_export( $var );
        }
    }

    /**
     *
     * @param string $output
     * @return string
     */
    public function cleanCompiledCodeOnly(&$output)
    {

        return preg_replace( '#' . preg_quote( Compiler_Abstract::PHP_CLOSE, '#' ) . '([\t\s\n\r]*)' . preg_quote( Compiler_Abstract::PHP_OPEN, '#' ) . '#', '', $output );
    }


    /**
     *
     * @param string $compiledFile
     * @param string $output
     * @param null $useProxy
     */
    public function cleanCompiledCode($compiledFile, $output, $useProxy = null)
    {

        if ( strpos( $compiledFile, 'show_html.php' ) != false )
        {
            # die($output . $GLOBALS[ 'COMPILER_TEMPLATE' ]);
        }

        if ( $this->useEndPostCompiler )
        {
            $output                   = $this->postCompiler( $output );
            $this->useEndPostCompiler = false;
        }

        $output = str_replace( array(
            '@@@TRANS-PATCH@@@',
            '@@@TRANS-PATCH-END@@@'
        ), array(
            Compiler_Abstract::PHP_OPEN,
            Compiler_Abstract::PHP_CLOSE
        ), $output );

        // little patch for script tags
        $output = Compiler_Parser::jsBackwardClean( $output );

        $output = str_replace( '##JS_AMP####JS_AMP##', '&&', $output );


        /**
         * Starting file writing
         */
        $fileOutput = Compiler_Abstract::PHP_OPEN;
        $fileOutput .= "\n" . ' if(!isset($GLOBALS[\'COMPILER\'])) { throw new Compiler_Exception(\'No direct use allowed!\'); } ob_start();' . "\n";


        // prepare file modification check if not disabled
        /*
        $checkMod = '';
        if ( $this->disableModCheck === false )
        {
            $modCheck = $this->getIsModifiedCode();

            if ( $modCheck && !$this->disableModCheck )
            {
                $checkMod = '/ * startmodcheck * / if (' . $modCheck . ') { $this->clearCompilerCache( __FILE__ ); return false; }/ * endmodcheck * /';
                $fileOutput .= "\n" . $checkMod;
            }

            $this->disableModCheck = false;
        }
        */


        // load first all registred plugins
        $fileOutput .= "\n" . trim( implode( "\n", $this->templateInstance->getCompiler()->getUsedPlugins() ) );

        // now check registred blocks
        if ( !$this->templateInstance->isProxyTemplate && $useProxy == null )
        {
            // $fileOutput .= "\n" . trim(implode("\n", array_values($this->compiler->getUsedBlocks())));
            $fileOutput .= "\n" . trim( implode( "\n", array_values( $this->templateInstance->getCompiler()->getUsedBlocks() ) ) );

            // $bf = $this->templateInstance->getBlockFiles();
            $cf = $this->templateInstance->getCheckFiles();
            if ( is_array( $cf ) )
            {

                $fileOutput .= '
if (isset($GLOBALS[\'ADM_CLEAR_TPLCACHE\'])) {
    $cf = ' . $this->var_export_min( $cf ) . ';
    $bf = $this->getUsedBlocks();
    $clear = false;
    $dirpath = dirname(__FILE__) .\'/\';

    foreach ($cf as $filepath => $mtime)
    {
        if ( $mtime && is_file(ROOT_PATH . $filepath) )
        {
            if (@filemtime(ROOT_PATH . $filepath) != $mtime) { $clear = true; break; }
        }
    }

    if ( $clear ) {
        foreach ($bf as $name => $file){ if (is_file($dirpath . $file)) { @unlink( $dirpath . $file ); } }
        @unlink(__FILE__);
    }
    return;
}
';
            }

            // file checks for extends
            if ( is_array( $this->templateInstance->scriptHeader ) && !$this->templateInstance->getCompiler()->fromprovidertag )
            {


                $fileOutput .= "\n" . implode( "\n", array_unique( $this->templateInstance->scriptHeader ) );
            }
        }

        $output = str_replace( array('\'$this->', '"$this->'), array('\'.$this->', '".$this->'), $output );

        // add the compiled template
        $fileOutput .= "\n" . Compiler_Abstract::PHP_CLOSE;

        if ( strpos( $output, 'function _Tree_' ) !== false )
        {
            $fileOutput .= $this->patchTreeFunction( utf8_decode( $output ) ); // content
        }
        else
        {
            $fileOutput .= utf8_decode( $output ); // content
        }

        $fileOutput .= Compiler_Abstract::PHP_OPEN . "\n" . 'return ob_get_clean();' . "\n" . Compiler_Abstract::PHP_CLOSE . "";

        $fileOutput = str_replace( Compiler_Abstract::PHP_CLOSE .' '.Compiler_Abstract::PHP_OPEN, Compiler_Abstract::PHP_CLOSE .'&nbsp;'.Compiler_Abstract::PHP_OPEN, $fileOutput );
        $fileOutput = preg_replace( '#' . preg_quote( Compiler_Abstract::PHP_CLOSE, '#' ) . '([\s\t\n\r]*)' . preg_quote( Compiler_Abstract::PHP_OPEN, '#' ) . '#', '', $fileOutput );

        /*
                $fileOutput = str_replace(array (
                                                '#tagopen#',
                                                '#tagclose#'
                                          ), array (
                                                   '<',
                                                   '>'
                                             ), $fileOutput);
        */

        $this->saveCompiledTemplate( $compiledFile, $fileOutput );

        unset( $fileOutput );
    }

    /**
     * @param $compiledFile
     * @param $fileOutput
     */
    public function saveCompiledTemplate($compiledFile, $fileOutput)
    {

        // save the compiled template
        $this->_makeDirectory( dirname( $compiledFile ) );

        $fh = fopen( $compiledFile, 'w+' );
        fwrite( $fh, $fileOutput );
        fclose( $fh );


        @chmod( $compiledFile, 0666 );
    }

    /**
     * @param $path
     * @param null $baseDir
     */
    private function _makeDirectory($path, $baseDir = null)
    {

        if ( is_dir( $path ) === true )
        {
            return;
        }

        if ( $this->chmod === null )
        {
            $chmod = 0777;
        }
        else
        {
            $chmod = $this->chmod;
        }

        mkdir( $path, $chmod, true );

        // enforce the correct mode for all directories created
        if ( $baseDir !== null && strpos( PHP_OS, 'WIN' ) !== 0 )
        {
            $path    = strtr( str_replace( $baseDir, '', $path ), '\\', '/' );
            $folders = explode( '/', trim( $path, '/' ) );
            foreach ( $folders as $folder )
            {
                $baseDir .= $folder . '/';
                chmod( $baseDir, $chmod );
            }
        }
    }

    /**
     *
     * @param string $code
     * @return string
     */
    private function patchTreeFunction($code)
    {
        preg_match_all( '#function _Tree_\d(.*)\}//ENDTree#sU', $code, $matches );
        if ( is_array( $matches[ 1 ] ) )
        {
            foreach ( $matches[ 1 ] as $str )
            {
                $newStr = str_replace( '$this->filterContent', 'Compiler::filterContent', $str );
                $newStr = str_replace( '$this->dat[', 'Compiler::$_staticData[', $newStr );
                $code   = str_replace( $str, $newStr, $code );
            }
        }

        return $code;
    }


    public static $_compiledvars = array();

    /**
     * @param string $code (reference)
     * @param null|string $tagname
     * @param null|string $attributname (reference)
     * @return mixed
     */
    public function postCompiler(&$code, $tagname = null, $attributname = null)
    {

        preg_match_all( '/\{(' . $this->_rConstante . '|' . $this->_rVariable . '|' . $this->_rFunctions . '.*\))\}/xU', $code, $match, PREG_SET_ORDER );

        $lastScope = $this->templateInstance->getCompiler()->getLastScope();
        $cnt       = count( $match );

        if ( !$lastScope )
        {
            $lastScope = '';
        }


        for ( $i = 0; $i < $cnt; ++$i )
        {
            if ( $match[ $i ][ 1 ] === ' ' || $match[ $i ][ 1 ] == '' )
            {
                //echo "\nSkip Res: ". utf8_encode($_match[ $i ][ 1 ]);
                continue;
            }

            $original = $match[ $i ][ 0 ];
            $cleaned  = $match[ $i ][ 1 ];

            $hash = md5( $lastScope . $cleaned );

            if ( $lastScope === '' )
            {
                $lastScope = null;
            }

            $str = '';
            if ( $cleaned[ 0 ] === '$' || $cleaned[ 0 ] === '@' )
            {

                $result = $this->compileVariable( $cleaned, $lastScope );

                //self::$_compiledvars[$hash] = $result;

                $str .= Compiler_Abstract::PHP_OPEN;

                if ( strpos( $result, 'constant(\'' ) === false && strpos( $result, 'User::get' ) === false && strpos( $result, 'HTTP::' ) === false && strpos( $result, 'Session::get' ) === false && strpos( $result, 'Cookie::get' ) === false
                )
                {

                        $str .= 'echo isset(' . $result . ') ? \'\'.' . $result . ' : \'\';';

                }
                else
                {

                        $str .= 'echo ' . $result . ';';

                }

                $str .= Compiler_Abstract::PHP_CLOSE;
            }
            else
            {

                $result = $this->compileExpression( $cleaned, $lastScope );
                $str .= Compiler_Abstract::PHP_OPEN;

                    $str .= 'echo ' . $result[ 0 ] . ';';


                $str .= Compiler_Abstract::PHP_CLOSE;
            }

            $code = str_replace( $original, $str, $code );
        }

        #print_r($match); exit;
        #unset( $match );

        return $code;
    }


    /**
     * @param string $code (reference)
     * @return mixed
     */
    public function postCompileVars(&$code)
    {

        preg_match_all( '/\{' . $this->_rConstante . '|' . $this->_rVariable . '\}/xU', $code, $match );

        $match[ 0 ] = array_unique( $match[ 0 ] );
        $cnt        = sizeof( $match[ 0 ] );


        for ( $i = 0; $i < $cnt; ++$i )
        {
            if ( ctype_space( $match[ 0 ][ $i ] ) || $match[ 0 ][ $i ] == '' )
            {
                continue;
            }

            $original = $match[ 0 ][ $i ];

            $match[ 0 ][ $i ] = substr( $match[ 0 ][ $i ], 1 );
            $match[ 0 ][ $i ] = substr( $match[ 0 ][ $i ], 0, -1 );

            $result = $this->compileVariable( $match[ 0 ][ $i ], null );

            $str = Compiler_Abstract::PHP_OPEN;

            $str .= 'echo (string)' . $result . ';';

            /*
            if ( strpos($result, 'constant(\'') === false && strpos($result, 'User::get') === false && strpos($result, 'HTTP::') === false && strpos($result, 'Session::get') === false && strpos($result, 'Cookie::get') === false )
            {
                $str .= "\n" . ' echo isset(' . $result . ') ? "".' . $result . ' : "";' . "\n";
            }
            else
            {
                $str .= "\n" . ' echo "".' . $result . ';' . "\n";
            }
            */

            // $str .= "\n" . ' echo (isset(' . $result . ') ? "".' . $result . ' : "");' . "\n";
            $str .= Compiler_Abstract::PHP_CLOSE;

            $code = preg_replace( "/(" . preg_quote( $original, '/' ) . ")/sU", $str, $code, 1 );
        }

        unset( $match );

        return $code;
    }

    /**
     * @param string $code (reference)
     * @param string|null $lastScope default is null
     * @param array|null $matches
     * @throws Compiler_Exception
     * @return mixed
     */
    public function postCompileFunctions(&$code, $lastScope = null, $matches = null)
    {
        if ( $matches === null )
        {
            preg_match_all( '/\{(' . $this->_rFunctions . '.*\))\}/xisU', $code, $match );

            $match[ 0 ] = array_unique( $match[ 0 ] );
            $match[ 1 ] = array_unique( $match[ 1 ] );

        }
        else
        {
            $match = $matches;
        }

        $cnt = sizeof( $match[ 0 ] );

        for ( $i = 0; $i < $cnt; ++$i )
        {
            if ( ctype_space( $match[ 0 ][ $i ] ) || $match[ 0 ][ $i ] == '' )
            {
                continue;
            }

            $original = $match[ 0 ][ $i ];
            $cleaned  = $match[ 1 ][ $i ];

            # $match[ 0 ][ $i ] = substr( $match[ 0 ][ $i ], 1 );
            # $match[ 0 ][ $i ] = substr( $match[ 0 ][ $i ], 0, -1 );

            $result = $this->compileExpression( $cleaned, $lastScope );

            if ( $result[ 0 ] == $cleaned || ( empty( $result[ 0 ] ) && $cleaned ) )
            {
                throw new Compiler_Exception( 'Invalid function: ' . $original );
            }

            $str = Compiler_Abstract::PHP_OPEN;
            $str .= 'echo ' . $result[ 0 ] . ';';
            $str .= Compiler_Abstract::PHP_CLOSE;

            $code = str_replace( $original, $str, $code );

            // $code = preg_replace( "/(" . preg_quote( $original, '/' ) . ")/sU", $str, $code, 1 );
        }


        return $code;
    }

    /**
     * @param string $text
     * @return mixed
     */
    public function parseEntities($text)
    {

        return preg_replace_callback( '/\&(([a-zA-Z\_\:]{1}[a-zA-Z0-9\_\:\-\.]*)|(\#((x[a-fA-F0-9]+)|([0-9]+))))\;/', array(
            $this,
            '_decodeEntity'
        ), $text );
        //	return htmlspecialchars_decode(str_replace(array_keys($this->_entities), array_values($this->_entities), $text));
    }

    /**
     * Smart special character replacement that leaves entities
     * unmodified. Used by parseSpecialChars().
     *
     * @internal
     * @param array $text Matching string
     * @return string Modified text
     */
    protected function _entitize(&$text)
    {

        switch ( $text[ 0 ] )
        {
            case '&':
                return '&amp;';
            case '>':
                return '&gt;';
            case '<':
                return '&lt;';
            case '"':
                return '&quot;';
            default:
                return $text[ 0 ];
        }
    }

    /**
     * @param string $text
     * @return string
     * @throws Base_Exception
     */
    protected function _decodeEntity(&$text)
    {

        switch ( $text[ 1 ] )
        {
            case 'amp':
                return '&';
            case 'quot':
                return '"';
            case 'lt':
                return '<';
            case 'gt':
                return '>';
            case 'apos':
                return "'";
            default:

                if ( isset( $this->_entities[ $text[ 1 ] ] ) )
                {
                    return $this->_entities[ $text[ 1 ] ];
                }

                if ( $text[ 1 ][ 0 ] === '#' )
                {
                    return html_entity_decode( $text[ 0 ], ENT_COMPAT, $this->templateInstance->getCompiler()->charset );
                }
                elseif ( $this->templateInstance->getCompiler()->htmlEntities && $text[ 0 ] != ( $result = html_entity_decode( $text[ 0 ], ENT_COMPAT, $this->templateInstance->getCompiler()->charset ) ) )
                {
                    return $result;
                }

                throw new Base_Exception( sprintf( 'The entity %s is not registered in the XML parser.', htmlspecialchars( $text[ 0 ] ) ) );
        }
    }

    /**
     * Replaces only specific entities &lb; and &rb; to the corresponding
     * characters.
     *
     * @param string $text Input text
     * @return string output text
     */
    public function parseShortEntities(&$text)
    {

        return str_replace( array(
            '&lb;',
            '&rb;'
        ), array(
            '{',
            '}'
        ), $text );
    }

    /**
     * Replaces the XML special characters back to entities with smart ommiting of &
     * that already creates an entity.
     *
     * @param string $text Input text.
     * @return string Output text.
     */
    public function parseSpecialChars($text)
    {
        return preg_replace_callback( '/(\&\#?[a-zA-Z0-9]*\;)|\<|\>|\"|\&/', array(
            $this,
            '_entitize'
        ), $text );
    }


    /**
     * This utility function helps removing the CDATA state from the
     * specified node and their descendants. If the extra attribute is
     * set to false, the compiler does not replace to entities the special
     * symbols. By default, they are entitized.
     *
     * @static
     * @param object $node The starting node.
     * @param boolean $entitize Replace the special symbols to entities?
     */
    static public function removeCdata($node, $entitize = true)
    {

        // Do not use true recursion.
        $queue = new SplQueue;
        $queue->enqueue( $node );
        do
        {
            $current = $queue->dequeue();

            if ( $current instanceof Compiler_CData )
            {
                if ( $current->get( 'cdata' ) === true )
                {
                    $current->set( 'cdata', false );
                }

                if ( !$entitize )
                {
                    $current->set( 'noEntitize', true );
                }

                // Add the children of the node to the queue for furhter processing
                foreach ( $current as $subnode )
                {
                    $queue->enqueue( $subnode );
                }
            }
        }
        while ( $queue->count() > 0 );
    }

    /**
     * This utility function helps removing the COMMENT state from the
     * specified node and their descendants. If the extra attribute is
     * set to false, the compiler does not replace to entities the special
     * symbols. By default, they are entitized.
     *
     * @static
     * @param object $node The starting node.
     * @param boolean $entitize Replace the special symbols to entities?
     */
    static public function removeComments($node, $entitize = true)
    {

        // Do not use true recursion.
        $queue = new SplQueue;
        $queue->enqueue( $node );
        do
        {
            $current = $queue->dequeue();

            if ( $current instanceof Compiler_CData )
            {
                if ( $current->get( 'comment' ) === true )
                {
                    $current->set( 'comment', false );
                }

                if ( !$entitize )
                {
                    $current->set( 'noEntitize', true );
                }

                // Add the children of the node to the queue for furhter processing
                foreach ( $current as $subnode )
                {
                    $queue->enqueue( $subnode );
                }
            }
        }
        while ( $queue->count() > 0 );
    }


}