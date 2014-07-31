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
 * @package      DreamCMS
 * @version      3.0.0 Beta
 * @category     Framework
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Menu.php
 */
class Menu /* extends Loader */
{

	private $id = null;

	private static $cachedMenu = null;

	protected static $_instance = null;

	private static $_menuGroups = array (
		'home'    => array (),
		'content' => array (),
		'user'    => array (),
		'tools'   => array (),
		'system'  => array (),
		'plugin'  => array (),
		'layout'  => array (),
		'custom'  => array (),
		'help'    => array (),
	);

	private static $_pluginPerms = array ();

	private static $_application = null;

    /**
     *
     */
    public function __construct ()
	{

		$this->db = Database::getInstance();
	}

	/**
	 *
	 * @return Menu
	 */
	public function getInstance ()
	{

		if ( is_null(self::$_instance) )
		{
			self::$_instance = new Menu();
		}

		return self::$_instance;
	}

	/**
	 *
	 * @param integer $id
	 * @return array the result
	 */
	public function getMenuById ( $id )
	{

		return $this->db->query('SELECT * FROM %tp%navi WHERE id = ?', $id)->fetch();
	}

	/**
	 *
	 * @param integer $id
	 * @return array the result
	 */
	public function getMenuItemById ( $id )
	{

		$transq = $this->buildTransWhere('menu_items', 'i.id', 'it');

		return $this->db->query('SELECT * FROM %tp%navi_items AS i
                                 LEFT JOIN %tp%navi_items_trans AS it 
                                 WHERE i.itemid = ? AND ' . $transq, $id)->fetch();
	}

    /**
     *
     * @param int|string $menuIDs eg: 1,2,3,....
     * @return array the result
     */
	public function getMenuitemFromMenu ( $menuIDs = 0 )
	{

		$transq = $this->buildTransWhere('navi_items', 'i.id', 'it');

		return $this->db->query('SELECT * FROM %tp%navi_items AS i
                                 LEFT JOIN %tp%navi_items_trans AS it 
                                 WHERE i.itemid IN(?) AND ' . $transq, $menuIDs)->fetchAll();
	}

	/**
	 *
	 */
	public function getMenuForSitemap ()
	{

		$menues = $this->db->query('SELECT * FROM %tp%navi WHERE published = 1')->fetchAll();
		$data   = array ();
		foreach ( $menues as $rs )
		{
			if ( trim($rs[ 'menuitems' ]) )
			{
				$items = $this->getMenuitemFromMenu($rs[ 'menuitems' ]);
				foreach ( $items as $r )
				{
					if ( $r[ 'type' ] != 'megamenu' && $r[ 'type' ] != 'spacer' && $r[ 'type' ] != 'folder' && $r[ 'message' ] != 'span' && $r[ 'message' ] != 'div' )
					{
						$data[ ] = array (
							'url' => ''
						);
					}
				}
				$data = array_merge($data, $items);
			}
		}

		return $data;
	}

    /**
     * @param $key
     */
    public function getMenuCache ( $key )
	{

	}

	/**
	 *
	 * @param string $pluginKey
	 * @param array  $perm
	 */
	public static function registerPluginPerms ( $pluginKey, $perm )
	{

		self::$_pluginPerms[ 'plugin_' . strtolower($pluginKey) ] = $perm;
	}

	/**
	 *
	 * @param string $controller
	 * @param array  $items
	 * @param string $groupname
	 */
	private static function prepareItems ( $controller, &$items, $groupname )
	{

		foreach ( $items as $idx => &$r )
		{
			if ( isset( $r[ 'items' ] ) && is_array($r[ 'items' ]) && count($r[ 'items' ]) )
			{
				self::prepareItems($controller, $r[ 'items' ], $groupname);
			}
			else
			{

				if ( !isset( $r[ 'type' ] ) || $r[ 'type' ] !== 'separator' )
				{
					if ( strtolower($groupname) == 'plugin' && strtolower($controller) != 'plugin' )
					{
						#     print_r( self::$_pluginPerms );
						#     exit;

						if ( !isset( self::$_pluginPerms[ 'plugin_' . strtolower($controller) ] ) )
						{
							unset( $r );
							continue;
						}

						if ( empty( $r[ 'action' ] ) )
						{
							$r[ 'action' ] = 'run';
						}

						if ( isset( self::$_pluginPerms[ 'plugin_' . strtolower($controller) ][ $r[ 'action' ] ][ 'requirelogin' ] ) )
						{
							if ( self::$_pluginPerms[ 'plugin_' . strtolower($controller) ][ $r[ 'action' ] ][ 'requirelogin' ] && !User::isLoggedIn() )
							{
								unset( $r );
								continue;
							}
						}


						$permKey = 'plugin_' . strtolower($controller);
						if ( !empty( $r[ 'action' ] ) )
						{
							$permKey .= '/' . strtolower($r[ 'action' ]);
						}
						else
						{
							$permKey .= '/run';
						}
						if ( isset( self::$_pluginPerms[ 'plugin_' . strtolower($controller) ][ $r[ 'action' ] ][ 'requirepermission' ] ) )
						{
							if ( self::$_pluginPerms[ 'plugin_' . strtolower($controller) ][ $r[ 'action' ] ][ 'requirepermission' ] )
							{

								if ( !Permission::hasControllerActionPerm($permKey) )
								{ #die('0'.$permKey);
									unset( $r );
									continue;
								}
								# die('1'.$permKey);
							}
						}


						$r[ 'controller' ] = 'plugin';
						$r[ 'url' ]        = 'admin.php?adm=plugin&plugin=' . $controller;

						if ( !empty( $r[ 'action' ] ) )
						{
							$r[ 'url' ] .= '&action=' . $r[ 'action' ];
						}
					}
					else
					{
						$permKey = $controller;
						if ( !empty( $r[ 'action' ] ) )
						{
							$permKey .= '/' . $r[ 'action' ];
						}
						else
						{
							if ( isset( $r[ 'action' ] ) )
							{
								$permKey .= '/index';
							}
						}

						if ( !User::isLoggedIn() && Action::requireLogin($permKey) )
						{
							unset( $items[ $idx ] );
							continue;
						}

						if ( Action::requirePermission($permKey) && !Permission::hasControllerActionPerm($permKey) )
						{
							unset( $items[ $idx ] );
							continue;
						}


						$r[ 'controller' ] = $controller;
						$r[ 'url' ]        = 'admin.php?adm=' . $controller;

						if ( !empty( $r[ 'action' ] ) )
						{
							$r[ 'url' ] .= '&action=' . $r[ 'action' ];
						}
					}

					if ( !empty( $r[ 'extraparams' ] ) )
					{
						$r[ 'url' ] .= ( substr($r[ 'extraparams' ], 0, 1) !== '&' ? '&' . $r[ 'extraparams' ] : $r[ 'extraparams' ] );
					}
				}
			}
		}
	}

    /**
     *
     * @param bool|string $groupname
     * @param bool $controller
     * @param array $options
     */
	public static function addMenuItem ( $groupname = false, $controller = false, $options = array () )
	{

		if ( self::$_application === null )
		{
			$reg = Registry::getObject('Application');

			if ( !$reg instanceof Application )
			{
				trigger_error('Invalid Application instance', E_USER_ERROR);
			}

			self::$_application = $reg;
			unset( $reg );
		}

		if ( !$groupname || !isset( self::$_menuGroups[ $groupname ] ) )
		{
			trigger_error('Sorry this Menugroup "' . $groupname . '" not exists! Allowed Groups: ' . implode(', ', array_keys(self::$_menuGroups)), E_USER_ERROR);
		}

		if ( !$controller )
		{
			trigger_error('Sorry this Menugroup "' . $groupname . '" must have the CONTROLLER!', E_USER_ERROR);
		}


		if ( strtolower($groupname) === 'plugin' && strtolower($controller) != 'plugin' && $controller != '' )
		{
			$options[ 'isplugin' ] = true;
		}



		if ( !self::$_application->isActiveModul($controller) && (!isset($options[ 'isplugin' ]) || !$options[ 'isplugin' ]) )
		{
			return;
		}


		$opt[ 'controller' ] = $controller;




		if ( isset( $options[ 'items' ] ) && is_array($options[ 'items' ]) && count($options[ 'items' ]) )
		{
			$ucfirst = ucfirst(strtolower($controller));

			if ( strtolower($groupname) == 'plugin' && strtolower($controller) != 'plugin' )
			{
				$options[ 'isplugin' ] = true;

				if ( empty( $options[ 'action' ] ) )
				{
					$options[ 'action' ] = 'run';
				}

				if ( isset(self::$_pluginPerms[ 'plugin_' . strtolower($controller) ][ $options[ 'action' ] ][ 'requirelogin' ]) && self::$_pluginPerms[ 'plugin_' . strtolower($controller) ][ $options[ 'action' ] ][ 'requirelogin' ] && !User::isLoggedIn() )
				{
					return;
				}


				$permKey = 'plugin_' . strtolower($controller);
				if ( !empty( $options[ 'action' ] ) )
				{
					$permKey .= '/' . $options[ 'action' ];
				}
				else
				{
					$permKey .= '/run';
				}

				if ( isset(self::$_pluginPerms[ 'plugin_' . strtolower($controller) ][ $options[ 'action' ] ][ 'requirepermission' ]) && self::$_pluginPerms[ 'plugin_' . strtolower($controller) ][ $options[ 'action' ] ][ 'requirepermission' ] )
				{
					if ( !Permission::hasControllerActionPerm($permKey) )
					{
						return;
					}
				}


				if ( empty( $options[ 'icon' ] ) )
				{
					if ( is_file(PLUGIN_PATH . $ucfirst . '/Resources/' . $ucfirst . '_16x16.png') )
					{
						$options[ 'icon' ] = '/Packages/plugins/' . $ucfirst . '/Resources/' . $ucfirst . '_16x16.png';
					}
				}
			}
			else
			{

				$permKey = $controller;
				if ( !empty( $options[ 'action' ] ) )
				{
					$permKey .= '/' . $options[ 'action' ];
				}
				else
				{
					$permKey .= '/index';
				}

				if ( !User::isLoggedIn() && Action::requireLogin($permKey) )
				{

					return;
				}

				if ( Action::requirePermission($permKey) && !Permission::hasControllerActionPerm($permKey) )
				{
					return;
				}


				if ( empty( $options[ 'icon' ] ) )
				{
					if ( is_file(MODULES_PATH . $ucfirst . '/Resources/' . $ucfirst . '_16x16.png') )
					{
						$options[ 'icon' ] = '/Modules/' . $ucfirst . '/Resources/' . $ucfirst . '_16x16.png';
					}
				}
			}

			$items = $options[ 'items' ];
			self::prepareItems($controller, $items, $groupname);
			$options[ 'items' ] = $items;
		}
		else
		{
			if ( !isset( $options[ 'type' ] ) || $options[ 'type' ] != 'separator' )
			{
				$permKey = ( strtolower($groupname) == 'plugin' && strtolower($controller) != 'plugin' ? 'plugin_' : '' ) . $controller;

				$ucfirst = ucfirst(strtolower($controller));

				if ( strtolower($groupname) === 'plugin' && strtolower($controller) !== 'plugin' )
				{
					if ( empty( $options[ 'action' ] ) )
					{
						$options[ 'action' ] = 'run';
					}

					if (
						isset(self::$_pluginPerms[ 'plugin_' . strtolower($controller) ][ $options[ 'action' ] ][ 'requirelogin' ]) &&
						self::$_pluginPerms[ 'plugin_' . strtolower($controller) ][ $options[ 'action' ] ][ 'requirelogin' ] && !User::isLoggedIn() )
					{

						return;
					}

					if ( !empty( $options[ 'action' ] ) )
					{
						$permKey .= '/' . $options[ 'action' ];
					}
					else
					{

						$permKey .= '/run';
					}

					if (
						isset(self::$_pluginPerms[ 'plugin_' . strtolower($controller) ][ $options[ 'action' ] ][ 'requirepermission' ]) &&
						self::$_pluginPerms[ 'plugin_' . strtolower($controller) ][ $options[ 'action' ] ][ 'requirepermission' ] )
					{
						if ( !Permission::hasControllerActionPerm($permKey) )
						{

							return;
						}
					}


					$options[ 'controller' ] = 'plugin';
					$options[ 'url' ]        = 'admin.php?adm=plugin&plugin=' . $controller;

					if ( !empty( $options[ 'action' ] ) )
					{
						$options[ 'url' ] .= '&action=' . $options[ 'action' ];
					}

					if ( empty( $options[ 'icon' ] ) )
					{
						if ( is_file(PLUGIN_PATH . $ucfirst . '/Resources/' . $ucfirst . '_16x16.png') )
						{
							$options[ 'icon' ] = '/Packages/plugins/' . $ucfirst . '/Resources/' . $ucfirst . '_16x16.png';
						}
					}
				}
				else
				{

					if ( !empty( $options[ 'action' ] ) )
					{
						$permKey .= '/' . $options[ 'action' ];
					}
					else
					{

						$permKey .= '/index';
					}

					if ( !User::isLoggedIn() && Action::requireLogin($permKey) )
					{
						return;
					}

					if ( Action::requirePermission($permKey) && !Permission::hasControllerActionPerm($permKey) )
					{
						return;
					}

					if ( empty( $options[ 'icon' ] ) )
					{
						if ( is_file(MODULES_PATH . $ucfirst . '/Resources/' . $ucfirst . '_16x16.png') )
						{
							$options[ 'icon' ] = '/Modules/' . $ucfirst . '/Resources/' . $ucfirst . '_16x16.png';
						}
					}

					$options[ 'controller' ] = $controller;
					$options[ 'url' ]        = 'admin.php?adm=' . $controller;

					if ( !empty( $options[ 'action' ] ) )
					{
						$options[ 'url' ] .= '&action=' . $options[ 'action' ];
					}
				}

				if ( !empty( $options[ 'extraparams' ] ) )
				{
					$options[ 'url' ] .= ( substr($options[ 'extraparams' ], 0, 1) !== '&' ? '&' . $options[ 'extraparams' ] : $options[ 'extraparams' ] );
				}
			}
		}

		$opt = array_merge($opt, $options);

		self::$_menuGroups[ $groupname ][ ] = $opt;
	}

    /**
     * @param $list
     * @return mixed
     */
    private static function removeSeparators ( $list )
	{

		foreach ( $list as $idx => &$r )
		{
			if ( $r[ 'type' ] == 'separator' )
			{
				unset( $list[ $idx ] );
			}
		}

		return $list;
	}

	/**
	 *
	 */
	public static function getMenu ()
	{

		if ( defined('ADM_SCRIPT') && ADM_SCRIPT )
		{

			$tmp = array ();

			$tt = self::$_menuGroups;
			foreach ( $tt as $key => &$arr )
			{
				foreach ( $arr as $k => $r )
				{
					if ( isset($r[ 'type' ]) && $r[ 'type' ] == 'separator' )
					{
						unset( $arr[ $k ] );
					}
				}
			}

			foreach ( $tt as $key => $arr )
			{
				if ( !count($arr) )
				{
					unset( self::$_menuGroups[ $key ] );
					continue;
				}
			}

            /*
            function cmp($a, $b)
            {
                return strcmp($a["label"], $b["label"]);
            }

            foreach ( self::$_menuGroups as $key => &$arr )
            {
                usort($arr, "cmp");
            }
            */



			foreach ( self::$_menuGroups as $key => $arr )
			{
				if ( !count($arr) )
				{
					continue;
				}




				$label = false;
                $icon  = null;

				if ( $key == 'home' )
				{
					$label = trans('Start');
                    $icon  = 'fa-users';
				}
				else if ( $key == 'content' )
				{
					$label = trans('Inhalte');
                    $icon  = 'fa-pencil';
				}
				else if ( $key == 'tools' )
				{
					$label = trans('Tools');
                    $icon  = 'fa-flask';
				}
				else if ( $key == 'plugin' )
				{
					$label = trans('Plugins');
                    $icon  = 'fa-building-o';
				}
				else if ( $key == 'layout' )
				{
					$label = trans('Layout');
                    $icon  = 'fa-desktop';
				}
				else if ( $key == 'system' )
				{
					$label = trans('System');
                    $icon  = 'fa-cogs';
				}
				else if ( $key == 'help' )
				{
					$label = trans('Hilfe');
                    $icon  = 'fa-question-circle';
				}
				else if ( $key == 'custom' )
				{
					$label = trans('Sonstiges');
                    $icon  = 'fa-tint';
				}
				else if ( $key == 'user' )
				{
					$label = trans('Benutzer');
                    $icon  = 'fa-users';
				}


				$tmp[ ] = array (
					'label' => $label,
                    'icon'  => $icon,
					'items' => $arr
				);
			}


			return $tmp;
		}


	}

	/**
	 *
	 * OLD MENU
	 */
	private static $db = null;

	private static $orderedMenuArray = array ();

	private static $orderedMenuArrayLoad = false;

	private static $tempMenuTree = array ();

	private static $getAncestorsCache = null;

    /**
     *
     * @param bool|int $menu_id default is false
     * @return array
     */
	public static function load_menu ( $menu_id = false )
	{

		if ( $menu_id === false )
		{
			return array ();
		}

		if ( is_null(self::$db) )
		{
			self::$db = Database::getInstance();
		}

		$sql = "SELECT * FROM %tp%menu WHERE id=?";
		$r   = self::$db->query($sql, $menu_id)->fetch();

		return $r;
	}

    /**
     * @return array|null
     * @throws BaseException
     */
    public static function _getMenu ()
	{

		if ( is_null(self::$db) )
		{
			self::$db = Database::getInstance();
		}

		if ( !Registry::objectExists('Application') )
		{
			throw new BaseException( 'Application instance not exists' );
		}

		$pagedata    = Registry::getObject('Application')->Site->getSiteData('website');
		$lft         = intval($pagedata[ 'lft' ]);
		$rgt         = intval($pagedata[ 'rgt' ]);
		$webSiteId   = PAGEID;
		$nodeId      = intval($pagedata[ 'parentid' ]);
		$usergroupID = User::getGroupId();

		if ( self::$cachedMenu === null )
		{
			$cache = Menu::buildCache();
		}
		else
		{
			$cache = self::$cachedMenu;
		}

		return $cache;


		$uns = array (
			'id'       => 2,
			'parentid' => 0
		);
		#array_unshift($cache, $uns );

		$nodes = array ();
		$tree  = array ();
		foreach ( $cache as &$node )
		{
			$node[ "Children" ] = array ();
			$id                 = $node[ "id" ];
			$parent_id          = $node[ "parentid" ];
			$nodes[ $id ]       = & $node;
			if ( isset($nodes[$parent_id]) )
			{
				$nodes[ $parent_id ][ "Children" ][ ] = & $node;
			}
			else
			{
				$tree[ ] = & $node;
			}
		}

		print_r($tree);
		exit;
		$tree = new Tree( 2 );
		$tree->setupData($cache, 'id', 'parentid', false);

		$ncache = $tree->buildRecurseArray(2);

		print_r($ncache);
		exit;


		$treeMap = TemplateCompiler::mapTree($cache, 'parentid', 'id');

		print_r($treeMap);
		exit;

		return $cache;
	}

    /**
     * generate the menutree for the frontend modul
     *
     * @param int|\type $node
     * @throws BaseException
     * @return array
     */
	static function getMenuTree ( $node = 2 )
	{

		if ( is_null(self::$db) )
		{
			self::$db = Database::getInstance();
		}

		if ( !Registry::objectExists('Application') )
		{
			throw new BaseException( 'Application instance not exists' );
		}

		$pagedata = Registry::getObject('Application')->Site->getSiteData('website');


		$lft         = intval($pagedata[ 'lft' ]);
		$rgt         = intval($pagedata[ 'rgt' ]);
		$webSiteId   = PAGEID;
		$nodeId      = intval($pagedata[ 'parentid' ]);
		$usergroupID = User::getGroupId();

		if ( self::$cachedMenu === null )
		{
			$cache = Menu::buildCache();
		}

		$cache = self::$cachedMenu;

		$activeMenuData = Registry::getObject('Application')->Site->getSiteData('contentdata');
		$page           = array (); //(array) $GLOBALS['FRONTEND']->Data->get('Page');

		$isApp = false;

		if ( CONTROLLER === 'apps' )
		{
			$isApp = true;
			if ( isset( $activeMenuData[ 'app' ] ) && isset( $activeMenuData[ 'app' ][ 'apptype' ] ) )
			{

				$activeMenuData[ 'apptype' ]  = $activeMenuData[ 'app' ][ 'apptype' ];
				$activeMenuData[ 'appalias' ] = $activeMenuData[ 'app' ][ 'alias' ];
			}
			if ( isset( $activeMenuData[ 'cat' ] ) )
			{
				if ( isset( $activeMenuData[ 'cat' ][ 'catid' ] ) )
				{
					$activeMenuData[ 'catid' ] = $activeMenuData[ 'cat' ][ 'catid' ];
				}
				if ( isset( $activeMenuData[ 'cat' ][ 'alias' ] ) )
				{
					$activeMenuData[ 'alias' ] = $activeMenuData[ 'cat' ][ 'alias' ];
				}
				if ( isset( $activeMenuData[ 'cat' ][ 'activemenuitemid' ] ) )
				{
					$activeMenuData[ 'activemenuitemid' ] = $activeMenuData[ 'cat' ][ 'activemenuitemid' ];
				}
			}

			if ( isset( $activeMenuData[ 'item' ] ) )
			{
				if ( isset( $activeMenuData[ 'item' ][ 'itemid' ] ) )
				{
					$activeMenuData[ 'itemid' ] = $activeMenuData[ 'item' ][ 'itemid' ];
				}
				if ( isset( $activeMenuData[ 'item' ][ 'alias' ] ) )
				{
					$activeMenuData[ 'alias' ] = $activeMenuData[ 'item' ][ 'alias' ];
				}
				if ( isset( $activeMenuData[ 'item' ][ 'activemenuitemid' ] ) )
				{
					$activeMenuData[ 'activemenuitemid' ] = $activeMenuData[ 'item' ][ 'activemenuitemid' ];
				}
			}
		}


		#$activeMenuData = array_merge($activeMenuData, $modul);


		$tmp        = array ();
		$_tmpActive = array ();

		/**
		 *
		 */
		foreach ( $cache as $idx => $r )
		{

			/**
			 * @todo send error if not usergroup exists
			 */
			/*
			  if ( $r['mgroups'] !== '' && CONTROLLER === $r['controller'] )
			  {
			  $g = explode(',', $r['mgroups']);
			  if ( !in_array(User::getGroupId(), $g) && !in_array(0, $g) )
			  {
			  $GLOBALS['FRONTEND']->Site->disableSiteCaching();
			  $GLOBALS['FRONTEND']->Page->sendAccessError(trans('Sie nicht die erforderlichen Rechte!'));
			  }
			  }
			 */

			if ( $r[ 'activemenuitemid' ] )
			{
				$_tmpActive[ $r[ 'activemenuitemid' ] ] = true;
			}
		}


		$coreRequest = REQUEST;
		if ( substr($coreRequest, 0, 5) === 'apps/' )
		{
			$coreRequest = substr($coreRequest, 5);
		}


		$isActiveSet = false;
		$reset       = false;

		foreach ( $cache as $idx => $r )
		{
			if ( !$r[ 'published' ] )
			{
				continue;
			}
			/**
			 * skip all menu items when user not has access
			 */
			$mgroups = isset( $r[ 'mgroups' ] ) ? explode(',', $r[ 'mgroups' ]) : array (
				0
			);
			if ( count($mgroups) )
			{
				if ( ( !in_array($usergroupID, $mgroups) && !in_array(0, $mgroups) ) || !$r[ 'published' ] )
				{
					continue;
				}
			}


			$cache[ $idx ][ 'isActive' ] = false;


			if ( $isApp )
			{
				if ( isset( $activeMenuData[ 'apptype' ] ) && $activeMenuData[ 'apptype' ] == $r[ 'appcontroller' ] )
				{
					$active = false;

					if ( $activeMenuData[ 'catid' ] == $r[ 'contentid' ] )
					{
						$active      = true;
						$isActiveSet = true;
					}

					if ( !$isActiveSet && !$active && $activeMenuData[ 'itemid' ] && $activeMenuData[ 'alias' ] == $r[ 'alias' ] )
					{
						$active      = true;
						$isActiveSet = true;
					}


					if ( !$isActiveSet && !$active && $activeMenuData[ 'activemenuitemid' ] > 0 && $activeMenuData[ 'activemenuitemid' ] == $r[ 'id' ] )
					{
						$active      = true;
						$isActiveSet = true;
					}

					if ( !$isActiveSet && !$r[ 'contentid' ] && !$active )
					{
						$active      = true;
						$isActiveSet = true;
					}

					$cache[ $idx ][ 'isActive' ] = $active;
				}
				else
				{
					$active = false;

					if ( !$isActiveSet && !isset( $activeMenuData[ 'itemtype' ] ) && isset( $activeMenuData[ 'catid' ] ) && $activeMenuData[ 'catid' ] == $r[ 'contentid' ] )
					{
						$active      = true;
						$isActiveSet = true;
					}

					if ( !$isActiveSet && isset( $activeMenuData[ 'itemtype' ] ) && isset( $activeMenuData[ 'itemid' ] ) && $activeMenuData[ 'itemid' ] && $activeMenuData[ 'alias' ] == $r[ 'alias' ] )
					{
						$active      = true;
						$isActiveSet = true;
					}


					if ( !$isActiveSet && isset( $activeMenuData[ 'activemenuitemid' ] ) && $activeMenuData[ 'activemenuitemid' ] > 0 && $activeMenuData[ 'activemenuitemid' ] == $r[ 'id' ] )
					{
						$active      = true;
						$isActiveSet = true;
					}

					if ( !$isActiveSet && $active )
					{
						$isActiveSet = true;
					}
					$cache[ $idx ][ 'isActive' ] = $active;
				}


				if ( $r[ 'link' ] !== '' && substr($r[ 'link' ], 0, 1) === '/' )
				{
					$r[ 'link' ] = substr($r[ 'link' ], 1);
				}

				if ( !$active && ( $r[ 'link' ] === $coreRequest ) )
				{
					$cache[ $idx ][ 'isActive' ] = true;
					$isActiveSet                 = true;
				}
			}
			else
			{
				if ( /* ($activeMenuData['controller'] == $r['controller'] && (defined('CONTROLLER') && CONTROLLER != $r['controller']) ) || */
					( defined('CONTROLLER') && CONTROLLER == $r[ 'controller' ] ) || ( $r[ 'link' ] === $coreRequest )
				)
				{
					$active = false;

					if ( !$isActiveSet && $activeMenuData[ 'action' ] == $r[ 'action' ] )
					{
						$active      = true;
						$isActiveSet = true;
					}

					if ( !$isActiveSet && !$active && $activeMenuData[ 'contentid' ] == $r[ 'contentid' ] )
					{
						$active      = true;
						$isActiveSet = true;
					}

					if ( $activeMenuData[ 'activemenuitemid' ] > 0 && $activeMenuData[ 'activemenuitemid' ] == $r[ 'id' ] )
					{
						$active      = true;
						$isActiveSet = true;
					}

					if ( !$isActiveSet && !$r[ 'contentid' ] && !$active )
					{
						$active      = true;
						$isActiveSet = true;
					}

					$cache[ $idx ][ 'isActive' ] = $active;
				}
				else
				{
					if ( !$isActiveSet && isset( $activeMenuData[ 'menuitemid' ] ) && $activeMenuData[ 'menuitemid' ] == $r[ 'id' ] )
					{
						$cache[ $idx ][ 'isActive' ] = true;
						$isActiveSet                 = true;
					}
				}
			}

			if ( $r[ 'controller' ] === 'printpage' )
			{
				$cache[ $idx ][ 'isActive' ] = false;
				$cache[ $idx ][ 'isPrint' ]  = true;
				$cache[ $idx ][ 'link' ]     = REQUEST . '?print=1';
				$cache[ $idx ][ 'target' ]   = '_blank';
				$cache[ $idx ][ 'name' ]     = trans('Druckansicht');
			}

			$tmp[ ] = $cache[ $idx ];
		}

		/**
		 * no active Menuitem is set
		 */
		if ( !$isActiveSet )
		{
			# $tmp[0]['isActive'] = true;
		}


		/*

		  print_r($tmp);
		  print_r($activeMenuData);
		  exit;
		 *
		 */

		return $tmp;
	}

	/**
	 *
	 * @param integer $pageID (NOT USED)
	 * @return array
	 */
	public static function buildCache ( $pageID = 0 )
	{

		if ( self::$cachedMenu !== null )
		{
			return self::$cachedMenu;
		}


		Library::enableErrorHandling();

		self::$cachedMenu = Cache::get('fe_menucache', 'data/menu/' . CONTENT_TRANS);

		if ( !self::$cachedMenu )
		{
			$jstree = new Tree_JsTree( '%tp%page', array (
			                                             "id"        => "id",
			                                             "parentid"  => "parentid",
			                                             "position"  => "ordering",
			                                             "type"      => "type",
			                                             "is_folder" => "is_folder"
			                                       ) );

			$tmp = $jstree->_get_children(PAGEID, true);
			unset( $tmp[ PAGEID ] );


			$_ids = array ();
			foreach ( $tmp as $k => $v )
			{
				$_ids[ ] = $k;
			}

			/**
			 * language select
			 */
			$transq1 = Loader::sbuildTransWhere('page', 'p.id', 'pt');

			$db = Database::getInstance();

			$translations = $db->query('SELECT pt.* FROM %tp%page AS p
                                              LEFT JOIN %tp%page_trans AS pt ON (pt.id=p.id) 
                                              WHERE p.id IN(' . implode(',', $_ids) . ') AND ' . $transq1)->fetchAll();

			foreach ( $translations as $r )
			{

				$tmp[ $r[ 'id' ] ] = array_merge($tmp[ $r[ 'id' ] ], $r);
			}

			$results = array ();
			foreach ( $tmp as $id => $data )
			{
				if ( !$data[ 'mpublished' ] || $data[ 'pageid' ] != PAGEID )
				{
					continue;
				}

				$data[ 'is_folder' ] = self::hasChilds($id, $tmp);

				$count = 0;
				if ( $data[ 'is_folder' ] )
				{
					foreach ( $tmp as $sid => $r )
					{
						if ( $r[ 'parentid' ] == $id && $data[ 'mpublished' ] )
						{
							$count++;
						}
					}
				}

				$data[ 'activesubitems' ] = $count;
				$data[ 'orginal_type' ]   = $data[ 'type' ];
				$results[ ]               = $data;
			}


			unset( $tmp );

			// remove Root
			$cache     = array ();
			$ids       = array ();
			$lastlevel = 0;


			$defaultSuffix = Settings::get('mod_rewrite_suffix', 'html');


			foreach ( $results as $idx => $r )
			{

				if ( !$r[ 'activesubitems' ] )
				{
					$r[ 'is_folder' ] = 0;
				}


				$r[ 'type' ] = ( $r[ 'link' ] == 1 ? 'site' : $r[ 'type' ] );


				if ( empty( $r[ 'suffix' ] ) )
				{
					$r[ 'suffix' ] = $defaultSuffix;
				}


				if ( empty( $r[ 'link' ] ) )
				{
					$rootlink = array ();

					if ( !$r[ 'appid' ] )
					{
						if ( $r[ 'controller' ] != 'main' )
						{
							$rootlink[ ] = $r[ 'controller' ];
						}

						if ( ( !$r[ 'contentid' ] && $r[ 'action' ] != 'index' ) )
						{
							$rootlink[ ] = $r[ 'action' ];
						}

						if ( $r[ 'alias' ] != '' && $r[ 'suffix' ] != '' )
						{
							$rootlink[ ] = $r[ 'alias' ] . '.' . $r[ 'suffix' ];
						}
						else
						{
							$rootlink[ ] = $r[ 'action' ];
						}
					}
					else
					{
						$rootlink[ ] = ( !empty( $r[ 'appalias' ] ) ? $r[ 'appalias' ] : $r[ 'appcontroller' ] );

						if ( $r[ 'action' ] != 'category' )
						{
							$rootlink[ ]   = $r[ 'action' ];
							$r[ 'itemid' ] = $r[ 'contentid' ];
						}
						elseif ( !$r[ 'contentid' ] && $r[ 'action' ] == 'category' )
						{
							// $rootlink[] = $r['action'];
							$r[ 'catid' ] = 0;
						}
						elseif ( $r[ 'contentid' ] && $r[ 'action' ] == 'category' )
						{
							$r[ 'catid' ] = $r[ 'contentid' ];
							$rootlink[ ]  = $r[ 'action' ];
						}


						if ( $r[ 'contentid' ] && $r[ 'action' ] != 'category' )
						{
							$rootlink[ ] = $r[ 'contentid' ];
						}

						if ( $r[ 'alias' ] != '' && $r[ 'suffix' ] != '' )
						{
							$rootlink[ ] = $r[ 'alias' ] . '.' . $r[ 'suffix' ];
						}
					}

					$r[ 'link' ] = implode('/', $rootlink);
				}


				$r[ 'menuitemid' ] = $r[ 'id' ];

				$r[ 'level' ] = $r[ 'level' ] - 3;
				$r[ 'name' ]  = ( $r[ 'pagetitle' ] != '' ? $r[ 'pagetitle' ] : $r[ 'title' ] );
				$cache[ ]     = $r;
			}

			self::$cachedMenu = $cache;
			Cache::write('fe_menucache', $cache, 'data/menu/' . CONTENT_TRANS);
		}

		return self::$cachedMenu;
	}

	/**
	 *
	 * @param array $results
	 * @return array
	 */
	public static function create_tree ( $results )
	{

		$return = $results[ 0 ];
		array_shift($results);

		if ( $return[ 'lft' ] + 1 === $return[ 'rgt' ] )
		{
			$return[ 'leaf' ] = true;
		}
		else
		{
			foreach ( $results as $key => $result )
			{
				if ( $result[ 'lft' ] > $return[ 'rgt' ] ) //not a child
				{
					break;
				}

				if ( $rgt > $result[ 'lft' ] ) //not a top-level child
				{
					continue;
				}
				$return[ 'children' ][ ] = self::create_tree(array_values($results));

				foreach ( $results as $child_key => $child )
				{
					if ( $child[ 'rgt' ] < $result[ 'rgt' ] )
					{
						unset( $results[ $child_key ] );
					}
				}

				$rgt = $result[ 'rgt' ];
				unset( $results[ $key ] );
			}
		}

		unset( $return[ 'lft' ], $return[ 'rgt' ] );

		return $return;
	}

	/**
	 *
	 * @param integer $id
	 * @param array   $data
	 * @return boolean
	 */
	private static function buildOrderedMenuArray ( $id = 0, $data = array () )
	{

		// database has already been queried
		if ( is_array($data[ $id ]) )
		{
			foreach ( $data[ $id ] as $ordering => $holder )
			{
				foreach ( $holder as $idx => $row )
				{
					self::$orderedMenuArray[ ] = $row;
					// unset($data[$id]);
					self::buildOrderedMenuArray($row[ 'id' ], $data);
				} // end foreach
			} // end foreach
		} // end if

		return true;
	}

	/**
	 *
	 * @param integer $pid
	 * @param array   $data
	 * @return boolean
	 */
	private static function hasChilds ( $pid, $data = array () )
	{

		foreach ( $data as $id => $r )
		{
			if ( $r[ 'parentid' ] == $pid )
			{
				return true;
			}
		}

		return false;
	}

}
