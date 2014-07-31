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
 * @file         Fields.php
 */
class Forms_Action_Fields extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->getApplication()->getMode() === Application::BACKEND_MODE )
		{
			$formid = (int)$this->input('formid');

			$this->load('Grid');
			$this->Grid->setUiqid('form_fields' . $formid);
			$this->Grid->initGrid('form_fields', 'fieldid', 'name', 'asc')->setGridDataUrl('admin.php?adm=forms&action=fields&formid='. $formid .'&ajax=1');

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


			if ( $formid )
			{
				$this->Grid->addHeader(array (
				                             // sql feld						 header	 	sortieren		standart
				                             array (
					                             'islabel' => true,
					                             "field"   => "name",
					                             "content" => 'Titel',
					                             'width'   => '20%',
					                             "sort"    => "name",
					                             "default" => true
				                             ),
				                             array (
					                             "field"   => "fieldtype",
					                             "content" => 'Feld Typ',
					                             'width'   => '12%',
					                             "sort"    => "fieldtype",
					                             "default" => true
				                             ),
				                             array (
					                             "field"   => "description",
					                             "content" => 'Feld Beschreibung',
					                             "sort"    => "description",
					                             "default" => true
				                             ),
				                             array (
					                             "field"   => "options",
					                             "content" => 'Optionen',
					                             'width'   => '10%',
					                             "default" => true,
					                             'align'   => 'tc'
				                             ),
				                       ));
				Library::addNavi(trans('Formularfelder Übersicht'));
			}
			else
			{
				$this->Grid->addHeader(array (
				                             // sql feld						 header	 	sortieren		standart
				                             array (
					                             'islabel' => true,
					                             "field"   => "name",
					                             "content" => 'Titel',
					                             'width'   => '20%',
					                             "sort"    => "name",
					                             "default" => true
				                             ),
				                             array (
					                             "field"   => "fieldtype",
					                             "content" => 'Feld Typ',
					                             'width'   => '12%',
					                             "sort"    => "fieldtype",
					                             "default" => true
				                             ),
				                             array (
					                             "field"   => "rel",
					                             "content" => 'Feld für',
					                             'width'   => '8%',
					                             "sort"    => "rel",
					                             "default" => true
				                             ),
				                             array (
					                             "field"   => "description",
					                             "content" => 'Feld Beschreibung',
					                             "sort"    => "description",
					                             "default" => true
				                             ),
				                             array (
					                             "field"   => "options",
					                             "content" => 'Optionen',
					                             'width'   => '10%',
					                             "default" => true,
					                             'align'   => 'tc'
				                             ),
				                       ));
				Library::addNavi(trans('Profilfelder Übersicht'));
			}


			$this->Grid->addActions(array (
			                              "deletefield" => array (
				                              'label' => trans('Löschen'),
				                              'msg'   => trans('Ausgewählte Formularfelder werden gelöscht. Inhalte die ein Formular verwenden, werden womöglich nicht mehr korrekt angezeigt. Wollen Sie fortsetzen?')
			                              )
			                        ));


			$model   = Model::getModelInstance();
			$_result = $model->getGridFieldsData($formid);


			$im = BACKEND_IMAGE_PATH;
			$p  = trans('Profilefeld');
			$e  = trans('Formularfeld %s bearbeiten');
			$d  = trans('Formularfeld %s löschen');
			foreach ( $_result[ 'result' ] as $rs )
			{
				$_e = sprintf($e, $rs[ 'name' ]);
				$_d = sprintf($d, $rs[ 'name' ]);


				$rs[ 'options' ] = <<<EOF
		<a class="doTab" href="admin.php?adm=forms&action=editfield&field_id={$rs['field_id']}&formid={$rs['formid']}"><img src="{$im}edit.png" border="0" alt="{$_e}" title="{$_e}" /></a> &nbsp;
		<a class="delconfirm ajax" href="admin.php?adm=forms&action=deletefield&fieldid={$rs['field_id']}&formid={$rs['formid']}"><img src="{$im}delete.png" border="0" alt="{$_d}" title="{$_d}" /></a>
EOF;


				$rs[ 'fieldid' ]   = $rs[ 'field_id' ];
				$rs[ 'fieldtype' ] = $rs[ 'type' ];


				$row = $this->Grid->addRow($rs);
				$row->addFieldData("name", $rs[ "name" ]);
				$row->addFieldData("fieldtype", $rs[ 'type' ]);
				$row->addFieldData("rel", ($rs[ 'formid' ] ? '' : $p));
				$row->addFieldData("description", $rs[ 'description' ]);
				$row->addFieldData("options", $rs[ 'options' ]);
			}


			$griddata = array ();
			$griddata = $this->Grid->renderData($_result[ 'total' ]);
			$data     = array ();
			if ( $this->input('getGriddata') )
			{


				$data[ 'success' ] = true;
				$data[ 'total' ]   = $_result[ 'total' ];
				# $data['sort'] = $GLOBALS['sort'];
				# $data['orderby'] = $GLOBALS['orderby'];
				$data[ 'datarows' ] = $griddata[ 'rows' ];
				unset($_result, $this->Grid);

				Ajax::Send(true, $data);
				exit;
			}

			unset($_result, $this->Grid);

			$this->Template->process('forms/fields-index', array ( ), true);

			exit;
		}
	}

}

?>