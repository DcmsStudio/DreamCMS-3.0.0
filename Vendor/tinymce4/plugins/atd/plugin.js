/* global tinymce */
/*
 * TinyMCE Writing Improvement Tool Plugin
 * Author: Raphael Mudge (raffi@automattic.com)
 *
 * Updated for TinyMCE 4.0
 *
 * http://www.afterthedeadline.com
 *
 * Distributed under the LGPL
 *
 * Derived from:
 *	$Id: editor_plugin_src.js 425 2007-11-21 15:17:39Z spocke $
 *
 *	@author Moxiecode
 *	@copyright Copyright (C) 2004-2008, Moxiecode Systems AB, All rights reserved.
 *
 *	Moxiecode Spell Checker plugin released under the LGPL with TinyMCE
 */

/**
 * This class logic for filtering text and matching words.
 *
 * @class tinymce.spellcheckerplugin.TextFilter
 * @private
 */

(function ( exports, undefined )
{
	"use strict";

	var modules = {};

	function require( ids, callback )
	{
		var module, defs = [];

		for ( var i = 0; i < ids.length; ++i ) {
			module = modules[ids[i]] || resolve( ids[i] );
			if ( !module ) {
				throw 'module definition dependecy not found: ' + ids[i];
			}

			defs.push( module );
		}

		callback.apply( null, defs );
	}

	function define( id, dependencies, definition )
	{
		if ( typeof id !== 'string' ) {
			throw 'invalid module definition, module id must be defined and be a string';
		}

		if ( dependencies === undefined ) {
			throw 'invalid module definition, dependencies must be specified';
		}

		if ( definition === undefined ) {
			throw 'invalid module definition, definition function must be specified';
		}

		require( dependencies, function ()
		{
			modules[id] = definition.apply( null, arguments );
		} );
	}

	function defined( id )
	{
		return !!modules[id];
	}

	function resolve( id )
	{
		var target = exports;
		var fragments = id.split( /[.\/]/ );

		for ( var fi = 0; fi < fragments.length; ++fi ) {
			if ( !target[fragments[fi]] ) {
				return;
			}

			target = target[fragments[fi]];
		}

		return target;
	}

	function expose( ids )
	{
		for ( var i = 0; i < ids.length; i++ ) {
			var target = exports;
			var id = ids[i];
			var fragments = id.split( /[.\/]/ );

			for ( var fi = 0; fi < fragments.length - 1; ++fi ) {
				if ( target[fragments[fi]] === undefined ) {
					target[fragments[fi]] = {};
				}

				target = target[fragments[fi]];
			}

			target[fragments[fragments.length - 1]] = modules[id];
		}
	}



	define( "tinymce/spellcheckerplugin/DomTextMatcher", [], function ()
	{
		// Based on work developed by: James Padolsey http://james.padolsey.com
		// released under UNLICENSE that is compatible with LGPL
		// TODO: Handle contentEditable edgecase:
		// <p>text<span contentEditable="false">text<span contentEditable="true">text</span>text</span>text</p>
		return function ( node, editor )
		{
			var m, matches = [], text, dom = editor.dom;
			var blockElementsMap, hiddenTextElementsMap, shortEndedElementsMap;

			blockElementsMap = editor.schema.getBlockElements(); // H1-H6, P, TD etc
			hiddenTextElementsMap = editor.schema.getWhiteSpaceElements(); // TEXTAREA, PRE, STYLE, SCRIPT
			shortEndedElementsMap = editor.schema.getShortEndedElements(); // BR, IMG, INPUT

			function createMatch( m, data )
			{
				if ( !m[0] ) {
					throw 'findAndReplaceDOMText cannot handle zero-length matches';
				}

				return {
					start: m.index,
					end: m.index + m[0].length,
					text: m[0],
					data: data
				};
			}

			function getText( node )
			{
				var txt;

				if ( node.nodeType === 3 ) {
					return node.data;
				}

				if ( hiddenTextElementsMap[node.nodeName] && !blockElementsMap[node.nodeName] ) {
					return '';
				}

				txt = '';

				if ( blockElementsMap[node.nodeName] || shortEndedElementsMap[node.nodeName] ) {
					txt += '\n';
				}

				if ( (node = node.firstChild) ) {
					do {
						txt += getText( node );
					} while ( (node = node.nextSibling) );
				}

				return txt;
			}

			function stepThroughMatches( node, matches, replaceFn )
			{
				var startNode, endNode, startNodeIndex,
					endNodeIndex, innerNodes = [], atIndex = 0, curNode = node,
					matchLocation, matchIndex = 0;

				matches = matches.slice( 0 );
				matches.sort( function ( a, b )
				{
					return a.start - b.start;
				} );

				matchLocation = matches.shift();

				out: while ( true ) {
					if ( blockElementsMap[curNode.nodeName] || shortEndedElementsMap[curNode.nodeName] ) {
						atIndex++;
					}

					if ( curNode.nodeType === 3 ) {
						if ( !endNode && curNode.length + atIndex >= matchLocation.end ) {
							// We've found the ending
							endNode = curNode;
							endNodeIndex = matchLocation.end - atIndex;
						} else if ( startNode ) {
							// Intersecting node
							innerNodes.push( curNode );
						}

						if ( !startNode && curNode.length + atIndex > matchLocation.start ) {
							// We've found the match start
							startNode = curNode;
							startNodeIndex = matchLocation.start - atIndex;
						}

						atIndex += curNode.length;
					}

					if ( startNode && endNode ) {
						curNode = replaceFn( {
							startNode: startNode,
							startNodeIndex: startNodeIndex,
							endNode: endNode,
							endNodeIndex: endNodeIndex,
							innerNodes: innerNodes,
							match: matchLocation.text,
							matchIndex: matchIndex
						} );

						// replaceFn has to return the node that replaced the endNode
						// and then we step back so we can continue from the end of the
						// match:
						atIndex -= (endNode.length - endNodeIndex);
						startNode = null;
						endNode = null;
						innerNodes = [];
						matchLocation = matches.shift();
						matchIndex++;

						if ( !matchLocation ) {
							break; // no more matches
						}
					} else if ( (!hiddenTextElementsMap[curNode.nodeName] || blockElementsMap[curNode.nodeName]) && curNode.firstChild ) {
						// Move down
						curNode = curNode.firstChild;
						continue;
					} else if ( curNode.nextSibling ) {
						// Move forward:
						curNode = curNode.nextSibling;
						continue;
					}

					// Move forward or up:
					while ( true ) {
						if ( curNode.nextSibling ) {
							curNode = curNode.nextSibling;
							break;
						} else if ( curNode.parentNode !== node ) {
							curNode = curNode.parentNode;
						} else {
							break out;
						}
					}
				}
			}

			/**
			 * Generates the actual replaceFn which splits up text nodes
			 * and inserts the replacement element.
			 */
			function genReplacer( callback )
			{
				function makeReplacementNode( fill, matchIndex )
				{
					var match = matches[matchIndex];

					if ( !match.stencil ) {
						match.stencil = callback( match );
					}

					var clone = match.stencil.cloneNode( false );
					clone.setAttribute( 'data-mce-index', matchIndex );

					if ( fill ) {
						clone.appendChild( dom.doc.createTextNode( fill ) );
					}

					return clone;
				}

				return function ( range )
				{
					var before, after, parentNode, startNode = range.startNode,
						endNode = range.endNode, matchIndex = range.matchIndex,
						doc = dom.doc;

					if ( startNode === endNode ) {
						var node = startNode;

						parentNode = node.parentNode;
						if ( range.startNodeIndex > 0 ) {
							// Add "before" text node (before the match)
							before = doc.createTextNode( node.data.substring( 0, range.startNodeIndex ) );
							parentNode.insertBefore( before, node );
						}

						// Create the replacement node:
						var el = makeReplacementNode( range.match, matchIndex );
						parentNode.insertBefore( el, node );
						if ( range.endNodeIndex < node.length ) {
							// Add "after" text node (after the match)
							after = doc.createTextNode( node.data.substring( range.endNodeIndex ) );
							parentNode.insertBefore( after, node );
						}

						node.parentNode.removeChild( node );

						return el;
					} else {
						// Replace startNode -> [innerNodes...] -> endNode (in that order)
						before = doc.createTextNode( startNode.data.substring( 0, range.startNodeIndex ) );
						after = doc.createTextNode( endNode.data.substring( range.endNodeIndex ) );
						var elA = makeReplacementNode( startNode.data.substring( range.startNodeIndex ), matchIndex );
						var innerEls = [];

						for ( var i = 0, l = range.innerNodes.length; i < l; ++i ) {
							var innerNode = range.innerNodes[i];
							var innerEl = makeReplacementNode( innerNode.data, matchIndex );
							innerNode.parentNode.replaceChild( innerEl, innerNode );
							innerEls.push( innerEl );
						}

						var elB = makeReplacementNode( endNode.data.substring( 0, range.endNodeIndex ), matchIndex );

						parentNode = startNode.parentNode;
						parentNode.insertBefore( before, startNode );
						parentNode.insertBefore( elA, startNode );
						parentNode.removeChild( startNode );

						parentNode = endNode.parentNode;
						parentNode.insertBefore( elB, endNode );
						parentNode.insertBefore( after, endNode );
						parentNode.removeChild( endNode );

						return elB;
					}
				};
			}

			function unwrapElement( element )
			{
				var parentNode = element.parentNode;
				parentNode.insertBefore( element.firstChild, element );
				element.parentNode.removeChild( element );
			}

			function getWrappersByIndex( index )
			{
				var elements = node.getElementsByTagName( '*' ), wrappers = [];

				index = typeof(index) == "number" ? "" + index : null;

				for ( var i = 0; i < elements.length; i++ ) {
					var element = elements[i], dataIndex = element.getAttribute( 'data-mce-index' );

					if ( dataIndex !== null && dataIndex.length ) {
						if ( dataIndex === index || index === null ) {
							wrappers.push( element );
						}
					}
				}

				return wrappers;
			}

			/**
			 * Returns the index of a specific match object or -1 if it isn't found.
			 *
			 * @param  {Match} match Text match object.
			 * @return {Number} Index of match or -1 if it isn't found.
			 */
			function indexOf( match )
			{
				var i = matches.length;
				while ( i-- ) {
					if ( matches[i] === match ) {
						return i;
					}
				}

				return -1;
			}

			/**
			 * Filters the matches. If the callback returns true it stays if not it gets removed.
			 *
			 * @param {Function} callback Callback to execute for each match.
			 * @return {DomTextMatcher} Current DomTextMatcher instance.
			 */
			function filter( callback )
			{
				var filteredMatches = [];

				each( function ( match, i )
				{
					if ( callback( match, i ) ) {
						filteredMatches.push( match );
					}
				} );

				matches = filteredMatches;

				/*jshint validthis:true*/
				return this;
			}

			/**
			 * Executes the specified callback for each match.
			 *
			 * @param {Function} callback  Callback to execute for each match.
			 * @return {DomTextMatcher} Current DomTextMatcher instance.
			 */
			function each( callback )
			{
				for ( var i = 0, l = matches.length; i < l; i++ ) {
					if ( callback( matches[i], i ) === false ) {
						break;
					}
				}

				/*jshint validthis:true*/
				return this;
			}

			/**
			 * Wraps the current matches with nodes created by the specified callback.
			 * Multiple clones of these matches might occur on matches that are on multiple nodex.
			 *
			 * @param {Function} callback Callback to execute in order to create elements for matches.
			 * @return {DomTextMatcher} Current DomTextMatcher instance.
			 */
			function wrap( callback )
			{
				if ( matches.length ) {
					stepThroughMatches( node, matches, genReplacer( callback ) );
				}

				/*jshint validthis:true*/
				return this;
			}

			/**
			 * Finds the specified regexp and adds them to the matches collection.
			 *
			 * @param {RegExp} regex Global regexp to search the current node by.
			 * @param {Object} [data] Optional custom data element for the match.
			 * @return {DomTextMatcher} Current DomTextMatcher instance.
			 */
			function find( regex, data )
			{
				if ( text && regex.global ) {
					while ( (m = regex.exec( text )) ) {
						matches.push( createMatch( m, data ) );
					}
				}

				return this;
			}

			/**
			 * Unwraps the specified match object or all matches if unspecified.
			 *
			 * @param {Object} [match] Optional match object.
			 * @return {DomTextMatcher} Current DomTextMatcher instance.
			 */
			function unwrap( match )
			{
				var i, elements = getWrappersByIndex( match ? indexOf( match ) : null );

				i = elements.length;
				while ( i-- ) {
					unwrapElement( elements[i] );
				}

				return this;
			}

			/**
			 * Returns a match object by the specified DOM element.
			 *
			 * @param {DOMElement} element Element to return match object for.
			 * @return {Object} Match object for the specified element.
			 */
			function matchFromElement( element )
			{
				return matches[element.getAttribute( 'data-mce-index' )];
			}

			/**
			 * Returns a DOM element from the specified match element. This will be the first element if it's split
			 * on multiple nodes.
			 *
			 * @param {Object} match Match element to get first element of.
			 * @return {DOMElement} DOM element for the specified match object.
			 */
			function elementFromMatch( match )
			{
				return getWrappersByIndex( indexOf( match ) )[0];
			}

			/**
			 * Adds match the specified range for example a grammar line.
			 *
			 * @param {Number} start Start offset.
			 * @param {Number} length Length of the text.
			 * @param {Object} data Custom data object for match.
			 * @return {DomTextMatcher} Current DomTextMatcher instance.
			 */
			function add( start, length, data )
			{
				matches.push( {
					start: start,
					end: start + length,
					text: text.substr( start, length ),
					data: data
				} );

				return this;
			}

			/**
			 * Returns a DOM range for the specified match.
			 *
			 * @param  {Object} match Match object to get range for.
			 * @return {DOMRange} DOM Range for the specified match.
			 */
			function rangeFromMatch( match )
			{
				var wrappers = getWrappersByIndex( indexOf( match ) );

				var rng = editor.dom.createRng();
				rng.setStartBefore( wrappers[0] );
				rng.setEndAfter( wrappers[wrappers.length - 1] );

				return rng;
			}

			/**
			 * Replaces the specified match with the specified text.
			 *
			 * @param {Object} match Match object to replace.
			 * @param {String} text Text to replace the match with.
			 * @return {DOMRange} DOM range produced after the replace.
			 */
			function replace( match, text )
			{
				var rng = rangeFromMatch( match );

				rng.deleteContents();

				if ( text.length > 0 ) {
					rng.insertNode( editor.dom.doc.createTextNode( text ) );
				}

				return rng;
			}

			/**
			 * Resets the DomTextMatcher instance. This will remove any wrapped nodes and remove any matches.
			 *
			 * @return {[type]} [description]
			 */
			function reset()
			{
				matches.splice( 0, matches.length );
				unwrap();

				return this;
			}

			text = getText( node );

			return {
				text: text,
				matches: matches,
				each: each,
				filter: filter,
				reset: reset,
				matchFromElement: matchFromElement,
				elementFromMatch: elementFromMatch,
				find: find,
				add: add,
				wrap: wrap,
				unwrap: unwrap,
				replace: replace,
				rangeFromMatch: rangeFromMatch,
				indexOf: indexOf
			};
		};
	} );
	expose( ["tinymce/spellcheckerplugin/DomTextMatcher", "tinymce/spellcheckerplugin/Plugin"] );
})( this );



