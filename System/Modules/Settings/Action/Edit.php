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
 * @package      Settings
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Edit.php
 */
class Settings_Action_Edit extends Controller_Abstract
{

	/**
	 * @param $groupkey
	 * @param $groups
	 * @return array|null
	 */
	private function findGroup ( $groupkey, $groups )
	{

		foreach ( $groups as $key => $rs )
		{

			if ( is_array($rs) )
			{
				if ( isset( $rs[ $groupkey ] ) )
				{
					return $rs;
				}
			}
			else
			{
				if ( isset( $groups[ $groupkey ] ) )
				{
					return $rs;
				}
			}
		}

		return null;
	}

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$type     = $this->input('type');
		$groupkey = $this->input('group');
		$groups   = Dashboard_Config_Base::loadConfigOptions();


		$group = $this->findGroup($groupkey, $groups);


		if ( !isset( $group[ $groupkey ] ) )
		{
			if ( $this->input('group') )
			{
				Library::sendJson(false, trans('This Config Group not exists!'));
			}


			trigger_error('This Config Group not exists!', E_USER_ERROR);
		}

		$items = $group[ $groupkey ];

		if ( $this->_post('send') )
		{
			demoadm();

			$newValues    = array ();
			$inputvalues  = $this->_post();
			$field_error  = false;
			$field_errors = array ();
			foreach ( $items[ 'items' ] as $fieldname => $field )
			{
				if ( $field_error )
				{
					continue;
				}

				$field[ 'id' ]   = $fieldname;
				$field[ 'name' ] = $fieldname;

				$value = ( isset( $inputvalues[ $fieldname ] ) && $inputvalues[ $fieldname ] != '' ? $inputvalues[ $fieldname ] : '' );





				if ( is_array($value) )
				{
					$value = implode(',', $value);
				}

				if ( $field[ 'rgxp' ][ 0 ] != '' || ( $field[ 'maxlength' ] && $value != '' ) || $field[ 'controll' ] )
				{
					$rules = array ();
					if ( $field[ 'controls' ] )
					{
						$rules[ $fieldname ][ 'required' ] = array (
							'message' => trans('Eingabe ist erforderlich'),
							'stop'    => true
						);

						if ( $field[ 'rgxp' ][ 0 ] != '-' && $field[ 'rgxp' ][ 0 ] != '' && isset( $field[ 'rgxp' ][ 1 ] ) )
						{
							$rules[ $fieldname ][ $field[ 'rgxp' ][ 0 ] ] = array (
								'message' => $field[ 'rgxp' ][ 1 ],
								'stop'    => true
							);
						}
					}

					if ( ( $field[ 'maxlength' ] && $value != '' ) )
					{
						$rules[ $fieldname ][ 'max_length' ] = array (
							'message' => sprintf(trans('Eingabe darf maximal %s Zeichen lang sein'), $field[ 'maxlength' ]),
							'stop'    => true,
							'test'    => $field[ 'maxlength' ]
						);
					}

                    if ( $field[ 'rgxp' ][ 0 ] != '-' && $field[ 'rgxp' ][ 0 ] != '' && isset( $field[ 'rgxp' ][ 1 ] ) )
                    {
                        $rules[ $fieldname ][ $field[ 'rgxp' ][ 0 ] ] = array (
                            'message' => $field[ 'rgxp' ][ 1 ],
                            'stop'    => true
                        );
                    }



					$validator = new Validation( $inputvalues, $rules );
					$error     = $validator->validate();

					if ( count($error) )
					{
						$field_errors[ ] = $error[ 0 ] . ' (' . $field[ 'label' ] . ')';
						$field_error     = true;
					}
				}
                if (($field['type'] == 'radio' || $field['type'] == 'checkbox') && $value === '' ) {
                    $value = 0;
                }
				$newValues[ $fieldname ] = $value;
			}


			if ( $field_error )
			{
				Ajax::Send(false, array (
				                        'msg' => sprintf(trans('Die Konfiguration konnte nicht abgeschlossen werden. %s'), implode(', ', $field_errors))
				                  ));
                exit;
			}


			$this->db->query("DELETE FROM %tp%config WHERE pageid = ? AND `group` = ?", PAGEID, $groupkey);

			foreach ( $newValues as $fieldname => $value )
			{
				$this->db->query("REPLACE INTO %tp%config (`value`, varname, `group`, pageid, modul) VALUES(?,?,?,?,?)", $value, $fieldname, $groupkey, PAGEID, ( $type == 'modules' ? 1 : ( $type == 'plugin' ? 2 : 0 ) ));
			}

			Library::enableErrorHandling();
			Settings::write();

			Library::log(sprintf('Change settings for the module `%s`', $items[ 'label' ]));
			Library::sendJson(true, sprintf(trans('Die Konfiguration wurde aktualisiert.')));

			exit;
		}


