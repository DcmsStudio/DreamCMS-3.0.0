var weIsTextEditor = true;
var wizardOpen = false;
var editor = null;

var wizardHeight = {
	"open" : 305,
	"closed" : 140
}

function sizeEditor() {
	var h = window.innerHeight ? window.innerHeight : document.body.offsetHeight;
	var w = window.innerWidth ? window.innerWidth : document.body.offsetWidth;
	w = Math.max(w,350);
	var editorWidth = w - 37;

	var wizardOpen = weGetCookieVariable("but_weTMPLDocEdit") == "right";

	var editarea = document.getElementById("source");
	var wizardTable = document.getElementById("wizardTable");
	var tagAreaCol = document.getElementById("tagAreaCol");
	var tagSelectCol = document.getElementById("tagSelectCol");
	var spacerCol = document.getElementById("spacerCol");
	var tag_edit_area = document.getElementById("tag_edit_area");

	if (editarea) {
		editarea.style.width=editorWidth;
		if(editarea.nextSibling!=undefined && editarea.nextSibling.style)
			editarea.nextSibling.style.width=editorWidth;
	}

	if(window.editor && window.editor.frame) {
		editorWidth-=window.editor.frame.nextSibling.offsetWidth;
		document.getElementById("reindentButton").style.marginRight=window.editor.frame.nextSibling.offsetWidth-3;
		window.editor.frame.style.width = editorWidth;
	}

	if (h) { // h must be set (h!=0), if several documents are opened very fast -> editors are not loaded then => h = 0

		if (wizardTable != null) {
			
			var editorHeight = (h - (wizardOpen ? wizardHeight.closed : wizardHeight.open));
			
			if (editarea) {
				editarea.style.height= (h - (wizardOpen ? wizardHeight.closed : wizardHeight.open)) + "px";
				if(editarea.nextSibling!=undefined && editarea.nextSibling.style)
					editarea.nextSibling.style.height= (h - (wizardOpen ? wizardHeight.closed : wizardHeight.open)) + "px";
			}
			
			if(window.editor && window.editor.frame) {
				window.editor.frame.style.height = (h - (wizardOpen ? wizardHeight.closed : wizardHeight.open)) + "px";
			}
			
			if (document.weEditorApplet && typeof(document.weEditorApplet.setSize) != "undefined") {
				document.weEditorApplet.height = editorHeight;
				document.weEditorApplet.setSize(editorWidth,editorHeight);
			}
			
			
			wizardTable.style.width=editorWidth+"px";
			wizardTableButtons.style.width=editorWidth+"px";
			tagAreaCol.style.width=(editorWidth-300)+"px";
			tag_edit_area.style.width=(editorWidth-300)+"px";
			tagSelectCol.style.width = "250px";
			spacerCol.style.width = "50px";

		} else {
			if (editarea) {
				editarea.style.height = h - wizardHeight.closed;
				if(editarea.nextSibling!=undefined && editarea.nextSibling.style)
					editarea.nextSibling.style.height = h - wizardHeight.closed;
			}

			if(window.editor && window.editor.frame) {
				window.editor.frame.style.height = h - wizardHeight.closed;
			}

			if (document.weEditorApplet && typeof(document.weEditorApplet.setSize) != "undefined") {
				document.weEditorApplet.height = h - wizardHeight.closed;
				document.weEditorApplet.setSize(editorWidth,h - wizardHeight.closed);
			}
		}
	}
	window.scroll(0,0);

}

function initEditor() {
	sizeEditor();
	window.setTimeout('scrollToPosition();',50);
	document.getElementById("bodydiv").style.display="block";
}

function toggleTagWizard() {
	var w = window.innerWidth ? window.innerWidth : document.body.offsetWidth;
	w = Math.max(w,350);
	var editorWidth = w - 37;
	var h = window.innerHeight ? window.innerHeight : document.body.offsetHeight;

	var editarea = document.getElementById("source");
	editarea.style.height=h- (wizardOpen ? wizardHeight.closed : wizardHeight.open);
	if(editarea.nextSibling!=undefined && editarea.nextSibling.style)
		editarea.nextSibling.style.height=h- (wizardOpen ? wizardHeight.closed : wizardHeight.open);

	if(window.editor && window.editor.frame) {
		window.editor.frame.style.height = h- (wizardOpen ? wizardHeight.closed : wizardHeight.open);
	}		

}


