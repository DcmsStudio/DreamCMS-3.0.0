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
 * @package      Sys
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Sys_Action_Index extends Controller_Abstract
{

	/**
	 * @var null
	 */
	protected $_sysinfo = null;

	/**
	 * @var null
	 */
	protected $sys = null;

	public function execute ()
	{

		if ( $this->getApplication()->getMode() === Application::BACKEND_MODE )
		{
			$this->_getServerInfo();
		}
	}

	private function _getServerInfo ()
	{


		$os  = PHP_OS;
		$_os = (string)'Sys_Model_' . $os;


		if ( !class_exists($_os) )
		{
			throw new BaseException('The Os "' . $_os . '" is not supported!');
		}


		$sys = new Sys_Helper_System();


		$this->_sysinfo = new $_os;
		$this->_sysinfo->setSys($sys);
		$this->_sysinfo->build();


		$dat = array ();

		$totalCores   = 0;
		$cpuLoadTotal = 0;

		$cpuName = '';
		foreach ( $sys->getCpus() as $device )
		{
			$dat[ 'srv' ][ 'cpuname' ]  = $device->getModel();
			$dat[ 'srv' ][ 'cpuspeed' ] = $device->getCpuSpeed();
			$dat[ 'srv' ][ 'cpuload' ]  = $device->getLoad();

			if ( $device->getBusSpeed() !== null )
			{
				$dat[ 'srv' ][ 'BusSpeed' ] = $device->getBusSpeed();
			}
			if ( $device->getL2Cache() !== null )
			{
				$dat[ 'srv' ][ 'L2Cache' ] = Tools::formatSize($device->getL2Cache());
			}

			if ( $device->getL3Cache() !== null )
			{
				$dat[ 'srv' ][ 'L3Cache' ] = Tools::formatSize($device->getL3Cache());
			}

			if ( $device->getVirt() !== null )
			{
				$dat[ 'srv' ][ 'Virt' ] = $device->getVirt();
			}


			$dat[ 'cpus' ][ ] = array (
				'speed'      => $device->getCpuSpeed(),
				'model'      => $device->getModel(),
				'load'       => $device->getLoad(),
				'temperatur' => $device->getTemp()
			);

			$totalCores++;
			$cpuLoadTotal += $device->getLoad();
		}

		if ( $sys->getLoadPercent() !== null )
		{
			$dat[ 'srv' ][ 'cpu_load_total' ] = $sys->getLoadPercent();
		}

		$load = explode(' ', trim($sys->getLoad()));

		$dat[ 'srv' ][ 'LoadAvg' ][ '1min' ]  = $load[ 0 ];
		$dat[ 'srv' ][ 'LoadAvg' ][ '5min' ]  = $load[ 1 ];
		$dat[ 'srv' ][ 'LoadAvg' ][ '15min' ] = $load[ 2 ];


		$dat[ 'srv' ][ 'totalcores' ] = $totalCores;

		$uptime = $sys->getUptime();

		$dat[ 'srv' ][ 'os' ][ 'server_uptime' ]      = $this->formatUptime($uptime);
		$dat[ 'srv' ][ 'os' ][ 'server_uptime_date' ] = date('d.m.Y, H:i:s', time() - $uptime);


		$dat[ 'srv' ][ 'os' ][ 'kernel' ]       = $sys->getKernel();
		$dat[ 'srv' ][ 'os' ][ 'distribution' ] = $sys->getDistribution();


		$dat[ 'srv' ][ 'ram' ][ 'free' ]        = Tools::formatSize($sys->getMemFree());
		$dat[ 'srv' ][ 'ram' ][ 'used' ]        = Tools::formatSize($sys->getMemUsed());
		$dat[ 'srv' ][ 'ram' ][ 'total' ]       = Tools::formatSize($sys->getMemTotal());
		$dat[ 'srv' ][ 'ram' ][ 'usedpercent' ] = $sys->getMemPercentUsed();
		$dat[ 'srv' ][ 'ram' ][ 'freepercent' ] = round($sys->getMemFree() * 100 / $sys->getMemTotal(), 2);

		if ( $sys->getMemApplication() !== null )
		{
			$dat[ 'srv' ][ 'App' ]        = Tools::formatSize($sys->getMemApplication());
			$dat[ 'srv' ][ 'AppPercent' ] = $sys->getMemPercentApplication();
		}

		if ( $sys->getMemBuffer() !== null )
		{
			$dat[ 'srv' ][ 'Buffers' ]        = Tools::formatSize($sys->getMemBuffer());
			$dat[ 'srv' ][ 'BuffersPercent' ] = $sys->getMemPercentBuffer();
		}

		if ( $sys->getMemCache() !== null )
		{
			$dat[ 'srv' ][ 'Cached' ]        = Tools::formatSize($sys->getMemCache());
			$dat[ 'srv' ][ 'CachedPercent' ] = $sys->getMemPercentCache();
		}


		if ( count($sys->getSwapDevices()) > 0 )
		{
			$dat[ 'srv' ][ 'swap' ][ 'free' ]        = Tools::formatSize($sys->getSwapFree());
			$dat[ 'srv' ][ 'swap' ][ 'used' ]        = Tools::formatSize($sys->getSwapUsed());
			$dat[ 'srv' ][ 'swap' ][ 'total' ]       = Tools::formatSize($sys->getSwapTotal());
			$dat[ 'srv' ][ 'swap' ][ 'usedpercent' ] = $sys->getSwapPercentUsed();
			$dat[ 'srv' ][ 'swap' ][ 'freepercent' ] = round($sys->getSwapFree() * 100 / $sys->getSwapTotal(), 2);
		}


		/**
		 * read the installed jquery version
		 *
		 * $jquery = file_get_contents(HTML_PATH . 'js/jquery/jquery.js', false);
		 *
		 * $version = array();
		 * preg_match('~Library v(.*?)\\n~iU', $jquery, $version);
		 *
		 * if ( !empty($version[1]) )
		 * {
		 * $dat['srv']['jquery_version'] = $version[1];
		 * }


		 */
		$dat[ 'srv' ][ 'server_software' ] = $this->getServerSoftware();


		$constants           = get_defined_constants(true);
		$_dat[ 'constants' ] = $constants[ 'user' ];
		ksort($_dat[ 'constants' ]);
		unset($_dat[ 'constants' ][ 'VERSION' ]);
		unset($_dat[ 'constants' ][ 'INSTALL_VERSION' ]);
		unset($_dat[ 'constants' ][ 'DB_PASSWORD' ]);
		unset($_dat[ 'constants' ][ 'DB_USER' ]);


		$dat[ 'srv' ][ 'dreamcmsversion' ] = VERSION;

		foreach ( $_dat[ 'constants' ] as $key => $value )
		{
			$dat[ 'constants' ][ ] = array (
				'name'  => $key,
				'value' => $this->formatValue($value)
			);
		}


		$dbx = Database::getDatabaseInfo();

		$dat[ 'srv' ][ 'sql_type' ]    = $this->db->getAdapter();
		$dat[ 'srv' ][ 'sql_version' ] = $dbx[ 'version' ];

		$dat[ 'srv' ][ 'php_version' ]   = phpversion();
		$dat[ 'srv' ][ 'php_sapi_name' ] = php_sapi_name();
		$dat[ 'srv' ][ 'php_uname' ]     = php_uname();


		$dat[ 'srv' ][ 'user_agent' ] = phpversion() <= "4.2.1" ? getenv("HTTP_USER_AGENT") :
			$_SERVER[ 'HTTP_USER_AGENT' ];
		$dat[ 'srv' ][ 'basedir' ]    = (($ob = ini_get('open_basedir')) ? $ob : 'none');


		$frei           = disk_free_space(ROOT_PATH);
		$insgesamt      = disk_total_space(ROOT_PATH);
		$belegt         = $insgesamt - $frei;
		$prozent_belegt = (100 * $belegt) / $insgesamt;
		$prozent_free   = (100 * $frei) / $insgesamt;

		$dat[ 'srv' ][ 'disk_space' ]         = Tools::formatSize($insgesamt);
		$dat[ 'srv' ][ 'disk_space_free' ]    = Tools::formatSize($frei);
		$dat[ 'srv' ][ 'free_space_percent' ] = round($prozent_free, "2") . '%';

		$dat[ 'srv' ][ 'inuse_space' ]         = Tools::formatSize($belegt);
		$dat[ 'srv' ][ 'inuse_space_percent' ] = round($prozent_belegt, "2") . '%';

		ob_start();
		phpinfo(INFO_CONFIGURATION | INFO_MODULES);
		$phpinfoStr = ob_get_contents();
		ob_end_clean();

		$phpinfo = array (
			'phpinfo' => array ()
		);
		if ( preg_match_all('#(?:<h2>(?:<a name="[^>]*">)?(.*?)(?:</a>)?</h2>)|(?:<tr(?: class="[^>]*")?><t[hd](?: class="[^>]*")?>(.*?)</t[hd]>(?:<t[hd](?: class="[^>]*")?>(.*?)</t[hd]>(?:<t[hd](?: class="[^>]*")?>(.*?)\s*</t[hd]>)?)?</tr>)#is', $phpinfoStr, $matches, PREG_SET_ORDER) )
		{
			foreach ( $matches as $match )
			{
				if ( strlen($match[ 1 ]) )
				{
					$phpinfo[ $match[ 1 ] ] = array ();
				}
				elseif ( isset($match[ 3 ]) )
				{
					$keys                                 = array_keys($phpinfo);
					$phpinfo[ end($keys) ][ $match[ 2 ] ] = isset($match[ 4 ]) ? array (
						$match[ 3 ],
						$match[ 4 ]
					) : $match[ 3 ];
				}
				else
				{
					$keys                     = array_keys($phpinfo);
					$phpinfo[ end($keys) ][ ] = $match[ 2 ];
				}
			}
		}


		$_info = array ();

		foreach ( $phpinfo as $name => $section )
		{
			$tmp = array ();
			foreach ( $section as $key => $val )
			{
				if ( is_array($val) )
				{
					$tmp[ ] = array (
						'key'   => $key,
						'value' => $val[ 0 ]
					);
				}
				elseif ( is_string($key) )
				{
					$tmp[ ] = array (
						'key'   => $key,
						'value' => $val
					);
				}
				else
				{
					$tmp[ ] = array (
						'value' => $val
					);
				}
			}
			$_info[ ] = array (
				'header' => $name,
				'data'   => $tmp
			);
		}
		$dat[ 'phpinfo' ] = $_info;


		uksort($dat[ 'srv' ], "strnatcasecmp");
		foreach ( $dat[ 'srv' ] as $key => $value )
		{
			$dat[ 'srv' ][ $key ] = $this->formatValue($value);
		}

		if( function_exists('shell_exec') )
		{
			if( strpos( strtolower( PHP_OS ), 'win' ) === 0 )
			{
				$tasks = @shell_exec( "tasklist" );
				#$tasks = str_replace( " ", "&nbsp;", $tasks );
			}
			else if( strtolower( PHP_OS ) == 'darwin' )
			{
				$tasks = @shell_exec( "top -l 1" );
				#$tasks = str_replace( " ", "&nbsp;", $tasks );
			}
			else
			{
				$tasks = @shell_exec( "top -b -n 1" );
				#$tasks = str_replace( " ", "&nbsp;", $tasks );
			}

			$dat[ 'tasks' ] = $tasks;
		}


		$this->Template->process('generic/system_infos', $dat, true);
	}

	/**
	 * @param $val
	 * @return string
	 */
	private function getPHPSetting ( $val )
	{

		$r = (ini_get($val) == '1' ? 1 : 0);

		return $r ? 'ON' : 'OFF';
	}

	/**
	 *
	 * @return string
	 */
	private function getServerSoftware ()
	{

		if ( isset($_SERVER[ 'SERVER_SOFTWARE' ]) )
		{
			return $_SERVER[ 'SERVER_SOFTWARE' ];
		}
		else if ( ($sf = getenv('SERVER_SOFTWARE')) )
		{
			return $sf;
		}
		else
		{
			return 'n/a';
		}
	}

	/**
	 * @param $seconds
	 * @return string
	 */
	private function formatUptime ( $seconds )
	{

		$secs  = (int)$seconds % 60;
		$mins  = (int)$seconds / 60 % 60;
		$hours = (int)$seconds / 3600 % 24;
		$days  = (int)$seconds / 86400;

		if ( $days > 0 )
		{
			$uptimeString .= $days;
			$uptimeString .= (($days == 1) ? " Day" : " Days");
		}
		if ( $hours > 0 )
		{
			$uptimeString .= (($days > 0) ? " " : "") . $hours;
			$uptimeString .= (($hours == 1) ? " Hour" : " Hours");
		}
		if ( $mins > 0 )
		{
			$uptimeString .= (($days > 0 || $hours > 0) ? " " : "") . $mins;
			$uptimeString .= (($mins == 1) ? " Minute" : " Minutes");
		}
		if ( $secs > 0 )
		{
			$uptimeString .= (($days > 0 || $hours > 0 || $mins > 0) ? " " : "") . $secs;
			$uptimeString .= (($secs == 1) ? " Second" : " Seconds");
		}

		return $uptimeString;
	}

	/**
	 *
	 * @param mixed $value
	 * @return string
	 */
	private function formatValue ( $value )
	{

		if ( is_bool($value) )
		{
			return ($value ? 'true' : 'false');
		}
		if ( $value === '' )
		{
			return trans('empty string');
		}
		if ( is_string($value) )
		{
			return htmlentities($value);
		}
		if ( is_int($value) )
		{
			return '' . $value;
		}
		if ( is_float($value) )
		{
			return '' . $value;
		}
		if ( is_null($value) )
		{
			return 'NULL';
		}

		return $value;
	}

}

?>