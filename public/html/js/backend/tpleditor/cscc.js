/* CSCC - Common Sense Code Completion
 *  Basic but practical code completion for CodeMirror
 *
 * Written in 2010 by Martin Kool of Q42 to help developers and designers working
 * in quplo (http://quplo.com) reduce the daily amount of keystrokes.
 *
 * The purpose of CSCC is simple; to aid you while you type. It is not meant to be
 * a full-fledged code completion engine. CSCC is not context aware, but simply
 * sees what tag you are typing and offers the attributes and possible values.
 *
 * Follow me on twitter (@mrtnkl) or contact me directly: martin@q42.nl.
 * If you want to read more on our philosophy of creating an online editor,
 * follow the quplog: http://blog.quplo.com
 *
 * Marijn Haverbeke deserves ALL the credits as he created CodeMirror!
 * http://marijn.haverbeke.nl/codemirror/index.html
 */

function AttachEvent(obj, evt, fnc, useCapture) {
    if (!useCapture)
        useCapture = false;
    if (obj.addEventListener) {
        obj.addEventListener(evt, fnc, useCapture);
        return true;
    } else if (obj.attachEvent)
        return obj.attachEvent("on" + evt, fnc);
    else {
        MyAttachEvent(obj, evt, fnc);
        obj['on' + evt] = function() {
            MyFireEvent(obj, evt)
        };
    }
}

//The following are for browsers like NS4 or IE5Mac which don't support either
//attachEvent or addEventListener
function MyAttachEvent(obj, evt, fnc) {
    if (!obj.myEvents)
        obj.myEvents = {};
    if (!obj.myEvents[evt])
        obj.myEvents[evt] = [];
    var evts = obj.myEvents[evt];
    evts[evts.length] = fnc;
}
function MyFireEvent(obj, evt) {
    if (!obj || !obj.myEvents || !obj.myEvents[evt])
        return;
    var evts = obj.myEvents[evt];
    for (var i = 0, len = evts.length; i < len; i++)
        evts[i]();
}

top.weEditorFrameController = {
    "getVisibleEditorFrame": function() {
        return top;
    }
};

function doScrollTo() {
    if (parent.scrollToVal) {
        window.scrollTo(0, parent.scrollToVal);
        parent.scrollToVal = 0;
    }
}

function setScrollTo() {
    parent.scrollToVal = pageYOffset;
}

function goTemplate(tid) {
    if (tid) {
        top.weEditorFrameController.openDocument("tblTemplates", tid, "text/weTmpl");
    }
}

