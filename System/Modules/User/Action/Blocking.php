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
 * @package      User
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Blocking.php
 */
class User_Action_Blocking extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		demoadm();


		$doBlock = true;
		$userid  = (int)HTTP::input('userid');
		$ids     = explode(',', HTTP::input('ids'));
		if ( is_array($ids) )
		{

			$res   = $this->model->findUsersById($ids);
			$users = '';
			foreach ( $res as $r )
			{
				$users .= ($users ? ', ' . $r[ 'username' ] : $r[ 'username' ]);
			}

			$this->model->setBlocking(true, $ids);

			Library::log(sprintf('Block the Users: %s', $users));
			Library::sendJson(true, sprintf(trans('Die Benutzer `%s` wurden nun gesperrt!'), $users));
		}
		else
		{
			$res = $this->model->findUsersById(array (
			                                         $ids
			                                   ));
			$this->model->setBlocking(true, array (
			                                      $ids
			                                ));
			$r = $res[ 0 ];

			Library::log(sprintf('Block the Users: %s', $r[ 'username' ]));
			Library::sendJson(true, sprintf(trans('Dem Benutzer `%s` wurde gesperrt!'), $r[ 'username' ]));
		}
	}

}

?>