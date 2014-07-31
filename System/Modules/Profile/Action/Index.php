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
 * @file         Index.php
 */
class Profile_Action_Index extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			$this->load('Page');

			$userid = (int)$this->input('userid');
			if ( !$userid )
			{
				$username = $this->input('username');
				$userdata = $this->model->getUserByUsername($username);
			}
			else
			{
				$userdata = $this->model->getUserById($userid);
			}


			if ( !isset($userdata[ 'userid' ]) || $userdata[ 'activation' ] != 1 )
			{
				$this->Page->error(404, trans('Das von Ihnen angeforderte Profil existiert nicht.'));
			}

			if ( $userdata[ 'blocked' ] )
			{
				$this->Page->error(404, trans('Das von Ihnen angeforderte Profil wurde vorübergehend gesperrt.'));
			}


			if ( (!User::hasPerm('user/canviewotherprofiles', false) && $userdata[ 'userid' ] != User::getUserId()) )
			{
				$this->Page->sendAccessError(trans('Sie besitzen nicht die nötigen Rechte um sich dieses Profil anzusehen!'));
			}

			$data[ 'profile' ]                = $userdata;
			$data[ 'profile' ][ 'userphoto' ] = User::getUserPhoto($userdata);


			$data[ 'profile' ][ 'rank' ] = $this->model->getRank($userdata[ 'groupid' ], $userdata[ 'userposts' ], $userdata[ 'gender' ]);


			if ( $userdata[ 'msn' ] != '' )
			{
				$data[ 'profile' ][ 'msnstatus' ] = (array)User::getMSNStatus($userdata[ 'msn' ]);
			}

			if ( $userdata[ 'icq' ] != '' && is_numeric($userdata[ 'icq' ]) )
			{
				$data[ 'profile' ][ 'icqstatus' ] = (array)User::getICQStatus($userdata[ 'icq' ]);
			}
			if ( !is_numeric($userdata[ 'icq' ]) )
			{
				$data[ 'profile' ][ 'icq' ] = false;
			}

			if ( $userdata[ 'yim' ] != '' )
			{
				$data[ 'profile' ][ 'yimstatus' ] = (array)User::getYIMStatus($userdata[ 'yim' ]);
			}

			if ( $userdata[ 'skype' ] != '' )
			{
				$data[ 'profile' ][ 'skypestatus' ] = (array)User::getSkypeStatus($userdata[ 'skype' ]);
			}

			// Set the BBCode settings
			BBCode::allowBBCodes('biobbcodes');

			$data[ 'profile' ][ 'bio' ] = BBCode::toXHTML($data[ 'profile' ][ 'usertext' ]);

			$this->Breadcrumb->add(sprintf(trans('Profil von %s'), $data[ 'profile' ][ 'username' ]), '');


			HTTP::setinput('action', 'profile');
			$this->Template->process('profile/index', $data, true);
		}
	}

}

?>