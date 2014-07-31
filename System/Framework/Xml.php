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
 * @package      DreamCMS
 * @version      3.0.0 Beta
 * @category     Framework
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Xml.php
 */
class Xml
{

	/**
	 *
	 * @var DomDocument
	 */
	protected $xml;

	/**
	 *
	 * @var DomDocument
	 */
	protected $_xmlTmp = null;

	/**
	 * @var string
	 */
	private $_version = '1.0';

	private $_encoding = 'UTF-8';

	private $_rootTag = 'dreamcms';

	private $_outputFormat = false;

	/**
	 *
	 * @param string  $rootTag      default will use 'dreamcms'
	 * @param string  $version      default will use '1.0'
	 * @param string  $encoding     default will use 'UTF-8'
	 * @param boolean $formatOutput default will use false
	 */
	public function __construct ( $rootTag = null, $encoding = null, $version = null, $formatOutput = null )
	{

		if ( is_string($version) )
		{
			$this->_version = $version;
		}

		if ( is_string($encoding) )
		{
			$this->_encoding = $encoding;
		}

		if ( is_string($rootTag) )
		{
			$this->_rootTag = $rootTag;
		}

		if ( is_bool($formatOutput) )
		{
			$this->_outputFormat = $formatOutput;
		}
	}

	/**
	 * Some pre-processing logic, such as removing control characters from xml to prevent parsing errors
	 *
	 * @param string $xml
	 * @return string the cleaned xml string
	 */
	public static function preprocessXml ( & $xml )
	{
		return str_replace("&", "&amp;", str_replace("&amp;", "&", $xml));
	}

	/**
	 * Validate XML to be valid for import
	 *
	 * @param string $xml
	 * @param object $errors optional
	 * @return bool Validation status
	 */
	public static function validateXml ( & $xml, $errors )
	{

		if ( false === $xml || '' == $xml )
		{
			$errors && $errors->add('XML file does not exist, not accessible or empty');
		}
		else
		{
			self::preprocessXml($xml);

			libxml_use_internal_errors(true);
			libxml_clear_errors();
			$_x         = @simplexml_load_string($xml);
			$xml_errors = libxml_get_errors();
			libxml_clear_errors();

			if ( $xml_errors )
			{
				$error_msg = '<strong>Invalid XML</strong><ul>';
				foreach ( $xml_errors as $error )
				{
					$error_msg .= '<li>';
					$error_msg .= 'Line ' . $error->line . ', ';
					$error_msg .= 'Column ' . $error->column . ', ';
					$error_msg .= 'Code ' . $error->code . ': ';
					$error_msg .= '<em>' . trim(htmlspecialchars($error->message)) . '</em>';
					$error_msg .= '</li>';
				}
				$error_msg .= '</ul>';
				$errors && $errors->add($error_msg);
			}
			else
			{
				unset($_x);

				return true;
			}
		}

		return false;
	}

	/**
	 *
	 */
	private function init ()
	{
		$this->xml               = new DomDocument($this->_version, $this->_encoding);
		$this->xml->formatOutput = $this->_outputFormat;
		$this->xml->encoding     = $this->_encoding;
	}

