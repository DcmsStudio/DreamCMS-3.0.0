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
 * @file         Input.php
 */
class Input
{

	/**
	 *
	 */
	const STRING = 1;

	/**
	 *
	 */
	const INTEGER = 2;

	/**
	 * @const Array type
	 */
	const ARR = 3; // as Array
	/**
	 *
	 */
	const BOOL = 4;

	/**
	 * Current object instance (Singleton)
	 *
	 * @var Input
	 */

	protected static $objInstance = null;

	/**
	 * lowercase
	 *
	 * @var string
	 */
	private static $_method;

	/**
	 *
	 * @var array
	 */
	private static $_inputGET = array ();

	/**
	 *
	 * @var array
	 */
	private static $_inputPOST = array ();

	/**
	 *
	 * @var array
	 */
	private static $_RouterInput = array ();

	/**
	 *
	 * @var array
	 */
	private static $_request = array ();

	protected static $isAdmin = false;

	/**
	 * @var array
	 */
	private $arrKeywords = array (
		'/\bj\s*a\s*v\s*a\s*s\s*c\s*r\s*i\s*p\s*t\b/is', // javascript
		'/\bv\s*b\s*s\s*c\s*r\s*i\s*p\s*t\b/is', // vbscript
		'/\bv\s*b\s*s\s*c\s*r\s*p\s*t\b/is', // vbscrpt
		'/\bs\s*c\s*r\s*i\s*p\s*t\b/is', //script
		'/\ba\s*p\s*p\s*l\s*e\s*t\b/is', // applet
		'/\ba\s*l\s*e\s*r\s*t\b/is', // alert
		'/\bd\s*o\s*c\s*u\s*m\s*e\s*n\s*t\b/is', // document
		'/\bw\s*r\s*i\s*t\s*e\b/is', // write
		'/\bc\s*o\s*o\s*k\s*i\s*e\b/is', // cookie
		'/\bw\s*i\s*n\s*d\s*o\s*w\b/is' // window
	);

	/**
	 * Prevent cloning of the object (Singleton)
	 */
	final private function __clone ()
	{

	}

	/**
	 * Return the current object instance (Singleton)
	 *
	 * @return Input
	 */
	public static function getInstance ()
	{

		if ( self::$objInstance === null )
		{

			self::$objInstance = new Input();
			self::$objInstance->initRequest();
		}

		return self::$objInstance;
	}

	/**
	 * Strip slashes
	 *
	 * @param  mixed
	 * @return mixed
	 */
	protected static function stripSlashes ( $varValue )
	{

		// Recursively clean arrays
		if ( is_array($varValue) )
		{
			foreach ( $varValue as $k => $v )
			{
				$varValue[ $k ] = self::stripSlashes($v);
			}

			return $varValue;
		}

		return get_magic_quotes_gpc() ? stripslashes($varValue) : $varValue;
	}

	/**
	 *
	 * @return string
	 */
	public function getMethod ()
	{

		if ( !self::$_method )
		{
			self::$_method = strtolower((string)$_SERVER[ 'REQUEST_METHOD' ]);
		}

		return self::$_method;
	}

	/**
	 *
	 * @param string $key
	 */
	public function remove ( $key )
	{

		if ( $this->getMethod() === 'get' )
		{
			if ( isset( $_GET[ $key ] ) )
			{
				unset( $_GET[ $key ] );
				unset( self::$_inputGET[ $key ] );
				unset( self::$_request[ $key ] );
			}
		}
		elseif ( $this->getMethod() === 'post' )
		{
			if ( isset( $_POST[ $key ] ) )
			{
				unset( $_POST[ $key ] );
				unset( self::$_inputPOST[ $key ] );
				unset( self::$_request[ $key ] );
			}
		}
	}

	/**
	 *
	 */
	private function initRequest ()
	{

		if ( defined('ADM_SCRIPT') && ADM_SCRIPT )
		{
			self::$isAdmin = true;
		}



		$input = array ();

		if ( $this->getMethod() === 'get' )
		{
			$input = $_GET;

			if ( get_magic_quotes_gpc() )
			{
				$input = self::stripSlashes($input);
			}
			foreach ( $input as $key => &$value )
			{
				$value = $this->clean($value);
			}
			self::$_inputGET = $input;
		}
		elseif ( $this->getMethod() === 'post' )
		{
			$input = $_POST;

			if ( get_magic_quotes_gpc() )
			{
				$input = self::stripSlashes($input);
			}


			foreach ( $input as $key => &$value )
			{
				$value = $this->clean($value);
			}

			self::$_inputPOST = $input;
		}
		elseif ( $this->getMethod() === 'put' )
		{
			$input = $_PUT;

			if ( get_magic_quotes_gpc() )
			{
				$input = self::stripSlashes($input);
			}
			foreach ( $input as $key => &$value )
			{
				$value = $this->clean($value);
			}
			self::$_inputPOST = $input;
		}

		unset( $input );

		self::$_request = array_merge(self::$_inputGET, self::$_inputPOST);
	}

