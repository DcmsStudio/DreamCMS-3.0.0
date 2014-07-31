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
 * @package     Importer
 * @version     3.0.0 Beta
 * @category    Config
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Base.php
 */
class Widget_Messenger_Show extends Widget
{

    /**
     *
     * @return string 
     */
    public function getData()
    {
        $params[ 'page' ] = 1;
        $params[ 'limit' ] = 5;
        $params[ 'order' ] = '';
        $params[ 'sort' ] = '';
        $params[ 'folder' ] = 1;


        $messi = Module::getInstance( 'Messenger' );

        if ( !$messi )
        {
            return $this->setWidgetData( array() );
        }

        $messages = Model::getModelInstance( 'Messenger' )->getMessages( $params );


        $data = array();
        foreach ( $messages as $idx => $r )
        {
            $today = mktime( 0, 0, 0, date( "m" ), date( "d" ), date( "Y" ) );
            $date = $r[ 'sendtime' ];
            if ( $date < $today )
            {
                $date = date( 'd.m.Y, H:i:s', $date );
            }
            else
            {
                $date = date( 'H:i:s', $date );
            }

            $r[ 'time' ] = $date;
            $r[ 'is_read' ] = ($r[ 'readtime' ] > 0 ? true : false);
            $data[ 'messages' ][] = $r;
        }


        $data[ 'container_id' ] = 'wdgt-' . $this->getName() . '-' . $this->getID();

        return $this->setWidgetData( $data );
    }

}
