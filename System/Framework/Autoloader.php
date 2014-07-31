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
 * @copyright    2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Autoloader.php
 *
 */
class Autoloader
{

    /**
     * @var Autoloader
     */
    protected static $_instance;

    /**
     * The list of available libraries.
     * @var array
     */
    private $_libraries = array();

    /**
     * The list of available modul libraries.
     * @var array
     */
    private $_modulLibraries = array();

    /**
     * The library extensions.
     * @var array
     */
    private $_extensions = array(
        '' => '.php');

    /**
     * The namespace separator
     * @var string
     */
    private $_namespaceSeparator = '\\';

    /**
     * Speedup the Loader
     * @var bool
     */
    private $_useFileCheck = true;

    private $_useAutoloadDebug = false;

    private $_loaded = array();

    private static $_loadPathCache = array();

    /**
     * Constructs the autoloader.
     *
     * @param string $namespaceSeparator The namespace separator used in this autoloader.
     * @param string $defaultPath The default library path.
     */
    public function __construct($namespaceSeparator = '\\', $defaultPath = './')
    {
        if ( ( self::$_instance instanceof Autoloader ) )
        {
            return self::$_instance;
        }


        $this->_namespaceSeparator = $namespaceSeparator;

        if ( $defaultPath[ strlen( $defaultPath ) - 1 ] != '/' )
        {
            $defaultPath .= '/';
        }
        $this->_defaultPath = $defaultPath;

        self::$_instance = $this;

        return self::$_instance;
    }


    public function __clone()
    {

    }

    public function __destruct()
    {

    }

    /**
     * @param string $namespaceSeparator
     * @param string $defaultPath
     * @return Autoloader
     */
    public static function getInstance($namespaceSeparator = '\\', $defaultPath = './')
    {
        if ( ( self::$_instance instanceof Autoloader ) )
        {
            return self::$_instance;
        }
        else
        {
            if ( DEBUG )
            {
                $_startTime = Debug::getMicroTime();
            }

            self::$_instance = new Autoloader( $namespaceSeparator, $defaultPath );

            if ( DEBUG )
            {
                Debug::store( '`Autoloader`', 'End Load... ' . str_replace( ROOT_PATH, '', Library::formatPath( __FILE__ ) ) . ' @Line: ' . ( __LINE__ - 4 ), $_startTime );
            }

            return self::$_instance;
        }
    }

    /**
     *
     * @return Autoloader
     */
    public function disableAutoloadDebug()
    {
        $this->_useAutoloadDebug = false;

        return ( self::$_instance );
    }

    /**
     *
     * @return Autoloader
     */
    public function enableAutoloadDebug()
    {
        $this->_useAutoloadDebug = true;

        return ( self::$_instance );
    }

    /**
     *
     * @return Autoloader
     */
    public function disableFileCheck()
    {
        $this->_useFileCheck = false;

        return ( self::$_instance );
    }

    /**
     *
     * @return Autoloader
     */
    public function enableFileCheck()
    {
        $this->_useFileCheck = true;

        return ( self::$_instance );
    }

    /**
     * @return array
     */
    public function getLibrarys()
    {
        return array_merge( $this->_libraries, $this->_modulLibraries );
    }

    /**
     * Registers a new modul library to match.
     *
     * @param string $module The modul name to add. (ucfirst)
     * @param string $library The library name to add.
     * @param string $path The path to the library.
     * @param string $extension The library file extension.
     * @return Autoloader
     */
    public function addModulLibrary($module, $library, $path = null, $extension = '.php')
    {
        if ( isset( $this->_modulLibraries[ (string)$module ][ (string)$library ] ) )
        {
            return $this;
        }

        if ( $path !== null )
        {
            if ( $path[ strlen( $path ) - 1 ] != '/' )
            {
                $path .= '/';
            }

            $this->_modulLibraries[ (string)$module ][ (string)$library ] = $path;
        }
        else
        {
            $this->_modulLibraries[ (string)$module ][ (string)$library ] = $this->_defaultPath;
        }

        $this->_extensions[ (string)$library ] = $extension;

        return $this;
    }

