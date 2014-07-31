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
 * @file         System.php
 */
class Sys_Helper_System
{

	/**
	 * detailed Information about the kernel
	 *
	 * @var String
	 */
	private $_kernel = "Unknown";

	/**
	 * name of the distribution
	 *
	 * @var String
	 */
	private $_distribution = "Unknown";

	/**
	 * icon of the distribution (must be available in phpSysInfo)
	 *
	 * @var String
	 */
	private $_distributionIcon = "unknown.png";

	/**
	 * time in sec how long the system is running
	 *
	 * @var Integer
	 */
	private $_uptime = 0;

	/**
	 * count of users that are currently logged in
	 *
	 * @var Integer
	 */
	private $_users = 0;

	/**
	 * load of the system
	 *
	 * @var String
	 */
	private $_load = "";

	/**
	 * load of the system in percent (all cpus, if more than one)
	 *
	 * @var Integer
	 */
	private $_loadPercent = null;

	/**
	 * array with cpu devices
	 *
	 * @var Array
	 */
	private $_cpus = array ();

	/**
	 * array with disk devices
	 *
	 * @var Array
	 */
	private $_diskDevices = array ();

	/**
	 * array with swap devices
	 *
	 *
	 * @var Array
	 */
	private $_swapDevices = array ();

	/**
	 * free memory in bytes
	 *
	 * @var Integer
	 */
	private $_memFree = 0;

	/**
	 * total memory in bytes
	 *
	 * @var Integer
	 */
	private $_memTotal = 0;

	/**
	 * used memory in bytes
	 *
	 * @var Integer
	 */
	private $_memUsed = 0;

	/**
	 * used memory by applications in bytes
	 *
	 * @var Integer
	 */
	private $_memApplication = null;

	/**
	 * used memory for buffers in bytes
	 *
	 * @var Integer
	 */
	private $_memBuffer = null;

	/**
	 * used memory for cache in bytes
	 *
	 * @var Integer
	 */
	private $_memCache = null;

	/**
	 * @var
	 */
	protected $sys;

	/**
	 * @param Sys_Helper_System $sys
	 */
	public function setSys ( Sys_Helper_System $sys )
	{

		$this->sys = $sys;
	}

	/**
	 * Find a system program, do also path checking when not running on WINNT
	 * on WINNT we simply return the name with the exe extension to the program name
	 *
	 * @param string $strProgram name of the program
	 *
	 * @return string complete path and name of the program
	 */
	private static function _findProgram ( $strProgram )
	{

		$arrPath = array ();
		if ( PHP_OS == 'WINNT' )
		{
			$strProgram .= '.exe';
			$arrPath = preg_split('/;/', getenv("Path"), -1, PREG_SPLIT_NO_EMPTY);
		}
		else
		{
			$arrPath = preg_split('/:/', getenv("PATH"), -1, PREG_SPLIT_NO_EMPTY);
		}
		if ( PSI_ADD_PATHS !== false )
		{
			$addpaths = preg_split('/,/', PSI_ADD_PATHS, -1, PREG_SPLIT_NO_EMPTY);
			$arrPath  = array_merge($addpaths, $arrPath); // In this order so $addpaths is before $arrPath when looking for a program
		}
		//add some default paths if we still have no paths here
		if ( empty($arrPath) && PHP_OS != 'WINNT' )
		{
			array_push($arrPath, '/bin', '/sbin', '/usr/bin', '/usr/sbin', '/usr/local/bin', '/usr/local/sbin');
		}
		// If open_basedir defined, fill the $open_basedir array with authorized paths,. (Not tested when no open_basedir restriction)
		if ( (bool)ini_get('open_basedir') )
		{
			$open_basedir = preg_split('/:/', ini_get('open_basedir'), -1, PREG_SPLIT_NO_EMPTY);
		}
		foreach ( $arrPath as $strPath )
		{
			// To avoid "open_basedir restriction in effect" error when testing paths if restriction is enabled
			if ( (isset($open_basedir) && !in_array($strPath, $open_basedir)) || !is_dir($strPath) )
			{
				continue;
			}
			$strProgrammpath = $strPath . "/" . $strProgram;
			if ( is_executable($strProgrammpath) )
			{
				return $strProgrammpath;
			}
		}
	}

