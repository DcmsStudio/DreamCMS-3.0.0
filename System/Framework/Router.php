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
 * @package     DreamCMS
 * @version     3.0.0 Beta
 * @category    Framework
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Router.php
 *
 */
class Router extends Router_Abstract
{

    /**
     * @var null
     */
    protected static $objrInstance = null;

    /**
     * @var string
     */
    protected $appRegex = '(blog|page|business|cookbook|documentation|download|movie|product)';

    //
    /**
     * @var bool
     */
    public $isApplication = false;

    /**
     * @var bool
     */
    public $isAddon = false;

    /**
     * @var null
     */
    public $applicationType = null;

    //
    /**
     * @var
     */
    protected $uri_string;

    // the original uri
    /**
     * @var
     */
    protected $patched_uri_string; // the patched uri only removed filename
    /**
     * cache all route config files
     * @var array
     */

    protected $routeConfigs = array();

    /**
     *
     * @var array
     */
    protected $modulRouteConfig = array();

    /**
     * cache all routes
     * @var array
     */
    protected $routes = array();

    /**
     * @var array
     */
    protected $_keyval = array();

    /**
     * @var array
     */
    protected $_segments = array();

    /**
     * @var int
     */
    protected $_numSegments = 0;

    /**
     * @var
     */
    protected $_ModulController;

    /**
     * @var string
     */
    protected $_ModulAction = 'index';

    /**
     * @var null
     */
    protected $_currentRoute = null;

    /**
     * @var null
     */
    protected $_defaultAction = null;

    /**
     * @var null
     */
    protected $_defaultController = null;

    /**
     * @var null
     */
    protected $_documentname = null;

    /**
     * @var null
     */
    protected $_documentNameOnly = null;

    /**
     * @var null
     */
    protected $_documentExtension = null;

    /**
     *
     * @var boolean
     */
    protected $_routeError = null;

    /**
     *
     * @var string
     */
    protected $_urlVariable = ':';

    /**
     *
     * @var string
     */
    protected $_urlDelimiter = '/';

    /**
     * Holds names of all route's pattern variable names. Array index holds a position in URL.
     * @var array
     */
    protected $_variables = array();

    /**
     * @var null
     */
    protected $_currentRouteParams = null;

    /**
     * @var null
     */
    protected $_currentRouteParamKeyIndexes = null;

    /**
     * @var bool
     */
    protected $_hardError = false;

    /**
     * @var array
     */
    private $_mapIndex = array();

    /**
     * @var bool
     */
    public static $RouteOptions = false;

    /**
     *
     * @var Router_Regex 
     */
    private $routeRegex = null;

    /**
     *
     * @var Router_Static 
     */
    private $routeStatic = null;

    /**
     * Prevent cloning of the object (Singleton)
     */
    final private function __clone()
    {
        
    }

    /**
     * Return the current object instance (Singleton)
     * @return object
     */
    public static function getInstance()
    {
        if ( self::$objrInstance === null )
        {
            self::$objrInstance = new Router();
        }

        return self::$objrInstance;
    }

    /**
     *
     */
    public function freeMem()
    {

        $this->routes = array();
        $this->_numSegments = 0;
        $this->_keyval = null;
        $this->_segments = null;
        $this->_currentRoute = null;
        $this->_mapIndex = array();
        $this->modulRouteConfig = array();
        $this->routeConfigs = array();

        self::$RouteOptions = false;
        self::$objrInstance = null;
    }

    public function __destruct()
    {
        parent::__destruct();
        $this->freeMem();
    }

    /**
     * @return null
     */
    public function getDefaultController() {
		return $this->_defaultController;
	}

    /**
     * @return null
     */
    public function getDefaultAction() {
		return $this->_defaultAction;
	}


    /**
     * @return bool
     */
    public function isFatalError()
    {
        return $this->_hardError;
    }

    /**
     *
     * @param string $modul
     * @param        $data
     * @internal param array $files
     */
    public function addRouteConfig( $modul, $data )
    {
        $this->routeConfigs[ ucfirst( strtolower( $modul ) ) ] = $data;
    }

    /**
     *
     * @param string $controller
     */
    public function setDefaultController( $controller )
    {
        $this->_defaultController = $controller;
    }

