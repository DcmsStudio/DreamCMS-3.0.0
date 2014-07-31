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
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Badips_Action_Index extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$this->listBase();
	}

	protected function listBase ()
	{

		$b1 = (int)HTTP::input('b1');
		$b2 = (int)HTTP::input('b2');
		$b3 = (int)HTTP::input('b3');
		$b4 = (int)HTTP::input('b4');

		$sql = "SELECT countryid, country FROM %tp%countries ORDER BY country";
		$res = $this->db->query($sql)->fetchAll();

		$countrys        = array ();
		$countrys[ '0' ] = trans('Alle Länder');
		foreach ( $res as $r )
		{
			$countrys[ $r[ 'countryid' ] ] = $r[ 'country' ];
		}

		unset($res);

		$ip_filter = <<<S
		<div style="display:inline;line-height:20px">
            <input style="float:none" type="text" name="b1" value="{$b1}" size="3" maxlength="3" />.
            <input style="float:none" type="text" name="b2" value="{$b2}" size="3" maxlength="3" />.
            <input style="float:none" type="text" name="b3" value="{$b3}" size="3" maxlength="3" />.
            <input style="float:none" type="text" name="b4" value="{$b4}" size="3" maxlength="3" />
        </div>
S;


		$this->load('Grid');
		$this->Grid
            ->initGrid('spammers', 'spammer_id', 'spammer_ip', 'asc')
            ->setGridDataUrl('admin.php?adm=badips&action=' . $this->input('action'))
            ->addGridEvent('onAfterLoad', 'function(data, gridInst) {
                gridInst.dataTable.find("a.details").click(function(e) {
                    e.preventDefault();
                    Tools.popup($(this).attr("href"), {
                    title: "'. trans('Details der IP: ') .'" + $(this).text().trim(),
                    Width: 780,
                    Height: 420,
                    WindowResizeable: false,
                    nopadding: true,
                        onAfterCreated: function(){

                        }
                    } );
                });
            }');

		$this->Grid->addFilter(array (
		                             array (
			                             'type'  => 'html',
			                             'code'  => $ip_filter,
			                             'label' => trans('IP Adresse'),
			                             'show'  => true,
			                             'parms' => array (
				                             'size' => '40'
			                             )
		                             ),
		                             array (
			                             'name'   => 'countryid',
			                             'type'   => 'select',
			                             'select' => $countrys,
			                             'label'  => trans('Land'),
			                             'show'   => true
		                             )
		                       ));


		$this->Grid->addActions(array (
		                              "delete" => trans('Löschen'),
		                        ));

		$this->Grid->addHeader(array (
		                             // sql feld						 header	 	sortieren		standart
		                             array (
			                             'islabel' => true,
			                             "field"   => "spammer_ip",
			                             "content" => trans('IP'),
			                             "sort"    => "spammer_ip",
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "spammer_count",
			                             "content" => trans('Hits'),
			                             "sort"    => "spammer_count",
			                             'width'   => '5%',
			                             "default" => true,
			                             'align'   => 'tr'
		                             ),
		                             array (
			                             "field"   => "country",
			                             "content" => trans('Land'),
			                             "sort"    => "country",
			                             'width'   => '20%',
			                             "default" => true,
			                             'nowrap'  => true
		                             ),
		                             array (
			                             "field"   => "added",
			                             "content" => trans('hinzugefügt am'),
			                             "sort"    => "time",
			                             "default" => true,
			                             'width'   => '15%',
			                             'nowrap'  => true,
			                             'align'   => 'tc'
		                             ),
		                             array (
			                             "field"   => "lastvisit",
			                             "content" => trans('letzter Zugriff'),
			                             "sort"    => "lastvisit",
			                             "default" => true,
			                             'width'   => '15%',
			                             'nowrap'  => true,
			                             'align'   => 'tc'
		                             ),
		                             //array("field" => "provider", "content" => 'Provider', "sort" => "provider", "default" => false, 'nowrap' => false),
		                             array (
			                             "field"   => "percent",
			                             "content" => trans('Spam in %'),
			                             "sort"    => "percent",
			                             'width'   => '10%',
			                             "default" => true,
			                             'align'   => 'tr'
		                             ),
		                             array (
			                             "field"   => "options",
			                             "content" => trans('Optionen'),
			                             'width'   => '10%',
			                             "default" => true,
			                             'align'   => 'tc'
		                             ),
		                       ));


		$limit = $this->getPerpage();


		$result = $this->model->getGridData();


		$im = BACKEND_IMAGE_PATH;

		foreach ( $result[ 'result' ] as $rs )
		{
			$rs[ "percent" ] = sprintf("%0.2f", $rs[ 'percent' ]);


			$rs[ 'added' ]     = date('d.m.Y, H:i', $rs[ 'added' ]);
			$rs[ 'lastvisit' ] = $rs[ 'lastvisit' ] ? date('d.m.Y, H:i', $rs[ 'lastvisit' ]) : '';
			$spammedetails     = trans('Details der IP Adresse');

			if ( $rs[ 'country' ] != '' && $rs[ 'country' ] != '-' )
			{
				$rs[ 'spammer_ip' ] = <<<E
                <a href="admin.php?adm=badips&action=details&id={$rs['spammer_id']}" class="details" title="IP details">{$rs['spammer_ip']}</a>
E;
			}

			$rs[ 'country' ] = ($rs[ 'country' ] == '' || $rs[ 'country' ] == '-' ? 'Unbekannt' : $rs[ 'country' ]);

			$rs[ 'options' ] = '';

			$row = $this->Grid->addRow($rs);
			$row->addFieldData("spammer_ip", $rs[ 'spammer_ip' ]);

			$row->addFieldData("country", $rs[ "country" ]);
			$row->addFieldData("spammer_count", $rs[ "spammer_count" ]);
			$row->addFieldData("added", $rs[ "added" ]);
			$row->addFieldData("lastvisit", $rs[ "lastvisit" ]);
			$row->addFieldData("percent", $rs[ "percent" ]);
			$row->addFieldData("options", $rs[ 'options' ]);
		}

		$griddata = $this->Grid->renderData($result[ 'total' ]);

		if ( HTTP::input('getGriddata') )
		{
			$data[ 'success' ] = true;
			$data[ 'total' ]   = $result[ 'total' ];
			$data[ 'datarows' ] = $griddata[ 'rows' ];

			echo Library::json($data);
			exit;
		}

		Library::addNavi(trans('Gesperrte IPs'));

		$this->Template->addScript(BACKEND_JS_URL . 'dcms.googlemap.js');
		$this->Template->process('badips/index', array (), true);
	}

	protected function listSpammers ()
	{

		$b1        = (int)HTTP::input('b1');
		$b2        = (int)HTTP::input('b2');
		$b3        = (int)HTTP::input('b3');
		$b4        = (int)HTTP::input('b4');
		$countryid = (int)HTTP::input('countryid');

		$sql = "SELECT countryid, country FROM %tp%countries ORDER BY country";
		$res = $this->db->query($sql)->fetchAll();

		$countrys       = array ();
		$countrys[ '' ] = trans('Alle Länder');
		foreach ( $res as $r )
		{
			$countrys[ $r[ 'countryid' ] ] = $r[ 'country' ];
		}

		$ip_filter = <<<S
				<div style="display:inline;line-height:20px">
                    <input style="float:none" type="text" name="b1" value="{$b1}" size="3" maxlength="3" /> .
                    <input style="float:none" type="text" name="b2" value="{$b2}" size="3" maxlength="3" /> .
                    <input style="float:none" type="text" name="b3" value="{$b3}" size="3" maxlength="3" /> .
                    <input style="float:none" type="text" name="b4" value="{$b4}" size="3" maxlength="3" />
                </div>
S;


		$this->load('Grid');
		$this->Grid->initGrid('spammers', 'spammer_id', 'ip', 'asc')->setGridDataUrl('admin.php?adm=badips&action=' . $this->input('action'));
		$this->Grid->addFilter(array (
		                             array (
			                             'type'  => 'html',
			                             'code'  => $ip_filter,
			                             'label' => trans('IP Adresse'),
			                             'show'  => true
		                             ),
		                             array (
			                             'name'   => 'countryid',
			                             'type'   => 'select',
			                             'select' => $countrys,
			                             'label'  => trans('Land'),
			                             'show'   => false
		                             ),
		                       ));


		$this->Grid->addHeader(array (
		                             // sql feld						 header	 	sortieren		standart
		                             array (
			                             "field"   => "spammer_ip",
			                             "content" => trans('IP'),
			                             "sort"    => "spammer_ip",
			                             "default" => true
		                             ),
		                             array (
			                             "field"   => "spammer_count",
			                             "content" => trans('Hits'),
			                             "sort"    => "spammer_count",
			                             'width'   => '8%',
			                             "default" => true,
			                             'align'   => 'tr'
		                             ),
		                             array (
			                             "field"   => "country",
			                             "content" => trans('Land'),
			                             "sort"    => "country",
			                             'width'   => '20%',
			                             "default" => true,
			                             'nowrap'  => true
		                             ),
		                             array (
			                             "field"   => "added",
			                             "content" => trans('Datum'),
			                             "sort"    => "time",
			                             "default" => false,
			                             'width'   => '15%',
			                             'nowrap'  => true,
			                             'align'   => 'tc'
		                             ),
		                             //array("field" => "provider", "content" => 'Provider', "sort" => "provider", "default" => false, 'nowrap' => false),
		                             array (
			                             "field"   => "percent",
			                             "content" => trans('Spam in %'),
			                             "sort"    => "percent",
			                             'width'   => '8%',
			                             "default" => false,
			                             'align'   => 'tr'
		                             ),
		                             array (
			                             "field"   => "options",
			                             "content" => trans('Optionen'),
			                             'width'   => '10%',
			                             "default" => true,
			                             'align'   => 'tc'
		                             ),
		                       ));


		$this->Grid->addActions(array (
		                              "delete" => trans('Löschen'),
		                        ));


		switch ( $GLOBALS[ 'sort' ] )
		{
			case "desc":
			default:
				$sort = " DESC";
				break;
			case "asc":
				$sort = " ASC";
				break;
		}

		switch ( $GLOBALS[ 'orderby' ] )
		{
			case 'spammer_ip':
			default:
				$order = " ORDER BY spam.spammer_ip";
				break;

			case 'spammer_count':
				$order = " ORDER BY spam.spammer_count";
				break;

			case 'time':
				$order = " ORDER BY spam.added";
				break;

			case 'provider':
				$order = " ORDER BY provider";
				break;

			case 'country':
				$order = " ORDER BY c.country";
				break;

			case 'percent':
				$order = " ORDER BY percent";
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

		$_ip = '';
		$_ip .= ($b1 > 0 && $b1 < 255 ? $b1 . '.' : '');
		$_ip .= (($b1 > 0 && $b1 < 255 && $b2 > 0 && $b2 < 255) ? $b2 . '.' : '');
		$_ip .= (($b1 > 0 && $b1 < 255 && $b2 > 0 && $b2 < 255 && $b3 > 0 && $b3 < 255) ? $b3 . '.' : '');
		$_ip .= (($b1 > 0 && $b1 < 255 && $b2 > 0 && $b2 < 255 && $b3 > 0 && $b3 < 255 && $b4 > 0 && $b4 < 255) ?
			$b4 . '' : '');

		$addwhere = ($_ip != '' ? ' WHERE spam.spammer_count > 0 AND spam.spammer_ip LIKE \'' . $_ip . '%\' ' :
			' WHERE spam.spammer_count > 0 ');

		if ( $countryid > 0 )
		{
			$addwhere .= ($addwhere != '' ? " AND spam.countryid={$countryid}" : " WHERE spam.countryid={$countryid}");
		}

		$sql = "SELECT COUNT(spam.spammer_id) AS total,
                SUM(spam.spammer_count) AS counttotal 
                FROM %tp%spammers AS spam
                LEFT JOIN %tp%countries AS c ON(c.countryid=spam.countryid ) " . $addwhere;
		$rrs = $this->db->query_first($sql);

		$limit             = $this->getPerpage();
		$this->dataresults = $rrs[ 'total' ];


		if ( $rrs[ 'total' ] )
		{
			$pages = ceil($rrs[ 'total' ] / $limit);


			$sql = "SELECT spam.*, c.country, /*isp.provider */ '' AS provider,
				IF(spam.spammer_count>0,(spam.spammer_count * 100 / {$rrs['counttotal']} ),0) AS percent
				FROM %tp%spammers AS spam
				LEFT JOIN %tp%countries AS c ON(c.countryid=spam.countryid )
				/* LEFT JOIN ip2country_isp AS isp ON(isp.ispid=spam.ispid ) */
                    " . $addwhere . "
                {$order} {$sort}
				LIMIT " . ($limit * ($page - 1)) . ",$limit";
			$res = $this->db->query($sql)->fetchAll();

			$im = BACKEND_IMAGE_PATH;
			foreach ( $res as $rs )
			{
				$rs[ "percent" ]  = sprintf("%0.2f", $rs[ 'percent' ]);
				$rs[ 'provider' ] = ($rs[ 'provider' ] == '' || $rs[ 'provider' ] == '-' ? 'Unbekannt' :
					$rs[ 'provider' ]);
				$rs[ 'country' ]  = ($rs[ 'country' ] == '' || $rs[ 'country' ] == '-' ? 'Unbekannt' :
					$rs[ 'country' ]);
				$rs[ 'added' ]    = date('d.m.Y, H:i', $rs[ 'added' ]);
				$rs[ 'options' ]  = '';

				$spammedetails      = trans('Details der IP Adresse');
				$rs[ 'spammer_ip' ] = <<<E
                <a href="javascript:void(0);" onclick="Tools.popup('admin.php?adm=banned&action=details&id={$rs['spammer_id']}', '{$spammedetails}', 780)" title="IP details">{$rs['spammer_ip']}</a>
E;

				$row = $this->Grid->addRow($rs);
				$row->addFieldData("spammer_ip", $rs[ 'spammer_ip' ]);
				$row->addFieldData("provider", $rs[ "provider" ]);
				$row->addFieldData("country", $rs[ "country" ]);
				$row->addFieldData("spammer_count", (int)$rs[ "spammer_count" ]);
				$row->addFieldData("added", $rs[ "added" ]);
				$row->addFieldData("percent", $rs[ "percent" ]);
				$row->addFieldData("options", $rs[ 'options' ]);
			}
		}


		$griddata = $this->Grid->renderData($rrs[ 'total' ]);

		if ( HTTP::input('getGriddata') )
		{
			$data[ 'success' ] = true;
			$data[ 'total' ]   = $rrs[ 'total' ];
			# $data['sort'] = $GLOBALS['sort'];
			# $data['orderby'] = $GLOBALS['orderby'];
			$data[ 'datarows' ] = $griddata[ 'rows' ];

			echo Library::json($data);
			exit;
		}

		Library::addNavi(trans('Spammer'));
		$this->Template->process('badips/index', array (), true);
		exit;
	}

}
