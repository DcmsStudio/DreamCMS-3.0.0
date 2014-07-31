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
 * @package      Auth
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Login.php
 */
class Auth_Action_Login extends Controller_Abstract
{

	public function execute ()
	{

		if ( User::isLoggedIn() && Session::get('userid') > 0 )
		{
			if ( IS_AJAX )
			{
				Library::sendJson(false, trans('Sie sind bereits eingeloggt!'));
			}
			else
			{
				$this->load('Page');
				$this->Page->sendError(trans('Sie sind bereits eingeloggt!'));
			}
		}

		if ( $this->getApplication()->getMode() === Application::BACKEND_MODE )
		{
			Hook::run("onBeforeDashboardLogin"); // {CONTEXT: dashboard, DESC: Dieses Ereignis wird vor der dem Login ausgelöst.}


            if (Library::isBlacklistedUsername(HTTP::post('logusername', Input::STRING))) {
                Library::log('Blacklisted Username will login in the Backend!', 'warn');
                Library::sendJson(false, trans('Blacklisted Username!'));

                exit;
            }



			if ( User::login(HTTP::post('logusername', Input::STRING), HTTP::post('logpassword', Input::STRING)) )
			{
				//  $r = $this->db->query( 'SELECT * FROM %tp%page WHERE `type` = ? AND id = ?', 'rootpage', (int)HTTP::input( 'setpage', Input::INTEGER  ) )->fetch();


				Session::save('WEBSITE_ID', (int)HTTP::input('setpage', Input::INTEGER));
				//    Session::save( 'WEBSITE_DATA', $r );

				if ( HTTP::input('permanet') )
				{
					Cookie::set('loginpermanet', User::getUserUiqKey(), $GLOBALS[ 'SESSIONTIMEOUT' ] * 50);
				}
				else
				{
					Cookie::delete('loginpermanet');
				}
				Cookie::set('uhash', User::getUserUiqKey(), $GLOBALS[ 'SESSIONTIMEOUT' ]);
				// Cache des Users löschen und anschießend neu aufbauen (backend)
				Cache::delete('menu_user_' . User::getUserId());

				Hook::run("onAfterDashboardLogin"); // {CONTEXT: dashboard, DESC: Dieses Ereignis wird nach der dem Login ausgelöst.}


				$data = $this->loadBackendData();

				Ajax::Send(true,$data );
				exit;


				Library::sendJson(true, $data);
			}
			else
			{
				Hook::run('onDashboardLoginFail'); // {CONTEXT: dashboard, DESC: Dieses Ereignis wird ausgelöst, wenn der Login fehlerhaft war.}

				Library::log(sprintf("`%s` attempted to log in, but failed", HTTP::input('logusername', Input::STRING)), 'critical');

				Ajax::Send(false, array (
				                        'msg' => sprintf(trans("Could not log you in for one of the following reasons:
                            <ul>
                            <li>the username is incorrect;</li>
                            <li>the password is incorrect;</li>
                            <li>the account has not been activated;</li>
                            <li>the account has been blocked.</li>
                            </ul>"), HTTP::input('logusername'))
				                  ));
			}


			exit;
		}
		else
		{


			Hook::run("onBeforeDashboardLogin"); // {CONTEXT: dashboard, DESC: Dieses Ereignis wird vor der dem Login ausgelöst.}
			// $password = md5(HTTP::post('logpassword', Input::STRING));


            if (Library::isBlacklistedUsername(HTTP::post('logusername', Input::STRING))) {
                Library::log('Blacklisted Username will login', 'warn');
                Library::sendJson(false, trans('Blacklisted Username!'));

                exit;
            }

			if ( User::login(HTTP::post('logusername', Input::STRING), HTTP::post('logpassword', Input::STRING)) )
			{
				//    $r = $this->db->query( 'SELECT * FROM %tp%page WHERE `type` = ? AND id = ?', 'rootpage', (int)HTTP::input( 'setpage', Input::INTEGER  ) )->fetch();


				Session::save('WEBSITE_ID', (int)HTTP::input('setpage', Input::INTEGER));
				//  Session::save( 'WEBSITE_DATA', $r );

				if ( HTTP::input('permanet') )
				{
					Cookie::set('loginpermanet', User::getUserUiqKey(), $GLOBALS[ 'SESSIONTIMEOUT' ] * 50);
				}
				else
				{
					Cookie::delete('loginpermanet');
				}
				Cookie::set('uhash', User::getUserUiqKey(), $GLOBALS[ 'SESSIONTIMEOUT' ]);
				// Cache des Users löschen und anschießend neu aufbauen (backend)
				Cache::delete('menu_user_' . User::getUserId());

				Hook::run("onAfterDashboardLogin"); // {CONTEXT: dashboard, DESC: Dieses Ereignis wird nach der dem Login ausgelöst.}

				if ( IS_AJAX )
				{
					Library::sendJson(true);
					exit;
				}
				else
				{
					Library::redirect(Settings::get('portalurl'));
					exit;
				}
			}
			else
			{
				Hook::run('onDashboardLoginFail'); // {CONTEXT: dashboard, DESC: Dieses Ereignis wird ausgelöst, wenn der Login fehlerhaft war.}

				Library::log(sprintf("`%s` attempted to log in, but failed", HTTP::input('logusername', Input::STRING)), 'critical');
				if ( IS_AJAX )
				{
					Ajax::Send(false, array (
					                        'msg' => sprintf(trans("Could not log you in for one of the following reasons:
                            <ul>
                            <li>the username is incorrect;</li>
                            <li>the password is incorrect;</li>
                            <li>the account has not been activated;</li>
                            <li>the account has been blocked.</li>
                            </ul>"), HTTP::input('logusername'))
					                  ));

					exit;
				}
				else
				{
					$this->load('Page');
					$this->Page->sendError(sprintf("`%s` attempted to log in, but failed", HTTP::input('logusername', Input::STRING)));

					exit;
				}
			}
		}
	}


	protected function loadBackendData() {

		$ct = new Dashboard_Action_Contenttree();
		$treeContent = $ct->getTreeData();

		$wgt = new Widgets_Helper_Base();
		$widgets = $wgt->setWidgetSession();

		$dashboard = new Dashboard_Action_Index();
		$config = $dashboard->getBasicConfig();
		$tinymce = $dashboard->getTinyMCEConfig();


		$data = array(
			'modules' => $treeContent,
			'widgets' => $widgets,
			'tinymce' => $tinymce['tinymce']
		);




		$res = Model::getModelInstance('logs')->getLogs(40, true);
		foreach ( $res[ 'result' ] as &$r )
		{
			$r[ 'time' ] = Locales::formatDateTime($r[ 'time' ]);
		}

		$data['logs'] = $res[ 'result' ];


		$data = array_merge($data, $config);

		return $data;
	}
}
