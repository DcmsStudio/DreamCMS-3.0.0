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
 * @package      Skins
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Skins_Action_Index extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}


		$this->load('Grid');
		$this->Grid
            ->initGrid('skins', 'id', 'title', 'asc')
            ->setGridDataUrl('admin.php?adm=skins')
            ->addGridEvent('onAfterLoad', 'function() { registerSkinButtons(); }');




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
		                             /*  array('name' => 'searchin', 'type' => 'select', 'select' => $searchin, 'label' => 'Suchen in', 'show' => false), */
		                       ));

		$this->Grid->addHeader(array (
		                             // sql feld						 header	 	sortieren		standart
		                             array (
			                             'islabel' => true,
			                             "field"   => "title",
			                             "content" => 'Titel',
			                             "sort"    => "title",
			                             'width'   => '',
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "author",
			                             "content" => 'Autor',
			                             "sort"    => "author",
			                             'width'   => '20%',
			                             "default" => true,
			                             'nowrap'  => true
		                             ),
		                             array (
			                             "field"   => "published",
			                             "content" => 'Aktiv',
			                             "sort"    => "published",
			                             'width'   => '7%',
			                             "default" => true,
			                             'align'   => 'tc'
		                             ),
		                             array (
			                             "field"   => "default_set",
			                             "content" => 'Standart',
			                             "sort"    => "default_set",
			                             'width'   => '7%',
			                             "default" => true,
			                             'align'   => 'tc'
		                             ),
		                             array (
			                             "field"   => "options",
			                             "content" => 'Optionen',
			                             "default" => true,
			                             'align'   => 'tc',
			                             'width'   => '15%'
		                             ),
		                       ));


		$perpage = $this->getPerpage(); // oder $GLOBALS['perpage']


		switch ( strtolower(HTTP::input('sort')) )
		{
			case "desc":
			default:
				$_sortby = " DESC";
				break;
			case "asc":
				$_sortby = " ASC";
				break;
		}


		switch ( $GLOBALS[ 'orderby' ] )
		{
			case 'title':
				$order = " ORDER BY title";
				break;

			case 'author':
				$order = " ORDER BY author";
				break;

			case 'default_set':
				$order = " ORDER BY default_set";
				break;

			default:
				$order = " ORDER BY title";
				break;
		}

		$rs          = $this->db->query("SELECT * FROM %tp%skins WHERE pageid = ? " . $order . $_sortby, PAGEID)->fetchAll();
		$dataresults = count($rs);

		$export   = trans('Exportieren');
		$et       = trans('Bearbeiten');
		$dt       = trans('Löschen');
		$relt     = trans('Templates erneuern');
		$at       = trans('aktivieren/deaktivieren');
		$st       = trans('Standart setzen');
		$listtemp = trans('Templates');
		$compile  = trans('Templates Compileren');
		$im       = BACKEND_IMAGE_PATH;

		foreach ( $rs as $r )
		{

			$defaultlink = ' <img src="' . $im . 'tick.png" width="16" height="16" />';
			$dellink     = ' <img src="' . $im . 'spacer.gif" width="16" height="16" />';
			$disable = '';
			if ( $r[ 'default_set' ] )
			{
				$disable = ' disabled';
			}
				$dellink = <<<EOF

            <a class="delconfirm {$disable}" href="admin.php?adm=skins&amp;action=delete&amp;id={$r['id']}"><img src="{$im}delete.png" width="16" height="16" title="{$dt}" class="absmiddle" /></a>
EOF;


			$pub_icon = ($r[ 'published' ] > 0 ?
				'<img id="pub' . $r[ 'id' ] . '" src="' . $im . 'online.gif" width="16" height="16" alt="' . $at . '" title="' . $at . '" class="absmiddle" />' :
				'<img id="pub' . $r[ 'id' ] . '" src="' . $im . 'offline.gif" width="16" height="16" title="' . $at . '" alt="' . $at . '" class="absmiddle" />');


			$default_set_icon = ($r[ 'default_set' ] ?
				'<img id="skindef' . $r[ 'id' ] . '" src="' . $im . 'online.gif" width="16" height="16" alt="' . $st . '" title="' . $st . '" class="absmiddle" />' :
				'<img id="skindef' . $r[ 'id' ] . '" src="' . $im . 'offline.gif" width="16" height="16" title="' . $st . '" alt="' . $st . '" class="absmiddle" />');


			$defaultlink = <<<EOF

            <a href="admin.php?adm=skins&amp;action=setdefault&amp;id={$r['id']}" rel="skindef{$r['id']}" class="ajax">{$default_set_icon}</a>

EOF;

			$publink = <<<EOF

            <a href="javascript:void(0)" onclick="changePublish('pub{$r['id']}', 'admin.php?adm=skins&amp;action=changepublish&amp;id={$r['id']}')">{$pub_icon}</a>
EOF;

			/**
			 * @todo using recompile?
			 */
			// <a href="javascript:void(0)" rel="skin_{$r['id']}" class="compile-templates"><img src="{$im}buttons/compile.png" height="16" title="{$compile}" alt=""/></a>&nbsp;

			$r[ 'options' ] = <<<EOF
            <a href="javascript:void(0)" rel="skin_{$r['id']}" class="regenerate-templates"><img src="{$im}document-reload.png" height="16" title="{$relt}" alt=""/></a>&nbsp;
            <a class="export-skin" href="admin.php?adm=skins&amp;action=export&amp;skinid={$r['id']}"><img src="{$im}document_export.png" title="{$export}" width="16" height="16"/></a>&nbsp;
            <a class="templates self" href="admin.php?adm=skins&amp;action=templates&amp;id={$r['id']}"><img src="{$im}templates.png" title="{$listtemp}" width="16" height="16" /></a>&nbsp;
            {$defaultlink}
            <a class="doTab" href="admin.php?adm=skins&amp;action=edit&amp;id={$r['id']}"><img src="{$im}edit.png" title="{$et}" width="16" height="16" /></a>&nbsp;
            {$dellink}

EOF;


			$row = $this->Grid->addRow($r);
			$row->addFieldData("title", $r[ "title" ]);
			$row->addFieldData("author", $r[ "author" ]);

			$row->addFieldData("published", $publink);
			$row->addFieldData("default_set", $defaultlink);
			$row->addFieldData("options", $r[ "options" ]);
		}

		$griddata = $this->Grid->renderData($dataresults);

		if ( HTTP::input('getGriddata') )
		{
			$data[ 'success' ]  = true;
			$data[ 'total' ]    = $dataresults;
			$data[ 'datarows' ] = $griddata[ 'rows' ];

			echo Library::json($data);
			exit;
		}

		Library::addNavi(trans('Frontend Skins Übersicht'));


		$this->Template->process('skins/index', array (
		                                              'grid'           => $this->Grid->getJsonData($dataresults),
		                                              'isSingleWindow' => true
		                                        ), true);
	}

}

?>