    /**
     * Registers a new library to match.
     *
     * @param string $library The library name to add.
     * @param string $path The path to the library.
     * @param string $extension The library file extension.
     * @return Autoloader
     * @throws BaseException
     */
    public function addLibrary($library, $path = null, $extension = '.php')
    {
        if ( isset( $this->_libraries[ (string)$library ] ) )
        {
            throw new BaseException( 'Library ' . $library . ' is already exists.', 'PHP' );
        }

        if ( $path !== null )
        {
            if ( $path[ strlen( $path ) - 1 ] != '/' )
            {
                $path .= '/';
            }
            $this->_libraries[ (string)$library ] = $path;
        }
        else
        {
            $this->_libraries[ (string)$library ] = $this->_defaultPath;
        }

        $this->_extensions[ (string)$library ] = $extension;

        return $this;
    }

    /**
     * Checks if the specified library is available.
     *
     * @param string $module The modul name to add. (ucfirst)
     * @param string $library The library name to check.
     * @return bool
     */
    public function hasModulLibrary($module, $library)
    {
        return isset( $this->_modulLibraries[ (string)$module ][ (string)$library ] );
    }

    /**
     * Checks if the specified library is available.
     *
     * @param string $library The library name to check.
     * @return bool
     */
    public function hasLibrary($library)
    {
        return isset( $this->_libraries[ (string)$library ] );
    }

    /**
     * Removes a recognized library.
     *
     * @param string $module The modul name to add. (ucfirst)
     * @param string $library The library name to remove.
     * @throws BaseException
     */
    public function removeModulLibrary($module, $library)
    {
        if ( !isset( $this->_modulLibraries[ (string)$module ][ (string)$library ] ) )
        {
            throw new BaseException( 'Library ' . $library . ' for the modul ' . $module . ' is not available.', 'PHP' );
        }

        unset( $this->_modulLibraries[ (string)$module ][ (string)$library ] );
        unset( $this->_extensions[ (string)$library ][ (string)$module ] );
    }

    /**
     * Removes a recognized library.
     *
     * @param string $library The library name to remove.
     * @throws BaseException
     */
    public function removeLibrary($library)
    {
        if ( !isset( $this->_libraries[ (string)$library ] ) )
        {
            throw new BaseException( 'Library ' . $library . ' is not available.' );
        }
        unset( $this->_libraries[ (string)$library ] );
        unset( $this->_extensions[ (string)$library ] );
    }

    /**
     * Sets the namespace separator used by classes in the namespace of this class loader.
     *
     * @param string $sep The separator to use.
     */
    public function setNamespaceSeparator($sep)
    {
        $this->_namespaceSeparator = $sep;
    }

    /**
     * Gets the namespace seperator used by classes in the namespace of this class loader.
     *
     * @return string
     */
    public function getNamespaceSeparator()
    {
        return $this->_namespaceSeparator;
    }

    /**
     * Sets the default path used by the libraries. Note that it does not affect
     * the already added libraries.
     *
     * @param string $defaultPath The new default path.
     */
    public function setDefaultPath($defaultPath)
    {
        if ( $defaultPath[ strlen( $defaultPath ) - 1 ] != '/' )
        {
            $defaultPath .= '/';
        }
        $this->_defaultPath = $defaultPath;
    }

    /**
     * Returns the default path used by the libraries.
     *
     * @return string The current default path.
     */
    public function getDefaultPath()
    {
        return $this->_defaultPath;
    }

    /**
     * Installs this class loader on the SPL autoload stack.
     */
    public function register()
    {
        spl_autoload_register( array(
            $this,
            'loadClass') );
    }

    /**
     * Uninstalls this class loader from the SPL autoloader stack.
     */
    public function unregister()
    {
        spl_autoload_unregister( array(
            $this,
            'loadClass') );
    }

