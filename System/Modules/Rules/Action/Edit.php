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
 * @package      Rules
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Edit.php
 */
class Rules_Action_Edit extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}
		$id = (int)HTTP::input('id');


		if ( HTTP::input('send') )
		{
			demoadm();
			$controller = HTTP::input('controller');
			if ( !trim($controller) )
			{
				Library::sendJson(false, trans("Controller darf nicht leer sein"));
			}

			$maps = '';
			if ( HTTP::input('maps') && is_array(HTTP::input('maps')) )
			{
				$maps = serialize(HTTP::input('maps'));
			}

			$arr = array (
				'controller'  => HTTP::input('controller'),
				'action'      => HTTP::input('ruleaction'),
				'optionalmap' => $maps,
				'rule'        => (HTTP::input('raction') ? HTTP::input('raction') : ''),
				'published'   => (int)HTTP::input('published')
			);


			if ( $id )
			{
				$str = $this->db->compile_db_update_string($arr);
				$sql = "UPDATE %tp%routermap SET {$str} WHERE ruleid = {$id}";
				$this->db->query($sql);

				Cache::delete('routemap', 'data');


				Library::log(sprintf(trans("Router Rule for Controller `%s/%s` changed."), $arr[ 'controller' ], $arr[ 'action' ]));
				Library::sendJson(true, trans("Router Rule erfolgreich aktualisiert."));
			}
			else
			{
				$str = $this->db->compile_db_insert_string($arr);
				$sql = "INSERT %tp%routermap ({$str['FIELD_NAMES']}) VALUES({$str['FIELD_VALUES']})";
				$this->db->query($sql);

				Cache::delete('routemap', 'data');

				Library::log(sprintf(trans("Router Rule for Controller `%s/%s` added."), $arr[ 'controller' ], $arr[ 'action' ]));
				Library::sendJson(true, trans("Router Rule erfolgreich erstellt."));
			}
		}


		$data[ 'rule' ] = $this->db->query('SELECT *, ruleid AS id FROM %tp%routermap WHERE ruleid = ' . $id)->fetch();
		if ( $data[ 'rule' ][ 'optionalmap' ] )
		{

			$maps = unserialize($data[ 'rule' ][ 'optionalmap' ]);
			$tmp  = array ();

			foreach ( $maps[ 'attribute' ] as $idx => $value )
			{
				$tmp[ ] = array (
					'attribute' => $value,
					'match'     => $maps[ 'match' ][ $idx ]
				);
			}

			$data[ 'maps' ] = $tmp;
		}

		$this->Template->process('router/edit', $data, true);
		exit;
	}

}

?>