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
 * @package      Editorsettings
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Editorsettings_Action_Index extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$data                 = array ();
		$data[ 'usergroups' ] = $this->db->query('SELECT groupid, title, dashboard, editorsettings FROM %tp%users_groups ORDER BY title')->fetchAll();


		foreach ( $data[ 'usergroups' ] as $idx => $r )
		{
			$tadv_toolbars = unserialize($r['editorsettings']);


			$x = 0;
			if ( $tadv_toolbars !== null && is_array($tadv_toolbars) )
			{
				$x = count($tadv_toolbars);
			}

			if ( $x )
			{
				$data[ 'usergroups' ][ $idx ][ 'hastoolbar' ] = true;
			}
		}

		$this->Template->addScript(BACKEND_JS_URL . 'tadv');
		$this->Template->addScript(CSS_URL . 'tadv-styles', true);

		Library::addNavi(trans('Inhalts Editoren'));

		$this->Template->process('settings/editors', $data, true);
	}

}
