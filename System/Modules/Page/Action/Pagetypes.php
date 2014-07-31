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
 * @package      Page
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Pagetypes.php
 */
class Page_Action_Pagetypes extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->getApplication()->getMode() === Application::BACKEND_MODE )
		{
			$this->_processBackend();
		}
	}

	private function _processBackend ()
	{

		$this->load('Grid');
		$this->Grid
            ->initGrid('pages_types', 'id', 'title', 'asc')
            ->setGridDataUrl( 'admin.php?adm=page&action=pagetypes' )
            ->enableColumnVisibleToggle();

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

		$this->Grid->addHeader(array (
		                             array (
			                             "field"   => "id",
			                             "content" => trans('ID'),
			                             'width'   => '5%',
			                             "sort"    => "id",
			                             "default" => true,
			                             'type'    => 'num',
                                         'forcevisible' => true
		                             ),
		                             array (
			                             "field"   => "title",
			                             "content" => trans('Titel'),
			                             "sort"    => "title",
			                             "default" => true,
			                             'type'    => 'alpha label',
                                         'islabel' => true,
                                         'forcevisible' => true
                                     ),
		                             array (
			                             "field"   => "pagetype",
			                             "content" => trans('Seitentyp'),
			                             'width'   => '12%',
			                             "sort"    => "pagetype",
			                             "default" => true,
			                             'nowrap'  => true,
			                             'type'    => 'alpha'
		                             ),
		                             array (
			                             "field"   => "contentlayout",
			                             "content" => trans('Layout'),
			                             'width'   => '12%',
			                             "sort"    => "contentlayout",
			                             "default" => true,
			                             'nowrap'  => true,
			                             'type'    => 'date'
		                             ),
		                             array (
			                             "field"   => "created",
			                             "content" => trans('Erstellt am'),
			                             'width'   => '12%',
			                             "sort"    => "moddate",
			                             "default" => true,
			                             'nowrap'  => true,
			                             'type'    => 'date'
		                             ),
		                             array (
			                             "field"   => "created_user",
			                             "content" => trans('Autor'),
			                             'width'   => '10%',
			                             "sort"    => "createdby",
			                             "default" => true,
			                             'nowrap'  => true,
			                             'type'    => 'alpha'
		                             ),
		                             array (
			                             "field"   => "modifed",
			                             "content" => trans('Bearbeitet am'),
			                             'width'   => '12%',
			                             "sort"    => "moddate",
			                             "default" => false,
			                             'nowrap'  => true,
			                             'type'    => 'date'
		                             ),
		                             array (
			                             "field"   => "modifed_user",
			                             "content" => trans('Bearbeiter'),
			                             'width'   => '10%',
			                             "sort"    => "modifedby",
			                             "default" => false,
			                             'nowrap'  => true,
			                             'type'    => 'alpha'
		                             ),
		                             array (
			                             "field"   => "options",
			                             "content" => trans('Optionen'),
			                             'width'   => '10%',
			                             "default" => true,
			                             'align'   => 'tc'
		                             ),
		                       ));


		$this->Grid->addActions(array (
		                              //   'publish'     => trans( 'Veröffentlichen' ),
		                              //   "unpublish"   => trans( 'nicht Veröffentlichen' ),
		                              //   "archive"     => trans( 'Archivieren' ),
		                              //   "unarchive"   => trans( 'aus Archiv holen' ),
		                              "delete_pagetype" => array (
			                              'label' => trans('Löschen')
		                              )
		                        ));

		$_result = $this->model->getPagetypesGridData();

		$e  = trans('Barbeiten');
		$ef = trans('Formularfelder Bearbeiten');
		$im = BACKEND_IMAGE_PATH;

		foreach ( $_result[ 'result' ] as $rs )
		{
			$rs[ 'created' ] = date('d.m.Y, H:i', $rs[ 'created' ]);

			if ( $rs[ 'modifed' ] > 3600 )
			{
				$rs[ 'modifed' ] = date('d.m.Y, H:i', $rs[ 'modifed' ]);
				$lastModified    = ($rs[ 'modifed' ] > $lastModified ? $rs[ 'modifed' ] : $lastModified);
			}
			else
			{
				$rs[ 'modifed' ] = '';
				$lastModified    = $rs[ 'created' ];
			}
			$_ef    = htmlspecialchars(sprintf($ef, $rs[ "title" ]));
			$_e     = htmlspecialchars(sprintf($e, $rs[ "title" ]));

			$edit   = $this->linkIcon("adm=page&action=edittype&id={$rs['id']}&edit=1", 'edit', $_e);
			$delete = $this->linkIcon("adm=page&action=deletetype&id={$rs['id']}", 'delete');
			$fields = '<a class="doTab" href="admin.php?adm=page&amp;action=fields&amp;pagetypeid=' . $rs[ 'id' ] . '"><img src="' . $im . 'buttons/form.png" border="0" alt="' . $_ef . '" title="' . $_ef . '" /></a>';


			$rs[ 'options' ] = $edit . ' ' . $fields . ' ' . $delete;

			$row = $this->Grid->addRow($rs);
			$row->addFieldData("id", $rs[ "id" ]);
			$row->addFieldData("title", $rs[ "title" ]);
			$row->addFieldData("pagetype", $rs[ 'pagetype' ]);
			$row->addFieldData("contentlayout", $rs[ 'contentlayout' ]);
			$row->addFieldData("created", $rs[ 'created' ]);
			$row->addFieldData("modifed", $rs[ 'modifed' ]);
			$row->addFieldData("created_user", $rs[ "created_user" ]);
			$row->addFieldData("modifed_user", $rs[ "modifed_user" ]);
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
			unset($_result, $this->Grid);

			Ajax::Send(true, $data);
			exit;
		}


		Library::addNavi(trans('Seitentypen Übersicht'));
		$this->Template->process('pages/pagetypes', array (), true);

		exit;
	}

}
