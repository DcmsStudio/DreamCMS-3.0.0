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
 * @package      Backup
 * @version      3.0.0 Beta
 * @category     Config
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Backup_Config_Base
{

	/**
	 * @var array
	 */
	public static $controllerpermBackend = array (
		'delete'           => array (
			true,
			true
		),
		'create'           => array (
			true,
			true
		),
		'download'         => array (
			true,
			true
		),
		'removeoldbackups' => array (
			true,
			true
		),
		'index'            => array (
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
				'title'        => trans('Backups'),
				'description'  => trans('Achtung: Bitte diese Funktion nur dem Administrator zugängig machen!'),
				'hidden'       => 0,
				'access-items' => array (
					'index'            => array (
						trans('darf Backups verwalten'),
						0
					),
					'create'           => array (
						trans('darf Backups erstellen'),
						0
					),
					'download'         => array (
						trans('darf Backups herunterladen'),
						0
					),
					'delete'           => array (
						trans('darf Backups löschen'),
						0
					),
					'removeoldbackups' => array (
						trans('darf alte Backups löschen'),
						0
					)
				)
			);
		}
	}

	/**
	 * @return array
	 */
	public static function getConfigItems ()
	{

		return array (
			'items' => array (
				'oldbackups' => array (
					'label'       => trans('Alter für alte Backups'),
					'type'        => 'text',
					'value'       => '86000',
					'maxlength'   => 11,
					'size'        => 50,
					'controls'    => true,
					'description' => trans('Geben Sie hier (in sekunden) an, ab wann ein Backup als alt gilt.'),
					'data-inputtrigger' => 'calctime',
				)
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
			'dockurl'           => 'admin.php?adm=backup',
			'modulelabel'       => trans('Backups'),
			'moduledescription' => trans('Verwaltet und legt Backups an'),
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
			'label' => trans('Backups'),
			'items' => array (
				array (
					'label'       => trans('Backup Übersicht'),
					'description' => null,
					'icon'        => null,
					'action'      => ''
				),
				array (
					'type' => 'separator'
				),
				array (
					'label'       => trans('Backup erstellen'),
					'description' => null,
					'icon'        => null,
					'action'      => 'create',
					'ajax'        => true
				),
			)
		);

		Menu::addMenuItem('system', 'backup', $menu);
	}

	/**
	 *
	 * @return array
	 */
	public static function getBackendMenu ()
	{

		return array (
			array (
				'title' => trans('Backups'),
				'items' => array (
					array (
						'label' => trans('Über Backups'),
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
						'label'    => trans('Backups beenden'),
						'call'     => 'closeApp',
						'shortcut' => 'CMD-E'
					)
				)
			),
			array (
				'title' => trans('Datei'),
				'items' => array (
					array (
						'label'  => trans('Neues Backup'),
						'action' => 'create'
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
				'title' => trans('Extras'),
				'items' => array (
					array (
						'label'  => trans('alte Backups löschen'),
						'action' => 'removeoldbackups',
						'ajax'   => true
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
