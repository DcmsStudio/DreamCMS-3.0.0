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
 * @file         Abstract.php
 */
abstract class Controller_Abstract extends Loader
{

    /**
     * @var bool
     */
    protected $_modulRequireLogin = false;

    /**
     * @var bool
     */
    protected $_modulRequirePerms = false;

    /**
     * @var bool
     */
    private $_renderInited = false;

    /**
     * @var array
     */
    private $_renderAssings = array();

    /**
     * @var int
     */
    protected $lastEditTimeout = 2592000; // 30 days
    /**
     * @var int
     */

    protected $lastEditLimit = 50;

    /**
     * @var null
     */
    protected $_ControllerMenu = null;

    /**
     *
     * @var integer
     */
    protected $_documentID = 0;

    /**
     *
     * @var array
     */
    protected $_aliasRegistryData = null;

    /**
     *
     * @var object
     */
    public $_model = null;

    /**
     *
     * @var integer
     */
    protected $_applicationMode = null;

    /**
     * @var bool
     */
    protected $isApplication = false;

    /**
     * @var bool
     */
    protected $isAddon = false;

    /**
     * @var null
     */
    protected $addonName = null;

    /**
     *
     * @var array
     */
    protected static $_socialNetworkData = null;

    /**
     * @var Model
     */
    public $model = null;

    protected static $_plugins = null;


