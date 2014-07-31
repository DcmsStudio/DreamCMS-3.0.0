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
 * @file         FreeBSD.php
 */
class Sys_Model_FreeBSD extends Sys_Model_BsdCommon
{

	/**
	 * define the regexp for log parser
	 */
	public function __construct ()
	{

		parent::__construct();

		$this->setCPURegExp1("CPU: (.*) \((.*)-MHz (.*)\)");
		$this->setCPURegExp2("/(.*) ([0-9]+) ([0-9]+) ([0-9]+) ([0-9]+)/");


		/*
		  $this->setSCSIRegExp1("^(.*): <(.*)> .*SCSI.*device");
		  $this->setSCSIRegExp2("^(da[0-9]): (.*)MB ");
		  $this->setPCIRegExp1("/(.*): <(.*)>(.*) pci[0-9]$/");
		  $this->setPCIRegExp2("/(.*): <(.*)>.* at [.0-9]+ irq/");
		 *
		 */
	}

	/**
	 * UpTime
	 * time the system is running
	 *
	 * @return void
	 */
	private function _uptime ()
	{

		$s = preg_split('/ /', $this->grabkey('kern.boottime'));
		$a = preg_replace('/,/', '', $s[ 3 ]);
		$this->sys->setUptime(time() - $a);
	}

	/**
	 * extend the memory information with additional values
	 *
	 * @return void
	 */
	private function _memoryadditional ()
	{

		$pagesize = $this->grabkey("hw.pagesize");
		$this->sys->setMemCache($this->grabkey("vm.stats.vm.v_cache_count") * $pagesize);
		$this->sys->setMemApplication($this->grabkey("vm.stats.vm.v_active_count") * $pagesize);
		$this->sys->setMemBuffer($this->sys->getMemTotal() - $this->sys->getMemApplication() - $this->sys->getMemCache());
	}

	/**
	 * get the information
	 *
	 * @return Void
	 */
	function build ()
	{

		parent::build();

		$this->_memoryadditional();
		$this->_uptime();
	}

}
