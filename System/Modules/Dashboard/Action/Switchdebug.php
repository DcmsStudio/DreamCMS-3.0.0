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
 * @file         Switchdebug.php
 */
class Dashboard_Action_Switchdebug extends Controller_Abstract
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
			Library::sendJson(false, trans('The configuration file is not writable - DEBUG mode cannot be change.'));
		}

		demoadm();

		$contents = file_get_contents($path);
		if ( defined('DEBUGGING') && DEBUGGING )
		{
			$contents = preg_replace('#define\(\s*\'DEBUGGING\'\s*,\s*true\s*\);#', 'define(\'DEBUGGING\', false);', $contents);
			$message  = trans('Debug Mode wurde deaktiviert!');
			Library::log('Has disabled the Debugger');
		}
		elseif ( defined('DEBUGGING') && DEBUGGING !== true )
		{
			$contents = preg_replace('#define\(\s*\'DEBUGGING\'\s*,\s*false\s*\);#', 'define(\'DEBUGGING\', true);', $contents);
			$message  = trans('Debug Mode wurde Aktiviert!');
			Library::log('Has enabled the Debugger', 'warn');
		}
		else
		{
			$contents = preg_replace('/\?>$/', "\n// Use the Debugger\n" . 'define(\'DEBUGGING\', true); ' . "\n\n" . '?>', $contents);
		}


		file_put_contents($path, $contents);
		echo Library::json(array (
		                         'success' => true,
		                         'msg'     => $message,
		                         'debug'   => (DEBUG ? false : true)
		                   ));
		exit();
	}

}
