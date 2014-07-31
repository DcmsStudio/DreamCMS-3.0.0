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
 * @file         Index.php
 */
class Guestbook_Action_Index extends Controller_Abstract
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

		$this->load('Breadcrumb');
		$username = (HTTP::input('username') ? HTTP::input('username') : null);
		$data = array ();

	#	print_r($this->input());exit;

		if ( $username !== null )
		{
			$user = User::getUserByUsername($username);

			// allow private Guestbook?
			if ( !User::Allowed('user/userguestbook', $user, false) )
			{
				$this->Page->error(null, sprintf(trans('Der Benutzer `%s` darf kein eigenes Gästebuch benutzen.'), $user[ 'username' ]));
			}

			if ( strtolower(User::getUsername()) == strtolower($user[ 'username' ]) )
			{
				$this->Breadcrumb->add(trans('Dein Kontrollzentrum'), '/user/controlpanel');
				$this->Breadcrumb->add(trans('Mein Gästebuch'));
			}
			else
			{
				$this->Breadcrumb->add(sprintf(trans('Profil von %s'), $user[ 'username' ]), '/profile/' . $user[ 'userid' ]);
				$this->Breadcrumb->add(sprintf(trans('Gästebuch von %s'), $user[ 'username' ]));
			}


			$_result = $this->model->getUserGbData($user[ 'userid' ]);

			$data['gbuser'] = $user;

		}
		else
		{
			$this->Breadcrumb->add(trans('Gästebuch'));
			$_result = $this->model->getGbData();
		}


		$limit = Settings::get('guestbook.perpage', 10);


		static $_cache;
		foreach ( $_result[ 'result' ] as $r )
		{
			if ( Settings::get('guestbook.allowbbcode') )
			{
				$r[ 'message' ] = BBCode::toXHTML($r[ 'message' ]);

			}

			if ( $r[ 'userid' ] )
			{
				if ( !is_array($_cache[ $r[ 'userid' ] ]) )
				{
					$_cache[ $r[ 'userid' ] ] = User::getUserById($r[ 'userid' ]);
				}
				$r[ 'author' ]                = $_cache[ $r[ 'userid' ] ];
				$r[ 'author' ][ 'userphoto' ] = User::getUserPhoto($r[ 'author' ]);
			}

			$data[ 'posts' ][ ] = $r;
		}


		if ( $_result[ 'total' ] > 0 )
		{
			$page  = (int)$this->input('page') ? (int)$this->input('page') : 1;
			$pages = ceil($_result[ 'total' ] / $limit);

			$this->load('Paging');
			$url = $this->Paging->generate(array (
			                                     'username' => $username
			                               ));
			$this->Paging->setPaging($url, $page, $pages);
		}


		$this->Template->process('guestbook/index', $data, true);
	}

}
