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
 * @file         Index.php
 */
class Dashboard_Action_Index extends Controller_Abstract
{

    public function execute()
    {

        if ( $this->isFrontend() )
        {
            return;
        }


        $data = array();

        /**
         * set the GUI language from input
         */
        if ( $this->input( 'setguilang' ) )
        {
            $_locale = HTTP::getClean( $this->input( 'setguilang' ), true );

            Session::save( 'guilang', $_locale );
            Cookie::set( 'guilang', $_locale );

            echo Library::json( array(
                'success' => true
            ) );
            exit;
        }

        if ( $this->_post( 'storeWindowPosition' ) )
        {
            $personal = new Personal;


            $url = $this->_post( 'url' );
            $url = preg_replace( '#^' . preg_quote( Settings::get( 'portalurl' ) ) . '#', '', $url );
            $url = preg_replace( '#^.*/?(admin\.php.*)$#', '$1', $url );
            $url = preg_replace( '#([&\?])ajax=[^&]*#', '', $url );
            $url = preg_replace( '#([&\?])_=\d+#', '', $url );
            $url = md5( $url );

            $personal->set( 'window', $url, array(
                'url'        => $this->_post( 'url' ),
                'windowID'   => $this->_post( 'windowID' ),
                'screensize' => $this->_post( 'screensize' ), // width | height
                'windowpos'  => $this->_post( 'windowpos' ), // left  | top
                'windowsize' => $this->_post( 'windowsize' ) // width | height
            ) );


            echo Library::json( array(
                'success' => true
            ) );
            exit;
        }


        if ( $this->input( 'getBasics' ) && !User::isLoggedIn() )
        {
            $data = $this->getBasicConfig();


            Ajax::Send( true, $data );
            exit;
        }

        if ( $this->input( 'getBasics' ) && User::isLoggedIn() )
        {
            $data  = $this->getBasicConfig();
            $extra = $this->_loadBackendData();
            $data  = array_merge( $data, $extra );
            Ajax::Send( true, $data );
            exit;
        }


        if ( $this->input( 'getModulInfo' ) && User::isLoggedIn() )
        {
            //print_r($this->getApplication()->getModulRegistry($this->input( 'getModulInfo' ))); exit;


            $name = strtolower( $this->input( 'getModulInfo' ) );

            if ( preg_replace( '#([^a-z0-9_]*)#i', '', $name ) != $name )
            {
                die( '' );
            }

            if ( (int)$this->input( 'isAddon' ) )
            {

                $this->load( 'Plugin' );
                $this->Plugin->initPlugin( Plugin::getConfig( $name ), $name );
                $def = $this->Plugin->getDefinition();

                if ( !is_array( $def ) )
                {
                    $def = array();
                }
            }
            else
            {

                $def = $this->getApplication()->getModulRegistry( $name );
            }


            if ( empty( $def[ 'definition' ][ 'license' ] ) || !isset( $def[ 'definition' ][ 'license' ] ) )
            {
                $def[ 'definition' ][ 'license' ] = 'Licensed under GPL v2';
            }

            if ( empty( $def[ 'definition' ][ 'version' ] ) || !isset( $def[ 'definition' ][ 'version' ] ) )
            {
                $def[ 'definition' ][ 'version' ] = '1.0';
            }

            if ( empty( $def[ 'definition' ][ 'copyright' ] ) || !isset( $def[ 'definition' ][ 'copyright' ] ) )
            {
                $def[ 'definition' ][ 'copyright' ] = '&copy;' . date( 'Y' ) . ' by Marcel Domke';
            }

            //  print_r($def[ 'definition' ]); echo ' < --- >'.$this->input( 'getModulInfo' ) ; exit;


            $data = array(
                'info' => $def[ 'definition' ]
            );

            if ( !(int)$this->input( 'isAddon' ) )
            {
                $data[ 'info' ][ 'bytesize' ] = ( is_dir( MODULES_PATH . ucfirst( $name ) ) ? Library::dirSize( MODULES_PATH . ucfirst( $name ) . '/' ) : 0 );
            }
            else
            {
                $data[ 'info' ][ 'bytesize' ] = ( is_dir( PLUGIN_PATH . ucfirst( $name ) ) ? Library::dirSize( PLUGIN_PATH . ucfirst( $name ) . '/' ) : 0 );
            }

            Ajax::Send( true, $data );
            exit;
        }


        if ( $this->input( 'saveDock' ) && User::isLoggedIn() )
        {
            // @see Controller_Abstract::updateDock()
            Ajax::Send( true );
            exit;
        }


        if ( $this->input( 'getLaunchPad' ) && User::isLoggedIn() )
        {
            /*




              // get a list of the menu items
              $result = $this->db->query('SELECT * FROM %tp%admin_nav WHERE inlaunchpad=1 AND published=1 AND plugin = 0 ORDER BY title')->fetchAll();

              // establish the hierarchy of the menu
              $children = array();





              // first pass - collect children
              foreach ($result as $v)
              {

              if ($v['parentid'] > 0)
              {


              if (!trim((string) $v['url']))
              {
              continue;
              }


              $uc = preg_replace('/adm=([^&]*)/is', '$1', $v['url']);

              $controller = ($v['controller'] ? $v['controller'] : ($uc ? $uc : null));
              if ($controller)
              {
              $ua     = preg_replace('/action=([^&]*)/is', '$1', $v['url']);
              $action = ($v['action'] ? $v['action'] : ($ua ? $ua : 'index') );

              if ($action != 'index')
              {
              #  continue;
              }


              $str = $controller . '/' . $action;

              $_modulRequireLogin = Action::requireLogin($str);
              $_modulRequirePerms = Action::requirePermission($str);



              if ($_modulRequireLogin || $_modulRequirePerms)
              {
              if ($_modulRequireLogin === true && !User::isLoggedIn())
              {
              continue;
              }

              if ($_modulRequirePerms === true && !Permission::hasControllerActionPerm($str))
              {
              continue;
              }
              }

              $url      = str_replace('{php_ext}', 'php', $v['url']);
              $doc_path = $_SERVER['DOCUMENT_ROOT'] ? $_SERVER['DOCUMENT_ROOT'] : getenv('DOCUMENT_ROOT');

              $real_path = str_replace($doc_path, '', $root_path);
              $real_path = preg_replace('!/$!', '', $real_path);
              $url       = str_replace('<#REAL_DIR#>', $real_path, $url);

              if (strpos($url, 'admin.php?adm=') !== false || stripos($url, 'http://') !== false)
              {

              }
              else
              {
              $url      = str_replace('?', '&', $url);
              $url      = str_replace('&amp;', '&', $url);
              $url      = str_replace('.php', '', $url);
              $v['url'] = 'admin.php?adm=' . $url;
              }


              $def        = $this->getApplication()->getModulRegistry($controller);
              $v['title'] = $def['definition']['modulelabel'] ? $def['definition']['modulelabel'] : $v['title'];

              $children[] = $v;
              }
              }
              }



             */

            $data    = array();
            $reg     = $this->getApplication()->getModulRegistry();
            $modules = $this->getApplication()->loadFrontendModules();

            $plugins = false;

            foreach ( $reg as $modulname => $r )
            {

                if ( strtolower( $modulname ) === 'plugin' )
                {
                    $plugins = true;
                }


                if ( !$plugins && isset( $modules[ ucfirst( $modulname ) ] ) && !$modules[ ucfirst( $modulname ) ][ 'published' ] )
                {
                    continue;
                }


                if ( isset( $r[ 'definition' ][ 'dockurl' ] ) && !empty( $r[ 'definition' ][ 'dockurl' ] ) )
                {
                    $url = $r[ 'definition' ][ 'dockurl' ];

                    preg_match( '/adm=([a-z0-9_]*)/is', $url, $match );

                    $controller = $match[ 1 ];
                    if ( !$controller )
                    {
                        continue;
                    }
                    $match = null;


                    preg_match( '/action=([a-z0-9_]+)/is', $url, $match );
                    if ( empty( $match[ 1 ] ) )
                    {
                        $r[ 'definition' ][ 'dockurl' ] .= '&action=index';
                    }

                    $action = !empty( $match[ 1 ] ) ? $match[ 1 ] : 'index';
                    $str    = $controller . '/' . $action;


                    $_modulRequireLogin = Action::requireLogin( $str );
                    $_modulRequirePerms = Action::requirePermission( $str );


                    if ( $_modulRequireLogin || $_modulRequirePerms )
                    {
                        if ( $_modulRequireLogin === true && !User::isLoggedIn() )
                        {
                            continue;
                        }

                        if ( $_modulRequirePerms === true && !Permission::hasControllerActionPerm( $str ) )
                        {
                            continue;
                        }
                    }

                    if ( strtolower( $controller ) === 'plugin' )
                    {
                        $plugins = true;
                    }

                    $data[ ] = array(
                        'controller' => $controller,
                        'action'     => ( $ua ? $ua : 'index' ),
                        'title'      => $r[ 'definition' ][ 'modulelabel' ] ? $r[ 'definition' ][ 'modulelabel' ] : 'Unnamed Modul',
                        'url'        => $r[ 'definition' ][ 'dockurl' ]
                    );
                }
            }


            if ( $plugins )
            {
                $model   = Model::getModelInstance( 'plugin' );
                $plugins = $model->getPlugins();

                foreach ( $plugins as $plug )
                {
                    if ( $plug[ 'published' ] && $plug[ 'run' ] )
                    {
                        $data[ ] = array(
                            'controller' => 'plugin',
                            'action'     => 'run',
                            'title'      => $plug[ 'name' ],
                            'url'        => 'admin.php?adm=plugin&action=run&plugin=' . $plug[ 'key' ],
                            'isplugin'   => true,
                            'pluginkey'  => ucfirst( strtolower( $plug[ 'key' ] ) ),
                            'icon'       => $plug[ 'key' ]
                        );
                    }
                }
            }


            $data = array(
                'modules' => $data
            );
            Ajax::Send( true, $data );
            exit;
        }


        if ( !User::isLoggedIn() )
        {
            //   $data[ 'sites' ]      = $this->getRootPages();
            $data[ 'islogin' ]    = true;
            $data[ 'bootImages' ] = $this->getBootImages();
            $this->Template->process( 'auth/login', $data, true );
            exit;
        }


        if ( $this->input( 'translate' ) )
        {
            $translate = HTTP::getClean( $this->input( 'translate' ), true );

            /* Change the Content language only */
            $locale = Locales::getShortLocaleFromCode( $translate );
            Session::save( 'trans_code', $locale );
            Cookie::set( 'trans_code', $locale );

            echo Library::json( array(
                'success' => true
            ) );
            exit;
        }
        /*
                if ( HTTP::input('getBasics') )
                {

                    $config = Settings::getAll();

                    unset($config[ 'smtp_server' ], $config[ 'smtp_port' ], $config[ 'smtp_port' ], $config[ 'smtp_user' ], $config[ 'smtp_password' ], $config[ 'cli_key' ], $config[ 'frommail' ], $config[ 'disclaimer_text' ]);


                    $userdata = User::getUserData();

                    unset($userdata[ 'password' ], $userdata[ 'uniqidkey' ], $udata[ 'usertext' ], $udata[ 'signature' ], $udata[ 'permissions' ], $udata[ 'specialperms' ]);


                    $default = array (
                        'dockposition' => 'center',
                        'dockautohide' => false,
                        'mintoappicon' => false,
                        'dockHeight'   => 40,
                        'activeItems'  => array (),
                        'dockItems'    => array ()
                    );


                    $personal         = new Personal;
                    $personalsettings = User::getPersonalSettings();
                    $personaldata     = $personal->get('dock', 'settings', $default);


                    if ( BACKEND_SKIN_ISWINDOWED )
                    {
                        $personaldataIcs     = $personal->get('desktop', 'icons', array ());
                        $personaldataFolders = $personal->get('desktop', 'folders', array ());
                    }
                    if ( !$personalsettings [ 'desktopbackground' ] || !isset($personalsettings [ 'desktopbackground' ]) )
                    {
                        $personalsettings [ 'desktopbackground' ] = 'galaxy.jpg';
                    }

                    $config[ 'post_max_size' ]       = ini_get('post_max_size');
                    $config[ 'upload_max_filesize' ] = ini_get('upload_max_filesize');
                    $config[ 'max_file_uploads' ]    = ini_get('max_file_uploads');

                    $data = array (
                        'config'           => $config,
                        'userdata'         => $udata,
                        'personalsettings' => $personalsettings,
                        'dock'             => $personaldata,
                        'desktopicons'     => $personaldataIcs,
                        'desktopfolders'   => $personaldataFolders
                    );

                    $data[ 'sid' ]        = session_id();
                    $data[ 'bootImages' ] = $this->getBootImages();

                    Ajax::Send(true, $data);
                    exit;
                }
        */

        if ( $this->input( 'tinymce' ) === 'getconfig' )
        {
            $data = $this->getTinyMCEConfig();
            Ajax::Send( true, $data );
            exit;
        }


        if ( $this->input( 'getcontenttrans' ) )
        {
            $langs = $this->db->query( 'SELECT title, flag, id, `code` FROM %tp%locale WHERE contentlanguage = 1 ORDER BY title' )->fetchAll();

            echo Library::json( array(
                'success'      => true,
                'contentlangs' => $langs
            ) );
            exit;
        }


        if ( $this->input( 'setcontenttranslation' ) > 0 )
        {
            $translate = Locales::getLocaleById( $this->input( 'setcontenttranslation' ) );
            $trans     = $translate[ 'lang' ];
            Session::save( 'trans_code', $trans );
            Session::write();

            echo Library::json( array(
                'success' => true
            ) );
            exit;
        }


        if ( $this->input( 'getpagetypes' ) )
        {

            $a = Application::getInstance();
            echo Library::json( array(
                'success' => true, //  'types' => $types,
                'apps'    => $a->getApps(),
            ) );
            exit;
        }


        /**
         * Alias builder form
         *
         */
        if ( $this->_post( 'getAliasBuilder' ) )
        {
            $data                  = array();
            $data[ 'controller' ]  = $this->_post( 'modul' );
            $data[ 'action' ]      = $this->_post( 'modulaction' );
            $data[ 'page_suffix' ] = '';

            $html = $this->Template->process( 'pageidentifier/ajax_rebuild', $data );

            echo Library::json( array(
                'success' => true,
                'html'    => $html
            ) );
            exit;
        }


        /**
         * Save the Desktop Icons
         *
         */
        if ( $this->_post( 'storeDesktopIcons' ) )
        {
            $folders  = $this->_post( 'desktopFolders' );
            $allIcons = $this->_post( 'desktopIcons' );

            $iconWidth      = $this->_post( 'iconWidth' );
            $subIconWidth   = $this->_post( 'subIconWidth' );
            $iconLabelPos   = $this->_post( 'iconLabelPos' );
            $iconGutterSize = $this->_post( 'iconGutterSize' );
            $iconSort       = $this->_post( 'iconSort' );
            $showObjectInfo = $this->_post( 'showObjectInfo' );

            $default  = array(
                'showObjectInfo' => $showObjectInfo ? true : false,
                'iconGutterSize' => ( $iconGutterSize ? $iconGutterSize : 10 ),
                'iconLabelPos'   => ( $iconLabelPos ? $iconLabelPos : 'bottom' ),
                'iconWidth'      => $iconWidth,
                'subIconWidth'   => $subIconWidth,
                'iconSort'       => ( $iconSort ? $iconSort : 'none' )
            );
            $personal = new Personal;

            if ( is_array( $allIcons ) )
            {
                $url = Settings::get( 'portalurl' ) . '/';
                $ics = array();
                foreach ( $allIcons as $icon )
                {
                    foreach ( $icon as $k => $v )
                    {
                        if ( $v == "true" )
                        {
                            $icon[ $k ] = true;
                        }
                        elseif ( $v == "false" )
                        {
                            $icon[ $k ] = false;
                        }
                    }


                    unset( $icon[ 'WindowURL' ], $icon[ 'WindowTitle' ] );

                    $icon[ 'url' ] = str_replace( $url, '', $icon[ 'url' ] );

                    $ics[ $icon[ 'WindowID' ] ] = $icon;
                }

                $_ics = array_merge( $default, $ics );
                $personal->set( 'desktop', 'icons', $_ics );
            }
            else
            {
                $personal->set( 'desktop', 'icons', $default );
            }


            if ( is_array( $folders ) )
            {
                foreach ( $folders as &$f )
                {
                    foreach ( $f[ 'items' ] as &$item )
                    {
                        $item = str_replace( 'DesktopIcon', '', $item );
                    }
                }

                $_folders = array_merge( array(), $folders );
                $personal->set( 'desktop', 'folders', $_folders );
            }
            else
            {
                $personal->set( 'desktop', 'folders', array() );
            }


            echo Library::json( array(
                'success' => true
            ) );
            exit;
        }


        $data[ 'version_info' ]       = Session::get( 'version_check_output' );
        $data[ 'version_check_done' ] = Session::get( 'version_check_done', false );

        $extra = $this->_loadBackendData();
        $data  = array_merge( $data, $extra );

        $this->Template->process( 'generic/index', $data, true );

        exit;
    }

