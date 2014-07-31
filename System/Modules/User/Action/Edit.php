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
 * @package      User
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Edit.php
 */
class User_Action_Edit extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$userid = (int)HTTP::input('userid');

		$model     = Model::getModelInstance('user');
		$formmodel = Model::getModelInstance('forms');

		$data                   = array ();
		$user_profilefield_data = array ();

		if ( $userid )
		{
			$data[ 'users' ]        = $model->getUser($userid);
			$myname                 = $data[ 'users' ][ 'username' ];
			$user_profilefield_data = $model->getProfileFieldsData($userid);
		}

		// read all Assigned fields
		$profilefields = $formmodel->getProfileFields();

		if ( HTTP::input('send') )
		{
			demoadm();

			HTTP::setinput('username', preg_replace("/\s{2,}/", " ", HTTP::input('username')));
			$post = $this->_post();

			$error = $fieldvalues = $fieldnames = "";

			$field_error  = false;
			$field_errors = array ();

			$submit_fields     = HTTP::input('field');
			$submit_yearfield  = HTTP::input('yearfield');
			$submit_dayfield   = HTTP::input('dayfield');
			$submit_monthfield = HTTP::input('monthfield');


			$newValues = array ();
			foreach ( $profilefields as $field )
			{
				$field_error = false;
				$require     = ($field[ 'reg_required' ] ? 1 : 0);
				$inputvalue  = HTTP::input('pf_' . $field[ 'profilefieldid' ]);

				$class_name            = 'Field_' . ucfirst(strtolower($field[ 'type' ])) . 'Field';
				$field[ 'attributes' ] = call_user_func(array (
				                                              $class_name,
				                                              'getAttributes'
				                                        ));

				$options = array ();
				foreach ( $field[ 'attributes' ] as $attribute )
				{
					if ( !empty($data[ $attribute ]) )
					{
						$options[ $attribute ] = $data[ $attribute ];
					}
				}
				$field[ 'options' ] = serialize($options);

				$field_data = call_user_func_array(array (
				                                         $class_name,
				                                         'getFieldDefinition'
				                                   ), array (
				                                            $field
				                                      ));

				if ( $require )
				{
					if ( empty($inputvalue) )
					{
						$field_error     = true;
						$field_errors[ ] = ($field_data[ 'grouplabel' ] ? $field_data[ 'grouplabel' ] :
							$field_data[ 'label' ]);
					}
				}

				if ( $field_error )
				{
					continue;
				}

				$value = !empty($inputvalue) ? $inputvalue : '';
				if ( is_array($value) )
				{
					$value = implode(',', $value);
				}

				$newValues[ ] = array (
					'value'    => $value,
					'rel'      => 'profilefield',
					'field_id' => $field[ 'field_id' ]
				);
			}


			$errors = self::validate($post, ($userid ? true : false));
			array_merge($errors, $field_errors);


			if ( count($errors) > 0 )
			{
				if ( !IS_AJAX )
				{
					Error::raise('Folgende Fehler sind aufgetreten:<br/>' . implode('<br/>', $errors));
				}

				echo Library::json(array (
				                         'success' => false,
				                         'msg'     => implode('; ', $errors)
				                   ));
				exit;
			}


			// homepage
			if ( $post[ 'homepage' ] && $post[ 'homepage' ] != 'http://' )
			{
				HTTP::setinput('homepage', preg_replace('/http:\/\//i', '', trim((string)$post[ 'homepage' ])));
			}

			// Geburtstag
			if ( is_numeric($post[ 'day' ]) && is_numeric($post[ 'month' ]) && is_numeric($post[ 'year' ])
			)
			{
				$birthday = '';
				if ( strlen($post[ 'year' ]) == 2 )
				{
					$birthday .= '19' . $post[ 'year' ];
				}
				else
				{
					$birthday .= $post[ 'year' ];
				}

				$birthday .= '-';
				if ( (int)$post[ 'month' ] < 10 )
				{
					$birthday .= '0' . (int)$post[ 'month' ];
				}
				else
				{
					$birthday .= (int)$post[ 'month' ];
				}
				$birthday .= '-';
				if ( (int)$post[ 'day' ] < 10 )
				{
					$birthday .= '0' . (int)$post[ 'day' ];
				}
				else
				{
					$birthday .= (int)$post[ 'day' ];
				}
			}
			else
			{
				$birthday = "0000-00-00";
			}

			HTTP::setinput('username', htmlspecialchars($post[ 'username' ]));
			HTTP::setinput('groupid', (int)$post[ 'groupid' ]);

			$insertid = 0;

			if ( $userid )
			{
				$formmodel->deleteProfileFieldData($userid);

				/**
				 * Save Profilfields
				 */
				foreach ( $newValues as $rs )
				{
					$rs[ 'userid' ] = $userid;
					$formmodel->saveFieldData($rs);
				}

				$rankid = $model->getRank($post[ 'groupid' ], $data[ 'users' ][ 'userposts' ], $post[ 'gender' ]);

				$newData = array (
					'username'       => $post[ 'username' ], // 'password' 			=> md5(HTTP::input('password') ),
					'email'          => $post[ 'email' ],
					'groupid'        => (int)$post[ 'groupid' ],
					'rankid'         => (int)$rankid[ 'rankid' ], #'user_title' => HTTP::input('homepage')),
					'usertext'       => (string)HTTP::input('usertext'),
					'signature'      => (string)HTTP::input('signature'),
					'icq'            => (int)HTTP::input('icq'),
					'aim'            => (string)HTTP::input('aim'),
					'yim'            => (string)HTTP::input('yim'),
					'msn'            => (string)HTTP::input('msn'),
					'homepage'       => (string)HTTP::input('homepage'),
					'birthday'       => $birthday,
					'gender'         => (int)HTTP::input('gender'),
					'showemail'      => (int)HTTP::input('showemail'),
					'admincanemail'  => (int)HTTP::input('admincanemail'),
					'usercanemail'   => (int)HTTP::input('usercanemail'),
					'invisible'      => (int)HTTP::input('invisible'),
					'usecookies'     => (int)HTTP::input('usecookies'),
					'styleid'        => (int)HTTP::input('styleid'),
					'timezoneoffset' => (string)HTTP::input('timezoneoffset'),
					'startweek'      => (int)HTTP::input('startweek'),
					'dateformat'     => HTTP::input('udateformat'),
					'timeformat'     => HTTP::input('utimeformat'),
					'emailnotify'    => (int)HTTP::input('emailnotify'),
					'receivepm'      => (int)HTTP::input('receivepm'),
					'emailonpm'      => (int)HTTP::input('emailonpm'),
					'pmpopup'        => (int)HTTP::input('pmpopup'),
					'nosessionhash'  => (int)HTTP::input('nosessionhash'),
					'name'           => HTTP::input('name'),
					'lastname'       => HTTP::input('lastname'),
					'street'         => HTTP::input('street'),
					'company_name'   => HTTP::input('company_name'),
					'ustid'          => HTTP::input('ustid'),
					'country'        => HTTP::input('country'),
					'zip'            => HTTP::input('zip'),
					'user_from'      => HTTP::input('user_from'),
					'mobile_phone'   => HTTP::input('mobile_phone'),
					'phone'          => HTTP::input('phone'),
					'fax'            => HTTP::input('fax'),
					'blocked'        => (int)HTTP::input('blocked'),
				);


				$model->save($userid, $newData);

				Library::log("Update User Name: " . $post[ 'username' ]);
			}
			else
			{


				$rankid = $model->getRank($post[ 'groupid' ], 0, $post[ 'gender' ]);

				$newData = array (
					'username'       => $post[ 'username' ],
					'password'       => md5(HTTP::input('password')),
					'email'          => $post[ 'email' ],
					'groupid'        => (int)$post[ 'groupid' ],
					'rankid'         => $rankid[ 'rankid' ], // 'user_title' => HTTP::input('homepage')),
					'usertext'       => HTTP::input('usertext'),
					'signature'      => HTTP::input('signature'),
					'icq'            => (int)HTTP::input('icq'),
					'aim'            => HTTP::input('aim'),
					'yim'            => HTTP::input('yim'),
					'msn'            => HTTP::input('msn'),
					'homepage'       => HTTP::input('homepage'),
					'birthday'       => $birthday,
					'gender'         => (int)HTTP::input('gender'),
					'showemail'      => (int)HTTP::input('showemail'),
					'admincanemail'  => (int)HTTP::input('admincanemail'),
					'usercanemail'   => (int)HTTP::input('usercanemail'),
					'invisible'      => (int)HTTP::input('invisible'),
					'usecookies'     => (int)HTTP::input('usecookies'),
					'styleid'        => (int)HTTP::input('styleid'), // 'activation' 		=> 1,
					'timezoneoffset' => HTTP::input('timezoneoffset'),
					'startweek'      => (int)HTTP::input('startweek'),
					'dateformat'     => HTTP::input('udateformat'),
					'timeformat'     => HTTP::input('utimeformat'),
					'emailnotify'    => (int)HTTP::input('emailnotify'),
					'receivepm'      => (int)HTTP::input('receivepm'),
					'emailonpm'      => (int)HTTP::input('emailonpm'),
					'pmpopup'        => (int)HTTP::input('pmpopup'),
					'nosessionhash'  => (int)HTTP::input('nosessionhash'),
					'name'           => HTTP::input('name'),
					'lastname'       => HTTP::input('lastname'),
					'street'         => HTTP::input('street'),
					'company_name'   => HTTP::input('company_name'),
					'ustid'          => HTTP::input('ustid'),
					'country'        => HTTP::input('country'),
					'zip'            => HTTP::input('zip'),
					'user_from'      => HTTP::input('user_from'),
					'mobile_phone'   => HTTP::input('mobile_phone'),
					'phone'          => HTTP::input('phone'),
					'fax'            => HTTP::input('fax'),
					'blocked'        => (int)HTTP::input('blocked'),
					'regdate'        => time()
				);

				$insertid = $model->save(0, $newData);

				/**
				 * Save Profilfields
				 */
				foreach ( $newValues as $rs )
				{
					$rs[ 'userid' ] = $insertid;
					$formmodel->saveFieldData($rs);
				}
				Library::log("Create User Name: " . $post[ 'username' ]);
			}
			//$this->update_admins();

			echo Library::json(array (
			                         'success' => true,
			                         'msg'     => ($insertid ? trans('Benutzer wurde erstellt.') :
					                         trans('Änderungen am Benutzer wurden übernommen.')),
			                         'newid'   => $insertid
			                   ));


			exit;
		}
		else
		{
			$data[ 'users' ][ 'signature' ] = htmlspecialchars($data[ 'users' ][ 'signature' ]);
			$birthday                       = explode("-", $data[ 'users' ][ 'birthday' ]);
			$data[ 'users' ][ 'day' ]       = $birthday[ 2 ];
			$data[ 'users' ][ 'month' ]     = $birthday[ 1 ];

			if ( $birthday[ 0 ] != "0000" )
			{
				$data[ 'users' ][ 'year' ] = $birthday[ 0 ];
			}
		}


		$timezones = Library::getTimezones();
		foreach ( $timezones as $timeset => $title )
		{
			$data[ 'timezones' ][ ] = array (
				'value' => (string)$timeset,
				'title' => $title
			);
		}

		/**
		 *
		 */
		$_fields = array ();


		/**
		 * @todo Better Profilefield handling!
		 */
		foreach ( $profilefields as $f )
		{
			$f[ 'options' ] = (!empty($f[ 'options' ]) ? unserialize($f[ 'options' ]) : array ());


			$field               = array ();
			$field               = $f;
			$field[ 'id' ]       = 'pf_' . $f[ 'profilefieldid' ];
			$field[ 'required' ] = ($f[ 'reg_required' ] ? 1 : 0);
			$field[ 'controll' ] = $field[ 'required' ];
			$field[ 'value' ]    = isset($user_profilefield_data[ $f[ 'field_id' ] ]) ?
				$user_profilefield_data[ $f[ 'field_id' ] ] : '';


			$attributes = Field::getFieldAttributes($f[ 'type' ]);
			$options    = array ();
			foreach ( $attributes as $attribute )
			{
				if ( !empty($user_profilefield_data[ $attribute ]) )
				{
					$options[ $attribute ] = $user_profilefield_data[ $attribute ];
				}
			}

			$field[ 'options' ] = serialize($options);

			if ( isset($attributes[ 'controls' ]) )
			{
				$field[ 'controls' ] = $field[ 'required' ];
			}

			$field_data                    = Field::getFieldDefinition($field);
			$field_data[ 'required' ]      = ($f[ 'reg_required' ] ? $f[ 'reg_required' ] : $f[ 'required' ]);
			$field_data[ 'display_order' ] = $field[ 'display_order' ];
			$field_data[ 'field_id' ]      = $field[ 'field_id' ];

			$field_data[ 'field' ] = Field::getFieldRender($field_data);

			$field_data[ 'type' ]        = $field[ 'fieldtype' ];
			$field_data[ 'description' ] = $field[ 'description' ];
			$field_data[ 'label' ]       = ($f[ 'options' ][ 'grouplabel' ] ? $f[ 'options' ][ 'grouplabel' ] :
				$f[ 'options' ][ 'label' ]);
			$_fields[ ]                  = $field_data;
		}

		$data[ 'profilefields' ] = $_fields;


		for ( $i = 1; $i <= 12; $i++ )
		{
			$data[ 'month_options' ][ ] = array (
				'month' => $i,
				'name'  => Locales::getMonthName($i, true)
			);
		}

		for ( $i = 1; $i <= 31; $i++ )
		{
			$data[ 'day_options' ][ ] = array (
				'day' => $i
			);
		}

		for ( $i = 0; $i <= 6; $i++ )
		{
			$data[ 'startweek_options' ][ ] = array (
				'day'  => $i,
				'name' => Locales::getDayName($i, true)
			);
		}

		$data[ 'countries' ] = $this->db->query('SELECT tld, country AS name FROM %tp%countries ORDER BY country')->fetchAll();

		$sql                     = "SELECT id, title AS name FROM %tp%skins WHERE published=1 ORDER BY title ASC";
		$data[ 'style_options' ] = $this->db->query($sql)->fetchAll();


		$sql                   = "SELECT
				groupid,
				title
				FROM %tp%users_groups
				WHERE default_group <> 1
				ORDER BY default_group DESC, title ASC";
		$data[ 'user_groups' ] = $this->db->query($sql)->fetchAll();

		if ( defined('DEMO_USERID') && DEMO_USERID > 0 && User::getUserId() == DEMO_USERID )
		{
			$data[ 'users' ][ 'password' ] = '';
		}

		Library::addNavi(trans('Benutzer Übersicht'));
		Library::addNavi(($userid ? sprintf(trans('Benutzer `%s` bearbeiten'), $data[ 'users' ][ 'username' ]) :
			trans('Benutzer erstellen')));
		$this->Template->process('users/edit', $data, true);
	}

	/**
	 * Validate Fields
	 *
	 * @param array   $data input
	 * @param boolean $isUpdate
	 * @return array of errors
	 */
	public static function validate ( $data, $isUpdate = false )
	{

		$rules = array ();

		$rules[ 'username' ][ 'required' ]   = array (
			'message' => trans('Der Benutzername ist erforderlich'),
			'stop'    => true
		);
		$rules[ 'username' ][ 'min_length' ] = array (
			'message' => sprintf(trans('Der Benutzername muss mind. %s Zeichen lang sein'), Settings::get('minusernamelength', 3)),
			'test'    => Settings::get('minusernamelength', 3)
		);
		$rules[ 'username' ][ 'max_length' ] = array (
			'message' => sprintf(trans('Der Benutzername darf maximal %s Zeichen lang sein'), Settings::get('maxusernamelength', 50)),
			'test'    => Settings::get('maxusernamelength', 50)
		);

		if ( !$isUpdate )
		{
			$rules[ 'username' ][ 'unique' ] = array (
				'message'        => trans('Der Benutzername existiert schon und kann daher nicht verwendet werden'),
				'uservalidation' => true,
				'table'          => 'users',
				'id_field'       => 'username'
			);
		}

		$rules[ 'email' ][ 'required' ] = array (
			'message' => trans('Email-Adresse ist erforderlich'),
			'stop'    => true
		);
		$rules[ 'email' ][ 'email' ]    = array (
			'message' => trans('Email-Adresse ist nicht korrekt'),
			'stop'    => true
		);

		if ( !Settings::get('multipleemailuse') && !$isUpdate )
		{
			$rules[ 'email' ][ 'unique' ] = array (
				'message'        => trans('Der Email existiert schon und kann daher nicht verwendet werden'),
				'uservalidation' => true,
				'table'          => 'users',
				'id_field'       => 'email'
			);
		}

		if ( !$isUpdate )
		{
			$rules[ 'password' ][ 'required' ]   = array (
				'message' => trans('Passwort ist erforderlich'),
				'stop'    => true
			);
			$rules[ 'password' ][ 'min_length' ] = array (
				'message' => sprintf(trans('Dein Passwort muss mind. %s Zeichen lang sein'), Settings::get('minuserpasswordlength', 3)),
				'test'    => Settings::get('minuserpasswordlength', 3)
			);
		}

		$validator = new Validation($data, $rules);
		$errors    = $validator->validate();

		return $errors;
	}

}

?>