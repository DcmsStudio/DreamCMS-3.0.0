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
 * @file        Tracking.php
 *
 */
class Tracking extends Tracking_Abstract
{

    /**
     * @var int
     */
    private static $_trackID = 0;

    /**
     *
     */
    protected static function initTracker()
    {
        self::init();

        list($_ip, $proxy) = self::getIP();

        // herkunft aus DB holen (ip2country)
        if ( $_ip != '127.0.0.1' )
        {
            self::$countrycode = self::getCountry( $_ip );
        }

        if ( !empty( $_ip ) )
        {
            self::$ip = (int) ip2long( $_ip );
        }
        else
        {
            self::$ip = "0";
        }

        if ( !empty( $proxy ) && $proxy !== $_ip )
        {
            self::$proxyIp = $proxy;
            self::$prxdomain = self::getHostname( $proxy );
        }

        if ( self::$prxdomain )
        {
            self::$host_domain = self::$prxdomain;
        }
        else
        {
            self::$host_domain = self::getHostname( $_ip );
        }

        // Opreration system
        self::$os = Tracking_Os::getOS();

        // Spider
        self::$spider = Tracking_Spider::getSpider();

        // get the Browser if not a Spider
        if ( self::$spider === null )
        {
            Tracking_Browser::getBrowser();
        }


        // Extract search query from search engines
        if ( self::$fullref != '' )
        {
            Tracking_Spider::getSpiderRef();
        }
    }

    /**
     * @return array|null
     */
    public static function getSiteHits()
    {
        if ( self::$getSiteHits !== null )
        {
            return self::$getSiteHits;
        }

        $db = Database::getInstance();
        $ts = time();

        $d = (int)date( 'd', $ts  );
        $m = (int)date( 'n', $ts  );
        $y = (int)date( 'Y', $ts  );
        $h = (int)date( 'H', $ts  );

        $ret = array();
        $ret[ 'total' ] = $db->query( 'SELECT SUM(visitors) AS uiq_visitors FROM %tp%statistik_total LIMIT 1' )->fetch();

        $sql = "SELECT *, SUM(hits) AS totalhits FROM %tp%statistik_total WHERE day=? AND month=? AND year=? GROUP BY id LIMIT 1";
        $ret[ 'today' ] = $db->query( $sql, $d, $m, $y )->fetch();

        self::$getSiteHits = $ret;

        return $ret;
    }

