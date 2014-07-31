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
 * @package      Auth
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Lostpassword.php
 */
class Auth_Action_Lostpassword extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			$data = array ();
			if ( $this->_post('send') )
			{
				$post = $this->_post();

				if ( empty($post[ 'email' ]) && empty($post[ 'username' ]) )
				{
					$data[ 'error' ] = trans('Bitte geben Sie Ihre Email oder aber Ihren Benutzernamen ein!');

					if ( IS_AJAX )
					{
						Library::sendJson(false, $data[ 'error' ]);
					}
				}
				else
				{

					if ( $post[ 'email' ] )
					{
						if ( !Validation::isValidEmail($post[ 'email' ]) )
						{
							$data[ 'error' ] = trans('Die von Ihnen eingegebene Email ist nicht korrekt.');

							if ( IS_AJAX )
							{
								Library::sendJson(false, $data[ 'error' ]);
							}
						}
						else
						{
							$user = User::getUserByEmail($post[ 'email' ]);


							if ( !$user[ 'userid' ] )
							{
								$data[ 'error' ] = trans('Die von Ihnen eingegebene Email existiert nicht in unserer Datenbank.');

								if ( IS_AJAX )
								{
									Library::sendJson(false, $data[ 'error' ]);
								}
							}
							else
							{
								$originalPassword = $user[ 'password' ];

								if ( !$this->sendEmail($user) )
								{
									$data[ 'error' ] = trans('Der Account-Reset konnte nicht abgeschlossen werden. Sollte dieser Fehler erneut auftauchen, bitten wir Dich den Admin dieser Seite zu kontaktieren.');
									$this->model->resetUserPassword($user[ 'userid' ], $originalPassword);
								}
								else
								{
									$data[ 'mailsend' ] = true;
								}
							}
						}
					}


					if ( $post[ 'username' ] && !isset($data[ 'mailsend' ]) )
					{
						$user = User::getUserByUsername($post[ 'username' ]);

						if ( !$user[ 'userid' ] )
						{
							$data[ 'error' ] = trans('Der von Ihnen eingegebene Benutzer existiert nicht in unserer Datenbank.');

							if ( IS_AJAX )
							{
								Library::sendJson(false, $data[ 'error' ]);
							}
						}
						else
						{
							$originalPassword = $user[ 'password' ];

							if ( !$this->sendEmail($user) )
							{
								$data[ 'error' ] = trans('Der Account-Reset konnte nicht abgeschlossen werden. Sollte dieser Fehler erneut auftauchen, bitten wir Dich den Admin dieser Seite zu kontaktieren.');
								$this->model->resetUserPassword($user[ 'userid' ], $originalPassword);
							}
							else
							{
								$data[ 'mailsend' ] = true;
							}
						}
					}
				}
			}

			$this->Template->process('globals/lostpassword', $data, true);
		}
	}

	/**
	 * Will create a new random password and crypt it.
	 * Then saving in the Database.
	 *
	 * @param array $userdata
	 * @return string the plain text password
	 */
	protected function generateTempPassword ( $userdata )
	{

		$newPw = Library::getRandomChars(8, false);
		$this->model->resetUserPassword($userdata[ 'userid' ], $newPw);

		return $newPw;
	}

	/**
	 * @param $userdata
	 * @return bool
	 */
	protected function sendEmail ( $userdata )
	{

		$newPw = $this->generateTempPassword($userdata);

		$subject = sprintf(trans('Dein %s Account wurde zurückgesetzt'), Settings::get('pagename'));

		$m = new Mail(); // create the mail
		$m->mail_from(array (
		                    Settings::get('frommail'),
		                    Settings::get('pagename')
		              ));
		#$m->mail_cc( Settings::get( 'frommail' ) );
		$m->mail_to($userdata[ 'email' ]);
		$m->mail_subject(strip_tags($subject));

		$message = trans('Hallo %s,<br/><br/>Dein Account wurde durch die IP: %s (Hostname: %s) zurückgesetzt.<br/>Dein neues Passwort lautet: %s<br/><br/>Wir wünschen Dir viel Spaß.<br/><br/>Mit freundlichem Gruß<br/>%s');
		$message = sprintf($message, $userdata[ 'username' ], $this->Env->ip(), $this->Env->getUserHost(), $newPw, Settings::get('pagename'));
		$message = preg_replace('/<br\s*\/?\s*>/', "\n", $message);

		$m->mail_body($message); // set the body
		$m->mail_priority(1);

		return $m->send();
	}

}
