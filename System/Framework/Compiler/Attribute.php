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
 * @file         Attribute.php
 */

class Compiler_Attribute
{

	// Attribute types

	/**
	 *
	 */
	const STRING = 1;

	/**
	 *
	 */
	const HARD_STRING = 2;

	/**
	 *
	 */
	const NUMBER = 3;

	/**
	 *
	 */
	const EXPRESSION = 4;

	/**
	 *
	 */
	const ASSIGN_EXPR = 5;

	/**
	 *
	 */
	const ID = 6;

	/**
	 *
	 */
	const BOOL = 7;

	/**
	 *
	 */
	const ID_EMP = 8; // Same as "ID", but allows empty content.
	/**
	 *
	 */

	const EXPRESSION_EXT = 9;

	/**
	 *
	 */
	const OPERATOR = 10;

	/**
	 *
	 */
	const MATH_OPERATOR = 11;

	/**
	 * Attribute Parameter
	 */
	const REQUIRED = 1;

	/**
	 *
	 */
	const OPTIONAL = 2;

	/**
	 * @var null
	 */
	private $_NS = null;

	/**
	 * @var
	 */
	private $_name = '';

	/**
	 * @var string
	 */
	private $_value = '';

	/**
	 * @var bool
	 */
	private $_useexpression = false;

	/**
	 * @var null
	 */
	protected $_option = null;

	/**
	 * @var bool|null
	 */
	private $_isRequired = null;

	/**
	 * @var bool|null
	 */
	private $_isOptional = null;



	/**
	 * @var null|String
	 */
	private $_xmlTagName = null;

	/**
	 * @var null
	 */
	private $_attrDefault = null;

	/**
	 * @var Compiler_Template
	 */
	protected $Template = null;

	/**
	 * Is the node hidden?
	 * @internal
	 * @var boolean
	 */
	protected $_hidden = null;

	protected $_currentTag = '';

	protected $_tagname = '';

	protected $_attributes = null;

	protected $_isEmptyTag = false;

	protected $_isEndTag = false;

	/**
	 *
	 * @var integer
	 */
	private $_attrtype = null;

	private $_attributConfig = array();


	/**
	 *
	 * @param Compiler_Tag $tag
	 * @param Compiler     $compiler
	 * @param array        $attributConfig is the config for required, default and other attribut options
	 * @param array        $attribut the attribut data
	 */
	public function __construct ( Compiler_Tag &$tag, Compiler &$compiler, array $attributConfig, $attribut = null )
	{
		//$this->setCompiler($tag->_compiler);
		#$this->_compiler = $compiler;
		$this->Template = $tag->getTemplateInstance();
        $this->_tagname = $tag->getTagName();
        $this->_xmlTagName    = $tag->getXmlName();

		if ( is_array($attribut) )
		{
			$this->_name = $attribut[ 'name' ];
			$this->_NS   = !empty( $attribut[ 'ns' ] ) && $attribut[ 'ns' ] != ':' ? $attribut[ 'ns' ] : false;
			$this->setValue($attribut[ 'value' ]);
			$this->_useexpression = isset( $attribut[ 'useexpression' ] ) ? $attribut[ 'useexpression' ] : false;


			if ( isset( $attributConfig[ $attribut[ 'name' ] ] ) )
			{
				if ( $attributConfig[ $attribut[ 'name' ] ] === self::REQUIRED )
				{
					$this->_isRequired = true;
				}
				elseif ( $attributConfig[ $attribut[ 'name' ] ] === self::REQUIRED )
				{
					$this->_isOptional = true;
				}

				// Attribut Type
				$this->_attrtype = $attributConfig[ $attribut[ 'name' ] ][ 1 ];

				// Default value
				if ( isset( $attributConfig[ $attribut[ 'name' ] ][ 2 ] ) )
				{
					$this->_attrDefault = $attributConfig[ $attribut[ 'name' ] ][ 2 ];
				}
			}
		}
	}


	/**
	 * @return string
	 */
	public function __toString ()
	{

		return $this->_value;
	}

	/**
	 *
	 * @return boolean
	 */
	public function isRequired ()
	{

		return ( $this->_isRequired === true ? true : false );
	}

	/**
	 *
	 * @return boolean
	 */
	public function isOptional ()
	{

		return ( $this->_isOptional === true ? true : false );
	}

	/**
	 *
	 * @param string $name
	 */
	public function setName ( $name )
	{

		if ( strpos($name, ':') !== false )
		{
			$names       = explode(':', $name);
			$this->_NS   = $names[ 0 ];
			$this->_name = $names[ 1 ];
		}
		else
		{
			$this->_name = $name;
		}
	}

	/**
	 * set the attribut value
	 *
	 * @param string  $value
	 * @param boolean $escape default is false
	 * @internal param string $name
	 */
	public function setValue ( $value = '', $escape = false )
	{
        // $value = $this->Template->getCompiler()->getTemplate()->filterContent($this->_tagname, $this->_name, $value, $escape );
		$this->_value = ( $escape ? htmlspecialchars($value) : $value );
	}

