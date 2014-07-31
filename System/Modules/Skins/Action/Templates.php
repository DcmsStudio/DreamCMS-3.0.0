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
 * @package      Skins
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Templates.php
 */
class Skins_Action_Templates extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}


		$id       = (int)HTTP::input('id');
		$tplgroup = trim(HTTP::input('tplgroup'));


		$result = $this->model->listTemplates($id, $tplgroup);


		$data             = array ();
		$data[ 'skinid' ] = $id;


		if ( IS_AJAX && HTTP::input('tplgroup') )
		{

			$data[ 'templates' ] = array ();
			foreach ( $result as $idx => $r )
			{
				if ( !$r[ 'group_name' ] )
				{
					$r[ 'group_name' ] = 'ROOT';
				}

				$r[ 'username' ]        = ($r[ 'username' ] ? $r[ 'username' ] : trans('unbekannt'));
				$r[ 'updated' ]         = ($r[ 'updated' ] ? date('d.m.Y, H:i:s', $r[ 'updated' ]) : '---');
				$data[ 'templates' ][ ] = $r;
				$data[ 'skintitle' ]    = $r[ 'skintitle' ];
			}


			$data[ 'success' ] = true;
			echo Library::json($data);
			exit;
		}


		foreach ( $result as $idx => &$r )
		{
			if ( !$r[ 'group_name' ] )
			{
				$r[ 'group_name' ] = 'ROOT';
			}

			$data[ 'skintitle' ] = $r[ 'skintitle' ];
			break;
		}

		$data[ 'templates' ] = $result;



		Library::addNavi(trans('Frontend Skins Übersicht'));
		Library::addNavi(sprintf(trans('Frontend Skin `%s`'), $data[ 'skintitle' ]));
		Library::addNavi(sprintf(trans('Templates des Skin `%s`'), $data[ 'skintitle' ]));


		$this->Template->process('skins/list_templates', $data, true);
	}

}

?>