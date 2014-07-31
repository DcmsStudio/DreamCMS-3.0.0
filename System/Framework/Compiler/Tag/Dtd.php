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
 * @file         Dtd.php
 */


class Compiler_Tag_Dtd extends Compiler_Tag_Abstract
{

	/**
	 * @var bool
	 */
	private static $_isset = false;
	/**
	 *
	 */
	public function configure()
	{
		$this->tag->setAttributeConfig(
			array(
			     'type'    => array(
				     Compiler_Attribute::REQUIRED,
				     Compiler_Attribute::HARD_STRING ),
			)
		);
	}

	public function process()
	{

		if ( self::$_isset )
		{
			throw new Compiler_Exception( 'Multiple cp:dtd tags not allowed in the (x)Html code!' );
		}

		$this->set( 'nophp', true );

		$type = $this->getAttributeValue( 'type' );

		switch ( strtolower( $type ) )
		{
			case 'xhtml10strict':
				$dtd = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
				break;
			case 'xhtml10transitional':
				$dtd = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
				break;
			case 'xhtml10frameset':
				$dtd = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">';
				break;
			case 'xhtml11':
				$dtd = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
				break;
			case 'html40':
			case 'html4':
				$dtd = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
				break;
			case 'html5':
				$dtd = '<!DOCTYPE html>';
				break;
			default:
				throw new Compiler_Exception( 'cp:dtd template attribute: ' . $type );
		}

		if ( isset( $dtd ) )
		{
			$this->setStartTag( $dtd );
			self::$_isset = true;
		}




	}
}