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
 * @file         Tag.php
 */
class Compiler_Tag_Tag extends Compiler_Tag_Abstract
{
	/**
	 *
	 */
	public function configure()
	{
		$this->tag->setAttributeConfig(
			array(
			     'name'       => array(
				     Compiler_Attribute::REQUIRED,
				     Compiler_Attribute::EXPRESSION ),
			     'single'     => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::BOOL,
				     false ),
			     'ns'         => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::EXPRESSION ),
			     'forceclose' => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::BOOL,
				     false ),
			)
		);
	}

	public function process()
	{
		$name = $this->getAttributeValue( 'name' );
		$single = $this->getAttributeValue( 'single' );
		$ns = $this->getAttributeValue( 'ns' );
		$forceclose = $this->getAttributeValue( 'forceclose' );

		// clean attributes
		$this->removeAttribute( 'name' );
		$this->removeAttribute( 'single' );
		$this->removeAttribute( 'ns' );
		$this->removeAttribute( 'forceclose' );

		/*
		// get children Attributes Tags
		if ( $this->countChildren() )
		{

		}
*/
		$this->set( 'nophp', true );

		$attr = $this->getCompiledHtmlAttributes();
		if ( !empty( $attr ) )
		{
			$attr = ' ' . $attr;
		}

		$name[ 0 ] = substr( $name[ 0 ], 1, -1 );


		if ( $single )
		{
			$this->setStartTag( '<' . $name[ 0 ] . $attr . '/>' );
		}
		else
		{
			$this->setStartTag( '<' . $name[ 0 ] . $attr . '>' );
			$this->setEndTag( '</' . $name[ 0 ] . '>' );

			if ( $forceclose )
			{
				$this->setStartTag( '</' . $name[ 0 ] . '>' );
				$this->setEndTag( '' );
			}
		}
	}
}