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
 * @package      Trash
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Trash_Action_Index extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}


		$this->load('Grid');
		$this->Grid->initGrid('trash', 'trashid', 'deletedate', 'desc');
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
		                             )
		                       ));


		$this->Grid->addActions(array (
		                              "restore" => trans('Wiederherstellen'),
		                              "delete"  => array (
			                              'label' => trans('Endgültig Löschen'),
			                              'msg'   => trans('Ausgewählte Papiekorbeinträge werden endgültig gelöscht. Wiederherstellung der Daten anschließend nicht mehr möglich! Wollen Sie fortsetzen?')
		                              )
		                        ));


		$this->Grid->addHeader(array (
		                             // sql feld						 header	 	sortieren		standart
		                             array (
			                             "field"   => "datalabel",
			                             "content" => trans('Titel'),
			                             "sort"    => "datalabel",
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "typelabel",
			                             "content" => trans('Type'),
			                             'width'   => '15%',
			                             "sort"    => "label",
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "deletedate",
			                             "content" => trans('gelöscht am'),
			                             "sort"    => "date",
			                             "default" => true,
			                             'width'   => '15%',
			                             'nowrap'  => true,
			                             'type'    => 'date'
		                             ),
		                             array (
			                             "field"   => "username",
			                             "content" => trans('gelöscht von'),
			                             'width'   => '15%',
			                             "sort"    => "username",
			                             "default" => true,
			                             'nowrap'  => true
		                             ),
		                             array (
			                             "field"   => "options",
			                             "content" => trans('Optionen'),
			                             'width'   => '6%',
			                             "default" => true,
			                             'align'   => 'tc'
		                             ),
		                       ));


		$model = Model::getModelInstance('trash');

		$_result = $model->getGridData();


		$limit = $this->getPerpage();
		$pages = ceil($_result[ 'total' ] / $limit);


		$im = BACKEND_IMAGE_PATH;
		$e  = trans('Löschen');
		foreach ( $_result[ 'result' ] as $rs )
		{

			$rs[ 'options' ] = <<<EOF
		<a class="delconfirm ajax" href="admin.php?adm=trash&amp;action=delete&amp;id={$rs['id']}"><img src="{$im}delete.gif" border="0" alt="{$e}" title="{$e}" /></a>
EOF;
			$row             = $this->Grid->addRow($rs);
			$row->addFieldData("datalabel", $rs[ "datalabel" ]);
			$row->addFieldData("typelabel", $rs[ "typelabel" ]);
			$row->addFieldData("deletedate", date('d.m.Y, H:i:s', $rs[ 'deletedate' ]));
			$row->addFieldData("username", $rs[ 'username' ]);
			$row->addFieldData("options", $rs[ 'options' ]);
		}

		$griddata = array ();
		$griddata = $this->Grid->renderData($_result[ 'total' ]);
		$data     = array ();
		if ( $this->input('getGriddata') )
		{

			$data[ 'success' ]  = true;
			$data[ 'total' ]    = $_result[ 'total' ];
			$data[ 'datarows' ] = $griddata[ 'rows' ];

			Ajax::Send(true, $data);
			exit;
		}


		Library::addNavi(trans('Papierkorb'));
		$this->Template->process('trash/index', array (
		                                              'grid' => $this->Grid->getJsonData($_result[ 'total' ])
		                                        ), true);
	}

}

?>