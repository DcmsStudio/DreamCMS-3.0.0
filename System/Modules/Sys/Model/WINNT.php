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
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         WINNT.php
 */
class Sys_Model_WINNT extends Sys_Helper_System
{

	/**
	 * holds the COM object that we pull all the WMI data from
	 *
	 * @var Object
	 */
	private $_wmi;

	/**
	 * holds all devices, which are in the system
	 *
	 * @var array
	 */
	private $_wmidevices;

	/**
	 * store language encoding of the system to convert some output to utf-8
	 *
	 * @var string
	 */
	private $_charset = "";

	/**
	 * build the global Error object and create the WMI connection
	 */
	public function __construct ()
	{

		parent::__construct();
		// don't set this params for local connection, it will not work
		$strHostname = '';
		$strUser     = '';
		$strPassword = '';

		// initialize the wmi object
		$objLocator = new COM('WbemScripting.SWbemLocator');
		if ( $strHostname == "" )
		{
			$this->_wmi = $objLocator->ConnectServer();
		}
		else
		{
			$this->_wmi = $objLocator->ConnectServer($strHostname, 'rootcimv2', $strHostname . '\\' . $strUser, $strPassword);
		}
		$this->_getCodeSet();
	}

	/**
	 * store the codepage of the os for converting some strings to utf-8
	 *
	 * @return void
	 */
	private function _getCodeSet ()
	{

		$buffer         = $this->_getWMI('Win32_OperatingSystem', array (
		                                                                'CodeSet'
		                                                          ));
		$this->_charset = 'windows-' . $buffer[ 0 ][ 'CodeSet' ];
	}

	/**
	 * function for getting a list of values in the specified context
	 * optionally filter this list, based on the list from second parameter
	 *
	 * @param string $strClass name of the class where the values are stored
	 * @param array  $strValue filter out only needed values, if not set all values of the class are returned
	 *
	 * @return array content of the class stored in an array
	 */
	private function _getWMI ( $strClass, $strValue = array () )
	{

		$arrData = array ();
		$value   = "";
		try
		{
			$objWEBM    = $this->_wmi->Get($strClass);
			$arrProp    = $objWEBM->Properties_;
			$arrWEBMCol = $objWEBM->Instances_();
			foreach ( $arrWEBMCol as $objItem )
			{
				if ( is_array($arrProp) )
				{
					reset($arrProp);
				}
				$arrInstance = array ();
				foreach ( $arrProp as $propItem )
				{
					eval("\$value = \$objItem->" . $propItem->Name . ";");
					if ( empty($strValue) )
					{
						if ( is_string($value) )
						{
							$arrInstance[ $propItem->Name ] = trim($value);
						}
						else
						{
							$arrInstance[ $propItem->Name ] = $value;
						}
					}
					else
					{
						if ( in_array($propItem->Name, $strValue) )
						{
							if ( is_string($value) )
							{
								$arrInstance[ $propItem->Name ] = trim($value);
							}
							else
							{
								$arrInstance[ $propItem->Name ] = $value;
							}
						}
					}
				}
				$arrData[ ] = $arrInstance;
			}
		}
		catch ( Exception $e )
		{
			if ( PSI_DEBUG )
			{
				$this->error->addError($e->getCode(), $e->getMessage());
			}
		}

		return $arrData;
	}

	/**
	 * retrieve different device types from the system based on selector
	 *
	 * @param string $strType type of the devices that should be returned
	 *
	 * @return array list of devices of the specified type
	 */
	private function _devicelist ( $strType )
	{

		if ( empty($this->_wmidevices) )
		{
			$this->_wmidevices = $this->_getWMI('Win32_PnPEntity', array (
			                                                             'Name',
			                                                             'PNPDeviceID'
			                                                       ));
		}
		$list = array ();
		foreach ( $this->_wmidevices as $device )
		{
			if ( substr($device[ 'PNPDeviceID' ], 0, strpos($device[ 'PNPDeviceID' ], "\\") + 1) == ($strType . "\\") )
			{
				$list[ ] = $device[ 'Name' ];
			}
		}

		return $list;
	}

