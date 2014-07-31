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
 * @package      News
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Delete_cats.php
 */
class News_Action_Delete_cats extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}


		demoadm();
		$catid = (int)$this->input('cat_id');
		$cats  = $this->model->getCats(true);
		$r     = array ();
		foreach ( $cats as $rs )
		{
			if ( $rs[ 'id' ] == $catid )
			{
				$r = $rs;
				break;
			}
		}

		if ( !isset($r[ 'name' ]) )
		{
			Library::sendJson(false, 'Invalid request to delete the news categorie!');
		}

		$this->model->deleteNewsCat($catid);

		Library::log(sprintf('Delete the News Categorie "%s".', $r[ 'name' ]));
		Library::sendJson(true, sprintf(trans('Die News Kategorie `%s` wurde gelöscht.'), $r[ 'name' ]));
	}

}

?>