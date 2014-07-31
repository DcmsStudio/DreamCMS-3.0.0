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
 * @file         Edit.php
 */
class Contentprovider_Action_Edit extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$id       = (int)HTTP::input('id');
		$coretags = (int)HTTP::input('coretags');

		$model              = Model::getModelInstance();
		$data[ 'provider' ] = $model->getProvider($id);

		if ( !$data[ 'provider' ][ 'iscoretag' ] )
		{
			if ( isset($data[ 'provider' ][ 'system' ]) && $data[ 'provider' ][ 'system' ] == 1 )
			{
				Error::raise(sprintf(trans('Dieser Content Provider `%s` kann nicht bearbeitet werden da er vom System vorgegeben wird.'), $data[ 'provider' ][ 'name' ]));
			}

			$data[ 'provider' ][ 'file_exists' ] = false;

			if ( isset($data[ 'provider' ][ 'name' ]) )
			{
				$path = Library::formatPath(PROVIDER_PATH . ucfirst($data[ 'provider' ][ 'name' ]) . '/' . ucfirst($data[ 'provider' ][ 'name' ]) . '.php');

				if ( file_exists($path) )
				{
					$data[ 'provider' ][ 'file_exists' ] = true;
				}
			}


			Library::addNavi(trans('Content Provider'));
			Library::addNavi(($id ? sprintf(trans('Content Provider %s bearbeiten'), $data[ 'provider' ][ 'title' ]) :
				trans('Content Provider erstellen')));
		}
		else
		{
			if ( isset($data[ 'provider' ][ 'system' ]) && $data[ 'provider' ][ 'system' ] == 1 )
			{
				Error::raise(sprintf(trans('Dieser Core-Tag `%s` kann nicht bearbeitet werden da er vom System vorgegeben wird.'), $data[ 'provider' ][ 'name' ]));
			}

			$data[ 'provider' ][ 'file_exists' ] = false;

			if ( isset($data[ 'provider' ][ 'name' ]) )
			{
				$path = Library::formatPath(CORETAGS_PATH . ucfirst($data[ 'provider' ][ 'name' ]) . '/' . ucfirst($data[ 'provider' ][ 'type' ]) . '.php');

				if ( file_exists($path) )
				{
					$data[ 'provider' ][ 'file_exists' ] = true;
				}
			}


			Library::addNavi(trans('Core-Tags'));
			Library::addNavi(($id ? sprintf(trans('Core-Tag %s bearbeiten'), $data[ 'provider' ][ 'title' ]) :
				trans('Core-Tag erstellen')));
		}

		$this->Template->process('contentprovider/edit', $data, true);
	}

}

?>