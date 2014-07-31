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
 * @file         BsdCommon.php
 */
abstract class Sys_Model_BsdCommon
{

	/**
	 * content of the syslog
	 *
	 * @var array
	 */
	private $_dmesg = array ();

	/**
	 * regexp1 for cpu information out of the syslog
	 *
	 * @var string
	 */
	private $_CPURegExp1 = "";

	/**
	 * regexp2 for cpu information out of the syslog
	 *
	 * @var string
	 */
	private $_CPURegExp2 = "";

	/**
	 * regexp1 for scsi information out of the syslog
	 *
	 * @var string
	 */
	private $_SCSIRegExp1 = "";

	/**
	 * regexp2 for scsi information out of the syslog
	 *
	 * @var string
	 */
	private $_SCSIRegExp2 = "";

	/**
	 * regexp1 for pci information out of the syslog
	 *
	 * @var string
	 */
	private $_PCIRegExp1 = "";

	/**
	 * regexp1 for pci information out of the syslog
	 *
	 * @var string
	 */
	private $_PCIRegExp2 = "";

	/**
	 * @var
	 */
	protected $sys;

	/**
	 *
	 */
	public function __construct ()
	{

	}

	/**
	 * @param Sys_Helper_System $sys
	 */
	public function setSys ( Sys_Helper_System $sys )
	{

		$this->sys = $sys;
	}

	/**
	 * setter for cpuregexp1
	 *
	 * @param string $value value to set
	 *
	 * @return void
	 */
	protected function setCPURegExp1 ( $value )
	{

		$this->_CPURegExp1 = $value;
	}

	/**
	 * setter for cpuregexp2
	 *
	 * @param string $value value to set
	 *
	 * @return void
	 */
	protected function setCPURegExp2 ( $value )
	{

		$this->_CPURegExp2 = $value;
	}

	/**
	 * setter for scsiregexp1
	 *
	 * @param string $value value to set
	 *
	 * @return void
	 */
	protected function setSCSIRegExp1 ( $value )
	{

		$this->_SCSIRegExp1 = $value;
	}

	/**
	 * setter for scsiregexp2
	 *
	 * @param string $value value to set
	 *
	 * @return void
	 */
	protected function setSCSIRegExp2 ( $value )
	{

		$this->_SCSIRegExp2 = $value;
	}

	/**
	 * setter for pciregexp1
	 *
	 * @param string $value value to set
	 *
	 * @return void
	 */
	protected function setPCIRegExp1 ( $value )
	{

		$this->_PCIRegExp1 = $value;
	}

	/**
	 * setter for pciregexp2
	 *
	 * @param string $value value to set
	 *
	 * @return void
	 */
	protected function setPCIRegExp2 ( $value )
	{

		$this->_PCIRegExp2 = $value;
	}

	/**
	 * read /var/run/dmesg.boot, but only if we haven't already
	 *
	 * @return array
	 */
	protected function readdmesg ()
	{

		if ( count($this->_dmesg) === 0 )
		{
			if ( PHP_OS != "Darwin" )
			{
				if ( Sys_Helper_System::rfts('/var/run/dmesg.boot', $buf) )
				{
					$parts        = preg_split("/rebooting|Uptime/", $buf, -1, PREG_SPLIT_NO_EMPTY);
					$this->_dmesg = preg_split("/\n/", $parts[ count($parts) - 1 ], -1, PREG_SPLIT_NO_EMPTY);
				}
			}
		}

		return $this->_dmesg;
	}

	/**
	 * get a value from sysctl command
	 *
	 * @param string $key key for the value to get
	 *
	 * @param bool   $debug
	 * @return string
	 */
	protected function grabkey ( $key, $debug = false )
	{

		$buf = "";
		if ( Sys_Helper_System::executeProgram('sysctl', "-n $key", $buf, $debug) )
		{
			return $buf;
		}
		else
		{
			return '';
		}
	}

	/**
	 * Virtual Host Name
	 *
	 * @param bool $debug
	 * @return void
	 */
	protected function hostname ( $debug = false )
	{

		if ( PSI_USE_VHOST === true )
		{
			$this->sys->setHostname(getenv('SERVER_NAME'));
		}
		else
		{
			if ( Sys_Helper_System::executeProgram('hostname', '', $buf, $debug) )
			{
				$this->sys->setHostname($buf);
			}
		}
	}

	/**
	 * IP of the Canonical Host Name
	 *
	 * @return void
	 */
	protected function ip ()
	{

		if ( PSI_USE_VHOST === true )
		{
			$this->sys->setIp(gethostbyname($this->hostname()));
		}
		else
		{
			if ( !($result = getenv('SERVER_ADDR')) )
			{
				$this->sys->setIp(gethostbyname($this->hostname()));
			}
			else
			{
				$this->sys->setIp($result);
			}
		}
	}

	/**
	 * Kernel Version
	 *
	 * @return void
	 */
	protected function kernel ()
	{

		$s = $this->grabkey('kern.version');
		$a = preg_split('/:/', $s);
		$this->sys->setKernel($a[ 0 ] . $a[ 1 ] . ':' . $a[ 2 ]);
	}

