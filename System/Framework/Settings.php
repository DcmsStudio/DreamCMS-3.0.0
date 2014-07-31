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
 * @file        Settings.php
 *
 */
class Settings
{

    /**
     * @var bool
     */
    private static $init = false;

    /**
     * @var Application
     */
    private static $_applicationInstance = null;


    /**
     *
     * @param string $name
     * @param mixed $default default is empty string
     * @return mixed
     */
    public static function get( $name, $default = null )
    {

        if ( self::$_applicationInstance === null) {
            self::$_applicationInstance = Registry::getObject( 'Application' );

            if ( !(self::$_applicationInstance instanceof Application) )
            {
                trigger_error( 'The Settings Class can use only after Application is loaded!', E_USER_ERROR );
            }

            $cfg = self::$_applicationInstance->getSystemConfig();
            if ( !($cfg instanceof Config) )
            {
                trigger_error( 'The Settings Class can use only after Application is loaded!', E_USER_ERROR );
            }
        }

        $keyName = null;
        if ( strpos( $name, '.' ) !== false )
        {
            $_r = explode( '.', $name );
            $name = $_r[ 0 ];
            $keyName = $_r[ 1 ];
        }

        $v = self::$_applicationInstance->getSystemConfig()->get( $name, $default );

        if ( $keyName !== null && !($v instanceof Config) )
        {
            return $default;
            trigger_error( 'The Settings Class can use only after Application is loaded!', E_USER_ERROR );
        }

        return ($keyName !== null ? $v->get( $keyName, $default ) : $v);
    }

    /**
     *
     * @param string $name
     * @param null   $value
     * @internal param string $default
     * @return mixed
     */
    public static function set( $name, $value = null )
    {
        $applicationObj = Registry::getObject( 'Application' );

        if ( !($applicationObj instanceof Application) )
        {
            trigger_error( 'The Settings Class can use only after Application is loaded!', E_USER_ERROR );
        }

        return $applicationObj->getSystemConfig()->set( $name, $value );
    }

    /**
     * Get all Config Items
     *
     * @return array
     */
    public static function getAll()
    {
        $applicationObj = Registry::getObject( 'Application' );
        if ( !($applicationObj instanceof Application) )
        {
            trigger_error( 'The System settings can use only after Application is loaded!', E_USER_ERROR );
        }

        return $applicationObj->getSystemConfig()->toArray();
    }

    /**
     *
     * @param array $data
     * @throws BaseException
     */
    public static function write( $data = null )
    {
        if ( !is_file( PAGE_PATH . ".config.php" ) )
        {
            throw new BaseException( 'The config file not exists!' );
        }

        if ( !is_writeable( PAGE_PATH . ".config.php" ) )
        {
            throw new BaseException( 'The config file is not writeable!' );
        }


        $applicationObj = Registry::getObject( 'Application' );
        if ( !($applicationObj instanceof Application) )
        {
            $applicationObj = null;
            throw new BaseException( 'The Settings Class can use only after Application is loaded!' );
        }

        $_config = $applicationObj->getSystemConfig();
        $groups = Dashboard_Config_Base::loadConfigOptions();


        if ( is_array( $data ) )
        {
            
        }
        else
        {

            $cache = null;
            $db = Database::getInstance();

            // rewrite config from database
            $result = $db->query( 'SELECT * FROM %tp%config WHERE pageid = ? ORDER BY `group` ASC', PAGEID )->fetchAll();
            foreach ( $result as $r )
            {
                if ( $r[ 'varname' ] === "badsearchwords" )
                {
                    $fp_badwords = fopen( PAGE_CACHE_PATH . "badwords.db", "w" );
                    fwrite( $fp_badwords, trim( (string) $r[ 'value' ] ) );
                    fclose( $fp_badwords );

                    continue;
                }

                $items = $groups[ $r[ 'group' ] ][ 'items' ];
                $var = $items[ $r[ 'varname' ] ];


                $label = '';
                if ( isset( $var[ 'label' ] ) )
                {
                    $label = $var[ 'label' ];
                }

                $description = (isset( $var[ 'description' ] ) ? preg_replace( "/<br\s*\/?>/", "\n// ", $var[ 'description' ] ) : '');

                if ( is_numeric( $r[ 'value' ] ) && strlen( $r[ 'value' ] ) > 1 && substr( $r[ 'value' ], 0, 1 ) === "0" )
                {
                    // is numeric and begin with 0 (sero)
                    $value = '\'' . addcslashes( $r[ 'value' ], "'" ) . '\'';
                }
                elseif ( is_numeric( $r[ 'value' ] ) || is_integer( $r[ 'value' ] ) || is_int( $r[ 'value' ] ) )
                {
                    $value = (int) $r[ 'value' ];
                }
                else
                {
                    $value = '\'' . addcslashes( $r[ 'value' ], "'" ) . '\'';
                }

                if ( $r[ 'modul' ] == 1 || $r[ 'modul' ] == 2 )
                {
                    $cache[] = ($label ? '// ' . $label . "\n" : '') . ($description ? '// ' . $description . "\n" : '')
                            . '$cfg[\'' . strtolower( $r[ 'group' ] ) . '\'][\'' . $r[ 'varname' ] . '\'] = ' . $value . ';';
                }
                else
                {

                    $cache[] = ($label ? '// ' . $label . "\n" : '') . ($description ? '// ' . $description . "\n" : '') . '$cfg[\'' . $r[ 'varname' ] . '\'] = ' . $value . ';';
                }
            }

            $_config->setWriteable();

            if ( $_config->readOnly() )
            {
                $_config = null;
                throw new BaseException( 'The System settings can´t change! (ReadOnly Mode)' );
            }

            // die( implode( "\n\n", $cache ) );


            $code = '<?' . 'php';
            $code .= "
/**
 *  DreamCMS Ver. " . VERSION . "
 *  Do not edit manual this configuration!
 */
if (!defined('IN')) { throw new BaseException('No direct use allowed!'); }

" . implode( "\n\n", $cache );

            $code .= '?' . '>';

            if ( !$fp = fopen( PAGE_PATH . '.config.php', 'w' ) )
            {
                trigger_error( 'The System settings can´t change! (ReadOnly Mode)', E_USER_ERROR );
            }

            if ( !fwrite( $fp, $code ) )
            {
                trigger_error( 'The System settings can´t change! (ReadOnly Mode)', E_USER_ERROR );
            }

            fclose( $fp );
            //@chmod(PAGE_PATH . "config.php", 0666);

            $_config->setReadOnly();
        }
    }

}

?>