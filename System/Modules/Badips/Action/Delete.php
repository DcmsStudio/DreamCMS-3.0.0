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
 * @package      Badips
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Delete.php
 */
class Badips_Action_Delete extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$id  = (int)HTTP::input('id');
		$ids = HTTP::input('ids');
		if ( $ids )
		{
			$items = explode(',', $ids);
		}
		else
		{
			$items = array (
				$id
			);
		}

		foreach ( $items as $idx => $id )
		{
			$r = $this->db->query('SELECT spammer_ip FROM %tp%spammers WHERE spammer_id = ?', $id)->fetch();

			$this->db->query('DELETE FROM %tp%spammers WHERE spammer_id = ?', $id);
			Library::log(sprintf('Delete the Spammer IP: `%s`', $r[ 'spammer_ip' ]));
		}

		Library::sendJson(true, trans('Die ausgewählten IP(s) wurden entfernt.'));
	}

}
