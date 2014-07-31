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
 * @file         Parser.php
 */
class Compiler_Parser
{


    /**
     * The expression finding regular expression.
     * @var string
     */
    public $_rExpressionTag = '/(\{([^\}\{]*)\})/msi';

    /**
     * @var string
     */
    public $_rBacktickString = '`[^`\\\\]*(?:\\\\.[^`\\\\]*)*`';

    /**
     * @var string
     */
    public $_rSingleQuoteString = '\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'';

    /**
     * @var string
     */
    public $_rHexadecimalNumber = '\-?0[xX][0-9a-fA-F]+';

    /**
     * @var string
     */
    public $_rDecimalNumber = '[0-9]+\.?[0-9]*';

    /**
     * @var string
     */
    public $_rVariable = '\$[a-zA-Z\_][a-zA-Z0-9\-\_\.]*';

    /**
     * @var string
     */
    public $_rOperators = '\-\>|!==|===|==|!=|\=\>|<>|<<|>>|<=|>=|\&\&|\|\||\(|\)|,|\!|\^|=|\&|\~|<|>|\||\%|\+\+|\-\-|\+|\-|\*|\/|\[|\]|\.|\:\:|\{|\}|\'|\"|';

    /**
     * @var string
     */
    public $_rIdentifier = '[a-zA-Z\_][a-zA-Z0-9\_\.]*';

    // Template Cleaner
    /**
     * @var array
     */
    private static $_serachOps = array(
        '&&',
        '||',
        '<=',
        '>=',
        '<<',
        '>>',
        '<',
        '>');

    /**
     * @var array
     */
    private static $_replaceOps = array(
        ' and ',
        ' or ',
        ' lte ',
        ' gte ',
        ' shl ',
        ' shr ',
        ' lt ',
        ' gt ');

    /**
     * @var array
     */
    protected $_flatArray = array();

    /**
     * @var int
     */
    protected static $_tempIndex = 0;

    /**
     * @var null
     */
    public static $functionsArr = null;

    // Operator mappings
    /**
     * @var array
     */
    protected static $wordOperators = array(
        'eqt'  => '===',
        'eq'   => '==',
        'ne'   => '!=',
        'net'  => '!==',
        'neq'  => '!=',
        'neqt' => '!==',
        'lt'   => '<',
        'le'   => '<=',
        'lte'  => '<=',
        'gt'   => '>',
        'ge'   => '>=',
        'gte'  => '>=',
        'and'  => '&&',
        'or'   => '||',
        'xor'  => 'xor',
        'not'  => '!',
        'mod'  => '%',
        'div'  => '/',
        'add'  => '+',
        'sub'  => '-',
        'mul'  => '*',
        'shl'  => '<<',
        'shr'  => '>>'
    );

    private $_code = '';
    /**
     * @var null
     */
    protected static $_maskedJs = null;

    private static $trans = null;

    protected static $charset = '';

    /**
     * @var SplQueue
     */
    private $spl;

    /**
     * @param $charset
     * @param array $functions
     */
    public function __construct($charset, $functions = array())
    {

        #$this->spl =& $queue;

        if ( self::$trans === null )
        {
            self::$trans = array_map( 'utf8_encode', array_flip( array_diff( get_html_translation_table( HTML_ENTITIES ), get_html_translation_table( HTML_SPECIALCHARS ) ) ) );
        }

        self::$charset = $charset;

        if ( self::$functionsArr === null )
        {
            self::$functionsArr = $functions;
        }
    }

    public function __destruct()
    {
    }


    /**
     * @return array|null
     */
    private function getFunctions()
    {
        if ( is_array( self::$functionsArr ) )
        {
            return self::$functionsArr;
        }
        $f                  = Compiler_Functions::getSystemFunctions( self::$charset );
        self::$functionsArr = array_keys( $f );

        return self::$functionsArr;
    }


    public function _doCleanString()
    {

    }

    /**
     * @param $_str
     * @return string
     */
    public static function _doCleanFunctions($_str)
    {

        if ( isset( $_str[ 2 ] ) )
        {
            if ( $_str[ 2 ] === 'trans' )
            {
                $_str[ 3 ] = str_replace( '>', '-g-t-', str_replace( '<', '-l-t-', $_str[ 3 ] ) );
                $_str[ 3 ] = str_replace( '&amp;', '&', $_str[ 3 ] );
                return 'trans(' . $_str[ 3 ] . ')';
            }

            if ( !isset( self::$functionsArr[ $_str[ 2 ] ] ) )
            {
                return $_str[ 0 ];
            }

            $_str[ 3 ] = str_replace( self::$_serachOps, self::$_replaceOps, $_str[ 3 ] );

            return $_str[ 2 ] . '(' . $_str[ 3 ] . ')';
        }


        return $_str[ 0 ];
    }

