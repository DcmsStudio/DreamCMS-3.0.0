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
 * @package      News
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Edit_news.php
 */
class News_Action_Edit_News extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}


		$id = (int)$this->input('id');


        $this->Document
            ->validateModel('news', true)
            ->loadConfig()
            ->getMetaInstance()
            ->setMetadataType(true);


        /*

		$newsModel = Model::getModelInstance();
		$newsModel->setTable('news');
		$modelTables = $newsModel->getConfig('tables');
		if ( !isset($modelTables[ 'news' ]) )
		{
			throw new BaseException('The News modul has no Model configuration! ' . print_r($modelTables, true));
		}


		$this->Document->setTableConfiguration('news', $modelTables[ 'news' ]);
		$this->Document->getMetaInstance()->setMetadataType(true);

        */


		$this->Document->addRollback();


		// Send ajax Rollback?
		if ( $this->input('transrollback') )
		{
            $this->model->rollbackTranslation($id);
			$this->model->unlock($id, 'news', 'show');
			Library::sendJson(true);
		}


		$this->load('Versioning');

		// Change version
		if ( $this->input('setVersion') )
		{
			demoadm();
			$this->Versioning->undoVersion((int)$this->input('setVersion'), $id, 'news');
			Library::sendJson(true);
		}


		/**
		 * Init Tags
		 */
		$this->load('Tags');
		$this->Tags->setContentTable('news_trans');


		$r = array ();

		if ( $id )
		{

			if ( ($lockedBy = $this->model->checkLocked($id, 'news', 'show')) !== false )
			{
				Library::sendJson(false, sprintf(trans('Dieses Dokument wird bereits von %s bearbeitet.'), $lockedBy) );
			}



			$hasTranslation = $this->model->hasTranslation($id);

			// Create translation if not exists (temporary)
			// If the User click close tab or cancel then send a rollback event via ajax
			if ( !$hasTranslation )
			{
				// Copy the original article to translated
                $this->model->copyOriginalTranslation($id);
			}

			$transq = $this->buildTransWhere('news', 'n.id', 'nt');
			$r      = $this->db->query('SELECT n.*, nt.*,
                                u.username AS creator,
                                m.username AS modifer
                                FROM %tp%news AS n
                                LEFT JOIN %tp%news_trans AS nt ON (nt.id=n.id)
                                LEFT JOIN %tp%users AS u ON (u.userid=n.created_by)
                                LEFT JOIN %tp%users AS m ON (m.userid=n.modifed_by)
                                WHERE n.id= ? AND ' . $transq, $id)->fetch();

			if ( !$r[ 'id' ] )
			{
				Error::raise("News not exist! Code:" . __LINE__);
			}


			if ( HTTP::input('tomenu') )
			{
				$r[ 'form_keyname' ] = 'id';
				$this->getGriditemAppendToMenu('newsitem', $r);
			}


			/**
			 * Versioning
			 */
			$record      = $this->db->query('SELECT * FROM %tp%news WHERE id = ?', $id)->fetch();
			$recordTrans = $this->db->query('SELECT * FROM %tp%news_trans WHERE id = ? AND lang = ?', $id, CONTENT_TRANS)->fetch();


			// create init version
			$this->Versioning->createInitialVersion($id, 'news', $record, $recordTrans);
			Library::$versionRecords = $this->Versioning->getVersions($id, 'news');


			/**
			 * Init Metadata
			 */
			$this->Document->getMetaInstance()->initMetadata($r);


			// $this->load( 'ContentLock' );

			$this->model->lock(array (
			                         'pk'         => 'id',
			                         'table'      => '%tp%news',
			                         'contentid'  => $id,
			                         'title'      => $r[ 'title' ],
			                         'controller' => 'news',
			                         'action'     => 'show'
			                   ));
			// $this->ContentLock->lock( $id, 'news', 'show', $r[ 'title' ] );


			$r[ 'contenttags' ] = $this->Tags->getContentTags($r[ 'tags' ]);
			$r[ 'modified' ]    = (!empty($r[ 'modified' ]) ? $r[ 'modified' ] : 0);
			$r[ 'created' ]     = (!empty($r[ 'created' ]) ? $r[ 'created' ] : 0);
		}


		$categories = $this->model->getCats(true);

		// create tree for categories
		$tree = new Tree();
		$tree->setupData($categories, 'id', 'parentid');
		$c                  = $tree->buildRecurseArray();
		$r[ 'cat_options' ] = Arr::convertKeys($c, array (
		                                                 'id'       => 'value',
		                                                 'treename' => 'label'
		                                           ));


		if ( $id )
		{
			/**
			 * add recent item
			 */
			$this->addLastEdit($id, $r[ 'title' ], trans('Nachrichten'));

			// get inline gallerie images
			$r[ 'galleryimages' ] = $this->model->getContentImages($id, $r[ 'inlinegallery' ]);


			$r[ 'cat_options' ][ 'selected' ] = $r[ 'cat_id' ];
		}

		if ( !$id && $this->input('catid') > 0 )
		{
			$r[ 'cat_options' ][ 'selected' ] = (int)$this->input('catid');
		}

		unset($tree, $categories, $c);

		$groups = $this->getUserGroups();

		$r[ 'access_options' ] = Arr::convertKeys($groups, array (
		                                                         'groupid' => 'value',
		                                                         'title'   => 'label'
		                                                   ));

		array_unshift($r[ 'access_options' ], array (
		                                            'value' => 0,
		                                            'label' => trans('Alle Benutzergruppen')
		                                      ));

		unset($groups);

		$r[ 'access_options' ]               = Library::unempty($r[ 'access_options' ]);
		$r[ 'access_options' ][ 'selected' ] = ($r[ 'usergroups' ] ? explode(',', $r[ 'usergroups' ]) : array (
			0
		));


		$r[ 'can_comment' ]     = Settings::get('news.usecomments');
		$r[ 'addFileSelector' ] = true;


		$this->Document->setData($r);


		Library::addNavi(trans('News Übersicht'));
		Library::addNavi(($r[ 'id' ] ? sprintf(trans('News `%s` bearbeiten'), $r[ 'title' ]) :
			trans('News erstellen')));

		$this->setDraftButton(true);


		$this->Template->process('news/news_edit', $r, true);
	}

}

?>