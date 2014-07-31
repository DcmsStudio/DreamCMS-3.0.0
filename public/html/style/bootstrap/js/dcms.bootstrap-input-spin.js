/**
 * Created by marcel on 13.04.14.
 */
;
(function ( $, window, document, undefined )
{

	//"use strict"; // jshint ;_;
	var pluginName = 'dcmsInputSpin';

	function Plugin( element, options )
	{

		/**
		 * Variables.
		 **/
		this.obj = $( element );

		this.o = $.extend( {}, $.fn[pluginName].defaults, options );
		this.id = this.obj.attr( 'id' );

		this.init();
	};

	Plugin.prototype = {

		init: function ()
		{
			var self = this, min, max, step;

			if ( typeof this.id != 'string' ) {
				this.id = 'inputspin-' + new Date().getTime();
				this.obj.attr( 'id', this.id );
			}

			if ( this.obj.data( 'position' ) ) {
				this.o.position = this.obj.data( 'position' );
			}
			min = parseFloat( this.obj.data( 'min' ) );
			if ( !isNaN( min ) ) {
				this.o.min = min;
			}

			max = parseFloat( this.obj.data( 'max' ) );
			if ( !isNaN( max ) ) {
				this.o.max = max;
			}

			step = parseFloat( this.obj.data( 'step' ) );
			if ( !isNaN( step ) ) {
				this.o.step = step;
			}

			var container = $( '<div class="input-spin"></div>' );

			var value = parseFloat( this.obj.val() );
			if ( isNaN( value ) ) {
				this.obj.val( 0 );
			}

			var up = $( '<span class="up"><span class="' + this.o.upIconClass + '"></span></span>' );
			var down = $( '<span class="down"><span class="' + this.o.downIconClass + '"></span></span>' );

			this.up = up;
			this.down = down;

			var spinButtons = $( '<div class="input-spinbuttons"></div>' );
			spinButtons.append( up ).append( down );

			if ( this.o.position.match( /right/ig ) ) {
				container.addClass( 'pos-right' );
			}
			else if ( this.o.position.match( /left/ig ) ) {
				container.addClass( 'pos-left' );
			}
			else {
				container.addClass( 'pos-right' );
			}
			container.insertBefore( this.obj );
			this.obj.appendTo( container );
			container.append( spinButtons );

			var t;

			up.on( 'mousedown', function ( e )
			{
				t = setTimeout( function () { self._keyDown( 'up' ); }, 200 );
			} );

			up.on( 'mouseup', function ( e )
			{
				clearTimeout( t );
				clearTimeout( self.keyTimer );
				e.preventDefault();
			} );

			up.on( 'click', function ( e )
			{
				self.execute( 'up', this );
			} );

			down.on( 'mousedown', function ( e )
			{
				t = setTimeout( function () { self._keyDown( 'down' ); }, 200 );
			} );

			down.on( 'mouseup', function ( e )
			{
				clearTimeout( t );
				clearTimeout( self.keyTimer );
				e.preventDefault();
			} );

			down.on( 'click', function ( e )
			{
				self.execute( 'down', this );
			} );

			this.obj.on( 'focus', function ()
			{
				self.previous = parseFloat( $( this ).val() );
			} );

			this.obj.on( 'blur', function ()
			{
				if ( self.previous != parseFloat( $( this ).val() ) ) {
					$( this ).trigger( 'change' );
				}
			} );

			this.obj.on( 'mousewheel', function ( e )
			{

				if ( !e.originalEvent.deltaY ) {
					return;
				}

				if ( e.originalEvent.deltaY < 0 ) {
					self.execute( 'up', up );
				}
				else {
					self.execute( 'down', down );
				}
				e.preventDefault();
			} );

			this.obj.on( 'keydown', function ( e )
			{

				clearTimeout( self.keyTimer );

				if ( e.keyCode === 38 ) {
					self._keyDown( 'up', up );
				}

				if ( e.keyCode === 40 ) {
					self._keyDown( 'down', down );
				}
			} );

			this.obj.on( 'keyup', function ( e )
			{
				clearTimeout( self.keyTimer );

				var v = parseFloat( $( this ).val() );
				if ( isNaN( v ) ) {
					$( this ).val( 0 );
					self.previous = 0;
				}

			} );
		},

		_keyDown: function ( mode )
		{
			var self = this;

			if ( mode == 'up' ) { this.execute( 'up', this.up ); }
			if ( mode == 'down' ) { this.execute( 'down', this.up ); }

			this.keyTimer = setTimeout( function ()
			{
				self._keyDown( mode );
			}, 100 );
		},

		execute: function ( mode, btn )
		{

			var value = parseFloat( (this.obj.val() || 0) );
			this.previous = value;

			if ( mode === 'up' ) {
				var nv = value + this.o.step;

				if ( this.o.max !== false && nv > this.o.max ) {

					this.obj.val( this.o.max );

					return false;
				}
				this.obj.val( nv );

				if ( this.previous != nv ) {
					this.obj.trigger( 'change' );
				}

				if ( this.o.max !== false && nv == this.o.max ) {
					$( btn ).addClass( 'diabled' );
				}
				else {
					$( btn ).removeClass( 'diabled' );
				}
			}
			else if ( mode === 'down' ) {
				var nv = value - this.o.step;

				if ( this.o.min !== false && nv < this.o.min ) {

					this.obj.val( this.o.min );
					return false;
				}

				this.obj.val( nv );

				if ( this.previous != nv ) {
					this.obj.trigger( 'change' );
				}

				if ( this.o.min !== false && nv == this.o.min ) {
					$( btn ).addClass( 'diabled' );
				}
				else {
					$( btn ).removeClass( 'diabled' );
				}
			}
		},

		/**
		 * Destroy.
		 *
		 * @param:
		 **/
		destroy: function ()
		{
			var container = this.obj.parent();
			this.obj.insertBefore( container );
			container.remove();
			this.obj.removeData( pluginName );
		}
	};

	$.fn[pluginName] = function ( option )
	{
		return this.each( function ()
		{
			var $this = $( this );
			if ( $this.is( 'input' ) && $this.attr( 'type' ) !== 'hidden' && $this.attr( 'type' ) !== 'password' ) {
				var data = $this.data( pluginName );
				var options = typeof option == 'object' && option;

				if ( data && typeof option == 'string' ) {
					data[option]();
				}

				if ( !data ) {
					$this.data( pluginName, (data = new Plugin( this, options )) )
				}
			}
		} );
	};

	$.fn[pluginName].defaults = {
		upIconClass: 'fa fa-caret-up',
		downIconClass: 'fa fa-caret-down',
		position: 'right',
		min: false,
		max: false,
		step: 1
	};

})( jQuery, window, document );