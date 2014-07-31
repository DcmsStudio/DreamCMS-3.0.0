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
 * @package      Messenger
 * @version      3.0.0 Beta
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Mysql.php
 */
class Messenger_Model_Mysql extends Model
{

	/**
	 * @var bool
	 */
	static protected $system = false;

	/**
	 * @var
	 */
	static protected $user_ids;

	/**
	 * @var
	 */
	static protected $usernames;

	/**
	 * @var array
	 */
	static protected $data = array ();

	/**
	 * @var
	 */
	static protected $errors;

	/**
	 * @var array
	 */
	static protected $systemfolders = array (
		'1' => 'inbox',
		'2' => 'send',
		'3' => 'trash'
	);

	/**
	 * @param $params
	 * @return array
	 */
	public function getMessages ( $params )
	{

		$start = ($params[ 'page' ] - 1) * $params[ 'limit' ];
		$end   = $params[ 'limit' ];

		$where  = '';
		$userid = (!empty($params[ 'userid' ]) ? $params[ 'userid' ] : User::getUserId());

		if ( !empty($params[ 'q' ]) )
		{
			if ( $params[ 'qtype' ] == 'exact' )
			{
				$where = !empty($params[ 'q' ]) ?
					"WHERE `" . $params[ 'qfield' ] . "`= " . $this->db->quote($params[ 'q' ]) : '';
			}
			else
			{
				$where = !empty($params[ 'q' ]) ?
					"WHERE `" . $params[ 'qfield' ] . "` LIKE " . $this->db->quote('%' . $params[ 'q' ] . "%") : '';
			}
		}



        switch ($params[ 'order' ]) {
            case 'title':
                $params[ 'order' ] = 'm.title';
                break;
            case 'fromuser':
                $params[ 'order' ] = 'u.username';
                break;
            case 'date':
            default:
                $params[ 'order' ] = 'm.sendtime';
                break;

        }

        switch ( strtolower($params[ 'sort' ]) ) {
            case 'asc':
                $params[ 'sort' ] = 'ASC';
                break;

            case 'desc':
            default:
                $params[ 'sort' ] = 'DESC';
                break;
        }


        $where = !empty($where) ? $where . ' AND m.touser=? AND m.folder=?' : ' WHERE m.touser=? AND m.folder=?';


        $sql = "SELECT COUNT(id) AS total FROM %tp%messages WHERE touser = ? AND folder = ?";
		$rs  = $this->db->query($sql, $userid, $params[ 'folder' ])->fetch();

		if ( $params[ 'folder' ] == 2 )
		{
			$sql = "SELECT m.*, u.username
					FROM %tp%messages AS m
					LEFT JOIN %tp%users AS u ON (u.userid=m.fromuser)
					$where
					ORDER BY " . $params[ 'order' ] . " " . $params[ 'sort' ] . "
					LIMIT $start, $end";
		}
		else
		{
			$sql = "SELECT m.*, u.username
					FROM %tp%messages AS m
					LEFT JOIN %tp%users AS u ON (u.userid=m.fromuser)
					$where
					ORDER BY " . $params[ 'order' ] . " " . $params[ 'sort' ] . "
					LIMIT $start, $end";
		}

		return array (
			'total'  => $rs[ 'total' ],
			'result' => $this->db->query($sql, $userid, $params[ 'folder' ])->fetchAll()
		);
	}

	/**
	 * @param      $message
	 * @param bool $full
	 * @return mixed
	 */
	public function getUsernames ( &$message, $full = true )
	{

		$users = array ();


		$users = array_merge($users, explode(',', $message[ 'to_users' ]));
		$users = Library::unempty(array_unique($users));

		if ( count($users) > 0 )
		{
			$message[ 'to_users' ] = $this->db->query("SELECT userid, username, `name`, lastname FROM %tp%users WHERE userid IN (" . implode(',', $users) . ")")->fetchAll();
		}

		if ( $full )
		{
			$users    = array_merge((is_array($users) ? $users : array ()), explode(',', $message[ 'cc_users' ]));
			$users[ ] = $message[ 'fromuser' ];
		}

		$users = Library::unempty(array_unique($users));

		if ( count($users) > 0 )
		{
			$message[ 'cc_users' ] = $this->db->query("SELECT userid, username, `name`, lastname FROM %tp%users WHERE userid IN (" . implode(',', $users) . ")")->fetchAll();
		}

		if ( in_array(User::getUserId(), explode(',', $message[ 'bcc_users' ])) )
		{
			$message[ 'bcc_users' ] = User::getUsername();
		}
		else
		{
			$message[ 'bcc_users' ] = '';
		}

		return $message;
	}

