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
 * @package      Logs
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Showfull.php
 */
class Logs_Action_Showfull extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$id = (int)$this->input('id');

		$r = $this->db->query('SELECT * FROM %tp%logs WHERE id = ?', $id)->fetch();
		if ( $r[ 'data' ] )
		{
			$r[ 'data' ] = unserialize($r[ 'data' ]);
            $r[ 'backtrace' ] = unserialize($r[ 'backtrace' ]);


            $r[ 'backtrace' ][ 'args' ] = Debug::dump($r[ 'backtrace' ][ 'args' ], 500, 5);


			//if ($r['userid']) {
			// replace login password only for valid users
			if ( isset($r[ 'data' ][ 'request' ]['logpassword'])) {
				$r[ 'data' ][ 'request' ]['logpassword'] = str_repeat('*', strlen($r[ 'data' ][ 'request' ]['logpassword']) );
			}

			// replace document passwords
			if ( isset($r[ 'data' ][ 'request' ]['password'])) {
				$r[ 'data' ][ 'request' ]['password'] = str_repeat('*', strlen($r[ 'data' ][ 'request' ]['password']) );
			}
			//}


			if ( isset($r[ 'data' ][ 'log' ]) && is_array($r[ 'data' ][ 'log' ]) )
			{
				$r[ 'data' ][ 'log' ] = Library::prettyPrint($r[ 'data' ][ 'log' ]);
			}

			if ( isset($r[ 'data' ][ 'request' ]) && is_array($r[ 'data' ][ 'request' ]) )
			{
				$r[ 'data' ][ 'request' ] = Library::prettyPrint($r[ 'data' ][ 'request' ]);
			}
		}

		$rs = $this->db->query('SELECT c.* FROM %tp%countries AS c
								LEFT JOIN %tp%ip2nation AS n ON(n.countryid = c.countryid)
								WHERE n.ip < INET_ATON(?)
								ORDER BY n.ip DESC LIMIT 1', $r[ 'ip' ])->fetch();


		$r[ 'country' ] = $rs[ 'country' ];
		$r[ 'lat' ]     = $rs[ 'lat' ];
		$r[ 'lon' ]     = $rs[ 'lon' ];

		if ( $r[ 'ip' ] )
		{
			$hostname = $this->Env->nslookup($r[ 'ip' ]);
		}

		$hostname    = ($hostname ? $hostname : trans('unbekannt'));
		$r[ 'host' ] = $hostname;



		$rs = $this->db->query('SELECT spammer_ip FROM %tp%spammers WHERE spammer_ip = ?', $r[ 'ip' ])->fetch();

		$r[ 'allow_add_ip' ] = true;
		if ($rs['spammer_ip']) {
			$r[ 'allow_add_ip' ] = false;
		}
		$r[ 'date' ]    = date('d.m.Y, H:i:s', $r[ 'time' ]);
		$r[ 'success' ] = true;

		echo Library::json($r);
		exit();
	}

}