    protected static $_RequestDataFromRouter;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->_applicationMode = $this->getApplication()->getMode();
    }

    public function __destruct()
    {

        parent::__destruct();
        $this->_model = null;
    }

    /**
     *
     * @param string $method
     * @param        mixed /array $arguments
     */
    public function __call($method, $arguments)
    {
        trigger_error( 'Class `' . get_class( $this ) . '` has no method `' . $method . '`.', E_USER_ERROR );
    }

    /**
     * @param mixed $data
     */
    public function setRouterRequestData($data)
    {
        self::$_RequestDataFromRouter = $data;
    }

    /**
     * init all Base Objects
     *
     * @param Controller $_controllerInstance
     * @param bool $isAddon
     * @return Controller_Abstract
     */
    public function _initController($_controllerInstance = null, $isAddon = false)
    {

        $isBackend = $this->isBackend();

        if ( !$isBackend )
        {
            $this->load( 'SideCache' );
        }

        /**
         * If is a Plugin then check if is active.
         * If the Plugin not active send error 404.
         *
         */
        if ( is_string( $isAddon ) )
        {
            if ( !Plugin::isActive( strtolower( $isAddon ) ) )
            {
                $this->load( 'Input' );
                $this->load( 'Page' );
                $this->load( 'Action' );
                $this->load( 'Router' );
                $this->load( 'Document' );
                $this->load( 'Output' );
                $this->load( 'Template' );


                $this->Page->error( 404, trans( 'Diese Seite ist nicht aktiv.' ) );
            }
            $this->isAddon = true;
        }

        if ( !$isBackend && ( $_controllerInstance === false || $_controllerInstance === null ) )
        {
            if ( Settings::get( 'pagedefaultenablecaching', false ) )
            {
                $this->SideCache->enable();
                $this->SideCache->getCache();
            }
            else
            {
                $this->SideCache->disable();
            }
        }


        /**
         * Load first the rest of all basic Framework Classes
         */
        $this->load( 'Input' );
        $this->load( 'Page' );
        $this->load( 'Action' );
        $this->load( 'Router' );
        $this->load( 'Document' );
        $this->load( 'Output' );
        $this->load( 'Template' );


        if ( !$isBackend )
        {
            $this->load( 'Breadcrumb' );
        }


        if ( $_controllerInstance instanceof Controller )
        {
            $this->setControllerInstance( $_controllerInstance );
            $this->Router->setDefaultController( ucfirst( strtolower( $this->getApplication()->getOption( 'defaultController' ) ) ) );
            $this->Router->setDefaultAction( ucfirst( strtolower( $this->getApplication()->getOption( 'defaultAction' ) ) ) );

            /**
             * register Module Routes to the Router
             */
            $modules = $this->getApplication()->getModuleNames();
            foreach ( $modules as $module )
            {
                $config = $this->getApplication()->loadRouteConfig( $module );

                if ( $config !== null )
                {
                    $this->Router->addRouteConfig( $module, $config );
                }
                else
                {
                    $this->Router->addRouteConfig( $module, null );
                }
            }
        }


        return $this;
    }

    /**
     *
     * @param string $modul
     * @return int
     */
    public function getModulID($modul = null)
    {

        $m        = new Module();
        $registry = $m->getModul( ( $modul ? $modul : strtolower( CONTROLLER ) ) );

        return $registry[ 'id' ];
    }

    /**
     * When is backend the returns true
     * @return bool
     */
    public function isBackend()
    {
        return Application::isBackend();
    }

    /**
     * When is frontend the returns true
     * @return bool
     */
    public function isFrontend()
    {
        return Application::isFrontend();
    }

    /**
     * returns the giving or current
     * Modul Configuration
     *
     * @param string|null $modulName default is null
     * @return mixed
     */
    public function getModulConfig($modulName = null)
    {

        if ( is_string( $modulName ) && !empty( $modulName ) )
        {
            $className = ucfirst( strtolower( $modulName ) ) . '_Config_Base';
        }
        else
        {
            $className = ucfirst( strtolower( CONTROLLER ) ) . '_Config_Base';
        }

        return call_user_func( $className . '::getModulDefinition' );
    }

    /**
     *
     * @param string $settingsKey
     * @param mixed $default the default is null
     * @return mixed
     *
     * @throws BaseExeption
     */
    public function getModulOption($settingsKey, $default = null)
    {

        $config = $this->getApplication()->getSystemConfig();
        $mod    = strtolower( CONTROLLER );


        if ( !( $config instanceof Config ) )
        {
            throw new BaseExeption( 'Invalid Configuration!' );
        }

        $opt = $config->get( $mod );

        if ( !( $config instanceof Config ) )
        {
            return $default;
        }


        $opts = ( ( $opt instanceof Config ) ? $opt->toArray() : $opt );


        return ( isset( $opts[ $settingsKey ] ) ? $opts[ $settingsKey ] : $default );
    }

    /**
     * returns the Modul Label if exists
     *
     * @return string|null
     */
    public function getModulLabel()
    {

        $definition = $this->getModulConfig();

        return ( isset( $definition[ 'modulelabel' ] ) ? $definition[ 'modulelabel' ] : null );
    }

    /**
     * returns the giving or current
     * Modul Permission Keys
     *
     * @param string $modulName default is null
     * @param bool $useBackend default is null
     *
     * @return array
     */
    public function getModulPermissions($modulName = null, $useBackend = null)
    {
        static $useBackend;

        if ( !is_bool( $useBackend ) )
        {
            $useBackend = $this->isBackend();
        }

        if ( is_string( $modulName ) && !empty( $modulName ) )
        {
            $className = ucfirst( strtolower( $modulName ) ) . '_Config_Base';
        }
        else
        {
            $className = ucfirst( strtolower( CONTROLLER ) ) . '_Config_Base';
        }

        return call_user_func( $className . '::getControllerPermissions', $useBackend );
    }

    /**
     * returns the Class Name of the current Controller Action
     *
     * @throws BaseException
     * @return string
     */
    public function getModulActionClass()
    {
        if ( $this->isApplication )
        {
            return 'App_Action_' . ucfirst( strtolower( ACTION ) );
        }

        $pluginInput = $this->input( 'plugin' );

        if ( is_string($pluginInput) && !empty($pluginInput) )
        {
            $str = preg_replace( '#([^a-z0-9_]*)#is', '', $pluginInput );

            if ( $str !== $pluginInput )
            {
                throw new BaseException( 'Invalid Plugin!!!' );
            }

            if ( CONTROLLER === 'Plugin' && $str )
            {
                return 'Addon_' . ucfirst( strtolower( $str ) ) . '_Action_' . ucfirst( strtolower( ACTION ) );
            }

            throw new BaseException( 'Invalid Plugin!!!' );
        }

        if ( $this->isAddon )
        {
            if ( is_string( $this->addonName ) && $this->addonName !== '' )
            {

                $str = preg_replace( '#([^a-z0-9_]*)#is', '', $this->addonName );
                if ( $str !== $this->addonName )
                {
                    throw new BaseException( 'Invalid Plugin!!! Name: ' . $this->addonName );
                }

                if ( CONTROLLER === 'Plugin' && $str )
                {
                    return 'Addon_' . ucfirst( strtolower( $str ) ) . '_Action_' . ucfirst( strtolower( ACTION ) );
                }
            }

            throw new BaseException( 'Invalid Plugin!!! Name: ' . $this->addonName );
        }


        return ucfirst( strtolower( CONTROLLER ) ) . '_Action_' . ucfirst( strtolower( ACTION ) );
    }

    /**
     *
     * @param null $modulName
     * @return array
     */
    public function getModulBackendMenu($modulName = null)
    {
        static $useBackend;

        if ( $this->isAddon )
        {
            $this->load( 'Plugin' );

            $menuData = $this->Plugin->getBackendMenu();

            if ( is_array( $menuData ) )
            {
                return $menuData;
            }
        }

        if ( is_string( $modulName ) && !empty( $modulName ) )
        {
            $className = ucfirst( strtolower( $modulName ) ) . '_Config_Base';
        }
        else
        {
            $className = ucfirst( strtolower( CONTROLLER ) ) . '_Config_Base';
        }

        if ( !is_bool( $useBackend ) )
        {
            $useBackend = $this->isBackend();
        }

        $data = null;

        if ( class_exists( $className ) && checkClassMethod( $className . '/getBackendMenu', 'static' ) )
        {
            $data = call_user_func( $className . '::getBackendMenu', $useBackend );
        }


        if ( !is_array( $data ) )
        {
            return array();
        }

        return $data;
    }

    /**
     *
     * @param string $className
     * @return string
     */
    public function getModelString($className)
    {

        $modelStr = false;
        //$isAddon  = false;

        if ( substr( $className, 0, 5 ) === 'Addon' )
        {
            $exp = explode( '_', $className );
            //$isAddon  = ucfirst(strtolower($exp[ 1 ]));
            $modelStr = ucfirst( strtolower( $exp[ 0 ] ) ) . '_' . ucfirst( strtolower( $exp[ 1 ] ) );

            /**
             *
             */
            define( 'PLUGIN_MODEL', $modelStr );
        }

        return $modelStr;
    }

    /**
     * Execute the Action
     */
    public function executeAction()
    {

        $className = $this->getController()->getModulActionClass();

        $modelStr = $this->getModelString( $className );

        $isAddon  = $modelStr ? true : false;


        if ( $isAddon )
        {
            $exp             = explode( '_', $className );
            $isAddon         = ucfirst( strtolower( $exp[ 1 ] ) );
            $this->isAddon   = true;
            $this->addonName = $isAddon;
        }



        if ( $this->isAddon )
        {
            $this->load( 'Plugin' );
            $this->Plugin->initPlugin( Plugin::getConfig( $this->addonName ), $this->addonName );


            $actionInput = $this->input( 'action' );
            $str         = preg_replace( '#([^a-z0-9_]*)#is', '', $actionInput );


            if ( $actionInput != '' && $str !== $actionInput )
            {
                throw new BaseException( 'Invalid Action!!!' );
            }

            $pluginInput = $this->input( 'plugin' );
            $className   = false;
            if ( $pluginInput )
            {
                // Cleanup Input String
                $str = preg_replace( '#([^a-z0-9_]*)#is', '', $pluginInput );
                if ( $str !== $pluginInput )
                {
                    throw new BaseException( 'Invalid Plugin!!!' );
                }


                if ( CONTROLLER === 'Plugin' && $str )
                {
                    /**
                     * Patch for the Shortlink Head Tag
                     */
                    if ( !$this->input( 'action' ) )
                    {
                        $this->Input->set( 'action', 'run' );
                        $actionInput = 'run';
                    }
                    /**
                     * ------ END Patch for the Shortlink Head Tag
                     */


                    $className = 'Addon_' . ucfirst( strtolower( $str ) ) . '_Action_' . ucfirst( strtolower( $actionInput ) );
                }
                else
                {
                    throw new BaseException( 'Invalid Plugin!!!' );
                }
            }

            if ( $this->isAddon && !$pluginInput )
            {
                if ( is_string( $this->addonName ) )
                {
                    $str = preg_replace( '#([^a-z0-9_]*)#is', '', $this->addonName );
                    if ( $this->addonName != '' && $str !== $this->addonName )
                    {
                        throw new BaseException( 'Invalid Action!!!' );
                    }


                    if ( CONTROLLER === 'Plugin' && $str )
                    {
                        $className = 'Addon_' . ucfirst( strtolower( $str ) ) . '_Action_' . ucfirst( strtolower( ACTION ) );


                        /**
                         * Patch for the Shortlink Head Tag
                         */
                        if ( !$this->input( 'action' ) )
                        {
                            $this->Input->set( 'action', 'run' );
                        }

                        if ( !$this->input( 'plugin' ) )
                        {
                            $this->Input->set( 'plugin', strtolower( $str ) );
                        }
                        /**
                         * ------ END Patch for the Shortlink Head Tag
                         */
                    }
                    else
                    {
                        throw new BaseException( 'Invalid Plugin!!!' );
                    }
                }
                else
                {
                    throw new BaseException( 'Invalid Plugin!!!' );
                }
            }

            /**
             *
             */
            define( 'PLUGIN', strtolower( $str ) );


            if ( !$className )
            {
                $className = 'Addon_' . ucfirst( strtolower( $str ) ) . '_Action_Run';
            }


            unset( $this->__RunController );

            $this->load( $className, '_RunAction' );

            $executer = $this->_RunAction->_initController( false, $isAddon );

            if ( !$this->isBackend() )
            {
                $executer->load( 'Breadcrumb' );
                $executer->load( 'SideCache' );
            }

            $executer->isAddon   = true;
            $executer->addonName = $this->addonName;

            if ( $executer->model === null )
            {
                $executer->model = Model::getModelInstance( $modelStr );
                $this->model     = $executer->model;
            }

            if ( !is_object( $this->model ) || !is_object( $executer->model ) )
            {
                throw new BaseException( 'Invalid Model Instance' );
            }

            $executer->load( 'Plugin' );
            $executer->Plugin->initPlugin( Plugin::getConfig( $isAddon ), $isAddon );

            // Permission Check?
            $executer->Plugin->checkPermsBeforeExecuteAction();

            if ( $this->isBackend() )
            {
                $beMenu = $executer->Plugin->getBackendMenu();
                if ( is_array( $beMenu ) )
                {
                    $this->getController()->addControllerMenu( $beMenu );
                }
            }

            if ( IS_AJAX && $this->_post( 'contentanalyse' ) )
            {
                $data = $executer->Document->analyseDocument( $this->_post( 'content' ) );

                Ajax::Send( true, $data );
                exit;
            }

            Hook::run( 'onBeforeRunController' ); // {CONTEXT: framework, DESC: Dieses Ereignis wird ausgelöst, kurz bevor der Controller ausgeführt wird.}
            // Execute
            $executer->execute();
        }
        else
        {
            unset( $this->__RunController );

            $className = $this->getController()->getModulActionClass();
            $this->load( $className, '_RunAction' );


            $executer = $this->_RunAction->_initController( false, $isAddon );
            if ( $executer->model === null )
            {
                $executer->model = Model::getModelInstance( $modelStr );
                $this->model     = $executer->model;
            }


            if ( IS_AJAX && $executer->_post( 'contentanalyse' ) )
            {
                $data = $executer->Document->analyseDocument( $this->_post( 'content' ) );

                Ajax::Send( true, $data );
                exit;
            }


            if ( !$this->isBackend() )
            {
                $executer->load( 'Breadcrumb' );
                $executer->load( 'SideCache' );
            }

            Hook::run( 'onBeforeRunController' ); // {CONTEXT: framework, DESC: Dieses Ereignis wird ausgelöst, kurz bevor der Controller ausgeführt wird.}

            $executer->execute();
        }
    }

    /**
     *
     * @param string $headerMode default is null
     * @param string $errorOutput default is null
     */
    public function checkPermsBeforeExecuteAction($headerMode = null, $errorOutput = null)
    {

        // $permissions = $this->getModulPermissions();

        $this->_modulRequireLogin = Action::requireLogin();
        $this->_modulRequirePerms = Action::requirePermission();


        if ( $this->_modulRequireLogin || $this->_modulRequirePerms )
        {
            // Login is required?
            if ( $this->_modulRequireLogin === true && !User::isLoggedIn() )
            {
                if ( $headerMode !== null )
                {

                    if ( IS_AJAX )
                    {
                        Ajax::Send( false, array(
                            'sessionerror' => true,
                            'msg'          => sprintf( 'Diese Funktion erfordert, dass Sie eingeloggt sind. (%s/%s)', CONTROLLER, ACTION )
                        ) );
                    }


                    $mime = Library::getMimeType( $headerMode );
                    header( 'Content-Type: ' . $mime );


                    if ( $errorOutput === null )
                    {
                        $errorOutput = sprintf( 'Diese Funktion erfordert, dass Sie eingeloggt sind. (%s/%s)', CONTROLLER, ACTION );
                    }

                    die( $errorOutput );
                }
                else
                {
                    if ( IS_AJAX )
                    {
                        Ajax::Send( false, array(
                            'sessionerror' => true,
                            'msg'          => sprintf( 'Diese Funktion erfordert, dass Sie eingeloggt sind. (%s/%s)', CONTROLLER, ACTION )
                        ) );
                    }
                    $this->load( 'Page' );
                    $this->Page->sendAccessError( sprintf( 'Diese Funktion erfordert, dass Sie eingeloggt sind. (%s/%s)', CONTROLLER, ACTION ) );

                    // Error::raise( sprintf( 'Diese Funktion erfordert, dass Sie eingeloggt sind. (%s/%s)', CONTROLLER, ACTION ) );
                }
            }


            // special perms required?
            if ( $this->_modulRequirePerms )
            {
                if ( !Permission::hasControllerActionPerm( CONTROLLER . '/' . ACTION ) )
                {

                    if ( IS_AJAX )
                    {
                        Ajax::Send( false, array(
                            'permissionerror' => true,
                            'msg'             => sprintf( 'Sie haben keine Berechtigung zum durchführen dieser Aktion. (%s/%s)', CONTROLLER, ACTION )
                        ) );
                        exit;
                    }
                    $this->load( 'Page' );
                    $this->Page->sendAccessError( sprintf( 'Sie haben keine Berechtigung zum durchführen dieser Aktion. (%s/%s)', CONTROLLER, ACTION ) );

                    //Error::raise( sprintf( 'Sie haben keine Berechtigung zum durchführen dieser Aktion. (%s/%s)', CONTROLLER, ACTION ) );
                }
            }
        }
        else
        {
            /*
              if ( !Permission::hasControllerActionPerm( CONTROLLER . '/' . ACTION ) )
              {
              if ( IS_AJAX )
              {
              Ajax::Send( false, array(
              'permissionerror' => true,
              'msg'             => sprintf( 'Sie haben keine Berechtigung zum durchführen dieser Aktion. (%s/%s)', CONTROLLER, ACTION ) ) );
              exit;
              }
              $this->Page->sendAccessError( sprintf( 'Sie haben keine Berechtigung zum durchführen dieser Aktion. (%s/%s)', CONTROLLER, ACTION ) );

              //Error::raise( sprintf( 'Sie haben keine Berechtigung zum durchführen dieser Aktion. (%s/%s)', CONTROLLER, ACTION ) );
              } */
        }


        if ( $this->isAddon )
        {
            die( 'Perm Check' );
        }
    }
    /**
     * Is the $controller a plugin then return true.
     *
     * @staticvar array $_plugins
     * @param string $controller
     * @return boolean
     */
    public function _isPlugin($controller)
    {
        if ( !is_array( self::$_plugins ) )
        {
            self::$_plugins = Plugin::getInteractivePlugins();
        }

        $controller = strtolower( $controller );
        foreach ( self::$_plugins as $key => $r )
        {
            if ( $key === $controller )
            {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $controller
     * @return boolean
     */
    public function isPluginPublished($controller) {
        if ( !is_array( self::$_plugins ) )
        {
            self::$_plugins = Plugin::getInteractivePlugins();
        }

        $controller = strtolower( $controller );

        foreach ( self::$_plugins as $key => $r )
        {
            if ( $key === $controller && $r[ 'published' ] )
            {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $controller
     * @return boolean
     */
    public function canRunPlugin($controller) {
        if ( !is_array( self::$_plugins ) )
        {
            self::$_plugins = Plugin::getInteractivePlugins();
        }

        $controller = strtolower( $controller );

        foreach ( self::$_plugins as $key => $r )
        {
            if ( $key === $controller && $r[ 'run' ] && $r[ 'published' ] )
            {
                return true;
            }
        }

        return false;
    }
    /**
     * Is the $controller a plugin and can run then return true.
     *
     * @staticvar array $_plugins
     * @param string $controller
     * @return boolean
     */
    public function isPlugin($controller)
    {

        if ( !is_array( self::$_plugins ) )
        {
            self::$_plugins = Plugin::getInteractivePlugins();
        }

        $controller = strtolower( $controller );

        foreach ( self::$_plugins as $key => $r )
        {
            if ( $key === $controller && $r[ 'run' ] && $r[ 'published' ] )
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Assign Template data
     *
     * @param string $key
     * @param mixed $value
     * @return Controller_Abstract
     */
    public function assign($key, $value = null)
    {

        $this->_renderAssings[ $key ] = $value;

        return $this;
    }

    /**
     * get all pages by typ 'rootpage'
     *
     * @return array
     */
    public function getRootPages()
    {

        return $this->db->query( 'SELECT * FROM %tp%page ORDER BY title' )->fetchAll();
    }

    /**
     * get page by typ 'rootpage' use the ID
     *
     * @staticvar array $root
     * @param integer $id
     * @return array
     */
    public function getRootPage($id = 0)
    {

        static $root;

        if ( !$id )
        {
            $id = Session::get( 'WEBSITE_ID' );
        }

        if ( !is_array( $root ) )
        {
            $root = array();
        }

        if ( is_array( $root ) && !isset( $root[ $id ] ) )
        {
            $root[ $id ] = $this->db->query( 'SELECT * FROM %tp%page WHERE id = ?', $id )->fetch();
        }

        return $root[ $id ];
    }

    /**
     * save the Gui Language in the Session and
     * change the Gui.
     *
     * @param string $lang default is "de"
     */
    public function setGuiLanguage($lang = 'de')
    {

        Session::save( 'GuiLang', $lang );
    }

    /**
     * get the Gui language
     *
     * @return string
     */
    public function getGuiLanguage()
    {

        return Session::get( 'GuiLang', 'de' );
    }

    /**
     * Will set the content language. If is not Translated content then will get automaticly
     * to the basic content translation.
     *
     * @param string $lang
     */
    public function setContentLanguage($lang = null)
    {

        Session::save( 'ContentLang', $lang );
    }

    /**
     * returns the global content translation
     *
     * @return string/null if is null use the System configuration
     */
    public function getContentLanguage()
    {

        return Session::get( 'ContentLang', null );
    }

    /**
     * used from the current Modul
     *
     * @param integer $articleId default is null
     * @param string $documentType the modul (news, blog, forum etc.) default is null
     */
    public function setCurrentDocument($articleId = null, $documentType = null)
    {

        Session::save( 'CurrentDocumentID', $articleId );
        Session::save( 'CurrentDocumentType', $documentType );
    }

    /**
     *
     */
    public function refreshLocation()
    {

        header( "Pragma: no-cache" );
        header( "Cache-Control: no-cache, must-revalidate" ); // HTTP/1.1
        header( 'Location: ' . $_SERVER[ 'REQUEST_URI' ] );

        exit;
    }

    /**
     * return the current document name
     *
     * @param string $default
     * @return string
     */
    public function getDocumentName($default = 'index')
    {

        if ( defined( 'DOCUMENT_NAME' ) && DOCUMENT_NAME != '' )
        {
            return str_replace( '.' . DOCUMENT_EXTENSION, '', DOCUMENT_NAME );
        }

        return $default;
    }

    /**
     * Generate an URL on the current rewriteURL setting and return it
     *
     * @param array $arrRow
     * @param string $strParams
     * @param bool $addWebsiteUrl
     * @return string
     */
    public function generateUrl($arrRow, $strParams = '', $addWebsiteUrl = false)
    {

        $strUrl = /*( Settings::get( 'mod_rewrite', false ) ? '' : 'index.php/' ) . */$strParams . ( strlen( $arrRow[ 'alias' ] ) ?
                $arrRow[ 'alias' ] : $arrRow[ 'id' ] ) . ( strlen( $arrRow[ 'suffix' ] ) ? '.' . $arrRow[ 'suffix' ] :
                '.' . Settings::get( 'mod_rewrite_suffix', 'html' ) );

        if ( substr( $strUrl, 0, 1 ) == '/' )
        {
            $strUrl = substr( $strUrl, 1 );
        }

        if ( $addWebsiteUrl )
        {
            if ( substr( Settings::get( 'portalurl' ), 0, 1 ) !== '/' )
            {
                $strUrl = Settings::get( 'portalurl' ) . '/' . $strUrl;
            }
            else
            {
                $strUrl = Settings::get( 'portalurl' ) . $strUrl;
            }
        }


        return $strUrl;
    }

    /**
     *
     * @param type $param
     * @deprecated since version 3.0.x
     */
    public function generatePaging($param)
    {

    }

    /**
     * Prüft ob ein dokument in bearbeitung ist. Alle Dokumente
     * die sich in Bearbeitung befinden werden im Frontend nicht sichtbarsein
     * bis diese endgültig gespeichert werden.
     *
     * @staticvar type $drafts
     * @param string $location
     * @return bool
     */
    public function isDraft($location)
    {

        static $drafts;

        $location = str_replace( '&', '&amp;', str_replace( '&amp;', '&', $location ) );

        if ( !is_array( $drafts ) )
        {
            $this->DRAFT_ICON = '<img src="' . BACKEND_IMAGE_PATH . 'document_mark_as_final.png" width="16" height="16" title="' . trans( 'Inhalt befindet sich in Bearbeitung' ) . '" alt=""/>';

            $drafts = $this->db->query( 'SELECT controller, contentlocation FROM %tp%drafts GROUP BY contentlocation LIMIT 200' )->fetchAll();
            if ( !is_array( $drafts ) )
            {
                $drafts = array();
            }
        }

        foreach ( $drafts as $r )
        {
            if ( $r[ 'controller' ] === CONTROLLER && strstr( $r[ 'contentlocation' ], $location ) !== false )
            {
                return true;
            }
        }

        return false;
    }

    /**
     * get publishing state icon and return the Icon
     *
     * @param integer $published
     * @param integer $id
     * @param integer $pubon
     * @param integer $puboff
     * @param string $publishlink default is null
     * @param string $onAfterChangeCallback default is null
     *
     * @return string
     */
    public function getGridState($published, $id, $pubon = 0, $puboff = 0, $publishlink = null, $onAfterChangeCallback = null)
    {

        //$published = ( $published == '' ? 0 : $published);
        $now = time();
        $alt = $img = '';

        if ( $published == PUBLISH_MODE )
        {
            $img = 'online.gif';
            $alt = trans( 'Veröffentlicht' );
        }
        elseif ( $published == TIME_MODE )
        {
            if ( $pubon > 0 && ( $pubon < $now || $puboff > 0 && $puboff > $now ) )
            {
                $img = 'clock.png';
                $alt = trans( 'Zeitgesteuert Veröffentlicht' );


                $alt .= "\n" . ( $pubon > 0 ? "von " . date( 'd.m.Y', $pubon ) : '' ) . ( $puboff > 0 ? ' bis ' . date( 'd.m.Y', $puboff ) : '' );


            }
            elseif ( ( ( $pubon > 0 && $pubon > $now ) || ( $puboff > 0 && $now > $puboff ) ) )
            {
                $img = 'offline.gif';
                $alt = trans( 'nicht Veröffentlicht da abgelaufen' );
            }
        }

        if ( $published == UNPUBLISH_MODE )
        {
            $img = "offline.gif";
            $alt = trans( 'nicht Veröffentlicht' );
        }

        if ( $published == ARCHIV_MODE )
        {
            $img = 'archive.png';
            $alt = trans( 'Ariviert' );
        }

        if ( $published == DRAFT_MODE )
        {
            $img = 'document_mark_as_final.png';
            $alt = trans( 'Inhalt befindet sich in Bearbeitung' );
        }

        $im   = BACKEND_IMAGE_PATH . $img;
        $icon = sprintf( '<img src="%s" width="16" alt="" title="%s" id="pubs%s"/>', $im, $alt, $id );


        if ( $published != DRAFT_MODE )
        {
            $icon = '<a href="javascript:void(0);" onclick="changePublish(\'pubs' . $id . '\',\'' . $publishlink . $id . '\'' . ( is_string( $onAfterChangeCallback ) ?
                    ',' . $onAfterChangeCallback : '' ) . ');">' . $icon . '</a>';
        }

        return $icon;
    }

    /**
     * create the option buttons for the Backend
     *
     * @param string $url
     * @param string $type
     * @param string $label
     * @param string $cssClass
     * @return string
     */
    public function linkIcon($url, $type = null, $label = '', $cssClass = '')
    {

        if ( $type === null )
        {
            Error::raise( 'Can´t create the Icon-Link. Please check your Attributes before can create the Icon-Link!' );
        }


        $a                            = array();
        $a[ 'tagname' ]               = 'a';
        $a[ 'attributes' ]            = array();
        $a[ 'attributes' ][ 'href' ]  = 'admin.php?' . $url;
        $a[ 'attributes' ][ 'class' ] = $cssClass;


        $img              = array();
        $img[ 'tagname' ] = 'img';


        $src = '';
        $alt = '';
        switch ( $type )
        {
            case 'edit':
                $src = 'edit.png';
                $alt = trans( 'Eintrag bearbeiten' );
                $a[ 'attributes' ][ 'class' ] .= ' doTab';
                break;

            case 'delete':
                $src = 'delete.png';
                $alt = trans( 'Eintrag löschen' );
                $a[ 'attributes' ][ 'class' ] .= ' delconfirm';
                break;

            case 'publish':
            case PUBLISH_MODE:
                $src = 'online.png';
                $alt = trans( 'Veröffentlicht' );
                $a[ 'attributes' ][ 'class' ] .= ' publishing';
                break;

            case 'unpublish':
            case UNPUBLISH_MODE:
                $src = 'offline.png';
                $alt = trans( 'nicht Veröffentlicht' );
                $a[ 'attributes' ][ 'class' ] .= ' publishing';
                break;

            case 'timecontroll':
            case TIME_MODE:
                $src = 'clock.png';
                $alt = trans( 'Zeitgesteuert Veröffentlicht' );
                break;

            case 'archive':
            case ARCHIV_MODE:
                $src = 'archive.png';
                $alt = trans( 'Ariviert' );
                break;

            case 'draft':
            case DRAFT_MODE:
                $src = 'document_mark_as_final.png';
                $alt = trans( 'Inhalt befindet sich in Bearbeitung' );
                break;
        }

        if ( !$src )
        {
            Error::raise( 'Can´t create the Icon-Link. Please check your Attributes before can create the Icon-Link!' );
        }

        $img[ 'attributes' ][ 'src' ]    = BACKEND_IMAGE_PATH . $src;
        $img[ 'attributes' ][ 'width' ]  = 16;
        $img[ 'attributes' ][ 'height' ] = 16;
        $img[ 'attributes' ][ 'alt' ]    = $alt;
        $img[ 'attributes' ][ 'title' ]  = ( $label !== '' ? $label : $alt );

        $a[ 'cdata' ]                 = Html::createTag( $img );
        $a[ 'attributes' ][ 'class' ] = preg_replace( '/^\s*/', '', $a[ 'attributes' ][ 'class' ] );

        return Html::createTag( $a );
    }

    /**
     *
     * @return array returns all Usergroups ordered by title
     */
    public function getUserGroups()
    {

        return $this->db->query( 'SELECT * FROM %tp%users_groups ORDER BY title ASC' )->fetchAll();
    }

    /**
     *
     */
    public function getRecentContents()
    {
        $this->db->query( 'SELECT * FROM %tp%last_edit WHERE controller = ? AND userid ORDER BY timestamp DESC LIMIT 15', CONTROLLER, User::getUserId() )->fetchAll();
    }

    /**
     * Used in controller.menu and this class
     * get all menuitem for Parent page selection
     *
     * @param array $data
     * @return array
     */
    public function loadParentPages($data = null)
    {

        $result = $this->db->query( 'SELECT *, title as name FROM %tp%page WHERE pageid = ? ORDER BY ordering, `title`', PAGEID )->fetchAll();


        $tree = new Tree();
        $tree->setupData( $result, 'id', 'parentid' );
        $_list = $tree->buildRecurseArray( 1 );

        // assemble menu items to the array
        $mitems        = array();
        $this_treename = '';
        foreach ( $_list as $idx => $item )
        {
            if ( $item[ 'id' ] == 1 )
            {
                unset( $_list[ $idx ] );
            }

            if ( $this_treename )
            {
                if ( $item[ 'id' ] != $data[ 'id' ] && strpos( $item[ 'treename' ], $this_treename ) === false )
                {
                    $mitems[ ] = array(
                        'id'       => $item[ 'id' ],
                        'treename' => $item[ 'treename' ]
                    );
                }
            }
            else
            {
                if ( $item[ 'id' ] != $data[ 'id' ] )
                {
                    $mitems[ ] = array(
                        'id'       => $item[ 'id' ],
                        'treename' => $item[ 'treename' ]
                    );
                }
                else
                {
                    $this_treename = $item[ 'treename' ] . ' / ';
                }
            }
        }

        return $mitems;
    }

    /**
     *
     * @param mixed $var
     * @param integer $mode
     * @return mixed
     */
    public function prepareInputData($var, $mode = null)
    {

        switch ( $mode )
        {
            case Controller::INPUT_DAY:

                return intval( $var );

                break;

            case Controller::INPUT_MONTH:

                return intval( $var );

                break;

            case Controller::INPUT_YEAR:

                return (int)$var;

                break;

            case Controller::INPUT_INTEGER:

                return (int)$var;

                break;
            case Controller::INPUT_STRING:

                return (string)$var;

                break;

            case Controller::INPUT_SEARCH:

                return preg_replace( '/([^a-z0-9_\-\+%\*\. ]*)/', '', $var );

                break;

            case Controller::INPUT_ORDER:

                return (string)$var;

                break;

            case Controller::INPUT_SORT:

                $var = strtolower( $var );
                if ( $var === 'asc' )
                {
                    return 'ASC';
                }
                elseif ( $var === 'desc' )
                {
                    return 'DESC';
                }
                else
                {
                    return null;
                }

                break;
        }
    }

    /**
     *
     * @param array $defaults
     * @param array $input
     * @param string $sessionKey
     * @return array
     */
    public function getOptions($defaults = array(), $input = array(), $sessionKey = null)
    {

        $data = array();

        foreach ( $defaults as $key => $value )
        {
            if ( isset( $input[ $key ] ) && $input[ $key ] !== null )
            {
                $data[ $key ] = $this->prepareInputData( $input[ $key ], $value[ 1 ] );
            }
            elseif ( isset( $input[ $key ] ) )
            {
                $data[ $key ] = $value[ 0 ];
            }
        }

        if ( is_string( $sessionKey ) )
        {
            Session::save( $sessionKey, $data );
        }

        return $data;
    }

    /**
     *
     * @param int $start
     * @param int $end
     * @deprecated since version 3.0.x
     */
    public function getBeetwenDate($start = null, $end = null)
    {

    }

    /**
     * @var
     */
    protected $linkparams;

    /**
     *
     * @param array $params
     */
    public function setLinkParams($params = array())
    {

        if ( !isset( $params[ 'controller' ] ) )
        {
            $params[ 'controller' ] = CONTROLLER;
        }

        if ( !isset( $params[ 'action' ] ) )
        {
            $params[ 'action' ] = ACTION;
        }

        $this->linkparams = array();
        $this->linkparams = $params;
    }

    /**
     * generate the pagelink by rule
     *
     * @param array $params (default is null)
     * @param bool $skipalias if true will not add the alias
     * @param bool $reset reset all cached rules
     * @return string|bool
     */
    public function generateLink($params = null, $skipalias = false, $reset = false)
    {

        static $routes, $tmp;

        if ( !is_array( $routes ) )
        {
            $routes = $this->getApplication()->getRouteConfig();
        }

        if ( is_array( $params ) )
        {
            $this->setLinkParams( $params );
        }


        if ( $reset )
        {
            $tmp = array();
        }

        $controller = $this->linkparams[ 'controller' ];
        $action     = $this->linkparams[ 'action' ];

        if ( !is_array( $tmp[ $controller ][ $action ] ) )
        {
            $tmp[ $controller ][ $action ] = array();


            foreach ( $routes as $__modul => $rs )
            {
                foreach ( $rs as $rule => $params )
                {
                    if ( strtolower( $controller ) == strtolower( $params[ 'controller' ] ) && strtolower( $action ) == strtolower( $params[ 'action' ] ) )
                    {
                        #$params['rule'] = $params['rule'];
                        $tmp[ $controller ][ $action ][ $params[ 'rule' ] ] = $params;
                    }
                }
            }
        }

        if ( !is_array( $tmp[ $controller ][ $action ] ) )
        {
            return false;
        }

        $alias  = $this->linkparams[ 'alias' ];
        $suffix = $this->linkparams[ 'suffix' ];


        unset( $this->linkparams[ 'controller' ] );
        unset( $this->linkparams[ 'action' ] );
        unset( $this->linkparams[ 'alias' ] );
        unset( $this->linkparams[ 'suffix' ] );

        $tmpUri      = '';
        $tmpUriFound = false; #

        if ( !function_exists( '_sort' ) )
        {


            /**
             * @param $a
             * @param $b
             * @return int
             */
            function _sort($a, $b)
            {

                if ( substr_count( $a[ 'rule' ], ':' ) == substr_count( $b[ 'rule' ], ':' ) )
                {
                    return 0;
                }

                return ( substr_count( $a[ 'rule' ], ':' ) > substr_count( $b[ 'rule' ], ':' ) ) ? -1 : 1;
            }

        }


        uasort( $tmp[ $controller ][ $action ], '_sort' );

        # print_r($tmp);


        foreach ( $tmp[ $controller ][ $action ] as $rule => $params )
        {


            $rule = preg_replace( '@/:page@s', '', $params[ 'rule' ] );


            $ruleParamCount  = count( $params[ 'paramkeys' ] );
            $foundParams     = $params[ 'paramkeys' ];
            $ruleparam_Count = substr_count( $rule, ':' );


            // clean rule
            #$tmprule = preg_replace('@([^a-z0-9_\-/:]+)@i', '', $rule);
            $tmpUri = $rule;
            #$tmpUri = preg_replace('@(/:page)@sU', '', $tmpUri);
            #print_r($params);
            #echo $tmpUri.'<br/>';

            $tmpUriFound = false;
            $replaced    = 0;


            if ( strpos( $params[ 'rule' ], ':page' ) !== false )
            {
                //$ruleParamCount  = $ruleParamCount;
                //$ruleparam_Count = $ruleparam_Count;
                unset( $foundParams[ 'page' ] );
            }

            foreach ( $this->linkparams as $key => $value )
            {
                if ( isset( $params[ 'paramkeys' ][ $key ] ) && isset( $params[ 'params' ][ $key ] ) )
                {
                    $value = trim( ( $value === null ? '' : str_replace( ':', '&#58;', $value ) ) );

                    if ( preg_match( '@(' . $params[ 'params' ][ $key ] . ')@isU', $value ) )
                    {
                        unset( $foundParams[ $key ] );
                        $tmpUri = preg_replace( '@:' . $key . '@isU', '' . $value, $tmpUri );
                        $replaced++;
                    }
                }

                #       echo "K:$key v:$value<br/>";
            }

            #  echo "tmpUri:$tmpUri = $ruleparam_Count -> replaced:$replaced<br>";


            if ( $ruleParamCount > 0 && $ruleparam_Count != $replaced )
            {
                continue;
            }


            if ( $ruleparam_Count == 0 && $alias != '' )
            {
                $tmpUriFound = true;
                break;
            }


            if ( $ruleParamCount > 0 && !$tmpUriFound )
            {
                if ( count( $foundParams ) == 0 && $tmpUri != '' )
                {
                    $tmpUriFound = true;
                    break;
                }
                else
                {
                    $tmpUriFound = false;
                    $foundParams = array();
                    $tmpUri      = $tmprule = '';
                }
            }
        }

        #  echo($tmprule.$tmpUriFound . ' -> ' . $tmpUri . ' -> ' . count($diff) . '<br>');
        #  exit;

        if ( $tmpUriFound )
        {
            if ( $skipalias !== true )
            {
                if ( substr( $tmpUri, -1 ) === '/' )
                {
                    $tmpUri = substr( $tmpUri, 0, -1 );
                }

                $tmpUri .= ( $alias != '' ? '/' . $alias . ( $suffix ? '.' . $suffix : '' ) : '' );
            }

            return $tmpUri;
        }

        return $tmpUri;
    }

    /**
     *
     * @return boolean/array
     */
    public function getSocialNetworkData()
    {

        if ( self::$_socialNetworkData == null )
        {
            return false;
        }

        return self::$_socialNetworkData;
    }

    /**
     *
     * @staticvar int $insertedImages
     * @param array $templateData
     * @param array $img
     * @param integer $limit
     */
    public function setSocialNetworkImageData(&$templateData, $img = array(), $limit = 3)
    {

        static $insertedImages;

        if ( !is_integer( $insertedImages ) )
        {
            $insertedImages = 0;
        }


        if ( isset( $img[ 'width' ] ) && $img[ 'width' ] > 0 && isset( $img[ 'height' ] ) && $img[ 'height' ] > 0 && $insertedImages < $limit )
        {
            $templateData[ 'og' ][ 'images' ][ ] = array(
                'src'    => $img[ 'src' ],
                'height' => $img[ 'height' ],
                'width'  => $img[ 'width' ]
            );
            $insertedImages++;
        }
    }

    /**
     *
     * @param array $templateData
     * @param string $title
     * @param string $description
     * @param boolean $autoExtractImages
     */
    public function setSocialNetworkData(&$templateData, $title = '', $description = '', $autoExtractImages = false)
    {

        if ( $autoExtractImages )
        {
            $img = Content::extractImages( $description );
            if ( is_array( $img ) )
            {
                foreach ( $img as $r )
                {
                    // process for metatags "og"
                    $this->setSocialNetworkImageData( $templateData, $r, 3 );
                }
            }
        }

        if ( $title )
        {
            $templateData[ 'og' ][ 'title' ] = Strings::htmlspecialcharsUnicode( strip_tags( $title ) );
        }

        if ( $description )
        {
            $templateData[ 'og' ][ 'description' ] = Strings::htmlspecialcharsUnicode( Strings::unhtmlSpecialchars( substr( trim( preg_replace( '/\s{2,}/', ' ', strip_tags( $description ) ) ), 0, 250 ), true ) );
        }

        $templateData[ 'og' ][ 'url' ] = Settings::get( 'portalurl' ) . $this->Env->requestUri();

        self::$_socialNetworkData = $templateData[ 'og' ];
    }

    /**
     *
     * @return array
     */
    public function getLayouts()
    {

        return $this->db->query( "SELECT l.*, s.title AS skintitle FROM %tp%layouts AS l
                                Left JOIN %tp%skins AS s ON(s.id=l.skinid)
                                ORDER BY s.title,l.title ASC" )->fetchAll();
    }

    /**
     *
     * @param string $permalink the url
     * @param integer $contentid default is 0
     * @param integer $modulid default is 0
     * @param array $shorturls default is a empty array
     * @param bool $forceUpdate default is false
     * @return array
     * @throws BaseException
     */
    public function getShorturls($permalink, $contentid = 0, $modulid = 0, $shorturls = array(), $forceUpdate = false)
    {

        $save    = false;
        $isempty = count( $shorturls );

        if ( !$isempty && empty( $contentid ) && empty( $modulid ) )
        {
            throw new BaseException( 'Could not get the Short Urls' );
        }

        if ( !$isempty && empty( $contentid ) )
        {
            throw new BaseException( 'Could not get the Short Urls' );
        }

        $reg = new AliasRegistry();

        if ( !$isempty && !empty( $contentid ) )
        {
            $shorturls = $reg->getShortUrls( $contentid, $modulid );
        }


        if ( $forceUpdate === true || !isset( $shorturls[ 'googl' ] ) || ( isset( $shorturls[ 'googl' ] ) && empty( $shorturls[ 'googl' ] ) ) )
        {

            $target = 'https://www.googleapis.com/urlshortener/v1/url?';

            $ch = curl_init();

            // Set our default target URL
            curl_setopt( $ch, CURLOPT_URL, $target );

            // We don't want the return data to be directly outputted, so set RETURNTRANSFER to true
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

            // Payload
            $data        = array(
                'longUrl' => $permalink
            );
            $data_string = '{ "longUrl": "' . $permalink . '" }';

            # Set cURL options
            curl_setopt( $ch, CURLOPT_POST, count( $data ) );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $data_string );
            curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json'
            ) );
            $_result = json_decode( curl_exec( $ch ) );
            $result  = $_result->id;

            if ( $result != "" )
            {
                $save                 = true;
                $shorturls[ 'googl' ] = trim( $result );
            }
            else
            {
                $shorturls[ 'googl' ] = '';
            }
        }


        /*
          if (!isset($shorturls['tinyurl']))
          {
          $save   = true;
          $result = Library::getRemoteFile("http://tinyurl.com/api-create.php?url=" . $permalink);
          if ($result != "" && strpos($result, "http://tinyurl.com") === 0)
          {
          $shorturls['tinyurl'] = trim($result);
          }
          else
          {
          $shorturls['tinyurl'] = '';
          }
          }

          if (!isset($shorturls['b2l']))
          {
          $save   = true;
          $result = Library::getRemoteFile("http://b2l.me/api.php?alias=&url=" . $permalink);
          if ($result != "" && strpos($result, "http://b2l.com") === 0)
          {
          $shorturls['b2l'] = trim($result);
          }
          else
          {
          $shorturls['b2l'] = '';
          }
          }



          if ( !isset( $shorturls[ 'isgd' ] ) )
          {
          $save   = true;
          $result = Library::getRemoteFile( 'http://is.gd/api.php?longurl=' . urlencode( $permalink ) );

          if ( $result && $result != "" && strpos( $result, "http://is.gd/" ) === 0 )
          {
          $shorturls[ 'isgd' ] = trim( $result );
          $save                = true;
          }
          else
          {
          $shorturls[ 'isgd' ] = '';
          }
          } */

        /*

          if (!isset($shorturls['bitly']) || $shorturls['bitly'] == "http://bit.ly/1BOWLu")
          {
          $result = Library::getRemoteFile('http://bit.ly/api?url=' . urlencode($permalink));
          if ($result && $result != "" && strpos($result, "http://bit.ly/") === 0 && $result != "http://bit.ly/1BOWLu")
          {
          $shorturls['bitly'] = trim($result);
          $save = true;
          }
          }

          if (!isset($shorturls['snipr']) || !isset($shorturls['snipurl']) || !isset($shorturls['snurl']))
          {
          $result = Library::getRemoteFile('http://snipr.com/site/snip?r=simple&link=' . urlencode($permalink));
          if ($result && $result != "" && strpos($result, "http://snipr.com/") === 0)
          {
          $shorturls['snipr'] = trim($result);
          $shorturls['snurl'] = str_replace("snipr.com", "snurl.com", trim($result));
          $shorturls['snipurl'] = str_replace("snipr.com", "snipurl.com", trim($result));
          $save = true;
          }
          }

         */

        if ( $save && !empty( $contentid ) )
        {
            $reg->saveShortUrls( serialize( $shorturls ), $contentid, $modulid );
        }

        return $shorturls;
    }

    /**
     *
     * @param array $menu
     */
    public function addControllerMenu(array $menu)
    {

        $this->_ControllerMenu = $menu;
    }

    /**
     *
     * @return array/null
     */
    public function getControllerMenu()
    {

        return $this->_ControllerMenu;
    }

    /**
     * Internal Dock update only used in the Backend!
     *
     */
    public function updateDock()
    {

        if ( $this->isBackend() && $this->input( 'saveDock' ) && User::isLoggedIn() )
        {
            $default = array(
                'dockposition' => 'center',
                'dockautohide' => false,
                'mintoappicon' => false,
                'dockHeight'   => 40,
                'activeItems'  => array(),
                'dockItems'    => array()
            );


            $personal     = new Personal;
            $personaldata = $personal->get( 'dock', 'settings', $default );

            $newoptions                   = array();
            $newoptions[ 'dockHeight' ]   = (int)$this->input( 'dockHeight' ) ? (int)$this->input( 'dockHeight' ) :
                $personaldata[ 'dockHeight' ];
            $newoptions[ 'dockposition' ] = $personaldata[ 'dockposition' ];
            $newoptions[ 'dockautohide' ] = $personaldata[ 'dockautohide' ];
            $newoptions[ 'mintoappicon' ] = $personaldata[ 'mintoappicon' ];


            if ( $this->input( 'activeItems' ) == false || $this->input( 'activeItems' ) == 'false' )
            {
                $newoptions[ 'activeItems' ] = array();
            }
            elseif ( is_array( $this->input( 'activeItems' ) ) )
            {
                $newoptions[ 'activeItems' ] = array();
                foreach ( $this->input( 'activeItems' ) as $r )
                {
                    $newoptions[ 'activeItems' ][ ] = array(
                        'WindowID'          => $r[ 'WindowID' ],
                        'isRootApplication' => $r[ 'isRootApplication' ],
                        'url'               => preg_replace( '#^.*(admin.php\?(.*))#U', '$1', $r[ 'url' ] ),
                        'isStatic'          => $r[ 'isStatic' ],
                        'WindowTitle'       => $r[ 'WindowTitle' ],
                        'controller'        => $r[ 'controller' ],
                        'action'            => $r[ 'action' ],
                    );
                }
            }
            else
            {
                $newoptions[ 'activeItems' ] = $personaldata[ 'activeItems' ];
            }


            if ( $this->input( 'dockItems' ) == false || $this->input( 'dockItems' ) == 'false' )
            {
                $newoptions[ 'dockItems' ] = array();
            }
            elseif ( is_array( $this->input( 'dockItems' ) ) )
            {
                $newoptions[ 'dockItems' ] = array();
                foreach ( $this->input( 'dockItems' ) as $r )
                {
                    $newoptions[ 'dockItems' ][ ] = array(
                        'WindowID'          => $r[ 'WindowID' ],
                        'isRootApplication' => $r[ 'isRootApplication' ],
                        'url'               => preg_replace( '#^.*(admin.php\?(.*))#U', '$1', $r[ 'url' ] ),
                        'isStatic'          => $r[ 'isStatic' ],
                        'WindowTitle'       => $r[ 'WindowTitle' ],
                        'controller'        => $r[ 'controller' ],
                        'action'            => $r[ 'action' ],
                    );
                }
            }
            else
            {
                $newoptions[ 'dockItems' ] = $personaldata[ 'dockItems' ];
            }

            $personal->set( "dock", 'settings', $newoptions );
        }
    }

    /**
     * @return array
     */
    public function getSmilies()
    {

        $smilies = BBCode::getSmilies();
        $out     = array();

        if ( is_array( $smilies ) )
        {
            foreach ( $smilies as $r )
            {
                $out[ ] = array(
                    'title'   => $r[ 'smilietitle' ],
                    'imgpath' => $r[ 'smiliepath' ],
                    'bbcode'  => $r[ 'smiliecode' ]
                );
            }
        }

        return $out;
    }
}

?>