	/**
	 * @return array
	 */
	public function getFolders ()
	{

		return $this->db->query("SELECT * FROM %tp%messages_folders WHERE userid=" . User::getUserId())->fetchAll();
	}

	/**
	 * @return array
	 */
	public function getCount ()
	{

		$sql = "SELECT folder, COUNT(id) AS counted FROM %tp%messages WHERE touser=" . User::getUserId() . " GROUP BY folder, id";

		return $this->db->query_first($sql);
	}

	/**
	 * @return array
	 */
	public function getUserFolderCount ()
	{

		$sql  = "SELECT folder, COUNT(id) AS counted FROM %tp%messages WHERE touser=" . User::getUserId() . " GROUP BY folder, id";
		$data = $this->db->query($sql)->fetchAll();

		$folders                    = array ();
		$folders[ 'totalmessages' ] = 0;
		foreach ( $data as $row )
		{
			$folders[ $row[ 'folder' ] ] += $row[ 'counted' ];
			$folders[ 'totalmessages' ] += $row[ 'counted' ];
		}

		return $folders;
	}

	/**
	 * @param      $message
	 * @param bool $system
	 * @return array|bool
	 */
	public function send ( $message, $system = false )
	{

		self::$system    = $system;
		self::$user_ids  = array_unique($this->getUserIds($message));
		self::$usernames = array_flip(self::$user_ids);

		self::$data[ 'm_from' ] = empty($message[ 'm_from' ]) ? User::getUserId() : $message[ 'fromuser' ];
		self::$data[ 'm_to' ]   = $this->parseUsernames($message[ 'm_to' ], 'to'); // String
		self::$data[ 'm_cc' ]   = $this->parseUsernames($message[ 'm_cc' ], 'cc');
		self::$data[ 'm_bcc' ]  = $this->parseUsernames($message[ 'm_bcc' ], 'bcc');

		if ( empty(self::$data[ 'm_to' ]) )
		{
			if ( defined('ADM_SCRIPT') )
			{
				self::$errors[ ] = trans('Please give the message a subject');
			}
			else
			{
				self::$errors[ 'to' ][ ] = trans('Please select at least one user to send the message to');
			}
		}

		if ( !empty($message[ 'm_subject' ]) )
		{
			self::$data[ 'm_subject' ] = $message[ 'm_subject' ];
		}
		else
		{

			if ( defined('ADM_SCRIPT') )
			{
				self::$errors[ ] = trans('Please give the message a subject');
			}
			else
			{
				self::$errors[ 'subject' ][ ] = trans('Please give the message a subject');
			}
		}

		if ( !empty($message[ 'm_body' ]) )
		{
			self::$data[ 'm_body' ] = $message[ 'm_body' ];
		}
		else
		{

			if ( defined('ADM_SCRIPT') )
			{
				self::$errors[ ] = trans('Please enter a message');
			}
			else
			{
				self::$errors[ 'body' ][ ] = trans('Please enter a message');
			}
		}

		self::$data[ 'rcpt' ] = isset($message[ 'rcpt' ]) && $message[ 'rcpt' ] == 1;
		self::$data[ 'copy' ] = isset($message[ 'copy' ]) && $message[ 'copy' ] == 1;
		self::$data[ 'prio' ] = isset($message[ 'prio' ]) && $message[ 'prio' ] == 1;


		if ( !empty(self::$errors) && !self::$system )
		{
			return self::$errors;
		}

		//self::$db->begin();

		$recipients = array_unique(array_merge(self::$data[ 'm_to' ], self::$data[ 'm_cc' ], self::$data[ 'm_bcc' ]));

		foreach ( $recipients as $m_owner )
		{
			$this->insertMessage($m_owner, 1);
		}

		if ( self::$data[ 'copy' ] )
		{
			$this->insertMessage(User::getUserId(), 2);
		}

		if ( !empty(self::$errors) )
		{
			//self::$db->rollback();
			return self::$errors;
		}
		else
		{
			//self::$db->commit();
			return true;
		}
	}