    /**
     *
     * @param string $action
     */
    public function setDefaultAction( $action )
    {
        $this->_defaultAction = $action;
    }

    /**
     *
     * @param array $arr
     */
    public function registerApplications( $arr = array() )
    {
        if ( is_array( $arr ) && count( $arr ) > 0 )
        {
            $this->appRegex = '(' . implode( '|', $arr ) . '|' . substr( $this->appRegex, 1, -1 ) . ')';
        }
    }

    /**
     * Parse cli arguments
     *
     * Take each command line argument and assume it is a URI segment.
     *
     * @access    private
     * @return    string
     */
    private function parseCliArgs()
    {
        $args = array_slice( $_SERVER[ 'argv' ], 1 );
        return $args ? '/' . implode( '/', $args ) : '';
    }

    /**
     * Filter segments for malicious characters
     *
     * @access    private
     * @param    string
     * @return    string
     */
    private function filterUri( $str )
    {
        // Convert programatic characters to entities
        $bad = array(
            '$',
            '(',
            ')',
            '%28',
            '%29' );
        $good = array(
            '&#36;',
            '&#40;',
            '&#41;',
            '&#40;',
            '&#41;' );
        $str = str_replace( $bad, $good, $str );

        return remove_invisible_characters( $str );
    }

    /**
     * Explode the URI Segments. The individual segments will
     * be stored in the $this->segments array.
     *
     * @access    private
     * @param string $str
     * @return    array
     */
    private function extractSegments( $str = '' )
    {

        $segments = array();
        if ( !$str )
        {
            return $segments;
        }

        $exploded = explode( "/", /* preg_replace("|/*(.+?)/*$|", "\\1", */ $str /* ) */ );

        foreach ( $exploded as $val )
        {
            if ( empty( $val ) )
            {
                continue;
            }
            // Filter segments for security
            $val = trim( $this->filterUri( $val ) );
            $segments[] = $val;
        }

        return $segments;
    }

    /**
     *
     * @return bool
     */
    private function validateUri()
    {
        if ( is_dir( MODULES_PATH . ucfirst( strtolower( $this->_ModulController ) ) ) )
        {
            return true;
        }

        return false;
    }

    /**
     * returns only the request uri out of _GET Params
     *
     * @param string $uri
     * @return string
     */
    private function removeGetFromUri( $uri )
    {
        $uri = explode( '?', $uri );

        return $uri[ 0 ];
    }

    /**
     * will only extract (.htm, .html, .dcms, .php)
     *
     * @param string|null $uri
     * @return mixed|null
     */
    private function extractDocumentName( $uri )
    {
        $uri = $this->removeGetFromUri( $uri );

        if ( strpos( $uri, '.' ) === false ||
                (stripos( $uri, '.htm' ) === false &&
                stripos( $uri, '.html' ) === false &&
	                stripos( $uri, '.xhtml' ) === false &&
                stripos( $uri, '.dcms' ) === false &&
                stripos( $uri, '.php' ) === false)
        )
        {
            return null;
        }

        $uris = explode( '/', $uri );

        return array_pop( $uris );
    }

