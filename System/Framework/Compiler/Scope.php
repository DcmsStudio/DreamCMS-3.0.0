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
 * @file         Scope.php
 */


class Compiler_Scope
{

	/**
	 * stores the scopename from loops/for/foreach (temp)
	 *
	 * @var array
	 */
	protected $scope = array ();
	protected $keyCache = array ();
	protected $index = 0;

	private static $x = 0;

	/**
	 * @var Compiler
	 */
	private $compiler = null;

    /**
     * @param $compiler
     */
    public function __construct($compiler)
	{
		$this->compiler = $compiler;
	}

    /**
     *
     *
     * @param string $originalname
     * @param string $scopename the varname in loop/foreach/for/tree
     * @param $source
     */
	public function addScope ( $originalname, $scopename, $source)
	{
		$this->compiler->addScope ( $originalname, $scopename, $source);
	}

    /**
     *
     * @param string $originalname
     * @param int $nested
     */
	public function removeScope ( $originalname, $nested = 0 )
	{
		$this->compiler->removeScope ( $originalname );
	}

    /**
     * @param $originalname
     * @param int $nested
     */
    public function removeLastScope ( $originalname, $nested = 0 )
	{
		$this->compiler->removeScope ( $originalname );
	}

    /**
     * @param $name
     * @return array
     */
	public function getScope ( $name )
	{
		return $this->compiler->getScope ( $name );
	}

	/**
	 * @return array
	 */
	public function getScopes ()
	{

		return $this->compiler->getScopes();
	}

	/**
	 * @return array
	 */
	public function getLastScope ()
	{
		$last  = $this->compiler->getLastScope() ;

		return (is_array($last) ? $last : null);
	}

    /**
     * @param null $scopes
     */
    public function setScopes ( $scopes = null )
	{
		#$this->scope = $scopes;
	}

    /**
     * @param $name
     * @return bool
     */
    public function isScope ( $name )
	{
		$val = $this->compiler->getScope ( $name );

		return ($val !== null ? true : false);
	}

}