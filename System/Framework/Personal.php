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
 * @file        Personal.php
 *
 */
class Personal extends Loader
{

    /**
     * @var bool
     */
    protected static $loaded = false;

    /**
     * @var null
     */
    protected static $personalSettings = null;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->initSettings();
    }

    /**
     *
     * @param integer $userid
     */
    public function initSettings( $userid = null )
    {
        if ( self::$loaded && is_array( self::$personalSettings ) )
        {
            return;
        }

        if ( $userid === null )
        {
            $userid = User::getUserId();
        }

        $result = $this->db->query( 'SELECT * FROM %tp%admin_options WHERE userid = ?', $userid )->fetchAll();
        // $data = array();
        foreach ( $result as $r )
        {
            $r[ 'content' ] = !empty( $r[ 'content' ] ) && preg_match( '#^a:\d{1,}:\{.*#', $r[ 'content' ] ) ? unserialize( $r[ 'content' ] ) : $r[ 'content' ];
            self::$personalSettings[ $r[ 'category' ] ][ $r[ 'name' ] ] = $r[ 'content' ];
        }
        $this->db->free();

        self::$loaded = true;
        $result = null;
    }

    /**
     *
     * @param string $category
     * @param string $name
     * @param mixed $value
     */
    public function set( $category, $name, $value = null )
    {
        $userid = User::getUserId();
        self::$personalSettings[ $category ][ $name ] = $value;


        $value = (is_array( $value ) ? serialize( $value ) : (is_string( $value ) ? $value : ''));

        $this->db->query( 'REPLACE %tp%admin_options (userid,category,`name`,`content`, common) VALUES(?,?,?,?,?)', $userid, $category, $name, $value, 0 );
    }

    /**
     *
     * @param string $category
     * @param string $name
     */
    public function remove( $category, $name = null )
    {
        $userid = User::getUserId();

        if ( $name !== null )
        {
            unset( self::$personalSettings[ $category ][ $name ] );
            $this->db->query( 'DELETE FROM %tp%admin_options WHERE userid = ? AND category = ? AND `name` = ?', $userid, $category, $name );
        }
        else
        {
            unset( self::$personalSettings[ $category ] );
            $this->db->query( 'DELETE FROM %tp%admin_options WHERE userid = ? AND category = ?', $userid, $category );
        }
    }

    /**
     *
     * @param string $category
     * @param string $name
     * @param mixed $defaultvalue
     * @return mixed
     */
    public function get( $category, $name, $defaultvalue = null )
    {
        if ( !isset( self::$personalSettings[ $category ] ) )
        {
            self::$personalSettings[ $category ] = array();
            self::$personalSettings[ $category ][ $name ] = $defaultvalue;

            $this->set( $category, $name, $defaultvalue );
        }

        if ( !isset( self::$personalSettings[ $category ][ $name ] ) )
        {
            self::$personalSettings[ $category ][ $name ] = $defaultvalue;
            $this->set( $category, $name, $defaultvalue );
        }

        return self::$personalSettings[ $category ][ $name ];
    }

}
