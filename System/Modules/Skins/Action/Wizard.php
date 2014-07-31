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
 * @file         Wizard.php
 */
class Skins_Action_Wizard extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}


		$tagDefine = null;

		if ( HTTP::input('tag') )
		{
			$tag = HTTP::input('tag');
			$tag = strtolower(preg_replace('/([^a-z:]*)/i', '', $tag));

			$tag = str_replace('cp:', '', $tag);

			// read attributes of this tag
			if ( empty($tag) )
			{
				Library::sendJson(false, trans('Es wurde keine Tag übergeben.'));
			}

			// read attributes of this tag
			if ( !file_exists(DATA_PATH . 'system/tagwizard/tags/' . $tag . '.php') )
			{
				Library::sendJson(false, sprintf(trans('Es wurde keine Tag-Definition für den Tag `%s` gefunden.'), $tag));
			}

			include(DATA_PATH . 'system/tagwizard/tags/' . $tag . '.php');

			if ( empty($tagDefine) )
			{
				Library::sendJson(false, sprintf(trans('Es wurde keine Tag-Definition für den Tag `%s` gefunden.'), $tag));
			}
		}
		elseif ( HTTP::input('fnc') )
		{
			$function = HTTP::input('fnc');
			$function = strtolower(preg_replace('/([^a-z0-9]*)/i', '', $function));

			// read attributes of this tag
			if ( empty($function) )
			{
				Library::sendJson(false, trans('Es wurde keine Function übergeben.'));
			}

			// read attributes of this tag
			if ( !file_exists(DATA_PATH . 'system/tagwizard/function/' . $function . '.php') )
			{
				Library::sendJson(false, sprintf(trans('Es wurde keine Functions-Definition für die Function `%s` gefunden.'), $function));
			}

			include(DATA_PATH . 'system/tagwizard/function/' . $function . '.php');
		}


		$data        = array ();
		$_attributes = array ();
		if ( isset($tagDefine[ 'attributes' ]) )
		{
			$_attributes = $tagDefine[ 'attributes' ];
		}

		$_parsed = array ();
		foreach ( $_attributes as $name => $r )
		{
			$r[ 'fieldname' ] = $name;

			if ( is_array($r[ 'values' ]) )
			{

				$tmp = array ();
				foreach ( $r[ 'values' ] as $arr )
				{
					$tmp[ ] = array (
						'label'   => $arr[ 1 ],
						'value'   => $arr[ 0 ],
						'checked' => false
					);
				}


				$r[ 'values' ] = $tmp;
			}

			$_parsed[ ] = $r;
		}


		$data[ 'tag' ][ 'name' ]        = ucfirst(strtolower($tagDefine[ 'tagname' ]));
		$data[ 'tag' ][ 'description' ] = $tagDefine[ 'description' ];

		$data[ 'attributes' ] = $_parsed;


		$this->Template->process('skins/tagwizard', $data, true);
		/*
		  $data = array('success' => true, 'wizard'  => $html);

		  echo Library::json($data);
		 *
		 */
		exit;
	}

}
