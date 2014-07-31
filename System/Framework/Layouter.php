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
 * @file        Layouter.php
 *
 */
class Layouter extends Controller
{

    /**
     * Current object instance (do not remove)
     * @var object
     */
    protected static $objInstance = null;

    /**
     * @var string
     */
    public $styleGuide = '';

    /**
     * @var null
     */
    public $templatePath = null;

    /**
     * @var null
     */
    protected $layoutCachePath = null;

    /**
     * @var null
     */
    public $layoutCacheFilePath = null;

    /**
     * @var null
     */
    public $sectionPath = null;

    /**
     * @var array
     */
    public $styleGuideLines = array();

    /**
     * @var array
     */
    public $styleGuideLinesOrig = array();

    /**
     * @var array
     */
    protected $systemTplTags = array();

    /**
     * caches
     */
    protected $layoutData = array();

    /**
     * @var null
     */
    protected $layoutBlocks = null;

    /**
     * @var null
     */
    protected $layoutBlocksData = null;

    /**
     * This is a variable holding the XPath object for the document
     * @var DomXpath
     */
    private $xpath = null;

    /**
     * This is the full path to style guide HTML file
     * @var string
     */
    private $styleFile = '';

    /**
     * @var null
     */
    protected static $_layoutBlocks = null;

    /**
     * @var null
     */
    protected static $_layoutBlocksData = null;

    /**
     * Return the current object instance (Singleton)
     * @return object
     */
    public static function getInstance()
    {
        if ( self::$objInstance === null )
        {
            self::$objInstance = new Layouter();
        }
        return self::$objInstance;
    }

    public function __destruct()
    {
        parent::__destruct();
        self::$objInstance = null;
    }

    /**
     * The class constructor
     *
     * Calls the parent constructor
     */
    public function init( $layoutTemplate = null )
    {
        $this->layoutCachePath = DATA_PATH . 'cache/layout';
        $this->layoutCacheFilePath = $this->layoutCachePath . '/layout-styled.html';
        $this->sectionPath = DATA_PATH . 'cache/layout/sections/';
        $this->templatePath = DATA_PATH . 'layouts/';

        if ( is_null( $layoutTemplate ) )
        {
            $this->styleFile = $this->templatePath . '/layout.html';
        }
        else
        {

            if ( strpos( $layoutTemplate, '_html5' ) !== false )
            {
                // $layoutTemplate = str_replace('_html5', '', $layoutTemplate);
            }

            $this->layoutCacheFilePath = $this->layoutCachePath . '/' . $layoutTemplate . '-styled.html';
            $this->styleFile = $this->templatePath . '/' . $layoutTemplate . '.html';
        }
    }

    /**
     *
     * @return string
     */
    public function setStyleFile()
    {
        return $this->styleFile;
    }

    /**
     * This function cleans up the HTML returns by DomDocument as its not 100% clean. It messes up BR tags, link tags, meta tags, etc.
     *
     * @param string $html The HTML to be cleaned up
     * @param boolean $final This should be true if we're passing for the last time before output.
     *                       This option ensures that the less than and greater than in the template system code are not left as HTML entities
     * @return mixed
     */
    public static function cleanUpHTML( $html, $final = false )
    {

        // DOMDocument likes to add separate closing tags for all tags, even those in HTML that are self closing.
        // Convert these tags to be self closing.
        $selfClosingTags = array(
            'base',
            'meta',
            'link',
            'frame',
            'hr',
            'br',
            'basefont',
            'param',
            'img',
            'area',
            'input',
            'isindex',
            'col' );
        foreach ( $selfClosingTags as $tagToFix )
        {
            $html = preg_replace( '#<' . $tagToFix . '([^>]*)>([^<]*)</' . $tagToFix . '>#ismU', '<' . $tagToFix . '$1 />', $html );
            $html = preg_replace( '#<' . $tagToFix . '([^>]*)([^/])>#ism', '<' . $tagToFix . '$1$2 />', $html );
            $html = str_replace( '</' . $tagToFix . '>', '', $html );
        }

        // Clean up any remaining BR tags
        $html = preg_replace( '#<br([^>/]*)>#ismU', '<br$1 />', $html );

        if ( $final === true )
        {
            // This should only be called if this is the last action before saving the content to files.
            // It may otherwise cause problems if it is repeatedly called

            preg_match_all( '#\{([^\}]*)(&gt;|&lt;)([^\}]*)\}#ismU', $html, $matches );

            foreach ( $matches[ 0 ] as $k => $fullCode )
            {
                $new = str_replace( array(
                    '&gt;',
                    '&lt;',
                    '&amp;&amp;' ), array(
                    '>',
                    '<',
                    '&&' ), $fullCode );
                $html = str_replace( $fullCode, $new, $html );
            }

            // Dirty hacks for IE 6 which doesn't play nice with **whitespace** in and between list items
            $html = preg_replace( '#<([/])?(ul|li|a)([^>]*)>([ \s\n\r\t]*){#im', '<$1$2$3>{', $html );
            $html = preg_replace( '#}([ \s\n\r\t]*)<([/])?(ul|li|a)([^>]*)>#im', '}<$2$3$4>', $html );
            $html = preg_replace( '#</cp:if>([ \s\n\r\t]*)</cp:loop>#im', '</cp:if></cp:loop>', $html );
            $html = preg_replace( '#}([ \s\n\r\t]*)\<cp:if([^\>]*)+\>([ \s\n\r\t]*)<li#im', '}<cp:if$2><li', $html );

            // Revert entities that have been generated inside template code
            // e.g. {if $foo && $bar} would end up like {if $foo &amp;&amp; $bar}
            $html = str_replace( '&amp;&amp;', '&&', $html );

            //	Put links on a single line so spaces are not generated by the browser before and/or after the linked content.
            $html = preg_replace( '#<a([^>]*)>[\s\n\t\r ]*(.*)(?-U)[\s\n\t\r ]*</a>#im', '<a$1>$2</a>', $html );
            $html = preg_replace( '#<a([^>]*)>[\s\n\t\r ]*<#im', '<a$1><', $html );
            $html = preg_replace( '#[\s\n\t\r ]*</a>#im', '</a>', $html );

            // Revert the URL encoding of template code.
            // Using href="{$foo}" would end up like href="%7B%24foo%7D"
            if ( preg_match_all( "/(src|href)=([\"'])(.*?%7B%24.*?%7D.*?)\\2/im", $html, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE ) )
            {
                //	this finds all src and href attributes that have both {$ and } in them at least once and reverses the encoding for these characters specifically
                //	go backwards through the matches so the offsets don't change
                $matches = array_reverse( $matches );

                foreach ( $matches as $match )
                {
                    $replaceLength = strlen( $match[ 0 ][ 0 ] );
                    $replaceOffset = $match[ 0 ][ 1 ];

                    $replaceUrl = strtr( $match[ 3 ][ 0 ], array(
                        '%7B' => '{',
                        '%24' => '$',
                        '%7D' => '}',
                            ) );

                    $replaceWith = $match[ 1 ][ 0 ] . '=' . $match[ 2 ][ 0 ] . $replaceUrl . $match[ 2 ][ 0 ];

                    $html = substr_replace( $html, $replaceWith, $replaceOffset, $replaceLength );
                }
            }

            // remove system classes
            // $html = preg_replace('#tplvar-[0-9a-zA-Z\-]*#im','', $html);
            // $html = preg_replace('#tplsection-[0-9a-zA-Z\-]*#im','', $html);
            // $html = preg_replace('#tplcond-[0-9a-zA-Z\-]*#im','', $html);
        }
        $html = preg_replace( '#<hook #im', '<cp:hook ', $html );
        $html = preg_replace( '#<systag#im', '<xsl:systag', $html );

        return $html;
    }

