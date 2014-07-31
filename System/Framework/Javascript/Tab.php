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
 * @file        Tab.php
 *
 */
class Javascript_Tab
{

    /**
     * @var Javascript
     */
    private $_javascript;

    /**
     * @var array
     */
    private $_tabs = array();

    /**
     * @var null
     */
    private $tab_js_code = null;

    /**
     * @var int
     */
    private $_totalTabs = 0;

    /**
     * @var null
     */
    private $_selectedTab = null;

    /**
     * @param Javascript $_javascript
     */
    public function __construct( Javascript $_javascript )
    {
        $this->_javascript = $_javascript;

        return $this;
    }

    /**
     *
     * @param string $label
     * @param bool $isDefaultTab default is false
     * @return Javascript_Tab
     */
    public function addTab( $label, $isDefaultTab = false )
    {
        if ( $isDefaultTab && $this->_selectedTab === null )
        {
            $this->_selectedTab = true;
        }

        if ( is_array( $label ) )
        {
            foreach ( $label as $l )
            {
                if ( $l )
                {
                    $this->_tabs[] = array(
                        $l,
                        $isDefaultTab );
                }
            }

            return $this;
        }

        if ( $label )
        {
            $this->_tabs[] = array(
                $label,
                $isDefaultTab );
        }

        return $this;
    }

    private function startTab()
    {
        $this->tab_js_code = <<<EOF

<!-- tab_controll -->
<div class="tabcontainer">
	<div class="tabHeader">
		<ul class="tabbedMenu">
EOF;
    }

    private function endTab()
    {
        $this->tab_js_code .= <<<EOF
		</ul>
	</div>
    <div class="tabs_bgfooter"></div>
</div>

<!-- / tab_controll -->
EOF;
    }

    /**
     *
     * @param string $caption
     * @param integer $id
     */
    private function add_selected_tab_item( $caption, $id = 0 )
    {
        $this->tab_js_code .= <<<EOF
	<li id="tab_$id" class="actTab">
		<span>{$caption}</span>
	</li>
EOF;
    }

    /**
     *
     * @param string $caption
     * @param integer $id
     */
    private function add_tab_item( $caption, $id = 0 )
    {
        $this->tab_js_code .= <<<EOF
	<li id="tab_$id" class="defTab">
		<span>{$caption}</span>
	</li>
EOF;
    }

    /**
     *
     * @return Javascript_Tab
     */
    public function create()
    {
        $this->startTab();

        $x = 0;
        $defaultIsSet = false;
        foreach ( $this->_tabs as $row )
        {

            if ( $this->_selectedTab === null )
            {
                $row[ 1 ] = true;
            }


            if ( $row[ 1 ] === true && !$defaultIsSet )
            {
                $this->add_selected_tab_item( $row[ 0 ], $x++ );
                $defaultIsSet = true;
            }
            else
            {
                $this->add_tab_item( $row[ 0 ], $x++ );
            }
        }

        $this->endTab();

        $this->_totalTabs = sizeof( $this->_tabs );

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getScript()
    {
        if ( defined( 'ADM_SCRIPT' ) && ADM_SCRIPT )
        {
            return '';
        }

        return '<script type="text/javascript">if (typeof init_tabs != "function" ) { Loader.require(\'' . JS_URL . 'dcms_tabs.js\', function () {
            init_tabs();
}); }else { init_tabs(); }</script>';
    }

    /**
     *
     * @return string
     */
    public function getHtml()
    {
        return $this->tab_js_code;
    }

}

?>