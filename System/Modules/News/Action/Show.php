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
 * @package      News
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Show.php
 */
class News_Action_Show extends Controller_Abstract
{

	public function execute ()
	{


		if ( !$this->isFrontend() )
		{
			return;
		}



        /*

		$this->model->setTable('news');
		$modelTables = $this->model->getConfig('tables');

		if ( !isset($modelTables[ 'news' ]) )
		{
			throw new BaseException('The News modul has no Model configuration! ' . print_r($modelTables, true));
		}
        */


		$this->Document
            ->validateModel('news', true)
            ->loadConfig() //->setTableConfiguration('news', $modelTables[ 'news' ]);
		    ->getMetaInstance()
            ->setMetadataType(true);


		$id = (int)$this->input('id'); //($this->Document->getDocumentID() ? $this->Document->getDocumentID() : (int)HTTP::input( 'id'  ));

		if ( $id )
		{
			$rs = $this->model->findItemByID($id);
		}
		elseif ( $this->getDocumentName(false) )
		{
			$rs = $this->model->findItemByAlias($this->getDocumentName(false));
		}


		/**
		 * News Item was not found then send Error Page
		 */
		if ( !$rs[ 'id' ] )
		{
			$this->Page->send404(trans('Die von Ihnen aufgerufene Seite existiert nicht.'));
		}





		$id = $rs[ 'id' ];

		$access = explode(',', $rs[ 'usergroups' ]);
		if ( $rs[ 'usergroups' ] != '' && !in_array(User::getGroupId(), $access) && !in_array(0, $access) )
		{
			$this->Page->sendAccessError(trans('Sie haben keine rechte diese Seite zu sehn. Bitte loggen Sie sich ein oder Registrieren Sie sich.'));
		}

		$this->Input->set('action', 'show');
		$this->Input->set('id', $rs[ 'id' ]);

		$this->load('ContentLock');
		if ( $this->ContentLock->isLock($rs[ 'id' ], 'news', 'show') && (!User::isAdmin() || !User::hasPerm('generic/canviewofflinedocuments')) )
		{
			$this->Document->offline();
		}

		// Set the document id
		$this->Document->setDocumentID($rs[ 'id' ]);


		if ( empty($rs[ 'text' ]) )
		{
			$this->Page->send404(trans('Die von Ihnen aufgerufene News existiert nicht.'));
		}


		if ( isset($rs[ 'catpublished' ]) && !$rs[ 'catpublished' ] )
		{
			$this->Page->send404(trans('Die von Ihnen aufgerufene News existiert nicht.'));
		}


		/**
		 * Add Rss Header
		 */
		$this->Document->addRssHeader('atom', $this->getModulLabel(), 'news/index/' . $this->getDocumentName('news'));
		$this->Document->addRssHeader('rss', $this->getModulLabel(), 'news/index/' . $this->getDocumentName('news'));


		/**
		 * Check Usergroup Permissions
		 *
		 */
		$gid    = User::getGroupId();
		$groups = explode(",", $rs[ 'cat_access' ]);
		if ( !in_array($gid, $groups) && !in_array(0, $groups) )
		{
			$this->Page->sendAccessError(trans('Sie haben keine Rechte sich diese News anzusehen.'));
		}

		/**
		 * Rate this News Item
		 */
		if ( $this->input('rate') && IS_AJAX )
		{
			if ( !$rs[ 'id' ] )
			{
				if ( IS_AJAX )
				{
					Library::sendJson(false, 'News not found.');
				}

				$rs[ 'rate_error' ] = trans('News not found.');
			}



            if ( $rs[ 'draft' ] || $rs[ 'locked' ] )
            {
                $rs[ 'rate_error' ] = trans('Sorry aber Sie können diesen Inhalt nicht bewerten, da dieser gerade überarbeitet wird.');
            }


			if ( !$rs[ 'rate_error' ] )
			{
				if ( Session::get('news-rate-' . $rs[ 'id' ]) )
				{
					if ( IS_AJAX )
					{
						Library::sendJson(false, trans('Sorry aber Sie können diesen Inhalt nicht mehr bewerten, da Sie Ihre Wertung schon abgegeben haben.'));
					}


					$rs[ 'rate_error' ] = trans('Sorry aber Sie können diesen Inhalt nicht mehr bewerten, da Sie Ihre Wertung schon abgegeben haben.');
				}


				if ( !$rs[ 'rate_error' ] )
				{
					$rate = (int)$this->input('rate');
					if ( !$rate )
					{
						if ( IS_AJAX )
						{
							Library::sendJson(false, 'Your Rating is Empty.');
						}

						$rs[ 'rate_error' ] = 'Your Rating is Empty.';
					}
					else
					{
						$newrating = ($rs[ 'rating' ] * $rs[ 'votes' ] + $rate) / ($rs[ 'votes' ] + 1);
						$this->db->query('UPDATE %tp%news SET rating = ?, votes = votes + 1 WHERE id = ?', $newrating, $rs[ 'id' ]);

						Session::save('news-rate-' . $rs[ 'id' ], 1);
						$newrating = sprintf("%01.2f", $newrating);

						if ( IS_AJAX )
						{
							echo Library::json(array (
							                         'success' => true,
							                         'msg'     => trans('Danke für Ihre Bewertung'),
							                         'rating'  => $newrating,
							                         'votes'   => $rs[ 'votes' ] + 1
							                   ));
							exit();
						}

						$rs[ 'rate_ok' ] = true;
					}
				}
			}
            else {
                Library::sendJson(false, $rs[ 'rate_error' ] );
            }
		}

		if ( $id > 0 )
		{
			$rs[ 'commentscounter' ] = $rs[ 'comments' ];
		}

		#$rs[ 'text' ] = preg_replace('#src=(["\']).*(pages/.+)\1#i', 'src=$1$2$1', $rs[ 'text' ]);

		if ( Settings::get('news.parsefootnotes', false) )
		{
			Content::enableFootnotes();
		}
		else
		{
			Content::disableFootnotes();
		}

		if ( Settings::get('news.parsevideos', false) )
		{
			Content::enableTubeVideos();
		}
		else
		{
			Content::disableTubeVideos();
		}

		Content::enablePagebreaks();

		// prepare the content
		$rs[ 'text' ]      = Strings::tinyMCECoreTags($rs[ 'text' ]);
		$rs[ 'text' ]      = Content::parseContent($rs[ 'text' ], "news/" . $rs[ 'id' ] . "/" . $rs[ 'cat_id' ]);
		$rs[ 'siteindex' ] = Content::getSiteIndexes();
		$rs[ 'paging' ]    = Content::getContentPageing();


		// news author
		if ( $rs[ 'created_by' ] )
		{
			$rs[ 'author' ] = User::getUserById($rs[ 'created_by' ]);
		}

		$rs[ 'authorname' ] = $rs[ 'newsauthor' ];

		if ( $rs[ 'author' ] )
		{
			BBCode::allowBBCodes('biobbcodes');

			$rs[ 'author' ][ 'userphoto' ] = User::getUserPhoto($rs[ 'author' ]);
			$rs[ 'author' ][ 'bio' ]       = BBCode::toXHTML($rs[ 'author' ][ 'usertext' ]);
		}
		else {

		}


		$rating                 = sprintf("%01.2f", $rs[ 'rating' ]);
		$image_name             = Library::makeRatingImg($rating);
		$rs[ 'ratingimg_name' ] = $image_name;
		$rs[ 'ratingsum' ]      = $rating;
		$rs[ 'rating' ]         = round($rating);


		$rs[ 'show_category_link' ] = 1;
		$rs[ 'show_author' ]        = 1;
		$rs[ 'show_modifydate' ]    = 1;
		$rs[ 'show_createdate' ]    = 1;
		$rs[ 'show_voteform' ]      = 1;

		/**
		 * Init Tags
		 */
		if ( $rs[ 'tags' ] )
		{
			$this->load('Tags');
			$this->Tags->setContentTable('news_trans');
			$rs[ 'tags' ] = $this->Tags->getContentTags($rs[ 'tags' ]);
		}
		else {
			$rs[ 'tags' ] = array();
		}

        $can_comment = $rs[ 'can_comment' ];





		// allow Comments
		if ( $can_comment && Settings::get('news.usecomments', true) )
		{
			// User can not comment by perm?
			if ( !User::hasPerm('news/cancommentnews') )
			{
				$can_comment = false;
			}
		}
		else
		{
			$can_comment = false;
		}

        User::setPerm('news/cancommentnews', $can_comment);

		$this->Document->setCommenting('news/cancommentnews', $can_comment); // set user perm







		// create cat link
		$rs[ 'caturl' ] = $this->generateUrl(array (
		                                           'action' => 'index',
		                                           'catid'  => isset($rs[ 'cat_id' ]) ? $rs[ 'cat_id' ] : 0,
		                                           'alias'  => $rs[ 'catalias' ],
		                                           'suffix' => $rs[ 'catsuffix' ]
		                                     ), 'news/');


		/**
		 * Content Gallery
		 */
		if ( $rs[ 'inlinegallery' ] )
		{
			$rs[ 'contentgallery' ] = $this->model->getContentImages($id, $rs[ 'inlinegallery' ]);

			$this->Template->addScript('public/html/js/jquery/jcarousel/jcarousel.responsive.css', true);
			$this->Template->addScript('public/html/js/jquery/jcarousel/jquery.jcarousel.min.js', false);
		}


		$this->Document->setMetaAuthor($rs[ 'newsauthor' ]);
		$this->Document->setClickAnalyse($rs[ 'clickanalyse' ]);

		// Set Page Caching
		if ( $rs[ 'cacheable' ] || $this->SideCache->enabled )
		{
			$this->Document->enableSiteCaching($rs[ 'cachetime' ]);
			$this->Document->setSiteCachingGroups($rs[ 'cachegroups' ]);
		}
		else
		{
			$this->Document->disableSiteCaching();
		}



		Hook::run( 'onBeforeNewsShow', $rs[ 'text' ] ); // {CONTEXT: News, DESC: Run code before the Application is inited}


		$newsCatCache = Cache::get('newsCategories-' . CONTENT_TRANS, 'data/news');
		if ( !$newsCatCache )
		{
			$result = $this->model->getCategories();
			foreach ( $result as &$r )
			{
				$r[ 'cat_title' ]   = Strings::fixLatin($r[ 'title' ]);
				$r[ 'description' ] = Strings::fixLatin($r[ 'description' ]);

				/**
				 * has categorie a extra teaser image (full width Image)
				 */
				if ( $r[ 'teaserimage' ] != '' )
				{
					$r[ 'teaserimage' ] = unserialize($r[ 'teaserimage' ]);

					if ( !$r[ 'teaserimage' ][ 'src' ] )
					{
						$r[ 'teaserimage' ] = null;
					}
					else
					{
						$r[ 'teaserimage' ][ 'src' ] = PAGE_URL_PATH . preg_replace('/^\//', '', $r[ 'teaserimage' ][ 'src' ]);
					}
				}

				$r[ 'caturl' ] = $this->generateUrl(array (
				                                          'id'     => isset($r[ 'cat_id' ]) ? $r[ 'cat_id' ] : 0,
				                                          'alias'  => $r[ 'alias' ],
				                                          'suffix' => $r[ 'suffix' ]
				                                    ), '/news/category/');
			}

			$rs[ 'categories' ] = $result;
			Cache::write('newsCategories', $result, 'data/news');
			unset($result);
		}
		else
		{
			$rs[ 'categories' ] = $newsCatCache;
			unset($newsCatCache);
		}

		Cache::freeMem('newsCategories-' . CONTENT_TRANS, 'data/news');

		/**
		 * Build Pagebreadcrumbs
		 */
		if ( !$this->Breadcrumb->getBreadcrumbs() )
		{
			$this->Breadcrumb->add($this->getModulLabel(), '/news');
		}

		$breadcrumbs = $this->Breadcrumb->getNewsBreadcrumb($rs[ 'cat_id' ]);
		foreach ( $breadcrumbs as $rx )
		{
			$link = $this->generateUrl(array (
			                                 'id'     => isset($rx[ 'cat_id' ]) ? $rx[ 'cat_id' ] : 0,
			                                 'alias'  => $rx[ 'alias' ],
			                                 'suffix' => $rx[ 'suffix' ]
			                           ), '/news/category/');
			$this->Breadcrumb->add($rx[ 'title' ], $link);
		}

		$rs[ 'pagetitle' ] = trim($rs[ 'pagetitle' ]);
		$this->Breadcrumb->add($rs[ 'title' ], '');

        //
        if ( $rs[ 'draft' ] || $rs[ 'locked' ] && (!User::isAdmin() || !User::hasPerm('generic/canviewofflinedocuments')) )
        {
            $this->Document->offline();
        }

		// set the last modify date for http header
		$lastModified = ($rs[ 'modifed' ] > $rs[ 'created' ] ? $rs[ 'modifed' ] : $rs[ 'created' ]);
		$this->Document->setLastModified($lastModified);

		// Set Content of this page
		$this->Document->setData($rs);

		//
		$this->setSocialNetworkData($rs, (!empty($rs[ 'pagetitle' ]) ? $rs[ 'pagetitle' ] :
			$rs[ 'title' ]), $rs[ 'text' ], true);

		// Update the hits counter
		$this->model->updateHits($rs[ 'id' ]);


		// Set the Page Layout
		#$this->Document->setLayout('news');^
		//Session::save( 'comment_' . $postType.'_perm', 'news/' );

		$this->Template->process('news/show', array (
		                                            'news' => $rs
		                                      ), true);

		exit();
	}

}

?>