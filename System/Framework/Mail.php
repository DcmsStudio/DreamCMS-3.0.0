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
 * @file        Mail.php
 *
 */
class Mail
{

    /**
     * @var mixed
     */
    private $type; // 1 = smtp, 0 = mail
    /**
     * @var bool
     */

    private $message = false;

    /**
     * @var bool
     */
    private $mailer = false;

    /**
     * @var bool
     */
    private $to = false;

    /**
     * @var bool
     */
    private $subject = false;

    /**
     * @var bool
     */
    private $body = false;

    /**
     * @var bool
     */
    private $from = false;

    /**
     * @var int
     */
    private $intPriority = 3;

    /**
     * @var bool
     */
    public $debug = false;

    /**
     *
     */
    public function __construct()
    {
        require_once VENDOR_PATH . 'swift/lib/swift_required.php';


        $this->type = Settings::get( 'mailtype', 0 );

        if ( $this->type === 1 )
        {
            $encryption = Settings::get( 'smtp_encryption', 'none' );
            if ( $encryption != 'none' )
            {
                if ( !in_array( $encryption, stream_get_transports() ) )
                {
                    Error::raise( 'Transport encryption type stream not registered.' );
                }
            }

            $host = Settings::get( 'smtp_server' );
            $port = Settings::get( 'smtp_port' );

            if ( empty( $host ) || empty( $port ) )
            {
                Error::raise( trans( 'SMTP Mail configuration is incomplete - please visit the general settings page to complete the configuration.' ) );
            }

            if ( $encryption != 'none' )
            {
                $transport = Swift_SmtpTransport::newInstance( $host, $port, $encryption );
            }
            else
            {
                $transport = Swift_SmtpTransport::newInstance( $host, $port );
            }

            $user = Settings::get( 'smtp_user' );
            $password = Settings::get( 'smtp_password' );

            if ( $user )
            {
                $transport->setUsername( $user );
            }

            if ( $password )
            {
                $transport->setPassword( $password );
            }

            $transport->setTimeout( 10 );
        }
        elseif ( $this->type === 0 )
        {
            $transport = Swift_MailTransport::newInstance();
        }
        else
        {
            Error::raise( trans( 'Mail configuration is incomplete - please visit the general settings page to complete the configuration.' ) );
        }

        $this->mailer = Swift_Mailer::newInstance( $transport );
        $this->message = Swift_Message::newInstance();
    }

    /**
     *
     * @exception Error::raise
     * @return bool
     */
    public function send()
    {
        $this->message->setPriority( $this->intPriority );

        $from = $this->message->getFrom();
        if ( empty( $from ) )
        {
            $address = Settings::get( 'frommail' );
            $name = Settings::get( 'pagename', 'DreamCMS' );
            $this->mail_from( array(
                $address,
                $name ) );
        }

        try
        {
            return $this->mailer->send( $this->message );
        }
        catch ( Exception $e )
        {
            Error::raise( $e->getMessage() );
        }
    }

    /**
     *
     * @param integer/string $priority 1-5 or '1 (highest)', '2 (high)', '3 (normal)', '4 (low)', '5 (lowest)'
     */
    public function mail_priority( $priority = 3 )
    {
        switch ( $priority )
        {
            case 1:
            case 'highest':
                $this->intPriority = 1;
                break;
            case 2:
            case 'high':
                $this->intPriority = 2;
                break;
            case 3:
            case 'normal':
                $this->intPriority = 3;
                break;
            case 4:
            case 'low':
                $this->intPriority = 4;
                break;
            case 5:
            case 'lowest':
                $this->intPriority = 5;
                break;
        }
    }

    /**
     *
     * @param mixed $data array(adress, name) or adress
     */
    public function mail_to( $data )
    {
        if ( is_array( $data ) )
        {
            if ( !empty( $data[ 0 ] ) && !empty( $data[ 1 ] ) )
            {
                $this->message->addTo( $data[ 0 ], $data[ 1 ] );
            }
            else if ( !empty( $data[ 0 ] ) )
            {
                $this->message->addTo( $data[ 0 ] );
            }
        }
        else
        {
            $this->message->addTo( $data );
        }
    }

    /**
     *
     * @param mixed $data array(adress, name) or adress
     */
    public function mail_cc( $data )
    {
        if ( is_array( $data ) )
        {
            if ( !empty( $data[ 0 ] ) && !empty( $data[ 1 ] ) )
            {
                $this->message->addCc( $data[ 0 ], $data[ 1 ] );
            }
            else if ( !empty( $data[ 0 ] ) )
            {
                $this->message->addCc( $data[ 0 ] );
            }
        }
        else
        {
            $this->message->addCc( $data );
        }
    }

    /**
     *
     * @param mixed $data array(adress, name) or adress
     */
    public function mail_bcc( $data )
    {
        if ( is_array( $data ) )
        {
            if ( !empty( $data[ 0 ] ) && !empty( $data[ 1 ] ) )
            {
                $this->message->addBcc( $data[ 0 ], $data[ 1 ] );
            }
            else if ( !empty( $data[ 0 ] ) )
            {
                $this->message->addBcc( $data[ 0 ] );
            }
        }
        else
        {
            $this->message->addBcc( $data );
        }
    }

    /**
     *
     * @param mixed $data array(adress, name) or adress
     */
    public function mail_from( $data )
    {


        if ( is_array( $data ) )
        {
            if ( !empty( $data[ 0 ] ) && !empty( $data[ 1 ] ) )
            {
                $this->message->setFrom( $data[ 0 ], $data[ 1 ] );
            }
            else if ( !empty( $data[ 0 ] ) )
            {
                $this->message->setFrom( $data[ 0 ] );
            }
        }
        else
        {
            $this->message->setFrom( $data );
        }
    }

    /**
     *
     * @param string $subject
     */
    public function mail_subject( $subject )
    {
        $this->message->setSubject( $subject );
    }

    /**
     *
     * @param string $body
     * @param string $format default is text/html
     */
    public function mail_body( $body, $format = 'text/html' )
    {
        $this->message->setBody( $body, $format );
    }

    /**
     *
     * @param string $part
     * @param string $format default is text/html
     */
    public function mail_part( $part, $format = 'text/html' )
    {
        $this->message->addPart( $part, $format );
    }

}
