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
 * @package      DreamCMS
 * @version      3.0.0 Beta
 * @category     Framework
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Abstract.php
 */
abstract class Document_Abstract extends Model_Abstract
{

	/**
	 * @var null
	 */
	protected $_siteoutputdata = null;

	/**
	 *
	 * @var array
	 */
	protected static $metatags = array (
		'author'            => null,
		'description'       => null,
		'keywords'          => null,
		'language'          => null,
		'created'           => null,
		'lastmodify'        => null,
		'copyright'         => null,
		'expires'           => null,
		'robot_indexfollow' => null,
		'robot_revisit'     => null
	);

	/**
	 * @var bool
	 */
	protected static $_defaultLoaded = false;

	/**
	 *
	 * @var integer
	 */
	protected static $_aliasRegistryID;

	/**
	 * the alias registry data
	 *
	 * @var array
	 */
	protected static $_registryData = null;

	/**
	 * @var null
	 */
    public static $_tableName = null;

	/**
	 *
	 * @var integer
	 */
	protected static $_documentID = null;

	/**
	 * store the original document data
	 *
	 * @var array
	 */
	protected static $_documentData = null;

	/**
	 * store the original translation document data
	 *
	 * @var array
	 */
	protected static $_documentTranslationData = null;

	/**
	 * store all changes of the current document
	 *
	 * @var array
	 */
	protected static $_documentChangeData = null;

	/**
	 * store all translation changes of the current document
	 *
	 * @var array
	 */
	protected static $_documentTranslationChangeData = null;

	/**
	 *
	 * @var boolean
	 */
	protected static $_documentHasMeta = false;

	/**
	 *
	 * @var string
	 */
	protected static $_primaryKeyInMainTable = null;

	/**
	 *
	 * @var string
	 */
	protected static $_primaryKeyInTransTable = null;

	/**
	 *
	 * @var boolean
	 */
	protected static $_useDocumentTranslation = false;

	/**
	 *
	 * @var array
	 */
	protected static $_translateableFields = null;

	/**
	 * used in cp_translations
	 *
	 * @var string
	 */
	protected $_sourceMode = null;

	/**
	 *
	 * @var string
	 */
	protected static $_commentType = null;

	/**
	 *
	 * @var integer
	 */
	protected static $_lastModified = null;

	/**
	 *
	 * @var integer the Document ID
	 */
	protected static $_commentPostId = null;

	/**
	 *
	 * @var array
	 */
	public $_rssHeaders = null;

	/**
	 *
	 * @var bool
	 */
	protected static $cachable = false;

	/**
	 *
	 * @var integer
	 */
	protected static $cachtime = null;

	/**
	 * @var null
	 */
	protected static $cachgroups = null;

	/**
	 *
	 * @var bool
	 */
	protected static $clickanalyse = false;

	/**
	 * @var null
	 */
	protected static $commentingPermKey = null;

	/**
	 * @var null
	 */
	protected static $commentingPermValue = null;

	/**
	 *
	 * @var string
	 */
	protected static $metaKeywords = null;

	/**
	 *
	 * @var string
	 */
	protected static $metaDescription = null;

	/**
	 *
	 * @var boolean
	 */
	protected static $_rollback = false;

