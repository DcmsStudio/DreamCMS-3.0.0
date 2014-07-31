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
 * @package      Profile
 * @version      3.0.0 Beta
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Mysql.php
 */
class Profile_Model_Mysql extends Model
{

	/**
	 *
	 * @param array  $data
	 * @param string $validateMode
	 * @return array
	 */
	public function validate ( $data, $validateMode )
	{

		$rules = array ();

		switch ( $validate )
		{
			case 'settings':

				// MSN is only a email format
				if ( $data[ 'msn' ] != '' )
				{
					$rules[ 'msn' ][ 'email' ] = array (
						'message' => trans('MSN Adresse ist nicht korrekt. Es wird eine Email Adresse erwartet'),
						'stop'    => true
					);
				}
				break;

			case 'password':

				$rules[ 'securecode' ][ 'required' ]  = array (
					'message' => trans('Sicherheitscode ist erforderlich'),
					'stop'    => true
				);
				$rules[ 'securecode' ][ 'identical' ] = array (
					'message' => trans('Sicherheitscode ist fehlerhaft'),
					'stop'    => true,
					'test'    => Session::get('site_captcha')
				);

				$rules[ 'oldpassword' ][ 'required' ] = array (
					'message' => trans('das alte Passwort ist erforderlich'),
					'stop'    => true
				);

				$rules[ 'password' ][ 'required' ]   = array (
					'message' => trans('Passwort ist erforderlich'),
					'stop'    => true
				);
				$rules[ 'password' ][ 'min_length' ] = array (
					'message' => sprintf(trans('Dein Passwort muss mind. %s Zeichen lang sein'), Settings::get('minuserpasswordlength', 3)),
					'test'    => Settings::get('minuserpasswordlength', 3)
				);

				$rules[ 'passwordconfirm' ][ 'required' ] = array (
					'message' => trans('Bestätigungs Passwort ist erforderlich'),
					'stop'    => true
				);
				$rules[ 'password' ][ 'identical' ]       = array (
					'message' => trans('Passwörter sind nicht identisch'),
					'stop'    => true,
					'test'    => $data[ 'passwordconfirm' ]
				);

				// crypt old password
				$data[ 'oldpassword' ] = md5($data[ 'oldpassword' ]);

				$rules[ 'oldpassword' ][ 'identical' ] = array (
					'message' => trans('die eingabe des alten Passworts ist fehlerhaft'),
					'stop'    => true,
					'test'    => User::get('password')
				);


				break;
			case 'signatur':

				break;
			case 'avatar':
				// allowedavatarextensions

				break;
			case 'other':

				break;
		}


		$validator = new Validation($data, $rules);
		$errors    = $validator->validate();

		return $errors;
	}

	/**
	 *
	 * @param integer $userid
	 */
	public function updateProfileViews ( $userid )
	{

	}

	/**
	 *
	 * @param integer $userid
	 * @return array
	 */
	public function getUserById ( $userid )
	{

		return User::getUserById($userid);
	}

	/**
	 *
	 * @param string $username
	 * @return array
	 */
	public function getUserByUsername ( $username = '' )
	{

		return User::getUserByUsername($username);
	}

	/**
	 *
	 * @param integer $usergroup
	 * @param integer $posts  default is 0
	 * @param integer $gender default is 0
	 * @return array
	 */
	public function getRank ( $usergroup, $posts = 0, $gender = 0 )
	{

		$rank = $this->db->query('SELECT * FROM %tp%users_ranks WHERE groupid = ? AND needposts >= ? AND gender = ? ORDER BY needposts ASC LIMIT 1', $usergroup, $posts, $gender)->fetch();

		$rank[ 'rank_images' ] = User::getRankImage($rank);

		return $rank;
	}

}

?>