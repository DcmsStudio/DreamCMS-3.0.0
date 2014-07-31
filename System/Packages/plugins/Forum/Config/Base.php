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
 * @package     DreamCMS
 * @version     3.0.0 Beta
 * @category    Plugin s
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Base.php
 */
class Addon_Forum_Config_Base
{

    /**
     *
     * @return array
     */
    public static function getControllerPermissions( $be = false )
    {
        if ( !$be )
        {
            return array(
                'run' => array(
                    false,
                    true )
            );
        }
        else
        {
            return array(
                'run'          => array(
                    true,
                    true ),
                'addforum'     => array(
                    true,
                    true ),
                'editforum'    => array(
                    true,
                    true ),
                'publishforum' => array(
                    true,
                    true ),
                'addmod'       => array(
                    true,
                    true ),
                'editmod'      => array(
                    true,
                    true ),
                'publishmod'   => array(
                    true,
                    true ),
            );
        }
    }

    /**
     *
     * @param boolean $getBackend
     * @return array 
     */
    public static function getPermissions( $getBackend = false )
    {
        if ( $getBackend )
        {
            return array(
                'title'        => trans( 'Forum Plugin' ),
                'hidden'       => 0,
                'access-items' => array(
                    'run'          => array(
                        trans( 'darf Forum verwalten' ),
                        0 ),
                    'addforum'     => array(
                        trans( 'darf Foren hinzufügen' ),
                        0 ),
                    'editforum'    => array(
                        trans( 'darf Forum bearbeiten' ),
                        0 ),
                    'publishforum' => array(
                        trans( 'darf Forum aktivieren/deaktivieren' ),
                        0 ),
                    'addmod'       => array(
                        trans( 'darf Forum Moderatoren hinzufügen' ),
                        0 ),
                    'editmod'      => array(
                        trans( 'darf Forum Moderatoren bearbeiten' ),
                        0 ),
                    'publishmod'   => array(
                        trans( 'darf Forum Moderatoren aktivieren/deaktivieren' ),
                        0 ),
                )
            );
        }
        else
        {
            return array(
                // Tab Label
                'tablabel'                    => trans( 'Forum Plugin' ),
                // global perms
                'run'                         => array(
                    'type'        => 'checkbox',
                    'label'       => trans( 'Kann das Forum ausführen' ),
                    'default'     => 0,
                    'isActionKey' => true ),
                'canuseforumsearch'           => array(
                    'type'    => 'checkbox',
                    'label'   => trans( 'kann das Forum durchsuchen' ),
                    'require' => 'run',
                    'default' => 0 ),
                'canviewothers'               => array(
                    'type'    => 'checkbox',
                    'label'   => trans( 'kann Beiträge von andern Benutzern sehen' ),
                    'require' => 'run',
                    'default' => 1 ),
                'canpostnew'                  => array(
                    'type'    => 'checkbox',
                    'label'   => trans( 'kann neue Beiträge schreiben' ),
                    'require' => 'run',
                    'default' => 1 ),
                'canreplyown'                 => array(
                    'type'    => 'checkbox',
                    'label'   => trans( 'kann auf seine eigenen Beiträge antworten' ),
                    'require' => 'canpostnew',
                    'default' => 0 ),
                'canreplyothers'              => array(
                    'type'    => 'checkbox',
                    'label'   => trans( 'kann auf andere Beiträge antworten' ),
                    'require' => 'canpostnew',
                    'default' => 1 ),
                'isalwaysmoderated'           => array(
                    'type'    => 'checkbox',
                    'label'   => trans( 'neue Themen und Beiträge müssen erst durch einen Moderator freigeschalten werden' ),
                    'require' => 'canpostnew',
                    'default' => 0 ),
                'caneditpost'                 => array(
                    'type'    => 'checkbox',
                    'label'   => trans( 'kann Beiträge bearbeiten' ),
                    'require' => 'canpostnew',
                    'default' => 0 ),
                'candeletepost'               => array(
                    'type'    => 'checkbox',
                    'label'   => trans( 'kann Beiträge löschen' ),
                    'require' => 'canpostnew',
                    'default' => 0 ),
                'candeletethread'             => array(
                    'type'    => 'checkbox',
                    'label'   => trans( 'kann Themen löschen' ),
                    'require' => 'canpostnew',
                    'default' => 0 ),
                'canopenclose'                => array(
                    'type'    => 'checkbox',
                    'label'   => trans( 'kann Themen schließen' ),
                    'require' => 'canpostnew',
                    'default' => 0 ),
                'canmove'                     => array(
                    'type'    => 'checkbox',
                    'label'   => trans( 'kann Themen verschieben' ),
                    'require' => 'canpostnew',
                    'default' => 0 ),
                'canpostattachment'           => array(
                    'type'    => 'checkbox',
                    'label'   => trans( 'kann Attachments zu beiträgen hinzufügen' ),
                    'require' => 'canpostnew',
                    'default' => 0 ),
                'maxuploadsize'               => array(
                    'type'    => 'text',
                    'size'    => 20,
                    'label'   => trans( 'maximale Dateigröße für Attachments (in KB)' ),
                    'require' => 'canpostattachment',
                    'default' => 500 ),
                'allowedattachmentextensions' => array(
                    'type'    => 'text',
                    'size'    => 70,
                    'label'   => trans( 'Erlaubte Dateitypen für Attachments' ),
                    'require' => 'canpostattachment',
                    'default' => 'jpg, jpeg, gif, png, txt, css, js, php, zip, rar, gz, tar' ),
                'canpostpoll'                 => array(
                    'type'    => 'checkbox',
                    'label'   => trans( 'kann Umfragen erstellen' ),
                    'require' => 'run',
                    'default' => 0 ),
                'cangetattachment'            => array(
                    'type'    => 'checkbox',
                    'label'   => trans( 'kann Attachments herunterladen' ),
                    'require' => 'run',
                    'default' => 0 ),
                'canvote'                     => array(
                    'type'    => 'checkbox',
                    'label'   => trans( 'kann Umfragen bewerten' ),
                    'require' => 'run',
                    'default' => 0 ),
                'canthreadrate'               => array(
                    'type'    => 'checkbox',
                    'label'   => trans( 'kann Themen bewerten' ),
                    'require' => 'run',
                    'default' => 1 ),
                'canpostrate'                 => array(
                    'type'    => 'checkbox',
                    'label'   => trans( 'kann Beiträge bewerten' ),
                    'require' => 'run',
                    'default' => 1 ),
                'canseedelnotice'             => array(
                    'type'    => 'checkbox',
                    'label'   => trans( 'kann die Notz der Löschung von Themen und Beiträgen sehen' ),
                    'require' => 'run',
                    'default' => 0 ),
            );
        }
    }

