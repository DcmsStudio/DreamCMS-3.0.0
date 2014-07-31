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
 * @file         Translation.php
 */
class Locale_Action_Translation extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}
		$model         = Model::getModelInstance('locale');
		$translationid = $this->input('id');

		if ( $this->input('translate') )
		{
			$rs              = $model->getTranslation((int)$translationid);
			$rs[ 'success' ] = ($rs[ 'id' ] ? true : false);
			echo Library::json($rs);
			exit;
		}


		$locales = $model->getLocales('title');

		$locale      = array ();
		$locale[ 0 ] = trans('alle Sprachen');
		foreach ( $locales AS $r )
		{
			$locale[ $r[ 'id' ] ] = $r[ 'title' ];
		}

		$this->load('Grid');
		$this->Grid
			->initGrid('locale_translations', 'id', 'original', 'asc')
			->setGridDataUrl('admin.php?adm=locale&action=translation')
			->addGridEvent('onAfterLoad', 'function () { registerTransEdit() }');

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
			                             'name'   => 'locale',
			                             'type'   => 'select',
			                             'select' => $locale,
			                             'label'  => trans('in Locale'),
			                             'show'   => true
		                             )
		                       ));

		$this->Grid->addHeader(array (
		                             // sql feld						 header	 	sortieren		standart
		                             array (
			                             'islabel' => true,
			                             "field"   => "original",
			                             'sort'    => 'original',
			                             "content" => trans('Original String'),
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "translation",
			                             'sort'    => 'translation',
			                             "content" => trans('Übersetzung'),
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "locale",
			                             'sort'    => 'locale',
			                             "content" => trans('Locale'),
			                             'width'   => '7%',
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


		$data = $model->getTranslationData();

		$default_localeid = $this->getDefaultLocaleId();

		$im = BACKEND_IMAGE_PATH;
		foreach ( $data[ 'result' ] as $rs )
		{
			$edit   = $this->linkIcon("adm=locale&action=" . strtolower(ACTION) . "&translate=1&id={$rs['id']}&edit=1", 'edit', trans('Bearbeiten'), 'edit');
			$delete = $this->linkIcon("adm=locale&amp;action=delete_translation&amp;id={$rs['id']}", 'delete');

			if ( $default_localeid == $rs[ 'localeid' ] )
			{
				$delete = '<img src="' . $im . 'delete.gif" border="0" alt="" title="' . trans('Löschen nicht möglich da vom System verwendet') . '" />';
			}

			$rs[ 'options' ] = <<<EOF
            {$edit} &nbsp; {$delete}
EOF;


			$row = $this->Grid->addRow($rs);
			$row->addFieldData("original", substr($rs[ "original" ], 0, 80));
			$row->addFieldData("translation", substr($rs[ 'translation' ], 0, 80));
			$row->addFieldData("locale", $rs[ 'code' ]);
			$row->addFieldData("options", $rs[ 'options' ]);
		}

		$griddata = $this->Grid->renderData($data[ 'total' ]);

		if ( HTTP::input('getGriddata') )
		{
			$_data               = array ();
			$_data[ 'success' ]  = true;
			$_data[ 'total' ]    = $data[ 'total' ];
			$_data[ 'datarows' ] = $griddata[ 'rows' ];

			echo Library::json($_data);
			exit;
		}

		$griddata[ 'sort' ]         = $GLOBALS[ 'sort' ];
		$griddata[ 'orderby' ]      = $GLOBALS[ 'orderby' ];
		$griddata[ 'total' ]        = $data[ 'total' ];
		$griddata[ 'searchitems' ]  = Json::encode($griddata[ 'searchitems' ]);
		$griddata[ 'colModel' ]     = Json::encode($griddata[ 'colModel' ]);
		$griddata[ 'gridActions' ]  = Json::encode($griddata[ 'gridActions' ]);
		$griddata[ 'activeFilter' ] = Json::encode($griddata[ 'activeFilter' ]);


		Library::addNavi(trans('Übersetzungen'));
		$this->Template->process('locale/list_translations', array (
		                                                           'grid' => $griddata
		                                                     ), true);
	}

}

?>