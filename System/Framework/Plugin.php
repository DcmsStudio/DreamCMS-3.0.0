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
 * @file         Plugin.php
 */
class Plugin extends Loader
{

	/**
	 * @var null
	 */
	protected static $_instanceObj = null;

	/**
	 * @var
	 */
	protected static $plugins;

	/**
	 * @var bool
	 */
	public $key = false;

	/**
	 * @var bool
	 */
	public $config = false;

	/**
	 * @var bool
	 */
	public $is_runnable = false;

	/**
	 * @var bool
	 */
	public $is_configurable = false;

	/**
	 * @var array
	 */
	public $data = array ();

	/**
	 * @var null
	 */
	protected $_permissions = null;

	/**
	 * @var null
	 */
	protected $_definition = null;

	/**
	 * @var null
	 */
	protected static $_pluginRegistry = null;

	/**
	 * @var array
	 */
	protected static $_pluginPerms = array ();

	/**
	 * @return null|Plugin
	 */
	public static function getInstance ()
	{

		if ( self::$_instanceObj === null )
		{
			self::$_instanceObj = new Plugin();

			Plugin::registerPlugins();
		}

		return self::$_instanceObj;
	}

	/**
	 * @return null
	 */
	public static function getPluginRegistry ()
	{

		return self::$_pluginRegistry;
	}

	/**
	 * @param null $pluginRegistry
	 */
	public static function setPluginRegistry ( $pluginRegistry )
	{

		self::$_pluginRegistry = $pluginRegistry;
	}

	/*
	  protected function __construct()
	  {
	  parent::__construct();
	  }
	 */

	/**
	 * @param $method
	 * @param $args
	 */
	public function __call ( $method, $args )
	{

		$name = get_class($this);

		if ( $name != 'Plugin' )
		{
			$name = strtolower(str_replace('Plugin', '', $name));
			Database::getInstance()->query("DELETE FROM %tp%event_hook WHERE `type` = 'plugin' AND event = ? AND handler = ?", $method, $name);
			Cache::delete('event_hooks');
		}
		else
		{
			trigger_error('Class `' . get_class($this) . '` has no method `' . $method . '`.', E_USER_ERROR);
		}
	}

	/**
	 *
	 */
	public static function hasPerm ()
	{

	}

	/**
	 * Get a config item for the current Plugin
	 *
	 * @param string $key
	 * @param mixed  $default
	 * @return mixed
	 */
	public function get ( $key, $default = null )
	{

		return ( isset( $this->config[ $key ] ) ? $this->config[ $key ] : $default );
	}

	/**
	 * Set a config item for the current Plugin
	 *
	 * @param string $key
	 * @param mixed  $value default is null
	 * @return mixed
	 */
	public function set ( $key, $value = null )
	{

		$this->config[ $key ] = $value;
	}

	/**
	 *
	 */
	public static function registerPlugins ()
	{

		if ( self::$_pluginRegistry === null )
		{
			$plugins = self::getInstalledPlugins();

			foreach ( $plugins as $r )
			{

				self::$_pluginRegistry[ $r[ 'key' ] ] = $r;
			}
		}
	}

	public static function registerBackendMenues ()
	{

		self::registerPlugins();


		foreach ( self::$_pluginRegistry as $name => $r )
		{
			$ucModul = ucfirst($name);

			$className = 'Addon_' . $ucModul . '_Config_Base';
			// register backend menu
			if ( checkClassMethod($className . '/registerBackedMenu', 'static') )
			{
				call_user_func($className . '::registerBackedMenu');
			}

		}
	}

	/**
	 *
	 * @param string $key
	 * @return bool
	 */
	public static function isActive ( $key )
	{

		self::registerPlugins();

		if ( !isset( self::$_pluginRegistry[ $key ] ) )
		{
			return false;
		}

		if ( !isset( self::$_pluginRegistry[ $key ][ 'published' ] ) || !self::$_pluginRegistry[ $key ][ 'published' ] )
		{
			return false;
		}

		self::registerPluginPerms($key);

		return true;
	}

	/**
	 * @param $key
	 * @return mixed returns false if has no model class
	 */
	public static function getModel ( $key )
	{

		$className = 'Addon_' . ucfirst(strtolower($key)) . '_Model_Mysql';
		if ( !class_exists($className, false) && is_file(PLUGIN_PATH . ucfirst(strtolower($key)) . '/Model/Mysql.php') )
		{
			include_once( PLUGIN_PATH . ucfirst(strtolower($key)) . '/Model/Mysql.php' );

			return new $className();
		}

		return false;
	}