	/**
	 *
	 * @param array $params
	 */
	public function setFromRouter ( array $params )
	{

		$input = $params;

		if ( get_magic_quotes_gpc() )
		{
			$input = self::stripSlashes($input);
		}

		self::$_RouterInput = $input;

		if ( $this->getMethod() === 'get' )
		{
			foreach ( $input as $k => $v )
			{
				if ( isset( self::$_inputGET[ $k ] ) && $v === '' && self::$_inputGET[ $k ] != '' )
				{
					unset( $input[ $k ] );
				}
			}

			self::$_inputGET = array_merge(self::$_inputGET, $input);
		}
		elseif ( $this->getMethod() === 'post' )
		{
			self::$_inputPOST = array_merge(self::$_inputPOST, $input);
		}

		self::$_request = array_merge(self::$_inputPOST, self::$_inputGET);

		unset( $input );
	}

	/**
	 *
	 * @param string $key
	 * @param mixed  $value default null
	 */
	public function set ( $key, $value = null )
	{

		self::$_request[ $key ] = $value;
        $_REQUEST[ $key ] = $value;

		if ( $this->getMethod() === 'get' )
		{
			self::$_inputGET[ $key ] = $value;
            $_GET[$key] = $value;
		}
		elseif ( $this->getMethod() === 'post' )
		{
			self::$_inputPOST[ $key ] = $value;
            $_POST[$key] = $value;
		}
	}

	/**
	 * returns Request
	 * if $key not exists returns null
	 *
	 * @param string $key default is null and will return the array
	 * @param null   $type
	 * @return array|int|mixed|null|string
	 */
	public function input ( $key = null, $type = null )
	{

		if ( $key === null )
		{
			return self::$_request;
		}

		return ( isset( self::$_request[ $key ] ) ? $this->getType(self::$_request[ $key ], $type) : null );
	}

	/**
	 * returns only Request from _GET
	 * if $key not exists returns null
	 *
	 * @param string $key default is null and will return the array
	 * @param null   $type
	 * @return array|int|mixed|null|string
	 */
	public function get ( $key = null, $type = null )
	{

		if ( $key === null )
		{
			return self::$_inputGET;
		}

		return ( isset( self::$_inputGET[ $key ] ) ? $this->getType(self::$_inputGET[ $key ], $type) : null );
	}

	/**
	 * returns only Request from _POST
	 * if $key not exists returns null
	 *
	 * @param string $key default is null and will return the array
	 * @param null   $type
	 * @return array|int|mixed|null|string
	 */
	public function post ( $key = null, $type = null )
	{

		if ( $key === null )
		{
			return self::$_inputPOST;
		}

		return ( isset( self::$_inputPOST[ $key ] ) ? $this->getType(self::$_inputPOST[ $key ], $type) : null );
	}

	/**
	 *
	 * @param mixed  $value
	 * @param string $type
	 * @return array|int|mixed|string
	 */
	private function getType ( $value, $type = null )
	{

		if ( $type === 'str' || $type === self::STRING )
		{
			return (string)$value;
		}
		elseif ( $type === 'int' || $type === self::INTEGER )
		{
			return intval($value);
		}
		elseif ( $type === 'array' || $type === self::ARR )
		{
			return (array)$value;
		}
		elseif ( $type === 'bool' || $type === self::BOOL )
		{
			return (bool)$value;
		}

		return $value;
	}

    /**
     *
     * @param mixed $data
     * @param boolean $strictMode
     *
     * @param bool $cleanHtml
     * @return mixed
     */
	public function clean ( $data, $strictMode = false, $cleanHtml = false )
	{

		$cleanValue = null;

		$cleanValue = $this->stripSlashes($data);

		if (!self::$isAdmin) {
			$cleanValue = $this->decodeEntities($cleanValue);
		}

		if ( (!self::$isAdmin && $strictMode) || (self::$isAdmin && $strictMode) ) {
			$cleanValue = $this->xssClean($cleanValue, $strictMode);
		}

		if (!self::$isAdmin && $cleanHtml) {
			$cleanValue = $this->stripTags($cleanValue);
		}

		return $cleanValue;
	}

