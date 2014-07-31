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
 * @file        AliasRegistry.php
 *
 */
class AliasRegistry
{

    /**
     * @var int
     */
    protected $aliasErrorID = 0;

    /**
     * @var string
     */
    protected $newAlias = '';

    /**
     * @var string
     */
    protected $newSuffix = '';

    /**
     * @var string
     */
    protected $defaultSuffix = '';

    /**
     * @var null
     */
    protected $_alias = null;

    /**
     * @var mixed|null
     */
    protected $_suffix = null;

    /**
     * @var Database_Adapter_Abstract|null
     */
    private $db = null;

    /**
     * @var array
     */
    private static $_cache = array();

    /**
     *
     */
    public function __construct()
    {
        $this->db = Database::getInstance();

        // set default document suffix
        $this->_suffix = Settings::get( 'suffix', 'html' );
    }

    public function destruct()
    {
        $this->freeMem();
    }

    public function freeMem()
    {
        $this->_suffix = null;
        $this->db = null;
    }

    /**
     *
     * @param integer $modulid
     * @return Database_Adapter_Abstract
     */
    public function getByModulId( $modulid )
    {
        return $this->db->query( 'SELECT * FROM %tp%alias_registry WHERE modulid = ?', $modulid );
    }

    /**
     * @param $param
     */
    public function getAllAliase( $param )
    {
        
    }

    /**
     *
     * @param integer $contentid
     * @param integer $modulid
     * @return array
     */
    public function getShortUrls( $contentid = 0, $modulid = 0 )
    {

        if ( isset( self::$_cache[ $contentid ][ $modulid ] ) )
        {
            return (!empty( self::$_cache[ $contentid ][ $modulid ][ 'shorturls' ] ) ? unserialize( self::$_cache[ $contentid ][ $modulid ][ 'shorturls' ] ) : array());
        }


        self::$_cache[ $contentid ][ $modulid ] = $this->db->query( 'SELECT shorturls FROM %tp%alias_registry WHERE contentid = ? AND modulid = ?', $contentid, $modulid )->fetch();
        return (!empty( self::$_cache[ $contentid ][ $modulid ][ 'shorturls' ] ) ? unserialize( self::$_cache[ $contentid ][ $modulid ][ 'shorturls' ] ) : array());
    }

    /**
     *
     * @param string $urls
     * @param integer $contentid
     * @param integer $modulid
     */
    public function saveShortUrls( $urls, $contentid = 0, $modulid = 0 )
    {
        $this->db->query( 'UPDATE %tp%alias_registry SET shorturls = ? WHERE contentid = ? AND modulid = ?', $urls, $contentid, $modulid );
    }

