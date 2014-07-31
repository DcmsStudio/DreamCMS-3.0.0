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
 * @package      Fileman
 * @version      3.0.0 Beta
 * @category     Config
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Fileman_Config_Base
{

	/**
	 * @var array
	 */
	public static $controllerpermBackend = array (
		'edit'   => array (
			true,
			true
		),
		'delete' => array (
			true,
			true
		),
		'save'   => array (
			true,
			false
		), // 'publish' => array(true, true),
		'index'  => array (
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
				'title'        => trans('Dateimanager'),
				'access-items' => array (
					'index'  => array (
						trans('darf den Dateimanager benutzen'),
						0
					),
					'delete' => array (
						trans('darf Dateien/Ordner löschen'),
						0
					),
					'edit'   => array (
						trans('darf Dateien bearbeiten'),
						0
					)
				)
			);
		}
	}


	/**
	 * @return Fileman_Helper_Index
	 */
	public static function getIndexerInstance ()
	{

		return Application::getIndexerInstance();
	}


	public static function bindEvents ()
	{


		if ( Application::isBackend() )
		{
            $event = Registry::getObject('Event');

            $event->bindevent('upload.fileman', function ( $dirpath, $filename = null )
			{

				Fileman_Config_Base::getIndexerInstance()->updateIndexFromPath($dirpath);
			});

			// set only state trash
            $event->bindevent('delete.fileman', function ( $path, $hash = null )
			{
				if ($hash)
				{
					Fileman_Config_Base::getIndexerInstance()->removeIndexByHash( $hash);
				}
				else
				{
					Fileman_Config_Base::getIndexerInstance()->removeFromIndex($path, $hash);
				}
			});

			// update metadata
            $event->bindevent('update.fileman', function ( $dirpath, $filename = null )
			{

				Fileman_Config_Base::getIndexerInstance()->updateIndexFromPath($dirpath);
			});


			// rename file or directory
            $event->bindevent('rename.fileman', function ( $dirpath, $target, $name )
			{

				Fileman_Config_Base::getIndexerInstance()->updateIndexFromPath($dirpath, false, $target, $name);
			});


			//
            $event->bindevent('move.fileman', function ( $dirpath, $filename = null )
			{

				Fileman_Config_Base::getIndexerInstance()->updateIndexFromPath($dirpath, true);
			});

            $event->bindevent('extract.fileman', function ( $dirpath, $filename = null )
			{

				Fileman_Config_Base::getIndexerInstance()->updateIndexFromPath($dirpath, true);
			});

            $event->bindevent('makedir.fileman', function ( $dirpath, $filename = null )
			{

				Fileman_Config_Base::getIndexerInstance()->updateIndexFromPath($dirpath);
			});

            $event->bindevent('makefile.fileman', function ( $dirpath, $filename = null )
			{

				Fileman_Config_Base::getIndexerInstance()->updateIndexFromPath($dirpath);
			});
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
			'dockurl'           => 'admin.php?adm=fileman',
			'modulelabel'       => trans('Dateimanager'),
			'moduledescription' => '',
			'version'           => '0.0.1',
			'allowmetadata'     => false,
		);
	}

	/**
	 *
	 */
	public static function registerBackedMenu ()
	{

		$menu = array (
			'label'       => trans('Dateimanager'),
			'description' => null,
			'icon'        => null,
			'action'      => ''
		);

		Menu::addMenuItem('tools', 'fileman', $menu);
	}

	/**
	 *
	 * @return array
	 */
	public static function getBackendMenu ()
	{

		return array (
			array (
				'title' => trans('Dateimanager'),
				'items' => array (
					array (
						'label' => trans('Über Dateimanager'),
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
						'label'    => trans('Dateimanager beenden'),
						'call'     => 'closeApp',
						'shortcut' => 'CMD-E'
					)
				)
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
