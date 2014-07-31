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
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Editcat.php
 */
class Page_Action_Editcat extends Controller_Abstract
{

	/**
	 * @param bool $isNew
	 * @throws BaseException
	 */
	public function execute ( $isNew = false )
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

		$id = (int)$this->input('catid');


		$this->model->setTable('pages_categories');

		$modelTables = $this->model->getConfig('tables');
		if ( !isset($modelTables[ 'pages_categories' ]) )
		{
			throw new BaseException('The page categories modul has no Model configuration! ' . print_r($modelTables, true));
		}


		$this->Document->setTableConfiguration('pages_categories', $modelTables[ 'pages_categories' ]);
		$this->Document->getMetaInstance()->setMetadataType(true);


		// Rollback the new translation
		if ( $this->input('transrollback') )
		{
			$this->model->rollbackTranslation($id, true);
			Library::sendJson(true);
		}

		$this->load('Versioning');

		// Change version
		if ( $this->_post('setVersion') )
		{
			$this->Versioning->undoVersion((int)$this->_post('setVersion'), $id, 'pages_categories');
		}

		$this->load('ContentLock');
		$this->load('ContentTranslation');


		/**
		 * Save
		 */
		if ( $this->input('send') )
		{
			demoadm();

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


			$alias  = $this->input('alias');
			$suffix = $this->input('suffix');

			$aliasRegistry = new AliasRegistry();
			$aliasExists   = $aliasRegistry->aliasExists(array (
			                                                   'alias'         => $alias,
			                                                   'suffix'        => $suffix,
			                                                   'documenttitle' => $this->input('title')
			                                             ));

			if ( $aliasExists && ($id && $aliasRegistry->getErrorAliasID() != $id) )
			{
				Library::log(sprintf('Alias Builder has found many errors! The Alias `%s` already exists!', $aliasRegistry->getAlias()), 'warn');
				Library::sendJson(false, sprintf(trans('Der Alias "%s" existiert bereits!'), $aliasRegistry->getAlias()));
			}


			// Neu erstellen
			if ( !$id )
			{
				$sql = "SELECT c.catid FROM %tp%pages_categories AS c
			    LEFT JOIN %tp%pages_categories_trans AS ct ON(ct.catid=c.catid)
			    WHERE ct.title=? AND ct.lang = ?";
				$r   = $this->db->query($sql, $t, CONTENT_TRANS)->fetch();

				if ( $r[ 'catid' ] )
				{
					Library::sendJson(false, trans('Diese Kategorie existiert bereits!'));
				}

				//	$this->load( 'PageCache' );
				//	$this->PageCache->cleanCache( 0, 'news', 'index' );


				$post             = $this->_post();
				$post[ 'alias' ]  = $aliasRegistry->getAlias();
				$post[ 'suffix' ] = $aliasRegistry->getSuffix();

				$newid = $this->model->saveCatTranslation(0, $post);

				/**
				 * Build initial Version
				 */
				$record      = $this->db->query('SELECT * FROM %tp%pages_categories WHERE catid=?', $newid)->fetch();
				$contentData = $this->ContentTranslation->getTranslation($newid, 'pages_categories', 'catid');

				$this->saveContentDraft($newid, trim((string)HTTP::input('title')), trans('Seiten Kategorie'));
				$this->Versioning->createInitialVersion($newid, 'pages_categories', $record, $contentData);


				Library::log("Add Page Categorie " . $this->input('title') . ".");

				if ( !IS_AJAX )
				{
					header("Location: admin.php?adm=page&action=pagecats");
				}
				else
				{

                    $ajax = array (
                        'success'          => true,
                        'newid'            => $newid,
                        'msg'              => trans('Seiten Kategorie wurde erfolgreich gespeichert'),

                        'versionselection' => $this->Versioning->buildAjaxVersions($newid, '%tp%pages_categories')
                    );

                    if ($this->input('addinline'))
                    {
                        $categories = $this->model->getCats(true);
                        $parent = 0;
                        foreach ( $categories as $rc )
                        {
                            if ( $r[ 'catid' ] == $rc[ 'catid' ] )
                            {
                                $parent = $rc[ 'parentid' ];
                            }
                        }


                        $tree = new Tree();
                        $tree->setupData($categories, 'catid', 'parentid');
                        $c = $tree->buildRecurseArray();


                        $emptyData = array (
                            'value'    => 0,
                            'parentid' => 0,
                            'label'    => '-----'
                        );


                        array_unshift($c, $emptyData);


                        $ajax[ 'cat_options' ] = Arr::convertKeys($c, array (
                            'id'       => 'value',
                            'treename' => 'label'
                        ));

                        if ( $id )
                        {
                            $ajax[ 'cat_options' ][ 'selected' ] = ($parent ? $parent : 0);
                        }

                        if ( !$id && $this->input('catid') > 0 )
                        {
                            $ajax[ 'cat_options' ][ 'selected' ] = (int)$this->input('catid');
                        }

                    }

					echo Library::json($ajax);
				}

				exit;
			}
			else
			{


				if ( !$id )
				{
					Error::raise("Invalid categorie ID!");
				}

				$sql = "SELECT c.catid FROM
                            %tp%pages_categories AS c
                        LEFT JOIN %tp%pages_categories_trans AS ct ON(ct.catid=c.catid)
                       WHERE ct.title=? AND ct.lang = ?";
				$r   = $this->db->query($sql, $t, CONTENT_TRANS)->fetch();
				if ( $r[ 'catid' ] && $r[ 'catid' ] != $id )
				{
					Library::sendJson(false, trans('Diese Kategorie existiert bereits!'));
				}

				//	$original = $newsModel->getVersioningRecord( $cat_id, $r[ 'lang' ], true );
				$post                  = $this->_post();
				$post[ 'alias' ]       = $aliasRegistry->getAlias();
				$post[ 'suffix' ]      = $aliasRegistry->getSuffix();
				$post[ 'teaserimage' ] = serialize($post[ 'teaserimage' ]);

				if ( $id == (int)$this->input('parentid') )
				{
					Library::sendJson(false, trans('Die Kategorie kann nicht gleichzeitig als Übergeordnete Kategorie verwendet werden!'));
				}

				$newid = $this->model->saveCatTranslation($id, $post);

				$this->saveContentDraft($newid, trim((string)$this->input('title')), trans('Seiten Kategorie'));


				Library::log("Edit Page Categorie " . $this->input('title') . " (ID:{$id}).");

				Cache::delete('pages-cats-' . PAGEID . '-' . CONTENT_TRANS, 'data/pages/');

				//$this->load( 'PageCache' );
				//$this->PageCache->cleanCache( $cat_id, 'news', 'index' );

				if ( !IS_AJAX )
				{
					header("Location: admin.php?adm=page&action=pagecats");
				}
				else
				{
					echo Library::json(array (
					                         'success'          => true,
					                         'msg'              => trans('Seiten Kategorie wurde erfolgreich aktualisiert'),
					                         'versionselection' => $this->Versioning->buildAjaxVersions($id, '%tp%pages_categories')
					                   ));
				}

				exit;
			}
		}


