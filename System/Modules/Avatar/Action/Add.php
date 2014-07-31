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
 * @file         Add.php
 */
class Avatar_Action_Add extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}
		$this->load('Usergroup');
		$data[ 'usergroups' ] = $this->Usergroup->getAllUsergroups();


		if ( $this->_post('send') )
		{
			if ( !$this->_post('filename') )
			{
				Library::sendJson(false, trans('Bitte erst eine Datei uploaden!'));
			}

			$need = $this->_post('needposts');

			if ( !isset($need) )
			{
				Library::sendJson(false, trans('Bitte `Ab Beiträge` ausfüllen!'));
			}


			$data = $this->_post();

			if ( !is_file(UPLOAD_PATH . 'tmp/avatar-' . $data[ 'filename' ]) )
			{
				Library::sendJson(false, trans('Die Upload Datei existiert leider nicht mehr!'));
			}

			demoadm();

			$info = getimagesize(UPLOAD_PATH . 'tmp/avatar-' . $data[ 'filename' ]);


			$data[ 'width' ]  = $info[ 0 ];
			$data[ 'height' ] = $info[ 1 ];

			$data[ 'avatarextension' ] = Library::getExtension($data[ 'filename' ]);
			$data[ 'avatarname' ]      = str_replace('.' . $data[ 'avatarextension' ], '', $data[ 'filename' ]);

			$newid = $this->model->save(0, $data);


			copy(UPLOAD_PATH . 'tmp/avatar-' . $data[ 'filename' ], ROOT_PATH . HTML_URL . 'img/avatars/avatar-' . $newid . '.' . $data[ 'avatarextension' ]);
			@unlink(UPLOAD_PATH . 'tmp/avatar-' . $data[ 'filename' ]);


			echo Library::json(array (
			                         'success' => true,
			                         'msg'     => trans('Das Avatar wurde hinzugefügt.'),
			                         'newid'   => $newid
			                   ));

			exit;
		}


		foreach ( $data[ 'usergroups' ] as $idx => &$r )
		{
			if ( $r[ 'default_group' ] )
			{
				unset($data[ 'usergroups' ][ $idx ]);
			}
			$r[ 'label' ] = $r[ 'title' ];
			$r[ 'value' ] = $r[ 'groupid' ];
		}


		Library::addNavi(trans('Avatare'));
		Library::addNavi(trans('Neues Avatar hinzufügen'));

		$this->Template->process('avatar/edit', $data, true);
	}

}

?>