    /**
     *
     * @return string
     */
    public function processStyle()
    {
        $stopExecution = false;
        $returnXmlErrors = array();

        // This lets us capture the XML validation errors so we can display them neatly to the user
        libxml_use_internal_errors( true );

        $xhtml = new DOMDocument( '1.0', 'http://www.w3.org/1999/XSL/Transform' );
        $xhtml->preserveWhiteSpace = true;

        $xhtml->loadXML( $this->styleGuide );
        $xmlErrors = @libxml_get_errors();

        $xhtml->formatOutput = true;

        if ( sizeof( $xmlErrors ) > 0 )
        {

            foreach ( $xmlErrors as $k => $error )
            {
                if ( $error->code === '513' )
                {
                    // 513 is ID <your-id> already defined
                    continue;
                }
                $thisError = $errorType = '';
                $line = $error->line;

                switch ( $error->level )
                {
                    case LIBXML_ERR_WARNING:
                        $errorType = 'Warning ' . $error->code . ': ';
                        break;
                    case LIBXML_ERR_ERROR:
                        $errorType = 'Error ' . $error->code . ': ';
                        break;
                    case LIBXML_ERR_FATAL:
                        $stopExecution = true;
                        $errorType = 'Fatal Error ' . $error->code . ': ';
                        break;
                }

                $row = $this->styleGuideLines[ $line - 1 ];
                $startLen = strlen( $row );
                $endLen = strlen( ltrim( $row ) );
                $diffLen = $startLen - $endLen;

                $newLine = array_search( $row, $this->styleGuideLinesOrig );

                $thisError = "<b>" . $errorType . " </b> <span>" . $error->message . "</span><br /><b>Location:</b> <i>Line " . ($newLine + 1) . ", Column " . $error->column . "</i><br />";
                $row = htmlspecialchars( substr( $row, 0, $error->column ), ENT_QUOTES, 'utf-8' ) . '<span class="validation-error-chars">' . htmlspecialchars( substr( $row, $error->column, 1 ), ENT_QUOTES, 'utf-8' ) . '</span>' . htmlspecialchars( substr( $row, $error->column + 1 ), ENT_QUOTES, 'utf-8' );
                $thisError .= '<pre class="validation-error-code">' . trim( $row ) . "\n" . str_repeat( ' ', ($error->column - $diffLen ) ) . "^\n" . '</pre>';
                $returnXmlErrors[] = $thisError;
            }
        }

        libxml_clear_errors();


        $returnXmlErrors = array_reverse( $returnXmlErrors );
        if ( $stopExecution )
        {
            return $returnXmlErrors;
        }


        $this->xpath = new DOMXPath( $xhtml );
        $this->xpath->registerNamespace( 'xsl', 'http://www.w3.org/1999/XSL/Transform' );
        $this->xpath->registerNamespace( 'cp', 'http://www.w3.org/1999/cp/Transform' );
        $classSearch = 'tplcond-hideif-';
        $hideIf = $this->xpath->query( '//*[contains(concat(\' \', @class, \' \'), \' ' . $classSearch . '\')]' );

        if ( $hideIf->length > 0 )
        {
            foreach ( $hideIf as $thisNode )
            {
                $thisAttr = DomHelper::getAttributes( $thisNode );

                if ( !isset( $thisAttr[ 'class' ] ) )
                {
                    continue;
                }

                $classList = preg_split( '#\s+#', $thisAttr[ 'class' ], -1, PREG_SPLIT_NO_EMPTY );

                if ( empty( $classList ) )
                {
                    continue;
                }

                foreach ( $classList as $thisClass )
                {
                    if ( substr( $thisClass, 0, strlen( $classSearch ) ) === $classSearch )
                    {
                        $columns = substr( $thisClass, strlen( $classSearch ) );
                        $columnList = preg_split( '#\-#', $columns, -1, PREG_SPLIT_NO_EMPTY );

                        if ( empty( $columnList ) )
                        {
                            continue;
                        }

                        foreach ( $columnList as $key => $val )
                        {
                            $columnList[ $key ] = str_replace( 'columns', '', $columnList[ $key ] );
                            $columnList[ $key ] = str_replace( 'column', '', $columnList[ $key ] );
                            $columnList[ $key ] = (int) $columnList[ $key ];
                            if ( $columnList[ $key ] < 1 )
                            {
                                unset( $columnList[ $key ] );
                            }
                        }

                        if ( empty( $columnList ) )
                        {
                            continue;
                        }

                        DomHelper::WrapElement( $thisNode, ' <cp:if condition="!in_array($layout.columnCount, array(' . implode( ', ', $columnList ) . '))">', '</cp:if>' );
                    }
                }
            }
        }


        $sectionsList = array(
            'top',
            'customtop',
            'bottom',
            'custombottom',
            'middle',
            'right',
            'left' );

        foreach ( $sectionsList as $thisSection )
        {
            $thisBaseSectionClass = 'tplsect-' . $thisSection;
            $sectionXPath = $this->xpath->query( '//*[contains(concat(\' \', @class, \' \'), \' ' . $thisBaseSectionClass . '\')]' );
            if ( $sectionXPath->length === 0 )
            {
                continue;
            }

            foreach ( $sectionXPath as $thisSectionElement )
            {
                $thisAttr = DomHelper::getAttributes( $thisSectionElement );

                if ( !isset( $thisAttr[ 'class' ] ) )
                {
                    continue;
                }

                $classList = preg_split( '#\s+#', $thisAttr[ 'class' ], -1, PREG_SPLIT_NO_EMPTY );

                if ( empty( $classList ) )
                {
                    continue;
                }

                $thisContent = DomHelper::innerHTML( $thisSectionElement );


                $indent = preg_replace( "#\r\n#", "\n", $thisContent );
                $indent = preg_replace( "#^\n*#", '', $indent );
                $indent = preg_replace( "#^(\s*\t*).*#s", '$1', $indent );


                foreach ( $classList as $thisClass )
                {

                    $replaceStart = "\n" . $indent . '<xsl:hook name="Before' . ucfirst( strtolower( $thisSection ) ) . '" />' . "\r\n\r\n";
                    $replaceMiddle = "\n" . $thisContent . "\n"; /* . $indent . '{$layout.' . $thisSection . 'Blocks}' . "\n"; */
                    $replaceEnd = $indent . '<xsl:hook name="After' . ucfirst( strtolower( $thisSection ) ) . '" />' . "\r\n\r\n";


                    $replaceValue = $replaceStart . $replaceMiddle . $replaceEnd;

                    #$newElement = DomHelper::HTML2Element($replaceValue, $xhtml);

                    if ( in_array( $thisClass, array(
                                $thisBaseSectionClass,
                                $thisBaseSectionClass . '-inside' ) )
                    )
                    {

                        #$newElement

                        DomHelper::replaceInnerHTML( $thisSectionElement, $replaceValue );


                        //DomHelper::insertHTMLBefore($replaceValue, $thisSectionElement->firstChild);
                    }
                    elseif ( substr( $thisClass, 0, strlen( $thisBaseSectionClass ) ) === $thisBaseSectionClass )
                    {
                        // we have a base class name
                        $positions = preg_split( '#\-#', substr( $thisClass, strlen( $thisBaseSectionClass ) ), -1, PREG_SPLIT_NO_EMPTY );

                        if ( empty( $positions ) )
                        {
                            DomHelper::insertHTMLBefore( $replaceValue, $thisSectionElement->firstChild );
                            continue;
                        }

                        $where = 'prepend';

                        if ( in_array( strtolower( $positions[ 0 ] ), array(
                                    'after',
                                    'before',
                                    'inside',
                                    'prepend',
                                    'append',
                                    'replace' ) )
                        )
                        {
                            $where = $positions[ 0 ];
                            unset( $positions[ 0 ] );
                        }

                        if ( empty( $positions ) )
                        {
                            $positions[] = 'all';
                        }

                        if ( !in_array( $where, array(
                                    'before',
                                    'append',
                                    'replace' ) )
                        )
                        {
                            $positions = array_reverse( $positions );
                        }

                        foreach ( $positions as $thisPosition )
                        {
                            if ( preg_match( '#^block([1-9][0-9]*)$#i', trim( $thisPosition ), $match ) )
                            {
                                if ( isset( $match[ 1 ] ) && (int) $match[ 1 ] > 0 )
                                {
                                    $replaceValue = '{$layout.' . strtolower( $thisSection ) . 'Block' . (int) $match[ 1 ] . '}';
                                }
                            }

                            switch ( $where )
                            {
                                case 'inside':
                                    DomHelper::replaceInnerHTML( $thisSectionElement, $replaceValue );

                                    $where = 'prepend';
                                    break;
                                case 'after':
                                    DomHelper::insertHTMLAfter( $replaceValue, $thisSectionElement );
                                    break;
                                case 'before':
                                    DomHelper::insertHTMLBefore( $replaceValue, $thisSectionElement );
                                    break;
                                case 'prepend':
                                    DomHelper::insertHTMLBefore( $replaceValue, $thisSectionElement->firstChild );
                                    break;
                                case 'append':
                                    DomHelper::insertHTMLAfter( $replaceValue, $thisSectionElement->lastChild );
                                    break;
                                case 'replace':
                                    DomHelper::insertHTMLBefore( $replaceValue, $thisSectionElement );
                                    break;
                            }
                        }

                        if ( $where === 'replace' )
                        {
                            $thisSectionElement->parentNode->removeChild( $thisSectionElement );
                        }
                    }
                }
            }
        }


        // check for conditionals
        $classSearch = 'tplcond-addcolumncounttoclass-';
        $condList = $this->xpath->query( '//*[contains(concat(\' \', @class, \' \'), \' ' . $classSearch . '\')]' );

        if ( $condList->length > 0 )
        {
            foreach ( $condList as $thisNode )
            {
                $thisAttr = DomHelper::getAttributes( $thisNode );

                if ( !isset( $thisAttr[ 'class' ] ) )
                {
                    continue;
                }

                $classList = preg_split( '#\s+#', $thisAttr[ 'class' ], -1, PREG_SPLIT_NO_EMPTY );

                if ( empty( $classList ) )
                {
                    continue;
                }

                foreach ( $classList as $thisClass )
                {
                    if ( substr( $thisClass, 0, strlen( $classSearch ) ) === $classSearch )
                    {
                        $className = substr( $thisClass, strlen( $classSearch ) );
                        $thisNode->setAttribute( 'class', $thisNode->getAttribute( 'class' ) . ' ' . $className . '{$layout.columnCount}' );
                    }
                }
            }
        }


        $conditions = array(
            1 => 'tplcond-if1column-addclass-',
            2 => 'tplcond-if2columns-addclass-',
            3 => 'tplcond-if3columns-addclass-', );

        foreach ( $conditions as $colCount => $classSearch )
        {
            // check for conditionals
            $condList = $this->xpath->query( '//*[contains(concat(\' \', @class, \' \'), \' ' . $classSearch . '\')]' );

            if ( $condList->length > 0 )
            {
                foreach ( $condList as $thisNode )
                {
                    $thisAttr = DomHelper::getAttributes( $thisNode );

                    if ( !isset( $thisAttr[ 'class' ] ) )
                    {
                        continue;
                    }

                    $classList = preg_split( '#\s+#', $thisAttr[ 'class' ], -1, PREG_SPLIT_NO_EMPTY );

                    if ( empty( $classList ) )
                    {
                        continue;
                    }

                    foreach ( $classList as $thisClass )
                    {
                        if ( substr( $thisClass, 0, strlen( $classSearch ) ) === $classSearch )
                        {
                            $className = substr( $thisClass, strlen( $classSearch ) );
                            $thisNode->setAttribute( 'class', $thisNode->getAttribute( 'class' ) . ' <cp:if "$layout.columnCount === ' . $colCount . '">' . $className . '</cp:if>' );
                        }
                    }
                }
            }
        }

        $xml = $xhtml->saveXML();
        /**
         * repare empty block tags
         */
        $xml = $this->cerrarTag( 'div', $xml );
        $xml = $this->cerrarTag( 'span', $xml );
        $xml = $this->cerrarTag( 'p', $xml );
        $xml = $this->cerrarTag( 'ins', $xml );
        $xml = $this->cerrarTag( 'h1', $xml );
        $xml = $this->cerrarTag( 'h2', $xml );
        $xml = $this->cerrarTag( 'h3', $xml );
        $xml = $this->cerrarTag( 'h4', $xml );
        $xml = $this->cerrarTag( 'h5', $xml );
        $xml = $this->cerrarTag( 'h6', $xml );
        $xml = $this->cerrarTag( 'em', $xml );
        $xml = $this->cerrarTag( 'strong', $xml );
        $xml = $this->cerrarTag( 'ul', $xml );
        $xml = $this->cerrarTag( 'li', $xml );


        /**
         * remove temporary css class names
         */
        foreach ( $sectionsList as $name )
        {
            $xml = preg_replace( '#(\s*)tplsect-' . $name . '-(inside|after|before|prepend|append|replace)#i', '', $xml );
        }


        // prepare for saving and then save
        $html = self::cleanUpHTML( $xml, true );
        return $html;
    }

