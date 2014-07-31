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
 * @file         Logout.php
 */
class Auth_Action_Logout extends Controller_Abstract
{

	public function execute ()
	{


		Hook::run('onBeforeDashboardLogout'); // {CONTEXT: dashboard, DESC: Dieses Ereignis wird vor dem Logout ausgelöst.}

		Cookie::delete('ToolbarTabs');
		Cookie::delete('seemodePopup');

		if ( User::isLoggedIn() )
		{
			$usename = User::getUsername();

			Cookie::destroy();
			Session::destroy();
			Session::regenerate_id();
			Csrf::cleanCSRF('token');

			Cookie::delete('seemodePopup');
			Cookie::delete(session_name());

            if (!$this->isBackend()) {
                Session::delete( 'seemode' );
            }




			Library::log(sprintf("User %s logged out successfully.", $username));
		}
		else
		{
			Cookie::destroy();

			Session::destroy();
			Session::regenerate_id();
			Csrf::cleanCSRF('token');


            if (!$this->isBackend()) {
                Session::delete( 'seemode' );
            }

			Cookie::delete('seemodePopup');
			Cookie::delete(session_name());

			Library::log('A user tried to log out, but was not logged in.', 'warning');
		}

		ob_get_clean();



		Cookie::delete('seemodePopup');
		Cookie::delete(session_name());
		Csrf::cleanCSRF('token');

		$_SESSION = array();
		Session::write();



		Hook::run('onAfterDashboardLogout'); // {CONTEXT: dashboard, DESC: Dieses Ereignis wird nach der dem Logout ausgelöst.}

		if ( !IS_AJAX )
		{
			if ( $this->getApplication()->getMode() === Application::BACKEND_MODE )
			{
				header("Location: admin.php");
			}
			else
			{
				Library::redirect(Settings::get('portalurl'));
			}
		}
		else
		{
			Library::sendJson(true);
		}

		exit();
	}

}