    /**
     * @param $_str
     * @return string
     */
    public static function _doCleanExpression($_str)
    {


        if ( isset( $_str[ 1 ] ) && $_str[ 1 ] && $_str[ 3 ] && $_str[ 1 ] != 'trans' )
        {
            $space = substr( $_str[ 0 ], 0, 1 );
            $ret   = ( $space == ' ' ? ' ' : '' ) . $_str[ 1 ] . '=' . ( $_str[ 2 ] ? $_str[ 2 ] : '' );
            $ret .= str_replace( self::$_serachOps, self::$_replaceOps, $_str[ 3 ] );

            return $ret . ( $_str[ 2 ] ? $_str[ 2 ] : '' ) . ' ';
        }

        return $_str[ 0 ];
    }


    /**
     * @param $code
     * @return mixed
     */
    public static function jsBackwardClean(&$code)
    {
        preg_match_all( '#<script[^>]*>(.*)</script>#isU', $code, $matches );

        if ( is_array( $matches[ 1 ] ) )
        {
            $code = preg_replace( '#<script([^>]*)>(.*)</script>#isU', '<script$1>@@@SCRIPT@@</script>', $code );
            foreach ( $matches[ 1 ] as $c )
            {
                $c = str_replace( self::$_replaceOps, self::$_serachOps, $c );
                // $c    = str_replace( '#AMP##AMP#', '&&', $c );
                $code = preg_replace( '#@@@SCRIPT@@#', $c, $code, 1 );
            }

            self::$_maskedJs = null;
        }


        // html if patch
        $code = str_replace( '_~##~##', '<!--', $code );
        $code = str_replace( '~##~##_', '>', $code );
        $code = str_replace( '~E##~!', '<!', $code );
        $code = str_replace( '~##E~', '-->', $code );

        $code = str_replace( array(
            '#AMPSAN#',
            '#AMP#',
            '#_AMP#'), '&', $code );

        //$code = preg_replace('#<\!--\s*\[([^\]]*)#', '##~##$1', $code);

        return $code;
    }

    /**
     * @param $code
     * @return mixed
     */
    public static function jsCpTagsInJs(&$code)
    {
        $code = preg_replace( '#(lt\s*(/)?' . ( Compiler::TAGNAMESPACE ? Compiler::TAGNAMESPACE . '\:' : '' ) . '(.*)\s*gt)#isU', '<$2cp:$3>', $code );

        return $code;
    }

    /**
     *
     * @param array $match
     * @return string
     */
    public static function multiAttributPatch($match)
    {

        /*
          Array
          (
          [0] =>  cp:on=
          [1] => cp:
          [2] => on
          )
         */

        if ( empty( $match[ 2 ] ) )
        {
            return $match[ 0 ];
        }

        return ' ' . $match[ 1 ] . $match[ 2 ] . '-' . self::$_tempIndex++ . '=';
    }

