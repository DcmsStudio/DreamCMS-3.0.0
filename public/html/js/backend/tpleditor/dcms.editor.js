window.editorLoaded = true;
window.cmThemesLoaded = false;

top.currentHoveredTag = undefined;

var useLineNumbers = true;
var useWrapping = false;
var wizardOpen = false;
var isFullscreen = false;
var dcmseditor;
var hlLine;
var activeCodeMirror = null;
var __cmirrors = [];
var __modLoaded = [];


var wizardHeight = {
    "open": 155,
    "closed": 140
};

var initCmThemes = function() {

    if (window.cmThemesLoaded)
    {
        return;
    }

    var themes = ['neat',
        "elegant", "erlang-dark", "night", "monokai", "cobalt", "eclipse", "rubyblue"
                , 'lesser-dark'
                , 'xq-dark'
                , 'xq-light'
                , 'ambiance'
                , 'blackboard'
                , 'vibrant-ink'
                , 'solarized'
                , 'twilight'];
    for (var x = 0; x < themes.length; x++)
    {
        $('head').prepend('<link href="Vendor/codemirror/theme/' + themes[x] + '.css" rel="stylesheet" type="text/css"/>');
    }

    window.cmThemesLoaded = true;
};

var hideDescription = function()
{
    $('#tagDescriptionDiv').hide();
};





