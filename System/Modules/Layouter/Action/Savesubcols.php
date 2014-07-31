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
 * @file         Savesubcols.php
 */
class Layouter_Action_Savesubcols extends Layouter_Helper_Base
{

	public function execute ()
	{

		if ( $this->getApplication()->getMode() === Application::BACKEND_MODE )
		{
			$_name    = HTTP::input('current');
			$name     = HTTP::input('layoutblock');
			$cols     = HTTP::input('cols');
			$layoutid = (int)HTTP::input('layoutid');

			$htmldata  = HTTP::input('htmldata'); // the layout containers
			$neworders = HTTP::input('neworder');


			$htmldata = str_replace('[]', '', $htmldata);


			demoadm();

			$rs = $this->db->query('SELECT * FROM %tp%layout_settings WHERE layoutid = ? AND `name` = ?', $layoutid, $name)->fetch();

			if ( $rs[ 'blockid' ] )
			{
				$this->db->query('UPDATE %tp%layout_settings SET cols = ?, settings = ?, subcolhtml = ? WHERE blockid = ?', $cols, $neworders, trim($htmldata), $rs[ 'blockid' ]);
				$blockid = $rs[ 'blockid' ];
			}
			else
			{
				$this->db->query('INSERT INTO %tp%layout_settings
                    (layoutid,`name`,cols,settings,subcolhtml) VALUES(?,?,?,?,?)', $layoutid, $name, $cols, $neworders, trim($htmldata));
				$blockid = $this->db->insert_id();
			}


			/**
			 * move the Contentbox?
			 */
			$items = explode(',', $neworders);
			foreach ( $items as $itm )
			{

				if ( $blockid && $itm )
				{
					$rs = $this->db->query('SELECT id, blockid FROM %tp%layout_data WHERE blockname = ? AND layoutid = ? LIMIT 1', $itm, $layoutid)->fetch();
					if ( $rs[ 'blockid' ] != $blockid && $rs[ 'id' ] )
					{
						// update the blockid in data table
						$this->db->query('UPDATE %tp%layout_data SET blockid = ? WHERE id = ?', $blockid, $rs[ 'id' ]);

						// get the current settings and remove the item from it
						$r           = $this->db->query('SELECT settings FROM %tp%layout_settings WHERE blockid = ?', $rs[ 'blockid' ])->fetch();
						$settings    = explode(',', $r[ 'settings' ]);
						$newsettings = array ();

						foreach ( $settings as $name )
						{
							if ( $name != $itm && $name )
							{
								$newsettings[ ] = $name;
							}
						}

						// update the old settings
						$this->db->query('UPDATE %tp%layout_settings SET `settings` = ? WHERE blockid = ?', implode(',', $newsettings), $rs[ 'blockid' ]);

						$this->clearLayoutCache($layoutid, $rs[ 'blockid' ]);
					}
				}
			}

			$this->clearLayoutCache($layoutid);


			echo Library::json(array (
			                         'success' => true,
			                         array (
				                         'blockid' => $blockid
			                         )
			                   ));
			exit;
		}
	}

}

?>