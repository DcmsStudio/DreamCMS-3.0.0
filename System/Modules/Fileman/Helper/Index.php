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
 * @package
 * @version      3.0.0 Beta
 * @category
 * @copyright    2008-2014 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */

class Fileman_Helper_Index extends Loader
{

	/**
	 * @var Fileman_Helper_Base
	 */
	protected static $inst = null;

	/**
	 * @var string|null
	 */
	protected $_path = null;

	/**
	 * @var string|null
	 */
	protected $_indexBasePath = null;

	/**
	 * @var string|null
	 */
	protected $_file = null;

	protected $_excludeDirs = null;

	/**
	 * @return Fileman_Helper_Base
	 */
	public static function getInstance ()
	{

		if ( is_null(self::$inst) )
		{
			self::$inst = new Fileman_Helper_Index();

			if ( defined('PAGE_CACHE_PATH') )
			{
				self::$inst->addExclude( PAGE_CACHE_PATH );
			}
		}

		return self::$inst;
	}


	public function addExclude ( $path )
	{
		if ( !is_array($this->_excludeDirs) ) {
			$this->_excludeDirs = array();
		}
		$this->_excludeDirs[] = $path;

		return $this;
	}


	/**
	 * @param string $path
	 * @return Fileman_Helper_Base
	 */
	public function setIndexBasePath ( $path )
	{

		$this->_indexBasePath = $path;

		return $this;
	}


	/**
	 * @param null|string $path
	 * @return Fileman_Helper_Base
	 */
	public function setPath ( $path = null )
	{

		$this->_path = $path;

		return $this;
	}

	/**
	 * @param null|string $file
	 * @return Fileman_Helper_Base
	 */

	public function setFile ( $file = null )
	{

		$this->_file = $file;

		return $this;
	}


	/**
	 * @param string $hash
	 * @return Fileman_Helper_Base
	 */
	public function removeIndexByHash ( $hash )
	{
		$this->db->query('UPDATE %tp%filemanager SET state = ? WHERE hash = ?', TRASH_MODE, $hash);
		// $this->db->query('DELETE FROM %tp%filemanager WHERE hash = ?', $hash);

		return $this;
	}


	/**
	 * @param null|string $fullpath
	 * @param null|string $name
	 * @return Fileman_Helper_Base
	 * @throws BaseException
	 */
	public function removeFromIndex ( $fullpath = null, $name = null )
	{

		if ( is_string($fullpath) && $fullpath && is_string($name) && $name )
		{
			$this->db->query('DELETE FROM %tp%filemanager WHERE path = ?, filename = ?', $fullpath, $name);

			return $this;
		}
		else
		{
			if ( $this->_path && $this->_file )
			{
				$this->db->query('DELETE FROM %tp%filemanager WHERE path = ?, filename = ?', $this->_path, $this->_file);

				return $this;
			}
			else
			{
				throw new BaseException( 'Invalid path and filename to remove from Filemanager index' );
			}
		}
	}

	/**
	 * create the full file index
	 */
	public function updateIndex ()
	{

		$all = array ();
		$this->_getFiles($this->_indexBasePath, true, $all);
		$this->insert($all);

	}

	/**
	 * refresh index from path only
	 *
	 * @param string $dirpath
	 * @param bool   $inclSubs if set true the will update $fullpath and all sub directorys.
	 *                         default is false
	 */
	public function updateIndexFromPath ( $dirpath, $inclSubs = false, $target = null, $name = null )
	{

		$all = array ();


		if ($dirpath && $target && $name)
		{
			// trigger rename.fileman


			if (strpos($dirpath, $this->_indexBasePath ) === false)
			{
				$dirpath = $this->_indexBasePath . $dirpath;
			}

			$helper = new Fileman_Helper_Base();


			if (is_dir($dirpath . $name )) {
				$hash = $helper->_hash($target);

				$all[] = array('dirname' => $name, 'path' => $dirpath );
				$this->insert($all, $hash);
				return;
			}
			elseif (is_file($dirpath . $name )) {

				$hash = $helper->_hash($target);
				$all[] = array('filename' => $name, 'path' => $dirpath );

				$this->insert($all, $hash);
				return;
			}
			else {
				throw new BaseException('File "' .$dirpath . $name. '" not found');
			}

			return;

		}


		$this->_getFiles($dirpath, $inclSubs, $all);

		$this->insert($all);
	}

	/**
	 * @param $path
	 * @param $name
	 * @return type
	 */
	protected function getFileFromDb($path, $name)
	{
		return $this->db->query('SELECT * FROM %tp%filemanager WHERE path = ? AND filename = ?', $path, $name)->fetch();
	}

	/**
	 * @param       $path
	 * @param bool  $subScan
	 * @param array $_file
	 * @return array
	 */
	protected function _getFiles ( $path, $subScan = false, &$_file = array () )
	{

		if ( is_dir($path) !== true )
		{
			return array ();
		}

		$path = str_replace('\\', '/', $path);

		$files = scandir($path);

		//$file = array();
		natcasesort($files);



		// All dirs
		foreach ( $files as $file )
		{


			if ( $subScan && $file != '.' && $file != '..' && is_dir($path . $file) )
			{
				if (is_array($this->_excludeDirs))
				{
					if ( in_array($path . $file, $this->_excludeDirs) || in_array($path . $file.'/', $this->_excludeDirs) )
					{
						continue;
					}
				}

				$_file[ ] = array (
					'dirname' => $file,
					'path'    => $path
				);

				$this->_getFiles($path . $file . '/', $subScan, $_file);
			}
			else if ( $file != '.' && $file != '..' && is_file($path . $file) && !is_dir($path . $file) )
			{
				if (is_array($this->_excludeDirs))
				{
					if ( in_array($path . $file, $this->_excludeDirs) || in_array($file, $this->_excludeDirs) )
					{
						continue;
					}
				}


				$_file[ ] = array (
					'filename' => $file,
					'path'     => $path
				);
			}
		}

		return $_file;
	}