    private function cleanCode()
    {
        // remove template comments
        // clean document type header
        $this->_code = preg_replace( '#({\*([^\*]\}*)*\*}|<\!DOCTYPE[^>]*>)#ismU', '', $this->_code );


        // clean document type header
        //$this->_code = preg_replace('#<\!DOCTYPE[^>]*>#isU', '', $this->_code );


        // html if patch
        $this->_code = preg_replace( '#<\!--\s*\[([^\]]*)]\s*>#sU', '_~##~##[$1]~##~##_', $this->_code );
        $this->_code = preg_replace( '#<\!\s*\[([^\]]*)]\s*-->#sU', '~E##~![$1]~##E~', $this->_code );


        $this->_code = str_replace( array_keys( self::$trans ), array_values( self::$trans ), $this->_code );


        // convert CDATA to Literal tag


        $this->_code = preg_replace('#/\*{1,}\n*\s*\t*<\!\[CDATA\[\n*\s*\t*\*{1,}/#', '<' . ( Compiler::TAGNAMESPACE ? Compiler::TAGNAMESPACE . ':' : '' ) . 'literal type="cdata_comment">', $this->_code);
        $this->_code = preg_replace('#/\*{1,}\n*\s*\t*\]\]>\n*\s*\t*\*{1,}/#', '</' . ( Compiler::TAGNAMESPACE ? Compiler::TAGNAMESPACE . ':' : '' ) . 'literal>', $this->_code);

        $this->_code = str_replace( '<![CDATA[', '<' . ( Compiler::TAGNAMESPACE ? Compiler::TAGNAMESPACE . ':' : '' ) . 'literal type="cdata_comment">', $this->_code );
        $this->_code = str_replace( ']]>', '</' . ( Compiler::TAGNAMESPACE ? Compiler::TAGNAMESPACE . ':' : '' ) . 'literal>', $this->_code );



#        if (strpos($this->_code, '<![CDATA[') !== false || strpos($this->_code, 'literal type=')) {
#die($this->_code);
#        }



        /**
         * Mask js scripts
         */
        preg_match_all( '#<script[^>]*>(.*)</script>#isU', $this->_code, $matches );
        $scripts = $matches;
        if ( is_array( $scripts ) && count( $scripts[ 1 ] ) )
        {
            // self::$_maskedJs = $scripts;
            $this->_code = preg_replace( '#<script([^>]*)>(.*)</script>#isU', '<script$1>@@@SCRIPT@@</script>', $this->_code );
        }




        /**
         *
         * Prepare old function expression
         */
        // '@\s*((' . implode( '|', $this->getFunctions() ) . ')\s*\(([^\)\(]+)\))@isU'
        $this->_code = preg_replace_callback( '@\s*(([a-z0-9_]{1,})\s*\(([^\)\(]+)\))@is', 'self::_doCleanFunctions', $this->_code );


        /**
         * Prepare old expression
         */
        $this->_code = preg_replace_callback( '@\s*(condition|test|use|if|is|on)\s*=\s*(["\'])(.+?)\2\s*@is', 'self::_doCleanExpression', $this->_code );





        /**
         * multi attribut patcher
         */
        $this->_code = preg_replace_callback( '@\s*(' . Compiler::TAGNAMESPACE . '\:|parse\:)(if|on|[a-zA-Z]{1,})\s*=@is', 'self::multiAttributPatch', $this->_code );

        #  preg_match_all('@\s*(' . Compiler::TAGNAMESPACE . '\:|parse\:)(if|on|[a-zA-Z]{1,})\s*=@is', $this->_code, $m);

        # print_r($m);



        $this->_code = str_replace( '&amp;', '#AMPSAN#;', $this->_code );

        $this->_code = preg_replace( '/&amp;/', '#AMPSAN#;', $this->_code );

        $this->_code = preg_replace( '/&((#[Xx][0-9A-fa-f]+|#[0-9]+|[a-zA-Z0-9]+);)/', '#_AMP#$2;', $this->_code );
        #$this->_code = preg_replace( '#(&([\#a-z0-9]+);)#is', '#_AMP#$2;', $this->_code );
        $this->_code = str_replace( '&', '&amp;', $this->_code );

        // $this->_code = str_replace( array('#AMPSAN#', '#AMP#'), '&', $this->_code );


        /**
         * Javascript patch
         */
        if ( is_array( $scripts ) && count( $scripts[ 1 ] ) )
        {
            $len = strlen('<' . ( Compiler::TAGNAMESPACE ? Compiler::TAGNAMESPACE . ':' : '' ) . 'literal type="cdata_comment">');
            $literalStart = $c = '<' . ( Compiler::TAGNAMESPACE ? Compiler::TAGNAMESPACE . ':' : '' ) . 'literal type="cdata_comment">';
            $literalEnd = '</' . ( Compiler::TAGNAMESPACE ? Compiler::TAGNAMESPACE . ':' : '' ) . 'literal>';

            foreach ( $scripts[ 1 ] as $c )
            {
                // skip empty scripts
                if (!trim($c))
                {
                    $this->_code = preg_replace( '#@@@SCRIPT@@#S', $c, $this->_code, 1 );
                    continue;
                }
                /*
                // add cdata literal
                if (strpos($c, '<![CDATA[') === false && strpos($c, ']]>') === false &&
                    strpos($c, '<' . ( Compiler::TAGNAMESPACE ? Compiler::TAGNAMESPACE . ':' : '' ) . 'literal type="') === false &&
                    strpos($c, '</' . ( Compiler::TAGNAMESPACE ? Compiler::TAGNAMESPACE . ':' : '' ) . 'literal>') === false &&
                    trim($c)) {

                    $c = '<' . ( Compiler::TAGNAMESPACE ? Compiler::TAGNAMESPACE . ':' : '' ) . 'literal type="cdata_comment">' . $c .'</' . ( Compiler::TAGNAMESPACE ? Compiler::TAGNAMESPACE . ':' : '' ) . 'literal>';
                }
                */


                if ( strpos($c, '<![CDATA[') === false && substr(trim($c), 0, $len) != $literalStart ) {
                    $c = $literalStart . $c;
                }

                if ( strpos($c, ']]>') === false && substr(trim($c), 0-strlen($literalEnd) ) !== $literalEnd ) {
                    $c .= $literalEnd;
                }

                // mask strings
                preg_match_all( '#((\'|")([^\2]*)\2)#isU', $c, $m );
                $c = preg_replace( '#((\'|")([^\2]*)\2)#isU', '@@STR@@', $c );

                // fix
                $c = str_replace( self::$_serachOps, self::$_replaceOps, $c ); //self::_doCleanExpression( array( 0 => $c ) );

                // backward masked strings
                foreach ( $m[ 0 ] as $s )
                {
                    $c = preg_replace( '#@@STR@@#U', $s, $c, 1 );
                }

                // fix ampsan
                $c = str_replace( '&&', '#AMP##AMP#', $c );
                $c = preg_replace( '#(&([\#a-z0-9]+);)#iS', '#AMP#$2;', $c );
                $c = str_replace( '&amp;', '#AMPSAN#;', $c );

                $c = str_replace( '&', '&amp;$1', $c );
                $c = str_replace( array(
                    '#AMPSAN#',
                    '#AMP#'), '&', $c );

                // fix cp tags
                $c = self::jsCpTagsInJs( $c );

                //$c    = preg_replace( '#(lt\s*(/)?cp\:literal(.*)\s*gt)#isSU', '<$2cp:literal$3>', $c );
                $this->_code = preg_replace( '#@@@SCRIPT@@#S', $c, $this->_code, 1 );
            }
        }


        $ns = Compiler::TAGNAMESPACE;
        if ( strpos( Compiler::TAGNAMESPACE, ':' ) === false )
        {
            $ns .= ':';
        }

        if ( stripos( $this->_code, 'xmlns:' . str_replace( ':', '', Compiler::TAGNAMESPACE ) . '=' ) === false )
        {

            if ( stripos( $this->_code, '<' . $ns . 'xmlroot' ) === false )
            {
                $this->_code = '<?xml version="1.0"?><xmlroot xmlns:hook="http://xml.dcms-studio.de/xsl.dtd" xmlns:cp="http://xml.dcms-studio.de/cp.dtd" xmlns:str="http://xml.dcms-studio.de/parse.dtd" xmlns:parse="http://xml.dcms-studio.de/parse.dtd" xmlns:cycle="http://xml.dcms-studio.de/cycle.dtd" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">' . $this->_code . '</xmlroot>';
            }
            else
            {
                $this->_code = preg_replace( '#^<([a-z0-9\:]*)\s([^>]*)>#isU', '<?xml version="1.0"?><$1 $2  xmlns:str="http://xml.dcms-studio.de/parse.dtd" xmlns:cp="http://xml.dcms-studio.de/cp.dtd" xmlns:parse="http://xml.dcms-studio.de/parse.dtd" xmlns:cycle="http://xml.dcms-studio.de/cycle.dtd" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">', ltrim( $this->_code ) );
            }
        }

    }


