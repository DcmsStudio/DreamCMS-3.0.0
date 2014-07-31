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
 * @category     Helper
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Statistic_Helper_Base extends Controller_Abstract
{

	/**
	 * @var string
	 */
	public $worldmap = 'worldmap.jpg';

	/**
	 * @var string
	 */
	public $worldmap_pin = 'pin_red.png';

	/**
	 * @var null
	 */
	public $selectedMonth = null;

	/**
	 * @var null
	 */
	public $selectedYear = null;

	/**
	 * @var array
	 */
	public $stat_ids = array ();

	/**
	 * @var array
	 */
	public $dTempArray = array ();

	/**
	 * @var array
	 */
	public $hTempArray = array ();

	/**
	 * @var array
	 */
	public $wTempArray = array ();

	// refferer limit
	/**
	 * @var int
	 */
	public $reffererLimit = 100;

	/**
	 * @var int
	 */
	public $real_visits_month = 0;

	/**
	 * @var int
	 */
	public $total_hits_month = 0;

	/**
	 * @var int
	 */
	public $total_browser_hits = 0;

	/**
	 * @var int
	 */
	public $total_os_hits = 0;

	/**
	 * @var int
	 */
	public $total_lang_hits = 0;

	/**
	 * @var int
	 */
	public $total_spider_hits = 0;

	/**
	 * @var int
	 */
	public $total_refferer_hits = 0;

	/**
	 * @var int
	 */
	public $total_screen_hits = 0;

	/**
	 * @var string
	 */
	public $iconLib_path = 'public/html/img/statistic/';

	/**
	 * @var array
	 */
	public $minStat = array ();

	/**
	 * @var array
	 */
	public $maxStat = array ();

	/**
	 * @var array
	 */
	public $colors = array (
		'F79473',
		'FFAD7B',
		'F7C684',
		'D6496C',
		'BDE794',
		'A5DE94',
		'8CD694',
		'94D6C6',
		'94D6E7',
		'9CADCE',
		'9C94C6',
		'947BBD',
		'B584BD',
		'C684BD',
		'C684BD',
		'FF94C6',
		'F7949C',
		'FF6342',
		'FF8C4A',
		'F7B54A',
		'E2F86B',
		'A5DE63',
		'7BCE6B',
		'42BD6B',
		'52BDA5',
		'5AC6DE',
		'6B8CC6',
		'6B63AD',
		'689EB9',
		'9C52AD',
		'BD52AD',
		'FF63AD',
		'FF6384',
		'FE881D',
		'FF9C21',
		'EFFF29',
		'7BCE31',
		'7BCE6B',
		'42BD6B',
		'52C6AD',
		'63C6DE',
		'6B8CBD',
		'3963AD',
		'F7298C',
		'A51800',
		'AD3908',
		'9CA510',
		'528C21',
		'217B29',
		'00B0A4',
		'08738C',
		'C6B59C',
		'9C8C73',
		'C6A56B',
		'AD844A',
		'9710B4',
		'946331',
		'734210',
		'008400',
		'3EC19A',
		'28D7D7',
		'A4C13E',
		'A7588B',
		'03CF45',
		'808040',
		'840084',
		'737373',
		'C48322',
		'809254',
		'1E8259',
		'46128D',
		'8080C0'
	);

	/**
	 * set all arrays to 0
	 */
	public function initStat ()
	{

		// =================================================
		// Anzahl der Tage im Monat
		// =================================================
		$maximaleDays = date("t", mktime(0, 0, 0, $this->selectedMonth, 1, $this->selectedYear));
		for ( $i = 1; $i <= $maximaleDays; $i++ )
		{
			$this->dTempArray[ $i ] = 0; // Standart auf 0 setzen
		}

		// =================================================
		// Stundenauswertung
		// =================================================
		for ( $i = 0; $i < 24; $i++ )
		{
			$hstring                            = ($i < 10 ? '0' . $i : $i);
			$this->hTempArray[ 'h' . $hstring ] = 0; // Standart auf 0 setzen
		}

		// =================================================
		// Wochentage
		// =================================================
		for ( $i = 0; $i <= 6; $i++ )
		{
			$this->wTempArray[ $i ] = 0; // Standart auf 0 setzen
		}
	}

	public function getMinMaxStat ()
	{

		$r  = $this->db->query('SELECT day,month,year FROM %tp%statistik_total ORDER BY day ASC, year ASC, month ASC LIMIT 1')->fetch();
		$ts = mktime(0, 0, 0, $r[ 'day' ], $r[ 'month' ], $r[ 'year' ]);

		$this->minStat = array (
			'day'       => $r[ 'day' ],
			'month'     => $r[ 'month' ],
			'monthname' => Locales::getTranslatedDate('F', $ts),
			'year'      => $r[ 'year' ]
		);
		$this->maxStat = array (
			'day'       => date('j'),
			'month'     => date('n'),
			'monthname' => Locales::getTranslatedDate('F', time()),
			'year'      => date('Y')
		);
	}

	/**
	 * returns data from giving month and year
	 */
	public function getMonthYear ()
	{

		$data = array ();


		$this->stat_ids[ 0 ] = 0;
		$total_hour_hits     = 0;
		$real_visits_month   = 0;

		$result = $this->getData();
		foreach ( $result as $r )
		{
			if ( !isset($this->stat_ids[ $r[ 'id' ] ]) )
			{
				$this->stat_ids[ $r[ 'id' ] ] = $r[ 'id' ];
			}

			// hours
			for ( $i = 0; $i < 24; $i++ )
			{
				$hstring = ($i < 10 ? '0' . $i : $i);
				$total_hour_hits += (int)$r[ 'h' . $hstring ];
				$this->hTempArray[ 'h' . $hstring ] += $r[ 'h' . $hstring ] > 0 ? $r[ 'h' . $hstring ] : 0;
			}


			$this->dTempArray[ (int)$r[ 'day' ] ] += ($r[ 'hits' ] > 0 ? $r[ 'hits' ] : 0);
			$ts      = mktime(12, 0, 1, $r[ 'month' ], $r[ 'day' ], $r[ 'year' ]);
			$weekday = date('w', $ts);
			$this->wTempArray[ (int)$weekday ] += ($r[ 'hits' ] > 0 ? $r[ 'hits' ] : 0);

			$real_visits_month += $r[ 'visitors' ];
		}
		unset($result);

		$this->total_hour_hits = $total_hour_hits;
		$data[ 'hours' ]       = $this->hTempArray;
		$data[ 'weeks' ]       = $this->wTempArray;
		$data[ 'days' ]        = $this->dTempArray;


		$total_hits_month = (int)array_sum($this->dTempArray);

		$this->total_hits_month  = $total_hits_month;
		$this->real_visits_month = $real_visits_month;

		$stat_idStr = implode(',', $this->stat_ids);


		$result                   = $this->getData('browser', $stat_idStr);
		$browsers                 = array ();
		$this->total_browser_hits = 0;
		foreach ( $result as $r )
		{
			$this->total_browser_hits += $r[ 'nhits' ];
			$browsers[ $r[ 'browkey' ] . '||' . $r[ 'version' ] ] = (isset($browsers[ $r[ 'browkey' ] . '||' . $r[ 'version' ] ]) ?
				$browsers[ $r[ 'browkey' ] . '||' . $r[ 'version' ] ] + $r[ 'nhits' ] : $r[ 'nhits' ]);
		}
		unset($result);


		$result              = $this->getData('os', $stat_idStr);
		$os_systems          = array ();
		$this->total_os_hits = 0;
		foreach ( $result as $r )
		{
			$this->total_os_hits += $r[ 'nhits' ];
			$os_systems[ $r[ 'oskey' ] ] = $os_systems[ $r[ 'oskey' ] ] + $r[ 'nhits' ];
		}
		unset($result);


		$result                = $this->getData('country', $stat_idStr);
		$countrys              = array ();
		$this->total_lang_hits = 0;
		foreach ( $result as $r )
		{
			$this->total_lang_hits += $r[ 'nhits' ];
			$countrys[ $r[ 'langkey' ] ] = $countrys[ $r[ 'langkey' ] ] + $r[ 'nhits' ];
		}
		unset($result);

		$result                  = $this->getData('robots', $stat_idStr);
		$spiders                 = array ();
		$this->total_spider_hits = 0;
		foreach ( $result as $r )
		{
			$this->total_spider_hits += $r[ 'nhits' ];
			$spiders[ $r[ 'spiderkey' ] ] = $spiders[ $r[ 'spiderkey' ] ] + $r[ 'nhits' ];
		}
		unset($result);

		$result                  = $this->getData('screen', $stat_idStr);
		$screensize              = array ();
		$this->total_screen_hits = 0;
		foreach ( $result as $r )
		{
			$this->total_screen_hits += $r[ 'nhits' ];
			$screensize[ $r[ 'screensize' ] ] = $screensize[ $r[ 'screensize' ] ] + $r[ 'nhits' ];
		}
		unset($result);

		$result                    = $this->getData('refferer', $stat_idStr);
		$refferer                  = array ();
		$this->total_refferer_hits = 0;
		foreach ( $result as $r )
		{
			$this->total_refferer_hits += $r[ 'nhits' ];
			if ( isset($refferer[ $r[ 'refferer' ] ]) )
			{
				$refferer[ $r[ 'refferer' ] ][ 'nhits' ] += $r[ 'nhits' ];
			}
			$refferer[ $r[ 'refferer' ] ] = $r;
		}
		unset($result);

		$data[ 'refferer' ]   = $this->formatData('refferer', $refferer);
		$data[ 'screensize' ] = $this->formatData('screensize', $screensize);
		$data[ 'browsers' ]   = $this->formatData('browser', $browsers);
		$data[ 'os' ]         = $this->formatData('os', $os_systems);
		$data[ 'spiders' ]    = $this->formatData('robots', $spiders);
		$data[ 'countrys' ]   = $this->formatData('country', $countrys);


		$data[ 'jqplot' ][ 'screensize' ] = $this->createJqPlotData('screensize', $data[ 'screensize' ]);
		$data[ 'jqplot' ][ 'browsers' ]   = $this->createJqPlotData('browsers', $data[ 'browsers' ]);
		$data[ 'jqplot' ][ 'os' ]         = $this->createJqPlotData('os', $data[ 'os' ]);
		$data[ 'jqplot' ][ 'spiders' ]    = $this->createJqPlotData('spiders', $data[ 'spiders' ]);
		$data[ 'jqplot' ][ 'country' ]    = $this->createJqPlotData('countrys', $data[ 'countrys' ]);


		$data[ 'jqplot' ][ 'hours' ]    = $this->createJqPlotData('hours', $data[ 'hours' ]);
		$data[ 'jqplot' ][ 'weekdays' ] = $this->createJqPlotData('weekdays', $data[ 'weeks' ]);
		$data[ 'jqplot' ][ 'days' ]     = $this->createJqPlotData('days', $data[ 'days' ]);

		Session::save('statistic_hours', $this->hTempArray);
		Session::save('statistic_weeks', $this->wTempArray);
		Session::save('statistic_days', $this->dTempArray);
		Session::save('statistic_jqPlot', $data[ 'jqplot' ]);
		Session::save('statistic_refferer', $data[ 'refferer' ]);
		Session::save('statistic_screensize', $data[ 'screensize' ]);
		Session::save('statistic_browsers', $data[ 'browsers' ]);
		Session::save('statistic_os', $data[ 'os' ]);
		Session::save('statistic_spiders', $data[ 'spiders' ]);
		Session::save('statistic_countrys', $data[ 'countrys' ]);

		return $data;
	}

	/*
	  $v = {"params":{
	  "axes":
	  {"xaxis":{
	  "ticks":["Europa"," ","Nordamerika"," ","Afrika"," "],
	  "labels":["Europa","Asien","Nordamerika","S\u00fcdamerika","Afrika","Andere"]},"yaxis":{"tickOptions":{"formatString":"%s"}},"y2axis":{"tickOptions":{"formatString":"%s$"}}},"series":[{"label":"Besuche","internalLabel":"nb_visits","yaxis":"yaxis"},{"label":"Aktionen","internalLabel":"nb_actions","yaxis":"yaxis"},{"label":"Konversionen","internalLabel":"nb_conversions","yaxis":"y2axis"},{"label":"Gesamteinnahmen","internalLabel":"revenue","yaxis":"yaxis"}],"seriesColors":["#5170AE","#F3A010","#CC3399","#9933CC","#80a033","#246AD2","#FD16EA","#49C100"]},"data":[[3289,1280,1264,168,111,171],[7202,2354,2842,253,167,306],[88,71,40,4,1,2],[136,131,68,6,3,4]],"tooltip":{"yUnits":["","","$"],"percentages":[[52,20,20,3,2,3],[55,18,22,2,1,2],[43,34,19,2,0,1],[39,38,20,2,1,1]]},"seriesPicker":{"selectableColumns":[{"column":"nb_visits","translation":"Besuche","displayed":true},{"column":"nb_actions","translation":"Aktionen","displayed":true},{"column":"nb_conversions","translation":"Konversionen","displayed":true},{"column":"revenue","translation":"Gesamteinnahmen","displayed":true}],"multiSelect":true}};
	 */

	/**
	 * @param       $type
	 * @param array $arr
	 * @return string
	 */
	public function createJqPlotData ( $type, $arr = array () )
	{

		shuffle($this->colors);

		$monthnum    = Session::get('statistic_month', 1);
		$currentyear = Session::get('statistic_year', date('Y'));
		$monthname   = Locales::getMonthName($monthnum, true);


		$_jqData = array ();
		$values  = array ();
		$percent = array ();


		$_jqData[ 'axes' ][ 'yaxis' ][ 'tickOptions' ]  = array (
			'formatString' => '%s'
		);
		$_jqData[ 'axes' ][ 'y2axis' ][ 'tickOptions' ] = array (
			'formatString' => '%s$'
		);
		$_jqData[ 'axes' ][ 'xaxis' ][ 'ticks' ]        = array ();


		$max   = 0;
		$total = 1;
		if ( $type == 'hours' || $type == 'days' || $type == 'weekdays' )
		{
			if ( $type == 'hours' )
			{
				$total = $this->total_hits_month;
			}
			else
			{
				foreach ( $arr as $x => $r )
				{
					$total += (int)$r;
				}
			}
		}

		foreach ( $arr as $x => $r )
		{
			if ( $type != 'hours' && $type != 'days' && $type != 'weekdays' )
			{
				$values[ ]  = (int)$r[ 'hits' ];
				$percent[ ] = $r[ 'percent' ];
			}
			elseif ( $type == 'hours' || $type == 'days' || $type == 'weekdays' )
			{
				$values[ ]  = (int)$r;
				$percent[ ] = sprintf('%01.2f', ((int)$r > 0 ? ((int)$r * 100) / $total : 0));
			}

			if ( $type == 'countrys' )
			{
				$_jqData[ 'axes' ][ 'xaxis' ][ 'ticks' ][ ]  = $r[ 'title' ];
				$_jqData[ 'axes' ][ 'xaxis' ][ 'labels' ][ ] = $r[ 'title' ];
				$_jqData[ 'series' ][ ]                      = array (
					'yaxis'         => 'yaxis',
					'label'         => trans('Hits'),
					'internalLabel' => 'country'
				);
			}
			elseif ( $type == 'browsers' )
			{
				$_jqData[ 'axes' ][ 'xaxis' ][ 'ticks' ][ ]  = $r[ 'title' ] . ' ' . $r[ 'version' ];
				$_jqData[ 'axes' ][ 'xaxis' ][ 'labels' ][ ] = $r[ 'title' ] . ' ' . $r[ 'version' ];
				$_jqData[ 'series' ][ ]                      = array (
					'yaxis'         => 'yaxis',
					'label'         => trans('Hits'),
					'internalLabel' => 'browsers'
				);
			}
			elseif ( $type == 'os' )
			{
				$_jqData[ 'axes' ][ 'xaxis' ][ 'ticks' ][ ]  = $r[ 'title' ];
				$_jqData[ 'axes' ][ 'xaxis' ][ 'labels' ][ ] = $r[ 'title' ];
				$_jqData[ 'series' ][ ]                      = array (
					'yaxis'         => 'yaxis',
					'label'         => trans('Hits'),
					'internalLabel' => 'os'
				);
			}
			elseif ( $type == 'screensize' )
			{
				$_jqData[ 'axes' ][ 'xaxis' ][ 'ticks' ][ ]  = $r[ 'title' ];
				$_jqData[ 'axes' ][ 'xaxis' ][ 'labels' ][ ] = $r[ 'title' ];
				$_jqData[ 'series' ][ ]                      = array (
					'yaxis'         => 'yaxis',
					'label'         => trans('Hits'),
					'internalLabel' => 'screensize'
				);
			}
			elseif ( $type == 'spiders' )
			{
				$_jqData[ 'axes' ][ 'xaxis' ][ 'ticks' ][ ]  = strip_tags($r[ 'title' ]);
				$_jqData[ 'axes' ][ 'xaxis' ][ 'labels' ][ ] = strip_tags($r[ 'title' ]);
				$_jqData[ 'series' ][ ]                      = array (
					'yaxis'         => 'yaxis',
					'label'         => trans('Hits'),
					'internalLabel' => 'spiders'
				);
			}
			elseif ( $type == 'hours' )
			{
				$_jqData[ 'axes' ][ 'xaxis' ][ 'ticks' ][ ]  = substr($x, 1) . ' ' . trans('Uhr');
				$_jqData[ 'axes' ][ 'xaxis' ][ 'labels' ][ ] = substr($x, 1) . ' ' . trans('Uhr');
				$_jqData[ 'series' ][ ]                      = array (
					'yaxis'         => 'yaxis',
					'label'         => trans('Hits'),
					'internalLabel' => 'hours'
				);
			}
			elseif ( $type == 'days' )
			{
				$_jqData[ 'axes' ][ 'xaxis' ][ 'ticks' ][ ]  = $x;
				$_jqData[ 'axes' ][ 'xaxis' ][ 'labels' ][ ] = $x . '. ' . $monthname;
				$_jqData[ 'series' ][ ]                      = array (
					'yaxis'         => 'yaxis',
					'label'         => trans('Hits'),
					'internalLabel' => 'days'
				);
			}
			elseif ( $type == 'weekdays' )
			{
				$name                                        = Locales::getDayName($x, true);
				$_jqData[ 'axes' ][ 'xaxis' ][ 'ticks' ][ ]  = $name;
				$_jqData[ 'axes' ][ 'xaxis' ][ 'labels' ][ ] = $name;
				$_jqData[ 'series' ][ ]                      = array (
					'yaxis'         => 'yaxis',
					'label'         => trans('Hits'),
					'internalLabel' => 'weekdays'
				);
			}
		}


		$dataLength = count($values);
		for ( $x = 0; $x < $dataLength; ++$x )
		{
			$_jqData[ 'seriesColors' ][ ] = '#' . $this->colors[ $x ];
		}

		$_jqData[ 'data' ]                        = array (
			$values
		);
		$_jqData[ 'tooltip' ][ 'yUnits' ]         = array (
			'',
			'',
			'%s$'
		);
		$_jqData[ 'tooltip' ][ 'percentages' ][ ] = $percent;

		if ( $type == 'country' )
		{
			$_jqData[ 'title' ]        = trans('Land');
			$_jqData[ 'seriesPicker' ] = array (
				'selectableColumns' => array (
					'column'      => 'country',
					"translation" => trans('Land'),
					'displayed'   => true
				)
			);
		}
		else if ( $type == 'browsers' )
		{
			$_jqData[ 'title' ]        = trans('Browser');
			$_jqData[ 'seriesPicker' ] = array (
				'selectableColumns' => array (
					'column'      => 'browsers',
					"translation" => trans('Browser'),
					'displayed'   => true
				)
			);
		}
		else if ( $type == 'os' )
		{
			$_jqData[ 'title' ]        = trans('Betriebssysteme');
			$_jqData[ 'seriesPicker' ] = array (
				'selectableColumns' => array (
					'column'      => 'os',
					"translation" => trans('Betriebssystem'),
					'displayed'   => true
				)
			);
		}
		else if ( $type == 'screensize' )
		{
			$_jqData[ 'title' ]        = trans('Auflösungen');
			$_jqData[ 'seriesPicker' ] = array (
				'selectableColumns' => array (
					'column'      => 'screensize',
					"translation" => trans('Auflösung'),
					'displayed'   => true
				)
			);
		}
		else if ( $type == 'spiders' )
		{
			$_jqData[ 'title' ]        = trans('Suchmaschienen');
			$_jqData[ 'seriesPicker' ] = array (
				'selectableColumns' => array (
					'column'      => 'spiders',
					"translation" => trans('Suchmaschienen'),
					'displayed'   => true
				)
			);
		}
		else if ( $type == 'hours' )
		{
			$_jqData[ 'title' ]        = sprintf(trans('Auswertung der Tageszeiten für %s %s'), $monthname, $currentyear);
			$_jqData[ 'seriesPicker' ] = array (
				'selectableColumns' => array (
					'column'      => 'hours',
					"translation" => trans('Uhrzeiten'),
					'displayed'   => true
				)
			);
		}
		else if ( $type == 'days' )
		{
			$_jqData[ 'title' ]        = sprintf(trans('Ansicht Tage für %s %s'), $monthname, $currentyear);
			$_jqData[ 'seriesPicker' ] = array (
				'selectableColumns' => array (
					'column'      => 'days',
					"translation" => trans('Tagesansicht'),
					'displayed'   => true
				)
			);
		}
		else if ( $type == 'weekdays' )
		{
			$_jqData[ 'title' ]        = sprintf(trans('Auswertung der Wochentage für %s %s'), $monthname, $currentyear);
			$_jqData[ 'seriesPicker' ] = array (
				'selectableColumns' => array (
					'column'      => 'weekdays',
					"translation" => trans('Wochentagsansicht'),
					'displayed'   => true
				)
			);
		}


		$_jqData[ 'seriesPicker' ][ 'selectableColumns' ] = array (
			$_jqData[ 'seriesPicker' ][ 'selectableColumns' ]
		);
		$_jqData[ 'seriesPicker' ][ 'multiSelect' ]       = false;


		return json_encode($_jqData);
	}

	/**
	 * get SQL Result
	 */
	public function getData ( $type = '', $stat_idStr = '' )
	{

		switch ( $type )
		{
			case 'browser':
				return $this->db->query('SELECT
                                              browkey AS browkey, `version`, SUM(hits) AS nhits
                                         FROM %tp%statistik_browser
                                         WHERE
                                                statid IN(' . $stat_idStr . ')
                                         GROUP BY browkey
                                         ORDER BY browkey ASC')->fetchAll();

				break;

			case 'os':
				return $this->db->query('SELECT
                                              oskey, SUM(hits) AS nhits
                                         FROM %tp%statistik_os
                                         WHERE
                                                statid IN(' . $stat_idStr . ')
                                         GROUP BY oskey
                                         ORDER BY oskey ASC')->fetchAll();
				break;

			case 'country':
				return $this->db->query('SELECT
                                              langkey, SUM(hits) AS nhits
                                         FROM %tp%statistik_country
                                         WHERE
                                                statid IN(' . $stat_idStr . ')
                                         GROUP BY langkey
                                         ORDER BY nhits DESC')->fetchAll();
				break;

			case 'screen':
				/**
				 * @todo screen statistic
				 */
				return $this->db->query('SELECT
                                              screensize, SUM(hits) AS nhits
                                         FROM %tp%statistik_screens
                                         WHERE
                                                statid IN(' . $stat_idStr . ')
                                         GROUP BY screensize
                                         ORDER BY nhits DESC')->fetchAll();

				break;

			case 'robots':
				return $this->db->query('SELECT
                                              spiderkey, SUM(hits) AS nhits
                                         FROM %tp%statistik_spiders
                                         WHERE
                                                statid IN(' . $stat_idStr . ')
                                         GROUP BY spiderkey
                                         ORDER BY nhits DESC')->fetchAll();
				break;

			case 'refferer':

				return $this->db->query('SELECT
                                                s.spiderkey, o.oskey, b.browkey, b.version, c.langkey,
                                              r.refferer, SUM(r.hits) AS nhits
                                         FROM %tp%statistik_refferer AS r
                                         LEFT JOIN %tp%statistik_spiders AS s ON(s.id = r.spiderid)
                                         LEFT JOIN %tp%statistik_os AS o ON(o.id = r.osid)
                                         LEFT JOIN %tp%statistik_browser AS b ON(b.id = r.browserid)
                                         LEFT JOIN %tp%statistik_country AS c ON(c.id = r.langid)
                                         WHERE
                                                r.statid IN(' . $stat_idStr . ') AND r.refferer != \'\'
                                         GROUP BY r.refferer
                                         ORDER BY nhits DESC LIMIT ' . $this->reffererLimit)->fetchAll();
				break;

			default:
				return $this->db->query('SELECT * FROM %tp%statistik_total
                                         WHERE month=? AND year=? ORDER BY hits DESC', $this->selectedMonth, $this->selectedYear)->fetchAll();
				break;
		}
	}

	/**
	 * return formated array
	 *
	 * @param string $type
	 * @param array  $arr
	 * @return array
	 */
	public function formatData ( $type, $arr = array () )
	{

		$formated = array ();

		switch ( $type )
		{
			case 'browser':
				/*
				  include_once(DATA_PATH . 'counter_data/browser_hashkeys.php');
				  foreach ( $BrowsersHashIDLib as $k => $v )
				  {
				  $BrowsersHashIDLib[ strtolower( $k ) ] = $v;
				  }
				 */
				foreach ( $arr as $key => $hits )
				{


					$keys  = explode('||', $key);
					$keyx  = $keys[ 0 ];
					$label = Tracking_AgentParser::getBrowserNameFromId($keyx);


					$title = ($label ? $label :
						trans('Unbekannt')); //isset( $BrowsersHashIDLib[ $keyx ] ) ? strip_tags( $BrowsersHashIDLib[ $keyx ] ) : trans( 'Unbekannt' ) . " ($key)";

					/*
					  $icon  = isset( $BrowsersHashIcon[ $keyx ] ) ? $BrowsersHashIcon[ $keyx ] : 'unknown';
					  if ( $key == 'AppleWebKit' )
					  {
					  $icon = 'safari';
					  }
					 */

					$icon = $this->iconLib_path . 'browsers/UNK.gif';
					if ( file_exists(ROOT_PATH . $this->iconLib_path . 'browsers/' . $keyx . '.gif') )
					{
						$icon = $this->iconLib_path . 'browsers/' . $keyx . '.gif';
					}


					$url     = '';
					$percent = sprintf('%01.2f', ($hits > 0 ? ($hits * 100) / $this->total_browser_hits : 0));

					$formated[ ] = array (
						'hits'    => $hits,
						'title'   => $title,
						'icon'    => $icon, //$this->iconLib_path . 'browser/' . $icon . '.png',
						'version' => $keys[ 1 ],
						'percent' => $percent,
						'url'     => $url
					);
				}

				break;
			case 'os':
				//include_once (DATA_PATH . 'counter_data/os_hashkeys.php');

				foreach ( $arr as $key => $hits )
				{

					$label = Tracking_AgentParser::getOperatingSystemNameFromId($key);
					$title = $label ? $label : trans('Unbekannt');

					$icon = $this->iconLib_path . 'os/UNK.gif';
					if ( file_exists(ROOT_PATH . $this->iconLib_path . 'os/' . $key . '.gif') )
					{
						$icon = $this->iconLib_path . 'os/' . $key . '.gif';
					}


					if ( file_exists(ROOT_PATH . $this->iconLib_path . 'os/' . $key . '.png') )
					{
						$icon = $this->iconLib_path . 'os/' . $key . '.png';
					}

					$percent     = sprintf('%01.2f', ($hits > 0 ? ($hits * 100) / $this->total_os_hits : 0));
					$formated[ ] = array (
						'hits'    => $hits,
						'title'   => $title,
						'icon'    => $icon, //$this->iconLib_path . 'os/' . str_replace( '/', '', $icon ) . '.png',
						'percent' => $percent
					);
				}


				break;


			case 'screensize':
				//include_once (DATA_PATH . 'counter_data/os_hashkeys.php');

				foreach ( $arr as $key => $hits )
				{

					$type  = $this->getScreenTypeFromResolution($key);
					$title = $key ? $key : trans('Unbekannt');

					$title = str_replace(';', ' x ', $title);

					$icon = $this->iconLib_path . 'screens/unknown.gif';
					if ( file_exists(ROOT_PATH . $this->iconLib_path . 'screens/' . $type . '.gif') )
					{
						$icon = $this->iconLib_path . 'screens/' . $type . '.gif';
					}

					$percent     = sprintf('%01.2f', ($hits > 0 ? ($hits * 100) / $this->total_screen_hits : 0));
					$formated[ ] = array (
						'hits'    => $hits,
						'title'   => $title,
						'icon'    => $icon, //$this->iconLib_path . 'os/' . str_replace( '/', '', $icon ) . '.png',
						'percent' => $percent
					);
				}


				break;


			case 'robots':
				include_once(DATA_PATH . 'counter_data/robots_hashkeys.php');
				include_once(DATA_PATH . 'counter_data/robots.php');
				foreach ( $arr as $key => $hits )
				{


					$title = isset($robots[ $key ]) ? $robots[ $key ][ 0 ] : ($key ? $key : trans('Unbekannt'));
					$icon  = false;

					if ( file_exists(ROOT_PATH . $this->iconLib_path . 'robots/r_' . $key . '.png') )
					{
						$icon = $this->iconLib_path . 'robots/r_' . $key . '.png';
					}

					/*

					  $keyEndWith = substr( $key, -3 );

					  foreach ( $robot as $r )
					  {
					  $s = explode( '|', $r );

					  if ( !$icon && stripos( $s[ 0 ], $key ) !== false || stripos( $s[ 1 ], $key ) !== false )
					  {

					  if ( !$icon && file_exists( PUBLIC_PATH . $this->iconLib_path . 'robots/robot_' . strtolower( $s[ 1 ] ) . '.png' ) )
					  {
					  $icon = $this->iconLib_path . 'robots/robot_' . strtolower( $s[ 1 ] ) . '.png';
					  }
					  elseif ( !$icon && file_exists( PUBLIC_PATH . $this->iconLib_path . 'robots/robot_' . substr( strtolower( $s[ 0 ] ), 0, strlen( $key ) ) . '.png' ) )
					  {
					  $icon = $this->iconLib_path . 'robots/robot_' . substr( strtolower( $s[ 0 ] ), 0, strlen( $key ) ) . '.png';
					  }
					  elseif ( !$icon && file_exists( PUBLIC_PATH . $this->iconLib_path . 'robots/robot_' . substr( strtolower( $s[ 0 ] ), 0, strlen( $key ) - 3 ) . '.png' ) )
					  {
					  $icon = $this->iconLib_path . 'robots/robot_' . substr( strtolower( $s[ 0 ] ), 0, strlen( $key ) - 3 ) . '.png';
					  }
					  elseif ( !$icon && file_exists( PUBLIC_PATH . $this->iconLib_path . 'robots/robot_' . $key . '.png' ) )
					  {
					  $icon = $this->iconLib_path . 'robots/robot_' . $key . '.png';
					  }
					  }
					  }

					 */


					if ( !$icon )
					{
						$icon = $this->iconLib_path . 'robots/r_robot.png';
					}

					$url         = '';
					$percent     = sprintf('%01.2f', ($hits > 0 ? ($hits * 100) / $this->total_spider_hits : 0));
					$formated[ ] = array (
						'hits'    => $hits,
						'percent' => $percent,
						'title'   => $title/* . $key */,
						'icon'    => $icon
					);
				}

				break;


			case 'refferer':

				include DATA_PATH . 'system/countrys.php';


				$unknown = trans('Unbekannt');


				foreach ( $arr as $key => $r )
				{
					if ( empty($r[ 'refferer' ]) )
					{
						continue;
					}
					$langtitle = $langicon = $ostitle = $osicon = $browsertitle = $browsericon = false;


					if ( !empty($r[ 'langkey' ]) && isset($GLOBALS[ 'country_array' ][ strtolower($r[ 'langkey' ]) ]) )
					{
						$langtitle = $GLOBALS[ 'country_array' ][ strtolower($r[ 'langkey' ]) ];
						$langicon  = HTML_URL . 'img/flags/' . strtolower($r[ 'langkey' ]) . '.png';
					}


					if ( !empty($r[ 'oskey' ]) )
					{
						$label   = Tracking_AgentParser::getOperatingSystemNameFromId($r[ 'oskey' ]);
						$ostitle = $label ? $label : $unknown;
						$osicon  = $this->iconLib_path . 'os/UNK.gif';
						if ( file_exists(ROOT_PATH . $this->iconLib_path . 'os/' . $r[ 'oskey' ] . '.gif') )
						{
							$osicon = $this->iconLib_path . 'os/' . $r[ 'oskey' ] . '.gif';
						}
					}

					if ( !empty($r[ 'browkey' ]) )
					{
						$label        = Tracking_AgentParser::getBrowserNameFromId($r[ 'browkey' ]);
						$browsertitle = $label ? $label : $unknown;
						$browsericon  = $this->iconLib_path . 'browsers/UNK.gif';
						if ( file_exists(ROOT_PATH . $this->iconLib_path . 'browsers/' . $r[ 'browkey' ] . '.gif') )
						{
							$browsericon = $this->iconLib_path . 'browsers/' . $r[ 'browkey' ] . '.gif';
						}
					}


					$hits    = $r[ 'nhits' ];
					$percent = sprintf('%01.2f', ($hits > 0 ? ($hits * 100) / $this->total_refferer_hits : 0));


					$formated[ ] = array (
						'hits'         => $hits,
						'percent'      => $percent,
						'refferer'     => $r[ 'refferer' ],
						'ostitle'      => $ostitle,
						'osicon'       => $osicon,
						'browsertitle' => $browsertitle,
						'browsericon'  => $browsericon,
						'countrytitle' => $langtitle,
						'countryicon'  => $langicon,
					);
				}


				break;


			case 'country':

				include DATA_PATH . 'system/countrys.php';

				foreach ( $arr as $key => $hits )
				{
					$title = trans('Unbekannt');
					$icon  = '';

					if ( isset($GLOBALS[ 'country_array' ][ strtolower($key) ]) )
					{
						$title = $GLOBALS[ 'country_array' ][ strtolower($key) ];
						$icon  = strtolower($key);
					}

					$percent = sprintf('%01.2f', ($hits > 0 ? ($hits * 100) / $this->total_lang_hits : 0));

					$formated[ ] = array (
						'hits'    => $hits,
						'percent' => $percent,
						'icon'    => (!$icon ? '' : HTML_URL . 'img/flags/' . $icon . '.png'),
						'title'   => ($title == '-' ? $key : $title)
					);
				}
				break;
		}

		return $formated;
	}

	/**
	 * @param $resolution
	 * @return string
	 */
	protected function getScreenTypeFromResolution ( $resolution )
	{

		if ( $resolution === 'unknown' || empty($resolution) )
		{
			return $resolution;
		}

		$resolution = str_replace(';', 'x', $resolution);

		$width  = (int)substr($resolution, 0, strpos($resolution, 'x'));
		$height = (int)substr($resolution, strpos($resolution, 'x' + 1));
		$ratio  = Library::secureDiv($width, $height);

		if ( $width < 640 )
		{
			$name = 'mobile';
		}
		elseif ( $ratio < 1.4 )
		{
			$name = 'normal';
		}
		else if ( $ratio < 2 )
		{
			$name = 'wide';
		}
		else
		{
			$name = 'dual';
		}

		return $name;
	}

	/**
	 * convert html colors to rgb colors
	 *
	 * @param string $color
	 * @return array
	 */
	protected function html2rgb ( $color )
	{

		if ( $color[ 0 ] == '#' )
		{
			$color = substr($color, 1);
		}

		if ( strlen($color) == 6 )
		{
			list($r, $g, $b) = array (
				$color[ 0 ] . $color[ 1 ],
				$color[ 2 ] . $color[ 3 ],
				$color[ 4 ] . $color[ 5 ]
			);
		}
		elseif ( strlen($color) == 3 )
		{
			list($r, $g, $b) = array (
				$color[ 0 ] . $color[ 0 ],
				$color[ 1 ] . $color[ 1 ],
				$color[ 2 ] . $color[ 2 ]
			);
		}
		else
		{
			return false;
		}

		$r = hexdec($r);
		$g = hexdec($g);
		$b = hexdec($b);

		return array (
			$r,
			$g,
			$b
		);
	}

	/**
	 * @return array
	 */
	public function getRGBColors ()
	{


		shuffle($this->colors);
		$rgb_colors = array ();
		foreach ( $this->colors as $color )
		{
			$rgb           = $this->html2rgb($color);
			$rgb_colors[ ] = array (
				'R' => $rgb[ 0 ],
				'G' => $rgb[ 1 ],
				'B' => $rgb[ 2 ]
			);
		}

		return $rgb_colors;
	}

}
