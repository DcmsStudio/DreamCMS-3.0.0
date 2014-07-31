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
 * @file        Mysql.php
 *
 */
class Database_Adapter_Pdo_Mysql
{

    /**
     * @var Database_Adapter_Pdo
     */
    protected $_pdo;

    /**
     * @var
     */
    protected $_result;

    /**
     * @var null
     */
    protected static $_connect = null;

    /**
     * @param Database_Adapter_Pdo $pdo
     */
    public function __construct( Database_Adapter_Pdo $pdo )
    {
        $this->_pdo = &$pdo;

        return $this;
    }

    public function connect()
    {
        $_port = $this->_pdo->getPort();

        $port = (isset( $_port ) && !empty( $_port ) ? ';port=' . $_port : ';port=');


        $database = $this->_pdo->getDatabaseName();

        $_hostname = $this->_pdo->getHostname();
        $_username = $this->_pdo->getUsername();
        $_password = $this->_pdo->getPassword();
        $_charset = $this->_pdo->getCharset();

        $initOptions = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING );

        if ( !empty( $_charset ) && $_charset !== '' )
        {
            #$initOptions[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES \''. $_charset .'\'';
        }


        $hash = md5( $_hostname . $port . $database . $_username . $_password );

        if ( isset( self::$_connect[ $hash ] ) )
        {
            return self::$_connect[ $hash ];
        }


        try
        {
            self::$_connect[ $hash ] = new PDO( 'mysql:host=' . $_hostname . $port . ';dbname=' . $database . '', $_username, $_password, $initOptions );


            self::$_connect[ $hash ]->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            self::$_connect[ $hash ]->setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC );
        }
        catch ( PDOException $e )
        {
            die( '<h1>Database Error</h1><h3>'.trans('Fehler beim Aufbau einer Datenbankverbindung').'</h3><code>'.$e->getMessage().'</code>' );
            throw new BaseException( $e->getMessage(), 'SQL' );

            //throw new pdoDbException($e);
        }

        if ( !self::$_connect[ $hash ] instanceof PDO )
        {
            die( 'Invalid Database connect!' );
        }


        return self::$_connect[ $hash ];
    }

    /**
     * @param string $charset
     */
    public function _setCharset( $charset = 'utf8' )
    {
        self::$_connect[ $hash ]->exec( 'SET NAMES ' . $charset );
    }

    /**
     * @param $param
     */
    public function selectDb( $param )
    {
        return;
    }

    /**
     * Version number query string
     * @return    string
     */
    public function version()
    {
        return "SELECT version() AS `version`";
    }

}

?>