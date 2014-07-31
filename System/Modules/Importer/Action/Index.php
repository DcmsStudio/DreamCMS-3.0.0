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
 * @package      Importer
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Importer_Action_Index extends Controller_Abstract
{

	/**
	 * @uses Ajax
	 * @uses Grid
	 * @uses Library
	 * @uses Template
	 * @return void
	 */
	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$this->load('Grid');
		$this->Grid->initGrid('indexer', 'id', 'filepath', 'asc');
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
		                              "delete"    => array (
			                              'label' => trans('Import(e) Löschen'),
			                              'msg'   => trans('Ausgewählte Import(e) werden gelöscht. Wollen Sie fortsetzen?')
		                              ),
		                              "deleteall" => array (
			                              'label' => trans('Import(e) und derene Inhalte Löschen'),
			                              'msg'   => trans('Ausgewählte Import(e) und derene Inhalte werden gelöscht. Wollen Sie fortsetzen?')
		                              ),
		                        ));

		$this->Grid->addHeader(array (
		                             array (
			                             "field"   => "id",
			                             'width'   => '5%',
			                             "content" => trans('ID'),
			                             "default" => true,
			                             'type'    => 'num'
		                             ), // sql feld						 header	 	sortieren		standart
		                             array (
			                             "field"   => "filepath",
			                             "content" => trans('Datei'),
			                             "sort"    => "filepath",
			                             "default" => true,
			                             'type'    => 'alpha label'
		                             ),
		                             array (
			                             "field"   => "xpath",
			                             "content" => trans('XPath'),
			                             'width'   => '15%',
			                             "sort"    => "xpath",
			                             "default" => true,
			                             'nowrap'  => true,
			                             'type'    => 'alpha'
		                             ),
		                             array (
			                             "field"   => "created_user",
			                             "content" => trans('Autor'),
			                             'width'   => '10%',
			                             "sort"    => "createdby",
			                             "default" => false,
			                             'nowrap'  => true,
			                             'type'    => 'alpha'
		                             ),
		                             array (
			                             "field"   => "initdate",
			                             "content" => trans('erster Import'),
			                             "sort"    => "initdate",
			                             'width'   => '120',
			                             "default" => true,
			                             'nowrap'  => true,
			                             'align'   => 'tc',
			                             'type'    => 'num'
		                             ),
		                             array (
			                             "field"   => "lastupdate",
			                             "content" => trans('letzter Import'),
			                             "sort"    => "lastupdate",
			                             'width'   => '120',
			                             "default" => true,
			                             'nowrap'  => true,
			                             'align'   => 'tc',
			                             'type'    => 'num'
		                             ),
		                             array (
			                             "field"   => "published",
			                             "content" => trans('Aktiv'),
			                             "sort"    => "published",
			                             'width'   => '5%',
			                             "default" => true,
			                             'align'   => 'tc'
		                             ),
		                             array (
			                             "field"   => "options",
			                             "content" => trans('Optionen'),
			                             'width'   => '7%',
			                             "default" => true,
			                             'align'   => 'tc'
		                             ),
		                       ));

		$_result = $this->model->getData();

		foreach ( $_result[ 'result' ] as $rs )
		{

			$row = $this->Grid->addRow($rs);
			$row->addFieldData("id", $rs[ "id" ]);
			$row->addFieldData("title", $rs[ "title" ], $fcss);
		}

		$griddata = $this->Grid->renderData();

		if ( $this->input('getGriddata') )
		{
			$data[ 'success' ]  = true;
			$data[ 'total' ]    = $_result[ 'total' ];
			$data[ 'datarows' ] = $griddata[ 'rows' ];
			Ajax::Send(true, $data);
			exit;
		}

		Session::delete('Importer');

		Library::addNavi(trans('Übersicht Imports'));
		$this->Template->process('importer/index', array (
		                                                 'grid' => $this->Grid->getJsonData($_result[ 'total' ])
		                                           ), true);

		exit;
	}

}
