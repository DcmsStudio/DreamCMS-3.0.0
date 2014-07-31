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
 * @file        Editmod.php
 */
class Addon_Forum_Action_Editmod extends Addon_Forum_Helper_Base
{

    public function execute()
    {
        if ( $this->isFrontend() )
        {
            return;
        }




        if ( $this->_post( 'getUser' ) )
        {
            $result = User::findUser( $this->_post( 'q' ) );
            $tmp = array();
            foreach ( $result as &$r )
            {
                $tmp[] = array(
                    'label' => $r[ 'username' ],
                    'id'    => $r[ 'userid' ] );
            }
            unset( $result );
            echo Library::json( array(
                'success' => true,
                'items'   => $tmp ) );
            exit;
        }







        $modid = intval( $this->input( 'id' ) );
        $forumid = intval( $this->input( 'forumid' ) );

        if ( !$forumid )
        {
            Library::sendJson( false, 'Das angegebene Forum existiert nicht.' );
        }


        $data = $this->_post();




        if ( $this->_post( 'send' ) )
        {

            if ( !$data[ 'userid' ] )
            {
                Library::sendJson( false, trans( 'Bitte einen Moderator eintragen bzw. suchen' ) );
            }


            $tmp = array();
            $data[ 'userid' ] = intval( $this->_post( 'userid' ) );
            $data[ 'published' ] = intval( $data[ 'published' ] );
            $data[ 'permissions' ] = is_array( $data[ 'perm' ] ) ? serialize( $data[ 'perm' ] ) : array();
            $data[ 'forum' ] = $this->model->getForumById( $forumid );
            $data[ 'user' ] = User::getUserById( $data[ 'userid' ] );
            $newid = $this->model->saveModerator( $modid, $data );




            //   $cfgMods = Settings::get('');


            $this->updateModeratorsCache();



            if ( $modid )
            {
                Library::sendJson( true, trans( 'Der Forum Moderator `%s` wurde dem Forum `%s` hinzugefügt' ), $newid );
            }

            Library::sendJson( true, trans( 'Der Forum Moderator `%s` wurde für das Forum `%s` aktualisiert' ), $newid );

            exit;
        }




        $data[ 'forum' ] = $this->model->getForumById( $forumid );
        $data[ 'modperm' ] = array();

        if ( $modid )
        {
            $data[ 'mod' ] = $this->model->getModeratorByID( $modid, $forumid );
            $data[ 'modperm' ] = $data[ 'mod' ][ 'permissions' ] ? unserialize( $data[ 'mod' ][ 'permissions' ] ) : array();
        }




        foreach ( $this->modPermissions as $name => &$r )
        {
            $r[ 'name' ] = $name;

            if ( isset( $data[ 'modperm' ][ $name ] ) )
            {
                $r[ 'value' ] = $data[ 'modperm' ][ $name ];
            }
            else
            {
                $r[ 'value' ] = isset( $data[ 'modperm' ][ $name ][ 'default' ] ) ? $data[ 'modperm' ][ $name ][ 'default' ] : false;
            }
        }
        $data[ 'permissions' ] = $this->modPermissions;



        Library::addNavi( trans( 'Foren' ) );
        Library::addNavi( sprintf( trans( 'Moderatoren des Forums `%s` bearbeiten' ), $data[ 'forum' ][ 'title' ] ) );
        $this->Template->process( 'forum/editmoderator', $data, true );
    }

}