	/**
	 * @param      $config
	 * @param bool $name
	 * @throws BaseException
	 */
	public function initPlugin ( $config, $name = false )
	{

		if ( !is_array($config) || !$name )
		{
			throw new BaseException( 'Invalid Plugin Configuration' );
		}

		$this->key    = strtolower($name);
		$this->config = $config;


		$className = 'Addon_' . ucfirst($this->key) . '_Config_Base';

		/**
		 *
		 */
		if (!defined('PLUGIN_PERMKEY') ) {
            /**
             *
             */
            define( 'PLUGIN_PERMKEY', 'plugin_' . $this->key ); }

		if ( !$this->getController()->isBackend() )
		{
			$this->initRoute();
			# define( 'PLUGIN_PERMKEY', 'plugin_' . $this->key );
		}
		else
		{
			# define( 'PLUGIN_PERMKEY', 'plugin_' . $this->key );
		}

		$this->load('Template');
		$this->Template->assign('pluginpath', PLUGIN_URL_PATH . ucfirst($this->key) . '/');


		// Read all permission options
		if ( checkClassMethod($className . '/getControllerPermissions', 'static') )
		{
			/**
			 * Store frontent permissions
			 */
			$frontendPerms = call_user_func($className . '::getControllerPermissions', false);

			if ( is_array($frontendPerms) )
			{
				foreach ( $frontendPerms as $action => $value )
				{
					$this->_permissions[ Application::FRONTEND_MODE ][ $action ] = array (
						'requirelogin'      => ( $value[ 0 ] ? true : false ),
						'requirepermission' => ( $value[ 1 ] ? true : false )
					);
				}
			}

			$frontendPerms = null;

			/**
			 * Store backend permissions
			 */
			$backendPerms = call_user_func($className . '::getControllerPermissions', true);

			if ( is_array($backendPerms) )
			{
				foreach ( $backendPerms as $action => $value )
				{
					$this->_permissions[ Application::BACKEND_MODE ][ $action ] = array (
						'requirelogin'      => ( $value[ 0 ] ? true : false ),
						'requirepermission' => ( $value[ 1 ] ? true : false )
					);
				}
			}

			$backendPerms = null;
		}


		// Read modul definitions
		if ( checkClassMethod($className . '/getModulDefinition', 'static') )
		{
			$this->_definition[ 'definition' ] = call_user_func($className . '::getModulDefinition', true);
		}

        $this->load('Template');
	}

	/**
	 * @param $key
	 * @return bool|mixed
	 */
	public static function getPluginDefinition ( $key )
	{

		$className = 'Addon_' . ucfirst(strtolower($key)) . '_Config_Base';

		if ( checkClassMethod($className . '/getModulDefinition', 'static') )
		{
			return call_user_func($className . '::getModulDefinition', true);
		}

		return false;
	}

	/**
	 *
	 * @return array/null
	 */
	public function getDefinition ()
	{

		return $this->_definition;
	}

