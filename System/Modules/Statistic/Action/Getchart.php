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
 * @file         Getchart.php
 */
class Statistic_Action_Getchart extends Statistic_Helper_Base
{

	private $chartHeight = 600;

	private $chartWidth = 800;

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$this->iconLib_path = 'img/statistic/';
		$type               = HTTP::input('type');

		$this->selectedMonth = ((int)HTTP::input('month' > 0) ? (int)HTTP::input('month') : date('m'));
		$this->selectedYear  = ((int)HTTP::input('year' > 0) ? (int)HTTP::input('year') : date('Y'));


		$this->setInput('month', $this->selectedMonth);
		$this->setInput('year', $this->selectedYear);

		Session::save('statistic_month', '' . $this->selectedMonth);
		Session::save('statistic_year', '' . $this->selectedYear);


		/**
		 * allowed types (bar,line,pie)
		 */
		$charttype = HTTP::input('charttype') != '' ? HTTP::input('charttype') : 'bar';


		if ( !in_array($charttype, array (
		                                 'bar',
		                                 'barup',
		                                 'line',
		                                 'pie'
		                           ))
		)
		{
			$charttype = 'bar';
		}


		$chartwidth  = (int)HTTP::input('width') > 0 ? (int)HTTP::input('width') : 800;
		$chartheight = (int)HTTP::input('height') > 0 ? (int)HTTP::input('height') : 600;

		$this->chartWidth  = $chartwidth;
		$this->chartHeight = $chartheight;


		$this->initStat();

		//$result = Session::get('statistic_' . $type, null);
		$resultd = $this->getMonthYear();
		if ( !is_array($resultd[ $type ]) )
		{
			die('No result');
		}
		$result = $resultd[ $type ];


		#print_r($result); exit;
		#      error_reporting( E_ALL );
		#      Library::enableErrorHandling();
		#      include(VENDOR_PATH . 'pChart/pCache.php');
		#       include(VENDOR_PATH . 'pChart/pData.php');
		#     include(VENDOR_PATH . 'pChart/pChart.php');


		include(VENDOR_PATH . 'pChart/autoloader.php');

		$font      = VENDOR_PATH . 'pChart/fonts/tahoma.ttf';
		$titlefont = VENDOR_PATH . 'pChart/fonts/verdana.ttf';
		$rgbcolors = $this->getRGBColors();

		$chartData = new pData();
		$chartData->loadPalette(VENDOR_PATH . "palettes/autumn.color", true);
		//shuffle($rgbcolors);
		$hits    = array ();
		$labels  = array ();
		$palette = array ();


		$monthnum    = Session::get('statistic_month', 0);
		$currentyear = Session::get('statistic_year', date('Y'));
		$monthname   = Locales::getMonthName($monthnum, true);


		$mode_barUpHeight = $chartheight - 150;
		$mode_barUpWidth  = $chartwidth - 60;

		$mode_barHeight = $chartheight - 60;
		$mode_barWidth  = $chartwidth - 30;


		$mode_lineHeight = $chartheight - 200;
		$mode_lineWidth  = $chartwidth - 80;


		if ( $charttype === 'pie' && $type != 'os' && $type != 'spiders' && $type != 'browsers' && $type != 'countrys' )
		{

		}

