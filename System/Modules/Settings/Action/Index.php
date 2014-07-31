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
 * @package      Settings
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Settings_Action_Index extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}
		$groups = Dashboard_Config_Base::loadConfigOptions();


		foreach ( $groups as &$rs )
		{
			foreach ( $rs as $group => &$r )
			{

				if ( $r[ 'label' ] == '-' )
				{
					continue;
				}


				$r[ 'title' ] = $r[ 'label' ];
				$r[ 'url' ]   = 'admin.php?adm=settings&action=edit&group=' . $group;
			}
		}


		$configs    = array ();
		$configs[ ] = array (
			'title' => trans('Allgemein'),
			'type'  => 'basic',
			'items' => $groups[ 'basic' ]
		);
		$configs[ ] = array (
			'title' => trans('Ausgabe'),
			'type'  => 'output',
			'items' => $groups[ 'output' ]
		);

		$configs[ ] = array (
			'title' => trans('Sicherheit'),
			'type'  => 'security',
			'items' => $groups[ 'security' ]
		);

        $configs[ ] = array (
            'title' => trans('Inhalt'),
            'type'  => 'content',
            'items' => $groups[ 'content' ]
        );



		$configs[ ] = array (
			'title' => trans('Sonstige'),
			'type'  => 'modules',
			'items' => $groups[ 'modules' ]
		);
		$configs[ ] = array (
			'title' => trans('Plugins'),
			'type'  => 'plugin',
			'items' => $groups[ 'plugin' ]
		);


		$data                     = array ();
		$data[ 'configgroups' ]   = $configs;
		$data[ 'isSingleWindow' ] = true;
		$data[ 'nopadding' ]      = true;
		$data[ 'WinResizeable' ]  = false;
		$data[ 'WinHeight' ]      = 512;
		$data[ 'WinWidth' ]       = 600;
		Library::addNavi(trans('DreamCMS Einstellungen'));

		$this->Template->process('settings/index', $data, true);
		exit;


	}

}
