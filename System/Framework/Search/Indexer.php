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
 * @file        Indexer.php
 *
 */
class Search_Indexer extends Loader
{

    /**
     *
     * @var Search_Indexer
     */
    protected static $objInstance;

    /**
     *
     * @var integer
     */
    private $min_wordlen = 4; // Minimale wortlänge in Zeichen (Standart)

    /**
     *
     * @var integer
     */
    private $max_wordlen = 20;

    /**
     *
     * @var array
     */
    private $wordcache = array();

    /**
     * @var array
     */
    private $searchWordArray = array();

    /**
     * @var array
     */
    private $stopwords = array();

    /**
     * @var array
     */
    private $all_words = array();

    /**
     * Prevent cloning of the object (Singleton)
     */
    private function __clone()
    {
        
    }

    /**
     * Return the current object instance (Singleton)
     *
     * @return Search_Indexer
     */
    public static function getInstance()
    {
        if ( !is_object( self::$objInstance ) )
        {
            self::$objInstance = new Search_Indexer();

            /**
             * setup defaults
             */
            self::$objInstance->min_wordlen = (Settings::get( 'minwordlength', 3 ) > 0 ? Settings::get( 'minwordlength', 3 ) : self::$objInstance->min_wordlen);
            self::$objInstance->max_wordlen = (Settings::get( 'maxwordlength', 10 ) > 0 ? Settings::get( 'maxwordlength', 10 ) : self::$objInstance->max_wordlen);


            self::$objInstance->setBadwords();
        }

        return self::$objInstance;
    }

    /**
     *
     * @return Search
     */
    public function optimizeIndexes()
    {
        $this->db->query( "OPTIMIZE TABLE %tp%search_fulltext" );
        $this->db->query( "OPTIMIZE TABLE %tp%search_index" );
        $this->db->query( "OPTIMIZE TABLE %tp%search_wordindex" );

        return $this;
    }

    /**
     * @param $content
     * @return int
     */
    private function getContentSize( $content )
    {
        // $content = strip_tags($content);
        return strlen( $content );
    }

    /**
     * Prepare Content for Indexing
     * @param string $text
     * @return string
     */
    public function prepareContent( $text )
    {
        // Alle HTML Tags löschen
        $text = strip_tags( Strings::unhtmlspecialchars( $text, true ) );

        $text = str_replace( array(
            "\n",
            "\r",
            "\t",
            '&#160;',
            '&nbsp;' ), ' ', $text );
        $text = str_replace( array(
            '´',
            '`' ), "'", $text );


        // Remove special characters
        $text = preg_replace( array(
            '/-\s*/',
            '/\s*-/',
            "/'{1,}\s*/",
            "/\s*'/",
            '/\.{1,}\s*/',
            '/\.{1,}$/',
            '/:{1,}\s*/',
            '/:{1,}\s*$/',
            '/,{1,}\s*/',
            '/,{1,}\s*$/',
            '/[^\pN\pL\'\.:,_-]/ux' ), ' ', $text );

        // Urls im Inhalt löschen (keine Tags)
        // $text = preg_replace("!(http://www\.|http://www\.|http://|http://)!is", " ", $text);
        // Strip external URLs
        $uc = "A-Za-z0-9_\\/:.,~%\\-+&;#?!=()@\\xA0-\\xFF";
        $protos = "https|http|ftp|mailto|news|gopher";
        $pat = "/(^|[^\\[])({$protos}):[{$uc}]+([^{$uc}]|$)/ix";

        // BBCode Remove
        // $text = preg_replace('!\[([^\]\[]*)\]!is', '', $text);
        $text = preg_replace( '!\[(/)([^\]\[]*)\]!is', '', $text );

        $text = preg_replace( $pat, "$1 $2", $text );

        $p1 = "/([^\\[])\\[({$protos}):[{$uc}]+]/";
        $p2 = "/([^\\[])\\[({$protos}):[{$uc}]+\\s+([^\\]]+)]/";

        $text = preg_replace( $p1, "\\1 ", $text );
        $text = preg_replace( $p2, "\\1 \\3 ", $text );


        $text = preg_replace( '/ +/', ' ', $text );
        $text = preg_replace( '!([a-z]+)?\-([a-z]+)?!isx', '$1 $2', $text );

        #$chrs = implode('', array_merge($this->cyrylicChars, $this->otherChars) );
        #$text = preg_replace('#([^a-z0-9_\-\sàáâãäåàáâãäåèéêëìíîïòóôõöùúûüñçÿ'.$chrs.']+)#is', ' ', $text);
        $text = preg_replace( '#[_\- ]+$#isx', '', $text );

        // replace multible spaces
        $text = preg_replace( '#\s{2,}#', ' ', $text );
        $text = preg_replace( '#^\s{1,}#', '', $text );


        return trim( $text );
    }

