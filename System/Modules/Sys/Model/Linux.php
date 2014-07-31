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
 * @file         Linux.php
 */
class Sys_Model_Linux extends Sys_Model_BsdCommon
{

	/**
	 * Kernel Version
	 *
	 * @return void
	 */
	private function _kernel ()
	{

		if ( Sys_Helper_System::executeProgram('uname', '-r', $strBuf, PSI_DEBUG) )
		{
			$result = trim($strBuf);
			if ( Sys_Helper_System::executeProgram('uname', '-v', $strBuf, PSI_DEBUG) )
			{
				if ( preg_match('/SMP/', $strBuf) )
				{
					$result .= ' (SMP)';
				}
			}
			if ( Sys_Helper_System::executeProgram('uname', '-m', $strBuf, PSI_DEBUG) )
			{
				$result .= ' ' . trim($strBuf);
			}
			$this->sys->setKernel($result);
		}
		else
		{
			if ( Sys_Helper_System::rfts('/proc/version', $strBuf, 1) )
			{
				if ( preg_match('/version (.*?) /s', $strBuf, $ar_buf) )
				{
					$result = $ar_buf[ 1 ];
					if ( preg_match('/SMP/', $strBuf) )
					{
						$result .= ' (SMP)';
					}
					$this->sys->setKernel($result);
				}
			}
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

		Sys_Helper_System::rfts('/proc/uptime', $buf, 1);
		$ar_buf = preg_split('/ /', $buf);
		$this->sys->setUptime(trim($ar_buf[ 0 ]));
	}

	/**
	 * Processor Load
	 * optionally create a loadbar
	 *
	 * @return void
	 */
	private function _loadavg ()
	{

		if ( Sys_Helper_System::rfts('/proc/loadavg', $buf) )
		{
			$result = preg_split("/\s/", $buf, 4);
			// don't need the extra values, only first three
			unset($result[ 3 ]);
			$this->sys->setLoad(implode(' ', $result));
		}
		if ( PSI_LOAD_BAR )
		{
			$this->sys->setLoadPercent($this->_parseProcStat('cpu'));
		}
	}

	/**
	 * fill the load for a individual cpu, through parsing /proc/stat for the specified cpu
	 *
	 * @param String $cpuline cpu for which load should be meassured
	 *
	 * @return Integer
	 */
	private function _parseProcStat ( $cpuline )
	{

		$load   = 0;
		$load2  = 0;
		$total  = 0;
		$total2 = 0;
		if ( Sys_Helper_System::rfts('/proc/stat', $buf) )
		{
			$lines = preg_split("/\n/", $buf, -1, PREG_SPLIT_NO_EMPTY);
			foreach ( $lines as $line )
			{
				if ( preg_match('/^' . $cpuline . ' (.*)/', $line, $matches) )
				{
					$ab = 0;
					$ac = 0;
					$ad = 0;
					$ae = 0;
					sscanf($buf, "%*s %Ld %Ld %Ld %Ld", $ab, $ac, $ad, $ae);
					$load  = $ab + $ac + $ad; // cpu.user + cpu.sys
					$total = $ab + $ac + $ad + $ae; // cpu.total
					break;
				}
			}
		}

		// we need a second value, wait 1 second befor getting (< 1 second no good value will occour)
		if ( PSI_LOAD_BAR )
		{
			sleep(1);
		}


		if ( Sys_Helper_System::rfts('/proc/stat', $buf) )
		{
			$lines = preg_split("/\n/", $buf, -1, PREG_SPLIT_NO_EMPTY);
			foreach ( $lines as $line )
			{
				if ( preg_match('/^' . $cpuline . ' (.*)/', $line, $matches) )
				{
					$ab = 0;
					$ac = 0;
					$ad = 0;
					$ae = 0;
					sscanf($buf, "%*s %Ld %Ld %Ld %Ld", $ab, $ac, $ad, $ae);
					$load2  = $ab + $ac + $ad;
					$total2 = $ab + $ac + $ad + $ae;
					break;
				}
			}
		}
		if ( $total > 0 && $total2 > 0 && $load > 0 && $load2 > 0 && $total2 != $total && $load2 != $load )
		{
			return (100 * ($load2 - $load)) / ($total2 - $total);
		}

		return 0;
	}

	/**
	 * CPU information
	 * All of the tags here are highly architecture dependant.
	 *
	 * @return void
	 */
	private function _cpuinfo ()
	{

		if ( Sys_Helper_System::rfts('/proc/cpuinfo', $bufr) )
		{
			$processors = preg_split('/\s?\n\s?\n/', trim($bufr));
			foreach ( $processors as $processor )
			{
				$dev     = new Sys_Helper_Cpu();
				$details = preg_split("/\n/", $processor, -1, PREG_SPLIT_NO_EMPTY);
				foreach ( $details as $detail )
				{
					$arrBuff = preg_split('/\s+:\s+/', trim($detail));
					if ( count($arrBuff) == 2 )
					{
						switch ( strtolower($arrBuff[ 0 ]) )
						{
							case 'processor':
								if ( PSI_LOAD_BAR )
								{
									$dev->setLoad($this->_parseProcStat('cpu' . trim($arrBuff[ 1 ])));
								}
								break;
							case 'model name':
							case 'cpu':
								$dev->setModel($arrBuff[ 1 ]);
								break;
							case 'cpu mhz':
							case 'clock':
								$dev->setCpuSpeed($arrBuff[ 1 ]);
								break;
							case 'cycle frequency [hz]':
								$dev->setCpuSpeed($arrBuff[ 1 ] / 1000000);
								break;
							case 'cpu0clktck':
								$dev->setCpuSpeed(hexdec($arrBuff[ 1 ]) / 1000000); // Linux sparc64
								break;
							case 'l2 cache':
							case 'cache size':
								$dev->setL2Cache(preg_replace("/[a-zA-Z]/", "", $arrBuff[ 1 ]) * 1024);
								break;
							case 'bogomips':
							case 'cpu0bogo':
								$dev->setBogomips($arrBuff[ 1 ]);
								break;
							case 'flags':
								if ( preg_match("/vmx/", $arrBuff[ 1 ]) )
								{
									$dev->setVirt("vmx");
								}
								else if ( preg_match("/smv/", $arrBuff[ 1 ]) )
								{
									$dev->setVirt("smv");
								}
								break;
						}
					}
				}
				// sparc64 specific code follows
				// This adds the ability to display the cache that a CPU has
				// Originally made by Sven Blumenstein <bazik@gentoo.org> in 2004
				// Modified by Tom Weustink <freshy98@gmx.net> in 2004
				$sparclist = array (
					'SUNW,UltraSPARC@0,0',
					'SUNW,UltraSPARC-II@0,0',
					'SUNW,UltraSPARC@1c,0',
					'SUNW,UltraSPARC-IIi@1c,0',
					'SUNW,UltraSPARC-II@1c,0',
					'SUNW,UltraSPARC-IIe@0,0'
				);
				foreach ( $sparclist as $name )
				{
					if ( Sys_Helper_System::rfts('/proc/openprom/' . $name . '/ecache-size', $buf, 1, 32, false) )
					{
						$dev->setCache(base_convert($buf, 16, 10));
					}
				}
				// sparc64 specific code ends
				// XScale detection code
				if ( $dev->getModel() === "" )
				{
					foreach ( $details as $detail )
					{
						$arrBuff = preg_split('/\s+:\s+/', trim($detail));
						if ( count($arrBuff) == 2 )
						{
							switch ( strtolower($arrBuff[ 0 ]) )
							{
								case 'processor':
									$dev->setModel($arrBuff[ 1 ]);
									break;
								case 'bogomips':
									$dev->setCpuSpeed($arrBuff[ 1 ]); //BogoMIPS are not BogoMIPS on this CPU, it's the speed
									$dev->setBogomips(null); // no BogoMIPS available, unset previously set BogoMIPS
									break;
								case 'i size':
								case 'd size':
									if ( $dev->getCache() === null )
									{
										$dev->setL2Cache($arrBuff[ 1 ] * 1024);
									}
									else
									{
										$dev->setL2Cache($dev->getCache() + ($arrBuff[ 1 ] * 1024));
									}
									break;
							}
						}
					}
				}
				if ( Sys_Helper_System::rfts('/proc/acpi/thermal_zone/THRM/temperature', $buf, 1, 4096, false) )
				{
					$dev->setTemp(substr($buf, 25, 2));
				}

				if ( $dev->getModel() === "" )
				{
					$dev->setModel("unknown");
				}
				$this->sys->setCpus($dev);
			}
		}
	}

	/**
	 * Physical memory information and Swap Space information
	 *
	 * @return void
	 */
	private function _memory ()
	{

		if ( Sys_Helper_System::rfts('/proc/meminfo', $bufr) )
		{
			$bufe = preg_split("/\n/", $bufr, -1, PREG_SPLIT_NO_EMPTY);
			foreach ( $bufe as $buf )
			{
				if ( preg_match('/^MemTotal:\s+(.*)\s*kB/i', $buf, $ar_buf) )
				{
					$this->sys->setMemTotal($ar_buf[ 1 ] * 1024);
				}
				elseif ( preg_match('/^MemFree:\s+(.*)\s*kB/i', $buf, $ar_buf) )
				{
					$this->sys->setMemFree($ar_buf[ 1 ] * 1024);
				}
				elseif ( preg_match('/^Cached:\s+(.*)\s*kB/i', $buf, $ar_buf) )
				{
					$this->sys->setMemCache($ar_buf[ 1 ] * 1024);
				}
				elseif ( preg_match('/^Buffers:\s+(.*)\s*kB/i', $buf, $ar_buf) )
				{
					$this->sys->setMemBuffer($ar_buf[ 1 ] * 1024);
				}
			}
			$this->sys->setMemUsed($this->sys->getMemTotal() - $this->sys->getMemFree());
			// values for splitting memory usage
			if ( $this->sys->getMemCache() !== null && $this->sys->getMemBuffer() !== null )
			{
				$this->sys->setMemApplication($this->sys->getMemUsed() - $this->sys->getMemCache() - $this->sys->getMemBuffer());
			}
			if ( Sys_Helper_System::rfts('/proc/swaps', $bufr) )
			{
				$swaps = preg_split("/\n/", $bufr, -1, PREG_SPLIT_NO_EMPTY);
				unset($swaps[ 0 ]);
				foreach ( $swaps as $swap )
				{
					$ar_buf = preg_split('/\s+/', $swap, 5);
					$dev    = new Sys_Helper_Disk();
					$dev->setMountPoint($ar_buf[ 0 ]);
					$dev->setName("SWAP");
					$dev->setTotal($ar_buf[ 2 ] * 1024);
					$dev->setUsed($ar_buf[ 3 ] * 1024);
					$dev->setFree($dev->getTotal() - $dev->getUsed());
					$this->sys->setSwapDevices($dev);
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
	private function _distro ( $debug = false )
	{

		$list = @parse_ini_file(MODULES_PATH . 'Sys/data/distros.ini', true);
		if ( !$list )
		{
			return;
		}
		// We have the '2>/dev/null' because Ubuntu gives an error on this command which causes the distro to be unknown
		if ( Sys_Helper_System::executeProgram('lsb_release', '-a 2>/dev/null', $distro_info, $debug) )
		{
			$distro_tmp = preg_split("/\n/", $distro_info, -1, PREG_SPLIT_NO_EMPTY);
			foreach ( $distro_tmp as $info )
			{
				$info_tmp                 = preg_split('/:/', $info, 2);
				$distro[ $info_tmp[ 0 ] ] = trim($info_tmp[ 1 ]);
				if ( isset($distro[ 'Distributor ID' ]) && isset($list[ $distro[ 'Distributor ID' ] ][ 'Image' ]) )
				{
					$this->sys->setDistributionIcon($list[ $distro[ 'Distributor ID' ] ][ 'Image' ]);
				}
				if ( isset($distro[ 'Description' ]) )
				{
					$this->sys->setDistribution($distro[ 'Description' ]);
				}
			}
		}
		else
		{
			// Fall back in case 'lsb_release' does not exist ;)
			foreach ( $list as $section => $distribution )
			{
				if ( !isset($distribution[ "Files" ]) )
				{
					continue;
				}
				else
				{
					foreach ( preg_split("/;/", $distribution[ "Files" ], -1, PREG_SPLIT_NO_EMPTY) as $filename )
					{
						if ( file_exists($filename) )
						{
							Sys_Helper_System::rfts($filename, $buf);
							if ( isset($distribution[ "Image" ]) )
							{
								$this->sys->setDistributionIcon($distribution[ "Image" ]);
							}
							if ( isset($distribution[ "Name" ]) )
							{
								if ( $distribution[ "Name" ] == 'Synology' )
								{
									$this->sys->setDistribution($distribution[ "Name" ]);
								}
								else
								{
									$this->sys->setDistribution($distribution[ "Name" ] . " " . trim($buf));
								}
							}
							else
							{
								$this->sys->setDistribution(trim($buf));
							}

							return;
						}
					}
				}
			}
		}
	}

	/**
	 * get the information
	 *
	 * @return Void
	 */
	public function build ()
	{

		$this->_distro();
		$this->_kernel();
		$this->_uptime();
		$this->_cpuinfo();
		$this->_memory();
		$this->_loadavg();
	}

}
