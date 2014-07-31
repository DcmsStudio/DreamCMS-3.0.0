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
 * @file        Permission.php
 *
 */
class Permission
{

    /**
     * @var bool
     */
    private static $loaded = false;

    /**
     * @var array
     */
    private static $action_permissions = array();

    // Controller Rechte
    /**
     * @var array
     */
    private static $dash_permissions = array();

    // Action Rechte für den Benutzer
    /**
     * @var null
     */
    protected static $group_controllerpermissions = null;

    /**
     * @var null
     */
    protected static $private_controllerpermissions = null;

    /**
     *
     * @var type
     */
    protected static $_dashboardPermissions = null;

    /**
     *
     * @var type
     */
    protected static $_dashboardPermissions_Private = null;

    /**
     *
     * @var type
     */
    protected static $grouppermissions = null;

    /**
     * @var null
     */
    protected static $privatepermissions = null;

    /**
     * @var null
     */
    protected static $isBackend = null;

    /**
     *
     * @var Permission
     */
    protected static $_instance = null;

    /**
     * Prevent cloning of the object (Singleton)
     */
    final private function __clone()
    {
        
    }

    /**
     * Return the current object instance (Singleton)
     *
     * @return Permission
     */
    public static function getInstance()
    {
        if ( self::$_instance === null )
        {
            self::$_instance = new Permission;
            self::$_instance->initPermissions();
        }

        return self::$_instance;
    }

    /**
     * will init all backend permissions
     * permissions by usergroup and special permissions for the user
     *
     * @return void
     */
    private static function initDashboardPermissions()
    {
        if ( self::$_dashboardPermissions !== null || !User::isAdmin() )
        {
            return;
        }

        $db = Database::getInstance();
        $result = $db->query( 'SELECT * FROM %tp%admin_usergroup_access WHERE groupid = ?', self::$usergroupid )->fetchAll();

        foreach ( $result as $r )
        {
            self::$_dashboardPermissions[ $r[ 'controller' ] ][ $r[ 'action' ] ] = true;
        }

        $db->free();


        $result = $db->query( 'SELECT * FROM %tp%admin_user_access WHERE userid = ?', self::$user_id )->fetchAll();
        foreach ( $result as $r )
        {
            self::$_dashboardPermissions_Private[ $r[ 'controller' ] ][ $r[ 'action' ] ] = true;
        }

        $db->free();
    }