	/**
	 * Execute a system program. return a trim()'d result.
	 * does very crude pipe checking.  you need ' | ' for it to work
	 * ie $program = CommonFunctions::executeProgram('netstat', '-anp | grep LIST');
	 * NOT $program = CommonFunctions::executeProgram('netstat', '-anp|grep LIST');
	 *
	 * @param string  $strProgramname name of the program
	 * @param string  $strArgs        arguments to the program
	 * @param string  &$strBuffer     output of the command
	 * @param boolean $booErrorRep    en- or disables the reporting of errors which should be logged
	 *
	 * @throws BaseException
	 * @return boolean command successfull or not
	 */
	public static function executeProgram ( $strProgramname, $strArgs, &$strBuffer, $booErrorRep = true )
	{

		$strBuffer  = '';
		$strError   = '';
		$pipes      = array ();
		$strProgram = self::_findProgram($strProgramname);

		if ( !$strProgram )
		{
			if ( $booErrorRep )
			{
				throw new BaseException('program "' . $strProgramname . '" not found on the machine');
			}

			return false;
		}

		// see if we've gotten a |, if we have we need to do path checking on the cmd
		if ( $strArgs )
		{
			$arrArgs = preg_split('/ /', $strArgs, -1, PREG_SPLIT_NO_EMPTY);
			for ( $i = 0, $cnt_args = count($arrArgs); $i < $cnt_args; $i++ )
			{
				if ( $arrArgs[ $i ] == '|' )
				{
					$strCmd    = $arrArgs[ $i + 1 ];
					$strNewcmd = self::_findProgram($strCmd);
					$strArgs   = preg_replace("/\| " . $strCmd . '/', "| " . $strNewcmd, $strArgs);
				}
			}
		}
		$descriptorspec = array (
			0 => array (
				"pipe",
				"r"
			),
			1 => array (
				"pipe",
				"w"
			),
			2 => array (
				"pipe",
				"w"
			)
		);
		$process        = proc_open($strProgram . " " . $strArgs, $descriptorspec, $pipes);
		if ( is_resource($process) )
		{
			$strBuffer .= self::_timeoutfgets($pipes, $strBuffer, $strError);
			$return_value = proc_close($process);
		}
		$strError  = trim($strError);
		$strBuffer = trim($strBuffer);

		if ( !empty($strError) && $return_value <> 0 )
		{
			if ( $booErrorRep )
			{
				throw new BaseException('program "' . $strProgram . '" error:' . $strError . " \nReturn value: " . $return_value);
			}

			return false;
		}
		if ( !empty($strError) )
		{
			if ( $booErrorRep )
			{
				throw new BaseException('program "' . $strProgram . '" error:' . $strError . " \nReturn value: " . $return_value);
			}

			return true;
		}

		return true;
	}

	/**
	 * get the content of stdout/stderr with the option to set a timeout for reading
	 *
	 * @param array   $pipes array of file pointers for stdin, stdout, stderr (proc_open())
	 * @param string  &$out  target string for the output message (reference)
	 * @param string  &$err  target string for the error message (reference)
	 * @param integer $sek   timeout value in seconds
	 *
	 * @return void
	 */
	private static function _timeoutfgets ( $pipes, &$out, &$err, $sek = 10 )
	{

		// fill output string
		$time = $sek;
		$w    = null;
		$e    = null;

		while ( $time >= 0 )
		{
			$read = array (
				$pipes[ 1 ]
			);
			/*
			  while (!feof($read[0]) && ($n = stream_select($read, $w, $e, $time)) !== false && $n > 0 && strlen($c = fgetc($read[0])) > 0) {
			  $out .= $c;
			 */
			while ( !feof($read[ 0 ]) && ($n = stream_select($read, $w, $e, $time)) !== false && $n > 0 )
			{
				$out .= fread($read[ 0 ], 4096);
			}
			--$time;
		}
		// fill error string
		$time = $sek;
		while ( $time >= 0 )
		{
			$read = array (
				$pipes[ 2 ]
			);
			/*
			  while (!feof($read[0]) && ($n = stream_select($read, $w, $e, $time)) !== false && $n > 0 && strlen($c = fgetc($read[0])) > 0) {
			  $err .= $c;
			 */
			while ( !feof($read[ 0 ]) && ($n = stream_select($read, $w, $e, $time)) !== false && $n > 0 )
			{
				$err .= fread($read[ 0 ], 4096);
			}
			--$time;
		}
	}

