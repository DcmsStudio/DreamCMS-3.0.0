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
 * @package      Database
 * @version      3.0.0 Beta
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Mysql.php
 */
class Database_Model_Mysql extends Model
{

	/**
	 *
	 * @param array $tables
	 */
	public function optimizeTables ( $tables )
	{


        $schema = $this->db->getPreparedDatabaseName();
        if ($tables === true)
        {
            $tables = $this->db->listTables();
        }

		while ( list($key, $val) = each($tables) )
		{
			$this->db->query("OPTIMIZE TABLE " . $schema . '.' . $val);
		}
	}

	/**
	 *
	 * @param array $tables
	 */
	public function repairTables ( $tables )
	{

		$schema = $this->db->getPreparedDatabaseName();
		while ( list($key, $val) = each($tables) )
		{
			$this->db->query("REPAIR TABLE " . $schema . '.' . $val);
		}
	}

}

?>