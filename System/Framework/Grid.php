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
 * @file         Grid.php
 */
class Grid extends Grid_Abstract
{

    /**
     * @var null
     */
    protected $dataTable = null;

    /**
     * @var null
     */
    protected $primaryKey = null;

    /**
     * @var string
     */
    protected $defaultSort = 'asc';

    /**
     * @var string
     */
    protected $defaultOrderby = '';

    /**
     * @var array
     */
    protected $dataRows = array();

    /**
     * @var array
     */
    protected $data = array();

    /**
     * @var null
     */
    protected $sendRedrawCol = null;

    /**
     * @var null
     */
    protected $gridActions = null;

    /**
     * @var null
     */
    protected $_renderedData = null;

    protected $defaultVisibleFields = array();

    protected $_labelColumn = false;

    protected $_gridColumnChangeVisible = false;

    protected $forceselectable = false;

    protected $gridDataUrl = null;

    protected $gridEvents = array();

    /**
     *
     */
    public function __construct()
    {

        parent::__construct();
        $this->load( 'Template' );
        //$this->Template->addScript(JS_URL . 'dcms.grid')->addScript(BACKEND_CSS_PATH . 'grid.css', true, '');
    }

    /**
     * init the grid
     * if not set the uiq string then will generate a uiq string based on (CONTROLLER,ACTION,isSeemodePopup,APPID)
     *
     * @param string $table
     * @param string $primaryKey
     * @param        $defaultorderby
     * @param string $defaultsort
     * @internal param string $defaultsortby
     * @internal param string $defaultorder
     * @return Grid
     */
    public function initGrid($table, $primaryKey, $defaultorderby, $defaultsort = 'asc')
    {


        $this->dataTable      = $table;
        $this->primaryKey     = $primaryKey;
        $this->defaultSort    = $defaultsort;
        $this->defaultOrderby = $defaultorderby;


        /**
         * redraw col
         */
        if ( HTTP::input( 'getcoldata' ) != '' )
        {
            $this->sendRedrawCol = HTTP::input( 'getcoldata' );
        }


        if ( HTTP::input( 'getGriddata' ) )
        {
            Library::skipDebug();
        }


        //
        $isSeemode = Cookie::get( 'isSeemodePopup' ) ? '-seemode' : null;

        if ( !$this->uniq )
        {
            $this->uniq = md5( $this->dataTable . CONTROLLER . ACTION . $isSeemode . ( !empty( $GLOBALS[ 'APPID' ] ) ? $GLOBALS[ 'APPID' ] : '' ) );
        }

        //
        // $pp = intval( HTTP::input( 'perpage' ) );

        $pp = intval( $this->getPerpage() );

        if ( $pp > 0 )
        {
            $GLOBALS[ 'perpage' ] = $pp;
        }

        if ( HTTP::input( 'orderby' ) != '' )
        {
            $GLOBALS[ 'orderby' ] = HTTP::input( 'orderby' );
        }

        if ( HTTP::input( 'sort' ) != '' )
        {
            $GLOBALS[ 'sort' ] = HTTP::input( 'sort' );
        }

        $this->gridSettings = array(
            'columns'        => '',
            'columnWidths'   => array(),
            'allcolumns'     => '',
            'visiblecolumns' => '',
            'perpage'        => $this->defaultPerpage,
            'sort'           => ( isset( $GLOBALS[ 'sort' ] ) ? $GLOBALS[ 'sort' ] : $this->defaultSort ),
            'orderby'        => ( isset( $GLOBALS[ 'orderby' ] ) ? $GLOBALS[ 'orderby' ] : $this->defaultOrderby )
        );


        $this->load( 'Personal' );
        $this->Personal->initSettings();
        $aOptions = $this->Personal->get( "list", $this->dataTable . '-' . $this->uniq );

        $this->gridSettings[ 'sort' ]           = ( isset( $aOptions[ 'sort' ] ) ? $aOptions[ 'sort' ] : $this->gridSettings[ 'sort' ] );
        $this->gridSettings[ 'orderby' ]        = ( isset( $aOptions[ 'orderby' ] ) ? $aOptions[ 'orderby' ] : $this->gridSettings[ 'orderby' ] );
        $this->gridSettings[ 'perpage' ]        = ( isset( $aOptions[ 'perpage' ] ) ? $aOptions[ 'perpage' ] : $this->gridSettings[ 'perpage' ] );
        $this->gridSettings[ 'filter' ]         = ( isset( $aOptions[ 'filter' ] ) ? $aOptions[ 'filter' ] : null );
        $this->gridSettings[ 'columns' ]        = ( isset( $aOptions[ 'columns' ] ) ? $aOptions[ 'columns' ] : $this->gridSettings[ 'columns' ] );
        $this->gridSettings[ 'visiblecolumns' ] = ( isset( $aOptions[ 'visiblecolumns' ] ) ? $aOptions[ 'visiblecolumns' ] : $this->gridSettings[ 'visiblecolumns' ] );
        //	$this->gridSettings[ 'columnWidths' ]   = ( isset( $aOptions[ 'columnWidths' ] ) ? $aOptions[ 'columnWidths' ] : $this->gridSettings[ 'columnWidths' ] );
        $this->gridSettings[ 'allcolumns' ] = ( isset( $aOptions[ 'allcolumns' ] ) ? $aOptions[ 'allcolumns' ] : $this->gridSettings[ 'allcolumns' ] );

        $this->visibleFields = ( isset( $this->gridSettings[ 'columns' ] ) ? explode( ',', $this->gridSettings[ 'columns' ] ) : array() );

        if ( trim( $this->gridSettings[ 'visiblecolumns' ] ) != '' )
        {
            $this->visibleFields = ( isset( $this->gridSettings[ 'visiblecolumns' ] ) ? explode( ',', $this->gridSettings[ 'visiblecolumns' ] ) : array() );
        }


        if ( HTTP::input( 'filter' ) )
        {
            $input = HTTP::input();
            unset( $input[ 'filter' ], $input[ '_' ], $input[ 'ajax' ], $input[ 'table' ], $input[ 'allfields' ], $input[ 'visiblefields' ], $input[ 'getGriddata' ], $input[ 'page' ], $input[ 'getcoldata' ] );
            $this->gridSettings[ 'filter' ] = $input;
        }
        else
        {

        }

        if ( HTTP::input( 'removefilter' ) )
        {
            $this->gridSettings[ 'filter' ] = array();
        }


        if ( is_array( $this->gridSettings[ 'filter' ] ) )
        {
            foreach ( $this->gridSettings[ 'filter' ] as $key => $value )
            {
                HTTP::setinput( $key, $value );
            }
        }


        if ( !empty( $GLOBALS[ 'perpage' ] ) && intval( $GLOBALS[ 'perpage' ] ) > 0 )
        {
            //$GLOBALS['perpage'] = intval( $_SESSION["perpage"][$uniq_pp] );
            $_SESSION[ 'perpage' ][ $this->uniq ] = intval( $GLOBALS[ 'perpage' ] );
        }
        elseif ( !empty( $_SESSION[ 'perpage' ][ $this->uniq ] ) && intval( $_SESSION[ "perpage" ][ $this->uniq ] ) > 0 )
        {
            $GLOBALS[ 'perpage' ] = intval( $_SESSION[ "perpage" ][ $this->uniq ] );
        }
        else
        {
            $GLOBALS[ 'perpage' ]                 = ( isset( $aOptions[ "perpage" ] ) && intval( $aOptions[ "perpage" ] ) > 0 ? $aOptions[ "perpage" ] : $this->defaultPerpage );
            $_SESSION[ "perpage" ][ $this->uniq ] = $GLOBALS[ 'perpage' ];
        }

        // Sort (asc/desc)
        if ( isset( $GLOBALS[ 'sort' ] ) && !empty( $GLOBALS[ 'sort' ] ) )
        {
            $_SESSION[ "SORT" ][ $this->uniq ] = $GLOBALS[ 'sort' ];
        }
        else if ( isset( $_SESSION[ "SORT" ][ $this->uniq ] ) && !empty( $_SESSION[ "SORT" ][ $this->uniq ] ) )
        {
            $GLOBALS[ 'sort' ] = $_SESSION[ "SORT" ][ $this->uniq ];
        }
        else
        {
            if ( isset( $aOptions[ 'sort' ] ) && !empty( $aOptions[ "sort" ] ) )
            {
                $GLOBALS[ 'sort' ] = $aOptions[ "sort" ];
            }
            else
            {
                $GLOBALS[ 'sort' ] = $this->defaultSort;
            }
        }

        // OrderBY
        if ( isset( $GLOBALS[ 'orderby' ] ) && !empty( $GLOBALS[ 'orderby' ] ) )
        {
            $_SESSION[ "ORDER" ][ $this->uniq ] = $GLOBALS[ 'orderby' ];
        }
        else if ( isset( $_SESSION[ "ORDER" ][ $this->uniq ] ) && !empty( $_SESSION[ "ORDER" ][ $this->uniq ] ) )
        {
            $GLOBALS[ 'orderby' ] = $_SESSION[ "ORDER" ][ $this->uniq ];
        }
        else
        {
            if ( !empty( $aOptions[ "order" ] ) )
            {
                $GLOBALS[ 'orderby' ] = $aOptions[ "order" ];
            }
            else
            {
                $GLOBALS[ 'orderby' ] = $this->defaultOrderby;
            }
        }


        $this->perpage = $GLOBALS[ 'perpage' ];

        HTTP::setinput( 'perpage', $GLOBALS[ 'perpage' ] );
        HTTP::setinput( 'sort', $GLOBALS[ 'sort' ] );
        HTTP::setinput( 'orderby', $GLOBALS[ 'orderby' ] );

        return $this;
    }


