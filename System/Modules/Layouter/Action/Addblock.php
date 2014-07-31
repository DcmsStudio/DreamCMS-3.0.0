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
 * @file         Addblock.php
 */
class Layouter_Action_Addblock extends Layouter_Helper_Base
{

	public function execute ()
	{

		if ( $this->getApplication()->getMode() === Application::BACKEND_MODE )
		{
			$_name    = HTTP::post('current');
			$block    = HTTP::post('block');
			$cols     = HTTP::post('cols');
			$layoutid = (int)HTTP::post('layoutid');
			$dataid   = (int)HTTP::post('dataid');


			$rel = HTTP::post('rel');

			demoadm();

			$relBlockData = null;

			if ( !$dataid && $rel )
			{
				$relBlockData = $this->db->query('SELECT * FROM %tp%layout_data WHERE blockname = ? AND layoutid = ? LIMIT 1', $rel, $layoutid)->fetch();

				if ( !$dataid && !empty($relBlockData[ 'id' ]) )
				{
					$dataid = $relBlockData[ 'id' ];
				}
			}
			else if ( $dataid && $rel )
			{
				$relBlockData[ 'id' ] = $dataid;
			}


			if ( preg_match('/^modul_/', $_name) )
			{
				$list = explode('_', $_name);

				//array_shift($list);
				//array_pop($list);

				if ( count($list) != 4 )
				{
					Library::sendJson(false, sprintf('Invalid Modul (%s)', $_name));
				}

				$rs = $this->db->query('SELECT * FROM %tp%layout_settings WHERE layoutid = ? AND `name` = ? AND `cols` = ?', $layoutid, $block, $cols)->fetch();

				if ( $rs[ 'blockid' ] )
				{
					$settings    = explode(',', $rs[ 'settings' ]);
					$settings[ ] = implode('_', $list);

					$this->db->query('UPDATE %tp%layout_settings SET settings = ?, cols = ? WHERE blockid = ?', implode(',', $settings), $cols, $rs[ 'blockid' ]);
					$blockid = $rs[ 'blockid' ];
				}
				else
				{
					$settings[ ] = implode('_', $list);
					$this->db->query('INSERT INTO %tp%layout_settings (settings,layoutid,`name`,cols,subcolsettings) VALUES(?,?,?,?,?)', implode(',', $settings), $layoutid, $block, $cols, '');
					$blockid = $this->db->insert_id();
				}
			}
			else
			{
				$namelist = explode('_', $_name);
				if ( count($namelist) != 2 )
				{
					Library::sendJson(false, sprintf('Invalid Content Modul (%s)', $_name));
				}

				$rs = $this->db->query('SELECT * FROM %tp%layout_settings WHERE layoutid = ? AND `name` = ? AND `cols` = ?', $layoutid, $block, $cols)->fetch();

				if ( $rs[ 'blockid' ] )
				{
					$settings = explode(',', $rs[ 'settings' ]);
					$before   = $settings;

					if ( !is_array($settings) )
					{
						$settings = array ();
					}

					$settings[ ] = implode('_', $namelist);
					$this->db->query('UPDATE %tp%layout_settings SET `settings` = ?, `cols` = ? WHERE blockid = ?', implode(',', $settings), $cols, $rs[ 'blockid' ]);
					$blockid = $rs[ 'blockid' ];
				}
				else
				{
					$settings    = array ();
					$settings[ ] = $_name;
					$this->db->query('INSERT INTO %tp%layout_settings (`settings`,layoutid, `name`, `cols`, subcolsettings) VALUES(?,?,?,?,?)', $_name, $layoutid, $block, $cols, '');
					$blockid = $this->db->insert_id();
				}
			}


			/**
			 * move the Contentbox?
			 */
			if ( $dataid != $relBlockData[ 'id' ] && $dataid )
			{
				$rs = $this->db->query('SELECT id FROM %tp%layout_data WHERE id = ? AND layoutid = ?', $dataid, $layoutid)->fetch();

				if ( $rs[ 'id' ] )
				{
					$this->db->query('UPDATE %tp%layout_data SET blockid = ? WHERE id = ?', $blockid, $dataid);
					$relBlockData[ 'id' ] = $rs[ 'id' ];
				}
			}

			/**
			 * is not moved an not exists
			 */
			if ( $relBlockData[ 'id' ] > 0 )
			{
				$this->db->query('INSERT INTO %tp%layout_data
                (blockid, layoutid, blockname, relid, visible) 
                VALUES(?,?,?,?,?) ', $blockid, $layoutid, $_name, $relBlockData[ 'id' ], 1);

				$newdataid = $this->db->insert_id();

				$this->db->query('INSERT INTO %tp%layout_data_trans
                (dataid, title, value, lang, iscorelang) 
                VALUES(?,?,?,?,?)', $newdataid, '', '', CONTENT_TRANS, 1);
			}
			else
			{
				$this->db->query('INSERT INTO %tp%layout_data
                (blockid, layoutid, blockname, relid, visible) 
                VALUES(?,?,?,?,?) ', $blockid, $layoutid, $_name, 0, 1);

				$newdataid = $this->db->insert_id();

				unset($relBlockData[ 'id' ]);


				$this->db->query('INSERT INTO %tp%layout_data_trans
                (dataid, title, value, lang, iscorelang) 
                VALUES(?,?,?,?,?)', $newdataid, '', '', CONTENT_TRANS, 1);
			}


			$this->clearLayoutCache($layoutid, $blockid);


			echo Library::json(array (
			                         'success' => true,
			                         array (
				                         'dataid'  => (!empty($relBlockData[ 'id' ]) ? $relBlockData[ 'id' ] :
						                         $newdataid),
				                         'blockid' => $blockid
			                         )
			                   ));
			exit;
		}
	}

}

?>