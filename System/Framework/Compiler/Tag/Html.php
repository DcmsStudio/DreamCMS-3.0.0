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
 * @file         Html.php
 */


class Compiler_Tag_Html extends Compiler_Tag_Abstract
{

	private static $_layout = null;

	/**
	 *
	 * @throws Compiler_Exception
	 * @return void
	 */
	public function process ()
	{
		$this->set('nophp', true);
        $str = $this->tag->getCurrentTag();

        if ($str)
        {
            if ( strpos($str, 'cp:') === false && strpos($str, 'parse:') === false && strpos($str, '$') === false && strpos($str, '$') === false )
            {
                if ( defined('ADM_SCRIPT') && ADM_SCRIPT === true )
                {
                    if ( $this->tag->getTagName() === 'img' || $this->tag->getTagName() === 'script' )
                    {
                        $this->replaceAttributValue('src', 'images/', BACKEND_IMAGE_PATH, false, true);
                        $this->replaceAttributValue('src', 'core/admin/images/', BACKEND_IMAGE_PATH, false, true);
                        $this->replaceAttributValue('src', 'admin/js/', BACKEND_JS_URL, false, true);
                    }
                    elseif ( $this->tag->getTagName() === 'link' )
                    {
                        $this->replaceAttributValue('href', 'images/', BACKEND_IMAGE_PATH, false, true);
                        $this->replaceAttributValue('href', 'core/admin/images/', BACKEND_IMAGE_PATH, false, true);
                        $this->replaceAttributValue('href', 'admin/js/', BACKEND_JS_URL, false, true);
                    }
                }

                $this->appendStartTag($this->tag->getTagSource());

                if ( !$this->tag->isEmptyTag() )
                {
                    $this->appendEndTag('</' . $this->tag->getTagName() . '>');
                }

                return;
            }
        }


		if ( !$this->tag->hasNamespacedAttributes() )
		{
			if ( defined('ADM_SCRIPT') && ADM_SCRIPT === true )
			{
				if ( $this->tag->getTagName() === 'img' || $this->tag->getTagName() === 'script' )
				{
					$this->replaceAttributValue('src', 'images/', BACKEND_IMAGE_PATH, false, true);
					$this->replaceAttributValue('src', 'core/admin/images/', BACKEND_IMAGE_PATH, false, true);
					$this->replaceAttributValue('src', 'admin/js/', BACKEND_JS_URL, false, true);
				}
				elseif ( $this->tag->getTagName() === 'link' )
				{
					$this->replaceAttributValue('href', 'images/', BACKEND_IMAGE_PATH, false, true);
					$this->replaceAttributValue('href', 'core/admin/images/', BACKEND_IMAGE_PATH, false, true);
					$this->replaceAttributValue('href', 'admin/js/', BACKEND_JS_URL, false, true);
				}


				// will get faster the current html tag
				#$str = $this->tag->getCurrentTag();
				#if ($str === false)
				#{
					// not set then build the html tag
					$attr = $this->getCompiledHtmlAttributes();
					if ( !empty( $attr ) )
					{
						$attr = ' ' . $attr;
					}

					$str = '<' . $this->tag->getTagName() . $attr . ( $this->tag->isEmptyTag() ? ' /' : '' ) . '>';
				#}
			}
			else
			{
				// will get faster the current html tag
				#$str = $this->tag->getCurrentTag();
				#if ($str === false)
				#{
					// not set then build the html tag
					$attr = $this->getCompiledHtmlAttributes(array(), true);
					if ( !empty( $attr ) )
					{
						$attr = ' ' . $attr;
					}
					$str = '<' . $this->tag->getTagName() . $attr . ( $this->tag->isEmptyTag() ? ' /' : '' ) . '>';
				#}
			}

			$this->appendStartTag($str);

			if ( !$this->tag->isEmptyTag() )
			{
				$this->appendEndTag('</' . $this->tag->getTagName() . '>');
			}
		}
		else
		{
			/**
			 * @var array with Compiler_Attribute
			 */
			$namespaced = $this->getNamespacedAttributes();

			$_compiledAttributes = array ();
			$_BeforeStartTag     = array ();
			$_AfterStartTag      = array ();
			$_BeforeEndTag       = array ();
			$_AfterEndTag        = array ();
			$_parse              = array ();
			$_onparse            = array ();

			$templateInstance = $this->tag->getTemplateInstance();

			foreach ( $namespaced as $data )
			{
				/**
				 * @var $data Compiler_Attribute
				 */
				if ( $templateInstance->isCompilerNamespace( $data->getNamespace() ) )
				{
					$nsName = strtolower( $data->getName() );
					switch ( $nsName )
					{
						case 'on':
							/**
							 * <tag cp:on="">demo ...</tag>
							 */
							$expressionStr = $data->getValue(true);

							$result = $this->extractOnAttribute( $expressionStr );

							if ( $result === null )
							{
								throw new Compiler_Exception( 'Attribute "on" not set for attribut parser. ' . $expressionStr );
							}

							$onAttribut = $result[ 'onattribut' ];
							$expr = $result[ 'expr' ];
							$result = null;

							$scopes = $templateInstance->getScopes();

							$lastScope = null;
							if ( is_array( $scopes ) && !empty( $scopes ) )
							{
								$lastScope = array_pop( $scopes );
							}
							$result = null;
							$result = $templateInstance->compileExpression( $expr, $lastScope );

							$_onparse[ $onAttribut ] = array(
								'onBefore' => "\n" . 'if ( ' . $result[ 0 ] . ' ){' . "\n",
								'onAfter'  => "\n" . '} ' . "\n",
							);

							$result = null;

							break;
						case 'if':
							/**
							 * <tag cp:if="$test">demo ...</tag>
							 *
							 * show "<tag>demo ...</tag>" if $test is not empty
							 * show "demo ..." if $test is empty
							 */

							$expressionStr = $data->getValue( true );
							$scopes = $templateInstance->getScopes();

							$lastScope = null;
							if ( is_array( $scopes ) && !empty( $scopes ) )
							{
								$lastScope = array_pop( $scopes );
							}


							$result = $templateInstance->compileExpression( $expressionStr, $lastScope );
							if ( empty( $result ) )
							{
								throw new Compiler_Exception( 'Attribute "if" not set for attribut parser. ' . $expressionStr );
							}

							$_BeforeStartTag[] = "\n" . 'if (' . $result[ 0 ] . '){' . "\n";
							$_AfterStartTag[] = "\n" . '} ' . "\n";

							$_BeforeEndTag[] = "\n" . 'if (' . $result[ 0 ] . '){' . "\n";
							$_AfterEndTag[] = "\n" . '} ' . "\n";

							$result = null;

							break;

						case 'is':
							/**
							 * <tag cp:is="$test">demo ...</tag>
							 *
							 * show "<tag>demo ...</tag>" if $test is valid
							 * show "" if $test is not valid
							 */
							$expressionStr = $data->getValue( true );
							$scopes = $templateInstance->getScopes();

							$lastScope = null;
							if ( is_array( $scopes ) && !empty( $scopes ) )
							{
								$lastScope = array_pop( $scopes );
							}

							$result = $templateInstance->compileExpression( $expressionStr, $lastScope );

							$_BeforeStartTag[] = "\n" . 'if (' . $result[ 0 ] . '){' . "\n";

							if ( $this->tag->isEmptyTag() )
							{
								$_AfterStartTag[] = "\n" . '} ' . "\n";
							}
							else
							{
								$_AfterEndTag[] = "\n" . '} ' . "\n";
							}

							$result = null;

							break;

						case 'notempty':
							/**
							 * <tag cp:notempty="$test">demo ...</tag>
							 *
							 * show "<tag>demo ...</tag>" if $test is not empty
							 * show "" if $test is empty
							 */
							$expressionStr = $data->getValue( true );
							$scopes = $templateInstance->getScopes();

							$lastScope = null;
							if ( is_array( $scopes ) && !empty( $scopes ) )
							{
								$lastScope = array_pop( $scopes );
							}

							$result = $templateInstance->compileExpression( $expressionStr, $lastScope );

							if (preg_match('#\w+\(#', $result[ 0 ])) {
								$_BeforeStartTag[] = "\n" . 'if (' . $result[ 0 ] . '){' . "\n";
							}
							else {
								$_BeforeStartTag[] = "\n" . 'if (!empty(' . $result[ 0 ] . ')){' . "\n";
							}


							if ( $this->tag->isEmptyTag() )
							{
								$_AfterStartTag[] = "\n" . '} ' . "\n";
							}
							else
							{
								$_AfterEndTag[] = "\n" . '} ' . "\n";
							}
							$result = null;

							break;

						case 'empty':
							/**
							 * <tag cp:empty="$test">demo ...</tag>
							 *
							 * show "<tag>demo ...</tag>" if $test is empty
							 * show "" if $test is empty
							 */
							$expressionStr = $data->getValue( true );
							$scopes = $templateInstance->getScopes();

							$lastScope = null;
							if ( is_array( $scopes ) && !empty( $scopes ) )
							{
								$lastScope = array_pop( $scopes );
							}

							$result = $templateInstance->compileExpression( $expressionStr, $lastScope );

							$_BeforeStartTag[] = "\n" . 'if (empty(' . $result[ 0 ] . ')){' . "\n";

							if ( $this->tag->isEmptyTag() )
							{
								$_AfterStartTag[] = "\n" . '} ' . "\n";
							}
							else
							{
								$_AfterEndTag[] = "\n" . '} ' . "\n";
							}
							$result = null;

							break;

						case 'content':

							$expressionStr = $data->getValue( true );
							$scopes = $templateInstance->getScopes();

							$lastScope = null;
							if ( is_array( $scopes ) && !empty( $scopes ) )
							{
								$lastScope = array_pop( $scopes );
							}

							$result = $templateInstance->compileExpression( $expressionStr, $lastScope );

							$_AfterStartTag[] = "\n" . '$tmp = ' . $result[ 0 ] . '; if ( empty($tmp) ){' . "\n";
							$_BeforeEndTag[] = "\n" . '} else { echo $tmp; $tmp = null; }' . "\n";

							break;


						case 'block':

							/**
							 * <tag cp:block="test">demo ...</tag>
							 *
							 * show "<tag>demo ...</tag>" if the block test is not empty
							 * show "" if block test is empty
							 */
							$blockName = $data->getValue( true );

							$_BeforeStartTag[] = "\n" . 'if (trim($this->useBlock(\'' . $blockName . '\')) ){' . "\n";

							if ( $this->tag->isEmptyTag() )
							{
								$_AfterStartTag[] = "\n" . '} ' . "\n";
							}
							else
							{
								$_AfterEndTag[] = "\n" . '} ' . "\n";
							}
							$result = null;

							break;
					}


					if ( $data->getNamespace() == 'parse' )
					{
						$expressionStr = $data->getValue( true );
						$scopes = $templateInstance->getScopes();

						$lastScope = null;
						if ( is_array( $scopes ) && !empty( $scopes ) )
						{
							$lastScope = array_pop( $scopes );
						}

						$_parsedExpression = $expressionStr;

						if ( preg_match( '/^([^\'])\:[^\:]/', $expressionStr, $found ) )
						{
							$isprotocoll = isset($found[ 0 ]) ? strtolower( $found[ 0 ] ) : '';

							if ( $isprotocoll !== 'http' && $isprotocoll !== 'https' && $isprotocoll !== 'ftp' )
							{
								$result = $templateInstance->compileExpression( $expressionStr, $lastScope, false, TemplateCompiler_Abstract::ESCAPE_OFF );

								if ( substr( $result[ 0 ], 0, 1 ) === "'" )
								{
									$result[ 0 ] = substr( $result[ 0 ], 1 );
								}

								if ( substr( $result[ 0 ], -1 ) === "'" )
								{
									$result[ 0 ] = substr( $result[ 0 ], 0, -1 );
								}

								$_parsedExpression = $result[ 0 ];
							}
						}
						else
						{
							//$isprotocoll = isset($found[ 0 ]) ? strtolower( $found[ 0 ] ) : '';

							//if ( $isprotocoll !== 'http' && $isprotocoll !== 'https' && $isprotocoll !== 'ftp' )
							//{
								$_parsedExpression = $templateInstance->postCompiler( $expressionStr );

								if ( $_parsedExpression === $expressionStr && substr( $expressionStr, 0, 1 ) === '{' && substr( $expressionStr, -1 ) === '}' )
								{
									$_parsedExpression = $templateInstance->postCompileFunctions( $expressionStr );
								}
							//}
						}


						// xml:lang patch
						$name = $data->getName();
						if ( substr( $name, 0, 4 ) === 'xml-' )
						{
							$name = 'xml:' . substr( $name, 4 );
						}


						$_parse[ $data->getName() ] = $name . '="' . $_parsedExpression . '"';
					}
					elseif ( $data->getNamespace() == 'cycle' )
					{
						$parsedAttribut = '<?php
                        echo TemplateCompiler_Functions::cycle( explode(\'|\', "' . $data->getValue() . '") ); ?>';

						$_compiledAttributes[] = $data->getName() . '="' . $parsedAttribut . '"';
					}
				}
				elseif ( $data->getNamespace() == 'cycle' )
				{
					$parsedAttribut = '<?php
                        echo TemplateCompiler_Functions::cycle( explode(\'|\', "' . $data->getValue() . '") ); ?>';

					$_compiledAttributes[] = $data->getName() . '="' . $parsedAttribut . '"';
				}
			}


			$attr = $this->getCompiledHtmlAttributes( array(
			                                               'parse',
			                                               'on',
			                                               'if',
			                                               'cycle',
			                                               'data',
			                                               Compiler::TAGNAMESPACE ) );
			if ( !empty( $attr ) )
			{
				$attr = ' ' . $attr;
			}


			//
			$attr .= $this->buildOn( $_onparse, $_parse );


			if ( count( $_BeforeStartTag ) )
			{
				$this->set( 'nophp', false );
				$this->setStartTag( implode( '', $_BeforeStartTag ) );
			}

			$this->set( 'nophp', true );
			$this->appendStartTag( '<' . $this->tag->getTagName() . $attr . ($this->tag->isEmptyTag() ? ' /' : '') . '>' );

			if ( count( $_AfterStartTag ) )
			{
				$this->set( 'nophp', false );
				$this->appendStartTag( implode( '', $_AfterStartTag ) );
			}

			if ( !$this->tag->isEmptyTag() )
			{

				if ( count( $_BeforeEndTag ) )
				{
					$this->setEndTag( implode( '', $_BeforeEndTag ) );
				}

				$this->set( 'nophp', true );
				$this->appendEndTag( '</' . $this->tag->getTagName() . '>' );


				if ( count( $_AfterEndTag ) )
				{
					$this->set( 'nophp', false );
					$this->appendEndTag( implode( '', $_AfterEndTag ) );
				}
			}
		}
	}



	/**
	 * @param $expression
	 * @return array|null
	 */
	private function extractOnAttribute( $expression )
	{
		$str = explode( ':', $expression );
		if ( count( $str ) <= 1 )
		{
			return null;
		}
		else
		{
			$_onattribut = array_shift( $str );
			return array(
				'onattribut' => strtolower( trim( $_onattribut ) ),
				'expr'       => implode( '', $str ) );
		}
	}

	/**
	 *
	 * @param array $expressions
	 * @param       $parse
	 * @return string
	 * @internal param array $onparse
	 */
	private function buildOn( $expressions, &$parse )
	{
		$result = array();
		if ( count( $expressions ) )
		{
			foreach ( $expressions as $attribut => $code )
			{
				if ( isset( $parse[ $attribut ] ) && isset( $parse[ $attribut ] ) )
				{
					$result[] = $code[ 'onBefore' ] . Compiler_Abstract::PHP_CLOSE . ' ' . $parse[ $attribut ] .Compiler_Abstract::PHP_OPEN . $code[ 'onAfter' ];
					unset( $parse[ $attribut ] );
				}
			}
			if ( count( $result ) )
			{
				$result = array(
					0 => Compiler_Abstract::PHP_OPEN . implode( '', $result ) . Compiler_Abstract::PHP_CLOSE );
			}
		}


		foreach ( $parse as $attribut => $code )
		{
			$result[] = ' ' . $code;
		}

		return implode( ' ', $result );
	}

}