    public static function getConfigItems()
    {
        return array(
            'items' => array(
                'uselikesystem'  => array(
                    'label'       => trans( '"Gefällt mir" & "Gefällt mir nicht" Buttons aktivieren' ),
                    'type'        => 'checkbox',
                    'values'      => '1|' . trans( 'aktivieren' ) . '|checked',
                    'description' => trans( 'Ermöglicht es einzelne Beiträge mit "Gefällt mir" & "Gefällt mir nicht" zu bewerten' ),
                ),
                'threadsperpage' => array(
                    'label'       => trans( 'Anzahl der Thema pro Seite' ),
                    'type'        => 'select',
                    'value'       => 20, // default per page
                    'values'      => '5|5 ' . trans( 'Themen' ) . '|
10|10 ' . trans( 'Themen' ) . '|
15|15 ' . trans( 'Themen' ) . '|
20|20 ' . trans( 'Themen' ) . '|
25|25 ' . trans( 'Themen' ) . '|
30|30 ' . trans( 'Themen' ) . '|
35|35 ' . trans( 'Themen' ) . '|
50|50 ' . trans( 'Themen' ) . '|',
                    'description' => trans( 'Geben Sie hier an, wieviel Thema pro Seite angezeigt werden sollen.' ),
                ),
	            'threadorder'      => array(
		            'label'       => trans( 'Themen sortieren nach' ),
		            'type'        => 'select',
		            'value'       => 'date', // default per page
		            'values'      => 'date|' . trans( 'letzter Beitrag' ) . '|selected
title|' . trans( 'Titel' ) . '|
rating|' . trans( 'Bewertung' ) . '|
hits|' . trans( 'Aufrufe' ) . '|',
	            ),
	            'threadsort'      => array(
		            'label'       => trans( 'Themen Sortierung' ),
		            'type'        => 'select',
		            'value'       => 'desc', // default per page
		            'values'      => 'desc|' . trans( 'absteigend' ) . '|selected
asc|' . trans( 'aufsteigend' ) . '|',
	            ),
                'postsperpage'   => array(
                    'label'       => trans( 'Anzahl der Beiträge im Thema pro Seite' ),
                    'type'        => 'select',
                    'value'       => 20, // default per page
                    'values'      => '5|5 ' . trans( 'Beiträge' ) . '|
10|10 ' . trans( 'Beiträge' ) . '|
15|15 ' . trans( 'Beiträge' ) . '|
20|20 ' . trans( 'Beiträge' ) . '|
25|25 ' . trans( 'Beiträge' ) . '|
30|30 ' . trans( 'Beiträge' ) . '|
35|35 ' . trans( 'Beiträge' ) . '|
50|50 ' . trans( 'Beiträge' ) . '|
75|75 ' . trans( 'Beiträge' ) . '|',
                    'description' => trans( 'Geben Sie hier an, wieviel Einträge in einem Thema pro Seite angezeigt werden sollen.' ),
                ),
                'postorder'      => array(
	                'label'       => trans( 'Beiträge sortieren nach' ),
	                'type'        => 'select',
	                'value'       => 'desc', // default per page
	                'values'      => 'desc|' . trans( 'Datum absteigend' ) . '|selected
asc|' . trans( 'Datum aufsteigend' ) . '|',
                ),
                'showattachimages'  => array(
	                'label'       => trans( 'Attachment Bilder als Thumbnails anzeigen' ),
	                'type'        => 'checkbox',
	                'values'      => '1|' . trans( 'Ja' ) . '|',
	              //  'description' => trans( 'Ermöglicht es einzelne Beiträge mit "Gefällt mir" & "Gefällt mir nicht" zu bewerten' ),
                ),
                'attachthumbwidth'    => array (
	                'label'        => trans('Breite des Thumbnails'),
	                'type'         => 'text',
	                'value'        => '100',
	                'maxlength'    => 4,
	                'size'         => 5,
	                'controls'     => false,
	                'fieldrequire' => 'showattachimages'
                ),
                'attachthumbheight'    => array (
	                'label'        => trans('Höhe des Thumbnails'),
	                'type'         => 'text',
	                'value'        => '100',
	                'maxlength'    => 4,
	                'size'         => 5,
	                'controls'     => false,
	                'fieldrequire' => 'showattachimages'
                ),


            )
        );
    }

