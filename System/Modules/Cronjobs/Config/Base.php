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
 * @package      Cronjobs
 * @version      3.0.0 Beta
 * @category     Config
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Cronjobs_Config_Base
{

	/**
	 * @var array
	 */
	public static $controllerpermBackend = array (
		'edit'    => array (
			true,
			true
		),
		'run'     => array (
			true,
			true
		),
		'publish' => array (
			true,
			true
		),
		'delete'  => array (
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
				'title'        => trans('Cron Jobs'),
				'access-items' => array (
					'index'   => array (
						trans('Darf Cron Jobs verwalten'),
						0
					),
					'edit'    => array (
						trans('Darf Cron Jobs bearbeiten'),
						0
					),
					'delete'  => array (
						trans('Darf Cron Jobs löschen'),
						0
					),
					'run'     => array (
						trans('Darf Cron Jobs ausführen'),
						0
					),
					'publish' => array (
						trans('Darf Cron Jobs aktivieren/deaktivieren'),
						0
					),
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
			'dockurl'           => 'admin.php?adm=cronjobs',
			'modulelabel'       => trans('Cron Jobs'),
			'moduledescription' => trans('Cron Job Management'),
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
			'label' => trans('Cron Jobs'),
			'items' => array (
				array (
					'label'       => trans('Cron Job Übersicht '),
					'description' => null,
					'icon'        => null,
					'action'      => ''
				),
				array (
					'label'       => trans('Cron Job hinzufügen'),
					'description' => null,
					'icon'        => null,
					'action'      => 'edit'
				),
			)
		);

		Menu::addMenuItem('system', 'cronjobs', $menu);
	}

	/**
	 *
	 * @return array
	 */
	public static function getBackendMenu ()
	{

		return array (
			array (
				'title' => trans('System Logs'),
				'items' => array (
					array (
						'label' => trans('Über Cron Job Management'),
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
						'label'    => trans('Cron Job Management beenden'),
						'call'     => 'closeApp',
						'shortcut' => 'CMD-E'
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
