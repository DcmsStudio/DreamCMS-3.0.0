<?php
/*************************************************************************************
 * dwoo.php
 * ----------
 * Author: Jordi Boggiano (j.boggiano@seld.be)
 * Copyright: (c) 2008 Jordi Boggiano (http://seld.be/), Nigel McNie (http://qbnz.com/highlighter)
 * Release Version: 1.0.0
 * CVS Revision Version: $Revision: 995 $
 * Date Started: 2008/03/10
 * Last Modified: $Date: 2007-07-02 00:21:31 +1200 (Mon, 02 Jul 2007) $
 *
 * Dwoo templates language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2008/03/10 (1.0.0)
 *  -  Initial Release
 *
 * TODO
 * ----
 *
 *************************************************************************************
 *
 *     This file is part of GeSHi.
 *
 *   GeSHi is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   GeSHi is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with GeSHi; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 ************************************************************************************/

$language_data = array (
	'LANG_NAME' => 'DCMS',
	'COMMENT_SINGLE' => array(),
	'COMMENT_MULTI' => array('{*' => '*}'),
	'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
	'QUOTEMARKS' => array("'", '"'),
	'ESCAPE_CHAR' => '\\',
	'KEYWORDS' => array(
		1 => array( // Blocks
			'block', 'capture', 'forelse', 'for', 'foreachelse', 'foreach', 'loop',
			'elseif', 'if', 'else', 'php', 'textformat', 'withelse', 'with', 'include'
			),
		2 => array( // Functions
			'assign', 'capitalize', 'cat', 'count_characters', 'count_paragraphs', 'count_sentences', 'count_words', 'counter',
			'date_format', 'default', 'dump', 'eol', 'escape', 'eval', 'extends', 'fetch', 'indent', 'isset', 'lower',
			'mailto', 'math', 'nl2br', 'regex_replace',	'replace', 'reverse', 'spacify', 'string_format', 'strip_tags', 'strip', 'truncate',
			'upper', 'wordwrap', 'trans', 'cycle', 'iif', 'info',
			

			),
		3 => array( // Helpers
			'array',
			),
		4 => array( // Textual Symbols
			'eq', 'neq', 'ne', 'lte', 'gte', 'ge', 'le', 'not', 'mod', 'by', 'even', 'odd'
			),
		5 => array( // Core vars
			'$get', '$post', '$request', '$cookies', '$cookie',
			'$env', '$server', '$smarty', '$.now', '$.template',
			'$.version', '$.ad',
			),
        6 => array(
            'name', 'file', 'scope', 'global', 'key', 'once', 'script', 'var', 'op', 'condition', 'value',
            'loop', 'start', 'step', 'max', 'show', 'values', 'value', 'from', 'item'
            ),
		),
	'SYMBOLS' => array(
		'/', '==', '!=', '=', '>=', '<=', '>', '<', '!', '%', '(', ')', '[', ']', '|', '&'
		),

    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => false,
        2 => false,
        3 => false,
        4 => false,
        5 => false,
        6 => false
        ),
	'STYLES' => array(
		'KEYWORDS' => array(
			1 => 'color: #06c; font-weight:bold;',		// Blocks
			2 => 'color: #06c;font-weight:bold;',		// Functions
			3 => 'color: #06c;font-weight:bold;',		// Helpers
			4 => 'color: #06c; font-weight:bold;',		// Textual Symbols
			5 => 'font-weight:bold;',		// Core vars
			6 => 'color: #6A0A0A;',        //Attributes
			),
		'COMMENTS' => array(
			'MULTI' => 'color: #3f5fbf;'
			),
		'ESCAPE_CHAR' => array(
			0 => 'color: #000099;'
			),
		'BRACKETS' => array(
			0 => 'color: #00A418;'
			),
		'STRINGS' => array(
			0 => 'color: #ff0000;'
			),
		'NUMBERS' => array(
			0 => 'color: #cc66cc;'
			),
		'METHODS' => array(
			1 => 'color: #006600;'
			),
		'SYMBOLS' => array(
			0 => 'color: #66cc66;'
			),
		'SCRIPT' => array(
            0 => '',
            1 => 'color: #808080; font-style: italic;',
            2 => 'color: #009000;'
			),
		'REGEXPS' => array(
			0 => 'color: #00aaff;',
			1 => 'color: #00aaff;'
			)
		),
	'URLS' => array(
		1 => 'http://wiki.dwoo.org/index.php/Blocks:{FNAME}',
		2 => 'http://wiki.dwoo.org/index.php/Functions:{FNAME}',
		3 => 'http://wiki.dwoo.org/index.php/Helpers:{FNAME}',
		4 => '',
		5 => '',
		),
	'OOLANG' => true,
	'OBJECT_SPLITTERS' => array(
		1 => '.'
		),
	'REGEXPS' => array(
        0 => array(//attribute names
            GESHI_SEARCH => '([a-z_:][\w\-\.:]*)(=)',
            GESHI_REPLACE => '\\1',
            GESHI_MODIFIERS => 'i',
            GESHI_BEFORE => '',
            GESHI_AFTER => '\\2'
            ),
        3 => array(//Tag end markers
            GESHI_SEARCH => '(([\/|\?])?&gt;)',
            GESHI_REPLACE => '\\1',
            GESHI_MODIFIERS => 'i',
            GESHI_BEFORE => '',
            GESHI_AFTER => ''
            ),
		4 => '(\$[a-zA-Z][a-zA-Z0-9_\.]*)'
	),
	'STRICT_MODE_APPLIES' => GESHI_ALWAYS,
	'SCRIPT_DELIMITERS' => array(
        -1 => array(
            '<!--' => '-->'
            ),
        0 => array(
            '<!DOCTYPE' => '>'
            ),
        1 => array(
            '&' => ';'
            ),
        2 => array(
            '<![CDATA[' => ']]>'
            ),
		3 => array(
			'<cp:' => '>'
			),
		4 => array(
			'{' => '}'
			),
		5 => array(
			'</cp:' => '>'
			),
		5 => array(
			'<' => '>'
			),
	),
	'HIGHLIGHT_STRICT_BLOCK' => array(
        -1 => false,
        0 => false,
        1 => false,
        2 => false,
        3 => true, 
		4 => true,
		5 => true, 
		6 => true 
		),
	'TAB_WIDTH' => 4,
	
    'PARSER_CONTROL' => array(
        'ENABLE_FLAGS' => array(
            'NUMBERS' => GESHI_NEVER
        ),
        'KEYWORDS' => array(

        )
    )
);

?>