function getScrollPosTop () {
	var elem = document.getElementById("source");
	if (elem) {
		return elem.scrollTop;
	}
	return 0;
	
}

function getScrollPosLeft () {
	var elem = document.getElementById("source");
	if (elem) {
		return elem.scrollLeft;
	}
	return 0;
}

function scrollToPosition () {
	var elem = document.getElementById("source");
	if (elem) {
		elem.scrollTop=parent.editorScrollPosTop;
		elem.scrollLeft=parent.editorScrollPosLeft;
	}
}


function wedoKeyDown(ta,keycode){

	if (keycode == 9) { // TAB
		if (ta.setSelectionRange) {
			var selectionStart = ta.selectionStart;
			var selectionEnd = ta.selectionEnd;
			ta.value = ta.value.substring(0, selectionStart)
				  + "\t"
				  + ta.value.substring(selectionEnd);
			ta.focus();
			ta.setSelectionRange(selectionEnd+1, selectionEnd+1);
			ta.focus();
			return false;

		} else if (document.selection) {
			var selection = document.selection;
			var range = selection.createRange();
			range.text = "\t";
			return false;
		}
	}
		
	return true;
}
// ############ EDITOR PLUGIN ################

function setSource(source){
	document.forms['we_form'].elements['we_766eb50f0148719d1b276cab7b3eaa11_txt[data]'].value=source;
	// for Applet
	setCode(source);
}

function getSource(){
	if (document.weEditorApplet && typeof(document.weEditorApplet.getCode) != "undefined") {
		return document.weEditorApplet.getCode();
	} else {
		return document.forms['we_form'].elements['we_766eb50f0148719d1b276cab7b3eaa11_txt[data]'].value;
	}
}

function getCharset(){
	return "UTF-8";
}

// ############ CodeMirror Functions ################

function reindent() { // reindents code of CodeMirror
	if(editor.selection().length)
		editor.reindentSelection();
	else
		editor.reindent();
}

// <textarea id="editarea" style="width: 900px; height: 700px;" id="data" name="we_766eb50f0148719d1b276cab7b3eaa11_txt[data]" wrap="off"  onkeypress="return wedoKeyDown(this,event.keyCode);">
		

