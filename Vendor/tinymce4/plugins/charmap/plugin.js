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

tinymce.PluginManager.add('charmap', function(editor) {
	var charmap = [
		['160', 'no-break space'],
		['38', 'ampersand'],
		['34', 'quotation mark'],
	// finance
		['162', 'cent sign'],
		['8364', 'euro sign'],
		['163', 'pound sign'],
		['165', 'yen sign'],
	// signs
		['169', 'copyright sign'],
		['174', 'registered sign'],
		['8482', 'trade mark sign'],
		['8240', 'per mille sign'],
		['181', 'micro sign'],
		['183', 'middle dot'],
		['8226', 'bullet'],
		['8230', 'three dot leader'],
		['8242', 'minutes / feet'],
		['8243', 'seconds / inches'],
		['167', 'section sign'],
		['182', 'paragraph sign'],
		['223', 'sharp s / ess-zed'],
	// quotations
		['8249', 'single left-pointing angle quotation mark'],
		['8250', 'single right-pointing angle quotation mark'],
		['171', 'left pointing guillemet'],
		['187', 'right pointing guillemet'],
		['8216', 'left single quotation mark'],
		['8217', 'right single quotation mark'],
		['8220', 'left double quotation mark'],
		['8221', 'right double quotation mark'],
		['8218', 'single low-9 quotation mark'],
		['8222', 'double low-9 quotation mark'],
		['60', 'less-than sign'],
		['62', 'greater-than sign'],
		['8804', 'less-than or equal to'],
		['8805', 'greater-than or equal to'],
		['8211', 'en dash'],
		['8212', 'em dash'],
		['175', 'macron'],
		['8254', 'overline'],
		['164', 'currency sign'],
		['166', 'broken bar'],
		['168', 'diaeresis'],
		['161', 'inverted exclamation mark'],
		['191', 'turned question mark'],
		['710', 'circumflex accent'],
		['732', 'small tilde'],
		['176', 'degree sign'],
		['8722', 'minus sign'],
		['177', 'plus-minus sign'],
		['247', 'division sign'],
		['8260', 'fraction slash'],
		['215', 'multiplication sign'],
		['185', 'superscript one'],
		['178', 'superscript two'],
		['179', 'superscript three'],
		['188', 'fraction one quarter'],
		['189', 'fraction one half'],
		['190', 'fraction three quarters'],
	// math / logical
		['402', 'function / florin'],
		['8747', 'integral'],
		['8721', 'n-ary sumation'],
		['8734', 'infinity'],
		['8730', 'square root'],
		['8764', 'similar to'],
		['8773', 'approximately equal to'],
		['8776', 'almost equal to'],
		['8800', 'not equal to'],
		['8801', 'identical to'],
		['8712', 'element of'],
		['8713', 'not an element of'],
		['8715', 'contains as member'],
		['8719', 'n-ary product'],
		['8743', 'logical and'],
		['8744', 'logical or'],
		['172', 'not sign'],
		['8745', 'intersection'],
		['8746', 'union'],
		['8706', 'partial differential'],
		['8704', 'for all'],
		['8707', 'there exists'],
		['8709', 'diameter'],
		['8711', 'backward difference'],
		['8727', 'asterisk operator'],
		['8733', 'proportional to'],
		['8736', 'angle'],
	// undefined
		['180', 'acute accent'],
		['184', 'cedilla'],
		['170', 'feminine ordinal indicator'],
		['186', 'masculine ordinal indicator'],
		['8224', 'dagger'],
		['8225', 'double dagger'],
	// alphabetical special chars
		['192', 'A - grave'],
		['193', 'A - acute'],
		['194', 'A - circumflex'],
		['195', 'A - tilde'],
		['196', 'A - diaeresis'],
		['197', 'A - ring above'],
		['198', 'ligature AE'],
		['199', 'C - cedilla'],
		['200', 'E - grave'],
		['201', 'E - acute'],
		['202', 'E - circumflex'],
		['203', 'E - diaeresis'],
		['204', 'I - grave'],
		['205', 'I - acute'],
		['206', 'I - circumflex'],
		['207', 'I - diaeresis'],
		['208', 'ETH'],
		['209', 'N - tilde'],
		['210', 'O - grave'],
		['211', 'O - acute'],
		['212', 'O - circumflex'],
		['213', 'O - tilde'],
		['214', 'O - diaeresis'],
		['216', 'O - slash'],
		['338', 'ligature OE'],
		['352', 'S - caron'],
		['217', 'U - grave'],
		['218', 'U - acute'],
		['219', 'U - circumflex'],
		['220', 'U - diaeresis'],
		['221', 'Y - acute'],
		['376', 'Y - diaeresis'],
		['222', 'THORN'],
		['224', 'a - grave'],
		['225', 'a - acute'],
		['226', 'a - circumflex'],
		['227', 'a - tilde'],
		['228', 'a - diaeresis'],
		['229', 'a - ring above'],
		['230', 'ligature ae'],
		['231', 'c - cedilla'],
		['232', 'e - grave'],
		['233', 'e - acute'],
		['234', 'e - circumflex'],
		['235', 'e - diaeresis'],
		['236', 'i - grave'],
		['237', 'i - acute'],
		['238', 'i - circumflex'],
		['239', 'i - diaeresis'],
		['240', 'eth'],
		['241', 'n - tilde'],
		['242', 'o - grave'],
		['243', 'o - acute'],
		['244', 'o - circumflex'],
		['245', 'o - tilde'],
		['246', 'o - diaeresis'],
		['248', 'o slash'],
		['339', 'ligature oe'],
		['353', 's - caron'],
		['249', 'u - grave'],
		['250', 'u - acute'],
		['251', 'u - circumflex'],
		['252', 'u - diaeresis'],
		['253', 'y - acute'],
		['254', 'thorn'],
		['255', 'y - diaeresis'],
		['913', 'Alpha'],
		['914', 'Beta'],
		['915', 'Gamma'],
		['916', 'Delta'],
		['917', 'Epsilon'],
		['918', 'Zeta'],
		['919', 'Eta'],
		['920', 'Theta'],
		['921', 'Iota'],
		['922', 'Kappa'],
		['923', 'Lambda'],
		['924', 'Mu'],
		['925', 'Nu'],
		['926', 'Xi'],
		['927', 'Omicron'],
		['928', 'Pi'],
		['929', 'Rho'],
		['931', 'Sigma'],
		['932', 'Tau'],
		['933', 'Upsilon'],
		['934', 'Phi'],
		['935', 'Chi'],
		['936', 'Psi'],
		['937', 'Omega'],
		['945', 'alpha'],
		['946', 'beta'],
		['947', 'gamma'],
		['948', 'delta'],
		['949', 'epsilon'],
		['950', 'zeta'],
		['951', 'eta'],
		['952', 'theta'],
		['953', 'iota'],
		['954', 'kappa'],
		['955', 'lambda'],
		['956', 'mu'],
		['957', 'nu'],
		['958', 'xi'],
		['959', 'omicron'],
		['960', 'pi'],
		['961', 'rho'],
		['962', 'final sigma'],
		['963', 'sigma'],
		['964', 'tau'],
		['965', 'upsilon'],
		['966', 'phi'],
		['967', 'chi'],
		['968', 'psi'],
		['969', 'omega'],
	// symbols
		['8501', 'alef symbol'],
		['982',  'pi symbol'],
		['8476', 'real part symbol'],
		['978',  'upsilon - hook symbol'],
		['8472', 'Weierstrass p'],
		['8465', 'imaginary part'],
	// arrows
		['8592', 'leftwards arrow'],
		['8593', 'upwards arrow'],
		['8594', 'rightwards arrow'],
		['8595', 'downwards arrow'],
		['8596', 'left right arrow'],
		['8629', 'carriage return'],
		['8656', 'leftwards double arrow'],
		['8657', 'upwards double arrow'],
		['8658', 'rightwards double arrow'],
		['8659', 'downwards double arrow'],
		['8660', 'left right double arrow'],
		['8756', 'therefore'],
		['8834', 'subset of'],
		['8835', 'superset of'],
		['8836', 'not a subset of'],
		['8838', 'subset of or equal to'],
		['8839', 'superset of or equal to'],
		['8853', 'circled plus'],
		['8855', 'circled times'],
		['8869', 'perpendicular'],
		['8901', 'dot operator'],
		['8968', 'left ceiling'],
		['8969', 'right ceiling'],
		['8970', 'left floor'],
		['8971', 'right floor'],
		['9001', 'left-pointing angle bracket'],
		['9002', 'right-pointing angle bracket'],
		['9674', 'lozenge'],
		['9824', 'black spade suit'],
		['9827', 'black club suit'],
		['9829', 'black heart suit'],
		['9830', 'black diamond suit'],
		['8194', 'en space'],
		['8195', 'em space'],
		['8201', 'thin space'],
		['8204', 'zero width non-joiner'],
		['8205', 'zero width joiner'],
		['8206', 'left-to-right mark'],
		['8207', 'right-to-left mark'],
		['173',  'soft hyphen']
	];


    function get_html_translation_table(table, quote_style) {
        //  discuss at: http://phpjs.org/functions/get_html_translation_table/
        // original by: Philip Peterson
        //  revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // bugfixed by: noname
        // bugfixed by: Alex
        // bugfixed by: Marco
        // bugfixed by: madipta
        // bugfixed by: Brett Zamir (http://brett-zamir.me)
        // bugfixed by: T.Wild
        // improved by: KELAN
        // improved by: Brett Zamir (http://brett-zamir.me)
        //    input by: Frank Forte
        //    input by: Ratheous
        //        note: It has been decided that we're not going to add global
        //        note: dependencies to php.js, meaning the constants are not
        //        note: real constants, but strings instead. Integers are also supported if someone
        //        note: chooses to create the constants themselves.
        //   example 1: get_html_translation_table('HTML_SPECIALCHARS');
        //   returns 1: {'"': '&quot;', '&': '&amp;', '<': '&lt;', '>': '&gt;'}

        var entities = {},
            hash_map = {},
            decimal;
        var constMappingTable = {},
            constMappingQuoteStyle = {};
        var useTable = {},
            useQuoteStyle = {};

        // Translate arguments
        constMappingTable[0] = 'HTML_SPECIALCHARS';
        constMappingTable[1] = 'HTML_ENTITIES';
        constMappingQuoteStyle[0] = 'ENT_NOQUOTES';
        constMappingQuoteStyle[2] = 'ENT_COMPAT';
        constMappingQuoteStyle[3] = 'ENT_QUOTES';

        useTable = !isNaN(table) ? constMappingTable[table] : table ? table.toUpperCase() : 'HTML_SPECIALCHARS';
        useQuoteStyle = !isNaN(quote_style) ? constMappingQuoteStyle[quote_style] : quote_style ? quote_style.toUpperCase() :
            'ENT_COMPAT';

        if (useTable !== 'HTML_SPECIALCHARS' && useTable !== 'HTML_ENTITIES') {
            throw new Error('Table: ' + useTable + ' not supported');
            // return false;
        }

        entities['38'] = '&amp;';
        if (useTable === 'HTML_ENTITIES') {
            entities['160'] = '&nbsp;';
            entities['161'] = '&iexcl;';
            entities['162'] = '&cent;';
            entities['163'] = '&pound;';
            entities['164'] = '&curren;';
            entities['165'] = '&yen;';
            entities['166'] = '&brvbar;';
            entities['167'] = '&sect;';
            entities['168'] = '&uml;';
            entities['169'] = '&copy;';
            entities['170'] = '&ordf;';
            entities['171'] = '&laquo;';
            entities['172'] = '&not;';
            entities['173'] = '&shy;';
            entities['174'] = '&reg;';
            entities['175'] = '&macr;';
            entities['176'] = '&deg;';
            entities['177'] = '&plusmn;';
            entities['178'] = '&sup2;';
            entities['179'] = '&sup3;';
            entities['180'] = '&acute;';
            entities['181'] = '&micro;';
            entities['182'] = '&para;';
            entities['183'] = '&middot;';
            entities['184'] = '&cedil;';
            entities['185'] = '&sup1;';
            entities['186'] = '&ordm;';
            entities['187'] = '&raquo;';
            entities['188'] = '&frac14;';
            entities['189'] = '&frac12;';
            entities['190'] = '&frac34;';
            entities['191'] = '&iquest;';
            entities['192'] = '&Agrave;';
            entities['193'] = '&Aacute;';
            entities['194'] = '&Acirc;';
            entities['195'] = '&Atilde;';
            entities['196'] = '&Auml;';
            entities['197'] = '&Aring;';
            entities['198'] = '&AElig;';
            entities['199'] = '&Ccedil;';
            entities['200'] = '&Egrave;';
            entities['201'] = '&Eacute;';
            entities['202'] = '&Ecirc;';
            entities['203'] = '&Euml;';
            entities['204'] = '&Igrave;';
            entities['205'] = '&Iacute;';
            entities['206'] = '&Icirc;';
            entities['207'] = '&Iuml;';
            entities['208'] = '&ETH;';
            entities['209'] = '&Ntilde;';
            entities['210'] = '&Ograve;';
            entities['211'] = '&Oacute;';
            entities['212'] = '&Ocirc;';
            entities['213'] = '&Otilde;';
            entities['214'] = '&Ouml;';
            entities['215'] = '&times;';
            entities['216'] = '&Oslash;';
            entities['217'] = '&Ugrave;';
            entities['218'] = '&Uacute;';
            entities['219'] = '&Ucirc;';
            entities['220'] = '&Uuml;';
            entities['221'] = '&Yacute;';
            entities['222'] = '&THORN;';
            entities['223'] = '&szlig;';
            entities['224'] = '&agrave;';
            entities['225'] = '&aacute;';
            entities['226'] = '&acirc;';
            entities['227'] = '&atilde;';
            entities['228'] = '&auml;';
            entities['229'] = '&aring;';
            entities['230'] = '&aelig;';
            entities['231'] = '&ccedil;';
            entities['232'] = '&egrave;';
            entities['233'] = '&eacute;';
            entities['234'] = '&ecirc;';
            entities['235'] = '&euml;';
            entities['236'] = '&igrave;';
            entities['237'] = '&iacute;';
            entities['238'] = '&icirc;';
            entities['239'] = '&iuml;';
            entities['240'] = '&eth;';
            entities['241'] = '&ntilde;';
            entities['242'] = '&ograve;';
            entities['243'] = '&oacute;';
            entities['244'] = '&ocirc;';
            entities['245'] = '&otilde;';
            entities['246'] = '&ouml;';
            entities['247'] = '&divide;';
            entities['248'] = '&oslash;';
            entities['249'] = '&ugrave;';
            entities['250'] = '&uacute;';
            entities['251'] = '&ucirc;';
            entities['252'] = '&uuml;';
            entities['253'] = '&yacute;';
            entities['254'] = '&thorn;';
            entities['255'] = '&yuml;';
        }

        if (useQuoteStyle !== 'ENT_NOQUOTES') {
            entities['34'] = '&quot;';
        }
        if (useQuoteStyle === 'ENT_QUOTES') {
            entities['39'] = '&#39;';
        }
        entities['60'] = '&lt;';
        entities['62'] = '&gt;';

        // ascii decimals to real symbols
        for (decimal in entities) {
            if (entities.hasOwnProperty(decimal)) {
                hash_map[String.fromCharCode(decimal)] = entities[decimal];
            }
        }

        return hash_map;
    }
    function htmlentities(string, quote_style, charset, double_encode) {
        //  discuss at: http://phpjs.org/functions/htmlentities/
        // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        //  revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        //  revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // improved by: nobbler
        // improved by: Jack
        // improved by: Rafał Kukawski (http://blog.kukawski.pl)
        // improved by: Dj (http://phpjs.org/functions/htmlentities:425#comment_134018)
        // bugfixed by: Onno Marsman
        // bugfixed by: Brett Zamir (http://brett-zamir.me)
        //    input by: Ratheous
        //  depends on: get_html_translation_table
        //   example 1: htmlentities('Kevin & van Zonneveld');
        //   returns 1: 'Kevin &amp; van Zonneveld'
        //   example 2: htmlentities("foo'bar","ENT_QUOTES");
        //   returns 2: 'foo&#039;bar'

        var hash_map = get_html_translation_table('HTML_ENTITIES', quote_style),
            symbol = '';
        string = string == null ? '' : string + '';

        if (!hash_map) {
            return false;
        }

        if (quote_style && quote_style === 'ENT_QUOTES') {
            hash_map["'"] = '&#039;';
        }

        if ( !! double_encode || double_encode == null) {
            for (symbol in hash_map) {
                if (hash_map.hasOwnProperty(symbol)) {
                    string = string.split(symbol)
                        .join(hash_map[symbol]);
                }
            }
        } else {
            string = string.replace(/([\s\S]*?)(&(?:#\d+|#x[\da-f]+|[a-zA-Z][\da-z]*);|$)/g, function (ignore, text, entity) {
                for (symbol in hash_map) {
                    if (hash_map.hasOwnProperty(symbol)) {
                        text = text.split(symbol)
                            .join(hash_map[symbol]);
                    }
                }

                return text + entity;
            });
        }

        return string;
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
        '€': 'euro',
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

    function charToCode(char) {
        return typeof Entities[char] != 'undefined' ? '&' + Entities[char] + ';' : char;
    }





	function showDialog() {
		var gridHtml, x, y, win;

		function getParentTd(elm) {
			while (elm) {
				if (elm.nodeName == 'TD') {
					return elm;
				}

				elm = elm.parentNode;
			}
		}

		gridHtml = '<table role="presentation" cellspacing="0" class="mce-charmap"><tbody>';

		var width = 25;
		for (y = 0; y < 10; y++) {
			gridHtml += '<tr>';

			for (x = 0; x < width; x++) {
				var chr = charmap[y * width + x];

				gridHtml += '<td title="' + chr[1] + '"><div tabindex="-1" title="' + chr[1] + '" role="button">' +
					(chr ?  String.fromCharCode(parseInt(chr[0], 10))  : '&nbsp;') + '</div></td>';
			}

			gridHtml += '</tr>';
		}

		gridHtml += '</tbody></table>';

		var charMapPanel = {
			type: 'container',
			html: gridHtml,
			onclick: function(e) {
				var target = e.target;
				if (/^(TD|DIV)$/.test(target.nodeName))
                {
					editor.execCommand('mceInsertContent', false, tinymce.trim( target.innerText || target.textContent) );

					if (!e.ctrlKey) {
						win.close();
					}
				}
			},
			onmouseover: function(e) {
				var td = getParentTd(e.target);

				if (td) {
					win.find('#preview').text(td.firstChild.firstChild.data);
				}
			}
		};

		win = editor.windowManager.open({
			title: "Special character",
			spacing: 10,
			padding: 10,
			items: [
				charMapPanel,
				{
					type: 'label',
					name: 'preview',
					text: ' ',
					style: 'font-size: 40px; text-align: center',
					border: 1,
					minWidth: 100,
					minHeight: 80
				}
			],
			buttons: [
				{text: "Close", onclick: function() {
					win.close();
				}}
			]
		});
	}

	editor.addButton('charmap', {
		icon: 'charmap',
		tooltip: 'Special character',
		onclick: showDialog
	});

	editor.addMenuItem('charmap', {
		icon: 'charmap',
		text: 'Special character',
		onclick: showDialog,
		context: 'insert'
	});
});