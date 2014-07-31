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
 * @file         Export.php
 */
class Skins_Action_Export extends Controller_Abstract
{

	private function escapeCDATA ( $str )
	{

		$str = str_replace('<![CDATA[', '@@![CDATA[', $str);

		return str_replace(']]>', ']]@@', $str);
	}

	private function unescapeCDATA ( $str )
	{

		$str = str_replace('@@![CDATA[', '<![CDATA[', $str);

		return str_replace(']]@@', ']]>', $str);
	}

	private function createDOMElement ( $source, $tagName )
	{

		if ( !is_array($source) )
		{
			$element = $this->dom->createElement($tagName);
			$element->appendChild($this->dom->createCDATASection($this->escapeCDATA($source)));

			return $element;
		}

		$element = $this->dom->createElement($tagName);

		foreach ( $source as $key => $value )
		{
			if ( is_string($key) )
			{
				foreach ( (is_array($value) ? $value : array (
					$value
				)) as $elementKey => $elementValue )
				{
					$element->appendChild($this->createDOMElement($elementValue, $key));
				}
			}
			else
			{
				$element->appendChild($this->createDOMElement($value, $tagName));
			}
		}

		return $element;
	}

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$id = (int)HTTP::input('skinid');

		if ( !$id )
		{
			Error::raise(trans('Skin existiert leider nicht.'));
		}
		Library::enableErrorHandling();
		error_reporting(E_ALL);


		$xml = new Xml(null, null, null, true);


		$skin      = $this->model->getSkinByID($id);
		$templates = $this->model->getTemplatesBySkinId($id);

		$merged               = array ();
		$merged[ 'skin' ]     = $xml->convertDatabaseArray($skin);
		$merged['templates'][ 'template' ] = $xml->convertDatabaseArray($templates);


		if ( is_dir(PUBLIC_PATH . 'simg/' . $skin[ 'img_dir' ]) )
		{
			$tmp      = array ();
			$iterator = new RecursiveDirectoryIterator(PUBLIC_PATH . 'simg/' . $skin[ 'img_dir' ] . '/');
			foreach ( new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST) as $filename => $file )
			{
				//exclude dot files
				if ( substr($file->getFilename(), 0, 3) === '.DS' || $file->getFilename() == '.' || $file->getFilename() == '..' )
				{
					continue;
				}

				//exclude cache and backup dirs
				if ( strpos($filename, DATA_PATH . 'backup/') !== false || strpos($filename, CACHE_PATH) !== false || !is_readable($filename) )
				{
					continue;
				}

				if ( !is_dir($filename) )
				{
					$paths = explode('/', dirname($filename));
					$dir   = array_pop($paths);

					$tmp[ 'files' ][ ] = array (
						'file' => array (
							'pathname' => array (
								'value' => str_replace(PUBLIC_PATH . 'simg/' . $skin[ 'img_dir' ] . '/', '', dirname($filename))
							),
							'filename' => array (
								'value' => $file->getFilename()
							),
							'data'     => array (
								'cdata' => base64_encode( gzcompress(file_get_contents($filename), 9) )
							)
						)
					);
				}
			}

			$merged[ 'skinfiles' ] = $tmp;
		}

		$xmlCode = $xml->createXML($merged, true);

		Library::makeDirectory(PAGE_PATH . 'export/');
		Library::protectFolder(PAGE_PATH . 'export', true);

		file_put_contents(PAGE_PATH . 'export/skin-' . preg_replace('#([^a-z0-9_\-]*)#i', '', str_replace(' ', '_', $skin[ 'title' ])) . '-export-' . TIMESTAMP . '.xml', $xmlCode);

		Library::sendJson(true, sprintf(trans('Skin "%s" wurde exportiert'), $skin[ 'title' ]));
	}

}

?>