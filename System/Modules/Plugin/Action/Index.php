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
 * @package      Plugin
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Plugin_Action_Index extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->getApplication()->getMode() === Application::BACKEND_MODE )
		{


			$this->load('Grid');
			$this->Grid
				->initGrid('plugin', 'id', 'name', 'asc')
				->setGridDataUrl('admin.php?adm=plugin')
				->addGridEvent('onAfterDelConfirm', 'function(event, data){
				if (typeof Launchpad != "undefined") { Launchpad.refresh(); }
				else {
					if (typeof Core != "undefined") {
						Core.refreshMenu();
					}
				}


				}')
				->addGridEvent('onAfterLoad', 'function(event, grid){ bindPluginGrid(event, grid); }');

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
				                             'submitbtn' => true
			                             ),
			                       ));

			$this->Grid->addHeader(array (
			                             // sql feld						 header	 	sortieren		standart
			                             array (
				                             'islabel' => true,
				                             "field"   => "name",
				                             "content" => trans('Plugin'),
				                             "sort"    => "name",
				                             "default" => true,
				                             'width'   => '30%',
				                             'nowrap'  => true
			                             ),
			                             array (
				                             "field"   => "description",
				                             "content" => trans('Beschreibung'),
				                             "sort"    => "description",
				                             "default" => true,
				                             'nowrap'  => true
			                             ),
			                             array (
				                             "field"   => "version",
				                             "content" => trans('Version'),
				                             'width'   => '10%',
				                             "sort"    => "version",
				                             'align'   => 'tl',
				                             "default" => true,
				                             'nowrap'  => true
			                             ),
			                             array (
				                             "field"   => "author",
				                             "content" => trans('Autor'),
				                             'width'   => '15%',
				                             "sort"    => "author",
				                             "default" => true,
				                             'nowrap'  => true
			                             ),
			                             array (
				                             "field"   => "published",
				                             "content" => trans('Aktiv'),
				                             "default" => true,
				                             'align'   => 'tc',
				                             'width'   => '8%'
			                             ),
			                             array (
				                             "field"   => "options",
				                             "content" => trans('Optionen'),
				                             "default" => true,
				                             'align'   => 'tc',
				                             'width'   => '10%'
			                             ),
			                       ));

			$res = $this->model->getGridData();

			foreach ( $res[ 'result' ] as $r )
			{
				$published = $this->getGridState($r[ 'published' ], $r[ 'id' ], 0, 0, 'admin.php?adm=plugin&action=publish&id=', 'Launchpad.refresh');

				$delete = $this->linkIcon("adm=plugin&amp;action=uninstall&id={$r['id']}", 'delete');

				$r[ 'options' ] = <<<EOF
			{$delete}

EOF;

				$row = $this->Grid->addRow($r);

				$row->addFieldData("name", $r[ 'name' ]);
				$row->addFieldData("description", $r[ 'description' ]);
				$row->addFieldData("version", $r[ 'version' ]);
				$row->addFieldData("author", $r[ 'author' ]);
				$row->addFieldData("published", $published);
				$row->addFieldData("options", $r[ 'options' ]);
			}

			$this->Grid->addActions(array (
			                              'publish'   => trans('Veröffentlichen'),
			                              "unpublish" => trans('nicht Veröffentlichen'),
			                              'uninstall' => trans('Deinstallieren')
			                        ));


			$griddata = $this->Grid->renderData($res[ 'total' ]);

			if ( HTTP::input('getGriddata') )
			{
				$data[ 'success' ]  = true;
				$data[ 'total' ]    = $res[ 'total' ];
				$data[ 'datarows' ] = $griddata[ 'rows' ];

				echo Library::json($data);
				exit;
			}


			Library::addNavi(trans('Plugins'));
			$this->Template->process('plugin/index', array (), true);
		}
	}

}

?>