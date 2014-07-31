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
 * @package      Linkcheck
 * @version      3.0.0 Beta
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Mysql.php
 */
class Linkcheck_Model_Mysql extends Model
{

	/**
	 * @param int    $valid
	 * @param int    $invalid
	 * @param string $currenturl
	 * @param bool   $isValid
	 * @param int    $sleep
	 * @param int    $stop
	 */
	public function writeData ( $valid = 0, $invalid = 0, $currenturl = '', $isValid = false, $sleep = 0, $stop = 0 )
	{

		$this->db->query('REPLACE INTO %tp%linkcheck (valid, invalid, currenturl, isvalid, sleep, stop, sessionid)
                          VALUES(?, ?, ?, ?, ?, ?, ?)', $valid, $invalid, $currenturl, $isValid, $sleep, $stop, session_id());
	}

	public function cleanData ()
	{

		$this->db->query('DELETE FROM %tp%linkcheck WHERE sessionid=?', session_id());
	}

	/**
	 * @return type
	 */
	public function getData ()
	{

		return $this->db->query('SELECT * FROM %tp%linkcheck WHERE sessionid=?', session_id())->fetch();
	}

}

?>