	/**
	 *
	 */
	public function checkPermsBeforeExecuteAction ()
	{

		if ( is_array($this->_permissions) )
		{

			$acts = explode('/', REQUEST);
			$acts = Library::unempty($acts);

			$extra = ( $this->getController()->isBackend() ? '' : '' );

			$lowerAction = strtolower(ACTION);
			$action      = (string)$this->input('action');

			$actionStr = preg_replace('#([^a-z0-9_]*)#i', '', $action);
			if ( $action != '' && $actionStr != $action )
			{
				$this->load('Page');
				$this->Page->error(404, sprintf('Die von Ihnen aufgerufene Seite existiert.'));
			}

			if ( $action != $lowerAction )
			{
				$lowerAction = strtolower($action);
			}

			if ( empty( $lowerAction ) )
			{
				$lowerAction = 'run';
			}


			/**
			 *
			 */
			if (!defined( 'PLUGIN') ) {
                /**
                 *
                 */
                define( 'PLUGIN', $this->key ); }
			/**
			 *
			 */
			if (!defined( 'PLUGIN_ACTION') ) {
                /**
                 *
                 */
                define( 'PLUGIN_ACTION', $lowerAction ); }


			Permission::getInstance();
			$params = $this->_permissions[ $this->getApplication()->getMode() ];


			if ( is_array($params) && isset( $params[ $lowerAction ] ) )
			{
				$param = $params[ $lowerAction ];

				if ( isset($param[ 'requirelogin' ]) && $param[ 'requirelogin' ] && !User::isLoggedIn() )
				{
					if ( IS_AJAX )
					{
						Ajax::Send(false, array (
						                        'sessionerror' => true,
						                        'msg'          => sprintf('Diese Funktion erfordert, dass Sie eingeloggt sind. (%s/%s)', PLUGIN_PERMKEY, $lowerAction)
						                  ));
					}

					$this->load('Page');
					$this->Page->sendAccessError(sprintf('Diese Funktion erfordert, dass Sie eingeloggt sind. (%s/%s)', PLUGIN_PERMKEY, $lowerAction));
				}

				if ( isset($param[ 'requirepermission' ]) && $param[ 'requirepermission' ] )
				{
					if ( !Permission::hasControllerActionPerm(PLUGIN_PERMKEY . '/' . $extra . $lowerAction) )
					{
						if ( IS_AJAX )
						{
							Ajax::Send(false, array (
							                        'permissionerror' => true,
							                        'msg'             => sprintf('Sie haben keine Berechtigung zum durchführen dieser Aktion. (%s/%s)', PLUGIN_PERMKEY, $lowerAction)
							                  ));
							exit;
						}

						$this->load('Page');
						$this->Page->sendAccessError(sprintf('Sie haben keine Berechtigung zum durchführen dieser Aktion. (%s/%s)', PLUGIN_PERMKEY, $lowerAction));
					}
				}
			}

			/*
			  if ($param[ 'requirepermission' ] && !Permission::hasControllerActionPerm( PLUGIN_PERMKEY . '/' . $extra . $lowerAction ) )
			  {
			  if ( IS_AJAX )
			  {
			  Ajax::Send( false, array(
			  'permissionerror' => true,
			  'msg'             => sprintf( 'Sie haben keine Berechtigung zum durchführen dieser Aktion. (%s/%s)', PLUGIN_PERMKEY, $lowerAction ) ) );
			  exit;
			  }

			  $this->load( 'Page' );
			  $this->Page->sendAccessError( sprintf( 'Sie haben keine Berechtigung zum durchführen dieser Aktion. (%s/%s)', PLUGIN_PERMKEY, $lowerAction ) );
			  }
			 */
		}
	}


	/**
	 *
	 * @param string $addon default is null
	 * @return array/boolean return false if has not an extra backend menu
	 */
	public function getBackendMenu ( $addon = null )
	{

		$ucfirststr = ( is_string($addon) && $addon != '' ? ucfirst(strtolower($addon)) : ucfirst($this->key) );
		if ( !is_string($ucfirststr) )
		{
			return false;
		}

		$className = 'Addon_' . $ucfirststr . '_Config_Base';
		$data      = false;

		if ( is_file(PLUGIN_PATH . $ucfirststr . '/Config/Base.php') )
		{
			include_once( PLUGIN_PATH . $ucfirststr . '/Config/Base.php' );

			if ( class_exists($className, false) && checkClassMethod($className . '/getBackendMenu', 'static') )
			{
				$data = call_user_func($className . '::getBackendMenu', $this->getController()->isBackend());


				if ( is_array($data) )
				{
					foreach ( $data as &$row )
					{
						if ( isset($row[ 'items' ]) && is_array($row[ 'items' ]) )
						{
							foreach ( $row[ 'items' ] as &$r )
							{
								if ( $r[ 'type' ] != 'line' )
								{
									$r[ 'isAddon' ] = true;
								}
							}
						}
					}
				}
			}
		}


		return $data;
	}

	/**
	 * @param $pluginName
	 * @return array|null
	 */
	public static function loadRouteConfig ( $pluginName )
	{

		$ucfirststr = ucfirst($pluginName);

		if ( is_file(PLUGIN_PATH . $ucfirststr . '/Config/Routes.php') )
		{
			$route = array ();
			include( PLUGIN_PATH . $ucfirststr . '/Config/Routes.php' );

			foreach ( $route as &$r )
			{
				$params = null;
				if ( isset($r[ 'params' ]) && is_array($r[ 'params' ]) && isset($r[ 'paramkeys' ]) && is_array($r[ 'paramkeys' ]) )
				{
					$paramKeys = array ();
					$params    = array ();

					$i = 1;
					foreach ( $r[ 'paramkeys' ] as $idx => $mapparam )
					{
						$paramKeys[ $i++ ]   = $mapparam;
						$params[ $mapparam ] = ( isset( $r[ 'params' ][ $idx ] ) ? $r[ 'params' ][ $idx ] : '.+' );
					}
				}

				$r[ 'params' ] = $params;
			}

			return $route;
		}

		return null;
	}

