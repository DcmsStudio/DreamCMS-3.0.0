/**
 * Created by marcel on 28.03.14.
 */
;
(function ( $, window, document, undefined )
{

	//"use strict"; // jshint ;_;
	var pluginName = 'dcmsFileinput';

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
			if (typeof this.id != 'string') {
				this.id = 'fileinput-' + new Date().getTime();
				this.obj.attr( 'id', this.id );
			}

			//
			this.obj.attr( 'tabindex', "-1");


			var self = this;
			var button = $('<span class="button"></span>');
			var container = $('<div class="file-input input"></div>');

			var text = false, addTip = false;
			if (this.obj.attr('title')) {
				text = this.obj.attr('title');
				this.attrTitle = text;
				this.obj.removeAttr('title');
			}
			else if (this.obj.data('tooltip')) {
				text = this.obj.data('tooltip');
			}

			if ( (this.o.tooltip === true && text !== false) || (this.o.tooltip === 'auto' && text) && this.o.position ) {
				container.addClass('input-tooltip');

				if (this.o.position.match(/right/ig)) {
					container.append('<i class="icon-append '+ this.o.questionIconClass +'"></i>');
					container.addClass('tip-right');
				}
				else if (this.o.position.match(/left/ig)) {
					container.append('<i class="icon-prepend '+ this.o.questionIconClass +'"></i>');
					container.addClass('tip-left');
				}
				else {
					container.append('<i class="icon-append '+ this.o.questionIconClass +'"></i>');
					container.addClass('tip-right');
				}
				addTip = true;
			}




			var placeholderLabel = this.o.placeholderLabel;
			if (this.obj.data('placeholder')) {
				placeholderLabel = this.obj.data('placeholder');
			}


			var input = $('<input class="input-dummy" type="text" readonly="readonly" placeholder="' + placeholderLabel + '">');
			container.append(button);
			container.append(input);

			if (addTip)
			{
				if ( typeof BBCodeConverter != 'undefined') {
					var bbcode = new BBCodeConverter();
					text = bbcode.bbcodeToHtml(text);
				}

				container.append( $('<b class="tooltip tooltip-'+ this.o.position +'"><i class="'+ this.o.tipIconClass +' txt-color-teal"></i></b>' ).append(text) );
				container.find('.icon-append,.icon-prepend').click(function() {
					$(this ).parent().find('.input-dummy').focus();
				});

			}


			container.insertBefore(this.obj);
			this.obj.appendTo(button);
			button.append(this.o.buttonLabel );

			this.obj.attr('onchange', 'this.parentNode.nextSibling.value = this.value');

		},
		/**
		 * Destroy.
		 *
		 * @param:
		 **/
		destroy: function ()
		{
			var container = this.obj.parent().parent();
			this.obj.insertBefore(container);
			container.remove();
			this.obj.removeAttr('onchange');
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
		buttonLabel: 'Browse',
		placeholderLabel: 'Include some files',

		// for tooltip
		tooltip: false, // true, false or auto
		questionIconClass: 'fa fa-question-circle',
		tipIconClass: 'fa fa-warning',
		position: 'top-left'
	};

})( jQuery, window, document );