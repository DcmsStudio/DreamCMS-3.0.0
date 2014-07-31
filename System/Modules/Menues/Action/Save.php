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
 * @file         Save.php
 */
class Menues_Action_Save extends Controller_Abstract
{

	function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		demoadm();


		$validInput = true;
		$newid      = 0;
		$isApp      = false;
		$appID      = 0;


		$id          = (int)HTTP::input('id'); // menuitem id
		$insertafter = (int)HTTP::input('insertafter');
		$_params     = (is_array(HTTP::input('params')) ? serialize(HTTP::input('params')) : '');

		$data = HTTP::input();


		if ( !trim($data[ 'title' ]) )
		{
			Library::sendJson(false, trans('Es fehlt der Titel, dieser ist aber erforderlich!'));
		}

        $this->model->disableSearchIndex();
        $this->Document
            ->validateModel('navi_items', true)
            ->loadConfig()
            ->getMetaInstance()
            ->setMetadataType(true);


		$data[ 'breadcrumb' ] = 1;


		$aliasChangedWarn = false;
		$skipAlias        = false;

		//$load_menu = $model->_load_menu($id);

		if ( $id )
		{
			$load_menu = $this->model->getMenuItemByID($id);

			if ( isset($load_menu[ 'itemid' ]) )
			{
				$data = array_merge($load_menu, $data);

				$data[ 'action' ] = $load_menu[ 'action' ];
				$data[ 'module' ] = $load_menu[ 'controller' ] . '/' . $load_menu[ 'action' ];
			}
			else
			{
				Library::sendJson(false, trans('Dieser Menüpunkt existiert nicht!'));
			}
		}

