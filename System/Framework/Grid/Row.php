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
 * @category     Framework
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Row.php
 */
class Grid_Row extends Grid_Abstract
{

	/**
	 * @var array
	 */
	private $fieldsData = array ();

	/**
	 * @var string
	 */
	public $primaryKey = '';

	/**
	 * @var int
	 */
	public $primaryKeyValue = 0;

	/**
	 * @var array
	 */
	private $data = array ();

	protected $headers = array ();

	/**
	 *
	 */
	public function __construct ( $headers )
	{

		parent::__construct();

		$this->headers = $headers; // all grid header columns
	}


    /**
     * @param $name
     * @return bool
     */
    protected function isInHeader ( $name )
	{

		if ( is_array($this->headers) && $name != $this->primaryKey )
		{
			foreach ( $this->headers as $idx => &$r )
			{
				if ( $r[ 'field' ] == $name )
				{
					return true;
				}
			}
		}

		return false;
	}


	/**
	 * @param $data
	 */
	public function prepare ( $data )
	{

		foreach ( $data as $fiedname => $v )
		{
			if ( !$this->isInHeader($fiedname) )
			{
				continue;
			}


			$this->fieldsData[ $fiedname ] = array ();


			$this->fieldsData[ $fiedname ][ 'data' ] = str_replace('&amp;', '&', $v);


		}
	}

	/**
	 *
	 * @param string $fiedname
	 * @param string $data
	 * @param string $css // css classname
	 * @return Grid_Row
	 */
	public function addFieldData ( $fiedname, $data, $css = null )
	{

		$this->fieldsData[ $fiedname ] = array ();
		if ( !isset( $this->fieldsData[ $this->primaryKey ] ) )
		{
			$this->fieldsData[ $this->primaryKey ] = $this->primaryKeyValue;
		}


		$this->fieldsData[ $fiedname ][ 'data' ] = str_replace('&amp;', '&', $data);


		// $this->fieldsData[ $fiedname ][ 'data' ] = !preg_match('#^([\d]+?|true|false)$#i', $data) ? str_replace( '&amp;', '&', $data ) : $data;

		if ( $css )
		{
			$this->fieldsData[ $fiedname ][ 'css' ] = $css;
		}

		return $this;
	}

	/**
	 *
	 * @return array
	 */
	public function getFieldData ()
	{

		$keys = array_keys($this->fieldsData);


		return $this->fieldsData;
	}

}
