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
 * @file         Newthread.php
 */
class Addon_Forum_Action_Newthread extends Addon_Forum_Helper_Base
{

	private $forumID;

	function execute ()
	{

		if ( $this->isFrontend() )
		{
			$forumid   = ( $this->input('forumid') ? intval($this->input('forumid')) : 0 );
			$forumname = ( HTTP::input('forumname') ? HTTP::input('forumname') : '' );


			$this->currentForumID = $forumid;


			$this->initCache();

			$data            = array ();
			$data[ 'forum' ] = ( isset( $this->forum_by_id[ $forumid ] ) ? $this->forum_by_id[ $forumid ] : false );
			$this->forum     = $data[ 'forum' ];


			$parents = $this->getParents($forumid);

			$this->buildBreadCrumb($parents, array ());

			if ( !$data[ 'forum' ] )
			{
				$this->Page->send404(trans('Hoppla dieses Forum existiert leider nicht!'));
			}


			if ( !User::getPerm('forum/run') )
			{
				$this->Page->sendAccessError(trans('Sie dürfen dieses Forum leider nicht sehen. Loggen Sie sich bitte ein um dieses Forum sehen zu können.'));
			}

			if ( !User::getPerm('forum/canpostnew') )
			{
				$this->Page->sendAccessError(trans('Sie dürfen in diesem Forum leider keine neuen Themen erstellen.'));
				exit;
			}

			if ( $data[ 'forum' ][ 'access' ] != '' && !in_array(User::getGroupId(), explode(',', $data[ 'forum' ][ 'access' ])) && !in_array(0, explode(',', $data[ 'forum' ][ 'access' ])) )
			{
				$this->Page->sendAccessError(trans('Sie dürfen in diesem Forum leider keine neuen Themen erstellen.'));
			}


			if ( $this->_post('send') )
			{
				$this->doPost();

				exit;
			}


			if ( $data[ 'forum' ][ 'allowicons' ] )
			{
				$data[ 'posticons' ] = $this->loadPostIcons();
			}

			if ( !Session::get('posthash' . $forumid) )
			{
				Session::save('posthash' . $forumid, md5(time() . $forumid));
			}

			$data[ 'posthash' ] = Session::get('posthash' . $forumid);
			$data[ 'smilies' ]  = Json::encode($this->getSmilies());


			$this->Template->addScript('html/js/jquery/wysibb/jquery.wysibb.js');
			$this->Template->addScript('html/js/jquery/wysibb/lang/en.js');
			$this->Template->addScript('html/js/jquery/wysibb/lang/de.js');
			$this->Template->mergeJavascripts();

			$this->Template->process('board/newthread', $data, true);
		}
	}

