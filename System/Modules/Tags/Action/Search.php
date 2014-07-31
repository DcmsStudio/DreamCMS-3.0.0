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
 * @package      Tags
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Search.php
 */
class Tags_Action_Search extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$table = Session::get('TAGS_FOR_TABLE');

		if ( !$table && $this->input('table') )
		{
			$table = $this->input('table');
		}



		if ( !$table && !$this->input('table') )
		{
			Library::sendJson(false, trans('Der Tag kann nicht gefunden werden, da die Inhalts Tabelle nicht übergeben wurde'));
		}


		$q       = trim(HTTP::input('q'));
		$q       = str_replace('*', '%', $q);
		$skipids = trim(HTTP::input('skip'));


		$this->load('Tags');
		$this->Tags->setContentTable($table);
		$tags = $this->Tags->getSearchTag($q, $skipids);

		echo Library::json(array (
		                         'success' => true,
		                         'tags'    => $tags
		                   ));
		exit;
	}

}

?>