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
 * @file        ContentLock.php
 *
 */
class ContentLock extends Loader
{

    /**
     * @var null
     */
    protected static $_instance = null;

    /**
     * Prevent cloning of the object (Singleton)
     */
    final private function __clone()
    {
        
    }

    /**
     * Return the current object instance (Singleton)
     * @return ContentLock
     */
    public static function getInstance()
    {
        if ( self::$_instance === null )
        {
            self::$_instance = new ContentLock();
            self::$_instance->load( 'Env' );
        }

        return self::$_instance;
    }
	/**
	 * @param $contentid
	 * @param $modul
	 * @param string $action
	 * @return bool|string
	 */
	public function isLockedByUser( $contentid, $modul, $action = '' )
	{
		$rs = $this->db->query( 'SELECT l.userid, u.username FROM %tp%contentlock AS l
							     LEFT JOIN %tp%users AS u ON(u.userid = l.userid )
								 WHERE l.contentid = ? AND l.modul = ? AND l.action = ? LIMIT 1', $contentid, $modul, $action )->fetch();

		if ($rs[ 'userid' ])
		{
			if ( $rs[ 'userid' ] != User::getUserId() )
			{
				$GLOBALS['content_lockerror'] = true;
				return $rs[ 'username' ];
			}
		}
		return false;
	}


    /**
     * @param $contentid
     * @param $modul
     * @param string $action
     * @return bool
     */
    public function isLocked( $contentid, $modul, $action = '' )
    {
        $rs = $this->db->query( 'SELECT id FROM %tp%contentlock WHERE contentid = ? AND modul = ? AND action = ? LIMIT 1', $contentid, $modul, $action )->fetch();

        return $rs[ 'id' ] ? true : false;
    }

    /**
     * @param $contentid
     * @param $modul
     * @param string $action
     * @return bool
     */
    public function isLock( $contentid, $modul, $action = '' )
    {
        // Usergroup can view the locked documents?
        if ( User::hasPerm( 'generic/canviewofflinedocuments', false ) )
        {
            return false;
        }

        $rs = $this->db->query( 'SELECT id FROM %tp%contentlock WHERE contentid = ? AND modul = ? AND action = ? LIMIT 1', $contentid, $modul, $action )->fetch();

        return $rs[ 'id' ] ? true : false;
    }

    /**
     * Will lock a document
     * @param array $data array('location', 'title', 'table', 'pk', 'contentid', 'controller' ,'action')
     */
    public function lock( $data )
    {
        if ( !Settings::get( 'autolock', false ) )
        {
            return;
        }


	    $GLOBALS['contentlock'] = true;

        if ( !isset( $data[ 'location' ] ) )
        {
            $editlocation = $this->Env->requestUri();
        }
        else
        {
            $editlocation = $data[ 'location' ];
        }


        $editlocation = preg_replace( '#^.*\.php\?([^\?]*)#sU', '$1', $editlocation );
        $editlocation = preg_replace( '#&ajax=[^&]*#s', '', $editlocation );
        $editlocation = preg_replace( '#&_=[^&]*#s', '', $editlocation );




        if ( substr( $data[ 'table' ], 0, 4 ) !== '%tp%' )
        {
            $data[ 'table' ] = '%tp%' . $data[ 'table' ];
        }


        $this->unlock( $data[ 'contentid' ], $data[ 'controller' ], $data[ 'action' ], false );

        $this->db->query( 'REPLACE INTO %tp%contentlock (contenttable, pk, contentid, modul, action, userid, locktime, location, title) VALUES(?,?,?,?,?,?,?,?,?)', $data[ 'table' ], $data[ 'pk' ], $data[ 'contentid' ], $data[ 'controller' ], $data[ 'action' ], User::getUserId(), time(), ($editlocation ? $editlocation : '' ), ($data[ 'title' ] ? $data[ 'title' ] : '' ) );
    }

    /**
     * Will unlock a document
     *
     * @param int $contentid
     * @param string $modul
     * @param string $action
     * @param bool $force default is true
     */
    public function unlock( $contentid, $modul, $action = '', $force = true )
    {
        $rs = $this->db->query( 'SELECT * FROM %tp%contentlock WHERE contentid = ? AND modul = ? AND action = ? LIMIT 1', $contentid, $modul, $action )->fetch();
        $this->db->query( 'DELETE FROM %tp%contentlock WHERE contentid = ? AND modul = ? AND action = ?', $contentid, $modul, $action );

        if ( $force && $rs[ 'contenttable' ] )
        {
            if ( substr( $rs[ 'contenttable' ], 0, 4 ) !== '%tp%' )
            {
                $rs[ 'contenttable' ] = '%tp%' . $rs[ 'contenttable' ];
            }

            $sql = 'UPDATE ' . $rs[ 'contenttable' ] . ' SET locked = 0 WHERE ' . $rs[ 'pk' ] . '=' . $rs[ 'contentid' ];
            $this->db->query( $sql );
        }
    }

}