    /**
     *
     * @param string $classNameIn
     * @param $_namespaces
     * @param $base
     * @throws BaseException
     * @return boolean
     */
    protected function findByModul($classNameIn, $_namespaces, $base)
    {
        //$_namespaces = explode( $this->_namespaceSeparator, $classNameIn );
        // $base        = array_shift( $_namespaces );
        $usePath     = false;
        $replacement = false;

        if ( $base === 'Plugin' && ( $classNameIn === 'Plugin' || $_namespaces[ 0 ] == 'Abstract' ) )
        {
            return false;
        }

        if ( isset( $_namespaces[ 0 ] ) )
        {
            if ( $_namespaces[ 0 ] !== 'Abstract' )
            {

                $ucfbase = ucfirst( strtolower( $base ) );

                if ( $base && isset( $this->_modulLibraries[ $ucfbase ][ $_namespaces[ 0 ] ] ) )
                {
                    $library     = array_shift( $_namespaces );
                    $usePath     = $this->_modulLibraries[ $ucfbase ][ $library ];
                    $replacement = implode( $this->_namespaceSeparator, $_namespaces );
                }
            }
        }


        if ( $usePath && $replacement )
        {
            if ( is_file( $usePath . $replacement . '.php' ) )
            {
                include_once $usePath . $replacement . '.php';

                return true;
                /*
                                if ( class_exists( $classNameIn, false ) || interface_exists($classNameIn, false) )
                                {
                                    $this->_loaded[ $classNameIn ] = true;
                                    return true;
                                }
                                else {
                                    if (strpos($classNameIn, '_Model') === false ) {
                                        throw new BaseException( sprintf( 'The File "%s" has no Class "%s" @' . __LINE__, $usePath . $replacement . '.php', $classNameIn ) );
                                    }
                                }
                */
            }
            else
            {
                throw new BaseException( sprintf( 'The Class File is not readable in Directory "%s" @' . __LINE__, $usePath . $replacement . '.php' ) );
            }
        }


        return false;
    }


    /**
     * @param $className
     * @param $base
     * @param array $_namespaces
     * @return null|string
     */
    private function getPath($className, $base, array $_namespaces)
    {

        if ( $base === 'Widget' && ( $className === 'Widget' || ( isset( $_namespaces[ 0 ] ) && $_namespaces[ 0 ] === 'Abstract' ) ) )
        {
            return $this->_defaultPath;
        }
        elseif ( $base === 'Addon' && ( $className === 'Addon' || ( isset( $_namespaces[ 0 ] ) && $_namespaces[ 0 ] === 'Abstract' ) ) )
        {
            return $this->_defaultPath;
        }
        elseif ( $base === 'Widget' )
        {
            return $this->_libraries[ $base ];
        }
        elseif ( $base === 'Provider' && isset( $_namespaces[ 0 ] ) && $_namespaces[ 0 ] !== 'Abstract' )
        {
            #return $this->_libraries[ $base ];
        }

        return null;
    }

    /**
     * @param $classNameIn
     * @param $base
     * @param array $_namespaces
     * @return string
     */
    private function getClassName($classNameIn, $base, array $_namespaces)
    {
        if ( isset( $_namespaces[ 0 ] ) && $_namespaces[ 0 ] && $_namespaces[ 0 ] !== 'Abstract' )
        {

            if ( $base === 'Provider' )
            {
                return substr( $classNameIn, strlen( $base ) + 1 ) . '_' . substr( $classNameIn, strlen( $base ) + 1 );
            }
            elseif ( $base === 'Widget' )
            {
                return substr( $classNameIn, strlen( $base ) + 1 );
            }
            elseif ( $base === 'Addon' )
            {
                return substr( $classNameIn, strlen( $base ) + 1 );
            }
            elseif ( $base === 'CoreTag' )
            {
                return substr( $classNameIn, strlen( $base ) + 1 );
            }
            elseif ( $base === 'Field' )
            {
                return substr( $classNameIn, strlen( $base ) + 1 );
            }
        }

        return $classNameIn;
    }


