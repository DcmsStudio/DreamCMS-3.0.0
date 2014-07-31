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
 * @file        Versioning.php
 *
 */
class Versioning
{

    protected $db;

    protected $prefix;

    /**
     *
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->prefix = $this->db->tp( true );
    }

    /**
     * @param string $table
     * @return mixed
     */
    private function getTable( $table = '' )
    {
        if (substr($table, 0 , 4) !== '%tp%') {
            return $this->db->tp( true ) . trim( $table );
        }
        $prefix = $this->db->tp( true );
        return preg_replace( '/^(' . preg_quote( $prefix, '/' ) . '|%tp%)/', '', trim( $table ) );
    }

    /**
     * @param string $table
     * @return bool
     */
    private function canVersioning( $table )
    {
        $noprefixedTable = $this->getTable( $table );

		// Versionig is global enabled?
	    if ( !isset( $GLOBALS[ 'versioning' ][ 'enabled' ] ) || $GLOBALS[ 'versioning' ][ 'enabled' ] !== true || empty( $noprefixedTable ) )
	    {
		    return false;
	    }

	    // if is global enabled then test modul versioning enabled
	    if (isset( $GLOBALS[ 'versioning' ][ $noprefixedTable ][ 'enabled' ] ) && $GLOBALS[ 'versioning' ][ $noprefixedTable ][ 'enabled' ] != true) {
		    return false;
	    }
	    elseif (isset( $GLOBALS[ 'versioning' ][ $noprefixedTable ][ 'enabled' ] ) && $GLOBALS[ 'versioning' ][ $noprefixedTable ][ 'enabled' ] ) {
		    return true;
	    }
		else {

	        if ( !isset( $GLOBALS[ 'versioning' ][ 'enabled' ] ) || $GLOBALS[ 'versioning' ][ 'enabled' ] !== true ||
	                empty( $noprefixedTable ) ||
	                (
	                isset( $GLOBALS[ 'versioning' ][ $noprefixedTable ][ 'enabled' ] ) &&
	                $GLOBALS[ 'versioning' ][ $noprefixedTable ][ 'enabled' ] !== true)
	        )
	        {
	            return false;
	        }
		}
        return true;
    }

