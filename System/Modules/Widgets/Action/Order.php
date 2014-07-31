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
 * @file         Order.php
 */
class Widgets_Action_Order extends Widgets_Helper_Base
{

	public function execute ()
	{

		if ( BACKEND_SKIN_ISWINDOWED )
		{
			$id   = (int)$this->input('id');
			$left = (int)$this->input('left');
			$top  = (int)$this->input('top');
			$this->model->saveWidgetPos($id, $left, $top);
		}
		else
		{
			for ( $i = 1; $i < 5; ++$i) {
				if ( $this->input('col' . $i) )
				{
					$widgets = explode(',', $this->input('col' . $i));
					foreach ( $widgets as $order => $id )
					{
						$this->model->saveWidgetOrder($id, $i, $order);
					}
				}
			}
		}


		echo Library::json(array (
		                         "success" => true
		                   ));
		exit;
	}

}
