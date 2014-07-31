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
 * @file         Run.php
 */
class Plugin_Action_Run extends Controller_Abstract
{

	/**
	 * @var null
	 */
	protected $pluginInstance = null;

	public function execute ()
	{

		$pluginInput = $this->input('plugin');
		$method      = (ACTION != 'index' ? ACTION : false);
		$plugin      = preg_replace('#([^a-zA-Z0-9]*)#', '', $pluginInput);

		if ( empty($plugin) || $plugin != $pluginInput )
		{
			$this->Page->error(404, sprintf(trans('Ungültiges Plugin (%s)!'), $pluginInput));
		}

		$plugin = strtolower($plugin);
		$plug   = $this->model->getPluginByName($plugin);
		if ( !$plug[ 'key' ] )
		{
			$this->Page->error(404, trans('Ungültiges Plugin oder das Plugin ist nicht installiert!'));
		}

		/**
		 *
		 */
		define('PLUGIN_PERMKEY', 'plugin_' . $plug[ 'key' ]);

		$this->isAddon = true;
		$this->load('Plugin');
		Plugin::registerPluginPerms($plug[ 'key' ]);
		$pluginInstance = Plugin::getPlugin($plug[ 'key' ]);


		if ( $this->isBackend() )
		{
			$method = HTTP::input('method') ? HTTP::input('method') : 'Index';

			if ( !in_array($method, get_class_methods($pluginInstance)) )
			{
				Error::raise(sprintf(trans('Plugin `%s` has no method `%s`.'), get_class($pluginInstance), $method));
			}


			$output = $pluginInstance->$method();
			$this->load('Template');


			if ( isset($output[ 'renderTemplate' ]) && $output[ 'renderTemplate' ] === true )
			{
				$template = Library::formatPath(PLUGIN_PATH . $output[ 'key' ] . '/template/' . $output[ 'template' ] . '.html');


				$parser = Library::getDcmsParser();


				$data                       = $output[ 'data' ];
				$data[ 'plugin' ][ 'key' ]  = $output[ 'key' ];
				$data[ 'plugin' ][ 'name' ] = $output[ 'key' ];
				$dat                        = $this->Template->_initBackendData();
				$data                       = array_merge($data, $dat);

				Library::disableErrorHandling();
				$html = $parser->get($template, $data);
				Library::enableErrorHandling();
				# print_r($data);
				$data             = array ();
				$data[ 'output' ] = $html;
				$this->Template->process('dummy', $data, true);
			}
			else
			{
				$data[ 'plugin' ][ 'output' ] = $output;

				$this->Template->process('plugins/run', $data, true);
			}
		}
		else
		{
			if ( !User::hasPerm(PLUGIN_PERMKEY . '/run', true) )
			{
				$this->Page->sendAccessError(trans('Ihnen fehlen die Rechte für diese Seite!'));
			}

			if ( !in_array($method, get_class_methods($pluginInstance)) )
			{
				Error::raise(sprintf(trans('Plugin `%s` has no method `%s`.'), get_class($pluginInstance), $method));
			}

			$pluginInstance->$method();

			$this->Page->error(404, sprintf(trans('Plugin `%s` has no output! ......'), $plug[ 'name' ]));
		}
	}

}
