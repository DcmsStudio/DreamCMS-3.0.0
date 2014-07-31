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
 * @category     Config
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Settings_Config_Base
{

	/**
	 * @var array
	 */
	public static $controllerpermBackend = array (
		'edit'  => array (
			true,
			true
		),
		'index' => array (
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
				'title'        => trans('System Einstellungen'),
				'description'  => trans('Achtung: Bitte diese Funktion nur dem Administrator zugängig machen!'),
				'hidden'       => 0,
				'access-items' => array (
					'index' => array (
						trans('darf die System Einstellungen ansehen'),
						0
					),
					'edit'  => array (
						trans('darf die System Einstellungen bearbeiten'),
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
			'isSingleWindow'    => true,
			'dockurl'           => 'admin.php?adm=settings',
			'modulelabel'       => trans('System Einstellungen'),
			'allowmetadata'     => true,
			'moduledescription' => null,
			'version'           => '0.1',
			'metatables'        => array ()
		);
	}

	/**
	 *
	 */
	public static function registerBackedMenu ()
	{

		Menu::addMenuItem('system', 'settings', array (
		                                              'type' => 'separator'
		                                        ));

		$menu = array (
			'label'       => trans('System Einstellungen'),
			'description' => null,
			'icon'        => null,
			'action'      => ''
		);

		Menu::addMenuItem('system', 'settings', $menu);
	}

	/**
	 *
	 * @return array
	 */
	public static function getBackendMenu ()
	{

		$groups = Dashboard_Config_Base::loadConfigOptions();

		$_arrs = array ();
		foreach ( $groups as $group => $r )
		{
			foreach ( $r as $key => $rs )
			{
				if ( $rs[ 'label' ] == '-' )
				{
					continue;
				}

				$_arrs[ ] = array (
					'controller' => $rs[ 'controller' ],
					'action'     => $rs[ 'action' ],
					'url'        => 'admin.php?adm=settings&action=edit&group=' . $group,
					'icon'       => false,
					'isCoreIcon' => false,
					'label'      => $rs[ 'label' ],
					'tip'        => ''
				);
			}
		}


		return array (
			array (
				'title' => trans('System Einstellungen'),
				'items' => array (
					array (
						'label' => trans('Über System Einstellungen'),
						'call'  => 'aboutApp'
					),
					array (
						'label'     => trans('System Einstellungen beenden'),
						'call'      => 'closeApp',
						'shortcut=' => 'CMD-E'
					)
				)
			),
			array (
				'title' => trans('Einstellungen'),
				'items' => $_arrs
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
