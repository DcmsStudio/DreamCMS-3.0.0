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
 * @package      Firewall
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2014 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */

class Firewall_Action_Index extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isBackend() )
		{
			demoadm();
			$this->processBackend();
		}
	}

	protected function processBackend ()
	{



        $id = intval( $this->input('id') );

        if ( $id )
        {
            $r = $this->model->getIpByID($id);

            if (!$r['id']) {
                Library::sendJson(false, trans('Kann die IP nicht finden') );
            }

            $r['host'] = (trim($r[ "ip" ]) == trim($r[ 'dns' ]) ? trans('Unbekannt') : $r[ 'dns' ]);
            $r['date'] = date('d.m.Y, H:i:s', $r['timestamp']);
            $r['browser'] = $r['useragent'];

            $rs = $this->db->query( 'SELECT c.* FROM %tp%countries AS c
								LEFT JOIN %tp%ip2nation AS n ON(n.countryid = c.countryid)
								WHERE n.ip < INET_ATON(?)
								ORDER BY n.ip DESC LIMIT 1', $r[ 'ip' ] )->fetch();

            $r['lat'] = $rs['lat'];
            $r['lon'] = $rs['lon'];
            $r['country'] = $rs['country'];

            Ajax::Send(true, $r);

            exit;
        }






        $this->load('Grid');
        $this->Grid->initGrid('firewall_log', 'id', 'timestamp', 'desc')
            ->setGridDataUrl('admin.php?adm=firewall')
            ->addGridEvent('onAfterLoad', 'function(data, gridInst) {


                gridInst.dataTable.find("a.ip-detail").unbind().click(function(e) {
                    e.preventDefault();

                    if ( typeof getAdvancedFirewall == "function") {
                       // firewallWinOpt.WindowID = HashGen.md5( $(this).attr("href") );

                        $.get($(this).attr("href"), function(data) {
                            if (Tools.responseIsOk(data))
                            {
                                getAdvancedFirewall(data, Win.windowID);
                            }
                        });
                    }
                });

                gridInst.dataTable.find("a[href*=ban]").unbind().click(function(e) {
                    e.preventDefault();
                    var s = $(this);
                    $(this).find(".fa-cog").show();

                    $.get($(this).attr("href"), function(data) {
                        if (Tools.responseIsOk(data))
                        {
                            if (data.msg) {
                                Notifier.info(data.msg);
                            }

                            var ip = s.parent().prev().text().trim();
                            if ( ip ) {
                                gridInst.dataTable.find("div.opt").each(function(){
                                    if ($(this).prev().text().trim() == ip) {
                                        $(this).find("a[href*=ban]").replaceWith("<span>'.trans('gesperrt') .'</span>");
                                    }
                                });
                            }

                            s.replaceWith("<span>'.trans('gesperrt') .'</span>");
                        }
                    })
                });
            }');
        $this->Grid->addFilter(array (
            array (
                'name'  => 'q',
                'type'  => 'input',
                'value' => '',
                'label' => 'Suchen nach',
                'show'  => true,
                'parms' => array (
                    'size' => '40'
                )
            ),
            array(
                'name'   => 'in',
                'type'   => 'select',
                'select' => array(
                    '' => '----',
                    'ip' => trans('IP'),
                    'refferer' => trans('Refferer'),
                    'errortype' => trans('Typ'),
                    'requesturi' => trans('Request'),
                    'dns' => trans('DNS'),
                    'useragent' => trans('Agent')
                ),
                'label'  => trans( 'Suchen in' ),
                'show'   => false
            ),
            array (
                'submitbtn' => true
            ),
        ));
        $this->Grid->addHeader(array (
            // sql feld						 header	 	sortieren		standart
            array (
                'islabel' => true,
                "field"   => "errortype",
                "content" => trans('Typ'),
                'width'   => '5%',
                "sort"    => "errortype",
                "default" => true
            ),
            array (
                "field"   => "ip",
                "content" => trans('IP'),
                // 'width'   => '10%',
                "sort"    => "ip",
                'width'   => '10%',
                "default" => true,
                'islabel' => true
            ),

            array (
                "field"   => "dns",
                "content" => trans('DNS'),
                "sort"    => "dns",
                'width'   => '15%',
                "default" => true
            ),

            array (
                "field"   => "useragent",
                "content" => trans('Agent'),
                "sort"    => "useragent",
                'width'   => '15%',
                "default" => true
            ),
            array (
                "field"   => "refferer",
                "content" => trans('Refferer'),
                "sort"    => "refferer",
                "default" => true
            ),
            array (
                "field"   => "requesturi",
                "content" => trans('Request'),
                "sort"    => "requesturi",
                'width'   => '25%',
                "default" => true
            ),
            array (
                "field"   => "timestamp",
                "content" => trans('Datum'),
                "sort"    => "timestamp",
                'width'   => '10%',
                "default" => true
            ),
            array (
                "field"   => "options",
                "content" => trans('Optionen'),
                'width'   => '12%',
                "default" => true,
                'align'   => 'tc'
            ),
        ));

        $this->Grid->addActions(array (
            "delete" => array (
                'label' => trans('Löschen'),
                'msg'   => trans('Ausgewählte Firewall-Logs werden gelöscht. Wollen Sie fortsetzen?')
            ),
            "ban" => array (
                'label' => trans('Sperren'),
                'msg'   => trans('Ausgewählte Firewall-Logs werden gesperrt. Wollen Sie fortsetzen?')
            )
        ));



        $_result = $this->model->getGridData();

        $im = BACKEND_IMAGE_PATH;

        $d = trans('Löschen');
        $b = trans('IP Sperren');

        foreach ( $_result[ 'result' ] as $rs )
        {
            $ban = '';
            if (!$rs['blocked']) {
                $ban = <<<E
<a class="ban ajax" href="admin.php?adm=firewall&amp;action=ban&amp;id={$rs['id']}"><img src="{$im}delete.png" border="0" alt="{$b}" title="{$b}" /></a>
E;
            }

            $rs[ 'options' ] = <<<EOF

        {$ban}
        <a class="delconfirm" href="admin.php?adm=firewall&amp;action=delete&amp;id={$rs['id']}"><img src="{$im}delete.png" border="0" alt="{$d}" title="{$d}" /></a>
EOF;

            $rs[ 'dns' ] = (trim($rs[ "ip" ]) == trim($rs[ 'dns' ]) ? trans('Unbekannt') : $rs[ 'dns' ]);


            $rs[ "ip" ] = <<<E
        <a class="ip-detail" href="admin.php?adm=firewall&amp;id={$rs['id']}">{$rs["ip"]}</a>
E;





            $row             = $this->Grid->addRow($rs);

            $row->addFieldData("ip", $rs[ "ip" ]);
            $row->addFieldData("dns", $rs[ 'dns' ] );
            $row->addFieldData("errortype", $rs[ 'errortype' ]);
            $row->addFieldData("timestamp", date('d.m.Y, H:i:s', $rs['timestamp']));

            $row->addFieldData("requesturi", htmlspecialchars($rs[ "requesturi" ]) );
            $row->addFieldData("refferer", htmlspecialchars($rs[ 'refferer' ]));
            $row->addFieldData("useragent", htmlspecialchars($rs[ 'useragent' ]));




            $row->addFieldData("options", $rs[ 'options' ]);
        }

        $griddata = $this->Grid->renderData($_result[ 'total' ]);
        $data     = array ();
        if ( $this->input('getGriddata') )
        {

            $data[ 'success' ] = true;
            $data[ 'total' ]   = $_result[ 'total' ];
            $data[ 'datarows' ] = $griddata[ 'rows' ];
            unset($_result, $this->Grid);

            Ajax::Send(true, $data);
            exit;
        }

        Library::addNavi(trans('Firewall-Log Übersicht'));
        $this->Template->process('firewall/index', array (), true);
	}

}