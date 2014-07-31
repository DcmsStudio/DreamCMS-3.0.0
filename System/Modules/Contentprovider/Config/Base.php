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
 * @package      Contentprovider
 * @version      3.0.0 Beta
 * @category     Config
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Contentprovider_Config_Base
{

	/**
	 * @var array
	 */
	public static $controllerpermBackend = array (
		'index'     => array (
			true,
			true
		),
		'add'       => array (
			true,
			true
		),
		'edit'      => array (
			true,
			true
		),
		'check'     => array (
			true,
			false
		),
		'save'      => array (
			true,
			false
		),
		'delete'    => array (
			true,
			true
		),
		'order'     => array (
			true,
			true
		),
		'saveorder' => array (
			true,
			true
		),
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
				'title'        => trans('Contentprovider/CoreTags'),
				'access-items' => array (
					'index'  => array (
						trans('Contentprovider/CoreTags Verwalten'),
						0
					),
					'add'    => array (
						trans('Contentprovider/CoreTags hinzufügen'),
						0
					),
					'delete' => array (
						trans('Contentprovider/CoreTags löschen'),
						0
					),
					'edit'   => array (
						trans('Contentprovider/CoreTags bearbeiten'),
						0
					),
					'order'  => array (
						trans('Contentprovider/CoreTags umsortieren'),
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
			'label' => trans('Contentprovider'),
			'items' => array (
				array (
					'label'       => trans('Contentprovider Übersicht '),
					'description' => null,
					'icon'        => null,
					'action'      => ''
				),
				array (
					'label'       => trans('Contentprovider/CoreTag hinzufügen'),
					'description' => null,
					'icon'        => null,
					'action'      => 'add'
				),
			)
		);

		Menu::addMenuItem('tools', 'contentprovider', $menu);
	}

	/**
	 * Used in Class SystemManager
	 *
	 * @return array
	 */
	public static function getModulDefinition ()
	{

		return array (
			'dockurl'           => 'admin.php?adm=contentprovider',
			'modulelabel'       => trans('Contentprovider'),
			'allowmetadata'     => false,
			'moduledescription' => trans('Verwaltet Contentprovider.'),
			'version'           => '0.1',
			'metatables'        => array ()
		);
	}

}