    /**
     *
     */
    public function execute( $dbg = 0 )
    {
        //$this->compileRoules();
        $setVars = true;
        //Debug::store( 'Router', 'Start Router' );

        if ( $this->isAddon && defined('REQUEST') )
        {
            $r = explode( '/', REQUEST );
            array_shift( $r );
            array_shift( $r );
            $uri = '/' . implode( '/', $r );
        }
        else
        {

            if ($this->Input->getMethod() === 'get') {
                if ( isset($_GET['_call']) )
                {
                    if ($this->_get( '_call' ))
                    {
                        $uri = $this->filterUri( $this->_get( '_call' ) );
                    }
                    else
                    {

                        return $this;
                    }
                }
            }
            else {
                $uri = $this->filterUri( $_SERVER[ 'REQUEST_URI' ] );
            }
        }


        if ( substr( $uri, 0, 11 ) == '/index.php/' )
        {
            $uri = substr( $uri, 11 );
        }




        // remove all invisible chars
        $uri = remove_invisible_characters( $uri );


        // remove GET params from query
        $uri = preg_replace( '#\?.*$#', '', $uri );


        if ( empty( $uri ) )
        {
            //  Debug::store( 'Router', 'Stop Router @' . __LINE__ );
            return $this;
        }

        // ----------- PATCH

        if ( substr( $uri, 0, 1 ) === '/' )
        {
            $uri = substr( $uri, 1 );
        }


        $tmp = $this->extractSegments( $uri );
        $d = explode( '/', substr( ROOT_PATH, 0, -1 ) );
        $dirname = end( $d );

        if ( array_shift( $tmp ) === $dirname )
        {
            $uri = substr( $uri, strlen( $dirname ) + 1 );
        }

        // ----------- END PATCH


        $this->_documentname = $this->extractDocumentName( $uri );

        if ( $this->_documentname )
        {
            $uri = str_replace( '/' . $this->_documentname, '', $uri );
            $uri = str_replace( $this->_documentname, '', $uri );

            $names = explode( '.', $this->_documentname );
            $this->_documentExtension = array_pop( $names );
            $this->_documentNameOnly = implode( '.', $names );
        }


        $this->_currentRoute = $uri;
        if ( substr( $this->_currentRoute, -1 ) !== '/' )
        {
            $this->_currentRoute .= '/';
        }


        $this->_segments = $this->extractSegments( $this->_currentRoute );
        $this->_numSegments = count( $this->_segments );

        // first Segment is the Modul
        if ( $this->_numSegments > 0 )
        {
            $_tmp = array_shift( $this->_segments );
            //$_clean = preg_replace( '/[^a-z][a-z0-9]*/isS', '', $_tmp );
            if ( !ctype_alnum($_tmp) )
            {
                // Error 404
                $this->_routeError = true;
                $this->_hardError = true; // not exists

                return $this;
            }


            $this->isApplication = false;
            $appType = '';


            if ( $_tmp === 'app' )
            {
                $_ModulController = ucfirst( strtolower( $_tmp ) );
            }
            else
            {
                $_ModulController = ucfirst( strtolower( $_tmp ) );
            }


            if ( Plugin::isPlugin( $_tmp ) )
            {
                $this->isAddon = $_tmp;

                if ( !Plugin::isPublished( $_tmp ) || !Plugin::isExcecutable( $_tmp ) )
                {
                    // Error 404
                    $this->_routeError = true;
                    $this->_hardError = true; // not exists
                    //    Debug::store( 'Router', 'Stop Router - Error 404 @' . __LINE__ );

                    return $this;
                }
                $this->routeConfigs[ $_ModulController ] = Plugin::loadRouteConfig( $_tmp );
            }

            $dbg = 0;


            // has routes?
            if ( isset( $this->routeConfigs[ $_ModulController ] ) )
            {

                $this->_ModulController = $_ModulController;

                if ( !$this->getRoute( $setVars, $dbg ) )
                {
                    $this->_Controller = $this->_ModulController;
                    $this->_ControllerAction = $this->_defaultAction;
                }
                else
                {
                    if ( ($action = $this->getVariable( 'action' )) !== null )
                    {

                        $_clean = preg_replace( '/[^a-z][a-z0-9_]*/isS', '', $action );

                        if ( $action !== $_clean )
                        {
                            // Error 404
                            $this->_routeError = true;
                            $this->_hardError = true; // not exists

                            return $this;
                        }
                        else
                        {
                            $this->_ControllerAction = $action;
                        }
                    }
                }


                if ( $this->isApplication )
                {
                    $this->appKey = $appType;
                    $this->_ModulController = 'App';
                    $this->_Controller = $this->_ModulController;
                }
            }
            else
            {
                $this->_routeError = true;
                $this->_hardError = true; // not exists
            }
        }
        else
        {
            $this->_ModulController = ucfirst( strtolower( $this->_defaultController ) );


            if ( $this->_numSegments === 0 && !empty( $this->_segments[ 0 ] ) )
            {
                // $_clean = preg_replace( '/[^a-z][a-z]*/i', '', $this->_segments[ 0 ] );
                if ( !ctype_alpha($this->_segments[ 0 ]) /*$this->_segments[ 0 ] !== $_clean*/ )
                {
                    // Error 404
                    $this->_routeError = true;
                    $this->_hardError = true; // not exists
                    //      Debug::store( 'Router', 'Stop Router @' . __LINE__ );

                    return $this;
                }

                $_ModulController = ucfirst(strtolower($this->_segments[ 0 ])); //ucfirst( strtolower( $_clean ) );

                if ( $this->_defaultController !== $_ModulController && isset( $this->routeConfigs[ $_ModulController ] ) )
                {
                    $this->_ModulController = $_ModulController;
                    $this->_hardError = false; // not exists
                }
            }


            $this->_Controller = $this->_ModulController;
            $this->_ControllerAction = $this->_defaultAction;
        }

        if ( !$this->isAddon && !$this->validateUri() )
        {
            // Error 404
            $this->_routeError = true;
            $this->_hardError = true; // not exists
        }

        return $this;
    }

