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
 * @package      Eventmanager
 * @version      3.0.0 Beta
 * @category     Config
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Eventmanager_Config_Base
{

	/**
	 * @var array
	 */
	public static $controllerpermBackend = array (
		'savehookorder'       => array (
			true,
			true
		),
		'component'           => array (
			true,
			true
		),
		'removecomponenthook' => array (
			true,
			true
		),
		'add'                 => array (
			true,
			true
		),
		'edit'                => array (
			true,
			true
		),
		'synchooks'           => array (
			true,
			true
		),
		'scanevents'          => array (
			true,
			true
		),
		'index'               => array (
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
			return array ();
		}
		else
		{
			return array (
				'title'        => trans('Eventmanager'),
				'hidden'       => 0,
				'access-items' => array (
					'component'           => array (
						trans('darf Komponenten '),
						0
					),
					'savehookorder'       => array (
						trans('darf Hook-Sortierung ändern'),
						0
					),
					'removecomponenthook' => array (
						trans('darf Component Hooks löschen'),
						0
					),
					'add'                 => array (
						trans('darf hinzufügen'),
						0
					),
					'edit'                => array (
						trans('darf bearbeiten'),
						0
					),
					'synchooks'           => array (
						trans('darf Hooks syncronisieren'),
						0
					),
					'scanevents'          => array (
						trans('darf neue Events Scannen'),
						0
					),
					'index'               => array (
						trans('darf Eventmanager verwenden'),
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
			'dockurl'           => 'admin.php?adm=eventmanager',
			'modulelabel'       => trans('Eventmanager'),
			'moduledescription' => '',
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
			'label'       => trans('Eventmanager'),
			'description' => null,
			'icon'        => null,
			'action'      => ''
		);

		Menu::addMenuItem('tools', 'eventmanager', $menu);
	}

	/**
	 *
	 * @return array
	 */
	public static function getBackendMenu ()
	{

		return array (
			array (
				'title' => trans('Eventmanager'),
				'items' => array (
					array (
						'label' => trans('Über Eventmanager'),
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
						'label'    => trans('Eventmanager beenden'),
						'call'     => 'closeApp',
						'shortcut' => 'CMD-E'
					)
				)
			),
			array (
				'title' => trans('Aktion'),
				'items' => array (
					array (
						'label'       => trans('Scanne System Ereignisse'),
						'action'      => 'scanevents&mode=system',
						'id'          => 'scaneventsSystem',
						'ajax'        => true,
						'onAfterCall' => 'eventManagerSetMessage'
					),
					array (
						'label'       => trans('Scanne Plugin Ereignisse'),
						'action'      => 'scanevents&mode=plugin',
						'id'          => 'scaneventsPlugin',
						'ajax'        => true,
						'onAfterCall' => 'eventManagerSetMessage'
					),
					array (
						'label'  => trans('Sync Hooks'),
						'action' => 'synchooks',
						'id'     => 'syncHooks',
						'ajax'   => true
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
