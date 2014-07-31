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
 * @package      Register
 * @version      3.0.0 Beta
 * @category     Config
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Register_Config_Base
{

	/**
	 * @var array
	 */
	public static $controllerpermBackend = array ();

	/**
	 * @var array
	 */
	public static $controllerpermFrontend = array (
		'index'     => array (
			false,
			false
		),
		'verify'    => array (
			false,
			false
		),
		'checkuser' => array (
			false,
			false
		),
	);

	/**
	 *
	 * @param bool $getBackend default false
	 * @return array
	 */
	public static function getControllerPermissions ( $getBackend = false )
	{

		if ( !$getBackend )
		{
			return self::$controllerpermFrontend;
		}
		else
		{
			return self::$controllerpermBackend;
		}
	}

	/**
	 * @return array
	 */
	public static function getConfigItems ()
	{

		return array (
			'items' => array (
				'allowregister'   => array (
					'label'       => trans('Mitglieder Registrierung aktivieren'),
					'type'        => 'checkbox',
					'values'      => '1|' . trans('aktivieren') . '|checked',
					'description' => trans('Geben Sie hier an, ob die Mitglieder Registrierung verfügbar sein soll.'),
				),
				'emailverifymode' => array (
					'label'       => trans('Registrierungsverhalten'),
					'type'        => 'radio',
					'value'       => 0,
					'values'      => '0|' . trans('sofort Freischalten') . '|
1|' . trans('Email mit einem Link mit dem er seinen Account Aktivieren muss') . '|
2|' . trans('Benutzer soll vorher erst geprüft werden') . '|
3|' . trans('Email mit einem generierten Passwort an Benutzer senden (sofort Freischalten)') . '|',
					'description' => false,
                    'fieldrequire' => 'allowregister'
				),

                'multipleemailuse' => array(
                    'label'       => trans('Mehrfache Benutzung einer eMail Adresse'),
                    'type'        => 'checkbox',
                    'values'      => '1|' . trans('aktivieren') . '|checked',
                    'description' => trans('Darf eine eMail Adresse mehrfach bei der Registrierung verwendet werden? Dies hätte zur Folge, dass eine eMail Adresse von mehreren Benutzern benutzt werden könnte.'),
                    'fieldrequire' => 'allowregister'
                ),
                'minusernamelength' => array(
                    'label' => trans('minimale Benutzernamenlänge'),
                    'rgxp' => array('integer', trans('Der Wert darf nur aus Zahlen bestehen')),
                    'type' => 'text', 'value' => '4', 'maxlength' => 2, 'size' => 30, 'controls' => true,
                    'description' => trans('Geben Sie hier an, wie lang ein Benutzername mindestens sein muß. Diese Einstellung gilt auch für die Benutzernamen von Gästen bei der Erstellung von Beiträgen.'),
                    'fieldrequire' => 'allowregister'
                ),
                'maxusernamelength' => array(
                    'label' => trans('maximale Benutzernamenlänge'),
                    'rgxp' => array('integer', trans('Der Wert darf nur aus Zahlen bestehen')),
                    'type' => 'text', 'value' => '20', 'maxlength' => 2, 'size' => 30, 'controls' => true,
                    'description' => trans('Geben Sie hier an, wie lang ein Benutzername maximal sein darf. Diese Einstellung gilt auch für die Benutzernamen von Gästen bei der Erstellung von Beiträgen.'),
                    'fieldrequire' => 'allowregister'
                ),
			)
		);
	}

	/**
	 * Used in Class SystemManager
	 *
	 * @return array
	 */
	public static function getModulDefinition ()
	{

		return array (
			'modulelabel'       => trans('Registrierung'),
			'allowmetadata'     => false,
			'moduledescription' => null,
			'version'           => '0.3',
			'metatables'        => array ()
		);
	}

}
