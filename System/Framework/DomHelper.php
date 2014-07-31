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
 * @file        DomHelper.php
 *
 */
class DomHelper
{

    /**
     * This function takes a DomElement object and outputs the outer HTML of it. i.e. The HTML of all the children AND the current element's HTML
     *
     * @param DomElement $node        The DomElement object to get outer HTML for.
     * @param string     $NodeContent The html content to append from a parent element
     * @param bool       $first
     * @return mixed|string
     */
    public static function outerHTML( $node, $NodeContent = '', $first = true )
    {
        if ( $first === true )
        {
            $firstNodeName = $node->nodeName;
            $firstNodeValue = $node->nodeValue;
            $NodeContent .= '<' . $firstNodeName;
            $attAre = $node->attributes;
            foreach ( $attAre as $value )
            {
                if ( !in_array( iwp_strtolower( $value->nodeName ), array(
                            'block',
                            'blockstyle' ) ) )
                {
                    $NodeContent .= " {$value->nodeName}='{$value->nodeValue}'";
                }
            }
            $NodeContent .= ">";
        }

        $NodList = $node->childNodes;
        if ( $NodList instanceof DOMNodeList )
        {
            for ( $j = 0; $j < $NodList->length; $j++ )
            {
                $subNode = $NodList->item( $j ); //Node j
                $nodeName = $subNode->nodeName;
                $nodeValue = $subNode->nodeValue;

                if ( $subNode->nodeType === XML_TEXT_NODE )
                {
                    $NodeContent .= htmlspecialchars( $nodeValue );
                }
                elseif ( $subNode->nodeType === XML_COMMENT_NODE )
                {
                    $NodeContent .= '<!--' . $subNode->data . '-->';
                }
                elseif ( $subNode->nodeType === XML_CDATA_SECTION_NODE )
                {
                    $NodeContent .= "\n" . '/*<![CDATA[*/' . "\n" . $subNode->data . "\n" . '/*]]>*/' . "\n";
                }
                else
                {
                    $NodeContent .= '<' . $nodeName;
                    $attAre = $subNode->attributes;
                    if ( !is_null( $attAre ) )
                    {
                        foreach ( $attAre as $value )
                        {
                            if ( !in_array( strtolower( $value->nodeName ), array(
                                        'block',
                                        'blockstyle' ) ) )
                            {
                                $NodeContent .= " {$value->nodeName}='{$value->nodeValue}'";
                            }
                        }
                    }
                    $NodeContent .= ">";
                    $NodeContent = self::outerHTML( $subNode, $NodeContent, false );
                    $NodeContent .= '</' . $nodeName . '>';
                }
            }
        }

        if ( $first === true )
        {
            $NodeContent .= '</' . $firstNodeName . '>';
            $NodeContent = Layouter::cleanUpHTML( $NodeContent );
        }

        return $NodeContent;
    }

    /**
     * This function takes a DomElement object and outputs the inner HTML of it. i.e. The HTML of all the children, but not the current tag.
     *
     * @param        $mainNode
     * @param string $NodeContent The html content to append from a parent element
     * @return string
     * @internal param \DomElement $node The DomElement object to get inner HTML from.
     */
    public static function innerHTML( $mainNode, $NodeContent = '' )
    {
        $NodList = $mainNode->childNodes;
        for ( $j = 0; $j < $NodList->length; $j++ )
        {

            $subNode = $NodList->item( $j );
            $nodeName = $subNode->nodeName;
            $nodeValue = $subNode->nodeValue;

            if ( $subNode->nodeType === XML_TEXT_NODE )
            {
                $NodeContent .= htmlspecialchars( $nodeValue );
            }
            elseif ( $subNode->nodeType === XML_COMMENT_NODE )
            {
                $NodeContent .= '<!--' . $subNode->data . '-->';
            }
            elseif ( $subNode->nodeType === XML_CDATA_SECTION_NODE )
            {
                $NodeContent .= "\n" . '/*<![CDATA[*/' . "\n" . $subNode->data . "\n" . '/*]]>*/' . "\n";
            }
            else
            {
                $NodeContent .= '<' . $nodeName;

                $attAre = $subNode->attributes;
                if ( $attAre )
                {
                    foreach ( $attAre as $value )
                    {
                        $NodeContent .= ' ' . $value->nodeName . "=\"" . $value->nodeValue . "\"";
                    }
                }
                $NodeContent .= ">";
                $NodeContent = self::innerHTML( $subNode, $NodeContent );
                $NodeContent .= '</' . $nodeName . '>';
            }
        }
        return $NodeContent;
    }

