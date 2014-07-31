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
 * @category     Helper
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Cpu.php
 */
class Sys_Helper_Cpu
{

	/**
	 * model of the cpu
	 *
	 * @var String
	 */
	private $_model = "";

	/**
	 * speed of the cpu in hertz
	 *
	 * @var Integer
	 */
	private $_cpuSpeed = 0;

	/**
	 * cache size in bytes, if available
	 *
	 * @var Integer
	 */
	private $_cacheL2 = null;

	/**
	 * cache size in bytes, if available
	 *
	 * @var Integer
	 */
	private $_cacheL3 = null;

	/**
	 * virtualization, if available
	 *
	 * @var String
	 */
	private $_virt = null;

	/**
	 * busspeed in hertz, if available
	 *
	 * @var Integer
	 */
	private $_busSpeed = null;

	/**
	 * temperature of the cpu, if available
	 *
	 * @var Integer
	 */
	private $_temp = null;

	/**
	 * bogomips of the cpu, if available
	 *
	 * @var Integer
	 */
	private $_bogomips = null;

	/**
	 * current load in percent of the cpu, if available
	 *
	 * @var Integer
	 */
	private $_load = null;

	/**
	 * Returns $_bogomips.
	 *
	 * @see Cpu::$_bogomips
	 *
	 * @return Integer
	 */
	public function getBogomips ()
	{

		return $this->_bogomips;
	}

	/**
	 * Sets $_bogomips.
	 *
	 * @param Integer $bogomips bogompis
	 *
	 * @see Cpu::$_bogomips
	 *
	 * @return Void
	 */
	public function setBogomips ( $bogomips )
	{

		$this->_bogomips = $bogomips;
	}

	/**
	 * Returns $_busSpeed.
	 *
	 * @see Cpu::$_busSpeed
	 *
	 * @return Integer
	 */
	public function getBusSpeed ()
	{

		return $this->_busSpeed;
	}

	/**
	 * Sets $_busSpeed.
	 *
	 * @param Integer $busSpeed busspeed
	 *
	 * @see Cpu::$_busSpeed
	 *
	 * @return Void
	 */
	public function setBusSpeed ( $busSpeed )
	{

		$this->_busSpeed = $busSpeed;
	}

	/**
	 * Returns $_cache.
	 *
	 *
	 * @return Integer
	 */
	public function getL2Cache ()
	{

		return $this->_cacheL2;
	}

	/**
	 * Sets $_cache.
	 *
	 * @param Integer $cache cache size
	 *
	 * @see Cpu::$_cache
	 *
	 * @return Void
	 */
	public function setL2Cache ( $cache )
	{

		$this->_cacheL2 = $cache;
	}

	/**
	 * Returns $_cache.
	 *
	 *
	 * @return Integer
	 */
	public function getL3Cache ()
	{

		return $this->_cacheL3;
	}

	/**
	 * Sets $_cache.
	 *
	 * @param Integer $cache cache size
	 *
	 * @see Cpu::$_cache
	 *
	 * @return Void
	 */
	public function setL3Cache ( $cache )
	{

		$this->_cacheL3 = $cache;
	}

	/**
	 * Returns $_virt.
	 *
	 * @see Cpu::$_virt
	 *
	 * @return String
	 */
	public function getVirt ()
	{

		return $this->_virt;
	}

	/**
	 * Sets $_virt.
	 *
	 * @param $virt
	 * @internal param String $_virt
	 *
	 * @see      Cpu::$_virt
	 *
	 * @return Void
	 */
	public function setVirt ( $virt )
	{

		$this->_virt = $virt;
	}

	/**
	 * Returns $_cpuSpeed.
	 *
	 * @see Cpu::$_cpuSpeed
	 *
	 * @return Integer
	 */
	public function getCpuSpeed ()
	{

		return $this->_cpuSpeed;
	}

	/**
	 * Sets $_cpuSpeed.
	 *
	 * @param Integer $cpuSpeed cpuspeed
	 *
	 * @see Cpu::$_cpuSpeed
	 *
	 * @return Void
	 */
	public function setCpuSpeed ( $cpuSpeed )
	{

		$this->_cpuSpeed = $cpuSpeed;
	}

	/**
	 * Returns $_model.
	 *
	 * @see Cpu::$_model
	 *
	 * @return String
	 */
	public function getModel ()
	{

		return $this->_model;
	}

	/**
	 * Sets $_model.
	 *
	 * @param String $model cpumodel
	 *
	 * @see Cpu::$_model
	 *
	 * @return Void
	 */
	public function setModel ( $model )
	{

		$this->_model = $model;
	}

	/**
	 * Returns $_temp.
	 *
	 * @see Cpu::$_temp
	 *
	 * @return Integer
	 */
	public function getTemp ()
	{

		return $this->_temp;
	}

	/**
	 * Sets $_temp.
	 *
	 * @param Integer $temp temperature
	 *
	 * @see Cpu::$_temp
	 *
	 * @return Void
	 */
	public function setTemp ( $temp )
	{

		$this->_temp = $temp;
	}

	/**
	 * Returns $_load.
	 *
	 * @see CpuDevice::$_load
	 *
	 * @return Integer
	 */
	public function getLoad ()
	{

		return $this->_load;
	}

	/**
	 * Sets $_load.
	 *
	 * @param Integer $load load percent
	 *
	 * @see CpuDevice::$_load
	 *
	 * @return Void
	 */
	public function setLoad ( $load )
	{

		$this->_load = $load;
	}

}
