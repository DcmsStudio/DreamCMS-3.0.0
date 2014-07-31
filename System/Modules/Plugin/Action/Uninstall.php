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
 * @package      Plugin
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Uninstall.php
 */
class Plugin_Action_Uninstall extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$plugin = (int)$this->input('id');

		if ( empty($plugin) )
		{
			Error::raise(trans('Plugin existiert nicht!'));
		}

		if ( empty($keys) )
		{
			$keys[ ] = $plugin;
		}

		$tmp = array ();

		foreach ( $keys as $id )
		{

			$data = $this->model->getPluginById($id);

			if ( $data )
			{
				$name = $data[ 'key' ];


				$installerClassName = 'Addon_' . ucfirst($name) . '_Installer';
				if ( class_exists($installerClassName, true) )
				{
					if ( is_callable(array (
					                       $installerClassName,
					                       'Uninstall'
					                 ))
					)
					{
						call_user_func(array (
						                     $installerClassName,
						                     'Uninstall'
						               ));
					}
				}


				$this->model->uninstallPlugin(strtolower($name));

				$tmp[ ] = $data[ 'name' ];
				Cache::delete('plugin_config_' . $name);
				Library::log('Install the Plugin ' . $data[ 'name' ], 'warn');
			}
		}

		Cache::delete('menu_user_' . User::getUserId());
		Cache::delete('interactive_plugins');
		Cache::delete('installed_plugins');

		SystemManager::syncEventHooks();

		if ( IS_AJAX )
		{
			Library::sendJson(true, sprintf(trans('Plugin `%s` wurde deinstalliert.'), implode(', ', $tmp)));
		}

		header('Location: admin.php?adm=plugin');

		exit;
	}

}
