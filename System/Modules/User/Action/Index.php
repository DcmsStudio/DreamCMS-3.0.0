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
 * @package      User
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class User_Action_Index extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{

			return;
		}
		else
		{

			$model = Model::getModelInstance('user');
			Library::addNavi(trans('Benutzer Übersicht'));


			$sql    = "SELECT groupid, title FROM %tp%users_groups ORDER BY title ASC";
			$result = $this->db->query($sql)->fetchAll();

			$groups      = array ();
			$groups[ 0 ] = '-------------------------';

			foreach ( $result as $rs )
			{
				$groups[ $rs[ 'groupid' ] ] = $rs[ 'title' ];
			}

			$allgroups = $groups;


			$blocked = array (
				''  => '-------------------------',
				'2' => 'nicht gesperrte Benutzer',
				'1' => 'gesperrte Benutzer'
			);


			$this->load('Grid');
			$this->Grid->initGrid('users', 'userid', 'username', 'desc');
			$this->Grid->addFilter(array (
			                             array (
				                             'name'  => 'q',
				                             'type'  => 'input',
				                             'value' => '',
				                             'label' => trans('Suche nach Benutzer'),
				                             'show'  => true,
				                             'parms' => array (
					                             'size' => '40'
				                             )
			                             ),
			                             array (
				                             'name'   => 'groupid',
				                             'type'   => 'select',
				                             'select' => $allgroups,
				                             'label'  => trans('Benutzergruppe'),
				                             'show'   => false
			                             ),
			                             array (
				                             'name'   => 'blocked',
				                             'type'   => 'select',
				                             'select' => $blocked,
				                             'label'  => trans('Status'),
				                             'show'   => false
			                             ),
			                             array (
				                             'wrap' => true
			                             ),
			                             array (
				                             'name'  => 'email',
				                             'type'  => 'input',
				                             'value' => '',
				                             'label' => trans('Email'),
				                             'show'  => false,
				                             'parms' => array (
					                             'size' => '20'
				                             )
			                             ),
			                             array (
				                             'name'  => 'msn',
				                             'type'  => 'input',
				                             'value' => '',
				                             'label' => trans('MSN'),
				                             'show'  => false,
				                             'parms' => array (
					                             'size' => '20'
				                             )
			                             ),
			                             array (
				                             'name'  => 'icq',
				                             'type'  => 'input',
				                             'value' => '',
				                             'label' => trans('ICQ'),
				                             'show'  => false,
				                             'parms' => array (
					                             'size' => '20'
				                             )
			                             ),
			                             array (
				                             'name'  => 'yim',
				                             'type'  => 'input',
				                             'value' => '',
				                             'label' => trans('YIM'),
				                             'show'  => false,
				                             'parms' => array (
					                             'size' => '20'
				                             )
			                             ),
			                             array (
				                             'wrap' => true
			                             ),
			                             array (
				                             'name'  => 'userposts_morethen',
				                             'type'  => 'input',
				                             'value' => '',
				                             'label' => trans('mehr als X Posts'),
				                             'show'  => false,
				                             'parms' => array (
					                             'size' => '6'
				                             )
			                             ),
			                             array (
				                             'name'  => 'userposts_lessthen',
				                             'type'  => 'input',
				                             'value' => '',
				                             'label' => trans('weniger als X Posts'),
				                             'show'  => false,
				                             'parms' => array (
					                             'size' => '6'
				                             )
			                             ),
			                       ));

			$this->Grid->addHeader(array (
			                             // sql feld						 header	 	  sortieren		             standart
			                             array (
				                             "field"   => "userid",
				                             "content" => trans('ID'),
				                             'width'   => '4%',
				                             "default" => true
			                             ),
			                             array (
				                             'islabel' => true,
				                             "field"   => "username",
				                             "content" => trans('Username'),
				                             "sort"    => "username",
				                             "default" => true
			                             ),
			                             array (
				                             "field"   => "email",
				                             "content" => trans('Email'),
				                             "sort"    => "email",
				                             "default" => false
			                             ),
			                             array (
				                             "field"   => "grouptitle",
				                             "content" => trans('Gruppe'),
				                             'width'   => '15%',
				                             "sort"    => "usergroup",
				                             "default" => true,
				                             'nowrap'  => true
			                             ),
			                             array (
				                             "field"   => "userposts",
				                             "content" => trans('Posts'),
				                             'width'   => '6%',
				                             "sort"    => "userposts",
				                             "default" => false,
				                             'align'   => 'tc'
			                             ),
			                             array (
				                             "field"   => "regdate",
				                             "content" => trans('Registriert seit'),
				                             'width'   => '12%',
				                             "sort"    => "regdate",
				                             "default" => false,
				                             'align'   => 'tl',
				                             'nowrap'  => true
			                             ),
			                             array (
				                             "field"   => "lastactivity",
				                             "content" => trans('zuletzt Online'),
				                             'width'   => '12%',
				                             "sort"    => "lastactivity",
				                             "default" => true,
				                             'align'   => 'tl',
				                             'nowrap'  => true
			                             ),
			                             array (
				                             "field"   => "options",
				                             "content" => trans('Optionen'),
				                             "default" => true,
				                             'align'   => 'tc',
				                             'width'   => '12%',
				                             'nowrap'  => true
			                             ),
			                       ));


			$this->Grid->addActions(array (
			                              'email'      => array (
				                              trans('eMail senden'),
				                              true
			                              ),
			                              "access"     => array (
				                              trans('Zugriffsrechte setzen'),
				                              true
			                              ),
			                              "activate"   => trans('Benutzer freischalten'),
			                              "blocking"   => trans('Benutzer sperren'),
			                              "unblocking" => trans('Benutzer sperre aufheben'),
			                              "delete"     => array (
				                              'label' => trans('Benutzer löschen'),
				                              'msg'   => trans('Ausgewählte Benutzer werden gelöscht. Wollen Sie fortsetzen?')
			                              )
			                        ));


			if ( HTTP::input('page') )
			{
				$page = (int)HTTP::input('page');
				if ( $page < 1 )
				{
					$page = 1;
				}
			}
			else
			{
				$page = 1;
			}

			$limit   = $this->getPerpage(); // oder $GLOBALS['perpage']
			$add_url = '';

			$search = '';
			if ( HTTP::input('q') && HTTP::input('q') )
			{
				$search = HTTP::input('q');
			}


			if ( HTTP::input('username') && HTTP::input('username') || $search )
			{
				$model->add2where("u.username LIKE " . $this->db->quote('%' . htmlspecialchars(($search ? $search :
							HTTP::input('username'))) . '%'), 'OR');
				$add_url .= '-&-username=' . htmlspecialchars(($search ? $search : HTTP::input('username')));
			}

			if ( HTTP::input('email') && HTTP::input('email') )
			{
				$model->add2where("u.email LIKE " . $this->db->quote('%' . htmlspecialchars(HTTP::input('email')) . '%'));
				$add_url .= '-&-email=' . htmlspecialchars(HTTP::input('email'));
			}


			if ( HTTP::input('groupid') && (int)HTTP::input('groupid') )
			{
				$model->add2where("u.groupid = " . (int)HTTP::input('groupid') . "");
				$add_url .= '-&-groupid=' . HTTP::input('groupid');
			}


			if ( HTTP::input('rankid') && HTTP::input('rankid') )
			{
				$model->add2where("u.rankid = " . (int)HTTP::input('rankid') . "");
				$add_url .= '-&-rankid=' . HTTP::input('rankid');
			}


			if ( HTTP::input('title') && HTTP::input('title') || $search )
			{
				$model->add2where("u.user_title LIKE " . $this->db->quote('%' . htmlspecialchars(($search ? $search :
							HTTP::input('title'))) . '%'), 'OR');
				$add_url .= '-&-title=' . htmlspecialchars(($search ? $search : HTTP::input('title')));
			}


			if ( HTTP::input('usertext') && HTTP::input('usertext') || $search )
			{
				$model->add2where("u.usertext LIKE " . $this->db->quote('%' . htmlspecialchars(($search ? $search :
							HTTP::input('usertext'))) . '%'), 'OR');
				$add_url .= '-&-usertext=' . htmlspecialchars(($search ? $search : HTTP::input('usertext')));
			}


			if ( HTTP::input('signature') && HTTP::input('signature') || $search )
			{
				$model->add2where("u.signature LIKE " . $this->db->quote('%' . ($search ? $search :
							HTTP::input('signature')) . '%'), 'OR');
				$add_url .= '-&-signature=' . htmlspecialchars(($search ? $search : HTTP::input('signature')));
			}


			if ( HTTP::input('homepage') && HTTP::input('homepage') )
			{
				$model->add2where("u.homepage LIKE " . $this->db->quote('%' . htmlspecialchars(HTTP::input('homepage')) . '%'));
				$add_url .= '-&-homepage=' . htmlspecialchars(HTTP::input('homepage'));
			}


			if ( HTTP::input('icq') && HTTP::input('icq') )
			{
				$model->add2where("u.icq = '" . (int)HTTP::input('icq') . "'");
				$add_url .= '-&-icq=' . htmlspecialchars(HTTP::input('icq'));
			}

			if ( HTTP::input('yim') && HTTP::input('yim') )
			{
				$model->add2where("u.yim LIKE " . $this->db->quote('%' . htmlspecialchars(HTTP::input('yim')) . '%'));
				$add_url .= '-&-yim=' . htmlspecialchars(HTTP::input('yim'));
			}

			if ( HTTP::input('msn') && HTTP::input('msn') )
			{
				$model->add2where("u.msn LIKE " . $this->db->quote('%' . htmlspecialchars(HTTP::input('msn')) . '%'));
				$add_url .= '-&-msn=' . htmlspecialchars(HTTP::input('msn'));
			}

			if ( HTTP::input('userposts_morethen') && HTTP::input('userposts_morethen') )
			{
				$model->add2where("u.userposts > " . (int)HTTP::input('userposts_morethen'));
				$add_url .= '-&-userposts_morethen=' . HTTP::input('userposts_morethen');
			}

			if ( HTTP::input('userposts_lessthen') && HTTP::input('userposts_lessthen') )
			{
				$model->add2where("u.userposts < " . (int)HTTP::input('userposts_lessthen'));
				$add_url .= '-&-userposts_lessthen=' . HTTP::input('userposts_lessthen');
			}


			if ( HTTP::input('lastactivity_in') && HTTP::input('lastactivity_in') )
			{
				$model->add2where("u.lastactivity >= " . (time() - (int)HTTP::input('lastactivity_in') * 3600) . "");
				$add_url .= '-&-lastactivity_in=' . HTTP::input('lastactivity_in');
			}


			if ( HTTP::input('lastactivity_notin') && HTTP::input('lastactivity_notin') )
			{
				$model->add2where("u.lastactivity < " . (time() - (int)HTTP::input('lastactivity_notin') * 3600) . "");
				$add_url .= '-&-lastactivity_notin=' . HTTP::input('lastactivity_notin');
			}


			if ( HTTP::input('activation') && HTTP::input('activation') == 99 )
			{
				$model->add2where("u.activation > 10");
				$add_url .= '-&-activation=-1';
			}
			elseif ( HTTP::input('activation') && HTTP::input('activation') == 1 )
			{
				$model->add2where("u.activation = 1");
				$add_url .= '-&-activation=' . HTTP::input('activation');
			}


			if ( HTTP::input('blocked') && HTTP::input('blocked') == 1 )
			{
				$model->add2where("u.blocked = 1");
				$add_url .= '-&-blocked=1';
			}
			elseif ( HTTP::input('blocked', 'isset') && HTTP::input('blocked') == 2 )
			{
				$model->add2where("u.blocked = 0");
				$add_url .= '-&-blocked=2';
			}


			switch ( HTTP::input('sort') )
			{
				case "desc":
					$sortorder = "DESC";
					break;
				case "asc":
				default:
					$sortorder = "ASC";
					break;
			}

			switch ( HTTP::input('orderby') )
			{
				case "username":
					$sortby = "u.username";
					break;
				case "email":
					$sortby = "u.email";
					break;
				case "regdate":
					$sortby = "u.regdate";
					$model->add2where("activation >= 1");
					break;
				case "lastactivity":
					$sortby = "u.lastactivity";
					break;
				case "userposts":
					$sortby = "u.userposts";
					break;
				case "usergroup":
					$sortby = "grouptitle";
					break;
				default:
					$sortby = "u.username";
					break;
			}

			if ( HTTP::input('group') == 1 )
			{
				$group = 1;

				if ( HTTP::input('is_admin') )
				{
					$model->add2where("g.canuseacp=1");
				}

				if ( HTTP::input('is_smod') )
				{
					$model->add2where("g.issupermod=1 AND g.canuseacp=0");
				}

				if ( HTTP::input('is_mod') )
				{
					$model->add2where("g.ismod=1 AND g.issupermod=0 AND g.canuseacp=0");
				}
			}


			$sql   = "SELECT COUNT(u.userid) AS total FROM %tp%users AS u " . ($group == 1 ?
					"LEFT JOIN %tp%users_groups AS g  ON(u.groupid=g.groupid)" : '') . ($model->getWhere() != '' ?
					" WHERE " . $model->getWhere() : '');
			$found = $this->db->query_first($sql);


			$sql    = "SELECT u.*, g.title AS grouptitle
				FROM %tp%users AS u
				LEFT JOIN %tp%users_groups AS g ON(u.groupid = g.groupid)" . ($model->getWhere() != '' ?
					" WHERE " . $model->getWhere() :
					'') . " ORDER BY " . $sortby . " " . $sortorder . " LIMIT " . ($limit * ($page - 1)) . ", " . $limit;
			$result = $this->db->query($sql)->fetchAll();

			$im = BACKEND_IMAGE_PATH;
			foreach ( $result as $rs )
			{

				$rs[ 'lastactivity' ] = Locales::formatDateTime( ($rs[ 'lastactivity' ] > $rs[ 'regdate' ] ? $rs[ 'lastactivity' ] : $rs[ 'regdate' ]) );
				$rs[ 'regdate' ]      = Locales::formatDateTime($rs[ 'regdate' ]);

				$e      = sprintf(trans('Bearbeiten'), $rs[ 'username' ]);
				$d      = sprintf(trans('Benutzer löschen'), $rs[ 'username' ]);
				$email  = sprintf(trans('eMail senden'), $rs[ 'username' ]);
				$access = sprintf(trans('Zugriffsrechte'), $rs[ 'username' ]);

				$edit   = $this->linkIcon("adm=user&amp;action=edit&amp;userid={$rs['userid']}", 'edit', $e);
				$delete = $this->linkIcon("adm=user&amp;action=delete&amp;userid={$rs['userid']}", 'delete', $d);

				$rs[ 'options' ] = <<<EOF
            {$edit}
	<a class="doTab" href="admin.php?adm=user&amp;action=email&amp;userid={$rs['userid']}"><img src="{$im}email.gif" border="0" width="16" height="16" title="{$email}"/></a>
	<a class="doTab" href="admin.php?adm=user&amp;action=access&amp;userid={$rs['userid']}"><img src="{$im}critical.png" width="16" height="16" border="0" title="{$access}"/></a>

	{$delete}
EOF;


				$row              = $this->Grid->addRow($rs);
				$rs[ 'username' ] = ($rs[ "blocked" ] ? '<img src="' . $im . 'blocked.png" width="16" height="16"/> ' :
						'') . $rs[ 'username' ];
				$row->addFieldData("userid", $rs[ 'userid' ]);
				$row->addFieldData("username", $rs[ 'username' ]);
				$row->addFieldData("regdate", $rs[ 'regdate' ]);
				$row->addFieldData("lastactivity", $rs[ "lastactivity" ]);
				$row->addFieldData("userposts", $rs[ "userposts" ]);
				$row->addFieldData("email", $rs[ "email" ]);
				$row->addFieldData("grouptitle", $rs[ "grouptitle" ]);
				$row->addFieldData("options", $rs[ 'options' ]);
			}

			$griddata = $this->Grid->renderData($found[ 'total' ]);

			if ( HTTP::input('getGriddata') )
			{
				$data               = array ();
				$data[ 'success' ]  = true;
				$data[ 'total' ]    = $found[ 'total' ];
				$data[ 'datarows' ] = $griddata[ 'rows' ];

				echo Library::json($data);
				exit;
			}

			$this->Template->process('users/users_show', array (
			                                                   'grid' => $this->Grid->getJsonData($found[ 'total' ])
			                                             ), true);
		}
	}

}

?>