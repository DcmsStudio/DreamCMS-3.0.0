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
 * @package      Statistic
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Worldmap.php
 */
class Statistic_Action_Worldmap extends Statistic_Helper_Base
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}


		$this->selectedMonth = Session::get('statistic_month');
		$this->selectedYear  = Session::get('statistic_year');

		$mapwidth  = (int)HTTP::input('width') > 0 ? (int)HTTP::input('width') : null;
		$mapheight = (int)HTTP::input('height') > 0 ? (int)HTTP::input('height') : null;


		$map = $this->renderWorldMap($mapwidth, $mapheight);

		echo Library::json(array (
		                         'success'  => true,
		                         'worldmap' => $map[ 0 ],
		                         'height'   => $map[ 1 ]
		                   ));
		exit;
	}

	/**
	 *
	 * @param null $mapwidth
	 * @param null $mapheight
	 * @return string generated HTML Code
	 */
	private function renderWorldMap ( $mapwidth = null, $mapheight = null )
	{

		$result = $this->getData();
		foreach ( $result as $r )
		{
			if ( !isset($this->stat_ids[ $r[ 'id' ] ]) )
			{
				$this->stat_ids[ $r[ 'id' ] ] = $r[ 'id' ];
			}
		}


		if ( !is_array($this->stat_ids) )
		{
			$this->stat_ids[ 0 ] = 0;
		}


		$stat_idStr = implode(',', $this->stat_ids);


		$sql    = "SELECT COUNT(sc.statid) AS hits, sc.langkey, c.lat, c.lon, c.country FROM %tp%statistik_country AS sc
                    LEFT JOIN %tp%countries AS c ON(c.tld=sc.langkey)
                " . ($stat_idStr != '' ? " WHERE sc.statid IN({$stat_idStr}) " : ' WHERE sc.statid>0') . "
                GROUP BY sc.langkey";
		$result = $this->db->query($sql)->fetchAll();

		$countrys = array ();
		foreach ( $result as $r )
		{
			$countrys[ $r[ 'country' ] ] = $r[ 'lat' ] . '|' . $r[ 'lon' ] . '|' . $r[ 'hits' ];
		}

		$result = null;

		// get image data
		$image_worldmap_info = getimagesize(ROOT_PATH . HTML_URL . 'img/statistic/' . $this->worldmap);
		// $image_pin_info      = getimagesize(ROOT_PATH . HTML_URL . 'img/statistic/' . $this->worldmap_pin);


		$_w     = $image_worldmap_info[ 0 ];
		$_h     = $image_worldmap_info[ 1 ];
		$scalar = 0;

		if ( $mapwidth > 0 && $mapwidth > $_w )
		{
			$scalar = $mapwidth / $_w;
			$_w     = $mapwidth;
			$_h     = ($_h * $scalar);
		}
		/*
		  if ($mapheight > 0 && $mapheight > $_h)
		  {
		  $scalar = round($mapwidth / $_h);
		  $_h     = $mapheight;
		  $_w     = ($_w * $scalar);
		  }
		 */

		$image_worldmap_width  = $_w;
		$image_worldmap_height = $_h;
		// $image_pin_width       = $image_pin_info [0];
		// $image_pin_height      = $image_pin_info [1];
		// map parameters
		$scale = 360 / $image_worldmap_width;


		// show worldmap
		$maphtml = '<div style="position:relative;margin:0px!important;padding:0px!important;display:inline-table;margin: 0 auto;" id="map">


            <img src="' . HTML_URL . 'img/statistic/' . $this->worldmap . '" width="' . $image_worldmap_width . '" height="' . $image_worldmap_height . '" border="0" style="position:absolute;top:0!important;z-index:10;border:none!important;padding:0px!important;margin:0px!important;"/>

            <!-- <div id="worldmap" style="position:relative;left:0;top:0;background-image:url(' . HTML_URL . 'img/statistic/' . $this->worldmap . ');margin:0px auto;width:' . $image_worldmap_width . 'px;height:' . $image_worldmap_height . 'px;border:none;"> -->';

		// create pin on the map
		foreach ( $countrys as $key => $v )
		{

			//---------- explode lat lon ----------------
			$values = explode("|", $v);
			if ( $values[ 1 ] == '' || $values[ 0 ] == '' )
			{
				continue;
			}


			$x = floor(($values [ 1 ] + 180) / $scale) - 8;
			$y = floor((180 - ($values [ 0 ] + 90)) / $scale) - 8;

			// $maphtml .= '<img style="position:absolute;z-index:20;top:' . $y . 'px;left:' . $x . 'px;" src="' . HTML_URL . 'img/statistic/' . $this->worldmap_pin . '" width="' . $image_pin_width . '" height="' . $image_pin_height . '" alt="" title="' . $key .' ('. $values[2] .' hits )' . '">';


			$maphtml .= '<span class="mapPin" style="left:' . $x . 'px;top:' . $y . 'px;" data="' . $key . ' (' . $values[ 2 ] . ' hits)"></span>';
		}

		$maphtml .= '</div>';

		return array (
			$maphtml,
			$image_worldmap_height
		);
	}

}
