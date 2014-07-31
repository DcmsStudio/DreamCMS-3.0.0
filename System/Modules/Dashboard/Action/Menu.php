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
 * @package      Dashboard
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Menu.php
 */
class Dashboard_Action_Menu extends Controller_Abstract
{

	/**
	 * @var null
	 */
	protected $menu = null;

	/**
	 * @var null
	 */
	protected $_menu = null;

	/**
	 * @var
	 */
	protected $parent_menu_array;

	/**
	 * @var string
	 */
	protected $_icon_type = 'png';

	/**
	 * @var string
	 */
	protected $menu_item_dir = 'pulldownmenu';

	/**
	 * @var string
	 */
	protected $icon_dir = 'core/admin/images/menuicons/32x32';

	public function execute ()
	{

		if ( $this->getApplication()->getMode() === Application::BACKEND_MODE )
		{
			$menu = Menu::getMenu();


			$menu = Library::unempty($menu);
			# print_r($menu);
			# exit;

			$output = new Output();
			$output->addHeader('Content-Type', 'application/javascript');
			$output->appendOutput('top.accOpen = \'' . $GLOBALS[ 'sidebar_pos' ] . '\'; top.menuItems = ' . json_encode($menu) . ";
        top.currentPageTitle = '" . Cookie::get('pageCurrentTitle') . "';
        top.currentPageIcon = '" . Cookie::get('pageCurrentIcon') . "';");

			$output->sendOutput();

			exit;


			foreach ( $menu as $arr )
			{
				foreach ( $arr[ 'items' ] as $row )
				{
					if ( is_array($row) )
					{
						foreach ( $row as $controller => $r )
						{

						}
					}
				}
			}


			$this->_menu = Cache::get('menu_user_' . User::getUserId() . '_' . GUI_LANGUAGE);


			# print_r( Menu::getMenu() );


			print_r(json_decode($this->_menu));
			exit;


			//if (HTTP::input('refresh'))
			//{
			Cache::delete('menu_user_' . User::getUserId() . '_' . GUI_LANGUAGE);
			//}

			$this->menu_item_dir = BACKEND_IMAGE_PATH . $this->menu_item_dir;
			$this->_menu         = Cache::get('menu_user_' . User::getUserId() . '_' . GUI_LANGUAGE);

			if ( is_null($this->_menu) )
			{
				$this->build();
				$this->_menu = Json::encode($this->_menu); //json_encode($this->_menu);
				Cache::write('menu_user_' . User::getUserId() . '_' . GUI_LANGUAGE, $this->_menu);
			}


			$trash    = $this->db->query('SELECT COUNT(trashid) AS total FROM %tp%trash')->fetch();
			$hasTrash = "top.hasTrash=" . ($trash[ 'total' ] ? 'true' :
					'false') . ";top.totalInTrash = " . $trash[ 'total' ] . ';';


			$output = new Output();
			$output->addHeader('Content-Type', 'application/javascript');
			$output->appendOutput('top.accOpen = \'' . $GLOBALS[ 'sidebar_pos' ] . '\'; top.menuItems = ' . $this->_menu . ";
        top.currentPageTitle = '" . Cookie::get('pageCurrentTitle') . "';
        top.currentPageIcon = '" . Cookie::get('pageCurrentIcon') . "';" . $hasTrash);

			$output->sendOutput();

			exit;
		}
	}

	private function loadMenu ()
	{

		// get a list of the menu items
		$result = $this->db->query('SELECT * FROM %tp%admin_nav WHERE published=1 AND plugin = 0 ORDER BY parentid, ordering')->fetchAll();

		// establish the hierarchy of the menu
		$children = array ();

		// first pass - collect children
		foreach ( $result as $v )
		{

			if ( $v[ 'parentid' ] > 0 )
			{

				$uc = preg_replace('/adm=([^&]*)/is', '$1', $v[ 'url' ]);

				$controller = ($v[ 'controller' ] ? $v[ 'controller' ] : ($uc ? $uc : null));
				if ( $controller )
				{
					$ua     = preg_replace('/action=([^&]*)/is', '$1', $v[ 'url' ]);
					$action = ($v[ 'action' ] ? $v[ 'action' ] : ($ua ? $ua : 'index'));

					$str = $controller . '/' . $action;

					$_modulRequireLogin = Action::requireLogin($str);
					$_modulRequirePerms = Action::requirePermission($str);


					if ( $_modulRequireLogin || $_modulRequirePerms )
					{
						if ( $_modulRequireLogin === true && !User::isLoggedIn() )
						{
							continue;
						}

						if ( $_modulRequirePerms === true && !Permission::hasControllerActionPerm($str) )
						{
							continue;
						}
					}
				}
			}


			$pt   = $v[ 'parentid' ];
			$list = @$children[ $pt ] ? $children[ $pt ] : array ();
			array_push($list, $v);
			$children[ $pt ] = $list;
		}

		/*
		  // Menüpunkte
		  $result = $this->db->query('SELECT * FROM %tp%admin_nav WHERE parentid>0 AND published=1 AND plugin = 0 GROUP BY title ORDER BY parentid,ordering ASC')->fetchAll();

		  $_plugs = array();
		  foreach ($result as $r)
		  {
		  $r['icon'] = $this->replace_icon_ext(( $r['icon'] != '' ? $r['icon'] : 'plugin.png'), $this->_icon_type);

		  if ($r['plugin'] > 0)
		  {
		  $r['icon'] = ( $r['icon'] ? $r['icon'] : 'plugin.png');
		  array_push($_plugs, $r);
		  }
		  }

		  unset($r);

		  // Menüpunkte der Plugins laden
		  if (!class_exists('DOMIT_Lite_Document', false))
		  {
		  require_once( EXTERNAL_PATH . 'domit/xml_domit_lite_parser.php' );
		  }


		  $sql = "SELECT pluginkey, id AS plugin_id FROM %tp%plugins";
		  $result = $this->db->query($sql)->fetchAll();

		  $all_plugins = array();
		  foreach ($result as $rs)
		  {
		  $all_plugins[$rs['plugin_id']] = $rs['pluginkey'];
		  }



		  foreach ($_plugs as $r)
		  {
		  if ($r['plugin'] == 0)
		  {
		  continue;
		  }

		  if ($r['parentid'] && $r['url'] != '')
		  {
		  continue;
		  }

		  if (isset($all_plugins[$r['plugin']]))
		  {
		  $plugin_path = MODULES_PATH . $all_plugins[$r['plugin']] . '/';

		  // XML Daten laden
		  $xmlDoc = new DOMIT_Lite_Document();
		  $xmlDoc->resolveErrors(true);

		  if ($xmlDoc->loadXML($plugin_path . 'xmldata/admin_menu.xml', true, true, true))
		  {
		  $element = & $xmlDoc->documentElement;

		  if ($element->getTagName() == 'cpxml')
		  {
		  if ($element = $xmlDoc->getElementsByPath('plugin', 1))
		  {
		  $this->_xmlElem = & $element;
		  }
		  }
		  }

		  if (is_object($this->_xmlElem))
		  {
		  $element = $this->_xmlElem;

		  foreach ($element->childNodes as $node)
		  {
		  $numChannels = count($node->childNodes);

		  if ($numChannels > 0)
		  {
		  $items = array();
		  $items['parentid'] = $r['id'];

		  foreach ($node->childNodes as $n)
		  {
		  $cur_tag = strtolower($n->getTagName());
		  $cur_val = $n->getText();

		  if ($cur_tag == 'url')
		  {
		  $start = '';
		  if (!preg_match('/^&/', $cur_val))
		  {
		  $start = '&';
		  }
		  $cur_val = "plugin.php?pl=" . $r['plugin'] . $start . $cur_val;
		  }
		  $items[$cur_tag] = $cur_val;
		  }

		  $pt = $items['parentid'];
		  $list = @$children[$pt] ? $children[$pt] : array();
		  array_push($list, $items);
		  $children[$pt] = $list;
		  }
		  }
		  }
		  }
		  unset($xmlDoc);
		  }
		 */
		// second pass - get an indent list of the items
		$this->parent_menu_array = array ();
		$this->parent_menu_array = $children;

		return;
	}

	/**
	 *
	 * @param string $icon
	 * @param string $to
	 * @return string
	 */
	private function _replaceIconExtension ( $icon, $to = 'gif' )
	{

		if ( $icon )
		{
			return str_ireplace('.gif', '.' . $to, $icon);
		}

		return $icon;
	}

	/**
	 * @return bool
	 */
	private function build ()
	{

		Hook::run('onBeforeGenerateDashboardMenu'); // {CONTEXT: dashboard, DESC: This event fires just before the Dashboard menu is generated.}

		$this->loadMenu();


		foreach ( $this->parent_menu_array[ 0 ] as $idx => $r )
		{

			if ( !empty($r[ 'controller' ]) && !Permission::hasPerm(false, $r[ 'controller' ]) )
			{
				#continue;
			}

			if ( !empty($r[ 'controller' ]) && !empty($r[ 'action' ]) )
			{

				if ( !User::isLoggedIn() && Action::requireLogin($r[ 'controller' ] . '/' . $r[ 'action' ]) )
				{
					#continue;
				}

				if ( Action::requirePermission($r[ 'controller' ] . '/' . $r[ 'action' ]) && !Permission::hasControllerActionPerm($r[ 'controller' ] . '/' . $r[ 'action' ]) )
				{
					# continue;
				}
			}


			$icon = 'spacer.gif';

			$menuicon = $this->_replaceIconExtension(($r[ 'icon' ] != '' ? $r[ 'icon' ] : ''), $this->_icon_type);

			if ( is_file(PUBLIC_PATH . $this->menu_item_dir . '/' . $menuicon) )
			{
				$icon = $menuicon;
			}

			/*
			  JS Menu Build (AJAX)
			 */

			if ( trim((string)$r[ 'item_function' ]) && $r[ 'parentid' ] == 0 )
			{
				//=========================================================
				// Interne CMS Function dieser Classe aufrufen
				//=========================================================
				$this->_menu[ 'menu-' . $idx ] = $this->$r[ 'item_function' ]($r);
			}
			else
			{
				if ( isset($this->parent_menu_array[ $r[ 'parentid' ] ]) )
				{
					$_arrs                         = array ();
					$this->_menu[ 'menu-' . $idx ] = array (
						'controller' => $r[ 'controller' ],
						'action'     => $r[ 'action' ],
						'name'       => 'menu-' . $idx,
						'label'      => $r[ 'title' ],
						'icon'       => $icon,
						'items'      => $this->get_parent_items_arr($_arrs, $idx, $r[ 'id' ]),
						'tip'        => $r[ 'description' ],
						'minwidth'   => $r[ 'minwidth' ],
						'minheight'  => $r[ 'minheight' ],
						'modal'      => $r[ 'modal' ],
						'position'   => $r[ 'position' ]
					);


					//$this->_menu['menu-'.$r['id'].'-'.$idx]['items'] = $this->get_parent_items_arr($_arrs, $idx, $r['id']);
				}
			}
		}


		//Cache::setCompress(true);

		return true;
	}

	/**
	 * @param array $_arrs
	 * @param int   $rootidx
	 * @param int   $parent_id
	 * @return array
	 */
	function get_parent_items_arr ( $_arrs = array (), $rootidx = 0, $parent_id = 0 )
	{

		if ( !isset($this->parent_menu_array[ $parent_id ]) || !count($this->parent_menu_array[ $parent_id ]) )
		{
			return $_arrs;
		}

		$submenuStart  = false;
		$total_in_menu = count($this->parent_menu_array[ $parent_id ]);
		$temp_arrs     = array ();
		$i             = 0;

		foreach ( $this->parent_menu_array[ $parent_id ] as $idx => $r )
		{
			if ( !isset($r[ 'id' ]) )
			{
				continue;
			}

			if ( !empty($r[ 'controller' ]) )
			{

				if ( !User::isLoggedIn() && Action::requireLogin($r[ 'controller' ]) )
				{
					continue;
				}

				if ( !User::isLoggedIn() && Action::requirePermission($r[ 'controller' ]) && !Permission::hasControllerActionPerm($r[ 'controller' ]) )
				{
					continue;
				}
			}


			if ( !empty($r[ 'controller' ]) && !empty($r[ 'action' ]) )
			{

				if ( !User::isLoggedIn() && Action::requireLogin($r[ 'controller' ] . '/' . $r[ 'action' ]) )
				{
					continue;
				}
				#if ( $r['controller'] == 'linkcheck') die(Action::requirePermission($r['controller'] . '/' . $r['action']) ? '1/' .$r['action'] : '0/'.$r['action']);
				if ( Action::requirePermission($r[ 'controller' ] . '/' . $r[ 'action' ]) && !Permission::hasControllerActionPerm($r[ 'controller' ] . '/' . $r[ 'action' ]) )
				{
					continue;
				}
			}
			/*

			  if (!empty($r['controller']) && !Permission::hasPerm(false, $r['controller']))
			  {
			  #  continue;
			  }

			  if (!empty($r['controller']) && !empty($r['action']) && !Permission::hasPerm($r['action'], $r['controller']))
			  {
			  # continue;
			  }
			 */


			$group_items = null;
			$r_next      = null;
			if ( isset($this->parent_menu_array[ $parent_id ][ $idx + 1 ]) )
			{
				$r_next = $this->parent_menu_array[ $parent_id ][ $idx + 1 ];
			}

			if ( !defined('_APPMLOAD') && $parent_id == 280 )
			{
				// $temp_arrs[] = $this->getPlugins($temp_arrs, $parent_id, $rootidx);
				/**
				 *
				 */
				define('_APPMLOAD', true);

				$tmparr = array ();
				#	$newidx = count($temp_arrs);
				$tmparr = $this->getApps($r, count($temp_arrs) + 1, $tmparr);
				foreach ( $tmparr as $ar )
				{
					$temp_arrs[ ] = $ar;
				}


				# print_r($temp_arrs);
				# exit;
			}


			//=========================================================
			// Dazugehörige Menüpunkte
			//=========================================================
			if ( isset($this->parent_menu_array[ $r[ 'id' ] ]) )
			{
				$menuicon = $this->_replaceIconExtension(($r[ 'icon' ] != '' ? $r[ 'icon' ] : ''), $this->_icon_type);

				if ( is_file(PUBLIC_PATH . $this->menu_item_dir . '/' . $menuicon) )
				{
					$icon = $menuicon;
				}
				else
				{
					$icon = 'spacer.gif';
				}

				if ( !$submenuStart && count($temp_arrs) )
				{
					$temp_arrs[ ] = array (
						'type' => 'separator',
					);
					$submenuStart = true;

					$newidx = count($temp_arrs);
				}
				else
				{
					$submenuStart = true;
					$newidx       = count($temp_arrs);
				}

				$temp_arrs[ $newidx ] = array (
					'controller' => $r[ 'controller' ],
					'action'     => $r[ 'action' ],
					'url'        => '#',
					'label'      => $r[ 'title' ],
					'icon'       => $icon,
					'ajax'       => $r[ 'ajax' ],
					'items'      => array (),
					'tip'        => $r[ 'description' ],
					'minwidth'   => $r[ 'minwidth' ],
					'minheight'  => $r[ 'minheight' ],
					'modal'      => $r[ 'modal' ],
					'position'   => $r[ 'position' ]
				);


				$tmparr                          = array ();
				$temp_arrs[ $newidx ][ 'items' ] = $this->get_parent_items_arr($tmparr, $r[ 'id' ], $r[ 'id' ]);
			}
			else
			{


				#$this->last_java_var = 'm'.$r['id'];

				if ( $r[ 'item_function' ] )
				{
					$url = $this->_prepareUrl($r[ 'url' ]);

					if ( $r[ 'item_function' ] != 'get_config_items' )
					{
						$icon = 'spacer.gif';

						$menuicon = $this->_replaceIconExtension(($r[ 'icon' ] != '' ? $r[ 'icon' ] :
							''), $this->_icon_type);

						if ( is_file(PUBLIC_PATH . $this->menu_item_dir . '/' . $menuicon) )
						{
							$icon = $menuicon;
						}

						$temp_arrs[ ] = array (
							'controller' => $r[ 'controller' ],
							'action'     => $r[ 'action' ],
							'url'        => '#',
							'ajax'       => $r[ 'ajax' ],
							'label'      => $r[ 'title' ],
							'icon'       => $icon,
							'items'      => array (),
							'tip'        => $r[ 'description' ],
							'minwidth'   => $r[ 'minwidth' ],
							'minheight'  => $r[ 'minheight' ],
							'modal'      => $r[ 'modal' ],
							'position'   => $r[ 'position' ]
						);
						$submenuStart = true;

						$tmparr                                        = array ();
						$temp_arrs[ count($temp_arrs) - 1 ][ 'items' ] = $this->$r[ 'item_function' ]($r, $tmparr);
					}
					else
					{
						$icon = 'spacer.gif';

						$menuicon = $this->_replaceIconExtension(($r[ 'icon' ] != '' ? $r[ 'icon' ] :
							''), $this->_icon_type);

						if ( is_file(PUBLIC_PATH . $this->menu_item_dir . '/' . $menuicon) )
						{
							$icon = $menuicon;
						}

						$temp_arrs[ ] = array (
							'controller' => $r[ 'controller' ],
							'action'     => $r[ 'action' ],
							'url'        => '#',
							'ajax'       => $r[ 'ajax' ],
							'label'      => $r[ 'title' ],
							'icon'       => $icon,
							'items'      => array (),
							'tip'        => $r[ 'description' ],
							'minwidth'   => $r[ 'minwidth' ],
							'minheight'  => $r[ 'minheight' ],
							'modal'      => $r[ 'modal' ],
							'position'   => $r[ 'position' ]
						);
						$submenuStart = true;

						$tmparr                                        = array ();
						$temp_arrs[ count($temp_arrs) - 1 ][ 'items' ] = $this->$r[ 'item_function' ]($r, $tmparr);
						//die($r['item_function']);
					}
				}
				else
				{

					if ( !empty($r[ 'plugin' ]) )
					{
						$r[ 'icon' ] = $this->_replaceIconExtension(($r[ 'icon' ] != '' ? $r[ 'icon' ] :
							'plugin.png'), $this->_icon_type);
					}

					if ( !empty($r[ 'icon' ]) && is_file(PUBLIC_PATH . $this->menu_item_dir . '/' . $r[ 'icon' ]) )
					{
						$icon = $r[ 'icon' ];
					}
					else
					{
						$icon = 'spacer.gif';
					}


					if ( $r[ 'title' ] == '-' || $r[ 'title' ] == '' )
					{
						$temp_arrs[ ] = array (
							'type' => 'separator',
						);
					}
					else
					{

						if ( $submenuStart && count($temp_arrs) )
						{
							$submenuStart = false;
							$temp_arrs[ ] = array (
								'type' => 'separator',
							);
						}

						if ( $r[ 'url' ] == '' || $r[ 'title' ] == '' )
						{
							$url = 'javascript:alert("null");';
							continue;
						}
						else
						{
							$url = $this->_prepareUrl($r[ 'url' ]);
						}

						$temp_arrs[ ] = array (
							'controller' => $r[ 'controller' ],
							'action'     => $r[ 'action' ],
							'url'        => $url,
							'icon'       => $icon,
							'ajax'       => $r[ 'ajax' ],
							'label'      => $r[ 'title' ],
							'tip'        => $r[ 'description' ],
							'minwidth'   => $r[ 'minwidth' ],
							'minheight'  => $r[ 'minheight' ],
							'modal'      => $r[ 'modal' ],
							'position'   => $r[ 'position' ]
						);
					}

					if ( !defined('_PLUGMLOAD') && $parent_id == 303 && $total_in_menu == $i + 1 )
					{
						// $temp_arrs[] = $this->getPlugins($temp_arrs, $parent_id, $rootidx);
						/**
						 *
						 */
						define('_PLUGMLOAD', true);

						$tmparr = array ();
						#	$newidx = count($temp_arrs);
						$tmparr = $this->getPlugins($r, count($temp_arrs) + 1, $tmparr);
						foreach ( $tmparr as $ar )
						{
							$temp_arrs[ ] = $ar;
						}
					}
				}

				$i++;
			}
		}

		return $temp_arrs;
	}

	/**
	 * @param       $r
	 * @param int   $idx
	 * @param array $temp_arrs
	 * @return array
	 */
	function getPlugins ( $r, $idx = 0, $temp_arrs = array () )
	{

		$plugins = Plugin::getInteractivePlugins();

		if ( count($plugins) > 0 )
		{
			$nxt               = $idx++;
			$temp_arrs[ $nxt ] = array (
				'type' => 'separator',
			);


			$nxt               = $idx++;
			$temp_arrs[ $nxt ] = array (
				'url'   => '#',
				'icon'  => 'plugin-run.png',
				'items' => array (),
				'label' => trans('Plugins ausführen')
			);

			$display_run_menu = false;
			foreach ( $plugins as $key => $plugin )
			{
				if ( $plugin[ 'run' ] )
				{
					$display_run_menu                = true;
					$temp_arrs[ $nxt ][ 'items' ][ ] = array (
						'url'          => 'admin.php?adm=plugin&action=run&plugin=' . $key,
						'icon'         => self::getIcon($key),
						'isPluginIcon' => true,
						'label'        => $plugin[ 'name' ],
						'tip'          => sprintf(trans('Plugin `%s` ausführen.'), $plugin[ 'name' ])
					);
				}
			}
			if ( $display_run_menu === false || !count($temp_arrs[ $nxt ][ 'items' ]) )
			{
				unset($temp_arrs[ $nxt ]);
				$nxt--;
			}

			$nxt                 = $idx++;
			$temp_arrs[ $nxt ]   = array (
				'url'   => '#',
				'icon'  => 'plugin-management.png',
				'items' => array (),
				'label' => trans('Plugin Einstellungen')
			);
			$display_config_menu = false;
			foreach ( $plugins as $key => $plugin )
			{
				if ( $plugin[ 'config' ] )
				{
					$display_config_menu             = true;
					$temp_arrs[ $nxt ][ 'items' ][ ] = array (
						'url'          => 'admin.php?adm=plugin&action=config&plugin=' . $key,
						'icon'         => self::getIcon($key),
						'isPluginIcon' => true,
						'label'        => $plugin[ 'name' ],
						'tip'          => sprintf(trans('Plugin `%s` Einstellungen.'), $plugin[ 'name' ])
					);
				}
			}
			if ( $display_config_menu === false )
			{
				unset($temp_arrs[ $nxt ]);
			}
		}


		return $temp_arrs;
	}

	/**
	 *
	 * @param type        $r
	 * @param int|\type   $_idx
	 * @param array|\type $temp_arrs
	 * @return type
	 */
	function getApps ( $r, $_idx = 0, $temp_arrs = array () )
	{

		return $temp_arrs;


		$this->load('App');
		$apps = $this->App->getApps();

		//print_r($apps);exit;

		if ( count($apps) > 0 )
		{
			if ( $_idx > 1 )
			{
				$nxt               = $_idx++;
				$temp_arrs[ $nxt ] = array (
					'type' => 'separator',
				);
			}

			$applicationTypes = $this->App->getApplicationTypes();

			$display_run_menu = false;

			foreach ( $apps as $idx => $app )
			{
				if ( isset($applicationTypes[ $app[ 'apptype' ] ]) )
				{

					$nxt               = $_idx++;
					$temp_arrs[ $nxt ] = array (
						'url'        => '#',
						'icon'       => HTML_URL . 'img/apps/' . $app[ 'apptype' ] . '-small.png',
						'isCoreIcon' => false,
						'items'      => array (),
						'label'      => $app[ 'title' ],
						'tip'        => strip_tags($app[ 'description' ])
					);

					$display_run_menu                = true;
					$temp_arrs[ $nxt ][ 'items' ][ ] = array (
						'url'   => 'admin.php?adm=app&action=items&appid=' . $app[ 'appid' ],
						'icon'  => 'app-contents.png',
						'label' => trans('Inhalte'),
						'isApp' => $this->db->tp(true) . 'applications_items',
						'tip'   => sprintf(trans('Inhalte der Anwendung `%s` bearbeiten.'), $app[ 'title' ])
					);


					$temp_arrs[ $nxt ][ 'items' ][ ] = array (
						'url'   => 'admin.php?adm=app&action=cats&appid=' . $app[ 'appid' ],
						'icon'  => 'app-categories.png',
						'label' => trans('Kategorien'),
						'isApp' => $this->db->tp(true) . 'applications_categories',
						'tip'   => sprintf(trans('Kategorien der Anwendung `%s` bearbeiten.'), $app[ 'title' ])
					);


					$temp_arrs[ $nxt ][ 'items' ][ ] = array (
						'url'   => 'admin.php?adm=app&action=comments&appid=' . $app[ 'appid' ],
						'icon'  => 'comments.png',
						'label' => trans('Kommentare'),
						'isApp' => 1,
						'tip'   => sprintf(trans('Kommentare der Anwendung `%s` freischalten / bearbeiten.'), $app[ 'title' ])
					);

					$temp_arrs[ $nxt ][ 'items' ][ ] = array (
						'url'   => 'admin.php?adm=app&action=tags&appid=' . $app[ 'appid' ],
						'icon'  => 'tag-label.png',
						'label' => trans('Tags'),
						'isApp' => 1,
						'tip'   => sprintf(trans('Tags der Anwendung `%s` bearbeiten.'), $app[ 'title' ])
					);
					$temp_arrs[ $nxt ][ 'items' ][ ] = array (
						'type' => 'separator',
					);

					$temp_arrs[ $nxt ][ 'items' ][ ] = array (
						'url'   => 'admin.php?adm=app&action=buildindex&appid=' . $app[ 'appid' ],
						'icon'  => 'zoom.png',
						'label' => trans('Suchindex erneuern'),
						'isApp' => 1,
						'tip'   => sprintf(trans('Suchindex der Anwendung `%s` neu erzeugen.'), $app[ 'title' ])
					);

					$temp_arrs[ $nxt ][ 'items' ][ ] = array (
						'url'   => 'admin.php?adm=identifier&get=form&controller=app&type=items&action=build_identifiers&appid=' . $app[ 'appid' ],
						'icon'  => 'sitemap.png',
						'label' => trans('Inhalt Rewrite Identifiers erneuern'),
						'ajax'  => true,
						'isApp' => 1,
						'tip'   => sprintf(trans('Rewrite Identifiers der Anwendung `%s` neu erzeugen.'), $app[ 'title' ])
					);

					$temp_arrs[ $nxt ][ 'items' ][ ] = array (
						'url'   => 'admin.php?adm=identifier&get=form&controller=app&type=cat&action=build_identifiers&appid=' . $app[ 'appid' ],
						'icon'  => 'sitemap.png',
						'label' => trans('Kategorien Rewrite Identifiers erneuern'),
						'ajax'  => true,
						'isApp' => 1,
						'tip'   => sprintf(trans('Kategorie rewrite Identifiers der Anwendung `%s` neu erzeugen.'), $app[ 'title' ])
					);

					$temp_arrs[ $nxt ][ 'items' ][ ] = array (
						'type' => 'separator',
					);

					$temp_arrs[ $nxt ][ 'items' ][ ] = array (
						'url'   => 'admin.php?adm=app&action=edit&_type=app&manage=types&appid=' . $app[ 'appid' ],
						'icon'  => 'app-settings.png',
						'label' => trans('Typen Einstellungen'),
						'isApp' => 1,
						'tip'   => sprintf(trans('Typen Einstellungen des Apps `%s`.'), $app[ 'title' ])
					);
					$temp_arrs[ $nxt ][ 'items' ][ ] = array (
						'url'   => 'admin.php?adm=app&action=edit&_type=app&appid=' . $app[ 'appid' ],
						'icon'  => 'app-settings.png',
						'label' => trans('Einstellungen'),
						'isApp' => 1,
						'tip'   => sprintf(trans('Einstellungen der Anwendung `%s`.'), $app[ 'title' ])
					);
				}
			}
			if ( $display_run_menu === false || !count($temp_arrs[ $nxt ][ 'items' ]) )
			{
				unset($temp_arrs[ $nxt ]);
				$nxt--;
			}
		}


		return $temp_arrs;
	}

	/**
	 * @param $key
	 * @return string
	 */
	static function getIcon ( $key )
	{

		$path = Library::formatPath(PLUGIN_PATH . $key . '/plugin.png');

		if ( !is_file($path) )
		{
			return ASSET_PATH . 'styles/' . STYLE . '/img/icon/plugin.png';
		}
		else
		{
			return PLUGIN_URL_PATH . $key . '/plugin.png';
		}
	}

	/**
	 *
	 * @param array $r
	 * @param array $_arrs
	 * @return array
	 */
	private function get_gui_languages ( $r, $_arrs = array () )
	{

		if ( isset($r[ 'item_icon' ]) && trim((string)$r[ 'item_icon' ]) )
		{
			$icon = $r[ 'item_icon' ];
		}


		$icon   = '';
		$icon   = $this->menu_item_dir . 'configuration.gif';
		$url    = $this->_prepareUrl($r[ 'url' ]);
		$code   = '';
		$groups = Dashboard_Config_Base::loadConfigOptions();

		$sql    = "SELECT code, flag, title FROM %tp%locale WHERE guilanguage = 1 ORDER BY title";
		$result = $this->db->query($sql)->fetchAll();


		$idx = 0;
		foreach ( $result as $rs )
		{
			if ( !is_file(I18N_PATH . $rs[ 'code' ] . '/LC_MESSAGES/DreamCMS.po') )
			{
				#continue;
			}


			$url  = 'admin.php?setguilang=' . $rs[ 'code' ];
			$icon = 'spacer.gif';


			if ( $rs[ 'flag' ] && is_file(BACKEND_IMAGE_PATH . 'flags/' . $rs[ 'flag' ]) )
			{
				$icon = 'flags/' . $rs[ 'flag' ];
			}
			else
			{
				$icon = 'spacer.gif';
			}


			$_arrs[ ] = array (
				'controller' => '',
				'action'     => '',
				'url'        => $url,
				'icon'       => $icon,
				'isCoreIcon' => false,
				'label'      => $rs[ 'title' ],
				'tip'        => '',
				'ajax'       => 3 // refresh
			);
		}

		return $_arrs;
	}

	/**
	 *
	 * @param array $r
	 * @param array $_arrs
	 * @return array
	 */
	function get_config_items ( $r, $_arrs = array () )
	{

		if ( isset($r[ 'item_icon' ]) && trim((string)$r[ 'item_icon' ]) )
		{
			$icon = $r[ 'item_icon' ];
		}

		$icon   = '';
		$icon   = 'configuration.gif';
		$url    = $this->_prepareUrl($r[ 'url' ]);
		$code   = '';
		$groups = Dashboard_Config_Base::loadConfigOptions();
		$idx    = 0;
		foreach ( $groups as $group => $rs )
		{
			//$url = $this->make_nav_url("options.php?action=edit&optiongroupid={$row['optiongroupid']}");
			$url        = 'admin.php?adm=settings&action=edit&group=' . $group;
			$icon       = 'spacer.gif';
			$isCoreIcon = true;

			if ( $rs[ 'icon' ] && $rs[ 'icon' ] != '.gif' && is_file(BACKEND_IMAGE_PATH . 'cfgitems/' . $rs[ 'icon' ]) )
			{
				$icon       = 'cfgitems/' . $rs[ 'icon' ];
				$isCoreIcon = false;
			}


			$_arrs[ ] = array (
				'controller' => $r[ 'controller' ],
				'action'     => $r[ 'action' ],
				'url'        => $url,
				'icon'       => $icon,
				'isCoreIcon' => $isCoreIcon,
				'label'      => $rs[ 'label' ],
				'tip'        => '',
				'minwidth'   => $rs[ 'minwidth' ],
				'minheight'  => $rs[ 'minheight' ],
				'modal'      => 1,
				'position'   => 'center'
			);
		}

		return $_arrs;
	}

	/**
	 * @param      $url
	 * @param bool $frontpage
	 * @return mixed|string
	 */
	private function _prepareUrl ( $url, $frontpage = false )
	{

		if ( !trim((string)$url) )
		{
			return "null";
		}

		$url      = str_replace('{php_ext}', 'php', $url);
		$doc_path = $_SERVER[ 'DOCUMENT_ROOT' ] ? $_SERVER[ 'DOCUMENT_ROOT' ] : getenv('DOCUMENT_ROOT');

		$real_path = str_replace($doc_path, '', $root_path);
		$real_path = preg_replace('!/$!', '', $real_path);
		$url       = str_replace('<#REAL_DIR#>', $real_path, $url);

		if ( strpos($url, 'admin.php?adm=') !== false )
		{
			return $url;
		}

		if ( stripos($url, 'http://') !== false )
		{
			return $url;
		}

		$url = str_replace('?', '&', $url);
		$url = str_replace('&amp;', '&', $url);
		$url = str_replace('.php', '', $url);
		$url = 'admin.php?adm=' . $url;

		if ( !$frontpage )
		{
			return "" . $url;
		}
		else
		{
			return $url;
		}
	}

}