    /**
     * Loads the given class or interface.
     * @param string $classNameIn The name of the class to load.
     * @return bool
     * @throws BaseException
     */
    public function loadClass($classNameIn)
    {
        if ( $classNameIn === null )
        {
            throw new BaseException( 'The $classNameIn is null!' );
        }

        /*
        if ( isset( $this->_loaded[ $classNameIn ] ) || class_exists( $classNameIn, false ) || interface_exists($classNameIn, false) )
        {
            return true;
        }

        */


        $_namespaces = explode( $this->_namespaceSeparator, $classNameIn );
        $base        = array_shift( $_namespaces );
        $usePath     = null;

        if ( strpos( $classNameIn, '_Action_' ) !== false || strpos( $classNameIn, '_Controller_' ) !== false || strpos( $classNameIn, '_Config_' ) !== false || strpos( $classNameIn, '_Model_' ) !== false || strpos( $classNameIn, '_Helper_' ) !== false || strpos( $classNameIn, '_Widget_' ) !== false )
        {
            if ( $this->findByModul( $classNameIn, $_namespaces, $base ) )
            {
                return true;
            }
        }


        $className = $this->getClassName( $classNameIn, $base, $_namespaces );
        $usePath   = $this->getPath( $className, $base, $_namespaces );


        if ( isset( $this->_libraries[ $base ] ) && $usePath === null )
        {
            $usePath = $this->_libraries[ $base ];
        }

        $replacement = str_replace( array(
            '_',
            $this->_namespaceSeparator), '/', $className );


        if ( $usePath === null )
        {
            $usePath = $this->_defaultPath;
        }


        if ( $classNameIn == 'Provider_Asset' )
        { #die($usePath);
            #die($className .' '.$usePath . $replacement);
        }
        if ( is_readable( $usePath . $replacement . '.php' ) )
        {
            include_once $usePath . $replacement . '.php';

            return true;

        }
        else
        {
            return false;
        }
    }