		/**
		 * Versioning
		 */
		$record      = $this->db->query('SELECT * FROM %tp%pages_categories WHERE catid = ?', $id)->fetch();
		$recordTrans = $this->db->query('SELECT * FROM %tp%pages_categories_trans WHERE catid = ? AND lang = ?', $id, CONTENT_TRANS)->fetch();


		// create init version
		$this->Versioning->createInitialVersion($id, 'pages_categories', $record, $recordTrans);
		Library::$versionRecords = $this->Versioning->getVersions($id, 'pages_categories');

		/**
		 * Init Tags
		 */
		$this->load('Tags');
		$this->Tags->setContentTable('pages_categories_trans');


		if ( $id )
		{

			if ( ($lockedBy = $this->model->checkLocked($id, 'page', 'cat')) !== false )
			{
				Library::sendJson(false, sprintf(trans('Dieses Dokument wird bereits von %s bearbeitet.'), $lockedBy) );
			}


			/**
			 * copy core translation to current translation
			 */
			if ( !$this->model->hasTranslation($id, true) )
			{
				$this->model->copyOriginalTranslation($id, true);
			}


			$r = $this->model->getCategorieById($id);
			/**
			 * Init Metadata
			 */
			$this->Document->getMetaInstance()->initMetadata($r);

			$r[ 'contenttags' ] = $this->Tags->getContentTags($r[ 'tags' ]);

			Library::$versionRecords = $this->Versioning->getVersions($id, 'pages_categories');


			$this->Document->addRollback();

			$this->model->lock(array (
			                         'pk'         => 'catid',
			                         'table'      => '%tp%pages_categories',
			                         'contentid'  => $id,
			                         'title'      => $r[ 'title' ],
			                         'controller' => 'page',
			                         'action'     => 'cat'
			                   ));
		}

		if ( $r[ 'password' ] != "" )
		{
			$this->load('Crypt');
			$r[ 'password' ] = $this->Crypt->decrypt($r[ 'password' ]);
		}


		$r[ 'description' ] = $r[ 'description' ];
		$r[ 'page_suffix' ] = $r[ 'suffix' ];
		$r[ 'page_alias' ]  = $r[ 'alias' ];

		$categories = $this->model->getCats(true);


		$parent = 0;
		foreach ( $categories as $rc )
		{
			if ( $r[ 'catid' ] == $rc[ 'catid' ] )
			{
				$parent = $rc[ 'parentid' ];
			}
		}


		$tree = new Tree();
		$tree->setupData($categories, 'catid', 'parentid');
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
			$r[ 'cat_options' ][ 'selected' ] = ($parent ? $parent : 0);
		}

		if ( !$id && $this->input('catid') > 0 )
		{
			$r[ 'cat_options' ][ 'selected' ] = (int)$this->input('catid');
		}


		$tree = $categories = $c = null;

		$groups                = $this->getUserGroups();
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


		// $r[ 'teaserimage' ] = unserialize( $r[ 'teaserimage' ] );

		Library::addNavi(trans('Seiten Kategorien'));
		Library::addNavi(($id ? sprintf(trans('Seiten Kategorie `%s` bearbeiten'), $r[ 'title' ]) :
			trans('Seiten Kategorie erstellen')));

		$this->Template->process('pages/catedit', $r, true);

		exit;
	}

}
