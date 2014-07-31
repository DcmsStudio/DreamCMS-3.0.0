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
 * @package      Contentprovider
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Check.php
 */
class Contentprovider_Action_Check extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		if ( HTTP::input('provider') != '' )
		{
			$name = ucfirst(HTTP::input('provider'));
			$path = Library::formatPath(PROVIDER_PATH . $name . '/' . $name . '.php');
		}
		elseif ( HTTP::input('coretag') != '' )
		{
			$name = ucfirst(HTTP::input('coretag'));
			$type = ucfirst(HTTP::input('type'));


			$path = Library::formatPath(CORETAGS_PATH . $name . '/' . $type . '.php');
		}

		if ( file_exists($path) )
		{
			echo Library::json(array (
			                         'success'     => true,
			                         'file_exists' => true
			                   ));
		}
		else
		{
			echo Library::json(array (
			                         'success'     => true,
			                         'file_exists' => false
			                   ));
		}


		exit;
	}

}

?>