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
 * @package      Component
 * @version      3.0.0 Beta
 * @category     Config
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Component_Config_Base
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
		'view'           => array (
			true,
			true
		),
		'savecategories' => array (
			true,
			false
		),
		'deletecategory' => array (
			true,
			true
		),
		'save'           => array (
			true,
			false
		),
		'addcategory'    => array (
			true,
			true
		),
		'category'       => array (
			true,
			true
		),
		'index'          => array (
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
				'title'        => trans('Komponenten'),
				'description'  => '',
				'hidden'       => 0,
				'access-items' => array (
					'index'          => array (
						trans('darf Komponenten verwalten'),
						0
					),
					'delete'         => array (
						trans('darf Komponenten hinzufügen'),
						0
					),
					'save'           => array (
						trans('darf Komponenten bearbeiten'),
						0
					),
					'addcategory'    => array (
						trans('darf Komponenten sperren/entsperren'),
						0
					),
					'category'       => array (
						trans('darf Komponenten-Kategorien verwalten'),
						0
					),
					'edit'           => array (
						trans('darf Komponenten-Kategorien bearbeiten'),
						0
					),
					'add'            => array (
						trans('darf Komponenten-Kategorien hinzufügen'),
						0
					),
					'deletecategory' => array (
						trans('darf Komponenten-Kategorien löschen'),
						0
					),
					'view'           => array (
						trans('darf Komponenten löschen'),
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
			'dockurl'           => 'admin.php?adm=component',
			'modulelabel'       => trans('Komponenten'),
			'allowmetadata'     => true,
			'moduledescription' => null,
			'version'           => '0.2',
			'license'           => 'GPL v2 <p>sdfjs jdf sdpf jnsdj fd fsd</p>sdf jnsd<p/> sdfjn on osadnf <p/>noasdjnfa dfg <br/>asdj nasdas jsda <p>hnhhhhi</p>djfm jsdf nsdv<p/><p/> sdx',
			'copyright'         => '(c) 2012-2013 by Marcel Domke',
			'metatables'        => array (),
			'modulactions'      => array (),
			'treeactions'       => array ()
		);
	}

	/**
	 *
	 */
	public static function registerBackedMenu ()
	{

		$menu = array (
			'label' => trans('Komponenten'),
			'items' => array (
				array (
					'label'       => trans('Komponenten Übersicht '),
					'description' => null,
					'icon'        => null,
					'action'      => ''
				),
				array (
					'label'       => trans('Komponente erstellen'),
					'description' => null,
					'icon'        => null,
					'action'      => 'edit'
				),
			)
		);

		Menu::addMenuItem('tools', 'component', $menu);
	}

	/**
	 *
	 * @return array
	 */
	public static function getBackendMenu ()
	{

		return array (
			array (
				'title' => trans('Komponenten'),
				'items' => array (
					array (
						'label' => trans('Über Komponenten'),
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
						'label'    => trans('Komponenten beenden'),
						'call'     => 'closeApp',
						'shortcut' => 'CMD-E'
					)
				)
			),
			array (
				'title' => trans('Datei'),
				'items' => array (
					array (
						'label'     => trans('Neue Komponente'),
						'action'    => 'add',
						'useWindow' => true
					),
					array (
						'label'     => trans('Neue Komponenten Kategorie'),
						'action'    => 'edit_news',
						'useWindow' => true
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
				'title' => trans('Extras'),
				'items' => array (
					array (
						'label'  => trans('Komponenten Cache leeren'),
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