    /**
     * @param string $url
     * @return Grid
     */
    public function setGridDataUrl($url)
    {

        $this->gridDataUrl = $url;

        return $this;
    }

    /**
     * @param string $eventName the name of the Event
     * @param string $functionCall the Javascript string
     * @return Grid
     */
    public function addGridEvent($eventName, $functionCall)
    {

        $this->gridEvents[ $eventName ] = $functionCall;

        return $this;
    }
    /**
     * Enable force row selectable
     *
     * @return Grid
     */
    public function enableForceSelectable()
    {

        $this->forceselectable = true;

        return $this;
    }
    /**
     * disable force row selectable
     *
     * @return Grid
     */
    public function disableForceSelectable()
    {
        $this->forceselectable = false;
        return $this;
    }
    /**
     * Enable Column Visible Toggle
     *
     * @return Grid
     */
    public function enableColumnVisibleToggle()
    {

        $this->_gridColumnChangeVisible = true;

        return $this;
    }

    /**
     * Disable Column Visible Toggle
     *
     * @return Grid
     */
    public function disableColumnVisibleToggle()
    {

        $this->_gridColumnChangeVisible = false;

        return $this;
    }


    /**
     *
     * @param array $arr
     * @return Grid
     */
    public function addHeader($arr = array())
    {

        $defaultorder = array();
        foreach ( $arr as $r )
        {
            $defaultorder[ ] = $r[ 'field' ];
        }


        // fix ordering
        if ( !in_array( $GLOBALS[ 'orderby' ], $defaultorder ) )
        {
            $GLOBALS[ 'orderby' ]            = $this->defaultOrderby;
            $this->gridSettings[ 'orderby' ] = $GLOBALS[ 'orderby' ];

            if ( is_string( $this->uniq ) && !empty( $this->uniq ) )
            {
                $_SESSION[ "ORDER" ][ $this->uniq ] = $GLOBALS[ 'orderby' ];
            }
        }


        $defaultvisible  = array();
        $labelColumnName = false;
        foreach ( $arr as $r )
        {
            if ( isset( $r[ 'default' ] ) && $r[ 'default' ] )
            {
                $defaultvisible[ ] = $r[ 'field' ];
            }

            if ( isset( $r[ 'islabel' ] ) && $r[ 'islabel' ] )
            {
                $labelColumnName = $r[ 'field' ];
            }
        }

        $this->_labelColumn         = $labelColumnName;
        $this->defaultVisibleFields = $defaultvisible;

        /**
         * Reorder Cols by persional settings
         */
        if ( is_array( $this->visibleFields ) && count( $this->visibleFields ) )
        {
            $tmp = array();
            foreach ( $this->visibleFields as $order => $fieldname )
            {
                if ( !$fieldname )
                {
                    continue;
                }

                foreach ( $arr as $x => $r )
                {
                    if ( $r[ 'field' ] == $fieldname )
                    {
                        $tmp[ ] = $r[ 'field' ];
                    }
                }
            }

            $this->visibleFields             = $tmp;
            $this->gridSettings[ 'columns' ] = implode( ',', $tmp );
        }
        else
        {
            $this->visibleFields             = $this->defaultVisibleFields;
            $this->gridSettings[ 'columns' ] = implode( ',', $this->defaultVisibleFields );
        }


        /**
         * update from ajax
         */
        if ( HTTP::post( 'saveColumns' ) )
        {

            $allfields  = HTTP::post( 'allfields' );
            $widths     = HTTP::post( 'widths' ); // alle breiten
            $_width     = explode( ',', trim( $widths ) );
            $_allfields = explode( ',', trim( $allfields ) );

            $cache = array();
            foreach ( $_allfields as $idx => $name )
            {
                if ( $name && isset( $_width[ $idx ] ) )
                {
                    $cache[ $name ] = $_width[ $idx ];
                }
            }

            $visiblefields = HTTP::post( 'visiblefields' );

            $this->gridSettings[ 'allcolumns' ]     = $allfields;
            $this->gridSettings[ 'columnWidths' ]   = $cache;
            $this->gridSettings[ 'visiblecolumns' ] = $visiblefields;

            $this->load( 'Personal' );
            $this->Personal->set( 'list', $this->dataTable . '-' . $this->uniq, $this->gridSettings );


            Library::sendJson( true );
        }


        // Update the grid settings
        $this->Personal->set( 'list', $this->dataTable . '-' . $this->uniq, $this->gridSettings );
        $this->headers = $arr;


        return $this;
    }

