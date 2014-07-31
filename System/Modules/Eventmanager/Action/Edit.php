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
 * @file         Edit.php
 */
class Eventmanager_Action_Edit extends Eventmanager_Helper_Base
{

	public function execute ()
	{

		if ( $this->getApplication()->getMode() !== Application::BACKEND_MODE )
		{
			return;
		}
		$event = $this->input('event');

		if ( empty($event) )
		{
			Error::raise(sprintf(trans('There are no hooks for event `%s`.'), $event));
		}


		$data            = array ();
		$data[ 'hooks' ] = $this->model->getEventHook($event);

		Library::addNavi(trans('Eventmanager'));
		Library::addNavi(sprintf(trans('Event `%s` bearbeiten'), $event));


		$this->Template->process('events/edit', $data, true);
	}

}
