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
 * @package
 * @version      3.0.0 Beta
 * @category
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         widget.clock.php
 */
class Widget_Clock_Show extends Widget
{

	public function getData ()
	{
		$cfg = $this->getConfig();

		return $this->setWidgetData(array (
		                                  'skin'          => isset($cfg['skin']) ? $cfg['skin'] : '' ,
		                                  'showSecondHand'=> isset($cfg['showSecondHand']) ? ($cfg['showSecondHand'] ? 'true' : 'false') : 'true',
		                                  'displayRadius' => (isset($cfg['displayRadius']) && intval($cfg['displayRadius']) > 0 ? intval($cfg['displayRadius']) : 85),
		                                  'gmtOffset'     => isset($cfg['gmtOffset']) ? $cfg['gmtOffset'] : 0,
		                                  'showDigital'   => (isset($cfg['showDigital']) ? ($cfg['showDigital']?'true':'false') : 'false' )
		                            ));
	}

}

?>