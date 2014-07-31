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
 * @package      Widgets
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Save.php
 */
class Widgets_Action_Save extends Widgets_Helper_Base
{

	public function execute ()
	{

		$arr = $this->get_user_widget_by_id($this->input('id'));
		if ( !isset($arr[ 'widget' ]) )
		{
			throw new BaseException('Widget was not found! ID:' . $this->input('id', 'int'));
		}

		$data = $this->input();

		demoadm();

        $ucfirst = ucfirst($arr[ 'widget' ]);


		if ( method_exists('Widget_' . $ucfirst . '_Config', 'saveWidgetConfig') )
		{
            cache::delete('widget_data_' . $ucfirst . '_' . $arr[ 'id' ]);
            cache::delete('widget_data_' . $arr[ 'id' ] . '_' . User::getUserId());

			call_user_func_array(array (
			                           'Widget_' . $ucfirst . '_Config',
			                           'saveWidgetConfig'
			                     ), $data);

		}
		else
		{
			if ( empty($data[ 'config' ]) )
			{
				die('No widget configuration data found in POST.');
			}
			else
			{
				$config = serialize($data[ 'config' ]);
				$this->db->query('UPDATE %tp%users_widget SET config=?, label = ? WHERE id=? AND userid=?', $config, (isset($data[ 'wgtlabel' ]) ? $data[ 'wgtlabel' ] : ''), $arr[ 'id' ], User::getUserId());

				cache::delete('widget_data_' . $arr[ 'widget' ] . '_' . $arr[ 'id' ]);
				cache::delete('widget_data_' . $arr[ 'id' ] . '_' . User::getUserId());
			}
		}

		echo Library::json(array (
		                         'success' => true,
		                         'widget'  => HTTP::post('name'),
		                         'id'      => $arr[ 'id' ]
		                   ));
		exit;
	}

}
