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
 * @file         Editstep.php
 */
class Transform_Action_Editstep extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$transid = (int)HTTP::input('id');
		$stepid  = (int)HTTP::input('stepid');

		$data             = array ();
		$data[ 'id' ]     = $transid;
		$data[ 'stepid' ] = $stepid;


		$model = Model::getModelInstance();

		$data[ 'transformation' ] = $model->getTransformation($transid);
		$data[ 'step' ]           = $model->getStep($stepid);
		$data[ 'type' ]           = !empty($data[ 'step' ][ 'type' ]) ? $data[ 'step' ][ 'type' ] : '';
		$filters                  = ImageTools::getTransformations();

		$data[ 'filters' ] = array ();
		foreach ( $filters as $idx => $value )
		{
			$data[ 'filters' ][ ] = array (
				'label' => $value . ' (' . $idx . ')',
				'value' => $idx
			);
		}


		Library::addNavi(sprintf(trans('Transform `%s` Schritt `%s` bearbeiten'), $data[ 'transformation' ][ 'title' ], $data[ 'step' ][ 'type' ]));

		$this->Template->process('transformation/editstep', $data, true);
		exit;
	}

}

?>