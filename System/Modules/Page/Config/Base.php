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
 * @package      Page
 * @version      3.0.0 Beta
 * @category     Config
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Page_Config_Base
{

	/**
	 * @var array
	 */
	public static $controllerpermBackend = array (
		'add'            => array (
			true,
			true
		),
		'edit'           => array (
			true,
			true
		),
		'delete'         => array (
			true,
			true
		),
		'publish'        => array (
			true,
			true
		),
		'versions'       => array (
			true,
			true
		),
		'preview_backup' => array (
			true,
			true
		),
		'restore'        => array (
			true,
			true
		),
		'delete_backup'  => array (
			true,
			true
		),
		'reload_page'    => array (
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
		'create_index'   => array (
			true,
			true
		),
		'diff'           => array (
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
	public static $controllerpermFrontend = array (
		'index' => array (
			false,
			false
		)
	);

	/**
	 *
	 */
	public static function getCoreFields ()
	{

		return array (
			'page'          => array (
				'title',
				'content',
				'tags'
			),
			'documentation' => array (
				'title',
				'content',
				'tags',
				'categorie'
			),
			'product'       => array (
				'title',
				'content',
				'tags',
				'categorie'
			),
			'movie'         => array (
				'title',
				'content',
				'tags',
				'categorie'
			),
			'audio'         => array (
				'title',
				'content',
				'tags',
				'categorie'
			),
			'portfolio'     => array (
				'title',
				'content',
				'tags'
			),
		);
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
				'tablabel'         => trans('Statische Seite'), // Bit Perms
				'cansubmitpagess'  => array (
					'type'    => 'checkbox',
					'label'   => trans('kann Seiten hinzufügen'),
					'default' => 0
				),
				'cancommentpages'  => array (
					'type'    => 'checkbox',
					'label'   => trans('Kommentare zur Seiten hinzufügen'),
					'default' => 0
				),
				'maxcommentlength' => array (
					'type'    => 'text',
					'width'   => 20,
					'label'   => trans('maximale länge des Kommentars'),
					'require' => 'cancommentpages',
					'default' => 500
				)
			);
		}
		else
		{
			return array (
				'title'        => trans('Statische Seite'),
				'hidden'       => 0,
				'access-items' => array (
					'index'   => array (
						trans('darf Statische Seite benutzen'),
						1
					),
					'add'     => array (
						trans('darf Statische Seite hinzufügen'),
						0
					),
					'edit'    => array (
						trans('darf Statische Seite bearbeiten'),
						0
					),
					'publish' => array (
						trans('darf Statische Seite aktivieren/deaktivieren'),
						0
					),
					'delete'  => array (
						trans('darf Statische Seite löschen'),
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
	 * @return array
	 */
	public static function getIndexerOptions ()
	{

		return array (
			'controller' => 'page',
			'action'     => 'index',
			'location'   => 'page',
			'getData'    => 'getSearchIndexData', // function from model News
			'countData'  => 'getSearchIndexDataCount' // function from model News
		);
	}

    public static function bindEvents() {
        if ( Application::isBackend() )
        {
            $event = Registry::getObject('Event');

            $event->bindevent('onBeforeSave.page', function ( $newid, $postdata )
            {

            });
            $event->bindevent('onAfterSave.page', function ( $newid, $postdata )
            {
            });


            $event->bindevent('onBeforeSave.pagecat', function ( $newid, $postdata )
            {

            });
            $event->bindevent('onAfterSave.pagecat', function ( $newid, $postdata )
            {
            });
        }
    }

	/**
	 *
	 */
	public static function registerBackedMenu ()
	{

		$menu = array (
			'label' => trans('Seiten'),
			'items' => array (
				array (
					'label'       => trans('Seiten Übersicht'),
					'description' => null,
					'icon'        => null,
					'action'      => ''
				),
				array (
					'label'       => trans('Neue Seite'),
					'description' => null,
					'icon'        => null,
					'action'      => 'add'
				),
				array (
					'type' => 'separator'
				),
				array (
					'label'       => trans('Seite Katrgorien'),
					'description' => null,
					'icon'        => null,
					'action'      => 'pagecats'
				),
				array (
					'label'       => trans('Seite Katrgorie erstellen'),
					'description' => null,
					'icon'        => null,
					'action'      => 'editcat'
				),
				array (
					'type' => 'separator'
				),
				array (
					'label'       => trans('Seiten Importieren'),
					'description' => null,
					'icon'        => null,
					'action'      => 'import'
				),
			)
		);

		Menu::addMenuItem('content', 'page', $menu);
	}

	/**
	 *
	 *
	 * @return array
	 */
	public static function getModulDefinition ()
	{
		$GLOBALS[ 'versioning' ]['pages']            = array( 'enabled' => true, 'period' => 7776000 ); // all versions that are older than 3 months automatically delete
		$GLOBALS[ 'versioning' ]['pages_categories'] = array( 'enabled' => true, 'period' => 7776000 );

		return array (
			'dockurl'           => 'admin.php?adm=page',
			'modulelabel'       => trans('Statische Seite'),
			'allowmetadata'     => true,
			'cancomment'        => true,
			'moduledescription' => trans('Verwaltet statische Seiten. (Reiner HTML Code)'),
			'version'           => '0.2.7',
			'metatables'        => array (
				'pages'            => array (
					'controller' => 'page',
					'action'     => 'item',
					'primarykey' => 'id',
					'type'       => 'contents'
				),
				'pages_categories' => array (
					'controller' => 'page',
					'action'     => 'index',
					'primarykey' => 'catid',
					'type'       => 'categories'
				),
			),
			'modulactions'      => array (
				'movecat'     => 'adm=page&action=move&mode=cat&moveto={catid}&id={itemid}',
				'moveitem'    => 'adm=page&action=move&moveto={catid}&id={itemid}',
				'add-item'    => 'adm=page&action=add',
				'mod-publish' => 'adm=modules&action=publish',
			),
			'treeactions'       => array (
				'pages'            => array (
					'moveitem'        => 'adm=page&action=move&moveto={catid}&id={itemid}',
					'edit-item'       => 'adm=page&action=edit&id={contentid}',
					'add-item'        => 'adm=page&action=add',
					'item-publish'    => 'adm=page&action=publish&id={contentid}',
					'item-setindex'   => 'adm=page&action=setindex&id={contentid}',
					//
					'lock'            => 'index',
					'unlock'          => 'index',
					'lockunlock_data' => array (
						'table' => '%tp%pages',
						'pk'    => 'id',
					)
				),
				'pages_categories' => array (
					'movecat'         => 'adm=page&action=move&mode=cat&moveto={catid}&id={itemid}',
					'moveitem'        => 'adm=page&action=move&moveto={catid}&id={itemid}',
					'add-item'        => 'adm=page&action=add&catid={catid}',
					'edit-cat'        => 'adm=page&action=editcat&catid={catid}',
					'add-cat'         => 'adm=page&action=editcat&catid={catid}',
					'item-publish'    => 'adm=page&action=catpublish&catid={catid}',
					//
					'lock'            => 'cat',
					'unlock'          => 'cat',
					'lockunlock_data' => array (
						'table' => '%tp%pages_categories',
						'pk'    => 'catid',
					)
				),
			)
		);
	}

	/**
	 *
	 * @return array
	 */
	public static function getBackendMenu ()
	{

		return array (
			array (
				'title' => trans('Statische Seiten'),
				'items' => array (
					array (
						'label' => trans('Über Statische Seiten'),
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
						'label'    => trans('Statische Seiten beenden'),
						'call'     => 'closeApp',
						'shortcut' => 'CMD-E'
					)
				)
			),
			array (
				'title' => trans('Datei'),
				'items' => array (
					array (
						'label'     => trans('Neue Seite'),
						'action'    => 'add',
						'id'        => 'menuitem-newpage',
						'useWindow' => true
					),
					array (
						'label'     => trans('Neue Kategorien'),
						'action'    => 'editcat',
						'id'        => 'menuitem-newcat',
						'useWindow' => true
					), /*
                      array(
                      'label'     => trans( 'Seiten Typen' ),
                      'action'    => 'pagetypes',
                      'useWindow' => true
                      ), */
					array (
						'label'     => trans('Seiten Kategorien'),
						'action'    => 'pagecats',
						'useWindow' => true,
						'id'        => 'menuitem-pagecats'
					),
					array (
						'type' => 'line'
					),
					array (
						'title'       => trans('zuletzt bearbeitet...'),
						'dynamicItem' => true,
						'action'      => 'lastEdited',
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
