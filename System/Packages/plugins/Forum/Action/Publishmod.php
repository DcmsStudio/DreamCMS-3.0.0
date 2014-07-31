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
 * @file        Publishmod.php
 */
class Addon_Forum_Action_Publishmod extends Addon_Forum_Helper_Base
{

    public function execute()
    {
        if ( !$this->isFrontend() )
        {
            $forumid = intval( $this->input( 'forumid' ) );
            $id = intval( $this->input( 'id' ) );

            $this->initForumCats();


            $mod = $this->model->getModeratorByID( $id, $forumid );
            $active = $this->model->setModeratorPublishing( $id, $forumid );

            Library::log(
                    sprintf(
                            ($active ? 'user has change forum mod "%s" in forum "%s" publishing to online' : 'user has change forum mod "%s" in forum "%s" publishing to offline' ), $mod[ 'username' ], $this->forum_by_id[ $forumid ][ 'title' ] ) );

            Library::sendJson( true, ($active ? sprintf( trans( '%s wurde als Moderator für das Forum %s aktiviert' ), $mod[ 'username' ], $this->forum_by_id[ $forumid ][ 'title' ] ) : sprintf( trans( '%s wurde als Moderator für das Forum %s deaktiviert' ), $mod[ 'username' ], $this->forum_by_id[ $forumid ][ 'title' ] ) ) );
        }
    }

}
