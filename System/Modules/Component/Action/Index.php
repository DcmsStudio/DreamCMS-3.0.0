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
 * @package      Component
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Component_Action_Index extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$model = Model::getModelInstance();
		$data  = array ();

		$categories = $model->getComponents();
		$counts     = $model->getCategoryCounts();
		$x          = 0;
		foreach ( $categories as $idx => $r )
		{
			$x += (int)$counts[ $r[ 'cat_id' ] ];

			$categories[ $idx ][ 'description' ] = nl2br(htmlspecialchars($r[ 'description' ]));

			$categories[ $idx ][ 'cat_component_count' ] = (string)(int)$counts[ $r[ 'cat_id' ] ];
		}
		$data[ 'components' ] = $categories;

		$data[ 'total_categories' ] = count($model->getCategories());
		$data[ 'total_components' ] = $x;


		Library::addNavi('Componenten');
		$this->Template->process('componentes/index', $data, true);
		exit;
	}

	// Übersicht aller Kategorien
	public function category ()
	{

		global $cfg, $fct, $cp, $adm_skin;

		$data       = array ();
		$categories = self::getCatComponents();
		$counts     = self::getCategoryCounts();

		foreach ( $categories as $idx => $r )
		{
			$categories[ $idx ][ 'counts' ] = (int)$counts[ $r[ 'id' ] ];
		}
		$data[ 'categories' ] = $categories;


		Library::addNavi(trans('Componenten Kategorien'));
		$this->Template->process('componentes/category', $data, true);
		exit;
	}

}

?>