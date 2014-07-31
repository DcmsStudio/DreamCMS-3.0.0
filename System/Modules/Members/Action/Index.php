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
 * @package      Members
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Index.php
 */
class Members_Action_Index extends Controller_Abstract
{

	/**
	 * @var int
	 */
	private $limit = 20;

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			if ( User::hasPerm('user/members', false) )
			{

				$result = $this->model->getMembers();

				$data[ 'members' ] = $result[ 'result' ];


				foreach ( $data[ 'members' ] as $idx => $r )
				{
					$data[ 'members' ][ $idx ][ 'userphoto' ] = User::getUserPhoto($r);
					$data[ 'members' ][ $idx ][ 'usertext' ]  = BBCode::removeBBCode($r[ 'usertext' ]);
				}


				if ( $this->input('ajax') )
				{
					switch ( $this->_post('viewmode') )
					{
						case 'advancedlist':
							$data[ 'viewmode' ] = 'advanced';
							Session::save('memberlist-viewmode', $data[ 'viewmode' ]);

							$html = $this->Template->process('members/index', $data, null, 'ajaxlist');
							echo Library::json(array (
							                         'success' => true,
							                         'content' => $html
							                   ));
							exit;
							break;
						case 'list':
						default:

							Session::save('memberlist-viewmode', 'list');
							$data[ 'viewmode' ] = '';
							$html               = $this->Template->process('members/index', $data, null, 'table-list');
							echo Library::json(array (
							                         'success' => true,
							                         'content' => $html
							                   ));
							exit;
							break;
					}
				}


				if ( !$db_search )
				{
					$this->Breadcrumb->add(trans('Mitgliederliste'), '');
				}
				else
				{
					$this->Breadcrumb->add(trans('Mitgliederliste'), 'members');
					$this->Breadcrumb->add(sprintf(trans('Mitglieder Suche nach %s'), $q), '');
				}


				$mode               = Session::get('memberlist-viewmode', null);
				$data[ 'viewmode' ] = '';
				if ( !is_null($mode) )
				{
					$data[ 'viewmode' ] = $mode;
				}


				$this->Template->addScript('Modules/Members/asset/js/memberslist.js', false);
				$this->Template->process('members/index', $data, true);
				exit();
			}
			else
			{
				$this->Page->sendAccessError(trans('Sie haben nicht die erforderlichen Rechte, um sich die Benutzerliste ansehen zu können!'));
			}
		}
	}

}

?>