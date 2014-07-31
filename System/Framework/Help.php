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
 * @file        Help.php
 *
 */
class Help extends Loader
{

    /**
     * @var null
     */
    protected static $objInstance = null;

    /**
     * @var string
     */
    protected static $_helpResponse = 'http://help.dcms-studio.de/index.php';

    /**
     * @var null
     */
    protected $modulName = null;

    /**
     * @var array
     */
    protected $postParams = array();

    /**
     * Return the current object instance (Singleton)
     * @return Help
     */
    public static function getInstance()
    {
        if ( self::$objInstance === null )
        {
            self::$objInstance = new Help();
        }

        return self::$objInstance;
    }

    /**
     *
     * @return \Help
     */
    public function init()
    {
        if ( $this->db === null )
        {
            $this->db = Database::getInstance();
        }

        return $this;
    }

    /**
     *
     * @param string $name
     * @return \Help
     */
    public function setModul( $name )
    {
        $this->modulName = ucfirst( strtolower( $name ) );
        return $this;
    }

    /**
     *
     * @param array $params
     * @return \Help
     */
    public function setParams( array $params )
    {
        $this->postParams = $params;
        return $this;
    }

    /**
     *
     */
    public function execute()
    {
        $this->load( 'Remote' );
        return $this->getHelp();
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->Remote->getContent();
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->Remote->getError();
    }

    /**
     * @return mixed
     */
    public function getHeaders()
    {
        return $this->Remote->getHeaders();
    }

    /**
     *
     * @return string
     */
    private function getHelp()
    {
        $url = self::$_helpResponse;
        $post = array(
            'language' => CONTENT_TRANS,
            'modul'    => strtolower( $this->modulName ) );

        if ( is_array( $this->postParams ) )
        {
            $post = array_merge( $post, $this->postParams );
        }


        $this->Remote->setUrl( $url )->setPostParams( $post )->run();
    }

}
