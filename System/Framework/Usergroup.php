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
 * @category    Framework
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Usergroup.php
 *
 */
class Usergroup
{

    /**
     * Current object instance (Singleton)
     * @var object
     */
    protected static $objInstance = null;

    public $db = null;

    /**
     * all forum permission bits
     * @var array
     */
    protected static $bits = array(
        'forumpermissions'     => array(
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
        ),
// field names for administrative permissions
        'adminpermissions'     => array(
            'ismoderator'              => 1,
            'canadminforums'           => 2,
            'canadminthreads'          => 4,
            'canadminforumpermissions' => 8
        ),
// Forum Moderator permissions
        'moderatorpermissions' => array(
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
        )
    );

    /**
     * Prevent cloning of the object (Singleton)
     */
    final private function __clone()
    {
        
    }

    /**
     * Return the current object instance (Singleton)
     * @return object
     */
    public static function getInstance()
    {
        if ( self::$objInstance === null )
        {
            self::$objInstance = new Usergroup();
            self::$objInstance->db = Database::getInstance();
        }

        return self::$objInstance;
    }

    /**
     *
     * @return array
     */
    public static function getUsergroupTypes()
    {
        // Enum => Translation
        return array(
            'administrator' => trans( 'System Administrator' ),
            'chefredakteur' => trans( 'Chefredakteur' ),
            'author'        => trans( 'Autor' ),
            'default'       => trans( 'Benutzer' ),
            'guest'         => trans( 'Gäste' ),
        );
    }

    /**
     *
     * @return array
     */
    public function getAllUsergroups()
    {
        return $this->db->query( "SELECT * FROM %tp%users_groups ORDER BY title ASC" )->fetchAll();
    }

    /**
     *
     * @param integer $groupid
     * @return array
     */
    public function getUsergroupByID( $groupid = 0 )
    {
        return $this->db->query( "SELECT * FROM %tp%users_groups WHERE groupid = ?", $groupid )->fetch();
    }

    /**
     *
     * @param integer $groupid
     * @param boolean $backendMode default is false
     * @return array
     */
    public function getUsergroupPermissionsByID( $groupid = 0, $backendMode = false )
    {
        return $this->db->query( "SELECT * FROM %tp%users_groupactionperms WHERE groupid = ? AND isbackend = ?", $groupid, ($backendMode ? 1 : 0 )
                )->fetchAll();
    }