tinymce.PluginManager.add( 'atd', function ( editor )
{
	var languageMenuItems, suggestionsMenu, started, atdCore, dom,
		each = tinymce.each, settings = editor.settings;

	function buildMenuItems( listName, languageValues )
	{
		var items = [];

		tinymce.util.Tools.each( languageValues, function ( languageValue )
		{
			items.push( {
				selectable: true,
				text: languageValue.name,
				data: languageValue.value
			} );
		} );

		return items;
	}

	var languagesString = settings.spellchecker_languages ||
		'English=en,Danish=da,Dutch=nl,Finnish=fi,French=fr_FR,' +
			'German=de,Italian=it,Polish=pl,Portuguese=pt_BR,' +
			'Spanish=es,Swedish=sv';

	languageMenuItems = buildMenuItems( 'Language',
		tinymce.util.Tools.map( languagesString.split( ',' ),
			function ( lang_pair )
			{
				var lang = lang_pair.split( '=' );

				return {
					name: lang[0],
					value: lang[1]
				};
			}
		)
	);


	function getTextMatcher()
	{
		if ( !self.textMatcher ) {
			self.textMatcher = new tinymce.spellcheckerplugin.DomTextMatcher( editor.getBody(), editor );
		}

		return self.textMatcher;
	}

	/* initializes the functions used by the AtD Core UI Module */
	function initAtDCore()
	{

		atdCore = new window.AtDCore();
		atdCore.map = each;
		atdCore._isTinyMCE = true;

		atdCore.getAttrib = function ( node, key )
		{
			return dom.getAttrib( node, key );
		};

		atdCore.findSpans = function ( parent )
		{
			if ( parent === undefined ) {
				return dom.select( 'span' );
			} else {
				return dom.select( 'span', parent );
			}
		};

		atdCore.hasClass = function ( node, className )
		{
			return dom.hasClass( node, className );
		};

		atdCore.contents = function ( node )
		{
			return node.childNodes;
		};

		atdCore.replaceWith = function ( old_node, new_node )
		{
			return dom.replace( new_node, old_node );
		};

		atdCore.create = function ( node_html )
		{
			return dom.create( 'span', { 'class': 'mceItemHidden', 'data-mce-bogus': 1 }, node_html );
		};

		atdCore.removeParent = function ( node )
		{
			dom.remove( node, true );
			return node;
		};

		atdCore.remove = function ( node )
		{
			dom.remove( node );
		};

		var ignore = editor.getParam( 'atd_ignore_strings', false );

		if ( ignore === false ) {
			ignore = [];
		}

		atdCore.setIgnoreStrings( ignore.join( ',' ) );
		atdCore.showTypes( editor.getParam( 'atd_show_types', '' ) );
	}

	function getLang( key, defaultStr )
	{
		return ( window.AtD_l10n_r0ar && window.AtD_l10n_r0ar[key] ) || defaultStr;
	}

	function isMarkedNode( node )
	{
		return ( node.className && /\bhidden(GrammarError|SpellError|Suggestion)\b/.test( node.className ) );
	}

	function markMyWords( errors )
	{
		return atdCore.markMyWords( atdCore.contents( editor.getBody() ), errors );
	}

	// If no more suggestions, finish.
	function checkIfFinished()
	{
		if ( !editor.dom.select( 'span.hiddenSpellError, span.hiddenGrammarError, span.hiddenSuggestion' ).length ) {
			if ( suggestionsMenu ) {
				suggestionsMenu.hideMenu();
			}

			finish();
		}
	}

	function ignoreWord( target, word, all )
	{
		var dom = editor.dom;

		if ( all ) {
			each( editor.dom.select( 'span.hiddenSpellError, span.hiddenGrammarError, span.hiddenSuggestion' ), function ( node )
			{
				var text = node.innerText || node.textContent;

				if ( text === word ) {
					dom.remove( node, true );
				}
			} );
		} else {
			dom.remove( target, true );
		}

		checkIfFinished();
	}

	// Called when the user clicks "Finish" or when no more suggestions left.
	// Removes all remaining spans and fires custom event.
	function finish()
	{
		var node,
			dom = editor.dom,
			regex = new RegExp( 'mceItemHidden|hidden(((Grammar|Spell)Error)|Suggestion)' ),
			nodes = dom.select( 'span' ),
			i = nodes.length;

		while ( i-- ) { // reversed
			node = nodes[i];

			if ( node.className && regex.test( node.className ) ) {
				dom.remove( node, true );
			}
		}

		// Rebuild the DOM so AtD core can find the text nodes
		editor.setContent( editor.getContent( { format: 'raw' } ), { format: 'raw' } );

		started = false;
		editor.nodeChanged();
		editor.fire( 'SpellcheckEnd' );
	}

	function sendRequest( file, data, success )
	{
		var id = editor.getParam( 'atd_rpc_id', '12345678' ),
			url = editor.getParam( 'atd_rpc_url', '{backend}' );

		if ( url === '{backend}' || id === '12345678' ) {
			window.alert( 'Please specify: atd_rpc_url and atd_rpc_id' );
			return;
		}

		url = url.replace( '%s', settings.spellchecker_language );

		// create the nifty spinny thing that says "hizzo, I'm doing something fo realz"
		editor.setProgressState( true );

		tinymce.util.XHR.send( {
			url: url + '/' + file,
			content_type: 'text/xml',
			type: 'POST',
			data: 'data=' + encodeURI( data ).replace( /&/g, '%26' ) + '&key=' + id,
			success: success,
			error: function ( type, req, o )
			{
				editor.setProgressState();
				window.alert( type + '\n' + req.status + '\nAt: ' + o.url );
			}
		} );
	}

	function storeIgnoredStrings( text )
	{
		// Store in sessionStorage?
	}

	function setAlwaysIgnore( text )
	{
		var url = editor.getParam( 'atd_ignore_rpc_url' );

		if ( !url || url === '{backend}' ) {
			// Store ignored words for this session only
			storeIgnoredStrings( text );
		} else {
			// Plugin is configured to send ignore preferences to server, do that
			tinymce.util.XHR.send( {
				url: url + encodeURIComponent( text ) + '&key=' + editor.getParam( 'atd_rpc_id', '12345678' ),
				content_type: 'text/xml',
				type: 'GET',
				error: function ()
				{
					storeIgnoredStrings( text );
				}
			} );
		}

		// Update atd_ignore_strings with the new value
		atdCore.setIgnoreStrings( text );
	}

	// Create the suggestions menu
	function showSuggestions( target )
	{
		var pos, root, targetPos,
			items = [],
			text = target.innerText || target.textContent,
			errorDescription = atdCore.findSuggestion( target );

		if ( !errorDescription ) {
			items.push( {
				text: getLang( 'menu_title_no_suggestions', 'No suggestions' ),
				classes: 'atd-menu-title',
				disabled: true
			} );
		} else {
			items.push( {
				text: errorDescription.description,
				classes: 'atd-menu-title',
				disabled: true
			} );

			if ( errorDescription.suggestions.length ) {
				items.push( { text: '-' } ); // separator

				each( errorDescription.suggestions, function ( suggestion )
				{
					items.push( {
						text: suggestion,
						onclick: function ()
						{
							atdCore.applySuggestion( target, suggestion );
							checkIfFinished();
						}
					} );
				} );
			}
		}

		if ( errorDescription && errorDescription.moreinfo ) {
			items.push( { text: '-' } ); // separator

			items.push( {
				text: getLang( 'menu_option_explain', 'Explain...' ),
				onclick: function ()
				{
					editor.windowManager.open( {
						title: getLang( 'menu_option_explain', 'Explain...' ),
						url: errorDescription.moreinfo,
						width: 480,
						height: 380,
						inline: true
					} );
				}
			} );
		}

		items.push.apply( items, [
			{ text: '-' }, // separator

			{ text: getLang( 'menu_option_ignore_once', 'Ignore suggestion' ), onclick: function ()
			{
				ignoreWord( target, text );
			}}
		] );

		if ( editor.getParam( 'atd_ignore_enable' ) ) {
			items.push( {
				text: getLang( 'menu_option_ignore_always', 'Ignore always' ),
				onclick: function ()
				{
					setAlwaysIgnore( text );
					ignoreWord( target, text, true );
				}
			} );
		} else {
			items.push( {
				text: getLang( 'menu_option_ignore_all', 'Ignore all' ),
				onclick: function ()
				{
					ignoreWord( target, text, true );
				}
			} );
		}

		// Render menu
		suggestionsMenu = new tinymce.ui.Menu( {
			items: items,
			context: 'contextmenu',
			onautohide: function ( event )
			{
				if ( isMarkedNode( event.target ) ) {
					event.preventDefault();
				}
			},
			onhide: function ()
			{
				suggestionsMenu.remove();
				suggestionsMenu = null;
			}
		} );

		suggestionsMenu.renderTo( document.body );

		editor.selection.select(target);

		// Position menu
		var matchNode = target;
		var pos = tinymce.dom.DOMUtils.DOM.getPos( editor.getContentAreaContainer() );
		var targetPos = editor.dom.getPos( matchNode );
		var root = editor.dom.getRoot();

		// Adjust targetPos for scrolling in the editor
		if ( root.nodeName == 'BODY' ) {
			targetPos.x -= root.ownerDocument.documentElement.scrollLeft || root.scrollLeft;
			targetPos.y -= root.ownerDocument.documentElement.scrollTop || root.scrollTop;
			pos.x += targetPos.x;
			pos.y += targetPos.y;
		} else {
			targetPos.x -= root.scrollLeft;
			pos.x += targetPos.x;
			pos.y += targetPos.y - (root.scrollTop + 120);
		}


		if (typeof jQuery != 'undefined') {

			var p = $(target ).position();
			var o = $(target ).offset();
			var h = $( '#'+ suggestionsMenu._id ).outerHeight(true), w = $( '#'+ suggestionsMenu._id ).outerWidth(true);


			var t = o.top + matchNode.offsetHeight;
			var l = o.left;


			if ( o.top + matchNode.offsetHeight + h > $( window ).height() ) {
				t -= h + matchNode.offsetHeight;
			}

			if (l + w > $( window ).width() ) {
				l -= w - $(target ).width();
			}

			$( '#'+ suggestionsMenu._id ).css({
				left: l,
				top: t
			});
		}
		else {
			suggestionsMenu.moveTo( pos.x, pos.y + matchNode.offsetHeight );
		}


	}

	// Init everything
	editor.on( 'init', function ()
	{
		if ( typeof window.AtDCore === 'undefined' ) {
			return;
		}

		// Set dom and atdCore
		dom = editor.dom;
		initAtDCore();

		// add a command to request a document check and process the results.
		editor.addCommand( 'mceWritingImprovementTool', function ( callback )
		{
			var results,
				errorCount = 0;

			if ( typeof callback !== 'function' ) {
				callback = function () {};
			}

			// checks if a global var for click stats exists and increments it if it does...
			if ( typeof window.AtD_proofread_click_count !== 'undefined' ) {
				window.AtD_proofread_click_count++;
			}

			// remove the previous errors
			if ( started ) {
				finish();
				return;
			}

			// send request to our service
			sendRequest( 'checkDocument', editor.getContent( { format: 'raw' } ), function ( data, request )
			{
				// turn off the spinning thingie
				editor.setProgressState();

				// if the server is not accepting requests, let the user know
				if ( request.status !== 200 || request.responseText.substr( 1, 4 ) === 'html' || !request.responseXML ) {
					editor.windowManager.alert(
						getLang( 'message_server_error', 'There was a problem communicating with the Proofreading service. Try again in one minute.' ),
						callback( 0 )
					);

					return;
				}

				// check to see if things are broken first and foremost
				if ( request.responseXML.getElementsByTagName( 'message' ).item( 0 ) !== null ) {
					editor.windowManager.alert(
						request.responseXML.getElementsByTagName( 'message' ).item( 0 ).firstChild.data,
						callback( 0 )
					);

					return;
				}

				results = atdCore.processXML( request.responseXML );

				if ( results.count > 0 ) {
					errorCount = markMyWords( results.errors );
				}

				if ( !errorCount ) {
					editor.windowManager.alert( getLang( 'message_no_errors_found', 'No writing errors were found.' ) );
				} else {
					started = true;
					editor.fire( 'SpellcheckStart' );
				}

				callback( errorCount );
			} );
		} );

		if ( editor.settings.content_css !== false ) {
			// CSS for underlining suggestions
			dom.addStyle( '.hiddenSpellError{border-bottom:2px solid red;cursor:default;}' +
				'.hiddenGrammarError{border-bottom:2px solid green;cursor:default;}' +
				'.hiddenSuggestion{border-bottom:2px solid blue;cursor:default;}' );
		}

		// Menu z-index > DFW
		tinymce.DOM.addStyle( 'div.mce-floatpanel{z-index:150100 !important;}' );

		// Click on misspelled word
		editor.on( 'click', function ( event )
		{
			if ( isMarkedNode( event.target ) ) {
				event.preventDefault();
				editor.selection.select( event.target );
				// Create the suggestions menu
				showSuggestions( event.target );
			}
		} );
	} );

	function updateSelection( e )
	{
		var selectedLanguage = settings.spellchecker_language;

		e.control.items().each( function ( ctrl )
		{
			ctrl.active( ctrl.settings.data === selectedLanguage );
		} );
	}

	var buttonArgs = {
		tooltip: 'Spellcheck',
		cmd: 'mceWritingImprovementTool',
		onPostRender: function ()
		{
			var self = this;

			editor.on( 'SpellcheckStart SpellcheckEnd', function ()
			{
				self.active( started );
			} );
		}
	};

	if ( languageMenuItems.length > 1 ) {
		buttonArgs.type = 'splitbutton';
		buttonArgs.menu = languageMenuItems;
		buttonArgs.onshow = updateSelection;
		buttonArgs.onselect = function ( e )
		{
			settings.spellchecker_language = e.control.settings.data;
		};
	}

	editor.addButton( 'spellchecker', buttonArgs );

	editor.addMenuItem( 'spellchecker', {
		text: getLang( 'button_proofread_tooltip', 'Proofread Writing' ),
		context: 'tools',
		cmd: 'mceWritingImprovementTool',
		onPostRender: function ()
		{
			var self = this;

			editor.on( 'SpellcheckStart SpellcheckEnd', function ()
			{
				self.active( started );
			} );
		}
	} );

	editor.on( 'remove', function ()
	{
		if ( suggestionsMenu ) {
			suggestionsMenu.remove();
			suggestionsMenu = null;
		}
	} );

	// Set default spellchecker language if it's not specified
	settings.spellchecker_language = settings.spellchecker_language || settings.language || 'en';

} );
