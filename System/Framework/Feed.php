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
 * @file        Feed.php
 *
 */
class Feed
{

    /**
     * Namespace constants
     */
    const NAMESPACE_ATOM_03 = 'http://purl.org/atom/ns#';

    /**
     *
     */
    const NAMESPACE_ATOM_10 = 'http://www.w3.org/2005/Atom';

    /**
     *
     */
    const NAMESPACE_RDF = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';

    /**
     *
     */
    const NAMESPACE_RSS_090 = 'http://my.netscape.com/rdf/simple/0.9/';

    /**
     *
     */
    const NAMESPACE_RSS_10 = 'http://purl.org/rss/1.0/';

    /**
     * Feed type constants
     */
    const TYPE_ANY = 'any';

    /**
     *
     */
    const TYPE_ATOM_03 = 'atom-03';

    /**
     *
     */
    const TYPE_ATOM_10 = 'atom-10';

    /**
     *
     */
    const TYPE_ATOM_10_ENTRY = 'atom-10-entry';

    /**
     *
     */
    const TYPE_ATOM_ANY = 'atom';

    /**
     *
     */
    const TYPE_RSS_090 = 'rss-090';

    /**
     *
     */
    const TYPE_RSS_091 = 'rss-091';

    /**
     *
     */
    const TYPE_RSS_091_NETSCAPE = 'rss-091n';

    /**
     *
     */
    const TYPE_RSS_091_USERLAND = 'rss-091u';

    /**
     *
     */
    const TYPE_RSS_092 = 'rss-092';

    /**
     *
     */
    const TYPE_RSS_093 = 'rss-093';

    /**
     *
     */
    const TYPE_RSS_094 = 'rss-094';

    /**
     *
     */
    const TYPE_RSS_10 = 'rss-10';

    /**
     *
     */
    const TYPE_RSS_20 = 'rss-20';

    /**
     *
     */
    const TYPE_RSS_ANY = 'rss';

    /**
     * @var string
     */
    protected $characterSet = 'UTF-8';

    /**
     * @var string
     */
    protected $language = '';

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var string
     */
    protected $link = '';

    /**
     * @var int
     */
    protected $published = 0;

    /**
     * @var array
     */
    protected static $_extensions = array(
        'feed'  => array(
            'DublinCore_Feed',
            'Atom_Feed'
        ),
        'entry' => array(
            'Content_Entry',
            'DublinCore_Entry',
            'Atom_Entry'
        ),
        'core'  => array(
            'DublinCore_Feed',
            'Atom_Feed',
            'Content_Entry',
            'DublinCore_Entry',
            'Atom_Entry'
        )
    );

    /**
     * Feed name
     * @var string
     */
    protected $strName;

    /**
     * Data array
     * @var array
     */
    protected $arrData = array();

    /**
     * Items
     * @var array
     */
    protected $arrItems = array();

    /**
     * Take an array of arguments and initialize the object
     * @param array $initdata
     */
    public function __construct( $initdata = array() )
    {
        // speziell für Atom
        if ( !empty( $initdata[ 'atomName' ] ) )
        {
            $this->strName = $initdata[ 'atomName' ];
        }

        if ( !empty( $initdata[ 'characterSet' ] ) )
        {
            $this->characterSet = strtoupper( $initdata[ 'characterSet' ] );
        }

        if ( !empty( $initdata[ 'language' ] ) )
        {
            $this->language = $initdata[ 'language' ];
        }
        else
        {
            $this->language = Locales::getLocale();
        }


        if ( !empty( $initdata[ 'title' ] ) )
        {
            $this->title = $initdata[ 'title' ];
        }

        if ( !empty( $initdata[ 'description' ] ) )
        {
            $this->description = $initdata[ 'description' ];
        }

        if ( !empty( $initdata[ 'link' ] ) )
        {
            $this->link = $initdata[ 'link' ];
        }

        if ( !empty( $initdata[ 'published' ] ) )
        {
            $this->published = $initdata[ 'published' ];
        }
        else
        {
            $this->published = time();
        }
    }

    /**
     * Set an object property
     * @param string
     * @param mixed
     */
    public function __set( $strKey, $varValue )
    {
        $this->arrData[ $strKey ] = $varValue;
    }

    /**
     * Return an object property
     *
     * @param $strKey
     * @return mixed
     */
    public function __get( $strKey )
    {
        return $this->arrData[ $strKey ];
    }

    /**
     * Add an item
     * @param array
     */
    public function addItem( $objItem )
    {
        $this->arrItems[] = $objItem;
    }

