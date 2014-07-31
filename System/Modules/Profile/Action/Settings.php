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
 * @package      Profile
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Settings.php
 */
class Profile_Action_Settings extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			if ( !User::isLoggedIn() )
			{
				$this->Page->sendError(trans('Sie sind nicht eingeloggt. Um diese Funktion nutzen zu können, loggen Sie sich bitte ein. Falls Sie eingeloggt sind, und dennoch diese Fehlermeldung erscheint, wenden Sie sich bitte an den Administrator.'));
			}

			// $this->Site->set( 'cacheable', false );

			$send = HTTP::post('send');
			$data = array ();

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

				$data = HTTP::post();
				unset($data[ 'send' ]);

				$data[ 'errors' ] = $this->model->validate($data, 'settings');
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


			$this->Breadcrumb->add(trans('Dein Kontrollzentrum'), '/profile');
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
			//Session::write();

			$data[ 'countries' ] = $this->db->query('SELECT tld, country AS name FROM %tp%countries ORDER BY country')->fetchAll();

			//$this->Site->disableSiteCaching();
			$this->Template->process('usercontrol/change_settings', $data, true);
			exit;
		}
	}

}
