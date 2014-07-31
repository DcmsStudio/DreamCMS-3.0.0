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
 * @file         Replythread.php
 */
class Addon_Forum_Action_Replythread extends Addon_Forum_Helper_Base
{

	function execute ()
	{

		if ( $this->isFrontend() )
		{

			$threadid     = ( $this->input('threadid') ? intval($this->input('threadid')) : 0 );
			$do           = ( $this->input('do') ? $this->input('do') : '' );
			$parentpostid = ( $this->input('postid') ? intval($this->input('postid')) : 0 );

			if ( !$threadid )
			{
				$this->Page->send404(trans('Dieses Thema wurde leider nicht gefunden.'));
			}


			if ( $do == '' )
			{
				$thread = $this->model->getThreadPost($threadid);

				if ( !$thread[ 'threadid' ] )
				{
					$this->Page->send404(trans('Der Beitrag existiert leider nicht.'));
				}
			}
			elseif ( $do == 'reply' || $do == 'quote' )
			{
				$thread = $this->model->getThreadPost($threadid, $parentpostid);

				if ( !$thread[ 'threadid' ] )
				{
					$this->Page->send404(trans('Der Beitrag auf den Sie antworten möchten existiert leider nicht.'));
				}
			}

			$this->currentForumID = $thread[ 'forumid' ];

			$this->initCache();

			$parents = $this->getParents($thread[ 'forumid' ]);
			$childs  = $this->getChildren($thread[ 'forumid' ]);

			$data[ 'forum' ] = ( isset( $this->forum_by_id[ $thread[ 'forumid' ] ] ) ? $this->forum_by_id[ $thread[ 'forumid' ] ] : false );

			if ( !$data[ 'forum' ] )
			{
				$this->Page->send404(trans('Hoppla dieses Forum existiert leider nicht!'));
			}


			$this->forum = & $data[ 'forum' ];

			$this->buildBreadCrumb($parents, $thread);

			if ( !User::hasPerm('forum/run') )
			{
				$this->Page->sendAccessError(trans('Sie dürfen dieses Forum leider nicht sehen. Loggen Sie sich bitte ein um dieses Forum sehen zu können.'));
			}

			if ( !User::hasPerm('forum/canreplyothers', false) )
			{
				$this->Page->sendAccessError(trans('Sie dürfen in diesem Forum leider auf keine Themen antworten.'));
				exit;
			}


			if ( User::isLoggedIn() && $thread[ 'posterid' ] > 0 )
			{
				if ( $thread[ 'posterid' ] === User::getUserId() && !User::hasPerm('forum/canreplyown', false) )
				{
					$this->Page->sendAccessError(trans('Sie dürfen auf ihre eigenen Beiträge leider nicht antworten.'));
					exit;
				}
			}


			$data                = array ();
			$data[ 'forumpost' ] = $thread;
			$forumid             = (int)$data[ 'forumpost' ][ 'forumid' ];

			if ( $do == 'quote' )
			{
				$data[ 'forumpost' ][ 'message' ] = '[quote' . ( $thread[ 'username' ] != '' ? '="' . $thread[ 'username' ] . '"' : '' ) . ']' . $thread[ 'content' ] . '[/quote]' . "\r\n\r\n";
			}
			else
			{
				$data[ 'forumpost' ][ 'message' ] = null;
			}


			if ( $data[ 'forum' ][ 'access' ] != '' && !in_array(User::getGroupId(), explode(',', $data[ 'forum' ][ 'access' ])) && !in_array(0, explode(',', $data[ 'forum' ][ 'access' ])) )
			{
				$this->Page->sendAccessError(trans('Sie dürfen in diesem Forum leider auf keine Themen antworten.'));
			}


			if ( HTTP::post('send') )
			{

				$error = array ();
				if ( $this->_post('posthash') != Session::get('posthash' . $threadid, false) )
				{
					$error[ ][ 'msg' ] = trans('Das Formular wurde manipuliert.');
				}



				$username       = ( User::isLoggedIn() ? '' : $this->_post('username') );
				$subject        = trim($this->_post('subject'));
				$message        = trim($this->_post('message'));



                if (Library::isBlacklistedUsername($username))
                {
                    Library::log('Blacklisted Username will reply Forum Thread', 'warn');
                    $error[ ][ 'msg' ] = trans('Blacklisted Username!');
                }


				$_ch = trim($this->_post('_ch'));
				$orig       = Session::get('captcha-' . $_ch);
				$securecode = $this->_post('securecode');

				if (strtoupper($securecode) !== strtoupper($orig) )
				{
					$error[ ] = trans('Der Sicherheitscode fehlt oder ist nicht richtig.');
				}

				if ( !User::isLoggedIn() && !trim($username) )
				{
					$error[ ][ 'msg' ] = trans('Sie haben keinen Benutzernamen angegeben.');
				}

				if ( $do == 'quote' && trim($data[ 'forumpost' ][ 'message' ]) == trim($message) )
				{
					$error[ ][ 'msg' ] = trans('Sie haben keinen Inhalt angegeben bzw. zu wenig zeichen.');
				}

				if (  ( !$message || strlen($message) < 10 ) )
				{
					$error[ ][ 'msg' ] = trans('Sie haben keinen Inhalt angegeben bzw. zu wenig zeichen.');
				}




				if ( IS_AJAX && count($error) )
				{
					Library::sendJson(false, implode('<br/>', array_values($error)));
				}
				elseif ( !count($error) )
				{


					if ( intval($parentpostid) > 0 )
					{
						$re = $this->db->query('SELECT title FROM %tp%board_posts WHERE postid = ?', $parentpostid)->fetch();

						if ( $re[ 'title' ] && !$subject )
						{
							$subject = 'RE: ' . $re[ 'title' ];
						}
						else if ( $re[ 'title' ] && $subject )
						{
							$subject = 'RE: ' . $re[ 'title' ] . ' - ' . $subject;
						}
						else
						{
							$subject = 'RE: ' . $thread[ 'title' ] . ' - ' . $subject;
						}

					}
					else
					{
						$subject = 'RE: ' . $thread[ 'title' ] . ( $subject ? ' - ' . $subject : '' );
					}



					if (!$this->doUpload(true)) // execute classic upload
					{
						$data[ 'error' ] = $this->uploadError;
					}
					else
					{
						$data = array (
							'threadid'      => $threadid,
							'username'      => $username,
							'userid'        => User::getUserId(),
							'parent'        => intval($parentpostid),
							'iconid'        => intval($this->_post('iconid')),
							'title'         => $subject,
							'content'       => $message,
							'createdate'    => TIMESTAMP,
							'published'     => 1,
							'ip'            => $this->Env->ip(),
							'pageid'        => PAGEID,
							'parsesmilies'  => intval($this->_post('disablesmilies')) ? 0 : 1,
							'parseurls'     => intval($this->_post('parseurls')),
							'showsignature' => intval($this->_post('showsignature')),
							'parsebbcode'   => intval($this->_post('disablebbcode')) ? 0 : 1,
							'likes'         => 0,
							'dislike'       => 0,
							'posthash'      => $this->_post('posthash')
						);


						$str = $this->db->compile_db_insert_string($data);
						$sql = "INSERT INTO %tp%board_posts ({$str['FIELD_NAMES']}) VALUES({$str['FIELD_VALUES']})";
						$this->db->query($sql);

						$postid = $this->db->insert_id();

						$this->db->query('UPDATE %tp%board_threads SET lastposttime = ?, lastpostid = ? WHERE threadid = ?', TIMESTAMP, $postid, $threadid);
						$parents[ ] = $forumid;

						$this->updateUploads($postid, $this->_post('posthash'));

						$this->model->sync('thread', $threadid);
						$this->model->sync('forum', $forumid);


						$this->updateSearchIndexer($threadid, $postid);



						// clean Cache
						#$this->PageCache->cleanCache( $forumid, 'forum', 'index' );
						#$this->PageCache->cleanCache( $threadid, 'forum', 'thread' );

						Session::delete('posthash' . $threadid);


                        // update userposts and lastpost section
						User::subPostCounter();
                        User::updateLastpost('forum/' . $threadid . '/' . Library::suggest(( $subject ? $subject : $thread[ 'title' ] ), true) . '?getpost=' . $postid . '#post-' . $postid, ( $subject ? $subject : $thread[ 'title' ] ));


						if ( IS_AJAX )
						{
							echo Library::json(array (
							                         'success'  => true,
							                         'threadid' => $threadid,
							                         'postid'   => $postid,
							                         'msg'      => trans('Ihr Beitrag wurde erfolgreich gespeichert.')
							                   ));
						}
						else
						{
							$data[ 'smilies' ]   = json_encode(array ());
							$data[ 'posticons' ] = array ();
							$data[ 'postid' ]    = $postid;
							$data[ 'submited' ]  = trans('Ihr Beitrag wurde erfolgreich gespeichert.');

							$this->Template->process('board/replythread', $data, true);
						}

						exit;
					}
				}
				else
				{
					$data[ 'error' ] = $error;
				}
			}


			if ( empty( $data[ 'forumpost' ][ 'forumid' ] ) || !isset( $data[ 'forumpost' ][ 'forumid' ] ) )
			{
				$this->Page->send404(trans('Forum wurde nicht gefunden.'));
			}

			if ( $data[ 'forum' ][ 'allowicons' ] )
			{
				$data[ 'posticons' ] = $this->loadPostIcons();
			}

			/*

			  $parents = array_reverse( $parents );

			  $this->Breadcrumb->add( trans( 'Forum' ), '/plugin/forum' );

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

			  $this->Breadcrumb->add( $thread[ 'title' ], '/plugin/forum/thread/' . $thread[ 'threadid' ] . '/' . Url::makeRw( $thread[ 'alias' ], $thread[ 'suffix' ], $thread[ 'title' ] ) );
			  $this->Breadcrumb->add( sprintf( trans( 'Auf das Thema `%s` antworten ' ), $thread[ 'title' ] ), '' );


			 */


			if ( !Session::get('posthash' . $threadid, false) )
			{
				Session::save('posthash' . $threadid, md5(TIMESTAMP . '' . $threadid . '' . $parentpostid));
			}

			$data[ 'posthash' ] = Session::get('posthash'. $threadid);
			$this->Input->set('posthash', $data[ 'posthash' ]  );
			$data[ 'smilies' ]  = Json::encode($this->getSmilies());




			$this->Template->addScript('html/js/jquery/wysibb/jquery.wysibb.js');
			$this->Template->addScript('html/js/jquery/wysibb/lang/en.js');
			$this->Template->addScript('html/js/jquery/wysibb/lang/de.js');
			$this->Template->mergeJavascripts();

			$this->Template->process('board/replythread', $data, true);
		}
	}

}
