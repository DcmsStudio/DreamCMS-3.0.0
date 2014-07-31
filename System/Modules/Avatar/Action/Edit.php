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
 * @package      Avatar
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Edit.php
 */
class Avatar_Action_Edit extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}


		$avatarid = (int)HTTP::input('id');
		$r        = $this->model->getAvatarByID($avatarid);

		if ( !$r[ 'avatarid' ] )
		{
			Library::sendJson(false, trans('Das Avatar existiert nicht'));
		}


		$this->load('Usergroup');
		$groups = $this->Usergroup->getAllUsergroups();

		if ( $this->_post('send') )
		{
			demoadm();

			$this->model->save($avatarid, $this->_post());


			Library::sendJson(true, sprintf(trans('Das Avatar `%s` wurde aktualisiert.'), $r[ 'avatarname' ]));
		}


		$r[ 'usergroups' ] = $groups;
		if ( $r[ 'avatarid' ] )
		{
			$r[ 'avatarurl' ] = HTML_URL . "img/avatars/avatar-" . $r[ 'avatarid' ] . '.' . $r[ 'avatarextension' ];
		}

		foreach ( $r[ 'usergroups' ] as $idx => &$rs )
		{
			if ( $rs[ 'default_group' ] )
			{
				unset($r[ 'usergroups' ][ $idx ]);
			}
			$rs[ 'label' ] = $rs[ 'title' ];
			$rs[ 'value' ] = $rs[ 'groupid' ];
		}

		$r[ 'usergroups' ][ 'selected' ] = $r[ 'groupid' ];


		Library::addNavi(trans('Avatare'));
		Library::addNavi(sprintf(trans('Avatar %s bearbeiten'), $r[ 'avatarname' ]));

		$this->Template->process('avatar/edit', $r, true);
	}

}

?>