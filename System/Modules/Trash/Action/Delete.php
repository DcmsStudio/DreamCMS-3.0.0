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
 * @package      Trash
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Delete.php
 */
class Trash_Action_Delete extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}


		demoadm();

		$Model = Model::getModelInstance();
		$data  = $Model->getMultipleIds('id', 'ids');

		if ( !$this->_post('empty') )
		{
			if ( !$data[ 'id' ] && !$data[ 'isMulti' ] )
			{
				Library::sendJson(false, "Invalid ID");
			}
		}

		$this->load('Trash');


		if ( !$this->_post('empty') )
		{
			$label = $this->Trash->delete('id', 'ids', false);
			if ( !$data[ 'isMulti' ] )
			{
				Library::sendJson(true, sprintf(trans('Der Eintrag `s` wurde endgültig aus dem Papierkorb gelöscht.'), $label));
			}
			else
			{
				Library::sendJson(true, trans('Die ausgewählten Einträge wurden endgültig aus dem Papierkorb gelöscht.'));
			}
		}
		elseif ( $this->_post('empty') )
		{
			$this->Trash->delete('id', 'ids', true);
			Library::sendJson(true, trans('Der Papierkorb wurde erfolgreich geleert.'));
		}
		else
		{
			Library::sendJson(false, "Invalid ID");
		}
	}

}

?>