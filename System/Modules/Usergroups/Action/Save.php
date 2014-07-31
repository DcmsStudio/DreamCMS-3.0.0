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
 * @package      Usergroups
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Save.php
 */
class Usergroups_Action_Save extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		if ( !Permission::hasControllerActionPerm('usergroups/edit') )
		{
			Error::raise(sprintf('Sie haben keine Berechtigung zum durchführen dieser Aktion. (%s/%s)', CONTROLLER, ACTION));
		}

		demoadm();


		$model    = Model::getModelInstance('usergroups');
		$submitok = false;

		$id = (int)HTTP::input('id');

		if ( $id )
		{
			$group = $this->db->query("SELECT * FROM %tp%users_groups WHERE groupid = ?", $id)->fetch();
		}

		$submitok = $model->saveGroup($id, $group);

		if ( $submitok === false )
		{
			Error::raise('Submit Error!');
		}

		if ( !IS_AJAX )
		{
			header("Location: admin.php?adm=usergroups");
		}
		else
		{
			echo Library::json(array (
			                         'success' => true,
			                         'msg'     => trans("Benutzergruppe wurde erfolgreich gespeichert"),
			                         'newid'   => $submitok
			                   ));
		}
		exit();
	}

}

?>