	/**
	 * @param $data
	 * @return array
	 */
	private function getUserIds ( $data )
	{

		$usernames = array (
			$data[ 'm_to' ],
			$data[ 'm_cc' ],
			$data[ 'm_bcc' ]
		);
		$usernames = implode(',', $usernames);
		$usernames = array_map('trim', Library::unempty(explode(',', $usernames)));

		foreach ( $usernames as $key => $username )
		{
			$usernames[ $key ] = $this->db->quote($username);
		}

		$usernames = implode(',', $usernames);
		if ( Library::length($usernames) == 0 )
		{
			return array ();
		}

		$users    = $this->db->query("SELECT userid, username FROM %tp%users WHERE username IN (" . $usernames . ")")->fetchAll();
		$user_ids = array ();
		foreach ( $users as $user )
		{
			$user_ids[ $user[ 'username' ] ] = $user[ 'userid' ];
		}

		return $user_ids;
	}

	/**
	 * @param        $usernames
	 * @param string $type
	 * @return array
	 */
	private function parseUsernames ( $usernames, $type = 'to' )
	{

		$usernames = array_map('trim', Library::unempty(explode(',', $usernames)));
		$user_ids  = array ();
		foreach ( $usernames as $username )
		{
			if ( isset(self::$user_ids[ $username ]) )
			{
				$user_ids[ ] = self::$user_ids[ $username ];
			}
			else
			{
				self::$errors[ $type ][ ] = sprintf(trans('Der Benutzer `%s` existiert nicht'), $username);
			}
		}

		return $user_ids;
	}

	/**
	 * @param     $m_owner
	 * @param int $m_folder
	 */
	private function insertMessage ( $m_owner, $m_folder = 1 )
	{

		$max = 150; //(int)Settings::get('max_messages', 0);

		if ( $max != 0 && self::$system == false )
		{
			$row = $this->db->query("SELECT COUNT(*) AS counted FROM %tp%messages WHERE touser=?", $m_owner)->fetch();
			if ( $m_folder == 1 && $row[ 'counted' ] >= $max )
			{
				self::$errors[ 'general' ][ ] = sprintf(trans('%s\'s messenger is full. The message has not been sent.'), self::$usernames[ $m_owner ]);

				return;
			}
			if ( $m_folder == 2 && $row[ 'counted' ] >= $max )
			{
				self::$errors[ 'general' ][ ] = sprintf(trans('Your messenger is full and the message cannot be saved in your `sent` folder. The message has not been sent.'));

				return;
			}
		}

		$time = time();

		$sql = "
                fromuser = " . self::$data[ 'm_from' ] . ",
                pageid = " . PAGEID . ",
                touser = " . $m_owner . ",
                to_users = " . $this->db->quote(implode(',', self::$data[ 'm_to' ])) . ",
                cc_users = " . $this->db->quote(implode(',', self::$data[ 'm_cc' ])) . ",
                bcc_users = " . $this->db->quote(implode(',', self::$data[ 'm_bcc' ])) . ",
                sendtime = " . $time . ",
                receipt = " . (self::$data[ 'rcpt' ] ? 1 : 0) . ",
                important = " . (self::$data[ 'prio' ] ? self::$data[ 'prio' ] : 0) . ",
                folder = " . $m_folder . ",
                title = " . $this->db->quote(self::$data[ 'm_subject' ]) . ",
                message = " . $this->db->quote(self::$data[ 'm_body' ]) . "
		";

		$this->db->query("INSERT INTO %tp%messages SET " . $sql);

		if ( $m_folder == 2 )
		{
			$id = $this->db->insert_id();
			$this->db->query("UPDATE %tp%messages SET readtime = " . $time . " WHERE id=" . $id);
		}
	}

	/**
	 * @param      $id
	 * @param bool $user_id
	 * @return type
	 */
	public function getAndReadMessage ( $id, $user_id = false )
	{

		$message = $this->getMessage($id, $user_id);
		if ( isset($message[ 'id' ]) && !$message[ 'readtime' ] )
		{
			$this->readMessage($id, $user_id, true);
		}

		return $message;
	}

	/**
	 * @param      $id
	 * @param bool $user_id
	 * @return type
	 */
	public function getMessage ( $id, $user_id = false )
	{

		$user_id = $user_id !== false ? $user_id : User::getUserId();
		$sql     = "SELECT m.*, u.username, u2.username AS tousername, u.`name` AS firstname, u.lastname FROM %tp%messages AS m
				LEFT JOIN %tp%users AS u ON(u.userid=m.fromuser) 
                                LEFT JOIN %tp%users AS u2 ON(u2.userid=m.touser)
				WHERE m.id=" . $id . " AND m.touser=" . $user_id;

		return $this->db->query($sql)->fetch();
	}

