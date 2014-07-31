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
 * @package      Backup
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Backup_Action_Index extends Backup_Helper_BaseHelper
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$data    = array ();
		$backups = $this->getBackups();


		$this->load('Grid');
		$this->Grid
			->initGrid('backups', 'id', 'time', 'desc')
			->setGridDataUrl('admin.php?adm=backup')
			->addGridEvent('onAfterLoad', 'function () { registerBackupLoad(); }');

		$this->Grid->addHeader(array (
		                             // sql feld						 header	 	sortieren		standart
		                             array (
			                             'islabel' => true,
			                             "field"   => "file",
			                             "content" => trans('Datei'),
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "date",
			                             "content" => trans('Backup Datum'),
			                             'width'   => '25%',
			                             "default" => true,
			                             'align'   => 'tl'
		                             ),
		                             array (
			                             "field"   => "size",
			                             "content" => trans('Größe'),
			                             'width'   => '12%',
			                             'align'   => 'tr',
			                             "default" => true
		                             ),
		                             // array( "field" => "createdby", "content" => trans( 'erstellt von' ), 'width' => '20%', "default" => true, 'align' => 'tr' ),
		                             array (
			                             "field"   => "options",
			                             "content" => trans('Optionen'),
			                             'width'   => '10%',
			                             "default" => true,
			                             'align'   => 'tc'
		                             ),
		                       ));

		$this->Grid->addActions(array (
		                              "delete" => array (
			                              'label' => trans('Löschen'),
			                              'msg'   => trans('Ausgewählte Backups werden gelöscht. Wollen Sie fortsetzen?')
		                              )
		                        ));


		$im = BACKEND_IMAGE_PATH;
		$x  = 1;
		foreach ( $backups as $rs )
		{
			$delete = '<a class="delconfirm ajax" href="admin.php?adm=backup&amp;action=delete&amp;id=' . $x . '"><img src="' . $im . 'delete.png" border="0" alt="" title="' . trans('Löschen') . '" /></a>';

			$dl = '<a class="download" href="admin.php?adm=backup&amp;action=download&amp;id=' . $x . '"><img src="' . $im . 'buttons/drive-download.png" border="0" alt="" title="' . trans('Download') . '" /></a>';

			$rs[ 'options' ] = <<<EOF
              {$dl} {$delete}
EOF;

			$rs[ 'date' ] = date("j. M. Y, H:i:s", $rs[ 'date' ]);


			$row = $this->Grid->addRow($rs);
			$row->addFieldData("file", $rs[ 'name' ]);
			$row->addFieldData("size", $rs[ 'size' ]);
			$row->addFieldData("time", $rs[ 'date' ]);
			$row->addFieldData("options", $rs[ 'options' ]);

			$x++;
		}


		$griddata = $this->Grid->renderData(count($backups));

		if ( HTTP::input('getGriddata') )
		{

			$data[ 'success' ]  = true;
			$data[ 'total' ]    = count($backups);
			$data[ 'datarows' ] = $griddata[ 'rows' ];
			Ajax::Send(true, $data);
			exit;
		}

		Library::addNavi(trans('Backups'));

		$this->Template->process('backup/index', array (
		                                               'grid' => $this->Grid->getJsonData(count($backups))
		                                         ), true);
		exit;
	}

}

?>