    protected function _loadBackendData()
    {

        $ct          = new Dashboard_Action_Contenttree();
        $treeContent = $ct->getTreeData();

        $wgt     = new Widgets_Helper_Base();
        $widgets = $wgt->setWidgetSession();


        $config  = $this->getBasicConfig();
        $tinymce = $this->getTinyMCEConfig();


        $data = array(
            'modules' => $treeContent,
            'widgets' => $widgets,
            'tinymce' => $tinymce[ 'tinymce' ]
        );

        $res = Model::getModelInstance( 'logs' )->getLogs( 40, true );
        foreach ( $res[ 'result' ] as &$r )
        {
            $r[ 'time' ] = Locales::formatDateTime( $r[ 'time' ] );
        }

        $data[ 'logs' ] = $res[ 'result' ];

        $data = array_merge( $data, $config );

        return $data;
    }

    /**
     * @return array
     */
    private function buildIndex()
    {

        $r                       = array();
        $r[ 'new_members' ]      = $this->get_new_members();
        $r[ 'active_members' ]   = $this->get_total_members();
        $r[ 'disabled_members' ] = $this->get_disabled_members();
        $r[ 'read_sql_errors' ]  = $this->read_sql_errors();

        $df1 = disk_total_space( ROOT_PATH );
        $df2 = disk_total_space( PAGE_PATH . "mediacenter/" );

        $r[ 'mediacenter_disk_usage' ] = Library::humanSize( $df2 );
        #$r['skincache_disk_usage'] = Library::humanSize(disk_total_space(SKIN_PATH));


        if ( is_dir( ROOT_PATH . "pages/" . SERVER_PAGE . "/static_pages/" ) )
        {
            $r[ 'staticpage_disk_usage' ] = Library::humanSize( disk_total_space( PAGE_PATH . "static_pages/" ) );
        }

        $r[ 'cache_disk_usage' ] = Library::humanSize( disk_total_space( PAGE_PATH . ".cache/" ) );
        $r[ '_disk_usage' ]      = Library::humanSize( $df1 );
        $r[ 'active_sections' ]  = $this->get_active_sections();

        $undef            = trans( 'Unbekannt' );
        $r[ 'maxupload' ] = str_replace( array(
            'M',
            'm'
        ), 'MB', @ini_get( 'upload_max_filesize' ) );


        $dbVersion            = Database::getDatabaseInfo();
        $r[ 'mysqlversionc' ] = $dbVersion[ 'version' ];

        $r[ 'magicquotes' ]          = ( @ini_get( 'magic_quotes_gpc' ) == 1 ) ? 'An' : 'Aus';
        $r[ 'maxmemory' ]            = ( @ini_get( 'memory_limit' ) != '' ) ? @ini_get( 'memory_limit' ) : $undef;
        $r[ 'maxupload' ]            = Library::humanSize( $r[ 'maxupload' ] * 1024 );
        $r[ 'phpversion' ]           = ( @PHP_VERSION != "" ) ? @PHP_VERSION : $undef;
        $r[ 'disabledfunctions' ]    = ( strlen( ini_get( 'disable_functions' ) ) > 1 ) ? @ini_get( 'disable_functions' ) : $undef;
        $r[ 'safemode' ]             = ( @ini_get( 'safe_mode' ) == 1 ) ? 'An' : 'Aus';
        $r[ 'mysqlversion' ]         = ( $r[ 'mysqlversionc' ] != "" ) ? $r[ 'mysqlversionc' ] : $undef;
        $r[ 'max_execution_time' ]   = @ini_get( 'max_execution_time' );
        $r[ 'magic_quotes_runtime' ] = ( @ini_get( 'magic_quotes_runtime' ) == 1 ) ? 'An' : 'Aus';
        $r[ 'magic_quotes_sybase' ]  = ( @ini_get( 'magic_quotes_sybase' ) == 1 ) ? 'An' : 'Aus';
        $r[ 'apache_modules' ]       = ( function_exists( 'apache_get_modules' ) ) ? apache_get_modules() : 'Function not available on this Server!';

        return $r;
    }

