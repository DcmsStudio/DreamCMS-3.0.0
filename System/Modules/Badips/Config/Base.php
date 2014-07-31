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
 * @package      Badips
 * @version      3.0.0 Beta
 * @category     Config
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Badips_Config_Base
{

	/**
	 * @var
	 */
	private static $menutype;

	/**
	 * @var
	 */
	private static $_backendMenu;

	/**
	 * @var array
	 */
	public static $controllerpermBackend = array (
		'edit'    => array (
			true,
			true
		),
		'delete'  => array (
			true,
			true
		),
		'details' => array (
			true,
			false
		),
		'save'    => array (
			true,
			false
		),
		'publish' => array (
			true,
			true
		),
		'index'   => array (
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
				'title'        => trans('Bad-Ips'),
				'description'  => '',
				'hidden'       => 0,
				'access-items' => array (
					'index'   => array (
						trans('darf Bad-Ips verwalten'),
						0
					),
					'edit'    => array (
						trans('darf Bad-Ips bearbeiten'),
						0
					),
					'add'     => array (
						trans('darf Bad-Ips hinzufügen'),
						0
					),
					'publish' => array (
						trans('darf Bad-Ips aktivieren/deaktivieren'),
						0
					),
					'delete'  => array (
						trans('darf Bad-Ips löschen'),
						0
					)
				)
			);
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
			'dockurl'           => 'admin.php?adm=badips',
			'modulelabel'       => trans('Bad-Ips'),
			'moduledescription' => null,
			'version'           => '0.2',
			'allowmetadata'     => true,
			'metatables'        => array (),
			'modulactions'      => array (),
			'treeactions'       => array ()
		);
	}

	/**
	 *
	 */
	public static function registerBackedMenu ()
	{

		$menu = array (
			'label'       => trans('Bad-Ips Übersicht'),
			'description' => null,
			'icon'        => null,
			'action'      => ''
		);

		Menu::addMenuItem('tools', 'badips', $menu);
	}

	/**
	 *
	 * @return array
	 */
	public static function getBackendMenu ()
	{

		return array (
			array (
				'title' => trans('Bad-Ips'),
				'items' => array (
					array (
						'label' => trans('Über Bad-Ips'),
						'call'  => 'aboutApp'
					),
					array (
						'type' => 'line'
					),
					array (
						'label' => trans('Einstellungen'),
						'call'  => 'appSettings'
					),
					array (
						'type' => 'line'
					),
					array (
						'label'    => trans('Bad-Ips beenden'),
						'call'     => 'closeApp',
						'shortcut' => 'CMD-E'
					)
				)
			),
			array (
				'title' => trans('Datei'),
				'items' => array (
					array (
						'label'     => trans('Neuer Ip'),
						'action'    => 'edititem&appid=' . (int)HTTP::input('appid'),
						'id'        => 'appAddItem',
						'useWindow' => true
					),
					array (
						'type' => 'line'
					),
					array (
						'title'       => trans('zuletzt bearbeitet...'),
						'dynamicItem' => true,
						'action'      => 'lastEditedNews&appid=' . (int)HTTP::input('appid'),
						'call'        => 'gridRecent'
					)
				)
			),
			array (
				'title'       => trans('Ansicht'),
				'require'     => 'grid',
				'mode'        => 'grid',
				'dynamicItem' => true,
				'call'        => 'gridViewMode',
				'items'       => array ()
			),
			array (
				'title' => trans('Hilfe'),
				'items' => array (
					array (
						'label' => trans('Inhalt'),
						'call'  => 'help'
					),
					array (
						'label' => trans('Update'),
						'call'  => 'updateApp'
					)
				)
			)
		);
	}

}
