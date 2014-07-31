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
 * @package      DreamCMS
 * @version      3.0.0 Beta
 * @category     Template Engine
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Groupbutton.php
 */
class Compiler_Tag_Groupbutton extends Compiler_Tag_Abstract
{

	/**
	 *
	 */
	public function configure ()
	{

		$this->tag->setAttributeConfig(array (
		                                     'addtb'  => array (
			                                     Compiler_Attribute::OPTIONAL,
			                                     Compiler_Attribute::BOOL
		                                     ),
		                                     'size'  => array (
			                                     Compiler_Attribute::OPTIONAL,
			                                     Compiler_Attribute::STRING
		                                     ),
		                                     'label' => array (
			                                     Compiler_Attribute::OPTIONAL,
			                                     Compiler_Attribute::EXPRESSION
		                                     ),

		                               ));
	}

	public function process ()
	{

		$label = $this->getAttributeValue('label');
		$size  = $this->getAttributeValue('size');
		$addtb  = $this->getAttributeValue('addtb');

		//
		$attr  = $this->getCompiledHtmlAttributes();
		$_attr = '';
		if ( !empty( $attr ) )
		{
			$attr  = preg_replace('#class\s*=\s*(["\'])([^\1]*)\1#isU', '', $attr);
			$_attr = ' ' . $attr;
		}

		$this->set('nophp', true);

		$_start = '';
		$_end = '';
		if ($addtb)
		{
			$_start .= '<div class="btn-toolbar" role="toolbar">';
			$_end .= '</div>';
		}

		if ( $label[ 0 ] )
		{
			$this->setStartTag($_start.'<div class="btn-group-container">
    <div class="btn-group' . ( $size[ 0 ] ? ' ' . $size[ 0 ] : '' ) . '"' . $_attr . '>');

			$this->setEndTag('
    </div>
    <div class="btn-group-label">' . Compiler_Abstract::PHP_OPEN . 'echo ' . $label[ 0 ] . ';' . Compiler_Abstract::PHP_CLOSE . '</div>
</div>'. $_end);
		}
		else
		{
			$this->setStartTag($_start . '
    <div class="btn-group' . ( $size[ 0 ] ? ' ' . $size[ 0 ] : '' ) . '"' . $_attr . '>');

			$this->setEndTag('
    </div>' . $_end);
		}
	}


}
