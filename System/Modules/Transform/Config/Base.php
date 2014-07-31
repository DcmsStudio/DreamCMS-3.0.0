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
 * @package      Transform
 * @version      3.0.0 Beta
 * @category     Config
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Transform_Config_Base
{

	/**
	 * @var array
	 */
	public static $controllerpermBackend = array (
		'index'      => array (
			true,
			false
		),
		'edit'       => array (
			true,
			true
		),
		'editstep'   => array (
			true,
			false
		),
		'deletestep' => array (
			true,
			false
		),
		'stepparams' => array (
			true,
			false
		),
		'savestep'   => array (
			true,
			false
		),
		'preview'    => array (
			true,
			false
		),
		'save'       => array (
			true,
			false
		),
		'delete'     => array (
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
	 * @param bool $getBackend
	 * @return array|null
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
				'title'        => trans('Bild Transformation'),
				'hidden'       => 0,
				'access-items' => array (
					'index'  => array (
						trans('Bild Transformationen benutzen'),
						1
					),
					'edit'   => array (
						trans('Bild Transformation bearbeiten'),
						0
					),
					'delete' => array (
						trans('Bild Transformation löschen'),
						0
					)
				)
			);
		}
	}

	/**
	 *
	 */
	public static function registerBackedMenu ()
	{

		$menu = array (
			'label'       => trans('Bild Transformation'),
			'description' => null,
			'icon'        => null,
			'action'      => ''
		);

		Menu::addMenuItem('tools', 'transform', $menu);
	}

	/**
	 * Used in Class SystemManager
	 *
	 * @return array
	 */
	public static function getModulDefinition ()
	{

		return array (
			'dockurl'           => 'admin.php?adm=transform',
			'modulelabel'       => trans('Bild Transformation'),
			'moduledescription' => null,
			'version'           => '0.1',
			'allowmetadata'     => false,
		);
	}

}
