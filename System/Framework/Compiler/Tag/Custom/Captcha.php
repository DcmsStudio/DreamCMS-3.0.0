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
 * @file         Captcha.php
 */

class Compiler_Tag_Custom_Captcha extends Compiler_Tag_Abstract
{

	/**
	 * @var
	 */
	private static $_id;

	/**
	 * @var array
	 */
	private static $_a = array (
		'name',
		'width',
		'height',
		'audio',
		'reload',
		'quality',
		'bgcolor'
	);

	/**
	 *
	 */
	public function configure ()
	{

		$this->tag->setAttributeConfig(array (

		                                     'audio'  => array (
			                                     Compiler_Attribute::OPTIONAL,
			                                     Compiler_Attribute::BOOL
		                                     ),
		                                     'reload' => array (
			                                     Compiler_Attribute::OPTIONAL,
			                                     Compiler_Attribute::BOOL
		                                     ),
		                                     'width'  => array (
			                                     Compiler_Attribute::OPTIONAL,
			                                     Compiler_Attribute::NUMBER
		                                     ),
		                                     'height' => array (
			                                     Compiler_Attribute::OPTIONAL,
			                                     Compiler_Attribute::NUMBER
		                                     ),
		                                     'name'   => array (
			                                     Compiler_Attribute::OPTIONAL,
			                                     Compiler_Attribute::HARD_STRING
		                                     )

		                               ));
	}

	public function process ()
	{

		$parms = array ();
		foreach ( self::$_a as $key )
		{
			if ( $key )
			{
				$parms[ $key ] = $this->getAttributeValue($key);
			}
		}

		if ( !isset( $parms[ 'reload' ] ) )
		{
			$parms[ 'reload' ] = true;
		}

		if ( !isset( $parms[ 'audio' ] ) )
		{
			$parms[ 'audio' ] = false;
		}

		if ( !isset( $parms[ 'name' ] ) || empty( $parms[ 'name' ] ) )
		{
			$parms[ 'name' ] = Compiler_Library::UUIDv4();
		}


		$this->setStartTag('$captchaopts = ' . var_export($parms, true) . '; $captchaCode = Captcha::regenerate($captchaopts); echo Captcha::getCaptcha($captchaopts, $captchaCode);unset($captchaCode);');
	}

}