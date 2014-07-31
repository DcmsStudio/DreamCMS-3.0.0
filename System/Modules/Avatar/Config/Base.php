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
 * @package      Avatar
 * @version      3.0.0 Beta
 * @category     Config
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Avatar_Config_Base
{

	/**
	 * @var array
	 */
	public static $controllerpermBackend = array (
		'edit'   => array (
			true,
			true
		),
		'add'    => array (
			true,
			true
		),
		'delete' => array (
			true,
			true
		),
		'index'  => array (
			true,
			true
		)
	);

	/**
	 * @var array
	 */
	public static $controllerpermFrontend = array ();

	/**
	 *
	 * @param boolean $getBackend
	 * @return array
	 */
	public static function getPermissions ( $getBackend = false )
	{

		if ( !$getBackend )
		{
			return null;
		}
		else
		{
			return array (
				'title'        => trans('Avatar Verwaltung'),
				'access-items' => array (
					'index'  => array (
						trans('Avatar Verwaltung benutzen'),
						0
					),
					'add'    => array (
						trans('Avatar hinzufügen'),
						0
					),
					'delete' => array (
						trans('Avatar löschen'),
						0
					),
					'edit'   => array (
						trans('Avatar bearbeiten'),
						0
					)
				)
			);
		}
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
	 *
	 */
	public static function registerBackedMenu ()
	{

		$menu = array (
			'label' => trans('Avatar Verwaltung'),
			'items' => array (
				array (
					'label'       => trans('Avatar Übersicht'),
					'description' => null,
					'icon'        => null,
					'action'      => ''
				),
				array (
					'type' => 'separator'
				),
				array (
					'label'       => trans('Avatar erstellen'),
					'description' => null,
					'icon'        => null,
					'action'      => 'add'
				),
			)
		);

		Menu::addMenuItem('custom', 'avatar', $menu);
	}

	/**
	 * Used in Class SystemManager
	 *
	 * @return array
	 */
	public static function getModulDefinition ()
	{

		return array (
			'dockurl'           => 'admin.php?adm=avatar',
			'modulelabel'       => trans('Avatar Verwaltung'),
			'allowmetadata'     => false,
			'moduledescription' => trans('Verwaltet Avatare der Benutzer.'),
			'version'           => '0.1',
			'metatables'        => array ()
		);
	}

}
