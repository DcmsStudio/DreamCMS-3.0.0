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
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Mysql.php
 */
class Menues_Model_Mysql extends Model
{

	/**
	 *
	 */
	public function __construct ()
	{

		parent::__construct();
	}

	/**
	 * Get all menues
	 *
	 * @return array
	 */
	public function getMenus ()
	{

		return $this->db->query('SELECT * FROM %tp%navi ORDER BY title ASC')->fetchAll();
	}

	/**
	 * Get a menu by the ID
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getMenuByID ( $id = 0 )
	{

		$extra = '';
		if ( $this->getApplication()->getMode() === Application::FRONTEND_MODE )
		{
			$extra = ' AND published = 1';
		}

		return $this->db->query('SELECT * FROM %tp%navi WHERE id = ?' . $extra, $id)->fetch();
	}

	/**
	 * Get a menu by the Template Key
	 *
	 * @param string $key
	 * @return array
	 */
	public function getMenuByTplkey ( $key )
	{

		$extra = '';
		if ( $this->getApplication()->getMode() === Application::FRONTEND_MODE )
		{
			$extra = ' AND published = 1';
		}

		return $this->db->query('SELECT * FROM %tp%navi WHERE templatekey = ?' . $extra . ' GROUP BY id', $key)->fetch();
	}

