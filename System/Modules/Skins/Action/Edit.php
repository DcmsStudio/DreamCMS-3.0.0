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
 * @package      Skins
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Edit.php
 */
class Skins_Action_Edit extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$id             = (int)HTTP::input('id');
		$title          = HTTP::input('title');
		$description    = HTTP::input('description');
		$author         = HTTP::input('author');
		$author_mail    = HTTP::input('author_mail');
		$author_website = HTTP::input('author_website');
		$navsplitter    = HTTP::input('navsplitter');
		$img_dir        = HTTP::input('img_dir');
		$published      = HTTP::input('published');
		$default_set    = HTTP::input('isdefault');

		$data = array ();


		$defaultskin   = $this->model->getDefaultSkin();
		$defaultskinId = $defaultskin[ 'id' ];

		if ( !$id && ACTION == 'edit' )
		{
			if ( IS_AJAX )
			{
				Library::sendJson(false, 'Skin not found.');
			}
			else
			{
				Error::raise('Skin not found.');
			}
		}


		if ( $id > 0 )
		{
			$data = $this->model->getSkinByID($id);


			if ( HTTP::input('send') )
			{
				demoadm();

				if ( !trim($title) )
				{
					Library::sendJson(false, trans('Sie haben den Titel des Skins vergessen einzutragen'));
				}

				if ( !trim($author) )
				{
					Library::sendJson(false, trans('Sie haben den Autor des Skins vergessen einzutragen'));
				}

				if ( $defaultskinId != $id )
				{
					//   $this->model->updateDefaultSkin( $id );
				}

				$add_arr = array (
					'title'          => $title,
					'description'    => $description,
					'author'         => $author,
					'author_mail'    => $author_mail,
					'author_website' => $author_website,
					'navsplitter'    => $navsplitter, //'default_set' => (int)$default_set ,
					'img_dir'        => $img_dir,
					'published'      => (int)(!$published && $defaultskinId != $id ? $published : 1)
				);

				$this->model->updateSkin($id, $add_arr);
				$this->writeSkinSettings($id);

				Library::log(sprintf('Edit the skin `%s`', $title));


				$this->addLastEdit($id, 'Skin ' . trim((string)$title));
				Library::sendJson(true, trans('Skin wurde erfolgreich aktualisiert'));
			}
		}
		else
		{
			if ( HTTP::input('send') )
			{
				if ( !trim($title) )
				{
					Library::sendJson(false, trans('Sie haben den Titel des Skins vergessen einzutragen'));
				}

				if ( !trim($author) )
				{
					Library::sendJson(false, trans('Sie haben den Autor des Skins vergessen einzutragen'));
				}


				$add_arr = array (
					'title'          => $title,
					'templates'      => '',
					'description'    => $description,
					'author'         => $author,
					'author_mail'    => $author_mail,
					'author_website' => $author_website,
					'navsplitter'    => $navsplitter,
					'default_set'    => $default_set,
					'img_dir'        => $img_dir,
					'published'      => ((!$published && $defaultskinId != $id) ? $published : 1)
				);

				$id = $this->model->saveSkin($add_arr);


				$this->writeSkinSettings($id);

				Library::log(sprintf('Add new skin `%s`', $title));


				$this->addLastEdit($id, 'Skin ' . trim((string)$title));
				Library::sendJson(true, trans('Skin wurde erfolgreich eingetragen'));
			}

			$data = array ();
		}


		Library::addNavi(trans('Frontend Skins Übersicht'));
		Library::addNavi(($id ? sprintf(trans('Frontend Skin `%s` bearbeiten'), $data[ 'title' ]) :
			trans('Frontend Skin erstellen')));


		$data[ 'image_dirs' ] = Library::getDirs(PUBLIC_PATH . 'simg/');

		if ( $id )
		{
			$data[ 'navsplitter' ] = Library::encode($data[ 'navsplitter' ]);
		}
		else
		{
			$data[ 'author_website' ] = User::get('homepage');
			$data[ 'author_mail' ]    = User::get('email');
			$data[ 'author' ]         = User::get('username');
			$data[ 'navsplitter' ]    = ' » ';
		}


		$this->Template->process('skins/edit', $data, true);
	}

	/**
	 * @todo create a XML file into the skin directory
	 *
	 * @param integer $id
	 */
	private function writeSkinSettings ( $id )
	{

		$data = $this->model->getSkinByID($id);


		if ( !is_dir(TEMPLATES_PATH . 'tpl-' . $id) )
		{
			mkdir(TEMPLATES_PATH . 'tpl-' . $id, 0777);
			$this->model->updateSkinAfterCreated($id, 'tpl-' . $id);
		}
		else
		{

		}
	}

}

?>