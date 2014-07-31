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
 * @file        UrlMapper.php
 *
 */
class UrlMapper
{

    protected $db = null;

    private $urlmap = null;

    private $extraParams = array();

    private $modules;

    /**
     *
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     *
     * @internal param int $siteID
     * @internal param int $modulID
     */
    public function regenerateMap()
    {
        
    }

    /**
     *
     * @param integer $siteID
     * @param $modul
     * @internal param int $modulID
     */
    public function regenerate( $siteID, $modul )
    {
        $row = $this->getModulID( $modul );

        // Clean
        $this->db->delete( '%tp%urlmap' )->where( 'modulid', '=', $row[ 'id' ] )->execute();


        $aliasDB = new AliasRegistry();
        $result = $aliasDB->getByModulId( $row[ 'id' ] );


        $map = array();

        if ( ($total = $result->rowCount()) > 0 )
        {

            $tmp = array();

            $i = 1;
            foreach ( $result->fetchAll() as $r )
            {
                $strUrl = $row[ 'module' ] . ($r[ 'action' ] !== 'index' ? '/' . $r[ 'action' ] : '') . '/' . (strlen( $r[ 'alias' ] ) ? $r[ 'alias' ] : $r[ 'contentid' ]) . (strlen( $r[ 'suffix' ] ) ? '.' . $r[ 'suffix' ] : '.' . Settings::get( 'mod_rewrite_suffix', 'html' ));

                if ( $i == 500 )
                {
                    $this->db->
                            insert( '%tp%urlmap' )->
                            values( $tmp )->execute();
                    $tmp = array();
                    $i = 0;
                }

                $i++;
                $tmp[] = array(
                    'itemid'     => $r[ 'contentid' ],
                    'modulid'    => $row[ 'id' ],
                    'url'        => $strUrl,
                    'controller' => $row[ 'module' ],
                    'action'     => $r[ 'action' ] );
            }


            if ( count( $tmp ) )
            {
                $this->db->
                        insert( '%tp%urlmap' )->
                        values( $tmp )->execute();

                $tmp = null;
            }


            $result = null;
        }
    }

    /**
     *
     * @param integer $siteID
     * @param $modul
     * @return array|mixed|null
     */
    public function getSiteMap( $siteID, $modul )
    {
        $row = $this->getModulID( $modul );
        $map = Cache::get( $row[ 'module' ] . '-' . $row[ 'id' ], 'data/urlmaps' );

        if ( !$map )
        {
            $result = $this->db->query( 'SELECT * FROM %tp%urlmap WHERE modulid=?', $row[ 'id' ] );

            $map = array();
            if ( $result->rowCount() > 0 )
            {
                foreach ( $result->fetchAll() as $r )
                {
                    $map[ 'by_url' ][ $r[ 'url' ] ] = array(
                        'itemid'     => $r[ 'itemid' ],
                        'controller' => $r[ 'controller' ],
                        'action'     => $r[ 'action' ]
                    );

                    $map[ 'by_id' ][ $r[ 'itemid' ] ] = array(
                        'url' => $r[ 'url' ] );
                }

                Cache::write( $row[ 'module' ] . '-' . $row[ 'id' ], $map, 'data/urlmaps' );
                $result = null;
            }
        }

        return $map;
    }

    /**
     *
     * @param string $modul
     */
    public function getModulID( $modul )
    {
        if ( isset( $this->modules[ $modul ] ) )
        {
            return $this->modules[ $modul ];
        }

        $m = new Module();
        $this->modules[ $modul ] = $m->getModul( $modul );
        return $this->modules[ $modul ];
    }

    /**
     *
     * @param integer $id
     * @return null|array
     */
    private function getModulById( $id )
    {
        foreach ( $this->modules as $r )
        {
            if ( $r[ 'id' ] === $id )
            {
                return $r;
            }
        }

        return null;
    }

}
