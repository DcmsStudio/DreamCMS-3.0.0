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
 * @package      Search
 * @version      3.0.0 Beta
 * @category     Helper
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Base.php
 */
class Search_Helper_Base extends Controller_Abstract
{

	/**
	 * @var null
	 */
	private $search_hash = null;

	/**
	 * @var null
	 */
	public $resulthash = null;

	/**
	 * Instance of class Search
	 *
	 * @var Search
	 */
	public $searchObj = null;

	/**
	 *
	 * @var integer
	 */
	protected $contextLength = 40;

	/**
	 *
	 * @var integer
	 */
	protected $totalLength = 600;

	/**
	 *
	 * @param string $search_hash
	 * @return bool
	 */
	public function checkHash ( $search_hash = '' )
	{

		$r = $this->db->query('SELECT id FROM %tp%search_spider WHERE searchhash = ? LIMIT 1', $search_hash)->fetch();
		if ( $r[ 'id' ] )
		{
			$this->search_hash = $search_hash;

			return true;
		}
		else
		{
			$this->search_hash = '';

			return false;
		}
	}

	// Hash erzeugen
	/**
	 * @param $searchString
	 * @return string
	 */
	public function createSearchHash ( $searchString )
	{

		return substr(md5($searchString), 0, 10);
	}

	/**
	 *
	 * @return string/null
	 */
	public function getHash ()
	{

		return $this->search_hash;
	}

}
