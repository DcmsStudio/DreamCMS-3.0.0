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
 * @package      Importer
 * @version      3.0.0 Beta
 * @category     Helper
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Csv.php
 */
class Importer_Helper_Base
{

	/**
	 * @var array
	 */
	public static $allowedMimes = array (
		'xml' => array (
			'text/xml',
			'application/xml'
		),
		'csv' => 'text/x-comma-separated-values'
	);

	/**
	 *
	 * @var DOMDocument
	 */
	protected $_dom;

	/**
	 *
	 * @var DOMXPath
	 */
	protected $xpath;

	/**
	 *
	 * @param string $filepath
	 * @return DOMDocument
	 */
	public function getDomDocument ( $filepath )
	{

		$this->_dom = new DOMDocument; //( '1.0', 'UTF-8' );

		$old = libxml_use_internal_errors(true);
		$this->_dom->load($filepath);
		// $dom->loadXML( preg_replace( '%xmlns\s*=\s*([\'"]).*\1%sU', '', $xml ) ); // FIX: libxml xpath doesn't handle default namespace properly, so remove it upon XML load
		libxml_use_internal_errors($old);

		return $this->_dom;
	}

	/**
	 *
	 * @return \Importer_Helper_Base
	 */
	public function unsetDomDocument ()
	{

		unset($this->_dom);

		return $this;
	}

	/**
	 *
	 * @return \Importer_Helper_Base
	 */
	public function unsetDomXpath ()
	{

		unset($this->xpath);

		return $this;
	}

	/**
	 *
	 * @param array  $import
	 * @param int    $recordnumber
	 * @param string $filepath
	 * @param string $_xpath
	 * @return DOMElement|void
	 * @throws BaseException
	 */
	public function getDataRecord ( &$import, $recordnumber = 0, $filepath = '', $_xpath = '//' )
	{

		$dom = $this->getDomDocument($filepath);

		// get record counter
		$this->xpath = new DOMXPath($dom);
		$numRecords  = $this->getNumRecords($import, $this->xpath);

		if ( $numRecords === false )
		{
			// error
			throw new BaseException(trans('Invalid XML Records'));
		}

		$el     = null;
		$_xpath = (substr($_xpath, 0, 2) != '//' ? $_xpath : $_xpath);

		if ( ($elements = $this->xpath->query($_xpath)) && $elements->length )
		{
			if ( $recordnumber <= 1 )
			{
				$recordnumber = 1;
			}

			if ( $recordnumber > $numRecords )
			{
				$recordnumber = $numRecords;
			}

			$el = $elements->item(($recordnumber - 1));
		}

		if ( $el !== null )
		{
			return $el;
		}

		return null;
	}

	/**
	 *
	 * @param array $import
	 * @return string|bool
	 */
	public function getRootElement ( &$import )
	{

		if ( !isset($import[ 'root_element' ]) )
		{
			$file         = fopen($import[ 'filepath' ], 'rb');
			$contents     = '';
			$founded_tags = array ();

			while ( !feof($file) )
			{

				$contents .= fread($file, 8192);


				if ( preg_match_all("/<\\w+\\s*[^<|^\n]*\\s*\/?>/i", $contents, $matches, PREG_PATTERN_ORDER) )
				{
					foreach ( $matches[ 0 ] as $tag )
					{
						$tag = explode(" ", trim(str_replace(array (
						                                           '<',
						                                           '>',
						                                           '/'
						                                     ), '', $tag)));
						array_push($founded_tags, $tag[ 0 ]);
					}


					if ( count($founded_tags) >= 1 )
					{
						break;
					}
				}
			}

			$rootElementName = array_shift($founded_tags);

			if ( $rootElementName )
			{
				$import[ 'root_element' ] = $rootElementName;
				unset($contents, $founded_tags);
			}

			fclose($file);
		}


		return (isset($import[ 'root_element' ]) ? $import[ 'root_element' ] : false);
	}

	/**
	 *
	 * @param array $import
	 * @return string|bool
	 */
	public function getRecordElement ( &$import )
	{

		if ( !isset($import[ 'element' ]) )
		{
			$file = new Importer_Helper_Chuck($import[ 'filepath' ], array (
			                                                               'element' => '',
			                                                               'path'    => dirname($import[ 'filepath' ])
			                                                         ));

			$recordElement = null;

			while ( $xml = $file->read() )
			{
				if ( !empty($xml) )
				{
					if ( $file->options[ 'element' ] )
					{
						$recordElement = $file->options[ 'element' ];
						break;
					}
				}
			}

			unset($file);

			if ( $recordElement !== null )
			{
				$import[ 'element' ] = $recordElement;
			}
		}

		return (isset($import[ 'element' ]) ? $import[ 'element' ] : false);
	}

	/**
	 *
	 * @param array $import
	 * @param type  $xpath
	 * @return int|bool
	 */
	public function getNumRecords ( &$import, &$xpath )
	{

		if ( !isset($import[ 'count' ]) && isset($import[ 'xpath' ]) && $import[ 'xpath' ] != '' )
		{
			$query = "count(" . $import[ 'xpath' ] . ")";
			$count = $xpath->evaluate($query);
			if ( $count >= 0 )
			{
				$import[ 'count' ] = $count;
			}
		}

		return (isset($import[ 'count' ]) ? $import[ 'count' ] : false);
	}

