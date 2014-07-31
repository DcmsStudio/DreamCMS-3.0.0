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
 * @package      Eventmanager
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Eventmanager_Action_Index extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->getApplication()->getMode() !== Application::BACKEND_MODE )
		{
			return;
		}

		$this->load('Grid');
		$this->Grid
			->initGrid('event', 'id', 'event', 'asc')
			->setGridDataUrl('admin.php?adm=eventmanager')
			->addGridEvent('onAfterLoad', 'function(data, gridInst) { eventManagerGridonAfterLoad(data, gridInst); }');

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
		                       ));

		$this->Grid->addHeader(array (
		                             // sql feld						 header	 	sortieren		standart
		                             array (
			                             'islabel' => true,
			                             "field"   => "event",
			                             "content" => trans('Event'),
			                             "sort"    => "event",
			                             "default" => true,
			                             'nowrap'  => true
		                             ),
		                             array (
			                             "field"   => "description",
			                             "content" => trans('Beschreibung'),
			                             "sort"    => "description",
			                             'width'   => '44%',
			                             "default" => true,
			                             'nowrap'  => true
		                             ),
		                             array (
			                             "field"   => "context",
			                             "content" => trans('Context'),
			                             'width'   => '20%',
			                             "sort"    => "context",
			                             "default" => true,
			                             'nowrap'  => true
		                             ),
		                             array (
			                             "field"   => "hooks",
			                             "content" => trans('Hooks'),
			                             'width'   => '8%',
			                             "default" => true,
			                             'nowrap'  => true
		                             ),
		                             array (
			                             "field"   => "options",
			                             "content" => trans('Optionen'),
			                             'width'   => '8%',
			                             "default" => true,
			                             'nowrap'  => true,
			                             'align'   => 'tc'
		                             ),
		                       ));


		$_result = $this->model->getGridData();


		$limit = $this->getPerpage();
		$pages = ceil($_result[ 'total' ] / $limit);

		$tmp = array ();
		foreach ( $_result[ 'result' ] as $rs )
		{
			$tmp[ ] = $rs[ 'event' ];
		}

		$found = $this->model->getEventHooks($tmp);
		$tmp   = null;


		$e = trans('Hooks');
		$a = trans('hinzufügen');

		$path = BACKEND_IMAGE_PATH;


		foreach ( $_result[ 'result' ] as $rs )
		{

			if ( isset($found[ 'found_hooks' ][ $rs[ 'event' ] ]) )
			{
				$rs[ 'hooks' ] = $found[ 'found_hooks' ][ $rs[ 'event' ] ];
			}
			else
			{
				$rs[ 'hooks' ] = 0;
			}

			$disable = (!$rs[ 'hooks' ] ? ' disabled' : '');

			$e = sprintf($e, $rs[ "event" ]);
			$a = sprintf($a, $rs[ "event" ]);

			$rs[ 'options' ] = <<<EOF
			<a href="admin.php?adm=eventmanager&action=edit&event={$rs['event']}" class="event-edit{$disable}"><img src="{$path}event-edit.png" alt="" title="{$e}"/></a> &nbsp;
			<a href="admin.php?adm=eventmanager&action=add&event={$rs['event']}" class="event-add"><img src="{$path}event-install.png" alt="" title="{$a}"/></a>
EOF;
			$row             = $this->Grid->addRow($rs);
			$row->addFieldData("event", $rs[ "event" ]);
			$row->addFieldData("description", $rs[ 'description' ]);
			$row->addFieldData("context", $rs[ 'context' ]);
			$row->addFieldData("hooks", $rs[ "hooks" ]);
			$row->addFieldData("options", $rs[ 'options' ]);
		}


		$found = null;


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


		Library::addNavi(trans('Event Manager'));
		$this->Template->process('events/index', array (), true);
	}

}

?>