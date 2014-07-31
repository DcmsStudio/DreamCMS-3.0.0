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
 * @file         Edit_cats.php
 */
class News_Action_Edit_cats extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}


		if ( $this->_post('updateOrder') )
		{
			$data            = $this->_post();
			$data[ 'items' ] = $data[ 'items' ][ 0 ];
			$this->model->updateCatOrdering($data);

			Library::sendJson(true);
		}

		$cat_id = (int)$this->input('id');




        $this->Document
            ->validateModel('news_categories', true)
            ->loadConfig()
            ->getMetaInstance()
            ->setMetadataType(true);
/*


		$newsModel = Model::getModelInstance();
		$newsModel->setTable('news_categories');
		$modelTables = $newsModel->getConfig('tables');
		if ( !isset($modelTables[ 'news_categories' ]) )
		{
			throw new BaseException('The news categories modul has no Model configuration! ' . print_r($modelTables, true));
		}


		$this->Document->setTableConfiguration('news_categories', $modelTables[ 'news_categories' ]);
		$this->Document->getMetaInstance()->setMetadataType(true);

        */

		$this->Document->addRollback();


		// Rollback the new translation
		if ( HTTP::input('transrollback') )
		{
            $this->model->rollbackTranslation($cat_id, true);

			$this->model->unlock($cat_id, 'news', 'index');
			Library::sendJson(true);
		}

		$this->load('Versioning');

		// Change version
		if ( $this->_post('setVersion') )
		{
			$this->Versioning->undoVersion((int)$this->_post('setVersion'), $cat_id, 'news_categories');
		}

		$this->load('ContentLock');
		$this->load('ContentTranslation');


		if ( $this->input('send') )
		{
			demoadm();


			$alias  = $this->input('alias');
			$suffix = $this->input('suffix');

			$aliasRegistry = new AliasRegistry();
			$aliasExists   = $aliasRegistry->aliasExists(array (
			                                                   'alias'         => $alias,
			                                                   'suffix'        => $suffix,
			                                                   'documenttitle' => $this->input('title')
			                                             ));

			if ( $aliasExists && ($cat_id && $aliasRegistry->getErrorAliasID() != $cat_id) )
			{
				Library::log(sprintf('Alias Builder has found many errors! The Alias `%s` already exists!', $aliasRegistry->getAlias()), 'warn');
				Library::sendJson(false, sprintf(trans('Der Alias "%s" existiert bereits!'), $aliasRegistry->getAlias()));
			}


			// Einfügen
			$t = $this->input('title');
			if ( !trim(strip_tags($t)) )
			{
				if ( !IS_AJAX )
				{
					Error::raise(trans('Sie haben keinen Kategorie Titel angegeben!'));
				}
				else
				{
					Library::sendJson(false, trans('Sie haben keinen Kategorie Titel angegeben!'));
				}
			}

			// Neu erstellen
			if ( !$cat_id )
			{
				$sql = "SELECT c.id FROM %tp%news_categories AS c
			    LEFT JOIN %tp%news_categories_trans AS ct ON(ct.id=c.id)
			    WHERE ct.title=? AND ct.lang = ?";
				$r   = $this->db->query($sql, $t, CONTENT_TRANS)->fetch();

				if ( $r[ 'id' ] )
				{
					Library::sendJson(false, trans('Diese Kategorie existiert bereits!'));
				}

				//	$this->load( 'PageCache' );
				//	$this->PageCache->cleanCache( 0, 'news', 'index' );


				$post             = $this->_post();
				$post[ 'alias' ]  = $aliasRegistry->getAlias();
				$post[ 'suffix' ] = $aliasRegistry->getSuffix();


                $this->Event->trigger('onBeforeSave.newscat', 0, $post);

				$newid            = $this->model->saveCatTranslation(0, $post);


				$this->saveContentDraft($newid, trim((string)$this->input('title')), trans('Nachrichten Kategorie'));

				/**
				 * Build initial Version
				 */
				$record      = $this->db->query('SELECT * FROM %tp%news_categories WHERE id=?', $newid)->fetch();
				$contentData = $this->ContentTranslation->getTranslation($newid, 'news_categories', 'id');


				$this->Versioning->createInitialVersion($newid, 'news_categories', $record, $contentData);

                $this->Event->trigger('onAfterSave.newscat', $newid, $post);

				Library::log("Add News Categorie " . $this->_post('title') . ".");

				if ( !IS_AJAX )
				{
					header("Location: admin.php?adm=news&action=list_cats");
				}
				else
				{



                    $ajaxData = array (
                        'success'          => true,
                        'newid'            => $newid,
                        'msg'              => trans('News Kategorie wurde erfolgreich gespeichert'),
                        'versionselection' => array ()
                    );

                    if ($this->input('addinline'))
                    {
                        $categories = $this->model->getCats(true);
                        $parent = 0;
                        foreach ( $categories as $rc )
                        {
                            if ( $r[ 'id' ] == $rc[ 'id' ] )
                            {
                                $parent = $rc[ 'parentid' ];
                            }
                        }


                        $tree = new Tree();
                        $tree->setupData($categories, 'id', 'parentid');
                        $c = $tree->buildRecurseArray();


                        $emptyData = array (
                            'value'    => 0,
                            'parentid' => 0,
                            'label'    => '-----'
                        );

                        array_unshift($c, $emptyData);

                        $ajaxData[ 'cat_options' ] = Arr::convertKeys($c, array (
                            'id'       => 'value',
                            'treename' => 'label'
                        ));
                    }



					echo Library::json($ajaxData);
					//Library::sendJson(true, trans("News Kategorie wurde erfolgreich gespeichert") );
				}

				exit;
			}
			else
			{


				if ( !$cat_id )
				{
					Error::raise("Invalid categorie ID!");
				}

				$sql = "SELECT c.id FROM
                            %tp%news_categories AS c
                        LEFT JOIN %tp%news_categories_trans AS ct ON(ct.id=c.id)
                       WHERE ct.title=? AND ct.lang = ?";
				$r   = $this->db->query($sql, $t, CONTENT_TRANS)->fetch();
				if ( $r[ 'id' ] && $r[ 'id' ] != $cat_id )
				{
					Library::sendJson(false, trans('Diese Kategorie existiert bereits!'));
				}

				//	$original = $newsModel->getVersioningRecord( $cat_id, $r[ 'lang' ], true );

				$post                  = $this->_post();
				$post[ 'alias' ]       = $aliasRegistry->getAlias();
				$post[ 'suffix' ]      = $aliasRegistry->getSuffix();
				$post[ 'teaserimage' ] = serialize($post[ 'teaserimage' ]);


				$t   = $this->input('title');
				$sql = "SELECT c.id, ct.lang FROM
                            %tp%news_categories AS c
			    LEFT JOIN %tp%news_categories_trans AS ct ON(ct.id=c.id) 
			    WHERE ct.title=? AND ct.lang = ?";
				$r   = $this->db->query($sql, $t, CONTENT_TRANS)->fetch();

				if ( $r[ 'id' ] && $r[ 'id' ] != $cat_id )
				{
					Library::sendJson(false, trans('Diese Kategorie existiert bereits!'));
				}


				if ( $cat_id == (int)$this->input('parentid') )
				{
					Library::sendJson(false, trans('Die Kategorie kann nicht gleichzeitig als Übergeordnete Kategorie verwendet werden!'));
				}

                $this->Event->trigger('onBeforeSave.newscat', $cat_id, $post);

				$newid = $this->model->saveCatTranslation($cat_id, $post);

				$this->saveContentDraft($newid, trim((string)$this->input('title')), trans('Nachrichten Kategorie'));

				//$newsModel->unlockDocument( $cat_id, 'news', 'index' );
				#$this->Pagemeta->saveData($cat_id);
				//	$newsModel->createVersion( $original, true );

                $this->Event->trigger('onAfterSave.newscat', $cat_id, $post);

				Library::log("Edit News Categorie " . $this->input('title') . " (ID:{$cat_id}).");


				//$this->load( 'PageCache' );
				//$this->PageCache->cleanCache( $cat_id, 'news', 'index' );

				if ( !IS_AJAX )
				{
					header("Location: admin.php?adm=news&action=list_cats");
				}
				else
				{
					echo Library::json(array (
					                         'success'          => true,
					                         'msg'              => trans('News Kategorie wurde erfolgreich aktualisiert'),
					                         'versionselection' => $this->Versioning->buildAjaxVersions($cat_id, '%tp%news_categories')
					                   ));
				}

				exit;
			}
		}


		/**
		 * Versioning
		 */
		$record      = $this->db->query('SELECT * FROM %tp%news_categories WHERE id = ?', $cat_id)->fetch();
		$recordTrans = $this->db->query('SELECT * FROM %tp%news_categories_trans WHERE id = ? AND lang = ?', $cat_id, CONTENT_TRANS)->fetch();


		// create init version
		$this->Versioning->createInitialVersion($cat_id, 'news_categories', $record, $recordTrans);
		Library::$versionRecords = $this->Versioning->getVersions($cat_id, 'news_categories');

		/**
		 * Init Tags
		 */
		$this->load('Tags');
		$this->Tags->setContentTable('news_trans');


		if ( $cat_id )
		{

			if ( ($lockedBy = $this->model->checkLocked($cat_id, 'news', 'index')) !== false )
			{
				Library::sendJson(false, sprintf(trans('Dieses Dokument wird bereits von %s bearbeitet.'), $lockedBy) );
			}



			/**
			 * copy core translation to current translation
			 */
			if ( !$this->model->hasTranslation($cat_id, true) )
			{
                $this->model->copyOriginalTranslation($cat_id, true);
			}

			$transq = $this->buildTransWhere('news_categories', 'c.id', 'ct');
			$r      = $this->db->query('SELECT c.*, ct.* FROM %tp%news_categories AS c
                                   LEFT JOIN %tp%news_categories_trans AS ct ON(ct.id=c.id)
                                   WHERE c.id= ? AND ' . $transq, $cat_id)->fetch();
			/**
			 * Init Metadata
			 */
			$this->Document->getMetaInstance()->initMetadata($r);

			$r[ 'contenttags' ] = $this->Tags->getContentTags($r[ 'tags' ]);

			Library::$versionRecords = $this->Versioning->getVersions($cat_id, 'news_categories');
		}


		$this->model->lock(array (
		                         'pk'         => 'id',
		                         'table'      => '%tp%news_categories',
		                         'contentid'  => $cat_id,
		                         'title'      => $r[ 'title' ],
		                         'controller' => 'news',
		                         'action'     => 'index'
		                   ));

		/*
		  $this->model->lock( 'id', 'news_categories', $id, 'index' );
		  $this->ContentLock->lock( $cat_id, 'news', 'index', $r[ 'title' ] );
		 */

		if ( $r[ 'password' ] != "" )
		{
			$this->load('Crypt');
			$r[ 'password' ] = $this->Crypt->decrypt($r[ 'password' ]);
		}


		$r[ 'description' ] = $r[ 'description' ];
		$r[ 'page_suffix' ] = $r[ 'suffix' ];
		$r[ 'page_alias' ]  = $r[ 'alias' ];

		$categories = $this->model->getCats(true);


		$tree = new Tree();
		$tree->setupData($categories, 'id', 'parentid');
		$c = $tree->buildRecurseArray();


		$emptyData = array (
			'value'    => 0,
			'parentid' => 0,
			'label'    => '-----'
		);


		array_unshift($c, $emptyData);


		$r[ 'cat_options' ] = Arr::convertKeys($c, array (
		                                                 'id'       => 'value',
		                                                 'treename' => 'label'
		                                           ));

		if ( $id )
		{
			$r[ 'cat_options' ][ 'selected' ] = $r[ 'cat_id' ];
		}

		if ( !$id && $this->input('catid') > 0 )
		{
			$r[ 'cat_options' ][ 'selected' ] = (int)$this->input('catid');
		}


		unset($c);

		$groups = $this->getUserGroups();

		$r[ 'access_options' ] = Arr::convertKeys($groups, array (
		                                                         'groupid' => 'value',
		                                                         'title'   => 'label'
		                                                   ));

		array_unshift($r[ 'access_options' ], array (
		                                            'value' => 0,
		                                            'label' => trans('Alle Benutzergruppen')
		                                      ));

		$r[ 'access_options' ][ 'selected' ] = (!empty($r[ 'access' ]) ? explode(',', $r[ 'access' ]) : array (
			0
		));


		$r[ 'teaserimage' ] = unserialize($r[ 'teaserimage' ]);


		Library::addNavi(trans('News Kategorien'));
		Library::addNavi(($cat_id ? sprintf(trans('News Kategorie `%s` bearbeiten'), $r[ 'title' ]) :
			trans('News Kategorie erstellen')));
		$this->Template->process('news/news_catedit', $r, true);
		exit;
	}

}

?>