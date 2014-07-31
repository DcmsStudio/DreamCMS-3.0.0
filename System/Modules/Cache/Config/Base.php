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
 * @package      Cache
 * @version      3.0.0 Beta
 * @category     Config
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Cache_Config_Base
{

	/**
	 * @var array
	 */
	public static $controllerpermBackend = array (
		'clear'          => array (
			true,
			true
		),
		'clearfull'      => array (
			true,
			true
		),
		'clearpagecache' => array (
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
				'title'        => trans('Cache'),
				'access-items' => array (
					'clear'          => array (
						trans('Darf den Cache leeren'),
						0
					),
					'clearfull'      => array (
						trans('Darf den Cache komplett leeren'),
						0
					),
					'clearpagecache' => array (
						trans('Darf den Seiten Cache leeren'),
						0
					),
                    'clearassets' => array (
                        trans('Darf den Asset Cache leeren'),
                        0
                    ),
                    'clearthumbs' => array (
                        trans('Darf den Thubnail Cache leeren'),
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
			'label' => trans('System Cache'),
			'items' => array (
				array (
					'label'       => trans('Cache einfaches leeren'),
					'description' => null,
					'icon'        => null,
					'action'      => 'clear',
					'ajax'        => true
				),
				array (
					'label'       => trans('Cache vollständiges leeren'),
					'description' => null,
					'icon'        => null,
					'action'      => 'clearfull',
					'ajax'        => true
				),
                array(
                    'label'       => trans('Asset Cache leeren'),
                    'description' => null,
                    'icon'        => null,
                    'action'      => 'clearassets',
                    'ajax'        => true
                ),
                array(
                    'label'       => trans('Thumbnail Cache leeren'),
                    'description' => null,
                    'icon'        => null,
                    'action'      => 'clearthumbs',
                    'ajax'        => true
                ),
				array (
					'label'       => trans('Seiten Cache leeren'),
					'description' => null,
					'icon'        => null,
					'action'      => 'clearpagecache',
					'ajax'        => true
				),
			)
		);

		Menu::addMenuItem('system', 'cache', $menu);
	}

	/**
	 * Used in Class SystemManager
	 *
	 * @return array
	 */
	public static function getModulDefinition ()
	{

		return array (
			'modulelabel'       => trans('System Cache'),
			'moduledescription' => trans('System Cache Management'),
			'version'           => '0.1',
			'allowmetadata'     => false,
		);
	}

}
