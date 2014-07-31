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
 * @file         Seemode.php
 */

class Compiler_Tag_Custom_Seemode extends Compiler_Tag_Abstract
{
	/**
	 *
	 */
	public function configure()
	{
		$this->tag->setAttributeConfig(
			array(
			     'tpl'       => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::STRING ),
			     'modul'     => array(
				     Compiler_Attribute::REQUIRED,
				     Compiler_Attribute::STRING ), // (controller)
			     'contentid' => array(
				     Compiler_Attribute::REQUIRED,
				     Compiler_Attribute::STRING ),
			     'edit'      => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::STRING ), // (action)
			     'publish'   => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::STRING ), // (action)
			     'state'     => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::STRING ), //
			     'container' => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::STRING ), //
			     'delete'    => array(
				     Compiler_Attribute::OPTIONAL,
				     Compiler_Attribute::STRING ), // (action)
			)
		);
	}

	/**
	 *
	 * @throws Compiler_Exception
	 * @return void
	 */
	public function process()
	{


		$template = $this->getAttributeValue( 'tpl' );
		$modul = $this->getAttributeValue( 'modul' );
		$contentid = $this->getAttributeValue( 'contentid' );
		$edit = $this->getAttributeValue( 'edit' );
		$publish = $this->getAttributeValue( 'publish' );
		$state = $this->getAttributeValue( 'state' );
		$delete = $this->getAttributeValue( 'delete' );
		$container = $this->getAttributeValue( 'container' );

		if ( !$edit[ 0 ] && !$publish[ 0 ] && !$delete[ 0 ] )
		{
			throw new Compiler_Exception( '' );
		}

		$url = 'adm=' . Compiler_Abstract::PHP_OPEN . ' echo ' . $modul[ 0 ] . ';' . Compiler_Abstract::PHP_CLOSE;

		$editLink = '';
		$publishLink = '';
		$deleteLink = '';


		if ( $edit[ 0 ] )
		{
			$editLink = Compiler_Abstract::PHP_OPEN . ' echo ' . $edit[ 0 ] . ';' . Compiler_Abstract::PHP_CLOSE;
		}

		if ( $publish[ 0 ] )
		{
			$publishLink = Compiler_Abstract::PHP_OPEN . ' echo ' . $publish[ 0 ] . ';' . Compiler_Abstract::PHP_CLOSE;
		}

		if ( $delete[ 0 ] )
		{
			$deleteLink = Compiler_Abstract::PHP_OPEN . ' echo ' . $delete[ 0 ] . ';' . Compiler_Abstract::PHP_CLOSE;
		}


		if ( !$container[ 0 ] )
		{
			$container[ 0 ] = '\'parent\'';
		}

		if ( $template[ 0 ] )
		{

		}
		else
		{
			$code = Compiler_Abstract::PHP_OPEN . ' if (User::isAdmin() && Session::get(\'seemode\')) { ' . Compiler_Abstract::PHP_CLOSE . '<div class="seemode-content"><div class="seemode-button"><span>' . Compiler_Abstract::PHP_OPEN . ' echo trans(\'Inhalt\'); ' . Compiler_Abstract::PHP_CLOSE . '</span></div>'
				. '<div controller="' . Compiler_Abstract::PHP_OPEN . ' echo ' . $modul[ 0 ] . ';' . Compiler_Abstract::PHP_CLOSE . '" contentid="' . Compiler_Abstract::PHP_OPEN . 'echo ' . $contentid[ 0 ] . ';' . Compiler_Abstract::PHP_CLOSE . '" id="seemode-' . Compiler_Abstract::PHP_OPEN . 'echo ' . $contentid[ 0 ] . ';' . Compiler_Abstract::PHP_CLOSE . '" class="seemode-actions">';

			if ( $publishLink )
			{
				$code .= '<span container="' . Compiler_Abstract::PHP_OPEN . 'echo ' . $container[ 0 ] . ';' . Compiler_Abstract::PHP_CLOSE . '" act="' . $publishLink . '" class="publish' . Compiler_Abstract::PHP_OPEN . 'if (empty(' . $state[ 0 ] . ')){ echo \' off\'; } ' . Compiler_Abstract::PHP_CLOSE . '" title="Change Publishing"></span>';
			}
			if ( $editLink )
			{
				$code .= '<span act="' . $editLink . '" class="edit" title="Edit"></span>';
			}
			if ( $deleteLink )
			{
				$code .= '<span act="' . $deleteLink . '" class="delete"></span>';
			}
			$code .= '</div></div>' . Compiler_Abstract::PHP_OPEN . ' } ' . Compiler_Abstract::PHP_CLOSE;


			$this->set( 'nophp', true );
			$this->setStartTag( $code );
		}
	}

}
