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
 * @category    Widget s
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Show.php
 */
class Widget_Hetznerstatus_Show extends Widget
{

    private $refreshTimeout = 3600;

    /**
     *
     * @return string 
     */
    public function getData()
    {

        $rss = Cache::get( 'widget_data_' . $this->getID() );
        $rss_fetch_time = 0;
        if ( is_null( $rss ) || $rss[ 'expires' ] < time() )
        {
            $requestAddress = 'http://www.hetzner-status.de/de.atom';
            $xml_str = Library::getRemoteFile( $requestAddress );

            if ( $xml_str === false )
            {
                $this->setError('<p>The remote server could not be reached while trying to fetch data for the widget.</p>');
                return;
            }

            if ( strpos($xml_str, '<html') !== false )
            {
                $this->setError('<p>The remote server returns HTML code!?</p>');
                return;
            }



            $expires = time() + $this->refreshTimeout;
            $rss_fetch_time = $expires;

            #Cache::setCompress(true);
            Cache::write( 'widget_data_' . $this->getID(), array(
                'expires' => $expires,
                'str'     => $xml_str ) );
        }
        else
        {
            $xml_str = $rss[ 'str' ];
            $rss_fetch_time = $rss[ 'expires' ];
        }

        if (!$xml_str)
        {
            $this->setError('<p>'. trans('Hetzner Status konnte nicht abgefragt werden. Versuchen Sie es später erneut.') .'</p>');
            return;
        }

        $_data = array();
        $_data[ 'rss_fetched' ] = $rss_fetch_time - $this->refreshTimeout;

		if (!class_exists('SimplePie', false)) {
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
        /*
          $item = $h[$i];
          $data['items'][$i]['type'] = (string) $item['type'];
          $data['items'][$i]['rztitle'] = (string) $item['rztitle'];
          $data['items'][$i]['start'] = (string) $item['start'];
          $data['items'][$i]['ende'] = (string) $item['ende'];
          $data['items'][$i]['description'] = (string) $item['description'];
         */

        foreach ( $feed->get_items() as $item )
        {
            if ( !trim( $item->get_title() ) )
            {
                continue;
            }

            $_data[ 'items' ][] = array(
                'title'   => $item->get_title(),
                'link'    => $item->get_link(),
                'date'    => $item->get_date( 'd.m.Y, H:i' ),
                'content' => preg_replace( '/<br([^>]*)>/i', '<br/>', $item->get_content() ),
            );
        }
        $_data[ 'container_id' ] = 'wdgt-' . $this->getName() . '-' . $this->getID();

        return $this->setWidgetData( $_data );


        print_r( $_data );
        exit;




        $doc = new DOMDocument;
        $doc->strictErrorChecking = false;









        @$doc->loadHTML( $xml_str );

        $xpath = new DOMXPath( $doc );

        // find out how many messages there are
        $n = $xpath->evaluate( 'count(//entry)' );

        // get messages
        $titles = $xpath->query( '//entry/title' );

        $entry = $xpath->query( '//entry' );

        // now parse the netinfo<n> tables
        for ( $i = 0; $i < $n; $i++ )
        {
            // put messages into array
            $h[ $i ][ 'rztitle' ] = $titles->item( $i )->nodeValue;


            # $entry->item($i)->nodeValue;

            $_keys = $entry->item( $i );
            $keys = $xpath->query( '//entry//summary' );

            foreach ( $keys as $k )
            {
                //$values = $xpath->query('//table[@id="netinfo' . ($i + 1) . '"]//td[span="' . $k->nodeValue . '"]/following-sibling::td/span');
                $values = $xpath->query( '//table[@id="report_' . $i . '"]//tbody/tr/th/following-sibling::td' );
                //$values = $xpath->query('//table[@id="netinfo'.($i+1).'"]//td[span="'.$k->nodeValue.'"]/following-sibling::td/*[@class="content"]');
                $v = $values->item( 0 );
                switch ( trim( $k->nodeValue ) )
                {
                    case "Status-Typ:": // metadata
                        $h[ $i ][ 'type' ] = $v->textContent;
                        continue 2;
                    case "Beschreibung:": // the entry itself
                        $h[ $i ][ 'description' ] = $v->textContent;
                        continue 2;
                    case "Start:":
                        $d = explode( "Uhr", $v->textContent );
                        $h[ $i ][ 'start' ] = strtotime( trim( $d[ 0 ] ) );
                        continue 2;
                    case "Ende:":
                        $d = explode( "Uhr", $v->textContent );
                        $h[ $i ][ 'ende' ] = strtotime( trim( $d[ 0 ] ) );
                        if ( !is_numeric( $h[ $i ][ 'ende' ] ) )
                            $h[ $i ][ 'ende' ] = "";
                        continue 2;
                    case "Update:":
                        $d = explode( "Uhr", $v->textContent );
                        $h[ $i ][ 'updated' ] = strtotime( trim( $d[ 0 ] ) );
                        continue 2;
                    default:
                        continue 2;
                }
            }
        }


        if ( $n == 0 )
        {
            
        }
        else
        {
            for ( $i = 0; $i < $n; $i++ )
            {
                if ( isset( $h[ $i ] ) )
                {
                    $item = $h[ $i ];
                    $data[ 'items' ][ $i ][ 'type' ] = (string) $item[ 'type' ];
                    $data[ 'items' ][ $i ][ 'rztitle' ] = (string) $item[ 'rztitle' ];
                    $data[ 'items' ][ $i ][ 'start' ] = (string) $item[ 'start' ];
                    $data[ 'items' ][ $i ][ 'ende' ] = (string) $item[ 'ende' ];
                    $data[ 'items' ][ $i ][ 'description' ] = (string) $item[ 'description' ];
                }
            }
        }

        $data[ 'container_id' ] = 'wdgt-' . $this->getName() . '-' . $this->getConfig( 'id' );

        return $this->setWidgetData( $data )->renderShow();

        $content = $this->renderTemplate( 'rss.html', $data );
        return $content;
    }

}

?>