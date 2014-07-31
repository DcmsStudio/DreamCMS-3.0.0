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
 * @file         Controller.php
 */

/** @noinspection PhpUndefinedClassInspection */

/** @noinspection PhpUndefinedClassInspection */

/** @noinspection PhpUndefinedClassInspection */
class Controller extends Controller_Abstract
{

    /**
     *
     */
    const INPUT_STRING = 1;

    /**
     *
     */
    const INPUT_INTEGER = 2;

    /**
     *
     */
    const INPUT_SEARCH = 3;

    /**
     *
     */
    const INPUT_SORT = 4;

    /**
     *
     */
    const INPUT_ORDER = 5;

    /**
     *
     */
    const INPUT_DAY = 6;

    /**
     *
     */
    const INPUT_MONTH = 7;

    /**
     *
     */
    const INPUT_YEAR = 8;

    /**
     * @var Controller
     */
    protected static $_instance = null;

    /**
     * Prevent cloning of the object (Singleton)
     */
    final private function __clone()
    {

    }

    /**
     * Return the current object instance (Singleton)
     *
     * @return Controller
     */
    public static function getInstance()
    {

        if ( self::$_instance === null )
        {
            self::$_instance = new Controller;
        }

        return self::$_instance;
    }

    public function __destruct()
    {

        parent::__destruct();
        self::$_instance = null;
    }

    /**
     *
     */
    private function _callError()
    {

        $call = 'null';
        if ( !defined( 'CONTROLLER' ) )
        {
            if ( $this->_get( 'cp' ) || $this->_post( 'cp' ) )
            {
                $call = $this->_get( 'cp' ) ? $this->_get( 'cp' ) : $this->_post( 'cp' );
            }
            elseif ( $this->_get( 'adm' ) || $this->_post( 'adm' ) )
            {
                $call = $this->_get( 'adm' ) ? $this->_get( 'adm' ) : $this->_post( 'adm' );
            }
        }
        else
        {
            $call = CONTROLLER;
        }


        if ( !defined( 'ACTION' ) )
        {
            if ( $this->_get( 'action' ) || $this->_post( 'action' ) )
            {
                $call .= '/' . $this->_get( 'action' ) ? $this->_get( 'action' ) : $this->_post( 'action' );
            }
        }
        else
        {
            $call .= '/' . ACTION;
        }

        die( sprintf( 'Invalid Script call `%s`!', $call ) );
    }


