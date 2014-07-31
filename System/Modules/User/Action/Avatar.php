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
 * @file         Avatar.php
 */
class User_Action_Avatar extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{

			$send = $this->_post('send');
			if ( !empty($send) )
			{
				if ( !empty($data[ 'imgpath' ]) )
				{

					if ( !is_file(ROOT_PATH . $data[ 'imgpath' ]) )
					{
						echo Library::json(array (
						                         'success' => false,
						                         'msg'     => 'Irgendwie ist deine Datei abhanden gekommen.'
						                   ));
						exit;
					}

					$size      = getimagesize(ROOT_PATH . $data[ 'imgpath' ]);
					$ext       = Library::getExtension($data[ 'imgpath' ]);
					$file      = explode('/', Library::formatPath($data[ 'imgpath' ]));
					$file_name = array_pop($file);


					$old = $this->db->query('SELECT * FROM %tp%avatars WHERE userid = ' . User::getUserId())->fetch();

					if ( $old[ 'avatarid' ] )
					{
						if ( file_exists(PAGE_PATH . 'upload/userfiles/userphotos/' . User::getUserId() . '/' . $old[ 'avatarname' ] . '.' . $old[ 'avatarextension' ]) )
						{
							#     unlink(PAGE_PATH . 'upload/userfiles/userphotos/' . User::getUserId() . '/' . $old['avatarname'] . '.' . $old['avatarextension']);
						}

						#$old = $this->db->query('DELETE FROM %tp%avatars WHERE userid = ' . User::getUserId());
					}

					$file_name = str_replace('.' . $ext, '', $file_name);

					$dat = array (
						'width'           => $size[ 0 ],
						'height'          => $size[ 1 ],
						'avatarextension' => $ext,
						'avatarname'      => $file_name,
						'userid'          => User::getUserId()
					);

					$str = $this->db->compile_db_insert_string($dat);
					$sql = "INSERT INTO %tp%avatars ({$str['FIELD_NAMES']}) VALUES({$str['FIELD_VALUES']})";
					$this->db->query($sql);

					$avatarid = $this->db->insert_id();
					$this->db->query("UPDATE %tp%users SET avatarid = ? WHERE userid = ?", $avatarid, User::getUserId());

					echo Library::json(array (
					                         'success' => true,
					                         'msg'     => 'Dein Avatar/Profilbild wurde erfolgreich geändert.'
					                   ));
					exit;
				}
				else
				{
					$avatarid = (int)$this->_post('avatarid');
					if ( !empty($avatarid) && $avatarid != User::get('avatarid') )
					{
						$this->db->query("UPDATE %tp%users SET avatarid = ? WHERE userid = ?", $avatarid, User::getUserId());

						echo Library::json(array (
						                         'success' => true,
						                         'msg'     => 'Dein Avatar/Profilbild wurde erfolgreich geändert.'
						                   ));
						exit;
					}
					else
					{
						echo Library::json(array (
						                         'success' => true,
						                         'msg'     => 'Dein Avatar/Profilbild wurde nicht geändert.'
						                   ));
						exit;
					}
				}
			}


			User::getPhoto();

			$this->Breadcrumb->add(trans('Dein Kontrollzentrum'), '/user/controlpanel');
			$this->Breadcrumb->add(trans('Avatar ändern'), '');

			$data = array ();
			Session::save('uiqtoken', Library::UUIDv4());


			$avatarpath = HTML_URL . "img/avatars/avatar-" . $rs[ 'avatarid' ] . '.' . $rs[ 'avatarextension' ];

			$GLOBALS[ 'perpage' ] = 27;

			$avatars = Model::getModelInstance('Avatar');
			$_result = $avatars->getGridData();
			foreach ( $_result[ 'result' ] as $rs )
			{
				$data[ 'avatars' ][ ] = array (
					'id'     => $rs[ 'avatarid' ],
					'src'    => HTML_URL . "img/avatars/avatar-" . $rs[ 'avatarid' ] . '.' . $rs[ 'avatarextension' ],
					'width'  => $rs[ 'width' ],
					'height' => $rs[ 'height' ],
				);
			}

			if ( IS_AJAX )
			{
				echo Library::json(array (
				                         'success' => true,
				                         'avatars' => $data[ 'avatars' ]
				                   ));
				exit;
			}

			if ( $_result[ 'total' ] > 0 )
			{
				$limit = $this->getPerpage();

				$page  = (int)$this->input('page') ? (int)$this->input('page') : 1;
				$pages = ceil($_result[ 'total' ] / $limit);

				$this->load('Paging');
				$url = $this->Paging->generate(array ());
				$this->Paging->setPaging($url, $page, $pages);
			}

			$this->Template->process('usercontrol/change_avatar', $data, true);
		}
	}

}