	/**
	 * read a file and return the content as a string
	 *
	 * @param string  $strFileName name of the file which should be read
	 * @param string  &$strRet     content of the file (reference)
	 * @param integer $intLines    control how many lines should be read
	 * @param integer $intBytes    control how many bytes of each line should be read
	 * @param boolean $booErrorRep en- or disables the reporting of errors which should be logged
	 *
	 * @throws BaseException
	 * @return boolean command successfull or not
	 */
	public static function rfts ( $strFileName, &$strRet, $intLines = 0, $intBytes = 4096, $booErrorRep = true )
	{

		$strFile    = "";
		$intCurLine = 1;

		if ( file_exists($strFileName) )
		{
			if ( $fd = fopen($strFileName, 'r') )
			{
				while ( !feof($fd) )
				{
					$strFile .= fgets($fd, $intBytes);
					if ( $intLines <= $intCurLine && $intLines != 0 )
					{
						break;
					}
					else
					{
						$intCurLine++;
					}
				}
				fclose($fd);
				$strRet = $strFile;
			}
			else
			{
				if ( $booErrorRep )
				{
					throw new BaseException('fopen(' . $strFileName . ') file can not read by DreamCMS');
				}

				return false;
			}
		}
		else
		{
			if ( $booErrorRep )
			{
				throw new BaseException('file_exists(' . $strFileName . ') the file does not exist on your machine');
			}

			return false;
		}

		return true;
	}

	/**
	 * reads a directory and return the name of the files and directorys in it
	 *
	 * @param string  $strPath     path of the directory which should be read
	 * @param boolean $booErrorRep en- or disables the reporting of errors which should be logged
	 *
	 * @throws BaseException
	 * @return array content of the directory excluding . and ..
	 */
	public static function gdc ( $strPath, $booErrorRep = true )
	{

		$arrDirectoryContent = array ();

		if ( is_dir($strPath) )
		{
			if ( $handle = opendir($strPath) )
			{
				while ( ($strFile = readdir($handle)) !== false )
				{
					if ( $strFile != "." && $strFile != ".." )
					{
						$arrDirectoryContent[ ] = $strFile;
					}
				}
				closedir($handle);
			}
			else
			{
				if ( $booErrorRep )
				{
					throw new BaseException('opendir(' . $strPath . ') directory can not be read by DreamCMS');
				}
			}
		}
		else
		{
			if ( $booErrorRep )
			{
				throw new BaseException('is_dir(' . $strPath . ') directory does not exist on your machine');
			}
		}

		return $arrDirectoryContent;
	}

	/**
	 * return percent of used memory
	 *
	 * @see System::_memUsed
	 * @see System::_memTotal
	 *
	 * @return Integer
	 */
	public function getMemPercentUsed ()
	{

		if ( $this->_memTotal > 0 )
		{
			return round($this->_memUsed / $this->_memTotal * 100, 2);
		}
		else
		{
			return 0;
		}
	}

	/**
	 * return percent of used memory for applications
	 *
	 * @see System::_memApplication
	 * @see System::_memTotal
	 *
	 * @return Integer
	 */
	public function getMemPercentApplication ()
	{

		if ( $this->_memApplication !== null )
		{
			if ( $this->_memApplication > 0 )
			{
				return round($this->_memApplication / $this->_memTotal * 100, 2);
			}
			else
			{
				return 0;
			}
		}
		else
		{
			return null;
		}
	}

	/**
	 * return percent of used memory for cache
	 *
	 * @see System::_memCache
	 * @see System::_memTotal
	 *
	 * @return Integer
	 */
	public function getMemPercentCache ()
	{

		if ( $this->_memCache !== null )
		{
			if ( $this->_memCache > 0 )
			{
				return round($this->_memCache / $this->_memTotal * 100, 2);
			}
			else
			{
				return 0;
			}
		}
		else
		{
			return null;
		}
	}

	/**
	 * return percent of used memory for buffer
	 *
	 * @see System::_memBuffer
	 * @see System::_memTotal
	 *
	 * @return Integer
	 */
	public function getMemPercentBuffer ()
	{

		if ( $this->_memBuffer !== null )
		{
			if ( $this->_memBuffer > 0 )
			{
				return round($this->_memBuffer / $this->_memTotal * 100, 2);
			}
			else
			{
				return 0;
			}
		}
		else
		{
			return null;
		}
	}

	/**
	 * Returns total free swap space
	 *
	 * @see System::_swapDevices
	 * @see DiskDevice::getFree()
	 *
	 * @return Integer
	 */
	public function getSwapFree ()
	{

		if ( count($this->_swapDevices) > 0 )
		{
			$free = 0;
			foreach ( $this->_swapDevices as $dev )
			{
				$free += $dev->getFree();
			}

			return $free;
		}

		return null;
	}

