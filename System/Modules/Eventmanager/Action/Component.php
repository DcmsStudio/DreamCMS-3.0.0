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
 * @package      Eventmanager
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Component.php
 */
class Eventmanager_Action_Component extends Eventmanager_Helper_Base
{

	public function execute ()
	{

		if ( $this->getApplication()->getMode() !== Application::BACKEND_MODE )
		{
			return;
		}
		$event     = $this->input('event');
		$component = $this->input('component');

		if ( empty($event) || empty($component) )
		{
			Error::raise(trans('Cannot hook component - missing parameters.'));
		}

		demoadm();

		$this->model->addComponentToHook($event, $component);


		Library::log('Add the Component "' . $component . '" to Event-Hook "' . $event . '".');
		Library::sendJson(true, sprintf(trans('Die Komponente `%s` wurde dem Hook `%s` hinzugefügt'), $component, $event));
	}

}
