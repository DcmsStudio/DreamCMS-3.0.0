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
 * @file         Disk.php
 */
class Sys_Helper_Disk
{

	/**
	 * name of the disk device
	 *
	 * @var String
	 */
	private $_name = "";

	/**
	 * type of the filesystem on the disk device
	 *
	 * @var String
	 */
	private $_fsType = "";

	/**
	 * diskspace that is free in bytes
	 *
	 * @var Integer
	 */
	private $_free = 0;

	/**
	 * diskspace that is used in bytes
	 *
	 * @var Integer
	 */
	private $_used = 0;

	/**
	 * total diskspace
	 *
	 * @var Integer
	 */
	private $_total = 0;

	/**
	 * mount point of the disk device if available
	 *
	 * @var String
	 */
	private $_mountPoint = null;

	/**
	 * additional options of the device, like mount options
	 *
	 * @var String
	 */
	private $_options = null;

	/**
	 * inodes usage in percent if available
	 *
	 * @var
	 */
	private $_percentInodesUsed = null;

	/**
	 * Returns PercentUsed calculated when function is called from internal values
	 *
	 * @see DiskDevice::$_total
	 * @see DiskDevice::$_used
	 *
	 * @return Integer
	 */
	public function getPercentUsed ()
	{

		if ( $this->_total > 0 )
		{
			return ceil($this->_used / $this->_total * 100);
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Returns $_PercentInodesUsed.
	 *
	 * @see DiskDevice::$_PercentInodesUsed
	 *
	 * @return Integer
	 */
	public function getPercentInodesUsed ()
	{

		return $this->_percentInodesUsed;
	}

	/**
	 * Sets $_PercentInodesUsed.
	 *
	 * @param Integer $percentInodesUsed inodes percent
	 *
	 * @see DiskDevice::$_PercentInodesUsed
	 *
	 * @return Void
	 */
	public function setPercentInodesUsed ( $percentInodesUsed )
	{

		$this->_percentInodesUsed = $percentInodesUsed;
	}

	/**
	 * Returns $_free.
	 *
	 * @see DiskDevice::$_free
	 *
	 * @return Integer
	 */
	public function getFree ()
	{

		return $this->_free;
	}

	/**
	 * Sets $_free.
	 *
	 * @param Integer $free free bytes
	 *
	 * @see DiskDevice::$_free
	 *
	 * @return Void
	 */
	public function setFree ( $free )
	{

		$this->_free = $free;
	}

	/**
	 * Returns $_fsType.
	 *
	 * @see DiskDevice::$_fsType
	 *
	 * @return String
	 */
	public function getFsType ()
	{

		return $this->_fsType;
	}

	/**
	 * Sets $_fsType.
	 *
	 * @param String $fsType filesystemtype
	 *
	 * @see DiskDevice::$_fsType
	 *
	 * @return Void
	 */
	public function setFsType ( $fsType )
	{

		$this->_fsType = $fsType;
	}

	/**
	 * Returns $_mountPoint.
	 *
	 * @see DiskDevice::$_mountPoint
	 *
	 * @return String
	 */
	public function getMountPoint ()
	{

		return $this->_mountPoint;
	}

	/**
	 * Sets $_mountPoint.
	 *
	 * @param String $mountPoint mountpoint
	 *
	 * @see DiskDevice::$_mountPoint
	 *
	 * @return Void
	 */
	public function setMountPoint ( $mountPoint )
	{

		$this->_mountPoint = $mountPoint;
	}

	/**
	 * Returns $_name.
	 *
	 * @see DiskDevice::$_name
	 *
	 * @return String
	 */
	public function getName ()
	{

		return $this->_name;
	}

	/**
	 * Sets $_name.
	 *
	 * @param String $name device name
	 *
	 * @see DiskDevice::$_name
	 *
	 * @return Void
	 */
	public function setName ( $name )
	{

		$this->_name = $name;
	}

	/**
	 * Returns $_options.
	 *
	 * @see DiskDevice::$_options
	 *
	 * @return String
	 */
	public function getOptions ()
	{

		return $this->_options;
	}

	/**
	 * Sets $_options.
	 *
	 * @param String $options additional options
	 *
	 * @see DiskDevice::$_options
	 *
	 * @return Void
	 */
	public function setOptions ( $options )
	{

		$this->_options = $options;
	}

	/**
	 * Returns $_total.
	 *
	 * @see DiskDevice::$_total
	 *
	 * @return Integer
	 */
	public function getTotal ()
	{

		return $this->_total;
	}

	/**
	 * Sets $_total.
	 *
	 * @param Integer $total total bytes
	 *
	 * @see DiskDevice::$_total
	 *
	 * @return Void
	 */
	public function setTotal ( $total )
	{

		$this->_total = $total;
	}

	/**
	 * Returns $_used.
	 *
	 * @see DiskDevice::$_used
	 *
	 * @return Integer
	 */
	public function getUsed ()
	{

		return $this->_used;
	}

	/**
	 * Sets $_used.
	 *
	 * @param Integer $used used bytes
	 *
	 * @see DiskDevice::$_used
	 *
	 * @return Void
	 */
	public function setUsed ( $used )
	{

		$this->_used = $used;
	}

}
