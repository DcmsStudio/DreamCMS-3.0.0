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
 * @package      Menues
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Delete.php
 */
class Menues_Action_Delete extends Controller_Abstract
{

	/**
	 * @var
	 */
	private $jsTree;

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}
		demoadm();

		$model = Model::getModelInstance();
		$data  = $this->_post();

		if ( !(int)$data[ 'menuid' ] )
		{
			Error::raise("Invalid Menu ID");
		}

		if ( !(int)$data[ 'itemid' ] )
		{
			Error::raise("Invalid Menuitem ID");
		}


		$model->removeMenuitem($data[ 'menuid' ], $data[ 'itemid' ]);


		Library::log('Delete the Menuitem ID: ' . (int)$data[ 'itemid' ]);
		Ajax::Send(true);


		$id = (int)HTTP::input('id');
		if ( !$id )
		{
			Error::raise("Invalid ID");
		}

		demoadm();

		$this->jsTree = new DocumentTree('%tp%page', array (
		                                                   "id"        => "id",
		                                                   "parentid"  => "parentid",
		                                                   "position"  => "ordering",
		                                                   "type"      => "type",
		                                                   "is_folder" => "is_folder"
		                                             ));


		if ( HTTP::input('operation') === 'remove_node' )
		{
			$item = $this->jsTree->_get_node($id);


			$code = $this->jsTree->remove_node(HTTP::input());

			if ( $code )
			{
				$this->db->query('DELETE FROM %tp%page_trans WHERE id = ?', $id);

				Library::log('Delete the Menuitem ID: ' . $id);


				Ajax::Send(true);
			}
			else
			{
				Ajax::Send(false, array (
				                        'msg' => trans('Menüpunkt konnte nicht gelöscht werden!')
				                  ));
			}
		}


		throw new BaseException('Invalid request!');
	}

}

?>