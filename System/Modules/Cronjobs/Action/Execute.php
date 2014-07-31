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
 * @package      
 * @version      3.0.0 Beta
 * @category     
 * @copyright    2008-2014 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Execute.php
 */

class Cronjobs_Action_Execute extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}
		$id = (int)$this->input('id');
		$task = $this->model->getCronJob($id);

		if (!$task['job_id'])
		{
			Library::sendJson(false, trans('Der Cronjob existiert nicht') );
		}


		if ( is_file(DATA_PATH . '.scheduler_running') ) {
			Library::sendJson(false, trans('Der Cronjob kann zur zeit nicht Ausgeführt werden, da ein Cronjob gerade seine Arbeit verrichtet.') );
		}

		$old_umask = umask( 0 );
		file_put_contents( DATA_PATH . '.scheduler_running', 'scheduler running...' );
		umask( $old_umask );

		$job = new Cronjob();
		$job->setType('cron');
		$job->runCronjob($id);

		if ( is_file( DATA_PATH . '.scheduler_running' ) )
		{
			unlink( DATA_PATH . '.scheduler_running' );
		}


		Library::sendJson(true, sprintf( trans('Der Cronjob "%s" wurde ausgeführt.'), $task['job_title']) );

	}
}
