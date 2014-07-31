// the tagRangeFinder function is
//   Copyright (C) 2011 by Daniel Glazman <daniel@glazman.org>
// released under the MIT license (../../LICENSE) like the rest of CodeMirror


/*
 CodeMirror.tagRangeFinder = function(cm, start) {
 var nameStartChar = "A-Z_a-z\\u00C0-\\u00D6\\u00D8-\\u00F6\\u00F8-\\u02FF\\u0370-\\u037D\\u037F-\\u1FFF\\u200C-\\u200D\\u2070-\\u218F\\u2C00-\\u2FEF\\u3001-\\uD7FF\\uF900-\\uFDCF\\uFDF0-\\uFFFD";
 var nameChar = nameStartChar + "\-\:\.0-9\\u00B7\\u0300-\\u036F\\u203F-\\u2040";
 var xmlNAMERegExp = new RegExp("^[" + nameStartChar + "][" + nameChar + "]*");
 
 
 
 
 var lineText = cm.getLine(start.line);
 var found = false;
 var tag = null;
 var pos = start.ch;
 while (!found) {
 pos = lineText.indexOf("<", pos);
 if (-1 == pos) // no tag on line
 return;
 if (pos + 1 < lineText.length && lineText[pos + 1] == "/") { // closing tag
 pos++;
 continue;
 }
 // ok we seem to have a start tag
 if (!lineText.substr(pos + 1).match(xmlNAMERegExp)) { // not a tag name...
 pos++;
 continue;
 }
 var gtPos = lineText.indexOf(">", pos + 1);
 if (-1 == gtPos) { // end of start tag not in line
 var l = start.line + 1;
 var foundGt = false;
 var lastLine = cm.lineCount();
 while (l < lastLine && !foundGt) {
 var lt = cm.getLine(l);
 gtPos = lt.indexOf(">");
 if (-1 != gtPos) { // found a >
 foundGt = true;
 var slash = lt.lastIndexOf("/", gtPos);
 if (-1 != slash && slash < gtPos) {
 var str = lineText.substr(slash, gtPos - slash + 1);
 if (!str.match(/\/\s*\>/)) // yep, that's the end of empty tag
 return;
 }
 }
 l++;
 }
 found = true;
 }
 else {
 var slashPos = lineText.lastIndexOf("/", gtPos);
 if (-1 == slashPos) { // cannot be empty tag
 found = true;
 // don't continue
 }
 else { // empty tag?
 // check if really empty tag
 var str = lineText.substr(slashPos, gtPos - slashPos + 1);
 if (!str.match(/\/\s*\>/)) { // finally not empty
 found = true;
 // don't continue
 }
 }
 }
 if (found) {
 var subLine = lineText.substr(pos + 1);
 tag = subLine.match(xmlNAMERegExp);
 if (tag) {
 // we have an element name, wooohooo !
 tag = tag[0];
 // do we have the close tag on same line ???
 if (-1 != lineText.indexOf("</" + tag + ">", pos)) // yep
 {
 found = false;
 }
 // we don't, so we have a candidate...
 }
 else
 found = false;
 }
 if (!found)
 pos++;
 }
 
 if (found) {
 var startTag = "(\\<\\/" + tag + "\\>)|(\\<" + tag + "\\>)|(\\<" + tag + "\\s)|(\\<" + tag + "$)";
 var startTagRegExp = new RegExp(startTag);
 var endTag = "</" + tag + ">";
 var depth = 1;
 var l = start.line + 1;
 var lastLine = cm.lineCount();
 while (l < lastLine) {
 lineText = cm.getLine(l);
 var match = lineText.match(startTagRegExp);
 if (match) {
 for (var i = 0; i < match.length; i++) {
 if (match[i] == endTag)
 depth--;
 else
 depth++;
 if (!depth)
 return {from: {line: start.line, ch: gtPos + 1},
 to: {line: l, ch: match.index}};
 }
 }
 l++;
 }
 return;
 }
 };
 */

