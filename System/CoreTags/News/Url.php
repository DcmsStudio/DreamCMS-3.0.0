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
 * @package     CoreTags
 * @version     3.0.0 Beta
 * @category    Core Tag
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Url.php
 *
 */
class CoreTag_News_Url extends Provider_Abstract
{

    /**
     * @param $tag
     * @return string
     */
    public function render( $tag )
    {

        $_alias = '';
        $_id    = 0;
        if ( is_numeric( $tag[ 'contentid' ] ) )
        {
            $_id = intval( $tag[ 'contentid' ] );
        }
        elseif ( is_string( $tag[ 'alias' ] ) )
        {
            $_alias = trim( $tag[ 'alias' ] );
            $_alias = preg_replace( '/([^0-9a-z_\-]*)/i', '', $_alias );
        }

        $cache = self::getCacheData( 'news' );

        $output = '';

        if ( $_id > 0 )
        {
            if ( isset( $cache[ $_id ] ) )
            {
                $data = $cache[ $_id ];
            }
            else
            {
                $transq1 = $this->buildTransWhere( 'news', 'n.id', 'nt' );
                $page    = $this->db->query( '
                    SELECT n.id, nt.`lang`, nt.alias, nt.suffix, nt.title
                    FROM %tp%news AS n
                    LEFT JOIN %tp%news_trans AS nt ON(nt.id=n.id)
                    WHERE n.id = ? AND ' . $transq1, $_id )->fetch();

                $cache[ $page[ 'alias' ] ] = $page;
                $cache[ $page[ 'id' ] ]    = $page;
                $data                  = $page;

                self::setCacheData( 'news', $cache );
            }

            if ( isset( $data[ 'id' ] ) )
                $output .= 'news/item/' . $data[ 'alias' ] . ($data[ 'suffix' ] ? '.' . $data[ 'suffix' ] : '.html');
            else
                $output .= '';
        }
        elseif ( $_alias )
        {

            if ( isset( $cache[ $_alias ] ) )
            {
                $data = $cache[ $_alias ];
            }
            else
            {
                $transq1 = $this->buildTransWhere( 'news', 'n.id', 'nt' );
                $page    = $this->db->query( 'SELECT n.id, nt.`lang`, nt.alias, nt.suffix, nt.title
                                                            FROM %tp%news AS n
                                                            LEFT JOIN %tp%news_trans AS nt ON(nt.id=n.id)
                                                            WHERE nt.alias = ? AND ' . $transq1, $_alias )->fetch();

                $cache[ $page[ 'alias' ] ] = $page;
                $cache[ $page[ 'id' ] ]    = $page;
                $data                  = $page;
                self::setCacheData( 'news', $cache );
            }

            if ( isset( $data[ 'id' ] ) )
            {
                $output .= 'news/item/' . $data[ 'alias' ] . ($data[ 'suffix' ] ? '.' . $data[ 'suffix' ] : '.html');
            }
            else
            {
                $output .= '';
            }
        }

        return $output;
    }
}
