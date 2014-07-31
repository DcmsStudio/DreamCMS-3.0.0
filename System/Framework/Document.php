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
 * @file        Document.php
 *
 */
class Document extends Document_Abstract
{

    /**
     * @var null
     */
    protected static $_Document = null;

    /**
     * @var null
     */
    protected $_documentMeta = null;

    /**
     * Prevent cloning of the object (Singleton)
     */
    final private function __clone()
    {

    }

    /**
     * @return Document|null
     */
    public static function getInstance()
    {
        if ( !(self::$_Document instanceof Document) )
        {
            self::$_Document = new Document();
            self::$_Document->load( 'Document_Metadata', '_documentMeta' );
            self::$_Document->loadDefaultMetatags();
        }
        return self::$_Document;
    }

    public function __destruct()
    {
        self::$_Document = null;
        $this->_documentMeta = null;
    }

    /**
     * Set
     * @param string $key
     * @param mixed $value default is null
     * @return Document
     */
    public function set( $key, $value = null )
    {
        self::$_documentData[ $key ] = $value;


        return $this;
    }

    /**
     * Get
     *
     * @param string $key
     * @return array|null
     */
    public function get( $key = null )
    {
        if ( $key === null )
        {

            return self::$_documentData;
        }

        return (isset( self::$_documentData[ $key ] ) ? self::$_documentData[ $key ] : null);
    }

    /**
     * Send document offline Page
     *
     * @param string $message
     * @param bool   $website
     */
    public function offline( $message = '', $website = false )
    {

        $this->load( 'Output' );
        $this->Output->setStatus( 404 );

        $this->load( 'Template' );
        $message = $message ? $message : trans( 'Die von Ihnen angeforderte Seite wird gerade überarbeitet.<br/>Schauen Sie doch etwas später noch einmal vorbei.' );
        if ( !$website )
        {
            $this->Template->process( 'offline', array(
                'offlinemessage' => $message ), true );
        }
        else
        {
            $this->Template->process( 'offline_website', array(
                'offlinemessage' => $message ), true );
        }
    }

    /**
     * 
     * @param bool|string $message
     */
    public function protect( $message = false )
    {
        $message = $message ? $message : trans( 'Die von Ihnen angeforderte Seite ist Passwort gesichert.<br/>Bitte geben Sie das Passwort ein.' );
        $this->load( 'Output' );
        $this->Output->setStatus( 403 );
        $this->load( 'Template' );
        $this->Template->process( 'protected_document', array(
            'message' => $message ), true );
    }

    /**
     *
     *
     * @param string $value
     */
    public function setLayout( $value )
    {
        $this->_sitedata[ 'layout' ] = $value;
    }

    /**
     *
     * @return string/null
     */
    public function getLayout()
    {
        return (isset( $this->_sitedata[ 'layout' ] ) ? $this->_sitedata[ 'layout' ] : null);
    }

    /**
     *
     * @return Document_Metadata
     */
    public function getMetaInstance()
    {
        return $this->_documentMeta;
    }

    /**
     * @param null|string $tablename
     * @param bool $setastable
     * @return $this|Model
     */
    public function validateModel($tablename = null, $setastable = false)
    {
        $tablename = $this->_getTblname($tablename);

        $this->_initModel();

        parent::validateModel($tablename, $setastable);

        if ( $setastable )
        {
            $this->useTable($tablename);
            self::$_tableName = $tablename;
        }

        return $this;
    }