    private function getRoute()
    {

        $_controllerInput = null;
        $_controller      = null;
        $_action          = null;

        if ( !$this->_get( 'adm' ) && !$this->_post( 'adm' ) && !$this->_get( 'cp' ) && !$this->_post( 'cp' ) )
        {
            if ( $this->getApplication()->getMode() !== Application::BACKEND_MODE )
            {


                // Use custom frontpage?
                if ( !$this->_get( '_call' ) )
                {
                    if ( Settings::get( 'frontpage', '' ) != '' )
                    {
                        $url = Settings::get( 'frontpage', '' );

                        if ( stripos( $url, $_SERVER[ 'REQUEST_URI' ] ) !== false )
                        {
                            $_SERVER[ 'REQUEST_URI' ] = preg_replace( '#^' . preg_quote( Settings::get( 'portalurl' ), '#' ) . '#is', '', $url );
                            $this->Input->set( '_call', $_SERVER[ 'REQUEST_URI' ] );
                            // $this->Env->init();
                            $GLOBALS[ 'IS_FRONTPAGE' ] = true;
                        }
                        else
                        {
                            $GLOBALS[ 'IS_FRONTPAGE' ] = true;
                            $this->Input->set( '_call', preg_replace( '#^' . preg_quote( Settings::get( 'portalurl' ), '#' ) . '#is', '', $url ) );
                        }
                    }
                    $useQuery = ltrim( $_SERVER[ 'REQUEST_URI' ], '/' );
                }
                else
                {
                    $call     = (string)$this->_get( '_call' );
                    $useQuery = ltrim( $call, '/' );
                }


                if ( !defined( 'REQUEST' ) )
                {
                    define( 'REQUEST', '/' . $useQuery );
                }

                // now get the first name (modulname or plugin name)
                $uri   = $useQuery;
                $names = explode( '/', $uri );
                $modul = array_shift( $names );

                $this->addonName = null;

                if ( $modul )
                {
                    $modul = strtolower( $modul );

                    if ( $this->_isPlugin( $modul ) )
                    {
                        $this->addonName = $modul;

                        if ( $this->isPluginPublished( $modul ) && $this->canRunPlugin( $modul ) )
                        {
                            $routes = $this->getRoutesByPlugin( $modul );

                        }
                        else
                        {
                            // error
                        }
                    }
                    else
                    {
                        if ( $this->getApplication()->isActiveModul( $modul ) )
                        {
                            $routes = $this->getRoutesByModul( $modul );
                        }
                        else
                        {
                            // error
                        }
                    }


                    if ( $routes !== null )
                    {
                        $route = new Route( $uri, false, true );
                        Registry::setObject( 'Route', $route );

                       # $route->reset()->ns( '/' . $modul, function () use ($route, $routes)
                       # {
                            $found = false;

                            foreach ( $routes as $r )
                            {
                                if ( !$route->found() && $r[ 'rule' ] )
                                {
                                    $route->setAction( $r[ 'action' ] )->setController( $r[ 'controller' ] )->respond( $r[ 'rule' ], function (Route $request)
                                    {
                                        if ( $request->found() )
                                        {
                                            $controllerInstance = Controller::getInstance();
                                            $controllerInstance->setRouterRequestData( $request->Request()->params() );

                                            $_action = $request->param( 'action' );

                                            if ( $_action === null )
                                            {
                                                $_action = $request->getAction() ? $request->getAction() : $controllerInstance->getApplication()->getOption( 'defaultAction' );
                                            }

                                            if ( empty( $_action ) )
                                            {
                                                $_action = 'index';
                                            }


                                            $_controller = $request->param( 'cp' );

                                            if ( $_controller === null )
                                            {
                                                $_controller = $request->getController();
                                            }
                                            if ( $controllerInstance->_isPlugin( $_controller ) )
                                            {
                                                $_controller = 'Plugin'; // reset controller to Plugin Controller
                                            }


                                            $controllerInstance->Input->setFromRouter( $request->Request()->params() );
                                            $controllerInstance->Input->set( 'cp', strtolower( $_controller ) );
                                            $controllerInstance->Input->set( 'action', strtolower( $_action ) );
                                            $controllerInstance->Input->remove( '_call' );
                                            $controllerInstance->Input->remove( 'cmd' );

                                            $_controller = ucfirst( strtolower( $_controller ) );
                                            $_action     = ucfirst( strtolower( $_action ) );

                                            if ( $controllerInstance->getApplication()->getMode() === Application::BACKEND_MODE )
                                            {
                                                switch ( strtolower( $_controller ) )
                                                {
                                                    case 'lang':
                                                        $_controller = 'Main';
                                                        $_action     = 'Lang';
                                                        break;
                                                }

                                                /**
                                                 *
                                                 */
                                                define( 'CONTROLLER', ucfirst( strtolower( $_controller ) ) );
                                                /**
                                                 *
                                                 */
                                                define( 'ACTION', ucfirst( strtolower( $_action ) ) );
                                            }
                                            else
                                            {
                                                /**
                                                 *
                                                 */
                                                define( 'CONTROLLER', ucfirst( strtolower( $_controller ) ) );

                                                /**
                                                 *
                                                 */
                                                define( 'ACTION', ucfirst( strtolower( $_action ) ) );

                                                /**
                                                 *
                                                 */
                                                define( 'DOCUMENT_EXTENSION', $request->getDocumentExtension() );

                                                /**
                                                 *
                                                 */
                                                define( 'DOCUMENT_NAME', $request->getDocumentName() );
                                            }
                                        }
                                    } );


                                 #  print_r( $route->getRouteData() );


                                    if ( $route->found() )
                                    {
#print_r($route->Request()->params());
                                        #print_r($this->input());
                                        #die(ACTION);
                                        break;
                                    }
                                }
                            }

                      #  } );

#exit;

                        if ( Settings::get( 'websiteoffline', false ) && !User::hasPerm( 'generic/canviewoffline', false ) && !$this->isBackend() )
                        {
                            if ( defined( 'CONTROLLER' ) && strtolower( CONTROLLER ) !== 'asset' )
                            {
                                $this->load( 'Document' );
                                $this->load( 'Breadcrumb' );
                                $this->Document->offline( trans( 'Die Website befindet sich im Offline Modus. Versuchen Sie es bitte zu einem späteren Zeitpunkt erneut.<p>Vielen Dank für Ihr Verständnis</p>' ), true );
                            }
                        }

                        // send 404 page
                        if ( !$route->found() )
                        {
                            if ( $this->getApplication()->getOption( 'defaultController' ) )
                            {
                                /**
                                 *
                                 */
                                define( 'CONTROLLER', ucfirst( strtolower( $this->getApplication()->getOption( 'defaultController' ) ) ) );
                                /**
                                 *
                                 */
                                define( 'ACTION', ucfirst( strtolower( $this->getApplication()->getOption( 'defaultAction' ) ) ) );
                            }
                            $this->_initController( $this );
                            $this->load( 'Page' );
                            $this->Page->error( 404, trans( 'Die von Ihnen aufgerufene Seite existiert.' ) );

                        }
                        else
                        {
                            $this->callController();
                        }

                        exit;
                    }

                }
                else
                {
                    // scan all routes


                    $modules = $this->getApplication()->getModuleNames();
                    foreach ( $modules as $modul )
                    {
                        $modul = strtolower( $modul );

                        if ( $this->_isPlugin( $modul ) )
                        {
                            $this->addonName = $modul;

                            if ( $this->isPluginPublished( $modul ) && $this->canRunPlugin( $modul ) )
                            {
                                $routes = $this->getRoutesByPlugin( $modul );

                            }
                            else
                            {
                                // error
                            }
                        }
                        else
                        {
                            if ( $this->getApplication()->isActiveModul( $modul ) )
                            {
                                $routes = $this->getRoutesByModul( $modul );
                            }
                            else
                            {
                                // error
                            }
                        }

                        if ( $routes !== null )
                        {
                            $route = new Route( $uri, false, true );
                            Registry::setObject( 'Route', $route );

                            $found = false;

                            foreach ( $routes as $r )
                            {
                                if ( !$route->found() && $r[ 'rule' ] )
                                {
                                    $route->setAction( $r[ 'action' ] )->setController( $r[ 'controller' ] )->respond( $r[ 'rule' ], function (Route $request)
                                    {
                                        if ( $request->found() )
                                        {
                                            $controllerInstance = Controller::getInstance();
                                            $controllerInstance->setRouterRequestData( $request->Request()->params() );

                                            $_action = $request->param( 'action' );

                                            if ( $_action === null )
                                            {
                                                $_action = $request->getAction() ? $request->getAction() : $controllerInstance->getApplication()->getOption( 'defaultAction' );
                                            }

                                            if ( empty( $_action ) )
                                            {
                                                $_action = 'index';
                                            }


                                            $_controller = $request->param( 'cp' );

                                            if ( $_controller === null )
                                            {
                                                $_controller = $request->getController();
                                            }
                                            if ( $controllerInstance->_isPlugin( $_controller ) )
                                            {
                                                $_controller = 'Plugin'; // reset controller to Plugin Controller
                                            }


                                            $controllerInstance->Input->setFromRouter( $request->Request()->params() );
                                            $controllerInstance->Input->set( 'cp', strtolower( $_controller ) );
                                            $controllerInstance->Input->set( 'action', strtolower( $_action ) );
                                            $controllerInstance->Input->remove( '_call' );
                                            $controllerInstance->Input->remove( 'cmd' );

                                            $_controller = ucfirst( strtolower( $_controller ) );
                                            $_action     = ucfirst( strtolower( $_action ) );

                                            if ( $controllerInstance->getApplication()->getMode() === Application::BACKEND_MODE )
                                            {
                                                switch ( strtolower( $_controller ) )
                                                {
                                                    case 'lang':
                                                        $_controller = 'Main';
                                                        $_action     = 'Lang';
                                                        break;
                                                }

                                                /**
                                                 *
                                                 */
                                                define( 'CONTROLLER', ucfirst( strtolower( $_controller ) ) );
                                                /**
                                                 *
                                                 */
                                                define( 'ACTION', ucfirst( strtolower( $_action ) ) );
                                            }
                                            else
                                            {
                                                /**
                                                 *
                                                 */
                                                define( 'CONTROLLER', ucfirst( strtolower( $_controller ) ) );

                                                /**
                                                 *
                                                 */
                                                define( 'ACTION', ucfirst( strtolower( $_action ) ) );

                                                /**
                                                 *
                                                 */
                                                define( 'DOCUMENT_EXTENSION', $request->getDocumentExtension() );

                                                /**
                                                 *
                                                 */
                                                define( 'DOCUMENT_NAME', $request->getDocumentName() );
                                            }
                                        }
                                    } );


                                    if ( $route->found() )
                                    {
                                        print_r($route->getRouteData());exit;
                                        break;
                                    }
                                }
                            }

                            if ( Settings::get( 'websiteoffline', false ) && !User::hasPerm( 'generic/canviewoffline', false ) && !$this->isBackend() )
                            {
                                if ( defined( 'CONTROLLER' ) && strtolower( CONTROLLER ) !== 'asset' )
                                {
                                    $this->load( 'Document' );
                                    $this->load( 'Breadcrumb' );
                                    $this->Document->offline( trans( 'Die Website befindet sich im Offline Modus. Versuchen Sie es bitte zu einem späteren Zeitpunkt erneut.<p>Vielen Dank für Ihr Verständnis</p>' ), true );
                                }
                            }

                            // send 404 page
                            if ( !$route->found() )
                            {
                                if ( $this->getApplication()->getOption( 'defaultController' ) )
                                {
                                    /**
                                     *
                                     */
                                    define( 'CONTROLLER', ucfirst( strtolower( $this->getApplication()->getOption( 'defaultController' ) ) ) );
                                    /**
                                     *
                                     */
                                    define( 'ACTION', ucfirst( strtolower( $this->getApplication()->getOption( 'defaultAction' ) ) ) );
                                }
                                $this->_initController( $this );
                                $this->load( 'Page' );
                                $this->Page->error( 404, trans( 'Die von Ihnen aufgerufene Seite existiert.' ) );

                            }
                            else
                            {
                                $this->callController();
                            }

                            exit;
                        }


                    }
                }
            }
            else
            {
                // backend mode
            }
        }
        else
        {

        }

    }