    /**
     * @param $tag
     * @param $xml
     * @return mixed
     */
    private function cerrarTag( $tag, $xml )
    {
        $indice = 0;
        while ( $indice < strlen( $xml ) )
        {
            $pos = strpos( $xml, "<$tag ", $indice );
            if ( $pos )
            {
                $posCierre = strpos( $xml, ">", $pos );
                if ( $xml[ $posCierre - 1 ] === "/" )
                {
                    $xml = substr_replace( $xml, "></$tag>", $posCierre - 1, 2 );
                }
                $indice = $posCierre;
            }
            else
                break;
        }
        return $xml;
    }

    /**
     * @param string $content
     * @return string
     */
    private function doMask( $content = '' )
    {
        if ( !$content )
        {
            return ' op=""';
        }
        $content = str_replace( array(
            '>',
            '<' ), array(
            '__gt',
            '__lt' ), $content );
        return ' op="' . $content . '"';
    }

    /**
     * @param $code
     * @return mixed
     */
    private function maskIfOperators( $code )
    {
        return preg_replace( '/([ ]op[ ]*=[ ]*(["\'])([^"\']*)\\2)/eisU', "\$this->doMask('\\3')", $code );
    }

    /**
     * @param string $condition
     * @param string $before
     * @param string $after
     * @param bool   $mask
     * @return string
     */
    private function doMaskCondition( $condition = '', $before = '', $after = '', $mask = false )
    {
        if ( $mask === true )
        {
            $condition = str_replace( array(
                '>',
                '<',
                ' || ',
                ' && ',
                ' xor ',
                ' or ',
                ' and ' ), array(
                '__gt',
                '__lt',
                ' _|_|_ ',
                ' _AMP_AMP_ ',
                ' _X_OR_ ',
                ' _O_R_ ',
                ' _A_ND_ ' ), $condition );
        }
        return $before . '"' . $condition . '"';
    }

