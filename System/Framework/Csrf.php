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
 * @file         Csrf.php
 */

class Csrf
{

	protected static $enableThrowException = false;

	/**
	 * @var bool
	 */
	protected static $doOriginCheck = true;

	/**
	 * Adds extra useragent and remote_addr checks to CSRF protections.
	 */
	public static function enableOriginCheck ()
	{

		self::$doOriginCheck = true;
	}


	/**
	 *
	 */
	public static function disableOriginCheck ()
	{

		self::$doOriginCheck = false;
	}

    /**
     * Check CSRF tokens match between session and $origin.
     * Make sure you generated a token in the form before checking it.
     *
     * @param string $key The session and $origin key where to find the token.
     * @param $postdata
     * @param bool $throwException TRUE to throw exception on check fail, FALSE or default to return false.
     * @param null $timespan Makes the token expire after $timespan seconds. (null = never)
     * @param bool $multiple Makes the token reusable and not one-time. (Useful for ajax-heavy requests).
     * @throws BaseException
     * @internal param mixed $origin The object/associative array to retreive the token data from (usually $_POST).
     * @return bool Returns FALSE if a CSRF attack is detected, TRUE otherwise.
     */
	public static function checkCSRF ( $key, $postdata, $throwException = false, $timespan = null, $multiple = true )
	{

		$token = Session::get('csrf_' . $key, false);

		if ( !$token )
		{
			if ( $throwException || self::$enableThrowException )
			{
				throw new BaseException( 'Missing CSRF session token.' );
			}
			else
			{
				return false;
			}
		}

		if ( !isset( $postdata[ $key ] ) )
		{
			if ( $throwException || self::$enableThrowException )
			{
				throw new BaseException( 'Missing CSRF form token.' );
			}
			else
			{
				return false;
			}
		}

		// Get valid token from session
		$hash = $token;

		// Free up session token for one-time CSRF token usage.
		#if ( !$multiple )
		#{
			Session::delete('csrf_' . $key);
		#}

		// Origin checks
		if ( self::$doOriginCheck )
		{
			$env   = Env::getInstance();
			$s = $env->ip() . $env->httpUserAgent();
			if ( sha1($s) !== substr(base64_decode($hash), 10, 40) )
			{
				if ( $throwException || self::$enableThrowException )
				{
					throw new BaseException( 'Form origin does not match token origin.' );
				}
				else
				{
					return false;
				}
			}
		}

		// Check if session token matches form token
		if ( $postdata[ $key ] != $hash )
		{
			if ( $throwException || self::$enableThrowException )
			{
				throw new BaseException( 'Invalid CSRF token.' );
			}
			else
			{
				return false;
			}
		}

		// Check for token expiration
		if ( $timespan !== null && is_int($timespan) )
		{
			if ( intval(substr(base64_decode($hash), 0, 10)) + $timespan < TIMESTAMP )
			{
				Session::delete('csrf_' . $key);

				if ( $throwException || self::$enableThrowException )
				{

					throw new BaseException( 'CSRF token has expired.' );
				}
				else
				{
					return false;
				}
			}
		}

		return true;
	}


	/**
	 * CSRF token generation method. After generating the token, put it inside a hidden form field named $key.
	 *
	 * @param string $key The session key where the token will be stored. (Will also be the name of the hidden field name)
	 * @return string The generated, base64 encoded token.
	 */
	public static function generateCSRF ( $key )
	{
		$token = Session::get('csrf_' . $key, false);
		if ( $token )
		{
			return $token;
		}

		$extra = '';

		if ( self::$doOriginCheck )
		{
			$env   = Env::getInstance();
			$s = $env->ip() . $env->httpUserAgent();
			$extra = sha1($s);
		}


		// token generation (basically base64_encode any random complex string, time() is used for token expiration)
		$token = base64_encode(TIMESTAMP . $extra . self::randomString(32));

		// store the one-time token in session
		Session::save('csrf_' . $key, $token);

		return $token;
	}


	/**
	 * @param string $key The session and $origin key where to find the token.
	 * @return array|bool
	 */
	public static function getCSRFToken ( $key )
	{

		return Session::get('csrf_' . $key);
	}

    /**
     * @param $key
     */
    public static function cleanCSRF ( $key )
	{
		Session::delete('csrf_' . $key);
	}

	/**
	 * Generates a random string of given $length.
	 *
	 * @param Integer $length The string length.
	 * @return String The randomly generated string.
	 */
	protected static function randomString ( $length )
	{

		$seed = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijqlmnopqrtsuvwxyz0123456789';
		$max  = strlen($seed) - 1;

		$string = '';
		for ( $i = 0; $i < $length; ++$i )
		{
			$string .= $seed{intval(mt_rand(0.0, $max))};
		}

		return $string;
	}

}