/* 
 * DreamCMS 3.0
 * 
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE Version 2
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-2.0.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@dcms-studio.de so we can send you a copy immediately.
 * 
 * PHP Version 5.3.6
 * @copyright	Copyright (c) 2008-2013 Marcel Domke (http://www.dcms-studio.de)
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 */


ace.define('dcms_ace/mergeview', ['require', 'exports', 'module', 'ace/ace', 'ace/lib/dom', 'ace/lib/event', 'ace/range'], function (require, exports, module) {

    var dom = require("ace/lib/dom");
    var event = require("ace/lib/event");
    var Range = require("ace/range").Range;

    function Pos (line, ch) {
        if (!(this instanceof Pos))
            return new Pos(line, ch);
        this.line = line || 1;
        this.ch = ch || 0;
    }


    var Mergeview = function (editor, options) {
        if (editor.Mergeview)
            return;

        editor.Mergeview = this;

        this.sourceeditor = ace.sourceEditor;
        this.editor = editor;


        this.wrapper = ace.sourceEditor.jqWrapper;

        this.connectorDiv = $('<div id="' + this.sourceeditor.editorID + '-diff-connectors" class="ace-diff-connectors">');
        this.diffEditorContainer = $('<div id="' + this.sourceeditor.editorID + '-diff" class="ace-diff-editor">');


        this.wrapper.append(this.connectorDiv).append(this.diffEditorContainer);


        this.original = ace.edit(this.sourceeditor.editorID + '-diff');
        $('#' + this.sourceeditor.editorID + '-diff').css('font-size', this.sourceeditor.aceopts.fontsize);

        CodeMirror.Pos = Pos;

        this.original.getSession().setMode('ace/mode/' + this.sourceeditor.mode);
        this.original.setTheme('ace/theme/' + this.sourceeditor.theme);

        this.options = options;

        this.classes = (typeof options.type == 'undefined' || options.type == "left"
                ? {chunk: "ace-merge-l-chunk",
            start: "ace-merge-l-chunk-start",
            end: "ace-merge-l-chunk-end",
            insert: "ace-merge-l-inserted",
            del: "ace-merge-l-deleted",
            connect: "ace-merge-l-connect"}
        : {chunk: "ace-merge-r-chunk",
            start: "ace-merge-r-chunk-start",
            end: "ace-merge-r-chunk-end",
            insert: "ace-merge-r-inserted",
            del: "ace-merge-r-deleted",
            connect: "ace-merge-r-connect"});


        this.$init();


    };


    var dmp = new diff_match_patch();

    function getDiff (a, b) {
        var diff = dmp.diff_main(a, b);
        dmp.diff_cleanupSemantic(diff);
        // The library sometimes leaves in empty parts, which confuse the algorithm
        for (var i = 0; i < diff.length; ++i) {
            var part = diff[i];
            if (!part[1]) {
                diff.splice(i--, 1);
            } else if (i && diff[i - 1][0] == part[0]) {
                diff.splice(i--, 1);
                diff[i][1] += part[1];
            }
        }
        return diff;
    }

    function moveOver (pos, str, copy, other) {
        var out = copy ? Pos(pos.row, pos.column) : pos, at = 0;
        for (; ; ) {
            var nl = str.indexOf("\n", at);

            if (nl == -1)
                break;
            ++out.row;
            if (other)
                ++other.row;
            at = nl + 1;
        }
        out.column = (at ? 0 : out.column) + (str.length - at);
        if (other)
            other.column = (at ? 0 : other.column) + (str.length - at);
        return out;
    }

    function endOfLineClean (diff, i) {
        if (i == diff.length - 1)
            return true;
        var next = diff[i + 1][1];
        if (next.length == 1 || next.charCodeAt(0) != 10)
            return false;
        if (i == diff.length - 2)
            return true;
        next = diff[i + 2][1];
        return next.length > 1 && next.charCodeAt(0) == 10;
    }

    function startOfLineClean (diff, i) {
        if (i == 0)
            return true;
        var last = diff[i - 1][1];
        if (last.charCodeAt(last.length - 1) != 10)
            return false;
        if (i == 1)
            return true;
        last = diff[i - 2][1];
        return last.charCodeAt(last.length - 1) == 10;
    }

    function posMin (a, b) {
        return (a.row - b.row || a.column - b.column) < 0 ? a : b;
    }
    function posMax (a, b) {
        return (a.row - b.row || a.column - b.column) > 0 ? a : b;
    }
    function posEq (a, b) {
        return a.row == b.row && a.column == b.column;
    }


    function elt (tag, content, className, style) {
        var e = document.createElement(tag);
        if (className)
            e.className = className;
        if (style)
            e.style.cssText = style;
        if (typeof content == "string")
            e.appendChild(document.createTextNode(content));
        else if (content)
            for (var i = 0; i < content.length; ++i)
                e.appendChild(content[i]);
        return e;
    }



    (function () {

        this.range = new Range();
        this.diff = null;


        this.getScrollposTop = function (editor) {
            return editor.getSession().getScrollTop();
        };

        this.getViewPort = function (editor) {
            var pos = editor.getCursorPosition();
            var p = editor.documentToScreenPosition(pos.row, pos.column);

            var lastRow = editor.getScreenLastRowColumn(pos.row);

        };

        this.getLineHeight = function (editor) {

        };


        this.clipPos = function (editor, doc, pos) {
            if (pos.row < 0)
                return {
                    row: 0,
                    column: 0
                };
            if (pos.row >= doc.size)
                return {
                    row: doc.size - 1,
                    column: editor.session.getLine(doc.size - 1).text.length
                };
            var column = pos.column, linelen = editor.session.getLine(pos.row).text.length;
            if (column == null || column > linelen)
                return {
                    row: pos.row,
                    column: linelen
                };
            else if (column < 0)
                return {
                    row: pos.row,
                    column: 0
                };
            else
                return pos;
        };





        this.markText = function (editor, line, className) {

        };

        this.addLineClass = function (editor, line, toline, className) {


            if (typeof toline !== 'number')
            {
                toline = line;
            }

            var range = new Range(line, 0, toline, Infinity);
            range.id = editor.getSession().addMarker(range, className, "fullLine", false);

            return range;
        };

        this.clearMarks = function (editor, markedRanges) {
            for (var i = 0; i < markedRanges.length; ++i) {
                var range = markedRanges[i];
                editor.getSession().removeMarker(range.id);
            }
            arr.length = 0;
        };

        this.markChanges = function (editor, diff, type, marks, from, to, classes, dv) {
            var self = this, pos = Pos(0, 0);
            var top = Pos(from, 0), bot = editor.clipPos(Pos(to - 1));
            var cls = type == DIFF_DELETE ? classes.del : classes.insert;

            function markChunk (start, end) {
                var bfrom = Math.max(from, start), bto = Math.min(to, end);

                for (var i = bfrom; i < bto; ++i) {
                    var range = self.addLineClass(editor, i, null, classes.chunk);

                    if (i == start)
                    {
                        range = self.addLineClass(editor, i, start, classes.start);
                    }
                    if (i == end - 1)
                    {
                        range = self.addLineClass(editor, i, end, classes.end);
                    }

                    marks.push(range);
                }
                // When the chunk is empty, make sure a horizontal line shows up
                if (start == end && bfrom == end && bto == end) {
                    if (bfrom)
                        marks.push(self.addLineClass(editor, bfrom - 1, classes.end));
                    else
                        marks.push(self.addLineClass(editor, bfrom, classes.start));
                }
            }



            var chunkStart = 0;
            for (var i = 0; i < diff.length; ++i) {
                var part = diff[i], tp = part[0], str = part[1];
                if (tp == DIFF_EQUAL) {
                    var cleanFrom = pos.line + (startOfLineClean(diff, i) ? 0 : 1);
                    moveOver(pos, str);
                    var cleanTo = pos.line + (endOfLineClean(diff, i) ? 1 : 0);
                    if (cleanTo > cleanFrom) {
                        if (i)
                            markChunk(chunkStart, cleanFrom);
                        chunkStart = cleanTo;
                    }
                } else {
                    if (tp == type) {
                        var end = moveOver(pos, str, true);
                        var a = posMax(top, pos), b = posMin(bot, end);
                        if (!posEq(a, b))
                            marks.push(editor.markText(a, b, {className: cls}));
                        pos = end;
                    }
                }
            }

            if (chunkStart <= pos.line)
            {
                markChunk(chunkStart, pos.line + 1);
            }
        };

        this.copyChunk = function (dv, chunk) {
            if (dv.diffOutOfDate)
                return;

            dv.orig.replaceRange(dv.edit.getRange(Pos(chunk.topEdit, 0), Pos(chunk.botEdit, 0)),
                    Pos(chunk.topOrig, 0), Pos(chunk.botOrig, 0));

            this.drawConnectors(dv);
        }

        this.updateMarks = function (editor, state, type) {
            var self = this;
            var vp = editor.getViewport();
            var changedLines = 0;
            editor.operation(function () {

                if (state.from == state.to || vp.from - state.to > 20 || state.from - vp.to > 20) {
                    self.clearMarks(editor, state.marked);
                    self.markChanges(editor, self.diff, type, state.marked, vp.from, vp.to, dv);
                    state.from = vp.from;
                    state.to = vp.to;
                } else {
                    if (vp.from < state.from) {
                        self.markChanges(editor, self.diff, type, state.marked, vp.from, state.from, dv);
                        state.from = vp.from;
                    }
                    if (vp.to > state.to) {
                        self.markChanges(editor, self.diff, type, state.marked, state.to, vp.to, dv);
                        state.to = vp.to;
                    }
                }

                changedLines++;
            });
        };



        this.buildGap = function (dv) {

            var counter = this.mergecounter = elt("div", null, "merge-counter");


            var lock = this.lockButton = elt("div", null, "ace-merge-scrolllock");
            lock.title = "Toggle locked scrolling";
            var lockWrap = elt("div", [lock], "ace-merge-scrolllock-wrap");
            CodeMirror.on(lock, "click", function () {
                setScrollLock(dv, !dv.lockScroll);
            });
            this.copyButtons = elt("div", null, "ace-merge-copybuttons-" + dv.type);
            CodeMirror.on(this.copyButtons, "click", function (e) {
                var node = e.target || e.srcElement;
                if (node.chunk)
                    copyChunk(dv, node.chunk);
            });

            var gapElts = [dv.copyButtons, lockWrap];
            var svg = document.createElementNS && document.createElementNS(svgNS, "svg");
            if (svg && !svg.createSVGRect)
                svg = null;
            this.svg = svg;
            if (this.svg)
                gapElts.push(this.svg);

            return this.gap = elt("div", gapElts, "ace-merge-gap");
        };




        this.drawConnectors = function () {
            if (this.svg) {
                clear(this.svg);
                var w = this.gap.offsetWidth;
                attrs(this.svg, "width", w, "height", this.gap.offsetHeight);
            }
            clear(this.copyButtons);
            this.changes = 0;

            var flip = this.type == "right";
            var vpEdit = dv.edit.getViewport(), vpOrig = dv.orig.getViewport();
            var sTopEdit = this.editor.getSession().getScrollTop() /* dv.edit.getScrollInfo().top*/, sTopOrig = this.original.getSession().getScrollTop()/* dv.orig.getScrollInfo().top*/;
            var count = 0;

            iterateChunks(this.diff, function (topOrig, botOrig, topEdit, botEdit) {
                if (topEdit > vpEdit.to || botEdit < vpEdit.from ||
                        topOrig > vpOrig.to || botOrig < vpOrig.from)
                    return;

                var topLpx = dv.orig.heightAtLine(topOrig, "local") - sTopOrig, top = topLpx;
                if (this.svg) {
                    var topRpx = dv.edit.heightAtLine(topEdit, "local") - sTopEdit;
                    if (flip) {
                        var tmp = topLpx;
                        topLpx = topRpx;
                        topRpx = tmp;
                    }
                    var botLpx = dv.orig.heightAtLine(botOrig, "local") - sTopOrig;
                    var botRpx = dv.edit.heightAtLine(botEdit, "local") - sTopEdit;

                    if (flip) {
                        var tmp = botLpx;
                        botLpx = botRpx;
                        botRpx = tmp;
                    }
                    var curveTop = " C " + w / 2 + " " + topRpx + " " + w / 2 + " " + topLpx + " " + (w + 2) + " " + topLpx;
                    var curveBot = " C " + w / 2 + " " + botLpx + " " + w / 2 + " " + botRpx + " -1 " + botRpx;
                    attrs(this.svg.appendChild(document.createElementNS(svgNS, "path")),
                            "d", "M -1 " + topRpx + curveTop + " L " + (w + 2) + " " + botLpx + curveBot + " z",
                            "class", this.classes.connect);
                }

                var copy = this.copyButtons.appendChild(elt("div", this.type == "left" ? "\u21dd" : "\u21dc",
                        "CodeMirror-merge-copy"));
                copy.title = "Revert chunk";



                // copy.chunk = {topEdit: topEdit, botEdit: botEdit, topOrig: topOrig, botOrig: botOrig};
                copy.chunk = {topEdit: topOrig, botEdit: botOrig, topOrig: topEdit, botOrig: botEdit};
                copy.style.top = top + "px";
            });
        };


        this.registerUpdate = function () {
            var self = this;
            var edit = {from: 0, to: 0, marked: []};
            var orig = {from: 0, to: 0, marked: []};

            this.editor.getSession().on('change', function () {
                self.diff = getDiff(self.original.getValue(), self.editor.getValue());
                self.updateMarks(self.editor, edit, DIFF_INSERT);
                self.updateMarks(self.original, orig, DIFF_DELETE);
                self.drawConnectors();
            });
        };

        this.$init = function () {


            this.registerUpdate();
        };

        this.destroy = function () {
            event.removeListener(this.editor.renderer.scroller, "mousemove", this.onMouseMove);
            event.removeListener(this.editor.renderer.content, "mouseout", this.onMouseOut);
            delete this.editor.Mergeview;
        };
    }).call(Mergeview.prototype);

    exports.Mergeview = Mergeview;

});