	/**
	 * Returns total swap space
	 *
	 * @see System::_swapDevices
	 * @see DiskDevice::getTotal()
	 *
	 * @return Integer
	 */
	public function getSwapTotal ()
	{

		if ( count($this->_swapDevices) > 0 )
		{
			$total = 0;
			foreach ( $this->_swapDevices as $dev )
			{
				$total += $dev->getTotal();
			}

			return $total;
		}
		else
		{
			return null;
		}
	}

	/**
	 * Returns total used swap space
	 *
	 * @see System::_swapDevices
	 * @see DiskDevice::getUsed()
	 *
	 * @return Integer
	 */
	public function getSwapUsed ()
	{

		if ( count($this->_swapDevices) > 0 )
		{
			$used = 0;
			foreach ( $this->_swapDevices as $dev )
			{
				$used += $dev->getUsed();
			}

			return $used;
		}
		else
		{
			return null;
		}
	}

	/**
	 * return percent of total swap space used
	 *
	 * @see System::getSwapUsed()
	 * @see System::getSwapTotal()
	 *
	 * @return Integer
	 */
	public function getSwapPercentUsed ()
	{

		if ( $this->getSwapTotal() !== null )
		{
			if ( $this->getSwapTotal() > 0 )
			{
				return round($this->getSwapUsed() / $this->getSwapTotal() * 100, 2);
			}
			else
			{
				return 0;
			}
		}
		else
		{
			return null;
		}
	}

	/**
	 * Returns $_distribution.
	 *
	 * @see System::$_distribution
	 *
	 * @return String
	 */
	public function getDistribution ()
	{

		return $this->_distribution;
	}

	/**
	 * Sets $_distribution.
	 *
	 * @param String $distribution distributionname
	 *
	 * @see System::$_distribution
	 *
	 * @return Void
	 */
	public function setDistribution ( $distribution )
	{

		$this->_distribution = $distribution;
	}

	/**
	 * Returns $_distributionIcon.
	 *
	 * @see System::$_distributionIcon
	 *
	 * @return String
	 */
	public function getDistributionIcon ()
	{

		return $this->_distributionIcon;
	}

	/**
	 * Sets $_distributionIcon.
	 *
	 * @param String $distributionIcon distribution icon
	 *
	 * @see System::$_distributionIcon
	 *
	 * @return Void
	 */
	public function setDistributionIcon ( $distributionIcon )
	{

		$this->_distributionIcon = $distributionIcon;
	}

	/**
	 * Returns $_hostname.
	 *
	 * @see System::$_hostname
	 *
	 * @return String
	 */
	public function getHostname ()
	{

		return $this->_hostname;
	}

	/**
	 * Sets $_hostname.
	 *
	 * @param String $hostname hostname
	 *
	 * @see System::$_hostname
	 *
	 * @return Void
	 */
	public function setHostname ( $hostname )
	{

		$this->_hostname = $hostname;
	}

	/**
	 * Returns $_ip.
	 *
	 * @see System::$_ip
	 *
	 * @return String
	 */
	public function getIp ()
	{

		return $this->_ip;
	}

	/**
	 * Sets $_ip.
	 *
	 * @param String $ip IP
	 *
	 * @see System::$_ip
	 *
	 * @return Void
	 */
	public function setIp ( $ip )
	{

		$this->_ip = $ip;
	}

	/**
	 * Returns $_kernel.
	 *
	 * @see System::$_kernel
	 *
	 * @return String
	 */
	public function getKernel ()
	{

		return $this->_kernel;
	}

	/**
	 * Sets $_kernel.
	 *
	 * @param String $kernel kernelname
	 *
	 * @see System::$_kernel
	 *
	 * @return Void
	 */
	public function setKernel ( $kernel )
	{

		$this->_kernel = $kernel;
	}

	/**
	 * Returns $_load.
	 *
	 * @see System::$_load
	 *
	 * @return String
	 */
	public function getLoad ()
	{

		return $this->_load;
	}

	/**
	 * Sets $_load.
	 *
	 * @param String $load current system load
	 *
	 * @see System::$_load
	 *
	 * @return Void
	 */
	public function setLoad ( $load )
	{

		$this->_load = $load;
	}

	/**
	 * Returns $_loadPercent.
	 *
	 * @see System::$_loadPercent
	 *
	 * @return Integer
	 */
	public function getLoadPercent ()
	{

		return $this->_loadPercent;
	}

