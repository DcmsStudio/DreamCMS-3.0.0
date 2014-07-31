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
 * @file         Update.php
 */
class Modules_Action_Update extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}


		demoadm();
		$id = (int)HTTP::input('id');

		if ( !$id )
		{
			Library::sendJson(false, 'Dieses Modul existiert nicht.');
		}

		$r = $this->db->query('SELECT * FROM %tp%module WHERE id = ?', $id)->fetch();

		$registry      = $this->getApplication()->getModulRegistry($r[ "module" ]);
		$modulRegistry = isset($registry[ 'definition' ]) ? $registry[ 'definition' ] : array ();


		if ( $modulRegistry[ 'version' ] == $r[ 'version' ] )
		{
			Library::sendJson(true, sprintf(trans('Das Modul `%s` ist auf dem Aktuellsten stand und wurde daher nicht aktualisiert.'), (!empty($modulRegistry[ 'metatables' ]) ?
				$modulRegistry[ 'modulelabel' ] : $r[ 'module' ])));
		}

		$str = $this->db->compile_db_update_string(array (
		                                                 'configurable'  => (!empty($modulRegistry[ 'configurable' ]) && !empty($modulRegistry[ 'configfields' ]) ?
				                                                 1 : 0),
		                                                 'version'       => $modulRegistry[ 'version' ],
		                                                 'allowmetadata' => (int)$modulRegistry[ 'allowmetadata' ],
		                                                 'metatables'    => (!empty($modulRegistry[ 'metatables' ]) ?
				                                                 serialize($modulRegistry[ 'metatables' ]) : ''),
		                                                 'treeactions'   => (!empty($modulRegistry[ 'treeactions' ]) ?
				                                                 serialize($modulRegistry[ 'treeactions' ]) : ''),
		                                                 'modulactions'  => (!empty($modulRegistry[ 'modulactions' ]) ?
				                                                 serialize($modulRegistry[ 'modulactions' ]) : '')
		                                           ));

		$this->db->query("UPDATE %tp%module SET $str WHERE id = ?", $id);

		Cache::delete('modules', 'data');

		Library::log(sprintf("Update the module `%s`", (!empty($modulRegistry[ 'metatables' ]) ?
			$modulRegistry[ 'modulelabel' ] : $r[ 'module' ])));
		Library::sendJson(true, sprintf(trans('Das Modul `%s` wurde aktualisiert'), (!empty($modulRegistry[ 'metatables' ]) ?
			$modulRegistry[ 'modulelabel' ] : $r[ 'module' ])));
	}

}
