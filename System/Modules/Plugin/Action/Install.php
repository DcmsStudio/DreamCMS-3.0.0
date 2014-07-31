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
 * @file         Install.php
 */
class Plugin_Action_Install extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$data[ 'plugins' ] = $this->getAvailablePlugins();

		$plugin = $this->input('pluginname');
		if ( !empty($plugin) && $plugin )
		{
			if ( !isset($data[ 'plugins' ][ $plugin ]) )
			{
				$name = ucfirst(strtolower($plugin));
				Library::sendJson(false, sprintf(trans('Das Plugin %s ist nicht vorhanden.'), $name));
			}

			$this->installPlugin($plugin);

			exit;
		}

		$this->Template->process('plugin/install', $data, true);
		exit;
	}

	/**
	 * @return array
	 */
	private function getAvailablePlugins ()
	{

		$available = array ();

		// read installed plugins
		$installed = $this->model->getInstalledPlugins();


		// read available plugins
		$plugins = glob(PLUGIN_PATH . '*', GLOB_ONLYDIR);
		foreach ( $plugins as $plugin )
		{
			$key = basename($plugin);
			if ( !in_array(strtolower($key), $installed) )
			{
				$className = 'Addon_' . ucfirst($key) . '_Config_Base';

				if ( class_exists($className, true) )
				{
					// Read modul definitions
					if ( checkClassMethod($className . '/getModulDefinition', 'static') )
					{
						$def                           = call_user_func($className . '::getModulDefinition', true);
						$def[ 'key' ]                  = strtolower($key);
						$available[ strtolower($key) ] = $def;
					}
				}
			}
		}


		return $available;
	}

	/**
	 *
	 * @param string $plugin
	 */
	private function installPlugin ( $plugin )
	{

		$name = ucfirst(strtolower($plugin));


		$className = 'Addon_' . $name . '_Config_Base';

		if ( class_exists($className, true) )
		{
			// Read modul definitions
			if ( checkClassMethod($className . '/getModulDefinition', 'static') )
			{

				$installerClassName = 'Addon_' . $name . '_Installer';

				if ( class_exists($installerClassName, true) )
				{
					if ( is_callable(array (
					                       $installerClassName,
					                       'Install'
					                 ))
					)
					{
						call_user_func(array (
						                     $installerClassName,
						                     'Install'
						               ));
					}
				}

				$def = call_user_func($className . '::getModulDefinition', true);


				$add = array (
					'key'         => strtolower($plugin),
					'name'        => (string)$def[ 'modulelabel' ],
					'version'     => (string)$def[ 'version' ],
					'description' => (string)$def[ 'moduledescription' ],
					'author'      => (string)$def[ 'author' ],
					'website'     => (string)$def[ 'website' ],
					'run'         => (int)$def[ 'run' ],
					'config'      => (int)$def[ 'config' ]
				);

				$newid = $this->model->installPlugin($add);

				Cache::delete('menu_user_' . User::getUserId());
				Cache::delete('interactive_plugins');
				Cache::delete('installed_plugins');

				SystemManager::syncEventHooks();

				Library::sendJson(true, sprintf(trans('Das Plugin (%s) wurde installiert'), $def[ 'modulelabel' ]), $newid);
			}

			Library::sendJson(false, sprintf(trans('Das von Ihnen zu installierende Plugin (%s) hat keine definition'), $name));
		}

		Library::sendJson(false, sprintf(trans('Das von Ihnen zu installierende Plugin (%s) existiert nicht'), $name));
	}

}
