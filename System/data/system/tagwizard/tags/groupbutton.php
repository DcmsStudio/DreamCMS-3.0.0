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
 * @package      
 * @version      3.0.0 Beta
 * @category     
 * @copyright    2008-2014 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         groupbutton.php
 */

$tagDefine = array(
	'tagname' => 'groupbutton',
	'description' => trans('Dieses Tag erzeugt Bootstrap 3 Group Buttons. Html Attribute bis auf das Attribut "class" können mit angefügt werden.'),
	'attributes' => array(
		'addtb' => array(
			'type' => 'select',
			'default' => 'true',
			'values' => array(
				'true' => trans('Ja'),
				'false' => trans('Nein')
			),
			'label' => trans('Toolbar mit erzeugen'),
			'required' => false,
		),
		'size' => array(
			'type' => 'text',
			'size' => 50,
			'default' => '',
			'label' => trans('Bootstrap Size angabe (CSS Klasse)'),
			'description' => '',
			'required' => false,
		),
		'label' => array(
			'type' => 'text',
			'size' => 50,
			'default' => '',
			'label' => trans('Groupbuttons Label'),
			'description' => trans('Wird nur erzeugt, wenn das Attribut nicht leer ist'),
			'required' => false,
		),
	),
	'isSingleTag' => false

);