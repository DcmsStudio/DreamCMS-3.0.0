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
 * @file         Dashaccess.php
 */
class Usergroups_Action_Dashaccess extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$registry = $this->getApplication()->getModulRegistry();


		// read all installed modules
		$this->load('Module');
		$installedModules = $this->Module->getModules();

		$id = (int)$this->input('id');

		// load permissions from Usergroup
		$this->load('Usergroup');
		$group = $this->Usergroup->getUsergroupByID($id);

		if ( HTTP::input('send') )
		{
			if ( !Permission::hasControllerActionPerm('usergroups/edit') )
			{
				Error::raise(sprintf('Sie haben keine Berechtigung zum durchführen dieser Aktion. (%s/%s)', CONTROLLER, ACTION));
			}

			demoadm();

			$data           = array ();
			$data[ 'perm' ] = HTTP::input('access');

			Cache::delete('groupactionperms-' . $id);
			Cache::delete('menu_user_' . User::getUserId() . '_' . GUI_LANGUAGE);

			$this->load('Action');
			$this->Action->saveUsergroupControllerPerms($data, $id, true);

			Library::log(sprintf('Has set dashboard rights for the Usergroup `%s`', $group[ 'title' ]), 'warn');
			Library::sendJson(true, sprintf(trans("Dashboard Rechte für die Benutzergruppe `%s` wurden aktualisiert."), $group[ 'title' ]));
		}


		$groupPerms = $this->Usergroup->getUsergroupPermissionsByID($id, true);

		$groupaccess = array ();
		foreach ( $groupPerms as $r )
		{
			$groupaccess[ $r[ 'controller' ] ][ $r[ 'action' ] ] = ($r[ 'hasperm' ] ? true : false);
		}

		$groupPerms = null;

		$data                = array ();
		$data[ 'permitems' ] = array ();


		$backendPerms = Permission::initBackendPermissions();


		foreach ( $backendPerms[ 'usergroup' ] as $modulKey => $_data )
		{
			if ( substr($modulKey, 0, 7) != 'plugin_' && !isset($registry[ strtolower($modulKey) ]) )
			{
				continue;
			}


			if ( is_array($_data) && isset($_data[ 'access-items' ]) )
			{
				$_tmp            = $_data;
				$_tmp[ 'modul' ] = strtolower($modulKey);

				foreach ( $_data[ 'access-items' ] as $key => $_d )
				{
					// set default checked
					$defaultchk = (isset($groupaccess[ $_tmp[ 'modul' ] ][ $key ]) && $groupaccess[ $_tmp[ 'modul' ] ][ $key ] ?
						true : null);


					$_tmp[ 'access' ][ ] = array (
						'modul'    => $_tmp[ 'modul' ],
						'action'   => $key,
						'label'    => $_d[ 0 ],
						'default'  => $_d[ 1 ],
						'hasperm'  => $defaultchk,
						'isPlugin' => (substr(strtolower($modulKey), 0, 7) !== 'plugin_' ? false : true)
					);
				}
				unset($perms);
				unset($_tmp[ 'access-items' ]);

				$data[ 'permitems' ][ $_tmp[ 'title' ] ] = $_tmp;
			}
		}

		ksort($data[ 'permitems' ]);

		$data[ 'permissions' ] = $groupaccess;
		$data[ 'group' ]       = $group;


		Library::addNavi(trans('Benutzergruppen Übersicht'));
		Library::addNavi(($group[ 'grouptitle' ] ?
			sprintf(trans('Dashboard Rechte für die Benutzergruppe `%s` bearbeiten'), $group[ 'title' ]) :
			sprintf(trans('Dashboard Rechte für die Benutzergruppe `%s` erstellen'), $group[ 'title' ])));


		$this->Template->addScript(BACKEND_JS_URL . 'dcms.perms.js');

		$this->Template->process('group/dashboard_access', $data, true);
	}

}

?>