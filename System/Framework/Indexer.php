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
class Indexer extends Loader
{

    /**
     *
     * @var Indexer
     */
    protected static $objInstance;

    /**
     * Minimale wortlänge in Zeichen (Standart)
     * @var integer
     */
    private $min_wordlen = 3;

    /**
     *
     * @var integer
     */
    private $max_wordlen = 30;

    /**
     *
     * @var integer
     */
    private $limit = 250;

    /**
     * @var int
     */
    private $total = 0;

    /**
     *
     * @var
     */
    private $stopwords = array();

    /**
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
    private $all_words = array();

    /**
     * @var array
     */
    private $index_ids = array();

    /**
     * @var null
     */
    protected $progressbar = null;

    /**
     * @var bool
     */
    public $isCronjob = false;

    /**
     *
     * @var type
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
     * @return Indexer
     */
    public static function getInstance()
    {
        if ( !is_object( self::$objInstance ) )
        {
            self::$objInstance = new Indexer();

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
     * @param null $limit
     */
    public function setLimit( $limit = null )
    {
        $this->limit = $limit;
    }

    /**
     *
     * @return Indexer
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
     * @param $currentModul
     * @return int|string
     */
    private function getNextModul( $currentModul )
    {
        $x = 0;
        foreach ( self::$_indexModulOptions as $mod => $opts )
        {
            if ( $currentModul == $mod )
            {
                $x++;
                break;
            }
            $x++;
        }
        $y = 0;
        foreach ( self::$_indexModulOptions as $mod => $opts )
        {
            if ( $x == $y && $mod )
            {
                return $mod;
            }
            $y++;
        }

        return '';
    }

    // alten Index löschen
    /**
     * @param $section
     * @param $lang
     * @param $location
     */
    private function deleteIndexes( $section, $lang, $location )
    {
        $section_id = $this->getIndexSection( $section, $lang, $location );

        if ( defined( 'IS_CLI' ) && IS_CLI )
        {
            Cli::p( 'Deleting Fulltext...' );
            $this->db->query( "DELETE FROM %tp%search_fulltext WHERE section_id = ?", $section_id );

            Cli::p( 'Deleting Word Index...' );
            $this->db->query( "DELETE FROM %tp%search_wordindex WHERE section_id = ?", $section_id );
        }
        else
        {
            $this->db->query( "DELETE FROM %tp%search_fulltext WHERE section_id = ?", $section_id );
            $this->db->query( "DELETE FROM %tp%search_wordindex WHERE section_id = ?", $section_id );
            //$this->db->query("DELETE FROM %tp%search_index WHERE section_id = ?", $section_id);
        }


        return;
    }

    /**
     * @param $opts
     */
    public function removeIndex( $opts )
    {
        set_time_limit( 20 );
        $section_id = $this->getIndexSection( $opts[ 'modul' ], CONTENT_TRANS, $opts[ 'location' ] );


        $this->db->query( "DELETE FROM %tp%search_fulltext WHERE section_id = ? AND contentid = ? AND controller = ? AND action = ?", $section_id, $opts[ 'contentid' ], $opts[ 'controller' ], $opts[ 'action' ] );
    }

    /**
     * Use this Function when add new content.
     * 
     * @param array $opts
     */
    public function buildIndex( $opts )
    {

        $section_id = $this->getIndexSection( $opts[ 'modul' ], CONTENT_TRANS, $opts[ 'location' ] );

        if ( is_array( $opts[ 'data' ] ) )
        {
            foreach ( $opts[ 'data' ] as $r )
            {
                $arr = array(
                    'suffix'     => $r[ 'suffix' ],
                    'alias'      => $r[ 'alias' ],
                    'title'      => $r[ 'title' ],
                    'content'    => $r[ 'content' ],
                    'time'       => $r[ 'time' ],
                    'contentid'  => $r[ 'contentid' ],
                    'controller' => $opts[ 'controller' ],
                    'action'     => $opts[ 'action' ],
                    'groups'     => $r[ 'groups' ],
                    'section_id' => $section_id
                );
                $this->createIndex( $arr );
            }
        }
        else
        {
            // remove
            foreach ( $opts[ 'remove' ] as $r )
            {
                $this->db->query( "DELETE FROM %tp%search_fulltext WHERE section_id = ? AND contentid = ? AND controller = ? AND action = ?", $section_id, $r[ 'contentid' ], $opts[ 'controller' ], $opts[ 'action' ] );
            }
        }

        $this->cleanSearchIndex();
        $this->optimizeIndexes();
    }

    /**
     * @param     $y
     * @param int $page
     */
    private function getData( &$y, $page = 1 )
    {
        static $sects;

        if ( !is_array( $sects ) )
        {
            $sects = array();
        }

        $from = ($this->limit * ($page - 1));
        $res = $this->db->query( 'SELECT * FROM %tp%indexer LIMIT ' . $from . ',' . $this->limit )->fetchAll();
        foreach ( $res AS $r )
        {
            $this->setProgress( $y, $r[ 'modul' ], $page, '' );
            Session::save( 'indexer', array(
                'current'   => $y,
                'page'      => $page,
                'nextModul' => $r[ 'modul' ] ) );

            if ( isset( self::$_indexModulOptions[ $r[ 'modul' ] ] ) )
            {
                $opts = self::$_indexModulOptions[ $r[ 'modul' ] ];

                if ( !isset( $sects[ $r[ 'modul' ] ] ) )
                {
                    $section_id = $this->getIndexSection( $r[ 'modul' ], CONTENT_TRANS, $opts[ 'location' ] );
                }
                else
                {
                    $section_id = $sects[ $r[ 'modul' ] ];
                }


                // forum patch
                if ( $r[ 'modul' ] == 'forum' && preg_match( '/\/$/', $r[ 'alias' ] ) )
                {
                    $r[ 'alias' ] = $r[ 'alias' ] . Library::suggest( $r[ 'title' ] );
                }




                $arr = array(
                    'suffix'     => $r[ 'suffix' ],
                    'alias'      => $r[ 'alias' ],
                    'title'      => $r[ 'title' ],
                    'content'    => $r[ 'content' ],
                    'time'       => $r[ 'content_time' ],
                    'contentid'  => $r[ 'contentid' ],
                    'controller' => $opts[ 'controller' ],
                    'action'     => $opts[ 'action' ],
                    'groups'     => $r[ 'groups' ],
                    'section_id' => $section_id
                );

                $this->createIndex( $arr );
            }

            $y++;
        }
    }

    /**
     * 
     */
    public function buildIndexes()
    {
        @set_time_limit( 0 );

        $ts = intval( HTTP::input( 'ts' ) ) ? intval( HTTP::input( 'ts' ) ) : 0;
        $nextmodul = HTTP::input( 'nextmodul' ) ? HTTP::input( 'nextmodul' ) : false;
        $complete = intval( HTTP::input( 'clear' ) ) == 1 ? 1 : 0;
        $page = intval( HTTP::input( 'page' ) ) > 1 ? intval( HTTP::input( 'page' ) ) : 1;
        $current = intval( HTTP::input( 'current' ) ) > 1 ? intval( HTTP::input( 'current' ) ) : false;


        if ( $this->isCronjob )
        {
            $this->limit = 10000;
        }


        if ( $page === 1 )
        {
            $this->db->query( 'TRUNCATE TABLE %tp%search_fulltext' );
            $this->db->query( 'TRUNCATE TABLE %tp%search_results' );
            $this->db->query( 'TRUNCATE TABLE %tp%search_spider' );
            $this->db->query( 'TRUNCATE TABLE %tp%search_index' );
            $this->db->query( 'TRUNCATE TABLE %tp%search_wordindex' );
        }


        $pages = ceil( $this->total / $this->limit );
        $forceStop = true;
        $y = $current ? $current : 1;

        $this->getData( $y, $page );


        if ( $page < $pages )
        {
            $this->setProgress( $y, '', ($page + 1 ), '' );

            echo Library::json( array(
                'success'  => true,
                'nextpage' => ($page + 1),
                'current'  => $y ) );

            // Library::redirect( 'admin.php?adm=indexer&page=' . ($page + 1) . '&RUNID=' . $this->getProgressBar()->getUniqueId() );
            exit;
        }
        else
        {


            $this->db->query( 'TRUNCATE TABLE %tp%indexer' );

            Library::sendJson( true, trans( 'Suchmaschienen Index wurde neu erzeugt.' . $this->total . ' ' . $y ) );
        }

        exit;
    }

    /**
     * @param int    $y
     * @param string $nextModul
     * @param int    $page
     * @param string $message
     */
    private function setProgress( $y = 1, $nextModul = '', $page = 1, $message = '' )
    {
        $data = array(
            'success'     => true,
            'globalTotal' => $this->total,
            'current'     => $y,
            'nextModul'   => $nextModul,
            'page'        => $page,
            'message'     => $message
        );
        $this->progressbar->set( json_encode( $data ) );
    }

    public function initIndexer()
    {
        $ts = intval( HTTP::input( 'ts' ) ) ? intval( HTTP::input( 'ts' ) ) : 0;


        $this->initProgressBar( $ts );


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


        $this->total = 0;

        foreach ( self::$_indexModulOptions as $mod => $opts )
        {
            if ( !empty( $opts[ 'countData' ] ) )
            {
                if ( !$opts[ 'isAddon' ] )
                {
                    $model = Model::getModelInstance( $mod );
                    $total = call_user_func_array( array(
                        $model,
                        $opts[ 'countData' ] ), array(
                        $this ) );
                    self::$_indexModulOptions[ $mod ][ 'total_items' ] = $total;
                }
                else if ( $opts[ 'isAddon' ] )
                {
                    $model = Model::getModelInstance( 'Addon_' . ucfirst( strtolower( $mod ) ) );
                    $total = call_user_func_array( array(
                        $model,
                        $opts[ 'countData' ] ), array(
                        $this ) );
                    self::$_indexModulOptions[ $mod ][ 'total_items' ] = $total;
                }

                $this->total += $total;
            }
            else
            {
                unset( self::$_indexModulOptions[ $mod ] );
            }
        }
    }

    /**
     *
     * @param $ts
     * @internal param string $key
     */
    public function initProgressBar( $ts )
    {
        $this->progressbar = new Progressbar( 'searchindex', 480 );
    }

    /**
     * @return array|type
     */
    public function getIndexableModules()
    {
        return self::$_indexModulOptions;
    }

    /**
     *
     * @return Progressbar
     */
    public function getProgressBar()
    {
        return $this->progressbar;
    }

    /**
     *
     * @staticvar integer $section_id
     * @param string $section_key
     * @param string $lang_key
     * @param string $location
     * @return integer
     */
    public function getIndexSection( $section_key, $lang_key = 'de', $location = '' )
    {

        static $_sects;


        if ( !is_array( $_sects ) )
        {
            $_sects = array();
        }

        $hash = md5( $section_key . $lang_key . $location );
        if ( !isset( $_sects[ $hash ] ) )
        {

            $r = $this->db->query( 'SELECT id FROM %tp%search_sections WHERE `section_key` = ? AND `lang` = ? AND `location` = ? LIMIT 1', $section_key, $lang_key, $location )->fetch();

            if ( $r[ 'id' ] )
            {
                $_sects[ $hash ] = $r[ 'id' ];
            }
            else
            {
                $this->db->query( 'INSERT INTO %tp%search_sections (`section_key`, `lang`, `location`) VALUES(?, ?, ?)', $section_key, $lang_key, $location );
                $_sects[ $hash ] = $this->db->insert_id();
            }
        }


        return $_sects[ $hash ];
    }

    /**
     *
     * @param array $index_data
     * @return void
     */
    public function createIndex( $index_data = array() )
    {
        $index_time = time();
        $location_url = $index_data[ 'location' ];


        $title = $this->prepare_content( $index_data[ 'title' ] );
        $text = $this->prepare_content( $index_data[ 'content' ] );
        $content_size = $this->get_content_size( $index_data[ 'content' ] . ' ' . $index_data[ 'title' ] );
        $content_time = $index_data[ 'time' ];


        $section_id = $index_data[ 'section_id' ] ? $index_data[ 'section_id' ] : 0;

        $title = utf8_substr( $title, 0, 200 );


        $checksum = md5( preg_replace( '/ +/', ' ', strip_tags( $title . $text ) ) );


        // Prüfen ob im Index Vorhanden
        $indexs = $this->db->query( "SELECT id, checksum FROM %tp%search_fulltext WHERE contentid = ? AND controller = ? AND action = ? AND section_id = ?", $index_data[ 'contentid' ], $index_data[ 'controller' ], $index_data[ 'action' ], $section_id )->fetch();


        if ( $indexs[ 'id' ] && $checksum == $indexs[ 'checksum' ] )
        {
            return false;
        }


        // Wenn index Existiert aktualisiren
        if ( $indexs[ 'id' ] > 0 )
        {
            $sql = "UPDATE %tp%search_fulltext SET
                    checksum = " . $this->db->quote( $checksum ) . ",
                    title = " . $this->db->quote( $title ) . ",
		            content = " . $this->db->quote( $text ) . ",
                    alias = " . $this->db->quote( $index_data[ 'alias' ] ) . ",
                    suffix = " . $this->db->quote( $index_data[ 'suffix' ] ) . ",
		            index_time = " . $this->db->quote($index_time ) . ",
                    content_time = " . $this->db->quote($content_time ) . ",
                    content_bytes = " . $this->db->quote($content_size ) . ",
                    controller = " . $this->db->quote( $index_data[ 'controller' ] ) . ",
                    action = " . $this->db->quote( $index_data[ 'action' ] ) . ",
                    groups = " . $this->db->quote( $index_data[ 'groups' ] ) . ",
                    contentid = " . $this->db->quote($index_data[ 'contentid' ] ) . ",
                    section_id = " . $section_id . ",
                    apptype = " . $this->db->quote( (isset( $index_data[ 'apptype' ] ) ? $index_data[ 'apptype' ] : '' ) ) . "
                    WHERE id = " . $indexs[ 'id' ];

            $this->db->query( $sql );
        }
        else
        {
            $sql = "INSERT INTO %tp%search_fulltext (checksum, alias, suffix, title, content, index_time, content_time, content_bytes, section_id, controller, action, groups, contentid, appid, apptype)
                    VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $this->db->query( $sql, array(
                $checksum,
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
                (isset( $index_data[ 'appid' ] ) ? intval( $index_data[ 'appid' ] ) : 0),
                (isset( $index_data[ 'apptype' ] ) ? $index_data[ 'apptype' ] : '')
            ) );

            $indexs[ 'id' ] = $this->db->insert_id();
        }

        // ===========================================================
        $scores = array();
        $enginefiller = array();
        $wordcacheFull = array();


        // Übergebene Wörter zerlegen
        $words = trim( (string) $title );
        $allwords = ' ' . $words . ' ';

        $wordarray = preg_split( '/ +/', utf8_strtolower( $allwords ) );

        foreach ( $wordarray as $word )
        {
            // Strip a leading plus
            if ( strncmp( $word, '+', 1 ) === 0 )
            {
                $word = utf8_substr( $word, 1 );
            }

            $word = trim( $word );

            if ( !strlen( $word ) || preg_match( '/^[\.\?!:,\'_-]+$/', $word ) )
            {
                continue;
            }

            if ( preg_match( '/^[\':,]/', $word ) )
            {
                $word = utf8_substr( $word, 1 );
            }

            if ( preg_match( '/[\':,\.\?!]$/', $word ) )
            {
                $word = utf8_substr( $word, 0, -1 );
            }

            // Leerzeichen und zu kurze wörter überspringen
            $len = strlen( $word );
            if ( ord( $word ) == 0 || $len < $this->min_wordlen || $len > $this->max_wordlen || isset( $this->stopwords[ $word ] ) )
            {
                continue;
            }

            if ( !isset( $this->wordcache[ $word ] ) )
            {
                $wordcacheFull[] = $word;
            }


            if ( isset( $scores[ $word ] ) )
            {
                $scores[ $word ] ++;
            }
            else
            {
                $scores[ $word ] = 1;
            }
        }


        // Text
        $allwords = trim( (string) $text );
        $allwords = ' ' . $allwords . ' ';
        $wordlist = preg_split( '/ +/', utf8_strtolower( $allwords ) );

        foreach ( $wordlist as $word )
        {
            // Strip a leading plus
            if ( strncmp( $word, '+', 1 ) === 0 )
            {
                $word = utf8_substr( $word, 1 );
            }

            $word = trim( $word );

            if ( !strlen( $word ) || preg_match( '/^[\.\?!:,\'_-]+$/', $word ) )
            {
                continue;
            }

            if ( preg_match( '/^[\':,]/', $word ) )
            {
                $word = utf8_substr( $word, 1 );
            }

            if ( preg_match( '/[\':,\.\?!]$/', $word ) )
            {
                $word = utf8_substr( $word, 0, -1 );
            }

            // Leerzeichen und zu kurze wörter überspringen
            $len = strlen( $word );

            if ( ord( $word ) == 0 || $len < $this->min_wordlen || $len > $this->max_wordlen || isset( $this->stopwords[ $word ] ) )
            {
                continue;
            }

            if ( !isset( $this->wordcache[ $word ] ) )
            {
                $wordcacheFull[] = $word;
            }


            if ( isset( $scores[ $word ] ) )
            {
                $scores[ $word ] ++;
            }
            else
            {
                $scores[ $word ] = 1;
            }
        }

        if ( count( $wordcacheFull ) )
        {
            $getwordidsql = "word IN(" . $this->db->quote( implode( ',', $wordcacheFull ) ) . ")";

            $sql = "SELECT id, word FROM %tp%search_index WHERE $getwordidsql";
            $res = $this->db->query( $sql, $section_id )->fetchAll();
            foreach ( $res as $r )
            {
                $this->wordcache[ $r[ 'word' ] ] = $r[ 'id' ];
            }
        }


        $insertsql = array();
        $newwords = array();
        $newtitlewords = '';

        foreach ( $scores as $word => $score )
        {

            if ( $word != '' )
            {
                if ( isset( $this->wordcache[ $word ] ) )
                {
                    // Does this word already exist in the word table?
                    $insertsql[] = ",(fulltextid = " . $indexs[ 'id' ] . ", score = " . $score . ", section_id = " . $section_id . " WHERE indexid = " . intval( $this->wordcache[ $word ] ) . ")";
                }
                else
                {
                    $newwords[] = $this->db->quote( $word ); // No so add it to the word table
                }
            }
        }

        $sum = count( $insertsql );
        if ( $sum )
        {

            if ( $sum == 1 )
            {
                $insertsql = substr( implode( '', $insertsql ), 2 );
                $insertsql = substr( $insertsql, 0, -1 );

                $sql = "UPDATE %tp%search_wordindex SET " . $insertsql;
                $this->db->query( $sql );
            }
            elseif ( $sum > 1 )
            {
                //$insertsql = substr( implode( '', $insertsql ), 1 );
                foreach ( $insertsql as $q )
                {
                    $q = substr( $q, 2 );
                    $q = substr( $q, 0, -1 );
                    $sql = "UPDATE %tp%search_wordindex SET " . $q;
                    $this->db->query( $sql );
                }
            }


            unset( $insertsql );
        }


        //$newwords = trim((string)$newwords);
        $sum = count( $newwords );
        if ( $sum )
        {
            $insertwords = '';

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

            unset( $insertwords );

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

            unset( $scoressql );
        }

        unset( $wordcacheFull, $scores );

        $this->wordcache = array();

        return;
    }

    // Keywords bereinigen
    public function cleanSearchIndex()
    {
        return;
    }

    // Tabellen Optimieren
    /**
     * @return bool
     */
    public function optimizeIndexes()
    {
        $this->db->query( "OPTIMIZE TABLE %tp%search_fulltext" );
        $this->db->query( "OPTIMIZE TABLE %tp%search_index" );
        $this->db->query( "OPTIMIZE TABLE %tp%search_wordindex" );

        return true;
    }

    // Inhalt parsen und bereinigen
    /**
     * @param $text
     * @return string
     */
    private function prepare_content( $text )
    {
	    $text = preg_replace('#(<(script|style)([^>]*)>.*</$2>)#isU', ' ', $text );
	    $text = preg_replace('#(</?([^>]*)>)#', ' ', $text );

        $text = Strings::unhtmlspecialchars( $text, true );
        $text = Strings::rehtmlconverter( $text );
        $text = Strings::fixLatin( $text );

	    $text = BBCode::removeBBCode($text);
	    $text = BBCode::removeSmilies($text);


        $text = str_replace( array(
            "\n",
            "\r",
            "\t",
            '&#160;',
            '&nbsp;' ), ' ', $text );

        // Alle HTML Tags löschen
        $text = str_replace( array('´', '`' ), "'", $text );

        #$text = preg_replace( '!([\w]+)([\-_\+\*#]*)([\w]+)!is', '$1 $3', $text );


        // Remove special characters
        if ( function_exists( 'mb_eregi_replace' ) )
        {
            $text = mb_eregi_replace( '[^[:alnum:]\'\.:,\+_-]|- | -|\' | \'|\. |\.$|: |:$|, |,$', ' ', $text );
        }
        else
        {

            $text = preg_replace(array('/- /s', '/ -/s', "/' /s", "/ '/s", '/\. /s', '/\.$/', '/: /s', '/:$/', '/, /s', '/,$/', '/[^\pN\pL\'\.:,\+_-]/u'), ' ', $text);
        }

        // other BBCode Remove
        $text = preg_replace( '!(\[/?[^\]]*\])!isU', ' ', $text );

        $text = preg_replace( '#[_\- ]+$#s', ' ', $text );

        //replace multiple spaces
        $text = preg_replace( '#\s\s*#', ' ', $text );


        return trim( $text );

    }

    /**
     * @param $content
     * @return int
     */
    private function get_content_size( $content )
    {
        // $content = strip_tags($content);
        return strlen( $content );
    }

}
