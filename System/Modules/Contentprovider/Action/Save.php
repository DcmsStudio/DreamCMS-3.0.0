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
 * @package      Contentprovider
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Save.php
 */
class Contentprovider_Action_Save extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		demoadm();

		$name    = HTTP::post('name');
		$title   = HTTP::post('title');
		$coretag = HTTP::post('coretags');


		$model = Model::getModelInstance();


		$errors = array ();

		if ( !HTTP::post('id') )
		{

			$r = $model->getProviderByName($name);

			if ( $r[ 'id' ] )
			{
				if ( !$coretag )
				{
					$errors[ ] = '<li>Provider ist schon vorhanden.</li>';
				}
				else
				{
					$errors[ ] = '<li>Core-Tag ist schon vorhanden.</li>';
				}
			}
		}
		if ( trim($name) == '' )
		{
			$errors[ ] = '<li>Name ist leer. Ist aber erforderlich!</li>';
		}

		if ( trim($title) == '' )
		{
			$errors[ ] = '<li>Titel ist leer. Ist aber erforderlich!</li>';
		}


		if ( count($errors) > 0 )
		{
			echo Library::json(array (
			                         'success' => false,
			                         'msg'     => implode('', $errors)
			                   ));
			exit;
		}
		else
		{
			$data = HTTP::post();
			if ( !$coretag )
			{
				$data[ 'type' ] = '';
			}


			$id = $model->save((int)HTTP::post('id'), $data);

			Cache::refresh();

			$exit = isset($data[ 'exit' ]) ? $data[ 'exit' ] : false;
			if ( $exit )
			{
				if ( !$coretag )
				{
					Library::notify(trans('Content Provider erfolgreich gespeichert!'));
				}
				else
				{

					Library::notify(trans('Core-Tag erfolgreich gespeichert!'));
				}
			}

			echo Library::json(array (
			                         'success' => true,
			                         'msg'     => (!$coretag ? trans('Content Provider erfolgreich gespeichert!') :
					                         trans('Core-Tag erfolgreich gespeichert!')),
			                         'newid'   => $id
			                   ));
			exit;
		}
	}

}

?>