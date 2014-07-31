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
 * @package      Cronjobs
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Edit.php
 */
class Cronjobs_Action_Edit extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}
		$id = (int)$this->input('id');

		$data = array ();
		if ( $id )
		{
			$data = $this->model->getCronJob($id);
		}


		if ( $this->_post('send') )
		{
			demoadm();

			$post = $this->_post();


			if ( empty($post[ 'job_title' ]) )
			{
				Library::sendJson(false, trans('Titel ist erforderlich'));
			}


			if ( empty($post[ 'job_filename' ]) )
			{
				Library::sendJson(false, trans('Cronjob Action ist erforderlich'));
			}


			$this->model->saveCronJob($id, $post);

			if ( $id )
			{
				Library::sendJson(true, sprintf(trans('Cronjob `%s` wurde aktualisiert'), $post[ 'job_title' ]));
			}
			else
			{
				Library::sendJson(true, sprintf(trans('Cronjob `%s` wurde angelegt'), $post[ 'job_title' ]));
			}
		}


		Library::addNavi(trans('Cron Jobs'));
		Library::addNavi(($data[ 'job_id' ] ? sprintf(trans('Cron Job `%s` bearbeiten'), $data[ 'job_title' ]) :
			trans('Cron Job erstellen')));
		$this->Template->process('cronjobs/edit', $data, true);
	}

}

?>