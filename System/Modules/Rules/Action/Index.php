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
 * @package      Rules
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Rules_Action_Index extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}
		$arr = array (
			''        => '---------------------------',
			'online'  => 'Active',
			'offline' => 'Deaktivierte',
		);


		$states = array ();
		foreach ( $arr as $k => $v )
		{
			$states[ $k ] = $v;
		}


		$this->load('Grid');
		$this->Grid->initGrid('routermap', 'ruleid', 'controller', 'desc')->setGridDataUrl('admin.php?adm=rules&action=index')->addGridEvent('onAfterLoad', 'function(){ registerToggleEvent(); }');

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
			                             'name'   => 'state',
			                             'type'   => 'select',
			                             'select' => $states,
			                             'label'  => 'Status',
			                             'show'   => false
		                             ),
		                             array (
			                             'submitbtn' => true
		                             ),
		                       ));

		$this->Grid->addHeader(array (
		                             // sql feld						 header	 	sortieren		standart
		                             array (
			                             "field"   => "controller",
			                             "content" => 'Controller',
			                             'width'   => '10%',
			                             "sort"    => "controller",
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "action",
			                             "content" => 'Action',
			                             'width'   => '10%',
			                             "sort"    => "action",
			                             "default" => true
		                             ),
		                             array (
			                             'islabel' => true,
			                             "field"   => "rule",
			                             "content" => 'Rule',
			                             "sort"    => "rule",
			                             "default" => true,
			                             'align'   => 'tl'
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

		# $lAdmin->runAfterInit = 'registerDelConfirm();registerToggleEvent()';

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
			case 'controller':
				$order = " ORDER BY controller";
				break;

			case 'rule':
				$order = " ORDER BY rule";
				break;

			case 'published':
				$order = " ORDER BY published";
				break;

			case 'action':
				$order = " ORDER BY action";
				break;

			default:
				$order = " ORDER BY controller";
				break;
		}


		switch ( HTTP::input('state') )
		{
			case 'online':
				$where[ ] = ' published = 1';
				break;
			case 'offline':
				$where[ ] = ' published = 0';
				break;
			default:
				$where[ ] = ' published >= 0';
				break;
		}


		$search = '';
		$search = HTTP::input('q');
		$search = trim((string)strtolower($search));
		$all    = null;

		$_s = '';
		if ( $search != '' )
		{
			$search = $this->db->quote('%' . str_replace("*", "%", $search) . '%');

			$_s = " AND ( LOWER(controller) LIKE " . $search;
			$_s .= "OR LOWER(action) LIKE " . $search;
			$_s .= "OR LOWER(rule) LIKE " . $search . ")";
		}


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


		// get the total number of records
		$sql = "SELECT COUNT(ruleid) AS total FROM %tp%routermap " . (count($where) ?
				"\nWHERE " . implode(' AND ', $where) : "") . (!count($where) ? "\nWHERE " . $_s : $_s);
		$r   = $this->db->query_first($sql);

		$total             = $r[ 'total' ];
		$limit             = $this->getPerpage();
		$this->dataresults = $total;
		$pages             = ceil($total / $limit);


		$sql    = "SELECT * FROM %tp%routermap" . (count($where) ? "\nWHERE " . implode(' AND ', $where) :
				"") . (!count($where) ? "\nWHERE " . $_s :
				$_s) . " " . $order . ' ' . $sort . " LIMIT " . ($limit * ($page - 1)) . "," . $limit;
		$result = $this->db->query($sql)->fetchAll();

		$im = BACKEND_IMAGE_PATH;
		foreach ( $result as $rs )
		{
			$img    = (!empty($rs[ 'published' ]) ? 'online.gif' : 'offline.gif');
			$edit   = $this->linkIcon("adm=rules&action=edit&id={$rs['ruleid']}&edit=1", 'edit', htmlspecialchars(sprintf(trans('Rule %s bearbeiten'), $rs[ 'rule' ])));
			$delete = $this->linkIcon("adm=rules&action=delete&id={$rs['ruleid']}", 'delete', trans('Löschen'));

			$publish = $this->getGridState((isset($rs[ 'draft' ]) && !empty($rs[ 'draft' ]) ? DRAFT_MODE :
				$rs[ 'published' ]), $rs[ 'ruleid' ], 0, 0, 'admin.php?adm=rules&amp;action=publish&amp;id=');

			$rs[ 'options' ] = <<<EOF
             {$edit} &nbsp; {$delete}
EOF;

			$str = '';
			if ( $rs[ 'optionalmap' ] )
			{
				$maps = unserialize($rs[ 'optionalmap' ]);
				$str  = $rs[ 'rule' ];
				foreach ( $maps[ 'attribute' ] as $idx => $mapparam )
				{
					$str = str_replace(':' . $mapparam, $maps[ 'match' ][ $idx ], $str);
				}
			}


			$row = $this->Grid->addRow($rs);
			$row->addFieldData("controller", $rs[ "controller" ]);
			$row->addFieldData("action", $rs[ 'action' ]);
			$row->addFieldData("rule", ($str ?
				'<a href="#">' . $rs[ 'rule' ] . '</a><br/><div class="rule" style="display:none">' . $str . '</div>' :
				$rs[ 'rule' ]));
			$row->addFieldData("published", $publish);
			$row->addFieldData("options", $rs[ 'options' ]);
		}


		$griddata = $this->Grid->renderData($total);

		if ( HTTP::input('getGriddata') )
		{
			$data[ 'success' ]  = true;
			$data[ 'total' ]    = $total;
			$data[ 'datarows' ] = $griddata[ 'rows' ];

			echo Library::json($data);
			exit;
		}

		Library::addNavi(trans('Router Rules Übersicht'));
		$this->Template->process('router/index', array (), true);
		exit;
	}

}

?>