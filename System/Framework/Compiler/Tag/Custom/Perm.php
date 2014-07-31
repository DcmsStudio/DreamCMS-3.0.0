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
 * @file         Perm.php
 */

class Compiler_Tag_Custom_Perm extends Compiler_Tag_Abstract
{
	/**
	 *
	 */
	public function configure()
	{
		$this->tag->setAttributeConfig(
			array(
			     'key'     => array(
				     Compiler_Attribute::REQUIRED,
				     Compiler_Attribute::HARD_STRING ),
			     'require' => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::BOOL ),
			     'default' => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::BOOL )
			)
		);
	}

	/**
	 *
	 * @return void
	 */
	public function process()
	{
		if ( $this->tag->isEndTag() )
		{
			return;
		}

		$key = $this->getAttributeValue( 'key' );
		$required = $this->getAttributeValue( 'require' );
		$default = $this->getAttributeValue( 'default' );

		switch ( strtolower( $default ) )
		{
			case 'true':
			case 'false':
				$default = strtolower( $default );
				break;
			case '1':
			case 1:
				$default = 'true';
				break;
			case '0':
			case 0:
			default:
				$default = 'false';
				break;
		}

		if ( $required != '' )
		{
			switch ( strtolower( $required ) )
			{
				case 'true':
				case 'false':
					$_require = strtolower( $required );
					break;
				case 0:
					$_require = 'false';
					break;

				case '1':
				case 1:
				default:
					$_require = 'true';
					break;
			}
		}

		$this->setStartTag( 'if ( User::hasPerm(\'' . $key . '\'' . ($default ? ', ' . $default : '') . ') === ' . $_require . '){ ' );
		$this->setEndTag( '} ' );
	}

}
