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
 * @package      Main
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Bbcodepreview.php
 */
class Main_Action_Bbcodepreview extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->getApplication()->getMode() === Application::BACKEND_MODE )
		{
			$this->_processBackend();
		}
		else
		{
			$this->_processFrontend();
		}
	}

	protected function _processFrontend ()
	{

		$text         = HTTP::post('data');
		$allowedBBode = HTTP::input('allowedbbcode');

		if ( !$allowedBBode )
		{
			echo Library::json(array (
			                         'success' => false,
			                         'msg'     => trans('Sorry aber du hast bestimmt den Übergabecode manipuliert?!')
			                   ));
			exit;
		}

		$found = false;
		switch ( $allowedBBode )
		{
			case 'commentbbcodes':
			case 'biobbcodes':
				$found = true;
				break;
		}

		if ( !$found )
		{
			echo Library::json(array (
			                         'success' => false,
			                         'msg'     => trans('Sorry aber du hast den Übergabecode manipuliert!')
			                   ));
			exit;
		}

		BBCode::setBBcodeHandler($allowedBBode);

		echo Library::json(array (
		                         'success' => true,
		                         'preview' => BBCode::toXHTML($text)
		                   ));
		exit;
	}

}