    /**
     *
     */
    public function freeMem()
    {

        $this->dataRows = null;
        $this->headers  = null;
    }

    /**
     *
     * @param array $data
     * @return Grid_Row
     */
    public function addRow($data = array())
    {

        $row                  = new Grid_Row( $this->headers );
        $row->primaryKey      = $this->primaryKey;
        $row->primaryKeyValue = $data[ $this->primaryKey ];
        $row->prepare( $data );
        $this->dataRows[ ] = & $row;

        return $row;
    }

    /**
     *
     */
    public function changeGridCols()
    {

        if ( !is_array( $this->visibleFields ) || !count( $this->visibleFields ) )
        {

        }
    }

    /**
     *
     * @param int $totalRows
     * @return array
     */
    public function renderData($totalRows = 0)
    {

        $data = array();


        if ( $this->sendRedrawCol && $this->sendRedrawCol != '' )
        {
            $data[ 'success' ] = true;
            $data[ 'rows' ]    = $this->prepareDataRows();

            /**
             * update grid settings
             */
            $this->Personal->set( 'list', $this->dataTable . '-' . $this->uniq, $this->gridSettings );


            echo Library::json( $data );
            exit;
        }

        if ( HTTP::input( 'getGriddata' ) )
        {
            $data[ 'rows' ]      = $this->prepareDataRows();
            $this->_renderedData = $data;
            $data                = null;

            return $this->_renderedData;
        }


        $data[ 'total' ]         = intval( $totalRows );
        $data[ 'perpage' ]       = $GLOBALS[ 'perpage' ];
        $data[ 'table' ]         = $this->dataTable;
        $data[ 'key' ]           = $this->primaryKey;
        $data[ 'orderby' ]       = $GLOBALS[ 'orderby' ];
        $data[ 'sort' ]          = $GLOBALS[ 'sort' ];
        $data[ 'defaultCols' ]   = $this->defaultFields;
        $data[ 'availebleCols' ] = $this->availebleFields;
        $data[ 'visibleCols' ]   = ( !count( $this->visibleFields ) ? $this->defaultFields : $this->visibleFields );
        $data[ 'labelColumn' ]   = $this->_labelColumn ? $this->_labelColumn : '';
        $data[ 'url' ]           = $this->gridDataUrl;
        $data[ 'activeFilter' ]  = is_array( $this->gridSettings[ 'filter' ] ) ? $this->gridSettings[ 'filter' ] : array();
        $data[ 'currentFilter' ] = $data[ 'activeFilter' ];

        if ( is_array( $this->gridActions ) && count( $this->gridActions ) )
        {
            $data[ 'gridActions' ] = array();
            foreach ( $this->gridActions as $key => $label )
            {
                if ( is_array( $label ) )
                {
                    $_data = array(
                        'adm' => $key
                    );
                    $tmp   = array();
                    foreach ( $label as $k => $v )
                    {
                        $tmp[ $k ] = $v;
                    }


                    $data[ 'gridActions' ][ ] = array_merge( $tmp, $_data );
                }
                else
                {
                    if ( $label )
                    {
                        $data[ 'gridActions' ][ ] = array(
                            'adm'   => $key,
                            'label' => $label
                        );
                    }
                }
            }
        }


        $data[ 'rows' ]        = $this->prepareDataRows();
        $data[ 'datarows' ]    = $data[ 'rows' ];
        $data[ 'colModel' ]    = $this->prepareHeaders();
        $data[ 'searchitems' ] = $this->prepareFilter();
        //$data[ 'events' ]                    = $this->gridEvents;
        $data[ 'enableColumnVisibleToggle' ] = $this->_gridColumnChangeVisible;
        $data[ 'forceselectable' ] = $this->forceselectable;

        if ( is_array( $this->gridEvents ) )
        {
            foreach ( $this->gridEvents as $name => $function )
            {
                $data[ $name ] = trim( preg_replace( '#function\s*\(#is', 'function(', $function ) );
            }
        }

        // prepare json function strings
        $value_arr    = array();
        $replace_keys = array();
        if ( is_array($data)) {
            foreach ( $data as $key => &$value )
            {
                // Look for values starting with 'function('
                if ( (is_string($value ) && strpos( $value, 'function(' ) === 0) || isset( $this->gridEvents[ $key ] ) )
                {
                    // Store function string.
                    $value_arr[ ] = $value;

                    // Replace function string in $foo with a 'unique' special key.
                    $value = '%' . $key . '%';

                    // Later on, we'll look for the value, and replace it.
                    $replace_keys[ ] = '"' . $value . '"';
                }
            }
        }

        //
        Json::$useBuiltinEncoderDecoder = false;

        $data[ 'gridJson' ] = Json::encode( $data );

        // convert json functions
        $data[ 'gridJson' ] = str_replace( $replace_keys, $value_arr, $data[ 'gridJson' ] );

        $this->Template->assign( 'gridJson', $data[ 'gridJson' ] );


        $this->_renderedData = $data;

        unset( $data );

        /**
         * update grid settings
         */
        $this->Personal->set( 'list', $this->dataTable . '-' . $this->uniq, $this->gridSettings );

        return $this->_renderedData;
    }

