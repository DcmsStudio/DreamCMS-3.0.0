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
 * @file         Delete.php
 */
class Skins_Action_Delete extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$skinid = $this->input('id');
		$skin = $this->model->getSkinByID($skinid);

		if ($skin['default_set'])
		{
			Library::sendJson(false, trans('Der Standart Skin kann nicht gelöscht werden') );
		}

		if ($skin['iscore'])
		{
			Library::sendJson(false, trans('Der Core Skin darf nicht gelöscht werden') );
		}


		$this->model->deleteSkin($skinid);

		$f = new File('', true);
		$f->deleteRescursiveDir(TEMPLATES_PATH . $skin['templates']);
		$f->deleteRescursiveDir(ROOT_PATH . SKIN_IMG_URL_PATH . $skin['img_dir']);


		Library::log(sprintf('Has delete the Skin %s with all skin files!', $skin['title']), 'warn');
		Library::sendJson(true, sprintf(trans('Der Skin %s und all seine dazugehörigen Dateien wurde gelöscht'), $skin['title']) );
	}

}

?>