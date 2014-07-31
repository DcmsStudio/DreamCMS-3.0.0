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
 * @file         Abstract.php
 */

abstract class Compiler_Template_Abstract
{


	// Bit Codes
	/**
	 *
	 */
	const BIT_VARIABLE = 1;

	/**
	 *
	 */
	const BIT_LANGUAGE_VAR = 2;

	/**
	 *
	 */
	const BIT_STRING = 4;

	/**
	 *
	 */
	const BIT_NUMBER = 8;

	/**
	 *
	 */
	const BIT_ARRAY = 16;

	/**
	 *
	 */
	const BIT_OBJECT = 32;

	/**
	 *
	 */
	const BIT_IDENTIFIER = 64;

	/**
	 *
	 */
	const BIT_OPERATOR = 128;

	/**
	 *
	 */
	const BIT_POST_OPERATOR = 256;

	/**
	 *
	 */
	const BIT_PRE_OPERATOR = 512;

	/**
	 *
	 */
	const BIT_ASSIGN = 1024;

	/**
	 *
	 */
	const BIT_NULL = 2048;

	/**
	 *
	 */
	const BIT_SQ_BRACKET = 4096;

	/**
	 *
	 */
	const BIT_SQ_BRACKET_E = 8192;

	/**
	 *
	 */
	const BIT_FUNCTION = 16384;

	/**
	 *
	 */
	const BIT_METHOD = 32768;

	/**
	 *
	 */
	const BIT_BRACKET = 65536;

	/**
	 *
	 */
	const BIT_CLASS = 131072;

	/**
	 *
	 */
	const BIT_CALL = 262144;

	/**
	 *
	 */
	const BIT_FIELD = 524288;

	/**
	 *
	 */
	const BIT_EXPRESSION = 1048576;

	/**
	 *
	 */
	const BIT_OBJMAN = 2097152;

	/**
	 *
	 */
	const BIT_BRACKET_E = 4194304;

	/**
	 *
	 */
	const BIT_TU = 8388608;

	/**
	 *
	 */
	const BIT_CURLY_BRACKET = 16777216;

	/**
	 *
	 */
	const ESCAPE_ON = true;

	/**
	 *
	 */
	const ESCAPE_OFF = false;

	/**
	 *
	 */
	const ESCAPE_BOTH = 2;


	//
	/**
	 * @var array
	 */
	protected $_attributNamespaces = array (
		'parse' => true,
		'cycle' => true,
		'if'    => true,
		'on'    => true
	);

	// Regular expressions
	/**
	 * @var string
	 */
	private $_rCDataExpression = '/(\<\!\[CDATA\[|\]\]\>)/msi';

	/**
	 * @var string
	 */
	private $_rCommentExpression = '/(\<\!\-\-|\-\-\>)/si';

	/**
	 * @var string
	 */
	private $_rCommentSplitExpression = '/(\<\!\-\-(.*?)\-\-\>)/si';

	/**
	 * @var string
	 */
	private $_rOpeningChar = '[a-zA-Z\:\_]';

	/**
	 * @var string
	 */
	private $_rNameChar = '[a-zA-Z0-9\:\.\_\-]';

	/**
	 * @var string
	 */
	public $_rExpressionTag = '/(\{([^\}\{]*)\})/msi';

	/**
	 * @var string
	 */
	private $_rAttributeTokens = '/(?:[^\=\"\'\s]+|\=|\"|\'|\s)/x';

	/**
	 * @var string
	 */
	private $_rPrologTokens = '/(?:[^\=\"\'\s]+|\=|\'|\"|\s)/x';

	/**
	 * @var string
	 */
	private $_rModifiers = 'si';

	/**
	 * @var string
	 */
	private $_rXmlHeader = '/(\<\?xml.+\?\>)/msi';

	/**
	 * @var string
	 */
	private $_rProlog = '/\<\?xml(.+)\?\>|/msi';

	/**
	 * @var string
	 */
	private $_rEncodingName = '/[A-Za-z]([A-Za-z0-9.\_]|\-)*/si';

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
	public $_rQuoteString = '"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"';
    /**
     * @var string
     */
    public $_rString = '"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"|\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'';


	/**
	 * @var string
	 */
	public $_rHexadecimalNumber = '\-?0[xX][0-9a-fA-F]+';

	/**
	 * @var string
	 */
	public $_rDecimalNumber = '[0-9]+?\.?[0-9]*';

	/**
	 * @var string
	 */
	public $_rVariable = '\$[a-zA-Z\_][a-zA-Z0-9\-\_\.]*';

	/**
	 * @var string
	 */
	public $_rConstante = '@[a-zA-Z\_][a-zA-Z0-9\-\_\.]*';

	/**
	 * @var string
	 */
	public $_rFunctions = '[a-zA-Z][a-zA-Z\_]*\s*\(';

	/**
	 * @var string
	 */
	public $_rOperators = '\-\>|!==|===|==|!=|\=\>|<>|<<|>>|<=|>=|\&\&|\|\||\(|\)|,|\!|\^|=|\&|\~|<|>|\||\%|\+\+|\-\-|\+|\-|\*|\/|\[|\]|\.|\:\:|\{|\}|\'|\"|';

	/**
	 * @var string
	 */
	public $_rIdentifier = '[a-zA-Z\_][a-zA-Z0-9\_\.]*';

	/**
	 * stores the custom plugins registered with this compiler
	 *
	 * @var array
	 */
	protected $customPlugins = array ();

	/**
	 * stores a list of plugins that are used in the currently compiled
	 * template, and that are not compilable. these plugins will be loaded
	 * during the template's runtime if required.
	 *
	 * it is a 1D array formatted as key:pluginName value:pluginType
	 *
	 * @var array
	 */
	protected $usedPlugins = array ();

	/**
	 * stores all block names
	 *
	 * @var array
	 */
	protected $usedBlocks = array ();

	/**
	 *
	 * @var array
	 */
	protected $_functions = array ();

	/**
	 *
	 * @var array
	 */
	protected $_classes = array ();

	/**
	 *
	 * @var array
	 */
	protected $_option = array ();

	/**
	 *
	 * @var array
	 */
	protected $_currentTag = array ();

    /**
     * @var Compiler
     */
    protected $compiler = false;
    /**
     * @var Compiler_Template
     */
    public $templateInstance = false;

	/**
	 * @var Compiler_Scope
	 */
	protected $scope = null;

	protected $_datacache = null;

    protected $_currentCompileTag = null;

    /**
     * @return bool|Compiler
     */
    public function getCompiler()
    {
        return $this->templateInstance->getCompiler();
    }

    /**
     * @param string $tagstr
     */
    public function setCurrentSourceTag($tagstr = null)
    {
        $this->_currentCompileTag = $tagstr;
    }

