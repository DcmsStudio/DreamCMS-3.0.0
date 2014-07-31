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
 * @file         Publishthread.php
 */
class Addon_Forum_Action_Publishthread extends Addon_Forum_Helper_Base
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			$is_array = is_array($this->input('threadid'));
			$threadid = !$is_array ? intval($this->input('threadid')) : $this->input('threadid');
			$mod      = $this->input('mode');
			$mode     = $this->_mod === null ? ( $mod !== null ? intval($this->input('mode')) : 1 ) : $this->_mod;


			if ( empty( $threadid ) )
			{
				$this->Page->send404(trans('Dieses Thema wurde leider nicht gefunden.'));
			}


			$this->initCache();


			if ( !$is_array )
			{
				$thread               = $this->model->getThreadById($threadid);
				$this->currentForumID = $thread[ 'forumid' ];

				$parents = $this->getParents($thread[ 'forumid' ]);
				$this->buildBreadCrumb($parents, $thread);
				if ( !$thread[ 'threadid' ] )
				{
					$this->Page->send404(trans('Dieses Thema wurde leider nicht gefunden.'));
				}

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

				if ( !$data[ 'ismod' ] || !$data[ 'mod' ][ 'canpublishthread' ] )
				{
					$this->Page->sendAccessError(trans('Sie besitzen nicht die nötigen Rechte um dieses das Thema zu aktivieren/deaktivieren!'));
				}

				$this->model->changeThreadPublishing($thread[ 'threadid' ], $mode);

				$this->model->sync('thread', $thread[ 'threadid' ]);
				$this->model->sync('forum', $thread[ 'forumid' ]);

				$this->updateSearchIndexer($thread[ 'threadid' ], null, $mode);

				Library::log(sprintf('Forum Moderator has change the Thread `%s` publishing to `' . ( $mode ? 'online' : 'offline' ) . '`', $thread[ 'title' ]));


				if ( IS_AJAX )
				{
					Library::sendJson(true, sprintf(trans('Das Thema `%s` wurde %s'), $thread[ 'title' ], ( $mode ? trans('aktiviert') : trans('deaktiviert') )));
				}
				else
				{
					Library::redirect('/plugin/forum/thread/' . $thread[ 'threadid' ] . '/' . ( Url::makeRw($thread[ 'alias' ], $thread[ 'suffix' ], $thread[ 'title' ]) ) . '?mod=1&changepublish=1');
				}
			}
			else
			{
				$forumid = intval($this->input('forumid'));
				if (!$forumid) {
					$this->Page->send404( trans( 'Die Forum ID wird benötigt.' ) );
				}

				$data[ 'moderators' ] = $this->model->getForumModerators( $forumid );
				$data[ 'ismod' ] = false;

				$mod = false;
				foreach ( $data[ 'moderators' ] as $rs )
				{
					if ( User::getUserId() == $rs[ 'userid' ] )
					{
						$rs[ 'perm' ] = $rs[ 'permissions' ] ? unserialize( $rs[ 'permissions' ] ) : array();
						$mod = $rs;
						break;
					}
				}

				if ( is_array( $mod ) )
				{
					$data[ 'ismod' ] = true;
					$data[ 'mod' ] = $mod[ 'perm' ];
					unset( $mod );
				}

				if ( !$data[ 'ismod' ] || !$data[ 'mod' ][ 'canpublishthread' ] )
				{
					$this->Page->sendAccessError( trans('Sie besitzen nicht die nötigen Rechte um dieses das Thema zu aktivieren/deaktivieren!') );
				}

				$tmp = array();
				foreach ( $threadid as $id )
				{
					if (intval($id)) {
						$tmp[] = $id;
					}
				}

				if (!count($tmp)) {
					$this->Page->send404( trans( 'Themen wurde leider nicht gefunden.' ) );
				}


				$this->model->changeThreadPublishing($tmp, $mode);




				foreach ( $tmp as $threadid ) {
					$thread = $this->model->getThreadById( $threadid );
					$this->model->sync('thread', $thread[ 'threadid' ]);
					$this->updateSearchIndexer($thread[ 'threadid' ], null, $mode);
					Library::log(sprintf('Forum Moderator has change the Thread `%s` publishing to `' . ( $mode ? 'online' : 'offline' ) . '`', $thread[ 'title' ]));
				}

				$this->model->sync('forum', $forumid);

				if ( IS_AJAX )
				{
					Library::sendJson(true, sprintf(trans('Es wurden %s Themen %s'), count($tmp), ( $mode ? trans('aktiviert') : trans('deaktiviert') )));
				}
				else
				{
					die();
				}
			}
		}
	}

}
