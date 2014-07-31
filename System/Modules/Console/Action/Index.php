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
 * @package      Console
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Console_Action_Index extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		if ( php_sapi_name() == 'cli' )
		{
			define('IS_CLI', true);
		}
		else
		{
			define('IS_CLI', false);
		}

		ob_start();

		Library::addNavi(trans('Konsole'));

		error_reporting(0);

		$_date = Locales::formatFullDate(time());
		$_time = Locales::formatTime(time());


		/**
		 *
		 */
		define('BACKEND_CONSOLE', true);

		$cmd  = $this->_post('command');
		$argv = $this->_post('argv');

		$args = is_array($argv) ? CommandLine::parseArgs($argv) : array ();


		if ( isset($args[ 'v' ]) )
		{
			Cli::execSystem($cmd, $args);
			exit;
		}

		$cli = new Cli($args, $cmd);
		if ( isset($args[ 'h' ]) || isset($args[ 'help' ]) || $cmd == 'help' )
		{
			$cli->printHelp($cmd);
			exit;
		}

		if ( $cmd == 'ls' )
		{
			unset($args[ 'ls' ]);

			$pattern = array_shift($args);
			$folder  = array_shift($args);


			if ( empty($folder) )
			{
				$folder = Library::formatPath(ROOT_PATH . Session::get('consolePath'));
			}
			else
			{
				$folder = Library::formatPath(ROOT_PATH . $folder);
			}

			if ( empty($pattern) )
			{
				$pattern = '*';
			}

			$arr = CommandLine::ls($pattern, $folder);

			echo "..\n";
			// echo str_replace(ROOT_PATH, '', $folder) . "\n";
			echo str_replace('\\', '/', implode("\n", $arr));

			ob_flush();

			$this->send();
		}
		else if ( $cmd == 'cd' )
		{
			unset($args[ 'cd' ]);
			CommandLine::cd(implode('', $args));
		}
		else if ( $cmd == 'dirname' )
		{
			$_path = Session::get('consolePath') ? Session::get('consolePath') : '/';
			echo "\n" . $_path . "\n";
			$this->send();
		}
		else if ( $cmd == 'date' )
		{

			echo "\n" . $_date . "\n";
			$this->send();
		}
		else if ( $cmd == 'time' )
		{
			echo "\n" . $_time . "\n";
			$this->send();
		}
		else
		{

			array_shift($args); // remove cmd
			Cli::execSystem($cmd, $args);

			$this->send();
		}


		echo Library::json(array (
		                         'success' => false,
		                         'output'  => 'Error ...' . print_r($args, true)
		                   ));

		exit;
	}

	protected function send ()
	{

		$_path = Session::get('consolePath') ? Session::get('consolePath') : '/';
		ob_flush();

		ob_end_clean();
		$output = ob_get_contents();
		ob_get_clean();
		echo Library::json(array (
		                         'success' => true,
		                         'output'  => $output,
		                         'cwd'     => (string)$_path
		                   ));
		exit;
	}

	/**
	 * @param $program
	 * @return int|string
	 */
	protected function GetProgCpuUsage ( $program )
	{

		if ( !$program )
		{
			return -1;
		}

		$c_pid = exec("ps aux | grep " . $program . " | grep -v grep | grep -v su | awk {'print $3'}");

		return $c_pid;
	}

	/**
	 * @param $program
	 * @return int|string
	 */
	protected function GetProgMemUsage ( $program )
	{

		if ( !$program )
		{
			return -1;
		}

		$c_pid = exec("ps aux | grep " . $program . " | grep -v grep | grep -v su | awk {'print $4'}");

		return $c_pid;
	}

}

?>