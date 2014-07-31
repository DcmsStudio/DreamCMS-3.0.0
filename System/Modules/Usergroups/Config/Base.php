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
 * @package      Usergroups
 * @version      3.0.0 Beta
 * @category     Config
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Usergroups_Config_Base
{

	/**
	 * @var array
	 */
	public static $controllerpermBackend = array (
		'edit'       => array (
			true,
			true
		),
		'add'        => array (
			true,
			true
		),
		'delete'     => array (
			true,
			true
		),
		'setdefault' => array (
			true,
			true
		),
		'dashaccess' => array (
			true,
			true
		), // 'list' => array(true, true),
		'index'      => array (
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
				'title'        => trans('Benutzergruppen'),
				'description'  => '',
				'hidden'       => 0,
				'access-items' => array (
					'index'      => array (
						trans('darf Benutzergruppen verwalten'),
						0
					),
					'setdefault' => array (
						trans('darf die Standart Benutzergruppe ändern'),
						0
					),
					'add'        => array (
						trans('darf Benutzergruppen hinzufügen'),
						0
					),
					'edit'       => array (
						trans('darf Benutzergruppen bearbeiten'),
						0
					),
					'delete'     => array (
						trans('darf Benutzergruppen löschen'),
						0
					),
					'dashaccess' => array (
						trans('darf die Backend Rechte von Benutzergruppen verwalten'),
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
			'dockurl'           => 'admin.php?adm=usergroups',
			'modulelabel'       => trans('Benutzergruppen'),
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

		$menu = array (
			'label' => trans('Benutzergruppen'),
			'items' => array (
				array (
					'label'       => trans('Benutzergruppen Übersicht '),
					'description' => null,
					'icon'        => null,
					'action'      => ''
				),
				array (
					'label'       => trans('Neue Benutzergruppe'),
					'description' => null,
					'icon'        => null,
					'action'      => 'add'
				),
			)
		);

		Menu::addMenuItem('user', 'usergroups', $menu);
	}

	/**
	 *
	 * @return array
	 */
	public static function getBackendMenu ()
	{

		return array (
			array (
				'title' => trans('Benutzergruppen'),
				'items' => array (
					array (
						'label' => trans('Über Benutzergruppen'),
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
						'label'    => trans('Benutzergruppen beenden'),
						'call'     => 'closeApp',
						'shortcut' => 'CMD-E'
					)
				)
			),
			array (
				'title' => trans('Neu'),
				'items' => array (
					array (
						'label'     => trans('Neue Benutzergruppe'),
						'action'    => 'add',
						'useWindow' => true
					),
					array (
						'type' => 'line'
					),
					array (
						'title'       => trans('zuletzt bearbeitet...'),
						'dynamicItem' => true,
						'action'      => 'lastEditedNews',
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
