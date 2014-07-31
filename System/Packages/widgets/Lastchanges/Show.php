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
 * @file        Show.php
 */
class Widget_Lastchanges_Show extends Widget
{

    public function getData()
    {
        $db = Database::getInstance();

        $limit = ( intval( $this->getConfig( 'limit' ) ) > 0 ? intval( $this->getConfig( 'limit' ) ) : 5);

        $sql = "SELECT l.*, u.username FROM %tp%last_edit AS l 
				LEFT JOIN %tp%users AS u ON(u.userid=l.userid)
				GROUP BY l.contentlocation
				ORDER BY l.`timestamp` DESC 
				LIMIT 0, " . $limit;

        $data = $db->query( $sql )->fetchAll();
        foreach ( $data as $idx => $r )
        {
            $data[ $idx ][ 'contentlocation' ] = preg_replace( '/.*\/admin\.php/', 'admin.php', $r[ 'contentlocation' ] );
            $data[ $idx ][ 'date' ] = date( 'd.m.Y, H:i:s', $r[ 'timestamp' ] );
        }

        $rs = array();
        $rs[ 'contentchanges' ] = $data;

        return $this->setWidgetData( $rs );

        return $this->out( 'lastchanges', $rs );
    }

}

?>