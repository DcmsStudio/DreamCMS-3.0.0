/**
 * Created by marcel on 01.04.14.
 * use Inputtrigger AFTER Bootstrap Tooltip
 */
;
(function ( $, window, document, undefined )
{

	//"use strict"; // jshint ;_;
	var pluginName = 'dcmsInputTrigger';

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
			var self = this, method = this.obj.attr('data-inputtrigger') ? this.obj.attr('data-inputtrigger') : this.obj.data('inputtrigger');
			if (!method && !this.o.method) {
				return;
			}

			if (method.replace(/\s/g, '') == '') {
				return;
			}

			var value = this.obj.val(), label = this._getCalculation(value, method.toLowerCase() );
			var after = this.obj.parent();
			this.label = $('<span class="input-description"></span>' ).append(label );
			after.append(this.label);

			this.o.method = method.toLowerCase();


			this.obj.bind('keyup.'+pluginName, function() {
				var label = self._getCalculation($(this).val(), self.o.method );
				self.label.empty().append(label);
			});

		},

		_getCalculation: function(value, method) {
			var str = '';

			if (value) {
				if ( method == 'calctime' ) {
					var converted = convertMS(value);

					if (converted.day && converted.day > 0) {
						str += converted.day + (converted.day > 1 ? ' days, ' : ' day, ');
					}

					if (converted.hour && converted.hour > 0) {
						str += converted.hour + (converted.hour > 1 ? ' Hours, ' : ' Hour, ');
					}

					if (converted.min && converted.min > 0) {
						str += converted.min + ' min, ';
					}

					if (converted.sec && converted.sec > 0) {
						str += converted.sec + 'sec';
					}

					str = str.replace(/,\s*$/, '');
				}
			}

			return str;
		},


		/**
		 * Destroy.
		 *
		 * @param:
		 **/
		destroy: function ()
		{
			this.label.remove();
			this.obj.unbind('keyup.'+pluginName);
			this.obj.removeData( pluginName );
		}
	};


	$.fn[pluginName] = function ( option )
	{

		return this.each( function ()
		{
			var $this = $( this );
			var method = $this.attr('data-inputtrigger') ? $this.attr('data-inputtrigger') : $this.data('inputtrigger');

			if (!method && (typeof option == 'object' && !option.method) ) {
				return this;
			}

			var options = typeof option == 'object' && option;
			var data = $this.data( pluginName );

			if ( !data ) {
				$this.data( pluginName, (data = new Plugin( this, options )) )
			}
			if ( typeof option == 'string' ) {
				data[option]();
			}
		} );
	};

	$.fn[pluginName].defaults = {
		method: false
	};

})( jQuery, window, document );