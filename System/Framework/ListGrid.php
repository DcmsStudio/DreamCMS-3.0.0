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
 * @file        ListGrid.php
 *
 */
class ListGrid
{

    private $rowInstance = null;

    private $rows = array();

    public $aHeaders = array();

    public $addCheckAll = false;

	protected $labelColumn = false;

	private $removedHeaders = array();

    /**
     *
     * @param boolean $addCheckAll
     */
    public function __construct( $addCheckAll )
    {
        $this->addCheckAll = $addCheckAll;
    }

    /**
     *
     * @param array $headers
     */
    public function addheader( array $headers )
    {

        foreach ( $headers as $params )
        {

	        if ($params[ 'islabel' ] && !$this->labelColumn) {
				$this->labelColumn = $params[ 'field' ];
	        }

            $this->aHeaders[ $params[ 'field' ] ] = $params;
        }

        $this->rowInstance = new ListGrid_Row( $this->aHeaders, $this->addCheckAll, $this->labelColumn );
    }

    /**
     *
     * @param bool|int $id
     * @param array $arRes
     * @param boolean $link
     * @param boolean $title
     * @throws BaseException
     * @return \ListGrid_Row
     */
    public function &addRow( $id = false, $arRes = array(), $link = false, $title = false )
    {
        if ( is_null( $this->rowInstance ) )
        {
            throw new BaseException( 'Please add first the header of the table before add a new row!' );
        }

        $this->rowInstance->id = $id;
        $this->rowInstance->rowData = $arRes;


	    $this->removedHeaders = $this->rowInstance->getRemovedColumns();

        return $this->rowInstance;
    }

    /**
     *
     * @return string
     */
    public function getHeader()
    {
        $ret = '
        <table width="100%" class="header-table">
            <thead>';

        if ( $this->addCheckAll )
        {
            $ret .= '
		<td class="checkbox" width="1%">
                    <div><input type="checkbox" class="checkall" name="ids[]" value="' . $this->id . '" title="' . trans( 'Auswählen/Abwählen' ) . '"/></div>
		</td>';
        }
        $colSpan = 0;
	    $x = 0;
        $totalCols = count( $this->aHeaders );

        foreach ( $this->aHeaders as $fieldname => $opts )
        {
	        if ( $this->labelColumn && $fieldname == 'options' || in_array($fieldname, $this->removedHeaders) ) {
		        continue;
	        }

            $header[ "width" ] = (isset( $opts[ "width" ] ) && $opts[ "width" ] > 0 ? (intval( $opts[ "width" ] ) > 1 ? (intval( $opts[ "width" ] ) . '%') : $opts[ "width" ]) : 'auto');
            $header[ "class" ] = (isset( $opts[ "class" ] ) && $opts[ "class" ] > 0 ? $opts[ "class" ] : false);

            if ( ($totalCols - 1) === $x )
            {
                if ( !empty( $opts[ "class" ] ) )
                {
                    $opts[ "class" ] .= ' tc';
                }
                else
                {
                    $opts[ "class" ] = 'tc';
                }
            }

	        $x++;

            $ret .= '
                <td' . ($opts[ "class" ] ? ' class="' . $opts[ "class" ] . '"' : '') . (!empty( $opts[ "width" ] ) ? ' width="' . $opts[ "width" ] . '"' : '') . '><div>
                    ';
            $ret .= trim( $opts[ "content" ] );
            $ret .= '
                </div></td>';
            $colSpan++;
        }

        $ret .= '
            </thead>
        </table>';

        return $ret;
    }

}
