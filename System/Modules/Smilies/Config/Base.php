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
 * @package      
 * @version      3.0.0 Beta
 * @category     
 * @copyright    2008-2014 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Smilies_Config_Base
{

	/**
	 * @var array
	 */
	public static $controllerpermBackend = array (
		'index' => array (
			true,
			false
		)
	);

	/**
	 * @var array
	 */
	public static $controllerpermFrontend = array (
		'index' => array (
			false,
			false
		)
	);

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
			return null;
		}
		else
		{
			return array (
				'title'        => trans('Smilies'),
				'hidden'       => 0,
				'access-items' => array (
					'index' => array (
						trans('darf Smilies verwalten'),
						0
					),
					'edit'  => array (
						trans('darf Smilies bearbeiten'),
						0
					),
					'add'  => array (
						trans('darf Smilies hinzufügen'),
						0
					),
					'delete'  => array (
						trans('darf Smilies löschen'),
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
			'label'       => trans('Smilie Verwaltung'),
			'description' => null,
			'icon'        => null,
			'action'      => ''
		);

		Menu::addMenuItem('custom', 'smilies', $menu);
	}

	/**
	 * Used in Class SystemManager
	 *
	 * @return array
	 */
	public static function getModulDefinition ()
	{

		return array (
			'dockurl'           => 'admin.php?adm=smilies',
			'modulelabel'       => trans('Smilies'),
			'allowmetadata'     => false,
			'moduledescription' => trans('Smilie Verwaltung'),
			'version'           => '0.1',
			'metatables'        => array ()
		);
	}

}
