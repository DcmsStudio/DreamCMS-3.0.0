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
 * @package      Linkcheck
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Linkcheck_Action_Index extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}


		if ( HTTP::input('dostop') )
		{
			file_put_contents(CACHE_PATH . 'linkcheck.tmp', '1');
			Library::sendJson(true);
			exit;
		}


		/*
		 *
		 *
		  if (HTTP::input('getstate'))
		  {

		  $r = Model::getModelInstance()->getData();

		  $stop          = $r['stop'];
		  $checkUrl      = $r['currenturl'];
		  $checkUrlSleep = $r['sleep'];
		  echo Library::json(array('success'    => true, 'stop'       => ($stop ? true : false), 'sleep'      => $checkUrlSleep, 'currenturl' => $checkUrl));
		  exit;
		  }


		  Model::getModelInstance()->cleanData();

		 *
		 *
		 */


		if ( HTTP::input('getstate') )
		{

			$stop          = Session::get('LinkCheckStop', false);
			$checkUrl      = Session::get('LinkCheckURL', '');
			$checkUrlSleep = Session::get('LinkCheckSleep', false);
			echo Library::json(array (
			                         'success'    => true,
			                         'stop'       => ($stop ? true : false),
			                         'sleep'      => $checkUrlSleep,
			                         'currenturl' => $checkUrl
			                   ));
			exit;
		}

		$_SESSION[ 'LinkCheckStop' ] = false;


		Session::save('LinkCheckStop', false);
		Session::save('LinkCheckURL', '');

		$this->Template->process('linkchecker/index', array (), true);
	}

}

?>