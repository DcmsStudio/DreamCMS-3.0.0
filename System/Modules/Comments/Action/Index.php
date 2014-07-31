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
 * @package      Comments
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Comments_Action_Index extends Controller_Abstract
{

	/**
	 * @var array
	 */
	private $_modules = array ();

	public function execute ()
	{

		if ( $this->isBackend() )
		{
			$this->processBackend();
		}
	}

	private function processBackend ()
	{

		$arr = array (
			''               => '----',
			'online'         => trans('Online Kommentare'),
			'offline'        => trans('Offline Kommentare'),
			'draft'          => trans('wartende Kommentare'),
			'online_offline' => trans('Online &amp; Offline Kommentare'),
            'spam'           => trans('Spam Kommentare'),
		);


		$states = array ();
		foreach ( $arr as $k => $v )
		{
			$states[ $k ] = $v;
		}


		$this->_modules = $this->getApplication()->getModulRegistry();

		$modules        = array ();
		$modules[ '-' ] = trans('Alle Module');
		foreach ( $this->_modules as $key => $dat )
		{
			if ( isset($dat[ 'definition' ]) && isset($dat[ 'definition' ][ 'cancomment' ]) && $dat[ 'definition' ][ 'cancomment' ] )
			{
				$modules[ strtolower($key) ] = $dat[ 'definition' ][ 'modulelabel' ];
			}
		}


		$this->load('Grid');
		$this->Grid->initGrid('comments', 'id', 'date', 'desc');
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
			                             'name'   => 'modul',
			                             'type'   => 'select',
			                             'select' => $modules,
			                             'label'  => trans('Modul'),
			                             'show'   => false
		                             ),
		                             array (
			                             'name'   => 'state',
			                             'type'   => 'select',
			                             'select' => $states,
			                             'label'  => trans('Status'),
			                             'show'   => false
		                             )
		                       ));

		$this->Grid->addHeader(array (
		                             // sql feld						 header	 	sortieren		standart
		                             array (
			                             "field"   => "title",
			                             "content" => trans('Titel'),
			                             "sort"    => "title",
			                             "default" => true,
                                         'islabel' => true,
			                             'type'    => 'alpha label'
		                             ),
		                             array (
			                             "field"   => "modul",
			                             "content" => trans('Modul'),
			                             'width'   => '20%',
			                             "sort"    => "cat",
			                             "default" => true,
			                             'nowrap'  => true,
			                             'type'    => 'alpha'
		                             ),
		                             array (
			                             "field"   => "timestamp",
			                             "content" => trans('Datum'),
			                             'width'   => '20%',
			                             "sort"    => "date",
			                             "default" => true,
			                             'nowrap'  => true,
			                             'type'    => 'date'
		                             ),
		                             array (
			                             "field"   => "created_user",
			                             "content" => trans('Autor'),
			                             'width'   => '15%',
			                             "sort"    => "createdby",
			                             "default" => false,
			                             'nowrap'  => true,
			                             'type'    => 'alpha'
		                             ),
		                             //                 array( "field"   => "modifed", "content" => trans( 'Bearbeitet am' ), 'width'   => '12%', "sort"    => "moddate", "default" => false, 'nowrap'  => true, 'type'    => 'date' ),
		                             //                 array( "field"   => "modifed_user", "content" => trans( 'Bearbeiter' ), 'width'   => '10%', "sort"    => "modifedby", "default" => false, 'nowrap'  => true, 'type'    => 'alpha' ),
		                             array (
			                             "field"   => "ip",
			                             "content" => trans('IP'),
			                             'width'   => '15%',
			                             "sort"    => "ip",
			                             "default" => false,
			                             'nowrap'  => true,
			                             'type'    => 'alpha'
		                             ),
		                             array (
			                             "field"   => "published",
			                             "content" => trans('Aktiv'),
			                             "sort"    => "published",
			                             'width'   => '8%',
			                             "default" => true,
			                             'align'   => 'tc'
		                             ),
		                             array (
			                             "field"   => "options",
			                             "content" => trans('Optionen'),
			                             'width'   => '12%',
			                             "default" => true,
			                             'align'   => 'tc'
		                             ),
		                       ));

		$this->Grid->addActions(array (
		                              'publish'   => trans('Veröffentlichen'),
		                              "unpublish" => trans('nicht Veröffentlichen'),
		                              "delete"    => array (
			                              'label' => trans('Löschen'),
			                              'msg'   => trans('Ausgewählte Kommentare werden gelöscht. Wollen Sie fortsetzen?')
		                              )
		                        ));


		$_result = $this->model->getGridData();

		$limit = $this->getPerpage();
		$pages = ceil($_result[ 'total' ] / $limit);


		$e  = trans('Kommentar barbeiten');
		$im = BACKEND_IMAGE_PATH;
		foreach ( $_result[ 'result' ] as $rs )
		{
			$rs[ 'timestamp' ] = date('d.m.Y, H:i', $rs[ 'timestamp' ]);


			$_e              = htmlspecialchars(sprintf($e, $rs[ "title" ]));
			$edit            = $this->linkIcon("adm=comments&action=edit&id={$rs['id']}&edit=1", 'edit', $_e);
			$delete          = $this->linkIcon("adm=comments&action=delete&id={$rs['id']}", 'delete');
			$rs[ 'options' ] = ' <a href="admin.php?adm=comments&action=show&id=' . $rs[ 'id' ] . '" class="doPopup"><img src="' . $im . 'comment.png" title="' . trans('Vorschau') . '"/></a> ' . $edit . ' ' . $delete;


			$published = $this->getGridState(($rs[ 'published' ] == 9 ? DRAFT_MODE :
				$rs[ 'published' ]), $rs[ 'id' ], 0, 0, 'admin.php?adm=comments&action=publish&id=');

			$row = $this->Grid->addRow($rs);
			$row->addFieldData("title", (empty($rs[ "title" ]) ? trans('Kommentar ohne Titel') : $rs[ "title" ]));
			$row->addFieldData("timestamp", $rs[ 'timestamp' ]);
			$row->addFieldData("created_user", $rs[ "username" ] . ($rs[ 'userid' ] ? '' : ' (' . trans('Gast') . ')'));
			$row->addFieldData("modul", $this->_getModulLabel($rs[ "modul" ]));
			$row->addFieldData("published", $published);
			$row->addFieldData("ip", $rs[ 'ip' ]);
			$row->addFieldData("options", $rs[ 'options' ]);
		}

		$griddata = $this->Grid->renderData($_result[ 'total' ]);
		$data     = array ();
		if ( $this->input('getGriddata') )
		{

			$data[ 'success' ]  = true;
			$data[ 'total' ]    = $_result[ 'total' ];
			$data[ 'sort' ]     = $GLOBALS[ 'sort' ];
			$data[ 'orderby' ]  = $GLOBALS[ 'orderby' ];
			$data[ 'datarows' ] = $griddata[ 'rows' ];
			unset($_result, $this->Grid);

			Ajax::Send(true, $data);
			exit;
		}

		Library::addNavi(trans('Kommentar Übersicht'));
		$this->Template->process('comments/index', array (
		                                                 'grid' => $this->Grid->getJsonData($_result[ 'total' ])
		                                           ), true);

		exit;
	}

	/**
	 * @param $modulKey
	 * @return mixed
	 */
	private function _getModulLabel ( $modulKey )
	{

		foreach ( $this->_modules as $key => $dat )
		{
			if ( $key == $modulKey )
			{
				if ( isset($dat[ 'definition' ]) )
				{
					return $dat[ 'definition' ][ 'modulelabel' ];
				}
			}
		}
	}

}

?>