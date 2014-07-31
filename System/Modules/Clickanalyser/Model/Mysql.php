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
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Mysql.php
 */
class Clickanalyser_Model_Mysql extends Model
{

	/**
	 *
	 * @param array $mouseTracker
	 */
	public function saveMouseTracker ( $mouseTracker )
	{

		if ( HTTP::input('url') )
		{
			$_url = HTTP::input('url');
			$_url = preg_replace("/(https?\:\/\/)(www\.)?/", "", $_url);
			$_url = preg_replace("/\/$/", "", $_url);

			if ( file_exists(PAGE_CACHE_PATH . 'mousetracker-' . md5($_url) . '.xml') )
			{
				$doc = new DOMDocument('1.0', 'iso-8859-1');
				$doc->load(PAGE_CACHE_PATH . 'mousetracker-' . md5($_url) . '.xml');
				$root = $doc->documentElement;


				$click = $doc->createElement("track");
				$root->appendChild($click);
				$click->setAttribute("time", time());
				$click->appendChild($doc->createTextNode(serialize($mouseTracker)));

				file_put_contents(PAGE_CACHE_PATH . 'mousetracker-' . md5($_url) . '.xml', $doc->saveXML());
			}
			else
			{
				$doc = new DOMDocument('1.0', 'iso-8859-1');

				$root = $doc->createElement('trackanalyse');
				$root = $doc->appendChild($root);


				$location = $doc->createElement("location");
				$root->appendChild($location);
				$location->appendChild($doc->createTextNode(HTTP::input('url')));
				$click = $doc->createElement("track");
				$root->appendChild($click);
				$click->setAttribute("time", time());
				$click->appendChild($doc->createTextNode(serialize($mouseTracker)));

				file_put_contents(PAGE_CACHE_PATH . 'mousetracker-' . md5($_url) . '.xml', $doc->saveXML());
			}

			Library::sendJson(true);
		}
		Library::sendJson(false);
		exit;
	}

	public function saveClick ()
	{

		if ( HTTP::input('x') && HTTP::input('y') && HTTP::input('url') )
		{
			$_url = base64_decode(HTTP::input('url'));
			$_url = preg_replace("/(https?\:\/\/)(www\.)?/", "", $_url);
			$_url = preg_replace("/\/$/", "", $_url);

			if ( file_exists(PAGE_CACHE_PATH . 'clickanalyse-' . md5($_url) . '.xml') )
			{
				$doc = new DOMDocument('1.0', 'iso-8859-1');
				$doc->load(PAGE_CACHE_PATH . 'clickanalyse-' . md5($_url) . '.xml');
				$root = $doc->documentElement;


				$click = $doc->createElement("click");
				$root->appendChild($click);
				$click->setAttribute("x", HTTP::input('x'));
				$click->setAttribute("y", HTTP::input('y'));
				$click->setAttribute("time", time());
				$click->setAttribute("screen", HTTP::input('screen'));

				file_put_contents(PAGE_CACHE_PATH . 'clickanalyse-' . md5($_url) . '.xml', $doc->saveXML());
			}
			else
			{
				$doc = new DOMDocument('1.0', 'iso-8859-1');

				$root = $doc->createElement('clickanalyse');
				$root = $doc->appendChild($root);


				$location = $doc->createElement("location");
				$root->appendChild($location);
				$location->appendChild($doc->createTextNode(base64_decode(HTTP::input('url'))));
				$click = $doc->createElement("click");
				$root->appendChild($click);
				$click->setAttribute("x", HTTP::input('x'));
				$click->setAttribute("y", HTTP::input('y'));
				$click->setAttribute("time", time());
				$click->setAttribute("screen", HTTP::input('screen'));

				file_put_contents(PAGE_CACHE_PATH . 'clickanalyse-' . md5($_url) . '.xml', $doc->saveXML());
			}
			Library::sendJson(true);
		}

		exit;
	}

}

?>