	/**
	 *
	 */
	public function initRoute ()
	{

		$ucfirststr = ucfirst($this->key);

		if ( is_file(PLUGIN_PATH . $ucfirststr . '/Config/Routes.php') )
		{
			$route = array ();
			include( PLUGIN_PATH . $ucfirststr . '/Config/Routes.php' );

			foreach ( $route as &$r )
			{
				$params = null;
				if ( isset($r[ 'params' ]) && is_array($r[ 'params' ]) && isset($r[ 'paramkeys' ]) && is_array($r[ 'paramkeys' ]) )
				{
					$paramKeys = array ();
					$params    = array ();

					$i = 1;
					foreach ( $r[ 'paramkeys' ] as $idx => $mapparam )
					{
						$paramKeys[ $i++ ]   = $mapparam;
						$params[ $mapparam ] = ( isset( $r[ 'params' ][ $idx ] ) ? $r[ 'params' ][ $idx ] : '.+' );
					}
				}

				$r[ 'params' ] = $params;
			}

			$Router = new Router();
			$Router->freeMem();


			$Router->isAddon = true;
			$Router->setDefaultController('plugin');
			$Router->addRouteConfig($ucfirststr, $route);

			$vars = $Router->execute(false)->getVariables();

			//    print_r($vars);exit;
			// Set Router Params to Input
			if ( is_array($vars) )
			{
				$this->Input->setFromRouter($vars);
			}
		}
	}

	/**
	 *
	 * @staticvar array $_plugins
	 * @param sting   $name
	 * @param boolean $assure_installed
	 * @param boolean $silent_fail
	 * @return stdClass
	 */
	public static function getPluginProvider ( $name, $assure_installed = true, $silent_fail = false )
	{

		$name = strtolower($name);

		static $_plugins;
		if ( !is_array($_plugins) )
		{
			$_plugins = self::getInstalledPlugins();
		}

		if ( $assure_installed && !isset( $_plugins[ $name ] ) )
		{
			if ( !$silent_fail )
			{
				Error::raise(sprintf(trans('Plugin `%s` ist nicht installiert!'), $name));
			}

			return null;
		}

		if ( !isset( self::$plugins[ $name ] ) )
		{
			$cls = 'Addon_' . ucfirst($name) . '_Helper_Provider';

			if ( class_exists($cls) )
			{

				$config = array ();
				if ( isset($_plugins[ $name ][ 'config' ]) && $_plugins[ $name ][ 'config' ] )
				{
					$config = self::getConfig($name);
				}

				self::$plugins[ $name ]         = new $cls( $config, $name );
				self::$plugins[ $name ]->config = $config;
				self::$plugins[ $name ]->key    = $name;

				return self::$plugins[ $name ];
			}
			else
			{
				//Error::raise(sprintf(trans('Plugin File `%s` ist nicht vorhanden!'), $plugin_file));

				if ( !$silent_fail )
				{
					Error::raise(sprintf(trans('Plugin Klasse `%s` ist nicht vorhanden!'), $cls));
				}

				return null;
			}
		}

		return self::$plugins[ $name ];
	}

	/**
	 *
	 * @param string $plugin
	 * @param bool   $backend
	 */
	static public function registerPluginPerms ( $plugin, $backend = false )
	{

		$className = 'Addon_' . ucfirst(strtolower($plugin)) . '_Config_Base';


		// Read all permission options
		if ( checkClassMethod($className . '/getPermissions', 'static') )
		{

			$perms = call_user_func($className . '::getPermissions', $backend);
			if ( is_array($perms) )
			{
				self::$_pluginPerms[ 'usergroup' ][ 'plugin_' . strtolower($plugin) ] = $perms;
			}
		}
	}

	/**
	 * @return array
	 */
	static public function getPluginPerms ()
	{

		return self::$_pluginPerms;
	}

