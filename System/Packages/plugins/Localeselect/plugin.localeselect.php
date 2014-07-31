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
 * @file        plugin.localeselect.php
 */
class LocaleselectPlugin extends Plugin
{

    public $is_runnable = true;

    public $is_configurable = false;

    public function __construct()
    {
        parent::__construct();
    }

    public function callMethod( $method )
    {
        $this->$method();
    }

    public function run()
    {

        $cache = Cache::get( 'LocaleselectPlugin' );
        if ( !is_array( $cache ) )
        {
            $data = array();
            $data[ 'guilocales' ] = $this->db->query( 'SELECT * FROM %tp%locale WHERE contentlanguage = 1 ORDER BY title' )->fetchAll();
            foreach ( $data[ 'guilocales' ] as $idx => $r )
            {
                $r[ 'active' ] = false;

                if ( $r[ 'lang' ] == CONTENT_TRANS )
                {
                    $r[ 'active' ] = true;
                }

                $r[ 'location' ] = 'index.php?setlocale=' . $r[ 'code' ] . '&amp;ajax=1';
                $r[ 'icon' ] = HTML_URL . 'img/flags/' . $r[ 'flag' ];
                $data[ 'guilocales' ][ $idx ] = $r;
            }

            Cache::write( 'LocaleselectPlugin', $data );
        }
        else
        {
            $data = $cache;
        }

        $cache = $this->renderTemplate( $data, 'run', null );
        return $cache;
    }

}

?>