	/**
	 * Basic Tables Meta
	 *
	 */
	public $tableCoreMetaFieldDefinition = array (
		'pageid'           => array (
			'type'    => 'int',
			'length'  => 10,
			'default' => 0,
			'index'   => true
		),
		// the website
		'clickanalyse'     => array (
			'type'    => 'tinyint',
			'length'  => 1,
			'default' => 0
		),
		// enable click analyse
		'searchable'       => array (
			'type'    => 'tinyint',
			'length'  => 1,
			'default' => 1
		),
		// document is searchable
		'language'         => array (
			'type'    => 'char',
			'length'  => 6,
			'default' => ''
		),
		// the base Language
		'languagefallback' => array (
			'type'    => 'tinyint',
			'length'  => 1,
			'default' => 1
		),
		// if the document exists in the current language then display the document in the current language if not exists show the document in the document language.
		// also is set to 1 then show the document in existing language
		// if set 0 and if not exists in the current language then do not display the document
		'activemenuitemid' => array (
			'type'    => 'int',
			'length'  => 10,
			'default' => 0
		),
		'published'        => array (
			'type'    => 'tinyint',
			'length'  => 2,
			'default' => 1,
			'index'   => true
		),
		'publishon'        => array (
			'type'    => 'int',
			'length'  => 11,
			'default' => 0,
			'index'   => true
		),
		'publishoff'       => array (
			'type'    => 'int',
			'length'  => 11,
			'default' => 0,
			'index'   => true
		),
		'indexfollow'      => array (
			'type'    => 'varchar',
			'length'  => 15,
			'default' => 1
		),
		'target'           => array (
			'type'    => 'varchar',
			'length'  => 10,
			'default' => ''
		),
		// the article target
		'cacheable'        => array (
			'type'    => 'tinyint',
			'length'  => 1,
			'default' => 0
		),
		'cachetime'        => array (
			'type'    => 'int',
			'length'  => 8,
			'default' => 0
		),
		'cachegroups'      => array (
			'type'     => 'varchar',
			'length'   => 250,
			'default'  => '',
			'datatype' => 'split'
		),
		'goto'             => array (
			'type'    => 'int',
			'length'  => 10,
			'default' => 0
		),
		/* since version 2.0 */
		'draft'            => array (
			'type'    => 'tinyint',
			'length'  => 1,
			'default' => 0
		),
		'rollback'         => array (
			'type'    => 'tinyint',
			'length'  => 1,
			'default' => 0
		),
		// only for translation rollback (temp article)
	);

	/**
	 * Translation Tables Meta
	 * since version 2.0
	 */
	public $tableTranslationMetaDefinition = array (
		'lang'            => array (
			'type'    => 'char',
			'length'  => 6,
			'default' => '',
			'index'   => true
		),
		'iscorelang'      => array (
			'type'    => 'tinyint',
			'length'  => 1,
			'default' => 0
		),
		'alias'           => array (
			'type'    => 'varchar',
			'length'  => 150,
			'default' => '',
			'index'   => true
		), // @since version 2.0.1 moved to the alias registry
		'suffix'          => array (
			'type'    => 'varchar',
			'length'  => 6,
			'default' => ''
		), // @since version 2.0.1 moved to the alias registry
		// @since version 2.0.1 added the alias registry id
		// 'rewriteid' => array('type' => 'int', 'length' => 10, 'default' => 0),
		'pagetitle'       => array (
			'type'    => 'varchar',
			'length'  => 250,
			'default' => ''
		),
		'metadescription' => array (
			'type' => 'text'
		),
		'metakeywords'    => array (
			'type' => 'text'
		),
		'draft'           => array (
			'type'    => 'tinyint',
			'length'  => 1,
			'default' => 0
		),
		'rollback'        => array (
			'type'    => 'tinyint',
			'length'  => 1,
			'default' => 0
		),
	);

    /**
     *
     * @param string $method
     * @param $arguments
     * @throws BaseException
     * @internal param $mixed /array $arguments
     */
    public function __call( $method, $arguments )
    {
        throw new BaseException( 'Class Document and Model `' . get_class( $this ) . '` has no method `' . $method . '`.' );
    }


    /**
     * @return null|string
     */
    public function getUsedTable() {
        if (method_exists('parent', 'getUsedTable')) {
            return parent::getUsedTable();
        }

        return self::$_tableName;
    }

	/**
	 *
	 * @return void
	 */
	public function loadDefaultMetatags ()
	{

		if ( self::$_defaultLoaded )
		{
			return;
		}

		if ( Settings::get('meta_author', '') )
		{
			self::$metatags[ 'author' ] = ( Settings::get('meta_author') );
		}

		if ( Settings::get('meta_copyright', '') )
		{
			self::$metatags[ 'copyright' ] = ( Settings::get('meta_copyright') );
		}

		if ( Settings::get('meta_description', '') )
		{
			self::$metatags[ 'description' ] = ( Settings::get('meta_description') );
		}

		if ( Settings::get('meta_keywords', '') )
		{
			self::$metatags[ 'keywords' ] = ( Settings::get('meta_keywords') );
		}

		if ( Settings::get('meta_revisitafter', '') )
		{
			self::$metatags[ 'robot_revisit' ] = ( Settings::get('meta_revisitafter') );
		}

		// index/follow ?
		if ( Settings::get('meta_robots', '') )
		{
			self::$metatags[ 'robot_indexfollow' ] = ( Settings::get('meta_robots') );
		}

		self::$_defaultLoaded = true;
	}

