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
 * @package      Asset
 * @version      3.0.0 Beta
 * @category     Helper
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class JSMinException extends Exception
{

}

/**
 * Class Asset_Helper_Base
 */
class Asset_Helper_Base
{

	/**
	 *
	 */
	const ORD_LF = 10;

	/**
	 *
	 */
	const ORD_SPACE = 32;

	/**
	 *
	 */
	const ACTION_KEEP_A = 1;

	/**
	 *
	 */
	const ACTION_DELETE_A = 2;

	/**
	 *
	 */
	const ACTION_DELETE_A_B = 3;

	/**
	 * @var string
	 */
	protected $a = '';

	/**
	 * @var string
	 */
	protected $b = '';

	/**
	 * @var mixed|string
	 */
	protected $input = '';

	/**
	 * @var int
	 */
	protected $inputIndex = 0;

	/**
	 * @var int
	 */
	protected $inputLength = 0;

	/**
	 * @var null
	 */
	protected $lookAhead = null;

	/**
	 * @var string
	 */
	protected $output = '';

	// -- Public Static Methods --------------------------------------------------

	/**
	 * Minify Javascript
	 *
	 * @uses __construct()
	 * @uses min()
	 * @param string $js Javascript to be minified
	 * @return string
	 */
	public static function removeComments ( $js )
	{

		$jsmin = new Asset_Helper_Base($js);

		return $jsmin->min();
	}

	// -- Public Instance Methods ------------------------------------------------

	/**
	 * Constructor
	 *
	 * @param string $input Javascript to be minified
	 */
	public function __construct ( $input )
	{

		$this->input       = str_replace("\r\n", "\n", $input);
		$this->inputLength = strlen($this->input);
	}

	// -- Protected Instance Methods ---------------------------------------------

	/**
	 * Action -- do something! What to do is determined by the $command argument.
	 *
	 * action treats a string as a single character. Wow!
	 * action recognizes a regular expression if it is preceded by ( or , or =.
	 *
	 * @uses next()
	 * @uses get()
	 * @throws JSMinException If parser errors are found:
	 * - Unterminated string literal
	 * - Unterminated regular expression set in regex literal
	 * - Unterminated regular expression literal
	 * @param int $command One of class constants:
	 *                     ACTION_KEEP_A Output A. Copy B to A. Get the next B.
	 *                     ACTION_DELETE_A Copy B to A. Get the next B. (Delete A).
	 *                     ACTION_DELETE_A_B Get the next B. (Delete B).
	 */
	protected function action ( $command )
	{

		switch ( $command )
		{
			case self::ACTION_KEEP_A:
				$this->output .= $this->a;

			case self::ACTION_DELETE_A:
				$this->a = $this->b;

				if ( $this->a === "'" || $this->a === '"' )
				{
					for ( ; ; )
					{
						$this->output .= $this->a;
						$this->a = $this->get();

						if ( $this->a === $this->b )
						{
							break;
						}

						if ( ord($this->a) <= self::ORD_LF )
						{
							throw new JSMinException('Unterminated string literal.');
						}

						if ( $this->a === '\\' )
						{
							$this->output .= $this->a;
							$this->a = $this->get();
						}
					}
				}

			case self::ACTION_DELETE_A_B:
				$this->b = $this->next();

				if ( $this->b === '/' && ($this->a === '(' || $this->a === ',' || $this->a === '=' || $this->a === ':' || $this->a === '[' || $this->a === '!' || $this->a === '&' || $this->a === '|' || $this->a === '?' || $this->a === '{' || $this->a === '}' || $this->a === ';' || $this->a === "\n") )
				{

					$this->output .= $this->a . $this->b;

					for ( ; ; )
					{
						$this->a = $this->get();

						if ( $this->a === '[' )
						{
							/*
							  inside a regex [...] set, which MAY contain a '/' itself. Example: mootools Form.Validator near line 460:
							  return Form.Validator.getValidator('IsEmpty').test(element) || (/^(?:[a-z0-9!#$%&'*+/=?^_`{|}~-]\.?){0,63}[a-z0-9!#$%&'*+/=?^_`{|}~-]@(?:(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)*[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\])$/i).test(element.get('value'));
							 */
							for ( ; ; )
							{
								$this->output .= $this->a;
								$this->a = $this->get();

								if ( $this->a === ']' )
								{
									break;
								}
								elseif ( $this->a === '\\' )
								{
									$this->output .= $this->a;
									$this->a = $this->get();
								}
								elseif ( ord($this->a) <= self::ORD_LF )
								{
									throw new JSMinException('Unterminated regular expression set in regex literal.');
								}
							}
						}
						elseif ( $this->a === '/' )
						{
							break;
						}
						elseif ( $this->a === '\\' )
						{
							$this->output .= $this->a;
							$this->a = $this->get();
						}
						elseif ( ord($this->a) <= self::ORD_LF )
						{
							throw new JSMinException('Unterminated regular expression literal.');
						}

						$this->output .= $this->a;
					}

					$this->b = $this->next();
				}
		}
	}

