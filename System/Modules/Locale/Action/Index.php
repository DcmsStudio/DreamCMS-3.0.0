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
 * @package      Locale
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Locale_Action_Index extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$model = Model::getModelInstance('locale');

		$this->load('Grid');
		$this->Grid
			->initGrid('locale', 'id', 'title', 'asc')
			->setGridDataUrl('admin.php?adm=locale');

		$this->Grid->addHeader(array (
		                             // sql feld						 header	 	sortieren		standart
		                             array (
			                             'islabel' => true,
			                             "field"   => "title",
			                             "content" => trans('Titel'),
			                             "sort"    => "title",
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "code",
			                             "content" => trans('Locale auf Unix Servern'),
			                             "sort"    => "code",
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "wincode",
			                             "content" => trans('Locale auf Windows&trade; Servern'),
			                             "sort"    => "wincode",
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "guilanguage",
			                             'sort'    => 'guilanguage',
			                             "content" => trans('GUI'),
			                             'align'   => 'tc',
			                             'width'   => '6%',
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "contentlanguage",
			                             'sort'    => 'contentlanguage',
			                             "content" => trans('Inhalte'),
			                             'align'   => 'tc',
			                             'width'   => '6%',
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "options",
			                             "content" => trans('Optionen'),
			                             'width'   => '6%',
			                             "default" => true,
			                             'align'   => 'tc'
		                             ),
		                       ));

		$data = $model->getData();

		$im = BACKEND_IMAGE_PATH;
		foreach ( $data[ 'result' ] as $rs )
		{
			$transedit = '<a class="doTab" href="admin.php?adm=locale&amp;action=translation&amp;id=' . $rs[ 'id' ] . '"><img src="' . $im . 'book.png" border="0" alt="" title="' . trans('Übersetzungen') . '" /></a>';
			$edit      = $this->linkIcon("adm=locale&amp;action=edit&amp;id={$rs['id']}", 'edit', trans('Bearbeiten'));
			$delete    = $this->linkIcon("adm=locale&amp;action=delete&amp;id={$rs['id']}", 'delete');

			$rs[ 'options' ] = <<<EOF
             {$transedit} {$edit} {$delete}
EOF;

			$flag = Html::createTag(array (
			                              'tagname'    => 'img',
			                              'attributes' => array (
				                              'width'  => 16,
				                              'height' => 16,
				                              'src'    => BACKEND_IMAGE_PATH . 'flags/' . $rs[ 'flag' ]
			                              )
			                        ));

			$row = $this->Grid->addRow($rs);
			$row->addFieldData("title", $flag . ' ' . $rs[ "title" ]);
			$row->addFieldData("code", $rs[ 'code' ]);
			$row->addFieldData("wincode", $rs[ 'wincode' ]);

			$rs[ 'guilanguage' ]     = ($rs[ 'guilanguage' ] ? 'tick.png' : 'tick_off.png');
			$rs[ 'contentlanguage' ] = ($rs[ 'contentlanguage' ] ? 'tick.png' : 'tick_off.png');

			$row->addFieldData("guilanguage", '<img src="' . $im . $rs[ 'guilanguage' ] . '" alt=""/>');
			$row->addFieldData("contentlanguage", '<img src="' . $im . $rs[ 'contentlanguage' ] . '" alt=""/>');
			$row->addFieldData("options", $rs[ 'options' ]);
		}

		$griddata = $this->Grid->renderData($data[ 'total' ]);

		if ( HTTP::input('getGriddata') )
		{
			$_data[ 'success' ]  = true;
			$_data[ 'total' ]    = $data[ 'total' ];
			$_data[ 'datarows' ] = $griddata[ 'rows' ];

			$data = null;

			echo Library::json($_data);
			exit;
		}

		$this->Template->process('locale/index', array (), true);
	}

}

?>