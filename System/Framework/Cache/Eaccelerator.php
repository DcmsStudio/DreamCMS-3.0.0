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
 * @file         Eaccelerator.php
 */

class Cache_Eaccelerator extends Cache_Abstract
{

    /**
     * @param $name
     * @param string $type
     * @return string
     */
    private static function getKey ( $name, $type = 'data' )
	{

		return md5($name . $type);
	}


    /**
     * @param $name
     * @param $data
     * @param string $type
     * @param bool $as_file
     */
    public static function set ( $name, $data, $type = 'data', $as_file = false )
	{

		$key = self::getKey($name, $type);

		self::$_dataCache[ $type ][ $name ] = $data;

		if ( is_array($data) || is_object($data) || is_resource($data) )
		{
			if ( !$as_file )
			{
				$data = '[s]'.serialize($data);
			}
			else
			{

				trigger_error('Cannot save non-scalar data in `' . $name . '` to cache as file (filecache::set()).', E_USER_ERROR);
			}
		}

		eaccelerator_put($key, $data, 0);


	}

    /**
     * @param $name
     * @param string $type
     * @param null $cacheTime
     * @return null
     */
    public static function get ( $name, $type = 'data', $cacheTime = null )
	{
		if ( isset( self::$_dataCache[ $type ][ $name ] ) )
		{
			return self::$_dataCache[ $type ][ $name ];
		}

		$key   = self::getKey($name, $type);
		$value = eaccelerator_get($key);

		if ( is_string($value)) {
			if (substr($value, 0, 3) === '[s]' ) {
				self::$_dataCache[ $type ][ $name ] = unserialize($value);
			}
			else {
				self::$_dataCache[ $type ][ $name ] = $value;
			}

			return self::$_dataCache[ $type ][ $name ];
		}

		return null;
	}

    /**
     * @param $name
     * @param string $type
     * @return mixed
     */
    public static function delete ( $name, $type = 'data' )
	{
		$key   = self::getKey($name, $type);
		$result = eaccelerator_rm($key);
		if ( $result ) {
			unset(self::$_dataCache[ $type ][ $name ]);
		}
		return $result;

	}


	public static function flush ()
	{
		eaccelerator_clear();
		self::$_dataCache = array ();
	}


}