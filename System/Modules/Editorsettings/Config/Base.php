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
 * @package      Editorsettings
 * @version      3.0.0 Beta
 * @category     Config
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Editorsettings_Config_Base
{

	/**
	 * @var array
	 */
	public static $controllerpermBackend = array (
		'edit'   => array (
			true,
			true
		),
		'delete' => array (
			true,
			true
		),
		'save'   => array (
			true,
			false
		), // 'publish' => array(true, true),
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
				'title'        => trans('Inhalts Editor (TinyMCE)'),
				'access-items' => array (
					'index'  => array (
						trans('Inhalts Editor Einstellungen Verwalten'),
						0
					),
					'delete' => array (
						trans('Inhalts Editor Einstellungen löschen'),
						0
					),
					'edit'   => array (
						trans('Inhalts Editor Einstellungen bearbeiten'),
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
			'label'       => trans('Inhalts Editor (TinyMCE)'),
			'description' => null,
			'icon'        => null,
			'action'      => ''
		);

		Menu::addMenuItem('user', 'editorsettings', $menu);
		Menu::addMenuItem('user', 'editorsettings', array (
		                                                  'type' => 'separator'
		                                            ));
	}

	/**
	 * Used in Class SystemManager
	 *
	 * @return array
	 */
	public static function getModulDefinition ()
	{

		return array (
			'dockurl'           => 'admin.php?adm=editorsettings',
			'modulelabel'       => trans('Inhalts Editor (TinyMCE)'),
			'moduledescription' => 'Spezielle Einstellungen für den TinyMCE Editor',
			'version'           => '0.1',
			'allowmetadata'     => false,
		);
	}

	/**
	 *
	 * @return array
	 */
	public static function getBackendMenu ()
	{

		return array (
			array (
				'title' => trans('Inhalts Editor (TinyMCE)'),
				'items' => array (
					array (
						'label' => trans('Über Inhalts Editor (TinyMCE)'),
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
						'label'    => trans('Inhalts Editor beenden'),
						'call'     => 'closeApp',
						'shortcut' => 'CMD-E'
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
