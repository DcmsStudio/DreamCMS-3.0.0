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
 * @package      Notes
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Add.php
 */
class Notes_Action_Add extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		// calculate some fake x and y coordinates
		$x = round(rand(100, 200), 0);
		$y = round(rand(100, 200), 0);


		$str = $this->db->compile_db_insert_string(array (
		                                                 'userid'  => User::getUserId(),
		                                                 'x'       => $x,
		                                                 'y'       => $y,
		                                                 'text'    => $this->_post('text'),
		                                                 'created' => TIMESTAMP
		                                           ));
		$sql = "INSERT INTO %tp%admin_notes ({$str['FIELD_NAMES']}) VALUES({$str['FIELD_VALUES']})";
		$this->db->query($sql);
		$id = $this->db->insert_id();

		$sql = "SELECT COUNT(id) AS found FROM %tp%admin_notes WHERE userid=" . User::getUserId();
		$r   = $this->db->query($sql)->fetch();

		Cache::delete('notes_' . (int)Session::get('userid'));

		echo Library::json(array (
		                         'success'    => true,
		                         'totalnotes' => $r[ 'found' ],
		                         'id'         => $id,
		                         'date'       => date('d.m.Y, H:i', TIMESTAMP),
		                         'label'      => Strings::TrimHtml($this->_post('text'), 30, null)
		                   ));
		exit;
	}

}
