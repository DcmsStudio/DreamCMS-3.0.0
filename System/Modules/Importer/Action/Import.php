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
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Import.php
 */
class Importer_Action_Import extends Controller_Abstract
{

	protected $largeFileSize = 1048576;

	/**
	 *
	 * @var array
	 */
	private $_helpers = array (
		'xml',
		'csv'
	);

	/**
	 *
	 * @var Object
	 */
	private $helper;

	/**
	 *
	 * @var string
	 */
	private $importContentType;

	/**
	 *
	 * @var string
	 */
	private $importType;

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$step = (string)$this->input('step');


		switch ( $step )
		{
			case '1':
				$this->importBase();
				break;

			case '2':
				$this->importOptions();
				break;

			case '3':
				$this->importExecute();
				break;

			case 'preview':
				$this->importPreview();
				break;


			default:
				$this->start();
				break;
		}
	}

	private function start ()
	{

		if ( $this->input('do') == 'import' )
		{

			$path = $this->input('filepath');


			if ( $this->input('mode') === 'file' )
			{

				$test = str_replace(array (
				                          '../',
				                          './'
				                    ), '', $path);


				if ( $test !== $path )
				{
					Library::sendJson(false, 'Invalid File');
				}
				else
				{
					if ( is_file(PUBLIC_PATH . $test) )
					{

						$status[ "filepath" ] = PUBLIC_PATH . $test;
						Session::save('Importer', $status);

						Session::write();


						Library::sendJson(true);
					}
					else
					{
						// error
						Library::sendJson(false, 'Invalid File');
					}
				}
			}
			elseif ( $this->input('mode') === 'url' )
			{

			}
			else
			{

			}
		}
		else
		{
			Session::save('Importer', null);
			Session::write();

			$this->Template->addScript('Modules/' . CONTROLLER . '/asset/js/importer.js');
			$this->Template->process('importer/import', array (
			                                                  'addFileSelector' => true
			                                            ), true);
		}
	}

	private function importBase ()
	{

		$import = Session::get('Importer', false);

		if ( !$import )
		{
			throw new BaseException(trans('Der Import kann leider nicht fortgesetzt werden, da ein Fehler aufgetreten ist.'));
		}


		$element = '';

		$isLargeFile = (filesize($import[ 'filepath' ]) > $this->largeFileSize ? true : false);


		if ( !isset($import[ 'index' ]) )
		{
			$import[ 'index' ] = 1;
		}

		if ( $this->_get('index') == 1 )
		{
			$import[ 'index' ] = ($import[ 'index' ] > 1 ? $import[ 'index' ] - 1 : 1);
		}
		elseif ( $this->_get('index') == 2 )
		{
			$import[ 'index' ] += 1;
		}

		$tagno = $import[ 'index' ];

		$base = new Importer_Helper_Base;

		$rootElementName = $base->getRootElement($import);
		if ( $rootElementName === false )
		{
			// error
			throw new BaseException(trans('Invalid XML Root Element'));
		}


		$recordElementName = $base->getRecordElement($import);
		if ( $recordElementName === false )
		{
			// error
			throw new BaseException(trans('Invalid XML Record Element'));
		}


		if ( !isset($import[ 'xpath' ]) && is_string($rootElementName) && is_string($recordElementName) )
		{
			$import[ 'xpath' ] = /* '/' . $rootElementName . */
				'//' . $recordElementName;
		}

		if ( !isset($import[ 'xpath' ]) && is_string($rootElementName) && !is_string($recordElementName) )
		{
			$import[ 'xpath' ] = '//' . $rootElementName;
		}

		if ( !isset($import[ 'xpath' ]) )
		{
			$import[ 'xpath' ] = '/';
		}


		$el = $base->getDataRecord($import, $tagno, $import[ 'filepath' ], $import[ 'xpath' ]);

		if ( $el !== null )
		{

			$nodes[ ] = array (
				'node' => $base->render_xml_element($el, false)
			);
		}

		$base->unsetDomXpath()->unsetDomDocument();
		/*


		  $dom               = new DOMDocument; //( '1.0', 'UTF-8' );
		  $dom->formatOutput = false;
		  $old               = libxml_use_internal_errors( true );
		  $dom->load( $import[ 'filepath' ] );
		  // $dom->loadXML( preg_replace( '%xmlns\s*=\s*([\'"]).*\1%sU', '', $xml ) ); // FIX: libxml xpath doesn't handle default namespace properly, so remove it upon XML load
		  libxml_use_internal_errors( $old );


		  // get record counter

		  $xpath      = new DOMXPath( $dom );
		  $numRecords = $base->getNumRecords( $import, $xpath );
		  if ( $numRecords === false )
		  {
		  // error
		  throw new BaseException( trans( 'Invalid XML Records' ) );
		  }
		  unset( $xpath );


		  // get the current record

		  Debug::store( 'StartXML Read', 'StartXML Read' );
		  $_xpath   = $import[ 'xpath' ];
		  $_xpath   = (substr( $_xpath, 0, 2 ) != '//' ? $_xpath : $_xpath);
		  $xpath    = new DOMXPath( $dom );
		  $nodes    = array();
		  if ( ($elements = $xpath->query( $_xpath )) && $elements->length )
		  {
		  Debug::store( 'StopXML Read', 'StopXML Read' );

		  if ( $tagno <= 1 )
		  {
		  $tagno = 1;
		  }

		  if ( $tagno > $numRecords )
		  {
		  $tagno             = $numRecords;
		  $import[ 'index' ] = $tagno;
		  }

		  $el = $elements->item( ($tagno - 1 ) );

		  if ( $el !== null )
		  {
		  $base    = new Importer_Helper_Base;
		  $nodes[] = array(
		  'node' => $base->render_xml_element( $el, true ) );
		  }
		  }

		  unset( $xpath, $dom );
		 */

		Session::save('Importer', $import);



		if ( $this->_get('index') )
		{
			echo Library::json(array (
			                         'success'      => true,
			                         'totalrecords' => $numRecords,
			                         'nodes'        => $nodes,
			                         'xpath'        => $import[ 'xpath' ]
			                   ));

			exit;
		}

		$this->Template->process('importer/import', array (
		                                                  'totalrecords' => $import[ 'count' ],
		                                                  'xpath'        => $import[ 'xpath' ],
		                                                  'xmlnodes'     => $nodes
		                                            ), true);
	}

	private function importPreview ()
	{

		$import = Session::get('Importer', false);

		if ( !$import )
		{
			Error::raise(trans('Der Import kann leider nicht fortgesetzt werden, da ein Fehler aufgetreten ist.'));
		}


		$tagno           = $import[ 'index' ];
		$base            = new Importer_Helper_Base;
		$rootElementName = $base->getRootElement($import);
		if ( $rootElementName === false )
		{
			// error
			throw new BaseException(trans('Invalid XML Root Element'));
		}


		$recordElementName = $base->getRecordElement($import);
		if ( $recordElementName === false )
		{
			// error
			throw new BaseException(trans('Invalid XML Record Element'));
		}


		if ( !isset($import[ 'xpath' ]) && is_string($rootElementName) && is_string($recordElementName) )
		{
			$import[ 'xpath' ] = /* '/' . $rootElementName . */
				'//' . $recordElementName;
		}

		if ( !isset($import[ 'xpath' ]) && is_string($rootElementName) && !is_string($recordElementName) )
		{
			$import[ 'xpath' ] = '//' . $rootElementName;
		}

		if ( !isset($import[ 'xpath' ]) )
		{
			$import[ 'xpath' ] = '/';
		}


		/**
		 * get DOMElement
		 */
		$el = $base->getDataRecord($import, $tagno, $import[ 'filepath' ], $import[ 'xpath' ]);

		if ( $el !== null )
		{
			//  print_r( $el );
			//  exit;
			$doc                     = new DOMDocument('1.0', 'UTF-8');
			$doc->preserveWhiteSpace = false;

			$newtag = $doc->createElement(($rootElementName ? $rootElementName : 'root')); //neuen Knoten erstllen
			// $cloned = $element->cloneNode( TRUE );
			$doc->appendChild($doc->importNode($el, true));


			$xml = $doc->saveXML();


			// $newtag = $doc->createElement( ($rootElementName ? $rootElementName : 'root' ) ); //neuen Knoten erstllen
			// $newtag->appendChild( $el );
			//$dom->importNode($el);
			// $xml = $doc->saveXML();
			if ( $xml === false )
			{
				throw new BaseException(trans('Invalid XML String'));
			}

			//die( $xml );


			$data = $this->input();
			if ( trim($data[ 'title' ]) )
			{
				$res             = Importer_Helper_XmlImportParser::factory($xml, $import[ 'xpath' ], $data[ 'title' ]);
				$out             = $res->parse();
				$data[ 'title' ] = $out[ 0 ];
			}

			if ( trim($data[ 'teaser' ]) )
			{
				$res              = Importer_Helper_XmlImportParser::factory($xml, $import[ 'xpath' ], $data[ 'teaser' ]);
				$out              = $res->parse();
				$data[ 'teaser' ] = $out[ 0 ];
			}


			if ( trim($data[ 'content' ]) )
			{
				$res               = Importer_Helper_XmlImportParser::factory($xml, $import[ 'xpath' ], $data[ 'content' ]);
				$out               = $res->parse();
				$data[ 'content' ] = $out[ 0 ];
			}
		}
		else
		{
			$data = array ();
		}


		$this->Template->process('importer/import', array (
		                                                  'preview' => $data
		                                            ), true);
	}

	private function importOptions ()
	{

		$import = Session::get('Importer', false);

		if ( !$import )
		{
			Error::raise(trans('Der Import kann leider nicht fortgesetzt werden, da ein Fehler aufgetreten ist.'));
		}
	}

	private function importExecute ()
	{

		$import = Session::get('Importer', false);

		if ( !$import )
		{
			Error::raise(trans('Der Import kann leider nicht fortgesetzt werden, da ein Fehler aufgetreten ist.'));
		}
	}

}
