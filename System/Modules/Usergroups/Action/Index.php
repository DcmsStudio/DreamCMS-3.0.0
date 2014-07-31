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
 * @package      Usergroups
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Usergroups_Action_Index extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$model = Model::getModelInstance('usergroups');

		$this->load('Grid');
		$this->Grid->initGrid('users_groups', 'groupid', 'title', 'desc')->setGridDataUrl('admin.php?adm=usergroups');
		$this->Grid->addHeader(array (
		                             // sql feld						 header	 	sortieren		standart
		                             array (
			                             "field"   => "title",
			                             "content" => 'Benutzergruppe',
			                             "sort"    => "title",
			                             "default" => true,
                                         'islabel' => true
		                             ),
		                             array (
			                             "field"   => "users",
			                             "content" => 'Benutzer',
			                             "sort"    => "users",
			                             'width'   => '10%',
			                             "default" => false,
			                             'align'   => 'tc'
		                             ),
                                    array (
                                        "field"   => "dash",
                                        "content" => 'Dashboard Zugriff',
                                        "sort"    => "dash",
                                        'width'   => '15%',
                                        "default" => true,
                                        'nowrap'  => true,
                                        'align'   => 'tc'
                                    ),
		                             array (
			                             "field"   => "default",
			                             "content" => 'Standart Benutzergruppe',
			                             'width'   => '15%',
			                             "default" => true,
			                             'nowrap'  => false,
			                             'align'   => 'tc'
		                             ),
		                             array (
			                             "field"   => "options",
			                             "content" => 'Optionen',
			                             'width'   => '10%',
			                             "default" => true,
			                             'align'   => 'tc'
		                             ),
		                       ));

		$_result = $model->getGridQuery();


		$limit = $this->getPerpage();
		$pages = ceil($_result[ 'total' ] / $limit);

		$im = BACKEND_IMAGE_PATH;

		$delTitle = trans('Löschen');
		$e        = trans('Benutzergruppe `%s` bearbeiten');
		$d        = trans('`%s` Dashboard Berechtigungen bearbeiten');

		foreach ( $_result[ 'result' ] as $rs )
		{
			$_e = sprintf($e, $rs[ "title" ]);
			$_d = sprintf($d, $rs[ "title" ]);


			$edit            = $this->linkIcon("adm=usergroups&action=edit&id={$rs['groupid']}&edit=1", 'edit', $_e);
			$delete          = $this->linkIcon("adm=usergroups&action=delete&id={$rs['groupid']}", 'delete');
			$rs[ 'options' ] = $edit . ' ' . $delete;
			/*

			  $rs['options'] = <<<EOF
			  <a class="doTab" href="admin.php?adm=usergroups&amp;sid={$cp->session_id}&amp;action=edit&amp;id={$rs['groupid']}"><img src="{$im}edit.gif" border="0" alt="{$_e}" title="{$_e}" /></a> &nbsp;
			  <a class="delconfirm" href="admin.php?adm=usergroups&action=delete&id={$rs['groupid']});"><img src="{$im}delete.gif" id="del_{$rs['groupid']}" border="0" alt="{$delTitle}" title="{$delTitle}" /></a>
			  EOF;
			 */
			if ( $rs[ 'system' ] )
			{
				$delTitle        = trans('Gruppe kann nicht gelöschst werden');
				$rs[ 'options' ] = $edit . ' ' . '<img src="' . $im . 'delete.png" id="del_' . $rs[ 'groupid' ] . '" class="disabled" border="0" alt="' . $delTitle . '" title="' . $delTitle . '" />';
			}


			$icon            = ($rs[ 'default_group' ] ? 'online' : 'offline');
			$rs[ 'default' ] = <<<EOF
	<a href="javascript:setDefaultGroup({$rs['groupid']});"><img src="{$im}{$icon}.gif" id="load_{$rs['groupid']}" border="0" alt="" title="Switch Standart Benutzergruppe" /></a>
EOF;


			$dash_icon    = ($rs[ 'dashboard' ] ? 'tick.png' : 'cross.png');
			$dashlink     = ($rs[ 'dashboard' ] ?
				'<a class="doTab" href="admin.php?adm=usergroups&amp;action=dashaccess&amp;id=' . $rs[ 'groupid' ] . '" title="' . $_d . '">' :
				'');
			$rs[ 'dash' ] = $dashlink . '<img src="' . $im . $dash_icon . '" title="' . $_d . '" />' . ($dashlink ?
					'</a>' : '');


			$row = $this->Grid->addRow($rs);
			$row->addFieldData("title", $rs[ "title" ]);
			$row->addFieldData("users", $rs[ 'users' ]);
			$row->addFieldData("dash", $rs[ 'dash' ]);
			$row->addFieldData("default", $rs[ 'default' ]);
			$row->addFieldData("options", $rs[ 'options' ]);
		}

		$griddata = $this->Grid->renderData($_result[ 'total' ]);

		$data = array ();
		if ( $this->input('getGriddata') )
		{

			$data[ 'success' ] = true;
			$data[ 'total' ]   = $_result[ 'total' ];
			$data[ 'datarows' ] = $griddata[ 'rows' ];
			Ajax::Send(true, $data);
			exit;
		}


		$this->Template->process('group/group_view', array (), true);
	}

}

?>