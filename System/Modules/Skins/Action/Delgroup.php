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
 * @package      Importer
 * @version      3.0.0 Beta
 * @category     Config
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Skins_Action_Delgroup extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$id   = (int)$this->input('skinid');
		$skin = $this->model->getSkinByID($id);

		if ( !$skin[ 'id' ] )
		{
			Library::sendJson(false, trans('Kann den Skin nicht finden'));
		}

		$groupname = strtolower($this->input('group'));

		if ( !$groupname || $groupname == 'root' )
		{
			Library::sendJson(false, trans('Die Root Gruppe kann leider nicht gelöscht werden.'));
		}


		$templates = $this->model->getTemplatesByGroup($groupname, $id);

		foreach ( $templates as $r )
		{
			$path = TEMPLATES_PATH . $skin[ 'templates' ] . '/' . $r[ 'group_name' ] . '/' . $r[ 'templatename' ] . '.html';
			if ( is_file($path) )
			{
				unlink($path);
				Library::log('Deleting Template `' . $skin[ 'templates' ] . '/' . $r[ 'group_name' ] . '/' . $r[ 'templatename' ] . '` in skin ' . $skin[ 'title' ], 'warn');
			}
		}


		$this->model->deleteTemplatesByGroup($groupname, $id);


		// remove the group folder
		Library::rmdirr(TEMPLATES_PATH . $skin[ 'templates' ] . '/' . $groupname);

		rmdir(TEMPLATES_PATH . $skin[ 'templates' ] . '/' . $groupname);

		Library::log('Deleting Template-Group `' . $groupname . '` for skin ' . $skin[ 'title' ], 'warn');


		Library::sendJson(true, sprintf(trans('Die Template Gruppe `%s` wurde gelöscht'), $groupname));
	}

}
