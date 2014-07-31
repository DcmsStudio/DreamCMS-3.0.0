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
 * @package      Transform
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Transform_Action_Index extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}


		$model = Model::getModelInstance();

		$this->load('Grid');
		$this->Grid->initGrid('transform', 'id', 'title', 'asc');
		$this->Grid->addHeader(array (
		                             // sql feld						 header	 	sortieren		standart
		                             array (
			                             "field"   => "title",
			                             "content" => trans('Name'),
			                             'width'   => '20%',
			                             "sort"    => "title",
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "description",
			                             "content" => trans('Beschreibung'),
			                             "sort"    => "code",
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


		$_result = $model->getGridData();

		$limit = $this->getPerpage();
		$pages = ceil($_result[ 'total' ] / $limit);


		$im = BACKEND_IMAGE_PATH;
		$t  = trans('Transformation %s bearbeiten');
		foreach ( $_result[ 'result' ] as $rs )
		{
			$t      = sprintf($t, $rs[ "title" ]);
			$edit   = '<a class="doTab" href="admin.php?adm=transform&amp;action=edit&amp;id=' . $rs[ 'id' ] . '"><img src="' . $im . 'edit.png" border="0" alt="" title="' . $t . '" /></a>';
			$delete = '<a class="delconfirm ajax" href="admin.php?adm=transform&amp;action=delete&amp;id=' . $rs[ 'id' ] . '"><img src="' . $im . 'delete.png" border="0" alt="" title="' . trans('Löschen') . '" /></a>';

			$rs[ 'options' ] = <<<EOF
             {$edit} &nbsp; {$delete}
EOF;
			$row             = $this->Grid->addRow($rs);
			$row->addFieldData("title", $rs[ "title" ]);
			$row->addFieldData("description", $rs[ 'description' ]);
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

		Library::addNavi(trans('Bild-Transformationen Übersicht'));
		$this->Template->process('transformation/index', array (
		                                                       'grid' => $this->Grid->getJsonData($_result[ 'total' ])
		                                                 ), true);

		exit;
	}

}

?>