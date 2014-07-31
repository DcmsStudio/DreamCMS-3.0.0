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
 * @file         Set.php
 */
class Compiler_Tag_Set extends Compiler_Tag_Abstract
{
	/**
	 *
	 */
	public function configure()
	{
		$this->tag->setAttributeConfig(
			array(
			     'var'       => array(
				     Compiler_Attribute::REQUIRED,
				     Compiler_Attribute::ID ),
			     'value'     => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::STRING ),
			     'cachetime' => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::NUMBER )
			)
		);
	}

	public function process()
	{

		if ( !$this->tag->hasAttribute( 'var' ) )
		{

		}

		if ( !$this->tag->hasAttribute( 'value' ) )
		{

		}

		$var = $this->getAttributeValue( 'var' );
		$ns = $this->isNamespacedAttribute( 'value' );

		$mode = Compiler_Attribute::STRING;

		$isBool = false;
		$isNumber = false;

		if ( !$ns && $ns != 'str' )
		{
			$v = $this->getAttributeValue( 'value', false, true );

			if ( preg_match( '/^(true|false)$/i', $v) )
			{
				$vs = $v;
				$isBool = true;
			}
			else if ( preg_match( '/^([\d]+?)$/', $v ) )
			{
				$vs = $v;
				$isNumber = true;
			}
			else {
				$vs =  "'" . addcslashes( $v , "'" ) . "'";
			}


			$value =  $vs;
		}
		else
		{
			$v = $this->getAttributeValue( 'value', false, true );

			if ( preg_match( '/^(true|false)$/i', $v) )
			{
				$vs = $v;
				$isBool = true;
			}
			else if ( preg_match( '/^([\d]+?)$/', $v ) )
			{
				$vs = $v;
				$isNumber = true;
			}
			else {
				$vs =  "'" . addcslashes( $v , "'" ) . "'";
			}


			$value = $vs;
		}


		if ( $value == "''" )
		{
			$value = "''";
		}

		if ( !is_array( $value ) )
		{
			$value = array(
				0 => $value
			);
		}

		if ( substr( $var, 0, 1 ) !== '$' )
		{
			$var = $this->tag->getTemplateInstance()->compileVariable( '$' . $var );
		}

		$_value = $this->getAttributeValue( 'value', false, false, Compiler_Attribute::STRING );


		if (preg_match( '/^\'(true|false)\'$/i', $_value[ 0 ])) {
			$_value[ 0 ] = substr($_value[ 0 ], 1, -1);
		}
		if (preg_match( '/^\'([\d]+?)\'$/i', $_value[ 0 ])) {
			$_value[ 0 ] = substr($_value[ 0 ], 1, -1);
		}





		$start = $var . ' = ' .$_value[ 0 ] . ';'; //$this->assignData(\''. $this->getAttributeValue('var', false, true) .'\', ' . $value[0] . ');';
		$end = null;

		$this->setStartTag( $start );
	}
}