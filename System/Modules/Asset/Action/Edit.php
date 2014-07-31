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
 * @package      Asset
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Edit.php
 */
class Asset_Action_Edit extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}


		$id = (int)HTTP::input('id');
		Library::addNavi(trans('Statische Inhalte'));
		$data = array ();
		if ( $id )
		{
			Library::addNavi(trans('Statischen Inhalt Bearbeiten'));
			$data[ 'asset' ] = Model::getModelInstance()->getAssetById($id); // $this->db->query('SELECT * FROM %tp%assets WHERE id = ?', $id)->fetch();
		}
		else
		{
			Library::addNavi(trans('Statischen Inhalt erstellen'));
		}

		if ( $this->_post('send') )
		{
			$data = $this->_post();
			$model  = Model::getModelInstance();
			$errors = $model->validate($data);

			if ( count($errors) > 0 )
			{
				echo Library::json(array (
				                         'success' => false,
				                         'msg'     => implode('; ', $errors)
				                   ));
				exit;
			}
			else
			{
				demoadm();

				//$data[ 'url' ] = '';

				if ( $id )
				{
					$id = $model->update($data);
					Library::log(sprintf('Edit the asset `%s`', ($data[ 'asset' ][ 'name' ] != $data[ 'name' ] ?
						$data[ 'name' ] : $data[ 'name' ])));
				}
				else
				{
					$id = $model->insert($data);
					Library::log(sprintf('Create a new asset Name: `%s`', $data[ 'name' ]));
				}

				Cache::refresh();
				echo Library::json(array (
				                         'success' => true,
				                         'msg'     => trans('Asset erfolgreich gespeichert!'),
				                         'newid'   => $id
				                   ));
				exit;
			}
		}


		//$this->Template->addScript(BACKEND_JS_URL . 'upload');
		$this->Template->process('assets/edit', $data, true);
		exit;
	}

}

?>