	/**
	 *
	 * @return array
	 */
	public function getMetatags ()
	{

		return self::$metatags;
	}



	/**
	 *
	 * @param boolean $coreTable
	 * @return null|string
	 */
	public function getPrimaryKey ( $coreTable = true )
	{

        if (!$coreTable)
        {
            return $this->getTranslationTablePrimaryKey();
        }

        return $this->getTablePrimaryKey();

		//return ( $coreTable ? self::$_primaryKeyInMainTable : self::$_primaryKeyInTransTable );
	}

	/**
	 *
	 * @return integer/null
	 */
	public function getDocumentID ()
	{

		return self::$_documentID;
	}

	/**
	 * @return integer
	 */
	public function getLastModified ()
	{

		return self::$_lastModified ? self::$_lastModified : TIMESTAMP;
	}

	/**
	 *
	 * @return array/null
	 */
	public function getRegistryData ()
	{

		return self::$_registryData;
	}











	/**
	 *
	 * @param integer $id
	 * @return null
	 */
	public function getSource ( $id )
	{

		if ( !$id )
		{
			return null;
		}

		$sql = "SELECT a.*, t.* FROM article AS a
                LEFT JOIN cp_translations AS tr ON(tr.articleid = a.articleid)
                LEFT JOIN article_trans AS t ON(t.id=tr.articletransid)
                WHERE tr.sourcelang = 'de' AND a.articleid = 1";
	}





















	public function addRollback ()
	{
		self::$_rollback = true;
	}

	/**
	 * @return bool
	 */
	public function getRollback ()
	{
		return self::$_rollback;
	}

	/**
	 * Enable the Site Caching
	 *
	 * @param integer $cachetime default null and will use the global cache time
	 */
	public function enableSiteCaching ( $cachetime = null )
	{
		self::$cachable = true;

		// only used if $cachetime > 0
		// if 0 or null the use global cache time
		if ( !is_null($cachetime) && (int)$cachetime > 0 )
		{
			self::$cachtime = (int)$cachetime;
		}
	}

	/**
	 * will disable Site Caching
	 */
	public function disableSiteCaching ()
	{

		self::$cachable = false;
	}

	/**
	 *
	 * @return bool
	 */
	public function canCache ()
	{

		return self::$cachable;
	}

	/**
	 *
	 * @return bool
	 */
	public function cachetime ()
	{

		return self::$cachtime;
	}

	/**
	 *
	 * @return type
	 */
	public function getCacheGroups ()
	{

		return self::$cachgroups;
	}

	/**
	 *
	 * @return type
	 */
	public function getClickAnalyse ()
	{

		return self::$clickanalyse;
	}

	/**
	 *
	 * @return type
	 */
	public function getDocumentMetadata ()
	{

		return $this->_siteoutputdata[ 'website' ][ 'metadata' ];
	}

	/**
	 *
	 * @return string
	 */
	public function getCommentingKey ()
	{

		return self::$commentingPermKey;
	}

	/**
	 *
	 * @return boolean
	 */
	public function getCommentingValue ()
	{

		return self::$commentingPermValue;
	}

	/**
	 *
	 * @return string/null
	 */
	public function getMetaDescription ()
	{

		return self::$metaDescription;
	}

	/**
	 *
	 * @return string/null
	 */
	public function getMetaKeywords ()
	{

		return self::$metaKeywords;
	}

