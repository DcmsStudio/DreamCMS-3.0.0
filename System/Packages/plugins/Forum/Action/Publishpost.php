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
 * @file         Publishpost.php
 */
class Addon_Forum_Action_Publishpost extends Addon_Forum_Helper_Base
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			$threadid = intval($this->input('threadid'));
			$is_array = is_array($this->input('postid'));
			$postid   = !$is_array ? intval($this->input('postid')) : $this->input('postid');
			$mod      = $this->input('mode');
			$mode     = $this->_mod === null ? ( $mod !== null ? intval($this->input('mode')) : 1 ) : $this->_mod;


			if ( empty( $postid ) )
			{
				$this->Page->send404(trans('Dieser Beitrag wurde leider nicht gefunden.'));
			}


			$this->initCache();

			$thread = $this->model->getThreadById($threadid);

			$data                 = array ();
			$data[ 'moderators' ] = $this->model->getForumModerators($thread[ 'forumid' ]);
			$data[ 'ismod' ]      = false;

			$mod = false;
			foreach ( $data[ 'moderators' ] as $rs )
			{
				if ( User::getUserId() == $rs[ 'userid' ] )
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

			if ( !$data[ 'ismod' ] || !$data[ 'mod' ][ 'canpublishpost' ] )
			{
				$this->Page->sendAccessError(trans('Sie besitzen nicht die nötigen Rechte um diese(n) Beitrag/Beiträge zu aktivieren/deaktivieren!'));
			}







			if ( !$is_array )
			{
				$post                 = $this->model->getPostById($postid);
				$this->currentForumID = $post[ 'forumid' ];
				$parents              = $this->getParents($post[ 'forumid' ]);
				$this->buildBreadCrumb($parents, $thread);

				if ( !$post[ 'threadid' ] )
				{
					$this->Page->send404(trans('Dieses Beitrag wurde leider nicht gefunden.'));
				}



				$this->model->changePostPublishing($postid, $mode);


				$this->model->sync('thread', $post[ 'threadid' ]);
				$this->model->sync('forum', $post[ 'forumid' ]);

				$this->updateSearchIndexer($post[ 'threadid' ], $postid, $mode);


				Library::log(sprintf('Has change the Forum Post `%s` publishing to `' . ( $mode ? 'online' : 'offline' ) . '`', $post[ 'title' ]));

				if ( IS_AJAX )
				{
					Library::sendJson(true, sprintf(trans('Der Beitrag `%s` wurde %s'), $post[ 'title' ], ( $mode ? trans('aktiviert') : trans('deaktiviert') )));
				}
				else
				{
					Library::redirect('/plugin/forum/thread/' . $post[ 'threadid' ] . '/' . ( Url::makeRw($post[ 'alias' ], $post[ 'suffix' ], ( $post[ 'title' ] ? $post[ 'title' ] : $post[ 'threadtitle' ] )) ) . '?mod=1&postpublish=1');
				}

			}
			else
			{


				$tmp = array();
				foreach ( $postid as $id )
				{
					if (intval($id)) {
						$tmp[] = $id;
					}
				}

				if (!count($tmp)) {
					$this->Page->send404( trans( 'Beiträge wurde leider nicht gefunden.' ) );
				}


				$this->model->changePostPublishing($tmp, $mode);

				$this->model->sync('thread', $thread[ 'threadid' ]);
				$this->model->sync('forum', $thread[ 'forumid' ]);

				foreach ($tmp as $postid) {
					$post                 = $this->model->getPostById($postid);
					$this->updateSearchIndexer($thread[ 'threadid' ], $postid, $mode);

					Library::log(sprintf('Has change the Forum Post `%s` publishing to `' . ( $mode ? 'online' : 'offline' ) . '` in the Thread '. $thread[ 'title' ], $post[ 'title' ]));
				}

				if ( IS_AJAX )
				{
					Library::sendJson(true, sprintf(trans('Der Beitrag/Beiträge `%s` wurde %s'), count($tmp), ( $mode ? trans('aktiviert') : trans('deaktiviert') )));
				}
				else
				{
					exit;
				}

			}


		}
	}

}
