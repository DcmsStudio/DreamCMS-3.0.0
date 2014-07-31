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
 * @package      Layouter
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Layouter_Action_Index extends Controller_Abstract
{

	public function execute ()
	{

		$data = array ();


		$skinid = (int)HTTP::input('skinid');
		if ( HTTP::input('skinid') && !$skinid )
		{
			Error::raise(trans('Es wurde keine Skin ID übergeben!'));
		}

		if ( IS_AJAX && (HTTP::input('load') && $skinid) || HTTP::input('getGriddata') )
		{
			$this->loadLayoutsFromSkin();
		}
		else
		{
			$data[ 'skins' ] = $this->db->query('SELECT * FROM %tp%skins ORDER BY title')->fetchAll();

			Library::addNavi(trans('Layouts'));
			$data[ 'isSingleWindow' ] = true;
			$data[ 'skinid' ]         = $skinid;
			$data[ 'nopadding' ]      = true;
			$data[ 'scrollable' ]     = false;
			$this->Template->process('layout/index', $data, true);
			exit;
		}
	}

	private function loadLayoutsFromSkin ()
	{

		$skinid = (int)HTTP::input('skinid');


		if ( !$skinid )
		{
			Library::sendJson(false, trans('Bitte wählen sie vorher einen Skin für den Sie Layouts verwalten/Erstellen möchen'));
		}


		$this->load('Grid');
		$this->Grid->setUiqid('layouts' . $skinid);
		$this->Grid->initGrid('layouts', 'id', 'title', 'asc');
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
		                             array (
			                             'name'   => 'doctype',
			                             'type'   => 'select',
			                             'select' => array (
				                             ''             => '--',
				                             'xhtml_trans'  => trans('XHTML Transitional'),
				                             'xhtml_strict' => trans('XHTML Strict'),
				                             'html_5' => trans('HTML 5'),
				                             'html_4' => trans('HTML 4')
			                             ),
			                             'label'  => 'Kategorie',
			                             'show'   => false
		                             ),
		                             array (
			                             'submitbtn' => true
		                             ),
		                       ));

		$this->Grid->addHeader(array (
		                             // sql feld						 header	 	sortieren		standart
		                             array (
			                             "field"   => "title",
			                             "content" => 'Layout Titel',
			                             "sort"    => "title",
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "doctype",
			                             "content" => 'Dokument Typ',
			                             'width'   => '15%',
			                             "sort"    => "doctype",
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "template",
			                             "content" => 'Template',
			                             'width'   => '15%',
			                             "sort"    => "template",
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "cols",
			                             "content" => 'Spalten',
			                             'width'   => '20%',
			                             "sort"    => "cols",
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "options",
			                             "content" => 'Optionen',
			                             'width'   => '8%',
			                             "default" => true,
			                             'align'   => 'tc'
		                             ),
		                       ));

		/*

		  $this->Grid->addActions(array(
		  'publish' => trans('Veröffentlichen'),
		  "unpublish" => trans('nicht Veröffentlichen'),
		  "delete" => array('label' => trans( 'Löschen' ), 'msg' => trans('') )
		  ));

		 */

		if ( HTTP::input('load') == 1 )
		{
			#HTTP::unsetRequest('load');
			#  $lAdmin->ignoreAjax = true;
			#  $lAdmin->runBeforeInit = 'setLayoutSkin()';
		}


		switch ( $GLOBALS[ 'sort' ] )
		{
			case 'asc':
				$sort = " ASC";
				break;

			case 'desc':
				$sort = " DESC";
				break;

			default:
				$sort = " ASC";
				break;
		}

		switch ( $GLOBALS[ 'orderby' ] )
		{

			case 'title':
			default:
				$order = " ORDER BY `title`";
				break;
			case 'doctype':
				$order = " ORDER BY `doctype`";
				break;
			case 'template':
				$order = " ORDER BY `template`";
				break;
			case 'cols':
				$order = " ORDER BY cols";
				break;
		}


		$r = $this->db->query('SELECT COUNT(id) AS total FROM %tp%layouts WHERE skinid = ? AND pageid = ?', $skinid, PAGEID)->fetch();

		$total             = $r[ 'total' ];
		$limit             = $this->getPerpage();
		$this->dataresults = $total;
		$pages             = ceil($total / $limit);

		if ( HTTP::input('page') > 0 )
		{
			$page = (int)HTTP::input('page');

			if ( $page == 0 )
			{
				$page = 1;
			}
		}
		else
		{
			$page = 1;
		}

		$result = $this->db->query('SELECT * FROM %tp%layouts
                                    WHERE skinid = ? AND pageid = ?' . $order . $sort . ' LIMIT ' . ($limit * ($page - 1)) . ',' . $limit, $skinid, PAGEID)->fetchAll();


		$e = trans('Layout %s bearbeiten');

		$im = BACKEND_IMAGE_PATH;
		foreach ( $result as $rs )
		{

			if ( $rs[ 'doctype' ] == 'xhtml_strict' )
			{
				$rs[ 'doctype' ] = trans('XHTML Strict');
			}
			elseif ( $rs[ 'doctype' ] == 'xhtml_trans' )
			{
				$rs[ 'doctype' ] = trans('XHTML Transitional');
			}


			switch ( $rs[ 'cols' ] )
			{
				case 'cols0-content':
					$rs[ 'cols' ] = trans('Nur die Hauptspalte');
					break;
				case 'cols0-content-margin':
					$rs[ 'cols' ] = trans('Nur die Hauptspalte (mit abstand)');
					break;
				case 'cols2-content-left':
					$rs[ 'cols' ] = trans('Hauptspalte und linke Spalte');
					break;
				case 'cols2-content-right':
					$rs[ 'cols' ] = trans('Hauptspalte und rechte Spalte');
					break;
				case 'cols3-left-content-right':
					$rs[ 'cols' ] = trans('Hauptspalte, linke und rechte Spalte');
					break;
			}


			$_e = sprintf($e, $rs[ "title" ]);

			$rs[ 'options' ] = <<<EOF
		<a class="doTab-applicationMenu" href="admin.php?adm=layouter&amp;action=edit&amp;skinid={$rs['skinid']}&amp;id={$rs['id']}"><img src="{$im}edit.png" border="0" alt="{$_e}" title="{$_e}" /></a>
		<a class="duplicate" href="admin.php?adm=layouter&amp;action=duplicate&amp;skinid={$rs['skinid']}&amp;id={$rs['id']}"><img src="{$im}duplicate.png" border="0" alt="Layout Duplizieren" title="Layout Duplizieren" /></a> <a class="delconfirm" href="admin.php?adm=layouter&amp;action=removelayout&amp;id={$rs['id']}"><img src="{$im}delete.png" border="0" alt="Löschen" title="Löschen" /></a>
EOF;
			$row             = $this->Grid->addRow($rs);
			$row->addFieldData("title", $rs[ "title" ]);
			$row->addFieldData("doctype", $rs[ 'doctype' ]);
			$row->addFieldData("template", $rs[ 'template' ]);
			$row->addFieldData("cols", $rs[ 'cols' ]);
			$row->addFieldData("options", $rs[ 'options' ]);
		}

		$griddata = $this->Grid->renderData($total);

		if ( HTTP::input('getGriddata') )
		{
			$data[ 'success' ]  = true;
			$data[ 'total' ]    = $total;
			$data[ 'sort' ]     = $GLOBALS[ 'sort' ];
			$data[ 'orderby' ]  = $GLOBALS[ 'orderby' ];
			$data[ 'datarows' ] = $griddata[ 'rows' ];

			echo Library::json($data);
			exit;
		}


		$skin = $this->db->query("SELECT * FROM %tp%skins WHERE id = ?", $skinid)->fetch();

		// Library::addNavi(trans('Layouter'));
		Library::addNavi(sprintf(trans('Layouts für den Skin %s'), $skin[ 'title' ]));


		$data                 = array (
			'grid' => $this->Grid->getJsonData($total)
		);
		$data[ 'skinid' ]     = $skin[ 'id' ];
		$data[ 'nopadding' ]  = true;
		$data[ 'scrollable' ] = false;

		$this->Template->process('layout/layoutlist', $data, true);
		exit;
	}

}

?>