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
 * @package      Register
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Register_Action_Index extends Controller_Abstract
{

	/**
	 * filter actions
	 * @var array
	 */
	protected $_invalidUsernames = array('action', 'adm', 'cp', 'edit', 'delete', 'remove', 'publish', 'submit', 'vote', 'rate');

	public function execute ()
	{

		if ( !$this->isFrontend() )
		{
			return;
		}


		if ( Session::get('reg-done') || User::isLoggedIn() )
		{
			$this->Page->sendError(trans('Sie haben sich schon Registriert.'));
		}

		if ( !Settings::get('register.allowregister', false) )
		{
			$this->Page->sendError(trans('Die Registrierung ist leider nicht möglich.'));
		}


		$send = $this->_post('send');

		if ( !empty($send) )
		{
			$data = $this->_post();
			unset($data[ 'send' ]);
			/*
			  $valid = Validation::isValidUsername($data['username']);
			  if ( !$valid )
			  {
			  echo Library::json(array('success' => false, 'msg' => trans('Der von Ihnen angegebene Benutzername ist nicht korrekt.') ));
			  exit;
			  }
			 */

			$errors = $this->validate($data);

			if ( !count($errors) )
			{
				$this->load('MailChecker');


				if ( $this->MailChecker->isTrashMail($data[ 'email' ]) )
				{
					$errors[ 'email' ][ ] = trans('Es sind keine Temporären Email Adressen erlaubt!');
				}
				else
				{
                    /*
                     *
                     * @todo produziert fehler!
                     *
                     *
					$this->MailChecker->setEmailFrom(Settings::get('frommail'));
					$this->MailChecker->setConnectionTimeout(10);
					if ( !$this->MailChecker->check($data[ 'email' ]) )
					{
						$errors[ 'email' ][ ] = trans('Diese Email Adresse existiert nicht!');
					}

                    */
				}


				if (in_array(strtolower($data[ 'username' ]), $this->_invalidUsernames))
				{
					$errors[ 'username' ][ ] = trans('Dieser Benutzername darf nicht verwendet werden');
				}
			}

            if (Library::isBlacklistedUsername($data[ 'username' ])) {
                Library::log('Blacklisted Username will register new Account', 'warn');
                $errors[ 'username' ][ ] = trans('Dieser Benutzername darf nicht verwendet werden');
            }

			if ( count($errors) )
			{
				if ( IS_AJAX )
				{
                    Ajax::Send(false, array (
                        'success' => false,
                        'errors'  => $errors
                    ));
                    exit;
				}
				else
				{

					$errorMessages = array ();
					foreach ( $errors as $fieldname => $row )
					{
						if ( is_array($row) )
						{
							foreach ( $row as $errorMsg )
							{
								$errorMessages[ ] = array (
									'message' => $errorMsg,
									'field'   => $fieldname
								);
							}
						}
					}

					$data[ 'errors' ] = $errorMessages;
				}
			}
			else
			{
				$phpass     = new PasswordHash(true);
				$activation = 1;

				$mode = (int)Settings::get('register.emailverifymode', 0);


				if ( $mode == 1 )
				{
					// Email mit einem Link mit dem er seinen Account Aktivieren muss
					$activation           = Library::getRandomChars(12);
					$data[ 'activation' ] = $activation;
					$password             = $phpass->HashPassword($data[ 'password' ]);
				}
				elseif ( $mode == 2 )
				{
					// Benutzer soll vorher erst geprüft werden
					$activation = 0; // activate manuell
					$password   = $phpass->HashPassword($data[ 'password' ]);
				}
				elseif ( $mode == 3 )
				{
					// Email mit einem generierten Passwort an Benutzer senden (sofort Freischalten)
					$data[ 'password' ] = Library::getRandomChars(8);
					$password           = $phpass->HashPassword($data[ 'password' ]);
				}
				else
				{

					if ( !empty($data[ 'password' ]) )
					{
						$password = $phpass->HashPassword($data[ 'password' ]);
					}
					else
					{
						$data[ 'password' ] = Library::getRandomChars(8);
						$password           = $phpass->HashPassword($data[ 'password' ]);
					}

					$data[ 'activation' ] = 1;
				}


				// get the default usergroup
				$group  = $this->db->query('SELECT * FROM %tp%users_groups WHERE default_usergroup=1 AND grouptype = ?', 'default')->fetch();
				$gender = (int)$data[ 'gender' ];


				/**
				 *
				 * @todo in the next version will choose the registration sql querys to the model class
				 *
				 */
				$sql  = "SELECT rankid FROM %tp%users_ranks WHERE (groupid = 4 AND needposts = 0) " . ($gender ?
						" AND gender=" . $gender : " AND gender=0") . " ORDER BY needposts ASC LIMIT 1";
				$rank = $this->db->query($sql)->fetch();


				$dat = array (
					'username'             => $data[ 'username' ],
					'password'             => $password,
					'email'                => $data[ 'email' ],
					'groupid'              => 4,
					'rankid'               => (int)$rank[ 'rankid' ],
					'regdate'              => time(),
					'lastvisit'            => 0,
					'lastactivity'         => 0,
					'gender'               => (int)$gender,
					'showemail'            => 0,
					'admincanemail'        => 1,
					'usercanemail'         => 0,
					'activation'           => (string)$activation,
					'dateformat'           => Settings::get('dateformat'),
					'timeformat'           => Settings::get('timeformat'),
					'emailnotify'          => 0,
					'receivepm'            => 1,
					'emailonpm'            => 1,
					'pmpopup'              => 0,
					'nosessionhash'        => 0,
					'timezoneoffset'       => (string)$data[ 'timezone' ],
					'usecookies'           => 1,
					'usertext'             => '',
					'signature'            => '',
					'buddylist'            => '',
					'ignorelist'           => '',
					'lastpost_section'     => '',
					'lastpost_url'         => '',
					'lastpost_url_caption' => '',
					'language'             => '',
					'uniqidkey'            => '',
					'icq'                  => '',
					'msn'                  => '',
					'aim'                  => '',
					'yim'                  => '',
					'skype'                => '',
					'homepage'             => '',
					'userblock'            => '',
					'country'              => '',
					'phone'                => '',
					'mobile_phone'         => '',
					'fax'                  => '',
					'name'                 => '',
					'lastname'             => '',
					'zip'                  => '',
					'street'               => '',
					'user_from'            => '',
					'company_name'         => '',
					'ustid'                => '',
					'company'              => '',
					'failed_logins'        => 0,
					'blocked_until'        => 0, /*
                          'username' => $db->esc(htmlspecialchars($r_username)),
                          'password' => md5($r_password),
                          'email' => $db->esc($r_email),
                          'groupid' => $groupid,
                          'rankid' => $rank,
                          'regdate' => time(),
                          'lastvisit' => time(),
                          'lastactivity' => time(),
                          'usertext' => $db->esc(htmlspecialchars($r_usertext)),
                          'signature' => $db->esc($r_signature),
                          'icq' => (int)$r_icq,
                          'aim' => $db->esc(htmlspecialchars($r_aim)),
                          'yim' => $db->esc(htmlspecialchars($r_yim)),
                          'msn' => $db->esc(htmlspecialchars($r_msn)),
                          'homepage' => $db->esc(htmlspecialchars($r_homepage)),
                          'birthday' => $db->esc(htmlspecialchars($birthday)),
                          'gender' => (int)$r_gender,
                          'showemail' => 1,
                          'admincanemail' => 1,
                          'usercanemail' => 0,
                          'invisible' => (int)$r_invisible,
                          'usecookies' => (int)$cfg['default_register_usecookies'],
                          'styleid' => $defaultstyle,
                          'activation' => (int)$activation,
                          'timezoneoffset' => $db->esc(addslashes($cfg['default_timezoneoffset'])),
                          'startweek' => (int)$cfg['default_startweek'],
                          'dateformat' => $db->esc(htmlspecialchars($cfg['dateformat'])),
                          'timeformat' => $db->esc(htmlspecialchars($cfg['timeformat'])),
                          'emailnotify' => 0,
                          'receivepm' => 1,
                          'emailonpm' => 1,
                          'pmpopup' => 0,
                          'nosessionhash' => 0,
                         *
                         */
				);

				#Library::sendJson(false, print_r($dat,true));


				$str = $this->db->compile_db_insert_string($dat);
				$sql = "INSERT INTO %tp%users ({$str['FIELD_NAMES']}) VALUES({$str['FIELD_VALUES']})";
				$this->db->query($sql);


				$data[ 'userid' ] = $this->db->insert_id();

				// Send Email
				if ( !$this->sendRegMail($data) )
				{
					// @tody Rollback
					$this->db->query('DELETE FROM %tp%users WHERE userid = ' . $data[ 'userid' ]);
					Library::log('Register Mail error!', 'warn');

					throw new BaseException(trans('Die Registrierung konnte nicht abgeschlossen werden. Sollte dieser Fehler erneut auftauchen, bitten wir Dich den Admin dieser Seite zu kontaktieren.'));

					exit;
				}


				#echo Library::json(array('success' => false, 'msg' => $message));
				#exit;

				switch ( $mode )
				{

					// 0 keine Überprüfung - sofortige Freischaltung
					// 1 Aktivierungscode verschicken
					// 2 manuelle Freischaltung
					// 3 zufallsgeneriertes Passwort verschicken

					case 1:
					case '1':
						$message = trans("Vielen Dank für dein Registrierung.<br/>Sie erhalten nun eine Email mit einem Link mit dem Sie Ihren Account aktivieren müssen.");

						break;

					case 2:
					case '2':
						$message = trans("Vielen Dank für deine Registrierung.<br/>Ihr Account wird nun geprüft und anschließend von uns freigeschalten.");

						break;

					case 3:
					case '3':
						$message = trans("Vielen Dank für deine Registrierung.<br/>Sie erhalten nun eine Email mit Ihrem Passwort mit diesem können Sie sich dann hier einloggen.");


						break;

					case 0:
					case '0':

						$message = trans("Vielen Dank für deine Registrierung.<br/>Dein Account ist ab sofort freigeschalten.");
						User::login($data[ 'username' ], $data[ 'password' ]);
						break;
				}


				Session::save('reg-done', true);

				Library::log(sprintf('User `%s` has registred', $data[ 'username' ]));

				$data[ 'done_message' ] = $message;


				if ( IS_AJAX )
				{

                    Ajax::Send(true, array (
                        'success' => true,
                        'msg'     => $message
                    ));

					exit;
				}
			}
		}

		$timezones = Locales::getTimezones();
		foreach ( $timezones as $timeset => $title )
		{
			$data[ 'timezones' ][ ] = array (
				'value' => $timeset,
				'label' => $title
			);
		}


		$this->Breadcrumb->add(trans('Registrierung'), '');


		$this->Template->addScript('Modules/' . CONTROLLER . '/assets/js/dcms.register.js');
		$this->Template->process('register/index', $data, true);
		exit;
	}

	/**
	 * Validate Fields
	 *
	 * @param array $data input
	 * @internal param int $appid the Application ID
	 * @internal param string $itemtype the Item Type of the Application
	 * @return array of errors
	 */
	public function validate ( $data )
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
		$rules[ 'username' ][ 'unique' ]     = array (
			'message'        => trans('Der Benutzername existiert schon und kann daher nicht verwendet werden'),
			'uservalidation' => true,
			'table'          => 'users',
			'id_field'       => 'username'
		);


		$rules[ 'username' ][ 'regex' ]     = array (
			'message'        => trans('Der Benutzername darf alle Zeichen, außer den Zeichen "*+\'"&lt;&gt;/\" enthalten'),
			'regex'          => '([^\'"><\*\+/\\]*)'
		);


		$rules[ 'email' ][ 'required' ]        = array (
			'message' => trans('Email-Adresse ist erforderlich'),
			'stop'    => true
		);
		$rules[ 'emailconfirm' ][ 'required' ] = array (
			'message' => trans('Bestätigungs Email-Adresse ist erforderlich'),
			'stop'    => true
		);

		$rules[ 'email' ][ 'email' ]        = array (
			'message' => trans('Email-Adresse ist nicht korrekt'),
			'stop'    => true
		);
		$rules[ 'emailconfirm' ][ 'email' ] = array (
			'message' => trans('Bestätigungs Email-Adresse ist nicht korrekt'),
			'stop'    => true
		);
		$rules[ 'email' ][ 'identical' ]    = array (
			'message' => trans('Bestätigungs Email-Adresse ist nicht identisch ihrer Email Adresse'),
			'stop'    => true,
			'test'    => $data[ 'emailconfirm' ]
		);


		if ( !Settings::get('register.multipleemailuse') )
		{
			$rules[ 'email' ][ 'unique' ] = array (
				'message'        => trans('Der Email existiert schon und kann daher nicht verwendet werden'),
				'uservalidation' => true,
				'table'          => 'users',
				'id_field'       => 'email'
			);
		}

		if ( Settings::get('register.emailverifymode') != 3 )
		{
			$rules[ 'password' ][ 'required' ]        = array (
				'message' => trans('Passwort ist erforderlich'),
				'stop'    => true
			);
			$rules[ 'password' ][ 'min_length' ]      = array (
				'message' => sprintf(trans('Dein Passwort muss mind. %s Zeichen lang sein'), Settings::get('minuserpasswordlength', 3)),
				'test'    => Settings::get('minuserpasswordlength', 3)
			);


			// Passwords should never be longer than 40 characters to prevent DoS attacks
			$rules[ 'password' ][ 'max_length' ]      = array (
				'message' => sprintf(trans('Dein Passwort darf maximal %s Zeichen lang sein'), 40),
				'test'    => 40
			);


			$rules[ 'password' ][ 'nostars' ] = array (
				'message' => trans('Passwörter dürfen das Zeichen "*" nicht enthalten'),
				'stop'    => true,
				'test'    => $data[ 'passwordconfirm' ]
			);

			$rules[ 'passwordconfirm' ][ 'required' ] = array (
				'message' => trans('Bestätigungs Passwort ist erforderlich'),
				'stop'    => true
			);
			$rules[ 'password' ][ 'identical' ]       = array (
				'message' => trans('Passwörter sind nicht identisch'),
				'stop'    => true,
				'test'    => $data[ 'passwordconfirm' ]
			);
		}

		$rules[ 'securecode' ][ 'required' ]  = array (
			'message' => trans('Sicherheitscode ist erforderlich'),
			'stop'    => true
		);
		$rules[ 'securecode' ][ 'identical' ] = array (
			'message' => trans('Sicherheitscode ist fehlerhaft'),
			'stop'    => true,
			'test'    => strtolower(Session::get('site_captcha'))
		);

		$rules[ 'accept' ][ 'required' ] = array (
			'message' => trans('Sie müssen mit unseren Bedingungen einverstanden sein, um sich bei uns Registrieren zu können'),
			'stop'    => true
		);

		$data[ 'securecode' ] = strtolower($data[ 'securecode' ]);

		$validator = new Validation($data, $rules);
		$errors    = $validator->validate();

		return $errors;
	}

	/**
	 *
	 * @param array $data the userdata array
	 * @return boolean returns false if mail is not send
	 */
	protected function sendRegMail ( $data )
	{

		$subject = sprintf(trans('Registrierung bei %s'), Settings::get('pagename'));

		$m = new Mail(); // create the mail
		$m->mail_from(array (
		                    Settings::get('frommail'),
		                    Settings::get('pagename')
		              ));
		$m->mail_cc(array (
		                  Settings::get('frommail'),
		                  Settings::get('pagename')
		            ));
		$m->mail_to(array (
		                  $data[ 'email' ],
		                  $data[ 'username' ]
		            ));
		$m->mail_subject(strip_tags($subject));

		$mode = (int)Settings::get('register.emailverifymode', 0);

		switch ( $mode )
		{

			// 0 keine Überprüfung - sofortige Freischaltung
			// 1 Aktivierungscode verschicken
			// 2 manuelle Freischaltung
			// 3 zufallsgeneriertes Passwort verschicken

			case 1:
			case '1':
				$message = trans('Hallo %s,<br/><br/>Vielen Dank für deine Registrierung bei %s.<br/><br/>Im nächsten Schritt klicke bitte auf den nachfolgenden Link, um deinen Account zu aktivieren.<br/>Aktivierungslink: <a href="%s">aktivieren</a> - %s<br/>Oder gehen Sie zur Seite: %s und geben die den Aktivierungscode: %s ein.<br/><br/>Mit freundlichem Gruß<br/>%s');
				$message = sprintf($message, $data[ 'username' ], Settings::get('pagename'), Settings::get('portalurl') . '/register/verify/' . $data[ 'activation' ], Settings::get('portalurl') . '/register/verify/' . $data[ 'activation' ], Settings::get('portalurl') . '/register/verify', $data[ 'activation' ], Settings::get('pagename'));
				break;

			case 2:
			case '2':
				$message = trans('Hallo %s,<br/><br/>Vielen Dank für deine Registrierung bei %s.<br/>Ihr Account wird nun geprüft und anschließend von uns freigeschalten.<br/><br/>Mit freundlichem Gruß<br/>%s');
				$message = sprintf($message, $data[ 'username' ], Settings::get('pagename'), Settings::get('pagename'));
				break;

			case 3:
			case '3':
				$message = trans('Hallo %s,<br/><br/>Vielen Dank für deine Registrierung bei %s.<br/><br/>Dies ist Ihr Passwort: %s<br/>Mit diesem Passwort können Sie sich ab sofort bei uns einloggen. Wir empfehlen Ihnen aber dennoch, das Passwort am besten sofort zu ändern.<br/><br/>Mit freundlichem Gruß<br/>%s');
				$message = sprintf($message, $data[ 'username' ], Settings::get('pagename'), $data[ 'password' ], Settings::get('pagename'));

				break;

			case 0:
			case '0':

				$message = trans('Hallo %s,<br/><br/>dein Account wurde soeben bei uns freigeschalten.<br/>Wir wünschen Dir viel Spaß.<br/><br/>Mit freundlichem Gruß<br/>%s');
				$message = sprintf($message, $data[ 'username' ], Settings::get('pagename'));
				break;
		}

		//  $message = preg_replace( '/<br\s*\/?\s*>/', "\n", $message );
		$m->mail_body($message); // set the body
		$m->mail_priority(2);
		$send = $m->send();

		return $send;
	}

}

?>