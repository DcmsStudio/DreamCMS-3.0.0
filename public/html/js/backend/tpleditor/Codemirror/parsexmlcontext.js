var QuploAddon =
{
  customTags: { "page": true, "layout": true, "write": true, "var": true,
    "part": true, "for": true, "do": true, "what": true, "else": true,
    "text": true, "attr": true
  }
};

/* This file defines an XML parser, with a few kludges to make it
 * useable for HTML. autoSelfClosers defines a set of tag names that
 * are expected to not have a closing tag, and doNotIndent specifies
 * the tags inside of which no indentation should happen (see Config
 * object). These can be disabled by passing the editor an object like
 * {useHTMLKludges: false} as parserConfig option.
 */

var XMLParser = Editor.Parser = (function () {
  var Kludges = {
    autoSelfClosers: { "br": true, "img": true, "hr": true, "link": true, "input": true,
      "meta": true, "col": true, "frame": true, "base": true, "area": true
    },
    doNotIndent: { "pre": true, "!cdata": true }
  };
  
  if ( typeof top.dcms_selfclosetags != 'undefined' )
  {
  		var selfCloses = top.dcms_selfclosetags.split(',');
  		for (var i=0; i<selfCloses.length; ++i)
  		{
  			if (selfCloses[i]) Kludges.autoSelfClosers[selfCloses[i]] = true;
  		}
  
  }
  
  
  
  var NoKludges = { autoSelfClosers: {}, doNotIndent: { "!cdata": true} };
  var UseKludges = Kludges;
  var alignCDATA = false;
  
  
    var lastQuote = '';
    var lastQuote2 = '';
    var tempAttrib = '';
    var tempTagname = '';
  
  // Simple stateful tokenizer for XML documents. Returns a
  // MochiKit-style iterator, with a state property that contains a
  // function encapsulating the current state. See tokenize.js.
  var tokenizeXML = (function () {
    function inText(source, setState) {
      var ch = source.next();
      if (ch == "<") {
        if (source.equals("!")) {
          source.next();
          if (source.equals("[")) {
            if (source.lookAhead("[CDATA[", true)) {
              setState(inBlock("xml-cdata", "]]>"));
              return null;
            }
            else {
              return "xml-text";
            }
          }
          else if (source.lookAhead("--", true)) {
            setState(inBlock("xml-comment", "-->"));
            return null;
          }
          else if (source.lookAhead("DOCTYPE", true)) {
            source.nextWhileMatches(/[\w\._\-]/);
            setState(inBlock("xml-doctype", ">"));
            return "xml-doctype";
          }
          else {
            return "xml-text";
          }
        }
        else if (source.equals("?")) {
          source.next();
          source.nextWhileMatches(/[\w\._\-]/);
          setState(inBlock("xml-processing", "?>"));
          return "xml-processing";
        }
        else {

          if (source.equals("/")) source.next();
          setState(inTag);
          return "xml-punctuation";
        }
      }

      // @mod by dw2k
      else if (ch == "{")
      {
      
		if (source.lookAhead("$", true)) {
			//source.back();

          source.next();
          source.nextWhileMatches(/[^\}\{\n]/);
          setState(inBlock("cp-var", "}", true));
          return null;
          //return "cp-var";
      	}
		isFunc = false;
		var str = '';		
      	while (!source.endOfLine()) {
      	
      		c = source.next();
      		str += c;
      		if (str.match(/[a-zA-Z]+:$/))
      		{
      			break;
      		}
      		else if (c == " ")
      		{
      			isFunc = true;
      			break;
      		}
		}
		
		if (isFunc && str.match(/[a-zA-Z]/)  )
		{
			source.nextWhileMatches(/[^\}]/);
			nch = source.next();
			
      		if (nch == '}'){      		
      			return "cp-func";
      		}
      		else
      		{
      			return "xml-text";
      		}
		}
		
		source.nextWhileMatches(/[^&<\n\{\}]/);
      	return "xml-text";
      	
      }
      
      /*else if (ch == "&") {
      while (!source.endOfLine()) {
      if (source.next() == ";")
      break;
      }
      return "xml-entity";
      }*/
      else {

        source.nextWhileMatches(/[^&<\n\{]/);
        return "xml-text";
      }
    }

    function inTag(source, setState) {
      var ch = source.next();
      if (ch == ">") {
        setState(inText);
        return "xml-punctuation";
      }
      else if (/[?\/]/.test(ch) && source.equals(">")) {
        source.next();
        setState(inText);
        return "xml-punctuation";
      }
      else if (ch == "=") {
        return "xml-punctuation";
      }

      // @mod by dw2k
      else if (ch == "{")
      {
      
		if (source.lookAhead("$", true)) {
          source.next();
          source.nextWhileMatches(/[^\}\{\n]/);
          setState(inBlock("cp-var", "}"));
          return null;
      	}
		isFunc = false;
		var str = '';		
      	while (!source.endOfLine()) {
      	
      		c = source.next();
      		str += c;
      		if (str.match(/[a-zA-Z]+:$/))
      		{
      			break;
      		}
      		else if (c == " ")
      		{
      			isFunc = true;
      			break;
      		}
		}
		
		if (isFunc && str.match(/[a-zA-Z]/)  )
		{
			source.nextWhileMatches(/[^\}]/);
			nch = source.next();
			
      		if (nch == '}'){      		
      			return "cp-func";
      		}
      		else {
        		source.nextWhileMatches(/[^\s\u00a0=<>\"\'\/?]/);
        		return "xml-name";
      		}
      	
		}
      	else {
        	source.nextWhileMatches(/[^\s\u00a0=<>\"\'\/?]/);
        	return "xml-name";
      	}
      	
      }
      else if (/[\'\"]/.test(ch)) {
                if (!lastQuote) lastQuote = ch;
                if (lastQuote) lastQuote2 = ch;
      
        setState(inAttribute(ch));
        return null;
      }
      
      else {
        source.nextWhileMatches(/[^\s\u00a0=<>\"\'\/?]/);
        return "xml-name";
      }
    }

    function inAttribute(quote) {
      return function (source, setState) {
      
      	
      	isDCMS = false;
      	endQuote = false;
      	inVar = false;
      	tempAttrib = '';
        while (!source.endOfLine()) {
        	// @todo scan DCMS Functions and Variables in attributes 
        	// see inTag()
        	
                    var s = source.next();
                    tempAttrib += s;
                    
                    if ( s == quote ) {
                        endQuote = true;
                        break;
                    }
                    
                    
                    if ( s == '{' && !endQuote )
                    {
                    	inVar = true;
                    	nextCh = source.next();
                    	
                    	
                    	//alert(tempAttrib);
                    	
                    	
                        if ( s == '{' && nextCh == '$') {
                            source.back();
                            source.back();
                            setState(varBlock("cp-var", "}", quote));
                            break;
                        }
						
                        else if (  s == '{' && nextCh.match(/[a-zA-Z]/) ) {
                            source.back();
                            source.back();
                            setState(varBlock("cp-func", "}", quote));
                            break;
                        }
                        else
                        {
                        setState( inAttribute(quote) );
                        break;
                        }
                        
                    }
                    
          if ( endQuote ) {
         //   setState(inTag);
         //   break;
          }
        }
        
        
        
                if ( endQuote )
                {
                    endQuote = false;
                    lastQuote = null;
                    setState(inTag);
                    tempAttrib = '';
                }
        
       // endQuote = false;
      //  lastQuote = null;
        
        
        
        return "xml-attribute";
      };
    }
    
    
function __substr_count (haystack, needle, offset, length) {
    // http://kevin.vanzonneveld.net
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Onno Marsman
    // *     example 1: substr_count('Kevin van Zonneveld', 'e');
    // *     returns 1: 3
    // *     example 2: substr_count('Kevin van Zonneveld', 'K', 1);
    // *     returns 2: 0
    // *     example 3: substr_count('Kevin van Zonneveld', 'Z', 0, 10);
    // *     returns 3: false
    var pos = 0,
        cnt = 0;

    haystack += '';
    needle += '';
    if (isNaN(offset)) {
        offset = 0;
    }
    if (isNaN(length)) {
        length = 0;
    }
    offset--;

    while ((offset = haystack.indexOf(needle, offset + 1)) != -1) {
        if (length > 0 && (offset + needle.length) > length) {
            return false;
        } else {
            cnt++;
        }
    }

    return cnt;
}
    
    
    
    
        function varBlock(style, terminator, quote) {
            return function(source, setState) {
            	var str = '';
            	foundEndpoint = false;
                while (!source.endOfLine()) {
                	ch = source.next();
                	str += ch;

                	if ( str.length > 1 && ch == '{' && __substr_count(str, '{') != 1 )
                	{
                		// is broken Var
                		setState( inAttribute(quote) );                		
                		return 'xml-error';
                		break;
                	}
                	else
                	{
                	
                	
                    if (ch == terminator /* && 
                    	str.match(/\{\w\s[^\{\}]+)\}/) || str.match(/\{\$[a-zA-Z\.]+\}/)
                    	*/
                    	) { //alert(str);
                        if (quote) setState( inAttribute(quote) );
                        //else inText(source, setState);
                        
                        foundEndpoint = true;
                        break;
                    }
                    
                    }
                }
				
                if (foundEndpoint != false) return style;
                
                return 'xml-error';
            };
        }
    
    

    function inBlock(style, terminator, isDCMS ) {
      return function (source, setState) {
      
      	var str = '';
      	error = '';
        while (!source.endOfLine()) {
        
          if (source.lookAhead(terminator, true)) {
            setState(inText);
            break;
          }
			source.next();
        }
        return style;
      };
    }

    return function (source, startState) {
      return tokenizer(source, startState || inText);
    };
  })();

  // The parser. The structure of this function largely follows that of
  // parseJavaScript in parsejavascript.js (there is actually a bit more
  // shared code than I'd like), but it is quite a bit simpler.
  function parseXML(source) {
    var tokens = tokenizeXML(source), token;
    var cc = [base];
    var tokenNr = 0, indented = 0;
    var currentTag = null, currentAttr = null, currentAttrs = {}, context = null;
    var consume;

    function push(fs) {
      for (var i = fs.length - 1; i >= 0; i--)
        cc.push(fs[i]);
    }
    function cont() {
      push(arguments);
      consume = true;
    }
    function pass() {
      push(arguments);
      consume = false;
    }

    function markErr() {
      if (token.style != 'xml-attribute' ) { token.style += " xml-error"; }
    }
    function expect(text) {
      return function (style, content) {
        if (content == text || content.match(/\{[a-zA-Z]\s.*\}/) || content.match(/\{\$[a-zA-Z\.]*\}/)) cont();
        else { markErr(); cont(arguments.callee); }
      };
    }

    function pushContext(tagname, startOfLine, attr) {
      var noIndent = UseKludges.doNotIndent.hasOwnProperty(tagname) || (context && context.noIndent);
      context = { prev: context, name: tagname, attributes: attr, indent: indented, startOfLine: startOfLine, noIndent: noIndent };
    }
    function popContext() {
      context = context.prev;
    }
    function computeIndentation(baseContext) {
      return function (nextChars, current) {
        var context = baseContext;
        if (context && context.noIndent)
          return current;
        if (alignCDATA && /<!\[CDATA\[/.test(nextChars))
          return 0;
        if (context && /^<\//.test(nextChars))
          context = context.prev;
        while (context && !context.startOfLine)
          context = context.prev;
        if (context)
          return context.indent + indentUnit;
        else {
          return current; // was 0 (handcraft)
        }
      };
    }

    function base() {
      return pass(element, base);
    }
    
    var harmlessTokens = { "xml-text": true, "xml-entity": true, "xml-comment": true, "xml-processing": true, "xml-doctype": true,
    "cp-var": true, "cp-func": true };
    
    function element(style, content) {
      if (!context && style == "xml-text") {
        token.style += " quplo-comment";
      }

      if (content == "<") cont(tagname, attributes, endtag(tokenNr == 1));
      else if (content == "</") cont(closetagname, expect(">"));
      else if (!context || style == "xml-text" && (/\{[a-zA-Z]\s.*\}/.test(content) || /\{\$[a-zA-Z\.]*\}/.test(content) ) ){
      	cont();
      }
      
      else if (style == "xml-cdata") {
        if (!context || context.name != "!cdata") pushContext("!cdata");
        if (/\]\]>$/.test(content)) popContext();
        cont();
      }
      else if (harmlessTokens.hasOwnProperty(style)) cont();
      else { 
      	if ( !token.style.match(/cp-(func|var)/) && ( !/\{[a-zA-Z]\s.*\}/.test(content) && !/\{\$[a-zA-Z\.]*\}/.test(content) ))
      	{
      		markErr(); 
      	}
      cont(); }
    }
    function tagname(style, content) {
      if (style == "xml-name") {
        currentTag = content.toLowerCase();
        currentAttr = null;
        currentAttrs = {};
        token.style = "xml-tagname";
        if (QuploAddon.customTags.hasOwnProperty(currentTag))
          token.style += " xml-quplotag";
        cont();
      }
      else {
        currentTag = null;
        pass();
      }
    }
    function closetagname(style, content) {
      if (style == "xml-name") {
        token.style = "xml-tagname";
        if (QuploAddon.customTags.hasOwnProperty(context.name))
          token.style += " xml-quplotag";
        if (context && content.toLowerCase() == context.name) popContext();
      	else if (style.match(/cp-(func|var)/) && ( /\{[a-zA-Z]\s.*\}/.test(content) || /\{\$[a-zA-Z\.]*\}/.test(content) ))
      	{
      		 popContext();
      	}
        
        else markErr();
      }
      cont();
    }
    function endtag(startOfLine) {
      return function (style, content) {
      	// mod by dw2k
      	if (content != "/>" && !style.match(/cp-(func|var)/) &&  Kludges.autoSelfClosers.hasOwnProperty(currentTag)  )
      	{
      		markErr(); cont(arguments.callee); 
      	}
      	else
      	{
        	if (content == "/>" || (content == ">" && Kludges.autoSelfClosers.hasOwnProperty(currentTag))) cont();
        	else if (content == ">") { pushContext(currentTag, startOfLine, currentAttrs); cont(); }
        	else { if (!style.match(/cp-(func|var)/) ) { markErr(); } cont(arguments.callee); }
        }
      };
    }
    function attributes(style, content) {
      if (style == "xml-name") { token.style = "xml-attname"; currentAttr = content.toLowerCase(); cont(attribute, attributes); }
      else pass();
    }
    function attribute(style, content) {
      if (content == "=") cont(value);
      else if (content == ">" || content == "/>") pass(endtag);
      else pass();
    }
    function value(style, content) {
      if (style == "xml-attribute") {
        if (currentAttr) {
          currentAttrs[currentAttr] = content.replace(/^["']+|["']+$/g, "");
          currentAttr = null;
        }

        cont(value);
      }
      else pass();
    }

    return {
      indentation: function () { return indented; },

      next: function () {
        token = tokens.next();
        if (token.style == "whitespace" && tokenNr == 0)
          indented = token.value.length;
        else
          tokenNr++;
          
        if (token.content == "\n") {
          indented = tokenNr = 0;
          token.indentation = computeIndentation(context);
        }

        if (token.style == "whitespace" || token.type == "xml-comment" || token.type == "cp-var" || token.type == "cp-func")
          return token;

        while (true) {
          consume = false;
          cc.pop()(token.style, token.content);
          if (consume) return token;
        }
      },

      context: function () {
        var _tokenState = tokens.state, _context = context;
        return {
          context: _context,
          tokenState: _tokenState
        };
      },

      copy: function () {
        var _cc = cc.concat([]), _tokenState = tokens.state, _context = context;
        var parser = this;

        return function (input) {
          cc = _cc.concat([]);
          tokenNr = indented = 0;
          context = _context;
          tokens = tokenizeXML(input, _tokenState);
          return parser;
        };
      }
    };
  }

  return {
    make: parseXML,
    electricChars: "/",
    configure: function (config) {
      if (config.useHTMLKludges != null)
        UseKludges = config.useHTMLKludges ? Kludges : NoKludges;
      if (config.alignCDATA)
        alignCDATA = config.alignCDATA;
    }
  };
})();