CodeMirror.tagRangeFinder = (function() {
    var nameStartChar = "A-Z_a-z\\u00C0-\\u00D6\\u00D8-\\u00F6\\u00F8-\\u02FF\\u0370-\\u037D\\u037F-\\u1FFF\\u200C-\\u200D\\u2070-\\u218F\\u2C00-\\u2FEF\\u3001-\\uD7FF\\uF900-\\uFDCF\\uFDF0-\\uFFFD";
    var nameChar = nameStartChar + "\-\:\.0-9\\u00B7\\u0300-\\u036F\\u203F-\\u2040";
    var xmlTagStart = new RegExp("<(/?)([" + nameStartChar + "][" + nameChar + "]*)", "g");

    return function(cm, start) {
        var line = start.line, ch = start.ch, lineText = cm.getLine(line);

        function nextLine() {
            if (line >= cm.lastLine())
                return;
            ch = 0;
            lineText = cm.getLine(++line);
            return true;
        }
        function toTagEnd() {
            for (; ; ) {
                var gt = lineText.indexOf(">", ch);
                if (gt == -1) {
                    if (nextLine())
                        continue;
                    else
                        return;
                }
                var lastSlash = lineText.lastIndexOf("/", gt);
                var selfClose = lastSlash > -1 && /^\s*$/.test(lineText.slice(lastSlash + 1, gt));
                ch = gt + 1;
                return selfClose ? "selfClose" : "regular";
            }
        }
        function toNextTag() {
            for (; ; ) {
                xmlTagStart.lastIndex = ch;
                var found = xmlTagStart.exec(lineText);
                if (!found) {
                    if (nextLine())
                        continue;
                    else
                        return;
                }
                ch = found.index + found[0].length;
                return found;
            }
        }

        var stack = [], startCh;
        for (; ; ) {
            var openTag = toNextTag(), end;
            if (!openTag || line != start.line || !(end = toTagEnd()))
                return;
            if (!openTag[1] && end != "selfClose") {
                stack.push(openTag[2]);
                startCh = ch;
                break;
            }
        }

        for (; ; ) {
            var next = toNextTag(), end, tagLine = line, tagCh = ch - (next ? next[0].length : 0);
            if (!next || !(end = toTagEnd()))
                return;
            if (end == "selfClose")
                continue;
            if (next[1]) { // closing tag
                for (var i = stack.length - 1; i >= 0; --i)
                    if (stack[i] == next[2]) {
                        stack.length = i;
                        break;
                    }

                if (!stack.length)
                    return {
                        from: CodeMirror.Pos(start.line, startCh),
                        to: CodeMirror.Pos(tagLine, tagCh)
                    };
            } else { // opening tag
                stack.push(next[2]);
            }
        }
    };
})();


CodeMirror.braceRangeFinder = function(cm, start) {
    var line = start.line, lineText = cm.getLine(line);
    var at = lineText.length, startChar, tokenType;
    var startCh;

    function findOpening(openCh) {
        for (var at = start.ch, pass = 0; ; ) {
            var found = at <= 0 ? -1 : lineText.lastIndexOf(openCh, at - 1);
            if (found == -1) {
                if (pass == 1)
                    break;
                pass = 1;
                at = lineText.length;
                continue;
            }
            if (pass == 1 && found < start.ch)
                break;

            var pos = CodeMirror.Pos(line, found + 1);
            tokenType = cm.getTokenTypeAt(pos);
            if (!/^(comment|string)/.test(tokenType))
                return found + 1;
            at = found - 1;
        }
    }

    var startToken = "{", endToken = "}", startCh = findOpening("{");
    if (startCh == null) {
        startToken = "[", endToken = "]";
        startCh = findOpening("[");
    }

    if (startCh == null)
    {
        return;
    }

    var count = 1, lastLine = cm.lastLine(), end, endCh, currentLoop = 0, maxLoop = 3000;
    outer: for (var i = line; i <= lastLine; ++i) {
        var text = cm.getLine(i), pos = i == line ? startCh : 0;
        for (; ; ) {
            var nextOpen = text.indexOf(startToken, pos), nextClose = text.indexOf(endToken, pos);
            if (nextOpen < 0)
                nextOpen = text.length;
            if (nextClose < 0)
                nextClose = text.length;
            pos = Math.min(nextOpen, nextClose);
            if (pos == text.length)
                break;
            if (cm.getTokenTypeAt(CodeMirror.Pos(i, pos + 1)) == tokenType) {
                if (pos == nextOpen)
                    ++count;
                else if (!--count) {
                    end = i;
                    endCh = pos;
                    break outer;
                }
            }

            ++pos; /*
             ++currentLoop;
             
             
             if (currentLoop >= maxLoop )
             {
             currentLoop = 0;
             break;
             } */
        }
    }

    if (end == null || line == end && endCh == startCh)
        return;


    return {from: CodeMirror.Pos(line, startCh), to: CodeMirror.Pos(end, endCh)};




    for (; ; ) {
        var found = lineText.lastIndexOf("{", at);
        if (found < start.ch)
            break;
        tokenType = cm.getTokenAt(CodeMirror.Pos(line, found + 1)).type;
        if (!/^(comment|string)/.test(tokenType)) {
            startChar = found;
            break;
        }
        at = found - 1;
    }
    
    if (startChar == null || lineText.lastIndexOf("}") > startChar)
        return;
    
    var count = 1, lastLine = cm.lineCount(), end, endCh;
    outer: for (var i = line + 1; i < lastLine; ++i) {
        var text = cm.getLine(i), pos = 0;
        for (; ; ) {
            var nextOpen = text.indexOf("{", pos), nextClose = text.indexOf("}", pos);
            
            if (nextOpen < 0)
                nextOpen = text.length;
            
            if (nextClose < 0)
                nextClose = text.length;
            
            pos = Math.min(nextOpen, nextClose);
            
            if (pos == text.length)
                break;
            
            if (cm.getTokenAt(CodeMirror.Pos(i, pos + 1)).type == tokenType) {
                if (pos == nextOpen)
                    ++count;
                else if (!--count) {
                    end = i;
                    endCh = pos;
                    break outer;
                }
            }
            
            ++pos;
        }
    }
    if (end == null || end == line + 1)
        return;
    return {from: CodeMirror.Pos(line, startChar + 1),
        to: CodeMirror.Pos(end, endCh)};
};

