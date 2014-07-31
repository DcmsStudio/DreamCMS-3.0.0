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
 * @package      Ranks
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Ranks_Action_Index extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}
		$sql    = "SELECT r.rankid, r.ranktitle, r.needposts, g.title AS group_title, r.gender, r.rankimages
			FROM %tp%users_ranks AS r
			LEFT JOIN %tp%users_groups AS g ON(g.groupid=r.groupid)
			ORDER BY r.needposts, r.ranktitle";
		$result = $this->db->query($sql)->fetchAll();

		foreach ( $result as $r )
		{

			$r[ 'graphic' ]    = User::getRankImage($r[ 'rankimages' ]);
			$r[ 'gender' ]     = User::getGender($r[ 'gender' ]);
			$data[ 'list' ][ ] = $r;
		}

		$data[ 'base_iconpath' ] = HTML_URL . 'img/ranks/';

		Library::addNavi(trans('Benutzer Ränge'));


		$this->Template->process('ranks/ranks_view', $data, true);
	}

}

?>