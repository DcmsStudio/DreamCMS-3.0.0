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
 * @package      Guestbook
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Add.php
 */
class Guestbook_Action_Add extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			$this->getFrontend();
		}
	}

	private function getFrontend ()
	{

		$username = (HTTP::input('username') ? strtolower(HTTP::input('username')) : null);

		if ( $username )
		{
			$user = User::getUserByUsername($username);


			if ( !$user[ 'userid' ] )
			{
				$this->Page->send404('Dieses Gästebuch wurde leider nicht gefunden.');
			}
		}

		$data = array ();

		if ( HTTP::post('send') )
		{
			$data = HTTP::post();
			unset($data[ 'send' ]);


			$errors = $this->validation($data);
			if ( count($errors) )
			{
				echo Library::json(array (
				                         'success' => false,
				                         'errors'  => $errors
				                   ));
				exit;
			}


			if ( isset($data[ 'username' ]) && trim($data[ 'username' ]) )
			{
				$user = User::getUserByUsername($data[ 'username' ]);

				if ( !$user[ 'userid' ] )
				{
					echo Library::json(array (
					                         'success' => false,
					                         'msg'     => 'Dieses Gästebuch wurde leider nicht gefunden.'
					                   ));
					exit;
				}

                if (Library::isBlacklistedUsername($data[ 'name' ]))
                {
                    Library::log('Blacklisted Username will post in "'.$user['username'].'" guestbook', 'warn');

                    // @todo add notifier message for the user or send a new private message the the user

                    Library::sendJson(false, trans('Blacklisted Username!'));
                }


				$dat = array (
					'user_gbid' => $user[ 'userid' ],
					'title'     => $data[ 'title' ],
					'message'   => $data[ 'message' ],
					'username'  => $data[ 'name' ] ? $data[ 'name' ] : '',
					'uid'       => User::getUserId(),
					'pageid'    => PAGEID,
					'ip'        => $this->Env->ip(),
					'timestamp' => time(),
					'email'     => $data[ 'email' ] ? $data[ 'email' ] : '',
					'homepage'  => $data[ 'userwebsite' ] ? $data[ 'userwebsite' ] : '',
				);


				$str = $this->db->compile_db_insert_string($dat);
				$sql = "INSERT INTO %tp%users_guestbook ({$str['FIELD_NAMES']}) VALUES({$str['FIELD_VALUES']})";
				$this->db->query($sql);


				$this->db->query('UPDATE %tp%users SET gbpostings=gbpostings+1 WHERE userid=' . $user[ 'userid' ]);

				User::subPostCounter();

				echo Library::json(array (
				                         'success' => true,
				                         'msg'     => sprintf(trans('Dein Eintrag wurde erfolgreich im Gästebuch von %s gespeichert.'), $user[ 'username' ])
				                   ));
				exit;
			}
			else
			{


                if (Library::isBlacklistedUsername($data[ 'name' ]))
                {
                    Library::log('Blacklisted Username will post in the guestbook', 'warn');
                    Library::sendJson(false, trans('Blacklisted Username!'));
                }

				$dat = array (
					'title'     => $data[ 'title' ],
					'message'   => $data[ 'message' ],
					'username'  => ($data[ 'name' ] ? $data[ 'name' ] : ''),
					'userid'    => User::getUserId(),
					'pageid'    => PAGEID,
					'ip'        => $this->Env->ip(),
					'timestamp' => TIMESTAMP,
					'email'     => ($data[ 'email' ] ? $data[ 'email' ] : ''),
					'homepage'  => ($data[ 'userwebsite' ] ? $data[ 'userwebsite' ] : ''),
				);
				$str = $this->db->compile_db_insert_string($dat);
				$sql = "INSERT INTO %tp%guestbook ({$str['FIELD_NAMES']}) VALUES({$str['FIELD_VALUES']})";
				$this->db->query($sql);

				User::subPostCounter();
			}


			echo Library::json(array (
			                         'success' => true,
			                         'msg'     => 'Ihr Eintrag wurde erfolgreich im Gästebuch gespeichert.'
			                   ));
			exit;
		}

		if ( $username && isset($user[ 'userid' ]) )
		{
			$this->load('Breadcrumb');

			$data['gbuser'] = $user;

			if ( strtolower(User::getUsername()) == strtolower($user[ 'username' ]) )
			{
				$this->Breadcrumb->add(trans('Dein Kontrollzentrum'), '/user/controlpanel');
				$this->Breadcrumb->add(trans('Mein Gästebuch'), '/guestbook/' . strtolower($username));
			}
			else
			{
				$this->Breadcrumb->add(sprintf(trans('Profil von %s'), $user[ 'username' ]), '/profile/' . $user[ 'userid' ]);
				$this->Breadcrumb->add(sprintf(trans('Gästebuch von %s'), $user[ 'username' ]), '/guestbook/' . strtolower($username));
			}
		}
		else
		{
			$this->load('Breadcrumb');
			$this->Breadcrumb->add(trans('Gästebuch'), '/guestbook');
		}

        $data[ 'bbcode_smilies' ]  = Json::encode($this->getSmilies());

		$this->Breadcrumb->add(trans('Eintrag schreiben'), '');
		$this->Document->setLayout('guestbook-index');
		$this->Template->process('guestbook/write', $data, true);
	}

	/**
	 * Validate the GB Post
	 *
	 * @param array $data
	 * @return array
	 */
	private function validation ( &$data )
	{

		$_ch = trim( $this->_post( '_ch' ) );

		$data['securecode'] = strtolower( $data['securecode'] );

		$rules                                = array ();
		$rules[ 'securecode' ][ 'required' ]  = array (
			'message' => trans('Sicherheitscode ist erforderlich'),
			'stop'    => true
		);
		$rules[ 'securecode' ][ 'identical' ] = array (
			'message' => trans('Sicherheitscode ist fehlerhaft'),
			'stop'    => true,
			'test'    => strtolower( Session::get('captcha-' .$_ch ) )
		);

		if ( !User::isLoggedIn() )
		{
			$rules[ 'name' ][ 'required' ]  = array (
				'message' => trans('Dein Name ist erforderlich'),
				'stop'    => true
			);
			$rules[ 'email' ][ 'required' ] = array (
				'message' => trans('Email-Adresse ist erforderlich'),
				'stop'    => true
			);
			$rules[ 'email' ][ 'email' ]    = array (
				'message' => trans('Email-Adresse ist nicht korrekt'),
				'stop'    => true
			);
		}

		$data[ 'title' ]                  = trim($data[ 'title' ]);
		$data[ 'title' ]                  = preg_replace('/\s{1,}/', ' ', $data[ 'title' ]);
		$rules[ 'title' ][ 'required' ]   = array (
			'message' => trans('Der Titel ist erforderlich'),
			'stop'    => true
		);
		$rules[ 'title' ][ 'min_length' ] = array (
			'message' => trans('Der Titel darf nicht leer sein. Mind. 5 Zeichen!'),
			'test'    => 5
		);


		$data[ 'message' ] = trim($data[ 'message' ]);
		$data[ 'message' ] = preg_replace('/\s{1,}/', ' ', $data[ 'message' ]);

		$rules[ 'message' ][ 'min_length' ] = array (
			'message' => sprintf(trans('Deine Nachricht muss mind. %s Zeichen lang sein'), 30),
			'test'    => 30
		);
		$rules[ 'message' ][ 'max_length' ] = array (
			'message' => sprintf(trans('Deine Nachricht darf max. %s Zeichen lang sein'), Settings::get('gbook_maxchars', 500)),
			'test'    => Settings::get('gbook_maxchars', 500)
		);

		$validator = new Validation($data, $rules);
		$errors    = $validator->validate();

		return $errors;
	}

}