    /**
     * @param $errno
     * @param $errstr
     * @param $errfile
     * @param $errline
     * @return bool
     */
    public static function handleXmlError($errno, $errstr, $errfile, $errline)
	{
		if ($errno == E_WARNING && (substr_count($errstr,"DOMDocument::loadXML()")>0))
		{
			die($errstr);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Get the root XML node, if there isn't one, create it.
	 *
	 * @return type
	 */
	public function getXMLRoot ()
	{

		if ( empty($this->xml) )
		{
			$this->init();
		}

		return $this->xml;
	}

    /**
     *
     * @param array $data
     * @param null $out
     * @return array|null
     */
	public function convertDatabaseArray ( $data, $out = null )
	{

		if ( $out === null )
		{
			$out = array ();
		}

		foreach ( $data as $name => $value )
		{
			if ( is_array($value) )
			{
				$out[ ] = $this->convertDatabaseArray($value);
			}
			else
			{
				if ( $this->isValidTagName($name) )
				{
					if ( preg_match('#([<>&])#', $value) )
					{
						$out[ $name ] = array (
							'cdata' => $value
						);
					}
					else
					{
						$out[ $name ] = array (
							'value' => $value
						);
					}
				}
			}
		}

		return $out;
	}

	/**
	 *
	 * @param string $str
	 * @return string
	 */
	private function escapeCDATA ( $str )
	{

		$str = str_replace('<![CDATA[', '@@![CDATA[', $str);

		return str_replace(']]>', ']]@@', $str);
	}

	/**
	 *
	 * @param string $str
	 * @return string
	 */
	private function unescapeCDATA ( $str )
	{

		$str = str_replace('@@![CDATA[', '<![CDATA[', $str);

		return str_replace(']]@@', ']]>', $str);
	}

	/**
	 *
	 * @param array  $source
	 * @param string $tagName
	 * @return type
	 */
	public function createDOMElement ( $source, $tagName = null )
	{

		if ( $tagName === null )
		{
			$tagName = $this->_rootTag;
		}

		$dom = $this->getXMLRoot();

		if ( !is_array($source) )
		{
			$element = $dom->createElement($tagName);
			$element->appendChild($dom->createCDATASection($this->escapeCDATA($source)));

			return $element;
		}

		$element = $dom->createElement($tagName);

		foreach ( $source as $key => $value )
		{
			if ( is_string($key) )
			{
				foreach ( (is_array($value) ? $value : array (
					$value
				)) as $elementKey => $elementValue )
				{
					$element->appendChild($this->createDOMElement($elementValue, $key));
				}
			}
			else
			{
				$element->appendChild($this->createDOMElement($value, $tagName));
			}
		}

		return $element;
	}

	/**
	 * Convert an XML to Array
	 *
	 * @param string/DOMDocument $input_xml - name of the root node to be converted
	 * @return array
	 */
	public function &createArray ( $input_xml )
	{
		if ( is_string($input_xml) )
		{


			set_error_handler(array('Xml', 'HandleXmlError'));

			$this->xml = $this->getXMLRoot();
			$parsed = $this->xml->loadXML($input_xml);

			restore_error_handler();

			if ( !$parsed )
			{
				trigger_error('Error parsing the XML string.', E_USER_ERROR);
			}
		}
		else
		{
			if ( get_class($input_xml) != 'DOMDocument' )
			{
				trigger_error('The input XML object should be of type: DOMDocument.', E_USER_ERROR);
			}
			$this->xml = $input_xml;
		}

		$array                                   = array ();
		$array[ $this->xml->documentElement->tagName ] = $this->convertToArray($this->xml->documentElement);
		$this->xml                               = null; // clear the xml node in the class for 2nd time use.

		return $array;
	}

	/**
	 * Convert an Array to XML
	 *
	 * @param array   $arr       aray to be converterd
	 * @param boolean $returnXml default is false an will return DomDocument,
	 *                           if use true then return the XML string
	 *
	 * @return Xml or returns string
	 */
	public function createXML ( $arr = array (), $returnXml = false )
	{

		$xml = $this->getXMLRoot();

		$xml->appendChild($this->convertToXML($this->_rootTag, $arr));

		#  $this->xml = null; // clear the xml node in the class for 2nd time use.

		if ( !$returnXml )
		{
			return $this;
		}
		else
		{
			return $xml->saveXML();
		}
	}

	/**
	 *
	 * @param DomDocument $xml
	 * @return string
	 */
	public function save ( DomDocument $xml )
	{

		return $xml->save();
	}

	/**
	 * Convert an Array to XML
	 *
	 * @param string $node_name - name of the root node to be converted
	 * @param array  $arr       - aray to be converterd
	 *
	 * @return DOMNode
	 */
	private function &convertToXML ( $node_name, &$arr )
	{

		//print_arr($node_name);
		$xml  = $this->getXMLRoot();
		$node = $xml->createElement($node_name);

		if ( is_array($arr) )
		{

			// get the attributes first.;
			if ( isset($arr[ 'attributes' ]) )
			{
				foreach ( $arr[ 'attributes' ] as $key => $value )
				{
					if ( !$this->isValidTagName($key) )
					{
						trigger_error('Illegal character in attribute name. attribute: ' . $key . ' in node: ' . $node_name, E_USER_ERROR);
					}

					$node->setAttribute($key, $this->bool2str($value));
				}
				unset($arr[ 'attributes' ]); //remove the key from the array once done.
			}

			// check if it has a value stored in @value, if yes store the value and return
			// else check if its directly stored as string
			if ( isset($arr[ 'value' ]) )
			{
				if ( trim($arr[ 'value' ]) )
				{
					$node->appendChild($xml->createTextNode($this->bool2str($arr[ 'value' ])));
				}
				unset($arr[ 'value' ]); //remove the key from the array once done.
				//return from recursion, as a note with value cannot have child nodes.
				return $node;
			}
			else if ( isset($arr[ 'cdata' ]) )
			{
				if ( trim($arr[ 'cdata' ]) )
				{
					$node->appendChild($xml->createCDATASection($this->bool2str($arr[ 'cdata' ])));
				}
				unset($arr[ 'cdata' ]); //remove the key from the array once done.
				//return from recursion, as a note with cdata cannot have child nodes.
				return $node;
			}
		}

		if ( is_array($arr) )
		{
			// create subnodes using recursion
			// recurse to get the node for that key
			foreach ( $arr as $key => $value )
			{
				if ( !$this->isValidTagName($key) )
				{
					trigger_error('Illegal character in tag name. tag: ' . $key . ' in node: ' . $node_name, E_USER_ERROR);
				}

				if ( is_array($value) && is_numeric(key($value)) )
				{
					// MORE THAN ONE NODE OF ITS KIND;
					// if the new array is numeric index, means it is array of nodes of the same kind
					// it should follow the parent key name
					foreach ( $value as $k => $v )
					{
						$node->appendChild($this->convertToXML($key, $v));
					}
				}
				else
				{
					// better empty node
					if ( (isset($arr[ 'cdata' ]) && $arr[ 'cdata' ]) || (isset($arr[ 'value' ]) && $arr[ 'value' ]) || (isset($arr[ 'attributes' ]) && $arr[ 'attributes' ]) || is_array($value)
					)
					{
						$node->appendChild($this->convertToXML($key, $value));
					}
				}
				unset($arr[ $key ]); //remove the key from the array once done.
			}
		}
		else
		{
			// after we are done with all the keys in the array (if it is one)
			// we check if it has any text value, if yes, append it.
			if ( $arr )
			{
				$node->appendChild($xml->createTextNode($this->bool2str($arr)));
			}
		}

		return $node;
	}

	/*
	 * Get string representation of boolean value
	 */

    /**
     * @param $v
     * @return string
     */
    private function bool2str ( $v )
	{

		//convert boolean to text value.
		$v = $v === true ? 'true' : $v;
		$v = $v === false ? 'false' : $v;

		return $v;
	}

	/*
	 * Check if the tag name or attribute name contains illegal characters
	 * Ref: http://www.w3.org/TR/xml/#sec-common-syn
	 */

    /**
     * @param $tag
     * @return bool
     */
    private function isValidTagName ( $tag )
	{

		$pattern = '/^[a-z_]+[a-z0-9\:\-\.\_]*[^:]*$/i';

		return preg_match($pattern, $tag, $matches) && $matches[ 0 ] == $tag;
	}

    /**
     * Convert an Array to XML
     *
     * @param mixed $node - XML as a string or as an object of DOMDocument
     * @throws Exception
     * @return mixed
     */
	private function &convertToArray ( $node )
	{

		$output = array ();

		switch ( $node->nodeType )
		{
			case XML_CDATA_SECTION_NODE:
				$output[ 'cdata' ] = trim($node->textContent);
				break;

			case XML_TEXT_NODE:
				$output = trim($node->textContent);
				break;

			case XML_ELEMENT_NODE:

				// for each child node, call the covert function recursively
				for ( $i = 0, $m = $node->childNodes->length; $i < $m; $i++ )
				{
					$child = $node->childNodes->item($i);
					$v     = '';
					if ( $child->nodeType == XML_TEXT_NODE || $child->nodeType == XML_CDATA_SECTION_NODE )
					{
						$t = $child->tagName;

						//check if it is not an empty text node
						if ( trim($child->textContent) !== '' )
						{
							$output = trim($child->textContent);
						}
					}
					else
					{

							$v = $this->convertToArray($child);


						if ( isset($child->tagName) )
						{
							$t = $child->tagName;

							// assume more nodes of same kind are coming
							if ( !isset($output[ $t ]) )
							{
								$output[ $t ] = array ();
							}

							try
							{
								$output[ $t ][ ] = $v;
							}
							catch ( Exception $e )
							{
								throw new Exception;
							}
						}
					}
				}

				if ( is_array($output) )
				{
					// if only one node of its kind, assign it directly instead if array($value);
					foreach ( $output as $t => $v )
					{
						if ( is_array($v) && count($v) == 1 )
						{
							$output[ $t ] = $v[ 0 ];
						}
					}

					if ( empty($output) )
					{
						//for empty nodes
						$output = '';
					}
				}
				else {
					$output[$t] = $output;
				}

				// loop through the attributes and collect them
				if ( $node->attributes->length )
				{
					$a = array ();
					foreach ( $node->attributes as $attrName => $attrNode )
					{
						$a[ $attrName ] = (string)$attrNode->value;
					}
					// if its an leaf node, store the value in @value instead of directly storing it.
					if ( !is_array($output) && !empty($output) )
					{
						$output = array (
							'value' => $output
						);
					}
					$output[ 'attributes' ] = $a;
				}
				break;
		}

		return $output;
	}

}
