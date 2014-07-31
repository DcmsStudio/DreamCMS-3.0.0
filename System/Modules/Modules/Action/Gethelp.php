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
 * @package      Action
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Gethelp.php
 */
class Modules_Action_Gethelp extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->getApplication()->getMode() === Application::BACKEND_MODE )
		{
			$this->_processBackend();
		}
	}

	private function _processBackend ()
	{

		$modul = $this->_post('modul');
		$clean = Library::cleanDirName($modul);


		if ( $modul !== $clean )
		{
			Library::sendJson(false, 'Invalid Dirname!!!');
		}


		$_postParams = array ();


		if ( $this->_post('q') )
		{
			$_postParams[ 'q' ] = $this->_post('q');
		}

		if ( $this->input('key') )
		{
			$_postParams[ 'key' ] = $this->input('key');
		}

		$_postParams[ 'lang' ] = GUI_LANGUAGE;


		$this->load('Help');
		$this->Help->setModul($clean)->setParams($_postParams)->execute();

		$content = $this->Help->getContent();
		$header  = $this->Help->getHeaders();


		echo Library::json(array (
		                         'success'  => true,
		                         'htmlCode' => $content,
		                         'header'   => print_r($header, true)
		                   ));
		exit;
	}

}
