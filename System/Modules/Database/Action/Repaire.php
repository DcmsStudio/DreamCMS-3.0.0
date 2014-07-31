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
 * @file         Repaire.php
 */
class Database_Action_Repaire extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		demoadm();

		$tables = HTTP::post('tables');
		if ( !is_array($tables) || !count($tables) )
		{
			Library::sendJson(false, trans('Sie haben keine Tabellen ausgewählt!'));
		}

		if ( defined('DEMO_MODE') && DEMO_MODE )
		{
			demo_error();
		}

		$this->model->repairTables($tables);

		Library::log("has repair DB-Tables: " . implode(', ', $tables));
		Library::sendJson(true, (count($tables) > 1 ? trans('Die ausgewählten Tabellen wurde erfolgreich repariert') :
			sprintf(trans('Die Tabelle `%s` wurde erfolgreich repariert'), $tables[ 0 ])));
	}

}

?>