var XgetComputedStyle = function(el, s) { // cross browser getComputedStyle()
	var computedStyle;
	if(typeof el.currentStyle!="undefined") {
		computedStyle = el.currentStyle;
	}
	else {
		computedStyle = document.defaultView.getComputedStyle(el, null);
	}
	return computedStyle[s];
}

    
var CMoptions = { //these are the CodeMirror options
	tabMode: "spaces",
	height: "700",
	textWrapping:false,
	parserfile: ["parsexml.js", "parsecss.js", "tokenizejavascript.js", "parsejavascript.js", "../contrib/php/js/tokenizephp.js", "../contrib/php/js/parsephp.js", "../contrib/php/js/parsephphtmlmixed.js"],
	stylesheet: ["/webEdition/editors/template/CodeMirror/css/xmlcolors.css", "/webEdition/editors/template/CodeMirror/css/jscolors.css", "/webEdition/editors/template/CodeMirror/css/csscolors.css", "/webEdition/editors/template/CodeMirror/contrib/php/css/phpcolors.css", "/webEdition/editors/template/CodeMirror/contrib/webEdition/css/webEdition.css"],
	path: "/webEdition/editors/template/CodeMirror/js/",
	autoMatchParens: false,
	cursorActivity: cscc.cursorActivity,
	undoDelay: 200,
	lineNumbers: true,
	initCallback: function() {
		window.setTimeout(function(){ //without timeout this will raise an exception in firefox
		/*
			if (document.addEventListener) {
				editor.frame.contentWindow.document.addEventListener( "keydown", top.dealWithKeyboardShortCut, true );
			} else if(document.attachEvent) {
				editor.frame.contentWindow.document.attachEvent( "onkeydown", top.dealWithKeyboardShortCut );
			}
			*/
			editor.focus();
			editor.frame.style.border="1px solid gray";
			
			var editorFrame=editor.frame.contentWindow.document.getElementsByTagName("body")[0];
			var originalTextArea=document.getElementById("source");
			var lineNumbers=editor.frame.nextSibling
			
			//we adapt font styles from original <textarea> to CodeMirror
			editorFrame.style.fontSize="13px";
			editorFrame.style.fontFamily="Monospace, Arial";
			editorFrame.style.lineHeight="120%";
			editorFrame.style.marginTop="5px";
			
			//we adapt font styles from orignal <textarea> to the line numbers of CodeMirror.
			lineNumbers.style.fontSize="13px";
			lineNumbers.style.fontFamily="Monospace, Arial";
			lineNumbers.style.lineHeight="120%";

			sizeEditor();
			var showDescription=function(e) { //this function will display a tooltip with the tags description. will be caled by onmousemove
				try{
					if(typeof(cscc)=="undefined")
						return;
					var wrap = cscc.editor.wrapping;
					var doc = wrap.ownerDocument;
					var tagDescriptionDiv = doc.getElementById("tagDescriptionDiv");
					if(!tagDescriptionDiv) { //if our div is not yet in the DOM, we create it
						var tagDescriptionDiv = doc.createElement("div");
						tagDescriptionDiv.setAttribute("id", "tagDescriptionDiv");
						if(tagDescriptionDiv.addEventListener) {
							tagDescriptionDiv.addEventListener("mouseover", hideDescription, false);
						}
						else {
							tagDescriptionDiv.attachEvent("onmouseover", hideDescription);
						}
						wrap.appendChild(tagDescriptionDiv);
					}
					if(top.currentHoveredTag===undefined) { //no tag is currently hoverd -> hide description
						hideDescription();
						return;
					}
					var tag=top.currentHoveredTag.innerHTML.replace(/\s/,"").replace(/&nbsp;/,"");
					if(top.we_tags[tag]===undefined) { //unkown tag -> hide description
						hideDescription();
						return;
					}
					//at this point we have a a description for our currently hovered tag. so we calculate of the mouse and display it
					tagDescriptionDiv.innerHTML=top.we_tags[tag].desc;
					x = (e.pageX ? e.pageX : window.event.x) + tagDescriptionDiv.scrollLeft - editor.frame.contentWindow.document.body.scrollLeft;
					y = (e.pageY ? e.pageY : window.event.y) + tagDescriptionDiv.scrollTop - editor.frame.contentWindow.document.body.scrollTop;

					if(x>0 && y>0) {
						if(window.innerWidth-x<468) {
							x+=(window.innerWidth-(e.pageX ? e.pageX : window.event.x)-468);
						}
						tagDescriptionDiv.style.left = (x + 25) + "px";
						tagDescriptionDiv.style.top   = (y + 15) + "px";
					}
					tagDescriptionDiv.style.display="block";
				}catch(e){};											
			};

			if(typeof(cscc) != "undefined" && typeof(cscc) != "false") { //tag completion is beeing used
				var hideCscc=function() {
					cscc.hide();
				}
				if(window.addEventListener) {
					editor.frame.contentWindow.document.addEventListener("mousemove", showDescription, false);
					editor.frame.contentWindow.document.addEventListener("click", hideCscc, false);
				}
				else {
					editor.frame.contentWindow.document.attachEvent("onmousemove", showDescription);
					editor.frame.contentWindow.document.attachEvent("onclick", hideCscc);
				}
			}
		},500)
	},
	onChange: function(){
		updateEditor();
	}
	
	,activeTokens: function(span, token) {
		if(token.style == "xml-tagname" && !span.className.match(/we-tagname/) && token.content.substring(0,3)=="we:" ) { //this is our hook to colorize we:tags
			span.className += " we-tagname";
			var clickTag=function(){
				hideDescription();
				we_cmd("open_tagreference",token.content.substring(3));
			};
			var mouseOverTag=function() {
				top.currentHoveredTag=span;
			}
			var mouseOutTag=function() {
				top.currentHoveredTag=undefined;
			}
			if(window.addEventListener) {
				span.addEventListener("dblclick", clickTag, false);
				span.addEventListener("mouseover", mouseOverTag, false);
				span.addEventListener("mouseout", mouseOutTag, false);
			}
			else {
				span.attachEvent("ondblclick", clickTag);
				span.attachEvent("onmouseover", mouseOverTag);
				span.attachEvent("onmouseout", mouseOutTag);
			}
		}
	},
	cursorActivity: function(el) { //this is our hook for focusing on the right item inside the tag-generator 
		try {
			if(el===null || el.className==undefined)
				return;
			while(!el.className.match(/we-tagname/)) {
				if(el.innerHTML=="&gt;" || el.innerHTML=="&lt;" || el.innerHTML=="/&gt;")
					return;
				el=el.previousSibling;
			}
			var currentTag=el.innerHTML.substring(3).replace(/\s/,"");
			for(var i=0;i<document.getElementById("weTagGroupSelect").options.length;i++) {
				if(document.getElementById("weTagGroupSelect").options[i].value=="alltags") {
					document.getElementById("weTagGroupSelect").options[i].selected="selected";
					selectTagGroup("alltags");
					for(var j=0;i<document.getElementById("tagSelection").options.length;j++) {
						if(document.getElementById("tagSelection").options[j].value==currentTag) {
							document.getElementById("tagSelection").options[j].selected="selected";
							break;
						}
					}
					break;
				}
			}
		}catch(e){};
	}

};

