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
 * @package      News
 * @version      3.0.0 Beta
 * @category     Config
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class News_Config_Base
{

	/**
	 * @var array
	 */
	public static $controllerpermBackend = array (
		'index'              => array (
			true,
			true
		),
		'list_cats'          => array (
			true,
			true
		),
		'edit_cats'          => array (
			true,
			true
		),
		'catpublish'         => array (
			true,
			true
		),
		'add'                => array (
			true,
			true
		),
		'edit_news'          => array (
			true,
			true
		),
		'publish'            => array (
			true,
			true
		),
		'unpublish'          => array (
			true,
			true
		),
		'archive'            => array (
			true,
			true
		),
		'unarchive'          => array (
			true,
			true
		),
		'publish_news'       => array (
			true,
			true
		),
		'move'               => array (
			true,
			true
		),
		'save_news'          => array (
			true,
			false
		),
		'rebuildidentifiers' => array (
			true,
			true
		),
		'delete'             => array (
			true,
			true
		),
		'delete_news'        => array (
			true,
			true
		),
		'create_index'       => array (
			true,
			true
		),
	);

	/**
	 * @var array
	 */
	public static $controllerpermFrontend = array (
		'index'   => array (
			false,
			false
		),
		'cat'     => array (
			false,
			false
		),
		'captcha' => array (
			false,
			false
		),
		'show'    => array (
			false,
			false
		),
		'item'    => array (
			false,
			false
		),
	);

	/**
	 * @return array
	 */
	public static function getConfigItems ()
	{

		return array (
			'items' => array (
				'enablesocialbuttons' => array (
					'label'  => trans('aktiviere Social Sharing Buttons'),
					'type'   => 'checkbox',
					'values' => '1|' . trans('aktivieren') . '|',
					//  'description' => trans( 'Versucht links zu diversen Video Sites zu parsen und das Video inline zur Verfügung zu stellen.' ),
				),
				/*
				  'fb' => array(
				  'fieldrequire' => 'enablesocialbuttons',
				  'label'        => trans( 'aktiviere Facebook Sharing Button' ),
				  'type'         => 'checkbox',
				  'values'       => '1|' . trans( 'aktivieren' ) . '|',
				  //  'description' => trans( 'Versucht links zu diversen Video Sites zu parsen und das Video inline zur Verfügung zu stellen.' ),
				  ),
				  'gplus' => array(
				  'fieldrequire' => 'enablesocialbuttons',
				  'label'        => trans( 'aktiviere Google Plus Sharing Button' ),
				  'type'         => 'checkbox',
				  'values'       => '1|' . trans( 'aktivieren' ) . '|',
				  //  'description' => trans( 'Versucht links zu diversen Video Sites zu parsen und das Video inline zur Verfügung zu stellen.' ),
				  ),
				  'twitter' => array(
				  'fieldrequire' => 'enablesocialbuttons',
				  'label'        => trans( 'aktiviere Twitter Sharing Button' ),
				  'type'         => 'checkbox',
				  'values'       => '1|' . trans( 'aktivieren' ) . '|',
				  //  'description' => trans( 'Versucht links zu diversen Video Sites zu parsen und das Video inline zur Verfügung zu stellen.' ),
				  ),

				 */
				'parsevideos'         => array (
					'label'       => trans('Videos Parsen (Standart)'),
					'type'        => 'checkbox',
					'values'      => '1|' . trans('aktivieren') . '|checked',
					'description' => trans('Versucht links zu diversen Video Sites zu parsen und das Video inline zur Verfügung zu stellen.'),
				),
				'parsefootnotes'      => array (
					'label'       => trans('Content Links als Fußnoten ausgeben (Standart)'),
					'type'        => 'checkbox',
					'values'      => '1|' . trans('aktivieren') . '|',
					'description' => trans('Alle Links als Fußnoten ausgeben.'),
				),
				'userating'           => array (
					'label'       => trans('Bewertungsfunktion aktivieren'),
					'type'        => 'checkbox',
					'values'      => '1|' . trans('aktivieren') . '|checked',
					'description' => trans('Geben Sie hier an, ob die Bewertungsfunktion für News verfügbar sein soll.'),
				),
				'usecomments'         => array (
					'label'       => trans('Kommentarfunktion aktivieren'),
					'type'        => 'checkbox',
					'values'      => '1|' . trans('aktivieren') . '|checked',
					'description' => trans('Geben Sie hier an, ob die Kommentarfunktion für News verfügbar sein soll.'),
				),
				'perpage'             => array (
					'label'       => trans('Anzahl der Artikel pro Seite'),
					'type'        => 'select',
					'value'       => 20,
					// default per page
					'values'      => '5|5 ' . trans('Artikel') . '|
10|10 ' . trans('Artikel') . '|
15|15 ' . trans('Artikel') . '|
20|20 ' . trans('Artikel') . '|
25|25 ' . trans('Artikel') . '|
30|30 ' . trans('Artikel') . '|
35|35 ' . trans('Artikel') . '|
50|50 ' . trans('Artikel') . '|
75|75 ' . trans('Artikel') . '|',
					'description' => trans('Geben Sie hier an, wieviel Einträge in der News-Übersicht angezeigt werden sollen.'),
				),
			)
		);
	}

	/**
	 * @return array
	 */
	public static function getIndexerOptions ()
	{

		return array (
			'controller'   => 'news',
			'action'       => 'show',
			'location'     => 'news/item',
			'editlocation' => 'adm=news&action=edit_news&id={contentid}',
			'getData'      => 'getSearchIndexData', // function from model News
			'countData'    => 'getSearchIndexDataCount' // function from model News
		);
	}

	/**
	 * @return array
	 */
	public static function getWidgets ()
	{

		return array (
			'categories' => trans('Nachrichten Katrgorien'),
			'lastnews'   => trans('letzte Nachrichten'),
			'topnews'    => trans('Top Nachrichten'),
			'list'       => trans('Nachrichten Liste'),
			'item'       => trans('Nachrichten Eintrag'),
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
				'tablabel'         => trans('Nachrichten'), // Bit Perms
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
				'title'        => trans('Nachrichten'),
				'hidden'       => 0,
				'access-items' => array (
					'list_cats'          => array (
						trans('darf Nachrichten-Kategorien verwalten'),
						0
					),
					'edit_cats'          => array (
						trans('darf Nachrichten-Kategorien bearbeiten'),
						0
					),
					'delete'             => array (
						trans('darf Nachrichten-Kategorien löschen'),
						0
					),
					'catpublish'         => array (
						trans('darf Nachrichten-Kategorien aktivieren/deaktivieren'),
						0
					),
					'index'              => array (
						trans('darf Nachrichten verwalten'),
						0
					),
					'edit_news'          => array (
						trans('darf Nachrichten bearbeiten'),
						0
					),
					'add'                => array (
						trans('darf Nachrichten erstellen'),
						0
					),
					'delete_news'        => array (
						trans('darf Nachrichten löschen'),
						0
					),
					'rebuildidentifiers' => array (
						trans('darf Nachrichten Aliase erneuern'),
						0
					),
					'create_index'       => array (
						trans('darf Nachrichten Suchindex erneuern'),
						0
					),
					'archive'            => array (
						trans('darf Nachrichten archivieren und aus Archiv holen'),
						0
					),
					'unarchive'          => array (
						trans('darf Nachrichten aus Archiv holen'),
						0
					),
					'publish'            => array (
						trans('darf Nachrichten aktivieren/deaktivieren'),
						0
					),
					'publish_news'       => array (
						trans('darf Nachrichten aktivieren'),
						0
					),
					'unpublish'          => array (
						trans('darf Nachrichten deaktivieren'),
						0
					),
					'move'               => array (
						trans('darf Nachrichten verschieben'),
						0
					)
				)
			);
		}
	}

	/**
	 * get actions
	 *
	 * @param bool $getBackend default false
	 * @return array return a array.
	 *                         Example: ( myaction -> (true, false) )
	 *                         the action -> (require login, require permissons)
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

    public static function bindEvents() {
        if ( Application::isBackend() )
        {
            $event = Registry::getObject('Event');

            $event->bindevent('onBeforeSave.news', function ( $newid, $postdata )
            {

            });
            $event->bindevent('onAfterSave.news', function ( $newid, $postdata )
            {
            });


            $event->bindevent('onBeforeSave.newscat', function ( $newid, $postdata )
            {

            });
            $event->bindevent('onAfterSave.newscat', function ( $newid, $postdata )
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
			'label' => trans('Nachrichten'),
			'items' => array (
				array (
					'label'       => trans('Nachrichten Übersicht'),
					'description' => null,
					'icon'        => null,
					'action'      => ''
				),
				array (
					'label'       => trans('Neu'),
					'description' => null,
					'icon'        => null,
					'action'      => 'add'
				),
				array (
					'type' => 'separator'
				),
				array (
					'label'       => trans('Nachrichten Katrgorien'),
					'description' => null,
					'icon'        => null,
					'action'      => 'list_cats'
				),
				array (
					'label'       => trans('Nachrichten Katrgorie erstellen'),
					'description' => null,
					'icon'        => null,
					'action'      => 'edit_cats'
				),
			)
		);

		Menu::addMenuItem('content', 'news', $menu);
	}

	/**
	 * Used in Class SystemManager
	 *
	 * @return array
	 */
	public static function getModulDefinition ()
	{

		return array (
			'dockurl'           => 'admin.php?adm=news',
			'modulelabel'       => trans('Nachrichten'),
			'allowmetadata'     => true,
			'cancomment'        => true,
			'moduledescription' => null,
			'version'           => '0.2.3',
			'license'           => 'GPL v2 <p>sdfjs jdf sdpf jnsdj fd fsd</p>sdf jnsd<p/> sdfjn on osadnf <p/>noasdjnfa dfg <br/>asdj nasdas jsda <p>hnhhhhi</p>djfm jsdf nsdv<p/><p/> sdx',
			'copyright'         => '(c) 2012-2013 by Marcel Domke',
			'metatables'        => array (
				'news'            => array (
					'controller' => 'news',
					'action'     => 'item',
					'primarykey' => 'id',
					'type'       => 'contents'
				),
				'news_categories' => array (
					'controller' => 'news',
					'action'     => 'index',
					'primarykey' => 'id',
					'type'       => 'categories'
				),
			),
			'modulactions'      => array (
				'movecat'     => 'adm=news&action=move&mode=cat&moveto={catid}&id={itemid}',
				//  'edit-item'   => 'adm=news&action=edit_news&id={contentid}',
				'add-cat'     => 'adm=news&action=edit_cats&id={catid}',
				'mod-publish' => 'adm=modules&action=publish',
			),
			'treeactions'       => array (
				'news'            => array (
					'moveitem'        => 'adm=news&action=move&moveto={catid}&id={itemid}',
					'edit-item'       => 'adm=news&action=edit_news&id={contentid}',
					'add-item'        => 'adm=news&action=add&catid={catid}',
					'item-publish'    => 'adm=news&action=publish&id={contentid}',
					//
					'lock'            => 'show',
					'unlock'          => 'show',
					'lockunlock_data' => array (
						'table' => '%tp%news',
						'pk'    => 'id'
					)
				),
				'news_categories' => array (
					'movecat'         => 'adm=news&action=move&mode=cat&moveto={catid}&id={itemid}',
					'moveitem'        => 'adm=news&action=move&moveto={catid}&id={itemid}',
					'add-item'        => 'adm=news&action=add&catid={catid}',
					'edit-cat'        => 'adm=news&action=edit_cats&id={catid}',
					'add-cat'         => 'adm=news&action=edit_cats&id={catid}',
					'item-publish'    => 'adm=news&action=catpublish&cat_id={catid}',
					//
					'lock'            => 'index',
					'unlock'          => 'index',
					'lockunlock_data' => array (
						'table' => '%tp%news_categories',
						'pk'    => 'id',
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
				'title' => trans('Nachrichten'),
				'items' => array (
					array (
						'label' => trans('Über Nachrichten'),
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
						'label'    => trans('Nachrichten beenden'),
						'call'     => 'closeApp',
						'shortcut' => 'CMD-E'
					)
				)
			),
			array (
				'title' => trans('Datei'),
				'items' => array (
					array (
						'label'     => trans('Neue Nachricht'),
						'action'    => 'add',
						'useWindow' => true
					),
					array (
						'label'     => trans('Neue Kategorie'),
						'action'    => 'edit_cats',
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
					),
					array (
						'title'  => trans('Nachrichten Suchindex erneuern'),
						'action' => 'create_index'
					),
					array (
						'title'  => trans('Aliase erneuern'),
						'action' => 'rebuildidentifiers',
						'call'   => 'aliasBuilder'
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
