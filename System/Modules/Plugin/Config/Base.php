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
 * @package      Plugin
 * @version      3.0.0 Beta
 * @category     Config
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Plugin_Config_Base
{

	/**
	 * @var array
	 */
	public static $controllerpermBackend = array (
		'index'     => array (
			true,
			true
		),
		'install'   => array (
			true,
			true
		),
		'uninstall' => array (
			true,
			true
		),
		'run'       => array (
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
			return array ();
		}
		else
		{
			return array (
				'title'        => trans('Pluginmanager'),
				'hidden'       => 0,
				'access-items' => array (
					'index'     => array (
						trans('darf Plugins verwalten'),
						0
					),
					'run'       => array (
						trans('darf Plugins ausführen'),
						0
					),
					'install'   => array (
						trans('darf Plugins installieren'),
						0
					),
					'uninstall' => array (
						trans('darf Plugins deinstallieren'),
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
			'dockurl'           => 'admin.php?adm=plugin',
			'modulelabel'       => trans('Pluginmanager'),
			'moduledescription' => trans('Verwaltet Plugins.'),
			'version'           => '0.1',
			'allowmetadata'     => false,
		);
	}

	/**
	 *
	 */
	public static function registerBackedMenu ()
	{

		$menu = array (
			'label'       => trans('Pluginmanager'),
			'description' => null,
			'icon'        => null,
			'action'      => ''
		);
		Menu::addMenuItem('plugin', 'plugin', $menu);


		Menu::addMenuItem('plugin', 'plugin', array (
		                                            'type' => 'separator'
		                                      ));

		Plugin::loadPluginPermissions(true);
		$plugins = Plugin::getInteractivePlugins();

		$_perms = Plugin::getPluginPerms();


		foreach ( $plugins as $key => $plugin )
		{
			$ucfirststr = ucfirst($key);
			if ( is_file(PLUGIN_PATH . $ucfirststr . '/Config/Base.php') )
			{
				include_once(PLUGIN_PATH . $ucfirststr . '/Config/Base.php');

				$className = 'Addon_' . $ucfirststr . '_Config_Base';

				if ( class_exists($className, false) && checkClassMethod($className . '/getControllerPermissions', 'static') )
				{
					$perm = call_user_func($className . '::getControllerPermissions', true);
					if ( is_array($perm) )
					{
						$tmp = array ();
						foreach ( $perm as $action => $value )
						{
							$tmp[ $action ] = array (
								'requirelogin'      => ($value[ 0 ] ? true : false),
								'requirepermission' => ($value[ 1 ] ? true : false)
							);
						}
						unset($perm);

						Menu::registerPluginPerms($key, $tmp);
					}
				}


				if ( class_exists($className, false) && checkClassMethod($className . '/registerBackedMenu', 'static') )
				{
					call_user_func($className . '::registerBackedMenu');
				}
			}
		}
	}

	/**
	 *
	 * @return array
	 */
	public static function getBackendMenu ()
	{

		return array (
			array (
				'title' => trans('Pluginmanager'),
				'items' => array (
					array (
						'label' => trans('Über Pluginmanager'),
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
						'label'    => trans('Pluginmanager beenden'),
						'call'     => 'closeApp',
						'shortcut' => 'CMD-E'
					)
				)
			),
			array (
				'title' => trans('Datei'),
				'items' => array (
					array (
						'label'     => trans('Neues Plugin installieren'),
						'action'    => 'install',
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
