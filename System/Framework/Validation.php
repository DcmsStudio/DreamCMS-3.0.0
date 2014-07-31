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
 * @file         Validation.php
 */
class Validation extends Loader
{

	private $data = array ();

	private $errors = array ();

	private $rules = array ();

    /**
     * @param $data
     * @param $rules
     */
    public function __construct ( $data, $rules )
	{

		parent::__construct();

		$this->data  = $data;
		$this->rules = $rules;
	}

    /**
     * @return array
     */
    public function validate ()
	{

		foreach ( $this->rules as $field => $ruleset )
		{
			$data = isset( $this->data[ $field ] ) ? $this->data[ $field ] : null;

			foreach ( $ruleset as $validation => $config )
			{
				$validator_method = 'validate_' . $validation;

				if ( method_exists($this, $validator_method) )
				{
					$valid = $this->$validator_method($data, $field, $config);

					if ( !$valid )
					{
						if ( defined('ADM_SCRIPT') )
						{
							$this->errors[ ] = $config[ 'message' ];
						}
						else
						{
							$this->errors[ $field ][ ] = $config[ 'message' ];
						}
					}


					if ( !$valid && ( isset( $config[ 'stop' ] ) && $config[ 'stop' ] === true ) )
					{
						break;
					}
				}
				else
				{
					Error::raise(sprintf(trans('The validator has no method `%s` - cannot validate posted data. Field: %s'), $validator_method, $field));
				}
			}
		}

		return $this->errors;
	}

    /**
     * @param $data
     * @param $field
     * @param $config
     * @return bool
     */
    protected function validate_required ( $data, $field, $config )
	{
		return isset( $this->data[ $field ] );
	}

    /**
     * @param $data
     * @param $field
     * @param $config
     * @return bool
     */
    protected function validate_min_length ( $data, $field, $config )
	{

		if ( !isset( $config[ 'test' ] ) )
		{
			Error::raise(sprintf(trans('The minimum length validation for `%s` has no length value supplied in it\'s configuration - cannot validate posted data.'), $field));
		}

		return strlen($data) >= $config[ 'test' ];
	}

    /**
     * @param $data
     * @param $field
     * @param $config
     * @return bool
     */
    protected function validate_max_length ( $data, $field, $config )
	{

		if ( !isset( $config[ 'test' ] ) )
		{
			Error::raise(sprintf(trans('The maximum length validation for `%s` has no length value supplied in it\'s configuration - cannot validate posted data.'), $field));
		}

		return strlen($data) <= $config[ 'test' ];
	}

    /**
     * @param $data
     * @param $field
     * @param $config
     * @return bool
     */
    protected function validate_unique ( $data, $field, $config )
	{

		if ( !isset( $config[ 'table' ] ) )
		{
			Error::raise(sprintf(trans('The unique validation for `%s` has no table supplied in it\'s configuration - cannot validate posted data.'), $field));
		}
		if ( !isset( $config[ 'id_field' ] ) )
		{
			Error::raise(sprintf(trans('The unique validation for `%s` has no id_field supplied in it\'s configuration - cannot validate posted data.'), $field));
		}

		$id = isset( $this->data[ $config[ 'id_field' ] ] ) ? $this->data[ $config[ 'id_field' ] ] : false;
		if ( $id === false )
		{
			Error::raise(sprintf(trans('Could not find the %s value to use in a unique validation - cannot validate posted data.'), $config[ 'id_field' ]));
		}

		$uservalidation = !empty( $config[ 'uservalidation' ] ) ? true : false;
		$where          = !empty( $config[ 'where' ] ) ? 'AND ' . $config[ 'where' ] : '';


		if ( $uservalidation )
		{
			$sql = 'SELECT COUNT(*) AS counted FROM %tp%' . $config[ 'table' ] . '
                    WHERE `' . $field . '` = ' . $this->db->quote($data) . $where;
			$rs  = $this->db->query($sql)->fetch();

			return ( ( $rs[ 'counted' ] > 0 ) ? false : true );
		}


		$sql = '
            SELECT COUNT(*) AS counted
            FROM %tp%' . $config[ 'table' ] . '
            WHERE `' . $field . '` = ' . $this->db->quote($data) . ' AND `' . $config[ 'id_field' ] . '` != ' . $this->db->quote($id) . ' ' . $where;

		$rs  = $this->db->query($sql);
		$row = $rs->fetch();

		return $row[ 'counted' ] === 0;
	}