    /**
     * @param $code
     * @return mixed
     */
    private function unmaskCondition( $code )
    {
        return str_replace( array(
            '__gt',
            '__lt',
            '_|_|_',
            '_AMP_AMP_',
            '_X_OR_',
            '_O_R_',
            '_A_ND_' ), array(
            '>',
            '<',
            '||',
            '&&',
            'xor',
            'or',
            'and' ), $code );
    }

    /**
     * @param $content
     * @return mixed
     */
    private function unmask_IfOperatorsInAttVal( $content )
    {
        return str_replace( array(
            '__gt',
            '__lt' ), array(
            '>',
            '<' ), $content );
    }

    /**
     * @param $code
     * @return mixed
     */
    private function maskCondition( $code )
    {
        return preg_replace( '/(([ ]condition[ ]*=[ ]*)(["])([^"]*)\\3)/eisU', '$this->doMaskCondition(\'$4\', \'$2\', \'"\', true)', $code );
    }

    /**
     * Convert Template Tags to XSL
     * @param type $code
     * @return type
     */
    private function cleanSystemTemplateTags( $code )
    {
        $code = $this->maskCondition( $code );
        $code = $this->maskIfOperators( $code );

        preg_match_all( "|(</?cp:[^>]+?/?+>)|isU", $code, $foo, PREG_OFFSET_CAPTURE );


        $foo = $foo[ 0 ];
        $folen = sizeof( $foo );

        for ( $i = 0; $i < $folen; $i++ )
        {
            $tag = $foo[ $i ][ 0 ];

            $this->systemTplTags[] = $tag;


            // Prüfen ob EndTag vorhanden oder nicht
            $et = substr( trim( $tag ), 0, 2 );
            $st = substr( rtrim( $tag ), 0, 2 );

            $end = '';
            if ( $et === '</' )
            {
                $end = '/';
            }

            $single = '';
            if ( preg_match( '/\/\s*>$/', $tag ) )
            {
                $single = ' /';
            }


            $code = preg_replace( '#' . preg_quote( $tag, '#' ) . '#U', '<' . $end . 'xsl:SYSTAG' . $single . '>', $code, 1 );
        }


        return $code;
    }

    /**
     * Reconvert XSL (<xsl:SYSTAG>) to Template Tags
     * @param type $code
     * @return type
     */
    private function uncleanSystemTemplateTags( $code )
    {
        preg_match_all( "|</?xsl:SYSTAG/?>|is", $code, $foo, PREG_OFFSET_CAPTURE );
        $foo = $foo[ 0 ];
        $folen = sizeof( $foo );
        for ( $i = 0; $i < $folen; $i++ )
        {
            $tag = $this->systemTplTags[ $i ];
            $masktag = $foo[ $i ][ 0 ];
            $code = preg_replace( '|' . preg_quote( $masktag, '|' ) . '|sU', $tag, $code, 1 );
        }
        return $code;
    }

    /**
     * @param $tagname
     * @param $code
     * @return string
     */
    private function getTag( $tagname, $code )
    {
        if ( !is_string( $code ) )
        {
            return '';
        }
        preg_match( '/(<' . $tagname . '([^>]*)>)/U', $code, $_matches );
        return $_matches[ 0 ];
    }