	/**
	 * Strip tags preserving HTML comments
	 *
	 * @param  mixed
	 * @param  string
	 * @return mixed
	 */
	protected function stripTags ( $varValue, $strAllowedTags = '' )
	{

		// Recursively clean arrays
		if ( is_array($varValue) )
		{
			foreach ( $varValue as $k => $v )
			{
				$varValue[ $k ] = $this->stripTags($v, $strAllowedTags);
			}

			return $varValue;
		}

		$varValue = str_replace(array (
		                              '<!--',
		                              '<![',
		                              '-->'
		                        ), array (
		                                 '&lt;!--',
		                                 '&lt;![',
		                                 '--&gt;'
		                           ), $varValue);

		$varValue = strip_tags($varValue, $strAllowedTags);

		$varValue = str_replace(array (
		                              '&lt;!--',
		                              '&lt;![',
		                              '--&gt;'
		                        ), array (
		                                 '<!--',
		                                 '<![',
		                                 '-->'
		                           ), $varValue);

		return $varValue;
	}

	/**
	 * Decode HTML entities
	 *
	 * @param  mixed
	 * @return mixed
	 */
	protected function decodeEntities ( $varValue )
	{

		// Recursively clean arrays
		if ( is_array($varValue) )
		{
			foreach ( $varValue as $k => $v )
			{
				$varValue[ $k ] = $this->decodeEntities($v);
			}

			return $varValue;
		}

		// Preserve basic entities
		$varValue = rawurldecode($varValue); /*str_replace(array (
		                              '[&amp;]',
		                              '&amp;',
		                              '[&lt;]',
		                              '&lt;',
		                              '[&gt;]',
		                              '&gt;',
		                              '[&nbsp;]',
		                              '&nbsp;',
		                              '[&shy;]',
		                              '&shy;'
		                        ), array (
		                                 '[&]',
		                                 '[&]',
		                                 '[lt]',
		                                 '[lt]',
		                                 '[gt]',
		                                 '[gt]',
		                                 '[nbsp]',
		                                 '[nbsp]',
		                                 '[-]',
		                                 '[-]'
		                           ), rawurldecode($varValue));
*/
		return html_entity_decode($varValue, ENT_COMPAT, 'utf-8');
	}

