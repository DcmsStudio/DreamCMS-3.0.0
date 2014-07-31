


(function ( $ )
{
	/*
	 * jQuery 1.9 support. browser object has been removed in 1.9
	 */
	var browser = $.browser

	if ( !browser ) {
		function uaMatch( ua )
		{
			ua = ua.toLowerCase();

			var match = /(chrome)[ \/]([\w.]+)/.exec( ua ) ||
				/(webkit)[ \/]([\w.]+)/.exec( ua ) ||
				/(opera)(?:.*version|)[ \/]([\w.]+)/.exec( ua ) ||
				/(msie) ([\w.]+)/.exec( ua ) ||
				ua.indexOf( "compatible" ) < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec( ua ) ||
				[];

			return {
				browser: match[ 1 ] || "",
				version: match[ 2 ] || "0"
			};
		}
		;

		var matched = uaMatch( navigator.userAgent );
		browser = {};

		if ( matched.browser ) {
			browser[ matched.browser ] = true;
			browser.version = matched.version;
		}

		// Chrome is Webkit, but Webkit is also Safari.
		if ( browser.chrome ) {
			browser.webkit = true;
		} else if ( browser.webkit ) {
			browser.safari = true;
		}
	}

	var docloaded = false;
	$( document ).ready( function ()
	{
		docloaded = true;
	} );

	Fileman = function ( element, options )
	{

		var obj = $( element );
		var self = this, fm = this;
		this.el = obj;

		this.globalContainer = obj;
		this.defaults = {
			externalScrollbarContainer: '.jspPane',
			deepPlaceholderSize: 16,
			onAfterLoad: null,
			disabled: [],
			rememberLastDir: true,
			coverflow: false,
			sortsRules: {},
			sortType: 'name', // default sort by
			sortOrder: 'asc', // default order
			dirSep: '/',
			viewMode: 'list', // thumb
			view: 'list',
			mode: 'fm', // or mm for media manager
			toolbarContainer: null  // external toolbar container
		};


		this.sortType = 'name';
		this.sortOrder = 'asc';

		/**
		 * Display folders first?
		 *
		 * @type {Boolean}
		 * @default true
		 */
		this.sortStickFolders = true;




		//
		this.extraEvents = {
			onSelectFile: false,
			onSelectDir: false
		};

		/* buttons on toolbar */
		this.toolbar = [
			['back', 'reload'],
			['select', 'open'],
			['mkdir', 'mkfile', 'upload'],
			['copy', 'paste', 'rm'],
			['rename', 'edit'],
			['archive', 'extract'],
			['info', 'quicklook', 'resize'],
			['icons', 'list', 'coverflow'],
			['help']
		];

		this.contextmenu = {
			/* contextmenu commands */
			'cwd': ['reload', 'delim', 'mkdir', 'mkfile', 'upload', 'delim', 'paste', 'delim', 'info'],
			'file': ['select', 'open', 'quicklook', 'delim', 'copy', 'cut', 'rm', 'delim', 'duplicate', 'rename', 'edit', 'resize', 'archive', 'extract', 'delim', 'info'],
			'group': ['select', 'copy', 'cut', 'rm', 'delim', 'archive', 'extract', 'delim', 'info']
		},
			this.ajaxLoading = false;
		this.locked = false;
		this.data = null;
		this.opts = {};
		this.id = '';
		this.dirInfo = {};


		this.eventsManager = null;
		this.quickview = null;
		this.command = null;
		/**
		 * Boolean. Enable/disable actions
		 **/
		this.locked = false;

		/**
		 * Events listeners
		 *
		 * @type Object
		 **/
		this.listeners = {};
		this._sortRules = {
			name: function ( file1, file2 )
			{
				return file1.name.toLowerCase().localeCompare( file2.name.toLowerCase() );
			},
			size: function ( file1, file2 )
			{
				var size1 = parseInt( file1.size ) || 0,
					size2 = parseInt( file2.size ) || 0;

				//return size1 == size2 ? 0 : size1 > size2 ? 1 : -1;
				return (parseInt( file1.size ) || 0) > (parseInt( file2.size ) || 0) ? 1 : -1;
			},
			kind: function ( file1, file2 )
			{
				return file1.ext.toLowerCase().localeCompare( file2.ext.toLowerCase() );
			},
			date: function ( file1, file2 )
			{
				var date1, date2;
				if ( file1.ts && file2.ts ) {
					date1 = parseInt( file1.ts, 0 );
					date2 = parseInt( file2.ts, 0 );
				}
				else {
					date1 = parseInt( file1.ts, 0 ) || parseInt( file1.date, 0 );
					date2 = parseInt( file2.ts, 0 ) || parseInt( file2.date, 0 );
				}
				return date1 == date2 ? 0 : date1 > date2 ? 1 : -1
			},
			dimensions: function ( file1, file2 )
			{
				var s1, s2;
                if ( typeof file1.dimensions != 'string' || typeof file2.dimensions != 'string' ) {
                    return 0;
                }

				var f1 = file1.dimensions.split( 'x' );
				var f2 = file2.dimensions.split( 'x' );

				if ( f1[0] && f1[1] && f2[0] && f2[1] ) {
					s1 = parseInt( f1[0] + f1[1], 0 );
					s2 = parseInt( f2[0] + f2[1], 0 );
				}
				else {
					s1 = parseInt( f1[0] + f1[1], 0 );
					s2 = parseInt( f2[0] + f2[1], 0 );
				}

				return s1 == s2 ? 0 : s1 > s2 ? 1 : -1
			}
		};
		/**
		 * Object. Mimetypes to kinds mapping
		 **/
		this.kinds = {
			'unknown': 'Unknown',
			'directory': 'Folder',
			'symlink': 'Alias',
			'symlink-broken': 'Broken alias',
			'application/x-empty': 'Plain text',
			'application/postscript': 'Postscript document',
			'application/octet-stream': 'Application',
			'application/vnd.ms-office': 'Microsoft Office document',
			'application/vnd.ms-word': 'Microsoft Word document',
			'application/vnd.ms-excel': 'Microsoft Excel document',
			'application/vnd.ms-powerpoint': 'Microsoft Powerpoint presentation',
			'application/pdf': 'Portable Document Format (PDF)',
			'application/vnd.oasis.opendocument.text': 'Open Office document',
			'application/x-shockwave-flash': 'Flash application',
			'application/xml': 'XML document',
			'application/x-bittorrent': 'Bittorrent file',
			'application/x-7z-compressed': '7z archive',
			'application/x-tar': 'TAR archive',
			'application/x-gzip': 'GZIP archive',
			'application/x-bzip2': 'BZIP archive',
			'application/zip': 'ZIP archive',
			'application/x-rar': 'RAR archive',
			'application/javascript': 'Javascript application',
			'text/plain': 'Plain text',
			'text/x-php': 'PHP source',
			'text/html': 'HTML document',
			'text/javascript': 'Javascript source',
			'text/css': 'CSS style sheet',
			'text/rtf': 'Rich Text Format (RTF)',
			'text/rtfd': 'RTF with attachments (RTFD)',
			'text/x-c': 'C source',
			'text/x-c++': 'C++ source',
			'text/x-shellscript': 'Unix shell script',
			'text/x-python': 'Python source',
			'text/x-java': 'Java source',
			'text/x-ruby': 'Ruby source',
			'text/x-perl': 'Perl script',
			'text/xml': 'XML document',
			'image/x-ms-bmp': 'BMP image',
			'image/jpeg': 'JPEG image',
			'image/gif': 'GIF Image',
			'image/png': 'PNG image',
			'image/x-targa': 'TGA image',
			'image/tiff': 'TIFF image',
			'image/vnd.adobe.photoshop': 'Adobe Photoshop image',
			'audio/mpeg': 'MPEG audio',
			'audio/midi': 'MIDI audio',
			'audio/ogg': 'Ogg Vorbis audio',
			'audio/mp4': 'MP4 audio',
			'audio/wav': 'WAV audio',
			'video/x-dv': 'DV video',
			'video/mp4': 'MP4 video',
			'video/mpeg': 'MPEG video',
			'video/x-msvideo': 'AVI video',
			'video/quicktime': 'Quicktime video',
			'video/x-ms-wmv': 'WM video',
			'video/x-flv': 'Flash video',
			'video/x-matroska': 'Matroska video'
		};

		/**
		 * Object. Current Working Dir Content
		 **/
		this.dircontent = {};
		this.dircontent_page = 1;
		this.dircontent_pages = 1;
		this.dircontent_files = 0;
		/**
		 * Object. Current Working Dir info
		 **/
		this.cwd = {};

		/**
		 * Object. tree buffer
		 **/
		this.tree = {};

		/**
		 * Object. tree buffer
		 **/
		this.treeSingleDimension = [];

		/**
		 * Object. Buffer for copied files
		 **/
		this.buffer = {};
		/**
		 * Array. Selected files IDs
		 **/
		this.selected = [];
		/**
		 * Array. Folder navigation history
		 **/
		this.history = [];
		/**
		 * Files/dirs cache
		 *
		 * @type Object
		 */
		this._files = [];

		/**
		 * Object.
		 **/
		this.eventsManager = new this.EventManager( this );

		/**
		 * Object.
		 **/
		this.layout = new this.Layout( this, element );

		/**
		 * Object.
		 **/
		this.quickview = new this.quickView( this );

		/**
		 * Object.
		 **/
		this.command = new this.Commands( this );

		/**
		 * Object.
		 **/
		this.coverflow = new this.Coverflow( this );

		/**
		 * Store info about files/dirs in "files" object.
		 *
		 * @param Array files
		 * @return void
		 **/
		this.cache = function ( data )
		{
			var l = data.length, f;
			var key;

			for ( key in data ) {
				f = data[key];
				if ( f.name && f.hash && f.mime ) {
					if ( !f.phash ) {
						var name = 'volume_' + f.name,
							i18 = self.i18n( name );

						if ( name != i18 ) {
							f.i18 = i18;
						}
					}

					this._files[f.hash] = f;
				}
			}
		};

		/**
		 *  Remove from all caches
		 *
		 */
		this.cacheRemove = function ( hash )
		{
			if ( typeof hash == 'string' ) {
				if ( this._files[hash] ) {
					delete this._files[hash];
				}

				if ( this.treeSingleDimension[hash] ) {
					delete this.treeSingleDimension[hash];
				}
			}
			else if ( typeof hash == "object" ) {
				for ( var k in hash ) {
					var value = hash[k];

					if ( this._files[value] ) {
						delete this._files[value];
					}

					if ( this.treeSingleDimension[value] ) {
						delete this.treeSingleDimension[value];
					}
				}
			}
		}

		/**
		 * Set/get data into/from localStorage
		 *
		 * @param String key
		 * @param String|void value
		 * @return String
		 */
		this.localStorage = function ( key, val )
		{
			var s = window.localStorage;

			key = 'fm-' + this.id +'-' + key;

			if ( val === null ) {
				//    console.log('remove', key)
				return s.removeItem( key );
			}

			if ( val !== void(0) ) {
				try {
					s.setItem( key, val );
				} catch ( e ) {
					s.clear();
					s.setItem( key, val );
				}
			}

			return s.getItem( key );
		};

		/**
		 * Get/set cookie
		 *
		 * @param String cookie name
		 * @param String|void cookie value
		 * @return String
		 */
		this.cookie = function ( name, value )
		{
			var d, o, c, i;

			name = 'fm-' + name + this.id;

			if ( value === void(0) ) {
				if ( document.cookie && document.cookie != '' ) {
					c = document.cookie.split( ';' );
					name += '=';
					for ( i = 0; i < c.length; i++ ) {
						c[i] = $.trim( c[i] );
						if ( c[i].substring( 0, name.length ) == name ) {
							return decodeURIComponent( c[i].substring( name.length ) );
						}
					}
				}
				return '';
			}

			o = $.extend( {}, this.options.cookie );
			if ( value === null ) {
				value = '';
				o.expires = -1;
			}
			if ( typeof (o.expires) == 'number' ) {
				d = new Date();
				d.setTime( d.getTime() + (o.expires * 86400000) );
				o.expires = d;
			}
			document.cookie = name + '=' + encodeURIComponent( value ) + '; expires=' + o.expires.toUTCString() + (o.path ? '; path=' + o.path : '') + (o.domain ? '; domain=' + o.domain : '') + (o.secure ? '; secure' : '');
			return value;
		};

		this.storage = function (k, v)
		{

			if ( this.allowLocalStorage() ) {
				return (typeof v != 'undefined' ? this.localStorage(k, v) : this.localStorage(k) );
			}
			else {
				return (typeof v != 'undefined' ? this.cookie(k, v) : this.cookie(k) );
			}

		};

		this.allowLocalStorage = function() {
			return 'localStorage' in window && window['localStorage'] !== null ? true : false;
		};





		/**
		 * Set/unset this.locked flag
		 *
		 * @param Boolean state
		 **/
		this.lock = function ( l )
		{
			this.layout.spinner( (this.locked = l || false) );
			this.command.lock = this.locked;
		};

		this.i18n = function ( text )
		{
			return text;
		};

		this.log = function ( msg )
		{
			Debug.log( msg );
		};

		this.calcChmod = function ( octalvalue )
		{
			var chmodGroups = ['owner', 'group', 'other'];
			var chmodPermissions = ['-', 'r', 'w', 'x'];
			var permissions = '-';

			for ( var i = 0; i < chmodGroups.length; i++ ) {
				var chmodPart = parseInt( value.substring( i, i + 1 ), 10 );

				for ( var j = 1; j <= 3; j++ ) {
					oForm.elements[chmodGroups[i] + j].checked = (chmodPart >= parseInt( oForm.elements[chmodGroups[i] + j].value, 10 ));
					permissions += (oForm.elements[chmodGroups[i] + j].checked) ? chmodPermissions[j] : chmodPermissions[0];
					chmodPart = (chmodPart >= parseInt( oForm.elements[chmodGroups[i] + j].value, 10 )) ? chmodPart - parseInt( oForm.elements[chmodGroups[i] + j].value, 10 ) : chmodPart;
				}

			}

		};

		/*
		 * Return wraped file name if needed
		 */
		this.formatName = function ( n )
		{
			var w = self.opts.wrap;
			if ( w > 0 ) {
				if ( n.length > w * 2 ) {
					return n.substr( 0, w ) + "&shy;" + n.substr( w, w - 5 ) + "&hellip;" + n.substr( n.length - 3 );
				} else if ( n.length > w ) {
					return n.substr( 0, w ) + "&shy;" + n.substr( w );
				}
			}
			return n;
		};

		/*
		 * Return localized date
		 */
		this.formatDate = function ( d )
		{
			if ( typeof d == 'undefined' ) {
				return '';
			}

			return d.replace( /([a-z]+)\s/i, function ( a1, a2 )
			{
				return self.i18n( a2 ) + ' ';
			} );
		};

		/*
		 * Convert mimetype into css class
		 */
		this.mime2class0 = function ( mime )
		{
			return mime.replace( '/', ' ' ).replace( /\./g, '-' );
		};

		/**
		 * Convert mimetype into css classes
		 *
		 * @param String file mimetype
		 * @return String
		 */
		this.mime2class = function ( mime )
		{
			if ( typeof mime == 'undefined' ) {
				return '';
			}
			var prefix = 'cwd-icon-';

			mime = mime.split( '/' );

			return prefix + mime[0] + (mime[0] != 'image' && mime[1] ? ' ' + prefix + mime[1].replace( /(\.|\+)/g, '-' ) : '');
		};

		/**
		 * Return css class marks file permissions
		 *
		 * @param Object file
		 * @return String
		 */
		this.perms2class = function ( o )
		{
			var c = '';

			if ( !o.read && !o.write ) {
				c = 'na';
			} else if ( !o.read ) {
				c = 'wo';
			} else if ( !o.write ) {
				c = 'ro';
			}
			return c;
		};

		/**
		 * Return kind of file
		 */
		this.mime2kind = function ( mime )
		{
			return this.i18n( this.kinds[mime] || 'unknown' );
		};

		/*
		 * Return formated file size
		 */
		this.formatSize = function ( s )
		{
			var n = 1, u = 'bytes';

			if ( s > 1073741824 ) {
				n = 1073741824;
				u = 'Gb';
			} else if ( s > 1048576 ) {
				n = 1048576;
				u = 'Mb';
			} else if ( s > 1024 ) {
				n = 1024;
				u = 'Kb';
			}
			return Math.round( s / n ) + ' ' + u;
		};

		/**
		 * Return localized string with file permissions
		 */
		this.formatPermissions = function ( r, w, rm )
		{
			var p = [];
			r && p.push( self.i18n( 'read' ) );
			w && p.push( self.i18n( 'write' ) );
			rm && p.push( self.i18n( 'remove' ) );
			return p.join( '/' );
		};

		/**
		 * Get/set last opened directory
		 *
		 * @param String|undefined dir hash
		 * @return String
		 */
		this.lastDir = function ( hash )
		{
			return this.opts.rememberLastDir ? this.storage( 'lastdir', hash ) : '';
		};

		/**
		 * Set file manager view type (list|icons)
		 *
		 * @param String v view name
		 **/
		this.setView = function ( v )
		{
			if ( v == 'list' || v == 'icons' ) {
				this.opts.view = v;
				this.cookie( this.vCookie, v );
			}
		};

		/**
		 * Return folders in places IDs
		 *
		 * @return Array
		 **/
		this.getPlaces = function ()
		{
			var pl = [], p = this.cookie( this.pCookie );
			if ( p.length ) {
				if ( p.indexOf( ':' ) != -1 ) {
					pl = p.split( ':' );
				} else {
					pl.push( p );
				}
			}
			return pl;
		};

		/**
		 * Add new folder to places
		 *
		 * @param String Folder ID
		 * @return Boolean
		 **/
		this.addPlace = function ( id )
		{
			var p = this.getPlaces();
			if ( $.inArray( id, p ) == -1 ) {
				p.push( id );
				this.savePlaces( p );
				return true;
			}
		};

		/**
		 * Remove folder from places
		 *
		 * @param String Folder ID
		 * @return Boolean
		 **/
		this.removePlace = function ( id )
		{
			var p = this.getPlaces();
			if ( $.inArray( id, p ) != -1 ) {
				this.savePlaces( $.map( p, function ( o )
				{
					return o == id ? null : o;
				} ) );
				return true;
			}
		};

		/**
		 * Save new places data in cookie
		 *
		 * @param Array Folders IDs
		 **/
		this.savePlaces = function ( p )
		{
			this.cookie( this.pCookie, p.join( ':' ) );
		};

		/**
		 *
		 * @returns {undefined}
		 */
		this.init = function ()
		{

		};

		/**
		 *
		 * @returns {undefined}
		 */
		this.initFileman = function ()
		{

			this._compare = $.proxy( this.compare, this );

			if ( this.storage('sortType') ) {
				this.sortType = this.storage('sortType');
			}
			else if (this.opts.sortType) {
				this.sortType = this.opts.sortType;
			}
			else {
				this.sortType = 'name';
			}

			if ( this.storage('sortOrder') ) {
				this.sortOrder = this.storage('sortOrder');
			}
			else if (this.opts.sortOrder) {
				this.sortOrder = this.opts.sortOrder;
			}
			else {
				this.sortOrder = 'asc';
			}

			this.sortRules = $.extend({}, this._sortRules, this.opts.sortsRules );

			$.each( this.sortRules, function ( name, method )
			{
				if ( typeof method != 'function' ) {
					delete self.sortRules[name];
				}
			} );


			if ( this.storage('view') ) {
				this.opts.view = this.storage('view');
			}

			if ( this.storage('coverflow') ) {
				this.opts.coverflow = true;
			}


			this.layout.initLayout();


			// reset data
			this.data = {};
			this.layout.spinner( true );



			this.getAjaxData( {
				init: true,
				tree: true
			}, function ()
			{
				self.prepareData( true, true );

			} );
		};

		/**
		 *
		 * @param {type} updateTree
		 * @returns {undefined}
		 */
		this.reload = function ( updateTree )
		{
			this.prepareData( updateTree, false );
		};

		/**
		 *
		 * @param {type} updateTree
		 * @param {type} isInitCall
		 * @returns {undefined}
		 */
		this.prepareData = function ( updateTree, isInitCall )
		{
			if ( this.data.error ) {
				this.layout.spinner( false );
				this.lock( false );
				jAlert( this.data.error );
				return;
			}

			if ( !Tools.responseIsOk( this.data ) && this.data.msg ) {
				if ( this.data.msg ) {
					jAlert( this.data.msg );
				}
				else {
					alert( 'An error was found...' );
				}
				this.layout.spinner( false );
				this.lock( false );
				return;
			}

			if ( this.opts.onBeforeLoad ) {
				this.opts.onBeforeLoad();
			}

			var cwd;
			if ( this.data.cwd ) {
				cwd = this.data.cwd.hash;
			}
			else if ( this.cwd.hash ) {
				cwd = this.cwd.hash;
			}

			if ( this.data.dircontent ) {
				this.dircontent = this.data.dircontent;
				if ( this.data.dircontent_page )
					this.dircontent_page = this.data.dircontent_page;
				if ( this.data.dircontent_pages )
					this.dircontent_pages = this.data.dircontent_pages;
				if ( this.data.dircontent_files )
					this.dircontent_files = this.data.filestotal;
			}

			delete this.data.dircontent;

			this.lastDir( cwd );

			if ( this.data.cwd )
				this.cwd = this.data.cwd;

			if ( updateTree === true && this.data.tree ) {
				this.tree = this.data.tree;
				delete this.data.tree;
			}

			// merge config
			if ( this.data.params ) {
				this.opts = $.extend( {}, this.opts, this.data.params );
				delete this.data.params;
			}

			// reset statusbar infos
			this.dirInfo.items = 0;
			this.dirInfo.size = 0;

			if ( isInitCall === true ) {
				this.command.initCommants( this.opts.disabled );
			}

			// create the tree
			if ( updateTree === true && this.tree ) {
				this.layout.renderTree( this.tree );
			}

			this.layout.renderCWD();

			this.cache( this.dircontent );
			if ( !this._files[cwd] ) {
				// cache the directory if not exists
				this.cache( [this.cwd] );
			}

			// update the statusbar
			this.layout.updateStatusbar();

			if ( typeof this.opts.onAfterLoad == 'function' ) {
				this.opts.onAfterLoad();
			}

			// update tree selection if delete a file/dir
			setTimeout( function ()
			{
				self.layout.updateTreeSelection();
			}, 100 );


			if (this.opts.selectFile && this.data.hash ) {
				if ( this.opts.view == 'list' && $( 'tr[hash="' + this.data.hash + '"]', this.layout.foldercontentContainer ).length ) {
					this.select( $( 'tr[hash="' + this.data.hash + '"]', this.layout.foldercontentContainer ), true, false, true );
				}
				if ( this.opts.view == 'icons' && $( 'div[hash="' + this.data.hash + '"]', this.layout.foldercontentContainer ).length ) {
					this.select( $( 'div[hash="' + this.data.hash + '"]', this.layout.foldercontentContainer ), true, false, true );
				}
			}

			this.layout.spinner( false );
			this.lock( false );
		};

		/**
		 *  Build the left side tree
		 */
		this.buildTree = function ()
		{

			if ( this.tree ) {
				this.layout.renderTree( this.tree );
			}
		};

		/**
		 *
		 * @returns {Boolean}
		 */
		this.canThumb = function ()
		{
			return this.data.hasOwnProperty( 'tmb' ) && this.data.tmb ? true : false;
		};

		/**
		 * Load generated thumbnails in background
		 *
		 **/
		this.tmb = function ()
		{
			var self = this;
			this.ajax( {
					async: false,
					action: 'thumb',
					current: self.cwd.hash,
					coverflow: this.opts.coverflow
				},
				function ( data )
				{
					if ( (self.opts.view == 'icons' || self.opts.coverflow == true) && data.images && data.current == self.cwd.hash ) {

						var iconView = self.layout.foldercontentContainerInner.find( '.iconview' );

						for ( var i in data.images ) {
							if ( self.dircontent[i] && data.images[i].tmb != undefined ) {
								self.dircontent[i].tmb = data.images[i].tmb;
								self.dircontent[i].coverflow = data.images[i].coverflow;

								if ( self.opts.coverflow == true ) {
									self.layout.addItemToCoverflow( self.dircontent[i] );
								}

								if ( self.opts.view != 'list' ) {
									iconView.find( 'div[hash="' + i + '"] .cwd-icon' )
										.addClass( 'found cwd-icon-image' )
										.attr( 'style', 'background-image:url(\'' + data.images[i].tmb + '\')' );
								}
							}
						}

						self.lock( false );
						self.layout.spinner( false );
						data.tmb && self.tmb();

						if ( !data.tmb && self.opts.coverflow == true ) {
							self.coverflow.initCoverflow( self.layout.coverflowContainer );
						}

					}
					else {
						if ( self.opts.coverflow == true && data.current == self.cwd.hash ) {
							self.coverflow.initCoverflow( self.layout.coverflowContainer );
						}
					}

				}, {
					lock: false,
					silent: true
				} );
		};

		/**
		 * Return true if file name is acceptable
		 *
		 * @param String file/folder name
		 * @return Boolean
		 */
		this.isValidName = function ( n )
		{
			if ( !this.cwd.dotFiles && n.indexOf( '.' ) == 0 ) {
				return false;
			}
			return n.match( /^[^\\\/\<\>:]+$/ );
		};

		/**
		 * Return true if file with this name exists
		 *
		 * @param String file/folder name
		 * @return Boolean
		 */
		this.fileExists = function ( n )
		{
			for ( var i in this.dircontent ) {
				if ( this.dircontent[i].name == n ) {
					return i;
				}
			}
			/*
			 for (var i in this.treeSingleDimension) {
			 if (this.treeSingleDimension[i].name == n) {
			 return i;
			 }
			 }
			 */
			return false;
		};

		/**
		 * Return name for new file/folder
		 *
		 * @param String base name (i18n)
		 * @param String extension for file
		 * @return String
		 */
		this.uniqueName = function ( n, ext )
		{
			n = self.i18n( n );
			var name = n, i = 0;
			ext = ext || '';

			if ( !this.fileExists( name + ext ) ) {
				return name + ext;
			}

			while ( i++ < 100 ) {
				if ( !this.fileExists( name + i + ext ) ) {
					return name + i + ext;
				}
			}
			return name.replace( '100', '' ) + Math.random() + ext;
		};

		/**
		 * Return root dir hash for current working directory
		 *
		 * @return String
		 */
		this.root = function ( hash )
		{
			var dir = this._files[hash || this.cwd], i;

			while ( dir && dir.phash ) {
				dir = this._files[dir.phash]
			}
			if ( dir ) {
				return dir.hash;
			}

			while ( i in this._files && this._files.hasOwnProperty( i ) ) {
				dir = this._files[i]
				if ( !dir.phash && !dir.mime == 'directory' && dir.read ) {
					return dir.hash
				}
			}

			return '';
		};

		/**
		 *
		 * @param {type} mode
		 * @returns {undefined}
		 */
		this.goHistory = function ( mode )
		{

		};

		/**
		 * Return file data from current dir or tree by it's hash
		 *
		 * @param String file hash
		 * @return Object
		 */
		this.file = function ( hash )
		{
			return this.treeSingleDimension[hash];
		};

		/**
		 * Return all cached files
		 *
		 * @return Array
		 */
		this.files = function ()
		{
			return $.extend( true, {}, this._files );
		};

		/**
		 * Return list of file parents hashes include file hash
		 *
		 * @param String file hash
		 * @return Array
		 */
		this.parents = function ( hash )
		{
			var parents = [], dir;

			while ( hash && (dir = this.file( hash )) && dir.phash ) {
				parents.unshift( dir );
				hash = dir.phash;
			}

			return parents;
		};

		/**
		 *
		 * @param {type} hash
		 * @returns {Array}
		 */
		this.path2array = function ( hash )
		{
			var file,
				path = [];
			while ( hash && (file = this._files[hash]) && file.hash ) {
				path.unshift( file.name );
				hash = file.hash;
			}

			return path;
		};

		/**
		 * Return file path
		 *
		 * @param Object file
		 * @return String
		 */
		this.path = function ( hash, i18 )
		{
			return this._files[hash] && this._files[hash].path
				? this._files[hash].path
				: this.path2array( hash, i18 ).join( '/' );
		};

		/**
		 * Fire event - send notification to all event listeners
		 *
		 * @param String event type
		 * @param Object data to send across event
		 * @return fm
		 */
		this.trigger = function ( event, data )
		{
			event = event.toLowerCase();
			var handlers = self.listeners[event] || [], i, j;

			//  this.log('event-' + event)

			if ( handlers.length ) {
				event = $.Event( event );

				for ( i = 0; i < handlers.length; i++ ) {
					// to avoid data modifications. remember about "sharing" passing arguments in js :)
					event.data = $.extend( true, {}, data );

					try {
						if ( handlers[i]( event, this ) === false
							|| event.isDefaultPrevented() ) {
							//this.debug('event-stoped', event.type);
							break;
						}
					} catch ( ex ) {
						window.console && window.console.log && window.console.log( ex );
					}

				}
			}
			return this;
		};

		/**
		 * Update sort options
		 *
		 * @param {String} sort type
		 * @param {String} sort order
		 * @param {Boolean} show folder first
		 */
		this.setSort = function ( type, order, stickFolders )
		{

			this.sortType = (this.sortType = this.sortRules[type] ? type : 'name');
			this.sortOrder = (this.sortOrder = /asc|desc/.test( order ) ? order : 'asc');
			this.sortStickFolders = (this.sortStickFolders = !!stickFolders) ? 1 : '';

			this.storage( 'sortType', this.sortType );
			this.storage( 'sortOrder', this.sortOrder );
			this.storage( 'sortStickFolders', this.sortStickFolders );

		};

		/**
		 * Compare files based on elFinder.sort
		 *
		 * @param Object file
		 * @param Object file
		 * @return Number
		 */
		this.compare = function ( file1, file2 )
		{
			var self = this,
				type = self.sortType,
				asc = self.sortOrder == 'asc',
				stick = self.sortStickFolders,
				rules = self.sortRules,
				sort = rules[type],
				d1 = file1.mime == 'directory',
				d2 = file2.mime == 'directory',
				res;

			if ( stick ) {
				if ( d1 && !d2 ) {
					return -1;
				} else if ( !d1 && d2 ) {
					return 1;
				}
			}

			res = asc ? sort( file1, file2 ) : sort( file2, file1 );





			return type != 'name' && res == 0
				? res = asc ? rules.name( file1, file2 ) : rules.name( file2, file1 )
				: res;
		};

		/**
		 * Sort files based on config
		 *
		 * @param Array files
		 * @return Array
		 */
		this.sortFiles = function ( files )
		{
			return files.sort( this._compare );
		};

		/**
		 * Set/unset lock for keyboard shortcuts
		 * @param Boolean state
		 */
		this.lockShortcuts = function ( l )
		{
			this.eventsManager.lock = !!l;
		};

		/**
		 * @param string
		 */
		this.setSelected = function ( hash )
		{
			self.selected = [];
			self.selected.push( hash );
			//self.quickview.update();
			self.command.update();
		};

		/**
		 * Return selected files data
		 *
		 * @param Number if set, returns only element with this index or empty object
		 * @return Array|Object
		 */
		this.getSelected = function ( ndx )
		{
			var i, s = [];
			if ( ndx >= 0 ) {
				return this.dircontent[this.selected[ndx]] || {};
			}
			for ( i = 0; i < this.selected.length; i++ ) {
				this.dircontent[this.selected[i]] && s.push( this.dircontent[this.selected[i]] );
			}
			return s;
		};

		/**
		 *
		 * @param {type} el
		 * @param {type} reset
		 * @param {type} fromCoverflow
		 * @returns {undefined}
		 */
		this.select = function ( el, reset, fromCoverflow, scrollToElement )
		{

			if ( reset ) {
				if ( this.opts.view == 'list' ) {
					$( 'tr.ui-selected', self.layout.foldercontentContainer ).removeClass( 'ui-selected' )
				}
				if ( this.opts.view == 'icons' ) {
					$( 'div[hash].ui-selected', self.layout.foldercontentContainer ).removeClass( 'ui-selected' )
				}
			}

			el.addClass( 'ui-selected' );
			self.updateSelect();

			if ( self.opts.coverflow == true && !fromCoverflow && reset ) {
				self.coverflow.getCover( el.attr( 'hash' ) , true);
			}

			self.layout.displayFileInfos( el.attr( 'hash' ) );
		};



		this.scrollTo = function (el) {
			// scroll to element
			if ( this.opts.view == 'list' ) {
				Tools.scrollBar( el.parents( '.body:first' ).children( ':first' ), el.get(0).offsetTop );
			}
			if ( this.opts.view == 'icons' ) {
				Tools.scrollBar( el.parents( '.iconview.body:first' ), el.get(0).offsetTop );
			}
		};





		/**
		 *
		 * @param {type} id
		 * @returns {undefined}
		 */
		this.selectById = function ( id )
		{

			var el;
			if ( this.opts.view == 'list' ) {
				el = $( 'tr[hash="' + id + '"]', self.layout.foldercontentContainer );
			}

			if ( this.opts.view == 'icons' ) {
				el = $( 'div[hash="' + id + '"]', self.layout.foldercontentContainer );
			}

			if ( el.length ) {
				self.select( el );
				self.checkSelectedPos();
			}
		};

		this.selectByPath = function ( path )
		{
			var self = this;

            if ( path ) {

                $.get( 'admin.php?adm=fileman&action=open&gethash=true&path=' + encodeURIComponent( path ), {}, function ( data )
                {
                    if ( Tools.responseIsOk( data ) ) {
                        var el;


                        if (data.cwd && data.cwd.hash && data.cwd.hash != self.cwd.hash ) {
                            self.data = data;
                            self.reload(false);
                            if ( self.opts.view == 'list' && (el = $( 'tr[hash="' + data.hash + '"]', self.layout.foldercontentContainer )).length ) {
                                self.select( el, true, false, true );
                                self.scrollTo (el);
                                return;
                            }

                            if ( self.opts.view == 'icons' && (el = $( 'div[hash="' + data.hash + '"]', self.layout.foldercontentContainer )).length ) {
                                self.select( el, true, false, true );
                                self.scrollTo (el);
                            }
                        }
                        else {

                            if ( self.opts.view == 'list' && (el = $( 'tr[hash="' + data.hash + '"]', self.layout.foldercontentContainer )).length ) {
                                self.select( el, true, false, true );
                                self.scrollTo (el);
                                return;
                            }

                            if ( self.opts.view == 'icons' && (el = $( 'div[hash="' + data.hash + '"]', self.layout.foldercontentContainer )).length ) {
                                self.select( el, true, false, true );
                                self.scrollTo (el);
                            }
                        }

                    }
                    else {

                    }
                } );
            }
		};

		/**
		 *
		 * @param {type} el
		 * @returns {undefined}
		 */
		this.unselect = function ( el )
		{

			el.removeClass( 'ui-selected' );
			self.updateSelect();
		};
		/**
		 *
		 * @param {type} el
		 * @returns {undefined}
		 */
		this.toggleSelect = function ( el )
		{
			el.toggleClass( 'ui-selected' );

			self.updateSelect();
		};

		/**
		 *
		 * @returns {undefined}
		 */
		this.selectAll = function ()
		{
			if ( this.opts.view == 'list' ) {
				$( 'tr[hash]', self.layout.foldercontentContainer ).addClass( 'ui-selected' )
			}

			if ( this.opts.view == 'icons' ) {
				$( 'div[hash]', self.layout.foldercontentContainer ).addClass( 'ui-selected' )
			}

			self.updateSelect();
		};
		/**
		 *
		 * @returns {undefined}
		 */
		this.unselectAll = function ()
		{
			if ( this.opts.view == 'list' ) {
				$( 'tr[hash].ui-selected', self.layout.foldercontentContainer ).removeClass( 'ui-selected' )
			}

			if ( this.opts.view == 'icons' ) {
				$( 'div[hash].ui-selected', self.layout.foldercontentContainer ).removeClass( 'ui-selected' )
			}

			self.updateSelect();
		};
		/**
		 *
		 * @returns {undefined}
		 */
		this.updateSelect = function ()
		{
			self.selected = [];

			if ( this.opts.view == 'list' ) {
				$( 'tr.ui-selected', self.layout.foldercontentContainer ).each( function ()
				{
					self.selected.push( $( this ).attr( 'hash' ) );
				} );
			}

			if ( this.opts.view == 'icons' ) {
				$( 'div[hash].ui-selected', self.layout.foldercontentContainer ).each( function ()
				{
					self.selected.push( $( this ).attr( 'hash' ) );
				} );
			}

			// self.layout.updateTreeSelection();
			self.layout.displayFileInfos( false );
			self.quickview.update();
			self.command.update();
		};

		/**
		 * Scroll selected element in visible position
		 *
		 * @param Boolean check last or first selected element?
		 */
		this.checkSelectedPos = function ( last )
		{
			var s = self.layout.foldercontentContainer.find( '.ui-selected:' + (last ? 'last' : 'first') ).eq( 0 );
			if ( !s )
				return;

			var p = s.position(),
				h = s.outerHeight(),
				ph = self.layout.foldercontentContainer.height();
			if ( !p )
				return;

			if ( p.top < 0 ) {
				self.layout.foldercontentContainer.scrollTop( p.top + self.layout.foldercontentContainer.scrollTop() - 2 );
			} else if ( ph - p.top < h ) {
				self.layout.foldercontentContainer.scrollTop( p.top + h - ph + self.layout.foldercontentContainer.scrollTop() );
			}
		};

		/**
		 * Return folders in places IDs
		 *
		 * @return Array
		 **/
		this.getPlaces = function ()
		{
			var pl = [], p = this.cookie( this.pCookie );
			if ( p.length ) {
				if ( p.indexOf( ':' ) != -1 ) {
					pl = p.split( ':' );
				} else {
					pl.push( p );
				}
			}
			return pl;
		};

		/**
		 * Add new folder to places
		 *
		 * @param String Folder ID
		 * @return Boolean
		 **/
		this.addPlace = function ( id )
		{
			var p = this.getPlaces();
			if ( $.inArray( id, p ) == -1 ) {
				p.push( id );
				this.savePlaces( p );
				return true;
			}
		};

		/**
		 * Remove folder from places
		 *
		 * @param String Folder ID
		 * @return Boolean
		 **/
		this.removePlace = function ( id )
		{
			var p = this.getPlaces();
			if ( $.inArray( id, p ) != -1 ) {
				this.savePlaces( $.map( p, function ( o )
				{
					return o == id ? null : o;
				} ) );
				return true;
			}
		};

		/**
		 * Save new places data in cookie
		 *
		 * @param Array Folders IDs
		 **/
		this.savePlaces = function ( p )
		{
			this.cookie( this.pCookie, p.join( ':' ) );
		};

		/**
		 * Execute after files was dropped onto folder
		 *
		 * @param Object drop event
		 * @param Object drag helper object
		 * @param String target folder ID
		 */
		this.drop = function ( e, ui, target )
		{
			if ( ui.helper.find( '[hash="' + target + '"]' ).length ) {
				return self.layout.error( 'Unable to copy into itself' );
			}

			var ids = [];
			ui.helper.find( '[hash]' ).each( function ()
			{
				var hash = $( this ).attr( 'hash' );
				//ids.push(hash);
				if ( !$( this ).hasClass( 'noaccess' ) ) {

					if ( !$( this ).children( 'em' ).length ) {
						ids.push( hash );
					}
					else if ( $( this ).hasClass( 'directory' ) && !$( this ).children( 'em' ).hasClass( 'readonly' ) ) {
						ids.push( hash );
					}
				}
			} );

			if ( !ui.helper.find( 'div:has(.filename-label>label):visible' ).length ) {
				ui.helper.hide();
			}

			if ( ids.length ) {
				self.setBuffer( ids, e.shiftKey ? 0 : 1, target );

				if ( self.buffer.files ) {
					/* some strange jquery ui bug (in list view) */
					setTimeout( function ()
					{
						self.command.exec( 'paste' );
						self.buffer = {}
					}, 300 );

					$( this ).removeClass( 'el-finder-droppable' );
				}
			}
			else {
				$( this ).removeClass( 'el-finder-droppable' );
			}
		};

		/**
		 * Add files to clipboard buffer
		 *
		 * @param Array files IDs
		 * @param Boolean copy or cut files?
		 * @param String destination folder ID
		 */
		this.setBuffer = function ( files, cut, dst )
		{
			var i, id, f;
			this.buffer = {
				src: this.cwd.hash,
				dst: dst,
				files: [],
				names: [],
				cut: cut || 0
			};

			for ( i = 0; i < files.length; i++ ) {
				id = files[i];
				f = this.dircontent[id];
				if ( f && f.read && f.type != 'link' ) {
					this.buffer.files.push( f.hash );
					this.buffer.names.push( f.name );
				}
			}

			if ( !this.buffer.files.length ) {
				this.buffer = {};
			}
		};

		/**
		 *  Open the selected (dblclicked) file/dir
		 *
		 */
		this.open = function ( node, hash, type, selectingFile )
		{
			if ( hash ) {

				var self = this, parents = this.parents( hash );
				for ( var x = 0; x < parents.length; x++ ) {

					var k = 'a[hash=' + parents[x].hash + ']', folder = $( this.layout.treeContainer ).find( k );

					if ( !$( folder ).find( '.collapsed' ).hasClass( 'expanded' ) ) {
						$( folder ).find( '.collapsed' ).addClass( 'expanded' );
						$( folder ).parent().find( '.subtree:first' ).show();
						//  console.log('open ' + folder.text());

						// container = folder.parent().find('ul:first').get(0);
					}
				}

				var folder = $( this.layout.treeContainer ).find( 'a[hash=' + hash + ']' );
				if ( !folder.find( '.collapsed:first' ).hasClass( 'expanded' ) ) {
					folder.find( '.collapsed:first' ).click();
					$( this.layout.treeContainer ).find( 'a.selected' ).removeClass( 'selected' ).parent().removeClass( 'selected' );
					folder.addClass( 'selected' ).parent().addClass( 'selected' );
				}
				else {
					$( this.layout.treeContainer ).find( 'a.selected' ).removeClass( 'selected' ).parent().removeClass( 'selected' );
					folder.addClass( 'selected' ).parent().addClass( 'selected' );
				}

				if ( (type == 'dir' || type == 'directory') && folder.length && typeof fm._files[hash] == 'undefined' ) {

					var path = self.getPathByHash( hash );
					this.layout.spinner( true );
					fm.getAjaxData( {
							action: 'open',
							pathHash: hash,
							cwd: encodeURIComponent( self.layout.getCwdPath() ),
							type: 'dir'              // (file/dir)
						},
						function ()
						{
							fm.prepareData();
						} );

				}
				else {

					if ( (fm._files[hash] && fm._files[hash].mime == 'directory') || type == 'dir' ) {
						var path = self.getPathByHash( hash );
						this.layout.spinner( true );
						fm.getAjaxData( {
								action: 'open',
								pathHash: hash,
								cwd: encodeURIComponent( self.layout.getCwdPath() ),
								type: 'dir'              // (file/dir)
							},
							function ()
							{
								fm.prepareData();
							} );
					}
					else if ( fm._files[hash] && fm._files[hash].mime != 'directory' && type == 'file' && typeof selectingFile != 'function' ) {

						// only for download
						var path = self.getPathByHash( hash );
						var iframe = $( '<iframe style="display:none!important;" id="file-downloading" src="admin.php?adm=fileman&action=open&type=file&fpathHash=' + hash + '&cwd=' + encodeURIComponent( self.layout.getCwdPath() ) + '"></iframe>' );

						iframe.on( 'load', function ()
						{
							setTimeout( function ()
							{
								iframe.remove();
								setTimeout( function ()
								{
									self.layout.spinner( false );
									self.lock( false );
									self.lockShortcuts();
								}, 500 );
							}, 50 );
						} );

						fm.globalContainer.append( iframe );
						return;
					}
					else if ( fm._files[hash] && fm._files[hash].mime != 'directory' && type == 'file' && typeof selectingFile == 'function' ) {
						var path = self.getPathByHash( hash );

						$.get( 'admin.php?adm=fileman&action=open&filesection=true&type=file&fpathHash=' + hash + '&cwd=' + encodeURIComponent( self.layout.getCwdPath() ), function ( data )
						{
							if ( Tools.responseIsOk( data ) ) {
								return selectingFile( data.filepath, data.width, data.height );
							}
						} );
					}
					else {
						Debug.error( 'This item is not a directory!' );

					}
				}
			}
			else {
				Debug.error( 'This hash (' + hash + ') was not found!' );
			}
		};

		/**
		 *
		 * @returns string
		 */
		this.getCwd = function ()
		{
			return this.cwd.join( '/' );
		};

		// get the path by hash
		this.getPathByHash = function ( hash )
		{
			return this.layout.getCwdPath();
		};

		/**
		 * Set/unset lock for keyboard shortcuts
		 *
		 * @param Boolean state
		 **/
		this.lockShortcuts = function ( l )
		{
			this.command.lock = !!l;
		};

		/**
		 *
		 * @param {type} params
		 * @param {type} callback
		 * @returns {undefined}
		 */
		this.ajax = function ( params, callback )
		{
			this.getAjaxData( params, callback );
		};

		/**
		 *
		 * @param {type} params
		 * @param {type} callback
		 * @returns {undefined}
		 */
		this.getAjaxData = function ( params, callback )
		{
			var self = this, _params = typeof params == 'object' ? params : {};
			this.lock( true );

			if (this.opts.selectFile) {
				_params.selectfile = this.opts.selectFile;
			}

			$.ajax( {
				url: this.opts.connectorUrl,
				method: 'GET',
				data: _params,
				dataType: 'json',
				async: true,
				cache: false,
				error: function ()
				{

					self.ajaxLoading = false;
				},
				success: function ( data )
				{
					self.data = data;

					if ( Tools.responseIsOk( data ) ) {
						_params.lock && self.lock();

						data.debug && self.log( data.msg );

						if ( typeof callback == 'function' ) {
							callback( self.data );
						}

						delete data;
					}
					else {
						if ( typeof callback == 'function' ) {
							callback( data );
						}

						if ( data && Tools.exists( data, 'msg' ) ) {
							self.layout.error( data.msg, (Tools.exists( data, 'errorData' ) ? data.errorData : null) );
						}
						else {
							Debug.error( [data] );
						}

						self.lock( false );
					}
				}
			} );
		};

		/**
		 *  Update the Panel sizes
		 *  (can use for resize the fileman container)
		 */
		this.resizePanels = function ( callback )
		{
			this.layout.updatePanelSizes( callback );
		};

		/**
		 *
		 * @returns {undefined}
		 */
		this.fixTableWidth = function ()
		{
			this.layout.fixTableSize();
		};

		/**
		 *
		 * @type @exp;$@call;extend
		 */
		var settings = $.extend( {}, this.defaults, options );



		this.opts = settings;

		this.globalContainer = obj;

		if ( this.opts.toolbarContainer ) {
			this.toolbarContainer = $( this.opts.toolbarContainer );
		}
		else {
			this.toolbarContainer = obj.find( '.fileman-toolbar' );
		}

		this.id = obj.attr( 'id' );
		this.window = (Desktop.isWindowSkin ? obj.parents( 'div.isWindowContainer:first' ) : obj.parent());

		$( element ).show();
		this.initFileman();

		element.Fileman = fm;

		// return element;
	};

	/**
	 *
	 * @param object events
	 * @returns
	 */
	$.fn.registerEvents = function ( events )
	{
		return this.each( function ()
		{
			if ( this.Fileman ) {
				this.Fileman.extraEvents = $.extend( {}, this.Fileman.extraEvents, events );
			}
		} );
	};

	/**
	 *
	 * @param object o
	 * @returns
	 */
	$.fn.Filemanager = function ( o )
	{
		return this.each( function ()
		{
			if ( !this.Fileman ) {
				this.Fileman = new Fileman( this, o );
			}
		} );
	};

	/**
	 *
	 * @returns
	 */
	$.fn.fixTableWidth = function ()
	{
		return this.each( function ()
		{
			if ( this.Fileman && this.Fileman.layout ) {
				this.Fileman.layout.fixTableSize();
			}
		} );
	};

	$.fn.selectFile = function ( path )
	{
		if ( this[0].Fileman && this[0].Fileman.layout ) {
			this[0].Fileman.selectByPath( path );
		}
	};

	/**
	 *
	 * @returns
	 */
	$.fn.getSelectedFile = function ()
	{

		var prop = false;

		if ( this[0].Fileman && this[0].Fileman.layout ) {
			if ( this[0].Fileman.selected.length == 1 ) {

				var s = this[0], f = this[0].Fileman.getSelected( 0 );
				var el = $( '[hash="' + f.hash + '"]', s.Fileman.layout.foldercontentContainer );
				if ( el.length ) {

					var key = f.hash;
					var type = el.hasClass( 'directory' ) ? 'dir' : 'file';
					var prop = {};

					s.Fileman.open( el, key, type, function ( path, width, height )
					{
						prop = {
							path: path,
							height: height,
							width: width
						};
					} );

				}
			}
		}

		return prop;

	};

	/**
	 *
	 * @returns
	 */
	$.fn.resizePanels = function ( callback )
	{
		return this.each( function ()
		{
			if ( this.Fileman && this.Fileman.layout ) {
				this.Fileman.layout.updatePanelSizes( callback );
			}
		} );
	};
})( jQuery, window );

(function ($) {
	"use strict";

	Fileman.prototype.EventManager = function (fm) {
		this.fm = fm;
		this.lock = false;

		this.isLocked = function() {
			return this.locked;
		};
	}

})(jQuery, window);