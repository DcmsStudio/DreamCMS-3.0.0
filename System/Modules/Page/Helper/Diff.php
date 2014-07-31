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
 * @package      Page
 * @version      3.0.0 Beta
 * @category     Helper
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Diff.php
 */
class Page_Helper_Diff extends Loader
{

	/**
	 * @param int $currentVersion
	 * @param int $targetVersion
	 * @param int $documentID
	 * @return array
	 * @throws BaseException
	 */
	public function getDiff ( $currentVersion = 1, $targetVersion = 1, $documentID = 0 )
	{

        $model = Model::getModelInstance('page');
        $model->load('Document');
        $model->Document
            ->validateModel('pages', true)
            ->loadConfig()
            ->getMetaInstance()
            ->setMetadataType(true);


		$this->load('Versioning');
		if ( $currentVersion > 0 )
		{
			$source = $this->Versioning->getVersion($documentID, 'pages', $currentVersion);
		}

		$target = $this->Versioning->getVersion($documentID, 'pages', $targetVersion);

		$ret = array ();
        $ret[ 'target' ] = $target;

		if ( isset($target[ 'transdata' ]) )
		{
            $d               = unserialize($target[ 'data' ]);
			$r               = unserialize($target[ 'transdata' ]);
			$ret[ 'target' ][ 'content' ] = $r[ 'content' ];

            if (isset($r[ 'title' ])) {
                $ret[ 'target' ][ 'title' ] = $r[ 'title' ];
            }

            if (isset($r[ 'teaser' ])) {
                $ret[ 'target' ][ 'teaser' ] = $r[ 'teaser' ];
            }
            if (isset($d[ 'created' ])) {
                $ret[ 'target' ][ 'created' ] = $d[ 'created' ];
            }
            if (isset($d[ 'modifed' ])) {
                $ret[ 'target' ][ 'modifed' ] = $d[ 'modifed' ];
            }
		}
		else
		{
            $ret[ 'target' ][ 'content' ] = '';

		}


		$rs = $model->getItemByID($documentID);

        $ret[ 'source' ] = $source;

		if ( !isset($source[ 'transdata' ]) )
		{
            $ret[ 'source' ][ 'content' ] = $rs[ 'content' ];


            if (isset($rs[ 'title' ])) {
                $ret[ 'source' ][ 'title' ] = $rs[ 'title' ];
            }
            if (isset($rs[ 'teaser' ])) {
                $ret[ 'source' ][ 'teaser' ] = $rs[ 'teaser' ];
            }

            if (isset($rs[ 'created' ])) {
                $ret[ 'source' ][ 'created' ] = $rs[ 'created' ];
            }
            if (isset($rs[ 'modifed' ])) {
                $ret[ 'source' ][ 'modifed' ] = $rs[ 'modifed' ];
            }
		}
		else
		{
			$r               = unserialize($source[ 'transdata' ]);
            $d               = unserialize($target[ 'data' ]);
            $ret[ 'source' ][ 'content' ] = $r[ 'content' ];

            if (isset($r[ 'title' ])) {
                $ret[ 'source' ][ 'title' ] = $r[ 'title' ];
            }

            if (isset($r[ 'teaser' ])) {
                $ret[ 'source' ][ 'teaser' ] = $r[ 'teaser' ];
            }
            if (isset($d[ 'created' ])) {
                $ret[ 'source' ][ 'created' ] = $d[ 'created' ];
            }
            if (isset($d[ 'modifed' ])) {
                $ret[ 'source' ][ 'modifed' ] = $d[ 'modifed' ];
            }
		}


        /**
         * get Custom fields

        if ( $currentVersion > 0 )
        {
            $source = $this->Versioning->getVersion($documentID, 'pages_fields', $currentVersion);
        }

        $target = $this->Versioning->getVersion($documentID, 'pages_fields', $targetVersion);
        if (isset($target['transdata'])) {
            $ret[ 'target' ]['customfields'] = unserialize($target[ 'transdata' ]);
        }

        if (isset($source['transdata'])) {
            $ret[ 'source' ]['customfields'] = unserialize($source[ 'transdata' ]);
        }
        */
        $ret[ 'versions' ] = $this->Versioning->getVersions($documentID, 'pages');
		Library::addNavi(sprintf(trans('Revision der Statischen Seite `%s`'), $rs[ 'title' ]), '');


		return $ret;
	}

}