    /**
     * Loads the given class or interface.
     *
     * @param string $classNameIn The name of the class to load.
     * @return bool
     * @throws BaseException
     */
    public function loadClass0($classNameIn)
    {


        if ( $classNameIn === null || empty( $classNameIn ) )
        {
            throw new BaseException( 'The $classNameIn is null!' );
        }


        if ( isset( $this->_loaded[ $classNameIn ] ) || class_exists( $classNameIn, false ) )
        {
            return true;
        }


        $_namespaces = explode( $this->_namespaceSeparator, $classNameIn );

        $base    = array_shift( $_namespaces );
        $usePath = null;


        if ( !empty( $_namespaces[ 0 ] ) && !empty( $base ) )
        {
            if ( isset( $this->_modulLibraries[ ucfirst( strtolower( $base ) ) ][ $_namespaces[ 0 ] ] ) )
            {
                $library = array_shift( $_namespaces );
                $usePath = $this->_modulLibraries[ ucfirst( strtolower( $base ) ) ][ $library ];

                $replacement = implode( $this->_namespaceSeparator, $_namespaces );
                #$replacement = str_replace(array('_', $this->_namespaceSeparator), '/', $classNameIn);
                # echo($usePath.implode($this->_namespaceSeparator, $_namespaces))."\n";
            }
        }


        $addonPatch    = false;
        $pluginPatch   = false;
        $widgetPatch   = false;
        $providerPatch = false;
        $coretagPatch  = false;

        if ( $base === 'Widget' && ( empty( $_namespaces[ 0 ] ) || $_namespaces[ 0 ] === 'Abstract' ) )
        {
            $widgetPatch = true;
        }

        if ( $base === 'Addon' && ( empty( $_namespaces[ 0 ] ) || $_namespaces[ 0 ] === 'Abstract' ) )
        {
            $addonPatch = true;
        }

        if ( $base === 'Plugin' && ( empty( $_namespaces[ 0 ] ) || $_namespaces[ 0 ] === 'Abstract' ) )
        {
            $pluginPatch = true;
        }

        if ( $base === 'Provider' && ( empty( $_namespaces[ 0 ] ) || $_namespaces[ 0 ] === 'Abstract' ) )
        {
            $providerPatch = true;
        }

        if ( $base === 'CoreTag' && ( empty( $_namespaces[ 0 ] ) || $_namespaces[ 0 ] === 'Abstract' ) )
        {
            $coretagPatch = true;
        }


        if ( !empty( $_namespaces[ 0 ] ) && $usePath === null && isset( $this->_libraries[ $base ] ) && !$widgetPatch && !$addonPatch && !$pluginPatch && !$providerPatch && !$coretagPatch
        )
        {
            $usePath     = $this->_libraries[ $base ];
            $replacement = implode( $this->_namespaceSeparator, $_namespaces );
        }


        if ( $base === 'Field' )
        {
            //      print_r($_namespaces);
        }


        if ( $base === 'Provider' && !$providerPatch && !empty( $_namespaces[ 0 ] ) )
        {
            $className   = substr( $classNameIn, strlen( $base ) + 1 );
            $className   = $className . '_' . $className;
            $replacement = str_replace( array(
                '_',
                $this->_namespaceSeparator), '/', $className );
        }

        if ( $base === 'Widget' && !$widgetPatch && !empty( $_namespaces[ 0 ] ) )
        {
            $className = substr( $classNameIn, strlen( $base ) + 1 );
            #  $replacement = str_replace(array('_', $this->_namespaceSeparator), '/', $className);
        }

        if ( $base === 'CoreTag' && !$coretagPatch && !empty( $_namespaces[ 0 ] ) )
        {
            $className   = substr( $classNameIn, strlen( $base ) + 1 );
            $replacement = str_replace( array(
                '_',
                $this->_namespaceSeparator), '/', $className );
        }


        if ( $base === 'Plugin' && !$pluginPatch && !empty( $_namespaces[ 0 ] ) )
        {
            $className = substr( $classNameIn, strlen( $base ) + 1 );
            #  $replacement = str_replace(array('_', $this->_namespaceSeparator), '/', $className);
        }


        if ( $base === 'Addon' && !$addonPatch && !empty( $_namespaces[ 0 ] ) )
        {
            $className = substr( $classNameIn, strlen( $base ) + 1 );
            #  $replacement = str_replace(array('_', $this->_namespaceSeparator), '/', $className);
        }


        if ( isset( $this->_libraries[ $base ] ) && $usePath === null && !$widgetPatch )
        {
            $usePath = $this->_libraries[ $base ];

            $className = $classNameIn;

            if ( $base === 'Widget' && !$widgetPatch )
            {
                $className = substr( $classNameIn, strlen( $base ) + 1 );
            }

            if ( $base === 'Plugin' && $pluginPatch )
            {
                $className = substr( $classNameIn, strlen( $base ) + 1 );
            }

            if ( $base === 'Addon' && $addonPatch )
            {
                $className = substr( $classNameIn, strlen( $base ) + 1 );

                // $replacement = str_replace(array('_', $this->_namespaceSeparator), '/', $className);
            }

            if ( $base === 'Provider' && !$providerPatch )
            {
                $className = substr( $classNameIn, strlen( $base ) + 1 );
                $className = $className . '_' . $className;
            }

            if ( $base === 'CoreTag' && !$coretagPatch )
            {
                $className = substr( $classNameIn, strlen( $base ) + 1 );
                //$className = $className .'_'.$className;
            }

            if ( $base === 'Provider' && !empty( $_namespaces[ 0 ] ) )
            {
                $className   = substr( $classNameIn, strlen( $base ) + 1 );
                $className   = $className . '_' . $className;
                $replacement = str_replace( array(
                    '_',
                    $this->_namespaceSeparator), '/', $className );
            }
            elseif ( $base === 'Addon' && !empty( $_namespaces[ 0 ] ) )
            {
                $className   = substr( $classNameIn, strlen( $base ) + 1 );
                $className   = $className . '_' . $className;
                $replacement = str_replace( array(
                    '_',
                    $this->_namespaceSeparator), '/', $className );
            }
            else
            {
                $replacement = str_replace( array(
                    '_',
                    $this->_namespaceSeparator), '/', $className );
            }
        }
        elseif ( $base === 'Widget' && !$widgetPatch )
        {
            $replacement = str_replace( array(
                '_',
                $this->_namespaceSeparator), '/', $className );
        }
        elseif ( $base === 'Addon' && !$widgetPatch )
        {
            $replacement = str_replace( array(
                '_',
                $this->_namespaceSeparator), '/', $className );
        }

        /**
         * load from framework
         */
        if ( $usePath === null )
        {
            $usePath     = $this->_defaultPath;
            $replacement = str_replace( array(
                '_',
                $this->_namespaceSeparator), '/', $classNameIn );
        }


        if ( $this->_useAutoloadDebug )
        {
            Debug::putFile(
                't.txt', 'Use path for class "' . $classNameIn . '" ' . "\n"
                . ' path:' . $usePath . $replacement . '.php' . "\n\n" );
        }


        if ( is_readable( $usePath . $replacement . '.php' ) )
        {
            include $usePath . $replacement . '.php';
            $this->_loaded[ $classNameIn ] = true;

            return true;
        }
        else
        {
            return true;
            //throw new BaseException(sprintf('The Class File is not readable in Directory "%s"', $usePath . $replacement . '.php'));
        }

    }

}

?>