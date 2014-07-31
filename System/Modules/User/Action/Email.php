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
 * @file         Email.php
 */
class User_Action_Email extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$id         = (int)HTTP::input('userid');
		$multiusers = HTTP::input('ids');

		$userids = null;
		if ( !empty($multiusers) )
		{
			$userids = Library::unempty(explode(',', $multiusers));
		}

		if ( is_null($userids) && $id > 0 )
		{
			$userids = array (
				$id
			);
		}
		elseif ( HTTP::input('userid') == "all" )
		{
			$userids = "all";
		}

		if ( !isset($userids) || empty($userids) )
		{
			Error::raise(trans("Sie haben keinen Benutzer ausgewählt."));
		}

		$data            = array ();
		$data[ 'users' ] = $this->model->getUsersForMailing(($userids != "all" ? $userids : true));

		if ( !count($data[ 'users' ]) )
		{
			Error::raise(trans("Sie haben keinen Benutzer ausgewählt oder aber der Benutzer existiert nicht."));
		}

		if ( HTTP::input('send') )
		{
			demoadm();

			$data[ 'post' ] = HTTP::input();

			$this->sendMails($data);
			Library::sendJson(true, ($userids !== true ?
				trans('Die Email wurde an alle ausgewählten Benutzer gesendet.') :
				trans('Die Email wurde an alle registrierten Benutzer gesendet.')));

			exit;
		}

		$users = array ();

		if ( $userids !== "all" )
		{
			if ( !count($data[ 'users' ]) )
			{
				Error::raise(trans("Sie haben keinen Benutzer ausgewählt oder aber der Benutzer existiert nicht."));
			}
			else
			{
				foreach ( $data[ 'users' ] as $r )
				{
					$users[ ] = $r[ 'username' ];
				}
			}
		}

		$data[ 'userids' ] = implode(',', $userids);

		Library::addNavi(trans('Benutzer Übersicht'));
		Library::addNavi((count($users) ? sprintf(trans('Email an `%s` verschicken'), implode(', ', $users)) :
			trans('Email an alle Benutzer verschicken')));
		$this->Template->process('users/users_email', $data, true);
		exit;
	}

	/**
	 * @param $data
	 */
	private function sendMails ( $data )
	{

		$subject = strip_tags($data[ 'post' ][ 'subject' ]);
		$message = $data[ 'post' ][ 'message' ];
		$message = preg_replace('/<br\s*\/?\s*>/', "\n", $message);


		$errors = 0;
		foreach ( $data[ 'users' ] as $r )
		{

			$subject = str_replace('[username]', $r[ 'username' ], $subject);
			$message = str_replace('[username]', $r[ 'username' ], $message);

			$m = new Mail(); // create the mail
			$m->mail_from(array (
			                    Settings::get('frommail'),
			                    Settings::get('pagename')
			              ));
			$m->mail_to($r[ 'email' ]);
			$m->mail_subject($subject);
			$m->mail_body($message); // set the body
			$send = $m->send();

			if ( !$send )
			{
				Library::log(sprintf('Can´t send mail to %s (%s)!', $r[ 'username' ], $r[ 'email' ]), 'warn');
				$errors++;
			}
		}

		if ( count($data[ 'users' ]) == $errors )
		{
			Library::sendJson(false, trans('Die Email konnte an keinen der Benutzer versendet werden. Bitte prüfen Sie ihre Email Einstellungen.'));
		}

		if ( $errors )
		{
			Library::sendJson(false, trans('Die Email konnte nicht an alle Benutzer versendet werden.'));
		}
	}

}

?>