    public function getSplashscreenInfos()
    {
        $splashData = array();

        $reg = $this->getApplication()->getModulRegistry();
        foreach ( $reg as $modul => $r )
        {
            if ( isset( $r[ 'definition' ] ) )
            {
                $o             = $r[ 'definition' ];
                $splashData[ $modul ] = array('modulelabel' => $o[ 'modulelabel' ], 'version' => $o[ 'version' ], 'copyright' => $o[ 'copyright' ]);
            }
        }

        $plugins = Plugin::getInstalledPlugins();
        foreach ( $plugins as $r )
        {
            if ( $r[ 'run' ] && $r[ 'published' ] )
            {
                $def = Plugin::getPluginDefinition( $r[ 'key' ] );
                if ( is_array( $def ) && isset( $def[ 'modulelabel' ] ) )
                {
                    $splashData[ $r[ 'key' ] ] = array(
                        'plugin'      => true,
                        'modulelabel' => $def[ 'modulelabel' ],
                        'version'     => isset( $def[ 'version' ] ) ? $def[ 'version' ] : '1.0',
                        'copyright'   => isset( $def[ 'author' ] ) ? $def[ 'author' ] : null
                    );
                }
            }
        }

        return $splashData;
    }

    public function getTinyMCEConfig()
    {
        list( $plugins, $toolbar_output, $_toolbars ) = Tinymce::getTinyMceToolbars();

        return array(
            'tinymce' => array_merge( array(
                'plugins'     => $plugins,
                'language'    => CONTENT_TRANS,
                'content_css' => Tinymce::getContentCss(),
                'templates'   => Tinymce::getContentTemplates(),
            ), $_toolbars )
        );
    }