    /**
     * get the route config for the modul
     * @param string $modul (ucfirst)
     * @return null,string
     */
    public function loadRouteConfig( $modul )
    {

        if ( !isset( $this->routeConfigs[ $modul ] ) )
        {
            return null;
        }

        return $this->routeConfigs[ $modul ];
    }

    /**
     *
     * @param string $matches
     * @return string
     */
    private function buildRegex( $matches )
    {
        $slash = '';
        if ( substr( $matches, 0, 1 ) === '/' )
        {
            $slash = '/?';
            $matches = substr( $matches, 1 );
        }

        /*
          $matches = str_replace(
          array(':num', ':alpha', ':alphanum', ':any'),
          array('\d+', '[a-zA-Z]+', '[a-zA-Z0-9]', '^[/]*'), $matches);
         */

        $key = str_replace( ':', '', $matches );

        if ( isset( $this->_currentRouteParams[ $key ] ) )
        {
            return '(' . $slash . $this->_currentRouteParams[ $key ] . ')';
        }
        else
        {
            return substr( $matches, 0, 1 ) === ':' ? $matches : '(' . $slash . '[^/]+?)';
        }
    }

    /**
     *
     * @param string $rule
     * @param array $data
     * @return string
     */
    private function prepareRuleRegex( $rule, $data = null )
    {
        $this->_currentRouteParams = (isset( $data[ 'params' ] ) ? $data[ 'params' ] : array());
        $this->_currentRouteParamKeyIndexes = null;

        if ( isset( $data[ 'params' ] ) )
        {
            $this->_currentRouteParamKeyIndexes = $data[ 'params' ];
        }

        preg_match_all( '@(:[\w]+)@', $rule, $matches );
        if ( empty( $matches[ 1 ] ) )
        {
            $regex = $rule;

            if ( substr( $regex, -1 ) === '/' )
            {
                $regex .= '?';
            }

            if ( substr( $regex, -2 ) !== '/?' )
            {
                $regex .= '/?';
            }

            return $regex;
        }


        $this->_mapIndex = array();
        foreach ( $matches[ 1 ] as $idx => $match )
        {
            $this->_mapIndex[ $idx + 1 ] = substr( $match, 1 );
            $rule = str_replace( $match, $this->buildRegex( $match ), $rule );
        }


        $regex = addcslashes( $rule, '#' );
        if ( substr( $regex, -1 ) === '/' )
        {
            $regex .= '?';
        }

        if ( substr( $regex, -2 ) !== '/?' )
        {
            $regex .= '/?';
        }
        return $regex;
    }

    /**
     * @param $tmp
     */
    public function compileRoules( &$tmp )
    {
        foreach ( $tmp as $modul => &$r )
        {
            if ( !is_array( $r ) )
            {
                continue;
            }

            foreach ( $r as $mod => &$roule )
            {
                if ( strpos( $roule[ 'rule' ], ':' ) !== false )
                {
                    $roule[ 'compiled_rule' ] = $this->prepareRuleRegex( $roule[ 'rule' ], $roule );
                }
                else
                {
                    $roule[ 'compiled_static_rule' ] = $this->prepareRuleRegex( $roule[ 'rule' ], $roule );
                }

                $roule[ 'map_index' ] = $this->_mapIndex;
            }
        }
    }

