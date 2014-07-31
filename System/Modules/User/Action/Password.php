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
 * @file         Password.php
 */
class User_Action_Password extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			if ( !User::isLoggedIn() )
			{
				$this->Page->error(403, trans('Sie sind nicht eingeloggt. Um diese Funktion nutzen zu können, loggen Sie sich bitte ein. Falls Sie eingeloggt sind, und dennoch diese Fehlermeldung erscheint, wenden Sie sich bitte an den Administrator.'));
			}

			//$this->Site->set( 'cacheable', false );

			$send = $this->_post('send');
			$data = array ();


			if ( !empty($send) )
			{

				// check form manipulation
				if ( Session::get('uiqtoken') != $this->_post('uiqtoken') )
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

				$data[ 'errors' ] = $this->model->validate($data, 'password');

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
					// crypt the old password input before test
					// $data[ 'password' ] = md5( $data[ 'password' ] );
					//
					//
					// Password Hash Strength is default 16
					$phpass             = new PasswordHash(16, true);
					$data[ 'password' ] = $phpass->HashPassword($data[ 'password' ]); // get the hash password

					$this->db->query('UPDATE %tp%users SET password = ? WHERE userid = ?', $data[ 'password' ], User::getUserId());

					if ( IS_AJAX )
					{
						Session::save('password', $data[ 'password' ]);
						Session::write();

						echo Library::json(array (
						                         'success' => true,
						                         'msg'     => trans('Dein Passwort wurde erfolgreich aktualisiert.')
						                   ));
						exit;
					}


					Session::save('password', $data[ 'password' ]);

					$data         = array ();
					$data[ 'ok' ] = true;
				}
			}

			$this->Breadcrumb->add(trans('Dein Kontrollzentrum'), '/user/controlpanel');
			$this->Breadcrumb->add(trans('Passwort ändern'), '');


			Session::save('uiqtoken', Library::UUIDv4());


			//$this->Site->disableSiteCaching();
			$this->Template->process('usercontrol/change_password', $data, true);
		}
	}

}