    /**
     * @param $incode
     * @return string
     */
    private function getBlockContentFromString( $incode )
    {
        $current_tagparms[ 'tag' ] = $this->getTag( 'body', $incode );
        $current_tagparms[ 'tagname' ] = 'body';


        $prefix = '';
        $isSingleTag = false;
        $tag = trim( $current_tagparms[ 'tag' ] );
        $tag = str_replace( '<' . $prefix . $current_tagparms[ 'tagname' ], '', $tag );
        $tag = substr( $tag, 0, -1 );

        $object_pieces = explode( '<' . $prefix . $current_tagparms[ 'tagname' ] . $tag, $incode, 2 );
        $code = array_shift( $object_pieces );

        $inBlockOpen = substr_count( $object_pieces[ 0 ], '<' . $prefix . $current_tagparms[ 'tagname' ] );
        $inBlockClose = substr_count( $object_pieces[ 0 ], '</' . $prefix . $current_tagparms[ 'tagname' ] . '>' );

        $totalOpen = ($inBlockOpen > 0 ? $inBlockClose - $inBlockOpen : 0);
        $allopened = 0;
        $retCode = '';
        $x = 1;

        $retOpen = 1;
        $retClosed = 0;

        foreach ( $object_pieces as $object_piece )
        {
            list ($loopattr, $end) = explode( '>', $object_piece, 2 );

            $pos2 = stripos( $end, '</' . $prefix . $current_tagparms[ 'tagname' ] );
            $testStr = substr( $end, 0, $pos2 );

            $closeCounter = substr_count( $end, '</' . $prefix . $current_tagparms[ 'tagname' ] );
            $matches = array();
            $opens = substr_count( $testStr, '<' . $prefix . $current_tagparms[ 'tagname' ] );
            $loops = explode( '</' . $prefix . $current_tagparms[ 'tagname' ] . '>', $end );

            if ( $opens > 0 )
            {
                $opens = substr_count( $end, '<' . $prefix . $current_tagparms[ 'tagname' ] );

                for ( $i = 0; $i <= $opens; $i++ )
                {
                    $retCode .= array_shift( $loops ) . '</' . $prefix . $current_tagparms[ 'tagname' ] . '>';
                }
            }
            else
            {
                $retCode = array_shift( $loops ) . '</' . $prefix . $current_tagparms[ 'tagname' ] . '>';
            }
        }


        // entferne den letzten CP Tag
        $retCode = rtrim( $retCode );
        if ( substr( $retCode, -strlen( '</' . $prefix . $current_tagparms[ 'tagname' ] . '>' ) ) === '</' . $prefix . $current_tagparms[ 'tagname' ] . '>' )
        {
            $retCode = substr( $retCode, 0, -strlen( '</' . $prefix . $current_tagparms[ 'tagname' ] . '>' ) );
        }


        return $retCode;
    }

    /**
     * This function loads in the styleguide html into a member variable called $this->styleGuide
     *
     * @param boolean $force Set this to true to force a refresh of the HTML, otherwise if the variable is already set it won't be reloaded
     * @return void
     */
    public function loadStyleGuide( $force = false )
    {
        if ( ($this->styleGuide == '' || $force === true) && is_file( $this->styleFile ) )
        {
            $this->styleGuide = @file_get_contents( $this->styleFile );

            $this->styleGuide = $this->getBlockContentFromString( $this->styleGuide );
            $this->styleGuide = $this->cleanSystemTemplateTags( $this->styleGuide );

            $this->styleGuide = str_replace( "&", '__AMPSAN__', $this->styleGuide );
            $this->styleGuide = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><root xmlns:str="http://xml.dcms-studio.de/parse.dtd" xmlns:cp="http://xml.dcms-studio.de/cp.dtd" xmlns:parse="http://xml.dcms-studio.de/parse.dtd" xmlns:cycle="http://xml.dcms-studio.de/cycle.dtd" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><body>' . $this->styleGuide . '</body></root>';


            $tmp = str_replace( "\r\n", "\n", $this->styleGuide );
            $tmp = str_replace( "\r", "\n", $tmp );


            $this->styleGuideLinesOrig = explode( "\n", $tmp );

            $this->styleGuide = preg_replace( '#<\!--\{ignore\}-->.*<\!--\{/ignore\}-->#ismU', '', $this->styleGuide );
            $this->styleGuide = preg_replace( '#<\!--\{ignore/\}(.*)-->#ismU', '', $this->styleGuide );

            // there are problems processing the HTML into a DOMDocument when the charset isn't valid
            // so we need to manually convert it here
            $this->styleGuide = str_ireplace( '{$site.charset}', 'utf-8', $this->styleGuide );
            $tmp = str_replace( "\r\n", "\n", $this->styleGuide );
            $tmp = str_replace( "\r", "\n", $tmp );
            $this->styleGuideLines = explode( "\n", $tmp );
        }
    }

    /**
     * @param $code
     * @return bool
     */
    public function saveProcessedStyle( $code )
    {
        $styleGuide = file_get_contents( $this->styleFile );


        // body Code
        $code = $this->getBlockContentFromString( $code );
        $code = $this->uncleanSystemTemplateTags( $code );

        // replace only the body
        $styleGuideBody = $this->getBlockContentFromString( $styleGuide );


        // final html code
        $code = str_replace( $styleGuideBody, $code, $styleGuide );
        $code = str_replace( '__AMPSAN__', "&", $code );


        Library::makeDirectory( $this->layoutCachePath );
        file_put_contents( $this->layoutCacheFilePath, $code );

        return true;
    }

    /**
     * This function extracts any style tags within the head section of the style guide and returns them
     *
     * @return string The style tags from within the styleguide file
     */
    public function getStyle()
    {
        $this->loadStyleGuide();
        libxml_use_internal_errors( FALSE );

        try
        {
            $xml = new SimpleXml( $this->styleGuide );
            $xml = (string) $xml->head->style->asXml();
        }
        catch ( Exception $e )
        {
            $xml = '';
        }

        return $xml;
    }

    /**
     *          Functions used in class Frontend
     */

