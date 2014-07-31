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
 * @package      Widgets
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         List.php
 */
class Widgets_Action_List extends Widgets_Helper_Base
{

	public function execute ()
	{

		$_installed    = array ();
		$_notinstalled = array ();


		$all       = $this->getAllWidgets();
		$installed = $this->model->getInstalledWidgets();
		$used      = $this->model->getAllUserWidgets();

		foreach ( $installed as $r )
		{
			if ( $r[ 'widgetkey' ] && !isset($_installed[ strtolower($r[ 'widgetkey' ]) ]) )
			{
				$_installed[ strtolower($r[ 'widgetkey' ]) ] = $r['id'];
			}
		}

		$xml = new Xml();

		foreach ( $all as $r )
		{
			if ( !isset($_installed[ strtolower($r[ 'dirname' ]) ]) )
			{

				if ( is_file(WIDGET_PATH . $r[ 'dirname' ] . '/' . strtolower($r[ 'dirname' ]) . '.xml') )
				{
					$content = file_get_contents(WIDGET_PATH . $r[ 'dirname' ] . '/' . strtolower($r[ 'dirname' ]) . '.xml');
					$arr     = $xml->createArray($content);

					unset($content);

					if ( !isset($arr[ 'widget' ]) )
					{
						continue;
					}
					$wgt = $arr[ 'widget' ];

				}
				else
				{
					continue;
				}

				$wgt[ 'widget' ]  = strtolower($r[ 'dirname' ]);
				$_notinstalled[ ] = $wgt;
			}
		}



		foreach ($used as $r )
		{
			foreach ($installed as &$rs) {
				if ($r['widget'] == $rs['widgetkey']) {
					$rs['userwidget'] = true;
				}
			}
		}





		unset($_installed);

		if ( $this->input('refresh') )
		{
			$buffer = $this->Template->process('widget/index', array (
			                                                         'installed'    => $installed,
			                                                         'notinstalled' => $_notinstalled,
			                                                         'used'         => $used
			                                                   ), null, 'available_widgets');
			echo Library::json(array ( 'success' => true, 'table' => $buffer ));
			exit;
		}

		$code = $this->Template->process('widget/index', array (
		                                                       'installed'    => $installed,
		                                                       'notinstalled' => $_notinstalled,
		                                                       'used'         => $used
		                                                 ), null, 'content');


		echo Library::json(array ( 'success' => true, 'template' => $code ));
		exit;
	}


}
