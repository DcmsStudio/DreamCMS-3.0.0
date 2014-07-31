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
 * @package      Asset
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Asset_Action_Index extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->getApplication()->getMode() === Application::BACKEND_MODE )
		{
			$this->_processBackend();
		}
		else
		{
			throw new BaseException('Invalid request');
		}
	}

	private function _processBackend ()
	{

		$arr = array (
			''            => '---------------------------',
			'name'        => 'Name',
			'description' => 'Beschreibung',
			'content'     => 'Statischen Inhalt',
		);

		$searchin = array ();
		foreach ( $arr as $k => $v )
		{
			$searchin[ $k ] = $v;
		}


		$this->load('Grid');
		$this->Grid
			->initGrid('assets', 'id', 'name', 'desc')
			->setGridDataUrl('admin.php?adm=asset');





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
			                             'name'   => 'searchin',
			                             'type'   => 'select',
			                             'select' => $searchin,
			                             'label'  => 'Suchen in',
			                             'show'   => false
		                             ),
		                       ));

		$this->Grid->addHeader(array (
		                             // sql feld						 header	 	sortieren		standart
		                             array (
			                             'islabel' => true,
			                             "field"   => "name",
			                             "content" => 'Name',
			                             'width'   => '25%',
			                             "sort"    => "name",
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "description",
			                             "content" => 'Beschreibung',
			                             "sort"    => "description",
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "published",
			                             "sort"    => "published",
			                             "content" => 'Publish',
			                             'width'   => '10%',
			                             "default" => true,
			                             'align'   => 'tc'
		                             ),
		                             array (
			                             "field"   => "options",
			                             "content" => 'Optionen',
			                             'width'   => '10%',
			                             "default" => true,
			                             'align'   => 'tc'
		                             ),
		                       ));

		$this->Grid->addActions(array (
		                              'publish'   => trans('Veröffentlichen'),
		                              "unpublish" => trans('nicht Veröffentlichen'),
		                              "delete"    => array (
			                              'label' => trans('Löschen'),
			                              'msg'   => trans('Ausgewählte Assets wird komplett gelöscht. Möchten Sie Fortsetzen?')
		                              )
		                        ));

		switch ( $GLOBALS[ 'sort' ] )
		{
			case 'asc':
				$sort = " ASC";
				break;

			case 'desc':
				$sort = " DESC";
				break;

			default:
				$sort = "DESC";
				break;
		}

		switch ( $GLOBALS[ 'orderby' ] )
		{
			case 'name':
				$order = " ORDER BY name";
				break;

			case 'published':
				$order = " ORDER BY published";
				break;

			case 'description':
				$order = " ORDER BY description";
				break;

			default:
				$order = " ORDER BY name";
				break;
		}


		$search = '';
		$search = HTTP::input('q');
		$search = trim((string)strtolower($search));
		$all    = null;

		$_s = '';
		$_s = 'pageid = ' . PAGEID;
		if ( $search != '' )
		{
			$search = $this->db->quote('%' . str_replace("*", "%", $search) . '%');

			switch ( HTTP::input('searchin') )
			{
				case 'name':
					$_s = "name LIKE " . $search;
					break;

				case 'description':
					$_s = "description LIKE " . $search;
					break;
				case 'content':
					$_s = "content LIKE " . $search;
					break;
				default:
					$_s = "( LOWER(description) LIKE " . $search;
					$_s .= "OR LOWER(name) LIKE " . $search;
					$_s .= "OR LOWER(content) LIKE " . $search . ")";
					break;
			}
		}

		// get the total number of records
		$sql = "SELECT COUNT(id) AS total FROM %tp%assets " . ($_s ? "\nWHERE " . $_s : '');
		$r   = $this->db->query_first($sql);

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

		$im = BACKEND_IMAGE_PATH;
		// get the total number of records
		$sql    = "SELECT * FROM %tp%assets " . ($_s ? "\nWHERE " . $_s :
				'') . " " . $order . ' ' . $sort . " LIMIT " . ($limit * ($page - 1)) . "," . $limit;
		$result = $this->db->query($sql)->fetchAll();
		foreach ( $result as $rs )
		{

			$pubicon = $this->getGridState($rs[ 'published' ], $rs[ 'id' ], 0, 0, 'admin.php?adm=asset&amp;action=publish&amp;id=');
			$publish = '<a  href="javascript:void(0);" onclick="changePublish(\'pubs' . $rs[ 'ruleid' ] . '\',\'admin.php?adm=asset&amp;action=publish&amp;id=' . $rs[ 'id' ] . '\')">' . $pubicon . '</a>';
			$_e      = htmlspecialchars(sprintf($e, $rs[ "title" ]));
			$edit    = $this->linkIcon("adm=asset&action=edit&id={$rs['id']}", 'edit', htmlspecialchars(sprintf(trans('Asset `%s` bearbeiten'), $rs[ "name" ])));
			$delete  = $this->linkIcon("adm=asset&action=delete&id={$rs['id']}", 'delete');


			$rs[ 'options' ] = <<<EOF
             {$edit} &nbsp; {$delete}
EOF;

			$row = $this->Grid->addRow($rs);
			$row->addFieldData("name", $rs[ "name" ]);
			$row->addFieldData("description", $rs[ 'description' ]);
			$row->addFieldData("published", $publish);
			$row->addFieldData("options", $rs[ 'options' ]);
		}

		$griddata = $this->Grid->renderData($total);

		if ( HTTP::input('getGriddata') )
		{
			$data[ 'success' ] = true;
			$data[ 'total' ]   = $total;
			# $data['sort'] = $GLOBALS['sort'];
			# $data['orderby'] = $GLOBALS['orderby'];
			$data[ 'datarows' ] = $griddata[ 'rows' ];

			echo Library::json($data);
			exit;
		}

		Library::addNavi(trans('Assets'));
		$this->Template->process('assets/index', array (), true);

		exit;
	}

}

?>