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
 * @package      Badips
 * @version      3.0.0 Beta
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Mysql.php
 */
class Badips_Model_Mysql extends Model
{

	public function getGridData ()
	{

		$b1        = (int)HTTP::input('b1');
		$b2        = (int)HTTP::input('b2');
		$b3        = (int)HTTP::input('b3');
		$b4        = (int)HTTP::input('b4');
		$countryid = (int)HTTP::input('countryid');


		$_ip = '';
		$_ip .= ($b1 > 0 && $b1 < 255 ? $b1 . '.' : '');
		$_ip .= (($b1 > 0 && $b1 < 255 && $b2 > 0 && $b2 < 255) ? $b2 . '.' : '');
		$_ip .= (($b1 > 0 && $b1 < 255 && $b2 > 0 && $b2 < 255 && $b3 > 0 && $b3 < 255) ? $b3 . '.' : '');
		$_ip .= (($b1 > 0 && $b1 < 255 && $b2 > 0 && $b2 < 255 && $b3 > 0 && $b3 < 255 && $b4 > 0 && $b4 < 255) ?
			$b4 . '' : '');

		$addwhere = ($_ip != '' ? ' WHERE spam.spammer_ip LIKE \'' . $_ip . '%\' ' : '');

		if ( $countryid > 0 )
		{
			$addwhere .= ($addwhere != '' ? " AND spam.countryid={$countryid}" : " WHERE spam.countryid={$countryid}");
		}

		$sql = "SELECT SUM(spam.spammer_count) AS counttotal FROM %tp%spammers AS spam
                LEFT JOIN %tp%countries AS c ON(c.countryid=spam.countryid )
                " . $addwhere;
		$rrs = $this->db->query_first($sql);

		$sql = "SELECT COUNT(spam.spammer_id) AS total FROM %tp%spammers AS spam
                LEFT JOIN %tp%countries AS c ON(c.countryid=spam.countryid ) " . $addwhere;
		$r   = $this->db->query_first($sql);

		switch ( $GLOBALS[ 'sort' ] )
		{
			case 'asc':
				$sort = " ASC";
				break;

			case 'desc':
			default:
				$sort = " DESC";
				break;
		}

		switch ( $GLOBALS[ 'orderby' ] )
		{
			case 'spammer_ip':
				default:
				$_order = " ORDER BY spam.spammer_ip";
				break;

			case 'spammer_count':
				$_order = " ORDER BY spam.spammer_count";
				break;

			case 'time':
				$_order = " ORDER BY spam.added";
				break;
			case 'lastvisit':
				$_order = " ORDER BY spam.lastvisit";
				break;
			case 'provider':
				$_order = " ORDER BY provider";
				break;

			case 'country':
				$_order = " ORDER BY c.country";
				break;

			case 'percent':
				$_order = " ORDER BY percent";
				break;
		}


		if ( HTTP::input('page') )
		{
			$page = (int)HTTP::input('page');
			if ( $page == 0 )
			{
				$page = 1;
			}
		}
		else
		{
			$page = 1;
		}


		$limit = $this->getPerpage();

		$sql = "SELECT spam.*, c.country, /*isp.provider */ '' AS provider,
					IF(spam.spammer_count>0,(spam.spammer_count * 100 /{$rrs['counttotal']} ),0) AS percent
					FROM %tp%spammers AS spam
					LEFT JOIN %tp%countries AS c ON(c.countryid=spam.countryid )
					/* LEFT JOIN ip2country_isp AS isp ON(isp.ispid=spam.ispid ) */
	                    " . $addwhere . "
	                {$_order} {$sort} LIMIT " . ($limit * ($page - 1)) . ",$limit";

		return array (
			'total'  => $r[ 'total' ],
			'result' => $this->db->query($sql)->fetchAll()
		);

	}

	public function insert ( $data )
	{
		$this->db->query('INSERT INTO %tp%spammers (spammer_name,spammer_ip,spammer_iplong,spammer_mail,added,lastvisit,spammer_count,countryid,ispid)
						  VALUES (?,?,?,?,?,?,?,?,?)', '', $data['ip'], $data['long_ip'], $data['email'], TIMESTAMP, 0, 0, $data['countryid'], 0);
	}

}

?>