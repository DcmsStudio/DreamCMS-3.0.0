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
 * @file        Action.php
 *
 */
class Action extends Loader
{

    /**
     * Current object instance (do not remove)
     * 
     * @var Action
     */
    protected static $objInstance;

    /**
     * @var null
     */
    protected static $controllerpermissions = null;

    /**
     * Return the current object instance (Singleton)
     *
     * @return Action
     */
    public static function getInstance()
    {
        if ( !is_object( self::$objInstance ) )
        {
            self::$objInstance = new Action();
            self::$objInstance->loadPermissionOptions();
        }

        return self::$objInstance;
    }

    /**
     * load the controller/action permissions
     * store all permissions in a cache file
     *
     * field permission
     * field login          login is required
     * 
     * 
     * @param boolean $frontend
     */
    public function loadPermissionOptions( $frontend = false )
    {
        $isBackend = 0;
        $actions = null;

        if ( $this->getApplication()->getMode() === Application::FRONTEND_MODE || $frontend === true )
        {
            // Frontend permissions
            $isBackend = 0;
            $actions = Cache::get( 'frontend_actions' );
        }
        else
        {
            // Backend permissions
            $isBackend = 1;
            $actions = Cache::get( 'backend_actions' );
        }

        if ( !is_null($actions) && empty( $actions ) )
        {
            $actions = array();
            $modules = $this->getApplication()->getModulRegistry();

            foreach ( $modules as $modulKey => $modul )
            {
                if ( isset( $modul[ 'permissions' ][ $this->getApplication()->getMode() ] ) )
                {
                    foreach ( $modul[ 'permissions' ][ $this->getApplication()->getMode() ] as $action => $_values )
                    {
                        $actions[ strtolower( $modulKey ) ][ $action ] = $_values;
                    }
                }
            }

            if ( $isBackend )
            {
                Cache::write( 'backend_actions', $actions );
            }
            else
            {
                Cache::write( 'frontend_actions', $actions );
            }
        }

        self::$controllerpermissions = $actions;
    }

    /**
     *
     * @param bool|string $action
     * @return bool returns false as default
     */
    public static function requireLogin( $action = false )
    {
        if ( $action !== false && is_string( $action ) && strpos( $action, '/' ) !== false )
        {
            list($controller, $event) = explode( '/', $action );


            if ( isset( self::$controllerpermissions[ $controller ] ) )
            {
                if ( isset( self::$controllerpermissions[ $controller ][ $event ][ 'requirelogin' ] ) )
                {
                    return (self::$controllerpermissions[ $controller ][ $event ][ 'requirelogin' ] !== false ? true : false);
                }
            }

            return false;
        }
        else
        {
            $controller = ($action !== false && is_string( $action ) ? $action : (defined('CONTROLLER') ? strtolower( CONTROLLER ) : false) );

            if ( isset( self::$controllerpermissions[ $controller ] ) )
            {
                if ( isset( self::$controllerpermissions[ $controller ][ strtolower( ACTION ) ][ 'requirelogin' ] ) )
                {
                    return (self::$controllerpermissions[ $controller ][ strtolower( ACTION ) ][ 'requirelogin' ] !== false ? true : false);
                }
            }

            return false;
        }
    }

    /**
     *
     * @param bool|string $action
     * @return bool returns false as default
     */
    public static function requirePermission( $action = false )
    {

        if ( $action !== false && is_string( $action ) && strpos( $action, '/' ) !== false )
        {
            list($controller, $event) = explode( '/', $action );

            if ( isset( self::$controllerpermissions[ $controller ] ) )
            {

                if ( isset( self::$controllerpermissions[ $controller ][ $event ][ 'requirepermission' ] ) )
                {
                    return (self::$controllerpermissions[ $controller ][ $event ][ 'requirepermission' ] !== false ? true : false);
                }
            }

            return false;
        }
        else
        {
            $controller = ($action !== false && is_string( $action ) ? $action : (defined('CONTROLLER') ? strtolower( CONTROLLER ) : false) );

            if ( isset( self::$controllerpermissions[ $controller ] ) )
            {
                if ( isset( self::$controllerpermissions[ $controller ][ strtolower( ACTION ) ][ 'requirepermission' ] ) )
                {
                    return (self::$controllerpermissions[ $controller ][ strtolower( ACTION ) ][ 'requirepermission' ] !== false ? true : false);
                }
            }

            return false;
        }

    }

    /**
     * return all controllers with actions
     *
     * @param int $isbackend
     * @return array
     */
    public function getAllActions( $isbackend = 0 )
    {
        return $this->db->query( "SELECT * FROM %tp%actions ORDER BY controller ASC, action ASC" )->fetchAll();
    }

