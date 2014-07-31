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
 * @package      Action
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Modules_Action_Index extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->getApplication()->getMode() === Application::BACKEND_MODE )
		{
			$this->_processBackend();
		}
		else
		{
			$this->_processFrontend();
		}
	}

	private function _processBackend ()
	{

		$model = Model::getModelInstance();

		$arr = array (
			''           => '---------------------------',
			'name'       => trans('Modul'),
			'metatables' => trans('Verwendete Meta Tabellen'),
		);


		$searchin = array ();
		foreach ( $arr as $k => $v )
		{
			$searchin[ $k ] = $v;
		}


		$this->load('Grid');
		$this->Grid
			->initGrid('modules', 'id', 'module', 'asc')
			->enableColumnVisibleToggle()
			->addGridEvent('onAfterLoad', 'function ( ev, grid ) { registerModulUpdate( grid ); }')
			->setGridDataUrl('admin.php?adm=modules');



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
			                             'name'   => 'searchin',
			                             'type'   => 'select',
			                             'select' => $searchin,
			                             'label'  => trans('Suchen in'),
			                             'show'   => false
		                             ),
		                       ));

		$this->Grid->addHeader(array (
		                             // sql feld						 header	 	sortieren		standart
		                             array (
			                             'islabel' => true,
			                             "field"   => "module",
			                             "content" => trans('Modul'),
			                             'width'   => '20%',
			                             "sort"    => "module",
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "description",
			                             "content" => trans('Modul Beschreibung'),
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "version",
			                             "content" => trans('Version'),
			                             'width'   => '8%',
			                             "sort"    => "version",
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "published",
			                             "sort"    => "published",
			                             "content" => trans('Publish'),
			                             'width'   => '7%',
			                             "default" => true,
			                             'align'   => 'tc'
		                             ),
		                             array (
			                             "field"   => "options",
			                             "content" => trans('Optionen'),
			                             'width'   => '10%',
			                             "default" => true,
			                             'align'   => 'tc'
		                             ),
		                       ));


		$coreModules = $this->getApplication()->_coreModules;
		$_result     = $model->getData();


		$limit = $this->getPerpage();
		$pages = ceil($_result[ 'total' ] / $limit);


		$im   = BACKEND_IMAGE_PATH;
		$data = array ();
		foreach ( $_result[ 'result' ] as $rs )
		{

			$registry      = $this->getApplication()->getModulRegistry($rs[ "module" ]);
			$modulRegistry = isset($registry[ 'definition' ]) ? $registry[ 'definition' ] : array ();

			$publish = $this->getGridState($rs[ 'published' ], $rs[ 'id' ], 0, 0, 'admin.php?adm=modules&amp;action=publish&amp;id=', 'refreshAfterModulPublishingChange' );

			$delete = '<a class="deinstall ajax" href="admin.php?adm=modules&amp;action=delete&amp;id=' . $rs[ 'id' ] . '" label="' . htmlspecialchars(!empty($modulRegistry[ "modulelabel" ]) ?
					$modulRegistry[ "modulelabel" ] :
					$rs[ "module" ]) . '" ><img src="' . $im . 'delete.png" border="0" alt="" title="' . trans('Modul Deinstallieren') . '"/></a>';

			$disable = '';
			if ( isset($modulRegistry[ "version" ]) && !empty($modulRegistry[ "version" ]) )
			{
				if ( version_compare($modulRegistry[ "version" ], $rs[ "version" ]) <= 0 )
				{
					$disable = ' disabled';
				}
			}

			$disableCfg = '';
			if ( !$rs[ 'configurable' ] )
			{
				$disableCfg = ' disabled';
			}


			$update = '<a class="update' . $disable . ' ajax" href="admin.php?adm=modules&amp;action=update&amp;id=' . $rs[ 'id' ] . '"><img src="' . $im . 'installer_box.png" border="0" alt="" title="' . trans('Aktualisieren') . '" /></a>';

			$edit = '<a class="edit' . $disableCfg . '" href="admin.php?adm=modules&amp;action=settings&amp;id=' . $rs[ 'id' ] . '"><img src="' . $im . 'settings.png" border="0" alt="" title="' . trans('Einstellungen') . '" /></a>';


			if ( isset($coreModules[ $rs[ "module" ] ]) )
			{
				$publish = '<img src="' . $im . 'spacer.gif" width="16" border="0" alt="" class="disabled" />';
				$delete  = '<img src="' . $im . 'delete.png" width="16" border="0" alt="" class="disabled" />';
			}


			$rs[ 'options' ] = <<<EOF
             {$edit} {$update} {$delete}
EOF;

			$row = $this->Grid->addRow($rs);
			$row->addFieldData("module", !empty($modulRegistry[ "modulelabel" ]) ? $modulRegistry[ "modulelabel" ] :
				$rs[ "module" ]);
			$row->addFieldData("description", isset($modulRegistry[ "moduledescription" ]) ?
				$modulRegistry[ "moduledescription" ] : '');
			$row->addFieldData("version", $rs[ "version" ]);
			$row->addFieldData("published", $publish);
			$row->addFieldData("options", $rs[ 'options' ]);
		}


		$griddata = $this->Grid->renderData($_result[ 'total' ]);

		if ( HTTP::input('getGriddata') )
		{
			$data               = array ();
			$data[ 'success' ]  = true;
			$data[ 'total' ]    = $_result[ 'total' ];
			$data[ 'datarows' ] = $griddata[ 'rows' ];

			echo Library::json($data);
			exit;
		}

		/*

		$griddata[ 'sort' ]    = $GLOBALS[ 'sort' ];
		$griddata[ 'orderby' ] = $GLOBALS[ 'orderby' ];
		$griddata[ 'total' ]   = $_result[ 'total' ];
		$griddata[ 'searchitems' ]  = json_encode($griddata[ 'searchitems' ]);
		$griddata[ 'colModel' ]     = json_encode($griddata[ 'colModel' ]);
		$griddata[ 'gridActions' ]  = json_encode($griddata[ 'gridActions' ]);
		$griddata[ 'activeFilter' ] = json_encode($griddata[ 'activeFilter' ]);
		$griddata[ 'datarows' ]     = json_encode($griddata[ 'rows' ]);
*/

		$data                  = array ();
		//$data[ 'grid' ]        = $griddata;
		$data[ 'showinstall' ] = ($_result[ 'total' ] === count($this->getApplication()->getModulRegistry()) ? false :true);


		Library::addNavi(trans('Module'));
		$this->Template->process('modules/index', $data, true);
	}

	private function _processFrontend ()
	{

	}

}

?>