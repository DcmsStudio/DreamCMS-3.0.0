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
 * @file         tag.php
 */

$tagDefine = array (
	'tagname'     => 'tag',
	'description' => trans('Hiermit ist es möglich HTML Tags zu erstellen.'),
	'attributes'  => array (
		'name'       => array (
			'name'     => 'text',
			'size'     => 50,
			'default'  => '',
			'label'    => trans('Html Tag'),
			'required' => true,
			'values'   => array (
				"html",
				"head",
				"title",
				"base",
				"link",
				"meta",
				"style",
				"script",
				"noscript",
				"body",
				"section",
				"nav",
				"article",
				"aside",
				"h1",
				"h2",
				"h3",
				"h4",
				"h5",
				"h6",
				"header",
				"footer",
				"address",
				"main",
				"p",
				"hr",
				"pre",
				"blockquote",
				"ol",
				"ul",
				"li",
				"dl",
				"dt",
				"dd",
				"figure",
				"figcaption",
				"div",
				"a",
				"em",
				"strong",
				"small",
				"s",
				"cite",
				"q",
				"dfn",
				"abbr",
				"data",
				"time",
				"code",
				"var",
				"samp",
				"kbd",
				"sub",
				"sup",
				"i",
				"b",
				"u",
				"mark",
				"ruby",
				"rt",
				"rp",
				"bdi",
				"bdo",
				"span",
				"br",
				"wbr",
				"ins",
				"del",
				"img",
				"iframe",
				"embed",
				"object",
				"param",
				"video",
				"audio",
				"source",
				"track",
				"canvas",
				"map",
				"area",
				"svg",
				"math",
				"table",
				"caption",
				"colgroup",
				"col",
				"tbody",
				"thead",
				"tfoot",
				"tr",
				"td",
				"th",
				"form",
				"fieldset",
				"legend",
				"label",
				"input",
				"button",
				"select",
				"datalist",
				"optgroup",
				"option",
				"textarea",
				"keygen",
				"output",
				"progress",
				"meter",
				"details",
				"summary",
				"command",
				"menu",
				"dialog"
			)
		),
		'class'      => array (
			'type'        => 'text',
			'size'        => 50,
			'default'     => '',
			'label'       => trans('CSS Classen'),
			'description' => '',
			'required'    => false,
		),
		'id'         => array (
			'type'        => 'text',
			'size'        => 50,
			'default'     => '',
			'label'       => trans('ID Attribut'),
			'description' => '',
			'required'    => false,
		),
		'forceclose' => array (
			'type'        => 'text',
			'size'        => 50,
			'default'     => '',
			'label'       => trans('Wenn true dann wird nur der Close Tag erzeugt'),
			'description' => '',
			'required'    => false,
		)
	),
	'isSingleTag' => true

);