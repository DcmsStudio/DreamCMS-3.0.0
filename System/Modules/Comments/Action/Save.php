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
 * @package      Comments
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Save.php
 */
class Comments_Action_Save extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->getApplication()->getMode() === Application::BACKEND_MODE )
		{
			$this->_processBackend();
		}
		else
		{
			$this->_processFrontend();
		}
	}

	protected function _processFrontend ()
	{


		$postid   = (int)HTTP::post('commentid');
		$postType = trim(HTTP::post('commenttype'));
		$parentID = (int)HTTP::post('parent');
		$title    = trim(HTTP::post('title'));
		$comment  = trim(HTTP::post('comment'));
		$username = trim(HTTP::post('guestname'));

        if (Library::isBlacklistedUsername($username))
        {
            Library::log('Blacklisted Username will post comment', 'warn');
            $error[ ][ 'msg' ] = trans('Blacklisted Username!');
        }

		if ( !$postid )
		{
			if ( IS_AJAX )
			{
				Library::sendJson(false, 'Sorry no Post ID was send.');
			}

			$this->Page->sendError('Sorry no Post ID was send.');
		}

		if ( !$postType )
		{
			if ( IS_AJAX )
			{
				Library::sendJson(false, 'Sorry no Post Type was send.');
			}

			$this->Page->sendError('Sorry no Post Type was send.');
		}


		if ( !User::isLoggedIn() && ($username == '' || strlen($username) < 3) )
		{
			if ( IS_AJAX )
			{
				Library::sendJson(false, 'Please write your Name before post the Comment.');
			}

			$this->Page->sendError(trans('Please write your Name before post the Comment.'));
		}


		if ( !User::hasPerm(Session::get('comment_' . $postType)) )
		{
			if ( IS_AJAX )
			{
				Library::sendJson(false, trans('Sie haben keine Berechtigung einen Kommentar zu schreiben' . Session::get('comment_' . $postType)));
			}

			$this->Page->sendAccessError(trans('Sie haben keine Berechtigung einen Kommentar zu schreiben'));
		}

		if ( $title == '' || strlen($title) <= 3 )
		{
			if ( IS_AJAX )
			{
				Library::sendJson(false, 'Sorry no Comment Title was send.');
			}

			$this->Page->sendError('Sorry no Comment Title was send.');
		}

		if ( !isset($comment) || empty($comment) || $comment == '' || strlen($comment) < 10 )
		{
			if ( IS_AJAX )
			{
				Library::sendJson(false, 'Sorry no Comment was send.');
			}

			$this->Page->sendError('Sorry no Comment was send.');
		}

		if ( (Session::get('hasComment-' . $postid . '-' . $postType) + Settings::get('fctime', 3600)) > time() )
		{

			if ( IS_AJAX )
			{

				Library::sendJson(false, sprintf(trans('Sorry aber Sie können nicht mehrere Kommentare hintereinander schreiben! Warten Sie bitte noch %s Sekunden um einen weiteren Kommentar zu posten.'), (Session::get('hasComment-' . $postid . '-' . $postType) + Settings::get('fctime', 3600)) - time()));
			}

			$this->Page->sendError(trans('Sorry aber Sie können nicht mehrere Kommentare hintereinander schreiben! Warten Sie bitte noch %s Sekunden um einen weiteren Kommentar zu posten.'));
		}


		$origcaptcha = Session::get('site_captcha');
		$usercaptcha = (string)HTTP::post('captcha');

		if ( strtoupper($usercaptcha) !== strtoupper($origcaptcha) )
		{
			if ( IS_AJAX )
			{
				Library::sendJson(false, 'Please check your Secure Code.');
			}

			$this->Page->sendError(trans('Please check your Secure Code.'));
		}


		/**
		 * Letzen kommentar prüfen
		 * wenn der selbe User dann fehler
		 */
		// $last = $this->model->getLastComment( $postid, $postType );

		$_username               = (User::getUserId() ? User::get('username') : $username);
		$data[ 'newcomment_id' ] = $this->model->saveComment($title, $comment, $postType, $postid, $_username, $parentID);
		/*
		  $data[ 'success' ]       = true;
		  $data[ 'timestamp' ]     = TIMESTAMP;
		  $data[ 'parentid' ]      = $parentID;
		  $data[ 'comment' ]       = BBCode::toXHTML( $comment );
		  $data[ 'userid' ]        = User::getUserId();
		  $data[ 'username' ]      = $_username;
		 */



        $com = $this->model->getCommentById($data[ 'newcomment_id' ]);

        $com[ 'article_url' ]    = rawurldecode($this->_post('article_url'));
        $com[ 'article_title' ]  = html_entity_decode( $this->_post('article_title') );

        $commentUrl = $com[ 'article_url' ] . '#comment_' . $com[ 'id' ];

        if ( $com['published'] === PUBLISH_MODE )
        {
            User::subPostCounter();

            if (Settings::get('emailnotifieroncomment')) {

                $mail = new Mail();
                $mail->mail_to( Settings::get('webmastermail') );
                $mail->mail_subject( trans('Neuer Kommentar') );
                $mail->mail_body( sprintf(trans('Hallo Admin,

ein neuer Kommentar wurde dem Artikel <a href="%s">%s</a> hinzugefügt.

Kommentar ID: %s
Url zum Kommentar: %s
'), $com[ 'article_url' ], $com[ 'article_title' ], $com[ 'id' ], $commentUrl) );

                if (!$mail->send()) {
                    Library::log('Comment Mailer could not send mail to '. Settings::get('webmastermail') , 'warn');
                }
            }
        }
        else if ( $com['published'] === SPAM_MODE )
        {
            //User::subPostCounter();

            if ( User::isLoggedIn() ) {
                // block the user

                if (Settings::get('blockuserifhaspostspam', 0)) {

                }

                if (Settings::get('emailnotifieronblockuser') || Settings::get('emailnotifieroncomment')) {


                    $mail = new Mail();
                    $mail->mail_to( Settings::get('webmastermail') );
                    $mail->mail_subject( sprintf(trans('Der Benutzer %s hat Kommentar-Spam gepostet'), User::getUsername() ) );
                    $mail->mail_body( sprintf(trans('Hallo Admin,

einer deiner Registrierten User hat einen Kommentar zum Artikel <a href="%s">%s</a> geschrieben, welcher als Spam markiert wurde.
Um missverständnisse zu vermeiden, überprüfe den Kommentar nochmals Manuell.

Kommentar ID: %s
Url zum Kommentar: %s'), $com[ 'article_url' ], $com[ 'article_title' ], $com[ 'id' ], $commentUrl));

                    if (!$mail->send()) {
                        Library::log('Comment Mailer could not send mail to '. Settings::get('webmastermail') , 'warn');
                    }

                }
            }

        }
        else if ( $com['published'] === MODERATE_MODE )
        {
            if (Settings::get('emailnotifieroncommentwait')) {
                $r = $this->model->countWaitingComments();



                $mail = new Mail();
                $mail->mail_to( Settings::get('webmastermail') );
                $mail->mail_subject( trans('Kommentare die auf Freischaltung warten') );
                $mail->mail_body( sprintf(trans('Hallo Admin,

ein neuer Kommentar wurde dem Artikel <a href="%s">%s</a> hinzugefügt.
Du hast aktuell %s Kommentar(e) die auf ihre Freischaltung warten.

letzte Kommentar ID: %s
Url zum letzten Kommentar: %s

'), $com[ 'article_url' ], $com[ 'article_title' ], $r[ 'total' ], $com[ 'id' ], $commentUrl));


                if (!$mail->send()) {
                    Library::log('Comment Mailer could not send mail to '. Settings::get('webmastermail') , 'warn');
                }



            }
            elseif (Settings::get('emailnotifieroncomment')) {


                $mail = new Mail();
                $mail->mail_to( Settings::get('webmastermail') );
                $mail->mail_subject( trans('Neuer Kommentar') );
                $mail->mail_body( sprintf(trans('Hallo Admin,

ein neuer Kommentar wurde dem Artikel <a href="%s">%s</a> hinzugefügt.

Kommentar ID: %s
Url zum Kommentar: %s
'), $com[ 'article_url' ], $com[ 'article_title' ], $com[ 'id' ], $commentUrl) );

                if (!$mail->send()) {
                    Library::log('Comment Mailer could not send mail to '. Settings::get('webmastermail') , 'warn');
                }
            }
        }



		Library::log(sprintf(trans('User (%s) wrote a comment to post %s id: %s'), $_username, $postType, $postid));


		// Session::delete('site_captcha');
		Session::delete('captcha_audio');
		Session::delete('comment_' . $postType);
		Session::save('hasComment-' . $postid . '-' . $postType, time());

		Cache::clear('data/lastcomments');

		if ( !User::getUserId() )
		{
			Session::save('comment-' . $postid . '-' . $postType . '-username', (string)$username);
		}






		if ( IS_AJAX )
		{

            if ( $com['published'] === MODERATE_MODE ) {
                $com['comment'] = trans('Ihr Kommentar wird von einem Moderator erst überprüft, bevor er erscheint.');
                $com[ 'newcomment_id' ] = $data[ 'newcomment_id' ];
                $data = $com;
            }
            else if ( $com['published'] === SPAM_MODE )
            {
                $com['comment'] = trans('Tut uns leid, aber Ihr Kommentar wurde als Spam deklariert und ist daher nicht sichtbar!');
                $com[ 'newcomment_id' ] = $data[ 'newcomment_id' ];
                $data = $com;
            }
            else {
                $com[ 'newcomment_id' ] = $data[ 'newcomment_id' ];
                $data = $com;
            }

			echo Library::json($data);
			exit;
		}

		if ( $commentUrl )
		{
			header('Location: ' . $commentUrl);

			exit;
		}
		else
		{
			// print a template
		}
	}

	/**
	 * Update a Comment in the Backend
	 */
	protected function _processBackend ()
	{

		demoadm();
	}

}

?>