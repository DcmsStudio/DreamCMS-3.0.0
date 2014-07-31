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
 * @file         Node.php
 */


abstract class Compiler_Node
{


	/**
	 * @var Compiler_Tag|Compiler_Tag_Html
	 */
	protected $_prevTag = null;

	/**
	 * @var Compiler_Tag|Compiler_Tag_Html
	 */
	protected $_parentTag = null;

	/**
	 * @var Compiler
	 */
	protected $_compiler = null;


	/**
	 * The extra node flags.
	 *
	 * @internal
	 * @var array
	 */
	protected $_args = null;


	/**
	 * Is the node hidden?
	 *
	 * @internal
	 * @var boolean
	 */
	protected $_hidden = null;

	protected $_currentTag = '';

	protected $_NS = '';

	protected $_tagname = '';

	protected $_attributes = null;

	protected $_isEmptyTag = false;

	protected $_isEndTag = false;

	/**
	 *
	 * @var integer
	 */
	protected $_type = null;

	protected $_subnodes = null;


	protected $processor = null;

	/**
	 * @var Compiler_Compile
	 */
	protected $templateInstance = null;

	/**
	 * @return Compiler_Compile
	 */
	public function getTemplateInstance ()
	{
		return $this->templateInstance;
	}

	/**
	 * @param Compiler_Tag $tag
	 */
	public function setTag ( Compiler_Tag $tag )
	{

		$this->tag = $tag;
	}

    /**
     * @param Compiler $compiler
     */
    public function setCompiler ( Compiler &$compiler )
	{

		$this->_compiler = &$compiler;
	}

	/**
	 * @return bool|Compiler
	 */
	public function getCompiler ()
	{

		return $this->_compiler;
	}

	/**
	 * Sets the node variable.
	 *
	 * @param string $name  The node variable name.
	 * @param mixed  $value The variable value
	 */
	public function set ( $name, $value = null )
	{

		if ( $name == 'hidden' )
		{
			$this->_hidden = $value;

			return;
		}

        $this->_args[ $name ] = $value;
	}

	/**
	 * Returns the node variable value.
	 *
	 * @param string $name The node variable name.
	 * @return mixed
	 */
	public function get ( $name )
	{

		if ( $name == 'hidden' )
		{
			return $this->_hidden;
		}

		if ( !is_array($this->_args) || !isset( $this->_args[ $name ] ) )
		{
			return null;
		}

		return $this->_args[ $name ];
	}


    /**
     * @return string
     */
    public function getXmlName ()
	{

		return ( $this->_NS ? $this->_NS . ':' : '' ) . $this->_tagname;
	}

	/**
	 * @return int
	 */
	public function getType ()
	{

		return $this->_type;
	}

	/**
	 * @param Compiler_Tag|Compiler_Tag_Html $instance
	 */
	public function setParent ( $instance )
	{

		$this->_parentTag = $instance;
	}

	/**
	 *
	 * @return TemplateCompiler_Tag
	 */
	public function &getParent ()
	{

		return $this->_parentTag;
	}


	/**
	 * @param Compiler_Tag|Compiler_Tag_Html $instance
	 */
	public function setPrev ( $instance )
	{

		$this->_prevTag = $instance;
	}

	/**
	 *
	 * @return TemplateCompiler_Tag
	 */
	public function &getPrev ()
	{

		return $this->_prevTag;
	}

	/**
	 *
	 * @param array $childen an array with all child nodes
	 *                       (TemplateCompiler_Tag)
	 */
	public function appendChildren ( &$childen )
	{
		if ( !is_array($this->_subnodes) )
		{
			$this->_subnodes = array ();
		}

		$this->_subnodes = & $childen;
	}

	/**
	 *
	 * @param $child
	 * @internal param \TemplateCompiler_Tag $node
	 */
	public function appendChild ( $child )
	{

		if ( !is_array($this->_subnodes) )
		{
			$this->_subnodes = array ();
		}

		$this->_subnodes[ ] = $child;
	}