/*
 CodeMirror.braceRangeFinder = function(cm, start) {
 var line = start.line, lineText = cm.getLine(line);
 var at = lineText.length, startChar, tokenType;
 for (; ; ) {
 var found = lineText.lastIndexOf("{", at);
 if (found < start.ch)
 break;
 tokenType = cm.getTokenAt({line: line, ch: found}).type;
 if (!/^(comment|string)/.test(tokenType)) {
 startChar = found;
 break;
 }
 at = found - 1;
 }
 if (startChar == null || lineText.lastIndexOf("}") > startChar)
 return;
 var count = 1, lastLine = cm.lineCount(), end, endCh;
 outer: for (var i = line + 1; i < lastLine; ++i) {
 var text = cm.getLine(i), pos = 0;
 for (; ; ) {
 var nextOpen = text.indexOf("{", pos), nextClose = text.indexOf("}", pos);
 if (nextOpen < 0)
 nextOpen = text.length;
 if (nextClose < 0)
 nextClose = text.length;
 pos = Math.min(nextOpen, nextClose);
 if (pos == text.length)
 break;
 if (cm.getTokenAt({line: i, ch: pos + 1}).type == tokenType) {
 if (pos == nextOpen)
 ++count;
 else if (!--count) {
 end = i;
 endCh = pos;
 break outer;
 }
 }
 ++pos;
 }
 }
 if (end == null || end == line + 1)
 return;
 return {from: {line: line, ch: startChar + 1},
 to: {line: end, ch: endCh}};
 };
 */
CodeMirror.indentRangeFinder = function(cm, start) {
    var tabSize = cm.getOption("tabSize"), firstLine = cm.getLine(start.line);
    var myIndent = CodeMirror.countColumn(firstLine, null, tabSize);
    for (var i = start.line + 1, end = cm.lineCount(); i < end; ++i) {
        var curLine = cm.getLine(i);
        if (CodeMirror.countColumn(curLine, null, tabSize) < myIndent &&
                CodeMirror.countColumn(cm.getLine(i - 1), null, tabSize) > myIndent)
            return {from: {line: start.line, ch: firstLine.length},
                to: {line: i, ch: curLine.length}};
    }
};
/*
 CodeMirror.newFoldFunction = function(rangeFinder, widget) {
 if (widget == null) widget = "\u2194";
 if (typeof widget == "string") {
 var text = document.createTextNode(widget);
 widget = document.createElement("span");
 widget.appendChild(text);
 widget.className = "CodeMirror-foldmarker";
 }
 
 return function(cm, pos) {
 if (typeof pos == "number") pos = {line: pos, ch: 0};
 var range = rangeFinder(cm, pos);
 if (!range) return true;
 
 var present = cm.findMarksAt(range.from), cleared = 0;
 for (var i = 0; i < present.length; ++i) {
 if (present[i].__isFold) {
 ++cleared;
 present[i].clear();
 }
 }
 if (cleared) return true;
 
 var myWidget = widget.cloneNode(true);
 
 
 CodeMirror.on(myWidget, "mousedown", function() {
 myRange.clear();
 });
 
 var myRange = cm.markText(range.from, range.to, {
 replacedWith: myWidget,
 clearOnEnter: true,
 __isFold: true
 });
 
 
 
 };
 };
 */

CodeMirror.newFoldFunction = function(rangeFinder, widget) {
    if (widget == null)
        widget = "\u2194";
    if (typeof widget == "string") {
        var text = document.createTextNode(widget);
        widget = document.createElement("span");
        widget.appendChild(text);
        widget.className = "CodeMirror-foldmarker";
    }

    return function(cm, pos) {
        if (typeof pos == "number")
            pos = CodeMirror.Pos(pos, 0);
        var range = rangeFinder(cm, pos);
        if (!range)
            return true;

        var folding = true;
        if (range.from.ch === range.to.ch && range.from.line == range.to.line)
        {
            return true;
        }


        var present = cm.findMarksAt(range.from), cleared = 0;
        for (var i = 0; i < present.length; ++i) {
            if (present[i].__isFold) {
                ++cleared;
                present[i].clear();
            }
        }
        if (cleared)
            return true;

        var myWidget = widget.cloneNode(true);
        CodeMirror.on(myWidget, "mousedown", function() {
            myRange.clear();
        });

        var myRange = cm.markText(range.from, range.to, {
            replacedWith: myWidget,
            clearOnEnter: true,
            __isFold: folding
        });
    };
};