	/**
	 * returns the attribut value
	 *
	 * @param boolean $returnOnly default is false.<br/>
	 *                            Will test the attribut type if is set "false" and return the parsed value. If the<br/>
	 *                            attribut type not set then will return the unparsed value.<br/>
	 *                            If this value "true", then ignore the attribut type and <br/>
	 *                            return the unparsed value.
	 * @param integer $type       default is null.<br/>
	 *                            If set the type-integer then will use this type to parse the value.
	 * @throws Compiler_Exception
	 * @return string
	 */
	public function getValue ( $returnOnly = false, $type = null )
	{


		$value = (string)$this->_value;
		if ( $returnOnly === true )
		{
			return $value;
		}

		$_type = ( $type !== null ? $type : $this->_attrtype );

		if ( is_null($_type ))
		{
			#Error::raise('Invalid type of attribut content for attribute "'.$this->_name. '" in '. $this->_xmlTagName .' Value: '. $value, 'PHP', __FILE__, __LINE__);
		}

		switch ( $_type )
		{

			case self::MATH_OPERATOR :
				switch ( $value )
				{
					case '+':
					case '*':
					case '/':
					case '-':
					case '%':
						return $value;
					default:
						throw new Compiler_Exception( 'Invalid type for the attribute "' . $this->getXmlName() . '" in ' . $this->_xmlTagName . ': operator expected. Value: ' . $value );
						break;
				}
				break;

			case self::OPERATOR :
				switch ( $value )
				{
					case '++':
					case '--':
						return $value;
					default:
						throw new Compiler_Exception( 'Invalid type for the attribute "' . $this->getXmlName() . '" in ' . $this->_xmlTagName . ': operator expected. Value: ' . $value );
						break;
				}
				break;


			case self::ID_EMP:
				if ( $value == '' )
				{
					return $value;
				}
				break;

			case self::ID:
				if ( !preg_match('/^[a-z0-9\-\_\.]+$/i', $value) )
				{
					throw new Compiler_Exception( 'Invalid type for the attribute "' . $this->getXmlName() . '" in ' . $this->_xmlTagName . ': identifier expected. Value: ' . $value );
				}

				return $value;
				break;

			case self::NUMBER:
				if ( !preg_match('/^\-?([0-9]+\.?[0-9]*)|(0[xX][0-9a-fA-F]+)$/', $value) )
				{
					throw new Compiler_Exception( 'Invalid type for the attribute "' . $this->getXmlName() . '" in ' . $this->_xmlTagName . ': number expected. Value: ' . $value );
				}

				return $value;
				break;

			case self::BOOL:
				$value = strtolower($value);

				if ( $value !== 'yes' && $value !== 'no' && $value !== 'true' && $value !== 'false' )
				{
					throw new Compiler_Exception( 'Invalid type for the attribute "' . $this->getXmlName() . '" in ' . $this->_xmlTagName . ': yes, no, true or false expected. Value: ' . $value );
				}

				if ( $value === 'true' || $value === 'false' )
				{
					return ( $value === 'true' );
				}


				if ( $value == 1 || $value == 0 )
				{
					return ( $value == 1 );
				}


				return ( $value === 'yes' );
				break;

			case self::EXPRESSION:
			case self::EXPRESSION_EXT:

				if ( empty( $value ) || strlen(trim($value)) === 0 )
				{
					return array ( $value );
				}


				if ( strlen(trim($value)) === 0 )
				{
					throw new Compiler_Exception( 'The attribute "' . $this->getXmlName() . '" in ' . $this->_xmlTagName . ' is empty.' );
				}


				$result = $this->Template->compileExpression($value, null, false, Compiler_Template_Abstract::ESCAPE_ON, true);

				return $result;

				break;

			// A string packed into PHP expression. Can be switched to EXPRESSION.
			case self::STRING:
			case self::HARD_STRING:


				if ( $_type === self::HARD_STRING )
				{
					return $value;
				}

				if ( trim( $value ) === '' )
				{
					return array (
						0 => '\'\''
					);
				}





				if ( preg_match('/\{?('. $this->Template->_rVariable . '|' . $this->Template->_rFunctions .'[^\}\{]*\))\}?/sU', $value) )
				{
					#echo 'compileExpression _rFunctions ' .$this->_name .' ' . $this->_xmlTagName .' '. $value ."\n";
					$result = $this->Template->compileExpression($value);
				} /*
				elseif ( preg_match('/\{?(' . $this->Template->_rVariable . ')\}?/isU', $value, $match) )
				{
					#echo 'compileExpression _rVariable ' .$this->_name .' ' . $this->_xmlTagName .' '. $value ."\n";
					$result = array(
						0 => $this->Template->compileVariable($match[1])
					);
				} */
				else
				{
					#echo 'compileString ' .$this->_name .' ' . $this->_xmlTagName .' '. $value ."\n";
					$result = array (
						0 => $this->Template->compileString($value)
					);
				}


				return $result;

				break;
		}


		return $value;
	}

	/**
	 * Returns the tag name (with the namespace, if possible)
	 *
	 * @return string
	 */
	public function getXmlName ()
	{

		if ( !$this->_NS )
		{
			return $this->_name;
		}

		return $this->_NS . ':' . $this->_name;
	}

	/**
	 * Returns the tag name
	 *
	 * @return string
	 */
	public function getName ()
	{

		return $this->_name;
	}

	/**
	 * Returns the tag name
	 *
	 * @return string
	 */
	public function getNamespace ()
	{

		return $this->_NS;
	}
}