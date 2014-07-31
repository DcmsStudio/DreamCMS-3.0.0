/**
 * plugin.js
 *
 * Copyright, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://www.tinymce.com/license
 * Contributing: http://www.tinymce.com/contributing
 */

/*global tinymce:true */
tinymce.PluginManager.add('bbcode', function(editor) {

	function strip_empty_html( html )
	{
		html = html.replace( '<([^>]+?)></([^>]+?)>', "");
		return html;
	}
	function strip_html( html )
	{
		html = html.replace( /<\/?([^>]+?)>/ig, "");
		return html;
	}


	var BBCodeConverter = function() {
		var me = this;            // stores the object instance
		var token_match = /{[A-Z_]+[0-9]*}/ig;

		// regular expressions for the different bbcode tokens
		var tokens = {
			'URL' : '((?:(?:[a-z][a-z\\d+\\-.]*:\\/{2}(?:(?:[a-z0-9\\-._~\\!$&\'*+,;=:@|]+|%[\\dA-F]{2})+|[0-9.]+|\\[[a-z0-9.]+:[a-z0-9.]+:[a-z0-9.:]+\\])(?::\\d*)?(?:\\/(?:[a-z0-9\\-._~\\!$&\'*+,;=:@|]+|%[\\dA-F]{2})*)*(?:\\?(?:[a-z0-9\\-._~\\!$&\'*+,;=:@\\/?|]+|%[\\dA-F]{2})*)?(?:#(?:[a-z0-9\\-._~\\!$&\'*+,;=:@\\/?|]+|%[\\dA-F]{2})*)?)|(?:www\\.(?:[a-z0-9\\-._~\\!$&\'*+,;=:@|]+|%[\\dA-F]{2})+(?::\\d*)?(?:\\/(?:[a-z0-9\\-._~\\!$&\'*+,;=:@|]+|%[\\dA-F]{2})*)*(?:\\?(?:[a-z0-9\\-._~\\!$&\'*+,;=:@\\/?|]+|%[\\dA-F]{2})*)?(?:#(?:[a-z0-9\\-._~\\!$&\'*+,;=:@\\/?|]+|%[\\dA-F]{2})*)?)))',
			'LINK' : '([a-z0-9\-\./]+[^"\' ]*)',
			'EMAIL' : '((?:[\\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+\.)*(?:[\\w\!\#$\%\'\*\+\-\/\=\?\^\`{\|\}\~]|&)+@(?:(?:(?:(?:(?:[a-z0-9]{1}[a-z0-9\-]{0,62}[a-z0-9]{1})|[a-z])\.)+[a-z]{2,6})|(?:\\d{1,3}\.){3}\\d{1,3}(?:\:\\d{1,5})?))',
			'TEXT' : '(.*?)',
			'SIMPLETEXT' : '([a-zA-Z0-9-+.,_ ]+)',
			'INTTEXT' : '([a-zA-Z0-9-+,_. ]+)',
			'IDENTIFIER' : '([a-zA-Z0-9-_]+)',
			'COLOR' : '([a-z]+|#[0-9abcdef]+)',
			'NUMBER'  : '([0-9]+)'
		};

		var bbcode_matches = [];        // matches for bbcode to html

		var html_tpls = [];             // html templates for html to bbcode

		var html_matches = [];          // matches for html to bbcode

		var bbcode_tpls = [];           // bbcode templates for bbcode to html

		/**
		 * Turns a bbcode into a regular rexpression by changing the tokens into
		 * their regex form
		 */
		var _getRegEx = function(str) {
			var matches = str.match(token_match);
			var nrmatches = matches.length;
			var i = 0;
			var replacement = '';

			if (nrmatches <= 0) {
				return new RegExp(preg_quote(str), 'g');        // no tokens so return the escaped string
			}

			for(; i < nrmatches; i += 1) {
				// Remove {, } and numbers from the token so it can match the
				// keys in tokens
				var token = matches[i].replace(/[{}0-9]/g, '');

				if (tokens[token]) {
					// Escape everything before the token
					replacement += preg_quote(str.substr(0, str.indexOf(matches[i]))) + tokens[token];

					// Remove everything before the end of the token so it can be used
					// with the next token. Doing this so that parts can be escaped
					str = str.substr(str.indexOf(matches[i]) + matches[i].length);
				}
			}

			replacement += preg_quote(str);      // add whatever is left to the string

			return new RegExp(replacement, 'gi');
		};

		/**
		 * Turns a bbcode template into the replacement form used in regular expressions
		 * by turning the tokens in $1, $2, etc.
		 */
		var _getTpls = function(str) {
			var matches = str.match(token_match);
			var nrmatches = matches.length;
			var i = 0;
			var replacement = '';
			var positions = {};
			var next_position = 0;

			if (nrmatches <= 0) {
				return str;       // no tokens so return the string
			}

			for(; i < nrmatches; i += 1) {
				// Remove {, } and numbers from the token so it can match the
				// keys in tokens
				var token = matches[i].replace(/[{}0-9]/g, '');
				var position;

				// figure out what $# to use ($1, $2)
				if (positions[matches[i]]) {
					position = positions[matches[i]];         // if the token already has a position then use that
				} else {
					// token doesn't have a position so increment the next position
					// and record this token's position
					next_position += 1;
					position = next_position;
					positions[matches[i]] = position;
				}

				if (tokens[token]) {
					replacement += str.substr(0, str.indexOf(matches[i])) + '$' + position;
					str = str.substr(str.indexOf(matches[i]) + matches[i].length);
				}
			}

			replacement += str;

			return replacement;
		};

		/**
		 * Adds a bbcode to the list
		 */
		me.addBBCode = function(bbcode_match, bbcode_tpl) {
			// add the regular expressions and templates for bbcode to html
			bbcode_matches.push(_getRegEx(bbcode_match));
			html_tpls.push(_getTpls(bbcode_tpl));

			// add the regular expressions and templates for html to bbcode
			html_matches.push(_getRegEx(bbcode_tpl));
			bbcode_tpls.push(_getTpls(bbcode_match));
		};

		/**
		 * Turns all of the added bbcodes into html
		 */
		me.bbcodeToHtml = function(str) {
			var nrbbcmatches = bbcode_matches.length;
			var i = 0;

			for(; i < nrbbcmatches; i += 1) {
				str = str.replace(bbcode_matches[i], html_tpls[i]);
			}

			return str;
		};

		/**
		 * Turns html into bbcode
		 */
		me.htmlToBBCode = function(str) {
			var nrhtmlmatches = html_matches.length;
			var i = 0;

			for(; i < nrhtmlmatches; i += 1) {
				str = str.replace(html_matches[i], bbcode_tpls[i]);
			}

			return str;
		}

		/**
		 * Quote regular expression characters plus an optional character
		 * taken from phpjs.org
		 */
		function preg_quote (str, delimiter) {
			return (str + '').replace(new RegExp('[.\\\\+*?\\[\\^\\]$(){}=!<>|:\\' + (delimiter || '') + '-]', 'g'), '\\$&');
		}

		// adds BBCodes and their HTML
		me.addBBCode('[b]{TEXT}[/b]', '<strong>{TEXT}</strong>');
		me.addBBCode('[i]{TEXT}[/i]', '<em>{TEXT}</em>');
		me.addBBCode('[u]{TEXT}[/u]', '<span style="text-decoration: underline;">{TEXT}</span>');
		me.addBBCode('[s]{TEXT}[/s]', '<span style="text-decoration: line-through;">{TEXT}</span>');

		me.addBBCode('[link={URL}]{TEXT}[/link]', '<a href="{URL}" title="link" target="_blank">{TEXT}</a>');


		me.addBBCode('[url={URL}]{TEXT}[/url]', '<a href="{URL}" title="link" target="_blank">{TEXT}</a>');
		me.addBBCode('[url]{URL}[/url]', '<a href="{URL}" title="link" target="_blank">{URL}</a>');
		me.addBBCode('[url={LINK}]{TEXT}[/url]', '<a href="{LINK}" title="link" target="_blank">{TEXT}</a>');
		me.addBBCode('[url]{LINK}[/url]', '<a href="{LINK}" title="link" target="_blank">{LINK}</a>');

		me.addBBCode('[img={URL} width={NUMBER1} height={NUMBER2}]{TEXT}[/img]', '<img src="{URL}" width="{NUMBER1}" height="{NUMBER2}" alt="{TEXT}" />');
		me.addBBCode('[img={URL}]', '<img src="{URL}" alt="{URL}" />');
		me.addBBCode('[img]{URL}[/img]', '<img src="{URL}" alt="{URL}" />');
		me.addBBCode('[img={LINK} width={NUMBER1} height={NUMBER2}]{TEXT}[/img]', '<img src="{LINK}" width="{NUMBER1}" height="{NUMBER2}" alt="{TEXT}" />');
		me.addBBCode('[img]{LINK}[/img]', '<img src="{LINK}" alt="{LINK}" />');
		me.addBBCode('[color={COLOR}]{TEXT}[/color]', '<span style="color: {COLOR}">{TEXT}</span>');
		me.addBBCode('[highlight={COLOR}]{TEXT}[/highlight]', '<span style="background-color: {COLOR}">{TEXT}</span>');
		me.addBBCode('[quote="{TEXT1}"]{TEXT2}[/quote]', '<div class="quote"><cite>{TEXT1}</cite><p>{TEXT2}</p></div>');
		me.addBBCode('[quote]{TEXT}[/quote]', '<cite>{TEXT}</cite>');
		me.addBBCode('[blockquote]{TEXT}[/blockquote]', '<blockquote>{TEXT}</blockquote>');
	};



	function convertToHtml(ed, code) {

		var bb = new BBCodeConverter();

		code = code.replace(/\[br\]/gi, '<br/>');
		code = code.replace(/\[p\]/gi, '<p>');
		code = code.replace(/\[\/p\]/gi, '</p>');

		code = bb.bbcodeToHtml(code);

		code = code.replace(/<\/p>\s*<br\s*\/?>/gmi, '</p>');

		editor.setContent(code, {format: 'raw', no_events: true});
		editor.selection.setCursorLocation();
	}

	function convertToBBCode(ed, code) {

		code = strip_empty_html(code);

		code = code.replace(/<p([^>]*)>/gi, '[p]');
		code = code.replace(/<\/p>/gi, '[/p][br]');

		code = code.replace(/<br\s*\/?>/gi, '[br]');
		code = code.replace(/&nbsp;/gi, ' ');



		var bb = new BBCodeConverter();
		code = bb.htmlToBBCode(code);

		code = strip_html(code);
		code = code.replace(/\[br\]/gi, (editor.settings.inline ? '<br/>' : '\n') );

		editor.setContent(code, {format: 'raw', no_events: true});
		editor.selection.setCursorLocation();
	}

	editor.addButton('bbcode', {
		// icon: 'code',
		text: 'BBcode/Html',
		tooltip: 'Switch BBcode/Html Mode',
		onclick: function(e) {

			var mode = editor.settings.bbmode || 'html';
			if (mode == 'bbcode') {
				e.control._active = false;
				editor.settings.bbmode = 'html';
				convertToHtml(editor, editor.getContent({format: 'raw', no_events: true}));
			}
			else if (mode == 'html') {
				e.control._active = true;
				editor.settings.bbmode = 'bbcode';
				convertToBBCode(editor, editor.getContent({format: 'raw', no_events: true}));
			}

			e.stopPropagation();
		}
	});

	editor.on('keyUp', function(e) {

		var mode = editor.settings.bbmode || 'html';
		if ( e.keyCode === 13 && mode == 'bbcode' ) {
			editor.execCommand( 'mceInsertContent', false, '[p][/p]<br/>' );
			e.stopPropagation();
		}
		else if ( e.keyCode == 8  && mode == 'bbcode' )
		{
			e.stopPropagation();
		}

	});



	editor.on( 'PostProcess', function(e) {
		var mode = editor.settings.bbmode || 'html';
		if ( mode == 'bbcode') {
			convertToBBCode(editor, editor.getContent({format: 'raw', no_events: true}) );
		}
		else if (mode == 'html') {
			convertToHtml(editor, editor.getContent({format: 'raw', no_events: true}) );
		}
	});

});
/*



(function() {
	tinymce.create('tinymce.plugins.BBCodePlugin', {
		init : function(ed) {
			var self = this, dialect = ed.getParam('bbcode_dialect', 'punbb').toLowerCase();

			ed.on('beforeSetContent', function(e) {
				e.content = self['_' + dialect + '_bbcode2html'](e.content);
			});

			ed.on('postProcess', function(e) {
				if (e.set) {
					e.content = self['_' + dialect + '_bbcode2html'](e.content);
				}

				if (e.get) {
					e.content = self['_' + dialect + '_html2bbcode'](e.content);
				}
			});
		},

		getInfo: function() {
			return {
				longname: 'BBCode Plugin',
				author: 'Moxiecode Systems AB',
				authorurl: 'http://www.tinymce.com',
				infourl: 'http://www.tinymce.com/wiki.php/Plugin:bbcode'
			};
		},

		// Private methods

		// HTML -> BBCode in PunBB dialect
		_punbb_html2bbcode : function(s) {
			s = tinymce.trim(s);

			function rep(re, str) {
				s = s.replace(re, str);
			}

			// example: <strong> to [b]
			rep(/<a.*?href=\"(.*?)\".*?>(.*?)<\/a>/gi,"[url=$1]$2[/url]");
			rep(/<font.*?color=\"(.*?)\".*?class=\"codeStyle\".*?>(.*?)<\/font>/gi,"[code][color=$1]$2[/color][/code]");
			rep(/<font.*?color=\"(.*?)\".*?class=\"quoteStyle\".*?>(.*?)<\/font>/gi,"[quote][color=$1]$2[/color][/quote]");
			rep(/<font.*?class=\"codeStyle\".*?color=\"(.*?)\".*?>(.*?)<\/font>/gi,"[code][color=$1]$2[/color][/code]");
			rep(/<font.*?class=\"quoteStyle\".*?color=\"(.*?)\".*?>(.*?)<\/font>/gi,"[quote][color=$1]$2[/color][/quote]");
			rep(/<span style=\"color: ?(.*?);\">(.*?)<\/span>/gi,"[color=$1]$2[/color]");
			rep(/<font.*?color=\"(.*?)\".*?>(.*?)<\/font>/gi,"[color=$1]$2[/color]");
			rep(/<span style=\"font-size:(.*?);\">(.*?)<\/span>/gi,"[size=$1]$2[/size]");
			rep(/<font>(.*?)<\/font>/gi,"$1");
			rep(/<img.*?src=\"(.*?)\".*?\/>/gi,"[img]$1[/img]");
			rep(/<span class=\"codeStyle\">(.*?)<\/span>/gi,"[code]$1[/code]");
			rep(/<span class=\"quoteStyle\">(.*?)<\/span>/gi,"[quote]$1[/quote]");
			rep(/<strong class=\"codeStyle\">(.*?)<\/strong>/gi,"[code][b]$1[/b][/code]");
			rep(/<strong class=\"quoteStyle\">(.*?)<\/strong>/gi,"[quote][b]$1[/b][/quote]");
			rep(/<em class=\"codeStyle\">(.*?)<\/em>/gi,"[code][i]$1[/i][/code]");
			rep(/<em class=\"quoteStyle\">(.*?)<\/em>/gi,"[quote][i]$1[/i][/quote]");
			rep(/<u class=\"codeStyle\">(.*?)<\/u>/gi,"[code][u]$1[/u][/code]");
			rep(/<u class=\"quoteStyle\">(.*?)<\/u>/gi,"[quote][u]$1[/u][/quote]");
			rep(/<\/(strong|b)>/gi,"[/b]");
			rep(/<(strong|b)>/gi,"[b]");
			rep(/<\/(em|i)>/gi,"[/i]");
			rep(/<(em|i)>/gi,"[i]");
			rep(/<\/u>/gi,"[/u]");
			rep(/<span style=\"text-decoration: ?underline;\">(.*?)<\/span>/gi,"[u]$1[/u]");
			rep(/<u>/gi,"[u]");
			rep(/<blockquote[^>]*>/gi,"[quote]");
			rep(/<\/blockquote>/gi,"[/quote]");
			rep(/<br \/>/gi,"\n");
			rep(/<br\/>/gi,"\n");
			rep(/<br>/gi,"\n");
			rep(/<p>/gi,"");
			rep(/<\/p>/gi,"\n");
			rep(/&nbsp;|\u00a0/gi," ");
			rep(/&quot;/gi,"\"");
			rep(/&lt;/gi,"<");
			rep(/&gt;/gi,">");
			rep(/&amp;/gi,"&");

			return s;
		},

		// BBCode -> HTML from PunBB dialect
		_punbb_bbcode2html : function(s) {
			s = tinymce.trim(s);

			function rep(re, str) {
				s = s.replace(re, str);
			}

			// example: [b] to <strong>
			rep(/\n/gi,"<br />");
			rep(/\[b\]/gi,"<strong>");
			rep(/\[\/b\]/gi,"</strong>");
			rep(/\[i\]/gi,"<em>");
			rep(/\[\/i\]/gi,"</em>");
			rep(/\[u\]/gi,"<u>");
			rep(/\[\/u\]/gi,"</u>");
			rep(/\[url=([^\]]+)\](.*?)\[\/url\]/gi,"<a href=\"$1\">$2</a>");
			rep(/\[url\](.*?)\[\/url\]/gi,"<a href=\"$1\">$1</a>");
			rep(/\[img\](.*?)\[\/img\]/gi,"<img src=\"$1\" />");
			rep(/\[color=(.*?)\](.*?)\[\/color\]/gi,"<font color=\"$1\">$2</font>");
			rep(/\[code\](.*?)\[\/code\]/gi,"<span class=\"codeStyle\">$1</span>&nbsp;");
			rep(/\[quote.*?\](.*?)\[\/quote\]/gi,"<span class=\"quoteStyle\">$1</span>&nbsp;");

			return s;
		}
	});

	// Register plugin
	tinymce.PluginManager.add('bbcode', tinymce.plugins.BBCodePlugin);
})();

	*/