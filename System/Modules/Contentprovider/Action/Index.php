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
 * @package      Contentprovider
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Contentprovider_Action_Index extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}


		$isCoreTag = (HTTP::input('coretags') ? true : false);

		$this->load('Grid');
		$this->Grid->setUiqid('provider' . $isCoreTag);
		$this->Grid->initGrid('provider', 'id', 'execution_order', 'asc');
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
		                       ));

		$this->Grid->addActions(array (
		                              "delete" => array (
			                              'label' => trans('Löschen'),
			                              'msg'   => trans('Ausgewählte Provider werden gelöscht. Inhalte die Provider verwenden, werden womöglich nicht mehr korrekt angezeigt. Wollen Sie fortsetzen?')
		                              )
		                        ));

		if ( !$isCoreTag )
		{
			$this->Grid->setGridDataUrl('admin.php?adm=contentprovider');

			$this->Grid->addHeader(array (
			                             // sql feld						 header	 	sortieren		standart
			                             array (
				                             "field"   => "name",
				                             "content" => trans('Name'),
				                             'width'   => '10%',
				                             "sort"    => "name",
				                             "default" => true
			                             ),
			                             array (
				                             'islabel' => true,
				                             "field"   => "title",
				                             "content" => trans('Titel'),
				                             'width'   => '20%',
				                             "sort"    => "title",
				                             "default" => true
			                             ),
			                             array (
				                             "field"   => "description",
				                             "content" => trans('Beschreibung'),
				                             "sort"    => "description",
				                             "default" => true
			                             ),
			                             array (
				                             "field"   => "execution_order",
				                             "content" => trans('Reihenfolge'),
				                             "sort"    => "order",
				                             "default" => true,
				                             'width'   => '8%',
				                             'align'   => 'tc'
			                             ),
			                             array (
				                             "field"   => "runnable",
				                             "content" => trans('Ausführbar'),
				                             "sort"    => "runnable",
				                             "default" => true,
				                             'width'   => '8%',
				                             'align'   => 'tc'
			                             ),
			                             array (
				                             "field"   => "system",
				                             "content" => trans('System-Vorgabe'),
				                             "default" => true,
				                             'width'   => '8%',
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
		}
		else
		{
			$this->Grid->setGridDataUrl('admin.php?adm=contentprovider&coretags=1');

			$this->Grid->addHeader(array (
			                             // sql feld						 header	 	sortieren		standart
			                             array (
				                             "field"   => "name",
				                             "content" => trans('Name'),
				                             'width'   => '10%',
				                             "sort"    => "name",
				                             "default" => true
			                             ),
			                             array (
				                             "field"   => "type",
				                             "content" => trans('Typ'),
				                             'width'   => '10%',
				                             "sort"    => "type",
				                             "default" => true
			                             ),
			                             array (
				                             'islabel' => true,
				                             "field"   => "title",
				                             "content" => trans('Titel'),
				                             'width'   => '20%',
				                             "sort"    => "title",
				                             "default" => true
			                             ),
			                             array (
				                             "field"   => "description",
				                             "content" => trans('Beschreibung'),
				                             "sort"    => "description",
				                             "default" => true
			                             ),
			                             array (
				                             "field"   => "execution_order",
				                             "content" => trans('Reihenfolge'),
				                             "sort"    => "order",
				                             "default" => true,
				                             'width'   => '8%',
				                             'align'   => 'tc'
			                             ),
			                             array (
				                             "field"   => "system",
				                             "content" => trans('System-Vorgabe'),
				                             "default" => true,
				                             'width'   => '8%',
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
		}


		$model = Model::getModelInstance();

		$_result = $model->getGridQuery($isCoreTag);

		$limit = $this->getPerpage();
		$pages = ceil($_result[ 'total' ] / $limit);

		$et = trans('Provider %s bearbeiten');

		$im = BACKEND_IMAGE_PATH;
		foreach ( $_result[ 'result' ] as $rs )
		{

			if ( $rs[ 'runnable' ] == 1 )
			{
				$runnable       = 'tick.png';
				$runnable_title = trans('Ausführbar');
			}
			else
			{
				$runnable       = "tick_off.png";
				$runnable_title = trans('nicht Ausführbar');
			}

			$_et = sprintf($et, $rs[ "title" ]);


			switch ( $rs[ "type" ] )
			{
				case 'link':
					$rs[ "type" ] = trans('Link');
					break;
				case 'title':
					$rs[ "type" ] = trans('Titel');
					break;
				case 'url':
					$rs[ "type" ] = trans('Url');
					break;
			}
			$edit = '';
			if ( !$rs[ 'system' ] )
			{
				$edit = $this->linkIcon("adm=contentprovider&action=edit&coretags=" . $isCoreTag . "&id={$rs['id']}", 'edit', htmlspecialchars($_et));
			}


			$delete = $this->linkIcon("adm=contentprovider&action=delete&coretags=" . $isCoreTag . "&ids={$rs['id']}", 'delete');

			if ( $rs[ 'system' ] == 1 )
			{
				$system       = 'tick.png';
				$delete       = '<img src="' . $im . 'spacer.gif" width="16" border="0" alt="" title="' . trans('Löschen') . '" />';
				$system_title = trans('System Vorgabe');
			}
			else
			{
				$system       = "tick_off.png";
				$system_title = trans('Ihre Vorgabe');
			}

			$runnable = '<img src="' . $im . $runnable . '" border="0" alt="" title="' . $runnable_title . '" />';
			$system   = '<img src="' . $im . $system . '" border="0" alt="" title="' . $system_title . '" />';

			$rs[ 'options' ] = <<<EOF
		{$edit} &nbsp; {$delete}
EOF;
			$row             = $this->Grid->addRow($rs);
			$row->addFieldData("title", $rs[ "title" ]);
			$row->addFieldData("description", $rs[ 'description' ]);
			$row->addFieldData("name", $rs[ 'name' ]);
			$row->addFieldData("type", $rs[ 'type' ]);
			$row->addFieldData("runnable", $runnable);
			$row->addFieldData("system", $system);
			$row->addFieldData("execution_order", $rs[ "execution_order" ]);
			$row->addFieldData("options", $rs[ 'options' ]);
		}

		$griddata = $this->Grid->renderData($_result[ 'total' ]);


		if ( HTTP::input('getGriddata') )
		{
			$data[ 'success' ]  = true;
			$data[ 'total' ]    = $_result[ 'total' ];
			$data[ 'datarows' ] = $griddata[ 'rows' ];

			echo Library::json($data);
			exit;
		}


		if ( $isCoreTag )
		{
			Library::addNavi(trans('Core-Tags'));
		}
		else
		{
			Library::addNavi(trans('Content Provider'));
		}

		$this->Template->process('contentprovider/index', array (), true);
	}

}

?>