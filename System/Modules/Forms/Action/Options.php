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
 * @package      Forms
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Options.php
 */
class Forms_Action_Options extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->getApplication()->getMode() === Application::BACKEND_MODE )
		{
			$this->options();
		}
	}

	/**
	 * Read the Field Settings
	 * used by Ajax request from Field edit/add
	 */
	public function options ()
	{

		$type = trim(HTTP::input('type'));
		$id   = (int)HTTP::input('id');

		if ( !$type )
		{
			Library::sendJson(trans('Unbekannter Formularfeld Typ!'));
		}
		$data                 = array ();
		$class_name           = 'Field_' . ucfirst(strtolower($type)) . 'Field';
		$data[ 'attributes' ] = call_user_func(array (
		                                             $class_name,
		                                             'getAttributes'
		                                       ));

		$field = $this->model->getField($id);

		$duplicate = in_array($field[ 'type' ], array (
		                                              'textarea',
		                                              'richtext'
		                                        )) && in_array($type, array (
		                                                                    'textarea',
		                                                                    'richtext'
		                                                              ));

		if ( $field[ 'type' ] == $type || $duplicate )
		{
			$data[ 'field' ] = call_user_func_array(array (
			                                              $class_name,
			                                              'getFieldDefinition'
			                                        ), array (
			                                                 $field
			                                           ));
		}
		else
		{
			$data[ 'field' ] = array (
				'type' => $type
			);
		}

		$output             = array (
			'success' => true
		);
		$output[ 'output' ] = $this->Template->process('forms/fieldoptions', $data);

		echo Library::json($output);
		exit;
	}

}

?>