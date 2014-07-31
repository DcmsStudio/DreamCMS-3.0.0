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
 * @file         Getwidgetconfig.php
 */
class Layouter_Action_Getwidgetconfig extends Controller_Abstract
{

	public function execute ()
	{

		$id = (int)$this->_get('itemid');

		if ( !$id )
		{
			Library::sendJson(false);
		}

		$item = $this->model->getItemById($id);

		if ( $item[ 'rel_itemid' ] > 0 )
		{
			$item = $this->model->getItemById($item[ 'rel_itemid' ]);
		}


		$callClass = false;
		if ( $item[ 'type' ] == 'modul' || $item[ 'type' ] == 'plugin' && $item[ 'call' ] && $item[ 'modul' ] )
		{
			$callClass = true;
		}

		$wg = new Layoutwidgets();

		// call modules and plugins
		if ( $callClass )
		{
			$wg      = new Layoutwidgets();
			$_caller = $wg->getContentWidget($item[ 'modul' ], $item[ 'call' ]);

			if ( $_caller )
			{
				$cls = new $_caller[ 'class' ];

				$cls->setOptions($item);

				$cls->widgetID = $id;


				$data              = $cls->execute();
				$data[ 'success' ] = true;
			}
			else
			{
				Library::sendJson(false);
			}
		}
		else
		{

		}


		echo Library::json($data);
		exit;
	}

}