	/**
	 * @param array $data
	 *
	 */
	protected function insert ( &$data, $updateHash = null )
	{

		$helper = new Fileman_Helper_Base();

		if (count($data) && $updateHash)
		{


			$rs = $this->db->query('SELECT id, path, filename, atime FROM %tp%filemanager WHERE hash = ?', $updateHash)->fetch();
			$_data = $data[0];

			if ( isset( $_data[ 'dirname' ] ) )
			{
				$stat = $helper->getStat($_data[ 'path' ] . $_data[ 'dirname' ]);

				$path = str_replace($this->_indexBasePath, '', $_data[ 'path' ]);

				if ( $rs[ 'id' ] )
				{
					$this->db->query('UPDATE %tp%filemanager SET hash = ?, path = ?, filename = ?, mtime = ?, atime = ? WHERE id = ?', $stat[ 'hash' ], $path, $_data[ 'dirname' ], $stat[ 'mtime' ], $stat[ 'atime' ], $rs[ 'id' ]);
				}

			}


			if ( isset( $_data[ 'filename' ] ) && is_file($_data[ 'path' ] . $_data[ 'filename' ]) )
			{
				$stat = $helper->getStat($_data[ 'path' ] . $_data[ 'filename' ]);

				$path = str_replace($this->_indexBasePath, '', $_data[ 'path' ]);

				if ( $rs[ 'id' ] )
				{
					$this->db->query('UPDATE %tp%filemanager SET hash = ?, path = ?, filename = ?, mtime = ?, atime = ? WHERE id = ?', $stat[ 'hash' ], $path, $_data[ 'filename' ], $stat[ 'mtime' ], $stat[ 'atime' ], $rs[ 'id' ]);
				}
			}



			return;
		}



		foreach ( $data as $r )
		{
			if ( isset( $r[ 'dirname' ] ) )
			{
				$stat = $helper->getStat($r[ 'path' ] . $r[ 'dirname' ]);

				$rs = $this->db->query('SELECT id, path, filename, atime FROM %tp%filemanager WHERE hash = ?', $stat[ 'hash' ])->fetch();

				$r[ 'path' ] = str_replace($this->_indexBasePath, '', $r[ 'path' ]);

				if ( $rs[ 'id' ] )
				{
					if ( $r[ 'path' ] != $rs[ 'path' ] || $r[ 'dirname' ] != $rs[ 'filename' ] || $stat[ 'atime' ] != $rs[ 'atime' ] )
					{
						$this->db->query('UPDATE %tp%filemanager SET path = ?, filename = ?, atime = ? WHERE id = ?', $r[ 'path' ], $r[ 'dirname' ], $stat[ 'atime' ], $rs[ 'id' ]);
					}
				}
				else
				{
					$this->db->query('INSERT INTO %tp%filemanager
											(hash,path,filename,type,mime,size,version,alt,title,description, atime, ctime, mtime)
											VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)', $stat[ 'hash' ], $r[ 'path' ], $r[ 'dirname' ], 'dir', $stat[ 'mime' ], $stat[ 'size' ], 1, '', '', ''
						, $stat[ 'atime' ]
						, $stat[ 'ctime' ], $stat[ 'mtime' ]);
				}

			}
			else
			{
				if ( is_file($r[ 'path' ] . $r[ 'filename' ]) )
				{
					$stat = $helper->getStat($r[ 'path' ] . $r[ 'filename' ]);

					$r[ 'path' ] = str_replace($this->_indexBasePath, '', $r[ 'path' ]);

					$rs = $this->db->query('SELECT id, path, filename, atime FROM %tp%filemanager WHERE hash = ?', $stat[ 'hash' ])->fetch();
					if ( $rs[ 'id' ] )
					{
						if ( $r[ 'path' ] != $rs[ 'path' ] || $r[ 'filename' ] != $rs[ 'filename' ] || $stat[ 'atime' ] != $rs[ 'atime' ])
						{
							$this->db->query('UPDATE %tp%filemanager SET path = ?, filename = ?, atime = ? WHERE id = ?', $r[ 'path' ], $r[ 'filename' ], $stat[ 'atime' ], $rs[ 'id' ]);
						}
					}
					else
					{
						$this->db->query('INSERT INTO %tp%filemanager
											(hash,path,filename,type,mime,size,version,alt,title,description, atime, ctime, mtime)
											VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)', $stat[ 'hash' ], $r[ 'path' ], $r[ 'filename' ], 'file', $stat[ 'mime' ], $stat[ 'size' ], 1, '', '', '', $stat[ 'atime' ]
							, $stat[ 'ctime' ], $stat[ 'mtime' ]);
					}
				}
			}
		}


	}

}