    /**
     *
     *
     * @param bool $setVars
     * @param bool $dbg
     * @return bool
     */
    private function getRoute( $setVars = true, $dbg = false )
    {

        $this->_routeError = false;
        $_maps = $this->loadRouteConfig( $this->_ModulController );

        if ( !is_array( $_maps ) )
        {

            $this->_routeError = true;
            return false;
        }


        #if ( $dbg )
        #{
       #     print_r( $this->routeConfigs );
        #    echo $this->_ModulController;
        #    print_r( $_maps );
        #    exit;
        #}

        $_found = false;
        $_defaults = array();

        Debug::store( 'Router', 'Start Router' );


        foreach ( $_maps as $idx => $opt )
        {

            $_mapKeys = isset( $opt[ 'paramkeys' ] ) ? $opt[ 'paramkeys' ] : array();
            $found = null;
            $isRegexRule = false;

            if ( isset( $opt[ 'compiled_static_rule' ] ) && $opt[ 'compiled_static_rule' ] )
            {
                $rule = $opt[ 'compiled_static_rule' ];
                $this->_mapIndex = $opt[ 'map_index' ];
            }
            elseif ( isset( $opt[ 'compiled_rule' ] ) && $opt[ 'compiled_rule' ] )
            {
                $rule = $opt[ 'compiled_rule' ];
                $this->_mapIndex = $opt[ 'map_index' ];
                $isRegexRule = true;
            }
            else
            {
                $isRegexRule = true;
                $rule = $this->prepareRuleRegex( $opt[ 'rule' ], $opt );
            }



           # if ( $dbg )
          #  {
          #      print_r( $this->_currentRouteParams );
          #      print_r( $this->_currentRouteParamKeyIndexes );
          #      echo $opt[ 'rule' ] . ' -> ' . $rule . ' ' . $this->_currentRoute . "\n";
           # }

            if ( $isRegexRule )
            {
                if ( $this->routeRegex === null )
                {
                    $this->routeRegex = new Router_Regex( $rule, $_defaults, $_mapKeys, false, $this->_mapIndex );
                }
                else
                {
                    $this->routeRegex->setRule( $rule, $_defaults, $_mapKeys, false, $this->_mapIndex );
                }

                $found = $this->routeRegex->match( $this->_currentRoute );
            }
            else
            {
                if ( $this->routeStatic === null )
                {
                    $this->routeStatic = new Router_Regex( $rule, $_defaults, $_mapKeys, false, $this->_mapIndex );
                }
                else
                {
                    $this->routeStatic->setRule( $rule, $_defaults, $_mapKeys, false, $this->_mapIndex );
                }

                $found = $this->routeStatic->match( $this->_currentRoute );
            }

            if ( $this->_numSegments === 2 && $this->_segments[ 0 ] === 'index' && !is_array( $found ) )
            {

                $routeStr = preg_replace( '#/?index/?$#iS', '', $this->_currentRoute );
                if ( $isRegexRule )
                {
                    $this->routeRegex->setRule( $rule, $_defaults, $_mapKeys, false, $this->_mapIndex );
                    $found = $this->routeRegex->match( $routeStr );
                }
                else
                {
                    $this->routeStatic->setRule( $rule, $_defaults, $_mapKeys, false, $this->_mapIndex );
                    $found = $this->routeStatic->match( $routeStr );
                }
            }


            /* }
              else
              {
              $rule = $_data['rule'];
              $route = new Router_Static($rule,
              array(
              'controller' => $_data['controller'],
              'action' => $_data['action']
              )
              );

              $found = $route->match($this->_currentRoute, true);
              }
             */

            if ( is_array( $found ) )
            {
              #  if ( $dbg )
             #   {
              #      echo '---- FOUND ----';
              #      print_r( $opt );
              #  }

                if ( $this->isAddon )
                {
                    $opt[ 'controller' ] = 'Plugin';
                }

                $this->_ModulAction = $opt[ 'action' ];
                self::$RouteOptions = $opt;
                $this->setVariables( $found, $opt[ 'controller' ], $opt[ 'action' ], $_mapKeys, $rule );
                $_found = true;
                break;
            }
        }

        Debug::store( 'Router', 'Stop Router' );

        #  die( $rule );
        if ( !$_found )
        {
            return false;
        }

        if ( $setVars )
        {
            $_vars = $this->getVariables();


            // print_r($_vars);

            if ( $_vars )
            {

                $_foundActionKey = false;
                if ( isset( $_vars[ 'action' ] ) )
                {
                    $_foundActionKey = true;
                }


                if ( !$_foundActionKey )
                {
                    // route not found
                    $_action = $this->getAction();

                    if ( $_action === null )
                    {

                        // Error
                        $this->_routeError = true;
                        $this->_hardError = true;
                        return;
                    }
                    else
                    {
                        // Secure
                        $_clean = preg_replace( '/[^a-z0-9_]*/iS', '', $_action );
                        if ( $_action !== $_clean )
                        {
                            // Error
                            $this->_routeError = true;
                            $this->_hardError = true;
                            return;
                        }

                        $this->_ModulAction = $_action;
                        $_vars[ 'action' ] = $_action;
                    }
                }
                else
                {

                    // Secure
                    $_clean = preg_replace( '/[^a-z0-9_]*/iS', '', $_vars[ 'action' ] );
                    if ( $_vars[ 'action' ] !== $_clean )
                    {
                        // Error
                        $this->_routeError = true;
                        $this->_hardError = true;
                        return;
                    }

                    $this->_ModulAction = $_vars[ 'action' ];
                }

                $this->setVariables( $_vars );
            }
        }

        return true;
    }

