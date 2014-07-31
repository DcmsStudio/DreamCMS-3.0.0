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
 * @package      Dashboard
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Switchfirewall.php
 */
class Dashboard_Action_Switchfirewall extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}


		$path = LIBRARY_PATH . 'config.inc.php';
		if ( !is_writable($path) )
		{
			Error::raise(trans('The configuration file is not writable - FIREWALL mode cannot be change.'));
		}

		demoadm();

		$contents = file_get_contents($path);
		if ( defined('USE_FIREWALL') && USE_FIREWALL )
		{
			$contents = preg_replace('#define\s*\(\s*\'USE_FIREWALL\'\s*,\s*true\s*\);#s', 'define(\'USE_FIREWALL\', false);', $contents);
			$message  = trans('Firewall wurde deaktiviert!');
			Library::log('Has disabled the Firewall', 'warn');
		}
		elseif ( defined('USE_FIREWALL') && USE_FIREWALL !== true )
		{
			$contents = preg_replace('#define\s*\(\s*\'USE_FIREWALL\'\s*,\s*false\s*\);#s', 'define(\'USE_FIREWALL\', true);', $contents);
			$message  = trans('Firewall wurde aktiviert!');
			Library::log('Has enabled the Firewall');
		}
		else
		{
			$contents = preg_replace('/\?>$/', "\n// Use the Firewall\n" . 'define(\'USE_FIREWALL\', true); ' . "\n\n" . '?>', $contents);
		}

		file_put_contents($path, $contents);
		echo Library::json(array (
		                         'success'  => true,
		                         'msg'      => $message,
		                         'firewall' => (USE_FIREWALL ? false : true)
		                   ));
		exit();
	}

}