    /**
     * will init all frontend permissions
     * permissions by usergroup and special permissions for the user
     *
     * @return void
     */
    public static function initPermissions()
    {
        static $mode;

        if ( self::$loaded === true )
        {
            return;
        }


        // read the group permission
        self::$grouppermissions = User::getGroupPermission(); // gruppen einstellung allgemein
        //
        // read the private permission
        self::$privatepermissions = User::getPrivatePermission(); // gruppen einstellung speziell für einen benutzer


        if ( !is_bool( $mode ) )
        {
            $mode = 0;

            if ( defined( 'ADM_SCRIPT' ) && ADM_SCRIPT )
            {
                $mode = 1;
            }
        }

        self::$isBackend = ($mode === Application::BACKEND_MODE ? true : false);

        $db = Database::getInstance();


        /**
         * Prepare Usergroup controller perms
         */
        $group_controllerpermissions = Cache::get( 'groupactionperms-' . User::getGroupId(), 'data' );
        if ( !is_array( $group_controllerpermissions ) )
        {
            $groupcontrollerpermissions = $db->query( 'SELECT * FROM %tp%users_groupactionperms WHERE groupid = ?
                                                      ORDER BY hasperm DESC', User::getGroupId() )->fetchAll();


            $tmp = array();
            foreach ( $groupcontrollerpermissions as $r )
            {

                if ( !isset( $tmp[ $r[ 'controller' ] ] ) )
                {
                    $tmp[ $r[ 'controller' ] ] = array();
                }


                if ( !isset( $tmp[ $r[ 'controller' ] ][ $r[ 'isbackend' ] ] ) )
                {
                    $tmp[ $r[ 'controller' ] ][ $r[ 'isbackend' ] ][ 'controllerhasperm' ] = ($r[ 'hasperm' ] ? true : false); // set default
                }

                $tmp[ $r[ 'controller' ] ][ $r[ 'isbackend' ] ][ $r[ 'action' ] ] = array(
                    'hasperm' => ($r[ 'hasperm' ] ? true : false) );
            }

            $db->free();


            $group_controllerpermissions = $tmp;


            Cache::write( 'groupactionperms-' . User::getGroupId(), $group_controllerpermissions, 'data' );
        }


        self::$group_controllerpermissions = $group_controllerpermissions;

        unset( $group_controllerpermissions );

        $private_controllerpermissions = array();
        if ( User::getUserId() )
        {
            // Prepare Private controller perms for this User
            $private_controllerpermissions = $db->query( 'SELECT * FROM %tp%users_useractionperms WHERE userid = ? ORDER BY hasperm DESC', User::getUserId() )->fetchAll();


            foreach ( $private_controllerpermissions as $r )
            {
                if ( !isset( self::$private_controllerpermissions[ $r[ 'controller' ] ][ $r[ 'isbackend' ] ] ) )
                {
                    self::$private_controllerpermissions[ $r[ 'controller' ] ][ $r[ 'isbackend' ] ][ 'controllerhasperm' ] = $r[ 'hasperm' ]; // set default
                }

                self::$private_controllerpermissions[ $r[ 'controller' ] ][ $r[ 'isbackend' ] ][ $r[ 'action' ] ] = array(
                    'hasperm' => $r[ 'hasperm' ] );
            }

            $db->free();
        }

        unset( $private_controllerpermissions );

        self::$loaded = true;
    }

    /**
     *
     * @param string $controller
     * @return bool
     */
    static private function assertGroupPermission( $controller )
    {
        return (isset( self::$grouppermissions[ $controller ] ) ? self::$grouppermissions[ $controller ] : false);
    }

    /**
     *
     * @param string $action
     * @return bool
     */
    static private function assertGroupActionPermission( $action )
    {
        list($controller, $event) = explode( '/', $action );

        // Check private Permission if exists the controller
        if ( isset( self::$privatepermissions[ $controller ][ $event ] ) )
        {
            return (isset( self::$privatepermissions[ $controller ][ $event ] ) ? self::$privatepermissions[ $controller ][ $event ] : false);
        }

        return (isset( self::$grouppermissions[ $controller ][ $event ] ) ? self::$grouppermissions[ $controller ][ $event ] : false);
    }

    /**
     *
     * @param string $controller
     * @return bool
     */
    static private function assertPrivateGroupPermission( $controller )
    {
        return isset( self::$grouppermissions[ $controller ] );
    }

    // ------------------- Controller

    /**
     *
     * @param string $action
     * @return bool
     */
    static private function groupControllerActionPermission( $action )
    {
        self::initPermissions();
        //$hasperm = false;

        list($controller, $event) = explode( '/', $action );
        $hasperm = self::assertGroupActionPermission( $action );

        if ( self::$isBackend )
        {
            $hasperm = false;

            # print_r(self::$group_controllerpermissions);exit;
            // Check private Permission if exists the controller and action
            if ( isset( self::$private_controllerpermissions[ $controller ][ 1 ][ $event ][ 'hasperm' ] ) )
            {
                $hasperm = (self::$private_controllerpermissions[ $controller ][ 1 ][ $event ][ 'hasperm' ] ? true : false);
            }
            else
            {
                if ( isset( self::$group_controllerpermissions[ $controller ][ 1 ][ $event ][ 'hasperm' ] ) )
                {
                    $hasperm = (self::$group_controllerpermissions[ $controller ][ 1 ][ $event ][ 'hasperm' ] ? true : false);
                }
            }
        }
        else
        {

            // Check private Permission if exists the controller and action
            if ( isset( self::$private_controllerpermissions[ $controller ][ 0 ][ $event ][ 'hasperm' ] ) )
            {
                $hasperm = (self::$private_controllerpermissions[ $controller ][ 0 ][ $event ][ 'hasperm' ] ? true : false);
            }
            else
            {
                if ( isset( self::$group_controllerpermissions[ $controller ][ 0 ][ $event ][ 'hasperm' ] ) )
                {
                    $hasperm = (self::$group_controllerpermissions[ $controller ][ 0 ][ $event ][ 'hasperm' ] ? true : false);
                }
            }
        }


        return $hasperm;
    }

    /**
     *
     * @param string $controller
     * @return bool
     */
    static private function groupControllerPermission( $controller )
    {
        self::initPermissions();
        $hasperm = self::assertGroupPermission( $controller );

        if ( self::$isBackend )
        {
            $hasperm = false;

            // Check private Permission if exists the controller
            if ( isset( self::$private_controllerpermissions[ $controller ][ 1 ] ) )
            {
                $hasperm = (self::$private_controllerpermissions[ $controller ][ 1 ][ 'controllerhasperm' ] ? true : false);
            }
            else
            {
                $hasperm = (isset( self::$group_controllerpermissions[ $controller ][ 1 ] ) && self::$group_controllerpermissions[ $controller ][ 1 ][ 'controllerhasperm' ] ? true : false);
            }
        }
        else
        {
            // Check private Permission if exists the controller
            if ( isset( self::$private_controllerpermissions[ $controller ][ 0 ] ) )
            {
                $hasperm = (self::$private_controllerpermissions[ $controller ][ 0 ][ 'controllerhasperm' ] ? true : false);
            }
            else
            {
                $hasperm = (isset( self::$group_controllerpermissions[ $controller ][ 0 ] ) && self::$group_controllerpermissions[ $controller ][ 0 ][ 'controllerhasperm' ] ? true : false);
            }
        }

        return $hasperm;
    }

    /**
     *
     * @param string $action
     * @return bool
     */
    public static function hasControllerActionPerm( $action = null )
    {
        if ( $action === null )
        {
            return false;
        }


        self::initPermissions();

        #   print_r(self::$group_controllerpermissions);


        $has_permission = false;
        $action = strtolower( $action );

        if ( strpos( $action, '/' ) !== false )
        {
            $has_permission = self::groupControllerActionPermission( $action );
        }
        else
        {
            $has_permission = self::groupControllerPermission( $action );
        }

        return $has_permission;
    }

    /**
     *
     *
      ---------------------------------------------------------------------------------
     *
     *
     */

    /**
     *
     * @param array $a
     * @param array $b
     * @return string
     */
    public static function cmp( $a, $b )
    {
        return strcmp( $a[ 'tablabel' ], $b[ 'tablabel' ] );
    }

    /**
     *
     * @param array $a
     * @param array $b
     * @return string
     */
    public static function cmp2( $a, $b )
    {
        return strcmp( $a[ 'title' ], $b[ 'title' ] );
    }

    /**
     *
     * @return array
     */
    public static function initFrontendPermissions()
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

        $perms = array();
// field names for calendar permissions
        $perms[ 'usergroup' ][ 'calendar' ] = array(
            // Tab Label
            'tablabel'           => trans( 'Kalender' ),
            // Bit Perms
            'calendar'           => array(
                'type'        => 'checkbox',
                'label'       => trans( 'sehen' ),
                'default'     => 1,
                'isActionKey' => true ),
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


        $perms[ 'usergroup' ][ 'generic' ] = array(
            // Tab Label
            'tablabel'                => trans( 'Allgemeine Rechte' ),
            // Bit Perms
            'isbannedgroup'           => array(
                'type'    => 'checkbox',
                'label'   => trans( 'ist eine gesperrte Gruppe' ),
                'default' => 0 ),
            'canviewofflinedocuments' => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann Seiten die in Bearbeitung (Locked) sind sehen' ),
                'default' => 0 ),
            'canviewoffline'          => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann das CMS im Offlinemodus sehen' ),
                'default' => 0 ),
            'cansearch'               => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann die Suchfunktion des Systems benutzen' ),
                'default' => 1 ),
            'cantelltofiend'          => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann die Seite einer anderen Person weiterempfehlen' ),
                'default' => 1 ),
        );