    /**
     *
     * @param string $modul
     * @return null|array
     */
    private function getRoutesByModul($modul)
    {
        Library::disableErrorHandling();
        $data = include MODULES_PATH . ucfirst( $modul ) . '/Config/Routes.php';
        Library::enableErrorHandling();

        if ( is_array( $data ) )
        {
            return $data;
        }

        return null;
    }

    /**
     *
     * @param string $plugin
     * @return null|array
     */
    private function getRoutesByPlugin($plugin)
    {
        Library::disableErrorHandling();
        $data = include PLUGIN_PATH . ucfirst( $plugin ) . '/Config/Routes.php';
        Library::enableErrorHandling();

        if ( is_array( $data ) )
        {
            return $data;
        }

        return null;
    }


    protected function callController()
    {

        $this->_initController( $this );

        if ( Session::get( 'seemode' ) )
        {
            /**
             *
             */
            define( 'IS_SEEMODE', true );
        }
        else
        {
            /**
             *
             */
            define( 'IS_SEEMODE', false );
        }

        $error = (string)$this->input( 'error' );

        /**
         * Stop if is input error
         */
        if ( $error )
        {
            User::disableUserLocationUpdate();
            if ( $error === '400' )
            {
                $this->Page->error( 400, trans( 'Der Server konnte die Syntax der Anforderung nicht interpretieren.' ) );
            }
            else if ( $error === '401' )
            {
                $this->Page->error( 401, trans( 'Die Anfrage erfordert eine Authentifizierung.' ) );
            }
            else if ( $error === '403' )
            {
                $this->Page->error( 403, trans( 'Die Anfrage wurde mangels Berechtigung des Clients nicht durchgeführt.' ) );
            }
            else if ( $error === '404' )
            {
                $this->Page->error( 404, trans( 'Die von Ihnen aufgerufene Seite existiert.' ) );
            }
            else if ( $error === '500' )
            {
                $this->Page->error( 500, trans( 'Der Server kann die Anforderung aufgrund eines Fehlers nicht ausführen.' ) );
            }
        }


        if ( $this->_post( 'cp' ) != 'tracker' && !$this->isBackend() )
        {
            Session::save( 'HTTP_REFERER', $this->Env->refferer() );
        }

        $seemode = Cookie::get( 'seemodePopup', false );
        $authKey = $this->input( 'authKey' );

        if ( !$this->isBackend() )
        {
            $this->load( 'Breadcrumb' );
            $this->load( 'SideCache' );
        }


        // init seemode user
        if ( strpos( REQUEST, 'asset/' ) === false && ( $this->input( 'seemodePopup' ) && $authKey ) || ( $seemode && $seemode != '' ) )
        {
            $key      = $authKey ? $authKey : ( $seemode && Cookie::get( 'loginpermanet' ) ? Cookie::get( 'loginpermanet' ) : '' );
            $valid    = false;
            $useLogin = false;

            if ( !User::isLoggedIn() )
            {
                $valid    = User::login( false, false, $key );
                $useLogin = true;
            }
            else
            {
                $valid = User::login( false, false, $key );
            }

            if ( !User::isLoggedIn() )
            {
                if ( IS_AJAX )
                {
                    Library::sendJson( false, 'Seemode Auth error!' );
                }

                die( 'Seemode Auth error!' );
            }


            if ( !$useLogin && $key && ( User::get( 'uniqidkey' ) || Cookie::get( 'uhash' ) ) )
            {

                if ( !$valid && User::get( 'uniqidkey' ) === $key )
                {
                    Cookie::set( 'seemodePopup', true );
                    Cookie::set( 'loginpermanet', User::getUserUiqKey(), $GLOBALS[ 'SESSIONTIMEOUT' ] );
                    Cookie::set( 'uhash', User::getUserUiqKey(), $GLOBALS[ 'SESSIONTIMEOUT' ] );
                    Session::save( 'seemodePopup', true );
                    $valid = true;
                }

                if ( !$valid && Cookie::get( 'uhash' ) === $key )
                {
                    Cookie::set( 'seemodePopup', true );
                    Cookie::set( 'loginpermanet', User::getUserUiqKey(), $GLOBALS[ 'SESSIONTIMEOUT' ] );
                    Cookie::set( 'uhash', User::getUserUiqKey(), $GLOBALS[ 'SESSIONTIMEOUT' ] );
                    Session::save( 'seemodePopup', true );
                    $valid = true;
                }


                if ( $valid )
                {
                    Session::save( 'username', User::getUsername() );
                    Session::save( 'password', User::get( 'password' ) );

                    // Cache des Users löschen und anschießend neu aufbauen (backend)
                    Cache::delete( 'menu_user_' . User::getUserId() );
                }
            }

            if ( $useLogin )
            {
                if ( $valid )
                {
                    Cookie::set( 'seemodePopup', true );
                    Cookie::set( 'loginpermanet', User::getUserUiqKey(), $GLOBALS[ 'SESSIONTIMEOUT' ] );
                    Cookie::set( 'uhash', User::getUserUiqKey(), $GLOBALS[ 'SESSIONTIMEOUT' ] );

                    Session::save( 'seemodePopup', true );
                    Session::save( 'username', User::getUsername() );
                    Session::save( 'password', User::get( 'password' ) );

                    // Cache des Users löschen und anschießend neu aufbauen (backend)
                    Cache::delete( 'menu_user_' . User::getUserId() );
                }
                else
                {
                    if ( IS_AJAX )
                    {
                        Library::sendJson( false, 'Seemode Auth error! Invalid login data!' );
                    }
                    die( 'Seemode Auth error! Invalid login data!' );
                }
            }

            if ( !$valid )
            {
                if ( IS_AJAX )
                {
                    Library::sendJson( false, 'Seemode Auth error!' );
                }
                die( 'Seemode Auth error!' );
            }
        }


        /**
         *
         */
        if ( $this->input( 'seemode' ) === 'on' /* || (User::isLoggedIn() && User::isAdmin()) */ )
        {
            Session::save( 'seemode', true );
        }
        elseif ( $this->input( 'seemode' ) === 'off' )
        {
            Session::delete( 'seemode' );
            Library::sendJson( true );
        }


        $_mode = ( $this->isBackend() ? 'Backend' : 'Frontend' );


        if ( !$this->isBackend() && is_string( $this->addonName ) )
        {
            $this->isAddon = true;
        }

        $controllerFile = MODULES_PATH . CONTROLLER . '/Controller/' . $_mode . '.php';


        $modul     = CONTROLLER;
        $className = CONTROLLER . '_Controller_' . $_mode;


        if ( is_readable( $controllerFile ) )
        {

            // is the modul active
            if ( !$this->getApplication()->isActiveModul( $modul ) )
            {
                if ( !IS_AJAX )
                {
                    User::disableUserLocationUpdate();

                    if ( $this->isBackend() )
                    {
                        Error::raise( trans( 'Das Modul "' . $modul . '" wurde nicht aktiviert!' ) );
                    }
                    else
                    {
                        $this->Page->error( 501, trans( 'Das Modul "' . $modul . '" wurde nicht aktiviert!' ) );
                    }
                }
                else
                {
                    Library::sendJson( false, trans( 'Das Modul "' . $modul . '" wurde nicht aktiviert!' ) );
                }
            }

            $this->load( $className, '__RunController' );


            if ( is_callable( array(
                $this->__RunController,
                'runAction'
            ) )
            )
            {
                if ( $this->isBackend() )
                {
                    // adding the Contoller Menu
                    $this->addControllerMenu( $this->getModulBackendMenu() );

                    //
                    $this->updateDock();
                }

                $this->__RunController->runAction( $this );

                User::disableUserLocationUpdate();
                $this->Page->error( 404, trans( 'Die von Ihnen aufgerufene Seite existiert nicht.' ) . ' @' . __LINE__ );
                exit;
            }
            else
            {
                User::disableUserLocationUpdate();

                // error 404
                if ( DEBUG )
                {
                    trigger_error( sprintf( 'The Controller "%s" has no method "runAction"!', CONTROLLER ), E_USER_ERROR );
                }
                else
                {
                    $this->load( 'Page' );
                    $this->Page->error( 404, trans( 'Die von Ihnen aufgerufene Seite existiert nicht.' ) . ' @' . __LINE__ );
                }
            }
        }
        else
        {
            User::disableUserLocationUpdate();

            // error 404
            if ( DEBUG )
            {
                trigger_error( 'The Controller is not readable in the Directory: ' . $controllerFile, E_USER_ERROR );
            }
            else
            {
                $this->load( 'Page' );
                $this->Page->error( 404, trans( 'Die von Ihnen aufgerufene Seite existiert nicht.' ) . ' @' . __LINE__ );
            }
        }

        $this->load( 'Page' );
        User::disableUserLocationUpdate();
        $this->Page->error( 404, trans( 'Die von Ihnen aufgerufene Seite existiert nicht.' ) . ' @' . __LINE__ );

        exit;


    }


