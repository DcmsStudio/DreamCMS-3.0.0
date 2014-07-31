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
 * @category     Helper
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Page_Helper_Base
{

	/**
	 * @var object
	 */
	private $model;

	/**
	 * @var array|null
	 */
	public $pagetypes = null;

	/**
	 *
	 */
	public function __construct ()
	{

		$this->pagetypes = array (
			'page'          => trans('Einfache Seite'),
			'documentation' => trans('Dokumentations Seite'),
			'product'       => trans('Produkt Seite'),
			'movie'         => trans('Film Seite'),
			'audio'         => trans('Audio Seite'),
			'portfolio'     => trans('Portfolio Seite')
		);


		$this->model = Model::getModelInstance('page');
	}

	/**
	 *
	 * @param string $pagetype
	 * @param string $itemtype
	 */
	public function getCoreFields ( $pagetype, $itemtype )
	{

	}

	/**
	 * @return array
	 */
	public function getPagetypesSelectData ()
	{

		$tmp = array ();
		foreach ( $this->pagetypes as $k => $value )
		{
			$tmp[ ] = array (
				'value' => $k,
				'label' => $value
			);
		}

		return $tmp;
	}

}