	/**
	 *
	 * @param string $text
	 * @param bool   $shorten
	 * @param bool   $is_render_collapsed
	 * @return string
	 */
	protected function render_xml_text ( $text, $shorten = false, $is_render_collapsed = false )
	{

		if ( empty($text) )
		{
			return; // do not display empty text nodes
		}


		if ( preg_match('%\[more:(\d+)\]%', $text, $mtch) )
		{
			$no = (int)$mtch[ 1 ];

			return '<div class="xml-more">[ &dArr; ' . sprintf(trans('<strong>%s</strong> %s more'), $no, trans('elements')) . ' &dArr; ]</div>';
		}
		$more = '';
		if ( $shorten && preg_match('%^(.*?\s+){20}(?=\S)%', $text, $mtch) )
		{
			$text = $mtch[ 0 ];
			$more = '<span class="xml-more">[' . trans('more') . ']</span>';
		}
		$is_short = (strlen($text) <= 40);
		$text     = htmlspecialchars($text);
		$text     = preg_replace('%(?<!\s)\b(?!\s|\W[\w\s])|\w{20}%', '$0&#8203;', $text); // put explicit breaks for xml content to wrap
		return '<div class="xml-content textonly' . ($is_short ? ' short' : '') . ($is_render_collapsed ? ' collapsed' :
			'') . '">' . $text . $more . '</div>';
	}

	/**
	 *
	 * @param DOMElement $el
	 * @param string     $path
	 * @return string
	 */
	protected function render_xml_attributes ( DOMElement $el, $path = '/' )
	{

		$ret = '';

		foreach ( $el->attributes as $attr )
		{
			$ret .= ' <span class="xml-attr" title="' . $path . '@' . $attr->nodeName . '"><span class="xml-attr-name">' . $attr->nodeName . '</span>=<span class="xml-attr-value">"' . htmlspecialchars($attr->value) . '"</span></span>';
		}

		return $ret;
	}

	/**
	 *
	 * @param DOMElement $el
	 * @param bool       $shorten
	 * @param string     $path
	 * @param int        $ind
	 * @param int        $lvl
	 * @return string
	 */
	public function render_xml_element ( DOMElement $el, $shorten = false, $path = '/', $ind = 1, $lvl = 0 )
	{

		$path .= $el->nodeName;
		if ( !$el->parentNode instanceof DOMDocument && $ind > 0 )
		{
			$path .= "[$ind]";
		}

		$ret = '';
		$ret .= '<div class="xml-element lvl-' . $lvl . ' lvl-mod4-' . ($lvl % 4) . '" title="' . $path . '">';
		if ( $el->hasChildNodes() )
		{
			$is_render_collapsed = $ind > 1;
			if ( $el->childNodes->length > 1 || !($el->childNodes->item(0) instanceof DOMText) || strlen(trim($el->childNodes->item(0)->wholeText)) > 40 )
			{
				$ret .= '<div class="xml-expander">' . ($is_render_collapsed ? '+' : '-') . '</div>';
			}
			$ret .= '<div class="xml-tag opening">&lt;<span class="xml-tag-name">' . $el->nodeName . '</span>';
			$ret .= $this->render_xml_attributes($el, $path . '/');
			$ret .= '&gt;</div>';

			if ( 1 == $el->childNodes->length && ($el->childNodes->item(0) instanceof DOMText) )
			{
				$ret .= $this->render_xml_text(trim($el->childNodes->item(0)->wholeText), $shorten, $is_render_collapsed);
			}
			else
			{
				$ret .= '<div class="xml-content' . ($is_render_collapsed ? ' collapsed' : '') . '">';
				$indexes = array ();
				foreach ( $el->childNodes as $child )
				{
					if ( $child instanceof DOMElement )
					{
						if ( empty($indexes[ $child->nodeName ]) )
						{
							$indexes[ $child->nodeName ] = 0;
						}
						$indexes[ $child->nodeName ]++;
						$ret .= $this->render_xml_element($child, $shorten, $path . '/', $indexes[ $child->nodeName ], $lvl + 1);
					}
					elseif ( $child instanceof DOMText )
					{
						$ret .= $this->render_xml_text(trim($child->wholeText), $shorten);
					}
					elseif ( $child instanceof DOMComment )
					{
						if ( preg_match('%\[pmxi_more:(\d+)\]%', $child->nodeValue, $mtch) )
						{
							$no = intval($mtch[ 1 ]);
							$ret .= '<div class="xml-more">[ &dArr; ' . sprintf(trans('<strong>%s</strong> %s more'), $no, _n('element', 'elements', $no, 'pmxi_plugin')) . ' &dArr; ]</div>';
						}
					}
				}
				$ret .= '</div>';
			}
			$ret .= '<div class="xml-tag closing">&lt;/<span class="xml-tag-name">' . $el->nodeName . '</span>&gt;</div>';
		}
		else
		{
			$ret .= '<div class="xml-tag opening empty">&lt;<span class="xml-tag-name">' . $el->nodeName . '</span>';
			$ret .= $this->render_xml_attributes($el);
			$ret .= '/&gt;</div>';
		}
		$ret .= '</div>';

		return $ret;
	}

}