    /**
     * get the current block
     *
     * @param array $settings
     * @param array $blockcontentdata
     * @internal param string $blockname
     * @return array
     */
    private function processBlock( $settings, $blockcontentdata )
    {
        if ( !$blockcontentdata[ 'visible' ] )
        {
            return array();
        }

        $type = $blockcontentdata[ 'type' ];
        $blockname = $blockcontentdata[ 'blockname' ];

        $blockdata[ 'attributes' ] = $blockcontentdata;


        $types = explode( '_', $blockname );
        $type = strtolower( $types[ 0 ] );

        $data = array();


        switch ( $type )
        {
            case 'html-content':
            case 'other-content':
                $data[] = '<div class="layout-box ' . $type . '">' . $blockcontentdata[ 'value' ] . '</div>';
                break;
            case 'php-content':
                $data[] = '<div class="layout-box php-content">' . $blockcontentdata[ 'value' ] . '</div>';
                break;
            case 'modul':
                array_pop( $types );


                $blockName = array_pop( $types );
                $moduleName = str_replace( 'modul_', '', strtolower( $types[ 1 ] ) );
                $model = Model::getModelInstance( $moduleName );

                if ( $model->hasBlocks )
                {
                    $moduleBlocks = $model->getLayoutBlocks();

                    if ( is_array( $moduleBlocks ) && !empty( $moduleBlocks ) )
                    {
                        foreach ( $moduleBlocks as $key => $thisBlock )
                        {
                            if ( strtolower( $thisBlock[ 'id' ] ) === strtolower( $blockName ) )
                            {
                                $options = ($blockcontentdata[ 'value' ] !== '' ? Library::unserialize( $blockcontentdata[ 'value' ] ) : null);

                                if ( !is_array( $options ) )
                                {
                                    return $data;
                                }

                                try
                                {
                                    $options = array_merge( $blockdata, $options );
                                    $data[] = Model::processLayoutBlock( $moduleName, $blockName, $options );
                                }
                                catch ( Exception $e )
                                {
                                    Error::raise( 'Unhandled Exception: ' . $e->getMessage() . ' Block:' . $blockName, 'PHP', $e->getCode(), $e->getFile(), $e->getLine() );
                                }

                                break;
                            }
                        }
                    }
                }

                break;
        }
        $blockdata = null;

        return $data;
    }

    /**
     * Reset Layouter Data
     *
     * @return Layouter
     */
    public function reset()
    {
        $this->layoutData = null;
        $this->layoutBlocksData = null;

        return $this;
    }

    /**
     * Init Block data
     * used in class Frontend
     *
     * @param array $layout
     * @param array $layoutBlocks
     * @param array $layoutBlocksData
     */
    public function setLayoutData( &$layout, &$layoutBlocks, &$layoutBlocksData )
    {
        if ( $this->layoutData == null )
        {
            $this->layoutData = & $layout;
            $this->layoutBlocksData = & $layoutBlocksData;


            foreach ( $layoutBlocks as $block )
            {
                $substrname = strtolower( $block[ 'name' ] );
                $substrname = substr( $substrname, 9 );

                $this->layoutBlocks[ $substrname ] = $block;
            }
        }
    }

    /**
     * @param $itmID
     * @return null
     */
    protected function findBlockData( $itmID )
    {
        foreach ( $this->layoutBlocksData as $blockdata )
        {
            if ( !$blockdata[ 'visible' ] )
            {
                continue;
            }
            if ( $blockdata[ 'blockname' ] === $itmID )
            {
                return $blockdata;
            }


            if ( $itmID === 'ROOT' )
            {
                foreach ( $itemsData[ $itmID ] as $_box => $__data )
                {
                    if ( $blockdata[ 'blockname' ] === $_box )
                    {
                        return $blockdata;
                        break;
                    }
                }
            }
        }

        return null;
    }

    /**
     *
     * @param integer $relID
     * @param array $inData
     * @return array/null
     */
    public function findRelBlockData( $relID = 0, $inData )
    {
        foreach ( $inData as $r )
        {
            if ( $r[ 'id' ] === $relID )
            {
                return $r;
            }
        }

        return array();
    }

    /**
     * @param      $name
     * @param bool $before
     * @return string
     */
    public function _renderLayoutBlock( $name, $before = false )
    {
        $name = strtolower( $name );

        if ( !isset( $this->layoutBlocks[ $name ] ) )
        {
            return '';
        }


        $block = $this->layoutBlocks[ $name ];


        if ( $before )
        {
            $_settings = explode( 'contentPlaceholder', $block[ 'settings' ] );
            $settings = $_settings[ 0 ];
        }
        elseif ( !$before )
        {
            $_settings = explode( 'contentPlaceholder', $block[ 'settings' ] );
            if ( !isset( $_settings[ 1 ] ) )
            {
                return '';
            }
            $settings = $_settings[ 1 ];
        }
        else
        {
            $_settings = explode( 'contentPlaceholder', $block[ 'settings' ] );
            $settings = $_settings[ 0 ];
        }

        if ( !$settings )
        {
            return '';
        }

        $subcoldata = $this->extractSubCols( $block );

        $skipNames = array();
	    $outdata = '';


        if ( $subcoldata !== null )
        {
            foreach ( $subcoldata[ 'subcols' ] as $colidname => $htmlcode )
            {
                $items = $subcoldata[ 'subcolitems' ][ $colidname ];
                $htmlcode_in = $htmlcode;
                foreach ( $items as $itmID )
                {
                    $skipNames[ $itmID ] = $itmID;

                    $currentdata = $this->findBlockData( $itmID );

                    if ( $currentdata )
                    {
                        $_data = $this->processBlock( $block, $currentdata );
                        $htmlcode_in = str_replace( '[' . $itmID . ']', implode( '', $_data ), $htmlcode_in );
                    }
                    else
                    {
                        $htmlcode_in = str_replace( '[' . $itmID . ']', '', $htmlcode_in );
                    }
                }


                if ( $htmlcode_in == $htmlcode )
                {
                    unset( $subcoldata[ 'subcols' ][ $colidname ] );
                    continue;
                }

                $items = null;


                // clean HTML code
                // $htmlcode = preg_replace('#\s*style="[ˆ"]+"#is', '', $htmlcode);
                $htmlcode_in = preg_replace( '#\s*(cbox|itemBox|sortableSubCols|allowSubCols|connectedSortable|dropaccept|ui-sortable|ui-sortable-disabled|ui-draggable|ui-draggable-disabled|ui-droppable|ui-droppable-disabled)\s*#is', '', $htmlcode_in );
                $htmlcode_in = preg_replace( '#(\s*title="Subtemplate([^"]*)"|\s*equalize\s*)#isS', '', $htmlcode_in );

                $htmlcode_in = preg_replace( '#"\s*subsort\s*"#isS', '"sub-inner"', $htmlcode_in );
                $htmlcode_in = preg_replace( '#"\s*(subcolumns|subcolumnsdropzone)\s*"#isS', '"subcolumns equalize"', $htmlcode_in );

                $subcoldata[ 'subcols' ][ $colidname ] = $htmlcode_in;
                $htmlcode = null;
            }
        }


        $settings = explode( ',', $settings );
        foreach ( $settings as $blockname )
        {

            if ( $blockname === '' )
            {
                continue;
            }

            if ( isset( $skipNames[ $blockname ] ) )
            {
                continue;
            }

            if ( $subcoldata !== null && isset( $subcoldata[ 'subcols' ][ $blockname ] ) )
            {
                $outdata .= $subcoldata[ 'subcols' ][ $blockname ];
                continue;
            }

            $currentdata = null;
            foreach ( $this->layoutBlocksData as $blockdata )
            {
                if ( $blockdata[ 'blockname' ] === $blockname )
                {
                    $currentdata = $blockdata;
                    break;
                }
            }

            if ( is_array( $currentdata ) )
            {

                # echo ($before ? ' vor:' . $blockname . ' ' : ' hin:' . $blockname . ' ') . '<br/>';
                $_data = $this->processBlock( $block, $currentdata );
                $outdata .= implode( '', $_data );
            }
        }




        # print_r( $name . $outdata );
        # exit;


        unset( $subcoldata, $data, $currentdata, $settings );
#if ( $name != 'custombottom') {
        return $outdata;
#}


        ob_clean();
        echo $outdata . ' --- ';


        print_r( $subcoldata );
        exit;
        $outdata = '';
        foreach ( $this->layoutBlocks as $block )
        {
            $substrname = strtolower( $block[ 'name' ] );
            $substrname = substr( $substrname, 9 );


            $subcoldata = $this->extractSubCols( $block );

            $subColTemplateData = null;
            if ( isset( $subcoldata[ $block[ 'name' ] ] ) )
            {
                $subColTemplateData = $subcoldata[ $block[ 'name' ] ];
            }


            if ( $substrname !== '' && $substrname === $name )
            {
                if ( $before )
                {
                    $_settings = explode( 'contentPlaceholder', $block[ 'settings' ] );
                    $settings = $_settings[ 0 ];
                }
                elseif ( !$before )
                {
                    $_settings = explode( 'contentPlaceholder', $block[ 'settings' ] );
                    if ( !isset( $_settings[ 1 ] ) )
                    {
                        continue;
                    }
                    $settings = $_settings[ 1 ];
                }
                else
                {
                    $_settings = explode( 'contentPlaceholder', $block[ 'settings' ] );
                    $settings = $_settings[ 0 ];
                }

                if ( !$settings )
                {
                    continue;
                }


                $settings = explode( ',', $settings );

                $subcolitems = null;
                $subcols = null;

                if ( $subColTemplateData !== null )
                {
                    $subcolitems = $subColTemplateData[ 'subcolitems' ];
                    $subcols = $subColTemplateData[ 'subcols' ];
                }


                foreach ( $settings as $blockname )
                {

                    if ( $blockname === '' )
                    {
                        continue;
                    }

                    if ( $subcols !== null && isset( $subcols[ $blockname ] ) )
                    {
                        $_data = $this->processBlock( $block, $currentdata );
                        $subcols[ $blockname ] = str_replace( $blockname, $_data, $subcols[ $blockname ] );
                    }
                }

                print_r( $subcols );
                die();


                foreach ( $settings as $blockname )
                {

                    if ( $blockname === '' )
                    {
                        continue;
                    }

                    if ( $subcols !== null && isset( $subcols[ $blockname ] ) )
                    {
                        $_data = $this->processBlock( $block, $currentdata );


                        $subcols[ $blockname ] = str_replace( $blockname, $_data, $subcols[ $blockname ] );
                    }


                    $currentdata = null;
                    foreach ( $this->layoutBlocksData as $blockdata )
                    {
                        if ( $blockdata[ 'blockname' ] === $blockname )
                        {
                            $currentdata = $blockdata;

                            break;
                        }
                    }


                    if ( is_array( $currentdata ) )
                    {

                        # echo ($before ? ' vor:' . $blockname . ' ' : ' hin:' . $blockname . ' ') . '<br/>';
                        $_data = $this->processBlock( $block, $currentdata );
                        $outdata .= implode( '', $_data );
                    }
                }
            }
        }

        return $outdata;
    }