	/**
	 * Clean user input and try to prevent XSS attacks
	 *
	 * @param  mixed
	 * @param  boolean
	 * @return mixed
	 */
	public function xssClean ( $varValue, $blnStrictMode = false )
	{

		// Recursively clean arrays
		if ( is_array($varValue) )
		{
			foreach ( $varValue as $k => $v )
			{
				$varValue[ $k ] = $this->xssClean($v);
			}

			return $varValue;
		}

		// Return if var is not a string
		if ( is_bool($varValue) || is_null($varValue) || is_numeric($varValue) )
		{
			return $varValue;
		}


		// Validate standard character entites and UTF16 two byte encoding
		$varValue = preg_replace('/(&#*\w+)[\x00-\x20]+;/i', '$1;', $varValue);
		$varValue = preg_replace('/(&#x*)([0-9a-f]+);/i', '$1$2;', $varValue);

		/*
		  // straight replacements, the user should never need these since they're normal characters
		  // this prevents like <IMG SRC=&#X40&#X61&#X76&#X61&#X73&#X63&#X72&#X69&#X70&#X74&#X3A&#X61&#X6C&#X65&#X72&#X74&#X28&#X27&#X58&#X53&#X53&#X27&#X29>
		*/
		$search = 'abcdefghijklmnopqrstuvwxyz';
		$search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$search .= '1234567890!@#$%^&*()';
		$search .= '~`";:?+/={}[]-_|' . "'";
		$len = strlen($search);
		for ( $i = 0; $i < $len; $i++ )
		{
			// ;? matches the ;, which is optional
			// 0{0,7} matches any padded zeros, which are optional and go up to 8 chars
			// &#x0040 @ search for the hex values
			$varValue = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[ $i ])) . ';?)/i', $search[ $i ], $varValue); // with a ;
			// &#00064 @ 0{0,7} matches '0' zero to seven times
			$varValue = preg_replace('/(&#0{0,8}' . ord($search[ $i ]) . ';?)/', $search[ $i ], $varValue); // with a ;
		}


		// remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
		// this prevents some character re-spacing such as <java?script>
		// note that you have to handle splits with n, r, and t later since they *are* allowed in some inputs
		// $varValue = preg_replace('/([x00-x08,x0b-x0c,x0e-x19])/', '', $varValue);
		// Remove carriage returns
		$varValue = preg_replace('/\r+/', '', $varValue);

		// Replace unicode entities
		$varValue = utf8_decode_entities($varValue);

		// Remove NULL characters
		$varValue = preg_replace('/\0+/', '', $varValue);
		$varValue = preg_replace('/(\\\\0)+/', '', $varValue);


		// Compact exploded keywords like "j a v a s c r i p t"
		foreach ( $this->arrKeywords as $strKeyword )
		{
			$arrMatches = array ();
			preg_match_all($strKeyword, $varValue, $arrMatches);

			foreach ( $arrMatches[ 0 ] as $strMatch )
			{
				$varValue = str_replace($strMatch, preg_replace('/\s*/', '', $strMatch), $varValue);
			}
		}

		$arrRegexp[ ] = '/<(a|img)[^>]*[^a-z](<script|<xss)[^>]*>/is';
		$arrRegexp[ ] = '/<(a|img)[^>]*[^a-z]document\.cookie[^>]*>/is';
		$arrRegexp[ ] = '/<(a|img)[^>]*[^a-z]vbscri?pt\s*:[^>]*>/is';

		// Also remove event handlers and JavaScript in strict mode
		if ( $blnStrictMode )
		{
			$arrRegexp[ ] = '/vbscri?pt\s*:/is';
			$arrRegexp[ ] = '/javascript\s*:/is';
			$arrRegexp[ ] = '/<\s*embed.*swf/is';
			$arrRegexp[ ] = '/<(a|img)[^>]*[^a-z]alert\s*\([^>]*>/is';
			$arrRegexp[ ] = '/<(a|img)[^>]*[^a-z]javascript\s*:[^>]*>/is';
			$arrRegexp[ ] = '/<(a|img)[^>]*[^a-z]window\.[^>]*>/is';
			$arrRegexp[ ] = '/<(a|img)[^>]*[^a-z]document\.[^>]*>/is';
			$arrRegexp[ ] = '/<[^>]*[^a-z]onabort\s*=[^>]*>/is';
			$arrRegexp[ ] = '/<[^>]*[^a-z]onblur\s*=[^>]*>/is';
			$arrRegexp[ ] = '/<[^>]*[^a-z]onchange\s*=[^>]*>/is';
			$arrRegexp[ ] = '/<[^>]*[^a-z]onclick\s*=[^>]*>/is';
			$arrRegexp[ ] = '/<[^>]*[^a-z]onerror\s*=[^>]*>/is';
			$arrRegexp[ ] = '/<[^>]*[^a-z]onfocus\s*=[^>]*>/is';
			$arrRegexp[ ] = '/<[^>]*[^a-z]onkeypress\s*=[^>]*>/is';
			$arrRegexp[ ] = '/<[^>]*[^a-z]onkeydown\s*=[^>]*>/is';
			$arrRegexp[ ] = '/<[^>]*[^a-z]onkeyup\s*=[^>]*>/is';
			$arrRegexp[ ] = '/<[^>]*[^a-z]onload\s*=[^>]*>/is';
			$arrRegexp[ ] = '/<[^>]*[^a-z]onmouseover\s*=[^>]*>/is';
			$arrRegexp[ ] = '/<[^>]*[^a-z]onmouseup\s*=[^>]*>/is';
			$arrRegexp[ ] = '/<[^>]*[^a-z]onmousedown\s*=[^>]*>/is';
			$arrRegexp[ ] = '/<[^>]*[^a-z]onmouseout\s*=[^>]*>/is';
			$arrRegexp[ ] = '/<[^>]*[^a-z]onreset\s*=[^>]*>/is';
			$arrRegexp[ ] = '/<[^>]*[^a-z]onselect\s*=[^>]*>/is';
			$arrRegexp[ ] = '/<[^>]*[^a-z]onsubmit\s*=[^>]*>/is';
			$arrRegexp[ ] = '/<[^>]*[^a-z]onunload\s*=[^>]*>/is';
			$arrRegexp[ ] = '/<[^>]*[^a-z]onresize\s*=[^>]*>/is';
		}

		return preg_replace($arrRegexp, '', $varValue);
	}

}

?>