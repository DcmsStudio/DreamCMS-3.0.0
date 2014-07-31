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
 * @file        Search.php
 *
 */
class Search extends Loader
{

    /**
     *
     * @var Search
     */
    protected static $objInstance;

    /**
     *
     * @var integer
     */
    private $min_wordlen = 3; // Minimale wortlänge in Zeichen (Standart)

    /**
     *
     * @var integer
     */
    private $max_wordlen = 20;

    /**
     *
     * @var type
     */
    private $cyrylicChars;

    /**
     * @var
     */
    private $otherChars;

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
     * @var array
     */
    private $index_ids = array();

    /**
     *
     * @var string
     */
    protected $search_hash = '';

    /**
     *
     * @var type
     */
    public $searched_sections;

    /**
     * @var
     */
    public $searched_categories;

    /**
     * @var
     */
    private $search_in;

    /**
     * @var
     */
    private $search_incats;

    /**
     * Suchmaschinen Resultate automatisch nach einer Stunde
     * aus ( Temp "cp_search_spider" ) Tabelle löschen
     *
     * @var integer
     */
    private $cleanup_time = 43200; // 12 Stunden
    /**
     * @var int
     */

    private $max_searchlimit = 1000; // Maximal 2000 ergebnisse in Ergebnisstabelle schreiben siehe Google

    /**
     * @var array
     */
    private static $_indexModulOptions = array();

    /**
     * Prevent cloning of the object (Singleton)
     */
    private function __clone()
    {
        
    }

    /**
     * Return the current object instance (Singleton)
     *
     * @return Search
     */
    public static function getInstance()
    {
        if ( !is_object( self::$objInstance ) )
        {
            self::$objInstance = new Search();

            /**
             * setup defaults
             */
            self::$objInstance->min_wordlen = (Settings::get( 'minwordlength', 3 ) > 0 ? Settings::get( 'minwordlength', 3 ) : self::$objInstance->min_wordlen);
            self::$objInstance->max_wordlen = (Settings::get( 'maxwordlength', 10 ) > 0 ? Settings::get( 'maxwordlength', 10 ) : self::$objInstance->max_wordlen);

            self::$objInstance->init();
            self::$objInstance->setBadwords();
        }

        return self::$objInstance;
    }

    /**
     * @return array
     */
    public function getIndexerModules()
    {
        return self::$_indexModulOptions;
    }

    public function init()
    {
        $modules = $this->getApplication()->getModuleNames();
        foreach ( $modules as $modul )
        {
            $modul = strtolower( $modul );
            $modUcFirst = ucfirst( $modul );

            $_cls = $modUcFirst . '_Config_Base/getIndexerOptions';
            if ( checkClassMethod( $_cls ) )
            {
                $options = call_user_func( $modUcFirst . '_Config_Base::getIndexerOptions' );
                self::$_indexModulOptions[ $modul ] = $options;
            }
        }

        $plugins = Plugin::getInteractivePlugins();
        foreach ( $plugins as $key => $r )
        {
            $modul = strtolower( $r[ 'key' ] );
            $modUcFirst = ucfirst( $r[ 'key' ] );

            $_cls = 'Addon_' . $modUcFirst . '_Config_Base/getIndexerOptions';
            if ( checkClassMethod( $_cls ) )
            {
                $options = call_user_func( 'Addon_' . $modUcFirst . '_Config_Base::getIndexerOptions' );
                $options[ 'isAddon' ] = true;
                self::$_indexModulOptions[ $modul ] = $options;
            }
        }
    }

    /**
     *
     * @return Search
     */
    private function setTimeout()
    {
        // Timeout für das Indizeiren auf 3600 Sekunden sezten ( 1h )
        @set_time_limit( 3600 );
        return $this;
    }

    /**
     *
     * @param string $searchString
     * @return string
     */
    public function createSearchHash( $searchString = '' )
    {
        return strlen( $searchString ) > 0 ? substr( md5( $searchString ), 0, 10 ) : '';
    }