var updateEditor=function(){ //this wil save content from CoeMirror to our original <textarea>.
	var currentTemplateCode=editor.getCode();
	if(window.orignalTemplateContent!=currentTemplateCode) {
		window.orignalTemplateContent=currentTemplateCode;
		document.getElementById("source").value=currentTemplateCode;
	}
}
window.orignalTemplateContent=document.getElementById("source").value; //this is our reference of the original content to compare with current content

cscc.init("source");
editor = cscc.editor;




function openTagWizardPrompt( _wrongTag ) {


	var _prompttext = "Name des we:tags eingeben: ";
	if ( _wrongTag ) {
		_prompttext = "Den Tag \"" + _wrongTag + "\" gibt es nicht\n\n" + _prompttext;
	}

	var _tagName = prompt(_prompttext);
	var _tagExists = false;

	if ( typeof(_tagName) == "string") {

		for ( i=0; i < tagGroups["alltags"].length && !_tagExists; i++ ) {
			if ( tagGroups["alltags"][i] == _tagName ) {
				_tagExists = true;

			}
		}

		if ( _tagExists ) {
			edit_wetag(_tagName, 1);

		} else {
			openTagWizardPrompt( _tagName );

		}
	}
}

function edit_wetag(tagname, insertAtCursor) {
	if (!insertAtCursor) {
		insertAtCursor = 0;
	}
	we_cmd("open_tag_wizzard", tagname, insertAtCursor);

}

function insertAtStart(tagText) {
	if (document.weEditorApplet && typeof(document.weEditorApplet.insertAtStart) != "undefined") {
		document.weEditorApplet.insertAtStart(tagText);
	} else if(window.editor && window.editor.frame) {
		window.editor.insertIntoLine(window.editor.firstLine(), 0, tagText + "\n");
	} else {
		document.we_form["we_766eb50f0148719d1b276cab7b3eaa11_txt[data]"].value = tagText + "\n" + document.we_form["we_766eb50f0148719d1b276cab7b3eaa11_txt[data]"].value;
	}
}

function insertAtEnd(tagText) {
	if (document.weEditorApplet && typeof(document.weEditorApplet.insertAtEnd) != "undefined") {
		document.weEditorApplet.insertAtEnd(tagText);
	} else if(window.editor && window.editor.frame) {
		window.editor.insertIntoLine(window.editor.lastLine(), "end", "\n" + tagText);
	} else {
		document.we_form["we_766eb50f0148719d1b276cab7b3eaa11_txt[data]"].value += "\n" + tagText;
	}
}

function addCursorPosition ( tagText ) {

	if (document.weEditorApplet && typeof(document.weEditorApplet.replaceSelection) != "undefined") {
		document.weEditorApplet.replaceSelection(tagText);
	} else if(window.editor && window.editor.frame) {
		window.editor.replaceSelection(tagText);
	} else {

		var weForm = document.we_form["we_766eb50f0148719d1b276cab7b3eaa11_txt[data]"];
		if(document.selection)
		{
			weForm.focus();
			document.selection.createRange().text=tagText;
			document.selection.createRange().select();
		}
		else if (weForm.selectionStart || weForm.selectionStart == "0")
		{
			intStart = weForm.selectionStart;
			intEnd = weForm.selectionEnd;
			weForm.value = (weForm.value).substring(0, intStart) + tagText + (weForm.value).substring(intEnd, weForm.value.length);
			window.setTimeout("scrollToPosition();",50);
			weForm.focus();
			weForm.selectionStart = eval(intStart+tagText.length);
			weForm.selectionEnd = eval(intStart+tagText.length);
		}
		else
		{
			weForm.value += tagText;
		}
	}
}