    /**
     * @param $data
     * @param $field
     * @param $config
     * @return bool
     */
    protected function validate_identical ( $data, $field, $config )
	{

		if ( !isset( $config[ 'test' ] ) )
		{
			Error::raise(sprintf(trans('The identical validation for `%s` has no value to test against - cannot validate posted data.'), $field));
		}
		$value_1 = $data;
		$value_2 = $config[ 'test' ];

		return $value_1 === $value_2;
	}

    /**
     * @param string $data
     * @param string $field
     * @param array $config
     * @return bool
     */
    protected function validate_email ( $data, $field, $config )
	{

		#$valid = ( self::isValidEmail($data) ? true : false );

		$chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
		if ( strpos($data, '@') !== false && strpos($data, '.') !== false && strlen($data) < 256 )
		{
			if ( preg_match($chars, $data) )
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}


		return ( strlen($data) < 256 && $valid );
	}
    /**
     * @param string $data
     * @param string $field
     * @param array $config
     * @return bool
     */
	protected function validate_integer ( $data, $field, $config )
	{

		return ( preg_match('/^\d*$/', $data) === 1 );
	}

    /**
     * @param string $data
     * @param string $field
     * @param array $config
     * @return bool
     */
	protected function validate_alpha ( $data, $field, $config )
	{

		if ( function_exists('mb_eregi') )
		{
			if ( !mb_eregi('^[[:alpha:] \.-]*$', $data) )
			{
				return false;
			}
		}
		else
		{
			if ( !preg_match('/^[\pL \.-]*$/u', $data) )
			{
				return false;
			}
		}

		return true;
	}
    /**
     * @param string $data
     * @param string $field
     * @param array $config
     * @return bool
     */
	protected function validate_alphanum ( $data, $field, $config )
	{

		if ( function_exists('mb_eregi') )
		{
			if ( !mb_eregi('^[[:alnum:] \.-]*$', $data) )
			{
				return false;
			}
		}
		else
		{
			if ( !preg_match('/^[\pN\pL \._-]*$/u', $data) )
			{
				return false;
			}
		}

		return true;
	}
    /**
     * @param string $data
     * @param string $field
     * @param array $config
     * @return bool
     */
	protected function validate_nowhitespace ( $data, $field, $config )
	{

		return !preg_match('/\s/', $data);
	}
    /**
     * @param string $data
     * @param string $field
     * @param array $config
     * @return bool
     */
	protected function validate_nostars ( $data, $field, $config )
	{

		return !preg_match('/\*/', $data);
	}
    /**
     * @param string $data
     * @param string $field
     * @param array $config
     * @return bool
     */
	protected function validate_phone ( $data, $field, $config )
	{

		if ( !preg_match('/^[\d \+\(\)\/-]*$/', html_entity_decode($data)) )
		{
			return false;
		}

		return true;
	}
    /**
     * @param string $data
     * @param string $field
     * @param array $config
     * @return bool
     */
	protected function validate_percent ( $data, $field, $config )
	{

		if ( !is_numeric($data) || $data < 0 || $data > 100 )
		{
			return false;
		}

		return true;
	}
    /**
     * @param string $data
     * @param string $field
     * @param array $config
     * @return bool
     */
	protected function validate_minvalue ( $data, $field, $config )
	{

		if ( !isset( $config[ 'test' ] ) )
		{
			Error::raise(sprintf(trans('The minimum value validation for `%s` has no value supplied in it\'s configuration - cannot validate posted data.'), $field));
		}

		return $data >= $config[ 'test' ];
	}
    /**
     * @param string $data
     * @param string $field
     * @param array $config
     * @return bool
     */
	protected function validate_maxvalue ( $data, $field, $config )
	{

		if ( !isset( $config[ 'test' ] ) )
		{
			Error::raise(sprintf(trans('The maximum value validation for `%s` has no value supplied in it\'s configuration - cannot validate posted data.'), $field));
		}

		return $data <= $config[ 'test' ];
	}
    /**
     * @param string $data
     * @param string $field
     * @param array $config
     * @return bool
     */
	protected function validate_username ( $data, $field, $config )
	{

		return self::isValidUsername($data);
	}
    /**
     * @param string $data
     * @param string $field
     * @param array $config
     * @return bool
     */
	protected function validate_url ( $data, $field, $config )
	{

		return self::isValidUrl($data, true, 'https?');
	}
    /**
     * @param string $data
     * @param string $field
     * @param array $config
     * @return bool
     */
	protected function validate_regex ( $data, $field, $config )
	{

		if ( !isset( $config[ 'regex' ] ) )
		{
			Error::raise(sprintf(trans('The test value validation for `%s` has no value supplied in it\'s configuration - cannot validate posted data.'), $field));
		}

		$clean = preg_replace('#' . preg_quote($config[ 'regex' ], '#') . '#is', '', $data);

		if ( $clean === $data )
		{
			return true;
		}

		return false;
	}

