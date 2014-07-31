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
 * @package      Asset
 * @version      3.0.0 Beta
 * @category     Config
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Asset_Config_Base
{

	/**
	 * @var array
	 */
	public static $controllerpermBackend = array (
		'edit'      => array (
			true,
			true
		),
		'publish'   => array (
			true,
			true
		),
		'unpublish' => array (
			true,
			true
		),
		'delete'    => array (
			true,
			true
		),
		'index'     => array (
			true,
			true
		)
	);

	/**
	 * @var array
	 */
	public static $controllerpermFrontend = array (
		'css' => array (
			false,
			false
		),
		'js'  => array (
			false,
			false
		),
		'swf' => array (
			false,
			false
		),
		'img' => array (
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
				'title'        => trans('Asset Verwaltung'),
				'access-items' => array (
					'index'   => array (
						trans('darf die Asset Verwaltung benutzen'),
						0
					),
					'delete'  => array (
						trans('darf Assets löschen'),
						0
					),
					'edit'    => array (
						trans('darf Assets hinzufügen'),
						0
					),
					'publish' => array (
						trans('darf Assets aktivieren/deaktivieren'),
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
	 *
	 *
	 * @return array
	 */
	public static function getModulDefinition ()
	{

		return array (
			'dockurl'           => 'admin.php?adm=asset',
			'modulelabel'       => trans('Medieninhalte (Assets)'),
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
			'label' => trans('Medieninhalte (Assets)'),
			'items' => array (
				array (
					'label'       => trans('Asset Übersicht'),
					'description' => null,
					'icon'        => null,
					'action'      => ''
				),
				array (
					'type' => 'separator'
				),
				array (
					'label'       => trans('Asset erstellen'),
					'description' => null,
					'icon'        => null,
					'action'      => 'edit'
				),
			)
		);

		Menu::addMenuItem('tools', 'asset', $menu);
	}

	/**
	 *
	 * @return array
	 */
	public static function getBackendMenu ()
	{

		return array (
			array (
				'title' => trans('Asset Verwaltung'),
				'items' => array (
					array (
						'label' => trans('Über Assets'),
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
						'label'    => trans('Assets beenden'),
						'call'     => 'closeApp',
						'shortcut' => 'CMD-E'
					)
				)
			),
			array (
				'title' => trans('Datei'),
				'items' => array (
					array (
						'label'  => trans('Neues Asset'),
						'action' => 'edit'
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
