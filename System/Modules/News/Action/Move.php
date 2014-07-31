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
 * @file         Move.php
 */
class News_Action_Move extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}
		demoadm();

		$moveto = (int)HTTP::input('moveto');


		if ( !$moveto )
		{
			Error::raise(trans('Sie haben keine Kategorie gewählt. Bitte wählen Sie erst eine Kategorie in die, die News verschoben werden sollen.'));
		}

		$ids = HTTP::input('ids') ? explode(',', HTTP::input('ids')) : null;
		if ( $ids === null || !count($ids) )
		{
			Error::raise("Invalid News IDs");
		}


		$cat = $this->db->query('SELECT title FROM %tp%news_categories_trans WHERE id = ? AND (lang = ? OR iscorelang = 1) LIMIT 1', $moveto, CONTENT_TRANS)->fetch();


		$ids    = implode(',', $ids);
		$sql    = "SELECT nt.title, n.id FROM %tp%news AS n
                    LEFT JOIN %tp%news_trans AS nt ON(nt.id = n.id)
                    WHERE n.id IN(0,$ids)
                    GROUP BY n.id";
		$result = $this->db->query($sql)->fetchAll();
		foreach ( $result as $r )
		{
			$this->db->query('UPDATE %tp%news SET cat_id=? WHERE id=?', '' . $moveto, $r[ 'id' ]);
			Library::log(sprintf("Has move the News \"%s\" to %s (ID:%s).", $r[ 'title' ], $cat[ 'title' ], $r[ 'id' ]));
		}

		Library::sendJson(true, sprintf(trans('Die ausgewählten News wurden erfolgreich in die Kategorie `%s` verschoben.'), $cat[ 'title' ]));
		exit;
	}

}

?>