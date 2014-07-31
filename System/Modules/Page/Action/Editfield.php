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
 * @file         Editfield.php
 */
class Page_Action_Editfield extends Controller_Abstract
{

	protected $_invalidFieldNames = array( 'adm', 'action','id', 'send', 'pagetype',  'teaser','catid','cssclass','isindexpage','usetitle','userating','useauthorinfo','usefootnotes','usesocialbookmarks','cancomment','content', 'title', 'tags','access','layout','parentid');



	public function execute ()
	{

		if ( $this->getApplication()->getMode() === Application::BACKEND_MODE )
		{
			$this->_processBackend();
		}
	}




	private function _processBackend ()
	{

		$pagetypeid = (int)$this->input('pagetypeid');

		if ( !$pagetypeid )
		{
			if ( IS_AJAX )
			{
				Library::sendJson(false, trans('Kein Seitentyp übergeben'));
			}
		}

		$pagetype = $this->model->getPagetypeById($pagetypeid);

		$id = (int)$this->input('fieldid');

		$field = array ();

		if ( $id )
		{
			$field              = $this->model->getFieldById($id);
			$field[ 'name' ]    = $field[ 'fieldname' ];
			$field[ 'id' ]      = $field[ 'name' ];
			$field[ 'fieldid' ] = $id;
			$field[ 'type' ]    = $field[ 'fieldtype' ];
			if ( !empty($field[ 'options' ]) )
			{
				$field = array_merge($field, unserialize($field[ 'options' ]));
			}
		}

		if ( $this->_post('send') )
		{
			$post   = $this->_post();
			$errors = $this->validate($post);

			if ( count($errors) && is_array($errors) )
			{
				$str = '';
				foreach ( $errors as $_field => $e )
				{
					$str = ($str ? ', ' : '') . 'Error: ' . $_field . ' ';
					foreach ( $e as $err )
					{
						$str .= $err;
					}
				}
				Library::sendJson(false, $str);
			}

			if ( in_array($post['fieldname'], $this->_invalidFieldNames)  ) {
				Library::sendJson(false, trans('Fehlerhafter Name des Feldes! Diesen Namen dürfen Sie nicht verwenden.'));

			}


			if ( $id )
			{
				$this->model->savePagetypeField($id, $post);
				Library::sendJson(true, sprintf(trans('Formularfeld `%s` wurde erfolgreich aktualisiert'), $post[ 'fieldname' ]));
			}
			else
			{
				$newid = $this->model->savePagetypeField(0, $post);
				echo Library::json(array (
				                         'success' => true,
				                         'msg'     => trans('Formularfeld wurde erfolgreich hinzugefügt'),
				                         'newid'   => $newid
				                   ));
				exit;
			}
		}


		$data[ 'field' ]      = $field;
		$data[ 'pagetypeid' ] = $pagetypeid;
		$data[ 'fieldid' ]    = $id;

		Library::addNavi(trans('Seitentypen Übersicht'));
		Library::addNavi(sprintf(trans('Formularfelder des Seitentypes `%s` '), $pagetype[ 'title' ]));
		Library::addNavi(($data[ 'field' ][ 'fieldid' ] ?
			sprintf(trans('Formularfeld `%s` bearbeiten'), '' . $data[ 'field' ][ 'name' ]) :
			trans('Formularfeld erstellen')));

		$this->Template->process('pages/editfields', $data, true);
	}

	/**
	 * Validating the Field
	 *
	 * @param array $data
	 * @internal param bool $isForm
	 * @return type
	 */
	public function validate ( $data )
	{

		$rules = array ();

		$rules[ 'fieldname' ][ 'required' ]   = array (
			'message' => trans('Feldname ist erforderlich'),
			'stop'    => true
		);


		$rules[ 'fieldname' ][ 'min_length' ] = array (
			'message' => trans('Der Feldname muss mind. 4 Zeichen lang sein'),
			'test'    => 3
		);

		$rules[ 'fieldname' ][ 'max_length' ] = array (
			'message' => trans('Der Feldname darf nicht länger als mind. 50 Zeichen lang sein'),
			'test'    => 50
		);

		$validator = new Validation($data, $rules);
		$errors    = $validator->validate();


		return $errors;
	}

}
