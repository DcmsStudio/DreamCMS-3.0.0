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
 * @package      Transform
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Preview.php
 */
class Transform_Action_Preview extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$mask = HTTP::input('mask') != '' ? HTTP::input('mask') : null;

		if ( !is_null($mask) )
		{
			$path = PUBLIC_PATH . 'img/masks/' . $mask . '.png';

			header("Cache-Control: no-cache, must-revalidate");
			header("Expires: Sat, 20 Jul 1990 06:00:00 GMT");
			header('Content-type: image/png');
			readfile($path);
			exit;
		}


		$transformation = HTTP::input('transformation') != '' ? HTTP::input('transformation') : 'default';
		$format         = strtolower((HTTP::input('format') != '' ? HTTP::input('format') : 'jpeg'));


		Cache::delete('imagechains');

		$format = in_array($format, array (
		                                  'jpeg',
		                                  'png',
		                                  'gif'
		                            )) ? $format : 'jpeg';
		$path   = PUBLIC_PATH . 'img/preview.jpg';

		$chain = Library::getImageChain($transformation);


		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Sat, 20 Jul 1990 06:00:00 GMT");

		ImageTools::create()->output(array (
		                                   'source'  => $path,
		                                   'chain'   => $chain,
		                                   'output'  => $format,
		                                   'cache'   => false,
		                                   'quality' => 80
		                             ));

		die();
	}

}

?>