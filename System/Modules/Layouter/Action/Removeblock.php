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
 * @file         Removeblock.php
 */
class Layouter_Action_Removeblock extends Layouter_Helper_Base
{

	public function execute ()
	{

		if ( $this->getApplication()->getMode() === Application::BACKEND_MODE )
		{
			$_name        = HTTP::input('contentbox');
			$currentblock = HTTP::input('layoutblock');
			$cols         = HTTP::input('cols');
			$layoutid     = (int)HTTP::input('layoutid');


			$rs = $this->db->query('SELECT * FROM %tp%layout_settings WHERE layoutid = ? AND `name` = ? AND cols = ?', $layoutid, $currentblock, $cols)->fetch();

			if ( !$rs[ 'blockid' ] )
			{
				Library::sendJson(false, 'Invalid Modul ' . print_r(HTTP::input(), true));
			}
			else
			{
				$rs[ 'subcolhtml' ] = str_replace('[' . $_name . ']', '', $rs[ 'subcolhtml' ]);


				$settings = explode(',', $rs[ 'settings' ]);
				foreach ( $settings as $i => $name )
				{
					if ( $_name == $name )
					{
						unset($settings[ $i ]);
						break;
					}
				}


				demoadm();

				// $this->db->query('DELETE FROM %tp%layout_data WHERE blockid = ? AND layoutid = ? AND `blockname` = ?', $rs['blockid'], $layoutid, $_name);
				$this->db->query('UPDATE %tp%layout_settings SET settings = ?,subcolhtml = ? WHERE blockid = ?', implode(',', $settings), $rs[ 'subcolhtml' ], $rs[ 'blockid' ]);
			}

			$this->clearLayoutCache($layoutid, $rs[ 'blockid' ]);


			Library::sendJson(true);
		}
	}

}

?>