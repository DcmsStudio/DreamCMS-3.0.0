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
 * @file         Userinstall.php
 */
class Widgets_Action_Userinstall extends Widgets_Helper_Base
{

	public function execute ()
	{

		$id = (int)$this->input('id');

		$arr = $this->getWidgetById($id);
		$xml = new Xml();

		if ( is_file(WIDGET_PATH . ucfirst($arr[ 'widgetkey' ]) . '/' . $arr[ 'widgetkey' ] . '.xml') )
		{
			$content = file_get_contents(WIDGET_PATH . ucfirst($arr[ 'widgetkey' ]) . '/' . $arr[ 'widgetkey' ] . '.xml');
			$_arr    = $xml->createArray($content);
			unset($content);
			$wgt = $_arr[ 'widget' ];
		}


		// error if config not exists
		if ( !isset($wgt[ 'name' ]) )
		{
			Library::sendJson(false, 'The widget config file was not found!');
		}

		// check multiple instances
		if ( !$arr[ 'multiple' ] )
		{
			$rs = $this->model->getWidgetByName($arr[ 'widgetkey' ]);
			if ( $rs[ 'id' ] )
			{
				Library::sendJson(false, trans('Es darf leider nur ein Widget verwendet werden.'));
			}
		}


		$wgt[ 'widget' ]  = $arr[ 'widgetkey' ];
		$newid            = $this->model->installUserWidget($wgt);

		$w = $this->get_user_widget_by_id($newid);

		$wgt[ 'success' ] = true;
		$wgt[ 'widget' ]  = array_merge($arr, $w );
		$wgt[ 'widget' ]['name'] = $arr[ 'title' ];
		$wgt[ 'msg' ]     = trans('Das Widget wurde hinzugefügt');


		echo Library::json($wgt);
		exit;

	}

}
