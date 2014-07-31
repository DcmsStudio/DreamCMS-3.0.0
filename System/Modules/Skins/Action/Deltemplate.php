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
 * @package      Skins
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Deltemplate.php
 */
class Skins_Action_Deltemplate extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}


		$id       = (int)$this->input('id');
		$template = $this->model->getTemplateByID($id);

		if ( !$template[ 'id' ] )
		{
			Library::sendJson(false, trans('Das Template existiert nicht'));
		}

		$skin = $this->model->getSkinByID($template[ 'set_id' ]);

		$this->model->deleteTemplate($id);


		$path = TEMPLATES_PATH . $skin[ 'templates' ] . '/' . $template[ 'group_name' ] . '/' . $template[ 'templatename' ] . '.html';
		if ( is_file($path) )
		{
			unlink($path);
		}

		Library::log('Deleting Template `' . $template[ 'group_name' ] . '/' . $template[ 'templatename' ] . '` for skin ' . $skin[ 'title' ], 'warn');
		Library::sendJson(true, sprintf(trans('Das Template `%s` wurde gelöscht'), $template[ 'templatename' ]));
	}

}

?>