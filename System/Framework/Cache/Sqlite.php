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
 * @package      DreamCMS
 * @version      3.0.0 Beta
 * @category     Framework
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Sqlite.php
 */
class Cache_Sqlite extends Cache_Abstract
{

	/**
	 * @var Database_Adapter_Sqlite
	 */
	protected static $db = null;

	protected static $cache = null;

	protected static $cachePath = '';

	protected static $tableName = 'SqliteCache';

	protected static $cacheTime = 3600;

    /**
     * @param string $name
     */
    public static function setCachePath ( $name = '' )
	{
		self::$cachePath = $name;
	}

    /**
     * @return bool|string
     */
    public static function getCachePath ()
	{

		return ( !self::$cachePath ? Cache::getCachePath() : self::$cachePath );
	}


    /**
     * @param null $name
     * @param string $type
     * @return string
     */
    private static function getKey($name = null, $type = 'data') {

		if ( !$name ) {
			return $type.'##';
		}

		return $type.'##'.$name;
	}

    /**
     * @param string $table_name
     * @return bool
     */
    private static function create_table ( $table_name = 'SqliteCache' )
	{

		$table_name = self::$tableName;


		$sql    = "SELECT name FROM sqlite_master WHERE type='table' AND name='" . $table_name . "'";
		$result = self::$db->query($sql)->fetch();
		#die('C');

		if ( !$result[ 'name' ] )
		{
			#die('C');
			// self::$db->query("drop table {$table_name}");
			$valid = self::$db->query("
                    CREATE TABLE {$table_name}
                    (
                    cache_key TEXT PRIMARY KEY,
                    cache_name TEXT,
                    cache_value TEXT,
                    cache_expires INTEGER
                    )");


			if ( !$valid )
			{
				die( 'Invalid Sqlite DB' );
			}

			return true;

			//return self::$db->query("CREATE INDEX cache_cache_expires ON {$table_name} (cache_expires)");
		}
		else
		{
			return true;
		}

		return false;
	}

    /**
     * @param string $table_name
     * @throws BaseException
     */
    private static function loadDBCache ( $table_name = 'SqliteCache' )
	{

		if ( self::$db !== null )
		{
			return;
		}

		$sqliteRegistry = array ();
		$table_name     = self::$tableName;
		self::$db       = Database::getInstance('SQLITECACHE');


		if ( is_file(DATA_PATH . 'sqliteDB/tableregistry.php') )
		{
			$sqliteRegistry = include( DATA_PATH . 'sqliteDB/tableregistry.php' );
		}

		if ( !isset( $sqliteRegistry[ $table_name ] ) )
		{
			$ok = self::create_table();

			if ( !$ok )
			{
				throw new BaseException( 'Could not create the SqliteCache Table!' );
			}

			$sqliteRegistry[ $table_name ] = true;

			$data = "<?php\nif(!defined('IN')) { die('Access Denied'); }\nreturn " . self::var_export_min($sqliteRegistry, true) . ";\n?>";
			file_put_contents(DATA_PATH . 'sqliteDB/tableregistry.php', $data);
		}

		self::$cache = array ();

		return;


		$query = "SELECT * FROM " . $table_name;
		$cache = self::$db->query($query)->fetchAll();

		self::$cache = array ();

		foreach ( $cache as $r )
		{

			$k = explode('##', $r[ 'cache_key' ]);


			if ( TIMESTAMP > $r[ 'cache_expires' ]  )
			{
				self::delete($r[ 'cache_name' ], $k[0]);
				unset(self::$cache[ $k[0] ][ $r[ 'cache_name' ] ]);
				continue;
			}

			$v = $r[ 'cache_value' ];

			// check for serialization
			if ( strstr($v, '[dcms_serialized]') )
			{
				$v = str_replace('[dcms_serialized]', '', $v);
				$v = unserialize($v);
				$v = is_array($v) ? $v : null;
			}

			self::$cache[ $k[0] ][ $r[ 'cache_name' ] ] = $v;
		}


		self::$db->close();
	}

    /**
     * @param mixed $var
     * @param bool $return
     * @return mixed|string
     */
    static function var_export_min ( $var, $return = false )
	{

		if ( is_array($var) )
		{
			$toImplode = array ();
			foreach ( $var as $key => $value )
			{
				$toImplode[ ] = var_export($key, true) . '=>' . self::var_export_min($value, true);
			}
			$code = 'array(' . implode(',', $toImplode) . ')';
			if ( $return )
			{
				return $code;
			}
			else
			{
				echo $code;
			}
		}
		else
		{
			return var_export($var, $return);
		}
	}

    /**
     * @param $name
     * @param $inputdata
     * @param string $type
     * @param bool $as_file
     */
    public static function set ( $name, $inputdata, $type = 'data', $as_file = false )
	{
		Debug::store('Sqlite Cache set', $name .' '. $type);


		if ( is_array($inputdata) || is_object($inputdata) )
		{
			if ( !$as_file )
			{
				$data = '[dcms_serialized]' . serialize($inputdata);
			}
			else
			{
				Error::raise('Cannot save non-scalar data in `' . $name . '` to cache as file (SqliteCache::set()).', E_USER_ERROR);
			}
		}
		else
		{
			$data = $inputdata;
		}

		$table_name = self::$tableName;

	#	self::loadDBCache();


		$query = "REPLACE INTO {$table_name} (cache_name, cache_key, cache_value, cache_expires ) VALUES(?, ?, ?, ?)";
		self::$db->query($query, $name, $type.'##'.$name, $data, time() + self::$cacheTime);


		self::$cache[ $type ][ $name ] = $inputdata;


	}

    /**
     * @param $name
     * @param string $type
     * @return null
     */
    public static function get ( $name, $type = 'data' )
	{
		self::loadDBCache();

		$table_name = self::$tableName;
		$query = "SELECT * FROM " . $table_name ." WHERE cache_name = ? AND cache_key = ?";
		$r = self::$db->query($query, $name, self::getKey($name, $type))->fetch();

		Debug::store('Sqlite Cache get', $name .' '. $type);

		if (isset($r['cache_value'])) {

			$v = $r[ 'cache_value' ];

			// check for serialization
			if ( strstr($v, '[dcms_serialized]') )
			{
				$v = str_replace('[dcms_serialized]', '', $v);
				$v = unserialize($v);
				$v = is_array($v) ? $v : null;
			}

			if (  !is_null( $v ) )
			{
				self::$cache[ $type ][ $name ] = $v;
			}
		}
		self::$db->close();


		if ( isset(self::$cache[ $type ][ $name ]) && !is_null( self::$cache[ $type ][ $name ] ) )
		{
			return self::$cache[ $type ][ $name ];
		}

		return null;
	}

    /**
     * @param $name
     * @param string $type
     */
    public static function delete ( $name, $type = 'data' )
	{

		self::loadDBCache();

		Debug::store('Sqlite Cache delete', $name .' '. $type);


		$table_name = self::$tableName;

		$query = "DELETE FROM {$table_name} WHERE cache_name = ? AND cache_key = ?";
		self::$db->query($query, $name, self::getKey($name, $type));

		unset( self::$cache[ $type ][ $name ] );
	}

    /**
     * @param string $type
     * @param bool $clearsubdirs
     */
    public static function clear ( $type = 'data', $clearsubdirs = false )
	{

		self::loadDBCache();

		$table_name = self::$tableName;

		$query = "DELETE FROM {$table_name} WHERE cache_key = ?";
		self::$db->query($query, self::getKey($name, $type));

		unset( self::$cache[ $type ] );

	}

    /**
     * @param array $excludes
     */
    public static function flush ( $excludes = array () )
	{

		self::loadDBCache();
		foreach ( self::$cache as $type => $r )
		{
			foreach ( $r as $name => $data )
			{
				if ( !in_array($name, $excludes) )
				{
					$query = "DELETE FROM {$table_name} WHERE cache_name = ? AND cache_key = ?";
					self::$db->query($query, $name, self::getKey($name, $type));

					unset( self::$cache[ $type ][ $name ] );
				}
			}
		}

	}

	public static function runShutdown ()
	{

		self::loadDBCache();
		$table_name = self::$tableName;

		foreach ( self::$cache as $type => $r )
		{
			foreach ( $r as $name => $inputdata )
			{

				if ( is_array($inputdata) || is_object($inputdata) )
				{
					$data = '[dcms_serialized]' . serialize($inputdata);
				}
				else
				{
					$data = $inputdata;
				}


				$query = "REPLACE INTO {$table_name} (cache_name, cache_key, cache_value, cache_expires ) VALUES(?, ?, ?, ?)";
				self::$db->query($query, $name, self::getKey($name, $type), $data, time() + self::$cacheTime);
			}
		}
	}

}
