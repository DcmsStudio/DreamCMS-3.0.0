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
 * @file         Save_news.php
 */
class News_Action_Save_news extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}


		demoadm();

		$id = HTTP::input('newsid', 'int');


		$state            = (int)HTTP::input('published');
		$_access          = (is_array(HTTP::input('access')) ? implode(',', HTTP::input('access')) :
			HTTP::input('access'));
		$images           = HTTP::input('imagelist'); // Inline bilder
		$created_by       = ((int)HTTP::input('created_by') ? (int)HTTP::input('created_by') : User::getUserId());
		$created_by_alias = HTTP::input('created_by_alias');
		$published        = (int)HTTP::input('published');

		if ( $id )
		{
			$modified    = time();
			$modified_by = User::getUserId();
		}


		$fulltext = str_replace('<br>', '<br/>', trim((string)HTTP::input('content')));


		if ( trim((string)HTTP::input('title')) == '' )
		{
			Library::sendJson(false, trans("News Titel fehlt!"));
		}

		if ( !(int)HTTP::input('cat_id') )
		{
			Library::sendJson(false, trans("News enthält eine fehlerhafte Kategorie!"));
		}

		if ( !strlen(trim($fulltext)) )
		{
			Library::sendJson(false, trans("News enthält keinen Inhalt!"));
		}


		$alias  = HTTP::input('alias');
		$suffix = HTTP::input('suffix');

		$aliasRegistry = new AliasRegistry();
		$aliasExists   = $aliasRegistry->aliasExists(array (
		                                                   'alias'         => $alias,
		                                                   'suffix'        => $suffix,
		                                                   'documenttitle' => HTTP::input('title')
		                                             ));

		if ( $aliasExists && ($id && $aliasRegistry->getErrorAliasID() != $id) )
		{
			Library::log(sprintf('Alias Builder has found many errors! The Alias `%s` already exists!', $aliasRegistry->getAlias()), 'warn');
			Library::sendJson(false, sprintf(trans('Der Alias "%s" existiert bereits!'), $aliasRegistry->getAlias()));
		}


		/**

		$newsModel   = Model::getModelInstance();
		$modelTables = $newsModel->enableSearchIndexer()->getConfig('tables');
		if ( !isset($modelTables[ 'news' ]) )
		{
			throw new BaseException('The News modul has no Model configuration! ' . print_r($modelTables, true));
		}

		$this->Document->setTableConfiguration('news', $modelTables[ 'news' ]);
		$this->Document->getMetaInstance()->setMetadataType(true);
		$newsModel->setTable('news');

*/

        $this->Document
            ->validateModel('news', true)
            ->loadConfig()
            ->getMetaInstance()
            ->setMetadataType(true);





		$this->load('Versioning');

		$post            = HTTP::input();
		$post[ 'alias' ] = $aliasRegistry->getAlias();


		$aliasRegistry->freeMem();
		unset($aliasRegistry);


		if ( $id )
		{
			$original = $this->model->getVersioningRecord($id);

			if ( !$original[ 'record' ][ 'id' ] )
			{
				Error::raise("News not exist!");
			}

            $this->Event->trigger('onBeforeSave.news', $id, $post);

            $this->model->saveNewsTranslation($id, $post);

			$isDraft = $this->saveContentDraft($id, trim((string)$this->input('title')), trans('Nachrichten'));

			/**
			 * Create new version
			 */
            $this->model->createVersion($original);

			/**
			 *
			 */
			$versSelect = $this->Versioning->buildAjaxVersions($id, 'news');

			// Remove Cache
			Cache::delete('newsText-' . $id, 'data/news/' . CONTENT_TRANS);

            $this->model->unlockDocument($id, 'news', 'show');

            $this->Event->trigger('onAfterSave.news', $id, $post);

			/*
			  $this->load('PageCache');
			  $this->PageCache->cleanCache($id, 'news', 'show');


			  $this->load('Search');
			  $this->Search->updateIndex($id, CONTROLLER, HTTP::input('title'), HTTP::input('content'));
			 */

            // send pings
            if (!$isDraft && $post['documentmeta']['published'] == 1) {
                $ps = new PingService();
                $ps->setData('news/item/'. $post['alias'] . ($post['suffix'] ? '.'.$post['suffix'] : '.'.Settings::get( 'mod_rewrite_suffix', 'html' )) , $this->input('title'))->genericPing();
            }

            User::updateLastpost('news/item/' . $post['alias'] . ($post['suffix'] ? '.'.$post['suffix'] : '.'.Settings::get( 'mod_rewrite_suffix', 'html' )), $this->input('title'));

            Library::log("Updating News '" . HTTP::input('title') . "' (ID:{$id}).");


			echo Library::json(array (
			                         'success'          => true,
			                         'msg'              => trans('News wurde erfolgreich geändert'),
			                         'versionselection' => $versSelect
			                   ));
			exit;
		}
		else
		{

            $this->Event->trigger('onBeforeSave.news', $id, $post);

			$id = $this->model->saveNewsTranslation($id, $post);
			/*
			  $this->load('Search');
			  $this->Search->createIndex($id, CONTROLLER, HTTP::input('title'), HTTP::input('content'));
			 */

            $isDraft = $this->saveContentDraft($id, trim((string)$this->input('title')), trans('Nachrichten'));

            // send pings
            if (!$isDraft && $post['documentmeta']['published'] == 1)
            {
                $ps = new PingService();
                $ps->setData('news/item/'. $post['alias'] . ($post['suffix'] ? '.'.$post['suffix'] : '.'.Settings::get( 'mod_rewrite_suffix', 'html' )) , $this->input('title'))->genericPing();
            }

            // update user posts
            User::subPostCounter();
            User::updateLastpost('news/item/' . $post['alias'] . ($post['suffix'] ? '.'.$post['suffix'] : '.'.Settings::get( 'mod_rewrite_suffix', 'html' )), $this->input('title'));

            $this->Event->trigger('onAfterSave.news', $id, $post);

            Library::log("Creating News '" . HTTP::input('title') . "' (ID:{$id}).");


			echo Library::json(array (
			                         'success' => true,
			                         'newid'   => $id,
			                         'msg'     => trans('News wurde erfolgreich hinzugefügt.'),
			                         //'versionselection' => $versSelect
			                   ));


			exit;
		}


		die(print_r(HTTP::input()));
	}

}

?>