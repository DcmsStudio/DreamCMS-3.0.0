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
 * @package      Messenger
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Write.php
 */
class Messenger_Action_Write extends Controller_Abstract
{

	public function execute ()
	{

		$data = array ();
		$id   = (int)HTTP::get('id');

		if ( $id )
		{
			switch ( HTTP::get('type') )
			{

				case 'reply':
				default:
					$data = $this->model->prepareMessage($id, 'reply');
					break;
				case 'replyall':
					$data = $this->model->prepareMessage($id, 'replyall');
					break;
				case 'forward':
					$data = $this->model->prepareMessage($id, 'forward');
					break;
			}
		}

		$this->Template->process('messenger/write_message', $data, true);
	}

}

?>