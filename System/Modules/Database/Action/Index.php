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
 * @package      Database
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Database_Action_Index extends Controller_Abstract
{

	/**
	 * @param $status
	 * @return float|string
	 */
	static function get_dbtable_size ( $status )
	{

		if ( $status[ 'Type' ] == 'MyISAM' || $status[ 'Type' ] == 'ISAM' || $status[ 'Type' ] == 'HEAP' || !isset($status[ 'Type' ]) )
		{
			return (doubleval($status[ 'Data_length' ]) + doubleval($status[ 'Index_length' ]));
		}
		elseif ( $status[ 'Type' ] == 'InnoDB' )
		{
			return $status[ 'Data_length' ] + $status[ 'Index_length' ];
		}
		elseif ( $status[ 'Type' ] == "MRG_MyISAM" || $status[ 'Type' ] == "BerkeleyDB" )
		{
			return "unknown";
		}
		else
		{
			return "unknown";
		}
	}

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}


		$_tables   = "";
		$tblcount  = 0;
		$rowcount  = 0;
		$sizecount = 0;

		$tables = $this->db->listTables();


		$i             = 1;
		$overhead_size = 0;

		$data = array ();

		foreach ( $tables as $idx => $table )
		{
			$status = $this->db->getTableState($table);

			$count = $status[ 'Rows' ];
			$rowcount += $count;
			$name = $table;
			$type = ($status[ 'Engine' ] ? $status[ 'Engine' ] : '-');
			$size = self::get_dbtable_size($status);

			$formated_overhead = '-';

			if ( isset($status[ 'Data_free' ]) && $status[ 'Data_free' ] > 0 )
			{
				$formated_overhead = Library::humanSize($status[ 'Data_free' ]);
				$overhead_size += $status[ 'Data_free' ];
			}

			if ( is_numeric($size) )
			{
				$sizecount += $size;
				$size = Library::humanSize($size);
			}

			$add_css = '';
			if ( $status[ 'Data_free' ] > 0 )
			{
				$add_css = ' dbtbl_optimizerow';
			}


			$rowclass = ($idx % 2 ? 'even' : 'odd');

			$_tables .= <<<EOF
<tr class="{$rowclass}{$add_css}">
    <td class="tc"><input type=checkbox value="$name" name="tables[]" class="checkbox1"></td>
    <td><a href="javascript:void(0);" onclick="getInfoData('admin.php?adm=database&ajax=1&action=showtable&name=$name&sid=$cp->session_id')"><img src="images/msg_info.gif" class="absmiddle" title="Details" alt="Details"/></a> $name</td>
    <td class="tr">$count</td>
    <td class="tc">$type</td>
    <td class="tr">$size</td>
    <td class="tr">$formated_overhead</td>
</tr>
EOF;

			$data[ 'tables' ][ ] = array (
				'name'     => $name,
				'engine'   => $type,
				'rows'     => $count,
				'size'     => $size,
				'overhead' => $formated_overhead,
			);
		}
		$data[ 'total_rows' ]         = $rowcount;
		$data[ 'total_size' ]         = Library::humanSize($sizecount);
		$data[ 'total_overheadsize' ] = Library::humanSize($overhead_size);


		$data[ 'nopadding' ] = true;


		$this->Template->process('database/index', $data, true);
	}

}

?>