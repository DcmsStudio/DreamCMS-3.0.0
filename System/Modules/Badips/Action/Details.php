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
 * @file         Details.php
 */
class Badips_Action_Details extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}


		$id = (int)HTTP::input('id');

		$sql = "SELECT spam.spammer_count, spam.spammer_ip, c.country, c.lat,c.lon, c.capital, c.region
                FROM %tp%spammers AS spam
                LEFT JOIN %tp%countries AS c ON(c.countryid=spam.countryid )
                /* LEFT JOIN ip2country_isp AS isp ON(isp.ispid=spam.ispid ) */
                WHERE spam.spammer_id = ?";
		$rs  = $this->db->query($sql, $id)->fetch();
		//$hostname = @gethostbyaddr($rs['spammer_ip']);

		$hostname = $this->Env->nslookup($rs[ 'spammer_ip' ]);
		$hostname = ($hostname ? $hostname : trans('unbekannt'));

		$sip = sprintf(trans('Spammer IP details %s'), $rs[ 'spammer_ip' ]);


		$html = <<<EOF


                <div id="dcmsgmap-{$id}" style="width:780px!important;height:380px!important;"></div>

                <script type="text/javascript">
                    var lat = '{$rs['lat']}';
                    var lon = '{$rs['lon']}';

                    if( lat != '' && lon != '' )
                    {
                        var ipnote = '<div id="content" style="width: 300px;overflow:hidden"><div id="siteNotice"></div>'
                            + '<h1 id="firstHeading" class="firstHeading" style="margin-bottom: 10px">Spammer Infos</h1>' 
                            + '<div id="bodyContent" style="width: 300px;overflow:hidden"><p>IP: {$rs['spammer_ip']}<br/>Host: {$hostname}<br/>Land: {$rs['country']}<br/>Region: {$rs['region']}</p></div>'
                            + '</div>';
                            
                        initMap({
                              mapElement: '#dcmsgmap-{$id}',
                              lat: lat,
                              lon: lon,
                              useMarker: true,
                              infoWindow: ipnote,
                              markerTitle: 'Spammer IP {$rs['spammer_ip']}'
                        });
                        
                    }
                    else
                    {
                        $("#dcmsgmap-{$id}").html('<p style="line-height: 300px; font-size: 24px; text-align: center; color: #A0A0A0">Kann leider das Land nicht in der Datenbank finden<p>');
                    }
                </script>
EOF;


		echo Library::json(array (
		                         'success'          => true,
		                         'maincontent'      => $html,
		                         'pageCurrentTitle' => $sip
		                   ));
		exit;
	}

}