function selectTagGroup(groupname) {

	if(groupname == "snippet_custom") {
		document.getElementById('codesnippet_standard').style.display = 'none';
		document.getElementById('tagSelection').style.display = 'none';
		document.getElementById('codesnippet_custom').style.display = 'block';

	} else if(groupname == "snippet_standard") {
		document.getElementById('codesnippet_custom').style.display = 'none';
		document.getElementById('tagSelection').style.display = 'none';
		document.getElementById('codesnippet_standard').style.display = 'block';

	} else if (groupname != "-1") {
		document.getElementById('codesnippet_custom').style.display = 'none';
		document.getElementById('codesnippet_standard').style.display = 'none';
		document.getElementById('tagSelection').style.display = 'block';
		elem = document.getElementById("tagSelection");

		for(var i=(elem.options.length-1); i>=0;i--) {
			elem.options[i] = null;
		}

		for (var i=0; i<tagGroups[groupname].length; i++) {
			elem.options[i] = new Option(tagGroups[groupname][i],tagGroups[groupname][i]);
		}
	}
}

tagGroups = new Array();
tagGroups['alltags'] = new Array('a', 'addDelNewsletterEmail', 'addDelShopItem', 'addPercent', 'answers', 'author', 'back', 'banner', 'bannerSelect', 'bannerSum', 'block', 'calculate', 'captcha', 'category', 'categorySelect', 'charset', 'checkForm', 'colorChooser', 'condition', 'conditionAdd', 'conditionAnd', 'conditionOr', 'content', 'controlElement', 'cookie', 'createShop', 'css', 'customer', 'date', 'dateSelect', 'delete', 'deleteShop', 'description', 'DID', 'docType', 'else', 'field', 'flashmovie', 'form', 'formfield', 'formmail', 'hidden', 'hidePages', 'href', 'icon', 'ifBack', 'ifbannerexists', 'ifCaptcha', 'ifCat', 'ifClient', 'ifConfirmFailed', 'ifCurrentDate', 'ifcustomerexists', 'ifDeleted', 'ifDoctype', 'ifDoubleOptIn', 'ifEditmode', 'ifEmailExists', 'ifEmailInvalid', 'ifEmailNotExists', 'ifEmpty', 'ifEqual', 'ifFemale', 'ifField', 'ifFieldEmpty', 'ifFieldNotEmpty', 'ifFound', 'ifHasChildren', 'ifHasCurrentEntry', 'ifHasEntries', 'ifHasShopVariants', 'ifHtmlMail', 'ifIsDomain', 'ifIsNotDomain', 'ifLastCol', 'ifLoginFailed', 'ifMailingListEmpty', 'ifMale', 'ifNew', 'ifnewsletterexists', 'ifNext', 'ifNoJavaScript', 'ifNotCaptcha', 'ifNotCat', 'ifNotDeleted', 'ifNotDoctype', 'ifNotEditmode', 'ifNotEmpty', 'ifNotEqual', 'ifNotField', 'ifNotFound', 'ifNotHasChildren', 'ifNotHasCurrentEntry', 'ifNotHasEntries', 'ifNotHasShopVariants', 'ifNotHtmlMail', 'ifNotNew', 'ifNotObject', 'ifNotObjectLanguage', 'ifNotPageLanguage', 'ifNotPosition', 'ifNotRegisteredUser', 'ifNotReturnPage', 'ifNotSearch', 'ifNotSeeMode', 'ifNotSelf', 'ifNotSendMail', 'ifNotSidebar', 'ifNotSubscribe', 'ifNotTemplate', 'ifNotTop', 'ifNotUnsubscribe', 'ifNotVar', 'ifNotVarSet', 'ifNotVote', 'ifNotVoteActive', 'ifNotVoteIsRequired', 'ifNotVotingField', 'ifNotWebEdition', 'ifNotWorkspace', 'ifNotWritten', 'ifObject', 'ifObjectLanguage', 'ifobjektexists', 'ifPageLanguage', 'ifPosition', 'ifRegisteredUser', 'ifRegisteredUserCanChange', 'ifReturnPage', 'ifSearch', 'ifSeeMode', 'ifSelf', 'ifSendMail', 'ifShopEmpty', 'ifshopexists', 'ifShopNotEmpty', 'ifShopPayVat', 'ifShopVat', 'ifSidebar', 'ifSubscribe', 'ifTemplate', 'ifTop', 'ifUnsubscribe', 'ifUserInputEmpty', 'ifUserInputNotEmpty', 'ifVar', 'ifVarEmpty', 'ifVarNotEmpty', 'ifVarSet', 'ifVote', 'ifVoteActive', 'ifVoteIsRequired', 'ifvotingexists', 'ifVotingField', 'ifVotingFieldEmpty', 'ifVotingFieldNotEmpty', 'ifWebEdition', 'ifWorkspace', 'ifWritten', 'img', 'include', 'input', 'js', 'keywords', 'link', 'linklist', 'linkToSeeMode', 'list', 'listdir', 'listview', 'listviewEnd', 'listviewPageNr', 'listviewPages', 'listviewRows', 'listviewStart', 'makeMail', 'master', 'metadata', 'navigation', 'navigationEntries', 'navigationEntry', 'navigationField', 'navigationWrite', 'newsletterConfirmLink', 'newsletterField', 'newsletterSalutation', 'newsletterUnsubscribeLink', 'next', 'noCache', 'object', 'objectLanguage', 'pageLanguage', 'pagelogger', 'path', 'paypal', 'position', 'postlink', 'prelink', 'printVersion', 'processDateSelect', 'quicktime', 'registeredUser', 'registerSwitch', 'repeat', 'repeatShopItem', 'returnPage', 'saferpay', 'saveRegisteredUser', 'search', 'select', 'sendMail', 'sessionField', 'sessionLogout', 'sessionStart', 'setVar', 'shipping', 'shopField', 'shopVat', 'showShopItemNumber', 'sidebar', 'subscribe', 'sum', 'target', 'textarea', 'title', 'toolfactory', 'tr', 'unsubscribe', 'url', 'userInput', 'useShopVariant', 'var', 'voting', 'votingField', 'votingList', 'votingSelect', 'votingSession', 'write', 'writeShopData', 'writeVoting', 'xmlfeed', 'xmlnode');

