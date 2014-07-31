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
 * @package     Library
 * @version     3.0.0 Beta
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        permission_defines.php
 */
defined( 'IN' ) or die( 'Direct Access to this location is not allowed.' );

// permissions für einen speziellen Benutzer unabhängig von seiner Benutzergruppe
$GLOBALS[ 'userpermissions' ] = array(
        'showsignatures'    => array(
                'type'  => 'checkbox',
                'label' => trans( 'darf Signaturen anderer Benutzer sehen' ) ),
        'showavatars'       => array(
                'type'  => 'checkbox',
                'label' => trans( 'darf Avatare/Profilbilder anderer Benutzer sehen' ) ),
        'showimages'        => array(
                'type'  => 'checkbox',
                'label' => trans( 'darf Bilder sehen' ) ),
        'coppauser'         => array(
                'type'  => 'checkbox',
                'label' => trans( 'ist ein "Sheriff"' ) ),
        'adminemail'        => array(
                'type'  => 'checkbox',
                'label' => trans( 'darf Dateien herunterladen' ) ),
        'showemail'         => array(
                'type'  => 'checkbox',
                'label' => trans( 'darf die Email Adresse anderer Benutzer sehen' ) ),
        'invisible'         => array(
                'type'  => 'checkbox',
                'label' => trans( 'darf sich unsichtbar machen' ) ),
        'showreputation'    => array(
                'type'  => 'checkbox',
                'label' => trans( 'darf Dateien herunterladen' ) ),
        'receivepm'         => array(
                'type'  => 'checkbox',
                'label' => trans( 'darf Privater Nachrichten nutzen' ) ),
        'emailonpm'         => array(
                'type'  => 'checkbox',
                'label' => trans( 'soll eine Email bei neuer Privater Nachricht erhalten' ) ),
        'hasaccessmask'     => array(
                'type'  => 'checkbox',
                'label' => trans( 'darf Dateien herunterladen' ) ),
        'emailnotification' => array(
                'type'  => 'checkbox',
                'label' => trans( 'darf Dateien herunterladen' ) ),
        'postorder'         => array(
                'type'  => 'checkbox',
                'label' => trans( 'darf Dateien herunterladen' ) ),
);


// field names for calendar permissions
$GLOBALS[ 'usergroup' ][ 'calendar' ] = array(
        // Tab Label
        'tablabel'           => trans( 'Kalender' ),
        // Bit Perms
        'canviewcalendar'    => array(
                'type'    => 'checkbox',
                'label'   => trans( 'sehen' ),
                'default' => 1 ),
        'canpostevent'       => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann Events eintragen' ),
                'default' => 0 ),
        'maxeventlength'     => array(
                'type'    => 'text',
                'width'   => 20,
                'label'   => trans( 'maximale länge des Event Textes' ),
                'require' => 'canpostevent',
                'default' => 500 ),
        'caneditevent'       => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann private Events eintragen' ),
                'require' => 'canpostevent',
                'default' => 0 ),
        'candeleteevent'     => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann Events löschen' ),
                'require' => 'canpostevent',
                'default' => 0 ),
        'canviewothersevent' => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann Events von anderen Benutzern sehen' ),
                'require' => 'canviewcalendar',
                'default' => 0 ), // Set to no to make a "Private Calendar"
);

// Fields for news
$GLOBALS[ 'usergroup' ][ 'news' ] = array(
        // Tab Label
        'tablabel'         => trans( 'News' ),
        // Bit Perms
        'cansubmitnews'    => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann News hinzufügen' ),
                'default' => 0 ),
        'cancommentnews'   => array(
                'type'    => 'checkbox',
                'label'   => trans( 'Kommentare zur News hinzufügen' ),
                'default' => 0 ),
        'maxcommentlength' => array(
                'type'    => 'text',
                'width'   => 20,
                'label'   => trans( 'maximale länge des Kommentars' ),
                'require' => 'cancommentnews',
                'default' => 500 )
);


// Fields for global use

$GLOBALS[ 'usergroup' ][ 'generic' ] = array(
        // Tab Label
        'tablabel'       => trans( 'Allgemeine Rechte' ),
        // Bit Perms
        'isbannedgroup'  => array(
                'type'    => 'checkbox',
                'label'   => trans( 'ist eine gesperrte Gruppe' ),
                'default' => 0 ),
        'canviewoffline' => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann das CMS im Offlinemodus sehen' ),
                'default' => 0 ),
        'cansearch'      => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann die Suchfunktion des Systems benutzen' ),
                'default' => 1 ),
        'cantelltofiend' => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann die Seite einer anderen Person weiterempfehlen' ),
                'default' => 1 ),
);


