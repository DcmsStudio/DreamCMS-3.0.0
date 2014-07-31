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
 * @package      Development
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Development_Action_Index extends Controller_Abstract
{

	public function execute ()
	{

		if ( !$this->isFrontend() )
		{

		}
	}

	/**
	 * @return array
	 */
	private static function mergePluginPOFiles ()
	{
		$db                = Database::getInstance();
		$plugins           = $db->query('SELECT `key` FROM %tp%plugin')->fetchAll();
		$processed_plugins = array ();
		foreach ( $plugins as $plugin )
		{
			$processed_plugins[ ] = $key = $plugin[ 'key' ];
			$files                = self::getPluginFiles($key);
			if ( !empty($files) )
			{
				self::compilePOTFile($files, $key . 'Plugin', '--keyword="p_trans:2"');
			}

			self::mergePOFiles($key . 'Plugin');
		}

		return $processed_plugins;
	}

	/**
	 * @return array
	 */
	private static function mergeWidgetPOFiles ()
	{

		$db                = Database::getInstance();
		$widgets           = $db->query('SELECT `widgetkey` FROM %tp%widget')->fetchAll();
		$processed_widgets = array ();
		foreach ( $widgets as $widget )
		{
			$processed_widgets[ ] = $key = $widget[ 'widgetkey' ];
			$files                = self::getWidgetFiles($key);
			if ( !empty($files) )
			{
				self::compilePOTFile($files, $key . 'Widget', '--keyword="p_trans:2"');
			}

			self::mergePOFiles($key . 'Widget');
		}

		return $processed_widgets;
	}

}

?>