    /**
     * Inserts the new node after the node identified by the '$refnode'. The
     * reference node can be identified either by the number or by the object.
     * If the reference node is empty, the new node is appended to the children
     * list, if the last argument allows for that.
     *
     * @param \Compiler_Tag|\TemplateCompiler_Tag $newnode The new node.
     * @param integer|TemplateCompiler_Tag $refnode The reference node.
     * @param boolean $appendOnError Do we like to append the node, if $refnode does not exist?
     */
	public function insertBefore ( Compiler_Tag $newnode, $refnode = null, $appendOnError = true )
	{

		// Test if the node can be a child of this and initialize an
		// empty array if needed.
		if ( !is_array($this->_subnodes) )
		{
			$this->_subnodes = array ();
		}

		$newnode->setParent($this);

		if ( is_null($refnode) )
		{
			$this->_appendChild($newnode, $appendOnError);
		}

		if ( is_object($refnode) )
		{
			$i   = 0;
			$cnt = sizeof($this->_subnodes);
			while ( $cnt > 0 )
			{
				if ( isset( $this->_subnodes[ $i ] ) )
				{
					$cnt--;
					if ( $this->_subnodes[ $i ] === $refnode )
					{
						$this->_subnodes       = $this->_arrayCreateHole($this->_subnodes, $i);
						$this->_subnodes[ $i ] = $newnode;
						break;
					}
				}
				$i++;
			}
		}
		elseif ( is_integer($refnode) )
		{
			end($this->_subnodes);
			if ( $refnode <= key($this->_subnodes) && $refnode >= 0 )
			{
				$this->_subnodes             = $this->_arrayCreateHole($this->_subnodes, $refnode);
				$this->_subnodes[ $refnode ] = $newnode;
			}
			else
			{
				$this->_appendChild($newnode, $appendOnError);
			}
		}
	}

	/**
	 * Removes all the children. The memory after the children is not freed.
	 */
	public function removeChildren ()
	{
		if ( is_array($this->_subnodes) )
		{
			foreach ( $this->_subnodes as $subnode )
			{
				if ( is_object($subnode) )
				{
					$subnode->setParent(null); // destruct
				}
                else {




                }
			}
		}

		unset( $this->_subnodes );
		$this->_subnodes = null;
	}

	/**
	 * Removes the child identified either by the number or the object.
	 *
	 * @param integer|TemplateCompiler_Tag $node The node to be removed.
	 * @return boolean
	 */
	public function removeChild ( $node )
	{

		if ( !is_array($this->_subnodes) )
		{
			return false;
		}

		if ( is_object($node) )
		{
			$cnt   = sizeof($this->_subnodes);
			$found = 0;
			for ( $i = 0; $i < $cnt; $i++ )
			{
				if ( isset( $this->_subnodes[ $i ] ) )
				{
					if ( $this->_subnodes[ $i ] === $node )
					{
                        $null = null;
						$node->setParent($null);
						unset( $this->_subnodes[ $i ] );
						$found++;
					}
				}
			}
			$this->_subnodes = $this->_arrayReduceHoles($this->_subnodes);

			return $found > 0;
		}
		elseif ( is_integer($node) && isset( $this->_subnodes[ $node ] ) )
		{
            $null = null;
			$this->_subnodes[ $node ]->setParent($null);
			unset( $this->_subnodes[ $node ] );
			$this->_subnodes = $this->_arrayReduceHoles($this->_subnodes);

			return true;
		}

		return false;
	}

	/**
	 * Returns true, if the current node contains any children.
	 *
	 * @return boolean
	 */
	public function hasChildren ()
	{

		if ( !is_array($this->_subnodes) )
		{
			return false;
		}

		return sizeof($this->_subnodes) > 0;
	}

	/**
	 * Returns the number of the children.
	 *
	 * @return integer
	 */
	public function countChildren ()
	{

		if ( !is_array($this->_subnodes) )
		{
			return 0;
		}

		return sizeof($this->_subnodes);
	}

	/**
	 * Returns the last child of the node.
	 *
	 * @return object
	 */
	public function &getLastChild ()
	{

		if ( !is_array($this->_subnodes) )
		{
			return null;
		}
		if ( sizeof($this->_subnodes) > 0 )
		{
			return end($this->_subnodes);
		}

		return null;
	}

	/**
	 * Returns the array containing all the children.
	 *
	 * @return array
	 */
	public function &getChildren ()
	{

		return $this->_subnodes;
	}

	/**
	 * @param $array
	 * @return array
	 */
	private function _arrayReduceHoles ( $array )
	{

		$newArray = array ();
		foreach ( $array as $value )
		{
			$newArray[ ] = $value;
		}

		return $newArray;
	}

	/**
	 * @param $array
	 * @param $where
	 * @return array
	 */
	private function _arrayCreateHole ( $array, $where )
	{

		$newArray = array ();
		$i        = 0;
		foreach ( $array as $value )
		{
			if ( $i == $where )
			{
				$newArray[ $i ] = null;
				$i++;
			}
			$newArray[ $i ] = $value;
			$i++;
		}

		return $newArray;
	}

}