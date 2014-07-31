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
 * @package      Forms
 * @version      3.0.0 Beta
 * @category     Config
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Forms_Config_Base
{

	/**
	 * @var array
	 */
	public static $controllerpermBackend = array (
		'deletefield' => array (
			true,
			true
		),
		'deleteform'  => array (
			true,
			true
		),
		'options'     => array (
			true,
			true
		),
		'editfield'   => array (
			true,
			true
		),
		'editform'    => array (
			true,
			true
		),
		'fields'      => array (
			true,
			true
		),
		'index'       => array (
			true,
			false
		)
	);

	/**
	 * @var array
	 */
	public static $controllerpermFrontend = array (
		'send' => array (
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
				'title'        => trans('Formular Verarbeitung'),
				'hidden'       => 0,
				'access-items' => array (
					'editform'    => array (
						trans('Formulare bearbeiten'),
						0
					),
					'deleteform'  => array (
						trans('Formulare löschen'),
						0
					),
					'options'     => array (
						trans('options'),
						0
					),
					'fields'      => array (
						trans('Formularfelder auflisten'),
						0
					),
					'editfield'   => array (
						trans('Formularfelder hinzufügen/bearbeiten'),
						0
					),
					'deletefield' => array (
						trans('Formularfelder löschen'),
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
	 */
	public static function registerBackedMenu ()
	{

		$menu = array (
			'label' => trans('Formulare'),
			'items' => array (
				array (
					'label'       => trans('Formular Übersicht '),
					'description' => null,
					'icon'        => null,
					'action'      => ''
				),
				array (
					'label'       => trans('Profilfelder'),
					'description' => null,
					'icon'        => null,
					'action'      => 'fields'
				),
				array (
					'type' => 'separator'
				),
				array (
					'label'       => trans('Neues Formular'),
					'description' => null,
					'icon'        => null,
					'action'      => 'editform'
				)
			)
		);

		Menu::addMenuItem('layout', 'forms', $menu);
	}

	/**
	 *
	 * @return array
	 */
	public static function getBackendMenu ()
	{

		return array (
			array (
				'title' => trans('Formular'),
				'items' => array (
					array (
						'label' => trans('Über Formulare'),
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
						'label'    => trans('Formulare beenden'),
						'call'     => 'closeApp',
						'shortcut' => 'CMD-E'
					)
				)
			),
			array (
				'title' => trans('Datei'),
				'items' => array (
					array (
						'label'     => trans('Profilfelder'),
						'action'    => 'fields',
						'useWindow' => true
					),
					array (
						'type' => 'line'
					),
					array (
						'label'     => trans('Neues Formular'),
						'action'    => 'editform',
						'useWindow' => true
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

	/**
	 * Used in Class SystemManager
	 *
	 * @return array
	 */
	public static function getModulDefinition ()
	{

		return array (
			'dockurl'       => 'admin.php?adm=forms',
			'modulelabel'   => trans('Formular Verarbeitung'),
			'allowmetadata' => false,
			'version'       => '0.1',
			'metatables'    => array ()
		);
	}

}
