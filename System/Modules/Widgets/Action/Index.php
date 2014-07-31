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
 * @package      Widgets
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Widgets_Action_Index extends Widgets_Helper_Base
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

	private function _processBackend ()
	{

		if ( $this->input('get') )
		{
			ob_clean();
			$this->runWidget($this->input('get'), $this->input('id'));
			exit;
		}
		else
		{
			header("content-type: application/x-javascript");
			/*
			  $w = Session::get( 'WIDGETS' );
			  if ( $w )
			  {
			  echo Library::json( array( 'widgets' => $w, 'success' => true ) );
			  exit;
			  //die($w);
			  }
			  else
			  { */
			$w = $this->setWidgetSession();
			echo Library::json(array (
			                         'widgets' => $w,
			                         'success' => true
			                   ));
			exit;
			//    }
			//die($code);
		}
	}

	private function _processFrontend ()
	{

	}

}