function translate(c) {
    f = c.form;
    n = c.name;
    n2 = n.replace(/tmp_/, "we_");
    n = n2.replace(/^(.+)#.+\]$/, "$1]");
    t = f.elements[n];
    check = f.elements[n2].value;

    t.value = (check == "on") ? br2nl(t.value) : nl2br(t.value);

}
function nl2br(i) {
    i = i.replace(/\r\n/g, "<br>");
    i = i.replace(/\n/g, "<br>");
    i = i.replace(/\r/g, "<br>");
    return i.replace(/<br>/g, "<br>\n");
}
function br2nl(i) {
    i = i.replace(/\n\r/g, "");
    i = i.replace(/\r\n/g, "");
    i = i.replace(/\n/g, "");
    i = i.replace(/\r/g, "");
    return i.replace(/<br ?\/?>/gi, "\n");
}




// Common Sense Code Completion
var cscc =
        {
            IE: (navigator.appName == "Microsoft Internet Explorer"),
            triggerChars: 1, // the number of tagname character after which cscc triggers
            visible: false, // if suggestions are visible
            selected: null, // the currently selected suggestion
            initialPos: 0,
            sensePath: "",
            visibleItemsType: 0, // the type of items (tags, attribute names, or values)
            currentParser: null,
            currentLeft: 0,
            currentSelect: null,
            currentEditor: null,
            currentContextTree: null,
            currentContextElem: null,
            elAtCursor: null,
            editor: null, // the instance of codemirror to complete
            maxItems: 6,
            curItems: 0,
            previousSuggestions: null,
            sensePathSeparator: "}}",
            // Options...
            options: {
                dontStyle: false, // if true, do not add a STYLE with the CSS to the document.
                xhtmlAware: true  // if true, adds a "/" to self closing tags: ==> <br />
            },
            // Extend function borrowed from Underscore.js
            // Use external libraries (jQuery, Underscore) if available
            extend: function(obj, source) {
                for (var prop in source)
                    obj[prop] = source[prop];
                return obj;
            },
            initOptions: function(options, editor) {

                // modify some values below to meet your wishes
                var opts = cscc.extend({
                    tabMode: "shift",
                    height: "90%",
                    textWrapping: true,
                    parserfile: ["parsexmlcontext.js", "parsecss.js", "tokenizejavascript.js", "parsejavascript.js"],
                    stylesheet: ["Codemirror/css/xmlcolors.css", "Codemirror/css/jscolors.css", "Codemirror/css/csscolors.css"],
                    path: "Codemirror/js/",
                    autoMatchParens: false,
                    lineNumbers: false//,
                            // cursorActivity: cscc.cursorActivity
                }, options);
                //if (!cscc.IE) {

                if (editor.getMode().name == 'xml' || editor.getMode().name == 'html')
                {

                    opts.keyDownFunction = cscc.keyDown;
                    opts.keyPressFunction = cscc.keyPress;
                    opts.keyUpFunction = cscc.keyUp;

                    //CodeMirror.on('cursorActivity', cscc.cursorActivity);
                    editor.on('keyDown', cscc.keyDown);
                    editor.on('keyPress', cscc.keyPress);
                    editor.on('keyUp', cscc.keyUp);

                }
                //}
                this.options = this.extend(this.options, opts);

                // Don't add styles to the document head if we don't have to
                if (!this.options.dontStyle)
                    this.addStyle();

                if (editor.getMode().name == 'xml' || editor.getMode().name == 'html')
                {
                    csccSense.init(this);
                    for (i in top.dcms_tags) { //added by we:willRockYou - inject dynamic we:tag dictionary
                        csccSense.xmlDictionary[i] = top.dcms_tags[i].attributes;
                    }
                }
                
                
                
                return opts;
            },
            // creates the CodeMirror instance
            init: function(textareaId, options, editor) {

                    this.editor = editor;
                    $('#cmc-suggestions').remove();
                    var opts = cscc.initOptions(options, editor);
                

                return opts;
                //cscc.editor = CodeMirror.fromTextArea(textareaId, opts);	
                //return cscc.editor;
            },
            // @mod by dw2k
            setEditor: function(editor) {
                cscc.editor = editor;

            },
            cursorActivity: function(elAtCursor) {
                if (!elAtCursor)
                    return;
                else {
                    console.log([elAtCursor]);
                }
                cscc.elAtCursor = elAtCursor;
            },
            addStyle: function() {

                if ($('#cmc').length)
                {
                    return;
                }


                var style = document.createElement('style');
                var cssStr = [
                    "#cmc-suggestions, .cmc-suggestions",
                    "{",
                    "  max-height: 203px;", //added by we:willRockYou - we need a maximum height
                    "  margin-top: 18px;",
                    "  position: absolute;",
                    "  z-index:999999!important;",
                    "  font-family: tahoma;",
                    "  font-size: 11px;",
                    "  background: #EEF2F4;",
                    "  width: 150px; max-width: 250px;",
                    //  "  max-height: " + (17 * cscc.maxItems) + "px;",
                    "  overflow-y: auto;", //added by we:willRockYou - and therefor we need a scrollbar as well
                    "  overflow-x: hidden;", //added by we:willRockYou
                    "  cursor:default;",
                    "  border: outset 1px;",
                    "  box-shadow:1px 2px 3px rgba(0,0,0,.5);",
                    "  -webkit-box-shadow:1px 2px 3px rgba(0,0,0,.5);",
                    "  -moz-box-shadow:1px 2px 3px rgba(0,0,0,.5);",
                    "  -o-box-shadow:1px 2px 3px rgba(0,0,0,.5);",
                    "  border-radius:3px;",
                    "  -webkit-border-radius:3px;",
                    "  -moz-border-radius:3px;",
                    "  -o-border-radius:3px;",
                    "}.cmc-suggestions{height: auto; max-height: 203px;margin-top: 1px;}",
                    "#cscc-scrollbar {",
                    "  display:none;",
                    "  position: absolute;",
                    "  right:3px;",
                    "  top:0;",
                    "  margin-top:3px;",
                    "  width:4px;",
                    "  height:4px;",
                    "  background: #000;",
                    "  opacity:.2;",
                    "  z-index:999999;",
                    "  border-radius:4px;",
                    "  -webkit-border-radius:4px;",
                    "  -moz-border-radius:4px;",
                    "  -o-border-radius:4px;",
                    "}",
                    "#cmc-suggestions div,.cmc-suggestions li",
                    "{",
                    "  padding: 2px 0 2px 5px;position: relative;display: inline-block;line-height: 14px;padding-left: 3px;padding-bottom: 2px;width: 100%;",
                    "}",
                    "#cmc-suggestions div.selected,.cmc-suggestions .selected",
                    "{",
                    "  background: #738B9B;",
                    "  color: #fff;",
                    "}"
                ].join("");
                style.setAttribute("type", "text/css");
                style.setAttribute("id", "cmc");




                if (style.styleSheet)
                    style.styleSheet.cssText = cssStr;
                else {
                    var cssText = document.createTextNode(cssStr);
                    style.appendChild(cssText);
                }
                document.body.appendChild(style);
            },
            contextTree: function(node) {
                var contextTree = [];
                var initialContext = node && node.parserContext && node.parserContext.context;
                while (initialContext) {
                    contextTree.push(initialContext);
                    initialContext = initialContext.prev;
                }
                if (contextTree.length == 0)
                    contextTree.push({
                        name: cscc.sensePathSeparator,
                        attributes: {}
                    });
                return contextTree;
            },
            keyDownInsideEditor: function(evt, select, editor) {
                return cscc.keyDown(evt, select, editor);
            },
            keyDown: function(evt, select, editor) {

                if (editor.getMode().name != 'xml' && editor.getMode().name != 'html')
                {
                    return;
                }

                cscc.selectX = select; //added by we:willRockYou - we need this in onclick-event. I know, doesn't look pretty. didn't know how to get these objects, but they are out here somewhere, I'm sure!
                cscc.editorX = editor; //added by we:willRockYou

                top.currentHoveredTag = undefined; //added by we:willRockYou - we need to hide our tooltip on keypress
                // hideDescription(); //added by we:willRockYou - we need to hide our tooltip on keypress

                //console.log(['keyDown event', select, editor, cscc.editor]);

                var l = cscc.getCursorInfo();
                var text = l.text.substr(0, l.pos);
                var startPos = text.lastIndexOf("<");
                var endPos = text.lastIndexOf(">");
                var inTag = startPos > endPos;
                var brEl = l.obj.line;

                if ((!cscc.IE && evt.keyCode == 59) || (cscc.IE && evt.keyCode == 186))
                {
                    //alert('OK');
                }

                // handle basic cursor and other key activity
                switch (evt.keyCode) {

                    case 16: // shift
                        return false;
                        break;
                    case 38: // up
                        if (cscc.visible) {
                            cscc.prev();
                            evt.stop();
                            return false;
                        }
                        break;
                    case 40: // down
                        if (cscc.visible) {
                            cscc.next();
                            evt.stop();
                            return false;
                        }
                        break;
                    case 13: // enter
                    case 9: // tab
                        if (cscc.visible) {
                            cscc.pick(evt, select, editor);
                            return false;
                        }
                        break;
                    case 27: // escape
                        if (cscc.visible) {
                            cscc.hide();
                            evt.stop();
                            return false;
                        }
                        break;
                }

                return true;
            },
            /*
             
             keyDown: function (evt, select, editor) {
             // handle basic cursor and other key activity
             switch (evt.keyCode) {
             case 38: // up
             if (cscc.visible) {
             cscc.prev();
             evt.stop();
             return false;
             }
             break;
             case 40: // down
             if (cscc.visible) {
             cscc.next();
             evt.stop();
             return false;
             }
             break;
             case 13: // enter
             case 9: // tab
             if (cscc.visible) {
             cscc.pick(evt, select, editor);
             return false;
             }
             break;
             case 27: // escape
             if (cscc.visible) {
             cscc.hide();
             evt.stop();
             return false;
             }
             break;
             }
             
             
             // start of custom quplo keysection
             if (evt.keyCode == 13 && evt.ctrlKey) {
             Sheet.lastLine = cscc.getCursorInfo(true);
             Sheet.maximize();
             Sheet.refocus();
             return false;
             }
             if (evt.keyCode == 27) {
             Sheet.hideSheets(true);
             return false;
             }
             if (evt.keyCode == 38 && evt.ctrlKey && evt.shiftKey) { // down
             Sheet.highlightPrevSheet();
             return false;
             }
             if (evt.keyCode == 89 && evt.ctrlKey && evt.shiftKey) { // Y
             evt.stop();
             Sheet.wrap();
             return false;
             }
             if (evt.keyCode == 67 && evt.ctrlKey && evt.shiftKey) { // C = create sheet
             evt.stop();
             window.open("/workon/" + webAddress + "/new");
             return false;
             }
             if (evt.keyCode == 69 && evt.ctrlKey && evt.shiftKey) { // Y
             evt.stop();
             Sheet.reIndent();
             return false;
             }
             if (evt.keyCode == 40 && evt.ctrlKey && evt.shiftKey) {
             Sheet.highlightNextSheet();
             return false;
             }
             // end of custom quplo key section
             
             return true;
             },
             */
            keyPress: function(evt, select, editor) {

                var character = evt.character;
                if (character != "'" && character != "\"" && character != ":" && character != "<" && character != ">" && character != "=")
                {
                    return true;
                }

                if (editor.getMode().name != 'xml' && editor.getMode().name != 'html')
                {
                    return;
                }

                var l = cscc.getCursorInfo();
                var text = l.text.substr(0, l.pos);
                var startPos = text.lastIndexOf("<");
                var endPos = text.lastIndexOf(">");
                var inTag = startPos > endPos;

                if ((!cscc.IE && evt.keyCode == 59) || (cscc.IE && evt.keyCode == 186))
                {
                    //alert('OK');
                }


                //console.log(['keyPress event', select, editor, cscc.editor]);



                if (!inTag && !cscc.isInCssDeclaration(l)) {
                    cscc.hide();
                    return true;
                }

                // Quote pressed, check if we need to omit end quote.
                if (character == "'" || character == "\"") {
                    if (l.text[l.pos] == character) {
                        select.setCursorPos(editor.container, {
                            node: l.obj.line,
                            offset: l.pos + 1
                        });
                        evt.stop();
                        return false;
                    }
                }


                // Expression Patch by dw2k
                if (character == ":") {
                    if (l.text[l.pos] == character) {
                        select.setCursorPos(editor.container, {
                            node: l.obj.line,
                            offset: l.pos + 1
                        });
                        evt.stop();
                        return true;
                    }
                }


                // Expression Patch by dw2k
                if (character == "<") {

                    select.insertAtCursor("˂"); // unicode char U+02C2
                    select.setCursorPos(editor.container, {
                        node: l.obj.line,
                        offset: l.pos + 1
                    });

                    evt.stop();
                    return false;
                }



                if (character == ">") {
                    // get the tagName that we're in

                    var endPosDouble = text.lastIndexOf('"');
                    var endPosSingle = text.lastIndexOf("'");
                    tmp = '';
                    if (endPosDouble)
                    {
                        tmp = text.substr(endPosDouble - 1);
                    }
                    else if (endPosSingle)
                    {
                        tmp = text.substr(endPosSingle - 1);
                    }

                    // @mod by dw2k
                    if (
                            !tmp.match(/^="/)  /* && 
                             (
                             text.match(/^([\w\._\-:]+).*="([^"]*)"\s*$/) || 
                             text.match(/^([\w\._\-:]+).*=\'([^\']*)\'\s*$/) || 
                             (text.match(/^([\w\._\-:]+).*$/) && !text.match(/^([\w\._\-:]+).*="([^"]*)"\s*$/) && !text.match(/^([\w\._\-:]+).*=\'([^\']*)\'\s*$/))
                             ) */
                            ) {
                        text = text.substr(startPos + 1);


                        var tagName = text.replace(/^([\w\._\-:]+).*$/, "$1");

                        // ">" pressed, check for autoclosing the tag, but ignore if already autoclosed
                        if (!text.match(/\/$/) && text.match(/^[\w\._\-:]+.*?$/)) {
                            // Now look if this tag is auto close...
                            if (csccSense.isSelfClose(tagName)) {
                                // For autoclose tags we might just let the ">" character slip through
                                // but I think it makes much more sense to add the ending slash here.
                                if (!text.match(/\/$/) && cscc.options.xhtmlAware) {
                                    select.insertAtCursor("/>");
                                    select.setCursorPos(editor.container, {
                                        node: l.obj.line,
                                        offset: l.pos + 2
                                    });
                                    cscc.hide();
                                    evt.stop();
                                    return false;
                                }
                            } else {
                                var endTag = "</" + tagName + ">";
                                if (l.text.indexOf(endTag) == -1 && !l.text.match(/\/$/)) {
                                    // auto insert closing tag
                                    select.insertAtCursor(">" + endTag);
                                    select.setCursorPos(editor.container, {
                                        node: l.obj.line,
                                        offset: l.pos + 1
                                    });
                                    cscc.hide();
                                    evt.stop();
                                    return false;
                                }
                            }
                        }
                    }
                    else
                    {
                        // Expression Patch by dw2k
                        select.insertAtCursor("˃"); // unicode char U+02C3
                        select.setCursorPos(editor.container, {
                            node: l.obj.line,
                            offset: l.pos + 1
                        });
                        evt.stop();
                        return false;

                    }

                }



                // autocomplete quotes, so id= becomes id="" whith cursor properly placed
                if (character == "=") {
                    text = text.substr(startPos + 1);
                    var p = new csccParseXml(text, l.pos - startPos);

                    if (p.state == csccParseXml.inAttributeName) {
                        select.insertAtCursor(character + "\"\"");
                        select.setCursorPos(editor.container, {
                            node: l.obj.line,
                            offset: l.pos + 2
                        });

                        // refresh cursor position and text, so the parser takes into account our added quotes
                        l = cscc.getCursorInfo();
                        text = l.text.substr(startPos + 1);

                        // see if we have anything to suggest
                        var parser = new csccParseXml(text, l.pos - startPos - 1);
                        cscc.update(l, parser, evt, select, editor);

                        evt.stop();
                        return false;
                    }
                }
                return true;
            },
            keyUp: function(evt, select, editor) {
                var k = evt.keyCode;

                if (editor.getMode().name != 'xml' && editor.getMode().name != 'html')
                {
                    return;
                }

                if (k == 13)
                    return; // enter
                if (k == 35 || k == 36 && cscc.visible)
                    return cscc.hide(); // home end
                if (k == 37 || k == 39)
                    return cscc.hide(); // left, right
                if (k != 8 && k != 32 && k < 48)
                    return;
                if (k == 83 && evt.ctrlKey)
                    return cscc.hide(); // Ctrl + S

                if ((!cscc.IE && evt.keyCode == 59) || (cscc.IE && evt.keyCode == 186))
                {
                    //alert('OK');
                }

                // console.log(['keyUp event', select, editor, cscc.editor]);

                var l = cscc.getCursorInfo();
                var text = l.text.substr(0, l.pos);
                var startPos = text.lastIndexOf("<");
                var endPos = text.lastIndexOf(">");
                var inTag = startPos > endPos;


                if (!inTag) {
                    cscc.hide();
                    return true;
                }



                // clear up cscc suggestions when last character was removed with backspace
                if (k == 8 && text.match(/^\s*$/))
                    return cscc.hide();

                if (!inTag) {
                    var inCssStateDeclaration = cscc.isInCssDeclaration(l);
                    if (inCssStateDeclaration) {
                        var curPos = l.pos;

                        for (var c in {
                            "{": 1,
                            ";": 1
                        }) {
                            startPos = text.lastIndexOf(c);
                            if (startPos != -1) {
                                text = text.substr(startPos + 1);
                                curPos -= (startPos + 1);
                            }
                        }

                        var declarationPart = text.replace(/^\s*(.*)$/gi, "$1");
                        indentationLength = text.length - declarationPart.length;
                        curPos -= indentationLength;
                        text = declarationPart;

                        var parser = new csccParseCss(text, curPos);
                        cscc.update(l, parser, evt, select, editor);
                    }
                    return;
                }

                text = text.substr(startPos + 1);
                var tagName = text.replace(/^([\w\._\-:]+)[^\/]*$/, "$1");

                // autocomplete quotes, so id= becomes id="" whith cursor properly placed
                if (!evt.shiftKey && (evt.keyCode == 107 || evt.keyCode == 187)) {
                    var p = new csccParseXml(text, l.pos - startPos);
                    if (p.state == csccParseXml.inAttributeEquals) {
                        select.insertAtCursor(editor.win, "\"\"");
                        select.setCursorPos(editor.container, {
                            node: l.obj.line,
                            offset: l.pos + 1
                        });
                        evt.stop();
                        // refresh cursor position and text, so the parser takes into account our added quotes
                        l = cscc.getCursorInfo();
                        text = l.text.substr(0, l.pos);
                    }
                }



                // see if we have anything to suggest
                var parser = new csccParseXml(text, l.pos - startPos);
                cscc.update(l, parser, evt, select, editor);
            },
            // simple wrapper method to get some cursor information from codemirror
            getCursorInfo: function(getLastLine) {

                var cursor = cscc.editor.getCursor();
                var info = cscc.editor.lineInfo(cursor.line);
                var lineNo = typeof getLastLine != 'undefined' ? cscc.editor.getCursor().line : 1;
                var curPosObj = cursor;

                // console.log('lineNo:'+ info.line +' line:'+ info.text);

                return {
                    line: info.line,
                    pos: cursor.ch,
                    text: info.text,
                    obj: curPosObj
                };
            },
            getSuggestionsDescriptionElement: function()
            {
                if ($('#cmc-suggestions-description').length == 1) {
                    return $('#cmc-suggestions-description').get(0);
                }

                var els = $('<div>').attr('id', 'cmc-suggestions-description');
                $('body').append(els);

                var el = $('#cmc-suggestions-description').get(0);

                return el;
            },
            // gets the suggestions container
            getSuggestionsElement: function() {
                if ($('#cmc-suggestions').length == 1) {
                    return $('#cmc-suggestions').get(0);
                }

                var els = $('<div>').attr('id', 'cmc-suggestions');
                $('body').append(els);

                var el = $('#cmc-suggestions').get(0);

                return el;
            },
            // returns the object, or when it is a function returns its resulting object
            getValueOrFunctionResult: function(obj) {
                try {
                    if (typeof (obj) == "function") {
                        var text = cscc.getCursorInfo().text;
                        var functionResult = obj(text);
                        return functionResult;
                    }
                } catch (e) {
                }
                return obj;
            },
            // parses the sensePath and gets the items for it
            getItemsForPath: function(parser, type) {

                function isOfType(obj, t) {
                    obj = cscc.getValueOrFunctionResult(obj);
                    if (obj == t)
                        return true;
                    if (typeof (obj) == "function")
                        return false;
                    for (n in obj) {
                        if (obj[n] == (t + 1))
                            return true;
                        var newObj = cscc.getValueOrFunctionResult(obj[n]);
                        // if the function result immediatly returned a numeric value 
                        if (newObj == t + 1)
                            return true;
                        for (m in newObj) {
                            if (newObj[m] == (t + 2))
                                return true;
                        }
                    }
                    return false;
                }

                // the dictionary is where all tag and attribute definitions are, the context contains nested relations
                var dictionary = oriDictionary = csccSense[parser.type + "Dictionary"];
                var context_dictionary = csccSense[parser.type + "Context"];
                var contextDictionaryApplies = false;

                // do some juggling with them
                if (context_dictionary && cscc.currentContextTree) {
                    var newDict = {};
                    var contextTag = cscc.currentContextTree[0];

                    // the current contextual tag has a definition in our context tree...
                    if (contextTag && context_dictionary[contextTag.name]) {

                        // usually, contents is an array of possible children tagnames
                        var contents = context_dictionary[contextTag.name];

                        // however, if it's an object, it means it either requires an attribute or an ancestor tag
                        var requiresMoreContext = typeof (contents) == "object" && !contents.push;

                        if (requiresMoreContext)
                        {
                            // first, check if there's an attribute
                            var attributeMatchFound = false;

                            // iterate through all contextual values (such as "*", "thead", "@id", "@id=bar", etc)
                            for (var name in contents) {
                                // if an attribute was found
                                if (name.indexOf("@") == 0) {
                                    // get the name and optional value
                                    var pairs = name.substr(1).split('='), attrName = pairs[0], attrValue = pairs[1];
                                    var attrObj = contextTag.attributes[attrName];
                                    if (attrObj) {
                                        if (!attrValue || attrObj == attrValue) {
                                            attributeMatchFound = true;
                                            contents = contents[name];
                                            break;
                                        }
                                    }
                                }
                            }

                            if (!attributeMatchFound) {
                                var ancestorTag = cscc.currentContextTree[1];
                                if (ancestorTag && contents[ancestorTag.name])
                                    contents = contents[ancestorTag.name];
                                else
                                    contents = contents["*"];
                            }
                        }

                        // fill the suggested dictionary only with tags specified in the context
                        var childTags = contents;
                        for (var i = 0; i < childTags.length; i++) {
                            var childTag = childTags[i];
                            var originalTagDefinition = dictionary[childTag];
                            if (originalTagDefinition)
                                newDict[childTag] = originalTagDefinition;
                        }

                        dictionary = newDict;
                        contextDictionaryApplies = true;
                    }
                }

                cscc.visibleItemsType = type;
                var items = [];
                var parts = parser.getSensePath().split(cscc.sensePathSeparator);
                var curSense = dictionary;
                var fragment = null;
                var matchedOnTopLevel = false;
                var previousSuggestionsContainItemsStartingWithThisFragment = false;
                var previousSuggestionsContainItemsStartingWithThisFragmentForType = type;

                // check if a previous suggestion starts with this fragment, so we don't switch to offer suggestions for "p" when "page" and "part" were shaun (the sheep)
                if (cscc.previousSuggestions && parts.length > 0)
                {
                    for (var i = 0; i < cscc.previousSuggestions.length; i++)
                    {
                        if (cscc.previousSuggestions[i].indexOf(parts[parts.length - 1]) == 0)
                        {
                            previousSuggestionsContainItemsStartingWithThisFragment = true;
                            previousSuggestionsContainItemsStartingWithThisFragmentIndex = type;
                            break;
                        }
                    }
                }

                // iterate over the parts and see where we're at
                for (var i = 0; i < parts.length; i++) {
                    var partName = parts[i];
                    var checkType = i + 1;
                    if (curSense && curSense[partName]) {
                        curSense = curSense[partName];
                        if (i == 0)
                            matchedOnTopLevel = true;
                    }
                    // check on root level of the dictionary for the first item in the sense path (solve <write p> conflict with p element)
                    else if (dictionary[partName] && i == 0 && !previousSuggestionsContainItemsStartingWithThisFragment)
                    {
                        curSense = dictionary[partName];
                        matchedOnTopLevel = true;
                    }
                    else if (oriDictionary[partName] && i == 0)
                    {
                        if (!previousSuggestionsContainItemsStartingWithThisFragment ||
                                previousSuggestionsContainItemsStartingWithThisFragmentForType != checkType) {
                            curSense = oriDictionary[partName];
                            matchedOnTopLevel = true;
                        }
                    }
                    else
                        fragment = partName;

                    if (curSense)
                        curSense = cscc.getValueOrFunctionResult(curSense);
                }

                if (!matchedOnTopLevel && type > 1)
                    return items;

                // if cscc is making sense, prepare the result
                if (curSense && typeof curSense == "object") {
                    for (var name in curSense) {
                        if (!parser.attributes || parser.attributes[name] == null) {
                            if (isOfType(curSense[name], type))
                                if (!fragment || name.toLowerCase().indexOf(fragment.toLowerCase()) == 0)
                                    items.push(name);
                        }
                    }
                }
                cscc.previousSuggestions = items;
                return items;
            },
            // fills the suggestions element with the right items
            fill: function(items, mode) {
                var isFont = cscc.currentParser.getSensePath().indexOf("font-family") == 0;
                var selectedValue = "";
                if (cscc.selected)
                    selectedValue = cscc.selected.innerHTML;



                var root = cscc.getSuggestionsElement();
                root.innerHTML = "";
                //root.innerHTML = "<span id='cscc-scrollbar'></span>";
                cscc.selected = null;
                var newSelectedEl = null;

                // add the items, and take into account the prev selected item
                for (var i = 0; i < items.length; i++) {
                    var el = root.ownerDocument.createElement("div");


                    el.onclick = function(e) { //added by we:willRockYou, enables clicking on an item
                        var results = cscc.getSuggestionsElement().getElementsByTagName("div");
                        var selectedIndex = -1;
                        for (var i = 0; i < results.length; i++) {
                            var result = results[i];
                            if (result.className.indexOf("selected") != -1) {
                                selectedIndex = i;
                                result.className = "";
                            }
                        }
                        e.target.className = "selected";
                        cscc.selected = e.target;
                        cscc.pick(e, cscc.selectX, cscc.editorX);
                        cscc.editor.focus();
                    }
                    /*
                     if ( mode == 'inTagName' )
                     {
                     if (items[i].match(/cp:/))
                     {
                     
                     }
                     }
                     else if (mode == 'inAttributeName')
                     {
                     if (items[i].match(/cp:/))
                     {
                     
                     }
                     }
                     else
                     {
                     
                     }
                     */

                    var value = items[i].replace("|", "");
                    el.setAttribute("rel", items[i]);
                    el.innerHTML = value;
                    if (isFont)
                        el.style.fontFamily = value;

                    root.appendChild(el);
                    if (!newSelectedEl)
                        newSelectedEl = el;
                    if (items[i] == selectedValue) {
                        newSelectedEl = el;
                    }
                }
                if (newSelectedEl)
                    newSelectedEl.className = "selected";
                cscc.selected = newSelectedEl;
                cscc.curItems = items.length;


            },
            // pop up the suggestions element
            show: function(line, pos, items, mode)
            {
                if (!this.visible) {
                    this.selected = null;
                }
                this.fill(items, mode);

                //var el = cscc.editor.nthLine(line);
                var offsetTop = 0;
                //if (!el) {
                //    el = cscc.editor.nthLine(line + 1);
                //    offsetTop = -26;
                //}





                // following lines modified by we:willRockYou - CodeMirror now has
                // it's own methods to get cursorCoords()!
                // See http://we.willrockyou.net/webEdition-editor-demo/
                // Also see original comments by Daniel (@we_willRockYou) on Quplog:
                // http://blog.quplo.com/2010/06/css-code-completion-in-your-browser/
                var coords = this.editor.cursorCoords(this.editor.getCursor());

                var el = this.getSuggestionsElement();
                var elDesc = this.getSuggestionsDescriptionElement();

                el.style.display = "block";
                elDesc.setAttribute("size", items.length);
                el.setAttribute("size", items.length);
                el.style.top = (coords.top - 5) + "px";
                el.style.left = coords.left + "px";


                elDesc.style.top = (coords.top - 5) + "px";
                elDesc.style.left = coords.left + $(el).outerWidth() + 5 + "px";

                this.visible = true;
                if (!cscc.selected)
                    cscc.next();

                // reposition if at bottom of screen
                if (el.offsetTop + el.offsetHeight > document.body.offsetHeight) {
                    el.style.top = (coords.top - el.offsetHeight - 17) + "px";
                    elDesc.style.top = (coords.top - el.offsetHeight - 17) + "px";
                }
            },
            // hide the box
            hide: function() {
                cscc.getSuggestionsDescriptionElement().style.display = "none";
                cscc.getSuggestionsElement().style.display = "none";
                cscc.visible = false;
                cscc.previousSuggestions = null;
            },
            // update the current suggestions box, if needed
            update: function(lineObj, parser, evt, select, editor) {
                cscc.currentParser = parser;
                cscc.currentSelect = select;
                cscc.currentEditor = editor;
                var items = [];

                var mode = 0;


                switch (parser.type) {
                    case "xml":
                        cscc.currentContextTree = cscc.contextTree(cscc.elAtCursor);
                        switch (parser.state) {
                            case csccParseXml.atStart:
                            case csccParseXml.inTagName:
                                var currentTag = parser.tagName;
                                // by default, only show tag suggestions based on the taglength treshold
                                var showSuggestions = (currentTag.length >= cscc.triggerChars);
                                // but if we're running contextual, provide suggestions immediatly
                                var firstContextualElement = cscc.currentContextTree[0];
                                if (csccSense.xmlContext && firstContextualElement && csccSense.xmlContext[firstContextualElement.name])
                                    showSuggestions = true;
                                if (showSuggestions)
                                    items = cscc.getItemsForPath(parser, 1);
                                mode = 'inTagName';
                                break;
                            case csccParseXml.beforeAttributeName:
                            case csccParseXml.inAttributeName:
                            case csccParseXml.afterTagOrAttribute:
                                items = cscc.getItemsForPath(parser, 2);
                                mode = 'inAttributeName';
                                break;
                            case csccParseXml.inAttributeValue:
                                items = cscc.getItemsForPath(parser, 3);
                                mode = 'inAttributeValue';
                                break;
                        }
                        break;
                    case "css":
                        switch (parser.state) {
                            case csccParseCss.atStart:
                            case csccParseCss.inProperty:
                                var currentProperty = parser.propertyName;
                                if (currentProperty.length < cscc.triggerChars)
                                    return;
                                items = cscc.getItemsForPath(parser, 1);
                                break;
                            case csccParseCss.beforeValue:
                            case csccParseCss.inValue:
                                items = cscc.getItemsForPath(parser, 2);
                                break;
                        }
                        break;
                }
                if (items.length > 0)
                    cscc.show(lineObj.line, lineObj.pos, items, mode);
                else
                    cscc.hide();
            },
            // highlight the next suggestion
            next: function() {
                var sug = cscc.getSuggestionsElement(),
                        desc = cscc.getSuggestionsDescriptionElement(),
                        results = sug.getElementsByTagName("div");

                var selectedIndex = -1;
                for (var i = 0; i < results.length; i++) {
                    var result = results[i];
                    if (result.className.indexOf("selected") != -1) {
                        selectedIndex = i;
                        result.className = "";
                    }
                }
                if (selectedIndex < results.length - 1)
                    selectedIndex++;
                else
                    selectedIndex = 0;
                var item = results[selectedIndex];
                if (item) {
                    cscc.selected = item;
                    item.className = "selected";
                    cscc.afterPrevOrNext(selectedIndex, item, sug);
                    var strObj = top.dcms_tags[item.getAttribute("rel")];

                    if (typeof strObj != "undefined" && typeof strObj.desc != "undefined" && strObj.desc != '')
                    {
                        $(desc).empty().append(strObj.desc).show();
                    }
                    else
                    {
                        $(desc).empty().hide();
                    }
                }



                if (results[selectedIndex].scrollIntoView) { //added by we:willRockYou - will scroll selected item into viewport. the div has now overflow-y:auto, so the currently selected item might be outside of viewport
                    results[selectedIndex].scrollIntoView();
                }


            },
            // highlight the previous suggestion
            prev: function() {
                var sug = cscc.getSuggestionsElement(),
                        desc = cscc.getSuggestionsDescriptionElement(),
                        results = sug.getElementsByTagName("div");
                var selectedIndex = -1;
                for (var i = 0; i < results.length; i++) {
                    var result = results[i];
                    if (result.className.indexOf("selected") != -1) {
                        selectedIndex = i;
                        result.className = "";
                    }
                }
                if (selectedIndex > 0)
                    selectedIndex--;
                else
                    selectedIndex = results.length - 1;
                var item = results[selectedIndex];
                if (item) {
                    cscc.selected = item;
                    item.className = "selected";
                    cscc.afterPrevOrNext(selectedIndex, item, sug);

                    var strObj = top.dcms_tags[item.getAttribute("rel")];


                    if (typeof strObj != "undefined" && typeof strObj.desc != "undefined" && strObj.desc != '')
                    {
                        $(desc).empty().append(strObj.desc).show();
                    }
                    else
                    {
                        $(desc).empty().hide();
                    }


                }

                if (results[selectedIndex].scrollIntoView) { //added by we:willRockYou - will scroll selected item into viewport. the div has now overflow-y:auto, so the currently selected item might be outside of viewport
                    results[selectedIndex].scrollIntoView();
                }

            },
            afterPrevOrNext: function(selectedIndex, item, sug) {
                var singleItemHeightInScrollbarSize = cscc.maxItems * 17 / cscc.curItems;
                if (item.offsetTop + item.offsetHeight > sug.offsetHeight + sug.scrollTop) {
                    sug.scrollTop = (selectedIndex - cscc.maxItems + 1) * 17;
                    var scrollbarTop = sug.scrollTop + ((selectedIndex - cscc.maxItems + 1) * singleItemHeightInScrollbarSize);
                    //$('#cscc-scrollbar').css('top', scrollbarTop + "px" );
                }
                else if (item.offsetTop < sug.scrollTop) {
                    sug.scrollTop = item.offsetTop;
                    var scrollbarTop = sug.scrollTop + (selectedIndex * singleItemHeightInScrollbarSize);
                    //$('#cscc-scrollbar').css('top', scrollbarTop + "px" );
                }
            },
            // highlight the first suggestion
            first: function() {
                var results = cscc.getSuggestionsElement().getElementsByTagName("div");
                var selectedIndex = -1;
                for (var i = 0; i < results.length; i++) {
                    var result = results[i];
                    result.className = (i == 0) ? "selected" : "";
                }
            },
            // highlight the last suggestion
            last: function() {
                var results = cscc.getSuggestionsElement().getElementsByTagName("div");
                var selectedIndex = -1;
                for (var i = 0; i < results.length; i++) {
                    var result = results[i];
                    result.className = (i == results.length - 1) ? "selected" : "";
                }
            },
            // pick the highlighted suggestion
            pick: function(evt, select, editor) {
                if (cscc.selected) {
                    var l = cscc.getCursorInfo();
                    var pos = l.pos;
                    cscc.hide();
                    var text = cscc.selected.innerHTML;
                    var cursorPos = cscc.selected.getAttribute("rel").indexOf("|");
                    var cursorOffset = 0;
                    var textOffset = 0; // used for the amount of extra text we insert, like ": "
                    if (cursorPos != -1)
                        cursorOffset = cursorPos - text.length - 1;

                    switch (this.currentParser.type) {
                        case "css":
                            if (cscc.visibleItemsType == 1) {
                                text = text.substr(cscc.currentParser.propertyName.length);
                                text += ": ";
                                textOffset = 2;
                                select.insertAtCursor(text);
                                if (evt.stop)
                                    evt.stop();

                                // when picking the attribute name, reupdate intellisense for possible attribute values
                                setTimeout(function() {
                                    var l = cscc.getCursorInfo();
                                    text = l.text;
                                    var curPos = l.pos;

                                    for (var c in {
                                        "{": 1,
                                        ";": 1
                                    }) {
                                        startPos = text.lastIndexOf(c);
                                        if (startPos != -1) {
                                            text = text.substr(startPos + 1);
                                            curPos -= (startPos + 1);
                                        }
                                    }

                                    var declarationPart = text.replace(/^\s*(.*)$/gi, "$1");
                                    indentationLength = text.length - declarationPart.length;
                                    curPos -= indentationLength;
                                    text = declarationPart;

                                    var parser = new csccParseCss(text, l.pos - startPos);
                                    cscc.update(l, parser, evt);
                                }, 0);
                            }
                            if (cscc.visibleItemsType == 2) {
                                text = text.substr(cscc.currentParser.propertyValue.length);
                                text += ";";
                                textOffset = 1;
                                select.insertAtCursor(text);
                                if (evt.stop)
                                    evt.stop();
                            }
                            break;
                        case "xml":


                            if (cscc.visibleItemsType == 1) {
                                text = text.toLowerCase();
                                text = text.substr(cscc.currentParser.tagName.length);
                                console.log('insert visibleItemsType 1');
                                // select.insertAtCursor(text);
                                select.replaceSelection(text);




                                select.setCursor(cscc.editor.getCursor().line, cscc.editor.getCursor().ch + text.length + 1);

                                if (evt.stop)
                                    evt.stop();
                            }


                            if (cscc.visibleItemsType == 2) {

                                console.log('insert visibleItemsType 2');

                                text = text.substr(cscc.currentParser.attributeName.length);
                                text = text.toLowerCase();
                                text += "=\"\"";
                                //select.insertAtCursor(text);
                                select.replaceSelection(text);
                                select.setCursor(cscc.editor.getCursor().line, cscc.editor.getCursor().ch + text.length - 1);


                                if (evt.stop)
                                    evt.stop();

                                // when picking the attribute name, reupdate intellisense for possible attribute values
                                setTimeout(function() {
                                    var l = cscc.getCursorInfo();
                                    var text = l.text.substr(0, l.pos);
                                    var startPos = text.lastIndexOf("<");
                                    text = text.substr(startPos + 1);
                                    text = text.toLowerCase();

                                    var parser = new csccParseXml(text, cscc.editor.getCursor().ch - startPos);
                                    cscc.update(l, parser, evt);
                                }, 0);
                            }
                            if (cscc.visibleItemsType == 3) {

                                console.log('insert visibleItemsType 3');

                                text = text.substr(cscc.currentParser.attributeValue.length);
                                select.replaceSelection(text);
                                select.setCursor(cscc.editor.getCursor().line, cscc.editor.getCursor().ch + text.length + 1);
                                /*
                                 select.setCursorPos(editor.container, {
                                 node: l.obj.line, 
                                 offset: l.pos + text.length + 1
                                 }); */
                                if (evt.stop)
                                    evt.stop();
                            }
                            break;
                    }

                    // offset cursor if "|" was present in a value
                    if (cursorOffset != 0) {
                        //var l = cscc.getCursorInfo();
                        select.setCursor(
                                cscc.editor.getCursor().line,
                                cscc.editor.getCursor().ch + (cursorOffset - textOffset + 1)
                                );
                    }
                }
            },
            isInCssDeclaration: function(cursorInfo) {

                return false;
                var parseEl = cscc.elAtCursor;
                if (parseEl == null || parseEl.previousSibling == null) {
                    parseEl = cursorInfo.obj.line;
                    parseEl = cscc.editor.getTokenAt(cursorInfo.obj);
                }
                var state = 0;
                while (parseEl) {
                    if (parseEl.tagName != "BR" && parseEl.nodeType != 3) {
                        var cn = parseEl.className;
                        if (!cn)
                            break;
                        if (cn != "whitespace") {
                            if (cn.indexOf("css-") != 0)
                                break;
                            else {
                                if (cn == "css-punctuation" && parseEl.innerHTML.indexOf("{") == 0) {
                                    state = 2;
                                    break;
                                }
                                if (cn == "css-punctuation" && parseEl.innerHTML.indexOf("}") == 0) {
                                    state = 1;
                                    break;
                                }
                            }
                        }
                    }
                    parseEl = parseEl.previousSibling;
                }
                return state == 2;
            }
        };