    /**
     * remove all controller permissions for a Usergroup
     *
     * @param integer $groupid
     * @param bool $isbackend
     */
    public function cleanUsergroupControllerPerms( $groupid = 0, $isbackend = true )
    {
        $this->db->query( "DELETE FROM %tp%users_groupactionperms WHERE groupid = ? AND isbackend = ?", $groupid, ($isbackend === true ? 1 : 0 ) );
    }

    /**
     * Save Controller Perms for a Usergroup
     *
     * @param array $data
     * @param integer $groupid
     * @param bool $isbackend (also set backend perms only)
     */
    public function saveUsergroupControllerPerms( &$data, $groupid = 0, $isbackend = true )
    {
        // lock table for read
        //
		// remove first from database
        $this->cleanUsergroupControllerPerms( $groupid, $isbackend );


        $isbackend = ($isbackend ? 1 : 0);

        $actions = $this->getAllActions( $isbackend );

        foreach ( $actions as $r )
        {
            if ( $r[ 'isbackend' ] === $isbackend )
            {
                if ( !$r[ 'permission' ] )
                {
                    //     continue;
                }

                $hasPerm = false;
                if ( (isset( $data[ 'perm' ][ $r[ 'controller' ] ] ) && isset( $data[ 'perm' ][ $r[ 'controller' ] ][ $r[ 'action' ] ] )) || $r[ 'login' ] === 0 )
                {
                    $hasPerm = true;
                }

                if ( $r[ 'permission' ] )
                {
                    $hasPerm = false;

                    if ( isset( $data[ 'perm' ][ $r[ 'controller' ] ] ) && isset( $data[ 'perm' ][ $r[ 'controller' ] ][ $r[ 'action' ] ] ) && $data[ 'perm' ][ $r[ 'controller' ] ][ $r[ 'action' ] ] )
                    {
                        $hasPerm = true;
                    }
                }

                $this->db->query( "INSERT INTO %tp%users_groupactionperms (groupid,controller,action,hasperm,isbackend) VALUES(?, ?, ?, ?, ?)", $groupid, $r[ 'controller' ], $r[ 'action' ], ($hasPerm ? 1 : 0 ), $isbackend );
            }
        }

        $this->savePluginUsergroupControllerPerms( $data, $groupid, $isbackend );
    }

