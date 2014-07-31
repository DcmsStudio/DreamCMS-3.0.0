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
 * @file        Connect.php
 *
 */
class Database_Adapter_Sqlite_Connect
{

    /**
     * @var
     */
    protected $_result;

    /**
     * @var null
     */
    protected static $_connect = null;

    /**
     *
     */
    public function __construct()
    {
        
    }

    /**
     *
     * @param string $_hostname is the database path
     * @param string $_username
     * @param string $_password
     * @param integer $_port
     * @return PDO
     * @throws BaseException
     */
    public function connect( $_hostname, $_username = null, $_password = null, $_port = null )
    {
        $hash = md5( $_hostname . serialize( $options ) );

        if ( isset( self::$_connect[ $hash ] ) )
        {
            return self::$_connect[ $hash ];
        }

        try
        {
	        self::$_connect[ $hash ] = new PDO('sqlite:' . $_hostname);
	        // Set errormode to exceptions
	        self::$_connect[ $hash ]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // self::$_connect[ $hash ] = new SQLite3( $_hostname );
        }
        catch ( Exception $e )
        {
            throw new BaseException( $e->getMessage() );
        }

        return self::$_connect[ $hash ];
    }

    /**
     * @param string $charset
     */
    public function _setCharset( $charset = 'utf-8' )
    {
        // $this->_pdo->connection->exec('SET NAMES ' . $charset);
    }

    /**
     * @param $param
     */
    public function selectDb( $param )
    {
        return;
    }

}
