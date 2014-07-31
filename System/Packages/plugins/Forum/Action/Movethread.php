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
 * @file         Movethread.php
 */
class Addon_Forum_Action_Movethread extends Addon_Forum_Helper_Base
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			$is_array  = is_array($this->input('threadid'));
			$threadid  = !$is_array ? intval($this->input('threadid')) : $this->input('threadid');
			$mod       = $this->input('mode');
			$mode      = $this->_mod === null ? ( $mod !== null ? intval($this->input('mode')) : 1 ) : $this->_mod;
			$toforumid = intval($this->input('toforumid'));

			if ( empty( $threadid ) )
			{
				$this->Page->send404(trans('Dieses Thema wurde leider nicht gefunden.'));
			}

			if ( !$toforumid )
			{
				$this->Page->send404(trans('Dieses Thema kann nicht verschoben werden, da das Forum nicht gefunden wurde.'));
			}

			$this->initCache();
			if ( !$is_array )
			{
				$thread = $this->model->getThreadById($threadid);

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

				if ( !$data[ 'ismod' ] || !$data[ 'mod' ][ 'canmove' ] )
				{
					$this->Page->sendAccessError(trans('Sie besitzen nicht die nötigen Rechte um dieses das Thema zu schließen/öffnen!'));
				}
			}
			else
			{

				$forumid = intval($this->input('forumid'));
				if ( !$forumid )
				{
					$this->Page->send404(trans('Die Forum ID wird benötigt.'));
				}


				if ( !isset( $this->forum_by_id[ $toforumid ] ) )
				{
					$this->Page->send404(trans('Dieses Thema kann nicht verschoben werden, da das Forum nicht gefunden wurde.'));
				}

				if ( !$this->forum_by_id[ $toforumid ][ 'containposts' ] )
				{
					$this->Page->send404(sprintf(trans('Dieses Thema kann nicht verschoben werden, da das Forum %s keine Themen enthalten darf.'), $this->forum_by_id[ $toforumid ][ 'title' ]));
				}


				$data                         = array ();
				$data[ 'moderators' ]         = $this->model->getForumModerators($forumid);
				$data[ 'toforum_moderators' ] = $this->model->getForumModerators($toforumid);

				$ismod = false;
				$mod   = false;
				foreach ( $data[ 'moderators' ] as $rs )
				{
					if ( User::getUserId() == $rs[ 'userid' ] )
					{
						$mod = $rs[ 'permissions' ] ? unserialize($rs[ 'permissions' ]) : array ();
						break;
					}
				}

				if ( is_array($mod) )
				{
					$ismod = true;
				}

				if ( !$ismod || !$mod[ 'canmove' ] )
				{
					$this->Page->sendAccessError(trans('Sie besitzen nicht die nötigen Rechte um diese Themen zu verschieben! ' . print_r($mod, true)));
				}

/*
				$ismod = false;
				$target   = false;
				foreach ( $data[ 'toforum_moderators' ] as $rs )
				{
					if ( User::getUserId() == $rs[ 'userid' ] )
					{
						$target = $rs[ 'permissions' ] ? unserialize($rs[ 'permissions' ]) : array ();
						break;
					}
				}

				if ( is_array($target) )
				{
					$ismod  = true;
				}

				if ( !$ismod || !$target[ 'canmove' ] )
				{
					$this->Page->sendAccessError(trans('Sie besitzen nicht die nötigen Rechte um diese Themen in das Target Forum zu verschieben!'));
				} */

				$tmp = array ();
				foreach ( $threadid as $id )
				{
					if ( intval($id) )
					{
						$tmp[ ] = $id;
					}
				}

				if ( !count($tmp) )
				{
					$this->Page->send404(trans('Themen wurde leider nicht gefunden.'));
				}

				$this->model->moveThread($tmp, $forumid, $toforumid);

				foreach ( $tmp as $threadid )
				{
					$thread = $this->model->getThreadById($threadid);
					Library::log(sprintf('Forum Moderator has move the Thread `%s` to Forum `%s`', $thread[ 'title' ], $this->forum_by_id[ $toforumid ][ 'title' ]));
				}



				$this->model->sync('forum', $forumid);
				$this->model->sync('forum', $toforumid);

				if ( IS_AJAX )
				{
					Library::sendJson(true, sprintf(trans('Es wurden %s Themen in das Forum %s verschoben'), count($tmp), $this->forum_by_id[ $toforumid ][ 'title' ]));
				}
				else
				{
					die();
				}
			}
		}
	}

}
