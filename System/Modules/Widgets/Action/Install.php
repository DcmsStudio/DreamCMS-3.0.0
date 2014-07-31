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
 * @file         Install.php
 */
class Widgets_Action_Install extends Widgets_Helper_Base
{

	public function execute ()
	{

		$name = strtolower($this->input('widget'));

		$arr = $this->getWidgetById($name);
		$xml = new Xml();

		if ( is_file(WIDGET_PATH . ucfirst($name) . '/' . $name . '.xml') )
		{
			$content = file_get_contents(WIDGET_PATH . ucfirst($name) . '/' . $name . '.xml');
			$_arr    = $xml->createArray($content);
			unset($content);
			$wgt = $_arr[ 'widget' ];
		}

		// error if config not exists
		if ( !isset($wgt[ 'name' ]) )
		{
			Library::sendJson(false, 'The widget config file was not found!');
		}


		if ( $arr[ 'id' ] && $wgt[ 'version' ] === $arr[ 'version' ] )
		{
			// error if is identicly version of the widget
			Library::sendJson(false, trans('Dieses Widget kann nicht installiert werden, da die Version schon vorhanden ist'));
		}


		unset($xml);

		$str = $this->db->compile_db_insert_string(array (
		                                                 'title'          => $wgt[ 'name' ],
		                                                 'widgetkey'      => $name,
		                                                 'configurable'   => $wgt[ 'configurable' ],
		                                                 'collapsible'    => $wgt[ 'collapsible' ],
		                                                 'multiple'       => $wgt[ 'multiple' ],
		                                                 'author'         => $wgt[ 'author' ],
		                                                 'website'        => $wgt[ 'website' ],
		                                                 'description'    => $wgt[ 'description' ],
		                                                 'version'        => $wgt[ 'version' ],
		                                                 'externalconfig' => $wgt[ 'externalconfig' ] ? 1 : 0
		                                           ));

		$sql = "INSERT INTO %tp%widget ({$str['FIELD_NAMES']}) VALUES({$str['FIELD_VALUES']})";
		$this->db->query($sql);

		Library::log('Install Widget: ' . $arr[ 'name' ]);
		Library::sendJson(true, sprintf(trans('Das Widget %s wurde installiert'), $wgt[ 'name' ] .' '. $wgt[ 'version' ]) );
		exit;

	}

}