    /**
     *
     * @return array
     */
    public static function initFrontendPermKeys()
    {


        // permissions für einen speziellen Benutzer unabhängig von seiner Benutzergruppe
        $perms[ 'userpermissions' ] = array(
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
        $perms[ 'usergroup' ][ 'calendar' ] = array(
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
        /*
          // Fields for news
          $perms[ 'usergroup' ][ 'news' ]  = array(
          // Tab Label
          'tablabel'         => trans( 'News' ),
          // Bit Perms
          'cansubmitnews'    => array( 'type'    => 'checkbox', 'label'   => trans( 'kann News hinzufügen' ), 'default' => 0 ),
          'cancommentnews'   => array( 'type'    => 'checkbox', 'label'   => trans( 'Kommentare zur News hinzufügen' ), 'default' => 0 ),
          'maxcommentlength' => array( 'type'    => 'text', 'width'   => 20, 'label'   => trans( 'maximale länge des Kommentars' ), 'require' => 'cancommentnews', 'default' => 500 )
          );
          // Fields for news
          $perms[ 'usergroup' ][ 'pages' ] = array(
          // Tab Label
          'tablabel'         => trans( 'Seiten' ),
          // Bit Perms
          'cansubmitpagess'  => array( 'type'    => 'checkbox', 'label'   => trans( 'kann Seiten hinzufügen' ), 'default' => 0 ),
          'cancommentpages'  => array( 'type'    => 'checkbox', 'label'   => trans( 'Kommentare zur Seiten hinzufügen' ), 'default' => 0 ),
          'maxcommentlength' => array( 'type'    => 'text', 'width'   => 20, 'label'   => trans( 'maximale länge des Kommentars' ), 'require' => 'cancommentpages', 'default' => 500 )
          );
         */
// Fields for global use

        $perms[ 'usergroup' ][ 'generic' ] = array(
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
        $perms[ 'usergroup' ][ 'polls' ] = array(
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
        $perms[ 'usergroup' ][ 'user' ] = array(
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
                'label'   => trans( 'Erlaubte Endungen für Avatare' ),
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
            // private guestbook
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
            // can view other user profiles
            'canviewotherprofiles'    => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann das Profil anderer Benutzer sehen' ),
                'default' => 1 ),
            // Signature
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
            /*

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
             */
            'maxbiolength'            => array(
                'type'    => 'text',
                'width'   => 20,
                'label'   => trans( 'maximale länge der eigenen Beschreibung/Biografie' ),
                'default' => 1000 ),
        );

        /*

          $perms[ 'usergroup' ][ 'forum' ] = array(
          // Tab Label
          'tablabel'          => trans( 'Forum' ),
          // Bit Perms
          'canview'           => array( 'type'    => 'checkbox', 'label'   => trans( 'kann das Forum sehen' ), 'default' => 1 ),
          'canviewothers'     => array( 'type'    => 'checkbox', 'label'   => trans( 'kann Beiträge von andern Benutzern sehen' ), 'require' => 'canview', 'default' => 1 ),
          'canuseforumsearch' => array( 'type'    => 'checkbox', 'label'   => trans( 'kann das Forum durchsuchen' ), 'require' => 'canview', 'default' => 0 ),
          'canpostnew'        => array( 'type'    => 'checkbox', 'label'   => trans( 'kann neue Beiträge schreiben' ), 'require' => 'canview', 'default' => 1 ),
          'canreplyown'       => array( 'type'    => 'checkbox', 'label'   => trans( 'kann auf seine eigenen Beiträge antworten' ), 'require' => 'canpostnew', 'default' => 0 ),
          'canreplyothers'    => array( 'type'    => 'checkbox', 'label'   => trans( 'kann auf andere Beiträge antworten' ), 'require' => 'canpostnew', 'default' => 1 ),
          'caneditpost'       => array( 'type'    => 'checkbox', 'label'   => trans( 'kann Beiträge bearbeiten' ), 'require' => 'canpostnew', 'default' => 0 ),
          'candeletepost'     => array( 'type'    => 'checkbox', 'label'   => trans( 'kann Beiträge löschen' ), 'require' => 'canpostnew', 'default' => 0 ),
          'candeletethread'   => array( 'type'    => 'checkbox', 'label'   => trans( 'kann Themen löschen' ), 'require' => 'canpostnew', 'default' => 0 ),
          'canopenclose'      => array( 'type'    => 'checkbox', 'label'   => trans( 'kann Themen schließen' ), 'require' => 'canpostnew', 'default' => 0 ),
          'canmove'           => array( 'type'    => 'checkbox', 'label'   => trans( 'kann Themen verschieben' ), 'require' => 'canpostnew', 'default' => 0 ),
          'maxuploadsize'               => array( 'type'    => 'text', 'size'    => 20, 'label'   => trans( 'maximale Dateigröße für Attachments (in KB)' ), 'require' => 'canpostnew', 'default' => 500 ),
          'allowedattachmentextensions' => array( 'type'    => 'text', 'size'    => 70, 'label'   => trans( 'Erlaubte Dateitypen für Attachments' ), 'require' => 'canpostnew', 'default' => 'jpg, jpeg, gif, png, txt, css, js, php, zip, rar, gz, tar' ),
          'canpostattachment'           => array( 'type'    => 'checkbox', 'label'   => trans( 'kann Attachments zu beiträgen hinzufügen' ), 'require' => 'canpostnew', 'default' => 0 ),
          'canpostpoll' => array( 'type'    => 'checkbox', 'label'   => trans( 'kann Umfragen erstellen' ), 'require' => 'canpostnew', 'default' => 0 ),
          'cangetattachment' => array( 'type'    => 'checkbox', 'label'   => trans( 'kann Attachments herunterladen' ), 'require' => 'canview', 'default' => 0 ),
          'canvote'           => array( 'type'    => 'checkbox', 'label'   => trans( 'kann Umfragen bewerten' ), 'require' => 'canview', 'default' => 0 ),
          'canthreadrate'     => array( 'type'    => 'checkbox', 'label'   => trans( 'kann Themen bewerten' ), 'require' => 'canview', 'default' => 0 ),
          'isalwaysmoderated' => array( 'type'    => 'checkbox', 'label'   => trans( 'neue Beiträge müssen erst freigeschalten werden' ), 'require' => 'canview', 'default' => 0 ),
          'canseedelnotice'   => array( 'type'    => 'checkbox', 'label'   => trans( 'kann die Notz der Löschung von Themen und Beiträgen sehen' ), 'require' => 'canview', 'default' => 0 ),
          );
         */
        self::getControllerFrontendPerms( $perms );
        /*
          $app = new App();
          $appPerms = $app->registerPermissions();
          foreach($appPerms as $key => $data)
          {
          $perms['usergroup'][$key] = $data;
          }
         */

        // Register all Plugin Perms
        Plugin::loadPluginPermissions();

        $pluginPerms = Plugin::getPluginPerms();

        if ( is_array( $pluginPerms[ 'usergroup' ] ) )
        {
            foreach ( $pluginPerms[ 'usergroup' ] as $pluginKey => $dat )
            {
                $perms[ 'usergroup' ][ $pluginKey ] = $dat;
            }
        }


        //print_r($perms[ 'usergroup' ]);exit;

        uasort( $perms[ 'usergroup' ], "Usergroup::cmp" );
        return $perms;
    }

    /**
     * @param $a
     * @param $b
     * @return int
     */
    private static function cmp( $a, $b )
    {
        return (isset($a[ 'tablabel' ]) && isset($b[ 'tablabel' ]) ? strcmp( $a[ 'tablabel' ], $b[ 'tablabel' ] ) : 0);
    }

    /**
     * Sort the Perms by Tablabel
     * @param array $perms
     */
    private static function sortPermTabs( $perms )
    {
        
    }

    /**
     * Get all installed Modules and return the Frontent Permissions
     * @param array $perms
     * @throws BaseException
     */
    public static function getControllerFrontendPerms( &$perms )
    {

        $application = Registry::getObject( 'Application' );

        if ( !($application instanceof Application) )
        {
            throw new BaseException( 'Application Framework is not in the Registry!' );
        }

        $modules = $application->loadFrontendModules();

        foreach ( $modules as $modul => $r )
        {

            if ( !checkClassMethod( $modul . '_Config_Base/getPermissions' ) )
            {
                continue;
                //throw new BaseException( sprintf( 'The modul function `getPermissions` not exists in Modul "%s"!', $modul ) );
            }

            $tmp = call_user_func_array( array(
                $modul . '_Config_Base',
                "getPermissions" ), array(
                false ) );

            if ( is_array( $tmp ) && count( $tmp ) )
            {
                if ( !isset( $tmp[ 'tablabel' ] ) || empty( $tmp[ 'tablabel' ] ) && isset( $r[ 'modulelabel' ] ) )
                {
                    $tmp[ 'tablabel' ] = $r[ 'modulelabel' ];
                }
                elseif ( !isset( $tmp[ 'tablabel' ] ) || empty( $tmp[ 'tablabel' ] ) && !isset( $r[ 'modulelabel' ] ) )
                {
                    $tmp[ 'tablabel' ] = 'Undefined Tab Label (' . $modul . ')';
                }

                $perms[ 'usergroup' ][ strtolower( $modul ) ] = $tmp;
                $tmp = null;
            }
        }

        $application = $modules = null;
    }

    /**
     * Get all installed Modules and return the Backend Permissions
     * @param array $perms
     * @throws BaseException
     */
    public static function getControllerBackendPerms( &$perms )
    {

        $application = Registry::getObject( 'Application' );

        if ( !($application instanceof Application) )
        {
            throw new BaseException( 'Application Framework is not in the Registry!' );
        }

        $modules = $application->loadFrontendModules();

        foreach ( $modules as $modul => $r )
        {

            if ( !method_exists( $modul . '_Config_Base', 'getPermissions' ) )
            {
                continue;
                //throw new BaseException( sprintf( 'The modul function `getPermissions` not exists in Modul "%s"!', $modul ) );
            }

            $tmp = call_user_func_array( array(
                $modul . '_Config_Base',
                "getPermissions" ), array(
                true ) );
            if ( is_array( $tmp ) && count( $tmp ) )
            {
                if ( !isset( $tmp[ 'tablabel' ] ) || empty( $tmp[ 'tablabel' ] ) && isset( $r[ 'modulelabel' ] ) )
                {
                    $tmp[ 'tablabel' ] = $r[ 'modulelabel' ];
                }
                elseif ( !isset( $tmp[ 'tablabel' ] ) || empty( $tmp[ 'tablabel' ] ) && !isset( $r[ 'modulelabel' ] ) )
                {
                    $tmp[ 'tablabel' ] = 'Undefined Tab Label (' . $modul . ')';
                }

                $perms[ 'usergroup' ][ strtolower( $modul ) ] = $tmp;
                $tmp = null;
            }
        }

        $application = $modules = null;
    }

    /**
     * get all permission keys by $keyName
     *
     * @see Usergroup::initFrontendPermKeys
     * @param string $keyName
     * @return array|null
     */
    public static function getGroupPermissionKeys( $keyName )
    {
        $perms = self::initFrontendPermKeys();
        return (isset( $perms[ $keyName ] ) ? $perms[ $keyName ] : null);
    }

    /**
     *
     * @see Usergroup::$bits
     * @param string $keyName
     * @return array|null
     */
    public static function getPermissionBitFields( $keyName )
    {
        return (isset( self::$bits[ $keyName ] ) ? self::$bits[ $keyName ] : null);
    }

}
