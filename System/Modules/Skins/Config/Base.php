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
 * @package      Skins
 * @version      3.0.0 Beta
 * @category     Config
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Skins_Config_Base
{

	/**
	 * @var array
	 */
	public static $controllerpermBackend = array (
		'edit'           => array (
			true,
			true
		),
		'delete'         => array (
			true,
			true
		),
		'index'          => array (
			true,
			true
		),
		'add'            => array (
			true,
			true
		),
		'export'         => array (
			true,
			true
		),
		'import'         => array (
			true,
			true
		),
		'changepublish'  => array (
			true,
			true
		),
		'setdefault'     => array (
			true,
			true
		),
		'templates'      => array (
			true,
			false
		),
		'edittemplate'   => array (
			true,
			true
		),
		'renametemplate' => array (
			true,
			true
		),
		'regenerate'     => array (
			true,
			true
		),
		'delgroup'       => array (
			true,
			true
		),
		'deltemplate'    => array (
			true,
			true
		),
		'search'         => array (
			true,
			false
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
				'title'        => trans('Skins'),
				'description'  => '',
				'hidden'       => 0,
				'access-items' => array (
					'index'          => array (
						trans('darf Skins verwalten'),
						0
					),
					'addset'         => array (
						trans('erstellen'),
						0
					),
					'edit'           => array (
						trans('darf Skins bearbeiten'),
						0
					),
					'setdefault'     => array (
						trans('Standart Skin ändern'),
						0
					),
					/*
					'copy_templates' => array (
						'Templates kopieren',
						0
					),
					*/
					'delete'         => array (
						trans('Skins löschen'),
						0
					),
					'publish'        => array (
						trans('kann Skins aktivieren/deaktivieren'),
						0
					),
					'add'            => array (
						trans('Skins hinzufügen'),
						0
					),
					'changepublish'  => array (
						trans('Skins aktivieren/deaktivieren'),
						0
					),
					'delete'         => array (
						trans('Skins löschen'),
						0
					),
					'delgroup'       => array (
						trans('Template-Gruppen löschen'),
						0
					),
					'deltemplate'    => array (
						trans('Templates löschen'),
						0
					),
					'edittemplate'   => array (
						trans('Templates bearbeiten'),
						0
					),
					'export'         => array (
						trans('Templates exportieren'),
						0
					),
					'import'         => array (
						trans('Templates Importieren'),
						0
					),
					'regenerate'     => array (
						trans('Templates erneuern'),
						0
					),
					'renametemplate' => array (
						trans('Templates umbenennen'),
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
			'label'       => trans('Skins'),
			'description' => null,
			'icon'        => null,
			'action'      => ''
		);

		Menu::addMenuItem('layout', 'skins', $menu);
	}

	/**
	 * Used in Class SystemManager
	 *
	 * @return array
	 */
	public static function getModulDefinition ()
	{

		return array (
			'dockurl'           => 'admin.php?adm=skins',
			'modulelabel'       => trans('Skins'),
			'allowmetadata'     => false,
			'moduledescription' => trans('Verwaltet Frontend Skins an'),
			'version'           => '0.1',
			'metatables'        => array ()
		);
	}

}
