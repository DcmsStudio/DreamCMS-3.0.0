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
 * @file         Do.php
 */
class Compiler_Tag_Do extends Compiler_Tag_Abstract
{


	/**
	 *
	 */
	public function configure()
	{
		$this->tag->setAttributeConfig(
			array(
			     'plugin' => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::STRING ),
			     'modul'  => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::STRING ),
			     'show'   => array(
				     Compiler_Attribute::REQUIRED,
				     Compiler_Attribute::STRING )
			)
		);
	}

	public function process()
	{

		$plugin = $this->getAttributeValue( 'plugin' );
		$modul = $this->getAttributeValue( 'modul' );

		if ( empty( $modul ) && empty( $plugin ) )
		{
			throw new Compiler_Exception( 'The tag cp:do requires attribute "modul" or "plugin"! Please check your Templates.' );
		}

		$show = $this->getAttributeValue( 'show' );
		$this->set( 'nophp', false );

		// as array string
		$_attr = $this->getAttributeArray();
		unset( $_attr[ 'modul' ], $_attr[ 'show' ] );
		$attr = $this->var_export_min( $_attr, true );

		$start = 'echo Api::callModul(' . $modul[ 0 ] . ', ' . $show[ 0 ] . ', ' . $attr . ');';
		$this->appendStartTag( $start );
	}

}