    /**
     * @param $contentColumn
     * @return array
     */
	public function analyseDocument ( $contentColumn )
	{

		$content = isset( self::$_documentData[ $contentColumn ] ) ? self::$_documentData[ $contentColumn ] : $contentColumn;


		if ( $content )
		{

			$content = preg_replace('&<script(.*)</script>&isU', '', $content);
			$content = str_replace('>', '> ', $content);

			$text_only = strip_tags($content);
			$html_only = $text_only;
			$text_only = preg_replace('/[^0-9a-zA-ZäüöÄÜÖß ]/', '', $text_only);
			$text_only = trim(Strings::removeDoubleSpace($text_only));

			$old_words = explode(' ', $text_only);
			$words     = array ();

			foreach ( $old_words as $word )
			{
				if ( strlen(trim($word)) > 1 )
				{
					$words[ ] = $word;
				}
			}

			$word_count   = count($words);
			$letter_count = strlen($text_only);

			$counter = array (
				'h'     => substr_count($content, '</h1>') + substr_count($content, '</h2>') + substr_count($content, '</h3>') + substr_count($content, '</h4>'),
				'p'     => substr_count($content, '</p>'),
				'img'   => substr_count($content, '<img '),
				'quote' => substr_count($content, '</blockquote>'),
				'ul'    => substr_count($content, '</ul>') + substr_count($content, '</ol>')
			);

			// filter words
			$extract_words = array (
				'die',
				'der',
				'und',
				'in',
				'zu',
				'den',
				'das',
				'nicht',
				'von',
				'sie',
				'ist',
				'des',
				'sich',
				'mit',
				'dem',
				'dass',
				'er',
				'es',
				'ein',
				'ich',
				'auf',
				'so',
				'eine',
				'auch',
				'als',
				'an',
				'nach',
				'wie',
				'im',
				'für',
				'man',
				'aber',
				'aus',
				'durch',
				'wenn',
				'nur',
				'war',
				'noch',
				'werden',
				'bei',
				'hat',
				'wir',
				'was',
				'wird',
				'sein',
				'einen',
				'welche',
				'sind',
				'oder',
				'zur',
				'um',
				'haben',
				'einer',
				'mir',
				'über',
				'uumlber',
				'ihm',
				'diese',
				'einem',
				'ihr',
				'uns',
				'da',
				'zum',
				'kann',
				'doch',
				'vor',
				'dieser',
				'mich',
				'ihn',
				'du',
				'hatte',
				'seine',
				'mehr',
				'am',
				'denn',
				'nun',
				'unter',
				'sehr',
				'selbst',
				'schon',
				'hier',
				'bis',
				'habe',
				'ihre',
				'dann',
				'ihnen',
				'seiner',
				'alle',
				'wieder',
				'meine',
				'gegen',
				'vom',
				'ganz',
				'wo',
				'muss',
				'ohne',
				'eines',
				'können',
				'sei',
				'amp',
				'für',
				'fuumlr'
			);

			//
			$founds = array ();
			$wdfs   = array ();
			for ( $x = 0; $x < $word_count; $x++ )
			{
				$dwort = $words[ $x ];

				if ( !empty( $dwort ) && !ctype_digit($dwort) && !in_array(strtolower($dwort), $extract_words) )
				{
					$founds[ $dwort ] += 1;
				}
			}

			foreach ( $founds as $word => $wcount )
			{
				$wdf           = round(log(( $wcount + 1 ), 2) / log($word_count, 2), 2);
				$wdfs[ $word ] = $wdf;
			}

			arsort($wdfs);


			$sf = array ( 'auml', 'uuml', 'ouml', 'Auml', 'Uuml', 'Ouml', 'szlig' );
			$ef = array ( 'ä', 'ü', 'ö', 'Ä', 'Ü', 'Ö', 'ß' );

			$countv = 0;
			$data   = array ();
			foreach ( $wdfs as $word => $wdf )
			{
				$v2 = (int)$founds[ $word ];

				$countv++;
				$percent = round($v2 / $word_count * 100, 1);
				$word    = str_replace($sf, $ef, $word);

				$data[ ] = array (

					'word'         => substr($word, 0, 18),
					'word_in_text' => $v2,
					'percent'      => $percent,
					'wdf'          => $wdf
				);

				if ( $countv == 15 )
				{
					break;
				}
			}


			$counter[ 'sentences' ] = Strings::countSentences($html_only);
			$counter[ 'chars' ]     = $letter_count;
			$counter[ 'words' ]     = $word_count;


			return array (
				'content'   => $content,
				'sentences' => Strings::countSentences($html_only),
				'chars'     => $letter_count,
				'words'     => $word_count,
				'counters'  => $counter,
				'wordlist'  => $data
			);
		}
	}

}

?>