    /**
     *
     * @param null|string $tablename
     * @return $this
     * @throws BaseException
     */
    public function loadConfig($tablename = null)
    {

        $used = $this->getUsedTable();

        if ((!is_string($tablename) || empty($tablename)) && is_string($used) && !empty($used))
        {
            self::$_tableName = $tablename = $used;
        }
        else
        {
            if (!is_string($tablename) || empty($tablename)) {
                throw new BaseException( 'Please set the table name before run Document::loadConfig()!' );
            }
        }

        $tablename = $this->_getTblname($tablename);
        $usetrans = $this->allowTrans($tablename);

        $mainpk = $this->getTablePrimaryKey($tablename);
        if ( empty( $mainpk ) )
        {
            throw new BaseException( 'The Primary Key for the Table "'.$tablename.'" is not set or empty!' );
        }

        if ($usetrans)
        {
            $transpk = $this->getTranslationTablePrimaryKey($tablename);
            if ( empty( $transpk ) )
            {
                throw new BaseException( 'The Primary Key for the Translation Table "'.$tablename.'_trans" is not set or empty!' );
            }
        }

        self::$_primaryKeyInTransTable = $transpk;
        self::$_primaryKeyInMainTable = $mainpk;

        $transfields = $this->getTableTranslationFields($tablename);
        if ( is_array( $transfields ) )
        {
            self::$_translateableFields = $transfields;
        }

        if ( !empty( $data[ 'useMetadata' ] ) )
        {
            self::$_documentHasMeta = true;
        }


        $o = $this->getTableOption('sourcemode', $tablename);
        if ($o !== null)
        {
            $this->_sourceMode = $o;
        }

        return $this;
    }













    /**
     * set the database table configuration
     *
     * @param string $table the table name
     * @param array  $data
     * @throws BaseException
     * @return Document
     */
    public function setTableConfiguration( $table, array $data )
    {

        // remove the prefix placeholder
	    self::$_tableName = str_replace( '%tp%', '', $table );


        if ( !isset( $data[ 'mainPK' ] ) || empty( $data[ 'mainPK' ] ) )
        {
            throw new BaseException( 'The Primary Key for the Main Table is not set or empty!' );
        }

        if ( !isset( $data[ 'sourcemode' ] ) || empty( $data[ 'sourcemode' ] ) )
        {
            // throw new BaseException('The sourcemode is not set or empty!');
        }

        if ( !empty( $data[ 'useTranslation' ] ) )
        {
            if ( !isset( $data[ 'transPK' ] ) || empty( $data[ 'transPK' ] ) )
            {
                throw new BaseException( 'The Primary Key for the Translation Table is not set or empty!' );
            }

	        self::$_useDocumentTranslation = true;
	        self::$_primaryKeyInTransTable = $data[ 'transPK' ];
        }

	    self::$_primaryKeyInMainTable = $data[ 'mainPK' ];

        $this->_sourceMode = $data[ 'sourcemode' ];

        if ( !empty( $data[ 'useMetadata' ] ) )
        {
	        self::$_documentHasMeta = true;
        }


        if ( isset( $data[ 'translateFields' ] ) && is_array( $data[ 'translateFields' ] ) )
        {
	        self::$_translateableFields = $data[ 'translateFields' ];
        }


        return $this;
    }

    /**
     *
     * @param mixed $id convert to integer
     * @return Document
     */
    public function setDocumentID( $id )
    {
        self::$_documentID = (int)$id ;
        return $this;
    }

    /**
     * will set the alias registry data
     *
     * @param array $data
     * @return Document
     */
    public function setRegistryData( array $data )
    {
        self::$_documentID = $data[ 'contentid' ];
        unset( $data[ 'contentid' ] );

        self::$_registryData = $data;

        return $this;
    }

    /**
     *
     * @param array $data
     * @return Document
     */
    public function setData( array $data )
    {
        self::$_documentData = $data;


        if ( isset( $data[ 'metadescription' ] ) && trim( $data[ 'metadescription' ] ) )
        {
            self::$metatags[ 'description' ] = trim( $data[ 'metadescription' ] );
        }

        if ( isset( $data[ 'metakeywords' ] ) && trim( $data[ 'metakeywords' ] ) )
        {
            self::$metatags[ 'keywords' ] = trim( $data[ 'metakeywords' ] );
        }

        if ( isset( $data[ 'indexfollow' ] ) && trim( $data[ 'indexfollow' ] ) )
        {
            self::$metatags[ 'robot_indexfollow' ] = trim( $data[ 'indexfollow' ] );
        }

        if ( isset( $data[ 'language' ] ) && trim( $data[ 'language' ] ) )
        {
            self::$metatags[ 'language' ] = trim( $data[ 'language' ] );
        }

        return $this;
    }