    public static function getIndexerOptions()
    {
        return array(
            'controller' => 'plugin',
            'action'     => 'run',
            'location'   => 'plugin/forum/thread',
            'getData'    => 'getSearchIndexData', // function from model News
            'countData'  => 'getSearchIndexDataCount' // function from model News
        );
    }

    /**
     *
     * @return array
     */
    public static function getModulDefinition()
    {

	    Registry::getObject('Event')->bindevent('remove.plugin', function() {

	    });

	    Registry::getObject('Event')->bindevent('publish.plugin', function() {

	    });


        return array(
            'modulelabel'       => trans( 'Forum' ),
            'moduledescription' => trans( 'Ein Forum Plugin' ),
            'version'           => '0.1',
            'author'            => 'Marcel Domke',
            'website'           => 'http://www.dreamcms.de',
            'run'               => true,
            'config'            => true,
            'allowmetadata'     => true,
            'metatables'        => array(
                'board' => array(
                    'controller' => 'p:forum',
                    'action'     => 'index',
                    'primarykey' => 'forumid',
                    'type'       => 'categories' ),
            //'news_categories' => array( 'controller' => 'news', 'action'     => 'index', 'primarykey' => 'id', 'type'       => 'categories' ),
            ),
        );
    }

    /**
     * 
     */
    public static function registerBackedMenu()
    {
        $menu = array(
            'label' => trans( 'Forum' ),
            'items' => array(
                array(
                    'label'       => trans( 'Foren-Übersicht' ),
                    'description' => null,
                    'icon'        => null,
                    'action'      => ''
                ),
                array(
                    'label'       => trans( 'Neues Forum anlegen' ),
                    'description' => null,
                    'icon'        => null,
                    'action'      => 'addforum'
                ),
                array(
	                'label'       => trans( 'Foren Counter Aktualisieren' ),
	                'description' => null,
	                'icon'        => null,
	                'action'      => 'updateforumcounters',
	                'ajax'        => true
                ),
                array(
                    'type' => 'separator' ),
                array(
                    'label'       => trans( 'Einstellungen' ),
                    'description' => null,
                    'icon'        => null,
                    'call'        => 'appSettings'
                ),
            )
        );

        Menu::addMenuItem( 'plugin', 'forum', $menu );
    }

    /**
     * 
     * @return array
     */
    public static function getBackendMenu()
    {
        return array(
            array(
                'title' => trans( 'Forum' ),
                'items' => array(
                    array(
                        'label' => trans( 'Über Forum' ),
                        'call'  => 'aboutApp'
                    ),
                    array(
                        'type' => 'line' ),
                    array(
                        'label' => trans( 'Einstellungen' ),
                        'call'  => 'appSettings'
                    ),
                    array(
                        'type' => 'line' ),
                    array(
                        'label'    => trans( 'Forum beenden' ),
                        'call'     => 'closeApp',
                        'shortcut' => 'CMD-E'
                    )
                )
            ),
            array(
                'title' => trans( 'Datei' ),
                'items' => array(
                    array(
                        'label'     => trans( 'Neues Forum anlegen' ),
                        'action'    => 'addforum',
                        'useWindow' => true
                    )
                )
            ),
            array(
                'title' => trans( 'Hilfe' ),
                'items' => array(
                    array(
                        'label' => trans( 'Inhalt' ),
                        'call'  => 'help'
                    ),
                    array(
                        'label' => trans( 'Update' ),
                        'call'  => 'updateApp'
                    )
                )
            )
        );
    }

}
