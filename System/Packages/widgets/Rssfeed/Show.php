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
 * @package
 * @version      3.0.0 Beta
 * @category
 * @copyright    2008-2014 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Show.php
 */

class Widget_Rssfeed_Show extends Widget
{
    private $refreshTimeout = 900;

    public function getData()
    {
        if ( !$this->getConfig( 'url' ) )
        {
            $this->setError( '<p>' . trans( 'Bitte überprüfen Sie die Konfiguration des Widgets' ) . '</p>' );

            return;
        }

        $rss            = Cache::get( 'widget_data_' . $this->getID() );
        $rss_fetch_time = 0;
        if ( is_null( $rss ) || $rss[ 'expires' ] < time() )
        {
            $xml_str = Library::getRemoteFile( $this->getConfig( 'url' ) );

            if ( $xml_str === false )
            {
                $this->setError( '<p>The remote server could not be reached while trying to fetch data for the widget.</p>' );

                return;
            }

            if ( strpos( $xml_str, '<html' ) !== false )
            {
                $this->setError( '<p>The remote server returns HTML code!?</p>' );

                return;
            }


            $expires        = time() + $this->refreshTimeout;
            $rss_fetch_time = $expires;

            #Cache::setCompress(true);
            Cache::write( 'widget_data_' . $this->getID(), array(
                    'expires' => $expires,
                    'str'     => $xml_str) );
        }
        else
        {
            $xml_str        = $rss[ 'str' ];
            $rss_fetch_time = $rss[ 'expires' ];
        }

        if ( !$xml_str )
        {
            $this->setError( '<p>' . trans( 'Bitte überprüfen Sie die Konfiguration des Widgets' ) . '</p>' );

            return;
        }


        if ( !class_exists( 'SimplePie', false ) )
        {
            include_once VENDOR_PATH . 'simplepie/SimplePieAutoloader.php';
            include_once VENDOR_PATH . 'simplepie/idn/idna_convert.class.php';
        }


        // Create a new instance of the SimplePie object
        $feed = new SimplePie();

        $feed->set_raw_data( $xml_str );
        $success = $feed->init();
        $feed->handle_content_type();

        if ( $feed->error() )
        {
            throw new BaseException( $feed->error() );
        }


        $_data[ 'rss_fetched' ] = $rss_fetch_time - $this->refreshTimeout;


        foreach ( $feed->get_items() as $item )
        {
            if ( !trim( $item->get_title() ) )
            {
                continue;
            }

            $_data[ 'items' ][ ] = array(
                'title'   => $item->get_title(),
                'link'    => $item->get_link(),
                'date'    => $item->get_date( 'd.m.Y, H:i' ),
                'content' => preg_replace( '/<br([^>]*)>/i', '<br/>', $item->get_content() ),
            );
        }
        $_data[ 'container_id' ] = 'wdgt-' . $this->getName() . '-' . $this->getID();

        return $this->setWidgetData( $_data );
    }

}