// Fields for polls
$GLOBALS[ 'usergroup' ][ 'polls' ] = array(
        // Tab Label
        'tablabel'         => trans( 'Umfragen' ),
        'canviewpoll'      => array(
                'type'    => 'checkbox',
                'label'   => trans( 'Umfragen sehen' ),
                'default' => 1 ),
        'canpoll'          => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann an Umfragen teilnehmen' ),
                'default' => 1 ),
        'cancommentpoll'   => array(
                'type'    => 'checkbox',
                'label'   => trans( 'Kommentare zur Umfrage hinzufügen' ),
                'default' => 0 ),
        'maxcommentlength' => array(
                'type'    => 'text',
                'width'   => 20,
                'label'   => trans( 'maximale länge des Kommentars' ),
                'require' => 'cancommentpoll',
                'default' => 500 )
);





// field names for User permissions
$GLOBALS[ 'usergroup' ][ 'user' ] = array(
        // Tab Label
        'tablabel'                => trans( 'Benutzer' ),
        // Bit Perms
        'canuseavatar'            => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann Avatare benutzen' ),
                'default' => 0 ),
        'canuseprivateavatar'     => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann eigene Avatare benutzen' ),
                'require' => 'canuseavatar',
                'default' => 0 ),
        'allowedavatarextensions' => array(
                'type'    => 'text',
                'width'   => 70,
                'label'   => trans( 'Erlaubte Endungen für Avatare. Bsp: jpg, jpeg, gif, png' ),
                'require' => 'canuseprivateavatar',
                'default' => 'jpg, jpeg, gif, png' ),
        'maxavatarsize'           => array(
                'type'    => 'text',
                'width'   => 20,
                'label'   => trans( 'maximale Dateigröße des eigenen Avatars in KB' ),
                'require' => 'canuseprivateavatar',
                'default' => 50 ),
        'maxavatarwidth'          => array(
                'type'    => 'text',
                'width'   => 20,
                'label'   => trans( 'maximale breite des eigenen Avatars' ),
                'require' => 'canuseprivateavatar',
                'default' => 200 ),
        'maxavatarheight'         => array(
                'type'    => 'text',
                'width'   => 20,
                'label'   => trans( 'maximale höhe des eigenen Avatars' ),
                'require' => 'canuseprivateavatar',
                'default' => 200 ),
        'canuseprivategbook'      => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann eigenes Gästebuch benutzen' ),
                'default' => 0 ),
        'maxprivategbookmessages' => array(
                'type'    => 'text',
                'width'   => 20,
                'label'   => trans( 'maximale Anzahl der erlaubten Gästebucheinträge' ),
                'require' => 'canuseprivategbook',
                'default' => 2000 ),
        'canviewotherprofiles'    => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann das Profil anderer Benutzer sehen' ),
                'default' => 1 ),
        'canusesignatur'          => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann Signatur benutzen' ),
                'default' => 0 ),
        'maxsignaturlength'       => array(
                'type'    => 'text',
                'width'   => 20,
                'label'   => trans( 'maximale länge der eigenen Signatur' ),
                'require' => 'canusesignatur',
                'default' => 200 ),
        'showbirthday'            => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann das Geburtsdatum von Benutzern sehen' ),
                'default' => 0 ),
        'showmemberlist'          => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann die Benutzerliste sehen' ),
                'default' => 1 ),
        'canseeinvisibleusers'    => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann versteckte Benutzer sehen' ),
                'default' => 0 ),
        'canuseghostmod'          => array(
                'type'        => 'checkbox',
                'label'       => trans( 'kann sich unsichtbar machen' ),
                'description' => trans( 'Hebt sich auf, wenn die Benutzergruppe auch versteckte Benutzer sehen darf' ),
                'default'     => 0 ),
        'canusepms'               => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann private Nachrichten benutzen' ),
                'default' => 0 ),
        'maxpmlength'             => array(
                'type'    => 'text',
                'width'   => 20,
                'label'   => trans( 'maximale länge von private Nachrichten' ),
                'require' => 'canusepms',
                'default' => 500 ),
        'maxpmfolders'            => array(
                'type'    => 'text',
                'width'   => 20,
                'label'   => trans( 'maximal anzahl an Ordnern' ),
                'require' => 'canusepms',
                'default' => 5 ),
        'maxpms'                  => array(
                'type'    => 'text',
                'width'   => 20,
                'label'   => trans( 'maximal anzahl an Nachrichten' ),
                'require' => 'canusepms',
                'default' => 100 ),
        'maxbiolength'            => array(
                'type'    => 'text',
                'width'   => 20,
                'label'   => trans( 'maximale länge der eigenen Beschreibung/Biografie' ),
                'default' => 1000 ),
);



