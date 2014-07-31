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
 * @package      Logs
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Delete.php
 */
class Logs_Action_Delete extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}


		demoadm();

		$all   = (int)HTTP::input('all');
		$id    = (int)HTTP::input('id');
		$ids   = HTTP::input('ids');
		$multi = false;


		if ( $ids )
		{
			$id    = $ids;
			$multi = true;
		}

		if ( !$id && !$all )
		{
			Error::raise("Invalid ID");
		}

		if ( !$all )
		{
			if ( $multi )
			{
				$sql = 'DELETE FROM %tp%logs WHERE id IN(0,' . $id . ')';
			}
			else
			{
				$sql = 'DELETE FROM %tp%logs WHERE id = ' . $id;
			}

			$this->db->query($sql);
		}
		else
		{
			$sql = 'DELETE FROM %tp%logs WHERE pageid=? OR pageid = 0 AND userid = 0';
			$this->db->query($sql, PAGEID);
		}


		Library::sendJson(true, 'Log-Einträge wurde gelöscht!');
	}

}
