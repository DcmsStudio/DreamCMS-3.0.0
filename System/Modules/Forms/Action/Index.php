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
 * @package      Forms
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Forms_Action_Index extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}


		$this->load('Grid');
		$this->Grid->initGrid('forms', 'formid', 'name', 'asc')->setGridDataUrl('admin.php?adm=forms');
		$this->Grid->addFilter(array (
		                             array (
			                             'name'  => 'q',
			                             'type'  => 'input',
			                             'value' => '',
			                             'label' => 'Suchen nach',
			                             'show'  => true,
			                             'parms' => array (
				                             'size' => '40'
			                             )
		                             ),
		                             array (
			                             'submitbtn' => true
		                             ),
		                       ));
		$this->Grid->addHeader(array (
		                             // sql feld						 header	 	sortieren		standart
		                             array (
			                             "field"   => "name",
			                             "content" => 'Name',
			                             'width'   => '10%',
			                             "sort"    => "name",
			                             "default" => true
		                             ),
		                             array (
			                             'islabel' => true,
			                             "field"   => "title",
			                             "content" => 'Title',
			                             'width'   => '20%',
			                             "sort"    => "name",
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "description",
			                             "content" => 'Beschreibung',
			                             "sort"    => "description",
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "options",
			                             "content" => 'Optionen',
			                             'width'   => '12%',
			                             "default" => true,
			                             'align'   => 'tc'
		                             ),
		                       ));

		$this->Grid->addActions(array (
		                              "deleteform" => array (
			                              'label' => trans('Löschen'),
			                              'msg'   => trans('Ausgewählte Formulare werden gelöscht. Inhalte die ein Formular verwenden, werden womöglich nicht mehr korrekt angezeigt. Wollen Sie fortsetzen?')
		                              )
		                        ));

		$model   = Model::getModelInstance('forms');
		$_result = $model->getGridData();


		$e  = trans('Formular %s bearbeiten');
		$ef = trans('Formularfelder');
		$im = BACKEND_IMAGE_PATH;
		foreach ( $_result[ 'result' ] as $rs )
		{
			$_e              = sprintf($e, $rs[ 'title' ]);
			$_ef             = sprintf($ef, $rs[ 'title' ]);
			$rs[ 'options' ] = <<<EOF
		<a class="doTab" href="admin.php?adm=forms&amp;action=editform&amp;formid={$rs['formid']}"><img src="{$im}edit.png" border="0" alt="{$_e}" title="{$_e}" /></a>

        <a class="doTab" href="admin.php?adm=forms&amp;action=fields&amp;formid={$rs['formid']}"><img src="{$im}buttons/form.png" border="0" alt="{$_ef}" title="{$_ef}" /></a>
        <a class="delconfirm ajax" href="admin.php?adm=forms&amp;action=deleteform&amp;formid={$rs['formid']}"><img src="{$im}delete.png" border="0" alt="Löschen" title="Löschen" /></a>
EOF;
			$row             = $this->Grid->addRow($rs);
			$row->addFieldData("name", $rs[ "name" ]);
			$row->addFieldData("title", $rs[ 'title' ]);
			$row->addFieldData("description", strip_tags($rs[ 'description' ]));
			$row->addFieldData("options", $rs[ 'options' ]);
		}

		$griddata = $this->Grid->renderData($_result[ 'total' ]);
		$data     = array ();
		if ( $this->input('getGriddata') )
		{

			$data[ 'success' ] = true;
			$data[ 'total' ]   = $_result[ 'total' ];
			$data[ 'datarows' ] = $griddata[ 'rows' ];
			unset($_result, $this->Grid);

			Ajax::Send(true, $data);
			exit;
		}

		Library::addNavi(trans('Formularfelder Übersicht'));
		$this->Template->process('forms/index', array (), true);

		exit;
	}

}

?>