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
 * @package      
 * @version      3.0.0 Beta
 * @category     
 * @copyright    2008-2014 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         CData.php
 */

class Compiler_CData extends Compiler_Node {
	/**
	 * @var
	 */
	private $_text;

	/**
	 * @var
	 */
	private $_typeconst;

    /**
     * @param array $node
     * @param \Compiler_Compile|\Compiler_Template $obj
     */
	public function __construct( array &$node, Compiler_Compile &$obj ) {

	//	parent::__construct($obj);

        $comp = $obj->getCompiler();
        $this->setCompiler( $comp  );

		$this->_text = $node[ 'value' ];
		$this->_typeconst = $node[ 'type' ];

		if ( $node[ 'type' ] === Compiler::CDATA )
		{
			$this->set( 'cdata', true );
		}

		$this->templateInstance =& $obj;
	}


	public function getType()
	{
		return $this->_typeconst;
	}

	/**
	 * Appends the string to the existing node content.
	 *
	 * @param $str
	 * @internal param String $cdata The new string.
	 */
	public function appendData( $str )
	{
		$this->_text .= $str;
	}

	/**
	 * Inserts the string in the specified offset.
	 * @param Integer $offset The offset.
	 * @param String $cdata The new string.
	 */
	public function insertData( $offset, $cdata )
	{
		$this->_text = substr( $this->_text, 0, $offset ) . $cdata . substr( $this->_text, $offset, strlen( $this->_text ) - $offset );
	}

	/**
	 * Deletes the specified part of the content.
	 * @param Integer $offset The position of the first character to delete.
	 * @param Integer $count The number of characters to delete.
	 */
	public function deleteData( $offset, $count )
	{
		$this->_text = substr( $this->_text, 0, $offset ) . substr( $this->_text, $offset + $count, strlen( $this->_text ) - $offset - $count );
	}

	/**
	 * Replaces the specified amount of the original text with the part of the new string.
	 * @param Integer $offset The position of the first character to replace.
	 * @param Integer $count The number of characters to replace.
	 * @param String $text The replacing string.
	 */
	public function replaceData( $offset, $count, $text )
	{
		$this->_text = substr( $this->_text, 0, $offset ) . substr( $text, 0, $count ) . substr( $this->_text, $offset + $count, strlen( $this->_text ) - $offset - $count );
	}

	/**
	 * Returns the specified part of the content.
	 * @param Integer $offset The position of the first character to return.
	 * @param Integer $count The number of characters to return.
	 * @return String
	 */
	public function substringData( $offset, $count )
	{
		return substr( $this->_text, $offset, $count );
	}

	/**
	 * Returns the content length.
	 * @return Integer
	 */
	public function length()
	{
		return strlen( $this->_text );
	}

	/**
	 *
	 * @return string
	 */
	public function getCompiled()
	{
		return $this->_text;
	}

	/**
	 * Returns the content.
	 * @return String
	 */
	public function __toString()
	{
		return $this->_text;
	}

}