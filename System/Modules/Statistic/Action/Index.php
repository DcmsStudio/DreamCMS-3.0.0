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
 * @file         Index.php
 */
class Statistic_Action_Index extends Statistic_Helper_Base
{

	public function execute ()
	{

		if ( $this->getApplication()->getMode() === Application::BACKEND_MODE )
		{
			$this->_processBackend();
		}
		else
		{
			$this->_processFrontend();
		}
	}

	private function _processBackend ()
	{

		Library::disableErrorHandling();

		$month = $this->input('month');
		$year  = $this->input('year');


		$this->selectedMonth = ($month != null && (int)$month ? $month : date('n'));
		$this->selectedYear  = ($year != null && (int)$year > 0 ? (int)$year : date('Y'));


		// die( ''.$this->selectedMonth );


		$this->setInput('month', $this->selectedMonth);
		$this->setInput('year', $this->selectedYear);

		Session::save('statistic_month', $this->selectedMonth);
		Session::save('statistic_year', $this->selectedYear);


		$this->iconLib_path = 'public/html/img/statistic/';
		$this->initStat();
		$data = $this->getMonthYear();
		$this->getMinMaxStat();

		Session::write();
		Library::enableErrorHandling();

		$data[ 'selection' ][ 'min' ] = $this->minStat;
		$data[ 'selection' ][ 'max' ] = $this->maxStat;

		if ( IS_AJAX && HTTP::input('block') )
		{

			$maincontent = $this->Template->process('statistic/index', $data, null, 'statdata');
			$advanced    = $this->Template->process('statistic/index', $data, null, 'advanced');


			echo Library::json(array (
			                         'success'       => true,
			                         'html_statdata' => trim($maincontent),
			                         'html_advanced' => trim($advanced)
			                   ));
			exit;
		}


		// $this->Template->addScript(JS_URL . 'jquery/jqplot/css/jquery.jqplot.min.css', true);
		$this->Template->addScript(BACKEND_CSS_PATH . 'dcms.statistic.css', true);
		//$this->Template->addScript(JS_URL . 'jquery/jqplot/jquery.jqplot.min.js', false);
		$this->Template->addScript(JS_URL . 'jquery/jqplot/jqplot.core.js', false);

		$this->Template->addScript(JS_URL . 'jquery/jqplot/jqplot.themeEngine.js', false);
		$this->Template->addScript(JS_URL . 'jquery/jqplot/jqplot.sprintf.js', false);


		$this->Template->addScript(JS_URL . 'jquery/jqplot/jqplot.canvasGridRenderer.js', false);

		$this->Template->addScript(JS_URL . 'jquery/jqplot/jqplot.shapeRenderer.js', false);
		$this->Template->addScript(JS_URL . 'jquery/jqplot/jqplot.shadowRenderer.js', false);
		$this->Template->addScript(JS_URL . 'jquery/jqplot/jqplot.markerRenderer.js', false);

		$this->Template->addScript(JS_URL . 'jquery/jqplot/jqplot.tableLegendRenderer.js', false);
		$this->Template->addScript(JS_URL . 'jquery/jqplot/jqplot.lineRenderer.js', false);
		$this->Template->addScript(JS_URL . 'jquery/jqplot/jqplot.linearTickGenerator.js', false);
		$this->Template->addScript(JS_URL . 'jquery/jqplot/jqplot.linearAxisRenderer.js', false);
		$this->Template->addScript(JS_URL . 'jquery/jqplot/jqplot.axisLabelRenderer.js', false);
		$this->Template->addScript(JS_URL . 'jquery/jqplot/jqplot.axisTickRenderer.js', false);
		$this->Template->addScript(JS_URL . 'jquery/jqplot/jqplot.divTitleRenderer.js', false);


		$this->Template->addScript(JS_URL . 'jquery/jqplot/plugins/jqplot.cursor.js', false);
		$this->Template->addScript(JS_URL . 'jquery/jqplot/plugins/jqplot.ciParser.js', false);
		$this->Template->addScript(JS_URL . 'jquery/jqplot/plugins/jqplot.json2.js', false);


		$this->Template->addScript(JS_URL . 'jquery/jqplot/plugins/jqplot.blockRenderer.js', false);
		$this->Template->addScript(JS_URL . 'jquery/jqplot/plugins/jqplot.highlighter.js', false);
		$this->Template->addScript(JS_URL . 'jquery/jqplot/plugins/jqplot.canvasAxisLabelRenderer.js', false);
		$this->Template->addScript(JS_URL . 'jquery/jqplot/plugins/jqplot.canvasAxisTickRenderer.js', false);
		$this->Template->addScript(JS_URL . 'jquery/jqplot/plugins/jqplot.canvasTextRenderer.js', false);

		$this->Template->addScript(JS_URL . 'jquery/jqplot/plugins/jqplot.categoryAxisRenderer.js', false);


		$this->Template->addScript(JS_URL . 'jquery/jqplot/plugins/jqplot.pointLabels.js', false);
		$this->Template->addScript(JS_URL . 'jquery/jqplot/plugins/jqplot.logAxisRenderer.js', false);


		$this->Template->addScript(JS_URL . 'jquery/jqplot/plugins/jqplot.barRenderer.min.js', false);
		$this->Template->addScript(JS_URL . 'jquery/jqplot/plugins/jqplot.pieRenderer.min.js', false);
		$this->Template->addScript(JS_URL . 'jquery/jqplot/plugins/jqplot.trendline.min.js', false);


		$this->Template->addScript(JS_URL . 'backend/dcms.statistic.js', false);


		$this->Template->process('statistic/index', $data, true);
	}

	private function _processFrontend ()
	{

	}

}
