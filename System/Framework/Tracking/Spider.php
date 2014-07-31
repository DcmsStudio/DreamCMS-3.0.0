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
 * @file        Spider.php
 *
 */
class Tracking_Spider extends Tracking_Abstract
{

    /**
     * @param null $ua
     * @return int|null|string
     */
    public static function getSpider( $ua = null )
    {
        static $robots;

        if ( !is_array( $robots ) )
        {
            $robots = array();
            include(DATA_PATH . 'counter_data/robots.php');
        }

        $ua = ($ua === null ? self::$ua : $ua);

        foreach ( $robots as $key => $r )
        {
            /*
              $rs     = explode( '|', $r );
              $match  = $rs[ 1 ];
              $_match = null;
             */

            if ( preg_match( '#(' . $r[ 1 ] . ')#is', $ua ) )
            {
                return $key; // return the spider key
            }
        }


        return null;
    }

    /**
     * Parses query terms from referer
     *
     * @param string $referer
     * @return string
     * @access private
     */
    private static function extractSearchTerms( $referer )
    {
        static $qterm = '[\?(?:.+&|)Keywords=(.+?)(?:&|$)]
[\?(?:.+&|)MT=(.+?)(?:&|$)]
[\?(?:.+&|)Q=(.+?)(?:&|$)]
[\?(?:.+&|)QUERY=(.+?)(?:&|$)]
[\?(?:.+&|)Suchwort=(.+?)(?:&|$)]
[\?(?:.+&|)T=(.+?)(?:&|$)]
[\?(?:.+&|)ask=(.+?)(?:&|$)]
[\?(?:.+&|)eingabe=(.+?)(?:&|$)]
[\?(?:.+&|)entry=(.+?)(?:&|$)]
[\?(?:.+&|)general=(.+?)(?:&|$)]
[\?(?:.+&|)heureka=(.+?)(?:&|$)]
[\?(?:.+&|)in=(.+?)(?:&|$)]
[\?(?:.+&|)k=(.+?)(?:&|$)]
[\?(?:.+&|)key=(.+?)(?:&|$)]
[\?(?:.+&|)keys=(.+?)(?:&|$)]
[\?(?:.+&|)keyword=(.+?)(?:&|$)]
[\?(?:.+&|)keywords=(.+?)(?:&|$)]
[\?(?:.+&|)KERESES=(.+?)(?:&|$)]
[\?(?:.+&|)kw=(.+?)(?:&|$)]
[\?(?:.+&|)mots=(.+?)(?:&|$)]
[\?(?:.+&|)motscles=(.+?)(?:&|$)]
[search\?(?:.+&|)p=(.+?)(?:&|$)]
[\?(?:.+&|)pattern=(.+?)(?:&|$)]
[\?(?:.+&|)pgm=(.+?)(?:&|$)]
[\?(?:.+&|)q=(.+?)(?:&|$)]
[\?(?:.+&|)qr=(.+?)(?:&|$)]
[\?(?:.+&|)qry=(.+?)(?:&|$)]
[\?(?:.+&|)qs=(.+?)(?:&|$)]
[\?(?:.+&|)qt=(.+?)(?:&|$)]
[\?(?:.+&|)qu=(.+?)(?:&|$)]
[\?(?:.+&|)query=(.+?)(?:&|$)]
[\?(?:.+&|)query2=(.+?)(?:&|$)]
[\?(?:.+&|)queryterm=(.+?)(?:&|$)]
[\?(?:.+&|)question=(.+?)(?:&|$)]
[\?(?:.+&|)s=(.+?)(?:&|$)]
[\?(?:.+&|)sTerm=(.+?)(?:&|$)]
[\?(?:.+&|)sc=(.+?)(?:&|$)]
[\?(?:.+&|)search=(.+?)(?:&|$)]
[\?(?:.+&|)search2=(.+?)(?:&|$)]
[\?(?:.+&|)searchfor=(.+?)(?:&|$)]
[\?(?:.+&|)searchstr=(.+?)(?:&|$)]
[\?(?:.+&|)searchText=(.+?)(?:&|$)]
[\?(?:.+&|)searchWord=(.+?)(?:&|$)]
[\?(?:.+&|)srch=(.+?)(?:&|$)]
[\?(?:.+&|)stext=(.+?)(?:&|$)]
[\?(?:.+&|)string=(.+?)(?:&|$)]
[\?(?:.+&|)su=(.+?)(?:&|$)]
[\?(?:.+&|)such=(.+?)(?:&|$)]
[\?(?:.+&|)suche=(.+?)(?:&|$)]
[\?(?:.+&|)szukaj=(.+?)(?:&|$)]
[\?(?:.+&|)tx=(.+?)(?:&|$)]
[\?(?:.+&|)tx0=(.+?)(?:&|$)]
[\?(?:.+&|)tx1=(.+?)(?:&|$)]
[\?(?:.+&|)tx2=(.+?)(?:&|$)]
[\?(?:.+&|)what=(.+?)(?:&|$)]
[\?(?:.+&|)word=(.+?)(?:&|$)]
[\?(?:.+&|)words=(.+?)(?:&|$)]
[\?(?:.+&|)wyr=(.+?)(?:&|$)]';

        $lines = explode( "\n", str_replace( "\r", '', $qterm ) );

        foreach ( $lines as $match )
        {
            if ( $match )
            {
                $m = null;
                if ( preg_match( '#' . $match . '#isU', $referer, $m ) )
                {
                    return urldecode( $m[ 0 ] );
                }
            }
        }

        return '';
    }

    public static function getSpiderRef()
    {
        // Come from Search Engiene
        if ( self::$fullref !== null )
        {
            $qt = self::extractSearchTerms( self::$fullref );

            if ( !empty( $qt ) )
            {
                self::$sphrase = $qt;
            }
        }
    }

}