	private function doPost ()
	{

		if ( Session::get('posthash' . $this->currentForumID) != $this->_post('posthash') )
		{
			Library::sendJson(false, trans('Das Formular wurde manipuliert.'));
		}


		$_ch = trim($this->_post('_ch'));


		$username       = ( User::isLoggedIn() ? '' : $this->_post('username') );
		$subject        = trim($this->_post('subject'));
		$message        = trim($this->_post('message'));
		$parseurl       = intval($this->_post('parseurl'));
		$disablesmilies = intval($this->_post('disablesmilies'));
		$signature      = intval($this->_post('signature'));

        if (Library::isBlacklistedUsername($username))
        {
            Library::log('Blacklisted Username found for post new Forum Thread', 'warn');
            Library::sendJson(false, trans('Blacklisted Username!'));
        }







		$orig       = Session::get('captcha-' . $_ch);
		$securecode = $this->_post('securecode');

		if ( strtoupper($securecode) !== strtoupper($orig) )
		{
			Library::sendJson(false, 'Der Sicherheitscode fehlt oder ist nicht richtig.');
		}

		if ( !User::isLoggedIn() && !trim($username) )
		{
			Library::sendJson(false, trans('Sie haben keinen Benutzernamen angegeben.'));
		}

		if ( !$subject || strlen($subject) < 4 )
		{
			Library::sendJson(false, trans('Sie haben kein Titel für ihr Thema angegeben.'));
		}

		if ( !$message || strlen($message) < 10 )
		{
			Library::sendJson(false, trans('Sie haben keinen Inhalt angegeben bzw. zu wenig zeichen.'));
		}


		if ( !$this->doUpload(true) ) // execute classic upload
		{
			echo Library::json(array (
			                         'success'  => false,
			                         'msg'      => $this->uploadError
			                   ));
			exit;
		}
		else
		{
			$threaddata = array (
				'forumid'           => $this->currentForumID,
				'realforumid'       => $this->currentForumID,
				'threadauthorid'    => User::getUserId(),
				'threadauthor'      => $username,
				'threadtype'        => intval($this->_post('threadtype')),
				'lastposttime'      => TIMESTAMP,
				'createdate'        => TIMESTAMP,
				'title'             => $subject,
				'access'            => '',
				'published'         => User::getPerm('forum/isalwaysmoderated') ? 0 : 1,
				'hits'              => 0,
				'firstpostid'       => 0,
				'lastpostid'        => 0,
				'pageid'            => PAGEID,
				'attachmentcounter' => 0,
				'pinned'            => 0,
				'closed'            => 0,
				'postcounter'       => 0,
				'iconid'            => intval($this->_post('iconid')),
				'alias'             => '',
				'suffix'            => ''
			);


			$str = $this->db->compile_db_insert_string($threaddata);
			$sql = "INSERT INTO %tp%board_threads ({$str['FIELD_NAMES']}) VALUES({$str['FIELD_VALUES']})";
			$this->db->query($sql);


			$threadID = $this->db->insert_id();
			$postdata = array (
				'alias'         => '',
				'suffix'        => '',
				'threadid'      => $threadID,
				'username'      => $username,
				'userid'        => User::getUserId(),
				'parent'        => 0,
				'title'         => $subject,
				'content'       => $message,
				'createdate'    => TIMESTAMP,
				'published'     => User::getPerm('forum/isalwaysmoderated') ? 0 : 1,
				'iconid'        => intval($this->_post('iconid')),
				'showsignature' => intval($this->_post('showsignature')),
				'parsebbcode'   => intval($this->_post('disablebbcode')) ? 0 : 1,
				'parseurls'     => intval($this->_post('parseurls')),
				'parsesmilies'  => intval($this->_post('disablesmilies')) ? 0 : 1,
				'ip'            => $this->Env->ip(),
				'pageid'        => PAGEID,
				'likes'         => 0,
				'dislike'       => 0,
				'tags'          => ''
			);

			$str = $this->db->compile_db_insert_string($postdata);
			$sql = "INSERT INTO %tp%board_posts ({$str['FIELD_NAMES']}) VALUES({$str['FIELD_VALUES']})";
			$this->db->query($sql);
			$postid = $this->db->insert_id();


			$this->updateUploads($postid, $this->_post('posthash'));


			$sql = "UPDATE %tp%board_trans SET
                        lastposttime = " . TIMESTAMP . ",
                        lastpostthreadid = $threadID,
                        lastpostuserid = " . User::getUserId() . ",
                        lastpostusername = " . $this->db->quote($username) . ",
                        lastposttitle = " . $this->db->quote($subject) . "
                    WHERE forumid = ? AND lang = ?";
			$this->db->query($sql, $this->currentForumID, $this->forum[ 'lang' ]);

			$this->model->sync('thread', $threadID);
			$this->model->sync('forum', $this->currentForumID);


			//   $this->model->updateThreadCounters( $this->currentForumID );
			//   $this->model->updateForumCounters( $this->currentForumID );



			$this->updateSearchIndexer($threadID);

			Session::delete('posthash');

            // Update userposts and lastpost section
            User::updateLastpost('forum/' . $threadID . '/' . Library::suggest($subject, true) . '?getpost=' . $postid . '#post-' . $postid, $subject);
            User::subPostCounter();


			$msg = trans('Ihr Thema wurde erfolgreich gespeichert.');

			if ( User::getPerm('forum/isalwaysmoderated') )
			{
				$msg = trans('Ihr Thema wurde erfolgreich gespeichert und wird erst durch einen Moderator freigeschaltet.');
			}



			echo Library::json(array (
			                         'success'  => true,
			                         'threadid' => $threadID,
			                         'postid'   => $postid,
			                         'msg'      => $msg
			                   ));
			exit;

		}




	}

}
