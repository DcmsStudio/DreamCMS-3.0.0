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
 * @package      Config
 * @version      3.0.0 Beta
 * @category     Config
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Modules_Config_Base
{

	/**
	 * @var array
	 */
	public static $controllerpermBackend = array (
		'settings'        => array (
			true,
			true
		),
		'update'          => array (
			true,
			true
		),
		'uninstall'       => array (
			true,
			true
		),
		'createplugin'    => array (
			true,
			true
		),
		'publish'         => array (
			true,
			true
		),
		'registryrefresh' => array (
			true,
			true
		),
		'index'           => array (
			true,
			true
		)
	);

	/**
	 * @var array
	 */
	public static $controllerpermFrontend = array (
		'index' => array (
			false,
			false
		)
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
	 *
	 *
	 * @return array
	 */
	public static function getModulDefinition ()
	{

		return array (
			'dockurl'           => 'admin.php?adm=modules',
			'modulelabel'       => trans('Modulverwaltung'),
			'allowmetadata'     => false,
			'moduledescription' => null,
			'version'           => '0.1',
			'metatables'        => array ()
		);
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

			return array (
				'title'        => trans('Module'),
				'access-items' => array (
					'index' => array (
						trans('darf Module verwenden'),
						1
					)
				)
			);
		}
		else
		{
			return array (
				'title'        => trans('Module'),
				'access-items' => array (
					'index'           => array (
						trans('darf Modul-Verwaltung verwenden'),
						1
					),
					'registryrefresh' => array (
						trans('darf Modul-Registy aktualisieren'),
						0
					),
					'publish'         => array (
						trans('darf Module aktivieren/deaktivieren'),
						0
					),
					'settings'        => array (
						trans('darf die Module-Einstellungen bearbeiten'),
						0
					),
					'uninstall'       => array (
						trans('darf Module deinstallieren'),
						0
					),
					'update'          => array (
						trans('darf Module aktualisieren'),
						0
					)
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
			'label' => trans('Module'),
			'items' => array (
				array (
					'label'       => trans('Übersicht der Module'),
					'description' => null,
					'icon'        => null,
					'action'      => '',
					'extraparams' => '',
				),
				array (
					'label'       => trans('weitere Module installieren'),
					'description' => null,
					'icon'        => null,
					'action'      => 'install',
					'extraparams' => ''
				),
				array (
					'label'       => trans('Modul-Registy aktualisieren'),
					'description' => null,
					'icon'        => null,
					'action'      => 'registryrefresh',
					'extraparams' => '',
					'ajax'        => true
				),
			)
		);

		Menu::addMenuItem('system', 'modules', $menu);
	}

	/**
	 *
	 * @return array
	 */
	public static function getBackendMenu ()
	{

		return array (
			array (
				'title' => trans('Module'),
				'items' => array (
					array (
						'label' => trans('Über Module'),
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
						'label'    => trans('Module beenden'),
						'call'     => 'closeApp',
						'shortcut' => 'CMD-E'
					)
				)
			),
			array (
				'title' => trans('Datei'),
				'items' => array (
					array (
						'label'     => trans('Modul installieren'),
						'action'    => 'install',
						'useWindow' => true
					),
					array (
						'label'  => trans('Modul-Registy aktualisieren'),
						'action' => 'registryrefresh',
						'ajax'   => true
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
