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
 * @package      Forms
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Send.php
 */
class Forms_Action_Send extends Controller_Abstract
{

	/**
	 * @param $data
	 */
	private function validate ( $data )
	{

		$rules = array ();
	}

	public function execute ()
	{

		if ( $this->getApplication()->getMode() == Application::BACKEND_MODE )
		{
			return;
		}
		demoadm();

		$data = HTTP::input();

		if ( !isset($data[ 'formid' ]) || !(int)$data[ 'formid' ] )
		{
			Error::raise(trans('Das von Ihnen übergebene Formular existiert leider nicht.'));
		}

		$formData = $this->model->getForm((int)$data[ 'formid' ]);


		if ( $this->Input->getMethod() == 'post' )
		{
			$data = $this->_post();
		}
		elseif ( $this->Input->getMethod() == 'get' )
		{
			$data = $this->_get();
		}


		$this->load('Form');


		// check form manipulation
		if ( !$this->Form->isValidToken() )
		{
			if ( IS_AJAX )
			{
				echo Library::json(array (
				                         'success' => false,
				                         'msg'     => 'Sorry your Request has a Invalid Token'
				                   ));
				exit;
			}


			$this->Page->sendError('Sorry your Request has a Invalid Token');
		}


		$validation = $this->Form->validateForm($formData[ 'name' ], $data);


		if ( count($validation[ 'errors' ]) )
		{

			if ( IS_AJAX )
			{
				echo Library::json(array (
				                         'success' => false,
				                         'errors'  => $validation[ 'errors' ]
				                   ));

				exit;
			}
			else
			{
				Error::sendError($formData[ 'errormsg' ]);
			}
		}


		if ( $formData[ 'formtype' ] === 'mail' )
		{
			$fields = $this->model->getFieldsByFormID($formData[ 'formid' ]);


			$emailField = '';
			$msg        = '';
			foreach ( $fields as $r )
			{
				$rs          = unserialize($r[ 'options' ]);
				$label       = $rs[ 'label' ];
				$description = $rs[ 'description' ];
				$msg .= '<p>' . $data[ $r[ 'name' ] ] . '</p>';

				if ( $r[ 'type' ] == 'email' && !$emailField )
				{
					$emailField = $data[ $r[ 'name' ] ];
				}

				$formData[ 'email_template' ] = str_ireplace('[' . $r[ 'name' ] . ']', (!empty($data[ $r[ 'name' ] ]) ?
						$data[ $r[ 'name' ] ] : '-'), $formData[ 'email_template' ]);
			}


			$email   = (!empty($formData[ 'email' ]) ? $formData[ 'email' ] : Settings::get('frommail'));
			$message = (trim($formData[ 'email_template' ]) ? $formData[ 'email_template' ] : $msg);
			$subject = $formData[ 'title' ];

			foreach ( $data as $key => $value )
			{
				if ( is_string($key) )
				{
					$message = str_ireplace('[' . $key . ']', $value, $message);
				}
			}


			Library::log('Sending email to: ' . $emailField);

			$m = new Mail(); // create the mail
			$m->mail_from(array (
			                    Settings::get('frommail'),
			                    Settings::get('pagename') . ' - Formmailer'
			              ));
			$m->mail_cc(array (
			                  Settings::get('frommail'),
			                  Settings::get('pagename') . ' - Formmailer'
			            ));
			$m->mail_to($email);
			$m->mail_subject(Settings::get('pagename') . ' - ' . strip_tags($subject));

			$m->mail_body($message); // set the body
			$send  = $m->send();
			$send2 = true;

			if ( $send && $emailField )
			{
				unset($m);

				$m = new Mail(); // create the mail
				$m->mail_from(array (
				                    Settings::get('frommail'),
				                    Settings::get('pagename')
				              ));

				$m->mail_to($emailField); // send to the user

				$m->mail_subject(Settings::get('pagename') . ' - ' . sprintf(trans('Kopie Ihrer Nachricht "%s"'), strip_tags($subject)));

				$m->mail_body(sprintf(trans('Kopie deiner Nachricht an uns:<br/><br/>%s'), $message)); // set the body
				$send2 = $m->send();
			}


			if ( !$send || !$send2 )
			{
				if ( IS_AJAX )
				{
					Library::sendJson(false, trans('Leider konnte Ihr Formular nicht abgeschickt werden.'));
				}
				else
				{
					Error::sendError(trans('Leider konnte Ihr Formular nicht abgeschickt werden.'));
				}
			}
		}
		elseif ( $formData[ 'formtype' ] === 'data' )
		{
			$doc = new DOMDocument('1.0', 'utf-8');


			$root = $doc->appendChild($doc->createElement('form_' . $data[ 'formid' ]));

			$f = $doc->createElement('timestamp');
			$root->appendChild($f);
			$f->appendChild($doc->createTextNode(time()));

			$f = $doc->createElement('ip');
			$root->appendChild($f);
			$f->appendChild($doc->createTextNode($this->Env->ip()));

			$f = $doc->createElement('agent');
			$root->appendChild($f);
			$f->appendChild($doc->createTextNode($this->Env->httpUserAgent()));


			$fields = $this->model->getFieldsByFormID($data[ 'formid' ]);
			foreach ( $fields as $r )
			{
				$f = $doc->createElement($r[ 'name' ]);
				$root->appendChild($f);
				$cdata = $doc->createCDATASection($data[ $r[ 'name' ] ]);
				$f->appendChild($cdata);
			}

			$value = $doc->saveXML();

			unset($doc, $root);

			if ( $formData[ 'cryptdata' ] )
			{
				$this->load('Crypt');
				$value = $this->Crypt->encrypt($value);
				file_put_contents(DATA_PATH . 'form-' . $data[ 'formid' ] . '-' . date('d-m-Y_h-i-s') . '.dcms.crypt', $value);
				$this->unload('Crypt');
			}
			else
			{
				file_put_contents(DATA_PATH . 'form-' . $data[ 'formid' ] . '-' . date('d-m-Y_h-i-s') . '.xml', $value);
			}
		}
		else
		{
			if ( IS_AJAX )
			{
				Library::sendJson(false, trans('Leider weiß ich nicht wohin mit Ihren Daten.'));
			}
			else
			{
				Error::sendError(trans('Leider weiß ich nicht wohin mit Ihren Daten.'));
			}
		}

		if ( IS_AJAX )
		{
			Library::sendJson(true, (!empty($formData[ 'submitmsg' ]) ? $formData[ 'submitmsg' ] :
				trans('Ihr Formular wurde erfolgreich verschickt.')));
		}
		else
		{
			$this->Template->process('forms/send', array (
			                                             'message' => (!empty($formData[ 'submitmsg' ]) ?
					                                             $formData[ 'submitmsg' ] :
					                                             trans('Ihr Formular wurde erfolgreich verschickt.'))
			                                       ), true);
		}
	}

}
