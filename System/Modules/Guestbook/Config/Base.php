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
 * @package      Guestbook
 * @version      3.0.0 Beta
 * @category     Config
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Guestbook_Config_Base
{

	/**
	 * @var array
	 */
	public static $controllerpermBackend = array ();

	/**
	 * @var array
	 */
	public static $controllerpermFrontend = array (
		'index'        => array (
			false,
			false
		),
		'usergbook'    => array (
			false,
			false
		),
		'verify'       => array (
			false,
			false
		),
		'add'          => array (
			false,
			false
		),
		'addusergbook' => array (
			false,
			false
		),
		'publish'      => array (
			true,
			false
		),
		'remove'       => array (
			true,
			false
		),
	);
	/**
	 * @return array
	 */
	public static function getConfigItems ()
	{

		return array (
			'items' => array (
				'perpage'   => array (
					'label'       => trans( 'Anzahl der Einträge pro Seite' ),
					'type'        => 'select',
					'value'       => 20, // default per page
					'values'      => '5|5 ' . trans( 'Einträge' ) . '|
10|10 ' . trans( 'Einträge' ) . '|
15|15 ' . trans( 'Einträge' ) . '|
20|20 ' . trans( 'Einträge' ) . '|selected
25|25 ' . trans( 'Einträge' ) . '|
30|30 ' . trans( 'Einträge' ) . '|
35|35 ' . trans( 'Einträge' ) . '|
50|50 ' . trans( 'Einträge' ) . '|',
					'description' => trans( 'Geben Sie hier an, wieviel Einträge pro Seite angezeigt werden sollen.' ),
				),
				'allowbbcode'   => array (
					'label'       => trans('BBCodes erlauben'),
					'type'        => 'checkbox',
					'values'      => '1|' . trans('aktivieren') . '|checked',
					//'description' => trans('Geben Sie hier an, ob die Mitglieder Registrierung verfügbar sein soll.'),
				),
			)
		);
	}


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
	 * Used in Class SystemManager
	 *
	 * @return array
	 */
	public static function getModulDefinition ()
	{

		return array (
			'modulelabel'   => trans('Gästebuch'),
			'allowmetadata' => false,
			'version'       => '0.2',
			'metatables'    => array ()
		);
	}

}
