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
 * @file         Expression.php
 */

class Compiler_Expression extends Compiler_Node
{
	/**
	 * @var
	 */
	private $_expression;

	private $_typeconst;


    /**
     * @param array $node
     * @param \Compiler_Compile|\Compiler_Template $obj
     */
	public function __construct ( array &$node, Compiler_Compile &$obj )
	{
	//	parent::__construct($obj);
		$this->_expression = $node[ 'value' ];
		$this->_typeconst = $node[ 'type' ];

		$this->templateInstance =& $obj;
	}
	public function __destruct()
	{
	}

	public function getType()
	{
		return $this->_typeconst;
	}

	/**
	 * Returns the expression stored in the node.
	 * @return String
	 */
	public function getExpression()
	{
		return $this->_expression;
	}

	/**
	 * Sets a new expression for the node.
	 * @param String $expression The new expression.
	 */
	public function setExpression( $expression )
	{
		$this->_expression = $expression;
	}

	/**
	 * Returns the content.
	 * @return String
	 */
	public function __toString()
	{
		return $this->_expression;
	}

}