// Fields for polls
        $perms[ 'usergroup' ][ 'polls' ] = array(
            // Tab Label
            'tablabel'         => trans( 'Umfragen' ),
            'poll'             => array(
                'type'        => 'checkbox',
                'label'       => trans( 'Umfragen sehen' ),
                'default'     => 1,
                'isActionKey' => true ),
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
            'avatar'                  => array(
                'type'        => 'checkbox',
                'label'       => trans( 'kann Avatare benutzen' ),
                'default'     => 0,
                'isActionKey' => true ),
            'privateavatar'           => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann eigene Avatare benutzen' ),
                'require' => 'avatar',
                'default' => 0 ),
            'allowedavatarextensions' => array(
                'type'    => 'text',
                'width'   => 70,
                'label'   => trans( 'Erlaubte Endungen für Avatare' ),
                'require' => 'privateavatar',
                'default' => 'jpg, jpeg, gif, png' ),
            'maxavatarsize'           => array(
                'type'    => 'text',
                'width'   => 20,
                'label'   => trans( 'maximale Dateigröße des eigenen Avatars in KB' ),
                'require' => 'privateavatar',
                'default' => 50 ),
            'maxavatarwidth'          => array(
                'type'    => 'text',
                'width'   => 20,
                'label'   => trans( 'maximale breite des eigenen Avatars' ),
                'require' => 'privateavatar',
                'default' => 200 ),
            'maxavatarheight'         => array(
                'type'    => 'text',
                'width'   => 20,
                'label'   => trans( 'maximale höhe des eigenen Avatars' ),
                'require' => 'privateavatar',
                'default' => 200 ),
            'userguestbook'           => array(
                'type'        => 'checkbox',
                'label'       => trans( 'kann eigenes Gästebuch benutzen' ),
                'default'     => 0,
                'isActionKey' => true ),
            'maxprivategbookmessages' => array(
                'type'    => 'text',
                'width'   => 20,
                'label'   => trans( 'maximale Anzahl der erlaubten Gästebucheinträge' ),
                'require' => 'userguestbook',
                'default' => 2000 ),
            'canviewotherprofiles'    => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann das Profil anderer Benutzer sehen' ),
                'default' => 1 ),
            'signatur'                => array(
                'type'        => 'checkbox',
                'label'       => trans( 'kann Signatur benutzen' ),
                'default'     => 0,
                'isActionKey' => true ),
            'maxsignaturlength'       => array(
                'type'    => 'text',
                'width'   => 20,
                'label'   => trans( 'maximale länge der eigenen Signatur' ),
                'require' => 'signatur',
                'default' => 200 ),
            'showbirthday'            => array(
                'type'    => 'checkbox',
                'label'   => trans( 'kann das Geburtsdatum von Benutzern sehen' ),
                'default' => 0 ),
            'members'                 => array(
                'type'        => 'checkbox',
                'label'       => trans( 'kann die Benutzerliste sehen' ),
                'default'     => 1,
                'isActionKey' => true ),
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
              'messenger'               => array(
              'type'        => 'checkbox',
              'label'       => trans( 'kann private Nachrichten benutzen' ),
              'default'     => 0,
              'isActionKey' => true ),
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
          'tablabel'                    => trans( 'Forum' ),
          // Bit Perms
          'forum'                       => array( 'type'        => 'checkbox', 'label'       => trans( 'kann das Forum sehen' ), 'default'     => 1, 'isActionKey' => true ),
          'canviewothers'               => array( 'type'    => 'checkbox', 'label'   => trans( 'kann Beiträge von andern Benutzern sehen' ), 'require' => 'canview', 'default' => 1 ),
          'canuseforumsearch'           => array( 'type'    => 'checkbox', 'label'   => trans( 'kann das Forum durchsuchen' ), 'require' => 'canview', 'default' => 0 ),
          'canpostnew'                  => array( 'type'    => 'checkbox', 'label'   => trans( 'kann neue Beiträge schreiben' ), 'require' => 'canview', 'default' => 1 ),
          'canreplyown'                 => array( 'type'    => 'checkbox', 'label'   => trans( 'kann auf seine eigenen Beiträge antworten' ), 'require' => 'canpostnew', 'default' => 0 ),
          'canreplyothers'              => array( 'type'    => 'checkbox', 'label'   => trans( 'kann auf andere Beiträge antworten' ), 'require' => 'canpostnew', 'default' => 1 ),
          'caneditpost'                 => array( 'type'    => 'checkbox', 'label'   => trans( 'kann Beiträge bearbeiten' ), 'require' => 'canpostnew', 'default' => 0 ),
          'candeletepost'               => array( 'type'    => 'checkbox', 'label'   => trans( 'kann Beiträge löschen' ), 'require' => 'canpostnew', 'default' => 0 ),
          'candeletethread'             => array( 'type'    => 'checkbox', 'label'   => trans( 'kann Themen löschen' ), 'require' => 'canpostnew', 'default' => 0 ),
          'canopenclose'                => array( 'type'    => 'checkbox', 'label'   => trans( 'kann Themen schließen' ), 'require' => 'canpostnew', 'default' => 0 ),
          'canmove'                     => array( 'type'    => 'checkbox', 'label'   => trans( 'kann Themen verschieben' ), 'require' => 'canpostnew', 'default' => 0 ),
          'maxuploadsize'               => array( 'type'    => 'text', 'size'    => 20, 'label'   => trans( 'maximale Dateigröße für Attachments (in KB)' ), 'require' => 'canpostnew', 'default' => 500 ),
          'allowedattachmentextensions' => array( 'type'    => 'text', 'size'    => 70, 'label'   => trans( 'Erlaubte Dateitypen für Attachments' ), 'require' => 'canpostnew', 'default' => 'jpg, jpeg, gif, png, txt, css, js, php, zip, rar, gz, tar' ),
          'canpostattachment'           => array( 'type'    => 'checkbox', 'label'   => trans( 'kann Attachments zu beiträgen hinzufügen' ), 'require' => 'canpostnew', 'default' => 0 ),
          'canpostpoll'                 => array( 'type'    => 'checkbox', 'label'   => trans( 'kann Umfragen erstellen' ), 'require' => 'canpostnew', 'default' => 0 ),
          'cangetattachment'            => array( 'type'    => 'checkbox', 'label'   => trans( 'kann Attachments herunterladen' ), 'require' => 'canview', 'default' => 0 ),
          'canvote'                     => array( 'type'    => 'checkbox', 'label'   => trans( 'kann Umfragen bewerten' ), 'require' => 'canview', 'default' => 0 ),
          'canthreadrate'               => array( 'type'    => 'checkbox', 'label'   => trans( 'kann Themen bewerten' ), 'require' => 'canview', 'default' => 0 ),
          'isalwaysmoderated'           => array( 'type'    => 'checkbox', 'label'   => trans( 'neue Beiträge müssen erst freigeschalten werden' ), 'require' => 'canview', 'default' => 0 ),
          'canseedelnotice'             => array( 'type'    => 'checkbox', 'label'   => trans( 'kann die Notz der Löschung von Themen und Beiträgen sehen' ), 'require' => 'canview', 'default' => 0 ),
          );
         */
        self::getFrontendPerms( $perms );


        // Register all Plugin Perms
        Plugin::loadPluginPermissions( false );
        $pluginPerms = Plugin::getPluginPerms();

        if ( is_array( $pluginPerms[ 'usergroup' ] ) )
        {
            foreach ( $pluginPerms[ 'usergroup' ] as $pluginKey => $dat )
            {
                if ( $pluginKey )
                {
                    $perms[ 'usergroup' ][ $pluginKey ] = $dat;
                }
            }
        }


        $perms[ 'usergroup' ] = Library::unempty( $perms[ 'usergroup' ] );


        uasort( $perms[ 'usergroup' ], "Permission::cmp" );
        return $perms;
    }

    /**
     * @return array
     */
    public static function initBackendPermissions()
    {
        $perms = array();

        self::getBackendPerms( $perms );

        // Register all Plugin Perms
        Plugin::loadPluginPermissions( true );
        $pluginPerms = Plugin::getPluginPerms();

        if ( is_array( $pluginPerms[ 'usergroup' ] ) )
        {
            foreach ( $pluginPerms[ 'usergroup' ] as $pluginKey => $dat )
            {
                $perms[ 'usergroup' ][ $pluginKey ] = $dat;
            }
        }

        uasort( $perms[ 'usergroup' ], "Permission::cmp2" );
        return $perms;
    }

    /**
     * Get all installed Modules and return the Frontent Permissions
     *
     * @param array $perms is reference
     * @throws BaseException
     */
    public static function getFrontendPerms( &$perms )
    {

        $application = Registry::getObject( 'Application' );

        if ( !($application instanceof Application) )
        {
            die( 'Application Framework is not in the Registry!' );
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
                if ( $tmp[ 'tablabel' ] )
                {

                    $perms[ 'usergroup' ][ strtolower( $modul ) ] = $tmp;
                }
                $tmp = null;
            }
        }

        $application = $modules = null;
    }

    /**
     * Get all installed Modules and return the Backend Permissions
     *
     * @param array $perms is reference
     * // @throws BaseException
     */
    public static function getBackendPerms( &$perms )
    {
        $application = Registry::getObject( 'Application' );

        if ( !($application instanceof Application) )
        {
	        die( 'Application Framework is not in the Registry!' );
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
                $perms[ 'usergroup' ][ strtolower( $modul ) ] = $tmp;
                $tmp = null;
            }
        }

        $application = $modules = null;
    }

    // ============================================================
    // takes a bitfield and the array describing the resulting fields
    // ============================================================
    /**
     * @param int $bitfield
     * @param array $_FIELDNAMES
     * @return array
     */
    public static function convert_bits_to_array( $bitfield, $_FIELDNAMES )
    {
        $bitfield = intval( $bitfield );
        $arr = array();
        foreach ( $_FIELDNAMES AS $field => $bitvalue )
        {
            if ( $bitfield & $bitvalue )
            {
                $arr[ "$field" ] = 1;
            }
            else
            {
                $arr[ "$field" ] = 0;
            }
        }
        return $arr;
    }

    // ============================================================
    // takes an array and returns the bitwise value
    // ============================================================
    /**
     * @param array $arr
     * @param array $_FIELDNAMES
     * @param int $unset
     * @return int
     */
    public static function convert_array_to_bits( $arr, $_FIELDNAMES, $unset = 0 )
    {
        $bits = 0;

        foreach ( $_FIELDNAMES AS $fieldname => $bitvalue )
        {

            if ( $arr[ $fieldname ] == 1 )
            {
                $bits += $bitvalue;
            }

            if ( $unset )
            {
                unset( $arr[ $fieldname ] );
            }
        }

        return $bits;
    }

}

?>