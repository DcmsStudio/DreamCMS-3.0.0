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
 * @package      Layouter
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Duplicate.php
 */
class Layouter_Action_Duplicate extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->getApplication()->getMode() === Application::BACKEND_MODE )
		{
			$data   = array ();
			$skinid = (int)HTTP::input('skinid');
			$id     = (int)HTTP::input('id');

			if ( !$skinid )
			{
				Error::raise(trans('Es wurde keine Skin ID übergeben!'));
			}

			demoadm();

			$data = $this->db->query("SELECT * FROM %tp%layouts WHERE id = ?", $id)->fetch();
			unset($data[ 'created' ], $data[ 'modified' ], $data[ 'id' ]);

			$data[ 'created' ]  = time();
			$data[ 'modified' ] = 0;
			$originalTitle      = $data[ 'title' ];
			$data[ 'title' ]    = $data[ 'title' ] . ' (' . trans('Duplikat') . ')';

			$str = $this->db->compile_db_insert_string($data);
			$this->db->query("INSERT INTO %tp%layouts ({$str['FIELD_NAMES']}) VALUES({$str['FIELD_VALUES']})");

			echo Library::json(array (
			                         'success' => true,
			                         'newid'   => $id,
			                         'msg'     => sprintf(trans('Layout `%s` wurde erfolgreich Dupliziert.'), $originalTitle)
			                   ));
			exit;
		}
	}

}

?>