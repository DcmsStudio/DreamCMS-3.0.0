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
 * @package      Notes
 * @version      3.0.0 Beta
 * @category     Action
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Get.php
 */
class Notes_Action_Get extends Controller_Abstract
{

	public function execute ()
	{

		if ( $this->isFrontend() )
		{
			return;
		}

		$id     = (int)$this->_post('id');
		$result = $this->db->query("SELECT * FROM %tp%admin_notes WHERE id= ? AND userid = ?", $id, User::getUserId())->fetch();


		$data[ 'value' ] = $result[ 'text' ];
		$data[ 'date' ]  = $result[ 'text' ] ? date('d.m.Y, H:i', $result[ 'created' ]) : '';
		$data[ 'label' ] = $result[ 'text' ] ? date('d.m.Y, H:i', $result[ 'created' ]) : '';


		echo Library::json(array (
		                         'success'  => true,
		                         'notedata' => $data
		                   ));
		exit;


		//$sql = "SELECT * FROM %tp%admin_notes WHERE userid=" . Session::get('userid');
		/** @noinspection PhpUnreachableStatementInspection */
		$result = $this->db->select('*')->from('%tp%admin_notes')->where('userid', '=', Session::get('userid'))->execute()->fetchAll();

		$divs = '';
		$x    = 99999;
		foreach ( $result as $r )
		{
			$text = utf8_decode(htmlspecialchars($r[ 'text' ]));
			$divs .= <<<EOF
		<div id="note_{$r['id']}" class="note" style="left:{$r['x']}px;top:{$r['y']}px;position:absolute;display:block; z-index:{$x};">
			<div class="note-toolbar">
				<img src="html/style/default/img/aero-close.gif" onclick="delete_note({$r['id']});" onmouseover="this.src=this.src.replace('aero-close', 'aero-close-over');" onmouseout="this.src=this.src.replace('aero-close-over', 'aero-close');" width="19" height="19" alt="" title="Delete this note"  /> <img src="html/style/default/img/collapse.gif" onclick="hide_notes({$r['id']});" onmouseover="this.src=this.src.replace('collapse', 'collapse-over');" onmouseout="this.src=this.src.replace('collapse-over', 'collapse');" width="19" height="19" alt="" title="Close"  />
				<div class="note-load" id="note_load_{$r['id']}">&nbsp;</div>
			</div><textarea onkeydown="return catch_tab(this,event)" id="note_text_{$r['id']}" class="note-text" rows="4" cols="20" onchange="save_note(this,{$r['id']})">{$text}</textarea>
		</div>
EOF;
			$x++;
		}
		echo Library::json(array (
		                         'success' => true,
		                         'notes'   => $divs
		                   ));
		exit;
	}

}
