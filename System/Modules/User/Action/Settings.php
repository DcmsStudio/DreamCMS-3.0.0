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
 * @file         Settings.php
 */
class User_Action_Settings extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			if ( !User::isLoggedIn() )
			{
				$this->Page->error(403, trans('Sie sind nicht eingeloggt. Um diese Funktion nutzen zu können, loggen Sie sich bitte ein. Falls Sie eingeloggt sind, und dennoch diese Fehlermeldung erscheint, wenden Sie sich bitte an den Administrator.'));
			}

			// $this->Site->set( 'cacheable', false );

			$send = HTTP::post('send');
			$data = array ();

			$formmodel = Model::getModelInstance('forms');

			// read all Assigned fields
			$profilefields = $formmodel->getProfileFields();


			if ( !empty($send) )
			{
				// check form manipulation
				if ( Session::get('uiqtoken') != HTTP::post('uiqtoken') )
				{
					if ( IS_AJAX )
					{
						echo Library::json(array (
						                         'success' => false,
						                         'msg'     => 'Sorry your Request has a Invalid Token'
						                   ));
						exit;
					}


					$this->Page->sendError('Sorry your Request has a Invalid Token');
				}

				$data = $this->_post();
				unset($data[ 'send' ]);

				$data[ 'errors' ] = $this->model->validate($data, 'settings');


				$newValues = array ();
				foreach ( $profilefields as $field )
				{
					$field_error    = false;
					$f[ 'options' ] = !empty($field[ 'options' ]) ? unserialize($field[ 'options' ]) : false;

					$require    = ($field[ 'reg_required' ] || $f[ 'options' ][ 'controls' ] ? 1 : 0);
					$inputvalue = $this->_post('pf_' . $field[ 'profilefieldid' ]);

					/*
					  $class_name            = 'Field_' . ucfirst( strtolower( $field[ 'type' ] ) ) . 'Field';
					  $field[ 'attributes' ] = call_user_func( array( $class_name, 'getAttributes' ) );

					  $options = array( );
					  foreach ( $field[ 'attributes' ] as $attribute )
					  {
					  if ( !empty( $data[ $attribute ] ) )
					  {
					  $options[ $attribute ] = $data[ $attribute ];
					  }
					  }
					  $field[ 'options' ] = serialize( $options );

					  $field_data = call_user_func_array( array( $class_name, 'getFieldDefinition' ), array( $field ) );

					 */


					if ( $require )
					{
						if ( empty($inputvalue) )
						{
							$field_error                                               = true;
							$data[ 'errors' ][ 'pf_' . $field[ 'profilefieldid' ] ][ ] = sprintf(trans('Das Feld `%s` ist erforderlich'), ($f[ 'options' ][ 'grouplabel' ] ?
								$f[ 'options' ][ 'grouplabel' ] : $f[ 'options' ][ 'label' ]));
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
						'field_id' => $field[ 'field_id' ],
						'userid'   => User::getUserId()
					);
				}


				if ( count($data[ 'errors' ]) )
				{
					if ( IS_AJAX )
					{
						echo Library::json(array (
						                         'success' => false,
						                         'errors'  => $data[ 'errors' ]
						                   ));
						exit;
					}
				}
				else
				{


					$formmodel->deleteProfileFieldData(User::getUserId());

					/**
					 * Save Profilfields
					 */
					foreach ( $newValues as $rs )
					{
						$formmodel->saveFieldData($rs);
					}


					$arr = array (
						'timezoneoffset' => ($data[ 'timezone' ] ? (string)$data[ 'timezone' ] :
								User::get('timezoneoffset')),
						'usertext'       => $data[ 'usertext' ],
						'name'           => $data[ 'name' ],
						'lastname'       => $data[ 'lastname' ],
						'street'         => $data[ 'street' ],
						'zip'            => (string)$data[ 'zip' ],
						'user_from'      => $data[ 'user_from' ],
						'country'        => $data[ 'country' ],
						'msn'            => $data[ 'msn' ],
						'aim'            => $data[ 'aim' ],
						'yim'            => $data[ 'yim' ],
						'icq'            => $data[ 'icq' ],
						'skype'          => $data[ 'skype' ]
					);

					$str = $this->db->compile_db_update_string($arr);
					$sql = "UPDATE %tp%users SET " . $str . " WHERE userid = " . User::getUserId();
					$this->db->query($sql);

					if ( IS_AJAX )
					{
						echo Library::json(array (
						                         'success' => true,
						                         'msg'     => trans('Deine Einstellungen wurde erfolgreich aktualisiert.')
						                   ));
						exit;
					}

					$data         = array ();
					$data[ 'ok' ] = true;
				}
			}


			$data                    = array ();
			$_user_profilefield_data = $formmodel->getProfileFieldData(User::getUserID());
			$user_profilefield_data  = array ();
			foreach ( $_user_profilefield_data as $r )
			{
				$user_profilefield_data[ $r[ 'field_id' ] ] = $r;
			}

			/**
			 * @todo Better Profilefield handling!
			 */
			foreach ( $profilefields as $f )
			{
				$f[ 'options' ] = (!empty($f[ 'options' ]) ? unserialize($f[ 'options' ]) : false);

				$field         = array ();
				$field         = $f;
				$field[ 'id' ] = 'pf_' . $field[ 'profilefieldid' ];

				$field[ 'value' ] = isset($user_profilefield_data[ $field[ 'field_id' ] ][ 'value' ]) ?
					$user_profilefield_data[ $field[ 'field_id' ] ][ 'value' ] : '';

				$field[ 'options' ]  = !empty($field[ 'options' ]) ? serialize($field[ 'options' ]) : false;
				$field[ 'controls' ] = false;

				if ( $f[ 'options' ] === false )
				{
					$attributes = Field::getFieldAttributes($field[ 'type' ]);
					$options    = array ();
					foreach ( $attributes as $attribute )
					{
						if ( !empty($user_profilefield_data[ $field[ 'field_id' ] ][ $attribute ]) )
						{
							$options[ $attribute ] = $user_profilefield_data[ $field[ 'field_id' ] ][ $attribute ];
						}
					}

					$field[ 'required' ] = ($f[ 'options' ][ 'controls' ] ? 1 : 0);
					$field[ 'controll' ] = $field[ 'required' ];

					$field[ 'options' ] = serialize($options);
				}
				else
				{
					$field[ 'required' ] = ($f[ 'options' ][ 'controls' ] ? 1 : 0);
					$field[ 'controll' ] = $field[ 'required' ];
				}

				$field_data                    = Field::getFieldDefinition($field);
				$field_data[ 'required' ]      = $field[ 'required' ];
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


			$this->Breadcrumb->add(trans('Dein Kontrollzentrum'), '/user/controlpanel');
			$this->Breadcrumb->add(trans('Einstellungen ändern'), '');


			$timezones = Library::getTimezones();
			foreach ( $timezones as $timeset => $title )
			{
				$data[ 'timezones' ][ ] = array (
					'value' => (string)$timeset,
					'title' => $title
				);
			}

			Session::save('uiqtoken', Library::UUIDv4());


			$data[ 'countries' ] = $this->db->query('SELECT tld, country AS name FROM %tp%countries ORDER BY country')->fetchAll();

			//$this->Site->disableSiteCaching();
			$this->Template->process('usercontrol/change_settings', $data, true);
			exit;
		}
	}

}
