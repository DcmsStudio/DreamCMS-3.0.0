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
 * @package      Search
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Search_Action_Index extends Search_Helper_Base
{

	public function execute ()
	{

		$this->searchObj = new Search();
		if ( $this->getApplication()->getMode() === Application::BACKEND_MODE )
		{
			$this->doSearch(true);
		}
		else
		{
			if ( User::hasPerm('generic/cansearch') )
			{
				$this->doSearch(false);
			}
			else
			{
				$this->Page->sendAccessError(trans('Sie haben nicht die erforderlichen Rechte, um die Suche benutzen zu können!'));
			}
		}
	}

	/**
	 *
	 * @param bool $isBackend
	 */
	private function doSearch ( $isBackend = false )
	{

		/**
		 * Set Backend search ordering
		 */
		if ( $isBackend )
		{
			$this->Input->set('sort', 'desc');
			$this->Input->set('order', 'date');
		}


		$this->searchTime = microtime(true);
		$q                = trim($this->input('q'));
		$q                = str_replace(array (
		                                      '`',
		                                      '´'
		                                ), "'", $q);

		if ( $q == '' )
		{
			$this->getResult($isBackend);
		}

		$categories = $this->input('categories');
		$types      = $this->input('searchin');

		$this->searchObj->setSearchHash(null);

		if ( $this->input('hash') && strlen($this->input('hash')) === 11 )
		{
			$this->searchObj->setSearchHash($this->input('hash'));
		}

		if ( !$isBackend )
		{
			$this->searchObj->doSearch($q, $types, $categories, array (
			                                                          $this->input('perpage'),
			                                                          $this->input('sort'),
			                                                          $this->input('order')
			                                                    ));
		}
		else
		{
			$this->searchObj->doSearch($q, $types, $categories, array (
			                                                          30,
			                                                          $this->input('sort'),
			                                                          $this->input('order')
			                                                    ));
		}

		$this->resulthash = null;

		if ( $this->resulthash === null )
		{
			Session::delete('search-' . $this->searchObj->getResultHash());
			$this->resulthash = $this->searchObj->getResultHash();
		}

		if ( !$isBackend )
		{
			$this->getResult();
		}
		else
		{
			$this->getBackendResult();
		}
	}

	/**
	 *
	 */
	private function getResult ()
	{

		# $this->setLayout('search');

		$data = array ();
		if ( $this->resulthash === null && $this->input('hash') )
		{
			$this->searchTime = microtime(true);

			$this->resulthash = $this->input('hash');
			$this->searchObj->setSearchHash($this->resulthash);
		}


		$page = (int)$this->input('page') > 0 ? (int)$this->input('page') : 1;
		$hash = $this->searchObj->getResultHash();

		if ( !$this->checkHash($hash) && HTTP::requestType() !== 'post' )
		{
			header('Location: ./search');
			exit;
		}


		$hashlog = $this->db->query('SELECT q, searchtime, orderby, sort, perpage, si FROM %tp%search_log WHERE searchhash= ?', $hash)->fetch();


		if ( $hashlog[ 'si' ] )
		{
			$this->Input->set('searchin', unserialize($hashlog[ 'si' ]));
		}


		$__pp = (int)$this->input('perpage');

		$pp       = $__pp > 0 && $__pp <= 100 ? $__pp : 20;
		$per_page = $pp;

		$inper_page = 0;
		if ( $pp <= 100 && $pp > 0 )
		{
			$inper_page = $pp;
		}
		else
		{
			$pp = 20;
		}

		$order = $this->input('order');
		$sort  = $this->input('sort');

		switch ( strtolower($sort) )
		{
			case 'asc':
				$sort = 'ASC';
				break;
			case 'desc':
			default:
				$sort = 'DESC';
				break;
		}

		switch ( strtolower($order) )
		{
			case 'title':
				$order = 'title';
				break;

			case 'date':
				$order = 'date';
				break;

			case 'relevance':
				$order = 'relevance';
				break;
			default:
				$order = 'relevance';
				break;
		}

		if ( !Session::get('search-' . $hash) )
		{
			$data[ 'indexed_sites' ] = $this->model->countIndexedSites();

			$r               = $this->model->scoreRelevance($hash);
			$total_found     = $r[ 'total' ];
			$max_words_found = $r[ 'total_relevance' ];


			if ( $inper_page )
			{
				$per_page = $inper_page;
			}


			Session::save('search-' . $hash, serialize(array (
			                                                 'total'           => $total_found,
			                                                 'total_relevance' => $max_words_found,
			                                                 'indexed_sites'   => $data[ 'indexed_sites' ],
			                                                 'perpage'         => $per_page,
			                                                 'order'           => $order,
			                                                 'sort'            => strtolower($sort)
			                                           )));


			$this->Input->set('order', $order);
			$this->Input->set('sort', strtolower($sort));
			$this->Input->set('perpage', $per_page);
			$this->Input->set('q', $hashlog[ 'q' ]);
		}
		else
		{
			$serialized = Session::get('search-' . $hash);
			$r          = unserialize($serialized);

			$total_found             = $r[ 'total' ];
			$data[ 'indexed_sites' ] = $r[ 'indexed_sites' ];
			$max_words_found         = $r[ 'total_relevance' ];
			$__pp                    = $r[ 'perpage' ];

			$per_page = $r[ 'perpage' ];

			Session::save('search-' . $hash, serialize(array (
			                                                 'total'           => $r[ 'total' ],
			                                                 'total_relevance' => $r[ 'total_relevance' ],
			                                                 'indexed_sites'   => $r[ 'indexed_sites' ],
			                                                 'perpage'         => $r[ 'perpage' ],
			                                                 'order'           => $r[ 'order' ],
			                                                 'sort'            => $r[ 'sort' ]
			                                           )));


			$this->Input->set('order', $r[ 'order' ]);
			$this->Input->set('sort', $r[ 'sort' ]);
			$this->Input->set('perpage', $r[ 'perpage' ]);
			$this->Input->set('q', $hashlog[ 'q' ]);
		}


		$data[ 'searchresult' ] = array ();
		$data[ 'found_sites' ]  = $total_found;
		$data[ 'result_start' ] = ($per_page * ($page - 1)) > 0 ? ($per_page * ($page - 1)) + 1 : 1;
		$data[ 'result_end' ]   = $total_found >= $per_page ? ($page * $per_page) : $total_found;
		$data[ 'result_end' ]   = ($data[ 'result_end' ] > $total_found ? $total_found : $data[ 'result_end' ]);

		$__pp  = (int)$this->input('perpage');
		$pages = ceil($total_found / $__pp);
		//die("$total_found");

		$searchResults = $this->model->getResults($hash, $page, $__pp);

		$data[ 'searchtime' ] = sprintf("%0.4f", (microtime(true) - $this->searchTime));


		$indexer  = new Search_Indexer;
		$parsed_q = $indexer->prepareContent($hashlog[ 'q' ]);
		$parsed_q = str_replace('*', '', $parsed_q);

		unset($indexer);

		// Split keywords
		$arrWords = array ();
		preg_match_all('/"[^"]+"|[\+\-]?[^ ]+\*?/s', $parsed_q, $arrWords);

		#####$a = Application::getInstance();
		######$appcache = $a->getApps();


		$tmpApps = array ();


		#####foreach ( $appcache as $r )
		####{
		####    $tmpApps[$r['appid']] = $r['apptype'];
		####}
		#$this->load('Pagelink');
		$this->load('Site');


		foreach ( $searchResults as $rs )
		{
			$arrContext = array ();
			$arrMatches = array ();

			$length = mb_strlen($rs[ 'content' ]);
			// Get context
			foreach ( $arrWords[ 0 ] as $strWord )
			{
				if ( trim($strWord) )
				{

					$arrChunks = array ();
					preg_match_all('/(^|\w|\b.{0,' . $this->contextLength . '}\PL)(' . trim($strWord) . ')(\PL.{0,' . $this->contextLength . '}\b|$)/isu', $rs[ 'content' ], $arrChunks);
					$arrMatches[ ] = trim($strWord);

					foreach ( $arrChunks[ 0 ] as $strContext )
					{
						$arrContext[ ] = $strContext;
					}
				}
			}

			// Shorten context and highlight keywords
			if ( count($arrContext) )
			{


				$rs[ 'content' ] = trim(implode(' … ', $arrContext));
				$rs[ 'content' ] = Strings::TrimHtml($rs[ 'content' ], $this->totalLength);
				// (\PL\w*)(' . implode( '|', $arrMatches ) . ')(\w*\PL)
				$rs[ 'content' ] = preg_replace('/(' . implode('|', $arrMatches) . ')/usi', '<span class="highlight">$1</span>', $rs[ 'content' ]);


				if ( $length > $this->totalLength )
				{
					$rs[ 'content' ] .= ' …';
				}
			}
			else
			{
				$rs[ 'content' ] = trim(Strings::substrHtml($rs[ 'content' ], $this->totalLength));
				if ( $length > $this->totalLength )
				{
					$rs[ 'content' ] .= ' …';
				}
			}


			// create the link to the content
			$url = $rs[ 'location' ];


			if ( substr($url, 0, 7) === 'plugin/' )
			{
				$url = substr($url, 7);
			}


			if ( substr($url, -1) != '/' )
			{
				$url .= '/';
			}


			$url .= $rs[ 'alias' ] ? $rs[ 'alias' ] : Library::suggest($rs[ 'title' ]);
			$url .= '.' . ( !empty($rs[ 'suffix' ]) ? $rs[ 'suffix' ] : Settings::get('mod_rewrite_suffix', 'html'));


			/*
			  if ($rs['alias'] && !$rs['appid'])
			  {
			  $url = $this->generateLink(array('action' => $rs['action'], 'controller' => $rs['controller'], 'id' => $rs['id'], 'alias' => $rs['alias'], 'suffix' => $rs['suffix']));
			  }


			  if ($rs['appid'] && isset($tmpApps[$rs['appid']]))
			  {
			  # if ($rs['apptype'])
			  # {
			  $url = Library::suggest($tmpApps[$rs['appid']], 'alias', false) . '/item/' . $rs['contentid'] . '/';
			  if ($rs['alias'])
			  {
			  $url .= $rs['alias'] . ($rs['alias'] && $rs['suffix'] ? '.' . $rs['suffix'] : '');
			  }
			  # }
			  }

			 */


			$data[ 'searchresult' ][ ] = array (
				'contentsize' => ($rs[ 'content_bytes' ] > 0 ? Library::formatSize($rs[ 'content_bytes' ] + 12000) :
						'1.00 kb'),
				'title'       => $rs[ 'title' ],
				'content'     => $rs[ 'content' ],
				'url'         => $url,
				'score'       => sprintf("%01.2f", ((($rs[ 'score' ]) * 100) / $max_words_found)),
				'section_key' => $rs[ 'section_key' ],
				'action'      => $rs[ 'action' ],
				'controller'  => $rs[ 'controller' ],
				'contentid'   => $rs[ 'contentid' ],
				'contentdate' => Locales::formatFullDate($rs[ 'content_time' ]),
				'alias'       => $rs[ 'alias' ],
				'suffix'      => $rs[ 'suffix' ]
			);
		}


		if ( $pages > 1 )
		{
			$this->load('Paging');
			$url = $this->Paging->generate(array (
			                                     'action' => 'index',
			                                     'hash'   => $hash
			                               ));
			$this->Paging->setPaging($url, $page, $pages);
		}

		$data[ 'sections' ] = $this->searchObj->getSearchables();

		$this->searchObj = $searchResults = null;


		$this->Site->disableSiteCaching();
		$this->Breadcrumb->add(trans('Suchmaschine'));
		$this->Template->process('search/search_form', $data, true);
	}

	/**
	 *
	 */
	private function getBackendResult ()
	{

		$data = array ();
		if ( $this->resulthash === null && $this->input('hash') )
		{
			$this->searchTime = microtime(true);
			$this->resulthash = $this->input('hash');
			$this->searchObj->setSearchHash($this->resulthash);
		}

		$page = (int)$this->input('page') > 0 ? (int)$this->input('page') : 1;
		$hash = $this->searchObj->getResultHash();

		if ( !$this->checkHash($hash) && HTTP::requestType() !== 'post' )
		{
			Library::sendJson(true);
		}

		$order = $this->input('order');
		$sort  = $this->input('sort');

		switch ( strtolower($sort) )
		{
			case 'asc':
				$sort = 'ASC';
				break;
			case 'desc':
			default:
				$sort = 'DESC';
				break;
		}

		switch ( strtolower($order) )
		{
			case 'title':
				$order = 'title';
				break;

			case 'date':
				$order = 'date';
				break;

			case 'relevance':
				$order = 'relevance';
				break;
			default:
				$order = 'relevance';
				break;
		}

		$hashlog = $this->db->query('SELECT q, searchtime, orderby, sort, perpage, si FROM %tp%search_log WHERE searchhash= ?', $hash)->fetch();

		$data[ 'indexed_sites' ] = $this->model->countIndexedSites();

		$r               = $this->model->scoreRelevance($hash);
		$total_found     = $r[ 'total' ];
		$max_words_found = $r[ 'total_relevance' ];

		$__pp          = (int)30;
		$pages         = ceil($total_found / $__pp);
		$searchResults = $this->model->getResults($hash, $page, $__pp);


		$reg  = $this->getApplication()->getModulRegistry();
		$mods = $this->searchObj->getIndexerModules();


		$indexer      = new Search_Indexer;
		$parsed_query = $indexer->prepareContent($hashlog[ 'q' ]);
		$parsed_q     = str_replace('*', '', $parsed_query);

		unset($indexer);

		$grouped = array ();


		// Split keywords
		$arrWords = array ();
		preg_match_all('/"[^"]+"|[\+\-]?[^ ]+\*?/s', $parsed_q, $arrWords);

		foreach ( $searchResults as $rs )
		{
			$arrContext = array ();
			$arrMatches = array ();

			$length = strlen($rs[ 'content' ]);
			// Get context
			foreach ( $arrWords[ 0 ] as $strWord )
			{
				if ( trim($strWord) )
				{

					$arrChunks = array ();
					preg_match_all('/(^|\w|\b.{0,' . $this->contextLength . '}\PL)(' . trim($strWord) . ')(\PL.{0,' . $this->contextLength . '}\b|$)/isu', $rs[ 'content' ], $arrChunks);
					$arrMatches[ ] = trim($strWord);

					foreach ( $arrChunks[ 0 ] as $strContext )
					{
						$arrContext[ ] = $strContext;
					}
				}
			}

			// Shorten context and highlight keywords
			if ( count($arrContext) )
			{
				$rs[ 'content' ] = trim(implode(utf8_encode(' … '), $arrContext));
				$rs[ 'content' ] = Strings::TrimHtml($rs[ 'content' ], $this->totalLength);
				$rs[ 'content' ] = preg_replace('/(' . implode('|', $arrMatches) . ')/usi', '<span class="highlight">$1</span>', $rs[ 'content' ]);
				if ( $length > $this->totalLength )
				{
					$rs[ 'content' ] .= utf8_encode(' …');
				}
			}
			else
			{
				$rs[ 'content' ] = trim(Strings::substrHtml($rs[ 'content' ], $this->totalLength));
				if ( $length > $this->totalLength )
				{
					$rs[ 'content' ] .= utf8_encode(' …');
				}
			}

			// create the link to the content
			$url      = $rs[ 'location' ];
			$isPlugin = false;

			if ( substr($url, 0, 7) === 'plugin/' )
			{
				$url = substr($url, 7);

				$pl       = explode('/', $url);
				$isPlugin = strtolower($pl[ 0 ]);
			}

			if ( substr($url, -1) != '/' )
			{
				$url .= '/';
			}

			$url .= $rs[ 'alias' ] ? $rs[ 'alias' ] : Library::suggest($rs[ 'title' ]);
			$url .= '.' . ($rs[ 'suffix' ] ? $rs[ 'suffix' ] : Settings::get('mod_rewrite_suffix', 'html'));


			$label = (isset($reg[ $rs[ 'controller' ] ][ 'definition' ][ 'modulelabel' ]) && !isset($grouped[ $rs[ 'controller' ] ]) ?
				$reg[ $rs[ 'controller' ] ][ 'definition' ][ 'modulelabel' ] : false);

			$editurl = (isset($mods[ $rs[ 'controller' ] ][ 'editlocation' ]) ?
				$mods[ $rs[ 'controller' ] ][ 'editlocation' ] : false);

			if ( Plugin::isPlugin($isPlugin) )
			{
				$def                  = Plugin::getPluginDefinition($isPlugin);
				$label                = (isset($def[ 'modulelabel' ]) && !isset($grouped[ $isPlugin ]) ?
					$def[ 'modulelabel' ] : false);
				$editurl              = (isset($def[ 'editlocation' ]) ? $def[ 'editlocation' ] : false);
				$grouped[ $isPlugin ] = true;
			}


			$data[ 'searchresult' ][ ] = array (
				'id'          => $rs[ 'id' ],
				'label'       => $label,
				'editurl'     => $editurl,
				'contentsize' => ($rs[ 'content_bytes' ] > 0 ? Library::formatSize($rs[ 'content_bytes' ] + 12000) :
						'1.00 kb'),
				'title'       => $rs[ 'title' ],
				'content'     => $rs[ 'content' ],
				'url'         => $url,
				'score'       => sprintf("%01.2f", ((($rs[ 'score' ]) * 100) / $max_words_found)),
				'section_key' => $rs[ 'section_key' ],
				'action'      => $rs[ 'action' ],
				'controller'  => $rs[ 'controller' ],
				'contentid'   => $rs[ 'contentid' ],
				'contentdate' => Locales::formatFullDate($rs[ 'content_time' ]),
				'alias'       => $rs[ 'alias' ],
				'suffix'      => $rs[ 'suffix' ]
			);

			if ( !isset($grouped[ $rs[ 'controller' ] ]) )
			{
				$grouped[ $rs[ 'controller' ] ] = true;
			}
		}


		$data[ 'success' ] = true;
		echo Library::json($data);
		exit;
	}

}
