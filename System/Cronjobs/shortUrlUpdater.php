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
 * @category    
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        shortUrlUpdater.php
 */

$controller = Registry::getObject( 'Controller' );

if ( ($controller instanceof Controller ) )
{
    /**
     *
     */
    $db = Database::getInstance();
    $rs = $db->query( 'SELECT id FROM %tp%module WHERE pageid = ? AND module = ?', PAGE_ID, 'news' )->fetch();
    $results = $db->query( 'SELECT modulid, contentid, alias, suffix, shorturls FROM %tp%alias_registry WHERE contentid > 0 AND modulid > 0' )->fetchAll();

	set_time_limit(500);

    foreach ( $results as $r )
    {
        $url = Settings::get( 'portalurl' ) . '/news/item/' . $r[ 'alias' ] . '.' . (!empty( $r[ 'suffix' ] ) ? $r[ 'suffix' ] : 'html');

	    if (trim($r['shorturls'])) {
		    $r['shorturls'] = unserialize($r['shorturls']);
	    }

        $controller->getShorturls( $url, $r[ 'contentid' ], $r[ 'modulid' ], $r['shorturls'] );
	    usleep(300000);
    }


    Library::log( 'Short URL Updater has update all urls!', 'info' );
}
else
{
    Library::log( 'Short URL Updater could not update! The Controller is not in the Registry', 'warn' );
}