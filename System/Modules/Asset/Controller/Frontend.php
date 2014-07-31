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
 * @package      Asset
 * @version      3.0.0 Beta
 * @category     Controller
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Frontend.php
 */
class Asset_Controller_Frontend extends Controller_Abstract
{

	/**
	 *
	 * @param Controller $controller
	 */
	public function runAction ( Controller $controller )
	{

		$_headerMode  = null;
		$_errorOutput = null;
		$_action      = strtolower(ACTION);

		switch ( $_action )
		{
			case 'img':
				$_headerMode  = 'gif';
				$_errorOutput = base64_decode("R0lGODlhAQABAPcAAPz+/AAAAAAAAAAAAG+MKBrp6wATEwAAADoARRoACQAAkgAAfAAATgDwCRb9kgB/fAkBMQAA0QAAAAAAAAAA1ALw6QD9EwB/AAABAAEAAAAMAAAAAMw0FOjq7RMTEwAAAOkAGOUA7oEAkXwAfADohAAG7QEWEwAAAFagCADpAAATAAAAANQEiOeu6hM7EwB+AHMAvgAAAwAMkgAAfPQAKugA6xMAEwAAABgAJ+4AAJEAAHwAAHCwcAUkCZLEknwAfP8BwP8A5P8AmP8AfG1F7wUJQJKSknx8fIVOu+cJQIGSknx8fAAEUADtcxYTFgAAAFgkAAMAdAACFgAAAIjwADrqABcTAAAAAPgCAm8AABYAAAAAAACQJwBBAACSAAB8AH4AAACAAAD9DMB/AAAFAAAQ8ACR/QB8f/+YAP/pAP8TAP8AAP8ACP8AAP8ACv8AAABoiADqPgATkgAAfAAYGgDuAgCRAAB8AABwqAAJ7RaSEwB8AATAYOnknhOYgAB8fNJvGOY+IYGSF3x8APhiAG8+ABaSAAB8AEoIB+MCAIEAAHwAAKCEAHftAFATAAAAAPgAAG8AIAEAFwAAAGtGAAAAAAAAAAAAAEC3AOjBABMAAAAAAAAcAADqAAATAAAAAKyFAPsrABODAAB8ABgAaO4AnpEAgHwAfHAA/wUA/5IA/3wA//8AYP8Anv8AgP8AfG0pLQW3AJKTAHx8AEoYLfQhAIAXAHwAAAA0CABk9BaDEwB8AAD//wD//wD//wD///gAAG8AABYAAAAAAAAASAHq6wATEwAAAAA09gBkOACDTAB8AFesKPT764ATE3wAACwYd+ruEBORTwB8APgAXG+36xaTEwB8AL7/NJT/ZDb/g37/fDUYASEh7DkXE34AAKY00Qdk/zmD/wB8fw9E1ADr6wATEwAAAADnGABkIQCDFwB8AACINABkZACDgwB8fAABGAAAIQAAFwAAAKsxtwIAwQAAAAAAAONtABiZADiAAH5DAAAAjQAA4gAARwAAACH5BAEAAAAALAAAAAABAAEABwgEAAEEBAA7");
				break;

			case 'js':
				$_headerMode  = 'js';
				$_errorOutput = "/**\nPermission Error!\nTo load this Javascript must be login or you have no permission to load this file!\n**/";
				break;

			case 'css':
				$_headerMode  = 'css';
				$_errorOutput = "/**\nPermission Error!\nTo load this CSS File must be login or you have no permission to load this file!\n**/";
				break;
		}


		// Permission Check?
		$this->checkPermsBeforeExecuteAction($_headerMode, $_errorOutput);


		// Execute
		$this->executeAction();
	}

}
