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
 * @file         Verify.php
 */
class Register_Action_Verify extends Controller_Abstract
{

	public function execute ()
	{

		if ( !$this->isFrontend() )
		{
			return;
		}

		$this->Document->disableSiteCaching();

		// $this->Site->disableSiteCaching();


		if ( User::isLoggedIn() )
		{
			$this->Page->sendError(trans('Sie sind schon Registriert und Ihr Account ist schon aktiviert.'));
		}

		if ( !Settings::get('register.allowregister', false) )
		{
			$this->Page->sendError(trans('Die Registrierung ist leider nicht möglich.'));
		}

		$data    = array ();
		$account = trim((string)HTTP::input('key'));

		if ( $account !== '' || $this->_post('send') )
		{
			if ( empty($account) || preg_replace('/([^a-z0-9]*)/i', '', $account) !== $account )
			{
				$this->Page->sendError(trans('Fehlerhafter Aktivierungsschlüssel.'));
			}

			$user = $this->db->query("SELECT * FROM %tp%users WHERE activation = ?", $account)->fetch();

			if ( empty($user[ 'userid' ]) )
			{
				$this->Page->error(404, trans('Benutzer wurde vom System nicht gefunden!'));
			}

			if ( $user[ 'activation' ] == 1 )
			{
				$this->Page->sendError(trans('Du hast deinen Account schon freigeschaltet.'));
			}

			if ( $user[ 'activation' ] != $account || empty($user[ 'activation' ]) )
			{
				$this->Page->sendError(trans('Dein übergebener Aktivierungslink ist leider nicht korrekt. Bitte versuch es noch einmal oder wende dich an den Administrator.'));
			}


			if ( !$this->sendAccountActivationMail($user) )
			{
				throw new BaseException(trans('Die Aktivierung konnte nicht abgeschlossen werden. Sollte dieser Fehler erneut auftauchen, bitten wir Dich den Admin dieser Seite zu kontaktieren.'));
			}


			$this->db->query("UPDATE %tp%users SET activation = 1 WHERE userid = ?", $user[ 'userid' ]);
			Library::log(sprintf('User `%s` has account activated', $user[ 'username' ]));

			$data[ 'done' ] = true;

			if ( IS_AJAX )
			{
				Library::sendJson(true);
			}
			else
			{

				$this->Breadcrumb->add(trans('Registrierung abgeschlossen'), '');
			}
		}

		if ( !isset($data[ 'done' ]) )
		{
			$this->Breadcrumb->add(trans('Registrierung abschließen'), '');
		}

		$this->Template->process('register/register_activation', $data, true);
	}

	/**
	 * @param $data
	 * @return bool
	 */
	private function sendAccountActivationMail ( $data )
	{

		$subject = trans("Registrierung bei %s ist abgeschlossen");
		$subject = sprintf($subject, Settings::get('pagename'));

		$message = trans("Hallo %s,<br/><br/>dein Account wurde soeben bei uns freigeschalten.<br/>Wir wünschen Dir viel Spaß.<br/><br/>Mit freundlichem Gruß<br/>%s");
		$message = sprintf($message, $data[ 'username' ], Settings::get('pagename'));

		$m = new Mail(); // create the mail
		$m->mail_from(array (
		                    Settings::get('frommail'),
		                    Settings::get('pagename')
		              ));
		$m->mail_cc(array (
		                  Settings::get('frommail'),
		                  Settings::get('pagename')
		            ));
		$m->mail_to($data[ 'email' ]);
		$m->mail_subject(strip_tags($subject));

		$message = preg_replace('/<br\s*\/?\s*>/', "\n", $message);
		$m->mail_body($message); // set the body
		$m->mail_priority(2);
		$send = $m->send();

		return $send;
	}

}

?>