    /**
     *
     * @param string $templateCode
     * @param bool $recompile
     * @throws Compiler_Exception
     * @return \SplQueue
     */
    public function parse($templateCode, $recompile = false)
    {
        $this->_code = $templateCode;
        $this->cleanCode();


        if ( $templateCode && !$this->_code ) {
            die($templateCode);
        }

#die($this->_code);
        $reader = new XMLReader;
        $reader->xml( $this->_code );

        libxml_use_internal_errors( true );

        $reader->setParserProperty( XMLReader::VALIDATE, false );
        $reader->setParserProperty( XMLReader::SUBST_ENTITIES, true );

        $GLOBALS[ 'COMPILER_TEMPLATE' ] = $this->_code;
        /*
                if ( !$reader->isValid() )
                {
                    $errors     = libxml_get_errors();


                    exit;


                    $xml_errors = '';
                    if ( sizeof( $errors ) > 0 )
                    {

                        foreach ( $errors AS $xmlError )
                        {
                            $xml_errors .= $xmlError->message;
                        }

                        if ( $xml_errors )
                        {
                            $msg = current( $errors );
                            throw new Compiler_Exception(
                                $xml_errors . '<br/>Please check your Template!<br/>' .
                                ' @Line: ' . $msg->line
                            );
                        }
                    }
                }
        */

        // Error checking
        $errors = libxml_get_errors();
        if ( sizeof( $errors ) > 0 )
        {
            #libxml_clear_errors();

            #print_r($errors);exit;



            $xml = explode("\n", $this->_code );
            //$msg = '';
            $noerror = false;
            foreach ($errors as $error)
            {
                $x = $this->display_xml_error($error, $xml);

                if ( $x === false ) {
                    $noerror = true;
                    break;
                }
                //else $msg .= $x;
            }

            if (!$noerror) {
                throw new Compiler_Exception(
                    'Please check your Template!' //<br/><pre>' .$msg .'</pre>'
                );
            }
            else {
                libxml_clear_errors();
            }
        }

        if ( $templateCode && !$this->_code ) {
            die($templateCode);
        }
        //$this->spl = new SplQueue();

        return $this->xml2assoc( $reader );
        # $tree   = array_shift( $tree );
        /*


                print_r($spl);exit;
                $this->test();
                exit;

               # print_r($this->spl);exit;

                $GLOBALS[ 'COMPILER_TEMPLATE' ] = null;

                return $spl;

                return isset( $tree[ 'children' ] ) ? $tree[ 'children' ] : false;

        */
    }


