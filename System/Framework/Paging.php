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
 * @file        Paging.php
 *
 */
class Paging extends Loader
{

    /**
     * @var null
     */
    private $linkparams = null;

    /**
     * @var string
     */
    private $_tmpUrl = '';

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     *
     * @param array $params
     * @param string $modul default is null and will use the constant CONTROLLER
     * @return bool|mixed
     */
    public function generate( $params, $modul = null )
    {
        $this->load( 'Router' );
        $config = $this->Router->loadRouteConfig( ($modul !== null ? ucfirst( strtolower( $modul ) ) : CONTROLLER ) );
        if ( !is_array( $config ) )
        {
            return '';
        }

        if ( is_array( $params ) )
        {
            if ( !isset( $params[ 'controller' ] ) )
            {
                $params[ 'controller' ] = CONTROLLER;
            }

            if ( !isset( $params[ 'action' ] ) )
            {
                $params[ 'action' ] = ACTION;
            }

            $this->linkparams = $params;
        }




        $tmp = array();
	    $skipalias = false;
        $controller = ucfirst( strtolower( $this->linkparams[ 'controller' ] ) );
        $action = ucfirst( strtolower( $this->linkparams[ 'action' ] ) );

        if (!isset($tmp[ $controller ][ $action ]) )
        {
            $tmp[ $controller ][ $action ] = array();
            foreach ( $config as $param )
            {
                if ( $controller === ucfirst( strtolower( $param[ 'controller' ] ) ) && $action === ucfirst( strtolower( $param[ 'action' ] ) ) )
                {
                    $tmp[ $controller ][ $action ][ $param[ 'rule' ] ] = $param;
                }
            }
        }
        else
        {
            if ( is_array( Router::$RouteOptions ) )
            {
                $tmp[ $controller ][ $action ][ Router::$RouteOptions[ 'rule' ] ] = Router::$RouteOptions;
            }
        }



        if ( !is_array( $tmp[ $controller ][ $action ] ) )
        {
            return false;
        }

        $alias = isset($this->linkparams[ 'alias' ]) ? $this->linkparams[ 'alias' ] : null;
        $suffix = isset($this->linkparams[ 'suffix' ]) ? $this->linkparams[ 'suffix' ] : null;

        unset( $this->linkparams[ 'controller' ] );
        unset( $this->linkparams[ 'action' ] );
        unset( $this->linkparams[ 'alias' ] );
        unset( $this->linkparams[ 'suffix' ] );

        $tmpUri = '';
        $tmpUriFound = false;

        if ( !function_exists( '_sort' ) )
        {

            /**
             * @param $a
             * @param $b
             * @return int
             */
            function _sort( $a, $b )
            {
                if ( substr_count( $a[ 'rule' ], ':' ) == substr_count( $b[ 'rule' ], ':' ) )
                {
                    return 0;
                }

                return (substr_count( $a[ 'rule' ], ':' ) > substr_count( $b[ 'rule' ], ':' )) ? -1 : 1;
            }

        }


        uasort( $tmp[ $controller ][ $action ], '_sort' );


        foreach ( $tmp[ $controller ][ $action ] as $l => $param )
        {
            // remove the page param from urlrewrite and unset the attribute
            $rule = preg_replace( '@/\??:page@', '', $param[ 'rule' ] );
            unset( $param[ 'params' ][ 'page' ] );

            $ruleParamCount = count( $param[ 'paramkeys' ] );
            $foundParams = $param[ 'paramkeys' ];
            $ruleparam_Count = substr_count( $rule, ':' );


            $tmpUri = $rule;
            $tmpUriFound = false;
            $replaced = 0;


            if ( strpos( $param[ 'rule' ], ':page' ) !== false )
            {
                $ruleParamCount = $ruleParamCount - 1;
                $ruleparam_Count = $ruleparam_Count - 1;
                unset( $foundParams[ 'page' ] );
            }

            foreach ( $this->linkparams as $key => $value )
            {
                if ( isset( $param[ 'params' ][ $key ] ) )
                {
                    $_value = trim( ($value === null ? '' : str_replace( ':', '&#58;', $value ) ) );

                    if ($value !== null && preg_match( '@(' . $param[ 'params' ][ $key ] . ')@isU', $_value ) )
                    {

                        unset( $foundParams[ $key ] );
                        $tmpUri = preg_replace( '@/\??:' . $key . '@isU', ($_value ? '/' . $_value : '' ), $tmpUri );
                        $replaced++;
                    }
	                elseif ( $value === null ) {
		                unset( $foundParams[ $key ] );
		                $tmpUri = preg_replace( '@/\??:' . $key . '@isU', '', $tmpUri );
	                }
                }

            }


            if ( $ruleParamCount > 0 && $ruleparam_Count != $replaced && $ruleparam_Count !== 0 )
            {
                continue;
            }


            if ( $ruleparam_Count == 0 )
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
                    $tmpUri = $tmprule = '';
                }
            }
        }


        if ( $tmpUriFound )
        {
            if ( $skipalias !== true )
            {
                $tmpUri .= ($alias != '' ? '/' . $alias . ($suffix ? '.' . $suffix : '') : '');
            }

            $tmpUri = preg_replace( '#\/\/#', '', $tmpUri );
            $this->_tmpUrl = $tmpUri;

            return $tmpUri;
        }

        $tmpUri = preg_replace( '#\/\/#', '', $tmpUri );


        $this->_tmpUrl = $tmpUri;


        return $tmpUri;
    }

    /**
     *
     * @param         $link
     * @param integer $page
     * @param integer $pages
     * @param null    $putToTemplateVar
     * @internal param string $url
     * @return string
     */
    public function setPaging( $link, $page = 1, $pages = 0, $putToTemplateVar = null )
    {
        if ( $pages <= 1 )
        {
            return '';
        }

        $end_link = '';
        $isAdmin = false;
        if ( $this->getApplication()->isBackend() )
        {
            $isAdmin = true;
        }


        if ( !$isAdmin )
        {
            // $link = (Settings::get( 'mod_rewrite', false ) ? '' : 'index.php/') . $link;
        }


        if ( defined( 'DOCUMENT_NAME' ) && DOCUMENT_NAME != '' && DOCUMENT_NAME != '.' && !$isAdmin )
        {
            if ( strpos( $link, '/' . DOCUMENT_NAME . '.' . DOCUMENT_EXTENSION ) === false )
            {
                $end_link = '/' . DOCUMENT_NAME . '.' . DOCUMENT_EXTENSION;
            }
        }


        $number = 3;
        $userewrite = Settings::get( 'mod_rewrite', false );
        $addPublic = Settings::get('mod_rewrite_addpublic', false);

        $pagelink = '<ul class="page-numbers">';
        $pagelink .= '<li class="num-of-pages">' . trans( sprintf( 'Seite %s von %s Seiten', $page, $pages ) ) . '</li>';

        if (!$isAdmin && !$userewrite) {
            $r = parse_url( $link );


            if ( isset( $r[ 'path' ] ) && !empty( $r[ 'path' ] )) {

                $link = '';

                if ($addPublic) {
                    $link .= '/public';
                }

                $link .= '/index.php?_call='.$r[ 'path' ];

                $end_link = '';
            }

        }

        if ( $page > 1 )
        {
            $temppage = $page - 1;
            $l = $link . (!$isAdmin && $userewrite ? "/$temppage" : '&amp;page=' . $temppage);
            $back = trans( 'Zurück' );
            $pagelink .= "<li class=\"back-page\"><a href=\"" . $l . $end_link . "\"></a></li>";
        }

        if ( ($page - $number) > 1 )
        {
            $l = $link . (!$isAdmin && $userewrite ? '/1' : '&amp;page=1');
            $fp = trans( '« Erste Seite' );
            $pagelink .= "<li><a href=\"" . $l . $end_link . "\">1</a></li><li class=\"space\">...</li>";
        }


        $count = ($page + $number >= $pages ? $pages : $page + $number);

        for ( $i = $page - $number; $i <= $count; $i++ )
        {
            if ( $i < 1 )
            {
                $i = 1;
            }

            if ( $i == $page )
            {
                $pagelink .= "<li class=\"active-page\">$i</li>";
            }
            else
            {
                $l = $link . (!$isAdmin && $userewrite ? "/$i" : '&amp;page=' . $i);
                $pagelink .= "<li><a href=\"" . $l . $end_link . "\">$i</a></li>";
            }
        }


        if ( ($page + $number) < $pages )
        {
            $l = $link . (!$isAdmin && $userewrite ? "/$pages" : '&amp;page=' . $pages);
            $pagelink .= "<li class=\"space\">...</li><li><a href=\"" . $l . $end_link . "\">{$pages}</a></li>";
        }


        if ( $page < $pages )
        {
            $temppage = $page + 1;
            $l = $link . (!$isAdmin && $userewrite ? "/$temppage" : '&amp;page=' . $temppage);
            $next = trans( 'Weiter' );
            $pagelink .= "<li class=\"next-page\"><a href=\"" . $l . $end_link . "\"></a></li>";
        }

        $pagelink .= "</ul>";


        $this->load( 'Document' );
        $this->Document->set( 'pageing', $pagelink );


        if ( is_string( $putToTemplateVar ) )
        {
            $this->load( 'Template' );
            $this->Template->assign( $putToTemplateVar, $pagelink );
        }


        return $pagelink;
    }

}
