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
 * @file         Darwin.php
 */
class Sys_Model_Darwin extends Sys_Model_BsdCommon
{

	/**
	 *
	 */
	public function __construct ()
	{

		parent::__construct();
	}

	/**
	 * get a value from sysctl command
	 *
	 * @param string $key key of the value to get
	 *
	 * @param bool   $debug
	 * @return string
	 */
	protected function grabkey ( $key, $debug = false )
	{

		if ( Sys_Helper_System::executeProgram('sysctl', $key, $s, $debug) )
		{
			$s = preg_replace('/' . $key . ': /', '', $s);
			$s = preg_replace('/' . $key . ' = /', '', $s);

			return $s;
		}
		else
		{
			return '';
		}
	}

	/**
	 * get a value from ioreg command
	 *
	 * @param string $key key of the value to get
	 *
	 * @param bool   $debug
	 * @return string
	 */
	protected function _grabioreg ( $key, $debug = false )
	{

		if ( Sys_Helper_System::executeProgram('ioreg', '-c "' . $key . '"', $s, $debug) )
		{
			/* delete newlines */
			$s = preg_replace("/\s+/", " ", $s);
			/* new newlines */
			$s = preg_replace("/[\|\t ]*\+\-\o/", "\n", $s);
			/* combine duplicate whitespaces and some chars */
			$s = preg_replace("/[\|\t ]+/", " ", $s);

			$lines = preg_split("/\n/", $s, -1, PREG_SPLIT_NO_EMPTY);
			$out   = "";
			foreach ( $lines as $line )
			{
				if ( preg_match('/^([^<]*) <class ' . $key . ',/', $line) )
				{
					$out .= $line . "\n";
				}
			}

			return $out;
		}
		else
		{
			return '';
		}
	}

	/**
	 * UpTime
	 * time the system is running
	 *
	 * @param bool $debug
	 * @return void
	 */
	protected function _uptime ( $debug = false )
	{

		if ( Sys_Helper_System::executeProgram('sysctl', '-n kern.boottime', $a, $debug) )
		{
			$tmp = explode(" ", $a);
			if ( $tmp[ 0 ] == "{" )
			{ /* kern.boottime= { sec = 1096732600, usec = 885425 } Sat Oct 2 10:56:40 2004 */
				$data = trim($tmp[ 3 ], ",");
				$this->sys->setUptime(time() - $data);
			}
			else
			{ /* kern.boottime= 1096732600 */
				$this->sys->setUptime(time() - $a);
			}
		}
	}

	/**
	 * get CPU information
	 *
	 * @param bool $debug
	 * @return void
	 */
	protected function cpuinfo ( $debug = false )
	{

		$dev = new Sys_Helper_Cpu();
		if ( Sys_Helper_System::executeProgram('hostinfo', '| grep "Processor type"', $buf, $debug) )
		{


			$dev->setModel(preg_replace('/Processor type: /', '', $buf));
			$buf = $this->grabkey('hw.model');
			if ( Sys_Helper_System::rfts(MODULES_PATH . 'Sys/data/ModelTranslation.txt', $buffer) )
			{
				$buffer = preg_split("/\n/", $buffer, -1, PREG_SPLIT_NO_EMPTY);
				foreach ( $buffer as $line )
				{
					$ar_buf = preg_split("/:/", $line, 2);
					if ( trim($buf) === trim($ar_buf[ 0 ]) )
					{
						$dev->setModel(trim($ar_buf[ 1 ]));
					}
				}


				if ( Sys_Helper_System::executeProgram('sysctl', '-a machdep.cpu.brand_string', $bufs, $debug) )
				{
					if ( trim($bufs) )
					{
						$dev->setModel(preg_replace('/machdep\.cpu\.brand_string:\s*/i', '', trim($bufs)));
					}
				}
			}
		}


		if ( Sys_Helper_System::executeProgram('hostinfo', '| grep "Load average"', $buf, $debug) )
		{
			$dev->setLoad(preg_replace('/Load average:\s*/is', '', $buf));
		}


		$dev->setCpuSpeed(round($this->grabkey('hw.cpufrequency') / 1000000));
		$dev->setBusSpeed(round($this->grabkey('hw.busfrequency') / 1000000));
		$dev->setL2Cache(round($this->grabkey('hw.l2cachesize')));
		$dev->setL3Cache(round($this->grabkey('hw.l3cachesize')));


		for ( $i = $this->grabkey('hw.ncpu'); $i > 0; $i-- )
		{
			$this->sys->setCpus($dev);
		}
	}

	/**
	 * get memory and swap information
	 *
	 * @param bool $debug
	 * @return void
	 */
	protected function memory ( $debug = false )
	{

		$s = $this->grabkey('hw.memsize');
		if ( Sys_Helper_System::executeProgram('vm_stat', '', $pstat, $debug) )
		{
			$lines  = preg_split("/\n/", $pstat, -1, PREG_SPLIT_NO_EMPTY);
			$ar_buf = preg_split("/\s+/", $lines[ 1 ], 19);
			// calculate free memory from page sizes (each page = 4MB)
			$this->sys->setMemTotal($s);
			$this->sys->setMemFree($ar_buf[ 2 ] * 4 * 1024);
			$this->sys->setMemUsed($this->sys->getMemTotal() - $this->sys->getMemFree());


			if ( Sys_Helper_System::executeProgram('sysctl', 'vm.swapusage | colrm 1 22', $swapBuff, $debug) )
			{
				$swap1 = preg_split('/M/', $swapBuff);
				$swap2 = preg_split('/=/', $swap1[ 1 ]);
				$swap3 = preg_split('/=/', $swap1[ 2 ]);


				$dev = new Sys_Helper_Disk();
				$dev->setName('SWAP');
				$dev->setMountPoint('SWAP');
				$dev->setFsType('swap');
				$dev->setTotal($swap1[ 0 ] * 1024 * 1024);
				$dev->setUsed($swap2[ 1 ] * 1024 * 1024);
				$dev->setFree($swap3[ 1 ] * 1024 * 1024);

				$this->sys->setSwapDevices($dev);
			}
		}
	}

	/**
	 * get icon name
	 *
	 * @param bool $debug
	 * @return void
	 */
	protected function distro ( $debug = false )
	{

		$this->sys->setDistributionIcon('Darwin');
		if ( !Sys_Helper_System::executeProgram('system_profiler', 'SPSoftwareDataType', $buffer, $debug) )
		{
			parent::distro();
		}
		else
		{
			$arrBuff = preg_split("/\n/", $buffer, -1, PREG_SPLIT_NO_EMPTY);
			foreach ( $arrBuff as $line )
			{
				$arrLine = preg_split("/:/", $line, -1, PREG_SPLIT_NO_EMPTY);
				if ( trim($arrLine[ 0 ]) === "System Version" )
				{
					$distro = trim($arrLine[ 1 ]);

					if ( preg_match('/^Mac OS/', $distro) )
					{
						$this->sys->setDistributionIcon('Apple');
					}

					$this->sys->setDistribution($distro);

					return;
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

		$this->distro();
		$this->memory();
		$this->cpuinfo();
		$this->kernel();
		$this->loadavg();
		$this->_uptime();
	}

}
