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
 * @package     Importer
 * @version     3.0.0 Beta
 * @category    Config
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Base.php
 */
class MailChecker
{

    /**
     * User name 
     * @var string 
     */
    private $_fromName;

    /**
     * Domain name 
     * @var string 
     */
    private $_fromDomain;

    /**
     * SMTP port number 
     * @var int 
     */
    private $_port;

    /**
     * The connection timeout, in seconds. 
     * @var int 
     */
    private $_maxConnectionTimeout;

    /**
     * The timeout on socket connection 
     * @var int 
     */
    private $_maxStreamTimeout;

    private $_isFakeMain = false;

    /**
     *
     */
    public function __construct()
    {
        $this->_fromName = 'noreply';
        $this->_fromDomain = 'localhost';
        $this->_port = 25;
        $this->_maxConnectionTimeout = 15;
        $this->_maxStreamTimeout = 5;
    }

    /**
     * Set email address for SMTP request 
     * @param string $email Email address 
     */
    public function setEmailFrom( $email )
    {
        list($this->_fromName, $this->_fromDomain) = $this->_parseEmail( $email );
    }

    /**
     * Set connection timeout, in seconds. 
     * @param int $seconds 
     */
    public function setConnectionTimeout( $seconds )
    {
        $this->_maxConnectionTimeout = $seconds;
    }

    /**
     * Set the timeout on socket connection 
     * 
     * @param int $seconds 
     */
    public function setStreamTimeout( $seconds )
    {
        $this->_maxStreamTimeout = $seconds;
    }

    /**
     * Validate email address. 
     * 
     * @param string $email 
     * @return boolean  True if valid. 
     */
    public function isValid( $email )
    {
        return (false !== filter_var( $email, FILTER_VALIDATE_EMAIL ));
    }

    /**
     * Get array of MX records for host. Sort by weight information. 
     * 
     * @param string $hostname The Internet host name. 
     * @return array Array of the MX records found. 
     */
    public function getMXrecords( $hostname )
    {
        $mxhosts = array();
        $mxweights = array();
        if ( getmxrr( $hostname, $mxhosts, $mxweights ) )
        {
            array_multisort( $mxweights, $mxhosts );
        }

        /**
         * Add A-record as last chance (e.g. if no MX record is there). 
         * Thanks Nicht Lieb. 
         */
        $mxhosts[] = $hostname;
        return $mxhosts;
    }

    /**
     * @param $email
     * @return bool
     */
    public function isTrashMail( $email )
    {
        global $extraList;

        include DATA_PATH . 'system/trash_mails.php';

        if ( is_array( $extraList ) )
        {

            $domain = explode( '@', $email );

            if ( $domain[ 1 ] && in_array( $domain[ 1 ], $extraList ) )
            {
                return true;
            }
        }

        return false;
    }

    /**
     * check up e-mail 
     * 
     * @param string $email Email address 
     * @return boolean True if the valid email also exist 
     */
    public function check( $email )
    {
        global $extraList;

        $result = false;

        include DATA_PATH . 'system/trash_mails.php';

        if ( is_array( $extraList ) )
        {

            $domain = explode( '@', $email );

            if ( $domain[ 1 ] && in_array( $domain[ 1 ], $extraList ) )
            {
                $this->_isFakeMain = true;
                return false;
            }
        }



        if ( $this->isValid( $email ) )
        {
            list($user, $domain) = $this->_parseEmail( $email );
            $mxs = $this->getMXrecords( $domain );
            $fp = false;
            $timeout = ceil( $this->_maxConnectionTimeout / count( $mxs ) );
            foreach ( $mxs as $host )
            {
//                if ($fp = @fsockopen($host, $this->_port, $errno, $errstr, $timeout)) { 
                if ( $fp = @stream_socket_client( "tcp://" . $host . ":" . $this->_port, $errno, $errstr, $timeout ) )
                {
                    stream_set_timeout( $fp, $this->_maxStreamTimeout );
                    stream_set_blocking( $fp, 1 );
//                    stream_set_blocking($fp, 0); 
                    $code = $this->_fsockGetResponseCode( $fp );
                    if ( $code == '220' )
                    {
                        break;
                    }
                    else
                    {
                        fclose( $fp );
                        $fp = false;
                    }
                }
            }

            if ( $fp )
            {
                $this->_fsockquery( $fp, "HELO " . $this->_fromDomain );
                //$this->_fsockquery($fp, "VRFY " . $email); 
                $this->_fsockquery( $fp, "MAIL FROM: <" . $this->_fromName . '@' . $this->_fromDomain . ">" );
                $code = $this->_fsockquery( $fp, "RCPT TO: <" . $user . '@' . $domain . ">" );
                $this->_fsockquery( $fp, "RSET" );
                $this->_fsockquery( $fp, "QUIT" );
                fclose( $fp );
                if ( $code == '250' )
                {
                    /**
                     * http://www.ietf.org/rfc/rfc0821.txt 
                     * 250 Requested mail action okay, completed 
                     * email address was accepted 
                     */
                    $result = true;
                }
                elseif ( $code == '450' || $code == '451' || $code == '452' )
                {
                    /**
                     * http://www.ietf.org/rfc/rfc0821.txt 
                     * 450 Requested action not taken: the remote mail server 
                     *     does not want to accept mail from your server for 
                     *     some reason (IP address, blacklisting, etc..) 
                     *     Thanks Nicht Lieb. 
                     * 451 Requested action aborted: local error in processing 
                     * 452 Requested action not taken: insufficient system storage 
                     * email address was greylisted (or some temporary error occured on the MTA) 
                     * i believe that e-mail exists 
                     */
                    $result = true;
                }
            }
        }
        return $result;
    }

    /**
     * Parses input string to array(0=>user, 1=>domain) 
     * 
     * @param string $email 
     * @return array 
     * @access private 
     */
    private function _parseEmail( &$email )
    {
        return sscanf( $email, "%[^@]@%s" );
    }

    /**
     * writes the contents of string to the file stream pointed to by handle $fp
     *
     * @access private
     * @param resource $fp
     * @param $query
     * @internal param string $string The string that is to be written
     * @return string Returns a string of up to length - 1 bytes read from the file pointed to by handle.
     * If an error occurs, returns FALSE.
     */
    private function _fsockquery( &$fp, $query )
    {
        stream_socket_sendto( $fp, $query . "\r\n" );
        return $this->_fsockGetResponseCode( $fp );
    }

    /**
     * Reads all the line long the answer and analyze it. 
     * 
     * @access private 
     * @param resource $fp 
     * @return string Response code 
     * If an error occurs, returns FALSE 
     */
    private function _fsockGetResponseCode( &$fp )
    {
        $stopCount = 0;
        do
        {
            $reply = stream_get_line( $fp, 1024, "\r\n" );
            $status = stream_get_meta_data( $fp );
            $stopCount++;
        }
        while ( ($reply[ 3 ] != ' ') && ($status[ 'timed_out' ] === FALSE) );

        preg_match( '/^(?<code>[0-9]{3}) (.*)$/ims', $reply, $matches );
        $code = isset( $matches[ 'code' ] ) ? $matches[ 'code' ] : false;
        return $code;
    }

}