    /**
     *
     * @param string $hash
     * @return Search
     */
    public function setSearchHash( $hash )
    {
        $this->search_hash = $hash;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getResultHash()
    {
        return $this->search_hash;
    }

    /**
     *
     * @return Search
     */
    public function setBadwords()
    {
        // stopwords aus der Konfiguration
        if ( ($badwords = Settings::get( 'badsearchwords', '' )) !== '' )
        {
            $stopwords = preg_split( "/[\n,;]/", $badwords );
            foreach ( $stopwords as $wrd )
            {
                $this->stopwords[ $wrd ] = true;
            }
        }

        $words = Locales::getStopwords( null, true );
        $this->stopwords = array_merge( $this->stopwords, $words );

        unset( $words, $stopwords );

        return $this;
    }

    /**
     *
     * @param string $searchString
     * @param array  $types
     * @param array  $categories
     * @param null   $extra
     * @return int
     */
    public function doSearch( $searchString, $types = null, $categories = null, $extra = null )
    {
        $IP = $this->Env->ip();

        if ( $this->search_hash === null || $this->search_hash === '' )
        {
            // create new search hash
            $this->search_hash = $this->createSearchHash( $searchString . (is_array( $types ) ? serialize( $types ) : '') . (is_array( $categories ) ? serialize( $categories ) : '') . (is_array( $extra ) ? serialize( $extra ) : '') );
        }

        // Prüfen ob hash in der Datenbank existiert
        $r = $this->db->query( 'SELECT searchhash FROM %tp%search_spider WHERE searchhash = ?', $this->search_hash )->fetch();

        if ( $r[ 'searchhash' ] === $this->search_hash )
        {
            return 1;

            // $this->db->query( 'DELETE FROM %tp%search_spider WHERE searchhash = ?', $this->search_hash );
        }


        #$searchString = HTTP::getClean($searchString);
        // Timeout für die Suche nur auf 60 Sekunden setzen
        @set_time_limit( 60 );

        $si_forsearch_log = '';
        $incats_forsearch_log = '';

        // Suchen IN
        if ( !is_null( $types ) && is_array( $types ) )
        {
            $this->searched_sections = $types;
            $si_forsearch_log = serialize( $types );
        }

        if ( !is_null( $categories ) && is_array( $categories ) )
        {
            $this->searched_categories = $categories;
            $incats_forsearch_log = serialize( $categories );
        }


        $this->searchWordArray = array();

        $searchString = preg_replace( array(
            '/\. /',
            '/\.$/',
            '/\$/',
            '/: /',
            '/:$/',
            '/, /',
            '/,$/',
            '/[^\pN\pL \*\+"\.:,_-]/u' ), ' ', $searchString );

        // Check keyword string
        if ( !strlen( $searchString ) )
        {
            Error::raise( 'Empty keyword string' );
        }


        // Split keywords
        $arrChunks = array();
        preg_match_all( '/"[^"]*"|[\+\-]?[^ ]+\*?/s', $searchString, $arrChunks );

        $arrPhrases = array();
        $arrKeywords = array();
        $arrWildcards = array();
        $arrIncluded = array();
        $arrExcluded = array();

        $blnFuzzy = false;
        $blnOrSearch = true;


        foreach ( $arrChunks[ 0 ] as $idx => $strKeyword )
        {
            if ( substr( $strKeyword, -1 ) == '*' && ($len = strlen( $strKeyword )) > 1 )
            {
                if ( $len < $this->min_wordlen || $len > $this->max_wordlen || isset( $this->stopwords[ $strKeyword ] ) )
                {
                    continue;
                }
                $arrWildcards[] = '' . str_replace( '*', '%', $strKeyword );
                continue;
            }


            switch ( substr( $strKeyword, 0, 1 ) )
            {
                // exact Phrases
                case '"':
                    if ( ($strKeyword = trim( substr( $strKeyword, 1, -1 ) )) != false )
                    {
                        $len = strlen( $strKeyword );
                        $arrPhrases[] = '[[:<:]]' . str_replace( array(
                                    ' ',
                                    '*',
                                    '%' ), array(
                                    '[^[:alnum:]]+',
                                    '' ), $strKeyword ) . '[[:>:]]';
                    }
                    break;

                // Included keywords
                case '+':
                    if ( ($strKeyword = trim( substr( $strKeyword, 1 ) )) != false )
                    {
                        $strKeyword = str_replace( '"', '', $strKeyword );
                        $len = strlen( $strKeyword );
                        if ( $len < $this->min_wordlen || $len > $this->max_wordlen || isset( $this->stopwords[ $strKeyword ] ) )
                        {
                            continue;
                        }
                        $arrIncluded[] = $strKeyword;
                    }
                    break;

                // Excluded keywords
                case '-':
                    if ( ($strKeyword = trim( substr( $strKeyword, 1 ) )) != false )
                    {
                        $strKeyword = str_replace( '"', '', $strKeyword );
                        $len = strlen( $strKeyword );
                        if ( $len < $this->min_wordlen || $len > $this->max_wordlen || isset( $this->stopwords[ $strKeyword ] ) )
                        {
                            continue;
                        }


                        $arrExcluded[] = $strKeyword;
                    }
                    break;

                // Wildcards
                case '*':
                    if ( ($len = strlen( $strKeyword )) > 1 )
                    {
                        $strKeyword = str_replace( '"', '', $strKeyword );
                        if ( $len < $this->min_wordlen || $len > $this->max_wordlen || isset( $this->stopwords[ $strKeyword ] ) )
                        {
                            continue;
                        }
                        $arrWildcards[] = str_replace( '*', '%', $strKeyword );
                    }
                    break;

                // Normal keywords
                default:

                    $len = strlen( $strKeyword );
                    if ( $len < $this->min_wordlen || $len > $this->max_wordlen || isset( $this->stopwords[ $k ] ) )
                    {
                        continue;
                    }

                    $arrKeywords[] = $strKeyword;
                    break;
            }
        }

        // Fuzzy search
        if ( !$blnFuzzy )
        {
            foreach ( $arrKeywords as $strKeyword )
            {
                $strKeyword = str_replace( '"', '', $strKeyword );
                $arrWildcards[] = '%' . $strKeyword . '%';
            }

            $arrKeywords = array();
        }


        // Count keywords
        $intPhrases = count( $arrPhrases );
        $intWildcards = count( $arrWildcards );
        $intIncluded = count( $arrIncluded );
        $intExcluded = count( $arrExcluded );
        $intKeywords = count( $arrKeywords );


        $arrValues = array();
        $strQuery = '';


        // Prepare keywords array
        $arrAllKeywords = array();

        // Get keywords
        if ( count( $arrKeywords ) )
        {
            $arrAllKeywords[] = implode( ' OR ', array_fill( 0, count( $arrKeywords ), 'w.word LIKE ?' ) );
            $arrValues = array_merge( $arrValues, $arrKeywords );
            $intKeywords += count( $arrKeywords );
        }

        // Get included keywords
        if ( $intIncluded )
        {
            $arrAllKeywords[] = implode( ' OR ', array_fill( 0, $intIncluded, 'w.word LIKE ?' ) );
            $arrValues = array_merge( $arrValues, $arrIncluded );
            $intKeywords += $intIncluded;
        }

        // Get keywords from phrases
        if ( $intPhrases )
        {
            foreach ( $arrPhrases as $strPhrase )
            {
                $arrWords = explode( '[^[:alnum:]]+', utf8_substr( $strPhrase, 7, -7 ) );
                $arrAllKeywords[] = implode( ' OR ', array_fill( 0, count( $arrWords ), 'w.word LIKE ?' ) );
                $arrValues = array_merge( $arrValues, $arrWords );
                $intKeywords += count( $arrWords );
            }
        }


        // Get wildcards
        if ( $intWildcards )
        {
            $arrAllKeywords[] = implode( ' OR ', array_fill( 0, $intWildcards, 'f.content LIKE ?' ) );
            $arrValues = array_merge( $arrValues, $arrWildcards );
        }


        if ( count( $arrAllKeywords ) )
        {
            $strQuery .= " (" . implode( ' OR ', $arrAllKeywords ) . ")";
        }

        if ( $intPhrases )
        {
            $arrValues = array_merge( $arrValues, $arrPhrases );
            $strQuery .= ($strQuery ? ' AND ' : '') . " (" . implode( ($blnOrSearch ? ' OR ' : ' AND ' ), array_fill( 0, $intPhrases, 'i.indexid IN(SELECT pr.id FROM %tp%search_index AS pr WHERE pr.word REGEXP ?)' ) ) . ")";
        }

        if ( $intKeywords )
        {
            //    $arrValues = array_merge( $arrValues, $arrKeywords );
            //    $addSql[ ]  = ' OR ';
            //    $addSql[ ]  = implode( ' OR ', array_fill( 0, count( $arrKeywords ), 'w.word=?' ) );
            //    $strQuery .= ($strQuery ? ' AND ' : '') . " w.id IN(SELECT kw.id FROM %tp%search_index AS kw WHERE " . implode( ' OR ', array_fill( 0, $intKeywords, 'kw.word=?' ) ) . ")";
        }

        if ( $intIncluded )
        {
            $arrValues = array_merge( $arrValues, $arrIncluded );
            $strQuery .= ($strQuery ? ' AND ' : '') . " w.id IN(SELECT inc.id FROM %tp%search_index AS inc WHERE " . implode( ' OR ', array_fill( 0, $intIncluded, 'inc.word=?' ) ) . ")";
        }

        if ( $intExcluded )
        {
            $arrValues = array_merge( $arrValues, $arrExcluded );
            $strQuery .= ($strQuery ? ' AND ' : '') . " w.id NOT IN(SELECT ex.id FROM %tp%search_index AS ex WHERE " . implode( ' OR ', array_fill( 0, $intExcluded, 'ex.word=?' ) ) . ")";
        }


        if ( !trim( $strQuery ) )
        {
            return 0;
        }

        /*
          //$intKeywords = 0;
          // Get keywords
          if ( count( $arrKeywords ) )
          {
          $intKeywords += count( $arrKeywords );
          }
         * 
         */

        // Get included keywords
        if ( $intIncluded )
        {
            $intKeywords += $intIncluded;
        }

        // Get keywords from phrases
        if ( $intPhrases )
        {
            foreach ( $arrPhrases as $strPhrase )
            {
                $arrWords = explode( '[^[:alnum:]]+', utf8_substr( $strPhrase, 7, -7 ) );
                $intKeywords += count( $arrWords );
            }
        }


        // Abfrage
        $s = false;
        if ( is_array( $this->searched_sections ) && count( $this->searched_sections ) )
        {
            $s = true;
        }


        $sql = "SELECT DISTINCT i.fulltextid, COUNT(*) AS count, SUM(i.score) AS scores";


        // Get the number of wildcard matches
        if ( !$blnOrSearch && $intWildcards )
        {
            $sql .= ", (SELECT COUNT(*) FROM %tp%search_index WHERE (" . implode( ' OR ', array_fill( 0, $intWildcards, 'word LIKE ?' ) ) . ") AND id=i.indexid) AS wildcards";
            $arrValues = array_merge( $arrValues, $arrWildcards );
        }

        $sql .= " FROM %tp%search_fulltext AS f
				LEFT JOIN %tp%search_wordindex AS i ON(i.fulltextid=f.id)
				LEFT JOIN %tp%search_index AS w ON(w.id = i.indexid)
				WHERE " . $strQuery . "" . ($s ? " AND i.section_id IN(" . implode( ',', $this->searched_sections ) . ")" : "") . "
				GROUP BY f.contentid, i.section_id";


        // Make sure to find all words
        if ( !$blnOrSearch )
        {
            // Number of keywords without wildcards
            $sql .= " HAVING count >= " . $intKeywords;

            // Dynamically add the number of wildcard matches
            if ( $intWildcards )
            {
                $sql .= " + IF(wildcards>" . $intWildcards . ", wildcards, " . $intWildcards . ")";
            }
        }

        $sql .= " LIMIT 0," . $this->max_searchlimit;

        $result = $this->db->query( $sql, $arrValues )->fetchAll();

        $search_time = time();
        $_inserts = '';
        foreach ( $result as $row )
        {
            $_inserts .= ", (" . $this->db->quote( $this->search_hash ) . ", " . $this->db->quote( $row[ 'scores' ] ) . ", " . $row[ 'fulltextid' ] . ", " . $search_time /* . ", ". $this->db->quote($row['matches']) */ . ")";
        }

        $found = 0;

        if ( !empty( $_inserts ) )
        {
            $inserts = substr( $_inserts, 1 );
            $this->db->query( "REPLACE INTO %tp%search_spider (searchhash, score, indexid, searchtime/*, matches*/) VALUES " . $inserts );
            $found = 1;
        }

        //
        $searchString = str_replace( "'", "", $searchString );


        // Search Log erzeugen
        $this->db->query( 'REPLACE INTO %tp%search_log (q,searchtime,searchhash,ip,orderby,sort,perpage,si) VALUES(?,?,?,?,?,?,?,?)', array(
            $searchString,
            $search_time,
            $this->search_hash,
            $IP,
            $order_forsearch_log ? $order_forsearch_log : '',
            $sort_forsearch_log ? $sort_forsearch_log : '',
            $per_page ? $per_page : 20,
            (is_array( $types ) ? serialize( $types ) : '')
        ) );


        // Löscht alte Suchanfragen
        $rs = $this->db->query_first( 'SELECT COUNT(id) as total FROM %tp%search_spider WHERE searchtime < ?', (time() - $this->cleanup_time ) );
        if ( $rs[ 'total' ] >= 10 )
        {
            $sql = "DELETE FROM %tp%search_spider WHERE searchtime < " . (time() - $this->cleanup_time) . " AND searchhash != ?";
            $this->db->query( $sql, $this->search_hash );
        }

        // Optimieren der Tabelle
        $sql = "OPTIMIZE TABLE %tp%search_spider";
        $this->db->query( $sql );

        return $found;
    }

    // Hash erzeugen
    /**
     * @param $searchString
     * @return string
     */
    public function create_search_hash( $searchString )
    {
        return substr( md5( $searchString ), 0, 10 );
    }

    /**
     * @return array
     */
    public function getSearchables()
    {
        $result = $this->db->query( 'SELECT * FROM %tp%search_sections' )->fetchAll();

        $data = array();
        $controller = $this->getController();
        foreach ( $result as $r )
        {
            $cls = ucfirst( strtolower( $r[ 'section_key' ] ) ) . '_Config_Base';

            if ( $controller->isPlugin( $r[ 'section_key' ] ) )
            {
                $cls = 'Addon_' . ucfirst( strtolower( $r[ 'section_key' ] ) ) . '_Config_Base';
            }

            if ( checkClassMethod( $cls . '/getModulDefinition' ) )
            {
                $options = call_user_func( $cls . '::getModulDefinition' );
                $data[] = array(
                    'value' => $r[ 'id' ],
                    'label' => $options[ 'modulelabel' ] );
            }
        }

        return $data;
    }

    // Letzte Spider ID für die Resultate holen
    /**
     * @param string $hash
     */
    public function _get_index_ids( $hash = '' )
    {
        if ( $hash )
        {
            foreach ( $this->db->query( "SELECT indexid FROM %tp%search_spider searchhash = ?", $hash )->fetchAll() as $r )
            {
                $this->index_ids[] = $r[ 'indexid' ];
            }
        }
    }

}
