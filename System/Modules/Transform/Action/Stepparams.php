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
 * @package      Transform
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Stepparams.php
 */
class Transform_Action_Stepparams extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$type   = HTTP::input('type');
		$stepid = (int)HTTP::input('stepid');

		$data           = array ();
		$data[ 'type' ] = $type;


		$model = Model::getModelInstance();

		if ( $type == 'mask' )
		{
			$data[ 'masks' ] = $model->loadMasks();
		}

		$filters = ImageTools::getTransformations();
		$class   = 'ImageTransformation' . ucfirst($type);


		if ( method_exists($class, 'getDescription') )
		{
			$data[ 'description' ] = call_user_func(array (
			                                              $class,
			                                              'getDescription'
			                                        ));
		}


		$data[ 'params' ] = ImageTools::getParameters($type);
		$data[ 'step' ]   = $model->getStep($stepid);

		if ( $data[ 'step' ][ 'type' ] == $type )
		{
			$data[ 'values' ] = unserialize($data[ 'step' ][ 'parameters' ]);
		}
		else
		{
			$data[ 'values' ] = array ();
		}

		try
		{

			$output = $this->Template->process('transformation/step/' . $type, $data, null);

			echo Library::json(array (
			                         'success' => true,
			                         'html'    => $output
			                   ));
		}
		catch ( Exception $e )
		{
			$output = $type . ' not yet implemented';
			echo Library::json(array (
			                         'success' => false,
			                         'msg'     => $output
			                   ));
		}


		exit;
	}

}

?>