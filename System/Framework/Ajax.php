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
 * @copyright    2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Ajax.php
 *
 */

/** @noinspection PhpUndefinedClassInspection */
class Ajax extends Loader
{

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Send automaticly the Json string
     * you can optional add attributes as an Array
     *
     * @param bool $success
     * @param array $options
     * @throws BaseException
     */
    public static function Send($success = true, $options = array())
    {

        $isAdmin = defined( 'ADM_SCRIPT' ) && ADM_SCRIPT !== false;

        if ( !HTTP::input( 'getGriddata' ) && $isAdmin )
        {
            if ( empty( $options[ 'debugoutput' ] ) )
            {
                if ( $isAdmin )
                {
                    $options[ 'debugoutput' ] = Debug::write( true );
                }
                else
                {
                    if ( ( !defined( 'SKIP_DEBUG' ) || SKIP_DEBUG != true ) )
                    {
                        $options[ 'debugoutput' ] = Debug::write( true );
                    }
                }
            }
        }
        else
        {
            unset( $options[ 'debugoutput' ] );
        }


        $disableAjaxDebug = Registry::get( 'disableAjaxDebug' );

        if ( is_array( $disableAjaxDebug ) )
        {

            $cl = strtolower( CONTROLLER );
            $ac = strtolower( ACTION );


            foreach ( $disableAjaxDebug as $r )
            {
                if ( $cl === $r[ 'controller' ] && $ac === $r[ 'action' ] )
                {
                    unset( $options[ 'debugoutput' ] );
                    break;
                }
            }
        }


        if ( defined( 'SEND_UNLOCK' ) )
        {
            unset( $options[ 'lock_content' ] );
            $options[ 'unlock_content' ] = true;
        }

        if ( $isAdmin && isset( $GLOBALS[ 'content_lockerror' ] ) )
        {
            $options[ 'lockerror' ] = $GLOBALS[ 'content_lockerror' ];
        }

        if ( $isAdmin )
        {
            $options[ 'mem_limit' ] = (int)ini_get( 'memory_limit' );
            $options[ 'mem_usage' ] = function_exists( 'memory_get_usage' ) ? round( memory_get_usage() / 1024 / 1024, 2 ) : 0;

            if ( !empty( $options[ 'mem_usage' ] ) && !empty( $options[ 'mem_limit' ] ) )
            {
                $options[ 'mem_percent' ] = round( $options[ 'mem_usage' ] / $options[ 'mem_limit' ] * 100, 0 );
            }
        }


        $len1 = ob_get_length();
        if ( $len1 )
        {
            $buffer = ob_get_clean();
        }

        if ( !isset( $options[ 'csrfToken' ] ) )
        {
            $options[ 'csrfToken' ] = Csrf::generateCSRF( 'token' );
        }

        session_write_close();

        $_optionsEncoded = null;

        if ( is_array( $options ) )
        {
            $options[ 'success' ] = $success;
            $_optionsEncoded      = Json::encode( $options );
        }
        else
        {
            $options[ 'success' ] = $success;
            $_optionsEncoded      = Json::encode( $options );
        }

        if ( isset( $GLOBALS[ 'contentlock' ] ) )
        {
            $options[ 'lock_content' ] = true;
        }

        if ( empty( $_optionsEncoded ) || !is_string( $_optionsEncoded ) )
        {
            throw new BaseException( 'Invalid Json String or empty result!' );
        }

        Session::write();

        self::_Send( $_optionsEncoded );
    }

    /**
     *
     * @param string $_json
     */
    protected static function _Send($_json)
    {


        $output = new Output();
        $output->setMode( Output::AJAX );

        $output->addHeader( 'Content-Type', 'application/json' );
        $output->addHeader( 'Last-Modified', gmdate( "D, d M Y H:i:s" ) . " GMT" );
        $output->addHeader( 'Cache-Control', 'no-store, no-cache, must-revalidate' );
        $output->addHeader( 'Pragma', 'no-cache' );
        $output->addHeader( 'Expires', 'Mon, 20 Jul 1995 05:00:00 GMT' );

        // Add json body
        $output->appendOutput( $_json );

        // Send
        $output->sendOutput();

        exit;
    }

}

?>