	/**
	 * @param int $limit
	 * @return array
	 */
	public function getWidMessages ( $limit = 10 )
	{

		$user_id = User::getUserId();
		$sql     = "SELECT m.*, u.username, u.`name` AS firstname, u.lastname FROM %tp%messages AS m
				LEFT JOIN %tp%users AS u ON(u.userid=m.fromuser)
				WHERE m.touser=" . $user_id . "
				ORDER BY m.sendtime DESC
				LIMIT " . (int)$limit . "";

		return $this->db->query($sql)->fetchAll();
	}

	/**
	 * @param      $id
	 * @param bool $user_id
	 * @param bool $read
	 */
	public function readMessage ( $id, $user_id = false, $read = true )
	{

		$user_id = $user_id !== false ? $user_id : User::getUserId();

		if ( $read )
		{
			$this->db->query("UPDATE %tp%messages SET readtime=" . time() . " WHERE touser= " . $user_id . " AND id=" . $id);
		}
		else
		{
			$this->db->query("UPDATE %tp%messages SET readtime=0 WHERE touser= " . $user_id . " AND id=" . $id);
		}
	}

	/**
	 * @param        $id
	 * @param string $type
	 * @return array
	 */
	public function prepareMessage ( $id, $type = 'reply' )
	{

		$message = $this->getMessage($id);

		$data = array ();
		switch ( $type )
		{
			case 'reply' :
				$ref = array (
					'to_users' => $message[ 'fromuser' ]
				);
				$this->getUsernames($ref, false);
				$data[ 'send_to' ] = $ref[ 'to_users' ][ 0 ][ 'username' ];
				$data[ 'send_cc' ] = '';

				$prefix = trans('Re') . ': ';
				if ( strpos($message[ 'title' ], $prefix) === 0 )
				{
					$data[ 'send_subject' ] = $message[ 'title' ];
				}
				else
				{
					$data[ 'send_subject' ] = $prefix . $message[ 'title' ];
				}
				break;
			case 'replyall' :
				//
				$to  = $message[ 'fromuser' ] . ',' . $message[ 'to_users' ];
				$ref = array (
					'to_users' => $to
				);
				$this->getUsernames($ref, false);
				$dat = array ();
				if ( is_array($ref[ 'to_users' ]) )
				{
					foreach ( $ref[ 'to_users' ] as $idx => $r )
					{
						$dat[ ] = $r[ 'username' ];
					}
				}

				$data[ 'send_to' ] = implode(', ', $dat);

				//
				$ref = array (
					'to_users' => $message[ 'cc_users' ]
				);
				$this->getUsernames($ref, false);

				$dat = array ();
				if ( is_array($ref[ 'to_users' ]) )
				{
					foreach ( $ref[ 'to_users' ] as $idx => $r )
					{
						$dat[ ] = $r[ 'username' ];
					}
				}
				$data[ 'send_cc' ] = implode(', ', $dat);


				$prefix = trans('Re') . ': ';
				if ( strpos($message[ 'title' ], $prefix) === 0 )
				{
					$data[ 'send_subject' ] = $message[ 'title' ];
				}
				else
				{
					$data[ 'send_subject' ] = $prefix . $message[ 'title' ];
				}
				break;
			case 'forward' :
				$data[ 'send_to' ] = '';
				$data[ 'send_cc' ] = '';
				$prefix            = trans('Fwd') . ': ';
				if ( strpos($message[ 'title' ], $prefix) === 0 )
				{
					$data[ 'send_subject' ] = $message[ 'title' ];
				}
				else
				{
					$data[ 'send_subject' ] = $prefix . $message[ 'title' ];
				}
				break;
		}


		$ref = array (
			'to_users' => $message[ 'fromuser' ]
		);
		$this->getUsernames($ref, false);

		$data[ 'send_message' ] = "\n\n\n==================\n" . trans('Von') . ": " . $ref[ 'to_users' ][ 0 ][ 'username' ] . "\n" . trans('Datum') . ": " . date('d.m.Y, H:i:s', $message[ 'sendtime' ]) . "\n" . trans('Betreff') . ": " . $message[ 'title' ] . "\n\n" . $message[ 'message' ];

		return $data;
	}

}

?>