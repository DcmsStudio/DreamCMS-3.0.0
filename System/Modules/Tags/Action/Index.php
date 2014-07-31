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
 * @package      Tags
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Tags_Action_Index extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}


		$this->load('Grid');
		$this->Grid->initGrid('tags', 'id', 'tag', 'asc')->setGridDataUrl('admin.php?adm=tags');

		$this->Grid->addHeader(array (
		                             // sql feld						 header	 	sortieren		standart
		                             array (
			                             'islabel' => true,
			                             "field"   => "tag",
			                             "content" => trans('Tag'),
			                             "sort"    => "tag",
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "hits",
			                             "content" => trans('Hits'),
			                             'width'   => '10%',
			                             "sort"    => "hits",
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "options",
			                             "content" => trans('Optionen'),
			                             'width'   => '10%',
			                             "default" => true,
			                             'align'   => 'tc'
		                             ),
		                       ));


		$_result = $this->model->getGridData();
		$im      = BACKEND_IMAGE_PATH;

		foreach ( $_result[ 'result' ] as $rs )
		{
			$delete = '<a class="delconfirm ajax" href="admin.php?adm=tags&amp;action=delete&amp;id=' . $rs[ 'id' ] . '"><img src="' . $im . 'delete.png" border="0" alt="" title="' . trans('Tag löschen') . '"/></a>';


			$rs[ 'options' ] = <<<EOF
                    
                 {$delete}   
EOF;


			$row = $this->Grid->addRow($rs);
			$row->addFieldData("hits", $rs[ "hits" ]);
			$row->addFieldData("tag", $rs[ "tag" ]);
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

		$data                  = array ();
		$data[ 'grid' ]        = $this->Grid->getJsonData($_result[ 'total' ]);
		$data[ 'showinstall' ] = ($_result[ 'total' ] === count($this->getApplication()->getModulRegistry()) ? false :
			true);


		Library::addNavi(trans('Tags'));
		$this->Template->process('tags/index', $data, true);
	}

}

?>