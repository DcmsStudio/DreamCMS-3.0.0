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
 * @package      Editorsettings
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Save.php
 */
class Editorsettings_Action_Save extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		demoadm();

		$groupid = intval( $this->_post('groupid') );

		$group = $this->db->query('SELECT groupid, title, dashboard FROM %tp%users_groups WHERE groupid = ?', $groupid)->fetch();

		$tb1 = $tb2 = $tb3 = $tb4 = $btns = array ();

	#	parse_str(HTTP::input('toolbar_1'), $tb1);
	#	parse_str(HTTP::input('toolbar_2'), $tb2);
	#	parse_str(HTTP::input('toolbar_3'), $tb3);
	#	parse_str(HTTP::input('toolbar_4'), $tb4);

        $tadv_toolbars = array();
		$tadv_toolbars[ 'toolbar_1' ] = $this->_post('toolbar_1');
		$tadv_toolbars[ 'toolbar_2' ] = $this->_post('toolbar_2');
		$tadv_toolbars[ 'toolbar_3' ] = $this->_post('toolbar_3');
		$tadv_toolbars[ 'toolbar_4' ] = $this->_post('toolbar_4');

        // $tadv_toolbars = Library::unempty($tadv_toolbars);



		$str = serialize($tadv_toolbars);

		$this->db->query('UPDATE %tp%users_groups SET editorsettings = ? WHERE groupid = ?', $str, $groupid);

		Cache::write('tinymce-' . $groupid, $tadv_toolbars);

		if ($groupid == User::getGroupId())
        {
			list($plugins, $toolbar_output, $_toolbars) = Tinymce::getTinyMceToolbars($tadv_toolbars);
			$data = array (
				'success' => true,
				'msg'       => sprintf(trans('Toolbar für Benutzergruppe %s gespeichert'), $group[ 'title' ]),
				'tinymce' => array_merge(array (
				                               'plugins'     => $plugins,
				                               'language'    => CONTENT_TRANS,
				                               'content_css' => Tinymce::getContentCss(),
				                               'templates'   => Tinymce::getContentTemplates(),
				                         ), $_toolbars)
			);

            Ajax::Send(true, $data);

			exit;
		}



		Library::sendJson(true, sprintf(trans('Toolbar für Benutzergruppe %s gespeichert'), $group[ 'title' ]));
	}

}
