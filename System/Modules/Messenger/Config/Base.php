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
 * @package      Messenger
 * @version      3.0.0 Beta
 * @category     Config
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Messenger_Config_Base
{

	/**
	 * @var array
	 */
	public static $controllerpermBackend = array (
		'index'        => array (
			true,
			true
		),
		'write'        => array (
			true,
			true
		),
		'usersearch'   => array (
			true,
			false
		),
		'send'         => array (
			true,
			false
		),
		'createfolder' => array (
			true,
			true
		),
		'deletefolder' => array (
			true,
			true
		),
		'renamefolder' => array (
			true,
			false
		),
		'mark'         => array (
			true,
			false
		),
		'receipt'      => array (
			true,
			false
		),
		'move'         => array (
			true,
			true
		),
		'empty'        => array (
			true,
			true
		),
		'view'         => array (
			true,
			false
		)
	);

	/**
	 * @var array
	 */
	public static $controllerpermFrontend = array ();

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
	 * @param bool $getBackend
	 * @return array
	 */
	public static function getPermissions ( $getBackend = false )
	{

		if ( !$getBackend )
		{
			return array (
				// Tab Label
				'tablabel'         => trans('Private Nachrichten'), // Bit Perms
				'index'            => array (
					'type'        => 'checkbox',
					'label'       => trans('kann Private Nachrichten verwenden'),
					'default'     => 0,
					'isActionKey' => true
				),
				'maxpms'           => array (
					'require' => 'index',
					'type'    => 'text',
					'width'   => 20,
					'label'   => trans('maximale Anzahl privaten Nachrichten'),
					'default' => 100
				),
				'cancreatefolders' => array (
					'require' => 'index',
					'type'    => 'checkbox',
					'label'   => trans('darf eigene Ordner anlegen'),
					'default' => 0
				),
				'maxfolders'       => array (
					'require' => 'cancreatefolders',
					'type'    => 'text',
					'width'   => 20,
					'label'   => trans('maximale Anzahl der Ordner'),
					'require' => 'cancreatefolders',
					'default' => 3
				),
			);
		}
		else
		{
			return array (
				'title'        => trans('Private Nachrichten'),
				'hidden'       => 0,
				'access-items' => array (
					'index'        => array (
						trans('darf Private Nachrichten benutzen'),
						1
					),
					'write'        => array (
						trans('darf Private Nachrichten versenden'),
						0
					),
					'createfolder' => array (
						trans('darf Ordner erstellen'),
						0
					),
					'deletefolder' => array (
						trans('darf Ordner löschen'),
						0
					),
					'move'         => array (
						trans('darf Private Nachrichten verschieben'),
						0
					),
					'empty'        => array (
						trans('darf Posteingang leeren'),
						0
					),
				)
			);
		}
	}

	/**
	 *
	 */
	public static function registerBackedMenu ()
	{

		$menu = array (
			'label'       => trans('Private Nachrichten'),
			'description' => null,
			'icon'        => null,
			'action'      => ''
		);

		Menu::addMenuItem('home', 'messenger', $menu);
	}

	/**
	 * Used in Class SystemManager
	 *
	 * @return array
	 */
	public static function getModulDefinition ()
	{

		return array (
			'dockurl'           => 'admin.php?adm=messenger',
			'modulelabel'       => trans('Private Nachrichten'),
			'allowmetadata'     => false,
			'moduledescription' => null,
			'version'           => '0.2',
			'metatables'        => array ()
		);
	}

}