	/**
	 * Host Name
	 *
	 * @return void
	 */
	private function _hostname ()
	{

		if ( PSI_USE_VHOST === true )
		{
			$this->sys->setHostname(getenv('SERVER_NAME'));
		}
		else
		{
			$buffer = $this->_getWMI('Win32_ComputerSystem', array (
			                                                       'Name'
			                                                 ));
			$result = $buffer[ 0 ][ 'Name' ];
			$ip     = gethostbyname($result);
			if ( $ip != $result )
			{
				$this->sys->setHostname(gethostbyaddr($ip));
			}
		}
	}

	/**
	 * IP of the Canonical Host Name
	 *
	 * @return void
	 */
	private function _ip ()
	{

		if ( PSI_USE_VHOST === true )
		{
			$this->sys->setIp(gethostbyname($this->_hostname()));
		}
		else
		{
			$buffer = $this->_getWMI('Win32_ComputerSystem', array (
			                                                       'Name'
			                                                 ));
			$result = $buffer[ 0 ][ 'Name' ];
			$this->sys->setIp(gethostbyname($result));
		}
	}

	/**
	 * UpTime
	 * time the system is running
	 *
	 * @return void
	 */
	private function _uptime ()
	{

		$result = 0;
		date_default_timezone_set('UTC');
		$buffer    = $this->_getWMI('Win32_OperatingSystem', array (
		                                                           'LastBootUpTime',
		                                                           'LocalDateTime'
		                                                     ));
		$byear     = (int)substr($buffer[ 0 ][ 'LastBootUpTime' ], 0, 4);
		$bmonth    = int(int)substr($buffer[ 0 ][ 'LastBootUpTime' ], 6, 2		$bday      = intval(int)substr($buffer[ 0 ][ 'LastBootUpTime' ], 8, 2bhour     = intval(su(int)substr($buffer[ 0 ][ 'LastBootUpTime' ], 10, 2nute   = intval(subs(int)substr($buffer[ 0 ][ 'LastBootUpTime' ], 12, 2nds  = intval(substr((int)substr($buffer[ 0 ][ 'LocalDateTime' ], 0, 4  = intval(substr($buff(int)substr($buffer[ 0 ][ 'LocalDateTime' ], 4, 2ntval(substr($buffer[(int)substr($buffer[ 0 ][ 'LocalDateTime' ], 6, 2al(substr($buffer[ 0 (int)substr($buffer[ 0 ][ 'LocalDateTime' ], 8, 2substr($buffer[ 0 ][ (int)substr($buffer[ 0 ][ 'LocalDateTime' ], 10, 2str($buffer[ 0 ][ 'L(int)substr($buffer[ 0 ][ 'LocalDateTime' ], 12, 2r($buffer[ 0 ][ 'LocalDateTime' ], 12, 2));
		$boottime  = mktime($bhour, $bminute, $bseconds, $bmonth, $bday, $byear);
		$localtime = mktime($lhour, $lminute, $lseconds, $lmonth, $lday, $lyear);
		$result    = $localtime - $boottime;
		$this->sys->setUptime($result);
	}

	/**
	 * Number of Users
	 *
	 * @return void
	 */
	private function _users ()
	{

		$users  = 0;
		$buffer = $this->_getWMI('Win32_Process', array (
		                                                'Caption'
		                                          ));
		foreach ( $buffer as $process )
		{
			if ( strtoupper($process[ 'Caption' ]) == strtoupper('explorer.exe') )
			{
				$users++;
			}
		}
		$this->sys->setUsers($users);
	}

	/**
	 * Distribution
	 *
	 * @return void
	 */
	private function _distro ()
	{

		$buffer = $this->_getWMI('Win32_OperatingSystem', array (
		                                                        'Version',
		                                                        'ServicePackMajorVersion'
		                                                  ));
		$kernel = $buffer[ 0 ][ 'Version' ];
		if ( $buffer[ 0 ][ 'ServicePackMajorVersion' ] > 0 )
		{
			$kernel .= ' SP' . $buffer[ 0 ][ 'ServicePackMajorVersion' ];
		}
		$this->sys->setKernel($kernel);

		$buffer = $this->_getWMI('Win32_OperatingSystem', array (
		                                                        'Caption'
		                                                  ));
		$this->sys->setDistribution($buffer[ 0 ][ 'Caption' ]);

		if ( $kernel[ 0 ] == 6 )
		{
			$icon = 'vista.png';
		}
		else
		{
			$icon = 'xp.png';
		}
		$this->sys->setDistributionIcon($icon);
	}

	/**
	 * Processor Load
	 * optionally create a loadbar
	 *
	 * @return void
	 */
	private function _loadavg ()
	{

		$loadavg = "";
		$sum     = 0;
		$buffer  = $this->_getWMI('Win32_Processor', array (
		                                                   'LoadPercentage'
		                                             ));
		foreach ( $buffer as $load )
		{
			$value = $load[ 'LoadPercentage' ];
			$loadavg .= $value . ' ';
			$sum += $value;
		}
		$this->sys->setLoad(trim($loadavg));
		if ( PSI_LOAD_BAR )
		{
			$this->sys->setLoadPercent($sum / count($buffer));
		}
	}

	/**
	 * CPU information
	 *
	 * @return void
	 */
	private function _cpuinfo ()
	{

		$allCpus = $this->_getWMI('Win32_Processor', array (
		                                                   'Name',
		                                                   'L2CacheSize',
		                                                   'CurrentClockSpeed',
		                                                   'ExtClock',
		                                                   'NumberOfCores'
		                                             ));
		foreach ( $allCpus as $oneCpu )
		{
			$coreCount = 1;
			if ( isset($oneCpu[ 'NumberOfCores' ]) )
			{
				$coreCount = $oneCpu[ 'NumberOfCores' ];
			}
			for ( $i = 0; $i < $coreCount; $i++ )
			{
				$cpu = new Sys_Helper_Cpu();
				$cpu->setModel($oneCpu[ 'Name' ]);
				$cpu->setCache($oneCpu[ 'L2CacheSize' ] * 1024);
				$cpu->setCpuSpeed($oneCpu[ 'CurrentClockSpeed' ]);
				$cpu->setBusSpeed($oneCpu[ 'ExtClock' ]);
				$this->sys->setCpus($cpu);
			}
		}
	}

	/**
	 * Physical memory information and Swap Space information
	 *
	 * @link http://msdn2.microsoft.com/En-US/library/aa394239.aspx
	 * @link http://msdn2.microsoft.com/en-us/library/aa394246.aspx
	 * @return void
	 */
	private function _memory ()
	{

		$buffer = $this->_getWMI("Win32_OperatingSystem", array (
		                                                        'TotalVisibleMemorySize',
		                                                        'FreePhysicalMemory'
		                                                  ));
		$this->sys->setMemTotal($buffer[ 0 ][ 'TotalVisibleMemorySize' ] * 1024);
		$this->sys->setMemFree($buffer[ 0 ][ 'FreePhysicalMemory' ] * 1024);
		$this->sys->setMemUsed($this->sys->getMemTotal() - $this->sys->getMemFree());

		$buffer = $this->_getWMI('Win32_PageFileUsage');
		foreach ( $buffer as $swapdevice )
		{
			$dev = new Sys_Helper_Disk();
			$dev->setName("SWAP");
			$dev->setMountPoint($swapdevice[ 'Name' ]);
			$dev->setTotal($swapdevice[ 'AllocatedBaseSize' ] * 1024 * 1024);
			$dev->setUsed($swapdevice[ 'CurrentUsage' ] * 1024 * 1024);
			$dev->setFree($dev->getTotal() - $dev->getUsed());
			$dev->setFsType('swap');
			$this->sys->setSwapDevices($dev);
		}
	}

	/**
	 * get os specific encoding
	 *
	 * @see OS::getEncoding()
	 *
	 * @return string
	 */
	function getEncoding ()
	{

		return $this->_charset;
	}

	/**
	 * get the information
	 *
	 * @see PSI_Interface_OS::build()
	 *
	 * @return Void
	 */
	function build ()
	{

		$this->_distro();
		$this->_uptime();
		$this->_cpuinfo();
		$this->_memory();
		$this->_loadavg();
	}

}
