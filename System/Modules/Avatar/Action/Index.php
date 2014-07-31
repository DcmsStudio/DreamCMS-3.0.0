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
 * @file         Index.php
 */
class Avatar_Action_Index extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}
		$this->load('Usergroup');
		$groups = $this->Usergroup->getAllUsergroups();

		$g      = array ();
		$g[ 0 ] = trans('--- Alle Benutzergruppen ---');
		foreach ( $groups as $r )
		{
			$g[ $r[ 'groupid' ] ] = $r[ 'title' ];
		}


		$this->load('Grid');
		$this->Grid
			->initGrid('avatars', 'avatarid', 'avatarname', 'asc')
			->setGridDataUrl('admin.php?adm=avatar');


		$this->Grid->addFilter(array (
		                             array (
			                             'name'  => 'q',
			                             'type'  => 'input',
			                             'value' => '',
			                             'label' => trans('Suchen nach'),
			                             'show'  => true,
			                             'parms' => array (
				                             'size' => '40'
			                             )
		                             ),
		                             array (
			                             'name'   => 'groupid',
			                             'type'   => 'select',
			                             'select' => $g,
			                             'label'  => trans('Benutzergruppe'),
			                             'show'   => false
		                             ),
		                       ));


		$this->Grid->addHeader(array (
		                             // sql feld						 header	 	sortieren		standart
		                             array (
			                             "field"   => "avatar",
			                             "content" => trans('Avatar'),
			                             'width'   => '12%',
			                             "default" => true
		                             ),
		                             array (
			                             'islabel' => true,
			                             "field"   => "avatarname",
			                             "content" => trans('Avatarname'),
			                             'width'   => '30%',
			                             "sort"    => "avatarname",
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "groupname",
			                             "content" => trans('Benutzergruppe'),
			                             "sort"    => "groupname",
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "username",
			                             "content" => trans('Benutzer'),
			                             "sort"    => "username",
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "needposts",
			                             "sort"    => "needposts",
			                             "content" => trans('ab Beiträge'),
			                             'width'   => '8%',
			                             "default" => true,
			                             'align'   => 'tc'
		                             ),
		                             array (
			                             "field"   => "published",
			                             "sort"    => "published",
			                             "content" => trans('Aktiv'),
			                             'width'   => '6%',
			                             "default" => true,
			                             'align'   => 'tc'
		                             ),
		                             array (
			                             "field"   => "options",
			                             "content" => trans('Optionen'),
			                             'width'   => '6%',
			                             "default" => true,
			                             'align'   => 'tc'
		                             ),
		                       ));

		$this->Grid->addActions(array (
		                              "delete" => array (
			                              'label' => trans('Löschen'),
			                              'msg'   => trans('Ausgewählte Avatare werden gelöscht. Wollen Sie fortsetzen?')
		                              )
		                        ));


		$result = $this->model->getGridData();

		foreach ( $result[ 'result' ] as $rs )
		{

			$edit   = $this->linkIcon("adm=avatar&action=edit&id={$rs['avatarid']}", 'edit', htmlspecialchars(sprintf(trans('Avatar `%s` bearbeiten'), $rs[ "avatarname" ])));
			$delete = $this->linkIcon("adm=avatar&action=delete&id={$rs['avatarid']}", 'delete');

			$rs[ 'options' ] = <<<EOF
             {$edit} &nbsp; {$delete}
EOF;


			$publish = $this->getGridState($rs[ 'published' ], $rs[ 'avatarid' ], 0, 0, 'admin.php?adm=avatar&amp;action=publish&amp;id=');

			$avatarpath = HTML_URL . "img/avatars/avatar-" . $rs[ 'avatarid' ] . '.' . $rs[ 'avatarextension' ];
			$width      = $rs[ 'width' ];
			$height     = $rs[ 'height' ];

			$row = $this->Grid->addRow($rs);
			$row->addFieldData("avatar", sprintf('<img src="%s" width="%s" height="%s" />', $avatarpath, $width, $height));
			$row->addFieldData("avatarname", $rs[ 'avatarname' ]);
			$row->addFieldData("needposts", $rs[ 'needposts' ]);
			$row->addFieldData("username", $rs[ 'username' ]);
			$row->addFieldData("groupname", $rs[ 'grouptitle' ]);
			$row->addFieldData("published", $publish);
			$row->addFieldData("options", $rs[ 'options' ]);
		}


		$griddata = $this->Grid->renderData($result[ 'total' ]);

		if ( HTTP::input('getGriddata') )
		{
			$data[ 'success' ]  = true;
			$data[ 'total' ]    = $result[ 'total' ];
			$data[ 'datarows' ] = $griddata[ 'rows' ];

			echo Library::json($data);
			exit;
		}

		Library::addNavi(trans('Avatare'));

		$this->Template->process('avatar/index', array (), true);
	}

}

?>