	/**
	 * Get next char. Convert ctrl char to space.
	 *
	 * @return string|null
	 */
	protected function get ()
	{

		$c               = $this->lookAhead;
		$this->lookAhead = null;

		if ( $c === null )
		{
			if ( $this->inputIndex < $this->inputLength )
			{
				$c = substr($this->input, $this->inputIndex, 1);
				$this->inputIndex += 1;
			}
			else
			{
				$c = null;
			}
		}

		if ( $c === "\r" )
		{
			return "\n";
		}

		if ( $c === null || $c === "\n" || ord($c) >= self::ORD_SPACE )
		{
			return $c;
		}

		return ' ';
	}

	/**
	 * Is $c a letter, digit, underscore, dollar sign, or non-ASCII character.
	 *
	 * @param $c
	 * @return bool
	 */
	protected function isAlphaNum ( $c )
	{

		return ord($c) > 126 || $c === '\\' || preg_match('/^[\w\$]$/', $c) === 1;
	}

	/**
	 * Perform minification, return result
	 *
	 * @uses action()
	 * @uses isAlphaNum()
	 * @uses get()
	 * @uses peek()
	 * @return string
	 */
	protected function min ()
	{

		if ( 0 == strncmp($this->peek(), "\xef", 1) )
		{
			$this->get();
			$this->get();
			$this->get();
		}

		$this->a = "\n";
		$this->action(self::ACTION_DELETE_A_B);

		while ( $this->a !== null )
		{
			switch ( $this->a )
			{
				case ' ':
					if ( $this->isAlphaNum($this->b) )
					{
						$this->action(self::ACTION_KEEP_A);
					}
					else
					{
						$this->action(self::ACTION_DELETE_A);
					}
					break;

				case "\n":
					switch ( $this->b )
					{
						case '{':
						case '[':
						case '(':
						case '+':
						case '-':
						case '!':
						case '~':
							$this->action(self::ACTION_KEEP_A);
							break;

						case ' ':
							$this->action(self::ACTION_DELETE_A_B);
							break;

						default:
							if ( $this->isAlphaNum($this->b) )
							{
								$this->action(self::ACTION_KEEP_A);
							}
							else
							{
								$this->action(self::ACTION_DELETE_A);
							}
					}
					break;

				default:
					switch ( $this->b )
					{
						case ' ':
							if ( $this->isAlphaNum($this->a) )
							{
								$this->action(self::ACTION_KEEP_A);
								break;
							}

							$this->action(self::ACTION_DELETE_A_B);
							break;

						case "\n":
							switch ( $this->a )
							{
								case '}':
								case ']':
								case ')':
								case '+':
								case '-':
								case '"':
								case "'":
									$this->action(self::ACTION_KEEP_A);
									break;

								default:
									if ( $this->isAlphaNum($this->a) )
									{
										$this->action(self::ACTION_KEEP_A);
									}
									else
									{
										$this->action(self::ACTION_DELETE_A_B);
									}
							}
							break;

						default:
							$this->action(self::ACTION_KEEP_A);
							break;
					}
			}
		}

		return $this->output;
	}

	/**
	 * Get the next character, skipping over comments. peek() is used to see
	 * if a '/' is followed by a '/' or '*'.
	 *
	 * @uses get()
	 * @uses peek()
	 * @throws JSMinException On unterminated comment.
	 * @return string
	 */
	protected function next ()
	{

		$c = $this->get();

		if ( $c === '/' )
		{
			switch ( $this->peek() )
			{
				case '/':
					for ( ; ; )
					{
						$c = $this->get();

						if ( ord($c) <= self::ORD_LF )
						{
							return $c;
						}
					}

				case '*':
					$this->get();

					for ( ; ; )
					{
						switch ( $this->get() )
						{
							case '*':
								if ( $this->peek() === '/' )
								{
									$this->get();

									return ' ';
								}
								break;

							case null:
								throw new JSMinException('Unterminated comment.');
						}
					}

				default:
					return $c;
			}
		}

		return $c;
	}

	/**
	 * Get next char. If is ctrl character, translate to a space or newline.
	 *
	 * @uses get()
	 * @return string|null
	 */
	protected function peek ()
	{

		$this->lookAhead = $this->get();

		return $this->lookAhead;
	}

}