	/**
	 * Number of Users
	 *
	 * @param bool $debug
	 * @return void
	 */
	protected function users ( $debug = false )
	{

		if ( Sys_Helper_System::executeProgram('who', '| wc -l', $buf, $debug) )
		{
			$this->sys->setUsers($buf);
		}
	}

	/**
	 * Processor Load
	 * optionally create a loadbar
	 *
	 * @return void
	 */
	protected function loadavg ()
	{

		$s = $this->grabkey('vm.loadavg');
		$s = preg_replace('/{ /', '', $s);
		$s = preg_replace('/ }/', '', $s);
		$this->sys->setLoad($s);


		if ( PSI_LOAD_BAR )
		{
			if ( $fd = $this->grabkey('kern.cp_time') )
			{
				// Find out the CPU load
				// user + sys = load
				// total = total
				preg_match($this->_CPURegExp2, $fd, $res);
				$load  = $res[ 2 ] + $res[ 3 ] + $res[ 4 ]; // cpu.user + cpu.sys
				$total = $res[ 2 ] + $res[ 3 ] + $res[ 4 ] + $res[ 5 ]; // cpu.total
				// we need a second value, wait 1 second befor getting (< 1 second no good value will occour)
				sleep(1);
				$fd = $this->grabkey('kern.cp_time');
				preg_match($this->_CPURegExp2, $fd, $res);
				$load2  = $res[ 2 ] + $res[ 3 ] + $res[ 4 ];
				$total2 = $res[ 2 ] + $res[ 3 ] + $res[ 4 ] + $res[ 5 ];
				$this->sys->setLoadPercent((100 * ($load2 - $load)) / ($total2 - $total));
			}
		}
	}

	/**
	 * CPU information
	 *
	 * @return void
	 */
	protected function cpuinfo ()
	{

		$dev = new Sys_Helper_Cpu();
		$dev->setModel($this->grabkey('hw.model'));
		foreach ( $this->readdmesg() as $line )
		{
			if ( preg_match("/" . $this->_CPURegExp1 . "/", $line, $ar_buf) )
			{
				$dev->setCpuSpeed(round($ar_buf[ 2 ]));
				break;
			}
		}
		$this->sys->setCpus($dev);
	}

	/**
	 * Physical memory information and Swap Space information
	 *
	 * @param bool $debug
	 * @return void
	 */
	protected function memory ( $debug = false )
	{

		if ( PHP_OS == 'FreeBSD' || PHP_OS == 'OpenBSD' )
		{
			// vmstat on fbsd 4.4 or greater outputs kbytes not hw.pagesize
			// I should probably add some version checking here, but for now
			// we only support fbsd 4.4
			$pagesize = 1024;
		}
		else
		{
			$pagesize = $this->grabkey('hw.pagesize');
		}

		if ( Sys_Helper_System::executeProgram('vmstat', '', $vmstat, $debug) )
		{
			$lines  = preg_split("/\n/", $vmstat, -1, PREG_SPLIT_NO_EMPTY);
			$ar_buf = preg_split("/\s+/", trim($lines[ 2 ]), 19);
			if ( PHP_OS == 'NetBSD' || PHP_OS == 'DragonFly' )
			{
				$this->sys->setMemFree($ar_buf[ 4 ] * 1024);
			}
			else
			{
				$this->sys->setMemFree($ar_buf[ 4 ] * $pagesize);
			}
			$this->sys->setMemTotal($this->grabkey('hw.physmem'));
			$this->sys->setMemUsed($this->sys->getMemTotal() - $this->sys->getMemFree());


			if ( ((PHP_OS == 'OpenBSD' || PHP_OS == 'NetBSD') && Sys_Helper_System::executeProgram('swapctl', '-l -k', $swapstat, PSI_DEBUG)) || Sys_Helper_System::executeProgram('swapinfo', '-k', $swapstat, PSI_DEBUG) )
			{
				$lines = preg_split("/\n/", $swapstat, -1, PREG_SPLIT_NO_EMPTY);
				foreach ( $lines as $line )
				{
					$ar_buf = preg_split("/\s+/", $line, 6);
					if ( ($ar_buf[ 0 ] != 'Total') && ($ar_buf[ 0 ] != 'Device') )
					{
						$dev = new Sys_Helper_Disk();
						$dev->setMountPoint($ar_buf[ 0 ]);
						$dev->setName("SWAP");
						$dev->setFsType('swap');
						$dev->setTotal($ar_buf[ 1 ] * 1024);
						$dev->setUsed($ar_buf[ 2 ] * 1024);
						$dev->setFree($dev->getTotal() - $dev->getUsed());
						$this->sys->setSwapDevices($dev);
					}
				}
			}
		}
	}

	/**
	 * Distribution
	 *
	 * @param bool $debug
	 * @return void
	 */
	protected function distro ( $debug = false )
	{

		if ( Sys_Helper_System::executeProgram('uname', '-s', $result, $debug) )
		{
			$this->sys->setDistribution($result);
		}
	}

}