	/**
	 * if the IP is valid then return true
	 *
	 * @param bool|string $ip
	 * @return bool
	 */
	public static function isBannedIp ( $ip = false )
	{

		if ( $ip === '' || $ip === false || $ip == '127.0.0.1' )
		{
			return false;
		}

		$db = Database::getInstance();
		$r  = $db->query("SELECT spammer_id FROM %tp%spammers WHERE spammer_ip=" . $db->quote($ip))->fetch();

		if ( $r[ 'spammer_id' ] )
		{
			$sql = "UPDATE %tp%spammers SET spammer_count=spammer_count+1 WHERE spammer_id=" . $r[ 'spammer_id' ];
			$db->query($sql);

			return true;
		}

		return false;
	}

	/**
	 * Is a valid Email then return true
	 *
	 * @param string $email
	 * @return bool
	 */
	public static function isValidEmail ( $email )
	{

		if ( !trim($email) )
		{
			return false;
		}

		$no_ws_ctl      = "[\\x01-\\x08\\x0b\\x0c\\x0e-\\x1f\\x7f]";
		$alpha          = "[\\x41-\\x5a\\x61-\\x7a]";
		$digit          = "[\\x30-\\x39]";
		$cr             = "\\x0d";
		$lf             = "\\x0a";
		$crlf           = "($cr$lf)";
		$obs_char       = "[\\x00-\\x09\\x0b\\x0c\\x0e-\\x7f]";
		$obs_text       = "($lf*$cr*($obs_char$lf*$cr*)*)";
		$text           = "([\\x01-\\x09\\x0b\\x0c\\x0e-\\x7f]|$obs_text)";
		$obs_qp         = "(\\x5c[\\x00-\\x7f])";
		$quoted_pair    = "(\\x5c$text|$obs_qp)";
		$wsp            = "[\\x20\\x09]";
		$obs_fws        = "($wsp+($crlf$wsp+)*)";
		$fws            = "((($wsp*$crlf)?$wsp+)|$obs_fws)";
		$ctext          = "($no_ws_ctl|[\\x21-\\x27\\x2A-\\x5b\\x5d-\\x7e])";
		$ccontent       = "($ctext|$quoted_pair)";
		$comment        = "(\\x28($fws?$ccontent)*$fws?\\x29)";
		$cfws           = "(($fws?$comment)*($fws?$comment|$fws))";
		$cfws           = $fws . "*";
		$atext          = "($alpha|$digit|[\\x21\\x23-\\x27\\x2a\\x2b\\x2d\\x2f\\x3d\\x3f\\x5e\\x5f\\x60\\x7b-\\x7e])";
		$atom           = "($cfws?$atext+$cfws?)";
		$qtext          = "($no_ws_ctl|[\\x21\\x23-\\x5b\\x5d-\\x7e])";
		$qcontent       = "($qtext|$quoted_pair)";
		$quoted_string  = "($cfws?\\x22($fws?$qcontent)*$fws?\\x22$cfws?)";
		$word           = "($atom|$quoted_string)";
		$obs_local_part = "($word(\\x2e$word)*)";
		$obs_domain     = "($atom(\\x2e$atom)*)";
		$dot_atom_text  = "($atext+(\\x2e$atext+)*)";
		$dot_atom       = "($cfws?$dot_atom_text$cfws?)";
		$dtext          = "($no_ws_ctl|[\\x21-\\x5a\\x5e-\\x7e])";
		$dcontent       = "($dtext|$quoted_pair)";
		$domain_literal = "($cfws?\\x5b($fws?$dcontent)*$fws?\\x5d$cfws?)";
		$local_part     = "($dot_atom|$quoted_string|$obs_local_part)";
		$domain         = "($dot_atom|$domain_literal|$obs_domain)";
		$addr_spec      = "($local_part\\x40$domain)";


		$done = 0;

		while ( !$done )
		{
			$new = preg_replace("!$comment!", '', $email);

			if ( strlen($new) === strlen($email) )
			{
				$done = 1;
			}

			$email = $new;
		}

		if ( preg_match("!^$addr_spec$!", $email) )
		{
			return true;
		}

		return false;
	}

