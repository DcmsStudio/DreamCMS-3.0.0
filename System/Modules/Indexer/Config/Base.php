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
 * @package      Indexer
 * @version      3.0.0 Beta
 * @category     Config
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Indexer_Config_Base
{

	/**
	 * @var array
	 */
	public static $controllerpermBackend = array (
		'createindex' => array (
			true,
			true
		),
		'index'       => array (
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
			return array (
				// Tab Label
				'tablabel'         => trans('Suchmaschiene'), // Bit Perms
				'cansubmitnews'    => array (
					'type'    => 'checkbox',
					'label'   => trans('kann Nachrichten hinzufügen'),
					'default' => 0
				),
				'cancommentnews'   => array (
					'type'    => 'checkbox',
					'label'   => trans('Kommentare zur Nachrichten hinzufügen'),
					'default' => 0
				),
				'maxcommentlength' => array (
					'type'    => 'text',
					'width'   => 20,
					'label'   => trans('maximale länge des Kommentars'),
					'require' => 'cancommentnews',
					'default' => 500
				)
			);
		}
		else
		{
			return array (
				'title'        => trans('Suchmaschiene'),
				'hidden'       => 0,
				'access-items' => array (
					'index'     => array (
						trans('darf Suchmaschiene benutzen'),
						0
					),
					'edit_news' => array (
						trans('darf Indexe erneuern'),
						0
					)
				)
			);
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
			'modulelabel'       => trans('Suchmaschiene'),
			'moduledescription' => '',
			'version'           => '0.1',
			'allowmetadata'     => false,
		);
	}

}