    /**
     *
     *
     * @param string $originalname
     * @param string $scopename the varname in loop/foreach/for/tree
     * @param int $nested
     */
	public function addScope ( $originalname, $scopename, $nested = 0 )
	{

		$this->scope->addScope($originalname, $scopename, $nested);
	}

	/**
	 *
	 * @param string $originalname
	 */
	public function removeScope ( $originalname )
	{
		$this->scope->removeScope ( $originalname );
	}

    /**
     * @param $name
     * @return array
     */
	public function getScope ( $name )
	{
		return $this->scope->getScope ( $name );
	}

	/**
	 * @return array
	 */
	public function getScopes ()
	{
		return $this->scope->getScopes ( );
	}


    /**
     * @param null $scopes
     */
    public function setScopes ( $scopes = null )
	{
		return $this->scope->setScopes ( $scopes );
	}

    /**
     * @param $originalname
     * @param int $cn
     */
    public function removeLastScope($originalname, $cn = 0) {
		$this->scope->removeLastScope ( $originalname, $cn );
	}
	/**
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	public function set ( $key, $value )
	{

		if ( !is_array($this->_option) )
		{
			$this->_option = array ();
		}

		$this->_option[ $key ] = $value;
	}

	/**
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get ( $key )
	{

		return ( isset( $this->_option[ $key ] ) ? $this->_option[ $key ] : null );
	}

	/**
	 *
	 * @param string $namespaceStr
	 * @return bool
	 */
	public function isAttributeNamespace ( $namespaceStr = '' )
	{

		if ( in_array($namespaceStr, $this->_attributNamespaces) )
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
	public function isTagNamespace ( $namespaceStr = '' )
	{

		return ( Compiler::TAGNAMESPACE === $namespaceStr ? true : false );
	}

	/**
	 *
	 * @param string $namespaceStr
	 * @return bool
	 */
	public function isCompilerNamespace ( $namespaceStr = '' )
	{

		if ( $this->isAttributeNamespace($namespaceStr) || Compiler::TAGNAMESPACE === $namespaceStr )
		{
			return true;
		}

		return false;
	}

    /**
     * @param $name
     * @param $value
     */
    public function addBlock ( $name, $value )
	{

		$this->_useBlocks[ $name ] = $value;
	}


	/**
	 *
	 * @param array $tagData
	 * @deprecated
	 */
	public function setCurrentTag ( array $tagData )
	{

		$this->_currentTag = $tagData;
	}

	/**
	 *
	 * @param string $tag
	 * @param array  $attr
	 * @param bool   $removeTagPrefix
	 * @return string
	 */
	public function removeAtributesFromTag ( $tag, $attr = array (), $removeTagPrefix = false )
	{

		$tag = preg_replace('#(\s*(' . implode('|', $attr) . ')\s*=\s*(["\'])([^\2]*)\2)#iU', '', $tag);

		if ( $removeTagPrefix )
		{
			$tag = preg_replace('#<' . preg_quote(Compiler::TAGNAMESPACE, '#') . '#i', '<', $tag);
		}

		return $tag;
	}

	/**
	 *
	 * @param string  $expression
	 * @param boolean $status will return "'htmlspecialchars(' . $expression . ')'"
	 * @return string
	 */
	public function escape ( $expression, $status = null )
	{

		$escape = false;

		// Expression settings
		if ( !is_null($status) )
		{
			$escape = ( $status === true ? true : false );
		}

		if ( $escape )
		{
			return 'htmlspecialchars(' . $expression . ')';
		}

		return $expression;
	}

    /**
     * @param $expr
     * @param null $currentScopeName
     * @param bool $allowAssignment
     * @param bool $escape
     * @return array
     */
    public function compileExpression0 ( $expr, $currentScopeName = null, $allowAssignment = false, $escape = self::ESCAPE_ON )
    {
        $expression = new Compiler_Expression_Standard($this->templateInstance->getCompiler());
        $comp = $expression->parse($expr, true);

        return array (
            $comp['bare'],
            $comp['type'] == Compiler_Expression_Interface::ASSIGNMENT,
            $comp['type'] == Compiler_Expression_Interface::SINGLE_VAR,
            $comp['bare']
        );
    }


    /**
     *
     * @param string $expr
     * @param string $currentScopeName
     * @param boolean $allowAssignment =false True, if the assignments are allowed.
     * @param boolean $escape =true The HTML escaping policy for this expression.
     *
     * @param bool $isIf
     * @throws Compiler_Exception
     * @return array
     */
	public function compileExpression ( $expr, $currentScopeName = null, $allowAssignment = false, $escape = self::ESCAPE_ON, $isIf = false )
	{

		// The expression modifier must not be tokenized, so we
		// capture it before doing anything with the expression.
		$modifier = '';
		if ( preg_match('/^([^\'])\:[^\:]/sU', $expr, $found) )
		{
			$modifier = $found[ 1 ];
			if ( $modifier !== 'e' && $modifier !== 'u' )
			{
				throw new Compiler_Exception( 'Invalid Expression Modifier `' . $modifier . '`. Expression: ' . $expr, $this->_currentTag );
			}

			$expr = substr($expr, 2, strlen($expr) - 2);
		}


		// cat $expr > /dev/oracle > $result > happy programmer :)
		preg_match_all('/(?:' . $this->_rSingleQuoteString . '|' . $this->_rQuoteString . '|' . $this->_rBacktickString . '|' . $this->_rHexadecimalNumber . '|' . $this->_rDecimalNumber . '|' . $this->_rVariable . '|' . $this->_rConstante . '|' . $this->_rOperators . '|' . $this->_rIdentifier . ')/x', $expr, $match);


		// Skip the whitespaces and create the translation units
		$cnt         = sizeof($match[ 0 ]);
		$stack       = new SplStack;
		$tu          = array (
			0 => array ()
		);
		$tuid        = 0;
		$maxTuid     = 0;
		$prev        = '';
		$chr         = chr(18);
		$assignments = array ();


		/* The translation units allow to avoid recursive compilation of the
		 * expression. Each sub-expression within parentheses and that is a
		 * function call parameter, becomes a separate translation unit. The
		 * loop below scans the array of tokens, looking for translation
		 * unit separators and builds suitable arrays of tokens for each
		 * TU.
		 */
		for ( $i = 0; $i < $cnt; $i++ )
		{
			if ( ctype_space($match[ 0 ][ $i ]) || $match[ 0 ][ $i ] === '' )
			{
				continue;
			}

			switch ( $match[ 0 ][ $i ] )
			{
				case ',':
					if ( $prev === '(' || $prev === ',' )
					{
						throw new Compiler_Exception( 'Invalid Expression comma `' . $match[ 0 ][ $i ] . '`. Expression: ' . $expr );
					}

					$tuid = $stack->pop();

					if ( in_array($tuid, $assignments) )
					{
						$tuid = $stack->pop();
					}

				case '[':
				case '(':
				case 'is':
				case '=':
					if ( $match[ 0 ][ $i ] === '=' || $match[ 0 ][ $i ] === 'is' )
					{
						$assignments[ ] = $tuid;
					}

					$tu[ $tuid ][ ] = $match[ 0 ][ $i ];
					++$maxTuid;
					$tu[ $tuid ][ ] = $chr . $maxTuid; // A fake token that marks the translation unit which goes here.
					$stack->push($tuid);
					$tuid        = $maxTuid;
					$tu[ $tuid ] = array ();
					break;

				case ']':
				case ')':
					// If we have a situation like (), we can remove the TU we've just created,
					// because it's empty and will confuse the expression compiler later.
					if ( $prev === '(' )
					{
						unset( $tu[ $tuid ] );
						--$maxTuid;
					}

					if ( $stack->count() > 0 )
					{
						$tuid = $stack->pop();
						if ( in_array($tuid, $assignments) )
						{
							$tuid = $stack->pop();
						}
					}

					if ( $prev === '(' )
					{
						array_pop($tu[ $tuid ]);
					}

					if ( $prev === ',' )
					{
						throw new Compiler_Exception( 'Invalid Expression `' . $match[ 0 ][ $i ] . '`. Expression: ' . $expr );
					}

					$tu[ $tuid ][ ] = $match[ 0 ][ $i ];
					break;
				default:
					$tu[ $tuid ][ ] = $match[ 0 ][ $i ];
			}
			$prev = $match[ 0 ][ $i ];
		}

		if ( sizeof($tu[ 0 ]) === 0 )
		{
			throw new Compiler_Exception( 'Expression is empty! Expression: ' . $expr );
		}

		#print_r($tu);

		/*
		 * Now we have an array of translation units and their tokens and
		 * we can process it linearly, thus avoiding recursive calls.
		 */
		$tuItem = array ();
		foreach ( $tu as $id => &$tuItem )
		{
			$tuItem    = $this->_compileExpression($expr, $currentScopeName, $allowAssignment, $tuItem, $id, $isIf);
			$tu[ $id ] = $tuItem;
		}


		$assign   = $tu[ 0 ][ 1 ];
		$variable = $tu[ 0 ][ 2 ];

		/*
		 * Finally, we have to link all the subexpressions into an output
		 * expression. We use SPL stack to achieve this, because we need
		 * to store the current subexpression status while finding a new one.
		 */
		$tuid       = 0;
		$i          = -1;
		$cnt        = sizeof($tu[ 0 ][ 0 ]);
		$stack      = new SplStack;
		$prev       = null;
		$expression = '';

		while ( true )
		{
			$i++;
			$token = & $tu[ $tuid ][ 0 ][ $i ];

			// If we've found a translation unit, we must stop for a while the current one
			// and link the new.
			if ( strlen($token) > 0 && ( $token[ 0 ] == $chr || $token == chr(32) ) )
			{
				$wasAssignment = in_array($tuid, $assignments);
				$stack->push(array (
				                   $tuid,
				                   $i,
				                   $cnt
				             ));
				$tuid = (int)ltrim($token, $chr);
				$i    = -1;
				$cnt  = sizeof($tu[ $tuid ][ 0 ]);
				if ( $cnt == 0 && $wasAssignment )
				{
					throw new Compiler_Exception( 'Invalid Expression (NULL)! Expression: ' . $expr );
				}
				continue;
			}
			else
			{
				$expression .= $token;
			}

			if ( $i >= $cnt )
			{
				if ( $stack->count() == 0 )
				{
					break;
				}
				// OK, current TU is ready. Check, whether there are unfinished upper-level TUs
				// on the stack
				unset( $tu[ $tuid ] );
				list( $tuid, $i, $cnt ) = $stack->pop();
			}

			$prev = $token;
		}


		/*
		 * Now it's time to apply the escaping policy to this expression. We check
		 * the expression for the "e:" and "u:" modifiers and redirect the task to
		 * the escape() method.
		 */
		$result = $expression;
		if ( $escape != self::ESCAPE_OFF && !$assign )
		{
			if ( $modifier != '' )
			{
				$result = $this->escape($result, $modifier == 'e');
			}
			else
			{
				$result = $this->escape($result);
			}
		}



		// Pack everything
		if ( $escape != self::ESCAPE_BOTH )
		{
			return array (
				0 => $result,
				$assign,
				$variable,
				null
			);
		}
		else
		{
			return array (
				0 => $result,
				$assign,
				$variable,
				$expression
			);
		}
	}

    /**
     * Compiles a single translation unit in the expression.
     *
     * @param string &$expr A reference to the compiled expressions for debug purposes.
     * @param string $currentScopeName
     *
     *
     * @param boolean $allowAssignment True, if the assignments are allowed in this unit.
     * @param array &$tokens A reference to the array of tokens for this translation unit.
     * @param string $tu The number of the current translation unit.
     * @param bool $isIf
     * @throws Compiler_Exception
     * @return array An array build of three items: the compiled expression, the assignment status
     *                                 and the variable status (whether the expression is in fact a single variable).
     */
	public function _compileExpression ( $expr, $currentScopeName, $allowAssignment, array &$tokens, &$tu, $isIf = false )
	{

		// Operator mappings
		$wordOperators = array (
			'eq'   => '==',
			'eqt'  => '===',
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

		// Previous token information
		$previous = array (
			'token'  => null,
			'source' => null,
			'result' => null
		);

		// Some standard "next token sets"
		$valueSet = self::BIT_VARIABLE | self::BIT_LANGUAGE_VAR | self::BIT_STRING | self::BIT_NUMBER | self::BIT_IDENTIFIER | self::BIT_PRE_OPERATOR | self::BIT_OBJMAN | self::BIT_BRACKET;

		$operatorSet = self::BIT_OPERATOR | self::BIT_POST_OPERATOR | self::BIT_NULL;


		// Initial state
		$state = array (
			'prev'        => null,
			'next'        => $valueSet | self::BIT_NULL, // What token must occur next.
			'step'        => 0, // This flag helps processing brackets by saving some extra token information.
			'func'        => 0, // The function call type: 0 - function (with "$this" as the first argument); 1 - ordinary function
			'oper'        => false, // The assignment flag. The value must be assigned to a variable, so on the left side there must not be any operator (false).
			'clone'       => 0, // We've already used "clone"
			'preop'       => false, // Prefix operators ++ and -- found. This flag is cancelled by any other operator.
			'rev'         => null, // Changing the argument order options
			'assign_func' => false, // Informing the bracket parser that the first argument must be a language block, which must be processed separately.
			'tu'          => 0, // What has opened a translation unit? The field contains the token type.
			'variable'    => null, // To detect if the expression is a single variable or not.
			'function'    => null // Function name for the argument checker errors
		);


		#  print_r($tokens);


		$chr    = chr(18); // Which ASCII code marks the translation unit
		$result = array (); // Here we put the compilation result
		$void   = false; // This is a fake variable for a recursive call, as a last argument (reference)
		$assign = false;
		$to     = sizeof($tokens);


		// Loop through the token list.
		for ( $i = 0; $i < $to; ++$i )
		{
			// Some initializing stuff.
			$token     = & $tokens[ $i ];
			$parsefunc = false;
            $tokenlen  = strlen($token);

            $tokenfirstchar = $token[0];
            $tokenlastchar  = $token[$tokenlen-1];



			// echo $token . "<br/>\n";

			$current = array (
				'token'  => null, // Symbolic token type. Look at the file header to find the token definitions.
				'source' => $token, // Original form of the token is also remembered.
				'result' => null, // Here we have to put the result PHP code generated from the token.
			);


			// Find out, what it is and process it.
			switch ( $token )
			{

				case '[':
					// This code checks, whether the token is properly used. We have to assign it to one of the token groups.
					if ( !( $state[ 'next' ] & self::BIT_SQ_BRACKET ) )
					{
						throw new Compiler_Exception( 'Invalid BIT_SQ_BRACKET ' . $token . ' in Expression: ' . $expr . ' @' . __LINE__, $this->_currentCompileTag );
					}

					$result[ ]       = '[';
					$state[ 'tu' ]   = self::BIT_SQ_BRACKET_E;
					$state[ 'next' ] = self::BIT_TU;
					$state[ 'step' ] = self::BIT_VARIABLE;
					continue;

				case ']':
					if ( !( $state[ 'next' ] & self::BIT_SQ_BRACKET_E ) )
					{
						throw new Compiler_Exception( 'Invalid BIT_SQ_BRACKET_E ' . $token . ' in Expression: ' . $expr . ' @' . __LINE__, $this->_currentCompileTag );
					}
					$current[ 'token' ]  = $state[ 'step' ];
					$current[ 'result' ] = ']';
					$state[ 'step' ]     = 0;
					// This is the way we mark, what tokens can occur next.
					$state[ 'next' ] = self::BIT_OPERATOR | self::BIT_NULL | self::BIT_SQ_BRACKET;
					if ( $state[ 'clone' ] == 1 )
					{
						$state[ 'next' ] = self::BIT_NULL | self::BIT_SQ_BRACKET;
					}
					break;

				// These tokens are invalid and must produce an error
				case '\'':
				case '"':
				case '{':
				case '}':

					throw new Compiler_Exception( 'Invalid BIT_CURLY_BRACKET ' . $token . ' in Expression: ' . $expr . ' @' . __LINE__, $this->_currentCompileTag );

					break;

				// Text operators.
				case 'add':
				case 'sub':
				case 'mul':
				case 'div':
				case 'mod':
				case 'shl':
				case 'shr':
				case 'eq':
				case 'neq':
				case 'eqt':
				case 'neqt':
				case 'ne':
				case 'net':
				case 'lt':
				case 'le':
				case 'lte':
				case 'gt':
				case 'gte':
				case 'ge':
					// die(''. $token. ' ' . $previous[ 'token' ] .' '.self::BIT_CALL );

					// These guys can be also method names, if in proper context
					if ( $previous[ 'token' ] == self::BIT_CALL )
					{
						$this->_compileIdentifier($token, $previous[ 'token' ], $previous[ 'result' ], isset( $tokens[ $i + 1 ] ) ? $tokens[ $i + 1 ] : null, $operatorSet, $expr, $current, $state);
						break;
					}
					else
					{

						$revert = isset( $wordOperators[ $token ] ) ? $wordOperators[ $token ] : null;
						#$result[] = $revert;
						#print_r($result);

						#  die('!BIT_CALL' . $token);

						$current[ 'result' ] = $revert;
						$current[ 'token' ]  = self::BIT_OPERATOR;
						#   $state['next'] = $valueSet;
						$state[ 'variable' ] = false;
						$state[ 'preop' ]    = false;
					}

				case '||':
				case '&&':
				case 'and':
				case 'or':
				case 'xor':

					$this->_testPreOperators($previous[ 'token' ], $state[ 'preop' ], $token, $expr);

					// And these three ones - only strings.
					if ( $state[ 'next' ] & self::BIT_STRING )
					{
						$current[ 'result' ] = '\'' . $token . '\'';
						$current[ 'token' ]  = self::BIT_STRING;
						$state[ 'next' ]     = $operatorSet | self::BIT_SQ_BRACKET_E;
					}
					else
					{
						if ( !( $state[ 'next' ] & self::BIT_OPERATOR ) )
						{
							throw new Compiler_Exception( 'Invalid BIT_OPERATOR ' . $token . ' in Expression: ' . $expr . ' @' . __LINE__, $this->_currentCompileTag );
						}
						$revert = isset( $wordOperators[ $token ] ) ? $wordOperators[ $token ] : $token;

						$current[ 'result' ] = ' ' . $revert . ' ';
						$current[ 'token' ]  = self::BIT_OPERATOR;
						#     $state[ 'next' ] = $valueSet;
						$state[ 'preop' ] = false;
					}

					$state[ 'variable' ] = false;
					break;

				case 'not':
					if ( !( $state[ 'next' ] & self::BIT_PRE_OPERATOR ) )
					{
						throw new Compiler_Exception( 'Invalid BIT_PRE_OPERATOR ' . $token . ' in Expression: ' . $expr . ' @' . __LINE__, $this->_currentCompileTag );
					}

					$current[ 'token' ]  = self::BIT_PRE_OPERATOR;
					$current[ 'result' ] = $wordOperators[ $token ];
					$state[ 'next' ]     = $valueSet;
					$state[ 'variable' ] = false;
					break;

				case 'is':
					if ( $state[ 'next' ] & self::BIT_STRING )
					{
						$current[ 'result' ] = '\'' . $token . '\'';
						$state[ 'next' ]     = $operatorSet | self::BIT_SQ_BRACKET_E | self::BIT_TU;
						break;
					}

				case '=':

					if ( !$allowAssignment )
					{
						throw new Compiler_Exception( 'Expression option is disabled! ' . $token . ' in Expression: ' . $expr . ' @' . __LINE__, $this->_currentCompileTag );
					}

					// We have to assign the data to the variable or object field.
					if ( ( $previous[ 'token' ] == self::BIT_VARIABLE || $previous[ 'token' ] == self::BIT_FIELD ) && !$state[ 'oper' ] && $previous[ 'token' ] != self::BIT_LANGUAGE_VAR )
					{
						$current[ 'result' ] = '';
						$current[ 'token' ]  = self::BIT_ASSIGN;
						$state[ 'variable' ] = false;
						$state[ 'next' ]     = self::BIT_TU;
						$state[ 'tu' ]       = self::BIT_NULL;
						$assign              = true;
					}
					else
					{
						throw new Compiler_Exception( 'Invalid BIT_ASSIGN ' . $token . ' in Expression: ' . $expr . ' @' . __LINE__, $this->_currentCompileTag );
					}
					break;


				case '!==':
				case '==':
				case '===':
				case '!=':
				case '+':
				case '*':
				case '/':
				case '%':
					if ( !( $state[ 'next' ] & self::BIT_OPERATOR ) )
					{
						throw new Compiler_Exception( 'Invalid BIT_OPERATOR ' . $token . ' in Expression: ' . $expr . ' @' . __LINE__, $this->_currentCompileTag );
					}


					$this->_testPreOperators($previous[ 'token' ], $state[ 'preop' ], $token, $expr);

					$current[ 'result' ] = $token;
					$state[ 'next' ]     = $valueSet;
					$state[ 'oper' ]     = true;
					$state[ 'preop' ]    = false;
					$state[ 'variable' ] = false;


					break;

				case '-':

					if ( $state[ 'next' ] & self::BIT_OPERATOR )
					{
						$this->_testPreOperators($previous[ 'token' ], $state[ 'preop' ], $token, $expr);

						$current[ 'result' ] = $token;
						$state[ 'oper' ]     = true;
						$state[ 'next' ]     = $valueSet;
						$state[ 'preop' ]    = false;
					}
					elseif ( $state[ 'next' ] & self::BIT_NUMBER | self::BIT_VARIABLE | self::BIT_IDENTIFIER )
					{
						$current[ 'result' ] = $token;
						$state[ 'next' ]     = self::BIT_NUMBER | self::BIT_VARIABLE | self::BIT_IDENTIFIER;
					}
					else
					{
						throw new Compiler_Exception( 'Invalid BIT_OPERATOR ' . $token . ' in Expression: ' . $expr . ' @' . __LINE__, $this->_currentCompileTag );
					}
					$state[ 'variable' ] = false;
					break;

				case '++':
				case '--':
					$current[ 'token' ] = self::BIT_PRE_OPERATOR;
					if ( !( $state[ 'next' ] & self::BIT_PRE_OPERATOR ) )
					{
						$current[ 'token' ] = self::BIT_POST_OPERATOR;
						if ( !( $state[ 'next' ] & self::BIT_POST_OPERATOR ) )
						{
							throw new Compiler_Exception( 'Invalid BIT_POST_OPERATOR ' . $token . ' in Expression: ' . $expr . ' @' . __LINE__, $this->_currentCompileTag );
						}
						else
						{
							$state[ 'next' ] = self::BIT_OPERATOR | self::BIT_NULL;
						}
					}
					else
					{
						$state[ 'next' ]  = self::BIT_VARIABLE | self::BIT_LANGUAGE_VAR | self::BIT_NUMBER;
						$state[ 'preop' ] = true;
					}

					$state[ 'oper' ]     = true;
					$state[ 'variable' ] = false;
					$current[ 'result' ] = $token;

					break;


				case '!':
					if ( !( $state[ 'next' ] & self::BIT_PRE_OPERATOR ) )
					{
						$current[ 'token' ] = self::BIT_POST_OPERATOR;
						if ( !( $state[ 'next' ] & self::BIT_POST_OPERATOR ) && !( $state[ 'next' ] & $operatorSet ) )
						{
							throw new Compiler_Exception( 'Invalid BIT_PRE_OPERATOR ' . $token . ' in Expression: ' . $expr . ' @' . __LINE__, $this->_currentCompileTag );
						}
						else
						{
							$state[ 'next' ] = self::BIT_OPERATOR | self::BIT_NULL;
						}
						//   throw new Compiler_Exception( 'Invalid BIT_PRE_OPERATOR ' . $token . ' in Expression: ' . $expr . ' '. $state[ 'next' ] . ' @' . __LINE__ );
					}

					$current[ 'result' ] = $token;
					$current[ 'token' ]  = self::BIT_PRE_OPERATOR;
					$state[ 'variable' ] = false;
					break;

				case 'null':
				case 'false':
				case 'true':
					// These special values are treated as numbers by the compiler.
					if ( !( $state[ 'next' ] & self::BIT_NUMBER ) )
					{
						throw new Compiler_Exception( 'Invalid BIT_NUMBER ' . $token . ' in Expression: ' . $expr . ' @' . __LINE__, $this->_currentCompileTag );
					}

					$current[ 'token' ]  = self::BIT_NUMBER;
					$current[ 'result' ] = $token;
					$state[ 'next' ]     = $operatorSet;
					break;



				case '.':
                        if ( !($state[ 'next' ] & $operatorSet) || !($state[ 'next' ] & self::BIT_SQ_BRACKET) || !($state[ 'next' ] & self::BIT_CALL))
                        {
                            throw new Compiler_Exception( 'Invalid Token "' . $token . '" in Expression: ' . $expr . ' @' . __LINE__, $this->_currentCompileTag );
                        }
                    $current[ 'result' ] = $token;
                    $state[ 'next' ]     = $operatorSet | self::BIT_SQ_BRACKET_E;
					break;

				case '(':

					// Check, if the parenhesis begins a function/method argument list
					if ( $previous[ 'token' ] == self::BIT_METHOD || $previous[ 'token' ] == self::BIT_FUNCTION || $previous[ 'token' ] == self::BIT_CLASS )
					{


						// Yes, this is a function call, so we need to find its arguments.
						$args = array ();
						for ( $j = $i + 1; $j < $to && $tokens[ $j ] != ')'; ++$j )
						{
							if ( $tokens[ $j ][ 0 ] == $chr )
							{
								$args[ ] = $tokens[ $j ];
							}
							elseif ( $tokens[ $j ] != ',' )
							{
								throw new Compiler_Exception( 'Invalid BIT_UNKNOWN ' . $token . ' in Expression: ' . $expr . ' @' . __LINE__, $this->_currentCompileTag );
							}
						}

						$argNum = sizeof($args);

						// Optionally, change the argument order
						if ( !is_null($state[ 'rev' ]) )
						{
							$this->_reverseArgs($args, $state[ 'rev' ], $state[ 'function' ]);
							$state[ 'rev' ] = null;
							$argNum         = sizeof($args);
						}

						// Put the parenthesis to the compiled token list.
						$result[ ] = '(';

						// If we have a call of the assign() function, we need to store the
						// number of the translation unit in the _translationConversion field.
						// This will allow the language variable compiler to notice that here
						// we should have a language call that must be treated in a bit different
						// way.
						if ( $argNum > 0 && $state[ 'assign_func' ] )
						{
							$this->_translationConversion = (int)trim($args[ 0 ], $chr);
						}

						// Build the argument list.
						for ( $k = 0; $k < $argNum; ++$k )
						{
							if ( $state[ 'function' ] === 'trans' )
							{
								$args[ $k ] = preg_replace('# lt #', '<', $args[ $k ]);
								$args[ $k ] = preg_replace('# gt #', '>', $args[ $k ]);
							}

							$result[ ] = $args[ $k ];
							if ( $k < $argNum - 1 )
							{
								$result[ ] = ',';
							}
						}


						$i               = $j - 1;
						$state[ 'next' ] = self::BIT_BRACKET_E;
						$state[ 'step' ] = $previous[ 'token' ];
						$state[ 'prev' ] = self::BIT_FUNCTION;

						continue;
					}
					else
					{
						if ( !( $state[ 'next' ] & self::BIT_BRACKET ) && !( $state[ 'next' ] & $operatorSet ) )
						{
							throw new Compiler_Exception( 'Invalid BIT_BRACKET ' . $token . ' in Expression: ' . $expr . ' @' . __LINE__, $this->_currentCompileTag );
						}

						$result[ ]       = '(';
						$state[ 'tu' ]   = self::BIT_BRACKET_E;
						$state[ 'next' ] = self::BIT_TU;
						$state[ 'step' ] = self::BIT_VARIABLE;
					}

					break;

				case ')':

					if ( $state[ 'step' ] == 0 )
					{
						throw new Compiler_Exception( 'Invalid BIT_BRACKET ' . $token . ' in Expression: ' . $expr . ' @' . __LINE__, $this->_currentCompileTag );
					}
					else
					{
						if ( !( $state[ 'next' ] & self::BIT_BRACKET_E ) )
						{
							throw new Compiler_Exception( 'Invalid BIT_BRACKET_E ' . $token . ' in Expression: ' . $expr . ' @' . __LINE__, $this->_currentCompileTag );
						}

						$current[ 'token' ]  = $state[ 'step' ];
						$current[ 'result' ] = ')';
						$state[ 'step' ]     = 0;
						$state[ 'next' ]     = self::BIT_OPERATOR | self::BIT_NULL | self::BIT_CALL;

						if ( $state[ 'clone' ] == 1 )
						{
							$state[ 'next' ] = self::BIT_NULL | self::BIT_CALL;
						}

						$state[ 'prev' ] = null;
					}

					break;


				default:
					if ( $token === chr(32) )
					{
						$result[ ]       = $token;
						$state[ 'next' ] = $state[ 'tu' ];
					}
					else if ( $token[ 0 ] == $chr )
					{
						// We've found another translation unit.
						if ( !( $state[ 'next' ] & self::BIT_TU ) )
						{
							throw new Compiler_Exception( 'Invalid BIT_TU. Translation unit #' . ltrim($token, $chr) . '. Expression:' . $expr . ' @' . __LINE__, $this->_currentCompileTag );
						}

						if ( $previous[ 'token' ] != self::BIT_ASSIGN )
						{
							$result[ ] = $token;
						}

						$state[ 'next' ] = $state[ 'tu' ];
					}
                    elseif ( ($tokenfirstchar === '"' || $tokenfirstchar === "'") && $tokenfirstchar === $tokenlastchar /*preg_match('/^(' . $this->_rString . ')$/isU', $token)*/ )
                    {
                        if ( !( $state[ 'next' ] & self::BIT_STRING ) )
                        {
                            throw new Compiler_Exception( $tokenfirstchar . ' == '. $tokenlastchar .' Invalid BIT_STRING ' . $token . ' in Expression: ' . $expr . ' @' . __LINE__, $this->_currentCompileTag );
                        }

                        $current[ 'result' ] = $this->compileString($token);
                        $state[ 'next' ]     = $operatorSet | self::BIT_SQ_BRACKET_E;
                    }
					elseif ( $tokenfirstchar === '@' && ctype_alnum($tokenlastchar)  /*preg_match('/^' . $this->_rConstante . '$/isU', $token)*/ )
					{

						// Variable call.
						if ( !( $state[ 'next' ] & self::BIT_VARIABLE ) && !( $state[ 'next' ] & self::BIT_OPERATOR) )
						{
							throw new Compiler_Exception( 'Invalid Variable ' . $token . ' in Expression: ' . $expr . ' state next: ' . $state[ 'next' ] . ' @' . __LINE__, $this->_currentCompileTag );
						}

						// We do the first character test manually, because
						// in regular expression the parser would receive too much rubbish.
						if ( !ctype_alpha($token[ 1 ]) && $token[ 1 ] != '_' )
						{
							throw new Compiler_Exception( 'Invalid Variable ' . $token . ' in Expression: ' . $expr . ' @' . __LINE__, $this->_currentCompileTag );
						}


						$out                 = $this->compileVariable($token);
						$current[ 'result' ] = $out;
						$current[ 'token' ]  = self::BIT_VARIABLE;


						if ( is_null($state[ 'variable' ]) )
						{
							$state[ 'variable' ] = false;
						}

						// Hmmm... and what is the purpose of this IF? Seriously, I forgot.
						// So better do not touch it; it must have been very important.
						if ( $state[ 'clone' ] == 1 )
						{
							$state[ 'next' ] = self::BIT_SQ_BRACKET | self::BIT_CALL | self::BIT_NULL;
						}
						else
						{
							$state[ 'next' ] = $operatorSet | self::BIT_SQ_BRACKET | self::BIT_CALL;
						}
					}
					elseif ( $tokenfirstchar === '$' && ctype_alnum($tokenlastchar) /*preg_match('/^' . $this->_rVariable . '$/isU', $token)*/ )
					{
						// Variable call.
						if ( !( $state[ 'next' ] & self::BIT_VARIABLE ) )
						{
							//throw new Compiler_Exception('Invalid Variable "' . $token . '" in Expression: ' . $expr. ' state next: '. $state['next'] . ' @' . __LINE__);
						}

						// We do the first character test manually, because
						// in regular expression the parser would receive too much rubbish.
						if ( !ctype_alpha($token[ 1 ]) && $token[ 1 ] != '_' )
						{
							throw new Compiler_Exception( 'Invalid Variable "' . $token . '" in Expression: ' . $expr . ' @' . __LINE__, $this->_currentCompileTag );
						}

						// Moreover, we need to know the future (assignments)
						$assignment = null;
						if ( isset( $tokens[ $i + 1 ] ) && ( $tokens[ $i + 1 ] === '=' || $tokens[ $i + 1 ] === 'is' ) )
						{
							$assignment = $tokens[ $i + 2 ];
						}


						$out = $this->compileVariable($token, null, $assignment, (isset($current[ 'prev' ]) ? ( $current[ 'prev' ] & self::BIT_FUNCTION ) : false) );


						if ( is_array($out) )
						{
							foreach ( $out as $t )
							{
								$result[ ] = $t;
							}
							$current[ 'result' ] = '';
							$current[ 'token' ]  = self::BIT_VARIABLE;
						}
						else
						{

							$current[ 'result' ] = $out;
							$current[ 'token' ]  = self::BIT_VARIABLE;
						}

						if ( is_null($state[ 'variable' ]) )
						{
							$state[ 'variable' ] = true;
						}

						// Hmmm... and what is the purpose of this IF? Seriously, I forgot.
						// So better do not touch it; it must have been very important.
						if ( $state[ 'clone' ] == 1 )
						{
							$state[ 'next' ] = self::BIT_SQ_BRACKET | self::BIT_CALL | self::BIT_NULL;
						}
						else
						{
							$state[ 'next' ] = $operatorSet | self::BIT_SQ_BRACKET | self::BIT_CALL;
						}
					}
					elseif ( preg_match('/^' . $this->_rDecimalNumber . '$/isU', $token) )
					{
						// Handling the decimal numbers.
						if ( !( $state[ 'next' ] & self::BIT_NUMBER ) && !( $state[ 'next' ] & self::BIT_OPERATOR ) )
						{
							throw new Compiler_Exception( 'Invalid BIT_NUMBER ' . $token . ' in Expression: ' . $expr . ' @' . __LINE__, $this->_currentCompileTag );
						}

						$current[ 'result' ] = $token;
						$state[ 'next' ]     = $operatorSet | self::BIT_SQ_BRACKET_E;
					}
					elseif ( preg_match('/^' . $this->_rHexadecimalNumber . '$/isU', $token) )
					{
						// Hexadecimal, too.
						if ( !( $state[ 'next' ] & self::BIT_NUMBER ) )
						{
							throw new Compiler_Exception( 'Invalid BIT_NUMBER ' . $token . ' in Expression: ' . $expr . ' @' . __LINE__, $this->_currentCompileTag );
						}

						$current[ 'result' ] = $token;
						$state[ 'next' ]     = $operatorSet | self::BIT_SQ_BRACKET_E;
					}
					elseif ( preg_match('/^' . $this->_rIdentifier . '$/isU', $token) )
					{
						$this->_compileIdentifier($token, $previous[ 'token' ], $previous[ 'result' ], isset( $tokens[ $i + 1 ] ) ? $tokens[ $i + 1 ] : null, $operatorSet, $expr, $current, $state);
					}


					break;
			}


			$previous = $current;

			if ( $current[ 'result' ] != '' )
			{
				$result[ ] = trim( $current[ 'result' ] );
			}
		}
		// Finally, test if the pre- operators have been used properly.
		$this->_testPreOperators($previous[ 'token' ], $state[ 'preop' ], $token, $expr);

		// And if we are allowed to finish here...
		if ( !( $state[ 'next' ] & self::BIT_NULL ) )
		{
			throw new Compiler_Exception( 'Invalid BIT_NULL ' . $token . ' in Expression: ' . $expr . ' @' . __LINE__, $this->_currentCompileTag );
		}

		// TODO: For variable detection: check also class/object fields!
		return array (
			$result,
			$assign,
			$state[ 'variable' ]
		);
	}

	/**
	 * Compiles the specified identifier encountered in the expression
	 * to the PHP code.
	 *
	 * @internal
	 * @param String $token       The encountered token.
	 * @param Int    $previous    Previous token
	 * @param String $pt          Used for OOP parsing to determine whether we have a static call.
	 * @param String $next        The next token in the list
	 * @param Int    $operatorSet The flag of allowed opcodes at this position.
	 * @param String &$expr       The current expression (for debug purposes)
	 * @param Array  &$current    Reference to the current token information
	 * @param Array  &$state      Reference to the parser state flags.
	 * @throws Compiler_Exception
	 */
	protected function _compileIdentifier ( $token, $previous, $pt, $next, $operatorSet, &$expr, &$current, &$state )
	{

		if ( $previous == self::BIT_OBJMAN )
		{
			throw new Compiler_Exception( sprintf('Class `%s` is not allowed!', $token) );
		}
		elseif ( $next == '(' )
		{
			// Function/method call
			if ( $previous == self::BIT_CALL )
			{
				$current[ 'result' ] = $token;
				$current[ 'token' ]  = self::BIT_METHOD;
				$state[ 'next' ]     = self::BIT_BRACKET;
				$state[ 'func' ]     = 1;
			}
			elseif ( isset( $this->getCompiler()->_functions[ $token ] ) )
			{
				$name = $this->getCompiler()->_functions[ $token ];

				if ( $name[ 0 ] == '#' )
				{
					$pos = strpos($name, '#', 1);
					if ( $pos === false )
					{
						throw new Compiler_Exception( sprintf('Invalid Argument Format in the Expression. Function: %s, Name: %s', $token, $name), $this->_currentCompileTag );
					}

					$state[ 'rev' ] = substr($name, 1, $pos - 1);
					$name           = substr($name, $pos + 1, strlen($name));
				}

				$current[ 'result' ] = $name;
				$current[ 'token' ]  = self::BIT_FUNCTION;
				$state[ 'next' ]     = self::BIT_BRACKET;
				$state[ 'function' ] = $token;
			}
			else
			{
				throw new Compiler_Exception( sprintf('Function `%s` is not allowed!', $token) . ' @' . __LINE__, $this->_currentCompileTag );
			}
		}
		elseif ( $previous == self::BIT_CALL )
		{
			// Class/object field call, check whether static or not.
			$current[ 'result' ] = ( $pt == '::' ? '$' . $token : $token );
			$current[ 'token' ]  = self::BIT_FIELD;
			$state[ 'next' ]     = $operatorSet | self::BIT_SQ_BRACKET | self::BIT_CALL;
			if ( $state[ 'clone' ] == 1 )
			{
				$state[ 'next' ] = self::BIT_SQ_BRACKET | self::BIT_CALL | self::BIT_NULL;
			}
		}
		elseif ( $next == '::' )
		{
			// Static class call
			if ( isset( $this->_classes[ $token ] ) )
			{
				$current[ 'result' ] = $this->_classes[ $token ];
				$current[ 'token' ]  = self::BIT_CLASS;
				$state[ 'next' ]     = self::BIT_CALL;
			}
			else
			{
				throw new Compiler_Exception( sprintf('Class `%s` is not allowed! Expression: %s', $token, $expr) . ' @' . __LINE__, $this->_currentCompileTag );
			}
		}
		else
		{
			// An ending string.
			if ( !( $state[ 'next' ] & self::BIT_STRING ) )
			{
				#   throw new Compiler_Exception(sprintf('BIT_STRING Error `%s`! Expression: %s', $token, $expr) . ' @' . __LINE__);

				$state[ 'next' ]     = self::BIT_NULL;
				$current[ 'token' ]  = self::BIT_STRING;
				$current[ 'result' ] = ' .\'' . $token . '\'';
			}
			else
			{
				$state[ 'next' ]     = self::BIT_NULL;
				$current[ 'token' ]  = self::BIT_STRING;
				$current[ 'result' ] = '\'' . $token . '\'';
			}
		}
	}

	/**
	 *
	 * @param array  $array
	 * @param string $phpSourceTemplate
	 * @return string
	 */
	protected function _prepareVariable ( &$array, $phpSourceTemplate )
	{

		$parsed = '';
		foreach ( $array as $str )
		{
			$parsed .= sprintf($phpSourceTemplate, $str);
		}

		return $parsed;
	}

    /**
     *
     * @param string $variableString
     * @param null $lastScope
     * @param null $assign
     * @param bool $prevIsFunc
     * @throws BaseException
     * @throws Compiler_Exception
     * @internal param null $scopeName
     * @internal   param $string /boolean $scopeName default is false.
     *             If set "false" will not use registred scopes.
     *             If set "null" will autodetect scope names.
     *
     * @return string
     */
	public function compileVariable ( $variableString, $lastScope = null, $assign = null, $prevIsFunc = false )
	{

		$chrOne = substr($variableString, 0, 1);

		if ( $chrOne !== '$' && $chrOne !== '@' )
		{
			throw new Compiler_Exception( 'Invalid Variable/Constante `' . $variableString . '`.' );
		}

		$variableString = substr($variableString, 1);

		// compile constante
		if ( $chrOne === '@' )
		{
			return 'constant(\'' . $variableString . '\')';
		}

		// compile variables
		$keys = explode('.', $variableString);
		$key  = array_shift($keys);

		$_parsed = '';
		//$_parsedIsset = false;
		$phpSourceTemplate = '';

		switch ( strtolower($key) )
		{
			case 'post':
			case 'get':
			case 'input':
				$_parsed           = 'HTTP::' . $key;
				$phpSourceTemplate = '(\'%s\')';
				break;

			case 'session':
				$_parsed           = 'Session::get';
				$phpSourceTemplate = '(\'%s\')';

				break;

			case 'cookie':
				$_parsed           = 'Cookie::get';
				$phpSourceTemplate = '(\'%s\')';
				break;


			case 'server':
				//$_parsedIsset = 'isset($_SERVER';
				$_parsed           = '$_SERVER';
				$phpSourceTemplate = '[\'%s\']';
				break;
            case 'globals':
                $_parsed           = '$GLOBALS';
                $phpSourceTemplate = '[\'%s\']';
                break;



            case 'env':
                //$_parsedIsset = 'isset($_SERVER';
                $_parsed           = '$this->Env->';
                $phpSourceTemplate = '%s()';
                break;

			case 'user':
				$_parsed           = 'User::get';
				$phpSourceTemplate = '(\'%s\')';

				break;



			default:

				//$tmp = $scopeName;
                if ( !$this->scope->isScope($key) )
                {
                    #   print_r($this->scope);
                    array_unshift($keys, $key);
                    $_parsed = '$this->dat';
                    //	                $_parsedIsset = 'isset($this->_data';
                }
                else
                {
                    //$tmp = $key;

                    $sn = $this->scope->getScope($key);

                    if (empty($sn)) {
                        throw new BaseException('Invalid Scope: '. $key);
                    }

                    $_parsed = '$' . $sn;
                    //	                $_parsedIsset = 'isset($' . $this->scope[ $key ];
                }

				$phpSourceTemplate = '[\'%s\']';
				break;
		}

		$_parsed .= $this->_prepareVariable($keys, $phpSourceTemplate);

		return $_parsed;
	}

	/**
	 * Compiles the string call in the expression to a suitable PHP source code.
	 *
	 * @internal
	 * @param String $str The "string" string (with the delimiting characters)
	 * @throws Compiler_Exception
	 * @return String The output PHP code.
	 */
	public function compileString ( $str )
	{
        if (!is_string($str))
        {
            throw new Compiler_Exception('compileString() must give a string. You give a '. gettype($str));
        }

        if (!$str) {
            return '\'\'';
        }



		$str = preg_replace('# lt #s', '<', $str);
		$str = preg_replace('# gt #s', '>', $str);

		// TODO: Fix
		// COMMENT: Fix what?
		switch ( $str[ 0 ] )
		{
			case '"':
			case '\'':
				return $str;
			case '`':
				throw new Compiler_Exception( 'backticks not supported!' );
			default:
                if ( strpos($str, '"') === false ) {
                    return '"' . $str . '"';
                }
                else {
                    return '\'' . $str . '\'';
                }
            break;
		}
	}

	/**
	 * An utility function that allows to test the preincrementation
	 * operators, if they are used in the right place. In case of
	 * problems, it generates an exception.
	 *
	 * @internal
	 * @param Int     $previous The previous token type.
	 * @param Boolean $state    The state of the "preop" expression parser flag.
	 * @param String  $token    The current token provided for debug purposes.
	 * @param String  &$expr    The reference to the parsed expression for debug purposes.
	 * @throws Compiler_Exception
	 */
	protected function _testPreOperators ( $previous, $state, &$token, &$expr )
	{

		if ( ( $previous == self::BIT_METHOD || $previous == self::BIT_FUNCTION || $previous == self::BIT_EXPRESSION ) && $state )
		{
			// Invalid use of prefix operators!
			throw new Compiler_Exception( 'Invalid use of prefix operators! "' . $token . '" Expression:' . $expr . ' @' . __LINE__ );
		}
	}

	/**
	 * Processes the argument order change functionality for function
	 * parsing in expressions.
	 *
	 * @internal
	 * @param Array  &$args    Reference to a list of function arguments.
	 * @param String $format   The new order format code.
	 * @param String $function The function name provided for debugging purposes.
	 * @throws Compiler_Exception
	 */
	protected function _reverseArgs ( &$args, $format, $function )
	{

		$codes   = explode(',', $format);
		$newArgs = array ();
		$i       = 0;
		foreach ( $codes as $code )
		{
			$data = explode(':', $code);
			if ( !isset( $args[ $i ] ) )
			{
				if ( !isset( $data[ 1 ] ) )
				{
					throw new Compiler_Exception( 'Invalid arguments for function ' . $function . '! The argument "' . $i . '" is not allowed' . ' @' . __LINE__, $this->_currentCompileTag );
				}

				$newArgs[ (int)$data[ 0 ] - 1 ] = $data[ 1 ];
			}
			else
			{
				$newArgs[ (int)$data[ 0 ] - 1 ] = $args[ $i ];
			}
			$i++;
		}
		$args = $newArgs;
	}

}