    /**
     * Find a Page by Alias used in frontend
     *
     * @param string $alias
     * @param string $controller      default is null
     * @param null   $action
     * @param string $applicationType default is null
     * @return Database_Adapter_Abstract
     */
    public function findAlias( $alias, $controller = null, $action = null, $applicationType = null )
    {
        $result = null;

        $name = Strings::getFirstCharsForFilename( $alias );

        if ( $controller !== null && strtolower( $controller ) === 'apps' && $applicationType !== null )
        {

            $cacheresult = Cache::get( $name, 'data/aliascache/' . CONTENT_TRANS . '/apps' );

            if ( !isset( $cacheresult[ $alias ] ) )
            {

                $result = $this->db->query( 'SELECT r.*,
                                        m.module, m.published, m.groups, m.settings,
                                        IF(a.apptype != \'\', 1, 0) AS isapp,
                                        a.apptype, a.appfields AS applicationfields, a.settings AS applicationsettings, a.metadata AS applicationmeta
                                        FROM %tp%alias_registry AS r
                                        LEFT JOIN %tp%module AS m ON(m.id = r.modulid)
                                        LEFT JOIN %tp%applications AS a ON(a.appid = r.appid)
                                        WHERE 
                                        r.alias = ? AND
                                        m.`module` = ? AND
                                        m.pageid = ? AND
                                        a.apptype = ? 
                                        LIMIT 1', $alias, strtolower( $controller ), PAGEID, $applicationType
                );

                if ( $result->rowCount() === 1 )
                {
                    $cacheresult[ $alias ] = $result->fetch();

                    $this->db->free();


                    Cache::write( $name, $cacheresult, 'data/aliascache/' . CONTENT_TRANS . '/apps' );

                    $result = new Arr( array(
                        $cacheresult[ $alias ] ) );
                }
            }
            else
            {
                $result = new Arr( array(
                    $cacheresult[ $alias ] ) );
            }
        }
        elseif ( $controller !== null && strtolower( $controller ) != 'apps' && $action !== null )
        {

            //        $cacheresult = Cache::get($name, 'data/aliascache/' . CONTENT_TRANS . '/' . strtolower($controller));
            //        if (!isset($cacheresult[$action][$alias]))
            //       {
            $result = $this->db->query( 'SELECT r.*,
                                        m.module, m.published, m.groups, m.settings, 0 AS isapp
                                        FROM %tp%alias_registry AS r
                                        LEFT JOIN %tp%module AS m ON(m.id = r.modulid)
                                        WHERE 
                                        r.alias = ? AND
                                        m.module = ? AND 
                                        r.action = ? AND 
                                        m.pageid = ? AND r.pageid = ?
                                        LIMIT 1', $alias, strtolower( $controller ), strtolower( $action ), PAGEID, PAGEID );

            if ( $result->rowCount() === 1 )
            {
                $cacheresult[ $action ][ $alias ] = $result->fetch();

                $this->db->free();


                //            Cache::write($name, $cacheresult, 'data/aliascache/' . CONTENT_TRANS . '/' . strtolower($controller));
                $result = new Arr( array(
                    $cacheresult[ $action ][ $alias ] ) );
            }
            //      }
            //      else
            //      {
            //          $result = new Arr(array($cacheresult[strtolower($action)][$alias]));
            //       }
        }
        elseif ( $controller !== null && strtolower( $controller ) != 'apps' && $action === null )
        {
            //    $cacheresult = Cache::get($name, 'data/aliascache/' . CONTENT_TRANS . '/' . strtolower($controller));
            //     if (!isset($cacheresult[$alias]))
            //     {


            $result = $this->db->query( 'SELECT r.*,
                                        m.module, m.published, m.groups, m.settings, 0 AS isapp
                                        FROM %tp%alias_registry AS r
                                        LEFT JOIN %tp%module AS m ON(m.id = r.modulid)
                                        WHERE 
                                        r.alias = ? AND
                                        m.module = ? AND 
                                        m.pageid = ?  AND r.pageid = ?
                                        LIMIT 1', $alias, strtolower( $controller ), PAGEID, PAGEID );


            if ( $result->rowCount() === 1 )
            {
                $cacheresult[ $alias ] = $result->fetch();
                $this->db->free();

                //        Cache::write($name, $cacheresult, 'data/aliascache/' . CONTENT_TRANS . '/' . strtolower($controller));
                $result = new Arr( array(
                    $cacheresult[ $alias ] ) );
            }
            //      }
            //       else
            //      {
            //          $result = new Arr(array($cacheresult[$alias]));
            //       }
        }
        else
        {
            //     $cacheresult = Cache::get($name, 'data/aliascache/' . CONTENT_TRANS . '');
            //     if (!isset($cacheresult[$alias]))
            //     {
            $result = $this->db->query( 'SELECT r.*,
                                        m.module,
                                        m.published,
                                        m.groups, m.settings,
                                        0 AS isapp
                                        FROM %tp%alias_registry AS r
                                        LEFT JOIN %tp%module AS m ON(m.id = r.modulid)
                                        WHERE r.alias = ? AND m.pageid = ? AND r.pageid = ? LIMIT 1', $alias, PAGEID, PAGEID );


            if ( $result->rowCount() === 1 )
            {
                $cacheresult[ $alias ] = $result->fetch();

                $this->db->free();

                //            Cache::write($name, $cacheresult, 'data/aliascache/' . CONTENT_TRANS . '');
                $result = new Arr( array(
                    $cacheresult[ $alias ] ) );
            }
            //    }
            //    else
            //    {
            //        $result = new Arr(array($cacheresult[$alias]));
            //    }
        }

        $cacheresult = null;

        return $result;
    }

    /**
     *
     * @param string $alias
     * @param string $controller
     * @param string $applicationType
     * @return bool
     */
    public function hasAlias( $alias, $controller = null, $applicationType = null )
    {
        $aliasData = $this->findAlias( $alias, $controller, $applicationType )->fetch();
        return ($aliasData[ 'rewriteid' ] ? true : false);
    }

    /**
     * Check existing Alias
     * returns true if found the alias and false if not found
     *
     * @param array $data Example: array(alias => ... , suffix => ... , appid => ... , contentid => ..., documenttitle => ...)
     * @param string $controller
     * @param string $applicationType
     * @return bool
     */
    public function aliasExists( $data, $controller = null, $applicationType = null )
    {
        $this->aliasErrorID = 0;
        $this->newAlias = '';
        $this->newSuffix = '';

        $alias = isset( $data[ 'alias' ] ) ? $data[ 'alias' ] : '';
        $suffix = isset( $data[ 'suffix' ] ) ? $data[ 'suffix' ] : '';
        $documenttitle = isset( $data[ 'documenttitle' ] ) ? $data[ 'documenttitle' ] : '';
        $appid = isset( $data[ 'appid' ] ) ? (int)$data[ 'appid' ]  : 0;
        $suffix = ($suffix != '' ? $suffix : $this->defaultSuffix);

        if ( trim( $alias ) === '' )
        {
            if ( trim( $documenttitle ) === '' )
            {
                Error::raise( 'Dokument Titel fehlt daher kann nicht überprüft werden ob der Alias existiert!' );
            }
            else
            {
                $alias = Library::suggest( (string) $documenttitle );
                $alias = substr( $alias, 0, 140 );
            }
        }
        else
        {
            $alias = Library::suggest( (string) $alias );
            $alias = substr( $alias, 0, 140 );
        }

        $this->newAlias = $alias;
        $this->newSuffix = $suffix;

        $aliasData = $this->findAlias( $alias, $controller, $applicationType )->fetchAll();


        if ( count( $aliasData ) >= 1 )
        {
            $this->aliasErrorID = $aliasData[ 0 ][ 'contentid' ];

            if ( $appid )
            {
                foreach ( $aliasData as $r )
                {
                    if ( $r[ 'appid' ] > 0 && $appid !== $r[ 'appid' ] )
                    {
                        return true;
                    }
                }
            }
            else {
                return true;
            }
        }

        return false;
    }

    /**
     * @return int
     */
    public function getErrorAliasID()
    {
        return $this->aliasErrorID;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->newAlias;
    }

    /**
     * @return string
     */
    public function getSuffix()
    {
        return $this->newSuffix;
    }

    /**
     * Save a new Alias to the Alias Registry
     *
     * @param array $data
     *
     * @example
     * array(controller => ..., action => ..., alias => ... , suffix => ... , appid => ... , contentid => ... , [ modulid => ... ])
     *
     * @return integer ID of alias_registry
     */
    public function registerAlias( $data )
    {
        if ( !is_array( $data ) )
        {
            Error::raise( 'The alias registry could not save the alias. The giving data is not a array!' );
        }

        if ( !isset( $data[ 'contentid' ] ) || empty( $data[ 'contentid' ] ) )
        {
            Error::raise('The alias registry could not save the alias. The giving data has no contentid or contentid is 0!');
        }


        if ( !isset( $data[ 'action' ] ) || empty( $data[ 'action' ] ) )
        {
            Error::raise( 'The alias registry could not save the alias. The giving data has no action!' );
        }

        if ( isset( $data[ 'appid' ] ) && !empty( $data[ 'appid' ] ) )
        {
            $modul = Module::getInstance( 'app' )->getModul( 'app' );

            // print_r($modul); exit;
            $data[ 'modulid' ] = $modul[ 'id' ];


            //Error::raise(trans('Alias kann nicht angelegt werden, da kein Modul übergeben wurde! '. __LINE__));
        }
        else
        {

            if ( (!isset( $data[ 'modulid' ] ) || empty( $data[ 'modulid' ] )) && isset( $data[ 'controller' ] ) && !empty( $data[ 'controller' ] ) )
            {
                $modul = Module::getInstance()->getModul( $data[ 'controller' ] );
                $data[ 'modulid' ] = $modul[ 'id' ];
            }


            // is a plugin
            if ( !isset( $data[ 'modulid' ] ) && isset( $data[ 'controller' ] ) && !empty( $data[ 'controller' ] ) && substr( $data[ 'controller' ], 0, 2 ) === 'p:' )
            {
                $modul = Module::getInstance()->getModul( 'plugin' );
                $data[ 'modulid' ] = $modul[ 'id' ];
            }
        }

        if ( !isset( $data[ 'modulid' ] ) || empty( $data[ 'modulid' ] ) )
        {
            Error::raise( trans( 'Alias kann nicht angelegt werden, da kein Modul übergeben wurde! ' . __LINE__ ) );
        }


        $r = $this->db
            ->select('rewriteid')
            ->from('alias_registry')
            ->where('modulid', '=', $data[ 'modulid' ])
            ->and_where('appid', '=', $data[ 'appid' ])
            ->and_where('contentid', '=', $data[ 'contentid' ])
            ->and_where('lang', '=', $data[ 'lang' ])
            ->and_where('action', '=', $data[ 'action' ])
            ->and_where('pageid', '=', PAGEID)
            ->get();

        // remove first the old alias
        if ($r['rewriteid'])
        {
            $this->db->delete('alias_registry')->where('rewriteid', '=', $r['rewriteid'])->execute();
        }


        $this->db
            ->insert('alias_registry')
            ->values(array('modulid' => $data[ 'modulid' ], 'appid' => $data[ 'appid' ], 'contentid' => $data[ 'contentid' ], 'lang' => $data[ 'lang' ], 'action' => $data[ 'action' ],'alias' => $data[ 'alias' ], 'suffix' => $data[ 'suffix' ], 'pageid' => PAGEID))->execute();


/*
        // remove first the old alias
        $this->db->query( 'DELETE FROM %tp%alias_registry 
            WHERE modulid = ? AND appid = ? AND contentid = ? AND `lang` = ? AND `action`=? AND pageid=?',
            $data[ 'modulid' ], $data[ 'appid' ], $data[ 'contentid' ], $data[ 'lang' ], $data[ 'action' ], PAGEID
        );

        $this->db->query( 'REPLACE INTO %tp%alias_registry (modulid, appid, contentid, alias, suffix,`lang`, `action`, pageid)
                          VALUES(?,?,?,?,?,?,?,?)', $data[ 'modulid' ], $data[ 'appid' ], $data[ 'contentid' ], $data[ 'alias' ], $data[ 'suffix' ], $data[ 'lang' ], $data[ 'action' ], PAGEID );
*/
        return $this->db->insert_id();
    }

    /**
     * Remove a Alias from the Alias Registry
     *
     * @param mixed $alias (string or integer) if integer giving the remove by id
     * @param string $controller default is null
     * @param null $action
     * @param string $applicationType default is null
     */
    public function removeAlias( $alias, $controller = null, $action = null, $applicationType = null )
    {
        $where = ' alias = ? AND `action` = ?';
        if ( is_integer( $alias ) )
        {
            $where = ' rewriteid = ? AND `action` = ?';
        }

        if ( $controller === null || !trim( $controller ) )
        {
            Error::raise( trans( 'Alias kann nicht gelöscht werden, da kein Controller übergeben wurde!' ) );
        }

        $modul = Module::getInstance()->getModul( $controller );

        $this->db->query( 'DELETE FROM %tp%alias_registry WHERE pageid = ? AND modulid = ? AND ' . $where, PAGEID, $modul[ 'id' ], $alias, $action );
    }

    /**
     *
     * @param integer $id
     * @param string $controller
     * @param string $action
     */
    public function removeAliasByContentid( $id, $controller = null, $action = null )
    {
        if ( $controller === null || !trim( $controller ) )
        {
            Error::raise( trans( 'Alias kann nicht gelöscht werden, da kein Controller übergeben wurde!' ) );
        }

        $modul = Module::getInstance()->getModul( $controller );

        $this->db->query( 'DELETE FROM %tp%alias_registry WHERE pageid = ? AND modulid = ? AND contentid = ? AND `action` = ?', PAGEID, $modul[ 'id' ], $id, $action );
    }

}