		switch ( $type )
		{
			case 'os':
				foreach ( $result as $idx => $r )
				{
					$rgbcolors[ $idx ][ "Alpha" ] = 100;
					$palette[ ]                   = $rgbcolors[ $idx ];
					$hits[ ]                      = (int)$r[ 'hits' ];
					$labels[ ]                    = trim(strip_tags($r[ 'title' ]));
				}
				/*
				  $this->buildVerticalBarGraph( $hits, $labels, array(
				  'legend' => false,
				  'title'  => trans( 'Betriebssysteme' ) . ' - ' . $monthname . ' ' . $currentyear,
				  'yLabel' => trans( 'hits' ),
				  // 'xLabel' => trans( 'Betriebssystem' )
				  ) );

				  $this->buildLineGraph( $hits, $labels, array(
				  'legend' => false,
				  'title'  => trans( 'Betriebssysteme' ) . ' - ' . $monthname . ' ' . $currentyear,
				  'yLabel' => trans( 'hits' ),
				  // 'xLabel' => trans( 'Betriebssystem' )
				  )
				  );
				 */


				$chartData->addPoints($hits, "Hits");
				$chartData->setAxisName(0, "Hits");

				$chartData->addPoints($labels, "OS");
				//$chartData->setSerieDescription("OS", "OS");
				$chartData->setAbscissa("OS");


				$chartImg            = new pImage($chartwidth, $chartheight, $chartData, true);
				$chartImg->Antialias = false;
				$chartImg->setFontProperties(array (
				                                   "FontName" => $font,
				                                   "FontSize" => 8
				                             ));


				$barScale = array (
					"CycleBackground" => true,
					"DrawSubTicks"    => true,
					'LabelRotation'   => 25,
					"GridR"           => 0,
					"GridG"           => 0,
					"GridB"           => 0,
					"GridAlpha"       => 5,
				);

				if ( $charttype === 'barup' )
				{
					/* Turn on shadow computing */
					#$chartImg->setShadow(TRUE, array("X" => 1, "Y" => 1, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));


					/* Draw the chart scale */
					$chartImg->setGraphArea(80, 30, $mode_barUpWidth, $mode_barUpHeight);

					$barScale[ 'LabelRotation' ] = 35;
					$barScale[ 'Mode' ]          = SCALE_MODE_START0;
					$barScale[ 'Pos' ]           = SCALE_POS_LEFTRIGHT;
					$chartImg->drawScale($barScale);
				}
				elseif ( $charttype === 'bar' )
				{
					/* Turn on shadow computing */
					#$chartImg->setShadow(TRUE, array("X" => 1, "Y" => 1, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));

					/* Draw the chart scale */
					$chartImg->setGraphArea(120, 30, $mode_barWidth, $mode_barHeight);


					$barScale[ 'Floating' ] = true;
					$barScale[ 'YMargin' ]  = 0;
					$barScale[ 'XMargin' ]  = 10;
					$barScale[ 'Pos' ]      = SCALE_POS_TOPBOTTOM;


					$chartImg->drawScale($barScale);
				}
				elseif ( $charttype === 'line' )
				{

					/* Draw the chart scale */
					$barScale[ 'Pos' ]      = SCALE_POS_LEFTRIGHT;
					$barScale[ 'Mode' ]     = SCALE_MODE_FLOATING;
					$barScale[ 'Floating' ] = true;
					$chartImg->setGraphArea(80, 30, $mode_lineWidth, $mode_lineHeight);
					$chartImg->drawScale($barScale);

					if ( count($hits) )
					{
						$this->drawLine($chartImg);
					}
				}
				elseif ( $charttype === 'pie' )
				{

					$this->drawPie($chartwidth, $chartheight, $palette, $chartData, $chartImg);
				}


				if ( $charttype === 'barup' || $charttype === 'bar' )
				{
					/* Draw the chart */
					$chartImg->drawBarChart(array (
					                              "DisplayPos"     => LABEL_POS_INSIDE,
					                              "DisplayValues"  => true,
					                              "Rounded"        => false,
					                              "Surrounding"    => 30,
					                              "OverrideColors" => $palette
					                        ));
				}

				/* Write the legend */
				#$chartImg->drawLegend(570, 215, array("Style" => LEGEND_NOBORDER, "Mode" => LEGEND_HORIZONTAL));
				$chartImg->stroke();


				break;


			case 'screensize':
				foreach ( $result as $idx => $r )
				{
					$rgbcolors[ $idx ][ "Alpha" ] = 100;
					$palette[ ]                   = $rgbcolors[ $idx ];
					$hits[ ]                      = $r[ 'hits' ];
					$labels[ ]                    = trim(strip_tags($r[ 'title' ]));
				}


				$chartData->addPoints($hits, "Hits");
				$chartData->setAxisName(0, "Hits");

				$chartData->addPoints($labels, "Auflösungen");
				//$chartData->setSerieDescription("Browsers", "Browsers");
				$chartData->setAbscissa("Auflösungen");


				$chartImg            = new pImage($chartwidth, $chartheight, $chartData, true);
				$chartImg->Antialias = false;

				$chartImg->setFontProperties(array (
				                                   "FontName" => $font,
				                                   "FontSize" => 8
				                             ));


				$barScale = array (
					"CycleBackground" => true,
					"DrawSubTicks"    => true,
					'LabelRotation'   => 25,
					"GridR"           => 0,
					"GridG"           => 0,
					"GridB"           => 0,
					"GridAlpha"       => 5,
				);

				if ( $charttype === 'barup' )
				{
					/* Turn on shadow computing */
					#$chartImg->setShadow(TRUE, array("X" => 1, "Y" => 1, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));

					/* Draw the chart scale */
					$chartImg->setGraphArea(80, 30, $mode_barUpWidth, $mode_barUpHeight);


					$barScale[ 'LabelRotation' ] = 35;
					$barScale[ 'Mode' ]          = SCALE_MODE_START0;
					$barScale[ 'Pos' ]           = SCALE_POS_LEFTRIGHT;
					$chartImg->drawScale($barScale);
				}
				elseif ( $charttype === 'bar' )
				{


					/* Turn on shadow computing */
					#$chartImg->setShadow(TRUE, array("X" => 1, "Y" => 1, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 1));


					/* Draw the chart scale */
					$chartImg->setGraphArea(120, 30, $mode_barWidth, $mode_barHeight);

					$barScale[ 'Floating' ] = true;
					$barScale[ 'YMargin' ]  = 0;
					$barScale[ 'XMargin' ]  = 10;
					$barScale[ 'Pos' ]      = SCALE_POS_TOPBOTTOM;


					$chartImg->drawScale($barScale);
				}
				elseif ( $charttype === 'line' )
				{
					/* Turn on shadow computing */
					//$chartImg->setShadow(TRUE, array("X" => 1, "Y" => 1, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));

					/* Draw the chart scale */
					$barScale[ 'Pos' ]      = SCALE_POS_LEFTRIGHT;
					$barScale[ 'Mode' ]     = SCALE_MODE_START0;
					$barScale[ 'Floating' ] = true;
					$chartImg->setGraphArea(80, 30, $mode_lineWidth, $mode_lineHeight);
					$chartImg->drawScale($barScale);
					$this->drawLine($chartImg);
				}
				elseif ( $charttype === 'pie' )
				{

					$this->drawPie($chartwidth, $chartheight, $palette, $chartData, $chartImg);
				}


				if ( $charttype === 'barup' || $charttype === 'bar' )
				{
					/* Draw the chart */
					$chartImg->drawBarChart(array (
					                              "DisplayPos"     => LABEL_POS_INSIDE,
					                              "DisplayValues"  => true,
					                              "Rounded"        => false,
					                              "Surrounding"    => 30,
					                              "OverrideColors" => $palette
					                        ));
				}
				/* Write the legend */
				#$chartImg->drawLegend(570, 215, array("Style" => LEGEND_NOBORDER, "Mode" => LEGEND_HORIZONTAL));
				$chartImg->stroke();


				break;
			case 'browsers':

				foreach ( $result as $idx => $r )
				{
					$rgbcolors[ $idx ][ "Alpha" ] = 100;
					$palette[ ]                   = $rgbcolors[ $idx ];
					$hits[ ]                      = $r[ 'hits' ];
					$labels[ ]                    = trim(strip_tags($r[ 'title' ]));
				}
				/*
				  $chartData->AddPoint( $hits, "Hits" );
				  $chartData->SetSerieName( "Hits", "Hits" );
				  $chartData->SetYAxisName( "OS" );
				  $chartData->AddAllSeries();
				  $chartData->SetAbsciseLabelSerie();

				  $chartImg = new pChart( $chartwidth, $chartheight );
				  $chartImg->setFontProperties( $font, 8 );


				  $chartImg->setGraphArea( 80, 30, $mode_lineWidth, $mode_lineHeight );

				  $chartImg->drawFilledRoundedRectangle( 7, 7, 693, 223, 5, 240, 240, 240 );
				  $chartImg->drawRoundedRectangle( 5, 5, 695, 225, 5, 230, 230, 230 );
				  $chartImg->drawGraphArea( 255, 255, 255, TRUE );
				  $chartImg->drawScale( $chartData->GetData(), $chartData->GetDataDescription(), SCALE_NORMAL, 150, 150, 150, TRUE, 0, 2 );
				  $chartImg->drawGrid( 4, TRUE, 230, 230, 230, 50 );




				  // Draw the 0 line
				  $chartImg->setFontProperties( "Fonts/tahoma.ttf", 6 );
				  $chartImg->drawTreshold( 0, 143, 55, 72, TRUE, TRUE );


				  // Draw the line graph
				  $chartImg->drawLineGraph( $chartData->GetData(), $chartData->GetDataDescription() );
				  $chartImg->drawPlotGraph( $chartData->GetData(), $chartData->GetDataDescription(), 3, 2, 255, 255, 255 );

				  // Finish the graph
				  $chartImg->setFontProperties( "Fonts/tahoma.ttf", 8 );
				  $chartImg->drawLegend( 75, 35, $chartData->GetDataDescription(), 255, 255, 255 );
				  $chartImg->setFontProperties( "Fonts/tahoma.ttf", 10 );
				  $chartImg->drawTitle( 60, 22, "example 1", 50, 50, 50, 585 );


				  $chartImg->Stroke();

				  exit;

				 */
				$chartData->addPoints($hits, "Hits");
				$chartData->setAxisName(0, "Hits");

				$chartData->addPoints($labels, "Browsers");
				//$chartData->setSerieDescription("Browsers", "Browsers");
				$chartData->setAbscissa("Browsers");


				$chartImg            = new pImage($chartwidth, $chartheight, $chartData, true);
				$chartImg->Antialias = false;

				$chartImg->setFontProperties(array (
				                                   "FontName" => $font,
				                                   "FontSize" => 8
				                             ));


				$barScale = array (
					"CycleBackground" => true,
					"DrawSubTicks"    => true,
					'LabelRotation'   => 25,
					"GridR"           => 0,
					"GridG"           => 0,
					"GridB"           => 0,
					"GridAlpha"       => 5,
				);

				if ( $charttype === 'barup' )
				{
					/* Turn on shadow computing */
					#$chartImg->setShadow(TRUE, array("X" => 1, "Y" => 1, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));

					/* Draw the chart scale */
					$chartImg->setGraphArea(80, 30, $mode_barUpWidth, $mode_barUpHeight);


					$barScale[ 'LabelRotation' ] = 35;
					$barScale[ 'Mode' ]          = SCALE_MODE_START0;
					$barScale[ 'Pos' ]           = SCALE_POS_LEFTRIGHT;
					$chartImg->drawScale($barScale);
				}
				elseif ( $charttype === 'bar' )
				{


					/* Turn on shadow computing */
					#$chartImg->setShadow(TRUE, array("X" => 1, "Y" => 1, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 1));


					/* Draw the chart scale */
					$chartImg->setGraphArea(120, 30, $mode_barWidth, $mode_barHeight);

					$barScale[ 'Floating' ] = true;
					$barScale[ 'YMargin' ]  = 0;
					$barScale[ 'XMargin' ]  = 10;
					$barScale[ 'Pos' ]      = SCALE_POS_TOPBOTTOM;


					$chartImg->drawScale($barScale);
				}
				elseif ( $charttype === 'line' )
				{
					/* Turn on shadow computing */
					//$chartImg->setShadow(TRUE, array("X" => 1, "Y" => 1, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));

					/* Draw the chart scale */
					$barScale[ 'Pos' ]      = SCALE_POS_LEFTRIGHT;
					$barScale[ 'Mode' ]     = SCALE_MODE_START0;
					$barScale[ 'Floating' ] = true;
					$chartImg->setGraphArea(80, 30, $mode_lineWidth, $mode_lineHeight);
					$chartImg->drawScale($barScale);
					$this->drawLine($chartImg);
				}
				elseif ( $charttype === 'pie' )
				{

					$this->drawPie($chartwidth, $chartheight, $palette, $chartData, $chartImg);
				}


				if ( $charttype === 'barup' || $charttype === 'bar' )
				{
					/* Draw the chart */
					$chartImg->drawBarChart(array (
					                              "DisplayPos"     => LABEL_POS_INSIDE,
					                              "DisplayValues"  => true,
					                              "Rounded"        => false,
					                              "Surrounding"    => 30,
					                              "OverrideColors" => $palette
					                        ));
				}
				/* Write the legend */
				#$chartImg->drawLegend(570, 215, array("Style" => LEGEND_NOBORDER, "Mode" => LEGEND_HORIZONTAL));
				$chartImg->stroke();
				break;


			case 'spiders':

				foreach ( $result as $idx => $r )
				{
					$rgbcolors[ $idx ][ "Alpha" ] = 100;
					$palette[ ]                   = $rgbcolors[ $idx ];
					$hits[ ]                      = $r[ 'hits' ];
					$labels[ ]                    = trim(strip_tags($r[ 'title' ]));
				}

				$chartData->addPoints($hits, "Hits");
				$chartData->setAxisName(0, "Hits");

				$chartData->addPoints($labels, "Spider");
				$chartData->setAbscissa("Spider");


				$chartImg            = new pImage($chartwidth, $chartheight, $chartData, true);
				$chartImg->Antialias = false;


				$chartImg->setFontProperties(array (
				                                   "FontName" => $font,
				                                   "FontSize" => 8
				                             ));


				$barScale = array (
					"CycleBackground" => true,
					"DrawSubTicks"    => true,
					'LabelRotation'   => 25,
					"GridR"           => 0,
					"GridG"           => 0,
					"GridB"           => 0,
					"GridAlpha"       => 5,
				);

				if ( $charttype === 'barup' )
				{
					/* Turn on shadow computing */
					# $chartImg->setShadow(TRUE, array("X" => 1, "Y" => 1, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));

					/* Draw the chart scale */
					$chartImg->setGraphArea(80, 30, $mode_barUpWidth, $mode_barUpHeight);

					$barScale[ 'LabelRotation' ] = 35;
					$barScale[ 'Mode' ]          = SCALE_MODE_START0;
					$barScale[ 'Pos' ]           = SCALE_POS_LEFTRIGHT;
					$chartImg->drawScale($barScale);
				}
				elseif ( $charttype === 'bar' )
				{
					/* Turn on shadow computing */
					#    $chartImg->setShadow(TRUE, array("X" => 1, "Y" => 1, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));


					/* Draw the chart scale */
					$chartImg->setGraphArea(120, 30, $mode_barWidth, $mode_barHeight);


					$barScale[ 'Floating' ] = true;
					$barScale[ 'YMargin' ]  = 0;
					$barScale[ 'XMargin' ]  = 5;
					$barScale[ 'Pos' ]      = SCALE_POS_TOPBOTTOM;

					$chartImg->drawScale($barScale);
				}
				elseif ( $charttype === 'line' )
				{
					/* Draw the chart scale */
					$barScale[ 'Pos' ]      = SCALE_POS_LEFTRIGHT;
					$barScale[ 'Mode' ]     = SCALE_MODE_START0;
					$barScale[ 'Floating' ] = true;

					$chartImg->setGraphArea(80, 30, $mode_lineWidth, $mode_lineHeight);
					$chartImg->drawScale($barScale);
					$this->drawLine($chartImg);
				}
				elseif ( $charttype === 'pie' )
				{

					$this->drawPie($chartwidth, $chartheight, $palette, $chartData, $chartImg);
				}


				if ( $charttype === 'barup' || $charttype === 'bar' )
				{
					/* Draw the chart */
					$chartImg->drawBarChart(array (
					                              "DisplayPos"     => LABEL_POS_INSIDE,
					                              "DisplayValues"  => true,
					                              "Rounded"        => false,
					                              "Surrounding"    => 30,
					                              "OverrideColors" => $palette
					                        ));
				}

				$chartImg->stroke();
				break;


			case 'countrys':

				include DATA_PATH . 'system/countrys.php';

				foreach ( $result as $idx => $r )
				{
					$rgbcolors[ $idx ][ "Alpha" ] = 100;
					$palette[ ]                   = $rgbcolors[ $idx ];
					$hits[ ]                      = $r[ 'hits' ];
					$labels[ ]                    = trim(strip_tags($r[ 'title' ]));
				}

				$chartData->addPoints($hits, "Hits");
				$chartData->setAxisName(0, "Hits");

				$chartData->addPoints($labels, "Land");
				$chartData->setAbscissa("Land");


				$chartImg            = new pImage($chartwidth, $chartheight, $chartData, true);
				$chartImg->Antialias = false;


				$chartImg->setFontProperties(array (
				                                   "FontName" => $font,
				                                   "FontSize" => 8
				                             ));


				$barScale = array (
					"CycleBackground" => true,
					"DrawSubTicks"    => true,
					'LabelRotation'   => 25,
					"GridR"           => 0,
					"GridG"           => 0,
					"GridB"           => 0,
					"GridAlpha"       => 5,
				);


				if ( $charttype === 'barup' )
				{
					/* Turn on shadow computing */
					#    $chartImg->setShadow(TRUE, array("X" => 1, "Y" => 1, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));

					/* Draw the chart scale */
					$chartImg->setGraphArea(80, 30, $mode_barUpWidth, $mode_barUpHeight);


					$barScale[ 'LabelRotation' ] = 35;
					$barScale[ 'Mode' ]          = SCALE_MODE_START0;
					$barScale[ 'Pos' ]           = SCALE_POS_LEFTRIGHT;
					$chartImg->drawScale($barScale);
				}
				elseif ( $charttype === 'bar' )
				{
					/* Turn on shadow computing */
					#    $chartImg->setShadow(TRUE, array("X" => 1, "Y" => 1, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));


					/* Draw the chart scale */
					$chartImg->setGraphArea(120, 30, $mode_barWidth, $mode_barHeight);


					$barScale[ 'Floating' ] = true;
					$barScale[ 'YMargin' ]  = 0;
					$barScale[ 'XMargin' ]  = 0;
					$barScale[ 'Pos' ]      = SCALE_POS_TOPBOTTOM;

					$chartImg->drawScale($barScale);
				}
				elseif ( $charttype === 'line' )
				{
					/* Draw the chart scale */
					$barScale[ 'Pos' ]      = SCALE_POS_LEFTRIGHT;
					$barScale[ 'Mode' ]     = SCALE_MODE_START0;
					$barScale[ 'Floating' ] = true;
					$chartImg->setGraphArea(80, 30, $mode_lineWidth, $mode_lineHeight);
					$chartImg->drawScale($barScale);
					$this->drawLine($chartImg);
				}
				elseif ( $charttype === 'pie' )
				{

					$this->drawPie($chartwidth, $chartheight, $palette, $chartData, $chartImg);
				}


				if ( $charttype === 'barup' || $charttype === 'bar' )
				{
					/* Draw the chart */
					$chartImg->drawBarChart(array (
					                              "DisplayPos"     => LABEL_POS_INSIDE,
					                              "DisplayValues"  => true,
					                              "Rounded"        => false,
					                              "Surrounding"    => 30,
					                              "OverrideColors" => $palette
					                        ));
				}

				$chartImg->stroke();
				break;


			case 'days':
				$x = 0;
				#print_r($result);
				foreach ( $result as $day => $_hits )
				{
					$rgbcolors[ $x ][ "Alpha" ] = 100;
					$palette[ $x ]              = $rgbcolors[ $x ];
					$hits[ $x ]                 = $_hits ? $_hits : 0;
					#$name = Locales::getDayName($day, true);
					$labels[ $x ] = $day . '.';


					$x++;
				}


				$chartData->addPoints($hits, "Hits");
				$chartData->setAxisName(0, "Hits");

				$chartData->addPoints($labels, "Tage");
				$chartData->setSerieDescription("Tage", "Tag");
				$chartData->setAbscissa("Tage");


				$chartImg            = new pImage($chartwidth, $chartheight, $chartData, true);
				$chartImg->Antialias = false;

				$chartImg->setFontProperties(array (
				                                   "FontName" => $font,
				                                   "FontSize" => 6
				                             ));
				/* Turn on shadow computing */
				#$chartImg->setShadow(TRUE, array("X" => 2, "Y" => 1, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));

				$chartImg->setGraphArea(90, 60, $chartwidth - 50, $chartheight - 30);


				$charttype = ($charttype == 'bar' ? 'barup' : $charttype);
				if ( $charttype !== 'pie' )
				{


					$chartImg->drawScale(array (
					                           "CycleBackground" => true,
					                           'Mode'            => SCALE_MODE_START0,
					                           "DrawSubTicks"    => true,
					                           "GridR"           => 0,
					                           "GridG"           => 0,
					                           "GridB"           => 0,
					                           "GridAlpha"       => 10
					                     ));
				}


				/* Write the chart title */

				$chartImg->drawText($chartwidth / 2, 20, sprintf(trans('Tage für %s %s'), $monthname, $currentyear), array (
				                                                                                                           "FontSize" => 12,
				                                                                                                           "Align"    => TEXT_ALIGN_BOTTOMMIDDLE
				                                                                                                     ));


				$chartImg->setFontProperties(array (
				                                   "FontName" => $titlefont,
				                                   "FontSize" => 8
				                             ));


				if ( $charttype === 'barup' )
				{
					$chartImg->setFontProperties(array (
					                                   "FontName" => $font,
					                                   "FontSize" => 8
					                             ));
					$settings = array (
						"FontSize"       => 6,
						"Gradient"       => true,
						"DisplayPos"     => LABEL_POS_INSIDE,
						"DisplayValues"  => true,
						"DisplayR"       => 0,
						"DisplayG"       => 0,
						"DisplayB"       => 0,
						"DisplayShadow"  => false,
						"Surrounding"    => 30,
						"OverrideColors" => $palette,
						'Mode'           => SCALE_MODE_START0
					);
					$chartImg->drawBarChart($settings);
				}
				elseif ( $charttype === 'line' )
				{
					$chartImg->setFontProperties(array (
					                                   "FontName" => $font,
					                                   "FontSize" => 7
					                             ));
					$this->drawLine($chartImg);
				}
				elseif ( $charttype === 'pie' )
				{
					$chartImg->setGraphArea(118, 30, $chartwidth, $chartheight);
					$this->drawPie($chartwidth, $chartheight, $palette, $chartData, $chartImg);
				}


				$chartImg->stroke();

				break;
			case 'weeks':

				$x = 0;
				foreach ( $result as $weekday => $_hits )
				{
					$rgbcolors[ $x ][ "Alpha" ] = 100;
					$palette[ $x ]              = $rgbcolors[ $x ];
					$hits[ $x ]                 = $_hits;
					$name                       = Locales::getDayName($weekday, true);
					$labels[ $x ]               = $name;
					$x++;
				}


				$chartData->addPoints($hits, "Hits");
				$chartData->setAxisName(0, "Hits");

				$chartData->addPoints($labels, "wTage");
				$chartData->setSerieDescription("wTage", "Tag");
				$chartData->setAbscissa("wTage");


				$chartImg            = new pImage($chartwidth, $chartheight, $chartData, true);
				$chartImg->Antialias = false;
				$chartImg->setFontProperties(array (
				                                   "FontName" => $font,
				                                   "FontSize" => 8
				                             ));


				/* Turn on shadow computing */
				#$chartImg->setShadow(TRUE, array("X" => 2, "Y" => 1, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));
				#$chartImg->drawGradientArea(0, 0, $chartwidth, $chartheight, DIRECTION_HORIZONTAL, array("StartR" => 240, "StartG" => 240, "StartB" => 240, "EndR" => 180, "EndG" => 180, "EndB" => 180, "Alpha" => 20));

				$charttype = ($charttype == 'bar' ? 'barup' : $charttype);

				if ( $charttype !== 'pie' )
				{

					$chartImg->setGraphArea(60, 25, $chartwidth - 20, $chartheight - 25);
					$chartImg->drawScale(array (
					                           "CycleBackground" => true,
					                           'Mode'            => SCALE_MODE_START0,
					                           "DrawSubTicks"    => true,
					                           "GridR"           => 0,
					                           "GridG"           => 0,
					                           "GridB"           => 0,
					                           "GridAlpha"       => 10
					                     ));
				}


				/* Write the chart title */
				$chartImg->setFontProperties(array (
				                                   "FontName" => $titlefont,
				                                   "FontSize" => 8
				                             ));
				$chartImg->drawText($chartwidth / 2, 20, sprintf(trans('Wochentage für %s %s'), $monthname, $currentyear), array (
				                                                                                                                 "FontSize" => 12,
				                                                                                                                 "Align"    => TEXT_ALIGN_BOTTOMMIDDLE
				                                                                                                           ));


				if ( $charttype === 'barup' )
				{
					$settings = array (
						"FontSize"       => 6,
						"Gradient"       => true,
						"DisplayPos"     => LABEL_POS_INSIDE,
						"DisplayValues"  => true,
						"DisplayR"       => 50,
						"DisplayG"       => 50,
						"DisplayB"       => 50,
						"DisplayShadow"  => false,
						"Surrounding"    => 30,
						"OverrideColors" => $palette
					);
					$chartImg->drawBarChart($settings);
				}
				elseif ( $charttype === 'line' )
				{
					$this->drawLine($chartImg);
				}
				elseif ( $charttype === 'pie' )
				{
					$chartImg->setGraphArea(118, 30, $chartwidth - 20, $chartheight - 15);

					$this->drawPie($chartwidth, $chartheight, $palette, $chartData, $chartImg);
				}


				$chartImg->stroke();

				break;
			case 'hours':

				$x = 0;

				$_xlabels = array ();
				foreach ( $result as $hour => $_hits )
				{
					$rgbcolors[ $x ][ "Alpha" ] = 100;
					$palette[ $x ]              = $rgbcolors[ $x ];
					$hits[ $x ]                 = $_hits;
					$labels[ $x ]               = $hour;
					$_xlabels[ $x ]             = trans('Uhr');
					$x++;
				}


				$chartData->setAxisName(0, "Hits");
				$chartData->addPoints($hits, "Hits");

				$chartData->setAxisName(1, trans('Uhr'));

				/* Bind a data serie to the X axis */
				$chartData->addPoints($labels, trans('Uhr'));
				$chartData->setSerieDescription(trans('Uhr'), 'Uhr' . trans(' Aufrufe je Stunde'));
				$chartData->setAbscissa(trans('Uhr'));


				$chartImg            = new pImage($chartwidth, $chartheight, $chartData, true);
				$chartImg->Antialias = false;


				/* Turn on shadow computing */
				#$chartImg->setShadow(TRUE, array("X" => 2, "Y" => 1, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));
				#$chartImg->drawGradientArea(0, 0, $chartwidth, $chartheight, DIRECTION_VERTICAL, array("StartR" => 240, "StartG" => 240, "StartB" => 240, "EndR" => 180, "EndG" => 180, "EndB" => 180, "Alpha" => 100));
				#$chartImg->drawGradientArea(0, 0, $chartwidth, $chartheight, DIRECTION_HORIZONTAL, array("StartR" => 240, "StartG" => 240, "StartB" => 240, "EndR" => 180, "EndG" => 180, "EndB" => 180, "Alpha" => 20));


				$chartImg->setFontProperties(array (
				                                   "FontName" => $titlefont,
				                                   "FontSize" => 8
				                             ));

				$charttype = ($charttype == 'bar' ? 'barup' : $charttype);

				if ( $charttype !== 'pie' )
				{
					$chartImg->setGraphArea(60, 30, $chartwidth - 20, $chartheight - 30);

					$chartImg->drawScale(array (
					                           "CycleBackground" => true,
					                           'Mode'            => SCALE_MODE_START0,
					                           "DrawSubTicks"    => true,
					                           "GridR"           => 0,
					                           "GridG"           => 0,
					                           "GridB"           => 0,
					                           "GridAlpha"       => 10
					                     ));
				}

				/* Write the chart title */

				$chartImg->drawText($chartwidth / 2, 20, sprintf(trans('Sundenauswertung für %s %s'), $monthname, $currentyear), array (
				                                                                                                                       "FontSize" => 12,
				                                                                                                                       "Align"    => TEXT_ALIGN_BOTTOMMIDDLE
				                                                                                                                 ));

				$chartImg->setFontProperties(array (
				                                   "FontName" => $titlefont,
				                                   "FontSize" => 8
				                             ));


				if ( $charttype === 'barup' )
				{

					$chartImg->setFontProperties(array (
					                                   "FontName" => $titlefont,
					                                   "FontSize" => 8
					                             ));
					$settings = array (
						"Gradient"       => true,
						"DisplayPos"     => LABEL_POS_INSIDE,
						"DisplayValues"  => true,
						"DisplayR"       => 0,
						"DisplayG"       => 0,
						"DisplayB"       => 0,
						"DisplayShadow"  => false,
						"Surrounding"    => 30,
						"OverrideColors" => $palette
					);
					$chartImg->drawBarChart($settings);
				}
				elseif ( $charttype === 'line' )
				{
					$this->drawLine($chartImg);
				}
				elseif ( $charttype === 'pie' )
				{
					$this->drawPie($chartwidth, $chartheight, $palette, $chartData, $chartImg);
				}


				$chartImg->stroke();

				break;
		}
	}

	/**
	 *
	 * @param array $hits
	 * @param array $labels
	 * @param array $opt eg: array('yLabel' => your label, 'xLabel' => your label, 'title' => your title)
	 */
	private function buildLineGraph ( $hits, $labels, $opt = array () )
	{

		$font = VENDOR_PATH . 'pChart/fonts/tahoma.ttf';

		$chartData = new pData();


		# $mode_lineHeight = $this->chartHeight - 100;
		$mode_lineWidth = $this->chartWidth - 40;

		$chartData->AddPoint($hits, "Serie1");
		$chartData->AddPoint($labels, "Serie2");

		$chartData->AddAllSeries();
		$chartData->SetAbsciseLabelSerie("Serie1");
		$chartData->SetAbsciseLabelSerie("Serie2");
		# $chartData->SetAbsciseLabelSerie();

		$chartData->SetSerieName("Hits", "Serie1");
		$chartData->SetSerieName("OS", "Serie2");

		if ( !empty($opt[ 'yLabel' ]) )
		{
			$chartData->SetYAxisName($opt[ 'yLabel' ]);
		}
		else
		{
			$chartData->SetYAxisName('hits');
		}

		if ( !empty($opt[ 'xLabel' ]) )
		{
			$chartData->SetXAxisName($opt[ 'xLabel' ]);
		}
		else
		{
			//$chartData->SetXAxisName( 'hits' );
		}
		//  $chartData->SetXAxisName( "B" );

		$chartImg = new pChart($this->chartWidth, $this->chartHeight);

		$chartImg->setFontProperties($font, 8);
		$chartImg->setGraphArea(65, 40, $mode_lineWidth, $this->chartHeight - 50);
		$chartImg->setFixedScale(0, $this->total_hits_month);

		$chartImg->drawFilledRoundedRectangle(7, 7, $this->chartWidth - 5, $this->chartHeight - 3, 5, 240, 240, 240);
		$chartImg->drawRoundedRectangle(5, 5, $this->chartWidth - 3, $this->chartHeight - 1, 5, 230, 230, 230);
		$chartImg->drawGraphArea(255, 255, 255, true);
		$chartImg->drawScale($chartData->GetData(), $chartData->GetDataDescription(), SCALE_NORMAL, 150, 150, 150, true, 0, 2);
		$chartImg->drawGrid(4, true, 230, 230, 230, 50);

		// Draw the 0 line
		$chartImg->setFontProperties($font, 6);
		$chartImg->drawTreshold(0, 143, 55, 72, true, true);


		// Draw the line graph
		$chartImg->setLineStyle(1, 0);
		//   $chartImg->setShadowProperties();
		$chartImg->drawLineGraph($chartData->GetData(), $chartData->GetDataDescription());
		$chartImg->drawPlotGraph($chartData->GetData(), $chartData->GetDataDescription(), 2, 1, 150, 150, 150);


		// Finish the graph
		if ( $opt[ 'legend' ] )
		{

			$chartImg->setFontProperties($font, 8);
			$chartImg->drawLegend($this->chartWidth - 80, 35, $chartData->GetDataDescription(), 255, 255, 255);
		}

		if ( !empty($opt[ 'title' ]) )
		{
			$chartImg->setFontProperties($font, 10);
			$chartImg->drawTitle(60, 22, $opt[ 'title' ], 50, 50, 50, 585);
		}

		$chartImg->Stroke();

		exit;
	}

	private function buildVerticalBarGraph ( $hits, $labels, $opt = array () )
	{

		$font = VENDOR_PATH . 'pChart/fonts/tahoma.ttf';


		$colors = $this->getRGBColors();


		$DataSet = new pData;
		$DataSet->AddPoint($hits, "Serie1");
		$DataSet->AddPoint($labels, "Serie2");

		$DataSet->AddAllSeries();


		#$DataSet->SetAbsciseLabelSerie( "Serie1" );
		$DataSet->SetAbsciseLabelSerie("Serie2");


		if ( !empty($opt[ 'yLabel' ]) )
		{
			$DataSet->SetYAxisName($opt[ 'yLabel' ]);
		}
		else
		{
			$DataSet->SetYAxisName('hits');
		}

		if ( !empty($opt[ 'xLabel' ]) )
		{
			$DataSet->SetXAxisName($opt[ 'xLabel' ]);
			$DataSet->SetSerieName($opt[ 'xLabel' ], "Serie2");
		}

		$chartImg = new pChart($this->chartWidth, $this->chartHeight);
		$chartImg->setFontProperties($font, 6);
		$chartImg->setGraphArea(75, 40, $this->chartWidth - 50, $this->chartHeight - 50);
		# $chartImg->setFixedScale( 0, $this->total_hits_month );

		$chartImg->drawFilledRoundedRectangle(7, 7, $this->chartWidth - 3, $this->chartHeight - 3, 5, 240, 240, 240);
		$chartImg->drawRoundedRectangle(5, 5, $this->chartWidth - 1, $this->chartHeight - 1, 5, 230, 230, 230);
		$chartImg->drawGraphArea(255, 255, 255, true);
		$chartImg->drawScale($DataSet->GetData(), $DataSet->GetDataDescription(), SCALE_NORMAL, 150, 150, 150, true, 0, 2, true);
		$chartImg->drawGrid(4, true, 230, 230, 230, 50);

		// Draw the 0 line
		$chartImg->setFontProperties($font, 6);
		$chartImg->drawTreshold(0, 143, 55, 72, true, true);


		// Draw the bar graph
		$chartImg->drawBarGraph($DataSet->GetData(), $DataSet->GetDataDescription(), true);


		// Finish the graph

		if ( $opt[ 'legend' ] )
		{
			$chartImg->setFontProperties($font, 8);
			$chartImg->drawLegend($this->chartWidth - 80, 20, $DataSet->GetDataDescription(), 255, 255, 255);
		}

		if ( !empty($opt[ 'title' ]) )
		{
			$chartImg->setFontProperties($font, 10);
			$chartImg->drawTitle(60, 22, $opt[ 'title' ], 50, 50, 50, 585);
		}
		$chartImg->Stroke();

		exit;
	}

	/**
	 * @param $img
	 */
	private function drawBarUp ( $img )
	{

		$img->drawScale(array (
		                      "CycleBackground" => true,
		                      "DrawSubTicks"    => true,
		                      "GridR"           => 0,
		                      "GridG"           => 0,
		                      "GridB"           => 0,
		                      "GridAlpha"       => 10
		                ));
	}

	/**
	 * @param $img
	 */
	private function drawBarWidth ( $img )
	{

		$img->drawScale(array (
		                      "CycleBackground" => true,
		                      "DrawSubTicks"    => true,
		                      "GridR"           => 0,
		                      "GridG"           => 0,
		                      "GridB"           => 0,
		                      "GridAlpha"       => 10,
		                      "Pos"             => SCALE_POS_TOPBOTTOM
		                ));
	}

	/**
	 * @param $img
	 */
	private function drawLine ( $img )
	{

		$Config = array (
			"BreakVoid" => false,
			"BreakR"    => 234,
			"BreakG"    => 55,
			"BreakB"    => 26
		);
		$img->drawSplineChart($Config);


		$img->Antialias = false;
		$img->setShadow(true, array (
		                            "X"     => 1,
		                            "Y"     => 1,
		                            "R"     => 0,
		                            "G"     => 0,
		                            "B"     => 0,
		                            "Alpha" => 10
		                      ));
		$img->setFontProperties(array (
		                              "FontName" => VENDOR_PATH . 'pChart/fonts/verdana.ttf',
		                              "FontSize" => 8
		                        ));
		$img->drawLineChart(array (
		                          "DisplayValues" => true,
		                          "DisplayColor"  => DISPLAY_MANUAL,
		                          'DisplayR'      => 80,
		                          'DisplayG'      => 80,
		                          'DisplayB'      => 80
		                    ));
	}

	/**
	 * @param $chartwidth
	 * @param $chartheight
	 * @param $palette
	 * @param $data
	 * @param $img
	 */
	private function drawPie ( $chartwidth, $chartheight, $palette, $data, $img )
	{

		$img->Antialias = false;

		$PieChart = new pPie($img, $data);

		foreach ( $palette as $idx => $rgb )
		{
			$PieChart->setSliceColor($idx, $rgb);
		}


		$img->setFontProperties(array (
		                              "FontName" => VENDOR_PATH . 'pChart/fonts/verdana.ttf',
		                              "FontSize" => 6,
		                              "R"        => 0,
		                              "G"        => 0,
		                              "B"        => 0
		                        ));
		$img->setShadow(true, array (
		                            "X"     => 2,
		                            "Y"     => 1,
		                            "R"     => 5,
		                            "G"     => 5,
		                            "B"     => 5,
		                            "Alpha" => 40
		                      ));

		$PieChart->draw3DPie($chartwidth / 2.2, $chartheight / 2, array (
		                                                                'SkewFactor'    => 0.5,
		                                                                'SliceHeight'   => 50,
		                                                                'Radius'        => ($chartheight / 2) - 20,
		                                                                "DataGapAngle"  => 2,
		                                                                "DataGapRadius" => 2,
		                                                                "WriteValues"   => PIE_VALUE_NATURAL,
		                                                                'SecondPass'    => true,
		                                                                "Border"        => true,
		                                                                "DrawLabels"    => false,
		                                                                'LabelStacked'  => false,
		                                                                'ValueR'        => 0,
		                                                                'ValueG'        => 0,
		                                                                'ValueB'        => 0,
		                                                                'ValuePadding'  => 10,
		                                                                'ValuePosition' => PIE_VALUE_OUTSIDE
		                                                          ));

		/* Write the legend box */
		$img->setShadow(false);
		$img->setFontProperties(array (
		                              "FontName" => VENDOR_PATH . 'pChart/fonts/verdana.ttf',
		                              "FontSize" => 8
		                        ));


		$PieChart->drawPieLegend($chartwidth - 200, 20, array (
		                                                      "Alpha"     => 30,
		                                                      'BoxWidth'  => 150,
		                                                      'BoxHeight' => ($chartheight / 2),
		                                                      'Family'    => LEGEND_FAMILY_LINE,
		                                                      'Style'     => LEGEND_ROUND,
		                                                      "Mode"      => LEGEND_VERTICAL
		                                                ));
	}

}
