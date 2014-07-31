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
 * @package      Layouter
 * @version      3.0.0 Beta
 * @category     Config
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Layouter_Config_Base
{

	/**
	 * @var array
	 */
	public static $controllerpermBackend = array (
		'edit'         => array (
			true,
			true
		),
		'duplicate'    => array (
			true,
			true
		),
		'delete'       => array (
			true,
			true
		),
		'getcode'      => array (
			true,
			false
		),
		'gettemplate'  => array (
			true,
			false
		),
		'styler'       => array (
			true,
			false
		),
		'addblock'     => array (
			true,
			true
		),
		'editblock'    => array (
			true,
			true
		),
		'removeblock'  => array (
			true,
			true
		),
		'moveblock'    => array (
			true,
			true
		),
		'savesubcols'  => array (
			true,
			false
		),
		'renderlayout' => array (
			true,
			false
		),
		'index'        => array (
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
				'title'        => trans('Layouter'),
				'hidden'       => 0,
				'access-items' => array (
					'index'       => array (
						trans('darf Layouters verwalten'),
						0
					),
					'edit'        => array (
						trans('darf Layouters bearbeiten'),
						0
					),
					'delete'      => array (
						trans('darf Layouters löschen'),
						0
					),
					'duplicate'   => array (
						trans('darf Layouters duplizieren'),
						0
					),
					'addblock'    => array (
						trans('darf Layout-Blöcke erstellen'),
						0
					),
					'editblock'   => array (
						trans('darf Layout-Blöcke bearbeiten'),
						0
					),
					'moveblock'   => array (
						trans('darf Layout-Blöcke verschieben'),
						0
					),
					'removeblock' => array (
						trans('darf Layout-Blöcke löschen'),
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
			'dockurl'           => 'admin.php?adm=layouter',
			'modulelabel'       => trans('Layouter'),
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
			'label'       => trans('Layouter'),
			'description' => null,
			'icon'        => null,
			'action'      => ''
		);

		Menu::addMenuItem('layout', 'layouter', $menu);
	}

	/**
	 *
	 * @return array
	 */
	public static function getBackendMenu ()
	{

		return array (
			array (
				'title' => trans('Layouter'),
				'items' => array (
					array (
						'label' => trans('Über Layouter'),
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
						'label'    => trans('Layouter beenden'),
						'call'     => 'closeApp',
						'shortcut' => 'CMD-E'
					)
				)
			),
			array (
				'title' => trans('Datei'),
				'items' => array (
					array (
						'onBeforeCall' => 'getMenuUrlForID',
						'onAfterCall'  => null,
						'id'           => 'menu-add-layout',
						'label'        => trans('Neues Layouter erstellen'),
						'action'       => 'edit',
						'useWindow'    => true
					),
					array (
						'type' => 'line'
					),
					array (
						'title'       => trans('zuletzt bearbeitet...'),
						'dynamicItem' => true,
						'action'      => 'lastEditedNews',
						'call'        => 'gridRecent'
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
				'title' => trans('Extras'),
				'items' => array (
					array (
						'label'  => trans('Layout Cache leeren'),
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