tagGroups['newsletter'] = new Array('addDelNewsletterEmail', 'ifConfirmFailed', 'ifDoubleOptIn', 'ifEmailExists', 'ifEmailInvalid', 'ifEmailNotExists', 'ifFemale', 'ifHtmlMail', 'ifMailingListEmpty', 'ifMale', 'ifNotHtmlMail', 'ifNotSubscribe', 'ifNotUnsubscribe', 'ifSubscribe', 'ifUnsubscribe', 'newsletterConfirmLink', 'newsletterField', 'newsletterSalutation', 'newsletterUnsubscribeLink', 'subscribe', 'unsubscribe', 'ifnewsletterexists');

tagGroups['shop'] = new Array('addDelShopItem', 'addPercent', 'calculate', 'createShop', 'deleteShop', 'ifHasShopVariants', 'ifNotHasShopVariants', 'ifShopEmpty', 'ifShopNotEmpty', 'ifShopPayVat', 'ifShopVat', 'paypal', 'repeatShopItem', 'saferpay', 'shipping', 'shopField', 'shopVat', 'showShopItemNumber', 'sum', 'useShopVariant', 'writeShopData', 'ifshopexists');

tagGroups['voting'] = new Array('answers', 'cookie', 'ifNotVote', 'ifNotVoteIsRequired', 'ifNotVoteActive', 'ifNotVotingField', 'ifVote', 'ifVoteActive', 'ifVoteIsRequired', 'voting', 'votingField', 'ifVotingField', 'ifVotingFieldEmpty', 'ifVotingFieldNotEmpty', 'votingList', 'votingSelect', 'votingSession', 'writeVoting', 'ifvotingexists');

tagGroups['users'] = new Array('author');

tagGroups['banner'] = new Array('banner', 'bannerSelect', 'bannerSum', 'ifbannerexists');

