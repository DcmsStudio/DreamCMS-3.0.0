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
 * @package      Component
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Save.php
 */
class Component_Action_Save extends Component_Helper_BaseHelper
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}


		$data   = HTTP::input();
		$errors = $this->validate($data);

		if ( count($errors) > 0 )
		{

			echo Library::json(array (
			                         'success'     => false,
			                         'form_errors' => $errors
			                   ));
			exit;
		}
		else
		{
			demoadm();

			$id = $this->model->saveComponente($data);
			Cache::refresh();

			Library::sendJson(true, trans("Componente wurde erfolgreich gespeichert"));
			exit;
		}
	}

}

?>