    /**
     *
     * @staticvar integer $section_id
     * @param string $section_key (controller)
     * @param string $lang_key
     * @param string $location
     * @return integer
     */
    private function getIndexSection( $section_key, $lang_key = 'de', $location = '' )
    {
        static $section_id;

        if ( !(int)$section_id  )
        {
            $sql = "SELECT id FROM %tp%search_sections WHERE `section_key` = ? AND `lang` = ? AND `location` = ?";
            $r = $this->db->query( $sql, $section_key, $lang_key, $location )->fetch();

            if ( $r[ 'id' ] )
            {
                $section_id = $r[ 'id' ];
            }
            else
            {

                $this->db->query( 'INSERT INTO %tp%search_sections (`section_key`, `lang`, `location`) VALUES(?, ?, ?)', $section_key, $lang_key, $location );
                $section_id = $this->db->insert_id();
            }
        }

        return $section_id;
    }

    /**
     *
     * @param array $index_data
     * @return void
     */
    function createIndex( $index_data = array() )
    {

        @set_time_limit( 120 );
        $db = Database::getInstance();


        $index_time = time();
        $location_url = $index_data[ 'location' ];
        $title = $this->prepareContent( $index_data[ 'title' ] );
        $text = $this->prepareContent( $index_data[ 'content' ] );
        $content_size = $this->getContentSize( $index_data[ 'content' ] );
        $content_time = $index_data[ 'time' ];


        $section_id = $index_data[ 'section_id' ] ? $index_data[ 'section_id' ] : 0;
        $title = utf8_substr( $title, 0, 200 );

        // Prüfen ob im Index Vorhanden
        $indexs = $db->query( "SELECT id FROM %tp%search_fulltext 
            WHERE contentid = ? 
            AND controller = ? 
            AND action = ? 
            AND section_id = ?", $index_data[ 'contentid' ], $index_data[ 'controller' ], $index_data[ 'action' ], $section_id )->fetch();


        // Wenn index Existiert aktualisiren
        if ( $indexs[ 'id' ] > 0 )
        {
            $sql = "UPDATE %tp%search_fulltext SET
                    title = " . $this->db->quote( $title ) . ",
					content = " . $this->db->quote( $text ) . ",
                    alias = " . $this->db->quote( $index_data[ 'alias' ] ) . ",
                    suffix = " . $this->db->quote( $index_data[ 'suffix' ] ) . ",
					index_time = " . $index_time . ",
                    content_time = " . $content_time . ",
                    content_bytes = " . $content_size . ",
                    controller = " . $this->db->quote( $index_data[ 'controller' ] ) . ",
                    action = " . $this->db->quote( $index_data[ 'action' ] ) . ",
                    groups = " . $this->db->quote( $index_data[ 'groups' ] ) . ",
                    contentid = " . $index_data[ 'contentid' ] . ",
                    section_id = " . $section_id . ",
                    apptype = " . $this->db->quote( (isset( $index_data[ 'apptype' ] ) ? $index_data[ 'apptype' ] : '' ) ) . "
                    WHERE id = " . $indexs[ 'id' ];

            $this->db->query( $sql );
        }
        else
        {
            $sql = "INSERT INTO %tp%search_fulltext (alias, suffix, title, content, index_time, content_time, content_bytes, section_id, controller, action, groups, contentid, appid, apptype)
                    VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $this->db->query( $sql, array(
                $index_data[ 'alias' ],
                $index_data[ 'suffix' ],
                $title,
                $text,
                $index_time,
                $content_time,
                $content_size,
                $section_id,
                $index_data[ 'controller' ],
                $index_data[ 'action' ],
                $index_data[ 'groups' ],
                $index_data[ 'contentid' ],
                (isset( $index_data[ 'appid' ] ) ? (int)$index_data[ 'appid' ]  : 0),
                (isset( $index_data[ 'apptype' ] ) ? $index_data[ 'apptype' ] : '')
            ) );

            $indexs[ 'id' ] = $db->insert_id();
        }

        // ===========================================================
        $scores = array();
        $enginefiller = array();
        $wordcacheFull = array();


        // Übergebene Wörter zerlegen
        $words = trim( (string) $title );
        $allwords = ' ' . $words;

        $wordarray = preg_split( '/ +/', utf8_strtolower( $title ) );


        $this->min_wordlen = 3;
        $this->max_wordlen = 10;


        foreach ( $wordarray as $word )
        {
            $word = trim( $word );
            $len = strlen( $word );

            if ( !$len || preg_match( '/^[\.:,\'_-]+$/', $word ) )
            {
                continue;
            }

            if ( preg_match( '/^[\':,]/', $word ) )
            {
                $word = substr( $word, 1 );
            }

            if ( preg_match( '/[\':,\.]$/', $word ) )
            {
                $word = substr( $word, 0, -1 );
            }

            // Leerzeichen und zu kurze wörter überspringen
            $len = utf8_strlen( $word );
            if ( ord( $word ) == 0 || $len < $this->min_wordlen || $len > $this->max_wordlen || isset( $this->stopwords[ $word ] ) )
            {
                unset( $scores[ $word ] );
                continue;
            }

            if ( !isset( $this->wordcache[ $word ] ) )
            {
                $wordcacheFull[] = $word;
            }


            if ( isset( $scores[ $word ] ) )
            {
                $scores[ $word ] += 1;
            }
            else
            {
                $scores[ $word ] = 1;
            }
        }


        // Text
        $allwords = trim( (string) $text );
        $wordlist = preg_split( '/ +/', utf8_strtolower( $text ) );

        foreach ( $wordlist as $idx => $word )
        {
            $word = trim( $word );
            $len = strlen( $word );

            if ( !$len || preg_match( '/^[\.:,\'_-]+$/', $word ) )
            {
                unset( $wordlist[ $idx ] );
                continue;
            }

            if ( preg_match( '/^[\':,]/', $word ) )
            {
                $word = substr( $word, 1 );
            }

            if ( preg_match( '/[\':,\.]$/', $word ) )
            {
                $word = substr( $word, 0, -1 );
            }

            // Leerzeichen und zu kurze wörter überspringen
            $len = utf8_strlen( $word );
            if ( ord( $word ) == 0 || $len < $this->min_wordlen || $len > $this->max_wordlen || isset( $this->stopwords[ $word ] ) )
            {
                unset( $scores[ $word ] );
                unset( $wordlist[ $idx ] );
                continue;
            }

            if ( !isset( $this->wordcache[ $word ] ) )
            {
                $wordcacheFull[] = $word;
            }


            if ( isset( $scores[ $word ] ) )
                $scores[ $word ] += 1;
            else
                $scores[ $word ] = 1;
        }

        if ( count( $wordcacheFull ) )
        {
            $getwordidsql = "word IN(" . $this->db->quote( implode( ',', $wordcacheFull ) ) . ")";

            $sql = "SELECT id, word FROM %tp%search_index WHERE $getwordidsql";
            $res = $db->query( $sql, $section_id )->fetchAll();
            foreach ( $res as $r )
            {
                $this->wordcache[ $r[ 'word' ] ] = $r[ 'id' ];
            }
        }


        $insertsql = '';
        $newwords = array();
        $newtitlewords = '';

        foreach ( $scores as $word => $score )
        {
            $len = strlen( $word );
            if ( ord( $word ) == 0 || $len < $this->min_wordlen || $len > $this->max_wordlen || isset( $this->stopwords[ $word ] ) )
            {
                continue;
            }

            if ( $word != '' )
            {
                if ( isset( $this->wordcache[ $word ] ) )
                {
                    // Does this word already exist in the word table?
                    $insertsql .= ",(" . $this->db->quote( $this->wordcache[ $word ] ) . "," . $indexs[ 'id' ] . "," . $score . "," . $section_id . ")";
                }
                else
                {
                    $newwords[] = $this->db->quote( $word ); // No so add it to the word table
                }
            }
        }

        if ( !empty( $insertsql ) )
        {
            $insertsql = substr( $insertsql, 1 );
            $sql = "REPLACE INTO %tp%search_wordindex (indexid,fulltextid,score,section_id) VALUES " . $insertsql;
            $this->db->query( $sql );
        }

        #echo $sql;
        //$newwords = trim((string)$newwords);

        if ( count( $newwords ) )
        {
            $insertwords = '';

            $sum = count( $newwords );
            if ( $sum > 1 )
            {
                $insertwords = '(' . implode( '),(', $newwords ) . ')';
            }
            elseif ( $sum == 1 )
            {
                $insertwords = '(' . implode( '', $newwords ) . ')';
            }

            $sql = "REPLACE INTO %tp%search_index (`word`) VALUES " . $insertwords;
            $this->db->query( $sql );

            $selectwords = "word IN (" . implode( ",", $newwords ) . ")";
            $scoressql = 'CASE word';

            foreach ( $scores AS $word => $score )
            {
                $scoressql .= "\n WHEN " . $this->db->quote( $word ) . " THEN " . $score;
            }
            $scoressql .= ' ELSE 1 END';


            $sql = "REPLACE INTO %tp%search_wordindex (indexid, fulltextid, section_id, score)
			        SELECT DISTINCT id, " . $indexs[ 'id' ] . ", " . $section_id . ", " . $scoressql . " FROM %tp%search_index WHERE " . $selectwords;
            $this->db->query( $sql );
        }

        $newwords = $scores = $insertsql = null;


        return;
    }

}
