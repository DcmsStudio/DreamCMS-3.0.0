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
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Mysql.php
 */
class Forms_Model_Mysql extends Model
{

	/**
	 *
	 */
	public function __construct ()
	{

		parent::__construct();
	}

	/**
	 *
	 * @return array
	 */
	public function getGridData ()
	{

		switch ( strtolower($GLOBALS[ 'sort' ]) )
		{
			case 'asc':
				$sort = " ASC";
				break;

			case 'desc':
				$sort = " DESC";
				break;

			default:
				$sort = " ASC";
				break;
		}

		switch ( strtolower($GLOBALS[ 'orderby' ]) )
		{

			case 'name':
			default:
				$order = " ORDER BY f.`name`";
				break;
			case 'title':
				$order = " ORDER BY f.`title`";
				break;
			case 'description':
				$order = " ORDER BY f.description";
				break;
		}

		$total = $r[ 'total' ];
		$limit = $this->getPerpage();
		$page  = $this->getCurrentPage();

		$search = '';
		$search = HTTP::input('q');
		$search = trim((string)strtolower($search));

		$_s = '';
		if ( $search != '' )
		{
			$search = str_replace("*", "%", $search);
			$search = $this->db->quote('%' . $search . '%');
			$_s     = " WHERE ( LOWER( f.description ) LIKE " . $search . " OR LOWER( f.`name` ) LIKE " . $search . " OR LOWER( f.`title` ) LIKE " . $search . " )";
		}

		$r = $this->db->query('SELECT COUNT(f.formid) AS total FROM %tp%forms AS f ' . $_s)->fetch();

		return array (
			'result' => $this->db->query('SELECT f.* FROM %tp%forms AS f ' . $_s . ' ' . $order . $sort . '
                                            LIMIT ' . ($limit * ($page - 1)) . "," . $limit)->fetchAll(),
			'total'  => $r[ 'total' ]
		);
	}

	/**
	 * @param int $formid
	 * @return array
	 */
	public function getGridFieldsData ( $formid = 0 )
	{

		switch ( strtolower($GLOBALS[ 'sort' ]) )
		{
			case 'asc':
				$sort = " ASC";
				break;

			case 'desc':
				$sort = " DESC";
				break;

			default:
				$sort = " ASC";
				break;
		}

		switch ( strtolower($GLOBALS[ 'orderby' ]) )
		{

			case 'name':
			default:
				$order = " ORDER BY `name`";
				break;
			case 'type':
			case 'fieldtype':
				$order = " ORDER BY `type`";
				break;
			case 'rel':
				$order = " ORDER BY `rel`";
				break;
			case 'description':
				$order = " ORDER BY description";
				break;
		}

		$limit = $this->getPerpage();
		$page  = $this->getCurrentPage();

		$search = '';
		$search = HTTP::input('q');
		$search = trim((string)strtolower($search));

		$_s = '';
		if ( $search != '' )
		{
			$search = str_replace("*", "%", $search);

			$search = $this->db->quote('%' . $search . '%');
			$_s     = " AND ( LOWER( `type` ) LIKE " . $search;
			$_s .= " OR LOWER( description ) LIKE " . $search . " OR LOWER( `name` ) LIKE " . $search . " )";
		}

		$r = $this->db->query('SELECT COUNT(field_id) AS total FROM %tp%form_fields WHERE formid = ?' . $_s, $formid)->fetch();

		return array (
			'result' => $this->db->query('SELECT * FROM %tp%form_fields
                                    WHERE formid = ' . $formid . $_s . ' ' . $order . $sort . '
                                    LIMIT ' . ($limit * ($page - 1)) . ',' . $limit)->fetchAll(),
			'total'  => $r[ 'total' ]
		);
	}

	/**
	 *
	 * @return array
	 */
	public function getProfileFields ()
	{

		return $this->db->query('SELECT pf.profilefieldid, pf.required, pf.reg_required, f.*
                                            FROM %tp%profile_fields AS pf
                                            LEFT JOIN %tp%form_fields AS f ON(f.field_id = pf.field_id AND f.formid = 0) 
                                            WHERE f.field_id > 0')->fetchAll();
	}

	/**
	 *
	 * @param integer $userid
	 * @return array
	 */
	public function getProfileFieldData ( $userid = 0 )
	{

		return $this->db->query('SELECT * FROM %tp%form_fielddata WHERE `userid` = ? AND `rel` = ' . $this->db->quote('profilefield'), $userid)->fetchAll();
	}

	/**
	 *
	 * @param integer $userid
	 * @return $this
	 */
	public function deleteProfileFieldData ( $userid = 0 )
	{

		$this->db->query('DELETE FROM %tp%form_fielddata WHERE `userid` = ' . $userid . ' AND `rel` = ' . $this->db->quote('profilefield'));

		return $this;
	}

	/**
	 *
	 * @param array $data
	 * @return void
	 */
	public function saveFieldData ( array $data )
	{

		return $this->db->insert('%tp%form_fielddata')->values($data)->execute();
	}

	/**
	 * getField
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getField ( $id )
	{

		return $this->db->query("SELECT * FROM %tp%form_fields WHERE field_id = ?", $id)->fetch();
	}

	/**
	 * getForm
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getForm ( $id )
	{

		return $this->db->query("SELECT * FROM %tp%forms WHERE formid = ?", $id)->fetch();
	}

	/**
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getFieldsByFormID ( $id )
	{

		return $this->db->query("SELECT * FROM %tp%form_fields WHERE formid = ?", $id)->fetchAll();
	}

	/**
	 *
	 * @param array $id
	 * @return array
	 */
	public function getFieldsByIds ( $id )
	{

		if ( !is_array($id) || (is_array($id) && !count($id)) )
		{
			return array ();
		}

		$ids = implode(',', $id);

		return $this->db->query("SELECT * FROM %tp%form_fields WHERE field_id IN(0," . $ids . ")")->fetchAll();
	}

	/**
	 *
	 * @param array $data
	 * @return array
	 * @throws BaseException
	 */
	public function getCreateForm ( $data )
	{

		if ( !isset($data[ 'name' ]) && !isset($data[ 'formid' ]) )
		{
			throw new BaseException('Could not create the Form!');
		}

		$data = array ();

		if ( isset($data[ 'name' ]) && !empty($data[ 'name' ]) )
		{
			$data = Form::generateForm($data[ 'name' ], $tag);
		}
		elseif ( isset($data[ 'formid' ]) && !(int)$data[ 'formid' ] )
		{
			$data = Form::generateForm($data[ 'name' ], $tag);
		}
		else
		{
			throw new BaseException('Could not create the Form!');
		}

		return $this->Template->process('forms/form', $data);
	}

	/**
	 * Saving Fields and Forms
	 *
	 * @param array $data
	 * @param bool  $isForm
	 * @return integer id of the Field or Form
	 */
	public function saveData ( $data, $isForm = false )
	{

		$this->db->begin();

		if ( !$isForm )
		{
			if ( (int)$data[ 'field_id' ] == 0 )
			{
				$field_id = $this->_insert($data);
				Library::log(sprintf("Created custom field %s.", $data[ 'name' ]));
			}
			else
			{
				$field_id = $this->_update($data);
				Library::log(sprintf("Edited custom field %s.", $data[ 'name' ]));
			}
		}
		else
		{
			if ( (int)$data[ 'formid' ] == 0 )
			{
				$field_id = $this->_insertForm($data);
				Library::log(sprintf("Created a new Form %s.", $data[ 'title' ]));
			}
			else
			{
				$field_id = $this->_updateForm($data);
				Library::log(sprintf("Edited the Form %s.", $data[ 'title' ]));
			}
		}

		$this->db->commit();

		return $field_id;
	}

	/**
	 *
	 * @param array $data
	 * @return integer
	 */
	private function _insert ( $data )
	{

		$data = $this->prepareData($data);

		$this->db->query('
            INSERT INTO %tp%form_fields SET
            `formid` = ?,
            `name` = ?,
            `type` = ?,
            `description` = ?,
            `options` = ?, `rel` = ?', (int)$data[ 'formid' ], $data[ 'name' ], $data[ 'type' ], $data[ 'description' ], $data[ 'options' ], $data[ 'rel' ]);

		$newid = $this->db->insert_id();

		if ( !(int)$data[ 'formid' ] )
		{
			$this->db->query('INSERT INTO %tp%profile_fields (field_id,ordering,required,reg_required) VALUES(?,0,0,0)', $newid);
		}

		return $newid;
	}

	/**
	 *
	 * @param array $data
	 * @return void
	 */
	private function _update ( $data )
	{

		$data = $this->prepareData($data);

		$this->db->query('UPDATE %tp%form_fields SET `name` = ?, `type` = ?, `description` = ?, `options` = ?, `rel` = ? WHERE `field_id` = ?', $data[ 'name' ], $data[ 'type' ], $data[ 'description' ], $data[ 'options' ], $data[ 'rel' ], (int)$data[ 'field_id' ]);

		if ( !(int)$data[ 'formid' ] )
		{
			$this->db->query('UPDATE %tp%profile_fields SET ordering = ?, required = ?, reg_required = ? WHERE field_id = ?', (int)$data[ 'ordering' ], (int)$data[ 'required' ], (int)$data[ 'reg_required' ], (int)$data[ 'field_id' ]);
		}

		return;
	}

	/**
	 * insert new form
	 *
	 * @param array $data
	 * @return integer
	 */
	private function _insertForm ( &$data )
	{

		$this->db->query('
            INSERT INTO %tp%forms SET `name` = ?, `title` = ?, `description` = ?, `submitmsg` = ?,
            `errormsg` = ?, `fields` = ?, formaction = ?, method = ?, formtype = ?, email = ?, datapath = ?, cryptdata = ?, email_template=?', $data[ 'name' ], $data[ 'title' ], $data[ 'description' ], $data[ 'submitmsg' ], $data[ 'errormsg' ], implode(',', $data[ 'field' ]), $data[ 'formaction' ], $data[ 'method' ], $data[ 'formtype' ], $data[ 'email' ], $data[ 'datapath' ], (int)$data[ 'cryptdata' ], $data[ 'email_template' ]);

		return $this->db->insert_id();
	}

	/**
	 * update a form
	 *
	 * @param array $data
	 * @return void
	 */
	private function _updateForm ( &$data )
	{

		$this->db->query('
            UPDATE %tp%forms SET
            `name` = ?, `title` = ?, `description` = ?, `submitmsg` = ?,
            `errormsg` = ?, `fields` = ?, formaction = ?, method = ?, 
            formtype = ?, email = ?, datapath = ?, cryptdata = ?, email_template=?
            WHERE `formid` = ?', $data[ 'name' ], $data[ 'title' ], $data[ 'description' ], $data[ 'submitmsg' ], $data[ 'errormsg' ], implode(',', $data[ 'field' ]), $data[ 'formaction' ], $data[ 'method' ], $data[ 'formtype' ], $data[ 'email' ], $data[ 'datapath' ], (int)$data[ 'cryptdata' ], $data[ 'email_template' ], (int)$data[ 'formid' ]);

		return;
	}

	/**
	 * Prepare Form Field settings for saving
	 *
	 * @param array $data
	 * @return array
	 */
	private function prepareData ( $data )
	{

		$class_name = 'Field_' . ucfirst(strtolower($data[ 'type' ])) . 'Field';
		$attributes = call_user_func(array (
		                                   $class_name,
		                                   'getAttributes'
		                             ));

		$options = array ();
		foreach ( $attributes as $attribute )
		{
			if ( !empty($data[ $attribute ]) )
			{
				$options[ $attribute ] = $data[ $attribute ];
			}
		}

		$ret                  = array ();
		$ret[ 'options' ]     = serialize($options);
		$ret[ 'formid' ]      = (int)$data[ 'formid' ];
		$ret[ 'field_id' ]    = (int)$data[ 'field_id' ];
		$ret[ 'name' ]        = (string)$data[ 'name' ];
		$ret[ 'type' ]        = (string)$data[ 'type' ];
		$ret[ 'description' ] = (string)$data[ 'description' ];
		$ret[ 'rel' ]         = (!$ret[ 'formid' ] ? 'profilefield' : (string)$data[ 'rel' ]);

		return $ret;
	}

	/**
	 *
	 */
	public function deleteform ()
	{

		$formid = HTTP::input('ids') ? HTTP::input('ids') : HTTP::input('formid');
		$ids    = explode(',', $formid);


		$names = '';
		foreach ( $ids as $id )
		{
			$id = (int)$id;

			if ( !$id )
			{
				continue;
			}

			$form = $this->getForm($id);

			$this->db->query('DELETE FROM %tp%forms WHERE formid = ? ', $id);
			$this->db->query('DELETE FROM %tp%form_fields WHERE formid = ?', $id);

			// remove data for the fields
			$form[ 'fields' ] = !empty($form[ 'fields' ]) ? unserialize($form[ 'fields' ]) : array ();
			$this->db->query('DELETE FROM %tp%form_fielddata WHERE field_id IN(0,' . implode(',', $form[ 'fields' ]) . ')');

			$names .= ($names ? ', ' : '') . $form[ 'title' ];
		}

		Library::log(sprintf('Has deleted Form(s) `%s`', $names));
		Library::sendJson(true, sprintf(trans('Formular(e) `%s` wurde gelöscht.'), $names));
	}

	/**
	 *
	 */
	public function deletefield ()
	{

		$fieldid = HTTP::input('ids') ? HTTP::input('ids') : HTTP::input('fieldid');
		$formid  = (int)HTTP::input('formid');

		if ( $formid > 0 )
		{
			$form = $this->getForm($formid);
		}

		$fieldids = explode(',', $fieldid);

		$names = '';
		foreach ( $fieldids as $id )
		{
			if ( !(int)$id )
			{
				continue;
			}

			$field = $this->getField($id);
			$this->db->query('DELETE FROM %tp%form_fields WHERE formid = ? AND field_id = ?', $formid, $id);

			if ( !$formid )
			{
				// remove profilefield
				$this->db->query('DELETE FROM %tp%profile_fields WHERE field_id = ?', $id);
			}

			// remove data for the field
			$this->db->query('DELETE FROM %tp%form_fielddata WHERE field_id = ?', $id);

			$names .= ($names ? ', ' : '') . $field[ 'name' ];

			if ( $formid && isset($form[ 'fields' ]) )
			{
				$fields = unserialize($form[ 'fields' ]);

				foreach ( $fields as $idx => $id )
				{
					if ( $id === $fieldid )
					{
						unset($fields[ $idx ]);
					}
				}

				$this->db->query('UPDATE %tp%forms SET `fields` = ? WHERE formid = ?', serialize($fields), $formid);
			}
		}
		if ( $formid )
		{
			Library::log(sprintf('Has deleted the Formfield(s) `%s` in the Form `%s`', $names, $form[ 'title' ]));
			Library::sendJson(true, sprintf(trans('Formularfeld(er) `%s` im Formular `%s` wurde gelöscht.'), $names, $form[ 'title' ]));
		}
		else
		{
			Library::log(sprintf('Has deleted the Profilefield(s) `%s`', $names));
			Library::sendJson(true, sprintf(trans('Profilefeld(er) `%s` wurde gelöscht.'), $names));
		}
	}

}

?>