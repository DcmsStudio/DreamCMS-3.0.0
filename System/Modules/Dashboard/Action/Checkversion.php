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
 * @package      Dashboard
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Checkversion.php
 */
class Dashboard_Action_Checkversion extends Controller_Abstract
{

	/**
	 * @var string
	 */
	protected static $mothership = 'http://www.dcms-studio.de/remote/';

	/**
	 * @return The
	 */
	private function getLastVersion ()
	{

		return Library::getRemoteFile(self::$mothership . 'version.xml');
	}

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$version = Cache::get('mothership_version');

		if ( is_null($version) || $version[ 'expires' ] < time() )
		{
			$xml     = $this->getLastVersion();
			$expires = time() + 1800;

			$data = array (
				'expires' => $expires,
				'xml'     => $xml
			);

			Cache::write('mothership_version', $data, 'data');
		}
		else
		{
			$xml = $version[ 'xml' ];

			if ( $version[ 'expires' ] < TIMESTAMP )
			{
				$xml     = $this->getLastVersion();
				$expires = time() + 1800;

				$data = array (
					'expires' => $expires,
					'xml'     => $xml
				);

				Cache::write('mothership_version', $data, 'data');
			}
		}

		if ( $xml === false )
		{
			Cache::delete('mothership_version');

			Session::save('version_check_output', '');
			Session::save('version_check_done', true);

			echo Library::json(array (
			                         'success' => true,
			                         'body'  => false
			                   ));
			die();
		}

		if ( stripos($xml, '<html') )
		{
			echo Library::json(array (
			                         'success' => true,
			                         'title'   => trans('Hoppla...'),
			                         'body'    => trans('Prüfen der Version zur Zeit nicht möglich. Bitte versuchen Sie es später erneut.')
			                   ));
			exit;
		}

		$xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

		$res = 0;
		if ( is_object($xml) )
		{
			$remote = (string)$xml->current;
			$res    = version_compare($remote, VERSION);
		}

		if ( $res > 0 )
		{
			$class = ( is_object($xml) && $xml->type != 'update' ? 'info' : 'warning' );
			$title = $class == 'info' ? trans("There's a new version (%s) of the DreamCMS available!") : trans("Critical DreamCMS update (%s) available!");
			$icon  = $class == 'info' ? 'info' : 'not-ok';

			if ( $class == 'info' )
			{
				$bootstrapClass = 'panel-info';
			}
			else
			{
				$bootstrapClass = 'panel-default';
			}

			$output = '<p>';
			$output .= $xml->message;
			$output .= '</p><br/><a href="' . $xml->download . '" class="action-button">' . trans('Download') . '</a>';

		}
		else
		{
			$output = '';
		}

		Session::save('version_check_output', $output);
		Session::save('version_check_done', true);


		echo Library::json(array (
		                         'success' => true,
		                         'title'   => sprintf($title, $remote),
		                         'icon'    => '<img src="' . BACKEND_IMAGE_PATH . $icon . '.png" width="16" height="16" alt="" />',
		                         'body'    => $output
		                   ));
		exit;
	}

}

?>