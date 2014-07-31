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
 * @package      Menues
 * @version      3.0.0 Beta
 * @category     Config
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Menues_Config_Base
{

	/**
	 * @var array
	 */
	public static $controllerpermBackend = array (
		'editmenu'    => array (
			true,
			true
		),
		'edit'        => array (
			true,
			true
		),
		'add'         => array (
			true,
			true
		),
		'addmenu'     => array (
			true,
			true
		),
		'deletemenu'  => array (
			true,
			true
		),
		'save'        => array (
			true,
			false
		),
		'savemenu'    => array (
			true,
			false
		),
		'publish'     => array (
			true,
			true
		),
		'menupublish' => array (
			true,
			true
		),
		'delete'      => array (
			true,
			true
		),
		'index'       => array (
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
				'title'        => trans('Frontent Menü'),
				'hidden'       => 0,
				'access-items' => array (
					'index'       => array (
						trans('darf Menüs verwalten'),
						0
					),
					'addmenu'     => array (
						trans('darf Menüs erstellen'),
						0
					),
					'menupublish' => array (
						trans('darf Menüs aktivieren/deaktivieren'),
						0
					),
					'deletemenu'  => array (
						trans('darf Menüs löschen'),
						0
					), // menu Items
					'edit'        => array (
						trans('darf Menüpunkte bearbeiten'),
						0
					),
					'add'         => array (
						trans('darf Menüpunkte erstellen'),
						0
					),
					'editmenu'    => array (
						trans('darf Menü erstellen/bearbeiten'),
						0
					),
					'publish'     => array (
						trans('darf Menüpunkte aktivieren/deaktivieren'),
						0
					),
					'delete'      => array (
						trans('darf Menüpunkte löschen'),
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
	 * Used in Class SystemManager
	 *
	 * @return array
	 */
	public static function getModulDefinition ()
	{

		return array (
			'dockurl'           => 'admin.php?adm=menues',
			'modulelabel'       => trans('Menü Verwaltung'),
			'moduledescription' => null,
			'version'           => '0.1',
			'allowmetadata'     => false,
		);
	}

	/**
	 *
	 */
	public static function registerBackedMenu ()
	{

		$menu = array (
			'label'       => trans('Menü Verwaltung'),
			'description' => null,
			'icon'        => null,
			'action'      => ''
		);

		Menu::addMenuItem('content', 'menues', $menu);
	}

	/**
	 *
	 * @return array
	 */
	public static function getBackendMenu ()
	{

		return array (
			array (
				'title' => trans('Menü Verwaltung'),
				'items' => array (
					array (
						'label' => trans('Über Menü Verwaltung'),
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
						'label'    => trans('Menü Verwaltung beenden'),
						'call'     => 'closeApp',
						'shortcut' => 'CMD-E'
					)
				)
			),
			array (
				'title' => trans('Datei'),
				'items' => array (
					array (
						'label'  => trans('Neues Menü'),
						'action' => 'add'
					),
					array (
						'type' => 'line'
					),
					array (
						'title'       => trans('zuletzt bearbeitet...'),
						'dynamicItem' => true,
						'action'      => 'lastEdited',
						'call'        => 'gridRecent'
					)
				)
			),
			array (
				'title' => trans('Extras'),
				'items' => array (
					array (
						'label'  => trans('Cache leeren'),
						'action' => 'clearcache'
					)
				)
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