    /**
     * Init the Controller and Action
     *
     */
    public function _initCall()
    {
        $_controllerInput = null;
        $_controller      = null;
        $_action          = null;


        if ( !$this->_get( 'adm' ) && !$this->_post( 'adm' ) && !$this->_get( 'cp' ) && !$this->_post( 'cp' ) )
        {
            if ( $this->getApplication()->getMode() !== Application::BACKEND_MODE )
            {
                // Use custom frontpage?
                if ( !$this->_get( '_call' ) )
                {
                    if ( Settings::get( 'frontpage', '' ) != '' )
                    {

                        $url = Settings::get( 'frontpage', '' );

                        if ( stripos( $url, $_SERVER[ 'REQUEST_URI' ] ) !== false )
                        {
                            $_SERVER[ 'REQUEST_URI' ] = preg_replace( '#^' . preg_quote( Settings::get( 'portalurl' ), '#' ) . '#is', '', $url );
                            $this->Input->set( '_call', $_SERVER[ 'REQUEST_URI' ] );
                            $GLOBALS[ 'IS_FRONTPAGE' ] = true;
                        }
                        else
                        {
                            $GLOBALS[ 'IS_FRONTPAGE' ] = true;
                            $this->Input->set( '_call', preg_replace( '#^' . preg_quote( Settings::get( 'portalurl' ), '#' ) . '#is', '', $url ) );
                        }
                    }
                }


                // Frontend defaults
                // execute Router
                $vars = $this->Router->execute( false )->getVariables();


                if ( $this->Router->isFatalError() )
                {

                    if ( $this->getApplication()->getOption( 'defaultController' ) )
                    {
                        /**
                         *
                         */
                        define( 'CONTROLLER', ucfirst( strtolower( $this->getApplication()->getOption( 'defaultController' ) ) ) );
                        /**
                         *
                         */
                        define( 'ACTION', ucfirst( strtolower( $this->getApplication()->getOption( 'defaultAction' ) ) ) );
                    }


                    $this->Page->error( 404, trans( 'Die von Ihnen aufgerufene Seite existiert.' ) );
                }
                elseif ( !$this->Router->isValidRoute() )
                {
                    $this->Page->error( 500, trans( 'Der Server kann die Anforderung aufgrund eines Fehlers nicht ausführen.' ) );
                }
                else
                {
                    $this->Input->setFromRouter( $vars );
                    $this->Input->set( 'cp', strtolower( $this->Router->getApplicationController() ) );
                }


                // remove the cmd from rewrite
                $this->Input->remove( 'cmd' );
                $this->Input->setFromRouter( $vars );

                if ( $this->Router->getApplicationController() && $this->Router->getAction() )
                {
                    $_controller = ucfirst( strtolower( $this->Router->getApplicationController() ) );
                    $_action     = ucfirst( strtolower( $this->Router->getAction() ) );
                }
                else
                {
                    // use the registred Default controller
                    $_controller = ucfirst( strtolower( $this->getApplication()->getOption( 'defaultController' ) ) );
                    $_action     = ucfirst( strtolower( $this->getApplication()->getOption( 'defaultAction' ) ) );
                }
            }
            else
            {
                // Backend defaults
                $vars = $this->Router->execute()->getVariables();

                if ( $this->input( 'action', 'str' ) )
                {
                    $_actionInput = $this->input( 'action', 'str' );
                    $_action      = null;
                    if ( $_actionInput )
                    {
                        $_action = preg_replace( '#([^a-z0-9_]*)#i', '', $_actionInput );
                        if ( $_actionInput !== $_action )
                        {
                            // Error
                            Error::raise( 'Invalid Script action call!' );
                        }
                    }

                    $this->Router->setDefaultAction( $_action );
                    $this->Input->remove( 'action' );
                }
                else
                {
                    $_action = ucfirst( strtolower( $this->Router->getAction() ) );
                }

                $this->Input->setFromRouter( $vars );

                $_controller = ucfirst( strtolower( $this->getApplication()->getOption( 'defaultController' ) ) );
            }
        }
        else
        {

            if ( $this->getApplication()->getMode() !== Application::BACKEND_MODE )
            {
                // Frontend
                $_controllerInput = $this->input( 'cp' );

                if ( Plugin::isPlugin( $_controllerInput ) )
                {
                    $this->Router->isAddon = $_controllerInput;
                }
            }
            else
            {
                // Backend
                $_controllerInput = $this->input( 'adm' );

                /**
                 * is not logged in the use contoller auth
                 */
                if ( Action::requireLogin() && !User::isLoggedIn() )
                {
                    $_controllerInput = 'auth';
                }
            }


            /**
             * secure check the controller
             */
            $_controller = preg_replace( '#([^a-z0-9_]*)#i', '', $_controllerInput );
            if ( $_controllerInput !== $_controller )
            {
                // Error
                Error::raise( 'Invalid Script call! ' . __LINE__ . ' Controller:' . $this->getApplication()->getMode() . print_r( $this->input( 'adm' ), true ) );
            }


            /**
             * secure check the action
             */
            $_actionInput = $this->input( 'action' );

            if ( $_actionInput )
            {
                $_action = preg_replace( '#([^a-z0-9_]*)#i', '', $_actionInput );
                if ( $_actionInput !== $_action )
                {
                    // Error
                    Error::raise( 'Invalid Script action call!' );
                }
            }
            else
            {
                $this->Input->set( 'action', strtolower( $this->getApplication()->getOption( 'defaultAction' ) ) );
            }
        }


        if ( empty( $_action ) )
        {
            $_action = 'index';
        }

        if ( $this->Router && $this->Router->isAddon )
        {
            $this->addonName = $this->Router->isAddon; // set the Addon Name
            $_controller     = 'Plugin'; // reset controller to Plugin Controller
        }

        $_controllerBase = $_controller;

        if ( empty( $_controllerBase ) )
        {
            $this->_callError();
        }

        if ( $this->getApplication()->getMode() === Application::BACKEND_MODE )
        {
            switch ( strtolower( $_controllerBase ) )
            {
                case 'lang':
                    $_controller = 'Main';
                    $_action     = 'Lang';
                    break;
            }

            /**
             *
             */
            define( 'CONTROLLER', ucfirst( strtolower( $_controller ) ) );
            /**
             *
             */
            define( 'ACTION', ucfirst( strtolower( $_action ) ) );
        }
        else
        {
            /**
             *
             */
            define( 'CONTROLLER', ucfirst( strtolower( $_controllerBase ) ) );
            /**
             *
             */
            define( 'ACTION', ucfirst( strtolower( $_action ) ) );

            /**
             *
             */
            define( 'DOCUMENT_EXTENSION', $this->Router->getDocumentExtension() );
            /**
             *
             */
            define( 'DOCUMENT_NAME', $this->Router->getDocumentName( false ) );
        }

        if ( !defined( 'CONTROLLER' ) || CONTROLLER === '' )
        {
            $this->_callError();
        }
    }

