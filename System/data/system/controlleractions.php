<?php

$data = array(
        'development'      => array(
                'title'        => trans('Entwicklungstools'),
                'description'  => '',
                'hidden'       => 0,
                'access-items' => array(
                        'index'               => array(
                                'index',
                                0)
                        ,
                        'session'             => array(
                                'session',
                                0)
                        ,
                        'scancontrollers'     => array(
                                'scancontrollers',
                                0),
                        'scanxmlcontrollers'  => array(
                                'scanxmlcontrollers',
                                0),
                        'scanfrontendmodules' => array(
                                'scanfrontendmodules',
                                0),
                        'translations'        => array(
                                'translations',
                                0),
                )
        )
        ,
        'adminperms'       => array(
                'title'        => trans('Dashboard Rechte'),
                'description'  => trans('Achtung: Diese Option erlaubt dem User dieses Rechtesystem zu benutzen! Bitte diese Funktion nur dem Administrator zugängig machen!'),
                'hidden'       => 0,
                'access-items' => array(
                        'edit_user' => array(
                                trans('darf Rechte vergeben'),
                                0)
                        ,
                        'index'     => array(
                                trans('Dashboard Rechte verwenden'),
                                0)
                )
        )
        ,
        'options'          => array(
                'title'        => trans('System Konfiguration'),
                'description'  => trans('Achtung: Bitte diese Funktion nur dem Administrator zugängig machen!'),
                'hidden'       => 0,
                'access-items' => array(
                        'edit' => array(
                                trans('darf die System Konfiguration bearbeiten'),
                                0)
                )
        )
        ,
        'console'          => array(
                'title'        => trans('System Konsole'),
                'description'  => trans('Achtung: Bitte diese Funktion nur dem Administrator zugängig machen!'),
                'hidden'       => 0,
                'access-items' => array(
                        'index' => array(
                                trans('darf die System Konsole benutzen'),
                                0)
                )
        )
        ,
        'statistic'        => array(
                'title'        => trans('Statistiken'),
                'description'  => trans('Zeigt System Statistiken wie Besucher Herkunft/Browser/Betriebssystem usw. an'),
                'hidden'       => 0,
                'access-items' => array(
                        'index' => array(
                                trans('darf System Statistiken benutzen'),
                                0)
                )
        )
        ,
        'widget'           => array(
                'title'        => trans('Widgets'),
                'description'  => '',
                'hidden'       => 0,
                'access-items' => array(
                        'index'           => array(
                                trans('kann Widgets benutzen'),
                                1)
                        ,
                        'getwidgetconfig' => array(
                                trans('kann die Widget Einstellungen bearbeiten'),
                                1)
                        ,
                        'install'         => array(
                                trans('kann Widgets installieren'),
                                0)
                )
        )
        ,
        'pluginmanager'    => array(
                'title'        => trans('Plugin Manager'),
                'description'  => '',
                'hidden'       => 0,
                'access-items' => array(
                        'index'     => array(
                                trans('kann Plugins verwalten'),
                                1)
                        ,
                        'install'   => array(
                                trans('kann Plugins installieren'),
                                0)
                        ,
                        'uninstall' => array(
                                trans('kann Plugins deinstallieren'),
                                0)
                        ,
                        'config'    => array(
                                trans('kann Plugin Einstellungen bearbeiten'),
                                0)
                        ,
                        'publish'   => array(
                                trans('kann Plugins aktivieren/deaktivieren'),
                                0)
                )
        ),
        'transform'        => array(
                'title'        => trans('Bild Transformation'),
                'description'  => '',
                'hidden'       => 0,
                'access-items' => array(
                        'index'  => array(
                                trans('Bild Transformationen benutzen'),
                                1)
                        ,
                        'edit'   => array(
                                trans('Bild Transformation bearbeiten'),
                                0)
                        ,
                        'delete' => array(
                                trans('Bild Transformation löschen'),
                                0)
                )
        ),
        'usergroups'       => array(
                'title'        => trans('Benutzergruppen'),
                'description'  => '',
                'hidden'       => 0,
                'access-items' => array(
                        'index'      => array(
                                trans('darf Benutzergruppen verwalten'),
                                0)
                        ,
                        'setdefault' => array(
                                trans('darf die Standart Benutzergruppe ändern'),
                                0)
                        ,
                        'add'        => array(
                                trans('darf Benutzergruppen hinzufügen'),
                                0)
                        ,
                        'edit'       => array(
                                trans('darf Benutzergruppen bearbeiten'),
                                0)
                        ,
                        'delete'     => array(
                                trans('darf Benutzergruppen löschen'),
                                0)
                        ,
                        'dashaccess' => array(
                                trans('darf die Backend Rechte von Benutzergruppen verwalten'),
                                0)
                )
        )
        ,
        'users'            => array(
                'title'        => trans('Benutzer'),
                'description'  => '',
                'hidden'       => 0,
                'access-items' => array(
                        'index'      => array(
                                trans('darf Benutzer verwalten'),
                                0)
                        ,
                        'add'        => array(
                                trans('darf Benutzer hinzufügen'),
                                0)
                        ,
                        'edit'       => array(
                                trans('darf Benutzer bearbeiten'),
                                0)
                        ,
                        'blocking'   => array(
                                'darf Benutzer sperren',
                                0)
                        ,
                        'delete'     => array(
                                trans('darf Benutzer löschen'),
                                0)
                        ,
                        'access'     => array(
                                trans('darf Benutzern Spezielle Rechte zuweisen/diese verwalten'),
                                0)
                        ,
                        'activate'   => array(
                                trans('darf Benutzer aktivieren'),
                                0)
                        ,
                        'email'      => array(
                                trans('darf Benutzern Emails senden'),
                                0)
                        ,
                        'unblocking' => array(
                                trans('darf Benutzer entsperren'),
                                0)
                )
        )
        ,
        'profilefield'     => array(
                'title'        => trans('Profilefelder'),
                'description'  => '',
                'hidden'       => 0,
                'access-items' => array(
                        'index'  => array(
                                trans('darf Profilefelder verwalten'),
                                1)
                        ,
                        'add'    => array(
                                trans('darf Profilefelder hinzufügen'),
                                0)
                        ,
                        'edit'   => array(
                                trans('darf Profilefelder bearbeiten'),
                                0)
                        ,
                        'delete' => array(
                                trans('darf Profilefelder löschen'),
                                0)
                )
        )
        ,
        'eventmanager'     => array(
                'title'        => trans('Eventmanager (Hooks)'),
                'description'  => '',
                'hidden'       => 0,
                'access-items' => array(
                        'index'               => array(
                                trans('darf Events/Hooks verwalten'),
                                0)
                        ,
                        'removecomponenthook' => array(
                                trans('Hook Kompenenten entfernen'),
                                0)
                        ,
                        'synchooks'           => array(
                                trans('Events Syncronisieren'),
                                0)
                        ,
                        'savehookorder'       => array(
                                trans('Events sortieren'),
                                0)
                        ,
                        'scanevents'          => array(
                                trans('scannen'),
                                0)
                        ,
                        'component'           => array(
                                trans('component'),
                                0)
                        ,
                        'add'                 => array(
                                trans('hinzufügen'),
                                0)
                        ,
                        'edit'                => array(
                                trans('bearbeiten'),
                                0)
                )
        )
        ,
        'component'        => array(
                'title'        => trans('Komponenten'),
                'description'  => '',
                'hidden'       => 0,
                'access-items' => array(
                        'index'          => array(
                                trans('darf Komponenten verwalten'),
                                0)
                        ,
                        'delete'         => array(
                                trans('darf Benutzer hinzufügen'),
                                0)
                        ,
                        'save'           => array(
                                trans('darf Benutzer bearbeiten'),
                                0)
                        ,
                        'addcategory'    => array(
                                trans('darf Benutzer sperren/entsperren'),
                                0)
                        ,
                        'category'       => array(
                                trans('darf Benutzer löschen'),
                                0)
                        ,
                        'edit'           => array(
                                trans('darf Benutzer löschen'),
                                0)
                        ,
                        'add'            => array(
                                trans('darf Benutzer löschen'),
                                0)
                        ,
                        'deletecategory' => array(
                                trans('darf Benutzer löschen'),
                                0)
                        ,
                        'view'           => array(
                                trans('darf Benutzer löschen'),
                                0)
                        ,
                        'savecategories' => array(
                                trans('darf Benutzer löschen'),
                                0)
                )
        )
        ,
        'skins'            => array(
                'title'        => trans('Skins'),
                'description'  => '',
                'hidden'       => 0,
                'access-items' => array(
                        'index'          => array(
                                trans('darf Skins verwalten'),
                                0)
                        ,
                        'wizard'         => array(
                                trans('darf den Tag-Wizard verwenden'),
                                0)
                        ,
                        'addset'         => array(
                                trans('erstellen'),
                                0)
                        ,
                        'edit'           => array(
                                trans('darf Skins bearbeiten'),
                                0)
                        ,
                        'setdefault'     => array(
                                trans('Standart Skin ändern'),
                                0)
                        ,
                        'copy_templates' => array(
                                'Templates kopieren',
                                0)
                        ,
                        'remove'         => array(
                                trans('Skins löschen'),
                                0)
                        ,
                        'publish'        => array(
                                trans('kann Skins aktivieren/deaktivieren'),
                                0)
                        ,
                        'add'            => array(
                                trans('Skins hinzufügen'),
                                0)
                        ,
                        'changepublish'  => array(
                                trans('Skins aktivieren/deaktivieren'),
                                0)
                        ,
                        'delete'         => array(
                                trans('Skins löschen'),
                                0)
                        ,
                        'deltemplate'    => array(
                                trans('Templates löschen'),
                                0)
                        ,
                        'edittemplate'   => array(
                                trans('Templates bearbeiten'),
                                0)
                        ,
                        'export'         => array(
                                trans('Templates exportieren'),
                                0)
                        ,
                        'import'         => array(
                                trans('Templates Importieren'),
                                0)
                        ,
                        'regenerate'     => array(
                                trans('Templates erneuern'),
                                0)
                        ,
                        'renametemplate' => array(
                                trans('Templates umbenennen'),
                                0)
                        ,
                        'search'         => array(
                                trans('Templates Suchen'),
                                0)
                        ,
                        'getsensejs'     => array(
                                trans('getsense'),
                                0)
                        ,
                        'wizard'         => array(
                                trans('wizard'),
                                0)
                )
        )
        ,
        'bbcodes'          => array(
                'title'        => trans('BB-Codes'),
                'description'  => '',
                'hidden'       => 0,
                'access-items' => array(
                        'index'  => array(
                                trans('darf BB-Codes verwalten'),
                                1)
                        ,
                        'add'    => array(
                                trans('darf BB-Codes hinzufügen'),
                                0)
                        ,
                        'edit'   => array(
                                trans('darf BB-Codes bearbeiten'),
                                0)
                        ,
                        'delete' => array(
                                trans('darf BB-Codes löschen'),
                                0)
                )
        )
        ,
        'smilie'           => array(
                'title'        => trans('Smilies'),
                'description'  => '',
                'hidden'       => 0,
                'access-items' => array(
                        'index'  => array(
                                trans('darf Smilies verwalten'),
                                1)
                        ,
                        'add'    => array(
                                trans('darf Smilies hinzufügen'),
                                0)
                        ,
                        'edit'   => array(
                                trans('darf Smilies bearbeiten'),
                                0)
                        ,
                        'delete' => array(
                                trans('darf Smilies löschen'),
                                0)
                )
        )
        ,
        'icon'             => array(
                'title'        => trans('Icons'),
                'description'  => '',
                'hidden'       => 0,
                'access-items' => array(
                        'index'  => array(
                                trans('darf Icons verwalten'),
                                1)
                        ,
                        'add'    => array(
                                trans('darf Icons hinzufügen'),
                                0)
                        ,
                        'edit'   => array(
                                trans('darf Icons bearbeiten'),
                                0)
                        ,
                        'delete' => array(
                                trans('darf Icons löschen'),
                                0)
                )
        )
        ,
        'ranks'            => array(
                'title'        => trans('Benutzer Ränge'),
                'description'  => '',
                'hidden'       => 0,
                'access-items' => array(
                        'index'  => array(
                                trans('darf Ränge verwalten'),
                                1)
                        ,
                        'add'    => array(
                                trans('darf Ränge hinzufügen'),
                                0)
                        ,
                        'edit'   => array(
                                trans('darf Ränge bearbeiten'),
                                0)
                        ,
                        'delete' => array(
                                trans('darf Ränge löschen'),
                                0)
                )
        )
        ,
        'cachecontrol'     => array(
                'title'        => trans('Cache System'),
                'description'  => '',
                'hidden'       => 0,
                'access-items' => array(
                        /*
                          'index' => array(
                          trans('darf Cache System benutzen'),
                          0)
                          , */
                        'clear'          => array(
                                trans('darf Cache leeren'),
                                0)
                        ,
                        'clearfull'      => array(
                                trans('darf Cache komplett leeren'),
                                0)
                        ,
                        'clearpagecache' => array(
                                trans('darf Seitencache leeren'),
                                0)
                )
        )
        ,
        'database'         => array(
                'title'        => trans('Datenbank'),
                'description'  => '',
                'hidden'       => 0,
                'access-items' => array(
                        'index'    => array(
                                trans('darf Datenbank verwalten'),
                                0)
                        ,
                        'exec'     => array(
                                trans('SQL Befehle ausführen'),
                                0)
                        ,
                        'optimize' => array(
                                trans('darf Tabellen optimieren'),
                                0)
                        ,
                        'repaire'  => array(
                                trans('darf Tabellen reparieren'),
                                0)
                        ,
                        'import'   => array(
                                trans('darf SQL Dateien importieren (ausführen)'),
                                0)
                )
        )
        ,
        'backup'           => array(
                'title'        => trans('Backups'),
                'description'  => trans('Achtung: Bitte diese Funktion nur dem Administrator zugängig machen!'),
                'hidden'       => 0,
                'access-items' => array(
                        'index'    => array(
                                trans('darf Backups verwalten'),
                                0)
                        ,
                        'create'   => array(
                                trans('Backups erstellen'),
                                0)
                        ,
                        'download' => array(
                                trans('darf Backups herunterladen'),
                                0)
                        ,
                        'delete'   => array(
                                trans('darf Backups löschen'),
                                0)
                )
        )
        ,
        'filemanager'      => array(
                'title'        => trans('Dateimanager'),
                'description'  => '',
                'hidden'       => 0,
                'access-items' => array(
                        'index'     => array(
                                trans('darf Dateimanager benutzen'),
                                0)
                        ,
                        'delete'    => array(
                                trans('darf Dateien/Verzeichnisse löschen'),
                                0)
                        ,
                        'createdir' => array(
                                trans('darf Verzeichnisse erstellen'),
                                0)
                        ,
                        'save'      => array(
                                trans('darf Dateien bearbeiten/erstellen'),
                                0)
                        ,
                        'upload'    => array(
                                trans('darf Dateien Uploaden'),
                                0)
                        ,
                        'view'      => array(
                                trans('darf Datei Inhalte anzeigen'),
                                0)
                        ,
                        'rename'    => array(
                                trans('darf Verzeichnisse/Dateien umbenennen'),
                                0)
                        ,
                        'copy'      => array(
                                trans('darf Verzeichnisse/Dateien kopieren'),
                                0)
                        ,
                        'zip'       => array(
                                trans('darf Zip Dateien erstellen'),
                                0)
                )
        )
        ,
        'menues'           => array(
                'title'        => trans('Frontent Menüpunkte'),
                'description'  => '',
                'hidden'       => 0,
                'access-items' => array(
                        'list_menuitems'   => array(
                                trans('darf Menüpunkte verwalten'),
                                0)
                        ,
                        'edit_menuitem'    => array(
                                trans('darf Menüpunkte bearbeiten'),
                                0)
                        ,
                        'add_menuitem'     => array(
                                trans('darf Menüpunkte erstellen'),
                                0)
                        ,
                        'publish_menuitem' => array(
                                trans('darf Menüpunkte aktivieren/deaktivieren'),
                                0)
                        ,
                        'delete_menuitem'  => array(
                                trans('darf Menüpunkte löschen'),
                                0)
                        ,
                        'moveitem'         => array(
                                trans('darf Menüpunkte verschieben'),
                                0)
                        ,
                        'copyitem'         => array(
                                trans('darf Menüpunkte kopieren'),
                                0)
                        ,
                        'add_menu'         => array(
                                trans('add_menu'),
                                0)
                        ,
                        'delete_menu'      => array(
                                trans('delete_menu'),
                                0)
                        ,
                        'edit_menu'        => array(
                                trans('edit_menu'),
                                0)
                        ,
                        'menu_publish'     => array(
                                trans('menu_publish'),
                                0)
                        ,
                        'menu_unpublish'   => array(
                                trans('menu_unpublish'),
                                0)
                        ,
                        'reorder'          => array(
                                trans('reorder'),
                                0)
                        ,
                        'save_menu'        => array(
                                trans('save_menu'),
                                0)
                        ,
                        'save_menuitem'    => array(
                                trans('save_menuitem'),
                                0)
                )
        )
        ,
        'trash'            => array(
                'title'        => trans('Papierkorb'),
                'description'  => trans('Achtung: Bitte diese Funktion nur dem Administrator zugängig machen!'),
                'hidden'       => 0,
                'access-items' => array(
                        'index'   => array(
                                trans('darf Papierkorb benutzen'),
                                1)
                        ,
                        'restore' => array(
                                trans('darf Einträge im Papierkorb wiederherstellen'),
                                0)
                        ,
                        'delete'  => array(
                                trans('darf Papierkorb Einträge löschen und den Papierkorb leeren'),
                                0)
                )
        )
        ,
        'news'             => array(
                'title'        => trans('News'),
                'description'  => trans(''),
                'hidden'       => 0,
                'access-items' => array(
                        'index'             => array(
                                trans('darf News verwalten'),
                                0)
                        ,
                        'delete'            => array(
                                trans('darf News löschen'),
                                0)
                        ,
                        'add'               => array(
                                trans('darf News erstellen'),
                                0)
                        ,
                        'list_cats'         => array(
                                trans('darf News-Kategorien verwalten'),
                                0)
                        ,
                        'edit_cats'         => array(
                                trans('darf News-Kategorien bearbeiten'),
                                0)
                        ,
                        'catpublish'        => array(
                                trans('darf News-Kategorien aktivieren/deaktivieren'),
                                0)
                        ,
                        'edit_news'         => array(
                                trans('darf News bearbeiten'),
                                0)
                        ,
                        'delete_news'       => array(
                                trans('darf News löschen'),
                                0)
                        ,
                        'build_identifiers' => array(
                                trans('Aliase erneuern'),
                                0)
                        ,
                        'create_index'      => array(
                                trans('Suchindex erneuern'),
                                0)
                        ,
                        'archive'           => array(
                                trans('darf News archivieren und aus Archiv holen'),
                                0)
                        ,
                        'unarchive'         => array(
                                trans('aus Archiv holen'),
                                0)
                        ,
                        'publish'           => array(
                                trans('darf News aktivieren/deaktivieren'),
                                0)
                        ,
                        'publish_news'      => array(
                                trans('News aktivieren'),
                                0)
                        ,
                        'unpublish'         => array(
                                trans('News deaktivieren'),
                                0)
                        ,
                        'move'              => array(
                                trans('News verschieben'),
                                0)
                )
        )
        ,
        'application'      => array(
                'title'        => trans('Anwendungen'),
                'access-items' => array(
                        'index'      => array(
                                trans('Anwendungen verwalten'),
                                0)
                        ,
                        'edit'       => array(
                                trans('Anwendungen bearbeiten/hinzufügen'),
                                0)
                        ,
                        'buildindex' => array(
                                trans('Suchindex erneuern'),
                                0)
                        ,
                        'delete'     => array(
                                trans('Anwendungen löschen'),
                                0)
                        ,
                        'publish'    => array(
                                trans('Anwendungen aktivieren/deaktivieren'),
                                0)
                        ,
                        'addtab'     => array(
                                trans('Neue Tabs hinzufügen'),
                                0)
                        ,
                        'deletetab'  => array(
                                trans('Tabs löschen'),
                                0)
                        ,
                        'renametab'  => array(
                                trans('Tabs umbenennen'),
                                0)
                )
        )
        ,
        'applicationcats'  => array(
                'title'        => trans('Anwendungs Kategorien'),
                'access-items' => array(
                        'index'             => array(
                                trans('Anwendungs Kategorien verwalten'),
                                0)
                        ,
                        'edit'              => array(
                                trans('Anwendungs Kategorien bearbeiten/erstellen'),
                                0)
                        ,
                        'delete'            => array(
                                trans('Anwendungs Kategorien löschen'),
                                0)
                        ,
                        'publish'           => array(
                                trans('Anwendungs Kategorien aktivieren/deaktivieren'),
                                0)
                        ,
                        'reorder'           => array(
                                trans('Anwendungs Kategorien umsortieren'),
                                0)
                        ,
                        'build_identifiers' => array(
                                trans('Aliase der Anwendungs Kategorien aktualisieren'),
                                0)
                )
        )
        ,
        'applicationitems' => array(
                'title'        => trans('Anwendungsinhalte'),
                'access-items' => array(
                        'index'             => array(
                                trans('Anwendungsinhalte verwalten'),
                                0)
                        ,
                        'edit'              => array(
                                trans('Anwendungsinhalte erstellen/bearbeiten'),
                                0)
                        ,
                        'delete'            => array(
                                trans('Anwendungsinhalte löschen'),
                                0)
                        ,
                        'publish'           => array(
                                trans('Anwendungsinhalte aktivieren'),
                                0)
                        ,
                        'unpublish'         => array(
                                trans('Anwendungsinhalte deaktivieren'),
                                0)
                        ,
                        'build_identifiers' => array(
                                trans('Aliase der Anwendungsinhalte aktualisieren'),
                                0)
                ,
                )
        )
        ,
        'asset'            => array(
                'title'        => trans('Asset Verwaltung'),
                'access-items' => array(
                        'index'   => array(
                                trans('Asset Verwaltung benutzen'),
                                0)
                        ,
                        'delete'  => array(
                                trans('Asset löschen'),
                                0)
                        ,
                        'edit'    => array(
                                trans('Asset hinzufügen'),
                                0)
                        ,
                        'publish' => array(
                                trans('Assets aktivieren/deaktivieren'),
                                0)
                )
        )
        ,
        'avatar'           => array(
                'title'        => trans('Avatar Verwaltung'),
                'access-items' => array(
                        'index'  => array(
                                trans('Avatar Verwaltung benutzen'),
                                0),
                        'add'    => array(
                                trans('Avatar hinzufügen'),
                                0)
                        ,
                        'delete' => array(
                                trans('Avatar löschen'),
                                0)
                        ,
                        'edit'   => array(
                                trans('Avatar bearbeiten'),
                                0)
                )
        )
        ,
        'ban_ips'          => array(
                'title'        => trans('Blockierte IP´s'),
                'access-items' => array(
                        'index'    => array(
                                trans('Blockierte IP´s einsehen'),
                                0)
                        ,
                        'spamstat' => array(
                                trans('Spam Statistik einsehen'),
                                0)
                )
        )
        ,
        'banned'           => array(
                'title'        => trans('Blockierte IP´s'),
                'access-items' => array(
                        'index'    => array(
                                trans('Blockierte IP´s einsehen'),
                                0)
                        ,
                        'delete'   => array(
                                trans('Blockierte IP´s löschen'),
                                0)
                        ,
                        'details'  => array(
                                trans('Spammer Details einsehen'),
                                0)
                        ,
                        'spamstat' => array(
                                trans('Spam Statistik einsehen'),
                                0)
                )
        )
        ,
        'clickanalyser'    => array(
                'title'        => trans('Klick-Analyser'),
                'access-items' => array(
                        'analayse'      => array(
                                trans('Klick-Analyser Benutzen (erfordert Adminrechte und ist nur im Frontend sichtbar)'),
                                0)
                        ,
                        'clearanalayse' => array(
                                trans('Klick-Analyser ststistik für eine URL löschen (leeren)'),
                                0)
                )
        )
        ,
        'comments'         => array(
                'title'        => trans('Kommentare'),
                'access-items' => array(
                        'index'   => array(
                                trans('Kommentare verwalten'),
                                0),
                        'delete'  => array(
                                trans('Kommentare löschen'),
                                0)
                        ,
                        'publish' => array(
                                trans('Kommentare freischalten'),
                                0)
                        ,
                        'edit'    => array(
                                trans('Kommentare bearbeiten'),
                                0)
                )
        )
        ,
        'contentprovider'  => array(
                'title'        => trans('Contentprovider'),
                'access-items' => array(
                        'index'  => array(
                                trans('Contentprovider Verwalten'),
                                0)
                        ,
                        'add'    => array(
                                trans('Contentprovider hinzufügen'),
                                0)
                        ,
                        'delete' => array(
                                trans('Contentprovider löschen'),
                                0)
                        ,
                        'edit'   => array(
                                trans('Contentprovider bearbeiten'),
                                0)
                        ,
                        'order'  => array(
                                trans('Contentprovider umsortieren'),
                                0)
                )
        )
        ,
        'dashboard'        => array(
                'title'        => 'Dashboard',
                'access-items' => array(
                        'sessionhistory' => array(
                                trans('Sitzungshistorie sehen'),
                                0)
                )
        )
        ,
        'editorsettings'   => array(
                'title'        => trans('Editor Einstellungen'),
                'access-items' => array(
                        'delete'  => array(
                                trans('Editor Einstellungen löschen'),
                                0)
                        ,
                        'edit'    => array(
                                trans('Editor Einstellungen bearbeiten'),
                                0)
                        ,
                        'index'   => array(
                                trans('Editor Einstellungen einsehen'),
                                0)
                        ,
                        'publish' => array(
                                trans('Editor Einstellungen aktivieren/deaktivieren'),
                                0)
                )
        )
        ,
        'fileupload'       => array(
                'title'        => 'Dateiupload',
                'access-items' => array(
                        'index' => array(
                                trans('Dateiupload benutzen'),
                                0)
                )
        )
        ,
        'forms'            => array(
                'title'        => trans('Formulare'),
                'access-items' => array(
                        'editform'    => array(
                                trans('Formulare bearbeiten'),
                                0)
                        ,
                        'deleteform'  => array(
                                trans('Formulare löschen'),
                                0)
                        ,
                        'options'     => array(
                                trans('options'),
                                0)
                        ,
                        'fields'      => array(
                                trans('Formularfelder auflisten'),
                                0)
                        ,
                        'editfield'   => array(
                                trans('Formularfelder hinzufügen/bearbeiten'),
                                0)
                        ,
                        'deletefield' => array(
                                trans('Formularfelder löschen'),
                                0)
                )
        )
        ,
        'forum'            => array(
                'title'        => trans('Forum'),
                'access-items' => array(
                        'counters'   => array(
                                trans(''),
                                0)
                        ,
                        'delete'     => array(
                                trans(''),
                                0)
                        ,
                        'edit'       => array(
                                trans(''),
                                0)
                        ,
                        'managemods' => array(
                                trans(''),
                                0)
                        ,
                        'publish'    => array(
                                trans(''),
                                0)
                        ,
                        'reorder'    => array(
                                trans(''),
                                0)
                        ,
                        'save'       => array(
                                trans(''),
                                0)
                )
        )
        ,
        'help'             => array(
                'title'        => 'Lizenz',
                'access-items' => array(
                        'license' => array(
                                trans('Lizense Einsehen'),
                                0)
                )
        )
        ,
        'jqfiletree'       => array(
                'title'        => trans('Filetree'),
                'description'  => trans('Filetree erfordert zugriff auf den Dateimanager'),
                'access-items' => array(
                        'index' => array(
                                trans('Filetree benutzen'),
                                0)
                )
        )
        ,
        'layouter'         => array(
                'title'        => trans('Layout Manager'),
                'access-items' => array(
                        'index'       => array(
                                trans('Layout Manager benutzen'),
                                0)
                        ,
                        'delete'      => array(
                                trans('Layouts löschen'),
                                0)
                        ,
                        'duplicate'   => array(
                                trans('Layouts duplizieren'),
                                0)
                        ,
                        'edit'        => array(
                                trans('Layouts bearbeiten'),
                                0)
                        ,
                        'addblock'    => array(
                                trans('Neue Inhaltsblöcke hinzufügen'),
                                0)
                        ,
                        'editblock'   => array(
                                trans('Inhaltsblöcke bearbeiten'),
                                0)
                        ,
                        'moveblock'   => array(
                                trans('Inhaltsblöcke verschieben'),
                                0)
                        ,
                        'removeblock' => array(
                                trans('Inhaltsblöcke löschen'),
                                0)
                )
        )
        ,
        'linkcheck'        => array(
                'title'        => trans('Linkchecker'),
                'access-items' => array(
                        'index' => array(
                                trans('Linkchecker benutzen'),
                                0)
                )
        )
        ,
        'locale'           => array(
                'title'        => trans('Sprachen'),
                'access-items' => array(
                        'delete'           => array(
                                trans('löschen'),
                                0)
                        ,
                        'edit'             => array(
                                trans('bearbeiten'),
                                0)
                        ,
                        'edit_translation' => array(
                                trans('übesetzen (GUI Sprachen)'),
                                0)
                        ,
                        'index'            => array(
                                trans('darf Sprachen verwalten'),
                                0)
                        ,
                        'save'             => array(
                                trans('save'),
                                0)
                )
        )
        ,
        'logs'             => array(
                'title'        => trans('System Logs'),
                'access-items' => array(
                        'delete' => array(
                                trans('darf System Logs löschen'),
                                0)
                        ,
                        'index'  => array(
                                trans('darf System Logs sehen'),
                                0)
                )
        )
        ,
        'media'            => array(
                'title'        => trans('Media Manager'),
                'access-items' => array(
                        'createdir' => array(
                                trans('Biblioteken erstellen'),
                                0)
                        ,
                        'delete'    => array(
                                trans('löschen'),
                                0)
                        ,
                        'edit'      => array(
                                trans('bearbeiten'),
                                0)
                        ,
                        'link'      => array(
                                trans('neue Inhalte als Dateilink hinzufügen'),
                                0)
                        ,
                        'rename'    => array(
                                trans('Inhalte umbenennen'),
                                0)
                        ,
                        'upload'    => array(
                                trans('neue Inhalte hochladen'),
                                0)
                )
        )
        ,
        'modules'          => array(
                'title'        => trans('System Module'),
                'access-items' => array(
                        'createplugin' => array(
                                trans('createplugin'),
                                0)
                        ,
                        'publish'      => array(
                                trans('aktivieren/deaktivieren'),
                                0)
                        ,
                        'settings'     => array(
                                trans('darf die Einstellungen bearbeiten'),
                                0)
                        ,
                        'uninstall'    => array(
                                trans('darf Module deinstallieren'),
                                0)
                        ,
                        'update'       => array(
                                trans('darf Module aktualisieren'),
                                0)
                )
        )
        ,
        'packages'         => array(
                'title'        => trans('Paket Manager'),
                'access-items' => array(
                        'fetch' => array(
                                trans('Hinzufügen/aktualisieren'),
                                0)
                )
        )
        ,
        'pagestree'        => array(
                'title'        => trans('Pagetree'),
                'access-items' => array(
                        'index' => array(
                                trans('darf Pagetree verwenden'),
                                0)
                )
        )
        ,
        'repository'       => array(
                'title'        => trans('Controller: repository -> dev only'),
                'access-items' => array(
                        'delete'  => array(
                                trans('löschen'),
                                0)
                        ,
                        'edit'    => array(
                                trans('inhalte bearbeiten'),
                                0)
                        ,
                        'editcat' => array(
                                trans('kategorien bearbeiten'),
                                0)
                        ,
                        'upload'  => array(
                                trans('uploaden'),
                                0)
                )
        )
        ,
        'rules'            => array(
                'title'        => trans('Router'),
                'access-items' => array(
                        'delete'  => array(
                                trans('darf Routen löschen'),
                                0)
                        ,
                        'edit'    => array(
                                trans('darf Routen bearbeiten'),
                                0)
                        ,
                        'publish' => array(
                                trans('darf Routen aktivieren/deaktivieren'),
                                0)
                )
        )
        ,
        'switchdebug'      => array(
                'title'        => trans('Debugger'),
                'access-items' => array(
                        'index' => array(
                                trans('darf den Debugger zu/abschalten'),
                                0)
                )
        )
        ,
        'switchfirewall'   => array(
                'title'        => trans('Firewall'),
                'access-items' => array(
                        'index' => array(
                                trans('darf die Firewall zu/abschalten'),
                                0)
                )
        )
        ,
        'sys'              => array(
                'title'        => trans('Systeminformationen'),
                'access-items' => array(
                        'index' => array(
                                trans('darf die Systeminformationen sehen'),
                                0)
                )
        )
        ,
        'tags'             => array(
                'title'        => trans('Tags'),
                'access-items' => array(
                        'delete' => array(
                                trans('darf Tags löschen'),
                                0)
                )
        )
);
?>