    /**
     *
     * @param array $data
     * @param integer $groupid
     * @param boolean $isbackend (also set backend perms only)
     */
    public function savePluginUsergroupControllerPerms( &$data, $groupid = 0, $isbackend = true )
    {

        Plugin::loadPluginPermissions( $isbackend );
        $_perms = Plugin::getPluginPerms();

        if ( is_array( $_perms[ 'usergroup' ] ) )
        {
            foreach ( $_perms[ 'usergroup' ] as $pluginKey => $p )
            {
                if ( $isbackend )
                {
                    if ( is_array( $p[ 'access-items' ] ) )
                    {
                        foreach ( $p[ 'access-items' ] as $action => $r )
                        {
                            $hasPerm = false;
                            if ( isset( $data[ 'perm' ][ $pluginKey ] ) && isset( $data[ 'perm' ][ $pluginKey ][ $action ] ) )
                            {
                                $hasPerm = true;
                            }


                            $this->db->query( "REPLACE INTO %tp%users_groupactionperms (groupid,controller,action,hasperm,isbackend)
											   VALUES(?, ?, ?, ?, ?)", $groupid, $pluginKey, $action, $hasPerm, $isbackend );
                        }
                    }
                }
                else
                {
                    if ( is_array( $p ) )
                    {
                        foreach ( $p as $action => $r )
                        {
                            $hasPerm = false;
                            if ( isset( $data[ 'perm' ][ $pluginKey ] ) && isset( $data[ 'perm' ][ $pluginKey ][ $action ] ) && $data[ 'perm' ][ $pluginKey ][ $action ] )
                            {
                                $hasPerm = true;
                            }

                            $this->db->query( "REPLACE INTO %tp%users_groupactionperms (groupid,controller,action,hasperm,isbackend)
											   VALUES(?, ?, ?, ?, ?)", $groupid, $pluginKey, $action, $hasPerm, $isbackend );
                        }
                    }
                }
            }
        }
    }

    /**
     * remove all private controller permissions for a user
     *
     * @param integer $userid
     * @param bool $isbackend
     */
    public function cleanUserControllerPerms( $userid = 0, $isbackend = true )
    {
        $this->db->query( "DELETE FROM %tp%users_useractionperms WHERE userid = ? AND isbackend = ?", $userid, ($isbackend ? 1 : 0 ) );
    }

    /**
     * Save private Controller Perms for a User
     *
     * @param array $data
     * @param integer $userid
     * @param bool $isbackend default is true (also set backend perms only)
     */
    public function saveUserControllerPerms( $data, $userid = 0, $isbackend = true )
    {


        // lock table for read
        // remove first from database
        $this->cleanUserControllerPerms( $userid, $isbackend );

        $isbackend = ($isbackend ? 1 : 0);
        $actions = $this->getAllActions();

        foreach ( $actions as $r )
        {
            if ( $r[ 'isbackend' ] === $isbackend )
            {
                if ( !$r[ 'permission' ] )
                {
                    continue;
                }

                $hasPerm = false;
                if ( isset( $data[ 'perm' ][ $r[ 'controller' ] ] ) && isset( $data[ 'perm' ][ $r[ 'controller' ] ][ $r[ 'action' ] ] ) || $r[ 'login' ] === 0 )
                {
                    $hasPerm = true;
                }

                $this->db->query( "INSERT INTO %tp%users_useractionperms (userid,controller,action,hasperm,isbackend) VALUES(?, ?, ?, ?, ?)", $userid, $r[ 'controller' ], $r[ 'action' ], ($hasPerm ? 1 : 0 ), $isbackend );
            }
        }


        Plugin::loadPluginPermissions();
        $_perms = Plugin::getPluginPerms();

        if ( is_array( $_perms[ 'usergroup' ] ) )
        {
            foreach ( $_perms[ 'usergroup' ] as $pluginKey => $p )
            {
                if ( $isbackend )
                {
                    if ( is_array( $p[ 'access-items' ] ) )
                    {
                        foreach ( $p[ 'access-items' ] as $action => $r )
                        {
                            $hasPerm = false;
                            if ( isset( $data[ 'perm' ][ $pluginKey ] ) && isset( $data[ 'perm' ][ $pluginKey ][ $action ] ) )
                            {
                                $hasPerm = true;
                            }


                            $this->db->query( "INSERT INTO %tp%users_useractionperms (userid,controller,action,hasperm,isbackend)
											   VALUES(?, ?, ?, ?, ?)", $userid, $pluginKey, $action( $hasPerm ? 1 : 0  ), $isbackend );
                        }
                    }
                }
                else
                {
                    if ( is_array( $p ) )
                    {
                        foreach ( $p as $action => $r )
                        {
                            $hasPerm = false;
                            if ( isset( $data[ 'perm' ][ $pluginKey ] ) && isset( $data[ 'perm' ][ $pluginKey ][ $action ] ) )
                            {
                                $hasPerm = true;
                            }

                            $this->db->query( "INSERT INTO %tp%users_useractionperms (userid,controller,action,hasperm,isbackend)
											   VALUES(?, ?, ?, ?, ?)", $userid, $pluginKey, $action, ($hasPerm ? 1 : 0 ), $isbackend );
                        }
                    }
                }
            }
        }
    }

    /**
     *
     * @param integer $groupid
     * @param bool $isbackend
     * @return array
     */
    public function getControllerPermsByGroup( $groupid = 0, $isbackend = true )
    {
        return $this->db->query( 'SELECT * FROM %tp%users_groupactionperms WHERE groupid = ? AND isbackend = ?', $groupid, ($isbackend ? 1 : 0 ) )->fetchAll();
    }

    /**
     *
     * @param integer $userid
     * @param bool $isbackend
     * @return array
     */
    public function getControllerPermsByUser( $userid = 0, $isbackend = true )
    {
        return $this->db->query( 'SELECT * FROM %tp%users_useractionperms WHERE userid = ? AND isbackend = ?', $userid, ($isbackend ? 1 : 0 ) )->fetchAll();
    }

    /**
     *
     * @param string $controller
     * @param string $action
     * @param boolean $plugin default is false
     * @return bool
     */
    public function isValid( $controller = null, $action = null, $plugin = false )
    {
        if ( $controller === null )
        {
            $controller = defined( 'CONTROLLER' ) ? strtolower( CONTROLLER ) : null;
        }

        if ( $action === null )
        {
            $action = defined( 'ACTION' ) ? strtolower( ACTION ) : null;
        }

        if ( $controller === null || $action === null )
        {
            return false;
        }

        if ( is_file( MODULES_PATH . ucfirst( $controller ) . '/Action/' . ucfirst( $action ) . '.php' ) )
        {
            return true;
        }

        return false;
    }

    /**
     * Prevent cloning of the object (Singleton)
     */
    final private function __clone()
    {
        
    }

}

?>