var focusedAceEdit = null;
window.focusedAceEdit = null;

var AceEdit = function ()
{
	var arrHints = [], sourceEditor_selfCloseTags = ['', "!doctype", "area", "base", "br", "hr", "input", "img", "link", "meta",
		'cp:footnote', 'cp:include', 'cp:set', 'cp:unset', 'cp:tag', ''
	];
	var sourceEditor_html_tags = ["!doctype", "a", "abbr", "acronym", "address", "applet", "area", "article", "aside", "audio", "b", "base", "basefont", "bdo", "bgsound", "big", "blink", "blockquote", "body", "br", "button", "canvas", "caption", "center", "cite", "code", "col", "colgroup", "command", "comment", "datalist", "dd", "del", "dfn", "dir", "div", "dl", "dt", "em", "embed", "fieldset", "figcaption", "figure", "font", "footer", "form", "frame", "frameset", "h1", "h2", "h3", "h4", "h5", "h6", "head", "header", "hgroup", "hr", "html", "i", "iframe", "ilayer", "img", "input", "ins", "isindex", "kbd", "keygen", "label", "layer", "legend", "li", "link", "listing", "map", "mark", "marquee", "menu", "meta", "meter", "multicol", "nav", "nextid", "nobr", "noembed", "noframes", "nolayer", "noscript", "object", "ol", "optgroup", "option", "output", "p", "param", "plaintext", "pre", "progress", "q", "rb", "rbc", "rp", "rt", "rtc", "ruby", "s", "samp", "script", "section", "select", "small", "source", "spacer", "span", "strike", "strong", "style", "sub", "summary", "sup", "table", "tbody", "td", "textarea", "tfoot", "th", "thead", "time", "tr", "tt", "u", "ul", "var", "wbr", "video", "wbr", "xml", "xmp"];
	//Default rules
	var ruleSets = {
			csslint: false,
			jshint: false,
			'tagname-lowercase': true,
			'attr-lowercase': true,
			'attr-value-double-quotes': true,
			'doctype-first': false,
			'doctype-html5': false,
			'tag-pair': true,
			'spec-char-escape': false,
			'id-unique': true
		},
		noHtmlRuleSet = {
			csslint: false,
			jshint: false,
			'tagname-lowercase': false,
			'attr-lowercase': false,
			'attr-value-double-quotes': false,
			'doctype-first': false,
			'doctype-html5': false,
			'tag-pair': false,
			'spec-char-escape': false,
			'id-unique': false
		},
		ruleCSSLint = {
			"display-property-grouping": true,
			"known-properties": true
		},
		ruleJSHint = {
		};

	var ID_REGEX = /[a-zA-Z_0-9\$-]/;
	var cursorChangeTimeout;

	this.fullscreen = false;
	this.isDirty = false;
	this.disableHint = false;
	this.editor = null;

	this.TokenTooltip = null;
	this.Intellisense = null;

	this.EnableIntellisense = true;

	this.textarea = null;
	this.vim = null;
	this.emacs = null;
	this.mode = 'html';
	this.editorID = null;
	this.win = null;
	this.editorToolbar = null;
	this.loadedThemes = {};

	// jQuery objects
	this.jqWrapper = null;
	this.jqEditor = null;
	this.jqErrorBar = null;

	this.jqEditorStatusbar = $( '<div id="editor-status" class="ace-status-bar"><div class="editor-state"><span class="column">Col: <span></span></span><span class="line">Line: <span></span></span><span class="length">Length: <span></span></span></div><div>' );

	this._worker = null;
	this.$mode = [];

	this.loadingImage = $( '<img src="' + Config.get( 'backendImagePath' ) + 'loading.gif"/>' );

	this.aceopts = {
		fontsize: '12px',
		theme: 'netbeans_dark',
		gutter: true,
		highlight_active: true,
		highlight_selected: true,
		show_invisibles: false,
		persistent_hscroll: false,
		csslint: true,
		jshint: true,
		htmlhint: true,
		autoCloseBrackets: true
	};

	this.retrievePrecedingIdentifier = function ( text, pos, regex )
	{
		regex = regex || ID_REGEX;
		var buf = [];
		for ( var i = pos - 1; i >= 0; i-- ) {
			if ( regex.test( text[i] ) )
				buf.push( text[i] );
			else
				break;
		}
		return buf.reverse().join( "" );
	};

    this.utf8_encode = function(e) {
        var t = e + "",
            n = "",
            r, i, s = 0;
        r = i = 0, s = t.length;
        for (var o = 0; o < s; o++) {
            var u = t.charCodeAt(o),
                a = null;
            u < 128 ? i++ : u > 127 && u < 2048 ? a = String.fromCharCode(u >> 6 | 192) + String.fromCharCode(u & 63 | 128) : a = String.fromCharCode(u >> 12 | 224) + String.fromCharCode(u >> 6 & 63 | 128) + String.fromCharCode(u & 63 | 128), a !== null && (i > r && (n += t.substring(r, i)), n += a, r = i = o + 1)
        }
        return i > r && (n += t.substring(r, t.length)), n
    }

    this.utf8_decode = function(utftext) {
        var string = "";
        var i = 0;
        var c = c1 = c2 = 0;

        while ( i < utftext.length ) {

            c = utftext.charCodeAt(i);

            if (c < 128) {
                string += String.fromCharCode(c);
                i++;
            }
            else if((c > 191) && (c < 224)) {
                c2 = utftext.charCodeAt(i+1);
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2;
            }
            else {
                c2 = utftext.charCodeAt(i+1);
                c3 = utftext.charCodeAt(i+2);
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }

        }

        return string;
    }

    this.get_html_translation_table = function(e, t) {
        var n = {}, r = {}, i = 0,
            s = "",
            o = {}, u = {}, a = {}, f = {};
        o[0] = "HTML_SPECIALCHARS", o[1] = "HTML_ENTITIES", u[0] = "ENT_NOQUOTES", u[2] = "ENT_COMPAT", u[3] = "ENT_QUOTES", a = isNaN(e) ? e ? e.toUpperCase() : "HTML_SPECIALCHARS" : o[e], f = isNaN(t) ? t ? t.toUpperCase() : "ENT_COMPAT" : u[t];
        if (a !== "HTML_SPECIALCHARS" && a !== "HTML_ENTITIES") throw new Error("Table: " + a + " not supported");
        n[38] = "&amp;", a === "HTML_ENTITIES" && (n[160] = "&nbsp;", n[161] = "&iexcl;", n[162] = "&cent;", n[163] = "&pound;", n[164] = "&curren;", n[165] = "&yen;", n[166] = "&brvbar;", n[167] = "&sect;", n[168] = "&uml;", n[169] = "&copy;", n[170] = "&ordf;", n[171] = "&laquo;", n[172] = "&not;", n[173] = "&shy;", n[174] = "&reg;", n[175] = "&macr;", n[176] = "&deg;", n[177] = "&plusmn;", n[178] = "&sup2;", n[179] = "&sup3;", n[180] = "&acute;", n[181] = "&micro;", n[182] = "&para;", n[183] = "&middot;", n[184] = "&cedil;", n[185] = "&sup1;", n[186] = "&ordm;", n[187] = "&raquo;", n[188] = "&frac14;", n[189] = "&frac12;", n[190] = "&frac34;", n[191] = "&iquest;", n[192] = "&Agrave;", n[193] = "&Aacute;", n[194] = "&Acirc;", n[195] = "&Atilde;", n[196] = "&Auml;", n[197] = "&Aring;", n[198] = "&AElig;", n[199] = "&Ccedil;", n[200] = "&Egrave;", n[201] = "&Eacute;", n[202] = "&Ecirc;", n[203] = "&Euml;", n[204] = "&Igrave;", n[205] = "&Iacute;", n[206] = "&Icirc;", n[207] = "&Iuml;", n[208] = "&ETH;", n[209] = "&Ntilde;", n[210] = "&Ograve;", n[211] = "&Oacute;", n[212] = "&Ocirc;", n[213] = "&Otilde;", n[214] = "&Ouml;", n[215] = "&times;", n[216] = "&Oslash;", n[217] = "&Ugrave;", n[218] = "&Uacute;", n[219] = "&Ucirc;", n[220] = "&Uuml;", n[221] = "&Yacute;", n[222] = "&THORN;", n[223] = "&szlig;", n[224] = "&agrave;", n[225] = "&aacute;", n[226] = "&acirc;", n[227] = "&atilde;", n[228] = "&auml;", n[229] = "&aring;", n[230] = "&aelig;", n[231] = "&ccedil;", n[232] = "&egrave;", n[233] = "&eacute;", n[234] = "&ecirc;", n[235] = "&euml;", n[236] = "&igrave;", n[237] = "&iacute;", n[238] = "&icirc;", n[239] = "&iuml;", n[240] = "&eth;", n[241] = "&ntilde;", n[242] = "&ograve;", n[243] = "&oacute;", n[244] = "&ocirc;", n[245] = "&otilde;", n[246] = "&ouml;", n[247] = "&divide;", n[248] = "&oslash;", n[249] = "&ugrave;", n[250] = "&uacute;", n[251] = "&ucirc;", n[252] = "&uuml;", n[253] = "&yacute;", n[254] = "&thorn;", n[255] = "&yuml;"), f !== "ENT_NOQUOTES" && (n[34] = "&quot;"), f === "ENT_QUOTES" && (n[39] = "&#39;"), n[60] = "&lt;", n[62] = "&gt;";
        for (i in n) s = String.fromCharCode(i), r[s] = n[i];
        return r
    }

    this.htmlentities = function(e, t) {
        if (e === null) return "";
        var n = {}, r = "",
            i = "",
            s = "";
        i = e.toString();
        if (!1 === (n = this.get_html_translation_table("HTML_ENTITIES", t))) return !1;
        n["'"] = "&#039;";
        for (r in n) s = n[r], i = i.split(r).join(s);
        return i
    }

    this.html_entity_decode = function(e, t) {
        if (!e) return "";
        var n = {}, r = "",
            i = "",
            s = "";
        i = e.toString();
        if (!1 === (n = this.get_html_translation_table("HTML_ENTITIES", t))) return !1;
        n["&"] = "&amp;";
        for (r in n) s = n[r], i = i.split(s).join(r);
        return i = i.split("&#039;").join("'"), i
    }
    this.toUnicode = function(theString) {
        var unicodeString = '';
        for (var i=0; i < theString.length; i++) {
            var theUnicode = theString.charCodeAt(i).toString(16).toUpperCase();
            while (theUnicode.length < 4) {
                theUnicode = '0' + theUnicode;
            }
            theUnicode = '\\u' + theUnicode;
            unicodeString += theUnicode;
        }
        return unicodeString;
    }
	this.init = function ( editorID, object, winObj )
	{

		/*
		 Loader.require(['html/js/backend/tpleditor/beautifier/lib/beautify.js',
		 'html/js/backend/tpleditor/beautifier/lib/beautify-css.js',
		 'html/js/backend/tpleditor/beautifier/lib/beautify-html.js',
		 'html/js/backend/tpleditor/beautifier/test/sanitytest.js',
		 'html/js/backend/tpleditor/beautifier/test/beautify-tests.js',
		 'html/js/backend/tpleditor/beautifier/lib/unpackers/javascriptobfuscator_unpacker.js',
		 'html/js/backend/tpleditor/beautifier/lib/unpackers/urlencode_unpacker.js',
		 'html/js/backend/tpleditor/beautifier/lib/unpackers/p_a_c_k_e_r_unpacker.js',
		 'html/js/backend/tpleditor/beautifier/lib/unpackers/myobfuscate_unpacker.js']);

		 */
		var self = this;

		// init editor
		this.editorID = editorID;
		if ( winObj.find( '.sourceEditor-toolbar-buttons' ).length == 1 ) {
			this.editorToolbar = winObj.find( '.sourceEditor-toolbar-buttons' );

			this.win = winObj;

		}
		else if ( winObj ) {
			var hash = winObj.attr( 'id' ).replace( 'tab-', '' ).replace( 'content-', '' );
			if ( $( '#buttons-' + hash ).find( '.sourceEditor-toolbar-buttons' ).length == 1 ) {
				this.editorToolbar = $( '#buttons-' + hash ).find( '.sourceEditor-toolbar-buttons' );

				this.win = $( winObj );

			}
		}

		this.jqEditor = $( '#' + editorID );
		this.jqWrapper = $( '#' + editorID + '-wrapper' );

		this.jQcheckingSyntax = $( '<div class="ace-checking-syntax"/>' ).append( this.loadingImage ).append( '<span>Checking Syntax...</span>' );
		this.jQvalidateSyntax = $( '<div class="ace-valid-syntax"/>' );
		this.jqErrorBar = $( '<div id="editor-errors' + editorID + '" class="ace-hint-bar"/>' ).show();
		this.jqErrorBar.append( this.jQcheckingSyntax ).append( this.jQvalidateSyntax );

		if ( !$( '#ace-editor-status-' + editorID ).length ) {
			this.jqEditorStatusbar.attr( 'id', 'ace-' + this.jqEditorStatusbar.attr( 'id' ) + '-' + editorID );
			this.jqEditorStatusbar.appendTo( this.jqWrapper );
		}

		$( '#' + editorID + '-wrapper' ).prepend( this.jqErrorBar );

		this.jQvalidateSyntax.hide();
		this.jQcheckingSyntax.show();

		ace.require( "ace/ext/emmet" );
		ace.require( 'ace/mode/html_completions' );

		this.editor = ace.edit( editorID );

		this.setEditorToolbar();

		$( object ).hide();
		this.textarea = $( object );

		this.vim = ace.require( 'ace/keyboard/vim' ).handler;
		this.emacs = ace.require( 'ace/keyboard/emacs' ).handler;
		ace.require( "ace/worker/worker_client" );

		this.editor.setOption( "enableEmmet", true );

		// set theme
		this.editor.setTheme( 'ace/theme/' + this.aceopts.theme );
		this.loadedThemes[this.aceopts.theme] = true;

		this.jqWrapper.addClass( 'ace-' + this.aceopts.theme );

		var classNames = $( object ).attr( 'class' );

		//set default behviours
		this.editor.setBehavioursEnabled( false );

		this.EnableIntellisense = false;
		this.noWorker = true;

		if ( classNames.match( /php/ ) ) {
			//set behviours
			this.editor.setBehavioursEnabled( true );

			this.noWorker = false;
			this.mode = 'php';
			this.EnableIntellisense = true;

			this.updateWorker();
		}
		else if ( classNames.match( /html/ ) || classNames.match( /xml/ ) ) {
			this.mode = 'html';
			this.EnableIntellisense = true;

			//set behviours
			this.editor.setBehavioursEnabled( false );
		}
		else if ( classNames.match( /css/ ) || classNames.match( /stylesheet/ ) ) {
			//set behviours
			this.editor.setBehavioursEnabled( true );

			this.mode = 'css';
			this.updateWorker();
		}
		else if ( classNames.match( /js/ ) || classNames.match( /javascript/ ) ) {
			//set behviours
			this.editor.setBehavioursEnabled( true );

			this.mode = 'javascript';
			this.updateWorker();
		}
		else if ( classNames.match( /pl/ ) || classNames.match( /perl/ ) ) {
			//set behviours
			this.editor.setBehavioursEnabled( true );

			this.mode = 'perl';
			this.stopWorker();
		}
		else if ( classNames.match( /json/ ) ) {
			//set behviours
			this.editor.setBehavioursEnabled( true );

			this.mode = 'json';
			this.stopWorker();
		}

		this.setEditorMode( this.mode );

		this.aceopts.htmlhint = false;

		if ( this.mode === 'html' || this.mode === 'javascript' || this.mode === 'php' || this.mode === 'css' ) {
			this.aceopts.htmlhint = true;
			this.EnableIntellisense = true;
		}

		ace.sourceEditor = this;

		this.Intellisense = ace.require( 'dcms_ace/intellisense' ).Intellisense;
		this.editor.Intellisense = new this.Intellisense( this.editor, this );

		/*
		 $('#' + editorID).on('keyup.sourceeditor', function (e) {
		 self.editor.Intellisense.currentEvent = e;
		 }).on('click.sourceeditor', function (e) {
		 self.editor.Intellisense.currentEvent = e;
		 });
		 */

		this.editor.setAnimatedScroll( true );

		// full line selection
		this.editor.setSelectionStyle( true );

		// highlight active line
		this.editor.setHighlightActiveLine( this.aceopts.highlight_active );

		// highlight selected word
		this.editor.setHighlightSelectedWord( this.aceopts.highlight_selected );

		// show invisibles
		this.editor.setShowInvisibles( this.aceopts.show_invisibles );

		// persistent hscroll
		this.editor.renderer.setHScrollBarAlwaysVisible( this.aceopts.persistent_hscroll );

		// show gutter
		this.editor.renderer.setShowGutter( this.aceopts.gutter );

		this.editor.getSession().setUseWrapMode( true );
		this.editor.getSession().setWrapLimitRange( 80 );

		// set soft tab
		this.editor.getSession().setUseSoftTabs( true );

		// set print margin
		this.editor.setShowPrintMargin( true );
		// allows @ symbol on mac
		this.editor.commands.removeCommand( 'fold' );

		// set fontsize
		$( '#' + editorID ).css( 'font-size', this.aceopts.fontsize );

        var value =  $( object ).val();
       // value = value.replace(/\u00a0/g, ' ');
       // value = this.utf8_decode( value );






		this.editor.setValue(value, -1 );
		this.editor.focus();
		this.editor.gotoLine( 1 );

		this.editor.on( "focus", function ( e )
		{
			focusedAceEdit = self;
			window.focusedAceEdit = self;

		} );

		this.editor.on( "blur", function ( e )
		{
			focusedAceEdit = null;
			window.focusedAceEdit = null;
			setTimeout( function ()
			{
				clearTimeout( cursorChangeTimeout );

				if ( self.editor && self.editor.Intellisense ) {

					if ( self.editor.Intellisense.isVisible && self.editor.jqEvent && !$( self.editor.jqEvent.target ).parents( '.ace-intellisense' ).length ) {
						self.editor.Intellisense.hide();
					}
				}
				$( '#contextmenu-' + self.editorID ).remove();
			}, 200 );
		} );


        Core.addShortcutHelp('Ctrl+Alt+F', 'Toggle Editor Fullscreen', true);

		// bind ctrl+enter to full screen mode.
		this.editor.commands.addCommand( {
			name: 'fullScreenEditing',
			bindKey: {
				win: 'Ctrl-Alt-F',
				mac: 'Command-Alt-F'
			},
			exec: function ( env, args, request )
			{
				self.fullscreenEdit()
			}
		} );

        Core.addShortcutHelp('Ctrl+Alt+P', 'Code Prettyfier', true);

		// pretty
		this.editor.commands.addCommand( {
			name: 'codePretty',
			bindKey: {
				win: 'Ctrl-Alt-P',
				mac: 'Command-Alt-P'
			},
			exec: function ( env, args, request )
			{
				self.reindentCode()
			}
		} );


        Core.addShortcutHelp('Ctrl+L', 'Go to Line', true);
        Core.addShortcutHelp('Ctrl+-', 'Increase Editor Font Size', true);
        Core.addShortcutHelp('Ctrl++', 'Decrease Editor Font Size', true);
        Core.addShortcutHelp('Ctrl+0', 'Reset Editor Font Size', true);

		// fontsize change
		this.editor.commands.addCommands( [
			{
				name: "gotoline",
				bindKey: {win: "Ctrl-L", mac: "Command-L"},
				exec: function ( editor )
				{
					var lineNum = prompt( "Go to Line", "" );
					if ( lineNum ) {
						var line = parseInt( lineNum, 10 );
						if ( !isNaN( line ) )
							editor.gotoLine( line );
					}
				},
				readOnly: true
			},
			{
				name: "increaseFontSize",
				bindKey: "Ctrl-+",
				exec: function ( editor )
				{
					var size = parseInt( editor.getFontSize(), 10 ) || 12;
					var nsize = Math.max( size + 1 || 16 );
					if ( nsize < 18 ) {
						editor.setFontSize( nsize );
					}
				}
			},
			{
				name: "decreaseFontSize",
				bindKey: "Ctrl+-",
				exec: function ( editor )
				{
					var size = parseInt( editor.getFontSize(), 10 ) || 12;
					var nsize = Math.max( size - 1 || 12 );
					if ( nsize > 9 ) {
						editor.setFontSize( nsize );
					}
				}
			},
			{
				name: "resetFontSize",
				bindKey: "Ctrl+0",
				exec: function ( editor )
				{
					editor.setFontSize( 12 );
				}
			},
			{
				name: "resetFontSize",
				bindKey: "Ctrl+Numpad0",
				exec: function ( editor )
				{
					editor.setFontSize( 12 );
				}
			}
		] );

		/*
		 var keyWordCompleter = {
		 getCompletions: function (editor, session, pos, prefix, callback) {
		 var state = editor.session.getState(pos.row);
		 var completions = session.$mode.getCompletions(state, session, pos, prefix);
		 callback(null, completions);
		 }
		 };
		 if (typeof this.editor.completers != 'array') {
		 this.editor.completers = new Array();
		 }



		 var completers = [keyWordCompleter];
		 this.editor.completers.push(completers);
		 ace.require("ace/ext/language_tools");
		 this.editor.setOptions({
		 enableBasicAutocompletion: true,
		 enableSnippets: true
		 });

		 var Autocomplete = ace.require('ace/autocomplete').Autocomplete;
		 var settings_menu = ace.require("ace/ext/settings_menu");
		 settings_menu.init(this.editor);

		 var whitespace = ace.require("ace/ext/whitespace");
		 this.editor.commands.addCommands(whitespace.commands);


		 this.editor.commands.addCommand({
		 name: "enter",
		 bindKey: {
		 win: "return",
		 mac: "return",
		 sender: "editor"
		 },
		 exec: function (e, n, r) {


		 },
		 multiSelectAction: "forEach"
		 });
		 */

		this.editor.commands.addCommand( {
			name: "rangle",
			bindKey: {
				win: ">",
				mac: ">",
				sender: "editor"
			},
			exec: function ( e, n, r )
			{
				return self.closeTag( e ), !0;
			},
			multiSelectAction: "forEach"
		} );

		this.editor.commands.addCommand( {
			name: "rangle2",
			bindKey: {
				win: "/",
				mac: "/",
				sender: "editor"
			},
			exec: function ( e, n, r )
			{
				return self.closeSelfCloseTag( e ), !0;
			},
			multiSelectAction: "forEach"
		} );

        Core.addShortcutHelp('Ctrl+Left', 'Display last hint', true);

		this.editor.commands.addCommand( {
			name: 'left',
			bindKey: {win: 'Ctrl-Left', mac: 'Command-Left'},
			exec: self.showLastHint,
			readOnly: true // false if this command should not apply in readOnly mode
		} );

        Core.addShortcutHelp('Ctrl+Right', 'Display next hint', true);
		this.editor.commands.addCommand( {
			name: 'right',
			bindKey: {win: 'Ctrl-Right', mac: 'Command-Right'},
			exec: self.showNextHint,
			readOnly: true // false if this command should not apply in readOnly mode
		} );

        Core.addShortcutHelp('Ctrl+Up', 'Display last hint', true);
		this.editor.commands.addCommand( {
			name: 'up',
			bindKey: {win: 'Ctrl-Up', mac: 'Command-Up'},
			exec: self.showLastHint,
			readOnly: true // false if this command should not apply in readOnly mode
		} );

        Core.addShortcutHelp('Ctrl+Down', 'Display next hint', true);
		this.editor.commands.addCommand( {
			name: 'down',
			bindKey: {win: 'Ctrl-Down', mac: 'Command-Down'},
			exec: self.showNextHint,
			readOnly: true // false if this command should not apply in readOnly mode
		} );

		if ( this.aceopts.autoCloseBrackets ) {
			this.editor.commands.addCommand( {
				name: "lbracket",
				bindKey: {
					win: "(",
					mac: "(",
					sender: "editor"
				},
				exec: function ( e, n, r )
				{
					return self.closeBracket( e, "(" ), !0;
				},
				multiSelectAction: "forEach"
			} );
			this.editor.commands.addCommand( {
				name: "lsquere",
				bindKey: {
					win: "[",
					mac: "[",
					sender: "editor"
				},
				exec: function ( e, n, r )
				{
					return self.closeBracket( e, "[" ), !0;
				},
				multiSelectAction: "forEach"
			} );
			this.editor.commands.addCommand( {
				name: "rbracket",
				bindKey: {
					win: ")",
					mac: ")",
					sender: "editor"
				},
				exec: function ( e, n, r )
				{
					return self.closeBracket( e, ")" ), !0;
				},
				multiSelectAction: "forEach"
			} );
			this.editor.commands.addCommand( {
				name: "rsquere",
				bindKey: {
					win: "]",
					mac: "]",
					sender: "editor"
				},
				exec: function ( e, n, r )
				{
					return self.closeBracket( e, "]" ), !0
				},
				multiSelectAction: "forEach"
			} );
			this.editor.commands.addCommand( {
				name: "lCurly",
				bindKey: {
					win: "{",
					mac: "{",
					sender: "editor"
				},
				exec: function ( e, n, r )
				{
					self.closeBracket( e, "{" )
				},
				multiSelectAction: "forEach"
			} );

			this.editor.commands.addCommand( {
				name: "rCurly",
				bindKey: {
					win: "}",
					mac: "}",
					sender: "editor"
				},
				exec: function ( e, n, r )
				{
					self.closeBracket( e, "}" )
				},
				multiSelectAction: "forEach"
			} );
		}
		else {
			this.editor.commands.removeCommands( ["lbracket", "lsquere", "lCurly", "rbracket", "rsquere", "rCurly"] )
		}

		/*
		 this.editor.commands.on("exec", function (e) {
		 console.log([e]);

		 if (e.command.name == 'golinedown' || e.command.name == 'golineup' && self.editor.Intellisense.isVisible) {
		 e.preventDefault();
		 return true;
		 }

		 }); */

		this.editor.commands.on( "afterExec", function ( e )
		{
			if ( self.EnableIntellisense ) {

				if ( e.command.name == "insertstring" && /^[\w.&:\s]$/.test( e.args ) || (e.command.name == 'backspace' && self.editor.Intellisense.isVisible) ) {
					clearTimeout( cursorChangeTimeout );



					var session = self.editor.getSession();
					var pos = self.editor.getCursorPosition();
					var range = self.editor.getSelectionRange();

					var line = session.getLine( pos.row );
					var prefix = self.retrievePrecedingIdentifier( line, pos.column );

					var basePos = self.editor.getCursorPosition();
					basePos.column -= prefix.length;
					var token = session.getTokenAt( pos.row, pos.column );

					if ( token ) {
						var tokenState = typeof token.state == "object" ? token.state[0] : token.state;

						self.editor.Intellisense.currentEvent = e;
						if ( self.editor.Intellisense.getCompletions( tokenState, session, pos, prefix, basePos, token ) ) {
							self.jQvalidateSyntax.hide();
							self.jQcheckingSyntax.show();
							self.updateHTMLHint();

							return;
						}
					}
				}

				if ( e.command.name == "insertstring" && line ) {
					//self.editor.Intellisense.hide();
					if ( /\}$/.test( line ) && !/\{\s*\t*\r*\n*[\$@a-z0-9\-_]*\.?[a-z0-9\-_]*\}$/i.test( line ) ) {
						self.closeCurly();
						return true;
					}
                    else {
                        if (token && token.value == ' ' ) {
                            self.insert( ' ', !0 );
                            return;
                        }
                    }
				}

				if ( e.command.name == "Return" ) {
					if ( self.editor.Intellisense.isVisible ) {
						// self.editor.Intellisense.hide();
						return true;
					}
					else {
						self.onEnter( e );
					}
				}

			}
		} );

		// Contextmenu event
		this.jqEditor.on( "contextmenu", function ( e, t )
		{

			if ( self.editor.Intellisense.isVisible ) {
				self.editor.Intellisense.hide();
			}
			if ( e.ctrlKey === !1 ) {
				// Prevent browser from opening the system context menu
				e.preventDefault();
				self.contextmenuInit();
				setTimeout( function ()
				{
					self.contextmenuShow( e );
				}, 200 );
			}
		} );

		this.TokenTooltip = ace.require( 'dcms_ace/token_tooltip' ).TokenTooltip;
		this.editor.tokenTooltip = new this.TokenTooltip( this.editor );

		this.updateHTMLHint();
		this.initStatusbar();

		setTimeout( function ()
		{
			self.editor.gotoLine( 1 );
		}, 400 );

		this.isDirty = false;

		return this;
	};

	this.destroy = function ()
	{
		clearTimeout( cursorChangeTimeout );
		if ( this.editor && this.editor.tokenTooltip ) {
			this.editor.tokenTooltip.destroy();
		}
		if ( this.editor ) {
			this.editor.destroy();
		}
		this.editor = null;
		this.Intellisense = null;

		if ( this.jqEditor ) {
			this.jqEditor.unbind();
			this.jqEditor.removeData();
		}
		this.jqEditor = null;


        Core.removeShortcutHelp('Ctrl+Up', true);
        Core.removeShortcutHelp('Ctrl+Down', true);
        Core.removeShortcutHelp('Ctrl+Left', true);
        Core.removeShortcutHelp('Ctrl+Right', true);
        Core.removeShortcutHelp('Ctrl++', true);
        Core.removeShortcutHelp('Ctrl+-', true);
        Core.removeShortcutHelp('Ctrl+0', true);
        Core.removeShortcutHelp('Ctrl+Alt+P', true);
        Core.removeShortcutHelp('Ctrl+Alt+L', true);


	};

	this.reset = function ()
	{

		//    var t = this.getCursorPosition();

		this.editor.setValue( this.textarea.val(), -1 );
		this.editor.focus();
		this.editor.gotoLine( 1 );

		this.isDirty = false;

		this.initStatusbar();
	};

	this.isDirty = function ()
	{
		return this.isDirty;
	};

	this.removeDirty = function ()
	{
		this.isDirty = false;
		if (typeof Win != 'undefined' && typeof Form != 'undefined') {
			if ( $( '#' + Win.windowID ).data( 'formID' ) ) {
				Form.resetDirty( $( '#' + $( '#' + Win.windowID ).data( 'formID' ) ) );
			}
		}
	};

	this.setDirty = function ()
	{

		if ( !this.isDirty && typeof Win != 'undefined' && typeof Form != 'undefined' ) {
			if ( $( '#' + Win.windowID ).data( 'formID' ) ) {
				Form.setDirty( null, $( '#' + $( '#' + Win.windowID ).data( 'formID' ) ), Win.windowID );
			}
		}
		this.isDirty = true;

	};

	this.stopWorker = function ()
	{
		this._worker && this._worker.terminate(), this._worker = null;

	};

	this.startWorker = function ()
	{
		var self = this;

		if ( typeof Worker != "undefined" && !this.noWorker )
			try {
				this._worker = this.$mode[ this.mode ].createWorker( this );

				this._worker.on( "error", function ( e )
				{
					self.setCustomHint( e.data );
				} );

			} catch ( t ) {
				console.log( "Could not load worker" ), console.log( t ), this._worker = null
			}
		else
			this._worker = null
	};

	this.refreshAfterResize = function ()
	{
		this.editor.tokenTooltip.destroy();
		this.editor.tokenTooltip = new this.TokenTooltip( this.editor );
	};

	this.fullscreenEdit = function ()
	{
		var editorDiv = this.jqWrapper;
		var editor = this.jqEditor;
		if ( this.editorToolbar !== null ) {

		}

		var hintBar = this.jqErrorBar;
		var parentBody = window.parent.document.body

		if ( editorDiv.hasClass( 'fullscreen' ) ) {
			// shut down full screen
			editorDiv.removeClass( 'fullscreen' );
			var tmp = editorDiv.data();
			editorDiv.height( tmp.origHeight ).width( tmp.origWidth ).css( {position: '', left: '', top: '', zIndex: ''} );

			tmp = editor.data();
			editor.height( tmp.origHeight ).width( tmp.origWidth );



			if (parentBody) {

				if (this.editorToolbar) { this.editorToolbar.insertAfter( $( '#dummy-tb' + this.editorID ) ); }
				editorDiv.insertAfter( $( '#dummy-' + this.editorID ) );


				$( '#dummy-' + this.editorID + ',#dummy-tb' + this.editorID + ',#ace-toolbar-' + this.editorID, parentBody ).remove();
			}
			else {

				if (this.editorToolbar) { this.editorToolbar.insertAfter( $( '#dummy-tb' + this.editorID ) ); }
				editorDiv.insertAfter( $( '#dummy-' + this.editorID ) );


				$( '#dummy-' + this.editorID + ',#dummy-tb' + this.editorID + ',#ace-toolbar-' + this.editorID ).remove();
			}

			this.fullscreen = false;
		} else {

			$( '<div id="dummy-tb' + this.editorID + '">' ).insertBefore( this.editorToolbar );
			$( '<div id="dummy-' + this.editorID + '">' ).insertBefore( editorDiv );


			if (this.editorToolbar) {
				var aceToolbar = $( '<div class="ace-toolbar" id="ace-toolbar-' + this.editorID + '">' );

				this.editorToolbar.appendTo( aceToolbar );
			}

			if ( $( '#fullscreenContainer' ).length ) {
				if (this.editorToolbar) {
					aceToolbar.css( {position: 'absolute', left: '0px', top: '0px', zIndex: 999999} ).appendTo( $( '#fullscreenContainer' ) );
				}
				editorDiv.appendTo( $( '#fullscreenContainer' ) );
			}
			else {

				if (parentBody) {



					if (this.editorToolbar) {
						aceToolbar.css( {position: 'absolute', left: '0px', top: '0px', zIndex: 999999} ).appendTo( $( parentBody ) );
					}
					editorDiv.appendTo( $(  parentBody ) );

				}
				else {

					if (this.editorToolbar) {
						aceToolbar.css( {position: 'absolute', left: '0px', top: '0px', zIndex: 999999} ).appendTo( $( 'body' ) );
					}
					editorDiv.appendTo( $( 'body' ) );
				}
			}

			// turn on full screen
			editorDiv.addClass( 'fullscreen' );
			editorDiv.data( 'origWidth', editorDiv.width() );
			editorDiv.data( 'origHeight', editorDiv.height() );

			editor.data( 'origWidth', editor.width() );
			editor.data( 'origHeight', editor.height() );

			editorDiv.css( {position: 'absolute', left: '0px', top: (aceToolbar ? aceToolbar.outerHeight( true ) : 0), zIndex: 999999} );

			var extra = 0;
			if ( hintBar.is( ':visible' ) ) {
				extra = hintBar.outerHeight( true );
			}

			var width, height;
			if (parentBody) {
				 width = $( window.parent.window ).width() * 0.999;
				 height = $( window.parent.window ).height() * 0.999;
			}
			else {
				 width = $( window ).width() * 0.999;
				 height = $( window ).height() * 0.999;
			}

			editorDiv.height( height ).width( width );
			editor.height( height - (aceToolbar ? aceToolbar.outerHeight( true ) : 0) - extra - this.jqEditorStatusbar.outerHeight( true ) ).width( width );

			this.fullscreen = true;
		}

		this.editor.renderer.onResize( true );
		this.editor.renderer.updateFull();
		this.editor.focus();
		this.refreshAfterResize();


		$(window ).trigger('resize');

	};

	this.contextmenuInit = function ()
	{

		var self = this;
		if ( $( '#contextmenu-' + self.editorID ).length ) {
			return;
		}

		var ul = $( '<ul>' ).attr( 'id', 'contextmenu-' + self.editorID ).addClass( 'contextmenu' );

		if ( $( '#copy-text' ).length == 0 ) {
			$( 'body' ).append( '<div id="copy-text" style="display:inline-block;height:0;width:0;overflow: hidden;position: absolute;top: -1000px;"></div>' );

		}

		var clipText = $( '#copy-text' ).html();

		ul.append( $( '<li id="' + self.editorID + '-Copy" class="firstitem">' ).append( '<span>Copy</span><em>Ctrl+C</em>' ).on( 'click', function ( e )
		{
			if ( !$( this ).hasClass( 'disabled' ) ) {
				self.CopyText();
				$( this ).parent().hide().remove();
			}
		} ) );

		ul.append( $( '<li id="' + self.editorID + '-Past" class="' + (clipText == '' ? ' disabled' : '') + '">' ).append( '<span>Past</span><em>Ctrl+P</em>' ).on( 'click', function ( e )
		{
			if ( !$( this ).hasClass( 'disabled' ) ) {
				self.PastText();
				$( this ).parent().hide().remove();
			}
		} ) );

		ul.append( $( '<li class="separator"/>' ) );

		ul.append( $( '<li id="' + self.editorID + '-Collapse">' ).append( '<span>Collapse Selection</span><em>Ctrl+Shift+C</em>' ).on( 'click', function ( e )
		{
			self.collapseSelection();
			$( this ).parent().hide().remove();
		} ) );

		ul.append( $( '<li id="' + self.editorID + '-Expand">' ).append( '<span>Expand Selection</span><em>Ctrl+Shift+E</em>' ).on( 'click', function ( e )
		{
			self.expandSelection();
			$( this ).parent().hide().remove();
		} ) );

		ul.append( $( '<li class="separator"/>' ) );

		ul.append( $( '<li>' ).append( '<span>Apply HTML Comment</span>' ).on( 'click', function ( e )
		{
			self.wrapSelection( "<!--", "-->" );
			$( this ).parent().hide().remove();
		} ) );
		ul.append( $( '<li>' ).append( '<span>Apply /* */ Comment</span>' ).on( 'click', function ( e )
		{
			self.wrapSelection( "/*", "*/" );
			$( this ).parent().hide().remove();
		} ) );
		ul.append( $( '<li>' ).append( '<span>Apply // Comment</span>' ).on( 'click', function ( e )
		{
			self.prependLineSelection( "//" );
			$( this ).parent().hide().remove();
		} ) );

		ul.append( $( '<li class="separator"/>' ) );

		ul.append( $( '<li>' ).append( '<span>Convert \' To "</span>' ).on( 'click', function ( e )
		{
			self.replaceInSelection( "'", '"' );
			$( this ).parent().hide().remove();
		} ) );
		ul.append( $( '<li>' ).append( '<span>Convert " To \'</span>' ).on( 'click', function ( e )
		{
			self.replaceInSelection( '"', "'" );
			$( this ).parent().hide().remove();
		} ) );
		ul.append( $( '<li>' ).append( '<span>Convert Tabs To Spaces</span>' ).on( 'click', function ( e )
		{
			self.replaceInSelection( "	", "  " );
			$( this ).parent().hide().remove();
		} ) );
		ul.append( $( '<li>' ).append( '<span>Convert Spaces To Tabs</span>' ).on( 'click', function ( e )
		{
			self.replaceInSelection( "  ", "	" );
			$( this ).parent().hide().remove();
		} ) );
		ul.append( $( '<li>' ).append( '<span>Convert To Uppercase</span>' ).on( 'click', function ( e )
		{
			self.selectionToUppercase();
			$( this ).parent().hide().remove();
		} ) );
		ul.append( $( '<li class="lastitem">' ).append( '<span>Convert To Lowercase</span>' ).on( 'click', function ( e )
		{
			self.selectionToLowercase();
			$( this ).parent().hide().remove();
		} ) );

		if ( $( '#fullscreenContainer' ).length ) {
			$( '#fullscreenContainer' ).append( ul.hide() );
		}
		else {
			$( 'body' ).append( ul.hide() );
		}
	};

	this.contextmenuShow = function ( e, n )
	{

		var self = this, r = this.getEditor(), i = r.getCursorPosition(), s = this.getEditor().getSession().getTokenAt( i.row, i.column );
		if ( typeof s == 'undefined' ) {
			return;
		}
		var height = $( '#contextmenu-' + this.editorID ).height(), width = $( '#contextmenu-' + this.editorID ).width();
		var top = e.pageY + 5, left = e.pageX + 5;

		if ( (top + height + 50) > $( 'body' ).height() ) {
			top -= (height + 10);
		}
		if ( (left + width) > $( 'body' ).width() ) {
			left -= (width - 10);
		}

		$( '#contextmenu-' + this.editorID ).css( {position: 'absolute', zIndex: 99999, left: left, top: top} ).show( 0, function ()
		{
			if ( self.editor.Intellisense.isVisible ) {
				self.editor.Intellisense.hide();
			}
		} );
	};

	this.CopyText = function ()
	{
		if ( $( '#copy-text' ).length == 0 ) {
			$( 'body' ).append( '<div id="copy-text" style="display:inline-block;height:0;width:0;overflow: hidden;position: absolute;top: -1000px;"></div>' );
		}
		var clipText = this.getSelection();
		$( '#copy-text' ).html( clipText );
	};

	this.PastText = function ()
	{
		if ( $( '#copy-text' ).length == 0 ) {
			$( 'body' ).append( '<div id="copy-text" style="display:inline-block;height:0;width:0;overflow: hidden;position: absolute;top: -1000px;"></div>' );
		}
		var clipText = $( '#copy-text' ).html();
		this.editor.insert( clipText );
		$( '#copy-text' ).empty();

	};

	this.onEnter = function ()
	{
		var e = this.getCursorPosition(),
			r = this.getLine( e.row ),
			str = r.slice( e.column - 1, e.column );

		if ( str == "{" ) {
			this.closeCurly();
			return;
		}

		if ( /\}$/.test( r ) ) {
			this.closeCurly();
		}
		else {
			this.newLine();
		}
	};

	this.newLine = function ()
	{
		return this.editor.insert( "\n" );
	};

	this.closeBracket = function ( e, t )
	{
		var closing;

		var pos = this.editor.getCursorPosition();
		var line = this.editor.session.getLine( pos.row );

		if ( /(jQuery|\$)$/.test( line ) ) {
			//this.insert('(');
			//return false;
		}

		if ( ["(", "[", , "{"].indexOf( t ) !== -1 ) {
			switch ( t ) {
				case "(":
					closing = ")";
					break;
				case "[":
					closing = "]";
					break;
				case "{":
					closing = "}"
			}
			this.insert( t + closing ), this.navigateLeft();
		} else if ( [")", "]", "}"].indexOf( t ) !== -1 ) {
			this.insert( t );
			var n = this.getSelectionRange(),
				r = this.getLine( n.start.row );
			r.substr( n.start.column, 1 ) == t && this.removeRight();
		}
	};

	this.closeCurly = function ()
	{
		var i, e = this.getSelectionRange(),
			t = this.getLine( e.start.row ),
			r = "";

		if ( t.match( /\{[@\$a-z0-9_]+?/ ) ) {
			return;
		}

		for ( i = 0; i < t.length; i++ ) {
			if ( !t[i].match( /\s/ ) )
				break;
			r += t[i];
		}
		var s = "	";
		var o = t.slice( e.start.column, e.start.column + 1 );
		o == "}" ? this.insert( "\n" + r + s + "\n" + r.replace( s, '' ) ) : this.insert( "\n" + r + s + "\n" );
		var u = {
			start: {
				row: e.start.row,
				column: e.start.column
			},
			end: {
				row: e.end.row,
				column: e.end.column
			}
		};
		return u.start.column += 3, u.start.column += r.length, u.start.row += 1, u.end.column = u.start.column, u.end.row = u.start.row, this.setSelectionRange( u ), !0;
	};

	this.navigateLeft = function ( e )
	{
		return this.editor.navigateLeft( e );
	};
	this.navigateRight = function ( e )
	{
		return this.editor.navigateRight( e );
	};
	this.navigateUp = function ( e )
	{
		if ( !this.editor.Intellisense.isVisible ) {
			return this.editor.navigateUp( e );
		}
	};
	this.navigateDown = function ( e )
	{
		if ( !this.editor.Intellisense.isVisible ) {
			return this.editor.navigateDown( e );
		}
	};

	this.undo = function ()
	{
		this.editor.undo();
	};

	this.redo = function ()
	{
		this.editor.redo();
	};

	this.selectionToUppercase = function ()
	{
		var e = this.getSelection( this.editor );
		this.insert( e.toUpperCase(), !0 );
	};
	this.selectionToLowercase = function ()
	{
		var e = this.getSelection( this.editor );
		this.insert( e.toLowerCase(), !0 );
	};

	this.wrapSelection = function ( e, t )
	{
		var n = this.getSelection( this.editor );
		n.substr( 0, e.length ) == e && n.substr( n.length - t.length ) == t ? n = n.substr( e.length, n.length - e.length - t.length ) : n = e + n + t, this.insert( n, !0 );
	};

	this.prependLineSelection = function ( e )
	{
		var t = this.getSelection( this.editor );
		this.insert( e + t.replace( new RegExp( "\r\n", "g" ), "\r\n" + e ), !0 );
	}, this.appendLineSelection = function ( e )
	{
		var t = this.getSelection( this.editor );
		this.insert( t.replace( new RegExp( "\r\n", "g" ), e + "\r\n" ) + e, !0 );
	}, this.replaceInSelection = function ( e, t )
	{
		var n = this.getSelection( this.editor );
		this.insert( n.replace( new RegExp( e, "g" ), t ), !0 );
	}, this.collapseSelection = function ( e )
	{
		this.editor.commands.exec( "fold", this.editor );
	}, this.expandSelection = function ( e )
	{
		this.editor.commands.exec( "unfold", this.editor );
	};

	this.getSelection = function ()
	{
		return this.editor.getSession().doc.getTextRange( this.editor.getSelectionRange() );
	};
	this.setSelection = function ( e )
	{
		return this.insert( e );
	};
	this.getCursorPosition = function ( e )
	{
		return e ? this.editor.selection.getSelectionLead() : this.editor.getCursorPosition();
	};
	this.setCursorPosition = function ( e )
	{
		return this.editor.moveCursorToPosition( e );
	};
	this.getSelectionRange = function ()
	{
		return this.editor.getSelectionRange();
	};
	this.setSelectionRange = function ( e )
	{
		var t = ace.require( "ace/range" ).Range;
		return this.editor.selection.setSelectionRange( new t( e.start.row, e.start.column, e.end.row, e.end.column ) );
	};

	this.getLine = function ( e )
	{
		return this.editor.session.getLine( e );
	};

	this.setLine = function ( e, t )
	{
		return this.gotoLine( e ), this.editor.selection.selectLineEnd(), this.insert( t );
	};

	this.gotoLine = function ( e )
	{
		typeof e != "undefined" ? this.editor.gotoLine( parseInt( e, 10 ) ) : false;
	};

	this.removeRight = function ()
	{
		return this.editor.remove( "right" );
	};

	/**
	 *
	 * @param {type} e
	 * @param {type} t
	 * @returns {undefined}
	 */
	this.insert = function ( e, t )
	{
		if ( t )
			var n = this.getSelectionRange( this.editor );
		this.editor.insert( e );
		if ( t ) {
			var r = this.getSelectionRange( this.editor );
			this.setSelectionRange( {
				start: n.start,
				end: r.end
			} );
		}
	};

	/**
	 *
	 * @param {type} tagName
	 * @returns {undefined}
	 */
	this.insertTag = function ( tagName )
	{
		var n = this.getCursorPosition();

		var re = new RegExp(tagName, 'ig');



		if ( sourceEditor_selfCloseTags.join(' ').match( re ) ) {
			this.wrapSelection( "<" + tagName + "/>", "" );
		}
		else {
			this.wrapSelection( "<" + tagName + ">", "</" + tagName + ">" );
			this.navigateLeft();
			this.setCursorPosition( {row: n.row, column: n.column + tagName.length + 2} );
		}

		this.editor.focus();
	};

	/**
	 *
	 * @param {type} source
	 * @returns {Boolean|AceEdit.looks_like_html.trimmed}
	 */

	function looks_like_html( source )
	{
		// <foo> - looks like html
		// <!--\nalert('foo!');\n--> - doesn't look like html
		var trimmed = source.replace( /^[ \t\n\r]+/, '' );
		var comment_mark = '<' + '!-' + '-';
		return (trimmed && (trimmed.substring( 0, 1 ) === '<' && trimmed.substring( 0, 4 ) !== comment_mark));
	}

	/**
	 *
	 * @param {type} regex
	 * @param {type} haystack
	 * @returns {AceEdit.preg_match_all.matchArray|Array}
	 */

	function preg_match_all( regex, haystack )
	{
		var globalRegex = new RegExp( regex, 'mig' );
		var globalMatch = haystack.match( globalRegex );
		var matchArray = new Array();
		for ( var i in globalMatch ) {
			if ( typeof globalMatch[i] === 'string' ) {
				var nonGlobalRegex = new RegExp( regex );
				var nonGlobalMatch = globalMatch[i].match( nonGlobalRegex );
				matchArray.push( nonGlobalMatch[0] );
			}
		}
		return matchArray;
	}

	/**
	 *
	 * @param {type} pattern
	 * @param {type} replace
	 * @param {type} subject
	 * @param {type} limit
	 * @returns {AceEdit.preg_replace.rtn}
	 */
	function preg_replace( pattern, replace, subject, limit )
	{
		if ( limit === undefined ) {
			limit = -1;
		}

		var _flag = pattern.substr( pattern.lastIndexOf( pattern[0] ) + 1 );
		var _pattern = pattern.substr( 1, pattern.lastIndexOf( pattern[0] ) - 1 );
		var reg = new RegExp( _pattern, _flag ),
			rs = null,
			res = [],
			x = 0,
			y = 0,
			rtn = subject;

		var tmp = [];
		if ( limit === -1 ) {
			do {
				tmp = reg.exec( subject );
				if ( tmp !== null ) {
					res.push( tmp );
				}
			} while ( tmp !== null && _flag.indexOf( 'g' ) !== -1 );
		}
		else {
			res.push( reg.exec( subject ) );
		}

		if ( res === null || res[0] === null ) {
			return rtn;
		}

		for ( x = res.length - 1; x > -1; x-- ) {
			tmp = replace;

			for ( y = res[x].length; y > -1; y-- ) {
				tmp = tmp.replace( '${' + y + '}', res[x][y] )
					.replace( '$' + y, res[x][y] )
					.replace( '\\' + y, res[x][y] );
			}
			rtn = rtn.replace( res[x][0], tmp );
		}
		return rtn;
	}
	;

	/**
	 *
	 * @param {type} str
	 * @param {type} times
	 * @returns {String}
	 */
	this.repeat = function ( str, times )
	{
		return (new Array( times + 1 )).join( str );
	};

	var definitions = {

		tag: { regexp: /^<\/?[a-zA-Z][a-zA-Z0-9]*[]*>$/, skip: false },
		attribute: { regexp: /^[a-zA-Z]+[a-zA-Z0-9]*\s*=\s*$/, skip: false },
		string: { regexp: /^\"[^\"]*\"|\'[^\']*\'$/, skip: false },
		//	decimal_integer :   { regexp : /^[1-9]*\d+$/,                  skip : false },
		//	left_parenthesis :  { regexp : /^\($/,                         skip : false },
		//	right_parenthesis : { regexp : /^\)$/,                         skip : false },
		//	comma :             { regexp : /^,$/,                          skip : false },
		whitespace: { regexp: /^[\t \n]$/, skip: true  }
	};

	this.Lexer = function ( string )
	{
		var index = 0;
		var length = 1;
		var arrayPosition = -1;
		var _Lexer = this;
		_Lexer.tokens = [];
		_Lexer.lexemes = [];
		_Lexer.token = null;
		_Lexer.lexeme = null;
		_Lexer.bof = true;
		_Lexer.eof = false;
		_Lexer.line_number = 0;
		_Lexer.char_position = 0;

		// fill tokens and lexemes
		while ( index + length <= string.length ) {
			var small = string.substr( index, length );
			var big = string.substr( index, length + 1 );
			for ( var def in definitions ) {
				var smallmatch = small.match( definitions[def].regexp ) !== null;
				var bigmatch = big.match( definitions[def].regexp ) !== null;
				if ( smallmatch && ( !bigmatch || small == big ) ) {
					// found a token
					index += length;
					length = 0;
					_Lexer.tokens.push( def );
					_Lexer.lexemes.push( small );
					break;
				}
			}
			length++;
		}

		_Lexer.next = function ( count )
		{
			if ( count == undefined ) count = 1;
			while ( count-- > 0 ) {
				arrayPosition++;
				if ( arrayPosition < _Lexer.tokens.length ) {
					_Lexer.token = _Lexer.tokens[arrayPosition];
					_Lexer.lexeme = _Lexer.lexemes[arrayPosition];
					_Lexer.bof = false;
					_Lexer.char_position += _Lexer.lexeme.length;
					if ( definitions[_Lexer.token].skip ) {
						if ( _Lexer.token == 'newline' ) {
							_Lexer.line_number++;
							_Lexer.char_position = 0;
						}
						_Lexer.next();
					}
				} else {
					_Lexer.token = 'EOF';
					_Lexer.lexeme = null;
					_Lexer.eof = true;
				}
			}
		}

		_Lexer.prev = function ( count )
		{
			if ( count == undefined ) count = 1;
			while ( count-- > 0 ) {
				arrayPosition--;
				if ( arrayPosition-- > 0 ) {
					_Lexer.token = _Lexer.tokens[arrayPosition];
					_Lexer.lexeme = _Lexer.lexemes[arrayPosition];
					_Lexer.eof = false;
					_Lexer.char_position -= _Lexer.lexeme.length;
					if ( definitions[_Lexer.token].skip ) {
						if ( _Lexer.token == 'newline' ) {
							_Lexer.line_number--;
							_Lexer.char_position = 0;
						}
						_Lexer.prev();
					}
				} else {
					_Lexer.token = 'BOF';
					_Lexer.lexeme = null;
					_Lexer.bof = true;
					break;
				}
			}
		}
	}

	this._formatXml = function ( xml, options )
	{
		var multi_parser, indent_size, indent_character, max_char, brace_style;
		options = options || {};
		indent_size = options.indent_size || 4;
		indent_character = options.indent_char || " ";
		brace_style = options.brace_style || "collapse";
		max_char = options.max_char || "70";
		unformatted = options.unformatted || ["a"];

		function Parser()
		{
			this.pos = 0;
			this.token = "";
			this.current_mode = "CONTENT";
			this.tags = {
				parent: "parent1",
				parentcount: 1,
				parent1: ""
			};
			this.tag_type = "";
			this.token_text = this.last_token = this.last_text = this.token_type = "";
			this.Utils = {
				whitespace: "\n\r\t ".split( "" ),
				single_token: "br,input,link,meta,!doctype,basefont,base,area,hr,wbr,param,img,isindex,?xml,embed".split( "," ),
				extra_liners: "head,body,/html".split( "," ),
				in_array: function ( what, arr )
				{
					for ( var i = 0; i < arr.length; i++ ) if ( what === arr[i] ) return true;
					return false
				}
			};
			this.get_content = function ()
			{
				var input_char = "";
				var content = [];
				var space = false;
				while ( this.input.charAt( this.pos ) !== "<" ) {
					if ( this.pos >= this.input.length ) return content.length ? content.join( "" ) : ["", "TK_EOF"];
					input_char = this.input.charAt( this.pos );
					this.pos++;
					this.line_char_count++;
					if ( this.Utils.in_array( input_char, this.Utils.whitespace ) ) {
						if ( content.length ) space = true;
						this.line_char_count--;
						continue
					} else if ( space ) {
						if ( this.line_char_count >= this.max_char ) {
							content.push( "\n" );
							for ( var i = 0; i < this.indent_level; i++ ) content.push( this.indent_string );
							this.line_char_count = 0
						} else {
							content.push( " " );
							this.line_char_count++
						}
						space = false
					}
					content.push( input_char )
				}
				return content.length ? content.join( "" ) : ""
			};
			this.get_script = function ()
			{
				var input_char = "";
				var content = [];
				var reg_match = new RegExp( "<\/script" + ">", "igm" );
				reg_match.lastIndex = this.pos;
				var reg_array = reg_match.exec( this.input );
				var end_script = reg_array ? reg_array.index : this.input.length;
				while ( this.pos < end_script ) {
					if ( this.pos >= this.input.length ) return content.length ? content.join( "" ) : ["", "TK_EOF"];
					input_char = this.input.charAt( this.pos );
					this.pos++;
					content.push( input_char )
				}
				return content.length ? content.join( "" ) : ""
			};
			this.record_tag = function ( tag )
			{
				if ( this.tags[tag + "count"] ) {
					this.tags[tag + "count"]++;
					this.tags[tag + this.tags[tag + "count"]] = this.indent_level
				} else {
					this.tags[tag + "count"] = 1;
					this.tags[tag + this.tags[tag + "count"]] = this.indent_level
				}
				this.tags[tag + this.tags[tag + "count"] + "parent"] = this.tags.parent;
				this.tags.parent = tag + this.tags[tag + "count"]
			};
			this.retrieve_tag = function ( tag )
			{
				if ( this.tags[tag + "count"] ) {
					var temp_parent = this.tags.parent;
					while ( temp_parent ) {
						if ( tag + this.tags[tag + "count"] === temp_parent ) break;
						temp_parent = this.tags[temp_parent + "parent"]
					}
					if ( temp_parent ) {
						this.indent_level = this.tags[tag + this.tags[tag + "count"]];
						this.tags.parent = this.tags[temp_parent + "parent"]
					}
					delete this.tags[tag + this.tags[tag + "count"] + "parent"];
					delete this.tags[tag + this.tags[tag + "count"]];
					if ( this.tags[tag + "count"] == 1 ) delete this.tags[tag + "count"];
					else this.tags[tag + "count"]--
				}
			};
			this.get_tag = function ()
			{
				var input_char = "";
				var content = [];
				var space = false;
				do {
					if ( this.pos >= this.input.length ) return content.length ? content.join( "" ) : ["", "TK_EOF"];
					input_char = this.input.charAt( this.pos );
					this.pos++;
					this.line_char_count++;
					if ( this.Utils.in_array( input_char, this.Utils.whitespace ) ) {
						space = true;
						this.line_char_count--;
						continue
					}
					if ( input_char === "'" || input_char === '"' ) if ( !content[1] || content[1] !== "!" ) {
						input_char += this.get_unformatted( input_char );
						space = true
					}
					if ( input_char === "=" ) space = false;
					if ( content.length && content[content.length - 1] !== "=" && input_char !== ">" && space ) {
						if ( this.line_char_count >= this.max_char ) {
							this.print_newline( false, content );
							this.line_char_count = 0
						} else {
							content.push( " " );
							this.line_char_count++
						}
						space = false
					}
					content.push( input_char )
				} while ( input_char !== ">" );

				var tag_complete = content.join( "" );
				var tag_index;
				if ( tag_complete.indexOf( " " ) != -1 ) tag_index = tag_complete.indexOf( " " );
				else tag_index = tag_complete.indexOf( ">" );
				var tag_check = tag_complete.substring( 1, tag_index ).toLowerCase();
				if ( tag_complete.charAt( tag_complete.length - 2 ) === "/" || this.Utils.in_array( tag_check, this.Utils.single_token ) ) this.tag_type = "SINGLE";
				else if ( tag_check === "script" ) {
					this.record_tag( tag_check );
					this.tag_type = "SCRIPT"
				} else if ( tag_check === "style" ) {
					this.record_tag( tag_check );
					this.tag_type = "STYLE"
				} else if ( this.Utils.in_array( tag_check, unformatted ) ) {
					var comment = this.get_unformatted( "</" + tag_check + ">", tag_complete );
					content.push( comment );
					this.tag_type = "SINGLE"
				} else if ( tag_check.charAt( 0 ) === "!" ) if ( tag_check.indexOf( "[if" ) != -1 ) {
					if ( tag_complete.indexOf( "!IE" ) != -1 ) {
						var comment = this.get_unformatted( "--\>", tag_complete );
						content.push( comment )
					}
					this.tag_type = "START"
				} else if ( tag_check.indexOf( "[endif" ) != -1 ) {
					this.tag_type = "END";
					this.unindent()
				} else if ( tag_check.indexOf( "[cdata[" ) != -1 ) {
					var comment = this.get_unformatted( "]]\>", tag_complete );
					content.push( comment );
					this.tag_type = "SINGLE"
				} else {
					var comment = this.get_unformatted( "--\>", tag_complete );
					content.push( comment );
					this.tag_type = "SINGLE"
				} else {
					if ( tag_check.charAt( 0 ) === "/" ) {
						this.retrieve_tag( tag_check.substring( 1 ) );
						this.tag_type = "END"
					} else {
						this.record_tag( tag_check );
						this.tag_type = "START"
					}
					if ( this.Utils.in_array( tag_check, this.Utils.extra_liners ) ) this.print_newline( true, this.output )
				}
				return content.join( "" )
			};
			this.get_unformatted = function ( delimiter, orig_tag )
			{
				if ( orig_tag && orig_tag.indexOf( delimiter ) != -1 ) return "";
				var input_char = "";
				var content = "";
				var space = true;
				do {
					if ( this.pos >= this.input.length ) return content;
					input_char = this.input.charAt( this.pos );
					this.pos++;
					if ( this.Utils.in_array( input_char, this.Utils.whitespace ) ) {
						if ( !space ) {
							this.line_char_count--;
							continue
						}
						if ( input_char === "\n" || input_char === "\r" ) {
							content += "\n";
							this.line_char_count = 0;
							continue
						}
					}
					content += input_char;
					this.line_char_count++;
					space = true
				} while ( content.indexOf( delimiter ) == -1 );
				return content
			};
			this.get_token = function ()
			{
				var token;
				if ( this.last_token === "TK_TAG_SCRIPT" ) {
					var temp_token = this.get_script();
					if ( typeof temp_token !== "string" ) return temp_token;
					token = js_beautify( temp_token.replace( /^[\r\n]+/, "" ), {
						"indent_size": this.indent_size,
						"indent_char": this.indent_character,
						"brace_style": this.brace_style
					} );
					return [token, "TK_CONTENT"]
				}
				if ( this.current_mode === "CONTENT" ) {
					token = this.get_content();
					if ( typeof token !== "string" ) return token;
					else return [token, "TK_CONTENT"]
				}
				if ( this.current_mode === "TAG" ) {
					token = this.get_tag();
					if ( typeof token !== "string" ) return token;
					else {
						var tag_name_type = "TK_TAG_" + this.tag_type;
						return [token, tag_name_type]
					}
				}
			};
			this.printer = function ( js_source, indent_character, indent_size, max_char, brace_style )
			{
				this.input = js_source || "";
				this.output = [];
				this.indent_character = indent_character;
				this.indent_string = "";
				this.indent_size = indent_size;
				this.brace_style = brace_style;
				this.indent_level = 0;
				this.max_char = max_char;
				this.line_char_count = 0;
				for ( var i = 0; i < this.indent_size; i++ ) this.indent_string += this.indent_character;
				this.print_newline = function ( ignore, arr )
				{
					this.line_char_count = 0;
					if ( !arr || !arr.length ) return;
					if ( !ignore ) while ( this.Utils.in_array( arr[arr.length - 1], this.Utils.whitespace ) ) arr.pop();
					arr.push( "\n" );
					for ( var i = 0; i < this.indent_level; i++ ) arr.push( this.indent_string )
				};
				this.print_token = function ( text )
				{
					this.output.push( text )
				};
				this.indent = function ()
				{
					this.indent_level++
				};
				this.unindent = function ()
				{
					if ( this.indent_level > 0 ) this.indent_level--
				}
			};
			return this
		}

		multi_parser = new Parser;
		multi_parser.printer( xml, indent_character, indent_size, max_char, brace_style );
		while ( true ) {
			var t = multi_parser.get_token();
			multi_parser.token_text = t[0];
			multi_parser.token_type = t[1];
			if ( multi_parser.token_type === "TK_EOF" ) break;
			switch ( multi_parser.token_type ) {
				case "TK_TAG_START":
				case "TK_TAG_STYLE":
					multi_parser.print_newline( false, multi_parser.output );
					multi_parser.print_token( multi_parser.token_text );
					multi_parser.indent();
					multi_parser.current_mode = "CONTENT";
					break;
				case "TK_TAG_SCRIPT":
					multi_parser.print_newline( false, multi_parser.output );
					multi_parser.print_token( multi_parser.token_text );
					multi_parser.current_mode = "CONTENT";
					break;
				case "TK_TAG_END":
					multi_parser.print_newline( true, multi_parser.output );
					multi_parser.print_token( multi_parser.token_text );
					multi_parser.current_mode = "CONTENT";
					break;
				case "TK_TAG_SINGLE":
					multi_parser.print_newline( false, multi_parser.output );
					multi_parser.print_token( multi_parser.token_text );
					multi_parser.current_mode = "CONTENT";
					break;
				case "TK_CONTENT":
					if ( multi_parser.token_text !== "" ) {
						multi_parser.print_newline( false, multi_parser.output );
						multi_parser.print_token( multi_parser.token_text )
					}
					multi_parser.current_mode = "TAG";
					break
			}
			multi_parser.last_token = multi_parser.token_type;
			multi_parser.last_text = multi_parser.token_text
		}
		return multi_parser.output.join( "" )
	};

	this.s = function ()
	{
		if ( this.editor.mode == 'js' ) {
			return false;
		}

		var TokenIterator = ace.require( "ace/token_iterator" ).TokenIterator;
		var iterator = new TokenIterator( this.editor.getSession(), 0, 0 );
		var token = iterator.getCurrentToken();

		var code = '';

		var newLines = [
			{
				type: 'support.php_tag',
				value: '<?php'
			},
			{
				type: 'support.php_tag',
				value: '<?'
			},
			{
				type: 'support.php_tag',
				value: '?>'
			},
			{
				type: 'paren.lparen',
				value: '{',
				breakBefore: true,
				indent: true
			},
			{
				type: 'paren.rparen',
				value: '}',
				indent: false
			},
			{
				type: 'comment'
			},
			{
				type: 'text',
				value: ';'
			},
			{
				type: 'text',
				value: ':',
				context: 'php'
			},
			{
				type: 'keyword',
				value: 'case',
				indent: true,
				dontBreak: true
			},
			{
				type: 'keyword',
				value: 'default',
				indent: true,
				dontBreak: true
			},
			{
				type: 'keyword',
				value: 'break',
				indent: false,
				dontBreak: true
			},
			{
				type: 'punctuation.doctype.end',
				value: '>'
			},
			{
				type: 'meta.tag.punctuation.end',
				value: '>'
			},
			{
				type: 'meta.tag.punctuation.begin',
				value: '<',
				blockTag: true,
				indent: true,
				dontBreak: true
			},
			{
				type: 'meta.tag.punctuation.begin',
				value: '</',
				indent: false,
				breakBefore: true,
				dontBreak: true
			},
			{
				type: 'punctuation.operator',
				value: ';'
			}
		];

		var spaces = [
			{
				type: 'xml-pe',
				prepend: true
			},
			{
				type: 'entity.other.attribute-name',
				prepend: true
			},
			{
				type: 'storage.type',
				value: 'var',
				append: true
			},
			{
				type: 'storage.type',
				value: 'function',
				append: true
			},
			{
				type: 'keyword.operator',
				value: '='
			},
			{
				type: 'keyword',
				value: 'as',
				prepend: true,
				append: true
			},
			{
				type: 'keyword',
				value: 'function',
				append: true
			},
			{
				type: 'support.function',
				next: /[^\(]/,
				append: true
			},
			{
				type: 'keyword',
				value: 'or',
				append: true,
				prepend: true
			},
			{
				type: 'keyword',
				value: 'and',
				append: true,
				prepend: true
			},
			{
				type: 'keyword',
				value: 'case',
				append: true
			},
			{
				type: 'keyword.operator',
				value: '||',
				append: true,
				prepend: true
			},
			{
				type: 'keyword.operator',
				value: '&&',
				append: true,
				prepend: true
			}
		];

		var single_tags = ['!doctype', 'area', 'base', 'br', 'hr', 'input', 'img', 'link', 'meta'];
		var indentation = 0;
		var dontBreak = false;
		var tag;
		var lastTag;
		var lastToken = {};
		var nextTag;
		var nextToken = {};
		var breakAdded = false;
		var value = '';

		//get context
		var context = this.editor.mode !== 'php' ? this.editor.mode : 'html';

		while ( token !== null ) {
			console.log( token );

			if ( !token ) {
				token = iterator.stepForward();
				continue;
			}

			//change syntax
			//php
			if ( token.type == 'support.php_tag' && token.value != '?>' ) {
				context = 'php';
			}
			else if ( token.type == 'support.php_tag' && token.value == '?>' ) {
				context = 'html';
			}
			//css
			else if ( token.type == 'meta.tag.name.style' && context != 'css' ) {
				context = 'css';
			}
			else if ( token.type == 'meta.tag.name.style' && context == 'css' ) {
				context = 'html';
			}
			//js
			else if ( token.type == 'meta.tag.name.script' && context != 'js' ) {
				context = 'js';
			}
			else if ( token.type == 'meta.tag.name.script' && context == 'js' ) {
				context = 'html';
			}

			nextToken = iterator.stepForward();

			//tag name
			if ( nextToken && nextToken.type.indexOf( 'meta.tag.name' ) == 0 ) {
				nextTag = nextToken.value;
			}

			//don't linebreak
			if ( lastToken.type == 'support.php_tag' && lastToken.value == '<?=' ) {
				dontBreak = true;
			}

			//lowercase
			if ( token.type == 'meta.tag.name' ) {
				token.value = token.value.toLowerCase();
			}

			//trim spaces
			if ( token.type == 'text' ) {
				token.value = token.value.trim();
			}

			//skip empty tokens
			if ( !token.value ) {
				token = nextToken;
				continue;
			}

			//put spaces back in
			value = token.value;
			for ( var i in spaces ) {
				if (
					token.type == spaces[i].type &&
						(!spaces[i].value || token.value == spaces[i].value) &&
						(
							nextToken &&
								(!spaces[i].next || spaces[i].next.test( nextToken.value ))
							)
					) {
					if ( spaces[i].prepend ) {
						value = ' ' + token.value;
					}

					if ( spaces[i].append ) {
						value += ' ';
					}
				}
			}

			//tag name
			if ( token.type.indexOf( 'meta.tag.name' ) == 0 ) {
				tag = token.value;
				console.log( tag );
			}

			//new line before
			breakAdded = false;

			//outdent
			for ( i in newLines ) {
				if (
					token.type == newLines[i].type &&
						(
							!newLines[i].value ||
								token.value == newLines[i].value
							) &&
						(
							!newLines[i].blockTag ||
								single_tags.indexOf( nextTag ) === -1
							) &&
						(
							!newLines[i].context ||
								newLines[i].context === context
							)
					) {
					if ( newLines[i].indent === false ) {
						indentation--;
					}

					if ( newLines[i].breakBefore ) {
						code += "\n";
						breakAdded = true;

						//indent
						for ( i = 0; i < indentation; i++ ) {
							code += "\t";
						}
					}

					break;
				}
			}

			if ( dontBreak === false ) {
				for ( i in newLines ) {
					if (
						lastToken.type == newLines[i].type &&
							(
								!newLines[i].value || lastToken.value == newLines[i].value
								) &&
							(
								!newLines[i].blockTag ||
									single_tags.indexOf( tag ) === -1
								) &&
							(
								!newLines[i].context ||
									newLines[i].context === context
								)
						) {
						if ( newLines[i].indent === true ) {
							indentation++;
						}

						if ( !newLines[i].dontBreak && !breakAdded ) {
							code += "\n";

							//indent
							for ( i = 0; i < indentation; i++ ) {
								code += "\t";
							}
						}

						break;
					}
				}
			}

			code += value;

			//linebreaks back on after end short php tag
			if ( lastToken.type == 'support.php_tag' && lastToken.value == '?>' ) {
				dontBreak = false;
			}

			//next token
			lastTag = tag;

			lastToken = token;

			token = nextToken;

			if ( token === null ) {
				break;
			}
		}

		console.log( code );
	}

	/**
	 * Format the giving Html Code
	 * @param string xml
	 * @returns {String}
	 */
	this.formatXml = function ( xml )
	{

		if ( typeof xml !== 'string' ) {
			return xml;
		}

		//	this.s();

		/*
		 var lexer = new this.Lexer(xml);
		 while (! lexer.eof ) {
		 lexer.next()
		 console.log( lexer.token + ' ' );
		 }
		 */

//		console.log( scriptCache );

		xml = xml.replace( /\|/g, '__BAR__' );
		//	xml = xml.replace( /{(if|while|for)/g, '{ $1' );

		var tplFunctions = document.templateFunctions || ['iif'];
		var re = tplFunctions.join( '|' );

		// mask system vars and functions
		var masked = false, m = preg_match_all( '({(([@\\$])[a-zA-Z0-9]([^}]*)|(' + re + ')\(([^\)]*)\))})', xml );
		if ( m.length ) {

			console.log( m );

			masked = [];

			for ( var i = 0; i < m.length; ++i ) {
				// javascript function patch
				if ( m[i].match( /\{([a-zA-Z0-9]+?)\./g ) || m[i].match( /;\s*\}$/g ) ) {
					continue;
				}

				masked.push( m[i] );

				var regex = m[i].replace( /\[/g, '\\[' );
				regex = regex.replace( /\]/g, '\\]' );
				regex = regex.replace( /\)/g, '\\)' );
				regex = regex.replace( /\(/g, '\\(' );
				regex = regex.replace( /\+/g, '\\+' );
				regex = regex.replace( /\?/g, '\\?' );
				regex = regex.replace( /\*/g, '\\*' );
				regex = regex.replace( /\$/g, '\\$' );
				regex = regex.replace( /\|/g, '__BAR__' );
				regex = regex.replace( /\!/g, '\\!' );
				regex = regex.replace( /\./g, '\\.' );
				regex = regex.replace( /\//g, '\\/' );

				xml = preg_replace( '/' + regex + '/g', '##INTERNAL' + i + '##', xml, 1 );
			}
		}

		var operators = ['>>', '<<', '>=', '<=', '>', '<'];
		var operatorsReplace = ['@@shr@@', '@@shl@@', '@@gte@@', '@@lte@@', '@@gt@@', '@@lt@@'];
		/*
		 'eq'   => '==',
		 'eqt'  => '===',
		 'ne'   => '!=',
		 'net'  => '!==',
		 'neq'  => '!=',
		 'neqt' => '!==',
		 'lt'   => '<',
		 'le'   => '<=',
		 'lte'  => '<=',
		 'gt'   => '>',
		 'ge'   => '>=',
		 'gte'  => '>=',
		 'and'  => '&&',
		 'or'   => '||',
		 'xor'  => 'xor',
		 'not'  => '!',
		 'mod'  => '%',
		 'div'  => '/',
		 'add'  => '+',
		 'sub'  => '-',
		 'mul'  => '*',
		 'shl'  => '<<',
		 'shr'  => '>>'

		 */

		var regexCache = [];

		var m = preg_match_all( '"([^"]*)"|\'([^\']*)\'', xml );
		if ( m.length ) {
			console.log( m );
			var i = 0, maxLength = m.length;

			//for ( var i = 0; i < m.length; ++i ) {
			while ( i < maxLength ) {
				var fix = m[i]; //.replace( />/g, '@@__gt__@@' ).replace( /</g, '@@__lt__@@' );
				var regex = m[i];

				++i;
				if ( !fix ) {
					continue;
				}
				if ( fix.match( />/mg ) || fix.match( /</mg ) ) {
					for ( var x = 0; x < operators.length; ++x ) {
						//if ( typeof regexCache[x] == 'undefined' ) {
						//	regexCache[x] = new RegExp( operators[x], 'mg' );
						//}
						fix = fix.replace( new RegExp( operators[x], 'g' ), operatorsReplace[x] );
					}
				}

			//	fix = fix.replace( />=/g, '@@gte@@' ).replace( /<=/g, '@@lte@@' ).replace( /</g, '@@lt@@' ).replace( />/g,'@@gt@@' );


				regex = regex.replace( /\[/g, '\\[' ).replace( /\]/g, '\\]' );
				regex = regex.replace( /\)/g, '\\)' );
				regex = regex.replace( /\(/g, '\\(' );
				regex = regex.replace( /\+/g, '\\+' );
				regex = regex.replace( /\?/g, '\\?' );
				regex = regex.replace( /\$/g, '\\$' );
				regex = regex.replace( /\* /g, '\\* ' );
				regex = regex.replace( /\|/g, '__BAR__' );
				regex = regex.replace( /\!/g, '\\!' );
				regex = regex.replace( /\./g, '\\.' );
				regex = regex.replace( /\//g, '\\/' );

				xml = preg_replace(  '/' + regex + '/g', fix, xml, 1 );

			}
		}

		console.log( xml );

		//	xml = xml.replace( /=\s*([\'"])([^\1]*)>([^\1]*)$1/g, '=$1$2@@__gt__@@$3$1' );
		//	xml = xml.replace( /([\'"])([^\1]*)>([^\1]*)$1/g, '=$1$2@@__gt__@@$3$1' );

		xml = xml.replace( /\/\/\n*\s*<\!\[CDATA\[/g, '<![CDATA[' );
		xml = xml.replace( /\/\/\n*\s*]]>/g, ']]>' );

		xml = xml.replace( /\/\*\n*\s*<\!\[CDATA\[\n*\s*\*\//g, '<![CDATA[' );
		xml = xml.replace( /\/\*\n*\s*]]>\n*\s*\*\//g, ']]>' );

		xml = xml.replace( '    ', '' );
		xml = xml.replace( /\n?\t*\s*(<\/?[a-z0-9:]([^>]*)>)\t*\s*\n?\t*\s*/ig, '\n$1\n' );

		//    xml = xml.replace(/(\n*\s*\t*)<\/a>\n*\t*\s*/gi, '</a>\n');
		//    xml = xml.replace(/\t*\s*\n*(<a\s([^>]+)>)(\n*\s*\t*)/gi, '\n<a $2>');

		//  xml = xml.replace(/(<a\s([^>]+)>)(\n*\s*\t*)<\/a>\n*\t*\s*/gi, '$1</a>\n');

		// prepare template comments
		xml = xml.replace( /\t*\s*\n*\{\*\t*\s*\n*/g, '\n{*\n' );
		xml = xml.replace( /\t*\s*\n*\*\}\t*\s*\n*/g, '\n*}\n' );

		//	xml = xml.replace(/\n{2}(<\/?[a-z0-9:]([^>]*)>)\n{2}/ig, '\n$1\n');
		xml = xml.replace( /^\n/g, '' );

		//	console.log( this._formatXml(xml) );

		var lines = xml.split( "\n" ), pretty = [];
		var level = 1, indent = 0, indentJs = 0, scriptStart = false, lastIsEmptyTag = false, tagOpen = false;
		var ifTagOpen = true, openIndenter = 0, tagName = null;
		var i = 0, currentTag = '', maxLength = lines.length;

		//	for ( var i = 0; i < lines.length; ++i )
		//	{
		while ( i < maxLength ) {
			var l = lines[i];
			++i;

			if ( l.match( /^\t*\s*$/ ) ) {
				pretty.push( '' );
				continue;
			}
			if ( l == '' ) {
				continue;
			}
			//        if (l.match(/^(<\w([^>]*)>)$/g)) {
			if ( l != '' && l.match( /^(<[a-z0-9:]+?([^>]*))/ig ) ) {
				tagName = l.replace( /^<([a-z0-9:]*)\s*/ig, '$1' );

				if ( !l.match( /^<\!\[CDATA\[$/i ) && !l.match( /^<\!-{1,}$/i ) && tagName ) {

					if ( indent < 0 ) {
						indent = 0;
					}

					if ( l.match( /.*\/>\s*\t*$/g ) ) {
						pretty.push( (this.repeat( '    ', indent ) + l.replace( /^\s*\t*/g, '' ).replace( /\s*\t*$/g, '' )) );

						ifTagOpen = false;
						// indent += level;
					}
					else {

						pretty.push( (this.repeat( '    ', indent ) + l.replace( /^\s*\t*/g, '' ).replace( /\s*\t*$/g, '' )) );
						if ( l.match( />$/g ) ) {
							currentTag = tagName;
							ifTagOpen = false;
							indent += level;
						}
						else {
							ifTagOpen = true;
						}
					}
				}

				/*

				 if (l.match(/^<\w([^>]*)\/>$/g))
				 {
				 lastIsEmptyTag = true;
				 // $indent += (substr($el, 0, 2) === '<!' ? 0 : $level);
				 }
				 else
				 {
				 lastIsEmptyTag = false;

				 if (l.match(/^<\!\[CDATA\[$/i) || l.match(/^<\!-{1,}$/i)) {

				 }
				 else {
				 //if (!l.match(/<textarea/i)) { 
				 indent += level;
				 //}
				 tagOpen = true;
				 }
				 }

				 */
			}
			else {

				if ( l != '' ) {

					var isTag = false, isJS = false, isFixed = false;

					//console.log('1137 ' + l);
					if ( l.match( /<\/[a-z0-9:]*([^>]*)>$/ig ) /*|| l.match(/^\]\]>$/g)*/ ) {

						if ( l.match( />.*<\/a>$/ig ) ) {
							indent += level;
						}

						currentTag = '';
						openIndenter = 0;
						tagOpen = false;
						// if (!l.match(/<\/textarea/i)) { 
						indent -= level; // closing tag, decrease indent 
						// }
						//  console.log('1141 ' + l);
					}
					else if ( l.match( /^\s*\t*\{([\$@])[a-z0-9\._\-]*\}$/ig ) || l.match( /^\s*\t*\{[a-z0-9_\-]+?\([^\)]*\)\s*\}$/ig ) ) {
						// vars & functions
					}
					else if ( l.match( /^\s*\t*\{\*.*\*\}$/ig ) || l.match( /^\s*\t*\{\*.*/ig ) || l.match( /^.*\*\}$/ig ) ) {
						// comment
					}
					else {

						if ( ifTagOpen && !l.match( />$/g ) ) {
							// openIndenter = indent;
						}
						else if ( ifTagOpen && l.match( />$/g ) ) {

							if ( l.match( /\/>$/g ) ) {
								openIndenter = 0;
								ifTagOpen = false;
							}

							if ( ifTagOpen && l.match( />$/g ) ) {
								ifTagOpen = false;
								isFixed = true;
								openIndenter = (openIndenter > 0 ? openIndenter : indent) + level;
								indent += level;
							}

						}
						else {

						}

					}

					if ( indent < 0 ) {
						indent = 0;
					}

					if ( isFixed ) {
						isFixed = false;
						pretty.push( (this.repeat( '    ', indent ) + l.replace( /^\s*\t*/g, '' ).replace( /\s*\t*$/g, '' ) ) );
					}
					else {

						if ( l.match( /^\s*\t*<\/textarea>$/ig ) ) {
							pretty.push( l.replace( /^\s*\t*/g, '' ).replace( /\s*\t*$/g, '' ) );
						}
						else {
							pretty.push( (this.repeat( '    ', indent ) + l.replace( /^\s*\t*/g, '' ).replace( /\s*\t*$/g, '' ) ) );
						}

						if ( currentTag.toLowerCase() === 'script' ) {
							if ( l.match( /\{$/g ) || l.match( /^\s*\t*\{\*$/g ) ) {
								indent += level;
							}
						}
					}
				}
				else {
					if ( typeof l == 'string' ) {
						//	pretty.push('');
					}
				}
			}
		}




		delete lines;
		var str = pretty.join( '\n' );
		str = str.replace( /__BAR__/g, '|' );

		for ( var x = 0; x < operators.length; ++x ) {
			var r = new RegExp( operatorsReplace[x], 'g' );
			str = str.replace( r, operators[x] );
		}


	//	fix.replace( />=/g, '@@gte@@' ).replace( /<=/g, '@@lte@@' ).replace( /</g, '@@lt@@' ).replace( />/g,'@@gt@@' );




		if ( str.match( /<script/ig ) ) {

			str = str.replace( /<!\[CDATA\[/ig, '/* cdata_open */' );
			str = str.replace( /\]\]>/ig, '/* cdata_close */' );
			str = str.replace( /{\s*\n*\t*\*/g, '/**T** ' ).replace( /\*\s*\n*\t*}/g, ' **T**/' );

			var lines = str.split( "\n" ), pretty = [], indent = 0, minIndent = 0, startScript = false;

			var i = 0, maxLength = lines.length, re = /((["']){(\w+?)\s*\(([^}]*)}(\2))/g, startLine = 0, scriptContent = [], clean = [];

			//for ( var i = 0; i < lines.length;++i  ) {
			while ( i < maxLength ) {
				var l = lines[i];
				++i;

				if ( l.match( /\s*\t*<script([^>]*)>$/img ) ) {
					startScript = true;
					startLine = i;

					var start = l.replace( /(\s*\t*)<script([^>]*)>$/img, '$1' )
					var count = start.match( /(    )/g );
					indent = (count && count.length > 0 ? count.length : 0);

					clean.push( l );
				}
				else {
					if ( startScript ) {
						if ( l.match( /.*<\/script>$/ig ) ) {

							var cl = l.replace( /<\/script>$/ig, '' )
							while ( (m = re.exec( cl )) != null ) {
								if ( m[2] === "'" && m[5] === "'" ) {
									if ( m[4].match( /'/g ) ) {
										m[4] = m[4].replace( /'/g, '"' );
									}

								}

								if ( m[2] === '"' && m[5] === '"' ) {
									if ( m[4].match( /"/g ) ) {
										m[4] = m[4].replace( /"/g, "'" );
									}

								}

								cl = cl.replace( m[1], m[2] + '{' + m[3] + '(' + m[4] + '}' + m[2] );
							}

							scriptContent.push( cl );

							var newstr = '', content = scriptContent.join( "\n" );
							content = content.replace( /<(\/?)cp:([^>]*)>/g, '/*#$1cp:$2#*/' );

							if ( content.replace( /\s*\t*/g, '' ) != '' ) {
								if ( typeof js_beautify == 'undefined' ) {
									Loader.require( 'html/js/backend/tpleditor/beautifier/lib/beautify.js', function ()
									{

										newstr = js_beautify( content.replace( /^\s*\t*/g, '' ) );

									} );
								}
								else {
									newstr = js_beautify( content.replace( /^\s*\t*/g, '' ) );
								}

								var nlines = newstr.split( "\n" );
								for ( var x = 0; x < nlines.length; ++x ) {
									nlines[x] = this.repeat( '    ', indent + 1 ) + nlines[x];
								}

								clean.push( nlines.join( "\n" ) );
								clean.push( this.repeat( '    ', indent ) + '</script>' );
							}
							else {
								clean.push( this.repeat( '    ', indent ) + '</script>' );
							}

							//	console.log(newstr);
							indent = 0;
							startScript = false;
							scriptContent = [];
						}
						else {
							while ( (m = re.exec( l )) != null ) {

								if ( m[2] === "'" && m[5] === "'" ) {
									if ( m[4].match( /'/g ) ) {
										m[4] = m[4].replace( /'/g, '"' );
									}
								}
								if ( m[2] === '"' && m[5] === '"' ) {
									if ( m[4].match( /"/g ) ) {
										m[4] = m[4].replace( /"/g, "'" );
									}
								}

								l = l.replace( m[1], m[2] + '{' + m[3] + '(' + m[4] + '}' + m[2] );
							}

							scriptContent.push( l );
						}
					}
					else {
						clean.push( l );
					}
				}
			}

			str = clean.join( "\n" );
		}

		// backward masked sys functions and vars
		if ( masked && masked.length ) {

			for ( var i = 0; i < masked.length; ++i ) {
				str = preg_replace( '/(##INTERNAL' + i + '##)/g', masked[i], str, 1 );
				str = preg_replace( '/(## INTERNAL' + i + '##)/g', masked[i], str, 1 );
				str = preg_replace( '/(##INTERNAL' + i + ' ##)/g', masked[i], str, 1 );
				str = preg_replace( '/(## INTERNAL' + i + ' ##)/g', masked[i], str, 1 );
			}
		}

		str = str.replace( /{\s*\n*\t*(\$[a-zA-Z0-9_\-]+?)\s*\n*\t*}/mg, '{$1}' ).replace( /{\s*\n*\t*\@([a-zA-Z0-9_\-]+?)\s*\n*\t*}/mg, '{@$1}' );
		str = str.replace( /{\s*\n*\t*([a-zA-Z0-9_]+?)\s*\(([^\{\};]*)\)\s*\n*\t*}/mg, '{$1($2)}' );

		// backward tags
		str = str.replace( /\/\*#(\/?)cp:([^#]*)#\*\//g, '<$1cp:$2>' );

		// backward cdata
		str = str.replace( /\/\*\scdata_open\s\*\//g, '<![CDATA[' ).replace( /\/\*\scdata_close\s\*\//g, ']]>' );

		// backward comments
		str = str.replace( /\/\*\*T\*\*\s/g, '{* ' ).replace( /\s\*\*T\*\*\//g, ' *}' );
		str = str.replace( /\[\*/g, '{*' ).replace( /\*\]/g, '*}' );
		//	str = str.replace(/##N#L##/g, '\n' );






		delete lines;

		str = str.replace( /'([^\']*)'/g, function ( match, contents, offset, s )
		{

			if ( contents.match( /<\/?\w[^>]*>/g ) ) {

				contents = contents.replace( /^\n*\r*\s*\t*\n*\r* /, '' );
				contents = contents.replace( />\n*\r*\s*\t* /, '>' );
				contents = contents.replace( /\n*\r*\s*\t*<\//, '</' );

				return "'" + contents.replace( /\n*\r*\s*\t*\n*\r*$/, '' ) + "'";
			}
			return "'" + contents + "'";
		} );

		str = str.replace( /"([^\"]*)"/g, function ( match, contents, offset, s )
		{

			if ( contents.match( /<\/?\w[^>]*>/g ) ) {
				contents = contents.replace( /^\n*\r*\s*\t*\n*\r* /, '' );
				contents = contents.replace( />\n*\r*\s*\t* /, '>' );
				contents = contents.replace( /\n*\r*\s*\t*<\//, '</' );
				return '"' + contents.replace( /\n*\r*\s*\t*\n*\r*$/, '' ) + '"';
			}
			return '"' + contents + '"';
		} );

		str = str.replace( />\n\n/g, '>\n' ); //.replace( /##N-L##/g, '>\n' );

		// CDATA Patch
		str = str.replace( /<\!\[CDATA\[/g, '/* <![CDATA[ */' );
		str = str.replace( /]]>/gi, '/* ]]> */' );

		str = str.replace( /@@__gt__@@/g, '>' ).replace( /@@__lt__@@/g, '<' );
		str = str.replace( /__BAR__/g, '|' );

		str = str.replace( /<(textarea|pre\s)([^>]*)>\n*\s*\t*/ig, '<$1$2>' ).replace( /\n*\s*\t*<\/(textarea|pre)>/ig, '</$1>' );
		str = str.replace( /<(script|a\s)([^>]*)>\n*\s*\t*<\/\1>/img, '<$1$2></$1>' );

		// js object fix
		str = str.replace( /\)\n*\s*\t*\.([a-z0-9_]*)/img, ').$1' );

		for (i=0; i<operatorsReplace.length; i++) {
			str = str.replace(operatorsReplace[i], operators[i]);
		}


		return str;

		var reg = /(>)(<)(\/*)/g;
		var wsexp = / *(.*) +\n/g;
		var contexp = /(<.+>)(.+\n)/g;
		xml = xml.replace( reg, '$1\n$2$3' ).replace( wsexp, '$1\n' ).replace( contexp, '$1\n$2' ).replace( /\n{1,}/g, '\n' ).replace( /    /g, '' );
		xml = xml.replace( /\s*\t*<(.+)/, '<$1' );
		xml = xml.replace( />\s*\t*/, '>' );
		xml = xml.replace( /\r{1,}/, '\n' );
		xml = xml.replace( /\n{1,}/, '\n' );

		var pad = 0;
		var formatted = '';
		var lines = xml.split( '\n' );
		var indent = 0;
		var lastType = 'other';
		// 4 types of tags - single, closing, opening, other (text, doctype, comment) - 4*4 = 16 transitions 
		var transitions = {
			'single->single': 0,
			'single->closing': -1,
			'single->opening': 0,
			'single->other': 0,
			'closing->single': 0,
			'closing->closing': -1,
			'closing->opening': 0,
			'closing->other': 0,
			'opening->single': 1,
			'opening->closing': 0,
			'opening->opening': 1,
			'opening->other': 1,
			'other->single': 0,
			'other->closing': -1,
			'other->opening': 0,
			'other->other': 0
		};

		for ( var i = 0; i < lines.length; i++ ) {
			var ln = lines[i];
			var single = Boolean( ln.match( /<.+\/>/ ) ); // is this line a single tag? ex. <br />
			var closing = Boolean( ln.match( /<\/.+>/ ) ); // is this a closing tag? ex. </a>
			var opening = Boolean( ln.match( /<[^!].*>/ ) ); // is this even a tag (that's not <!something>)
			var type = single ? 'single' : closing ? 'closing' : opening ? 'opening' : 'other';
			var fromTo = lastType + '->' + type;
			lastType = type;
			var padding = '';
			indent += transitions[fromTo];
			for ( var j = 0; j < indent; j++ ) {
				padding += '    ';
			}

			formatted += padding + ln + '\n';
		}

		return formatted;
	};

	/**
	 *
	 * @returns {Boolean|undefined}
	 *
	 */
	this.reindentCode = function ()
	{

		if ( this.mode !== 'html' && this.mode !== 'javascript' && this.mode !== 'css' ) {
			return;
		}

		var baseHint = this.disableHint;
		this.disableHint = true;
		this.editor.focus();

		if (typeof Win != 'undefined') { $( '#body-content-' + Win.windowID + ',#' + Win.windowID ).mask( 'Please wait...' ); }
		var self = this, newvalue = '';

		setTimeout( function ()
		{
			var source = self.editor.getSession().getValue();

			var pos = self.getCursorPosition();

			newvalue = self.formatXml( source );

			self.editor.focus();
			self.editor.setValue( newvalue, 0 );
			self.editor.renderer.updateFull();
			self.setCursorPosition( pos );
			self.editor.focus();

			self.disableHint = baseHint;
			self.jQvalidateSyntax.hide();
			self.jQcheckingSyntax.show();
			self.updateHTMLHint();

			if (typeof Win != 'undefined') { $( '#body-content-' + Win.windowID + ',#' + Win.windowID ).unmask(); }
		}, 100 );

		return true;

		var opts = {
			indent_size: 1,
			indent_char: '\t',
			preserve_newlines: true,
			keep_array_indentation: false,
			break_chained_methods: true,
			indent_scripts: 'keep',
			brace_style: 'collapse',
			space_before_conditional: true,
			unescape_strings: false,
			wrap_line_length: 0,
			space_after_anon_function: true
		};

		setTimeout( function ()
		{
			var source = self.editor.getSession().getValue();

			if ( typeof js_beautify == 'undefined' ) {
				Loader.require(
					['html/js/backend/tpleditor/beautifier/lib/beautify.js',
						'html/js/backend/tpleditor/beautifier/lib/beautify-css.js',
						'html/js/backend/tpleditor/beautifier/lib/beautify-html.js',
						'html/js/backend/tpleditor/beautifier/test/sanitytest.js',
						'html/js/backend/tpleditor/beautifier/test/beautify-tests.js',
						'html/js/backend/tpleditor/beautifier/lib/unpackers/javascriptobfuscator_unpacker.js',
						'html/js/backend/tpleditor/beautifier/lib/unpackers/urlencode_unpacker.js',
						'html/js/backend/tpleditor/beautifier/lib/unpackers/p_a_c_k_e_r_unpacker.js',
						'html/js/backend/tpleditor/beautifier/lib/unpackers/myobfuscate_unpacker.js'], function ()
					{

						if ( typeof js_beautify != 'undefined' ) {
// CDATA Patch
							source = source.replace( /((\/\*\s*)(\<\s*\S*\!\[CDATA\[)(\s*\S*\*\/))/gi, '/* <![CDATA[ */' );
							source = source.replace( /((\/\*\s*)(\]\]\s*\S*>)(\s*\S*\*\/))/gi, '/* ]]> */' );
							source = source.replace( /{\*(\s*)/gi, '{* ' );
							source = source.replace( /(\s*)\*}/gi, ' *}' );
							if ( looks_like_html( source ) ) {
								newvalue = html_beautify( source, opts );
							}
							else {
								source = unpacker_filter( source );
								newvalue = js_beautify( source, opts );
							}

						}
						else {
							setTimeout( function ()
							{
								self.reindentCode();
							}, 300 );
						}
					} );
			}
			else {

				// CDATA Patch
				source = source.replace( /((\/\*\s*)(\<\s*\S*\!\[CDATA\[)(\s*\S*\*\/))/gi, '/* <![CDATA[ */' );
				source = source.replace( /((\/\*\s*)(\]\]\s*\S*>)(\s*\S*\*\/))/gi, '/* ]]> */' );
				source = source.replace( /{\*(\s*)/gi, '{* ' );
				source = source.replace( /(\s*)\*}/gi, ' *}' );

				if ( looks_like_html( source ) ) {
					newvalue = html_beautify( source, opts );
				}
				else {
					source = unpacker_filter( source );
					newvalue = js_beautify( source, opts );
				}
			}

			// CDATA Patch
			newvalue = newvalue.replace( /{\*(\s*)/gi, '{* ' );
			newvalue = newvalue.replace( /(\s*)\*}/gi, ' *}' );
			newvalue = newvalue.replace( /((\/\*\s*){1,}(\<\s*\S*\!\[CDATA\[)(\s*\S*\*\/)?)/g, '/* <![CDATA[ */' );
			newvalue = newvalue.replace( /((\/\*\s*){1,}(\]\]\s*\S*>)(\s*\S*\*\/)?)/gi, '/* ]]> */' );

			var pos = self.getCursorPosition();
			self.editor.focus();
			self.editor.setValue( newvalue, 0 );
			self.editor.renderer.updateFull();
			self.setCursorPosition( pos );

			$( '#body-content-' + Win.windowID ).unmask();
			self.disableHint = baseHint;

			self.jQvalidateSyntax.hide();
			self.jQcheckingSyntax.show();
			self.updateHTMLHint();

		}, 200 );
	};

	function hasType( e, t )
	{
		var n = !0,
			r = e.type.split( "." ),
			i = t.split( "." );
		return i.forEach( function ( e )
		{
			if ( r.indexOf( e ) == -1 )
				return n = !1, !1
		} ), n;
	}

	function findTagName( session, pos )
	{
		var i = ace.require( "ace/token_iterator" ).TokenIterator,
			iterator = new i( session, pos.row, pos.column );
		var token = iterator.getCurrentToken();
		if ( !token || !hasType( token, 'meta.tag' ) && !(hasType( token, 'text' ) && token.value.match( '/' )) ) {
			do {
				token = iterator.stepBackward();
			} while ( token && (
				hasType( token, 'string' ) ||
					hasType( token, 'cpconstante' ) || // for cms tags
					hasType( token, 'cpfunction' ) || // for cms tags
					hasType( token, 'cpvariable' ) || // for cms tags
					hasType( token, 'cpconditions' ) || // for cms tags
					hasType( token, 'keyword.operator' ) || hasType( token, 'entity.attribute-name' ) || hasType( token, 'text' )) );
		}
		if ( token && (hasType( token, 'meta.tag' ) || hasType( token, 'meta.tag.name' )) && !iterator.stepBackward().value.match( '/' ) )
			return token.value;
	}

	function cursorInAttribute( session, pos )
	{
		var i = ace.require( "ace/token_iterator" ).TokenIterator,
			iterator = new i( session, pos.row, pos.column );
		var token = iterator.getCurrentToken();
		if ( !token || !hasType( token, 'entity.attribute-name' ) && !hasType( token, 'keyword.operator' ) && !hasType( token, 'meta.tag' ) && !(hasType( token, 'text' ) && token.value.match( '/' )) ) {
			do {
				token = iterator.stepBackward();

			} while ( token && (hasType( token, 'string' ) ||
				hasType( token, 'cpconstante' ) || // for cms tags
				hasType( token, 'cpfunction' ) || // for cms tags
				hasType( token, 'cpvariable' ) || // for cms tags
				hasType( token, 'cpconditions' ) || // for cms tags
				hasType( token, 'text' )) && !hasType( token, 'keyword.operator' ) );
		}

		if ( token && hasType( token, 'keyword.operator' ) && !iterator.stepBackward().value.match( '/' ) || token.value == '""' || token.value == "''" )
			return true;

		return false;
	}

	this.findTagAttributes = function ( session, pos )
	{
		var attr = [], x = 0;
		var i = ace.require( "ace/token_iterator" ).TokenIterator,
			iterator = new i( session, pos.row, pos.column );
		var token = iterator.getCurrentToken();
		if ( !token || !hasType( token, 'meta.tag' ) && !(hasType( token, 'text' ) && token.value.match( '/' )) ) {
			do {
				token = iterator.stepBackward();
				if ( hasType( token, 'entity.attribute-name' ) && token.value ) {
					// var kk = [];
					// kk[token.value] = true;
					attr.push( token.value );
				}

			} while ( token && (hasType( token, 'string' ) ||
				hasType( token, 'cpconstante' ) || // for cms tags
				hasType( token, 'cpfunction' ) || // for cms tags
				hasType( token, 'cpvariable' ) || // for cms tags
				hasType( token, 'cpconditions' ) || // for cms tags
				hasType( token, 'keyword.operator' ) || hasType( token, 'entity.attribute-name' ) || hasType( token, 'text' )) );
		}

		var back = iterator.stepBackward();
		if ( token && (hasType( token, 'meta.tag' ) || hasType( token, 'meta.tag.name' )) && (back && !back.value.match( '/' )) )
			return attr;
		else
			return [];
	};

	this.getState = function ( e, t )
	{
		return this.editor.getSession().getState( e, t )
	};

	this.isStartHtmlTag = function ( session, pos )
	{
		var i = ace.require( "ace/token_iterator" ).TokenIterator,
			iterator = new i( session, pos.row, pos.column );
		var token = iterator.getCurrentToken();
		if ( !token || !hasType( token, 'meta.tag' ) && !(hasType( token, 'text' ) && token.value.match( '/' )) ) {
			do {
				token = iterator.stepBackward();
			} while ( token && (hasType( token, 'string' ) ||
				hasType( token, 'cpconstante' ) || // for cms tags
				hasType( token, 'cpfunction' ) || // for cms tags
				hasType( token, 'cpvariable' ) || // for cms tags
				hasType( token, 'cpconditions' ) || // for cms tags
				hasType( token, 'keyword.operator' ) || hasType( token, 'entity.attribute-name' ) || hasType( token, 'text' )) );
		}

		var back = iterator.stepBackward();

		if ( token && (hasType( token, 'meta.tag' ) || hasType( token, 'meta.tag.name' )) && hasType( back, 'meta.tag.punctuation.begin' ) ) {
			return true;
		}
		return false;
	};

	this.closeSelfCloseTag = function ( e )
	{
		if ( (this.mode != 'html' && this.mode != 'xml') || this.editor.Intellisense.dictMode != 'html' ) {
			this.editor.setBehavioursEnabled( false );
			this.insert( "/" );
			this.editor.setBehavioursEnabled( true );
			return false;
		}

		var s = !1;
		var t = this.getCursorPosition(),
			r = this.getState( t.row, t.column ),
			i = ace.require( "ace/token_iterator" ).TokenIterator,
			s = new i( e.session, t.row, t.column );
		var baseToken = s.getCurrentToken();
		var line = e.session.getLine( t.row );
		var tagname = findTagName( e.session, t );
		var inAttribute = false, test = line.substring( 0, t.column );

		if ( (baseToken.type === 'string' || baseToken.type === 'string.cpstring') && (r === "start_tag_stuff" || r === 'start') ) {
			if ( !test.match( /=\s*"[^\"]*"[^\"]*$/ ) && !test.match( /=\s*'[^\']*'[^\']*$/ ) ) {
				inAttribute = true;
			}
		}

		if ( tagname && !inAttribute && this.isStartHtmlTag( e.session, t ) ) {
			var re = new RegExp(tagname, 'ig');
			if ( sourceEditor_selfCloseTags.join(' ').match( re ) || (typeof top.dcms_tags != 'undefined' && top.dcms_tags[tagname] && top.dcms_tags[tagname].singleTag) ) {
				this.editor.setBehavioursEnabled( false );
				this.insert( "/>" );
				this.editor.setBehavioursEnabled( true );

				return false;
			}
		}
		this.editor.setBehavioursEnabled( false );
		this.insert( "/" );
		this.editor.setBehavioursEnabled( true );

		return true;
	};

	this.closeTag = function ( e )
	{

		if ( (this.mode != 'html' && this.mode != 'xml') || this.editor.Intellisense.dictMode != 'html' ) {
			this.editor.setBehavioursEnabled( false );
			this.insert( ">" );
			this.editor.setBehavioursEnabled( true );
			return false;
		}

		var s = !1;
		var t = this.getCursorPosition(),
			r = this.getState( t.row, t.column ),
			i = ace.require( "ace/token_iterator" ).TokenIterator,
			s = new i( e.session, t.row, t.column );
		var baseToken = s.getCurrentToken();
		var line = e.session.getLine( t.row );
		var tagname = findTagName( e.session, t );
		var inAttribute = false, test = line.substring( 0, t.column );

		var attributes = this.findTagAttributes( e.session, t );

		if ( (baseToken.type === 'string' || baseToken.type === 'string.cpstring') && (r === "start_tag_stuff" || r === 'start') ) {
			if ( !test.match( /=\s*"[^\"]*"[^\"]*$/ ) && !test.match( /=\s*'[^\']*'[^\']*$/ ) ) {
				inAttribute = true;
			}
		}

		if ( test.match( /('|")$/g ) && test === line ) {
			inAttribute = false;
		}

		// inAttribute = cursorInAttribute(this.editor.session, t);

		if ( inAttribute ) {
			this.editor.setBehavioursEnabled( false );
			this.insert( ">" );
			this.editor.setBehavioursEnabled( true );
			return;
		}

		this.editor.setBehavioursEnabled( true );

		if ( tagname && this.isStartHtmlTag( e.session, t ) ) {
			var re = new RegExp(tagname, 'ig');
			if ( sourceEditor_selfCloseTags.join(' ').match( re ) || (typeof top.dcms_tags != 'undefined' && top.dcms_tags[tagname] && top.dcms_tags[tagname].singleTag && !tagname.match( /block$/i )) ) {
				this.insert( "/>" );
				return false;
			}
			else {
				this.insert( ">" );
				/*
				 if (attributes.length > 1) {
				 var a = this.getCursorPosition();
				 this.insert("</" + tagname + ">");
				 this.setCursorPosition(a);

				 }

				 */

				return;
			}
		}
		else {
			if ( this.mode == 'html' || this.mode == 'xml' ) {
				if ( !hasType( baseToken, 'meta.tag.punctuation.end' ) ) {
					this.insert( ">" );
				}
			}
			else {
				this.insert( ">" );
			}
		}
	};

	this.setCustomHint = function ( data, mode )
	{
		if ( this.disableHint || !this.jqEditor ) {
			return;
		}

		//   this.jqErrorBar.empty().append(this.loadingImage).append(' Loading');

		if ( mode === 'javascript' && typeof window.JSHINT === 'function' ) {
			var code = this.editor.getValue();

			JSHINT( code, {
				"undef": true,
				"unused": true,
				forin: true,
				eqeqeq: false,
				plusplus: true,
				nonstandard: true, 'jquery': true, 'browser': true

			} );
			/*
			 var e = JSHINT.errors;

			 var data = [];
			 for (var k in e) {
			 var err = e[k];
			 data.push({
			 row: err.line - 1,
			 column: err.character > 0 ? err.character : 1,
			 text: err.reason,
			 type: err.id,
			 raw: err.evidence
			 });
			 }
			 */
		}

		var errors = [], message;
		for ( var i = 0, l = data.length; i < l; i++ ) {
			message = data[i];
			if ( message.text ) {
				errors.push( {
					row: message.row,
					column: message.column > 0 ? message.column : 1,
					text: message.text,
					type: message.type,
					raw: message.raw
				} );
			}
		}
		arrHints = [];
		arrHints = errors;
		var eD = this.jqEditor;

		if ( !eD.find( '#hints' ).length ) {
			eD.append( $( '<div id="hints">' ).hide() );
		}

		var errorCount = errors.length;
		if ( errorCount ) {
			eD.find( '#hints' ).html( 'Hints found: <strong>' + errorCount + '</strong>' ).show();
			eD.find( '.ace_gutter:first' ).addClass( 'ace-gutter-error' );
		} else {
			if ( eD.find( '#hints' ).is( ':visible' ) ) {
				eD.find( '#hints' ).hide();
				eD.find( '.ace-gutter-error:first' ).removeClass( 'ace-gutter-error' );
			}
		}

		this.setErrorBar();
	};

	this.getTokenMode = function ()
	{
		var range = this.editor.getSelectionRange();
		var session = this.editor.getSession();
		var v = range.start, y = session.getLine( v.row ).substr( 0, v.column );
		var w = v.row > 0 ? v.row - 1 : 0;
		var EditorState = session.getState( w, v.column );
		var mode = session.getMode(), x = mode.getTokenizer();
		var token = x.getLineTokens( y, EditorState, v.row );
		var tokenState = typeof token.state == "object" ? token.state[0] : token.state;
		var currentMode = this.editor.Intellisense.dictMode !== "php" ? this.editor.Intellisense.dictMode : "html";

		return (tokenState.match( /(php|js|css)\-/i ) || ["", currentMode])[1].toLowerCase();
	}

	function str_repeat( input, multiplier )
	{
		// http://kevin.vanzonneveld.net
		// +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
		// +   improved by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
		// +   improved by: Ian Carter (http://euona.com/)
		// *     example 1: str_repeat('-=', 10);
		// *     returns 1: '-=-=-=-=-=-=-=-=-=-='

		var y = '';
		while ( true ) {
			if ( multiplier & 1 ) {
				y += input;
			}
			multiplier >>= 1;
			if ( multiplier ) {
				input += input;
			}
			else {
				break;
			}
		}
		return y;
	}

	this.updateHTMLHint = function ()
	{

		if ( this.disableHint ) {
			return;
		}

		var eD = this.jqEditor, tokenMode = this.getTokenMode(), isHtmlMode = false, isCSSMode = false, isJsMode = false;

		if ( this.mode !== 'html' && this.mode !== 'javascript' && this.mode !== 'css' && tokenMode !== 'html' && tokenMode !== 'javascript' && tokenMode !== 'css' ) {
			if ( arrHints.length ) {
				eD.find( '#hints' ).hide();
				eD.find( '.ace-gutter-error:first' ).removeClass( 'ace-gutter-error' );

				eD.find( '#hints' ).html( 'Hints found: <strong>' + arrHints.length + '</strong>' ).show();
				eD.find( '.ace_gutter:first' ).addClass( 'ace-gutter-error' );

				// reset hints
				this.setErrorBar( errors );
				return;
			}
		}

		this.jQvalidateSyntax.hide();
		this.jQcheckingSyntax.show();

		var rules;
		if ( this.mode === 'html' || tokenMode == 'html' || tokenMode === 'html' || tokenMode === 'javascript' || tokenMode === 'css' ) {
			rules = ruleSets;
			isHtmlMode = true;
		}
		else {
			rules = noHtmlRuleSet;

			if ( this.mode == 'css' && this.aceopts.csslint === true ) {
				var rules = ruleCSSLint;
				isCSSMode = true;
			}
			else if ( this.mode == 'javascript' && this.aceopts.jshint === true ) {
				var rules = ruleJSHint;
				isJsMode = true;
			}
		}

		if ( !rules ) {
			this.jQvalidateSyntax.show();
			this.jQcheckingSyntax.hide();
			eD.find( '#hints' ).hide();
			eD.find( '.ace-gutter-error:first' ).removeClass( 'ace-gutter-error' );
			return;
		}

		var grabCode = false;
		if ( this.mode === 'php' || tokenMode === 'php' ) {
			grabCode = true;
		}

		var code = this.editor.getValue();
		if ( grabCode ) {
			var lines = code.split( "\n" ), length = lines.length, phpStart = false, i = 0;
			var regex = /(<\?(php)([^\?>]*)\?>)/gi;
			while ( i < length ) {

				if ( phpStart ) {
					lines[i] = '';
				}

				if ( !phpStart ) {

					if ( lines[i].match( /<\?(php)/gi ) && !lines[i].match( regex ) ) {
						phpStart = true;
						lines[i] = lines[i].replace( /<\?(php).*/, '' );
					}

					if ( lines[i].match( regex ) ) {
						phpStart = false;

						while ( match = regex.exec( lines[i] ) ) {
							var str = match[1];
							str = str_repeat( ' ', str.length );
							lines[i] = lines[i].replace( match[1], str );
						}

						if ( phpStart ) {
							phpStart = false;
						}
					}
				}

				if ( phpStart && lines[i].match( /\?>/g ) ) {
					phpStart = false;

					var s = lines[i].match( /.*\?>/ );
					s = str_repeat( ' ', s.length );
					lines[i] = lines[i].replace( /.*\?>/, s );
				}

				++i;
			}

			code = lines.join( "\n" );
		}

		var messages = [];
		if ( isHtmlMode ) {
			messages = HTMLHint.verify( code, rules );
		}
		else if ( isCSSMode ) {
			var _messages = CSSLint.verify( code, rules );
			messages = _messages.messages;
		}
		else if ( isJsMode && typeof window.JSHINT === 'function' ) {
			messages = JSHINT( code, rules );
		}

		var errors = [], message;
		for ( var i = 0, l = messages.length; i < l; i++ ) {
			message = messages[i];
			errors.push( {
				row: message.line - 1,
				column: message.col - 1,
				text: message.message,
				type: message.type,
				raw: message.raw
			} );
		}
		arrHints = [];
		arrHints = errors;
		this.editor.getSession().setAnnotations( errors );
		if ( !eD.find( '#hints' ).length ) {
			eD.append( $( '<div id="hints">' ).hide() );
		}

		var errorCount = errors.length;
		if ( errorCount ) {
			eD.find( '#hints' ).html( 'Find Hints: <strong>' + errorCount + '</strong>' ).show();
			eD.find( '.ace_gutter:first' ).addClass( 'ace-gutter-error' );
		} else {
			if ( eD.find( '#hints' ).is( ':visible' ) ) {

				eD.find( '#hints' ).hide();
				eD.find( '.ace-gutter-error:first' ).removeClass( 'ace-gutter-error' );
			}
		}

		this.setErrorBar( errors );
	}

	this.hintIndex = 0;

	this.setErrorBar = function ()
	{
		var errorCount = arrHints.length;
		if ( errorCount ) {
			var self = this, err = typeof arrHints[this.hintIndex] != 'undefined' ? arrHints[this.hintIndex] : arrHints[0];

			this.jqErrorBar.removeClass( 'no-errors' );
			this.jQvalidateSyntax.empty();

			var prev = $( '<span>' ).addClass( 'prev-hint' ).on( 'click', function ()
			{
				if ( $( this ).hasClass( 'disabled' ) ) {
					return;
				}

				if ( typeof arrHints[self.hintIndex - 1] != 'undefined' ) {
					self.prevHint( self.hintIndex - 1 );
					if ( errorCount > 1 ) {
						$( this ).next().removeClass( 'disabled' );
					}

					if ( self.hintIndex == 0 ) {
						$( this ).addClass( 'disabled' );
					}

				}
				else {
					$( this ).addClass( 'disabled' );
				}
			} );

			var next = $( '<span>' ).addClass( 'next-hint' ).on( 'click', function ()
			{
				if ( $( this ).hasClass( 'disabled' ) ) {
					return;
				}

				if ( typeof arrHints[self.hintIndex + 1] != 'undefined' ) {
					self.nextHint( self.hintIndex + 1 );
					$( this ).prev().removeClass( 'disabled' );
					if ( self.hintIndex == errorCount - 1 ) {
						$( this ).addClass( 'disabled' );
					}
				}
				else {
					$( this ).addClass( 'disabled' );
				}
			} );

			if ( errorCount == 1 ) {
				next.addClass( 'disabled' );
			}

			var message = $( '<span>' ).addClass( 'hint-message' );

			message.append( '<a href="#" class="fix">Fix</a> [' + (this.hintIndex + 1) + '/' + errorCount + '] ' + err.text + ' on <a href="#" rel="' + err.row + '" col="' + err.column + '" class="goto">line ' + (err.row + 1) + '</a>' );

			message.find( 'a.goto' ).on( 'click', function ( e )
			{
				e.preventDefault();
				self.editor.gotoLine( $( this ).attr( 'rel' ) );
				self.setCursorPosition( {row: $( this ).attr( 'rel' ), column: $( this ).attr( 'col' )} );
			} );

			message.find( 'a.fix' ).on( 'click', function ( e )
			{
				e.preventDefault();
				self.fixError();
			} );

			this.jQvalidateSyntax.append( prev ).append( next ).append( message );
			this.editor.focus();

			this.jQcheckingSyntax.hide();
			this.jQvalidateSyntax.show();

		}
		else {
			this.jqErrorBar.addClass( 'no-errors' );
			this.jQvalidateSyntax.empty().append( 'No syntax errors' );
			this.jQcheckingSyntax.hide();
			this.jQvalidateSyntax.show();
		}
	};

	this.prevHint = function ( num )
	{
		var errorCount = arrHints.length;
		var self = this, err = arrHints[num];
		this.hintIndex = num;
		self.editor.gotoLine( err.row + 1 );
		var message = this.jqErrorBar.find( '.hint-message' );

		message.empty().append( '<a href="#" class="fix">Fix</a> [' + (num + 1) + '/' + errorCount + '] ' + err.text + ' on <a href="#" rel="' + err.row + '" col="' + err.column + '" class="goto">line ' + (err.row + 1) + '</a>' );

		message.find( 'a.goto' ).on( 'click', function ( e )
		{
			e.preventDefault();
			self.editor.gotoLine( $( this ).attr( 'rel' ) );
			self.setCursorPosition( {row: $( this ).attr( 'rel' ), column: $( this ).attr( 'col' )} );
		} );

		message.find( 'a.fix' ).on( 'click', function ( e )
		{
			e.preventDefault();
			self.fixError();
		} );
	};

	this.nextHint = function ( num )
	{
		var errorCount = arrHints.length;
		var self = this, err = arrHints[num];
		this.hintIndex = num;

		self.editor.gotoLine( err.row + 1 );

		var message = this.jqErrorBar.find( '.hint-message' );
		message.empty().append( '<a href="#" class="fix">Fix</a> [' + (num + 1) + '/' + errorCount + '] ' + err.text + ' on <a href="#" rel="' + err.row + '" col="' + err.column + '" class="goto">line ' + (err.row + 1) + '</a>' );

		message.find( 'a.goto' ).on( 'click', function ( e )
		{
			e.preventDefault();
			self.editor.gotoLine( $( this ).attr( 'rel' ) );
			self.setCursorPosition( {row: $( this ).attr( 'rel' ), column: $( this ).attr( 'col' )} );
		} );

		message.find( 'a.fix' ).on( 'click', function ( e )
		{
			e.preventDefault();
			self.fixError();
		} );

	};

	this.fixError = function ()
	{
		var extraStr = '';
		var fixMode = {
			javascript: {
				"Missing semicolon.": function ( e, t )
				{
					return e.substr( 0, t.column ) + ";" + e.substr( t.column )
				},
				"Expected '{a}' and instead saw '{b}'.": function ( e, t )
				{
					return e.substr( 0, t.column + 1 ) + "=" + e.substr( t.column + 1 )
				},
				"A leading decimal point can be confused with a dot: '{a}'.": function ( e, t )
				{
					return e.substr( 0, t.column ).replace( /(\.\d+$)/, "0$1" ) + e.substr( t.column )
				},
				"Missing radix parameter.": function ( e, t )
				{
					return e.substr( 0, t.column ) + ", 10" + e.substr( t.column )
				},
				"Mixed spaces and tabs.": function ( e, t )
				{
					var n = "";
					for ( i = 0; i < l.tabSize; i++ )
						n += " ";
					return l.softTabs ? e = e.replace( /\t/g, n ) : e = e.replace( new RegExp( n, "g" ), "	" ), e
				},
				"Unnecessary semicolon.": function ( e, t )
				{
					return e.substr( 0, t.column ) + e.substr( t.column + 1 )
				},
				"Use '===' to compare with 'null'.": function ( e, t )
				{
					return e.substr( 0, t.column ) + '=' + e.substr( t.column )
				},
				"Use '!==' to compare with 'null'.": function ( e, t )
				{
					return e.substr( 0, t.column ) + '=' + e.substr( t.column )
				},
			},
			css: {
				"Disallow overqualified elements": function ( e, t )
				{
					return e.substr( 0, t.column ) + e.substr( t.column ).replace( /^[^\.#]+/, "" )
				},
				"Disallow units for 0 values": function ( e, t )
				{
					return e.substr( 0, t.column + 1 ) + e.substr( t.column + 1 ).replace( /^[a-zA-Z%]+/, "" )
				},
				baseFIX: function ( e, t, str )
				{
					if ( str.match( /^Expected RBRACE at line \d/ ) ) {
						var line = parseInt( str.replace( /^Expected RBRACE at line ([\d]+),.*/g, '$1' ), 0 );
						var col = parseInt( str.replace( /.*, col ([\d]+)./g, '$1' ), 0 );

						var start = e.substr( 0, col );
						var end = e.substr( col );

						return start + '}' + end;
					}
				}
			},
			html: {
				// HTML JS
				"Missing semicolon.": function ( e, t )
				{
					return e.substr( 0, t.column ) + ";" + e.substr( t.column )
				},
				"Expected '{a}' and instead saw '{b}'.": function ( e, t )
				{
					return e.substr( 0, t.column + 1 ) + "=" + e.substr( t.column + 1 )
				},
				"A leading decimal point can be confused with a dot: '{a}'.": function ( e, t )
				{
					return e.substr( 0, t.column ).replace( /(\.\d+$)/, "0$1" ) + e.substr( t.column )
				},
				"Missing radix parameter.": function ( e, t )
				{
					return e.substr( 0, t.column ) + ", 10" + e.substr( t.column )
				},
				"Mixed spaces and tabs.": function ( e, t )
				{
					var n = "";
					for ( i = 0; i < l.tabSize; i++ )
						n += " ";
					return l.softTabs ? e = e.replace( /\t/g, n ) : e = e.replace( new RegExp( n, "g" ), "	" ), e
				},
				"Unnecessary semicolon.": function ( e, t )
				{
					return e.substr( 0, t.column ) + e.substr( t.column + 1 )
				},
				// HTML CSS
				"Disallow overqualified elements": function ( e, t )
				{
					return e.substr( 0, t.column ) + e.substr( t.column ).replace( /^[^\.#]+/, "" )
				},
				"Disallow units for 0 values": function ( e, t )
				{
					return e.substr( 0, t.column + 1 ) + e.substr( t.column + 1 ).replace( /^[a-zA-Z%]+/, "" )
				},
				// HTML 
				"Special characters must be escaped : [ < ].": function ( e, t )
				{

					var start = e.substr( 0, t.column );
					var s = e.substr( t.column, 1 );
					var end = e.substr( t.column + 1 );
					return start + s.replace( /^[<]/, "&lt;" ) + end;
				},
				"Special characters must be escaped : [ > ].": function ( e, t )
				{
					var start = e.substr( 0, t.column );
					var s = e.substr( t.column, 1 );
					var end = e.substr( t.column + 1 );
					return start + s.replace( /^[>]/, "&gt;" ) + end;
				},
				"Alt of img tag must be set value.": function ( e, t )
				{
					var start = e.substr( 0, t.column );
					var end = e.substr( t.column + 1 );
					return start + ' alt=""' + end;
				},
				'baseFIX': function ( e, t, str )
				{

					if ( str.match( /^'[^\']+' is already defined.$/ ) ) {
						var name = str.replace( /^'([^\']+)' is already defined.$/, '$1' );

						var start = e.substr( 0, t.column );
						var end = e.substr( t.column );

						start = start.replace( 'var ' + name, name );
						start = start.replace( 'var  ' + name, name );
						start = start.replace( 'var   ' + name, name );
						start = start.replace( 'var    ' + name, name );

						return start + end;
					}
					else if ( str.match( /The value of attribute \[\s*([a-z0-9_:]+?)\s*\] must closed by double quotes\./ig ) ) {
						e = e.replace( /=\s*([^\s]*)>/g, '="$1">' );
						e = e.replace( /=\s*([^\s]*)\s/g, '="$1" ' );
						e = e.replace( /""/g, '"' );
						return e;
					}

					return e;
				}
			},
			php: {}
		};

		var r = arrHints[this.hintIndex];

		if ( !r || typeof fixMode[this.mode] === 'undefined' )
			return;

		var isBaseFix = false, fixFunction = r.text.toString(), m = fixMode[this.mode];

		if ( typeof m[fixFunction] !== 'function' ) {
			if ( typeof m['baseFIX'] !== 'function' ) {
				return;
			}
			else {
				isBaseFix = true;
				fixFunction = 'baseFIX';
			}
		}

		var a = this.gotoLine( parseInt( r.row + 1 ) ), str = this.getLine( parseInt( r.row ) );
		r.text || (r.text = r.rule);

		var fixStr = fixMode[this.mode][fixFunction]( str, r, r.text.toString() );

		if ( typeof fixStr == "string" ) {
			// delete hint
			delete arrHints[this.hintIndex];

			if ( this.hintIndex > 0 ) {
				this.hintIndex--;
			}

			this.setLine( parseInt( r.row ) + 1, fixStr );
		}
	};

	this.showLastHint = function ()
	{
		if ( arrHints.length > 0 ) {
			var cursor = this.editor.selection.getCursor(),
				curRow = cursor.row,
				curColumn = cursor.column;
			var hint, hintRow, hintCol;
			for ( var i = arrHints.length - 1; i >= 0; i-- ) {
				hint = arrHints[i];
				hintRow = hint.row;
				hintCol = hint.column;
				if ( hintRow < curRow || (hintRow === curRow && hintCol < curColumn) ) {
					this.editor.moveCursorTo( hintRow, hintCol );
					this.editor.selection.clearSelection();
					break;
				}
			}
		}
		return false;
	};

	this.showNextHint = function ()
	{
		if ( arrHints.length > 0 ) {
			var cursor = this.editor.selection.getCursor(),
				curRow = cursor.row,
				curColumn = cursor.column;
			var hint, hintRow, hintCol;
			for ( var i = 0; i < arrHints.length; i++ ) {
				hint = arrHints[i];
				hintRow = hint.row;
				hintCol = hint.column;
				if ( hintRow > curRow || (hintRow === curRow && hintCol > curColumn) ) {
					this.editor.moveCursorTo( hintRow, hintCol );
					this.editor.selection.clearSelection();
					break;
				}
			}
		}
		return false;
	};

	this.setEditorMode = function ( mode )
	{

		if ( mode === 'stylesheet' ) {
			mode = 'css';
		}
		else if ( mode === 'text' ) {
			mode = 'plain_text';
		}

		this.stopWorker();

		this.editor.getSession().setMode( 'ace/mode/' + mode );
		if ( this.editor.Intellisense ) {
			this.editor.Intellisense.setMode( mode );
		}
		this.mode = mode;
		this.updateWorker();
	};
	var wt;

	this.updateWorker = function ()
	{
		var self = this;

		clearTimeout( wt );

		if ( this.mode === 'php' ) {
			this.noWorker = false;
			this.EnableIntellisense = true;
			this.editor.session.setUseWorker( true );

			this.editor.session.on( 'changeAnnotation', function ( a )
			{

				clearTimeout( wt );
				var aaa = self.editor.session.getAnnotations();

				if ( arrHints.length === 0 || arrHints.length > aaa.length ) {

					self.jQvalidateSyntax.hide();
					self.jQcheckingSyntax.show();

					wt = setTimeout( function ()
					{
						self.jQvalidateSyntax.hide();
						self.jQcheckingSyntax.show();
						self.setCustomHint( aaa );
						document.body.style.cursor = '';
					}, 400 );
				}
			} );
		}
		else if ( this.mode === 'javascript' ) {
			this.noWorker = false;
			this.EnableIntellisense = true;

			this.editor.session.setUseWorker( true );

			this.editor.session.on( 'changeAnnotation', function ( a )
			{
				clearTimeout( wt );
				var aaa = self.editor.session.getAnnotations();

				if ( arrHints.length === 0 || arrHints.length > aaa.length ) {
					self.jQvalidateSyntax.hide();
					self.jQcheckingSyntax.show();

					wt = setTimeout( function ()
					{
						self.jQvalidateSyntax.hide();
						self.jQcheckingSyntax.show();
						self.setCustomHint( aaa, 'javascript' );
						document.body.style.cursor = '';
					}, 400 );
				}
			} );
		}
		else if ( this.mode === 'html' ) {
			this.noWorker = false;
			this.EnableIntellisense = true;

			this.editor.session.setUseWorker( true );

			this.editor.session.on( 'changeAnnotation', function ( a )
			{
				clearTimeout( wt );
				var aaa = self.editor.session.getAnnotations();

				if ( arrHints.length === 0 || arrHints.length > aaa.length ) {
					self.jQvalidateSyntax.hide();
					self.jQcheckingSyntax.show();

					wt = setTimeout( function ()
					{
						self.jQvalidateSyntax.hide();
						self.jQcheckingSyntax.show();
						self.setCustomHint( aaa, 'javascript' );
						document.body.style.cursor = '';
					}, 400 );
				}
			} );
		}
		else {
			this.noWorker = true;
		}

	};

	this.getValue = function ()
	{
		return this.editor.getValue();
	};

	var lastLine = 0, lastColumn = 0, ttt;
	var ti, upTimer;

	this.initStatusbar = function ()
	{

		clearTimeout( upTimer );
		clearTimeout( ti );
		clearTimeout( cursorChangeTimeout );

		var self = this, position = this.editor.selection.getCursor();
		this.jqEditorStatusbar.find( '.line span' ).text( (position.row + 1) );
		this.jqEditorStatusbar.find( '.column span' ).text( (position.column + 1) );
		var base, text = this.editor.getSession().getValue(), lastLine = 0, lastColumn = 0;
		this.jqEditorStatusbar.find( '.length span' ).text( text.length );

		// current line
		if ( this.editor.getSession() && typeof this.editor.getSession().selection != 'undefined' ) {
			this.editor.getSession().selection.on( 'changeCursor', function ()
			{

				clearTimeout( cursorChangeTimeout );
				// Show current line and column
				var position = self.editor.selection.getCursor();
				self.jqEditorStatusbar.find( '.line span' ).text( (position.row + 1) );
				self.jqEditorStatusbar.find( '.column span' ).text( (position.column + 1) );

				// hide Intellisense on cursor is changed column or row
				if ( self.editor.Intellisense.isVisible && (lastLine != position.row || lastColumn != position.column) ) {
					cursorChangeTimeout = setTimeout( function ()
					{
						self.editor.Intellisense.hide();
						lastLine = position.row;
						lastColumn = position.column;

					}, 200 );
				}

				$( '#contextmenu-' + self.editorID ).remove();

			} );

		}

		// total count of lines
		this.editor.getSession().on( 'change', function ( e )
		{

			clearTimeout( upTimer );
			clearTimeout( ti );

			var text = self.editor.getSession().getValue();
			self.jqEditorStatusbar.find( '.length span' ).text( text.length );

			// Get the value from the editor and place it into the textarea.
			self.textarea.get( 0 ).value = text;
			self.setDirty();

			ti = setTimeout( function ()
			{
				$( '#contextmenu-' + self.editorID ).remove();
			}, 200 );

			// HTML code in PHP
			if ( !self.disableHint && ((self.mode !== 'html' && self.editor.Intellisense.dictMode === 'html') || self.mode == 'html') ) {
				upTimer = setTimeout( function ()
				{
					self.updateHTMLHint();
				}, 500 );
			}
		} );
	};

	this.setEditorToolbar = function ()
	{
		if ( this.editorToolbar !== null ) {
			var self = this;
			this.editorToolbar.find( 'li' ).each( function ()
			{
				var clsName = $( this ).attr( 'class' );
				if ( clsName.match( /undo/g ) ) {
					$( this ).removeAttr( 'onclick' ).on( 'click.sourceedit', function ()
					{
						self.undo();
					} );
				}
				else if ( clsName.match( /redo/g ) ) {
					$( this ).removeAttr( 'onclick' ).on( 'click.sourceedit', function ()
					{
						self.redo();
					} );
				}

				else if ( clsName.match( /italic/g ) ) {
					$( this ).removeAttr( 'onclick' ).on( 'click.sourceedit', function ()
					{
						self.insertTag( 'em' );
					} );
				}
				else if ( clsName.match( /strong/g ) ) {
					$( this ).removeAttr( 'onclick' ).on( 'click.sourceedit', function ()
					{
						self.insertTag( 'strong' );
					} );
				}
				else if ( clsName.match( /underline/g ) ) {
					$( this ).removeAttr( 'onclick' ).on( 'click.sourceedit', function ()
					{
						var t = this.getCursorPosition();
						self.wrapSelection( "/*", "*/" );
					} );
				}
				else if ( clsName.match( /sourceEditor-reindent/g ) ) {
					$( this ).removeAttr( 'onclick' ).on( 'click.sourceedit', function ()
					{
						self.reindentCode();
					} );
				}
				else if ( clsName.match( /sourceEditor-fullscreen/g ) ) {
					$( this ).removeAttr( 'onclick' ).on( 'click.sourceedit', function ()
					{
						self.fullscreenEdit();
					} );
				}

			} );
			this.selectFontTool();
			this.selectThemeTool();
			Win.prepareWindowFormUi();
		}

	};
	this.selectFontTool = function ()
	{
		var self = this, fontSize = '<select id=\"selectFont_' + this.editorID + '\">' +
			'<option value="10px">10px</option>' +
			'<option value="11px">11px</option>' +
			'<option value="12px" selected="selected">12px</option>' +
			'<option value="13px">13px</option>' +
			'<option value="14px">14px</option>' +
			'<option value="15px">15px</option>' +
			'<option value="16px">16px</option>' +
			'<option value="18px">18px</option>' +
			'<option value="20px">20px</option>' +
			'</select>';
		this.editorToolbar.append( $( '<li>' ).addClass( 'sourceEditor-headline-selects' ).append( fontSize ) );
		this.editorToolbar.find( '#selectFont_' + this.editorID ).bind( 'change.sourceedit', function ()
		{
			$( '#' + self.editorID ).css( 'font-size', $( this ).val() );
		} );
	};

	this.selectThemeTool = function ()
	{
		var self = this, theme = '<select id="selectTheme_' + this.editorID + '">' +
			'<option value="twilight">Twilight</option>' +
			'<option value="netbeans">Netbeans Light</option>' +
			'<option value="netbeans_dark">Netbeans Dark</option>' +
			'<option value="eclipse">Eclipse</option>' +
			'<option value="ambiance">Ambiance</option>' +
			'<option value="textmate">Textmate</option>' +
			'<option value="solarized_light">Solarized</option>' +
			'<option value="xcode">XCode</option>' +
			'<option value="cobalt">Cobalt</option>' +
			'<option value="vibrant_ink">Vibrant</option>' +
			'<option value="monokai">Monokai</option>' +
			'<option value="github">GitHub</option>' +
			'<option value="kr_theme">krTheme</option>' +
			'<option value="merbivore">merbivore</option>' +
			'<option value="merbivore_soft">merbivore soft</option>' +
			'<option value="tomorrow_night">Tomorrow Night</option>' +
			'<option value="mono_industrial">Mono Industrial</option>' +
			'<option value="dreamweaver">Dreamweaver</option>' +
			'<option value="chrome">Chrome</option>' +
			'<option value="terminal">Terminal</option>' +
			'</select>';
		this.editorToolbar.append( $( '<li>' ).addClass( 'sourceEditor-headline-selects' ).append( theme ) );
		this.editorToolbar.find( '#selectTheme_' + this.editorID ).find( 'option' ).each( function ()
		{
			if ( $( this ).attr( 'value' ) == self.aceopts.theme ) {
				$( this ).attr( 'selected', true );
			}
		} );
		this.editorToolbar.find( '#selectTheme_' + this.editorID ).bind( 'change.sourceedit', function ()
		{

			var value = $( this ).val();

			self.jqWrapper.attr( 'class', 'ace-wrapper' );

			if ( !self.loadedThemes[value] ) {
				Loader.require( 'Vendor/ace/theme-' + value, function ()
				{
					// set theme

					self.jqWrapper.addClass( 'ace-' + value );
					self.editor.setTheme( 'ace/theme/' + value );
					self.loadedThemes[value] = true;
				} );
			}
			else {
				// set theme
				self.jqWrapper.addClass( 'ace-' + value );
				self.editor.setTheme( 'ace/theme/' + value );
			}

			self.aceopts.theme = value;

		} );
	};

	this.getEditor = function ()
	{
		return this.editor;
	};

	this.autocompleteState = function ()
	{
		var range = this.editor.getSelectionRange(),
			v = range.start,
			y = this.editor.getLine( v.row ).substr( 0, v.column ),
			session = this.editor.getSession(),
			w = v.row > 0 ? v.row - 1 : 0,
			EditorState = this.editor.getState( w, v.column ),
			mode = session.getMode(),
			x = mode.getTokenizer(),
			token = x.getLineTokens( y, EditorState, v.row ),
			tokenState = typeof token.state == "object" ? token.state[0] : token.state,
			currentMode = this.editor.mode !== "php" ? this.editor.mode : "html",
			C = (tokenState.match( /(php|js|css)\-/i ) || ["", currentMode])[1].toLowerCase(), C2 = C;

		var set = C;

		if ( C === "html" || C == "js" ) {
			C2 = 'html';
		}
		if ( C == "php" ) {
			C2 = 'php';
		}
		if ( C == "js" ) {
			C2 = 'html';
		}

		this.Intellisense.setEditorMode( C2 );
	};

};

var tt;
function resizeAce( wm, delta_x, delta_y )
{
	if ( typeof wm.id != 'undefined' && !wm.id )
		return;

	var $windowObj = typeof wm.$el != 'undefined' ? $( wm.$el ) : $( wm );
	var sources = $windowObj.find( 'textarea.sourceEdit' );

	if ( sources.length == 0 ) {
		return;
	}

	sources.each( function ()
	{
		var aceData = $( this ).data( 'ace' );
		var aceElements = $( this ).data( 'aceElements' );
		if ( aceData && typeof aceData.editor != 'undefined' && aceElements ) {
			var aceE = aceData.editor;
			if ( aceE && !aceData.fullscreen ) {
				var maxWidth = typeof wm.$el != 'undefined' ? $( '#body-' + wm.id ).width() : $( '#content-container' ).width();
				var maxHeight = typeof wm.$el != 'undefined' ? wm.getContentHeight() : $( '#content-container' ).outerHeight( true );



					if ( $windowObj.hasClass( 'no-padding' ) || $( '#content-container' ).hasClass( 'no-padding' ) ) {
						var h = 0, bar = aceData.jqErrorBar;
						if ( bar && bar.is( ':visible' ) ) {
							h = bar.outerHeight( true );
						}
						if ( aceElements.aceStatusBar.length === 1 ) {
							h += parseInt( aceElements.aceStatusBar.outerHeight( true ), 10 );
						}
						aceElements.wrapper.style.width = maxWidth + 'px';
						aceElements.wrapper.style.height = maxHeight + 'px';
						aceElements.aceContainer.style.width = maxWidth + 'px';
						aceElements.aceContainer.style.height = (maxHeight - h) + 'px';
					}
					else {
						maxWidth = parseInt( $( this ).parents( 'fieldset:first' ).width(), 0 ) - 20;
						if ( !maxWidth ) {
							maxWidth = parseInt( $( this ).parents( 'div.box' ).innerWidth(), 0 ) - 20;
						}
						maxHeight = parseInt( $( this ).height(), 10 );

						if ( maxHeight < 220 ) {
							maxHeight = 220;
						}

						var h = 0, bar = aceData.jqErrorBar;
						if ( aceElements.aceStatusBar.length === 1 ) {
							h += parseInt( aceElements.aceStatusBar.height(), 10 );
						}
						aceElements.wrapper.style.width = maxWidth + 'px';
						aceElements.wrapper.style.height = maxHeight + 'px';
						aceElements.aceContainer.style.width = maxWidth + 'px';
						aceElements.aceContainer.style.height = (maxHeight - h) + 'px';
					}

				aceE.focus();

				if ( aceE.renderer ) {
					aceE.renderer.onResize( true );
					aceE.renderer.updateFull();
				}

				aceData.refreshAfterResize();
			}
		}
	} );
}

function insTag( tagName )
{
	if ( tagName && focusedAceEdit !== null ) {
		focusedAceEdit.insertTag( tagName );
	}
}

function setFullScreen( cm, full )
{
	var wrap = cm.getWrapperElement();
	if ( full ) {
		wrap.className += " CodeMirror-fullscreen";
		document.documentElement.style.overflow = "hidden";
		$( '<span>' ).addClass( 'editor-place' ).css( {
			height: '0'
		} ).insertBefore( $( wrap ) );
		$( wrap ).appendTo( $( 'body' ) );
	} else {

		var place = $( 'span.editor-place' );
		wrap.className = wrap.className.replace( " CodeMirror-fullscreen", "" );
		wrap.style.height = "";
		document.documentElement.style.overflow = "";
		$( wrap ).insertAfter( $( place ) );
		place.remove();
	}

	cm.refresh();
	cm.focus();
}

function toggleFullScreen()
{
	var sourceCodeEditor = $( '#' + Win.windowID ).data( 'ace' );
	var edit = sourceCodeEditor.getEditor();
	setFullScreen( edit, !isFullScreen( edit ) );
}

function createTemplateEditor( windowID, callback )
{
	if ( !windowID )
		return;

	var $windowObj = $( '#' + windowID );
	var areas = $windowObj.find( 'textarea.sourceEdit' );
	if ( areas.length == 0 ) {
        if (callback) {
            callback();
        }
		return;
	}
	$windowObj.mask( 'Please wait...' );

	if ( !Desktop.isWindowSkin ) {
		$windowObj.parents( '#content-container' ).addClass( 'no-padding' );
	}

	$windowObj.data( 'nopadding', true );

	if ( typeof window.ace == 'undefined' ) {
		Loader.require( [
			'Vendor/ace/htmlhint',
			'Vendor/ace/ace',
			'Vendor/ace/ext-chromevox',
			'Vendor/ace/ext-elastic_tabstops_lite',
			'Vendor/ace/ext-emmet',
			'Vendor/ace/emmet',
			'Vendor/ace/jshint',
			'Vendor/ace/csslint',
			'Vendor/ace/ext-keybinding_menu',
			'Vendor/ace/ext-language_tools',
			'Vendor/ace/ext-modelist',
			'Vendor/ace/ext-settings_menu',
			'Vendor/ace/ext-static_highlight',
			//   'Vendor/ace/ext-statusbar',
			//   'Vendor/ace/ext-textarea',
			'Vendor/ace/ext-themelist',
			'Vendor/ace/ext-whitespace',
			'Vendor/ace/worker-javascript',
			'Vendor/ace/worker-php',
            'Vendor/ace/worker-css',
			'Vendor/ace/keybinding-emacs',
			'Vendor/ace/keybinding-vim',
			'Vendor/ace/theme-netbeans',
			'Vendor/ace/mode-html',
			'public/html/js/backend/tpleditor/dcms.ace.token_tooltip',
			'public/html/js/backend/tpleditor/dcms.ace.intellisense',
			'html/js/backend/tpleditor/beautifier/lib/beautify.js',
             /*'html/js/backend/tpleditor/beautifier/lib/beautify-css.js',
			'html/js/backend/tpleditor/beautifier/lib/beautify-html.js',
			'html/js/backend/tpleditor/beautifier/test/sanitytest.js',
			'html/js/backend/tpleditor/beautifier/test/beautify-tests.js',
			'html/js/backend/tpleditor/beautifier/lib/unpackers/javascriptobfuscator_unpacker.js',
			'html/js/backend/tpleditor/beautifier/lib/unpackers/urlencode_unpacker.js',
			'html/js/backend/tpleditor/beautifier/lib/unpackers/p_a_c_k_e_r_unpacker.js',
			'html/js/backend/tpleditor/beautifier/lib/unpackers/myobfuscate_unpacker.js'
			*/
		], function ()
		{



			//  $windowObj.addClass('no-padding');
			Win.redrawWindowHeight( windowID, true );

			setTimeout( function ()
			{
				areas.each( function ()
				{
					if ( !$( this ).data( 'ace' ) ) {

						var id = $( this ).attr( 'id' );
						var editorID = 'ace-' + (id ? id : $( this ).attr( 'name' ).replace( '[', '_' ).replace( ']', '_' )) + 'ace';
						$( this ).parent().css( {position: 'relative'} );

						var height = $( this ).parent().height();

						if ( height < 180 ) {
							height = 220;
						}
						$( '<div id="' + editorID + '" />' ).css( {height: height} ).insertBefore( $( this ) );

						$( '#' + editorID ).wrap( $( '<div id="' + editorID + '-wrapper"/>' ).addClass( 'ace-wrapper' ) );

						var editor = new AceEdit;
						editor.init( editorID, this, $windowObj );
						$( this ).attr( 'aceid', editorID ).data( 'ace', editor );

						var wrapper = $( '#' + editorID + '-wrapper' ).get( 0 );
						var aceContainer = $( '#' + editorID ).get( 0 );

						var aceStatusBar = $windowObj.find( 'div.ace-status-bar' );

						$( this ).data( 'aceElements', {'wrapper': wrapper, 'aceContainer': aceContainer, 'aceStatusBar': aceStatusBar} );

						if ( Desktop.isWindowSkin ) {
							$windowObj.data( 'WindowManager' ).set( 'onResize', function ( e, wm, contentsize )
							{
								Win.redrawWindowHeight( wm.id, true );
								resizeAce( wm );
							} );

							$windowObj.data( 'WindowManager' ).set( 'onResizeStop', function ( e, wm, contentsize )
							{
								setTimeout( function ()
								{
									resizeAce( wm );
									$( '#' + windowID ).unmask();
									Win.redrawWindowHeight( windowID, true );
								}, 5 );
							} );
						}
						else {
							$windowObj.data( 'ace', editor );
						}
					}
				} );

				if ( Desktop.isWindowSkin ) { Win.redrawWindowHeight( windowID, true ); }

				if ( $windowObj.find( '#editContainer' ).length == 1 ) {
					$windowObj.find( 'div.pane:first' ).hide();
				}

				if ( Desktop.isWindowSkin ) {
					setTimeout( function ()
					{
						if ( Desktop.isWindowSkin ) {
							Win.redrawWindowHeight( windowID, true );
							resizeAce( $windowObj.data( 'WindowManager' ) );
							Win.redrawWindowHeight( windowID, true );
						}
						else {
							resizeAce( '#' + windowID );
						}

						if ( $windowObj.find( '#editContainer' ).length == 1 ) {
							$windowObj.find( 'div.pane:first' ).hide();
						}
					}, 10 );
				}
				else {
					resizeAce( '#' + windowID );

					if ( $windowObj.find( '#editContainer' ).length == 1 ) {
						$windowObj.find( 'div.pane:first' ).hide();
					}

                    if ( callback ) {
                        callback();
                    }
				}

			}, 10 );
		} );
	}
	else {
		Win.redrawWindowHeight( windowID, true );

		$windowObj.find( '.sourceEdit' ).each( function ()
		{
			if ( !$( this ).data( 'ace' ) ) {
				var id = $( this ).attr( 'id' );
				var editorID = 'ace-' + (id ? id : $( this ).attr( 'name' ).replace( '[', '_' ).replace( ']', '_' )) + 'ace';
				var height = $( this ).parent().height();
				if ( height < 180 ) {
					height = 220;
				}
				$( '<div id="' + editorID + '" />' ).css( {height: height} ).insertBefore( $( this ) );

				$( '#' + editorID ).wrap( $( '<div id="' + editorID + '-wrapper"/>' ).addClass( 'ace-wrapper' ) );

				var editor = new AceEdit;
				editor.init( editorID, this, $windowObj );
				$( this ).attr( 'aceid', editorID ).data( 'ace', editor );

				var wrapper = $( '#' + editorID + '-wrapper' ).get( 0 );
				var aceContainer = $( '#' + editorID ).get( 0 );
				var aceStatusBar = $windowObj.find( '.ace-status-bar' );

				$( this ).data( 'aceElements', {'wrapper': wrapper, 'aceContainer': aceContainer, 'aceStatusBar': aceStatusBar} );

				if ( Desktop.isWindowSkin ) {
					$windowObj.data( 'WindowManager' ).set( 'onResize', function ( e, wm, contentsize )
					{
						Win.redrawWindowHeight( wm.id, true );
						resizeAce( wm );
					} );

					$windowObj.data( 'WindowManager' ).set( 'onResizeStop', function ( e, wm, contentsize )
					{
						setTimeout( function ()
						{

							resizeAce( wm );
							$( '#' + windowID ).unmask();
							Win.redrawWindowHeight( windowID, true );
						}, 5 );
					} );

					setTimeout( function ()
					{
						Win.redrawWindowHeight( windowID, true );
						resizeAce( $windowObj.data( 'WindowManager' ) );
						Win.redrawWindowHeight( windowID, true );
					}, 10 );
				}
				else {
					$windowObj.data( 'ace', editor );
					resizeAce( '#' + windowID );

                    if ( callback ) {
                        callback();
                    }
				}
			}
		} );
	}

}
