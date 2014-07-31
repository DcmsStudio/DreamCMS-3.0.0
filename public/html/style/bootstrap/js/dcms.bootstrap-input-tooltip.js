/**
 * Created by marcel on 28.03.14.
 */
;
(function ( $, window, document, undefined )
{

	//"use strict"; // jshint ;_;
	var pluginName = 'dcmsInputTooltip';

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
			var self = this, cls = 'input';

			var text = '';
			if (this.obj.attr('title')) {
				if (typeof this.obj.attr('title') === 'string' && this.obj.attr('title').replace(/\s/g, '') != '' )
				{
					text = this.obj.attr('title');
					this.attrTitle = text;
					this.obj.removeAttr('title');
				}
			}

			if (!text && this.obj.data('tooltip')) {
				text = this.obj.data('tooltip');
			}
			if (!text && this.obj.attr('data-tooltip')) {
				text = this.obj.attr('data-tooltip');
			}
			if (text.replace(/\s/g, '') == '') {
				return;
			}



			if ( typeof BBCodeConverter != 'undefined') {
				var bbcode = new BBCodeConverter();
				text = bbcode.bbcodeToHtml(text);
			}


			if (this.obj.is('textarea')) {
				cls = 'textarea';
			}



			if (typeof this.id != 'string') {
				this.id = cls + '-' + new Date().getTime();
				this.obj.attr( 'id', this.id );
			}


			if (this.obj.data('position')) {
				this.o.position = this.obj.data('position');
			}

			var container = $('<div class="input-tooltip ' + cls +'"></div>');

			if (this.o.position.match(/right/ig)) {
				container.append('<i class="icon-append '+ this.o.questionIconClass +'"></i>');
			}
			else if (this.o.position.match(/left/ig)) {
				container.append('<i class="icon-prepend '+ this.o.questionIconClass +'"></i>');
			}
			else {
				container.append('<i class="icon-append '+ this.o.questionIconClass +'"></i>');
			}

			container.find('.icon-append,.icon-prepend').click(function() {
				self.obj.focus();
			});

			container.insertBefore( this.obj );
			this.obj.appendTo(container);


			var inner = $('<b class="tooltip tooltip-'+ this.o.position +'"><i class="'+ this.o.tipIconClass +' txt-color-teal"></i></b>' ).append(text);
			inner.find('a' ).each(function() {
				$(this ).click(function() {
					self.obj.focus();
				})
			});

			container.append( inner );

			/*
			this.obj.bind('focus.tooltip', function() {

			});
			this.obj.bind('blur.tooltip', function() {

			});
			*/
		},
		/**
		 * Destroy.
		 *
		 * @param:
		 **/
		destroy: function ()
		{
			if ( typeof this.attrTitle === 'string') {
				this.obj.attr('title', this.attrTitle);
				this.attrTitle = false;
			}
			var container = this.obj.parent();
			this.obj.insertBefore(container);
			container.remove();
/*
			this.obj.unbind('focus.tooltip');
			this.obj.unbind('blur.tooltip');
*/
			this.obj.removeData( pluginName );
		}
	};


	$.fn[pluginName] = function ( option )
	{
		return this.each( function ()
		{
			var $this = $( this );
			var data = $this.data( pluginName );
			var options = typeof option == 'object' && option;
			if ( !data ) {
				$this.data( pluginName, (data = new Plugin( this, options )) )
			}
			if ( typeof option == 'string' ) {
				data[option]();
			}
		} );
	};

	$.fn[pluginName].defaults = {
		questionIconClass: 'fa fa-question-circle',
		tipIconClass: 'fa fa-warning',
		position: 'top-right', // tooltip-right, tooltip-left
	};

})( jQuery, window, document );