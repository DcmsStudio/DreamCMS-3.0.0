var formTabIndex = 1;



function getLoadingImage ()
{
	return '<img src="'+ Config.get('backendImagePath') +'loading.gif" class="loading"/>';
}

var Tools = {
	mimeTypes: {
		"js": ["application/javascript", "application/x-javascript"],
		"javascript": ["application/javascript", "application/x-javascript"],
		"xml": ["application/xml", "text/xml", "application/x-google-gadget"],
		"groovy": ["script/groovy", "application/x-groovy", "application/x-jaxrs+groovy", "application/x-groovy+html", "application/x-chromattic+groovy"],
		"html": ["text/html", "application/x-uwa-widget"],
		"jpg": "image/jpeg",
		"ai": "application/postscript",
		"aif": "audio/x-aiff",
		"aifc": "audio/x-aiff",
		"aiff": "audio/x-aiff",
		"any": "text/any",
		"asc": "text/plain",
		"au": "audio/basic",
		"avi": "video/x-msvideo",
		"bcpio": "application/x-bcpio",
		"bin": "application/octet-stream",
		"bz2": "application/x-bzip2",
		"cdf": "application/x-netcdf",
		"class": "application/octet-stream",
		"cpio": "application/x-cpio",
		"cpt": "application/mac-compactpro",
		"cq": "application/cq-durboser",
		"csh": "application/x-csh",
		"css": "text/css",
		"dcr": "application/x-director",
		"dir": "application/x-director",
		"dms": "application/octet-stream",
		"doc": "application/msword",
		"dvi": "application/x-dvi",
		"dxr": "application/x-director",
		"ecma": "text/qhtml",
		"eps": "application/postscript",
		"esp": "text/qhtml",
		"etx": "text/x-setext",
		"exe": "application/octet-stream",
		"ez": "application/andrew-inset",
		"gif": "image/gif",
		"gtar": "application/x-gtar",
		"gz": "application/x-gzip",
		"hdf": "application/x-hdf",
		"hqx": "application/mac-binhex40",
		"htm": "text/html",
		"ice": "x-conference/x-cooltalk",
		"ief": "image/ief",
		"iges": "model/iges",
		"igs": "model/iges",
		"jpeg": "image/jpeg",
		"jpe": "image/jpeg",
		"bmp": "image/bmp",
		"kar": "audio/midi",
		"latex": "application/x-latex",
		"lha": "application/octet-stream",
		"lzh": "application/octet-stream",
		"man": "application/x-troff-man",
		"manifest": ["text/plain", "text/cache-manifest"],
		"me": "application/x-troff-me",
		"mesh": "model/mesh",
		"mid": "audio/midi",
		"midi": "audio/midi",
		"mif": "application/vnd=mif",
		"mov": "video/quicktime",
		"m4v": "video/x-m4v",
		"m4a": "audio/x-m4a",
		"movie": "video/x-sgi-movie",
		"mp2": "audio/mp2",
		"mp3": "audio/mp3",
		"mp4": "video/mp4",
		"mpe": "video/mpe",
		"mpeg": "video/mpeg",
		"mpg": "video/mpeg",
		"mpga": "audio/mpga",
		"ms": "application/x-troff-ms",
		"msh": "model/mesh",
		"nc": "application/x-netcdf",
		"oda": "application/oda",
		"pbm": "image/x-portable-bitmap",
		"pdb": "chemical/x-pdb",
		"pdf": "application/pdf",
		"pgm": "image/x-portable-graymap",
		"pgn": "application/x-chess-pgn",
		"php": "application/x-httpd-php",
		"png": "image/png",
		"pnm": "image/x-portable-anymap",
		"ppm": "image/x-portable-pixmap",
		"ppt": "application/ppt",
		"properties": "text/plain",
		"ps": "application/postscript",
		"qhtml": "text/qhtml",
		"qt": "video/quicktime",
		"ra": "audio/x-realaudio",
		"ram": "audio/x-pn-realaudio",
		"rm": "audio/x-pn-realaudio",
		"ras": "image/x-cmu-raster",
		"rgb": "image/x-rgb",
		"roff": "application/x-troff",
		"rpm": "application/x-rpm",
		"rtf": "text/rtf",
		"rtx": "text/richtext",
		"sgm": "text/sgml",
		"sgml": "text/sgml",
		"sh": "application/x-sh",
		"shar": "application/x-shar",
		"silo": "model/mesh",
		"sit": "application/x-stuffit",
		"skd": "application/x-koan",
		"skm": "application/x-koan",
		"skp": "application/x-koan",
		"skt": "application/x-koan",
		"smi": "application/smil",
		"smil": "application/smil",
		"snd": "audio/basic",
		"spl": "application/x-futuresplash",
		"src": "application/x-wais-source",
		"sv4cpio": "application/x-sv4cpio",
		"sv4crc": "application/x-sv4crc",
		"swf": "application/x-shockwave-flash",
		"t": "application/x-troff",
		"tar": "application/x-tar",
		"tcl": "application/x-tcl",
		"tex": "application/x-tex",
		"texi": "application/x-texinfo",
		"texinfo": "application/x-texinfo",
		"tgz": "application/x-gzip",
		"tif": "image/tiff",
		"tiff": "image/tiff",
		"tr": "application/x-troff",
		"tsv": "text/tab-separated-values",
		"txt": "text/plain",
		"odt": "application/vnd.oasis.opendocument.text",
		"ods": "application/vnd.oasis.opendocument.spreadsheet",
		"odp": "application/vnd.oasis.opendocument.presentation",
		"odb": "application/vnd.oasis.opendocument.database",
		'ogv': 'video/ogg',
		'ogm': 'video/ogg',
		'ogg': 'audio/ogg',
		'oga': 'audio/ogg',
		"ustar": "application/x-ustar",
		"vcd": "application/x-cdlink",
		"vm": "text/plain",
		"vrml": "model/vrml",
		"wav": ["audio/x-wav", 'audio/wav'],
		'webm': 'video/webm',
		"wrl": "model/vrml",
		"xbm": "image/x-xbitmap",
		"xls": "application/xls",
		"xpdl": "text/xml",
		"xpm": "image/x-xpixmap",
		"xwd": "image/x-xwindowdump",
		"xyz": "chemical/x-pdb",
		"zip": "application/zip",
		"rar": "application/rar",
		"msg": "application/vnd.ms-outlook"
	},
	getLoadingImage: function ()
	{
		return '<img src="' + Config.get( 'loadingImgSmall' ) + '" class="loading"/>';
	},
	escapeJqueryRegex: function ( name )
	{
		return name.replace( /[#;&,.+*~':"!^$[\]()=>|\/]/g, "\\\\$&" );
	},
	trans: function ()
	{
		var returnStr, str = arguments.shift();

		try {
			eval( 'returnStr = sprintf(str, ' + arguments.join( ',' ) + ');' );
		}
		catch ( e ) {
			console.log( 'trans Error: ' + e );

			return '';
		}

		return returnStr;
	},
	loadCss: function ( url, callback )
	{
		var hash = md5( url );

		if ( $( '#css-' + hash, $( "head" ) ).length == 0 ) {


                var head  = $( "head").get(0);
                var link  = $( "<link>").get(0);
                link.id   = 'css-'+hash;
                link.rel  = 'stylesheet';
                link.type = 'text/css';
                link.href = url;
                link.media = 'all';
                head.appendChild(link);


            if ( typeof callback == 'function' ) {
                callback( $(link) );
            }

            /*


			var styleTag = $( '<link/>' ).attr( 'type', 'text/css' ).attr( 'id', 'css-' + hash ).attr( 'href', url );
            styleTag.load(function() {
                if ( typeof callback == 'function' ) {
                    callback( styleTag );
                }
            })
			$( "head" ).append( styleTag );





			$.ajax( {
				url: url,
				dataType: "text",
				error: function ()
				{
					Debug.error( 'Could not get the CSS File: ' + url );
				},
				success: function ( data )
				{
					var styleTag = $( '<style>' ).attr( 'id', 'css-' + hash );
					$( "head" ).prepend( styleTag.text( data ) );

					if ( typeof callback == 'function' ) {
						callback( styleTag );
					}
				}
			} );
*/
		}
		else {
			if ( typeof callback == 'function' ) {
				callback( $( '#css-' + hash, $( "head" ) ) );
			}
		}

	},
	loadScripts: function ( url, callback )
	{
		if ( typeof url == 'string' ) {
			$.getScript( url, function ()
			{
				if ( typeof callback == 'function' ) {
					callback();
				}
			} );
		}
		else if ( typeof url == 'object' ) {
			Core.loadScripts( url, callback );
		}
		else {
			Debug.warn( 'Invalid Script load!' );
		}
	},
	loadScript: function ( url, callback )
	{
		if ( typeof Core === 'object' && typeof Core.loadScripts === 'function' && typeof url == 'object' ) {
			Core.loadScripts( url, callback );
		}
		else if ( typeof url == 'string' ) {

			Core.jqGetScript( url, function ()
			{
				if ( typeof callback == 'function' ) {
					callback();
				}
			} );
		}
		else {
			Debug.warn( 'Invalid Script load!' );

		}
	},
	globalEval: function ( data )
	{
		if ( typeof data === 'string' && jQuery.trim( data ) ) {
			// We use execScript on Internet Explorer
			// We use an anonymous function so that context is window
			// rather than jQuery in Firefox
			try {
				(window.execScript || function ( data )
				{
					window[ "eval" ].call( window, data );
				})( data );
			}
			catch ( e ) {
				console.log( e + ' Data: ' + data );
			}

		}
	},
	eval: function ( strObject )
	{

		$( strObject ).filter( 'script' ).each( function ()
		{
			if ( typeof this.text == 'string' || typeof this.textContent == 'string' || typeof this.innerHTML == 'string' ) {
				Tools.globalEval( this.text || this.textContent || this.innerHTML );
			}
		} );
	},

	convertObjectToArray: function(arr) {
		if (!this.isObject(arr)) {
			return [];
		}
		var outArray = [];
		for (k in arr) {
			outArray.push(arr[k]);
		}
		return outArray;
	},
	exists: function ( object, name )
	{

		if ( object === null || typeof object == 'undefined' || !Tools.isObject( object ) || !object ) {
			// Debug.log('Could not check type exists! Key to check:' + name);
			return false;
		}
		return object.hasOwnProperty( name );
	},
	isUndefined: function ( test )
	{
		if ( typeof test == "undefined" ) {
			return true;
		}
		return false;
	},
	isObject: function ( test )
	{
		if ( typeof test == "object" ) {
			return true;
		}
		return false;
	},
	isFunction: function ( test )
	{
		if ( typeof test == "function" ) {
			return true;
		}
		return false;
	},
	isInteger: function ( test )
	{
		if ( typeof test == 'string' && /^[0-9]+$/.test( test ) ) {
			return true;
		}
		return false;
	},
	isString: function ( test )
	{
		if ( typeof test == 'string' ) {
			return true;
		}
		return false;
	},
	responseIsOk: function ( data )
	{

		if ( data && this.isObject( data ) )
		{
			// refresh all secure tokens
			if (this.exists( data, 'csrfToken' ) && data.csrfToken )
			{
				if (data.csrfToken != Config.get('token') ) {
					Config.set('token', data.csrfToken);
					$('#content-container input[name=token],#main-content-buttons input[name=token]' ).val(data.csrfToken);
				}






			//	$('#content-container input[name=token],#main-content-buttons input[name=token]' ).val(data.csrfToken);
			//	Config.set('token', data.csrfToken);
			}

			if ( this.exists( data, 'success' ) && data.success == false ) {
				if ( data.error && data.error !== null ) {
					DesktopConsole.setErrors( data.error );
				}
			}
			else {
				if ( data.debugoutput && data.debugoutput !== null ) {
					DesktopConsole.setDebug( data.debugoutput );
				}
			}
		}

		if ( data != null && this.isObject( data ) && data.hasOwnProperty( 'permissionerror' ) ) {
			Tools.html5Audio( 'html/audio/hero' );
			return false;
		}

		else if ( data != null && this.isObject( data ) && data.hasOwnProperty( 'sessionerror' ) ) {

			Tools.html5Audio( 'html/audio/session-error' );

			setTimeout( function ()
			{
				document.location.href = 'admin.php?adm=auth&action=logout';
			}, 1500 );

			// return false;
		}
		else if ( data == null || this.isString( data ) || (this.isObject( data ) && this.exists( data, 'success' ) && data.success == false) )
        {
			Tools.html5Audio( 'html/audio/error' );
			return false;
		}
		else {
			return true;
		}
	},
	/**
	 *      URL Tools
	 */
	prepareAjaxUrl: function ( url )
	{
		var base = Config.get( 'portalurl', '' );

		if ( url !== null && typeof url === 'string' && !url.match( /^\w+\:\/\// ) ) {
			url = base.replace( /\/$/, "" ) + "/" + url.replace( /^\//, "" );
		}

		if ( Config.get( 'SSL_MODE', false ) ) {
			url = (url !== null && typeof url == 'string' ? url.replace( /^https?:/i, "https:" ) : Config.get( 'SSL_portalurl', '' ))
		}

		return url;
	},
	extractAppInfoFromUrl: function ( url )
	{
		var to = (typeof url);

		if ( url !== null && typeof url === 'object' ) {
			return {
				controller: url.adm,
				action: url.action
			};
		}
		else if ( url !== null && typeof url === 'string' ) {
			return {
				controller: $.getURLParam( 'adm', url ), //url.replace(/.*adm=([\w0-9_]*).*/g, '$1'),
				action: $.getURLParam( 'action', url ),
				plugin: $.getURLParam( 'plugin', url )//url.replace(/.*([&\?]?)action=([\w0-9_]*).*/g, '$2')
			};
		}

		return {
			controller: null,
			action: null
		};
	},
	convertUrlToObject: function ( urlStr )
	{

		var strQueryString = urlStr.substr( urlStr.indexOf( "?" ) + 1 );
		strQueryString = strQueryString.replace( '&amp;', '&' );

		var obj = {};

		if ( strQueryString != '' ) {
			strQueryString += '&';
			var params = strQueryString.split( '&' );
			for ( var x = 0; x < params.length; x++ ) {
				var p = params[x].split( '=' );
				if ( p[0] && p[1] != '' ) {
					obj[p.shift()] = p.shift();
				}
			}
		}

		return obj;
	},
	/**
	 *
	 *      File tools
	 */
	getMime: function ( ext )
	{
		return (this.mimeTypes[ ext.toLowerCase() ] != null ? this.mimeTypes[ ext.toLowerCase() ] : false);
	},
	formatSize: function ( size )
	{
		var units = new Array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB' );
		i = 0;
		while ( size > 1024 ) {
			i++;
			size = size / 1024;
		}
		return size.toFixed( 1 ) + ' ' + units[i];
	},
	unformatSize: function ( size )
	{
        size = ''+ size;
        if ( !size.match( /b/i ) && !size.match( /byte/i ) &&
             !size.match( /k/i ) && !size.match( /kb/i ) &&
             !size.match( /m/i ) && !size.match( /mb/i ) &&
             !size.match( /g/i ) && !size.match( /gb/i )
            ) {
            return parseInt( size, 10 );
        }


		if ( size.match( /b/i ) || size.match( /byte/i ) ) {
			var inSize = parseInt( size, 10 );
			return inSize;
		}

		if ( size.match( /k/i ) || size.match( /kb/i ) ) {
			var inSize = parseInt( size, 10 );
			return inSize * 1024;
		}

		if ( size.match( /m/i ) || size.match( /mb/i ) ) {
			var inSize = parseInt( size, 10 );
			return inSize * 1024 * 1024;
		}

		if ( size.match( /g/i ) || size.match( /gb/i ) ) {
			var inSize = parseInt( size, 10 );
			return inSize * 1024 * 1024 * 1024;
		}
	},
	/**
	 *
	 *
	 */
	rgb2hex: function ( rgb )
	{
		var rgbm = rgb.match( /^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/ );

		if ( typeof rgbm != 'undefined' && rgbm ) {
			return "#" +
				("0" + parseInt( rgbm[1], 10 ).toString( 16 )).slice( -2 ) +
				("0" + parseInt( rgbm[2], 10 ).toString( 16 )).slice( -2 ) +
				("0" + parseInt( rgbm[3], 10 ).toString( 16 )).slice( -2 );
		}
		return rgb;
	},
	/**
	 *
	 *
	 */
	scrollBar: function ( elObj, scollToObj, onScrollEvent )
	{
		// elObj is the scrollContent
		var $el = elObj.parent(); // use parent as Container
		var $scrollContent = elObj;

		if ( elObj.hasClass( 'window-body-content' ) ) {
			$el = elObj;
			$scrollContent = elObj.find( ':visible:first' );
		}

        if (!$el.length || typeof $el.nanoScroller !== 'function') {
            return;
        }

		$scrollContent.addClass( 'scroll-content' ).height( '' );
		$el.addClass( 'nano' );

		var opt = {}; //{scrollContent: $scrollContent};

		if ( typeof scollToObj === 'object' ) {
			opt.scrollTo = scollToObj;
		}
		else if ( scollToObj === 'bottom' ) {
			opt.scroll = 'bottom';
		}
		else if ( scollToObj === 'top' ) {
			opt.scroll = 'top';
		}
		else if ( typeof scollToObj !== 'undefined' && scollToObj !== null && scollToObj !== false ) {
			opt.scrollTo = scollToObj;
		}

		if ( typeof onScrollEvent === 'function' ) {
			opt.onScroll = onScrollEvent;
		}

		$el.nanoScroller( opt );
	},
	refreshScrollBar: function ( elObj )
	{
		// elObj is the scrollContent
		var $el = elObj.parent(); // use parent as Container
		var $scrollContent = elObj;

		if ( elObj.hasClass( 'window-body-content' ) ) {
			$el = elObj;
			$scrollContent = elObj.children( ':visible:first' );
		}
		var el = $el.get( 0 );
		if ( el && el.hasOwnProperty( 'nanoscroller' ) ) {
			$el.nanoScroller( {scrollContent: $scrollContent} );
		}
	},
	getScrollPosTop: function ( elObj )
	{
		var $el = elObj.parent(); // use parent as Container
		if ( elObj.hasClass( 'window-body-content' ) ) {
			$el = elObj;
		}

		var el = $el.get( 0 );
		if ( el && el.hasOwnProperty( 'nanoscroller' ) ) {
			return $el.nanoScroller( 'scrollPosTop' );
		}

		return 0;
	},
	removeScrollBar: function ( elObj )
	{
		// elObj is the scrollContent
		var $el = elObj.parent(); // use parent as Container
		var $scrollContent = elObj;

		if ( elObj.hasClass( 'window-body-content' ) ) {
			$el = elObj;
			$scrollContent = elObj.children( '>:visible:first' );
		}

		if ( $el.hasClass( 'has-scrollbar' ) ) {
			$el.removeNanoScroller( {scrollContent: $scrollContent} );
			$el.removeClass( 'nano' );
			$scrollContent.removeClass( 'scroll-content' );
		}
	},


    reindexArray : function( array )
    {
        var index = 0;                          // The index where the element should be
        for( var key in array )                 // Iterate the array
        {
            if( parseInt( key ) !== index )     // If the element is out of sequence
                array[index] = array[key];      // Move it to the correct, earlier position in the array
            ++index;                            // Update the index
        }

       // array.splice( index );  // Remove any remaining elements (These will be duplicates of earlier items)
    },





	/**
	 *
	 * @type String
	 */

	popup: function ( url, title, width, height, nopadding )
	{


		var opt = {
			Width: 310,
			Height: 300,
			icon: '',
			title: 'DreamCMS...',
			loadWithAjax: true,
			allowAjaxCache: false,
			WindowToolbar: false,
			WindowMaximize: false,
			WindowMinimize: false,
			WindowResizeable: true,
			DesktopIconWidth: 36,
			DesktopIconHeight: 36,
			UseWindowIcon: false,
			WindowContent: null,
			onBeforeShow: null,
			onBeforeClose: null,
			onBeforeOpen: null,
			onAfterCreated: null,
			onClose: null
		};


        var defaults = opt;


        if (typeof title === 'object') {
            opt = $.extend({}, defaults, title);
            opt.minWidth = opt.Width;
            opt.minHeight = opt.Height;
        }
        else
        {
            opt.title = title;

            if ( width && width > 100 ) {
                opt.minWidth = width;
                opt.Width = width;
            }

            if ( height && height > 100 ) {
                opt.minHeight = height;
                opt.Height = height;
            }

            if ( nopadding ) {
                opt.nopadding = nopadding;
            }
        }




		opt.WindowDesktopIconFile = '';

		$.ajax( {
			type: 'GET',
			url: url + '&ajax=true',
			cache: false,
			async: true,
			global: false,
			beforeSend: function ()
			{
				document.body.style.cursor = 'progress';
			},
			success: function ( data )
			{
				if ( Tools.responseIsOk( data ) ) {

					opt.WindowContent = data.maincontent;
					opt.onAfterOpen = function ()
					{
						document.body.style.cursor = '';

						if ( $( data.maincontent ).filter( 'script' ).length ) {
							//   console.log('Eval Scripts after window Created');
							Tools.eval( $( data.maincontent ) );
						}
					};

					var load = 0;
					if ( Tools.exists( data.loadScripts, 'css' ) && data.loadScripts.css.length ) {
						load += data.loadScripts.css.length;
					}
					if ( Tools.exists( data.loadScripts, 'js' ) && data.loadScripts.js.length ) {
						load += data.loadScripts.js.length;
					}

					if ( load ) {

						if ( Tools.exists( data.loadScripts, 'css' ) && data.loadScripts.css.length ) {
							for ( var x = 0; x < data.loadScripts.css.length; x++ ) {
								if ( data.loadScripts.css[x].substr( data.loadScripts.css[x].length - 4, data.loadScripts.css[x].length ) != '.css' ) {
									data.loadScripts.css[x] += '.css';
								}

								var cssh = hash;
								if ( !$( '#css-' + cssh ).length ) {
									Tools.loadCss( data.loadScripts.css[x], function ( styleTag )
									{
										styleTag.attr( 'id', 'css-' + cssh );
									} );
								}
							}

							if ( Tools.exists( data.loadScripts, 'js' ) && !data.loadScripts.js.length ) {
								Tools.createPopup( data.maincontent, opt );
							}

						}

						if ( Tools.exists( data.loadScripts, 'js' ) && data.loadScripts.js.length ) {
							Tools.loadScripts( data.loadScripts.js, function ()
							{
								Tools.createPopup( data.maincontent, opt );
							} );
						}

					}
					else {
						Tools.createPopup( data.maincontent, opt );
					}

				}
			}} );
	},
	createPopup: function ( content, options )
	{
		var defaults = {
			minWidth: 310,
			minHeight: 180,
			Width: 310,
			Height: 300,
			icon: '',
			title: 'DreamCMS...',
			opener: false,
			loadWithAjax: true,
			allowAjaxCache: false,
			WindowToolbar: false,
			WindowMaximize: false,
			WindowMinimize: false,
			WindowResizeable: false,
			DesktopIconWidth: 36,
			DesktopIconHeight: 36,
			UseWindowIcon: false,
			WindowContent: false,
			onBeforeShow: false,
			onBeforeClose: false,
			onBeforeOpen: false,
			onAfterCreated: false,
			onClose: false,
			ajaxData: false,
			addFileSelector: options.addFileSelector || false
		};

		if ( typeof options != 'object' ) {
			options = {};
		}


		var opts = $.extend( {}, defaults, options );


        if (opts.minWidth > opts.Width) {
            opts.Width = opts.minWidth;
        }

        if (opts.minHeight > opts.Height) {
            opts.Height = opts.minHeight;
        }



        var popupID = 'popup-' + new Date().getTime();
        Win.windowID = popupID;

		var container = $( '<div class="popup fade">' ).attr('id', popupID );

		if ( opts.opener ) {
			container.attr( 'opener', opts.opener.replace( 'tab-', '' ).replace( 'content-', '' ) );
		}
		else {
			if ( typeof Win.windowID === 'string' ) { container.attr( 'opener', Win.windowID.replace( 'tab-', '' ).replace( 'content-', '' ) ); }
		}

		container.append( '<div class="popup-head"><div class="popup-header"></div><div class="popup-buttons"></div></div><div class="popup-toolbar"></div><div class="popup-content"><div class="popup-content-inner"></div></div>' );

        if ( typeof opts.WindowTitle == 'string' && opts.WindowTitle != '' ) {
            container.find( '.popup-header' ).append( opts.WindowTitle );
        }
		else if ( opts.title ) {
			container.find( '.popup-header' ).append( opts.title );
		}

		var closeBtn = $( '<span class="close"></span>' );


		container.find( '.popup-buttons' ).append( closeBtn );

		if ( opts.WindowMaximize || opts.WindowResizeable || opts.resizeable ) {
			var maxBtn = $( '<span class="max"></span>' );
			maxBtn.click( function ()
			{

				var btn = $(this), pop = $( this ).parents( 'div.popup' );

				if ( !pop.data( 'old' ) ) {

					pop.data( 'old', {
						top: pop.offset().top,
						left: pop.offset().left,
						width: pop.width(),
						height: pop.height(),
                        contentHeight: pop.find('div.popup-content').height()
					} );

					pop.animate( {
						left: 0,
						top: 0,
						width: $( document ).width(),
						height: $( document ).height()
					}, {
						duration: 300,
						complete: function ()
						{

                            //$('body').addClass('');

                            $(this).addClass('is-max');
                            var h = $(this).find( 'div.popup-head' ).outerHeight( true ) + $(this).find( 'div.popup-toolbar' ).outerHeight( true );
                            var padding = parseInt($(this).find( '.popup-content').css('paddingTop'), 10) + parseInt($(this).find( '.popup-content').css('paddingBottom'), 10);
                            $(this).find('div.popup-content').height($( document ).height() - padding - h );
                            $(window).trigger('resize');
						}
					} );

				}
				else {
					var dat = pop.data( 'old' );
					pop.animate( {
						left: dat.left,
						top: dat.top,
						width: dat.width,
						height: dat.height
					}, {
						duration: 300,
						complete: function ()
						{
                            $(this).find('div.popup-content').height(dat.contentHeight);
                            $(this).removeData( 'old' );
                            $(this).removeClass('is-max');
                            $(window).trigger('resize');
						}
					} );
				}
			} );

			container.find( '.popup-buttons' ).prepend( maxBtn );

			container.resizable( {
				minWidth: (opts.Width > 100 ? opts.Width : 100),
				minHeight: (opts.Height > 60 ? opts.Height : 60),
				resize: function(e, ui) {
                    var padding = parseInt($(this).find( '.popup-content').css('paddingTop'), 10) + parseInt($(this).find( '.popup-content').css('paddingBottom'), 10);

                    var h = $(this).find( 'div.popup-head' ).outerHeight( true ) + $(this).find( 'div.popup-toolbar' ).outerHeight( true );
					$(this).find( 'div.popup-content' ).height(ui.size.height - padding - h);
				}
			} );

		}


		if ( opts.WindowToolbar ) {
			container.find( 'div.popup-toolbar' ).append( opts.WindowToolbar ).show();
		}
		else if ( opts.toolbar ) {
			container.find( 'div.popup-toolbar' ).append( opts.WindowToolbar ).show();
		}
		else {
			container.find( 'div.popup-toolbar' ).hide();
		}

		container.find( 'div.popup-content-inner' ).append( (typeof content == 'string' ? content : opts.WindowContent) );

		container.css( {
			zIndex: 9999
		} ).show();

		if ( opts.Width > 100 ) {
			container.width( opts.Width );
			container.css( {
				left: ($( document ).width() / 2) - (opts.Width / 2)
			} );
		}
		else {
			container.width( 100 );
			container.css( {
				left: ($( document ).width() / 2) - 50
			} );
		}

		if ( opts.Height > 60 ) {
			container.height( opts.Height );
			container.css( {
				top: ($( document ).height() / 2) - (opts.Height / 2)
			} );
		}
		else {
			container.height( 60 );
			container.css( {
				top: ($( document ).height() / 2) - 30
			} );
		}

		if ( opts.nopadding ) {
			container.addClass( 'no-padding' );
		}

		if ( typeof opts.onBeforeOpen == 'function' ) {
			opts.onBeforeOpen();
		}

		if ( $( '#fullscreenContainer' ).length ) {
			$( '#fullscreenContainer' ).append( container );
		}
		else {
			$( 'body' ).append( container );
		}

		container.find( '.popup-content' ).height( container.height() - 20 - container.find( '.popup-head' ).outerHeight( true ) - container.find( '.popup-toolbar' ).outerHeight( true ) );
		container.draggable( {
			handle: container.find( '.popup-header' )
		} );

		if ( typeof $.fn.BootstrapVersion != 'undefined' ) {
			closeBtn.click( function ( e )
			{
				var s = this;

				if ( opts.onBeforeClose ) {
					var call = function() {
						$( this ).parents( 'div.popup:first' ).modal('hide');
                        Win.windowID = null;
					};

					opts.onBeforeClose( e, $( this ).parents( 'div.popup:first' ), $.proxy(call, this) );
				}
				else {
					$( this ).parents( 'div.popup:first' ).modal('hide');
				}
			} );
		}
		else {
			closeBtn.click( function ( e )
			{
				var s = this;

				if ( opts.onBeforeClose ) {
					opts.onBeforeClose( e, $( this ).parents( 'div.popup:first' ), function ()
					{
						$( s ).parents( 'div.popup' ).hide();
						$( s ).parents( 'div.popup' ).remove();
                        Win.windowID = null;
					} );
				}
				else {
					$( this ).parents( 'div.popup' ).hide();
					$( this ).parents( 'div.popup' ).remove();
                    Win.windowID = null;
				}
			} );
		}


		container.on( 'shown.bs.modal', function ( e ){
			Doc.loadTinyMce( $(this), true );

			if ( typeof opts.onAfterOpen == 'function' ) {
				opts.onAfterOpen( $(this), false, false );

                var padding = parseInt($(this).find( '.popup-content').css('paddingTop'), 10) + parseInt($(this).find( '.popup-content').css('paddingBottom'), 10);
                $(this).find( '.popup-content' ).height( $(this).height() - padding - $(this).find( '.popup-head' ).outerHeight( true ) - $(this).find( '.popup-toolbar' ).outerHeight( true ) );
            }
            else {

                var padding = parseInt($(this).find( '.popup-content').css('paddingTop'), 10) + parseInt($(this).find( '.popup-content').css('paddingBottom'), 10);

                $(this).find( '.popup-content' ).height( $(this).height() - padding - $(this).find( '.popup-head' ).outerHeight( true ) - $(this).find( '.popup-toolbar' ).outerHeight( true ) );
            }


		});

		container.on( 'hidden.bs.modal', function ( e ){
			$( this ).remove();
            Win.windowID = null;
		});


		container.modal({
			keyboard: false,
			show: true,
			backdrop: false
		});



		/*
		container.show( 0, function ()
		{

			Doc.loadTinyMce( $(this), true );

			if ( typeof opts.onAfterOpen == 'function' ) {

				opts.onAfterOpen( container, false, false );

			}
		} );

		*/


	},
	/**
	 * Play automatic the sound
	 * @param {string} pathToFile without extension!!!
	 * @returns {undefined}
	 */
	lastPlay: null,
	html5Audio: function ( pathToFile )
	{
		if ( this.lastPlay === pathToFile ) {
			return;
		}

		if ( window.HTMLAudioElement ) {
			var snd = new Audio( '' );
			this.lastPlay = pathToFile;

			if ( snd.canPlayType( 'audio/ogg' ) ) {
				snd = new Audio( pathToFile + '.ogg' );
				snd.volume = 0.7;
				snd.play();
				this.lastPlay = null;
				//     console.log('Play audio');
			}
			else if ( snd.canPlayType( 'audio/mp3' ) ) {
				snd = new Audio( pathToFile + '.mp3' );
				snd.volume = 0.7;
				snd.play();
				this.lastPlay = null;
				//      console.log('Play audio');
			}
			else {
				//    console.log('Skip audio');
			}
		}
		else {
			//     console.log('Skip audio');
		}
	},
	/**
	 * create a multi file upload
	 * @param object opts
	 * @returns {undefined}
	 */
	MultiUploadControl: function ( opt )
	{

		var HTML5_UploaderEnabled = (!!window.FileReader || typeof window.FileReader !== 'undefined') && Modernizr.draganddrop;
		var uploadControl = false;
		var opts = $.extend( {}, opt );

		if ( typeof opt.control == 'string' ) {
			uploadControl = $( '#' + opt.control );
		}
		else if ( typeof opt.control != 'undefined' ) {
			uploadControl = opt.control;
		}
		else {
			jAlert( 'Invalid upload Control container', 'error' );
			return false;
		}

		if ( uploadControl.length == 0 ) {
			Debug.warn( 'Invalid upload Control container' );
			return false;
		}

		if ( uploadControl.find( '#start-upload-btn' ).length ) {
			// Debug.warn('Invalid upload Control container! Upload exists!');
			//return false;
		}

		//  return $(uploadControl).upload(opt); 

		/*

		 if (HTML5_UploaderEnabled)          {
		 */

		var self = this, dropControl = uploadControl;

		if ( opt.refresh === true ) {
			if ( typeof opt.postParams == 'object' ) {
				dropControl.filedrop( {refresh: true, data: opt.postParams} );
			}

			return;
		}

		var $ul = dropControl.parents( 'form:first' ).find( 'ul.dropped-files' );
		if ( !$ul.length ) {
			Debug.warn( 'Invalid List for dropped files! create a ul.dropped-files element' );
			return;
		}

		var uploadform = dropControl.parents( 'form:first' );
		var fallbackInput = dropControl.find( 'span.browse:first' ).parent().find( 'input[type=file]:first' );

        fallbackInput.addClass('no-bootstrap');

		$ul.empty().hide();

		dropControl.removeData('upload-opts');
		dropControl.unbind();

		uploadform.unbind();
		uploadform.removeData('upload-opts');

		uploadform.data( 'upload-opts', opts );
		dropControl.data( 'upload-opts', opts );

		dropControl.find( 'input[type=file]:first' ).unbind();
		dropControl.find( 'span.browse:first').unbind( 'click.upload' ).addClass('btn btn-default');
		dropControl.find( 'span.browse:first' ).bind( 'click.upload', function ()
		{
			// Simulate a click on the file input button
			// to show the file browser dialog
			$( this ).parent().find( 'input[type=file]:first' ).click();
		} );
		var fileMaskRegex = '.*';

		if ( typeof opt.file_type_mask == 'string' ) {
			var masks = opt.file_type_mask.split( ',' );
			fileMaskRegex = '';
			var tmp = [];
			for ( var i = 0; i < masks.length; ++i ) {
				if ( masks[i] != '' && masks[i] != '*.*' ) {
					tmp.push( masks[i].replace( '*.', '' ) );
				}
			}

			if ( tmp.length ) {
				fileMaskRegex = new RegExp( '.*\\.(' + tmp.join( '|' ) + ')$', 'i' );
			}
			else {
				fileMaskRegex = /.*/;
				fileMaskRegex = new RegExp( '.*$', 'i' );
				opt.file_type_mask = '*.*';
			}
		}
		else {
			opt.file_type_mask = '*.*';
		}

		if ( file_upload_limit == undefined ) {
			var file_upload_limit = 1;
		}

		if ( file_queue_limit == undefined ) {
			var file_queue_limit = 1;
		}

		if ( max_upload_files == undefined ) {
			var max_upload_files = 1;
		}

		var url,
			cmsurl = Config.get( 'portalurl' ),
			session_id = Desktop.SessionID,
			upload_max_filesize = Config.get( 'upload_max_filesize', '1M' ),
			max_file_uploads = Config.get( 'max_file_uploads', '1' ),
			post_max_size = Config.get( 'post_max_size', '1M' );

		upload_max_filesize = (opt.max_file_size || upload_max_filesize);
		file_queue_limit = (opt.file_queue_limit || (max_file_uploads || file_queue_limit));
		max_upload_files = (opt.max_upload_files || max_upload_files);

		if ( post_max_size < upload_max_filesize ) {
			//console.log('Post size is lower as upload_max_filesize size ');
		}

		var warnSet = false;
		var formatedMaxFileSize = Tools.formatSize( Tools.unformatSize( upload_max_filesize ) );

		var activeWin = Core.getContent();

		$( 'span.allowed-filesize', uploadform ).text( 'Maximale Dateigröße: %s'.replace( '%s', formatedMaxFileSize ) );
		$( 'span.allowed-extensions', uploadform ).text( 'Erlaubt sind: %s'.replace( '%s', opts.file_type_mask ) );

		var uploadButton = dropControl.find( '#start-upload-btn:first' );
		var cancelButton = dropControl.find( '#cancel-upload-btn:first' );

		if ( !uploadButton.length ) {
			uploadButton = $( '<span id="start-upload-btn" />' ).addClass('btn btn-primary').append( 'Upload' );
			uploadButton.insertAfter( dropControl.find( 'span.browse:first' ) );
		}
        else {
            uploadButton.addClass('btn btn-primary');
        }

		if ( !cancelButton.length ) {
			cancelButton = $( '<span id="cancel-upload-btn" />' ).addClass('btn btn-default').append( 'Abbrechen' );
			cancelButton.insertAfter( dropControl.find( 'span.browse:first' ) );
		}
        else {
            cancelButton.addClass('btn btn-default');
        }


		var $uploadButton = $( '#start-upload-btn:first', uploadform );
		var $cancelButton = $( '#cancel-upload-btn:first', uploadform );
		var queued = 0; //queued files counter

		$uploadButton.hide();
		$cancelButton.hide();

		$uploadButton.unbind();
		$uploadButton.on( 'click', function ( e )
		{
			if ( parseInt( queued ) > 0 ) {//if queued files

				if ( typeof opt.onUploadStart === 'function' ) {
					opt.onUploadStart();
				}

				$( this ).parents( 'form:first' ).on( 'submit', true );//enabling submit

				dropControl.trigger( 'upload' );

				$( this ).hide();
				$cancelButton.css( {
					display: 'inline-block'} );
			}
		} );

		$cancelButton.unbind();
		$cancelButton.on( 'click', function ( e )
		{

			if ( $ul.find( 'li' ).length ) {
				//if queued files
				dropControl.trigger( 'cancelAll' );
			}

			$( this ).hide();
			$ul.empty().hide();

			// all uploads done
			if ( typeof opt.onComplite == 'function' ) {
				opt.onComplite( false );
			}

		} );

		var dropbox, message, maxFilesCount;
		var upload_tpl = $( '<li class="working">'
			+ '<div class="progressbar"><div class="bar" style="width:0%;"></div></div>'
			+ '<div class="file-info"></div><div class="control"><span class="start"></span><span class="cancel"></span></div></li>' );

		var dropbox = dropControl, message = dropbox.next();

		// dropbox.addClass('dragAndDropUploadZone');

		if ( !message.hasClass( 'upload-message' ) ) {
			message = $( '<div class="upload-message dragAndDropUploadZone"></div>' );
			message.insertAfter( dropbox );
		}
		else {
			message.addClass( 'dragAndDropUploadZone' );
		}

		if ( file_upload_limit == undefined ) {
			var file_upload_limit = 1;
		}

		if ( file_queue_limit == undefined ) {
			var file_queue_limit = 1;
		}

		if ( max_upload_files == undefined ) {
			var max_upload_files = 1;
		}

		var url,
			cmsurl = Config.get( 'portalurl', '' ),
			session_id = Desktop.SessionID,
			upload_max_filesize = Config.get( 'upload_max_filesize', '1M' ),
			max_file_uploads = Config.get( 'max_file_uploads', '1' ),
			post_max_size = Config.get( 'post_max_size', '1M' );

		upload_max_filesize = (opt.max_file_size || upload_max_filesize);
		file_queue_limit = (opt.file_queue_limit || (max_file_uploads || file_queue_limit));
		max_upload_files = (opt.max_upload_files || max_upload_files);

		if ( post_max_size < upload_max_filesize ) {
			// console.log('Post size is lower as upload_max_filesize size ');
		}


		if ( cmsurl.substr( cmsurl.length - 1, cmsurl.length ) != '/' ) {
			url = cmsurl + '/';
		}
		else {
			url = cmsurl;
		}

		var _types = opt.file_type_mask || '*.*';
		var types = _types.split( ',' );

		var postparams = {
			"sid": session_id,
			//    "swfupload_sid": session_id,
			//    "is_flash": true,
			"adm": opt.postParams.adm,
			"action": opt.postParams.action,
			// "setpage": webSite,
			"uploadpath": opt.postParams.uploadpath,
			//     "swfupload": 1,
			"ajax": 1
		};

		if ( !opt.postParams.uploadpath ) {
			delete postparams.uploadpath;
		}

		if ( opt.type == 'gal' ) {
			postparams = {
				"sid": session_id,
				//   "swfupload_sid": session_id,
				//     "is_flash": true,
				"adm": opt.postParams.adm,
				// "setpage": webSite,
				"plugin": opt.postParams.plugin,
				"action": opt.postParams.action,
				"galid": opt.postParams.galid,
				//         "swfupload": 1,
				"ajax": 1
			};
		}

		if ( opt.postParams ) {
			postparams = $.extend( {}, postparams, opt.postParams );
		}

		if ( opt.dropHereLabel && dropControl.find( opt.dropHereLabel ).length ) {
			dropControl.on( 'dragenter',function ()
			{
				$( this ).find( opts.dropHereLabel ).addClass( 'drag-over' );
			} ).on( 'dragover',function ()
				{
					$( this ).find( opts.dropHereLabel ).addClass( 'drag-over' );
				} ).on( 'dragleave',function ()
				{
					$( this ).find( opts.dropHereLabel ).removeClass( 'drag-over' );
				} ).on( 'drop', function ()
				{
					$( this ).find( opts.dropHereLabel ).removeClass( 'drag-over' );
				} );
		}

		dropControl.filedrop( {
			// The name of the $_FILES entry:
			fallbackInput: fallbackInput,
			paramname: (opt.filePostParamName ? opt.filePostParamName : 'Filedata'),
			autoUpload: false,
			queuewait: 10,
			refresh: 500,
			queuefiles: file_queue_limit,
			maxfiles: max_upload_files,
			maxfilesize: upload_max_filesize,
			url: opt.url,
			data: postparams,
			uploadFinished: function ( index, file, response, timeDiff, xhr )
			{
				var id = gethash( file.name );
				var uploadc = $( 'li#' + id );

				if ( response ) {
					queued--;
					uploadc.removeClass( 'working' ).addClass( 'done' );
					uploadc.find( '.progressbar,.filespeed,.filesize' ).hide();

					if ( Tools.responseIsOk( response ) ) {
						// for external events (eg: create thumb or other functions)
						if ( typeof opt.onSuccess === 'function' ) {
							opt.onSuccess( response, null, file, uploadc );
						}
						else {
							setTimeout( function ()
							{
								uploadc.fadeOut( 400, function ()
								{
									$( this ).remove();
								} );
							}, 2000 );
						}
					} else {
						uploadc.addClass( 'upload-error' );

						if ( Tools.isFunction( opt.onError ) ) {
							opt.onError( response, null, file, uploadc );
						}
						else {
							uploadc.find( '.progressbar,.filespeed,.filesize' ).hide();
							uploadc.find( '.filename' ).empty().append( (response.error ? response.error : (response.msg ? response.msg : 'Error')) );

							setTimeout( function ()
							{
								uploadc.fadeOut( 400, function ()
								{
									$( this ).remove();
								} );
							}, 4000 );
						}
					}
				}

				if ( !queued ) {
					$cancelButton.hide();
					$uploadButton.hide();

					postparams.removeRelSession = 1;
					$.post( url + opt.url, postparams, function ( dat )
					{
					} );
					// all uploads done
					if ( typeof opt.onComplite == 'function' ) {
						opt.onComplite( response, null, file, uploadc );
					}

					$ul.hide();
				}

			},
			error: function ( err, file )
			{
				queued--;

				opts = $( this ).parents( 'form:first' ).data( 'upload-opts' );
				switch ( err ) {
					case 'BrowserNotSupported':
						message = 'Your browser does not support HTML5 file uploads!';
						break;
					case 'TooManyFiles':
						message = 'Too many files! Please select ' + file_queue_limit + ' at most! (configurable)';
						break;
					case 'FileTooLarge':
						message = file.name + ' is too large! Please upload files up to ' + Tools.formatSize( Tools.unformatSize( upload_max_filesize ) );
						break;
					default:
						message = 'Upload error!'
						break;
				}

				if ( !file ) {
					jAlert( message );

					if ( !queued ) {
						$cancelButton.hide();
						$uploadButton.hide();
					}
				}

				var id = gethash( file.name );
				var uploadc = $( '#' + id );

				uploadc.find( '.progressbar,.filespeed,.filesize' ).hide();
				uploadc.find( '.filename' ).text( message );

				if ( !queued ) {
					postparams.removeRelSession = 1;
					$.post( url + opt.url, postparams, function ( dat )
					{
					} );
					$cancelButton.hide();
					$uploadButton.hide();
					$ul.hide();
				}
			},
			onCancel: function ( file )
			{
				queued--;

				var id = gethash( file.name );
				var uploadc = $( '#' + id );
				uploadc.find( '.progressbar,.filespeed,.filesize' ).hide();

				setTimeout( function ()
				{
					uploadc.fadeOut( 400, function ()
					{
						$( this ).remove();
					} );
				}, 2000 );

				if ( !queued ) {
					postparams.removeRelSession = 1;
					$.post( url + opt.url, postparams, function ( dat )
					{
					} );

					$cancelButton.hide();
					$uploadButton.hide();

					// all uploads done
					if ( typeof opt.onComplite == 'function' ) {
						opt.onComplite( false, null, file, uploadc );
					}

					$ul.hide();
				}
			},
			// Called before each upload is started
			beforeEach: function ( file )
			{
				opts = $( this ).parents( 'form:first' ).data( 'upload-opts' );
				if ( !validateFile( file ) ) {
					return false;
				}
			}, uploadStarted: function ( file, hash )
			{

			},
			progressUpdated: function ( index, file, currentProgress )
			{
				var id = gethash( file.name );
				$( '#' + id ).find( '.progressbar' ).show();
				$( '#' + id ).find( '.bar' ).css( {width: (currentProgress + '%')} );
			},
			speedUpdated: function ( index, file, speed, loaded, diffTime )
			{
				var id = gethash( file.name );

				var formatedSpeed = '';
				if ( parseFloat( speed ) > 1024.0 ) {
					formatedSpeed = (parseFloat( speed / 1024 ).toFixed( 2 )).toString() + 'MB/s';
				} else if ( parseFloat( speed ) < 1024.0 ) {
					formatedSpeed = (parseFloat( speed ).toFixed( 2 )).toString() + 'KB/s';
				}

				$( '#' + id ).find( '.filespeed' ).text( formatedSpeed ).show();
			},
			uploadAbort: function ( e, xhr, file )
			{
				var id = gethash( file.name );
				$( '#' + id ).addClass( 'abort' ).find( '.progressbar,.filespeed,.filesize' ).hide();
				$( '#' + id ).find( '.filename' ).text( 'Upload der Datei `%s` abgebrochen.'.replace( '%s', file.name ) );

				setTimeout( function ()
				{
					$( '#' + id ).fadeOut( 400, function ()
					{
						$( this ).remove();
					} );
				}, 2000 );

				queued--;

				if ( !queued ) {
					$cancelButton.hide();
					$uploadButton.hide();

					postparams.removeRelSession = 1;
					$.post( url + opt.url, postparams, function ( dat )
					{
					} );

					if ( typeof opt.onComplite == 'function' ) {
						opt.onComplite( false, null, file, uploadc );
					}

					$ul.hide();

					if ( queued < 0 ) {
						queued = 0;

					}
				}
			},
			add: function ( file )
			{

				if ( queued <= 0 && $ul.find( 'li' ).length ) {
					$ul.empty();
					queued = 0;
				}

				if ( file.size > Tools.unformatSize( upload_max_filesize ) ) {
					Notifier.warn( 'Die Datei %f ist größer als %s.<br/>Die maximale Dateigröße beträgt %s!'.replace( '%s', Tools.formatSize( Tools.unformatSize( upload_max_filesize ) ) ).replace( '%f', file.name ) );
					return false;
				}

				if ( !validateFile( file ) ) {
					Notifier.warn( 'Die Datei "%f" hat keine der erlaubten Endung `%s` erlaubt'.replace( '%s', _types ).replace( '%f', file.name ) );
					return false;
				}

				queued++;

				var id = gethash( file.name );
				$( upload_tpl ).clone( false, false ).attr( 'id', id ).data( file ).appendTo( $ul );

				$( '#' + id ).find( '.file-info' ).append( $( '<span class="filename"/>' ).text( file.name ) );
				$( '#' + id ).find( '.file-info' ).append( $( '<span class="filesize"/>' ).text( Tools.formatSize( file.size ) ) );
				$( '#' + id ).find( '.file-info' ).append( $( '<span class="filespeed"/>' ) );
				$( '#' + id ).find( '.progressbar .bar' ).width( '0%' );

				$ul.show();

				$( '#' + id ).find( '.cancel' ).click( function ( e )
				{
					if ( dropControl.trigger( 'cancel', $( e.target ).parents( 'li:first' ).data() ) === true ) {
						queued--;
						$( '#' + id ).addClass( 'abort' ).find( '.progressbar,.filespeed,.filesize' ).hide();
						$( '#' + id ).find( '.filename' ).text( 'Upload der Datei `%s` abgebrochen.'.replace( '%s', file.name ) );

						setTimeout( function ()
						{
							$( '#' + id ).fadeOut( 400, function ()
							{
								$( this ).remove();
							} );
						}, 2000 );

						if ( !queued ) {
							$cancelButton.hide();
							$uploadButton.hide();
							postparams.removeRelSession = 1;
							$.post( url + opt.url, postparams, function ( dat )
							{
							} );
							if ( queued < 0 ) {
								queued = 0;
							}
						}
					}
				} );

				$uploadButton.show();

				if ( typeof opt.onAdd == 'function' ) {
                    opt.onAdd();
				}


				return true;
			}
		} );

		function gethash( s )
		{
			var char, hash, i, len, test, _i;
			hash = 0;
			len = s.length;
			if ( len === 0 ) {
				return hash;
			}
			for ( i = _i = 0; 0 <= len ? _i <= len : _i >= len; i = 0 <= len ? ++_i : --_i ) {
				char = s.charCodeAt( i );
				test = ((hash << 5) - hash) + char;
				if ( !isNaN( test ) ) {
					hash = test & test;
				}
			}
			return 'file-' + Math.abs( hash );
		}

		function validateFile( file )
		{
			var currentMime = file.type;
			var regex = '';

			if ( currentMime.match( /^image\// ) ) {
				file.isImage = true;
			}
			else {
				file.isImage = false;
			}

			if ( !types.length ) {
				return true;
			}

			if ( fileMaskRegex ) {
				if ( !file.name.match( fileMaskRegex ) /* fileMaskRegex.test(file.name)*/ ) {
					return false;
				}

				return true;
			}

			for ( var i = 0; i < types.length; ++i ) {
				if ( types[i].length ) {
					if ( types[i] != '*.*' && types[i] ) {
						var strExt = types[i].split( '.' );
						if ( !strExt[1] ) {
							continue;
						}

						var val = Tools.getMime( strExt[1] );

						if ( val != false ) {
							if ( Tools.isObject( val ) ) {
								regex += val.join( '|' );
							}
							else if ( Tools.isString( val ) ) {
								regex += (regex != '' ? '|' + val : val);
							}
						}
					}
				}
			}

			if ( regex !== '' ) {
				regex = regex.replace( '/', '\/' );
				regex = regex.replace( '.', '\.' );

				var reg = new RegExp( '(' + regex + ')', 'i' );
				if ( !reg.test( currentMime ) ) {
					jAlert( 'This Filetype is not allowed! Only Filetype: ' + types, 'Upload Error...' );
					// Returning false will cause the
					// file to be rejected
					return false;
				}
			}

			return true;
		}

		function createPreview( file, len )
		{

			if ( max_upload_files === 1 ) {
				$( '.dragAndDropUploadZone.preview', $( '#' + Win.windowID ) ).remove();
			}

			var uploadc = $.data( file, 'uploaddata' );
			var preview = uploadc, image = $( 'img', preview );
			var reader = new FileReader();

			preview.find( '.item-errormessage' ).empty().hide();

			if ( file.isImage && opt.type != 'gal' ) {
				reader.onload = function ( e )
				{
					// e.target.result holds the DataURL which
					// can be used as a source of the image:
					image.attr( 'src', e.target.result );
					image.attr( 'width', 100 ).height( 100 );
				};
				preview.find( 'img' ).hide();
			}
			else {
				preview.find( 'img' ).remove();
			}

			// Reading the file as a DataURL. When finished,
			// this will trigger the onload function above:
			reader.readAsDataURL( file );

			message.hide();

			var _after = message;

			if ( message.parent().find( '.upload-file' ).length ) {
				_after = message.parent().find( '.upload-file:last' );
			}

			preview.insertAfter( _after );

			// Associating a preview container
			// with the file, using jQuery's $.data():
			//$.data(file, 'uploadcontainer').append(preview);
			//$.data(file, 'preview', preview);
		}

		function showMessage( msg )
		{
			message.html( msg );
		}

		/*
		 return;
		 }
		 else
		 {
		 if (typeof SWFUpload == 'undefined')
		 {
		 Loader.require('public/html/js/swfupload/swfupload.js', function () {
		 setTimeout(function () {
		 Tools.UploadControl(opts);
		 }, 50);
		 });
		  }
		 else
		 {
		 Tools.UploadControl(opts);
		 }
		 }
		 */
	},
};

// ==========================================
// Publish/Unpublish per Ajax // ==========================================
function changePublish( imageid, url, callback )
{
	var orgsrc = $( '#' + imageid, $( '#' + Win.windowID ) ).attr( 'src' );
	$( '#' + imageid, $( '#' + Win.windowID ) ).attr( 'src', Config.get( 'backendImagePath', '' ) + 'loading.gif' );
	url = url.replace( "/&amp;/", "&" );
	url = url + '&ajax=1';
	setTimeout( function ()
	{
		$.get( url, {}, function ( data )
		{
			if ( Tools.responseIsOk( data ) ) {
				if ( typeof listViewTbl != "undefined" && listViewTbl != '' && listViewTbl != null ) {
					eval( listViewTbl + '.Reload(\'' + document.location + '\')' );
				}
				else {
					console.log( typeof callback );

					if ( data.msg && data.msg == '0' ) {
						$( '#' + imageid, $( '#' + Win.windowID ) ).attr( 'src', Config.get( 'backendImagePath', '' ) + 'offline.png' );
						if ( typeof callback == 'function' ) {
							callback();
						}
						return false;
					}

					if ( data.msg && data.msg == '1' ) {
						$( '#' + imageid, $( '#' + Win.windowID ) ).attr( 'src', Config.get( 'backendImagePath', '' ) + 'online.png' );
						if ( typeof callback == 'function' ) {
							callback();
						}
						return false;
					}

					if ( orgsrc.match( /online\.(gif|png)/ig ) ) {
						$( '#' + imageid, $( '#' + Win.windowID ) ).attr( 'src', Config.get( 'backendImagePath', '' ) + 'offline.png' );
					}
					else {
						$( '#' + imageid, $( '#' + Win.windowID ) ).attr( 'src', Config.get( 'backendImagePath', '' ) + 'online.png' );
					}

					if ( typeof data.msg != "undefined" ) {
						Notifier.info( data.msg );
					}

					if ( typeof callback == 'function' ) {
						callback();
					}
				}
			}
			else {
				$( '#' + imageid, $( '#' + Win.windowID ) ).attr( 'src', orgsrc );
				alert( "Error:\r\n" + data.msg );
			}
		}, 'json' );
	}, 100 );

	return false;

}

// ==========================================
// CookieRegistry
// ==========================================
function CookieRegistry()
{
	var self = this;
	var registryName = '';
	var rawCookie = '';
	var cookie = {};
	this.initialize = function ( name )
	{
		self.registryName = name;
		name = name + '=';
		var cookies = document.cookie.split( ';' );
		for ( i = 0; i < cookies.length; i++ ) {
			var cookie = cookies[i];
			while ( cookie.charAt( 0 ) == ' ' )
				cookie = cookie.substring( 1, cookie.length );
			if ( cookie.indexOf( name ) == 0 )
				self.rawCookie = decodeURIComponent( cookie.substring( name.length, cookie.length ) );
		}
		if ( self.rawCookie ) {
			self.cookie = eval( '(' + self.rawCookie + ')' );
		}
		else {
			self.cookie = {};
		}
		self.write();
	};
	this.get = function ( name, def )
	{
		def = typeof def != 'undefined' ? def : false;
		return typeof self.cookie[name] != 'undefined' ? self.cookie[name] : def;
	};
	this.set = function ( name, value )
	{
		self.cookie[name] = value;
		self.write();
	};
	this.erase = function ( name )
	{
		if ( name ) {
			delete self.cookie[name];
		}
		else {
			self.cookie = {};
		}
		self.write();
	};
	this.encode = function ()
	{
		var results = [];
		for ( var property in self.cookie ) {
			var value = self.cookie[property];
			if ( typeof value != "number" && typeof value != "boolean" ) {
				value = '"' + value + '"';
			}
			results.push( '"' + property + '":' + value );
		}
		return '{' + results.join( ', ' ) + '}';
	};
	this.write = function ()
	{
		var date = new Date();
		date.setTime( date.getTime() + Config.get( 'cookie_timer', 3600 ) );
		var expires = "; expires=" + date.toGMTString();
		document.cookie = self.registryName + "=" + self.encode() + expires + "; path=/";
	};
}
var Cookie = new CookieRegistry;
Cookie.initialize( cookiePrefix + '_registry' );

/* Copyright (c) 2006 Mathias Bank (http://www.mathias-bank.de)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) 
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 * 
 * Thanks to Hinnerk Ruemenapf - http://hinnerk.ruemenapf.de/ for bug reporting and fixing.
 */
jQuery.extend( {
	/**
	 * Returns get parameters.
	 *
	 * If the desired param does not exist, null will be returned      *
	 * @example value = $.getURLParam("paramName");
	 */
	getURLParam: function ( strParamName, str )
	{
		var strReturn = "";
		var strHref = window.location.href;
		var bFound = false;
		if ( typeof str == 'string' ) {
			strHref = str;
		}
		var cmpstring = strParamName + "=";
		var cmplen = cmpstring.length;
		if ( strHref.indexOf( "?" ) > -1 ) {
			var strQueryString = strHref.substr( strHref.indexOf( "?" ) + 1 );
			var aQueryString = strQueryString.split( "&" );
			for ( var iParam = 0; iParam < aQueryString.length; iParam++ ) {
				if ( aQueryString[iParam].substr( 0, cmplen ) == cmpstring ) {
					var aParam = aQueryString[iParam].split( "=" );
					strReturn = aParam[1];
					bFound = true;
					break;
				}
			}
		}
		if ( bFound == false )
			return null;
		return strReturn;
	}
} );

var jQueryAppend, jQueryPrepend, jQueryAppendTo, jQueryBefore, jQueryAfter;
if ( jQuery ) {
	jQueryAppend = $.fn.append;
	jQueryPrepend = $.fn.prepend;
	jQueryAppendTo = $.fn.appendTo;
	jQueryBefore = $.fn.before;
	jQueryAfter = $.fn.after;
}

// ==========================================
(function ( $ )
{
	$.fn.upload = function ( opts )
	{
		return this.each( function ()
		{
			var self = this, uploadControl = $( this );

			if ( opts.refresh === true ) {
				if ( typeof opts.postParams == 'object' ) {
					$( this ).filedrop( {refresh: true, data: opts.postParams} );
				}

				return;
			}

			var $ul = $( this ).parents( 'form:first' ).find( 'ul.dropped-files' );
			if ( !$ul.length ) {
				Debug.error( 'Invalid List for dropped files! create a ul.dropped-files element' );
				return;
			}

			var uploadform = $( this ).parents( 'form:first' );
			var fallbackInput = $( this ).find( 'span.browse' ).parent().find( 'input' );
			$ul.empty().hide();

			$( this ).removeData();
			$( this ).unbind();

			uploadform.unbind();
			uploadform.removeData();

			this.opts = opts;

			$( this ).find( 'input[type=file]' ).unbind();
			$( this ).find( 'span.browse' ).unbind( 'click.upload' );
			$( this ).find( 'span.browse' ).bind( 'click.upload', function ()
			{
				// Simulate a click on the file input button
				// to show the file browser dialog
				$( this ).parent().find( 'input[type=file]' ).click();
			} );

			self.fileMaskRegex = '.*';

			if ( typeof self.opts.file_type_mask == 'string' ) {
				var masks = self.opts.file_type_mask.split( ',' );
				self.fileMaskRegex = '';
				var tmp = [];
				for ( var i = 0; i < masks.length; ++i ) {
					if ( masks[i] != '' && masks[i] != '*.*' ) {
						tmp.push( masks[i].replace( '*.', '' ) );
					}
				}
				if ( tmp.length ) {
					self.fileMaskRegex = new RegExp( '.*\\.(' + tmp.join( '|' ) + ')$', 'i' );
				}
				else {
					self.fileMaskRegex = /.*/;
					self.fileMaskRegex = new RegExp( '.*$', 'i' );
					self.opts.file_type_mask = '*.*';
				}
			}
			else {
				self.opts.file_type_mask = '*.*';
			}

			self.url,
				self.cmsurl = Config.get( 'portalurl' ),
				self.session_id = Desktop.SessionID,
				self.upload_max_filesize = Config.get( 'upload_max_filesize', '1M' ),
				self.max_file_uploads = Config.get( 'max_file_uploads', '1' ),
				self.post_max_size = Config.get( 'post_max_size', '1M' );

			self.upload_max_filesize = (self.opts.max_file_size || self.upload_max_filesize);
			self.file_queue_limit = (self.opts.file_queue_limit || (self.max_file_uploads || self.file_queue_limit));
			self.max_upload_files = (self.opts.max_upload_files || self.max_upload_files);

			if ( self.post_max_size < self.upload_max_filesize ) {
				console.log( 'Post size is lower as upload_max_filesize size ' );
			}

			var warnSet = false;
			var formatedMaxFileSize = Tools.formatSize( Tools.unformatSize( self.upload_max_filesize ) );

			$( this ).find( 'span.allowed-filesize' ).text( 'Maximale Dateigröße: %s'.replace( '%s', formatedMaxFileSize ) );
			$( this ).find( 'span.allowed-extensions' ).text( 'Erlaubt sind: %s'.replace( '%s', self.opts.file_type_mask ) );

			if ( !$( this ).find( '#start-upload-btn' ).length ) {
				$( '<span id="start-upload-btn" />' ).append( 'Upload' ).insertAfter( $( this ).find( 'span.browse' ) )
			}

			if ( !$( this ).find( '#cancel-upload-btn' ).length ) {
				$( '<span id="cancel-upload-btn" />' ).append( 'Abbrechen' ).insertAfter( $( this ).find( 'span.browse' ) )
			}

			var queued = 0; //queued files counter
			self.uploadButton = $( this ).find( '#start-upload-btn' );
			self.cancelButton = $( this ).find( '#cancel-upload-btn' );

			self.uploadButton.hide();
			self.cancelButton.hide();

			self.uploadButton.unbind();
			self.uploadButton.on( 'click', function ( e )
			{
				if ( parseInt( queued ) > 0 ) {//if queued files

					if ( typeof self.opts.onUploadStart === 'function' ) {
						self.opts.onUploadStart();
					}

					uploadform.on( 'submit', true );//enabling submit

					$( self ).trigger( 'upload' );
					$( this ).hide();
					self.cancelButton.css( {
						display: 'inline-block'
					} );
				}
			} );

			self.cancelButton.unbind();
			self.cancelButton.on( 'click', function ( e )
			{

				if ( $ul.find( 'li' ).length ) {
					//if queued files
					$( self ).trigger( 'cancelAll' );
				}

				$( this ).hide();
				$ul.empty().hide();

				// all uploads done
				if ( typeof self.opts.onComplite == 'function' ) {
					self.opts.onComplite( false );
				}
			} );

			var dropbox;
			self.upload_tpl = $( '<li class="working">'
				+ '<div class="progressbar"><div class="bar" style="width:0%;"></div></div>'
				+ '<div class="file-info"></div><div class="control"><span class="start"></span><span class="cancel"></span></div></li>' );

			var dropbox = $( this );
			self.message = $( this ).next();

			if ( !self.message.hasClass( 'upload-message' ) ) {
				self.message = $( '<div class="upload-message dragAndDropUploadZone"></div>' );
				self.message.insertAfter( $( this ) );
			} else {
				self.message.addClass( 'dragAndDropUploadZone' );
			}

			if ( self.file_upload_limit == undefined ) {
				self.file_upload_limit = 1;
			}

			if ( self.file_queue_limit == undefined ) {
				self.file_queue_limit = 1;
			}

			if ( self.max_upload_files == undefined ) {
				self.max_upload_files = 1;
			}

			var _types = self.opts.file_type_mask || '*.*';
			self.types = _types.split( ',' );

			self.postparams = {
				"sid": self.session_id,
				//    "swfupload_sid": session_id,
				//    "is_flash": true,
				"adm": self.opts.postParams.adm,
				"action": self.opts.postParams.action,
				// "setpage": webSite,
				"uploadpath": self.opts.postParams.uploadpath,
				//     "swfupload": 1,
				"ajax": 1
			};

			if ( !self.opts.postParams.uploadpath ) {
				delete self.postparams.uploadpath;
			}

			if ( self.opts.type == 'gal' ) {
				self.postparams = {
					"sid": self.session_id,
					//   "swfupload_sid": session_id,
					//     "is_flash": true,
					"adm": self.opts.postParams.adm,
					// "setpage": webSite,
					"plugin": self.opts.postParams.plugin,
					"action": self.opts.postParams.action,
					"galid": self.opts.postParams.galid,
					//         "swfupload": 1,
					"ajax": 1
				};
			}

			if ( self.opts.postParams ) {
				self.postparams = $.extend( {}, self.postparams, self.opts.postParams );
			}

			if ( self.opts.dropHereLabel && $( this ).find( self.opts.dropHereLabel ).length ) {
				$( this ).on( 'dragenter',function ()
				{
					$( this ).find( self.opts.dropHereLabel ).addClass( 'drag-over' );
				} ).on( 'dragover',function ()
					{
						$( this ).find( self.opts.dropHereLabel ).addClass( 'drag-over' );
					} ).on( 'dragleave',function ()
					{
						$( this ).find( self.opts.dropHereLabel ).removeClass( 'drag-over' );
					} ).on( 'drop', function ()
					{
						$( this ).find( self.opts.dropHereLabel ).removeClass( 'drag-over' );
					} );
			}

			$( this ).filedrop( {
				// The name of the $_FILES entry:
				fallbackInput: fallbackInput,
				paramname: (self.opts.filePostParamName ? self.opts.filePostParamName : 'Filedata'),
				autoUpload: false,
				queuewait: 10,
				refresh: 500,
				queuefiles: self.file_queue_limit,
				maxfiles: self.max_upload_files, maxfilesize: self.upload_max_filesize,
				url: self.opts.url,
				data: self.postparams,
				uploadFinished: function ( index, file, response, timeDiff, xhr )
				{
					var id = gethash( file.name );
					var uploadc = $( 'li#' + id );

					if ( response ) {
						queued--;
						uploadc.removeClass( 'working' ).addClass( 'done' );
						uploadc.find( '.progressbar,.filespeed,.filesize' ).hide();

						if ( Tools.responseIsOk( response ) ) {
							// for external events (eg: create thumb or other functions)
							if ( Tools.isFunction( self.opts.onSuccess ) ) {
								self.opts.onSuccess( response, null, file, uploadc );
							}
							else {
								setTimeout( function ()
								{
									uploadc.fadeOut( 400, function ()
									{
										$( this ).remove();
									} );
								}, 2000 );
							}
						}
						else {
							uploadc.addClass( 'upload-error' );

							if ( Tools.isFunction( self.opts.onError ) ) {
								self.opts.onError( response, null, file, uploadc );
							}
							else {
								uploadc.find( '.progressbar,.filespeed,.filesize' ).hide();
								uploadc.find( '.filename' ).empty().append( (response.error ? response.error : (response.msg ? response.msg : 'Error')) );

								setTimeout( function ()
								{
									uploadc.fadeOut( 400, function ()
									{
										$( this ).remove();
									} );
								}, 4000 );
							}
						}
					}

					if ( !queued ) {
						self.cancelButton.hide();
						self.uploadButton.hide();

						self.postparams.removeRelSession = 1;
						$.post( url + self.opts.url, self.postparams, function ( dat )
						{
						} );
						// all uploads done
						if ( typeof self.opts.onComplite == 'function' ) {
							self.opts.onComplite( response, null, file, uploadc );
						}

						$ul.hide();
					}

				},
				error: function ( err, file )
				{
					queued--;

					switch ( err ) {
						case 'BrowserNotSupported':
							self.message = 'Your browser does not support HTML5 file uploads!';
							break;
						case 'TooManyFiles':
							self.message = 'Too many files! Please select ' + self.file_queue_limit + ' at most! (configurable)';
							break;
						case 'FileTooLarge':
							self.message = file.name + ' is too large! Please upload files up to ' + Tools.formatSize( Tools.unformatSize( upload_max_filesize ) );
							break;
						default:
							self.message = 'Upload error!'
							break;
					}

					if ( !file ) {
						jAlert( self.message );

						if ( !queued ) {
							self.cancelButton.hide();
							self.uploadButton.hide();
						}
					}

					var id = gethash( file.name );
					var uploadc = $( '#' + id );

					uploadc.find( '.progressbar,.filespeed,.filesize' ).hide();
					uploadc.find( '.filename' ).text( self.message );

					if ( !queued ) {
						self.postparams.removeRelSession = 1;
						$.post( url + self.opts.url, self.postparams, function ( dat )
						{
						} );

						self.cancelButton.hide();
						self.uploadButton.hide();
						$ul.hide();
					}
				},
				onCancel: function ( file )
				{
					queued--;

					var id = gethash( file.name );
					var uploadc = $( '#' + id );
					uploadc.find( '.progressbar,.filespeed,.filesize' ).hide();

					setTimeout( function ()
					{
						uploadc.fadeOut( 400, function ()
						{
							$( this ).remove();
						} );
					}, 2000 );

					if ( !queued ) {
						self.postparams.removeRelSession = 1;
						$.post( url + self.opts.url, self.postparams, function ( dat )
						{
						} );

						self.cancelButton.hide();
						self.uploadButton.hide();

						// all uploads done
						if ( typeof self.opts.onComplite == 'function' ) {
							self.opts.onComplite( false, null, file, uploadc );
						}

						$ul.hide();
					}
				},
				// Called before each upload is started
				beforeEach: function ( file )
				{
					if ( !validateFile( file ) ) {
						return false;
					}
				},
				uploadStarted: function ( file, hash )
				{

				},
				progressUpdated: function ( index, file, currentProgress )
				{
					var id = gethash( file.name );
					$( '#' + id ).find( '.progressbar' ).show();
					$( '#' + id ).find( '.bar' ).css( {width: (currentProgress + '%')} );
				},
				speedUpdated: function ( index, file, speed, loaded, diffTime )
				{
					var id = gethash( file.name );

					var formatedSpeed = '';
					if ( parseFloat( speed ) > 1024.0 ) {
						formatedSpeed = (parseFloat( speed / 1024 ).toFixed( 2 )).toString() + 'MB/s';
					} else if ( parseFloat( speed ) < 1024.0 ) {
						formatedSpeed = (parseFloat( speed ).toFixed( 2 )).toString() + 'KB/s';
					}

					$( '#' + id ).find( '.filespeed' ).text( formatedSpeed ).show();
				},
				uploadAbort: function ( e, xhr, file )
				{
					var id = gethash( file.name );
					$( '#' + id ).addClass( 'abort' ).find( '.progressbar,.filespeed,.filesize' ).hide();
					$( '#' + id ).find( '.filename' ).text( 'Upload der Datei `%s` abgebrochen.'.replace( '%s', file.name ) );

					setTimeout( function ()
					{
						$( '#' + id ).fadeOut( 400, function ()
						{
							$( this ).remove();
						} );
					}, 2000 );

					queued--;

					if ( !queued ) {
						self.cancelButton.hide();
						self.uploadButton.hide();

						self.postparams.removeRelSession = 1;
						$.post( url + self.opts.url, self.postparams, function ( dat )
						{
						} );

						if ( typeof self.opts.onComplite == 'function' ) {
							self.opts.onComplite( false, null, file, uploadc );
						}

						$ul.hide();

						if ( queued < 0 ) {
							queued = 0;

						}
					}
				},
				add: function ( file )
				{
					if ( queued <= 0 && $ul.find( 'li' ).length ) {
						$ul.empty();
						queued = 0;
					}

					if ( file.size > Tools.unformatSize( self.upload_max_filesize ) ) {
						Notifier.warn( 'Die Datei %f ist größer als %s.<br/>Die maximale Dateigröße beträgt %s!'.replace( '%s', Tools.formatSize( Tools.unformatSize( self.upload_max_filesize ) ) ).replace( '%f', file.name ) );
						return false;
					}

					if ( !validateFile( file ) ) {
						Notifier.warn( 'Die Datei "%f" hat keine der erlaubten Endung `%s` erlaubt'.replace( '%s', _types ).replace( '%f', file.name ) );
						return false;
					}

					queued++;

					var id = gethash( file.name );
					$( self.upload_tpl ).clone( false, false ).attr( 'id', id ).data( file ).appendTo( $ul );

					$( '#' + id ).find( '.file-info' ).append( $( '<span class="filename"/>' ).text( file.name ) );
					$( '#' + id ).find( '.file-info' ).append( $( '<span class="filesize"/>' ).text( Tools.formatSize( file.size ) ) );
					$( '#' + id ).find( '.file-info' ).append( $( '<span class="filespeed"/>' ) );
					$( '#' + id ).find( '.progressbar .bar' ).width( '0%' );

					$ul.show();

					$( '#' + id ).find( '.cancel' ).click( function ( e )
					{
						if ( uploadControl.trigger( 'cancel', $( e.target ).parents( 'li:first' ).data() ) === true ) {
							queued--;
							$( '#' + id ).addClass( 'abort' ).find( '.progressbar,.filespeed,.filesize' ).hide();
							$( '#' + id ).find( '.filename' ).text( 'Upload der Datei `%s` abgebrochen.'.replace( '%s', file.name ) );

							setTimeout( function ()
							{
								$( '#' + id ).fadeOut( 400, function ()
								{
									$( this ).remove();
								} );
							}, 2000 );

							if ( !queued ) {
								self.cancelButton.hide();
								self.uploadButton.hide();
								self.postparams.removeRelSession = 1;
								$.post( url + self.opts.url, self.postparams, function ( dat )
								{
								} );
								if ( queued < 0 ) {
									queued = 0;
								}
							}
						}
					} );

					if ( typeof self.opts.onAdd == 'function' ) {
						self.opts.onAdd();
					}

					self.uploadButton.css( {
						display: 'inline-block'
					} );

					return true;
				}
			} );

			function gethash( s )
			{
				var char, hash, i, len, test, _i;
				hash = 0;
				len = s.length;
				if ( len === 0 ) {
					return hash;
				}
				for ( i = _i = 0; 0 <= len ? _i <= len : _i >= len; i = 0 <= len ? ++_i : --_i ) {
					char = s.charCodeAt( i );
					test = ((hash << 5) - hash) + char;
					if ( !isNaN( test ) ) {
						hash = test & test;
					}
				}
				return 'file-' + Math.abs( hash );
			}

			function validateFile( file )
			{
				var currentMime = file.type;
				var regex = '';

				if ( currentMime.match( /^image\// ) ) {
					file.isImage = true;
				}
				else {
					file.isImage = false;
				}
				if ( !self.types.length ) {
					return true;
				}

				if ( self.fileMaskRegex ) {
					if ( !file.name.match( self.fileMaskRegex ) /* fileMaskRegex.test(file.name)*/ ) {
						return false;
					}
					return true;
				}

				for ( var i = 0; i < self.types.length; ++i ) {
					if ( self.types[i].length ) {
						if ( self.types[i] != '*.*' && self.types[i] ) {
							var strExt = self.types[i].split( '.' );
							if ( !strExt[1] ) {
								continue;
							}

							var val = Tools.getMime( strExt[1] );

							if ( val != false ) {
								if ( Tools.isObject( val ) ) {
									regex += val.join( '|' );
								}
								else if ( Tools.isString( val ) ) {
									regex += (regex != '' ? '|' + val : val);
								}
							}
						}
					}
				}

				if ( regex !== '' ) {
					regex = regex.replace( '/', '\/' );
					regex = regex.replace( '.', '\.' );

					var reg = new RegExp( '(' + regex + ')', 'i' );
					if ( !reg.test( currentMime ) ) {
						jAlert( 'This Filetype is not allowed! Only Filetype: ' + types, 'Upload Error...' );
						// Returning false will cause the
						// file to be rejected
						return false;
					}
				}

				return true;
			}

			function createPreview( file, len )
			{

				if ( max_upload_files === 1 ) {
					$( '.dragAndDropUploadZone.preview', $( '#' + Win.windowID ) ).remove();
				}

				var uploadc = $.data( file, 'uploaddata' );
				var preview = uploadc, image = $( 'img', preview );
				var reader = new FileReader();

				preview.find( '.item-errormessage' ).empty().hide();

				if ( file.isImage && opts.type != 'gal' ) {
					reader.onload = function ( e )
					{
						// e.target.result holds the DataURL which
						// can be used as a source of the image:
						image.attr( 'src', e.target.result );
						image.attr( 'width', 100 ).height( 100 );
					};
					preview.find( 'img' ).hide();
				}
				else {
					preview.find( 'img' ).remove();
				}

				// Reading the file as a DataURL. When finished,
				// this will trigger the onload function above:
				reader.readAsDataURL( file );

				message.hide();

				var _after = message;

				if ( message.parent().find( '.upload-file' ).length ) {
					_after = message.parent().find( '.upload-file:last' );
				}

				preview.insertAfter( _after );
			}

			function showMessage( msg )
			{
				message.html( msg );
			}

		} );
	};

	$.fn.buildColorPicker = function ( options )
	{
		return this.each( function ()
		{
			var $this = $( this );

			if ( !$this.parent().hasClass( 'input-append' ) ) {
				$this.wrap( '<div class="input-append color"/>' )
				$( '<span class="add-on"><i></i></span>' ).insertAfter( $this );
			}

			var color = $this.val();
			if ( options.color != '' ) {
				color = '#' + options.color;
			}
			$this.addClass( 'colorpicker-input' );
			$this.val( color.replace( '#', '' ) );
			$this.next().find( 'i' ).css( {backgroundColor: '#' + color.replace( '#', '' )} ).on( 'click', function ()
			{

				$( document ).find( 'input.colorpicker-input' ).ColorPicker( 'hide' );
				$this.ColorPicker( 'show' );

				$( document ).unbind( 'click.colorpicker' );
				$( document ).bind( 'click.colorpicker', function ( e )
				{
					if ( !$( e.target ).parents( 'div.colorpicker' ).length && !$( e.target ).parents( 'div.input-append' ).length ) {
						$this.ColorPicker( 'hide' );

					}
				} );
			} );

			$this.ColorPicker( {isInput: false, format: 'hex'} ).on( 'changeColor',function ( ev )
			{
				$this.next().find( 'i' ).css( {backgroundColor: ev.color.toHex()} );
				$this.val( ev.color.toHex().replace( '#', '' ) );
			} ).on( 'hide', function ( ev )
				{
					$this.val( ev.color.toHex().replace( '#', '' ) );
				} );

			// $this.ColorPicker('isInput',  false);
			$this.ColorPicker( 'setValue', '#' + color.replace( '#', '' ) );
			$this.unbind( 'click focus' );
		} );
	};

	$.fn.triggerAll = function ()
	{
		return this.each( function ()
		{
			var $this = $( this );
			var $data = $this.data( 'events' );
			if ( $data ) {
				$.each( $data, function ( k, v )
				{
					$this.trigger( k );
				} );
			}
		} );
	};

	$.fn.disableTextSelection = function ()
	{
		return this.each( function ()
		{

			if ( $( this ).get( 0 ).tagName == 'SELECT' || $( this ).get( 0 ).tagName == 'INPUT' ) {
				return;
			}

			$( this ).find( 'input,textarea' ).css( {
				'-moz-user-select': 'all',
				'-webkit-user-select': 'all',
				'user-select': 'all',
				'-ms-user-select': 'all',
				cursor: 'auto'
			} );

			$( this ).css( {
				'-moz-user-select': 'none',
				'-webkit-user-select': 'none',
				'user-select': 'none',
				'-ms-user-select': 'none',
				cursor: 'default'
			} );
		} );
	};

	$.pagemask = {
		show: function ( label )
		{
			$.alerts._overlay( 'hide' );
			$( "body" ).append( $( '<div id="popup_overlay"></div>' ) );
			$( "#popup_overlay" ).css(
				{
					position: 'absolute',
					zIndex: 999998,
					top: '0',
					left: '0',
					width: '100%',
					'height': $( window ).height()
				} ).hide();

			if ( typeof label != "string" ) {
				label = cmslang.loading;
			}
			if ( typeof label == "string" ) {
				var maskMsgDiv = $( '<div class="loadmask-msg" id="popup_overlay_msg" style="display:none;"></div>' );
				maskMsgDiv.append( '<div>' + label + '</div>' );
				$( 'body' ).append( maskMsgDiv );
				maskMsgDiv.css(
					{
						zIndex: 999999,
						width: maskMsgDiv.width(),
						height: maskMsgDiv.height(),
						position: 'relative',
						left: '50%',
						top: '40%',
						marginLeft: 0 - Math.floor( maskMsgDiv.outerWidth() / 2 )
					} );
				maskMsgDiv.show();
			}
			var maskRmvDiv = $( '<div class="loadmask-remove" id="popup_overlay_remove" style="display:none;"></div>' );

			maskRmvDiv.append(
				$( '<img>' ).attr( {
					src: Config.get( 'backendImagePath' ) + 'cancel.png',
					width: 16,
					height: 16,
					title: ''
				} )
			);

			$( 'body' ).append( maskRmvDiv );
			maskRmvDiv.css(
				{
					opacity: .7,
					zIndex: 999999,
					width: 16,
					height: 16,
					position: 'fixed',
					right: '10px',
					top: '10px',
					cursor: 'pointer'
				} ).hide();
			maskRmvDiv.hover(
				function ()
				{
					$( this ).css(
						{
							opacity: 1
						} );
				}, function ()
				{
					$( this ).css(
						{
							opacity: .7
						} );
				} );

			maskRmvDiv.bind( 'click', function ()
			{
				$.pagemask.hide();
			} );

			$( '#popup_overlay,#popup_overlay_remove' ).show();
		},
		hide: function ()
		{
			$( '#popup_overlay,#popup_overlay_remove' ).hide();
			$( "#popup_overlay" ).remove();
			$( "#popup_overlay_msg" ).remove();
			$( "#popup_overlay_remove" ).remove();
		}
	};

	/**
	 * Displays loading mask over selected element.
	 *
	 * @param label Text message that will be displayed on the top of a mask besides a spinner (optional).
	 *              If not provided only mask will be displayed without a label or a spinner.
	 */
	$.fn.mask = function ( label, timeout )
	{
		if ( $( this ).hasClass( 'masked' ) ) {
			return this;
		}

		if ( typeof timeout != 'undefined' && timeout > 0 ) {
			var element = $( this );

			element.data( "_mask_timeout", setTimeout( function ()
			{
				$.maskElement( element, label );
			}, timeout ) );
		}
		else {
			$.fn.maskElement( $( this ), label );
		}

		return this;
	};

	$.fn.maskElement = function ( element, label )
	{
		//if this element has delayed mask scheduled then remove it and display the new one
		if ( element.data( "_mask_timeout" ) !== undefined ) {
			clearTimeout( element.data( "_mask_timeout" ) );
			element.removeData( "_mask_timeout" );
		}

		if ( element.hasClass( "masked" ) ) {
			$.fn.unmask( element );
		}
		element.addClass( "masked" );
		var height = element.outerHeight( true );
		var width = element.outerWidth( true );

		element.addClass( "masked" );

		//   var height = element.outerHeight(true);
		//   var width = element.outerWidth(true);
		var mask = $( '#masking' ).clone();
		mask.removeAttr( 'id' );
		mask.css( {
			zIndex: 99998
		} ).addClass( 'masking' ).show();

		mask.appendTo( element );

		if ( typeof label == "string" && label != '' ) {
			$( '#masking-msg', mask ).css( {
				zIndex: 99999
			} ).append( label );

			//calculate center position
			$( '#masking-msg', mask )
				.css( "top", (mask.height() / 2) )
				.css( "left", (mask.outerWidth( true ) / 2) - ($( '#masking-msg', mask ).outerWidth( true ) / 2) );
		}
		else {
			$( '#masking-msg', mask ).remove();
		}

		return element;

		var maskDiv = $( '<div class="loadmask">' );

		maskDiv.css( {
			zIndex: 99998
		} );

		maskDiv.height( height ).width( width ).show();
		element.append( maskDiv );

		if ( typeof label == "string" && label != '' ) {
			var bgPath, maskMsgDiv = $( '<div class="loadmask-msg"></div>' );
			var labelDiv = $( '<div/>' );

			var cloned = $( 'body' ).find( '#load-indicator-small' ).clone();

			if ( cloned.length ) {
				cloned.removeAttr( 'id' );
				cloned.show().appendTo( labelDiv );
			}
			else {
				labelDiv.append( '<span class="load-indicator"></span>' );
			}

			labelDiv.append( $( '<span class="load-msg"></span>' ).append( label ) );
			maskMsgDiv.append( labelDiv );
			element.append( maskMsgDiv );

			//calculate center position
			maskMsgDiv.css( "top", Math.round( element.height() / 2 - maskMsgDiv.height() ) );

			//maskMsgDiv.css("top", '50%');
			maskMsgDiv.css( "left", Math.round( element.outerWidth( true ) / 2 - (maskMsgDiv.width() - parseInt( maskMsgDiv.css( "padding-left" ), 10 ) - parseInt( maskMsgDiv.css( "padding-right" ), 10 )) / 2 ) + "px" );

			maskMsgDiv.css( {
				zIndex: 99999
			} );

			maskMsgDiv.show();
		}

		return element;

	};

	/**
	 * Checks if a single element is masked. Returns false if mask is delayed or not displayed.
	 */
	$.fn.isMasked = function ()
	{
		return this.hasClass( "masked" );
	};

	/**      * Removes mask from the element.      */
	$.fn.unmask = function ()
	{

		if ( $( this ).attr( 'id' ) == 'maincontent' ) {
			var self = this;
			$( this ).parent().each( function ()
			{
				$.unmaskElement( $( self ).parent() );
			} );
		} else {
			$.unmaskElement( $( this ) );
		}

	};

	$.unmaskElement = function ( element )
	{
		//if this element has delayed mask scheduled then remove it
		if ( typeof element.data( "_mask_timeout" ) != 'undefined' ) {
			clearTimeout( element.data( "_mask_timeout" ) );
			element.removeData( "_mask_timeout" );
		}

		element.find( '.masking,.loadmask' ).hide().remove();
		element.removeClass( "masked" ).removeClass( "masked-relative" );
		$( "select", element ).removeClass( "masked-hidden" );
	};

	$.fn.disableButton = function ()
	{
		return this.each( function ()
		{
			//   if ($(this).hasClass('pretty-button') || $(this).hasClass('action-button'))
			//   {
			$( this ).attr( 'disabled', 'disabled' );
			$( this ).addClass( 'button-disabled' );
			//   }
		} );
	};

	$.fn.enableButton = function ()
	{
		return this.each( function ()
		{
			//   if ($(this).hasClass('pretty-button') || $(this).hasClass('action-button'))
			//   {
			$( this ).removeAttr( 'disabled' );
			$( this ).removeClass( 'button-disabled' );
			//   }
		} );
	};

	$.fn.disableContext = function ()
	{
		return this.each( function ()
		{
			$( this ).attr( 'disabled', 'disabled' );
			$( this ).addClass( 'disabled' );
		} );
	};

	$.fn.enableContext = function ()
	{
		return this.each( function ()
		{
			// if($(this).hasClass('pretty-button') || $(this).hasClass('action-button')) {
			$( this ).removeAttr( 'disabled' );
			$( this ).removeClass( 'disabled' );             //  }
		} );
	};
	$.fn.cleardefault = function ()
	{
		return this.focus(function ()
		{
			if ( this.value == this.defaultValue ) {
				this.value = "";
			}
		} ).blur( function ()
			{
				if ( !this.value.length ) {
					this.value = this.defaultValue;
				}
			} );
	};

	/**
	 * jQuery-Plugin "addTab"
	 * by Marcel Domke
	 */
	$.fn.dcmsAddTab = function ( label, options )
	{

		var settings = jQuery.extend( {
			tab_container: null, // object
			tabcontent_container: null, // object
			pos: 'after'
		}, options );

		var self = $( this );
		var tabCounter = 0;
		var id = $( this ).find( '.tab:not(.add-tab):last' ).attr( 'id' );
		var x = id.replace( 'tab-', '' );
		tabCounter = x;
		tabCounter++;

		var icon = $( '<span class="icon"></span>' );
		var labeltab = $( '<a href="#tab-content-' + tabCounter + '"></a>' );
		labeltab.append( label );

		var _tab = $( '<li id="tab-' + tabCounter + '" class="tab"></li>' );
		_tab.append( labeltab );
		_tab.append( icon );

		var tab_content = $( '<div id="tab-content-' + tabCounter + '" class="tab-content"><ul class="sortable"></ul></div>' );

		if ( settings.pos == 'after' ) {
			_tab.insertAfter( '#tab-' + x );
		}
		else {
			$( self ).prepend( _tab );
		}

		$( settings.tabcontent_container ).append( tab_content );

		return false;
	};

	/**
	 *
	 */
	$.fn.removeTab = function ()
	{

	};



})( jQuery, window );







// ==========================================

/**
 * taboverflow plugin - allows tabs scrolling when the window's width is too small to display all tabs
 */
(function ( $ )
{

	$.fn.taboverflow = function ( options )
	{

		var opts = $.extend( {}, $.fn.taboverflow.defaults, options );

		$( document ).resize( function ()
		{
			$( '.tabbedMenu' ).toggleOverflowArrow();
		} );

		return this.each( function ()
		{
			var $this = $( this );

			$.fn.taboverflow.prepareDom( $this );
			$.fn.taboverflow.setListWidth( $this );
			$.fn.taboverflow.toggleTabslist( $this );
			$this.toggleOverflowArrow();

			$this.parents( '.tabbedMenuWrap:first' ).find( '.menuScrollRight:first' ).scrollTabs( {"direction": "right"} );
			$this.parents( '.tabbedMenuWrap:first' ).find( '.menuScrollLeft:first' ).scrollTabs( {"direction": "left"} );

		} );
	};

	/**
	 * Wraps the tabs with necessary DIVs and add anchors to scroll left and
	 * right
	 */
	$.fn.taboverflow.prepareDom = function ( $this )
	{

		if ( $this.parents( ".tabHeader" ).find( ".tabbedMenuWrap" ).length === 0 ) {

			$this.wrap( '<div class="tabbedMenuWrap"><div class="tabbedScrollWrap"></div></div>' );
			$this.parents( ".tabHeader" ).find( ".tabbedMenuWrap" ).prepend( '<a href="javascript:void(0);" style="display:none;" class="menuScrollLeft scrollArrows">&nbsp;</a><a href="javascript:void(0);" style="display:none;" class="menuScrollRight scrollArrows">&nbsp;</a>' );
		}
	};

	/**
	 * Sets the width of the UL containing the tabs      * */
	$.fn.taboverflow.setListWidth = function ( $this )
	{
		var accountForBorders, tabsTotalWidth = 0;

		$this.find( 'li' ).each( function ()
		{
			tabsTotalWidth = ($( this ).outerWidth( true ) + (tabsTotalWidth));
		} );

		if ( ($.browser.msie) && ($.browser.version == 7) ) {
			accountForBorders = 10;
		} else {
			accountForBorders = 2;
		}

		tabsTotalWidth = (tabsTotalWidth + accountForBorders);

		$this.width( tabsTotalWidth + "px" );
	};

	$.fn.taboverflow.toggleTabslist = function ( $this )
	{
		var $tabList = $this.parents().prev( ".tabList" );

		$tabList.find( '.tabListLink' ).click( function ( event )
		{
			event.stopPropagation();
			$( this ).siblings( 'ul' ).toggle( "fast" );
		} );

		$tabList.click( function ( event )
		{
			event.stopPropagation();
		} );
	};

	/**      * toggleOverflowArrow plugin to toggle the elements allowing to scroll
	 */
	$.fn.toggleOverflowArrow = function ( options )
	{
		var opts = $.extend( {}, $.fn.toggleOverflowArrow.defaults, options );

		return this.each( function ()
		{
			$this = $( this );

			var tabsTotalWidth = $this.width();
			var wrapperWidth = $this.parents( ".tabbedScrollWrap" ).outerWidth();
			$this.parents( ".tabbedScrollWrap" ).scrollLeft( 0 );

			if ( wrapperWidth > tabsTotalWidth ) {
				$this.parents( ".tabHeader" ).find( ".scrollArrows, .tabList" ).hide();
			} else {
				$this.parents( ".tabHeader" ).find( ".scrollArrows, .tabList" ).show();
			}
		} );
	};

	/**
	 * scrollTabs plugin to allow the scrolling of the tabs
	 */
	$.fn.scrollTabs = function ( options )
	{

		var opts = $.extend( {}, $.fn.scrollTabs.defaults, options );
		var scrollingLength = 200;

		return this.each( function ()
		{
			var $this = $( this );

			$this.unbind().click( function ()
			{
				var maxoffset = $( this ).parent().find( 'ul.tabbedMenu li:last' ).width() + $( this ).parent().find( 'ul.tabbedMenu li:last' ).offset().left;
				var curentoffset = Math.abs( parseInt( $( this ).parent().find( 'ul.tabbedMenu' ).offset().left ) );

				var offset, offsetOrg = $( this ).siblings( ".tabbedScrollWrap" ).scrollLeft();
				switch ( opts.direction ) {
					case "right":
						if ( curentoffset >= maxoffset ) {
							return;
						}
						offset = offsetOrg + scrollingLength;
						break;
					case "left":

						if ( curentoffset <= 0 ) {
							return;
						}

						offset = offsetOrg - scrollingLength;
						break;
				}

				$( this ).siblings( ".tabbedScrollWrap" ).stop().animate( {scrollLeft: offset}, "fast", "linear", function ()
				{

				} );
			} );
		} );

		$.fn.scrollTabs.defaults = {};
	};
})( jQuery, window  );

/*
 * Default text - jQuery plugin for html5 dragging files from desktop to browser
 *
 * Author: Weixi Yen
 *
 * Email: [Firstname][Lastname]@gmail.com
 *
 * Copyright (c) 2010 Resopollution
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Project home:
 *   http://www.github.com/weixiyen/jquery-filedrop
 *
 * Version:  0.1.0
 *
 * Features:
 *      Allows sending of extra parameters with file.
 *      Works with Firefox 3.6+
 *      Future-compliant with HTML5 spec (will work with Webkit browsers and IE9)
 * Usage:
 *  See README at project homepage
 *
 */
;
(function ( $ )
{

	jQuery.event.props.push( "dataTransfer" );

	function reindexArray( array )
	{
		var result = [];
		for ( var key in array )
			result.push( array[key] );
		return result;
	}

	$.fn.filedrop = function ( options )
	{

		var default_opts = {
				autoUpload: true,
				fallback_id: '',
				fallbackInput: false,
				url: '',
				refresh: 1000,
				requestType: 'POST',
				paramname: 'userfile',
				allowedfiletypes: [],
				maxfiles: 25, // Ignored if queuefiles is set > 0
				maxfilesize: 1, // MB file size limit
				queuefiles: 0, // Max files before queueing (for large volume uploads)
				queuewait: 200, // Queue wait time if full
				data: {},
				headers: {},
				add: empty,
				drop: empty,
				dragStart: empty,
				dragEnter: empty,
				dragOver: empty,
				dragLeave: empty,
				docEnter: empty,
				docOver: empty,
				docLeave: empty,
				beforeEach: empty,
				afterAll: empty,
				rename: empty,
				error: function ( err, file, i, status )
				{
					jAlert( err );
				},
				uploadStarted: empty,
				uploadFinished: empty,
				progressUpdated: empty,
				globalProgressUpdated: empty,
				speedUpdated: empty,
				uploadError: empty,
				uploadAbort: function ( event, xhr, file )
				{

				},
				onCancel: false,
				beforeSend: false,
				stop: false
			},
			errors = ["BrowserNotSupported", "TooManyFiles", "FileTooLarge", "FileTypeNotAllowed", "NotFound", "NotReadable", "AbortError", "ReadError"], doc_leave_timer, stop_loop = false,
			files_count = 0,
			files, stop = false;

		var uploadFiles, uploadFiles_count = 0, opts = {};

		if ( options.refresh === true ) {
			if ( typeof options.data == 'object' ) {
				opts.data = options.data;
			}

			if ( typeof options.url == 'string' ) {
				opts.url = options.url;
			}

			if ( typeof options.filePostParamName == 'string' ) {
				opts.paramname = options.filePostParamName;
			}

			return this;
		}

		if ( options.stop && options.stop === true ) {
			stop = true;
			return this;
		} else {
			opts = $.extend( {}, default_opts, options );
			stop = false;
			var global_progress = [];
		}

		this.on( 'drop', drop ).on( 'dragstart', opts.dragStart ).on( 'dragenter', dragEnter ).on( 'dragover', dragOver ).on( 'dragleave', dragLeave );

		this.on( 'upload', function ( e )
		{
			uploadFiles_count = files.length;

			if ( uploadFiles_count ) {
				uploadFiles = [];
				for ( var i = 0; i < uploadFiles_count; i++ ) {
					uploadFiles.push( files[i].originalFile );
				}

				upload();
			}
		} );

		this.on( 'cancelAll', function ()
		{
			if ( uploadFiles && uploadFiles.length ) {
				for ( var i = 0; i < uploadFiles.length; i++ ) {
					if ( uploadFiles[i].xhr ) {
						uploadFiles[i].xhr.abort();
					}
				}
			}
		} );

		this.on( 'cancel', function ( event, data )
		{
			if ( data && data.name ) {
				uploadFiles_count = files.length;

				if ( uploadFiles && uploadFiles.length ) {
					for ( var i = 0; i < uploadFiles.length; i++ ) {
						if ( uploadFiles[i].name == data.name && uploadFiles[i].xhr ) {
							uploadFiles[i].xhr.abort();

							delete(uploadFiles[i]);
							delete (files[i]);

							uploadFiles = reindexArray( uploadFiles );
							uploadFiles_count = uploadFiles.length;

							files = reindexArray( files );
							files_count = files.length;
							break;
						}
					}
				}
				else if ( files && files.length ) {
					for ( var i = 0; i < files.length; i++ ) {
						if ( files[i].name == data.name ) {

							if ( typeof opts.uploadAbort === 'function' ) {
								opts.uploadAbort( event, false, files[i] )
							}

							delete (files[i]);

							files = reindexArray( files );
							files_count = files.length;
							uploadFiles_count = files.length;
							return true;
						}
					}
				}
			}
		} );

		//   $(document).on('drop', docDrop).on('dragenter', docEnter).on('dragover', docOver).on('dragleave', docLeave); 
		if ( opts.fallbackInput ) {
			opts.fallbackInput.change( function ( e )
			{
				var data = {
					fileInput: $( e.target ),
					form: $( e.target.form )
				};

				_getFileInputFiles( data.fileInput ).always( function ( xfiles )
				{
					files = xfiles;
				} );

				files_count = files.length;

				for ( var i = 0; i < files_count; i++ ) {
					var f = files[i];
					f.originalFile = files[i];
				}

				if ( typeof opts.add == 'function' ) {
					for ( var i = 0; i < files_count; i++ ) {
						var f = files[i];
						f.upload = function ()
						{
							uploadFiles = [];
							uploadFiles.push( this.originalFile );
							uploadFiles_count = 1;
							upload();
						}

						if ( !opts.add( f ) ) {
							files = false;
							return false;
						}
					}
				}

				if ( opts.autoUpload ) {
					uploadFiles = files;
					uploadFiles_count = files_count;
					upload();
				}
			} );
		}

		function _handleFileTreeEntries( entries, path )
		{

			return $.when.apply(
					$, $.map( entries, function ( entry )
					{
						return _handleFileTreeEntry( entry, path );
					} )
				).pipe( function ()
				{
					return Array.prototype.concat.apply(
						[],
						arguments
					);
				} );
		}

		function _getSingleFileInputFiles( fileInput )
		{
			fileInput = $( fileInput );
			var entries = fileInput.prop( 'webkitEntries' ) ||
					fileInput.prop( 'entries' ),
				files,
				value;

			if ( entries && entries.length ) {
				return _handleFileTreeEntries( entries );
			}
			files = $.makeArray( fileInput.prop( 'files' ) );
			if ( !files.length ) {
				value = fileInput.prop( 'value' );
				if ( !value ) {
					return $.Deferred().resolve( [] ).promise();
				}
				// If the files property is not available, the browser does not
				// support the File API and we add a pseudo File object with
				// the input value as name with path information removed:
				files = [
					{name: value.replace( /^.*\\/, '' )}
				];
			} else if ( files[0].name === undefined && files[0].fileName ) {                 // File normalization for Safari 4 and Firefox 3:
				$.each( files, function ( index, file )
				{
					file.name = file.fileName;
					file.size = file.fileSize;
				} );
			}
			return $.Deferred().resolve( files ).promise();
		}

		function _getFileInputFiles( fileInput )
		{

			if ( !(fileInput instanceof $) || fileInput.length === 1 ) {
				return _getSingleFileInputFiles( fileInput );
			}

			return $.when.apply(
					$,
					$.map( fileInput, _getSingleFileInputFiles )
				).pipe( function ()
				{
					return Array.prototype.concat.apply(
						[],
						arguments
					);
				} );
		}

		function drop( e )
		{
			e.preventDefault();
			if ( opts.drop.call( this, e ) === false )
				return false;
			var dataTransfer = e.dataTransfer = e.originalEvent.dataTransfer;

			if ( dataTransfer === null || dataTransfer === undefined || dataTransfer.files.length === 0 ) {
				opts.error( errors[0] );
				return false;
			}
			files = dataTransfer.files;
			files_count = files.length;

			for ( var i = 0; i < files_count; i++ ) {
				var f = files[i];
				f.originalFile = files[i];
			}

			if ( typeof opts.add == 'function' ) {
				for ( var i = 0; i < files_count; i++ ) {
					var f = files[i];
					f.upload = function ()
					{
						uploadFiles = [];
						uploadFiles.push( this.originalFile );
						uploadFiles_count = 1;
						upload();
					}

					if ( !opts.add( f ) ) {
						files = false;
						return false;
					}
				}
			}

			if ( opts.autoUpload ) {
				uploadFiles = files;
				uploadFiles_count = files_count;
				upload();
			}

			return false;
		}

		/**
		 * OLD Scool
		 *
		 * @param {type} filename
		 * @param {type} filedata
		 * @param {type} mime
		 * @param {type} boundary
		 * @returns {builder}
		 */
		function getBuilder( filename, filedata, mime, boundary )
		{
			var dashdash = '--',
				crlf = '\r\n',
				builder = '';

			if ( opts.data ) {
				var params = opts.data; //$.param(opts.data).replace(/\+/g, '%20').split(/&/);

				$.each( params, function ( name, val )
				{
					/*


					 var pair = this.split("=", 2),
					 name = pair[0],
					 //name = decodeURIComponent(pair[0]),
					 //name = name.replace(/%5B/g, '[').replace(/%5D/g, ']'),

					 val = decodeURIComponent(pair[1]),
					 val = val.replace(/%5B/g, '[').replace(/%5D/g, ']');



					 if (pair.length !== 2) {
					 return;
					 }
					 */
					builder += dashdash;
					builder += boundary;
					builder += crlf;
					builder += 'Content-Disposition: form-data; name="' + name + '"';
					builder += crlf;
					builder += crlf;
					builder += val;
					builder += crlf;
				} );
			}

			builder += dashdash;
			builder += boundary;
			builder += crlf;
			builder += 'Content-Disposition: form-data; name="' + opts.paramname + '"';
			builder += '; filename="' + filename + '"';
			builder += crlf;

			builder += 'Content-Type: ' + mime;
			builder += crlf;
			builder += crlf;

			builder += filedata;
			builder += crlf;

			builder += dashdash;
			builder += boundary;
			builder += dashdash;
			builder += crlf;
			return builder;
		}

		function progress( e )
		{
			if ( e.lengthComputable ) {
				var percentage = Math.round( (e.loaded * 100) / e.total );
				if ( this.currentProgress !== percentage ) {

					this.currentProgress = percentage;
					opts.progressUpdated( this.index, this.file, this.currentProgress );

					global_progress[this.global_progress_index] = this.currentProgress;
					globalProgress();

					var elapsed = new Date().getTime();
					var diffTime = elapsed - this.currentStart;

					if ( diffTime >= opts.refresh ) {
						var diffData = e.loaded - this.startData;
						var speed = diffData / diffTime; // KB per second

						opts.speedUpdated( this.index, this.file, speed, e.loaded, diffTime );

						this.startData = e.loaded;
						this.currentStart = elapsed;
					}
				}
			}
		}

		function globalProgress()
		{
			if ( global_progress.length === 0 ) {
				return;
			}

			var total = 0, index;
			for ( index in global_progress ) {
				if ( global_progress.hasOwnProperty( index ) ) {
					total = total + global_progress[index];
				}
			}

			opts.globalProgressUpdated( Math.round( total / global_progress.length ) );
		}

		// Respond to an upload
		function upload( f )
		{
			stop_loop = false;

			if ( !uploadFiles ) {
				opts.error( errors[0] );
				return false;
			}
			if ( opts.allowedfiletypes.push && opts.allowedfiletypes.length ) {
				for ( var fileIndex = uploadFiles.length; fileIndex--; ) {
					if ( !uploadFiles[fileIndex].type || $.inArray( uploadFiles[fileIndex].type, opts.allowedfiletypes ) < 0 ) {
						opts.error( errors[3], uploadFiles[fileIndex] );
						return false;
					}
				}
			}

			var filesDone = 0, filesRejected = 0;

			if ( uploadFiles_count > opts.maxfiles && opts.queuefiles === 0 ) {
				opts.error( errors[1], uploadFiles[fileIndex] );
				return false;
			}

			// Define queues to manage upload process
			var workQueue = [];
			var processingQueue = [];
			var doneQueue = [];

			// Add everything to the workQueue
			for ( var i = 0; i < uploadFiles_count; i++ ) {
				workQueue.push( i );
			}

			// Helper function to enable pause of processing to wait
			// for in process queue to complete
			var pause = function ( timeout )
			{
				setTimeout( process, timeout );
				return;
			};

			var cancel = function ( e )
			{
				if ( stop ) {
					if ( opts.onCancel ) {
						opts.onCancel( uploadFiles[e.target.index] );
						return;
					}
				}
			};

			var send = function ( e )
			{

				var fileIndex = ((typeof (e.srcElement) === "undefined") ? e.target : e.srcElement).index;

				// Sometimes the index is not attached to the
				// event object. Find it by size. Hack for sure.
				if ( e.target.index === undefined ) {
					e.target.index = getIndexBySize( e.total );
				}

				var xhr = new XMLHttpRequest(),
					upload = xhr.upload,
					file = uploadFiles[e.target.index],
					index = e.target.index,
					start_time = new Date().getTime(),
					boundary = '------multipartformboundary' + (new Date()).getTime(), // for the old scool
					global_progress_index = global_progress.length,
					builder,
					newName = rename( file.name ),
					mime = file.type;

				uploadFiles[e.target.index].xhr = xhr;

				if ( opts.withCredentials ) {
					xhr.withCredentials = opts.withCredentials;
				}

				var useFormData = false;

				// prepare FormData
				// new scool html5 only if exists FormData
				if ( typeof FormData != 'undefined' ) {
					useFormData = true;
					var formData = new FormData();

					if ( opts.data ) {
						var params = $.param( opts.data ).replace( /\+/g, '%20' ).split( /&/ );
						$.each( params, function ()
						{
							var pair = this.split( "=", 2 );
							if ( pair.length == 2 ) {
								formData.append( pair[0], pair[1] );
							}
						} );
					}

					formData.append( opts.paramname, file );
				}
				else {
					if ( typeof newName === "string" ) {
						builder = getBuilder( newName, e.target.result, mime, boundary );
					} else {
						builder = getBuilder( file.name, e.target.result, mime, boundary );
					}
				}

				upload.index = index;
				upload.file = file;
				upload.downloadStartTime = start_time;
				upload.currentStart = start_time;
				upload.currentProgress = 0;
				upload.global_progress_index = global_progress_index;
				upload.startData = 0;
				upload.addEventListener( "progress", progress, false );

				// Allow url to be a method
				if ( jQuery.isFunction( opts.url ) ) {
					xhr.open( opts.requestType, opts.url(), true );
				} else {
					xhr.open( opts.requestType, opts.url, true );
				}

				// Add headers
				$.each( opts.headers, function ( k, v )
				{
					xhr.setRequestHeader( k, v );
				} );

				if ( useFormData ) {
					// new scool
					xhr.send( formData );
				} else {
					// old scool
					xhr.setRequestHeader( 'Content-Type', 'multipart/form-data; boundary=' + boundary );
					xhr.sendAsBinary( builder );
				}

				global_progress[global_progress_index] = 0;

				globalProgress();

				opts.uploadStarted( index, file, uploadFiles_count );

				xhr.addEventListener( 'abort', function ( e )
				{
					if ( opts.uploadAbort ) {
						opts.uploadAbort( e, this, file );
					}
				}, false );

				xhr.onload = function ()
				{
					var serverResponse = null;

					if ( xhr.responseText ) {
						try {
							serverResponse = jQuery.parseJSON( xhr.responseText );
						}
						catch ( e ) {
							serverResponse = xhr.responseText;
						}
					}

					var now = new Date().getTime(),
						timeDiff = now - start_time,
						result = opts.uploadFinished( index, file, serverResponse, timeDiff, xhr );
					filesDone++;
					// Remove from processing queue
					processingQueue.forEach( function ( value, key )
					{
						if ( value === fileIndex ) {
							processingQueue.splice( key, 1 );
						}
					} );

					// Add to donequeue
					doneQueue.push( fileIndex );

					// Make sure the global progress is updated
					global_progress[global_progress_index] = 100;
					globalProgress();

					if ( filesDone === (uploadFiles_count - filesRejected) ) {
						afterAll();
					}

					if ( result === false ) {
						stop_loop = true;
					}
					// Pass any errors to the error option
					if ( xhr.status < 200 || xhr.status > 299 ) {
						opts.error( xhr.statusText, file, fileIndex, xhr.status );
					}
				};
			};

			// Process an upload, recursive
			var process = function ()
			{

				var fileIndex;

				if ( stop_loop ) {
					return false;
				}

				// Check to see if are in queue mode
				if ( opts.queuefiles > 0 && processingQueue.length >= opts.queuefiles ) {
					return pause( opts.queuewait );
				} else {
					// Take first thing off work queue
					fileIndex = workQueue[0];
					workQueue.splice( 0, 1 );

					// Add to processing queue
					processingQueue.push( fileIndex );
				}

				try {
					if ( beforeEach( uploadFiles[fileIndex] ) !== false ) {
						if ( fileIndex === uploadFiles_count ) {
							return;
						}
						var reader = new FileReader(),
							max_file_size = 1048576 * parseInt( opts.maxfilesize );

						reader.index = fileIndex;
						if ( uploadFiles[fileIndex].size > parseInt( max_file_size ) ) {
							opts.error( errors[2], uploadFiles[fileIndex], fileIndex );

							// Remove from queue
							processingQueue.forEach( function ( value, key )
							{
								if ( value === fileIndex ) {
									processingQueue.splice( key, 1 );
								}
							} );
							filesRejected++;
							return true;
						}
						reader.onerror = function ( e )
						{
							switch ( e.target.error.code ) {
								case e.target.error.NOT_FOUND_ERR:
									opts.error( errors[4], uploadFiles[fileIndex] );
									return false;
								case e.target.error.NOT_READABLE_ERR:
									opts.error( errors[5], uploadFiles[fileIndex] );
									return false;
								case e.target.error.ABORT_ERR:
									opts.error( errors[6], uploadFiles[fileIndex] );
									return false;
								default:
									opts.error( errors[7], uploadFiles[fileIndex] );
									return false;
							}
							;
						};

						reader.onloadend = !opts.beforeSend ? send : function ( e )
						{
							opts.beforeSend( uploadFiles[fileIndex], fileIndex, function ()
							{
								return send( e );
							} );
						};

						reader.readAsBinaryString( uploadFiles[fileIndex] );
					} else {

						filesRejected++;
					}
				} catch ( err ) {
					// Remove from queue
					processingQueue.forEach( function ( value, key )
					{
						if ( value === fileIndex ) {
							processingQueue.splice( key, 1 );
						}
					} );
					opts.error( errors[0], uploadFiles[fileIndex] );
					return false;
				}

				// If we still have work to do,
				if ( workQueue.length > 0 ) {
					process();
				}
			};

			// Initiate the processing loop
			process();

		}

		function getIndexBySize( size )
		{
			for ( var i = 0; i < uploadFiles_count; i++ ) {
				if ( uploadFiles[i].size === size ) {
					return i;
				}
			}

			return undefined;
		}

		function rename( name )
		{
			return opts.rename( name );
		}

		function beforeEach( file )
		{
			return opts.beforeEach( file );
		}

		function afterAll()
		{
			return opts.afterAll();
		}

		function dragEnter( e )
		{
			clearTimeout( doc_leave_timer );
			e.preventDefault();
			opts.dragEnter.call( this, e );
		}

		function dragOver( e )
		{
			clearTimeout( doc_leave_timer );
			e.preventDefault();
			opts.docOver.call( this, e );
			opts.dragOver.call( this, e );
		}

		function dragLeave( e )
		{
			clearTimeout( doc_leave_timer );
			opts.dragLeave.call( this, e );
			e.stopPropagation();
		}

		function docDrop( e )
		{
			e.preventDefault();
			opts.docLeave.call( this, e );
			return false;
		}

		function docEnter( e )
		{
			clearTimeout( doc_leave_timer );
			e.preventDefault();
			opts.docEnter.call( this, e );
			return false;
		}

		function docOver( e )
		{
			clearTimeout( doc_leave_timer );
			e.preventDefault();
			opts.docOver.call( this, e );
			return false;
		}

		function docLeave( e )
		{
			doc_leave_timer = setTimeout( (function ( _this )
			{
				return function ()
				{
					opts.docLeave.call( _this, e );
				};
			})( this ), 200 );
		}

		return this;
	};

	function empty()
	{
	}

	try {

		if ( XMLHttpRequest.prototype.sendAsBinary ) {
			return;
		}

		XMLHttpRequest.prototype.sendAsBinary = function ( datastr )
		{
			function byteValue( x )
			{
				return x.charCodeAt( 0 ) & 0xff;
			}

			var ords = Array.prototype.map.call( datastr, byteValue );
			var ui8a = new Uint8Array( ords );

			try {
				this.send( ui8a );
			}
			catch ( er ) {
				this.send( ui8a.buffer );
			}

		};
	} catch ( e ) {
	}

})( jQuery, window  );
