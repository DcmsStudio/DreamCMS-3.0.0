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
 * @file         Form.php
 */

/**
 * Class Form
 */
class Form extends Loader
{

	/**
	 * @var null
	 */
	protected static $objInstance = null;

	/**
	 * @var null
	 */
	protected $_tokenKey = null;

	/**
	 * @var null
	 */
	protected $_token = null;

	/**
	 * @var
	 */
	public $allowed_fields;

	/**
	 * @var null
	 */
	private $parsedField = null;

	/**
	 * @var
	 */
	private $fieldData;

	/**
	 * @var
	 */
	private $uuid;

	protected static $doOriginCheck = false;

	/**
	 * Prevent cloning of the object (Singleton)
	 */
	private function __clone ()
	{

	}

	/**
	 * Return the current object instance (Singleton)
	 *
	 * @return Form
	 */
	public static function getInstance ()
	{

		if ( !is_object(self::$objInstance) )
		{
			self::$objInstance = new Form();
			self::$objInstance->setToken();
		}

		return self::$objInstance;
	}

	/**
	 * @param null $tokenKey
	 */
	public function setToken ( $tokenKey = null )
	{

		if ( $tokenKey === null )
		{
			$this->_tokenKey = substr(md5(Session::getId()), 0, 8);
		}
		else
		{
			$this->_tokenKey = substr(md5($tokenKey), 0, 8);
		}
	}

	/**
	 * Adds extra useragent and remote_addr checks to CSRF protections.
	 */
	public static function enableOriginCheck ()
	{

		self::$doOriginCheck = true;
	}


