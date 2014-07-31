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
 * @package      Clickanalyser
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Analyse.php
 */
class Clickanalyser_Action_Analyse extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$url  = base64_decode(HTTP::input('url'));
		$_url = preg_replace("/(https?\:\/\/)(www\.)?/", "", $url);
		$_url = preg_replace("/\/$/", "", $_url);
		if ( file_exists(PAGE_CACHE_PATH . 'clickanalyse-' . md5($_url) . '.xml') )
		{


			$xml = new Xml();


			#$doc = simplexml_load_file(PAGE_CACHE_PATH . 'clickanalyse-' . md5($_url) . '.xml');
			$arr = $xml->createArray(file_get_contents(PAGE_CACHE_PATH . 'clickanalyse-' . md5($_url) . '.xml'));
			#$arr['clicks'] = array_pop($arr);
			# print_r( $arr );
			# exit;
			$data[ 'clicks' ] = array ();
			if ( is_array($arr[ 'clickanalyse' ][ 'click' ]) )
			{
				foreach ( $arr[ 'clickanalyse' ][ 'click' ] as $index => $r )
				{
					if ( is_array($r[ 'attributes' ]) )
					{
						$data[ 'clicks' ][ ] = $r[ 'attributes' ];
					}
				}
			}

			$data[ 'auccess' ] = true;
			echo Library::json($data);

			exit;
		}
		else
		{
			Library::sendJson(true);
		}
	}

}

?>