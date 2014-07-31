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
 * @file         Token.php
 */


class Compiler_Tag_Token extends Compiler_Tag_Abstract
{

	/**
	 *
	 */
	public function configure ()
	{

		$this->tag->setAttributeConfig(array (
		                                     'get'  => array (
			                                     Compiler_Attribute::OPTIONAL,
			                                     Compiler_Attribute::BOOL
		                                     ),
		                                     'name' => array (
			                                     Compiler_Attribute::OPTIONAL,
			                                     Compiler_Attribute::ID
		                                     )
		                               ));
	}


	public function process ()
	{

		$name = $this->getAttributeValue('name');
		$get  = $this->getAttributeValue('get');


		if ( $get )
		{
			$this->set('nophp', false);
			if ( $name )
			{
				$this->setStartTag(' echo Csrf::generateCSRF(\'' . $name . '\'); ');
			}
			else
			{
				$this->setStartTag(' echo Csrf::generateCSRF(\'token\'); ');
			}
		}
		else
		{
			$this->set('nophp', true);
			if ( $name )
			{
				$start = Compiler_Abstract::PHP_OPEN . ' Csrf::generateCSRF(\'' . $name . '\'); ' . Compiler_Abstract::PHP_CLOSE;
				$this->setStartTag($start . '<input type="hidden" name="_fsend" value="1"/><input name="' . $name . '" value="' . Compiler_Abstract::PHP_OPEN . ' echo Csrf::getCSRFToken(\'token\'); ' . Compiler_Abstract::PHP_CLOSE . '" type="hidden"/>');
			}
			else
			{
				$start = Compiler_Abstract::PHP_OPEN . ' Csrf::generateCSRF(\'token\'); ' . Compiler_Abstract::PHP_CLOSE;
				$this->setStartTag($start . '<input type="hidden" name="_fsend" value="1"/><input name="token" value="' . Compiler_Abstract::PHP_OPEN . ' echo Csrf::getCSRFToken(\'token\'); ' . Compiler_Abstract::PHP_CLOSE . '" type="hidden"/>');
			}
		}
	}
}