	/**
	 * Get a menu by the ID
	 *
	 * @param int $ids
	 * @internal param int $id
	 * @return array
	 */
	public function getMenuItemsByMenuID ( $ids = 0 )
	{

		$idsclean = Library::unempty(explode(',', $ids));
		$_ids     = implode(',', $idsclean);
		$_ids     = ( $_ids ? $_ids : 0 );

		$extra = '';
		if ( $this->getApplication()->getMode() === Application::FRONTEND_MODE )
		{
			$extra = ' (FIND_IN_SET(' . User::getGroupId() . ', i.usergroups) OR FIND_IN_SET(0, i.usergroups)) AND ';
		}

		return $this->db->query('SELECT * FROM %tp%navi_items AS i
                                 LEFT JOIN %tp%navi_items_trans AS it ON(it.itemid=i.itemid) 
                                 WHERE ' . $extra . ' i.itemid IN(' . $_ids . ') 
                                 GROUP BY i.itemid 
                                 ORDER BY i.ordering ASC')->fetchAll();
	}

	/**
	 * Get a menu by the ID
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getMenuItemByID ( $id = 0 )
	{

		$extra = '';
		if ( $this->getApplication()->getMode() === Application::FRONTEND_MODE )
		{
			$extra = ' (FIND_IN_SET(' . User::getGroupId() . ', i.usergroups) OR FIND_IN_SET(0, i.usergroups)) AND ';
		}

		return $this->db->query('SELECT * FROM %tp%navi_items AS i
                                 LEFT JOIN %tp%navi_items_trans AS it ON(it.itemid=i.itemid) 
                                 WHERE ' . $extra . ' i.itemid = ? 
                                 GROUP BY i.itemid 
                                 ORDER BY i.ordering ASC', $id)->fetch();
	}

	/**
	 *
	 * @param array $data
	 * @param array $allIds
	 * @param array $cacheMenuItems
	 */
	private function prepareMenuData ( &$data, $allIds, $selectedID = 0 )
	{

		$controller = strtolower(CONTROLLER);
		$action     = strtolower(ACTION);
		$activeSet  = false;

		$this->load('Router');

		$systemDefaultController = strtolower($this->Router->getDefaultController());
		$systemDefaultAction     = strtolower($this->Router->getDefaultAction());

		$docname = strtolower(DOCUMENT_NAME . '.' . DOCUMENT_EXTENSION);

		$frontpageDoc = '';
		$frontpage    = Settings::get('frontpage', false);

		if ( isset( $GLOBALS[ 'IS_FRONTPAGE' ] ) && $frontpage )
		{
			$frontpageSegments = explode('/', $frontpage);
			$frontpageDoc      = array_pop($frontpageSegments);

			if ( strpos($frontpageDoc, '.') === false )
			{
				$frontpageDoc = '';
			}
		}
		else {
			$frontpage = false;
		}

		$isPlugin = defined('PLUGIN');


		foreach ( $data as $idx => $rs )
		{
			// remove this item if parent is not visible
			if ( $rs[ 'parentid' ] && !in_array($rs[ 'parentid' ], $allIds) )
			{
				unset( $data[ $idx ] );
				continue;
			}

			$skipAlias = false;
			$url       = $rs[ 'controller' ] === 'plugin' ? '' : $rs[ 'controller' ];

			if ( $systemDefaultController === $url )
			{
				$url       = '';
				$skipAlias = true;
			}


			if ( $rs[ 'action' ] !== $systemDefaultAction )
			{
				$url .= '/' . $rs[ 'action' ];
			}

			$doc = '';

			if ( $rs[ 'alias' ] && !$skipAlias )
			{
				$url .= '/';
				$doc = $rs[ 'alias' ];

				if ( $rs[ 'suffix' ] )
				{
					$doc .= '.' . $rs[ 'suffix' ];
				}
				else
				{
					$doc .= '.' . Settings::get('mod_rewrite_suffix', 'html');
				}
				$rs[ 'docname' ] = $doc;


				$url .= $doc;
			}


			// is a external redirect ???
			if ( $rs[ 'type' ] === 'url' && empty( $rs[ 'url' ] ) )
			{
				$url = false;
			}
			else if ( $rs[ 'type' ] === 'url' && !empty( $rs[ 'url' ] ) )
			{
				if ( Validation::isValidUrl($rs[ 'url' ]) )
				{
					$url = $rs[ 'url' ];
				}
				else
				{
					unset( $data[ $idx ] );
				}
			}

			if ( !$frontpage && !$activeSet && ( $docname && $docname == strtolower($doc) ) || (!$frontpage && REQUEST == '/' . $url) )
			{
				$data[ $idx ][ 'active' ] = true;
				$activeSet                = true;
			}

			if ( !$frontpage && !$activeSet && $isPlugin )
			{
				if ( $controller == strtolower($rs[ 'controller' ]) && strtolower($rs[ 'action' ]) == strtolower(PLUGIN) && $rs[ 'type' ] == 'plugin' )
				{
					$data[ $idx ][ 'active' ] = true;
					$activeSet                = true;
				}
				elseif ($rs[ 'type' ] == 'plugin' && $isPlugin && strtolower(PLUGIN) == strtolower($rs[ 'action' ]) )
				{
					$data[ $idx ][ 'active' ] = true;
					$activeSet                = true;
				}
			}
			else
			{

				if (!$frontpage && $controller === $rs[ 'controller' ] )
				{

					if ( !$activeSet && $selectedID && $controller === $rs[ 'controller' ] && $selectedID === $rs[ 'contentid' ] )
					{
						$data[ $idx ][ 'active' ] = true;
						$activeSet                = true;
					}

					if ( !$activeSet && ( !$action || $rs[ 'action' ] === $action ) && $doc && $docname && $docname == strtolower($doc) )
					{
						$data[ $idx ][ 'active' ] = true;
						$activeSet                = true;
					}
					elseif ( !$activeSet && $action && $rs[ 'action' ] === $action && !$docname )
					{
						$data[ $idx ][ 'active' ] = true;
						$activeSet                = true;
					}

					if ( !$activeSet )
					{
						$cacheMenuItems[ $idx ][ 'active' ] = true;
						$activeSet                          = true;
					}

				}

			}


			if ( !$frontpage && !$activeSet && $controller == strtolower($rs[ 'controller' ]) && strtolower($rs[ 'action' ]) == strtolower(PLUGIN) && $docname && $docname == strtolower($doc) )
			{
				$data[ $idx ][ 'active' ] = true;
				$activeSet                = true;
			}
			/*
						elseif ( !$activeSet && $controller == strtolower($rs[ 'controller' ]) && $rs[ 'action' ] === $action && $docname && $docname == strtolower($doc) )
						{
							$data[ $idx ][ 'active' ] = true;
							$activeSet                = true;
						}*/
			if ( !$frontpage && !$activeSet && $rs[ 'type' ] == 'plugin' && $isPlugin && strtolower(PLUGIN) == strtolower($rs[ 'action' ]) )
			{
				$data[ $idx ][ 'active' ] = true;
				$activeSet                = true;
			}


			$data[ $idx ][ 'message' ] = ( $data[ $idx ][ 'message' ] !== '' ? Content::tinyMCECoreTags($data[ $idx ][ 'message' ]) : '' );
			$data[ $idx ][ 'url' ]     = $url;
		}


		if ( !$activeSet )
		{
			foreach ( $data as $idx => $rs )
			{

				if ( $frontpage && $frontpageDoc )
				{
					if ( isset( $rs[ 'docname' ] ) && strtolower($frontpageDoc) == strtolower($rs[ 'docname' ]) )
					{
						$data[ $idx ][ 'active' ] = true;
						$activeSet                = true;
					}
				}

				if ( !$activeSet && $systemDefaultController == strtolower($rs[ 'controller' ]) && $rs[ 'action' ] === $systemDefaultAction )
				{
					$data[ $idx ][ 'active' ] = true;
					$activeSet                = true;
				}
			}
		}
	}

	/**
	 *
	 */
	public function getMenu ( $forSitemap = false )
	{

		if ( $forSitemap && !$this->getParam('key', null) )
		{
			$this->load('Env');
			$menues = $this->getMenus();

			$this->load('Document');
			$selectedID = $this->Document->getDocumentID();

			foreach ( $menues as $r )
			{
				$this->setParam('key', $r[ 'templatekey' ]);

				if ( !$this->getParam('key', null) )
				{
					continue;
				}

				$cacheName = $this->getParam('key') . '-' . User::getGroupId();


				$cacheMenu      = Cache::get($cacheName . '-menu', 'data/menu');
				$cacheMenuItems = Cache::get($cacheName . '-items', 'data/menu');

				if ( !$cacheMenu || !$cacheMenuItems )
				{
					$cacheMenu = $this->getMenuByTplkey($this->getParam('key'));
					$this->db->free();


					Cache::write($cacheName . '-menu', $cacheMenu, 'data/menu');


					$cacheMenuItems = $this->getMenuItemsByMenuID($cacheMenu[ 'menuitems' ]);

					$this->db->free();
					Cache::write($cacheName . '-items', $cacheMenuItems, 'data/menu');
				}

				$allIds = array ();
				foreach ( $cacheMenuItems as $rs )
				{
					$allIds[ ] = $rs[ 'itemid' ];
				}

				$this->prepareMenuData($cacheMenuItems, $allIds, $selectedID);


				foreach ( $cacheMenuItems as $idx => $rs )
				{
					if ( $rs[ 'type' ] == 'megamenu' || $rs[ 'type' ] == 'spacer' || $rs[ 'type' ] == 'folder' || $rs[ 'message' ] == '-' )
					{
						unset( $cacheMenuItems[ $idx ] );
						continue;
					}
				}
			}

			return $cacheMenuItems;
		}


		if ( !$this->getParam('key', null) )
		{
			throw new BaseException( trans('Bitte den Key des Menüs übergeben!') );
		}

		$this->load('Env');
		$cacheName = $this->getParam('key') . '-' . User::getGroupId();


		$this->load('Document');
		$selectedID = $this->Document->getDocumentID();


		$cacheMenu      = Cache::get($cacheName . '-menu', 'data/menu');
		$cacheMenuItems = Cache::get($cacheName . '-items', 'data/menu');

		if ( !$cacheMenu || !$cacheMenuItems )
		{
			$cacheMenu = $this->getMenuByTplkey($this->getParam('key'));
			$this->db->free();


			Cache::write($cacheName . '-menu', $cacheMenu, 'data/menu');


			$cacheMenuItems = $this->getMenuItemsByMenuID($cacheMenu[ 'menuitems' ]);

			$this->db->free();
			Cache::write($cacheName . '-items', $cacheMenuItems, 'data/menu');
		}

		$allIds = array ();
		foreach ( $cacheMenuItems as $rs )
		{
			$allIds[ ] = $rs[ 'itemid' ];
		}

		$this->prepareMenuData($cacheMenuItems, $allIds, $selectedID);

	#	print_r($cacheMenuItems);
	#	exit;


		$data[ 'menu' ]            = $cacheMenu;
		$data[ 'menu' ][ 'items' ] = $cacheMenuItems;
		#print_r($data);
		#    unset( $cacheMenu, $cacheMenuItems );

		$tpl = new Template();
        $tpl->isProvider = true;

		return $tpl->process('menu', $data, false);
	}

	/**
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getMenuTranslation ( $id )
	{

		return $this->db->query('SELECT * FROM %tp%page_trans WHERE id = ? AND `lang`= ?', $id, CONTENT_TRANS)->fetch();
	}

	/**
	 *
	 * @param integer $id
	 * @param array   $data
	 * @return int
	 */
	public function saveMenu ( $id = 0, $data = array () )
	{

		$this->setTable('navi_items');


		$coredata = array (
			'pageid'     => PAGEID,
			'controller' => $data[ 'controller' ],
			'action'     => $data[ 'action' ],
			'type'       => $data[ 'type' ],
			'parentid'   => (int)$data[ 'parentid' ],
			'usergroups' => (string)$data[ 'usergroups' ],
			'rollback'   => 0,
			'contentid'  => (int)$data[ 'contentid' ],
			'cssclass'   => $data[ 'cssclass' ],
		);


		if ( $data[ 'contentid' ] )
		{
			$coredata[ 'contentid' ] = $data[ 'contentid' ];
		}

		if ( !is_array(HTTP::input('documentmeta')) )
		{
			$coredata[ 'published' ] = $data[ 'published' ];
		}

		$transData[ 'controller' ] = $data[ 'controller' ];
		$transData[ 'action' ]     = $data[ 'action' ];
		$transData[ 'message' ]    = $data[ 'message' ];
		$transData[ 'title' ]      = $data[ 'title' ];


		if ( $id )
		{
			unset( $coredata[ 'created_by' ], $coredata[ 'created' ] );

			$transData[ 'data' ]   = $data;
			$transData[ 'alias' ]  = (string)$data[ 'alias' ];
			$transData[ 'suffix' ] = (string)$data[ 'suffix' ];


			$transData[ 'id' ] = $this->save($id, $coredata, $transData);
		}
		else
		{
			#$coredata['modifed'] = 0;
			#$coredata['modifed_by'] = 0;

			$transData[ 'data' ]   = $data;
			$transData[ 'alias' ]  = (string)$data[ 'alias' ];
			$transData[ 'suffix' ] = (string)$data[ 'suffix' ];

			$transData[ 'id' ] = $this->save(0, $coredata, $transData);


			$menu = $this->getMenuByID($data[ 'menuid' ]);
			$r    = explode(',', $menu[ 'menuitems' ]);
			array_push($r, $transData[ 'id' ]);
			$menu[ 'menuitems' ] = implode(',', $r);


			$this->db->query('UPDATE %tp%navi SET menuitems = ? WHERE id = ?', $menu[ 'menuitems' ], $data[ 'menuid' ]);
		}


		return $transData[ 'id' ];
	}

	/**
	 * Update the menuitems parent ids and ordering
	 *
	 * @param integer $id
	 * @param array   $data
	 * @return boolean
	 */
	public function updateMenuOrdering ( $id, $data )
	{

		if ( count($data[ 'items' ]) )
		{

			$orderings = explode(',', $data[ 'ordering' ]);
			foreach ( $data[ 'items' ] as $idx => $r )
			{
				if ( isset( $r[ 'itemid' ] ) )
				{
					$this->db->query('UPDATE %tp%navi_items SET ordering = ?, parentid = ? WHERE itemid = ?', $idx + 1, (int)$r[ 'parentid' ], $r[ 'itemid' ]);
				}
			}
			$this->db->query('UPDATE %tp%navi SET menuitems = ? WHERE id = ?', $data[ 'menuitems' ], $data[ 'menuid' ]);

			$menu = $this->getMenuByID($data[ 'menuid' ]);
			Cache::delete($menu[ 'templatekey' ] . '*', 'data/menu');

			return true;
		}

		return false;
	}

	/**
	 * @param $menuid
	 * @param $itemid
	 */
	public function removeMenuitem ( $menuid, $itemid )
	{

		$menu = $this->getMenuByID($menuid);

		$this->db->query('DELETE FROM %tp%navi_items WHERE itemid = ?', $itemid);
		$this->db->query('DELETE FROM %tp%navi_items_trans WHERE itemid = ?', $itemid);

		$menuitems = array ();

		$items = explode(',', $menu[ 'menuitems' ]);
		foreach ( $items as $id )
		{
			if ( $itemid != $id && $id )
			{
				$menuitems[ ] = $id;
			}
		}


		$this->db->query('UPDATE %tp%navi SET menuitems = ? WHERE id = ?', implode(',', $menuitems), $menuid);
		Cache::delete($menu[ 'templatekey' ] . '*', 'data/menu');
	}

	/**
	 *
	 * @param integer $menuid
	 * @param array   $data
	 * @return integer/void
	 */
	public function createMenu ( $menuid = 0, $data = array () )
	{

		if ( $menuid )
		{
			$menu = $this->getMenuByID($menuid);
			Cache::delete($menu[ 'templatekey' ] . '*', 'data/menu');

			$this->db->query('UPDATE %tp%navi SET title = ?, templatekey = ?, published = ? WHERE id = ?', $data[ 'menutitle' ], $data[ 'templatekey' ], $data[ 'published' ], $menuid);
		}
		else
		{
			$this->db->query('INSERT INTO %tp%navi (title,templatekey,published,menuitems)
                VALUES(?,?,?,?)', $data[ 'menutitle' ], $data[ 'templatekey' ], $data[ 'published' ], '');


			return $this->db->insert_id();
		}
	}

	/**
	 *
	 * @param integer $menuid
	 * @return boolean
	 */
	public function removeMenu ( $menuid = 0 )
	{

		if ( $menuid )
		{
			$menu = $this->getMenuByID($menuid);

			$idsclean = Library::unempty(explode(',', $menu[ 'menuitems' ]));
			$_ids     = implode(',', $idsclean);
			$_ids     = ( $_ids ? $_ids : 0 );


			$this->db->query('DELETE FROM %tp%navi_items WHERE itemid IN(' . $_ids . ')');
			$this->db->query('DELETE FROM %tp%navi_items_trans WHERE itemid IN(' . $_ids . ')');
			$this->db->query('DELETE FROM %tp%navi WHERE id = ?', $menuid);

			Cache::delete($menu[ 'templatekey' ] . '*', 'data/menu');

			return true;
		}

		return false;
	}

	/**
	 * @return array
	 */
	private function getFrontentModule ()
	{

		static $tmpResult;

		if ( !is_array($tmpResult) )
		{

			$modtranslation = array ();
			require_once( DATA_PATH . 'system/frontend_modules.php' );

			$optionGroups = array ();
			$options      = array ();


			foreach ( $modtranslation as $ctl => $arr )
			{
				if ( $ctl != '_other' )
				{

					# $options[$ctl][] = array('title' => $modtranslation[$ctl][0]);
					# $modtranslation[$ctl][0] = null;
					$grouplabel = ( is_array($modtranslation[ $ctl ]) ? array_shift($modtranslation[ $ctl ]) : array () );
					foreach ( $modtranslation[ $ctl ] as $act => $labels )
					{
						if ( !isset( $optionGroups[ $ctl ] ) )
						{
							$options[ $ctl ][ ]   = array (
								'controller' => '',
								'action'     => '',
								'title'      => $grouplabel
							);
							$optionGroups[ $ctl ] = true;
						}

						$options[ $ctl ][ ] = array (
							'controller'     => $ctl,
							'action'         => $act,
							'title'          => $labels[ 0 ],
							'advanced_title' => ( isset( $labels[ 1 ] ) ? $labels[ 1 ] : '' )
						);
					}
				}
				else
				{
					$grouplabel = ( is_array($modtranslation[ $ctl ]) ? array_shift($modtranslation[ $ctl ]) : array () );
					foreach ( $modtranslation[ $ctl ] as $other => $row )
					{
						if ( isset( $row[ 0 ] ) )
						{
							$options[ $ctl ][ ] = array (
								'controller'     => $other,
								'action'         => $other,
								'title'          => $row[ 0 ],
								'advanced_title' => ( isset( $row[ 1 ] ) ? $row[ 1 ] : '' )
							);
						}
						else
						{
							foreach ( $row as $act => $labels )
							{
								if ( !isset( $optionGroups[ $ctl ] ) )
								{
									$options[ $ctl ][ ]   = array (
										'controller' => '',
										'action'     => '',
										'title'      => $grouplabel
									);
									$optionGroups[ $ctl ] = true;
								}

								if ( is_array($labels) )
								{
									$options[ $ctl ][ ] = array (
										'controller'     => $other,
										'action'         => $act,
										'title'          => $labels[ 0 ],
										'advanced_title' => ( isset( $labels[ 1 ] ) ? $labels[ 1 ] : '' )
									);
								}
							}
						}
					}
				}
			}

			$tmpResult = array ();
			foreach ( $options as $row )
			{
				foreach ( $row as $r )
				{
					$tmpResult[ ] = $r;
				}
			}
		}

		return $tmpResult;
	}

	/**
	 * Output is for Ajax Request
	 *
	 * @param array $data
	 * @return string Parsed Template
	 */
	public function loadMenuType ( $data )
	{

		$source = '';

		switch ( $data[ 'type' ] )
		{

			case 'rootpage':
				$source = 'menu/type_rootpage';
				break;

			case 'folder':
				$source = 'menu/type_folder';
				break;

			case 'spacer':
				$source = 'menu/type_spacer';
				break;

			case 'megamenu':
				$source = 'menu/type_megamenu';
				break;

			case 'plugin':
				$inst    = Plugin::getInstalledPlugins();
				$plugins = array ();
				foreach ( $inst as $r )
				{

					$r[ 'alias' ]  = strtolower(Library::suggest($r[ 'name' ], 'alias'));
					$r[ 'suffix' ] = Settings::get('mod_rewrite_suffix', 'html');

					$plugins[ ] = array (
						'title'      => $r[ 'name' ],
						'controller' => 'plugin',
						'action'     => $r[ 'key' ],
						'alias'      => $r[ 'alias' ],
						'suffix'     => $r[ 'suffix' ],
						'plugin'     => $r[ 'key' ],
						'pagelink'   => 'plugin/' . $r[ 'key' ] . '/' . $r[ 'alias' ] . '.' . $r[ 'suffix' ]
					);
				}

				$data[ 'plugins' ] = $plugins;

				$source = 'menu/type_plugin';
				break;

			case 'url':
			default:
				$source = 'menu/type_url';
				break;


			case 'appcat':
				// Load all App Rules


				$a    = Application::getInstance();
				$apps = $a->getApps();
				$cats = array ();
				foreach ( $apps as $r )
				{
					$rule                  = $a->getRouterMap($r[ 'apptype' ], $r[ 'appid' ]);
					$appalias              = $a->getAppMapAlias();
					$_appalias             = $appalias[ $r[ 'appid' ] ];
					$cats[ $r[ 'appid' ] ] = $this->getAppCats($r, $_appalias);
				}

				$app_rules = array ();
				foreach ( $apps as $r )
				{
					$rule         = $a->getRouterMap($r[ 'apptype' ], $r[ 'appid' ]);
					$appalias     = $a->getAppMapAlias();
					$_appalias    = $appalias[ $r[ 'appid' ] ];
					$app_rules[ ] = array (
						'cats'       => $cats[ $r[ 'appid' ] ],
						'controller' => 'apps',
						'action'     => 'category',
						'apptype'    => $r[ 'apptype' ],
						'appid'      => $r[ 'appid' ],
						'title'      => $r[ 'title' ] . ' (Kategorien)'
					);
				}

				$data[ 'apps' ] = $app_rules;

				$source = 'menu/type_appcat';
				break;

			case 'internal':

				$tmp    = array ();
				$tmp[ ] = array (
					'empty' => true,
					'title' => '---------------------'
				);


				/*
				  $all = $this->db->query('SELECT * FROM %tp%actions WHERE isbackend = 0 ORDER BY controller, `action` ASC')->fetchAll();

				  $cache = array();
				  foreach ($all as $r)
				  {
				  $cache[$r['controller']][] = $r;
				  }
				 */


				$tmpResult = $this->getFrontentModule();

				foreach ( $tmpResult as $r )
				{
					$pagelink = '';
					$rule     = '';

					if ( $r[ 'controller' ] )
					{
						if ( $r[ 'action' ] == 'index' )
						{
							$r[ 'action' ] = '';
							$rule          = '/' . $r[ 'controller' ];
						}
						else
						{
							$rule = '/' . $r[ 'controller' ] . ( $r[ 'action' ] ? '/' . $r[ 'action' ] : '' );
						}
					}

					if ( $r[ 'controller' ] && $data[ 'controller' ] == $r[ 'controller' ] )
					{
						$pagelink = '/' . $r[ 'controller' ];
						if ( $r[ 'action' ] == $data[ 'action' ] && $data[ 'action' ] != 'index' )
						{
							$pagelink .= '/' . $r[ 'action' ];
						}
					}

					$tmp[ ] = array (
						'rule'           => $rule,
						'title'          => $r[ 'title' ],
						'advanced_title' => $r[ 'advanced_title' ],
						'pagelink'       => $pagelink,
						'controller'     => $r[ 'controller' ],
						'action'         => $r[ 'action' ]
					);
				}


				if ( HTTP::input('help') )
				{
					echo Library::json(array (
					                         'success' => true,
					                         'modules' => $tmp
					                   ));
					exit;
				}

				$data[ 'componentes' ] = $tmp;
				$source                = 'menu/type_internal';
				break;

			case 'page':


				$source = 'menu/type_page';
				break;

			case 'newscat':
				$data[ 'categories' ] = $this->getNewsCats();
				$source               = 'menu/type_newscat';
				break;

			case 'articlecat':
				$data[ 'categories' ] = $this->getArticleCats();
				$source               = 'menu/type_articlecat';
				break;
		}

		$this->load('Template');

		return $this->Template->process($source, $data);
	}

	/**
	 * Get all static pages
	 *
	 * @return array
	 */
	public function getPages ()
	{

		$transq1 = $this->buildTransWhere('pages', 'p.id', 'pt');

		$sql    = "SELECT p.*, pt.* FROM %tp%pages AS p
                LEFT JOIN %tp%pages_trans AS pt ON (pt.id=p.id) 
                WHERE p.pageid = ? AND " . $transq1 . "  ORDER BY p.parentid, pt.title";
		$result = $this->db->query($sql, PAGEID)->fetchAll();
		$pages  = array ();
		foreach ( $result as $row )
		{
			if ( empty( $row[ 'alias' ] ) )
			{
				$row[ 'alias' ]  = strtolower(Library::suggest($row[ 'title' ], 'alias'));
				$row[ 'suffix' ] = Settings::get('mod_rewrite_suffix', 'html');
			}


			$row[ 'suffix' ] = empty( $row[ 'suffix' ] ) ? Settings::get('mod_rewrite_suffix', 'html') : $row[ 'suffix' ];


			$pages[ $row[ 'parentid' ] ][ $row[ 'id' ] ] = $row;
		}

		$options = array ();
		$this->createPages($options, $pages, 0, 1);

		return $options;
	}

	/**
	 * Helper for function getPages
	 * build a array of all pages (selection tree)
	 *
	 * @param     $options
	 * @param     $pages
	 * @param int $parentid
	 * @param int $depth
	 * @return void
	 */
	private function createPages ( &$options, &$pages, $parentid = 0, $depth = 1 )
	{

		if ( !isset( $pages[ $parentid ] ) )
		{
			return;
		}

		while ( list( $key1, $rows ) = each($pages[ $parentid ]) )
		{

			$rows[ 'basetitle' ] = $rows[ 'title' ];


			if ( strlen($rows[ 'title' ]) > 60 )
			{
				$rows[ 'title' ] = substr($rows[ 'title' ], 0, 60) . '...';
			}
			$titel_of_board     = ( ( $depth > 1 ) ? str_repeat("&nbsp;&nbsp;&#0124;--", $depth - 1) : '' ) . " " . $rows[ 'title' ] . ' (' . $rows[ 'alias' ] . '.' . $rows[ 'suffix' ] . ')';
			$rows[ 'pagelink' ] = '/page/' . $rows[ 'alias' ] . '.' . $rows[ 'suffix' ];
			$rows[ 'title' ]    = $titel_of_board;


			$options[ ] = $rows;
			$this->createPages($options, $pages, $rows[ 'id' ], $depth + 1);
		}
	}

	/**
	 * get application categories from a appid
	 *
	 * @param array $app
	 * @return array
	 */
	public function getAppCats ( $app )
	{

		$list = array ();
		$list = Applicationcats::getInstance()->getCategorieTree($app[ 'appid' ], 0, true);

		return $list;
	}

	/**
	 * get all news categories
	 *
	 * @return array
	 */
	public function getNewsCats ()
	{

		$result = Model::getModelInstance('news')->getCategories();

		$list = array ();
		foreach ( $result as $idx => $row )
		{
			$row[ 'catlink' ] = '/news/category/' . $row[ 'id' ];

			if ( empty( $row[ 'alias' ] ) )
			{
				$row[ 'alias' ]  = Library::suggest($row[ 'title' ], 'alias');
				$row[ 'suffix' ] = Settings::get('mod_rewrite_suffix', 'html');
				$row[ 'catlink' ] .= '/' . $row[ 'alias' ] . '.' . $row[ 'suffix' ];
			}
			else
			{
				$row[ 'catlink' ] .= '/' . $row[ 'alias' ] . '.' . $row[ 'suffix' ];
			}


			$list[ ] = $row;
		}

		return $list;
	}

	/**
	 *
	 * @param integer $id
	 * @param bool    $table
	 * @internal param bool $useCat
	 * @return bool
	 */
	public function hasTranslation ( $id = 0, $table = false )
	{

		$trans = $this->db->query('SELECT id FROM %tp%page_trans WHERE id = ? AND lang = ?', $id, CONTENT_TRANS)->fetch();

		if ( $trans[ 'id' ] )
		{
			return true;
		}

		return false;
	}

	/**
	 *
	 * @param int $menu_id
	 * @return array
	 */
	public function _load_menu ( $menu_id = 0 )
	{

		$transq1 = $this->buildTransWhere('page', 'p.id', 'pt');

		return $this->db->query('
            SELECT * FROM %tp%page AS p
            LEFT JOIN %tp%page_trans AS pt ON (pt.id=p.id) 
            WHERE p.id = ? AND ' . $transq1, $menu_id)->fetch();
	}

	/**
	 * will rollback the temporary translated content
	 *
	 * @param integer $id
	 * @param bool    $table
	 * @return type
	 */
	function rollbackTranslation ( $id, $table = false )
	{

		$table = 'page';
		$this->db->query('DELETE FROM %tp%' . $table . '_trans WHERE `rollback` = 1 AND id = ? AND lang = ?', $id, CONTENT_TRANS);
	}

	/**
	 * Copy the original translation to other translation
	 *
	 * @param integer $id
	 * @param bool    $table
     * @return bool
	 */
	public function copyOriginalTranslation ( $id, $table = false )
	{

		$table = 'page';

		$r = $this->db->query('SELECT lang FROM %tp%' . $table . '_trans WHERE id = ? AND iscorelang = 1', $id)->fetch();
		if ( CONTENT_TRANS == $r[ 'lang' ] )
		{
			return false;
		}

		$trans                 = $this->db->query('SELECT t.* FROM %tp%' . $table . '_trans AS t WHERE t.id = ? AND t.lang = ?', $id, $r[ 'lang' ])->fetch();
		$trans[ 'lang' ]       = CONTENT_TRANS;
		$trans[ 'rollback' ]   = 1;
		$trans[ 'iscorelang' ] = 0;

		$f      = array ();
		$fields = array ();
		$values = array ();
		foreach ( $trans as $key => $value )
		{
			$fields[ ] = $key;
			$f[ ]      = '?';
			$values[ ] = $value;
		}

		$this->db->query('INSERT INTO %tp%' . $table . '_trans (' . implode(',', $fields) . ') VALUES(' . implode(',', $f) . ')', $values);
        return true;
	}

	/**
	 *
	 * @param integer $id
	 * @param array   $data
	 * @return int
	 */
	public function saveTranslation ( $id = 0, $data = array () )
	{

		$this->setTable('page');

		$access           = ( is_array($data[ 'groups' ]) ? $data[ 'groups' ] : array (
			0
		) );
		$data[ 'groups' ] = implode(',', $access);

		$access            = ( is_array($data[ 'mgroups' ]) ? $data[ 'mgroups' ] : array (
			0
		) );
		$data[ 'mgroups' ] = implode(',', $access);

		$coredata = array (
			'breadcrumb'    => 1,
			'pageid'        => PAGEID,
			'appid'         => (int)$data[ 'appid' ],
			'appcontroller' => $data[ 'appcontroller' ],
			'appalias'      => $data[ 'appalias' ],
			'link'          => $data[ 'link' ],
			'controller'    => $data[ 'controller' ],
			'action'        => $data[ 'action' ],
			'type'          => $data[ 'type' ],
			'mgroups'       => (string)$data[ 'mgroups' ],
			'groups'        => (string)$data[ 'groups' ], //  'hits' => 0,
			'created_by'    => (int)User::getUserId(),
			'created'       => time(),
			'modifed_by'    => (int)User::getUserId(),
			'modifed'       => time(),
			'rollback'      => 0,
			'contentid'     => (int)$data[ 'contentid' ],
			'domainname'    => $data[ 'domainname' ],
		);


		if ( $data[ 'contentid' ] )
		{
			$coredata[ 'contentid' ] = $data[ 'contentid' ];
		}

		if ( !is_array(HTTP::input('documentmeta')) )
		{
			$coredata[ 'published' ] = $data[ 'published' ];
		}

		$transData[ 'controller' ] = $data[ 'controller' ];
		$transData[ 'action' ]     = $data[ 'action' ];
		$transData[ 'title' ]      = $data[ 'title' ];


		if ( $id )
		{
			unset( $coredata[ 'created_by' ], $coredata[ 'created' ] );

			$transData[ 'data' ]   = $data;
			$transData[ 'alias' ]  = $data[ 'alias' ];
			$transData[ 'suffix' ] = $data[ 'suffix' ];


			$transData[ 'id' ] = $this->save($id, $coredata, $transData);
		}
		else
		{
			$coredata[ 'modifed' ]    = 0;
			$coredata[ 'modifed_by' ] = 0;

			$transData[ 'data' ]   = $data;
			$transData[ 'alias' ]  = $data[ 'alias' ];
			$transData[ 'suffix' ] = $data[ 'suffix' ];

			$transData[ 'id' ] = $this->save(0, $coredata, $transData);
		}

		return $transData[ 'id' ];
	}

}

?>