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
 * @package      Comments
 * @version      3.0.0 Beta
 * @category     Config
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Comments_Config_Base
{

	/**
	 * @var array
	 */
	public static $controllerpermBackend = array (
		'delete'  => array (
			true,
			true
		),
		'show'    => array (
			true,
			false
		),
		'edit'    => array (
			true,
			true
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
	public static $controllerpermFrontend = array (
		'save'  => array (
			false,
			false
		),
		'index' => array (
			false,
			false
		)
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
				'title'        => trans('Kommentare'),
				'hidden'       => 0,
				'access-items' => array (
					'index'   => array (
						trans('darf Kommentare verwalten'),
						1
					),
					'edit'    => array (
						trans('darf Kommentare bearbeiten'),
						0
					),
					'publish' => array (
						trans('darf Kommentare aktivieren/deaktivieren'),
						0
					),
					'delete'  => array (
						trans('darf Kommentare löschen'),
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
			'dockurl'           => 'admin.php?adm=comments',
			'modulelabel'       => trans('Kommentare'),
			'allowmetadata'     => false,
			'moduledescription' => trans('Verwaltet Kommentare'),
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
			'label' => trans('Kommentare'),
			'items' => array (
				array (
					'label'       => trans('Kommentare Übersicht'),
					'description' => null,
					'icon'        => null,
					'action'      => ''
				),
				array (
					'type' => 'separator'
				),
				array (
					'label'       => trans('neue Kommentare anzeigen'),
					'description' => null,
					'icon'        => null,
					'action'      => '',
					'extraparams' => 'mode=newest'
				),
			)
		);

		Menu::addMenuItem('content', 'comments', $menu);
	}

	/**
	 *
	 * @return array
	 */
	public static function getBackendMenu ()
	{

		return array (
			array (
				'title' => trans('Kommentare'),
				'items' => array (
					array (
						'label' => trans('Über Kommentare'),
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
						'label'    => trans('Kommentare beenden'),
						'call'     => 'closeApp',
						'shortcut' => 'CMD-E'
					)
				)
			),
			array (
				'title' => trans('Datei'),
				'items' => array (
					array (
						'label'  => trans('neue Kommentare anzeigen'),
						'action' => 'admin.php?adm=comments&mode=newest',
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
