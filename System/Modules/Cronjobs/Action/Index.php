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
 * @package      Cronjobs
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Cronjobs_Action_Index extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}


		$this->load('Grid');
		$this->Grid->initGrid('cronjob_manager', 'job_id', 'job_title', 'asc')->setGridDataUrl('admin.php?adm=cronjobs');

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
		                             )
		                       ));

		$this->Grid->addHeader(array (
		                             // sql feld						 header	 	sortieren		standart
		                             array (
			                             'islabel' => true,
			                             "field"   => "job_title",
			                             "content" => trans('Titel'),
			                             'width'   => '20%',
			                             "sort"    => "module",
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "job_description",
			                             "content" => trans('Beschreibung'),
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "job_lastrun",
			                             "content" => trans('letzte ausf.'),
			                             'width'   => '15%',
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "job_next_run",
			                             "content" => trans('nächste ausf.'),
			                             'width'   => '15%',
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "job_month_day",
			                             "content" => trans('Tag im Monat'),
			                             'width'   => '10%',
			                             "sort"    => "version",
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "job_week_day",
			                             "content" => trans('Wochentag'),
			                             'width'   => '10%',
			                             "sort"    => "version",
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "job_hour",
			                             "content" => trans('Std.'),
			                             'width'   => '5%',
			                             "sort"    => "version",
			                             "default" => true,
			                             'align'   => 'tr'
		                             ),
		                             array (
			                             "field"   => "job_minute",
			                             "content" => trans('Min.'),
			                             'width'   => '5%',
			                             "sort"    => "version",
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "published",
			                             "sort"    => "published",
			                             "content" => trans('Publish'),
			                             'width'   => '6%',
			                             "default" => true,
			                             'align'   => 'tc'
		                             ),
		                             array (
			                             "field"   => "options",
			                             "content" => trans('Optionen'),
			                             'width'   => '8%',
			                             "default" => true,
			                             'align'   => 'tc'
		                             ),
		                       ));


		$_result = $this->model->getGridData();

		$limit = $this->getPerpage();
		$pages = ceil($_result[ 'total' ] / $limit);

		$im   = BACKEND_IMAGE_PATH;
		$e    = trans('`%s` barbeiten');
		$execute    = trans('Ausführen');
		$data = array ();
		foreach ( $_result[ 'result' ] as $rs )
		{
			$rs[ "job_lastrun" ]  = ( $rs[ 'job_lastrun' ] ? date('d.m.Y, H:i', $rs[ 'job_lastrun' ]) : trans('Unbekannt') );
			$rs[ "job_next_run" ] = ( $rs[ 'job_next_run' ] > 1 ? date('d.m.Y, H:i', $rs[ 'job_next_run' ]) : trans('Unbekannt') );


			$rs[ 'job_month_day' ] = ( $rs[ 'job_month_day' ] == -1 ? '*' : $rs[ 'job_month_day' ] . '.' );
			$rs[ 'job_week_day' ]  = ( $rs[ 'job_week_day' ] <= -1 ? '*' : Locales::getDayName($rs[ 'job_week_day' ], true) );
			$rs[ 'job_hour' ]      = $rs[ 'job_hour' ] == -1 ? '*' : ( $rs[ 'job_hour' ] < 10 ? '0' . $rs[ 'job_hour' ] : $rs[ 'job_hour' ] );
			$rs[ 'job_minute' ]    = $rs[ 'job_minute' ] == -1 ? '*' : ( $rs[ 'job_minute' ] < 10 ? '0' . $rs[ 'job_minute' ] : $rs[ 'job_minute' ] );


			$_e              = htmlspecialchars(sprintf($e, $rs[ "job_title" ]));
			$publish         = $this->getGridState($rs[ 'job_enabled' ], $rs[ 'job_id' ], 0, 0, 'admin.php?adm=cronjobs&amp;action=publish&amp;id=');
			$edit            = $this->linkIcon("adm=cronjobs&action=edit&id={$rs['job_id']}&edit=1", 'edit', $_e);
			$delete          = $this->linkIcon("adm=cronjobs&action=delete&id={$rs['job_id']}", 'delete');


			$ex = <<<E
<a href="admin.php?adm=cronjobs&action=execute&id={$rs['job_id']}" class="ajax">{$execute}</a>
E;


			$rs[ 'options' ] = $ex . ' '. $edit . ' ' . $delete;

			$row = $this->Grid->addRow($rs);
			$row->addFieldData("job_title", $rs[ "job_title" ]);
			$row->addFieldData("job_next_run", $rs[ "job_next_run" ]);
			$row->addFieldData("job_lastrun", $rs[ "job_lastrun" ]);
			$row->addFieldData("job_description", $rs[ "job_description" ]);
			$row->addFieldData("job_month_day", $rs[ "job_month_day" ]);
			$row->addFieldData("job_week_day", $rs[ "job_week_day" ]);
			$row->addFieldData("job_hour", $rs[ "job_hour" ]);
			$row->addFieldData("job_minute", $rs[ "job_minute" ]);
			$row->addFieldData("published", $publish);
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


		$data = array ();
		#$data[ 'grid' ] = $this->Grid->getJsonData($_result[ 'total' ]);

		Library::addNavi(trans('Cron Jobs'));
		$this->Template->process('cronjobs/index', $data, true);
	}

}

?>