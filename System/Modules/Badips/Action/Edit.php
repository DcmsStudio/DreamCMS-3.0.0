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
 * @package      Badips
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Edit.php
 */
class Badips_Action_Edit extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		if ( $this->input('send') )
		{


			$data = array (
				'ip'    => $this->input('ip'),
				'email' => $this->input('email'),
			);


			$rs = $this->db->query('SELECT spammer_ip FROM %tp%spammers WHERE spammer_ip = ?', $data[ 'ip' ])->fetch();
			if ( $rs[ 'spammer_ip' ] )
			{
				Library::sendJson(false, sprintf(trans('Die IP `%s` existiert bereits in der Blacklist'), $rs[ 'spammer_ip' ]));
			}


			if ( !$this->input('long_ip') )
			{
				$data[ 'long_ip' ] = ip2long($data[ 'ip' ]);
			}

			$rs = $this->db->query('SELECT c.* FROM %tp%countries AS c
								LEFT JOIN %tp%ip2nation AS n ON(n.countryid = c.countryid)
								WHERE n.ip < INET_ATON(?)
								ORDER BY n.ip DESC LIMIT 1', $data[ 'ip' ])->fetch();

			$data[ 'countryid' ] = $rs[ 'countryid' ];

			$this->model->insert($data);

			Library::log('Add the IP ' . $data[ 'ip' ] . ' to the blacklist', 'warn');
			Library::sendJson(true, sprintf(trans('Die IP `%s` wurde zur Blacklist hinzugefügt'), $data[ 'ip' ]));
		}
	}

}
