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
 * @file         Publish.php
 */
class Modules_Action_Publish extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$id = (int)HTTP::input('id');
		demoadm();
		if ( !$id )
		{
			Library::sendJson(false, 'Dieses Modul existiert nicht.');
		}

		Cache::delete('modules', 'data');
		Cache::delete('fe_modules', 'data');
		Cache::delete('pages-tree', 'data');

		$rs          = $this->db->query('SELECT module, published FROM %tp%module WHERE id = ?', $id)->fetch();
		$new_publish = ($rs[ 'published' ] == 0 ? 1 : 0);

		$this->db->query('UPDATE %tp%module SET published = ? WHERE id = ?', $new_publish, $id);


		$this->getApplication()->refreshModulRegistry();

		$registry      = $this->getApplication()->getModulRegistry($rs[ "module" ]);
		$modulRegistry = isset($registry[ 'definition' ]) ? $registry[ 'definition' ] : array ();

		Library::log(sprintf("Change publishing for Module `%s` to %s", (!empty($modulRegistry[ "modulelabel" ]) ?
			$modulRegistry[ "modulelabel" ] : $rs[ "module" ]), ($new_publish ? 'online' : 'offline')));



		Library::sendJson(true, '' . $new_publish);
	}

}

?>