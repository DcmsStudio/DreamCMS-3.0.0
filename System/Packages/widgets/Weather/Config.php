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
 * @package      DreamCMS
 * @version      3.0.0 Beta
 * @category     Widget s
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Config.php
 */
class Widget_Weather_Config extends Widget
{

	public function findLocationByName ( $location )
	{

		if ( $location )
		{
			$location = str_replace(' ', '%20', $location);
			$result   = $this->db->query('SELECT * FROM %tp%weather_locations WHERE location LIKE ?', '%'.$location.'%')->fetch();

			if ( count($result) )
			{
				return $result;
			}
		}

		return array ();
	}

	public function getData ()
	{

		$data[ 'id' ]     = $this->getID();
		$data[ 'config' ] = $this->getConfig();

        $dat                           = $this->findLocationByName($data[ 'config' ][ 'location' ]);
        $data[ 'config' ][ 'country' ] = $dat[ 'country' ];
        $data[ 'config' ][ 'city' ]    = $dat[ 'city' ];

		$this->setWidgetData($data);
	}

}
