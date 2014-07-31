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
 * @file        Tags.php
 *
 */
class Tags extends Loader
{

    /**
     * Current object instance (do not remove)
     *
     * @var object
     */
    protected static $objInstance = null;

    /**
     * @var null
     */
    protected $table = null;

    /**
     * @var null
     */
    protected $tableHash = null;

    /**
     * @var int
     */
    protected $tableHashID = 0;

    /**
     * @var int
     */
    protected $contentId = 0;

    /**
     * @var null
     */
    protected $tags = null;

    /**
     * Prevent cloning of the object (Singleton)
     */
    final private function __clone()
    {
        
    }

    /**
     * Return the current object instance (Singleton)
     *
     * @return object
     */
    public static function getInstance()
    {
        if ( self::$objInstance === null )
        {
            self::$objInstance = new Tags();
        }
        return self::$objInstance;
    }

    /**
     *
     * @param string $table
     * @return string
     */
    public function getHash( $table )
    {
        $this->tableHash = md5( $this->removeTablePrefix( $table ) );
        return $this->tableHash;
    }

    /**
     *
     * @param string $table
     */
    public function setContentTable( $table )
    {
        $this->table = $this->removeTablePrefix( $table );
        $this->tableHash = $this->getHash( $table );
        $this->getHashId();


        Session::save( 'TAGS_FOR_TABLE', $table );
    }

    /**
     *
     * @return integer
     */
    private function getHashId()
    {
        if ( empty( $this->table ) )
        {
            Error::raise( 'Content Table for Tags is not set!' );
        }

        if ( $this->tableHashID > 0 )
        {
            return $this->tableHashID;
        }

        $hash = $this->db->query( 'SELECT hashid FROM %tp%tags_hash WHERE tablehash = ?', $this->tableHash )->fetch();
        if ( !$hash[ 'hashid' ] )
        {
            $this->db->query( 'INSERT INTO %tp%tags_hash (tablehash,tablename) VALUES(?,?)', $this->tableHash, $this->table );
            $hash[ 'hashid' ] = $this->db->insert_id();
        }

        $this->tableHashID = $hash[ 'hashid' ];
    }

    /**
     * Please use Tags:: getHash() before use this function
     *
     * @param string $tag
     * @return integer
     */
    public function getTagIdByTag( $tag )
    {

        $r = $this->db->query( '
            SELECT t.id FROM %tp%tags AS t 
            LEFT JOIN %tp%tags_hash AS h ON(h.hashid = t.hashid)
            WHERE tablehash = ? AND tag = ? LIMIT 1', $this->tableHash, $tag )->fetch();
        return ($r[ 'id' ] ? $r[ 'id' ] : 0);
    }

    /**
     * get all tags
     *
     * @return array
     */
    public function getTags()
    {
        $this->getHashId();
        return $this->db->query( 'SELECT id, tag FROM %tp%tags WHERE hashid = ? AND pageid = ?', $this->tableHashID, PAGEID )->fetchAll();
    }

    /**
     * Search Tags by givig search string
     *
     * @param string $str
     * @param string $skip
     * @return array|null
     */
    public function getSearchTag( $str, $skip = '' )
    {
        if ( !trim( $str ) )
        {
            return null;
        }

        $this->getHashId();
        $skips = implode( ',', explode( ',', $skip ) );

        return $this->db->query( 'SELECT id, tag FROM %tp%tags
            WHERE hashid = ? AND pageid = ?' . (trim( $skips ) != '' ? ' AND id NOT IN(' . $skips . ')' : '') .
                        ' AND tag LIKE ? LIMIT 50', $this->tableHashID, PAGEID, '%' . $str . '%' )->fetchAll();
    }

    /**
     * returns all content tags
     *
     * @param string $tagids
     * @param bool   $tagsonly
     * @internal param $array /string $tagids
     * @return array
     */
    public function getContentTags( $tagids = '', $tagsonly = false )
    {
        if ( is_array( $tagids ) )
        {
            $tagids = implode( ',', Library::unempty( $tagids ) );
        }
        else
        {
            $tagids = explode( ',', $tagids );
            $tagids = implode( ',', Library::unempty( $tagids ) );
        }

        if ( !trim( $tagids ) )
        {
            return array();
        }

        $this->getHashId();


        if ( $tagsonly !== false )
        {
            $rs = $this->db->query( 'SELECT tag FROM %tp%tags
                                     WHERE hashid = ? AND pageid = ? AND id IN(0,' . $tagids . ')', $this->tableHashID, PAGEID )->fetchAll();

            $tags = array();
            foreach ( $rs as $r )
            {
                $tags[] = $r[ 'tag' ];
            }
            return $tags;
        }
        return $this->db->query( 'SELECT id, tag, hits FROM %tp%tags
                                 WHERE hashid = ? AND pageid = ? AND id IN(0,' . $tagids . ')', $this->tableHashID, PAGEID )->fetchAll();
    }

    /**
     * Save a new tag or replace the tag
     *
     * @param $tag
     * @internal param $string /array $tag
     * @return bool|int
     */
    public function saveTag( $tag )
    {
        $this->getHashId();

        if ( is_array( $tag ) )
        {
            foreach ( $tag as $str )
            {
                $this->db->query( 'REPLACE INTO %tp%tags (tag,hashid,pageid) VALUES(?,?,?)', $tag, $this->tableHashID, PAGEID );
            }
            return true;
        }

        $this->db->query( 'REPLACE INTO %tp%tags (tag,hashid,pageid) VALUES(?,?,?)', $tag, $this->tableHashID, PAGEID );
        return $this->db->insert_id();
    }

    /**
     * Delete a Tag
     *
     * @param $id
     * @internal param \or $integer array with ids $id
     * @return bool
     */
    public function deleteTag( $id )
    {
        $this->getHashId();

        if ( is_array( $id ) )
        {
            $ids = implode( ',', $id );

            $tag = $this->db->query( 'SELECT tag FROM %tp%tags WHERE id IN(' . $tagids . ')', $this->tableHashID, PAGEID )->fetchAll();
            $this->db->query( 'DELETE FROM %tp%tags WHERE id IN(' . $ids . ')' );

            $tags = implode( ', ', array_values( $tag ) );
            Library::log( sprintf( 'User has delete multiple Tags: %s', $tags ) );
            return true;
        }

        $tag = $this->db->query( 'SELECT tag FROM %tp%tags WHERE id = ?', $id )->fetchAll();
        $this->db->query( 'DELETE FROM %tp%tags WHERE id = ?', $id );

        Library::log( sprintf( 'User has delete the Tag `%s`', $tag[ 'tag' ] ) );
        return true;
    }

}

?>