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
 * @package      Page
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Fieldoptions.php
 */
class Page_Action_Fieldoptions extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}
		$this->options();
	}

	/**
	 * Read the Field Settings
	 * used by Ajax request from Field edit/add
	 */
	public function options ()
	{

		$type       = trim(HTTP::input('type'));
		$pagetypeid = (int)HTTP::input('pagetypeid');
		$id         = (int)HTTP::input('fieldid');

		if ( !$type )
		{
			Library::sendJson(trans('Unbekannter Formularfeld Typ!'));
		}
		$data       = array ();
		$class_name = 'Field_' . ucfirst(strtolower($type)) . 'Field';

		$data[ 'attributes' ] = call_user_func(array (
		                                             $class_name,
		                                             'getAttributes'
		                                       ));

		$field              = $this->model->getFieldById($id);
		$field[ 'name' ]    = $field[ 'fieldname' ];
		$field[ 'id' ]      = $field[ 'name' ];
		$field[ 'type' ]    = $field[ 'fieldtype' ];
		$field[ 'fieldid' ] = $id;

		$duplicate = in_array($field[ 'type' ], array (
		                                              'textarea',
		                                              'richtext'
		                                        )) && in_array($type, array (
		                                                                    'textarea',
		                                                                    'richtext'
		                                                              ));

		if ( $field[ 'type' ] == $type || $duplicate )
		{

			$_data           = call_user_func_array(array (
			                                              $class_name,
			                                              'getFieldDefinition'
			                                        ), array (
			                                                 $field
			                                           ));
			$data[ 'field' ] = array_merge($field, $_data);
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
		$output[ 'output' ] = $this->Template->process('pages/fieldoptions', $data);

		echo Library::json($output);
		exit;
	}

}

?>