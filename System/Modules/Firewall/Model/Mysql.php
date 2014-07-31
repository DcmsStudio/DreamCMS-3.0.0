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
 * @package      Firewall
 * @version      3.0.0 Beta
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Mysql.php
 */
class Firewall_Model_Mysql extends Model
{
    /**
     * @param int $id
     * @return array
     */
    public function getIpByID($id)
    {
        return $this->db->query( 'SELECT * FROM %tp%firewall_log WHERE id = ?', $id )->fetch();
    }


    /**
     *
     * @return array
     */
    public function getGridData()
    {

        switch ( strtolower( $GLOBALS[ 'sort' ] ) )
        {
            case 'asc':
                $sort = " ASC";
                break;

            case 'desc':
            default:
                $sort = " DESC";
                break;
        }

        switch ( strtolower( $GLOBALS[ 'orderby' ] ) )
        {

            case 'timestamp':
            default:
                $order = " ORDER BY timestamp";
                break;
            case 'errortype':
                $order = " ORDER BY errortype";
                break;
            case 'dns':
                $order = " ORDER BY dns";
                break;
            case 'ip':
                $order = " ORDER BY ip";
                break;
            case 'useragent':
                $order = " ORDER BY useragent";
                break;
            case 'refferer':
                $order = " ORDER BY refferer";
                break;
            case 'requesturi':
                $order = " ORDER BY requesturi";
                break;
        }

        $limit = $this->getPerpage();
        $page  = $this->getCurrentPage();


        $search = HTTP::input( 'q' );
        $search = trim( (string)strtolower( $search ) );
        $_s     = '';
        if ( $search != '' )
        {
            $search = str_replace( "*", "%", $search );
            $search = $this->db->quote( '%' . $search . '%' );


            switch ( $this->input( 'in' ) )
            {

                case 'refferer':
                    $_s = " WHERE LOWER(refferer) LIKE " . $search;
                    break;
                case 'errortype':
                    $_s = " WHERE LOWER(errortype) LIKE " . $search;
                    break;
                case 'requesturi':
                    $_s = " WHERE LOWER(requesturi) LIKE " . $search;
                    break;
                case 'ip':
                    $_s = " WHERE LOWER(ip) LIKE " . $search;
                    break;
                case 'dns':
                    $_s = " WHERE LOWER(dns) LIKE " . $search;
                    break;
                case 'useragent':
                    $_s = " WHERE LOWER(useragent) LIKE " . $search;
                    break;
                default:
                    $_s = " WHERE LOWER(refferer) LIKE " . $search . "
                    OR LOWER(errortype) LIKE " . $search . "
                    OR LOWER(useragent) LIKE " . $search . "
                    OR LOWER(requesturi) LIKE " . $search . "
                    OR LOWER(ip) LIKE " . $search . "
                    OR LOWER(dns) LIKE " . $search . " ";
                    break;
            }


        }
        $r = $this->db->query( 'SELECT COUNT(id) AS total FROM %tp%firewall_log ' . $_s )->fetch();

        return array(
            'result' => $this->db->query( 'SELECT * FROM %tp%firewall_log ' . $_s . ' ' . $order . $sort . '
                                            LIMIT ' . ( $limit * ( $page - 1 ) ) . "," . $limit )->fetchAll(),
            'total'  => $r[ 'total' ]
        );
    }


    /**
     *
     */
    public function delete($id = 0, $table = null)
    {
        $id    = (int)$this->input( 'id' );
        $ids   = $this->input( 'ids' );
        $multi = false;


        if ( $ids )
        {
            $id    = is_array( $ids ) ? implode( ',', $ids ) : $ids;
            $multi = true;
        }

        if ( !$id )
        {
            Error::raise( "Invalid ID" );
        }

        if ( $multi )
        {
            $sql = 'DELETE FROM %tp%firewall_log WHERE id IN(0,' . $id . ')';
        }
        else
        {
            $sql = 'DELETE FROM %tp%firewall_log WHERE id = ' . $id;
        }

        $this->db->query( $sql );

    }

    /**
     *
     */
    public function clear()
    {
        $this->db->query( 'TRUNCATE TABLE %tp%firewall_log' );

        Library::log( 'Has clear the Firewall Log' );
    }


    public function ban()
    {
        $id    = (int)$this->input( 'id' );
        $ids   = $this->input( 'ids' );
        $multi = false;


        if ( $ids )
        {
            $id    = is_array( $ids ) ? implode( ',', $ids ) : $ids;
            $multi = true;
        }

        if ( !$id )
        {
            Error::raise( "Invalid ID" );
        }

        if ( $multi )
        {

            $this->db->query( 'UPDATE %tp%firewall_log SET blocked = 1 WHERE id IN(0,' . $id . ')' );
            $res = $this->db->query( 'SELECT spammer_ip FROM %tp%spammers WHERE spammer_ip = IN(0,' . $id . ')' )->fetchAll();
            $ids = explode( ',', $id );


            $tmp = array();
            foreach ( $ids as $_id )
            {
                $tmp[ $_id ] = $_id;
            }

            foreach ( $res as $r )
            {
                if ( isset( $tmp[ $r[ 'spammer_ip' ] ] ) )
                {
                    unset( $tmp[ $r[ 'spammer_ip' ] ] );
                }
            }

            $res = $this->db->query( 'SELECT id, ip FROM %tp%firewall_log WHERE id IN(0,' . implode( ',', $tmp ) . ')' )->fetchAll();

            foreach ( $res as $r )
            {
                $r[ 'long_ip' ] = ip2long( $r[ 'ip' ] );
                $rs = $this->db->query( 'SELECT c.* FROM %tp%countries AS c
								LEFT JOIN %tp%ip2nation AS n ON(n.countryid = c.countryid)
								WHERE n.ip < INET_ATON(?)
								ORDER BY n.ip DESC LIMIT 1', $r[ 'ip' ] )->fetch();

                $this->db->query( 'INSERT INTO %tp%spammers (spammer_name,spammer_ip,spammer_iplong,spammer_mail,added,lastvisit,spammer_count,countryid,ispid)
						  VALUES (?,?,?,?,?,?,?,?,?)', '', $r[ 'ip' ], $r[ 'long_ip' ], $r[ 'email' ], TIMESTAMP, 0, 0, $rs['countryid'], 0 );

                $this->db->query( 'UPDATE %tp%firewall_log SET blocked = 1 WHERE ip = ?', $r[ 'ip' ] );
            }

            // $this->db->query('UPDATE %tp%firewall_log SET blocked = 1 WHERE id IN(0,'. implode(',', $tmp).')');

        }
        else
        {
            $r = $this->db->query( 'SELECT id, ip FROM %tp%firewall_log WHERE id = ?', $id )->fetch();

            $r[ 'long_ip' ] = ip2long( $r[ 'ip' ] );

            $rs = $this->db->query( 'SELECT c.* FROM %tp%countries AS c
								LEFT JOIN %tp%ip2nation AS n ON(n.countryid = c.countryid)
								WHERE n.ip < INET_ATON(?)
								ORDER BY n.ip DESC LIMIT 1', $r[ 'ip' ] )->fetch();

            $this->db->query( 'INSERT INTO %tp%spammers (spammer_name,spammer_ip,spammer_iplong,spammer_mail,added,lastvisit,spammer_count,countryid,ispid)
                      VALUES (?,?,?,?,?,?,?,?,?)', '', $r[ 'ip' ], $r[ 'long_ip' ], $r[ 'email' ], TIMESTAMP, 0, 0, $rs['countryid'], 0 );

            $this->db->query( 'UPDATE %tp%firewall_log SET blocked = 1 WHERE ip = ?', $r[ 'ip' ] );
        }

    }
}