    /**
     *
     * @return array
     */
    private function prepareFilter()
    {

        if ( is_array( $this->filters ) )
        {
            $activeFilter = $this->gridSettings[ 'filter' ];
            if ( !is_array( $activeFilter ) )
            {
                $activeFilter = array();
            }


            $data = array();
            foreach ( $this->filters as $r )
            {
                $code = '';

                if ( isset( $activeFilter[ $r[ 'name' ] ] ) )
                {
                    // $r['value'] = $activeFilter[$r['name']];
                    HTTP::setinput( $r[ 'name' ], $r[ 'value' ] );
                }


                if ( isset( $r[ 'separator' ] ) && $r[ 'separator' ] )
                {
                    $r[ 'type' ] = 'separator';
                }
                elseif ( isset( $r[ 'wrap' ] ) && $r[ 'wrap' ] )
                {
                    $r[ 'type' ] = 'wrap';
                }
                else
                {

                    $code = '<div class="filter-item">';


                    switch ( $r[ 'type' ] )
                    {

                        case 'html':
                            $code .= '<div class="filter-label">'.$r[ 'label' ] . '</div><div class="filter-control">' . $r[ 'code' ] .'</div>';
                            break;
                        case 'input':
                            $code .= '<div class="filter-label">'.$r[ 'label' ] . '</div><div class="filter-control"><input type="text" name="' . $r[ 'name' ] . '" value="' . htmlspecialchars( $r[ 'value' ] ) . '" /></div>';
                            break;

                        case 'hidden':
                            $code .= '<input type="hidden" name="' . $r[ 'name' ] . '" value="' . htmlspecialchars( $r[ 'value' ] ) . '" />';

                            break;

                        case 'checkbox':
                            $selected     = HTTP::input( $r[ 'name' ] );
                            $r[ 'value' ] = (string)$r[ 'value' ];

                            $sel = ( ( $selected != '' && $selected == $r[ 'value' ] ) ? ' checked="checked"' : '' );


                            $code .= '<div class="filter-control-checkbox"><label for="cbk-' . $r[ 'name' ] . '">
                                    <input type="checkbox" id="cbk-' . $r[ 'name' ] . '" name="' . $r[ 'name' ] . '" value="' . htmlspecialchars( $r[ 'value' ] ) . '"' . ( isset( $r[ 'checked' ] ) && $r[ 'checked' ] ? ' checked="checked"' : $sel ) . ' />
                                        ' . $r[ 'label' ] . '</label></div>';

                            break;

                        case 'select':


                            $code .= '<div class="filter-label">'.$r[ 'label' ] . '</div><div class="filter-control"><select name="' . $r[ 'name' ] . '">';
                            if ( is_array( $r[ 'select' ] ) )
                            {
                                $selected = HTTP::input( $r[ 'name' ] );

                                foreach ( $r[ 'select' ] as $key => $value )
                                {
                                    $sel = ( ( $selected != '' && $selected == $key ) ? ' selected="selected"' : '' );
                                    $code .= '<option value="' . $key . '"' . $sel . '>' . $value . '</option>';
                                }
                            }

                            $code .= '</select></div>';
                            break;
                    }


                    $code .= '</div>';
                }


                $data[ ] = array(
                    'htmlcode' => $code,
                    'type'     => $r[ 'type' ]
                );
            }


            return $data;
        }

        return array();
    }