    public function getBasicConfig()
    {

        $config = Settings::getAll();
        unset( $config[ 'smtp_server' ], $config[ 'smtp_port' ], $config[ 'smtp_port' ], $config[ 'smtp_user' ], $config[ 'smtp_password' ], $config[ 'cli_key' ], $config[ 'frommail' ], $config[ 'disclaimer_text' ], $config[ 'crypt_key' ] );
        $_conf                              = Locales::getLocaleByLang( CONTENT_TRANS );
        $config[ 'backend_css_url' ]        = BACKEND_CSS_PATH;
        $config[ 'backendImagePath' ]       = BACKEND_IMAGE_PATH;
        $config[ 'js_url' ]                 = JS_URL;
        $config[ 'css_url' ]                = BACKEND_CSS_PATH;
        $config[ 'IS_AJAX' ]                = IS_AJAX;
        $config[ 'is_ajax' ]                = IS_AJAX;
        $config[ 'version' ]                = VERSION;
        $config[ 'cookieprefix' ]           = COOKIE_PREFIX;
        $config[ 'app_path' ]               = APP_PATH;
        $config[ 'data_path' ]              = DATA_PATH;
        $config[ 'html_path' ]              = HTML_URL;
        $config[ 'contenttranslationFlag' ] = $_conf[ 'flag' ];
        $config[ 'post_max_size' ]          = ini_get( 'post_max_size' );
        $config[ 'upload_max_filesize' ]    = ini_get( 'upload_max_filesize' );
        $config[ 'max_file_uploads' ]       = ini_get( 'max_file_uploads' );


        if ( !User::isLoggedIn() )
        {
            $data = array(
                'sysconfig' => $config
            );

        }
        else
        {
            $data = array(
                'sysconfig' => $config
            );

            $_conf                              = Locales::getLocaleByLang( CONTENT_TRANS );
            $config[ 'contenttranslationFlag' ] = $_conf[ 'flag' ];

            $udata = User::getUserData();

            unset( $udata[ 'password' ], $udata[ 'uhash' ], $udata[ 'uniqidkey' ], $udata[ 'usertext' ], $udata[ 'signature' ], $udata[ 'permissions' ], $udata[ 'specialperms' ] );


            if ( BACKEND_SKIN_ISWINDOWED )
            {
                $default = array(
                    'dockposition' => 'center',
                    'dockautohide' => false,
                    'mintoappicon' => false,
                    'dockHeight'   => 40,
                    'activeItems'  => array(),
                    'dockItems'    => array()
                );

                $personal         = new Personal;
                $personalsettings = User::getPersonalSettings();

                if ( !$personalsettings[ 'desktopbackground' ] || !isset( $personalsettings[ 'desktopbackground' ] ) )
                {
                    $personalsettings[ 'desktopbackground' ] = 'galaxy.jpg';
                }


                $personaldata        = $personal->get( 'dock', 'settings', $default );
                $personaldataIcs     = $personal->get( 'desktop', 'icons', array() );
                $personaldataFolders = $personal->get( 'desktop', 'folders', array() );

                foreach ( $personaldataIcs as $k => &$r )
                {
                    if ( !in_array( $k, array(
                        'iconGutterSize',
                        'iconLabelPos',
                        'iconWidth',
                        'subIconWidth',
                        'iconSort',
                        'showObjectInfo'
                    ) )
                    )
                    {
                        if ( $r[ 'controller' ] )
                        {


                            if ( $r[ 'controller' ] != 'plugin' && !preg_match( '#plugin=([a-z0-9]+)#i', $r[ 'url' ] ) )
                            {
                                $reg = $this->getApplication()->getModulRegistry( $r[ 'controller' ] );

                                if ( isset( $reg[ 'definition' ] ) )
                                {
                                    if ( isset( $reg[ 'definition' ][ 'version' ] ) )
                                    {
                                        $r[ 'version' ] = $reg[ 'definition' ][ 'version' ];
                                    }
                                    if ( isset( $reg[ 'definition' ][ 'moduledescription' ] ) && $reg[ 'definition' ][ 'moduledescription' ] )
                                    {
                                        $r[ 'moduledescription' ] = $reg[ 'definition' ][ 'moduledescription' ];
                                    }


                                    $r[ 'bytesize' ] = Library::dirSize( MODULES_PATH . ucfirst( $r[ 'controller' ] ) . '/' );
                                    $r[ 'size' ]     = Library::formatSize( $r[ 'bytesize' ] );
                                }
                            }
                            else
                            {
                                if ( $r[ 'url' ] )
                                {
                                    $n = array();
                                    preg_match( '#plugin=([a-z0-9]+)#i', $r[ 'url' ], $n );

                                    if ( $n[ 1 ] )
                                    {
                                        $this->load( 'Plugin' );
                                        $this->Plugin->initPlugin( Plugin::getConfig( $n[ 1 ] ), $n[ 1 ] );
                                        $reg = $this->Plugin->getDefinition();

                                        if ( is_array( $reg ) )
                                        {
                                            if ( isset( $reg[ 'definition' ] ) )
                                            {
                                                if ( isset( $reg[ 'definition' ][ 'version' ] ) )
                                                {
                                                    $r[ 'version' ] = $reg[ 'definition' ][ 'version' ];
                                                }
                                                if ( isset( $reg[ 'definition' ][ 'moduledescription' ] ) && $reg[ 'definition' ][ 'moduledescription' ] )
                                                {
                                                    $r[ 'moduledescription' ] = $reg[ 'definition' ][ 'moduledescription' ];
                                                }

                                                $r[ 'bytesize' ] = Library::dirSize( MODULES_PATH . ucfirst( $r[ 'controller' ] ) . '/' );
                                                $r[ 'size' ]     = Library::formatSize( $r[ 'bytesize' ] );
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            list( $plugins, $toolbar_output, $_toolbars ) = Tinymce::getTinyMceToolbars();

            $data = array(
                'sysconfig'        => $config,
                'userdata'         => $udata,
                'personalsettings' => $personalsettings,
                'dock'             => $personaldata,
                'desktopicons'     => $personaldataIcs,
                'desktopfolders'   => $personaldataFolders,
                'tinymce'          => array_merge( array(
                    'plugins'     => $plugins,
                    'language'    => CONTENT_TRANS,
                    'content_css' => Tinymce::getContentCss(),
                    'templates'   => Tinymce::getContentTemplates(),
                ), $_toolbars )
            );
        }


        $data[ 'sid' ]         = session_id();
        $data[ 'bootImages' ]  = array(); //$this->getBootImages();
        $data[ 'splashInfos' ] = $this->getSplashscreenInfos();

        return $data;

    }


    /**
     *
     * @return array $imageList
     */
    private function getBootImages()
    {

        if ( BACKEND_SKIN_ISWINDOWED )
        {
            $imageList = array();
            $images    = Library::getFiles( BACKEND_IMAGE_PATH . 'Apple/', true );
            foreach ( $images as $f )
            {
                if ( $f[ 'filename' ] != ".DS_Store" )
                {
                    $imagesInfo = pathinfo( $f[ 'path' ] . $f[ 'filename' ] );
                    if ( isset( $imagesInfo[ 'extension' ] ) )
                    {
                        if ( $imagesInfo[ 'extension' ] == 'png' || $imagesInfo[ 'extension' ] == 'jpeg' || $imagesInfo[ 'extension' ] == 'gif' || $imagesInfo[ 'extension' ] == 'jpg' )
                        {
                            $imageList[ ][ 'src' ] = str_replace( PUBLIC_PATH, '', $f[ 'path' ] ) . $f[ 'filename' ] . '|Core UI';
                        }
                    }
                }
            }

            $images = Library::getFiles( BACKEND_IMAGE_PATH . 'DesktopBalloon/', true );
            foreach ( $images as $f )
            {
                if ( $f[ 'filename' ] != ".DS_Store" )
                {
                    $imagesInfo = pathinfo( $f[ 'path' ] . $f[ 'filename' ] );
                    if ( isset( $imagesInfo[ 'extension' ] ) )
                    {
                        if ( $imagesInfo[ 'extension' ] == 'png' || $imagesInfo[ 'extension' ] == 'jpeg' || $imagesInfo[ 'extension' ] == 'gif' || $imagesInfo[ 'extension' ] == 'jpg' )
                        {
                            $imageList[ ][ 'src' ] = str_replace( PUBLIC_PATH, '', $f[ 'path' ] ) . $f[ 'filename' ] . '|Core UI';
                        }
                    }
                }
            }

            $images = Library::getFiles( BACKEND_IMAGE_PATH . 'DesktopIconContainer/', true );
            foreach ( $images as $f )
            {
                if ( $f[ 'filename' ] != ".DS_Store" )
                {
                    $imagesInfo = pathinfo( $f[ 'path' ] . $f[ 'filename' ] );
                    if ( isset( $imagesInfo[ 'extension' ] ) )
                    {
                        if ( $imagesInfo[ 'extension' ] == 'png' || $imagesInfo[ 'extension' ] == 'jpeg' || $imagesInfo[ 'extension' ] == 'gif' || $imagesInfo[ 'extension' ] == 'jpg' )
                        {
                            $imageList[ ][ 'src' ] = str_replace( PUBLIC_PATH, '', $f[ 'path' ] ) . $f[ 'filename' ] . '|Core UI';
                        }
                    }
                }
            }

        }


        $images = Library::getFiles( BACKEND_IMAGE_PATH . 'grid/', true );
        foreach ( $images as $f )
        {
            if ( $f[ 'filename' ] != ".DS_Store" )
            {
                $imagesInfo = pathinfo( $f[ 'path' ] . $f[ 'filename' ] );
                if ( isset( $imagesInfo[ 'extension' ] ) )
                {
                    if ( $imagesInfo[ 'extension' ] == 'png' || $imagesInfo[ 'extension' ] == 'jpeg' || $imagesInfo[ 'extension' ] == 'gif' || $imagesInfo[ 'extension' ] == 'jpg' )
                    {
                        $imageList[ ][ 'src' ] = str_replace( PUBLIC_PATH, '', $f[ 'path' ] ) . $f[ 'filename' ] . '|Core UI';
                    }
                }
            }
        }

        $images = Library::getFiles( BACKEND_IMAGE_PATH . 'layouter/', true );
        foreach ( $images as $f )
        {
            if ( $f[ 'filename' ] != ".DS_Store" )
            {
                $imagesInfo = pathinfo( $f[ 'path' ] . $f[ 'filename' ] );
                if ( isset( $imagesInfo[ 'extension' ] ) )
                {
                    if ( $imagesInfo[ 'extension' ] == 'png' || $imagesInfo[ 'extension' ] == 'jpeg' || $imagesInfo[ 'extension' ] == 'gif' || $imagesInfo[ 'extension' ] == 'jpg' )
                    {
                        $imageList[ ][ 'src' ] = str_replace( PUBLIC_PATH, '', $f[ 'path' ] ) . $f[ 'filename' ] . '|Core UI';
                    }
                }
            }
        }

        $images = Library::getFiles( BACKEND_IMAGE_PATH . 'credits/', true );
        foreach ( $images as $f )
        {
            if ( $f[ 'filename' ] != ".DS_Store" )
            {
                $imagesInfo = pathinfo( $f[ 'path' ] . $f[ 'filename' ] );
                if ( isset( $imagesInfo[ 'extension' ] ) )
                {
                    if ( $imagesInfo[ 'extension' ] == 'png' || $imagesInfo[ 'extension' ] == 'jpeg' || $imagesInfo[ 'extension' ] == 'gif' || $imagesInfo[ 'extension' ] == 'jpg' )
                    {
                        $imageList[ ][ 'src' ] = str_replace( PUBLIC_PATH, '', $f[ 'path' ] ) . $f[ 'filename' ] . '|Core UI';
                    }
                }
            }
        }

        $images = Library::getFiles( BACKEND_IMAGE_PATH . 'buttons/', true );
        foreach ( $images as $f )
        {
            if ( $f[ 'filename' ] != ".DS_Store" )
            {
                $imagesInfo = pathinfo( $f[ 'path' ] . $f[ 'filename' ] );
                if ( isset( $imagesInfo[ 'extension' ] ) )
                {
                    if ( $imagesInfo[ 'extension' ] == 'png' || $imagesInfo[ 'extension' ] == 'jpeg' || $imagesInfo[ 'extension' ] == 'gif' || $imagesInfo[ 'extension' ] == 'jpg' )
                    {
                        $imageList[ ][ 'src' ] = str_replace( PUBLIC_PATH, '', $f[ 'path' ] ) . $f[ 'filename' ] . '|Core UI';
                    }
                }
            }
        }

        $images = Library::getFiles( BACKEND_IMAGE_PATH, false );
        foreach ( $images as $f )
        {
            if ( $f[ 'filename' ] != ".DS_Store" )
            {
                $imagesInfo = pathinfo( $f[ 'path' ] . $f[ 'filename' ] );
                if ( isset( $imagesInfo[ 'extension' ] ) )
                {
                    if ( $imagesInfo[ 'extension' ] == 'png' || $imagesInfo[ 'extension' ] == 'jpeg' || $imagesInfo[ 'extension' ] == 'gif' || $imagesInfo[ 'extension' ] == 'jpg' )
                    {
                        $imageList[ ][ 'src' ] = str_replace( PUBLIC_PATH, '', $f[ 'path' ] ) . $f[ 'filename' ] . '|Core UI';
                    }
                }
            }
        }


        if ( BACKEND_SKIN_ISWINDOWED )
        {
            $images = Library::getFiles( BACKEND_IMAGE_PATH . 'desktop-backgrounds/', true );
            foreach ( $images as $f )
            {
                if ( $f[ 'filename' ] != ".DS_Store" )
                {
                    $imagesInfo = pathinfo( $f[ 'path' ] . $f[ 'filename' ] );
                    if ( isset( $imagesInfo[ 'extension' ] ) )
                    {
                        if ( $imagesInfo[ 'extension' ] == 'png' || $imagesInfo[ 'extension' ] == 'jpeg' || $imagesInfo[ 'extension' ] == 'gif' || $imagesInfo[ 'extension' ] == 'jpg' )
                        {
                            $imageList[ ][ 'src' ] = str_replace( PUBLIC_PATH, '', $f[ 'path' ] ) . $f[ 'filename' ] . '|Desktop Backgrounds';
                        }
                    }
                }
            }
        }

        $images = Library::getFiles( MODULES_PATH, true );

        foreach ( $images as $f )
        {
            if ( $f[ 'filename' ] != ".DS_Store" )
            {
                $imagesInfo = pathinfo( $f[ 'path' ] . $f[ 'filename' ] );
                if ( isset( $imagesInfo[ 'extension' ] ) )
                {
                    if ( $imagesInfo[ 'extension' ] == 'png' || $imagesInfo[ 'extension' ] == 'jpeg' || $imagesInfo[ 'extension' ] == 'gif' || $imagesInfo[ 'extension' ] == 'jpg' )
                    {
                        $imageList[ ][ 'src' ] = str_replace( ROOT_PATH, '', $f[ 'path' ] ) . $f[ 'filename' ] . '|Launchpad';
                    }
                }
            }
        }

        $images = Library::getFiles( VENDOR_PATH . 'tinymce/', true );

        foreach ( $images as $f )
        {
            if ( $f[ 'filename' ] != ".DS_Store" )
            {
                if ( strpos( $f[ 'path' ], 'tinymce/pdw_' ) !== false || strpos( $f[ 'path' ], 'tinymce/templates' ) !== false )
                {
                    continue;
                }


                $imagesInfo = pathinfo( $f[ 'path' ] . $f[ 'filename' ] );
                if ( isset( $imagesInfo[ 'extension' ] ) )
                {
                    if ( $imagesInfo[ 'extension' ] == 'png' || $imagesInfo[ 'extension' ] == 'jpeg' || $imagesInfo[ 'extension' ] == 'gif' || $imagesInfo[ 'extension' ] == 'jpg' )
                    {
                        $imageList[ ][ 'src' ] = str_replace( ROOT_PATH, '', $f[ 'path' ] ) . $f[ 'filename' ] . '|TinyMCE Files';
                    }
                }
            }
        }


        return $imageList;
    }

}

?>