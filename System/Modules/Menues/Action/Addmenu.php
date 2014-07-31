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
 * @package      Menues
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Addmenu.php
 */
class Menues_Action_Addmenu extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}
		demoadm();
		$model = Model::getModelInstance();
		$data  = $this->_post();

		if ( empty($data[ 'menutitle' ]) )
		{
			Library::sendJson(false, trans('Um ein Menü erstellen zu können, wird der Titel benötigt'));
		}

		$id    = (int)$data[ 'menuid' ];
		$newid = 0;

		if ( $id )
		{
			$model->createMenu($id, $data);
		}
		else
		{
			$newid = $model->createMenu(0, $data);
		}


		if ( $newid )
		{
			Library::log('Has created the menu id: ' . $newid . ' "' . $data[ 'menutitle' ] . '"');
			echo Library::json(array (
			                         'title'       => $data[ 'menutitle' ],
			                         'templatekey' => $data[ 'templatekey' ],
			                         'newid'       => $newid,
			                         'success'     => true,
			                         'msg'         => sprintf(trans('Das Menü `%s` wurde erstellt'), $data[ 'menutitle' ])
			                   ));
			exit;
		}
		else
		{
			Library::log('Has updated the menu id: ' . $id . ' "' . $data[ 'menutitle' ] . '"');
			Library::sendJson(true, sprintf(trans('Das Menü `%s` wurde erstellt'), $data[ 'menutitle' ]));
		}
	}

}

?>