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
 * @package      DreamCMS
 * @version      3.0.0 Beta
 * @category     Plugin s
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Thread.php
 */
class Addon_Forum_Action_Thread extends Addon_Forum_Helper_Base
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			$this->getFrontend();
		}
		else
		{

		}
	}

	private function getFrontend ()
	{

		$threadid = ( HTTP::input('threadid') ? intval(HTTP::input('threadid')) : 1 );
		$page     = ( intval(HTTP::input('page')) > 0 ? intval(HTTP::input('page')) : 1 );
		$do       = ( HTTP::input('do') ? HTTP::input('do') : null );
		$postid   = intval($this->input('postid'));

		if ( !$threadid )
		{
			$this->Page->send404(trans('Dieses Thema wurde leider nicht gefunden.'));
		}


		#print_r(User::getUserData());exit;

		Session::save('threadpage', $page);

		$this->initCache();
		$thread = $this->model->getThreadById($threadid);


		$data                 = array ();
		$data[ 'moderators' ] = ( isset($this->forum_cache[ $thread[ 'forumid' ] ][ 'moderators' ]) && is_array($this->forum_cache[ $thread[ 'forumid' ] ][ 'moderators' ]) ? $this->forum_cache[ $thread[ 'forumid' ] ][ 'moderators' ] : $this->model->getForumModerators($thread[ 'forumid' ]) );
		$data[ 'ismod' ]      = false;
		$userid               = User::getUserId();

		$mod = false;
		foreach ( $data[ 'moderators' ] as $rs )
		{
			if ( $userid == $rs[ 'userid' ] )
			{
				$rs[ 'perm' ] = $rs[ 'permissions' ] ? unserialize($rs[ 'permissions' ]) : array ();
				$mod          = $rs;
				break;
			}
		}

		if ( is_array($mod) )
		{
			$data[ 'ismod' ] = true;
			$data[ 'mod' ]   = $mod[ 'perm' ];

			unset( $mod );
		}


		$this->currentForumID = $thread[ 'forumid' ];

		$parents = $this->getParents($thread[ 'forumid' ]);

		if ( !$data[ 'ismod' ] && !$thread[ 'published' ] )
		{
			$thread = array ();
		}

		$this->buildBreadCrumb($parents, $thread);


		if ( !$thread[ 'threadid' ] )
		{
			$this->Page->send404(trans('Dieses Thema wurde leider nicht gefunden.'));
		}


		$data[ 'thread' ] = $thread;
		$data[ 'forum' ]  = ( isset( $this->forum_by_id[ $thread[ 'forumid' ] ] ) ? $this->forum_by_id[ $thread[ 'forumid' ] ] : false );

		if ( !$data[ 'forum' ] )
		{
			$this->Page->send404(trans('Hoppla dieses Forum existiert leider nicht!'));
		}

		if ( $data[ 'forum' ][ 'access' ] != '' && !in_array(User::getGroupId(), explode(',', $data[ 'forum' ][ 'access' ])) && !in_array(0, explode(',', $data[ 'forum' ][ 'access' ])) )
		{
			$this->Page->sendAccessError(trans('Sie besitzen nicht die nötigen Rechte um sich das Thema anzusehen!'));
		}
		/*

		  $this->Breadcrumb->add( trans( 'Forum' ), '/plugin/forum' );
		  $parents = array_reverse( $parents );

		  foreach ( $this->forum_cache as $idx => $row )
		  {
		  foreach ( $row as $idx => $forum )
		  {
		  foreach ( $parents as $parent )
		  {
		  if ( $forum[ 'forumid' ] == $parent )
		  {
		  $this->Breadcrumb->add( $forum[ 'title' ], '/plugin/forum/' . $forum[ 'forumid' ] . '/' . Url::makeRw( $forum[ 'alias' ], $forum[ 'suffix' ], $forum[ 'title' ] ) );
		  }
		  }

		  if ( $thread[ 'forumid' ] && $thread[ 'forumid' ] == $forum[ 'forumid' ] )
		  {

		  $this->Breadcrumb->add( $forum[ 'title' ], '/plugin/forum/' . $forum[ 'forumid' ] . '/' . Url::makeRw( $forum[ 'alias' ], $forum[ 'suffix' ], $forum[ 'title' ] ) );
		  }
		  }
		  }

		  $this->Breadcrumb->add( $thread[ 'title' ], '' );


		 */


		if ( $do === 'like' )
		{
			$skip = $this->model->likePost($this->Input->input('postid'));
			echo Library::json(array (
			                         'success' => true,
			                         'update'  => $skip
			                   ));
			exit;
		}

		if ( $do === 'dislike' )
		{
			$skip = $this->model->dislikePost($this->Input->input('postid'));
			echo Library::json(array (
			                         'success' => true,
			                         'update'  => $skip
			                   ));
			exit;
		}

		$limit = Settings::get('forum.postsperpage', 20);

		if ( $postid > 0 )
		{
			// We only have a post ID as parameter:
			// Find the right thread and page to display

			$setPage = $this->model->getThreadPostsAtPostID($data[ 'ismod' ], $data[ 'forum' ], $threadid, $postid);
			$this->Input->set('page', $setPage);
			$page = $setPage;
		}

		$getpostid = intval($this->Input->input('getpost'));
		if ( $getpostid )
		{
			$setPage = $this->model->getThreadPostsAtPostID($data[ 'ismod' ], $data[ 'forum' ], $threadid, $getpostid);
			$this->Input->set('page', $setPage);
			$page = $setPage;
		}


		$threadPosts = $this->model->getThreadPosts($data[ 'ismod' ], $data[ 'forum' ], $threadid, $page);

		$this->db->free();


		if ( !count($threadPosts[ 'result' ]) )
		{
			$this->Page->send404(trans('Diese Seite des Themas wurde leider nicht gefunden.'));
		}


		$_news_found = $threadPosts[ 'total' ];
		$seiten      = ceil($threadPosts[ 'total' ] / $limit);
		$a           = $limit * ( $page - 1 );

		if ( $seiten )
		{
			$data[ 'thread' ][ 'pages' ] = Library::paging($page, $seiten, "forum/thread/" . $threadid);
		}


		$avatarpath     = PAGE_PATH . 'avatars';
		$useravatarpath = PAGE_PATH . 'upload/userfiles';

		$postids = array ();
		foreach ( $threadPosts[ 'result' ] as $idx => $r )
		{
			$postids[ ] = $r[ 'postid' ];
		}

		$postorder = Settings::get('forum.postorder', 'desc');


		// load attachments
		$postattach = array ();
		$_attachmentHashes = array();
		if ( count($postids) )
		{
			$res = $this->model->getPostAttachments($postids);

			if ( !is_dir(PAGE_CACHE_PATH . 'thumbnails/forum') )
			{
				Library::makeDirectory(PAGE_CACHE_PATH . 'thumbnails/forum');
			}

			foreach ( $res as $rs )
			{
				$fname            = explode('/', str_replace('\\', '/', $rs[ 'path' ]));
				$fname            = $fname[ count($fname) - 1 ];
				$rs[ 'filename' ] = $fname;
				$rs[ 'fileext' ]  = Library::getExtension($rs[ 'filename' ]);
				$rs[ 'filesize' ] = Library::humanSize($rs[ 'filesize' ]);


				$c1 = md5($rs[ 'filename' ]);
				$c2 = md5($rs[ 'fileext' ]);
				$c3 = md5($rs[ 'filesize' ]);

				$v3uuid = Library::UUIDv3(substr($c1, 0, 8) . '-' . substr($c2, 0, 4) . '-' . substr(md5($c1 . $c2 . $c3), 0, 4) . '-' . substr($c3, 0, 4) . '-' . substr(md5($c1), 0, 12), 'post-' . $rs[ 'postid' ] . '-attach-' . $rs[ 'attachmentid' ]);


				$rs[ 'uiqid' ] = $v3uuid;
				$_attachmentHashes[$v3uuid] = $rs[ 'attachmentid' ];

				$isThumb = false;

			#	if ( !Session::get($v3uuid) )
			#	{
			#		Session::save($v3uuid, $rs[ 'attachmentid' ]);
			#	}


				if ( strpos($rs[ 'mime' ], 'image/') !== false && Settings::get('forum.showattachimages') )
				{
					if ( is_file(PAGE_PATH . $rs[ 'path' ]) )
					{
						$img = ImageTools::create(PAGE_CACHE_PATH . 'thumbnails/forum');
						$chain = array (
							0 => array (
								0 => 'resize',
								1 => array (
									'width'       => intval(Settings::get('forum.attachthumbwidth', 100)),
									'height'      => intval(Settings::get('forum.attachthumbheight', 100)),
									'keep_aspect' => true,
									'shrink_only' => false
								)
							)
						);

						$_data = $img->process(
							array(
							     'source' => Library::formatPath(PAGE_PATH . $rs[ 'path' ]),
                                        'output' => 'png',
                                        'chain'  => $chain
                                )
                        );

						if ( $_data['path'] )
						{
							$isThumb = true;

							$rs['thumbnail'] = str_replace(PUBLIC_PATH, '', $_data['path']);
							$rs['width'] = $_data['width'];
							$rs['height'] = $_data['height'];

							$postattach[ $rs[ 'postid' ] ]['thumbs'][] = $rs;
						}
					}
				}

				if (!$isThumb)
				{
					$postattach[ $rs[ 'postid' ] ]['files'][ ] = $rs;
				}
			}



			unset( $res );

		#	Session::write();
		}

		Session::save('forumattachments', $_attachmentHashes);


		#  print_r($_SESSION);
		#  die(Session::getId());

		$total = $threadPosts[ 'total' ];
		$num   = 1;

		foreach ( $threadPosts[ 'result' ] as $idx => &$r )
		{

			if ( $thread[ 'firstpostid' ] != $r[ 'postid' ] )
			{
				if ( !trim($r[ 'title' ]) )
				{
					$r[ 'title' ] = 'RE: ' . $thread[ 'title' ];
				}
				else
				{
					$r[ 'title' ] = 'RE: ' . str_replace('RE: ', '', $r[ 'title' ]);
				}
			}

			$r[ 'attachmentthumbs' ] = ( isset( $postattach[ $r[ 'postid' ] ]['thumbs'] ) ? $postattach[ $r[ 'postid' ] ]['thumbs'] : array () );
			$r[ 'attachmentsfiles' ] = ( isset( $postattach[ $r[ 'postid' ] ]['files'] ) ? $postattach[ $r[ 'postid' ] ]['files'] : array () );

			if ( $r[ 'userid' ] )
			{
				$r[ 'userphoto' ] = User::getUserPhoto($r);
				$r[ 'gender' ]    = User::getGender($r[ 'gender' ]);
				$r[ 'rankimage' ] = User::getRankImage($r);
			}
			else
			{
				$r[ 'userphoto' ] = 'html/img/nophoto.gif';
			}

			if ( $data[ 'forum' ][ 'allowicons' ] && $r[ 'iconpath' ] )
			{
				$r[ 'iconpath' ] = HTML_URL . 'img/icons/' . $r[ 'iconpath' ];
				if ( !is_file($r[ 'iconpath' ]) )
				{
					$r[ 'iconpath' ] = HTML_URL . 'img/icons/default.gif';
				}
			}
			else
			{
				$r[ 'iconpath' ] = HTML_URL . 'img/icons/default.gif';
			}

			$r[ 'userphoto' ] = $r[ 'userphoto' ];

			if ( !$data[ 'forum' ][ 'allowbbcode' ] )
			{

				$r[ 'content' ] = BBCode::removeBBCode($r[ 'content' ]);
			}
			else
			{
				if ( $r[ 'parsebbcode' ] )
				{

					BBCode::parseUrls($r[ 'parseurls' ]);
					BBCode::allowSmilies($r[ 'parsesmilies' ]);

					$r[ 'content' ] = BBCode::toXHTML($r[ 'content' ]);
				}
			}

			if ( User::get('allowsignatures') || Settings::get('allowsignatures') )
			{
				if ( $r[ 'showsignature' ] )
				{
					$r[ 'signature' ] = BBCode::toXHTML($r[ 'signature' ]);
				}
			}

			if ( strtolower($postorder) == 'desc' )
			{
				$r[ 'postnum' ] = $total - ( $limit * ( $page - 1 ) );
				$total--;
			}
			else
			{
				$r[ 'postnum' ] = ( $limit * ( $page - 1 ) ) + $num;
				$num++;
			}
		}

		#print_r($threadPosts[ 'result' ]);exit;

		$data[ 'posts' ]     = $threadPosts[ 'result' ];
		// $data[ 'pages' ]     = $pages;
		$data[ 'forumjump' ] = $this->catcache;

		unset( $postids, $postattach,$threadPosts );


		if ( !Session::get('thread-view-' . $threadid) )
		{
			$this->model->updateThreadHits($threadid);
		}

		Session::save('thread-view-' . $threadid, '' . time());
		#Session::write();


		//$this->PageCache->setCacheID( $threadid );
		//$this->freeMem();

	#	$this->Template->addScript(HTML_URL . 'js/jquery/fancybox/style.css', true);
	#	$this->Template->addScript(HTML_URL . 'js/jquery/fancybox/jquery.fancybox-1.3.1.js');

	#	$this->Template->addScript(HTML_URL . 'js/jquery/slimbox-2/css/slimbox2.css', true);
	#	$this->Template->addScript(HTML_URL . 'js/jquery/slimbox-2/src/slimbox2.js');
	#	$this->Template->addScript(HTML_URL . 'js/jquery/slimbox-2/src/autoload.js');
		$this->Template->mergeJavascripts();

		$this->Template->process('board/thread', $data, true);
		exit;
	}

}