	/**
	 * Check CSRF tokens match between session and $origin.
	 * Make sure you generated a token in the form before checking it.
	 *
	 * @param string $key            The session and $origin key where to find the token.
	 * @param mixed  $origin         The object/associative array to retreive the token data from (usually $_POST).
	 * @param bool   $throwException TRUE to throw exception on check fail, FALSE or default to return false.
	 * @param null   $timespan       Makes the token expire after $timespan seconds. (null = never)
	 * @param bool   $multiple       Makes the token reusable and not one-time. (Useful for ajax-heavy requests).
	 * @return bool Returns FALSE if a CSRF attack is detected, TRUE otherwise.
	 * @throws BaseException
	 */
	public static function checkCSRF ( $key, $origin, $throwException = false, $timespan = null, $multiple = false )
	{

		$token = Session::get('csrf_' . $key, false);

		if ( !$token )
		{
			if ( $throwException )
			{
				throw new BaseException( 'Missing CSRF session token.' );
			}
			else
			{
				return false;
			}
		}

		if ( !isset( $origin[ $key ] ) )
		{
			if ( $throwException )
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
		if ( !$multiple )
		{
			Session::delete('csrf_' . $key);
		}

		// Origin checks
		if ( self::$doOriginCheck && sha1($_SERVER[ 'REMOTE_ADDR' ] . $_SERVER[ 'HTTP_USER_AGENT' ]) !== substr(base64_decode($hash), 10, 40) )
		{
			if ( $throwException )
			{
				throw new BaseException( 'Form origin does not match token origin.' );
			}
			else
			{
				return false;
			}
		}

		// Check if session token matches form token
		if ( $origin[ $key ] !== $hash )
		{
			if ( $throwException )
			{
				throw new BaseException( 'Invalid CSRF token.' );
			}
			else
			{
				return false;
			}
		}

		// Check for token expiration
		if ( $timespan !== null && is_int($timespan) && (int)substr(base64_decode($hash, 0, 10)) + $timespan < time() )
		{
			if ( $throwException )
			{
				throw new BaseException( 'CSRF token has expired.' );
			}
			else
			{
				return false;
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

		if ( ($token = Session::get('csrf_' . $key, false)) )
		{
			return $token;
		}

		$extra = self::$doOriginCheck ? sha1($_SERVER[ 'REMOTE_ADDR' ] . $_SERVER[ 'HTTP_USER_AGENT' ]) : '';
		// token generation (basically base64_encode any random complex string, time() is used for token expiration)
		$token = base64_encode(time() . $extra . self::randomString(32));
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
			$string .= $seed{(int)mt_rand(0.0, $max)};
		}

		return $string;
	}

	public function init ()
	{

		$this->allowed_fields = array (
			"text"             => 'Textfeld',
			"password"         => 'Passwort',
			"textarea"         => 'Textarea',
			"radio"            => 'Radio',
			"checkbox"         => 'Checkbox',
			"select"           => 'Auswahl Feld',
			"multiselect"      => 'Mehrfach Auswahl',
			"wysiwyg"          => 'Wysiwyg Text',
			"richtext"         => 'Wysiwyg Text',
			"button"           => 'Button',
			"hidden"           => 'verstecktes feld',
			"time"             => 'Zeit',
			// "options" => 'Auswahl Feld',
			"sortorder"        => 'Sortierung',
			"date"             => 'Datum',
			"perpage"          => 'Per Page',
			"fileupload"       => 'Datei Upload',
			"options"          => '', //Checkbox
			"grouppermissions" => 'Benutzergruppen (Zugriffsberechtigung)',
			"spamprotect"      => 'Spamschutz'
		);
	}

	public static function _getFields ()
	{

		$f = new Form();

		return $f->allowed_fields;
	}

	/**
	 * @param        $field
	 * @param string $element_uuid
	 * @return bool|null
	 */
	public function process ( $field, $element_uuid = '' )
	{

		$this->init();


		if ( !isset( $this->allowed_fields[ strtolower($field[ 'type' ]) ] ) )
		{
			return false;
		}

		$this->uuid      = $element_uuid;
		$this->fieldData = $field;

		$type = ucfirst(strtolower($field[ 'type' ]));

		$method = $type . 'Field';
		$this->$method();

		return $this->parsedField;
	}

	/**
	 * @return null
	 */
	public function get ()
	{

		return $this->parsedField;
	}

	private function TextField ()
	{

		$size = ( isset( $this->fieldData[ 'size' ] ) ? $this->fieldData[ 'size' ] : 70 );


		$this->parsedField = '<input type="text" name="' . $this->fieldData[ 'name' ] . '" id="field-' . $this->fieldData[ 'name' ] . '" value="' . self::escape($this->fieldData[ 'value' ]) . '" size="' . $size . '" />';
	}

	private function PasswordField ()
	{

		$size = ( isset( $this->fieldData[ 'size' ] ) ? $this->fieldData[ 'size' ] : 70 );

		$this->parsedField = '<input type="password" name="' . $this->fieldData[ 'name' ] . '" id="field-' . $this->fieldData[ 'name' ] . '" value="' . self::escape($this->fieldData[ 'value' ]) . '" size="' . $size . '" />';
	}

	private function TextareaField ()
	{

		$cols = ( isset( $this->fieldData[ 'cols' ] ) ? $this->fieldData[ 'cols' ] : 60 );
		$rows = ( isset( $this->fieldData[ 'rows' ] ) ? $this->fieldData[ 'rows' ] : 8 );


		$this->parsedField = '<textarea name="' . $this->fieldData[ 'name' ] . '" id="field-' . $this->fieldData[ 'name' ] . '" rows="' . $rows . '" cols="' . $cols . '">' . self::escape($this->fieldData[ 'value' ]) . '</textarea>';
	}

	private function RadioField ()
	{

		$this->parsedField = '';
		if ( !empty( $this->fieldData[ 'label' ] ) )
		{
			$this->parsedField .= '<label for="field-' . $this->fieldData[ 'name' ] . '">';
		}

		$this->parsedField .= '<input type="radio" name="' . $this->fieldData[ 'name' ] . '" id="field-' . $this->fieldData[ 'name' ] . '-' . self::escape($this->fieldData[ 'value' ]) . '" value="' . self::escape($this->fieldData[ 'value' ]) . '"' . ( $this->fieldData[ 'value' ] == $this->fieldData[ 'currentvalue' ] ?
				' checked="checked"' : '' ) . ' />';

		if ( !empty( $this->fieldData[ 'label' ] ) )
		{
			$this->parsedField .= ' ' . $this->fieldData[ 'label' ] . '</label>';
		}
	}

	private function CheckboxField ()
	{

		$this->parsedField = '';
		if ( !empty( $this->fieldData[ 'label' ] ) )
		{
			$this->parsedField .= '<label for="field-' . $this->fieldData[ 'name' ] . '">';
		}

		$this->parsedField .= '<input type="checkbox" name="' . $this->fieldData[ 'name' ] . '" id="field-' . $this->fieldData[ 'name' ] . '" value="' . self::escape($this->fieldData[ 'value' ]) . '"' . ( $this->fieldData[ 'value' ] == $this->fieldData[ 'currentvalue' ] ? ' checked="checked"' :
				'' ) . ' />';

		if ( !empty( $this->fieldData[ 'label' ] ) )
		{
			$this->parsedField .= ' ' . $this->fieldData[ 'label' ] . '</label>';
		}
	}

	private function OptionsField ()
	{

		$values = explode("\n", $this->fieldData[ 'value' ]);


		$ret = '';


		$fieldname = $this->fieldData[ 'name' ];
		$fieldid   = $this->fieldData[ 'name' ];

		foreach ( $values as $idx => $valueStr )
		{
			$ret = '<input type="text" name="' . $fieldname . '" id="elements-' . $fieldid . '-' . $idx . '" value="' . self::escape($valueStr) . '"' . ( $idx == $this->fieldData[ 'currentvalue' ] ? ' checked="checked"' : '' ) . ' />';
		}


		$this->parsedField = '<input type="checkbox" name="elements[' . $this->uuid . '][' . $this->fieldData[ 'name' ] . ']" id="elements-' . $this->uuid . '-' . $this->fieldData[ 'name' ] . '" value="' . self::escape($this->fieldData[ 'value' ]) . '"' . ( $this->fieldData[ 'value' ] == $this->fieldData[ 'currentvalue' ] ?
				' checked="checked"' : '' ) . ' />';
	}

	private function SelectField ()
	{

		$currentvalue = $this->fieldData[ 'currentvalue' ];

		$values = explode('|', $this->fieldData[ 'form_field_data' ]);

		$ret = '<select name="' . $this->fieldData[ 'name' ] . '" id="field-' . $this->fieldData[ 'name' ] . '">';

		foreach ( $values as $r )
		{
			$v = explode(',', $r);
			$ret .= '<option value="' . $v[ 0 ] . '"' . ( $v[ 0 ] == $currentvalue ? ' selected="selected"' : '' ) . '>' . $v[ 1 ] . '</option>';
		}

		$ret .= '</select>';
		$this->parsedField = $ret;
	}

	private function MultiselectField ()
	{

		$currentvalues = $this->fieldData[ 'currentvalue' ];
		#$currentvalues = explode(',', $currentvalues);


		$values = explode('|', $this->fieldData[ 'form_field_data' ]);

		$ret = '<select name="' . $this->fieldData[ 'name' ] . '" id="field-' . $this->fieldData[ 'name' ] . '" multiple="multiple" size="20">';

		foreach ( $values as $r )
		{
			$v = explode(',', $r);
			$ret .= '<option value="' . $v[ 0 ] . '"' . ( in_array($v[ 0 ], $currentvalues) ? ' selected="selected"' : '' ) . '>' . $v[ 1 ] . '</option>';
		}

		$ret .= '</select>';
		$this->parsedField = $ret;
	}

	private function RichtextField ()
	{

		if ( !function_exists('InitEditor') )
		{
			$personal = new Personal();
			$wysiwyg  = $personal->get('personal', 'settings');
			$wysiwyg  = $wysiwyg[ 'wysiwyg' ];

			if ( is_file(VENDOR_PATH . $wysiwyg . '/' . $wysiwyg . '_php5.php') )
			{
				require_once( VENDOR_PATH . $wysiwyg . '/' . $wysiwyg . '_php5.php' );
			}
		}


		$cols = ( isset( $this->fieldData[ 'cols' ] ) ? $this->fieldData[ 'cols' ] : 60 );
		$rows = ( isset( $this->fieldData[ 'rows' ] ) ? $this->fieldData[ 'rows' ] : 8 );


		InitEditor(Settings::get('portalurl'));
		$this->parsedField = EditorArea($this->fieldData[ 'value' ], $this->fieldData[ 'name' ], '100%', '200px', $cols, $rows);
	}

	private function WysiwygField ()
	{

		return $this->RichtextField();
	}

	private function ButtonField ()
	{

	}

	private function HiddenField ()
	{

		$this->parsedField = '<input type="hidden" name="' . $this->fieldData[ 'name' ] . '" id="field-' . $this->fieldData[ 'name' ] . '" value="' . self::escape($this->fieldData[ 'value' ]) . '" />';
	}

	private function TimeField ()
	{

	}

	private function DateField ()
	{

	}

	private function SortorderField ()
	{

	}

	private function PerpageField ()
	{

		$currentvalue = $this->fieldData[ 'currentvalue' ];

		$values = explode('|', $this->fieldData[ 'form_field_data' ]);
		$ret    = '<select name="elements[' . $this->uuid . '][' . $this->fieldData[ 'name' ] . ']" id="' . $this->uuid . '-' . $this->fieldData[ 'name' ] . '">';

		foreach ( $values as $r )
		{
			$v = explode(',', $r);
			$ret .= '<option value="' . $v[ 0 ] . '"' . ( $v[ 0 ] == $currentvalue ? ' selected="selected"' : '' ) . '>' . $v[ 1 ] . '</option>';
		}

		$ret .= '</select>';
		$this->parsedField = $ret;
	}

	private function FileuploadField ()
	{

		$size              = ( isset( $this->fieldData[ 'size' ] ) ? $this->fieldData[ 'size' ] : 70 );
		$this->parsedField = '<input type="file" name="elements[' . $this->uuid . '][' . $this->fieldData[ 'name' ] . ']" id="' . $this->uuid . '-' . $this->fieldData[ 'name' ] . '" value="" size="' . $size . '" />';
	}

	private function GrouppermissionsField ()
	{

	}

	/**
	 * @param        $value
	 * @param string $esc
	 * @return string
	 */
	public static function escape ( $value, $esc = 'html' )
	{

		return htmlspecialchars($value);
	}

	/**
	 *
	 * @return string
	 */
	public function getTokenKey ()
	{

		return $this->_tokenKey;
	}

	/**
	 *
	 * @return string
	 */
	public function getToken ()
	{

		return $this->_token;
	}

	/**
	 *
	 * @return Form
	 */
	public function generateToken ()
	{

		$this->_token = md5(uniqid(rand(), true));

		// save the token key in the session
		Session::save($this->_tokenKey, $this->_token);

		return $this;
	}

	/**
	 *
	 * @param mixed $token
	 * @return boolean
	 */
	public function isValidToken ( $token = null )
	{

		if ( $token === null && $this->input('_token') )
		{
			$tokens = $this->input('_token');

			if ( $tokens !== null )
			{
				$t = explode(':', $tokens);

				if ( isset( $t[ 1 ] ) && $t[ 1 ] === Session::get($t[ 0 ]) )
				{
					return true;
				}
			}
		}
		else
		{
			if ( $token !== null && $token === Session::get($this->getTokenKey()) )
			{
				return true;
			}
		}

		return false;
	}

	/**
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getFormByID ( $id )
	{

		return $this->db->query('SELECT * FROM %tp%forms WHERE `formid` = ?', $id)->fetch();
	}

	/**
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getFieldsByFormID ( $id )
	{

		return $this->db->query('SELECT * FROM %tp%form_fields WHERE `formid` = ?', $id)->fetchAll();
	}

	/**
	 * Used only in Frontend by ContentProvider (provider.form.php)
	 *
	 * @param string $name the name of the Form
	 * @param array  $attr optional parameters
	 * @return array
	 * @throws BaseException
	 */
	public function generateForm ( $name, $attr )
	{

		$form = $this->db->query('SELECT * FROM %tp%forms WHERE `name` = ?', $name)->fetch();
		if ( empty( $form[ 'formid' ] ) )
		{

			return array (
				'form'       => sprintf('The Form "%s" not exists! Please check the givig name.', $name),
				'formfields' => array ()
			);

			// throw new BaseException( sprintf('The Form "%s" not exists! Please check the givig name.', $name) );
		}


		$_fields   = ( !empty( $form[ 'fields' ] ) ? explode(',', $form[ 'fields' ]) : array (0) );
		$allFields = $this->db->query('SELECT * FROM %tp%form_fields WHERE `field_id` IN(0,' . implode(',', $_fields) . ')')->fetchAll();


		$tmp = array ();

		foreach ( $_fields as $id )
		{
			foreach ( $allFields as $r )
			{
				if ( $r[ 'field_id' ] == $id )
				{
					$tmp[ ] = $r;
				}
			}
		}


		$usedFields = ( !empty( $form[ 'fields' ] ) ? explode(',', $form[ 'fields' ]) : array () );

		$_fields = array ();

		//   error_reporting( E_ALL );
		foreach ( $tmp as $field )
		{
			if ( !in_array($field[ 'field_id' ], $usedFields) )
			{
				continue;
			}

			$field[ 'fieldtype' ] = $field[ 'type' ];
			$field[ 'id' ]        = $field[ 'name' ];
			$field[ 'fieldid' ]   = $field[ 'field_id' ];
			$field[ 'required' ]  = ( (isset($field[ 'required' ]) && $field[ 'required' ]) || (isset($field[ 'controls' ]) && $field[ 'controls' ]) ? 1 : 0 );

			//$availableSettings = unserialize($field['options']);
			//$attributes = Field::getFieldAttributes($field['type']);


			if ( strtolower($field[ 'type' ]) == 'spamprotect' )
			{
				$field[ 'required' ] = true;
			}

			try
			{
				$field_data = Field::getFieldDefinition($field);
			}
			catch ( Exception $e )
			{
				throw new BaseException( $e->getMessage() );
			}


			$field_data[ 'required' ]      = ( (isset($field[ 'required' ]) && $field[ 'required' ]) || (isset($field[ 'controls' ]) && $field[ 'controls' ]) ? 1 : 0 );
			$field_data[ 'display_order' ] = isset($field[ 'display_order' ]) ? $field[ 'display_order' ] : 0;
			$field_data[ 'fieldid' ]       = $field[ 'fieldid' ];
			$field_data[ 'type' ]          = $field[ 'fieldtype' ];

			try
			{
				$field_data[ 'field' ] = Field::getFieldRender($field_data);
			}
			catch ( Exception $e )
			{
				throw new BaseException( $e->getMessage() );
			}

			$field_data[ 'field_label' ] = ( $field_data[ 'grouplabel' ] ? $field_data[ 'grouplabel' ] : $field_data[ 'label' ] );

			$_fields[ ] = $field_data;
		}

		/**
		 * Add the security token field
		 */
		$this->generateToken();
		$_fields[ ] = array (
			'type'  => 'hidden',
			'field' => '<input type="hidden" name="_token" value="' . $this->getTokenKey() . ':' . $this->getToken() . '"/>'
		);


		$data                 = array ();
		$data[ 'form' ]       = $form;
		$data[ 'formfields' ] = $_fields;

		return $data;
	}

	/**
	 *
	 * @param string $name the name of the Form
	 * @param array  $inputdata
	 * @return array
	 */
	public function validateForm ( $name, $inputdata = array () )
	{

		$form      = $this->db->query('SELECT * FROM %tp%forms WHERE name = ?', $name)->fetch();
		$allFields = $this->db->query('SELECT * FROM %tp%form_fields WHERE formid = ?', $form[ 'formid' ])->fetchAll();

		$usedFields = !empty( $form[ 'fields' ] ) ? explode(',', $form[ 'fields' ]) : array ();

		$field_errors = array ();

		foreach ( $allFields as $field )
		{
			if ( !in_array($field[ 'field_id' ], $usedFields) )
			{
				continue;
			}

			$opts = ( !empty( $field[ 'options' ] ) ? unserialize($field[ 'options' ]) : array () );

			$field_error = false;
			$require     = ( ( $opts[ 'required' ] || $opts[ 'controls' ] ) ? 1 : 0 );
			$inputvalue  = isset( $inputdata[ $field[ 'name' ] ] ) ? $inputdata[ $field[ 'name' ] ] : null;


			$origcaptcha = null;
			$isCaptcha   = false;
			if ( strtolower($field[ 'type' ]) == 'spamprotect' )
			{
				$require     = true;
				$isCaptcha   = true;
				$origcaptcha = Session::get('site_captcha');
			}


			$options    = array ();
			$class_name = 'Field_' . ucfirst(strtolower($field[ 'type' ])) . 'Field';
			$attributes = call_user_func(array (
			                                   $class_name,
			                                   'getAttributes'
			                             ));

			foreach ( $attributes as $attribute )
			{
				if ( !empty( $data[ $attribute ] ) )
				{
					$options[ $attribute ] = $data[ $attribute ];
				}
			}

			$field[ 'options' ] = serialize($options);


			$field[ 'fieldtype' ] = $field[ 'type' ];
			$field[ 'id' ]        = $field[ 'name' ];
			$field[ 'fieldid' ]   = $field[ 'field_id' ];
			$field[ 'required' ]  = ( $field[ 'required' ] || $field[ 'controls' ] ? 1 : 0 );


			$class_name = 'Field_' . ucfirst(strtolower($field[ 'type' ])) . 'Field';
			$field_data = call_user_func_array(array (
			                                         $class_name,
			                                         'getFieldDefinition'
			                                   ), array (
			                                            $field
			                                      ));

			$isMail = false;
			if ( $field[ 'fieldtype' ] === 'email' )
			{
				$isMail = true;
			}


			if ( $require )
			{
				if ( empty( $inputvalue ) )
				{
					$field_error = true;
					if ( !isset( $field_errors[ $field[ 'name' ] ] ) )
					{
						$field_errors[ $field[ 'name' ] ][ ] = sprintf(trans('`%s` muss ausgefüllt sein.'), ( !empty( $opts[ 'grouplabel' ] ) ? $opts[ 'grouplabel' ] : $opts[ 'label' ] ));
					}
				}
				else
				{
					if ( !isset( $field_errors[ $field[ 'name' ] ] ) )
					{
						if ( $isMail && !Validation::isValidEmail($inputvalue) )
						{
							$field_errors[ $field[ 'name' ] ][ ] = trans('Fehlerhafte Email-Adresse!');
						}

						if ( $isCaptcha && strtolower($inputvalue) != strtolower($origcaptcha) )
						{
							$field_errors[ $field[ 'name' ] ][ ] = trans('Sicherheitscode ist falsch!');
						}
					}
				}
			}
			else
			{

				if ( $isMail && !Validation::isValidEmail($inputvalue) && !isset( $field_errors[ $field[ 'name' ] ] ) )
				{
					$field_errors[ $field[ 'name' ] ][ ] = trans('Fehlerhafte Email-Adresse!');
				}
			}

			if ( $field_error )
			{
				continue;
			}
		}

		return array (
			'form'   => $form,
			'errors' => $field_errors
		);
	}

}
