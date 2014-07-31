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
 * @file        Publishforum.php
 */
class Addon_Forum_Action_Publishforum extends Addon_Forum_Helper_Base
{

    public function execute()
    {
        if ( $this->isFrontend() )
        {
            return;
        }

        $id = intval( HTTP::input( 'id' ) );

        $forum = $this->db->query( "SELECT ft.lang, ft.title, f.published FROM %tp%board AS f
                                   LEFT JOIN %tp%board_trans AS ft ON(ft.forumid = f.forumid AND IF(ft.lang != ?, ft.lang = f.corelang, ft.lang = ?))
                                   WHERE f.forumid = ?", CONTENT_TRANS, CONTENT_TRANS, $id )->fetch();

        if ( $forum[ 'published' ] )
        {
            $this->db->query( "UPDATE %tp%board SET published = 0 WHERE forumid = ?", $id );

            Library::log( 'Unpublish the Forum ' . $forum[ 'title' ] );
            Library::sendJson( true, '0' );
        }
        else
        {
            $this->db->query( "UPDATE %tp%board SET published = 1 WHERE forumid = ?", $id );
            Library::log( 'Publish the Forum ' . $forum[ 'title' ] );
            Library::sendJson( true, '1' );
        }
    }

}
