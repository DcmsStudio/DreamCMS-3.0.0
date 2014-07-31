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
 * @package      Tooltip
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Tooltip_Action_Index extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}
		$tip = HTTP::input('tip');

		if ( empty($tip) )
		{
			echo Library::json(array (
			                         'success' => false,
			                         'msg'     => 'Empty Tip ...'
			                   ));
			exit;
		}

		$r = explode('|', urldecode($tip));

		$path = XMLDATA_PATH . "tooltip_";

		if ( !file_exists($path . $r[ 0 ] . '.xml') )
		{

			echo Library::json(array (
			                         'success' => false,
			                         'msg'     => 'Tip file not exists ...'
			                   ));
			exit;
		}
		else
		{
			if ( empty($r[ 1 ]) )
			{
				echo Library::json(array (
				                         'success' => false,
				                         'msg'     => 'Tip request key is empty ...'
				                   ));
				exit;
			}

			$doc = new DOMDocument();
			$doc->load($path . $r[ 0 ] . '.xml');

			// $dom = simplexml_load_file($path . $r[0] . '.xml' );

			$root = $doc->getElementsByTagName($r[ 1 ]);

			if ( !$root->length )
			{
				// $results = print_r($xmlarr, true);
				echo Library::json(array (
				                         'success' => true,
				                         'title'   => "Tip key (" . $r[ 0 ] . ") not exists ...",
				                         'content' => 'Empty Content Key:' . $r[ 1 ]
				                   ));
				exit;
			}

			$el    = $root->item(0);
			$title = $el->getAttribute('title');
			$value = $el->nodeValue;

			echo Library::json(array (
			                         'success' => true,
			                         'title'   => ($title ? $title : 'Empty Title (Key:' . $r[ 0 ] . ')'),
			                         'content' => ($value ? nl2br($value) : 'Empty Content')
			                   ));
			exit;
		}
	}

}

?>