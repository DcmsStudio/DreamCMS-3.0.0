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
 * @package      Layouter
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Removelayout.php
 */
class Layouter_Action_Removelayout extends Layouter_Helper_Base
{

	public function execute ()
	{

		if ( $this->getApplication()->getMode() === Application::BACKEND_MODE )
		{
			$id = (int)HTTP::input('id');

			if ( !$id )
			{
				Library::sendJson(false, trans('Layout existiert nicht'));
			}

			demoadm();


			$data = $this->db->query("SELECT * FROM %tp%layouts WHERE id = ?", $id)->fetch();

			$_dat = $this->db->query("SELECT * FROM %tp%layout_data WHERE layoutid = ?", $id)->fetchAll();

			foreach ( $_dat as $r )
			{
				if ( !$r[ 'relid' ] )
				{
					$found = $this->findRelItem($r[ 'id' ]);
					if ( !count($found) )
					{
						$this->db->query("DELETE FROM %tp%layout_data WHERE id = ?", $r[ 'id' ]);
						$this->db->query("DELETE FROM %tp%layout_data_trans WHERE dataid = ?", $r[ 'id' ]);
					}
				}
				else
				{
					$this->db->query("DELETE FROM %tp%layout_data WHERE id = ?", $r[ 'id' ]);
					$this->db->query("DELETE FROM %tp%layout_data_trans WHERE dataid = ?", $r[ 'id' ]);
				}
			}

			$this->db->query("DELETE FROM %tp%layout_settings WHERE layoutid = ?", $id);
			$this->db->query('DELETE FROM %tp%layouts WHERE id = ?', $id);


			$this->clearLayoutCache($id);

			Library::log(sprintf('User has delete Layout `%s`', $data[ 'title' ]));
			Library::sendJson(true, trans('Layout wurde gelöscht.'));

			exit;
		}
	}

	/**
	 * @param $id
	 * @return array
	 */
	private function findRelItem ( $id )
	{

		return $this->db->query("SELECT * FROM %tp%layout_data WHERE relid = ?", $id)->fetchAll();
	}

}

?>