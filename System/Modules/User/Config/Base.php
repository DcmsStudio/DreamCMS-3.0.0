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
 * @package      User
 * @version      3.0.0 Beta
 * @category     Config
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class User_Config_Base
{

	/**
	 * @var array
	 */
	public static $controllerpermBackend = array (
		'add'        => array (
			true,
			true
		),
		'edit'       => array (
			true,
			true
		),
		'blocking'   => array (
			true,
			true
		),
		'unblocking' => array (
			true,
			true
		),
		'access'     => array (
			true,
			true
		),
		'delete'     => array (
			true,
			true
		),
		'email'      => array (
			true,
			true
		),
		'activate'   => array (
			true,
			true
		), //'find' => array(true, true),
		'index'      => array (
			true,
			true
		)
	);

	/**
	 * @var array
	 */
	public static $controllerpermFrontend = array (
		'index'      => array (
			false,
			false
		),
		'checkuser'  => array (
			false,
			false
		),
		'getprofile' => array (
			false,
			false
		),
		'verify'     => array (
			false,
			false
		),
		'password'   => array (
			true,
			false
		),
		'settings'   => array (
			true,
			false
		),
		'avatar'     => array (
			true,
			true
		),
		'signatur'   => array (
			true,
			true
		),
		'other'      => array (
			true,
			false
		),
	);

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
				'title'        => trans('Benutzer'),
				'description'  => '',
				'hidden'       => 0,
				'access-items' => array (
					'index'      => array (
						trans('darf Benutzer verwalten'),
						0
					),
					'add'        => array (
						trans('darf Benutzer hinzufügen'),
						0
					),
					'edit'       => array (
						trans('darf Benutzer bearbeiten'),
						0
					),
					'blocking'   => array (
						trans('darf Benutzer sperren'),
						0
					),
					'delete'     => array (
						trans('darf Benutzer löschen'),
						0
					),
					'access'     => array (
						trans('darf Benutzern Spezielle Rechte zuweisen/diese verwalten'),
						0
					),
					'activate'   => array (
						trans('darf Benutzer aktivieren'),
						0
					),
					'email'      => array (
						trans('darf Benutzern Emails senden'),
						0
					),
					'unblocking' => array (
						trans('darf Benutzer entsperren'),
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
			'dockurl'           => 'admin.php?adm=user',
			'modulelabel'       => trans('Benutzer'),
			'allowmetadata'     => false,
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
			'label' => trans('Benutzer'),
			'items' => array (
				array (
					'label'       => trans('Benutzer Übersicht '),
					'description' => null,
					'icon'        => null,
					'action'      => ''
				),
				array (
					'label'       => trans('neuen Benutzer anlegen'),
					'description' => null,
					'icon'        => null,
					'action'      => 'add'
				),
			)
		);

		Menu::addMenuItem('user', 'user', $menu);
	}

	/**
	 *
	 * @return array
	 */
	public static function getBackendMenu ()
	{

		return array (
			array (
				'title' => trans('Benutzer'),
				'items' => array (
					array (
						'label' => trans('Über Benutzer'),
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
						'label'    => trans('Benutzer beenden'),
						'call'     => 'closeApp',
						'shortcut' => 'CMD-E'
					)
				)
			),
			array (
				'title' => trans('Aufgabe'),
				'items' => array (
					array (
						'label'     => trans('Benutzer erstellen'),
						'action'    => 'add',
						'useWindow' => true
					),
					array (
						'type' => 'line'
					),
					array (
						'title'       => trans('zuletzt bearbeitet...'),
						'dynamicItem' => true,
						'action'      => 'lastEdited',
						'call'        => 'gridRecent'
					),
					array (
						'type' => 'line'
					),
					array (
						'label'  => trans('Email an Benutzer'),
						'action' => 'email'
					),
					array (
						'label'  => trans('Blockierte Benutzer anzeigen'),
						'action' => '',
						'call'   => 'gridActionCall'
					),
					array (
						'label'  => trans('Administratoren anzeigen'),
						'action' => '',
						'call'   => 'gridActionCall'
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
