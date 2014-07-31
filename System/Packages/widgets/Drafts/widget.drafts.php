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
 * @category    Widget s
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        widget.drafts.php
 */
class DraftsWidget extends Widget_Controller_Abstract
{

    public function DraftsWidget( $id )
    {
        $this->getUserConfig( $id );
    }

    public function getWidgetConfig( &$data )
    {
        exit;
    }

    public function getError()
    {
        
    }

    public function getData()
    {
        $db = Database::getInstance();

        $sql = "SELECT d.*, u.username FROM %tp%drafts AS d
				LEFT JOIN %tp%users AS u ON(u.userid=d.userid)
				ORDER BY `timestamp` DESC";
        $data = $db->query( $sql )->fetchAll();

        foreach ( $data as $idx => $r )
        {
            $data[ $idx ][ 'date' ] = date( 'd.m.Y, H:i:s', $r[ 'timestamp' ] );
        }




        $rs = array();
        $rs[ 'drafts' ] = $data;

        return $this->out( 'drafts', $rs );
    }

}

?>