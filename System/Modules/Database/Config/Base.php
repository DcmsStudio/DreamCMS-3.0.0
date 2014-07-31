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
 * @package      Database
 * @version      3.0.0 Beta
 * @category     Config
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Database_Config_Base
{

	/**
	 * @var array
	 */
	public static $controllerpermBackend = array (
		'optimize' => array (
			true,
			true
		),
		'repaire'  => array (
			true,
			true
		), # 'query' => array(true, true),
		'index'    => array (
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
				'title'        => trans('Datenbank Tools'),
				'description'  => '',
				'hidden'       => 0,
				'access-items' => array (
					'index'    => array (
						trans('darf Datenbank verwalten'),
						0
					),
					'optimize' => array (
						trans('darf Tabellen optimieren'),
						0
					),
					'repaire'  => array (
						trans('darf Tabellen reparieren'),
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
            'label' => trans('Datenbank Tools'),
            'items' => array (
                array (
                    'label'       => trans('Datenbank Übersicht'),
                    'description' => null,
                    'icon'        => null,
                    'action'      => 'index'
                ),
                array (
                    'label'       => trans('Datenbank optimieren'),
                    'description' => null,
                    'icon'        => null,
                    'action'      => 'optimize',
                    'extraparams' => '&all=1',
                    'ajax'        => true
                ),
            )
        );

        Menu::addMenuItem('system', 'database', $menu);
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
			'dockurl'           => 'admin.php?adm=database',
			'modulelabel'       => trans('Datenbank Tools'),
			'allowmetadata'     => false,
			'moduledescription' => trans('Verwaltet die Datenbank und bietet diverse zusatz Tools.'),
			'version'           => '0.1',
			'metatables'        => array ()
		);
	}

}
