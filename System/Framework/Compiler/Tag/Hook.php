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
 * @file         Hook.php
 */
class Compiler_Tag_Hook extends Compiler_Tag_Abstract
{
	/**
	 *
	 */
	public function configure()
	{
		$this->tag->setAttributeConfig(
			array(
			     'name' => array(
				     Compiler_Attribute::REQUIRED,
				     Compiler_Attribute::ID )
			)
		);
	}

	public function process()
	{
		$this->set( 'nophp', false );

		$name = $this->getAttributeValue( 'name' );


		if ( !$name || (defined( 'ADM_SCRIPT' ) && ADM_SCRIPT) )
		{
			return;
		}


		$isBefore = true;
		if ( substr( $name, 0, 6 ) === 'Before' )
		{
			$name = substr( $name, 6 );
		}

		if ( substr( $name, 0, 5 ) === 'After' )
		{
			$name = substr( $name, 5 );
			$isBefore = false;
		}


		if ( $name )
		{
			$start = 'if (!isset($__lay) ){ $__lay = new Layouter; } echo $__lay->getLayoutBlock(\'' . $name . '\', ' . ($isBefore ? 'true' : 'false') . ');';
			$this->appendStartTag( $start );
		}
	}
}