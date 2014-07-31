/**
 * Created by marcel on 28.03.14.
 */


var Bootstraper = {


	validInputTypes: {'text': true, 'password': true, 'date': true, 'time': true},

	init: function(container) {
		var self = this;


		if (!container) {
			container = $('body');
		}



		container.find('input[type=file]' ).each(function(){
			$(this).dcmsFileinput( {
				tooltip: 'auto',
				position: 'top-right'
			} );
		});


		container.find('textarea,input:not([type=file],[type=hidden])' ).each(function(){
			if (typeof self.validInputTypes[$(this ).attr('type')] !== 'undefined' || this.tagName.toLowerCase() == 'textarea') {


				$(this).dcmsInputTooltip( {
					position: 'top-right'
				});

				$(this).dcmsInputTrigger();

				if ($(this ).attr('type') == 'text' && $(this ).data('toggle') === 'spin') {
					$(this).dcmsInputSpin();
				}
			}
		});
	},
	destroy: function(container) {
		var self = this;

		container.find('input[type=file]' ).each(function(){
			$(this).dcmsFileinput('destroy');
		});

		container.find('textarea,input:not([type=file],[type=hidden])' ).each(function(){
			if (typeof self.validInputTypes[$(this ).attr('type')] !== 'undefined' || this.tagName.toLowerCase() == 'textarea') {

				$(this).dcmsInputTooltip('destroy');
				$(this).dcmsInputTrigger('destroy');
				$(this).dcmsInputSpin('destroy');
			}
		});
	}
};