    /**
     * @param int $contentid
     * @param string $table
     * @return array
     */
    public function getVersions( $contentid, $table )
    {
        if ( !$this->canVersioning( $table ) )
        {
            return null;
        }
        $noprefixedTable = $this->getTable( $table );

        return $this->db->query( 'SELECT v.`timestamp`, v.`current`, v.`version`, u.username
                                 FROM %tp%versions AS v
                                 LEFT JOIN %tp%users AS u ON(u.userid=v.userid)
                                 WHERE v.contentid = ? AND v.`table` = ? AND v.pageid = ? AND v.`lang` = ?
                                 ORDER BY v.`version` ASC', $contentid, $noprefixedTable, PAGEID, CONTENT_TRANS )->fetchAll();
    }

    /**
     * @param int $contentid
     * @param string $table
     * @param int $version
     * @return array
     */
    public function getVersion( $contentid, $table, $version )
    {
        if ( !$this->canVersioning( $table ) )
        {
            return null;
        }
        $noprefixedTable = $this->getTable( $table );

        return $this->db->query( 'SELECT v.*, u.username
                                 FROM %tp%versions AS v
                                 LEFT JOIN %tp%users AS u ON(u.userid=v.userid)
                                 WHERE v.contentid = ? AND v.`table` = ? AND v.pageid = ? AND v.`lang` = ? AND v.`version` = ?', $contentid, $noprefixedTable, PAGEID, CONTENT_TRANS, $version )->fetch();
    }

    /**
     * @param int $contentid
     * @param string $table
     * @return string
     */
    public function buildAjaxVersions( $contentid, $table )
    {
        $versions = $this->getVersions( $contentid, $table );

        $ret = '';
        if ( is_array( $versions ) )
        {
            foreach ( $versions as $r )
            {
                $sel = ($r[ 'current' ] ? ' selected="selected"' : '');
                $ret .= '<option value="' .  htmlspecialchars($r[ 'version' ]) . '"' . $sel . '> Version ' .  htmlspecialchars($r[ 'version' ]) . ' (' . date( 'd.m.Y, H:i:s', $r[ 'timestamp' ] ) . ') - ' . htmlspecialchars($r[ 'username' ]) . '</option>';
            }
        }

        return $ret;
    }

    /**
     * Create an initial version of a record
     *
     * @param int $contentid
     * @param string $table
     * @param array $recordData
     * @param null $contentRecordData
     * @param string $tableprimarykey
     * @return null
     */
    public function createInitialVersion( $contentid = 0, $table = '', $recordData = array(), $contentRecordData = null, $tableprimarykey = '' )
    {
        // @todo change params to array
/*
        $contentid = intval($data['contentid']);
        $table = $data['table'];
        $records = $data['records'];
        $transrecords = isset($data['transrecords']) ? $data['transrecords'] : null;
        $customrecords = isset($data['customrecords']) ? $data['customrecords'] : null;
        $tableprimarykey = isset($data['pk']) ? $data['pk'] : '';
*/

        if ( !$this->canVersioning( $table ) || !$contentid )
        {
            return null;
        }


        $noprefixedTable = $this->getTable( $table );

        $objVersion = $this->db->query( "
            SELECT COUNT(id) AS total FROM %tp%versions
            WHERE `table` = ? AND contentid = ? AND pageid = ? AND `lang` = ?", $noprefixedTable, $contentid, PAGEID, CONTENT_TRANS )->fetch();

        if ( !$objVersion[ 'total' ] )
        {
            $this->createVersion( $contentid, $table, $recordData, $contentRecordData, $tableprimarykey );
        }
    }

    /**
     * @param int $contentid
     * @param string $table
     * @param array $recordData
     * @param null $contentRecordData
     * @param string $tableprimarykey
     */
    public function createVersion( $contentid = 0, $table = '', $recordData = array(), $contentRecordData = null, $tableprimarykey = '' )
    {
        // @todo change params to array
        /*
        $contentid = intval($data['contentid']);
        $table = $data['table'];
        $records = $data['records'];
        $transrecords = isset($data['transrecords']) ? $data['transrecords'] : null;
        $customrecords = isset($data['customrecords']) ? $data['customrecords'] : null;
        $tableprimarykey = isset($data['pk']) ? $data['pk'] : '';
        */

        if ( !$this->canVersioning( $table ) || !$contentid )
        {
            return;
        }

        $noprefixedTable = $this->getTable( $table );

        // Delete old versions from the database
        $period = (isset( $GLOBALS[ 'versioning' ][ $noprefixedTable ][ 'period' ] ) ? $GLOBALS[ 'versioning' ][ $noprefixedTable ][ 'period' ] : $GLOBALS[ 'versioning' ][ 'period' ]);

		// delete first all older versions atomaticly
        if ( $period > 0 )
        {
            $this->db->query( 'DELETE FROM %tp%versions
                              WHERE `timestamp` < ? AND
                              pageid = ? AND
                              contentid = ? AND
                              `table` = ? AND
                              `lang` = ?', (time() - $period ), PAGEID, $contentid, $noprefixedTable, CONTENT_TRANS );
        }


        if ( is_array( $contentRecordData ) )
        {
            $serializedTransRecordData = serialize( $contentRecordData );
        }
        else
        {
            $serializedTransRecordData = '';
        }


        // Identical data?
        $activeVersion = $this->db->query( '
            SELECT `data`, transdata FROM %tp%versions WHERE current = 1 AND
            contentid = ? AND
            `table`= ? AND
            pageid = ? AND
            `lang` = ?', $contentid, $noprefixedTable, PAGEID, CONTENT_TRANS )->fetch();

        $serialized = serialize( $recordData );

        if ( $activeVersion[ 'id' ] )
        {
            if ( md5( $serializedTransRecordData ) == md5( $activeVersion[ 'transdata' ] ) && md5( $serialized ) == md5( $activeVersion[ 'data' ] ) )
            {
                // Identical data also back
                return;
            }
        }

        $intVersion = 1;
        $currentVersion = $this->db->query( 'SELECT MAX(`version`) AS `version` FROM %tp%versions
                                            WHERE
                                            contentid = ? AND
                                            `table`= ? AND
                                            pageid = ? AND
                                            `lang` = ?', $contentid, $noprefixedTable, PAGEID, CONTENT_TRANS )->fetch();
        if ( $currentVersion[ 'version' ] )
        {
            $intVersion = $currentVersion[ 'version' ] + 1;
        }

        // Deactivate current Version
        $this->db->query( 'UPDATE %tp%versions SET current = 0
                          WHERE contentid = ? AND `table`= ? AND pageid = ? AND lang = ?', $contentid, $noprefixedTable, PAGEID, CONTENT_TRANS );

        // Store new Version
        $this->db->query( 'INSERT INTO %tp%versions
                          (pageid, `contentid`,`version`,`timestamp`,`userid`,`table`,`tableprimarykey`,`current`,`data`, transdata, `lang`)
                          VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', array(
            PAGEID,
            $contentid,
            $intVersion,
            time(),
            User::getUserId(),
            $noprefixedTable,
            ($tableprimarykey ? $tableprimarykey : 'id'),
            1,
            $serialized,
            $serializedTransRecordData,
            CONTENT_TRANS
        ) );
    }

    /**
     * @param int $toVersion
     * @param int $contentid
     * @param string $table
     */
    public function undoVersion( $toVersion = 0, $contentid = 0, $table = '' )
    {

        if ( !$toVersion || !$contentid )
        {
            return;
        }

        $noprefixedTable = $this->getTable( $table );

        $toVersionData = $this->db->query( 'SELECT * FROM %tp%versions
                                           WHERE
                                           `version` = ? AND
                                           contentid = ? AND
                                           `table`= ? AND
                                           `lang` = ?', $toVersion, $contentid, $noprefixedTable, CONTENT_TRANS )->fetch();

        if ( !$toVersionData[ 'contentid' ] )
        {
            return;
        }

        $data = unserialize( $toVersionData[ 'data' ] );
        if ( is_array( $data ) )
        {
            $string = $this->db->compile_db_update_string( $data );
            $this->db->query( 'UPDATE %tp%' . $noprefixedTable . ' SET ' . $string . ' WHERE ' . $toVersionData[ 'tableprimarykey' ] . '=' . $contentid );


            $this->db->query(
                    'UPDATE %tp%versions SET current = 0
                 WHERE contentid = ? AND `table` = ? AND pageid = ? AND lang = ?', $contentid, $noprefixedTable, PAGEID, CONTENT_TRANS
            );

            $this->db->query( 'UPDATE %tp%versions SET current = 1
                              WHERE `version` = ? AND contentid = ? AND `table`= ? AND pageid = ? AND `lang` = ?', $toVersion, $contentid, $noprefixedTable, PAGEID, CONTENT_TRANS );
        }

        /**
         * @example Translation Content Tables must have ending with _trans
         */
        if ( $toVersionData[ 'transdata' ] != '' )
        {
            $contentdata = unserialize( $toVersionData[ 'transdata' ] );

            if ( is_array( $contentdata ) )
            {
                $string = $this->db->compile_db_update_string( $contentdata );
                $this->db->query( 'UPDATE %tp%' . $noprefixedTable . '_trans SET ' . $string . '
                    WHERE ' . $toVersionData[ 'tableprimarykey' ] . ' = ? AND lang = ?', $contentid, CONTENT_TRANS );
            }
        }
    }

    /**
     * @param int $versionid
     * @param int $contentid
     * @param string $table
     */
    public function deleteVersion($versionid, $contentid, $table = '')
    {
        if ( !$table || !$contentid || !$versionid )
        {
            return;
        }

        $noprefixedTable = $this->getTable( $table );
        $this->db->query( 'DELETE FROM %tp%versions
                              WHERE id = ?
                              pageid = ? AND
                              contentid = ? AND
                              `table` = ?', $versionid, PAGEID, $contentid, $noprefixedTable );
    }

    /**
     * @param int $contentid
     * @param string $table
     */
    public function deleteAllVersions($contentid, $table = '')
    {
        if ( !$table || !$contentid )
        {
            return;
        }

        $noprefixedTable = $this->getTable( $table );

        $this->db->query( 'DELETE FROM %tp%versions
                              WHERE
                              pageid = ? AND
                              contentid = ? AND
                              `table` = ? AND
                              `lang` = ?', PAGEID, $contentid, $noprefixedTable, CONTENT_TRANS );
    }
}

?>