    /**
     * This function retrieves all the attributes of a DomElement object. It then returns it as an array (usually they're DomAttr objects)
     *
     * @param $domElement
     * @return array
     * @internal param \DomElement $attributes The HTML to be cleaned up
     */
    public static function getAttributes( $domElement )
    {
        $attAre = $domElement->attributes;
        $return = array();
        foreach ( $attAre as $value )
        {
            $return[ $value->nodeName ] = $value->nodeValue;
        }
        return $return;
    }

    /**
     * This function takes a "base" class name and looks for all elements that contain the class or any derivations of it.
     * It will then position the replaceValue either inside the element with the class or elsewhere depending on whether it has extra position information appended.
     * Matched formats include: tpl-myvar, tpl-myvar-attr where 'attr' is the attribute name to enter the value into. If no 'attr' is given, the value will be inserted as the tag's value and not as an attribute's value.
     * There are special 'attr' values available for use:
     *  - inside - This is the same as not having an 'attr'
     *  - append - This appends the replaceValue to the end of the content inside the element, leaving existing data intact.
     *  - prepend - This prepends the replaceValue to the beginning of the content inside the element, leaving existing data intact.
     *  - spaceappend - This is the same as "append" but includes a space before the value. This is useful for combinding language variables.
     *  - spaceprepend - This works the same was as "spaceappend" but ads it to the end and the space is after the value.
     *  - styleprepend - This works the same as "spaceprepend", but the value is added into the "style" attribute, leaving existing styles intact.
     *  - styleappend - This works the same as "styleprepend", but adds it to the end instead of the beginning.
     *  - classprepend - This works the same as "styleprepend", but adds the values to the "class" attribute.
     *  - classappend - This works the same as "styleappend", but adds the values to the "class" attribute.
     *
     * Example: tplvar-myvar is a base class. But tpl-myvar, tpl-myvar-value, tpl-myvar-inside would all be matched.
     *
     * @param $domElement
     * @param $baseClassName
     * @param $replaceValue
     * @return unknown_type
     */
    public static function modifyTagByClassName( $domElement, $baseClassName, $replaceValue )
    {
        $baseClassName = trim( $baseClassName );
        $xpathFind = '';

        if ( ($domElement instanceof DOMDocument ) )
        {
            $xPath = new DOMXPath( $domElement );
        }
        else
        {
            $xPath = new DOMXPath( $domElement->ownerDocument );
            $xpath->registerNamespace( 'xsl', 'http://www.w3.org/1999/XSL/Transform' );
            $attr = self::getAttributes( $domElement );

            if ( isset( $attr[ 'block' ] ) )
            {
                $xpathFind = '//*[@block="' . $attr[ 'block' ] . '" and @blockstyle="' . $attr[ 'blockstyle' ] . '"]';
            }
        }

        $classMatches = $xPath->query( $xpathFind . '//*[contains(concat(\' \', @class, \' \'), \' ' . $baseClassName . ' \') or contains(concat(\' \', @class, \' \'), \' ' . $baseClassName . '-\')]' );

        if ( $classMatches->length == 0 )
        {
            return;
        }

        foreach ( $classMatches as $thisElement )
        {
            $thisAttr = self::getAttributes( $thisElement );

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
                if ( in_array( $thisClass, array(
                            $baseClassName,
                            $baseClassName . '-inside' ) ) )
                {
                    self::replaceInnerHTML( $thisElement, $replaceValue );
                }
                elseif ( substr( $thisClass, 0, strlen( $baseClassName ) ) == $baseClassName )
                {
                    // we have a base class name
                    $positions = preg_split( '#\-#', substr( $thisClass, strlen( $baseClassName ) ), -1, PREG_SPLIT_NO_EMPTY );
                    if ( empty( $positions ) )
                    {
                        continue;
                    }
                    foreach ( $positions as $thisPosition )
                    {
                        if ( $thisPosition === 'inside' )
                        {
                            self::replaceInnerHTML( $thisElement, $replaceValue );
                        }
                        elseif ( $thisPosition === 'append' )
                        {
                            self::insertHTMLAfter( $replaceValue, $thisElement->lastChild );
                        }
                        elseif ( $thisPosition === 'prepend' )
                        {
                            self::insertHTMLBefore( $replaceValue, $thisElement->firstChild );
                        }
                        elseif ( $thisPosition === 'spaceappend' )
                        {
                            self::insertHTMLAfter( ' ' . $replaceValue, $thisElement->lastChild );
                        }
                        elseif ( $thisPosition === 'spaceprepend' )
                        {
                            self::insertHTMLBefore( $replaceValue . ' ', $thisElement->firstChild );
                        }
                        elseif ( preg_match( '#^(.+?)((pre|ap)pend)$#', $thisPosition, $matches ) )
                        {
                            $attribute = $matches[ 1 ];
                            $action = $matches[ 2 ];
                            $addSpace = ($attribute === 'class' ? ' ' : '');

                            if ( $thisElement->hasAttribute( $attribute ) )
                            {
                                $currentValue = $thisElement->getAttribute( $attribute );
                                if ( $action == 'prepend' )
                                {
                                    $thisElement->setAttribute( $attribute, $replaceValue . $addSpace . $currentValue );
                                }
                                else
                                {
                                    $thisElement->setAttribute( $attribute, $currentValue . $addSpace . $replaceValue );
                                }
                            }
                            else
                            {
                                $thisElement->setAttribute( $attribute, $replaceValue );
                            }
                        }
                        else
                        {
                            $thisElement->setAttribute( $thisPosition, $replaceValue );
                        }
                    }
                }
            }
        }
    }

    /**
     * This function returns a new DomText object. By default it returns it with a new line before and after the text content
     *
     * @param string $text The text to be converted to a DOMText object
     * @param boolean $newLines True if the text should have a new line prepended and appended before being added into a DomText object
     * @return \DOMText
     */
    public static function NewDOMText( $text, $newLines = true )
    {
        if ( $newLines )
        {
            return new DOMText( "\n" . $text . "\n" );
        }
        else
        {
            return new DOMText( $text );
        }
    }

    /**
     * @param $domElement
     * @param $html
     */
    public static function replaceInnerHTML( $domElement, $html )
    {

        for ( $i = ($domElement->childNodes->length - 1); $i > -1; $i-- )
        {
            $childElement = $domElement->childNodes->item( $i );
            $domElement->removeChild( $childElement );
        }

        $node = new DOMDocument();
        $node->loadHTML( '<html><body><p>' . $html . '</p></body></html>' );

        $xpath = new DomXpath( $node );
        $xpath->registerNamespace( 'xsl', 'http://www.w3.org/1999/XSL/Transform' );
        $nodeList = $xpath->query( '//body/p' );

        if ( $nodeList->length == 0 )
        {
            return;
        }


        $cleanNodes = array();
        foreach ( $nodeList->item( 0 )->childNodes as $thisNode )
        {
            $cleanNodes[] = $domElement->ownerDocument->importNode( $thisNode->cloneNode( true ), true );
        }

        foreach ( $cleanNodes as $cleanNode )
        {
            $domElement->appendChild( $cleanNode );
        }
        unset( $node );
    }

    /**
     * @param $html
     * @param $before
     */
    public static function insertHTMLAfter( $html, $before )
    {
        $document = $before->ownerDocument;
        $node = new DOMDocument();
        $node->loadHTML( '<html><body><p>' . $html . '</p></body></html>' );

        $xpath = new DomXpath( $node );
        $xpath->registerNamespace( 'xsl', 'http://www.w3.org/1999/XSL/Transform' );
        $nodeList = $xpath->query( '//body/p' );

        if ( $nodeList->length == 0 )
        {
            return;
        }

        $cleanNodes = array();
        foreach ( $nodeList->item( 0 )->childNodes as $thisNode )
        {
            $cleanNodes[] = $document->importNode( $thisNode->cloneNode( true ), true );
        }

        //$cleanNodes = array_reverse($cleanNodes);

        foreach ( $cleanNodes as $cleanNode )
        {
            $before = $before->parentNode->insertBefore( $cleanNode, $before->nextSibling );
        }
        unset( $node );
    }

    /**
     * @param $html
     * @param $before
     */
    public static function insertHTMLBefore( $html, $before )
    {
        $document = $before->ownerDocument;
        $node = new DOMDocument();
        $node->loadHTML( '<html><body><p>' . $html . '</p></body></html>' );

        $xpath = new DomXpath( $node );
        $xpath->registerNamespace( 'xsl', 'http://www.w3.org/1999/XSL/Transform' );
        $nodeList = $xpath->query( '//body/p' );

        if ( $nodeList->length == 0 )
        {
            return;
        }

        $cleanNodes = array();
        foreach ( $nodeList->item( 0 )->childNodes as $thisNode )
        {
            $cleanNodes[] = $document->importNode( $thisNode->cloneNode( true ), true );
        }

        $cleanNodes = array_reverse( $cleanNodes );

        foreach ( $cleanNodes as $cleanNode )
        {
            $before = $before->parentNode->insertBefore( $cleanNode, $before );
        }
        unset( $node );
    }

    /**
     * @param $html
     * @param $doc
     * @return mixed
     */
    public static function HTML2Element( $html, $doc )
    {
        $node = new DOMDocument();
        $node->loadHTML( '<html><body><p>' . $html . '</p></body></html>' );
        $xpath = new DomXpath( $node );
        $xpath->registerNamespace( 'xsl', 'http://www.w3.org/1999/XSL/Transform' );
        $nodeList = $xpath->query( '//body/p/*' );
        $newNode = $nodeList->item( 0 );
        foreach ( $nodeList as $thisNode )
        {
            
        }
        $cleanNode = $doc->importNode( $newNode->cloneNode( true ) );

        unset( $node );

        return $cleanNode;
    }

    /**
     * @param $appendToElement
     * @param $domElement
     * @param $html
     */
    public static function AppendHTMLAfterElement( $appendToElement, $domElement, $html )
    {
        $node = new DOMDocument();
        $node->loadHTML( '<html><body>' . $html . '</body></html>' );

        $lastNode = $appendToElement->nextSibling;

        for ( $i = 0; $i < $node->childNodes->item( 1 )->childNodes->item( 0 )->childNodes->length; $i++ )
        {
            $newNode = $domElement->ownerDocument->importNode( $node->childNodes->item( 1 )->childNodes->item( 0 )->childNodes->item( $i ), true );
            $appendToElement->parentNode->insertBefore( $newNode, $lastNode );
            /*
              TODO: remove?
              Took this out because doesn't look like its needed.
              if(is_object($lastNode->nextSibling)){
              $lastNode = $lastNode->nextSibling;
              }
             */
        }
    }

    /**
     * @param $appendToElement
     * @param $domElement
     * @param $html
     */
    public static function AppendHTMLToElement( $appendToElement, $domElement, $html )
    {
        $node = new DOMDocument();
        $node->loadHTML( '<html><body>' . $html . '</body></html>' );

        $lastNode = $appendToElement->childNodes->item( $appendToElement->childNodes->length - 1 );

        for ( $i = 0; $i < $node->childNodes->item( 1 )->childNodes->item( 0 )->childNodes->length; $i++ )
        {
            $newNode = $domElement->ownerDocument->importNode( $node->childNodes->item( 1 )->childNodes->item( 0 )->childNodes->item( $i ), true );
            $appendToElement->insertBefore( $newNode, $lastNode );
            if ( is_object( $lastNode ) && is_object( $lastNode->nextSibling ) )
            {
                $lastNode = $lastNode->nextSibling;
            }
        }
    }

    /**
     * @param     $domElement
     * @param     $className
     * @param int $iterator
     * @return DOMElement
     */
    public static function GetElementByClass( $domElement, $className, $iterator = 0 )
    {
        $blocksXpath = new DOMXPath( $domElement->ownerDocument );
        $blocksXpath->registerNamespace( 'xsl', 'http://www.w3.org/1999/XSL/Transform' );
        $xpathFind = '';
        $attr = self::getAttributes( $domElement );
        $xpathFind = '';
        if ( isset( $attr[ 'block' ] ) )
        {
            $xpathFind = '//*[@block="' . $attr[ 'block' ] . '" and @blockstyle="' . $attr[ 'blockstyle' ] . '"]';
        }
        $blocks = $blocksXpath->query( $xpathFind . '//*[contains(concat(\' \', @class, \' \'), \' ' . $className . ' \') or normalize-space(@class)="' . $className . '"]' );

        return $blocks->item( $iterator );
    }

    /**
     * @param $domElement
     * @param $className
     * @return DOMNodeList
     */
    public static function GetElementListByClass( $domElement, $className )
    {
        $blocksXpath = new DOMXPath( $domElement->ownerDocument );
        $blocksXpath->registerNamespace( 'xsl', 'http://www.w3.org/1999/XSL/Transform' );
        $xpathFind = '';
        $attr = self::getAttributes( $domElement );

        $xpathFind = '@block="' . $attr[ 'block' ] . '" and @blockstyle="' . $attr[ 'blockstyle' ] . '"';
        $blocks = $blocksXpath->query( '//*[' . $xpathFind . ']//*[contains(concat(\' \', @class, \' \'), \' ' . $className . ' \') or normalize-space(@class)="' . $className . '"]' );

        return $blocks;
    }

    /**
     * @param $className
     * @return string
     */
    public static function getXpathForClass( $className )
    {
        return 'contains(concat(\' \', @class, \' \'), \' ' . $className . ' \') or normalize-space(@class)="' . $className . '"';
    }

    /**
     * @param $domElement
     * @param $className
     * @param $replaceWith
     */
    public static function ReplaceContentByClass( $domElement, $className, $replaceWith )
    {
        $blocksXpath = new DOMXPath( $domElement->ownerDocument );
        $blocksXpath->registerNamespace( 'xsl', 'http://www.w3.org/1999/XSL/Transform' );
        $xpathFind = '';
        $attr = self::getAttributes( $domElement );
        if ( isset( $attr[ 'block' ] ) )
        {
            $xpathFind = '//*[@block="' . $attr[ 'block' ] . '" and @blockstyle="' . $attr[ 'blockstyle' ] . '"]';
        }
        else
        {
            $xpathFind = '';
        }

        $blocks = $blocksXpath->query( $xpathFind . '//*[contains(concat(\' \', @class, \' \'), \' ' . $className . ' \') or normalize-space(@class)="' . $className . '"]' );
        foreach ( $blocks as $blockData )
        {
            $blockData->nodeValue = $replaceWith;
        }
    }

    /**
     * @param $domElement
     * @param $className
     * @param $attrName
     * @param $replaceWith
     */
    public static function ReplaceAttributeByClass( $domElement, $className, $attrName, $replaceWith )
    {
        $blocksXpath = new DOMXPath( $domElement->ownerDocument );
        $blocksXpath->registerNamespace( 'xsl', 'http://www.w3.org/1999/XSL/Transform' );
        $xpathFind = '';
        $attr = self::getAttributes( $domElement );

        if ( isset( $attr[ 'block' ] ) )
        {
            $xpathFind = '//*[@block="' . $attr[ 'block' ] . '" and @blockstyle="' . $attr[ 'blockstyle' ] . '"]';
        }
        else
        {
            $xpathFind = '';
        }

        $xpath = $xpathFind . '//*[contains(concat(\' \', @class, \' \'), \' ' . $className . ' \') or normalize-space(@class)="' . $className . '"]';

        $blocks = $blocksXpath->query( $xpath );

        foreach ( $blocks as $blockData )
        {
            $blockData->setAttribute( $attrName, $replaceWith );
        }
    }

    /**
     * @param      $domElement
     * @param      $className
     * @param      $attrName
     * @param      $replaceWith
     * @param bool $withSpace
     */
    public static function AppendAttributeByClass( $domElement, $className, $attrName, $replaceWith, $withSpace = true )
    {
        $blocksXpath = new DOMXPath( $domElement->ownerDocument );
        $blocksXpath->registerNamespace( 'xsl', 'http://www.w3.org/1999/XSL/Transform' );
        $space = $xpathFind = '';
        $attr = self::getAttributes( $domElement );

        if ( $withSpace )
        {
            $space = ' ';
        }

        if ( isset( $attr[ 'block' ] ) )
        {
            $xpathFind = '//*[@block="' . $attr[ 'block' ] . '" and @blockstyle="' . $attr[ 'blockstyle' ] . '"]';
        }
        else
        {
            $xpathFind = '';
        }

        $xpath = $xpathFind . '//*[contains(concat(\' \', @class, \' \'), \' ' . $className . ' \') or normalize-space(@class)="' . $className . '"]';

        $blocks = $blocksXpath->query( $xpath );

        foreach ( $blocks as $blockData )
        {

            $blockData->setAttribute( $attrName, (string) $blockData->getAttribute( $attrName ) . $space . $replaceWith );
        }
    }

    /**
     * @param $domElement
     * @param $attrName
     * @param $replaceWith
     */
    public static function ReplaceAttribute( $domElement, $attrName, $replaceWith )
    {
        $domElement->setAttribute( $attrName, $replaceWith );
    }

    /**
     * @param        $element
     * @param        $before
     * @param string $after
     */
    public static function WrapElement( $element, $before, $after = '' )
    {
        if ( $before )
        {
            $element->parentNode->insertBefore( self::NewDOMText( $before ), $element );
        }

        if ( $after )
        {
            $element->parentNode->insertBefore( self::NewDOMText( $after ), $element->nextSibling );
        }
    }

    /**
     * @param        $element
     * @param        $before
     * @param string $after
     */
    public static function WrapInnerElement( $element, $before, $after = '' )
    {
        if ( $before )
        {
            $element->insertBefore( self::NewDOMText( $before ), $element->firstChild );
        }

        if ( $after )
        {
            $element->insertBefore( self::NewDOMText( $after ), $element->lastChild->nextSibling );
        }
    }

    /**
     * @param        $domElement
     * @param        $className
     * @param        $before
     * @param string $after
     * @return int
     */
    public static function WrapElementsByClass( $domElement, $className, $before, $after = '' )
    {
        $nodes = self::GetElementListByClass( $domElement, $className );
        $count = 0;
        if ( is_object( $nodes ) )
        {
            foreach ( $nodes as $node )
            {
                self::WrapElement( $node, $before, $after );
                ++$count;
            }
        }
        return $count;
    }

    /**
     * @param        $domElement
     * @param        $className
     * @param        $before
     * @param string $after
     */
    public static function WrapInnerElementsByClass( $domElement, $className, $before, $after = '' )
    {
        $nodes = self::GetElementListByClass( $domElement, $className );
        if ( is_object( $nodes ) )
        {
            foreach ( $nodes as $node )
            {
                self::WrapInnerElement( $node, $before, $after );
            }
        }
    }

    /**
     * This function removes the block="" and blockStyle="" attributes from a simplexml object
     *
     * @param    SimpleXML    This msut be a SimpleXML object
     * @return void Returns nothing, changes are made the object
     */
    public static function removeBlockAttributes( $objBlock )
    {
        unset( $objBlock[ 'block' ] );
        unset( $objBlock[ 'blockStyle' ] );
    }

}

?>