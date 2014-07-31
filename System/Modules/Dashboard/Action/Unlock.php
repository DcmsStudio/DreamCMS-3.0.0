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
 * @package      Dashboard
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Unlock.php
 */
class Dashboard_Action_Unlock extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$modul       = $this->_post('modul');
		$modulaction = $this->_post('modulaction');
		$contentid   = (int)$this->_post('contentid');


		$this->load('ContentLock');

		$this->model->unlock($contentid, $modul, $modulaction);


		/*
		  $this->ContentLock->unlock( $contentid, $modul, $modulaction );
		  $model = Model::getModelInstance( $modul );
		  $model->unlock( $contentid, $modulaction );
		 */

		Library::log('Has unlock the Docment ID:' . $contentid . ' Modul: ' . $modul);
		Library::sendJson(true, trans('Das Dokument wurde aus der Bearbeitung genommen'));
	}

}