    /**
     *
     * @param array $row
     * @return mixed return array if has subcols and retuns null if not has subcols
     */
    private function extractSubCols( $row )
    {
        $list = explode( ',', $row[ 'settings' ] );
        $subcols = array();
        $subcolItems = array();
        $tmp = array();

        foreach ( $list as $idx => $listname )
        {
            if ( !trim( $listname ) )
            {
                continue;
            }

            // extract subcols
            if ( $row[ 'subcolhtml' ] !== null && trim( $row[ 'subcolhtml' ] ) !== '' )
            {

                preg_match_all( '#\[START:([a-z0-9_]+?)\]#is', $row[ 'subcolhtml' ], $matches );

                foreach ( $matches[ 1 ] as $subid )
                {
                    if ( $subid === $listname )
                    {
                        preg_match( '#\[START:' . $subid . '\](.*)\[/END:' . $subid . '\]#isU', $row[ 'subcolhtml' ], $subhtml );
                        $subcols[ $subid ] = $subhtml[ 1 ];

                        preg_match_all( '#\[([a-z0-9_\-]+?)\]#is', $subhtml[ 1 ], $matches );
                        $subcolItems[ $subid ] = $matches[ 1 ];

                        $tmp = array_merge( $tmp, $matches[ 1 ] );

                        $row[ 'subcolhtml' ] = preg_replace( '#\[START:' . $subid . '\](.*)\[/END:' . $subid . '\]#isU', '', $row[ 'subcolhtml' ], 1 );
                    }
                }

                $matches = array();
                preg_match_all( '#\[([a-z0-9_\-]+?)\]#isU', $row[ 'subcolhtml' ], $matches );

                $subcolItems[ 'ROOT' ] = array();
                foreach ( $matches[ 1 ] as $v )
                {
                    if ( !in_array( $v, $tmp ) && (isset( $subcolItems[ 'ROOT' ] ) && !in_array( $v, $subcolItems[ 'ROOT' ] )) && $v != 'contentPlaceholder' )
                    {
                        $subcolItems[ 'ROOT' ][] = $v;
                    }
                }
            }
        }


        if ( !count( $subcols ) )
        {
            // return null;
        }

        return array(
            'subcolitems' => $subcolItems,
            'subcols'     => $subcols );
    }