    /**
     * @param $error
     * @param $xml
     * @return bool|string
     */
    private function display_xml_error($error, $xml)
    {
        //
        if (!isset($xml[ $error->line - 1 ]))
        {
            return false;
        }

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
            "\nLine: $error->line" .
            "\nColumn: $error->column";

        if ( $error->file )
        {
            $return .= "\nFile: $error->file";
        }

        return "$return\n\n--------------------------------------------\n\n";
    }


    /**
     *
     * @param $name
     * @return array
     * @internal param string $str
     */
    private function extractNamespace($name)
    {
        $name      = strtolower( $name );
        $nameSpace = '';
        if ( strpos( $name, ':' ) !== false )
        {
            $ns = explode( ':', $name );

            $name      = $ns[ 1 ];
            $nameSpace = $ns[ 0 ];
        }

        return array(
            'Name'      => $name,
            'nameSpace' => $nameSpace);
    }

    /**
     * @param     $xml
     * @param int $depth
     * @return SplQueue
     * @throws Compiler_Exception
     */
    private function xml2assoc(XMLReader &$xml, $depth = 0)
    {
        $tree = null;
        $spl  = new SplQueue();

        try
        {
            while ( $xml->read() )
            {

                //$this->dump_xmlreader($xml);
                switch ( $xml->nodeType )
                {
                    case XMLReader::END_ELEMENT :
                        return $spl; // $tree;
                        break;
                    case XMLReader::ELEMENT:

                        $_attrData = $this->extractNamespace( $xml->name );


                        $node = array(
                            'type'      => Compiler::TAG,
                            'tagname'   => $_attrData[ 'Name' ],
                            'namespace' => $_attrData[ 'nameSpace' ],
                            'singletag' => $xml->isEmptyElement ? true : false,
                            'isEndTag'  => false);


                        $tmpEnd = array(
                            'type'      => Compiler::TAG,
                            'tagname'   => $_attrData[ 'Name' ],
                            'namespace' => $_attrData[ 'nameSpace' ],
                            'singletag' => false,
                            'isEndTag'  => true,
                            'tag'       => '</' . ( $_attrData[ 'nameSpace' ] ? $_attrData[ 'nameSpace' ] . ':' : '' ) . $_attrData[ 'Name' ] . '>'
                        );


                        $fulltag = '<' . ( $_attrData[ 'nameSpace' ] ? $_attrData[ 'nameSpace' ] . ':' : '' ) . $_attrData[ 'Name' ];


                        if ( $xml->hasAttributes )
                        {
                            $attributes = array();
                            while ( $xml->moveToNextAttribute() )
                            {
                                if ( $xml->prefix !== 'xmlns' )
                                {
                                    $_attrData = $this->extractNamespace( $xml->name );

                                    // test expression use???
                                    $result = null;
                                    //preg_match('/(\$[a-z0-9][a-z0-9_\.\-]*|\{([\{]*)\})/i', $xml->value, $result);
                                    // revert all multi attribute patched names
                                    $_attrData[ 'Name' ] = preg_replace( '/-\d*$/', '', $_attrData[ 'Name' ] );


                                    $fulltag .= ' ' . ( $_attrData[ 'nameSpace' ] ? $_attrData[ 'nameSpace' ] . ':' : '' ) . $_attrData[ 'Name' ] . '="' . addcslashes( $xml->value, '"' ) . '"';

                                    $attributes[ ] = array(
                                        'name'  => $_attrData[ 'Name' ],
                                        'ns'    => $_attrData[ 'nameSpace' ],
                                        'value' => $xml->value,
                                        // 'useexpression' => sizeof($result) ? true : false
                                    );

                                    $result = null;
                                }
                            }

                            $xml->moveToElement();

                            $node[ 'attributes' ] = $attributes;
                        }


                        $node[ 'depth' ] = $xml->depth - 1;

                        if ( $_attrData[ 'Name' ] )
                        {
                            $node[ 'tag' ] = $fulltag . '>';
                        }


                        if ( !$xml->isEmptyElement )
                        {
                            //
                            //$tmpEnd[ 'startIndex' ] = count( $tree );

                            if ( $_attrData[ 'Name' ] === 'xmlroot' )
                            {
                                return $this->xml2assoc( $xml, $xml->depth );
                            }


                            $c = $this->xml2assoc( $xml, $xml->depth );
                            $c->rewind();

                            $node[ 'children' ] = $c;

                            if ( /*!is_array( $node[ 'children' ] )*/
                            !$c->count()
                            )
                            {
                                unset( $node[ 'children' ] );
                            }
                        }

                        ## $tree[ ] = $node;


                        $spl->push( $node );

                        if ( !$xml->isEmptyElement )
                        {

                            $spl->push( $tmpEnd );

                            ##$tree[ ] = $tmpEnd;
                        }

                        break;

                    case XMLReader::COMMENT :

                        /*
                        $tree[ ] = array(
                            'depth' => $xml->depth - 1,
                            'type'  => Compiler::COMMENT,
                            'value' => $xml->value
                        );

                        */

                        $spl->push( array(
                            'depth' => $xml->depth - 1,
                            'type'  => Compiler::COMMENT,
                            'value' => $xml->value
                        ) );

                        break;


                    case XMLReader::WHITESPACE :
                    case XMLReader::SIGNIFICANT_WHITESPACE :
                    case XMLReader::TEXT :


                        $spl->push( array(
                            'depth'         => $xml->depth - 1,
                            'type'          => Compiler::TEXT,
                            'value'         => $xml->value,
                            'useexpression' => false
                        ) );
                        #   if (preg_match($this->_rExpressionTag, $xml->value))
                        # {
                        #       $this->_treeTextCompile($xml->value, $tree, false, $xml->depth - 1, TemplateCompiler::TEXT);
                        #    }
                        #   else
                        #    {

                        /*
                        $tree[ ] = array(
                            'depth'         => $xml->depth - 1,
                            'type'          => Compiler::TEXT,
                            'value'         => $xml->value,
                            'useexpression' => false
                        );
                    */

                        #    }

                        break;
                    case XMLReader::CDATA :


                        $spl->push( array(
                            'depth'         => $xml->depth - 1,
                            'type'          => Compiler::CDATA,
                            'value'         => $xml->value,
                            'useexpression' => false
                        ) );
                        /*
                        $tree[ ] = array(
                            'depth'         => $xml->depth - 1,
                            'type'          => Compiler::CDATA,
                            'value'         => $xml->value,
                            'useexpression' => false
                        );
                        */
                        break;
                }
            }
        }
        catch ( Exception $e )
        {
            throw new Compiler_Exception(
                $e->getMessage() . '<br/>Please check your Template!<br/>@Line: ' . $e->getLine()
            );
        }

        return $spl;
    }


}