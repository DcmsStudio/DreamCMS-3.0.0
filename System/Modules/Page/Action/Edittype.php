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
 * @package      Page
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Edittype.php
 */
class Page_Action_Edittype extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->getApplication()->getMode() === Application::BACKEND_MODE )
		{
			$this->_processBackend();
		}
	}

	private function _processBackend ()
	{

		$id         = (int)$this->input('id');
		$basehelper = new Page_Helper_Base;

		$pagetype = array ();
		if ( $id )
		{
			$pagetype = $this->model->getPagetypeById($id);
		}

		if ( $this->_post('send') )
		{
			$data            = $this->_post();
			$data[ 'title' ] = trim($data[ 'title' ]);
			if ( !$data[ 'title' ] )
			{
				Library::sendJson(false, trans('Titel des Seitentypes fehlt oder ist nicht erlaubt'));
			}


			$data[ 'fields' ] = is_array($data[ 'field' ]) ? implode(',', $data[ 'field' ]) : '';

			if ( $id )
			{
				$newid = $this->model->savePagetype($id, $data);
				Library::log(sprintf('User has updated the Pagetype `%s` (ID:%s).', $pagetype[ 'title' ], $id));
			}
			else
			{
				$newid = $this->model->savePagetype(0, $data);
				Library::log(sprintf('User has added the Pagetype `%s` (ID:%s).', $post[ 'title' ], $newid));
			}


			echo Library::json(array (
			                         'success' => true,
			                         'newid'   => '' . $newid,
			                         'msg'     => ($id ? trans('Seitentyp wurde aktualisiert') :
					                         trans('Seitentyp wurde erfolgreich erstellt'))
			                   ));
			exit;
		}


		$pagetype[ 'available' ] = $this->model->getFieldsByPagetypeId($id);
		$fields                  = !empty($pagetype[ 'fields' ]) ? explode(',', $pagetype[ 'fields' ]) : array ();
		$fields                  = is_array($fields) ? $fields : array ();


		$assigned = array ();

		//   foreach ( $fields as $_id )
		//  {
		foreach ( $pagetype[ 'available' ] as $idx => &$r )
		{
			$availableSettings = !empty($r[ 'options' ]) ? unserialize($r[ 'options' ]) : array ();


			if ( !is_array($availableSettings) )
			{
				$availableSettings = array ();
			}

			$r[ 'options' ] = $availableSettings;

			if ( in_array($r[ 'fieldid' ], $fields) )
			{
				$assigned[ ] = $r;
				unset($pagetype[ 'available' ][ $idx ]);
			}
		}
		// }


		$pagetype[ 'assigned' ] = $assigned;


		$pagetype[ 'contentlayouts' ] = $this->model->getContainerOptions();
		array_unshift($pagetype[ 'contentlayouts' ], array (
		                                                   'value' => 0,
		                                                   'label' => trans('--- Content Layout ---')
		                                             ));
		$pagetype[ 'contentlayouts' ][ 'selected' ] = $pagetype[ 'contentlayout' ];


		$pagetype[ 'pagelayouts' ] = $this->getLayouts();
		array_unshift($pagetype[ 'pagelayouts' ], array (
		                                                'value' => 0,
		                                                'label' => trans('--- Seiten Layout ---')
		                                          ));
		$pagetype[ 'pagelayouts' ][ 'selected' ] = $pagetype[ 'pagelayout' ];

		$pagetype[ 'pagetypes' ] = $basehelper->getPagetypesSelectData();

		Library::addNavi(trans('Seitentypen Übersicht'));
		Library::addNavi(($id ?
			sprintf(trans('Seitentyp `%s` (%s) bearbeiten'), $pagetype[ 'pagetype' ], $pagetype[ 'title' ]) :
			trans('Seitentyp anlegen')));

		$this->Template->process('pages/editpagetype', $pagetype, true);

		exit;
	}

}
