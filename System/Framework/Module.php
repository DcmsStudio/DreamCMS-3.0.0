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
 * @file        Module.php
 *
 */
class Module
{

    /**
     * Current object instance (Singleton)
     * @var object
     */
    protected static $objInstance = null;

    /**
     * @var Database_Adapter_Abstract|null
     */
    protected $db = null;

    /**
     * @var array
     */
    protected static $_modules = array();

    /**
     * @var null
     */
    protected static $_appInstance = null;

    /**
     * @var null
     */
    protected static $_controllerInstance = null;

    /**
     * @var
     */
    protected $currentFunction;

    /**
     * @var
     */
    protected $arguments;

    /**
     * @var
     */
    public $now;

    /**
     * @var array
     */
    private $_calls = array(
        'getComments'     => 1,
        'getCommentform'  => 1,
        'getLastcomments' => 1 );

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
        if ( self::$objInstance === null )
        {
            self::$objInstance = new Module();
            self::$objInstance->db = Database::getInstance();
        }

        return self::$objInstance;
    }

    /**
     *
     */
    public function __construct()
    {


        $this->db = Database::getInstance();
        self::$_modules = $this->cacheModules();
    }

    public function __destruct()
    {
        self::$objInstance = null;
    }

    /**
     *
     * @return array
     */
    private function cacheModules()
    {
        $mods = Cache::get( 'modules', 'data' );

        if ( !$mods )
        {
            $mods = $this->db->query( 'SELECT * FROM %tp%module WHERE pageid = ?', PAGEID )->fetchAll();

            foreach ( $mods as $r )
            {
                self::$_modules[ ucfirst( strtolower( $r[ 'module' ] ) ) ] = $r;
            }

            Cache::write( 'modules', self::$_modules, 'data' );
        }

        return $mods;
    }

    /**
     * @return array
     */
    public function getModules()
    {
        return $this->cacheModules();
    }

    /**
     *
     * @param string $key
     * @return array
     */
    public function getModul( $key )
    {
        $name = ucfirst( strtolower( $key ) );
        if ( isset( self::$_modules[ $name ] ) )
        {
            return self::$_modules[ $name ];
        }

        $key = strtolower( $key );


        self::$_modules[ $name ] = $this->db->query( '
            SELECT * FROM %tp%module 
            WHERE `module` = ? AND pageid = ?
            AND published >= ?', $key, PAGEID, (defined( 'ADM_SCTIPT' ) ? 0 : 1 ) )->fetch();

        return self::$_modules[ $name ];
    }

    /**
     *
     * @param string $key
     * @param array  $data
     *
     * @throws BaseException
     * @return mixed
     */
    public function run( $key, $data = array() )
    {
        $name = ucfirst( strtolower( $key ) );

        $isModul = false;

        // Private function?
        if ( $name === 'Instance' || $name === 'Modul' || $name === 'Modules' )
        {
            throw new BaseException( sprintf( 'This modul function `%s` is not allowed!', $name ) );
        }

        if ( isset( $this->_calls[ 'get' . $name ] ) && !isset( $data[ 'run' ] ) )
        {
            $this->currentFunction = 'get' . $name;
        }
        else
        {
            $callFunction = $data[ 'run' ];

            if ( empty( $callFunction ) )
            {
                throw new BaseException( sprintf( 'This modul `%s` must give a call!', $name ) );
            }

            unset( $data[ 'run' ] );
            $this->currentFunction = 'get' . ucfirst( strtolower( $callFunction ) );

            $isModul = true;
        }


        $this->arguments = $data;
        $this->now = time();


        // getComments and getCommentsform
        if ( isset( $this->_calls[ $this->currentFunction ] ) && !$isModul )
        {
            return call_user_func_array( array(
                $this,
                $this->currentFunction ), $this->arguments );
        }

        // load modul if not exists
        if ( !isset( self::$_modules[ $name ] ) )
        {
            $this->getModul( $key );
        }

        if ( !isset( self::$_modules[ $name ] ) )
        {
            throw new BaseException( sprintf( 'The modul `%s` not exists!', $name ) );
        }

        $model = Model::getModelInstance( $name );
        $model->providerModulCall = $this->currentFunction;

        if ( !method_exists( $model, $this->currentFunction ) )
        {
            throw new BaseException( sprintf( 'The modul function `%s` not exists!', $this->currentFunction ) );
        }

        $model->setModulParams( $this->arguments );

        return call_user_func_array( array(
            $model,
            $this->currentFunction ), $this->arguments );
    }

    /**
     *
     * @param string $name
     * @param null   $default
     * @return mixed returns null if not exists
     */
    private function getParam( $name, $default = null )
    {
        return (isset( $this->arguments[ $name ] ) ? $this->arguments[ $name ] : $default);
    }

    /**
     *
     * @param string $name
     * @param mixed $value
     */
    private function setParam( $name, $value = null )
    {
        $this->arguments[ $name ] = $value;
    }

    /**
     *
     * @param string  $name
     * @param boolean $debug
     * @throws BaseException
     * @return mixed
     */
    private function getRequiredParam( $name, $debug = false )
    {
        if ( $debug && !isset( $this->arguments[ $name ] ) )
        {
            throw new BaseException( sprintf( trans( 'Das parameter `%s` für das Modul `%s` ist erforderlich aber wurde nicht übergeben!' ), $name, $this->getModul( $name ) ) );
        }
        elseif ( !isset( $this->arguments[ $name ] ) )
        {
            return null;
        }

        return $this->arguments[ $name ];
    }

    /**
     *
     * @return Application
     */
    private function _getApplication()
    {
        if ( self::$_appInstance === null )
        {
            self::$_appInstance = Registry::getObject( 'Application' );
        }

        return self::$_appInstance;
    }

    /**
     *
     * @return Controller
     */
    private function _getController()
    {
        if ( self::$_controllerInstance === null )
        {
            self::$_controllerInstance = Registry::getObject( 'Controller' );
        }

        return self::$_controllerInstance;
    }

    /**
     *
     * @return string
     */
    private function getLastcomments()
    {
        $comments = Model::getModelInstance( 'Comments' );
        $data[ 'comments' ] = $comments;


        return $this->_getController()->Template->process( 'comments/comments', $data );
    }

    /**
     *
     * @return string
     */
    private function getComments()
    {

        $id = (int)$this->getRequiredParam( 'id', true  );
        $section = trim( $this->getRequiredParam( 'section', true ) );
        $appid = (int)$this->getParam( 'appid', false  );
        $permkey = $this->getParam( 'permkey' );
        #$data = $this->_getController()->Template->getData();
        $comments = Model::getModelInstance( 'comments' );

        if ( !isset( $data[ 'permissionkey' ] ) && $permkey )
        {
            $data[ 'permissionkey' ] = $permkey;
            $GLOBALS[strtolower($section)]['permissionkey'] = $permkey;
            $GLOBALS['permissionkey'] = $permkey;
            Session::save('comment_permissionkey', $permkey );
            Session::save('comment_'. $section, $permkey );
        }
        else {
            if ( $permkey ) {
                $GLOBALS[strtolower($section)]['permissionkey'] = $permkey;
                $GLOBALS['permissionkey'] = $permkey;
                Session::save('comment_permissionkey', $permkey );
                Session::save('comment_'. $section, $permkey );
            }
        }

        $data[ 'comments' ] = $comments->loadComments( $section, $id, $appid );

        $comments = null;

       # print_r($id);exit;


        $tpl = new Template();
        $tpl->isProvider = true;


        return $this->_getController()->Template->process( 'comments/comments', $data );
    }

    /**
     * @return array
     */
    private function getSmilies ()
    {

        $smilies = BBCode::getSmilies();
        $out     = array ();

        if (is_array($smilies)) {
            foreach ( $smilies as $r )
            {
                $out[ ] = array (
                    'title'   => $r[ 'smilietitle' ],
                    'imgpath' => $r[ 'smiliepath' ],
                    'bbcode'  => $r[ 'smiliecode' ]
                );
            }
        }

        return $out;
    }

    /**
     *
     * @throws BaseException
     * @return string
     */
    private function getCommentform()
    {

        $id = (int)$this->getRequiredParam( 'id', true  );
        $section = trim( $this->getRequiredParam( 'section', true ) );

        $article_location = trim( $this->getRequiredParam( 'location', true ) );
        $article_title = trim( $this->getRequiredParam( 'title', true ) );

        $appid = (int)$this->getParam( 'appid'  );
        $permkey = $this->getParam( 'permkey' );


        $data = $this->_getController()->Template->getData();
        $data[ 'captchaurl' ] = 'main/captcha';
        $data[ 'commentstype' ] = $section;
        $data[ 'commentpostid' ] = $id;

        $data[ 'article_url' ] = rawurldecode($article_location);
        $data[ 'article_title' ] = html_entity_decode( $article_title );
        $data[ 'bbcode_smilies' ]  = Json::encode($this->getSmilies());

        if (!isset($GLOBALS[strtolower($section)]['permissionkey']))
        {
            if ( !isset( $data[ 'permissionkey' ] ) && $permkey )
            {
                $data[ 'permissionkey' ] = $permkey;
            }
            else {
                throw new BaseException('No permission key found!');
            }

        }

        if ( $permkey ) {
            $GLOBALS[strtolower($section)]['permissionkey'] = $permkey;
            $GLOBALS['permissionkey'] = $permkey;
            Session::save('comment_permissionkey', $permkey );
            Session::save('comment_'. strtolower($section), $permkey );
        }

        return $this->_getController()->Template->process( 'comments/comment_form', $data );
    }

}
