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
 * @package      Action
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Install.php
 */
class Modules_Action_Install extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}


		// get all registred modules
		$this->getApplication()->refreshModulRegistry();


		$registry = $this->getApplication()->getModulRegistry();

		// get installed modules
		$_result = $this->model->getInstalled();

		foreach ( $_result as $rs )
		{
			if ( isset($registry[ $rs[ 'module' ] ]) )
			{
				unset($registry[ $rs[ 'module' ] ]);
			}
		}


		if ( $this->input('send') )
		{

			$modul = $this->input('modul');

			if ( !isset($registry[ $modul ]) )
			{
				Library::sendJson(false, trans('Das von ihnen zu installierende Modul existiert nicht oder wurde schon installiert.'));
			}

			demoadm();

			$data = $registry[ $modul ][ 'definition' ];

			$this->db->query('INSERT INTO %tp%module
                        (goto, link, groups, pageid, module, `version`, published, configurable, settings, allowmetadata, metatables, treeactions, modulactions) 
                        VALUES(0,?,?,?,?,?,?,?,?,?,?,?,?)', '', 0, PAGEID, $modul, (!empty($data[ 'version' ]) ?
				$data[ 'version' ] : ''), 0, ($data[ 'configurable' ] ? true : false), (!empty($data[ 'configForm' ]) ?
				serialize($data[ 'configForm' ]) : ''), ($data[ 'allowmetadata' ] ? true :
				false), (!empty($data[ 'metatables' ]) ? serialize($data[ 'metatables' ]) :
				''), (!empty($data[ 'treeactions' ]) ? serialize($data[ 'treeactions' ]) :
				''), (!empty($data[ 'modulactions' ]) ? serialize($data[ 'modulactions' ]) : ''));

			Cache::delete('modules', 'data');
			Cache::delete('pages-tree', 'data');
			Cache::delete('modul-props', 'data');
			SystemManager::cleanControllerActions();
			$this->getApplication()->refreshModulRegistry();

			Library::sendJson(true, trans('Das Modul wurde installiert.'));
		}


		$data              = array ();
		$data[ 'modules' ] = array ();
		foreach ( $registry as $key => $rs )
		{
			if ( empty($rs[ 'definition' ][ 'modulelabel' ]) )
			{
				$rs[ 'definition' ][ 'modulelabel' ] = $key;
			}
			$rs[ 'definition' ][ 'modul' ] = $key;

			$data[ 'modules' ][ ] = $rs[ 'definition' ];
		}

		Library::addNavi(trans('Weitere Module installieren'));
		$this->Template->process('modules/install', $data, true);
	}

}
