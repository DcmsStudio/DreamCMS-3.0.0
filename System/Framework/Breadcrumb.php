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
 * @file        Breadcrumb.php
 *
 */
class Breadcrumb extends Loader
{

    /**
     * @var array
     */
    private static $_BreadcrumbCache = array();

    /**
     * @var array
     */
    private static $_Breadcrumbs = array();

    private static $_count = 0;

    protected static $_inst = null;


    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->load( 'Site' );
    }

    /**
     * @return Breadcrumb|null
     */
    public static function getInstance() {

        if (self::$_inst === null) {
            self::$_inst = new Breadcrumb();
        }
        return self::$_inst;
    }

    /**
     *
     */
    public function freeMem()
    {
	    self::$_count = 0;
    }

    /**
     * insert
     * @param string $label
     * @param string $link
     */
    public function add( $label, $link = '' )
    {
	    /**
	     * Frontpage patch 1
	     */
	    if (self::$_count === 0 && (isset($GLOBALS['IS_FRONTPAGE']) && $GLOBALS['IS_FRONTPAGE'] ) )
	    {
		    self::$_count++;
		    return;
	    }

	    /**
	     * Frontpage patch 2
	     */
	    $frontpage = Settings::get('frontpage', '');
	    if ( $frontpage && $link ) {

		    $frontpageNoDomain = preg_replace('#^'. preg_quote(Settings::get('portalurl') , '#') .'#is', '', $frontpage );
		    if (preg_match('#^'. preg_quote($frontpage, '#') . '$#is', $link) || preg_match('#^'. preg_quote($frontpageNoDomain, '#') . '.*#is', $link) ) {
			    self::$_count++;
			    return;
		    }
	    }

	    if ($label)
        {
	        self::$_Breadcrumbs[]  = array(
	            $label,
	            $link
            );
		    self::$_count++;

            #print_r(self::$_Breadcrumbs);
            #exit;
	    }
    }

    /**
     *
     * @return array
     */
    public function get()
    {
        return self::$_Breadcrumbs;
    }

    /**
     *
     * @return array the last bradcrumb entry
     */
    public function removelast()
    {
        return array_pop( self::$_Breadcrumbs );
    }

    /**
     *
     * @return array the first bradcrumb entry
     */
    public function removefirst()
    {
        return array_shift( self::$_Breadcrumbs );
    }

    /**
     * insert content breakcrumbs to the root breakcrumbs
     * @param array $data
     */
    public function addContentBreakcrumb( $data )
    {
        
    }

    /**
     * remove content breakcrumb from root breakcrumbs
     *
     * @param integer $contentid
     * @return void
     */
    private function removeContentBreakcrumb( $contentid = 0 )
    {
        if ( !$contentid || !is_array( $this->Site->get( 'breadcrumbs' ) ) )
        {
            return;
        }

        foreach ( $this->Site->get( 'breadcrumbs' ) as $idx => $r )
        {
            if ( (defined( 'CONTROLLER' ) && $r[ 'contentid' ] === $contentid && $r[ 'controller' ] === CONTROLLER) || ($r[ 'contentid' ] === $contentid) )
            {
                unset( $this->Site->breadcrumbcache[ $idx ] );
                return;
            }
        }
    }

    /**
     *
     * @param integer $id
     * @return array
     */
    public function getNewsBreadcrumb( $id = 0 )
    {
        $result = Model::getModelInstance( 'news' )->getCategories();


        self::$_BreadcrumbCache = array();
        foreach ( $result as $r )
        {
            self::$_BreadcrumbCache[ $r[ 'id' ] ] = $r;
        }

        $parentlist = array();
        if ( $id && isset( self::$_BreadcrumbCache[ $id ] ) )
        {
            $parentlist[] = $id;
        }

        $parentlist = $this->getAllParentIds( $id, $parentlist );
        $parentlist = array_reverse( $parentlist );


        $navarray = array();
        foreach ( $parentlist AS $_id )
        {
            $this->removeContentBreakcrumb( $_id );
            $navarray[] = self::$_BreadcrumbCache[ $_id ];
        }

        // $this->Site->set('breadcrumbs', $navarray);


        return $navarray;
    }

    /**
     * returns an array of ALL parent ids for a given id($id)
     * @param integer $id
     * @param array $idarray
     * @return array
     */
    protected function getAllParentIds( $id, $idarray )
    {
        if ( !is_array( $idarray ) )
        {
            $idarray = array();
        }
        if ( !(int)$id  || !isset( self::$_BreadcrumbCache[ (int)$id  ] ) )
        {
            return $idarray;
        }

        $rs = self::$_BreadcrumbCache[ (int)$id  ];
        if ( !isset( $rs[ 'id' ] ) || empty( $rs[ 'parentid' ] ) )
        {
            return $idarray;
        }

        $idarray[] = $rs[ 'parentid' ];
        $idarray = $this->getAllParentIds( $rs[ 'parentid' ], $idarray );
        return $idarray;
    }

    /**
     * @param bool $addLink
     * @return bool
     */
    public function getBreadcrumbs( $addLink = true )
    {

        $this->load( 'Site' );
        $breadcrumbs = $this->Site->get( 'breadcrumbs' );

        if ( is_array( $breadcrumbs ) )
        {

            $cache = array();
            foreach ( $breadcrumbs as $r )
            {
                if ( ($r[ 'controller' ] === CONTROLLER && $r[ 'contentid' ] > 0) || $r[ 'type' ] === 'rootpage' )
                {
                    continue;
                }

                $cache[] = $r;
            }

            $i = 0;
            $total = count( $cache );
	        $frontpage = Settings::get('frontpage', '');

            if ( $total )
            {
                foreach ( $cache as $r )
                {

                    if ( $r[ 'controller' ] === CONTROLLER && $r[ 'contentid' ] > 0 )
                    {
                        #  $i++;
                        # continue;
                    }

                    $title = $r[ 'rootname' ];


                    if ( $r[ 'contentid' ] > 0 || (!$addLink && ($r[ 'appid' ] > 0 || $r[ 'contentid' ] > 0) && $r[ 'controller' ] === CONTROLLER) ||
                            (!$addLink && ($i + 1) === $total)
                    )
                    {
                        $r[ 'rootlink' ] = '';
                    }

	                if ( $frontpage && $i === 0 ) {

		                $frontpageNoDomain = preg_replace('#^'. preg_quote(Settings::get('portalurl') , '#') .'#is', '', $frontpage );

						if (preg_match('#'. preg_quote($frontpage, '#') . '#is', $r[ 'rootlink' ]) || preg_match('#'. preg_quote($frontpageNoDomain, '#') . '#is', $r[ 'rootlink' ]) ) {
							$i++;
							continue;
						}
	                }



                    if ( $total > $i )
                    {
                        $this->add( $title, $r[ 'rootlink' ] );
                    }
                    else
                    {
                        $this->add( $title, '' );
                    }
                    $i++;
                }
                return true;
            }
        }

        return false;
    }

}