    /**
     * @return bool
     */
    public static function track()
    {
        if (defined('ADM_SCRIPT'))
        {
            return true;
        }

        $db = Database::getInstance();
        self::initTracker();

        $ts = time();

        $d = (int)date( 'd', $ts  );
        $m = (int)date( 'n', $ts  );
        $y = (int)date( 'Y', $ts  );
        $h = (int)date( 'H', $ts  );

        if ( $h < 10 )
        {
            $h = '0' . $h;
        }

        $insert_id = 0;
        $is_insert = false;


        // Session uniqueHits exists only by Clients not for Spiders
        $updateUniqueHits = true;

        if ( Session::get( 'uniqueHits' ) === true )
        {
            // Reale Visits
            if ( Session::get( 'trackID' ) > 0 )
            {
                //Database::getInstance()->query('UPDATE %tp%statistik_total SET hits=hits+1 WHERE id=?', Session::get('trackID'));
                $updateUniqueHits = false;
            }
        }


        $ins = $db->query( 'SELECT * FROM %tp%statistik_total WHERE `year`= ? AND `month`= ? AND `day`= ? ', $y, $m, $d )->fetch();

        $_h = 'h' . $h;

        if ( $ins[ 'id' ] )
        {
            $db->query( "UPDATE %tp%statistik_total SET {$_h} = {$_h}+1, `hits`=hits+1" . ($updateUniqueHits ? ', uniquehits=uniquehits+1' : '') . " WHERE id = ?", $ins[ 'id' ] );
            $insert_id = $ins[ 'id' ];
            $is_insert = false;
        }
        else
        {
            $db->query( "INSERT INTO %tp%statistik_total ({$_h},hits,`day`,`month`,`year`,visitors,uniquehits) VALUES(1,1,?,?,?,1,1)", $d, $m, $y );
            $insert_id = $db->insert_id();
            $is_insert = true;
        }


        $spiderid = 0;
        $browserid = 0;
        $visitorid = 0;
        $osid = 0;
        $reffererid = 0;
        $countryid = 0;


        // Is new Track day
        if ( $is_insert )
        {
            if ( self::$spider !== null )
            {
                $db->query( 'INSERT INTO %tp%statistik_spiders (`spiderkey`, `hits`, `statid`, `time`) VALUES(?,1,?,?)', self::$spider, $insert_id, $ts );
                $spiderid = $db->insert_id();
            }
            else
            {
                if ( self::$browser !== null )
                {
                    if ( self::$browser_version !== null )
                    {
                        $db->query( 'INSERT INTO %tp%statistik_browser (`browkey`,`version`,`statid`,`time`,`hits`)
								VALUES(?,?,?,?,?)', self::$browser, self::$browser_version, $insert_id, $ts, 1 );
                        $browserid = $db->insert_id();
                    }
                    else
                    {
                        $db->query( 'INSERT INTO %tp%statistik_browser (`browkey`, `version`,`statid`,`time`,`hits`)
								VALUES(?,?,?,?,?)', self::$browser, '', $insert_id, $ts, 1 );
                        $browserid = $db->insert_id();
                    }
                }

                if ( self::$os !== null )
                {
                    $db->query( 'INSERT INTO %tp%statistik_os (`oskey`,`hits`,`statid`,`time`)
							VALUES(?,?,?,?)', self::$os, 1, $insert_id, $ts );
                    $osid = $db->insert_id();
                }

                if ( self::$countrycode !== null )
                {
                    $db->query( 'INSERT INTO %tp%statistik_country (`langkey`,`hits`,`statid`,`time`)
							VALUES(?,?,?,?)', self::$countrycode, 1, $insert_id, $ts );
                    $countryid = $db->insert_id();
                }
            }
        }
        else
        {
            // Is update Track day
            if ( self::$spider !== null )
            {
                $sr = $db->query( 'SELECT * FROM %tp%statistik_spiders WHERE spiderkey = ? AND statid = ?', self::$spider, $insert_id )->fetch();

                if ( !$sr[ 'id' ] )
                {
                    $db->query( 'INSERT INTO %tp%statistik_spiders (`spiderkey`, `hits`, `statid`, `time`) VALUES(?,?,?,?)', self::$spider, 1, $insert_id, $ts );
                    $spiderid = $db->insert_id();
                }
                else
                {
                    $spiderid = $sr[ 'id' ];
                    $db->query( 'UPDATE %tp%statistik_spiders SET `hits` = `hits`+1 WHERE id = ?', $spiderid );
                }
            }
            else
            {

                if ( self::$browser !== null )
                {
                    if ( self::$browser_version !== null )
                    {
                        $br = $db->query( 'SELECT * FROM %tp%statistik_browser
                                            WHERE 
                                            `browkey` = ? AND
                                            `version` = ? AND 
                                            statid = ? LIMIT 1', self::$browser, self::$browser_version, $insert_id )->fetch();
                    }
                    else
                    {
                        $br = $db->query( 'SELECT * FROM %tp%statistik_browser
                                            WHERE 
                                            `browkey` = ? AND
                                            statid = ? LIMIT 1', self::$browser, $insert_id )->fetch();
                    }


                    if ( !$br[ 'id' ] )
                    {

                        $db->query( 'INSERT INTO %tp%statistik_browser (`browkey`,`version`,`statid`,`time`,`hits`)
								VALUES(?,?,?,?,?)', self::$browser, (self::$browser_version !== null ? self::$browser_version : '' ), $insert_id, $ts, 1 );

                        $browserid = $db->insert_id();
                    }
                    else
                    {
                        $browserid = $br[ 'id' ];
                        $db->query( 'UPDATE %tp%statistik_browser SET `hits` = `hits`+1 WHERE id = ?', $browserid );
                    }
                }


                if ( self::$os !== null )
                {

                    $or = $db->query( 'SELECT * FROM %tp%statistik_os WHERE oskey = ? AND statid = ? LIMIT 1', self::$os, $insert_id )->fetch();
                    if ( !$or[ 'id' ] )
                    {
                        $db->query( 'INSERT INTO %tp%statistik_os (`oskey`,`hits`,`statid`,`time`)
							VALUES(?,?,?,?)', self::$os, 1, $insert_id, $ts );

                        $osid = $db->insert_id();
                    }
                    else
                    {
                        $osid = $or[ 'id' ];
                        $db->query( 'UPDATE %tp%statistik_os SET `hits` = `hits`+1 WHERE id =?', $osid );
                    }
                }

                if ( self::$countrycode !== null )
                {

                    $lr = $db->query( 'SELECT * FROM %tp%statistik_country WHERE langkey = ? AND statid = ? LIMIT 1', self::$countrycode, $insert_id )->fetch();

                    if ( !$lr[ 'id' ] )
                    {
                        $db->query( 'INSERT INTO %tp%statistik_country (`langkey`,`hits`,`statid`,`time`)
							VALUES(?,?,?,?)', self::$countrycode, 1, $insert_id, $ts );
                        $countryid = $db->insert_id();
                    }
                    else
                    {
                        $countryid = $lr[ 'id' ];
                        $db->query( 'UPDATE %tp%statistik_country SET `hits` = `hits`+1 WHERE id = ?', $countryid );
                    }
                }
            }
        }

        /**
         * Ergebnis von Suchmaschienen Refferer
         * (Client Click bei Suchmaschiene)
         */
        if ( self::$sphrase !== null )
        {
            $db->query( 'INSERT INTO %tp%statistik_spiderkeys (`spiderid`,`timestamp`,`keyword`,`hits`)
                    VALUES(?,?,?,?)', $spiderid, $ts, self::$sphrase, 1 );
        }


        $update_visitors = false; // Reale Visits
        // Track Visitor
        if ( $is_insert )
        {
            if ( !empty( self::$prxdomain ) )
            {
                $db->query( 'INSERT INTO %tp%statistik_visitors (ip,`host`,isproxy,statid,`time`,browserid,osid,countryid)
                             VALUES(?, ?, ?, ?, ?, ?, ?, ?)', self::$ip, self::$prxdomain, 1, $insert_id, $ts, $browserid, $osid, $countryid );
                $visitorid = $db->insert_id();
            }
            else
            {


                $db->query( 'INSERT INTO %tp%statistik_visitors (ip,`host`,isproxy,statid,`time`,browserid,osid,countryid)
                             VALUES(?, ?, ?, ?, ?, ?, ?, ?)', self::$ip, self::$host_domain, 0, $insert_id, $ts, $browserid, $osid, $countryid );
                $visitorid = $db->insert_id();
            }

            $update_visitors = true; // Reale Visits
        }
        else
        {
            $vis = $db->query( 'SELECT id FROM %tp%statistik_visitors WHERE statid=? AND ip=?', $insert_id, self::$ip )->fetch();
            if ( !$browserid )
            {
                $browserid = 0;
            }

            if ( !$vis[ 'id' ] )
            {
                if ( !empty( self::$prxdomain ) )
                {
                    $db->query( 'INSERT INTO %tp%statistik_visitors (ip,`host`,isproxy,statid,`time`,browserid,osid,countryid)
                                VALUES(?, ?, ?, ?, ?, ?, ?, ?)', self::$ip, self::$prxdomain, 1, $insert_id, $ts, $browserid, $osid, $countryid );
                    $visitorid = $db->insert_id();
                }
                else
                {

                    $db->query( 'INSERT INTO %tp%statistik_visitors (ip,`host`,isproxy,statid,`time`,browserid,osid,countryid)
                                VALUES(?, ?, ?, ?, ?, ?, ?, ?)', self::$ip, self::$host_domain, 0, $insert_id, $ts, $browserid, $osid, $countryid );
                    $visitorid = $db->insert_id();
                }

                $update_visitors = true; // Reale Visits
            }
            else
            {
                $visitorid = $vis[ 'id' ];
                $update_visitors = false; // Reale Visits
            }
        }

        $this_site = preg_replace( '/^(https|http)?:\/\/(www\.)?/isU', '', Settings::get( 'portalurl', '' ) );

        if ( self::$fullref != "" &&
                (
                !preg_match( "!^https?://" . $this_site . ".*?!is", self::$fullref ) &&
                !preg_match( "!^https?://www\." . $this_site . ".*?!is", self::$fullref )
                )
        )
        {
            $re = $db->query( 'SELECT id FROM %tp%statistik_refferer
                               WHERE statid = ? AND refferer = ? AND browserid = ? AND osid = ? AND langid = ? AND visitorid = ?', $insert_id, self::$fullref, $browserid, $osid, $countryid, $visitorid )->fetch();

            if ( !$re[ 'id' ] )
            {
                $db->query( 'INSERT INTO %tp%statistik_refferer (refferer, hits, statid, time, browserid, visitorid, osid, langid)
                             VALUES(?, ?, ?, ?, ?, ?, ?, ?)', self::$fullref, 1, $insert_id, $ts, $browserid, $visitorid, $osid, $countryid );
                $reffererid = $db->insert_id();
            }
            else
            {
                $sql = "UPDATE %tp%statistik_refferer SET hits=hits+1 WHERE id=" . $re[ 'id' ];
                $db->query( $sql );
                $reffererid = $re[ 'id' ];
            }
        }

        if ( $reffererid && $visitorid )
        {
            $db->query( 'UPDATE %tp%statistik_visitors SET refid=? WHERE id = ?', $reffererid, $visitorid );
        }

        // Reale Visits
        if ( $update_visitors )
        {
            $db->query( 'UPDATE %tp%statistik_total SET visitors=visitors+1 WHERE id=?', $insert_id );
        }


        // stop Tracking Clients
        if ( self::$spider === null )
        {
            Session::save( 'uniqueHits', true );
            Session::save( 'trackID', $insert_id );

            //Session::write();
        }


        self::$_trackID = $insert_id;

        return true;
    }

    /**
     *
     * @return boolean
     */
    public static function trackDisplaySize()
    {
        if (defined('ADM_SCRIPT'))
        {
            return true;
        }

        $db = Database::getInstance();

        $display = HTTP::input( 'screenXY' );
        $stat_total_id = (int)Session::get( 'trackID'  );

        if ( !$stat_total_id || trim( $display ) == ';' || $display == '' && !self::$_trackID )
        {
            return false;
        }

        $size = explode( ';', $display );
        if ( !isset( $size[ 0 ] ) || !(int)$size[ 0 ]  || !isset( $size[ 1 ] ) || !(int)$size[ 1 ]  )
        {
            return false;
        }

        if ( !$stat_total_id )
        {
            $stat_total_id = self::$_trackID;
        }

        $_size = $size[ 0 ] . ';' . $size[ 1 ];

        $coun = $db->query( 'SELECT id FROM %tp%statistik_screens WHERE statid=? AND screensize= ?', $stat_total_id, $_size )->fetch();

        if ( !$coun[ 'id' ] )
        {
            $db->query( 'INSERT INTO %tp%statistik_screens (screensize,hits,statid,time) VALUES(?,?,?,?)', $_size, 1, $stat_total_id, time() );
        }
        else
        {
            $db->query( 'UPDATE %tp%statistik_screens SET hits=hits+1 WHERE id=?', $coun[ 'id' ] );
        }

        return true;
    }

}
