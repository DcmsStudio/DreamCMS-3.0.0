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
 * @file        LoadBalance.php
 *
 */
class LoadBalance
{

    const SIMPLE_MODE = 0;

    const WEIGHT_MODE = 1;

    const LOAD_MODE = 2;

    /**
     *
     * @var array
     */
    private $config = array();

    /**
     *
     * @var boolean
     */
    private $_enabled = false;

    /**
     *
     * @var boolean
     */
    private $_allowLoadBalance = false;

    /**
     *
     * @var integer
     */
    private $_balanceMode;

    /**
     *
     * @param bool|int $_balanceMode default use simple Load mode
     */
    public function __construct( $_balanceMode = false )
    {

        if ( class_exists( 'Memcache', false ) )
        {
            $this->_allowLoadBalance = true;
        }


        $this->_balanceMode = ($_balanceMode ? $_balanceMode : self::SIMPLE_MODE);


        if ( !$this->_allowLoadBalance && $this->_balanceMode === self::LOAD_MODE )
        {
            $this->_balanceMode = self::SIMPLE_MODE;
        }


        // Config for Simple Load Balancing
        $this->config[ 'slaves' ] = array(
            "10.0.0.1",
            "10.0.0.2",
            "10.0.0.3" );

        /*
          //Config for Load Balancing by Weight

          $this->config['slaves']=array("10.0.0.1"=>2,"10.0.0.2"=>0, "10.0.0.3"=>1);

          //Config for Load Balancing by Load

          $this->config['slaves']=array(
          "10.0.0.1"=>"http://slave1/getload.php",
          "10.0.0.2"=>"http://slave2/getload.php",
          "10.0.0.3"=>"http://slave3/getload.php"
          );
          $this->config['memcache']=array(
          "localhost"=>"11211",
          "slave2", "11211"
          );
         */
    }

    /**
     *
     */
    public function disableBalance()
    {
        $this->_enabled = false;
    }

    /**
     *
     */
    public function enableBalance()
    {
        $this->_enabled = true;
    }

    /**
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->_enabled;
    }

    /**
     *
     * @throws BaseException
     * @return string
     */
    public function getBalance()
    {
        if ( $this->_balanceMode === self::SIMPLE_MODE )
        {
            return $this->SimpleLoadBalanncing();
        }
        else if ( $this->_balanceMode === self::WEIGHT_MODE )
        {
            return $this->LoadBalancingByWeight();
        }
        else if ( $this->_balanceMode === self::LOAD_MODE )
        {
            return $this->LoadBalancingByLoad();
        }


        throw new BaseException( 'Invalid Load-balance mode!' );
    }

    /**
     * This Method checks if a Query
     * can be run on Master or Slaves
     *
     * @param string $sql
     * @return boolean
     */
    public function canUseBalance( $sql )
    {
        $sql = strtolower( substr( trim( $sql ), 0, 6 ) );

        switch ( $sql )
        {
            // selects can be run on slaves, because it's not a modify query
            case "select":
                return true;
                break;

            // insert, update, delete, alter must be executed on master
            default:
                return false;
        }
    }

    /**
     * Simple Load Balanncing
     * Just random Load Balancing
     *
     * @return string ip
     */
    private function SimpleLoadBalanncing()
    {
        $i = count( $this->config[ 'slaves' ] ) - 1;
        $rand = rand( 0, $i );
        return $this->config[ 'slaves' ][ $rand ];
    }

    /**
     * Load Balancing by Weight
     *
     * @return string ip
     */
    private function LoadBalancingByWeight()
    {

        $i = count( $this->config[ 'slaves' ] );
        foreach ( $this->config[ 'slaves' ] as $server => $weight )
        {
            $rand = rand( 1, $i );
            $lb[ $server ] = $rand + $weight;
        }
        arsort( $lb );
        return key( $lb );
    }

    /**
     * LoadBalancingByLoad
     *
     * This method checks the load on n server.
     * Cache the result for 60 sec (because we don't want to ask too many times)
     * Finaly it returns the Server ip with the lowest load
     *
     * @return string ip
     */
    private function LoadBalancingByLoad()
    {
        $memcache = new Memcache;
        foreach ( $this->config[ 'memcache' ] as $memcahe_server => $port )
        {
            $memcache->addServer( $memcahe_server, $port );
        }

        foreach ( $this->config[ 'slaves' ] as $server => $checkloadurl )
        {
            if ( $memcache->get( $server ) )
            {
                $load[ $server ] = $memcache->get( $server );
            }
            else
            {
                $curl = curl_init();
                curl_setopt( $curl, CURLOPT_URL, $checkloadurl );
                curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
                $output = curl_exec( $curl );
                $memcache->set( $memcache, $server, $output, 0, 60 );
                $load[ $server ] = $output;
                curl_close( $curl );
            }
        }
        asort( $load );
        return key( $load );
    }

    /**
     * Get Load simply returns the laod average
     * from the last minute
     *
     * @return float load avg
     */
    public function GetLoad()
    {
        $load = sys_getloadavg();
        return $load[ 0 ];
    }

}