		$result   = $this->db->query('SELECT * FROM %tp%config WHERE `group` = ? AND pageid = ?', $groupkey, PAGEID)->fetchAll();
		$cfgcache = array ();
		foreach ( $result as $r )
		{
			$cfgcache[ $r[ 'varname' ] ] = $r[ 'value' ];
		}

		$_fields = array ();


		foreach ( $items[ 'items' ] as $fieldname => $field )
		{
			$field[ 'field_id' ]  = $fieldname;
			$field[ 'fieldid' ]   = $fieldname;
			$field[ 'id' ]        = $fieldname;
			$field[ 'fieldtype' ] = $field[ 'type' ];
			$field[ 'required' ]  = ( $field[ 'controll' ] ? 1 : 0 );
			$field[ 'rgxp' ]      = ( isset( $field[ 'rgxp' ] ) ? $field[ 'rgxp' ] : null );
			$field[ 'value' ]     = isset( $cfgcache[ $fieldname ] ) ? (string)$cfgcache[ $fieldname ] : $field[ 'value' ];

			$attributes = Field::getFieldAttributes($field[ 'type' ]);

			$options = array ();
			foreach ( $attributes as $attribute )
			{
				if ( !empty( $current_settings[ $attribute ] ) )
				{
					$options[ $attribute ] = $current_settings[ $attribute ];
				}
			}

			$field[ 'options' ]           = serialize($options);
			$field_data                   = Field::getFieldDefinition($field);
			$field_data[ 'required' ]     = $field[ 'required' ];
			$field_data[ 'rgxp' ]         = ( $field[ 'rgxp' ] ? $field[ 'rgxp' ] : null );
			$field_data[ 'fieldid' ]      = $field[ 'id' ];
			$field_data[ 'value' ]        = $field[ 'value' ];
			$field_data[ 'field' ]        = Field::getFieldRender($field_data);
			$field_data[ 'field_label' ]  = ( $field[ 'grouplabel' ] ? $field[ 'grouplabel' ] : $field[ 'label' ] );
			$field_data[ 'description' ]  = $field[ 'description' ];
			$field_data[ 'fieldrequire' ] = $field[ 'fieldrequire' ];
			$field_data[ 'type' ]         = $field[ 'type' ];

			$_fields[ ] = $field_data;
		}


		$data                        = array ();
		$data[ 'configopts' ][ 'label' ] = $items[ 'label' ];
		$data[ 'configopts' ][ 'group' ] = $groupkey;
		$data[ 'configopts' ][ 'type' ]  = $type;

		foreach ( $_fields as $field )
		{
			if ( isset( $field[ 'rgxp' ][ 0 ] ) )
			{
				$field[ 'field' ] = str_replace('class="', 'class="' . $field[ 'rgxp' ][ 0 ] . ' ', $field[ 'field' ]);
			}

			$data[ 'configopts' ][ 'fields' ][ ] = array (
				'class'       => ( $field[ 'fieldrequire' ] ? 'req req-' . $field[ 'fieldrequire' ] : $field[ 'fieldid' ] ),
				'field_label' => $field[ 'field_label' ],
				'field'       => $field[ 'field' ],
				'type'        => $field[ 'type' ],
				'id'          => $field[ 'id' ],
				'description' => $field[ 'description' ]
			);
		}



      #
       #print_r($data);exit;


		// $isSeemodePopup = '<button class="action-button back" onclick="back()"><span></span> ' . trans('Zurück') . '</button>';


		Library::addNavi(trans('DreamCMS Einstellungen'));
		Library::addNavi(sprintf(trans('%s Einstellungen'), $items[ 'label' ]));

		$this->Template->process('settings/index', $data, true);
		exit;
	}

}