	/**
	 * call from controller usergroups
	 *
	 */
	public static function loadPluginPermissions ( $backend = false )
	{

		$plugins = self::getInstalledPlugins();

		foreach ( $plugins as $key => $r )
		{
			if ( isset($r[ 'run' ]) && $r[ 'run' ] )
			{
				self::registerPluginPerms($r[ 'key' ], $backend);
			}
		}
	}

	/**
	 * Is the $controller a plugin and can run then return true.
	 *
	 * @staticvar array $_plugins
	 * @param string $controller
	 * @return boolean
	 */
	public static function isPlugin ( $controller )
	{

		static $_plugins;

		if ( !is_array($_plugins) )
		{
			$_plugins = self::getInteractivePlugins();
		}

		$controller = strtolower($controller);

		foreach ( $_plugins as $key => $r )
		{
			if ( $key === $controller )
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns true if is published
	 *
	 * @staticvar array $_plugins
	 * @param string $controller
	 * @return boolean
	 */
	public static function isPublished ( $controller )
	{

		static $_plugins;

		if ( !is_array($_plugins) )
		{
			$_plugins = self::getInteractivePlugins();
		}

		$controller = strtolower($controller);

		foreach ( $_plugins as $key => $r )
		{
			if ( $key === $controller && $r[ 'published' ] )
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns true if is executable
	 *
	 * @staticvar array $_plugins
	 * @param string $controller
	 * @return boolean
	 */
	public static function isExcecutable ( $controller )
	{

		static $_plugins;

		if ( !is_array($_plugins) )
		{
			$_plugins = self::getInteractivePlugins();
		}

		$controller = strtolower($controller);

		foreach ( $_plugins as $key => $r )
		{
			if ( $key === $controller && $r[ 'run' ] )
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Will return true if the Plugin is offline.
	 *
	 * @param string $name
	 * @return boolean
	 */
	public static function isOffline ( $name )
	{

		$plugs = self::getInteractivePlugins();

		return ( !isset( $plugs[ $name ] ) || $plugs[ $name ][ 'offline' ] || !$plugs[ $name ][ 'published' ] ? true : false );
	}

	/**
	 *
	 * @uses Cache
	 * @uses Database
	 * @return array
	 */
	public static function getInteractivePlugins ()
	{

		$plugins = Cache::get('interactive_plugins');
		if ( is_null($plugins) )
		{
			$plugins = array ();
			$res     = Database::getInstance()->query('SELECT * FROM %tp%plugin WHERE run = 1 OR config = 1 ORDER BY name ASC')->fetchAll();
			foreach ( $res as $row )
			{
				$plugins[ $row[ 'key' ] ] = $row;
			}


			Cache::write('interactive_plugins', $plugins);
		}

		return $plugins;
	}

	/**
	 * @uses Cache
	 * @uses Database
	 * @return array
	 */
	public static function getInstalledPlugins ()
	{

		$plugins = Cache::get('installed_plugins');
		if ( is_null($plugins) )
		{
			$plugins = array ();
			$res     = Database::getInstance()->query('SELECT * FROM %tp%plugin')->fetchAll();
			foreach ( $res as $row )
			{
				$plugins[ $row[ 'key' ] ] = $row;
			}
			Cache::write('installed_plugins', $plugins);
		}

		return $plugins;
	}


	/**
	 * will only register all plugin events.
	 * See plugin config getModulDefinition()
	 *
	 */
	public static function initPlugins ()
	{

		// read available plugins
		$plugins = glob(PLUGIN_PATH . '*', GLOB_ONLYDIR);
		foreach ( $plugins as $plugin )
		{
			$key       = basename($plugin);
			$className = 'Addon_' . ucfirst($key) . '_Config_Base';

			if ( class_exists($className, true) )
			{
				if ( checkClassMethod($className . '/getModulDefinition', 'static') )
				{
					call_user_func($className . '::getModulDefinition', true);
				}
			}
		}
	}


	/**
	 *
	 * @param string $key
	 * @uses Cache
	 * @uses Database
	 * @return array
	 */
	public static function getConfig ( $key )
	{

		$config = Cache::get('plugin_config_' . $key);

		if ( !is_array($config) )
		{
			$res    = Database::getInstance()->query('SELECT * FROM %tp%plugin_setting WHERE plugin = ?', $key)->fetchAll();
			$config = array ();
			foreach ( $res as $setting )
			{
				$config[ $setting[ 'name' ] ] = $setting[ 'value' ];
			}
			Cache::write('plugin_config_' . $key, $config);
		}

		return $config;
	}

	/**
	 * @param $key
	 */
	public static function initPermissions ( $key )
	{

	}

	/**
	 * @uses Cache
	 * @uses Database
	 * @param string $key
	 * @param array  $config
	 */
	public static function saveConfig ( $key, $config )
	{

		$db = Database::getInstance();
		$db->begin();
		$db->query('DELETE FROM %tp%plugin_setting WHERE plugin = ' . $db->quote($key));
		foreach ( $config as $name => $value )
		{
			if ( is_array($value) )
			{
				$value = serialize($value);
			}
			$db->query('INSERT INTO %tp%plugin_setting SET plugin = ' . $db->quote($key) . ', name = ' . $db->quote($name) . ', value = ' . $db->quote($value));
		}
		Cache::refresh();
		$db->commit();
	}

    /**
     * Render provider Template only!
     *
     * @param array       $data
     * @param bool|string $template default is false
     * @param boolean     $return   default is null
     * @param string      $getBlock default is null
     * @uses Library
     * @uses User
     * @return string
     */
    public function renderProviderTemplate ( $data = array (), $template = false, $return = null, $getBlock = null )
    {

        $data[ 'pluginpath' ] = PLUGIN_URL_PATH . ucfirst($this->key) . '/';


        if ( CONTROLLER === 'pluginmanager' && ACTION === 'run' )
        {
            $return                     = array ();
            $return[ 'renderTemplate' ] = true;
            $return[ 'data' ]           = $data;
            $return[ 'template' ]       = ( defined('ADM_SCRIPT') && ADM_SCRIPT ? 'backend/' : 'frontend/' ) . $template;
            $return[ 'key' ]            = $this->key;

            return $return;
        }
        else
        {
            $path = '';
            if ( defined('ADM_SCRIPT') )
            {
                $path = Library::formatPath(PLUGIN_PATH . ucfirst($this->key) . '/template/backend/') . $template . '.html';
            }
            else
            {
                $skinid = User::getSkinId();

                $path = Library::formatPath(SKIN_PATH . $skinid . '/html/plugin_' . $this->key . '/') . $template . '.html';

                if ( !is_file($path) )
                {
                    $path = Library::formatPath(PLUGIN_PATH . ucfirst($this->key) . '/template/frontend/') . $template . '.html';
                }
            }

            if ( !is_file($path) )
            {
                Error::raise(sprintf(trans('Plugin template `%s` does not exist.'), $template));
            }

            $ob = ob_get_contents();
            ob_clean();

            $data[ 'pluginkey' ] = $this->key;
            /*
            $this->load('Template');
            $this->Template->isProvider = true;


            Library::disableErrorHandling();
            $output = $this->Template->renderTemplate($path, $data, $return, $getBlock);
            Library::enableErrorHandling();
            $this->Template->isProvider = false;
            */

            $this->load('Template');
            $data = array_merge($this->Template->getTemplateData(), $data);


            $tpl = new Template();



#print_r($data);exit;

            $output = $tpl->renderTemplate($path, $data, $return, $getBlock);


            return $ob . $output;
        }
    }



	/**
	 *
	 * @param array       $data
	 * @param bool|string $template default is false
	 * @param boolean     $return   default is null
	 * @param string      $getBlock default is null
	 * @uses Library
	 * @uses User
	 * @return string
	 */
	public function renderTemplate ( $data = array (), $template = false, $return = null, $getBlock = null )
	{

		$data[ 'pluginpath' ] = PLUGIN_URL_PATH . ucfirst($this->key) . '/';


		if ( CONTROLLER === 'pluginmanager' && ACTION === 'run' )
		{
			$return                     = array ();
			$return[ 'renderTemplate' ] = true;
			$return[ 'data' ]           = $data;
			$return[ 'template' ]       = ( defined('ADM_SCRIPT') && ADM_SCRIPT ? 'backend/' : 'frontend/' ) . $template;
			$return[ 'key' ]            = $this->key;

			return $return;
		}
		else
		{
			$path = '';
			if ( defined('ADM_SCRIPT') )
			{
				$path = Library::formatPath(PLUGIN_PATH . ucfirst($this->key) . '/template/backend/') . $template . '.html';
			}
			else
			{
				$skinid = User::getSkinId();

				$path = Library::formatPath(SKIN_PATH . $skinid . '/html/plugin_' . $this->key . '/') . $template . '.html';

				if ( !is_file($path) )
				{
					$path = Library::formatPath(PLUGIN_PATH . ucfirst($this->key) . '/template/frontend/') . $template . '.html';
				}
			}

			if ( !is_file($path) )
			{
				Error::raise(sprintf(trans('Plugin template `%s` does not exist.'), $template));
			}

            $ob = ob_get_contents();
            ob_clean();

			$data[ 'pluginkey' ] = $this->key;
            /*
            $this->load('Template');
            $this->Template->isProvider = true;


            Library::disableErrorHandling();
            $output = $this->Template->renderTemplate($path, $data, $return, $getBlock);
            Library::enableErrorHandling();
            $this->Template->isProvider = false;
            */

            $this->load('Template');



            #$data = array_merge($this->Template->getTemplateData(), $data);
#print_r($data);exit;

			$output = $this->Template->renderTemplate($path, $data, $return, $getBlock);


			return $ob . $output;
		}
	}

	/**
	 *
	 * @param type $r
	 * @param type $key
	 * @uses Library
	 * @uses Error
	 * @uses Setup
	 * @uses SystemManager
	 * @uses Cache
	 * @uses Database
	 * @uses User
	 */
	public static function installPlugin ( $r, $key )
	{

		$path = Library::formatPath(PLUGIN_PATH . $key . '/' . $key . '.xml');
		if ( !is_file($path) )
		{
			if ( $r !== null )
			{
				Error::raise(sprintf(trans('Cannot load configuration file for plugin `%s` - installation aborted.'), $key));
			}
		}
		else
		{
			$xml = simplexml_load_file($path, 'SimpleXMLElement', LIBXML_NOCDATA);

			$plugin = self::getPlugin($key, false);


			if ( is_file(PLUGIN_PATH . $key . '/install.php') )
			{
				include_once( PLUGIN_PATH . $key . '/install.php' );
				$class = ucfirst($key) . 'PluginInstaller';
				if ( class_exists($class, false) && is_callable(array (
				                                                      $class,
				                                                      'install'
				                                                ))
				)
				{
					call_user_func(array (
					                     $class,
					                     'install'
					               ));
				}
			}


			// check for table schemas
			if ( is_file(PLUGIN_PATH . $key . '/schema/') )
			{
				Setup::setPath(PLUGIN_PATH . $key . '/schema/');
				Setup::addNewTables();
				Setup::updateTableFields();
				Setup::processIndexesAndConstraints();
				Setup::addDefaultData();
			}
			$configurable = !empty( $plugin->is_configurable ) ? $plugin->is_configurable : false;
			$runnable     = !empty( $plugin->is_runnable ) ? $plugin->is_runnable : false;

			$add_arr = array (
				'key'         => $key,
				'name'        => (string)$xml->name,
				'version'     => (string)$xml->version,
				'description' => (string)$xml->description,
				'author'      => (string)$xml->author,
				'website'     => (string)$xml->website,
				'run'         => $runnable,
				'config'      => $configurable
			);

			$db  = Database::getInstance();
			$str = $db->compile_db_insert_string($add_arr);
			$sql = "INSERT INTO %tp%plugin ({$str['FIELD_NAMES']}) VALUES ({$str['FIELD_VALUES']})";
			$db->query($sql);


			Cache::delete('interactive_plugins');
			Cache::delete('installed_plugins');
			Cache::delete('menu_user_' . User::getUserId());
			SystemManager::syncEventHooks();


			Library::log(sprintf("Installed plugin %s.", $key), $key, 'info');
		}
	}

	/**
	 *
	 * @param string $key
	 * @uses Library
	 * @uses Setup
	 * @uses SystemManager
	 * @uses Cache
	 * @uses Database
	 */
	public static function updatePlugin ( $key )
	{

		Cache::delete('interactive_plugins');
		Cache::delete('installed_plugins');
		Cache::delete('plugin_config_' . $key);
		SystemManager::syncEventHooks();

		$path = PLUGIN_PATH . $key . '/schema/';
		if ( is_file($path) )
		{
			Setup::setPath($path);
			Setup::runAllActions();
		}

		Library::log(sprintf("Updated plugin %s.", $key), $key, 'info');
	}

	/**
	 * @param null $definition
	 */
	public function setDefinition ( $definition )
	{

		$this->_definition = $definition;
	}

}
