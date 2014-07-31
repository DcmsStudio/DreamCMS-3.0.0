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
 * @package      Logs
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Logs_Action_Index extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}


		$model = Model::getModelInstance('logs');

		$arr = array (
			''        => '---------------------------',
			'name'    => trans('Benutzer'),
			'message' => trans('Meldung'),
			'ip'      => trans('IP Adresse'),
		);


		$searchin = array ();
		foreach ( $arr as $k => $v )
		{
			$searchin[ $k ] = $v;
		}


		$logtype         = array ();
		$logtype[ '' ]   = trans('Frontend und Backend Logs');
		$logtype[ 'fe' ] = trans('Frontend Logs');
		$logtype[ 'be' ] = trans('Backend Logs');


		$this->load('Grid');
		$this->Grid
			->initGrid('logs', 'id', 'time', 'desc')
			->setGridDataUrl('admin.php?adm=logs')
			->addGridEvent('onAfterLoad', 'function() { logsGridOnAfterLoad(); }');

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
			                             'name'   => 'searchin',
			                             'type'   => 'select',
			                             'select' => $searchin,
			                             'label'  => trans('Suchen in'),
			                             'show'   => false
		                             ),
		                             array (
			                             'name'   => 'logtype',
			                             'type'   => 'select',
			                             'select' => $logtype,
			                             'label'  => trans('Log Type'),
			                             'show'   => false
		                             ),
		                       ));

		$this->Grid->addHeader(array (
		                             // sql feld						 header	 	sortieren		standart
		                             array (
			                             "field"   => "logtype",
			                             "content" => trans('Typ'),
			                             'width'   => '3%',
			                             'align'   => 'tc',
			                             "sort"    => "logtype",
			                             "default" => true
		                             ),
		                             array (
			                             'islabel' => true,
			                             "field"   => "message",
			                             "content" => trans('Meldung'),
			                             "sort"    => "message",
			                             "default" => true
		                             ),
		                             array (

			                             "field"   => "username",
			                             "content" => trans('Benutzer'),
			                             'width'   => '12%',
			                             "sort"    => "name",
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "ip",
			                             "sort"    => "ip",
			                             "content" => trans('IP Adresse'),
			                             'width'   => '7%',
			                             "default" => true,
			                             'align'   => 'tr'
		                             ),
		                             array (
			                             "field"   => "time",
			                             "sort"    => "time",
			                             "content" => trans('Datum'),
			                             'width'   => '12%',
			                             "default" => true,
			                             'align'   => 'tr'
		                             ),
		                             array (
			                             "field"   => "options",
			                             "content" => trans('Optionen'),
			                             'width'   => '6%',
			                             "default" => true,
			                             'align'   => 'tc'
		                             ),
		                       ));

		$this->Grid->addActions(array (
		                              "delete" => array (
			                              'label' => trans('Löschen'),
			                              'msg'   => trans('Ausgewählte Logeinträge werden gelöscht. Wollen Sie fortsetzen?')
		                              )
		                        ));


		$_result = $model->getGridQuery();

		$limit = $this->getPerpage();
		$pages = ceil($_result[ 'total' ] / $limit);


		$this->load('Strings');
		$im = BACKEND_IMAGE_PATH;
        $edit = '';

		foreach ( $_result[ 'result' ] as $rs )
		{
			$delete          = '<a class="delconfirm ajax" href="admin.php?adm=logs&amp;action=delete&amp;id=' . $rs[ 'id' ] . '"><img src="' . $im . 'delete.png" border="0" alt="" title="' . trans('Löschen') . '" /></a>';
			$rs[ 'options' ] = <<<EOF
             {$edit} &nbsp; {$delete}
EOF;

			$rs[ 'time' ] = date("j. M. Y H:i:s", $rs[ 'time' ]);

			switch ( $rs[ 'logtype' ] )
			{
				case 'warn':
				case 'critical':
					$rs[ 'logtype' ] = trans('Warnung');
					$icon            = 'critical.png';
					break;
				case 'phperror':
				case 'error':
					$rs[ 'logtype' ] = trans('PHP Fehler');
					$icon            = 'not-ok.png';
					break;
				case 'info':
					$rs[ 'logtype' ] = trans('Info');
					$icon            = 'info.png';
					break;
				default:
					$icon = 'info.png';
					break;
			}


			$icon = sprintf('<img src="%s" width="16" height="16" alt="" title="%s" />', $im . $icon, $rs[ 'logtype' ]);

			$rs[ 'message' ] = preg_replace('#(<br\s*/?>){2,}#is', "\n", $rs[ 'message' ]);
			$rs[ 'message' ] = preg_replace('#\n{2,}#s', "\n", $rs[ 'message' ]);

			$rs[ 'message' ] = $this->Strings->Wrap(strip_tags(  preg_replace('#\n{2,}#s', "\n", $rs[ 'message' ]) ), 100, "\n ");


			$rs[ 'message' ] = '<a class="shortlog">' . substr(strip_tags($rs[ 'message' ]), 0, 120) . ' ...</a><div style="display:none" class="log">' . trans('Type:') . ' ' . $rs[ 'logtype' ] . '<br/>
                ' . trans('Nachricht:') . '<hr><pre>' . preg_replace('#\n{2,}#s', "\n", $rs[ 'message' ]) . '</pre><hr>
                ' . trans('User Agent:') . ' ' . $rs[ 'browser' ] . ' %s</div>';


			if ( $rs[ 'data' ] )
			{
				$rs[ 'message' ] = sprintf($rs[ 'message' ], '<div><a href="admin.php?adm=logs&amp;action=showfull&amp;id=' . $rs[ 'id' ] . '" class="advanced-infos">Advanced Infos</a></div>');
			}


			$row = $this->Grid->addRow($rs);

			$row->addFieldData("username", $rs[ "username" ]);
			$row->addFieldData("logtype", $icon);
			$row->addFieldData("message", $rs[ 'message' ]);
			$row->addFieldData("ip", $rs[ 'ip' ]);
			$row->addFieldData("time", $rs[ 'time' ]);
			$row->addFieldData("options", $rs[ 'options' ]);
		}

		$griddata = array ();
		$griddata = $this->Grid->renderData($_result[ 'total' ]);

		$data = array ();
		if ( HTTP::input('getGriddata') )
		{

			$data[ 'success' ] = true;
			$data[ 'total' ]   = $_result[ 'total' ];
			# $data['sort'] = $GLOBALS['sort'];
			# $data['orderby'] = $GLOBALS['orderby'];
			$data[ 'datarows' ] = $griddata[ 'rows' ];


			Ajax::Send(true, $data);
			exit;
		}

	//	$this->Template->addScript(BACKEND_JS_URL . 'dcms.googlemap.js');
		$this->Template->process('logs/index', array (
		                                             'grid' => $this->Grid->getJsonData($_result[ 'total' ])
		                                       ), true);
	}

}
