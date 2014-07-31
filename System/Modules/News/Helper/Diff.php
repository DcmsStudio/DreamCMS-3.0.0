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
 * @package      
 * @version      3.0.0 Beta
 * @category     
 * @copyright    2008-2014 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Diff.php
 */

class News_Helper_Diff extends Loader
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
/*
        $model = Model::getModelInstance('news');
        $model->setTable('news');
        $modelTables = $model->getConfig('tables');
        if ( !isset($modelTables[ 'news' ]) )
        {
            throw new BaseException('The News modul has no Model configuration! ' . print_r($modelTables, true));
        }

*/
        $model = Model::getModelInstance('news');
        $model->load('Document');

        $model->Document
            ->validateModel('news', true)
            ->loadConfig()
            ->getMetaInstance()
            ->setMetadataType(true);





        $this->load('Versioning');
        if ( $currentVersion > 0 )
        {
            $source = $this->Versioning->getVersion($documentID, 'news', $currentVersion);
        }

        $target = $this->Versioning->getVersion($documentID, 'news', $targetVersion);
        $ret = array ();

        $ret[ 'target' ] = $target;

        if ( isset($target[ 'transdata' ][ 'text' ]) )
        {
            $r               = unserialize($target[ 'transdata' ]);
            $ret[ 'target' ][ 'content' ] = $r[ 'text' ];

            if (isset($r[ 'title' ])) {
                $ret[ 'target' ][ 'title' ] = $r[ 'title' ];
            }

            if (isset($r[ 'teaser' ])) {
                $ret[ 'target' ][ 'teaser' ] = $r[ 'teaser' ];
            }
            if (isset($r[ 'created' ])) {
                $ret[ 'target' ][ 'created' ] = $r[ 'created' ];
            }
            if (isset($r[ 'modifed' ])) {
                $ret[ 'target' ][ 'modifed' ] = $r[ 'modifed' ];
            }
        }
        else
        {
            $ret[ 'target' ][ 'content' ] = '';

        }


        $rs = $model->findItemByID($documentID);

        $ret[ 'source' ] = $source;

        if ( !isset($source[ 'transdata' ]) )
        {
            $ret[ 'source' ][ 'content' ] = $rs[ 'text' ];

            if (isset($rs[ 'title' ])) {
                $ret[ 'source' ][ 'title' ] = $rs[ 'title' ];
            }
            if (isset($rs[ 'teaser' ])) {
                $ret[ 'source' ][ 'teaser' ] = $rs[ 'teaser' ];
            }

            if (isset($r[ 'created' ])) {
                $ret[ 'source' ][ 'created' ] = $r[ 'created' ];
            }
            if (isset($r[ 'modifed' ])) {
                $ret[ 'source' ][ 'modifed' ] = $r[ 'modifed' ];
            }
        }
        else
        {
            $r               = unserialize($source[ 'transdata' ]);
            $ret[ 'source' ][ 'content' ] = $r[ 'text' ];

            if (isset($r[ 'title' ])) {
                $ret[ 'source' ][ 'title' ] = $r[ 'title' ];
            }

            if (isset($r[ 'teaser' ])) {
                $ret[ 'source' ][ 'teaser' ] = $r[ 'teaser' ];
            }
            if (isset($r[ 'created' ])) {
                $ret[ 'source' ][ 'created' ] = $r[ 'created' ];
            }
            if (isset($r[ 'modifed' ])) {
                $ret[ 'source' ][ 'modifed' ] = $r[ 'modifed' ];
            }
        }

        $ret[ 'versions' ] = $this->Versioning->getVersions($documentID, 'news');


        Library::addNavi(sprintf(trans('Revision für News `%s`'), $rs[ 'title' ]), '');


        return $ret;
    }

}