    /**
     *
     * @todo clean the runController function
     */
    public function runController($ajaxCall = false)
    {
      #  $this->getRoute();

        $isBackend = $this->isBackend();

        if ( !defined( 'REQUEST' ) )
        {
            /**
             *
             */
            define( 'REQUEST', $this->Env->requestUri() );
        }

        $this->getRoute();
        $this->_initController( $this );


        $useFastLoad = false;

        if ( strpos( REQUEST, 'asset/' ) !== false || strpos( REQUEST, 'assets/' ) !== false || strpos( REQUEST, 'main/lang' ) !== false )
        {
            $useFastLoad = true;
        }

        /**
         * call the router
         */
        $this->_initCall();
        /*
                if ( !$this->input('send') && preg_match('#(edit|add)#', ACTION) ) {
                    Session::save( 'DraftLocation' . md5($this->Env->location()), array(
                            $this->Env->location(),
                            CONTROLLER,
                            ACTION) );
                }
        */
        $error = (string)$this->input( 'error' );

        if ( Settings::get( 'websiteoffline', false ) && !User::hasPerm( 'generic/canviewoffline', false ) && !$isBackend )
        {
            if ( defined( 'CONTROLLER' ) && strtolower( CONTROLLER ) !== 'asset' )
            {
                $this->load( 'Document' );
                $this->load( 'Breadcrumb' );
                $this->Document->offline( trans( 'Die Website befindet sich im Offline Modus. Versuchen Sie es bitte zu einem späteren Zeitpunkt erneut.<p>Vielen Dank für Ihr Verständnis</p>' ), true );
            }
        }

        if ( !$error && ( $useFastLoad || $ajaxCall ) )
        {
            $this->fastLoad();
            exit;
        }


        /**
         * Stop if is input error
         */
        if ( $error )
        {
            User::disableUserLocationUpdate();
            if ( $error === '400' )
            {
                $this->Page->error( 400, trans( 'Der Server konnte die Syntax der Anforderung nicht interpretieren.' ) );
            }
            else if ( $error === '401' )
            {
                $this->Page->error( 401, trans( 'Die Anfrage erfordert eine Authentifizierung.' ) );
            }
            else if ( $error === '403' )
            {
                $this->Page->error( 403, trans( 'Die Anfrage wurde mangels Berechtigung des Clients nicht durchgeführt.' ) );
            }
            else if ( $error === '404' )
            {
                $this->Page->error( 404, trans( 'Die von Ihnen aufgerufene Seite existiert.' ) );
            }
            else if ( $error === '500' )
            {

                $this->Page->error( 500, trans( 'Der Server kann die Anforderung aufgrund eines Fehlers nicht ausführen.' ) );
            }
        }

        if ( Session::get( 'seemode' ) )
        {
            /**
             *
             */
            define( 'IS_SEEMODE', true );
        }
        else
        {
            /**
             *
             */
            define( 'IS_SEEMODE', false );
        }


        if ( $this->_post( 'cp' ) != 'tracker' && !$isBackend )
        {
            Session::save( 'HTTP_REFERER', $this->Env->refferer() );
        }

        $seemode = Cookie::get( 'seemodePopup', false );
        $authKey = $this->input( 'authKey' );

        if ( !$isBackend )
        {
            $this->load( 'Breadcrumb' );
            $this->load( 'SideCache' );
        }
        else
        {
            //    SystemManager::cleanControllerActions();
        }


        if ( strpos( REQUEST, 'asset/' ) === false && ( $this->input( 'seemodePopup' ) && $authKey ) || ( $seemode && $seemode != '' ) )
        {

            $key      = $authKey ? $authKey : ( $seemode && Cookie::get( 'loginpermanet' ) ? Cookie::get( 'loginpermanet' ) : '' );
            $valid    = false;
            $useLogin = false;

            if ( !User::isLoggedIn() )
            {
                $valid    = User::login( false, false, $key );
                $useLogin = true;
            }
            else
            {
                $valid = User::login( false, false, $key );
            }

            if ( !User::isLoggedIn() )
            {
                if ( IS_AJAX )
                {
                    Library::sendJson( false, 'Auth error! 0' );
                }

                die( 'Auth error! 0' );
            }


            if ( !$useLogin && $key && ( User::get( 'uniqidkey' ) || Cookie::get( 'uhash' ) ) )
            {

                if ( !$valid && User::get( 'uniqidkey' ) === $key )
                {
                    Cookie::set( 'seemodePopup', true );
                    Cookie::set( 'loginpermanet', User::getUserUiqKey(), $GLOBALS[ 'SESSIONTIMEOUT' ] );
                    Cookie::set( 'uhash', User::getUserUiqKey(), $GLOBALS[ 'SESSIONTIMEOUT' ] );
                    Session::save( 'seemodePopup', true );
                    $valid = true;
                }

                if ( !$valid && Cookie::get( 'uhash' ) === $key )
                {
                    Cookie::set( 'seemodePopup', true );
                    Cookie::set( 'loginpermanet', User::getUserUiqKey(), $GLOBALS[ 'SESSIONTIMEOUT' ] );
                    Cookie::set( 'uhash', User::getUserUiqKey(), $GLOBALS[ 'SESSIONTIMEOUT' ] );
                    Session::save( 'seemodePopup', true );
                    $valid = true;
                }


                if ( $valid )
                {
                    Session::save( 'username', User::getUsername() );
                    Session::save( 'password', User::get( 'password' ) );

                    // Cache des Users löschen und anschießend neu aufbauen (backend)
                    Cache::delete( 'menu_user_' . User::getUserId() );
                }
            }

            if ( $useLogin )
            {
                if ( $valid )
                {
                    Cookie::set( 'seemodePopup', true );
                    Cookie::set( 'loginpermanet', User::getUserUiqKey(), $GLOBALS[ 'SESSIONTIMEOUT' ] );
                    Cookie::set( 'uhash', User::getUserUiqKey(), $GLOBALS[ 'SESSIONTIMEOUT' ] );

                    Session::save( 'seemodePopup', true );
                    Session::save( 'username', User::getUsername() );
                    Session::save( 'password', User::get( 'password' ) );

                    // Cache des Users löschen und anschießend neu aufbauen (backend)
                    Cache::delete( 'menu_user_' . User::getUserId() );
                }
                else
                {
                    if ( IS_AJAX )
                    {
                        Library::sendJson( false, 'Auth error! Invalid login data!' );
                    }
                    die( 'Auth error! Invalid login data!' );
                }
            }

            if ( !$valid )
            {
                if ( IS_AJAX )
                {
                    Library::sendJson( false, 'Auth error!' );
                }
                die( 'Auth error! xx' );
            }
        }

        /**
         *
         */
        if ( $this->input( 'seemode' ) === 'on' /* || (User::isLoggedIn() && User::isAdmin()) */ )
        {
            Session::save( 'seemode', true );
        }
        elseif ( $this->input( 'seemode' ) === 'off' )
        {
            Session::delete( 'seemode' );
            Library::sendJson( true );
        }


        $_mode = ( $this->getApplication()->getMode() === Application::BACKEND_MODE ? 'Backend' : 'Frontend' );

        /*
          if ( $this->Router->isApplication )
          {
          $controllerFile = MODULES_PATH . 'App/Controller/' . $_mode . '.php';
          $modul = 'App';
          $className = 'App_Controller_' . $_mode;

          $this->isApplication = true;
          }
          else
          {
         */
        if ( !$isBackend && $this->Router->isAddon )
        {
            $this->isAddon = true;
        }

        $controllerFile = MODULES_PATH . CONTROLLER . '/Controller/' . $_mode . '.php';


        $modul     = CONTROLLER;
        $className = CONTROLLER . '_Controller_' . $_mode;
        //}


        if ( is_readable( $controllerFile ) )
        {

            // is the modul active
            if ( !$this->getApplication()->isActiveModul( $modul ) )
            {
                if ( !IS_AJAX )
                {
                    User::disableUserLocationUpdate();

                    if ( $isBackend )
                    {
                        Error::raise( trans( 'Das Modul "' . $modul . '" wurde nicht aktiviert!' ) );
                    }
                    else
                    {
                        $this->Page->error( 501, trans( 'Das Modul "' . $modul . '" wurde nicht aktiviert!' ) );
                    }
                }
                else
                {
                    Library::sendJson( false, trans( 'Das Modul "' . $modul . '" wurde nicht aktiviert!' ) );
                }
            }

            $this->load( $className, '__RunController' );


            if ( is_callable( array(
                $this->__RunController,
                'runAction'
            ) )
            )
            {
                if ( $isBackend )
                {
                    // adding the Contoller Menu
                    $this->addControllerMenu( $this->getModulBackendMenu() );

                    //
                    $this->updateDock();
                }

                $this->__RunController->runAction( $this );

                User::disableUserLocationUpdate();
                $this->Page->error( 404, trans( 'Die von Ihnen aufgerufene Seite existiert nicht.' ) . ' @' . __LINE__ );
                exit;
            }
            else
            {
                User::disableUserLocationUpdate();

                // error 404
                if ( DEBUG )
                {
                    trigger_error( sprintf( 'The Controller "%s" has no method "runAction"!', CONTROLLER ), E_USER_ERROR );
                }
                else
                {
                    $this->load( 'Page' );
                    $this->Page->error( 404, trans( 'Die von Ihnen aufgerufene Seite existiert nicht.' ) . ' @' . __LINE__ );
                }
            }
        }
        else
        {
            User::disableUserLocationUpdate();

            // error 404
            if ( DEBUG )
            {
                trigger_error( 'The Controller is not readable in the Directory: ' . $controllerFile, E_USER_ERROR );
            }
            else
            {
                $this->load( 'Page' );
                $this->Page->error( 404, trans( 'Die von Ihnen aufgerufene Seite existiert nicht.' ) . ' @' . __LINE__ );
            }
        }

        $this->load( 'Page' );
        User::disableUserLocationUpdate();
        $this->Page->error( 404, trans( 'Die von Ihnen aufgerufene Seite existiert nicht.' ) . ' @' . __LINE__ );

        exit;


        // error 404
    }


    private function fastLoad()
    {

        $_mode          = ( $this->getApplication()->getMode() === Application::BACKEND_MODE ? 'Backend' : 'Frontend' );
        $className      = CONTROLLER . '_Controller_' . $_mode;
        $controllerFile = MODULES_PATH . CONTROLLER . '/Controller/' . $_mode . '.php';
        if ( is_readable( $controllerFile ) )
        {
            $this->load( $className, '__RunController' );
            if ( is_callable( array(
                $this->__RunController,
                'runAction'
            ) )
            )
            {
                $this->__RunController->runAction( $this );
            }
        }

        User::disableUserLocationUpdate();
        $this->Page->error( 404, trans( 'Die von Ihnen aufgerufene Seite existiert nicht.' ) . ' @' . __LINE__ );
        exit;
    }


}

?>