    /**
     *
     * @return string
     */
    public function routeAction()
    {

        return $this->_ModulAction;
    }

    /**
     *
     * @return string
     */
    public function routeController()
    {
        return $this->_Controller;
    }

    /**
     * @param boolean $withExtension default is true
     * @return string
     */
    public function getDocumentName( $withExtension = true )
    {

        if ( !$withExtension )
        {
            return !empty( $this->_documentNameOnly ) ? $this->_documentNameOnly : null;
        }


        return !empty( $this->_documentname ) ? $this->_documentname : null;
    }

    /**
     * @return null
     */
    public function getDocumentExtension()
    {
        return !empty( $this->_documentExtension ) ? $this->_documentExtension : null;
    }

    /**
     * Returns an array with all segments
     *
     * @return array
     */
    public function getSegments()
    {
        return $this->extractSegments( $this->_currentRoute );
    }

    /**
     * @return int
     */
    public function numOfSegments()
    {
        return $this->_numSegments;
    }

    /**
     *
     * @return boolean
     */
    public function isValidRoute()
    {
        return ($this->_routeError ? false : true);
    }

    /**
     * Returns the Uri (from "/x/y" to "index.php?var1=x&var2=y" ...)
         * @param string $routeStr
     * @return $this|string
     */
    public function routeToArgs( $routeStr )
    {
        $_ModulController = '';
        $_ModulAction = '';
        $temp = '';
        $uri = $this->removeGetFromUri( $routeStr );
        // $_documentname = $this->extractDocumentName( $uri );


        if ( substr( $uri, 0, 1 ) === '/' )
        {
            $uri = substr( $uri, 1 );
        }


        $tmp = $this->extractSegments( $uri );
        $d = explode( '/', substr( ROOT_PATH, 0, -1 ) );
        $dirname = end( $d );

        if ( array_shift( $tmp ) === $dirname )
        {
            $uri = substr( $uri, strlen( $dirname ) + 1 );
        }

        // remove document name if exists
        $_documentname = $this->extractDocumentName( $uri );

        if ( $_documentname )
        {
            $names = explode( '.', $_documentname );
            $_documentExtension = array_pop( $names );
            $_documentNameOnly = implode( '.', $names );

            if ( $_documentExtension )
            {
                $uri = str_replace( '/' . $_documentname, '', $uri );
                $uri = str_replace( $_documentname, '', $uri );
            }
        }


        if ( substr( $uri, -1 ) !== '/' )
        {
            $uri .= '/';
        }


        // print_r( $this->routeConfigs );
        // $this->_currentRoute = $uri;

        $segments = $this->extractSegments( $uri );
        $_numSegments = count( $segments );

        $_returnUri = '';

        // first Segment is the Controller
        if ( $_numSegments > 0 )
        {
            $_ModulController = array_shift( $segments );
            $_ModulController = strtolower( $_ModulController );

            $addon = false;
            if ( Plugin::isPlugin( $_ModulController ) )
            {
                $addon = $_ModulController;
                $this->routeConfigs[ ucfirst( $_ModulController ) ] = Plugin::loadRouteConfig( $_ModulController );
            }


            $_maps = $this->loadRouteConfig( ucfirst( $_ModulController ) );

            if ( !is_array( $_maps ) )
            {
                return $routeStr;
            }






            $_found = false;
            $_defaults = array();


            foreach ( $_maps as $idx => $opt )
            {

                $_mapKeys = isset( $opt[ 'paramkeys' ] ) ? $opt[ 'paramkeys' ] : array();
                $found = null;
                $rule = $this->prepareRuleRegex( $opt[ 'rule' ], $opt );

                if ( !($this->routeRegex instanceof Router_Regex ) )
                {
                    $this->routeRegex = new Router_Regex( $rule, $_defaults, $_mapKeys, false, $this->_mapIndex );
                }
                else
                {
                    $this->routeRegex->setRule( $rule, $_defaults, $_mapKeys, false, $this->_mapIndex );
                }


                $found = $this->routeRegex->match( $routeStr );


                if ( $_numSegments === 2 && $segments[ 0 ] === 'index' && !is_array( $found ) )
                {
                    $routeStr = preg_replace( '#/?index/?$#i', '', $routeStr );
                    $this->routeRegex->setRule( $rule, $_defaults, $_mapKeys, false, $this->_mapIndex );
                    $found = $this->routeRegex->match( $routeStr );
                }

                if ( is_array( $found ) )
                {

                    if ( $addon )
                    {
                        $opt[ 'controller' ] = 'plugin';
                    }

                    $_ModulAction = $opt[ 'action' ];


                    // self::$RouteOptions = $_data;
                    $this->setVariables( $found, $opt[ 'controller' ], $opt[ 'action' ], $_mapKeys, $rule );
                    $_found = true;
                    break;
                }
            }


            if ( !$_found )
            {
                // die($_data[ 'controller' ] . ' uri:'. $uri . ' '. $routeStr);
                return 'index.php?cp=' . $opt[ 'controller' ];
            }



            $ret = 'index.php';
            $index = 0;
            $params = array();


            if ( $opt[ 'controller' ] )
            {
                $params[] = 'cp=' . $opt[ 'controller' ];
                $index++;
            }


            if ( $opt[ 'action' ] )
            {
                $params[] = 'action=' . $opt[ 'action' ];
                $index++;
            }


            foreach ( $found as $k => $v )
            {
                if ( $k === 'cp' || $k === 'action' )
                {
                    continue;
                }
                $params[] = $k . '=' . $v;
                $index++;
            }



            if ( $index )
            {
                $ret .= '?' . implode( '&amp;', $params );
            }
            return $ret;



            die( $opt[ 'controller' ] . ' ' . $routeStr );
            die( $_ModulAction );
            print_r( $routeStr );
            exit;















            $this->getRoute( false );
            $_vars = $this->getVariables();

            if ( $_vars )
            {
                foreach ( $_vars as $key => $value )
                {
                    if ( $key === 'action' )
                    {
                        // Secure
                        $_clean = preg_replace( '/[^a-z0-9_]*/i', '', $value );
                        if ( $value !== $_clean )
                        {
                            return $routeStr;
                        }
                    }

                    $_ModulAction = $value;

                    unset( $_vars[ $key ] );
                }


                if ( $key === 'module' )
                {
                    // Secure
                    $_clean = preg_replace( '/[^a-z]*/i', '', $value );
                    if ( $value !== $_clean )
                    {
                        return $routeStr;
                    }


                    if ( strtolower( $value ) !== $_ModulController )
                    {
                        if ( $_ModulAction )
                        {
                            $_vars[ 'action' ] = $_ModulAction;
                            $_ModulAction = '';
                        }
                    }
                    else
                    {
                        unset( $_vars[ $key ] );
                    }
                }
            }


            foreach ( $_vars as $key => $value )
            {
                $_returnUri .= ($_returnUri ? '&amp;' : '') . $key . '=' . $value;
            }
        }

        if ( empty( $_ModulController ) )
        {
            return $routeStr;
        }

        $_ret = 'index.php?cp=' . $_ModulController;

        if ( !isset( $_vars[ 'action' ] ) && $_ModulAction )
        {
            $_ret .= '&amp;action=' . $_ModulAction;
        }

        if ( $_returnUri )
        {
            $_ret .= '&amp;' . $_returnUri;
        }

        return $_ret;
    }

}