function _dcmsSourceEditor() {
    /*
     _dcmsSourceEditor: function()
     {
     if (!(this instanceof _dcmsSourceEditor))
     return new _dcmsSourceEditor();
     return this;
     }, */
    this.status = {
        line: $('#editor-status .line>span'),
        col: $('#editor-status .column>span'),
        codeLength: $('#editor-status .length>span')
    };
    this.nodes = []; // DOM Node Container

    this.currentLine = 1;
    this.currentCol = 0;
    this.c = {
        lines: 0,
        'line': 0,
        'column': 0
    };
    // settings
    this.useWrapping = true;
    this.useLineNumbers = true;
    // onjects
    this.markerContainer = $('#eScrollContainer');
    this.activeLine = $('#activeLine');
    this.activeLineBg = null;
    this.dcmseditor = null;
    this.lineContainer = null;
    this.jq_lineContainer = null;
    this.editorFrame = null;
    this.jq_editorFrame = null;
    this.editorFrameBody = null;
    this.foldFunc_html = null;
    this.Window = null;
    this.config = {};
    this.cscc = null;
    this.textarea = null;
    this.lastKeyCode = null;
    this.lastChar = null;
    this.focused = false;
    this.isDirty = false;
    this.isInited = false;

    this.cmComp = null;
    this.windowFormConfig = false;

    this.selfCloseTags = ['img', 'meta', 'hr', 'link', 'base', 'embed', 'input', 'area', 'param', 'source', 'command', 'track', 'wbr'];
    this.htmlIndent = ["applet", "blockquote", "body", "button", "div", "dl", "fieldset", "form", "frameset", "h1", "h2", "h3", "h4",
        "h5", "h6", "head", "html", "iframe", "layer", "legend", "object", "ol", "p", "select", "table", "ul"];

    this.mode = null;

    this.getConfig = function(modname) {


        var lastKeyCode;
        var xself = this;

        CodeMirror.modeURL = "/Vendor/codemirror/mode/%N/%N.js";

        // code fold function
        if (this.foldFunc_html == null)
            this.foldFunc_html = CodeMirror.newFoldFunction(CodeMirror.tagRangeFinder, "\u2194");

        if (this.foldFunc_brace == null)
            this.foldFunc_brace = CodeMirror.newFoldFunction(CodeMirror.braceRangeFinder, "\u2194");

        var mode = 'application/xml';
        if (Tools.mimeTypes[modname])
        {
            if (typeof Tools.mimeTypes[modname] !== 'string')
            {
                mode = Tools.mimeTypes[modname][0];
            }
            else
            {
                mode = Tools.mimeTypes[modname]
            }
        }


        var config = {
            textWrapping: xself.useWrapping,
            gutters: ["CodeMirror-linenumbers", "code-fold-gutter"],
            //continuousScanning: 500,
            autoMatchParens: true,
            matchBrackets: true,
            indentUnit: 4,
            indentWithTabs: true,
            lineNumbers: xself.useLineNumbers,
            //syntax: mode,
            mode: mode,
            //theme: '',
            lineNumberDelay: 180,
            lineWrapping: true,
            autoCloseTags: true,
            extraKeys: {
                "'>'": function(cm) {
                    xself.triggerCloseTag(cm, '>');
                },
                "'/'": function(cm) {
                    xself.triggerCloseTag(cm, '/');
                },
                "F11": function(cm) {
                    setFullScreen(cm, !isFullScreen(cm));
                },
                "Esc": function(cm) {
                    if (isFullScreen(cm))
                        setFullScreen(cm, false);
                },
                "Ctrl-I": function(cm) {
                    reindent()
                }
            },
            autofocus: false,
            onKeyEvent: function(thisCM, e) {
                var mode = thisCM.getMode();
                var filter = '', key = e.keyCode, chr = String.fromCharCode(e.keyCode);
                var alpha = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                var num = '0123456789';
                var underdash = '_', cmComp;


                if (xself.cmComp === null)
                {
                    cmComp = xself.cmComp = $('.CodeMirror-completions');
                }
                else
                {
                    cmComp = xself.cmComp;
                }

                var skipStop = true, nonChar = false;

                if (!xself.isDirty)
                {
                    if (xself.windowFormConfig && xself.windowFormConfig.formid)
                    {
                        xself.windowFormConfig.setDirty(null, $('#' + xself.windowFormConfig.formid));
                    }
                }

                if (mode.name == 'php')
                {

                    if (key < 16 || // non printables
                            (key > 16 && key < 32) || // avoid shift
                            (key > 32 && key < 41) || // navigation keys
                            key == 46) {              // Delete Key (Add to these if you need)

                        nonChar = true;
                    }
                    else
                        nonChar = false;



                    if (key == 8)
                    {
                        cmComp.hide().remove();
                    }

                    if (e.type == "keydown")
                    {
                        skipStop = CodeMirror.simpleHint.keyDownEvent(e, xself.dcmseditor, thisCM, CodeMirror.phpHint);
                    }


                    if (skipStop === false)
                    {
                        xself.lastChar = null;
                        xself.lastKeyCode = e.keyCode;
                        e.stop();
                        return true;
                    }


                    if (key != 38 || key != 40 || key != 9 && !nonChar)
                    {
                        filter = num + alpha + underdash;

                        if (e.type == "keyup") {

                            if ((xself.lastChar && filter.indexOf(xself.lastChar) > -1) && (filter.indexOf(chr) > -1) && chr != '' && chr != ' ' && key > 31 && key < 256) {

                                if (cmComp.length)
                                {
                                    cmComp.hide().remove();
                                }

                                CodeMirror.simpleHint(xself.dcmseditor, CodeMirror.phpHint);
                                xself.lastChar = chr;
                                return;
                            }

                            if (chr != ' ' && cmComp.length)
                            {
                                cmComp.hide().remove();
                            }
                        }


                    }

                    if (nonChar)
                    {
                        xself.lastChar = null;
                        xself.lastKeyCode = e.keyCode;
                    }
                }
                else
                {
                    cmComp.hide().remove();

                    if (key == "8")
                    {
                        cscc.hide();
                        xself.lastKeyCode = e.keyCode;
                        return false;
                    }

                    var skipStop = true;
                    if (e.type == "keyup")
                    {
                        skipStop = cscc.keyUp(e, thisCM, thisCM);
                    }
                    if (e.type == "keydown")
                    {
                        skipStop = cscc.keyDown(e, thisCM, thisCM);
                    }
                    if (e.type == "keypress")
                    {
                        skipStop = cscc.keyPress(e, thisCM, thisCM);
                    }

                    if (skipStop === false)
                    {
                        xself.lastKeyCode = e.keyCode;
                        e.stop();
                        return true;
                    }

                    return;


                    if (e.type == "keyup" && e.keyCode == "16" && xself.lastKeyCode == "50") {
                        thisCM.replaceRange('"', thisCM.getCursor());
                        thisCM.setCursor(thisCM.getCursor().line, thisCM.getCursor().ch - 1);
                        xself.lastKeyCode = e.keyCode;
                    }

                    if (e.type == "keyup" && e.keyCode == "16" && xself.lastKeyCode == "163") {
                        thisCM.replaceRange("'", thisCM.getCursor());
                        thisCM.setCursor(thisCM.getCursor().line, thisCM.getCursor().ch - 1);
                        xself.lastKeyCode = e.keyCode;
                    }

                    var cx, htmlTagArray = [];

                    if (e.type == "keyup" && e.keyCode == "16" && xself.lastKeyCode == "190") {

                        // Set up array to store nest data
                        var state = thisCM.getTokenAt(thisCM.getCursor()).state;

                        if ("undefined" != typeof state) {

                            for (cx = state.context; cx; cx = cx.prev) {
                                if ("undefined" != typeof cx.tagName) {
                                    htmlTagArray.unshift(cx.tagName);
                                }
                            }
                        }
                        else
                        {
                            xself.lastKeyCode = e.keyCode;
                            return;
                        }

                        var tagString = htmlTagArray[htmlTagArray.length - 1];
                        var lastString, tok = thisCM.getTokenAt(thisCM.getCursor());
                        if (tok.string != ">") {
                            lastString = tok.string
                        }

                        if (!tagString)
                        {
                            xself.lastKeyCode = e.keyCode;
                            e.stop();
                            return true;
                        }

                        var canDoEndTag = true;
                        if (in_array(tagString, xself.selfCloseTags))
                        {
                            canDoEndTag = false;
                            xself.lastChar = chr;
                            xself.lastKeyCode = e.keyCode;


                            thisCM.replaceSelection("/>");
                            thisCM.setCursor(thisCM.getCursor().line, thisCM.getCursor().ch - tagString.length - 3);
                            return;
                        }



                        if (tagString != "script" && ((tagString + lastString).slice(0, 1) == "/" || (tagString + lastString).slice(0, 1) == "?")) {
                            canDoEndTag = false;
                        }

                        if (canDoEndTag) {
                            var numTabs = htmlTagArray.length;

                            if (htmlTagArray[0] == "html") {
                                numTabs--;
                            }

                            var i, tabs = "";
                            for (i = 0; i < numTabs - 1; i++) {
                                tabs += "\t";
                            }

                            var endTag = "</" + tagString + ">";

                            if (tagString == "script") {
                                endTag = "</" + "script>"
                            }

                            if (tagString == "title" || tagString == "a" || tagString == "li" || tagString == "span" || (tagString.slice(0, 1) == "h" && parseInt(tagString.slice(1, 2), 10) >= 1 && parseInt(tagString.slice(1, 2), 10) <= 7)) {
                                thisCM.replaceSelection(endTag);
                                thisCM.setCursor(thisCM.getCursor().line, thisCM.getCursor().ch - tagString.length - 3);
                            }
                            else if (tagString == "html" || tagString == "head") {
                                thisCM.replaceSelection("\n\n" + endTag);
                                thisCM.setCursor(thisCM.getCursor().line - 1, numTabs);
                            }
                            else {
                                thisCM.replaceSelection("\n" + tabs + "\t\n" + tabs + endTag);
                                thisCM.setCursor(thisCM.getCursor().line - 1, numTabs);
                            }
                        }
                    }
                    ;
                }
                xself.lastChar = chr;
                xself.lastKeyCode = e.keyCode;

                return true;
            },
            updateErrorLines: function()
            {
                xself.updateErrorLines();
            },
            updateLineBar: function()
            {
                xself.updateLineBar();
            },
            initCallback: function()
            {
                xself.initCallback();
            },
            activeTokens: function(span, token)
            {
                xself.activeTokens(span, token);
            },
            cursorActivity: function(el)
            {
                xself.cursorActivity(el);
              //  console.log('cursorActivity');
            }
        }; // end config

        if (modname == 'javascript') {
            CodeMirror.commands.autocomplete = function(cm) {
                CodeMirror.simpleHint(xself.dcmseditor, CodeMirror.javascriptHint);
            };
            config.extraKeys['Ctrl-Space'] = "autocomplete";
        }

        if (modname == 'php') {
            CodeMirror.commands.autocomplete = function(cm) {
                CodeMirror.simpleHint(xself.dcmseditor, CodeMirror.phpHint);
            };
            config.extraKeys['$'] = function(cm) {
                CodeMirror.simpleHint(xself.dcmseditor, CodeMirror.phpHint);
            };
            config.extraKeys['_'] = function(cm) {
                CodeMirror.simpleHint(xself.dcmseditor, CodeMirror.phpHint);
            };

            config.extraKeys['Ctrl-Space'] = "autocomplete";
        }

        return config;
    };

    this.init = function(windowID, textarea)
    {
        if (this.isInited)
        {
            return;
        }

        this.isInited = true;

        // initCmThemes();
        this.Window = $('#' + windowID);

        $('#tagDescriptionDiv').remove();
        if (!$('#tagDescriptionDiv').lenght)
        {
            $('body').append($('<div>').attr("id", "tagDescriptionDiv"));
        }

        this.Window.data('WindowManager').set('hasTemplateEditor', true);
        this.windowFormConfig = $(this.Window).data('formConfig');

        var classname = $(textarea).attr('class');
        var mode = 'application/xml';
        var modname = 'xml';
        var interval, activeCodeMirror, dcmseditor;

        if (classname)
        {
            var clean = classname;

            if (clean.match(/php/))
            {
                mode = 'application/x-httpd-php';
                modname = 'php';
            }
            else if (clean.match(/js/) || clean.match(/javascript/))
            {
                mode = 'text/javascript';
                modname = 'javascript';
            }
            else if (clean.match(/css/))
            {
                mode = 'text/css';
                modname = 'css';
            }
            
            if (clean.match(/xml/) || clean.match(/html/) && !clean.match(/php/))
            {
                mode = 'application/xml';
                modname = 'xml';
            }
        }


        if (mode && typeof __modLoaded[modname] == 'undefined')
        {
            if (modname === 'xml')
            {
                Tools.loadScript('Vendor/codemirror/mode/htmlmixed/htmlmixed.js', function() {
                    Tools.loadScript('Vendor/codemirror/mode/' + modname + '/' + modname + '.js', function() {
                        __modLoaded[modname] = true;
                        __modLoaded['htmlmixed'] = true;
                    });
                });
            }
            else
            {
                Tools.loadScript('Vendor/codemirror/mode/' + modname + '/' + modname + '.js', function() {
                    __modLoaded[modname] = true;
                });
            }
        }

        if (modname)
        {
            this.mode = modname;
        }

        var config = this.getConfig(this.mode), editorObj = $('.sourceEdit:first', this.Window);

        config.editorObj = editorObj;

        textarea.value = textarea.value.replace(/{\*(\s*)/gi, '{* ');
        textarea.value = textarea.value.replace(/(\s*)\*}/gi, ' *}');


        config.value = textarea.value;

        this.config = config;
        dcmseditor = CodeMirror.fromTextArea(textarea, config);

        return this.runAfterLoaded(dcmseditor, textarea);
    };



    this.indexOf = function(collection, elt) {
        if (collection.indexOf)
            return collection.indexOf(elt);
        for (var i = 0, e = collection.length; i < e; ++i)
            if (collection[i] == elt)
                return i;
        return -1;
    };


    this.triggerCloseTag = function(cm, ch)
    {
        // Set up array to store nest data
        var cx, htmlTagArray = [], state = cm.getTokenAt(cm.getCursor()).state;

        var pos = cm.getCursor(), lineText = cm.lineInfo(pos.line).text, tok = cm.getTokenAt(pos);
        var inner = CodeMirror.innerMode(cm.getMode(), tok.state), state = inner.state;

        var tagName = state.tagName;
        var instring = '';

        var type = inner.mode.configuration == "html" ? 'html' : inner.mode.configuration; // htmlmixed : xml


        if (tok.end > pos.ch) {
            tagName = tagName.slice(0, tagName.length - tok.end + pos.ch);
            instring = lineText.slice(pos.ch, lineText.length);
        }


        if ("undefined" != typeof state) {
            for (cx = state.context; cx; cx = cx.prev) {
                if ("undefined" != typeof cx.tagName) {
                    htmlTagArray.unshift(cx.tagName);
                }
            }
        }

        function indexOf(collection, elt) {
            if (collection.indexOf)
                return collection.indexOf(elt);
            for (var i = 0, e = collection.length; i < e; ++i)
                if (collection[i] == elt)
                    return i;
            return -1;
        }


        if (ch == ">" && state.tagName) {
            var lowerTagName = tagName.toLowerCase();

            if (in_array(lowerTagName, this.selfCloseTags))
            {
                if (tok.type == "cptag" && state.type == "closeTag" && (/\s*["']\s*>/.test(instring) || /\/\s*$/.test(tok.string)))
                {
                    throw CodeMirror.Pass;
                }
                else
                {
                    var doIndent = this.htmlIndent && this.indexOf(this.htmlIndent, lowerTagName) > -1;


                    if (/\/\s*$/.test(tok.string))
                    {
                        cm.replaceSelection(">" + (doIndent ? "\n\n" : ""), doIndent ? {line: pos.line + 1, ch: 0} : {line: pos.line, ch: pos.ch + 1});

                        if (doIndent) {
                            cm.indentLine(pos.line + 1);
                            cm.indentLine(pos.line + 2);
                        }
                        return;
                    }
                    else if (!/\/\s*$/.test(tok.string) && lowerTagName != 'cp:block')
                    {
                        cm.replaceSelection("/>" + (doIndent ? "\n\n" : ""), doIndent ? {line: pos.line + 1, ch: 0} : {line: pos.line, ch: pos.ch + 2});

                        if (doIndent) {
                            cm.indentLine(pos.line + 1);
                            cm.indentLine(pos.line + 2);
                        }
                        return;
                    }

                    if (!/\/\s*$/.test(tok.string) && lowerTagName == 'cp:block')
                    {
                        var doIndent = this.htmlIndent && this.indexOf(this.htmlIndent, lowerTagName) > -1;
                        cm.replaceSelection(">" + (doIndent ? "\n\n" : "") + "</" + tagName + ">",
                                doIndent ? {line: pos.line + 1, ch: 0} : {line: pos.line, ch: pos.ch + 1});
                        if (doIndent) {
                            cm.indentLine(pos.line + 1);
                            cm.indentLine(pos.line + 2);
                        }
                    }



                    return;
                }
            }
            else
            {
                // Don't process the '>' at the end of an end-tag or self-closing tag
                if ((tok.type == "tag" || tok.type == "cptag") && state.type == "closeTag" || /\s*["']\s*>/.test(instring) ||
                        /\/\s*$/.test(tok.string) ||
                        this.selfCloseTags && this.indexOf(this.selfCloseTags, lowerTagName) > -1)
                    throw CodeMirror.Pass;

                var doIndent = this.htmlIndent && this.indexOf(this.htmlIndent, lowerTagName) > -1;
                cm.replaceSelection(">" + (doIndent ? "\n\n" : "") + "</" + tagName + ">",
                        doIndent ? {line: pos.line + 1, ch: 0} : {line: pos.line, ch: pos.ch + 1});
                if (doIndent) {
                    cm.indentLine(pos.line + 1);
                    cm.indentLine(pos.line + 2);
                }
                return;
            }
        }
        else if (ch == "/" && ((tok.type == "tag" || tok.type == "cptag") && /<([^<]*)/.test(tok.string) || (tagName && state.tagName == tagName))) {

            if (tagName && in_array(tagName.toLowerCase(), this.selfCloseTags))
            {
                cm.replaceSelection("/>", "end");
                return;
            }

            if (state.type == "openTag" && !/<\/([^<]*)/.test(tok.string)) {
                cm.replaceSelection(">" + (doIndent ? "\n\n" : "") + "</" + tagName + ">",
                        doIndent ? {line: pos.line + 1, ch: 0} : {line: pos.line, ch: pos.ch + 1});
                if (doIndent) {
                    cm.indentLine(pos.line + 1);
                    cm.indentLine(pos.line + 2);
                }

                return;
            }

            var tagName = state.context && state.context.tagName;
            var lowerTagName = tagName.toLowerCase();

            if (tagName)
                cm.replaceSelection("/" + tagName + ">", "end");

            return;
        }


        throw CodeMirror.Pass;
    };



    this.getEditor = function()
    {
        return $('#' + Win.windowID).data('templateEditor');
    };
    /*
     getEditor: function()
     {
     return this.dcmseditor;
     }, */

    this.resetEditor = function()
    {
        this.currentLine = 1;
        this.currentCol = 0;
        this.markerContainer = $('#eScrollContainer', this.Window);
        this.activeLine = $('#activeLine', this.Window);
        this.activeLineBg = null;
        //this.dcmseditor = null;
        this.lineContainer = null;
        this.jq_lineContainer = null;
        this.editorFrame = null;
        this.jq_editorFrame = null;
        this.editorFrameBody = null;

        this.textarea = null, this.cscc = null;
        window.dcmseditor = null;
        //cscc.editor = null;
    };
    this.makeFocus = function()
    {
        if (this.dcmseditor == null)
            return;
        this.focused = true;
        this.getEditor().focus();
    };
    this.isFocus = function()
    {
        if (this.dcmseditor == null)
            return false;

        return this.focused;
    };
    this.getMarkerContainer = function()
    {
        return this.markerContainer;
    };
    this.getMarker = function()
    {
        return this.activeLine;
    };
    this.bindEvents = function(editor)
    {
        var self = this;
        activeCodeMirror = self.getEditor();
        $('#tagDescriptionDiv').hide();
        var _gutter = self.getEditor().getGutterElement();
        var hlLine = editor.addLineClass(0, "background", "activeline");
        var edit = self.getEditor();

        this.status = {
            line: $('#editor-status .line>span', this.Window),
            col: $('#editor-status .column>span', this.Window),
            codeLength: $('#editor-status .length>span', this.Window)
        };


        $('#' + Win.windowID).find('div.CodeMirror-wrap').addClass(this.mode);

        edit.on("cursorActivity", function() {
            var cur = editor.getLineHandle(editor.getCursor().line);
            if (cur != hlLine) {
                editor.removeLineClass(hlLine, "background", "activeline");
                hlLine = editor.addLineClass(cur, "background", "activeline");
                cscc.editor = self.dcmseditor;
                self.updateStatusBar();
                self.focused = true;
            }
            else
            {
                self.focused = true;
            }
        });

        edit.on("change", function(instance, changeObj) {
            cscc.editor = self.dcmseditor;
            self.validateSyntax();
            self.updateStatusBar();
            self.focused = true;

            if (!self.isDirty)
            {
                if ($(self.Window).data('formConfig') && $(self.Window).data('formConfig').formid)
                {
                    $(self.Window).data('formConfig').setDirty(null, $('#' + $(self.Window).data('formConfig').formid));
                }
            }
        });





        $('.CodeMirror-lines pre span', $('#' + Win.windowID)).unbind('mouseover').bind("mouseover", function(e) {
            cscc.editor = self.dcmseditor;
            if (e.target.className == 'cm-cptag')
            {
                var tag = e.target.innerHTML;
                var tagName = tag.replace(/&lt;\/?cp:([\w]+).*/g, 'cp:$1');
                self.showDescription(e, tagName);
            }
            else
            {
                $('#tagDescriptionDiv').hide();
            }

        });


        edit.on('focus', function() {
            self.focused = true;
            //$(self.Window).data('WindowManager').disableDragging();
        });


        edit.on('blur', function(e) {

            if ($(e.target).parents().children('.CodeMirror-wrap:first').length > 0)
            {
                self.focused = true;
                editor.focus();
                return true;
            }

            self.focused = false;
            if ($('.CodeMirror-completions:visible').length)
            {
                $('.CodeMirror-completions').hide().remove();
            }
            //$(self.Window).data('WindowManager').enableDragging();
            $('#tagDescriptionDiv').hide();
        });


        edit.on("gutterClick", function(thisCM, line, gutter, clickEvent) {
            self.focused = false;
            self.codeFolding(thisCM, line, gutter, clickEvent);

        });


        cscc.editor = edit;
        this.updateStatusBar();
        this.validateSyntax();
    };


    this.setEditorMode = function(mode)
    {
        if (mode != 'text' && mode != 'css' && mode != 'stylesheet' && mode != 'php' && mode != 'javascript' && mode != 'js' && mode != 'xml' && mode != 'html' && mode != 'mysql')
        {
            return;
        }

        var setMode = mode;
        switch (mode)
        {
            case 'css':
            case 'stylesheet':
                setMode = 'css';
                break;
            case 'xml':
            case 'html':
                setMode = 'xml';
                break;
            case 'javascript':
            case 'js':
                setMode = 'javascript';
                break;

        }


        $('#' + Win.windowID).find('div.CodeMirror-wrap').removeClass(this.mode).addClass(setMode);
        this.mode = setMode;

        if (this.mode == 'text')
        {
            return;
        }

        this.getEditor().setOption("mode", this.mode);
        CodeMirror.autoLoadMode(this.getEditor(), this.mode);
    };


    this.codeFolding = function(cm, line, gutter, clickEvent)
    {
        cscc.editor = cm;

        // reset
        cm.setGutterMarker(line, "code-fold-gutter", null);

        var isFoldOff = this.foldFunc_html ? this.foldFunc_html(cm, line) : true;
        if (isFoldOff)
        {
            cm.setGutterMarker(line, "code-fold-gutter", document.createTextNode(''));

            if (this.mode != 'javascript' && this.foldFunc_brace)
            {
                isFoldOff = this.foldFunc_brace(cm, line);

                if (!isFoldOff)
                {
                    var spanFold = document.createElement("span")
                    spanFold.className = 'fold';
                    cm.setGutterMarker(line, "code-fold-gutter", spanFold);
                }
            }

        }
        else
        {
            var spanFold = document.createElement("span")
            spanFold.className = 'fold';
            cm.setGutterMarker(line, "code-fold-gutter", spanFold);
        }

        gutter = cm.getGutterElement();
        var linesobject = $(gutter).parent().parent().find('.CodeMirror-lines');
        if (linesobject.find('.error:first').length > 0)
        {
            $(gutter).addClass('CodeMirror-linenumbers-errors');
            $(gutter).prev().prev().addClass('CodeMirror-linenumbers-errors');
        }
        else
        {
            $(gutter).removeClass('CodeMirror-linenumbers-errors');
            $(gutter).prev().prev().removeClass('CodeMirror-linenumbers-errors');
        }
    };




    this.runAfterLoaded = function(editor, textarea)
    {
        var self = this;
        this.Window.data('templateEditor', editor);
        this.resetEditor();
        this.dcmseditor = editor;
        this.dcmseditor.focus();
        this.editor = this.dcmseditor;
        this.textarea = textarea;
        //this.Window = $('#'+ Win.windowID);
        this.cscc = cscc;
        this.status =
                {
                    line: $('#editor-status .line>span', $('#' + Win.windowID)),
                    col: $('#editor-status .column>span', $('#' + Win.windowID)),
                    codeLength: $('#editor-status .length>span', $('#' + Win.windowID))
                };
        this.c =
                {
                    lines: 0,
                    'line': 0,
                    'column': 0
                };
        this.markerContainer = $('#markerContainer', $('#' + Win.windowID));
        this.activeLine = $('#activeLine', $('#' + Win.windowID));
        this.markerContainer.css({
            height: '100%'
        });
        var csccOptions = this.cscc.init(this.textarea, this.config, this.dcmseditor);
        this.bindEvents(this.dcmseditor);
        this.dcmseditor = editor;
        this.cscc.editor = this.dcmseditor;
        $(this.activeLine).css(
                {
                    'position': 'absolute'
                });
        $(this.markerContainer).css(
                {
                    height: "500px",
                    'font-size': '12px',
                    'font-family': "Monospace, Arial",
                    'line-height': '14px!important'
                });
        $(this.jq_lineContainer).css(
                {
                    'font-size': '12px',
                    'font-family': "Monospace, Arial",
                    'line-height': '14px!important',
                    'height': '100%'

                });
        $('#editContainer', self.Window).unmask();



        return this.dcmseditor;

    };
    this.showDescription = function(e, tagname)
    {
        if (typeof top.dcms_tags[tagname] == "undefined")
        {
            $('#tagDescriptionDiv').hide();
            return true;
        }

        var patch = $('.CodeMirror-sizer', $('#' + Win.windowID)).get(0).scrollTop;
        var x = (e.pageX ? e.pageX : window.event.x); // + $('#tagDescriptionDiv').get(0).scrollLeft;
        var y = (e.pageY ? e.pageY : window.event.y); // + $('#tagDescriptionDiv').get(0).scrollTop;


        $('#tagDescriptionDiv', $('body')).css({
            position: 'absolute',
            zIndex: 999999,
            left: x,
            top: y + 15 + patch
        }).empty().append(top.dcms_tags[tagname].desc).show();
    };
    this.initCallback = function()
    {
        return;
        var self = this;
        $(window).bind('resize', function()
        {
//updateEditorSize();
        });
        setTimeout(function() {
            self.updateErrorLines();
            self.validateSyntax();
            self.updateStatusBar();
            self.updateMarkerBar();
        }, 800);
    };
    this.updateStatusBar = function()
    {

        var self = this;
        this.cscc.editor = this.dcmseditor;
        var gutter = this.dcmseditor.getGutterElement();
        var linesobject = $(gutter).parent().parent().find('.CodeMirror-lines');
        if (linesobject.find('.error:first').length > 0)
        {
//
            $(gutter).addClass('CodeMirror-linenumbers-errors');
            $(gutter).prev().prev().addClass('CodeMirror-linenumbers-errors');
        }
        else
        {
            $(gutter).removeClass('CodeMirror-linenumbers-errors');
            $(gutter).prev().prev().removeClass('CodeMirror-linenumbers-errors');
        }


        $('.CodeMirror-lines pre span', $('#' + Win.windowID)).unbind('mouseover').bind("mouseover", function(e) {

            if (e.target.className == 'cm-cptag')
            {
/// 
                var tag = e.target.innerHTML;
                var tagName = tag.replace(/&lt;\/?cp:([\w]+).*/g, 'cp:$1');
                self.showDescription(e, tagName);
            }
            else
            {
                $('#tagDescriptionDiv').hide();
            }

        });
        // Statusbar Function
        this.currentLine = this.dcmseditor.getCursor().line; //this.dcmseditor.currentLine();
        this.currentCol = this.dcmseditor.getCursor().ch; //this.dcmseditor.cursorPosition().character;


        if ($('.CodeMirror-completions:visible').length)
        {
            $('.CodeMirror-completions:visible').hide().remove();
        }
        if (this.c.line != this.currentLine)
        {
            this.cscc.hide();
        }

        this.c.line = this.currentLine;
        this.c.column = this.currentCol;
        this.c.lines = this.dcmseditor.lineCount();
        this.status.line.text(this.currentLine);
        this.status.col.text(this.currentCol);
        this.status.codeLength.text(this.dcmseditor.getValue().length);
    };
    this.getCurrentLine = function()
    {
        return this.c;
    };
    this.setWrapping = function(wrap)
    {
        $('#editContainer', this.Window).mask('Please wait...');
        var interval, self = this, ok = false;
        if (wrap)
        {
            ok = this.dcmseditor.wrappingChanged(this.dcmseditor);
        }
        else
        {
            ok = this.dcmseditor.wrappingChanged(this.dcmseditor);
        }


        interval = setInterval(function() {
            if (ok)
            {
                clearInterval(interval);
                $('#editContainer', self.Window).unmask('Please wait...');
            }
        }, 50);
        return false;
    };
    this.onChange = function()
    {
        if (!this.dcmseditor.change)
        {
            this.dcmseditor.change = true;
        }
        var gutter = this.dcmseditor.getGutterElement();
        var linesobject = $(gutter).parent().parent().find('.CodeMirror-lines');
        if (linesobject.find('.error:first').length > 0)
        {
//
            $(gutter).addClass('CodeMirror-linenumbers-errors');
            $(gutter).prev().prev().addClass('CodeMirror-linenumbers-errors');
        }
        else
        {
            $(gutter).removeClass('CodeMirror-linenumbers-errors');
            $(gutter).prev().prev().removeClass('CodeMirror-linenumbers-errors');
        }

        this.textarea.value = this.dcmseditor.getValue();
        this.textarea.change();
        var self = this, a = this.dcmseditor.historySize();
        $('.CodeMirror-lines pre span', $('#' + Win.windowID)).unbind('mouseover').bind("mouseover", function(e) {

            if (e.target.className == 'cm-cptag')
            {
/// 
                var tag = e.target.innerHTML;
                var tagName = tag.replace(/&lt;\/?cp:([\w]+).*/g, 'cp:$1');
                self.showDescription(e, tagName);
            }
            else
            {
                $('#tagDescriptionDiv').hide();
            }

        });

        if (this.dcmseditor.hasErrors())
        {
        //    console.log('HTML Errors');
        }

        if (a.redo)
            $('.redo', $('#' + Win.windowID)).removeClass('disabled');
        if (!a.redo)
            $('.redo', $('#' + Win.windowID)).addClass('disabled');
        if (!a.undo)
            $('.undo', $('#' + Win.windowID)).addClass('disabled');
        if (a.undo)
            $('.undo', $('#' + Win.windowID)).removeClass('disabled');

        this.updateStatusBar();
        // this.validateSyntax();

    };
    // Update the Line Bar and Markers
    // Call from codemirror.js
    // after all Lines processed

    this.selectToken = function(span, token)
    {
        if (token.style == "xml-tagname")
        {
            alert(String(token.content));
        }
        else
        {
            alert(String(token.content));
        }
    };
    this.cursorActivity = function(el)
    { //this is our hook for focusing on the right item inside the tag-generator 
        try
        {
            this.updateStatusBar();
            this.updateErrorLines();
            if (el === null || el.className == undefined)
                return;
            while (!el.className.match(/cp-tagname/))
            {

                if (el.innerHTML == "&gt;" || el.innerHTML == "&lt;" || el.innerHTML == "/&gt;")
                    return;
                el = el.previousSibling;
            }
            var currentTag = el.innerHTML.substring(3).replace(/\s/, "");
            for (var i = 0; i < document.getElementById("TagGroupSelect").options.length; i++)
            {
                if (document.getElementById("TagGroupSelect").options[i].value == "alltags")
                {
                    document.getElementById("TagGroupSelect").options[i].selected = "selected";
                    selectTagGroup("alltags");
                    for (var j = 0; i < document.getElementById("tagSelection").options.length; j++)
                    {
                        if (document.getElementById("tagSelection").options[j].value == currentTag)
                        {
                            document.getElementById("tagSelection").options[j].selected = "selected";
                            break;
                        }
                    }
                    break;
                }
            }
        }
        catch (e)
        {
        }
    };
    // Set Error Icons
    this.validateSyntax = function()
    {
        var self = this;
        return;
        if (typeof self.dcmseditor == 'undefined')
        {
        //    console.log('indefined dcmseditor ');
        }

        if (self.dcmseditor.hasErrors())
        {
         //   console.log('HTML Errors');
        }


        this.markerContainer = $('#markerContainer', $('#' + Win.windowID));
        var info, span, x = 0, markerlength = $(this.markerContainer).find('div').length, codeLines = this.c.lines;
        if (markerlength == codeLines)
        {
            $(this.markerContainer).css('height', $('.CodeMirror-wrap', $('#' + Win.windowID)).height());
            for (x = 0; x < codeLines; x++)
            {
                span = $(this.markerContainer).find('div:eq(' + x + ')');
                span.attr('x', x);
                info = self.dcmseditor.lineInfo(x);
                if (typeof info != 'undefined' && typeof info.text != 'undefined')
                {
                    if (info.text != '' && info.text.match(/<cp:/))
                    {
                        span.addClass('cp-tag').unbind('click'); /*.click(function(){
                         
                         $(this).parent().find('.markerpoint').removeClass('markerpoint');
                         $(this).addClass('markerpoint');
                         
                         
                         
                         self.dcmseditor.focus();
                         });*/
                    }
                }
                else
                {
                    span.removeClass('cp-tag'); //.unbind('click');
                }
            }
            return;
        }

        x = parseInt(markerlength) ? parseInt(markerlength) : 0;
        var spanHeight = $(this.markerContainer).outerHeight() / codeLines;
        $(this.markerContainer).css('height', $('.CodeMirror-wrap', $('#' + Win.windowID)).height()); //.empty();
        for (; x < codeLines; x++)
        {

            span = $('<div>').attr('x', x).css({
                height: spanHeight,
                maxHeight: spanHeight
            });
            info = self.dcmseditor.lineInfo(x);
            if (typeof info != 'undefined' && typeof info.text != 'undefined')
            {
                if (info.text != '' && info.text.match(/<cp:/))
                {
                    span.addClass('cp-tag'); /*.click(function(){
                     
                     
                     $(this).parent().find('.markerpoint').removeClass('markerpoint');
                     $(this).addClass('markerpoint');
                     self.dcmseditor.setCursor( $(this).attr('x')+1,0);
                     
                     
                     self.dcmseditor.focus();
                     });*/


                }
                $(this.markerContainer).append(span);
            }
        }
        $(this.markerContainer).find('div').css({
            height: spanHeight,
            maxHeight: spanHeight
        });
        return;
        var errorsFound = false;
        gutterElements.each(function() {
            var gutter = $(this);
            var pre = $(this).parent().parent().html();
            if (pre.match(/cm-error/))
            {
                gutter.addClass('error').attr('title', 'prev nodes contains a unclosed Tag');
                errorsFound = true;
            }
            else
            {
                gutter.removeClass('error').removeAttr('title');
            }
        });
        if (!errorsFound)
        {
            return;
        }

        gutterElements.each(function() {
            $(this).parent().addClass('error-color');
        });
        return;
        $(lineNumbers).parent().addClass('linesbg-color');
        var error = false;
        var _str = self.editorFrameBody.innerHTML;
        var _str1 = _str.split('<br');
        var max = _str1.length
        var xcount = 0;
        var regex = /error/;
        var stop = false;
        var i = 0;
        while (!stop)
        {

            if (_str1[i] && regex.test(_str1[i]))
            {
                $(lineNumbers).find('pre:eq(' + i + ')').addClass('error').attr('title', 'prev nodes contains a unclosed Tag');
                error = true;
            }
            else
            {
                $(lineNumbers).find('pre:eq(' + i + ')').removeClass('error').removeAttr('title');
            }
            if (max == i)
            {
                stop = true;
                break;
            }
            i++;
        }

        self.updateMarkers();
        if (error)
        {
            $(lineNumbers).parent().addClass('error-color');
            $(lineNumbers).addClass('error-color');
        }
        else
        {
            $(lineNumbers).find('.error').removeClass('error').removeAttr('title');
            $(lineNumbers).parent().removeClass('error-color');
            $(lineNumbers).removeClass('error-color');
        }

        _str = _str1 = null;
        $('#editContainer', this.Window).unmask('');
        //},200);
    };
    this.updateLines = function(_str)
    {
        return;
        var self = this;
        var lineNumbers = this.jq_lineContainer;
        var _str1 = _str.split('<br>');
        var max = _str1.length
        var xcount = 0;
        var spacerlines = 0;
        var stop = false;
        var i = 0;
        var regex = /\scp-tagname/;
        //while (!stop)
        //{
        for (var yy = 0; yy < _str1.length; yy++)
        {
            var mark = this.markerContainer.find('pre:eq(' + i + ')');
            if (regex.test(_str1[yy]))
            {
                mark.removeAttr('style').removeClass('s').attr('x', i + 1).attr('style', 'height:' + 1 + 'px!important;').addClass('cp-tag').unbind('click').bind('click', function()
                {
                    $('.markerpoint', this.markerContainer).removeClass('markerpoint');
                    $(this).addClass('markerpoint');
                    var x = $(this).attr('x') - 1;
                    self.dcmseditor.firstLine();
                    self.dcmseditor.jumpToLine(Number(x + 1));
                    self.setActiveLine(x + 1);
                });
                xcount++;
            }
            else
            {
                spacerlines++;
                mark.attr('x', i + 1).removeAttr('style').addClass('s').removeClass('cp-tag').unbind('click');
            }

            if (max == i)
            {
                stop = true;
                break;
            }
            i++;
        }

        _str1 = null;
        this.calculateMarkerSize(max, xcount, spacerlines);
    };
    /**
     * 
     */
    this.calculateMarkerSize = function(codelines, dcmstaglines, spacerlines)
    {
// marker height = 2 px
        var markerHeight = 1;
        var lineheight = 1;
        var height = parseInt(this.markerContainer.innerHeight()) - 5;
        var allmarkers = dcmstaglines * markerHeight;
        // höhe aller makers abziehen
        var _newheight = height - allmarkers;
        // anzahl spacer
        var spaces = codelines + 1 - dcmstaglines;
        var spacerHeight = (_newheight / spacerlines);
        $($('.s', this.markerContainer), this.Window).attr('style', 'height:' + spacerHeight + 'px!important;');
    };

}
;
function getEditorInstanceID(windowID)
{
    if (!windowID)
        return false;

    var $windowObj = $('#' + windowID);
    if ($windowObj.find('.sourceEdit').length == 0)
    {
        return false;
    }

    return $windowObj.data('cmInstanceID');
}

function getEditorInstance(windowID)
{
    if (!windowID)
        return false;

    var $windowObj = $('#' + windowID);
    if ($windowObj.find('.sourceEdit').length == 0)
    {
        return false;
    }

    return $windowObj.data('templateEditor');
}

function destroyTemplateEditor(windowID)
{
    var instanceID;
    if (!(instanceID = getEditorInstanceID(windowID)))
        return;

    delete __cmirrors[instanceID];
    activeCodeMirror = null;
}


function setActiveCodemirror(windowID)
{
    var editor;
    if (!(editor = getEditorInstance(windowID)))
    {
        return;
    }
    activeCodeMirror = editor;
    editor.focus();
    editor.refresh();

}


function createTemplateEditor(windowID)
{
    if (!windowID)
        return;

    var $windowObj = $('#' + windowID);
    if ($windowObj.find('.sourceEdit').length == 0)
    {
        return;
    }

    Win.redrawWindowHeight(windowID, true);

    if (typeof $windowObj.data('templateEditor') != 'undefined')
    {
        refreshTemplateEditor(windowID, $windowObj);
        return false;
    }

    $windowObj.find('#editContainer').mask('Please wait...');
    $windowObj.find('textarea.sourceEdit').each(function(i, el) {

        if (!$(this).data('templateEditor'))
        {
            var sourceEdit = new _dcmsSourceEditor();
            var ed = sourceEdit.init(windowID, el);

            if (typeof __cmirrors[el.id] == 'undefined')
            {
                __cmirrors[el.id] = ed;
            }
            var formID = $(this).parents('form:first').attr('id');

            ed.setSize(($(this).parents('.window-body-content').width() - 60) + 'px');

            $windowObj.data('templateEditor', ed).data('cmCfg', sourceEdit.config).data('templateEditorFormID', formID).data('sourceCodeEditor', sourceEdit);
            $windowObj.data('cmInstanceID', el.id);



            //$(this).data('cm', ed).data('cmCfg', _dcmsSourceEditor.config);

            /*
             windowObj.data('WindowManager').set('onResize', function(e, d, wm, contentsize) {
             var wizardH = $(wm.$el).find('#wizard').outerHeight(true);
             var instancceID = $(wm.$el).data('cmInstanceID');
             $('#' + instancceID).parents('.sourceEditor:first').find('div:first').css({height: contentsize.height - wizardH - 20});
             $(wm.$el).data('templateEditor').setSize('100%', contentsize.height - wizardH - 20);
             });
             */

            $windowObj.data('WindowManager').set('onBeforeClose', function(e, wm, callback) {
                $('#cmc-suggestions,#cmc-suggestions-description,#tagDescriptionDiv').hide();

                if (callback)
                {
                    callback();
                }
            });

            $windowObj.data('WindowManager').set('onResize', function(e, d, wm, contentsize) {
                var wizardH = $(wm.$el).find('#wizard').outerHeight(true);
                var instancceID = $(wm.$el).data('cmInstanceID');
                var maxWidth = $('#' + instancceID).parents('.window-body-content').width() - 60;

                $('#' + instancceID).parents('.sourceEditor:first').find('div:first').css({width: maxWidth, height: contentsize.height - wizardH - 20});
                $(wm.$el).data('templateEditor').setSize(maxWidth + 'px', contentsize.height - wizardH - 20);
                $(wm.$el).data('templateEditor').focus();
                $(wm.$el).data('templateEditor').refresh();
            });

            $windowObj.data('WindowManager').set('onResizeStop', function(e, d, wm, contentsize) {
                var wizardH = $(wm.$el).find('#wizard').outerHeight(true);
                var instancceID = $(wm.$el).data('cmInstanceID');


                var maxWidth = $('#' + instancceID).parents('.window-body-content').width() - 60;

                $('#' + instancceID).parents('.sourceEditor:first').find('div:first').css({width: maxWidth, height: contentsize.height - wizardH - 20});
                $(wm.$el).data('templateEditor').setSize(maxWidth + 'px', contentsize.height - wizardH - 20);
                $(wm.$el).data('templateEditor').focus();
                $(wm.$el).data('templateEditor').refresh();
            });





        }
    });

    setupTagGroups();

    $windowObj.find('#editContainer').unmask();
}

function refreshTemplateEditor(windowID, windowObj)
{
    var cm = windowObj.data('templateEditor');
    var sourceCodeEditor = windowObj.data('sourceCodeEditor');
    var cmInstanceID = windowObj.data('cmInstanceID');
    activeCodeMirror = cm;
    cscc.init(windowObj.find('#' + cmInstanceID).get(0), windowObj.data('cmCfg'), cm);
    cm.focus();
    cm.refresh();

    return;
}









function selectTagGroup(groupname)
{

    if (groupname == "snippet_custom")
    {
        document.getElementById('codesnippet_standard').style.display = 'none';
        document.getElementById('tagSelection').style.display = 'none';
        document.getElementById('codesnippet_custom').style.display = 'block';
    }
    else if (groupname == "snippet_standard")
    {
        document.getElementById('codesnippet_custom').style.display = 'none';
        document.getElementById('tagSelection').style.display = 'none';
        document.getElementById('codesnippet_standard').style.display = 'block';
    }
    else if (groupname != "-1")
    {
        document.getElementById('codesnippet_custom').style.display = 'none';
        document.getElementById('codesnippet_standard').style.display = 'none';
        document.getElementById('tagSelection').style.display = 'block';
        elem = document.getElementById("tagSelection");
        for (var i = (elem.options.length - 1); i >= 0; i--)
        {
            elem.options[i] = null;
        }

        for (var i = 0; i < tagGroups[groupname].length; i++)
        {
            elem.options[i] = new Option(tagGroups[groupname][i], tagGroups[groupname][i]);
        }
    }
}

function getScrollPosTop()
{
    var elem = document.getElementById("source");
    if (elem)
    {
        return elem.scrollTop;
    }
    return 0;
}

function getScrollPosLeft()
{
    var elem = document.getElementById("source");
    if (elem)
    {
        return elem.scrollLeft;
    }
    return 0;
}

function scrollToPosition()
{
    var elem = document.getElementById("source");
    if (elem)
    {
        elem.scrollTop = parent.editorScrollPosTop;
        elem.scrollLeft = parent.editorScrollPosLeft;
    }
}

function wedoKeyDown(ta, keycode)
{

    if (keycode == 9)
    { // TAB
        if (ta.setSelectionRange)
        {
            var selectionStart = ta.selectionStart;
            var selectionEnd = ta.selectionEnd;
            ta.value = ta.value.substring(0, selectionStart) + "\t" + ta.value.substring(selectionEnd);
            ta.focus();
            ta.setSelectionRange(selectionEnd + 1, selectionEnd + 1);
            ta.focus();
            return false;
        }
        else if (document.selection)
        {
            var selection = document.selection;
            var range = selection.createRange();
            range.text = "\t";
            return false;
        }
    }

    return true;
}




function insertWords(html)
{
    var sourceCodeEditor = $('#' + Win.windowID).data('sourceCodeEditor');
    sourceCodeEditor.getEditor().focus();
    sourceCodeEditor.getEditor().replaceSelection(html);
}




function toggleSpecialChars(obj)
{
    if ($('#htmlchars', $('#' + Win.windowID)).find('span:first').length > 0)
    {
        if (obj.is(':visible'))
        {
            obj.hide();
        }
        else
        {
            obj.show();
        }
        return;
    }

    var Entities =
            {
// Latin-1 Entities
                ' ': 'nbsp',
                '¡': 'iexcl',
                '¢': 'cent',
                '£': 'pound',
                '¤': 'curren',
                '¥': 'yen',
                '¦': 'brvbar',
                '§': 'sect',
                '¨': 'uml',
                '©': 'copy',
                'ª': 'ordf',
                '«': 'laquo',
                '¬': 'not',
                '­': 'shy',
                '®': 'reg',
                '¯': 'macr',
                '°': 'deg',
                '±': 'plusmn',
                '²': 'sup2',
                '³': 'sup3',
                '´': 'acute',
                'µ': 'micro',
                '¶': 'para',
                '·': 'middot',
                '¸': 'cedil',
                '¹': 'sup1',
                'º': 'ordm',
                '»': 'raquo',
                '¼': 'frac14',
                '½': 'frac12',
                '¾': 'frac34',
                '¿': 'iquest',
                '×': 'times',
                '÷': 'divide',
                // Symbols and Greek Letters

                'ƒ': 'fnof',
                '•': 'bull',
                '…': 'hellip',
                "'": 'prime',
                '?': 'Prime',
                '?': 'oline',
                        '/': 'frasl',
                'P': 'weierp',
                'I': 'image',
                'R': 'real',
                '™': 'trade',
                '?': 'alefsym',
                        '?': 'larr',
                        '?': 'uarr',
                        '?': 'rarr',
                        '?': 'darr',
                        '?': 'harr',
                        '?': 'crarr',
                        '?': 'lArr',
                        '?': 'uArr',
                        '?': 'rArr',
                        '?': 'dArr',
                        '?': 'hArr',
                        '?': 'forall',
                        '?': 'part',
                        '?': 'exist',
                        'Ø': 'empty',
                '?': 'nabla',
                        '?': 'isin',
                        '?': 'notin',
                        '?': 'ni',
                        '?': 'prod',
                        '?': 'sum',
                        '-': 'minus',
                '*': 'lowast',
                'v': 'radic',
                '?': 'prop',
                        '8': 'infin',
                '?': 'ang',
                        '?': 'and',
                        '?': 'or',
                        'n': 'cap',
                '?': 'cup',
                        '?': 'int',
                        '?': 'there4',
                        '~': 'sim',
                '?': 'cong',
                        '˜': 'asymp',
                '?': 'ne',
                        '=': 'equiv',
                '=': 'le',
                        '=': 'ge',
                        '?': 'sub',
                        '?': 'sup',
                        '?': 'nsub',
                        '?': 'sube',
                        '?': 'supe',
                        '?': 'oplus',
                        '?': 'otimes',
                        '?': 'perp',
                        '·': 'sdot',
                        '?': 'loz',
                        '?': 'spades',
                        '?': 'clubs',
                        '?': 'hearts',
                        '?': 'diams',
                        // Other Special Characters

                        '"': 'quot',
                '&': 'amp',
                // This entity is automatically handled by the XHTML parser.
                '<': 'lt',
                // This entity is automatically handled by the XHTML parser.
                '>': 'gt',
                // This entity is automatically handled by the XHTML parser.
                'ˆ': 'circ',
                '˜': 'tilde',
                        ' ': 'ensp',
                        ' ': 'emsp',
                        '?': 'thinsp',
                        '?': 'zwnj',
                        '?': 'zwj',
                        '?': 'lrm',
                        '?': 'rlm',
                        '–': 'ndash',
                '—': 'mdash',
                '‘': 'lsquo',
                '’': 'rsquo',
                '‚': 'sbquo',
                '“': 'ldquo',
                '”': 'rdquo',
                '„': 'bdquo',
                '†': 'dagger',
                '‡': 'Dagger',
                '‰': 'permil',
                '‹': 'lsaquo',
                '›': 'rsaquo',
                '€': 'euro'
            };
    for (var e in Entities)
    {
        var charDiv = $('<span>');
        charDiv.html('&' + Entities[e] + ';');
        charDiv.bind('click', function()
        {

            var html = $(this).html();
            html = html.replace(/^&/g, '&amp;');
            insertWords(html);
            $(obj).slideUp();
        });
        charDiv.mouseover(function()
        {
            var html = $(this).html();
            //html = htmlo.replace(/^&/g, '&amp;');
            $('#charTooltip', $('#' + Win.windowID)).html(html);
            pos = $(this).position();
            $('#charTooltip', $('#' + Win.windowID)).css(
                    {
                        left: pos.left - 5,
                        top: pos.top + 20
                    });
            $('#charTooltip', $('#' + Win.windowID)).show();
        });
        charDiv.mouseout(function()
        {
            $('#charTooltip', $('#' + Win.windowID)).hide();
        });
        $('#htmlchars', $('#' + Win.windowID)).append(charDiv);
    }
    var LatinEntities =
            {
                'À': 'Agrave',
                'Á': 'Aacute',
                'Â': 'Acirc',
                'Ã': 'Atilde',
                'Ä': 'Auml',
                'Å': 'Aring',
                'Æ': 'AElig',
                'Ç': 'Ccedil',
                'È': 'Egrave',
                'É': 'Eacute',
                'Ê': 'Ecirc',
                'Ë': 'Euml',
                'Ì': 'Igrave',
                'Í': 'Iacute',
                'Î': 'Icirc',
                'Ï': 'Iuml',
                'Ð': 'ETH',
                'Ñ': 'Ntilde',
                'Ò': 'Ograve',
                'Ó': 'Oacute',
                'Ô': 'Ocirc',
                'Õ': 'Otilde',
                'Ö': 'Ouml',
                'Ø': 'Oslash',
                'Ù': 'Ugrave',
                'Ú': 'Uacute',
                'Û': 'Ucirc',
                'Ü': 'Uuml',
                'Ý': 'Yacute',
                'Þ': 'THORN',
                'ß': 'szlig',
                'à': 'agrave',
                'á': 'aacute',
                'â': 'acirc',
                'ã': 'atilde',
                'ä': 'auml',
                'å': 'aring',
                'æ': 'aelig',
                'ç': 'ccedil',
                'è': 'egrave',
                'é': 'eacute',
                'ê': 'ecirc',
                'ë': 'euml',
                'ì': 'igrave',
                'í': 'iacute',
                'î': 'icirc',
                'ï': 'iuml',
                'ð': 'eth',
                'ñ': 'ntilde',
                'ò': 'ograve',
                'ó': 'oacute',
                'ô': 'ocirc',
                'õ': 'otilde',
                'ö': 'ouml',
                'ø': 'oslash',
                'ù': 'ugrave',
                'ú': 'uacute',
                'û': 'ucirc',
                'ü': 'uuml',
                'ý': 'yacute',
                'þ': 'thorn',
                'ÿ': 'yuml',
                'Œ': 'OElig',
                'œ': 'oelig',
                'Š': 'Scaron',
                'š': 'scaron',
                'Ÿ': 'Yuml'
            };
    for (var e in LatinEntities)
    {
        var charDiv = $('<span>');
        charDiv.html('&' + LatinEntities[e] + ';');
        charDiv.bind('click', function()
        {
            var html = $(this).html();
            //html = html.replace(/^&/g, '&amp;');
            insertWords(html);
            $(obj).slideUp();
        });
        charDiv.mouseover(function()
        {
            var htmlo = $(this).html();
            html = htmlo.replace(/^&/g, '&amp;');
            $('#charTooltip', $('#' + Win.windowID)).html(html);
            pos = $(this).position();
            $('#charTooltip', $('#' + Win.windowID)).css(
                    {
                        left: pos.left - 5,
                        top: pos.top + 20
                    });
            $('#charTooltip', $('#' + Win.windowID)).show();
        });
        charDiv.mouseout(function()
        {
            $('#charTooltip', $('#' + Win.windowID)).hide();
        });
        $('#htmlchars', $('#' + Win.windowID)).append(charDiv);
    }

    var GreekEntities =
            {
                '?': 'Alpha',
                '?': 'Beta',
                        'G': 'Gamma',
                '?': 'Delta',
                        '?': 'Epsilon',
                        '?': 'Zeta',
                        '?': 'Eta',
                        'T': 'Theta',
                '?': 'Iota',
                        '?': 'Kappa',
                        '?': 'Lambda',
                        '?': 'Mu',
                        '?': 'Nu',
                        '?': 'Xi',
                        '?': 'Omicron',
                        '?': 'Pi',
                        '?': 'Rho',
                        'S': 'Sigma',
                '?': 'Tau',
                        '?': 'Upsilon',
                        'F': 'Phi',
                '?': 'Chi',
                        '?': 'Psi',
                        'O': 'Omega',
                'a': 'alpha',
                'ß': 'beta',
                '?': 'gamma',
                        'd': 'delta',
                'e': 'epsilon',
                '?': 'zeta',
                        '?': 'eta',
                        '?': 'theta',
                        '?': 'iota',
                        '?': 'kappa',
                        '?': 'lambda',
                        'µ': 'mu',
                '?': 'nu',
                        '?': 'xi',
                        '?': 'omicron',
                        'p': 'pi',
                '?': 'rho',
                        '?': 'sigmaf',
                        's': 'sigma',
                't': 'tau',
                '?': 'upsilon',
                        'f': 'phi',
                '?': 'chi',
                        '?': 'psi',
                        '?': 'omega'
            };
    for (var e in GreekEntities)
    {
        var charDiv = $('<span>');
        charDiv.html('&' + GreekEntities[e] + ';');
        charDiv.bind('click', function()
        {
            var html = $(this).html();
            html = html.replace(/^&/g, '&amp;');
            insertWords(html);
            $(obj).slideUp();
        });
        charDiv.mouseover(function()
        {
            var html = $(this).html();
            //html = htmlo.replace(/^&/g, '&amp;');
            $('#charTooltip', $('#' + Win.windowID)).html(html);
            pos = $(this).position();
            $('#charTooltip', $('#' + Win.windowID)).css(
                    {
                        left: pos.left - 5,
                        top: pos.top + 20
                    });
            $('#charTooltip', $('#' + Win.windowID)).show();
        });
        charDiv.mouseout(function()
        {
            $('#charTooltip', $('#' + Win.windowID)).hide();
        });
        $('#htmlchars', $('#' + Win.windowID)).append(charDiv);
    }

    $(obj).show();
}


function insTag(wath)
{
    switch (wath)
    {
        case 'a':
            replaceSelection('<a href="">', '</a>');
            break;
        case 'img':
            replaceSelection('<img src="" width="" height="" title="" alt="" border="0"/>', '');
            break;
        case 'strong':
            replaceSelection('<strong>', '</strong>');
            break;
        case 'italic':
            replaceSelection('<em>', '</em>');
            break;
        case 'underline':
            replaceSelection('<u>', '</u>');
            break;
        case 'h1':
            replaceSelection('<h1>', '</h1>');
            break;
        case 'h2':
            replaceSelection('<h2>', '</h2>');
            break;
        case 'h3':
            replaceSelection('<h3>', '</h3>');
            break;
        case 'h4':
            replaceSelection('<h4>', '</h4>');
            break;
        case 'h5':
            replaceSelection('<h5>', '</h5>');
            break;
        case 'h6':
            replaceSelection('<h6>', '</h6>');
            break;
    }

}





function replaceSelection(tag, endtag, nowrap)
{
    var sourceCodeEditor = $('#' + Win.windowID).data('sourceCodeEditor');
    var edit = sourceCodeEditor.getEditor();

    edit.focus();
    var sel = edit.getSelection();
    var indents = edit.getCursor(true);
    var indent = indents.ch;
    if (sel)
    {

        if (nowrap != true)
        {
            var str = makeWhiteSpace(indent * edit.options.indentUnit) + tag + (nowrap !== true ? "\r\n" : '');
            str += sel;
            str += (endtag !== undefined ? "\r\n" + makeWhiteSpace(indent * activeCodeMirror.options.indentUnit) + endtag : '');
        }
        else
        {
            var str = tag;
            str += sel;
            str += (endtag !== undefined ? endtag : '');
        }

        edit.replaceSelection(str);
        edit.focus();
        autoFormatSelection();
    }
    else
    {

        if (nowrap != true)
        {
            edit.replaceSelection(tag + (endtag != undefined ? "\r\n" + makeWhiteSpace(indent + 1 * edit.options.indentUnit) + "\r\n" + endtag : ''));
        }
        else
        {
            edit.replaceSelection(tag + (endtag != undefined ? endtag : ''));
        }

        edit.focus();
        autoFormatSelection();
    }


}

function getSelectedRange() {
    var sourceCodeEditor = $('#' + Win.windowID).data('sourceCodeEditor');
    var edit = sourceCodeEditor.getEditor();
    return {
        from: edit.getCursor(true),
        to: edit.getCursor(false)
    };
}

function autoFormatSelection() {
    var sourceCodeEditor = $('#' + Win.windowID).data('sourceCodeEditor');
    var edit = sourceCodeEditor.getEditor();
    var range = getSelectedRange();
    edit.autoFormatRange(range.from, range.to);
}

function makeWhiteSpace(n)
{
    var buffer = [],
            nb = true;
    for (; n > 0; n--)
    {
        buffer.push((nb || n == 1) ? "\u00a0" : " ");
        nb = !nb;
    }
    return buffer.join("");
}





// 
var doupdateEditorSize = false;
function updateEditorSize()
{
    if (doupdateEditorSize)
    {
        return;
    }

    doupdateEditorSize = true;
    wizardOpen = $('#wizard', $('#' + Win.windowID)).is(':visible') ? true : false;
    if (!isFullscreen)
    {
        var h = 500;
        if (!wizardOpen)
        {
            $('.CodeMirror-wrapping iframe:first', $('#' + Win.windowID)).css(
                    {
                        height: h
                    }).css(
                    {
                        height: h
                    });
            $('#eScrollContainer', $('#' + Win.windowID)).get(0).style.height = h;
            $('#dcmsDomTree', $('#' + Win.windowID)).css(
                    {
                        maxHeight: h
                    });
        }
        else
        {
            $('.CodeMirror-wrapping iframe:first', $('#' + Win.windowID)).css(
                    {
                        height: h - wizardHeight.open
                    }).next().css(
                    {
                        height: h - wizardHeight.open
                    });
            $('#eScrollContainer', $('#' + Win.windowID)).get(0).style.height = h - wizardHeight.open;
            $('#dcmsDomTree', $('#' + Win.windowID)).css(
                    {
                        maxHeight: h - wizardHeight.open
                    });
        }


    }
    else
    {
        var h = $(window).height() - $('#editorfooter', $('#' + Win.windowID)).height() - 35;
        if (!wizardOpen)
        {
            $('.CodeMirror-wrapping iframe:first', $('#' + Win.windowID)).css(
                    {
                        height: h
                    }).next().css(
                    {
                        height: h
                    });
            $('#eScrollContainer', $('#' + Win.windowID)).get(0).style.height = h;
            $('#dcmsDomTree', $('#' + Win.windowID)).css(
                    {
                        maxHeight: h
                    });
        }
        else
        {
            $('.CodeMirror-wrapping iframe:first', $('#' + Win.windowID)).css(
                    {
                        height: h - wizardHeight.open
                    }).next().css(
                    {
                        height: h - wizardHeight.open
                    });
            $('#eScrollContainer', $('#' + Win.windowID)).get(0).style.height = h - wizardHeight.open;
            $('#dcmsDomTree', $('#' + Win.windowID)).css(
                    {
                        maxHeight: h - wizardHeight.open
                    });
        }
    }

    $('#' + Win.windowID).data('templateEditor').validateSyntax();
    doupdateEditorSize = false;
}



function isFullScreen(cm) {
    return /\bCodeMirror-fullscreen\b/.test(cm.getWrapperElement().className);
}
function winHeight() {
    return window.innerHeight || (document.documentElement || document.body).clientHeight;
}
function setFullScreen(cm, full) {
    var wrap = cm.getWrapperElement();
    if (full) {
        wrap.className += " CodeMirror-fullscreen";
        document.documentElement.style.overflow = "hidden";
        $('<span>').addClass('editor-place').css({
            height: '0'
        }).insertBefore($(wrap));
        $(wrap).appendTo($('body'));
    } else {

        var place = $('span.editor-place');
        wrap.className = wrap.className.replace(" CodeMirror-fullscreen", "");
        wrap.style.height = "";
        document.documentElement.style.overflow = "";
        $(wrap).insertAfter($(place));
        place.remove();
    }

    cm.refresh();
    cm.focus();
}


function toggleFullScreen()
{
    var sourceCodeEditor = $('#' + Win.windowID).data('sourceCodeEditor');
    var edit = sourceCodeEditor.getEditor();
    setFullScreen(edit, !isFullScreen(edit));
}

function toggleWizard(obj)
{



    wizardOpen = ($(obj).is(':visible') ? true : false);
    if (wizardOpen && !$('#wizardTable', $('#' + Win.windowID)).is(':visible'))
    {

        $('#search-form', $('#' + Win.windowID)).hide();
        $('#wizardTable', $('#' + Win.windowID)).show();
        Win.redrawWindowHeight();
        Win.refreshWindowScrollbars(Win.windowID);
        return;
    }

    if ($('#' + Win.windowID).data('templateEditor'))
    {
        var h = parseInt($('.CodeMirror-wrapping iframe:first', $('#' + Win.windowID)).height());
        hfix = 0;
        if (isFullscreen)
        {
            hfix = 5;
        }

        if (!wizardOpen)
        {
            obj.show();
        }
        else
        {
            obj.hide();
        }
        Win.redrawWindowHeight();
        Win.refreshWindowScrollbars(Win.windowID);
    }
}


// Search Form

function searchCode()
{
    var sourceCodeEditor = $('#' + Win.windowID).data('sourceCodeEditor');
    var edit = sourceCodeEditor.getEditor();
    edit.focus();


    wizardOpen = ($('#wizard', $('#' + Win.windowID)).is(':visible') ? true : false);
    if ($('#search-form', $('#' + Win.windowID)).is(':visible'))
    {
        $('#search-form', $('#' + Win.windowID)).hide();
        $('#wizardTable', $('#' + Win.windowID)).show();
        Win.refreshWindowScrollbars();
        return;
    }

    if (!wizardOpen)
    {
        $('#wizardTable', $('#' + Win.windowID)).hide();
        toggleWizard($('#wizard', $('#' + Win.windowID)));
        Win.refreshWindowScrollbars();
    }

    $('#wizardTable', $('#' + Win.windowID)).hide();
    $('#search-form', $('#' + Win.windowID)).show();
}

var searchFirst = false;
var searchTerm = '';
var ignoreCase = false;
// Search Click

function searchFun(a)
{
    var sourceCodeEditor = $('#' + Win.windowID).data('sourceCodeEditor');
    var edit = sourceCodeEditor.getEditor();
    edit.focus();

    var b = a.value;
    if (b != searchTerm)
        searchFirst = false;

    searchTerm = b;
    if (b)
    {
        ignoreCase = $('#ignoreCases', $('#' + Win.windowID)).get(0).checked;
        var c = edit.getSearchCursor(b, 1, typeof b == "string" && b == b.toLowerCase());
        var d = c.findNext();
        if (!d && !searchFirst)
        {
        }
        else if (d)
        {
            searchFirst = true;
            c.select();
            if (window.opera)
                a.focus()
        }
        else
            searchFirst = false
    }
    return c;
}


function replaceFun(a, b)
{
    var sourceCodeEditor = $('#' + Win.windowID).data('sourceCodeEditor');
    var edit = sourceCodeEditor.getEditor();
    if (!edit && !edit.dcmseditor.frame)
    {
        return;
    }

    ignoreCase = $('#ignoreCases', $('#' + Win.windowID)).get(0).checked;
    searchFirst = false;
    var c = edit.dcmseditor.selection();
    if (c && !ignoreCase && c == b.value)
    {
        edit.dcmseditor.replaceSelection(b.value);
        return;
    }
    else if (c && ignoreCase && (c.toLowerCase == b.value.toLowerCase()))
    {
        edit.dcmseditor.replaceSelection(b.value);
        return;
    }

    var d = edit.dcmseditor.getSearchCursor(a.value, false, ignoreCase);
    if (d.findNext())
    {
        d.select();
        d.replace(b.value);
        edit.dcmseditor.onChange()
    }
}


function replaceAllFun(a, b)
{

    if (!window.dcmseditor && !window.dcmseditor.frame)
    {
        return;
    }

    ignoreCase = $('#ignoreCases', $('#' + Win.windowID)).get(0).checked;
    searchFirst = false;
    var c = $('#' + Win.windowID).data('templateEditor').dcmseditor.getSearchCursor(a.value, false, ignoreCase);
    var i = 0;
    while (c.findNext())
    {
        c.replace(b.value);
        i++
    }

    $('#editor-status .replaces-status', $('#' + Win.windowID)).text(i + ' Übereinstimmungen wurden ersetzt').show();
    setTimeout(function()
    {
        $('#editor-status .replaces-status', $('#' + Win.windowID)).fadeOut(500, function()
        {
            $(this).hide();
        });
    }, 5000);
}








function setupTagGroups()
{
    var select = $('#' + Win.windowID).find('#TagGroupSelect');
    var sourceCodeEditor = $('#' + Win.windowID).data('sourceCodeEditor');

    if (select.length == 0)
    {
        return;
    }


    select.empty();
    CodeMirror.xmlHints['<'] = ['cp:', 'div', 'span', 'p', 'strong', 'em', 'table', 'tbody', 'thead', 'tr', 'td'];
    CodeMirror.xmlHints['<cp:'] = [];
    for (tagName in top.dcms_tags) {
        var tag = top.dcms_tags[tagName];
        if (top.dcms_tags[tagName].isSingleTag || top.dcms_tags[tagName].singleTag && !in_array(tagName, sourceCodeEditor.selfCloseTags))
        {
            sourceCodeEditor.selfCloseTags.push(tagName);
        }
        else if (!top.dcms_tags[tagName].isSingleTag && !top.dcms_tags[tagName].singleTag && !in_array(tagName, sourceCodeEditor.htmlIndent))
        {
            sourceCodeEditor.htmlIndent.push(tagName);
        }

        var t = tagName.replace('cp:', '');
        if (typeof t == 'string' && t != '')
        {
            CodeMirror.xmlHints['<cp:'].push(t);

            if (typeof CodeMirror.xmlHints['<cp:' + t + ' '] === 'undefined' && top.dcms_tags[tagName].attributes && top.dcms_tags[tagName].attributes.length > 0)
            {
                CodeMirror.xmlHints['<cp:' + t + ' '] = [];
                for (attr in top.dcms_tags[tagName].attributes) {

                    if (attr)
                    {
                        CodeMirror.xmlHints['<cp:' + t + ' '].push(attr);
                    }
                }
            }
            CodeMirror.xmlHints['<cp:' + t + '>'] = ['<'];
        }
    }

    var group = $('<optgroup>').attr('label', 'cp: Tags');
    for (var key in top.tagLabels)
    {

        if (key == 'alltags')
        {
            group.append($('<option>').attr(
                    {
                        'value': key,
                        'selected': 'selected'
                    }).append(top.tagLabels[key]));
            group.append($('<option>').attr(
                    {
                        'value': '-1'
                    }).append('------------------'));
        }
        else
        {
            group.append($('<option>').attr(
                    {
                        'value': key
                    }).append(top.tagLabels[key]));
        }
    }
    select.append(group);
    group = $('<optgroup>').attr('label', 'Template Funktionen');
    select.append(group);
    selectTagGroup('alltags');
    select.addClass('inputS');
    //select.selectbox('detach');
    select.on('change', function() {
        selectTagGroup(this.value);
    });



    Win.prepareWindowFormUi(Win.windowID);
}

function selectTagGroup(groupname)
{

    if (groupname != "-1")
    {
        $('#tagSelection').show();
        var elem = $('#tagSelection');
        elem.empty();
        for (var key in top.tagGroups[groupname])
        {
            str = top.tagGroups[groupname][key];
            elem.append($('<option>').attr(
                    {
                        'value': str
                    }).append(str));
        }
    }
}


function executeEditButton()
{
    var _sel = document.getElementById('tagSelection');
    if (_sel.selectedIndex > -1)
    {
        editCpTag(_sel.value);
    }
}

function editCpTag(tagname, insertAtCursor)
{
    if (!insertAtCursor)
    {
        insertAtCursor = 0;
    }
    openWizard(tagname, insertAtCursor);
}


function openWizard(tagname, insertAtCursor)
{


    var tagDef = top.dcms_tags['cp:' + tagname];
    if (typeof tagDef != 'undefined' && (typeof tagDef['attributes'] != 'undefined' && tagDef['attributes'].length == 0) || typeof tagDef['attributes'] == 'undefined')
    {
        createWizardTag(tagname, insertAtCursor, new Array());
        return;
    }



    var timers;
    var parms = {};
    parms.adm = 'skins';
    parms.action = 'wizard';
    parms.tag = tagname;
    parms.ajax = 1;
    $('#desktop').mask('Lade Tag-Wizard...');
    $.post('admin.php', parms, function(data)
    {
        $('#desktop').unmask();

        if (Tools.responseIsOk(data))
        {

            var popup = Tools.createPopup(data.maincontent, {
                title: 'Tag Wizard...',
                minWidth: 450,
                minHeight: 160,
                WindowToolbar: data.toolbar,
                onAfterShow: function(event, wm, callback)
                {


                    if ($('#create_tag', wm.$el).length)
                    {
                        $('#create_tag', wm.$el).click(function()
                        {
                            var result = $('form:first', wm.$el).serializeArray();
                            createWizardTag(tagname, insertAtCursor, result);
                        });
                    }


                    Win.prepareWindowFormUi(wm.id);

                    if (Tools.isFunction(callback))
                    {
                        callback();
                    }

                },
                onBeforeClose: function(e, wm, callback)
                {
                    var result = $('form:first', wm.$el).serializeArray();
                    createWizardTag(tagname, insertAtCursor, result);

                    if (Tools.isFunction(callback))
                    {
                        callback();
                    }

                }
            });

            $('#create_tag', $(popup)).click(function()
            {
                var result = $('form:first', $(popup)).serializeArray();
                createWizardTag(tagname, insertAtCursor, result);
                $(popup).data('WindowManager').close();
            });
        }
        else
        {
            alert('Error: ' + data.msg);
        }
    }, 'json');
}

function createWizardTag(tagname, insertAtCursor, formdata)
{
    if (tagname && typeof formdata != 'undefined')
    {
        var tagStr = '<cp:' + tagname;
        var tagCData = '';
        // append Attributes
        for (var i = 0; i < formdata.length; ++i)
        {
            var field = formdata[i];
            if (field['name'] == '_tagname')
            {
                continue;
            }



            if (field['name'] && field['name'] != '__custom' && field['name'] != 'cdata')
            {
                tagStr += ' ' + field['name'] + '="' + field['value'] + '"';
            }
            else if (field['name'] && field['name'] == 'cdata')
            {
                tagCData = (typeof field['value'] != 'undefined' ? field['value'] : '');
            }
            else if (field['name'] && field['name'] == '__custom')
            {
// append custom Attributes

                rows = field['value'].split("\n");
                for (var i = 0; i < rows.length; ++i)
                {
                    if (rows[i].trim())
                    {
                        tagStr += ' ' + rows[i].trim();
                    }
                }
            }
        }


// check tag type (if exists the definition)
        if (top.dcms_tags['cp:' + tagname])
        {
            if (!top.dcms_tags['cp:' + tagname]['singleTag'])
            {
                tagStr += '>' + tagCData + '</cp:' + tagname + '>'; // add close tag
            }
            else
            {
                tagStr += '/>'; // single tag
            }
        }
        else
        {
// create default add close tag
            tagStr += '>' + tagCData + '</cp:' + tagname + '>';
        }

        document.getElementById('tag_edit_area').value = tagStr;
    }
}



function addCursorPosition(tagText)
{
    var sourceCodeEditor = $('#' + Win.windowID).data('sourceCodeEditor');
    var edit = sourceCodeEditor.getEditor();
    if (edit)
    {
        edit.replaceSelection(tagText);
    }
    else
    {
    }
}

function insertAtEnd(tagText)
{
    var sourceCodeEditor = $('#' + Win.windowID).data('sourceCodeEditor');
    var edit = sourceCodeEditor.getEditor();
    if (edit)
    {
        edit.insertIntoLine(edit.lastLine(), "end", "\n" + tagText);
    }
    else
    {

    }
}


function dcmsEditorUndo()
{
    var sourceCodeEditor = $('#' + Win.windowID).data('sourceCodeEditor');
    var edit = sourceCodeEditor.getEditor();

    edit.undo();
}

function dcmsEditorRedo()
{
    var sourceCodeEditor = $('#' + Win.windowID).data('sourceCodeEditor');
    var edit = sourceCodeEditor.getEditor();

    edit.redo();
}

function setWrapping(wrap)
{
    var sourceCodeEditor = $('#' + Win.windowID).data('sourceCodeEditor');
    var edit = sourceCodeEditor.getEditor();
    edit.setOption('lineWrapping', wrap);
}

var reindentinterval, _ti1;
function reindent()
{
    var sourceCodeEditor = $('#' + Win.windowID).data('sourceCodeEditor');
    var edit = sourceCodeEditor.getEditor();



    if (!edit)
    {
     //   console.log('invalid activeCodeMirror');
        return true;
    }
    /*
     var info = edit.getCursor();
     edit.setSelection({
     line: 0,
     ch: 0
     }, {
     line: edit.lineCount() - 1
     });
     
     
     autoFormatSelection();
     
     
     
     
     edit.setCursor(info.line, info.ch);
     edit.focus();
     
     return;
     */



    if (reindentinterval) {
        clearInterval(reindentinterval);
    }

    if (typeof js_beautify == 'undefined')
    {
        if (_ti1)
        {
            if (typeof js_beautify == 'undefined')
            {
                clearInterval(_ti1);
                _ti1 = setInterval(reindent, 10);
            }
            else
            {
                clearInterval(_ti1);
                reindent();
            }
        }
        else
        {
            Loader.require(
                    'html/js/backend/tpleditor/beautifier/lib/beautify.js',
                    'html/js/backend/tpleditor/beautifier/lib/beautify-css.js',
                    'html/js/backend/tpleditor/beautifier/lib/beautify-html.js',
                    'html/js/backend/tpleditor/beautifier/test/sanitytest.js',
                    'html/js/backend/tpleditor/beautifier/test/beautify-tests.js',
                    'html/js/backend/tpleditor/beautifier/lib/unpackers/javascriptobfuscator_unpacker.js',
                    'html/js/backend/tpleditor/beautifier/lib/unpackers/urlencode_unpacker.js',
                    'html/js/backend/tpleditor/beautifier/lib/unpackers/p_a_c_k_e_r_unpacker.js',
                    'html/js/backend/tpleditor/beautifier/lib/unpackers/myobfuscate_unpacker.js', function()
            {
                if (typeof js_beautify == 'undefined')
                {
                    _ti1 = setInterval(reindent, 10);
                }
                else
                {
                    clearInterval(_ti1);
                    reindent();
                }
            });
        }
    }
    else
    {
        var info = edit.getCursor();
        var source = edit.getValue();

        var tabSize = 1;

        var newvalue = '';

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
        // CDATA Patch
        source = source.replace(/((\/\*\s*)(\<\s*\S*\!\[CDATA\[)(\s*\S*\*\/))/gi, '/* <![CDATA[ */');
        source = source.replace(/((\/\*\s*)(\]\]\s*\S*>)(\s*\S*\*\/))/gi, '/* ]]> */');


        source = source.replace(/{\*(\s*)/gi, '{* ');
        source = source.replace(/(\s*)\*}/gi, ' *}');


        if (looks_like_html(source)) {
            newvalue = html_beautify(source, opts);

        }
        else
        {
            source = unpacker_filter(source);
            newvalue = js_beautify(source, opts);
        }
        /*
         if (js_source && js_source[0] === '<' && js_source.substring(0, 4) !== '<!--')
         {
         newvalue = style_html(js_source, tabSize, '\t', 9000);
         }
         else
         {
         newvalue = js_beautify(unpacker_filter(js_source), {
         indent_size: tabSize,
         indent_char: '\t',
         preserve_newlines: true,
         braces_on_own_line: false,
         keep_array_indentation: false,
         space_after_anon_function: true
         });
         }
         */
        // CDATA Patch
        newvalue = newvalue.replace(/{\*(\s*)/gi, '{* ');
        newvalue = newvalue.replace(/(\s*)\*}/gi, ' *}');

        newvalue = newvalue.replace(/((\/\*\s*){1,}(\<\s*\S*\!\[CDATA\[)(\s*\S*\*\/)?)/g, '/* <![CDATA[ */');
        newvalue = newvalue.replace(/((\/\*\s*){1,}(\]\]\s*\S*>)(\s*\S*\*\/)?)/gi, '/* ]]> */');

        var loaded = edit.setValue(newvalue);
        edit.setCursor(info.line, info.ch);
        edit.focus();
        /*
         reindentinterval = setInterval(function ()
         {
         if (loaded)
         {
         clearInterval(reindentinterval);
         $('#'+Win.windowID).data('templateEditor').updateErrorLines();
         $('#'+Win.windowID).data('templateEditor').updateMarkerBar();
         }
         },250);
         
         
         */
    }
}
function looks_like_html(source)
{
    // <foo> - looks like html
    // <!--\nalert('foo!');\n--> - doesn't look like html

    var trimmed = source.replace(/^[ \t\n\r]+/, '');
    var comment_mark = '<' + '!-' + '-';
    return (trimmed && (trimmed.substring(0, 1) === '<' && trimmed.substring(0, 4) !== comment_mark));
}


function toggleDomTree()
{

    if (!$('#_domTree', $('#' + Win.windowID)).is(':visible'))
    {

        var _contain = $('<div>').append($(dcmseditor.getCode()));
        var domStr = showDomTree(_contain.get(0), 0);
        $('#dcmsDomTree', $('#' + Win.windowID)).empty().append($(domStr));
        $('#_domTree', $('#' + Win.windowID)).show();
        $('#dcmsDomTree', $('#' + Win.windowID)).resizable(
                {
                    handles: 'w',
                    minWidth: 250,
                    maxWidth: 350
                });
    }
    else
    {
        $('#_domTree', $('#' + Win.windowID)).hide();
        $('#dcmsDomTree', $('#' + Win.windowID)).resizable("destroy");
    }
}



/**
 *	Create DOM Tree
 */

function showDomTree(topElement, topCode)
{
    var outStr = '';
    topCode = topCode ? topCode : '0';
    if (!topElement)
    {
        outStr = 'Error, no top level element supplied.';
    }
    else if (typeof(topElement.nodeType) == 'undefined')
    {
        outStr = 'Error, browser is not DOM compliant.';
    }
    else
    {

        if (topCode == '0')
        {
            if (topCode == '0')
            {
                outStr += '<ul><li>';
            }

            if (topElement.nodeType == 1)
            {
                for (var x = 0; topElement.childNodes[x]; x++)
                {
                    outStr += showDomTree(topElement.childNodes[x], topCode + '_' + x);
                }

            }

            if (topCode == '0')
            {
                outStr += '</li></ul>';
            }

        }
        else
        {

            if (topElement.nodeType == 1)
            {
                if (topCode == '0')
                {
                    outStr += '<ul>';
                }
                outStr += '<li id="n-' + topCode + '">' + (topElement.childNodes.length > 0 ? '<a href="#" class="nplus" onclick="disTog(\'' + topCode + '\',this);return false;">' : '');
                outStr += '<span>';
                outStr += (!topElement.childNodes.length ? '<ins></ins>' : '');
                outStr += topElement.nodeName + '</span>';
                outStr += (topElement.childNodes.length > 0 ? '</a>' : '');
                if (topElement.childNodes.length)
                {
                    outStr += '<ul id="' + topCode + '" style="display:none;">';
                    for (var x = 0; topElement.childNodes[x]; x++)
                    {
                        outStr += showDomTree(topElement.childNodes[x], topCode + '_' + x);
                    }
                    outStr += '</ul>';
                }
                outStr += '</li>';
                if (topCode == '0')
                {
                    outStr += '</ul>';
                }
            }

        }
    }
    return outStr;
}

function disTog(id, el)
{
    el.className = (el.className == 'nplus' ? 'nmin' : 'nplus');
    document.getElementById(id).style.display = document.getElementById(id).style.display ? '' : 'none';
}


function refreshEditorHeight(windowID, heightOfWindowContent, widthOfWindowContent)
{
    var fh = $('#' + windowID).find('#editorfooter').outerHeight(true);
    var ed = $('#' + windowID).data('templateEditor');
    var wizardH = $('#' + windowID).find('#wizard').outerHeight(true);
    $('#' + windowID).find('#editormain').css({height: heightOfWindowContent - fh - wizardH})
            .find('.sourceEditor > div:first').css({height: heightOfWindowContent - fh - wizardH});
    var h = (heightOfWindowContent - fh - wizardH);
    //var w = (widthOfWindowContent - $('#'+ windowID).find('#editorfooter').outerWidth() );
    ed.setSize('100%', h);
}