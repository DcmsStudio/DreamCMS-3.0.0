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
 * @file         Add.php
 */
class Menues_Action_Add extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}


		$data  = $this->_post();

		if ( empty($data[ 'title' ]) )
		{
			Library::sendJson(false, trans('Um einen Menüpunkt erstellen zu können, wird der Titel benötigt'));
		}

		if ( empty($data[ 'type' ]) )
		{
			Library::sendJson(false, trans('Bitte wählen Sie einen Typ für Ihren Menüpunkt'));
		}

		if ( !is_array($data[ 'access' ]) )
		{
			Library::sendJson(false, trans('Bitte wählen Sie mind. eine Benutzergruppe'));
		}

		$data[ 'usergroups' ] = implode(',', $data[ 'access' ]);
		if ( $data[ 'usergroups' ] == '' )
		{
			Library::sendJson(false, trans('Bitte wählen Sie mind. einen Benutzergruppe für Ihren Menüpunkt'));
		}
		demoadm();
		$data[ 'published' ] = (int)$data[ 'published' ];

		$data = $this->prepareMenuData($data);

        $this->Document
            ->validateModel('navi_items', true)
            ->loadConfig()
            ->getMetaInstance()
            ->setMetadataType(true);


		$id = $this->model->saveMenu((int)$data[ 'id' ], $data);


		$data[ 'itemid' ] = $id;
		$this->load('Usergroup');
		$data[ 'usergroups' ]  = $this->Usergroup->getAllUsergroups();
		$_data[ 'usergroups' ] = $data[ 'usergroups' ];

		if ( $data[ 'type' ] == 'spacer' || $data[ 'type' ] == 'megamenu' || $data[ 'type' ] == 'folder' )
		{
			$data[ 'pagetypehtml' ] = $this->model->loadMenuType($data);
		}

		$data[ 'accesslist' ] = $this->_post('access');

		$_data[ 'newitem' ] = $data;

		$htmlCode = $this->Template->process('menu/list_menues', $_data, false, 'new-menuitem');


		echo Library::json(array (
		                         'newid'         => $id,
		                         'title'         => $data[ 'title' ],
		                         'htmlCode'      => $htmlCode,
		                         'pagetype_html' => $this->model->loadMenuType($data),
		                         'msg'           => ((int)$data[ 'id' ] ?
				                         trans('Menüpunkt wurde erfolgreich aktualisiert') :
				                         trans('Menüpunkt wurde erfolgreich hinzugefügt'))
		                   ));
		exit;
	}

	/**
	 * @param $data
	 * @return mixed
	 */
	private function prepareMenuData ( &$data )
	{

		$aliasRegistry = new AliasRegistry();

		$data[ 'cssclass' ] = '';

		switch ( $data[ 'type' ] )
		{

			case 'rootpage':
				$data[ 'type' ]       = 'rootpage';
				$data[ 'controller' ] = 'main';
				$data[ 'action' ]     = 'index';

				$aliasCheck = $aliasRegistry->aliasExists(array (
				                                                'alias'         => $data[ 'alias' ],
				                                                'suffix'        => $data[ 'suffix' ],
				                                                'documenttitle' => $data[ 'title' ]
				                                          ));


				break;

			case 'spacer':
				$data[ 'type' ]       = 'spacer';
				$data[ 'controller' ] = 'main';
				$data[ 'action' ]     = 'index';
				$data[ 'link' ]       = '';
				$data[ 'alias' ]      = '';
				$data[ 'suffix' ]     = '';
				$skipAlias            = true;
				break;
			case 'folder':
				$data[ 'type' ]       = 'folder';
				$data[ 'controller' ] = 'main';
				$data[ 'action' ]     = 'index';
				$data[ 'link' ]       = '';
				$data[ 'alias' ]      = '';
				$data[ 'suffix' ]     = '';
				$skipAlias            = true;
				break;
			case 'megamenu':
				$data[ 'type' ]       = 'megamenu';
				$data[ 'controller' ] = 'main';
				$data[ 'action' ]     = 'index';
				$data[ 'link' ]       = '';
				$data[ 'alias' ]      = '';
				$data[ 'suffix' ]     = '';
				$skipAlias            = true;
				break;


			case 'plugin':
				$data[ 'type' ]       = 'plugin';
				$data[ 'controller' ] = 'plugin';
				$data[ 'action' ]     = $data[ 'plug' ];
				$data[ 'link' ]       = $data[ 'pluginurl' ];

				$aliasCheck = $aliasRegistry->aliasExists(array (
				                                                'alias'         => $data[ 'alias' ],
				                                                'suffix'        => $data[ 'suffix' ],
				                                                'documenttitle' => $data[ 'title' ]
				                                          ));

				break;

			case 'internal':

				if ( empty($data[ 'module' ]) || $data[ 'module' ] == '' )
				{
					Library::sendJson(false, trans('Sie müssen ein Modul angeben bevor sie fortsetzen können!'));
				}

				// Fix frontpage
				$cpage = explode('/', $data[ 'module' ]);

				if ( $cpage[ 0 ] === 'printpage' )
				{
					$data[ 'controller' ]    = $cpage[ 0 ];
					$data[ 'action' ]        = ($cpage[ 1 ] != '' ? $cpage[ 1 ] : 'index');
					$data[ 'appid' ]         = '';
					$data[ 'appcontroller' ] = '';
					$data[ 'appalias' ]      = '';
					$data[ 'contentid' ]     = '';
					$data[ 'type' ]          = 'internal';
					$data[ 'link' ]          = '';
					$data[ 'alias' ]         = '';
					$data[ 'suffix' ]        = '';
					$skipAlias               = true;
				}
				else
				{
					if ( $cpage[ 0 ] == 'index' && $cpage[ 1 ] == 'index' )
					{
						$cpage[ 0 ] = 'main';
					}

					$data[ 'type' ]       = 'internal';
					$data[ 'contentid' ]  = 0;
					$data[ 'controller' ] = $cpage[ 0 ];
					$data[ 'action' ]     = ($cpage[ 1 ] != '' ? $cpage[ 1 ] : 'index');
					$data[ 'link' ]       = $data[ 'url' ];
					$data[ 'alias' ]      = '';
					$data[ 'suffix' ]     = '';
					$aliasCheck           = $aliasRegistry->aliasExists(array (
					                                                          'alias'         => $data[ 'alias' ],
					                                                          'suffix'        => $data[ 'suffix' ],
					                                                          'documenttitle' => $data[ 'title' ]
					                                                    ));
				}
				break;

			case 'appcat':
				$isApp = true;

				$catid = (int)$data[ 'appcatid' ];
				$appid = (int)$data[ 'appid' ];

				$data[ 'current-url' ] = isset($data[ 'current-url' ]) ? trim($data[ 'current-url' ]) : '';

				$app = $this->db->query('SELECT appid, alias, apptype, title FROM %tp%applications WHERE appid = ? AND pageid = ?', $appid, PAGEID)->fetch();


				$currentcontentid = (int)$data[ 'current-contentid' ];

				if ( $currentcontentid && $data[ 'current-url' ] !== '' )
				{
					$data[ 'controller' ]    = 'apps';
					$data[ 'appid' ]         = $app[ 'appid' ];
					$data[ 'appcontroller' ] = $app[ 'apptype' ];
					$data[ 'appalias' ]      = $app[ 'alias' ] !== '' ? $app[ 'alias' ] :
						Library::suggest($app[ 'title' ]);
					$data[ 'contentid' ]     = $currentcontentid;
					$data[ 'type' ]          = 'appcat';
					$data[ 'action' ]        = 'item';
					$data[ 'link' ]          = $data[ 'current-url' ];
					$data[ 'alias' ]         = '';
					$data[ 'suffix' ]        = '';

					$skipAlias = true;
				}
				else
				{

					$data[ 'appid' ]         = $app[ 'appid' ];
					$data[ 'contentid' ]     = $catid;
					$data[ 'appcontroller' ] = $app[ 'apptype' ];
					$data[ 'appalias' ]      = Library::suggest($app[ 'title' ]);
					$data[ 'controller' ]    = 'apps';
					$data[ 'action' ]        = 'category';
					$data[ 'type' ]          = 'appcat';


					$catalias = Applicationcats::getInstance()->getCategorie($app[ 'appid' ], $catid);

					if ( $catid )
					{
						if ( $catalias[ 'alias' ] )
						{
							$aliasChangedWarn       = true;
							$aliasCheck[ 'alias' ]  = $catalias[ 'alias' ];
							$aliasCheck[ 'suffix' ] = $catalias[ 'suffix' ];
						}
						else
						{


							$aliasCheck = $aliasRegistry->aliasExists(array (
							                                                'alias'         => $data[ 'alias' ],
							                                                'suffix'        => $data[ 'suffix' ],
							                                                'documenttitle' => $catalias[ 'title' ]
							                                          ));
						}
					}
					else
					{

						$aliasCheck = $aliasRegistry->aliasExists(array (
						                                                'alias'         => $data[ 'alias' ],
						                                                'suffix'        => $data[ 'suffix' ],
						                                                'documenttitle' => $data[ 'title' ]
						                                          ));
					}
				}

				break;

			case 'page':
				$data[ 'contentid' ]  = $data[ 'staticpage' ];
				$data[ 'controller' ] = 'page';
				$data[ 'action' ]     = 'index';
				$data[ 'type' ]       = 'page';


				$_model  = Model::getModelInstance('page');
				$modData = $_model->findItemByID($data[ 'contentid' ]);

				$data[ 'alias' ]  = $modData[ 'alias' ];
				$data[ 'suffix' ] = $modData[ 'suffix' ];


				$aliasCheck = $aliasRegistry->aliasExists(array (
				                                                'alias'         => $data[ 'alias' ],
				                                                'suffix'        => $data[ 'suffix' ],
				                                                'documenttitle' => $data[ 'title' ]
				                                          ));
				break;

			case 'newscat':
				$data[ 'appid' ]      = 0;
				$data[ 'contentid' ]  = $data[ 'catid' ];
				$data[ 'controller' ] = 'news';
				$data[ 'action' ]     = 'index';
				$data[ 'type' ]       = 'newscat';

				$_model           = Model::getModelInstance('news');
				$cats             = $_model->getCategories();
				$data[ 'alias' ]  = '';
				$data[ 'suffix' ] = '';

				foreach ( $cats as $r )
				{
					if ( $data[ 'contentid' ] == $r[ 'id' ] )
					{
						$data[ 'alias' ]  = $r[ 'alias' ];
						$data[ 'suffix' ] = $r[ 'suffix' ];
						$data[ 'title' ]  = $r[ 'title' ];
					}
				}

				$aliasCheck = $aliasRegistry->aliasExists(array (
				                                                'alias'         => $data[ 'alias' ],
				                                                'suffix'        => $data[ 'suffix' ],
				                                                'documenttitle' => $data[ 'title' ]
				                                          ));
				break;

			case 'articlecat':

				$_ptype       = 'articlecategorie';
				$_pcontroller = 'article';
				$_paction     = 'index';

				$add = '';
				# HTTP::setinput('alias', $data['oldalias']);
				# HTTP::setinput('suffix', $data['oldsuffix']);

				if ( $data[ 'alias' ] )
				{
					$add .= '/' . $data[ 'alias' ];
				}
				if ( $add && $data[ 'suffix' ] )
				{
					$add .= '.' . $data[ 'suffix' ];
				}


				if ( !$add )
				{
					$data[ 'alias' ]  = Library::suggest($data[ 'name' ], 'alias');
					$data[ 'suffix' ] = Settings::get('mod_rewrite_suffix', 'html');

					if ( $data[ 'alias' ] )
					{
						$add .= '/' . $data[ 'alias' ];
					}
					if ( $add && $data[ 'suffix' ] )
					{
						$add .= '.' . $data[ 'suffix' ];
					}
				}

				if ( $add )
				{
					$data[ 'link' ] = $add;
				}
				else
				{
					$data[ 'link' ] = '/articlearchiv/' . $data[ 'catid' ] . $add;
				}

				$data[ 'componentid' ] = $data[ 'catid' ];
				$newpagetid            = $data[ 'catid' ];
				$data[ 'contentid' ]   = $data[ 'catid' ];
				break;


			case 'url':
			default:

				if ( !Validation::isValidUrl(trim($data[ 'url' ]), false) )
				{
					Library::sendJson(false, trans('Diese Url ist nicht Valid!'));
				}

				$data[ 'type' ]       = 'url';
				$data[ 'controller' ] = 'main';
				$data[ 'action' ]     = 'jumpextern';
				$data[ 'link' ]       = $data[ 'url' ];
				$data[ 'alias' ]      = '';
				$data[ 'suffix' ]     = '';
				$aliasCheck           = $aliasRegistry->aliasExists(array (
				                                                          'alias'         => $data[ 'alias' ],
				                                                          'suffix'        => $data[ 'suffix' ],
				                                                          'documenttitle' => $data[ 'title' ]
				                                                    ));

				break;
		}


		if ( !$skipAlias && $aliasCheck && ((int)$data[ 'id' ] && $aliasRegistry->getErrorAliasID() != (int)$data[ 'id' ]) )
		{
			Library::sendJson(false, sprintf(trans('Der Alias "%s" existiert bereits!'), $data[ 'alias' ]));
		}

		return $data;
	}

}

?>