$GLOBALS[ 'usergroup' ][ 'forum' ] = array(
        // Tab Label
        'tablabel'                    => trans( 'Forum' ),
        // Bit Perms
        'canview'                     => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann das Forum sehen' ),
                'default' => 1 ),
        'canviewothers'               => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann Beiträge von andern Benutzern sehen' ),
                'require' => 'canview',
                'default' => 1 ),
        'canuseforumsearch'           => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann das Forum durchsuchen' ),
                'require' => 'canview',
                'default' => 0 ),
        'canpostnew'                  => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann neue Beiträge schreiben' ),
                'require' => 'canview',
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
        'cangetattachment'            => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann Attachments herunterladen' ),
                'require' => 'canview',
                'default' => 0 ),
        'canpostattachment'           => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann Attachments zu beiträgen hinzufügen' ),
                'require' => 'canview',
                'default' => 0 ),
        'canpostpoll'                 => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann Umfragen erstellen' ),
                'require' => 'canview',
                'default' => 0 ),
        'canvote'                     => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann Umfragen bewerten' ),
                'require' => 'canview',
                'default' => 0 ),
        'canthreadrate'               => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann Themen bewerten' ),
                'require' => 'canview',
                'default' => 0 ),
        'isalwaysmoderated'           => array(
                'type'    => 'checkbox',
                'label'   => trans( 'neue Beiträge müssen erst freigeschalten werden' ),
                'require' => 'canview',
                'default' => 0 ),
        'canseedelnotice'             => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann die Notz der Löschung von Themen und Beiträgen sehen' ),
                'require' => 'canview',
                'default' => 0 ),
        'maxuploadsize'               => array(
                'type'    => 'text',
                'size'    => 20,
                'label'   => trans( 'maximale Dateigröße für Attachments (in KB)' ),
                'default' => 500 ),
        'allowedattachmentextensions' => array(
                'type'    => 'text',
                'size'    => 70,
                'label'   => trans( 'Erlaubte Dateitypen für Attachments' ),
                'default' => 'jpg, jpeg, gif, png, txt, css, js, php, zip, rar, gz, tar' ),
);










// field names for forum permissions
$GLOBALS[ 'BITFIELD' ][ 'forumpermissions' ] = array(
        'canview'           => 1,
        'canviewothers'     => 2,
        'cansearch'         => 4,
        'canemail'          => 8,
        'canpostnew'        => 16,
        'canreplyown'       => 32,
        'canreplyothers'    => 64,
        'caneditpost'       => 128,
        'candeletepost'     => 256,
        'candeletethread'   => 512,
        'canopenclose'      => 1024,
        'canmove'           => 2048,
        'cangetattachment'  => 4096,
        'canpostattachment' => 8192,
        'canpostpoll'       => 16384,
        'canvote'           => 32768,
        'canthreadrate'     => 65536,
        'isalwaysmoderated' => 131072,
        'canseedelnotice'   => 262144
);


// field names for administrative permissions
$GLOBALS[ 'BITFIELD' ][ 'adminpermissions' ] = array(
        'ismoderator'              => 1,
        'canadminforums'           => 2,
        'canadminthreads'          => 4,
        'canadminforumpermissions' => 8
);


// Forum Moderator permissions
$GLOBALS[ 'BITFIELD' ][ 'moderatorpermissions' ] = array(
        'caneditposts'           => 1,
        'candeleteposts'         => 2,
        'canopenclose'           => 4,
        'caneditthreads'         => 8,
        'canmanagethreads'       => 16,
        'canannounce'            => 32,
        'canmoderateposts'       => 64,
        'canmoderateattachments' => 128,
        'canmassmove'            => 256,
        'canmassprune'           => 512,
        'canviewips'             => 1024,
        'canbanusers'            => 4096,
        'canunbanusers'          => 8192,
        'newthreademail'         => 16384,
        'newpostemail'           => 32768,
        'cansetpassword'         => 65536,
        'canremoveposts'         => 131072,
);

// 
$GLOBALS[ 'moderatorpermissions' ] = array(
        'canmod_news'        => 1,
        'canmod_downloads'   => 2,
        'canmod_articles'    => 4,
        'canmod_links'       => 8,
        'canmod_users'       => 16,
        'canmod_gallerie'    => 32,
        'canmod_faq'         => 64,
        'canmod_cheats'      => 128,
        'canmod_games'       => 256,
        'canmod_gamereviews' => 512
        /* ,
          'canmod_gallerie' => 1024,
          '' => 2048,
          '' => 4096,
          '' => 8192,
          '' => 16384,
          '' => 32768,
          '' => 65536,
          '' => 131072,
          '' => 262144,
          '' => 524288,
          '' => 1048576,
          '' => 2097152,
          '' => 4194304,
          '' => 8388608,
          '' => 16777216,
          '' => 33554432,
          '' => 67108864,
          '' => 134217728,
          '' => 268435456,
          '' => 536870912,
          '' => 1073741824,
          '' => 2147483648,
          '' => 4294967296,
          '' => 8589934592,
          '' => 17179869184,
          '' => 34359738368,
          '' => 68719476736,
         */
);
?>