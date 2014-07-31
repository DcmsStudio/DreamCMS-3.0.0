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
 * @package      Menues
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Edit.php
 */
class Menues_Action_Edit extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}
		$this->editMenuItem();
	}

	private function renameItem ()
	{

		$menuID = (int)HTTP::input('id');
		$model  = Model::getModelInstance();

		$title = HTTP::input('title');

		if ( !trim($title) )
		{
			Library::sendJson(false);
		}

		$data = $model->_load_menu($menuID);

		demoadm();

		//$model->save()
	}

	/**
	 *
	 * @return void <type>
	 */
	private function editMenuItem ()
	{
        $this->Document
            ->validateModel('navi_items', true)
            ->loadConfig()
            ->getMetaInstance()
            ->setMetadataType(true);

		$menuitemid = (int)HTTP::input('itemid');
		$parentid   = (int)HTTP::input('parent') ? (int)HTTP::input('parent') : 1;


		$data = array ();

		if ( $menuitemid )
		{

			//  if (!$model->hasTranslation($menuitemid))
			//  {
			//  $model->copyOriginalTranslation($menu_id);
			// }


			$data = $this->model->getMenuItemsByMenuID($menuitemid);
			$data = $data[ 0 ];
		}


		/**
		 * Read Application Categories
		 * AJAX Request
		 */
		$appid = (int)HTTP::input('loadapp');
		if ( $appid )
		{

			$a    = Application::getInstance();
			$apps = $a->getApps();
			$cats = array ();
			foreach ( $apps as $r )
			{
				if ( $appid == $r[ 'appid' ] )
				{
					$rule      = $a->getRouterMap($r[ 'apptype' ], $r[ 'appid' ]);
					$appalias  = $a->getAppMapAlias();
					$_appalias = $appalias[ $r[ 'appid' ] ];
					$cats      = $this->getAppCats($r, $_appalias);

					break;
				}
			}

			echo Library::json(array (
			                         'success'   => true,
			                         'contentid' => $data[ 'contentid' ],
			                         'cats'      => $cats
			                   ));
			exit;
		}

		/**
		 * Init Metadata
		 */
		$this->Document->getMetaInstance()->initMetadata($data);

		if ( HTTP::input('loadtype') )
		{
			$data[ 'currenttype' ] = $data[ 'type' ];
			$data[ 'type' ]        = HTTP::input('loadtype');
		}


		$data[ 'accesslist' ] = explode(',', $data[ 'groups' ]);

		if ( $data[ 'link' ] )
		{
			$link = $data[ 'link' ];

			if ( substr($link, 0, 1) == '/' )
			{
				$link = substr($link, 1);
			}
			else
			{
				$data[ 'link' ] = '/' . $data[ 'link' ];
			}

			$vals = explode('/', $link);

			list($alias, $suffix) = explode('.', $vals[ count($vals) - 1 ]);
			if ( !is_numeric($alias) )
			{
				#   $data['page_alias'] = $alias;
				#   $data['page_suffix'] = $suffix;
			}
		}


		$data[ 'usergroups' ] = $this->db->query("SELECT groupid, title FROM %tp%users_groups ORDER BY title ASC")->fetchAll();
		$data[ 'pages' ]      = $this->model->getPages();
		//    $data[ 'parentpages' ]  = $this->loadParentPages( $data );
		$data[ 'pagetypehtml' ] = $this->model->loadMenuType($data);

		if ( HTTP::input('loadtype') )
		{

			echo Library::json(array (
			                         'success' => true,
			                         'html'    => $data[ 'pagetypehtml' ]
			                   ));
			exit;
		}

		//$this->Template->addScript(JS_URL . 'dcms.livesearch');
		$this->Template->process('menu/item_edit', $data, true);
		exit;
	}

}