    /**
     * @param $name
     */
    private function removeHeaderColumn($name)
    {
        if ( is_array( $this->headers ) )
        {
            foreach ( $this->headers as $idx => &$r )
            {
                if ( $r[ 'field' ] == $name )
                {
                    unset( $this->headers[ $idx ] );
                    break;
                }
            }
        }
    }

    /**
     * @param $name
     * @return array
     */
    private function &getHeaderColumn($name)
    {
        if ( is_array( $this->headers ) )
        {
            foreach ( $this->headers as $idx => $r )
            {
                if ( $r[ 'field' ] == $name )
                {
                    $ret =& $r;
                    return $ret;
                }
            }
        }
        else {
            $ret = array();
            return $ret;
        }
    }

    private function getPersonalGridOptions()
    {

        $useModel   = explode( ',', $this->gridSettings[ 'visiblecolumns' ] );
        $useModel   = Library::unempty( $useModel );
        $allColumns = explode( ',', $this->gridSettings[ 'allcolumns' ] );
        $allColumns = Library::unempty( $allColumns );
    }

    /**
     *
     * @return array
     */
    private function prepareHeaders()
    {

        if ( is_array( $this->headers ) )
        {

            foreach ( $this->headers as $idx => $r )
            {
                $this->availebleFields[ ] = array(
                    'label' => trim( $r[ 'content' ] ),
                    'name'  => $r[ 'field' ]
                );
            }

            $data       = array();
            $useModel   = explode( ',', $this->gridSettings[ 'visiblecolumns' ] );
            $useModel   = Library::unempty( $useModel );
            $allColumns = explode( ',', $this->gridSettings[ 'allcolumns' ] );
            $allColumns = Library::unempty( $allColumns );

            if ( count( $allColumns ) && count( $useModel ) )
            {
                $defaultFields = array();
                if ( count( $useModel ) )
                {
                    foreach ( $useModel as $value )
                    {
                        if ( $value )
                        {
                            $defaultFields[ ] = $value;
                        }
                    }
                }
                else
                {
                    $defaultFields = $this->defaultVisibleFields;
                }


                $this->defaultFields        = $defaultFields;
                $this->defaultVisibleFields = $defaultFields;

                if ( !count( $this->visibleFields ) )
                {
                    $this->visibleFields = $this->defaultFields;
                }


                foreach ( $allColumns as $name )
                {
                    if ( $name )
                    {
                        $r =& $this->getHeaderColumn( $name );

                        $tmp                = array();
                        $tmp[ 'isvisible' ] = true;
                        if ( !in_array( $r[ 'field' ], $defaultFields )
                        )
                        {
                            $tmp[ 'isvisible' ] = false;
                        }

                        $sortable = ( isset( $r[ 'sort' ] ) && !empty( $r[ 'sort' ] ) ? true : false );
                        if ( $sortable )
                        {
                            $this->sortbyForFilters[ ] = array(
                                'label' => trim( $r[ 'content' ] ),
                                'field' => $r[ 'field' ]
                            );
                            $tmp[ 'sortable' ]         = true;
                        }


                        if ( isset( $r[ 'forcevisible' ] ) )
                        {
                            $tmp[ 'forcevisible' ] = $r[ 'forcevisible' ];
                        }

                        if ( isset( $r[ 'fixedwidth' ] ) )
                        {
                            $tmp[ 'fixedwidth' ] = $r[ 'fixedwidth' ];
                        }

                        $tmp[ 'sortby' ] = $sortable ? $r[ 'sort' ] : false;
                        $tmp[ 'label' ]  = trim( $r[ 'content' ] );
                        $tmp[ 'name' ]   = $r[ 'field' ];
                        $tmp[ 'align' ]  = isset( $r[ 'align' ] ) && !empty( $r[ 'align' ] ) ? $r[ 'align' ] : '';

                        $width = ( isset( $r[ 'width' ] ) && $r[ 'width' ] ) ? $r[ 'width' ] : 'auto';

                        if ( isset( $this->gridSettings[ 'columnWidths' ][ $r[ 'field' ] ] ) && intval( $this->gridSettings[ 'columnWidths' ][ $r[ 'field' ] ] ) )
                        {
                            $width = $this->gridSettings[ 'columnWidths' ][ $r[ 'field' ] ];
                        }



                        $tmp[ 'width' ] = $width;
                        $tmp[ 'type' ]  = isset( $r[ 'type' ] ) && !empty( $r[ 'type' ] ) ? $r[ 'type' ] : '';

                        // defaultfield ?
                        $tmp[ 'isdefault' ] = isset( $r[ 'default' ] ) ? $r[ 'default' ] : false;


                        $data[ ] = $tmp;
                    }
                }
            }
            else
            {


                $this->defaultFields = $this->defaultVisibleFields;

                if ( !count( $this->visibleFields ) )
                {
                    $this->visibleFields = $this->defaultFields;
                }


                foreach ( $this->headers as $idx => $r )
                {
                    $tmp                = array();
                    $tmp[ 'isvisible' ] = true;
                    if ( ( !in_array( $r[ 'field' ], $this->defaultFields ) && !in_array( $r[ 'field' ], $this->visibleFields ) ) || in_array( $r[ 'field' ], $this->defaultFields ) && !in_array( $r[ 'field' ], $this->visibleFields )
                    )
                    {
                        $tmp[ 'isvisible' ] = false;
                    }


                    $sortable = ( isset( $r[ 'sort' ] ) && !empty( $r[ 'sort' ] ) ? true : false );
                    if ( $sortable )
                    {
                        $this->sortbyForFilters[ ] = array(
                            'label' => trim( $r[ 'content' ] ),
                            'field' => $r[ 'field' ]
                        );
                        $tmp[ 'sortable' ]         = true;
                    }

                    $tmp[ 'sortby' ] = $sortable ? $r[ 'sort' ] : false;
                    $tmp[ 'label' ]  = trim( $r[ 'content' ] );
                    $tmp[ 'name' ]   = $r[ 'field' ];
                    $tmp[ 'align' ]  = isset( $r[ 'align' ] ) && !empty( $r[ 'align' ] ) ? $r[ 'align' ] : '';

                    $width = ( isset( $r[ 'width' ] ) && $r[ 'width' ] ) ? $r[ 'width' ] : 'auto';
                    if ( isset( $this->gridSettings[ 'columnWidths' ][ $r[ 'field' ] ] ) && intval( $this->gridSettings[ 'columnWidths' ][ $r[ 'field' ] ] ) )
                    {
                        $width = $this->gridSettings[ 'columnWidths' ][ $r[ 'field' ] ];
                    }

                    $tmp[ 'width' ] = $width;
                    $tmp[ 'type' ]  = isset( $r[ 'type' ] ) && !empty( $r[ 'type' ] ) ? $r[ 'type' ] : '';

                    // defaultfield ?
                    $tmp[ 'isdefault' ] = isset( $r[ 'default' ] ) ? $r[ 'default' ] : false;


                    $data[ ] = $tmp;
                }
            }

            return $data;
        }

        return array();
    }