    /**
     *
     * @param array $data
     * @return Document
     */
    public function setChange( array $data )
    {
	    self::$_documentChangeData = $data;

        return $this;
    }

    /**
     *
     * @param integer $timestamp
     * @return Document
     */
    public function setLastModified( $timestamp = null )
    {
        if ( $timestamp !== null )
        {
            self::$_lastModified = $timestamp;
            self::$metatags[ 'lastmodify' ] = $timestamp;
        }

        return $this;
    }

    /**
     *
     * @param string $dataStr
     * @return Document
     */
    public function setMetaDescription( $dataStr = '' )
    {
        self::$metatags[ 'description' ] = trim( $dataStr );

        return $this;
    }

    /**
     *
     * @param string $keywords
     * @return Document
     */
    public function setMetaKeywords( $keywords )
    {
        self::$metatags[ 'keywords' ] = trim( $keywords );

        return $this;
    }

    /**
     *
     * @param string $typ
     * @param integer $id
     * @param boolean $usercancomment
     * @return Document
     */
    public function setComment( $typ, $id = 0, $usercancomment = null )
    {
        if ( !$id )
        {
            return;
        }

        if ( $usercancomment !== null )
        {
            User::setUserData( 'can_comment', $usercancomment );
        }
        else
        {
            User::setUserData( 'can_comment', true );
        }

        self::$_commentType = $typ;
        self::$_commentPostId = $id;

        return $this;
    }

    /**
     *
     * @param string $typ
     * @return Document
     */
    public function setCommentingType( $typ )
    {
        self::$_commentType = $typ;

        return $this;
    }

    /**
     *
     * @param string $type the rss header type (rss/atom)
     * @param string $title feed Title
     * @param string $controller
     * @return Document
     */
    public function addRssHeader( $type, $title, $controller = '' )
    {
        if ( !is_array( $this->_rssHeaders ) )
        {
            $this->_rssHeaders = array();
        }

        $this->_rssHeaders[] = array(
            'rsstype'    => strtolower( $type ),
            'title'      => $title,
            'controller' => $controller );

        return $this;
    }

    /**
     *
     * @param string $author
     * @return Document
     */
    public function setMetaAuthor( $author = null )
    {
        if ( is_string( $author ) && $author !== '' )
        {
            self::$metatags[ 'author' ] = trim( $author );
        }

        return $this;
    }

    /**
     *
     * @param string $permKey
     * @param boolean $forceValue
     * @return Document
     */
    public function setCommenting( $permKey, $forceValue = null )
    {
        if ( !$permKey )
        {
            return $this;
        }

        self::$commentingPermKey = $permKey;
        self::$commentingPermValue = User::hasPerm( $permKey );


        if ( $forceValue !== null && $forceValue && !User::hasPerm( $permKey ) )
        {
            self::$commentingPermValue = $forceValue;
        }


        if ( $forceValue !== null )
        {
            User::setUserData( 'can_comment', self::$commentingPermValue );
        }
        else
        {
            User::setUserData( 'can_comment', true );
        }


        User::setPerm( $permKey, self::$commentingPermValue );

        return $this;
    }

    /**
     * will set the Usergroups if can Site Caching
     *
     * @param (string or integer) $cachegroups default is null
     * @return Document
     */
    public function setSiteCachingGroups( $cachegroups = null )
    {
        self::$cachgroups = $cachegroups;

        return $this;
    }

    /**
     * will set the click analyser
     *
     * @param bool $clickanalyse
     * @internal param $ (bool or integer) $clickanalyse
     * @return Document
     */
    public function setClickAnalyse( $clickanalyse = false )
    {
        self::$clickanalyse = ($clickanalyse ? true : false);

        return $this;
    }

    /**
     * @return string
     */
    public function getPermanentLink()
    {
        
    }

}