    /**
     * Generate an RSS 2.0 feed and return it as XML string
     * @return string
     */
    public function generateRss()
    {
        $xml = '<?xml version="1.0" encoding="' . $this->characterSet . '"?>' . "\n";
        $xml .= '<rss version="2.0">' . "\n";
        $xml .= '  <channel>' . "\n";
        $xml .= '    <title>' . htmlspecialchars( Settings::get( 'pagename' ) . ' - ' . $this->title ) . '</title>' . "\n";
        $xml .= '    <description>' . htmlspecialchars( $this->description ) . '</description>' . "\n";
        $xml .= '    <link>' . htmlspecialchars( $this->link ) . '</link>' . "\n";
        $xml .= '    <language>' . $this->language . '</language>' . "\n";
        $xml .= '    <pubDate>' . date( 'r', $this->published ) . '</pubDate>' . "\n";
        $xml .= '    <generator>DreamCMS ' . VERSION . '</generator>' . "\n";

        foreach ( $this->arrItems as $objItem )
        {

            $objItem[ 'description' ] = preg_replace( '#(href|src)\s*=\s*"(pages/)?#', '$1="' . Settings::get( 'portalurl' ) . '/$2', $objItem[ 'description' ] );


            $xml .= '    <item>' . "\n";
            $xml .= '      <title>' . htmlspecialchars( $objItem[ 'title' ] ) . '</title>' . "\n";
            $xml .= '      <description><![CDATA[' . preg_replace( '/[\n\r]+/', ' ', $objItem[ 'description' ] ) . ']]></description>' . "\n";
            $xml .= '      <link>' . htmlspecialchars( $objItem[ 'link' ] ) . '</link>' . "\n";
            $xml .= '      <pubDate>' . date( 'r', $objItem[ 'published' ] ) . '</pubDate>' . "\n";
            $xml .= '      <guid>' . (isset($objItem[ 'guid' ]) && $objItem[ 'guid' ] ? $objItem[ 'guid' ] : htmlspecialchars( $objItem[ 'link' ] )) . '</guid>' . "\n";

            // Enclosures
            if ( isset($objItem[ 'enclosure' ]) && is_array( $objItem[ 'enclosure' ] ) )
                $xml .= '      <enclosure url="' . $objItem[ 'enclosure' ][ 'url' ] . '" length="' . $objItem[ 'enclosure' ][ 'length' ] . '" type="' . $objItem[ 'enclosure' ][ 'type' ] . '" />' . "\n";

            $xml .= '    </item>' . "\n";
        }

        $xml .= '  </channel>' . "\n";
        $xml .= '</rss>';

        return $xml;
    }

    /**
     * Generate an Atom feed and return it as XML string
     * @return string
     */
    public function generateAtom()
    {
        $xml = '<?xml version="1.0" encoding="' . $this->characterSet . '"?>' . "\n";
        $xml .= '<feed xmlns="http://www.w3.org/2005/Atom" xml:lang="' . $this->language . '">' . "\n";
        $xml .= '  <title>' . htmlspecialchars( Settings::get( 'pagename' ) . ' - ' . $this->title ) . '</title>' . "\n";
        $xml .= '  <subtitle>' . htmlspecialchars( $this->description ) . '</subtitle>' . "\n";
        $xml .= '  <link rel="alternate" href="' . htmlspecialchars( $this->link ) . '" />' . "\n";
        $xml .= '  <id>' . htmlspecialchars( $this->link ) . '</id>' . "\n";
        $xml .= '  <updated>' . preg_replace( '/00$/', ':00', date( 'Y-m-d\TH:i:sO', $this->published ) ) . '</updated>' . "\n";
        $xml .= '  <generator>DreamCMS ' . VERSION . '</generator>' . "\n";
        $xml .= '  <link rel="self" href="' . htmlspecialchars( Settings::get( 'portalurl' ) . '/' . $this->strName ) . '.atom" />' . "\n";

        foreach ( $this->arrItems as $objItem )
        {


            $objItem[ 'content' ] = preg_replace( '#(href|src)\s*=\s*"(pages/)?#', '$1="' . Settings::get( 'portalurl' ) . '/$2', $objItem[ 'content' ] );


            $xml .= '  <entry>' . "\n";
            $xml .= '    <title>' . htmlspecialchars( $objItem[ 'title' ] ) . '</title>' . "\n";
            $xml .= '    <summary type="xhtml"><div xmlns="http://www.w3.org/1999/xhtml">' . preg_replace( '/[\n\r]+/', ' ', $objItem[ 'description' ] ) . '</div></summary>' . "\n";
            $xml .= '    <content type="xhtml"><div xmlns="http://www.w3.org/1999/xhtml">' . preg_replace( '/[\n\r]+/', ' ', $objItem[ 'content' ] ) . '</div></content>' . "\n";
            $xml .= '    <link rel="alternate" href="' . htmlspecialchars( $objItem[ 'link' ] ) . '" />' . "\n";
            $xml .= '    <updated>' . preg_replace( '/00$/', ':00', date( 'Y-m-d\TH:i:sO', $objItem[ 'published' ] ) ) . '</updated>' . "\n";
            $xml .= '    <id>' . (isset($objItem[ 'guid' ]) && $objItem[ 'guid' ] ? $objItem[ 'guid' ] : htmlspecialchars( $objItem[ 'link' ] )) . '</id>' . "\n";

            // Enclosures
            if ( isset($objItem[ 'enclosure' ]) && is_array( $objItem[ 'enclosure' ] ) )
            {
                $xml .= '    <link rel="enclosure" type="' . $objItem[ 'enclosure' ][ 'type' ] . '" href="' . $objItem[ 'enclosure' ][ 'url' ] . '" length="' . $objItem[ 'enclosure' ][ 'length' ] . '" />' . "\n";
            }

            $xml .= '  </entry>' . "\n";
        }



        return $xml . '</feed>';
    }

}

?>