	/**
	 * Checking the HTTP input Date
	 * only use from Dashboard
	 * (Publish on / Publish off Date)
	 *
	 * @param  int $pubdate
	 * @return bool
	 */
	public static function isValidPublishingDate ( $pubdate )
	{

		if ( $pubdate === 0 )
		{
			return true;
		}
	}

	/**
	 * Test Username
	 *
	 * @param string $username
	 * @return bool
	 */
	public static function isValidUsername ( $username )
	{

		if ( !trim($username) )
		{
			return false;
		}

		$db        = Database::getInstance();
		$username  = trim((string)$username);
		$ban_names = explode("\n", preg_replace("/\s*\n\s*/", "\n", strtolower(trim((string)Settings::get('ban_name', '')))));
		if ( count($ban_names) && in_array(strtolower($username), $ban_names) )
		{
			return false;
		}

		$sql    = "SELECT COUNT(userid) AS found FROM %tp%users WHERE username = " . $db->quote($username);
		$result = $db->query($sql)->fetch();

		if ( $result[ 'found' ] )
		{
			return false;
		}


		return true;
	}

    /**
     * Check URL
     * if is Valid URL then return true
     *
     * @param string $strurl
     * @param bool $http_required
     * @param string $protocol
     * @return bool
     */
	public static function isValidUrl ( $strurl, $http_required = false, $protocol = 'http' )
	{

		$regex = "/^(http(s)?:\/\/)((\d+\.\d+\.\d+\.\d+)|(([\w-]+\.)+([a-zA-Z][\w-]*)))(:[1-9][0-9]*)?(\/([\w-.\/:%\+@&=]+[\w- \.\/?:\%+@&=]*)?)?(#(.*))?$/i";

		if ( $http_required )
		{
			$protocol = ( $protocol ? $protocol : 'https?' );

			if ( !preg_match('/^' . preg_quote($protocol, '/') . ':/i', $strurl) && !preg_match($regex, $strurl) )
			{
				return false;
			}
		}
		else
		{
			if ( !preg_quote($regex, $strurl) )
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Validing input Captcha
	 *
	 * @param string $inputcaptcha
	 * @return bool (true or false)
	 */
	public static function isValidCapcha ( $inputcaptcha )
	{

		if ( !trim($inputcaptcha) )
		{
			return false;
		}
	}

	/**
	 * Checking the HTTP input type
	 *
	 *
	 *
	 * @param string $input
	 * @param string $inputKey default is null
	 * @param string $type     (string|integer|array|float)
	 * @param string $not_allowed_chars
	 * @return bool default return false
	 */
	public static function isValidInput ( $input, $inputKey = null, $type = 'string', $not_allowed_chars = null )
	{

		$doReturn = false;

		$input = ( $inputKey !== null ? HTTP::input($input) : $input );


		switch ( strtolower($type) )
		{
			case 'string':
				if ( is_string($input) )
				{
					$doReturn = true;
				}

				if ( $doReturn && $not_allowed_chars !== null )
				{
					if ( preg_match('/(' . preg_quote($not_allowed_chars, '/') . ')/', $input) )
					{
						$doReturn = false;
					}
				}

				break;

			case 'integer':
				if ( is_integer($input) )
				{
					$doReturn = true;
				}
				break;

			case 'float':
				if ( is_float($input) )
				{
					$doReturn = true;
				}
				break;

			case 'array':

				if ( is_array($input) )
				{
					$doReturn = true;
				}
				break;
		}


		return $doReturn;
	}

}