    /**
     *
     * @return array
     */
    private function prepareDataRows()
    {

        $data = array();
        foreach ( $this->dataRows as $row => &$rk )
        {
            $rs  = $rk->getFieldData( $this->headers );
            $tmp = ( !$this->sendRedrawCol ? array() : '' );


            //
            if ( $rs === false )
            {
                continue;
            }


            foreach ( $rs as $key => $dat )
            {
                if ( $this->sendRedrawCol && $this->sendRedrawCol == $key )
                {
                    $data[ ] = $dat[ 'data' ];
                }
                else
                {

                    $tmp[ $key ] = $dat;
                }
            }

            if ( $this->_labelColumn )
            {

                if ( isset( $tmp[ $this->_labelColumn ] ) && isset( $tmp[ 'published' ][ 'data' ] ) && trim( $tmp[ 'published' ][ 'data' ] ) )
                {

                    preg_match_all( '#<a([^>]*)onclick=(["\'])([^\2]*)\2([^>]*)>(.*)</a>#isU', $tmp[ 'published' ][ 'data' ], $match, PREG_SET_ORDER );

                    if ( isset( $match[ 0 ] ) )
                    {
                        foreach ( $match as $idx => $s )
                        {

                            $url = $match[ $idx ][ 3 ];

                            if ( stripos( $url, 'changePublish' ) === false )
                            {
                                continue;
                            }

                            $label = false;
                            preg_match( '#title=(["\'])([^\1]*)\1#isU', $match[ $idx ][ 0 ], $m );
                            if ( !$m[ 2 ] )
                            {
                                preg_match( '#alt=(["\'])([^\1]*)\1#isU', $match[ $idx ][ 0 ], $m );
                                if ( !$m[ 2 ] )
                                {
                                    if ( trim( strip_tags( $m[ 5 ] ) ) )
                                    {
                                        $label = trim( strip_tags( $m[ 5 ] ) );
                                    }
                                    else
                                    {
                                        continue;
                                    }
                                }
                                else
                                {
                                    $label = trim( $m[ 2 ] );
                                }
                            }
                            else
                            {
                                $label = trim( $m[ 2 ] );
                            }


                            $isPublished = true;
                            preg_match( '#src=(["\'])([^\1]*)\1#isU', $match[ $idx ][ 5 ], $m );
                            if ( $m[ 2 ] )
                            {
                                if ( strpos( $m[ 2 ], 'online' ) !== false )
                                {
                                    $isPublished = 1;
                                }
                                else if ( strpos( $m[ 2 ], 'clock' ) !== false )
                                {
                                    $isPublished = 2;
                                }
                                else
                                {
                                    $isPublished = 0;
                                }
                            }


                            $type = 'publish';


                            // trim url
                            preg_match( '#,(["\'])([^\1]*)\1#is', $url, $m );
                            $url = $m[ 2 ];

                            $tmp[ $this->_labelColumn ][ 'actions' ][ ] = array(
                                'label'     => $label,
                                'url'       => $url,
                                'type'      => $type,
                                'published' => $isPublished
                            );
                        }

                        if ( $type === 'publish' )
                        {
                            $this->removeHeaderColumn( 'published' );
                            unset( $tmp[ 'published' ], $c );
                        }
                    }
                    else
                    {
                        // draft mode
                        $tmp[ $this->_labelColumn ][ 'actions' ][ ] = array(
                            'isdraft' => 1
                        );
                        $this->removeHeaderColumn( 'published' );
                        unset( $tmp[ 'published' ], $c );
                    }
                }


                if ( isset( $tmp[ $this->_labelColumn ] ) && isset( $tmp[ 'options' ][ 'data' ] ) && trim( $tmp[ 'options' ][ 'data' ] ) )
                {

                    preg_match_all( '#<a([^>]*)href\s*=\s*(["\'])([^\2]*)\2([^>]*)>(.*)</a>#isU', $tmp[ 'options' ][ 'data' ], $match, PREG_SET_ORDER );

                    if ( isset( $match[ 0 ] ) )
                    {

                        foreach ( $match as $idx => $s )
                        {
                            $icon       = $match[ $idx ][ 5 ];
                            $url        = $match[ $idx ][ 3 ];
                            $full       = $match[ $idx ][ 0 ];
                            $isDisabled = false;
                            $isDownload = false;
                            $isAjaxOnly = false;

                            if ( preg_match( '#class\s*=\s*(["\'])([^\1]*)disabled([^\1]*)\1#isU', $full ) )
                            {
                                $isDisabled = true;
                            }


                            if ( preg_match( '#class\s*=\s*(["\'])([^\1]*)ajax([^\1]*)\1#isU', $full ) )
                            {
                                $isAjaxOnly = true;
                            }


                            preg_match( '#class\s*=\s*(["\'])([^\1]*)(download|dl)([^\1]*)\1#isU', $full, $m );
                            if ( isset($m[ 3 ]) && ($m[ 3 ] === 'download' || $m[ 3 ] === 'dl') )
                            {
                                $isDownload = true;
                            }


                            if ( !empty( $icon ) )
                            {
                                $label = false;
                                preg_match( '#title\s*=\s*(["\'])([^\1]*)\1#isU', $icon, $m );
                                if ( !isset($m[ 2 ]) || empty($m[ 2 ]) )
                                {
                                    preg_match( '#alt\s*=\s*(["\'])([^\1]*)\1#isU', $icon, $m );
                                    if ( !isset($m[ 2 ]) || empty($m[ 2 ]) )
                                    {
                                        if ( isset($m[ 5 ]) && trim( strip_tags( $m[ 5 ] ) ) )
                                        {
                                            $label = trim( strip_tags( $m[ 5 ] ) );
                                        }
                                        else
                                        {
                                            if ( trim( $icon ) )
                                            {
                                                $label = trim( $icon );
                                            }
                                            else
                                            {
                                                continue;
                                            }


                                        }
                                    }
                                    else
                                    {
                                        $label = trim( $m[ 2 ] );
                                    }
                                }
                                else
                                {
                                    $label = isset( $m[ 2 ] ) ? trim( $m[ 2 ] ) : $icon;
                                }
                            }

                            if ( empty( $label ) )
                            {
                                continue;
                            }

                            $type = false;
                            if ( strpos( $url, '=edit' ) !== false || strpos( $url, 'edit=1' ) !== false )
                            {
                                $type = 'edit';
                            }
                            elseif ( strpos( $url, '=uninstall' ) !== false )
                            {
                                $type  = 'uninstall';
                                $label = trans( 'Deinstallieren' );
                            }
                            elseif ( strpos( $url, '=execute' ) !== false )
                            {
                                $type  = 'execute';
                                $label = trans( 'Ausführen' );
                            }
                            elseif ( strpos( $url, '=delete' ) !== false || strpos( $url, '=remove' ) !== false )
                            {
                                $type = 'delete';
                            }
                            elseif ( strpos( $url, 'action=download' ) !== false || strpos( $url, '=download' ) !== false )
                            {
                                $type  = 'download';
                                $label = ( $isDownload === true ? trans( 'Download' ) : $label );
                            }
                            elseif ( strpos( $url, 'action=index' ) === false && preg_match( '#action=([a-z0-9_\-]+?)#isU', $url, $ac ) )
                            {
                                $type  = $ac[ 1 ];
                                //$label = $label;
                            }


                            // trim url
                            $url = preg_replace( '#.*\?(.*)#U', '$1', $url );

                            $tmp[ $this->_labelColumn ][ 'actions' ][ ] = array('label' => $label, 'url' => $url, 'type' => $type, 'ajax' => $isAjaxOnly, 'disabled' => $isDisabled);
                        }
                    }

                    if ( $type !== false )
                    {
                        $this->removeHeaderColumn( 'options' );
                        unset( $tmp[ 'options' ] );
                    }
                }
            }


            if ( !$this->sendRedrawCol )
            {
                if ( !isset( $tmp[ $this->primaryKey ] ) )
                {
                    $tmp[ $this->primaryKey ] = $rk->primaryKeyValue;
                }

                $data[ ] = $tmp;
            }
        }

        //  print_r($data); die($label) ; exit;


        return $data;
    }

    /**
     *
     * @param integer $total
     * @return array
     */
    public function getJsonData($total = 0)
    {


        $this->_renderedData[ 'sort' ]         = $GLOBALS[ 'sort' ];
        $this->_renderedData[ 'orderby' ]      = $GLOBALS[ 'orderby' ];
        $this->_renderedData[ 'total' ]        = $total;
        $this->_renderedData[ 'perpage' ]      = $GLOBALS[ 'perpage' ];
        $this->_renderedData[ 'searchitems' ]  = Json::encode( $this->_renderedData[ 'searchitems' ] );
        $this->_renderedData[ 'colModel' ]     = Json::encode( $this->_renderedData[ 'colModel' ] );
        $this->_renderedData[ 'gridActions' ]  = Json::encode( $this->_renderedData[ 'gridActions' ] );
        $this->_renderedData[ 'activeFilter' ] = Json::encode( $this->_renderedData[ 'activeFilter' ] );
        $this->_renderedData[ 'labelColumn' ]  = $this->_labelColumn ? $this->_labelColumn : '';


        $this->_renderedData[ 'datarows' ] = Json::encode( $this->prepareDataRows() );

        return $this->_renderedData;
    }

}
