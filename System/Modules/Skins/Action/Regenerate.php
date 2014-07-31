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
 * @file         Regenerate.php
 */
class Skins_Action_Regenerate extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$id   = (int)$this->input('id');
		$skin = $this->model->getSkinByID($id);

		if ( !$skin[ 'id' ] )
		{
			Library::sendJson(false, trans('Kann den Skin nicht finden'));
		}


		Library::rmdirr(TEMPLATES_PATH . $skin[ 'templates' ]);


		$templates = $this->model->getTemplatesBySkinId($id);
		foreach ( $templates as $r )
		{
			if ( $r[ 'group_name' ] )
			{


				Library::makeDirectory(TEMPLATES_PATH . $skin[ 'templates' ] . '/' . $r[ 'group_name' ]);
				$path = TEMPLATES_PATH . $skin[ 'templates' ] . '/' . $r[ 'group_name' ] . '/' . $r[ 'templatename' ] . '.html';
			}
			else
			{
				Library::makeDirectory(TEMPLATES_PATH . $skin[ 'templates' ]);
				$path = TEMPLATES_PATH . $skin[ 'templates' ] . '/' . $r[ 'templatename' ] . '.html';
			}

			if ( is_file($path) )
			{
				unlink($path);
			}

			file_put_contents($path, $r[ 'content' ]);
			chmod($path, 0666);
		}


		Library::log('Has regenerate the Templates for the skin "' . $skin[ 'title' ] . '" ');

		Library::sendJson(true, sprintf(trans('Templates des Skins `%s` wurden erneuert'), $skin[ 'title' ]));
	}

}

?>