tagGroups['object'] = new Array('condition', 'conditionAdd', 'conditionAnd', 'conditionOr', 'ifField', 'ifNotField', 'ifNotObject', 'ifNotObjectLanguage', 'ifObject', 'ifObjectLanguage', 'objectLanguage', 'object', 'ifobjektexists');

tagGroups['customer'] = new Array('customer', 'ifLoginFailed', 'ifNotRegisteredUser', 'ifRegisteredUser', 'ifRegisteredUserCanChange', 'registeredUser', 'registerSwitch', 'saveRegisteredUser', 'sessionField', 'sessionLogout', 'ifcustomerexists');

tagGroups['apptags'] = new Array('toolfactory');

tagGroups['input_tags'] = new Array('date', 'flashmovie', 'href', 'img', 'input', 'link', 'object', 'quicktime', 'select', 'textarea');

tagGroups['if_tags'] = new Array('ifBack', 'ifbannerexists', 'ifCaptcha', 'ifCat', 'ifNotCat', 'ifClient', 'ifConfirmFailed', 'ifCurrentDate', 'ifcustomerexists', 'ifDeleted', 'ifDoctype', 'ifDoubleOptIn', 'ifEditmode', 'ifEmailExists', 'ifEmailInvalid', 'ifEmailNotExists', 'ifEmpty', 'ifEqual', 'ifFemale', 'ifField', 'ifFieldEmpty', 'ifFieldNotEmpty', 'ifFound', 'ifHasChildren', 'ifHasCurrentEntry', 'ifHasEntries', 'ifHasShopVariants', 'ifHtmlMail', 'ifIsDomain', 'ifIsNotDomain', 'ifLastCol', 'ifLoginFailed', 'ifMailingListEmpty', 'ifMale', 'ifNew', 'ifNext', 'ifNoJavaScript', 'ifNotCaptcha', 'ifNotDeleted', 'ifNotDoctype', 'ifNotEditmode', 'ifNotEmpty', 'ifNotEqual', 'ifNotField', 'ifNotFound', 'ifNotHasChildren', 'ifNotHasCurrentEntry', 'ifNotHasEntries', 'ifNotHasShopVariants', 'ifNotHtmlMail', 'ifNotNew', 'ifNotObject', 'ifNotObjectLanguage', 'ifNotPageLanguage', 'ifNotPosition', 'ifNotRegisteredUser', 'ifNotReturnPage', 'ifNotSearch', 'ifNotSeeMode', 'ifNotSelf', 'ifNotSendMail', 'ifNotSubscribe', 'ifNotTop', 'ifNotUnsubscribe', 'ifNotVar', 'ifNotVarSet', 'ifNotVote', 'ifNotVotingField', 'ifNotWebEdition', 'ifNotWorkspace', 'ifNotWritten', 'ifnewsletterexists', 'ifObject', 'ifObjectLanguage', 'ifobjektexists', 'ifPageLanguage', 'ifPosition', 'ifRegisteredUser', 'ifRegisteredUserCanChange', 'ifReturnPage', 'ifSearch', 'ifSeeMode', 'ifSendMail', 'ifShopEmpty', 'ifShopNotEmpty', 'ifShopPayVat', 'ifShopVat', 'ifshopexists', 'ifSubscribe', 'ifTemplate', 'ifTop', 'ifUnsubscribe', 'ifUserInputEmpty', 'ifUserInputNotEmpty', 'ifVar', 'ifVarEmpty', 'ifVarNotEmpty', 'ifVarSet', 'ifVote', 'ifVoteActive', 'ifvotingexists', 'ifVotingField', 'ifVotingFieldEmpty', 'ifVotingFieldNotEmpty', 'ifWebEdition', 'ifWorkspace', 'ifWritten');

tagGroups['navigation_tags'] = new Array('ifHasCurrentEntry', 'ifNotHasCurrentEntry', 'ifHasEntries', 'ifNotHasEntries', 'navigation', 'navigationEntries', 'navigationEntry', 'navigationField', 'navigationWrite');

function openTagWizWithReturn (Ereignis) {
	if (!Ereignis)
	Ereignis = window.event;
	if (Ereignis.which) {
	Tastencode = Ereignis.which;
	} else if (Ereignis.keyCode) {
	Tastencode = Ereignis.keyCode;
	}
	if (Tastencode==13) edit_wetag(document.getElementById("tagSelection").value);
	//return false;
}