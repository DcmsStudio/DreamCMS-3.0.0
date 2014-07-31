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
 * @category     Model
 * @copyright    2008-2013 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         model.user.php
 */
class UserModel extends Model
{

	/**
	 * @return array
	 */
	public function getConfigItems ()
	{

		return array (
			'icon'  => 'group.png',
			'items' => array (
				'membersperpage'         => array (
					'label'       => trans('Anzahl der Benutzer pro Seite'),
					'type'        => 'select',
					'value'       => '20',
					'values'      => "5|" . sprintf(trans('%s Benutzer'), 5) . "|\n10|" . sprintf(trans('%s Benutzer'), 10) . "|\n15|" . sprintf(trans('%s Benutzer'), 15) . "|\n20|" . sprintf(trans('%s Benutzer'), 20) . "|checked\n30|" . sprintf(trans('%s Benutzer'), 30) . "|\n40|" . sprintf(trans('%s Benutzer'), 40) . "|\n50|" . sprintf(trans('%s Benutzer'), 50) . "|",
					'description' => trans('Geben Sie hier an, wieviele Mitglieder pro Seite auf der Mitgliederauflistungsseite angezeigt werden sollen.'),
				),
				'showavatar'             => array (
					'label'       => trans('Avatare anzeigen'),
					'type'        => 'radio',
					'values'      => '1|' . trans('Ja') . "|checked\n0|" . trans('Nein') . '|',
					'description' => trans('Sollen die Avatare der Benutzer angezeigt werden?'),
				),
				'allowflashavatar'       => array (
					'label'        => trans('Avatare im swf Format erlauben'),
					'type'         => 'radio',
					'values'       => '1|' . trans('Ja') . "|\n0|" . trans('Nein') . '|checked',
					'description'  => trans('Sollen Avatare im swf Format angezeigt werden?'),
					'fieldrequire' => 'showavatar'
				),
				'allowsightml'           => array (
					'label'       => trans('HTML in Signatur'),
					'type'        => 'radio',
					'values'      => '1|' . trans('Ja') . "|\n0|" . trans('Nein') . '|checked',
					'description' => trans('Soll HTML Code in den Signaturen der Mitglieder erlaubt sein?'),
				),
				'allowsigbbcode'         => array (
					'label'       => trans('BBCode in Signatur'),
					'type'        => 'radio',
					'values'      => '1|' . trans('Ja') . "|checked\n0|" . trans('Nein') . '|',
					'description' => trans('Soll BBCode in den Signaturen der Mitglieder erlaubt sein?'),
				),
				'allowsigsmilies'        => array (
					'label'       => trans('Smilies in Signatur'),
					'type'        => 'radio',
					'values'      => '1|' . trans('Ja') . "|checked\n0|" . trans('Nein') . '|',
					'description' => trans('Sollen Smilies in den Signaturen der Mitglieder erlaubt sein?'),
				),
				'maxsigimage'            => array (
					'label'       => trans('Max. Bilderanzahl in der Signatur'),
					'rgxp'        => array (
						'integer',
						trans('Der Wert darf nur aus Zahlen bestehen')
					),
					'type'        => 'text',
					'value'       => '5',
					'maxlength'   => 2,
					'size'        => 30,
					'controls'    => false,
					'description' => trans('Maximale Anzahl der Bilder (auch Smilies) die in der Signatur erlaubt sind. 0 = unbegrenzt'),
				),
				'showuserlevelinprofile' => array (
					'label'       => trans('Benutzerlevel anzeigen'),
					'type'        => 'radio',
					'values'      => '1|' . trans('Ja') . "|\n0|" . trans('Nein') . '|checked',
					'description' => trans('Zeigt ein Benutzerlevel im Forum an'),
				),
			)
		);
	}

}

?>