    /**
     *
     * @param string $name
     * @param bool $before
     * @return string
     */
    public function getLayoutBlock( $name, $before = false )
    {
        static $layout, $blockContentIdsCache;

        if ( empty( $layout ) )
        {
            $this->load( 'Template' );
            $layout = $this->Template->getLayout();
        }

        if ( is_null( self::$_layoutBlocks ) )
        {
            self::$_layoutBlocks = Cache::get( 'layoutBlocks', 'data/layout/' . $layout[ 'id' ] );
            if ( !is_array( self::$_layoutBlocks ) )
            {
                self::$_layoutBlocks = $this->db->query( 'SELECT * FROM %tp%layout_settings WHERE layoutid = ?', $layout[ 'id' ] )->fetchAll();
                Cache::write( 'layoutBlocks', self::$_layoutBlocks, 'data/layout/' . $layout[ 'id' ] );
            }
            Cache::freeMem( 'layoutBlocks', 'data/layout/' . $layout[ 'id' ] );
        }


        $blockids = explode( ',', $layout[ 'blockids' ] );

        $key = implode( '_', $blockids );


        if ( !isset( self::$_layoutBlocksData[ implode( '_', $blockids ) ] ) )
        {

            $tmpData = array();

            $_name = 'layoutBlocksData-' . implode( '_', $blockids ) . '-' . CONTENT_TRANS;
            $data = Cache::get( $_name, 'data/layout/' . $layout[ 'id' ] );

            if ( !is_array( $data ) )
            {
                /**
                 * Read layout blocks with current language.
                 *
                 * @todo If change the content language then run ajax an update the current layouter!
                 */
                $_layoutBlockDataTemp = $this->db->query( 'SELECT relid, id, blockname
                FROM %tp%layout_data
                WHERE
                layoutid = ? AND
                visible = 1 ', $layout[ 'id' ] )->fetchAll();

                // cache the ids of layout data
                $ids = array( 0 );
                $blockNamesCache = array();

                foreach ( $_layoutBlockDataTemp as $r )
                {
                    if ( $r[ 'relid' ] > 0 )
                    {
                        $ids[] = $r[ 'id' ];
                    }

                    $ids[] = ($r[ 'relid' ] > 0 ? $r[ 'relid' ] : $r[ 'id' ]);
                }

                $ids = array_unique( $ids );

                unset( $_layoutBlockDataTemp );


                /**
                 * get the layout data
                 */
                $data = $this->db->query( 'SELECT d.*, dt.*
                                             FROM %tp%layout_data AS d 
                                             LEFT JOIN %tp%layout_data_trans AS dt ON(dt.dataid=d.id)
                                             WHERE d.id IN(' . implode( ',', $ids ) . ')  AND
                                             (
                                                dt.`lang` = ? OR dt.iscorelang = 1 AND
                                                NOT EXISTS (SELECT a.dataid FROM %tp%layout_data_trans AS a WHERE a.dataid = d.id AND a.`lang` = ?) 
                                             ) 
                                             ORDER BY blockname ASC', CONTENT_TRANS, CONTENT_TRANS )->fetchAll();

                $baseData = $data;
                foreach ( $data as &$r )
                {
                    if ( $r[ 'relid' ] )
                    {
                        $tmp = $this->findRelBlockData( $r[ 'relid' ], $baseData );

                        if ( isset( $tmp[ 'id' ] ) )
                        {
                            $r[ 'title' ] = $tmp[ 'title' ];
                            $r[ 'value' ] = $tmp[ 'value' ];
                        }
                    }
                }

                if ( count( $data ) )
                {
                    $tmpData[] = $data;
                }
                Cache::write( $_name, $data, 'data/layout/' . $layout[ 'id' ] );
            }
            else
            {
                $tmpData[] = $data;
            }









            /*














              $blockids = explode( ',', $layout[ 'blockids' ] );
              $tmpData  = array();

              foreach ( $blockids as $id )
              {
              if ( isset( $blockContentIdsCache[ $id ] ) || !intval( $id ) )
              {
              continue;
              }

              $_name = 'layoutBlocksData-' . $id . '-' . CONTENT_TRANS;
              $data  = Cache::get( $_name, 'data/layout/' . $layout[ 'id' ] );


              if ( !is_array( $data ) )
              {

              $_layoutBlockDataTemp = $this->db->query( 'SELECT relid, id, blockname FROM %tp%layout_data WHERE layoutid = ? AND visible = 1 ', $layout[ 'id' ] )->fetchAll();

              // cache the ids of layout data
              $ids             = array(
              0 );
              $blockNamesCache = array();


              foreach ( $_layoutBlockDataTemp as $r )
              {
              if ( $r[ 'relid' ] > 0 )
              {
              $ids[] = $r[ 'id' ];
              }

              $ids[] = ($r[ 'relid' ] > 0 ? $r[ 'relid' ] : $r[ 'id' ]);
              }

              $ids = array_unique( $ids );

              unset( $_layoutBlockDataTemp );


              $data = $this->db->query( 'SELECT d.*, dt.*
              FROM %tp%layout_data AS d
              LEFT JOIN %tp%layout_data_trans AS dt ON(dt.dataid=d.id)
              WHERE d.id IN(' . implode( ',', $ids ) . ')  AND
              (
              dt.`lang` = ? OR dt.iscorelang = 1 AND
              NOT EXISTS (SELECT a.dataid FROM %tp%layout_data_trans AS a WHERE a.dataid = d.id AND a.`lang` = ?)
              )
              ORDER BY blockname ASC', CONTENT_TRANS, CONTENT_TRANS )->fetchAll();

              $baseData = $data;
              foreach ( $data as &$r )
              {
              if ( $r[ 'relid' ] )
              {
              $tmp = $this->findRelBlockData( $r[ 'relid' ], $baseData );

              if ( isset( $tmp[ 'id' ] ) )
              {
              $r[ 'title' ] = $tmp[ 'title' ];
              $r[ 'value' ] = $tmp[ 'value' ];
              }
              }
              }



              if ( count( $data ) )
              {
              $tmpData[] = $data;
              }

              Cache::write( $_name, $data, 'data/layout/' . $layout[ 'id' ] );
              }
              else
              {
              $tmpData[] = $data;
              }

              $blockContentIdsCache[ $id ] = true;
              }

             */
            $tmpData = array_unique( $tmpData, SORT_STRING );



            self::$_layoutBlocksData[ $key ] = array();
            foreach ( $tmpData as $r )
            {
                self::$_layoutBlocksData[ $key ] = array_merge( self::$_layoutBlocksData[ $key ], $r );
            }
        }
        // print_r( self::$_layoutBlocks );
        #
        # print_r( self::$_layoutBlocksData );
        # exit;
        #if ($this->layoutData == null)
        #{
        $this->setLayoutData( $layout, self::$_layoutBlocks, self::$_layoutBlocksData[ $key ] );
        #}


        $outdata = $this->_renderLayoutBlock( $name, $before );

	    $outdata = preg_replace('#\[/?(START|END):subdyn_id\d*\]#is', '', $outdata);


        $outdata = Content::tinyMCECoreTags( $outdata );
        /*
          $this->load('Provider');
          if ( $this->Provider->hasProviders( $outdata ) )
          {
          // render providers
          $outdata = $this->Provider->renderProviderTags( $outdata );
          } */


        $layout = null;

        //  $outdata = preg_replace( '/ class="([^"\']*)?=(ui-draggable|ui-draggable-disabled|dropprotect|subsort)([^\1]*)"/isSU', ' class="$1 $3"', $outdata );

        return $outdata;
    }

}

?>