		switch ( $data[ 'type' ] )
		{

			case 'rootpage':
				$data[ 'type' ]       = 'rootpage';
				$data[ 'controller' ] = 'main';
				$data[ 'action' ]     = 'index';


				break;


			case 'megamenu':
				$skipAlias            = true;
				$data[ 'type' ]       = 'megamenu';
				$data[ 'link' ]       = $data[ 'url' ];
				$data[ 'controller' ] = 'main';
				$data[ 'action' ]     = 'index';
				$data[ 'link' ]       = '';
				$data[ 'alias' ]      = '';
				$data[ 'suffix' ]     = '';
				break;

			case 'spacer':
				$data[ 'type' ]       = 'spacer';
				$data[ 'controller' ] = 'main';
				$data[ 'action' ]     = 'index';
				$data[ 'link' ]       = '';
				$data[ 'alias' ]      = '';
				$data[ 'suffix' ]     = '';


				$skipAlias = true;

				break;

			case 'plugin':
				$data[ 'type' ]       = 'plugin';
				$data[ 'controller' ] = 'plugin';
				$data[ 'action' ]     = ($data[ 'action' ] ? $data[ 'action' ] : $data[ 'plug' ]);
				$data[ 'link' ]       = ($data[ 'link' ] ? $data[ 'link' ] : $data[ 'pluginurl' ]);

				if ( empty($data[ 'alias' ]) || !isset($data[ 'alias' ]) )
				{
					$names            = explode('/', $data[ 'link' ]);
					$name             = array_pop($names);
					$data[ 'alias' ]  = Library::getFilename($name);
					$data[ 'suffix' ] = Library::getExtension($name);
				}

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
					$data[ 'contentid' ]     = 0;
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
							$aliasCheck = Modrewrite::checkAlias(array (
							                                           'controller' => $data[ 'controller' ],
							                                           'action'     => $data[ 'action' ],
							                                           'alias'      => $data[ 'alias' ],
							                                           'suffix'     => $data[ 'suffix' ],
							                                           'title'      => $catalias[ 'title' ],
							                                           'contentid'  => $catid,
							                                     ), true);
						}
					}
					else
					{

						$aliasCheck = Modrewrite::checkAlias(array (
						                                           'controller' => $data[ 'controller' ],
						                                           'action'     => $data[ 'action' ],
						                                           'alias'      => $data[ 'alias' ],
						                                           'suffix'     => $data[ 'suffix' ],
						                                           'title'      => $data[ 'title' ],
						                                           'id'         => $id,
						                                     ));
					}
				}

				break;

			case 'page':
				$data[ 'contentid' ]  = $data[ 'staticpage' ];
				$data[ 'controller' ] = 'page';
				$data[ 'action' ]     = 'index';
				$data[ 'type' ]       = 'page';

				break;

			case 'newscat':
				$data[ 'appid' ]      = 0;
				$data[ 'contentid' ]  = $data[ 'catid' ];
				$data[ 'controller' ] = 'news';
				$data[ 'action' ]     = 'index';
				$data[ 'type' ]       = 'newscat';

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


				break;
		}


		#    $this->Pagemeta->setTable('page');
		#      $this->Pagemeta->setPrimaryKey('id');


		$alias  = HTTP::input('alias');
		$suffix = HTTP::input('suffix');


		$aliasRegistry = new AliasRegistry();
		$aliasExists   = $aliasRegistry->aliasExists(array (
		                                                   'alias'         => $alias,
		                                                   'suffix'        => $suffix,
		                                                   'documenttitle' => HTTP::input('title')
		                                             ), $data[ 'controller' ], ($data[ 'appcontroller' ] ?
			$data[ 'appcontroller' ] : null));

		if ( !$skipAlias )
		{
			if ( isset($data[ 'contentid' ]) )
			{
				if ( $aliasExists && ($data[ 'contentid' ] && $aliasRegistry->getErrorAliasID() != $data[ 'contentid' ]) )
				{
					Library::log(sprintf('Alias Builder has found many errors! The Alias `%s` already exists!', $aliasRegistry->getAlias()), 'warn');
					Library::sendJson(false, sprintf(trans('Der Alias "%s" existiert bereits!'), $aliasRegistry->getAlias()));
				}
			}
			else
			{
				if ( $aliasExists && ($id && $aliasRegistry->getErrorAliasID() != $id) )
				{
					Library::log(sprintf('Alias Builder has found many errors! The Alias `%s` already exists!', $aliasRegistry->getAlias()), 'warn');
					Library::sendJson(false, sprintf(trans('Der Alias "%s" existiert bereits!'), $aliasRegistry->getAlias()));
				}
			}
		}


		if ( !$skipAlias && $aliasCheck === false )
		{
			Library::sendJson(false, sprintf(trans('Der Alias "%s" existiert bereits!'), $data[ 'alias' ]));
		}


		$data[ 'alias' ]  = $aliasRegistry->getAlias();
		$data[ 'suffix' ] = $aliasRegistry->getSuffix();

		$data[ 'usergroups' ] = array (
			0
		);
		if ( !isset($data[ 'groups' ]) && isset($data[ 'access' ]) )
		{
			$data[ 'usergroups' ] = (is_array($data[ 'access' ]) ? $data[ 'access' ] : array (
				0
			));
		}


		$data[ 'usergroups' ] = implode(',', $data[ 'usergroups' ]);

		if ( $id )
		{
			$menu = $this->model->getMenuByID($id);
			Cache::delete($menu[ 'templatekey' ] . '*', 'data/menu');
		}

		$newid = $this->model->saveMenu($id, $data);


		echo Library::json(array (
		                         'success' => true,
		                         'msg'     => trans("Menüpunkt wurde erfolgreich aktualisiert"),
		                         'newid'   => $newid,
		                         'title'   => $data[ 'title' ],
		                         'alias'   => $aliasRegistry->getAlias()
		                   ));
		exit;


		$documentmeta = HTTP::input('documentmeta');
		$on           = 0;
		$off          = 0;

		if ( function_exists('date_parse_from_format') )
		{
			if ( !empty($documentmeta[ 'publishon' ]) )
			{
				$on = date_parse_from_format("d.m.Y, H:i", $documentmeta[ 'publishon' ]);
				$on = mktime($on[ 'hour' ], $on[ 'minute' ], 59, $on[ 'day' ], $on[ 'month' ], $on[ 'year' ]);
			}
			if ( !empty($documentmeta[ 'publishoff' ]) )
			{
				$off = date_parse_from_format("d.m.Y, H:i", $documentmeta[ 'publishoff' ]);
				$off = mktime($off[ 'hour' ], $off[ 'minute' ], 59, $off[ 'day' ], $off[ 'month' ], $off[ 'year' ]);
			}
		}
		else
		{
			if ( !empty($documentmeta[ 'publishon' ]) )
			{
				$on = @strtotime($documentmeta[ 'publishon' ]);
			}
			if ( !empty($documentmeta[ 'publishoff' ]) )
			{
				$off = @strtotime($documentmeta[ 'publishoff' ]);
			}
		}

		//$this->Pagemeta->initPagedata($id, $data['title']);


		$mgroups = (is_array(HTTP::input('access')) ? implode(',', HTTP::input('access')) : null);


		$translation = $model->getMenuTranslation($id);

		if ( !isset($translation[ 'id' ]) )
		{
			// insert the Translation
		}

		if ( !$id )
		{

			/**
			 * ------------------------------------
			 *
			 *
			 *
			 *      ERROR ????
			 *
			 *
			 *
			 *
			 *
			 * ------------------------------------
			 */
			$nextpage_id = $this->db->getNextInsertId('%tp%page');
			$contentid   = $data[ 'contentid' ];

			/**
			 * @todo Verschieben der alten um 10 nach hinten
			 */
			$order = $this->db->query('SELECT MAX(ordering) AS ordering FROM %tp%page WHERE breadcrumb = 1 AND id = ' . (int)HTTP::input('parentid'))->fetch();


			if ( $data[ 'type' ] == 'internal' && $isApp )
			{
				$data[ 'appid' ] = $appID;
			}


			if ( $insertafter )
			{
				/**
				 * @todo Verschieben der alten um 10 nach hinten
				 */
				$order = $this->db->query('SELECT MAX(ordering) AS ordering FROM %tp%page WHERE breadcrumb = 1 AND id = ' . (int)$insertafter)->fetch();
			}


			$str = $this->db->compile_db_insert_string(array (
			                                                 'title'         => $data[ 'title' ],
			                                                 'link'          => $data[ 'link' ],
			                                                 'controller'    => $data[ 'controller' ],
			                                                 'action'        => $data[ 'action' ],
			                                                 'type'          => $data[ 'type' ],
			                                                 'alias'         => (!$skipAlias ?
					                                                 $this->Modrewrite->getAlias() : ''),
			                                                 'suffix'        => (!$skipAlias ?
					                                                 $this->Modrewrite->getSuffix() : ''),
			                                                 'appid'         => (int)$data[ 'appid' ],
			                                                 'appcontroller' => $data[ 'appcontroller' ],
			                                                 'appalias'      => $data[ 'appalias' ],
			                                                 'contentid'     => (int)$data[ 'contentid' ],
			                                                 'parentid'      => (int)HTTP::input('parentid') ?
					                                                 (int)HTTP::input('parentid') : 1,
			                                                 'mgroups'       => ($mgroups !== null ? $mgroups : 0),
			                                                 'lft'           => 1,
			                                                 'rgt'           => 1,
			                                                 'ordering'      => $order[ 'ordering' ] + 10,
			                                                 'contentid'     => (int)$data[ 'contentid' ],
			                                                 'domainname'    => HTTP::input('domainname'),
			                                                 'mpublished'    => (int)$documentmeta[ 'published' ],
			                                                 'mpublishon'    => ((int)$on > 5000 ? $on : 0),
			                                                 'mpublishoff'   => ((int)$off > 5000 ? $on : 0),
			                                                 'breadcrumb'    => 1,
			                                                 'pageid'        => PAGEID,
			                                                 'cssclass'      => HTTP::input('cssclass'),
			                                           ));

			$sql = "INSERT %tp%page ({$str['FIELD_NAMES']}) VALUES({$str['FIELD_VALUES']})";
			$this->db->query($sql);

			// get the new ID of menu item
			$newid = $this->db->insert_id();


			//$this->Pagemeta->saveData($newid);

			Library::log("Add Menuitem " . HTTP::input('name') . ".");

			Cache::delete('fe_menucache', 'data');
			Cache::delete('ordered_menu', 'data');

			// $this->doReorderAll();
			//  Page::updateTree(1, false);

			if ( $data[ 'type' ] == 'rootpage' )
			{
				$this->db->query('UPDATE %tp%page SET pageid = ? WHERE id = ?', $newid, $newid);
			}


			if ( !IS_AJAX )
			{
				header("Location: admin.php?adm=menues&action=index");
				exit;
			}
			else
			{
				echo Library::json(array (
				                         'success' => true,
				                         'msg'     => trans("Menüpunkt wurde erfolgreich gespeichert"),
				                         'newid'   => $newid,
				                         'alias'   => $aliasCheck[ 'alias' ]
				                   ));
				exit;
			}
		}
		else
		{
			if ( !$id )
			{
				Error::raise('Error Invalid Menu ID!');
			}

			if ( !$load_menu[ 'id' ] )
			{
				Library::sendJson(false, trans("Error Invalid Menu ID!"));
			}

			if ( $load_menu[ 'id' ] == (int)HTTP::input('parentid') )
			{
				Library::sendJson(false, trans("Menüpunkt kann sich nicht selbst als Übergeordneten Menüpunkt haben!"));
			}


			$coreData = array (
				'pageid'        => PAGEID,
				'appid'         => (int)$data[ 'appid' ],
				'appcontroller' => $data[ 'appcontroller' ],
				'appalias'      => $data[ 'appalias' ],
				'link'          => $data[ 'link' ],
				'action'        => $data[ 'action' ],
				'type'          => $data[ 'type' ],
				'cssclass'      => HTTP::input('cssclass'),
				'contentid'     => $load_menu[ 'contentid' ] ? $load_menu[ 'contentid' ] : (int)$data[ 'contentid' ],
				'parentid'      => (int)HTTP::input('parentid') ? (int)HTTP::input('parentid') : 1,
				'mgroups'       => ($mgroups !== null ? $mgroups : $load_menu[ 'mgroups' ]),
				'contentid'     => (int)$data[ 'contentid' ],
				'domainname'    => HTTP::input('domainname'),
				'mpublished'    => (int)$documentmeta[ 'published' ],
				'mpublishon'    => ((int)$on > 5000 ? $on : 0),
				'mpublishoff'   => ((int)$off > 5000 ? $on : 0),
				'breadcrumb'    => 1,
			);


			$transData = array (
				'controller' => $data[ 'controller' ],
				'title'      => $data[ 'title' ]
			);


			$newid = $model->saveTranslation($id, $data);


			echo Library::json(array (
			                         'success' => true,
			                         'msg'     => trans("Menüpunkt wurde erfolgreich aktualisiert"),
			                         'newid'   => $newid,
			                         'alias'   => $aliasRegistry->getAlias()
			                   ));
			exit;


			$str = $this->db->compile_db_update_string(array (
			                                                 'title'         => $data[ 'title' ],
			                                                 'link'          => $data[ 'link' ],
			                                                 'controller'    => $data[ 'controller' ],
			                                                 'action'        => $data[ 'action' ],
			                                                 'type'          => $data[ 'type' ],
			                                                 'alias'         => (!$skipAlias ?
					                                                 $this->Modrewrite->getAlias() : ''),
			                                                 'suffix'        => (!$skipAlias ?
					                                                 $this->Modrewrite->getSuffix() : ''),
			                                                 'appid'         => (int)$data[ 'appid' ],
			                                                 'appcontroller' => $data[ 'appcontroller' ],
			                                                 'appalias'      => $data[ 'appalias' ],
			                                                 'contentid'     => $load_menu[ 'contentid' ] ?
					                                                 $load_menu[ 'contentid' ] :
					                                                 (int)$data[ 'contentid' ],
			                                                 'parentid'      => (int)HTTP::input('parentid') ?
					                                                 (int)HTTP::input('parentid') : 1,
			                                                 'mgroups'       => ($mgroups !== null ? $mgroups :
					                                                 $load_menu[ 'mgroups' ]),
			                                                 'contentid'     => (int)$data[ 'contentid' ],
			                                                 'domainname'    => HTTP::input('domainname'),
			                                                 'mpublished'    => (int)$documentmeta[ 'published' ],
			                                                 'mpublishon'    => ((int)$on > 5000 ? $on : 0),
			                                                 'mpublishoff'   => ((int)$off > 5000 ? $on : 0),
			                                                 'breadcrumb'    => 1,
			                                           ));

			$sql = "UPDATE %tp%page SET $str WHERE id={$id}";
			$this->db->query($sql);


			//$this->Pagemeta->saveData($id);

			Library::log("Edit Menuitem " . $data[ 'title' ] . " (ID:{$id}).");

			//  $this->doReorderAll();
			//  Page::updateTree(1, false);

			Cache::delete('fe_menucache', 'data');
			Cache::delete('ordered_menu', 'data');

			if ( $data[ 'type' ] == 'rootpage' )
			{
				$this->db->query('UPDATE %tp%page SET pageid = ? WHERE id = ?', $id, $id);
			}

			if ( !IS_AJAX )
			{
				header("Location: admin.php?adm=menues&action=index");
				exit;
			}
			else
			{

				echo Library::json(array (
				                         'success' => true,
				                         'msg'     => trans("Menüpunkt wurde erfolgreich aktualisiert"),
				                         'newid'   => $id,
				                         'alias'   => $aliasCheck[ 'alias' ]
				                   ));
				exit;
			}
		}
	}

}