	/**
	 * Sets $_loadPercent.
	 *
	 * @param Integer $loadPercent load percent
	 *
	 * @see System::$_loadPercent
	 *
	 * @return Void
	 */
	public function setLoadPercent ( $loadPercent )
	{

		$this->_loadPercent = $loadPercent;
	}

	/**
	 * Returns $_uptime.
	 *
	 * @see System::$_uptime
	 *
	 * @return Integer
	 */
	public function getUptime ()
	{

		return $this->_uptime;
	}

	/**
	 * Sets $_uptime.
	 *
	 * @param Interger $uptime uptime
	 *
	 * @see System::$_uptime
	 *
	 * @return Void
	 */
	public function setUptime ( $uptime )
	{

		$this->_uptime = $uptime;
	}

	/**
	 * Returns $_users.
	 *
	 * @see System::$_users
	 *
	 * @return Integer
	 */
	public function getUsers ()
	{

		return $this->_users;
	}

	/**
	 * Sets $_users.
	 *
	 * @param Integer $users user count
	 *
	 * @see System::$_users
	 *
	 * @return Void
	 */
	public function setUsers ( $users )
	{

		$this->_users = $users;
	}

	/**
	 * Returns $_cpus.
	 *
	 * @see System::$_cpus
	 *
	 * @return Array
	 */
	public function getCpus ()
	{

		return $this->_cpus;
	}

	/**
	 * Sets $_cpus.
	 *
	 * @param Cpu $cpus cpu device
	 *
	 * @return Void
	 */
	public function setCpus ( $cpus )
	{

		array_push($this->_cpus, $cpus);
	}

	/**
	 * Returns $_memApplication.
	 *
	 * @return Integer
	 */
	public function getMemApplication ()
	{

		return $this->_memApplication;
	}

	/**
	 * Sets $_memApplication.
	 *
	 * @param Integer $memApplication application memory
	 *
	 * @return Void
	 */
	public function setMemApplication ( $memApplication )
	{

		$this->_memApplication = $memApplication;
	}

	/**
	 * Returns $_memBuffer.
	 *
	 * @return Integer
	 */
	public function getMemBuffer ()
	{

		return $this->_memBuffer;
	}

	/**
	 * Sets $_memBuffer.
	 *
	 * @param Integer $memBuffer buffer memory
	 *
	 * @return Void
	 */
	public function setMemBuffer ( $memBuffer )
	{

		$this->_memBuffer = $memBuffer;
	}

	/**
	 * Returns $_memCache.
	 *
	 * @return Integer
	 */
	public function getMemCache ()
	{

		return $this->_memCache;
	}

	/**
	 * Sets $_memCache.
	 *
	 * @param Integer $memCache cache memory
	 *
	 * @return Void
	 */
	public function setMemCache ( $memCache )
	{

		$this->_memCache = $memCache;
	}

	/**
	 * Returns $_memFree.
	 *
	 * @return Integer
	 */
	public function getMemFree ()
	{

		return $this->_memFree;
	}

	/**
	 * Sets $_memFree.
	 *
	 * @param Integer $memFree free memory
	 *
	 * @return Void
	 */
	public function setMemFree ( $memFree )
	{

		$this->_memFree = $memFree;
	}

	/**
	 * Returns $_memTotal.
	 *
	 * @return Integer
	 */
	public function getMemTotal ()
	{

		return $this->_memTotal;
	}

	/**
	 * Sets $_memTotal.
	 *
	 * @param Integer $memTotal total memory
	 *
	 * @return Void
	 */
	public function setMemTotal ( $memTotal )
	{

		$this->_memTotal = $memTotal;
	}

	/**
	 * Returns $_memUsed.
	 *
	 * @return Integer
	 */
	public function getMemUsed ()
	{

		return $this->_memUsed;
	}

	/**
	 * Sets $_memUsed.
	 *
	 * @param Integer $memUsed used memory
	 *
	 * @return Void
	 */
	public function setMemUsed ( $memUsed )
	{

		$this->_memUsed = $memUsed;
	}

	/**
	 * Returns $_swapDevices.
	 *
	 * @see System::$_swapDevices
	 *
	 * @return Array
	 */
	public function getSwapDevices ()
	{

		return $this->_swapDevices;
	}

	/**
	 * Sets $_swapDevices.
	 *
	 * @param DiskDevice $swapDevices swap devices
	 *
	 * @see System::$_swapDevices
	 * @see DiskDevice
	 *
	 * @return Void
	 */
	public function setSwapDevices ( $swapDevices )
	{

		array_push($this->_swapDevices, $swapDevices);
	}

}
